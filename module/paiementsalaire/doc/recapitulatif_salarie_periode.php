<?php
require_once '../../main.inc.php';

// Import des fichiers spreadsheet implementes par Dolibarr
require_once DOL_DOCUMENT_ROOT.'/includes/phpoffice/phpspreadsheet/src/autoloader.php';
require_once DOL_DOCUMENT_ROOT.'/includes/Psr/autoloader.php';
require_once PHPEXCELNEW_PATH.'Spreadsheet.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

$fk_salarie = GETPOST('fk_salarie', 'int');
$id_societe = GETPOST('id_societe', 'int');

$mois_debut = GETPOST('mois_debut', 'int');
$annee_debut = GETPOST('annee_debut', 'int');
$mois_fin = GETPOST('mois_fin', 'int');
$annee_fin = GETPOST('annee_fin', 'int');

// Options d'affichage, identiques a l'ancien recapitulatif annuel
$fonction = GETPOST('fonction', 'alpha');
$banque = GETPOST('banque', 'alpha');
$compte = GETPOST('compte', 'alpha');
$categorie = GETPOST('categorie', 'alpha');
$situation_matrimoniale = GETPOST('situation_matrimoniale', 'alpha');

if (empty($fk_salarie) || empty($id_societe)) {
    dol_print_error($db, 'Parametres manquants : fk_salarie ou id_societe');
    exit;
}

if (empty($mois_debut)) {
    $mois_debut = 1;
}
if (empty($mois_fin)) {
    $mois_fin = 12;
}
if (empty($annee_debut)) {
    $annee_debut = (int) date('Y');
}
if (empty($annee_fin)) {
    $annee_fin = $annee_debut;
}

$mois_debut = max(1, min(13, (int) $mois_debut));
$mois_fin = max(1, min(13, (int) $mois_fin));
$annee_debut = (int) $annee_debut;
$annee_fin = (int) $annee_fin;

$periode_debut = ($annee_debut * 100) + $mois_debut;
$periode_fin = ($annee_fin * 100) + $mois_fin;

if ($periode_debut > $periode_fin) {
    $tmp = $periode_debut;
    $periode_debut = $periode_fin;
    $periode_fin = $tmp;

    $tmpMois = $mois_debut;
    $tmpAnnee = $annee_debut;
    $mois_debut = $mois_fin;
    $annee_debut = $annee_fin;
    $mois_fin = $tmpMois;
    $annee_fin = $tmpAnnee;
}

$mois_tab = array(
    1 => 'Janvier',
    2 => 'Fevrier',
    3 => 'Mars',
    4 => 'Avril',
    5 => 'Mai',
    6 => 'Juin',
    7 => 'Juillet',
    8 => 'Aout',
    9 => 'Septembre',
    10 => 'Octobre',
    11 => 'Novembre',
    12 => 'Decembre',
    13 => '13e Mois'
);

$where_periode = ' AND ((annee * 100) + mois) BETWEEN '.$periode_debut.' AND '.$periode_fin;
$where_base = ' fk_salarie='.$fk_salarie.' AND fk_societe='.$id_societe.$where_periode;

$bulletin_sql = 'SELECT * FROM '.MAIN_DB_PREFIX.'bulletin WHERE '.$where_base.' ORDER BY annee ASC, mois ASC, rowid ASC';
$res_bulletin = $db->query($bulletin_sql);

$bulletins = array();
$nom_salarie = '';
if ($res_bulletin) {
    while ($obj_bulletin = $db->fetch_object($res_bulletin)) {
        $bulletins[] = $obj_bulletin;
        if (empty($nom_salarie)) {
            $nom_salarie = trim(($obj_bulletin->prenom ?: '').' '.($obj_bulletin->nom ?: ''));
        }
    }
}

