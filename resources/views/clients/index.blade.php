{{-- Vue : liste des clients (cas "Consulter la liste des clients"). --}}
@extends('layouts.app')
@section('titre', 'Clients')
@section('contenu')
    <header class="page">
        <div>
            <h1>Clients</h1>
            <div class="sous-titre">Entités à qui des ressources réseau sont affectées.</div>
        </div>
        <a class="btn" href="{{ route('clients.create') }}">Ajouter un client</a> {{-- Cas "Ajouter" --}}
    </header>

    {{-- Colonnes du schéma : nom du client, plage(s) attribuée(s), technologie d'accès, bande passante.
         La colonne « Plages attribuées » affiche le NOMBRE D'ADRESSES contenues (convention de l'app :
         IPv4 = 2^(32-masque) adresses ; IPv6 = 2^(64-masque) réseaux /64), et non le CIDR brut. --}}
    <table>
        <thead><tr><th>Nom du client</th><th>Sous-blocs attribués (nombre d'adresses)</th><th>Technologie d'accès</th><th>Bande passante</th><th>Actions</th></tr></thead>
        <tbody>
            @forelse($clients as $client)
                @php
                    // Somme des adresses IPv4 et des réseaux /64 IPv6 sur toutes les plages du client.
                    $adressesIpv4 = $client->plagesIp->where('version_ip', 4)->sum(fn ($p) => $p->nombreAdresses() ?? 0);
                    $reseauxIpv6  = $client->plagesIp->where('version_ip', 6)->sum(fn ($p) => $p->nombreAdresses() ?? 0);
                @endphp
                <tr>
                    <td>{{ $client->nom }}</td>
                    <td>
                        {{-- Décompte d'adresses (IPv4) et de réseaux /64 (IPv6) des plages affectées --}}
                        @if($adressesIpv4 || $reseauxIpv6)
                            @if($adressesIpv4)
                                <b>{{ number_format($adressesIpv4, 0, ',', ' ') }}</b> adresses
                            @endif
                            @if($reseauxIpv6)
                                <div class="sous-titre">+ {{ number_format($reseauxIpv6, 0, ',', ' ') }} réseaux /64 (IPv6)</div>
                            @endif
                        @else
                            —
                        @endif
                    </td>
                    <td>
                        {{-- Technologies d'accès déduites des plages et allocations du client --}}
                        {{ $client->plagesIp->pluck('technologie.nom')
                            ->merge($client->allocations->pluck('technologie.nom'))
                            ->filter()->unique()->implode(', ') ?: '—' }}
                    </td>
                    <td>
                        {{-- [CAPACITY PLANNING] Somme des débits souscrits par le client --}}
                        {{ $client->allocations->sum('bande_passante_souscrite')
                            ? number_format($client->allocations->sum('bande_passante_souscrite'), 0, ',', ' ') . ' Mbps' : '—' }}
                    </td>
                    <td>
                        <div class="actions">
                            {{-- Fiche : plages, technologies et équipements du client --}}
                            <a class="btn petit secondaire" href="{{ route('clients.show', $client) }}">Détails</a>
                            <a class="btn petit secondaire" href="{{ route('clients.edit', $client) }}">Modifier</a>
                            <form method="POST" action="{{ route('clients.destroy', $client) }}"
                                  onsubmit="return confirm('Supprimer ce client ? Ses blocs d\'adresses redeviendront disponibles.')">
                                @csrf @method('DELETE')
                                <button class="btn petit danger" type="submit">Supprimer</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5">Aucun client enregistré.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div style="margin-top:14px">{{ $clients->links() }}</div>
@endsection
