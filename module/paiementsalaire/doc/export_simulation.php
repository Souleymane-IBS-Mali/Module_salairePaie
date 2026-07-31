<?php
// Inclure le chargement automatique de Composer
//require '../vendor/autoload.php';
require_once '../../main.inc.php';

$action = GETPOST("action", "alpha");

//Import des fichiers spreadsheet implementé par dolibarr
require_once DOL_DOCUMENT_ROOT.'/includes/phpoffice/phpspreadsheet/src/autoloader.php';
require_once DOL_DOCUMENT_ROOT.'/includes/Psr/autoloader.php';
require_once PHPEXCELNEW_PATH.'Spreadsheet.php';
// Importer les classes nécessaires de PhpSpreadsheet
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

if(!empty($action)){

// Créer un nouvel objet Spreadsheet
$spreadsheet = new Spreadsheet();
// Définir les propriétés du document
$spreadsheet->getProperties()->setCreator("Votre IBS")
                             ->setLastModifiedBy("Votre IBS")
                             ->setTitle("Document de Test Dolibarr")
                             ->setSubject("Document de Test Dolibarr")
                             ->setDescription("Document de test généré pour Dolibarr en utilisant PhpSpreadsheet.")
                             ->setKeywords("dolibarr php phpspreadsheet")
                             ->setCategory("Fichier de test");

/*// Définir la valeur de la cellule fusionnée
//global $mois, $annee, $nom_soc;
$mois= GETPOST("mois", "int");
$annee= GETPOST("annee", "int");
$id_societe= GETPOST("id_societe", "int");

$mois_tab = array(" Janvier "," Février "," Mars "," Avril "," Mai "," Juin "," Juillet "," Août "," Septembre "," Octobre "," Novembre "," Décembre ");

$soc_sql = "SELECT nom FROM ".MAIN_DB_PREFIX."societe WHERE rowid=".$id_societe;
      $res_soc = $db->query($soc_sql);
      if($res_soc)
            $nom_soc = $db->fetch_object($res_soc)->nom;*/
// Ajouter des données
$sheet = $spreadsheet->getActiveSheet();
// Fusionner les cellules de A1 à B1
$sheet->mergeCells('A1:H3');
//$sheet->mergeCells('A1:A2');

$info = "Etat de salaire de".$mois_tab[$mois-1]." ".$annee." de ".$nom_soc."";
$sheet->setCellValue('A1', $info);
$sheet->getStyle('A1')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

$liste_array = array();
if($action == "tout_exporter"){
      //Récuperation des GETPOST
      $sql_select = "SELECT * FROM ".MAIN_DB_PREFIX."simulation";
	$res_select = $db->query($sql_select);
	$nb = $db->num_rows($res_select);
	$a = 0;
	while ($a < $nb) {
		$liste_array[] = $db->fetch_object($res_select);
		$a ++;
	}
}else{
      //Récuperation des GETPOST
      $sql_select = "SELECT * FROM ".MAIN_DB_PREFIX."simulation";
	$res_select = $db->query($sql_select);
	$nb = $db->num_rows($res_select);
	$a = 0;
	while ($a < $nb) {
            $obj = $db->fetch_object($res_select);
            $cle = "simulation".$obj->rowid;
		if(!empty(GETPOST($cle, 'alpha'))){
		      $liste_array[] = $obj;
            }
		$a ++;
	}
}
                  // Appliquer du style à la cellule fusionnée (optionnel)
            $sheet->getStyle('A1')->getFont()->setBold(true);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A1')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);


            // Renommer la feuille de calcul
            $sheet->setTitle('Exemple');

                        if(count($liste_array) > 0){
                              //Les titre des colonnes
                              //Matricule
                              $sheet->setCellValue('A5', 'Libelle');
                              $sheet->getStyle('A5')->getFont()->setBold(true);
                              $sheet->mergeCells('A5:A6');


                              $colonne_courante = "A";

                              //Catégorie
                              $nextcolonne = getNextColumnName($colonne_courante);
                              $sheet->setCellValue($nextcolonne.'5', 'CATEGORIE');
                              $sheet->getStyle($nextcolonne.'5')->getFont()->setBold(true);
                              $sheet->mergeCells($nextcolonne.'5:'.$nextcolonne.'6');
                              $colonne_courante = $nextcolonne;

                              //Situation matrimoniale
                              $nextcolonne = getNextColumnName($colonne_courante);
                              $sheet->setCellValue($nextcolonne.'5', 'SITUAT. MAT');
                              $sheet->getStyle($nextcolonne.'5')->getFont()->setBold(true);
                              $sheet->mergeCells($nextcolonne.'5:'.$nextcolonne.'6');
                              $colonne_courante = $nextcolonne;

                              //Fonction
                              $nextcolonne = getNextColumnName($colonne_courante);
                              $sheet->setCellValue($nextcolonne.'5', 'FONCTION');
                              $sheet->getStyle($nextcolonne.'5')->getFont()->setBold(true);
                              $sheet->mergeCells($nextcolonne.'5:'.$nextcolonne.'6');
                              $colonne_courante = $nextcolonne;

                              

                              $nextcolonne = getNextColumnName($colonne_courante);
                              $sheet->setCellValue($nextcolonne.'5', 'SAL. BASE');
                              $sheet->getStyle($nextcolonne.'5')->getFont()->setBold(true);
                              $sheet->mergeCells($nextcolonne.'5:'.$nextcolonne.'6');
                              $colonne_courante = $nextcolonne;

                              $nextcolonne = getNextColumnName($colonne_courante);
                              $sheet->setCellValue($nextcolonne.'5', 'ANCIENNETE');
                              $sheet->getStyle($nextcolonne.'5')->getFont()->setBold(true);
                              $sheet->mergeCells($nextcolonne.'5:'.$nextcolonne.'6');
                              $colonne_courante = $nextcolonne;

                              $nextcolonne = getNextColumnName($colonne_courante);
                              $sheet->setCellValue($nextcolonne.'5', 'PRIMES & INDEMNITES');
                              $sheet->getStyle($nextcolonne.'5')->getFont()->setBold(true);
                              $sheet->mergeCells($nextcolonne.'5:'.$nextcolonne.'6');
                              $colonne_courante = $nextcolonne;

                              $nextcolonne = getNextColumnName($colonne_courante);
                              $sheet->setCellValue($nextcolonne.'5', 'SURSALAIRE');
                              $sheet->getStyle($nextcolonne.'5')->getFont()->setBold(true);
                              $sheet->mergeCells($nextcolonne.'5:'.$nextcolonne.'6');
                              $colonne_courante = $nextcolonne;
                              
                              $numero_colonne = 5;
                              
                              //Salaire brut
                              $nextcolonne = getNextColumnName($colonne_courante);
                              $sheet->setCellValue($nextcolonne."5", "SAL. BRUT");
                              $sheet->getStyle($nextcolonne."5")->getFont()->setBold(true);
                              $sheet->mergeCells($nextcolonne.'5:'.$nextcolonne.'6');
                              $colonne_courante = $nextcolonne;

                              //Salaire brut Cotisable
                              $nextcolonne = getNextColumnName($colonne_courante);
                              $sheet->setCellValue($nextcolonne."5", "SAL. COTIS");
                              $sheet->getStyle($nextcolonne."5")->getFont()->setBold(true);
                              $sheet->mergeCells($nextcolonne.'5:'.$nextcolonne.'6');
                              $colonne_courante = $nextcolonne;

                              //Salaire brut
                              $nextcolonne = getNextColumnName($colonne_courante);
                              $sheet->setCellValue($nextcolonne."5", "SAL. IMP.");
                              $sheet->getStyle($nextcolonne."5")->getFont()->setBold(true);
                              $sheet->mergeCells($nextcolonne.'5:'.$nextcolonne.'6');
                              $colonne_courante = $nextcolonne;

                              //INPS SALARIALE
                              $nextcolonne = getNextColumnName($colonne_courante);
                              $sheet->setCellValue($nextcolonne."5", "INPS SAL.");
                              $sheet->getStyle($nextcolonne."5")->getFont()->setBold(true);
                              $sheet->mergeCells($nextcolonne.'5:'.$nextcolonne.'6');
                              $colonne_courante = $nextcolonne;

                              //INPS PATRONALE
                              $nextcolonne = getNextColumnName($colonne_courante);
                              $sheet->setCellValue($nextcolonne."5", "INPS PATRO.");
                              $sheet->getStyle($nextcolonne."5")->getFont()->setBold(true);
                              $sheet->mergeCells($nextcolonne.'5:'.$nextcolonne.'6');
                              $colonne_courante = $nextcolonne;

                              //ITS
                              $nextcolonne = getNextColumnName($colonne_courante);
                              $sheet->setCellValue($nextcolonne."5", "ITS.");
                              $sheet->getStyle($nextcolonne."5")->getFont()->setBold(true);
                              $sheet->mergeCells($nextcolonne.'5:'.$nextcolonne.'6');
                              $colonne_courante = $nextcolonne;

                              //AMO
                              $nextcolonne = getNextColumnName($colonne_courante);
                              $sheet->setCellValue($nextcolonne."5", "AMO SAL.");
                              $sheet->getStyle($nextcolonne."5")->getFont()->setBold(true);
                              $sheet->mergeCells($nextcolonne.'5:'.$nextcolonne.'6');
                              $colonne_courante = $nextcolonne;

                              //AMO Patronale
                              $nextcolonne = getNextColumnName($colonne_courante);
                              $sheet->setCellValue($nextcolonne."5", "AMO PATRO.");
                              $sheet->getStyle($nextcolonne."5")->getFont()->setBold(true);
                              $sheet->mergeCells($nextcolonne.'5:'.$nextcolonne.'6');
                              $colonne_courante = $nextcolonne;

                              //Base CFE
                              $nextcolonne = getNextColumnName($colonne_courante);
                              $sheet->setCellValue($nextcolonne."5", "BASE CFE");
                              $sheet->getStyle($nextcolonne."5")->getFont()->setBold(true);
                              $sheet->mergeCells($nextcolonne.'5:'.$nextcolonne.'6');
                              $colonne_courante = $nextcolonne;

                              //Montant CFE
                              $nextcolonne = getNextColumnName($colonne_courante);
                              $sheet->setCellValue($nextcolonne."5", "MONTANT CFE.");
                              $sheet->getStyle($nextcolonne."5")->getFont()->setBold(true);
                              $sheet->mergeCells($nextcolonne.'5:'.$nextcolonne.'6');
                              $colonne_courante = $nextcolonne;

                              //Base TL
                              $nextcolonne = getNextColumnName($colonne_courante);
                              $sheet->setCellValue($nextcolonne."5", "BASE TL.");
                              $sheet->getStyle($nextcolonne."5")->getFont()->setBold(true);
                              $sheet->mergeCells($nextcolonne.'5:'.$nextcolonne.'6');
                              $colonne_courante = $nextcolonne;

                              //Montant TL
                              $nextcolonne = getNextColumnName($colonne_courante);
                              $sheet->setCellValue($nextcolonne."5", "MONTANT TL.");
                              $sheet->getStyle($nextcolonne."5")->getFont()->setBold(true);
                              $sheet->mergeCells($nextcolonne.'5:'.$nextcolonne.'6');
                              $colonne_courante = $nextcolonne;

                              //Salaire net
                              $nextcolonne = getNextColumnName($colonne_courante);
                              $sheet->setCellValue($nextcolonne."5", "SALAIRE NET");
                              $sheet->getStyle($nextcolonne."5")->getFont()->setBold(true);
                              $sheet->mergeCells($nextcolonne.'5:'.$nextcolonne.'6');
                              $colonne_courante = $nextcolonne;

                              //Cout Total
                              $nextcolonne = getNextColumnName($colonne_courante);
                              $sheet->setCellValue($nextcolonne."5", "COUT");
                              $sheet->getStyle($nextcolonne."5")->getFont()->setBold(true);
                              $sheet->mergeCells($nextcolonne.'5:'.$nextcolonne.'6');
                              //$colonne_courante = $nextcolonne;

            //--------------------------------------------------------------------
                        //Affichage des valeurs
                        $numero_ligne = 7;
                        $taille = count($liste_array);
                        for ($i=0; $i < $taille; $i++) { 
                                          $cout = 0;
                                    //Objet Utilisateur
                                          //Ajout d'une nouvelle ligne
                                          $sheet->insertNewRowBefore($numero_ligne+2, $numero_ligne);

                                          //Matricule
                                          $sheet->setCellValue('A'.$numero_ligne, $liste_array[$i]->libelle);

                                          $colonne_courante = "A";
                                          

                                          //Ajout de la catégorie
                                          $categ = $liste_array[$i]->categorie;
                                          if(!empty($liste_array[$i]->echelon) && $liste_array[$i]->echelon != 'N/A')
                                                $categ .= '->'.$liste_array[$i]->echelon;
                                          $nextcolonne = getNextColumnName($colonne_courante);
                                          $sheet->setCellValue($nextcolonne.$numero_ligne, $categ);
                                          $colonne_courante = $nextcolonne;


                                          //Ajout la situation familiale
                                          $stuati_fa = $liste_array[$i]->situation_familiale.' '.$liste_array[$i]->nombre_enfant.'('.$liste_array[$i]->nombre_enfant_hand.')';
                                          $nextcolonne = getNextColumnName($colonne_courante);
                                          $sheet->setCellValue($nextcolonne.$numero_ligne, $stuati_fa);
                                          $colonne_courante = $nextcolonne;

                                          //Fonction
                                          $nextcolonne = getNextColumnName($colonne_courante);
                                          $sheet->setCellValue($nextcolonne.$numero_ligne, $liste_array[$i]->fonction);
                                          $colonne_courante = $nextcolonne;

                                          //Ajout du salaire de base
                                          $nextcolonne = getNextColumnName($colonne_courante);
                                          $sheet->setCellValue($nextcolonne.$numero_ligne, $liste_array[$i]->salaire_base);
                                          $colonne_courante = $nextcolonne;

                                          //Ajout de l'ancienneté
                                          $nextcolonne = getNextColumnName($colonne_courante);
                                          $sheet->setCellValue($nextcolonne.$numero_ligne, round($liste_array[$i]->anciennete));
                                          $colonne_courante = $nextcolonne;

                                          //Primes Indemnités
                                          $nextcolonne = getNextColumnName($colonne_courante);
                                          $sheet->setCellValue($nextcolonne.$numero_ligne, $liste_array[$i]->primesindemnites);
                                          $colonne_courante = $nextcolonne;

                                          //Ajout du sursalaire
                                          $nextcolonne = getNextColumnName($colonne_courante);
                                          $sheet->setCellValue($nextcolonne.$numero_ligne, $liste_array[$i]->sursalaire);
                                          $colonne_courante = $nextcolonne;                                          

                                          //Salaire brut
                                          $nextcolonne = getNextColumnName($colonne_courante);
                                          $sheet->setCellValue($nextcolonne.$numero_ligne, round($liste_array[$i]->salaire_brut));
                                          $colonne_courante = $nextcolonne;

                                          $cout += $liste_array[$i]->salaire_brut;

                                          //Salaire brut cotisable
                                          $nextcolonne = getNextColumnName($colonne_courante);
                                          $sheet->setCellValue($nextcolonne.$numero_ligne, round($liste_array[$i]->salaire_brut_cotisable));
                                          $colonne_courante = $nextcolonne;

                                          //Salaire brut imposable
                                          $nextcolonne = getNextColumnName($colonne_courante);
                                          $sheet->setCellValue($nextcolonne.$numero_ligne, round($liste_array[$i]->salaire_brut_imposable));
                                          $colonne_courante = $nextcolonne;
                                         
                                          //INPS salarie
                                          $nextcolonne = getNextColumnName($colonne_courante);
                                          $sheet->setCellValue($nextcolonne.$numero_ligne, round($liste_array[$i]->atmp_salarie + $liste_array[$i]->prestation_familiale_salarie +
                                          $liste_array[$i]->retraite_salarie + $liste_array[$i]->invalidite_allocation_survivant_salarie + $liste_array[$i]->anpe_salarie));
                                          $colonne_courante = $nextcolonne;
                                          
                                          //Montant des cotisation (INPS) patronale
                                          $nextcolonne = getNextColumnName($colonne_courante);
                                          $sheet->setCellValue($nextcolonne.$numero_ligne, round($liste_array[$i]->atmp_patro + $liste_array[$i]->prestation_familiale_patro +
                                          $liste_array[$i]->retraite_patro + $liste_array[$i]->invalidite_allocation_survivant_patro + $liste_array[$i]->anpe_patro));
                                          $colonne_courante = $nextcolonne;
                                          $cout += $liste_array[$i]->atmp_patro + $liste_array[$i]->prestation_familiale_patro +
                                          $liste_array[$i]->retraite_patro + $liste_array[$i]->invalidite_allocation_survivant_patro + $liste_array[$i]->anpe_patro;

                                          //Montant des impôt I.T.S
                                          $nextcolonne = getNextColumnName($colonne_courante);
                                          $sheet->setCellValue($nextcolonne.$numero_ligne, round($liste_array[$i]->its));
                                          $colonne_courante = $nextcolonne;

                                          //AMO salariale
                                          $nextcolonne = getNextColumnName($colonne_courante);
                                          $sheet->setCellValue($nextcolonne.$numero_ligne, round($liste_array[$i]->amo_salarie));
                                          $colonne_courante = $nextcolonne;

                                          //AMO patronale
                                          $nextcolonne = getNextColumnName($colonne_courante);
                                          $sheet->setCellValue($nextcolonne.$numero_ligne, round($liste_array[$i]->amo_patro));
                                          $colonne_courante = $nextcolonne;
                                          $cout += $liste_array[$i]->amo_patro;

                                          //Base CFE
                                          $nextcolonne = getNextColumnName($colonne_courante);
                                          $sheet->setCellValue($nextcolonne.$numero_ligne, round($liste_array[$i]->salaire_brut));
                                          $colonne_courante = $nextcolonne;

                                          //Montant CFE
                                          $nextcolonne = getNextColumnName($colonne_courante);
                                          $sheet->setCellValue($nextcolonne.$numero_ligne, round($liste_array[$i]->montant_cfe));
                                          $colonne_courante = $nextcolonne;

                                          $cout += 0;

                                          //Base TL
                                          $nextcolonne = getNextColumnName($colonne_courante);
                                          $sheet->setCellValue($nextcolonne.$numero_ligne, round($liste_array[$i]->salaire_brut));
                                          $colonne_courante = $nextcolonne;

                                          //Montant TL
                                          $nextcolonne = getNextColumnName($colonne_courante);
                                          $sheet->setCellValue($nextcolonne.$numero_ligne, round($liste_array[$i]->montant_tl));
                                          $colonne_courante = $nextcolonne;

                                          $cout += 0;

                                          //Salaire Net
                                          $nextcolonne = getNextColumnName($colonne_courante);
                                          $sheet->setCellValue($nextcolonne.$numero_ligne, round($liste_array[$i]->net_payer));
                                          $colonne_courante = $nextcolonne;

                                          //Cout total
                                          $nextcolonne = getNextColumnName($colonne_courante);
                                          $sheet->setCellValue($nextcolonne.$numero_ligne, round($cout));
                                          $colonne_courante = $nextcolonne;


                                          $numero_ligne ++;
                                    }
            /* $salSql = "SELECT jour FROM ".MAIN_DB_PREFIX."salarie_nombre_jour_travaille where annee=".$annee." AND mois=".$mois." AND fk_salarie=".$obj_salarie->rowid;
            $result = $db->query($salSql);
            $nb_jours = $db->fetch_object($result)->jour;

            $bulletin_anc_sql = "SELECT * FROM ".MAIN_DB_PREFIX."bulletin_anciennete WHERE fk_bulletin=".$liste_array[$i]->rowid;
                  $res_select_anc = $db->query($bulletin_anc_sql);
                  $liste_array[$i]_anc = $db->fetch_object($res_select_anc);
                  
                  $pdf->SetLeftMargin(13);
                  $y = $pdf->GetY() + 6;
                  $pdf->SetY($y);
                  $pdf->Cell(30,4, utf8_decode($liste_array[$i]_anc->libelle),0,0,'L');
                  
                  $pdf->SetLeftMargin(63);
                  $pdf->Cell(20,4, utf8_decode($liste_array[$i]_anc->taux."%"),0,0,'R');

                  $pdf->SetLeftMargin(83);
                  $pdf->Cell(20,4, utf8_decode(apres_virgule($db, $id_societe, $liste_array[$i]->salaire_base, 2)),0,0,'R');
            */
            }else{
                  $sheet->mergeCells('A7:H8');
                  $sheet->setCellValue('A7', "Aucune information Disponible");

            }

            /*$mois= GETPOST("mois", "int");
            $annee= GETPOST("annee", "int");
            $id_societe= GETPOST("id_societe", "int");

            //Enregistrement dans les log || Traçabilité
            $sql_select = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$user->id;
            $obj = $db->fetch_object($db->query($sql_select));

            //On garde la trace de l'action
            $action_effectue = "Exportation des états de salaire de la société ".$nom_soc." du mois de ".$mois_tab[$mois - 1]." ".$annee." dans société Salaire";
            $sql_log = 'INSERT INTO '.MAIN_DB_PREFIX.'log (fk_user, nom, prenom, quand, action_effectue, object_concerne)';
            $sql_log .= ' VALUES('.$user->id.', "'.$obj->lastname.'","'.$obj->firstname.'",now(),"'.$action_effectue.'","Exportation")';
            $db->query($sql_log);*/

            // Envoyer le fichier au navigateur
            $filename = 'Export_simulation_'.gmdate('D_d_M_Y_H:i:s'); //le nom du fichier
            $filename = 'export_simulation'.date('Ymd_His').'.xlsx';

            $tmpdir = DOL_DATA_ROOT.'/paiementsalaire/temp';

            if (!file_exists($tmpdir)) {
            mkdir($tmpdir, 0777, true);
            }

            $tmpfile = $tmpdir.'/export_simulation'.time().'.xlsx';

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
            exit;

}else{
      print "Un problème est survenu";
}
function getNextColumnName($currentColumnName) {
      // Convertir le nom de la colonne en index de colonne
      $currentColumnIndex = Coordinate::columnIndexFromString($currentColumnName);
      
      // Obtenir l'index de la colonne suivante
      $nextColumnIndex = $currentColumnIndex + 1;
      
      // Convertir l'index de la colonne suivante en nom de colonne
      $nextColumnName = Coordinate::stringFromColumnIndex($nextColumnIndex);
      
      return $nextColumnName;
  }