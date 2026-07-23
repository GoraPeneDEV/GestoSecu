@extends('layouts.contentNavbarLayout')

@section('title', 'Dotation ' . $dotation->reference)

@section('content')
    <a href="{{ route('dotations.index') }}" class="btn btn-sm btn-outline-secondary mb-3">
        <i class="ti ti-arrow-left"></i> Retour
    </a>

    <div class="d-flex justify-content-between align-items-start mb-4">
        <h3 class="mb-0">{{ $dotation->reference }}</h3>
        <a href="{{ route('dotations.edit', $dotation->id) }}" class="btn btn-warning btn-sm">
            <i class="ti ti-pencil"></i> Modifier
        </a>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">Détails</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-5">Date</dt><dd class="col-7">{{ $dotation->date_dotation->format('d/m/Y') }}</dd>
                        <dt class="col-5">Type</dt><dd class="col-7">{{ ucfirst(strtolower($dotation->type_dotation)) }}</dd>
                        <dt class="col-5">Bénéficiaire</dt>
                        <dd class="col-7">
                            @if ($dotation->site)
                                Site — {{ $dotation->site->nom_site }}
                            @elseif ($dotation->employe)
                                Employé — {{ $dotation->employe->prenom }} {{ $dotation->employe->nom }}
                            @else
                                -
                            @endif
                        </dd>
                        <dt class="col-5">Motif</dt><dd class="col-7">{{ $dotation->motif ?? '-' }}</dd>
                        <dt class="col-5">Créée par</dt><dd class="col-7">{{ $dotation->createur->nom_complet ?? '-' }}</dd>
                        @if ($dotation->document_path)
                            <dt class="col-5">Document</dt>
                            <dd class="col-7"><a href="{{ \Illuminate\Support\Facades\Storage::url($dotation->document_path) }}" target="_blank">Voir le document</a></dd>
                        @endif
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">Articles dotés</div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Article</th>
                                <th>Quantité</th>
                                <th>Statut</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($dotation->details as $detail)
                                <tr>
                                    <td>{{ $detail->article->designation ?? '-' }}</td>
                                    <td>{{ $detail->quantite }}</td>
                                    <td>
                                        @if ($detail->is_returned)
                                            <span class="badge bg-secondary">Retourné ({{ $detail->statut_retour }})</span>
                                        @else
                                            <span class="badge bg-success">En cours</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if (!$detail->is_returned)
                                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="returnDetail({{ $detail->id }})">Retourner</button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalReturn" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="returnForm">
                    <div class="modal-header">
                        <h5 class="modal-title">Retour d'article</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="return_detail_id">
                        <div class="mb-2">
                            <label class="form-label">Statut du retour *</label>
                            <select id="return_statut" class="form-select" required>
                                <option value="recyclable">Recyclable (remis en stock)</option>
                                <option value="non_recyclable">Non recyclable</option>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Observation</label>
                            <textarea id="return_observation" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Confirmer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
function returnDetail(id) {
    document.getElementById('return_detail_id').value = id;
    new bootstrap.Modal(document.getElementById('modalReturn')).show();
}

document.getElementById('returnForm').addEventListener('submit', function (e) {
    e.preventDefault();
    fetch('{{ route('dotations.detail.return') }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({
            detail_id: document.getElementById('return_detail_id').value,
            statut_retour: document.getElementById('return_statut').value,
            observation: document.getElementById('return_observation').value,
        }),
    })
        .then(r => r.json())
        .then(res => {
            if (res.success) { window.location.reload(); } else { alert(res.message); }
        });
});
</script>
@endpush
