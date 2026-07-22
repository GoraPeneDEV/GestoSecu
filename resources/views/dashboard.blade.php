@extends('layouts.app')

@section('title', 'Tableau de bord')

@section('content')
    <h1 class="h3 mb-4">Bienvenue, {{ auth()->user()->prenom }}</h1>

    <div class="row g-3">
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">RH + Paie</h5>
                    <p class="card-text text-muted">Employés, contrats, absences, bulletins de paie.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Ronde + Supervision</h5>
                    <p class="card-text text-muted">Rondes agents, rondes superviseurs, visites de supervision.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">SAV + Articles + Dotations</h5>
                    <p class="card-text text-muted">Contrats clients, garanties, fiches de progrès, stock, immobilisations.</p>
                </div>
            </div>
        </div>
    </div>
@endsection
