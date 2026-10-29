<?php
// Contrôleur : ExportClientController
// Génère la fiche d'un client au format PDF ou CSV.
//  - Personnel (admin/manager) : passe par un formulaire dédié permettant d'AJUSTER les informations
//    juste pour le document généré (la fiche officielle en base n'est pas modifiée).
//  - Client : télécharge directement SA propre fiche, sans possibilité de modification.

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\JournalAudit;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ExportClientController extends Controller
{
    /**
     * Formulaire d'export dédié (cas "Modifier avant de télécharger", personnel uniquement).
     * Pré-rempli avec les valeurs actuelles du client, mais rien n'est enregistré en base ici.
     */
    public function formulaire(Client $client)
    {
        return view('clients.exporter', compact('client'));
    }

    /**
     * Génère le document (PDF ou CSV) à partir des valeurs éventuellement ajustées dans le formulaire.
     */
    public function generer(Request $request, Client $client)
    {
        $donnees = $request->validate([
            'nom'       => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email'],
            'telephone' => ['nullable', 'string', 'max:50'],
            'adresse'   => ['nullable', 'string', 'max:255'],
            'format'    => ['required', 'in:pdf,csv'],
        ]);

        // Fiche utilisée POUR LE DOCUMENT uniquement : peut différer de la fiche en base ($client).
        $fiche = (object) [
            'nom'       => $donnees['nom'],
            'email'     => $donnees['email'],
            'telephone' => $donnees['telephone'] ?? null,
            'adresse'   => $donnees['adresse'] ?? null,
        ];

        $client->load(['plagesIp.technologie', 'equipements.technologie', 'allocations.technologie']);

        // [AUDIT] Trace le téléchargement, y compris si les valeurs ont été ajustées pour le document.
        JournalAudit::consigner('telechargement', 'Client', $client->id,
            "Téléchargement de la fiche du client « {$client->nom} » (" . strtoupper($donnees['format']) . ").");

        return $donnees['format'] === 'pdf'
            ? $this->genererPdf($fiche, $client)
            : $this->genererCsv($fiche, $client);
    }

    /**
     * Téléchargement direct par le client connecté : SA propre fiche, sans formulaire de modification.
     */
    public function telechargerPortail(Request $request, string $format)
    {
        abort_unless(in_array($format, ['pdf', 'csv']), 404);

        $client = $request->user()->client;                 // Périmètre strictement limité à ce client
        abort_if(! $client, 403, "Votre compte n'est rattaché à aucune fiche client.");

        $client->load(['plagesIp.technologie', 'equipements.technologie', 'allocations.technologie']);

        // [AUDIT] Trace le téléchargement effectué par le client lui-même.
        JournalAudit::consigner('telechargement', 'Client', $client->id,
            "Téléchargement de sa propre fiche depuis le portail (" . strtoupper($format) . ").");

        return $format === 'pdf'
            ? $this->genererPdf($client, $client)
            : $this->genererCsv($client, $client);
    }

    /** Génère le PDF à partir de la fiche (client réel ou valeurs ajustées) et des ressources actuelles. */
    private function genererPdf(object $fiche, Client $client)
    {
        $pdf = Pdf::loadView('exports.client_pdf', compact('fiche', 'client'));
        return $pdf->download('fiche-client-' . Str::slug($client->nom) . '.pdf');
    }

    /**
     * [SÉCURITÉ] Neutralise l'injection de formule CSV ("CSV injection").
     * Une valeur commençant par =, +, - ou @ est interprétée comme une FORMULE par Excel
     * ou LibreOffice à l'ouverture du fichier (ex. un client nommé "=1+1" ou pire, une
     * commande). On préfixe donc ces valeurs d'une apostrophe : le tableur les affiche
     * alors comme du texte brut, sans les exécuter.
     *
     * @param  mixed $valeur  Valeur destinée à une cellule du CSV
     * @return string         Valeur sûre à écrire dans le fichier
     */
    private function cellule($valeur): string
    {
        $texte = (string) ($valeur ?? '');

        // Caractères déclencheurs de formule, y compris après une tabulation ou un retour chariot.
        return preg_match('/^[=+\-@\t\r]/', $texte) ? "'" . $texte : $texte;
    }

    /** Génère le CSV à partir de la fiche (client réel ou valeurs ajustées) et des ressources actuelles. */
    private function genererCsv(object $fiche, Client $client)
    {
        $nomFichier = 'fiche-client-' . Str::slug($client->nom) . '.csv';

        return response()->streamDownload(function () use ($fiche, $client) {
            $flux = fopen('php://output', 'w');
            fputs($flux, "\xEF\xBB\xBF");                   // BOM UTF-8 : accents corrects dans Excel

            fputcsv($flux, ['Fiche client']);
            fputcsv($flux, ['Nom', $this->cellule($fiche->nom)]);
            fputcsv($flux, ['Email', $this->cellule($fiche->email)]);
            fputcsv($flux, ['Téléphone', $this->cellule($fiche->telephone)]);
            fputcsv($flux, ['Adresse', $this->cellule($fiche->adresse)]);

            fputcsv($flux, []);
            fputcsv($flux, ["Sous-blocs d'adresses"]);
            fputcsv($flux, ['Réseau', 'Technologie', 'Description']);
            foreach ($client->plagesIp as $plage) {
                fputcsv($flux, [
                    $this->cellule($plage->reseau),
                    $this->cellule($plage->technologie->nom ?? ''),
                    $this->cellule($plage->description),
                ]);
            }

            fputcsv($flux, []);
            fputcsv($flux, ['Équipements']);
            fputcsv($flux, ['Nom', 'Type', 'Adresse IP', 'Technologie']);
            foreach ($client->equipements as $equipement) {
                fputcsv($flux, [
                    $this->cellule($equipement->nom),
                    $this->cellule($equipement->type),
                    $this->cellule($equipement->adresse_ip),
                    $this->cellule($equipement->technologie->nom ?? ''),
                ]);
            }

            fputcsv($flux, []);
            fputcsv($flux, ['Bande passante']);
            fputcsv($flux, ['Offre', 'Technologie', 'Débit souscrit (Mbps)']);
            foreach ($client->allocations as $allocation) {
                fputcsv($flux, [
                    $this->cellule($allocation->description),
                    $this->cellule($allocation->technologie->nom ?? ''),
                    $allocation->bande_passante_souscrite,     // Entier : aucun risque de formule
                ]);
            }

            fclose($flux);
        }, $nomFichier, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
