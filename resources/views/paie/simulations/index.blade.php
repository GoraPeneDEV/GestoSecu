@extends('layouts.contentNavbarLayout')

@section('title', 'Simulation de salaire')

@section('content')
    <h3 class="mb-4">Simulation de salaire</h3>

    <ul class="nav nav-tabs mb-3">
        <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#brutToNet">Brut → Net</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#netToBrut">Net → Brut</a></li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane fade show active" id="brutToNet">
            <div class="card" style="max-width: 700px;">
                <div class="card-body">
                    <form id="formBrutToNet" class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Salaire brut (FCFA) *</label>
                            <input type="number" name="salaire_brut" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Catégorie *</label>
                            <select name="categorie_professionnelle" class="form-select" required>
                                <option value="Non-cadre">Non-cadre</option>
                                <option value="Cadre">Cadre</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Parts fiscales *</label>
                            <input type="number" step="0.5" name="parts_fiscales" class="form-control" value="1" min="1" max="6" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Nb enfants</label>
                            <input type="number" name="nb_enfants" class="form-control" value="0" min="0" max="10">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Nb épouses</label>
                            <input type="number" name="nb_epouses" class="form-control" value="0" min="0" max="4">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">Calculer</button>
                        </div>
                    </form>
                    <div id="resultBrutToNet" class="mt-4"></div>
                </div>
            </div>
        </div>
        <div class="tab-pane fade" id="netToBrut">
            <div class="card" style="max-width: 700px;">
                <div class="card-body">
                    <form id="formNetToBrut" class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Salaire net souhaité (FCFA) *</label>
                            <input type="number" name="salaire_net" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Catégorie *</label>
                            <select name="categorie_professionnelle" class="form-select" required>
                                <option value="Non-cadre">Non-cadre</option>
                                <option value="Cadre">Cadre</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Parts fiscales *</label>
                            <input type="number" step="0.5" name="parts_fiscales" class="form-control" value="1" min="1" max="6" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Nb enfants</label>
                            <input type="number" name="nb_enfants" class="form-control" value="0" min="0" max="10">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Nb épouses</label>
                            <input type="number" name="nb_epouses" class="form-control" value="0" min="0" max="4">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">Calculer</button>
                        </div>
                    </form>
                    <div id="resultNetToBrut" class="mt-4"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
function renderResult(target, data) {
    var html = '<table class="table table-sm"><tbody>';
    Object.keys(data).forEach(function (key) {
        if (typeof data[key] === 'object') return;
        html += '<tr><td>' + key + '</td><td class="text-end">' + data[key] + '</td></tr>';
    });
    html += '</tbody></table>';
    $(target).html(html);
}

$('#formBrutToNet').on('submit', function (e) {
    e.preventDefault();
    $.post('{{ route('paie.simulations.brut-to-net') }}', $(this).serialize() + '&_token={{ csrf_token() }}')
        .done(function (data) { renderResult('#resultBrutToNet', data); })
        .fail(function () { alert('Erreur lors du calcul'); });
});

$('#formNetToBrut').on('submit', function (e) {
    e.preventDefault();
    $.post('{{ route('paie.simulations.net-to-brut') }}', $(this).serialize() + '&_token={{ csrf_token() }}')
        .done(function (data) { renderResult('#resultNetToBrut', data); })
        .fail(function () { alert('Erreur lors du calcul'); });
});
</script>
@endpush
