<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\LoanTransaction;
use App\Http\Controllers\SiteController;

$loan = LoanTransaction::find(53);
if (!$loan) {
    echo "Loan #53 not found.\n";
    exit;
}

echo "Testing Server-Side POST Renewal Request for Overdue Loan #53...\n";
echo "Loan ID: " . $loan->id . "\n";
echo "Due Date: " . $loan->due_date . "\n";
echo "Is Overdue: " . ($loan->isOverdue() ? 'YES' : 'NO') . "\n";

// Authenticate as the patron's user
auth()->loginUsingId($loan->patron->user_id);

$controller = new SiteController();
$response = $controller->renewLoan($loan->id);

echo "\n--- SERVER RESPONSE ---\n";
echo "HTTP Status Code: " . $response->getStatusCode() . "\n";
echo "JSON Content: " . $response->getContent() . "\n";
