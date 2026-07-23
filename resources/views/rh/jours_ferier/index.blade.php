@extends('layouts.contentNavbarLayout')

@section('title', 'Jours fériés')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Jours fériés</h3>
        <div>
            <a href="{{ route('jours_ferier.trashed') }}" class="btn btn-outline-secondary">
                <i class="ti ti-trash"></i> Corbeille
            </a>
            <a href="{{ route('jours_ferier.create') }}" class="btn btn-primary">
                <i class="ti ti-plus-lg"></i> Nouveau
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="joursTable" class="table table-hover w-100">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editJourModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editJourForm">
                    <div class="modal-header">
                        <h5 class="modal-title">Modifier le jour férié</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="edit_id">
                        <div class="mb-3">
                            <label class="form-label">Date</label>
                            <input type="date" id="edit_date_ferier" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <input type="text" id="edit_description" class="form-control">
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
    var table = $('#joursTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('jours_ferier.data') }}',
        columns: [
            { data: 'date_ferier', name: 'date_ferier' },
            { data: 'description', name: 'description' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false },
        ],
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/fr-FR.json' },
    });

    var modal = new bootstrap.Modal(document.getElementById('editJourModal'));

    $('#joursTable').on('click', '.btn-edit-jour', function () {
        var id = $(this).data('id');
        $.get('/jours_ferier/' + id + '/edit', function (data) {
            $('#edit_id').val(data.id);
            $('#edit_date_ferier').val(data.date_ferier);
            $('#edit_description').val(data.description);
            modal.show();
        });
    });

    $('#editJourForm').on('submit', function (e) {
        e.preventDefault();
        $.ajax({
            url: '/jours_ferier/' + $('#edit_id').val(),
            type: 'PUT',
            data: {
                _token: '{{ csrf_token() }}',
                date_ferier: $('#edit_date_ferier').val(),
                description: $('#edit_description').val(),
            },
            success: function () { modal.hide(); table.ajax.reload(); },
            error: function (xhr) { alert(xhr.responseJSON?.message || 'Erreur'); }
        });
    });

    $('#joursTable').on('click', '.btn-delete-jour', function () {
        if (!confirm('Supprimer ce jour férié ?')) return;
        var id = $(this).data('id');
        $.ajax({
            url: '/jours_ferier/' + id,
            type: 'DELETE',
            data: { _token: '{{ csrf_token() }}' },
            success: function () { table.ajax.reload(); },
            error: function () { alert('Erreur lors de la suppression'); }
        });
    });
});
</script>
@endpush
