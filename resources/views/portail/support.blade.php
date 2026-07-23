@extends('layouts.contentNavbarLayout')

@section('title', "Centre d'aide")

@section('content')
    <h3 class="mb-4">Support</h3>

    <div class="card">
        <div class="card-body text-center py-5">
            <i class="ti ti-headset display-4 text-primary"></i>
            <h5 class="mt-3">Besoin d'aide ?</h5>
            <p class="text-muted">Notre équipe support est à votre disposition.</p>
            <p class="mb-1"><strong>support@gestosecu.local</strong></p>
            <p class="text-muted small">Réponse sous 24h</p>
            <a href="mailto:support@gestosecu.local" class="btn btn-primary">
                <i class="ti ti-envelope"></i> Écrire
            </a>
        </div>
    </div>
@endsection
