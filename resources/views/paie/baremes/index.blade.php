@extends('layouts.contentNavbarLayout')

@section('title', 'Barèmes fiscaux')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Barèmes fiscaux</h3>
        <button type="button" class="btn btn-primary" id="btnNewBareme">
            <i class="ti ti-plus-lg"></i> Nouveau barème
        </button>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-2">
                <div class="col-auto">
                    <select id="filterAnnee" class="form-select form-select-sm">
                        @for ($a = now()->year - 2; $a <= now()->year + 1; $a++)
                            <option value="{{ $a }}" @selected($a == now()->year)>{{ $a }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-auto">
                    <select id="filterType" class="form-select form-select-sm">
                        <option value="">Tous les types</option>
                        <option value="ipres_rg">IPRES RG</option>
                        <option value="ipres_cadre">IPRES Cadre</option>
                        <option value="css">CSS</option>
                        <option value="ipm">IPM</option>
                        <option value="ir">IR</option>
                        <option value="trimf">TRIMF</option>
                        <option value="cfce">CFCE</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="baremesTable" class="table table-hover w-100">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Année</th>
                            <th>Taux / Tranche</th>
                            <th>Plafond</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="baremeModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="baremeForm">
                    <div class="modal-header">
                        <h5 class="modal-title">Barème fiscal</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body row g-3">
                        <input type="hidden" id="br_id">
                        <div class="col-md-6">
                            <label class="form-label">Type *</label>
                            <select id="br_type" class="form-select" required>
                                <option value="ipres_rg">IPRES RG</option>
                                <option value="ipres_cadre">IPRES Cadre</option>
                                <option value="css">CSS</option>
                                <option value="ipm">IPM</option>
                                <option value="ir">IR (tranche)</option>
                                <option value="trimf">TRIMF</option>
                                <option value="cfce">CFCE</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Année *</label>
                            <input type="number" id="br_annee" class="form-control" value="{{ now()->year }}" required>
                        </div>
                        <div class="col-md-4 champ-taux-double">
                            <label class="form-label">Taux salarial (%)</label>
                            <input type="number" step="0.01" id="br_taux_salarial" class="form-control">
                        </div>
                        <div class="col-md-4 champ-taux-double">
                            <label class="form-label">Taux patronal (%)</label>
                            <input type="number" step="0.01" id="br_taux_patronal" class="form-control">
                        </div>
                        <div class="col-md-4 champ-taux-double">
                            <label class="form-label">Plafond</label>
                            <input type="number" step="0.01" id="br_plafond" class="form-control">
                        </div>
                        <div class="col-md-4 champ-ir d-none">
                            <label class="form-label">Tranche min</label>
                            <input type="number" step="0.01" id="br_tranche_min" class="form-control">
                        </div>
                        <div class="col-md-4 champ-ir d-none">
                            <label class="form-label">Tranche max</label>
                            <input type="number" step="0.01" id="br_tranche_max" class="form-control">
                        </div>
                        <div class="col-md-4 champ-ir d-none">
                            <label class="form-label">Taux IR (%)</label>
                            <input type="number" step="0.01" id="br_taux_ir" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Référence légale</label>
                            <input type="text" id="br_reference_legale" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Description</label>
                            <input type="text" id="br_description" class="form-control">
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input type="checkbox" id="br_actif" class="form-check-input" checked>
                                <label class="form-check-label" for="br_actif">Actif</label>
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
    var table = $('#baremesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('paie.baremes-fiscaux.data') }}',
            data: function (d) {
                d.annee = $('#filterAnnee').val();
                d.type = $('#filterType').val();
            }
        },
        columns: [
            { data: 'type_label', name: 'type', orderable: false },
            { data: 'annee', name: 'annee' },
            { data: 'taux_info', name: 'taux_info', orderable: false },
            { data: 'plafond_info', name: 'plafond_info', orderable: false },
            { data: 'actif', name: 'actif' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false },
        ],
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/fr-FR.json' },
    });

    $('#filterAnnee, #filterType').on('change', function () { table.ajax.reload(); });

    var modal = new bootstrap.Modal(document.getElementById('baremeModal'));

    function toggleFields() {
        var isIR = $('#br_type').val() === 'ir';
        $('.champ-ir').toggleClass('d-none', !isIR);
        $('.champ-taux-double').toggleClass('d-none', isIR);
    }
    $('#br_type').on('change', toggleFields);

    $('#btnNewBareme').on('click', function () {
        $('#baremeForm')[0].reset();
        $('#br_id').val('');
        $('#br_annee').val({{ now()->year }});
        toggleFields();
        modal.show();
    });

    $('#baremesTable').on('click', '.btn-edit-bareme', function () {
        var id = $(this).data('id');
        $.get('/paie/baremes-fiscaux/' + id + '/edit', function (data) {
            $('#br_id').val(data.id);
            ['type', 'annee', 'taux_salarial', 'taux_patronal', 'plafond', 'tranche_min', 'tranche_max', 'taux_ir', 'reference_legale', 'description'].forEach(function (f) {
                $('#br_' + f).val(data[f]);
            });
            $('#br_actif').prop('checked', !!data.actif);
            toggleFields();
            modal.show();
        });
    });

    $('#baremeForm').on('submit', function (e) {
        e.preventDefault();
        var id = $('#br_id').val();
        var data = { _token: '{{ csrf_token() }}' };
        ['type', 'annee', 'taux_salarial', 'taux_patronal', 'plafond', 'tranche_min', 'tranche_max', 'taux_ir', 'reference_legale', 'description'].forEach(function (f) {
            data[f] = $('#br_' + f).val();
        });
        if ($('#br_actif').is(':checked')) data.actif = '1';

        var url = id ? '/paie/baremes-fiscaux/' + id : '{{ route('paie.baremes-fiscaux.store') }}';
        if (id) data._method = 'PUT';

        $.post(url, data)
            .done(function () { location.reload(); })
            .fail(function (xhr) { alert(xhr.responseJSON?.message || 'Erreur lors de l\'enregistrement'); });
    });

    $('#baremesTable').on('click', '.btn-delete-bareme', function () {
        if (!confirm('Supprimer ce barème ?')) return;
        var id = $(this).data('id');
        $.post('/paie/baremes-fiscaux/' + id, { _token: '{{ csrf_token() }}', _method: 'DELETE' })
            .done(function () { table.ajax.reload(); });
    });
});
</script>
@endpush
