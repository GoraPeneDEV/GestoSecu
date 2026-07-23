@extends('layouts.contentNavbarLayout')

@section('title', 'Mon profil')

@section('content')
    @php($portailUser = Auth::guard('portail')->user())

    <h3 class="mb-4">Mon profil</h3>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Informations</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('portail.profile.update') }}">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label class="form-label">Prénom</label>
                            <input type="text" class="form-control" value="{{ $portailUser->prenom }}" disabled>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nom</label>
                            <input type="text" class="form-control" value="{{ $portailUser->nom }}" disabled>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" value="{{ $portailUser->email }}" disabled>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Téléphone</label>
                            <input type="text" class="form-control" value="{{ $portailUser->telephone }}" disabled>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Client</label>
                            <input type="text" class="form-control" value="{{ $portailUser->client->nomClient ?? '-' }}" disabled>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
