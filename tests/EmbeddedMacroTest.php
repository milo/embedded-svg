<?php

declare(strict_types=1);

namespace Milo\EmbeddedSvg\Tests;

use Latte\Engine;
use Latte\Loaders\StringLoader;
use Nette\Bridges\ApplicationLatte\LatteFactory;
use Nette\Utils\FileSystem;
use Nette\Utils\Strings;
use PHPUnit\Framework\TestCase;

final class EmbeddedMacroTest extends TestCase
{
    private Engine $latteEngine;

    protected function setUp(): void
    {
        $containerFactory = new ContainerFactory();
        $container = $containerFactory->createFromConfig(__DIR__ . '/config/test_config.neon');

        /** @var LatteFactory $latteFactory */
        $latteFactory = $container->getByType(LatteFactory::class);

        $this->latteEngine = $latteFactory->create();
        $this->latteEngine->setLoader(new StringLoader());

        // prepare empty directory for img files - this parameter is defined in "baseDir" parameter
        // @see tests/config/test_config.neon:5
        FileSystem::createDir(__DIR__ . '/../temp/img');
    }

    public function test(): void
    {
        // just testing compilation works
        $compiledPhpCode = $this->latteEngine->compile('{$value}');

        // use tabs to unite editorconfig
        $compiledPhpCode = Strings::replace($compiledPhpCode, "#\t#", '    ');

        $this->assertStringMatchesFormatFile(__DIR__ . '/Fixture/expected_simple_value.php.inc', $compiledPhpCode);
    }
}
