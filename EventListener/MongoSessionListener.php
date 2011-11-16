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
  private $storage;
  
  /**
   * @author Magnus Nordlander
   **/
  public function __construct(MongoODMSessionStorage $storage)
  {
    $this->storage = $storage;
  }
  
  /**
   * @author Magnus Nordlander
   **/
  public function onKernelRequest(GetResponseEvent $event)
  {
    if ($event->getRequestType() == HttpKernelInterface::MASTER_REQUEST)
    {
      $request = $event->getRequest();

      $options = $this->storage->getOptions();

      $session_id = false;
      if ($request->cookies->has($options['name']))
      {
        $session_id = $request->cookies->get($options['name']);
      }

      $this->storage->setRequestSessionId($session_id);
    }
  }

  public function onKernelResponse(FilterResponseEvent $event)
  {
    if ($event->getRequestType() == HttpKernelInterface::MASTER_REQUEST)
    {
      if ($this->storage->isStarted())
      {
        $response = $event->getResponse();

        try 
        {
          $session_id = $this->storage->getId();
          $options = $this->storage->getOptions();

          $response->headers->setCookie(new Cookie($options['name'], 
                                                   $session_id, 
                                                   0, 
                                                   $options['path'],
                                                   $options['domain'],
                                                   $options['secure'],
                                                   $options['httponly']));
        }
        catch (\RuntimeException $e)
        {
          // Do nothing
        }
      }
    }
  }  
}
