<?php

namespace App\Http\Controllers\superviseur;

use Carbon\Carbon;
use App\Models\Site;
use App\Models\PointControleSup;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use chillerlan\QRCode\{QRCode, QROptions};
use chillerlan\QRCode\Output\QRMarkupSVG;
use Illuminate\Support\{Str, Facades\DB, Facades\Log, Facades\Storage};
use Yajra\DataTables\Facades\DataTables;

class PointControleSuperviseurController extends Controller
{
    public function index()
    {
        $this->authorize('point-controle-superviseur-view');
        $sites = Site::orderBy('nom_site')->get();
        $totalPointControles = PointControleSup::count();
        $pointControlesActifs = PointControleSup::where('actif', true)->count();
        $pointControlesInactifs = PointControleSup::where('actif', false)->count();

        return view('superviseur.pointcontroles.index', compact(
            'sites',
            'totalPointControles',
            'pointControlesActifs',
            'pointControlesInactifs'
        ));
    }

    public function getPointControles(Request $request)
    {
        try {
            $query = PointControleSup::with('site');

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
                'error' => true,
                'message' => 'Erreur: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function create()
    {
        $this->authorize('point-controle-superviseur-create');
        $sites = Site::orderBy('nom_site')->get();
        return view('superviseur.pointcontroles.create', compact('sites'));
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'site_id' => 'required|exists:sites,id',
                'nom' => 'required|string|max:255',
                'emplacement' => 'nullable|string|max:255',
                'ordre' => 'required|integer|min:1',
                'actif' => 'boolean',
            ]);

            PointControleSup::create([
                ...$validated,
                'qr_code' => $this->generateUniqueQRCode(),
                'actif' => $validated['actif'] ?? true,
            ]);

            DB::commit();
            return redirect()->route('superviseur.pointcontroles.index')
                ->with('success', 'Point de contrôle créé avec succès');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur création point de contrôle: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Erreur lors de la création');
        }
    }

    public function show(PointControleSup $pointControle)
    {
        $qrCode = $this->generateQRCodeSVG($pointControle->qr_code);
        return view('superviseur.pointcontroles.show', compact('pointControle', 'qrCode'));
    }

    public function edit(PointControleSup $pointControle)
    {
        $this->authorize('point-controle-superviseur-update');
        $sites = Site::orderBy('nom_site')->get();
        return view('superviseur.pointcontroles.edit', compact('pointControle', 'sites'));
    }

    public function update(Request $request, PointControleSup $pointControle)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'site_id' => 'required|exists:sites,id',
                'nom' => 'required|string|max:255',
                'emplacement' => 'nullable|string|max:255',
                'ordre' => 'required|integer|min:1',
                'actif' => 'boolean',
            ]);

            $pointControle->update([
                ...$validated,
                'actif' => $validated['actif'] ?? true,
            ]);

            DB::commit();
            return redirect()->route('superviseur.pointcontroles.index')
                ->with('success', 'Point de contrôle modifié avec succès');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur modification: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Erreur lors de la modification');
        }
    }

    public function destroy(PointControleSup $pointControle)
    {
        $this->authorize('point-controle-superviseur-delete');
        if ($pointControle->actif) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de supprimer un point actif'
            ], 400);
        }

        try {
            $pointControle->delete();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Erreur suppression: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression'
            ], 500);
        }
    }

    public function downloadQR(PointControleSup $pointControle)
    {
        $qrCode = $this->generateQRCodeSVG($pointControle->qr_code);
        return response($qrCode)
            ->header('Content-Type', 'image/svg+xml')
            ->header('Content-Disposition', 'attachment; filename="qrcode-' . $pointControle->id . '.svg"');
    }

    private function generateUniqueQRCode(): string
    {
        do {
            $code = Str::random(10);
        } while (PointControleSup::where('qr_code', $code)->exists());
        return $code;
    }

    private function generateQRCodeSVG(string $content): string
    {
        $options = new QROptions(['outputType' => QRMarkupSVG::class, 'svgViewBoxSize' => 400]);

        return (new QRCode($options))->render($content);
    }
}
