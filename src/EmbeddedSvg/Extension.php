<?php

namespace Milo\EmbeddedSvg;

use Latte\Engine;
use Nette\DI\CompilerExtension;
use Nette\DI\Definitions;


class Extension extends CompilerExtension
{
	public function loadConfiguration()
	{
		$definition = $this->getContainerBuilder()->getDefinition('latte.latteFactory');
		if (class_exists(Definitions\FactoryDefinition::class)) { # Nette DI v3 compatibility
			$definition = $definition->getResultDefinition();
		}

		if (version_compare(Engine::VERSION, '3.0.0', '<')) {
			$definition
				->addSetup('?->onCompile[] = function ($engine) { '
					. Macro::class . '::install($engine->getCompiler(), '
					. MacroSetting::class . '::createFromArray(?)'
					. ');}',
					['@self', $this->getConfig()]
				);
		} else {
			$definition
				->addSetup(
					sprintf('?->addExtension(new %s(%s::createFromArray(?)));', LatteExtension::class, MacroSetting::class),
					['@self', $this->getConfig()]
				);
		}
	}
}
