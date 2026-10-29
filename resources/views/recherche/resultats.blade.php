{{-- Vue : RÉSULTATS DE LA RECHERCHE GLOBALE (Admin / Manager).
     Affiche une FICHE COMPLÈTE selon le type détecté : IP/CIDR (IPv4-IPv6), ASN ou texte libre. --}}
@extends('layouts.app')
@section('titre', 'Recherche globale')
@section('contenu')
    <header class="page">
        <div>
            <h1>Recherche globale</h1>
            <div class="sous-titre">Saisissez une adresse IPv4, IPv6, un bloc CIDR, un ASN ou un nom (client, opérateur, technologie).</div>
        </div>
    </header>

    {{-- [RECHERCHE] Formulaire dédié : la page reste utilisable en arrivant par "Recherche avancée" --}}
    <form method="GET" action="{{ route('recherche') }}" class="carte"
          style="margin-bottom:18px; display:flex; gap:10px; flex-wrap:wrap">
        <input type="search" name="q" value="{{ $q }}" autofocus
               placeholder="IP, CIDR (IPv4/IPv6), ASN, client, opérateur, technologie…"
               aria-label="Terme de recherche"
               style="flex:1; min-width:220px; padding:10px 14px; border:1px solid var(--bord);
                      border-radius:8px; background:var(--champ-fond); color:var(--encre); font:inherit">
        <button class="btn" type="submit">Rechercher</button>
    </form>

    {{-- Rappel du terme et du type détecté --}}
    @if($q !== '')
        <div class="carte" style="margin-bottom:18px; display:flex; align-items:center; gap:10px; flex-wrap:wrap">
            <span class="sous-titre">Résultats pour</span> <b class="mono">{{ $q }}</b>
            @if($type === 'ip4')   <span class="badge accent">Adresse / bloc IPv4</span>
            @elseif($type === 'ip6') <span class="badge accent">Adresse / bloc IPv6</span>
            @elseif($type === 'asn') <span class="badge accent">Numéro d'AS (ASN)</span>
            @elseif($type === 'texte') <span class="badge gris">Texte libre</span>
            @endif
        </div>
    @endif

    {{-- ======== Résultats IP / CIDR : plages parentes avec fiche complète ======== --}}
    @if(in_array($type, ['ip4', 'ip6']))
        @forelse($plages as $plage)
            <div class="carte" style="margin-bottom:16px">
                {{-- Informations générales de la plage parente --}}
                <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap; margin-bottom:10px">
                    <h2 class="mono" style="font-size:1.05rem">{{ $plage->reseau }}</h2>
                    <span class="badge accent">IPv{{ $plage->version_ip }}</span>
                    <span class="badge {{ $plage->statut === 'disponible' ? 'vert' : 'gris' }}">
                        {{ $plage->statut === 'disponible' ? 'Disponible' : 'Utilisée' }}
                    </span>
                    @if($plage->technologie) <span class="badge gris">{{ $plage->technologie->nom }}</span> @endif
                </div>
                @if($plage->description) <div class="sous-titre" style="margin-bottom:10px">{{ $plage->description }}</div> @endif

                @if($plage->client)
                    {{-- Fiche du client rattaché --}}
                    <h3 style="font-size:.85rem; text-transform:uppercase; letter-spacing:.08em; color:var(--encre-2); margin:12px 0 6px">Client</h3>
                    <div><b>{{ $plage->client->nom }}</b>
                        <span class="sous-titre"> — {{ $plage->client->email ?? 'email non renseigné' }}
                        @if($plage->client->telephone) · {{ $plage->client->telephone }} @endif</span>
                    </div>

                    {{-- Profil de bande passante du client (allocations) --}}
                    <h3 style="font-size:.85rem; text-transform:uppercase; letter-spacing:.08em; color:var(--encre-2); margin:14px 0 6px">Bande passante</h3>
                    @forelse($plage->client->allocations as $allocation)
                        <div class="sous-titre" style="padding:4px 0">
                            <b style="color:var(--encre)">{{ $allocation->bande_passante_souscrite }} Mbps</b>
                            sur {{ $allocation->technologie->nom }}
                            @if($allocation->description) — {{ $allocation->description }} @endif
                        </div>
                    @empty
                        <div class="sous-titre">Aucune allocation de bande passante.</div>
                    @endforelse
                @else
                    <div class="sous-titre">Ce sous-bloc n'est affecté à aucun client (disponible).</div>
                @endif
            </div>
        @empty
            <div class="carte">Aucun sous-bloc d'adresses ne contient <b class="mono">{{ $q }}</b>.</div>
        @endforelse
    @endif

    {{-- ======== Résultats ASN : opérateur, sessions, préfixes, clients associés ======== --}}
    @if($type === 'asn' || $sessions->isNotEmpty())
        @foreach($sessions as $session)
            <div class="carte" style="margin-bottom:16px">
                <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap; margin-bottom:10px">
                    <h2 style="font-size:1.05rem">Session BGP — {{ $session->operateur->nom }}</h2>
                    <span class="badge accent">AS{{ $session->asn_distant }}</span>
                    <span class="badge {{ $session->etat === 'active' ? 'vert' : 'rouge' }}">{{ ucfirst($session->etat) }}</span>
                </div>
                <div class="sous-titre">Voisin : <span class="mono">{{ $session->ip_voisin }}</span>
                    @if($session->capacite_totale) · Capacité : {{ $session->capacite_totale }} Mbps @endif
                </div>

                {{-- Préfixes de la session --}}
                @if($session->prefixes->isNotEmpty())
                    <h3 style="font-size:.85rem; text-transform:uppercase; letter-spacing:.08em; color:var(--encre-2); margin:12px 0 6px">Préfixes ({{ $session->prefixes->count() }})</h3>
                    @foreach($session->prefixes as $prefixe)
                        <span class="badge {{ $prefixe->type === 'recu' ? 'gris' : 'accent' }}" style="margin:2px 4px 2px 0">
                            {{ $prefixe->prefixe }} · {{ $prefixe->type === 'recu' ? 'reçu' : 'annoncé' }}
                        </span>
                    @endforeach
                @endif
            </div>
        @endforeach
        @if($type === 'asn' && $sessions->isEmpty() && $operateurs->isEmpty())
            <div class="carte">Aucune session BGP ni opérateur ne correspond à l'AS <b>{{ $q }}</b>.</div>
        @endif
    @endif

    {{-- ======== Opérateurs trouvés (par ASN ou par nom) ======== --}}
    @foreach($operateurs as $operateur)
        <div class="carte" style="margin-bottom:16px">
            <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap; margin-bottom:8px">
                <h2 style="font-size:1.05rem">Opérateur — {{ $operateur->nom }}</h2>
                <span class="badge accent">AS{{ $operateur->asn }}</span>
            </div>
            <div class="sous-titre">Contact : {{ $operateur->contact ?? '—' }} · {{ $operateur->email_contact ?? '—' }}</div>
            <div class="sous-titre" style="margin-top:6px">Sessions BGP : {{ $operateur->sessionsBgp->count() }}</div>
        </div>
    @endforeach

    {{-- ======== Résultats texte libre : clients et technologies ======== --}}
    @if($type === 'texte')
        @foreach($clients as $client)
            <div class="carte" style="margin-bottom:16px">
                <h2 style="font-size:1.05rem; margin-bottom:6px">Client — {{ $client->nom }}</h2>
                <div class="sous-titre">{{ $client->email ?? '—' }} @if($client->telephone) · {{ $client->telephone }} @endif</div>
                <div class="sous-titre" style="margin-top:8px">
                    Sous-blocs : @forelse($client->plagesIp as $p)<span class="mono">{{ $p->reseau }}</span>@if(!$loop->last), @endif @empty aucun @endforelse
                    · Équipements : {{ $client->equipements->count() }}
                    · Bande passante : {{ $client->allocations->sum('bande_passante_souscrite') }} Mbps
                </div>
                <a class="btn petit secondaire" style="margin-top:10px" href="{{ route('clients.show', $client) }}">Voir la fiche complète</a>
            </div>
        @endforeach
        @foreach($technologies as $technologie)
            <div class="carte" style="margin-bottom:16px">
                <h2 style="font-size:1.05rem; margin-bottom:6px">Technologie — {{ $technologie->nom }}</h2>
                <div class="sous-titre">
                    {{ $technologie->plages_ip_count }} bloc(s) d'adresses · {{ $technologie->equipements_count }} équipement(s)
                    @if($technologie->debit_max_supporte) · limite {{ $technologie->debit_max_supporte }} Mbps/client @endif
                </div>
            </div>
        @endforeach
        @if($clients->isEmpty() && $operateurs->isEmpty() && $technologies->isEmpty())
            <div class="carte">Aucun résultat pour « {{ $q }} ».</div>
        @endif
    @endif

    {{-- [DESIGN] Bouton retour en bas de page, aligné à droite (pied d'action standard). --}}
    <div class="barre-actions">
        <a class="btn secondaire" href="{{ route('tableau-de-bord') }}"
           onclick="if (history.length > 1) { history.back(); return false; }">← Retour</a>
    </div>
@endsection
