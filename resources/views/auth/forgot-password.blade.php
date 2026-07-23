@php
    $configData = Helper::appClasses();
    $customizerHidden = 'customizer-hide';
@endphp

@extends('layouts/blankLayout')

@section('title', 'Mot de passe oublié')

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
                <img src="{{ asset('assets/img/illustrations/auth-forgot-password-illustration-' . $configData['style'] . '.png') }}" alt="auth-forgot-password-cover" class="my-5 auth-illustration d-lg-block d-none" data-app-light-img="illustrations/auth-forgot-password-illustration-light.png" data-app-dark-img="illustrations/auth-forgot-password-illustration-dark.png">
                <img src="{{ asset('assets/img/illustrations/bg-shape-image-' . $configData['style'] . '.png') }}" alt="auth-forgot-password-cover" class="platform-bg" data-app-light-img="illustrations/bg-shape-image-light.png" data-app-dark-img="illustrations/bg-shape-image-dark.png">
            </div>
        </div>

        <div class="d-flex col-12 col-lg-4 align-items-center authentication-bg p-sm-12 p-6">
            <div class="w-px-400 mx-auto mt-12 mt-5">
                <h4 class="mb-1">Mot de passe oublié ? 🔒</h4>
                <p class="mb-6">Entrez votre e-mail et nous vous enverrons les instructions pour réinitialiser votre mot de passe</p>

                @if (session('status'))
                    <div class="mb-1 text-success">{{ session('status') }}</div>
                @endif

                <form id="formAuthentication" class="mb-6" action="{{ route('password.email') }}" method="POST">
                    @csrf
                    <div class="mb-6">
                        <label for="email" class="form-label">Email</label>
                        <input type="text" class="form-control @error('email') is-invalid @enderror" id="email" name="email" placeholder="prenom.nom@gestosecu.local" autofocus value="{{ old('email') }}">
                        @error('email')
                            <span class="invalid-feedback" role="alert"><span class="fw-medium">{{ $message }}</span></span>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-primary d-grid w-100">Envoyer le lien de réinitialisation</button>
                </form>
                <div class="text-center">
                    @if (\Illuminate\Support\Facades\Route::has('login'))
                        <a href="{{ route('login') }}" class="d-flex align-items-center justify-content-center">
                            <i class="ti ti-chevron-left scaleX-n1-rtl me-1_5"></i>
                            Retour à la connexion
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
