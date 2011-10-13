<?php

namespace Ebutik\MongoSessionBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
  public function getConfigTreeBuilder()
  {
    $treeBuilder = new TreeBuilder();
    $rootNode = $treeBuilder->root('ebutik_mongo_session');

    $rootNode
      ->children()
        ->scalarNode('document_manager')->defaultValue('default')->end()
        ->booleanNode('strict_request_checking')->defaultValue(false)->end()
      ->end()
    ;

    return $treeBuilder;
  }
}