{{-- Partiel : champ MOT DE PASSE avec icône cadenas + bouton ŒIL (afficher/masquer).
     Paramètres : $nom (name/id du champ), $libelle (texte du label), $autocomplete (facultatif). --}}
<div class="champ">
    <label for="{{ $nom }}">{{ $libelle }}</label>
    <div class="enveloppe">
        {{-- Icône cadenas à gauche du champ --}}
        <svg class="gauche" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="10" width="16" height="10" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>
        {{-- Champ masqué par défaut ; le bouton œil bascule son type password/text --}}
        <input id="{{ $nom }}" type="password" name="{{ $nom }}" required
               autocomplete="{{ $autocomplete ?? 'current-password' }}">
        {{-- [ŒIL] Bouton afficher/masquer avec deux icônes échangées en fondu --}}
        <button type="button" class="btn-oeil" onclick="basculerOeil(this)" aria-label="Afficher ou masquer le mot de passe">
            <svg class="oeil-ouvert" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
            <svg class="oeil-barre" style="display:none" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3l18 18M10.6 10.7a3 3 0 0 0 4.2 4.2M9.9 5.2A9.7 9.7 0 0 1 12 5c6.5 0 10 7 10 7a17.6 17.6 0 0 1-3.2 4M6.1 6.5C3.6 8.4 2 12 2 12s3.5 7 10 7c1.4 0 2.7-.3 3.8-.8"/></svg>
        </button>
    </div>
    @error($nom) <div class="erreur">{{ $message }}</div> @enderror
</div>
