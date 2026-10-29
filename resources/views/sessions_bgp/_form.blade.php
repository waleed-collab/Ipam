{{-- Partiel : champs communs aux formulaires de session BGP. $session vaut null en création.
     Champs du schéma : neighbor, adresse neighbor, ASN, adresse locale, ASN local, statut. --}}
<div class="champ">
    <label for="operateur_id">Neighbor * (opérateur — cas "Associer un opérateur à une session BGP")</label>
    <select id="operateur_id" name="operateur_id" required>
        <option value="">— Choisir le neighbor —</option>
        @foreach($operateurs as $operateur) {{-- Liste déroulante des opérateurs existants --}}
            <option value="{{ $operateur->id }}"
                {{ old('operateur_id', $session->operateur_id ?? '') == $operateur->id ? 'selected' : '' }}>
                {{ $operateur->nom }} (AS{{ $operateur->asn }})
            </option>
        @endforeach
    </select>
    @error('operateur_id') <div class="erreur">{{ $message }}</div> @enderror
</div>
<div class="champ">
    <label for="ip_voisin">Adresse neighbor *</label>
    <input id="ip_voisin" name="ip_voisin" class="mono" value="{{ old('ip_voisin', $session->ip_voisin ?? '') }}" required>
    @error('ip_voisin') <div class="erreur">{{ $message }}</div> @enderror
</div>
<div class="champ">
    <label for="asn_distant">ASN (du neighbor) *</label>
    <input id="asn_distant" name="asn_distant" type="number" min="1"
           value="{{ old('asn_distant', $session->asn_distant ?? '') }}" required>
    @error('asn_distant') <div class="erreur">{{ $message }}</div> @enderror
</div>
<div class="champ">
    <label for="interface_id">Interface internationale du backbone</label>
    {{-- [BACKBONE] La session s'appuie sur une interface côté international du cadre "Local" --}}
    <select id="interface_id" name="interface_id">
        <option value="">— Aucune (lien non renseigné) —</option>
        @foreach($interfaces as $interface)
            <option value="{{ $interface->id }}"
                {{ old('interface_id', $session->interface_id ?? '') == $interface->id ? 'selected' : '' }}>
                {{ $interface->equipement->nom ?? '?' }} · {{ $interface->nom }}
                {{ $interface->adresse_ip ? ' (' . $interface->adresse_ip . ')' : '' }}
                {{ $interface->bande_passante ? ' — ' . $interface->bande_passante . ' Mbps' : '' }}
            </option>
        @endforeach
    </select>
    @error('interface_id') <div class="erreur">{{ $message }}</div> @enderror
    <div class="sous-titre">Les interfaces se déclarent sur la fiche de l'équipement backbone.</div>
</div>
<div class="champ">
    <label for="ip_locale">Adresse locale</label>
    <input id="ip_locale" name="ip_locale" class="mono" value="{{ old('ip_locale', $session->ip_locale ?? '') }}"
           placeholder="Notre côté du lien (reprise de l'interface si vide)">
    @error('ip_locale') <div class="erreur">{{ $message }}</div> @enderror
</div>
<div class="champ">
    <label>ASN local</label>
    {{-- L'ASN local est un paramètre GLOBAL de l'AS : affiché ici, modifiable dans "Ressources Internet" --}}
    <input class="mono" value="AS{{ $configurationAs->asn_local }}" disabled>
    <div class="sous-titre">Défini dans les paramètres du système autonome (identique pour toutes les sessions).</div>
</div>
<div class="champ">
    <label for="etat">État de la session *</label>
    <select id="etat" name="etat" required>
        @foreach(['active' => 'Active', 'inactive' => 'Inactive'] as $valeur => $libelle)
            <option value="{{ $valeur }}" {{ old('etat', $session->etat ?? 'inactive') === $valeur ? 'selected' : '' }}>
                {{ $libelle }}
            </option>
        @endforeach
    </select>
</div>
<div class="champ">
    <label for="capacite_totale">Capacité totale (Mbps)</label>
    {{-- [CAPACITY PLANNING] Débit physique global fourni par l'opérateur sur cette session --}}
    <input id="capacite_totale" name="capacite_totale" type="number" min="1"
           value="{{ old('capacite_totale', $session->capacite_totale ?? '') }}"
           placeholder="ex : 1000 pour 1 Gbps (laisser vide si inconnue)">
    @error('capacite_totale') <div class="erreur">{{ $message }}</div> @enderror
</div>
<div class="champ">
    <label for="description">Description</label>
    <textarea id="description" name="description" rows="3">{{ old('description', $session->description ?? '') }}</textarea>
</div>
