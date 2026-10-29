<?php
// Contrôleur : ConfigurationAsController
// Gestion des paramètres du Système Autonome local (cas "Configurer les paramètres AS").

namespace App\Http\Controllers;

use App\Models\ConfigurationAs;
use App\Models\JournalAudit;
use Illuminate\Http\Request;

class ConfigurationAsController extends Controller
{
    /**
     * Affiche le formulaire de configuration de l'AS (une seule configuration).
     */
    public function edit()
    {
        $configuration = ConfigurationAs::courante();       // Récupère (ou initialise) l'unique ligne
        return view('configuration_as.edit', compact('configuration'));
    }

    /**
     * Enregistre les nouveaux paramètres de l'AS.
     */
    public function update(Request $request)
    {
        // Validation : l'ASN local et le nom du réseau sont obligatoires.
        $donnees = $request->validate([
            'asn_local'   => ['required', 'integer', 'min:0'],      // Numéro d'AS local
            'nom_reseau'  => ['required', 'string', 'max:255'],     // Nom du réseau
            'router_id'   => ['nullable', 'ipv4'],                  // Router-ID = adresse IPv4 valide
            'description' => ['nullable', 'string'],                // Description libre
            // [RESSOURCES] Plages principales : format CIDR obligatoire (ex : 102.64.0.0/19)
            'prefixe_ipv4' => ['nullable', 'string', 'max:64', 'regex:/^\d{1,3}(\.\d{1,3}){3}\/\d{1,2}$/'],
            'prefixe_ipv6' => ['nullable', 'string', 'max:64', 'regex:/^[0-9a-fA-F:]+\/\d{1,3}$/'],
        ], [
            'prefixe_ipv4.regex' => 'La plage principale IPv4 doit être au format CIDR, ex : 102.64.0.0/19.',
            'prefixe_ipv6.regex' => 'La plage principale IPv6 doit être au format CIDR, ex : 2001:db8::/32.',
        ]);

        $configuration = ConfigurationAs::courante();
        $configuration->update($donnees);                   // Mise à jour de l'unique configuration

        // [AUDIT] Trace la modification des paramètres AS.
        JournalAudit::consigner('modification', 'ConfigurationAs', $configuration->id, 'Modification des paramètres AS.');

        return redirect()->route('configuration-as.edit')->with('succes', 'Ressources Internet enregistrés.');
    }
}
