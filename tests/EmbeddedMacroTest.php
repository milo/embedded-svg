<?php

declare(strict_types=1);

namespace Milo\EmbeddedSvg\Tests;

use Latte\Engine;
use Latte\Loaders\StringLoader;
use Nette\Bootstrap\Configurator;
use Nette\Bridges\ApplicationLatte\LatteFactory;
use Nette\DI\Container;
use Nette\Utils\FileSystem;
use Nette\Utils\Strings;
use PHPUnit\Framework\TestCase;

final class EmbeddedMacroTest extends TestCase
{
    private Engine $latteEngine;

    protected function setUp(): void
    {
        $container = $this->createContainerFromConfig(__DIR__ . '/config/test_config.neon');

        /** @var LatteFactory $latteFactory */
        $latteFactory = $container->getByType(LatteFactory::class);
        $this->latteEngine = $latteFactory->create();

        // prepare empty directory for img files
        FileSystem::createDir(__DIR__ . '/../temp/img');
    }

    public function test(): void
    {
        $this->latteEngine->setLoader(new StringLoader());

        // just testing compilatoin works
        $compiledPhpCode = $this->latteEngine->compile('{$value}');

        // use tabs to unite editorconfig
        $compiledPhpCode = Strings::replace($compiledPhpCode, "#\t#", '    ');

        $this->assertStringMatchesFormatFile(__DIR__ . '/Fixture/expected_simple_value.php.inc', $compiledPhpCode);
    }

    private function createContainerFromConfig(string $config): Container
    {
        $configurator = new Configurator();
        $configurator->addConfig($config);
        $configurator->setTempDirectory(__DIR__ . '/../temp');

        return $configurator->createContainer();
    }
}
