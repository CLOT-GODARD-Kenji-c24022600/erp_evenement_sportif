<?php

/**
 * YES – Your Event Solution
 * @file ExportController.php
 * @version 1.0  –  2026
 *
 * Export PDF et Excel/CSV pour :
 *  - Budget prévisionnel d'un événement ou projet
 *  - Facturation d'un événement ou projet
 *  - Planning d'un événement ou projet
 *
 * PDF : généré avec TCPDF (composer require tecnickcom/tcpdf)
 * Excel : CSV UTF-8 avec BOM (ouverture directe dans Excel)
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Models\BudgetModel;
use App\Models\FacturationModel;
use App\Models\PlanningModel;
use App\Models\EventModel;
use App\Models\ProjectModel;
use Core\Security;

class ExportController
{
    // ── Dispatch ─────────────────────────────────────────────

    public function handle(): void
    {
        $type    = Security::sanitizeString($_GET['type']    ?? '');
        $format  = Security::sanitizeString($_GET['format']  ?? 'pdf');
        $eventId = Security::sanitizeInt($_GET['event_id']   ?? 0);
        $projetId= Security::sanitizeInt($_GET['projet_id']  ?? 0);

        $allowed = ['budget', 'facturation', 'planning'];
        if (!in_array($type, $allowed, true)) {
            http_response_code(400);
            echo 'Type invalide.';
            exit;
        }

        // Contexte
        $contextLabel = '';
        if ($eventId > 0) {
            $ev = (new EventModel())->findById($eventId);
            $contextLabel = $ev['nom'] ?? "Événement #{$eventId}";
        } elseif ($projetId > 0) {
            $pr = (new ProjectModel())->findById($projetId);
            $contextLabel = $pr['nom'] ?? "Projet #{$projetId}";
        }

        $data = $this->loadData($type, $eventId, $projetId);

        if ($format === 'csv') {
            $this->exportCsv($type, $data, $contextLabel);
        } else {
            $this->exportPdf($type, $data, $contextLabel, $eventId, $projetId);
        }
    }

    // ── Chargement des données ────────────────────────────────

    private function loadData(string $type, int $eventId, int $projetId): array
    {
        return match ($type) {
            'budget'      => $eventId > 0
                             ? (new BudgetModel())->getByEvent($eventId)
                             : (new BudgetModel())->getByProjet($projetId),
            'facturation' => $eventId > 0
                             ? (new FacturationModel())->getByEvent($eventId)
                             : (new FacturationModel())->getByProjet($projetId),
            'planning'    => $eventId > 0
                             ? (new PlanningModel())->getByEvent($eventId)
                             : (new PlanningModel())->getByProjet($projetId),
            default       => [],
        };
    }

    // ── Export CSV ────────────────────────────────────────────

    private function exportCsv(string $type, array $data, string $label): void
    {
        $filename = $this->filename($type, $label, 'csv');

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, no-store, must-revalidate');

        $out = fopen('php://output', 'w');
        // BOM UTF-8 pour Excel
        fwrite($out, "\xEF\xBB\xBF");

        match ($type) {
            'budget'      => $this->csvBudget($out, $data),
            'facturation' => $this->csvFacturation($out, $data),
            'planning'    => $this->csvPlanning($out, $data),
            default       => null,
        };

        fclose($out);
        exit;
    }

    private function csvBudget($out, array $rows): void
    {
        fputcsv($out, ['Type','Catégorie','Sous-catégorie','Libellé','Fournisseur','Sponsor','Prévisionnel (€)','Comparatif (€)','Note'], ';');
        foreach ($rows as $r) {
            fputcsv($out, [
                $r['type'] === 'produit' ? 'Produit' : 'Charge',
                $r['categorie']      ?? '',
                $r['sous_categorie'] ?? '',
                $r['libelle']        ?? '',
                $r['fournisseur']    ?? '',
                $r['sponsor']        ?? '',
                number_format((float)$r['previsionnel'], 2, ',', ' '),
                number_format((float)$r['comparatif'],   2, ',', ' '),
                $r['note']           ?? '',
            ], ';');
        }
        // Totaux
        $produits = array_sum(array_map(fn($r) => $r['type']==='produit'?(float)$r['previsionnel']:0, $rows));
        $charges  = array_sum(array_map(fn($r) => $r['type']==='charge' ?(float)$r['previsionnel']:0, $rows));
        fputcsv($out, [], ';');
        fputcsv($out, ['','','','TOTAL PRODUITS','','',number_format($produits,2,',',' '),'',''], ';');
        fputcsv($out, ['','','','TOTAL CHARGES', '','',number_format($charges,2,',',' '),'',''], ';');
        fputcsv($out, ['','','','RÉSULTAT',       '','',number_format($produits-$charges,2,',',' '),'',''], ';');
    }

    private function csvFacturation($out, array $rows): void
    {
        fputcsv($out, ['Catégorie','Poste','Prestataire','Contact','Téléphone','Mail','P.U (€)','Qté','Total (€)','Devis','Facture','Virement','Note'], ';');
        foreach ($rows as $r) {
            $total = (float)$r['prix_unitaire'] * (float)$r['quantite'];
            fputcsv($out, [
                $r['categorie']     ?? '',
                $r['poste']         ?? '',
                $r['prestataire']   ?? '',
                $r['contact']       ?? '',
                $r['telephone']     ?? '',
                $r['mail']          ?? '',
                number_format((float)$r['prix_unitaire'], 2, ',', ' '),
                $r['quantite']      ?? 1,
                number_format($total, 2, ',', ' '),
                $r['statut_devis']    ? 'Oui' : 'Non',
                $r['statut_facture']  ? 'Oui' : 'Non',
                $r['statut_virement'] ? 'Oui' : 'Non',
                $r['note']          ?? '',
            ], ';');
        }
        $totalGeneral = array_sum(array_map(fn($r) => (float)$r['prix_unitaire']*(float)$r['quantite'], $rows));
        fputcsv($out, [], ';');
        fputcsv($out, ['','','','','','','','TOTAL',number_format($totalGeneral,2,',',' '),'','','',''], ';');
    }

    private function csvPlanning($out, array $rows): void
    {
        fputcsv($out, ['Ordre','Tâche','Statut','Date début','Date fin','Note'], ';');
        foreach ($rows as $r) {
            fputcsv($out, [
                $r['ordre']      ?? '',
                $r['tache']      ?? '',
                $r['statut']     ?? '',
                !empty($r['date_debut']) ? date('d/m/Y', strtotime($r['date_debut'])) : '',
                !empty($r['date_fin'])   ? date('d/m/Y', strtotime($r['date_fin']))   : '',
                $r['note']       ?? '',
            ], ';');
        }
    }

    // ── Export PDF ────────────────────────────────────────────

    private function exportPdf(string $type, array $data, string $label, int $eventId, int $projetId): void
    {
        // Vérifier que TCPDF est installé
        if (!class_exists('\TCPDF')) {
            // Fallback : rediriger vers CSV si TCPDF non installé
            header('Location: /export?type=' . $type . '&format=csv'
                . ($eventId  > 0 ? '&event_id='  . $eventId  : '')
                . ($projetId > 0 ? '&projet_id=' . $projetId : ''));
            exit;
        }

        $filename = $this->filename($type, $label, 'pdf');
        $titre    = ucfirst($type) . ' — ' . $label;

        $pdf = new \TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator('YES – Your Event Solution');
        $pdf->SetAuthor('YES ERP');
        $pdf->SetTitle($titre);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(true, 10);
        $pdf->AddPage();
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, $titre, 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 9);
        $pdf->Cell(0, 6, 'Généré le ' . date('d/m/Y à H:i'), 0, 1, 'R');
        $pdf->Ln(3);

        $html = $this->buildHtmlTable($type, $data);
        $pdf->writeHTML($html, true, false, true, false, '');

        $pdf->Output($filename, 'D');
        exit;
    }

    private function buildHtmlTable(string $type, array $data): string
    {
        $style = 'border-collapse:collapse;width:100%;font-size:8px;';
        $th    = 'style="background:#1e293b;color:#fff;padding:5px 4px;text-align:left;"';
        $td    = 'style="padding:4px;border-bottom:1px solid #dee2e6;"';
        $tdR   = 'style="padding:4px;border-bottom:1px solid #dee2e6;text-align:right;"';

        return match ($type) {
            'budget'      => $this->htmlBudget($data, $style, $th, $td, $tdR),
            'facturation' => $this->htmlFacturation($data, $style, $th, $td, $tdR),
            'planning'    => $this->htmlPlanning($data, $style, $th, $td, $tdR),
            default       => '',
        };
    }

    private function htmlBudget(array $rows, string $s, string $th, string $td, string $tdR): string
    {
        $h = "<table style=\"{$s}\"><thead><tr>
            <th {$th}>Type</th><th {$th}>Catégorie</th><th {$th}>Libellé</th>
            <th {$th}>Fournisseur</th><th {$th}>Sponsor</th>
            <th {$th} style=\"text-align:right\">Prévisionnel</th>
            <th {$th} style=\"text-align:right\">Comparatif</th>
            </tr></thead><tbody>";
        foreach ($rows as $r) {
            $color = $r['type']==='produit' ? '#d1fae5' : '#fee2e2';
            $h .= "<tr style=\"background:{$color};\">
                <td {$td}>" . ($r['type']==='produit'?'Produit':'Charge') . "</td>
                <td {$td}>" . htmlspecialchars($r['categorie']??'', ENT_QUOTES) . "</td>
                <td {$td}>" . htmlspecialchars($r['libelle']??'', ENT_QUOTES) . "</td>
                <td {$td}>" . htmlspecialchars($r['fournisseur']??'', ENT_QUOTES) . "</td>
                <td {$td}>" . htmlspecialchars($r['sponsor']??'', ENT_QUOTES) . "</td>
                <td {$tdR}>" . number_format((float)$r['previsionnel'],2,',',' ') . " €</td>
                <td {$tdR}>" . number_format((float)$r['comparatif'],2,',',' ') . " €</td>
            </tr>";
        }
        $prod = array_sum(array_map(fn($r)=>$r['type']==='produit'?(float)$r['previsionnel']:0, $rows));
        $chg  = array_sum(array_map(fn($r)=>$r['type']==='charge' ?(float)$r['previsionnel']:0, $rows));
        $h .= "<tr style=\"font-weight:bold;background:#e2e8f0;\">
            <td {$td} colspan=\"5\">RÉSULTAT</td>
            <td {$tdR}>" . number_format($prod-$chg,2,',',' ') . " €</td><td></td></tr>";
        return $h . "</tbody></table>";
    }

    private function htmlFacturation(array $rows, string $s, string $th, string $td, string $tdR): string
    {
        $h = "<table style=\"{$s}\"><thead><tr>
            <th {$th}>Catégorie</th><th {$th}>Poste</th><th {$th}>Prestataire</th>
            <th {$th}>Contact</th><th {$th} style=\"text-align:right\">P.U</th>
            <th {$th} style=\"text-align:right\">Qté</th>
            <th {$th} style=\"text-align:right\">Total</th>
            <th {$th}>Devis</th><th {$th}>Facture</th><th {$th}>Virement</th>
            </tr></thead><tbody>";
        foreach ($rows as $r) {
            $total = (float)$r['prix_unitaire'] * (float)$r['quantite'];
            $h .= "<tr>
                <td {$td}>" . htmlspecialchars($r['categorie']??'', ENT_QUOTES) . "</td>
                <td {$td}>" . htmlspecialchars($r['poste']??'', ENT_QUOTES) . "</td>
                <td {$td}>" . htmlspecialchars($r['prestataire']??'', ENT_QUOTES) . "</td>
                <td {$td}>" . htmlspecialchars($r['contact']??'', ENT_QUOTES) . "</td>
                <td {$tdR}>" . number_format((float)$r['prix_unitaire'],2,',',' ') . " €</td>
                <td {$tdR}>" . htmlspecialchars((string)$r['quantite'], ENT_QUOTES) . "</td>
                <td {$tdR}>" . number_format($total,2,',',' ') . " €</td>
                <td {$td}>" . ($r['statut_devis']?'✓':'') . "</td>
                <td {$td}>" . ($r['statut_facture']?'✓':'') . "</td>
                <td {$td}>" . ($r['statut_virement']?'✓':'') . "</td>
            </tr>";
        }
        $tot = array_sum(array_map(fn($r)=>(float)$r['prix_unitaire']*(float)$r['quantite'], $rows));
        $h .= "<tr style=\"font-weight:bold;background:#e2e8f0;\">
            <td {$td} colspan=\"6\">TOTAL GÉNÉRAL</td>
            <td {$tdR}>" . number_format($tot,2,',',' ') . " €</td>
            <td colspan=\"3\"></td></tr>";
        return $h . "</tbody></table>";
    }

    private function htmlPlanning(array $rows, string $s, string $th, string $td, string $tdR): string
    {
        $statColors = ['valide'=>'#d1fae5','en_cours'=>'#dbeafe','annule'=>'#fee2e2','wip'=>'#fef3c7'];
        $h = "<table style=\"{$s}\"><thead><tr>
            <th {$th}>#</th><th {$th}>Tâche</th><th {$th}>Statut</th>
            <th {$th}>Début</th><th {$th}>Fin</th><th {$th}>Note</th>
            </tr></thead><tbody>";
        foreach ($rows as $r) {
            $bg = $statColors[$r['statut']??''] ?? '#fff';
            $h .= "<tr style=\"background:{$bg};\">
                <td {$td}>" . htmlspecialchars((string)($r['ordre']??''), ENT_QUOTES) . "</td>
                <td {$td}>" . htmlspecialchars($r['tache']??'', ENT_QUOTES) . "</td>
                <td {$td}>" . htmlspecialchars($r['statut']??'', ENT_QUOTES) . "</td>
                <td {$td}>" . (!empty($r['date_debut'])?date('d/m/Y',strtotime($r['date_debut'])):'') . "</td>
                <td {$td}>" . (!empty($r['date_fin'])  ?date('d/m/Y',strtotime($r['date_fin']))  :'') . "</td>
                <td {$td}>" . htmlspecialchars($r['note']??'', ENT_QUOTES) . "</td>
            </tr>";
        }
        return $h . "</tbody></table>";
    }

    // ── Helpers ───────────────────────────────────────────────

    private function filename(string $type, string $label, string $ext): string
    {
        $clean = preg_replace('/[^a-zA-Z0-9_-]/', '_', $label);
        return strtolower($type) . '_' . $clean . '_' . date('Ymd') . '.' . $ext;
    }
}