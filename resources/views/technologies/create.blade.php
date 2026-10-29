{{-- Vue : ajout d'une technologie (cas "Ajouter une technologie"). --}}
@extends('layouts.app')
@section('titre', 'Nouvelle technologie')
@section('contenu')
    <header class="page"><h1>Ajouter une technologie</h1></header>
    <div class="carte">
        <form class="fiche" method="POST" action="{{ route('technologies.store') }}">
            @csrf
            @include('technologies._form', ['technologie' => null])
            <button class="btn" type="submit">Enregistrer</button>
            <a class="btn secondaire" href="{{ route('technologies.index') }}">Annuler</a>
        </form>
    </div>
@endsection
