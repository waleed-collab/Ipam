<?php
// Contrôleur : TechnologieController
// CRUD des technologies d'accès (personnel uniquement).
// Couvre : Ajouter / Modifier / Supprimer une technologie / Consulter les équipements par technologie.

namespace App\Http\Controllers;

use App\Models\JournalAudit;
use App\Models\Technologie;
use Illuminate\Http\Request;

class TechnologieController extends Controller
{
    /**
     * Liste des technologies avec le nombre d'équipements et de plages IP associés.
     */
    public function index()
    {
        $technologies = Technologie::withCount('equipements')   // Compteur d'équipements (SQL)
            ->with('plagesIp')                                  // Plages IP chargées pour afficher leurs CIDR
            ->orderBy('nom')
            ->paginate(15);
        return view('technologies.index', compact('technologies'));
    }

    /**
     * Formulaire d'ajout (cas "Ajouter une technologie").
     */
    public function create()
    {
        return view('technologies.create');
    }

    /**
     * Enregistre une nouvelle technologie.
     */
    public function store(Request $request)
    {
        // Validation : nom obligatoire et unique dans la table.
        $donnees = $request->validate([
            'nom'         => ['required', 'string', 'max:255', 'unique:technologies,nom'],
            'description' => ['nullable', 'string'],
            // [CAPACITY PLANNING] Limite technique de débit par client (Mbps), facultative
            'debit_max_supporte' => ['nullable', 'integer', 'min:1'],
        ]);

        $technologie = Technologie::create($donnees);       // Insertion en base

        // [AUDIT] Trace la création de la technologie.
        JournalAudit::consigner('creation', 'Technologie', $technologie->id, "Création de la technologie « {$technologie->nom} ».");

        return redirect()->route('technologies.index')->with('succes', 'Technologie ajoutée.');
    }

    /**
     * Fiche d'une technologie : liste ses équipements
     * (cas "Consulter les équipements par technologie").
     */
    public function show(Technologie $technologie)
    {
        // Charge les équipements (et leur client) ainsi que les plages IP de la technologie.
        $technologie->load(['equipements.client', 'plagesIp.client']);
        return view('technologies.show', compact('technologie'));
    }

    /**
     * Formulaire de modification (cas "Modifier une technologie").
     */
    public function edit(Technologie $technologie)
    {
        return view('technologies.edit', compact('technologie'));
    }

    /**
     * Met à jour une technologie.
     */
    public function update(Request $request, Technologie $technologie)
    {
        // Validation : le nom reste unique, sauf pour la ligne en cours de modification.
        $donnees = $request->validate([
            'nom'         => ['required', 'string', 'max:255', 'unique:technologies,nom,' . $technologie->id],
            'description' => ['nullable', 'string'],
            // [CAPACITY PLANNING] Limite technique de débit par client (Mbps), facultative
            'debit_max_supporte' => ['nullable', 'integer', 'min:1'],
        ]);

        $technologie->update($donnees);                     // Mise à jour en base

        // [AUDIT] Trace la modification de la technologie.
        JournalAudit::consigner('modification', 'Technologie', $technologie->id, "Modification de la technologie « {$technologie->nom} ».");

        return redirect()->route('technologies.index')->with('succes', 'Technologie modifiée.');
    }

    /**
     * Supprime une technologie (cas "Supprimer une technologie").
     * Les équipements et plages liés ne sont pas supprimés : leur lien devient simplement null.
     */
    public function destroy(Technologie $technologie)
    {
        // [AUDIT] Trace la suppression AVANT l'opération.
        JournalAudit::consigner('suppression', 'Technologie', $technologie->id, "Suppression de la technologie « {$technologie->nom} ».");

        $technologie->delete();                             // Suppression en base
        return redirect()->route('technologies.index')->with('succes', 'Technologie supprimée.');
    }
}
