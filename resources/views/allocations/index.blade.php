{{-- Vue : liste des ALLOCATIONS DE BANDE PASSANTE + jauges d'occupation des sessions BGP. --}}
@extends('layouts.app')
@section('titre', 'Bande passante')
@section('contenu')
    <header class="page">
        <div>
            <h1>Gestion de la bande passante</h1>
            <div class="sous-titre">Allocations de débit par client, dans la limite technique de chaque technologie.</div>
        </div>
        <a class="btn" href="{{ route('allocations.create') }}">+ Nouvelle allocation</a>
    </header>

    <table>
        <thead><tr>
            <th>Client</th><th>Technologie</th>
            <th>Souscrit</th><th>Offre</th><th>Actions</th>
        </tr></thead>
        <tbody>
            @forelse($allocations as $allocation)
                <tr>
                    <td>{{ $allocation->client->nom }}</td>
                    <td>{{ $allocation->technologie->nom }}</td>
                    <td class="mono">{{ $allocation->bande_passante_souscrite }} Mbps</td>
                    <td>{{ $allocation->description ?? '—' }}</td>
                    <td><div class="actions">
                        <a class="btn petit secondaire" href="{{ route('allocations.edit', $allocation) }}">Modifier</a>
                        {{-- Suppression protégée par confirmation + CSRF --}}
                        <form method="POST" action="{{ route('allocations.destroy', $allocation) }}"
                              onsubmit="return confirm('Supprimer cette allocation ?')">
                            @csrf @method('DELETE')
                            <button class="btn petit danger" type="submit">Supprimer</button>
                        </form>
                    </div></td>
                </tr>
            @empty
                <tr><td colspan="5">Aucune allocation de bande passante pour le moment.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div style="margin-top:14px">{{ $allocations->links() }}</div> {{-- Pagination Laravel --}}
@endsection
