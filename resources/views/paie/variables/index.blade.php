@extends('layouts.contentNavbarLayout')

@section('title', 'Variables de paie')

@section('content')
    <h3 class="mb-4">Variables de paie</h3>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2">
                <div class="col-auto">
                    <select name="mois" class="form-select form-select-sm" onchange="this.form.submit()">
                        @for ($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" @selected($mois == $m)>{{ \Carbon\Carbon::create(null, $m, 1)->translatedFormat('F') }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-auto">
                    <select name="annee" class="form-select form-select-sm" onchange="this.form.submit()">
                        @for ($a = now()->year - 1; $a <= now()->year + 1; $a++)
                            <option value="{{ $a }}" @selected($annee == $a)>{{ $a }}</option>
                        @endfor
                    </select>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Employé</th>
                            <th>Jours travaillés</th>
                            <th>Heures sup.</th>
                            <th>Prime except.</th>
                            <th>Retenue except.</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($employes as $employe)
                            @php($variable = $employe->variablesPaie->first())
                            <tr>
                                <td>{{ $employe->prenom }} {{ $employe->nom }}</td>
                                <td>{{ $variable->jours_travailles ?? '-' }}</td>
                                <td>{{ ($variable->heures_sup_15 ?? 0) + ($variable->heures_sup_40 ?? 0) + ($variable->heures_sup_60 ?? 0) + ($variable->heures_sup_100 ?? 0) }}</td>
                                <td>{{ $variable->prime_exceptionnelle ? number_format($variable->prime_exceptionnelle, 0, ',', ' ') : '-' }}</td>
                                <td>{{ $variable->retenue_exceptionnelle ? number_format($variable->retenue_exceptionnelle, 0, ',', ' ') : '-' }}</td>
                                <td>
                                    @if ($variable)
                                        <span class="badge {{ $variable->validee ? 'bg-success' : 'bg-warning' }}">{{ $variable->validee ? 'Validée' : 'Saisie' }}</span>
                                    @else
                                        <span class="badge bg-secondary">Non saisie</span>
                                    @endif
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-edit-variable"
                                        data-employe="{{ $employe->id }}" data-nom="{{ $employe->prenom }} {{ $employe->nom }}"
                                        data-variable='@json($variable)'>
                                        <i class="ti ti-pencil"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="variableModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="variableForm">
                    <div class="modal-header">
                        <h5 class="modal-title">Variables de paie — <span id="variable_nom"></span></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body row g-3">
                        <input type="hidden" id="variable_employe_id">
                        <div class="col-md-4">
                            <label class="form-label">Jours travaillés</label>
                            <input type="number" step="0.5" id="jours_travailles" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Jours d'absence non payée</label>
                            <input type="number" step="0.5" id="jours_absence_non_payee" class="form-control">
                        </div>
                        <div class="col-md-4"></div>
                        <div class="col-md-3">
                            <label class="form-label">HS 15%</label>
                            <input type="number" step="0.5" id="heures_sup_15" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">HS 40%</label>
                            <input type="number" step="0.5" id="heures_sup_40" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">HS 60%</label>
                            <input type="number" step="0.5" id="heures_sup_60" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">HS 100%</label>
                            <input type="number" step="0.5" id="heures_sup_100" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Prime exceptionnelle</label>
                            <input type="number" id="prime_exceptionnelle" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Motif prime</label>
                            <input type="text" id="motif_prime_exceptionnelle" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Retenue exceptionnelle</label>
                            <input type="number" id="retenue_exceptionnelle" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Motif retenue</label>
                            <input type="text" id="motif_retenue_exceptionnelle" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Acompte</label>
                            <input type="number" id="montant_acompte" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Avance</label>
                            <input type="number" id="montant_avance" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Commentaire</label>
                            <textarea id="commentaire" class="form-control" rows="2"></textarea>
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
    var modal = new bootstrap.Modal(document.getElementById('variableModal'));
    var fields = ['jours_travailles', 'jours_absence_non_payee', 'heures_sup_15', 'heures_sup_40', 'heures_sup_60', 'heures_sup_100',
        'prime_exceptionnelle', 'motif_prime_exceptionnelle', 'retenue_exceptionnelle', 'motif_retenue_exceptionnelle',
        'montant_acompte', 'montant_avance', 'commentaire'];

    $('.btn-edit-variable').on('click', function () {
        var variable = $(this).data('variable') || {};
        $('#variable_employe_id').val($(this).data('employe'));
        $('#variable_nom').text($(this).data('nom'));
        fields.forEach(function (f) {
            $('#' + f).val(variable[f] ?? '');
        });
        modal.show();
    });

    $('#variableForm').on('submit', function (e) {
        e.preventDefault();
        var data = {
            _token: '{{ csrf_token() }}',
            employe_id: $('#variable_employe_id').val(),
            mois: {{ $mois }},
            annee: {{ $annee }},
        };
        fields.forEach(function (f) { data[f] = $('#' + f).val(); });

        $.ajax({
            url: '{{ route('paie.variables.store') }}',
            type: 'POST',
            data: data,
            success: function () { location.reload(); },
            error: function (xhr) { alert(xhr.responseJSON?.message || 'Erreur'); }
        });
    });
});
</script>
@endpush
