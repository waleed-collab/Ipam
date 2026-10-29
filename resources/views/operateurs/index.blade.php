{{-- Vue : liste des opérateurs externes (cas "Consulter la liste des opérateurs"). --}}
@extends('layouts.app')
@section('titre', 'Opérateurs')
@section('contenu')
    <header class="page">
        <div>
            <h1>Opérateurs externes</h1>
            <div class="sous-titre">Fournisseurs et pairs avec lesquels des sessions BGP sont établies.</div>
        </div>
        <a class="btn" href="{{ route('operateurs.create') }}">Ajouter un opérateur</a> {{-- Cas "Ajouter" --}}
    </header>

    <table>
        <thead>
            <tr><th>Nom</th><th>ASN</th><th>Contact</th><th>Sessions BGP</th><th>Actions</th></tr>
        </thead>
        <tbody>
            @forelse($operateurs as $operateur) {{-- Boucle sur la page courante de la pagination --}}
                <tr>
                    <td>{{ $operateur->nom }}</td>
                    <td class="mono">AS{{ $operateur->asn }}</td>            {{-- ASN en police mono --}}
                    <td>{{ $operateur->contact ?? '—' }}</td>
                    <td>{{ $operateur->sessions_bgp_count }}</td>            {{-- Compteur withCount --}}
                    <td>
                        <div class="actions">
                            <a class="btn petit secondaire" href="{{ route('operateurs.edit', $operateur) }}">Modifier</a>
                            {{-- Suppression : formulaire DELETE avec confirmation JavaScript --}}
                            <form method="POST" action="{{ route('operateurs.destroy', $operateur) }}"
                                  onsubmit="return confirm('Supprimer cet opérateur et ses sessions BGP ?')">
                                @csrf @method('DELETE')      {{-- Simule le verbe HTTP DELETE --}}
                                <button class="btn petit danger" type="submit">Supprimer</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty {{-- Affiché si la table est vide --}}
                <tr><td colspan="5">Aucun opérateur enregistré. Ajoutez le premier avec le bouton ci-dessus.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div style="margin-top:14px">{{ $operateurs->links() }}</div> {{-- Liens de pagination --}}
@endsection
