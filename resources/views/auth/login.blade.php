@php
    $configData = Helper::appClasses();
    $customizerHidden = 'customizer-hide';
@endphp

@extends('layouts/blankLayout')

@section('title', 'Connexion')

@section('page-style')
@vite('resources/assets/vendor/scss/pages/page-auth.scss')
@endsection

@section('content')
<div class="authentication-wrapper authentication-cover">
    <a href="{{ url('/') }}" class="app-brand auth-cover-brand">
        <span class="app-brand-logo demo"><i class="ti ti-shield-check ti-lg text-white"></i></span>
        <span class="app-brand-text demo text-heading fw-bold">{{ config('app.name', 'GestoSecu') }}</span>
    </a>

    <div class="authentication-inner row m-0">
        <div class="d-none d-lg-flex col-lg-8 p-0">
            <div class="auth-cover-bg auth-cover-bg-color d-flex justify-content-center align-items-center">
                <img src="{{ asset('assets/img/illustrations/auth-login-illustration-' . $configData['style'] . '.png') }}" alt="auth-login-cover" class="my-5 auth-illustration d-lg-block d-none" data-app-light-img="illustrations/auth-login-illustration-light.png" data-app-dark-img="illustrations/auth-login-illustration-dark.png">
                <img src="{{ asset('assets/img/illustrations/bg-shape-image-' . $configData['style'] . '.png') }}" alt="auth-login-cover" class="platform-bg" data-app-light-img="illustrations/bg-shape-image-light.png" data-app-dark-img="illustrations/bg-shape-image-dark.png">
            </div>
        </div>

        <div class="d-flex col-12 col-lg-4 align-items-center authentication-bg p-sm-12 p-6">
            <div class="w-px-400 mx-auto mt-12 pt-5">
                <h4 class="mb-1">Bienvenue sur {{ config('app.name', 'GestoSecu') }} ! 👋</h4>
                <p class="mb-6">Veuillez vous connecter à votre compte pour commencer</p>

                @if (session('status'))
                    <div class="alert alert-success mb-1 rounded-0" role="alert">
                        <div class="alert-body">{{ session('status') }}</div>
                    </div>
                @endif

                <form id="formAuthentication" class="mb-6" action="{{ route('login') }}" method="POST">
                    @csrf
                    <div class="mb-6">
                        <label for="login-email" class="form-label">Email</label>
                        <input type="text" class="form-control @error('email') is-invalid @enderror" id="login-email" name="email" placeholder="prenom.nom@gestosecu.local" autofocus value="{{ old('email') }}">
                        @error('email')
                            <span class="invalid-feedback" role="alert"><span class="fw-medium">{{ $message }}</span></span>
                        @enderror
                    </div>
                    <div class="mb-6 form-password-toggle">
                        <label class="form-label" for="login-password">Mot de passe</label>
                        <div class="input-group input-group-merge @error('password') is-invalid @enderror">
                            <input type="password" id="login-password" class="form-control @error('password') is-invalid @enderror" name="password" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" aria-describedby="login-password" />
                            <span class="input-group-text cursor-pointer"><i class="ti ti-eye-off"></i></span>
                        </div>
                        @error('password')
                            <span class="invalid-feedback" role="alert"><span class="fw-medium">{{ $message }}</span></span>
                        @enderror
                    </div>
                    <div class="my-8">
                        <div class="d-flex justify-content-between">
                            <div class="form-check mb-0 ms-2">
                                <input class="form-check-input" type="checkbox" id="remember-me" name="remember" {{ old('remember') ? 'checked' : '' }}>
                                <label class="form-check-label" for="remember-me">Se souvenir de moi</label>
                            </div>
                            @if (\Illuminate\Support\Facades\Route::has('password.request'))
                                <a href="{{ route('password.request') }}">
                                    <p class="mb-0">Mot de passe oublié ?</p>
                                </a>
                            @endif
                        </div>
                    </div>
                    <button class="btn btn-primary d-grid w-100" type="submit">Se connecter</button>
                </form>

                <div class="text-center">
                    <div class="divider my-6">
                        <div class="divider-text">ou</div>
                    </div>
                    <p class="text-center">
                        <span class="text-muted">Vous êtes un client ?</span>
                        <a href="{{ route('portail.login') }}" class="fw-medium text-primary text-decoration-none">
                            Accédez au Portail Client
                            <i class="ti ti-arrow-right ms-1"></i>
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
