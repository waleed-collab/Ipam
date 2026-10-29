{{-- Partiel : champs communs aux formulaires de création et de modification d'un opérateur. --}}
{{-- $operateur vaut null en création ; old(...) garde la saisie en cas d'erreur de validation. --}}
<div class="champ">
    <label for="nom">Nom de l'opérateur *</label>
    <input id="nom" name="nom" value="{{ old('nom', $operateur->nom ?? '') }}" required>
    @error('nom') <div class="erreur">{{ $message }}</div> @enderror
</div>
<div class="champ">
    <label for="asn">Numéro d'AS (ASN) *</label>
    <input id="asn" name="asn" type="number" min="1" value="{{ old('asn', $operateur->asn ?? '') }}" required>
    @error('asn') <div class="erreur">{{ $message }}</div> @enderror
</div>
<div class="champ">
    <label for="contact">Personne de contact</label>
    <input id="contact" name="contact" value="{{ old('contact', $operateur->contact ?? '') }}">
</div>
<div class="champ">
    <label for="email_contact">Email de contact</label>
    <input id="email_contact" name="email_contact" type="email" value="{{ old('email_contact', $operateur->email_contact ?? '') }}">
    @error('email_contact') <div class="erreur">{{ $message }}</div> @enderror
</div>
<div class="champ">
    <label for="description">Description</label>
    <textarea id="description" name="description" rows="3">{{ old('description', $operateur->description ?? '') }}</textarea>
</div>
