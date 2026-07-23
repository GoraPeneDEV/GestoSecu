@extends('layouts.contentNavbarLayout')

@section('title', 'Ronde #' . $ronde->id)

@section('content')
    <a href="{{ route('sie.rondes.index') }}" class="btn btn-sm btn-outline-secondary mb-3">
        <i class="ti ti-arrow-left"></i> Retour
    </a>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="d-flex justify-content-between align-items-start mb-4">
        <h3 class="mb-0">Ronde — {{ $ronde->planningRonde->nom ?? '' }}</h3>
        <div>
            @if ($ronde->statut === 'en_cours')
                <a href="{{ route('sie.rondes.scan', $ronde->id) }}" class="btn btn-primary btn-sm">
                    <i class="ti ti-qr-code-scan"></i> Scanner
                </a>
                <button type="button" class="btn btn-warning btn-sm" onclick="terminerRonde()">
                    <i class="ti ti-check-square"></i> Terminer
                </button>
            @endif
            @if ($ronde->scans->where('anomalie', true)->count() > 0)
                <a href="{{ route('sie.rondes.export-anomalies', $ronde->id) }}" class="btn btn-outline-danger btn-sm">
                    <i class="ti ti-file-pdf"></i> Exporter les anomalies
                </a>
            @endif
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">Détails</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-5">Agent</dt><dd class="col-7">{{ $ronde->agent->prenom ?? '' }} {{ $ronde->agent->nom ?? '-' }}</dd>
                        <dt class="col-5">Site</dt><dd class="col-7">{{ $ronde->planningRonde->site->nom_site ?? '-' }}</dd>
                        <dt class="col-5">Début</dt><dd class="col-7">{{ $ronde->date_debut?->format('d/m/Y H:i') }}</dd>
                        <dt class="col-5">Fin</dt><dd class="col-7">{{ $ronde->date_fin?->format('d/m/Y H:i') ?? '-' }}</dd>
                        <dt class="col-5">Statut</dt>
                        <dd class="col-7">
                            @if ($ronde->statut === 'en_cours')
                                <span class="badge bg-warning">En cours</span>
                            @else
                                <span class="badge bg-success">Terminée</span>
                            @endif
                        </dd>
                        <dt class="col-5">Commentaire</dt><dd class="col-7">{{ $ronde->commentaire ?? '-' }}</dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">Points de contrôle</div>
                <div class="card-body p-0">
                    @php $scannedIds = $ronde->scans->pluck('point_controle_id')->toArray(); @endphp
                    <ul class="list-group list-group-flush">
                        @foreach ($ronde->planningRonde->pointsControle ?? [] as $point)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                {{ $point->nom }}
                                @if (in_array($point->id, $scannedIds))
                                    <span class="badge bg-success">Scanné</span>
                                @else
                                    <span class="badge bg-secondary">En attente</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">Scans effectués</div>
        <div class="card-body p-0">
            @if ($ronde->scans->isEmpty())
                <p class="text-muted p-3 mb-0">Aucun scan enregistré.</p>
            @else
                <table class="table table-sm mb-0">
                    <thead>
                        <tr>
                            <th>Point</th>
                            <th>Date</th>
                            <th>Anomalie</th>
                            <th>Commentaire</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($ronde->scans as $scan)
                            <tr class="{{ $scan->anomalie ? 'table-danger' : '' }}">
                                <td>{{ $scan->pointControle->nom ?? '-' }}</td>
                                <td>{{ $scan->date_scan?->format('d/m/Y H:i') }}</td>
                                <td>{{ $scan->anomalie ? ($scan->type_anomalie ?? 'Oui') : 'Non' }}</td>
                                <td>{{ $scan->commentaire ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
<script>
function terminerRonde() {
    if (!confirm('Terminer cette ronde ?')) return;
    fetch('{{ url('sie/rondes/' . $ronde->id . '/terminer') }}', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify({}),
    })
        .then(r => r.json())
        .then(res => {
            if (res.success) { window.location.reload(); } else { alert(res.message); }
        });
}
</script>
@endpush
