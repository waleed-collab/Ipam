{{-- ====================================================================
     Composant : CHARGEUR GLOBAL.
     Trois usages, tous basés sur le même visuel (composants.chargeur) :
       1. voile plein écran pendant un changement de page ou l'envoi d'un formulaire ;
       2. mini-chargeur dans le bouton d'envoi (évite aussi les doubles clics) ;
       3. exports PDF / CSV : le voile s'affiche puis se retire seul, car un
          téléchargement ne provoque aucune navigation.
     À inclure une fois par gabarit, juste avant la fermeture du corps.
     ==================================================================== --}}
<style>
    /* ---------- 1. Voile plein écran ---------- */
    .voile-chargement {
        position:fixed; inset:0; z-index:9999;
        display:flex; flex-direction:column; align-items:center; justify-content:center; gap:16px;
        background:rgba(4,16,22,.74); backdrop-filter:blur(3px);
        opacity:0; visibility:hidden; transition:opacity .25s ease, visibility .25s ease;
        /* Sur ce fond sombre, on force les couleurs claires du chargeur */
        --accent:#4db8cc;
    }
    .voile-chargement.actif { opacity:1; visibility:visible; }
    .voile-chargement .svg-frame { color:rgba(214,232,240,.45); }        /* Traits neutres éclaircis */
    .voile-chargement .libelle {
        color:#dbe7ee; font-size:.85rem; letter-spacing:.05em;
        animation:pulsation-libelle 1.8s ease-in-out infinite;
    }
    @keyframes pulsation-libelle { 0%,100%{opacity:.55} 50%{opacity:1} }

    /* ---------- 2. Mini-chargeur dans un bouton ---------- */
    .btn.chargement, .btn-principal.chargement { pointer-events:none; opacity:.9; }
    .mini-chargeur {
        position:relative; display:inline-block; width:1em; height:1em;
        vertical-align:-.15em; margin-right:.5em;
    }
    .mini-chargeur svg { position:absolute; inset:0; width:100%; height:100%; fill:none; }
    .mini-anneau {
        stroke:currentColor; stroke-width:22; stroke-dasharray:70 150; opacity:.5;
        transform-origin:center; animation:rotation-chargeur 1.1s linear infinite;
    }
    .mini-hexa {
        stroke:currentColor; stroke-width:18; transform-origin:center;
        animation:rotation-chargeur 2.2s ease-in-out infinite alternate;
    }
    @media (prefers-reduced-motion: reduce) {
        .mini-anneau, .mini-hexa, .voile-chargement .libelle { animation:none !important; }
    }
</style>

{{-- Voile : le chargeur est le composant partagé, en taille réduite --}}
<div class="voile-chargement" id="voile-chargement" role="alert" aria-live="assertive" aria-hidden="true">
    @include('composants.chargeur', ['taille' => 120])
    <div class="libelle">Chargement…</div>
</div>

{{-- Modèle du mini-chargeur, cloné dans les boutons par le script ci-dessous --}}
<template id="modele-mini-chargeur">
    <span class="mini-chargeur" aria-hidden="true">
        <svg viewBox="0 0 344 344"><circle class="mini-anneau" cx="172" cy="172" r="130"/></svg>
        <svg viewBox="0 0 344 344"><polygon class="mini-hexa" points="314,172 243,295 101,295 30,172 101,49 243,49"/></svg>
    </span>
</template>

<script>
(function () {
    'use strict';
    var voile   = document.getElementById('voile-chargement');
    var modele  = document.getElementById('modele-mini-chargeur');
    if (!voile) { return; }

    var minuterie = null;   // Petit délai avant affichage (évite un clignotement sur page rapide)
    var secours   = null;   // Filet de sécurité : le voile ne doit jamais rester bloqué

    function afficher(delaiSecours) {
        clearTimeout(minuterie); clearTimeout(secours);
        minuterie = setTimeout(function () {
            voile.classList.add('actif');
            voile.setAttribute('aria-hidden', 'false');
        }, 180);
        secours = setTimeout(masquer, delaiSecours);
    }

    function masquer() {
        clearTimeout(minuterie); clearTimeout(secours);
        voile.classList.remove('actif');
        voile.setAttribute('aria-hidden', 'true');
        // On rend aussi les boutons à leur état normal (retour arrière du navigateur)
        document.querySelectorAll('.chargement').forEach(function (bouton) {
            bouton.classList.remove('chargement');
            bouton.disabled = false;
            var mini = bouton.querySelector('.mini-chargeur');
            if (mini) { mini.remove(); }
        });
    }

    /** Un téléchargement ne change pas de page : le voile doit se retirer seul. */
    function estTelechargement(url) {
        return /exporter|\.pdf|\.csv/i.test(url || '');
    }

    /** Passe un bouton en état de chargement (mini-chargeur + désactivation). */
    function occuperBouton(bouton) {
        if (!bouton || bouton.classList.contains('chargement')) { return; }
        bouton.classList.add('chargement');
        if (modele && modele.content) {
            bouton.insertBefore(modele.content.cloneNode(true).firstElementChild, bouton.firstChild);
        }
        // Désactivation différée : sinon certains navigateurs annulent l'envoi en cours.
        setTimeout(function () { bouton.disabled = true; }, 0);
    }

    // ---------- Envoi d'un formulaire ----------
    // Écoute en phase de remontée : les gestionnaires onsubmit="return confirm(...)"
    // se sont donc déjà exécutés. Si l'utilisateur a annulé, on ne fait rien.
    document.addEventListener('submit', function (e) {
        if (e.defaultPrevented) { return; }
        var formulaire = e.target;
        if (formulaire.hasAttribute('data-sans-chargeur')) { return; }

        occuperBouton(formulaire.querySelector('button[type="submit"], button:not([type])'));
        afficher(estTelechargement(formulaire.action) ? 4000 : 15000);
    });

    // ---------- Navigation par lien ----------
    document.addEventListener('click', function (e) {
        var lien = e.target.closest ? e.target.closest('a') : null;
        if (!lien || e.defaultPrevented) { return; }
        if (e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) { return; }  // Nouvel onglet
        if (lien.hasAttribute('download') || lien.hasAttribute('data-sans-chargeur')) { return; }
        if (lien.target && lien.target !== '_self') { return; }

        var href = lien.getAttribute('href') || '';
        if (!href || href.charAt(0) === '#' || /^(javascript|mailto|tel):/i.test(href)) { return; }
        if (lien.origin && lien.origin !== window.location.origin) { return; }               // Lien externe

        afficher(estTelechargement(href) ? 4000 : 15000);
    });

    // ---------- Retour arrière / restauration depuis le cache ----------
    window.addEventListener('pageshow', masquer);
    document.addEventListener('visibilitychange', function () {
        if (document.visibilityState === 'visible') { masquer(); }
    });
})();
</script>
