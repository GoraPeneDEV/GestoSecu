@php
    $configData = Helper::appClasses();
    $customizerHidden = 'customizer-hide';
@endphp

@extends('layouts/blankLayout')

@section('title', 'Vérification en deux étapes')

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
                <img src="{{ asset('assets/img/illustrations/auth-two-step-illustration-' . $configData['style'] . '.png') }}" alt="auth-two-steps-cover" class="my-5 auth-illustration" data-app-light-img="illustrations/auth-two-step-illustration-light.png" data-app-dark-img="illustrations/auth-two-step-illustration-dark.png">
                <img src="{{ asset('assets/img/illustrations/bg-shape-image-' . $configData['style'] . '.png') }}" alt="auth-two-steps-cover" class="platform-bg" data-app-light-img="illustrations/bg-shape-image-light.png" data-app-dark-img="illustrations/bg-shape-image-dark.png">
            </div>
        </div>

        <div class="d-flex col-12 col-lg-4 align-items-center authentication-bg p-6 p-sm-12">
            <div class="w-px-400 mx-auto mt-12 mt-5">
                <h4 class="mb-1">Vérification en deux étapes 💬</h4>
                <p class="text-start mb-6">Entrez le code de votre application d'authentification, ou l'un de vos codes de récupération.</p>

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('two-factor.login') }}">
                    @csrf
                    <div class="mb-6">
                        <label for="code" class="form-label">Code</label>
                        <input id="code" type="text" inputmode="numeric" name="code" class="form-control" autofocus autocomplete="one-time-code">
                    </div>
                    <input type="hidden" name="recovery_code" id="recovery_code" value="">
                    <button type="submit" class="btn btn-primary d-grid w-100">Se connecter</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
