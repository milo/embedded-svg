<?php

declare(strict_types=1);

namespace Milo\EmbeddedSvg\Latte\Node;

use Latte\Compiler\Nodes\StatementNode;
use Latte\Compiler\PrintContext;

/**
 * @see https://github.com/nette/application/commit/7bfe14fd214c728cec1303b7b486b2f1e4dc4c43#diff-4962238ef3db33964744f40410cbdbc9d50b4b1620725ddf6ff34701c64bc51fR25
 *
 * {embeddedSvg "some.svg" [,] [params]}
 */
final class EmbeddedSvgNode extends StatementNode
{
    public static function create(): static
    {
        return new static();
    }

    public function print(PrintContext $context): string
    {
        dump($context);
        die;
        // ...
    }
}
