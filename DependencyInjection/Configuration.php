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
        ->scalarNode('session_prototype_id')->defaultValue('ebutik.mongosession.session.prototype')->end()
        ->booleanNode('strict_request_checking')->defaultValue(false)->end()
        ->scalarNode('purge_probability_divisor')->defaultValue(30)->end() // The probability of a purge. Use n where 1/n is the probability for a given request to purge old sessions. If this is 1, the sessions are purged in every request. If this is say 30, sessions are purged on average every thirtieth request.
      ->end()
    ;

    return $treeBuilder;
  }
}