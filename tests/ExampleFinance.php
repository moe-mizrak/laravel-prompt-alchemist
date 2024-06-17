<?php

namespace MoeMizrak\LaravelPromptAlchemist\Tests;

class ExampleFinance
{
    /**
     * Retrieves financial data for a specific user and timeframe.
     *
     * @param int $userId The unique identifier for the user.
     * @param string $startDate The starting date for the timeframe (inclusive).
     * @param string $endDate The ending date for the timeframe (inclusive).
     *
     * @return object An object containing details like totalAmount, transactions (array), and other relevant financial data.
     */
    public function getFinancialData(int $userId, string $startDate, string $endDate): object
    {
        return (object) [
            'totalAmount' => 1000.0,
            'transactions' => [
                ['amount' => 100, 'date' => '2023-01-01', 'description' => 'Groceries'],
                ['amount' => 200, 'date' => '2023-01-02', 'description' => 'Utilities'],
            ],
            'message' => "Retrieved financial data for user {$userId} from {$startDate} to {$endDate}"
        ];
    }

    /**
     * Categorizes a list of transactions based on predefined rules or machine learning models.
     *
     * @param array $transactions An array of transactions with details like amount, date, and description.
     *
     * @return array An array of transactions with an added "category" field if successfully categorized. Each transaction may also include a "confidenceScore" field.
     */
    public function categorizeTransactions(array $transactions): array
    {
        return [
            [
                'amount' => 100,
                'date' => '2023-01-01',
                'description' => 'Groceries',
                'category' => 'Food',
                'confidenceScore' => 0.95
            ],
            [
                'amount' => 50,
                'date' => '2023-01-02',
                'description' => 'Entertainment',
                'category' => 'Leisure',
                'confidenceScore' => 0.80
            ]
        ];
    }

    /**
     * Finds the top spending categories from a list of categorized transactions.
     *
     * @param array $transactions An array of categorized transactions.
     *
     * @return object A DTO object containing details of the top spending category.
     */
    public function getTopCategories(array $transactions): object
    {
        return (object) [
            'name' => 'Food',
            'totalAmount' => 300.00,
            'message' => 'Top spending category is Food with total amount 300.00'
        ];
    }

    /**
     * Retrieves the current credit score for a specific user.
     *
     * @param int $userId The unique identifier of the user.
     *
     * @return object An object containing the credit score, credit report summary, and any relevant notes.
     */
    public function getCreditScore(int $userId): object
    {
        return (object) [
            'creditScore' => 750,
            'creditReportSummary' => 'positive',
            'message' => "Retrieved credit score for user {$userId}"
        ];
    }

    /**
     * Retrieves the current balance for a specific user account.
     *
     * @param int $accountId The unique identifier of the account.
     * @param string|null $asOfDate The date for which the balance is requested (optional).
     *
     * @return object An object containing the current balance and the account status.
     */
    public function getAccountBalance(int $accountId, ?string $asOfDate = null): object
    {
        return (object) [
            'balance' => 5000.00,
            'status' => 'active',
            'message' => "Retrieved account balance for account {$accountId} as of " . ($asOfDate ?? 'today')
        ];
    }
}