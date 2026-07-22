<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class LoadMenuMiddleware
{
    private const DEPARTEMENT_MENU_FILES = [
        'RH' => 'verticalMenuRH.json',
        'Direction' => 'verticalMenuDirection.json',
        'IT' => 'verticalMenuIT.json',
        'SIE' => 'verticalMenuSIE.json',
        'Achats & Logistique' => 'verticalMenuAchatsLogistique.json',
        'Comptabilité' => 'verticalMenuComptabilite.json',
    ];

    public function handle($request, Closure $next)
    {
        // Le menu du portail client est géré par PortailMenuMiddleware (alias 'portail.menu').
        if (Auth::guard('web')->check()) {
            $this->loadEmployeeMenu();
        }

        return $next($request);
    }

    private function loadEmployeeMenu(): void
    {
        $user = Auth::guard('web')->user();

        if (!$user || !$user->departement) {
            $this->loadDefaultMenu();
            return;
        }

        $menuFile = self::DEPARTEMENT_MENU_FILES[$user->departement->nom] ?? 'verticalMenuDefault.json';
        $verticalMenuData = $this->readMenuJson(resource_path('menu/' . $menuFile));

        if (!$verticalMenuData) {
            $this->loadDefaultMenu();
            return;
        }

        View::share('menuData', [$verticalMenuData, []]);
        View::share('isPortailClient', false);
        View::share('userDepartment', $user->departement);
    }

    private function loadDefaultMenu(): void
    {
        $verticalMenuData = $this->readMenuJson(resource_path('menu/verticalMenuDefault.json'))
            ?? (object) ['menu' => [(object) ['url' => '/dashboard', 'name' => 'Tableau de bord', 'slug' => 'dashboard', 'icon' => 'menu-icon tf-icons ti ti-smart-home']]];

        View::share('menuData', [$verticalMenuData, []]);
        View::share('isPortailClient', false);
    }

    private function readMenuJson(string $path): ?object
    {
        if (!file_exists($path)) {
            return null;
        }

        return json_decode(file_get_contents($path));
    }
}
