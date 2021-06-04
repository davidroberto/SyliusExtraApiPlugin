<?php

namespace DavidRoberto\SyliusExtraApiPlugin\Repository;

use Doctrine\ORM\QueryBuilder;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\ProductRepository as BaseProductRepository;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Sylius\Component\Channel\Model\ChannelInterface;
use Sylius\Component\Core\Model\TaxonInterface;

class ProductRepository extends BaseProductRepository
{

	public function searchByTerm(ChannelInterface $channel, string $locale, $searchTerm): array
	{
		$qb = $this->createQueryBuilder('p')
					->addSelect('translation')
					// get the translated product for the product regarding the current locale
					->innerJoin('p.translations', 'translation', 'WITH', 'translation.locale = :locale')
					->orWhere('translation.name LIKE :searchTerm')
					->orWhere('translation.description LIKE :searchTerm')
					// get the taxons of the product
					->innerJoin('p.productTaxons', 'productTaxon')
					->innerJoin('productTaxon.taxon', 'taxon')
					// get the translated taxon
					->innerJoin('taxon.translations', 'taxonTranslation', 'WITH', 'taxonTranslation.locale = :locale')
					->orWhere('taxonTranslation.name LIKE :searchTerm')

					->andWhere(':channel MEMBER OF p.channels')
					->andWhere('p.enabled = true')
					->setParameter('searchTerm', '%'.$searchTerm.'%')
					->setParameter('locale', $locale)
					->setParameter('channel', $channel)
					->getQuery();

		return $qb->getResult();

	}

}


