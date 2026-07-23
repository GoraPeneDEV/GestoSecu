@extends('layouts.contentNavbarLayout')

@section('title', 'Tableau de bord')

@section('content')
    <h4 class="fw-bold mb-4">Bienvenue, {{ auth()->user()->prenom }}</h4>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <span class="avatar avatar-sm me-2"><span class="avatar-initial rounded bg-label-primary"><i class="ti ti-users"></i></span></span>
                        <h5 class="card-title mb-0">RH + Paie</h5>
                    </div>
                    <p class="card-text text-muted mb-0">Employés, contrats, absences, bulletins de paie.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <span class="avatar avatar-sm me-2"><span class="avatar-initial rounded bg-label-success"><i class="ti ti-route"></i></span></span>
                        <h5 class="card-title mb-0">Ronde + Supervision</h5>
                    </div>
                    <p class="card-text text-muted mb-0">Rondes agents, rondes superviseurs, visites de supervision.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <span class="avatar avatar-sm me-2"><span class="avatar-initial rounded bg-label-warning"><i class="ti ti-headset"></i></span></span>
                        <h5 class="card-title mb-0">SAV + Articles + Dotations</h5>
                    </div>
                    <p class="card-text text-muted mb-0">Contrats clients, garanties, fiches de progrès, stock, immobilisations.</p>
                </div>
            </div>
        </div>
    </div>
@endsection
