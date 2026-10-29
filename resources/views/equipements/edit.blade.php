{{-- Vue : modification d'un équipement. --}}
@extends('layouts.app')
@section('titre', 'Modifier un équipement')
@section('contenu')
    <header class="page"><h1>Modifier « {{ $equipement->nom }} »</h1></header>
    <div class="carte">
        <form class="fiche" method="POST" action="{{ route('equipements.update', $equipement) }}">
            @csrf @method('PUT')
            @include('equipements._form')
            <button class="btn" type="submit">Enregistrer les modifications</button>
            <a class="btn secondaire" href="{{ route('equipements.index') }}">Annuler</a>
        </form>
    </div>
@endsection
