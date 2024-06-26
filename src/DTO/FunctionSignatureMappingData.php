<?php

namespace MoeMizrak\LaravelPromptAlchemist\DTO;

use Spatie\DataTransferObject\DataTransferObject;

/**
 * Represents the signature mapping of a function, including its name, parameters, return type etc.
 *  This DTO is used to map namings of a dynamic function signature representation to a code-usable form, allowing the
 *  creation of actual function definitions at runtime.
 * e.g. new FunctionSignatureMappingData(['function_name' => 'function_name', 'parameters' => 'arguments' ...]) where it maps different formatted function descriptions.
 *
 * Class FunctionSignatureMappingData
 * @package MoeMizrak\LaravelPromptAlchemist\DTO
 */
class FunctionSignatureMappingData extends DataTransferObject
{
    /**
     * @var MappingData The name of the function
     */
    public MappingData $function_name;

    /**
     * @var MappingData|null The visibility of the function
     */
    public ?MappingData $function_visibility;

    /**
     * @var MappingData|null The description of the function
     */
    public ?MappingData $function_description;

    /**
     * @var MappingData|null The return value of the function
     */
    public ?MappingData $function_return_value;

    /**
     * @var MappingData|null The parameters of the function
     */
    public ?MappingData $parameters;

    /**
     * @var MappingData|null The name of the parameter
     */
    public ?MappingData $parameter_name;

    /**
     * @var MappingData|null The type of the parameter
     */
    public ?MappingData $parameter_type;

    /**
     * @var MappingData|null Information about whether the parameter is required
     */
    public ?MappingData $parameter_required_info;

    /**
     * @var MappingData|null The description of the parameter
     */
    public ?MappingData $parameter_description;

    /**
     * @var MappingData|null An example value for the parameter
     */
    public ?MappingData $parameter_example;

    /**
     * @var MappingData|null Value of the parameter if provided.
     */
    public ?MappingData $parameter_value;

    /**
     * @var MappingData|null The class name that function belongs to.
     */
    public ?MappingData $class_name;
}