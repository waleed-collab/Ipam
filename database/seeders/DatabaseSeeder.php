<?php
// Seeder principal : insère des données de démonstration RÉALISTES et COHÉRENTES.
// Lancement : php artisan migrate:fresh --seed
//
// Principes de cohérence appliqués :
//   - matériels réels (Cisco ASR 9006, Juniper MX204, Cambium PTP 550...) ;
//   - opérateurs de transit réels identifiés par leur ASN public (Cogent AS174, Hurricane Electric AS6939) ;
//   - plages IPv4 dans l'espace AFRINIC 102.0.0.0/8 (contexte Burkina Faso) + IPv6 documentation 2001:db8::/32 ;
//   - débits souscrits TOUJOURS <= limite technique de la technologie ;
//   - somme des débits souscrits (850 Mbps) < capacité de transit (1 500 Mbps) : pas de survente.

namespace Database\Seeders;

use App\Models\AllocationRessource;
use App\Models\Client;
use App\Models\ConfigurationAs;
use App\Models\Equipement;
use App\Models\InterfaceReseau;
use App\Models\Operateur;
use App\Models\PlageIp;
use App\Models\SessionBgp;
use App\Models\Technologie;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ---------- 1. Configuration de l'AS local ----------
        ConfigurationAs::create([
            'asn_local'   => 65001,                          // ASN privé (RFC 6996) de démonstration
            'nom_reseau'  => 'IPAM Backbone',                // Nom du réseau local
            'router_id'   => '10.255.0.1',                   // Router-ID BGP (loopback du cœur)
            'description' => 'Système autonome local — POP de Ouagadougou.',
            // [RESSOURCES] Plages principales reçues du RIR : c'est à l'intérieur de ces blocs
            // que sont découpés les sous-blocs affectés aux clients.
            'prefixe_ipv4' => '102.64.0.0/22',               // Bloc annoncé en BGP : 2^(32-22) = 1 024 adresses
            'prefixe_ipv6' => '2001:db8::/32',               // Compté en sous-réseaux /64
        ]);

        // ---------- 2. Opérateurs de transit (ASN publics réels) ----------
        $cogent = Operateur::create([
            'nom' => 'Cogent Communications', 'asn' => 174,      // Transit IP mondial, ASN 174
            'contact' => 'Cogent NOC', 'email_contact' => 'noc@cogentco.example',
            'description' => 'Fournisseur de transit IP principal.',
        ]);
        $he = Operateur::create([
            'nom' => 'Hurricane Electric', 'asn' => 6939,        // Transit / peering mondial, ASN 6939
            'contact' => 'HE NOC', 'email_contact' => 'noc@he.example',
            'description' => 'Fournisseur de transit IP secondaire.',
        ]);

        // ---------- 3. Équipement BACKBONE local (cadre "Local" du schéma) ----------
        // Cœur de réseau réel : Cisco ASR 9006. Les sessions BGP extérieures se font avec lui.
        $backbone = Equipement::create([
            'nom' => 'RTR-CORE-OUAGA-01', 'type' => 'Routeur cœur (Cisco ASR 9006)', 'role' => 'backbone',
            'adresse_ip' => '10.255.0.1',                    // IP de management (loopback)
        ]);

        // ---------- 4. Technologies d'accès (limite technique de débit par client, Mbps) ----------
        $ftth = Technologie::create(['nom' => 'FTTH (Fibre optique)', 'description' => 'Fibre jusqu\'à l\'abonné.',
                                     'debit_max_supporte' => 1000]);
        $fh   = Technologie::create(['nom' => 'Faisceau hertzien', 'description' => 'Liaison radio point à point.',
                                     'debit_max_supporte' => 400]);
        $fwa  = Technologie::create(['nom' => '5G FWA', 'description' => 'Accès fixe sans fil 4G/5G.',
                                     'debit_max_supporte' => 300]);
        $vdsl = Technologie::create(['nom' => 'VDSL2 (xDSL)', 'description' => 'Paire cuivre haut débit.',
                                     'debit_max_supporte' => 100]);
        Technologie::create(['nom' => 'VSAT', 'description' => 'Liaison satellite.',
                             'debit_max_supporte' => 50]);

        // ---------- 5. Clients ----------
        $sahel = Client::create([
            'nom' => 'Société Sahel Télécom', 'email' => 'contact@sahel.example',
            'telephone' => '+226 25 00 00 01', 'adresse' => 'Ouagadougou, Kadiogo',
        ]);
        $banque = Client::create([
            'nom' => 'Banque du Faso', 'email' => 'it@bdf.example',
            'telephone' => '+226 20 00 00 02', 'adresse' => 'Bobo-Dioulasso, Houet',
        ]);
        $faso = Client::create([
            'nom' => 'Faso Industries SA', 'email' => 'reseau@faso-industries.example',
            'telephone' => '+226 25 00 00 03', 'adresse' => 'Ouagadougou, zone industrielle Kossodo',
        ]);

        // ---------- 6. Plages IP (issues de notre agrégat public 102.64.0.0/22) ----------
        // Chaque bloc est attribué à un client via une technologie cohérente ; le statut est déduit.
        PlageIp::create([                                    // 256 adresses -> Sahel via FTTH
            'reseau' => '102.64.0.0/24', 'description' => 'Bloc client Sahel Télécom',
            'technologie_id' => $ftth->id, 'client_id' => $sahel->id,
        ]);
        PlageIp::create([                                    // 64 adresses -> Banque du Faso via FH
            'reseau' => '102.64.1.0/26', 'description' => 'Bloc client Banque du Faso',
            'technologie_id' => $fh->id, 'client_id' => $banque->id,
        ]);
        PlageIp::create([                                    // 32 adresses -> Faso Industries via 5G FWA
            'reseau' => '102.64.2.0/27', 'description' => 'Bloc client Faso Industries',
            'technologie_id' => $fwa->id, 'client_id' => $faso->id,
        ]);
        PlageIp::create([                                    // 256 adresses -> réserve disponible
            'reseau' => '102.64.3.0/24', 'description' => 'Réserve d\'adressage',
            'technologie_id' => $ftth->id,
        ]);
        // [IPv6] Blocs de documentation 2001:db8::/32 (RFC 3849) ; la version est déduite automatiquement.
        PlageIp::create([                                    // 65 536 réseaux /64 -> Sahel
            'reseau' => '2001:db8:1000::/48', 'description' => 'Bloc IPv6 client Sahel Télécom',
            'technologie_id' => $ftth->id, 'client_id' => $sahel->id,
        ]);
        PlageIp::create([                                    // Réserve IPv6
            'reseau' => '2001:db8:2000::/48', 'description' => 'Réserve IPv6',
            'technologie_id' => $ftth->id,
        ]);

        // ---------- 7. Équipements d'accès (matériels réels), installés chez les clients ----------
        $rtrSahel = Equipement::create([
            'nom' => 'RTR-SAHEL-01', 'type' => 'Routeur (Juniper MX204)', 'role' => 'acces',
            'adresse_ip' => '102.64.0.1', 'technologie_id' => $ftth->id, 'client_id' => $sahel->id,
        ]);
        $fhBanque = Equipement::create([
            'nom' => 'FH-BDF-01', 'type' => 'Faisceau hertzien (Cambium PTP 550)', 'role' => 'acces',
            'adresse_ip' => '102.64.1.1', 'technologie_id' => $fh->id, 'client_id' => $banque->id,
        ]);
        $cpeFaso = Equipement::create([
            'nom' => 'CPE-FASO-01', 'type' => 'CPE 5G (Huawei H155-381)', 'role' => 'acces',
            'adresse_ip' => '102.64.2.1', 'technologie_id' => $fwa->id, 'client_id' => $faso->id,
        ]);
        Equipement::create([                                 // Switch interne du POP (sans client)
            'nom' => 'SW-CORE-01', 'type' => 'Switch cœur (Cisco Catalyst 9300)', 'role' => 'acces',
            'adresse_ip' => '10.255.0.10',
        ]);

        // ---------- 8. Sessions BGP (avec l'interface internationale du backbone) ----------
        // CÔTÉ INTERNATIONAL : nom interface, @IP, bande passante, neighbor. L'AS local vient de la config.
        $ifCogent = InterfaceReseau::create([
            'equipement_id' => $backbone->id, 'nom' => 'Te0/0/0', 'type' => 'internationale',
            'adresse_ip' => '154.54.10.2', 'bande_passante' => 1000, 'operateur_id' => $cogent->id,
        ]);
        $ifHe = InterfaceReseau::create([
            'equipement_id' => $backbone->id, 'nom' => 'Te0/0/1', 'type' => 'internationale',
            'adresse_ip' => '184.105.20.2', 'bande_passante' => 500, 'operateur_id' => $he->id,
        ]);
        // Champs du schéma : neighbor, @neighbor, ASN, @locale, statut. @locale = IP de l'interface.
        $sessionCogent = SessionBgp::create([
            'operateur_id' => $cogent->id, 'interface_id' => $ifCogent->id,
            'ip_voisin' => '154.54.10.1', 'asn_distant' => 174,
            'ip_locale' => '154.54.10.2', 'etat' => 'active',
            'capacite_totale' => 1000, 'description' => 'Transit principal (full routes)',
        ]);
        $sessionCogent->prefixes()->create(['prefixe' => '0.0.0.0/0',      'type' => 'recu']);
        $sessionCogent->prefixes()->create(['prefixe' => '102.64.0.0/22',  'type' => 'annonce']);

        $sessionHe = SessionBgp::create([
            'operateur_id' => $he->id, 'interface_id' => $ifHe->id,
            'ip_voisin' => '184.105.20.1', 'asn_distant' => 6939,
            'ip_locale' => '184.105.20.2', 'etat' => 'inactive',
            'capacite_totale' => 500, 'description' => 'Transit secondaire (en maintenance)',
        ]);
        $sessionHe->prefixes()->create(['prefixe' => '102.64.0.0/22', 'type' => 'annonce']);

        // CÔTÉ ACCÈS : nom interface, @IP, bande passante, équipement d'accès, technologie derrière.
        // La bande passante de chaque lien d'accès couvre le débit souscrit du client desservi.
        InterfaceReseau::create([
            'equipement_id' => $backbone->id, 'nom' => 'Te0/1/0', 'type' => 'acces',
            'adresse_ip' => '10.10.0.1', 'bande_passante' => 1000,
            'equipement_distant_id' => $rtrSahel->id, 'technologie_id' => $ftth->id,
        ]);
        InterfaceReseau::create([
            'equipement_id' => $backbone->id, 'nom' => 'Te0/1/1', 'type' => 'acces',
            'adresse_ip' => '10.10.1.1', 'bande_passante' => 400,
            'equipement_distant_id' => $fhBanque->id, 'technologie_id' => $fh->id,
        ]);
        InterfaceReseau::create([
            'equipement_id' => $backbone->id, 'nom' => 'Te0/1/2', 'type' => 'acces',
            'adresse_ip' => '10.10.2.1', 'bande_passante' => 300,
            'equipement_distant_id' => $cpeFaso->id, 'technologie_id' => $fwa->id,
        ]);

        // ---------- 9. Allocations de bande passante (débit souscrit <= limite de la technologie) ----------
        AllocationRessource::create([                        // 500 <= 1000 (FTTH)
            'client_id' => $sahel->id, 'technologie_id' => $ftth->id,
            'bande_passante_souscrite' => 500, 'description' => 'FTTH Pro 500M',
        ]);
        AllocationRessource::create([                        // 200 <= 400 (FH)
            'client_id' => $banque->id, 'technologie_id' => $fh->id,
            'bande_passante_souscrite' => 200, 'description' => 'FH Entreprise 200M',
        ]);
        AllocationRessource::create([                        // 150 <= 300 (5G FWA)
            'client_id' => $faso->id, 'technologie_id' => $fwa->id,
            'bande_passante_souscrite' => 150, 'description' => 'FWA Business 150M',
        ]);

        // ---------- 10. Comptes utilisateurs (mot de passe : "password") ----------
        User::create([                                       // Compte administrateur
            'name' => 'Administrateur', 'email' => 'admin@ipam.test',
            'password' => 'password', 'role' => 'admin',
        ]);
        User::create([                                       // Compte manager
            'name' => 'Manager', 'email' => 'manager@ipam.test',
            'password' => 'password', 'role' => 'manager',
        ]);
        User::create([                                       // Compte client rattaché à la fiche Sahel Télécom
            'name' => 'Client Sahel', 'email' => 'client@ipam.test',
            'password' => 'password', 'role' => 'client', 'client_id' => $sahel->id,
        ]);
    }
}
