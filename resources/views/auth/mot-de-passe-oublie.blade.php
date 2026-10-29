{{-- Vue : MOT DE PASSE OUBLIÉ — étape 1 : demande d'envoi du lien par email. --}}
@extends('layouts.auth')
@section('titre', 'Mot de passe oublié')
@section('contenu')
    <h1>Mot de passe oublié ?</h1>
    <div class="sous-titre">Indiquez votre email : nous vous enverrons un lien de réinitialisation valable 60 minutes.</div>

    <form method="POST" action="{{ route('password.email') }}">
        @csrf                                               {{-- Jeton anti-CSRF --}}
        <div class="champ">
            <label for="email">Adresse email</label>
            <div class="enveloppe">
                <svg class="gauche" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email">
            </div>
            @error('email') <div class="erreur">{{ $message }}</div> @enderror
        </div>
        <button class="btn-principal" type="submit">Envoyer le lien</button>
    </form>

    <div class="liens">
        <a href="{{ route('login') }}">← Retour à la connexion</a>
    </div>
@endsection
