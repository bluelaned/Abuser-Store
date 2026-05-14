<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = \App\Models\User::where('email', 'bluelaned@gmail.com')->first();
if ($user) {
    $user->role = 'admin';
    $user->save();
    echo "Success: Set bluelaned@gmail.com to admin\n";
} else {
    echo "Error: User not found\n";
}
