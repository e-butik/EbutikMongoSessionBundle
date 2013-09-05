<?php

namespace Ebutik\MongoSessionBundle\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

use Symfony\Component\HttpFoundation\Session\Storage\MetadataBag;
use Symfony\Component\Serializer\Serializer;

use Ebutik\MongoSessionBundle\Interfaces\SessionEmbeddable;
use Ebutik\MongoSessionBundle\Serializer\Normalizer;

/**
 *
 * @MongoDB\Document(repositoryClass="Ebutik\MongoSessionBundle\Repository\SessionRepository")
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
   * @MongoDB\EmbedOne()
   */
  protected $embedded_bag;

  /**
   * @MongoDB\Field(type="hash")
   */
  protected $metadata_bag_snapshot;

  /**
   * @MongoDB\Field(type="hash")
   */
  protected $other_bag_snapshots = [];

  protected $serializer;

  /**
   * @author Magnus Nordlander
   **/
  public function __construct()
  {
    $this->serializer = new Serializer([
      new Normalizer\MetadataBagNormalizer(),
      new Normalizer\AttributeBagNormalizer(),
      new Normalizer\FlashBagNormalizer(),
    ]);

    $this->generateId();
    $this->embedded_bag = new EmbeddedBag();
    $this->setMetadataBagSnapshot(new MetadataBag());
  }

  public function getEmbeddedBag()
  {
    return $this->embedded_bag;
  }

  public function getMetadataBagSnapshot()
  {
    return $this->serializer
      ->denormalize(
        $this->metadata_bag_snapshot,
        'Symfony\Component\HttpFoundation\Session\Storage\MetadataBag'
      );
  }

  public function setMetadataBagSnapshot(MetadataBag $bag)
  {
    $this->metadata_bag_snapshot = $this->metadata_bag_normalizer->normalize($bag);
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

      $new_array = new ArrayCollection;
      foreach( $this->embeddable_attributes as $key => $data ) {
        $new_array[$key] = clone $data;
      }
      $this->embeddable_attributes = $new_array;

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
