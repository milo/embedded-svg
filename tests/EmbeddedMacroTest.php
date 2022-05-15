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
use Symplify\EasyTesting\DataProvider\StaticFixtureFinder;
use Symplify\EasyTesting\DataProvider\StaticFixtureUpdater;
use Symplify\EasyTesting\StaticFixtureSplitter;
use Symplify\SmartFileSystem\SmartFileInfo;

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
    }

    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fixtureFileInfo): void
    {
        $inputAndExpected = StaticFixtureSplitter::splitFileInfoToInputAndExpected($fixtureFileInfo);
        $inputLatteContent = $inputAndExpected->getInput();
        $expectedCompiledPhpContent = $inputAndExpected->getExpected();

        //  string $inputLatteContent, string $expectedCompiledPhpContent
        $compiledPhpCode = $this->latteEngine->compile($inputLatteContent);

        // use tabs to unite editorconfig
        $compiledPhpCode = Strings::replace($compiledPhpCode, "#\t#", '    ');

        // update tests on change
        StaticFixtureUpdater::updateFixtureContent(
            $inputLatteContent,
            $compiledPhpCode,
            $fixtureFileInfo
        );

        $this->assertStringMatchesFormat($expectedCompiledPhpContent, $compiledPhpCode);
    }

    public function provideData(): Iterator
    {
        // @see https://github.com/symplify/easy-testing
        // @see https://tomasvotruba.com/blog/2020/07/20/how-to-update-hundreds-of-test-fixtures-with-single-phpunit-run/
        return StaticFixtureFinder::yieldDirectory(__DIR__ . '/Fixture', '*.latte');
    }
}
