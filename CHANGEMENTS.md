# IPAM — Livraison incrémentale : audit + nouvelles fonctionnalités

Ce ZIP contient **uniquement les fichiers modifiés ou créés**. Décompressez-le à la racine
de votre projet Laravel existant (les chemins sont préservés), puis exécutez :

```bash
php artisan migrate          # Applique les 5 nouvelles migrations (aucune perte de données)
php artisan view:clear       # Vide le cache des vues
# Optionnel, pour repartir avec les données de démo enrichies :
# php artisan migrate:fresh --seed
```

Configuration email requise pour le "mot de passe oublié" (dans `.env`) :
`MAIL_MAILER=smtp` + vos identifiants SMTP (ou `MAIL_MAILER=log` en développement :
le lien de réinitialisation apparaît alors dans `storage/logs/laravel.log`).

---

## Phase 1 — Audit

Vérifié conforme au document fonctionnel : matrice des droits (routes + middleware `role:`),
CRUD des 8 entités, associations opérateurs/BGP/technologies/clients/plages, statut automatique
des plages, périmètre strict du portail client, type natif PostgreSQL `cidr` sur `plages_ip.reseau`.

Incohérences détectées et corrigées :
1. La table `password_reset_tokens` manquait (bloquant pour la réinitialisation) → migration ajoutée.
2. Les validations CIDR (plages IP et préfixes BGP) n'acceptaient que l'IPv4 → validation IPv4+IPv6 partagée (`PlageIp::analyserCidr()`).
3. `Client::technologiesAssociees()` ignore désormais aussi les nouvelles allocations → complété.

Aucune régression : aucun champ existant supprimé, toutes les nouvelles colonnes sont nullable,
toutes les routes et vues existantes sont conservées.

## Phase 2 — Authentification moderne

- **Inscription client** (`/inscription`) : nom, prénom, entreprise, email, téléphone, mot de passe +
  confirmation ; crée la fiche `clients` + le compte `users` (rôle `client` imposé) dans une transaction.
- **Mot de passe oublié** : workflow Laravel complet (demande → email en français → réinitialisation),
  routes conventionnelles `password.request/email/reset/update`.
- **Icône œil** sur tous les champs mot de passe (connexion, inscription, réinitialisation) avec animation.

## Phase 3 — Design des pages publiques

Nouveau layout `layouts/auth.blade.php` : fond dégradé animé, carte avec profondeur, icônes SVG,
transitions fluides, responsive, compatible clair/sombre. Pages connexion / inscription / mot de passe
oublié / réinitialisation refaites avec la même identité graphique.

## Phase 4 — Dashboard + mode clair/sombre

- Dashboard professionnel : cartes statistiques, 3 graphiques interactifs (Chart.js), widgets capacité,
  jauges bande passante et occupation, répartition IPv4/IPv6 (plages, capacité, bande passante),
  capacité utilisée/restante, taux d'occupation, alertes réseau, activité récente.
- **Mode clair / sombre** : bascule instantanée, mémorisation (`localStorage`), palette cohérente via
  variables CSS, toutes les pages compatibles (layout principal + layout auth).

## Phase 5 — Nouvelles fonctionnalités

1. **Bande passante (capacity planning)** : `sessions_bgp.capacite_totale`,
   `technologies.debit_max_supporte`, table pivot `allocation_ressources`
   (`bande_passante_souscrite`, `bande_passante_max`). Règles métier bloquantes :
   R1 débit ≤ limite de la technologie ; R2 somme des débits d'une session ≤ sa capacité.
   CRUD complet Admin/Manager (menu « Bande passante ») ; lecture seule sur le portail client.
   Alerte dashboard si une session dépasse 90 % d'occupation.
2. **IPv4/IPv6 natif** : colonne `cidr` PostgreSQL conservée (elle accepte l'IPv6 nativement),
   nouvelle colonne `version_ip` (4/6) auto-déduite et rétro-remplie via `family(reseau)` ;
   validations, formulaires et statistiques séparées IPv4/IPv6.
3. **Recherche globale** (Admin/Manager, barre en haut de page) : IP ou CIDR (IPv4/IPv6) via
   l'opérateur PostgreSQL `>>=` (plage parente la plus spécifique d'abord, avec client, technologie,
   opérateur et profil de bande passante via `with()`) ; ASN → sessions BGP, opérateur, préfixes et
   clients associés ; texte libre → clients, opérateurs, technologies. Résultats en fiches complètes.

## Fichiers livrés

Voir `LISTE_FICHIERS.txt` dans le ZIP.
