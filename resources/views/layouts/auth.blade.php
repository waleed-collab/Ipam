{{-- [DESIGN] Layout dédié aux pages d'AUTHENTIFICATION (connexion, inscription, mot de passe).
     Design moderne : fond dégradé animé, carte avec effet de profondeur, icônes,
     transitions fluides, responsive, compatible mode clair/sombre, icône œil sur les mots de passe. --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('titre', 'Connexion') — IPAM</title>

    {{-- [DARK MODE] Application du thème mémorisé AVANT le rendu (aucun flash) --}}
    <script>document.documentElement.dataset.theme = localStorage.getItem('theme-ipam') || 'clair';</script>

    <style>
        /* ============ Palette clair / sombre (cohérente avec le reste de l'application) ============ */
        :root, html[data-theme="clair"] {
            --fond-1:#0f6a7d; --fond-2:#123a4d; --fond-3:#0b4f5e;   /* Dégradé de fond */
            --carte:#ffffff; --encre:#16232e; --encre-2:#5b6b78;
            --accent:#0f6a7d; --accent-fonce:#0b4f5e; --accent-doux:#e3f0f3;
            --bord:#e2e6ea; --alerte:#b3261e; --ok:#1a7f4b; --ok-fond:#e2f3e9;
            --champ-fond:#ffffff;
        }
        html[data-theme="sombre"] {
            --fond-1:#0a2a33; --fond-2:#060d14; --fond-3:#0d1f2b;
            --carte:#16222e; --encre:#e6edf3; --encre-2:#94a7b8;
            --accent:#37a3b8; --accent-fonce:#4db8cc; --accent-doux:#12303a;
            --bord:#243342; --alerte:#ff8a80; --ok:#4cc98a; --ok-fond:#10301f;
            --champ-fond:#0f1a24;
        }

        * { box-sizing:border-box; margin:0; }

        /* [UNIFORMITÉ] Même design de barre de défilement que le reste de l'application */
        * { scrollbar-width: thin; scrollbar-color: rgba(125,147,163,.5) transparent; }
        *::-webkit-scrollbar { width: 8px; height: 8px; }
        *::-webkit-scrollbar-track { background: transparent; }
        *::-webkit-scrollbar-thumb { background: rgba(125,147,163,.5); border-radius: 999px; }
        *::-webkit-scrollbar-button { display: none; }

        /* [FIX] Le dégradé doit aussi être posé sur <html> : si le contenu dépasse 100vh
           (petite fenêtre, zoom navigateur...), c'est le fond blanc par défaut de <html>
           qui apparaissait sous la carte au lieu du dégradé. */
        html {
            min-height:100%;
            background: linear-gradient(135deg, var(--fond-1), var(--fond-2) 55%, var(--fond-3));
        }
        body {
            font-family: system-ui, "Segoe UI", Roboto, sans-serif;
            /* [FIX] align-items:center rognait la carte quand elle dépassait la hauteur de
               l'écran (cas du formulaire d'inscription) : le haut devenait inatteignable et
               les liens du bas passaient sous la ligne de flottaison. Avec flex-start et une
               marge automatique sur la carte, elle est centrée s'il y a la place, et sinon
               la page défile normalement sans rien rogner. */
            min-height:100vh; display:flex; align-items:flex-start; justify-content:center;
            padding:24px; color:var(--encre);
            /* Fond élégant : dégradé profond animé très lentement */
            background: linear-gradient(135deg, var(--fond-1), var(--fond-2) 55%, var(--fond-3));
            background-size: 220% 220%;
            animation: derive 18s ease infinite;                        /* Animation légère du fond */
        }
        @keyframes derive { 0%,100%{background-position:0% 0%} 50%{background-position:100% 100%} }

        /* Halos décoratifs flous derrière la carte (effet de profondeur) */
        .halo { position:fixed; border-radius:50%; filter:blur(90px); opacity:.35; pointer-events:none; z-index:1; }
        .halo.un   { width:380px; height:380px; background:#2fb3c9; top:-90px; left:-90px; }
        .halo.deux { width:300px; height:300px; background:#7ad0a5; bottom:-80px; right:-60px; }

        /* ================================================================
           [IDENTITÉ] Décor de fond : RÉSEAU DE FIBRES OPTIQUES.
           Filaments translucides au cœur lumineux qui se dispersent de façon
           organique, sur un fond bleu nuit tirant vers le noir. Un paquet de
           données lumineux parcourt la fibre principale en laissant une traînée.
           Profondeur de champ (certains filaments sont flous), particules
           ambiantes, éclairage diffus. Purement décoratif (aria-hidden).
           ================================================================ */
        .fond-fibres {
            position:fixed; inset:0; z-index:0; overflow:hidden; pointer-events:none;
            /* Dégradé radial profond : bleu nuit au centre, noir sur les bords */
            background: radial-gradient(ellipse 92% 84% at 50% 42%,
                        #0b1c3a 0%, #071228 42%, #020714 76%, #000205 100%);
        }
        .fond-fibres svg { width:100%; height:100%; display:block; }

        /* Particules ambiantes : poussière lumineuse en suspension */
        .particule { animation: derive-particule 15s ease-in-out infinite alternate; }
        @keyframes derive-particule {
            0%   { transform:translate(0,0);       opacity:.20; }
            50%  {                                 opacity:.70; }
            100% { transform:translate(12px,-24px); opacity:.28; }
        }

        /* ================================================================
           [IDENTITÉ] Photographie de SALLE SERVEURS en arrière-plan.
           Affichée seulement si le fichier existe (condition dans le corps de page) ;
           sinon le schéma réseau vectoriel ci-dessus prend le relais.
           Traitement : cadrage plein écran, zoom très lent (l'image respire),
           teinte de la palette de l'application et vignettage pour la lisibilité.
           ================================================================ */
        .photo-salle {
            position:fixed; inset:0; z-index:0; pointer-events:none;
            background-position:center; background-size:cover; background-repeat:no-repeat;
            animation: zoom-lent 46s ease-in-out infinite alternate;     /* Respiration cinématographique */
        }
        @keyframes zoom-lent { from{transform:scale(1)} to{transform:scale(1.09)} }

        /* Teinte : ramène la photo dans le bleu-pétrole de l'application */
        .teinte {
            position:fixed; inset:0; z-index:0; pointer-events:none;
            background: linear-gradient(135deg,
                        rgba(15,106,125,.58), rgba(9,38,52,.74) 55%, rgba(11,79,94,.62));
        }
        /* Vignettage : assombrit les bords, concentre le regard au centre */
        .vignettage {
            position:fixed; inset:0; z-index:0; pointer-events:none;
            background: radial-gradient(ellipse 78% 78% at 50% 45%, transparent 40%, rgba(2,10,16,.72) 100%);
        }

        /* Voile radial : assombrit le centre pour que la carte reste parfaitement lisible */
        .voile { position:fixed; inset:0; z-index:1; pointer-events:none;
                 background: radial-gradient(ellipse 600px 500px at 50% 50%,
                             rgba(3,14,20,.70) 0%, rgba(3,14,20,.34) 46%, transparent 74%); }

        /* [ACCESSIBILITÉ] Aucun mouvement si l'utilisateur le demande dans son système. */
        @media (prefers-reduced-motion: reduce) {
            body, .particule, .carte-auth, .photo-salle { animation:none !important; }
            .comete { display:none; }                    /* Le paquet de données cesse de circuler */
        }

        /* ---------- Carte centrale moderne ---------- */
        .carte-auth {
            position:relative; z-index:2;                               /* Au-dessus du décor réseau */
            width:100%; max-width:440px; background:var(--carte);
            border:1px solid var(--bord); border-radius:18px; padding:34px 32px;
            box-shadow: 0 24px 60px rgba(0,0,0,.35);                    /* Profondeur marquée */
            animation: entree .45s ease both;                           /* Apparition en douceur */
        }
        @keyframes entree { from{opacity:0; transform:translateY(14px)} to{opacity:1; transform:none} }

        .logo { display:flex; align-items:center; gap:10px; margin-bottom:6px; }
        .logo .pastille {                                               /* Pastille du logo */
            width:40px; height:40px; border-radius:12px; background:var(--accent);  /* Repli si l'image ne charge pas */
            display:flex; align-items:center; justify-content:center; color:#fff; overflow:hidden;
        }
        .logo .pastille img { width:100%; height:100%; object-fit:cover; }
        .logo b { font-size:1.15rem; letter-spacing:.1em; }
        .logo small { display:block; color:var(--encre-2); font-size:.78rem; letter-spacing:0; }
        h1 { font-size:1.25rem; margin:16px 0 4px; }
        .sous-titre { color:var(--encre-2); font-size:.88rem; margin-bottom:20px; }

        /* ---------- Champs avec icônes ---------- */
        .champ { margin-bottom:15px; }
        .champ label { display:block; font-size:.8rem; font-weight:600; margin-bottom:6px; }
        .enveloppe { position:relative; }                               /* Conteneur icône + champ */
        .enveloppe > svg.gauche {                                       /* Icône décorative à gauche */
            position:absolute; left:13px; top:50%; transform:translateY(-50%); color:var(--encre-2);
        }
        .champ input {
            width:100%; padding:11px 42px 11px 40px;                    /* Espace pour les deux icônes */
            border:1px solid var(--bord); border-radius:10px;
            background:var(--champ-fond); color:var(--encre); font:inherit; font-size:.92rem;
            transition: border-color .2s, box-shadow .2s;               /* Focus fluide */
        }
        .champ input:focus { outline:none; border-color:var(--accent); box-shadow:0 0 0 4px var(--accent-doux); }
        /* [FIX] Microsoft Edge ajoute automatiquement sa propre icône "œil" sur les champs
           password : sans ceci, elle se superposait à notre bouton .btn-oeil (deux icônes visibles). */
        .champ input[type="password"]::-ms-reveal,
        .champ input[type="password"]::-ms-clear { display:none; }
        .erreur { color:var(--alerte); font-size:.8rem; margin-top:5px; }
        .flash { padding:11px 14px; border-radius:10px; margin-bottom:16px;
                 background:var(--ok-fond); color:var(--ok); font-size:.88rem; }

        /* ---------- [ŒIL] Bouton afficher/masquer le mot de passe ---------- */
        .btn-oeil {
            position:absolute; right:8px; top:50%; transform:translateY(-50%);
            border:none; background:transparent; cursor:pointer; padding:6px;
            color:var(--encre-2); border-radius:8px; display:flex;
            transition: color .2s, transform .2s;                        /* Animation fluide */
        }
        .btn-oeil:hover { color:var(--accent); transform:translateY(-50%) scale(1.12); }
        .btn-oeil svg { transition: opacity .18s ease; }                 /* Fondu entre les deux icônes */

        /* ---------- Bouton principal moderne ---------- */
        .btn-principal {
            width:100%; padding:12px; border:none; border-radius:10px; cursor:pointer;
            background: linear-gradient(120deg, var(--accent), var(--accent-fonce));
            color:#fff; font:inherit; font-weight:600; font-size:.95rem;
            transition: transform .15s, box-shadow .2s;
        }
        .btn-principal:hover { transform:translateY(-1px); box-shadow:0 10px 24px rgba(0,0,0,.25); }
        .btn-principal:active { transform:none; }

        .liens { margin-top:18px; display:flex; justify-content:space-between; gap:10px; flex-wrap:wrap; font-size:.85rem; }
        .liens a { color:var(--accent); text-decoration:none; transition: opacity .15s; }
        .liens a:hover { opacity:.75; text-decoration:underline; }

        .case { display:flex; align-items:center; gap:8px; font-size:.85rem; color:var(--encre-2); }
        .case input { width:auto; }

        /* Bascule de thème discrète en haut à droite */
        .btn-theme { position:fixed; top:18px; right:18px; width:40px; height:40px; border-radius:999px;
            border:1px solid rgba(255,255,255,.35); background:rgba(255,255,255,.12); color:#fff;
            cursor:pointer; display:flex; align-items:center; justify-content:center; backdrop-filter: blur(6px); }
        html[data-theme="clair"] .icone-soleil { display:none; }
        html[data-theme="sombre"] .icone-lune  { display:none; }

        /* ================================================================
           [HARMONISATION] Accord de la carte et des champs avec le décor de
           fibres optiques : verre dépoli (glassmorphism), bordures teintées
           cyan et halo lumineux. Ce bloc est placé en dernier pour primer sur
           les règles génériques ci-dessus.
           ================================================================ */
        .carte-auth {
            /* Centrage vertical par marge automatique : compagnon du fix align-items ci-dessus */
            margin:auto;
            background: rgba(11, 24, 44, .60);                     /* Verre sombre translucide */
            -webkit-backdrop-filter: blur(20px) saturate(150%);
            backdrop-filter: blur(20px) saturate(150%);            /* Le décor transparaît, flouté */
            border:1px solid rgba(125, 211, 252, .20);             /* Liseré cyan très discret */
            box-shadow:
                0 26px 70px rgba(0,0,0,.55),                       /* Ombre portée profonde */
                0 0 60px rgba(34, 211, 238, .10),                  /* Halo cyan diffus */
                inset 0 1px 0 rgba(255,255,255,.07);               /* Reflet supérieur, effet verre */
        }
        /* En thème clair, le verre devient dépoli clair au lieu de blanc opaque */
        html[data-theme="clair"] .carte-auth {
            background: rgba(255,255,255,.88);
            border-color: rgba(255,255,255,.65);
            box-shadow: 0 26px 70px rgba(0,0,0,.45), 0 0 60px rgba(34,211,238,.12);
        }

        /* ---------- Champs de saisie assortis ---------- */
        .champ input {
            background: rgba(255,255,255,.05);                     /* Translucide, pas opaque */
            border-color: rgba(148, 197, 220, .22);
        }
        html[data-theme="clair"] .champ input {
            background: rgba(255,255,255,.78);
            border-color: rgba(16, 60, 80, .16);
        }
        .champ input::placeholder { color: rgba(148, 180, 200, .55); }
        /* Mise au point : liseré et halo cyan, en écho aux fibres */
        .champ input:focus {
            border-color:#22d3ee;
            box-shadow: 0 0 0 4px rgba(34, 211, 238, .16);
        }

        /* ---------- Bouton principal : dégradé cyan → indigo du décor ---------- */
        .btn-principal {
            background: linear-gradient(120deg, #22d3ee, #4f46e5);
            box-shadow: 0 8px 24px rgba(34, 211, 238, .18);
        }
        .btn-principal:hover { box-shadow: 0 12px 30px rgba(79, 70, 229, .32); }

        /* ---------- Liens et icônes accordés ---------- */
        .liens a { color:#67e8f9; }
        html[data-theme="clair"] .liens a { color:#0e7490; }        /* Cyan foncé, lisible sur verre clair */
        .btn-oeil:hover { color:#22d3ee; }
        .logo .pastille { box-shadow: 0 0 22px rgba(34, 211, 238, .28); }  /* Le logo capte la lumière */

        /* La bascule de thème adopte le même verre dépoli */
        .btn-theme { border-color: rgba(125, 211, 252, .28); background: rgba(11, 24, 44, .45); }
    </style>
</head>
<body>
    {{-- ================================================================
         [IDENTITÉ] Décor de fond : RÉSEAU DE FIBRES OPTIQUES.
         Chaque filament est tracé trois fois : un halo diffus très large, une
         gaine translucide, puis un cœur lumineux fin — ce qui donne l'effet
         « tube de verre éclairé de l'intérieur ». Les filaments du fond sont
         volontairement flous (profondeur de champ), ceux du premier plan nets.
         Un paquet de données parcourt la fibre principale, suivi d'une traînée.
         ================================================================ --}}
    <div class="fond-fibres" aria-hidden="true">
        <svg viewBox="0 0 1440 900" preserveAspectRatio="xMidYMid slice">
            <defs>
                {{-- Diffusion lumineuse : le trait rayonne au lieu d'être un simple filet --}}
                <filter id="rayonnement" x="-40%" y="-40%" width="180%" height="180%">
                    <feGaussianBlur stdDeviation="7"/>
                </filter>
                <filter id="rayonnement-doux" x="-40%" y="-40%" width="180%" height="180%">
                    <feGaussianBlur stdDeviation="3"/>
                </filter>
                {{-- Profondeur de champ : les filaments les plus lointains sont flous --}}
                <filter id="profondeur" x="-30%" y="-30%" width="160%" height="160%">
                    <feGaussianBlur stdDeviation="5"/>
                </filter>
                {{-- Halo intense pour la tête du paquet de données --}}
                <filter id="halo-paquet" x="-300%" y="-300%" width="700%" height="700%">
                    <feGaussianBlur stdDeviation="5" result="flou"/>
                    <feMerge><feMergeNode in="flou"/><feMergeNode in="SourceGraphic"/></feMerge>
                </filter>

                {{-- Tracés des fibres : réutilisés pour le halo, la gaine et le cœur --}}
                <path id="f1"  d="M-60,190 C 240,110 430,300 660,238 S 1090,110 1500,214" fill="none"/>
                <path id="f2"  d="M-60,430 C 250,384 380,566 700,520 S 1120,414 1500,486" fill="none"/>
                <path id="f3"  d="M-60,706 C 240,656 470,824 764,758 S 1150,652 1500,724" fill="none"/>
                <path id="f4"  d="M116,-60 C 206,220 56,428 224,664 S 384,884 300,980" fill="none"/>
                <path id="f5"  d="M1186,-60 C 1120,206 1306,382 1198,626 S 1074,864 1164,980" fill="none"/>
                <path id="f6"  d="M-60,66 C 300,206 500,58 828,182 S 1206,326 1500,116" fill="none"/>
                <path id="f7"  d="M-60,866 C 300,762 526,904 866,822 S 1186,900 1500,844" fill="none"/>
                <path id="f8"  d="M296,-60 C 424,244 706,306 906,142 S 1244,58 1500,306" fill="none"/>
                <path id="f9"  d="M-60,306 C 186,466 118,706 386,846" fill="none"/>
                <path id="f10" d="M1500,566 C 1244,704 1306,884 1084,980" fill="none"/>
                <path id="f11" d="M-60,626 C 180,600 300,700 480,660" fill="none"/>
                <path id="f12" d="M980,-60 C 1040,180 940,300 1060,470" fill="none"/>
                {{-- Fibre principale : celle que parcourt le paquet de données --}}
                <path id="fibre-maitresse"
                      d="M-70,524 C 300,458 520,296 786,340 C 1046,384 1200,566 1510,498" fill="none"/>
            </defs>

            {{-- ---------- Plan lointain : filaments flous (profondeur de champ) ---------- --}}
            <g filter="url(#profondeur)" opacity=".5">
                <use href="#f9"  stroke="#3b82f6" stroke-width="7"/>
                <use href="#f10" stroke="#7c3aed" stroke-width="8"/>
                <use href="#f11" stroke="#22d3ee" stroke-width="6"/>
                <use href="#f12" stroke="#d946ef" stroke-width="6"/>
                <use href="#f7"  stroke="#2563eb" stroke-width="7"/>
            </g>

            {{-- ---------- Halos diffus des fibres du premier plan ---------- --}}
            <g filter="url(#rayonnement)" opacity=".55">
                <use href="#f1" stroke="#22d3ee" stroke-width="9"/>
                <use href="#f2" stroke="#3b82f6" stroke-width="10"/>
                <use href="#f3" stroke="#7c3aed" stroke-width="9"/>
                <use href="#f4" stroke="#22d3ee" stroke-width="7"/>
                <use href="#f5" stroke="#8b5cf6" stroke-width="8"/>
                <use href="#f6" stroke="#2563eb" stroke-width="7"/>
                <use href="#f8" stroke="#d946ef" stroke-width="6"/>
                <use href="#fibre-maitresse" stroke="#22d3ee" stroke-width="11"/>
            </g>

            {{-- ---------- Gaines translucides (l'aspect « tube de verre ») ---------- --}}
            <g opacity=".26" filter="url(#rayonnement-doux)">
                <use href="#f1" stroke="#7dd3fc" stroke-width="3.4"/>
                <use href="#f2" stroke="#93c5fd" stroke-width="3.6"/>
                <use href="#f3" stroke="#c4b5fd" stroke-width="3.4"/>
                <use href="#f4" stroke="#7dd3fc" stroke-width="2.8"/>
                <use href="#f5" stroke="#c4b5fd" stroke-width="3"/>
                <use href="#f6" stroke="#93c5fd" stroke-width="2.8"/>
                <use href="#f8" stroke="#f0abfc" stroke-width="2.4"/>
                <use href="#fibre-maitresse" stroke="#a5f3fc" stroke-width="4"/>
            </g>

            {{-- ---------- Cœurs lumineux : le trait fin et net au centre de chaque fibre ---------- --}}
            <g opacity=".9">
                <use href="#f1" stroke="#e0fbff" stroke-width="1.1"/>
                <use href="#f2" stroke="#dbeafe" stroke-width="1.1"/>
                <use href="#f3" stroke="#ede9fe" stroke-width="1"/>
                <use href="#f4" stroke="#e0fbff" stroke-width=".9"/>
                <use href="#f5" stroke="#ede9fe" stroke-width=".9"/>
                <use href="#f6" stroke="#dbeafe" stroke-width=".9"/>
                <use href="#f8" stroke="#fae8ff" stroke-width=".8"/>
                <use href="#fibre-maitresse" stroke="#f0feff" stroke-width="1.4"/>
            </g>

            {{-- ---------- Paquet de données : tête nette + traînée qui s'estompe ----------
                 Les points partagent le même tracé et la même durée, décalés dans le temps :
                 ils s'échelonnent donc parfaitement le long de la courbe. --}}
            <g class="comete">
                <circle r="1.4" fill="#67e8f9" opacity=".14">
                    <animateMotion dur="9s" begin="-0.42s" repeatCount="indefinite"><mpath href="#fibre-maitresse"/></animateMotion>
                </circle>
                <circle r="1.8" fill="#67e8f9" opacity=".22">
                    <animateMotion dur="9s" begin="-0.35s" repeatCount="indefinite"><mpath href="#fibre-maitresse"/></animateMotion>
                </circle>
                <circle r="2.2" fill="#a5f3fc" opacity=".34">
                    <animateMotion dur="9s" begin="-0.28s" repeatCount="indefinite"><mpath href="#fibre-maitresse"/></animateMotion>
                </circle>
                <circle r="2.6" fill="#a5f3fc" opacity=".48">
                    <animateMotion dur="9s" begin="-0.21s" repeatCount="indefinite"><mpath href="#fibre-maitresse"/></animateMotion>
                </circle>
                <circle r="3" fill="#cffafe" opacity=".64">
                    <animateMotion dur="9s" begin="-0.14s" repeatCount="indefinite"><mpath href="#fibre-maitresse"/></animateMotion>
                </circle>
                <circle r="3.4" fill="#ecfeff" opacity=".82">
                    <animateMotion dur="9s" begin="-0.07s" repeatCount="indefinite"><mpath href="#fibre-maitresse"/></animateMotion>
                </circle>
                {{-- Tête du paquet : nette, avec un halo intense --}}
                <circle r="4" fill="#ffffff" filter="url(#halo-paquet)">
                    <animateMotion dur="9s" repeatCount="indefinite"><mpath href="#fibre-maitresse"/></animateMotion>
                </circle>
            </g>

            {{-- Deuxième paquet : fibre du haut, teinte bleu néon. Plus petit et plus lent
                 que le principal, pour rester secondaire dans la composition. --}}
            <g class="comete">
                <circle r="1.2" fill="#60a5fa" opacity=".12">
                    <animateMotion dur="13s" begin="-0.36s" repeatCount="indefinite"><mpath href="#f6"/></animateMotion>
                </circle>
                <circle r="1.6" fill="#60a5fa" opacity=".20">
                    <animateMotion dur="13s" begin="-0.30s" repeatCount="indefinite"><mpath href="#f6"/></animateMotion>
                </circle>
                <circle r="2" fill="#93c5fd" opacity=".32">
                    <animateMotion dur="13s" begin="-0.24s" repeatCount="indefinite"><mpath href="#f6"/></animateMotion>
                </circle>
                <circle r="2.4" fill="#bfdbfe" opacity=".46">
                    <animateMotion dur="13s" begin="-0.18s" repeatCount="indefinite"><mpath href="#f6"/></animateMotion>
                </circle>
                <circle r="2.7" fill="#dbeafe" opacity=".66">
                    <animateMotion dur="13s" begin="-0.09s" repeatCount="indefinite"><mpath href="#f6"/></animateMotion>
                </circle>
                <circle r="3.2" fill="#eff6ff" filter="url(#halo-paquet)">
                    <animateMotion dur="13s" repeatCount="indefinite"><mpath href="#f6"/></animateMotion>
                </circle>
            </g>

            {{-- Troisième paquet : fibre du bas, teinte violette. Décalé dans le temps
                 (begin) pour qu'il ne parte jamais en même temps que les autres. --}}
            <g class="comete">
                <circle r="1.2" fill="#a78bfa" opacity=".12">
                    <animateMotion dur="11s" begin="-3.36s" repeatCount="indefinite"><mpath href="#f3"/></animateMotion>
                </circle>
                <circle r="1.6" fill="#a78bfa" opacity=".20">
                    <animateMotion dur="11s" begin="-3.30s" repeatCount="indefinite"><mpath href="#f3"/></animateMotion>
                </circle>
                <circle r="2" fill="#c4b5fd" opacity=".32">
                    <animateMotion dur="11s" begin="-3.24s" repeatCount="indefinite"><mpath href="#f3"/></animateMotion>
                </circle>
                <circle r="2.4" fill="#ddd6fe" opacity=".46">
                    <animateMotion dur="11s" begin="-3.18s" repeatCount="indefinite"><mpath href="#f3"/></animateMotion>
                </circle>
                <circle r="2.7" fill="#ede9fe" opacity=".66">
                    <animateMotion dur="11s" begin="-3.09s" repeatCount="indefinite"><mpath href="#f3"/></animateMotion>
                </circle>
                <circle r="3.2" fill="#f5f3ff" filter="url(#halo-paquet)">
                    <animateMotion dur="11s" begin="-3s" repeatCount="indefinite"><mpath href="#f3"/></animateMotion>
                </circle>
            </g>

            {{-- ---------- Particules ambiantes en suspension ---------- --}}
            <g fill="#a5f3fc" filter="url(#rayonnement-doux)">
                <circle class="particule" cx="180"  cy="250" r="1.8" style="animation-delay:-1s"/>
                <circle class="particule" cx="360"  cy="640" r="1.4" style="animation-delay:-4s"/>
                <circle class="particule" cx="620"  cy="120" r="1.6" style="animation-delay:-7s"/>
                <circle class="particule" cx="880"  cy="760" r="1.3" style="animation-delay:-2s"/>
                <circle class="particule" cx="1120" cy="200" r="1.9" style="animation-delay:-9s"/>
                <circle class="particule" cx="1310" cy="560" r="1.5" style="animation-delay:-5s"/>
                <circle class="particule" cx="240"  cy="820" r="1.2" style="animation-delay:-11s"/>
                <circle class="particule" cx="1020" cy="430" r="1.4" style="animation-delay:-3s"/>
                <circle class="particule" cx="520"  cy="380" r="1.1" style="animation-delay:-8s"/>
                <circle class="particule" cx="1400" cy="810" r="1.6" style="animation-delay:-6s"/>
                <circle class="particule" cx="80"   cy="520" r="1.3" style="animation-delay:-10s"/>
                <circle class="particule" cx="760"  cy="880" r="1.5" style="animation-delay:-12s"/>
            </g>
        </svg>
    </div>

    {{-- ================================================================
         [IDENTITÉ] Photo de salle serveurs, si elle a été déposée dans
         public/images/. Plusieurs noms de fichiers sont acceptés (jpg, jpeg,
         png, webp) : le premier trouvé est utilisé. Tant qu'aucune photo n'est
         présente, c'est le schéma réseau vectoriel ci-dessus qui reste visible.
         ================================================================ --}}
    @php
        $photoFond = null;
        foreach (['salle-serveurs.jpg', 'salle-serveurs.jpeg', 'salle-serveurs.png', 'salle-serveurs.webp'] as $nomFichier) {
            if (is_file(public_path('images/' . $nomFichier))) {         // Premier fichier existant
                $photoFond = asset('images/' . $nomFichier);
                break;
            }
        }
    @endphp
    @if($photoFond)
        <div class="photo-salle" style="background-image:url('{{ $photoFond }}')" aria-hidden="true"></div>
        <div class="teinte" aria-hidden="true"></div>                   {{-- Harmonisation colorimétrique --}}
        <div class="vignettage" aria-hidden="true"></div>               {{-- Bords assombris --}}
    @endif

    <div class="voile"></div>                                           {{-- Lisibilité de la carte --}}
    <div class="halo un"></div><div class="halo deux"></div>            {{-- Halos décoratifs --}}

    {{-- [DARK MODE] Bascule clair/sombre également disponible sur les pages publiques --}}
    <button type="button" class="btn-theme" onclick="basculerTheme()" aria-label="Changer de thème">
        <svg class="icone-lune"   width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8Z"/></svg>
        <svg class="icone-soleil" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2M4.9 4.9l1.4 1.4m11.4 11.4 1.4 1.4M2 12h2m16 0h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
    </button>

    <div class="carte-auth">
        {{-- En-tête commun : logo de l'application --}}
        <div class="logo">
            <div class="pastille">
                <img src="{{ asset('images/moov africa logo3.jpg') }}" alt="Logo">
            </div>
            <div><b>IPAM</b><small>Gestion de ressources réseau</small></div>
        </div>

        @if(session('succes'))                              {{-- Message de succès éventuel --}}
            <div class="flash" data-flash>{{ session('succes') }}</div>
        @endif

        @yield('contenu')                                   {{-- Contenu propre à chaque page --}}
    </div>

    <script>
        // [DARK MODE] Bascule + mémorisation (même mécanisme que dans l'application)
        function basculerTheme() {
            const html = document.documentElement;
            const nouveau = html.dataset.theme === 'sombre' ? 'clair' : 'sombre';
            html.dataset.theme = nouveau;                               // Changement instantané
            localStorage.setItem('theme-ipam', nouveau);                // Mémorisation du choix
        }

        // [ŒIL] Affiche/masque un champ mot de passe avec un fondu entre les deux icônes
        function basculerOeil(bouton) {
            const champ = bouton.parentElement.querySelector('input');  // Champ associé au bouton
            const ouvert = champ.type === 'password';                   // Va-t-on l'afficher ?
            champ.type = ouvert ? 'text' : 'password';                  // Bascule du type du champ
            // Fondu : on échange les deux icônes (œil ouvert / œil barré)
            bouton.querySelector('.oeil-ouvert').style.display = ouvert ? 'none' : 'block';
            bouton.querySelector('.oeil-barre').style.display  = ouvert ? 'block' : 'none';
        }

        // [DESIGN] Le message flash de succès se referme tout seul après 6 secondes.
        document.querySelectorAll('[data-flash]').forEach(function (message) {
            setTimeout(function () {
                message.style.transition = 'opacity .4s ease, transform .4s ease';
                message.style.opacity = '0';
                message.style.transform = 'translateY(-6px)';
                setTimeout(function () { message.remove(); }, 400);
            }, 6000);
        });
    </script>

    {{-- [CHARGEUR] Voile plein écran + mini-chargeur dans les boutons d'envoi --}}
    @include('composants.chargeur-global')
</body>
</html>
