<?php


namespace DavidRoberto\SyliusExtraApiPlugin\Controller\Api;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\UserRepository;
use Sylius\Bundle\UserBundle\UserEvents;
use Sylius\Component\User\Repository\UserRepositoryInterface;
use Sylius\Component\User\Security\Generator\GeneratorInterface;
use Sylius\Component\User\Security\Generator\UniqueTokenGenerator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\RequestStack;

class ReinitPasswordRequestController
{

    /**
     * @var RequestStack
     */
    private $requestStack;
    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;
    /**
     * @var UniqueTokenGenerator
     */
    private $generator;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;


    public function __construct(
        RequestStack $requestStack,
        UserRepository $userRepository,
        GeneratorInterface $generator,
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->requestStack = $requestStack;
        $this->userRepository = $userRepository;
        $this->generator = $generator;
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function __invoke() {

        $request = $this->requestStack->getCurrentRequest();
        $body = json_decode($request->getContent(), true);
        $email = $body['email'];

        $user = $this->userRepository->findOneByEmail($email);
        if (null !== $user) {

            $user->setPasswordResetToken($this->generator->generate());
            $user->setPasswordRequestedAt(new \DateTime());

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->eventDispatcher->dispatch(new GenericEvent($user), UserEvents::REQUEST_RESET_PASSWORD_TOKEN);

            return true;
        }


        return false;
    }

}
