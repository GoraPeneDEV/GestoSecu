@extends('layouts.contentNavbarLayout')

@section('title', 'Modifier la dotation')

@section('content')
    <h3 class="mb-4">Modifier la dotation {{ $dotation->reference }}</h3>

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card" style="max-width: 900px;">
        <div class="card-body">
            <form method="POST" action="{{ route('dotations.update', $dotation->id) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Date de dotation *</label>
                        <input type="date" name="date_dotation" class="form-control" value="{{ $dotation->date_dotation->format('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Type *</label>
                        <select name="type_dotation" class="form-select" required>
                            <option value="INITIALE" @selected($dotation->type_dotation == 'INITIALE')>Initiale</option>
                            <option value="RENOUVELLEMENT" @selected($dotation->type_dotation == 'RENOUVELLEMENT')>Renouvellement</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Bénéficiaire</label>
                        <input type="text" class="form-control" value="{{ $dotation->site->nom_site ?? ($dotation->employe->prenom . ' ' . $dotation->employe->nom ?? '-') }}" disabled>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Motif</label>
                        <input type="text" name="motif" class="form-control" value="{{ $dotation->motif }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Document justificatif</label>
                        @if ($dotation->document_path)
                            <p class="mb-1"><a href="{{ \Illuminate\Support\Facades\Storage::url($dotation->document_path) }}" target="_blank">Document actuel</a></p>
                        @endif
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
                    <a href="{{ route('dotations.show', $dotation->id) }}" class="btn btn-outline-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
const articlesData = @json($articles->map(fn($a) => ['id' => $a->id, 'designation' => $a->designation, 'stock' => $a->stock_actuel]));
const existingDetails = @json($dotation->details->map(fn($d) => ['article_id' => $d->article_id, 'quantite' => $d->quantite]));
let rowIndex = 0;

function addArticleRow(articleId = '', quantite = 1) {
    const wrapper = document.getElementById('articlesRows');
    const div = document.createElement('div');
    div.className = 'row g-2 mb-2 align-items-center article-row';
    let options = '<option value="">-- Article --</option>';
    articlesData.forEach(a => {
        const selected = a.id == articleId ? 'selected' : '';
        options += `<option value="${a.id}" ${selected}>${a.designation} (stock: ${a.stock})</option>`;
    });
    div.innerHTML = `
        <div class="col-md-8">
            <select name="articles[${rowIndex}][article_id]" class="form-select form-select-sm" required>${options}</select>
        </div>
        <div class="col-md-3">
            <input type="number" name="articles[${rowIndex}][quantite]" class="form-control form-control-sm" min="1" value="${quantite}" required>
        </div>
        <div class="col-md-1">
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.article-row').remove()"><i class="ti ti-x"></i></button>
        </div>
    `;
    wrapper.appendChild(div);
    rowIndex++;
}

if (existingDetails.length) {
    existingDetails.forEach(d => addArticleRow(d.article_id, d.quantite));
} else {
    addArticleRow();
}
</script>
@endpush
