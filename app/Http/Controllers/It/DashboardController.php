<?php

namespace App\Http\Controllers\It;

use App\Http\Controllers\Controller;
use App\Models\PortailUser;

class DashboardController extends Controller
{
    public function index()
    {
        $this->authorize('it-dashboard-view');

        $stats = [
            'total_comptes' => PortailUser::count(),
            'comptes_actifs' => PortailUser::where('status', 'active')->count(),
            'comptes_inactifs' => PortailUser::where('status', '!=', 'active')->count(),
            'clients_avec_acces' => PortailUser::distinct('client_id')->count('client_id'),
            'connexions_recentes' => PortailUser::where('last_login_at', '>=', now()->subDays(7))->count(),
        ];

        $dernieresConnexions = PortailUser::with('client')
            ->whereNotNull('last_login_at')
            ->orderByDesc('last_login_at')
            ->limit(10)
            ->get();

        return view('it.dashboard', compact('stats', 'dernieresConnexions'));
    }
}
