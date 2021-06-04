<?php


namespace DavidRoberto\SyliusExtraApiPlugin\Repository;

use Sylius\Bundle\CoreBundle\Doctrine\ORM\PaymentRepository as BasePaymentRepository;

class PaymentRepository extends BasePaymentRepository
{

    /**
     * @param string $stripeCheckoutId
     * @return int|mixed|string|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneByPaymentIntentId(string $stripeCheckoutId)
    {
        $qb = $this->createQueryBuilder('p')
            ->where("JSON_CONTAINS(p.details, :stripeCheckoutId, '$.id') = 1")
            ->setParameter('stripeCheckoutId', '"' . $stripeCheckoutId . '"')
            ->orderBy("p.id", "DESC")
            ->setMaxResults(1)
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }

}
