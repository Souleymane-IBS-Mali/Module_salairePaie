<?php
// Inclure le chargement automatique de Composer
//require '../vendor/autoload.php';
require_once '../../main.inc.php';

//Import des fichiers spreadsheet implementé par dolibarr
require_once DOL_DOCUMENT_ROOT.'/includes/phpoffice/phpspreadsheet/src/autoloader.php';
require_once DOL_DOCUMENT_ROOT.'/includes/Psr/autoloader.php';
require_once PHPEXCELNEW_PATH.'Spreadsheet.php';
// Importer les classes nécessaires de PhpSpreadsheet
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

// Créer un nouvel objet Spreadsheet
$spreadsheet = new Spreadsheet();
// Définir les propriétés du document
$spreadsheet->getProperties()->setCreator("Votre Nom")
                             ->setLastModifiedBy("Votre Nom")
                             ->setTitle("Document de Test Dolibarr")
                             ->setSubject("Document de Test Dolibarr")
                             ->setDescription("Document de test généré pour Dolibarr en utilisant PhpSpreadsheet.")
                             ->setKeywords("dolibarr php phpspreadsheet")
                             ->setCategory("Fichier de test");

// Définir la valeur de la cellule fusionnée
//global $mois, $annee, $nom_soc;
$mois= GETPOST("mois", "int");
$annee= GETPOST("annee", "int");
$nom_soc= GETPOST("nom_soc", "int");
$id_societe= GETPOST("id_societe", "int");
$id_convention= GETPOST("id_convention", "int");

$mois_tab = array(" Janvier "," Février "," Mars "," Avril "," Mai "," Juin "," Juillet "," Août "," Septembre "," Octobre "," Novembre "," Décembre ");

// Ajouter des données
$sheet = $spreadsheet->getActiveSheet();
// Fusionner les cellules de A1 à B1
//$sheet->mergeCells('A1:A2');

