<?php

namespace App\Http\Controllers\immobilisations;

use App\Http\Controllers\Controller;
use App\Models\Immobilisation;
use Illuminate\Support\Facades\Auth;

class ScanController extends Controller
{
    /**
     * Affiche la fiche d'un bien via scan QR Code (authentification requise)
     */
    public function show($token)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Veuillez vous connecter pour accéder à cette fiche.');
        }

        $this->authorize('immobilisations-view');

        $immobilisation = Immobilisation::where('qr_token', $token)
            ->with(['categorie', 'site', 'emplacement', 'employe', 'amortissementLignes'])
            ->first();

        if (!$immobilisation) {
            return view('immobilisations.scan.unassigned', compact('token'));
        }

        $immobilisation->mouvements()->create([
            'type_mouvement' => 'inventaire',
            'motif' => 'Scan QR Code via ' . request()->header('User-Agent'),
            'created_by' => Auth::id(),
        ]);

        return view('immobilisations.scan.show', compact('immobilisation'));
    }
}
