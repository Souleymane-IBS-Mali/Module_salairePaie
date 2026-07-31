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
$id_societe= GETPOST("id_societe", "int");

$mois_tab = array(" Janvier "," Février "," Mars "," Avril "," Mai "," Juin "," Juillet "," Août "," Septembre "," Octobre "," Novembre "," Décembre ", " 13è Mois ");

if($mois != 12){
      $soc_sql = "SELECT nom FROM ".MAIN_DB_PREFIX."societe WHERE rowid=".$id_societe;
      $res_soc = $db->query($soc_sql);
      if($res_soc)
            $nom_soc = $db->fetch_object($res_soc)->nom;
}else{
      $soc_sql = "SELECT soc.nom, salp.afficher_regularisation_its FROM ".MAIN_DB_PREFIX."societe as soc";
      $soc_sql .= " LEFT JOIN ".MAIN_DB_PREFIX."salairepaie_societe as salp on soc.rowid = salp.fk_societe WHERE soc.rowid=".$id_societe;
      $res_soc = $db->query($soc_sql);
      if($res_soc)
            $obj_soc = $db->fetch_object($res_soc);
      $nom_soc = $obj_soc->nom;
      $affiche_reg_its = $obj_soc->afficher_regularisation_its;
}
// Ajouter des données
$sheet = $spreadsheet->getActiveSheet();
// Fusionner les cellules de A1 à B1
$sheet->mergeCells('A1:H3');
//$sheet->mergeCells('A1:A2');

$info = "ETAT DE SALAIRE DE".strtoupper($mois_tab[$mois-1]).$annee." DE ".strtoupper($nom_soc);
$sheet->setCellValue('A1', $info);
$sheet->getStyle('A1')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
      

      // Appliquer du style à la cellule fusionnée (optionnel)
$sheet->getStyle('A1')->getFont()->setBold(true);
$sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('A1')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);


// Renommer la feuille de calcul
$sheet->setTitle('Exemple');

