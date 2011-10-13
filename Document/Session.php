<?php

namespace Ebutik\MongoSessionBundle\Document;

use Doctrine\Common\Collections\ArrayCollection;

use Ebutik\MongoSessionBundle\Interfaces\SessionEmbeddable;

/**
 * 
 * @mongodb:Document(repositoryClass="Ebutik\MongoSessionBundle\Repository\SessionRepository")
 * @mongodb:HasLifecycleCallbacks
 * @author Magnus Nordlander
 */
class Session
{
  /**
   * @mongodb:Id(strategy="NONE")
   */
  protected $id;

  /**
   * @mongodb:Field(type="date")
   */
  protected $created_at;

  /**
   * @mongodb:Field(type="date")
   */
  protected $accessed_at;

  /**
   * @mongodb:Hash
   */
  protected $scalar_attributes = array();

  /**
   * @mongodb:EmbedMany(targetDocument="Ebutik\MongoSessionBundle\Document\EmbeddableSessionAttributeWrapper")
   */
  protected $embeddable_attributes;

  /**
   * @mongodb:Hash
   */
  protected $serialized_attributes = array();

  /**
   * @author Magnus Nordlander
   **/
  public function __construct()
  {
    $this->generateId();
    $this->embeddable_attributes = new ArrayCollection;
    $this->created_at = new \DateTime();
    $this->updateAccessTime();
  }

  /**
   * @author Magnus Nordlander
   * @mongodb:PostLoad
   **/
  public function updateAccessTime()
  {
    $this->accessed_at = new \DateTime();
  }

  /**
   * @author Magnus Nordlander
   **/
  private function generateId()
  {
    $this->id = mt_rand().uniqid();
  }

  /**
   * @author Magnus Nordlander
   **/
  protected function findEmbeddableAttributeWrapper($key)
  {
    foreach ($this->embeddable_attributes as $wrapper)
    {
      if ($wrapper->getKey() == $key)
      {
        return $wrapper;
      }
    }
    return null;
  }

  /**
   * @author Magnus Nordlander
   **/
  public function getId()
  {
    return $this->id;
  }

  /**
   * @author Magnus Nordlander
   **/
  public function read($key)
  {
    if (isset($this->scalar_attributes[$key]))
    {
      return $this->scalar_attributes[$key];
    }
    else if (isset($this->serialized_attributes[$key]))
    {
      return unserialize($this->scalar_attributes[$key]);
    }
    else if ($wrapper = $this->findEmbeddableAttributeWrapper($key))
    {
      return $wrapper->getAttribute();
    }
    else
    {
      return null;
    }
  }

  /**
   * @author Magnus Nordlander
   **/
  public function readAll()
  {
    return array_merge(
      $this->scalar_attributes,
      array_map('unserialize', $this->serialized_attributes),
      $this->getEmbeddableAttributeArray()
    );
  }

  /**
   * @author Magnus Nordlander
   **/
  protected function getEmbeddableAttributeArray()
  {
    $out = array();
    foreach ($this->embeddable_attributes as $wrapper)
    {
      $out[$wrapper->getKey()] = $wrapper->getAttribute();
    }

    return $out;
  }

  /**
   * @author Magnus Nordlander
   **/
  public function remove($key)
  {
    $retval = null;

    if (isset($this->scalar_attributes[$key]))
    {
      $retval = $this->scalar_attributes[$key];
      unset($this->scalar_attributes[$key]);
    }
    else if (isset($this->serialized_attributes[$key]))
    {
      $retval = unserialize($this->serialized_attributes[$key]);
      unset($this->serialized_attributes[$key]);
    }
    else if ($wrapper = $this->findEmbeddableAttributeWrapper($key))
    {
      $retval = $wrapper->getAttribute();
      $this->embeddable_attributes->removeElement($wrapper);
    }
    else
    {
      return null;
    }

    return $retval;
  }
  
  /**
   * @author Joakim Friberg
   */
  public function clear()
  {
    $this->scalar_attributes = array();
    $this->serialized_attributes = array();
    $this->embeddable_attributes->clear();
  }

  /**
   * @author Magnus Nordlander
   **/
  public function write($key, $data)
  {
    $this->remove($key);

    if ($data instanceOf SessionEmbeddable)
    {
      $this->embeddable_attributes->add(new EmbeddableSessionAttributeWrapper($key, $data));
    }
    else if (is_scalar($data))
    {
      $this->scalar_attributes[$key] = $data;
    }
    else if (is_object($data))
    {
      $this->serialized_attributes[$key] = serialize($data);
    }
    else if (is_array($data))
    {
      if (self::arrayOnlyContainsScalarsRecursive($data))
      {
        $this->scalar_attributes[$key] = $data;
      }
      else
      {
        $this->serialized_attributes[$key] = serialize($data);
      }
    }
    else
    {
      throw new \RuntimeError("Data of type ".gettype($data)." cannot be saved in the session");
    }
  }

  /**
   * @author Magnus Nordlander
   * @see http://www.doctrine-project.org/docs/orm/2.0/en/cookbook/implementing-wakeup-or-clone.html
   **/
  public function __clone()
  {
    // If the entity has an identity, proceed as normal.
    if ($this->id) 
    {
      $this->generateId();
    }
    // otherwise do nothing, do NOT throw an exception!
  }

  /**
   * @author Magnus Nordlander
   **/
  static protected function arrayOnlyContainsScalarsRecursive(array $array)
  {
    $callback = function($reduced, $item) use (&$callback)
    {
      if ($reduced == false)
      {
        return false;
      }
      else if (is_array($item))
      {
        return array_reduce($item, $callback, true);
      }
      else if (is_scalar($item))
      {
        return true;
      }

      return false;
    };

    return array_reduce($array, $callback, true);
  }
}