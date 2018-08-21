<?php
declare(strict_types=1);

namespace App\Tests\Integration;

use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

trait JsonSerialization
{
    /**
     * @return object
     */
    public function fromJson(array $data, string $className, ?DeserializationContext $context = null)
    {
        /**
         * @var SerializerInterface $serializer
        */
        $serializer = $this->getService('jms_serializer');

        return $serializer->deserialize(json_encode((object) $data), $className, 'json', $context);
    }

    /**
     * @param object $object
     */
    public function toJson($object): string
    {
        /**
         * @var SerializerInterface $serializer
        */
        $serializer = $this->getService('jms_serializer');

        return $serializer->serialize($object, 'json');
    }

    /**
     * @return object
     *
     * @throws ServiceNotFoundException When the service is not defined
     */
    abstract public function getService(string $id);

}