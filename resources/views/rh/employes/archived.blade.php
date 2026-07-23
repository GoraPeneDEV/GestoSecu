@extends('layouts.contentNavbarLayout')

@section('title', 'Employés archivés')

@section('content')
    <a href="{{ route('employes.index') }}" class="btn btn-sm btn-outline-secondary mb-3">
        <i class="ti ti-arrow-left"></i> Retour
    </a>

    <h3 class="mb-4">Employés archivés ({{ $totalArchived }})</h3>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="archivedTable" class="table table-hover w-100">
                    <thead>
                        <tr>
                            <th>Employé</th>
                            <th>Date d'arrêt</th>
                            <th>Motif</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(function () {
    var table = $('#archivedTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('employes.archivedData') }}',
        columns: [
            { data: 'employe_info', name: 'employe_info', orderable: false },
            { data: 'arret', name: 'arret' },
            { data: 'motif_arret', name: 'motif_arret' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false },
        ],
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/fr-FR.json' },
    });

    $('#archivedTable').on('click', '.btn-unarchive-employe', function () {
        if (!confirm('Désarchiver cet employé ?')) return;
        var id = $(this).data('id');
        $.ajax({
            url: '/employes/' + id + '/unarchive',
            type: 'PUT',
            data: { _token: '{{ csrf_token() }}' },
            success: function () { table.ajax.reload(); },
            error: function () { alert('Erreur lors du désarchivage'); }
        });
    });
});
</script>
@endpush
