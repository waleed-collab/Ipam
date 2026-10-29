<?php
// Contrôleur : PortailClientController
// Espace CLIENT en LECTURE SEULE : chaque client ne voit que SON périmètre.
// Couvre : Visualiser ses propres plages IP / les technologies associées / ses équipements.

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PortailClientController extends Controller
{
    /**
     * Récupère la fiche client rattachée à l'utilisateur connecté.
     * Si le compte n'est lié à aucune fiche, l'accès est refusé (403).
     */
    private function clientCourant(Request $request)
    {
        $client = $request->user()->client;                 // Relation User -> Client
        abort_if(! $client, 403, "Votre compte n'est rattaché à aucune fiche client."); // Sécurité
        return $client;                                     // Fiche du client connecté uniquement
    }

    /**
     * Accueil du portail : synthèse des ressources DU client connecté.
     */
    public function accueil(Request $request)
    {
        $client = $this->clientCourant($request);           // Périmètre strictement limité à ce client
        return view('portail.accueil', [
            'client'       => $client,                                       // Sa fiche
            'plages'       => $client->plagesIp()->with('technologie')->get(),   // Ses plages IP
            'equipements'  => $client->equipements()->with('technologie')->get(),// Ses équipements
            'technologies' => $client->technologiesAssociees(),              // Ses technologies (déduites)
            // [CAPACITY PLANNING] Sa bande passante, en LECTURE SEULE uniquement
            'allocations'  => $client->allocations()->with('technologie')->get(),
            'bandeTotale'  => $client->bandePassanteTotale(),                // Total souscrit (Mbps)
        ]);
    }
}
