<?php
$user = \App\Models\User::first();
if (;user) { $user = new \App\Models\User(); $user->id = 1; $user->name = 'Test User'; $user->phone = '08123456789'; }
$invoice = new \App\Models\Isp\Invoice();
$invoice->id = 999;
$invoice->amount = 150000;
$invoice->created_at = now();
$invoice->due_date = now()->addDays(7);
try {
    $user->notify(new \App\Notifications\Isp\InvoiceGenerated($invoice));
    echo 'Notification Sent';
} catch (\Throwable $e) {
    echo 'Error: ' . $e->getMessage();
}

