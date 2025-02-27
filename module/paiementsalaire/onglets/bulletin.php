<?php
/* Copyright (C) 2001-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2013 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2015 Regis Houssin        <regis.houssin@inodbox.com>
 * Copyright (C) 2015-2020 Juanjo Menent	<jmenent@2byte.es>
 * Copyright (C) 2015      Jean-François Ferry	<jfefe@aternatik.fr>
 * Copyright (C) 2015      Raphaël Doursenaud   <rdoursenaud@gpcsolutions.fr>
 * Copyright (C) 2016      Marcos García        <marcosgdf@gmail.com>
 * Copyright (C) 2019      Nicolas ZABOURI      <info@inovea-conseil.com>
 * Copyright (C) 2020      Tobias Sekan         <tobias.sekan@startmail.com>
 * Copyright (C) 2020      Josep Lluís Amador   <joseplluis@lliuretic.cat>
 * Copyright (C) 2021      Frédéric France		<frederic.france@netlogic.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 *	\file       htdocs/compta/index.php
 *	\ingroup    compta
 *	\brief      Main page of accountancy area
 */

require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/custom/paiementsalaire/lib/paiementsalaire.lib.php';

//-------------------------------------------------------------------------------------------
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
    $localkeydays = 15;
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
	if(!empty($results['localkey']))
    	file_put_contents(DOL_DOCUMENT_ROOT.'/paiementsalaire/onglets/local.txt', $results['localkey']);

		//Prendre le nombre de salariés
	$nb_sal = explode('=', $results["configoptions"]);//$results["configoptions"] = Salaries=25
	if($nb_sal[0]=="Salaries" && is_int((int)$nb_sal[1]))
		$nombre_salarie_licence = $nb_sal[1];
		
} else {
    $info =  "Votre licence est : ".$results['status'];
}

//-----------------------------------------------------------------------------------------------------------------------------------------------------

llxHeader("", "Paiement | Salaire");
//info sur la licence
if(!empty($info))
	print '<mark><h3 id="avertissement" style="color:red;">'.$info.'</h3></mark>';

//Titre 
print load_fiche_titre($langs->trans("Bulletin de paye"), '', '');
$fk_user = GETPOST("id","int");
$id_societe = GETPOST("id_societe","int");
$fk_salarie = GETPOST("fk_salarie", "int");
$id_convention = GETPOST("id_convention","int");
$annee_rechercher = GETPOST("annee_rechercher", "int");
$id_bull = 0;

$head = salaire_Head($fk_salarie, $fk_user, $id_societe, $id_convention);
	print dol_get_fiche_head($head, 'bulletin', "", -1, '');
	
