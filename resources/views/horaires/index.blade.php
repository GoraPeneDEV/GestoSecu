@extends('layouts.app')

@section('title', 'Horaires de planning')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Horaires de planning</h3>
        <a href="{{ route('horaires.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Nouvel horaire
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="horairesTable" class="table table-hover w-100">
                    <thead>
                        <tr>
                            <th>Libellé</th>
                            <th>Heures</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal édition -->
    <div class="modal fade" id="editHoraireModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editHoraireForm">
                    <div class="modal-header">
                        <h5 class="modal-title">Modifier l'horaire</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="edit_id">
                        <div class="mb-3">
                            <label class="form-label">Libellé</label>
                            <input type="text" id="edit_label" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Heure de début</label>
                            <input type="time" id="edit_heure_debut" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Heure de fin</label>
                            <input type="time" id="edit_heure_fin" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(function () {
    var table = $('#horairesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('horaires.data') }}',
        columns: [
            { data: 'label', name: 'label' },
            { data: 'heures', name: 'heures', orderable: false },
            { data: 'actions', name: 'actions', orderable: false, searchable: false },
        ],
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/fr-FR.json' },
    });

    var modal = new bootstrap.Modal(document.getElementById('editHoraireModal'));

    $('#horairesTable').on('click', '.btn-edit-horaire', function () {
        var id = $(this).data('id');
        $.get('/horaires/' + id + '/edit', function (data) {
            $('#edit_id').val(data.id);
            $('#edit_label').val(data.label);
            $('#edit_heure_debut').val(data.heure_debut);
            $('#edit_heure_fin').val(data.heure_fin);
            modal.show();
        });
    });

    $('#editHoraireForm').on('submit', function (e) {
        e.preventDefault();
        $.ajax({
            url: '/horaires/' + $('#edit_id').val(),
            type: 'PUT',
            data: {
                _token: '{{ csrf_token() }}',
                label: $('#edit_label').val(),
                heure_debut: $('#edit_heure_debut').val(),
                heure_fin: $('#edit_heure_fin').val(),
            },
            success: function () { location.reload(); },
            error: function () { alert('Erreur lors de la mise à jour'); }
        });
    });

    $('#horairesTable').on('click', '.btn-delete-horaire', function () {
        if (!confirm('Supprimer cet horaire ?')) return;
        var id = $(this).data('id');
        $.ajax({
            url: '/horaires/' + id,
            type: 'DELETE',
            data: { _token: '{{ csrf_token() }}' },
            success: function () { table.ajax.reload(); },
            error: function () { alert('Erreur lors de la suppression'); }
        });
    });
});
</script>
@endpush
