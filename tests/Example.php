<?php

namespace MoeMizrak\LaravelPromptAlchemist\Tests;

class Example
{
    // Sample private property
    private string $privateProperty = 'privateProperty';

    // Sample public property which is set in the constructor.
    public string $publicProperty;

    public function __construct() {
        $this->publicProperty = 'publicProperty';
    }

    /**
     * This private function is intended for testing purposes. It accepts a string and int parameters and returns a string.
     *
     * @param string $stringParam
     * @param int $intParam
     *
     * @return string
     */
    private function privateFunction(string $stringParam, int $intParam): string
    {
        return 'private return value ' . $stringParam . ' ' . $intParam;
    }

    /**
     * This public function is intended for testing purposes. It accepts a string and int parameters and returns a string.
     *
     * @param string $stringParam
     * @param int $intParam
     *
     * @return string
     */
    public function publicFunction(string $stringParam, int $intParam): string
    {
        return 'public return value ' . $stringParam . ' ' . $intParam;
    }

    /**
     * This function is intended for testing purposes. It has param with no type hint.
     *
     * @param string $stringParam
     * @param $paramMissingType
     *
     * @return string
     */
    public function functionWithMissingTypeHint(string $stringParam, $paramMissingType): string
    {
        return 'Return params ' . $stringParam . ' ' . $paramMissingType;
    }

    /**
     * This function is intended for testing purposes. It has param with no type hint and default value.
     *
     * @param string $stringParam
     * @param $paramMissingType
     *
     * @return string
     */
    public function functionWithMissingTypeHintAndDefaultValue(string $stringParam, $paramMissingTypeDefaultValue = 'someValue'): string
    {
        return 'Return params ' . $stringParam . ' ' . $paramMissingTypeDefaultValue;
    }

    /**
     * This public function is intended for testing purposes. It accepts a string and int parameters and returns a string.
     * It has additional parameter descriptions and detailed docblock.
     *
     * @param string $stringParam This is the string param description
     * @param int $intParam This is the int param description
     *
     * @return string This is the return value description
     */
    public function detailedDocBlockFunction(string $stringParam, int $intParam = 2): string
    {
        return 'detailed docblock function return value ' . $stringParam . ' ' . $intParam;
    }

    /**
     * This public function is intended for testing purposes. It accepts a string and int parameters and returns a string.
     * It has missing parameter descriptions.
     *
     * @return string This is the return value description
     */
    public function detailedDocBlockFunctionWithSomeMissingDocBlock(string $stringParam, int $intParam): string
    {
        return 'missing parameter docblock function return value ' . $stringParam . ' ' . $intParam;
    }

    function noExtraInfoProvidedFunction($stringParam, $intParam)
    {
        return 'missing parameter docblock function return value ' . $stringParam . ' ' . $intParam;
    }
}