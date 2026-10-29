{{-- Vue : modification d'une allocation de bande passante. --}}
@extends('layouts.app')
@section('titre', 'Modifier une allocation')
@section('contenu')
    <header class="page"><h1>Modifier l'allocation</h1></header>
    <div class="carte">
        <form class="fiche" method="POST" action="{{ route('allocations.update', $allocation) }}">
            @csrf @method('PUT') {{-- Méthode PUT pour la mise à jour --}}
            @include('allocations._form')
            <button class="btn" type="submit">Enregistrer</button>
            <a class="btn secondaire" href="{{ route('allocations.index') }}">Annuler</a>
        </form>
    </div>
@endsection
