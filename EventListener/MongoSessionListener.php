<?php

namespace Ebutik\MongoSessionBundle\EventListener;

use Ebutik\MongoSessionBundle\SessionStorage\MongoODMSessionStorage;

use Doctrine\ODM\MongoDB\DocumentManager;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

use Symfony\Component\HttpFoundation\Cookie;

/**
 * 
 *
 * @author Magnus Nordlander
 */
class MongoSessionListener
{
  private $container;
  private $dm;
  private $options;
  
  /**
   * @author Magnus Nordlander
   **/
  public function __construct($container, DocumentManager $dm, array $options = array())
  {
    $this->container = $container;
    $this->dm = $dm;

    $cookieDefaults = session_get_cookie_params();

    $this->options = array_merge(array(
        'name'          => '_SESS',
        'lifetime'      => 86400,
        'path'          => $cookieDefaults['path'],
        'domain'        => $cookieDefaults['domain'],
        'secure'        => $cookieDefaults['secure'],
        'httponly'      => isset($cookieDefaults['httponly']) ? $cookieDefaults['httponly'] : false,
    ), $options);

    session_name($this->options['name']);
  }
  
  /**
   * @author Magnus Nordlander
   **/
  public function onCoreRequest(GetResponseEvent $event)
  {
    if ($event->getRequestType() == HttpKernelInterface::MASTER_REQUEST)
    {
      $request = $event->getRequest();

      $session_id = null;
      if ($request->cookies->has($this->options['name']))
      {
        $session_id = $request->cookies->get($this->options['name']);
      }

      $session_storage = new MongoODMSessionStorage($this->dm, $this->options, $session_id);

      $this->container->set('ebutik.mongosession.storage', $session_storage);
    }
  }

  public function onCoreResponse(FilterResponseEvent $event)
  {
    if ($event->getRequestType() == HttpKernelInterface::MASTER_REQUEST)
    {
      $session_storage = $this->container->get('ebutik.mongosession.storage');

      if ($session_storage->isStarted())
      {
        $response = $event->getResponse();

        try 
        {
          $session_id = $session_storage->getId();
          $response->headers->setCookie(new Cookie($this->options['name'], 
                                                   $session_id, 
                                                   0, 
                                                   $this->options['path'],
                                                   $this->options['domain'],
                                                   $this->options['secure'],
                                                   $this->options['httponly']));
        }
        catch (\RuntimeException $e)
        {
          // Do nothing
        }
      }
    }
  }  
}
