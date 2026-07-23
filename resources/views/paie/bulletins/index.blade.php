@extends('layouts.contentNavbarLayout')

@section('title', 'Bulletins de paie')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Bulletins de paie</h3>
        <button type="button" class="btn btn-primary" id="btnGenerateBatch">
            <i class="ti ti-magic"></i> Générer les bulletins du mois
        </button>
    </div>

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
                <div class="col-auto">
                    <select name="statut" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">Tous les statuts</option>
                        <option value="brouillon" @selected($statut == 'brouillon')>Brouillon</option>
                        <option value="valide" @selected($statut == 'valide')>Validé</option>
                        <option value="envoye" @selected($statut == 'envoye')>Envoyé</option>
                        <option value="archive" @selected($statut == 'archive')>Archivé</option>
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
                            <th>N° Bulletin</th>
                            <th>Employé</th>
                            <th>Salaire brut</th>
                            <th>Salaire net</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($bulletins as $bulletin)
                            <tr>
                                <td>{{ $bulletin->numero_bulletin }}</td>
                                <td>{{ $bulletin->employe->prenom ?? '' }} {{ $bulletin->employe->nom ?? '' }}</td>
                                <td>{{ number_format($bulletin->salaire_brut, 0, ',', ' ') }}</td>
                                <td>{{ number_format($bulletin->salaire_net_a_payer, 0, ',', ' ') }}</td>
                                <td>
                                    @php($badges = ['brouillon' => 'bg-secondary', 'valide' => 'bg-success', 'envoye' => 'bg-info', 'archive' => 'bg-dark'])
                                    <span class="badge {{ $badges[$bulletin->statut] ?? 'bg-secondary' }}">{{ ucfirst($bulletin->statut) }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('paie.bulletins.show', $bulletin->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="ti ti-eye"></i>
                                    </a>
                                    @if ($bulletin->statut === 'brouillon')
                                        <button type="button" class="btn btn-sm btn-outline-success btn-validate-bulletin" data-id="{{ $bulletin->id }}">
                                            <i class="ti ti-check-lg"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-delete-bulletin" data-id="{{ $bulletin->id }}">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">Aucun bulletin pour cette période.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $bulletins->links() }}
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(function () {
    $('#btnGenerateBatch').on('click', function () {
        if (!confirm('Lancer la génération des bulletins pour {{ $mois }}/{{ $annee }} ?')) return;
        $.post('{{ route('paie.bulletins.generate-batch') }}', {
            _token: '{{ csrf_token() }}',
            mois: {{ $mois }},
            annee: {{ $annee }},
        }).done(function (res) {
            alert(res.message);
        }).fail(function (xhr) {
            alert(xhr.responseJSON?.message || 'Erreur');
        });
    });

    $('.btn-validate-bulletin').on('click', function () {
        if (!confirm('Valider ce bulletin ?')) return;
        var id = $(this).data('id');
        $.post('/paie/bulletins/' + id + '/valider', { _token: '{{ csrf_token() }}' })
            .done(function () { location.reload(); })
            .fail(function (xhr) { alert(xhr.responseJSON?.message || 'Erreur'); });
    });

    $('.btn-delete-bulletin').on('click', function () {
        if (!confirm('Supprimer ce bulletin ?')) return;
        var id = $(this).data('id');
        $.ajax({
            url: '/paie/bulletins/' + id,
            type: 'DELETE',
            data: { _token: '{{ csrf_token() }}' },
            success: function () { location.reload(); },
            error: function (xhr) { alert(xhr.responseJSON?.message || 'Erreur'); }
        });
    });
});
</script>
@endpush
