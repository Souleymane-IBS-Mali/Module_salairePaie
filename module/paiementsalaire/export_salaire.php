<?php
// Inclure le chargement automatique de Composer
//require '../vendor/autoload.php';
require_once './../main.inc.php';

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


//Récupération des données
$prenom = GETPOST("prenom", "alpha");
$nom = GETPOST("nom", "alpha");
$date_entree = GETPOST("date_entree", "alpha");
$fonction = GETPOST("fonction", "alpha");
$banque = GETPOST("banque", "alpha");
$compte = GETPOST("compte", "alpha");
$nb_jour_tr = GETPOST("nb_jour_tr", "alpha");
$nb_heure_tr = GETPOST("nb_heure_tr", "alpha");
$taux = GETPOST("pourcentage", "alpha");
$categorie = GETPOST("categorie", "alpha");
$situation_matrimoniale = GETPOST("situation_matrimoniale", "alpha");
$salaire_base = GETPOST("salaire_base", "alpha");
$sursalaire = GETPOST("sursalaire", "alpha");
$anciennete = GETPOST("anciennete", "alpha");
$prime = GETPOST("primes", "alpha");
$indemnite = GETPOST("indemnites", "alpha");
$salaire_brut = GETPOST("salaire_brut", "alpha");
$salaire_brut_imposable = GETPOST("salaire_brut_imposable", "alpha");
$salaire_brut_cotisable = GETPOST("salaire_brut_cotisable", "alpha");
$inps_employe = GETPOST("inps_employe", "alpha");
$inps_employeur = GETPOST("inps_employeur", "alpha");
$amo_employe = GETPOST("amo_employe", "alpha");
$amo_employeur = GETPOST("amo_employeur", "alpha");
$its = GETPOST("its", "alpha");
$base_cfe = GETPOST("base_cfe", "alpha");
$montant_cfe = GETPOST("montant_cfe", "alpha");
$base_tl = GETPOST("base_tl", "alpha");
$montant_tl = GETPOST("montant_tl", "alpha");
$avance = GETPOST("avance", "alpha");
$net_payer = GETPOST("net_payer", "alpha");
$cout = GETPOST("cout", "alpha");

$heure_sup = GETPOST("heure_sup", "alpha");




$sql_soc = "SELECT nom FROM ".MAIN_DB_PREFIX."societe WHERE rowid=".$id_societe;
$res_soc = $db->query($sql_soc);
$obj_soc = $db->fetch_object($res_soc);

$nom_soc= $obj_soc->nom;


$mois_tab = array(" Janvier "," Février "," Mars "," Avril "," Mai "," Juin "," Juillet "," Août "," Septembre "," Octobre "," Novembre "," Décembre ");

// Ajouter des données
$sheet = $spreadsheet->getActiveSheet();
// Fusionner les cellules de A1 à B1
$sheet->mergeCells('A1:H3');
//$sheet->mergeCells('A1:A2');

$info = "Etat de salaire de".$mois_tab[$mois-1]." ".$annee." de ".$nom_soc."";
$sheet->setCellValue('A1', $info);
      $sheet->getStyle('A1')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
      

      // Appliquer du style à la cellule fusionnée (optionnel)
$sheet->getStyle('A1')->getFont()->setBold(true);
$sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('A1')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);


// Renommer la feuille de calcul
$sheet->setTitle('Exemple');

