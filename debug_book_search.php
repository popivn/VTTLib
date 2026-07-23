<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\BookItem;

$barcode = '2024-0304';
$bookItem = BookItem::with(['bibliographicRecord', 'currentLoan.patron.user'])
    ->where('barcode', 'like', $barcode . '%')
    ->first();

if (!$bookItem) {
    echo "Book item not found for barcode: $barcode\n";
    exit;
}

echo "BOOK_ITEM_INFO:\n";
echo "ID: " . $bookItem->id . "\n";
echo "Barcode: " . $bookItem->barcode . "\n";
echo "Status: " . $bookItem->status . "\n";

$record = $bookItem->bibliographicRecord;
if ($record) {
    echo "\nBIBLIOGRAPHIC_RECORD_INFO:\n";
    echo "Title: " . var_export($record->title, true) . "\n";
    echo "Author: " . var_export($record->author, true) . "\n";
    echo "Call Number: " . var_export($record->call_number, true) . "\n";
    echo "Cover Image: " . var_export($record->cover_image, true) . "\n";
    
    // Check encoding of each property
    foreach ($record->toArray() as $key => $val) {
        if (is_string($val)) {
            $isUtf8 = mb_check_encoding($val, 'UTF-8');
            echo "Field '$key': " . ($isUtf8 ? "UTF-8 OK" : "MALFORMED UTF-8") . " (Hex: " . bin2hex($val) . ")\n";
        }
    }
} else {
    echo "\nNo bibliographic record found.\n";
}

$loan = $bookItem->currentLoan;
if ($loan) {
    echo "\nCURRENT_LOAN_INFO:\n";
    echo "Patron display name: " . var_export($loan->patron?->display_name, true) . "\n";
    echo "User name: " . var_export($loan->patron?->user?->name, true) . "\n";
} else {
    echo "\nNo current loan.\n";
}

// Try encoding $bookItem->toArray() as JSON and catch the error
$json = json_encode($bookItem->toArray());
if ($json === false) {
    echo "\nJSON_ENCODE_ERROR: " . json_last_error_msg() . "\n";
} else {
    echo "\nJSON_ENCODE_SUCCESSFUL\n";
}
