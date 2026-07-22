@extends('layouts.app')

@section('title', 'Demandes d\'explication')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Demandes d'explication</h3>
        <a href="{{ route('demandes-explications.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Nouvelle demande
        </a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4 col-6">
            <div class="card text-center h-100"><div class="card-body">
                <h3 class="mb-0">{{ $stats['total'] }}</h3><small class="text-muted">Total (ce mois)</small>
            </div></div>
        </div>
        <div class="col-md-4 col-6">
            <div class="card text-center h-100"><div class="card-body">
                <h3 class="mb-0 text-warning">{{ $stats['en_attente'] }}</h3><small class="text-muted">En attente</small>
            </div></div>
        </div>
        <div class="col-md-4 col-6">
            <div class="card text-center h-100"><div class="card-body">
                <h3 class="mb-0 text-success">{{ $stats['repondues'] }}</h3><small class="text-muted">Répondues</small>
            </div></div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="demandesTable" class="table table-hover w-100">
                    <thead>
                        <tr>
                            <th>Employé</th>
                            <th>Motif</th>
                            <th>Date incident</th>
                            <th>Statut</th>
                            <th>Document</th>
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
    var table = $('#demandesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('demandes-explications.data') }}',
        columns: [
            { data: 'employe_info', name: 'employe.prenom', orderable: false },
            { data: 'motif', name: 'motif' },
            { data: 'date_incident', name: 'date_incident' },
            { data: 'statut_badge', name: 'statut' },
            { data: 'document', name: 'document', orderable: false, searchable: false },
            { data: 'actions', name: 'actions', orderable: false, searchable: false },
        ],
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/fr-FR.json' },
    });

    $('#demandesTable').on('click', '.btn-view-demande', function () {
        var id = $(this).data('id');
        window.location.href = '/demandes-explications/' + id;
    });

    $('#demandesTable').on('click', '.btn-respond-demande', function () {
        var id = $(this).data('id');
        var dateReponse = prompt('Date de réponse (jj-mm-aaaa laisser vide = aujourd\'hui) :', new Date().toISOString().slice(0, 10));
        if (dateReponse === null) return;
        var input = document.createElement('input');
        input.type = 'file';
        input.onchange = function () {
            var formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('date_reponse', dateReponse);
            formData.append('reponse_document', input.files[0]);
            $.ajax({
                url: '/demandes-explications/' + id + '/repondre',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function () { table.ajax.reload(); },
                error: function (xhr) { alert(xhr.responseJSON?.message || 'Erreur'); }
            });
        };
        input.click();
    });

    $('#demandesTable').on('click', '.btn-delete-demande', function () {
        if (!confirm('Supprimer cette demande ?')) return;
        var id = $(this).data('id');
        $.ajax({
            url: '/demandes-explications/' + id,
            type: 'DELETE',
            data: { _token: '{{ csrf_token() }}' },
            success: function () { table.ajax.reload(); },
            error: function () { alert('Erreur'); }
        });
    });
});
</script>
@endpush
