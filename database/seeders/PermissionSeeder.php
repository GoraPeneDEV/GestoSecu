<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

/**
 * Catalogue complet des permissions référencées par les contrôleurs web et
 * mobile de GestoSecu (RH + Paie, Ronde + Supervision, SAV + Articles +
 * Dotations + Immobilisations). Le rôle super_admin bypass ces vérifications
 * via Gate::before (AuthServiceProvider), mais Spatie\Permission::hasPermissionTo()
 * lève une exception si le nom de permission n'existe pas du tout en base
 * (contrairement à can()/Gate qui retourne simplement false) — ces
 * enregistrements doivent donc exister même si aucun rôle autre que
 * super_admin ne les utilise encore.
 */
class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // RH
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

            // Paie
            'paie-dashboard-view', 'paie-employe-manage', 'paie-employe-read',
            'paie-variables-create', 'paie-variables-read', 'paie-variables-validate',
            'paie-bulletins-generate', 'paie-bulletins-validate', 'paie-bulletins-delete',
            'paie-bulletins-read-any', 'paie-bulletins-read-own', 'paie-bulletin-read-any', 'paie-bulletin-read-own',
            'paie-simulations-access',
            'elements-paie-view', 'elements-paie-create', 'elements-paie-edit', 'elements-paie-delete',
            'baremes-fiscaux-view', 'baremes-fiscaux-create', 'baremes-fiscaux-edit', 'baremes-fiscaux-delete',

            // Ronde + Supervision
            'ronde-view', 'ronde-create',
            'ronde-superviseur-view', 'ronde-superviseur-create',
            'planning-ronde-view', 'planning-ronde-create', 'planning-ronde-update', 'planning-ronde-delete',
            'point-controle-view', 'point-controle-create', 'point-controle-update', 'point-controle-delete',
            'point-controle-superviseur-view', 'point-controle-superviseur-create', 'point-controle-superviseur-update', 'point-controle-superviseur-delete',
            'supervision-view', 'supervision-create',

            // Articles + Dotations
            'article-view', 'article-create', 'article-update', 'article-delete',
            'dotation-view', 'dotation-create', 'dotation-update', 'dotation-delete',

            // Immobilisations
            'immobilisations-view', 'immobilisations-create', 'immobilisations-edit', 'immobilisations-delete',
            'immobilisation-categories-manage', 'immobilisation-sites-manage',

            // SAV
            'sav-dashboard-view',
            'sav-fiche-progres-view', 'sav-fiche-progres-create', 'sav-fiche-progres-edit', 'sav-fiche-progres-delete',
            'sav-fiche-progres-analyse', 'sav-fiche-progres-evaluer',
            'sav-contrat-view', 'sav-contrat-create', 'sav-contrat-edit', 'sav-contrat-delete', 'sav-contrat-renouveler',
            'sav-garantie-view', 'sav-garantie-create', 'sav-garantie-edit', 'sav-garantie-delete',
            'sav-interaction-view', 'sav-interaction-create', 'sav-interaction-edit', 'sav-interaction-delete',
            'sav-parc-view', 'sav-parc-create', 'sav-parc-edit', 'sav-parc-delete',
            'sav-maintenance-view', 'sav-maintenance-create', 'sav-maintenance-edit', 'sav-maintenance-delete',
            'sav-intervention-view', 'sav-intervention-create', 'sav-intervention-edit',
        ];

        foreach (array_unique($permissions) as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        // Portail client (guard 'portail', modèle PortailUser)
        $portailPermissions = [
            'portail-site-view',
            'portail-agent-view',
            'portail-ronde-view',
        ];

        foreach ($portailPermissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'portail']);
        }
    }
}
