{{-- Partiel : champs communs des formulaires client. $client vaut null en création. --}}
<div class="champ">
    <label for="nom">Nom / raison sociale *</label>
    <input id="nom" name="nom" value="{{ old('nom', $client->nom ?? '') }}" required>
    @error('nom') <div class="erreur">{{ $message }}</div> @enderror
</div>
<div class="champ">
    <label for="email">Email *</label>
    <input id="email" name="email" type="email" value="{{ old('email', $client->email ?? '') }}" required>
    @error('email') <div class="erreur">{{ $message }}</div> @enderror
</div>
<div class="champ">
    <label for="telephone">Téléphone</label>
    <input id="telephone" name="telephone" value="{{ old('telephone', $client->telephone ?? '') }}">
</div>
<div class="champ">
    <label for="adresse">Adresse</label>
    <input id="adresse" name="adresse" value="{{ old('adresse', $client->adresse ?? '') }}">
</div>
