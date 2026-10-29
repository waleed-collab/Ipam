<!DOCTYPE html>
{{-- Vue PDF (dompdf) : fiche client. CSS volontairement simple, dompdf ne supporte pas tout le CSS moderne. --}}
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #16232e; }
        h1 { font-size: 18px; margin-bottom: 2px; }
        h2 { font-size: 13px; margin: 18px 0 6px; border-bottom: 1px solid #0f6a7d; padding-bottom: 3px; color: #0f6a7d; }
        .sous-titre { color: #5b6b78; font-size: 11px; margin-bottom: 14px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        th { text-align: left; background: #fafbfc; border: 1px solid #e2e6ea; padding: 5px 8px; font-size: 10px; text-transform: uppercase; }
        td { border: 1px solid #e2e6ea; padding: 5px 8px; font-size: 11px; }
        .infos td:first-child { font-weight: bold; width: 130px; background: #fafbfc; }
    </style>
</head>
<body>
    <h1>Fiche client — {{ $fiche->nom }}</h1>
    <div class="sous-titre">Document généré le {{ now()->format('d/m/Y à H:i') }}</div>

    <h2>Informations générales</h2>
    <table class="infos">
        <tr><td>Nom / raison sociale</td><td>{{ $fiche->nom }}</td></tr>
        <tr><td>Email</td><td>{{ $fiche->email }}</td></tr>
        <tr><td>Téléphone</td><td>{{ $fiche->telephone ?? '—' }}</td></tr>
        <tr><td>Adresse</td><td>{{ $fiche->adresse ?? '—' }}</td></tr>
    </table>

    <h2>Sous-blocs d'adresses affectés</h2>
    <table>
        <thead><tr><th>Réseau</th><th>Technologie</th><th>Description</th></tr></thead>
        <tbody>
            @forelse($client->plagesIp as $plage)
                <tr>
                    <td>{{ $plage->reseau }}</td>
                    <td>{{ $plage->technologie->nom ?? '—' }}</td>
                    <td>{{ $plage->description ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="3">Aucun sous-bloc d'adresses affecté.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Équipements rattachés</h2>
    <table>
        <thead><tr><th>Nom</th><th>Type</th><th>Adresse IP</th><th>Technologie</th></tr></thead>
        <tbody>
            @forelse($client->equipements as $equipement)
                <tr>
                    <td>{{ $equipement->nom }}</td>
                    <td>{{ $equipement->type }}</td>
                    <td>{{ $equipement->adresse_ip ?? '—' }}</td>
                    <td>{{ $equipement->technologie->nom ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="4">Aucun équipement rattaché.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Bande passante souscrite</h2>
    <table>
        <thead><tr><th>Offre</th><th>Technologie</th><th>Débit souscrit</th></tr></thead>
        <tbody>
            @forelse($client->allocations as $allocation)
                <tr>
                    <td>{{ $allocation->description ?? '—' }}</td>
                    <td>{{ $allocation->technologie->nom ?? '—' }}</td>
                    <td>{{ $allocation->bande_passante_souscrite }} Mbps</td>
                </tr>
            @empty
                <tr><td colspan="3">Aucune bande passante souscrite.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
