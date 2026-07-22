@extends('layouts.app')

@section('title', 'Nouveau planning de ronde')

@section('content')
    <h3 class="mb-4">Nouveau planning de ronde</h3>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card" style="max-width: 800px;">
        <div class="card-body">
            <form id="planningForm" method="POST" action="{{ route('sie.plannings-ronde.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nom *</label>
                        <input type="text" name="nom" class="form-control" value="{{ old('nom') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Site *</label>
                        <select id="site_id" name="site_id" class="form-select" required>
                            <option value="">-- Sélectionner --</option>
                            @foreach ($sites as $site)
                                <option value="{{ $site->id }}">{{ $site->nom_site }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Fréquence *</label>
                        <select name="frequence" class="form-select" required>
                            <option value="quotidienne">Quotidienne</option>
                            <option value="hebdomadaire">Hebdomadaire</option>
                            <option value="mensuelle">Mensuelle</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Heure de début *</label>
                        <input type="time" name="heure_debut" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Durée estimée (minutes) *</label>
                        <input type="number" name="duree_estimee" class="form-control" min="1" required>
                    </div>
                </div>

                <h5 class="mt-4">Points de contrôle (dans l'ordre de passage)</h5>
                <p class="text-muted small">Sélectionnez un site pour charger ses points de contrôle, puis cliquez pour les ajouter dans l'ordre souhaité.</p>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">Disponibles</div>
                            <ul id="availablePoints" class="list-group list-group-flush"></ul>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">Sélectionnés (ordre)</div>
                            <ul id="selectedPoints" class="list-group list-group-flush"></ul>
                        </div>
                    </div>
                </div>
                <div id="hiddenPoints"></div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                    <a href="{{ route('sie.plannings-ronde.index') }}" class="btn btn-outline-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
let selected = [];
let available = [];

function renderLists() {
    const availableList = document.getElementById('availablePoints');
    const selectedList = document.getElementById('selectedPoints');
    const hidden = document.getElementById('hiddenPoints');

    availableList.innerHTML = '';
    available.filter(p => !selected.find(s => s.id === p.id)).forEach(p => {
        const li = document.createElement('li');
        li.className = 'list-group-item list-group-item-action';
        li.style.cursor = 'pointer';
        li.textContent = p.nom + (p.emplacement ? ' — ' + p.emplacement : '');
        li.onclick = () => { selected.push(p); renderLists(); };
        availableList.appendChild(li);
    });

    selectedList.innerHTML = '';
    hidden.innerHTML = '';
    selected.forEach((p, i) => {
        const li = document.createElement('li');
        li.className = 'list-group-item d-flex justify-content-between align-items-center';
        li.innerHTML = `<span>${i + 1}. ${p.nom}</span>`;
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'btn btn-sm btn-outline-danger';
        btn.innerHTML = '<i class="bi bi-x"></i>';
        btn.onclick = () => { selected = selected.filter(s => s.id !== p.id); renderLists(); };
        li.appendChild(btn);
        selectedList.appendChild(li);

        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = `points_controle[${i}]`;
        input.value = p.id;
        hidden.appendChild(input);
    });
}

document.getElementById('site_id').addEventListener('change', function () {
    selected = [];
    available = [];
    if (!this.value) { renderLists(); return; }
    fetch('{{ url('sie/plannings-ronde/points-controle') }}/' + this.value)
        .then(r => r.json())
        .then(points => { available = points; renderLists(); });
});
</script>
@endpush
