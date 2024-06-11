<?php

namespace MoeMizrak\LaravelPromptAlchemist\DTO;

use Spatie\DataTransferObject\DataTransferObject;

/**
 * DTO for return value.
 *
 * Class ReturnData
 * @package MoeMizrak\LaravelPromptAlchemist\DTO
 */
class ReturnData extends DataTransferObject
{
    /**
     * Type of the return value e.g. integer, float, string, object etc.
     *
     * @var string|null
     */
    public ?string $type;

    /**
     * Description of the return value.
     * Such as "An object containing details like totalAmount, transactions (array), and other relevant financial data."
     *
     * @var string|null
     */
    public ?string $description;

    /**
     * Example of the return value.
     * Such as
     * example:
     *  creditScore: 750
     *  creditReportSummary: 'positive'
     *
     * @var mixed|null
     */
    public mixed $example;
}