<?php


namespace DavidRoberto\SyliusExtraApiPlugin\Controller\Api;

use Sylius\Bundle\CoreBundle\Doctrine\ORM\UserRepository;
use Sylius\Component\Core\Repository\CustomerRepositoryInterface;
use Sylius\Component\User\Repository\UserRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;

class GetCustomerUserByEmailController
{

    /**
     * @var RequestStack
     */
    private $requestStack;
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;
    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    public function __construct(
        RequestStack $requestStack,
        CustomerRepositoryInterface $customerRepository,
        UserRepository $userRepository
    ) {
        $this->requestStack = $requestStack;
        $this->customerRepository = $customerRepository;
        $this->userRepository = $userRepository;
    }

    public function __invoke() {
        $request = $this->requestStack->getCurrentRequest();
        $email = $request->get('id');

        $customerUser = $this->userRepository->findBy(['username' => $email]);

        if (empty($customerUser)) {
            return false;
        }

        return true;
    }

}
