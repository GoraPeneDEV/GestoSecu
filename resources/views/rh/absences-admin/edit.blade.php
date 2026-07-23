@extends('layouts.contentNavbarLayout')

@section('title', 'Modifier la demande d\'absence')

@section('content')
    <a href="{{ route('absences-admin.show', $demande) }}" class="btn btn-sm btn-outline-secondary mb-3">
        <i class="ti ti-arrow-left"></i> Retour
    </a>

    <h3 class="mb-4">Modifier la demande de {{ $demande->employe->prenom ?? '' }} {{ $demande->employe->nom ?? '' }}</h3>

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
            <form method="POST" action="{{ route('absences-admin.update', $demande) }}">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label">Type de demande *</label>
                    <select name="type_demande" class="form-select" required>
                        @foreach ([
                            'conge_annuel' => 'Congé annuel',
                            'autorisation_absence' => "Autorisation d'absence",
                            'conge_maladie' => 'Congé maladie',
                            'conge_maternite' => 'Congé maternité',
                            'conge_mariage' => 'Congé mariage',
                            'deces' => 'Décès',
                        ] as $value => $label)
                            <option value="{{ $value }}" @selected($demande->type_conges === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Date de début *</label>
                        <input type="date" name="date_debut" class="form-control" value="{{ $demande->date_debut?->format('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Date de fin *</label>
                        <input type="date" name="date_fin" class="form-control" value="{{ $demande->date_fin?->format('Y-m-d') }}" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Motif *</label>
                    <textarea name="motif" class="form-control" rows="3" required>{{ $demande->motif }}</textarea>
                </div>

                <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                <a href="{{ route('absences-admin.show', $demande) }}" class="btn btn-outline-secondary">Annuler</a>
            </form>
        </div>
    </div>
@endsection
