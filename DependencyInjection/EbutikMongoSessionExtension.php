<?php

namespace Ebutik\MongoSessionBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Alias;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Definition\Processor;

class EbutikMongoSessionExtension extends Extension
{
  public function load(array $configs, ContainerBuilder $container)
  {
    $processor = new Processor();
    $configuration = new Configuration();
    $config = $processor->processConfiguration($configuration, $configs);

    $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
    $loader->load('mongosession.xml');

    $container->setAlias(
        'ebutik.mongosession.document_manager',
        new Alias(sprintf('doctrine.odm.mongodb.%s_document_manager', $config['document_manager']))
    );
  }

  public function getAlias()
  {
    return 'ebutik_mongo_session';
  }

  /**
   * Returns the namespace to be used for this extension (XML namespace).
   *
   * @return string The XML namespace
   */
  public function getNamespace()
  {
      return 'http://developer.e-butik.se/schema/dic/ebutikmongosessionbundle';
  }

  /**
   * @return string
   */
  public function getXsdValidationBasePath()
  {
      return __DIR__.'/../Resources/config/schema';
  }
}
