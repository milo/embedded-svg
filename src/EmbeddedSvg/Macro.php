<?php

namespace Milo\EmbeddedSvg;

use DOMDocument;
use Latte\CompileException;
use Latte\Compiler;
use Latte\MacroNode;
use Latte\Macros\MacroSet;
use Latte\PhpWriter;


class Macro extends MacroSet
{
	use Helpers;

	private $setting;


	public function __construct(Compiler $compiler, MacroSetting $setting)
	{
		$this->checkRequirements($setting);

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

		$path = $this->getSvgFilePath($this->setting, $file);
		$dom = $this->loadSvgDom($this->setting, $path);
		$svgAttributes = $this->getSvgTagAttributes($dom);
		$inner = $this->extractInnerXml($this->setting, $dom);

		$macroAttributes = $writer->formatArray();

		return $writer->write('
			echo "<svg";
			foreach (%0.raw + %1.var as $__n => $__v) {
				if ($__v === null || $__v === false) {
					continue;
				} elseif ($__v === true) {
					echo " " . %escape($__n);
				} else {
					echo " " . %escape($__n) . "=\"" . %escape($__v) . "\"";
				}
			}
			unset($__n, $__v);
			echo ">" . %2.var . "</svg>";
			',
			$macroAttributes, $this->setting->defaultAttributes + $svgAttributes, $inner
		);
	}
}
