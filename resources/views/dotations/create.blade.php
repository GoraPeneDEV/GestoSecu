@extends('layouts.contentNavbarLayout')

@section('title', 'Nouvelle dotation')

@section('content')
    <h3 class="mb-4">Nouvelle dotation</h3>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card" style="max-width: 900px;">
        <div class="card-body">
            <form id="dotationForm" method="POST" action="{{ route('dotations.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Date de dotation *</label>
                        <input type="date" name="date_dotation" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Type *</label>
                        <select name="type_dotation" class="form-select" required>
                            <option value="INITIALE">Initiale</option>
                            <option value="RENOUVELLEMENT">Renouvellement</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Bénéficiaire *</label>
                        <select id="cible" name="cible" class="form-select" required>
                            <option value="site">Site</option>
                            <option value="employe">Employé</option>
                        </select>
                    </div>
                    <div class="col-md-6" id="siteWrapper">
                        <label class="form-label">Site *</label>
                        <select name="site_id" class="form-select">
                            <option value="">-- Sélectionner --</option>
                            @foreach ($sites as $site)
                                <option value="{{ $site->id }}">{{ $site->nom_site }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 d-none" id="employeWrapper">
                        <label class="form-label">Employé *</label>
                        <select name="employe_id" class="form-select">
                            <option value="">-- Sélectionner --</option>
                            @foreach ($employes as $employe)
                                <option value="{{ $employe->id }}">{{ $employe->matricule }} — {{ $employe->prenom }} {{ $employe->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Motif</label>
                        <input type="text" name="motif" class="form-control">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Document justificatif</label>
                        <input type="file" name="document" class="form-control">
                    </div>
                </div>

                <h5 class="mt-4">Articles</h5>
                <div id="articlesRows"></div>
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="addArticleRow()">
                    <i class="ti ti-plus-lg"></i> Ajouter un article
                </button>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                    <a href="{{ route('dotations.index') }}" class="btn btn-outline-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
const articlesData = @json($articles->map(fn($a) => ['id' => $a->id, 'designation' => $a->designation, 'stock' => $a->stock_actuel]));
let rowIndex = 0;

function addArticleRow() {
    const wrapper = document.getElementById('articlesRows');
    const div = document.createElement('div');
    div.className = 'row g-2 mb-2 align-items-center article-row';
    let options = '<option value="">-- Article --</option>';
    articlesData.forEach(a => {
        options += `<option value="${a.id}">${a.designation} (stock: ${a.stock})</option>`;
    });
    div.innerHTML = `
        <div class="col-md-8">
            <select name="articles[${rowIndex}][article_id]" class="form-select form-select-sm" required>${options}</select>
        </div>
        <div class="col-md-3">
            <input type="number" name="articles[${rowIndex}][quantite]" class="form-control form-control-sm" min="1" value="1" required>
        </div>
        <div class="col-md-1">
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.article-row').remove()"><i class="ti ti-x"></i></button>
        </div>
    `;
    wrapper.appendChild(div);
    rowIndex++;
}

document.getElementById('cible').addEventListener('change', function () {
    document.getElementById('siteWrapper').classList.toggle('d-none', this.value !== 'site');
    document.getElementById('employeWrapper').classList.toggle('d-none', this.value !== 'employe');
});

addArticleRow();

document.getElementById('dotationForm').addEventListener('submit', function (e) {
    e.preventDefault();
    fetch(this.action, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: new FormData(this),
    })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                window.location = '{{ route('dotations.index') }}';
            } else {
                alert(res.message || 'Erreur');
            }
        });
});
</script>
@endpush
