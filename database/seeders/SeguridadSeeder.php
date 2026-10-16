<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;

class SeguridadSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $environment = app()->environment();
        $this->command->info("[SeguridadSeeder] Entorno detectado: '{$environment}'.");

        // 1. Ejecutar PermisosSeeder y RoleSeeder
        $this->call([
            PermisosSeeder::class,
            RoleSeeder::class,
        ]);

        // 2. Creación / actualización del Administrador
        if ($environment === 'local') {
            $email = 'admin@example.com';
            $password = config('seguridad.password_desarrollo');

            $user = User::where('email', $email)->first();

            if ($user) {
                $user->password = Hash::make($password);
                $user->name = 'Administrador';
                $user->rol_principal = 'Admin';
                $user->activo = true;
                $user->debe_cambiar_password = false;

                if (empty($user->email_verified_at)) {
                    $user->email_verified_at = now();
                }

                $user->save();
                $this->command->info("[SeguridadSeeder] Administrador local actualizado: {$email}. Contraseña restablecida a desarrollo.");
            } else {
                $user = User::create([
                    'name' => 'Administrador',
                    'email' => $email,
                    'password' => Hash::make($password),
                    'rol_principal' => 'Admin',
                    'activo' => true,
                    'debe_cambiar_password' => false,
                    'email_verified_at' => now(),
                ]);
                $this->command->info("[SeguridadSeeder] Administrador local creado: {$email}.");
            }

            if (!$user->hasRole('Admin')) {
                $user->assignRole('Admin');
            }
        } else {
            $adminEmail = config('seguridad.admin_email');
            $adminPassword = config('seguridad.admin_password');

            if (empty($adminEmail) || empty($adminPassword)) {
                throw new InvalidArgumentException(
                    "Configuración incompleta: ADMIN_INICIAL_EMAIL y ADMIN_INICIAL_PASSWORD deben estar definidos fuera del entorno local."
                );
            }

            $user = User::where('email', $adminEmail)->first();

            if ($user) {
                // Si el usuario ya existe: no cambiar contraseña ni email_verified_at
                $user->rol_principal = 'Admin';
                $user->activo = true;
                $user->save();

                $this->command->info("[SeguridadSeeder] Administrador existente asegurado: {$adminEmail}. Contraseña conservada.");
            } else {
                // Si es nuevo: debe_cambiar_password = true y email_verified_at = now()
                $user = User::create([
                    'name' => 'Administrador',
                    'email' => $adminEmail,
                    'password' => Hash::make($adminPassword),
                    'rol_principal' => 'Admin',
                    'activo' => true,
                    'debe_cambiar_password' => true,
                    'email_verified_at' => now(),
                ]);

                $this->command->info("[SeguridadSeeder] Administrador inicial creado: {$adminEmail} (debe cambiar contraseña).");
            }

            if (!$user->hasRole('Admin')) {
                $user->assignRole('Admin');
            }
        }
    }
}
