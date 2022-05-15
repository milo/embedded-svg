<?php

declare(strict_types=1);

namespace Milo\EmbeddedSvg\Tests;

use Nette\Bootstrap\Configurator;
use Nette\DI\Container;

final class ContainerFactory
{
    public function createFromConfig(string $config): Container
    {
        $configurator = new Configurator();
        $configurator->addConfig($config);
        $configurator->setTempDirectory(__DIR__ . '/../temp');

        return $configurator->createContainer();
    }
}
