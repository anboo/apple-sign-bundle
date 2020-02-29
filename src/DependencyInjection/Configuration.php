<?php


namespace Anboo\AppleSign\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $tree = new TreeBuilder('anboo_apple_sign');
        $rootNode = $tree->getRootNode();

        return $tree;
    }
}
