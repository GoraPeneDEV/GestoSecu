<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rapport Rondes</title>
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
    <h3>Rapport Rondes — du {{ \Carbon\Carbon::parse($dateDebut)->format('d/m/Y') }} au {{ \Carbon\Carbon::parse($dateFin)->format('d/m/Y') }}</h3>

    <div class="stats">
        <div><strong>{{ $totalRondes }}</strong><br>Rondes</div>
        <div><strong>{{ $rondesTerminees }}</strong><br>Terminées</div>
        <div><strong>{{ $tauxCompletion }}%</strong><br>Taux de complétion</div>
        <div><strong>{{ $totalAnomalies }}</strong><br>Anomalies</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Agent</th>
                <th>Site</th>
                <th>Début</th>
                <th>Fin</th>
                <th>Statut</th>
                <th>Anomalies</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rondes as $ronde)
                <tr>
                    <td>{{ $ronde->agent->prenom ?? '' }} {{ $ronde->agent->nom ?? '-' }}</td>
                    <td>{{ $ronde->planningRonde->site->nom_site ?? '-' }}</td>
                    <td>{{ $ronde->date_debut?->format('d/m/Y H:i') }}</td>
                    <td>{{ $ronde->date_fin?->format('d/m/Y H:i') ?? '-' }}</td>
                    <td>{{ $ronde->statut === 'en_cours' ? 'En cours' : 'Terminée' }}</td>
                    <td>{{ $ronde->scans->where('anomalie', true)->count() }}</td>
                </tr>
            @empty
                <tr><td colspan="6">Aucune ronde sur la période.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
