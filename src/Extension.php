<?php

declare(strict_types=1);

namespace Milo\EmbeddedSvg;

use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\FactoryDefinition;
use Nette\DI\Definitions\ServiceDefinition;

class Extension extends CompilerExtension
{
    public function loadConfiguration()
    {
        $definition = $this->getContainerBuilder()->getDefinition('latte.latteFactory');
        if ($definition instanceof FactoryDefinition) {
            $definition = $definition->getResultDefinition();
        }

        if (! $definition instanceof ServiceDefinition) {
            return;
        }

        $definition
            ->addSetup(
                '?->onCompile[] = function ($engine) { '
                . Macro::class . '::install($engine->getCompiler(), '
                . MacroSetting::class . '::createFromArray(?)'
                . ');}',
                ['@self', $this->getConfig()]
            );
    }
}
