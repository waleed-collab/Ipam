{{-- Partiel : champs communs des formulaires d'équipement. $equipement vaut null en création. --}}
<div class="champ">
    <label for="nom">Nom / hostname *</label>
    <input id="nom" name="nom" value="{{ old('nom', $equipement->nom ?? '') }}" required>
    @error('nom') <div class="erreur">{{ $message }}</div> @enderror
</div>
<div class="champ">
    <label for="type">Type d'équipement *</label>
    <input id="type" name="type" placeholder="Routeur, switch, antenne..." value="{{ old('type', $equipement->type ?? '') }}" required>
    @error('type') <div class="erreur">{{ $message }}</div> @enderror
</div>
<div class="champ">
    <label for="role">Rôle dans l'architecture *</label>
    {{-- [BACKBONE] Le backbone est l'équipement local central : il porte les interfaces
         côté international (sessions BGP) et côté accès (cadre "Local" du schéma). --}}
    <select id="role" name="role" required>
        <option value="acces"    {{ old('role', $equipement->role ?? 'acces') === 'acces' ? 'selected' : '' }}>Équipement d'accès</option>
        <option value="backbone" {{ old('role', $equipement->role ?? '') === 'backbone' ? 'selected' : '' }}>Backbone (équipement local central)</option>
    </select>
    @error('role') <div class="erreur">{{ $message }}</div> @enderror
    <div class="sous-titre">Les interfaces (côté international / côté accès) se gèrent depuis la fiche d'un équipement backbone.</div>
</div>
<div class="champ">
    <label for="adresse_ip">Adresse IP de management</label>
    <input id="adresse_ip" name="adresse_ip" class="mono" value="{{ old('adresse_ip', $equipement->adresse_ip ?? '') }}">
    @error('adresse_ip') <div class="erreur">{{ $message }}</div> @enderror
</div>
<div class="champ">
    <label for="technologie_id">Technologie associée (cas "Associer un équipement à une technologie")</label>
    <select id="technologie_id" name="technologie_id">
        <option value="">— Aucune —</option>
        @foreach($technologies as $technologie) {{-- Liste des technologies existantes --}}
            <option value="{{ $technologie->id }}"
                {{ old('technologie_id', $equipement->technologie_id ?? '') == $technologie->id ? 'selected' : '' }}>
                {{ $technologie->nom }}
            </option>
        @endforeach
    </select>
</div>
