<?php
// Contrôleur : JournalAuditController
// Consultation du journal d'audit (personnel uniquement : admin & manager).
// Couvre : Consulter les connexions de tous les utilisateurs et les modifications
//          effectuées par le personnel, avec filtres par action et par rôle.

namespace App\Http\Controllers;

use App\Models\JournalAudit;
use Illuminate\Http\Request;

class JournalAuditController extends Controller
{
    /**
     * Liste paginée du journal, du plus récent au plus ancien.
     * Filtres optionnels : ?action=connexion|deconnexion|creation|modification|suppression
     *                      ?role=admin|manager|client
     */
    public function index(Request $request)
    {
        $requete = JournalAudit::query();

        // Filtre par type d'action, si fourni et valide.
        if (in_array($request->query('action'), ['connexion', 'deconnexion', 'creation', 'modification', 'suppression', 'telechargement'])) {
            $requete->where('action', $request->query('action'));
        }

        // Filtre par rôle, si fourni et valide.
        if (in_array($request->query('role'), ['admin', 'manager', 'client'])) {
            $requete->where('role', $request->query('role'));
        }

        $entrees = $requete->orderByDesc('created_at')->paginate(25)->withQueryString();

        return view('journal_audit.index', compact('entrees'));
    }

    /**
     * [PURGE] Supprime UNE entrée jugée sans intérêt (réservé à l'administrateur).
     * La suppression est elle-même tracée : on sait toujours qui a retiré quoi.
     */
    public function destroy(JournalAudit $entree)
    {
        $resume = "{$entree->action} du {$entree->created_at->format('d/m/Y H:i')} par {$entree->nom_utilisateur}";
        $entree->delete();

        // On consigne le retrait APRÈS coup, sinon la nouvelle entrée serait comptée dans la purge.
        JournalAudit::consigner('suppression', 'JournalAudit', null,
            "Suppression d'une entrée du journal ({$resume}).");

        return back()->with('succes', 'Entrée supprimée du journal.');
    }

    /**
     * [PURGE] Supprime en une fois toutes les entrées ANTÉRIEURES à un nombre de jours donné,
     * en respectant le filtre d'action éventuellement actif. Réservé à l'administrateur.
     *
     * Exemple : « plus de 90 jours » + filtre « Connexions » ne retire que les vieilles connexions.
     *
     * [SÉCURITÉ] La purge porte TOUJOURS sur des entrées anciennes : il n'existe volontairement
     * aucune option « tout l'historique ». Un journal d'audit intégralement effaçable perdrait
     * toute valeur de preuve, et l'activité récente doit rester consultable. La durée minimale
     * autorisée est de 7 jours ; la liste ci-dessous fait aussi office de garde-fou côté serveur
     * contre une requête forgée à la main.
     */
    public function purger(Request $request)
    {
        $donnees = $request->validate([
            'jours'  => ['required', 'integer', 'in:7,30,90,180,365'],   // Minimum : 7 jours
            'action' => ['nullable', 'in:connexion,deconnexion,creation,modification,suppression,telechargement'],
        ], [
            'jours.in' => "Durée de conservation non autorisée : la purge ne peut porter que sur des entrées d'au moins 7 jours.",
        ]);

        // On ne touche qu'aux entrées plus anciennes que le nombre de jours choisi.
        $requete = JournalAudit::where('created_at', '<', now()->subDays((int) $donnees['jours']));

        if (! empty($donnees['action'])) {
            $requete->where('action', $donnees['action']);
        }

        $nombre = $requete->count();
        $requete->delete();

        // Trace de la purge : combien d'entrées, sur quel périmètre, par qui.
        $perimetre = empty($donnees['action']) ? 'toutes actions' : "action « {$donnees['action']} »";
        JournalAudit::consigner('suppression', 'JournalAudit', null,
            "Purge du journal : {$nombre} entrée(s) supprimée(s) ({$perimetre}, de plus de {$donnees['jours']} jours).");

        return back()->with('succes', "{$nombre} entrée(s) supprimée(s) du journal.");
    }
}
