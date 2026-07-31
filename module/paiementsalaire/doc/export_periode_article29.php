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
    $mois_tab = array(" Janvier "," Février "," Mars "," Avril "," Mai "," Juin "," Juillet "," Août "," Septembre "," Octobre "," Novembre "," Décembre ", " 13è Mois ");
$debut = GETPOST("date_debut");
	$fin = GETPOST("date_fin");
	$date_debut = explode('_', $debut);
	$date_fin= explode('_', $fin);

$annee_debut= $date_debut[1];
$annee_fin= $date_fin[1];

$mois_debut= $date_debut[0];
$mois_fin= $date_fin[0];

$id_societe= GETPOST("id_societe", "int");
$mois_tab = array(" Janvier "," Février "," Mars "," Avril "," Mai "," Juin "," Juillet "," Août "," Septembre "," Octobre "," Novembre "," Décembre ", " 13è Mois ");

$sql_select = "SELECT nom FROM ".MAIN_DB_PREFIX."societe WHERE rowid=".$id_societe;
$obj_soc = $db->fetch_object($db->query($sql_select));
$nom_soc = $obj_soc->nom;
// Ajouter des données
$sheet = $spreadsheet->getActiveSheet();
// Fusionner les cellules de A1 à B1
$sheet->mergeCells('A1:H3');
//$sheet->mergeCells('A1:A2');

$info = "ETAT DE SALAIRE DE ".$mois_tab[$mois_debut - 1]." ".$annee_debut." A ".$mois_tab[$mois_fin - 1]." ".$annee_fin." DE ".$nom_soc;
$sheet->setCellValue('A1', $info);
$sheet->getStyle('A1')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
      

      // Appliquer du style à la cellule fusionnée (optionnel)
$sheet->getStyle('A1')->getFont()->setBold(true);
$sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('A1')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);


// Renommer la feuille de calcul
$sheet->setTitle('Exemple');

