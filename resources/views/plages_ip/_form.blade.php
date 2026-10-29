{{-- Partiel : champs communs des formulaires de sous-bloc d'adresses. $plage vaut null en création. --}}
<div class="champ">
    <label for="reseau">Bloc réseau (format CIDR) *</label>
    <input id="reseau" name="reseau" class="mono" placeholder="IPv4 : 192.168.10.0/24 — IPv6 : 2001:db8::/32"
           value="{{ old('reseau', $plage->reseau ?? '') }}" required>
    @error('reseau') <div class="erreur">{{ $message }}</div> @enderror
    {{-- [IPv4/IPv6] La version du protocole est détectée automatiquement à l'enregistrement --}}
    <div class="sous-titre">Les blocs IPv4 et IPv6 sont acceptés ; la version est détectée automatiquement.</div>
</div>
<div class="champ">
    <label for="description">Description / usage</label>
    <input id="description" name="description" value="{{ old('description', $plage->description ?? '') }}">
</div>
<div class="champ">
    <label for="technologie_id">Technologie associée (cas "Associer un sous-bloc d'adresses à une technologie")</label>
    <select id="technologie_id" name="technologie_id">
        <option value="">— Aucune —</option>
        @foreach($technologies as $technologie)
            <option value="{{ $technologie->id }}"
                {{ old('technologie_id', $plage->technologie_id ?? '') == $technologie->id ? 'selected' : '' }}>
                {{ $technologie->nom }}
            </option>
        @endforeach
    </select>
</div>
<div class="champ">
    <label for="client_id">Client affecté (cas "Associer un sous-bloc d'adresses à un client")</label>
    <select id="client_id" name="client_id">
        <option value="">— Sous-bloc disponible (aucun client) —</option>
        @foreach($clients as $client)
            <option value="{{ $client->id }}"
                {{ old('client_id', $plage->client_id ?? '') == $client->id ? 'selected' : '' }}>
                {{ $client->nom }}
            </option>
        @endforeach
    </select>
    <div class="sous-titre">Le statut (disponible / utilisé) est mis à jour automatiquement.</div>
</div>
