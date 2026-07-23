<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

/**
 * Rôles à privilège scopé par département officiel (RH, Direction, IT, SIE,
 * Achats & Logistique, Comptabilité). Contrairement à super_admin (bypass
 * total via Gate::before), ces rôles ne donnent accès qu'aux permissions de
 * leur périmètre — Direction fait exception avec un accès en lecture
 * transverse à tous les modules, cohérent avec son tableau de bord 360°.
 */
class DepartementRolesSeeder extends Seeder
{
    private const ROLES = [
        'rh' => [
            'employe-view', 'employe-create', 'employe-update', 'employe-delete', 'employes-archive',
            'departement-view', 'departement-create', 'departement-update', 'departement-delete',
            'contrat-view', 'contrat-create', 'contrat-update', 'contrat-delete', 'contrat-statut-edit', 'contrat-document-manage',
            'planning-view', 'planning-create', 'planning-update', 'planning-delete',
            'horaire-planning-view', 'horaire-planning-create', 'horaire-planning-update', 'horaire-planning-delete',
            'jour-ferier-view', 'jour-ferier-create', 'jour-ferier-update', 'jour-ferier-delete',
            'demande-explication-view', 'demande-explication-create', 'demande-explication-update', 'demande-explication-delete',
            'conge-admin-view', 'conge-admin-create', 'conge-admin-edit', 'conge-admin-delete',
            'conge-admin-dept-view', 'conge-admin-suivi-view', 'conge-admin-validate', 'conge-admin-refuse',
            'conge-admin-cancel', 'conge-admin-enregistrer', 'conge-admin-solde-adjust',
        ],

        'it' => [
            'it-dashboard-view',
            'portail-user-view', 'portail-user-create', 'portail-user-update', 'portail-user-delete', 'portail-user-reset-password',
        ],

        'sie' => [
            'ronde-view', 'ronde-create',
            'ronde-superviseur-view', 'ronde-superviseur-create',
            'planning-ronde-view', 'planning-ronde-create', 'planning-ronde-update', 'planning-ronde-delete',
            'point-controle-view', 'point-controle-create', 'point-controle-update', 'point-controle-delete',
            'point-controle-superviseur-view', 'point-controle-superviseur-create', 'point-controle-superviseur-update', 'point-controle-superviseur-delete',
            'supervision-view', 'supervision-create',
        ],

        'achats_logistique' => [
            'article-view', 'article-create', 'article-update', 'article-delete',
            'dotation-view', 'dotation-create', 'dotation-update', 'dotation-delete',
            'immobilisations-view', 'immobilisations-create', 'immobilisations-edit', 'immobilisations-delete',
            'immobilisation-categories-manage', 'immobilisation-sites-manage',
        ],

        'comptabilite' => [
            'paie-dashboard-view', 'paie-employe-manage', 'paie-employe-read',
            'paie-variables-create', 'paie-variables-read', 'paie-variables-validate',
            'paie-bulletins-generate', 'paie-bulletins-validate', 'paie-bulletins-delete',
            'paie-bulletins-read-any', 'paie-bulletins-read-own', 'paie-bulletin-read-any', 'paie-bulletin-read-own',
            'paie-simulations-access',
            'elements-paie-view', 'elements-paie-create', 'elements-paie-edit', 'elements-paie-delete',
            'baremes-fiscaux-view', 'baremes-fiscaux-create', 'baremes-fiscaux-edit', 'baremes-fiscaux-delete',
        ],

        // Vue 360° : lecture transverse sur tous les départements + pilotage
        'direction' => [
            'direction-dashboard-view',
            'employe-view', 'departement-view', 'contrat-view', 'planning-view',
            'paie-dashboard-view', 'paie-bulletins-read-any', 'paie-bulletin-read-any',
            'ronde-view', 'ronde-superviseur-view', 'supervision-view',
            'article-view', 'dotation-view', 'immobilisations-view',
            'sav-dashboard-view', 'sav-fiche-progres-view', 'sav-contrat-view', 'sav-garantie-view',
            'sav-interaction-view', 'sav-parc-view', 'sav-maintenance-view', 'sav-intervention-view',
            'it-dashboard-view', 'portail-user-view',
        ],
    ];

    public function run(): void
    {
        foreach (self::ROLES as $roleName => $permissions) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->syncPermissions($permissions);
        }
    }
}
