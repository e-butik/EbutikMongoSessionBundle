<?php

namespace Ebutik\MongoSessionBundle\Serializer\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;

class AttributeBagNormalizer implements NormalizerInterface, DenormalizerInterface
{
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
            'attributes' => $object->all(),
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
        $bag = new AttributeBag($data['storageKey']);
        $bag->setName($data['name']);
        $bag->initialize($data['attributes']);

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
        return $data instanceof AttributeBag;
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
        return $type == 'Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag';
    }
}