$bulletin_sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."bulletin WHERE annee >= ".$db->escape($annee_debut)." AND annee <= ".$db->escape($annee_fin)." AND mois >= ".$db->escape($mois_debut)." AND mois <= ".$db->escape($mois_fin)." AND fk_societe = ".$db->escape($id_societe);
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

                  //Date rentrée
                  $nextcolonne = getNextColumnName($colonne_courante);
                  $sheet->setCellValue($nextcolonne.'6', 'DATE RENTREE');
                  $sheet->getStyle($nextcolonne.'6')->getFont()->setBold(true);
                  $colonne_courante = $nextcolonne;

                  //Catégorie
                  $nextcolonne = getNextColumnName($colonne_courante);
                  $sheet->setCellValue($nextcolonne.'6', 'CATEGORIE');
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

                  //Banque
                  $nextcolonne = getNextColumnName($colonne_courante);
                  $sheet->setCellValue($nextcolonne.'6', 'BANQUE');
                  $sheet->getStyle($nextcolonne.'6')->getFont()->setBold(true);
                  $colonne_courante = $nextcolonne;

                  //Compte
                  $nextcolonne = getNextColumnName($colonne_courante);
                  $sheet->setCellValue($nextcolonne.'6', 'N° COMPTE');
                  $sheet->getStyle($nextcolonne.'6')->getFont()->setBold(true);
                  $colonne_courante = $nextcolonne;

                  //Jours Travaillé
                  $nextcolonne = getNextColumnName($colonne_courante);
                  $sheet->setCellValue($nextcolonne.'6', 'JOURS TRAV.');
                  $sheet->getStyle($nextcolonne.'6')->getFont()->setBold(true);
                  $colonne_courante = $nextcolonne;

                  //Taux
                  $nextcolonne = getNextColumnName($colonne_courante);
                  $sheet->setCellValue($nextcolonne.'6', 'TAUX');
                  $sheet->getStyle($nextcolonne.'6')->getFont()->setBold(true);
                  $colonne_courante = $nextcolonne;

                  $nextcolonne = getNextColumnName($colonne_courante);
                  $sheet->setCellValue($nextcolonne.'6', 'SAL. BASE');
                  $sheet->getStyle($nextcolonne.'6')->getFont()->setBold(true);
                  $colonne_courante = $nextcolonne;

                  $nextcolonne = getNextColumnName($colonne_courante);
                  $sheet->setCellValue($nextcolonne.'6', 'SURSALAIRE');
                  $sheet->getStyle($nextcolonne.'6')->getFont()->setBold(true);
                  $colonne_courante = $nextcolonne;

                  //Prime d'Aciennete
                  $bulletin_sql_anc = "SELECT bul.rowid, bul_an.libelle FROM ".MAIN_DB_PREFIX."bulletin as bul";
                  $bulletin_sql_anc .= " LEFT JOIN ".MAIN_DB_PREFIX."bulletin_anciennete as bul_an ON bul.rowid=bul_an.fk_bulletin";
                  $bulletin_sql_anc .= " WHERE annee>=".$db->escape($annee_debut)." AND annee <= ".$db->escape($annee_fin)." AND mois>=".$db->escape($mois_debut)." AND mois <= ".$db->escape($mois_fin)." AND fk_societe=".$db->escape($id_societe);
                  $res_bulletin_an = $db->query($bulletin_sql_anc);
                  $obj_bulletin_an = $db->fetch_object($res_bulletin_an);
                  $nextcolonne = getNextColumnName($colonne_courante);
                  $sheet->setCellValue($nextcolonne.'6', strtoupper($obj_bulletin_an->libelle));
                  $sheet->getStyle($nextcolonne.'6')->getFont()->setBold(true);
                  $colonne_courante = $nextcolonne;

                  
                  $numero_colonne = 6;
                  //Selection des primes
                  $array_id_pr = array();
                  $array_id_ind = array();
                  $array_id_pr_exc = array();
                  $array_id_hs = array();
                  $array_libelle_av = array();

                  $bulletin_sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."bulletin";
                  $bulletin_sql .= " WHERE annee>=".$db->escape($annee_debut)." AND annee <= ".$db->escape($annee_fin)." AND mois>=".$db->escape($mois_debut)." AND mois <= ".$db->escape($mois_fin)." AND fk_societe=".$db->escape($id_societe);
                  $res_bulletin = $db->query($bulletin_sql);
                  if($res_bulletin){
                        $i = 0;
                        $num_all = $db->num_rows($res_bulletin);
                        while ($i < ($num_all)){
                              $obj_bulletin = $db->fetch_object($res_bulletin);
                              $bulletin_pr = "SELECT DISTINCT fk_prime, libelle FROM ".MAIN_DB_PREFIX."bulletin_prime WHERE fk_bulletin=".$obj_bulletin->rowid;
                              $j = 0;
                              $res = $db->query($bulletin_pr);
                              while($j< $db->num_rows($res)){
                                    $obj_bulletin_pr = $db->fetch_object($res);
                                    if(!in_array($obj_bulletin_pr->libelle, $array_id_pr)){
                                          $array_id_pr[] = $obj_bulletin_pr->libelle;
                                          //$sheet->insertNewColumnBefore('I', 9);
                                          $nextcolonne = getNextColumnName($colonne_courante);
                                          $sheet->setCellValue($nextcolonne.'6', strtoupper($obj_bulletin_pr->libelle));
                                          $sheet->getStyle($nextcolonne.'6')->getFont()->setBold(true);
                  
                                          $colonne_courante = $nextcolonne;
                                    }
                                    $j ++;
                              }
                              $i ++;
                        }
                  }
  
                  //Selection des indemnités
                  $bulletin_sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."bulletin";
                  $bulletin_sql .= " WHERE annee>=".$db->escape($annee_debut)." AND annee <= ".$db->escape($annee_fin)." AND mois>=".$db->escape($mois_debut)." AND mois <= ".$db->escape($mois_fin)." AND fk_societe=".$db->escape($id_societe);
                  $res_bulletin = $db->query($bulletin_sql);
                  if($res_bulletin){
                        $i = 0;
                        $num_all = $db->num_rows($res_bulletin);
                        while ($i < ($num_all)){
                              $obj_bulletin = $db->fetch_object($res_bulletin);
                              $bulletin_ind = "SELECT DISTINCT fk_indemnite, libelle FROM ".MAIN_DB_PREFIX."bulletin_indemnite WHERE fk_bulletin=".$obj_bulletin->rowid;
                              $j = 0;
                              $res = $db->query($bulletin_ind);
                              while($j < $db->num_rows($res)){
                                    $obj_bulletin_ind = $db->fetch_object($res);
                                    if(!in_array($obj_bulletin_ind->libelle, $array_id_ind)){
                                          $array_id_ind[] = $obj_bulletin_ind->libelle;
                                          //$sheet->insertNewColumnBefore('I', 9);
                                          $nextcolonne = getNextColumnName($colonne_courante);
                                          $sheet->setCellValue($nextcolonne.'6', strtoupper($obj_bulletin_ind->libelle));
                                          $sheet->getStyle($nextcolonne.'6')->getFont()->setBold(true);
                  
                                          $colonne_courante = $nextcolonne;
                                    }
                                    $j ++;
                              }
                              $i ++;
                        }
                  }


                  //Primes exceptionnelle
                  $bulletin_sql_exc = "SELECT rowid FROM ".MAIN_DB_PREFIX."bulletin";
                  $bulletin_sql_exc .= " WHERE annee>=".$db->escape($annee_debut)." AND annee <= ".$db->escape($annee_fin)." AND mois>=".$db->escape($mois_debut)." AND mois <= ".$db->escape($mois_fin)." AND fk_societe=".$db->escape($id_societe);
                  $res_bulletin_exc = $db->query($bulletin_sql_exc);
                  if($res_bulletin_exc){
                        $i = 0;
                        $num_all = $db->num_rows($res_bulletin_exc);
                        while ($i < ($num_all)){
                              $obj_bulletin_exc = $db->fetch_object($res_bulletin_exc);
                              $bulletin_exc_pr = "SELECT DISTINCT fk_prime, libelle FROM ".MAIN_DB_PREFIX."bulletin_prime_exceptionnelle WHERE fk_bulletin=".$obj_bulletin_exc->rowid;
                              $res_pr_ex = $db->query($bulletin_exc_pr);
                              $num_ex = $db->num_rows($res_pr_ex);
                              $a = 0;
                              while ($a < $num_ex) {
                                    $obj_bulletin_pr_exc = $db->fetch_object($res_pr_ex);
                                    if(!in_array($obj_bulletin_pr_exc->libelle, $array_id_pr_exc)){
                                          $array_id_pr_exc[] = $obj_bulletin_pr_exc->libelle;
                                          //$sheet->insertNewColumnBefore('I', 9);
                                          $nextcolonne = getNextColumnName($colonne_courante);
                                          $sheet->setCellValue($nextcolonne.'6', strtoupper($obj_bulletin_pr_exc->libelle));
                                          $sheet->getStyle($nextcolonne.'6')->getFont()->setBold(true);
                  

                                          $colonne_courante = $nextcolonne;
                                    }
                                    $a ++;
                              }
                              $i ++;
                        }
                  }
                  
                  //Heures sup
                  $bulletin_sql_hs = "SELECT rowid FROM ".MAIN_DB_PREFIX."bulletin";
                  $bulletin_sql_hs .= " WHERE annee>=".$db->escape($annee_debut)." AND annee <= ".$db->escape($annee_fin)." AND mois>=".$db->escape($mois_debut)." AND mois <= ".$db->escape($mois_fin)." AND fk_societe=".$db->escape($id_societe);
                  $res_bulletin_hs = $db->query($bulletin_sql_hs);
                  if($res_bulletin_hs){
                        $i = 0;
                        $num_all = $db->num_rows($res_bulletin_hs);
                        while ($i < ($num_all)){
                              $obj_bulletin_hs = $db->fetch_object($res_bulletin_hs);
                              $bulletin_hs = "SELECT DISTINCT fk_heur_sup, libelle FROM ".MAIN_DB_PREFIX."bulletin_heure_sup WHERE fk_bulletin=".$obj_bulletin_hs->rowid;
                              
                              if($db->num_rows($db->query($bulletin_hs))>0){
                                    $obj_bulletin_heure_sup = $db->fetch_object($db->query($bulletin_hs));
                                    if(!in_array($obj_bulletin_heure_sup->libelle, $array_id_hs)){
                                          $array_id_hs[] = $obj_bulletin_heure_sup->libelle;

                                          $hs_sql = "SELECT taux, commentaire FROM ".MAIN_DB_PREFIX."heure_sup WHERE rowid=".$obj_bulletin_heure_sup->fk_heur_sup;
                                          $type_sal_heure_sup = $db->query($hs_sql);
                                          $obj_sal_heure_sup = $db->fetch_object($sal_heure_sup);

                                          //$sheet->insertNewColumnBefore('I', 9);
                                          //Nombre d'heure sup
                                          $nextcolonne = getNextColumnName($colonne_courante);
                                          $sheet->setCellValue($nextcolonne.'6', "Nombre HS à ".$obj_sal_heure_sup->taux);
                                          $sheet->getStyle($nextcolonne.'6')->getFont()->setBold(true);
                  
                                          $colonne_courante = $nextcolonne;

                                          //Montant heure sup
                                          $nextcolonne = getNextColumnName($colonne_courante);
                                          $sheet->setCellValue($nextcolonne.'6', "Montant HS à ".$obj_sal_heure_sup->taux);
                                          $sheet->getStyle($nextcolonne.'6')->getFont()->setBold(true);
                  
                                          $colonne_courante = $nextcolonne;
                                    }
                              }
                              $i ++;
                        }
                  }
                  
                  //Salaire brut
                  $nextcolonne = getNextColumnName($colonne_courante);
                  $sheet->setCellValue($nextcolonne."6", "SAL. BRUT");
                  $sheet->getStyle($nextcolonne."6")->getFont()->setBold(true);
                  $colonne_courante = $nextcolonne;

                  //Salaire brut Cotisable
                  $nextcolonne = getNextColumnName($colonne_courante);
                  $sheet->setCellValue($nextcolonne."6", "SAL. COTIS");
                  $sheet->getStyle($nextcolonne."6")->getFont()->setBold(true);
                  $colonne_courante = $nextcolonne;

                  //Salaire brut Imp
                  $nextcolonne = getNextColumnName($colonne_courante);
                  $sheet->setCellValue($nextcolonne."6", "SAL. IMP.");
                  $sheet->getStyle($nextcolonne."6")->getFont()->setBold(true);
                  $colonne_courante = $nextcolonne;

                  //INPS SALARIALE
                  $nextcolonne = getNextColumnName($colonne_courante);
                  $sheet->setCellValue($nextcolonne."6", "INPS SAL.");
                  $sheet->getStyle($nextcolonne."6")->getFont()->setBold(true);
                  $colonne_courante = $nextcolonne;

                  //INPS PATRONALE
                  $nextcolonne = getNextColumnName($colonne_courante);
                  $sheet->setCellValue($nextcolonne."6", "INPS PATRO.");
                  $sheet->getStyle($nextcolonne."6")->getFont()->setBold(true);
                  $colonne_courante = $nextcolonne;

                  //ITS
                  $nextcolonne = getNextColumnName($colonne_courante);
                  $sheet->setCellValue($nextcolonne."6", "ITS.");
                  $sheet->getStyle($nextcolonne."6")->getFont()->setBold(true);
                  $colonne_courante = $nextcolonne;

                  //AMO
                  $nextcolonne = getNextColumnName($colonne_courante);
                  $sheet->setCellValue($nextcolonne."6", "AMO SAL.");
                  $sheet->getStyle($nextcolonne."6")->getFont()->setBold(true);
                  $colonne_courante = $nextcolonne;

                  //AMO Patronale
                  $nextcolonne = getNextColumnName($colonne_courante);
                  $sheet->setCellValue($nextcolonne."6", "AMO PATRO.");
                  $sheet->getStyle($nextcolonne."6")->getFont()->setBold(true);
                  $colonne_courante = $nextcolonne;

                  //Base CFE
                  $nextcolonne = getNextColumnName($colonne_courante);
                  $sheet->setCellValue($nextcolonne."6", "BASE CFE");
                  $sheet->getStyle($nextcolonne."6")->getFont()->setBold(true);
                  $colonne_courante = $nextcolonne;

                  //Montant CFE
                  $nextcolonne = getNextColumnName($colonne_courante);
                  $sheet->setCellValue($nextcolonne."6", "MONTANT CFE.");
                  $sheet->getStyle($nextcolonne."6")->getFont()->setBold(true);
                  $colonne_courante = $nextcolonne;

                  //Base TL
                  $nextcolonne = getNextColumnName($colonne_courante);
                  $sheet->setCellValue($nextcolonne."6", "BASE TL.");
                  $sheet->getStyle($nextcolonne."6")->getFont()->setBold(true);
                  $colonne_courante = $nextcolonne;

                  //Montant TL
                  $nextcolonne = getNextColumnName($colonne_courante);
                  $sheet->setCellValue($nextcolonne."6", "MONTANT TL.");
                  $sheet->getStyle($nextcolonne."6")->getFont()->setBold(true);
                  $colonne_courante = $nextcolonne;

                  //Salaire net
                  $nextcolonne = getNextColumnName($colonne_courante);
                  $sheet->setCellValue($nextcolonne."6", "SALAIRE NET");
                  $sheet->getStyle($nextcolonne."6")->getFont()->setBold(true);
                  $colonne_courante = $nextcolonne;


                  //Liste des avance
                  $bulletin_sql_av = "SELECT rowid FROM ".MAIN_DB_PREFIX."bulletin";
                  $bulletin_sql_av .= " WHERE annee>=".$db->escape($annee_debut)." AND annee <= ".$db->escape($annee_fin)." AND mois>=".$db->escape($mois_debut)." AND mois <= ".$db->escape($mois_fin)." AND fk_societe=".$db->escape($id_societe);
                  $res_bulletin_av = $db->query($bulletin_sql_av);
                  if($res_bulletin_av){
                        while ($obj_bulletinav = $db->fetch_object($res_bulletin_av)){
                              $bulletin_av_sql = "SELECT libelle FROM ".MAIN_DB_PREFIX."bulletin_avance WHERE fk_bulletin=".$obj_bulletinav->rowid;
                              $res_avance_bul = $db->query($bulletin_av_sql);
                              while ($obj_bulletin_av = $db->fetch_object($res_avance_bul)) {
                                    if(!in_array($obj_bulletin_av->libelle, $array_libelle_av)){
                                          $array_libelle_av[] = $obj_bulletin_av->libelle;
                                          //$sheet->insertNewColumnBefore('I', 9);
                                                $nextcolonne = getNextColumnName($colonne_courante);
                                                $sheet->setCellValue($nextcolonne.'6', strtoupper($obj_bulletin_av->libelle));
                                                $sheet->getStyle($nextcolonne.'6')->getFont()->setBold(true);
                        

                                                $colonne_courante = $nextcolonne;
                                    }
                              }
                        }
                  }

                  //Total des avances
                  $nextcolonne = getNextColumnName($colonne_courante);
                  $sheet->setCellValue($nextcolonne."6", "TOTAL AVANCE");
                  $sheet->getStyle($nextcolonne."6")->getFont()->setBold(true);
                  $colonne_courante = $nextcolonne;

                  //Salaire net
                  $nextcolonne = getNextColumnName($colonne_courante);
                  $sheet->setCellValue($nextcolonne."6", "NET PAYE");
                  $sheet->getStyle($nextcolonne."6")->getFont()->setBold(true);
                  $colonne_courante = $nextcolonne;

                  //Cout Total
                  $nextcolonne = getNextColumnName($colonne_courante);
                  $sheet->setCellValue($nextcolonne."6", "COUT");
                  $sheet->getStyle($nextcolonne."6")->getFont()->setBold(true);
                  $colonne_courante = $nextcolonne;

                  //Régularisation ITS
                        //salaires bruts
                        $nextcolonne = getNextColumnName($colonne_courante);
                        $sheet->setCellValue($nextcolonne."6", "Mt salaire et autres retrib.Brut");
                        $sheet->getStyle($nextcolonne."6")->getFont()->setBold(true);
                        $colonne_courante = $nextcolonne;

                        //retraites
                        $nextcolonne = getNextColumnName($colonne_courante);
                        $sheet->setCellValue($nextcolonne."6", "Retraite");
                        $sheet->getStyle($nextcolonne."6")->getFont()->setBold(true);
                        $colonne_courante = $nextcolonne;

                        //Allocations & Indem. non Imposables
                        $nextcolonne = getNextColumnName($colonne_courante);
                        $sheet->setCellValue($nextcolonne."6", "Allocations & Indem. non Imposables");
                        $sheet->getStyle($nextcolonne."6")->getFont()->setBold(true);
                        $colonne_courante = $nextcolonne;

                        //Montages réel   Avantages   En Nature
                        $nextcolonne = getNextColumnName($colonne_courante);
                        $sheet->setCellValue($nextcolonne."6", "Montages réel Avantages En Nature");
                        $sheet->getStyle($nextcolonne."6")->getFont()->setBold(true);
                        $colonne_courante = $nextcolonne;

                        //Base Imposition
                        $nextcolonne = getNextColumnName($colonne_courante);
                        $sheet->setCellValue($nextcolonne."6", "Base Imposition");
                        $sheet->getStyle($nextcolonne."6")->getFont()->setBold(true);
                        $colonne_courante = $nextcolonne;

                        //Impôt Retenu
                        $nextcolonne = getNextColumnName($colonne_courante);
                        $sheet->setCellValue($nextcolonne."6", "Impôt Retenu");
                        $sheet->getStyle($nextcolonne."6")->getFont()->setBold(true);
                        $colonne_courante = $nextcolonne;

                        //Impôt Calculé
                        $nextcolonne = getNextColumnName($colonne_courante);
                        $sheet->setCellValue($nextcolonne."6", "Impôt Calculé");
                        $sheet->getStyle($nextcolonne."6")->getFont()->setBold(true);
                        $colonne_courante = $nextcolonne;

                        //Solde Impôt
                        $nextcolonne = getNextColumnName($colonne_courante);
                        $sheet->setCellValue($nextcolonne."6", "Solde Impôt");
                        $sheet->getStyle($nextcolonne."6")->getFont()->setBold(true);
                        $colonne_courante = $nextcolonne;

                        //Nbre mois
                        $nextcolonne = getNextColumnName($colonne_courante);
                        $sheet->setCellValue($nextcolonne."6", "Nbre mois");
                        $sheet->getStyle($nextcolonne."6")->getFont()->setBold(true);
                        $colonne_courante = $nextcolonne;


