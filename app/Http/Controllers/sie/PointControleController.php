<?php

namespace App\Http\Controllers\sie;

use Carbon\Carbon;
use App\Models\Site;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\PointControle;
use App\Http\Controllers\Controller;
use chillerlan\QRCode\{QRCode, QROptions};
use chillerlan\QRCode\Output\QRMarkupSVG;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\{DB, Log, Storage};

class PointControleController extends Controller
{
    /**
     * Affiche la liste des points de contrôle.
     */
    public function index()
    {
        $this->authorize('point-controle-view');
        $sites = Site::orderBy('nom_site')->get();

        $totalPointControles = PointControle::count();
        $pointControlesActifs = PointControle::where('actif', true)->count();
        $pointControlesInactifs = PointControle::where('actif', false)->count();

        return view('sie.pointcontroles.index', compact(
            'sites',
            'totalPointControles',
            'pointControlesActifs',
            'pointControlesInactifs'
        ));
    }

    /**
     * Récupère les données des points de contrôle (DataTables).
     */
    public function getPointControles(Request $request)
    {
        try {
            $query = PointControle::with('site');

            if ($request->filled('site_id')) {
                $query->where('site_id', $request->site_id);
            }

            if ($request->filled('actif')) {
                $query->where('actif', filter_var($request->actif, FILTER_VALIDATE_BOOLEAN));
            }

            return DataTables::of($query)
                ->filterColumn('nom', function ($query, $keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('nom', 'like', "%{$keyword}%")
                            ->orWhere('qr_code', 'like', "%{$keyword}%");
                    });
                })
                ->make(true);
        } catch (\Exception $e) {
            return response()->json([
                'error'   => true,
                'message' => 'Erreur lors du chargement des données: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Affiche le formulaire de création d'un point de contrôle.
     */
    public function create()
    {
        $this->authorize('point-controle-create');
        $sites = Site::orderBy('nom_site')->get();
        return view('sie.pointcontroles.create', compact('sites'));
    }

    /**
     * Enregistre un nouveau point de contrôle en base de données.
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'site_id'     => 'required|exists:sites,id',
                'nom'         => 'required|string|max:255',
                'emplacement' => 'nullable|string|max:255',
                'ordre'       => 'required|integer|min:1',
                'actif'       => 'boolean',
            ]);

            $pointControle = PointControle::create([
                ...$validated,
                'qr_code' => $this->generateUniqueQRCode(),
                'actif'   => $validated['actif'] ?? true,
            ]);

            DB::commit();
            return redirect()->route('sie.pointcontroles.index')
                ->with('success', 'Point de contrôle créé avec succès');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur création point de contrôle: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Erreur lors de la création du point de contrôle');
        }
    }

    /**
     * Affiche le formulaire d'édition d'un point de contrôle.
     */
    public function edit(PointControle $pointControle)
    {
        $this->authorize('point-controle-update');
        $sites = Site::orderBy('nom_site')->get();
        return view('sie.pointcontroles.edit', compact('pointControle', 'sites'));
    }

    /**
     * Met à jour un point de contrôle existant.
     */
    public function update(Request $request, PointControle $pointControle)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'site_id'     => 'required|exists:sites,id',
                'nom'         => 'required|string|max:255',
                'emplacement' => 'nullable|string|max:255',
                'ordre'       => 'required|integer|min:1',
                'actif'       => 'boolean',
            ]);

            $pointControle->update([
                ...$validated,
                'actif' => $validated['actif'] ?? true,
            ]);

            DB::commit();
            return redirect()->route('sie.pointcontroles.index')
                ->with('success', 'Point de contrôle modifié avec succès');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur modification point de contrôle: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Erreur lors de la modification du point de contrôle');
        }
    }

    /**
     * Supprime un point de contrôle.
     */
    public function destroy(PointControle $pointControle)
    {
        $this->authorize('point-controle-delete');
        if ($pointControle->actif) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de supprimer un point de contrôle actif',
            ], 400);
        }

        try {
            $pointControle->delete();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Erreur suppression point de contrôle: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression',
            ]);
        }
    }

    /**
     * Permet de télécharger le QR Code (format SVG).
     */
    public function downloadQR(PointControle $pointControle)
    {
        $qrCode = $this->generateQRCodeSVG($pointControle->qr_code);

        return response($qrCode)
            ->header('Content-Type', 'image/svg+xml')
            ->header('Content-Disposition', 'attachment; filename="qrcode-' . $pointControle->id . '.svg"');
    }

    /**
     * Affiche un point de contrôle précis.
     */
    public function show(PointControle $pointControle)
    {
        $qrCode = $this->generateQRCodeSVG($pointControle->qr_code);
        return view('sie.pointcontroles.show', compact('pointControle', 'qrCode'));
    }

    /**
     * Génère un code aléatoire et s'assure qu'il n'existe pas déjà.
     */
    private function generateUniqueQRCode(): string
    {
        do {
            $code = Str::random(10);
        } while (PointControle::where('qr_code', $code)->exists());

        return $code;
    }

    /**
     * Génère le code QR (au format SVG) pour la valeur donnée.
     */
    private function generateQRCodeSVG(string $content): string
    {
        $options = new QROptions(['outputType' => QRMarkupSVG::class, 'svgViewBoxSize' => 400]);

        return (new QRCode($options))->render($content);
    }
}
