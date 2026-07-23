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

        // Départements officiels de GestoSecu (pilote le menu dynamique par département, voir LoadMenuMiddleware)
        foreach (['RH', 'Direction', 'IT', 'SIE', 'Achats & Logistique', 'Comptabilité'] as $nomDepartement) {
            Departement::firstOrCreate(['nom' => $nomDepartement]);
        }

        $departementRh = Departement::where('nom', 'RH')->first();

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

        $this->call(DepartementRolesSeeder::class);
        $this->call(DepartementUsersSeeder::class);
    }
}
