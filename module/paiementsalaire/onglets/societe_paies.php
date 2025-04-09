<?php
require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/custom/paiementsalaire/lib/paiementsalaire.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';
//-------------------------------------------------------------------------------------------------------------------------------------------------------------------------
//Verification de la licence
// Récupérer la clé de licence
$licensekey = trim(file_get_contents(DOL_DOCUMENT_ROOT.'/paiementsalaire/onglets/licence.txt'));
$localkey = file_get_contents(DOL_DOCUMENT_ROOT.'/paiementsalaire/onglets/local.txt');
$nombre_salarie_licence = 0;
$licence = "";
$info = "";
$whmcsurl = 'https://my.ibs-mali.com/';
    // Enter the url to your WHMCS installation here
   // $whmcsurl = 'http://www.example.com/whmcs/';
    // Must match what is specified in the MD5 Hash Verification field
    // of the licensing product that will be used with this check.
   // Must match what is specified in the MD5 Hash Verification field
    // of the licensing product that will be used with this check.
    $licensing_secret_key = 'Ibs@dolipaie';
    // The number of days to wait between performing remote license checks
    $localkeydays = 1;
    // The number of days to allow failover for after local key expiry
    $allowcheckfaildays = 5;

    // -----------------------------------
    //  -- Do not edit below this line --
    // -----------------------------------

    $check_token = time() . md5(mt_rand(1000000000, 9999999999) . $licensekey);
    $checkdate = date("Ymd");
    $domain = $_SERVER['SERVER_NAME'];
    $usersip = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : $_SERVER['LOCAL_ADDR'];
    $dirpath = dirname(__FILE__);
    $verifyfilepath = 'modules/servers/licensing/verify.php';
    $localkeyvalid = false;
    if ($localkey) {
        $localkey = str_replace("\n", '', $localkey); # Remove the line breaks
        $localdata = substr($localkey, 0, strlen($localkey) - 32); # Extract License Data
        $md5hash = substr($localkey, strlen($localkey) - 32); # Extract MD5 Hash
        if ($md5hash == md5($localdata . $licensing_secret_key)) {
            $localdata = strrev($localdata); # Reverse the string
            $md5hash = substr($localdata, 0, 32); # Extract MD5 Hash
            $localdata = substr($localdata, 32); # Extract License Data
            $localdata = base64_decode($localdata);
            $localkeyresults = unserialize($localdata);
            $originalcheckdate = $localkeyresults['checkdate'];
            if ($md5hash == md5($originalcheckdate . $licensing_secret_key)) {
                $localexpiry = date("Ymd", mktime(0, 0, 0, date("m"), date("d") - $localkeydays, date("Y")));
                if ($originalcheckdate > $localexpiry) {
                    $localkeyvalid = true;
                    $results = $localkeyresults;
                    $validdomains = explode(',', $results['validdomain']);
                    if (!in_array($_SERVER['SERVER_NAME'], $validdomains)) {
                        $localkeyvalid = false;
                        $localkeyresults['status'] = "Invalid";
                        $results = array();
                    }
                    $validips = explode(',', $results['validip']);
                    if (!in_array($usersip, $validips)) {
                        $localkeyvalid = false;
                        $localkeyresults['status'] = "Invalid";
                        $results = array();
                    }
                    $validdirs = explode(',', $results['validdirectory']);
                    if (!in_array($dirpath, $validdirs)) {
                        $localkeyvalid = false;
                        $localkeyresults['status'] = "Invalid";
                        $results = array();
                    }
                }
            }
        }
    }
    if (!$localkeyvalid) {
        $postfields = array(
            'licensekey' => $licensekey,
            'domain' => $domain,
            'ip' => $usersip,
            'dir' => $dirpath,
        );

        if ($check_token) $postfields['check_token'] = $check_token;
        $query_string = '';
        foreach ($postfields AS $k=>$v) {
            $query_string .= $k.'='.urlencode($v).'&';
        }
        if (function_exists('curl_exec')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $whmcsurl . $verifyfilepath);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $query_string);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);// désactiver temporaiarement la verification ssl
            //curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

            
            $data = curl_exec($ch);
            curl_close($ch);
        } else {
            $fp = fsockopen($whmcsurl, 80, $errno, $errstr, 5);
            if ($fp) {
                $newlinefeed = "\r\n";
                $header = "POST ".$whmcsurl . $verifyfilepath . " HTTP/1.0" . $newlinefeed;
                $header .= "Host: ".$whmcsurl . $newlinefeed;
                $header .= "Content-type: application/x-www-form-urlencoded" . $newlinefeed;
                $header .= "Content-length: ".@strlen($query_string) . $newlinefeed;
                $header .= "Connection: close" . $newlinefeed . $newlinefeed;
                $header .= $query_string;
                $data = '';
                @stream_set_timeout($fp, 20);
                @fputs($fp, $header);
                $status = @socket_get_status($fp);
                while (!@feof($fp)&&$status) {
                    $data .= @fgets($fp, 1024);
                    $status = @socket_get_status($fp);
                }
                @fclose ($fp);
            }
        }
        if (!$data) {
            $localexpiry = date("Ymd", mktime(0, 0, 0, date("m"), date("d") - ($localkeydays + $allowcheckfaildays), date("Y")));
            if ($originalcheckdate > $localexpiry) {
                $results = $localkeyresults;
            } else {
                $results = array();
                $results['status'] = "Invalid";
                $results['description'] = "Remote Check Failed";
                return $results;
            }
        } else {

            preg_match_all('/<(.*?)>([^<]+)<\/\\1>/i', $data, $matches);
            $results = array();
            //var_dump($matches);
            foreach ($matches[1] AS $k=>$v) {
                //print $v."=".$matches[2][$k]."<br>";
                $results[$v] = $matches[2][$k];
            }
        }
        if (!is_array($results)) {
            die("Invalid License Server Response");
        }
        if ($results['md5hash']) {
            if ($results['md5hash'] != md5($licensing_secret_key . $check_token)) {
                $results['status'] = "Invalid";
                $results['description'] = "MD5 Checksum Verification Failed";
                return $results;
            }
        }
        if ($results['status'] == "Active") {
            $results['checkdate'] = $checkdate;
            $data_encoded = serialize($results);
            $data_encoded = base64_encode($data_encoded);
            $data_encoded = md5($checkdate . $licensing_secret_key) . $data_encoded;
            $data_encoded = strrev($data_encoded);
            $data_encoded = $data_encoded . md5($data_encoded . $licensing_secret_key);
            $data_encoded = wordwrap($data_encoded, 80, "\n", true);
            $results['localkey'] = $data_encoded;
			//print_r($results);
			//var_dump($results);
        }
        $results['remotecheck'] = true;
    }
    unset($postfields,$data,$matches,$whmcsurl,$licensing_secret_key,$checkdate,$usersip,$localkeydays,$allowcheckfaildays,$md5hash);

if ($results['status'] == "Active") {
    $licence = "Active";

	//Prendre le nombre de salariés
        //var_dump($results["configoptions"]);
        $tab = explode("|", $results["configoptions"]);
        for ($i=0; $i < count($tab); $i++) { 		
            $config_info_tab = explode('=', $tab[$i]);//$results["configoptions"] = (Salaries=25)
            if($config_info_tab[0]=="Salaries" && is_int((int)$config_info_tab[1]))
                $nombre_salarie_licence = $config_info_tab[1];

            if($config_info_tab[0]=="Entreprises" && is_int((int)$config_info_tab[1]))
                $nb_entreprise_licence = $config_info_tab[1];

            if($config_info_tab[0]=="Version" && !empty($config_info_tab[1])){
                $soc_sql = "SELECT * FROM ".MAIN_DB_PREFIX."version_dolipaie WHERE active=1";
                $soc_res = $db->query($soc_sql);//= $db->query($covSql);
                if($soc_res)
                    $obj = $db->fetch_object($soc_res);

                $version = explode('.', $config_info_tab[1]);
                $vx = $version[0]; //V1 ou V2 ou V... | par ce que $version[0] contient la lettre V il faut qu'on le traite comme une chaine de caractère

                $old_vers = explode('.', $obj->numero_version);
                if($vx[1] == $old_vers[0]){  //Comparer les premiers indices des versions | si les premier indices sont égaux
                    if($version[1] == $old_vers[1]){
                        if($version[2] > $old_vers[2]){
                            //Mise à jour disponible
                            $sql_update = 'UPDATE '.MAIN_DB_PREFIX.'version_dolipaie SET mise_a_jour="'.$config_info_tab[1].'" WHERE active=1';
                            $db->query($sql_update);
                        }
                    }elseif($version[1] > $old_vers[1]){
                        //Mise à jour disponible
                        $sql_update = 'UPDATE '.MAIN_DB_PREFIX.'version_dolipaie SET mise_a_jour="'.$config_info_tab[1].'" WHERE active=1';
                        $db->query($sql_update);
                    }

                }elseif($vx[1] > $old_vers[0]){
                    //Mise à jour disponible
                    $sql_update = 'UPDATE '.MAIN_DB_PREFIX.'version_dolipaie SET mise_a_jour="'.$config_info_tab[1].'" WHERE active=1';
                    $db->query($sql_update);
                }

            }
		}
	//Enregistrement des informations de licence dans la base de donnée
	$sql_licence = "SELECT rowid FROM ".MAIN_DB_PREFIX."dolipaie_type";
	$result_licence = $db->query($sql_licence);
	if($result_licence){
		$nb_row_licence = $db->num_rows($result_licence);
		if($nb_row_licence > 0){//On supprime et on met à jour les information
			$sql_sup = "DELETE FROM ".MAIN_DB_PREFIX."dolipaie_type";
			$result_sql_sup = $db->query($sql_sql_sup);

			$sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."dolipaie_type"; 
			$sql_insert .= " (licensekey, local_key, nb_salarie, licence_status, proprietaire, societe, email, nom_produit, date_activation, date_expiration, type_abonnement, nb_societe)";
			$sql_insert .= " VALUES('".$licensekey."', '".$results['localkey']."', ".$nombre_salarie_licence.", '".$results["status"]."', '".$results["registeredname"]."', '".$results["companyname"]."', '".$results["email"]."', '".$results["productname"]."', '".$results["regdate"]."', '".$results["nextduedate"]."', '".$results["billingcycle"]."', ".$nb_entreprise_licence.")";
			$result_insert = $db->query($sql_insert);
		}else{//on insert les informations pour la prémière fois
			$sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."dolipaie_type"; 
			$sql_insert .= " (licensekey, local_key, nb_salarie, licence_status, proprietaire, societe, email, nom_produit, date_activation, date_expiration, type_abonnement, nb_societe)";
			$sql_insert .= " VALUES('".$licensekey."', '".$results['localkey']."', ".$nombre_salarie_licence.", '".$results["status"]."', '".$results["registeredname"]."', '".$results["companyname"]."', '".$results["email"]."', '".$results["productname"]."', '".$results["regdate"]."', '".$results["nextduedate"]."', '".$results["billingcycle"]."', ".$nb_entreprise_licence.")";
			$result_insert = $db->query($sql_insert);
		}
	}

	if(!empty($results['localkey']))//stockage de la clé local dans un fichier
    	file_put_contents(DOL_DOCUMENT_ROOT.'/paiementsalaire/onglets/local.txt', $results['localkey']);
		
} else {
    //$info =  "Votre licence est : ".$results['status'];
}


