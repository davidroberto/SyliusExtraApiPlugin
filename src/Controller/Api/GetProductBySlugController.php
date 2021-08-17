<?php


namespace DavidRoberto\SyliusExtraApiPlugin\Controller\Api;

use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Repository\ProductRepositoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class GetProductBySlugController
{

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ChannelContextInterface
     */
    private $channelContext;
    /**
     * @var RequestStack
     */
    private $requestStack;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        ChannelContextInterface  $channelContext,
        RequestStack $requestStack
    ) {
        $this->productRepository = $productRepository;
        $this->channelContext = $channelContext;
        $this->requestStack = $requestStack;
    }

    public function __invoke() {
        $request = $this->requestStack->getCurrentRequest();
        $productSlug = $request->get('code');
        $locale = $request->get('localeCode');
        $channel = $this->channelContext->getChannel();
        return $this->productRepository->findOneByChannelAndSlug($channel, $locale, $productSlug);
    }

}
