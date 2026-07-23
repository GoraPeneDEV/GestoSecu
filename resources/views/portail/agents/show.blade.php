@extends('layouts.contentNavbarLayout')

@section('title', 'Agent - ' . $agent->prenom . ' ' . $agent->nom)

@section('content')
    <a href="{{ route('portail.agents.index') }}" class="btn btn-sm btn-outline-secondary mb-3">
        <i class="ti ti-arrow-left"></i> Retour aux agents
    </a>

    <h3 class="mb-4">{{ $agent->prenom }} {{ $agent->nom }}</h3>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">Informations</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-5">Matricule</dt>
                        <dd class="col-7">{{ $agent->matricule }}</dd>
                        <dt class="col-5">Fonction</dt>
                        <dd class="col-7">{{ $agent->fonction ?? '-' }}</dd>
                        <dt class="col-5">Département</dt>
                        <dd class="col-7">{{ $agent->departement->nom ?? '-' }}</dd>
                        <dt class="col-5">Téléphone</dt>
                        <dd class="col-7">{{ $agent->telephone ?? '-' }}</dd>
                    </dl>
                    <a href="{{ route('portail.agents.planning', $agent->id) }}" class="btn btn-sm btn-outline-primary mt-2">
                        <i class="ti ti-calendar"></i> Voir le planning
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">Sites assignés</div>
                <div class="card-body">
                    @if ($agent->plannings->isEmpty())
                        <p class="text-muted mb-0">Aucun site actuellement assigné.</p>
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach ($agent->plannings->unique('site_id') as $planning)
                                <li class="list-group-item">{{ $planning->site->nom_site ?? '-' }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">Documents</div>
        <div class="card-body">
            @if ($agent->documents->isEmpty())
                <p class="text-muted mb-0">Aucun document disponible.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Nom du fichier</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($agent->documents as $document)
                                <tr>
                                    <td>{{ $document->type_document }}</td>
                                    <td>{{ $document->nom_fichier }}</td>
                                    <td>
                                        <a href="{{ route('portail.agents.documents.view', [$agent->id, $document->id]) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                                            <i class="ti ti-eye"></i>
                                        </a>
                                        <a href="{{ route('portail.agents.documents.download', [$agent->id, $document->id]) }}" class="btn btn-sm btn-outline-secondary">
                                            <i class="ti ti-download"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection
