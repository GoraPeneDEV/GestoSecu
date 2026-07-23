<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rapport Sites</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ccc; padding: 4px 6px; text-align: left; }
        th { background: #f0f0f0; }
        .stats { display: flex; gap: 20px; margin-top: 10px; }
        .stats div { border: 1px solid #ccc; padding: 8px 16px; text-align: center; }
    </style>
</head>
<body>
    <h3>Rapport Sites — du {{ \Carbon\Carbon::parse($dateDebut)->format('d/m/Y') }} au {{ \Carbon\Carbon::parse($dateFin)->format('d/m/Y') }}</h3>

    <div class="stats">
        <div><strong>{{ $sites->count() }}</strong><br>Sites</div>
        <div><strong>{{ $sites->whereNull('date_arret')->count() }}</strong><br>Actifs</div>
        <div><strong>{{ $sites->whereNotNull('date_arret')->count() }}</strong><br>Archivés</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Site</th>
                <th>Type</th>
                <th>Région</th>
                <th>Début</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($sites as $site)
                <tr>
                    <td>{{ $site->nom_site }}</td>
                    <td>{{ $site->type_site ?? '-' }}</td>
                    <td>{{ $site->region ?? '-' }}</td>
                    <td>{{ \Carbon\Carbon::parse($site->date_debut)->format('d/m/Y') }}</td>
                    <td>{{ $site->date_arret ? 'Archivé' : 'Actif' }}</td>
                </tr>
            @empty
                <tr><td colspan="5">Aucun site sur la période.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
