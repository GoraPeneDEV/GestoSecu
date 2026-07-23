@extends('layouts.contentNavbarLayout')

@section('title', 'Scan de ronde')

@section('content')
    <a href="{{ route('sie.rondes.show', $ronde->id) }}" class="btn btn-sm btn-outline-secondary mb-3">
        <i class="ti ti-arrow-left"></i> Retour
    </a>

    <h3 class="mb-4">Scan — {{ $ronde->planningRonde->nom ?? '' }}</h3>

    <div class="card mb-3" style="max-width: 600px;">
        <div class="card-body">
            <label class="form-label">Code du point de contrôle</label>
            <div class="input-group">
                <input type="text" id="qrInput" class="form-control" placeholder="Saisir ou scanner le code QR" autofocus>
                <button type="button" class="btn btn-primary" onclick="verifierCode()">Vérifier</button>
            </div>
            <div id="verifyMessage" class="mt-2"></div>
        </div>
    </div>

    <div class="card" style="max-width: 600px;">
        <div class="card-header">Points restants ({{ $pointsRestants->count() }})</div>
        <div class="card-body p-0">
            @if ($pointsRestants->isEmpty())
                <p class="text-muted p-3 mb-0">Tous les points ont été scannés.</p>
            @else
                <ol class="list-group list-group-numbered list-group-flush">
                    @foreach ($pointsRestants as $point)
                        <li class="list-group-item">{{ $point->nom }} <small class="text-muted">{{ $point->emplacement }}</small></li>
                    @endforeach
                </ol>
            @endif
        </div>
    </div>

    <div class="modal fade" id="modalAnomalie" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="anomalieForm" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalPointNom">Point de contrôle</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="point_controle_id">
                        <div class="mb-2">
                            <label class="form-label">État *</label>
                            <select id="anomalie" class="form-select" onchange="document.getElementById('anomalieDetails').classList.toggle('d-none', this.value !== '1')">
                                <option value="0">RAS</option>
                                <option value="1">Anomalie constatée</option>
                            </select>
                        </div>
                        <div id="anomalieDetails" class="d-none">
                            <div class="mb-2">
                                <label class="form-label">Type d'anomalie</label>
                                <input type="text" id="type_anomalie" class="form-control">
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Description</label>
                                <textarea id="description" class="form-control" rows="3"></textarea>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Photo</label>
                                <input type="file" id="photo" class="form-control" accept="image/*">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Valider le point</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
function verifierCode() {
    const code = document.getElementById('qrInput').value.trim();
    if (!code) return;

    fetch('{{ url('sie/rondes/' . $ronde->id . '/verify-qr') }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ qr_code: code }),
    })
        .then(r => r.json())
        .then(res => {
            const msg = document.getElementById('verifyMessage');
            if (res.success) {
                msg.innerHTML = '<div class="alert alert-success py-1 mb-0">' + res.message + '</div>';
                document.getElementById('point_controle_id').value = res.point_controle_id;
                document.getElementById('anomalieForm').reset();
                document.getElementById('anomalieDetails').classList.add('d-none');
                new bootstrap.Modal(document.getElementById('modalAnomalie')).show();
            } else {
                msg.innerHTML = '<div class="alert alert-danger py-1 mb-0">' + res.message + '</div>';
            }
        });
}

document.getElementById('anomalieForm').addEventListener('submit', function (e) {
    e.preventDefault();
    const formData = new FormData();
    formData.append('point_controle_id', document.getElementById('point_controle_id').value);
    formData.append('anomalie', document.getElementById('anomalie').value);
    formData.append('type_anomalie', document.getElementById('type_anomalie').value);
    formData.append('description', document.getElementById('description').value);
    const photo = document.getElementById('photo').files[0];
    if (photo) formData.append('photo', photo);

    fetch('{{ url('sie/rondes/' . $ronde->id . '/anomalie') }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: formData,
    })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                if (res.ronde_terminee) {
                    window.location = '{{ route('sie.rondes.show', $ronde->id) }}';
                } else {
                    window.location.reload();
                }
            } else {
                alert(res.message || 'Erreur');
            }
        });
});
</script>
@endpush
