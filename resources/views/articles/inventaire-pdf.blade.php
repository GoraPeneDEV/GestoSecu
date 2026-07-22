<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Inventaire {{ $periode['mois_nom'] }} {{ $periode['annee'] }}</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 3px 5px; text-align: left; }
        th { background: #f0f0f0; }
    </style>
</head>
<body>
    <h3>Rapport d'inventaire — {{ $periode['mois_nom'] }} {{ $periode['annee'] }}</h3>
    <p>Généré le {{ $periode['date_generation'] }}</p>
    <p>
        Articles : {{ $statistiques['total_articles'] }} —
        Valeur totale : {{ number_format($statistiques['valeur_totale'], 0, ',', ' ') }} FCFA —
        En rupture : {{ $statistiques['articles_rupture'] }}
    </p>
    <table>
        <thead>
            <tr>
                <th>Référence</th>
                <th>Désignation</th>
                <th>Département</th>
                <th>Stock</th>
                <th>Stock min.</th>
                <th>Prix unitaire</th>
                <th>Valeur</th>
                <th>Observation</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($donnees as $row)
                <tr>
                    <td>{{ $row['reference'] }}</td>
                    <td>{{ $row['designation'] }}</td>
                    <td>{{ $row['departement'] }}</td>
                    <td>{{ $row['stock_actuel'] }}</td>
                    <td>{{ $row['stock_minimum'] }}</td>
                    <td>{{ number_format($row['prix_unitaire'], 0, ',', ' ') }}</td>
                    <td>{{ number_format($row['valeur_stock'], 0, ',', ' ') }}</td>
                    <td>{{ $row['observation'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
