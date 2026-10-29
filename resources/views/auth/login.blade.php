{{-- Vue : CONNEXION — design moderne (layout dédié, icônes, œil, animations). --}}
@extends('layouts.auth')                                    {{-- Layout d'authentification moderne --}}
@section('titre', 'Connexion')
@section('contenu')
    <h1>Bon retour 👋</h1>
    <div class="sous-titre">Connectez-vous pour accéder à votre espace (admin, manager ou client).</div>

    {{-- Formulaire envoyé en POST vers la route "login.post" --}}
    <form method="POST" action="{{ route('login.post') }}">
        @csrf                                               {{-- Jeton anti-CSRF --}}

        <div class="champ">
            <label for="email">Adresse email</label>
            <div class="enveloppe">
                {{-- Icône enveloppe --}}
                <svg class="gauche" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
                {{-- old('email') ré-affiche la valeur saisie en cas d'erreur --}}
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                       autocomplete="email" placeholder="vous@exemple.com">
            </div>
            @error('email') <div class="erreur">{{ $message }}</div> @enderror
        </div>

        {{-- Champ mot de passe AVEC icône œil (partiel réutilisable) --}}
        @include('auth._champ-mot-de-passe', ['nom' => 'password', 'libelle' => 'Mot de passe'])

        <div class="champ case">
            <input type="checkbox" id="remember" name="remember"> {{-- Cookie "se souvenir de moi" --}}
            <label for="remember" style="margin:0; font-weight:400">Se souvenir de moi</label>
        </div>

        <button class="btn-principal" type="submit">Se connecter</button>
    </form>

    {{-- Liens vers les autres parcours d'authentification --}}
    <div class="liens">
        <a href="{{ route('password.request') }}">Mot de passe oublié ?</a>
        <a href="{{ route('register') }}">Créer un compte client</a>
    </div>
@endsection
