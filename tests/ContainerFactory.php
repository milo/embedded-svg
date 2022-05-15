<?php

declare(strict_types=1);

namespace Milo\EmbeddedSvg\Tests;

use Nette\Bootstrap\Configurator;
use Nette\DI\Container;
use Nette\Utils\FileSystem;

final class ContainerFactory
{
    public function createFromConfig(string $config): Container
    {
        $tempDirectory = __DIR__ . '/../temp';

        // clear before factory create to invoke cache rebuild
        FileSystem::delete($tempDirectory);

        $configurator = new Configurator();
        $configurator->addConfig($config);
        $configurator->setTempDirectory($tempDirectory);

        return $configurator->createContainer();
    }
}
