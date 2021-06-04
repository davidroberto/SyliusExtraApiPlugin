<?php


namespace DavidRoberto\SyliusExtraApiPlugin\Controller\Api;


use Doctrine\ORM\EntityManagerInterface;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\UserRepository;
use Sylius\Component\Resource\Metadata\Metadata;
use Sylius\Component\User\Model\User;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class ReinitPasswordController
{
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var ParameterBagInterface
     */
    private $params;

    public function __construct(
        RequestStack $requestStack,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        ParameterBagInterface $params
    ) {
        $this->userRepository = $userRepository;
        $this->requestStack = $requestStack;
        $this->entityManager = $entityManager;
        $this->params = $params;
    }

    public function __invoke()
    {
        $request = $this->requestStack->getCurrentRequest();
        $body = json_decode($request->getContent(), true);
        $token = $body['token'];

        /** @var User $user */
        $user = $this->userRepository->findOneBy(['passwordResetToken' => $token]);
        if (null === $user) {
            return false;
        }

        $resetting = $this->params->get('sylius.resources')['sylius.shop_user']['resetting'];
        $lifetime = new \DateInterval($resetting['token']['ttl']);

        if (!$user->isPasswordRequestNonExpired($lifetime)) {
            $user->setPasswordResetToken(null);
            $user->setPasswordRequestedAt(null);

            $this->entityManager->flush();

            return false;
        }

        $newPassword = $body['newPassword'];


        $user->setPlainPassword($newPassword);
        $user->setPasswordResetToken(null);
        $user->setPasswordRequestedAt(null);

        $this->entityManager->flush();

        return true;
    }
}
