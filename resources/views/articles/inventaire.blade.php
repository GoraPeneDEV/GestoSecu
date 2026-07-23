@extends('layouts.contentNavbarLayout')

@section('title', "Rapport d'inventaire")

@section('content')
    <a href="{{ route('articles.index') }}" class="btn btn-sm btn-outline-secondary mb-3">
        <i class="ti ti-arrow-left"></i> Retour
    </a>

    <h3 class="mb-4">Rapport d'inventaire</h3>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-2 align-items-end">
                <div class="col-auto">
                    <label class="form-label">Mois</label>
                    <select id="mois" class="form-select form-select-sm">
                        @foreach (['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'] as $i => $nom)
                            <option value="{{ $i + 1 }}" @selected(($i + 1) == $moisActuel)>{{ $nom }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <label class="form-label">Année</label>
                    <input type="number" id="annee" class="form-control form-control-sm" value="{{ $anneeActuelle }}">
                </div>
                <div class="col-auto">
                    <label class="form-label">Département</label>
                    <select id="departement_id" class="form-select form-select-sm">
                        <option value="">Tous</option>
                        @foreach ($departements as $dep)
                            <option value="{{ $dep->id }}">{{ $dep->nom }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-sm btn-primary" onclick="genererRapport()">Générer</button>
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exporterPdf()">
                        <i class="ti ti-file-pdf"></i> Exporter PDF
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div id="statsRow" class="row g-3 mb-3 d-none">
        <div class="col-md-3"><div class="card text-center"><div class="card-body"><h5 id="stat_total" class="mb-0"></h5><small class="text-muted">Articles</small></div></div></div>
        <div class="col-md-3"><div class="card text-center"><div class="card-body"><h5 id="stat_valeur" class="mb-0"></h5><small class="text-muted">Valeur totale</small></div></div></div>
        <div class="col-md-3"><div class="card text-center"><div class="card-body"><h5 id="stat_rupture" class="mb-0"></h5><small class="text-muted">En rupture</small></div></div></div>
        <div class="col-md-3"><div class="card text-center"><div class="card-body"><h5 id="stat_bon" class="mb-0"></h5><small class="text-muted">Bon stock</small></div></div></div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm" id="inventaireTable">
                    <thead>
                        <tr>
                            <th>Référence</th>
                            <th>Désignation</th>
                            <th>Département</th>
                            <th>Stock</th>
                            <th>Valeur</th>
                            <th>Observation</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
function genererRapport() {
    fetch('{{ route('articles.inventaire.generer') }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({
            mois: document.getElementById('mois').value,
            annee: document.getElementById('annee').value,
            departement_id: document.getElementById('departement_id').value || null,
        }),
    })
        .then(r => r.json())
        .then(res => {
            if (!res.success) { alert(res.message || 'Erreur'); return; }
            document.getElementById('statsRow').classList.remove('d-none');
            document.getElementById('stat_total').textContent = res.statistiques.total_articles;
            document.getElementById('stat_valeur').textContent = new Intl.NumberFormat('fr-FR').format(res.statistiques.valeur_totale) + ' FCFA';
            document.getElementById('stat_rupture').textContent = res.statistiques.articles_rupture;
            document.getElementById('stat_bon').textContent = res.statistiques.articles_bon_stock;

            const tbody = document.querySelector('#inventaireTable tbody');
            tbody.innerHTML = '';
            res.data.forEach(row => {
                const tr = document.createElement('tr');
                tr.innerHTML = `<td>${row.reference}</td><td>${row.designation}</td><td>${row.departement}</td>
                    <td>${row.stock_actuel}</td><td>${new Intl.NumberFormat('fr-FR').format(row.valeur_stock)} FCFA</td>
                    <td>${row.observation}</td>`;
                tbody.appendChild(tr);
            });
        });
}

function exporterPdf() {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route('articles.inventaire.export-pdf') }}';
    form.innerHTML = `
        @csrf
        <input type="hidden" name="mois" value="${document.getElementById('mois').value}">
        <input type="hidden" name="annee" value="${document.getElementById('annee').value}">
        <input type="hidden" name="departement_id" value="${document.getElementById('departement_id').value}">
    `;
    document.body.appendChild(form);
    form.submit();
}
</script>
@endpush
