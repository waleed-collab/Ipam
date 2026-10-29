{{-- Vue : formulaire d'ajout d'un opérateur (cas "Ajouter un opérateur"). --}}
@extends('layouts.app')
@section('titre', 'Nouvel opérateur')
@section('contenu')
    <header class="page"><h1>Ajouter un opérateur</h1></header>
    <div class="carte">
        <form class="fiche" method="POST" action="{{ route('operateurs.store') }}">
            @csrf                                            {{-- Jeton anti-CSRF --}}
            @include('operateurs._form', ['operateur' => null]) {{-- Champs partagés, vides --}}
            <button class="btn" type="submit">Enregistrer</button>
            <a class="btn secondaire" href="{{ route('operateurs.index') }}">Annuler</a>
        </form>
    </div>
@endsection