if (empty($nom_salarie)) {
    $sql_sal = 'SELECT u.firstname, u.lastname FROM '.MAIN_DB_PREFIX.'salarie s LEFT JOIN '.MAIN_DB_PREFIX.'user u ON u.rowid=s.fk_user WHERE s.rowid='.$fk_salarie;
    $res_sal = $db->query($sql_sal);
    if ($res_sal && ($obj_sal = $db->fetch_object($res_sal))) {
        $nom_salarie = trim(($obj_sal->firstname ?: '').' '.($obj_sal->lastname ?: ''));
    }
}
if (empty($nom_salarie)) {
    $nom_salarie = 'Salarie_'.$fk_salarie;
}

$spreadsheet = new Spreadsheet();
$spreadsheet->getProperties()
    ->setCreator('Dolibarr')
    ->setLastModifiedBy('Dolibarr')
    ->setTitle('Recapitulatif par periode')
    ->setSubject('Recapitulatif par periode')
    ->setDescription('Recapitulatif de paie genere par periode')
    ->setKeywords('dolibarr php phpspreadsheet recapitulatif paie periode')
    ->setCategory('Récapitulatif');

$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Recapitulatif');

$periode_label = ($mois_tab[$mois_debut] ?: $mois_debut).' '.$annee_debut.' au '.($mois_tab[$mois_fin] ?: $mois_fin).' '.$annee_fin;
$titre = 'RECAPITULATIF DE '.strtoupper($nom_salarie).' - PERIODE : '.strtoupper($periode_label);

$sheet->mergeCells('A1:H3');
$sheet->setCellValue('A1', $titre);
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('A1')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
$sheet->getStyle('A1')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

$headers = array();
$headers[] = array('key' => 'mois', 'label' => 'Mois');
$headers[] = array('key' => 'annee', 'label' => 'Annee');

if ($categorie) {
    $headers[] = array('key' => 'categorie', 'label' => 'CATEGORIE');
}
if ($situation_matrimoniale) {
    $headers[] = array('key' => 'situation_matrimoniale', 'label' => 'SITUAT. MAT');
}
if ($fonction) {
    $headers[] = array('key' => 'fonction', 'label' => 'FONCTION');
}
if ($banque) {
    $headers[] = array('key' => 'banque', 'label' => 'BANQUE');
}
if ($compte) {
    $headers[] = array('key' => 'compte', 'label' => 'N° COMPTE');
}

$headers[] = array('key' => 'jours_trav', 'label' => 'JOURS TRAV.');
$headers[] = array('key' => 'taux', 'label' => 'TAUX');
$headers[] = array('key' => 'salaire_base', 'label' => 'SAL. BASE');
$headers[] = array('key' => 'sursalaire', 'label' => 'SURSALAIRE');

// Colonnes dynamiques basees uniquement sur les bulletins de la periode
$array_anciennete = array();
$array_primes = array();
$array_indemnites = array();
$array_primes_exceptionnelles = array();
$array_heures_sup = array();
$array_avances = array();

foreach ($bulletins as $bulletin) {
    collectDistinctLibelles($db, 'bulletin_anciennete', 'libelle', 'fk_bulletin='.$bulletin->rowid, $array_anciennete);
    collectDistinctLibelles($db, 'bulletin_prime', 'libelle', 'fk_bulletin='.$bulletin->rowid, $array_primes);
    collectDistinctLibelles($db, 'bulletin_indemnite', 'libelle', 'fk_bulletin='.$bulletin->rowid, $array_indemnites);
    collectDistinctLibelles($db, 'bulletin_prime_exceptionnelle', 'libelle', 'fk_bulletin='.$bulletin->rowid, $array_primes_exceptionnelles);
    collectDistinctLibelles($db, 'bulletin_heure_sup', 'libelle', 'fk_bulletin='.$bulletin->rowid, $array_heures_sup);
    collectDistinctLibelles($db, 'bulletin_avance', 'libelle', 'fk_bulletin='.$bulletin->rowid, $array_avances);
}

