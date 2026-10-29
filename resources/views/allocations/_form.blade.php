{{-- Partiel : champs communs des formulaires d'ALLOCATION DE BANDE PASSANTE.
     $allocation vaut null en création. Règle métier appliquée côté contrôleur :
     R1 : le débit souscrit <= limite technique de la technologie. --}}
<div class="champ">
    <label for="client_id">Client bénéficiaire *</label>
    <select id="client_id" name="client_id" required>
        <option value="">— Choisir un client —</option>
        @foreach($clients as $client)
            <option value="{{ $client->id }}"
                {{ old('client_id', $allocation->client_id ?? '') == $client->id ? 'selected' : '' }}>
                {{ $client->nom }}
            </option>
        @endforeach
    </select>
    @error('client_id') <div class="erreur">{{ $message }}</div> @enderror
</div>
<div class="champ">
    <label for="technologie_id">Technologie d'accès * (impose sa limite technique)</label>
    <select id="technologie_id" name="technologie_id" required>
        <option value="">— Choisir une technologie —</option>
        @foreach($technologies as $technologie)
            <option value="{{ $technologie->id }}"
                {{ old('technologie_id', $allocation->technologie_id ?? '') == $technologie->id ? 'selected' : '' }}>
                {{ $technologie->nom }}
                @if($technologie->debit_max_supporte) (max {{ $technologie->debit_max_supporte }} Mbps/client) @endif
            </option>
        @endforeach
    </select>
    @error('technologie_id') <div class="erreur">{{ $message }}</div> @enderror
</div>
<div class="champ">
    <label for="bande_passante_souscrite">Bande passante souscrite (Mbps) *</label>
    <input id="bande_passante_souscrite" name="bande_passante_souscrite" type="number" min="1" required
           value="{{ old('bande_passante_souscrite', $allocation->bande_passante_souscrite ?? '') }}">
    {{-- L'erreur de dépassement de la limite technique (règle R1) s'affiche ici --}}
    @error('bande_passante_souscrite') <div class="erreur">{{ $message }}</div> @enderror
</div>
<div class="champ">
    <label for="description">Libellé de l'offre</label>
    <input id="description" name="description" placeholder="ex : Fibre Pro 100M"
           value="{{ old('description', $allocation->description ?? '') }}">
</div>
