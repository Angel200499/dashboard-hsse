<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::where('role', 'Superadmin')->first();
if (!$user) {
    \App\Models\User::create([
        'name' => 'Superadmin GM',
        'username' => 'superadmin',
        'email' => 'superadmin@example.com',
        'password' => bcrypt('password'),
        'role' => 'Superadmin',
        'fungsi' => 'ALL',
        'is_active' => true
    ]);
    echo "Created Superadmin user (username: superadmin, password: password).\n";
} else {
    echo "Superadmin user already exists (username: {$user->username}).\n";
    
    // reset password to password for testing just in case
    $user->password = bcrypt('password');
    $user->save();
    echo "Password reset to 'password'.\n";
}
