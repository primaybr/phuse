<?php

/**
 * Transaction Example
 *
 * This example demonstrates how to use database transactions with the Model class.
 * Transactions ensure that multiple database operations are executed atomically -
 * either all succeed or all are rolled back.
 */

require_once __DIR__ . '/../Core/Boot.php';

try {
    // Create a model instance (this will fail if database is not configured)
    $userModel = new Core\Model('users');

    echo "=== Database Transaction Example ===\n\n";

    // Example 1: Manual transaction control
    echo "1. Manual Transaction Control:\n";

    // Begin transaction
    $userModel->beginTransaction();
    echo "   - Transaction started\n";

    try {
        // Perform multiple operations
        // $userModel->save(['name' => 'John Doe', 'email' => 'john@example.com']);
        // $userModel->save(['name' => 'Jane Doe', 'email' => 'jane@example.com']);

        echo "   - Operations would be performed here\n";

        // Commit transaction
        $userModel->commit();
        echo "   - Transaction committed successfully\n";

    } catch (Exception $e) {
        // Rollback on error
        $userModel->rollback();
        echo "   - Transaction rolled back due to error: " . $e->getMessage() . "\n";
    }

    echo "\n";

    // Example 2: Transaction callback (automatic rollback on exception)
    echo "2. Transaction Callback (Recommended):\n";

    $result = $userModel->transaction(function($model) {
        echo "   - Inside transaction callback\n";

        // Perform operations
        // $model->save(['name' => 'Bob Smith', 'email' => 'bob@example.com']);
        // $model->where('id', 1)->update(['status' => 'active']);

        echo "   - Operations would be performed here\n";

        return "Transaction completed successfully";
    });

    echo "   - Result: $result\n";

    echo "\n=== Transaction Example Complete ===\n";

} catch (Core\Exception\DatabaseException $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
    echo "This is expected if the database is not properly configured.\n";
} catch (Exception $e) {
    echo "General Error: " . $e->getMessage() . "\n";
}

echo "\nTransaction Methods Available:\n";
echo "- beginTransaction(): Start a transaction\n";
echo "- commit(): Commit the transaction\n";
echo "- rollback(): Rollback the transaction\n";
echo "- transaction(callable): Execute callback within transaction\n";
