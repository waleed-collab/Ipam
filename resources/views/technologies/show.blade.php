{{-- Vue : fiche d'une technologie — équipements et plages IP associés
     (cas "Consulter les équipements par technologie"). --}}
@extends('layouts.app')
@section('titre', 'Détail technologie')
@section('contenu')
    <header class="page">
        <div>
            <h1>{{ $technologie->nom }}</h1>
            <div class="sous-titre">{{ $technologie->description ?? 'Aucune description.' }}</div>
        </div>
    </header>

    <div class="carte" style="margin-bottom:18px">
        <h2 style="font-size:1rem; margin-bottom:12px">Équipements utilisant cette technologie</h2>
        <table>
            {{-- [PÉRIMÈTRE] Pas de colonne « Client » : le lien équipement→client est masqué
                 dans toute l'interface équipements. --}}
            <thead><tr><th>Nom</th><th>Type</th><th>IP</th></tr></thead>
            <tbody>
                @forelse($technologie->equipements as $equipement)
                    <tr>
                        <td>{{ $equipement->nom }}</td>
                        <td>{{ $equipement->type }}</td>
                        <td class="mono">{{ $equipement->adresse_ip ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3">Aucun équipement associé.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="carte">
        <h2 style="font-size:1rem; margin-bottom:12px">Sous-blocs d'adresses associés</h2>
        <table>
            <thead><tr><th>Réseau</th><th>Statut</th><th>Client</th></tr></thead>
            <tbody>
                @forelse($technologie->plagesIp as $plage)
                    <tr>
                        <td class="mono">{{ $plage->reseau }}</td>
                        <td><span class="badge {{ $plage->statut === 'disponible' ? 'vert' : 'gris' }}">{{ ucfirst($plage->statut) }}</span></td>
                        <td>{{ $plage->client->nom ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3">Aucun sous-bloc d'adresses associé.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- [DESIGN] Bouton retour en bas de page, aligné à droite (pied d'action standard). --}}
    <div class="barre-actions">
        <a class="btn secondaire" href="{{ route('technologies.index') }}">Retour à la liste</a>
    </div>
@endsection
