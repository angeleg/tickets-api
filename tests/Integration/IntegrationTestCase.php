<?php
declare(strict_types=1);

namespace App\Tests\Integration;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\ResettableContainerInterface;

abstract class IntegrationTestCase extends TestCase
{
    /**
     * @var ResettableContainerInterface
     */
    private static $container;

    /**
     * @var bool
     */
    private static $shouldMockServices = true;

    /**
     * @beforeClass
     */
    public static function createContainer(): void
    {
        if (self::$container !== null) {
            return;
        }

        $kernel = new TestKernel();
        $kernel->boot();

        $container = $kernel->getContainer();

        if (! $container instanceof ResettableContainerInterface) {
            throw new RuntimeException('Test container is supposed to be resettable');
        }

        self::$container = $container;
    }

    /**
     * @after
     */
    public function resetContainer(): void
    {
        $kernel = self::$container->get('kernel');

        self::$container->reset();
        self::$container->set('kernel', $kernel);

        self::$shouldMockServices = true;
    }

    /**
     * @param object $service
     */
    public function overrideService(string $id, $service)
    {
        self::$container->set($id, $service);
    }

    /**
     * @return object
     * @throws ServiceNotFoundException When the service is not defined
     */
    public function getService(string $id)
    {
        return self::$container->get($id);
    }

    /**
     * @return mixed
     */
    public function getParameter(string $id)
    {
        return self::$container->getParameter($id);
    }
}