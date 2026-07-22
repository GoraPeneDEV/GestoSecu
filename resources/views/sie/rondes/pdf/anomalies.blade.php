<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Anomalies — Ronde #{{ $ronde->id }}</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ccc; padding: 4px 6px; text-align: left; }
        th { background: #f0f0f0; }
        dl { display: flex; flex-wrap: wrap; }
        dt { width: 30%; font-weight: bold; }
        dd { width: 70%; margin: 0; }
    </style>
</head>
<body>
    <h3>Rapport d'anomalies — Ronde #{{ $ronde->id }}</h3>
    <dl>
        <dt>Site</dt><dd>{{ $ronde->planningRonde->site->nom_site ?? '-' }}</dd>
        <dt>Agent</dt><dd>{{ $ronde->agent->prenom ?? '' }} {{ $ronde->agent->nom ?? '-' }}</dd>
        <dt>Date</dt><dd>{{ $ronde->date_debut?->format('d/m/Y H:i') }} — {{ $ronde->date_fin?->format('d/m/Y H:i') ?? 'en cours' }}</dd>
    </dl>

    <table>
        <thead>
            <tr>
                <th>Point de contrôle</th>
                <th>Date du scan</th>
                <th>Type d'anomalie</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($anomalies as $anomalie)
                <tr>
                    <td>{{ $anomalie->pointControle->nom ?? '-' }}</td>
                    <td>{{ $anomalie->date_scan?->format('d/m/Y H:i') }}</td>
                    <td>{{ $anomalie->type_anomalie ?? '-' }}</td>
                    <td>{{ $anomalie->commentaire ?? '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="4">Aucune anomalie.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
