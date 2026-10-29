{{-- Vue : liste des technologies d'accès. --}}
@extends('layouts.app')
@section('titre', 'Technologies')
@section('contenu')
    <header class="page">
        <div>
            <h1>Technologies d'accès</h1>
            <div class="sous-titre">Modes de raccordement des clients (fibre, FH, VSAT...).</div>
        </div>
        <a class="btn" href="{{ route('technologies.create') }}">Ajouter une technologie</a>
    </header>

    <table>
        <thead><tr><th>Nom</th><th>Équipements</th><th>Sous-blocs attribués</th><th>Actions</th></tr></thead>
        <tbody>
            @forelse($technologies as $technologie)
                <tr>
                    <td>{{ $technologie->nom }}</td>
                    <td>{{ $technologie->equipements_count }}</td>       {{-- Compteur withCount --}}
                    <td class="mono">
                        {{-- Plages IP attribuées à cette technologie, en CIDR complet --}}
                        {{ $technologie->plagesIp->pluck('reseau')->implode(', ') ?: '—' }}
                    </td>
                    <td>
                        <div class="actions">
                            {{-- Cas "Consulter les équipements par technologie" --}}
                            <a class="btn petit secondaire" href="{{ route('technologies.show', $technologie) }}">Détails</a>
                            <a class="btn petit secondaire" href="{{ route('technologies.edit', $technologie) }}">Modifier</a>
                            <form method="POST" action="{{ route('technologies.destroy', $technologie) }}"
                                  onsubmit="return confirm('Supprimer cette technologie ?')">
                                @csrf @method('DELETE')
                                <button class="btn petit danger" type="submit">Supprimer</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4">Aucune technologie enregistrée.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div style="margin-top:14px">{{ $technologies->links() }}</div>
@endsection
