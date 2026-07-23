@extends('layouts.contentNavbarLayout')

@section('title', 'Absences — Mon département')

@section('content')
    <a href="{{ route('absences-admin.index') }}" class="btn btn-sm btn-outline-secondary mb-3">
        <i class="ti ti-arrow-left"></i> Retour
    </a>

    <h3 class="mb-4">Absences — {{ $deptNom ?? 'Mon département' }}</h3>

    <div class="row g-3 mb-4">
        <div class="col-md-2 col-6">
            <div class="card text-center h-100"><div class="card-body"><h4 class="mb-0">{{ $stats['total'] }}</h4><small class="text-muted">Total</small></div></div>
        </div>
        <div class="col-md-2 col-6">
            <div class="card text-center h-100"><div class="card-body"><h4 class="mb-0 text-warning">{{ $stats['en_attente'] }}</h4><small class="text-muted">En attente</small></div></div>
        </div>
        <div class="col-md-2 col-6">
            <div class="card text-center h-100"><div class="card-body"><h4 class="mb-0 text-info">{{ $stats['en_cours'] }}</h4><small class="text-muted">En cours</small></div></div>
        </div>
        <div class="col-md-2 col-6">
            <div class="card text-center h-100"><div class="card-body"><h4 class="mb-0 text-success">{{ $stats['approuvees'] }}</h4><small class="text-muted">Approuvées</small></div></div>
        </div>
        <div class="col-md-2 col-6">
            <div class="card text-center h-100"><div class="card-body"><h4 class="mb-0 text-danger">{{ $stats['refusees'] }}</h4><small class="text-muted">Refusées</small></div></div>
        </div>
        <div class="col-md-2 col-6">
            <div class="card text-center h-100"><div class="card-body"><h4 class="mb-0">{{ $stats['annulees'] }}</h4><small class="text-muted">Annulées</small></div></div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="deptTable" class="table table-hover w-100">
                    <thead>
                        <tr>
                            <th>Employé</th>
                            <th>Type</th>
                            <th>Période</th>
                            <th>Statut</th>
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
    var table = $('#deptTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('absences-admin.departement-data') }}',
        columns: [
            { data: 'employe_nom', name: 'employe_nom' },
            { data: 'type_label', name: 'type_conges' },
            { data: 'periode', name: 'periode', orderable: false },
            { data: 'statut_badge', name: 'statut' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false },
        ],
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/fr-FR.json' },
    });

    $('#deptTable').on('click', '.btn-dept-val-sup', function () {
        var id = $(this).data('id');
        var decision = confirm('OK = Valider, Annuler = Refuser') ? 'valider' : 'refuser';
        $.post('/absences-admin/' + id + '/validation-superieur', { _token: '{{ csrf_token() }}', decision: decision })
            .done(function () { table.ajax.reload(); })
            .fail(function () { alert('Erreur'); });
    });

    $('#deptTable').on('click', '.btn-dept-annuler, .btn-annuler-createur', function () {
        var id = $(this).data('id');
        var isCreateur = $(this).hasClass('btn-annuler-createur');
        var motif = prompt('Motif d\'annulation (min. 10 caractères) :');
        if (!motif) return;
        var url = isCreateur ? '/absences-admin/' + id + '/annuler-createur' : '/absences-admin/' + id + '/annuler';
        var field = isCreateur ? 'motif_annulation' : 'motif_annulation_rh';
        var data = { _token: '{{ csrf_token() }}' };
        data[field] = motif;
        $.post(url, data)
            .done(function () { table.ajax.reload(); })
            .fail(function () { alert('Erreur'); });
    });

    $('#deptTable').on('click', '.btn-dept-supprimer', function () {
        if (!confirm('Supprimer cette demande ?')) return;
        var id = $(this).data('id');
        $.ajax({
            url: '/absences-admin/' + id,
            type: 'DELETE',
            data: { _token: '{{ csrf_token() }}' },
            success: function () { table.ajax.reload(); },
            error: function () { alert('Erreur'); }
        });
    });
});
</script>
@endpush
