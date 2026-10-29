{{-- Vue : modification / association d'un sous-bloc d'adresses. --}}
@extends('layouts.app')
@section('titre', "Modifier un sous-bloc d'adresses")
@section('contenu')
    <header class="page"><h1>Modifier le sous-bloc <span class="mono">{{ $plage->reseau }}</span></h1></header>
    <div class="carte">
        <form class="fiche" method="POST" action="{{ route('plages-ip.update', $plage) }}">
            @csrf @method('PUT')
            @include('plages_ip._form')
            <button class="btn" type="submit">Enregistrer les modifications</button>
            <a class="btn secondaire" href="{{ route('plages-ip.index') }}">Annuler</a>
        </form>
    </div>
@endsection
