<?php

declare(strict_types=1);

namespace Milo\EmbeddedSvg\Tests;

use Iterator;
use Latte\Engine;
use Latte\Loaders\StringLoader;
use Nette\Bridges\ApplicationLatte\LatteFactory;
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

        $compiledPhpCode = $this->compileLatteToPhpContent($inputLatteContent);

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

    private function compileLatteToPhpContent(string $latteContent): string
    {
        //  string $inputLatteContent, string $expectedCompiledPhpContent
        $compiledPhpCode = $this->latteEngine->compile($latteContent);

        // use spaces to unite all files via .editorconfig
        return Strings::replace($compiledPhpCode, "#\t#", '    ');
    }
}
