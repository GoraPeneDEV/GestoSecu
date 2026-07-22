<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Departement;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(PermissionSeeder::class);

        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

        $departementRh = Departement::firstOrCreate(['nom' => 'RH']);

        $admin = User::firstOrCreate(
            ['email' => 'admin@gestosecu.local'],
            [
                'prenom' => 'Admin',
                'nom' => 'GestoSecu',
                'password' => bcrypt('password'),
                'status' => 'active',
                'departement_id' => $departementRh->id,
                'email_verified_at' => now(),
            ]
        );

        if (!$admin->hasRole('super_admin')) {
            $admin->assignRole($superAdmin);
        }

        $clientRole = Role::firstOrCreate(['name' => 'client', 'guard_name' => 'portail']);
        $clientRole->syncPermissions(['portail-site-view', 'portail-agent-view', 'portail-ronde-view']);
    }
}
