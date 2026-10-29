{{-- Vue : TABLEAU DE BORD professionnel (personnel uniquement).
     [DASHBOARD] Cartes statistiques, graphiques interactifs (Chart.js), indicateurs réseau,
     alertes, activité récente, répartition IPv4/IPv6, capacité et bande passante. --}}
@extends('layouts.app')
@section('titre', 'Tableau de bord')

@push('tete')
    {{-- Bibliothèque de graphiques interactifs (CDN) --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
@endpush

@section('contenu')
    <header class="page">
        <div>
            <h1>Tableau de bord</h1>
            <div class="sous-titre">Vue d'ensemble du réseau, des capacités et des ressources IPAM.</div>
        </div>
        <a class="btn" href="{{ route('recherche') }}">Recherche avancée</a>
    </header>

    {{-- ================= CARTES STATISTIQUES ================= ---
         [DESIGN] Icônes reprises à l'identique de la sidebar : même langage visuel dans toute l'appli. --}}
    <div class="stats" style="margin-bottom:18px">
        <div class="stat">
            <div class="stat-icone">
                <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="6" rx="1.5"/><rect x="3" y="14" width="18" height="6" rx="1.5"/><circle cx="7" cy="7" r=".6" fill="currentColor"/><circle cx="7" cy="17" r=".6" fill="currentColor"/></svg>
            </div>
            <div class="stat-corps"><div class="valeur">{{ $nbOperateurs }}</div><div class="libelle">Opérateurs externes</div></div>
        </div>
        <div class="stat">
            <div class="stat-icone">
                <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12h4l2-7 4 14 2-7h8"/></svg>
            </div>
            <div class="stat-corps">
                <div class="valeur">{{ $nbSessionsActives }}<span style="font-size:.95rem;color:var(--encre-2)"> / {{ $nbSessionsTotal }}</span></div>
                <div class="libelle">Sessions BGP actives</div>
            </div>
        </div>
        <div class="stat">
            <div class="stat-icone">
                <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5a11 11 0 0 1 14 0"/><path d="M8.3 15.9a6.5 6.5 0 0 1 7.4 0"/><path d="M11.6 19.2a2 2 0 0 1 .8-.2 2 2 0 0 1 .8.2"/><circle cx="12" cy="19.5" r=".6" fill="currentColor"/></svg>
            </div>
            <div class="stat-corps"><div class="valeur">{{ $nbTechnologies }}</div><div class="libelle">Technologies d'accès</div></div>
        </div>
        <div class="stat">
            <div class="stat-icone">
                <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="5" width="14" height="14" rx="2"/><rect x="9" y="9" width="6" height="6" rx="1"/><path d="M9 2v3M15 2v3M9 19v3M15 19v3M2 9h3M2 15h3M19 9h3M19 15h3"/></svg>
            </div>
            <div class="stat-corps"><div class="valeur">{{ $nbEquipements }}</div><div class="libelle">Équipements</div></div>
        </div>
        <div class="stat">
            <div class="stat-icone">
                <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"/><circle cx="10" cy="7" r="4"/><path d="M22.5 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <div class="stat-corps"><div class="valeur">{{ $nbClients }}</div><div class="libelle">Clients</div></div>
        </div>
        <div class="stat">
            <div class="stat-icone">
                <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="4" rx="1"/><rect x="3" y="10" width="18" height="4" rx="1"/><rect x="3" y="16" width="18" height="4" rx="1"/><path d="M7 6h.01M7 12h.01M7 18h.01"/></svg>
            </div>
            <div class="stat-corps"><div class="valeur">{{ $nbPlagesLibres }}</div><div class="libelle">Sous-blocs d'adresses disponibles</div></div>
        </div>
    </div>

    {{-- ================= RANGÉE 1 : identité du réseau + bande passante =================
         [MISE EN PAGE] Deux cartes de contenu comparable, donc de hauteur proche : elles
         occupent chacune la moitié de la largeur, sans espace perdu. Les cartes IPv4/IPv6,
         nettement plus hautes, ont leur propre rangée juste en dessous. --}}
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(320px,1fr)); gap:14px; margin-bottom:14px">
        {{-- Widget [PARAMÈTRES AS] : identité du réseau local, en lecture seule.
             Même icône que le lien de menu "Ressources Internet" + badge cliquable du même nom :
             le lien avec la page de réglages est visible sans ambiguïté. --}}
        <div class="carte">
            <h2>
                <span class="icone"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .34 1.87l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.7 1.7 0 0 0-1.87-.34 1.7 1.7 0 0 0-1.04 1.56V21a2 2 0 1 1-4 0v-.09A1.7 1.7 0 0 0 9 19.35a1.7 1.7 0 0 0-1.87.34l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.7 1.7 0 0 0 4.65 15a1.7 1.7 0 0 0-1.56-1.04H3a2 2 0 1 1 0-4h.09A1.7 1.7 0 0 0 4.65 9a1.7 1.7 0 0 0-.34-1.87l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.7 1.7 0 0 0 9 4.65a1.7 1.7 0 0 0 1.04-1.56V3a2 2 0 1 1 4 0v.09A1.7 1.7 0 0 0 15 4.65a1.7 1.7 0 0 0 1.87-.34l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.7 1.7 0 0 0 19.35 9a1.7 1.7 0 0 0 1.56 1.04H21a2 2 0 1 1 0 4h-.09A1.7 1.7 0 0 0 19.4 15Z"/></svg></span>
                Système autonome local
            </h2>
            <a href="{{ route('configuration-as.edit') }}" class="badge accent" style="text-decoration:none; display:inline-block; margin-bottom:10px">Ressources Internet</a>
            <div class="sous-titre">ASN local : <b style="color:var(--encre)">{{ $configurationAs->asn_local }}</b></div>
            <div class="sous-titre">Réseau : <b style="color:var(--encre)">{{ $configurationAs->nom_reseau }}</b></div>
            <div class="sous-titre">Router-ID : <b style="color:var(--encre)">{{ $configurationAs->router_id ?? '—' }}</b></div>
            <a href="{{ route('configuration-as.edit') }}" class="btn petit secondaire" style="margin-top:10px; display:inline-block">Modifier</a>
        </div>

        {{-- Widget 1 : bande passante (souscrite vs capacité physique totale) --}}
        <div class="carte">
            <h2><span class="icone"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21a9 9 0 1 1 9-9"/><path d="M12 12 17 7"/><path d="M12 3v2M3 12h2M21 12h-2"/></svg></span>Bande passante</h2>
            <div style="font-size:1.5rem; font-weight:700; color:var(--accent)">
                {{ number_format($bandeSouscrite, 0, ',', ' ') }} <small style="font-size:.8rem">Mbps souscrits</small>
            </div>
            <div class="sous-titre" style="margin:6px 0 8px">
                Capacité totale : {{ number_format($capaciteTotale, 0, ',', ' ') }} Mbps —
                restante : {{ number_format($capaciteRestante, 0, ',', ' ') }} Mbps
            </div>
            {{-- Jauge animée : couleur selon le niveau d'occupation --}}
            <div class="jauge"><span class="{{ $tauxBandePassante >= 90 ? 'rouge' : ($tauxBandePassante >= 75 ? 'orange' : '') }}"
                 style="width: {{ min(100, $tauxBandePassante) }}%"></span></div>
            <div class="sous-titre" style="margin-top:6px">Taux d'occupation : {{ $tauxBandePassante }} %</div>
        </div>
    </div>

    {{-- ================= RANGÉE 2 : occupation de l'adressage ================= --}}
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(340px,1fr)); gap:14px; margin-bottom:18px">
        {{-- ================= Widgets IPv4 et IPv6 =================
             Chaque carte part de la PLAGE PRINCIPALE reçue du RIR (saisie dans « Ressources
             Internet ») et en déduit l'occupation réelle :
                capacité globale  →  déjà découpé en sous-blocs  →  restant disponible.
             Exemple IPv4 : 102.64.0.0/19 = 8 192 adresses, 608 utilisées, 7 584 restantes.
             Le widget « Occupation des sous-blocs » a été retiré : cette information, plus
             précise, le remplace avantageusement.
             ================================================================ --}}
        @foreach([['IPv4', $ipv4], ['IPv6', $ipv6]] as [$libelleVersion, $stats])
            <div class="carte">
                <h2 style="font-size:.95rem; margin-bottom:10px">
                    <span class="badge accent">{{ $libelleVersion }}</span>
                    {{-- La plage principale est l'information de tête : on sait tout de suite de quoi on parle --}}
                    @if($stats['prefixe'])
                        <span class="mono" style="font-size:.9rem; color:var(--encre)">{{ $stats['prefixe'] }}</span>
                    @endif
                </h2>

                @if($stats['capacite'] !== null)
                    {{-- Capacité globale de la plage principale --}}
                    <div style="font-size:1.4rem; font-weight:700; color:var(--accent); line-height:1.2">
                        {{ number_format($stats['capacite'], 0, ',', ' ') }}
                        <small style="font-size:.72rem; font-weight:400; color:var(--encre-2)">{{ $stats['unite'] }} au total</small>
                    </div>

                    {{-- Répartition utilisé / restant --}}
                    <div class="sous-titre" style="margin:8px 0 6px">
                        Utilisées : <b style="color:var(--encre)">{{ number_format($stats['utilisees'], 0, ',', ' ') }}</b>
                        · Restantes : <b style="color:var(--ok)">{{ number_format($stats['restantes'], 0, ',', ' ') }}</b>
                    </div>

                    {{-- Jauge d'occupation de la plage principale --}}
                    <div class="jauge">
                        <span class="{{ $stats['taux'] >= 90 ? 'rouge' : ($stats['taux'] >= 75 ? 'orange' : '') }}"
                              style="width: {{ min(100, $stats['taux']) }}%"></span>
                    </div>
                    <div class="sous-titre" style="margin-top:6px">
                        {{-- « < 1 % » plutôt que « 0 % » : en IPv6 l'occupation est réelle mais
                             infime au regard de la capacité — afficher 0 serait trompeur. --}}
                        Taux d'occupation :
                        {{ ($stats['taux'] === 0 && $stats['utilisees'] > 0) ? '< 1' : $stats['taux'] }} % ·
                        {{ $stats['sousBlocs'] }} sous-bloc(s) découpé(s), dont {{ $stats['affectes'] }} affecté(s)
                    </div>
                @else
                    {{-- Tant qu'aucune plage principale n'est renseignée, on guide l'utilisateur --}}
                    <div class="sous-titre" style="margin-bottom:8px">
                        Aucune plage principale {{ $libelleVersion }} renseignée : la capacité ne peut pas être calculée.
                    </div>
                    <a class="btn petit secondaire" href="{{ route('configuration-as.edit') }}">Renseigner la plage</a>
                    <div class="sous-titre" style="margin-top:8px">
                        {{ $stats['sousBlocs'] }} sous-bloc(s) déclaré(s), dont {{ $stats['affectes'] }} affecté(s)
                    </div>
                @endif

                {{-- [LIBELLÉ] « des clients concernés » : total souscrit par les clients possédant des
                     sous-blocs de cette version. Un client ayant IPv4 ET IPv6 est compté dans les deux
                     cartes — ces montants ne s'additionnent donc pas. --}}
                <div class="sous-titre" style="margin-top:8px"
                     title="Total souscrit par les clients possédant des sous-blocs {{ $libelleVersion }}. Un client ayant les deux versions est compté dans les deux cartes.">
                    BP des clients concernés : <b style="color:var(--encre)">{{ number_format($stats['bande'], 0, ',', ' ') }}</b> Mbps
                </div>
            </div>
        @endforeach
    </div>

    {{-- [LIBELLÉ] Rappel explicite : les deux montants ci-dessus se recoupent. --}}
    <div class="sous-titre" style="margin:-8px 0 18px">
        Un client disposant à la fois d'IPv4 et d'IPv6 est compté dans les deux cartes : ces montants ne s'additionnent pas.
    </div>

    {{-- ================= ÉPUISEMENT DES SOUS-BLOCS =================
         Combien reste-t-il de libre dans chaque sous-bloc avant saturation ? Une adresse est
         comptée comme prise dès qu'un équipement la porte. Les sous-blocs les plus remplis
         apparaissent en tête : ce sont ceux qui demanderont bientôt une action. --}}
    <div class="carte" style="margin-bottom:18px">
        <h2><span class="icone"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 20h18"/><rect x="5" y="12" width="4" height="8" rx="1"/><rect x="10" y="8" width="4" height="12" rx="1"/><rect x="15" y="4" width="4" height="16" rx="1"/></svg></span>Restant avant épuisement des sous-blocs</h2>
        <table>
            <thead>
                <tr><th>Sous-bloc</th><th>Client</th><th>Attribuées</th><th>Restantes</th><th>Remplissage</th></tr>
            </thead>
            <tbody>
                @forelse($sousBlocs as $sousBloc)
                    @php
                        $attribuees  = $sousBloc->adressesAttribuees();
                        $restantes   = $sousBloc->adressesRestantes();
                        $affectables = $sousBloc->adressesAffectables();
                        $taux        = $sousBloc->tauxRemplissage();
                    @endphp
                    <tr>
                        <td class="mono">
                            <a href="{{ route('plages-ip.show', $sousBloc) }}">{{ $sousBloc->reseau }}</a>
                        </td>
                        <td>{{ $sousBloc->client->nom ?? '—' }}</td>
                        <td>{{ number_format($attribuees, 0, ',', ' ') }}</td>
                        <td>
                            <b style="color:{{ $restantes === 0 ? 'var(--alerte)' : 'var(--ok)' }}">
                                {{ number_format($restantes, 0, ',', ' ') }}
                            </b>
                            <span class="sous-titre">/ {{ number_format($affectables, 0, ',', ' ') }} {{ $sousBloc->uniteAdresses() }}</span>
                        </td>
                        <td style="min-width:150px">
                            {{-- Barre minimale de 2 % dès qu'une adresse est prise : sinon un taux
                                 arrondi à 0 donnerait une jauge vide, laissant croire à un bloc intact. --}}
                            <div class="jauge">
                                <span class="{{ $taux >= 90 ? 'rouge' : ($taux >= 75 ? 'orange' : '') }}"
                                      style="width: {{ min(100, max($taux, $attribuees > 0 ? 2 : 0)) }}%"></span>
                            </div>
                            <div class="sous-titre" style="margin-top:4px; font-size:.78rem">
                                @if($restantes === 0)
                                    <b style="color:var(--alerte)">épuisé</b>
                                @else
                                    {{ ($taux === 0 && $attribuees > 0) ? '< 1' : $taux }} %
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5">Aucun sous-bloc d'adresses enregistré.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ================= GRAPHIQUES INTERACTIFS ================= --}}
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px,1fr)); gap:14px; margin-bottom:18px">
        <div class="carte">
            <h2><span class="icone"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="4" rx="1"/><rect x="3" y="10" width="18" height="4" rx="1"/><rect x="3" y="16" width="18" height="4" rx="1"/><path d="M7 6h.01M7 12h.01M7 18h.01"/></svg></span>Plage principale IPv4 : utilisé / restant</h2>
            <canvas id="graphePlages" height="210"></canvas>
        </div>
        <div class="carte">
            <h2><span class="icone"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="12" r="6.5"/><circle cx="15" cy="12" r="6.5"/></svg></span>Répartition IPv4 / IPv6</h2>
            <canvas id="grapheVersions" height="210"></canvas>
        </div>
        <div class="carte">
            <h2><span class="icone"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5a11 11 0 0 1 14 0"/><path d="M8.3 15.9a6.5 6.5 0 0 1 7.4 0"/><path d="M11.6 19.2a2 2 0 0 1 .8-.2 2 2 0 0 1 .8.2"/><circle cx="12" cy="19.5" r=".6" fill="currentColor"/></svg></span>Bande passante par technologie (Mbps)</h2>
            <canvas id="grapheTechnos" height="210"></canvas>
        </div>
    </div>

    {{-- ================= ALERTES + ACTIVITÉ RÉCENTE ================= --}}
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(300px,1fr)); gap:14px">
        {{-- Alertes réseau --}}
        @php
            // Libellés lisibles par niveau. Le niveau le plus grave présent colore le compteur du titre.
            $libelles  = ['rouge' => 'Critique', 'orange' => 'Attention', 'gris' => 'Info'];
            $niveaux   = collect($alertes)->pluck('niveau');
            $plusGrave = $niveaux->contains('rouge') ? 'rouge' : ($niveaux->contains('orange') ? 'orange' : 'gris');
        @endphp
        <div class="carte">
            <h2>
                <span class="icone"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"/><path d="M12 9v4M12 17h.01"/></svg></span>
                Alertes réseau
                @if(count($alertes))
                    <span class="compteur-alertes {{ $plusGrave }}">{{ count($alertes) }}</span>
                @endif
            </h2>

            <div class="liste-alertes">
                @forelse($alertes as $alerte)
                    <div class="alerte {{ $alerte['niveau'] }}">
                        <span class="alerte-icone">
                            @if($alerte['niveau'] === 'rouge')
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                            @elseif($alerte['niveau'] === 'orange')
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            @else
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                            @endif
                        </span>
                        <div class="alerte-corps">
                            <span class="alerte-niveau">{{ $libelles[$alerte['niveau']] ?? 'Info' }}</span>
                            <p class="alerte-texte">{{ $alerte['texte'] }}</p>
                        </div>
                    </div>
                @empty
                    <div class="alerte-vide">
                        <span class="alerte-vide-icone"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg></span>
                        <div>
                            <div class="alerte-vide-titre">Aucune alerte</div>
                            <div class="sous-titre" style="margin-top:2px">Tous les indicateurs réseau sont au vert.</div>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Activité récente --}}
        <div class="carte">
            <h2><span class="icone"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3.5 2"/></svg></span>Activité récente</h2>
            @forelse($activite as $evenement)
                <div class="ligne-liste" style="justify-content:space-between; font-size:.88rem">
                    <span><span class="badge gris" style="margin-right:6px">{{ $evenement['type'] }}</span>{{ $evenement['texte'] }}</span>
                    {{-- Date relative ("il y a 2 jours") fournie par Carbon --}}
                    <span class="sous-titre" style="white-space:nowrap">{{ $evenement['quand']?->diffForHumans() }}</span>
                </div>
            @empty
                <div class="sous-titre">Aucune activité enregistrée pour le moment.</div>
            @endforelse
        </div>
    </div>

    {{-- ================= SCRIPTS DES GRAPHIQUES ================= --}}
    <script>
        // Couleurs lues depuis les variables CSS => les graphiques suivent le thème clair/sombre
        const style   = getComputedStyle(document.documentElement);
        const accent  = style.getPropertyValue('--accent').trim();
        const vert    = style.getPropertyValue('--ok').trim();
        const gris    = style.getPropertyValue('--encre-2').trim();
        Chart.defaults.color = gris;                              // Couleur des textes des graphiques
        Chart.defaults.font.family = 'system-ui, "Segoe UI", Roboto, sans-serif';

        // Graphique 1 : occupation RÉELLE de la plage principale IPv4, en adresses.
        // Il reprend exactement les chiffres de la carte IPv4 (ex : 608 utilisées / 416 restantes
        // sur 1 024) au lieu d'un simple décompte de sous-blocs, qui ne disait rien de la capacité.
        new Chart(document.getElementById('graphePlages'), {
            type: 'doughnut',                                     // Anneau interactif (survol = détail)
            data: {
                labels: ['Utilisées', 'Restantes'],
                datasets: [{ data: [{{ (int) ($ipv4['utilisees'] ?? 0) }}, {{ (int) ($ipv4['restantes'] ?? 0) }}],
                             backgroundColor: [accent, vert], borderWidth: 0 }],
            },
            options: {
                plugins: {
                    legend: { position: 'bottom' },
                    // Infobulle explicite : valeur + unité, sinon on ne sait pas ce qui est compté
                    tooltip: { callbacks: { label: (c) => ' ' + c.label + ' : ' +
                               c.parsed.toLocaleString('fr-FR') + ' adresses' } },
                },
                cutout: '62%',
            },
        });

        // Graphique 2 : barres IPv4 vs IPv6 (nombre de sous-blocs d'adresses)
        new Chart(document.getElementById('grapheVersions'), {
            type: 'bar',
            data: {
                labels: ['IPv4', 'IPv6'],
                datasets: [
                    { label: 'Sous-blocs totaux',   data: [{{ $ipv4['sousBlocs'] }}, {{ $ipv6['sousBlocs'] }}], backgroundColor: accent },
                    { label: 'Sous-blocs affectés', data: [{{ $ipv4['affectes'] }}, {{ $ipv6['affectes'] }}], backgroundColor: vert },
                ],
            },
            options: { scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } },
        });

        // Graphique 3 : bande passante souscrite par technologie
        new Chart(document.getElementById('grapheTechnos'), {
            type: 'bar',
            data: {
                labels: @json($bpParTechnologie->keys()),         // Noms des technologies
                datasets: [{ label: 'Mbps souscrits',
                             data: @json($bpParTechnologie->values()), backgroundColor: accent }],
            },
            options: { indexAxis: 'y',                            // Barres horizontales, plus lisibles
                       scales: { x: { beginAtZero: true } } },
        });
    </script>
@endsection
