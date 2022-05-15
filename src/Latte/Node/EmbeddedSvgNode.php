<?php

declare(strict_types=1);

namespace Milo\EmbeddedSvg\Latte\Node;

use Latte\Compiler\Nodes\Php\Expression\ArrayNode;
use Latte\Compiler\Nodes\Php\Scalar\StringNode;
use Latte\Compiler\Nodes\StatementNode;
use Latte\Compiler\PrintContext;
use Latte\Compiler\Tag;
use Milo\EmbeddedSvg\Configuration\MacroSetting;
use Milo\EmbeddedSvg\Exception\CompileException;
use Milo\EmbeddedSvg\Exception\XmlErrorException;

/**
 * @see https://github.com/nette/application/commit/7bfe14fd214c728cec1303b7b486b2f1e4dc4c43#diff-4962238ef3db33964744f40410cbdbc9d50b4b1620725ddf6ff34701c64bc51fR25
 *
 * {embeddedSvg "some.svg" [,] [params]}
 */
final class EmbeddedSvgNode extends StatementNode
{
    private string $svgFilepath;

    private ArrayNode $argsArrayNode;

    public function __construct(
        Tag $tag,
        private MacroSetting $macroSetting
    ) {
        // node requires at least 1 argument, the filename
        $tag->expectArguments();

        $this->svgFilepath = $this->resolveCompleteFilePath($tag, $macroSetting);

        $tag->parser->stream->tryConsume(',');
        $this->argsArrayNode = $tag->parser->parseArguments();
    }

    public function print(PrintContext $context): string
    {
        // @todo extract to own XML factory
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

        /** @var \DOMElement $documentElement */
        $documentElement = $domDocument->documentElement;

        if (strtolower($documentElement->nodeName) !== 'svg') {
            throw new CompileException("Sorry, only <svg> (non-prefixed) root element is supported but {$domDocument->documentElement->nodeName} is used. You may open feature request.");
        }

        $svgAttributes = [
            'xmlns' => $documentElement->namespaceURI,
        ];

        foreach ($documentElement->attributes as $attribute) {
            $svgAttributes[$attribute->name] = $attribute->value;
        }

        $innerSvgContent = '';
        $domDocument->formatOutput = $this->macroSetting->prettyOutput;
        foreach ($documentElement->childNodes as $childNode) {
            $innerSvgContent .= $domDocument->saveXML($childNode);
        }

        $svgAttributes += $this->macroSetting->defaultAttributes;

        // there might be better way to do this, but could not find it yet
        $staticArgumentsString = $this->createStaticArgumentString($svgAttributes);

        return $context->format(
            <<<'MACRO_CONTENT'
echo "<svg";
    foreach (%raw + %raw as $key => $value) {
        if ($value === null || $value === false) {
            continue;
        } elseif ($value === true) {
            echo " " . LR\Filters::escapeHtmlText($key);
        } else {
            echo " " . LR\Filters::escapeHtmlText($key) . "=\"" . LR\Filters::escapeHtmlText($value) . "\"";
        }
      };
echo ">%raw</svg>";
MACRO_CONTENT,
            $this->argsArrayNode,
            $staticArgumentsString,
            $innerSvgContent
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

    /**
     * @param array<string, mixed> $svgAttributes
     */
    private function createStaticArgumentString(array $svgAttributes): string
    {
        $staticArguments = $this->macroSetting->defaultAttributes + $svgAttributes;

        $staticArgumentsString = '[';
        foreach ($staticArguments as $key => $value) {
            $staticArgumentsString .= "'" . $key . "' => '" . $value . "', ";
        }

        $staticArgumentsString .= ']';

        return $staticArgumentsString;
    }
}
