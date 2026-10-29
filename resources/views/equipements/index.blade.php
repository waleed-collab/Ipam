{{-- Vue : liste des équipements réseau. --}}
@extends('layouts.app')
@section('titre', 'Équipements')
@section('contenu')
    <header class="page">
        <div>
            <h1>Équipements</h1>
            <div class="sous-titre">Matériel réseau interne et installé chez les clients.</div>
        </div>
        <a class="btn" href="{{ route('equipements.create') }}">Ajouter un équipement</a>
    </header>

    <table>
        <thead><tr><th>Nom</th><th>Type</th><th>Rôle</th><th>IP</th><th>Technologie</th><th>Interfaces</th><th>Actions</th></tr></thead>
        <tbody>
            @forelse($equipements as $equipement)
                <tr>
                    <td>{{ $equipement->nom }}</td>
                    <td>{{ $equipement->type }}</td>
                    <td>
                        {{-- [BACKBONE] Badge visuel : le backbone est l'équipement local central --}}
                        <span class="badge {{ $equipement->role === 'backbone' ? 'accent' : 'gris' }}">
                            {{ $equipement->role === 'backbone' ? 'Backbone' : 'Accès' }}
                        </span>
                    </td>
                    <td class="mono">{{ $equipement->adresse_ip ?? '—' }}</td>
                    <td>{{ $equipement->technologie->nom ?? '—' }}</td>  {{-- Relation chargée via with() --}}
                    <td>{{ $equipement->role === 'backbone' ? $equipement->interfaces_count : '—' }}</td>
                    <td>
                        <div class="actions">
                            {{-- Fiche : interfaces côté international / côté accès (backbone) --}}
                            <a class="btn petit secondaire" href="{{ route('equipements.show', $equipement) }}">Détails</a>
                            <a class="btn petit secondaire" href="{{ route('equipements.edit', $equipement) }}">Modifier</a>
                            <form method="POST" action="{{ route('equipements.destroy', $equipement) }}"
                                  onsubmit="return confirm('Supprimer cet équipement ?')">
                                @csrf @method('DELETE')
                                <button class="btn petit danger" type="submit">Supprimer</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7">Aucun équipement enregistré.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div style="margin-top:14px">{{ $equipements->links() }}</div>
@endsection
