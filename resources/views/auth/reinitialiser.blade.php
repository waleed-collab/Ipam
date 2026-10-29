{{-- Vue : RÉINITIALISATION — étape finale : choix du NOUVEAU mot de passe (via le lien reçu par email). --}}
@extends('layouts.auth')
@section('titre', 'Nouveau mot de passe')
@section('contenu')
    <h1>Choisissez un nouveau mot de passe</h1>
    <div class="sous-titre">Minimum 8 caractères, avec au moins une lettre et un chiffre.</div>

    <form method="POST" action="{{ route('password.update') }}">
        @csrf                                               {{-- Jeton anti-CSRF --}}
        {{-- Jeton de réinitialisation transmis dans le lien de l'email --}}
        <input type="hidden" name="token" value="{{ $token }}">

        <div class="champ">
            <label for="email">Adresse email</label>
            <div class="enveloppe">
                <svg class="gauche" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
                {{-- Email pré-rempli depuis l'URL du lien --}}
                <input id="email" type="email" name="email" value="{{ old('email', $email) }}" required autocomplete="email">
            </div>
            @error('email') <div class="erreur">{{ $message }}</div> @enderror
        </div>

        {{-- Nouveau mot de passe + confirmation, tous deux avec l'icône œil --}}
        @include('auth._champ-mot-de-passe', ['nom' => 'password', 'libelle' => 'Nouveau mot de passe', 'autocomplete' => 'new-password'])
        @include('auth._champ-mot-de-passe', ['nom' => 'password_confirmation', 'libelle' => 'Confirmation du nouveau mot de passe', 'autocomplete' => 'new-password'])

        <button class="btn-principal" type="submit">Réinitialiser le mot de passe</button>
    </form>

    <div class="liens">
        <a href="{{ route('login') }}">← Retour à la connexion</a>
    </div>
@endsection
