<?php

namespace Ebutik\MongoSessionBundle\Document;

use Ebutik\MongoSessionBundle\Collection\FlatteningParameterBag;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

use Doctrine\Common\Collections\ArrayCollection;

use Ebutik\MongoSessionBundle\Interfaces\SessionEmbeddable;

/**
 * @MongoDB\EmbeddedDocument
 */
class SessionAttributeBag extends FlatteningParameterBag
{
  /**
   * @MongoDB\Id
   * 
   * This attribute isn't REALLY needed, however, it's nice, because it makes things 
   * easier in __clone, and it allows us to work around MODM-160.
   */
  protected $id;

  /**
   * @MongoDB\Hash
   */
  protected $scalar_attributes = array();

  /**
   * @MongoDB\Hash
   */
  protected $serialized_attributes = array();

  /**
   * @MongoDB\EmbedMany(targetDocument="Ebutik\MongoSessionBundle\Document\EmbeddableSessionAttributeWrapper")
   */
  protected $embeddable_attributes;

  public function __construct()
  {
    $this->embeddable_attributes = new ArrayCollection();
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
      $new_embeddables = new ArrayCollection;
      foreach( $this->embeddable_attributes as $key => $wrapper ) {
        $new_embeddables->add(new EmbeddableSessionAttributeWrapper($wrapper->getKey(), clone $wrapper->getAttribute()));
      }
      $this->embeddable_attributes = $new_embeddables;
    }
    // otherwise do nothing, do NOT throw an exception!
  }

  protected function clear()
  {
    $this->scalar_attributes = array();
    $this->serialized_attributes = array();
    $this->embeddable_attributes->clear();
  }

  protected function _write(array $data)
  {
    $this->clear();

    foreach ($data as $key => $subdata)
    {
      if ($subdata instanceOf SessionEmbeddable)
      {
        $this->embeddable_attributes->add(new EmbeddableSessionAttributeWrapper($key, $subdata));
      }
      else if (is_scalar($subdata) || $subdata === null)
      {
        $this->scalar_attributes[$key] = $subdata;
      }
      else if (is_object($subdata))
      {
        $this->serialized_attributes[$key] = serialize($subdata);
      }
      else
      {
        throw new \RuntimeError("Data of type ".gettype($subdata)." cannot be saved in the session");
      }
    }
  }

  protected function getKeyValueArrayForEmbeddedObjects()
  {
    $array = array();

    foreach ($this->embeddable_attributes as $wrapper) 
    {
      $array[$wrapper->getKey()] = $wrapper->getAttribute();
    }

    return $array;
  }

  protected function _read()
  {
    return array_merge(
      $this->scalar_attributes,
      array_map('unserialize', $this->serialized_attributes),
      $this->getKeyValueArrayForEmbeddedObjects()
    );
  }

  protected function createEscaper()
  {
    $escaper = parent::createEscaper();
    $escaper->setEscapingMap(array('/' => '%', '.' => ':'));

    return $escaper;
  }
}