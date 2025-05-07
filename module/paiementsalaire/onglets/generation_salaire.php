<?php
require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/custom/paiementsalaire/lib/paiementsalaire.lib.php';
// lecture des information sur la société
/*$info = lire_ligne_fichier(1);
$id_societe = $info[0];
$id_convention = $info[1];
   $id_accord_etab = $info[2];
   $mois = $info[3];
   $annee = $info[4];
   $nb_sal_licence = $info[5];
*/
   //--------------------------------------------------
 
 	$id_convention = $_GET['id_convention'] ?: 0;
    $id_accord_etab = $_GET['id_accord'] ?: 0;
    $id_societe = $_GET['id_societe'] ?: 0;
    $mois = $_GET['mois'] ?: 0;
    $annee = $_GET['annee'] ?: 0;
	$annee = $_GET['annee'] ?: 0;
	$nb_sal_licence = $_GET['nb_sal_licence'];

	$soc_sql = "SELECT * FROM ".MAIN_DB_PREFIX."societe WHERE rowid=".$id_societe;
	$soc_res = $db->query($soc_sql);//= $db->query($covSql);
	$obj_soc = $db->fetch_object($soc_res);
	global $obj_soc;

	//Lecture des id des salariés
	$tab_id = lire_ligne_fichier(2);
	$i = 0;

	$sql_verif = "SELECT rowid FROM ".MAIN_DB_PREFIX."bulletin WHERE annee=".$annee." AND mois=".$mois." AND fk_societe=".$id_societe;
							$res_verif = $db->query($sql_verif);
							if($res_verif){
                                
								$d = 0;
								$dnum = $db->num_rows($res_verif);
								while ($d < $dnum) {
                                    
									$obj_verif = $db->fetch_object($res_verif);

									//suppression
									$sql_del = "DELETE FROM ".MAIN_DB_PREFIX."bulletin_prime WHERE fk_bulletin=".$obj_verif->rowid;
									$res_del = $db->query($sql_del);

									$sql_del = "DELETE FROM ".MAIN_DB_PREFIX."bulletin_prime_exceptionnelle WHERE fk_bulletin=".$obj_verif->rowid;
									$res_del = $db->query($sql_del);

									$sql_del = "DELETE FROM ".MAIN_DB_PREFIX."bulletin_indemnite WHERE fk_bulletin=".$obj_verif->rowid;
									$res_del = $db->query($sql_del);

									$sql_del = "DELETE FROM ".MAIN_DB_PREFIX."bulletin_taxe WHERE fk_bulletin=".$obj_verif->rowid;
									$res_del = $db->query($sql_del);

									$sql_del = "DELETE FROM ".MAIN_DB_PREFIX."bulletin_taxe2 WHERE fk_bulletin=".$obj_verif->rowid;
									$res_del = $db->query($sql_del);

									$sql_del = "DELETE FROM ".MAIN_DB_PREFIX."bulletin_cotisation WHERE fk_bulletin=".$obj_verif->rowid;
									$res_del = $db->query($sql_del);

									$sql_del = "DELETE FROM ".MAIN_DB_PREFIX."bulletin_anciennete WHERE fk_bulletin=".$obj_verif->rowid;
									$res_del = $db->query($sql_del);

									$sql_del = "DELETE FROM ".MAIN_DB_PREFIX."bulletin_organisme WHERE fk_bulletin=".$obj_verif->rowid;
									$res_del = $db->query($sql_del);

									$sql_del = "DELETE FROM ".MAIN_DB_PREFIX."bulletin_heure_sup WHERE fk_bulletin=".$obj_verif->rowid;
									$res_del = $db->query($sql_del);

									$sql_del = "DELETE FROM ".MAIN_DB_PREFIX."bulletin_avance WHERE fk_bulletin=".$obj_verif->rowid;
									$res_del = $db->query($sql_del);

									$sql_del = "DELETE FROM ".MAIN_DB_PREFIX."bulletin WHERE rowid=".$obj_verif->rowid;
									$res_del = $db->query($sql_del);


									$d ++;
								}
							}

while ($i < count($tab_id)) {
    generateSalary($tab_id[$i], $id_societe, $id_convention, $id_accord_etab, $mois, $annee);

    update_file($tab_id[$i]."/");
    usleep(50000); // Simuler un traitement lourd

    $i++;
}

//$message = "Les salaires des Employés du mois".$mois_tab[$mois-1]." sont générés avec succès";


function lire_ligne_fichier($ligne){
	$tab_id = array();
	$filename = "tmp_sal.txt";
	if(file_exists($filename)){
		$handle = fopen($filename, "r");
	
		if ($handle) {
			$i = 1;
			$val = "";
			while (($line = fgets($handle)) !== false) {
				if($i == $ligne)
					$val = $line;
	
				$i++;
			}
			fclose($handle);
		} else {
			echo "Impossible d'ouvrir le fichier.";
		}
	}

	if(!empty($val))
		$tab_id = explode('/', $val);
	
	return $tab_id;
}


