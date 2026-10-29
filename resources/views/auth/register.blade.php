{{-- Vue : INSCRIPTION CLIENT — même identité graphique que la connexion.
     Crée une fiche client + un compte au rôle "client" (lecture seule sur son périmètre). --}}
@extends('layouts.auth')
@section('titre', 'Inscription')
@section('contenu')
    <h1>Créer votre compte client</h1>
    <div class="sous-titre">Accédez en lecture seule à vos plages IP, technologies et équipements.</div>

    <form method="POST" action="{{ route('register.post') }}">
        @csrf                                               {{-- Jeton anti-CSRF --}}

        {{-- Nom et prénom côte à côte --}}
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px">
            <div class="champ">
                <label for="prenom">Prénom</label>
                <div class="enveloppe">
                    <svg class="gauche" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/></svg>
                    <input id="prenom" name="prenom" value="{{ old('prenom') }}" required autofocus autocomplete="given-name">
                </div>
                @error('prenom') <div class="erreur">{{ $message }}</div> @enderror
            </div>
            <div class="champ">
                <label for="nom">Nom</label>
                <div class="enveloppe">
                    <svg class="gauche" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/></svg>
                    <input id="nom" name="nom" value="{{ old('nom') }}" required autocomplete="family-name">
                </div>
                @error('nom') <div class="erreur">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="champ">
            <label for="entreprise">Entreprise <small style="color:var(--encre-2)">(si applicable)</small></label>
            <div class="enveloppe">
                <svg class="gauche" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="3" width="16" height="18" rx="1"/><path d="M9 8h2m2 0h2M9 12h2m2 0h2M9 16h2m2 0h2"/></svg>
                <input id="entreprise" name="entreprise" value="{{ old('entreprise') }}" autocomplete="organization">
            </div>
            @error('entreprise') <div class="erreur">{{ $message }}</div> @enderror
        </div>

        <div class="champ">
            <label for="email">Adresse email</label>
            <div class="enveloppe">
                <svg class="gauche" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email">
            </div>
            @error('email') <div class="erreur">{{ $message }}</div> @enderror
        </div>

        <div class="champ">
            <label for="telephone">Téléphone</label>
            <div class="enveloppe">
                <svg class="gauche" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 4h4l2 5-2.5 1.5a12 12 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2Z"/></svg>
                <input id="telephone" name="telephone" value="{{ old('telephone') }}" required autocomplete="tel" placeholder="+226 70 00 00 00">
            </div>
            @error('telephone') <div class="erreur">{{ $message }}</div> @enderror
        </div>

        {{-- Mot de passe + confirmation, tous deux avec l'icône œil --}}
        @include('auth._champ-mot-de-passe', ['nom' => 'password', 'libelle' => 'Mot de passe', 'autocomplete' => 'new-password'])
        @include('auth._champ-mot-de-passe', ['nom' => 'password_confirmation', 'libelle' => 'Confirmation du mot de passe', 'autocomplete' => 'new-password'])

        <button class="btn-principal" type="submit">Créer mon compte</button>
    </form>

    {{-- [FIX] « Déjà inscrit ? » était un simple <span> NON cliquable, et la mise en page
         (space-between) l'éloignait du lien à l'autre bout de la carte : on cliquait dessus
         sans aucun effet. Toute la phrase forme désormais un seul lien, centré. --}}
    <div class="liens" style="justify-content:center">
        <a href="{{ route('login') }}">
            <span style="color:var(--encre-2)">Déjà inscrit ?</span> Se connecter
        </a>
    </div>
@endsection
