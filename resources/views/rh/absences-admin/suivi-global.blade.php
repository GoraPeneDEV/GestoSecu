@extends('layouts.contentNavbarLayout')

@section('title', 'Suivi global des absences')

@section('content')
    <a href="{{ route('absences-admin.index') }}" class="btn btn-sm btn-outline-secondary mb-3">
        <i class="ti ti-arrow-left"></i> Retour
    </a>

    <h3 class="mb-4">Suivi global des absences</h3>

    <div class="row g-3 mb-4">
        <div class="col-md-2 col-6">
            <div class="card text-center h-100"><div class="card-body"><h4 class="mb-0">{{ $stats['total'] }}</h4><small class="text-muted">Total</small></div></div>
        </div>
        <div class="col-md-2 col-6">
            <div class="card text-center h-100"><div class="card-body"><h4 class="mb-0 text-warning">{{ $stats['en_attente'] }}</h4><small class="text-muted">En attente</small></div></div>
        </div>
        <div class="col-md-2 col-6">
            <div class="card text-center h-100"><div class="card-body"><h4 class="mb-0 text-info">{{ $stats['valide_superieur'] }}</h4><small class="text-muted">Validé responsable</small></div></div>
        </div>
        <div class="col-md-2 col-6">
            <div class="card text-center h-100"><div class="card-body"><h4 class="mb-0 text-success">{{ $stats['valide_rh'] }}</h4><small class="text-muted">Approuvé</small></div></div>
        </div>
        <div class="col-md-2 col-6">
            <div class="card text-center h-100"><div class="card-body"><h4 class="mb-0 text-danger">{{ $stats['refuse'] }}</h4><small class="text-muted">Refusé</small></div></div>
        </div>
        <div class="col-md-2 col-6">
            <div class="card text-center h-100"><div class="card-body"><h4 class="mb-0">{{ $stats['annule'] }}</h4><small class="text-muted">Annulé</small></div></div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-2 align-items-end">
                @if ($isRH || $isDirection)
                    <div class="col-auto">
                        <label class="form-label mb-0">Département</label>
                        <select id="filtreDepartement" class="form-select form-select-sm">
                            <option value="">Tous</option>
                            @foreach ($departements as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="col-auto">
                    <label class="form-label mb-0">Statut</label>
                    <select id="filtreStatut" class="form-select form-select-sm">
                        <option value="">Tous</option>
                        <option value="en_attente">En attente</option>
                        <option value="valide_superieur">Validé responsable</option>
                        <option value="valide_rh">Approuvé</option>
                        <option value="refuse_superieur">Refusé (resp.)</option>
                        <option value="refuse_rh">Refusé (RH)</option>
                        <option value="annule">Annulé</option>
                    </select>
                </div>
                <div class="col-auto">
                    <label class="form-label mb-0">Mois</label>
                    <input type="number" id="filtreMois" class="form-control form-control-sm" min="1" max="12" style="width: 90px;">
                </div>
                <div class="col-auto">
                    <label class="form-label mb-0">Année</label>
                    <input type="number" id="filtreAnnee" class="form-control form-control-sm" style="width: 100px;">
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="suiviGlobalTable" class="table table-hover w-100">
                    <thead>
                        <tr>
                            <th>Employé</th>
                            <th>Département</th>
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
    var table = $('#suiviGlobalTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('absences-admin.suivi-global-data') }}',
            data: function (d) {
                d.departement_id = $('#filtreDepartement').val();
                d.statut = $('#filtreStatut').val();
                d.mois = $('#filtreMois').val();
                d.annee = $('#filtreAnnee').val();
            }
        },
        columns: [
            { data: 'employe_nom', name: 'employe_nom' },
            { data: 'departement', name: 'departement', orderable: false },
            { data: 'type_conges_label', name: 'type_conges' },
            { data: 'periode', name: 'periode', orderable: false },
            { data: 'statut_badge', name: 'statut' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false },
        ],
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/fr-FR.json' },
    });

    $('#filtreDepartement, #filtreStatut, #filtreMois, #filtreAnnee').on('change', function () {
        table.ajax.reload();
    });

    $('#suiviGlobalTable').on('click', '.btn-sg-val-sup', function () {
        var id = $(this).data('id');
        var decision = confirm('OK = Valider, Annuler = Refuser') ? 'valider' : 'refuser';
        $.post('/absences-admin/' + id + '/validation-superieur', { _token: '{{ csrf_token() }}', decision: decision })
            .done(function () { table.ajax.reload(); })
            .fail(function () { alert('Erreur'); });
    });

    $('#suiviGlobalTable').on('click', '.btn-sg-val-rh', function () {
        var id = $(this).data('id');
        var decision = confirm('OK = Valider, Annuler = Refuser') ? 'valider' : 'refuser';
        var aDeduire = decision === 'valider' && $(this).data('isrh') == 1 ? confirm('Déduire du solde de congés ?') : false;
        $.post('/absences-admin/' + id + '/validation-rh', { _token: '{{ csrf_token() }}', decision: decision, a_deduire: aDeduire ? 1 : 0 })
            .done(function () { table.ajax.reload(); })
            .fail(function () { alert('Erreur'); });
    });

    $('#suiviGlobalTable').on('click', '.btn-sg-annuler', function () {
        var id = $(this).data('id');
        var motif = prompt('Motif d\'annulation (min. 10 caractères) :');
        if (!motif) return;
        $.post('/absences-admin/' + id + '/annuler', { _token: '{{ csrf_token() }}', motif_annulation_rh: motif })
            .done(function () { table.ajax.reload(); })
            .fail(function () { alert('Erreur'); });
    });

    $('#suiviGlobalTable').on('click', '.btn-sg-supprimer', function () {
        if (!confirm('Supprimer définitivement cette demande ?')) return;
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