//--------------------------------------------------------------------
            //Affichage des valeurs
            $numero_ligne = 7;

            $tout_sal_sql = "SELECT DISTINCT fk_salarie FROM ".MAIN_DB_PREFIX."bulletin";
            $tout_sal_sql .= " WHERE annee>=".$db->escape($annee_debut)." AND annee <= ".$db->escape($annee_fin)." AND mois>=".$db->escape($mois_debut)." AND mois <= ".$db->escape($mois_fin)." AND fk_societe = ".$db->escape($id_societe);
            $tout_sal_sql .= " ORDER BY fk_salarie";
            //Cout Total
            $res_tout_sal = $db->query($tout_sal_sql);
                  
                  if($res_tout_sal){
                        $num_all = $db->num_rows($res_tout_sal);
                        while ($obj_tout_sal = $db->fetch_object($res_tout_sal)){
                              $salaire_brut = 0;
                              $salaire_brut_imposable = 0;
                              $salaire_brut_cotisable = 0;
                              $salaire_net = 0;
                              $salaire_base = 0;
                              $sursalaire = 0;
                              $anciennete = 0;
                              $array_val_pr = array(0);
                              $array_val_ind = array(0);
                              $array_val_pr_exc = array(0);
                              $array_val_hs = array(0);
                              $array_nb_hs = array(0);
                              $its = 0;
                              $inps_patro = 0;
                              $inps_sal = 0;
                              $amo_sal = 0;
                              $amo_patro = 0;
                              $cfe = 0;
                              $tl = 0;
                              $array_val_avance = array(0);
                              $avance_annee = 0;

                              $bulletin_sql = "SELECT rowid, salaire_base, sursalaire, salaire_brut, salaire_brut_imposable, salaire_brut_cotisable, net_payer FROM ".MAIN_DB_PREFIX."bulletin";
                              $bulletin_sql .= " WHERE annee >= ".$db->escape($annee_debut)." AND annee <= ".$db->escape($annee_fin)." AND mois>=".$db->escape($mois_debut)." AND mois <= ".$db->escape($mois_fin)." AND fk_salarie = ".$obj_tout_sal->fk_salarie;
                              $res_bulletin = $db->query($bulletin_sql);
                              $num_all = $db->num_rows($res_bulletin);
                              $cout_annee = 0;
                              while ($obj_bulletin = $db->fetch_object($res_bulletin)){
                                    $cout = 0;


                                    //Ajout du salaire de base
                                    $salaire_base += $obj_bulletin->salaire_base;

                                    //Ajout du sursalaire
                                    $sursalaire += $obj_bulletin->sursalaire;

                                    //Ajout de l'ancienneté
                                    $bulletin_sql_anc = "SELECT taux FROM ".MAIN_DB_PREFIX."bulletin_anciennete WHERE fk_bulletin=".$obj_bulletin->rowid;
                                    $res_bulletin_an = $db->query($bulletin_sql_anc);
                                    $obj_bulletin_an = $db->fetch_object($res_bulletin_an);
                                    $anciennete += round(($obj_bulletin->salaire_base*$obj_bulletin_an->taux)/100);


                                    //Ajout des primes
                                    for ($j=0; $j < count($array_id_pr); $j++) { 
                                          $bulletin_pr = "SELECT montant FROM ".MAIN_DB_PREFIX."bulletin_prime WHERE fk_bulletin=".$obj_bulletin->rowid." AND libelle='".$array_id_pr[$j]."'";
                                          if($db->num_rows($db->query($bulletin_pr)) > 0){//Si le salarié a cette prime
                                                $obj_bulletin_pr = $db->fetch_object($db->query($bulletin_pr));
                                                $array_val_pr[$j] += $obj_bulletin_pr->montant;
                                          }else{
                                                $array_val_pr[$j] += 0;
                                          }
                                    }

                                    //Ajout des Indemnités
                                    for ($j=0; $j < count($array_id_ind); $j++) { 
                                          $bulletin_ind = "SELECT montant FROM ".MAIN_DB_PREFIX."bulletin_indemnite WHERE fk_bulletin=".$obj_bulletin->rowid." AND libelle='".$array_id_ind[$j]."'";
                                          $result_ind = $db->query($bulletin_ind);
                                          if($db->num_rows($result_ind)>0){
                                                $obj_bulletin_ind = $db->fetch_object($result_ind);
                                                $array_val_ind[$j] += $obj_bulletin_ind->montant;
                                          }else{
                                                $array_val_ind[$j] += 0;
                                          }
                                    }

                                    //Primes exceptionnelles
                                    for ($j=0; $j < count($array_id_pr_exc); $j++) { 
                                          $bulletin_pr_exc = "SELECT montant FROM ".MAIN_DB_PREFIX."bulletin_prime_exceptionnelle WHERE fk_bulletin=".$obj_bulletin->rowid." AND libelle='".$array_id_pr_exc[$j]."'";
                                          $result_exc = $db->query($bulletin_pr_exc);
                                          if($db->num_rows($result_exc)>0){
                                                //Si le salarié a cette prime
                                                $obj_bulletin_pr_exc = $db->fetch_object($result_exc);
                                                $array_val_pr_exc[$j] += $obj_bulletin_pr_exc->montant;
                                          }else{
                                                $array_val_pr_exc[$j] += 0;
                                          }
                                    }

                                    //Heure sup
                                    $montant_hs = 0;
                                    for ($j=0; $j < count($array_id_hs); $j++) {
                                          $bulletin_hs = "SELECT nombre_heure_sup, montant FROM ".MAIN_DB_PREFIX."bulletin_heure_sup WHERE fk_bulletin=".$obj_bulletin->rowid." AND libelle='".$array_id_hs[$j]."'";
                                          $obj_bulletin_hs = $db->fetch_object($db->query($bulletin_hs));
                                          $montant_hs += $obj_bulletin_hs->montant;

                                          //nombre d'heure sup
                                          $array_nb_hs[$j] += $obj_bulletin_hs->nombre_heure_sup?:0;

                                          //Montant heure sup
                                          $array_val_hs[$j] += $montant_hs;
                                          
                                    }
                                    

                                    //Salaire brut
                                    $salaire_brut += $obj_bulletin->salaire_brut;

                                    $cout += $obj_bulletin->salaire_brut;

                                    //Salaire brut cotisable
                                    $salaire_brut_cotisable += $obj_bulletin->salaire_brut_cotisable;

                                    //Salaire brut imposable
                                    $salaire_brut_imposable += $obj_bulletin->salaire_brut_imposable;

                                    //Montant des cotisation salariale
                                    $bulletin_cotis_sal = "SELECT SUM(montant_employe) as inps_sal FROM ".MAIN_DB_PREFIX."bulletin_cotisation WHERE fk_bulletin=".$obj_bulletin->rowid." AND fk_cotisation<>6";
                                    $obj_bulletin_cotis = $db->fetch_object($db->query($bulletin_cotis_sal));
                                    
                                    $inps_sal += $obj_bulletin_cotis->inps_sal;
                                    
                                    //Montant des cotisation patronale
                                    $bulletin_cotis_patr = "SELECT SUM(montant_employeur) as inps_patro FROM ".MAIN_DB_PREFIX."bulletin_cotisation WHERE fk_bulletin=".$obj_bulletin->rowid." AND fk_cotisation<>6";
                                    $obj_bulletin_cotis = $db->fetch_object($db->query($bulletin_cotis_patr));

                                    $inps_patro += $obj_bulletin_cotis->inps_patro;
                                    $cout += $obj_bulletin_cotis->inps_patro;

                                    //Montant des impôt I.T.S
                                    $bulletin_taxe = "SELECT montant as montant_imp FROM ".MAIN_DB_PREFIX."bulletin_taxe WHERE fk_bulletin=".$obj_bulletin->rowid;
                                    $obj_bulletin_imp = $db->fetch_object($db->query($bulletin_taxe));

                                    $its += $obj_bulletin_imp->montant_imp;

                                    //AMO salariale
                                    $bulletin_cotis_sal = "SELECT SUM(montant_employe) as amo_sal FROM ".MAIN_DB_PREFIX."bulletin_cotisation WHERE fk_bulletin=".$obj_bulletin->rowid." AND fk_cotisation=6";
                                    $obj_bulletin_cotis = $db->fetch_object($db->query($bulletin_cotis_sal));

                                    $amo_sal += $obj_bulletin_cotis->amo_sal;

                                    //AMO patronale
                                    $bulletin_cotis_patr = "SELECT SUM(montant_employeur) as amo_patro FROM ".MAIN_DB_PREFIX."bulletin_cotisation WHERE fk_bulletin=".$obj_bulletin->rowid." AND fk_cotisation=6";
                                    $obj_bulletin_cotis = $db->fetch_object($db->query($bulletin_cotis_patr));

                                    $amo_patro += $obj_bulletin_cotis->amo_patro;
                                    $cout += $obj_bulletin_cotis->amo_patro;

                                    //Montant CFE
                                    $bulletin_cfe = "SELECT montant_employeur as montant_cfe FROM ".MAIN_DB_PREFIX."bulletin_taxe2 WHERE taux_employeur=3.5 AND fk_bulletin=".$obj_bulletin->rowid;
                                    $obj_bulletin_cfe = $db->fetch_object($db->query($bulletin_cfe));

                                    $cfe += $obj_bulletin_cfe->montant_cfe;

                                    $cout += $obj_bulletin_cfe->montant_cfe;

                                    //Montant TL
                                    $bulletin_tl = "SELECT montant_employeur as montant_tl FROM ".MAIN_DB_PREFIX."bulletin_taxe2 WHERE taux_employeur=1 AND fk_bulletin=".$obj_bulletin->rowid;
                                    $obj_bulletin_tl = $db->fetch_object($db->query($bulletin_tl));

                                    $tl += $obj_bulletin_tl->montant_tl;

                                    $cout += $obj_bulletin_tl->montant_tl;

                                    //Salaire Net
                                    $salaire_net += $obj_bulletin->net_payer;
                                    
                                    //Valeurs des avance
                                    $total_av = 0;
                                    for ($j=0; $j < count($array_libelle_av); $j++) { 
                                          $bulletin_av_sql = "SELECT SUM(montant) as mont FROM ".MAIN_DB_PREFIX."bulletin_avance WHERE fk_bulletin=".$obj_bulletin->rowid." AND libelle='".$array_libelle_av[$j]."'";
                                          $res_avance_bul = $db->query($bulletin_av_sql);
                                          $montant = 0;
                                          if ($obj_bulletin_av = $db->fetch_object($res_avance_bul)) {
                                                $montant = $obj_bulletin_av->mont;
                                                $total_av += $montant;
                                          }

                                          $array_val_avance[$j] += $montant;
                                    }
                                    
                                    //Montant Avance                              
                                    $avance_annee += $total_av;

                                    //Cout total
                                    $cout_annee += $cout;

                              }
                              
                              $un_sal_sql = "SELECT matricule, nom, prenom, date_embauche, categorie, echelon, situation_familiale, nombre_enfant, nombre_enfant_hand, fonction, banque, compte FROM ".MAIN_DB_PREFIX."bulletin";
                              $un_sal_sql .= " WHERE annee>=".$db->escape($annee_debut)." AND annee <= ".$db->escape($annee_fin)." AND mois>=".$db->escape($mois_debut)." AND mois <= ".$db->escape($mois_fin)." AND fk_salarie = ".$obj_tout_sal->fk_salarie;
                              $un_sal_sql .= " ORDER BY fk_salarie";
                              //Cout Total
                              $res_un_sal = $db->query($un_sal_sql);
                              $obj_un_sal = $db->fetch_object($res_un_sal);

                              //Objet Utilisateur
                                    //Ajout d'une nouvelle ligne
                                    $sheet->insertNewRowBefore($numero_ligne+2, $numero_ligne);

                                    //Matricule
                                    $sheet->setCellValue('A'.$numero_ligne, $obj_un_sal->matricule);

                                    $colonne_courante = "A";
                                    //Ajout du nom et prénom
                                    $nextcolonne = getNextColumnName($colonne_courante);
                                    $sheet->setCellValue($nextcolonne.$numero_ligne, $obj_un_sal->prenom);
                                    $colonne_courante = $nextcolonne;
                                    

                                    $nextcolonne = getNextColumnName($colonne_courante);
                                    $sheet->setCellValue($nextcolonne.$numero_ligne, $obj_un_sal->nom);
                                    $colonne_courante = $nextcolonne;

                                    //Date rentrée
                                    $nextcolonne = getNextColumnName($colonne_courante);
                                    $sheet->setCellValue($nextcolonne.$numero_ligne, $obj_un_sal->date_embauche);
                                    $colonne_courante = $nextcolonne;

                                    //Ajout de la catégorie
                                    $categ = $obj_un_sal->categorie;
                                    if(!empty($obj_un_sal->echelon) && $obj_un_sal->echelon != 'N/A')
                                          $categ .= '->'.$obj_un_sal->echelon;
                                    $nextcolonne = getNextColumnName($colonne_courante);
                                    $sheet->setCellValue($nextcolonne.$numero_ligne, $categ);
                                    $colonne_courante = $nextcolonne;

                                    //Ajout la situation familiale
                                    $stuati_fa = $obj_un_sal->situation_familiale.' '.$obj_un_sal->nombre_enfant.' '.$obj_un_sal->nombre_enfant_hand;
                                    $nextcolonne = getNextColumnName($colonne_courante);
                                    $sheet->setCellValue($nextcolonne.$numero_ligne, $stuati_fa);
                                    $colonne_courante = $nextcolonne;

                                    //Fonction
                                    $nextcolonne = getNextColumnName($colonne_courante);
                                    $sheet->setCellValue($nextcolonne.$numero_ligne, $obj_un_sal->fonction);
                                    $colonne_courante = $nextcolonne;

                                    //Banque
                                    $nextcolonne = getNextColumnName($colonne_courante);
                                    $sheet->setCellValue($nextcolonne.$numero_ligne, $obj_un_sal->banque);
                                    $colonne_courante = $nextcolonne;

                                    //Compte
                                    $nextcolonne = getNextColumnName($colonne_courante);
                                    $sheet->setCellValue($nextcolonne.$numero_ligne, $obj_un_sal->compte);
                                    $colonne_courante = $nextcolonne;


                                    //Jours travaillé (12 mois et 13è mois)
                                    $salSql = "SELECT nb_jour_travailler FROM ".MAIN_DB_PREFIX."bulletin where annee>=".$db->escape($annee_debut)." AND annee <= ".$db->escape($annee_fin)." AND mois>=".$db->escape($mois_debut)." AND mois <= ".$db->escape($mois_fin)." AND fk_salarie = ".$obj_tout_sal->fk_salarie;
                                    $result = $db->query($salSql);
                                    $nb_jours = 0;
                                    while ($ob_jour = $db->fetch_object($result)) { 
                                          
                                          $nb_jours += $ob_jour->jour;
                                    }

                                    if (($annee % 4 == 0 && $annee % 100 != 0) || ($annee % 400 == 0)) {
                                          $nb_total_jour = 366;
                                          if($nb_jours > 366)
                                                $nb_jours = 366;
                                    } else {
                                          $nb_total_jour = 365;
                                          if($nb_jours > 365)
                                                $nb_jours = 365;
                                    }

                                    $nextcolonne = getNextColumnName($colonne_courante);
                                    $sheet->setCellValue($nextcolonne.$numero_ligne, $nb_jours);
                                    $colonne_courante = $nextcolonne;

                                    //Taux                                    
                                    $taux = 100;
                                    if($nb_jours != $nb_total_jour){
                                          $taux = round(($nb_jours * 100)/$nb_total_jour, 2);
                                    }

                                    $nextcolonne = getNextColumnName($colonne_courante);
                                    $sheet->setCellValue($nextcolonne.$numero_ligne, $taux."%");
                                    $colonne_courante = $nextcolonne;

                                    //Ajout du salaire de base
                                    $nextcolonne = getNextColumnName($colonne_courante);
                                    $sheet->setCellValue($nextcolonne.$numero_ligne, $salaire_base);
                                    $colonne_courante = $nextcolonne;

                                    //Ajout du sursalaire
                                    $nextcolonne = getNextColumnName($colonne_courante);
                                    $sheet->setCellValue($nextcolonne.$numero_ligne, $sursalaire);
                                    $colonne_courante = $nextcolonne;

                                    //Ajout de l'ancienneté
                                    $nextcolonne = getNextColumnName($colonne_courante);
                                    $sheet->setCellValue($nextcolonne.$numero_ligne, round($anciennete));
                                    $colonne_courante = $nextcolonne;


                                    //Ajout des primes
                                    for ($j=0; $j < count($array_id_pr); $j++) { 
                                          $nextcolonne = getNextColumnName($colonne_courante);
                                          $sheet->setCellValue($nextcolonne.$numero_ligne, $array_val_pr[$j]);
                                          $colonne_courante = $nextcolonne;  
                                    }

                                    //Ajout des Indemnités
                                    //$colonne_courante est déjà définie
                                    for ($j=0; $j < count($array_id_ind); $j++) { 
                                          $nextcolonne = getNextColumnName($colonne_courante);
                                          $sheet->setCellValue($nextcolonne.$numero_ligne, $array_val_ind[$j]);
                                          $colonne_courante = $nextcolonne;  
                                          
                                    }

                                    //Primes exceptionnelles
                                    for ($j=0; $j < count($array_id_pr_exc); $j++) { 
                                          $nextcolonne = getNextColumnName($colonne_courante);
                                          $sheet->setCellValue($nextcolonne.$numero_ligne, $array_val_pr_exc[$j]);
                                          $colonne_courante = $nextcolonne;  
                                    }

                              //Heure sup
                              $montant_hs = 0;
                              for ($j=0; $j < count($array_id_hs); $j++) {
                                    //nombre d'heure sup
                                    $nextcolonne = getNextColumnName($colonne_courante);
                                    $sheet->setCellValue($nextcolonne.$numero_ligne, $array_nb_hs[$j]);
                                    $colonne_courante = $nextcolonne;

                                    //Montant heure sup
                                    $nextcolonne = getNextColumnName($colonne_courante);
                                    $sheet->setCellValue($nextcolonne.$numero_ligne, $array_val_hs[$j]);
                                    $colonne_courante = $nextcolonne;  
                                    
                              }
                                    

                                    //Salaire brut
                                    $nextcolonne = getNextColumnName($colonne_courante);
                                    $sheet->setCellValue($nextcolonne.$numero_ligne, round($salaire_brut));
                                    $colonne_courante = $nextcolonne;

                                    $cout += $obj_bulletin->salaire_brut;

                                    //Salaire brut cotisable
                                    $nextcolonne = getNextColumnName($colonne_courante);
                                    $sheet->setCellValue($nextcolonne.$numero_ligne, round($salaire_brut_cotisable));
                                    $colonne_courante = $nextcolonne;

                                    //Salaire brut imposable
                                    $nextcolonne = getNextColumnName($colonne_courante);
                                    $sheet->setCellValue($nextcolonne.$numero_ligne, round($salaire_brut_imposable));
                                    $colonne_courante = $nextcolonne;

                                    //Montant des cotisation salariale                                    
                                    $nextcolonne = getNextColumnName($colonne_courante);
                                    $sheet->setCellValue($nextcolonne.$numero_ligne, round($inps_sal));
                                    $colonne_courante = $nextcolonne;
                                    
                                    //Montant des cotisation patronale
                                    $nextcolonne = getNextColumnName($colonne_courante);
                                    $sheet->setCellValue($nextcolonne.$numero_ligne, round($inps_patro));
                                    $colonne_courante = $nextcolonne;
                                    $cout += $obj_bulletin_cotis->inps_patro;

                                    //Montant des impôt I.T.S
                                    $nextcolonne = getNextColumnName($colonne_courante);
                                    $sheet->setCellValue($nextcolonne.$numero_ligne, round($its));
                                    $colonne_courante = $nextcolonne;

                                    //AMO salariale
                                    $nextcolonne = getNextColumnName($colonne_courante);
                                    $sheet->setCellValue($nextcolonne.$numero_ligne, round($amo_sal));
                                    $colonne_courante = $nextcolonne;

                                    //AMO patronale
                                    $nextcolonne = getNextColumnName($colonne_courante);
                                    $sheet->setCellValue($nextcolonne.$numero_ligne, round($amo_patro));
                                    $colonne_courante = $nextcolonne;
                                    $cout += $obj_bulletin_cotis->inps_patro;

                                    //Base CFE
                                    $nextcolonne = getNextColumnName($colonne_courante);
                                    $sheet->setCellValue($nextcolonne.$numero_ligne, round($salaire_brut));
                                    $colonne_courante = $nextcolonne;

                                    //Montant CFE
                                    $nextcolonne = getNextColumnName($colonne_courante);
                                    $sheet->setCellValue($nextcolonne.$numero_ligne, round($cfe));
                                    $colonne_courante = $nextcolonne;

                                    $cout += $obj_bulletin_cfe->montant_cfe;

                                    //Base TL
                                    $nextcolonne = getNextColumnName($colonne_courante);
                                    $sheet->setCellValue($nextcolonne.$numero_ligne, round($salaire_brut));
                                    $colonne_courante = $nextcolonne;

                                    //Montant TL
                                    $nextcolonne = getNextColumnName($colonne_courante);
                                    $sheet->setCellValue($nextcolonne.$numero_ligne, round($tl));
                                    $colonne_courante = $nextcolonne;

                                    $cout += $obj_bulletin_tl->montant_tl;

                                    //Salaire Net
                                    $nextcolonne = getNextColumnName($colonne_courante);
                                    $sheet->setCellValue($nextcolonne.$numero_ligne, round($salaire_net));
                                    $colonne_courante = $nextcolonne;
                                    
                                    //Valeurs des avance
                                    $total_av = 0;
                                    for ($j=0; $j < count($array_libelle_av); $j++) { 
                                          $nextcolonne = getNextColumnName($colonne_courante);
                                          $sheet->setCellValue($nextcolonne.$numero_ligne, $array_val_avance[$j]);
                                          $colonne_courante = $nextcolonne;
                                    }
                                    
                                    //Montant Avance                              
                                    $nextcolonne = getNextColumnName($colonne_courante);
                                    $sheet->setCellValue($nextcolonne.$numero_ligne, round($avance_annee));
                                    $colonne_courante = $nextcolonne;

                                    //Net à payer
                                    $nextcolonne = getNextColumnName($colonne_courante);
                                    $sheet->setCellValue($nextcolonne.$numero_ligne, round($salaire_net - $avance_annee));
                                    $colonne_courante = $nextcolonne;

                                    //Cout total
                                    $nextcolonne = getNextColumnName($colonne_courante);
                                    $sheet->setCellValue($nextcolonne.$numero_ligne, round($cout_annee));
                                    $colonne_courante = $nextcolonne;


                                    
                                    //Regularisation
                                    $article29_obj = regularisation_its_bul($obj_tout_sal->fk_salarie, $annee_debut, $annee_fin, $mois_debut, $mois_fin);

                                    //somme_brut_annuel
                                    $nextcolonne = getNextColumnName($colonne_courante);
                                    $sheet->setCellValue($nextcolonne.$numero_ligne, round($article29_obj["somme_brut_annuel"]));
                                    $colonne_courante = $nextcolonne;

                                    //somme_retraite
                                    $nextcolonne = getNextColumnName($colonne_courante);
                                    $sheet->setCellValue($nextcolonne.$numero_ligne, round($article29_obj["somme_retraite"]));
                                    $colonne_courante = $nextcolonne;

                                    //prime_indemnite_non_imposable
                                    $nextcolonne = getNextColumnName($colonne_courante);
                                    $sheet->setCellValue($nextcolonne.$numero_ligne, round($article29_obj["prime_indemnite_non_imposable"]));
                                    $colonne_courante = $nextcolonne;

                                    //avantage_nature
                                    $nextcolonne = getNextColumnName($colonne_courante);
                                    $sheet->setCellValue($nextcolonne.$numero_ligne, round($article29_obj["avantage_nature"]));
                                    $colonne_courante = $nextcolonne;

                                    //somme_brut_imposable_annuel
                                    $nextcolonne = getNextColumnName($colonne_courante);
                                    $sheet->setCellValue($nextcolonne.$numero_ligne, round($article29_obj["somme_brut_imposable_annuel"]));
                                    $colonne_courante = $nextcolonne;

                                    //somme_its_mois
                                    $nextcolonne = getNextColumnName($colonne_courante);
                                    $sheet->setCellValue($nextcolonne.$numero_ligne, round($article29_obj["somme_its_mois"]));
                                    $colonne_courante = $nextcolonne;

                                    //its_annuel
                                    $nextcolonne = getNextColumnName($colonne_courante);
                                    $sheet->setCellValue($nextcolonne.$numero_ligne, round($article29_obj["its_annuel"]));
                                    $colonne_courante = $nextcolonne;

                                    //difference
                                    $nextcolonne = getNextColumnName($colonne_courante);
                                    $sheet->setCellValue($nextcolonne.$numero_ligne, round($article29_obj["difference"]));
                                    $colonne_courante = $nextcolonne;

                                    //nb_mois
                                    $nextcolonne = getNextColumnName($colonne_courante);
                                    $sheet->setCellValue($nextcolonne.$numero_ligne, round($article29_obj["nb_mois"]));
                                    $colonne_courante = $nextcolonne;

                                    $numero_ligne ++;

                        }
                  }
}else{
      $sheet->mergeCells('A7:H8');
      $sheet->setCellValue('A7', "Tous les mois ne sont pas encore cloturés");

}

