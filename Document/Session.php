<?php

namespace Ebutik\MongoSessionBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document(repositoryClass="Ebutik\MongoSessionBundle\Repository\SessionRepository")
 * @MongoDB\ChangeTrackingPolicy("DEFERRED_EXPLICIT") 
 *
 * @author Magnus Nordlander
 */
class Session
{
  /**
   * @MongoDB\Id(strategy="NONE")
   */
  protected $id;

  /**
   * @MongoDB\Field(type="date")
   */
  protected $created_at;

  /**
   * @MongoDB\Field(type="date")
   * @MongoDB\Index
   */
  protected $accessed_at;

  /**
   * @MongoDB\EmbedOne(targetDocument="Ebutik\MongoSessionBundle\Document\SessionAttributeBag")
   */  
  protected $attribute_bag;

  /**
   * @author Magnus Nordlander
   **/
  public function __construct()
  {
    $this->generateId();
    $this->attribute_bag = new SessionAttributeBag();
    $this->created_at = new \DateTime();
    $this->updateAccessTime();
  }

  public function getAttributeBag()
  {
    return $this->attribute_bag;
  }

  /**
   * @author Magnus Nordlander
   * @MongoDB\PostLoad
   **/
  public function updateAccessTime()
  {
    $this->accessed_at = new \DateTime();
  }

  /**
   * @author Magnus Nordlander
   **/
  protected function generateId()
  {
    $this->id = mt_rand().uniqid();
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
   * @see http://www.doctrine-project.org/docs/orm/2.0/en/cookbook/implementing-wakeup-or-clone.html
   **/
  public function __clone()
  {
    // If the entity has an identity, proceed as normal.
    if ($this->id) 
    {
      $this->generateId();

      $this->attribute_bag = clone $this->attribute_bag;
    }
    // otherwise do nothing, do NOT throw an exception!
  }

}
