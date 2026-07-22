@extends('layouts.app')

@section('title', 'Nouvelle ronde')

@section('content')
    <h3 class="mb-4">Nouvelle ronde</h3>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card" style="max-width: 700px;">
        <div class="card-body">
            <form method="POST" action="{{ route('sie.rondes.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Planning de ronde *</label>
                        <select name="planning_ronde_id" class="form-select" required>
                            <option value="">-- Sélectionner --</option>
                            @foreach ($planningsRonde as $planning)
                                <option value="{{ $planning->id }}">{{ $planning->nom }} — {{ $planning->site->nom_site ?? '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Agent *</label>
                        <select name="agent_id" class="form-select" required>
                            <option value="">-- Sélectionner --</option>
                            @foreach ($agents as $agent)
                                <option value="{{ $agent->id }}">{{ $agent->prenom }} {{ $agent->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mt-3">Démarrer la ronde</button>
                <a href="{{ route('sie.rondes.index') }}" class="btn btn-outline-secondary mt-3">Annuler</a>
            </form>
        </div>
    </div>
@endsection
