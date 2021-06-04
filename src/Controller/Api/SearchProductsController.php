<?php


namespace DavidRoberto\SyliusExtraApiPlugin\Controller\Api;


use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Core\Repository\ProductRepositoryInterface;
use Sylius\Component\Locale\Context\LocaleContextInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class SearchProductsController
{

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;
    /**
     * @var RequestStack
     */
    private $requestStack;
    /**
     * @var LocaleContextInterface
     */
    private $localeContext;
    /**
     * @var ChannelContextInterface
     */
    private $channelContext;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        RequestStack $requestStack,
        LocaleContextInterface $localeContext,
        ChannelContextInterface $channelContext
    )
    {
        $this->productRepository = $productRepository;
        $this->requestStack = $requestStack;
        $this->localeContext = $localeContext;
        $this->channelContext = $channelContext;
    }

    public function __invoke()
    {
        $request = $this->requestStack->getCurrentRequest();
        $term = $request->get('search');
        $locale = $this->localeContext->getLocaleCode();
        $channel = $this->channelContext->getChannel();

        $products = $this->productRepository->searchByTerm($channel, $locale, $term);

        return $products;
    }

}
