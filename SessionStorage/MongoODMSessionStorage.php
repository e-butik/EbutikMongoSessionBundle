<?php

namespace Ebutik\MongoSessionBundle\SessionStorage;

use Symfony\Component\HttpFoundation\SessionStorage\SessionStorageInterface;

use Ebutik\MongoSessionBundle\Document\Session;
use Ebutik\MongoSessionBundle\Escaper\EscaperInterface;

use Doctrine\ODM\MongoDB\DocumentManager;

/**
* @author Magnus Nordlander
**/
class MongoODMSessionStorage implements SessionStorageInterface
{
  /**
   * @var DocumentManager
   */
  protected $dm;

  /**
   * @var EscaperInterface
   */
  protected $key_escaper;

  /**
   * @var array
   */
  protected $options;

  // NULL represents unset, false represents set, but with no session id
  protected $request_session_id;

  /**
   * @var boolean
   */
  protected $strict_request_checking;

  /**
   * @var Session|null
   */
  protected $session = null;

  /**
   * @author Magnus Nordlander
   **/
  public function __construct(DocumentManager $dm, EscaperInterface $key_escaper, array $options, $strict_request_checking = false)
  {
    $this->dm = $dm;

    $this->key_escaper = $key_escaper;

    $this->setOptions($options);

    $this->strict_request_checking = (bool)$strict_request_checking;
  }

  public function setRequestSessionId($request_session_id = false)
  {
    $this->request_session_id = $request_session_id;
  }

  protected function setOptions(array $options)
  {
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

  public function getOptions()
  {
    return $this->options;
  }

  /**
   * @author Magnus Nordlander
   **/
  public function isStarted()
  {
    return $this->session != null;
  }

  public function getRequestSessionId()
  {
    /* 
     * NativeSessionStorage cheats by using PHP methods that look at request cookies without using the request object.
     * We need to be able to cheat like that too, but we only do it if the user hasn't requested that we're strict
     * about the session id having to come from the request object.
     */
    if ($this->request_session_id === null && $this->strict_request_checking)
    {
      throw new \RuntimeException("A Mongo session cannot be started unless MongoODMSessionStorage::setRequestSessionId() has been called. If there is no current session, you still have to call the method, but with false as the argument.");
    }
    else if ($this->request_session_id === null && !$this->strict_request_checking)
    {
      if (isset($_COOKIE[$this->options['name']]))
      {
        $this->request_session_id = $_COOKIE[$this->options['name']];
      }
      else
      {
        $this->request_session_id = false;
      }
    }

    return $this->request_session_id;
  }

  /**
   * Starts the session.
   */
  public function start()
  {
    if (!$this->isStarted())
    {
      $this->dm->getRepository('Ebutik\MongoSessionBundle\Document\Session')->purgeBefore(new \DateTime("-".$this->options['lifetime'].' second'));

      if ($this->getRequestSessionId())
      {
        $this->session = $this->dm->find('Ebutik\MongoSessionBundle\Document\Session', $this->getRequestSessionId());
      }

      if (!$this->session)
      {
        $this->session = new Session;
      }
    }
  }

  /**
   * Returns the session ID
   *
   * @return mixed  The session ID
   *
   * @throws \RuntimeException If the session was not started yet
   */
  public function getId()
  {
    if (!$this->session)
    {
      throw new \RuntimeException("The session is not started yet.");
    }

    return $this->session->getId();
  }

  /**
   * Reads data from this storage.
   *
   * The preferred format for a key is directory style so naming conflicts can be avoided.
   *
   * @param  string $key  A unique key identifying your data
   *
   * @return mixed Data associated with the key
   *
   * @throws \RuntimeException If an error occurs while reading data from this storage
   */
  public function read($key)
  {
    if (!$this->session)
    {
      throw new \RuntimeException("The session is not started yet.");
    }

    if ($key != '_symfony2')
    {
      throw new \RuntimeException("This storage only stores Symfony2 data");
    }

    $attributes = array();
    foreach($this->session->readAll() as $attribute_key => $attribute_value)
    {
      $attributes[$this->key_escaper->unescape($attribute_key)] = $attribute_value;
    }

    return $attributes;
  }

  /**
   * Removes data from this storage.
   *
   * The preferred format for a key is directory style so naming conflicts can be avoided.
   *
   * @param  string $key  A unique key identifying your data
   *
   * @return mixed Data associated with the key
   *
   * @throws \RuntimeException If an error occurs while removing data from this storage
   */
  public function remove($key)
  {
    if (!$this->session)
    {
      throw new \RuntimeException("The session is not started yet.");
    }

    if ($key != '_symfony2')
    {
      throw new \RuntimeException("This storage only stores Symfony2 data");
    }

    foreach ($data as $subkey) 
    {
      $this->session->remove($subkey);
    }
  }

  /**
   * Writes data to this storage.
   *
   * The preferred format for a key is directory style so naming conflicts can be avoided.
   *
   * @param  string $key   A unique key identifying your data
   * @param  mixed  $data  Data associated with your key
   *
   * @throws \RuntimeException If an error occurs while writing to this storage
   */
  public function write($key, $data)
  {
    if (!$this->session)
    {
      throw new \RuntimeException("The session is not started yet.");
    }

    if ($key != '_symfony2')
    {
      throw new \RuntimeException("This storage only stores Symfony2 data");
    }
    
    $this->session->clear();

    foreach ($data as $subkey => $value) 
    {
      $this->session->write($this->key_escaper->escape($subkey), $value);
    }
    
    $this->flush();
  }

  /**
   * Regenerates id that represents this storage.
   *
   * @param  Boolean $destroy Destroy session when regenerating?
   *
   * @return Boolean True if session regenerated, false if error
   *
   * @throws \RuntimeException If an error occurs while regenerating this storage
   */
  public function regenerate($destroy = false)
  {
    if (!$this->session)
    {
      throw new \RuntimeException("The session is not started yet.");
    }

    $old_session = $this->session;

    $this->session = clone $old_session;

    if ($destroy && $this->dm->contains($old_session))
    {
      $this->dm->remove($old_session);
    }
  }

  /**
   * @author Magnus Nordlander
   **/
  public function flush()
  {
    if ($this->session)
    {
      $this->dm->persist($this->session);
      $this->dm->flush();
    }
  }
}