<?php
// Contrôleur : ClientController
// CRUD des clients (personnel uniquement).
// Couvre : Ajouter / Modifier / Supprimer / Consulter la liste des clients /
//          Associer un client à une plage IP et une technologie / Visualiser ses équipements.

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\JournalAudit;
use App\Models\PlageIp;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    /**
     * Liste des clients — colonnes du schéma : nom, plage(s) attribuée(s),
     * technologie d'accès et bande passante souscrite.
     */
    public function index()
    {
        $clients = Client::with(['plagesIp.technologie', 'allocations.technologie']) // Relations du tableau
            ->withCount('equipements')                              // Compteur SQL intégré
            ->orderBy('nom')
            ->paginate(15);
        return view('clients.index', compact('clients'));
    }

    /**
     * Formulaire d'ajout d'un client (cas "Ajouter un client").
     */
    public function create()
    {
        return view('clients.create');
    }

    /**
     * Enregistre un nouveau client.
     */
    public function store(Request $request)
    {
        // Validation : nom obligatoire, email obligatoire et unique.
        $donnees = $request->validate([
            'nom'       => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', 'unique:clients,email'],
            'telephone' => ['nullable', 'string', 'max:50'],
            'adresse'   => ['nullable', 'string', 'max:255'],
        ]);

        $client = Client::create($donnees);                 // Insertion en base

        // [AUDIT] Trace la création du client par le personnel (admin/manager).
        JournalAudit::consigner('creation', 'Client', $client->id, "Création du client « {$client->nom} ».");

        return redirect()->route('clients.index')->with('succes', 'Client ajouté.');
    }

    /**
     * Fiche détaillée d'un client : plages IP, technologies et équipements rattachés
     * (cas "Visualiser les équipements rattachés à un client").
     */
    public function show(Client $client)
    {
        $client->load(['plagesIp.technologie', 'equipements.technologie']); // Relations utiles à la vue
        // Plages encore disponibles (sans client), proposées pour une affectation rapide depuis la fiche.
        $plagesDisponibles = PlageIp::whereNull('client_id')
            ->orderBy('reseau')->get();
        return view('clients.show', compact('client', 'plagesDisponibles'));
    }

    /**
     * Formulaire de modification (cas "Modifier un client").
     */
    public function edit(Client $client)
    {
        return view('clients.edit', compact('client'));
    }

    /**
     * Met à jour un client.
     */
    public function update(Request $request, Client $client)
    {
        // L'email reste unique, sauf pour la fiche en cours de modification.
        $donnees = $request->validate([
            'nom'       => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', 'unique:clients,email,' . $client->id],
            'telephone' => ['nullable', 'string', 'max:50'],
            'adresse'   => ['nullable', 'string', 'max:255'],
        ]);

        $client->update($donnees);                          // Mise à jour en base

        // [AUDIT] Trace la modification du client par le personnel (admin/manager).
        JournalAudit::consigner('modification', 'Client', $client->id, "Modification du client « {$client->nom} ».");

        return redirect()->route('clients.index')->with('succes', 'Client modifié.');
    }

    /**
     * Supprime un client (cas "Supprimer un client").
     * Ses plages redeviennent disponibles (client_id remis à null par la contrainte nullOnDelete).
     */
    public function destroy(Client $client)
    {
        // [AUDIT] Trace la suppression AVANT l'opération (le nom du client doit encore être disponible).
        JournalAudit::consigner('suppression', 'Client', $client->id, "Suppression du client « {$client->nom} ».");

        $client->delete();                                  // Suppression en base
        // Remise à jour du statut des plages libérées (le hook du modèle s'exécute à la sauvegarde).
        PlageIp::whereNull('client_id')->where('statut', 'utilisee')->get()
            ->each->save();                                 // Chaque save() recalcule le statut
        return redirect()->route('clients.index')->with('succes', 'Client supprimé.');
    }

    /**
     * Affecte rapidement une plage IP disponible au client depuis sa fiche
     * (cas "Associer un client à une plage IP et une technologie").
     */
    public function affecterPlage(Request $request, Client $client)
    {
        // La plage choisie doit exister et être encore libre.
        $donnees = $request->validate([
            'plage_ip_id' => ['required', 'exists:plages_ip,id'],
        ]);

        $plage = PlageIp::findOrFail($donnees['plage_ip_id']);      // Récupération de la plage
        abort_if($plage->client_id !== null, 422, 'Cette plage est déjà affectée.'); // Garde-fou

        $plage->client_id = $client->id;                    // Affectation au client
        $plage->save();                                     // Le statut passe à "utilisee" automatiquement

        // [AUDIT] Trace l'affectation de la plage au client.
        JournalAudit::consigner('modification', 'Client', $client->id,
            "Affectation du sous-bloc d'adresses « {$plage->reseau} » au client « {$client->nom} ».");

        return back()->with('succes', "Sous-bloc d'adresses affecté au client.");
    }
}
