<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Rapport d'anomalies - Ronde {{ $ronde->id }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .anomalie {
            margin-bottom: 20px;
            padding: 10px;
            border: 1px solid #ddd;
        }

        .anomalie h3 {
            margin-top: 0;
            color: #dc3545;
        }

        .photo {
            max-width: 300px;
            margin-top: 10px;
        }

        .info {
            margin-bottom: 5px;
        }

    </style>
</head>
<body>
    <div class="header">
        <h1>Rapport d'anomalies</h1>
        <h2>Ronde: {{ $ronde->planningRonde->nom }}</h2>
    </div>

    <div class="info">
        <p><strong>Date:</strong> {{ $ronde->date_debut->format('d/m/Y') }}</p>
        <p><strong>Agent:</strong> {{ $ronde->agent->prenom }} {{ $ronde->agent->nom }}</p>
        <p><strong>Site:</strong> {{ $ronde->planningRonde->site->nom_site }}</p>
    </div>

    <hr>

    @foreach($anomalies as $scan)
    <div class="anomalie">
        <h3>Point de contrôle: {{ $scan->pointControle->nom }}</h3>
        <p><strong>Heure:</strong> {{ $scan->date_scan->format('H:i:s') }}</p>
        <p><strong>Type:</strong> {{ $scan->type_anomalie }}</p>
        <p><strong>Description:</strong> {{ $scan->commentaire }}</p>

        @if($scan->photo_url)
        <img src="{{ public_path('storage/' . $scan->photo_url) }}" alt="Photo anomalie" class="photo">
        @endif
    </div>
    @endforeach
</body>
</html>
