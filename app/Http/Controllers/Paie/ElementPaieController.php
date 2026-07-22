<?php

namespace App\Http\Controllers\Paie;

use App\Http\Controllers\Controller;
use App\Models\ElementPaie;
use Illuminate\Http\Request;

class ElementPaieController extends Controller
{
  // Afficher la liste des éléments de paie
  public function index()
  {
    $this->authorize('elements-paie-view');
    $elements = ElementPaie::orderBy('ordre_affichage')->orderBy('libelle')->get();
    return view('paie.elements-paie.index', compact('elements'));
  }

  // Récupérer les éléments pour Datatables
  public function getElementsPaie()
  {
    $elements = ElementPaie::select('element_paie.*')->orderBy('ordre_affichage')->orderBy('libelle');

    return datatables()->of($elements)
      ->addColumn('type_badge', function ($element) {
        $badges = [
          ElementPaie::TYPE_GAIN => '<span class="badge bg-success">Gain</span>',
          ElementPaie::TYPE_RETENUE => '<span class="badge bg-warning">Retenue</span>',
          ElementPaie::TYPE_COTISATION_SALARIALE => '<span class="badge bg-info">Cotisation Salariale</span>',
          ElementPaie::TYPE_COTISATION_PATRONALE => '<span class="badge bg-primary">Cotisation Patronale</span>',
        ];
        return $badges[$element->type] ?? '<span class="badge bg-secondary">N/A</span>';
      })
      ->addColumn('mode_calcul_badge', function ($element) {
        $badges = [
          ElementPaie::MODE_FIXE => '<span class="badge bg-label-secondary">Fixe</span>',
          ElementPaie::MODE_POURCENTAGE => '<span class="badge bg-label-info">Pourcentage</span>',
          ElementPaie::MODE_FORMULE => '<span class="badge bg-label-warning">Formule</span>',
        ];
        return $badges[$element->mode_calcul] ?? '<span class="badge bg-secondary">N/A</span>';
      })
      ->addColumn('valeur_display', function ($element) {
        if ($element->mode_calcul === ElementPaie::MODE_FIXE) {
          return number_format($element->valeur, 0, ',', ' ') . ' FCFA';
        } elseif ($element->mode_calcul === ElementPaie::MODE_POURCENTAGE) {
          return $element->valeur . '%';
        } elseif ($element->mode_calcul === ElementPaie::MODE_FORMULE) {
          return '<span class="badge bg-label-dark">' . ($element->formule_classe ?? 'N/A') . '</span>';
        }
        return 'N/A';
      })
      ->addColumn('soumissions', function ($element) {
        $badges = '';
        if ($element->soumis_ipres) {
          $badges .= '<span class="badge bg-label-primary mx-1">IPRES</span>';
        }
        if ($element->soumis_css) {
          $badges .= '<span class="badge bg-label-success mx-1">CSS</span>';
        }
        if ($element->soumis_ipm) {
          $badges .= '<span class="badge bg-label-info mx-1">IPM</span>';
        }
        if ($element->soumis_ir) {
          $badges .= '<span class="badge bg-label-warning mx-1">IR</span>';
        }
        return $badges ?: '<span class="badge bg-label-secondary">Aucune</span>';
      })
      ->editColumn('actif', fn($element) => $element->actif
        ? '<span class="badge bg-success">Actif</span>'
        : '<span class="badge bg-secondary">Inactif</span>')
      ->addColumn('actions', fn($element) => '
            <button class="btn btn-sm btn-outline-warning btn-edit-element" data-id="' . $element->id . '">
                <i class="ti ti-pencil"></i>
            </button>
            <button class="btn btn-sm btn-outline-danger btn-delete-element" data-id="' . $element->id . '">
                <i class="ti ti-trash"></i>
            </button>
        ')
      ->rawColumns(['type_badge', 'mode_calcul_badge', 'valeur_display', 'soumissions', 'actif', 'actions'])
      ->make(true);
  }

  // Récupérer un élément spécifique pour modification
  public function edit(ElementPaie $element)
  {
    $this->authorize('elements-paie-edit');
    return response()->json($element);
  }

  // Stocker un nouvel élément
  public function store(Request $request)
  {
    $this->authorize('elements-paie-create');

    $request->validate([
      'code' => 'required|string|max:20|unique:element_paie,code',
      'libelle' => 'required|string|max:255',
      'type' => 'required|in:gain,retenue,cotisation_salariale,cotisation_patronale',
      'mode_calcul' => 'required|in:fixe,pourcentage,formule',
      'valeur' => 'nullable|numeric|min:0',
      'formule_classe' => 'nullable|string|max:255',
      'plafond_exoneration' => 'nullable|numeric|min:0',
      'ordre_affichage' => 'nullable|integer|min:0',
    ]);

    $data = $request->all();

    // Si aucun ordre n'est spécifié, mettre à la fin
    if (!isset($data['ordre_affichage'])) {
      $maxOrdre = ElementPaie::ofType($data['type'])->max('ordre_affichage') ?? 0;
      $data['ordre_affichage'] = $maxOrdre + 1;
    }

    // Convertir les checkboxes en booléens
    $data['soumis_ipres'] = $request->has('soumis_ipres');
    $data['soumis_css'] = $request->has('soumis_css');
    $data['soumis_ipm'] = $request->has('soumis_ipm');
    $data['soumis_ir'] = $request->has('soumis_ir');
    $data['afficher_bulletin'] = $request->has('afficher_bulletin');
    $data['actif'] = $request->has('actif');

    ElementPaie::create($data);
    return redirect()->route('paie.elements-paie.index')->with('success', 'Élément de paie créé avec succès.');
  }

  // Mettre à jour un élément existant
  public function update(Request $request, ElementPaie $element)
  {
    $this->authorize('elements-paie-edit');

    $request->validate([
      'code' => 'required|string|max:20|unique:element_paie,code,' . $element->id,
      'libelle' => 'required|string|max:255',
      'type' => 'required|in:gain,retenue,cotisation_salariale,cotisation_patronale',
      'mode_calcul' => 'required|in:fixe,pourcentage,formule',
      'valeur' => 'nullable|numeric|min:0',
      'formule_classe' => 'nullable|string|max:255',
      'plafond_exoneration' => 'nullable|numeric|min:0',
      'ordre_affichage' => 'nullable|integer|min:0',
    ]);

    $data = $request->all();

    // Convertir les checkboxes en booléens
    $data['soumis_ipres'] = $request->has('soumis_ipres');
    $data['soumis_css'] = $request->has('soumis_css');
    $data['soumis_ipm'] = $request->has('soumis_ipm');
    $data['soumis_ir'] = $request->has('soumis_ir');
    $data['afficher_bulletin'] = $request->has('afficher_bulletin');
    $data['actif'] = $request->has('actif');

    $element->update($data);
    return redirect()->route('paie.elements-paie.index')->with('success', 'Élément de paie mis à jour avec succès.');
  }

  // Supprimer un élément
  public function destroy($elementId)
  {
    $this->authorize('elements-paie-delete');
    $element = ElementPaie::findOrFail($elementId);
    $element->delete();
    return redirect()->route('paie.elements-paie.index')->with('success', 'Élément de paie supprimé avec succès.');
  }
}
