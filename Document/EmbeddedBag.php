<?php

namespace Ebutik\MongoSessionBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;

/**
 * @MongoDB\EmbeddedDocument
 */
class EmbeddedBag implements AttributeBagInterface
{
   /**
    * @MongoDB\EmbedMany(strategy="set")
    */
    protected $embedded_documents;

    /**
     * Gets this bag's name
     *
     * @return string
     */
    public function getName()
    {
        return 'embedded';
    }

    /**
     * Initializes the Bag
     *
     * @param array $array
     */
    public function initialize(array &$array)
    {

    }

    /**
     * Gets the storage key for this bag.
     *
     * @return string
     */
    public function getStorageKey()
    {
        return 'embedded';
    }

    /**
     * Clears out data from bag.
     *
     * @return mixed Whatever data was contained.
     */
    public function clear()
    {
        // We can't use clear() here, because that issues an $unset, causing a race condition
        foreach ($this->embedded_documents as $key => $value)
        {
          $this->embedded_documents->remove($key);
        }
        // $this->embeddable_attributes->clear();
    }

    /**
     * Checks if an attribute is defined.
     *
     * @param string $name The attribute name
     *
     * @return Boolean true if the attribute is defined, false otherwise
     */
    public function has($name)
    {
        return $this->embedded_documents->containsKey($name);
    }

    /**
     * Returns an attribute.
     *
     * @param string $name    The attribute name
     * @param mixed  $default The default value if not found.
     *
     * @return mixed
     */
    public function get($name, $default = null)
    {
        if ($document = $this->embedded_documents->get($name)) {
            return $document;
        }

        return $default;
    }

    /**
     * Sets an attribute.
     *
     * @param string $name
     * @param mixed  $value
     */
    public function set($name, $value)
    {
        $this->embedded_documents->set($name, $value);
    }

    /**
     * Returns attributes.
     *
     * @return array Attributes
     */
    public function all()
    {
        return $this->embedded_documents->toArray();
    }

    /**
     * Sets attributes.
     *
     * @param array $attributes Attributes
     */
    public function replace(array $attributes)
    {
        foreach ($attributes as $key => $value) {
            $this->set($key, $value);
        }
    }

    /**
     * Removes an attribute.
     *
     * @param string $name
     *
     * @return mixed The removed value
     */
    public function remove($name)
    {
        return $this->embedded_documents->remove($name);
    }
}