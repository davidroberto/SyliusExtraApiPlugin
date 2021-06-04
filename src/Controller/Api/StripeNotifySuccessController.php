<?php


namespace DavidRoberto\SyliusExtraApiPlugin\Controller\Api;


use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use SM\Factory\FactoryInterface;
use Stripe\Event;
use Sylius\Bundle\CoreBundle\Mailer\Emails;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\OrderPaymentTransitions;
use Sylius\Component\Core\Repository\PaymentRepositoryInterface;
use Sylius\Component\Mailer\Sender\SenderInterface;
use Sylius\Component\Payment\PaymentTransitions;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use \Stripe\Webhook;
use \Stripe\Stripe;

class StripeNotifySuccessController
{

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var ParameterBagInterface
     */
    private $params;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var PaymentRepositoryInterface
     */
    private $paymentRepository;
    /**
     * @var FactoryInterface
     */
    private $stateMachineFactory;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var SenderInterface
     */
    private $emailSender;

    public function __construct(
        RequestStack $requestStack,
        ParameterBagInterface $params,
        LoggerInterface $logger,
        PaymentRepositoryInterface $paymentRepository,
        FactoryInterface $stateMachineFactory,
        EntityManagerInterface $entityManager,
        SenderInterface $emailSender
    ) {
        $this->requestStack = $requestStack;
        $this->params = $params;
        $this->logger = $logger;
        $this->paymentRepository = $paymentRepository;
        $this->stateMachineFactory = $stateMachineFactory;
        $this->entityManager = $entityManager;
        $this->emailSender = $emailSender;
    }

    public function __invoke()
    {
        Stripe::setApiKey($this->params->get('stripe_secret_key'));
        $endpoint_secret = $this->params->get('stripe_success_endpoint_secret_key');

        $payload = @file_get_contents('php://input');
        $request = $this->requestStack->getCurrentRequest();
        $signature = $request->headers->get('Stripe-Signature');

        $event = null;

        try {
            $event = Webhook::constructEvent(
                $payload, $signature, $endpoint_secret
            );

            $this->logger->info('signature');
            $this->logger->info($event);
        } catch(\UnexpectedValueException $e) {
            // Invalid payload
            return new Response(Response::HTTP_UNAUTHORIZED);
        } catch(\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            return new Response(Response::HTTP_BAD_REQUEST);
        }

        if ($event->type == 'payment_intent.succeeded') {
            $this->handlePaymentSuccess($event);
        }

        return new Response(Response::HTTP_OK);
    }

    /**
     * @param Event $event
     * @throws \SM\SMException
     */
    private function handlePaymentSuccess(Event $event): void
    {
        $session = $event->data->object;
        $this->logger->info('payment succeeded');
        $this->logger->info($event);
        $this->completePayment($session);
    }

    /**
     * @param $session
     * @throws \SM\SMException
     */
    private function completePayment($session): void
    {
        $this->logger->info('session id');
        $this->logger->info($session->id);
        /** @var PaymentInterface $payment */
        $payment = $this->paymentRepository->findOneByPaymentIntentId($session->id);
        $this->logger->info('payment');
        $this->logger->info($payment->getId());
        $order = $payment->getOrder();

        $this->logger->info('order id');
        $this->logger->info($order->getId());

        $stateMachine = $this->stateMachineFactory->get($order, OrderPaymentTransitions::GRAPH);
        $stateMachine->apply(OrderPaymentTransitions::TRANSITION_PAY);

        $stateMachine = $this->stateMachineFactory->get($payment, PaymentTransitions::GRAPH);
        $stateMachine->apply(PaymentTransitions::TRANSITION_COMPLETE);

//        $this->emailSender->send(
//            Emails::ORDER_CONFIRMATION_RESENT,
//            [$order->getCustomer()->getEmail()],
//            [
//                'order' => $order,
//                'channel' => $order->getChannel(),
//                'localeCode' => $order->getLocaleCode(),
//            ]
//        );

        $this->entityManager->flush();
    }

}
