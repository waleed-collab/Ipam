{{-- Vue : formulaire d'export dédié (personnel uniquement).
     Permet d'AJUSTER les informations juste pour le document généré, sans toucher la fiche en base
     (cas "modifier avant de télécharger" côté admin/manager). --}}
@extends('layouts.app')
@section('titre', 'Exporter la fiche client')
@section('contenu')
    <header class="page">
        <div>
            <h1>Exporter la fiche de {{ $client->nom }}</h1>
            <div class="sous-titre">
                Les valeurs ci-dessous ne servent qu'au document généré : elles ne modifient PAS la fiche officielle du client.
            </div>
        </div>
    </header>

    <div class="carte" style="max-width:640px">
        <form method="POST" action="{{ route('clients.exporter.generer', $client) }}" class="fiche">
            @csrf
            <div class="champ">
                <label for="nom">Nom / raison sociale *</label>
                <input id="nom" name="nom" value="{{ old('nom', $client->nom) }}" required>
                @error('nom') <div class="erreur">{{ $message }}</div> @enderror
            </div>
            <div class="champ">
                <label for="email">Email *</label>
                <input id="email" name="email" type="email" value="{{ old('email', $client->email) }}" required>
                @error('email') <div class="erreur">{{ $message }}</div> @enderror
            </div>
            <div class="champ">
                <label for="telephone">Téléphone</label>
                <input id="telephone" name="telephone" value="{{ old('telephone', $client->telephone) }}">
            </div>
            <div class="champ">
                <label for="adresse">Adresse</label>
                <input id="adresse" name="adresse" value="{{ old('adresse', $client->adresse) }}">
            </div>

            <div class="champ">
                <label>Format du document *</label>
                <div style="display:flex; gap:18px; margin-top:4px">
                    <label style="display:flex; align-items:center; gap:6px; font-weight:400">
                        <input type="radio" name="format" value="pdf" checked style="width:auto"> PDF
                    </label>
                    <label style="display:flex; align-items:center; gap:6px; font-weight:400">
                        <input type="radio" name="format" value="csv" style="width:auto"> Excel / CSV
                    </label>
                </div>
                @error('format') <div class="erreur">{{ $message }}</div> @enderror
            </div>

            <div style="display:flex; gap:10px">
                <button type="submit" class="btn">Télécharger</button>
                <a href="{{ route('clients.show', $client) }}" class="btn secondaire">Annuler</a>
            </div>
        </form>
    </div>
@endsection
