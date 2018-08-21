<?php
declare(strict_types=1);

namespace App\Tests\Integration;

use App\Kernel;
use ReflectionClass;
use Symfony\Component\Config\ConfigCache;

final class TestKernel extends Kernel
{
    public function __construct()
    {
        parent::__construct('test', true);
    }

    /**
     * Modified version so that we skip cache warming
     */
    protected function initializeContainer()
    {
        $class = $this->getContainerClass();
        $cache = new ConfigCache($this->getCacheDir() . '/' . $class . '.php', (bool) $this->debug);

        if (!$cache->isFresh()) {
            $container = $this->buildContainer();
            $container->compile();
            $this->dumpContainer($cache, $container, $class, $this->getContainerBaseClass());
        }

        include_once $cache->getPath();

        $this->container = new $class();
        $this->container->set('kernel', $this);
    }

    /**
     * Modified version so that we keep the test cache dir together with development
     */
    public function getRootDir()
    {
        if (null === $this->rootDir) {
            $this->rootDir = dirname(
                (new ReflectionClass(Kernel::class))->getFileName()
            );
        }

        return $this->rootDir;
    }

}