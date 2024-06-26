<?php

namespace MoeMizrak\LaravelPromptAlchemist\DTO;

use Spatie\DataTransferObject\DataTransferObject;

/**
 * DTO for parameter of a function.
 *
 * Class ParameterData
 * @package MoeMizrak\LaravelPromptAlchemist\DTO
 */
class ParameterData extends DataTransferObject
{
    /**
     * Name of the parameter.
     *
     * @var string
     */
    public string $name;

    /**
     * Type of the parameter e.g. integer, float, string, object etc.
     *
     * @var string|null
     */
    public ?string $type;

    /**
     * Sets if parameter is required or not.
     *
     * @var bool|null
     */
    public ?bool $required;

    /**
     * Description of the parameter.
     * Such as "The unique identifier of the account."
     *
     * @var string|null
     */
    public ?string $description;

    /**
     * Example of the parameter.
     * Such as '2023-06-01', or 20, or array
     *
     * @var mixed|null
     */
    public mixed $example;

    /**
     * Default value of the parameter.
     *
     * @var mixed|null
     */
    public mixed $default;

    /**
     * Value of the parameter if exists.
     *
     * @var mixed
     */
    public mixed $value;
}