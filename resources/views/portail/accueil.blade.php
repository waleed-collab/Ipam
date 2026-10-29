{{-- Vue : portail CLIENT (lecture seule) — le client ne voit QUE ses propres ressources.
     Couvre : Visualiser ses propres plages IP / les technologies associées / ses équipements. --}}
@extends('layouts.app')
@section('titre', 'Mes ressources')
@section('contenu')
    <header class="page">
        <div>
            <h1>Bienvenue, {{ $client->nom }}</h1>
            <div class="sous-titre">Consultation en lecture seule des ressources qui vous sont affectées.</div>
        </div>
        {{-- Cas "Le client peut uniquement télécharger" : aucune modification possible, juste le téléchargement --}}
        <div style="display:flex; gap:8px">
            <a class="btn secondaire" href="{{ route('portail.exporter', 'pdf') }}">Télécharger en PDF</a>
            <a class="btn secondaire" href="{{ route('portail.exporter', 'csv') }}">Télécharger en Excel/CSV</a>
        </div>
    </header>

    {{-- Bloc 1 : mes plages IP --}}
    <div class="carte" style="margin-bottom:18px">
        <h2 style="font-size:1rem; margin-bottom:12px">Mes sous-blocs d'adresses</h2>
        <table>
            <thead><tr><th>Réseau</th><th>Technologie</th><th>Description</th></tr></thead>
            <tbody>
                @forelse($plages as $plage) {{-- Uniquement les plages DU client connecté --}}
                    <tr>
                        <td class="mono">{{ $plage->reseau }}
                            <span class="badge accent">IPv{{ $plage->version_ip }}</span></td>
                        <td>{{ $plage->technologie->nom ?? '—' }}</td>
                        <td>{{ $plage->description ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3">Aucun sous-bloc d'adresses ne vous est affecté pour le moment.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Bloc [CAPACITY PLANNING] : ma bande passante, en LECTURE SEULE --}}
    <div class="carte" style="margin-bottom:18px">
        <h2 style="font-size:1rem; margin-bottom:6px">Ma bande passante</h2>
        <div class="sous-titre" style="margin-bottom:12px">
            Total souscrit : <b style="color:var(--accent)">{{ $bandeTotale }} Mbps</b> — consultation uniquement,
            contactez votre gestionnaire pour toute modification.
        </div>
        <table>
            <thead><tr><th>Offre</th><th>Technologie</th><th>Débit souscrit</th></tr></thead>
            <tbody>
                @forelse($allocations as $allocation) {{-- Uniquement les allocations DU client connecté --}}
                    <tr>
                        <td>{{ $allocation->description ?? '—' }}</td>
                        <td>{{ $allocation->technologie->nom }}</td>
                        <td class="mono">{{ $allocation->bande_passante_souscrite }} Mbps</td>
                    </tr>
                @empty
                    <tr><td colspan="3">Aucune bande passante souscrite pour le moment.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Bloc 2 : mes technologies (déduites de mes plages et équipements) --}}
    <div class="carte" style="margin-bottom:18px">
        <h2 style="font-size:1rem; margin-bottom:12px">Technologies associées</h2>
        @forelse($technologies as $technologie)
            <span class="badge gris" style="margin-right:6px">{{ $technologie->nom }}</span> {{-- Badge par techno --}}
        @empty
            <span class="sous-titre">Aucune technologie associée.</span>
        @endforelse
    </div>

    {{-- Bloc 3 : mes équipements --}}
    <div class="carte">
        <h2 style="font-size:1rem; margin-bottom:12px">Mes équipements</h2>
        <table>
            <thead><tr><th>Nom</th><th>Type</th><th>IP</th><th>Technologie</th></tr></thead>
            <tbody>
                @forelse($equipements as $equipement) {{-- Uniquement les équipements DU client connecté --}}
                    <tr>
                        <td>{{ $equipement->nom }}</td>
                        <td>{{ $equipement->type }}</td>
                        <td class="mono">{{ $equipement->adresse_ip ?? '—' }}</td>
                        <td>{{ $equipement->technologie->nom ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4">Aucun équipement ne vous est rattaché.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
