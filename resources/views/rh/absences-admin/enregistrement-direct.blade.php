@extends('layouts.contentNavbarLayout')

@section('title', 'Enregistrement direct d\'absence')

@section('content')
    <a href="{{ route('absences-admin.index') }}" class="btn btn-sm btn-outline-secondary mb-3">
        <i class="ti ti-arrow-left"></i> Retour
    </a>

    <h3 class="mb-4">Enregistrement direct d'une absence</h3>
    <p class="text-muted">Cette absence sera enregistrée directement au statut « Approuvé », sans passer par le circuit de validation habituel.</p>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card" style="max-width: 700px;">
        <div class="card-body">
            <form method="POST" action="{{ route('absences-admin.enregistrement-direct.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Employé *</label>
                    <select name="employe_id" class="form-select" required>
                        <option value="">-- Sélectionner --</option>
                        @foreach ($employes as $employe)
                            <option value="{{ $employe->id }}">{{ $employe->matricule }} — {{ $employe->prenom }} {{ $employe->nom }} (solde : {{ $employe->solde_conges ?? 0 }} j)</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Type de demande *</label>
                    <select name="type_demande" class="form-select" required>
                        <option value="conge_annuel">Congé annuel</option>
                        <option value="autorisation_absence">Autorisation d'absence</option>
                        <option value="conge_maladie">Congé maladie</option>
                        <option value="conge_maternite">Congé maternité</option>
                        <option value="conge_mariage">Congé mariage</option>
                        <option value="deces">Décès</option>
                    </select>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Date de début *</label>
                        <input type="date" name="date_debut" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Date de fin *</label>
                        <input type="date" name="date_fin" class="form-control" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Motif *</label>
                    <textarea name="motif" class="form-control" rows="3" required></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Document justificatif</label>
                    <input type="file" name="document" class="form-control">
                </div>

                <div class="form-check mb-3">
                    <input type="checkbox" name="a_deduire" value="1" class="form-check-input" id="aDeduire">
                    <label class="form-check-label" for="aDeduire">Déduire immédiatement du solde de congés de l'employé</label>
                </div>

                <button type="submit" class="btn btn-primary">Enregistrer l'absence</button>
                <a href="{{ route('absences-admin.index') }}" class="btn btn-outline-secondary">Annuler</a>
            </form>
        </div>
    </div>
@endsection
