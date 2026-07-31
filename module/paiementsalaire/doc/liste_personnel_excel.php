<?php
require_once '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/custom/paiementsalaire/lib/paiementsalaire.lib.php';

// Import PhpSpreadsheet Dolibarr
require_once DOL_DOCUMENT_ROOT.'/includes/phpoffice/phpspreadsheet/src/autoloader.php';
require_once DOL_DOCUMENT_ROOT.'/includes/Psr/autoloader.php';
require_once PHPEXCELNEW_PATH.'Spreadsheet.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

$id_societe = GETPOST('id_societe', 'int');
$id_convention = GETPOST('id_convention', 'int');

if (empty($id_societe)) {
    dol_print_error($db, 'Paramètre manquant : id_societe');
    exit;
}

// Société
$sql_soc = "SELECT rowid, nom FROM ".MAIN_DB_PREFIX."societe WHERE rowid=".((int) $id_societe);
$res_soc = $db->query($sql_soc);
$obj_soc = ($res_soc ? $db->fetch_object($res_soc) : null);
$nom_societe = ($obj_soc && !empty($obj_soc->nom)) ? $obj_soc->nom : 'Societe_'.$id_societe;

// Liste des personnels de la société
$sql = "SELECT ";
$sql .= "sal.rowid as salrowid, sal.matricule, sal.fk_user, sal.fk_categorie, sal.fk_echelon, sal.archiver, ";
$sql .= "u.rowid as userrowid, u.lastname, u.firstname, u.dateemployment, u.job, u.office_phone, u.user_mobile, u.email, u.office_fax, ";
$sql .= "ue.fk_object, ue.egp ";
$sql .= "FROM ".MAIN_DB_PREFIX."user as u ";
$sql .= "LEFT JOIN ".MAIN_DB_PREFIX."salarie as sal ON u.rowid = sal.fk_user ";
$sql .= "LEFT JOIN ".MAIN_DB_PREFIX."user_extrafields as ue ON u.rowid = ue.fk_object ";
$sql .= "WHERE ue.egp = ".((int) $id_societe)." ";
$sql .= "AND (sal.archiver != 'oui' OR sal.archiver IS NULL) ";
$sql .= "ORDER BY u.lastname ASC, u.firstname ASC";

$res = $db->query($sql);

$spreadsheet = new Spreadsheet();
$spreadsheet->getProperties()
    ->setCreator('Dolibarr')
    ->setLastModifiedBy('Dolibarr')
    ->setTitle('Liste des personnels')
    ->setSubject('Liste des personnels')
    ->setDescription('Liste des personnels de la société')
    ->setKeywords('dolibarr paie personnel excel')
    ->setCategory('Personnel');

$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Personnel');

// Titre
$sheet->mergeCells('A1:N2');
$sheet->setCellValue('A1', 'LISTE DES PERSONNELS - '.strtoupper($nom_societe));
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('A1')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
$sheet->getStyle('A1')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

$sheet->mergeCells('A3:N3');
$sheet->setCellValue('A3', 'Généré le '.date('d/m/Y H:i'));
$sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$headers = array(
    'N°',
    'Matricule',
    'Nom',
    'Prénom',
    'Date d\'entrée',
    'Fonction',
    'Catégorie',
    'Échelon',
    'Salaire de base',
    'Ancienneté',
    'Solde congé',
    'Téléphone',
    'Email',
    'Statut salarié'
);