/*je dois juste protéger les buttons générer
*/
if($nombre_salarie_licence == 0){
	$sql_licence = "SELECT nb_salarie, licence_status FROM ".MAIN_DB_PREFIX."dolipaie_type";
	$result_licence = $db->query($sql_licence);
	if($result_licence){
		$nombre_salarie_licence = $db->fetch_object($result_licence)->nb_salarie;
		$licence = $db->fetch_object($result_licence)->licence_status;
	}
}

//-------------------------------------------------------------------------------------------------------------------------------------------------------------------------
llxHeader("", "Paiement | Salaire");
if(!empty($info))
	print '<mark><h3 style="color:red;">'.$info.'</h3></mark>';
$id_societe = GETPOST('id_societe','int');
$action =  GETPOST('action','alpha');
$id_convention = GETPOST('id_convention','int');
//set_time_limit(300); // 300 secondes (5 minutes)
//Verification du nombre de salariés autorisé
$num_sal_exist = 0;
$sql = "SELECT sc.rowid FROM ".MAIN_DB_PREFIX."societe as sc";
	$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."societe_extrafields as sce ON sc.rowid=sce.fk_object WHERE sce.grp=1";
	$result = $db->query($sql);
		
    $num_societe = 0;
	if($result){
        $num_societe = $db->num_rows($result);
		$i = 0;
		while ($i < $num_societe){
		$societe = $db->fetch_object($result);

        $sql = "SELECT u.rowid FROM ".MAIN_DB_PREFIX."user as u";
        $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."user_extrafields as ue ON u.rowid=ue.fk_object Where ue.egp=".$societe->rowid;
        $result1 = $db->query($sql);
        if($result1){
            $num_user = $db->num_rows($result1);
            $j = 0;
            while ($j < $num_user) {
                $users = $db->fetch_object($result1);
                $sql_salarie = "SELECT * FROM ".MAIN_DB_PREFIX."salarie WHERE fk_user=".$users->rowid." AND archiver!='oui'";
                $res = $db->query($sql_salarie);
                if($res){
                    $salarie = $db->fetch_object($res);
                    if($salarie->matricule)
                        $num_sal_exist += 1;
                }
                $j++;
            }
            
        }

        $i++;
       
		}
    }

