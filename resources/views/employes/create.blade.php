@extends('layouts.app')

@section('title', 'Nouvel employé')

@section('content')
    <h3 class="mb-4">Nouvel employé</h3>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('employes.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="card mb-3">
            <div class="card-header">Informations personnelles</div>
            <div class="card-body row g-3">
                <div class="col-md-4">
                    <label class="form-label">Matricule</label>
                    <input type="text" name="matricule" class="form-control" value="{{ old('matricule') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Prénom *</label>
                    <input type="text" name="prenom" class="form-control" value="{{ old('prenom') }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Nom *</label>
                    <input type="text" name="nom" class="form-control" value="{{ old('nom') }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Sexe *</label>
                    <select name="sexe" class="form-select" required>
                        <option value="Homme" @selected(old('sexe') == 'Homme')>Homme</option>
                        <option value="Femme" @selected(old('sexe') == 'Femme')>Femme</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Date de naissance</label>
                    <input type="date" name="date_naissance" class="form-control" value="{{ old('date_naissance') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Lieu de naissance</label>
                    <input type="text" name="lieu_naissance" class="form-control" value="{{ old('lieu_naissance') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Téléphone</label>
                    <input type="text" name="telephone" class="form-control" value="{{ old('telephone') }}">
                </div>
                <div class="col-md-8">
                    <label class="form-label">Adresse</label>
                    <input type="text" name="adresse" class="form-control" value="{{ old('adresse') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Situation matrimoniale *</label>
                    <select name="situation_matrimoniale" class="form-select" required>
                        <option value="Célibataire">Célibataire</option>
                        <option value="Marié(e)">Marié(e)</option>
                        <option value="Divorcé(e)">Divorcé(e)</option>
                        <option value="Veuf(ve)">Veuf(ve)</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Nombre d'épouses</label>
                    <input type="number" name="nbr_femme" class="form-control" min="0" value="{{ old('nbr_femme', 0) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Nombre d'enfants</label>
                    <input type="number" name="nbr_enfants" class="form-control" min="0" value="{{ old('nbr_enfants', 0) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">CNI</label>
                    <input type="text" name="cni" class="form-control" value="{{ old('cni') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Date de délivrance CNI</label>
                    <input type="date" name="date_delivrance" class="form-control" value="{{ old('date_delivrance') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Photo</label>
                    <input type="file" name="photo" class="form-control" accept="image/*">
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">Poste et département</div>
            <div class="card-body row g-3">
                <div class="col-md-6">
                    <label class="form-label">Département *</label>
                    <select name="id_departement" class="form-select" required>
                        <option value="">-- Sélectionner --</option>
                        @foreach ($departements as $dep)
                            <option value="{{ $dep->id }}" @selected(old('id_departement') == $dep->id)>{{ $dep->nom }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Fonction *</label>
                    <input type="text" name="fonction" class="form-control" value="{{ old('fonction') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Niveau d'étude</label>
                    <input type="text" name="niveau_etude" class="form-control" value="{{ old('niveau_etude') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Diplôme</label>
                    <input type="text" name="diplome" class="form-control" value="{{ old('diplome') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Arts martiaux</label>
                    <select name="arts_martiaux" class="form-select">
                        <option value="Non">Non</option>
                        <option value="Oui">Oui</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Permis de conduire</label>
                    <select name="permis" class="form-select">
                        <option value="Non">Non</option>
                        <option value="Oui">Oui</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Service militaire</label>
                    <select name="service_militaire" id="service_militaire" class="form-select">
                        <option value="Non">Non</option>
                        <option value="Oui">Oui</option>
                    </select>
                </div>
                <div class="col-md-4 service-militaire-fields d-none">
                    <label class="form-label">Corps militaire</label>
                    <input type="text" name="corps_militaire" class="form-control">
                </div>
                <div class="col-md-4 service-militaire-fields d-none">
                    <label class="form-label">Début du service</label>
                    <input type="date" name="date_debut_service" class="form-control">
                </div>
                <div class="col-md-4 service-militaire-fields d-none">
                    <label class="form-label">Fin du service</label>
                    <input type="date" name="date_fin_service" class="form-control">
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">Contrat</div>
            <div class="card-body row g-3">
                <div class="col-md-4">
                    <label class="form-label">Type de contrat *</label>
                    <select name="type_contrat" class="form-select" required>
                        <option value="CDI">CDI</option>
                        <option value="CDD">CDD</option>
                        <option value="Stage">Stage</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Salaire (FCFA) *</label>
                    <input type="number" name="montant" class="form-control" min="0" step="1" required>
                </div>
                <div class="col-md-4"></div>
                <div class="col-md-4">
                    <label class="form-label">Date de début *</label>
                    <input type="date" name="date_debut" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Date de fin (si CDD)</label>
                    <input type="date" name="date_fin" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Document de contrat</label>
                    <input type="file" name="document" class="form-control" accept="application/pdf">
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">Personne à contacter en cas d'urgence</div>
            <div class="card-body row g-3">
                <div class="col-md-4">
                    <label class="form-label">Nom</label>
                    <input type="text" name="personne_contact" class="form-control" value="{{ old('personne_contact') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Téléphone</label>
                    <input type="text" name="numero_contact" class="form-control" value="{{ old('numero_contact') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Lien de parenté</label>
                    <input type="text" name="lien_parente" class="form-control" value="{{ old('lien_parente') }}">
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Enregistrer</button>
        <a href="{{ route('employes.index') }}" class="btn btn-outline-secondary">Annuler</a>
    </form>
@endsection

@push('scripts')
<script>
document.getElementById('service_militaire').addEventListener('change', function () {
    document.querySelectorAll('.service-militaire-fields').forEach(function (el) {
        el.classList.toggle('d-none', this.value !== 'Oui');
    }.bind(this));
});
</script>
@endpush
