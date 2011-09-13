<?php

namespace Ebutik\MongoSessionBundle\SessionStorage;

use Symfony\Component\HttpFoundation\SessionStorage\SessionStorageInterface;

use Ebutik\MongoSessionBundle\Document\Session;

use Doctrine\ODM\MongoDB\DocumentManager;

/**
* @author Magnus Nordlander
**/
class MongoODMSessionStorage implements SessionStorageInterface
{
  protected $dm;

  protected $session = null;

  protected $request_session_id;

  /**
   * @author Magnus Nordlander
   **/
  public function __construct(DocumentManager $dm, $request_session_id = null)
  {
    $this->dm = $dm;

    $this->request_session_id = $request_session_id;
  }

  /**
   * @author Magnus Nordlander
   **/
  public function __destruct()
  {
    $this->flush();
  }

  /**
   * Starts the session.
   */
  public function start()
  {
    if ($this->session == null)
    {
      $this->dm->getRepository('Ebutik\MongoSessionBundle\Document\Session')->purgeBefore(new \DateTime("-14 day"));

      if ($this->request_session_id)
      {
        $this->session = $this->dm->find('Ebutik\MongoSessionBundle\Document\Session', $this->request_session_id);
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

    return $this->session->readAll();
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

    foreach ($data as $subkey => $value) 
    {
      $this->session->write($subkey, $value);
    }
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