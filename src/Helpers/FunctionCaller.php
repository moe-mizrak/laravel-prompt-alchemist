<?php

namespace MoeMizrak\LaravelPromptAlchemist\Helpers;

use MoeMizrak\LaravelPromptAlchemist\DTO\ErrorData;
use MoeMizrak\LaravelPromptAlchemist\DTO\FunctionData;
use ReflectionClass;
use ReflectionException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

/**
 * This is a helper class for calling function with given function signature and parameters.
 *
 * Class FunctionCaller
 * @package MoeMizrak\LaravelPromptAlchemist\Helpers
 */
class FunctionCaller
{
    /**
     * Call function with given function signature and parameters.
     *
     * @param FunctionData $function
     *
     * @return mixed
     * @throws ReflectionException
     * @throws UnknownProperties
     */
    public function call(FunctionData $function): mixed
    {
        $params = [];
        // Initialize variables.
        $functionName = $function->function_name;
        $className = $function->class_name;
        $class = new ReflectionClass($className);
        // Retrieve the method from function_name.
        $method = $class->getMethod($functionName);

        foreach ($function->parameters as $parameter) {
            if ($parameter->required && ! isset($parameter->value)) {
                return new ErrorData([
                    'code'    => 400,
                    'message' => 'Required value of parameter '. $parameter->name .' is missing in function '. $functionName . ' to be able to call the function',
                ]);
            }

            $params[$parameter->name] = $parameter->value;
        }

        // Create an instance of the class
        $instance = $class->newInstance();

        // Call the function and get the result.
        return $method->invoke($instance, ...$params);
    }
}