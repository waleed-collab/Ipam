{{-- Vue : liste des sous-blocs d'adresses (cas "Consulter les blocs disponibles / utilisés"). --}}
@extends('layouts.app')
@section('titre', "Sous-blocs d'adresses")
@section('contenu')
    <header class="page">
        <div>
            <h1>Sous-blocs d'adresses</h1>
            {{-- [DESIGN] Filtres en pastilles : la pastille du filtre en cours est mise en évidence --}}
            <div class="filtres">
                <a href="{{ route('plages-ip.index') }}" class="{{ request('statut') ? '' : 'actif' }}">Toutes</a>
                <a href="{{ route('plages-ip.index', ['statut' => 'disponible']) }}" class="{{ request('statut') === 'disponible' ? 'actif' : '' }}">Disponibles</a>
                <a href="{{ route('plages-ip.index', ['statut' => 'utilisee']) }}" class="{{ request('statut') === 'utilisee' ? 'actif' : '' }}">Utilisées</a>
            </div>
        </div>
        <a class="btn" href="{{ route('plages-ip.create') }}">Ajouter un sous-bloc</a> {{-- Cas "Ajouter" --}}
    </header>

    {{-- [LIBELLÉ] Colonne « Taille du sous-bloc » : titre neutre car l'unité dépend du protocole
         (IPv4 = 2^(32-masque) adresses ; IPv6 = 2^(64-masque) réseaux /64). L'unité exacte
         est affichée à côté de chaque valeur. --}}
    <table>
        <thead><tr><th>Réseau</th><th>Description</th><th>Statut</th><th>Technologie</th><th>Client</th><th>Taille du sous-bloc</th><th>Restant avant épuisement</th><th>Actions</th></tr></thead>
        <tbody>
            @forelse($plages as $plage)
                <tr>
                    <td class="mono">{{ $plage->reseau }}</td>           {{-- CIDR en police mono --}}
                    <td>{{ $plage->description ?? '—' }}</td>
                    <td>
                        <span class="badge {{ $plage->statut === 'disponible' ? 'vert' : 'gris' }}">
                            {{ $plage->statut === 'disponible' ? 'Disponible' : 'Utilisée' }}
                        </span>
                    </td>
                    <td>{{ $plage->technologie->nom ?? '—' }}</td>
                    <td>{{ $plage->client->nom ?? '—' }}</td>
                    <td>
                        {{-- Nombre d'adresses (IPv4) ou de réseaux /64 (IPv6) du bloc --}}
                        @php($nb = $plage->nombreAdresses())
                        {{ $nb !== null ? number_format($nb, 0, ',', ' ') . ' ' . $plage->uniteAdresses() : '—' }}
                    </td>
                    {{-- [ÉPUISEMENT] Ce qu'il reste de libre dans ce sous-bloc, et son taux de remplissage --}}
                    <td>
                        @php($restantes = $plage->adressesRestantes())
                        @php($taux = $plage->tauxRemplissage())
                        @if($restantes === null)
                            —
                        @else
                            <b style="color:{{ $restantes === 0 ? 'var(--alerte)' : 'var(--ok)' }}">
                                {{ number_format($restantes, 0, ',', ' ') }}
                            </b>
                            <div class="sous-titre" style="margin-top:2px; font-size:.78rem">
                                {{ $restantes === 0 ? 'épuisé' : 'rempli à ' . (($taux === 0 && $plage->adressesAttribuees() > 0) ? '< 1' : $taux) . ' %' }}
                            </div>
                        @endif
                    </td>
                    <td>
                        <div class="actions">
                            {{-- Fiche : nombre d'adresses du bloc + affectation --}}
                            <a class="btn petit secondaire" href="{{ route('plages-ip.show', $plage) }}">Détails</a>
                            <a class="btn petit secondaire" href="{{ route('plages-ip.edit', $plage) }}">Modifier</a>
                            <form method="POST" action="{{ route('plages-ip.destroy', $plage) }}"
                                  onsubmit="return confirm('Supprimer ce bloc d\'adresses ?')">
                                @csrf @method('DELETE')
                                <button class="btn petit danger" type="submit">Supprimer</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7">Aucune plage IP pour ce filtre.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div style="margin-top:14px">{{ $plages->links() }}</div>
@endsection
