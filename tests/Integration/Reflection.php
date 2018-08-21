<?php
declare(strict_types=1);

namespace App\Tests\Integration;

use ReflectionClass;
use ReflectionException;
use ReflectionProperty;

trait Reflection
{
    /**
     * @return object
     *
     * @throws ReflectionException
     */
    public function instantiateWithoutConstructor(string $className, array $propertyValues = [])
    {
        $class    = new ReflectionClass($className);
        $instance = $class->newInstanceWithoutConstructor();

        foreach ($propertyValues as $propertyName => $value) {
            $this->overridePropertyValue(
                $this->resolveProperty($class, $propertyName),
                $instance,
                $value
            );
        }

        return $instance;
    }

    /**
     * @throws ReflectionException
     */
    private function resolveProperty(ReflectionClass $class, string $propertyName): ReflectionProperty
    {
        try {
            return $class->getProperty($propertyName);
        } catch (ReflectionException $exception) {
            $parentClass = $class->getParentClass();

            if (! $parentClass) {
                throw $exception;
            }

            return $this->resolveProperty($parentClass, $propertyName);
        }
    }

    /**
     * @param object $object
     * @param mixed  $value
     */
    private function overridePropertyValue(ReflectionProperty $property, $object, $value)
    {
        $property->setAccessible(true);
        $property->setValue($object, $value);
    }
}