// Renommer la feuille de calcul
$sheet->setTitle('Exemple');


      $sql = "SELECT sal.rowid as id, sal.matricule, sal.fk_user, u.rowid, u.lastname, u.firstname, u.dateemployment, ue.fk_object, ue.egp FROM ".MAIN_DB_PREFIX."salarie as sal";
      $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."user as u ON u.rowid=sal.fk_user";
      $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."user_extrafields as ue ON u.rowid=ue.fk_object";
      $sql .= " WHERE ue.egp=".$id_societe." AND ue.egp=".$id_societe;
      $res = $db->query($sql);
        if($res){
            $num = $db->num_rows($res);
            $i = 0;
            if($num > 0){
                  //$sheet->setCellValue('A5', 'Modifier uniquement la valeur des cinq(5) dernières colonnes (D, E, F, G et H)');
            
            //colonnes Nom et Prénom
                  $sheet->setCellValue('A1', 'ID');
                  $sheet->getStyle('A1')->getFont()->setBold(true);

                  $sheet->setCellValue('B1', 'Nom');
                  $sheet->getStyle('B1')->getFont()->setBold(true);

                  $sheet->setCellValue('C1', 'Prénom');
                  $sheet->getStyle('C1')->getFont()->setBold(true);

                  $sheet->setCellValue('D1', 'Libélé');
                  $sheet->getStyle('D1')->getFont()->setBold(true);

                  $sheet->setCellValue('E1', 'Montant');
                  $sheet->getStyle('E1')->getFont()->setBold(true);

                  $sheet->setCellValue('F1', 'Nombre de mois');
                  $sheet->getStyle('F1')->getFont()->setBold(true);

                  $sheet->setCellValue('G1', 'Mois debut');
                  $sheet->getStyle('G1')->getFont()->setBold(true);

                  $sheet->setCellValue('H1', 'Année debut');
                  $sheet->getStyle('H1')->getFont()->setBold(true);

                  $numero_ligne = 2;
                  while ($i < $num) {
                        $liste = $db->fetch_object($res);
                        $sheet->setCellValue('A'.$numero_ligne, $liste->id);

                        $sheet->setCellValue('B'.$numero_ligne, $liste->lastname);

                        $sheet->setCellValue('C'.$numero_ligne, $liste->firstname);

                        $sheet->setCellValue('D'.$numero_ligne, 'Une petite description');

                        $sheet->setCellValue('E'.$numero_ligne, 'entier > 0');

                        $sheet->setCellValue('F'.$numero_ligne, 'entier > 0');

                        $sheet->setCellValue('G'.$numero_ligne, 'entier > 0');

                        $sheet->setCellValue('H'.$numero_ligne, 'entier > 0');


                        $numero_ligne ++;
                        $i ++;
                  }

                  /*//Titres des colonnes
                  $sheet->setCellValue('I7', 'Pour "ID type hs" réferez-vous au tableau ci-dessous');

                  $sheet->setCellValue('I8', 'ID');
                  $sheet->getStyle('I8')->getFont()->setBold(true);
                  $sheet->setCellValue('J8', 'Taux');
                  $sheet->getStyle('J8')->getFont()->setBold(true);
                  $sheet->setCellValue('K8', 'Type hs');
                  $sheet->getStyle('K8')->getFont()->setBold(true);

                  //bordures
                  $sheet->getStyle('I8')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                  $sheet->getStyle('J8')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                  $sheet->getStyle('K8')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

                  //$colonne_courante = "J";
                  $heure_sup = "SELECT * FROM ".MAIN_DB_PREFIX."heure_sup WHERE fk_convention=".$id_convention." OR fk_societe=".$id_societe;
                  $heure_sup .= " ORDER BY rowid";
			$result_heure_sup = $db->query($heure_sup);//= $db->query($covSql);
			if($result_heure_sup){
				$i = 0;
				$num = $db->num_rows($result_heure_sup);
                        $numero_ligne = 9;
				while ($i < $num){
					$obj_heure_sup = $db->fetch_object($result_heure_sup);
                              //$nextcolonne = getNextColumnName($colonne_courante);
                              $sheet->setCellValue("I".$numero_ligne, $obj_heure_sup->rowid);

                              $taux = explode('%', $obj_heure_sup->taux)[0].'%';
                              $sheet->setCellValue("J".$numero_ligne, $taux);

                              $sheet->setCellValue("K".$numero_ligne, $obj_heure_sup->commentaire);

                              $sheet->getStyle('I'.$numero_ligne)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                              $sheet->getStyle('J'.$numero_ligne)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                              $sheet->getStyle('K'.$numero_ligne)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);


                              //$colonne_courante = $nextcolonne;
                              $numero_ligne ++;
                              $i ++;
                        }
                  }*/

            }else{
                  $sheet->mergeCells('A7:H8');
                  $sheet->setCellValue('A7', "Aucune information Disponible");

            }
        }else{
            $sheet->mergeCells('A7:H8');
            $sheet->setCellValue('A7', "Aucune information Disponible");
        }

// Envoyer le fichier au navigateur
$filename = "Exemple_avance_acompte".gmdate('D, d M Y H:i:s'); //le nom du fichier
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="'.$filename.'"');
header('Cache-Control: max-age=0');
header('Cache-Control: max-age=1');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // Always modified
header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
header('Pragma: public'); // HTTP/1.0

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');


function getNextColumnName($currentColumnName) {
      // Convertir le nom de la colonne en index de colonne
      $currentColumnIndex = Coordinate::columnIndexFromString($currentColumnName);
      
      // Obtenir l'index de la colonne suivante
      $nextColumnIndex = $currentColumnIndex + 1;
      
      // Convertir l'index de la colonne suivante en nom de colonne
      $nextColumnName = Coordinate::stringFromColumnIndex($nextColumnIndex);
      
      return $nextColumnName;
  }