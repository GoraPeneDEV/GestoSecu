<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Déclaration IPRES</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ccc; padding: 4px 6px; text-align: right; }
        th, td:first-child, td:nth-child(2) { text-align: left; }
        th { background: #f0f0f0; }
        tfoot td { font-weight: bold; background: #f7f7f7; }
    </style>
</head>
<body>
    <h3>Déclaration IPRES — {{ str_pad($mois, 2, '0', STR_PAD_LEFT) }}/{{ $annee }}</h3>

    <table>
        <thead>
            <tr>
                <th>Matricule</th>
                <th>Employé</th>
                <th>N° IPRES</th>
                <th>Salaire brut</th>
                <th>Cotisation salariale</th>
                <th>Cotisation patronale</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($bulletins as $b)
                <tr>
                    <td>{{ $b->employe->matricule ?? '-' }}</td>
                    <td>{{ $b->employe->prenom ?? '' }} {{ $b->employe->nom ?? '-' }}</td>
                    <td>{{ $b->employe->paieData->numero_ipres ?? '-' }}</td>
                    <td>{{ number_format($b->salaire_brut, 0, ',', ' ') }}</td>
                    <td>{{ number_format($b->cotisation_ipres, 0, ',', ' ') }}</td>
                    <td>{{ number_format($b->cotisation_patronale_ipres, 0, ',', ' ') }}</td>
                    <td>{{ number_format($b->cotisation_ipres + $b->cotisation_patronale_ipres, 0, ',', ' ') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4">Total ({{ $bulletins->count() }} employés)</td>
                <td>{{ number_format($totalCotisationSalariale, 0, ',', ' ') }}</td>
                <td>{{ number_format($totalCotisationPatronale, 0, ',', ' ') }}</td>
                <td>{{ number_format($totalCotisationSalariale + $totalCotisationPatronale, 0, ',', ' ') }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
