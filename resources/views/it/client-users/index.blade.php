@extends('layouts.contentNavbarLayout')

@section('title', 'Comptes du portail client')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Comptes du portail client</h3>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCompte" onclick="newCompte()">
            <i class="ti ti-plus-lg"></i> Nouveau compte
        </button>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="card text-center"><div class="card-body">
                <h4 class="mb-0">{{ $stats['total'] }}</h4><small class="text-muted">Total</small>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card text-center"><div class="card-body">
                <h4 class="mb-0 text-success">{{ $stats['actifs'] }}</h4><small class="text-muted">Actifs</small>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card text-center"><div class="card-body">
                <h4 class="mb-0 text-secondary">{{ $stats['inactifs'] }}</h4><small class="text-muted">Inactifs</small>
            </div></div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-2">
                <div class="col-auto">
                    <select id="filterClient" class="form-select form-select-sm">
                        <option value="">Tous les clients</option>
                        @foreach ($clients as $client)
                            <option value="{{ $client->id }}">{{ $client->nomClient }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <select id="filterStatus" class="form-select form-select-sm">
                        <option value="">Tous les statuts</option>
                        <option value="active">Actif</option>
                        <option value="inactive">Inactif</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="comptesTable" class="table table-hover w-100">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Client</th>
                            <th>Email</th>
                            <th>Statut</th>
                            <th>Dernière connexion</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalCompte" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="compteForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalCompteTitle">Nouveau compte</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="compte_id">
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label">Client *</label>
                                <select id="client_id" class="form-select" required>
                                    <option value="">-- Sélectionner --</option>
                                    @foreach ($clients as $client)
                                        <option value="{{ $client->id }}">{{ $client->nomClient }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Statut *</label>
                                <select id="status" class="form-select" required>
                                    <option value="active">Actif</option>
                                    <option value="inactive">Inactif</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Prénom *</label>
                                <input type="text" id="prenom" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nom *</label>
                                <input type="text" id="nom" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email *</label>
                                <input type="email" id="email" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Téléphone</label>
                                <input type="text" id="telephone" class="form-control">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Fonction</label>
                                <input type="text" id="fonction" class="form-control">
                            </div>
                        </div>
                        <p class="text-muted small mt-2 mb-0" id="newAccountNotice">Un mot de passe temporaire sera généré et affiché à la création.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalPassword" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Mot de passe temporaire</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Communiquez ce mot de passe au client — il ne sera plus affiché ensuite :</p>
                    <div class="input-group">
                        <input type="text" id="generatedPassword" class="form-control fw-bold" readonly>
                        <button type="button" class="btn btn-outline-secondary" onclick="navigator.clipboard.writeText(document.getElementById('generatedPassword').value)">
                            <i class="ti ti-copy"></i>
                        </button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
function newCompte() {
    document.getElementById('compteForm').reset();
    document.getElementById('compte_id').value = '';
    document.getElementById('modalCompteTitle').textContent = 'Nouveau compte';
    document.getElementById('newAccountNotice').classList.remove('d-none');
}

function editCompte(id) {
    fetch('{{ url('it/client-users') }}/' + id + '/edit')
        .then(r => r.json())
        .then(c => {
            document.getElementById('compte_id').value = c.id;
            document.getElementById('client_id').value = c.client_id;
            document.getElementById('status').value = c.status;
            document.getElementById('prenom').value = c.prenom;
            document.getElementById('nom').value = c.nom;
            document.getElementById('email').value = c.email;
            document.getElementById('telephone').value = c.telephone || '';
            document.getElementById('fonction').value = c.fonction || '';
            document.getElementById('modalCompteTitle').textContent = 'Modifier le compte';
            document.getElementById('newAccountNotice').classList.add('d-none');
            new bootstrap.Modal(document.getElementById('modalCompte')).show();
        });
}

function resetPasswordCompte(id) {
    if (!confirm('Réinitialiser le mot de passe de ce compte ?')) return;
    fetch('{{ url('it/client-users') }}/' + id + '/reset-password', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
    })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                document.getElementById('generatedPassword').value = res.mot_de_passe_temporaire;
                new bootstrap.Modal(document.getElementById('modalPassword')).show();
            } else {
                alert(res.message || 'Erreur');
            }
        });
}

function deleteCompte(id) {
    if (!confirm('Supprimer ce compte ?')) return;
    fetch('{{ url('it/client-users') }}/' + id, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
    })
        .then(r => r.json())
        .then(res => {
            if (res.success) { $('#comptesTable').DataTable().ajax.reload(); } else { alert(res.message); }
        });
}

$(function () {
    var table = $('#comptesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('it.client-users.index') }}',
            data: function (d) {
                d.client_id = $('#filterClient').val();
                d.status = $('#filterStatus').val();
            }
        },
        columns: [
            { data: 'nom_complet', name: 'nom', orderable: false },
            { data: 'client_nom', name: 'client.nomClient', orderable: false },
            { data: 'email', name: 'email' },
            { data: 'status_badge', name: 'status', orderable: false },
            { data: 'last_login_at', name: 'last_login_at' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false },
        ],
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/fr-FR.json' },
    });

    $('#filterClient, #filterStatus').on('change', function () { table.ajax.reload(); });

    document.getElementById('compteForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const id = document.getElementById('compte_id').value;
        const payload = {
            client_id: document.getElementById('client_id').value,
            status: document.getElementById('status').value,
            prenom: document.getElementById('prenom').value,
            nom: document.getElementById('nom').value,
            email: document.getElementById('email').value,
            telephone: document.getElementById('telephone').value,
            fonction: document.getElementById('fonction').value,
        };
        const url = id ? '{{ url('it/client-users') }}/' + id : '{{ route('it.client-users.store') }}';
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-HTTP-Method-Override': id ? 'PUT' : 'POST',
            },
            body: JSON.stringify(payload),
        })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    bootstrap.Modal.getInstance(document.getElementById('modalCompte')).hide();
                    table.ajax.reload();
                    if (res.mot_de_passe_temporaire) {
                        document.getElementById('generatedPassword').value = res.mot_de_passe_temporaire;
                        new bootstrap.Modal(document.getElementById('modalPassword')).show();
                    }
                } else {
                    alert(res.message || 'Erreur');
                }
            });
    });
});
</script>
@endpush
