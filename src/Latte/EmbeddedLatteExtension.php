<?php

declare(strict_types=1);

namespace Milo\EmbeddedSvg\Latte;

use Latte\CompileException;
use Latte\Compiler\Node;
use Latte\Compiler\Tag;
use Latte\Extension;
use LogicException;
use Milo\EmbeddedSvg\Configuration\MacroSetting;
use Milo\EmbeddedSvg\Latte\Node\EmbeddedSvgNode;

final class EmbeddedLatteExtension extends Extension
{
    private MacroSetting $macroSetting;

    /**
     * @param array<string, mixed> $configuration
     */
    public function __construct(
        array $configuration,
    ) {
        $this->macroSetting = new MacroSetting($configuration);

        $this->validate($this->macroSetting);
    }

    /**
     * @return array<string, \Closure>
     */
    public function getTags(): array
    {
        // add former "macros" here :)
        // @see https://github.com/nette/application/commit/7bfe14fd214c728cec1303b7b486b2f1e4dc4c43#diff-f478cae07da9b043d8410bf46671215af5c8ffb8bdd430beb395ed8b63e52ffcR54
        return [
            'embeddedSvg' => function (Tag $tag): Node {
                return new EmbeddedSvgNode($tag, $this->macroSetting);
            },
        ];
    }

    private function validate(MacroSetting $macroSetting): void
    {
        if (! extension_loaded('dom')) {
            throw new LogicException('Missing PHP extension xml');
        }

        if (! is_dir($macroSetting->baseDir)) {
            $errorMessage = sprintf('Base directory for SVG images "%s" does not exist', $macroSetting->baseDir);
            throw new CompileException($errorMessage);
        }
    }
}
