<?php

namespace MoeMizrak\LaravelPromptAlchemist\DTO;

use MoeMizrak\LaravelPromptAlchemist\Types\VisibilityType;
use Spatie\DataTransferObject\DataTransferObject;

/**
 * DTO for necessary function signatures such as name of the function, parameters etc..
 *
 * Class FunctionData
 * @package MoeMizrak\LaravelPromptAlchemist\DTO
 */
class FunctionData extends DataTransferObject
{
    /**
     * Name of the function.
     *
     * @var string|null
     */
    public ?string $function_name;

    /**
     * Parameters of the function.
     *
     * @var ParameterData[]|null
     */
    public ?array $parameters;

    /**
     * Visibility of function.
     * Default is public.
     *
     * @var string|null
     */
    public ?string $visibility = VisibilityType::PUBLIC;

    /**
     * Description of the function.
     * Such as "Retrieves financial data for a specific user and timeframe."
     *
     * @var string|null
     */
    public ?string $description;

    /**
     * Return data of the function.
     *
     * @var ReturnData|null
     */
    public ?ReturnData $return;

    /**
     * Class name that function belongs to.
     *
     * @var string|null
     */
    public ?string $class_name;

    /**
     * Set parameter values.
     *
     * @param array $parameters
     *
     * @return void
     */
    public function setParameterValues(array $parameters): void
    {
        foreach ($this->parameters as $llmReturnedParameter) {
            $matchingParameter = array_filter($parameters, fn($param) => $param->name === $llmReturnedParameter->name);
            if ($matchingParameter) {
                $llmReturnedParameter->value = current($matchingParameter)->value;
            }
        }
    }
}