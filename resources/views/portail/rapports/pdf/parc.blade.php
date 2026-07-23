<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rapport Parc</title>
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
    <h3>Rapport Parc / Équipements — {{ now()->format('d/m/Y') }}</h3>

    <div class="stats">
        <div><strong>{{ $assets->count() }}</strong><br>Équipements</div>
        <div><strong>{{ $parStatut['fonctionnel'] ?? 0 }}</strong><br>Fonctionnels</div>
        <div><strong>{{ ($parStatut['panne'] ?? 0) + ($parStatut['maintenance_requise'] ?? 0) }}</strong><br>En maintenance / panne</div>
        <div><strong>{{ $parStatut['hors_service'] ?? 0 }}</strong><br>Hors service</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Site</th>
                <th>Type</th>
                <th>Libellé</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($assets as $asset)
                <tr>
                    <td>{{ $asset->site->nom_site ?? '-' }}</td>
                    <td>{{ $asset->type }}</td>
                    <td>{{ $asset->label ?? '-' }}</td>
                    <td>{{ str_replace('_', ' ', ucfirst($asset->status)) }}</td>
                </tr>
            @empty
                <tr><td colspan="4">Aucun équipement.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
