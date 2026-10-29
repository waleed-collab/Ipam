{{-- Vue : création d'une allocation de bande passante. --}}
@extends('layouts.app')
@section('titre', 'Nouvelle allocation')
@section('contenu')
    <header class="page"><h1>Nouvelle allocation de bande passante</h1></header>
    <div class="carte">
        <form class="fiche" method="POST" action="{{ route('allocations.store') }}">
            @csrf {{-- Jeton anti-CSRF --}}
            @include('allocations._form', ['allocation' => null])
            <button class="btn" type="submit">Enregistrer</button>
            <a class="btn secondaire" href="{{ route('allocations.index') }}">Annuler</a>
        </form>
    </div>
@endsection
