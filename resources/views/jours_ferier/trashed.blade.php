@extends('layouts.app')

@section('title', 'Jours fériés supprimés')

@section('content')
    <a href="{{ route('jours_ferier.index') }}" class="btn btn-sm btn-outline-secondary mb-3">
        <i class="bi bi-arrow-left"></i> Retour
    </a>

    <h3 class="mb-4">Corbeille — Jours fériés</h3>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr><th>Date</th><th>Description</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($joursFerierTrashed as $jour)
                            <tr>
                                <td>{{ $jour->date_ferier?->format('d/m/Y') }}</td>
                                <td>{{ $jour->description ?? '-' }}</td>
                                <td>
                                    <form method="POST" action="{{ route('jours_ferier.restore', $jour->id) }}" class="d-inline">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="btn btn-sm btn-outline-success">
                                            <i class="bi bi-arrow-counterclockwise"></i> Restaurer
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('jours_ferier.forceDelete', $jour->id) }}" class="d-inline" onsubmit="return confirm('Suppression définitive. Continuer ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-x-circle"></i> Supprimer définitivement
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted">Corbeille vide.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
