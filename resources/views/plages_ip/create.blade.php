{{-- Vue : ajout d'un sous-bloc d'adresses (cas "Ajouter un sous-bloc"). --}}
@extends('layouts.app')
@section('titre', "Nouveau sous-bloc d'adresses")
@section('contenu')
    <header class="page"><h1>Ajouter un sous-bloc d'adresses</h1></header>
    <div class="carte">
        <form class="fiche" method="POST" action="{{ route('plages-ip.store') }}">
            @csrf
            @include('plages_ip._form', ['plage' => null])
            <button class="btn" type="submit">Enregistrer</button>
            <a class="btn secondaire" href="{{ route('plages-ip.index') }}">Annuler</a>
        </form>
    </div>
@endsection
