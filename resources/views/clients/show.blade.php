{{-- Vue : fiche détaillée d'un client — ses plages IP, équipements et affectation rapide
     (cas "Associer un client à une plage IP et une technologie" et
      "Visualiser les équipements rattachés à un client"). --}}
@extends('layouts.app')
@section('titre', 'Détail client')
@section('contenu')
    <header class="page">
        <div>
            <h1>{{ $client->nom }}</h1>
            <div class="sous-titre">{{ $client->email }} · {{ $client->telephone ?? 'Téléphone non renseigné' }} · {{ $client->adresse ?? '' }}</div>
        </div>
        {{-- [DESIGN] Action PRINCIPALE de la fiche, mise en avant en haut à droite
             (cas "modifier avant de télécharger les informations client"). --}}
        <a class="btn" href="{{ route('clients.exporter.formulaire', $client) }}">Exporter la fiche</a>
    </header>

    {{-- Bandeau récapitulatif : les 4 informations du schéma (nom en titre, puis plage / technologie / bande passante) --}}
    <div class="stats" style="margin-bottom:18px">
        <div class="stat">
            <div class="stat-corps">
                <div class="valeur">{{ $client->plagesIp->count() }}</div>
                <div class="libelle">Sous-bloc(s) attribué(s)</div>
            </div>
        </div>
        <div class="stat">
            <div class="stat-corps">
                <div class="valeur" style="font-size:1rem">{{ $client->technologiesAssociees()->pluck('nom')->implode(', ') ?: '—' }}</div>
                <div class="libelle">Technologie(s) d'accès</div>
            </div>
        </div>
        <div class="stat">
            <div class="stat-corps">
                <div class="valeur">{{ number_format($client->bandePassanteTotale(), 0, ',', ' ') }} <small style="font-size:.8rem">Mbps</small></div>
                <div class="libelle">Bande passante souscrite</div>
            </div>
        </div>
    </div>

    {{-- Bloc 1 : plages IP affectées + affectation rapide d'une plage disponible --}}
    <div class="carte" style="margin-bottom:18px">
        <h2 style="font-size:1rem; margin-bottom:12px">Sous-blocs d'adresses affectés</h2>
        <table>
            <thead><tr><th>Réseau</th><th>Technologie</th><th>Description</th></tr></thead>
            <tbody>
                @forelse($client->plagesIp as $plage)
                    <tr>
                        <td class="mono">{{ $plage->reseau }}</td>
                        <td>{{ $plage->technologie->nom ?? '—' }}</td>
                        <td>{{ $plage->description ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3">Aucun sous-bloc d'adresses affecté à ce client.</td></tr>
                @endforelse
            </tbody>
        </table>

        {{-- Affectation rapide : liste des plages encore disponibles --}}
        @if($plagesDisponibles->isNotEmpty())
            <form method="POST" action="{{ route('clients.affecter-plage', $client) }}"
                  style="display:flex; gap:8px; margin-top:12px; max-width:480px">
                @csrf
                <select name="plage_ip_id" required style="flex:1; padding:8px; border:1px solid var(--bord); border-radius:6px">
                    <option value="">— Affecter un bloc disponible —</option>
                    @foreach($plagesDisponibles as $plage)
                        <option value="{{ $plage->id }}">{{ $plage->reseau }} ({{ $plage->technologie->nom ?? 'sans technologie' }})</option>
                    @endforeach
                </select>
                <button class="btn petit" type="submit">Affecter</button>
            </form>
        @endif
    </div>

    {{-- Bloc 2 : équipements installés chez le client --}}
    <div class="carte">
        <h2 style="font-size:1rem; margin-bottom:12px">Équipements rattachés</h2>
        <table>
            <thead><tr><th>Nom</th><th>Type</th><th>IP</th><th>Technologie</th></tr></thead>
            <tbody>
                @forelse($client->equipements as $equipement)
                    <tr>
                        <td>{{ $equipement->nom }}</td>
                        <td>{{ $equipement->type }}</td>
                        <td class="mono">{{ $equipement->adresse_ip ?? '—' }}</td>
                        <td>{{ $equipement->technologie->nom ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4">Aucun équipement rattaché à ce client.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- [DESIGN] Navigation de retour en bas de page, alignée à droite (pied d'action standard). --}}
    <div class="barre-actions">
        <a class="btn secondaire" href="{{ route('clients.index') }}">Retour à la liste</a>
    </div>
@endsection
