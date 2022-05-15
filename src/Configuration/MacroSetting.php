<?php

declare(strict_types=1);

namespace Milo\EmbeddedSvg\Configuration;

final class MacroSetting
{
    /**
     * @var string
     */
    public $baseDir;

    /**
     * @var int
     */
    public $libXmlOptions = LIBXML_NOBLANKS;

    /**
     * @var bool
     */
    public $prettyOutput = false;

    /**
     * @var array<string, mixed>
     */
    public $defaultAttributes = [];

    /**
     * @var callable[]
     */
    public $onLoad = [];

    /**
     * @param array<string, mixed> $settings
     */
    public function __construct(array $settings)
    {
        foreach ($settings as $property => $value) {
            $this->{$property} = $value;
        }
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value): void
    {
        throw new \LogicException('Cannot write to an undeclared property ' . static::class . "::\$${name}.");
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
