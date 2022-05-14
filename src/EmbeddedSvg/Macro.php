<?php

declare(strict_types=1);

namespace Milo\EmbeddedSvg;

use DOMDocument;
use Latte\CompileException;
use Latte\Compiler;
use Latte\MacroNode;
use Latte\Macros\MacroSet;
use Latte\PhpWriter;

class Macro extends MacroSet
{
    private $setting;

    public function __construct(Compiler $compiler, MacroSetting $setting)
    {
        if (! extension_loaded('dom')) {
            throw new \LogicException('Missing PHP extension xml.');
        } elseif (! is_dir($setting->baseDir)) {
            throw new CompileException("Base directory '{$setting->baseDir}' does not exist.");
        }

        parent::__construct($compiler);
        $this->setting = $setting;
    }

    public static function install(Compiler $compiler, MacroSetting $setting)
    {
        $me = new static($compiler, $setting);
        $me->addMacro($setting->macroName, [$me, 'open']);
    }

    public function open(MacroNode $node, PhpWriter $writer)
    {
        $file = $node->tokenizer->fetchWord();
        if ($file === false) {
            throw new CompileException('Missing SVG file path.');
        }

        $path = $this->setting->baseDir . DIRECTORY_SEPARATOR . trim($file, '\'"');
        if (! is_file($path)) {
            throw new CompileException("SVG file '${path}' does not exist.");
        }

        XmlErrorException::try();
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        @$dom->load($path, $this->setting->libXmlOptions);  # @ - triggers warning on empty XML
        if ($e = XmlErrorException::catch()) {
            throw new CompileException("Failed to load SVG content from '${path}'.", 0, $e);
        }
        foreach ($this->setting->onLoad as $cb) {
            $cb($dom, $this->setting);
        }

        if (strtolower($dom->documentElement->nodeName) !== 'svg') {
            throw new CompileException("Sorry, only <svg> (non-prefixed) root element is supported but {$dom->documentElement->nodeName} is used. You may open feature request.");
        }

        $macroAttributes = $writer->formatArray();
        $svgAttributes = [
            'xmlns' => $dom->documentElement->namespaceURI,
        ];
        foreach ($dom->documentElement->attributes as $attribute) {
            $svgAttributes[$attribute->name] = $attribute->value;
        }

        $inner = '';
        $dom->formatOutput = $this->setting->prettyOutput;
        foreach ($dom->documentElement->childNodes as $childNode) {
            $inner .= $dom->saveXML($childNode);
        }

        return $writer->write(
            '
			echo "<svg";
			foreach (%0.raw + %1.var as $n => $v) {
				if ($v === null || $v === false) {
					continue;
				} elseif ($v === true) {
					echo " " . %escape($n);
				} else {
					echo " " . %escape($n) . "=\"" . %escape($v) . "\"";
				}
			};
			echo ">" . %2.var . "</svg>";
			',
            $macroAttributes,
            $this->setting->defaultAttributes + $svgAttributes,
            $inner
        );
    }
}
