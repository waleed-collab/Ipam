{{-- Layout principal : structure commune à toutes les pages (menu latéral + barre supérieure + contenu). --}}
{{-- [DESIGN] Intègre le MODE CLAIR / MODE SOMBRE (mémorisé) et la RECHERCHE GLOBALE (personnel). --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">                                              {{-- Encodage des caractères --}}
    <meta name="viewport" content="width=device-width, initial-scale=1">{{-- Rendu correct sur mobile --}}
    <title>@yield('titre', 'IPAM') — Gestion de Ressources IPAM</title> {{-- Titre défini par chaque page --}}

    {{-- [DARK MODE] Script exécuté AVANT le rendu : applique le thème mémorisé sans "flash" visuel --}}
    <script>
        // Lecture du choix mémorisé dans le navigateur (localStorage) ; "clair" par défaut
        document.documentElement.dataset.theme = localStorage.getItem('theme-ipam') || 'clair';
    </script>

    <style>
        /* ============ PALETTE — MODE CLAIR (par défaut) ============ */
        :root, html[data-theme="clair"] {
            --fond: #f5f6f8;            /* Fond général clair */
            --carte: #ffffff;           /* Fond des cartes/tableaux */
            --encre: #16232e;           /* Texte principal */
            --encre-2: #5b6b78;         /* Texte secondaire */
            --accent: #0f6a7d;          /* Accent bleu-pétrole (boutons, liens) */
            --accent-fonce: #0b4f5e;    /* Accent au survol */
            --accent-doux: #e3f0f3;     /* Accent très clair (fonds de badges/graphes) */
            --lateral: #101b24;         /* Fond du menu latéral */
            --ok: #1a7f4b;              /* Vert : succès / actif / disponible */
            --ok-fond: #e2f3e9;         /* Fond des badges verts */
            --alerte: #b3261e;          /* Rouge : suppression / inactif */
            --alerte-fond: #fbe5e4;     /* Fond des badges rouges */
            --orange: #b26a00;          /* Orange : avertissements */
            --orange-fond: #fdf1dc;     /* Fond des avertissements */
            --neutre-fond: #eceff2;     /* Fond des badges neutres */
            --bord: #e2e6ea;            /* Bordures discrètes */
            --entete-table: #fafbfc;    /* Fond des en-têtes de tableau */
            --champ-fond: #ffffff;      /* Fond des champs de formulaire */
        }
        /* ============ PALETTE — MODE SOMBRE (cohérente, activée via data-theme) ============ */
        html[data-theme="sombre"] {
            --fond: #0d1620;            /* Fond général sombre */
            --carte: #16222e;           /* Cartes légèrement plus claires */
            --encre: #e6edf3;           /* Texte principal clair */
            --encre-2: #94a7b8;         /* Texte secondaire */
            --accent: #37a3b8;          /* Accent éclairci pour le contraste */
            --accent-fonce: #4db8cc;    /* Survol */
            --accent-doux: #12303a;     /* Accent très sombre */
            --lateral: #0a111a;         /* Menu latéral encore plus sombre */
            --ok: #4cc98a;              --ok-fond: #10301f;
            --alerte: #ff8a80;          --alerte-fond: #3a1512;
            --orange: #ffbe5c;          --orange-fond: #33260f;
            --neutre-fond: #1d2b38;
            --bord: #243342;            /* Bordures adoucies */
            --entete-table: #121d28;
            --champ-fond: #0f1a24;      /* Champs sombres */
        }

        * { box-sizing: border-box; margin: 0; }                        /* Remise à zéro simple */
        body {
            font-family: system-ui, "Segoe UI", Roboto, sans-serif;     /* Police système lisible */
            background: var(--fond); color: var(--encre);
            display: flex; min-height: 100vh;                           /* Menu latéral + contenu côte à côte */
            transition: background .25s, color .25s;                    /* [DARK MODE] Bascule fluide */
        }
        code, .mono { font-family: ui-monospace, "Cascadia Mono", Consolas, monospace; } /* IP/ASN en mono */

        /* ---------- [UNIFORMITÉ] Barres de défilement ----------
           Un seul design fin et discret pour TOUTES les zones défilantes (contenu principal,
           menu latéral, tableaux...) au lieu de la barre système épaisse avec flèches. */
        * { scrollbar-width: thin; scrollbar-color: var(--bord) transparent; }  /* Firefox */
        *::-webkit-scrollbar { width: 8px; height: 8px; }               /* Chrome / Edge : finesse */
        *::-webkit-scrollbar-track { background: transparent; }         /* Piste invisible */
        *::-webkit-scrollbar-thumb { background: var(--bord); border-radius: 999px; }
        *::-webkit-scrollbar-thumb:hover { background: var(--encre-2); }
        *::-webkit-scrollbar-button { display: none; }                  /* Supprime les flèches haut/bas */
        /* Le menu latéral est toujours sombre : sa barre reprend les tons du panneau, pas du thème */
        aside nav { scrollbar-color: #2a3b4a transparent; }
        aside nav::-webkit-scrollbar-thumb { background: #2a3b4a; }
        aside nav::-webkit-scrollbar-thumb:hover { background: #3b5063; }

        /* ---------- [UNIFORMITÉ] Listes déroulantes ----------
           Mode "select personnalisable" (navigateurs modernes) : le menu d'options s'ouvre
           TOUJOURS SOUS le champ (jamais au-dessus) et adopte l'habillage du thème.
           Les navigateurs sans prise en charge ignorent ces règles => comportement natif conservé. */
        select, ::picker(select) { appearance: base-select; }
        ::picker(select) {
            position-area: block-end span-inline-end;                   /* Ancré sous le champ, aligné à gauche */
            position-try-fallbacks: none;                               /* Interdit le repli au-dessus du champ */
            margin-top: 4px; max-height: 280px; overflow-y: auto;
            background: var(--carte); color: var(--encre);
            border: 1px solid var(--bord); border-radius: 8px; padding: 4px;
            box-shadow: 0 10px 28px rgba(16,24,32,.22);
        }
        select option { padding: 7px 10px; border-radius: 6px; font-size: .85rem; }
        select option:hover, select option:focus { background: var(--accent-doux); color: var(--accent); }
        select option:checked { font-weight: 600; }
        select::picker-icon { color: var(--encre-2); }                  /* Chevron du champ */

        /* ---------- Menu latéral ---------- */
        aside {
            width: 264px; background: var(--lateral); color: #cdd8e0;
            display: flex; flex-direction: column;                      /* Logo / nav (scrollable) / profil empilés */
            flex-shrink: 0; position: sticky; top: 0; align-self: flex-start;
            /* [FIX] Sans ceci, un contenu principal long (tableau de bord) étirait <aside> à sa hauteur totale
               (flex-stretch par défaut) : la carte profil / déconnexion se retrouvait hors écran tant qu'on
               n'avait pas fait défiler toute la page. Hauteur fixée au viewport + collée en haut : le menu
               reste toujours visible et son propre <nav> défile s'il dépasse. */
            height: 100vh;
            background-image: radial-gradient(circle at 15% 0%, rgba(55,163,184,.16), transparent 55%); /* Léger halo d'accent en haut */
        }
        aside .logo {
            display:flex; align-items:center; gap:11px;
            padding: 22px 20px 20px;
        }
        aside .logo .marque {                                           /* Pastille du logo avant le nom */
            width:36px; height:36px; border-radius:10px; flex-shrink:0;
            background: linear-gradient(135deg, var(--accent), #1b8fa6);  /* Repli si l'image ne charge pas */
            display:flex; align-items:center; justify-content:center; overflow:hidden;
            box-shadow: 0 4px 14px rgba(15,106,125,.45);
        }
        aside .logo .marque img { width:100%; height:100%; object-fit:cover; }
        aside .logo strong { display:block; font-weight:700; letter-spacing:.1em; color:#fff; font-size:.95rem; }
        aside .logo small { display:block; color:#7d93a3; font-weight:400; letter-spacing:0; font-size:.72rem; margin-top:1px; }

        aside nav { flex:1; overflow-y:auto; padding: 4px 12px 16px; }  /* Défile si le menu dépasse la hauteur */
        aside .section {                                                /* Intitulé de groupe de menu */
            display:flex; align-items:center; gap:8px;
            padding: 18px 10px 7px; font-size: .68rem; text-transform: uppercase;
            letter-spacing: .13em; color: #5c7185; font-weight:600;
        }
        aside .section::after { content:''; flex:1; height:1px; background:#1c2b38; }  /* Filet après le libellé */
        aside a {
            display: flex; align-items:center; gap:11px;
            padding: 9px 12px 9px 9px; margin: 2px 0; border-radius: 8px;
            border-left: 3px solid transparent;                          /* [DESIGN] Réservé même inactif : jamais de décalage au survol/actif */
            color: #aebdc9; text-decoration: none; font-size: .87rem; font-weight:500;
            transition: background .15s, color .15s, border-color .15s;
        }
        aside a svg { flex-shrink:0; opacity:.75; transition: opacity .15s; }
        aside a:hover { background: rgba(255,255,255,.06); color: #fff; }
        aside a:hover svg { opacity:1; }
        aside a.actif {                                                 /* Page active : fond dégradé + bordure accent intégrée au bloc */
            background: linear-gradient(90deg, rgba(55,163,184,.22), rgba(55,163,184,.05));
            border-left-color: var(--accent);
            color:#fff;
        }
        aside a.actif svg { opacity:1; color: var(--accent-fonce); }
        html[data-theme="sombre"] aside a.actif svg { color: var(--accent); }

        /* ---------- Carte profil (bas de la sidebar, remplace le simple lien de déconnexion) ---------- */
        aside .profil {
            margin: 8px 12px 16px; padding: 11px 12px; border-radius: 10px;
            background: rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.07);
            display:flex; align-items:center; gap:10px;
        }
        aside .profil .avatar {
            width:34px; height:34px; border-radius:50%; flex-shrink:0;
            background: var(--accent-doux); color: var(--accent-fonce);
            display:flex; align-items:center; justify-content:center;
            font-size:.78rem; font-weight:700;
        }
        aside .profil .infos { flex:1; min-width:0; }
        aside .profil .nom { color:#fff; font-size:.83rem; font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        aside .profil .role { font-size:.72rem; color:#7d93a3; text-transform:capitalize; }
        aside .profil button {
            flex-shrink:0; width:30px; height:30px; border-radius:8px; border:none; cursor:pointer;
            background: transparent; color:#8ba0b0; display:flex; align-items:center; justify-content:center;
            transition: background .15s, color .15s;
        }
        aside .profil button:hover { background: var(--alerte-fond); color: var(--alerte); }

        /* ---------- Zone de contenu ---------- */
        .zone { flex:1; display:flex; flex-direction:column; min-width:0; } /* Barre + contenu empilés */
        main { flex: 1; padding: 24px 36px; max-width: 1280px; width:100%; }
        header.page {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 22px; gap:12px; flex-wrap:wrap;              /* Titre + bouton d'action */
        }
        header.page h1 { font-size: 1.35rem; }
        .sous-titre { color: var(--encre-2); font-size:.88rem; margin-top:4px; }
        /* [DESIGN] Barre d'actions en BAS de page, séparée du contenu par un filet léger.
           Tous les boutons sont groupés à GAUCHE : le retour en premier, suivi des
           éventuelles actions de la fiche (« Modifier »…). */
        .barre-actions {
            display:flex; justify-content:flex-start; gap:8px; flex-wrap:wrap;
            margin-top:24px; padding-top:16px; border-top:1px solid var(--bord);
        }

        /* ---------- [NOUVEAU] Barre supérieure : recherche globale + bascule de thème ---------- */
        .topbar {
            display:flex; align-items:center; gap:14px; padding: 14px 36px;
            background: var(--fond);                        /* [DESIGN] Même fond que la page : rendu uniforme */
            border-bottom: 1px solid var(--bord);            /* [DESIGN] Sépare franchement la barre du contenu */
        }
        .topbar.topbar--sobre {                             /* [DESIGN] Rien à séparer côté client : pas de filet ni de bande visible */
            border-bottom: none; padding-bottom: 6px;
        }
        .topbar form.recherche { flex:1; max-width:520px; position:relative; }
        .topbar form.recherche input {
            width:100%; padding:9px 12px 9px 38px;                      /* Espace pour l'icône loupe */
            border:1px solid var(--bord); border-radius:999px;          /* Champ arrondi moderne */
            background: var(--champ-fond); color: var(--encre); font:inherit; font-size:.88rem;
            transition: box-shadow .2s, border-color .2s;
        }
        .topbar form.recherche input:focus { outline:none; border-color:var(--accent); box-shadow:0 0 0 3px var(--accent-doux); }
        .topbar form.recherche .btn-loupe {                 /* [RECHERCHE] Loupe cliquable = lance la recherche */
            position:absolute; left:6px; top:50%; transform:translateY(-50%);
            border:none; background:transparent; cursor:pointer; padding:6px;
            color:var(--encre-2); display:flex; border-radius:999px; transition: color .15s;
        }
        .topbar form.recherche .btn-loupe:hover { color:var(--accent); }
        .btn-theme {                                                    /* Bouton soleil/lune */
            margin-left:auto; border:1px solid var(--bord); background:var(--champ-fond);
            color:var(--encre); border-radius:999px; width:38px; height:38px; cursor:pointer;
            display:flex; align-items:center; justify-content:center; transition: transform .2s;
        }
        .btn-theme:hover { transform: rotate(18deg); }                  /* Petite animation au survol */
        html[data-theme="clair"] .icone-soleil { display:none; }        /* En clair on propose la lune */
        html[data-theme="sombre"] .icone-lune  { display:none; }        /* En sombre on propose le soleil */

        /* ---------- Boutons ---------- */
        .btn {
            display: inline-block; padding: 8px 14px; border-radius: 6px;
            background: var(--accent); color: #fff; text-decoration: none;
            border: none; cursor: pointer; font-size: .88rem; transition: background .15s;
        }
        .btn:hover { background: var(--accent-fonce); }
        .btn.secondaire { background:var(--neutre-fond); color: var(--encre); }  /* Bouton neutre (Annuler...) */
        .btn.danger { background: var(--alerte); color:#fff; }          /* Bouton de suppression */
        .btn.petit { padding: 4px 10px; font-size: .8rem; }

        /* ---------- Cartes & tableaux ---------- */
        .carte {
            background: var(--carte); border:1px solid var(--bord); border-radius:12px; padding:20px;
            box-shadow: 0 1px 2px rgba(16,24,32,.04);                    /* [DESIGN] Légère profondeur, cohérente avec .stat */
        }
        .carte h2 {                                                     /* Titre de carte avec icône (widgets du dashboard) */
            display:flex; align-items:center; gap:10px; font-size:.95rem; margin-bottom:14px;
        }
        .carte h2 .icone {                                              /* Pastille d'icône devant le titre */
            width:28px; height:28px; border-radius:8px; flex-shrink:0;
            background: var(--accent-doux); color: var(--accent);
            display:flex; align-items:center; justify-content:center;
        }
        table { width:100%; border-collapse: collapse; background: var(--carte);
                border:1px solid var(--bord); border-radius:12px; overflow:hidden;
                box-shadow: 0 1px 2px rgba(16,24,32,.04); }
        th { text-align:left; font-size:.72rem; text-transform:uppercase; letter-spacing:.1em;
             color:var(--encre-2); padding:11px 14px; background:var(--entete-table); border-bottom:1px solid var(--bord); }
        td { padding:11px 14px; border-bottom:1px solid var(--bord); font-size:.9rem; }
        tr:last-child td { border-bottom:none; }
        tbody tr { transition: background .12s; }
        tbody tr:hover td { background: var(--entete-table); }          /* [DESIGN] Retour visuel au survol d'une ligne */
        td .actions { display:flex; gap:8px; }                          /* Boutons Modifier/Supprimer alignés */

        /* [ALIGNEMENT] Champ de saisie intégré dans une cellule de tableau (ligne d'ajout en tfoot) :
           largeur 100 % de SA colonne => chaque champ reste aligné sous son en-tête sans déborder. */
        .champ-tableau { width:100%; box-sizing:border-box; padding:7px 9px; font-size:.85rem;
                         border:1px solid var(--bord); border-radius:6px;
                         background:var(--fond); color:var(--encre); }
        tfoot td { background:var(--entete-table); }                    /* La ligne d'ajout se distingue du contenu */

        /* ---------- Badges d'état ---------- */
        .badge { padding:2px 10px; border-radius:999px; font-size:.75rem; font-weight:600; }
        .badge.vert   { background:var(--ok-fond); color:var(--ok); }         /* actif / disponible */
        .badge.rouge  { background:var(--alerte-fond); color:var(--alerte); } /* inactif */
        .badge.gris   { background:var(--neutre-fond); color:var(--encre-2); }/* utilisée / neutre */
        .badge.orange { background:var(--orange-fond); color:var(--orange); } /* avertissement */
        .badge.accent { background:var(--accent-doux); color:var(--accent); } /* mise en avant (IPv6...) */

        /* ---------- Formulaires ---------- */
        form.fiche { max-width: 560px; }
        .champ { margin-bottom: 16px; }
        .champ label { display:block; font-size:.82rem; font-weight:600; margin-bottom:5px; }
        .champ input, .champ select, .champ textarea {
            width:100%; padding:9px 11px; border:1px solid var(--bord); border-radius:6px;
            font: inherit; background:var(--champ-fond); color:var(--encre);
        }
        .champ input:focus, .champ select:focus, .champ textarea:focus {
            outline: 2px solid var(--accent); border-color: var(--accent);  /* Focus clavier visible */
        }
        .erreur { color: var(--alerte); font-size:.8rem; margin-top:4px; } /* Message d'erreur de champ */

        /* ---------- Messages flash ---------- */
        .flash { padding:11px 16px; border-radius:8px; margin-bottom:18px;
                 background:var(--ok-fond); color:var(--ok); border:1px solid var(--ok); }

        /* ---------- Statistiques (tableau de bord) ---------- */
        /* [MISE EN PAGE] auto-fill + 220px donnait 4 colonnes pour 6 tuiles : la seconde ligne
           n'en contenait que 2, laissant un grand vide à droite. Avec 280px on obtient 3 colonnes,
           soit deux lignes pleines de 3 tuiles — plus aucun espace perdu. */
        .stats { display:grid; grid-template-columns:repeat(auto-fit, minmax(280px,1fr)); gap:14px; }
        .stat  {
            background:var(--carte); border:1px solid var(--bord); border-radius:12px; padding:16px;
            display:flex; align-items:center; gap:14px;                 /* Icône à gauche, chiffre + libellé à droite */
            box-shadow: 0 1px 2px rgba(16,24,32,.04);
            transition: transform .15s, box-shadow .15s; }
        .stat:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(16,24,32,.09); } /* Effet de profondeur */
        .stat .stat-icone {                                             /* Pastille d'icône, même langage que la sidebar */
            width:42px; height:42px; border-radius:11px; flex-shrink:0;
            background: var(--accent-doux); color: var(--accent);
            display:flex; align-items:center; justify-content:center;
        }
        .stat .stat-corps { min-width:0; }
        .stat .valeur { font-size:1.55rem; font-weight:700; color:var(--encre); line-height:1.1; }
        .stat .libelle { color:var(--encre-2); font-size:.8rem; margin-top:3px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }

        /* ---------- [DESIGN] Filtres en pastilles (remplace les liens bruts) ---------- */
        .filtres { display:flex; gap:8px; margin-top:8px; flex-wrap:wrap; }
        .filtres a {
            padding:5px 13px; border-radius:999px; font-size:.8rem; text-decoration:none;
            color:var(--encre-2); background:var(--carte); border:1px solid var(--bord);
            transition: background .15s, color .15s, border-color .15s;   /* Survol fluide */
        }
        .filtres a:hover { color:var(--accent); border-color:var(--accent); }
        .filtres a.actif { background:var(--accent); border-color:var(--accent); color:#fff; } /* Filtre en cours */

        /* Liens du contenu harmonisés avec la charte (fini le violet par défaut).
           Les classes .btn et .filtres redéfinissent leur propre couleur (spécificité supérieure). */
        main a { color:var(--accent); }

        /* ---------- [DESIGN] Lignes de liste (alertes, activité récente du tableau de bord) ---------- */
        .ligne-liste {
            display:flex; align-items:flex-start; gap:10px; padding:9px 10px; margin:0 -10px 2px;
            border-radius:8px; border-left:3px solid transparent; transition: background .15s;
        }
        .ligne-liste:last-child { margin-bottom:-8px; }                 /* Compense le padding de .carte en bas de liste */
        .ligne-liste:hover { background: var(--entete-table); }         /* Retour visuel au survol, cohérent avec les tableaux */
        .ligne-liste.gravite-rouge  { border-left-color: var(--alerte); }  /* Bande de gravité, langage déjà utilisé (badges) */
        .ligne-liste.gravite-orange { border-left-color: var(--orange); }
        .ligne-liste.gravite-gris   { border-left-color: var(--bord); }

        /* ---------- [DESIGN] Alertes réseau : cartouches par gravité (tableau de bord) ---------- */
        .compteur-alertes {                                             /* Pastille de comptage dans le titre */
            margin-left:auto; font-size:.72rem; font-weight:700; line-height:1;
            padding:3px 9px; border-radius:999px;
        }
        .compteur-alertes.rouge  { background:var(--alerte-fond); color:var(--alerte); }
        .compteur-alertes.orange { background:var(--orange-fond); color:var(--orange); }
        .compteur-alertes.gris   { background:var(--neutre-fond); color:var(--encre-2); }
        .liste-alertes { display:flex; flex-direction:column; gap:10px; }
        .alerte {                                                       /* Cartouche d'une alerte */
            display:flex; align-items:flex-start; gap:12px;
            padding:12px 14px; border-radius:10px; border-left:4px solid transparent;
        }
        .alerte-icone { flex:none; display:flex; margin-top:1px; }       /* Icône de gravité alignée au texte */
        .alerte-corps { flex:1; min-width:0; }
        .alerte-niveau {                                                /* Libellé « CRITIQUE / ATTENTION / INFO » */
            display:block; font-size:.66rem; font-weight:700;
            text-transform:uppercase; letter-spacing:.05em; margin-bottom:3px;
        }
        .alerte-texte { margin:0; font-size:.86rem; line-height:1.45; color:var(--encre); }
        /* Couleurs par gravité : fond teinté + filet gauche + icône/libellé colorés */
        .alerte.rouge  { background:var(--alerte-fond); border-left-color:var(--alerte); }
        .alerte.rouge  .alerte-icone, .alerte.rouge  .alerte-niveau { color:var(--alerte); }
        .alerte.orange { background:var(--orange-fond); border-left-color:var(--orange); }
        .alerte.orange .alerte-icone, .alerte.orange .alerte-niveau { color:var(--orange); }
        .alerte.gris   { background:var(--neutre-fond); border-left-color:var(--encre-2); }
        .alerte.gris   .alerte-icone, .alerte.gris   .alerte-niveau { color:var(--encre-2); }
        .alerte-vide {                                                  /* État « aucune alerte » (tout est vert) */
            display:flex; align-items:center; gap:12px;
            padding:16px; border-radius:10px; background:var(--ok-fond);
        }
        .alerte-vide-icone { flex:none; color:var(--ok); display:flex; }
        .alerte-vide-titre { font-weight:700; color:var(--ok); font-size:.9rem; }

        /* ---------- [JOURNAL D'AUDIT] Lisibilité de la colonne « Détail » ---------- */
        .detail-audit { max-width:420px; line-height:1.5; }
        .sujet-audit {                                                  /* Sujet de l'action */
            font-family: ui-monospace, "Cascadia Code", Consolas, monospace;
            font-size:.86em; color:var(--accent); font-weight:600;
            background:var(--accent-doux); padding:1px 6px; border-radius:5px;
            white-space:nowrap;
        }
        .nombre-audit { color:var(--encre); font-weight:700; }          /* Décomptes chiffrés */

        /* ---------- [PAGINATION] Style maison, en remplacement de la vue Tailwind ---------- */
        .pagination { display:flex; align-items:center; flex-wrap:wrap; gap:6px; }
        .pagination .page {
            display:inline-flex; align-items:center; justify-content:center;
            min-width:34px; height:34px; padding:0 10px;
            border:1px solid var(--bord); border-radius:8px;
            background:var(--carte); color:var(--encre); text-decoration:none;
            font-size:.85rem; transition: background .15s, border-color .15s;
        }
        .pagination a.page:hover { background:var(--entete-table); border-color:var(--encre-2); }
        .pagination .page.actif {                                       /* Page courante */
            background:var(--accent); border-color:var(--accent); color:#fff; font-weight:600;
        }
        .pagination .page.desactive { opacity:.45; cursor:default; }    /* Bornes atteintes */
        .pagination .page.ellipse { border:none; background:transparent; }
        .pagination-total {                                             /* Repère « 1–25 sur 120 » */
            margin-left:auto; color:var(--encre-2); font-size:.82rem;
        }

        /* ---------- Jauges de progression (bande passante, occupation) ---------- */
        .jauge { height:8px; background:var(--neutre-fond); border-radius:999px; overflow:hidden; }
        .jauge > span { display:block; height:100%; background:var(--accent); border-radius:999px;
                        transition: width .6s ease; }                   /* Remplissage animé */
        .jauge > span.orange { background:var(--orange); }              /* > 75 % */
        .jauge > span.rouge  { background:var(--alerte); }              /* > 90 % */

        @media (max-width: 860px) {
            body{flex-direction:column}
            /* [FIX] En colonne (mobile), le menu ne doit plus se coller ni forcer 100vh : il redevient un bloc normal au-dessus du contenu. */
            aside{width:100%; height:auto; position:static}
            main{padding:18px} .topbar{padding:10px 18px}
        }
    </style>
    @stack('tete') {{-- Permet aux pages d'injecter des scripts/styles (ex : Chart.js du dashboard) --}}
</head>
<body>
    {{-- ================= MENU LATÉRAL ================= --}}
    <aside>
        <div class="logo">
            <div class="marque">
                <img src="{{ asset('images/moov africa logo3.jpg') }}" alt="Logo">
            </div>
            <div><strong>IPAM</strong><small>Gestion de ressources réseau</small></div>
        </div>
        <nav>
            @auth {{-- Le menu ne s'affiche que si un utilisateur est connecté --}}
                @if(auth()->user()->estPersonnel()) {{-- Menu complet : admin & manager --}}
                    <div class="section">Pilotage</div>
                    <a href="{{ route('tableau-de-bord') }}" class="{{ request()->routeIs('tableau-de-bord') ? 'actif' : '' }}">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/></svg>
                        Tableau de bord
                    </a>
                    <div class="section">Réseau</div>
                    <a href="{{ route('operateurs.index') }}"   class="{{ request()->routeIs('operateurs.*') ? 'actif' : '' }}">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="6" rx="1.5"/><rect x="3" y="14" width="18" height="6" rx="1.5"/><circle cx="7" cy="7" r=".6" fill="currentColor"/><circle cx="7" cy="17" r=".6" fill="currentColor"/></svg>
                        Opérateurs externes
                    </a>
                    <a href="{{ route('configuration-as.edit') }}" class="{{ request()->routeIs('configuration-as.*') ? 'actif' : '' }}">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .34 1.87l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.7 1.7 0 0 0-1.87-.34 1.7 1.7 0 0 0-1.04 1.56V21a2 2 0 1 1-4 0v-.09A1.7 1.7 0 0 0 9 19.35a1.7 1.7 0 0 0-1.87.34l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.7 1.7 0 0 0 4.65 15a1.7 1.7 0 0 0-1.56-1.04H3a2 2 0 1 1 0-4h.09A1.7 1.7 0 0 0 4.65 9a1.7 1.7 0 0 0-.34-1.87l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.7 1.7 0 0 0 9 4.65a1.7 1.7 0 0 0 1.04-1.56V3a2 2 0 1 1 4 0v.09A1.7 1.7 0 0 0 15 4.65a1.7 1.7 0 0 0 1.87-.34l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.7 1.7 0 0 0 19.35 9a1.7 1.7 0 0 0 1.56 1.04H21a2 2 0 1 1 0 4h-.09A1.7 1.7 0 0 0 19.4 15Z"/></svg>
                        Ressources Internet
                    </a>
                    <a href="{{ route('sessions-bgp.index') }}" class="{{ request()->routeIs('sessions-bgp.*') ? 'actif' : '' }}">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12h4l2-7 4 14 2-7h8"/></svg>
                        Sessions BGP
                    </a>
                    <div class="section">Ressources</div>
                    <a href="{{ route('technologies.index') }}" class="{{ request()->routeIs('technologies.*') ? 'actif' : '' }}">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5a11 11 0 0 1 14 0"/><path d="M8.3 15.9a6.5 6.5 0 0 1 7.4 0"/><path d="M11.6 19.2a2 2 0 0 1 .8-.2 2 2 0 0 1 .8.2"/><circle cx="12" cy="19.5" r=".6" fill="currentColor"/></svg>
                        Technologies d'accès
                    </a>
                    <a href="{{ route('equipements.index') }}"  class="{{ request()->routeIs('equipements.*') ? 'actif' : '' }}">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="5" width="14" height="14" rx="2"/><rect x="9" y="9" width="6" height="6" rx="1"/><path d="M9 2v3M15 2v3M9 19v3M15 19v3M2 9h3M2 15h3M19 9h3M19 15h3"/></svg>
                        Équipements
                    </a>
                    <a href="{{ route('plages-ip.index') }}"    class="{{ request()->routeIs('plages-ip.*') ? 'actif' : '' }}">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="4" rx="1"/><rect x="3" y="10" width="18" height="4" rx="1"/><rect x="3" y="16" width="18" height="4" rx="1"/><path d="M7 6h.01M7 12h.01M7 18h.01"/></svg>
                        Sous-blocs d'adresses
                    </a>
                    {{-- [CAPACITY PLANNING] Nouvelle entrée : gestion de la bande passante --}}
                    <a href="{{ route('allocations.index') }}"  class="{{ request()->routeIs('allocations.*') ? 'actif' : '' }}">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21a9 9 0 1 1 9-9"/><path d="M12 12 17 7"/><path d="M12 3v2M3 12h2M21 12h-2"/></svg>
                        Bande passante
                    </a>
                    <div class="section">Comptes</div>
                    <a href="{{ route('clients.index') }}"      class="{{ request()->routeIs('clients.*') ? 'actif' : '' }}">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"/><circle cx="10" cy="7" r="4"/><path d="M22.5 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        Clients
                    </a>
                    <div class="section">Sécurité</div>
                    {{-- [AUDIT] Journal des connexions et des modifications (admin & manager) --}}
                    <a href="{{ route('journal-audit.index') }}" class="{{ request()->routeIs('journal-audit.*') ? 'actif' : '' }}">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3 4 6v6c0 4.6 3.2 8.4 8 9 4.8-.6 8-4.4 8-9V6l-8-3Z"/><path d="M12 8v5M12 16h.01"/></svg>
                        Journal d'audit
                    </a>
                @else {{-- Menu réduit : rôle client (lecture seule) --}}
                    <div class="section">Mon espace</div>
                    <a href="{{ route('portail.accueil') }}" class="{{ request()->routeIs('portail.accueil') ? 'actif' : '' }}">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 11.5 12 4l9 7.5"/><path d="M5.5 10v9a1 1 0 0 0 1 1H10v-5.5h4V20h3.5a1 1 0 0 0 1-1v-9"/></svg>
                        Mes ressources
                    </a>
                @endif
        </nav>

        {{-- ---------- Carte profil : identité + déconnexion (remplace le simple lien texte) ---------- --}}
        <div class="profil">
            <div class="avatar">{{ Str::of(auth()->user()->name)->substr(0, 1) }}</div>
            <div class="infos">
                <div class="nom">{{ auth()->user()->name }}</div>
                <div class="role">{{ auth()->user()->role }}</div>
            </div>
            {{-- Déconnexion en POST (protégée par le jeton CSRF) --}}
            <form method="POST" action="{{ route('logout') }}">
                @csrf {{-- Jeton anti-CSRF obligatoire sur tout formulaire --}}
                <button type="submit" title="Se déconnecter" aria-label="Se déconnecter">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg>
                </button>
            </form>
        </div>
            @endauth
    </aside>

    {{-- ================= BARRE SUPÉRIEURE + CONTENU ================= --}}
    <div class="zone">
        {{-- [DESIGN] Chez le personnel, la barre de recherche justifie la séparation ; côté client,
             il n'y a que le bouton de thème : pas de filet ni de fond distinct, pour éviter une bande vide. --}}
        <div class="topbar {{ auth()->check() && auth()->user()->estPersonnel() ? '' : 'topbar--sobre' }}">
            @auth
                @if(auth()->user()->estPersonnel())
                    {{-- [RECHERCHE GLOBALE] IP, CIDR, ASN ou texte — envoyée en GET à /recherche --}}
                    <form class="recherche" method="GET" action="{{ route('recherche') }}">
                        {{-- [RECHERCHE] La loupe est un bouton : clic = recherche (Entrée fonctionne aussi) --}}
                        <button type="submit" class="btn-loupe" aria-label="Lancer la recherche" title="Lancer la recherche">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                        </button>
                        <input type="search" name="q" value="{{ request('q') }}"
                               placeholder="Rechercher : IP, CIDR (IPv4/IPv6), ASN, client, opérateur…"
                               aria-label="Recherche globale">
                    </form>
                @endif
            @endauth
            {{-- [DARK MODE] Bouton de bascule clair/sombre (choix mémorisé instantanément) --}}
            <button type="button" class="btn-theme" onclick="basculerTheme()" title="Changer de thème" aria-label="Changer de thème">
                <svg class="icone-lune"   width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8Z"/></svg>
                <svg class="icone-soleil" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2M4.9 4.9l1.4 1.4m11.4 11.4 1.4 1.4M2 12h2m16 0h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
            </button>
        </div>

        <main>
            {{-- Message flash de succès (défini par ->with('succes', ...) dans les contrôleurs).
                 [DESIGN] Disparaît tout seul après quelques secondes (voir script en bas de page). --}}
            @if(session('succes'))
                <div class="flash" data-flash>{{ session('succes') }}</div>
            @endif

            @yield('contenu') {{-- Chaque vue enfant injecte son contenu ici --}}
        </main>
    </div>

    {{-- [DARK MODE] Bascule + mémorisation du thème dans le navigateur --}}
    <script>
        function basculerTheme() {
            const html = document.documentElement;                          // Élément <html>
            const nouveau = html.dataset.theme === 'sombre' ? 'clair' : 'sombre'; // Inversion du thème
            html.dataset.theme = nouveau;                                   // Application instantanée
            localStorage.setItem('theme-ipam', nouveau);                    // Mémorisation du choix
        }
    </script>

    {{-- [DESIGN] Le message flash de succès se referme tout seul après 6 secondes,
         au lieu de rester affiché indéfiniment tant qu'on ne change pas de page. --}}
    <script>
        document.querySelectorAll('[data-flash]').forEach(function (message) {
            setTimeout(function () {
                message.style.transition = 'opacity .4s ease, transform .4s ease';
                message.style.opacity = '0';
                message.style.transform = 'translateY(-6px)';
                setTimeout(function () { message.remove(); }, 400); // Retrait du DOM une fois le fondu terminé
            }, 6000);
        });
    </script>

    {{-- [CHARGEUR] Voile plein écran + mini-chargeur dans les boutons d'envoi --}}
    @include('composants.chargeur-global')
</body>
</html>
