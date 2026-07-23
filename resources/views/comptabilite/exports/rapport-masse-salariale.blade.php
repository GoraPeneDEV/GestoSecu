<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rapport de masse salariale</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ccc; padding: 4px 6px; text-align: right; }
        th, td:first-child { text-align: left; }
        th { background: #f0f0f0; }
        .stats { display: flex; gap: 20px; margin-top: 10px; }
        .stats div { border: 1px solid #ccc; padding: 8px 16px; text-align: center; }
    </style>
</head>
<body>
    <h3>Rapport de masse salariale — {{ str_pad($mois, 2, '0', STR_PAD_LEFT) }}/{{ $annee }}</h3>

    <div class="stats">
        <div><strong>{{ $bulletins->count() }}</strong><br>Bulletins</div>
        <div><strong>{{ number_format($bulletins->sum('salaire_brut'), 0, ',', ' ') }}</strong><br>Masse brute (FCFA)</div>
        <div><strong>{{ number_format($bulletins->sum('salaire_net_a_payer'), 0, ',', ' ') }}</strong><br>Masse nette (FCFA)</div>
        <div><strong>{{ number_format($bulletins->sum('total_cotisations_patronales'), 0, ',', ' ') }}</strong><br>Charges patronales (FCFA)</div>
    </div>

    <h4>Répartition par département</h4>
    <table>
        <thead>
            <tr>
                <th>Département</th>
                <th>Employés</th>
                <th>Masse brute</th>
                <th>Masse nette</th>
                <th>Cot. salariales</th>
                <th>Cot. patronales</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($parDepartement as $nom => $ligne)
                <tr>
                    <td>{{ $nom }}</td>
                    <td>{{ $ligne['nb_employes'] }}</td>
                    <td>{{ number_format($ligne['masse_brute'], 0, ',', ' ') }}</td>
                    <td>{{ number_format($ligne['masse_nette'], 0, ',', ' ') }}</td>
                    <td>{{ number_format($ligne['cotisations_salariales'], 0, ',', ' ') }}</td>
                    <td>{{ number_format($ligne['cotisations_patronales'], 0, ',', ' ') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
