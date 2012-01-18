<?php

namespace Ebutik\MongoSessionBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class SetPrototypeClassPass implements CompilerPassInterface
{
  /**
   * @see Symfony\Component\DependencyInjection\Compiler.CompilerPassInterface::process()
   */
  public function process(ContainerBuilder $container)
  {
    if (!$container->hasDefinition('ebutik.mongosession.storage')) {
      return;
    }
    
    $storage_def = $container->getDefinition('ebutik.mongosession.storage');
    $args = $storage_def->getArguments();

    $prototype_id = $args[3];

    if (!$container->hasDefinition($prototype_id)) {
      throw new \RuntimeException("MongoDB Session Prototype doesn't exist");
    }

    $prototype_def = $container->getDefinition($prototype_id);
    $args[2] = $prototype_def->getClass();

    $storage_def->setArguments($args);
  }
}