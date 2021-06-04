<?php

declare(strict_types=1);

namespace DavidRoberto\SyliusExtraApiPlugin\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('davidroberto_sylius_extra_api_plugin');
        $rootNode = $treeBuilder->getRootNode();

        return $treeBuilder;
    }
}
