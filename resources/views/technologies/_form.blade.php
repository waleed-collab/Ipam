{{-- Partiel : champs communs des formulaires de technologie. $technologie vaut null en création. --}}
<div class="champ">
    <label for="nom">Nom de la technologie *</label>
    <input id="nom" name="nom" value="{{ old('nom', $technologie->nom ?? '') }}" required>
    @error('nom') <div class="erreur">{{ $message }}</div> @enderror
</div>
<div class="champ">
    <label for="description">Description</label>
    <textarea id="description" name="description" rows="3">{{ old('description', $technologie->description ?? '') }}</textarea>
</div>
<div class="champ">
    <label for="debit_max_supporte">Débit max supporté par client (Mbps)</label>
    {{-- [CAPACITY PLANNING] Limite technique : un client ne peut pas souscrire au-delà --}}
    <input id="debit_max_supporte" name="debit_max_supporte" type="number" min="1"
           value="{{ old('debit_max_supporte', $technologie->debit_max_supporte ?? '') }}"
           placeholder="Laisser vide si aucune limite">
    @error('debit_max_supporte') <div class="erreur">{{ $message }}</div> @enderror
</div>
