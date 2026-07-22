@extends('layouts.guest')

@section('content')
    <h5 class="mb-3">Vérification à deux facteurs</h5>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <p class="text-muted small">Entrez le code de votre application d'authentification, ou l'un de vos codes de récupération.</p>

    <form method="POST" action="{{ route('two-factor.login') }}">
        @csrf
        <div class="mb-3">
            <label for="code" class="form-label">Code</label>
            <input id="code" type="text" inputmode="numeric" name="code" class="form-control" autofocus autocomplete="one-time-code">
        </div>
        <input type="hidden" name="recovery_code" id="recovery_code" value="">
        <button type="submit" class="btn btn-primary w-100">Valider</button>
    </form>
@endsection