foreach ($array_anciennete as $libelle) {
    $headers[] = array('key' => 'anc_'.$libelle, 'label' => strtoupper($libelle));
}
foreach ($array_primes as $libelle) {
    $headers[] = array('key' => 'prime_'.$libelle, 'label' => strtoupper($libelle));
}
foreach ($array_indemnites as $libelle) {
    $headers[] = array('key' => 'ind_'.$libelle, 'label' => strtoupper($libelle));
}
foreach ($array_primes_exceptionnelles as $libelle) {
    $headers[] = array('key' => 'prime_exc_'.$libelle, 'label' => strtoupper($libelle));
}
foreach ($array_heures_sup as $libelle) {
    $headers[] = array('key' => 'hs_nb_'.$libelle, 'label' => 'Nombre HS '.$libelle);
    $headers[] = array('key' => 'hs_montant_'.$libelle, 'label' => 'Montant HS '.$libelle);
}

$headers[] = array('key' => 'salaire_brut', 'label' => 'SAL. BRUT');
$headers[] = array('key' => 'salaire_cotisable', 'label' => 'SAL. COTIS');
$headers[] = array('key' => 'salaire_imposable', 'label' => 'SAL. IMP.');
$headers[] = array('key' => 'inps_sal', 'label' => 'INPS SAL.');
$headers[] = array('key' => 'inps_patro', 'label' => 'INPS PATRO.');
$headers[] = array('key' => 'its', 'label' => 'ITS.');
$headers[] = array('key' => 'amo_sal', 'label' => 'AMO SAL.');
$headers[] = array('key' => 'amo_patro', 'label' => 'AMO PATRO.');
$headers[] = array('key' => 'base_cfe', 'label' => 'BASE CFE');
$headers[] = array('key' => 'montant_cfe', 'label' => 'MONTANT CFE.');
$headers[] = array('key' => 'base_tl', 'label' => 'BASE TL.');
$headers[] = array('key' => 'montant_tl', 'label' => 'MONTANT TL.');
$headers[] = array('key' => 'salaire_net', 'label' => 'SALAIRE NET');

foreach ($array_avances as $libelle) {
    $headers[] = array('key' => 'avance_'.$libelle, 'label' => strtoupper($libelle));
}

$headers[] = array('key' => 'total_avance', 'label' => 'TOTAL AVANCE');
$headers[] = array('key' => 'net_paye', 'label' => 'NET PAYE');
$headers[] = array('key' => 'cout', 'label' => 'COUT');

