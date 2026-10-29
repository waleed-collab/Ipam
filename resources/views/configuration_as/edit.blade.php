{{-- Vue : paramètres du Système Autonome local (cas "Configurer les paramètres AS"). --}}
@extends('layouts.app')
@section('titre', 'Ressources Internet')
@section('contenu')
    <header class="page">
        <div>
            <h1>Ressources Internet du réseau local</h1>
            <div class="sous-titre">Configuration du Système Autonome de votre réseau.</div>
        </div>
    </header>
    <div class="carte">
        <form class="fiche" method="POST" action="{{ route('configuration-as.update') }}">
            @csrf @method('PUT')                             {{-- Mise à jour de l'unique configuration --}}
            <div class="champ">
                <label for="asn_local">Numéro d'AS local *</label>
                <input id="asn_local" name="asn_local" type="number" min="0"
                       value="{{ old('asn_local', $configuration->asn_local) }}" required>
                @error('asn_local') <div class="erreur">{{ $message }}</div> @enderror
            </div>
            <div class="champ">
                <label for="nom_reseau">Nom du réseau *</label>
                <input id="nom_reseau" name="nom_reseau"
                       value="{{ old('nom_reseau', $configuration->nom_reseau) }}" required>
                @error('nom_reseau') <div class="erreur">{{ $message }}</div> @enderror
            </div>
            <div class="champ">
                <label for="router_id">Router-ID (adresse IPv4)</label>
                <input id="router_id" name="router_id" value="{{ old('router_id', $configuration->router_id) }}">
                @error('router_id') <div class="erreur">{{ $message }}</div> @enderror
            </div>
            {{-- [RESSOURCES] Plages principales : le bloc d'adresses officiellement attribué à
                 l'opérateur. C'est à l'intérieur de ces plages que sont découpés les sous-blocs
                 affectés aux clients. Le tableau de bord en déduit la capacité globale, la part
                 déjà utilisée et ce qu'il reste.

                 [AIDE] Le texte sous chaque champ est CALCULÉ à partir de la valeur réellement
                 saisie (et non d'un exemple figé) : on voit tout de suite combien d'adresses
                 représente le bloc que l'on est en train d'entrer. --}}
            <div class="champ">
                <label for="prefixe_ipv4">Plage principale IPv4</label>
                <input id="prefixe_ipv4" name="prefixe_ipv4" class="mono" placeholder="ex : 102.64.0.0/22"
                       value="{{ old('prefixe_ipv4', $configuration->prefixe_ipv4) }}"
                       data-calcul="4">
                @error('prefixe_ipv4') <div class="erreur">{{ $message }}</div> @enderror
                <div class="sous-titre">
                    Bloc d'adresses dont dispose votre réseau. Les sous-blocs des clients
                    sont découpés dedans.
                </div>
                <div class="sous-titre" id="calcul-ipv4" style="margin-top:4px"></div>
            </div>
            <div class="champ">
                <label for="prefixe_ipv6">Plage principale IPv6</label>
                <input id="prefixe_ipv6" name="prefixe_ipv6" class="mono" placeholder="ex : 2001:db8::/32"
                       value="{{ old('prefixe_ipv6', $configuration->prefixe_ipv6) }}"
                       data-calcul="6">
                @error('prefixe_ipv6') <div class="erreur">{{ $message }}</div> @enderror
                <div class="sous-titre">
                    Bloc IPv6 dont dispose votre réseau. Il se compte en sous-réseaux /64,
                    l'unité d'attribution habituelle en IPv6.
                </div>
                <div class="sous-titre" id="calcul-ipv6" style="margin-top:4px"></div>
            </div>
            <div class="champ">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="3">{{ old('description', $configuration->description) }}</textarea>
            </div>
            <button class="btn" type="submit">Enregistrer les paramètres</button>
        </form>
    </div>

    <script>
        // [AIDE] Traduit en clair le bloc saisi : « 102.64.0.0/22 → 1 024 adresses au total ».
        // Le calcul suit la convention de l'application :
        //   IPv4 : 2^(32 - masque) adresses      IPv6 : 2^(64 - masque) sous-réseaux /64
        (function () {
            'use strict';

            function expliquer(champ, sortie) {
                var valeur = (champ.value || '').trim();
                var partie = valeur.split('/');

                // Rien de saisi, ou pas encore de longueur de préfixe : on n'affiche rien.
                if (partie.length !== 2 || partie[1] === '' || isNaN(partie[1])) {
                    sortie.textContent = '';
                    return;
                }

                var ipv6    = champ.dataset.calcul === '6';
                var base    = ipv6 ? 64 : 32;
                var masque  = parseInt(partie[1], 10);
                var maxi    = ipv6 ? 128 : 32;

                if (masque < 0 || masque > maxi) {
                    sortie.textContent = 'Longueur de préfixe invalide (0 à ' + maxi + ').';
                    return;
                }

                var unite = ipv6 ? 'sous-réseaux /64' : 'adresses';
                var total = masque >= base ? 1 : Math.pow(2, base - masque);

                sortie.innerHTML = 'Soit <b>' + total.toLocaleString('fr-FR') + '</b> ' + unite +
                                   ' au total (2<sup>' + (base - Math.min(masque, base)) + '</sup>).';
            }

            document.querySelectorAll('input[data-calcul]').forEach(function (champ) {
                var sortie = document.getElementById('calcul-ipv' + champ.dataset.calcul);
                if (!sortie) { return; }
                expliquer(champ, sortie);                       // Au chargement de la page
                champ.addEventListener('input', function () {   // Puis à chaque frappe
                    expliquer(champ, sortie);
                });
            });
        })();
    </script>
@endsection
