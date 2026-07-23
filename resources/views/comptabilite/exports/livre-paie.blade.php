<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Livre de paie</title>
    <style>
        body { font-family: sans-serif; font-size: 9px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 3px 4px; text-align: right; }
        th, td:first-child, td:nth-child(2) { text-align: left; }
        th { background: #f0f0f0; }
        tfoot td { font-weight: bold; background: #f7f7f7; }
    </style>
</head>
<body>
    <h3>Livre de paie — {{ str_pad($mois, 2, '0', STR_PAD_LEFT) }}/{{ $annee }}</h3>
    <table>
        <thead>
            <tr>
                <th>Matricule</th>
                <th>Employé</th>
                <th>Salaire base</th>
                <th>Brut</th>
                <th>IPRES sal.</th>
                <th>CSS sal.</th>
                <th>IPM sal.</th>
                <th>Cot. salariales</th>
                <th>IR</th>
                <th>Net à payer</th>
                <th>Cot. patronales</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($bulletins as $b)
                <tr>
                    <td>{{ $b->employe->matricule ?? '-' }}</td>
                    <td>{{ $b->employe->prenom ?? '' }} {{ $b->employe->nom ?? '-' }}</td>
                    <td>{{ number_format($b->salaire_base, 0, ',', ' ') }}</td>
                    <td>{{ number_format($b->salaire_brut, 0, ',', ' ') }}</td>
                    <td>{{ number_format($b->cotisation_ipres, 0, ',', ' ') }}</td>
                    <td>{{ number_format($b->cotisation_css, 0, ',', ' ') }}</td>
                    <td>{{ number_format($b->cotisation_ipm, 0, ',', ' ') }}</td>
                    <td>{{ number_format($b->total_cotisations_salariales, 0, ',', ' ') }}</td>
                    <td>{{ number_format($b->impot_revenu, 0, ',', ' ') }}</td>
                    <td>{{ number_format($b->salaire_net_a_payer, 0, ',', ' ') }}</td>
                    <td>{{ number_format($b->total_cotisations_patronales, 0, ',', ' ') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2">Total ({{ $bulletins->count() }} bulletins)</td>
                <td>{{ number_format($bulletins->sum('salaire_base'), 0, ',', ' ') }}</td>
                <td>{{ number_format($bulletins->sum('salaire_brut'), 0, ',', ' ') }}</td>
                <td>{{ number_format($bulletins->sum('cotisation_ipres'), 0, ',', ' ') }}</td>
                <td>{{ number_format($bulletins->sum('cotisation_css'), 0, ',', ' ') }}</td>
                <td>{{ number_format($bulletins->sum('cotisation_ipm'), 0, ',', ' ') }}</td>
                <td>{{ number_format($bulletins->sum('total_cotisations_salariales'), 0, ',', ' ') }}</td>
                <td>{{ number_format($bulletins->sum('impot_revenu'), 0, ',', ' ') }}</td>
                <td>{{ number_format($bulletins->sum('salaire_net_a_payer'), 0, ',', ' ') }}</td>
                <td>{{ number_format($bulletins->sum('total_cotisations_patronales'), 0, ',', ' ') }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