if($action == "generer" && $num_sal_exist <= $nombre_salarie_licence){
	include("./progression.html");
}/*elseif($num_sal_exist > $nombre_salarie_licence){
	if(empty($info))
	print '<mark><h3 style="color:red;">Votre Abonnement permet de générer uniquement les salaires pour '.$nombre_salarie_licence.' salariés !!!
	<br>Pour générer les salaires de '.$num_sal_exist.' salariés veuillez mettre à jour votre abonnement</h3></mark>';
}*/
if($user->rights->paiementsalaire->societe->read){
	$message = "";
	print load_fiche_titre($langs->trans("Génération des bulletins de paie"), '', '');

	if(empty($action))
		$action = "annee_rechercher";

	$head = paiementsalaireSocieteHead($id_societe, $id_convention);
	print dol_get_fiche_head($head, 'paies', "", -1, '');

	if(!empty($id_convention)){
		$conv_sql = 'SELECT nom FROM '.MAIN_DB_PREFIX.'convention WHERE rowid='.$id_convention;
		$res_conv = $db->query($conv_sql);
		$obj_conv = $db->fetch_object($res_conv);
	$soc_sql = "SELECT * FROM ".MAIN_DB_PREFIX."societe WHERE rowid=".$id_societe;
	$soc_res = $db->query($soc_sql);//= $db->query($covSql);
	$obj_soc = $db->fetch_object($soc_res);
	$obj_soc->name = $obj_soc->nom;
	$obj_soc->element = "societe";
	$obj_soc->conv = $id_convention;

	societe_preview_next($db, $id_societe, $obj_soc);
	entete_societe($obj_soc, 'societe');

	$id_bull = 0;

	$modele_bulletin = "SELECT rowid FROM ".MAIN_DB_PREFIX."modele_bulletin WHERE actif=1";
	$result_modele_bulletin = $db->query($modele_bulletin);//= $db->query($covSql);
	if($result_modele_bulletin){
		$obj_modele_bulletin = $db->fetch_object($result_modele_bulletin);
		$id_bull = $obj_modele_bulletin->rowid;
	}

	if(empty($id_bull))
		$id_bull = 1;
	
	$mois_tab = array(" Janvier "," Février "," Mars "," Avril "," Mai "," Juin "," Juillet "," Août "," Septembre "," Octobre "," Novembre "," Décembre ");

	$monform = new Form($db);
	if($action == "cloture"){
		$mois = GETPOST("mois", "int");
		$annee = GETPOST("annee", "int");
		$url = $_SERVER['PHP_SELF']."?mainmenu=paiementsalaire&leftmenu=societe&id_societe=".$id_societe."&id_convention=".$id_convention."&annee=".$annee."&mois=".$mois;
		$text = "Voulez-vous Vraiment cloturé le mois de ".$mois_tab[($mois-1)]." ".$annee;
		$formconfirm = $monform->formconfirm(
			$url,
			'Les bulletins de ce mois seront cachetés (s\'il y a un cachet) après la cloture ?',
			$text,
			'cloturerMois',
			'',
			'',
			1,
			250,
			'70%'
		);
		print $formconfirm;
		$action = 'annee_rechercher';

	}

	if($action == "cloturerMois"){
		$mois = GETPOST("mois", "int");
		$annee = GETPOST("annee", "int");
		$sql = "UPDATE ".MAIN_DB_PREFIX."bulletin SET cloture='oui' WHERE annee=".$annee." AND mois=".$mois." AND fk_societe=".$id_societe;
		$res = $db->query($sql);
		if($res){
			$sql_select = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$user->id;
			$obj = $db->fetch_object($db->query($sql_select));

			//On garde la trace de l'action
			$message = "Le mois de ".$mois_tab[$mois-1]." ".$annee." est cloturé avec succès";
			$action_effectue = "Cloturé le bulletin de ".$mois_tab[$mois-1]." ".$annee." de la société ".$obj_soc->nom;
			$sql_log = 'INSERT INTO '.MAIN_DB_PREFIX.'log (fk_user, nom, prenom, quand, action_effectue, object_concerne)';
			$sql_log .= ' VALUES('.$user->id.', "'.$obj->lastname.'","'.$obj->firstname.'",now(),"'.$action_effectue.'","Cloture Bulletin")';
			$db->query($sql_log);

		}else $message = "Un problème est survenu";
		$action = 'annee_rechercher';
	}

	$annee = date("Y");
	$mois = (int) date("m");
	$trouve = false;
	$sql = "SELECT sal.fk_user, u.rowid, u.lastname, u.firstname, u.dateemployment, ue.fk_object, ue.egp FROM ".MAIN_DB_PREFIX."salarie as sal";
	$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."user as u ON sal.fk_user=u.rowid";
	$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."user_extrafields as ue ON u.rowid=ue.fk_object WHERE sal.archiver != 'oui' AND ue.egp=".$id_societe;
	
	$result = $db->query($sql);
	if($result){
		$num = $db->num_rows($result);
		if($num > 0){
			$trouve = true;
		}
	}
		if($trouve == true){
			$obj_liste = 0;

			$sql = "SELECT u.*, ue.fk_object, ue.egp FROM ".MAIN_DB_PREFIX."user as u";
			$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."user_extrafields as ue ON u.rowid=ue.fk_object Where ue.egp=".$id_societe;

			$result_user = $db->query($sql);
			if($result_user){
				$num_all = $db->num_rows($result_user);
				$i = 0;
				while ($i < $num_all){
					//Objet Utilisateur
					$obj_user = $db->fetch_object($result_user);
					//Objet salarie
					$sql_sal = "SELECT * FROM ".MAIN_DB_PREFIX."salarie WHERE fk_user=".$obj_user->rowid;
					$result_sal = $db->query($sql_sal);
					$obj_salarie = $db->fetch_object($result_sal);
					if($obj_salarie){
						if($obj_salarie->archiver != 'oui'){
							$salaire_base = 0;
							$grilleSql = "SELECT rowid FROM ".MAIN_DB_PREFIX."grille WHERE active=1 AND fk_convention=".$id_convention;
							$grilleResult = $db->query($grilleSql);//= $db->query($grilleSql);
							$obj_grille = $db->fetch_object($grilleResult);

							$salBaseSql = "SELECT salaire_base FROM ".MAIN_DB_PREFIX."grille_categorie_echelon_salaire_base WHERE fk_grille=".$obj_grille->rowid." AND fk_categorie=".$obj_salarie->fk_categorie." AND fk_echelon=".$obj_salarie->fk_echelon;
							$salBaseResult = $db->query($salBaseSql);//= $db->query($covSql);
							$objSalBase = null;
							if($salBaseResult){
								$objSalBase = $db->fetch_object($salBaseResult);
							}


							if($objSalBase->salaire_base == null){
								$obj_liste ++;
							}

							if($obj_salarie->sursalaire == null){
								$obj_liste ++;
							}

							$sql_contrat1 = "SELECT * FROM ".MAIN_DB_PREFIX."salarie_contrat WHERE fk_salarie=".$obj_salarie->rowid." AND active=1 AND fk_type_contrat != 2";
							$res_contrat1 = $db->query($sql_contrat1);
							if($res_contrat1){
								if($db->num_rows($res_contrat1) <= 0){
									$sql_contrat2 = "SELECT * FROM ".MAIN_DB_PREFIX."salarie_contrat WHERE fk_salarie=".$obj_salarie->rowid." AND active=1 AND fk_type_contrat = 2";
									$res_contrat2 = $db->query($sql_contrat2);
									if($db->num_rows($res_contrat2) <= 0){
										$obj_liste ++;
									}
								}
							}else $obj_liste ++;


							if(!$obj_salarie->date_anciennete && !$obj_user->dateemployment){
								$obj_liste ++;

							}

							if(!$obj_user->job){
								$obj_liste ++;

							}

							if(!$obj_salarie->situation_familiale){
								$obj_liste ++;
							}


							if($obj_salarie->nombre_enfant == null){
								$obj_liste ++;
							}

							if($obj_salarie->nombre_enfant_hand == null){
								$obj_liste ++;
							}
						}

						//---------------------------------------------------------------------
					}else $obj_liste ++;
					$i ++;
				}
			}
			if($obj_liste < 1){
				$verification_ok = true;
			}

	if($action == "generer"){
				$mois = GETPOST("mois",'int');
				$annee = GETPOST("annee",'int');
				$suppression = false;
				$ok = true;
				if($verification_ok == true){
					$id_accord_etab = 0;
					$accord_sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."accord_etablissement WHERE fk_socite=".$id_societe;
					$accord_res = $db->query($accord_sql);
					if($accord_res){
						if($db->num_row($accord_res) >0){
							$obj_accord = $db->fetch_object($accord_res);
							$id_accord_etab = $obj_accord->rowid;
						}
					}

				
				$tab_sal_rowid = array();
				$tab_user_rowid = array();

				$sql = "SELECT sal.rowid, sal.fk_user, u.rowid as id_user, u.lastname, u.firstname FROM ".MAIN_DB_PREFIX."salarie as sal";
				$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."user as u ON sal.fk_user=u.rowid";
				$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."user_extrafields as ue ON u.rowid=ue.fk_object WHERE sal.archiver!='oui' AND ue.egp=".$id_societe;
				$sql .= " ORDER BY sal.rowid";
				//$sql = "SELECT u.rowid, u., u.firstname, u.dateemployment, ue.fk_object, ue.egp FROM ".MAIN_DB_PREFIX."user as u";
				//$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."user_extrafields as ue ON u.rowid=ue.fk_object Where ue.egp=".$id_societe;
				$result_global = $db->query($sql);
				if($result_global){
					$tab_ok = array();
					$i = 0;
					$num_all = $db->num_rows($result_global);
					while ($i < $num_all){
						$ok = false;
						$somme_pr_ind_hs = 0;
						$obj_salarie = $db->fetch_object($result_global);

							$c = $i;
								if ($c < $num_all) {
									//Objet Utilisateur
									//A ce point tous les salariés ont un contrat
									//Verifions si le contrat est toujours actif (CDD);
									$sql_contrat1 = "SELECT * FROM ".MAIN_DB_PREFIX."salarie_contrat WHERE fk_salarie=".$obj_salarie->rowid." AND active=1 AND fk_type_contrat != 2";
									$sql_contrat1 .= " AND ( YEAR(date_fin)>".$annee;
									$sql_contrat1 .= " OR ((YEAR(date_fin) = ".$annee."  AND MONTH(date_fin) > ".$mois.") OR  (YEAR(date_fin) = ".$annee."  AND MONTH(date_fin) = ".$mois.")))";
									$res_contrat1 = $db->query($sql_contrat1);

									if($db->num_rows($res_contrat1) <= 0){
										//Pas de contrat CDD actif
										$sql_contrat2 = "SELECT * FROM ".MAIN_DB_PREFIX."salarie_contrat WHERE fk_salarie=".$obj_salarie->rowid." AND active=1 AND fk_type_contrat = 2";
										$res_contrat2 = $db->query($sql_contrat2);
										if($db->num_rows($res_contrat2) <= 0){
											$sql_contrat3 = "SELECT * FROM ".MAIN_DB_PREFIX."salarie_contrat WHERE fk_salarie=".$obj_salarie->rowid." AND active=1";
											$res_contrat3 = $db->query($sql_contrat3);
											if($db->num_rows($res_contrat3) > 0){
												//pas de contrat CDI ==> sont salaire ne doit pas être générer.
												print '<a target="_blank" href= "./contrat.php?mainmenu=paiementsalaire&leftmenu=salarie&fk_salarie='.$obj_salarie->rowid.'&id_societe='.$id_societe.'&id='.$obj_salarie->id_user.'&id_convention='.$id_convention.'&action=detail">'.$obj_salarie->firstname.' '.$obj_salarie->lastname.'</a> <mark>Matricule ="'.$obj_salarie->matricule.'"  à un problème de contrat. On ne peut pas générer son salaire.</mark><br><br>';
												//On passe au salarié suivant
												$ok = false;
												if($c < $num_all)
													$obj_salarie = $db->fetch_object($result_global);
											}else $contrat = "N/A";
										}else{
											$ok = true;
											if($obj_salarie->calcul_salaire == 'non'){
												$ok = false;
												print '<a target="_blank" href= "./salarie_information.php?mainmenu=paiementsalaire&leftmenu=salarie&fk_salarie='.$obj_salarie->rowid.'&id_societe='.$id_societe.'&id='.$obj_salarie->id_user.'&id_convention='.$id_convention.'&action=detail">Le champs calcul salaire de '.$obj_salarie->firstname.' '.$obj_salarie->lastname.'</a> <mark>Matricule ="'.$obj_salarie->matricule.'" est à NON.</mark><br><br>';
											}
											$sql_type_contrat = "SELECT libelle FROM ".MAIN_DB_PREFIX."type_contrat WHERE rowid=".$db->fetch_object($res_contrat2)->fk_type_contrat;
											$restype_contrat = $db->query($sql_type_contrat);
											$contrat = $db->fetch_object($restype_contrat)->libelle;

										}
									}else{
										if($obj_salarie->calcul_salaire == 'non'){
											$ok = false;
											print '<a target="_blank" href= "./salarie_information.php?mainmenu=paiementsalaire&leftmenu=salarie&fk_salarie='.$obj_salarie->rowid.'&id_societe='.$id_societe.'&id='.$obj_salarie->id_user.'&id_convention='.$id_convention.'&action=detail">Le champs calcul salaire de '.$obj_salarie->firstname.' '.$obj_salarie->lastname.'</a> <mark>Matricule ="'.$obj_salarie->matricule.'" est à NON.</mark><br><br>';
										}
										$ok = true;
										$sql_type_contrat = "SELECT libelle FROM ".MAIN_DB_PREFIX."type_contrat WHERE rowid=".$db->fetch_object($res_contrat1)->fk_type_contrat;
										$restype_contrat = $db->query($sql_type_contrat);
										$contrat = $db->fetch_object($restype_contrat)->libelle;

									}


								}


								
								//$fk_salarie = $obj_salarie->fk_salarie;
								if($ok){
									$tab_user_rowid[] = $obj_salarie->fk_user;
									$tab_sal_rowid[] = $obj_salarie->rowid;
								}

						$i ++;
					}

					if($num_sal_exist > $nombre_salarie_licence){
						print '<mark><h3 style="color:red;">Votre Abonnement permet de générer uniquement les salaires pour '.$nombre_salarie_licence.' salariés !!!
						<br>Pour générer les salaires de '.count($tab_sal_rowid).' salariés veuillez mettre à jour votre abonnement</h3></mark>';
					}else{
					// Nom du fichier à créer
					$filename = "tmp_sal.txt";
						// Ouvrir le fichier en mode écriture (créé s'il n'existe pas)
						$file = fopen($filename, "w"); // "w" = écrire et écraser le contenu existant
						fwrite($file, $id_societe."/".$id_convention."/".$id_societe."/".$mois."/".$annee."/".$nombre_salarie_licence."\n"); //ligne 1 = information de la société plus la date
						if ($file) {
							for ($i=0; $i < count($tab_sal_rowid); $i++) { //ligne 2 = id salarié
								// Écrire dans le fichier
								if($i < $nombre_salarie_licence)
									fwrite($file, $tab_sal_rowid[$i]."/");
							}

							fwrite($file, "\n");
							for ($i=0; $i < count($tab_user_rowid); $i++) { //ligne 3 = id utilisateur
								// Écrire dans le fichier
								fwrite($file, $tab_user_rowid[$i]."/");
							}

							fwrite($file, "\n");
							//ligne 4 //nombre total de salariés Initialisation de la progression
							if(count($tab_sal_rowid) < $nombre_salarie_licence) //si le nombre de salarié inscrit est inferieur au nombre de salarié lié à la licence alors on génère le salaire de tous le monde
								fwrite($file, count($tab_sal_rowid));
							else fwrite($file, $nombre_salarie_licence);//sinon on génère le salaire des salariés liés à la licence

							fclose($file); 						

						}else {
							echo "Erreur : Impossible d'ouvrir le fichier.";
						}

						//generateSalary(2);
						// Traitement des salariés par lots

						print "<script>
						const id_societe =". $id_societe.";
						const id_convention =". $id_convention.";

						const id_accord_etab =". $id_accord_etab.";
						const mois =". $mois.";
						const annee =". $annee.";
						const nb_sal_licence = ".$nombre_salarie_licence.";
						
						function updateProgression() {
							const params = new URLSearchParams({
								id_societe: id_societe, // Exemple de paramètre
								id_convention: id_convention,
								id_accord : id_accord_etab,
								mois: mois,
								annee: annee,
								nb_sal_licence: nb_sal_licence
							});
							fetch('generation_salaire.php?' + params)
								.then(response => response.json())
								.then(data => {
									alert(data.effectue);
								});

													

						}
						updateProgression();
						//setInterval(updateProgression, 2000);
					</script>";

					//Gardon la trace
						$sql_select = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$user->id;
						$obj = $db->fetch_object($db->query($sql_select));

						$soc_sql = "SELECT nom FROM ".MAIN_DB_PREFIX."societe WHERE rowid=".$id_societe;
							$soc_res = $db->query($soc_sql);//= $db->query($covSql);
							$obj_soc = $db->fetch_object($soc_res);

						$action_effectue = "Génération avec succès des bulletins de ".$mois_tab[$mois-1]." ".$annee." de la société ".$obj_soc->nom;
						$sql_log = 'INSERT INTO '.MAIN_DB_PREFIX.'log (fk_user, nom, prenom, quand, action_effectue, object_concerne)';
						$sql_log .= ' VALUES('.$user->id.', "'.$obj->lastname.'","'.$obj->firstname.'",now(),"'.$action_effectue.'","Generation Bulletins")';
						$db->query($sql_log);

					//On garde la traçabilité et générons un message du succès de l'opération
					if($dnum != 0 )
						$dnum = 0;
					if(in_array("OK", $tab_ok) && $dnum == 0){
						$sql_select = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$user->id;
						$obj = $db->fetch_object($db->query($sql_select));

						$action_effectue = "Génération avec succès des bulletins de ".$mois_tab[$mois-1]." ".$annee." de la société ".$obj_soc->nom;
						$sql_log = 'INSERT INTO '.MAIN_DB_PREFIX.'log (fk_user, nom, prenom, quand, action_effectue, object_concerne)';
						$sql_log .= ' VALUES('.$user->id.', "'.$obj->lastname.'","'.$obj->firstname.'",now(),"'.$action_effectue.'","Generation Bulletins")';
						$db->query($sql_log);
						$message = "Les salaires des Employés du mois".$mois_tab[$mois-1]." sont générés avec succès";
					}elseif($dnum == 0){
						$message = "Génération des salaires lancés avec succès<br>";
						$message .= "Veuillez attendre la fin du processus";

					}else{
						$message = "Tous les salariés ont un problème de contrat";
					}
				}
			}
				$action = "annee_rechercher";
			}else{
				print "<div><table class='tagtable liste'>";
				print "<tr class='liste_titre'><td>Resultat de la verification</td></tr>";
				print "<tr><td><h2>Echec de la phase de verification".img_picto("Impossible de Générer", "error")."</h2></td></tr>";
				print "<tr><td>".img_picto("Aide", "help")." Veuillez completer les informations des salariés dans l'onglet <a href='./validation_societe.php?mainmenu=paiementsalaire&leftmenu=societe&id_societe=".$id_societe."&id_convention=".$id_convention."' ><b>Validation</b><a/></td></tr>";
				print "</table> </div>";
			}
			}else if($action == "voir" || $action == "telecharger"){
				$annee_rech = GETPOST("annee", 'int');
				$mois_rech = (int) GETPOST("mois", 'int');
				$object_liste = array();
				$limit = GETPOST('limit','int')?:20;
				$arret = GETPOST('arret','int')?:0;
				$nb_page = GETPOST('nbpage','int')?:1;

				$recherche_nom = "";
				$recherche_prenom = "";
				$recherche_nom = GETPOST("recherche_nom", "alpha");
				$recherche_prenom = GETPOST("recherche_prenom", "alpha");


				$sql_verif = "SELECT sal.rowid , sal.fk_user, u.firstname, u.lastname, u.rowid as id_user, bul.rowid as id_bulletin, bul.fk_salarie as mat, bul.annee, bul.mois, bul.fk_societe  FROM ".MAIN_DB_PREFIX."salarie as sal";
				$sql_verif .= " LEFT JOIN ".MAIN_DB_PREFIX."bulletin as bul ON bul.fk_salarie=sal.rowid";
				$sql_verif .= " LEFT JOIN ".MAIN_DB_PREFIX."user as u ON sal.fk_user=u.rowid";
				$sql_verif .= " WHERE bul.annee=".$annee_rech." AND bul.mois=".$mois_rech." AND bul.fk_societe=".$id_societe;

				if(!empty(GETPOST("recherche_nom", "alpha"))){
					$sql_verif .= " AND (u.lastname LIKE '%".GETPOST("recherche_nom", "alpha")."%'";
					$sql_verif .= " OR u.firstname LIKE '%".GETPOST("recherche_nom", "alpha")."%')";

				}

				if(!empty(GETPOST("recherche_prenom", "alpha"))){
					$sql_verif .= " AND (u.firstname LIKE '%".GETPOST("recherche_prenom", "alpha")."%'";
					$sql_verif .= " OR u.lastname LIKE '%".GETPOST("recherche_prenom", "alpha")."%')";

				}
				$zero = false;
				$res_verif = $db->query($sql_verif);
				if($res_verif){
					$num = $db->num_rows($res_verif);
					if($num > 0){


						$a = 0;
						while ($a < $num) {

							$object_liste[count($object_liste)] = $db->fetch_object($res_verif);
								$a ++;
						}
					}
				//Gestion des action voir et telcharger
				//----------------------------------------------------------------------------------
				$num = count($object_liste) == 0 ? 1 : count($object_liste);
				$sel10 = "";
				$sel25 = "";
				$sel20 = "";
				$sel30 = "";
				$sel50 = "";
				$sel100 = "";
				$sel200 = "";
				$sel500 = "";
				$sel1000 = "";
				$seltout = "";
				if($limit == 5)
					$sel5 = "selected";
				elseif($limit == 10)
					$sel10 = "selected";
				elseif($limit == 15)
					$sel15 = "selected";
				elseif($limit == 20)
					$sel20 = "selected";
				elseif($limit == 30)
					$sel30 = "selected";
				elseif($limit == 50)
					$sel50 = "selected";
				elseif($limit == 100)
					$sel100 = "selected";
				elseif($limit == 200)
					$sel200 = "selected";
				elseif($limit == 500)
					$sel500 = "selected";
				elseif($limit == 1000)
					$sel1000 = "selected";
				else $seltout = "selected";
			print '<form name="ajouter" method="POST" action="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=societe&id_convention='.$id_convention.'&id_societe='.$id_societe.'&annee='.$annee_rech.'&mois='.$mois_rech.'&action='.$action.'">';
			print '<input type="hidden" name="token" value="'.newToken().'">';
			print '<input type="hidden" name="action" value="'.$action.'">';

			print "<div style='float:right; padding-top: 5px; margin-right:20px;'>";
			print"<select style='padding:10px' name='limit' id='limit' >";
			print "<option value='5' ".$sel5." ><b>5</b></option>
			<option value='10' ".$sel10."><b>10</b></option>
			<option value='15' ".$sel15."><b>15</b></option>
			<option value='20' ".$sel20."><b>20</b></option>
			<option value='30' ".$sel30."><b>30</b></option>
			<option value='50' ".$sel50."><b>50</b></option>
			<option value='100' ".$sel100."><b>100</b></option>
			<option value='200' ".$sel200."><b>200</b></option>
			<option value='500' ".$sel500."><b>500</b></option>
			<option value='1000' ".$sel1000."><b>1000</b></option>
			<option value='tout' ".$seltout."><b>tout</b></option>";

			print "</select>";
			if(!empty(GETPOST("limit", "alpha")))
				$limit = $num;

			print "<mark><b>".(GETPOST("nbpage", "int")?:1)."</b></mark> sur <mark><b>".(((int)($num%$limit))==0?((int)($num/$limit)):((int)($num/$limit)+1))."</b></mark>";
					print '<script type="text/javascript">
					var convention = document.getElementById("limit");
					convention.addEventListener("change", function () {
						var limit = convention.value;
						window.location.href = "'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenusociete&id_societe='.$id_societe.'&id_convention='.$id_convention.'&limit="+limit+"&action='.$action.'&annee='.$annee_rech.'&mois='.$mois_rech.'&recherche_nom='.$recherche_nom.'&recherche_prenom='.$recherche_prenom.'&recherche_fk_salarie='.$recherche_fk_salarie.'&recherche_anciennete='.$recherche_anciennete.'";
					},
					false,
					);
					</script>';

				print "</select>";
				print "</div><br><br>";
				$num = count($object_liste);

					if($action == "telecharger")
						$bouton = "Télécharger pour tous les salariés";
					else $bouton = "Voir pour tous les salariés";
					if($id_bull == 1)
						$url = "../doc/bulletin_tout_salarie.php?id_societe=".$id_societe."&id_convention=".$id_convention."&mois=".$mois_rech."&annee=".$annee_rech."&action=".$action;
					elseif($id_bull == 2)
						$url = "../doc/modele_moyen/bulletin_tout_salarie.php?id_societe=".$id_societe."&id_convention=".$id_convention."&mois=".$mois_rech."&annee=".$annee_rech."&action=".$action;
					elseif($id_bull == 3)
						$url = "../doc/modele_avance/bulletin_tout_salarie.php?id_societe=".$id_societe."&id_convention=".$id_convention."&mois=".$mois_rech."&annee=".$annee_rech."&action=".$action;

					print "<h2>Les bulletins de paie du ".$mois_tab[$mois_rech-1]." ".$annee_rech."<a target='_blank' href=".$url." style='float: right;' class='button'>".$bouton."</a></h2>";

					//Tableau
					print "<table style='width:100%;' class='tagtable liste'>";
					print "<thead><tr class='liste_titre'><th style='width:25%; padding: 15px; text-align:center;'>Nom<br>
					<input style='padding:10px' type='text' Placeholder='Nom' value='".$recherche_nom."' name='recherche_nom' ></th>

					<th style='width:25%; padding: 15px; text-align:center;'>Prénom <br><input style='padding:10px' type='text' Placeholder='Prenom' value='".$recherche_prenom."' name='recherche_prenom' >
					</th><th style='width:25%; padding: 15px; text-align:center;'>Bulletins (".$num.")<br>
					<input type='submit' class='button' value='Rechercher' >
					</form>
					<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenu=societe&id_societe=".$id_societe."&id_convention=".$id_convention."&mois=".$mois_rech."&annee=".$annee_rech."&action=".$action."' class='button' >Annuler</a></th></tr></thead>";

					print "<tbody>";
					if($num > 0){

							$i = $arret;
							while($i < $num){
								if($action == "telecharger"){
									print '<tr class="impair" ><td style="text-align:center; padding:0px">'.$object_liste[$i]->lastname.'</><td style="text-align:center; padding:0px">'.$object_liste[$i]->firstname.'</td>';
									if($id_bull == 1)
										print '<td style="text-align:center; padding:0px"><a class="button" target="_blank" href="../doc/bulletin.php?id_societe='.$id_societe.'&fk_user='.$object_liste[$i]->id_user.'&fk_salarie='.$object_liste[$i]->rowid.'&id_convention='.$id_convention.'&mois='.$mois_rech.'&annee='.$annee_rech.'&action='.$action.'">Télécharger</a></td></tr>';
									elseif($id_bull == 2)
										print '<td style="text-align:center; padding:0px"><a class="button" target="_blank" href="../doc/modele_moyen/bulletin.php?id_societe='.$id_societe.'&fk_user='.$object_liste[$i]->id_user.'&fk_salarie='.$object_liste[$i]->rowid.'&id_convention='.$id_convention.'&mois='.$mois_rech.'&annee='.$annee_rech.'&action='.$action.'">Télécharger</a></td></tr>';
									elseif($id_bull == 3)
										print '<td style="text-align:center; padding:0px"><a class="button" target="_blank" href="../doc/modele_avance/bulletin.php?id_societe='.$id_societe.'&fk_user='.$object_liste[$i]->id_user.'&fk_salarie='.$object_liste[$i]->rowid.'&id_convention='.$id_convention.'&mois='.$mois_rech.'&annee='.$annee_rech.'&action='.$action.'">Télécharger</a></td></tr>';
								}else{
									print '<tr class="impair" ><td style="text-align:center; padding:0px">'.$object_liste[$i]->lastname.'</><td style="text-align:center; padding:0px">'.$object_liste[$i]->firstname.'</td>';
									if($id_bull == 1)
										print '<td style="text-align:center; padding:0px"><a class="button" target="_blank" href="../doc/bulletin.php?id_societe='.$id_societe.'&fk_user='.$object_liste[$i]->id_user.'&fk_salarie='.$object_liste[$i]->rowid.'&id_convention='.$id_convention.'&mois='.$mois_rech.'&annee='.$annee_rech.'&action='.$action.'">Voir</a></td></tr>';
									elseif($id_bull == 2)
										print '<td style="text-align:center; padding:0px"><a class="button" target="_blank" href="../doc/modele_moyen/bulletin.php?id_societe='.$id_societe.'&fk_user='.$object_liste[$i]->id_user.'&fk_salarie='.$object_liste[$i]->rowid.'&id_convention='.$id_convention.'&mois='.$mois_rech.'&annee='.$annee_rech.'&action='.$action.'">Voir</a></td></tr>';
									elseif($id_bull == 3)
										print '<td style="text-align:center; padding:0px"><a class="button" target="_blank" href="../doc/modele_avance/bulletin.php?id_societe='.$id_societe.'&fk_user='.$object_liste[$i]->id_user.'&fk_salarie='.$object_liste[$i]->rowid.'&id_convention='.$id_convention.'&mois='.$mois_rech.'&annee='.$annee_rech.'&action='.$action.'">Voir</a></td></tr>';
								}
									if($i!= 0 && (($i+1)%$limit) == 0){
										$arret = $i;
										$i = $num;
									}else
										$i ++;
							}



					}
					if(count($object_liste) ==0){
						print "<tr><td colspan='3' align='center'><style='align:center;'>Aucun salarié</td></tr>";
					}
					print "</tbody>";
					print "</table>";

				}else print "<h2 style='align:center;'>Pas d'historique pour le ".$mois_tab[$mois_rech-1]."".$annee_rech."!";

				print '<span style="float:right; margin-left: 20px;">';
		$nb = (((int)($num%$limit))==0?((int)($num/$limit)):((int)($num/$limit)+1));
		$page_link = "";
		if($num>$limit){

			if($nb_page!= 1)
				if($nb==0 && 1 < ($nb))
					$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&annee=".$annee."&mois=".$mois_rech."&limit=".$limit."&arret=0&nbpage=1&action=".$action."&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_fk_salarie=".$recherche_fk_salarie."&recherche_anciennete=".$recherche_anciennete."' style='padding: 5px'><b>Debut</b>    </a>&nbsp;&nbsp;";
				else if(1 < ($nb+1))
				$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&annee=".$annee."&mois=".$mois_rech."&limit=".$limit."&arret=0&nbpage=1&action=".$action."&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_fk_salarie=".$recherche_fk_salarie."&recherche_anciennete=".$recherche_anciennete."' style='padding: 5px'><b>Debut</b>    </a>&nbsp;&nbsp;";


			if($arret > $limit){


				if($nb_page-3>=0)
					$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&annee=".$annee."&mois=".$mois_rech."&limit=".$limit."&arret=".($limit*($nb_page-3))."&nbpage=".($nb_page-2)."&action=".$action."&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_fk_salarie=".$recherche_fk_salarie."&recherche_anciennete=".$recherche_anciennete."' style='padding: 5px'><b>".($nb_page -2)."</b></a>&nbsp;&nbsp;";

				if($nb_page-2>=0)
							$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&annee=".$annee."&mois=".$mois_rech."&limit=".$limit."&arret=".($limit*($nb_page-2))."&nbpage=".($nb_page-1)."&action=".$action."&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_fk_salarie=".$recherche_fk_salarie."&recherche_anciennete=".$recherche_anciennete."' style='padding: 5px'><b>".($nb_page-1)."</b></a>&nbsp;&nbsp;";


				if($nb_page-1>=0)
						$page_link .= "<a style='background-color: yellow;' href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&annee=".$annee."&mois=".$mois_rech."&limit=".$limit."&arret=".($limit*($nb_page-1))."&nbpage=".($nb_page)."&action=".$action."&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_fk_salarie=".$recherche_fk_salarie."&recherche_anciennete=".$recherche_anciennete."' style='padding: 5px'><b>".($nb_page)."</b></a>&nbsp;&nbsp;";




					if(	(($nb_page+1) <= ($nb)))
						$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&annee=".$annee."&mois=".$mois_rech."&limit=".$limit."&arret=".($limit*$nb_page)."&nbpage=".($nb_page+1)."&action=".$action."&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_fk_salarie=".$recherche_fk_salarie."&recherche_anciennete=".$recherche_anciennete."' style='padding: 5px'><b>".($nb_page + 1)."</b></a>&nbsp;&nbsp;";


					if((($nb_page+2) <= ($nb)))
						$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&annee=".$annee."&mois=".$mois_rech."&limit=".$limit."&arret=".($limit*($nb_page +1))."&nbpage=".($nb_page+2)."&action=".$action."&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_fk_salarie=".$recherche_fk_salarie."&recherche_anciennete=".$recherche_anciennete."' style='padding: 5px'><b>".($nb_page + 2)."</b></a>&nbsp;&nbsp;";


					if((($nb_page+3) <= ($nb)))
						$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&annee=".$annee."&mois=".$mois_rech."&limit=".$limit."&arret=".($limit*($nb_page+2))."&nbpage=".($nb_page+3)."&action=".$action."&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_fk_salarie=".$recherche_fk_salarie."&recherche_anciennete=".$recherche_anciennete."' style='padding: 5px'><b>".($nb_page + 3)."</b></a>&nbsp;&nbsp;";




			}else{


					if( 1 <= ($nb))

						$page_link .= "<a style='background-color: yellow;' href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&annee=".$annee."&mois=".$mois_rech."&limit=".$limit."&arret=0&nbpage=1&action=".$action."&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_fk_salarie=".$recherche_fk_salarie."&recherche_anciennete=".$recherche_anciennete."' style='padding: 5px'><b>1</b></a>&nbsp;&nbsp;";


					if(2 <= ($nb))

						$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&annee=".$annee."&mois=".$mois_rech."&limit=".$limit."&arret=".$limit."&nbpage=2&action=".$action."&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_fk_salarie=".$recherche_fk_salarie."&recherche_anciennete=".$recherche_anciennete."' style='padding: 5px'><b>2</b></a>&nbsp;&nbsp;";


					if(3 <= ($nb))

						$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&annee=".$annee."&mois=".$mois_rech."&limit=".$limit."&arret=".($limit*2)."&nbpage=3&action=".$action."&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_fk_salarie=".$recherche_fk_salarie."&recherche_anciennete=".$recherche_anciennete."' style='padding: 5px'><b>3</b></a>&nbsp;&nbsp;";

					if(4 <= ($nb))

						$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&annee=".$annee."&mois=".$mois_rech."&limit=".$limit."&arret=".($limit*3)."&nbpage=4&action=".$action."&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_fk_salarie=".$recherche_fk_salarie."&recherche_anciennete=".$recherche_anciennete."' style='padding: 5px'><b>4</b></a>&nbsp;&nbsp;";

					if(5 <= ($nb))

						$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&annee=".$annee."&mois=".$mois_rech."&limit=".$limit."&arret=".($limit*4)."&nbpage=5&action=".$action."&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_fk_salarie=".$recherche_fk_salarie."&recherche_anciennete=".$recherche_anciennete."' style='padding: 5px'><b>5</b></a>&nbsp;&nbsp;";



			}
			if($nb_page != ($nb)  )
					$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&annee=".$annee."&mois=".$mois_rech."&limit=".$limit."&arret=".($limit*($nb-1))."&nbpage=".($nb)."&action=".$action."&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_fk_salarie=".$recherche_fk_salarie."&recherche_anciennete=".$recherche_anciennete."' style='padding: 5px'>      <b>Fin</b></a>&nbsp;&nbsp;";


		}
		print $page_link.'</span>';
	}

	
			//Gestion des année et des dates pour l'historique => Gestion des actions recherche année
			if($action == 'annee_rechercher'){
				$annee_rechercher = GETPOST("annee_rechercher", "int");
				$annee_courant = (int) date("Y");
				if(empty($annee_rechercher)){
					$sql_verif = "SELECT annee FROM ".MAIN_DB_PREFIX."bulletin WHERE fk_societe=".$id_societe." ORDER BY annee DESC";
						$res_verif = $db->query($sql_verif);
						$obj_verif = $db->fetch_object($res_verif);
						if($obj_verif)
							$annee_rechercher = $obj_verif->annee;
						else	$annee_rechercher = (int) date("Y");
				}
				$mois_courant = (int) date("m");

				if($annee_rechercher != $annee_courant)
					print "<h2 style='align:center; display: inline'>Historique de l'année ".$annee_rechercher."!</h2>";
				else print "<h2 style='align:center;display: inline'>Bulletins du ".$annee_rechercher."!</h2>";
				print "<div style='float: right; display: inline''>";
				print '<form name="add" method="POST" action="'.$_SERVER['PHP_SELF'].'?mainmenu=paiementsalaire&leftmenu=societe&id_societe='.$id_societe.'&id_convention='.$id_convention.'">';
					print '<input type="hidden" name="token" value="'.newToken().'">';
					print '<input type="hidden" name="action" value="annee_rechercher">';

					print "<select name='annee_rechercher'>";
					$sql_verif = "SELECT DISTINCT annee FROM ".MAIN_DB_PREFIX."bulletin WHERE fk_societe=".$id_societe;
					$res_verif = $db->query($sql_verif);
					if($res_verif){
						$num_all = $db->num_rows($res_verif);
						$i=0;
						$annee_tab = array();
						while ($i < $num_all) {
							$obj_annee = $db->fetch_object($res_verif);
							$annee_tab[] = $obj_annee->annee;
							if($obj_annee->annee == $annee_rechercher)
								print "<option value='".($obj_annee->annee)."' selected >".($obj_annee->annee)."</option>";
							else print "<option value='".($obj_annee->annee)."'>".($obj_annee->annee)."</option>";


							$i ++;
						}
						if($num_all == 0){
							print "<option value='".date("Y")."' selected >".date("Y")."</option>";
						}elseif(!in_array(date("Y"), $annee_tab))
							if($annee_rechercher == $annee_courant)
								print "<option value='".date("Y")."' selected>".date("Y")."</option>";
							else print "<option value='".date("Y")."' >".date("Y")."</option>";


					}
					print "</select><input type='submit' value='Rechercher'class='button'></form>";

				print "</div>";
				print "<table class='tagtable liste'>";
					print "<thead>";
					print "<tr class='liste_titre'><th rowspan='2'>Mois</th>";
					print "<th rowspan='2'>Nb salarié</th>";
					print "<th rowspan='2'>Masse salariale brute</th>";
					print "<th rowspan='2'>Masse salariale net</th>";
					print "<th rowspan='2'>Total I.T.S</th>";
					print "<th colspan='2' align='center'>Total Cotisation</th>";
					print "<th rowspan='2' align='center' >Opérations</tr>";
					print "<tr><th>Employé</th><th>Employeur</th></tr>";
					print "</thead>";
if($annee_rechercher == $annee_courant){
						
		$une_fois = false;
		$dernier_mois = 0;
		$etat_cloture = "";

		$sql_verif = "SELECT rowid FROM ".MAIN_DB_PREFIX."bulletin WHERE annee=".$annee_rechercher."  AND fk_societe=".$id_societe;
		$res_verif = $db->query($sql_verif);
		if($res_verif){
			$nb = $db->num_rows($res_verif);
			if($nb > 0){
					$une_fois = true;
			}
		}

		if($une_fois){
			$sql_verif = "SELECT mois, cloture FROM ".MAIN_DB_PREFIX."bulletin WHERE annee=".$annee_rechercher."  AND fk_societe=".$id_societe." ORDER BY mois DESC";
			$res_verif = $db->query($sql_verif);
			if($res_verif){
				$obj = $db->fetch_object($res_verif);
				$dernier_mois = $obj->mois;
				$etat_cloture = $obj->cloture;

			}

		

		}

		//AFFICHAGE
		$suivant = false;
		for ($i=0; $i < count($mois_tab); $i++) {

			$total = 0;
			$a = 0;
			$somme_taxe = 0;
			$somme_cotisation = 0;
			$somme_cotisation_employe = 0;
			$somme_cotisation_employeur = 0;
			$tab_obj = array();
		//Vérification d'un bulletin bonus (complément salaire)
		$sql_bonus_bulletin = "SELECT nom_bonus FROM ".MAIN_DB_PREFIX."bulletin_bonus WHERE annee=".$annee_rechercher." AND mois=".($i + 1)." AND fk_societe=".$id_societe;
		$res_bonus_bulletin  = $db->query($sql_bonus_bulletin);
		$num_bull_bon = $db->num_rows($res_bonus_bulletin);
		$info_bonus = '';
		$style = '';
		if($num_bull_bon > 0){
			$obj_bonus_bulletin = $db->fetch_object($res_bonus_bulletin);
			$titre = 'Il existe un Complément salaire "'.$obj_bonus_bulletin->nom_bonus.'" pour ce mois. Cliquez pour voir';
			$info_bonus = "&nbsp;&nbsp;<div style='display: inline; padding-bottom: 100px;'><a href='./bonus_paies.php?mainmenu=paiementsalaire&leftmenu=societe&id_convention=".$id_convention."&id_societe=".$id_societe."'><img src='./icon/down_arrow.png' onClick=cacher('bonus".$i."') width='20' height='15' title='".$titre."'></a></div>";
			$style = 'style="border : 0px;"';
		}


				$sql_verif = "SELECT rowid, cloture FROM ".MAIN_DB_PREFIX."bulletin WHERE annee=".$annee_rechercher." AND mois=".($i + 1)." AND fk_societe=".$id_societe;
				$res_verif = $db->query($sql_verif);
				if($res_verif){
					$nb_salarie = $db->num_rows($res_verif);
					if($nb_salarie > 0){
							$une_fois = true;
							$obj_verif = $db->fetch_object($res_verif);
							$sql_som_salaire = "SELECT SUM(salaire_brut) as sal_brut, SUM(net_payer) as sal_net FROM ".MAIN_DB_PREFIX."bulletin WHERE annee=".$annee_rechercher." AND mois=".($i + 1)." AND fk_societe=".$id_societe;
							$res_som_salaire  = $db->query($sql_som_salaire);
							$tab_obj[0] = $db->fetch_object($res_som_salaire);

							//$total += $tab_obj[0]->sal_brut + $tab_obj[0]->sal_net;

							
							$sql_id_bulletin = "SELECT rowid FROM ".MAIN_DB_PREFIX."bulletin WHERE annee=".$annee_rechercher." AND mois=".($i + 1)." AND fk_societe=".$id_societe;
							$res_id_bulletin  = $db->query($sql_id_bulletin);
							$num_k = $db->num_rows($res_id_bulletin);
							while ($a < $num_k){
								$obj_id_bulletin = $db->fetch_object($res_id_bulletin);
								$sql_som_taxe = "SELECT SUM(montant) as montant FROM ".MAIN_DB_PREFIX."bulletin_taxe WHERE fk_bulletin=".$obj_id_bulletin->rowid;
								$res_som_taxe  = $db->query($sql_som_taxe);
								if($res_som_taxe){
									$obj_som_taxe = $db->fetch_object($res_som_taxe);
									$somme_taxe += $obj_som_taxe->montant;
								}

								$sql_som_cotisation = "SELECT SUM(montant_employe) as som_empl, SUM(montant_employeur) as som_patro FROM ".MAIN_DB_PREFIX."bulletin_cotisation WHERE fk_bulletin=".$obj_id_bulletin->rowid;
								$res_som_cotisation  = $db->query($sql_som_cotisation);
								if($res_som_cotisation){
									$obj_som_cotisation = $db->fetch_object($res_som_cotisation);
									$somme_cotisation_employe += $obj_som_cotisation->som_empl;
									$somme_cotisation_employeur += $obj_som_cotisation->som_patro;
								}
								$a ++;
							}
							$db->free($res_id_bulletin);
							$somme_cotisation += $somme_cotisation_employe + $somme_cotisation_employeur;
							$total += $somme_taxe + $somme_cotisation;
							$tab_obj[0]->somme_cotisation = $somme_cotisation;
							$tab_obj[0]->somme_taxe = $somme_taxe;
							$tab_obj[0]->somme_cotisation_employe = $somme_cotisation_employe;
							$tab_obj[0]->somme_cotisation_employeur = $somme_cotisation_employeur;



						}
					}


		print "<tr class='impair'>";

				if(empty($info_bonus))
					print "<td ".$style." ><b>".$mois_tab[$i]."</b></td>";
				else{ 
					print "<td ".$style." ><b>".$mois_tab[$i]."</b>  ".$info_bonus."</td>";
				}


			if($une_fois){
				if(($i + 1) == $dernier_mois){ //Le denier mois généré
					if($etat_cloture == 'oui'){
						$suivant = true;
						print "<td ".$style.">".$nb_salarie."</td><td ".$style.">".apres_virgule($db, $id_societe, $tab_obj[0]->sal_brut?:0, 2)."</td><td ".$style.">".apres_virgule($db, $id_societe, $tab_obj[0]->sal_net?:0, 2)."</td><td ".$style.">".apres_virgule($db, $id_societe, $somme_taxe?:0, 2)."</td><td ".$style.">".apres_virgule($db, $id_societe, $somme_cotisation_employe, 2)."</td><td ".$style.">".apres_virgule($db, $id_societe, $somme_cotisation_employeur, 2)."</td>";
						print "<td ".$style." ><button class='button' disabled>Generer</button>";
						if($user->rights->paiementsalaire->salarie->voirDocument)
							print "<a style='text-decoration : none;' title='Voir' href='".$_SERVER['PHP_SELF']."?mainmenu=paiementsalaire&leftmenu=societe&id_convention=".$id_convention."&id_societe=".$id_societe."&action=voir&annee=".$annee_rechercher."&mois=".($i + 1)."'><span class='fa fa-search-plus'></span></a>&nbsp; &nbsp;&nbsp;
							<a style='text-decoration : none;' title='Télécharger' href='".$_SERVER['PHP_SELF']."?mainmenu=paiementsalaire&leftmenu=societe&id_convention=".$id_convention."&id_societe=".$id_societe."&action=telecharger&annee=".$annee_rechercher."&mois=".($i + 1)."'><span class='fa fa-download'></span></a>&nbsp;&nbsp;
							<a href='./../doc/export.php?mainmenu=paiementsalaire&leftmenu=societe&id_convention=".$id_convention."&id_societe=".$id_societe."&action=voir&annee=".$annee_rechercher."&mois=".($i + 1)."&nom_soc=".$obj_soc->nom."&action=exporter'><span class='file-export'>".img_picto('Exporter', 'logout', 'class="paddingright pictofixedwidth valignmiddle"')."</span></a>&nbsp; &nbsp;&nbsp;";
						else
							print "<span class='fa fa-search-plus' style='color: gray'></span> &nbsp;&nbsp;&nbsp;
							<span class='fa fa-download' style='color: gray'> &nbsp;&nbsp;&nbsp;";
						print "Cloturé</td>";
					}else{//vu que c'est le dernier mois généré alors cloture=non
						print "<td ".$style.">".$nb_salarie."</td><td ".$style.">".apres_virgule($db, $id_societe, $tab_obj[0]->sal_brut?:0, 2)."</td><td ".$style.">".apres_virgule($db, $id_societe, $tab_obj[0]->sal_net?:0, 2)."</td><td ".$style.">".apres_virgule($db, $id_societe, $somme_taxe?:0, 2)."</td><td ".$style.">".apres_virgule($db, $id_societe, $somme_cotisation_employe, 2)."</td><td ".$style.">".apres_virgule($db, $id_societe, $somme_cotisation_employeur, 2)."</td>";
						print "<td ".$style." >";
						if($user->rights->paiementsalaire->societe->genererBulletin)
							print "<a style='text-decoration : none;' href='".$_SERVER['PHP_SELF']."?mainmenu=paiementsalaire&leftmenu=societe&id_societe=".$id_societe."&id_convention=".$id_convention."&action=generer&annee=".$annee_rechercher."&mois=".($i+1)."' id='button_generer'><button class='button' >Générer</button></a>";
						else
							print "<button class='button' disabled>Generer</button>";
			
						if($user->rights->paiementsalaire->salarie->voirDocument)
							print "<a style='text-decoration : none;' title='Voir' href='".$_SERVER['PHP_SELF']."?mainmenu=paiementsalaire&leftmenu=societe&id_convention=".$id_convention."&id_societe=".$id_societe."&action=voir&annee=".$annee_rechercher."&mois=".($i + 1)."'><span class='fa fa-search-plus'></span></a>&nbsp;&nbsp;
							<a style='text-decoration : none;' title='Télécharger' href='".$_SERVER['PHP_SELF']."?mainmenu=paiementsalaire&leftmenu=societe&id_convention=".$id_convention."&id_societe=".$id_societe."&action=telecharger&annee=".$annee_rechercher."&mois=".($i + 1)."'><span class='fa fa-download'></span> </a>&nbsp;&nbsp;
							<a href='./../doc/export.php?mainmenu=paiementsalaire&leftmenu=societe&id_convention=".$id_convention."&id_societe=".$id_societe."&action=voir&annee=".$annee_rechercher."&mois=".($i + 1)."&nom_soc=".$obj_soc->nom."&action=exporter'><span class='file-export'>".img_picto('Exporter', 'logout', 'class="paddingright pictofixedwidth valignmiddle"')."</span></a>";
						else
							print "<span class='fa fa-search-plus' style='color: gray'></span> &nbsp;&nbsp;&nbsp;
							<span class='fa fa-download' style='color: gray'> &nbsp;&nbsp;&nbsp;";
						if($user->rights->paiementsalaire->societe->genererBulletin)
							print "<a style='text-decoration : none;' href='".$_SERVER['PHP_SELF']."?mainmenu=paiementsalaire&leftmenu=societe&id_societe=".$id_societe."&id_convention=".$id_convention."&action=cloture&annee=".$annee_rechercher."&mois=".($i + 1)."' id='cloture'><button class='button' >Cloturer</button></a></td>";
						else print "N/A</td>";

					}
				}else if(($i + 1) < $dernier_mois){ //on affiche les valeurs (clotue = oui)

					print "<td ".$style.">".$nb_salarie."</td><td ".$style.">".apres_virgule($db, $id_societe, $tab_obj[0]->sal_brut?:0, 2)."</td><td ".$style.">".apres_virgule($db, $id_societe, $tab_obj[0]->sal_net?:0, 2)."</td><td ".$style.">".apres_virgule($db, $id_societe, $somme_taxe?:0, 2)."</td><td ".$style.">".apres_virgule($db, $id_societe, $somme_cotisation_employe, 2)."</td><td ".$style.">".apres_virgule($db, $id_societe, $somme_cotisation_employeur, 2)."</td>";
					print "<td ".$style." ><button class='button' disabled>Generer</button>";
					if($user->rights->paiementsalaire->salarie->voirDocument)
						print "<a style='text-decoration : none;' title='Voir' href='".$_SERVER['PHP_SELF']."?mainmenu=paiementsalaire&leftmenu=societe&id_convention=".$id_convention."&id_societe=".$id_societe."&action=voir&annee=".$annee_rechercher."&mois=".($i + 1)."'><span class='fa fa-search-plus'></span></a>&nbsp; &nbsp;&nbsp;
						<a style='text-decoration : none;' title='Télécharger' href='".$_SERVER['PHP_SELF']."?mainmenu=paiementsalaire&leftmenu=societe&id_convention=".$id_convention."&id_societe=".$id_societe."&action=telecharger&annee=".$annee_rechercher."&mois=".($i + 1)."'><span class='fa fa-download'></span></a>&nbsp;&nbsp;
						<a href='./../doc/export.php?mainmenu=paiementsalaire&leftmenu=societe&id_convention=".$id_convention."&id_societe=".$id_societe."&action=voir&annee=".$annee_rechercher."&mois=".($i + 1)."&nom_soc=".$obj_soc->nom."&action=exporter'><span class='file-export'>".img_picto('Exporter', 'logout', 'class="paddingright pictofixedwidth valignmiddle"')."</span></a>&nbsp; &nbsp;&nbsp;";
					else
						print "<span class='fa fa-search-plus' style='color: gray'></span> &nbsp;&nbsp;&nbsp;
						<span class='fa fa-download' style='color: gray'> &nbsp;&nbsp;&nbsp;";
					print "Cloturé</td>";

				}else if($suivant == true){ //on affiche ce mois avec le bouton générer actif
					$suivant = false;

					
					print "<td ".$style.">0</td><td ".$style.">".apres_virgule($db, $id_societe, 0, 2)."</td><td ".$style.">".apres_virgule($db, $id_societe, 0, 2)."</td><td ".$style.">".apres_virgule($db, $id_societe, 0, 2)."</td><td ".$style.">".apres_virgule($db, $id_societe, 0, 2)."</td><td ".$style.">".apres_virgule($db, $id_societe, 0, 2)."</td>";
					print "<td ".$style." >";
					if($user->rights->paiementsalaire->societe->genererBulletin)
						print "<a style='text-decoration : none;' href='".$_SERVER['PHP_SELF']."?mainmenu=paiementsalaire&leftmenu=societe&id_societe=".$id_societe."&id_convention=".$id_convention."&action=generer&annee=".$annee_rechercher."&mois=".($i+1)."' id='button_generer'><button class='button' >Générer</button></a>&nbsp;";
					else
						print "<button class='button' disabled>Generer</button>";
				
						print "<span class='fa fa-search-plus' style='color: gray'></span> &nbsp;&nbsp;&nbsp;
						<span class='fa fa-download' style='color: gray'> &nbsp;&nbsp;&nbsp;";
						print "N/A</td>";
				}else{//on affiche les case vide
					print "<h2 style='background-color: red'>".$db->error()."</h2>";
					print "<td ".$style.">0</td><td ".$style.">".apres_virgule($db, $id_societe, 0, 2)."</td><td ".$style.">".apres_virgule($db, $id_societe, 0, 0)."</td><td ".$style.">".apres_virgule($db, $id_societe, 0, 2)."</td><td ".$style.">".apres_virgule($db, $id_societe, 0, 2)."</td><td ".$style.">".apres_virgule($db, $id_societe, 0, 2)."</td>";
					print "<td ".$style." ><button class='button' disabled>Generer</button>
					<span class='fa fa-search-plus' style='color: gray'></span> &nbsp;&nbsp;&nbsp;
					<span class='fa fa-download' style='color: gray'> &nbsp;&nbsp;&nbsp;";
					print "N/A</td>";
				}

			}else{//On va automatiquement au mois courant
				if(($i + 1) == $mois_courant){
					
					print "<td ".$style.">0</td><td ".$style.">".apres_virgule($db, $id_societe, 0, 2)."</td><td ".$style.">".apres_virgule($db, $id_societe, 0, 2)."</td><td ".$style.">".apres_virgule($db, $id_societe, 0, 2)."</td><td ".$style.">".apres_virgule($db, $id_societe, 0, 2)."</td><td ".$style.">".apres_virgule($db, $id_societe, 0, 2)."</td>";
					print "<td ".$style." >";
					if($user->rights->paiementsalaire->societe->genererBulletin)
						print "<a style='text-decoration : none;' href='".$_SERVER['PHP_SELF']."?mainmenu=paiementsalaire&leftmenu=societe&id_societe=".$id_societe."&id_convention=".$id_convention."&action=generer&annee=".$annee_rechercher."&mois=".($i+1)."' id='button_generer'><button class='button' >Générer</button></a>&nbsp;";
					else
						print "<button class='button' disabled>Generer</button>";
				
						print "<span class='fa fa-search-plus' style='color: gray'></span> &nbsp;&nbsp;&nbsp;
						<span class='fa fa-download' style='color: gray'> &nbsp;&nbsp;&nbsp;";
						print "N/A</td>";
				}else{//il n y a eu aucune génération et le mois n'est pas égal au mois en cours (vide)
					print "<h2 style='background-color: red'>".$db->error()."</h2>";
					
					print "<td ".$style.">0</td><td ".$style.">".apres_virgule($db, $id_societe, 0, 2)."</td><td ".$style.">".apres_virgule($db, $id_societe, 0, 0)."</td><td ".$style.">".apres_virgule($db, $id_societe, 0, 2)."</td><td ".$style.">".apres_virgule($db, $id_societe, 0, 2)."</td><td ".$style.">".apres_virgule($db, $id_societe, 0, 2)."</td>";
					print "<td ".$style." ><button class='button' disabled>Generer</button>
					<span class='fa fa-search-plus' style='color: gray'></span> &nbsp;&nbsp;&nbsp;
					<span class='fa fa-download' style='color: gray'> &nbsp;&nbsp;&nbsp;";
					print "N/A</td>";
				}

			}

			print "</tr>";

		}







}else{

						$gen = true;
						for ($i=0; $i < count($mois_tab); $i++) {
							//Vérification d'un bulletin bonus (complément salaire)
							$sql_bonus_bulletin = "SELECT nom_bonus FROM ".MAIN_DB_PREFIX."bulletin_bonus WHERE annee=".$annee_rechercher." AND mois=".($i + 1)." AND fk_societe=".$id_societe;
							$res_bonus_bulletin  = $db->query($sql_bonus_bulletin);
							$num_bull_bon = $db->num_rows($res_bonus_bulletin);
							$info_bonus = '';
							$style = '';
							if($num_bull_bon > 0){
								$obj_bonus_bulletin = $db->fetch_object($res_bonus_bulletin);
								$titre = 'Il existe un Complément salaire "'.$obj_bonus_bulletin->nom_bonus.'" pour ce mois. Cliquez pour voir';
								$info_bonus = "&nbsp;&nbsp;<div style='display: inline; padding-bottom: 100px;'><a href='./bonus_paies.php?mainmenu=paiementsalaire&leftmenu=societe&id_convention=".$id_convention."&id_societe=".$id_societe."'><img src='./icon/down_arrow.png' onClick=cacher('bonus".$i."') width='20' height='15' title='".$titre."'></a></div>";
								$style = 'style="border : 0px;"';
							}
	
								print "<tr class='impair'>";
							
									$sql_verif = "SELECT rowid, cloture FROM ".MAIN_DB_PREFIX."bulletin WHERE annee=".$annee_rechercher." AND mois=".($i + 1)." AND fk_societe=".$id_societe;
									$res_verif = $db->query($sql_verif);
									if($res_verif){
										$nb_salarie = $db->num_rows($res_verif);
										if($nb_salarie > 0){
												$une_fois = true;
												$obj_verif = $db->fetch_object($res_verif);
												$sql_som_salaire = "SELECT SUM(salaire_brut) as sal_brut, SUM(net_payer) as sal_net FROM ".MAIN_DB_PREFIX."bulletin WHERE annee=".$annee_rechercher." AND mois=".($i + 1)." AND fk_societe=".$id_societe;
												$res_som_salaire  = $db->query($sql_som_salaire);
												$obj_som_salaire = $db->fetch_object($res_som_salaire);
	
												//$total += $obj_som_salaire->sal_brut + $obj_som_salaire->sal_net;
	
											if(($obj_verif->cloture=="oui")){
												$total = 0;
												$a = 0;
												$somme_taxe = 0;
												$somme_cotisation = 0;
												$somme_cotisation_employe = 0;
												$somme_cotisation_employeur = 0;
												$sql_id_bulletin = "SELECT rowid FROM ".MAIN_DB_PREFIX."bulletin WHERE annee=".$annee_rechercher." AND mois=".($i + 1)." AND fk_societe=".$id_societe;
												$res_id_bulletin  = $db->query($sql_id_bulletin);
												$num_k = $db->num_rows($res_id_bulletin);
												while ($a < $num_k){
													$obj_id_bulletin = $db->fetch_object($res_id_bulletin);
													$sql_som_taxe = "SELECT SUM(montant) as montant FROM ".MAIN_DB_PREFIX."bulletin_taxe WHERE fk_bulletin=".$obj_id_bulletin->rowid;
													$res_som_taxe  = $db->query($sql_som_taxe);
													if($res_som_taxe){
														$obj_som_taxe = $db->fetch_object($res_som_taxe);
														$somme_taxe += $obj_som_taxe->montant;
													}
	
													$sql_som_cotisation = "SELECT SUM(montant_employe) as som_empl, SUM(montant_employeur) as som_patro FROM ".MAIN_DB_PREFIX."bulletin_cotisation WHERE fk_bulletin=".$obj_id_bulletin->rowid;
													$res_som_cotisation  = $db->query($sql_som_cotisation);
													if($res_som_cotisation){
														$obj_som_cotisation = $db->fetch_object($res_som_cotisation);
														$somme_cotisation_employe += $obj_som_cotisation->som_empl;
														$somme_cotisation_employeur += $obj_som_cotisation->som_patro;
													}
													$a ++;
												}
												$db->free($res_id_bulletin);
												$somme_cotisation += $somme_cotisation_employe + $somme_cotisation_employeur;
												$total += $somme_taxe + $somme_cotisation;
	
												if(empty($info_bonus))
													print "<td ".$style." ><b>".$mois_tab[$i]."</b></td>";
												else{ 
													print "<td ".$style." ><b>".$mois_tab[$i]."</b>  ".$info_bonus."</td>";
												}
												
	
												print "<td ".$style.">".$nb_salarie."</td><td ".$style.">".apres_virgule($db, $id_societe, $obj_som_salaire->sal_brut?:0, 2)."</td><td ".$style.">".apres_virgule($db, $id_societe, $obj_som_salaire->sal_net?:0, 2)."</td><td ".$style.">".apres_virgule($db, $id_societe, $somme_taxe?:0, 2)."</td><td ".$style.">".apres_virgule($db, $id_societe, $somme_cotisation_employe, 2)."</td><td ".$style.">".apres_virgule($db, $id_societe, $somme_cotisation_employeur, 2)."</td>";
												print "<td ".$style." ><button class='button' disabled>Generer</button>";
												if($user->rights->paiementsalaire->salarie->voirDocument)
													print "<a style='text-decoration : none;' title='Voir' href='".$_SERVER['PHP_SELF']."?mainmenu=paiementsalaire&leftmenu=societe&id_convention=".$id_convention."&id_societe=".$id_societe."&action=voir&annee=".$annee_rechercher."&mois=".($i + 1)."'><span class='fa fa-search-plus'></span></a>&nbsp; &nbsp;&nbsp;
													<a style='text-decoration : none;' title='Télécharger' href='".$_SERVER['PHP_SELF']."?mainmenu=paiementsalaire&leftmenu=societe&id_convention=".$id_convention."&id_societe=".$id_societe."&action=telecharger&annee=".$annee_rechercher."&mois=".($i + 1)."'><span class='fa fa-download'></span></a>&nbsp;&nbsp;
													<a href='./../doc/export.php?mainmenu=paiementsalaire&leftmenu=societe&id_convention=".$id_convention."&id_societe=".$id_societe."&action=voir&annee=".$annee_rechercher."&mois=".($i + 1)."&nom_soc=".$obj_soc->nom."&action=exporter'><span class='file-export'>".img_picto('Exporter', 'logout', 'class="paddingright pictofixedwidth valignmiddle"')."</span></a>&nbsp; &nbsp;&nbsp;";
												else
													print "<span class='fa fa-search-plus' style='color: gray'></span> &nbsp;&nbsp;&nbsp;
													<span class='fa fa-download' style='color: gray'> &nbsp;&nbsp;&nbsp;";
												print "Cloturé</td>";
												
	
												$precedent = false;
												$gen = true;
											}else if(($obj_verif->cloture=="non")){
													$total = 0;
													$a = 0;
													$somme_taxe = 0;
													$somme_cotisation = 0;
													$somme_cotisation_employe = 0;
													$somme_cotisation_employeur = 0;
													$sql_id_bulletin = "SELECT rowid FROM ".MAIN_DB_PREFIX."bulletin WHERE annee=".$annee_rechercher." AND mois=".($i + 1)." AND fk_societe=".$id_societe;
													$res_id_bulletin  = $db->query($sql_id_bulletin);
													$num_k = $db->num_rows($res_id_bulletin);
													while ($a < $num_k){
														$obj_id_bulletin = $db->fetch_object($res_id_bulletin);
														$sql_som_taxe = "SELECT SUM(montant) as montant FROM ".MAIN_DB_PREFIX."bulletin_taxe WHERE fk_bulletin=".$obj_id_bulletin->rowid;
														$res_som_taxe  = $db->query($sql_som_taxe);
														if($res_som_taxe){
															$obj_som_taxe = $db->fetch_object($res_som_taxe);
															$somme_taxe += $obj_som_taxe->montant;
														}
	
														$sql_som_cotisation = "SELECT SUM(montant_employe) as som_empl, SUM(montant_employeur) as som_patro FROM ".MAIN_DB_PREFIX."bulletin_cotisation WHERE fk_bulletin=".$obj_id_bulletin->rowid;
														$res_som_cotisation  = $db->query($sql_som_cotisation);
														if($res_som_cotisation){
															$obj_som_cotisation = $db->fetch_object($res_som_cotisation);
															$somme_cotisation_employe += $obj_som_cotisation->som_empl;
															$somme_cotisation_employeur += $obj_som_cotisation->som_patro;
														}
														$a ++;
													}
													$db->free($res_id_bulletin);
	
													$somme_cotisation += $somme_cotisation_employe + $somme_cotisation_employeur;
													$total += $somme_taxe + $somme_cotisation;
													if(empty($info_bonus))
														print "<td ".$style." ><b>".$mois_tab[$i]."</b></td>";
													else{ 
														print "<td ".$style." ><b>".$mois_tab[$i]."</b>  ".$info_bonus."</td>";
													}
														//print "<td ".$style." ><b>".$mois_tab[$i]." ".$info_bonus."</b></td>";
														print "<td ".$style.">".$nb_salarie."</td><td ".$style.">".apres_virgule($db, $id_societe, $obj_som_salaire->sal_brut?:0, 2)."</td><td ".$style.">".apres_virgule($db, $id_societe, $obj_som_salaire->sal_net?:0, 2)."</td><td ".$style.">".apres_virgule($db, $id_societe, $somme_taxe?:0, 2)."</td><td ".$style.">".apres_virgule($db, $id_societe, $somme_cotisation_employe, 2)."</td><td ".$style.">".apres_virgule($db, $id_societe, $somme_cotisation_employeur, 2)."</td>";
														print "<td ".$style." >";
														if($user->rights->paiementsalaire->societe->genererBulletin)
															print "<a style='text-decoration : none;' href='".$_SERVER['PHP_SELF']."?mainmenu=paiementsalaire&leftmenu=societe&id_societe=".$id_societe."&id_convention=".$id_convention."&action=generer&annee=".$annee_rechercher."&mois=".($i+1)."' id='button_generer'><button class='button' >Générer</button></a>";
														else
															print "<button class='button' disabled>Generer</button>";
	
														if($user->rights->paiementsalaire->salarie->voirDocument)
															print "<a style='text-decoration : none;' title='Voir' href='".$_SERVER['PHP_SELF']."?mainmenu=paiementsalaire&leftmenu=societe&id_convention=".$id_convention."&id_societe=".$id_societe."&action=voir&annee=".$annee_rechercher."&mois=".($i + 1)."'><span class='fa fa-search-plus'></span></a>&nbsp;&nbsp;
															<a style='text-decoration : none;' title='Télécharger' href='".$_SERVER['PHP_SELF']."?mainmenu=paiementsalaire&leftmenu=societe&id_convention=".$id_convention."&id_societe=".$id_societe."&action=telecharger&annee=".$annee_rechercher."&mois=".($i + 1)."'><span class='fa fa-download'></span> </a>&nbsp;&nbsp;
															<a href='./../doc/export.php?mainmenu=paiementsalaire&leftmenu=societe&id_convention=".$id_convention."&id_societe=".$id_societe."&action=voir&annee=".$annee_rechercher."&mois=".($i + 1)."&nom_soc=".$obj_soc->nom."&action=exporter'><span class='file-export'>".img_picto('Exporter', 'logout', 'class="paddingright pictofixedwidth valignmiddle"')."</span></a>";
														else
															print "<span class='fa fa-search-plus' style='color: gray'></span> &nbsp;&nbsp;&nbsp;
															<span class='fa fa-download' style='color: gray'> &nbsp;&nbsp;&nbsp;";
														if($user->rights->paiementsalaire->societe->genererBulletin)
															print "<a style='text-decoration : none;' href='".$_SERVER['PHP_SELF']."?mainmenu=paiementsalaire&leftmenu=societe&id_societe=".$id_societe."&id_convention=".$id_convention."&action=cloture&annee=".$annee_rechercher."&mois=".($i + 1)."' id='cloture'><button class='button' >Cloturer</button></a></td>";
														else print "N/A</td>";
	
														//Vérification d'un bulletin bonus (complément salaire)
														$gen = true;

												}
											}else{
												if($gen == false){
												print "<td ".$style." ><b>".$mois_tab[$i]."</b></td>";
													print "<td ".$style.">0</td><td ".$style.">".apres_virgule($db, $id_societe, 0, 2)."</td><td ".$style.">".apres_virgule($db, $id_societe, 0, 2)."</td><td ".$style.">".apres_virgule($db, $id_societe, 0, 2)."</td><td ".$style.">".apres_virgule($db, $id_societe, 0, 2)."</td><td ".$style.">".apres_virgule($db, $id_societe, 0, 2)."</td>";
													print "<td ".$style." >";
													if($user->rights->paiementsalaire->societe->genererBulletin)
														print "<a style='text-decoration : none;' href='".$_SERVER['PHP_SELF']."?mainmenu=paiementsalaire&leftmenu=societe&id_societe=".$id_societe."&id_convention=".$id_convention."&action=generer&annee=".$annee_rechercher."&mois=".($i+1)."' id='button_generer'><button class='button' >Générer</button></a>&nbsp;";
													else
														print "<button class='button' disabled>Generer</button>";

														print "<span class='fa fa-search-plus' style='color: gray'></span> &nbsp;&nbsp;&nbsp;
														<span class='fa fa-download' style='color: gray'> &nbsp;&nbsp;&nbsp;";
														print "N/A</td>";
													$gen = true;
												}else{
													print "<h2 style='background-color: red'>".$db->error()."</h2>";
													print "<td ".$style." ><b>".$mois_tab[$i]."</b></td>";
													print "<td ".$style.">0</td><td ".$style.">".apres_virgule($db, $id_societe, 0, 2)."</td><td ".$style.">".apres_virgule($db, $id_societe, 0, 0)."</td><td ".$style.">".apres_virgule($db, $id_societe, 0, 2)."</td><td ".$style.">".apres_virgule($db, $id_societe, 0, 2)."</td><td ".$style.">".apres_virgule($db, $id_societe, 0, 2)."</td>";
													print "<td ".$style." ><button class='button' disabled>Generer</button>
													<span class='fa fa-search-plus' style='color: gray'></span> &nbsp;&nbsp;&nbsp;
													<span class='fa fa-download' style='color: gray'> &nbsp;&nbsp;&nbsp;";
													print "N/A</td>";
												}
											}
										}else{
												print "<h2 style='background-color: red'>".$db->error()."</h2>";
												print "<td ".$style." ><b>".$mois_tab[$i]."</b></td>";
												print "<td ".$style.">0</td><td ".$style.">".apres_virgule($db, $id_societe, 0, 2)."</td><td ".$style.">".apres_virgule($db, $id_societe, 0, 0)."</td><td ".$style.">".apres_virgule($db, $id_societe, 0, 2)."</td><td ".$style.">".apres_virgule($db, $id_societe, 0, 2)."</td><td ".$style.">".apres_virgule($db, $id_societe, 0, 2)."</td>";
												print "<td ".$style." ><button class='button' disabled>Generer</button>
												<span class='fa fa-search-plus' style='color: gray'></span> &nbsp;&nbsp;&nbsp;
												<span class='fa fa-download' style='color: gray'> &nbsp;&nbsp;&nbsp;";
												print "N/A</td>";
											}
	
								}

					}
					print "</tbody>";
					print "</table>";
				
				
					//Csript pour voir ou cacher les informations des bulletin bonus
					/*print '<script>
					function cacher(e) {
						var cache1 = document.getElementById(e);

						if(cache1.style.display=="none"){
							cache1.style.display="block";
						}else{
							cache1.style.display="none";
						}  
					}
				</script>';	*/		//--------------------------------------------------------------------------------------------------------------------------------------
			}
			//----------------------------------------------------------------------------------------------------------------------------------------

	//Si la société n'a pas de salarié
		}else {
			print "<h2 style='align:center;'>Cette sociétée n'a aucun employé!";
		}
		$db->close();

	}else{
		print "<h2>Veuillez affecter une <b>convention</b> à cette société</h2>";
		}
}else{
	print "<h2 style='align:center;'>Vous n'avez pas la permission de voir cette page!";

}

