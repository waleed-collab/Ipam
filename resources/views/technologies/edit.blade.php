{{-- Vue : modification d'une technologie (cas "Modifier une technologie"). --}}
@extends('layouts.app')
@section('titre', 'Modifier une technologie')
@section('contenu')
    <header class="page"><h1>Modifier « {{ $technologie->nom }} »</h1></header>
    <div class="carte">
        <form class="fiche" method="POST" action="{{ route('technologies.update', $technologie) }}">
            @csrf @method('PUT')
            @include('technologies._form')
            <button class="btn" type="submit">Enregistrer les modifications</button>
            <a class="btn secondaire" href="{{ route('technologies.index') }}">Annuler</a>
        </form>
    </div>
@endsection
