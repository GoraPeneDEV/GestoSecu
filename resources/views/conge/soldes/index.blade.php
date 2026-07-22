@extends('layouts.app')

@section('title', 'Soldes de congés')

@section('content')
    <h3 class="mb-4">Soldes de congés</h3>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Employé</th>
                            <th>Département</th>
                            <th>Solde (jours)</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($employes as $employe)
                            <tr>
                                <td>{{ $employe->prenom }} {{ $employe->nom }}</td>
                                <td>{{ $employe->departement->nom ?? '-' }}</td>
                                <td>{{ $employe->solde_conges ?? 0 }}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-ajuster" data-id="{{ $employe->id }}" data-nom="{{ $employe->prenom }} {{ $employe->nom }}">
                                        <i class="bi bi-pencil-square"></i> Ajuster
                                    </button>
                                    <a href="{{ route('conge.soldes.historique', $employe->id) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-clock-history"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="ajusterModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="ajusterForm">
                    <div class="modal-header">
                        <h5 class="modal-title">Ajuster le solde de <span id="ajuster_nom"></span></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="ajuster_employe_id">
                        <div class="mb-3">
                            <label class="form-label">Type</label>
                            <select id="ajuster_type" class="form-select" required>
                                <option value="ajout">Ajout</option>
                                <option value="retrait">Retrait</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Montant (jours)</label>
                            <input type="number" id="ajuster_montant" class="form-control" min="1" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Commentaire (min. 10 caractères)</label>
                            <textarea id="ajuster_commentaire" class="form-control" rows="2" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Valider</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(function () {
    var modal = new bootstrap.Modal(document.getElementById('ajusterModal'));

    $('.btn-ajuster').on('click', function () {
        $('#ajuster_employe_id').val($(this).data('id'));
        $('#ajuster_nom').text($(this).data('nom'));
        modal.show();
    });

    $('#ajusterForm').on('submit', function (e) {
        e.preventDefault();
        $.ajax({
            url: '{{ route('conge.soldes.ajuster') }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                employe_id: $('#ajuster_employe_id').val(),
                type: $('#ajuster_type').val(),
                montant: $('#ajuster_montant').val(),
                commentaire: $('#ajuster_commentaire').val(),
            },
            success: function () { location.reload(); },
            error: function (xhr) { alert(xhr.responseJSON?.message || 'Erreur'); }
        });
    });
});
</script>
@endpush
