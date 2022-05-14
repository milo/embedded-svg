<?php

declare(strict_types=1);

namespace Milo\EmbeddedSvg;

class MacroSetting
{
    /**
     * @var string
     */
    public $baseDir;

    /**
     * @var string
     */
    public $macroName = 'embeddedSvg';

    /**
     * @var int
     */
    public $libXmlOptions = LIBXML_NOBLANKS;

    /**
     * @var bool
     */
    public $prettyOutput = false;

    /**
     * @var mixed[]
     */
    public $defaultAttributes = [];

    /**
     * @var callable[]
     */
    public $onLoad = [];

    /**
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value): void
    {
        throw new \LogicException('Cannot write to an undeclared property ' . static::class . "::\$${name}.");
    }

    /**
     * @param mixed[] $setting
     */
    public static function createFromArray(array $setting): self
    {
        $me = new self();
        foreach ($setting as $property => $value) {
            $me->{$property} = $value;
        }
        return $me;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function &__get($name)
    {
        throw new \LogicException('Cannot read an undeclared property ' . static::class . "::\$${name}.");
    }
}
