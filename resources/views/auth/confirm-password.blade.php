@extends('layouts.guest')

@section('content')
    <h5 class="mb-3">Confirmer le mot de passe</h5>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf
        <div class="mb-3">
            <label for="password" class="form-label">Mot de passe</label>
            <input id="password" type="password" name="password" class="form-control" required autofocus>
        </div>
        <button type="submit" class="btn btn-primary w-100">Confirmer</button>
    </form>
@endsection