$bulletin_sql = "SELECT * FROM ".MAIN_DB_PREFIX."bulletin_regularisation_its WHERE annee=".$annee." AND mois=".$mois." AND fk_societe=".$id_societe;
      $bulletin_sql .= " ORDER BY rowid";
      $res_bulletin = $db->query($bulletin_sql);
        if($res_bulletin)
            if($db->num_rows($res_bulletin)>0){
            //Les titre des colonnes
            //Matricule
                  $sheet->setCellValue('A6', 'MATRICULE');
                  $sheet->getStyle('A6')->getFont()->setBold(true);

                  $colonne_courante = "A";

                  //Prénom
                  $nextcolonne = getNextColumnName($colonne_courante);
                  $sheet->setCellValue($nextcolonne.'6', 'PRENOM');
                  $sheet->getStyle($nextcolonne.'6')->getFont()->setBold(true);
                  $colonne_courante = $nextcolonne;

                  //NOM
                  $nextcolonne = getNextColumnName($colonne_courante);
                  $sheet->setCellValue($nextcolonne.'6', 'NOM');
                  $sheet->getStyle($nextcolonne.'6')->getFont()->setBold(true);
                  $colonne_courante = $nextcolonne;

                  //Situation matrimoniale
                  $nextcolonne = getNextColumnName($colonne_courante);
                  $sheet->setCellValue($nextcolonne.'6', 'SITUAT. MAT');
                  $sheet->getStyle($nextcolonne.'6')->getFont()->setBold(true);
                  $colonne_courante = $nextcolonne;

                  //Fonction
                  $nextcolonne = getNextColumnName($colonne_courante);
                  $sheet->setCellValue($nextcolonne.'6', 'FONCTION');
                  $sheet->getStyle($nextcolonne.'6')->getFont()->setBold(true);
                  $colonne_courante = $nextcolonne;
                  
                  //Salaire brut
                  $nextcolonne = getNextColumnName($colonne_courante);
                  $sheet->setCellValue($nextcolonne."6", "Mt salaire et autres retrib.Brut");
                  $sheet->getStyle($nextcolonne."6")->getFont()->setBold(true);
                  $colonne_courante = $nextcolonne;

                  //Salaire brut Cotisable
                  $nextcolonne = getNextColumnName($colonne_courante);
                  $sheet->setCellValue($nextcolonne."6", "Retraite");
                  $sheet->getStyle($nextcolonne."6")->getFont()->setBold(true);
                  $colonne_courante = $nextcolonne;

                  //Salaire brut Imp
                  $nextcolonne = getNextColumnName($colonne_courante);
                  $sheet->setCellValue($nextcolonne."6", "Allocations & Indem. non Imposables");
                  $sheet->getStyle($nextcolonne."6")->getFont()->setBold(true);
                  $colonne_courante = $nextcolonne;

                  //INPS SALARIALE
                  $nextcolonne = getNextColumnName($colonne_courante);
                  $sheet->setCellValue($nextcolonne."6", "Montages réel   Avantages   En Nature");
                  $sheet->getStyle($nextcolonne."6")->getFont()->setBold(true);
                  $colonne_courante = $nextcolonne;

                  //INPS PATRONALE
                  $nextcolonne = getNextColumnName($colonne_courante);
                  $sheet->setCellValue($nextcolonne."6", "Base Imposition");
                  $sheet->getStyle($nextcolonne."6")->getFont()->setBold(true);
                  $colonne_courante = $nextcolonne;

                  //ITS
                  $nextcolonne = getNextColumnName($colonne_courante);
                  $sheet->setCellValue($nextcolonne."6", "Impôt Retenu");
                  $sheet->getStyle($nextcolonne."6")->getFont()->setBold(true);
                  $colonne_courante = $nextcolonne;

                  //AMO
                  $nextcolonne = getNextColumnName($colonne_courante);
                  $sheet->setCellValue($nextcolonne."6", "Impôt Calculé");
                  $sheet->getStyle($nextcolonne."6")->getFont()->setBold(true);
                  $colonne_courante = $nextcolonne;

                  //AMO Patronale
                  $nextcolonne = getNextColumnName($colonne_courante);
                  $sheet->setCellValue($nextcolonne."6", "Solde Impôt");
                  $sheet->getStyle($nextcolonne."6")->getFont()->setBold(true);
                  $colonne_courante = $nextcolonne;

                  //Base CFE
                  $nextcolonne = getNextColumnName($colonne_courante);
                  $sheet->setCellValue($nextcolonne."6", "Nbre mois");
                  $sheet->getStyle($nextcolonne."6")->getFont()->setBold(true);
                  $colonne_courante = $nextcolonne;


//--------------------------------------------------------------------
            //Affichage des valeurs
            $numero_ligne = 7;
            $sql_reg = "SELECT reg.*, bul.rowid, bul.matricule, bul.nom, bul.prenom, bul.situation_familiale, bul.nombre_enfant, bul.nombre_enfant_hand, bul.fonction";
            $sql_reg .= " FROM ".MAIN_DB_PREFIX."bulletin_regularisation_its AS reg";
            $sql_reg .= " INNER JOIN ".MAIN_DB_PREFIX."bulletin AS bul";
            $sql_reg .= " ON bul.fk_salarie = reg.fk_salarie AND bul.fk_societe = reg.fk_societe AND bul.annee = ".$annee_rechercher." AND bul.mois = 12";
            $sql_reg .= " WHERE reg.annee = ".$annee_rechercher." AND reg.mois = 12";
            $sql_reg .= " ORDER BY bul.nom";

            $res_bulletin = $db->query($sql_reg);
                  if($res_bulletin){
                        $num_all = $db->num_rows($res_bulletin);
                        while ($obj_bulletin = $db->fetch_object($res_bulletin)){
                              $cout = 0;
                        //Objet Utilisateur
                              //Ajout d'une nouvelle ligne
                              $sheet->insertNewRowBefore($numero_ligne+2, $numero_ligne);

                              //Matricule
                              $sheet->setCellValue('A'.$numero_ligne, $obj_bulletin->matricule);

                              $colonne_courante = "A";
                              //Ajout du nom et prénom
                              $nextcolonne = getNextColumnName($colonne_courante);
                              $sheet->setCellValue($nextcolonne.$numero_ligne, $obj_bulletin->nom);
                              $colonne_courante = $nextcolonne;
                                

                              $nextcolonne = getNextColumnName($colonne_courante);
                              $sheet->setCellValue($nextcolonne.$numero_ligne, $obj_bulletin->prenom);
                              $colonne_courante = $nextcolonne;


                              //Ajout la situation familiale
                              $stuati_fa = $obj_bulletin->situation_familiale.' '.$obj_bulletin->nombre_enfant.' '.$obj_bulletin->nombre_enfant_hand;
                              $nextcolonne = getNextColumnName($colonne_courante);
                              $sheet->setCellValue($nextcolonne.$numero_ligne, $stuati_fa);
                              $colonne_courante = $nextcolonne;

                              //Fonction
                              $nextcolonne = getNextColumnName($colonne_courante);
                              $sheet->setCellValue($nextcolonne.$numero_ligne, $obj_bulletin->fonction);
                              $colonne_courante = $nextcolonne; 

                              //Salaire brut
                              $nextcolonne = getNextColumnName($colonne_courante);
                              $sheet->setCellValue($nextcolonne.$numero_ligne, round($obj_bulletin->somme_brut_annuel));
                              $colonne_courante = $nextcolonne;

                              $cout += $obj_bulletin->salaire_brut;

                              //Salaire brut cotisable
                              $nextcolonne = getNextColumnName($colonne_courante);
                              $sheet->setCellValue($nextcolonne.$numero_ligne, round($obj_bulletin->somme_retraite));
                              $colonne_courante = $nextcolonne;

                              //Salaire brut imposable
                              $nextcolonne = getNextColumnName($colonne_courante);
                              $sheet->setCellValue($nextcolonne.$numero_ligne, round($obj_bulletin->prime_indemnite_non_imposable));
                              $colonne_courante = $nextcolonne;

                              //Montant des cotisation salariale
                              $nextcolonne = getNextColumnName($colonne_courante);
                              $sheet->setCellValue($nextcolonne.$numero_ligne, round($obj_bulletin->avantage_nature));
                              $colonne_courante = $nextcolonne;
                              
                              //Montant des cotisation salariale
                              $nextcolonne = getNextColumnName($colonne_courante);
                              $sheet->setCellValue($nextcolonne.$numero_ligne, round($obj_bulletin->somme_brut_imposable_annuel));
                              $colonne_courante = $nextcolonne;

                              //Base CFE
                              $nextcolonne = getNextColumnName($colonne_courante);
                              $sheet->setCellValue($nextcolonne.$numero_ligne, round($obj_bulletin->somme_its_mois));
                              $colonne_courante = $nextcolonne;

                              //Base TL
                              $nextcolonne = getNextColumnName($colonne_courante);
                              $sheet->setCellValue($nextcolonne.$numero_ligne, round($obj_bulletin->its_annuel));
                              $colonne_courante = $nextcolonne;

                              //Salaire Net
                              $nextcolonne = getNextColumnName($colonne_courante);
                              $sheet->setCellValue($nextcolonne.$numero_ligne, round($obj_bulletin->difference));
                              $colonne_courante = $nextcolonne;

                              //Salaire Net
                              $nextcolonne = getNextColumnName($colonne_courante);
                              $sheet->setCellValue($nextcolonne.$numero_ligne, round($obj_bulletin->nb_mois));
                              $colonne_courante = $nextcolonne;
                              

                              $numero_ligne ++;
                        }
                  }
     /* $salSql = "SELECT jour FROM ".MAIN_DB_PREFIX."salarie_nombre_jour_travaille where annee=".$annee." AND mois=".$mois." AND fk_salarie=".$obj_salarie->rowid;
    $result = $db->query($salSql);
    $nb_jours = $db->fetch_object($result)->jour;

    $bulletin_anc_sql = "SELECT * FROM ".MAIN_DB_PREFIX."bulletin_anciennete WHERE fk_bulletin=".$obj_bulletin->rowid;
      $res_bulletin_anc = $db->query($bulletin_anc_sql);
      $obj_bulletin_anc = $db->fetch_object($res_bulletin_anc);
      
      $pdf->SetLeftMargin(13);
      $y = $pdf->GetY() + 6;
      $pdf->SetY($y);
      $pdf->Cell(30,4, utf8_decode($obj_bulletin_anc->libelle),0,0,'L');
      
      $pdf->SetLeftMargin(63);
      $pdf->Cell(20,4, utf8_decode($obj_bulletin_anc->taux."%"),0,0,'R');

      $pdf->SetLeftMargin(83);
      $pdf->Cell(20,4, utf8_decode(apres_virgule($db, $id_societe, $obj_bulletin->salaire_base, 2)),0,0,'R');
*/
}else{
      $sheet->mergeCells('A7:H8');
      $sheet->setCellValue('A7', "Aucune information Disponible");

}

$mois= GETPOST("mois", "int");
$annee= GETPOST("annee", "int");
$id_societe= GETPOST("id_societe", "int");

//Enregistrement dans les log || Traçabilité
$sql_select = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$user->id;
$obj = $db->fetch_object($db->query($sql_select));

//On garde la trace de l'action
$action_effectue = "Exportation des états de salaire de la société ".$nom_soc." du mois de ".$mois_tab[$mois - 1]." ".$annee." dans société Salaire";
$sql_log = 'INSERT INTO '.MAIN_DB_PREFIX.'log (fk_user, nom, prenom, quand, action_effectue, object_concerne)';
$sql_log .= ' VALUES('.$user->id.', "'.$obj->lastname.'","'.$obj->firstname.'",now(),"'.$action_effectue.'","Exportation")';
$db->query($sql_log);

// Envoyer le fichier au navigateur
$filename = $nom_soc."_".$mois_tab[$mois-1]."_".$annee.'_'.gmdate('D_d_M_Y_H:i:s'); //le nom du fichier
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="'.$filename.'.xlsx"');
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