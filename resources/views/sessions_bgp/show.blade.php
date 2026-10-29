{{-- Vue : fiche d'une session BGP — préfixes reçus et annoncés
     (cas "Consulter les préfixes reçus et annoncés"). --}}
@extends('layouts.app')
@section('titre', 'Préfixes BGP')
@section('contenu')
    <header class="page">
        <div>
            <h1>Session BGP — {{ $session->operateur->nom }}</h1>
            <div class="sous-titre">
                Neighbor <span class="mono">{{ $session->ip_voisin }}</span> ·
                <span class="mono">AS{{ $session->asn_distant }}</span> ·
                Local <span class="mono">{{ $session->ip_locale ?? $session->interfaceLocale->adresse_ip ?? '—' }}</span> ·
                <span class="mono">AS{{ $configurationAs->asn_local }}</span>
                @if($session->interfaceLocale)
                    · via {{ $session->interfaceLocale->equipement->nom ?? '?' }} / <span class="mono">{{ $session->interfaceLocale->nom }}</span>
                @endif
                · <span class="badge {{ $session->etat === 'active' ? 'vert' : 'rouge' }}">{{ ucfirst($session->etat) }}</span>
            </div>
        </div>
    </header>

    {{-- Deux colonnes : préfixes reçus / préfixes annoncés --}}
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:18px">
        @foreach(['recu' => ['Préfixes reçus', $session->prefixesRecus],
                  'annonce' => ['Préfixes annoncés', $session->prefixesAnnonces]] as $type => [$titre, $liste])
            <div class="carte">
                <h2 style="font-size:1rem; margin-bottom:12px">{{ $titre }}</h2>
                <table>
                    <tbody>
                        @forelse($liste as $prefixe) {{-- Boucle sur les préfixes du bon type --}}
                            <tr>
                                <td class="mono">{{ $prefixe->prefixe }}</td>
                                <td style="text-align:right">
                                    {{-- Suppression d'un préfixe --}}
                                    <form method="POST"
                                          action="{{ route('sessions-bgp.prefixes.destroy', [$session, $prefixe]) }}"
                                          onsubmit="return confirm('Supprimer ce préfixe ?')">
                                        @csrf @method('DELETE')
                                        <button class="btn petit danger" type="submit">Retirer</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td>Aucun préfixe {{ $type === 'recu' ? 'reçu' : 'annoncé' }}.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                {{-- Formulaire d'ajout rapide d'un préfixe de ce type --}}
                <form method="POST" action="{{ route('sessions-bgp.prefixes.store', $session) }}"
                      style="display:flex; gap:8px; margin-top:12px">
                    @csrf
                    <input type="hidden" name="type" value="{{ $type }}">   {{-- Type fixé par la colonne --}}
                    <input class="mono" name="prefixe" placeholder="ex : 10.0.0.0/8" required
                           style="flex:1; padding:8px; border:1px solid var(--bord); border-radius:6px">
                    <button class="btn petit" type="submit">Ajouter</button>
                </form>
                @error('prefixe') <div class="erreur">{{ $message }}</div> @enderror
            </div>
        @endforeach
    </div>

    {{-- [DESIGN] Bouton retour en bas de page, aligné à droite (pied d'action standard). --}}
    <div class="barre-actions">
        <a class="btn secondaire" href="{{ route('sessions-bgp.index') }}">Retour à la liste</a>
    </div>
@endsection
