{{-- ====================================================================
     Composant : CHARGEUR (indicateur de chargement).
     Anneaux et hexagones concentriques qui tournent à des vitesses
     différentes, avec un dépliage 3D au survol.

     Inclusion : include('composants.chargeur') — taille par défaut 140 px.
     Taille personnalisée : passer la variable "taille" (en pixels).

     Le CSS n'est émis qu'une seule fois par page grâce à once, même si le
     composant est inclus plusieurs fois. Les couleurs suivent la palette de
     l'application via les variables --accent et --encre-2.
     ==================================================================== --}}
@once
    <style>
        /* Cadre : contexte 3D pour le dépliage des couches. La couleur du cadre
           sert de couleur de trait par défaut (currentColor dans les SVG). */
        .svg-frame {
            position:relative; display:flex; align-items:center; justify-content:center;
            width:var(--chargeur-taille, 140px); height:var(--chargeur-taille, 140px);
            transform-style:preserve-3d; color:var(--encre-2);
        }

        /* Chaque couche est un SVG superposé. --j règle l'empilement, --i l'écartement 3D. */
        .svg-frame svg {
            position:absolute; width:100%; height:100%; fill:none;
            transition:.5s; transform-origin:center;
            z-index:calc(1 - (0.2 * var(--j)));
        }

        /* Dépliage au survol : les couches s'écartent en perspective */
        .svg-frame:hover svg {
            transform: rotate(-80deg) skew(30deg)
                       translateX(calc(18px * var(--i))) translateY(calc(-14px * var(--i)));
        }
        .svg-frame svg #center { transition:.5s; transform-origin:center; }
        .svg-frame:hover svg #center { transform: rotate(-30deg) translateX(18px) translateY(-1px); }

        /* ---------- Rotations continues, à vitesses différentes ---------- */
        #out2   { animation:rotation-chargeur 7s ease-in-out infinite alternate; transform-origin:center; }
        #out3   { animation:rotation-chargeur 3s ease-in-out infinite alternate; transform-origin:center;
                  stroke:var(--accent); }                       /* Couche mise en couleur (accent) */
        #inner1,
        #inner3 { animation:rotation-chargeur 4s ease-in-out infinite alternate; transform-origin:center; }
        #inner2 { animation:rotation-chargeur 9s linear infinite; transform-origin:center; }
        #center1 { fill:var(--accent); animation:rotation-chargeur 2s ease-in-out infinite alternate;
                   transform-origin:center; }
        @keyframes rotation-chargeur { to { transform:rotate(360deg); } }

        /* [ACCESSIBILITÉ] Sans animation, on garde un chargeur lisible mais immobile. */
        @media (prefers-reduced-motion: reduce) {
            #out2, #out3, #inner1, #inner2, #inner3, #center1 { animation:none !important; }
        }
    </style>
@endonce

<div class="svg-frame" role="status" aria-label="Chargement en cours"
     @isset($taille) style="--chargeur-taille: {{ (int) $taille }}px" @endisset>

    {{-- Couche 1 : le cœur (point plein + anneau) --}}
    <svg viewBox="0 0 344 344" style="--i:1; --j:1">
        <g id="center">
            <circle id="center1" cx="172" cy="172" r="9"/>
            <circle cx="172" cy="172" r="21" stroke="currentColor" stroke-width="2"/>
        </g>
    </svg>

    {{-- Couche 2 : hexagone intérieur --}}
    <svg viewBox="0 0 344 344" style="--i:2; --j:2">
        <polygon id="inner1" points="224,172 198,217 146,217 120,172 146,127 198,127"
                 stroke="currentColor" stroke-width="2"/>
    </svg>

    {{-- Couche 3 : anneau pointillé --}}
    <svg viewBox="0 0 344 344" style="--i:3; --j:3">
        <circle id="inner2" cx="172" cy="172" r="74" stroke="currentColor" stroke-width="2"
                stroke-dasharray="6 10"/>
    </svg>

    {{-- Couche 4 : hexagone médian --}}
    <svg viewBox="0 0 344 344" style="--i:4; --j:4">
        <polygon id="inner3" points="268,172 220,255 124,255 76,172 124,89 220,89"
                 stroke="currentColor" stroke-width="2"/>
    </svg>

    {{-- Couche 5 : anneau extérieur finement pointillé --}}
    <svg viewBox="0 0 344 344" style="--i:5; --j:5">
        <circle id="out2" cx="172" cy="172" r="120" stroke="currentColor" stroke-width="2"
                stroke-dasharray="2 14"/>
    </svg>

    {{-- Couche 6 : hexagone extérieur, en couleur d'accent --}}
    <svg viewBox="0 0 344 344" style="--i:6; --j:6">
        <polygon id="out3" points="314,172 243,295 101,295 30,172 101,49 243,49" stroke-width="2"/>
    </svg>
</div>
