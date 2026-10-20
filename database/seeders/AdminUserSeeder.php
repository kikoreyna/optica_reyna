<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $name = config('initial_admin.name');
        $email = config('initial_admin.email');
        $password = config('initial_admin.password');

        if (blank($name) && blank($email) && blank($password)) {
            return;
        }

        if (blank($name) || blank($email) || blank($password)) {
            throw new InvalidArgumentException('Configura ADMIN_NAME, ADMIN_EMAIL y ADMIN_PASSWORD para crear al administrador.');
        }

        $datos = Validator::make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
        ])->validate();

        User::firstOrCreate(
            ['email' => $datos['email']],
            [
                'name' => $datos['name'],
                'password' => $datos['password'],
                'role' => User::ROLE_ADMINISTRADOR,
            ],
        );
    }
}
