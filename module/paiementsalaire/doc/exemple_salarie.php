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

                  //$sheet->setCellValue('A5', 'Modifier uniquement la valeur des cinq(5) dernières colonnes (D, E, F, G et H)');
            
                  //Partie User
            //colonnes Nom et Prénom
                  $sheet->setCellValue('A1', 'Identifiant');
                  $sheet->getStyle('A1')->getFont()->setBold(true);

                  $colonne_courante = 'A';
                  $numero_ligne = 1;

                  $nextcolonne = getNextColumnName($colonne_courante);
                  $sheet->setCellValue($nextcolonne.$numero_ligne, 'Nom');
                  $sheet->getStyle($nextcolonne.$numero_ligne)->getFont()->setBold(true);
                  $colonne_courante = $nextcolonne;

                  $nextcolonne = getNextColumnName($colonne_courante);
                  $sheet->setCellValue($nextcolonne.$numero_ligne, 'Prenom');
                  $sheet->getStyle($nextcolonne.$numero_ligne)->getFont()->setBold(true);
                  $colonne_courante = $nextcolonne;

                  $nextcolonne = getNextColumnName($colonne_courante);
                  $sheet->setCellValue($nextcolonne.$numero_ligne, 'Genre');
                  $sheet->getStyle($nextcolonne.$numero_ligne)->getFont()->setBold(true);
                  $colonne_courante = $nextcolonne;

                  $nextcolonne = getNextColumnName($colonne_courante);
                  $sheet->setCellValue($nextcolonne.$numero_ligne, 'Poste/Fonction');
                  $sheet->getStyle($nextcolonne.$numero_ligne)->getFont()->setBold(true);
                  $colonne_courante = $nextcolonne;

                  $nextcolonne = getNextColumnName($colonne_courante);
                  $sheet->setCellValue($nextcolonne.$numero_ligne, 'Date d\'embauche');
                  $sheet->getStyle($nextcolonne.$numero_ligne)->getFont()->setBold(true);
                  $colonne_courante = $nextcolonne;

                  //Partie salairePaie
                  $nextcolonne = getNextColumnName($colonne_courante);
                  $sheet->setCellValue($nextcolonne.$numero_ligne, 'ID Catégorie');
                  $sheet->getStyle($nextcolonne.$numero_ligne)->getFont()->setBold(true);
                  $colonne_courante = $nextcolonne;
                  
                  $nextcolonne = getNextColumnName($colonne_courante);
                  $sheet->setCellValue($nextcolonne.$numero_ligne, 'ID Echelon');
                  $sheet->getStyle($nextcolonne.$numero_ligne)->getFont()->setBold(true);
                  $colonne_courante = $nextcolonne;

                  $nextcolonne = getNextColumnName($colonne_courante);
                  $sheet->setCellValue($nextcolonne.$numero_ligne, 'Situation familiale');
                  $sheet->getStyle($nextcolonne.$numero_ligne)->getFont()->setBold(true);
                  $colonne_courante = $nextcolonne;

                  $nextcolonne = getNextColumnName($colonne_courante);
                  $sheet->setCellValue($nextcolonne.$numero_ligne, 'Nombre Enf');
                  $sheet->getStyle($nextcolonne.$numero_ligne)->getFont()->setBold(true);
                  $colonne_courante = $nextcolonne;

                  $nextcolonne = getNextColumnName($colonne_courante);
                  $sheet->setCellValue($nextcolonne.$numero_ligne, 'Nombre Enf-Handicapé');
                  $sheet->getStyle($nextcolonne.$numero_ligne)->getFont()->setBold(true);
                  $colonne_courante = $nextcolonne;

                  $nextcolonne = getNextColumnName($colonne_courante);
                  $sheet->setCellValue($nextcolonne.$numero_ligne, 'INPS');
                  $sheet->getStyle($nextcolonne.$numero_ligne)->getFont()->setBold(true);
                  $colonne_courante = $nextcolonne;

                  $nextcolonne = getNextColumnName($colonne_courante);
                  $sheet->setCellValue($nextcolonne.$numero_ligne, 'AMO');
                  $sheet->getStyle($nextcolonne.$numero_ligne)->getFont()->setBold(true);
                  $colonne_courante = $nextcolonne;

                  $nextcolonne = getNextColumnName($colonne_courante);
                  $sheet->setCellValue($nextcolonne.$numero_ligne, 'N° compte');
                  $sheet->getStyle($nextcolonne.$numero_ligne)->getFont()->setBold(true);
                  $colonne_courante = $nextcolonne;

                  $nextcolonne = getNextColumnName($colonne_courante);
                  $sheet->setCellValue($nextcolonne.$numero_ligne, 'Sursalaire');
                  $sheet->getStyle($nextcolonne.$numero_ligne)->getFont()->setBold(true);
                  $colonne_courante = $nextcolonne;

                  $nextcolonne = getNextColumnName($colonne_courante);
                  $sheet->setCellValue($nextcolonne.$numero_ligne, 'Date anciennete');
                  $sheet->getStyle($nextcolonne.$numero_ligne)->getFont()->setBold(true);
                  $colonne_courante = $nextcolonne;

                  $nextcolonne = getNextColumnName($colonne_courante);
                  $sheet->setCellValue($nextcolonne.$numero_ligne, 'ID de type banque');
                  $sheet->getStyle($nextcolonne.$numero_ligne)->getFont()->setBold(true);
                  $colonne_courante = $nextcolonne;
            
// Envoyer le fichier au navigateur
$filename = "Exemple_import_salarie.xlsx"; //le nom du fichier
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