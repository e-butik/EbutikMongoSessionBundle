<?php

namespace Ebutik\MongoSessionBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Ebutik\MongoSessionBundle\Interfaces\SessionEmbeddable;

/**
 * 
 * @MongoDB\EmbeddedDocument
 * @author Magnus Nordlander
 */
class EmbeddableSessionAttributeWrapper
{
  /**
   * @MongoDB\String
   */
  protected $key;

  /**
   * @MongoDB\EmbedOne
   */
  protected $attribute;

  /**
   * @author Magnus Nordlander
   **/
  public function __construct($key, SessionEmbeddable $attribute)
  {
    $this->key = $key;
    $this->attribute = $attribute;
  }

  public function setKey($key)
  {
    $this->key = $key;
  }

  /**
   * @author Magnus Nordlander
   **/
  public function getKey()
  {
    return $this->key;
  }

  /**
   * @author Magnus Nordlander
   **/
  public function getAttribute()
  {
    return $this->attribute;
  }
}