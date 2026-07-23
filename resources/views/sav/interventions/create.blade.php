@extends('layouts.contentNavbarLayout')

@section('title', 'Nouvelle intervention')

@section('content')
    <h3 class="mb-4">Nouveau rapport d'intervention</h3>

    <div class="card" style="max-width: 900px;">
        <div class="card-body">
            @if (!$site)
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Sélectionner un site pour continuer</label>
                        <select id="siteChooser" class="form-select">
                            <option value="">-- Sélectionner --</option>
                            @foreach ($sites as $s)
                                <option value="{{ $s->id }}">{{ $s->nom_site }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            @else
                <form method="POST" action="{{ route('sav.interventions.store') }}">
                    @csrf
                    <input type="hidden" name="site_id" value="{{ $site->id }}">
                    @if ($maintenance)
                        <input type="hidden" name="maintenance_id" value="{{ $maintenance->id }}">
                    @endif

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Site</label>
                            <input type="text" class="form-control" value="{{ $site->nom_site }}" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Type *</label>
                            <select name="type" class="form-select" required>
                                <option value="maintenance_prevue" @selected($maintenance)>Maintenance prévue</option>
                                <option value="ponctuelle" @selected(!$maintenance)>Ponctuelle</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Date d'intervention *</label>
                            <input type="date" name="date_intervention" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Recommandations générales</label>
                            <textarea name="recommandations_generales" class="form-control" rows="3"></textarea>
                        </div>
                    </div>

                    <h5 class="mt-4">Appareils concernés</h5>
                    @if ($assets->isEmpty())
                        <p class="text-muted">Aucun appareil enregistré pour ce site.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm align-middle">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th>Appareil</th>
                                        <th>Actions faites</th>
                                        <th>Recommandation</th>
                                        <th>Statut après intervention</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($assets as $i => $asset)
                                        <tr>
                                            <td>
                                                <input type="hidden" name="assets[{{ $i }}][id]" value="{{ $asset->id }}">
                                                <input type="checkbox" name="assets[{{ $i }}][checked]" value="1" class="form-check-input">
                                            </td>
                                            <td>{{ $asset->label ?? $asset->type }} <small class="text-muted">{{ $asset->brand }} {{ $asset->model }}</small></td>
                                            <td><input type="text" name="assets[{{ $i }}][actions]" class="form-control form-control-sm"></td>
                                            <td><input type="text" name="assets[{{ $i }}][recommandation]" class="form-control form-control-sm"></td>
                                            <td>
                                                <select name="assets[{{ $i }}][statut]" class="form-select form-select-sm">
                                                    <option value="ok">OK</option>
                                                    <option value="a_surveiller">À surveiller</option>
                                                    <option value="defectueux">Défectueux</option>
                                                    <option value="remplace">Remplacé</option>
                                                </select>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    <button type="submit" class="btn btn-primary mt-3">Enregistrer le rapport</button>
                    <a href="{{ route('sav.interventions.index') }}" class="btn btn-outline-secondary mt-3">Annuler</a>
                </form>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.getElementById('siteChooser')?.addEventListener('change', function () {
    if (this.value) {
        window.location = '{{ route('sav.interventions.create') }}?site_id=' + this.value;
    }
});
</script>
@endpush
