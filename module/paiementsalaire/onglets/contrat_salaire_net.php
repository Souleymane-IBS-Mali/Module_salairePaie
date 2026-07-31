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
require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';


llxHeader("", "Paiement | Salaire");
//Titre
print load_fiche_titre($langs->trans("Contrat"), '', '');
$id_societe = GETPOST("id_societe","int");
$fk_user = GETPOST("id","int");
$fk_salarie = GETPOST("fk_salarie", "int");
$id_convention = GETPOST("id_convention","int");
$id_contrat = GETPOST("id_contrat","int");
$page = GETPOST("page", "int") ?: 0;

// Recuperation des information après le clique sur l'onglet Salaire au niveau du module user
if($user->id !=1 && !$user->rights->paiementsalaire->contrats->write){
	print "<h2> Vous n\'avez pas ce droit </h2>";
}else{
	$head = salaire_Head($fk_salarie, $fk_user, $id_societe, $id_convention);
	print dol_get_fiche_head($head, 'contrat', "", -1, '');
	$action = GETPOST("action", "alpha") ?: "detail";


	$salaire_base = 0;
	$message = "";
	$annee = date("Y");
	$mois = (int)date("m");

	if(empty($fk_salarie)){
		print "<mark><strong>Il n'est pas enregistré</strong></mark><br>";
		print "Page non Disponible";
	}else{
		$obj_soc = prepare_objet_entete($fk_salarie, $fk_user, $db, $id_societe, $id_convention);
		entete_societe($obj_soc, 'societe');

		$head_contrat = salarie_contrat_Head($fk_salarie, $fk_user, $id_societe, $id_convention, $id_contrat);
		print dol_get_fiche_head($head_contrat, 'salaire_net', "", -1, '');
		//verification_contrat_salarie($db, $fk_salarie);
		//ajout d'un contrat

		$monform = new Form($db);
		if($action == "save_ajout_salaire_net"){
			$salaire_net = GETPOST('salaire_net', 'int');
			$sursalaire = GETPOST('sursalaire', 'int');
			$tab = simulation_sursalaire($db, $fk_salarie, $fk_user, $salaire_net, $id_convention, $id_societe);
			$sursalaire = $tab[0];
			$text = $tab[1];
			$formconfirm = $monform->formconfirm(
				$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=salarie&id='.$fk_user.'&fk_salarie='.$fk_salarie.'&id_convention='.$id_convention.'&id_contrat='.$id_contrat.'&id_societe='.$id_societe.'&salaire_net='.$salaire_net.'&sursalaire='.$sursalaire,
				'Affecter le Sursalaire correspondant a ce salaire net?',
				$text,
				'save_edit_salaire',
				'',
				'',
				1,
				700,
				'70%'
			);
			print $formconfirm;
			$action = "ajout_salaire_net";

		}
		if($action == "save_edit_salaire"){
			$salaire_net = GETPOST('salaire_net', 'int');
			if(empty($salaire_net))
				$message .= 'Le champ "SALAIRE NET" est obligatoire';

			if(empty($message)){
				$sql_update = 'UPDATE '.MAIN_DB_PREFIX.'salarie_contrat_salaire_net SET active=0, date_limit=now() WHERE fk_contrat='.$id_contrat;

				if($db->query($sql_update)){
						$sursalaire = GETPOST('sursalaire', 'int');
						$sql = 'INSERT INTO '.MAIN_DB_PREFIX.'salarie_contrat_salaire_net (fk_contrat, salaire_net, sursalaire, date_debut, active)';
						$sql .= 'VALUES('.$id_contrat.',"'.$salaire_net.'","'.$sursalaire.'",now(),1)';
						$db->query($sql);

						//Modification du sursalaire dans la table salarié
						$sql_update = 'UPDATE '.MAIN_DB_PREFIX.'salarie SET sursalaire="'.$sursalaire.'" WHERE rowid='.$fk_salarie;
						$db->query($sql_update);
						//Insertion dans salarié contrat salaire net
						$message = "Salaire net modifié avec succès";

							$sql_select = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$user->id;
							$obj = $db->fetch_object($db->query($sql_select));

							$sql_select_us = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$fk_user;
							$obj_us = $db->fetch_object($db->query($sql_select_us));

							//On garde la trace de l'action
							$action_effectue = "Modification du sursalaire (".$sursalaire.") et salaire net (".$salaire_net.") de ".$obj_us->firstname." ".$obj_us->lastname." Dans le contrat";
							$sql_log = 'INSERT INTO '.MAIN_DB_PREFIX.'log (fk_user, nom, prenom, quand, action_effectue, object_concerne)';
							$sql_log .= ' VALUES('.$user->id.', "'.$obj->lastname.'","'.$obj->firstname.'",now(),"'.$action_effectue.'","Modification")';
							$db->query($sql_log);

				}else{
					$message = "Un problème est survenu";
					$action = "ajout_salaire_net";
				}
			}else $action = "ajout_salaire_net";
		}



		if($action == "ajout_salaire_net" && !empty($id_contrat)){
			$sql = "SELECT sursalaire FROM ".MAIN_DB_PREFIX."salarie WHERE rowid=".$fk_salarie;
			$res = $db->query($sql);
			$obj = ($res ? $db->fetch_object($res) : null);
			$sursalaire = ($obj ? $obj->sursalaire : 0);
			print ' <form name="ajouter" method="POST" action="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=salarie&id='.$fk_user.'&fk_salarie='.$fk_salarie.'&id_convention='.$id_convention.'&id_societe='.$id_societe.'&id_contrat='.$id_contrat.'">';
			print '<input type="hidden" name="token" value="'.newToken().'">';
			print '<input type="hidden" name="action" value="save_ajout_salaire_net">';

			print "<table>";
			print "<tr class='fieldrequired'><td>Salaire net</td><td style='padding: 10px;'>
			<input type='text' value='".(GETPOST('salaire_net', 'int'))."' name='salaire_net' id='salaire_net' autofocus></td></tr>";

			print '<tr><td style="margin-top: 20px;"></td><td style="margin-top: 20px;"><input class="button" type="submit" value="Modifier">';
			print'</form>';
			print '<a href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=salarie&id='.$fk_user.'&fk_salarie='.$fk_salarie.'&id_convention='.$id_convention.'&id_societe='.$id_societe.'&id_contrat='.$id_contrat.'" class="button">Annuler</a>';

			print "</td></tr></table>";

		}else{
			$sql_contrat = "SELECT rowid, active FROM ".MAIN_DB_PREFIX."salarie_contrat WHERE rowid=".$id_contrat;
			$res_contrat = $db->query($sql_contrat);
				if($res_contrat){
					$obj_contrat = $db->fetch_object($res_contrat);

					if($obj_contrat && $user->rights->paiementsalaire->contrats->write)
						if($obj_contrat->active == 1)
							print_barre_liste("", $page, $_SERVER["PHP_SELF"], "", "", "", "", "", "", 'bill', 0, dolGetButtonTitle("Ajouter un nouveau salaire net", '', 'fa fa-plus-circle', $_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=salarie&id='.$fk_user.'&fk_salarie='.$fk_salarie.'&id_convention='.$id_convention.'&id_societe='.$id_societe.'&id_contrat='.$obj_contrat->rowid.'&action=ajout_salaire_net' , '', 1), '', 0, 0, 0, 1);
					print "<table class='tagtable liste'>";
					print "<tr class='liste_titre'><td>Salaire net</td><td>Sursalaire</td><td>Date debut</td><td>Date fin</td><td>Statut</td>";
					$sql_salaire_net  = "SELECT * FROM ".MAIN_DB_PREFIX."salarie_contrat_salaire_net WHERE fk_contrat=".$obj_contrat->rowid;
					$sql_salaire_net .= " ORDER BY rowid DESC";
					$res_salaire_net  = $db->query($sql_salaire_net );
					if($res_salaire_net){
						$actl[0] = img_picto("expiré", 'switch_off', 'class="size15x"');
						$actl[1] = img_picto("actif", 'switch_on', 'class="size15x"');
						$nb = $db->num_rows($res_salaire_net);
						$i = 0;
						while($i < $nb){
							$obj_salaire_net = $db->fetch_object($res_salaire_net );
									print "<tr class='fieldrequired impair'><td>".apres_virgule($db, $id_societe, $obj_salaire_net->salaire_net?:0)."</td>";
									print "<td>".apres_virgule($db, $id_societe, $obj_salaire_net->sursalaire)."</td>";
									print "<td>".$obj_salaire_net->date_debut."</td>";
									print "<td>".$obj_salaire_net->date_limit."</td>";
									print '<td>'.$actl[$obj_salaire_net->active].'</td></tr>';

							$i ++;
						}
						if($nb == 0)
							print "<tr><td align='center' colspan=5> Pas de contrat</td></td>";
					}else print "<tr><td align='center' colspan=5> Pas de contrat</td></td>";
				}else print "<tr><td align='center' colspan=5> Pas de contrat</td></td>";
					print '</table>';
			}

	}
	// $db->free(); // Evite une erreur si aucun résultat précis n'est fourni

	print '<script>
	var type_contrat = document.getElementById("type_contrat");
	var date_fin = document.getElementById("date_fin");

	if(type_contrat && date_fin){
		if(parseInt(type_contrat.value) == 2){
			date_fin.style.display = "none";
		}else{
			date_fin.style.display = "inline";
		}
		type_contrat.addEventListener("change", function () {
			if(parseInt(type_contrat.value) == 2){
				date_fin.style.display = "none";
			}else{
				date_fin.style.display = "inline";
			}
		}, false);
	}
	</script>';

	if(!empty($message))
			print "<script>
			$.jnotify('".$message."', {delay : 5000, fadeSpeed: 500});
			</script>";
}


function simulation_sursalaire($db, $fk_salarie, $fk_user, $contrat_salaire_net, $id_convention, $id_societe){
	$salarie_Sql = "SELECT fk_categorie, fk_echelon FROM ".MAIN_DB_PREFIX."salarie WHERE rowid=".$fk_salarie;
	$salarie_Result = $db->query($salarie_Sql);//= $db->query($salarie_Sql);
	$obj_salarie = ($salarie_Result ? $db->fetch_object($salarie_Result) : null);
	if(!$obj_salarie){
		return array(0, "<div>Informations salarié introuvables</div>");
	}

	//Recherche du salaire de base
	$grilleSql = "SELECT rowid FROM ".MAIN_DB_PREFIX."grille WHERE active=1 AND fk_convention=".$id_convention;
	$grilleResult = $db->query($grilleSql);//= $db->query($grilleSql);
	$obj_grille = ($grilleResult ? $db->fetch_object($grilleResult) : null);
	if(!$obj_grille){
		return array(0, "<div>Grille salariale active introuvable</div>");
	}

	$salBaseSql = "SELECT salaire_base FROM ".MAIN_DB_PREFIX."grille_categorie_echelon_salaire_base WHERE fk_grille=".$obj_grille->rowid." AND fk_categorie=".$obj_salarie->fk_categorie." AND fk_echelon=".$obj_salarie->fk_echelon;
	$salBaseResult = $db->query($salBaseSql);//= $db->query($covSql);
	$objSalBase = ($salBaseResult ? $db->fetch_object($salBaseResult) : null);
	$salaire_base = ($objSalBase ? $objSalBase->salaire_base : 0);

	$ind_array = salarie_indemnite_simulation($db, $fk_salarie, $salaire_base, $obj_salarie->fk_categorie,0,$id_convention);
	foreach ($ind_array as $key => $value) {
	if(!empty($key) && !empty($value)){
		$sql = "SELECT * FROM ".MAIN_DB_PREFIX."indemnite WHERE rowid=".$key;
		$ind_res = $db->query($sql);
		if($ind_res){
			$ind = $db->fetch_object($ind_res);
			if($ind->exonere == "oui")//retiré du salaire de base
				$salaire_base -= $value;

		//print "<br> Nom = ".$ind->libelle." afficher sur bulletin=".$ind->affiche_bulletin."=>".$value;

		}

	}
	}

	$pr_array = salarie_prime_simulation($db, $fk_salarie, $salaire_base, $obj_salarie->fk_categorie,0, $id_convention);
	foreach ($pr_array as $key => $value) {
	if(!empty($key) && !empty($value)){
		//$somme += $value;
		$sql = "SELECT * FROM ".MAIN_DB_PREFIX."primes WHERE rowid=".$key;
		$prime_res = $db->query($sql);
		if($prime_res){
			$pr = $db->fetch_object($prime_res);

			if($pr->exonere == "oui")//retiré du salaire de base
				$salaire_base -= $value;

			//print "<br> Nom = ".$pr->libelle." afficher sur bulletin=".$pr->affiche_bulletin."=>".$value;
		}
	}
	}

	$anciennete_tab = prime_anciennete($db, $fk_salarie, $id_convention, date('m'), date('Y'), $fk_user);
	if(!is_array($anciennete_tab)){
		$anciennete_tab = array(0, 0, '', 'Non', 'Non', 'Non');
	}
	for($k = 0; $k <= 5; $k++){
		if(!isset($anciennete_tab[$k])) $anciennete_tab[$k] = ($k == 1 ? 0 : 'Non');
	}
	$anciennete = $salaire_base*$anciennete_tab[1]/100;
	if($anciennete_tab[5] == "Oui")
	$salaire_base -= $anciennete;
	//print $anciennete_tab[0]." => ".$anciennete_tab[1];
	//les salaires
	$salaire_brut_imposable = $salaire_base;
	$salaire_brut_cotisable = $salaire_base;
	$salaire_brut = $salaire_base;

	$salaire_net = 0;
	$retenu_prest_empl = 0;
	$retenu_prest_patro = 0;
	$retenu_taxe = 0;
	$retenu = 0;

	$salaire_brut += $salaire_base*$anciennete_tab[1]/100;

	if($anciennete_tab[3] == "Oui")//exonere cotisation ou non
		$salaire_brut_cotisable += $salaire_base*$anciennete_tab[1]/100;

	if($anciennete_tab[4] == "Oui")//exonere impôt ou non
		$salaire_brut_imposable += $salaire_base*$anciennete_tab[1]/100;

		$pr_array = salarie_prime_simulation($db, $fk_salarie, $salaire_base, $obj_salarie->fk_categorie,0, $id_convention);
		foreach ($pr_array as $key => $value) {
		if(!empty($key) && !empty($value)){
			//$somme += $value;
			$sql = "SELECT * FROM ".MAIN_DB_PREFIX."primes WHERE rowid=".$key;
			$prime_res = $db->query($sql);
			if($prime_res){
				$pr = $db->fetch_object($prime_res);

					if($pr->soumis_cotisation=="Oui")
						$salaire_brut_cotisable += $value;

					if($pr->soumis_impot=="Oui")
					$salaire_brut_imposable += $value;

					$salaire_brut += $value;


				//print "<br> Nom = ".$pr->libelle." afficher sur bulletin=".$pr->affiche_bulletin."=>".$value;
			}
		}
		}


		//les primes flottantes de montant variable
		$pr_fl = prime_flottante($db, $fk_salarie);
		foreach ($pr_fl as $key => $value) {
			if(!empty($key) && !empty($value)){
				$sql = "SELECT * FROM ".MAIN_DB_PREFIX."primes WHERE rowid=".$key;
				$prime_res = $db->query($sql);
				if($prime_res){
					$pr = $db->fetch_object($prime_res);

					$val = $value;
				$pourc = 100;

					if(count(explode('%',$value."v")) > 1)
						$val = ($objSalBase->salaire_base*explode('%',$value)[0])/100;
					if($val != $value)
						$pourc = explode('%',$value)[0];

					$val = $value;
					if(count(explode('%',$value."v")) > 1)
						$val = ($objSalBase->salaire_base*explode('%',$value)[0])/100;
					$salaire_brut += $val;
					if($pr->soumis_cotisation=="Oui")
						$salaire_brut_cotisable += $val;

					if($pr->soumis_impot=="Oui")
						$salaire_brut_imposable += $val;

				}
			}
		}



		//les indemnités qui doivent être affichés sur le billetin
		//Indemnités
		$ind_array= salarie_indemnite_simulation($db, $fk_salarie, $salaire_base, $obj_salarie->fk_categorie,0, $id_convention);
		foreach ($ind_array as $key => $value) {
		if(!empty($key) && !empty($value)){
			$sql = "SELECT * FROM ".MAIN_DB_PREFIX."indemnite WHERE rowid=".$key;
			$ind_res = $db->query($sql);
			if($ind_res){
				$ind = $db->fetch_object($ind_res);
					$salaire_brut += $value;

					if($ind->soumis_cotisation=="Oui"){//les indemnités soumisent aux cotisations
						if(!empty($ind->porcentage_soumis_cotis))
							$salaire_brut_cotisable += ($value*$ind->porcentage_soumis_cotis)/100;
					}
					if($ind->soumis_impot=="Oui")////les indemnités soumisent aux impôt
						if(!empty($ind->porcentage_soumis_impot))
							$salaire_brut_imposable += ($value*$ind->porcentage_soumis_impot)/100;


				//print "<br> Nom = ".$ind->libelle." afficher sur bulletin=".$ind->affiche_bulletin."=>".$value;
			}

		}
		}



		$ind_array = indemnite_flottante($db, $fk_salarie);
		foreach ($ind_array as $key => $value) {
		if(!empty($key) && !empty($value)){
			$sql = "SELECT * FROM ".MAIN_DB_PREFIX."indemnite WHERE rowid=".$key;
			$ind_res = $db->query($sql);
			if($ind_res){
				$ind = $db->fetch_object($ind_res);
				$val = $value;
				$pourc = 100;

				if(count(explode('%',$value."v")) > 1)
					$val = ($objSalBase->salaire_base*explode('%',$value)[0])/100;
				if($val != $value)
					$pourc = explode('%',$value)[0];

				if($ind->soumis_cotisation=="Oui"){//les indemnités soumisent aux cotisations
					if(!empty($ind->porcentage_soumis_cotis))
						$salaire_brut_cotisable += ($val*$ind->porcentage_soumis_cotis)/100;
				}
				if($ind->soumis_impot=="Oui")////les indemnités soumisent aux impôt
					if(!empty($ind->porcentage_soumis_impot))
						$salaire_brut_imposable += ($val*$ind->porcentage_soumis_impot)/100;


				$salaire_brut += $val;
				$index ++;
			}
		}
		}


	$mon_salaire_brut = $salaire_brut;
	$mon_brut_cotis = 0;
	$mon_brut_imp = 0;
	$mon_net = 0;
	$fin = false;

	$sursalaire = 0;

	$net  = $contrat_salaire_net;
	$loop_guard = 0;
	while ($fin == false && $net && $loop_guard < 100000){
		$loop_guard++;
		$mon_salaire_brut += $sursalaire;
		$mon_brut_cotis = $salaire_brut_cotisable + ($mon_salaire_brut - $salaire_brut);
		$mon_brut_imp = $salaire_brut_imposable + ($mon_salaire_brut - $salaire_brut);
		$retenu_prest_empl = 0;
		$retenu_prest_patro = 0;
		$inps = 0;

		$index = 0;
		$global_cotis = salarie_prestation_simulation($db, $fk_salarie, $mon_brut_cotis, $id_convention);
		$cotis = $global_cotis[1];
		$taux_p = $global_cotis[0];
		foreach ($cotis as $key => $value) {
			$type_prest = "SELECT * FROM ".MAIN_DB_PREFIX."type_prestation WHERE nature='obligatoire' AND rowid=".$key;
				$result_type_prest = $db->query($type_prest);
				$obj_prest_type = $db->fetch_object($result_type_prest);
				if($obj_prest_type){
					$retenu_prest_empl += round($value*$mon_brut_cotis/100, 2);
					$retenu_prest_patro += round($taux_p[$index]*$mon_brut_cotis/100, 2);
				}
					//print $retenu_prest_empl."<br>";
				if($obj_prest_type && $obj_prest_type->rowid != 6)
					$inps += $value*$mon_brut_cotis/100;
			$index ++;

		}
		$mon_brut_imp -= $inps;
		$its = its_salarie($db, $fk_salarie, $mon_brut_imp);
		$retenu_taxe = $its[2];

		$mon_net = $mon_salaire_brut - $retenu_prest_empl - $retenu_taxe;

			if(round($mon_net + 100000) < ((int)$net))
				$sursalaire =  50000;
			elseif(round($mon_net+ 10000) < ($net))
				$sursalaire =  5000;
			elseif(round($mon_net + 1000) < ($net))
				$sursalaire =  500;
			elseif(round($mon_net+ 100) < ($net))
				$sursalaire = 20;
			elseif(round($mon_net) < $net)
				$sursalaire = 5;
			elseif(round($mon_net) == round($net))
				$fin = true;
			elseif(round($mon_net) > ($net + 20000))
				$sursalaire = -1000;
			elseif(round($mon_net) > ($net + 1000))
				$sursalaire = -100;
			elseif(round($mon_net) > ($net + 500))
				$sursalaire = -50;
			else $sursalaire = -1;
	}

	$text = "<div>";
	if ($fin){
		$salaire_brut_cotisable = $mon_brut_cotis ;
		$salaire_brut_imposable = $mon_brut_imp;
		$sursalaire = $mon_salaire_brut -  $salaire_brut;
		$salaire_brut = $mon_salaire_brut;

		$text .= "<table class='tagtable liste'>";
		$text .= '<tr class="impair"><td style="padding-bottom : 2px; padding-top: 1px" ><label>Salaire brut</label></td>';
		$text .= '<td style="padding-bottom : 2px; padding-top: 1px"><input type="text" disabled id="salaire_brut" name="salaire_brut" value="'.($salaire_brut).'"></td></tr>';
		$index = 0;
			$global_cotis = salarie_prestation_simulation($db, $fk_salarie, $salaire_brut_cotisable, $id_convention);
			$cotis = $global_cotis[1];
			$taux_p = $global_cotis[0];
			foreach ($cotis as $key => $value) {
				$type_prest = "SELECT * FROM ".MAIN_DB_PREFIX."type_prestation WHERE nature='obligatoire' AND rowid=".$key;
					$result_type_prest = $db->query($type_prest);
					$obj_prest_type = $db->fetch_object($result_type_prest);

					if($obj_prest_type){
						$retenu_prest_empl = round($value*$salaire_brut_cotisable/100, 2);
						$retenu_prest_patro = round($taux_p[$index]*$salaire_brut_cotisable/100, 2);
						//print $retenu_prest_empl."<br>";

						$text .= '<tr class="impair"><td style="padding-bottom : 2px; padding-top: 1px">'.$obj_prest_type->code.'</td>';
						$text .= '<td style="padding-bottom : 2px; padding-top: 1px"><input type="text" disabled id="its" name="its" value="'.$retenu_prest_patro.'"></td></tr>';
						$text .= '<tr class="impair"><td style="padding-bottom : 2px; padding-top: 1px">'.$obj_prest_type->code.' employé</td>';
						$text .= '<td style="padding-bottom : 2px; padding-top: 1px"><input type="text" disabled id="its" name="its" value="'.$retenu_prest_empl.'*"></td></tr>';

						$text .= "</tr>";
					}
			}


			$its = its_salarie($db, $fk_salarie, $salaire_brut_imposable);
		$text .= '<tr class="impair"><td style="padding-bottom : 2px; padding-top: 1px">I.T.S(mensuel)</td>';
		$text .= '<td style="padding-bottom : 2px; padding-top: 1px"><input type="text" disabled id="its" name="its" value="'.round($its[2]).'"></td></tr>';
		$text .= "<tr class='impair'>";
		$text .= '<input type="hidden" name="token" value="'.newToken().'">';
		$text .= '<input type="hidden" name="action" value="save_edit_sursalaire">';
		$text .= '<td style="padding-bottom : 2px; padding-top: 1px; background-color: gray; color: white;">Sursalaire</td>';
		$text .= '<td style="background-color: gray;"><input type="text" id="sursalaire" value="'.($sursalaire?:0).'" name="sursalaire" ></td>';
		$text .= '</tr>';
		$text .= "</table>";


	}
$text .= "</div>";
$array = array();
$array[] = $sursalaire;
$array[] = $text;
	return $array;
}

function apres_virgule($db, $id_societe, $valeur){
    $sep = ".";
    $decalage = 2;
    $reglage_bulletin = "SELECT separateur, decalage FROM ".MAIN_DB_PREFIX."reglage_bulletin WHERE fk_societe=".$id_societe;
      $result_reglage_bulletin = $db->query($reglage_bulletin);
      if($result_reglage_bulletin && $db->num_rows($result_reglage_bulletin) > 0){
        $obj_reglage_bulletin = $db->fetch_object($result_reglage_bulletin);
        $sep = $obj_reglage_bulletin->separateur;
        $decalage = $obj_reglage_bulletin->decalage;
      }
    return number_format(($valeur?:0.0), $decalage, $sep, ' ');
  }
