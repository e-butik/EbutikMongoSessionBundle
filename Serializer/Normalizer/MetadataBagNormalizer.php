<?php

namespace Ebutik\MongoSessionBundle\Serializer\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\HttpFoundation\Session\Storage\MetadataBag;

class MetadataBagNormalizer implements NormalizerInterface, DenormalizerInterface
{
    protected $metaReflProperty;
    protected $lastUsedProperty;

    public function __construct()
    {
        $this->metaReflProperty = new \ReflectionProperty(
            'Symfony\Component\HttpFoundation\Session\Storage\MetadataBag',
            'meta'
        );
        $this->metaReflProperty->setAccessible(true);

        $this->lastUsedProperty = new \ReflectionProperty(
            'Symfony\Component\HttpFoundation\Session\Storage\MetadataBag',
            'lastUsed'
        );
        $this->lastUsedProperty->setAccessible(true);
    }

    /**
     * Normalizes an object into a set of arrays/scalars
     *
     * @param object $object object to normalize
     * @param string $format format the normalization result will be encoded as
     * @param array $context Context options for the normalizer
     *
     * @return array|scalar
     */
    public function normalize($object, $format = null, array $context = array())
    {
        return [
            'name' => $object->getName(),
            'storageKey' => $object->getStorageKey(),
            'meta' => $this->metaReflProperty->getValue($object),
            'lastUsed' => $object->getLastUsed(),
        ];
    }

    /**
     * Denormalizes data back into an object of the given class
     *
     * @param mixed  $data   data to restore
     * @param string $class  the expected class to instantiate
     * @param string $format format the given data was extracted from
     * @param array  $context options available to the denormalizer
     *
     * @return object
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        $bag = new MetadataBag($data['storageKey']);
        $bag->setName($data['name']);
        $this->metaReflProperty->setValue($bag, $data['meta']);
        $this->lastUsedProperty->setValue($bag, $data['lastUsed']);

        return $bag;
    }

    /**
     * Checks whether the given class is supported for normalization by this normalizer
     *
     * @param mixed  $data   Data to normalize.
     * @param string $format The format being (de-)serialized from or into.
     *
     * @return Boolean
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof MetadataBag;
    }

    /**
     * Checks whether the given class is supported for denormalization by this normalizer
     *
     * @param mixed  $data   Data to denormalize from.
     * @param string $type   The class to which the data should be denormalized.
     * @param string $format The format being deserialized from.
     *
     * @return Boolean
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return $type == 'Symfony\Component\HttpFoundation\Session\Storage\MetadataBag';
    }
}