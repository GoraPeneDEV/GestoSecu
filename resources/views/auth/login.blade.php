@extends('layouts.guest')

@section('content')
    <h5 class="mb-3">Connexion</h5>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input id="email" type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Mot de passe</label>
            <input id="password" type="password" name="password" class="form-control" required>
        </div>
        <div class="mb-3 form-check">
            <input type="checkbox" name="remember" id="remember" class="form-check-input">
            <label for="remember" class="form-check-label">Se souvenir de moi</label>
        </div>
        <button type="submit" class="btn btn-primary w-100">Se connecter</button>
        @if (\Laravel\Fortify\Features::enabled(\Laravel\Fortify\Features::resetPasswords()))
            <div class="text-center mt-3">
                <a href="{{ route('password.request') }}" class="small">Mot de passe oublié ?</a>
            </div>
        @endif
    </form>
@endsection