if($user->id !=1 && $user->id != $fk_user && !$user->rights->paiementsalaire->salarie->voirBulletin){
	print "<h2> Vous n\'avez pas ce droit </h2>";
}else{

	if(empty($fk_salarie)){
		print "<mark><strong>Il n'a pas encore de fk_salarie</strong></mark><br>";
		print "Page non Disponible";
	}else{
		$obj_soc = prepare_objet_entete($fk_salarie, $fk_user, $db, $id_societe, $id_convention);
		entete_societe($obj_soc, 'societe');
		print '<hr>';

		if(empty($annee_rechercher))
			$annee_rechercher = date("Y");

			$modele_bulletin = "SELECT rowid FROM ".MAIN_DB_PREFIX."modele_bulletin WHERE actif=1";
			$result_modele_bulletin = $db->query($modele_bulletin);//= $db->query($covSql);
			if($result_modele_bulletin){
				$obj_modele_bulletin = $db->fetch_object($result_modele_bulletin);
				$id_bull = $obj_modele_bulletin->rowid;
			}
			if(empty($id_bull))
				$id_bull = 1;
		$mois_tab = array(" Janvier "," Février "," Mars "," Avril "," Mai "," Juin "," Juillet "," Août "," Septembre "," Octobre "," novembre "," Décembre ");

				print "<h2 style='position: justifie'>Les bulletins de paye de l'année ".$annee_rechercher;
				print "<div style='float: right; margin-right:'30px'>";
				print '<form name="add" method="POST" action="'.$_SERVER['PHP_SELF'].'?mainmenu=paiementsalaire&leftmenu=salarie&id='.$fk_user.'&fk_salarie='.$fk_salarie.'&id_societe='.$id_societe.'&id_convention='.$id_convention.'">';
				print '<input type="hidden" name="token" value="'.newToken().'">';
				print '<input type="hidden" name="action" value="save_edit">';
				$info = "Les années affichées sont les années auquelles ce salarié à au moins un bulletin";
				print info_admin($langs->trans($info), 1)."<select name='annee_rechercher' id='annee_rechercher'><option value='0'></option>";
				//affichage de la zone de recherche année
				//les valeurs son uniquement les années au cours desquelles le salarié a au moins un bulletin
					$sql_verif = "SELECT DISTINCT annee FROM ".MAIN_DB_PREFIX."bulletin WHERE fk_salarie=".$fk_salarie;
					$res_verif = $db->query($sql_verif);
						if($res_verif){
							$i = 0;
							$nb = $db->num_rows($res_verif);
							$annee_tab = array();
							while($i < $nb){
								$obj_verif = $db->fetch_object($res_verif);
								$annee_tab[] = $obj_verif->annee;
								if($obj_verif->annee == $annee_rechercher)
									print "<option value='".$obj_verif->annee."' selected >".$obj_verif->annee."</option>";
								else 
									print "<option value='".$obj_verif->annee."'>".$obj_verif->annee."</option>";

								$i ++;
							}

							if($nb == 0){
								print "<option value='".date("Y")."' selected >".date("Y")."</option>";
							}elseif(!in_array(date("Y"), $annee_tab))
								if($annee_rechercher == date("Y"))
									print "<option value='".date("Y")."' selected>".date("Y")."</option>";
								else print "<option value='".date("Y")."' >".date("Y")."</option>";
						}
						print '<input class="button" type="submit" value="RECHERCHER">';
						print'</form>';
				print "</div></h2>";

		//partie d'affichage du tableau
		print "</div>";
				print "<table class='tagtable liste'>";
					print "<thead>";
					print "<tr class='liste_titre'><th align='center'>Mois</th>";	
					print "<th>Salaire brut</th>";
					print "<th>Salaire net</th>";
					print "<th>I.T.S</th>";
					print "<th>Total Cotisations</th>";
					print "<th>Total Retenues</th>";
					print "<th align='center' >Opérations</tr>";
					print "</thead>";
					print "<tbody>";
					for ($i=0; $i < count($mois_tab); $i++) { 
						$total = 0;
						print "<tr class='impair'>";
							$sql_verif = "SELECT rowid FROM ".MAIN_DB_PREFIX."bulletin WHERE annee=".$annee_rechercher." AND mois=".($i + 1)." AND fk_salarie=".$fk_salarie;
							$res_verif = $db->query($sql_verif);
							if($res_verif){
								$nb_salarie = $db->num_rows($res_verif);
								$obj_verif = $db->fetch_object($res_verif);

								$sql_som_salaire = "SELECT salaire_brut as sal_brut, net_payer as sal_net FROM ".MAIN_DB_PREFIX."bulletin WHERE annee=".$annee_rechercher." AND mois=".($i + 1)." AND fk_salarie=".$fk_salarie;
								$res_som_salaire  = $db->query($sql_som_salaire);
								$obj_som_salaire = $db->fetch_object($res_som_salaire);
								//Si le modèle de base est actif rowid = 1
								if($id_bull == 1){
									if($annee_rechercher == date("Y") && ($i+1) == date("m")) {
										$info = "Vous pourrez télécharger le bulletin de paie une fois le mois cloturé";
										if(verification($db, $fk_salarie, $fk_user, $id_convention)){
											if($licence == "Active")
												print "<td ><b>".$mois_tab[$i]."</b></td>
												<td align='center' colspan='6'>".info_admin($langs->trans($info), 1)."<a class='button' target='_blank' href='../doc/bulletin.php?id_societe=".$id_societe."&fk_user=".$fk_user."&fk_salarie=".$fk_salarie."&id_convention=".$id_convention."&mois=".($i+1)."&annee=".$annee_rechercher."&action=no_save'>Voir un Aperçu du Bulletin</a>";
											else print "<td ><b>".$mois_tab[$i]."</b></td>
												<td align='center' colspan='6'>".info_admin("Impossible d'afficher les bulletins", 1)."<button>".$results['status']."</button>";

										}else{
											if($licence == "Active")
												print "<td ><b>".$mois_tab[$i]."</b></td>
												<td align='center' colspan='6'>".info_admin($langs->trans($info), 1)."<a class='button' target='_blank' href='./salarie_verification.php?mainmenu=paiementsalaire&leftmenu=salarie&id_societe=".$id_societe."&fk_salarie=".$fk_salarie."&id_convention=".$id_convention."&id=".$fk_user."' >Voir Onglet vérification</a>";
											else print "<td ><b>".$mois_tab[$i]."</b></td>
											<td align='center' colspan='6'>".info_admin("Impossible d'afficher les bulletins", 1)."<button>".$results['status']."</button>";
										}
			
									}elseif(($obj_verif->rowid)){
										$a = 0;
										$somme_taxe = 0;
										$somme_cotisation = 0;
										$sql_som_taxe = "SELECT montant FROM ".MAIN_DB_PREFIX."bulletin_taxe WHERE fk_bulletin=".$obj_verif->rowid;
										$res_som_taxe  = $db->query($sql_som_taxe);
										if($res_som_taxe){
											$obj_som_taxe = $db->fetch_object($res_som_taxe);
											$somme_taxe += $obj_som_taxe->montant;
										}

										$sql_som_cotisation = "SELECT SUM(montant_employe) as som_cotisation FROM ".MAIN_DB_PREFIX."bulletin_cotisation WHERE fk_bulletin=".$obj_verif->rowid;
										$res_som_cotisation  = $db->query($sql_som_cotisation);
										if($res_som_cotisation){
											$obj_som_cotisation = $db->fetch_object($res_som_cotisation);
											$somme_cotisation += $obj_som_cotisation->som_cotisation;
										}
										
										$total += $somme_taxe + $somme_cotisation;
										print "<td ><b>".$mois_tab[$i]."</b></td>";
										print "<td>".apres_virgule($db, $id_societe, $obj_som_salaire->sal_brut?:0)."</td><td>".apres_virgule($db, $id_societe, $obj_som_salaire->sal_net?:0)."</td><td>".apres_virgule($db, $id_societe, $somme_taxe)."</td><td>".apres_virgule($db, $id_societe, $somme_cotisation)."</td><td>".apres_virgule($db, $id_societe, $total)."</td>";
										print "<td align='center'><a style='text-decoration : none;' title='Voir' target='_blank' href='../doc/bulletin.php?id_societe=".$id_societe."&fk_user=".$fk_user."&fk_salarie=".$fk_salarie."&id_convention=".$id_convention."&mois=".($i +1)."&annee=".$annee_rechercher."&action=voir'><span class='fa fa-search-plus'></span>&nbsp; &nbsp;</a>&nbsp;
										<a style='text-decoration : none;' title='Télécharger' target='_blank' href='../doc/bulletin.php?id_societe=".$id_societe."&fk_user=".$fk_user."&fk_salarie=".$fk_salarie."&id_convention=".$id_convention."&mois=".($i +1)."&annee=".$annee_rechercher."&action=telecharger'><span class='fa fa-download'></span> &nbsp;</a>&nbsp;";
										print "</td>";
									}else if (($i+1) < $mois_courant){
										print "<td ><b>".$mois_tab[$i]."</b></td>";
										print "<td>".apres_virgule($db, $id_societe, 0)."</td><td>".apres_virgule($db, $id_societe, 0)."</td><td>".apres_virgule($db, $id_societe, 0)."</td><td>".apres_virgule($db, $id_societe, 0)."</td><td>".apres_virgule($db, $id_societe, 0)."</td>";
										print "<td align='center'>
										<span class='fa fa-search-plus' style='color: gray'></span> &nbsp;&nbsp;&nbsp;
										<span class='fa fa-download' style='color: gray'> &nbsp;&nbsp;&nbsp;";
										print "</td>";
									}else{
										print "<td ><b>".$mois_tab[$i]."</b></td>";
										print "<td>".apres_virgule($db, $id_societe, 0)."</td><td>".apres_virgule($db, $id_societe, 0)."</td><td>".apres_virgule($db, $id_societe, 0)."</td><td>".apres_virgule($db, $id_societe, 0)."</td><td>".apres_virgule($db, $id_societe, 0)."</td>";
										print "<td align='center'>
										<span class='fa fa-search-plus' style='color: gray'>&nbsp;&nbsp;
										<span class='fa fa-download'></span> &nbsp;&nbsp;";
										print "</td>";
									}

								//Si le modèle moyen est actif
								}elseif($id_bull == 2){
									if($annee_rechercher == date("Y") && ($i+1) == date("m")) {
										$info = "Vous pourrez télécharger le bulletin de paie une fois le mois cloturé";
										if(verification($db, $fk_salarie, $fk_user, $id_convention)){
											if($licence == "Active")
												print "<td ><b>".$mois_tab[$i]."</b></td>
												<td align='center' colspan='6'>".info_admin($langs->trans($info), 1)."<a class='button' target='_blank' href='../doc/modele_moyen/bulletin.php?id_societe=".$id_societe."&fk_user=".$fk_user."&fk_salarie=".$fk_salarie."&id_convention=".$id_convention."&mois=".($i+1)."&annee=".$annee_rechercher."&action=no_save'>Voir un Aperçu du Bulletin</a>";
											else print "<td ><b>".$mois_tab[$i]."</b></td>
												<td align='center' colspan='6'>".info_admin("Impossible d'afficher les bulletins", 1)."<button>".$results['status']."</button>";
										}else{
											if($licence == "Active")
												print "<td ><b>".$mois_tab[$i]."</b></td>
												<td align='center' colspan='6'>".info_admin($langs->trans($info), 1)."<a class='button' target='_blank' href='./salarie_verification.php?mainmenu=paiementsalaire&leftmenu=salarie&id_societe=".$id_societe."&fk_salarie=".$fk_salarie."&id_convention=".$id_convention."&id=".$fk_user."' >Voir Onglet vérification</a>";
											else print "<td ><b>".$mois_tab[$i]."</b></td>
												<td align='center' colspan='6'>".info_admin("Impossible d'afficher les bulletins", 1)."<button>".$results['status']."</button>";
										}

									}elseif(($obj_verif->rowid)){
										$a = 0;
										$somme_taxe = 0;
										$somme_cotisation = 0;
										$sql_som_taxe = "SELECT montant FROM ".MAIN_DB_PREFIX."bulletin_taxe WHERE fk_bulletin=".$obj_verif->rowid;
										$res_som_taxe  = $db->query($sql_som_taxe);
										if($res_som_taxe){
											$obj_som_taxe = $db->fetch_object($res_som_taxe);
											$somme_taxe += $obj_som_taxe->montant;
										}

										$sql_som_cotisation = "SELECT SUM(montant_employe) as som_cotisation FROM ".MAIN_DB_PREFIX."bulletin_cotisation WHERE fk_bulletin=".$obj_verif->rowid;
										$res_som_cotisation  = $db->query($sql_som_cotisation);
										if($res_som_cotisation){
											$obj_som_cotisation = $db->fetch_object($res_som_cotisation);
											$somme_cotisation += $obj_som_cotisation->som_cotisation;
										}
										
										$total += $somme_taxe + $somme_cotisation;
										print "<td ><b>".$mois_tab[$i]."</b></td>";
										print "<td>".apres_virgule($db, $id_societe, $obj_som_salaire->sal_brut?:0)."</td><td>".apres_virgule($db, $id_societe, $obj_som_salaire->sal_net?:0)."</td><td>".apres_virgule($db, $id_societe, $somme_taxe)."</td><td>".apres_virgule($db, $id_societe, $somme_cotisation)."</td><td>".apres_virgule($db, $id_societe, $total)."</td>";
										print "<td align='center'><a style='text-decoration : none;' title='Voir' target='_blank' href='../doc/modele_moyen/bulletin.php?id_societe=".$id_societe."&fk_user=".$fk_user."&fk_salarie=".$fk_salarie."&id_convention=".$id_convention."&mois=".($i +1)."&annee=".$annee_rechercher."&action=voir'><span class='fa fa-search-plus'></span>&nbsp; &nbsp;</a>&nbsp;
										<a style='text-decoration : none;' title='Télécharger' target='_blank' href='../doc/modele_moyen/bulletin.php?id_societe=".$id_societe."&fk_user=".$fk_user."&fk_salarie=".$fk_salarie."&id_convention=".$id_convention."&mois=".($i +1)."&annee=".$annee_rechercher."&action=telecharger'><span class='fa fa-download'></span> &nbsp;</a>&nbsp;";
										print "</td>";
									}else if (($i+1) < $mois_courant){
										print "<td ><b>".$mois_tab[$i]."</b></td>";
										print "<td>".apres_virgule($db, $id_societe, 0)."</td><td>".apres_virgule($db, $id_societe, 0)."</td><td>".apres_virgule($db, $id_societe, 0)."</td><td>".apres_virgule($db, $id_societe, 0)."</td><td>".apres_virgule($db, $id_societe, 0)."</td>";
										print "<td align='center'>
										<span class='fa fa-search-plus' style='color: gray'></span> &nbsp;&nbsp;&nbsp;
										<span class='fa fa-download' style='color: gray'> &nbsp;&nbsp;&nbsp;";
										print "</td>";
									}else{
										print "<td ><b>".$mois_tab[$i]."</b></td>";
										print "<td>".apres_virgule($db, $id_societe, 0)."</td><td>".apres_virgule($db, $id_societe, 0)."</td><td>".apres_virgule($db, $id_societe, 0)."</td><td>".apres_virgule($db, $id_societe, 0)."</td><td>".apres_virgule($db, $id_societe, 0)."</td>";
										print "<td align='center'>
										<span class='fa fa-search-plus' style='color: gray'>&nbsp;&nbsp;
										<span class='fa fa-download'></span> &nbsp;&nbsp;";
										print "</td>";
									}

								//Si le modèle avancé est actif
								}else if($id_bull == 3){
									if($annee_rechercher == date("Y") && ($i+1) == date("m")) {
										$info = "Vous pourrez télécharger le bulletin de paie une fois le mois cloturé";
										if(verification($db, $fk_salarie, $fk_user, $id_convention)){
											if($licence == "Active")
												print "<td ><b>".$mois_tab[$i]."</b></td>
												<td align='center' colspan='6'>".info_admin($langs->trans($info), 1)."<a class='button' target='_blank' href='../doc/modele_avance/bulletin.php?id_societe=".$id_societe."&fk_user=".$fk_user."&fk_salarie=".$fk_salarie."&id_convention=".$id_convention."&mois=".($i+1)."&annee=".$annee_rechercher."&action=no_save'>Voir un Aperçu du Bulletin</a>";
											else print "<td ><b>".$mois_tab[$i]."</b></td>
												<td align='center' colspan='6'>".info_admin("Impossible d'afficher les bulletins", 1)."<button>".$results['status']."</button>";
										}else{
											if($licence == "Active")
												print "<td ><b>".$mois_tab[$i]."</b></td>
												<td align='center' colspan='6'>".info_admin($langs->trans($info), 1)."<a class='button' target='_blank' href='./salarie_verification.php?mainmenu=paiementsalaire&leftmenu=salarie&id_societe=".$id_societe."&fk_salarie=".$fk_salarie."&id_convention=".$id_convention."&id=".$fk_user."' >Voir Onglet vérification</a>";
											else print "<td ><b>".$mois_tab[$i]."</b></td>
												<td align='center' colspan='6'>".info_admin("Impossible d'afficher les bulletins", 1)."<button>".$results['status']."</button>";
										}
			
									}elseif(($obj_verif->rowid)){
										$a = 0;
										$somme_taxe = 0;
										$somme_cotisation = 0;
										$sql_som_taxe = "SELECT montant FROM ".MAIN_DB_PREFIX."bulletin_taxe WHERE fk_bulletin=".$obj_verif->rowid;
										$res_som_taxe  = $db->query($sql_som_taxe);
										if($res_som_taxe){
											$obj_som_taxe = $db->fetch_object($res_som_taxe);
											$somme_taxe += $obj_som_taxe->montant;
										}

										$sql_som_cotisation = "SELECT SUM(montant_employe) as som_cotisation FROM ".MAIN_DB_PREFIX."bulletin_cotisation WHERE fk_bulletin=".$obj_verif->rowid;
										$res_som_cotisation  = $db->query($sql_som_cotisation);
										if($res_som_cotisation){
											$obj_som_cotisation = $db->fetch_object($res_som_cotisation);
											$somme_cotisation += $obj_som_cotisation->som_cotisation;
										}
										
										$total += $somme_taxe + $somme_cotisation;
										print "<td ><b>".$mois_tab[$i]."</b></td>";
										print "<td>".apres_virgule($db, $id_societe, $obj_som_salaire->sal_brut?:0)."</td><td>".apres_virgule($db, $id_societe, $obj_som_salaire->sal_net?:0)."</td><td>".$somme_taxe."</td><td>".$somme_cotisation."</td><td>".$total."</td>";
										print "<td align='center'><a style='text-decoration : none;' title='Voir' target='_blank' href='../doc/modele_avance/bulletin.php?id_societe=".$id_societe."&fk_user=".$fk_user."&fk_salarie=".$fk_salarie."&id_convention=".$id_convention."&mois=".($i +1)."&annee=".$annee_rechercher."&action=voir'><span class='fa fa-search-plus'></span>&nbsp; &nbsp;</a>&nbsp;
										<a style='text-decoration : none;' title='Télécharger' target='_blank' href='../doc/modele_avance/bulletin.php?id_societe=".$id_societe."&fk_user=".$fk_user."&fk_salarie=".$fk_salarie."&id_convention=".$id_convention."&mois=".($i +1)."&annee=".$annee_rechercher."&action=telecharger'><span class='fa fa-download'></span> &nbsp;</a>&nbsp;";
										print "</td>";
									}else if (($i+1) < $mois_courant){
										print "<td ><b>".$mois_tab[$i]."</b></td>";
										print "<td>".apres_virgule($db, $id_societe, 0)."</td><td>".apres_virgule($db, $id_societe, 0)."</td><td>".apres_virgule($db, $id_societe, 0)."</td><td>".apres_virgule($db, $id_societe, 0)."</td><td>".apres_virgule($db, $id_societe, 0)."</td>";
										print "<td align='center'>
										<span class='fa fa-search-plus' style='color: gray'></span> &nbsp;&nbsp;&nbsp;
										<span class='fa fa-download' style='color: gray'> &nbsp;&nbsp;&nbsp;";
										print "</td>";
									}else{
										print "<td ><b>".$mois_tab[$i]."</b></td>";
										print "<td>".apres_virgule($db, $id_societe, 0)."</td><td>".apres_virgule($db, $id_societe, 0)."</td><td>".apres_virgule($db, $id_societe, 0)."</td><td>".apres_virgule($db, $id_societe, 0)."</td><td>".apres_virgule($db, $id_societe, 0)."</td>";
										print "<td align='center'>
										<span class='fa fa-search-plus' style='color: gray'>&nbsp;&nbsp;
										<span class='fa fa-download'></span> &nbsp;&nbsp;";
										print "</td>";
									}
								}
							}else{
								
									print "<td ><b>".$mois_tab[$i]."</b></td>";
									print "<td></td><td></td><td></td><td></td>";
									print "<td align='center'>
									<span class='fa fa-search-plus' style='color: gray'>&nbsp;&nbsp;
									<span class='fa fa-download'></span> &nbsp;&nbsp;";
									print "</td>";
								
							}
						print "</tr>";

					}
					
				
					print "</tbody>";
					print "</table>";

		print "</div>";
		print "<div style='text-align:left; margin-top:10px'>";
		print "</div>";
		$sql_verif = "SELECT rowid FROM ".MAIN_DB_PREFIX."bulletin WHERE fk_salarie=".$fk_salarie." AND annee=".$annee_rechercher;
				$res_verif = $db->query($sql_verif);
				if($res_verif){
					$num = $db->num_rows($res_verif);
					if($action == "telecharger")
						$bouton = "Télécharger pour tous les (".$num.") salariés";
					else $bouton = "Les (".$num.") bulletin(s) du ".$annee_rechercher;
					if($id_bull == 1)
						print "<a target='_blank' href='../doc/tous_bulletins_salarie.php?id_societe=".$id_societe."&fk_salarie=".$fk_salarie."&id_convention=".$id_convention."&annee=".$annee_rechercher."&action=voir' style='float: right;' class='button'>".$bouton."</a></h2>";
					elseif($id_bull == 2)
						print "<a target='_blank' href='../doc/modele_moyen/tous_bulletins_salarie.php?id_societe=".$id_societe."&fk_salarie=".$fk_salarie."&id_convention=".$id_convention."&annee=".$annee_rechercher."&action=voir' style='float: right;' class='button'>".$bouton."</a></h2>";
					elseif($id_bull == 3)
						print "<a target='_blank' href='../doc/modele_avance/tous_bulletins_salarie.php?id_societe=".$id_societe."&fk_salarie=".$fk_salarie."&id_convention=".$id_convention."&annee=".$annee_rechercher."&action=voir' style='float: right;' class='button'>".$bouton."</a></h2>";

				}
	}
}


	function verification($db, $fk_salarie, $fk_user, $id_convention){
		$incorrect = 0;
			//Objet Utilisateur
			$sql_sal = "SELECT * FROM ".MAIN_DB_PREFIX."salarie WHERE rowid=".$fk_salarie;
			$result_sal = $db->query($sql_sal);
				$obj_salarie = $db->fetch_object($result_sal);
				$virgule = 0;
				
				$salaire_base = 0;
				$grilleSql = "SELECT rowid FROM ".MAIN_DB_PREFIX."grille WHERE active=1 AND fk_convention=".$id_convention;
				$grilleResult = $db->query($grilleSql);//= $db->query($grilleSql);
				$obj_grille = $db->fetch_object($grilleResult);

				$salBaseSql = "SELECT salaire_base FROM ".MAIN_DB_PREFIX."grille_categorie_echelon_salaire_base WHERE fk_grille=".$obj_grille->rowid." AND fk_categorie=".$obj_salarie->fk_categorie." AND fk_echelon=".$obj_salarie->fk_echelon;
				$salBaseResult = $db->query($salBaseSql);//= $db->query($covSql);
				if($salBaseResult){
					$objSalBase = $db->fetch_object($salBaseResult);
					if($objSalBase->salaire_base == null){
						$incorrect ++;
					}
				}else $incorrect ++;
				if($obj_salarie->sursalaire == null){
					$incorrect ++;
				}

				$annee = (int)date("Y");
				$mois = (int)date("m");
				$jour = (int)date("d");
				$sql_contrat = "SELECT * FROM ".MAIN_DB_PREFIX."salarie_contrat WHERE fk_salarie=".$obj_salarie->rowid." AND active=1 AND fk_type_contrat != 2";
				$sql_contrat .= " AND ( YEAR(date_fin)>".$annee;
				$sql_contrat .= " OR ((YEAR(date_fin) = ".$annee."  AND MONTH(date_fin) > ".$mois.") OR  (YEAR(date_fin) = ".$annee."  AND MONTH(date_fin) = ".$mois." AND DAY(date_fin) >= ".$jour.")))";
				$res_contrat = $db->query($sql_contrat);

				if($db->num_rows($res_contrat) <= 0){

					$sql_contrat = "SELECT * FROM ".MAIN_DB_PREFIX."salarie_contrat WHERE fk_salarie=".$obj_salarie->rowid." AND active=1 AND fk_type_contrat = 2";
					$res_contrat = $db->query($sql_contrat);
					if($db->num_rows($res_contrat) <= 0){
						$incorrect ++;
					}
				}
				$sql = "SELECT * FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$obj_salarie->fk_user;			
				$result_user = $db->query($sql);
				if($result_user)
					$obj_user = $db->fetch_object($result_user);			

				if(!$obj_user->dateemployment && !$obj_salarie->date_anciennete){
					$incorrect ++;
				}

				if(!$obj_user->job){
					$incorrect ++;
				}

				if($obj_salarie->situation_familiale == null){
					
					$incorrect ++;
				}
				
				if($obj_salarie->nombre_enfant == null){
					
					$incorrect ++;
				}

				if($obj_salarie->nombre_enfant_hand == null){
					$incorrect ++;
				}

				//---------------------------------------------------------------------
					
				//prime exceptionnelle
				$verification_ok = false;
			if($incorrect == 0)
				$verification_ok = true;
		return $verification_ok;
	}

	function apres_virgule($db, $id_societe, $valeur){
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
	
	  $db->close();
	