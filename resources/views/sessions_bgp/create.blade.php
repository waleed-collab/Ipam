{{-- Vue : ajout d'une session BGP (cas "Ajouter une session BGP"). --}}
@extends('layouts.app')
@section('titre', 'Nouvelle session BGP')
@section('contenu')
    <header class="page"><h1>Ajouter une session BGP</h1></header>
    <div class="carte">
        <form class="fiche" method="POST" action="{{ route('sessions-bgp.store') }}">
            @csrf
            @include('sessions_bgp._form', ['session' => null]) {{-- Champs partagés vides --}}
            <button class="btn" type="submit">Enregistrer</button>
            <a class="btn secondaire" href="{{ route('sessions-bgp.index') }}">Annuler</a>
        </form>
    </div>
@endsection
