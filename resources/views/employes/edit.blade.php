@extends('layouts.app')

@section('title', 'Modifier - ' . $employe->prenom . ' ' . $employe->nom)

@section('content')
    <h3 class="mb-4">Modifier {{ $employe->prenom }} {{ $employe->nom }}</h3>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('employes.update', $employe->id) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="card mb-3">
            <div class="card-header">Informations personnelles</div>
            <div class="card-body row g-3">
                <div class="col-md-4">
                    <label class="form-label">Matricule</label>
                    <input type="text" name="matricule" class="form-control" value="{{ old('matricule', $employe->matricule) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Prénom *</label>
                    <input type="text" name="prenom" class="form-control" value="{{ old('prenom', $employe->prenom) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Nom *</label>
                    <input type="text" name="nom" class="form-control" value="{{ old('nom', $employe->nom) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Sexe *</label>
                    <select name="sexe" class="form-select" required>
                        <option value="Homme" @selected(old('sexe', $employe->sexe) == 'Homme')>Homme</option>
                        <option value="Femme" @selected(old('sexe', $employe->sexe) == 'Femme')>Femme</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Date de naissance</label>
                    <input type="date" name="date_naissance" class="form-control" value="{{ old('date_naissance', $employe->date_naissance?->format('Y-m-d')) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Lieu de naissance</label>
                    <input type="text" name="lieu_naissance" class="form-control" value="{{ old('lieu_naissance', $employe->lieu_naissance) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Téléphone</label>
                    <input type="text" name="telephone" class="form-control" value="{{ old('telephone', $employe->telephone) }}">
                </div>
                <div class="col-md-8">
                    <label class="form-label">Adresse</label>
                    <input type="text" name="adresse" class="form-control" value="{{ old('adresse', $employe->adresse) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Situation matrimoniale *</label>
                    <select name="situation_matrimoniale" class="form-select" required>
                        @foreach (['Célibataire', 'Marié(e)', 'Divorcé(e)', 'Veuf(ve)'] as $situation)
                            <option value="{{ $situation }}" @selected(old('situation_matrimoniale', $employe->situation_matrimoniale) == $situation)>{{ $situation }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Nombre d'épouses</label>
                    <input type="number" name="nbr_femme" class="form-control" min="0" value="{{ old('nbr_femme', $employe->nbr_femme) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Nombre d'enfants</label>
                    <input type="number" name="nbr_enfants" class="form-control" min="0" value="{{ old('nbr_enfants', $employe->nbr_enfants) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">CNI</label>
                    <input type="text" name="cni" class="form-control" value="{{ old('cni', $employe->cni) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Solde congés (jours)</label>
                    <input type="number" name="solde_conges" class="form-control" min="0" value="{{ old('solde_conges', $employe->solde_conges) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Photo</label>
                    <input type="file" name="photo" class="form-control" accept="image/*">
                    @if ($employe->photo)
                        <div class="form-check mt-2">
                            <input type="checkbox" name="supprimer_photo" value="1" class="form-check-input" id="supprimer_photo">
                            <label class="form-check-label" for="supprimer_photo">Supprimer la photo actuelle</label>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">Poste et département</div>
            <div class="card-body row g-3">
                <div class="col-md-6">
                    <label class="form-label">Département *</label>
                    <select name="id_departement" class="form-select" required>
                        @foreach ($departements as $dep)
                            <option value="{{ $dep->id }}" @selected(old('id_departement', $employe->id_departement) == $dep->id)>{{ $dep->nom }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Fonction *</label>
                    <input type="text" name="fonction" class="form-control" value="{{ old('fonction', $employe->fonction) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Permis de conduire *</label>
                    <select name="permis" class="form-select" required>
                        <option value="Non" @selected(old('permis', $employe->permis) == 'Non')>Non</option>
                        <option value="Oui" @selected(old('permis', $employe->permis) == 'Oui')>Oui</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Service militaire</label>
                    <select name="service_militaire" id="service_militaire" class="form-select">
                        <option value="Non" @selected(old('service_militaire', $employe->service_militaire) == 'Non')>Non</option>
                        <option value="Oui" @selected(old('service_militaire', $employe->service_militaire) == 'Oui')>Oui</option>
                    </select>
                </div>
                <div class="col-md-4 service-militaire-fields {{ old('service_militaire', $employe->service_militaire) === 'Oui' ? '' : 'd-none' }}">
                    <label class="form-label">Corps militaire</label>
                    <input type="text" name="corps_militaire" class="form-control" value="{{ old('corps_militaire', $employe->corps_militaire) }}">
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">Contrat en cours</div>
            <div class="card-body row g-3">
                @php($contratActif = $employe->contrats->sortByDesc('date_debut')->first())
                <div class="col-md-4">
                    <label class="form-label">Type de contrat *</label>
                    <select name="type_contrat" class="form-select" required>
                        <option value="CDI" @selected(old('type_contrat', $contratActif->type_contrat ?? '') == 'CDI')>CDI</option>
                        <option value="CDD" @selected(old('type_contrat', $contratActif->type_contrat ?? '') == 'CDD')>CDD</option>
                        <option value="Stage" @selected(old('type_contrat', $contratActif->type_contrat ?? '') == 'Stage')>Stage</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Salaire (FCFA) *</label>
                    <input type="number" name="montant" class="form-control" min="0" value="{{ old('montant', $contratActif->montant ?? '') }}" required>
                </div>
                <input type="hidden" name="contrat_id" value="{{ $contratActif->id ?? '' }}">
                <div class="col-md-4"></div>
                <div class="col-md-4">
                    <label class="form-label">Date de début *</label>
                    <input type="date" name="date_debut" class="form-control" value="{{ old('date_debut', $contratActif->date_debut?->format('Y-m-d') ?? '') }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Date de fin</label>
                    <input type="date" name="date_fin" class="form-control" value="{{ old('date_fin', $contratActif->date_prevu_fin?->format('Y-m-d') ?? '') }}">
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">Personne à contacter en cas d'urgence</div>
            <div class="card-body row g-3">
                <div class="col-md-4">
                    <label class="form-label">Nom</label>
                    <input type="text" name="personne_contact" class="form-control" value="{{ old('personne_contact', $employe->personne_contact) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Téléphone</label>
                    <input type="text" name="numero_contact" class="form-control" value="{{ old('numero_contact', $employe->numero_contact) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Lien de parenté</label>
                    <input type="text" name="lien_parente" class="form-control" value="{{ old('lien_parente', $employe->lien_parente) }}">
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Enregistrer</button>
        <a href="{{ route('employes.show', $employe->id) }}" class="btn btn-outline-secondary">Annuler</a>
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
