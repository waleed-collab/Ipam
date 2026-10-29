{{-- Vue : liste des sessions BGP (cas "Visualiser les sessions BGP actives"). --}}
@extends('layouts.app')
@section('titre', 'Sessions BGP')
@section('contenu')
    <header class="page">
        <div>
            <h1>Sessions BGP</h1>
            {{-- [DESIGN] Filtres en pastilles : la pastille du filtre en cours est mise en évidence --}}
            <div class="filtres">
                <a href="{{ route('sessions-bgp.index') }}" class="{{ request('etat') ? '' : 'actif' }}">Toutes</a>
                <a href="{{ route('sessions-bgp.index', ['etat' => 'active']) }}" class="{{ request('etat') === 'active' ? 'actif' : '' }}">Actives</a>
                <a href="{{ route('sessions-bgp.index', ['etat' => 'inactive']) }}" class="{{ request('etat') === 'inactive' ? 'actif' : '' }}">Inactives</a>
            </div>
        </div>
        <a class="btn" href="{{ route('sessions-bgp.create') }}">Ajouter une session</a> {{-- Cas "Ajouter" --}}
    </header>

    {{-- Colonnes du schéma : neighbor, adresse neighbor, ASN, adresse locale, ASN local, statut --}}
    <table>
        <thead>
            <tr><th>Neighbor</th><th>Adresse neighbor</th><th>ASN</th><th>Adresse locale</th><th>ASN local</th><th>Statut</th><th>Actions</th></tr>
        </thead>
        <tbody>
            @forelse($sessions as $session)
                <tr>
                    <td>{{ $session->operateur->nom }}</td>              {{-- Relation chargée via with() --}}
                    <td class="mono">{{ $session->ip_voisin }}</td>
                    <td class="mono">AS{{ $session->asn_distant }}</td>
                    {{-- Adresse locale : saisie sur la session, ou héritée de l'interface backbone --}}
                    <td class="mono">{{ $session->ip_locale ?? $session->interfaceLocale->adresse_ip ?? '—' }}</td>
                    <td class="mono">AS{{ $configurationAs->asn_local }}</td>  {{-- Paramètre global de l'AS --}}
                    <td>
                        {{-- Badge coloré selon le statut de la session --}}
                        <span class="badge {{ $session->etat === 'active' ? 'vert' : 'rouge' }}">
                            {{ $session->etat === 'active' ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td>
                        <div class="actions">
                            {{-- Fiche : consultation des préfixes reçus/annoncés --}}
                            <a class="btn petit secondaire" href="{{ route('sessions-bgp.show', $session) }}">Préfixes</a>
                            <a class="btn petit secondaire" href="{{ route('sessions-bgp.edit', $session) }}">Modifier</a>
                            <form method="POST" action="{{ route('sessions-bgp.destroy', $session) }}"
                                  onsubmit="return confirm('Supprimer cette session BGP ?')">
                                @csrf @method('DELETE')
                                <button class="btn petit danger" type="submit">Supprimer</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7">Aucune session BGP pour ce filtre.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div style="margin-top:14px">{{ $sessions->links() }}</div>
@endsection
