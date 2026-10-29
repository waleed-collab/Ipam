<?php
// Contrôleur : OperateurController
// CRUD complet des opérateurs externes (réservé au personnel : admin & manager).
// Couvre : Ajouter / Modifier / Supprimer / Consulter la liste des opérateurs.

namespace App\Http\Controllers;

use App\Models\JournalAudit;
use App\Models\Operateur;
use Illuminate\Http\Request;

class OperateurController extends Controller
{
    /**
     * Liste tous les opérateurs (cas "Consulter la liste des opérateurs").
     */
    public function index()
    {
        // withCount : ajoute le nombre de sessions BGP par opérateur sans requête supplémentaire.
        $operateurs = Operateur::withCount('sessionsBgp')->orderBy('nom')->paginate(15);
        return view('operateurs.index', compact('operateurs'));   // Passe la collection à la vue
    }

    /**
     * Affiche le formulaire de création (cas "Ajouter un opérateur").
     */
    public function create()
    {
        return view('operateurs.create');                   // Formulaire vierge
    }

    /**
     * Enregistre un nouvel opérateur en base.
     */
    public function store(Request $request)
    {
        // Validation serveur : le nom et l'ASN sont obligatoires, l'ASN doit être un entier positif.
        $donnees = $request->validate([
            'nom'           => ['required', 'string', 'max:255'],   // Nom obligatoire
            'asn'           => ['required', 'integer', 'min:1'],    // Numéro d'AS valide
            'contact'       => ['nullable', 'string', 'max:255'],   // Contact facultatif
            'email_contact' => ['nullable', 'email'],               // Email facultatif mais bien formé
            'description'   => ['nullable', 'string'],              // Notes libres
        ]);

        $operateur = Operateur::create($donnees);           // Insertion en base via Eloquent

        // [AUDIT] Trace la création de l'opérateur.
        JournalAudit::consigner('creation', 'Operateur', $operateur->id, "Création de l'opérateur « {$operateur->nom} ».");

        // Redirection vers la liste avec un message flash de succès.
        return redirect()->route('operateurs.index')->with('succes', 'Opérateur ajouté avec succès.');
    }

    /**
     * Affiche le formulaire d'édition (cas "Modifier un opérateur").
     * Le paramètre $operateur est injecté automatiquement par Laravel (route model binding).
     */
    public function edit(Operateur $operateur)
    {
        return view('operateurs.edit', compact('operateur')); // Formulaire pré-rempli
    }

    /**
     * Met à jour un opérateur existant.
     */
    public function update(Request $request, Operateur $operateur)
    {
        // Mêmes règles de validation qu'à la création.
        $donnees = $request->validate([
            'nom'           => ['required', 'string', 'max:255'],
            'asn'           => ['required', 'integer', 'min:1'],
            'contact'       => ['nullable', 'string', 'max:255'],
            'email_contact' => ['nullable', 'email'],
            'description'   => ['nullable', 'string'],
        ]);

        $operateur->update($donnees);                       // Mise à jour en base

        // [AUDIT] Trace la modification de l'opérateur.
        JournalAudit::consigner('modification', 'Operateur', $operateur->id, "Modification de l'opérateur « {$operateur->nom} ».");

        return redirect()->route('operateurs.index')->with('succes', 'Opérateur modifié avec succès.');
    }

    /**
     * Supprime un opérateur (cas "Supprimer un opérateur").
     * Ses sessions BGP sont supprimées en cascade (contrainte définie dans la migration).
     */
    public function destroy(Operateur $operateur)
    {
        // [AUDIT] Trace la suppression AVANT l'opération.
        JournalAudit::consigner('suppression', 'Operateur', $operateur->id, "Suppression de l'opérateur « {$operateur->nom} ».");

        $operateur->delete();                               // Suppression en base
        return redirect()->route('operateurs.index')->with('succes', 'Opérateur supprimé.');
    }
}
