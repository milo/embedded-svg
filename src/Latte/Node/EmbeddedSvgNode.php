<?php

declare(strict_types=1);

namespace Milo\EmbeddedSvg\Latte\Node;

use Latte\Compiler\Nodes\Php\Expression\ArrayNode;
use Latte\Compiler\Nodes\Php\Scalar\StringNode;
use Latte\Compiler\Nodes\StatementNode;
use Latte\Compiler\PrintContext;
use Latte\Compiler\Tag;
<<<<<<< HEAD
use Milo\EmbeddedSvg\Exception\CompileException;
<<<<<<< HEAD
use Milo\EmbeddedSvg\MacroSetting;
use Milo\EmbeddedSvg\XmlErrorException;
=======
use Milo\EmbeddedSvg\Configuration\MacroSetting;
use Milo\EmbeddedSvg\Exception\CompileException;
use Milo\EmbeddedSvg\Exception\XmlErrorException;
>>>>>>> dc9f525... fixup! wip
=======
use Milo\EmbeddedSvg\Configuration\MacroSetting;
use Milo\EmbeddedSvg\Exception\XmlErrorException;
>>>>>>> 7b0e01a... wip

/**
 * @see https://github.com/nette/application/commit/7bfe14fd214c728cec1303b7b486b2f1e4dc4c43#diff-4962238ef3db33964744f40410cbdbc9d50b4b1620725ddf6ff34701c64bc51fR25
 *
 * {embeddedSvg "some.svg" [,] [params]}
 *
 * Replacement for @see \Milo\EmbeddedSvg\Macro
 */
final class EmbeddedSvgNode extends StatementNode
{
    private string $svgFilepath;

    private ArrayNode $argsArrayNode;

<<<<<<< HEAD
    public function __construct(Tag $tag, private MacroSetting $macroSetting)
    {
<<<<<<< HEAD
=======
    public function __construct(
        Tag $tag,
        private MacroSetting $macroSetting
    ) {
        // node requires at least 1 argument, the filename
>>>>>>> dc9f525... fixup! wip
=======
        // node requires at least 1 argument, the filename
>>>>>>> 7b0e01a... wip
        $tag->expectArguments();

        $this->svgFilepath = $this->resolveCompleteFilePath($tag, $macroSetting);

        $tag->parser->stream->tryConsume(',');
        $this->argsArrayNode = $tag->parser->parseArguments();
    }

    public function print(PrintContext $context): string
    {
        XmlErrorException::try();
        $domDocument = new \DOMDocument('1.0', 'UTF-8');
        $domDocument->preserveWhiteSpace = false;
        // @ - triggers warning on empty XML
        @$domDocument->load($this->svgFilepath, $this->macroSetting->libXmlOptions);

        $xmlErrorException = XmlErrorException::catch();

        if ($xmlErrorException instanceof XmlErrorException) {
            $errorMessage = sprintf('Failed to load SVG content from "%s"', $this->svgFilepath);
            throw new CompileException($errorMessage, 0, $xmlErrorException);
        }

        foreach ($this->macroSetting->onLoad as $callback) {
            $callback($domDocument, $this->macroSetting);
        }

        if (strtolower($domDocument->documentElement->nodeName) !== 'svg') {
            throw new CompileException("Sorry, only <svg> (non-prefixed) root element is supported but {$domDocument->documentElement->nodeName} is used. You may open feature request.");
        }

        $svgAttributes = [
            'xmlns' => $domDocument->documentElement->namespaceURI,
        ];

        foreach ($domDocument->documentElement->attributes as $attribute) {
            $svgAttributes[$attribute->name] = $attribute->value;
        }

        $inner = '';
        $domDocument->formatOutput = $this->macroSetting->prettyOutput;
        foreach ($domDocument->documentElement->childNodes as $childNode) {
            $inner .= $domDocument->saveXML($childNode);
        }

<<<<<<< HEAD
<<<<<<< HEAD
        $svgAttributes = [];

        return $context->format(
<<<CODE
    printf('<svg "%s"
        			foreach (%0.raw + %1.var as $n => $v) {
        				if ($v === null || $v === false) {
        					continue;
        				} elseif ($v === true) {
        					echo " " . %escape($n);
        				} else {
        					echo " " . %escape($n) . "=\"" . %escape($v) . "\"";
        				}
        			};
        			">" . %2.var . "</svg>";
CODE,
                    $this->argsArrayNode,
                    $this->macroSetting->defaultAttributes + $svgAttributes,
=======
        return $context->format(
            <<<'MACRO_CONTENT'
=======
        return $context->format(<<<'MACRO_CONTENT'
>>>>>>> 7b0e01a... wip
echo "<svg ";
    foreach ([] as $key => $value) {
        if ($value === null || $value === false) {
            continue;
        } elseif ($value === true) {
            echo " " . LR\Filters::escapeHtmlText($key);
        } else {
            echo " " . LR\Filters::escapeHtmlText($key) . "=\"" . LR\Filters::escapeHtmlText($value) . "\"";
        }
      };
echo ">%raw</svg>;"
MACRO_CONTENT,
//                    $this->argsArrayNode->items ,
                    //$this->macroSetting->defaultAttributes + $svgAttributes,
<<<<<<< HEAD
>>>>>>> dc9f525... fixup! wip
=======
>>>>>>> 7b0e01a... wip
                    $inner
        );
    }

    private function resolveCompleteFilePath(Tag $tag, MacroSetting $macroSetting): string
    {
        $filename = $tag->parser->parseUnquotedStringOrExpression();

        if (! $filename instanceof StringNode) {
            throw new CompileException('Missing SVG file path.');
        }

        $absoluteFilename = $macroSetting->baseDir . DIRECTORY_SEPARATOR . $filename->value;

        if (! is_file($absoluteFilename)) {
            $errorMessage = sprintf('SVG file "%s" does not exist.', $absoluteFilename);
            throw new CompileException($errorMessage);
        }

        return $absoluteFilename;
    }
}