$header_row = 6;
$col_index = 1;
foreach ($headers as $header) {
    $cell = Coordinate::stringFromColumnIndex($col_index).$header_row;
    $sheet->setCellValue($cell, $header['label']);
    $sheet->getStyle($cell)->getFont()->setBold(true);
    $sheet->getStyle($cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle($cell)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE6E6E6');
    $sheet->getStyle($cell)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    $col_index++;
}

$row = 7;
$totals = array();

if (count($bulletins) > 0) {
    foreach ($bulletins as $bulletin) {
        $cout = 0;
        $col_index = 1;

        $nb_jours = getScalar($db, 'SELECT jour FROM '.MAIN_DB_PREFIX.'salarie_nombre_jour_travaille WHERE annee='.(int) $bulletin->annee.' AND mois='.(int) $bulletin->mois.' AND fk_salarie='.$fk_salarie, 'jour', 0);
        $nb_total_jour = ($bulletin->mois == 13) ? 30 : cal_days_in_month(CAL_GREGORIAN, (int) $bulletin->mois, (int) $bulletin->annee);
        $taux = 100;
        if ($nb_total_jour > 0 && $nb_jours != $nb_total_jour) {
            $taux = round(($nb_jours * 100) / $nb_total_jour, 2);
        }

        $inps_sal = getScalar($db, 'SELECT SUM(montant_employe) as montant FROM '.MAIN_DB_PREFIX.'bulletin_cotisation WHERE fk_bulletin='.$bulletin->rowid.' AND fk_cotisation<>6', 'montant', 0);
        $inps_patro = getScalar($db, 'SELECT SUM(montant_employeur) as montant FROM '.MAIN_DB_PREFIX.'bulletin_cotisation WHERE fk_bulletin='.$bulletin->rowid.' AND fk_cotisation<>6', 'montant', 0);
        $its = getScalar($db, 'SELECT SUM(montant) as montant FROM '.MAIN_DB_PREFIX.'bulletin_taxe WHERE fk_bulletin='.$bulletin->rowid, 'montant', 0);
        $amo_sal = getScalar($db, 'SELECT SUM(montant_employe) as montant FROM '.MAIN_DB_PREFIX.'bulletin_cotisation WHERE fk_bulletin='.$bulletin->rowid.' AND fk_cotisation=6', 'montant', 0);
        $amo_patro = getScalar($db, 'SELECT SUM(montant_employeur) as montant FROM '.MAIN_DB_PREFIX.'bulletin_cotisation WHERE fk_bulletin='.$bulletin->rowid.' AND fk_cotisation=6', 'montant', 0);
        $montant_cfe = getScalar($db, 'SELECT SUM(montant_employeur) as montant FROM '.MAIN_DB_PREFIX.'bulletin_taxe2 WHERE taux_employeur=3.5 AND fk_bulletin='.$bulletin->rowid, 'montant', 0);
        $montant_tl = getScalar($db, 'SELECT SUM(montant_employeur) as montant FROM '.MAIN_DB_PREFIX.'bulletin_taxe2 WHERE taux_employeur=1 AND fk_bulletin='.$bulletin->rowid, 'montant', 0);

        $cout = ($bulletin->salaire_brut ?: 0) + $inps_patro + $amo_patro + $montant_cfe + $montant_tl;

        foreach ($headers as $header) {
            $key = $header['key'];
            $value = '';

            switch ($key) {
                case 'mois':
                    $value = isset($mois_tab[(int) $bulletin->mois]) ? $mois_tab[(int) $bulletin->mois] : $bulletin->mois;
                    break;
                case 'annee':
                    $value = $bulletin->annee;
                    break;
                case 'categorie':
                    $value = $bulletin->categorie;
                    if (!empty($bulletin->echelon) && $bulletin->echelon != 'N/A') {
                        $value .= '->'.$bulletin->echelon;
                    }
                    break;
                case 'situation_matrimoniale':
                    $value = trim(($bulletin->situation_familiale ?: '').' '.($bulletin->nombre_enfant ?: '0').' '.($bulletin->nombre_enfant_hand ?: '0'));
                    break;
                case 'fonction':
                    $value = $bulletin->fonction;
                    break;
                case 'banque':
                    $value = $bulletin->banque;
                    break;
                case 'compte':
                    $value = $bulletin->compte;
                    break;
                case 'jours_trav':
                    $value = $nb_jours;
                    break;
                case 'taux':
                    $value = $taux.'%';
                    break;
                case 'salaire_base':
                    $value = round($bulletin->salaire_base ?: 0);
                    break;
                case 'sursalaire':
                    $value = round($bulletin->sursalaire ?: 0);
                    break;
                case 'salaire_brut':
                    $value = round($bulletin->salaire_brut ?: 0);
                    break;
                case 'salaire_cotisable':
                    $value = round($bulletin->salaire_brut_cotisable ?: 0);
                    break;
                case 'salaire_imposable':
                    $value = round($bulletin->salaire_brut_imposable ?: 0);
                    break;
                case 'inps_sal':
                    $value = round($inps_sal);
                    break;
                case 'inps_patro':
                    $value = round($inps_patro);
                    break;
                case 'its':
                    $value = round($its);
                    break;
                case 'amo_sal':
                    $value = round($amo_sal);
                    break;
                case 'amo_patro':
                    $value = round($amo_patro);
                    break;
                case 'base_cfe':
                    $value = round($bulletin->salaire_brut ?: 0);
                    break;
                case 'montant_cfe':
                    $value = round($montant_cfe);
                    break;
                case 'base_tl':
                    $value = round($bulletin->salaire_brut ?: 0);
                    break;
                case 'montant_tl':
                    $value = round($montant_tl);
                    break;
                case 'salaire_net':
                    $value = round($bulletin->net_payer ?: 0);
                    break;
                case 'total_avance':
                    $value = round(getScalar($db, 'SELECT SUM(montant) as montant FROM '.MAIN_DB_PREFIX.'bulletin_avance WHERE fk_bulletin='.$bulletin->rowid, 'montant', 0));
                    break;
                case 'net_paye':
                    $total_avance = getScalar($db, 'SELECT SUM(montant) as montant FROM '.MAIN_DB_PREFIX.'bulletin_avance WHERE fk_bulletin='.$bulletin->rowid, 'montant', 0);
                    $value = round(($bulletin->net_payer ?: 0) - $total_avance);
                    break;
                case 'cout':
                    $value = round($cout);
                    break;
                default:
                    if (strpos($key, 'anc_') === 0) {
                        $libelle = substr($key, 4);
                        $tauxAnc = getScalar($db, 'SELECT taux FROM '.MAIN_DB_PREFIX.'bulletin_anciennete WHERE fk_bulletin='.$bulletin->rowid.' AND libelle="'.$db->escape($libelle).'"', 'taux', 0);
                        $value = round(($bulletin->salaire_base ?: 0) * $tauxAnc / 100);
                    } elseif (strpos($key, 'prime_') === 0 && strpos($key, 'prime_exc_') !== 0) {
                        $libelle = substr($key, 6);
                        $value = round(getScalar($db, 'SELECT SUM(montant) as montant FROM '.MAIN_DB_PREFIX.'bulletin_prime WHERE fk_bulletin='.$bulletin->rowid.' AND libelle="'.$db->escape($libelle).'"', 'montant', 0));
                    } elseif (strpos($key, 'ind_') === 0) {
                        $libelle = substr($key, 4);
                        $value = round(getScalar($db, 'SELECT SUM(montant) as montant FROM '.MAIN_DB_PREFIX.'bulletin_indemnite WHERE fk_bulletin='.$bulletin->rowid.' AND libelle="'.$db->escape($libelle).'"', 'montant', 0));
                    } elseif (strpos($key, 'prime_exc_') === 0) {
                        $libelle = substr($key, 10);
                        $value = round(getScalar($db, 'SELECT SUM(montant) as montant FROM '.MAIN_DB_PREFIX.'bulletin_prime_exceptionnelle WHERE fk_bulletin='.$bulletin->rowid.' AND libelle="'.$db->escape($libelle).'"', 'montant', 0));
                    } elseif (strpos($key, 'hs_nb_') === 0) {
                        $libelle = substr($key, 6);
                        $value = getScalar($db, 'SELECT SUM(nombre_heure_sup) as montant FROM '.MAIN_DB_PREFIX.'bulletin_heure_sup WHERE fk_bulletin='.$bulletin->rowid.' AND libelle="'.$db->escape($libelle).'"', 'montant', 0);
                    } elseif (strpos($key, 'hs_montant_') === 0) {
                        $libelle = substr($key, 11);
                        $value = round(getScalar($db, 'SELECT SUM(montant) as montant FROM '.MAIN_DB_PREFIX.'bulletin_heure_sup WHERE fk_bulletin='.$bulletin->rowid.' AND libelle="'.$db->escape($libelle).'"', 'montant', 0));
                    } elseif (strpos($key, 'avance_') === 0) {
                        $libelle = substr($key, 7);
                        $value = round(getScalar($db, 'SELECT SUM(montant) as montant FROM '.MAIN_DB_PREFIX.'bulletin_avance WHERE fk_bulletin='.$bulletin->rowid.' AND libelle="'.$db->escape($libelle).'"', 'montant', 0));
                    }
                    break;
            }

            $cell = Coordinate::stringFromColumnIndex($col_index).$row;
            $sheet->setCellValue($cell, $value);
            $sheet->getStyle($cell)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

            if (is_numeric($value)) {
                if (!isset($totals[$col_index])) {
                    $totals[$col_index] = 0;
                }
                $totals[$col_index] += $value;
            }

            $col_index++;
        }

        $row++;
    }

    // Ligne des totaux
    $sheet->setCellValue('A'.$row, 'TOTAUX');
    $sheet->getStyle('A'.$row)->getFont()->setBold(true);
    $sheet->getStyle('A'.$row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD9D9D9');
    $sheet->getStyle('A'.$row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

    for ($c = 2; $c <= count($headers); $c++) {
        $cell = Coordinate::stringFromColumnIndex($c).$row;
        if (isset($totals[$c])) {
            $sheet->setCellValue($cell, round($totals[$c]));
        }
        $sheet->getStyle($cell)->getFont()->setBold(true);
        $sheet->getStyle($cell)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD9D9D9');
        $sheet->getStyle($cell)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    }
} else {
    $sheet->mergeCells('A7:H8');
    $sheet->setCellValue('A7', 'Aucune information disponible pour cette periode');
    $sheet->getStyle('A7')->getFont()->setBold(true);
    $sheet->getStyle('A7')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
}

// Ajustement largeur colonnes
$last_col = Coordinate::stringFromColumnIndex(count($headers));
foreach (range('A', $last_col) as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

$sheet->freezePane('A7');

// Enregistrement dans les logs
$sql_select = 'SELECT firstname, lastname FROM '.MAIN_DB_PREFIX.'user WHERE rowid='.$user->id;
$res_user = $db->query($sql_select);
$obj_user_log = $res_user ? $db->fetch_object($res_user) : null;

$sql_soc = 'SELECT nom FROM '.MAIN_DB_PREFIX.'societe WHERE rowid='.$id_societe;
$res_soc = $db->query($sql_soc);
$obj_soc = $res_soc ? $db->fetch_object($res_soc) : null;

$action_effectue = 'Generation du recapitulatif par periode de '.$nom_salarie.' de '.$periode_label;
if ($obj_soc) {
    $action_effectue .= ' de la societe '.$obj_soc->nom;
}

$sql_log = 'INSERT INTO '.MAIN_DB_PREFIX.'log (fk_user, nom, prenom, quand, action_effectue, object_concerne)';
$sql_log .= ' VALUES('.$user->id.', "'.$db->escape($obj_user_log ? $obj_user_log->lastname : '').'", "'.$db->escape($obj_user_log ? $obj_user_log->firstname : '').'", now(), "'.$db->escape($action_effectue).'", "Récapitulatif par période")';
$db->query($sql_log);

$filename = 'Recapitulatif_'.$nom_salarie.'_'.$annee_debut.'_'.$mois_debut.'_au_'.$annee_fin.'_'.$mois_fin;
$filename = preg_replace('/[^A-Za-z0-9_\-]/', '_', $filename);

$tmpdir = DOL_DATA_ROOT.'/paiementsalaire/temp';

            if (!file_exists($tmpdir)) {
            mkdir($tmpdir, 0777, true);
            }

            $tmpfile = $tmpdir.'/Recapitulatif_'.time().'.xlsx';

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

            $db->free($resql);
            $db->close();

function collectDistinctLibelles($db, $table, $field, $where, &$array)
{
    $sql = 'SELECT DISTINCT '.$field.' as libelle FROM '.MAIN_DB_PREFIX.$table.' WHERE '.$where.' AND '.$field.' IS NOT NULL AND '.$field.' <> ""';
    $res = $db->query($sql);
    if ($res) {
        while ($obj = $db->fetch_object($res)) {
            if ($obj && !empty($obj->libelle) && !in_array($obj->libelle, $array)) {
                $array[] = $obj->libelle;
            }
        }
    }
}

function getScalar($db, $sql, $field, $default = 0)
{
    $res = $db->query($sql);
    if ($res) {
        $obj = $db->fetch_object($res);
        if ($obj && isset($obj->$field) && $obj->$field !== null) {
            return $obj->$field;
        }
    }
    return $default;
}
