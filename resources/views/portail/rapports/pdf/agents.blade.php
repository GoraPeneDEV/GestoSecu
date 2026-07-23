<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rapport Agents</title>
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
    <h3>Rapport Agents — {{ now()->format('d/m/Y') }}</h3>

    <div class="stats">
        <div><strong>{{ $agents->count() }}</strong><br>Agents affectés</div>
        <div><strong>{{ $agents->pluck('plannings')->flatten()->pluck('site.id')->unique()->count() }}</strong><br>Sites couverts</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Matricule</th>
                <th>Agent</th>
                <th>Fonction</th>
                <th>Sites</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($agents as $agent)
                <tr>
                    <td>{{ $agent->matricule }}</td>
                    <td>{{ $agent->prenom }} {{ $agent->nom }}</td>
                    <td>{{ $agent->fonction ?? '-' }}</td>
                    <td>{{ $agent->plannings->pluck('site.nom_site')->filter()->unique()->implode(', ') }}</td>
                </tr>
            @empty
                <tr><td colspan="4">Aucun agent affecté.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