$bulletin_sql = "SELECT * FROM ".MAIN_DB_PREFIX."bulletin WHERE annee=".$annee." AND mois=".$mois." AND fk_societe=".$id_societe;
      $bulletin_sql .= " ORDER BY rowid";
      $res_bulletin = $db->query($bulletin_sql);
        if($res_bulletin)
            if($db->num_rows($res_bulletin)>0){
            //Les titre des colonnes
            //Matricule
                  $sheet->setCellValue('A5', 'MATRICULE');
                  $sheet->getStyle('A5')->getFont()->setBold(true);
                  $sheet->mergeCells('A5:A6');


                  $colonne_courante = "A";

                  //Prénom
                  $nextcolonne = getNextColumnName($colonne_courante);
                  $sheet->setCellValue($nextcolonne.'5', 'PRENOM');
                  $sheet->getStyle($nextcolonne.'5')->getFont()->setBold(true);
                  $sheet->mergeCells($nextcolonne.'5:'.$nextcolonne.'6');
                  $colonne_courante = $nextcolonne;

                  //NOM
                  $nextcolonne = getNextColumnName($colonne_courante);
                  $sheet->setCellValue($nextcolonne.'5', 'NOM');
                  $sheet->getStyle($nextcolonne.'5')->getFont()->setBold(true);
                  $sheet->mergeCells($nextcolonne.'5:'.$nextcolonne.'6');
                  $colonne_courante = $nextcolonne;

                  //Date rentrée
                  if(!empty($date_entree)){
                        $nextcolonne = getNextColumnName($colonne_courante);
                        $sheet->setCellValue($nextcolonne.'5', 'DATE RENTREE');
                        $sheet->getStyle($nextcolonne.'5')->getFont()->setBold(true);
                        $sheet->mergeCells($nextcolonne.'5:'.$nextcolonne.'6');
                        $colonne_courante = $nextcolonne;
                  }

                  //Catégorie
                  if(!empty($categorie)){
                        $nextcolonne = getNextColumnName($colonne_courante);
                        $sheet->setCellValue($nextcolonne.'5', 'CATEGORIE');
                        $sheet->getStyle($nextcolonne.'5')->getFont()->setBold(true);
                        $sheet->mergeCells($nextcolonne.'5:'.$nextcolonne.'6');
                        $colonne_courante = $nextcolonne;
                  }

                  //Situation matrimoniale
                  if(!empty($situation_matrimoniale)){
                        $nextcolonne = getNextColumnName($colonne_courante);
                        $sheet->setCellValue($nextcolonne.'5', 'SITUAT. MAT');
                        $sheet->getStyle($nextcolonne.'5')->getFont()->setBold(true);
                        $sheet->mergeCells($nextcolonne.'5:'.$nextcolonne.'6');
                        $colonne_courante = $nextcolonne;
                  }

                  //Fonction
                  if(!empty($fonction)){
                        $nextcolonne = getNextColumnName($colonne_courante);
                        $sheet->setCellValue($nextcolonne.'5', 'FONCTION');
                        $sheet->getStyle($nextcolonne.'5')->getFont()->setBold(true);
                        $sheet->mergeCells($nextcolonne.'5:'.$nextcolonne.'6');
                        $colonne_courante = $nextcolonne;
                  }

                  //Banque
                  if(!empty($banque)){
                        $nextcolonne = getNextColumnName($colonne_courante);
                        $sheet->setCellValue($nextcolonne.'5', 'BANQUE');
                        $sheet->getStyle($nextcolonne.'5')->getFont()->setBold(true);
                        $sheet->mergeCells($nextcolonne.'5:'.$nextcolonne.'6');
                        $colonne_courante = $nextcolonne;
                  }

                  //Compte
                  if(!empty($compte)){
                        $nextcolonne = getNextColumnName($colonne_courante);
                        $sheet->setCellValue($nextcolonne.'5', 'N° COMPTE');
                        $sheet->getStyle($nextcolonne.'5')->getFont()->setBold(true);
                        $sheet->mergeCells($nextcolonne.'5:'.$nextcolonne.'6');
                        $colonne_courante = $nextcolonne;
                  }

                  //Jours Travaillé
                  if(!empty($nb_jour_tr)){
                        $nextcolonne = getNextColumnName($colonne_courante);
                        $sheet->setCellValue($nextcolonne.'5', 'JOURS TRAV.');
                        $sheet->getStyle($nextcolonne.'5')->getFont()->setBold(true);
                        $sheet->mergeCells($nextcolonne.'5:'.$nextcolonne.'6');
                        $colonne_courante = $nextcolonne;
                  }

                  //Taux Pourcentage
                  if(!empty($taux)){
                        $nextcolonne = getNextColumnName($colonne_courante);
                        $sheet->setCellValue($nextcolonne.'5', 'TAUX');
                        $sheet->getStyle($nextcolonne.'5')->getFont()->setBold(true);
                        $sheet->mergeCells($nextcolonne.'5:'.$nextcolonne.'6');
                        $colonne_courante = $nextcolonne;
                  }

                  //salaire de base
                  if(!empty($salaire_base)){
                        $nextcolonne = getNextColumnName($colonne_courante);
                        $sheet->setCellValue($nextcolonne.'5', 'SAL. BASE');
                        $sheet->getStyle($nextcolonne.'5')->getFont()->setBold(true);
                        $sheet->mergeCells($nextcolonne.'5:'.$nextcolonne.'6');
                        $colonne_courante = $nextcolonne;
                  }

                  //Sursalaire
                  if(!empty($sursalaire)){
                        $nextcolonne = getNextColumnName($colonne_courante);
                        $sheet->setCellValue($nextcolonne.'5', 'SURSALAIRE');
                        $sheet->getStyle($nextcolonne.'5')->getFont()->setBold(true);
                        $sheet->mergeCells($nextcolonne.'5:'.$nextcolonne.'6');
                        $colonne_courante = $nextcolonne;
                  }

                  //Prime d'Aciennete
                  if(!empty($anciennete)){
                        $bulletin_sql_anc = "SELECT bul.rowid, bul_an.libelle FROM ".MAIN_DB_PREFIX."bulletin as bul";
                        $bulletin_sql_anc .= " LEFT JOIN ".MAIN_DB_PREFIX."bulletin_anciennete as bul_an ON bul.rowid=bul_an.fk_bulletin";
                        $bulletin_sql_anc .= " WHERE annee=".$annee." AND mois=".$mois." AND fk_societe=".$id_societe;
                        $res_bulletin_an = $db->query($bulletin_sql_anc);
                        $obj_bulletin_an = $db->fetch_object($res_bulletin_an);
                        $nextcolonne = getNextColumnName($colonne_courante);
                        $sheet->setCellValue($nextcolonne.'5', strtoupper($obj_bulletin_an->libelle));
                        $sheet->getStyle($nextcolonne.'5')->getFont()->setBold(true);
                        $sheet->mergeCells($nextcolonne.'5:'.$nextcolonne.'6');
                        $colonne_courante = $nextcolonne;
                  }

                  
                  $numero_colonne = 5;
                  //Selection des primes
                  $array_id_pr = array();
                  $array_id_ind = array();
                  $array_id_pr_exc = array();
                  $array_id_hs = array();
                  $array_libelle_av = array();

                  if(!empty($prime)){
                        $bulletin_sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."bulletin";
                        $bulletin_sql .= " WHERE annee=".$annee." AND mois=".$mois." AND fk_societe=".$id_societe;
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
                                          if(!in_array($obj_bulletin_pr->fk_prime, $array_id_pr)){
                                                $array_id_pr[] = $obj_bulletin_pr->fk_prime;
                                                //$sheet->insertNewColumnBefore('I', 9);
                                                $nextcolonne = getNextColumnName($colonne_courante);
                                                $sheet->setCellValue($nextcolonne.'5', strtoupper($obj_bulletin_pr->libelle));
                                                $sheet->getStyle($nextcolonne.'5')->getFont()->setBold(true);
                                                $sheet->mergeCells($nextcolonne.'5:'.$nextcolonne.'6');
                                                $colonne_courante = $nextcolonne;
                                          }
                                          $j ++;
                                    }
                                    $i ++;
                              }
                        }
                  }
  
                  //Selection des indemnités
                  if(!empty($indemnite)){
                        $bulletin_sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."bulletin";
                        $bulletin_sql .= " WHERE annee=".$annee." AND mois=".$mois." AND fk_societe=".$id_societe;
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
                                          if(!in_array($obj_bulletin_ind->fk_indemnite, $array_id_ind)){
                                                $array_id_ind[] = $obj_bulletin_ind->fk_indemnite;
                                                //$sheet->insertNewColumnBefore('I', 9);
                                                $nextcolonne = getNextColumnName($colonne_courante);
                                                $sheet->setCellValue($nextcolonne.'5', strtoupper($obj_bulletin_ind->libelle));
                                                $sheet->getStyle($nextcolonne.'5')->getFont()->setBold(true);
                                                $sheet->mergeCells($nextcolonne.'5:'.$nextcolonne.'6');
                                                $colonne_courante = $nextcolonne;
                                          }
                                          $j ++;
                                    }
                                    $i ++;
                              }
                        }
                  }


                  //Primes exceptionnelle
                  if(!empty($prime)){
                        $bulletin_sql_exc = "SELECT rowid FROM ".MAIN_DB_PREFIX."bulletin";
                        $bulletin_sql_exc .= " WHERE annee=".$annee." AND mois=".$mois." AND fk_societe=".$id_societe;
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
                                          if(!in_array($obj_bulletin_pr_exc->fk_indemnite, $array_id_pr_exc)){
                                                $array_id_pr_exc[] = $obj_bulletin_pr_exc->fk_indemnite;
                                                //$sheet->insertNewColumnBefore('I', 9);
                                                $nextcolonne = getNextColumnName($colonne_courante);
                                                $sheet->setCellValue($nextcolonne.'5', strtoupper($obj_bulletin_pr_exc->libelle));
                                                $sheet->getStyle($nextcolonne.'5')->getFont()->setBold(true);
                                                $sheet->mergeCells($nextcolonne.'5:'.$nextcolonne.'6');

                                                $colonne_courante = $nextcolonne;
                                          }
                                          $a ++;
                                    }
                                    $i ++;
                              }
                        }
                  }
                  
                  //Heures sup
                  if(!empty($heure_sup)){
                        $bulletin_sql_hs = "SELECT rowid FROM ".MAIN_DB_PREFIX."bulletin";
                        $bulletin_sql_hs .= " WHERE annee=".$annee." AND mois=".$mois." AND fk_societe=".$id_societe;
                        $res_bulletin_hs = $db->query($bulletin_sql_hs);
                        if($res_bulletin_hs){
                              $i = 0;
                              $num_all = $db->num_rows($res_bulletin_hs);
                              while ($i < ($num_all)){
                                    $obj_bulletin_hs = $db->fetch_object($res_bulletin_hs);
                                    $bulletin_hs = "SELECT DISTINCT fk_heur_sup, libelle FROM ".MAIN_DB_PREFIX."bulletin_heure_sup WHERE fk_bulletin=".$obj_bulletin_hs->rowid;
                                    
                                    if($db->num_rows($db->query($bulletin_hs))>0){
                                          $obj_bulletin_heure_sup = $db->fetch_object($db->query($bulletin_hs));
                                          if(!in_array($obj_bulletin_heure_sup->fk_heur_sup, $array_id_hs)){
                                                $array_id_hs[] = $obj_bulletin_heure_sup->fk_heur_sup;
                                                //$sheet->insertNewColumnBefore('I', 9);
                                                      $nextcolonne = getNextColumnName($colonne_courante);
                                                      $sheet->setCellValue($nextcolonne.'5', "HEURES SUP");
                                                      $sheet->getStyle($nextcolonne.'5')->getFont()->setBold(true);
                                                      $sheet->mergeCells($nextcolonne.'5:'.$nextcolonne.'6');
                                                      $colonne_courante = $nextcolonne;
                                          }
                                    }
                                    $i ++;
                              }
                        }
                  }
                  
                  //Salaire brut
                  if(!empty($salaire_brut)){
                        $nextcolonne = getNextColumnName($colonne_courante);
                        $sheet->setCellValue($nextcolonne."5", "SAL. BRUT");
                        $sheet->getStyle($nextcolonne."5")->getFont()->setBold(true);
                        $sheet->mergeCells($nextcolonne.'5:'.$nextcolonne.'6');
                        $colonne_courante = $nextcolonne;
                  }

                  //Salaire brut Cotisable
                  if(!empty($salaire_brut_cotisable)){
                        $nextcolonne = getNextColumnName($colonne_courante);
                        $sheet->setCellValue($nextcolonne."5", "SAL. COTIS");
                        $sheet->getStyle($nextcolonne."5")->getFont()->setBold(true);
                        $sheet->mergeCells($nextcolonne.'5:'.$nextcolonne.'6');
                        $colonne_courante = $nextcolonne;
                  }

                  //Salaire brut Imposable
                  if(!empty($salaire_brut_imposable)){
                        $nextcolonne = getNextColumnName($colonne_courante);
                        $sheet->setCellValue($nextcolonne."5", "SAL. IMP.");
                        $sheet->getStyle($nextcolonne."5")->getFont()->setBold(true);
                        $sheet->mergeCells($nextcolonne.'5:'.$nextcolonne.'6');
                        $colonne_courante = $nextcolonne;
                  }

                  //INPS SALARIALE
                  if(!empty($inps_employe)){
                        $nextcolonne = getNextColumnName($colonne_courante);
                        $sheet->setCellValue($nextcolonne."5", "INPS SAL.");
                        $sheet->getStyle($nextcolonne."5")->getFont()->setBold(true);
                        $sheet->mergeCells($nextcolonne.'5:'.$nextcolonne.'6');
                        $colonne_courante = $nextcolonne;
                  }

                  //INPS PATRONALE
                  if(!empty($inps_employeur)){
                        $nextcolonne = getNextColumnName($colonne_courante);
                        $sheet->setCellValue($nextcolonne."5", "INPS PATRO.");
                        $sheet->getStyle($nextcolonne."5")->getFont()->setBold(true);
                        $sheet->mergeCells($nextcolonne.'5:'.$nextcolonne.'6');
                        $colonne_courante = $nextcolonne;
                  }

                  //ITS
                  if(!empty($its)){
                        $nextcolonne = getNextColumnName($colonne_courante);
                        $sheet->setCellValue($nextcolonne."5", "ITS.");
                        $sheet->getStyle($nextcolonne."5")->getFont()->setBold(true);
                        $sheet->mergeCells($nextcolonne.'5:'.$nextcolonne.'6');
                        $colonne_courante = $nextcolonne;
                  }

                  //AMO
                  if(!empty($amo_employe)){
                        $nextcolonne = getNextColumnName($colonne_courante);
                        $sheet->setCellValue($nextcolonne."5", "AMO SAL.");
                        $sheet->getStyle($nextcolonne."5")->getFont()->setBold(true);
                        $sheet->mergeCells($nextcolonne.'5:'.$nextcolonne.'6');
                        $colonne_courante = $nextcolonne;
                  }

                  //AMO Patronale
                  if(!empty($amo_employeur)){
                        $nextcolonne = getNextColumnName($colonne_courante);
                        $sheet->setCellValue($nextcolonne."5", "AMO PATRO.");
                        $sheet->getStyle($nextcolonne."5")->getFont()->setBold(true);
                        $sheet->mergeCells($nextcolonne.'5:'.$nextcolonne.'6');
                        $colonne_courante = $nextcolonne;
                  }

                  //Base CFE
                  if(!empty($base_cfe)){
                        $nextcolonne = getNextColumnName($colonne_courante);
                        $sheet->setCellValue($nextcolonne."5", "BASE CFE");
                        $sheet->getStyle($nextcolonne."5")->getFont()->setBold(true);
                        $sheet->mergeCells($nextcolonne.'5:'.$nextcolonne.'6');
                        $colonne_courante = $nextcolonne;
                  }

                  //Montant CFE
                  if(!empty($montant_cfe)){
                        $nextcolonne = getNextColumnName($colonne_courante);
                        $sheet->setCellValue($nextcolonne."5", "MONTANT CFE.");
                        $sheet->getStyle($nextcolonne."5")->getFont()->setBold(true);
                        $sheet->mergeCells($nextcolonne.'5:'.$nextcolonne.'6');
                        $colonne_courante = $nextcolonne;
                  }

                  //Base TL
                  if(!empty($base_tl)){
                        $nextcolonne = getNextColumnName($colonne_courante);
                        $sheet->setCellValue($nextcolonne."5", "BASE TL.");
                        $sheet->getStyle($nextcolonne."5")->getFont()->setBold(true);
                        $sheet->mergeCells($nextcolonne.'5:'.$nextcolonne.'6');
                        $colonne_courante = $nextcolonne;
                  }

                  //Montant TL
                  if(!empty($montant_tl)){
                        $nextcolonne = getNextColumnName($colonne_courante);
                        $sheet->setCellValue($nextcolonne."5", "MONTANT TL.");
                        $sheet->getStyle($nextcolonne."5")->getFont()->setBold(true);
                        $sheet->mergeCells($nextcolonne.'5:'.$nextcolonne.'6');
                        $colonne_courante = $nextcolonne;
                  }

                  //Salaire net
                  if(!empty($net_payer)){
                        $nextcolonne = getNextColumnName($colonne_courante);
                        $sheet->setCellValue($nextcolonne."5", "SALAIRE NET");
                        $sheet->getStyle($nextcolonne."5")->getFont()->setBold(true);
                        $sheet->mergeCells($nextcolonne.'5:'.$nextcolonne.'6');
                        $colonne_courante = $nextcolonne;
                  }

                  //Liste des avance
                  if(!empty($avance)){
                        $bulletin_sql_av = "SELECT rowid FROM ".MAIN_DB_PREFIX."bulletin";
                        $bulletin_sql_av .= " WHERE annee=".$annee." AND mois=".$mois." AND fk_societe=".$id_societe;
                        $res_bulletin_av = $db->query($bulletin_sql_av);
                        if($res_bulletin_av){
                              $i = 0;
                              $num_all = $db->num_rows($res_bulletin_av);
                              while ($i < ($num_all)){
                                    $obj_bulletinav = $db->fetch_object($res_bulletin_av);
                                    $bulletin_av_sql = "SELECT libelle FROM ".MAIN_DB_PREFIX."bulletin_avance WHERE fk_bulletin=".$obj_bulletinav->rowid;
                                    $res_avance_bul = $db->query($bulletin_av_sql);
                                    $num_av = $db->num_rows($res_avance_bul);
                                    $a = 0;
                                    while ($a < $num_av) {
                                          $obj_bulletin_av = $db->fetch_object($res_avance_bul);
                                          if(!in_array($obj_bulletin_av->libelle, $array_libelle_av)){
                                                $array_libelle_av[] = $obj_bulletin_av->libelle;
                                                //$sheet->insertNewColumnBefore('I', 9);
                                                      $nextcolonne = getNextColumnName($colonne_courante);
                                                      $sheet->setCellValue($nextcolonne.'5', strtoupper($obj_bulletin_av->libelle));
                                                      $sheet->getStyle($nextcolonne.'5')->getFont()->setBold(true);
                                                      $sheet->mergeCells($nextcolonne.'5:'.$nextcolonne.'6');

                                                      $colonne_courante = $nextcolonne;
                                          }
                                          $a ++;
                                    }
                                    $i ++;
                              }
                        }
                  }

                  //Total des avances
                  if(!empty($avance)){
                        $nextcolonne = getNextColumnName($colonne_courante);
                        $sheet->setCellValue($nextcolonne."5", "TOTAL AVANCE");
                        $sheet->getStyle($nextcolonne."5")->getFont()->setBold(true);
                        $sheet->mergeCells($nextcolonne.'5:'.$nextcolonne.'6');
                        $colonne_courante = $nextcolonne;
                  }

                  //Salaire net
                  if(!empty($net_payer)){
                        $nextcolonne = getNextColumnName($colonne_courante);
                        $sheet->setCellValue($nextcolonne."5", "NET PAYE");
                        $sheet->getStyle($nextcolonne."5")->getFont()->setBold(true);
                        $sheet->mergeCells($nextcolonne.'5:'.$nextcolonne.'6');
                        $colonne_courante = $nextcolonne;
                  }

                  //Cout Total
                  if(!empty($cout)){
                        $nextcolonne = getNextColumnName($colonne_courante);
                        $sheet->setCellValue($nextcolonne."5", "COUT");
                        $sheet->getStyle($nextcolonne."5")->getFont()->setBold(true);
                        $sheet->mergeCells($nextcolonne.'5:'.$nextcolonne.'6');
                  }
                  //$colonne_courante = $nextcolonne;