function update_file($a_remplacer){
    $filename = "tmp_sal.txt";
    // Vérifiez si le fichier existe
    if (file_exists($filename)) {
        $i = 1;
        // Lire le fichier ligne par ligne dans un tableau
        $lines = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if ($lines === false) {
            echo "Erreur lors de la lecture du fichier.";
        } else {
            // Parcourir les lignes et remplacer celle correspondante
            foreach ($lines as $index => $line) {
                if ($i == 2) { // Comparaison exacte
                    $nouvelle_ligne = str_replace($a_remplacer, "", $line);
                    $lines[$index] = $nouvelle_ligne;
                    break; // Arrêter après la première correspondance
                }

                $i ++;
            }

            // Réécrire le fichier avec les lignes modifiées
            if (file_put_contents($filename, implode(PHP_EOL, $lines) . PHP_EOL) === false) {
                echo "Erreur lors de l'écriture dans le fichier.";
            }
        }
    } else {
        echo "Le fichier n'existe pas.";
    }

}
// Fonction pour générer la paie d'un salarié (simplifié)
function generateSalary($id_salarie, $id_societe, $id_convention, $id_accord_etab, $mois, $annee) {
global $db, $obj_soc;
    // Logique de génération de paie (par exemple, calcul et insertion dans une autre table)
    $sql = "SELECT sal.rowid, sal.fk_user, sal.fk_categorie, sal.fk_echelon, sal.sursalaire, sal.matricule, sal.inps, sal.amo, sal.fk_type_banque, sal.compte,
				sal.situation_familiale,sal.nombre_enfant, sal.nombre_enfant_hand, sal.fk_user, sal.fk_categorie, sal.fk_echelon, sal.type_salarie, sal.fk_diplome, sal.calcul_salaire, sal.date_anciennete,
				u.rowid as id_user, u.lastname, u.firstname, u.gender, u.fk_country, u.town, u.address, u.personal_mobile, u.office_fax, u.user_mobile, u.email, u.dateemployment, u.job FROM ".MAIN_DB_PREFIX."salarie as sal";
				$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."user as u ON sal.fk_user=u.rowid";
				$sql .= " WHERE sal.rowid=".$id_salarie;

				//$sql = "SELECT u.rowid, u., u.firstname, u.dateemployment, ue.fk_object, ue.egp FROM ".MAIN_DB_PREFIX."user as u";
				//$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."user_extrafields as ue ON u.rowid=ue.fk_object Where ue.egp=".$id_societe;
				$result_global = $db->query($sql);
				$obj_salarie = $db->fetch_object($result_global);
                        
									//Categorie du salarié et son salaire de base
									$salaire_base = 0;
									$grilleSql = "SELECT rowid FROM ".MAIN_DB_PREFIX."grille WHERE active=1 AND fk_convention=".$id_convention;
									$grilleResult = $db->query($grilleSql);//= $db->query($grilleSql);
									$obj_grille = $db->fetch_object($grilleResult);

									$salBaseSql = "SELECT salaire_base FROM ".MAIN_DB_PREFIX."grille_categorie_echelon_salaire_base WHERE fk_grille=".$obj_grille->rowid." AND fk_categorie=".$obj_salarie->fk_categorie." AND fk_echelon=".$obj_salarie->fk_echelon;
									$salBaseResult = $db->query($salBaseSql);//= $db->query($covSql);
									$objSalBase = $db->fetch_object($salBaseResult);
									$salaire_base = $objSalBase->salaire_base;

									$retrait = 0;
									$tab_info_ind = salarie_indemnite($db, $obj_salarie->rowid, $salaire_base, $id_convention, $id_societe, $id_accord_etab);
									$pourcentage_ind = $tab_info_ind[0];
									$ind_array = $tab_info_ind[1];
									foreach ($ind_array as $key => $value) {
									if(!empty($key) && !empty($value)){
										//$somme += $value;
										$sql = "SELECT * FROM ".MAIN_DB_PREFIX."indemnite WHERE rowid=".$key;
										$ind_res = $db->query($sql);
										if($ind_res){
											$ind = $db->fetch_object($ind_res);
											if($ind->exonere == "oui")//retiré du salaire de base
												$retrait += $value;

											//print "<br> Nom = ".$ind->libelle." afficher sur bulletin=".$ind->affiche_bulletin."=>".$value;
										}

									}
									}

									$tab_info_pr = salarie_prime($db, $obj_salarie->rowid, $salaire_base, $id_convention, $id_societe, $id_accord_etab);
									$pourcentage_ind = $tab_info_pr[0];
									$ind_array = $tab_info_pr[1];
									foreach ($ind_array as $key => $value) {
									if(!empty($key) && !empty($value)){
										//$somme += $value;
										$sql = "SELECT * FROM ".MAIN_DB_PREFIX."primes WHERE rowid=".$key;
										$pr_res = $db->query($sql);
										if($pr_res){
											$pr = $db->fetch_object($pr_res);
											if($pr->exonere == "oui")//retiré du salaire de base
												$retrait += $value;

											//print "<br> Nom = ".$pr->libelle." afficher sur bulletin=".$pr->affiche_bulletin."=>".$value;
										}

									}
									}

									//print $salaire_base.'**';
									$salaire_base -= $retrait;
									//print $salaire_base.'<br>';

									$anciennete_tab = prime_anciennete($db, $obj_salarie->rowid, $id_convention, $mois, $annee, $obj_salarie->fk_user);
									$anciennete = $salaire_base*$anciennete_tab[1]/100;
									if($anciennete_tab[5] == "Oui")
										$salaire_base -= $anciennete;

//Proratisation
									$salSql = "SELECT jour FROM ".MAIN_DB_PREFIX."salarie_nombre_jour_travaille where annee=".$annee." AND mois=".$mois." AND fk_salarie=".$obj_salarie->rowid;
									$result = $db->query($salSql);
									$nb_jours = $db->fetch_object($result)->jour;
									$nb_total_jour = cal_days_in_month(CAL_GREGORIAN, $mois, $annee);
									$base_pourcentage = 1;
									if($nb_jours != $nb_total_jour){
										$sal_base = ($nb_jours*$salaire_base)/$nb_total_jour;
										$base_pourcentage = ($sal_base*100)/$salaire_base;
										$base_pourcentage = $base_pourcentage/100;
										$salaire_base = $salaire_base*$base_pourcentage;
									}

									//---------------------------------------------------------------------
									//sursalaire
									$sursalaire = $obj_salarie->sursalaire*$base_pourcentage;


									//les salaires : salaire brut, salaire brut imposable et salaire brut cotisable
									$salaire_brut_imposable = $salaire_base +$sursalaire;
									$salaire_brut_cotisable = $salaire_base +$sursalaire;
									$salaire_brut = $salaire_base +$sursalaire;

									$salaire_net = 0;
									$retenu_prest_empl = 0;
									$retenu_prest_patro = 0;
									$retenu_taxe = 0;
									$retenu = 0;
									$inps = 0;

									$array_prime = array();
									$array_prime_exceptionnelle = array();
									$array_indemnite = array();
									$array_taxe = array();
									$array_prestation = array();
									$array_heure_sup = array();
									$array_pr_ind_hs = array();//Cette table contient les primes et indemnités à ajouter à la base heure sup

									//if($anciennete_tab[3] == "Oui")//exonere ou non
									//prime d'anciennété
									$salaire_brut += $anciennete;

									if($anciennete_tab[3] == "Oui")
										$salaire_brut_cotisable += $anciennete;

									if($anciennete_tab[4] == "Oui")
										$salaire_brut_imposable += $anciennete;

									//les primes qui doivent être affichés sur le billetin
									$tab_info_pr = salarie_prime($db, $obj_salarie->rowid, $salaire_base, $id_convention, $id_societe, $id_accord_etab);
									$pourcentage_pr = $tab_info_pr[0];
									$pr_array = $tab_info_pr[1];
									$index = 0;
									$m = "";
									foreach ($pr_array as $key => $value) {
									if(!empty($key) && !empty($value)){
										//$somme += $value;
										$sql = "SELECT libelle, ajout_base_hs, soumis_cotisation, soumis_impot, affiche_bulletin FROM ".MAIN_DB_PREFIX."primes WHERE rowid=".$key;
										$prime_res = $db->query($sql);
										if($prime_res){
											$pr = $db->fetch_object($prime_res);
											$value = $value*$base_pourcentage;
											$array_prime[$index][0] = $key;
											$array_prime[$index][1] = $pr->affiche_bulletin;
											$array_prime[$index][2] = $value;
											$array_prime[$index][3] = ($pourcentage_pr[$index]?:0)*$base_pourcentage;
											$array_prime[$index][4] = $pr->libelle;

												$salaire_brut += $value;
												if($pr->soumis_cotisation=="Oui")
													$salaire_brut_cotisable += $value;

												if($pr->soumis_impot=="Oui")
												$salaire_brut_imposable += $value;

												if($pr->ajout_base_hs == "Oui"){
													$array_pr_ind_hs[] = $value;
													//$m .= $pr->libelle.'='.$value;
												}


											$index ++;
											//print "<br> Nom = ".$pr->libelle." afficher sur bulletin=".$pr->affiche_bulletin."=>".$value."--".$obj_salarie->lastname;
										}
									}
									}
									

									//les primes flottantes de montant variable
									$pr_fl = prime_flottante($db, $obj_salarie->rowid);
									foreach ($pr_fl as $key => $value) {
										if(!empty($key) && !empty($value)){
											$sql = "SELECT libelle, ajout_base_hs, ajout_base_hs, soumis_cotisation, soumis_impot, affiche_bulletin FROM ".MAIN_DB_PREFIX."primes WHERE rowid=".$key;
											$prime_res = $db->query($sql);
											if($prime_res){
												$pr = $db->fetch_object($prime_res);

												$val = $value;
											$pourc = 100;

												if(count(explode('%',$value."v")) > 1)
													$val = ($salaire_base*explode('%',$value)[0])/100;
												if($val != $value)
													$pourc = explode('%',$value)[0];
												$array_prime[$index][0] = $key;
												$array_prime[$index][1] = $pr->affiche_bulletin;
												$array_prime[$index][2] = $val*$base_pourcentage;
												$array_prime[$index][3] = $pourc*$base_pourcentage;
												$array_prime[$index][4] = $pr->libelle;

												$index ++;

												$val = $value;
												if(count(explode('%',$value."v")) > 1)
													$val = ($salaire_base*explode('%',$value)[0])/100;
												
												$salaire_brut += $val*$base_pourcentage;
												if($pr->soumis_cotisation=="Oui")
													$salaire_brut_cotisable += $val*$base_pourcentage;

												if($pr->soumis_impot=="Oui")
													$salaire_brut_imposable += $val*$base_pourcentage;

												if($pr->ajout_base_hs == "Oui"){
													$array_pr_ind_hs[] = $val*$base_pourcentage;
													//$m .= $pr->libelle.'='.$value;

												}

											}
										}
									}
									

									//primes exceptionnelles associable aux salariés :  elles ne sont pas proratisées.
									$array_prime_exceptionnelle = salarie_prime_exceptionnelle($db, $obj_salarie->rowid, $mois, $annee);
										for ($e=0; $e < count($array_prime_exceptionnelle); $e++) {
											$salaire_brut += $array_prime_exceptionnelle[$e][1];

											//Si la prime est soumise au impôt
											if($array_prime_exceptionnelle[$e][5] == 'Oui'){
												$salaire_brut_imposable += $array_prime_exceptionnelle[$e][1];
											  }

											  //Si la prime est soumise à la cotisation
											  if($array_prime_exceptionnelle[$e][6] == 'Oui'){
												$salaire_brut_cotisable += $array_prime_exceptionnelle[$e][1];

											  }
										}

									//les indemnités qui doivent être affichés sur le billetin
									$index = 0;
									$tab_info_ind = salarie_indemnite($db, $obj_salarie->rowid, $salaire_base,  $id_convention, $id_societe, $id_accord_etab);
									$pourcentage_ind = $tab_info_ind[0];
									$ind_array = $tab_info_ind[1];
									foreach ($ind_array as $key => $value) {
									if(!empty($key) && !empty($value)){
										//$somme += $value;
										$sql = "SELECT libelle, ajout_base_hs, affiche_bulletin, soumis_cotisation, soumis_impot, porcentage_soumis_impot, porcentage_soumis_cotis FROM ".MAIN_DB_PREFIX."indemnite WHERE rowid=".$key;
										$ind_res = $db->query($sql);
										if($ind_res){
											$ind = $db->fetch_object($ind_res);
											$array_indemnite[$index][0] = $key;
											$array_indemnite[$index][1] = $ind->affiche_bulletin;
											$array_indemnite[$index][2] = $value*$base_pourcentage;
											$array_indemnite[$index][3] = ($pourcentage_ind[$index]?:0)*$base_pourcentage;
											$array_indemnite[$index][4] = $ind->libelle;

											//retiré du salaire de base
												$salaire_brut += $value*$base_pourcentage;
												if($ind->soumis_cotisation=="Oui"){//les indemnités soumisent aux cotisations
													if(!empty($ind->porcentage_soumis_cotis))
														$salaire_brut_cotisable += ($value*$base_pourcentage*$ind->porcentage_soumis_cotis)/100;
												}
												if($ind->soumis_impot=="Oui")////les indemnités soumisent aux impôt
													if(!empty($ind->porcentage_soumis_impot))
														$salaire_brut_imposable += ($value*$base_pourcentage*$ind->porcentage_soumis_impot)/100;
												
												if($ind->ajout_base_hs == "Oui"){
													$array_pr_ind_hs[] = $value*$base_pourcentage;
													//$m .= $ind->libelle.'='.$value;

												}
												
											$index ++;
										}

									}
									}
	//les indemnités flottantes de montant variable
									$ind_array = indemnite_flottante($db, $obj_salarie->rowid);
									foreach ($ind_array as $key => $value) {
									if(!empty($key) && !empty($value)){
										$sql = "SELECT libelle, affiche_bulletin, soumis_cotisation, soumis_impot, porcentage_soumis_impot, porcentage_soumis_cotis FROM ".MAIN_DB_PREFIX."indemnite WHERE rowid=".$key;
										$ind_res = $db->query($sql);
										if($ind_res){
											$ind = $db->fetch_object($ind_res);
											$val = $value;
											$pourc = 100;

											if(count(explode('%',$value."v")) > 1)
												$val = ($objSalBase->salaire_base*explode('%',$value)[0])/100;
											if($val != $value)
												$pourc = explode('%',$value)[0];
											$array_indemnite[$index][0] = $key;
											$array_indemnite[$index][1] = $ind->affiche_bulletin;
											$array_indemnite[$index][2] = $val*$base_pourcentage;
											$array_indemnite[$index][3] = $pourc*$base_pourcentage;
											$array_indemnite[$index][4] = $ind->libelle;



											if($ind->soumis_cotisation=="Oui"){//les indemnités soumisent aux cotisations
												if(!empty($ind->porcentage_soumis_cotis))
													$salaire_brut_cotisable += ($val*$base_pourcentage*$ind->porcentage_soumis_cotis)/100;
											}
											if($ind->soumis_impot=="Oui")////les indemnités soumisent aux impôt
												if(!empty($ind->porcentage_soumis_impot))
													$salaire_brut_imposable += ($val*$base_pourcentage*$ind->porcentage_soumis_impot)/100;

											if($ind->ajout_base_hs == "Oui"){
												$array_pr_ind_hs[] = $val*$base_pourcentage;
												////$m .= $ind->libelle.'='.$value;

											}

											$salaire_brut += $val*$base_pourcentage;
											$index ++;
										}
									}
									}
									//$file = fopen("hs.txt", "w"); // "w" = écrire et écraser le contenu existant
									//fwrite($file, $m."-\n"); //ligne 1 = information de la société plus la date

									//Heures Supplémentaires
									$base = ($salaire_base + $retrait)/173.33; //base des heures sup
									$salSql = "SELECT * FROM ".MAIN_DB_PREFIX."societe_prime_heure_sup where fk_societe=".$id_societe;
									$result = $db->query($salSql);
									if($result){ //Verification d'une ou plusieur prime d'heure sup
										$obj = $db->fetch_object($result);
										if($obj->salaire_base == 'oui'){
											$base += $salaire_base/173.33;
										}

										if($obj->sursalaire == 'oui'){
											$base += $sursalaire/173.33;
										}

										if($obj->anciennete == 'oui'){
											$base += $anciennete/173.33;
										}

										if($obj->montant && $obj->montant != 0){
											$base = $obj->montant;
										}

									}

								$valeur_heur_sup = 0;
								$index = 0;
					//Recupération des heures sup liés à ID du salarié
								$tableau = salarie_heure_sup($db, $obj_salarie->rowid, $mois, $annee);
								$array_rowid_hs = $tableau[0];
								$array_hs_taux = $tableau[1];
								$array_nb_hs = $tableau[2];
								$trouve = false;


								$somme_pr_ind_hs = 0;
								//recuperations des montant primes et indemnités à ajouter à la base heure sup
								for ($m=0; $m < count($array_pr_ind_hs); $m++) { 
									$somme_pr_ind_hs += $array_pr_ind_hs[$m];
								}

								
						



								//Verifions si le salarié à une configuration particulière pour les heure sup
								$sql_spec = "SELECT taux, base FROM ".MAIN_DB_PREFIX."salarie_config_heure_sup WHERE fk_salarie=".$obj_salarie->rowid;
								$res_spec = $db->query($sql_spec);
								$specail_base = 0;
								$special_taux = 0;
								if($db->num_rows($res_spec)){
									$trouve = true;
									$obj_spec = $db->fetch_object($res_spec);
									$special_taux = $obj_spec->taux;

									$specail_base = $obj_spec->base;
								}
								
								$base += $somme_pr_ind_hs/173.33;

								//print $obj_salarie->firstname."***".$specail_base.'***'.$special_taux.'<br>';

								for($index=0; $index< count($array_hs_taux); $index++){
									//$taux est le taux d'heure sup
									//$nb_heure_sup est le nombre d'heure sup effectuée
									$taux = $array_hs_taux[$index];
									$nb_heure_sup = $array_nb_hs[$index];
									$sql_hs = "SELECT commentaire FROM ".MAIN_DB_PREFIX."heure_sup WHERE rowid=".$array_rowid_hs[$index];
									$result_sql_hs = $db->query($sql_hs);
									$obj_sql_hs = $db->fetch_object($result_sql_hs);
									$ma_base = $base + $base*$taux/100;
									if($trouve){
										$ma_base = $specail_base;
										$taux = $special_taux;
									}
									$array_heure_sup[$index][0] = $obj_sql_hs->commentaire;
									$array_heure_sup[$index][1] = $ma_base;
									$array_heure_sup[$index][2] = $taux;
									$array_heure_sup[$index][3] = $nb_heure_sup;
									$array_heure_sup[$index][4] = $ma_base*$nb_heure_sup;
									$array_heure_sup[$index][5] = $array_rowid_hs[$index];
									$valeur_heur_sup += $ma_base*$nb_heure_sup;

								}


								$salaire_brut += $valeur_heur_sup;
								$salaire_brut_cotisable += $valeur_heur_sup;
								$salaire_brut_imposable += $valeur_heur_sup;


								//Les cotisation sociales ou prestations sociales
								//les cotisation à afficher par organisme
								$old_fk_orga = 0;
								$nom_organisme = array();
								$id_organisme = array();
								$montant_org_sal = array();
								$montant_org_patro = array();
								$pourcentage_org = array();

								$index = 0;
								$global_cotis = salarie_prestation_organisme($db, $obj_salarie->rowid, $id_convention, $id_societe);
								$cotis = $global_cotis[1];
								$taux_p = $global_cotis[0];
								foreach ($cotis as $key => $value) {
									$type_prest = "SELECT * FROM ".MAIN_DB_PREFIX."type_prestation WHERE rowid=".$key;
									$result_type_prest = $db->query($type_prest);
									$obj_prest_type = $db->fetch_object($result_type_prest);

									if($obj_prest_type->fk_organisme != $old_fk_orga){
										$old_fk_orga = $obj_prest_type->fk_organisme;
										$organisme = "SELECT rowid, nom_organisme FROM ".MAIN_DB_PREFIX."organisme WHERE rowid=".$old_fk_orga;
										$result_organisme = $db->query($organisme);
										$id_organisme[] = $old_fk_orga;
										$obj_organisme = $db->fetch_object($result_organisme);
										$nom_organisme[] = $obj_organisme->nom_organisme;
										$montant_org_sal[] = $value*$salaire_brut_cotisable/100;
										$montant_org_patro[] = $taux_p[$index]*$salaire_brut_cotisable/100;
										$pourcentage_org[] = $value;

										$retenu_prest_empl += $value*$salaire_brut_cotisable/100;
										$retenu_prest_patro += $taux_p[$index]*$salaire_brut_cotisable/100;
									}else{
										$retenu_prest_empl += $value*$salaire_brut_cotisable/100;
										$retenu_prest_patro += $taux_p[$index]*$salaire_brut_cotisable/100;

										$montant_org_sal[(count($montant_org_sal) - 1)] += $value*$salaire_brut_cotisable/100;
										$montant_org_patro[(count($montant_org_patro) - 1)] += $taux_p[$index]*$salaire_brut_cotisable/100;
										$pourcentage_org[count($pourcentage_org)-1] += $value;
									}

									if($obj_prest_type->rowid != 6)
										$inps += $value*$salaire_brut_cotisable/100;

									$index ++;
								}

									//les prestations à afficher sur le bulletin
									$index = 0;
										$global_cotis = salarie_prestation($db, $obj_salarie->rowid, $id_convention, $id_societe);
										$cotis = $global_cotis[1];
										$taux_p = $global_cotis[0];
										foreach ($cotis as $key => $value) {
											$type_prest = "SELECT rowid, fk_organisme, code, affiche_bulletin FROM ".MAIN_DB_PREFIX."type_prestation WHERE rowid=".$key;
												$result_type_prest = $db->query($type_prest);
												$obj_prest_type = $db->fetch_object($result_type_prest);

												$array_prestation[$index][0] = $key;
												$array_prestation[$index][1] = $obj_prest_type->affiche_bulletin;
												$array_prestation[$index][2] = $value*$salaire_brut_cotisable/100;
												$array_prestation[$index][3] = $taux_p[$index]*$salaire_brut_cotisable/100;
												$array_prestation[$index][4] = $value;
												$array_prestation[$index][5] = $taux_p[$index];
												$array_prestation[$index][6] = $obj_prest_type->code;

												$index ++;
												if(!in_array($obj_prest_type->fk_organisme, $id_organisme)){
													$retenu_prest_empl += $value*$salaire_brut_cotisable/100;
													$retenu_prest_patro += $taux_p[$index]*$salaire_brut_cotisable/100;
													if($obj_prest_type->rowid != 6)
														$inps += $value*$salaire_brut_cotisable/100;
												}
										}


									//les taxes qui ont comme barème : barème cotisation
										$index = 0;
										$global_taxe = salarie_taxe2($db, $obj_salarie->rowid, $id_convention);
										$taxe = $global_taxe[1];
										$taux_t = $global_taxe[0];
										foreach ($taxe as $key => $value) {
											$type_taxe = "SELECT rowid, libelle, fk_organisme, affiche_bulletin FROM ".MAIN_DB_PREFIX."type_taxe WHERE rowid=".$key;
												$result_type_taxe = $db->query($type_taxe);
												$obj_taxe_type = $db->fetch_object($result_type_taxe);

												$array_taxe[$index][0] = $key;
												$array_taxe[$index][1] = $obj_taxe_type->affiche_bulletin;
												$array_taxe[$index][2] = $value*$salaire_brut/100;
												$array_taxe[$index][3] = $taux_t[$index]*$salaire_brut/100;
												$array_taxe[$index][4] = $value;
												$array_taxe[$index][5] = $taux_t[$index];
												$array_taxe[$index][6] = $obj_taxe_type->libelle;
												$index ++;
										}

										//A par amo les autres detail de l'INPS ne sont pas soumis aux impôt
										$salaire_brut_imposable -= $inps;
										//tratement de l'its
										$its = its_salarie($db, $obj_salarie->rowid, $salaire_brut_imposable);
										$retenu_taxe = $its[2];

										$retenu = $retenu_prest_empl + $retenu_taxe;
										//calcul du salaire net
										$salaire_net = $salaire_brut - $retenu_prest_empl - $retenu_taxe;

									$index = 0;
									$array_avance = array();
									$somme_avance = 0;
									$avance = salarie_avance_acompte_avec_save($db,$obj_salarie->rowid, $mois, $annee);
										foreach ($avance as $key => $value) {
											$sql_avance = "SELECT libelle FROM ".MAIN_DB_PREFIX."salarie_avance WHERE rowid=".$key;
												$result_avance = $db->query($sql_avance);
												$obj_avance = $db->fetch_object($result_avance);

												$array_avance[$index][0] = $key;
												$array_avance[$index][1] = $obj_avance->libelle;
												$array_avance[$index][2] = $value;
												$somme_avance += $value;
												$index ++;
										}

									//net à payer
									$net_payer = $salaire_net;


						//if($suppression == false){
							$suppression = true;
						//}

							//Recherche des informations afin de les enregistrer dans le bulletin
							$categorie_Sql = "SELECT code_categorie FROM ".MAIN_DB_PREFIX."dcategories WHERE rowid=".$obj_salarie->fk_categorie;
							$categorie_Result = $db->query($categorie_Sql);
							$categorie_Salarie = $db->fetch_object($categorie_Result);
							$categ = $categorie_Salarie->code_categorie?:"N/A";

							$echelon = "";
							if($obj_salarie->fk_echelon !=0){
								$echelon_Sql = "SELECT libelle FROM ".MAIN_DB_PREFIX."echelon where rowid=".($obj_salarie->fk_echelon?:0);
								$echelon_Result = $db->query($echelon_Sql);
								$echelon_Salarie = $db->fetch_object($echelon_Result);
								$echelon = $echelon_Salarie->libelle;
							}

							$type_sal_SQL = "SELECT libelle FROM ".MAIN_DB_PREFIX."type_salarie where rowid=".($obj_salarie->type_salarie?:0);
							$type_sal_Result = $db->query($type_sal_SQL);
							$type_salarie = "N/A";
							if(!empty($db->fetch_object($type_sal_Result)))
								$type_Salarie = $db->fetch_object($type_sal_Result);

							$type_dipl_SQL = "SELECT nom FROM ".MAIN_DB_PREFIX."diplome where rowid=".($obj_salarie->fk_diplome?:0);
							$type_dipl_Result = $db->query($type_dipl_SQL);
							$diplome = "N/A";
							if(!empty($db->fetch_object($type_dipl_Result)))
								$diplome = $db->fetch_object($type_dipl_Result);
								

							$sf = "N/A";
							if($obj_salarie->situation_familiale == "marie")
							$sf = "Marié";
							else if($obj_salarie->situation_familiale == "divorce")
							$sf = "Divorcé";
							else $sf = "Célibataire";

							$sexe = "N/A";
							if($obj_salarie->gender == "man")
							$sexe = "Masculin";
							else if($obj_salarie->gender == "woman")
								$sexe = "Féminin";

							$pays_Sql = "SELECT * FROM ".MAIN_DB_PREFIX."c_country where rowid=".($obj_salarie->fk_country?:0);
							$pays_Result = $db->query($pays_Sql);
							$pays = "N/A";
							if(!empty($obj_user->fk_country))
								$pays = $db->fetch_object($pays_Result)->label;

							$tel = "N/A";
							if(!empty($obj_salarie->user_mobile))
								$tel = $obj_salarie->user_mobile;


							$fax = "N/A";
							if(!empty($obj_salarie->office_fax))
								$fax = $obj_salarie->office_fax;

							$email = "N/A";
							if(!empty($obj_salarie->email))
								$email = $obj_salarie->email;

							$addresse = "N/A";
							if(!empty($obj_salarie->address))
							$addresse = $obj_salarie->address;

							$ville = "N/A";
							if(!empty($obj_salarie->town))
								$ville = $obj_salarie->town;

							$type_bank = "";
							$banque = "SELECT libelle FROM ".MAIN_DB_PREFIX."type_banque WHERE rowid=".($obj_salarie->fk_type_banque?:0);
							$result_banque = $db->query($banque);
							if($result_banque){
								$obj_type_banque = $db->fetch_object($result_banque);
								$type_bank =  $obj_type_banque->libelle;

							}

							if($base_pourcentage != 1){
								$base_pourcentage = round($base_pourcentage,2);
							}

							$date_embauche = ($obj_salarie->dateemployment?:($obj_salarie->date_anciennete?:"N/A"));


							//Récuperation de l'id du contrat pour le salarié
							$sql_contrat2 = "SELECT * FROM ".MAIN_DB_PREFIX."salarie_contrat WHERE fk_salarie=".$obj_salarie->rowid." AND active=1";
							$res_contrat2 = $db->query($sql_contrat2);
										
							//Récuperation du libelle du contrat pour l'id du contrat
							$sql_type_contrat = "SELECT libelle FROM ".MAIN_DB_PREFIX."type_contrat WHERE rowid=".$db->fetch_object($res_contrat2)->fk_type_contrat;
							$restype_contrat = $db->query($sql_type_contrat);
							$contrat = $db->fetch_object($restype_contrat)->libelle;

							$sql_bulletin = 'Insert into '.MAIN_DB_PREFIX.'bulletin (nom, prenom, fk_salarie, matricule, situation_familiale, nombre_enfant, nombre_enfant_hand, calcul_salaire, categorie
							, echelon, contrat, diplome, type_salarie, fonction, date_embauche, sexe, pays, ville, addresse, tel, email, annee, mois, salaire_base, sursalaire, salaire_brut, salaire_brut_cotisable,
							salaire_brut_imposable, net_payer, fk_societe, nom_societe, logo_societe, nom_convention,inps,amo,banque,compte,pourcentage)
							VALUES("'.$obj_salarie->lastname.'","'.$obj_salarie->firstname.'",'.$obj_salarie->rowid.',"'.$obj_salarie->matricule.'","'.$sf.'",'.$obj_salarie->nombre_enfant.','.$obj_salarie->nombre_enfant_hand.',"'.$obj_salarie->calcul_salaire.'",
							"'.$categ.'","'.$echelon.'","'.$contrat.'","'.$diplome.'","'.$type_salarie.'","'.($obj_salarie->job?:"N/A").'","'.($date_embauche).'",
							"'.$sexe.'","'.$pays.'","'.$ville.'","'.$addresse.'","'.($obj_salarie->personal_mobile?:($tel?:$fax)).'","'.$email.'",
							'.$annee.','.$mois.',"'.round($salaire_base, 2).'","'.$sursalaire.'","'.$salaire_brut.'","'.$salaire_brut_cotisable.'","'.$salaire_brut_imposable.'","'.round($net_payer).'",'.$id_societe.',"'.$obj_soc->nom.'","'.$obj_soc->logo.'",
							"'.$obj_conv->nom.'","'.$obj_salarie->inps.'","'.$obj_salarie->amo.'","'.$type_bank.'","'.$obj_salarie->compte.'",'.$base_pourcentage.')';

							$res_bulletin = $db->query($sql_bulletin);
							if(!$res_bulletin){
                                $m = "Une ou les informations de ".$obj_salarie->firstname."  ".$obj_salarie->firstname." sont trop longues";
                                header('Content-Type: application/json');
                                echo json_encode(['effectue' => $m]);
							}
								if($res_bulletin){
									$sql_verif = "SELECT rowid FROM ".MAIN_DB_PREFIX."bulletin WHERE fk_salarie=".$obj_salarie->rowid." AND annee=".$annee." AND mois=".$mois;
									$res_verif = $db->query($sql_verif);
									$obj_last = $db->fetch_object($res_verif);
									$rowid_bulletin = $obj_last->rowid;
									//insertion dans la table bulletin prime
									for ($e=0; $e < count($array_prime); $e++) {
										$fk_prime = $array_prime[$e][0];
										$affiche_bulletin = $array_prime[$e][1];
										$montant = round($array_prime[$e][2], 2);
										$poucentage = round($array_prime[$e][3], 2);
										$libelle = $array_prime[$e][4];
										$sql_bulletin = 'Insert into '.MAIN_DB_PREFIX.'bulletin_prime (fk_bulletin, fk_prime, libelle, montant, pourcentage, affiche_bulletin)';
										$sql_bulletin .= ' VALUES('.$rowid_bulletin.','.$fk_prime.',"'.$libelle.'","'.$montant.'","'.$poucentage.'","'.$affiche_bulletin.'")';
										$res_bulletin = $db->query($sql_bulletin);

									}

									//Insertion dans la table bulletin prime exceptionnelle
									for ($e=0; $e < count($array_prime_exceptionnelle); $e++) {

										$fk_prime = $array_prime_exceptionnelle[$e][0];
										$montant = $array_prime_exceptionnelle[$e][1];
										$affiche_bulletin = $array_prime_exceptionnelle[$e][2];
										$poucentage = $array_prime_exceptionnelle[$e][3];
										$libelle = $array_prime_exceptionnelle[$e][4];

										$sql_bulletin = 'Insert into '.MAIN_DB_PREFIX.'bulletin_prime_exceptionnelle (fk_bulletin, fk_prime, libelle, montant, pourcentage, affiche_bulletin)';
										$sql_bulletin .= ' VALUES('.$rowid_bulletin.','.$fk_prime.',"'.$libelle.'","'.$montant.'","'.$poucentage.'","'.$affiche_bulletin.'")';
										$res_bulletin = $db->query($sql_bulletin);

									}

									//insertion dans la table bulletin indemnite
									for ($f=0; $f < count($array_indemnite); $f++) {
										$fk_indemnite = $array_indemnite[$f][0];
										$affiche_bulletin = $array_indemnite[$f][1];
										$montant = round($array_indemnite[$f][2], 2);
										$poucentage = round($array_indemnite[$f][3], 2);
										$libelle = $array_indemnite[$f][4];
										$sql_bulletin = 'Insert into '.MAIN_DB_PREFIX.'bulletin_indemnite (fk_bulletin, fk_indemnite, libelle, montant, pourcentage, affiche_bulletin)';
										$sql_bulletin .= ' VALUES('.$rowid_bulletin.','.$fk_indemnite.',"'.$libelle.'","'.$montant.'","'.$poucentage.'","'.$affiche_bulletin.'")';
										$res_bulletin = $db->query($sql_bulletin);
									}
									//insertion dans la table bulletin taxe
									if($rowid_bulletin){
										$fk_taxe = 1;
										$montant = $its[2];
										$libelle = $its[3];
										$affiche_bulletin = "Oui";
										$sql_bulletin = 'Insert into '.MAIN_DB_PREFIX.'bulletin_taxe (fk_bulletin, fk_taxe, libelle, taux, montant, affiche_bulletin)';
										$sql_bulletin .= ' VALUES('.$rowid_bulletin.','.$fk_taxe.',"'.$libelle.'","'.round($its[0], 2).'","'.round($montant).'","'.$affiche_bulletin.'")';
										$res_bulletin = $db->query($sql_bulletin);
									}

										//insertion dans la table bulletin cotisations
									for ($g=0; $g < count($array_prestation); $g++) {
										$fk_cotisation = $array_prestation[$g][0];
										$affiche_bulletin = $array_prestation[$g][1];
										$montant_employe = $array_prestation[$g][2]?:0;
										$montant_employeur = $array_prestation[$g][3]?:0;
										$taux_employe = $array_prestation[$g][4]?:0;
										$taux_employeur = $array_prestation[$g][5]?:0;
										$libelle = $array_prestation[$g][6];
										$sql_bulletin = 'Insert into '.MAIN_DB_PREFIX.'bulletin_cotisation (fk_bulletin, fk_cotisation, libelle, taux_employe, taux_employeur, montant_employe, montant_employeur, affiche_bulletin)';
										$sql_bulletin .= ' VALUES('.$rowid_bulletin.','.$fk_cotisation.',"'.$libelle.'","'.$taux_employe.'","'.$taux_employeur.'","'.round($montant_employe).'","'.round($montant_employeur).'","'.$affiche_bulletin.'")';
										$res_bulletin = $db->query($sql_bulletin);
									}

										//insertion dans la table bulletin taxe2
									for ($g=0; $g < count($array_taxe); $g++) {
										$fk_taxe = $array_taxe[$g][0];
										$affiche_bulletin = $array_taxe[$g][1];
										$montant_employe = $array_taxe[$g][2]?:0;
										$montant_employeur = $array_taxe[$g][3]?:0;
										$taux_employe = $array_taxe[$g][4]?:0;
										$taux_employeur = $array_taxe[$g][5]?:0;
										$libelle = $array_taxe[$g][6];
										//print $obj_salarie->lastname."<br> te ".$taux_employe." tE ".$taux_employeur." me ".$montant_employe." mE ".$montant_employeur;
										$sql_bulletin = 'Insert into '.MAIN_DB_PREFIX.'bulletin_taxe2 (fk_bulletin, fk_taxe, libelle, taux_employe, taux_employeur, montant_employe, montant_employeur, affiche_bulletin)';
										$sql_bulletin .= ' VALUES('.$rowid_bulletin.','.$fk_taxe.',"'.$libelle.'","'.$taux_employe.'","'.$taux_employeur.'","'.round($montant_employe).'","'.round($montant_employeur).'","'.$affiche_bulletin.'")';
										$res_bulletin = $db->query($sql_bulletin);
									}

										//insertion dans la table bulletin organisme
									for ($g=0; $g < count($nom_organisme); $g++) {
										$sql_bulletin = 'Insert into '.MAIN_DB_PREFIX.'bulletin_organisme (fk_bulletin, fk_organisme, nom_organisme, pourcentage, montant_employe, montant_employeur)';
										$sql_bulletin .= ' VALUES('.$rowid_bulletin.','.$id_organisme[$g].',"'.$nom_organisme[$g].'","'.$pourcentage_org[$g].'","'.round($montant_org_sal[$g]).'","'.round($montant_org_patro[$g]).'")';
										$res_bulletin = $db->query($sql_bulletin);

									}

									//insertion dans la table bulletin prime d'ancienneté
									if($rowid_bulletin){
										$sql_bulletin = 'Insert into '.MAIN_DB_PREFIX.'bulletin_anciennete (fk_bulletin, fk_prime, libelle, anciennete, taux, affiche_bulletin)';
										$sql_bulletin .= ' VALUES('.$rowid_bulletin.',1,"'.$anciennete_tab[6].'",'.$anciennete_tab[0].',"'.$anciennete_tab[1].'","'.$anciennete_tab[2].'")';
										$res_bulletin = $db->query($sql_bulletin);
									}

									//insertion dans la table bulletin heure sup
									for ($e=0; $e < count($array_heure_sup); $e++) {
										$libelle = $array_heure_sup[$e][0];
										$base = round($array_heure_sup[$e][1],2);
										$taux = $array_heure_sup[$e][2];
										$nb_heure_sup = $array_heure_sup[$e][3];
										$montant = round($array_heure_sup[$e][4],2);
										$fk_hs = $array_heure_sup[$e][5];

										$sql_bulletin = 'Insert into '.MAIN_DB_PREFIX.'bulletin_heure_sup (fk_bulletin,fk_heur_sup,libelle,base,taux,nombre_heure_sup,montant)';
										$sql_bulletin .= ' VALUES('.$rowid_bulletin.','.$fk_hs.',"'.$libelle.'","'.$base.'","'.$taux.'",'.$nb_heure_sup.',"'.$montant.'")';
										$res_bulletin = $db->query($sql_bulletin);
									}

										//insertion dans la table bulletin avance
									if($somme_avance > 0)
										for ($e=0; $e < count($array_avance); $e++) {
											$fk_avance = $array_avance[$e][0];
											$libelle = $array_avance[$e][1];
											$montant = round($array_avance[$e][2],2);

											$sql_bulletin = 'Insert into '.MAIN_DB_PREFIX.'bulletin_avance (fk_bulletin,fk_avance,libelle,montant)';
											$sql_bulletin .= ' VALUES('.$rowid_bulletin.','.$fk_avance.',"'.$libelle.'","'.$montant.'")';
											$res_bulletin = $db->query($sql_bulletin);
										}
								}

}
	