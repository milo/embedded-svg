<?php

declare(strict_types=1);

namespace Milo\EmbeddedSvg\Latte;

use Latte\Extension;
use Milo\EmbeddedSvg\Latte\Node\EmbeddedSvgNode;

final class EmbeddedLatteExtension extends Extension
{
    public function getTags(): array
    {
        // add macros here :)
        // @see https://github.com/nette/application/commit/7bfe14fd214c728cec1303b7b486b2f1e4dc4c43#diff-f478cae07da9b043d8410bf46671215af5c8ffb8bdd430beb395ed8b63e52ffcR54
        return [
            'embeddedSvg' => [EmbeddedSvgNode::class, 'create']
        ];
    }
}
