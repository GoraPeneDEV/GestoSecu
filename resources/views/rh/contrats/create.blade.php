@extends('layouts.contentNavbarLayout')

@section('title', 'Nouveau contrat')

@section('content')
    <h3 class="mb-4">Nouveau contrat</h3>

    <div class="card" style="max-width: 700px;">
        <div class="card-body">
            <form method="POST" action="{{ route('contrats.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Employé *</label>
                    <select name="id_employe" class="form-select" required>
                        <option value="">-- Sélectionner --</option>
                        @foreach ($employes as $employe)
                            <option value="{{ $employe->id }}">{{ $employe->prenom }} {{ $employe->nom }} ({{ $employe->matricule }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Type de contrat *</label>
                    <select name="type_contrat" id="type_contrat" class="form-select" required>
                        <option value="CDI">CDI</option>
                        <option value="CDD">CDD</option>
                        <option value="Stage">Stage</option>
                        <option value="Prestation de service">Prestation de service</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Date de début *</label>
                    <input type="date" name="date_debut" class="form-control" required>
                </div>
                <div class="mb-3" id="date_fin_group">
                    <label class="form-label">Date de fin</label>
                    <input type="date" name="date_fin" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">Salaire (FCFA) *</label>
                    <input type="number" name="montant" class="form-control" min="0" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Motif</label>
                    <textarea name="motif" class="form-control" rows="2"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Document (PDF)</label>
                    <input type="file" name="document" class="form-control" accept="application/pdf">
                </div>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
                <a href="{{ route('contrats.index') }}" class="btn btn-outline-secondary">Annuler</a>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.getElementById('type_contrat').addEventListener('change', function () {
    var dateFin = document.querySelector('#date_fin_group input');
    dateFin.required = this.value !== 'CDI';
});
</script>
@endpush
