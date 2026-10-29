{{-- Vue : ajout d'un équipement. --}}
@extends('layouts.app')
@section('titre', 'Nouvel équipement')
@section('contenu')
    <header class="page"><h1>Ajouter un équipement</h1></header>
    <div class="carte">
        <form class="fiche" method="POST" action="{{ route('equipements.store') }}">
            @csrf
            @include('equipements._form', ['equipement' => null])
            <button class="btn" type="submit">Enregistrer</button>
            <a class="btn secondaire" href="{{ route('equipements.index') }}">Annuler</a>
        </form>
    </div>
@endsection
