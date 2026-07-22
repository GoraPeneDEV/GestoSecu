<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Maintenances</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 4px 6px; text-align: left; }
        th { background: #f0f0f0; }
    </style>
</head>
<body>
    <h3>Liste des maintenances — {{ now()->format('d/m/Y') }}</h3>
    <table>
        <thead>
            <tr>
                <th>Client</th>
                <th>Site</th>
                <th>Contrat</th>
                <th>Date prévue</th>
                <th>Date réalisation</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($maintenances as $m)
                <tr>
                    <td>{{ $m->contrat->client->nomClient ?? '-' }}</td>
                    <td>{{ $m->site->nom_site ?? '-' }}</td>
                    <td>{{ $m->contrat->numero_contrat ?? '-' }}</td>
                    <td>{{ \Carbon\Carbon::parse($m->date_prevue)->format('d/m/Y') }}</td>
                    <td>{{ $m->date_realisation ? \Carbon\Carbon::parse($m->date_realisation)->format('d/m/Y') : '-' }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $m->status)) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
