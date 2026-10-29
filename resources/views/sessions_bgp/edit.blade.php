{{-- Vue : modification d'une session BGP (cas "Modifier une session BGP"). --}}
@extends('layouts.app')
@section('titre', 'Modifier une session BGP')
@section('contenu')
    <header class="page"><h1>Modifier la session vers {{ $session->operateur->nom }}</h1></header>
    <div class="carte">
        <form class="fiche" method="POST" action="{{ route('sessions-bgp.update', $session) }}">
            @csrf @method('PUT')
            @include('sessions_bgp._form')                   {{-- Champs partagés pré-remplis --}}
            <button class="btn" type="submit">Enregistrer les modifications</button>
            <a class="btn secondaire" href="{{ route('sessions-bgp.index') }}">Annuler</a>
        </form>
    </div>
@endsection
