@extends('layouts.contentNavbarLayout')

@section('title', 'Départements')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Départements</h3>
        <a href="{{ route('departements.create') }}" class="btn btn-primary">
            <i class="ti ti-plus-lg"></i> Nouveau département
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Responsable</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($departements as $departement)
                            <tr>
                                <td><a href="{{ route('departements.show', $departement->id) }}">{{ $departement->nom }}</a></td>
                                <td>{{ $departement->responsable ? $departement->responsable->prenom . ' ' . $departement->responsable->nom : '-' }}</td>
                                <td>
                                    @if ($departement->trashed())
                                        <span class="badge bg-secondary">Supprimé</span>
                                    @else
                                        <span class="badge bg-success">Actif</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($departement->trashed())
                                        <form method="POST" action="{{ route('departements.restore', $departement->id) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-success">
                                                <i class="ti ti-arrow-counterclockwise"></i> Restaurer
                                            </button>
                                        </form>
                                    @else
                                        <a href="{{ route('departements.edit', $departement->id) }}" class="btn btn-sm btn-outline-warning">
                                            <i class="ti ti-pencil"></i>
                                        </a>
                                        <form method="POST" action="{{ route('departements.destroy', $departement->id) }}" class="d-inline" onsubmit="return confirm('Supprimer ce département ?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted">Aucun département.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
