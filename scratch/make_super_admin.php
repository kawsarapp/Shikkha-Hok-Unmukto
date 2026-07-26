<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

DB::statement("ALTER TABLE users MODIFY COLUMN role VARCHAR(50) NOT NULL DEFAULT 'student'");

$user = User::firstOrCreate(
    ['email' => 'admin@educationfree.com'],
    [
        'name' => 'Super Admin',
        'phone' => '01700000000',
        'password' => Hash::make('password123'),
    ]
);

$user->update([
    'name' => 'Super Admin',
    'role' => 'super_admin',
    'permissions' => ['manage_courses', 'manage_questions', 'manage_settings', 'manage_users'],
]);

echo "SUCCESS: Super Admin Account Configured\n";
echo "Email: admin@educationfree.com\n";
echo "Password: password123\n";
echo "Role: " . $user->role . "\n";
