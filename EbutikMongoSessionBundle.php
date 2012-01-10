<?php

namespace Ebutik\MongoSessionBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

use Ebutik\MongoSessionBundle\DependencyInjection\Compiler\SetPrototypeClassPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class EbutikMongoSessionBundle extends Bundle
{
  public function build(ContainerBuilder $container)
  {
    parent::build($container);

    $container->addCompilerPass(new SetPrototypeClassPass());
  }
}
