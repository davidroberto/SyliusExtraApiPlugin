<?php

namespace DavidRoberto\SyliusExtraApiPlugin\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;

class JWTCreatedListener
{
    public function onJWTCreated(JWTCreatedEvent $event)
    {
        $payload = $event->getData();
        $payload['id'] = $event->getUser()->getCustomer()->getId();

        $event->setData($payload);
    }

}