//Enregistrement dans les log || Traçabilité
$sql_select = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$user->id;
$obj = $db->fetch_object($db->query($sql_select));

//On garde la trace de l'action
$action_effectue = "Exportation des états de salaire de ".$mois_tab[$mois_debut - 1]." ".$annee_debut." a ".$mois_tab[$mois_fin - 1]." ".$annee_fin." de ".$nom_soc." dans société Salaire";
$sql_log = 'INSERT INTO '.MAIN_DB_PREFIX.'log (fk_user, nom, prenom, quand, action_effectue, object_concerne)';
$sql_log .= ' VALUES('.$user->id.', "'.$obj->lastname.'","'.$obj->firstname.'",now(),"'.$action_effectue.'","Exportation")';
$db->query($sql_log);

// Envoyer le fichier au navigateur
$filename = $nom_soc."_".$mois_debut."_".$annee_debut."_a_".$mois_fin."_".$annee_fin."_".gmdate('D_d_M_Y_H:i:s'); //le nom du fichier
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

  function regularisation_its_bul($id_salarie, $annee_debut, $annee_fin, $mois_debut, $mois_fin){
    global $db;
    $obj_array = array();
        $somme_brut = 0;
        $somme_brut_imposable = 0;
        $somme_its = 0;
        //Brut et brut imposable bulletin
        $sql_verif = "SELECT SUM(salaire_brut) as brut, SUM(salaire_brut_imposable) as brut_imposable FROM ".MAIN_DB_PREFIX."bulletin WHERE annee>=".$db->escape($annee_debut)." AND annee <= ".$db->escape($annee_fin)." AND mois>=".$db->escape($mois_debut)." AND mois <= ".$db->escape($mois_fin)." AND fk_salarie=".$id_salarie;
        $res_verif = $db->query($sql_verif);
        if($res_verif){
          $obj_verif = $db->fetch_object($res_verif);
          $somme_brut = $obj_verif->brut;
          $somme_brut_imposable = $obj_verif->brut_imposable;
        }

       //Brut et brut imposable Bulletin bonus
        $sql_verif = "SELECT SUM(salaire_brut) as brut, SUM(salaire_brut_imposable) as brut_imposable FROM ".MAIN_DB_PREFIX."bulletin_bonus WHERE annee>=".$db->escape($annee_debut)." AND annee <= ".$db->escape($annee_fin)." AND mois>=".$db->escape($mois_debut)." AND mois <= ".$db->escape($mois_fin)." AND fk_salarie=".$id_salarie;
        $res_verif = $db->query($sql_verif);
        if($res_verif){
          $obj_verif = $db->fetch_object($res_verif);
          $somme_brut += $obj_verif->brut;
          $somme_brut_imposable += $obj_verif->brut_imposable;
          
        }
  
        //Taxes bulletin
        $sql_bul = "SELECT rowid FROM ".MAIN_DB_PREFIX."bulletin WHERE annee>=".$db->escape($annee_debut)." AND annee <= ".$db->escape($annee_fin)." AND mois>=".$db->escape($mois_debut)." AND mois <= ".$db->escape($mois_fin)." AND fk_salarie=".$id_salarie;
        $res_bul = $db->query($sql_bul);
        if($res_bul){
			$num_bul = $db->num_rows($res_bul);
			$nb_mois = $num_bul;
			$j = 0;
			while($j < $num_bul){
				$obj_bul = $db->fetch_object($res_bul);
				$sql_taxe = "SELECT montant FROM ".MAIN_DB_PREFIX."bulletin_taxe WHERE fk_bulletin=".$obj_bul->rowid;
				$res_taxe = $db->query($sql_taxe);
				if($res_taxe){
				$obj_taxe = $db->fetch_object($res_taxe);
				$somme_its += $obj_taxe->montant;
				}
				$j ++;
			}
        

			//Taxes bulletin bonus
			$sql_bul_bonus = "SELECT rowid FROM ".MAIN_DB_PREFIX."bulletin_bonus WHERE annee>=".$db->escape($annee_debut)." AND annee <= ".$db->escape($annee_fin)." AND mois>=".$db->escape($mois_debut)." AND mois <= ".$db->escape($mois_fin)." AND fk_salarie=".$id_salarie;
			$res_bul_bonus = $db->query($sql_bul_bonus);
			if($res_bul_bonus){
			$num_bul_bonus = $db->num_rows($res_bul_bonus);
			$j = 0;
			while($j < $num_bul_bonus){
				$obj_bul_bonus = $db->fetch_object($res_bul_bonus);
				$sql_taxe = "SELECT montant FROM ".MAIN_DB_PREFIX."bulletin_bonus_taxe WHERE fk_bulletin=".$obj_bul_bonus->rowid;
				$res_taxe = $db->query($sql_taxe);
				if($res_taxe){
				$obj_taxe = $db->fetch_object($res_taxe);
				$somme_its += $obj_taxe->montant;
				}
				$j ++;
			}
			}

		}

		//Retraite bulletin
		  $somme_retraite = 0;
        $sql_bul_ret = "SELECT rowid FROM ".MAIN_DB_PREFIX."bulletin WHERE annee>=".$db->escape($annee_debut)." AND annee <= ".$db->escape($annee_fin)." AND mois>=".$db->escape($mois_debut)." AND mois <= ".$db->escape($mois_fin)." AND fk_salarie=".$id_salarie;
        $res_bul_ret = $db->query($sql_bul_ret);
        if($res_bul_ret){
          while($obj_bul_ret = $db->fetch_object($res_bul_ret)){
            $sql_ret = "SELECT montant_employe FROM ".MAIN_DB_PREFIX."bulletin_cotisation WHERE fk_cotisation = 3 AND fk_bulletin=".$obj_bul_ret->rowid;
            $res_ret = $db->query($sql_ret);
            if($obj_retraite = $db->fetch_object($res_ret)){
                  $somme_retraite += $obj_retraite->montant_employe;
            }

          }
		  

		}

        //retraite bulletin bonus
        $sql_bul_ret = "SELECT rowid FROM ".MAIN_DB_PREFIX."bulletin_bonus WHERE annee>=".$db->escape($annee_debut)." AND annee <= ".$db->escape($annee_fin)." AND mois>=".$db->escape($mois_debut)." AND mois <= ".$db->escape($mois_fin)." AND fk_salarie=".$id_salarie;
        $res_bul_ret = $db->query($sql_bul_ret);
        if($res_bul_ret){
          while($obj_bul_ret = $db->fetch_object($res_bul_ret)){
            $sql_ret = "SELECT montant_employe FROM ".MAIN_DB_PREFIX."bulletin_bonus_cotisation WHERE fk_cotisation = 3 AND fk_bulletin=".$obj_bul_ret->rowid;
            $res_ret = $db->query($sql_ret);
            if($obj_retraite = $db->fetch_object($res_ret)){
                  $somme_retraite += $obj_retraite->montant_employe;
            }

          }

		    }
  
        $sql_bul_info = "SELECT rowid, matricule, nom, prenom, situation_familiale, nombre_enfant, nombre_enfant_hand FROM ".MAIN_DB_PREFIX."bulletin WHERE annee>=".$db->escape($annee_debut)." AND annee <= ".$db->escape($annee_fin)." AND mois>=".$db->escape($mois_debut)." AND mois <= ".$db->escape($mois_fin)." AND fk_salarie=".$id_salarie;
        $res_bul_info = $db->query($sql_bul_info);
        if($res_bul_info){
          $num_bul_info = $db->num_rows($res_bul_info);
          if(0 < $num_bul_info)
            $obj_bul_info = $db->fetch_object($res_bul_info);
        }
		
          $obj_array["somme_brut_annuel"] = round($somme_brut);
          $obj_array["somme_brut_imposable_annuel"] = round($somme_brut_imposable);
          $obj_array["somme_retraite"] = round($somme_retraite);
          $obj_array["prime_indemnite_non_imposable"] = $somme_brut - $somme_retraite - $somme_brut_imposable;
          $obj_array["avantage_nature"] = 0;
          $obj_array["somme_its_mois"] = round($somme_its);
          $obj_array["nb_mois"] = $nb_mois ?:0;
          $its_annuel = its_salarie_annuel_bul($db, round($somme_brut_imposable), $obj_bul_info->situation_familiale, $obj_bul_info->nombre_enfant, $obj_bul_info->nombre_enfant_hand);
		  
          $obj_array["its_annuel"] = round($its_annuel["montant"]);
		  if($its_annuel["montant"] == 0){
				$obj_array["its_annuel"] = $somme_its;
			}
		  $obj_array["taux_its_annuel"] = $its_annuel["taux"];
          $obj_array["difference"] = round($obj_array["its_annuel"]) - round($somme_its);

    return $obj_array;
}


