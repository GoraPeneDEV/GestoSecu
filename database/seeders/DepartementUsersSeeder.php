<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Departement;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

/**
 * Crée un utilisateur de démonstration par département officiel, pour pouvoir
 * se connecter et voir le menu/tableau de bord propre à chaque département
 * (RH, Direction, IT, SIE, Achats & Logistique, Comptabilité) avec un
 * privilège scopé à son périmètre (rôle créé par DepartementRolesSeeder),
 * et non un accès super_admin qui contournerait toute vérification.
 *
 * Identifiants : {slug-departement}@demo.com / Pwd@demo
 */
class DepartementUsersSeeder extends Seeder
{
    private const DEPARTEMENTS_DEMO = [
        'RH' => ['slug' => 'rh', 'prenom' => 'Demo', 'nom' => 'RH', 'role' => 'rh'],
        'Direction' => ['slug' => 'direction', 'prenom' => 'Demo', 'nom' => 'Direction', 'role' => 'direction'],
        'IT' => ['slug' => 'it', 'prenom' => 'Demo', 'nom' => 'IT', 'role' => 'it'],
        'SIE' => ['slug' => 'sie', 'prenom' => 'Demo', 'nom' => 'SIE', 'role' => 'sie'],
        'Achats & Logistique' => ['slug' => 'achats-logistique', 'prenom' => 'Demo', 'nom' => 'Achats & Logistique', 'role' => 'achats_logistique'],
        'Comptabilité' => ['slug' => 'comptabilite', 'prenom' => 'Demo', 'nom' => 'Comptabilité', 'role' => 'comptabilite'],
    ];

    public function run(): void
    {
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

            $role = Role::firstOrCreate(['name' => $demo['role'], 'guard_name' => 'web']);
            $user->syncRoles([$role]);
        }
    }
}
