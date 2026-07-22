@extends('layouts.app')

@section('title', 'Éléments de paie')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Éléments de paie</h3>
        <button type="button" class="btn btn-primary" id="btnNewElement">
            <i class="bi bi-plus-lg"></i> Nouvel élément
        </button>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="elementsTable" class="table table-hover w-100">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Libellé</th>
                            <th>Type</th>
                            <th>Mode</th>
                            <th>Valeur</th>
                            <th>Soumissions</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="elementModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="elementForm">
                    <div class="modal-header">
                        <h5 class="modal-title">Élément de paie</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body row g-3">
                        <input type="hidden" id="el_id">
                        <div class="col-md-4">
                            <label class="form-label">Code *</label>
                            <input type="text" id="el_code" class="form-control" required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Libellé *</label>
                            <input type="text" id="el_libelle" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Type *</label>
                            <select id="el_type" class="form-select" required>
                                <option value="gain">Gain</option>
                                <option value="retenue">Retenue</option>
                                <option value="cotisation_salariale">Cotisation salariale</option>
                                <option value="cotisation_patronale">Cotisation patronale</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mode de calcul *</label>
                            <select id="el_mode_calcul" class="form-select" required>
                                <option value="fixe">Fixe</option>
                                <option value="pourcentage">Pourcentage</option>
                                <option value="formule">Formule (classe PHP)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Valeur</label>
                            <input type="number" step="0.01" id="el_valeur" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Classe formule (si mode = formule)</label>
                            <input type="text" id="el_formule_classe" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Plafond exonération</label>
                            <input type="number" step="0.01" id="el_plafond_exoneration" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ordre d'affichage</label>
                            <input type="number" id="el_ordre_affichage" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label d-block">Soumis à</label>
                            <div class="form-check form-check-inline">
                                <input type="checkbox" id="el_soumis_ipres" class="form-check-input"><label class="form-check-label" for="el_soumis_ipres">IPRES</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input type="checkbox" id="el_soumis_css" class="form-check-input"><label class="form-check-label" for="el_soumis_css">CSS</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input type="checkbox" id="el_soumis_ipm" class="form-check-input"><label class="form-check-label" for="el_soumis_ipm">IPM</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input type="checkbox" id="el_soumis_ir" class="form-check-input"><label class="form-check-label" for="el_soumis_ir">IR</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input type="checkbox" id="el_afficher_bulletin" class="form-check-input" checked>
                                <label class="form-check-label" for="el_afficher_bulletin">Afficher sur le bulletin</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input type="checkbox" id="el_actif" class="form-check-input" checked>
                                <label class="form-check-label" for="el_actif">Actif</label>
                            </div>
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
    var table = $('#elementsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('paie.elements-paie.data') }}',
        columns: [
            { data: 'code', name: 'code' },
            { data: 'libelle', name: 'libelle' },
            { data: 'type_badge', name: 'type', orderable: false },
            { data: 'mode_calcul_badge', name: 'mode_calcul', orderable: false },
            { data: 'valeur_display', name: 'valeur', orderable: false },
            { data: 'soumissions', name: 'soumissions', orderable: false },
            { data: 'actif', name: 'actif' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false },
        ],
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/fr-FR.json' },
    });

    var modal = new bootstrap.Modal(document.getElementById('elementModal'));
    var fields = ['code', 'libelle', 'type', 'mode_calcul', 'valeur', 'formule_classe', 'plafond_exoneration', 'ordre_affichage'];
    var checks = ['soumis_ipres', 'soumis_css', 'soumis_ipm', 'soumis_ir', 'afficher_bulletin', 'actif'];

    $('#btnNewElement').on('click', function () {
        $('#elementForm')[0].reset();
        $('#el_id').val('');
        modal.show();
    });

    $('#elementsTable').on('click', '.btn-edit-element', function () {
        var id = $(this).data('id');
        $.get('/paie/elements-paie/' + id + '/edit', function (data) {
            $('#el_id').val(data.id);
            fields.forEach(function (f) { $('#el_' + f).val(data[f]); });
            checks.forEach(function (c) { $('#el_' + c).prop('checked', !!data[c]); });
            modal.show();
        });
    });

    $('#elementForm').on('submit', function (e) {
        e.preventDefault();
        var id = $('#el_id').val();
        var data = { _token: '{{ csrf_token() }}' };
        fields.forEach(function (f) { data[f] = $('#el_' + f).val(); });
        checks.forEach(function (c) { if ($('#el_' + c).is(':checked')) data[c] = '1'; });

        var url = id ? '/paie/elements-paie/' + id : '{{ route('paie.elements-paie.store') }}';
        if (id) data._method = 'PUT';

        $.post(url, data)
            .done(function () { location.reload(); })
            .fail(function (xhr) { alert('Erreur lors de l\'enregistrement'); });
    });

    $('#elementsTable').on('click', '.btn-delete-element', function () {
        if (!confirm('Supprimer cet élément ?')) return;
        var id = $(this).data('id');
        $.post('/paie/elements-paie/' + id, { _token: '{{ csrf_token() }}', _method: 'DELETE' })
            .done(function () { table.ajax.reload(); });
    });
});
</script>
@endpush
