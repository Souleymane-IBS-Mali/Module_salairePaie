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

require '../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
require_once DOL_DOCUMENT_ROOT.'/custom/paiementsalaire/core/modules/modPaiementSalaire.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/paiementsalaire/class/html.form.class.php';

//$PaiementSalaire = new modPaiementSalaire($db);

llxHeader("", "Paiement | Salaire");
$action = GETPOST("action", "alpha");
$showtutorial .= img_picto('', 'chevron-down');
$message = "";
    print load_fiche_titre("Configuration du Salaire | Paie", '', 'configuration', 0, '', '', $showtutorial);

    print load_fiche_titre('<span class="fa fa-calendar-check-o"></span> '.$langs->trans("Les informations sur la licence actuelle"), '', '')."\n";

print '<hr><br>';
if($action == "save_licence" || $action == "rafraichir"){
    // Récupérer la clé de licence
$licensekey = GETPOST("cle_licence");
file_put_contents(DOL_DOCUMENT_ROOT.'/paiementsalaire/onglets/licence.txt', $licensekey);

$localkey = "";
$nombre_salarie_licence = 0;
$nb_entreprise_licence = 0;
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
                $result_sql_sup = $db->query($sql_sup);

                $sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."dolipaie_type"; 
                $sql_insert .= " (licensekey, local_key, nb_salarie, licence_status, proprietaire, societe, email, nom_produit, date_activation, date_expiration, type_abonnement, nb_societe)";
                $sql_insert .= " VALUES('".$licensekey."', '".$results['localkey']."', ".$nombre_salarie_licence.", '".$results["status"]."', '".$results["registeredname"]."', '".$results["companyname"]."', '".$results["email"]."', '".$results["productname"]."', '".$results["regdate"]."', '".$results["nextduedate"]."', '".$results["billingcycle"]."',".$nb_entreprise_licence.")";
                $result_insert = $db->query($sql_insert);
            }else{//on insert les informations pour la prémière fois
                $sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."dolipaie_type"; 
                $sql_insert .= " (licensekey, local_key, nb_salarie, licence_status, proprietaire, societe, email, nom_produit, date_activation, date_expiration, type_abonnement, nb_societe)";
                $sql_insert .= " VALUES('".$licensekey."', '".$results['localkey']."', ".$nombre_salarie_licence.", '".$results["status"]."', '".$results["registeredname"]."', '".$results["companyname"]."', '".$results["email"]."', '".$results["productname"]."', '".$results["regdate"]."', '".$results["nextduedate"]."', '".$results["billingcycle"]."',".$nb_entreprise_licence.")";
                $result_insert = $db->query($sql_insert);
            }
            print $db->error();
        }

        if(!empty($results['localkey']))//stockage de la clé local dans un fichier
        file_put_contents(DOL_DOCUMENT_ROOT.'/paiementsalaire/onglets/local.txt', $results['localkey']);
        
        
        //Enregistrement dans le log
        $sql_select = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$user->id;
		$obj = $db->fetch_object($db->query($sql_select));

		

        if($action == "rafraichir"){
            $message = 'Licence Rafraîchie avec succès';
            $action_effectue = "Rafraîchissement de la licence";
        }else{
            $message = 'Licence activée avec succès';
            $action_effectue = "Activation de la licence du module licence=".$licensekey;
        }
		$sql_log = 'INSERT INTO '.MAIN_DB_PREFIX.'log (fk_user, nom, prenom, quand, action_effectue, object_concerne)';
		$sql_log .= ' VALUES('.$user->id.', "'.$obj->lastname.'","'.$obj->firstname.'",now(),"'.$action_effectue.'","Rafraîchissement de la licence")';
		$db->query($sql_log);
        
    } else {
        $action = "saisir_licence";
        $incorrect = true;
    }

}
    $info = '<mark><h3 style="color:red;">Aucune licence trouvée <a class="butAction" title="insérer une clé de licence?" href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=configuration&action=saisir_licence">Saisir Licence</a><a class="butActionDelete" target="_blank" title="Veuillez insérer une clé de licence" href="https://my.ibs-mali.com/index.php?rp=/store/dolibarr-crm-erp">Acquérir une Licence</a></h3></mark>';

    $sql_licence = "SELECT * FROM ".MAIN_DB_PREFIX."dolipaie_type";
	$result_licence = $db->query($sql_licence);
	if($result_licence){
		$nb_row_licence = $db->num_rows($result_licence);
		if($nb_row_licence > 0){
            $licence_obj = $db->fetch_object($result_licence);
            print '<div class="fichecenter">';
			print '<div class="fichehalfleft">';
			print '<div class="underbanner clearboth"></div>';
			print '<table class="border tableforfield centpercent">';
				print "<tr class='underbanner'>";
				print '<td class="titlefield"><br>Propriétaire<br><br></td>';
				print '<td>'.$licence_obj->proprietaire.'</td>';
				print '</tr>';

                print "<tr class='underbanner'>";
                print '<td><br>Socété<br><br></td>';
                print '<td>'.$licence_obj->societe.'</td>';
                print "</tr>";

                print "<tr class='underbanner'>";
				print '<td ><br>email<br><br></td>';
				print '<td >'.$licence_obj->email.'</td>';
				print '</tr>';

                print "<tr class='underbanner'>";
                print '<td><br>Status de Licence<br><br></td>';
                print '<td>'.$licence_obj->licence_status.'</td>';
                print "</tr>";

                print "<tr class='underbanner'>";
                print '<td><br>Développeur<br><br></td>';
                print '<td>info@ibs-mali.com</td>';
                print "</tr>";
                print '</table>';

                
				print '</table>';
				print '</div>';
	//---------------------------------------------------
				print '<div class="fichehalfright">';
				print '<div class="underbanner clearboth"></div>';
				print '<table class="border tableforfield centpercent">';
                
                print "<tr class='underbanner'>";
				print '<td ><br>Clé de licence<br><br></td>';
				print '<td >'.$licence_obj->licensekey.' <a class="butAction" title="Rafraîchir la licence" href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=configuration&cle_licence='.$licence_obj->licensekey.'&action=rafraichir"><img src="rafraichir.png" alt="Rafraîchir" width="15"></a></td>';
				print '</tr>';
                
                print "<tr class='underbanner'>";
				print '<td ><br>Nom du produit<br><br></td>';
				print '<td >'.$licence_obj->nom_produit.'</td>';
				print '</tr>';

                print "<tr class='underbanner'>";
                print '<td><br>Date d\'expiration<br><br></td>';
                print '<td>'.$licence_obj->date_expiration.'</td>';
                print "</tr>";

                //calcul du nombre total de salarié
                $num_sal_exist = 0;
                $tout = 0;
                $sql = "SELECT sc.rowid FROM ".MAIN_DB_PREFIX."societe as sc";
                    $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."societe_extrafields as sce ON sc.rowid=sce.fk_object WHERE sce.grp=1";
                    $result = $db->query($sql);
                        
                    /*$num_societe = 0;
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
                                $users = $db->fetch_object($result1);*/
                                $sql_salarie = "SELECT count(rowid) as nb FROM ".MAIN_DB_PREFIX."salarie WHERE archiver='non'";
                                $res = $db->query($sql_salarie);
                                if($res){
                                    $num_sal_exist = $db->fetch_object($res)->nb;
                                }

                                $sql_salarie = "SELECT count(rowid) as nb FROM ".MAIN_DB_PREFIX."salarie";
                                $res = $db->query($sql_salarie);
                                if($res){
                                    $tout = $db->fetch_object($res)->nb;
                                }

                                /*$j++;
                            }
                            
                        }

                        $i++;
                    
                        }
                    }*/
                print "<tr class='underbanner'>";
                print '<td><br>Nombre salariés<br><br></td>';
                print '<td>'.$licence_obj->nb_salarie.'('.$num_sal_exist.' utilisés)('.($tout - $num_sal_exist).' archivés)</td>';
                print "</tr>";


                print "<tr class='underbanner'>";
                print '<td><br>Nombre de société(s)<br><br></td>';
                print '<td>'.$licence_obj->nb_societe.'</td>';
                print "</tr>";

                print '</table>';
				print '</div>';

                

            print '</div>';
            print '</div>';
			print '<div style="clear:both"></div>';
            print '<div style="clear:both"></div>';
			print '<div class="tabsAction">'."\n";

            print '<a class="butAction" title="insérer une clé de licence" href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=configuration&action=saisir_licence">Saisir Licence</a>';
			print '<a class="butActionDelete" target="_blank" title="Acheter une licence" href="https://my.ibs-mali.com/index.php?rp=/store/dolibarr-crm-erp">Acquérir une Licence</a>';

			print '</div>';
			print '</div>';

	//--------------------------------------------------------

        }else{
            print $info;
        }
    }else{
        print $info;
    }

    if($action == "saisir_licence"){
        $monform = new Form1($db);
        if($incorrect)
            $text = '<span style="color: red;">Clé incorrecte</span>';
        $array = array(
            array('label'=> 'saisi la Clé','type'=> 'text', 'size'=>'', 'morecss'=>'', 'moreattr'=>'size=30', 'name'=>'cle_licence','value'=>GETPOST("cle_licence", "alpha")),
            );
		$url = $_SERVER['PHP_SELF']."?mainmenu=paiementsalaire&leftmenu=configuration";
        
		$formconfirm = $monform->formconfirm1(
			$url,
			'Veuillez saisir votre clé de licence',
			$text,
			'save_licence',
			$array,
			'yes',
			1,
			185,
			'25%'
		);
		print $formconfirm;
    }

    if(!empty($message))
    print "<script>
    $.jnotify('".$message."', {delay : 5000, fadeSpeed: 500});
    </script>";	


















    /*
$step = 0;

    $step++;
	$s = img_picto('', 'puce').' <b> Etape '.$step.'</b>';
	$s = str_replace('{s}', '<a href="'.DOL_URL_ROOT.'/accountancy/admin/journals_list.php?id=35"><strong>'.$langs->transnoentitiesnoconv("Setup").' - '.$langs->transnoentitiesnoconv("AccountingJournals").'</strong></a>', $s);
    print $s." : Avant tout il faut créer une compagnie (Tiers) et lors de la cette création : n'oublié pas de mettre le champ '<strong>Gérer la paie</strong>' à <mark>OUI</mark>, et y affecter une convention '<strong>Convention</strong>'<br><br>.";

    $step++;
	$s = img_picto('', 'puce').' <b> Etape '.$step.'</b>';
	$s = str_replace('{s}', '<a href="'.DOL_URL_ROOT.'/accountancy/admin/journals_list.php?id=35"><strong>'.$langs->transnoentitiesnoconv("Setup").' - '.$langs->transnoentitiesnoconv("AccountingJournals").'</strong></a>', $s);
    print $s." : Pour affecter un <strong>Salarié</strong>, il faut créer un utilisateur(User) dolibarr.<br><br>";

    $step++;
	$s = img_picto('', 'puce').' <b> Etape '.$step.'</b>';
	$s = str_replace('{s}', '<a href="'.DOL_URL_ROOT.'/accountancy/admin/journals_list.php?id=35"><strong>'.$langs->transnoentitiesnoconv("Setup").' - '.$langs->transnoentitiesnoconv("AccountingJournals").'</strong></a>', $s);
    print $s." : Lors de la création de cet utilisateur, il faut l'affecter à une compagnie(Tiers) en précisant une compagnie(Tiers) comme valaur du champ '<strong>Entreprise <mark>(géré paie)</mark></strong>'.<br><br>";

    $step++;
	$s = img_picto('', 'puce').' <b> Etape '.$step.'</b>';
	$s = str_replace('{s}', '<a href="'.DOL_URL_ROOT.'/accountancy/admin/journals_list.php?id=35"><strong>'.$langs->transnoentitiesnoconv("Setup").' - '.$langs->transnoentitiesnoconv("AccountingJournals").'</strong></a>', $s);
    print $s." : Cliquez sur l'utilisateur que vous venez de créer, entrez dans l'onglet <strong>Salaire | Paie</strong> et Renseignez les informations nécessaires aux calculs du salaire.<br><br>";

    $step++;
	$s = img_picto('', 'puce').' <b> Etape '.$step.'</b>';
	$s = str_replace('{s}', '<a href="'.DOL_URL_ROOT.'/accountancy/admin/journals_list.php?id=35"><strong>'.$langs->transnoentitiesnoconv("Setup").' - '.$langs->transnoentitiesnoconv("AccountingJournals").'</strong></a>', $s);
    print $s." : Toutes <b>opérations</b> sur les <b>Salariés</b> se fait dans le Menu gauche <b>Salariés</b><br><br>";

    $s = img_picto('', 'puce').' <b>NB</b>';
	$s = str_replace('{s}', '<a href="'.DOL_URL_ROOT.'/accountancy/admin/journals_list.php?id=35"><strong>'.$langs->transnoentitiesnoconv("Setup").' - '.$langs->transnoentitiesnoconv("AccountingJournals").'</strong></a>', $s);
    print $s." : <b>Salariés>cliquez sur un salarié>Employé>Modifier>Enregistrer</b>.<br> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;La configuration permet la création et|ou modification des nouvelles informations ou de celles par défaut.<br><br>";

    $s = img_picto('', 'puce')." <b>Le Manuel d'utilisation</b>";
	$s = str_replace('{s}', '<a href="'.DOL_URL_ROOT.'/accountancy/admin/journals_list.php?id=35"><strong>'.$langs->transnoentitiesnoconv("Setup").' - '.$langs->transnoentitiesnoconv("AccountingJournals").'</strong></a>', $s);
    print $s." : <a href='".DOL_URL_ROOT."/paiementsalaire/config/manuel_utilisation.docx' target='_blank'><b>Manuel</b></a>.<br> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;La configuration permet la création et|ou modification des nouvelles informations ou de celles par défaut.<br><br>";
   */