function apres_virgule($db, $id_societe, $valeur, $decalage){
    $sep = ".";
    $decalage = 2;
    $reglage_bulletin = "SELECT separateur, decalage FROM ".MAIN_DB_PREFIX."reglage_bulletin WHERE fk_societe=".$id_societe;
      $result_reglage_bulletin = $db->query($reglage_bulletin);
      if($db->num_rows($result_reglage_bulletin) > 0){
        $obj_reglage_bulletin = $db->fetch_object($result_reglage_bulletin);
        $sep = $obj_reglage_bulletin->separateur;
        $decalage = $obj_reglage_bulletin->decalage;
      }
    return number_format($valeur, $decalage, $sep, ' ');
  }
//$confirmation = "Voulez vous gernerer les bulettins de paies pour l ensemble des salariés pour le mois de ".$mois_tab[$mois-1]." ? ce processus peut prendre plusieurs minutes selon le nombre de salarié(e)s.";
	  print "<script>
	  var button_generer = document.getElementById('button_generer');
	  button_generer.addEventListener('click', myFunction);

	  var ancien_button_generer = document.getElementById('ancien_button_generer');
	  ancien_button_generer.addEventListener('click', ancien_myFunction);



	  let mois_table = [' janvier ',' février ',' mars ',' avril ',' mai ',' juin ',' juillet ',' août ',' septembre ',' octobre ',' novembre ',' décembre '];
		function myFunction(){
			var date = new Date;
			var result = confirm('Voulez vous gernerer les bulettins de paies pour l ensemble des salariés pour le mois de '+mois_table[date.getMonth()]+' ? ce processus peut prendre plusieurs minutes selon le nombre de salarié(e)s.');
			if(result)
				button_generer.setAttribute('href', defaut);
			else
				button_generer.setAttribute('href', '#');


		}//e.preventdefault

		function ancien_myFunction(){
			var date = new Date;
			var result = confirm('Voulez vous modifié les bulletins deu'+mois_table[date.getMonth()-1]+' ? ce processus peut prendre plusieurs minutes selon le nombre de salarié(e)s.');
			if(result)
				ancien_button_generer.setAttribute('href', defaut);
			else
				ancien_button_generer.setAttribute('href', '#');


		}
		</script>";
		print "<style>
			a{text-decoration : none;

			}
		</style>";

	  if(!empty($message))
		print "<script>
		$.jnotify('".$message."', {delay : 5000, fadeSpeed: 500});
		</script>";					


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