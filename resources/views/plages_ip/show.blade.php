{{-- Vue : fiche détaillée d'une plage IP.
     [ADRESSES] Répond au schéma : pour une plage donnée, connaître le NOMBRE D'ADRESSES du bloc.
     Convention de l'application : IPv4 = 2^(32-masque) adresses ; IPv6 = 2^(64-masque) réseaux /64. --}}
@extends('layouts.app')
@section('titre', "Détail du sous-bloc d'adresses")
@section('contenu')
    <header class="page">
        <div>
            <h1 class="mono">{{ $plage->reseau }}</h1>
            <div class="sous-titre">
                {{ $plage->description ?? 'Sans description' }}
                @if($plage->technologie) · {{ $plage->technologie->nom }} @endif
                @if($plage->client) · affectée à {{ $plage->client->nom }} @endif
                · <span class="badge accent">IPv{{ $plage->version_ip }}</span>
            </div>
        </div>
    </header>

    @php
        $nb       = $plage->nombreAdresses();               // Nombre d'adresses (IPv4) ou de /64 (IPv6)
        $masque   = $plage->longueurPrefixe();              // Longueur de préfixe du bloc
        // [IPv4] Adresses réellement affectables : on retire l'adresse réseau et le broadcast
        // pour les blocs plus larges qu'un /31 ; information complémentaire au décompte total.
        $utilisables = ((int) $plage->version_ip === 4 && $nb !== null && $nb > 2) ? $nb - 2 : null;
        // [ÉPUISEMENT] Consommation réelle : une adresse est prise dès qu'un équipement la porte.
        $affectables = $plage->adressesAffectables();
        $attribuees  = $plage->adressesAttribuees();
        $restantes   = $plage->adressesRestantes();
        $taux        = $plage->tauxRemplissage();
    @endphp

    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px,1fr)); gap:18px">
        {{-- ================= Taille du sous-bloc =================
             [LIBELLÉ] Titre neutre : l'unité dépend du protocole (adresses en IPv4,
             réseaux /64 en IPv6) et figure juste à côté de la valeur. --}}
        <div class="carte">
            <h2 style="font-size:1rem; margin-bottom:12px">Taille du sous-bloc</h2>
            <div style="font-size:2rem; font-weight:700; color:var(--accent)">
                {{ $nb !== null ? number_format($nb, 0, ',', ' ') : '—' }}
                <small style="font-size:.9rem; font-weight:400; color:var(--encre-2)">{{ $plage->uniteAdresses() }}</small>
            </div>
            @if($utilisables !== null)
                <div class="sous-titre" style="margin-top:8px">
                    Dont {{ number_format($utilisables, 0, ',', ' ') }} adresses affectables
                    (hors adresse réseau et broadcast).
                </div>
            @endif

            {{-- [ÉPUISEMENT] Combien reste-t-il avant que ce sous-bloc soit plein ?
                 Une adresse est comptée comme prise dès qu'un équipement la porte. --}}
            @if($restantes !== null)
                <div style="margin-top:14px; padding-top:14px; border-top:1px solid var(--bord)">
                    <div class="sous-titre" style="margin-bottom:8px">
                        Attribuées : <b style="color:var(--encre)">{{ number_format($attribuees, 0, ',', ' ') }}</b>
                        · Restantes : <b style="color:var(--ok)">{{ number_format($restantes, 0, ',', ' ') }}</b>
                        <small>/ {{ number_format($affectables, 0, ',', ' ') }}</small>
                    </div>
                    <div class="jauge">
                        <span class="{{ $taux >= 90 ? 'rouge' : ($taux >= 75 ? 'orange' : '') }}"
                              style="width: {{ min(100, max($taux, $attribuees > 0 ? 2 : 0)) }}%"></span>
                    </div>
                    <div class="sous-titre" style="margin-top:6px">
                        {{-- « < 1 % » plutôt que « 0 % » quand des adresses sont bel et bien prises --}}
                        Remplissage : {{ ($taux === 0 && $attribuees > 0) ? '< 1' : $taux }} %
                        @if($restantes === 0)
                            · <b style="color:var(--alerte)">sous-bloc épuisé</b>
                        @elseif($taux >= 90)
                            · <b style="color:var(--alerte)">bientôt épuisé</b>
                        @endif
                    </div>
                </div>
            @endif
            <div class="sous-titre" style="margin-top:8px">
                @if((int) $plage->version_ip === 6)
                    Règle IPv6 : un bloc /{{ $masque }} contient 2<sup>64−{{ $masque }}</sup> sous-réseaux /64.
                @else
                    Règle IPv4 : un bloc /{{ $masque }} contient 2<sup>32−{{ $masque }}</sup> adresses.
                @endif
            </div>
        </div>

        {{-- ================= Informations d'affectation ================= --}}
        <div class="carte">
            <h2 style="font-size:1rem; margin-bottom:12px">Affectation</h2>
            <table>
                <tbody>
                    <tr><td>Statut</td><td>
                        <span class="badge {{ $plage->statut === 'disponible' ? 'vert' : 'gris' }}">
                            {{ $plage->statut === 'disponible' ? 'Disponible' : 'Utilisée' }}
                        </span>
                    </td></tr>
                    <tr><td>Technologie</td><td>{{ $plage->technologie->nom ?? '—' }}</td></tr>
                    <tr><td>Client</td><td>{{ $plage->client->nom ?? '—' }}</td></tr>
                    <tr><td>Version</td><td>IPv{{ $plage->version_ip }}</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- [DESIGN] Pied d'action standard : le retour à gauche, l'action à droite. --}}
    <div class="barre-actions">
        <a class="btn secondaire" href="{{ route('plages-ip.index') }}">Retour à la liste</a>
        <a class="btn secondaire" href="{{ route('plages-ip.edit', $plage) }}">Modifier</a>
    </div>
@endsection