$header_row = 5;
foreach ($headers as $index => $label) {
    $col = Coordinate::stringFromColumnIndex($index + 1);
    $cell = $col.$header_row;
    $sheet->setCellValue($cell, $label);
    $sheet->getStyle($cell)->getFont()->setBold(true);
    $sheet->getStyle($cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle($cell)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
    $sheet->getStyle($cell)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE6E6E6');
    $sheet->getStyle($cell)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
}

$row = 6;
$numero = 1;

if ($res) {
    while ($obj = $db->fetch_object($res)) {
        if (!$obj) {
            continue;
        }

        $categorie_label = '';
        $echelon_label = '';
        $salaire_base = 0;

        if (!empty($obj->fk_categorie)) {
            $sql_cat = "SELECT code_categorie FROM ".MAIN_DB_PREFIX."dcategories WHERE rowid=".((int) $obj->fk_categorie);
            $res_cat = $db->query($sql_cat);
            $obj_cat = ($res_cat ? $db->fetch_object($res_cat) : null);
            if ($obj_cat && !empty($obj_cat->code_categorie)) {
                $categorie_label = $obj_cat->code_categorie;
            }
        }

        // fk_echelon = 0 est accepté comme valeur valide dans la logique métier.
        $fk_echelon = 0;
        if ($obj->fk_echelon !== null && $obj->fk_echelon !== '') {
            $fk_echelon = (int) $obj->fk_echelon;
            if ($fk_echelon > 0) {
                $sql_ech = "SELECT libelle FROM ".MAIN_DB_PREFIX."echelon WHERE rowid=".$fk_echelon;
                $res_ech = $db->query($sql_ech);
                $obj_ech = ($res_ech ? $db->fetch_object($res_ech) : null);
                if ($obj_ech && !empty($obj_ech->libelle)) {
                    $echelon_label = $obj_ech->libelle;
                }
            } else {
                $echelon_label = '0';
            }
        }

        if (!empty($id_convention) && !empty($obj->fk_categorie)) {
            $sql_grille = "SELECT rowid FROM ".MAIN_DB_PREFIX."grille WHERE active=1 AND fk_convention=".((int) $id_convention);
            $res_grille = $db->query($sql_grille);
            $obj_grille = ($res_grille ? $db->fetch_object($res_grille) : null);

            if ($obj_grille) {
                $sql_salbase = "SELECT salaire_base FROM ".MAIN_DB_PREFIX."grille_categorie_echelon_salaire_base";
                $sql_salbase .= " WHERE fk_grille=".((int) $obj_grille->rowid);
                $sql_salbase .= " AND fk_categorie=".((int) $obj->fk_categorie);
                $sql_salbase .= " AND fk_echelon=".$fk_echelon;
                $res_salbase = $db->query($sql_salbase);
                $obj_salbase = ($res_salbase ? $db->fetch_object($res_salbase) : null);

                if (!$obj_salbase && $fk_echelon === 0) {
                    $sql_salbase = "SELECT salaire_base FROM ".MAIN_DB_PREFIX."grille_categorie_echelon_salaire_base";
                    $sql_salbase .= " WHERE fk_grille=".((int) $obj_grille->rowid);
                    $sql_salbase .= " AND fk_categorie=".((int) $obj->fk_categorie);
                    $sql_salbase .= " AND (fk_echelon=0 OR fk_echelon IS NULL)";
                    $sql_salbase .= " LIMIT 1";
                    $res_salbase = $db->query($sql_salbase);
                    $obj_salbase = ($res_salbase ? $db->fetch_object($res_salbase) : null);
                }

                if ($obj_salbase && $obj_salbase->salaire_base !== null) {
                    $salaire_base = (float) $obj_salbase->salaire_base;
                }
            }
        }

        $anciennete = array(0);
        if (!empty($obj->salrowid) && !empty($id_convention)) {
            $anciennete_tmp = prime_anciennete($db, $obj->salrowid, $id_convention, date('m'), date('Y'), $obj->userrowid);
            if (is_array($anciennete_tmp)) {
                $anciennete = $anciennete_tmp;
            }
        }
        if (!isset($anciennete[0])) {
            $anciennete[0] = 0;
        }

        $solde = 0;

		// Priorité à la date d'ancienneté du salarié
		if (!empty($obj->fk_salarie)) {

			$sql_sal = "SELECT date_anciennete
						FROM ".MAIN_DB_PREFIX."salarie
						WHERE rowid=".(int) $obj->fk_salarie;

			$req_sal = $db->query($sql_sal);

			if ($req_sal && ($obj_sal = $db->fetch_object($req_sal))) {
				if (!empty($obj_sal->date_anciennete)) {
					$date_anciennete = $obj_sal->date_anciennete;
				}
			}
		}
		// Si aucune date d'ancienneté n'existe, utiliser la date d'embauche
		if (empty($date_anciennete) && !empty($obj->dateemployment)) {
			$date_anciennete = $obj->dateemployment;
		}

        if (empty($date_anciennete))
            $date_anciennete = date('Y-m-d');

        if (!empty($date_anciennete)) {
            try {
                $date_donnee = new DateTime($date_anciennete);
                $aujourdhui = new DateTime();
                $interval = $date_donnee->diff($aujourdhui);
                $jours = $interval->days % 365;
                $solde = (int) (floor($jours / 30) * 2.5);
            } catch (Exception $e) {
                $solde = 0;
            }
        }

        $telephone = '';
        if (!empty($obj->office_phone)) {
            $telephone = $obj->office_phone;
        } elseif (!empty($obj->user_mobile)) {
            $telephone = $obj->user_mobile;
        } elseif (!empty($obj->office_fax)) {
            $telephone = $obj->office_fax;
        }

        $statut = !empty($obj->salrowid) ? 'Enregistré' : 'Non enregistré';

        $values = array(
            $numero,
            $obj->matricule ?: '',
            $obj->lastname ?: '',
            $obj->firstname ?: '',
            $obj->dateemployment ?: '',
            $obj->job ?: '',
            $categorie_label,
            $echelon_label,
            $salaire_base,
            $anciennete[0].' an(s)',
            $solde,
            $telephone,
            $obj->email ?: '',
            $statut
        );

        foreach ($values as $index => $value) {
            $col = Coordinate::stringFromColumnIndex($index + 1);
            $cell = $col.$row;
            $sheet->setCellValue($cell, $value);
            $sheet->getStyle($cell)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle($cell)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        }

        $row++;
        $numero++;
    }
}

if ($numero === 1) {
    $sheet->mergeCells('A6:N6');
    $sheet->setCellValue('A6', 'Aucun personnel trouvé');
    $sheet->getStyle('A6')->getFont()->setBold(true);
    $sheet->getStyle('A6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('A6')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
}

$last_col = Coordinate::stringFromColumnIndex(count($headers));
foreach (range('A', $last_col) as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

$sheet->freezePane('A6');
$sheet->getStyle('A5:'.$last_col.'5')->getAlignment()->setWrapText(true);

$filename = 'Liste_personnels_'.$nom_societe.'_'.date('Ymd_His').'.xlsx';
$filename = preg_replace('/[^A-Za-z0-9_\-.]/', '_', $filename);
if (substr($filename, -5) !== '.xlsx') {
    $filename .= '.xlsx';
}

$tmpdir = DOL_DATA_ROOT.'/paiementsalaire/temp';
if (!file_exists($tmpdir)) {
    mkdir($tmpdir, 0777, true);
}
$tmpfile = $tmpdir.'/liste_personnel_'.time().'_'.mt_rand(1000, 9999).'.xlsx';

$writer = new Xlsx($spreadsheet);
$writer->save($tmpfile);

while (ob_get_level()) {
    ob_end_clean();
}

header_remove();
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="'.$filename.'"');
header('Content-Length: '.filesize($tmpfile));
header('Cache-Control: max-age=0, must-revalidate');
header('Pragma: public');
header('Expires: 0');

readfile($tmpfile);
@unlink($tmpfile);

$db->close();
exit;
