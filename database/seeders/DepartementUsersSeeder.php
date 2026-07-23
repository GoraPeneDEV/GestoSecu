<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Departement;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

/**
 * Crée un utilisateur de démonstration par département officiel, pour pouvoir
 * se connecter et voir le menu/tableau de bord propre à chaque département
 * (RH, Direction, IT, SIE, Achats & Logistique, Comptabilité).
 *
 * Identifiants : {slug-departement}@demo.com / Pwd@demo
 */
class DepartementUsersSeeder extends Seeder
{
    private const DEPARTEMENTS_DEMO = [
        'RH' => ['slug' => 'rh', 'prenom' => 'Demo', 'nom' => 'RH'],
        'Direction' => ['slug' => 'direction', 'prenom' => 'Demo', 'nom' => 'Direction'],
        'IT' => ['slug' => 'it', 'prenom' => 'Demo', 'nom' => 'IT'],
        'SIE' => ['slug' => 'sie', 'prenom' => 'Demo', 'nom' => 'SIE'],
        'Achats & Logistique' => ['slug' => 'achats-logistique', 'prenom' => 'Demo', 'nom' => 'Achats & Logistique'],
        'Comptabilité' => ['slug' => 'comptabilite', 'prenom' => 'Demo', 'nom' => 'Comptabilité'],
    ];

    public function run(): void
    {
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

        foreach (self::DEPARTEMENTS_DEMO as $nomDepartement => $demo) {
            $departement = Departement::firstOrCreate(['nom' => $nomDepartement]);

            $user = User::firstOrCreate(
                ['email' => $demo['slug'] . '@demo.com'],
                [
                    'prenom' => $demo['prenom'],
                    'nom' => $demo['nom'],
                    'password' => bcrypt('Pwd@demo'),
                    'status' => 'active',
                    'departement_id' => $departement->id,
                ]
            );

            if (!$user->hasRole('super_admin')) {
                $user->assignRole($superAdmin);
            }
        }
    }
}
