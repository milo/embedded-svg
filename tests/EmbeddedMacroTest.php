<?php

declare(strict_types=1);

namespace Milo\EmbeddedSvg\Tests;

use Iterator;
use Latte\Engine;
use Latte\Loaders\StringLoader;
use Nette\Bridges\ApplicationLatte\LatteFactory;
use Nette\Utils\FileSystem;
use Nette\Utils\Finder;
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

    /**
     * @dataProvider provideData()
     */
    public function test(string $inputLatteContent, string $expectedCompiledPhpContent): void
    {
        $compiledPhpCode = $this->latteEngine->compile($inputLatteContent);

        // use tabs to unite editorconfig
        $compiledPhpCode = Strings::replace($compiledPhpCode, "#\t#", '    ');

        $this->assertStringMatchesFormat($expectedCompiledPhpContent, $compiledPhpCode);
    }

    public function provideData(): Iterator
    {
        $finder = Finder::findFiles('*.latte')
            ->in(__DIR__ . '/Fixture');

        /** @var \SplFileInfo[] $fileInfos */
        $fileInfos = iterator_to_array($finder->getIterator());

        foreach ($fileInfos as $fileInfo) {
            $fileContent = FileSystem::read($fileInfo->getRealPath());

            [$inputLatteContent, $expectedCompiledPhpContent] = explode("-----\n", $fileContent);
            yield [$inputLatteContent, $expectedCompiledPhpContent];
        }
    }
}
