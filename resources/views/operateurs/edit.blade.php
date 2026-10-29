{{-- Vue : formulaire de modification d'un opérateur (cas "Modifier un opérateur"). --}}
@extends('layouts.app')
@section('titre', 'Modifier un opérateur')
@section('contenu')
    <header class="page"><h1>Modifier « {{ $operateur->nom }} »</h1></header>
    <div class="carte">
        <form class="fiche" method="POST" action="{{ route('operateurs.update', $operateur) }}">
            @csrf @method('PUT')                             {{-- Verbe HTTP PUT pour la mise à jour --}}
            @include('operateurs._form')                     {{-- Champs partagés, pré-remplis --}}
            <button class="btn" type="submit">Enregistrer les modifications</button>
            <a class="btn secondaire" href="{{ route('operateurs.index') }}">Annuler</a>
        </form>
    </div>
@endsection
