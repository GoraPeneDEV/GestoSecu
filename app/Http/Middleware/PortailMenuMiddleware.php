<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class PortailMenuMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guard('portail')->check()) {
            $this->loadPortailMenu();
        }

        return $next($request);
    }

    private function loadPortailMenu()
    {
        $menuFilePath = resource_path('menu/verticalMenuPortail.json');

        if (file_exists($menuFilePath)) {
            $verticalMenuJson = file_get_contents($menuFilePath);
            $verticalMenuData = json_decode($verticalMenuJson);
        } else {
            $verticalMenuData = $this->getDefaultPortailMenu();
        }

        $horizontalMenuData = [];

        View::share('menuData', [$verticalMenuData, $horizontalMenuData]);

        View::share('isPortailClient', true);
        View::share('portailUser', Auth::guard('portail')->user());
        View::share('portailClient', Auth::guard('portail')->user()->client);
    }

    private function getDefaultPortailMenu()
    {
        return (object) [
            'menu' => [
                (object) [
                    'url' => '/portail/dashboard',
                    'name' => 'Tableau de bord',
                    'slug' => 'portail-dashboard',
                    'icon' => 'menu-icon tf-icons ti ti-smart-home'
                ],
                (object) [
                    'url' => '/portail/sites',
                    'name' => 'Mes Sites',
                    'slug' => 'portail-sites',
                    'icon' => 'menu-icon tf-icons ti ti-building'
                ],
                (object) [
                    'url' => '/portail/parc',
                    'name' => 'Mon Parc',
                    'slug' => 'portail-parc',
                    'icon' => 'menu-icon tf-icons ti ti-devices'
                ],
                (object) [
                    'url' => '/portail/rondes',
                    'name' => 'Rondes',
                    'slug' => 'portail-rondes',
                    'icon' => 'menu-icon tf-icons ti ti-shield-check'
                ],
                (object) [
                    'url' => '/portail/agents',
                    'name' => 'Agents',
                    'slug' => 'portail-agents',
                    'icon' => 'menu-icon tf-icons ti ti-users'
                ],
                (object) [
                    'url' => '/portail/support',
                    'name' => 'Support',
                    'slug' => 'portail-support',
                    'icon' => 'menu-icon tf-icons ti ti-help-circle'
                ]
            ]
        ];
    }
}
