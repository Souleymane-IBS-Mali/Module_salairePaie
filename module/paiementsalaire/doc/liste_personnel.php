<?php
// Appel de la librairie FPDF
require '../../main.inc.php';
require_once 'fpdf/fpdf.php';

$id_societe = GETPOST('id_societe', 'int');

// Récupération de la société
$sql = "SELECT nom FROM ".MAIN_DB_PREFIX."societe WHERE rowid=".((int) $id_societe);
$result1 = $db->query($sql);
$sc = ($result1 ? $db->fetch_object($result1) : null);
if (!$sc) {
    $sc = (object) array('nom' => '');
}

// Récupération de la liste des personnels
$sql = "SELECT u.rowid, u.lastname, u.firstname, u.dateemployment, u.office_phone, u.email, u.office_fax, ue.fk_object, ue.egp, sal.fk_user, sal.matricule";
$sql .= " FROM ".MAIN_DB_PREFIX."user as u";
$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."user_extrafields as ue ON u.rowid = ue.fk_object";
$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."salarie as sal ON sal.fk_user = u.rowid";
$sql .= " WHERE ue.egp = ".((int) $id_societe);
$sql .= " ORDER BY u.lastname";
$result = $db->query($sql);
$num = ($result ? $db->num_rows($result) : 0);

// Création de la classe PDF
class PDF extends FPDF
{
    public $societe_nom = '';

    // Header
    function Header()
    {
        $this->SetTitle(utf8_decode('Personnels'), true);
        $this->SetFont('Helvetica', 'B', 11);
        $this->SetFillColor(230, 230, 230);
        $this->SetX(70);

        $titre = 'Liste des Personnels '.($this->societe_nom ?: '');
        $this->Cell(60, 3, utf8_decode($titre), 0, 0, 'R', 0);
        $this->Line(12, $this->GetY() + 5, $this->GetPageWidth() - 12, $this->GetY() + 5);
    }

    // Footer
    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Helvetica', 'I', 9);
        $this->Cell(0, 10, 'Page '.$this->PageNo().'/{nb}', 0, 0, 'C');
        $this->Line(12, $this->GetY(), $this->GetPageWidth() - 12, $this->GetY());
    }
}

$pdf = new PDF('P', 'mm', 'A4');
$pdf->societe_nom = $sc->nom;
$pdf->AliasNbPages();
$pdf->AddPage();

$pdf->SetFont('Helvetica', '', 9);
$pdf->SetTextColor(0);
$pdf->SetLeftMargin(13);
$pdf->SetRightMargin(15);
$pdf->SetAutoPageBreak(true, 18);

function print_table_header($pdf, &$y)
{
    $pdf->SetY($y);
    $pdf->SetX(12);
    $pdf->SetFillColor(230, 230, 230);
    $pdf->Cell($pdf->GetPageWidth() - 24, 4, '', 0, 0, '', true);

    $pdf->SetFont('Helvetica', '', 8);
    $pdf->SetY($y);

    $pdf->SetX(13);
    $pdf->Cell(10, 4, utf8_decode('N°'), 0, 0, 'L');

    $pdf->SetX(25);
    $pdf->Cell(45, 4, utf8_decode('Prenom'), 0, 0, 'L');

    $pdf->SetX(72);
    $pdf->Cell(47, 4, utf8_decode('Nom'), 0, 0, 'L');

    $pdf->SetX(121);
    $pdf->Cell(44, 4, utf8_decode('Matricule'), 0, 0, 'L');

    $pdf->SetX(167);
    $pdf->Cell(31, 4, utf8_decode('Tel'), 0, 0, 'L');

    $pdf->Line(12, 15, 12, $pdf->GetPageHeight() - 15);
    $pdf->Line(24, 15, 24, $pdf->GetPageHeight() - 15);
    $pdf->Line(71, 15, 71, $pdf->GetPageHeight() - 15);
    $pdf->Line(120, 15, 120, $pdf->GetPageHeight() - 15);
    $pdf->Line(166, 15, 166, $pdf->GetPageHeight() - 15);
    $pdf->Line($pdf->GetPageWidth() - 12, 15, $pdf->GetPageWidth() - 12, $pdf->GetPageHeight() - 15);
    $pdf->Line(12, $y + 4, $pdf->GetPageWidth() - 12, $y + 4);

    $y += 5;
}

$y = 15;
print_table_header($pdf, $y);

if ($result && $num > 0) {
    $i = 0;
    while ($i < $num) {
        $liste = $db->fetch_object($result);
        if (!$liste) {
            $i++;
            continue;
        }

        if ($y > ($pdf->GetPageHeight() - 22)) {
            $pdf->AddPage();
            $y = 15;
            print_table_header($pdf, $y);
        }

        $firstname = !empty($liste->firstname) ? $liste->firstname : '';
        $lastname = !empty($liste->lastname) ? $liste->lastname : '';
        $matricule = !empty($liste->matricule) ? $liste->matricule : '';
        $telephone = !empty($liste->office_phone) ? $liste->office_phone : (!empty($liste->office_fax) ? $liste->office_fax : '');

        $pdf->SetFont('Helvetica', '', 8);
        $pdf->SetY($y);

        $pdf->SetX(13);
        $pdf->Cell(10, 4, utf8_decode((string) ($i + 1)), 0, 0, 'L');

        $pdf->SetX(25);
        $pdf->Cell(45, 4, utf8_decode($firstname), 0, 0, 'L');

        $pdf->SetX(72);
        $pdf->Cell(47, 4, utf8_decode($lastname), 0, 0, 'L');

        $pdf->SetX(121);
        $pdf->Cell(44, 4, utf8_decode($matricule), 0, 0, 'L');

        $pdf->SetX(167);
        $pdf->Cell(31, 4, utf8_decode($telephone), 0, 0, 'L');

        $pdf->Line(12, $y + 4, $pdf->GetPageWidth() - 12, $y + 4);

        $y += 6;
        $i++;
    }
} else {
    $pdf->SetFont('Helvetica', '', 9);
    $pdf->SetY($y + 4);
    $pdf->SetX(13);
    $pdf->Cell(0, 6, utf8_decode('Aucun personnel trouvé'), 0, 1, 'L');
}

$pdf->Output('listePersonnel.pdf', 'I');
$db->close();
