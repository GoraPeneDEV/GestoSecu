<?php

namespace App\Http\Controllers\Paie;

use App\Http\Controllers\Controller;
use App\Services\Payroll\SalaireSimulatorService;
use Illuminate\Http\Request;

class SimulationController extends Controller
{
    protected $simulator;

    public function __construct(SalaireSimulatorService $simulator)
    {
        $this->simulator = $simulator;
    }

    public function index()
    {
        $this->authorize('paie-simulations-access');

        return view('paie.simulations.index');
    }

    public function simulateBrutToNet(Request $request)
    {
        $this->authorize('paie-simulations-access');

        $validated = $request->validate([
            'salaire_brut' => 'required|numeric|min:0',
            'categorie_professionnelle' => 'required|in:Cadre,Non-cadre',
            'parts_fiscales' => 'required|numeric|min:1|max:6',
            'nb_enfants' => 'nullable|integer|min:0|max:10',
            'nb_epouses' => 'nullable|integer|min:0|max:4',
        ]);

        $result = $this->simulator->calculateBrutToNet(
            $validated['salaire_brut'],
            $validated['categorie_professionnelle'],
            $validated['parts_fiscales'],
            (int) ($validated['nb_enfants'] ?? 0),
            (int) ($validated['nb_epouses'] ?? 0)
        );

        return response()->json($result);
    }

    public function simulateNetToBrut(Request $request)
    {
        $this->authorize('paie-simulations-access');

        $validated = $request->validate([
            'salaire_net' => 'required|numeric|min:0',
            'categorie_professionnelle' => 'required|in:Cadre,Non-cadre',
            'parts_fiscales' => 'required|numeric|min:1|max:6',
            'nb_enfants' => 'nullable|integer|min:0|max:10',
            'nb_epouses' => 'nullable|integer|min:0|max:4',
        ]);

        $result = $this->simulator->calculateNetToBrut(
            $validated['salaire_net'],
            $validated['categorie_professionnelle'],
            $validated['parts_fiscales'],
            (int) ($validated['nb_enfants'] ?? 0),
            (int) ($validated['nb_epouses'] ?? 0)
        );

        return response()->json($result);
    }
}