//--------------------------------------------------------------------
            //Affichage des valeurs
            $numero_ligne = 7;
            $bulletin_sql = "SELECT * FROM ".MAIN_DB_PREFIX."bulletin WHERE annee=".$annee." AND mois=".$mois." AND fk_societe=".$id_societe;
            $bulletin_sql .= " ORDER BY rowid";
            $res_bulletin = $db->query($bulletin_sql);
                  if($res_bulletin){
                        $i = 0;
                        $num_all = $db->num_rows($res_bulletin);
                        while ($i < ($num_all)){
                              $cout = 0;
                        //Objet Utilisateur
                              $obj_bulletin = $db->fetch_object($res_bulletin);
                              //Ajout d'une nouvelle ligne
                              $sheet->insertNewRowBefore($numero_ligne+2, $numero_ligne);

                              //Matricule
                              $sheet->setCellValue('A'.$numero_ligne, $obj_bulletin->matricule);

                              $colonne_courante = "A";
                              //Ajout du nom et prénom
                              $nextcolonne = getNextColumnName($colonne_courante);
                              $sheet->setCellValue($nextcolonne.$numero_ligne, $obj_bulletin->prenom);
                              $colonne_courante = $nextcolonne;
                                

                              $nextcolonne = getNextColumnName($colonne_courante);
                              $sheet->setCellValue($nextcolonne.$numero_ligne, $obj_bulletin->nom);
                              $colonne_courante = $nextcolonne;

                              //Date rentrée
                              if(!empty($date_entree)){
                                    $nextcolonne = getNextColumnName($colonne_courante);
                                    $sheet->setCellValue($nextcolonne.$numero_ligne, $obj_bulletin->date_embauche);
                                    $colonne_courante = $nextcolonne;
                              }

                              //Ajout de la catégorie
                              if(!empty($categorie)){
                                    $categ = $obj_bulletin->categorie;
                                    if(!empty($obj_bulletin->echelon) && $obj_bulletin->echelon != 'N/A')
                                          $categ .= '->'.$obj_bulletin->echelon;
                                    $nextcolonne = getNextColumnName($colonne_courante);
                                    $sheet->setCellValue($nextcolonne.$numero_ligne, $categ);
                                    $colonne_courante = $nextcolonne;
                              }


                              //Ajout la situation familiale
                              if(!empty($situation_matrimoniale)){
                                    $stuati_fa = $obj_bulletin->situation_familiale.' '.$obj_bulletin->nombre_enfant.' '.$obj_bulletin->nombre_enfant_hand;
                                    $nextcolonne = getNextColumnName($colonne_courante);
                                    $sheet->setCellValue($nextcolonne.$numero_ligne, $stuati_fa);
                                    $colonne_courante = $nextcolonne;
                              }

                              //Fonction
                              if(!empty($fonction)){
                                    $nextcolonne = getNextColumnName($colonne_courante);
                                    $sheet->setCellValue($nextcolonne.$numero_ligne, $obj_bulletin->fonction);
                                    $colonne_courante = $nextcolonne;
                              }

                              //Banque
                              if(!empty($banque)){
                                    $nextcolonne = getNextColumnName($colonne_courante);
                                    $sheet->setCellValue($nextcolonne.$numero_ligne, $obj_bulletin->banque);
                                    $colonne_courante = $nextcolonne;
                              }

                              //Compte
                              if(!empty($compte)){
                                    $nextcolonne = getNextColumnName($colonne_courante);
                                    $sheet->setCellValue($nextcolonne.$numero_ligne, $obj_bulletin->compte);
                                    $colonne_courante = $nextcolonne;
                              }


                              //Ajout nombre du nombre de jours travaillés
                              if(!empty($nb_jour_tr)){
                                    $salSql = "SELECT jour FROM ".MAIN_DB_PREFIX."salarie_nombre_jour_travaille where annee=".$annee." AND mois=".$mois." AND fk_salarie=".$obj_bulletin->fk_salarie;
                                    $result = $db->query($salSql);
                                    $nb_jours = $db->fetch_object($result)->jour;
                                    $nextcolonne = getNextColumnName($colonne_courante);
                                    $sheet->setCellValue($nextcolonne.$numero_ligne, $nb_jours);
                                    $colonne_courante = $nextcolonne;
                              }

                              //Pourcentage des jours travaillés par montant par mois
                              if(!empty($taux)){
                                    $taux = 100;
                                    $nb_total_jour = cal_days_in_month(CAL_GREGORIAN, $mois, $annee);
                                    if($nb_jours != $nb_total_jour){
                                          $taux = round(($nb_jours * 100)/$nb_total_jour, 2);
                                    }
                                    $nextcolonne = getNextColumnName($colonne_courante);
                                    $sheet->setCellValue($nextcolonne.$numero_ligne, $taux.'%');
                                    $colonne_courante = $nextcolonne;
                              }

                              //Ajout du salaire de base
                              if(!empty($salaire_base)){
                                    $nextcolonne = getNextColumnName($colonne_courante);
                                    $sheet->setCellValue($nextcolonne.$numero_ligne, $obj_bulletin->salaire_base);
                                    $colonne_courante = $nextcolonne;
                              }

                              //Ajout du sursalaire
                              if(!empty($sursalaire)){
                                    $nextcolonne = getNextColumnName($colonne_courante);
                                    $sheet->setCellValue($nextcolonne.$numero_ligne, $obj_bulletin->sursalaire);
                                    $colonne_courante = $nextcolonne;
                              }

                              //Ajout de l'ancienneté
                              if(!empty($anciennete)){
                                    $bulletin_sql_anc = "SELECT taux FROM ".MAIN_DB_PREFIX."bulletin_anciennete WHERE fk_bulletin=".$obj_bulletin->rowid;
                                    $res_bulletin_an = $db->query($bulletin_sql_anc);
                                    $obj_bulletin_an = $db->fetch_object($res_bulletin_an);
                                    $nextcolonne = getNextColumnName($colonne_courante);
                                    $sheet->setCellValue($nextcolonne.$numero_ligne, round(($obj_bulletin->salaire_base*$obj_bulletin_an->taux)/100));
                                    $colonne_courante = $nextcolonne;
                              }


                              //Ajout des primes
                              if(!empty($prime)){
                                    for ($j=0; $j < count($array_id_pr); $j++) { 
                                          $bulletin_pr = "SELECT montant FROM ".MAIN_DB_PREFIX."bulletin_prime WHERE fk_bulletin=".$obj_bulletin->rowid." AND fk_prime=".$array_id_pr[$j];
                                          if($db->num_rows($db->query($bulletin_pr))>0){//Si le salarié a cette prime
                                                $obj_bulletin_pr = $db->fetch_object($db->query($bulletin_pr));
                                                //$sheet->insertNewColumnBefore('I', 9);
                                                $nextcolonne = getNextColumnName($colonne_courante);
                                                $sheet->setCellValue($nextcolonne.$numero_ligne, $obj_bulletin_pr->montant);
                                                $colonne_courante = $nextcolonne;  
                                          }else{
                                                $nextcolonne = getNextColumnName($colonne_courante);
                                                $sheet->setCellValue($nextcolonne.$numero_ligne, 0);
                                                $colonne_courante = $nextcolonne;
                                          }
                                    }
                              }

                              //Ajout des Indemnités
                              //$colonne_courante est déjà définie
                              if(!empty($indemnite)){
                                    for ($j=0; $j < count($array_id_ind); $j++) { 
                                          $bulletin_ind = "SELECT montant FROM ".MAIN_DB_PREFIX."bulletin_indemnite WHERE fk_bulletin=".$obj_bulletin->rowid." AND fk_indemnite=".$array_id_ind[$j];
                                          if($db->num_rows($db->query($bulletin_ind))>0){
                                                $obj_bulletin_ind = $db->fetch_object($db->query($bulletin_ind));
                                                //$sheet->insertNewColumnBefore('I', 9);
                                                $nextcolonne = getNextColumnName($colonne_courante);
                                                $sheet->setCellValue($nextcolonne.$numero_ligne, $obj_bulletin_ind->montant);
                                                $colonne_courante = $nextcolonne;  
                                          }else{
                                                $nextcolonne = getNextColumnName($colonne_courante);
                                                $sheet->setCellValue($nextcolonne.$numero_ligne, 0);
                                                $colonne_courante = $nextcolonne;
                                          }
                                    }
                              }

                              //Heure sup
                              if(!empty($heure_sup)){
                                    $montant_hs = 0;
                                    for ($j=0; $j < count($array_id_hs); $j++) { 
                                          $bulletin_hs = "SELECT montant FROM ".MAIN_DB_PREFIX."bulletin_heure_sup WHERE fk_bulletin=".$obj_bulletin->rowid." AND fk_heur_sup=".$array_id_hs[$j];
                                          $obj_bulletin_hs = $db->fetch_object($db->query($bulletin_hs));
                                          $montant_hs += $obj_bulletin_hs->montant;

                                          if( ($j + 1) == count($array_id_hs)){
                                                //$sheet->insertNewColumnBefore('I', 9);
                                                $nextcolonne = getNextColumnName($colonne_courante);
                                                $sheet->setCellValue($nextcolonne.$numero_ligne, $montant_hs);
                                                $colonne_courante = $nextcolonne;  
                                          }
                                    }
                              }
                                    

                              //Salaire brut
                              if(!empty($salaire_brut)){
                                    $nextcolonne = getNextColumnName($colonne_courante);
                                    $sheet->setCellValue($nextcolonne.$numero_ligne, round($obj_bulletin->salaire_brut));
                                    $colonne_courante = $nextcolonne;

                                    $cout += $obj_bulletin->salaire_brut;
                              }

                              //Salaire brut cotisable
                              if(!empty($salaire_brut_cotisable)){
                                    $nextcolonne = getNextColumnName($colonne_courante);
                                    $sheet->setCellValue($nextcolonne.$numero_ligne, round($obj_bulletin->salaire_brut_cotisable));
                                    $colonne_courante = $nextcolonne;
                              }

                              //Salaire brut imposable
                              if(!empty($salaire_brut_imposable)){
                                    $nextcolonne = getNextColumnName($colonne_courante);
                                    $sheet->setCellValue($nextcolonne.$numero_ligne, round($obj_bulletin->salaire_brut_imposable));
                                    $colonne_courante = $nextcolonne;
                              }

                              //Montant des cotisation salariale
                              if(!empty($inps_employe)){
                                    $bulletin_cotis_sal = "SELECT SUM(montant_employe) as inps_sal FROM ".MAIN_DB_PREFIX."bulletin_cotisation WHERE fk_bulletin=".$obj_bulletin->rowid." AND fk_cotisation<>6";
                                    $obj_bulletin_cotis = $db->fetch_object($db->query($bulletin_cotis_sal));
                                    
                                    $nextcolonne = getNextColumnName($colonne_courante);
                                    $sheet->setCellValue($nextcolonne.$numero_ligne, round($obj_bulletin_cotis->inps_sal));
                                    $colonne_courante = $nextcolonne;
                              }
                              
                              //Montant des cotisation patronale
                              if(!empty($inps_employeur)){
                                    $bulletin_cotis_patr = "SELECT SUM(montant_employeur) as inps_patro FROM ".MAIN_DB_PREFIX."bulletin_cotisation WHERE fk_bulletin=".$obj_bulletin->rowid." AND fk_cotisation<>6";
                                    $obj_bulletin_cotis = $db->fetch_object($db->query($bulletin_cotis_patr));

                                    $nextcolonne = getNextColumnName($colonne_courante);
                                    $sheet->setCellValue($nextcolonne.$numero_ligne, round($obj_bulletin_cotis->inps_patro));
                                    $colonne_courante = $nextcolonne;
                                    $cout += $obj_bulletin_cotis->inps_patro;
                              }

                              //Montant des impôt I.T.S
                              if(!empty($its)){
                                    $bulletin_taxe = "SELECT montant as montant_imp FROM ".MAIN_DB_PREFIX."bulletin_taxe WHERE fk_bulletin=".$obj_bulletin->rowid;
                                    $obj_bulletin_imp = $db->fetch_object($db->query($bulletin_taxe));
                                    $nextcolonne = getNextColumnName($colonne_courante);
                                    $sheet->setCellValue($nextcolonne.$numero_ligne, round($obj_bulletin_imp->montant_imp));
                                    $colonne_courante = $nextcolonne;
                              }

                              //AMO salariale
                              if(!empty($amo_employe)){
                                    $bulletin_cotis_sal = "SELECT SUM(montant_employe) as inps_sal FROM ".MAIN_DB_PREFIX."bulletin_cotisation WHERE fk_bulletin=".$obj_bulletin->rowid." AND fk_cotisation=6";
                                    $obj_bulletin_cotis = $db->fetch_object($db->query($bulletin_cotis_sal));
                                    $nextcolonne = getNextColumnName($colonne_courante);
                                    $sheet->setCellValue($nextcolonne.$numero_ligne, round($obj_bulletin_cotis->inps_sal));
                                    $colonne_courante = $nextcolonne;
                              }

                              //AMO patronale
                              if(!empty($amo_employeur)){
                                    $bulletin_cotis_patr = "SELECT SUM(montant_employeur) as inps_patro FROM ".MAIN_DB_PREFIX."bulletin_cotisation WHERE fk_bulletin=".$obj_bulletin->rowid." AND fk_cotisation=6";
                                    $obj_bulletin_cotis = $db->fetch_object($db->query($bulletin_cotis_patr));
                                    $nextcolonne = getNextColumnName($colonne_courante);
                                    $sheet->setCellValue($nextcolonne.$numero_ligne, round($obj_bulletin_cotis->inps_patro));
                                    $colonne_courante = $nextcolonne;
                                    $cout += $obj_bulletin_cotis->inps_patro;
                              }

                              //Base CFE
                              if(!empty($base_cfe)){
                                    $nextcolonne = getNextColumnName($colonne_courante);
                                    $sheet->setCellValue($nextcolonne.$numero_ligne, round($obj_bulletin->salaire_brut));
                                    $colonne_courante = $nextcolonne;
                              }

                              //Montant CFE
                              if(!empty($montant_cfe)){
                                    $bulletin_cfe = "SELECT montant_employeur as montant_cfe FROM ".MAIN_DB_PREFIX."bulletin_taxe2 WHERE taux_employeur=3.5 AND fk_bulletin=".$obj_bulletin->rowid;
                                    $obj_bulletin_cfe = $db->fetch_object($db->query($bulletin_cfe));
                                    $nextcolonne = getNextColumnName($colonne_courante);
                                    $sheet->setCellValue($nextcolonne.$numero_ligne, round($obj_bulletin_cfe->montant_cfe));
                                    $colonne_courante = $nextcolonne;

                                    $cout += $obj_bulletin_cfe->montant_cfe;
                              }

                              //Base TL
                              if(!empty($base_tl)){
                                    $nextcolonne = getNextColumnName($colonne_courante);
                                    $sheet->setCellValue($nextcolonne.$numero_ligne, round($obj_bulletin->salaire_brut));
                                    $colonne_courante = $nextcolonne;
                              }

                              //Montant TL
                              if(!empty($montant_tl)){
                                    $bulletin_tl = "SELECT montant_employeur as montant_tl FROM ".MAIN_DB_PREFIX."bulletin_taxe2 WHERE taux_employeur=1 AND fk_bulletin=".$obj_bulletin->rowid;
                                    $obj_bulletin_tl = $db->fetch_object($db->query($bulletin_tl));
                                    $nextcolonne = getNextColumnName($colonne_courante);
                                    $sheet->setCellValue($nextcolonne.$numero_ligne, round($obj_bulletin_tl->montant_tl));
                                    $colonne_courante = $nextcolonne;

                                    $cout += $obj_bulletin_tl->montant_tl;
                              }

                              //Salaire Net
                              if(!empty($net_payer)){
                                    $nextcolonne = getNextColumnName($colonne_courante);
                                    $sheet->setCellValue($nextcolonne.$numero_ligne, round($obj_bulletin->net_payer));
                                    $colonne_courante = $nextcolonne;
                              }
                              
                              //Valeurs des avance
                              if(!empty($avance)){
                                    $total_av = 0;
                                    for ($j=0; $j < count($array_libelle_av); $j++) { 
                                          $bulletin_av_sql = "SELECT montant FROM ".MAIN_DB_PREFIX."bulletin_avance WHERE fk_bulletin=".$obj_bulletin->rowid." AND libelle='".$array_libelle_av[$j]."'";
                                          $res_avance_bul = $db->query($bulletin_av_sql);
                                          $montant = 0;
                                          $a = 0;
                                          $nb_av = $db->num_rows($res_avance_bul);
                                          while ($a < $nb_av) {
                                                $obj_bulletin_av = $db->fetch_object($res_avance_bul);
                                                $montant += $obj_bulletin_av->montant;
                                                $total_av += $montant;
                                                $a ++;
                                          }

                                          $nextcolonne = getNextColumnName($colonne_courante);
                                          $sheet->setCellValue($nextcolonne.$numero_ligne, $montant);
                                          $colonne_courante = $nextcolonne;
                                    }
                              }

                              //Total Avance
                              if(!empty($avance)){                              
                                    $nextcolonne = getNextColumnName($colonne_courante);
                                    $sheet->setCellValue($nextcolonne.$numero_ligne, round($total_av));
                                    $colonne_courante = $nextcolonne;
                              }

                              //Net à payer
                              if(!empty($net_payer)){
                                    $nextcolonne = getNextColumnName($colonne_courante);
                                    $sheet->setCellValue($nextcolonne.$numero_ligne, round($obj_bulletin->net_payer - $total_av));
                                    $colonne_courante = $nextcolonne;
                              }

                              //Cout total
                              if(!empty($cout)){
                                    $nextcolonne = getNextColumnName($colonne_courante);
                                    $sheet->setCellValue($nextcolonne.$numero_ligne, round($cout));
                                    $colonne_courante = $nextcolonne;
                              }

                              $numero_ligne ++;
                              $i ++;
                        }
                  }
}else{
      $sheet->mergeCells('A7:H8');
      $sheet->setCellValue('A7', "Aucune information Disponible");

}

//Enregistrement dans les log || Traçabilité
$sql_select = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$user->id;
$obj = $db->fetch_object($db->query($sql_select));

//On garde la trace de l'action
$action_effectue = "Exportation des états de salaire de la société ".$nom_soc." du mois de ".$mois_tab[$mois - 1]." ".$annee." dans société ImportExport";
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