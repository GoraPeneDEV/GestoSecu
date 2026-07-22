<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rapport d'intervention {{ $intervention->numero_intervention }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        h2, h4 { margin-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #ccc; padding: 4px 6px; text-align: left; }
        th { background: #f0f0f0; }
        dl { display: flex; flex-wrap: wrap; }
        dt { width: 40%; font-weight: bold; }
        dd { width: 60%; margin: 0; }
    </style>
</head>
<body>
    <h2>Rapport d'intervention {{ $intervention->numero_intervention }}</h2>
    <dl>
        <dt>Client</dt><dd>{{ $intervention->site->client->nomClient ?? '-' }}</dd>
        <dt>Site</dt><dd>{{ $intervention->site->nom_site ?? '-' }}</dd>
        <dt>Type</dt><dd>{{ ucfirst(str_replace('_', ' ', $intervention->type)) }}</dd>
        <dt>Technicien</dt><dd>{{ $intervention->technicien->nom_complet ?? '-' }}</dd>
        <dt>Date</dt><dd>{{ $intervention->date_intervention?->format('d/m/Y') }}</dd>
    </dl>

    <h4>Recommandations générales</h4>
    <p>{{ $intervention->recommandations_generales ?? 'Aucune.' }}</p>

    <h4>Appareils concernés</h4>
    <table>
        <thead>
            <tr>
                <th>Appareil</th>
                <th>Actions faites</th>
                <th>Recommandation</th>
                <th>Statut après</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($intervention->assets as $asset)
                <tr>
                    <td>{{ $asset->label ?? $asset->type }}</td>
                    <td>{{ $asset->pivot->actions_faites ?? '-' }}</td>
                    <td>{{ $asset->pivot->recommandation_specifique ?? '-' }}</td>
                    <td>{{ $asset->pivot->statut_apres ?? '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="4">Aucun appareil.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