function its_salarie_annuel_bul($db, $salaire_brut, $situation_familiale = "Celibataire", $nb_enfant = 0, $nb_enf_hand = 0){
	
		$mont = (string)$salaire_brut;
        $its_annuel = array();
	if ($salaire_brut <= 250) {
        return 0;
    } else {
        // Derniers 3 chiffres
        $dern_ch = intval(substr($mont, -3));  

        // Retrait des 3 derniers chiffres pour pouvoir reconstruire après
        $base = substr($mont, 0, -3);

        if ($dern_ch >= 0 && $dern_ch <= 250) {
            $mont = $base . "000";
        } else if ($dern_ch >= 251 && $dern_ch <= 500) {
            $mont = $base . "250";
        } else if ($dern_ch >= 501 && $dern_ch <= 750) {
            $mont = $base . "500";
        } else if ($dern_ch >= 751 && $dern_ch <= 999) {
            $mont = $base . "750";
        }
	
		$ss = $mont;

	//-----------------------------------------	
			$tab = 0;
			$grille_bareme = "SELECT rowid FROM ".MAIN_DB_PREFIX."bareme_taxe WHERE fk_taxe=1 AND actif=1";
            $result_grille_bareme = $db->query($grille_bareme);
            if($result_grille_bareme){
				
                $obj_grille_bareme = $db->fetch_object($result_grille_bareme);
				$sql_bareme = "SELECT * FROM ".MAIN_DB_PREFIX."taxe WHERE fk_bareme =".$db->escape($obj_grille_bareme->rowid)." AND fk_type = 1 AND montant_debut <= ".$db->escape($ss)." ORDER BY montant_debut ASC";
				
				$result_bareme = $db->query($sql_bareme);
				if($result_bareme){
					$i = 0;
					$num = $db->num_rows($result_bareme);
					while ($i < $num) {
						$bareme = $db->fetch_object($result_bareme);
						if($num >= 2)
							if($i == ($num - 1)){
								$tab = $tab + ((($ss - $bareme->montant_debut)*$bareme->taux)/100);
							}else if($i == ($num - 2)){

								$tab = $tab +  $bareme->valeur;
							}
                            
						$i ++;
					}
                }
				
					$taux = 0;
					if($situation_familiale == "Marié")
						$taux = 10;
					$taux = $taux + ($nb_enfant - $nb_enf_hand)*2.5;
					$taux = $taux + $nb_enf_hand*10;


					$its_brut = $tab;

					$its_annuel_net = $its_brut - ($its_brut * $taux / 100);

					$taux_its_annuel =  ($its_annuel_net/$ss)*100;

					$taux_its_reduit = $taux_its_annuel - 2;

					if($taux_its_reduit < 0)
						$taux_its_reduit = 0;

					$its_annuel["taux"] = $taux_its_reduit;
                    $its_annuel["montant"] = ($taux_its_reduit*$ss)/100;

		}

		return $its_annuel;
	}
		
}
