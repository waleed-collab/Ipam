<?php
// Modèle Eloquent : JournalAudit
// Représente une ligne du journal d'audit : connexions/déconnexions de tous les utilisateurs
// et modifications (création/modification/suppression/téléchargement) effectuées par le personnel.

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JournalAudit extends Model
{
    protected $table = 'journal_audit';                     // Nom explicite de la table

    /** Le journal est immuable : aucune colonne "updated_at". */
    const UPDATED_AT = null;

    /** Colonnes remplissables en masse. */
    protected $fillable = [
        'user_id', 'nom_utilisateur', 'role', 'action',
        'entite', 'entite_id', 'description', 'adresse_ip',
    ];

    /** Relation : le compte utilisateur à l'origine de l'action (peut être null si supprimé). */
    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // ======================================================================
    // [LISIBILITÉ] Le journal stocke des noms techniques (PlageIp, ConfigurationAs…)
    // et des phrases brutes. Ces méthodes les traduisent pour l'affichage.
    // ======================================================================

    /** Correspondance entre le nom technique du modèle et le vocabulaire de l'interface. */
    private const LIBELLES_ENTITES = [
        'PlageIp'             => "Sous-bloc d'adresses",
        'Client'              => 'Client',
        'Equipement'          => 'Équipement',
        'Technologie'         => "Technologie d'accès",
        'Operateur'           => 'Opérateur externe',
        'SessionBgp'          => 'Session BGP',
        'AllocationRessource' => 'Bande passante',
        'ConfigurationAs'     => 'Ressources Internet',
        'JournalAudit'        => "Journal d'audit",
    ];

    /**
     * Nom lisible de la ressource concernée (« Sous-bloc d'adresses » plutôt que « PlageIp »).
     * Retourne null si l'entrée ne porte sur aucune ressource (connexion, déconnexion…).
     */
    public function libelleEntite(): ?string
    {
        if (! $this->entite) {
            return null;
        }

        // Nom inconnu : on retombe sur le nom technique plutôt que de masquer l'information.
        return self::LIBELLES_ENTITES[$this->entite] ?? $this->entite;
    }

    /**
     * Description enrichie pour l'affichage : le sujet de l'action, écrit entre guillemets
     * français dans le texte d'origine (« RTR-CORE-01 », « 102.64.0.0/24 »…), est mis en
     * évidence. Les nombres suivis d'une unité le sont également.
     *
     * [SÉCURITÉ] Le texte est ÉCHAPPÉ AVANT toute insertion de balises : une description
     * contient des données saisies par l'utilisateur (nom de client, description de bloc…),
     * qui pourraient sinon injecter du HTML dans la page.
     */
    public function descriptionFormatee(): \Illuminate\Support\HtmlString
    {
        $texte = e((string) $this->description);            // Échappement d'abord

        // Sujet entre guillemets français : mis en avant, en police à chasse fixe.
        $texte = preg_replace(
            '/«\s*(.+?)\s*»/u',
            '<b class="sujet-audit">$1</b>',
            $texte
        );

        // Décomptes (« 87 entrée(s) », « 500 Mbps ») : le nombre ressort du reste de la phrase.
        $texte = preg_replace(
            '/\b(\d[\d\s]*)\s(entrée\(s\)|Mbps|adresse\(s\))/u',
            '<b class="nombre-audit">$1</b> $2',
            $texte
        );

        return new \Illuminate\Support\HtmlString($texte);
    }

    /**
     * Enregistre une nouvelle ligne dans le journal d'audit.
     * Point d'entrée unique utilisé par tous les contrôleurs pour tracer une action.
     *
     * @param  string       $action       connexion, deconnexion, creation, modification, suppression, telechargement
     * @param  string|null  $entite       Type de ressource concernée (ex : "Client")
     * @param  int|null     $entiteId     Identifiant de la ressource concernée
     * @param  string|null  $description  Détail lisible affiché dans le journal
     */
    public static function consigner(
        string $action,
        ?string $entite = null,
        ?int $entiteId = null,
        ?string $description = null,
    ): void {
        $utilisateur = request()->user();                    // Utilisateur actuellement authentifié (ou null)

        self::create([
            'user_id'         => $utilisateur?->id,
            'nom_utilisateur' => $utilisateur?->name ?? 'Inconnu',
            'role'            => $utilisateur?->role ?? 'inconnu',
            'action'          => $action,
            'entite'          => $entite,
            'entite_id'       => $entiteId,
            'description'     => $description,
            'adresse_ip'      => request()->ip(),
        ]);
    }
}
