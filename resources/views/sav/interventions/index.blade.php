@extends('layouts.app')

@section('title', 'Interventions')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Interventions</h3>
        <a href="{{ route('sav.interventions.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Nouvelle intervention
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>N°</th>
                            <th>Site</th>
                            <th>Type</th>
                            <th>Technicien</th>
                            <th>Date</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($interventions as $intervention)
                            <tr>
                                <td>{{ $intervention->numero_intervention }}</td>
                                <td>{{ $intervention->site->nom_site ?? '-' }}</td>
                                <td>{{ ucfirst(str_replace('_', ' ', $intervention->type)) }}</td>
                                <td>{{ $intervention->technicien->nom_complet ?? '-' }}</td>
                                <td>{{ $intervention->date_intervention?->format('d/m/Y') }}</td>
                                <td><span class="badge bg-success">{{ ucfirst($intervention->statut) }}</span></td>
                                <td>
                                    <a href="{{ route('sav.interventions.show', $intervention->id) }}" class="btn btn-sm btn-info"><i class="bi bi-eye"></i></a>
                                    <a href="{{ route('sav.interventions.edit', $intervention->id) }}" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                                    <a href="{{ route('sav.interventions.pdf', $intervention->id) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-file-pdf"></i></a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted">Aucune intervention.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $interventions->links() }}
        </div>
    </div>
@endsection
