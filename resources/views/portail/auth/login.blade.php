@php
    $configData = Helper::appClasses();
    $customizerHidden = 'customizer-hide';
@endphp

@extends('layouts/blankLayout')

@section('title', 'Connexion - Portail Client')

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
                <img src="{{ asset('assets/img/illustrations/auth-login-illustration-' . $configData['style'] . '.png') }}" alt="portail-login-cover" class="my-5 auth-illustration d-lg-block d-none" data-app-light-img="illustrations/auth-login-illustration-light.png" data-app-dark-img="illustrations/auth-login-illustration-dark.png">
                <img src="{{ asset('assets/img/illustrations/bg-shape-image-' . $configData['style'] . '.png') }}" alt="portail-login-cover" class="platform-bg" data-app-light-img="illustrations/bg-shape-image-light.png" data-app-dark-img="illustrations/bg-shape-image-dark.png">
            </div>
        </div>

        <div class="d-flex col-12 col-lg-4 align-items-center authentication-bg p-sm-12 p-6">
            <div class="w-px-400 mx-auto mt-12 pt-5">
                <h4 class="mb-1">Portail Client {{ config('app.name', 'GestoSecu') }} 👋</h4>
                <p class="mb-6">Accédez à votre espace client sécurisé</p>

                @if ($errors->any())
                    <div class="alert alert-danger">
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <form id="formAuthentication" class="mb-6" action="{{ route('portail.login.submit') }}" method="POST">
                    @csrf
                    <div class="mb-6">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" placeholder="contact@client.com" autofocus value="{{ old('email') }}">
                        @error('email')
                            <span class="invalid-feedback" role="alert"><span class="fw-medium">{{ $message }}</span></span>
                        @enderror
                    </div>
                    <div class="mb-6 form-password-toggle">
                        <label class="form-label" for="password">Mot de passe</label>
                        <div class="input-group input-group-merge @error('password') is-invalid @enderror">
                            <input type="password" id="password" class="form-control @error('password') is-invalid @enderror" name="password" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" aria-describedby="password" />
                            <span class="input-group-text cursor-pointer"><i class="ti ti-eye-off"></i></span>
                        </div>
                        @error('password')
                            <span class="invalid-feedback" role="alert"><span class="fw-medium">{{ $message }}</span></span>
                        @enderror
                    </div>
                    <div class="mb-6 form-check">
                        <input type="checkbox" name="remember" class="form-check-input" id="remember">
                        <label class="form-check-label" for="remember">Se souvenir de moi</label>
                    </div>
                    <button class="btn btn-primary d-grid w-100" type="submit">Se connecter</button>
                </form>

                <div class="text-center">
                    <div class="divider my-6">
                        <div class="divider-text">ou</div>
                    </div>
                    <p class="text-center">
                        <span class="text-muted">Vous êtes un employé {{ config('app.name', 'GestoSecu') }} ?</span>
                        <a href="{{ route('login') }}" class="fw-medium text-primary text-decoration-none">
                            Accédez à l'espace employé
                            <i class="ti ti-arrow-right ms-1"></i>
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
