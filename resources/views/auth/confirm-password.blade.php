@php
    $configData = Helper::appClasses();
    $customizerHidden = 'customizer-hide';
@endphp

@extends('layouts/blankLayout')

@section('title', 'Confirmer le mot de passe')

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
                <img src="{{ asset('assets/img/illustrations/auth-forgot-password-illustration-' . $configData['style'] . '.png') }}" alt="auth-confirm-password-cover" class="my-5 auth-illustration d-lg-block d-none" data-app-light-img="illustrations/auth-forgot-password-illustration-light.png" data-app-dark-img="illustrations/auth-forgot-password-illustration-dark.png">
                <img src="{{ asset('assets/img/illustrations/bg-shape-image-' . $configData['style'] . '.png') }}" alt="auth-confirm-password-cover" class="platform-bg" data-app-light-img="illustrations/bg-shape-image-light.png" data-app-dark-img="illustrations/bg-shape-image-dark.png">
            </div>
        </div>

        <div class="d-flex col-12 col-lg-4 align-items-center authentication-bg p-sm-12 p-6">
            <div class="w-px-400 mx-auto mt-12 mt-5">
                <h4 class="mb-1">Confirmer le mot de passe</h4>
                <p class="mb-6">Veuillez confirmer votre mot de passe avant de continuer.</p>
                <form id="formAuthentication" class="mb-6" action="{{ route('password.confirm') }}" method="POST">
                    @csrf
                    <div class="mb-6 form-password-toggle">
                        <label class="form-label" for="password">Mot de passe</label>
                        <div class="input-group input-group-merge @error('password') is-invalid @enderror">
                            <input type="password" id="password" class="form-control @error('password') is-invalid @enderror" name="password" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" aria-describedby="password" autofocus />
                            <span class="input-group-text cursor-pointer"><i class="ti ti-eye-off"></i></span>
                        </div>
                        @error('password')
                            <span class="invalid-feedback" role="alert"><span class="fw-medium">{{ $message }}</span></span>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-primary d-grid w-100">Confirmer</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
