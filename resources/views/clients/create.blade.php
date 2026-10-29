{{-- Vue : ajout d'un client (cas "Ajouter un client"). --}}
@extends('layouts.app')
@section('titre', 'Nouveau client')
@section('contenu')
    <header class="page"><h1>Ajouter un client</h1></header>
    <div class="carte">
        <form class="fiche" method="POST" action="{{ route('clients.store') }}">
            @csrf
            @include('clients._form', ['client' => null])
            <button class="btn" type="submit">Enregistrer</button>
            <a class="btn secondaire" href="{{ route('clients.index') }}">Annuler</a>
        </form>
    </div>
@endsection
