<?php

namespace Milo\EmbeddedSvg;

use Nette\DI\CompilerExtension;


class Extension extends CompilerExtension
{
	public function loadConfiguration()
	{
		$this->getContainerBuilder()
			->getDefinition('latte.latteFactory')
				->addSetup('?->onCompile[] = function ($engine) { '
					. Macro::class . '::install($engine->getCompiler(), '
					. MacroSetting::class . '::createFromArray(?)'
					. ');}',
					['@self', $this->getConfig()]
				);
	}
}
