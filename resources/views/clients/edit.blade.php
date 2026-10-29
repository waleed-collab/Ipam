{{-- Vue : modification d'un client (cas "Modifier un client"). --}}
@extends('layouts.app')
@section('titre', 'Modifier un client')
@section('contenu')
    <header class="page"><h1>Modifier « {{ $client->nom }} »</h1></header>
    <div class="carte">
        <form class="fiche" method="POST" action="{{ route('clients.update', $client) }}">
            @csrf @method('PUT')
            @include('clients._form')
            <button class="btn" type="submit">Enregistrer les modifications</button>
            <a class="btn secondaire" href="{{ route('clients.index') }}">Annuler</a>
        </form>
    </div>
@endsection
