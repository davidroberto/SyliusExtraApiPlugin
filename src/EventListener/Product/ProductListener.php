<?php


namespace DavidRoberto\SyliusExtraApiPlugin\EventListener\Product;


use Sylius\Bundle\ResourceBundle\Event\ResourceControllerEvent;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ProductListener
{

    private $client;
    /**
     * @var ParameterBagInterface
     */
    private $parameterBag;

    public function __construct(HttpClientInterface $client, ParameterBagInterface $parameterBag)
    {
        $this->client = $client;
        $this->parameterBag = $parameterBag;
    }

    public function onProductPostCreate(ResourceControllerEvent $event)
    {
//        $this->deployFront();
    }

    public function onProductPostDelete(ResourceControllerEvent $event)
    {
//        $this->deployFront();
    }

    public function onProductPostUpdate(ResourceControllerEvent $event)
    {
//        $this->deployFront();
    }

    private function deployFront(): void
    {

        $this->client->request(
            'POST',
            $this->parameterBag->get('vercel_hook_deploy')
        );
    }

}
