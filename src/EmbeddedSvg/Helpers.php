<?php

namespace Milo\EmbeddedSvg;

use DOMDocument;
use Latte\CompileException;
use Latte\Engine;


/** @internal  */
trait Helpers
{
	private function checkRequirements(MacroSetting $setting)
	{
		if (!extension_loaded('dom')) {
			throw new \LogicException('Missing PHP extension xml.');
		} elseif (!is_dir($setting->baseDir)) {
			throw new CompileException("Base directory '$setting->baseDir' does not exist.");
		}
	}


	private function getSvgFilePath(MacroSetting $setting, $file)
	{
		$path = $setting->baseDir . DIRECTORY_SEPARATOR . trim($file, '\'"');
		if (!is_file($path)) {
			throw new CompileException("SVG file '$path' does not exist.");
		}

		return $path;
	}


	private function loadSvgDom(MacroSetting $setting, $path): DOMDocument
	{
		XmlErrorException::try();
		$dom = new DOMDocument('1.0', 'UTF-8');
		$dom->preserveWhiteSpace = false;
		@$dom->load($path, $setting->libXmlOptions);  # @ - triggers warning on empty XML
		if ($e = XmlErrorException::catch()) {
			throw new CompileException(
				"Failed to load SVG content from '$path'.",
				version_compare(Engine::VERSION, '3.0.0', '<') ? 0 : null,
				$e
			);
		}
		foreach ($setting->onLoad as $cb) {
			$cb($dom, $setting);
		}

		if (strtolower($dom->documentElement->nodeName) !== 'svg') {
			throw new CompileException("Sorry, only <svg> (non-prefixed) root element is supported but <{$dom->documentElement->nodeName}> is used. You may open feature request.");
		}

		return $dom;
	}


	private function getSvgTagAttributes(DOMDocument $dom)
	{
		$attributes = [
			'xmlns' => $dom->documentElement->namespaceURI,
		];
		foreach ($dom->documentElement->attributes as $attribute) {
			$attributes[$attribute->name] = $attribute->value;
		}

		return $attributes;
	}


	private function extractInnerXml(MacroSetting $setting, DOMDocument $dom)
	{
		$inner = '';
		$dom->formatOutput = $setting->prettyOutput;
		foreach ($dom->documentElement->childNodes as $childNode) {
			$inner .= $dom->saveXML($childNode);
		}
		return $inner;
	}
}
