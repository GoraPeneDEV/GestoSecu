@extends('layouts.contentNavbarLayout')

@section('title', 'Planning - ' . $agent->prenom . ' ' . $agent->nom)

@section('content')
    <a href="{{ route('portail.agents.show', $agent->id) }}" class="btn btn-sm btn-outline-secondary mb-3">
        <i class="ti ti-arrow-left"></i> Retour à l'agent
    </a>

    <h3 class="mb-4">Planning de {{ $agent->prenom }} {{ $agent->nom }}</h3>

    @if ($agent->plannings->isEmpty())
        <p class="text-muted">Aucun planning actif pour cet agent sur vos sites.</p>
    @endif

    @foreach ($agent->plannings as $planning)
        <div class="card mb-3">
            <div class="card-header">
                {{ $planning->site->nom_site ?? 'Site' }}
                <span class="text-muted small">— depuis le {{ $planning->date_debut?->format('d/m/Y') }}</span>
            </div>
            <div class="card-body">
                @if ($planning->detailsHorizontal->isEmpty())
                    <p class="text-muted mb-0">Aucun horaire renseigné.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Jour</th>
                                    <th>Horaire</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($planning->detailsHorizontal as $detail)
                                    <tr>
                                        <td>{{ ucfirst($detail->jour_semaine) }}</td>
                                        <td>
                                            @if ($detail->horaire)
                                                {{ $detail->horaire->label }}
                                                ({{ $detail->horaire->heure_debut }} - {{ $detail->horaire->heure_fin }})
                                            @else
                                                Repos
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    @endforeach
@endsection
