@extends('layouts.contentNavbarLayout')

@section('title', 'Parc — ' . $client->nomClient)

@section('content')
    <a href="{{ route('sav.parc.index') }}" class="btn btn-sm btn-outline-secondary mb-3">
        <i class="ti ti-arrow-left"></i> Retour
    </a>

    <h3 class="mb-4">Parc — {{ $client->nomClient }}</h3>

    @foreach ($sites as $site)
        <div class="card mb-3">
            <div class="card-header">{{ $site->nom_site }} ({{ $site->client_assets_count }})</div>
            <div class="card-body">
                @if ($site->clientAssets->isEmpty())
                    <p class="text-muted mb-0">Aucun équipement.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead><tr><th>Libellé</th><th>Type</th><th>N° série</th><th>Statut</th><th>Actions</th></tr></thead>
                            <tbody>
                                @foreach ($site->clientAssets as $asset)
                                    <tr>
                                        <td>{{ $asset->label }}</td>
                                        <td>{{ $asset->type }}</td>
                                        <td>{{ $asset->serial_number ?? '-' }}</td>
                                        <td>
                                            @php($badges = ['fonctionnel' => 'bg-success', 'maintenance_requise' => 'bg-warning', 'panne' => 'bg-danger', 'hors_service' => 'bg-secondary'])
                                            <span class="badge {{ $badges[$asset->status] ?? 'bg-secondary' }}">{{ str_replace('_', ' ', ucfirst($asset->status)) }}</span>
                                        </td>
                                        <td>
                                            <a href="{{ route('sav.parc.edit', $asset->id) }}" class="btn btn-sm btn-outline-warning"><i class="ti ti-pencil"></i></a>
                                            <form method="POST" action="{{ route('sav.parc.destroy', $asset->id) }}" class="d-inline" onsubmit="return confirm('Supprimer cet équipement ?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="ti ti-trash"></i></button>
                                            </form>
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
