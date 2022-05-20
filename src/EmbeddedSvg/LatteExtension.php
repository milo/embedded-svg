<?php

namespace Milo\EmbeddedSvg;

use Latte;


final class LatteExtension extends Latte\Extension
{
	use Helpers;

	/** @var MacroSetting */
	private $setting;


	public function __construct(MacroSetting $setting)
	{
		$this->checkRequirements($setting);
		$this->setting = $setting;
	}


	public function getTags(): array
	{
		return [
			$this->setting->macroName => function (Latte\Compiler\Tag $tag) {
				$tag->expectArguments();

				$node = $tag->parser->parseUnquotedStringOrExpression();
				assert($node instanceof Latte\Compiler\Nodes\Php\Scalar\StringNode);
				$file = $node->value;

				$tag->parser->stream->tryConsume(',');

				$path = $this->getSvgFilePath($this->setting, $file);
				$dom = $this->loadSvgDom($this->setting, $path);
				$svgAttributes = $this->getSvgTagAttributes($dom);
				$inner = $this->extractInnerXml($this->setting, $dom);

				$macroArguments = $tag->parser->parseArguments();

				return new Latte\Compiler\Nodes\AuxiliaryNode(function (Latte\Compiler\PrintContext $context) use ($macroArguments, $svgAttributes, $inner) {
					$macroAttributes = [];
					foreach ($macroArguments->toArguments() as $attribute) {
						$macroAttributes[] = sprintf('%s => %s',
							var_export($attribute->name->print($context), true),
							$attribute->value->print($context)
						);
					}

					return $context->format('
						echo "<svg";
						foreach ([%0.raw] + %1.dump as $n => $v) {
							if ($v === null || $v === false) {
								continue;
							} elseif ($v === true) {
								echo " " . %escape($n);
							} else {
								echo " " . %escape($n) . "=\"" . %escape($v) . "\"";
							}
						}
						echo ">" . %2.dump . "</svg>";
					', implode(', ', $macroAttributes), $this->setting->defaultAttributes + $svgAttributes, $inner
					);
				});
			}
		];
	}
}
