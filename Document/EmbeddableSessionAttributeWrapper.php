<?php

namespace Ebutik\MongoSessionBundle\Document;

use Ebutik\MongoSessionBundle\Interfaces\SessionEmbeddable;

/**
 * 
 * @mongodb:EmbeddedDocument
 * @author Magnus Nordlander
 */
class EmbeddableSessionAttributeWrapper
{
  /**
   * @mongodb:String
   */
  protected $key;

  /**
   * @mongodb:EmbedOne
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