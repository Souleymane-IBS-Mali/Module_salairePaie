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
print load_fiche_titre($langs->trans("Les avances et acomptes"), '', '');
$id_societe = GETPOST("id_societe","int");
$fk_user = GETPOST("id","int");
$fk_salarie = GETPOST("fk_salarie", "int");
$id_convention = GETPOST("id_convention","int");
$head = salaire_Head($fk_salarie, $fk_user, $id_societe, $id_convention);
	print dol_get_fiche_head($head, 'avance', "", -1, '');

if($user->id !=1 && $user->id != $fk_user && !$user->rights->paiementsalaire->salarie->read){
	print "<h2> Vous n\'avez pas ce droit </h2>";
}else{
	$action = GETPOST("action", "alpha");

	$mois_tab = array(" Janvier "," Février "," Mars "," Avril "," Mai "," Juin "," Juillet "," Août "," Septembre "," Octobre "," novembre "," Décembre ", " 13è Mois ");
	$salaire_base = 0;
	$message = "";
	$array = array();
	$montant_apayer = 0;
	$sursalaire = 0;
	$note = "";
	$page = 0;
	$num = 0;
	$result = null;



	if(empty($fk_salarie)){
		print "<mark><strong>Il n'a pas encore de fk_salarie</strong></mark><br>";
		print "Page non Disponible";
	}else{

		$obj_soc = prepare_objet_entete($fk_salarie, $fk_user, $db, $id_societe, $id_convention);
		entete_societe($obj_soc, 'societe');
		print '<hr>';

		//Suppression automatique de toute avance/acompte qui ont zéro comme montant à payer
		$sql = "DELETE FROM ".MAIN_DB_PREFIX."salarie_avance WHERE montant_total=0 AND fk_salarie=".$fk_salarie;
		$result2 = $db->query($sql);

		$monform = new Form($db);


		//Confirmer la suppression
		if($action == "supprimer_attention"){
			$id_avance = GETPOST("id_avance", "int");	
					$url = $_SERVER['PHP_SELF']."?mainmenu=paiementsalaire&leftmenu=societe&id_societe=".$id_societe."&id_convention=".$id_convention."&id_avance=".$id_avance."&fk_salarie=".$fk_salarie."&id=".$fk_user;
					$titre = "Voulez-vous vraiment supprimer cette avance/acompte";
	
					  $formconfirm = $monform->formconfirm(
						  $url, 
						  $titre, 
						  "", 
						  'supprimer', 
						  $array, 
						  '', 
						  1,
						  180,
						  '35%'
					  );
					  print $formconfirm;
		}

		if($action == "supprimer"){
			$id_avance = GETPOST("id_avance", "int");
			$sql = "SELECT libelle FROM ".MAIN_DB_PREFIX."salarie_avance WHERE rowid=".$id_avance;
			$res_av = $db->query($sql);
			$obj_av = $res_av ? $db->fetch_object($res_av) : null;
			if (!$obj_av) $obj_av = (object) array("libelle" => "");

			$sql = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$fk_user;
			$res_sal_user = $db->query($sql);
			$obj_sal_user = $res_sal_user ? $db->fetch_object($res_sal_user) : null;
			if (!$obj_sal_user) $obj_sal_user = (object) array("firstname" => "", "lastname" => "");

			$sql = "DELETE FROM ".MAIN_DB_PREFIX."salarie_avance WHERE rowid=".$id_avance;
			$result = $db->query($sql);
			$sql = "DELETE FROM ".MAIN_DB_PREFIX."detail_avance WHERE fk_avance=".$id_avance;
			$result2 = $db->query($sql);
			if($result && $result2){
				$sql_select = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$user->id;
				$res_user_log = $db->query($sql_select);
				$obj = $res_user_log ? $db->fetch_object($res_user_log) : null;
				if (!$obj) $obj = (object) array('firstname' => '', 'lastname' => '');

				$sql_select = "SELECT nom FROM ".MAIN_DB_PREFIX."societe WHERE rowid=".$id_societe;
				$res_soc_log = $db->query($sql_select);
				$obj_s = $res_soc_log ? $db->fetch_object($res_soc_log) : null;
				if (!$obj_s) $obj_s = (object) array("nom" => "");

				//On garde la trace de l'action
				$action_effectue = "Suppression d'une Avance-Acompte (libelle : ".$obj_av->libelle.") du salarié ".$obj_sal_user->firstname."-".$obj_sal_user->lastname." id_utilisateur=".$fk_user." salarié de la société ".$obj_s->nom;
				$sql_log = 'INSERT INTO '.MAIN_DB_PREFIX.'log (fk_user, nom, prenom, quand, action_effectue, object_concerne)';
				$sql_log .= ' VALUES('.$user->id.', "'.$obj->lastname.'","'.$obj->firstname.'",now(),"'.$action_effectue.'","Suppression")';
				$db->query($sql_log);
				$message = "Avance supprimée avec succès pour ce salarié";
			}else $message = "Un problème est survenu";
		}

		//verification de l'avance/accompte
		if($action == 'verification'){
			$libelle = GETPOST("libelle", "alpha");
			$montant = GETPOST("montant", "int");
			$mois_debut = GETPOST("mois_debut","int");
			$annee_debut = GETPOST("annee","int");

			if(GETPOST("type", "alpha") == 'avance'){
				$nb_mois = GETPOST("nb_mois","int");
			}else{
				$nb_mois = 1;
				$mois_debut = GETPOST("mois_debut_accompte","int");
				$annee_debut = GETPOST("annee_debut_accompte","int");
				
			}

			$note = GETPOST("note", "alpha");

			if(empty($libelle))
				$message = 'Le Champ "LIBELLE" est obligatoire<br>';

			if(empty($montant))
				$message .= 'Le Champ "MONTANT" est obligatoire<br>';

			if(empty($nb_mois))
				$message .= 'Le Champ "NOMBRE DE MOIS" est obligatoire<br>';

			if($montant <= $nb_mois)
				$message .= 'MONTANT doit être superieur au NOMBRE DE MOIS<br>';

			if(empty($mois_debut))
				$message .= 'Le Champ "MOIS DEBUT" est obligatoire';

			if(!empty($montant) && !empty($montant_apayer)){
				$montant = str_replace(' ', '', $montant);
				$montant_apayer = str_replace(' ', '', $montant_apayer);

				if($montant < $montant_apayer)
					$message .= 'La valeur du "MONTANT" doit être suérieur à celle du "MONTANT PAR MOIS" <br>';
			}

				if(empty($message)){
						$sql_verif = "SELECT rowid FROM ".MAIN_DB_PREFIX."salarie_avance WHERE fk_salarie=".$fk_salarie." AND (montant_paye < montant_total)";
							$res_verif = $db->query($sql_verif);
							if($res_verif){
								$nb = $db->num_rows($res_verif);
									if($nb > 1)
										$message = "Deux Avances/Acomptes en cours de paiement pour ce salarié";

							}
				}
				if(empty($message)){
					$taux = 33;
								$salaire_net = 0;
								//verifions si un taux limit à été fixé aux avance/Acompte de cette société
								$regle_avance_acompte = "SELECT taux FROM ".MAIN_DB_PREFIX."regle_avance_acompte WHERE fk_societe=".$id_societe;
								$result_regle_avance_acompte = $db->query($regle_avance_acompte);
								if($result_regle_avance_acompte && $db->num_rows($result_regle_avance_acompte) > 0){
									$obj_regle_avance_acompte = $db->fetch_object($result_regle_avance_acompte);
									$taux = $obj_regle_avance_acompte->taux;
								}

								//récupération du salaire net du salarié stipulé par le contrat
								$sql_contrat = "SELECT rowid FROM ".MAIN_DB_PREFIX."salarie_contrat WHERE fk_salarie=".$fk_salarie." AND active=1";
								$res_contrat = $db->query($sql_contrat);
								if($res_contrat && $db->num_rows($res_contrat) > 0){
									$obj_contrat = $db->fetch_object($res_contrat);
									$sql_salaire_net  = "SELECT salaire_net FROM ".MAIN_DB_PREFIX."salarie_contrat_salaire_net WHERE active=1 AND fk_contrat=".$obj_contrat->rowid;
									$res_salaire_net  = $db->query($sql_salaire_net );
									$obj_salaire_net = $res_salaire_net ? $db->fetch_object($res_salaire_net) : null;
									if ($obj_salaire_net) $salaire_net = (float) $obj_salaire_net->salaire_net;
								}

								//les avances de ce salarié en cours de paiement
								$montant_apayer = ($nb_mois > 0) ? round($montant / $nb_mois, 2) : 0;
								$montant_total_par_mois = $montant_apayer;
								$mois = date('m');
								$annee = date('Y');
								$sql = "SELECT rowid, montant_par_mois, montant_paye, montant_total FROM ".MAIN_DB_PREFIX."salarie_avance WHERE fk_salarie=".$fk_salarie;
								$sql .= " AND (montant_paye < montant_total OR  (montant_paye = montant_total AND ((annee_debut_paiement=".$annee." AND mois_debut_paiement<=".$mois."))))";
								$result = $db->query($sql);
								if($result){
									$p = 0;
									$num = $db->num_rows($result);
									while ($p < $num){
										$obj = $db->fetch_object($result);
										if($obj->montant_paye == $obj->montant_total){
											$sql_detail_avance  = "SELECT * FROM ".MAIN_DB_PREFIX."detail_avance WHERE annee_paiement=".$annee." AND mois_paiement=".$mois." AND fk_avance=".$obj->rowid;
											$res_detail_avance = $db->query($sql_detail_avance);
											if($res_detail_avance)
												if($db->num_rows($res_detail_avance) > 0){
													$montant_total_par_mois += $obj->montant_par_mois;
												}
										}else{
											$montant_total_par_mois += $obj->montant_par_mois;
										}
										
										$p ++;
									}
								}
				if($taux != 0 && $salaire_net != 0 && ($montant_total_par_mois > ($taux*$salaire_net/100))){
					$mois_tab = array(" Janvier "," Février "," Mars "," Avril "," Mai "," Juin "," Juillet "," Août "," Septembre "," Octobre "," Novembre "," Décembre ");
					$array = array(
						array('label'=> 'Taux configuré','type'=> 'text', 'size'=>'', 'morecss'=>'', 'moreattr'=>'disabled', 'name'=>'taux','value'=>$taux.'%'),
						array('label'=> 'Salaire net du salarié(contrat)','type'=> 'text', 'size'=>'', 'morecss'=>'', 'moreattr'=>'disabled', 'name'=>'salaire_net','value'=>$salaire_net),
						array('label'=> 'Limite (Produit du taux et salaire net)','type'=> 'text', 'size'=>'', 'morecss'=>'', 'moreattr'=>'disabled', 'name'=>'produit','value'=>($taux*$salaire_net/100)),

						array('label'=> 'Libelle','type'=> 'hidden', 'size'=>'', 'morecss'=>'', 'moreattr'=>'disabled', 'name'=>'libelle','value'=>$libelle),
						array('label'=> 'Nombre mois','type'=> 'hidden', 'size'=>'', 'morecss'=>'', 'moreattr'=>'disabled', 'name'=>'nb_mois','value'=>$nb_mois),
						array('label'=> 'Montant','type'=> 'hidden', 'size'=>'', 'morecss'=>'', 'moreattr'=>'disabled', 'name'=>'montant','value'=>$montant),
						array('label'=> 'Montant par mois','type'=> 'text', 'size'=>'', 'morecss'=>'', 'moreattr'=>'disabled', 'name'=>'montant_apayer','value'=>$montant_apayer),
						array('label'=> 'Autre montant à payer/mois','type'=> 'text', 'size'=>'', 'morecss'=>'', 'moreattr'=>'disabled', 'name'=>'toutavance','value'=>$montant_total_par_mois),

						array('label'=> 'Mois debut','type'=> 'hidden', 'size'=>'', 'morecss'=>'', 'moreattr'=>'disabled', 'name'=>'mois_debut','value'=>$mois_debut),
						array('label'=> 'Annee debut','type'=> 'hidden', 'size'=>'', 'morecss'=>'', 'moreattr'=>'disabled', 'name'=>'annee','value'=>$annee_debut),
						array('label'=> 'Note','type'=> 'hidden', 'size'=>'', 'morecss'=>'', 'moreattr'=>'disabled', 'name'=>'note','value'=>$note),
						);
					$formconfirm = $monform->formconfirm(
						$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=salarie&id='.$fk_user.'&fk_salarie='.$fk_salarie.'&id_convention='.$id_convention.'&id_societe='.$id_societe.'&sursalaire='.$sursalaire,
						'Attention',
						'<span style="background-color: red">Dépassement du taux autorisé</span>('.$montant_total_par_mois.' + '.$montant_apayer.') > '.($taux*$salaire_net/100),
						'save_avance',
						$array,
						'',
						1,
						440,
						'60%'
					);
					print $formconfirm;
					$action = 'ajouter_avance';
				}else{
					$action = 'save_avance';
				}
			}else $action = 'ajouter_avance';
	}
		if($action == "save_avance"){
			$libelle = GETPOST("libelle", "alpha");
			$numero_av = 1;
			$sql_verif = "SELECT rowid FROM ".MAIN_DB_PREFIX."salarie_avance WHERE fk_salarie=".$fk_salarie." AND (montant_paye < montant_total) AND libelle = '".$db->escape($libelle)."'";
			$res_verif = $db->query($sql_verif);
			if($res_verif){
				$nb = $db->num_rows($res_verif);
				if($nb > 0){
					if($nb > 1)
						$numero_av = $nb + 1;
					$libelle .= "(".$numero_av.")";
				}

			}
			
			$montant = GETPOST("montant", "int");
			$mois_debut = GETPOST("mois_debut","int");
			$annee_debut = GETPOST("annee","int");

			if(GETPOST("type", "alpha") == 'avance'){
				$nb_mois = GETPOST("nb_mois","int");
			}else{
				$nb_mois = 1;
				$mois_debut = GETPOST("mois_debut_accompte","int");
				$annee_debut = GETPOST("annee_debut_accompte","int");
				
			}
			$note = GETPOST("note", "alpha");
			if(empty($message)){
					$montant_apayer = round($montant/$nb_mois,2);
					$sql = 'INSERT INTO '.MAIN_DB_PREFIX.'salarie_avance (fk_salarie, libelle, montant_total, montant_par_mois, nombre_mois, mois_debut_paiement, annee_debut_paiement, note, montant_paye)
					VALUES ("'.$fk_salarie.'","'.$libelle.'","'.$montant.'", "'.$montant_apayer.'",'.$nb_mois.', '.$mois_debut.','.$annee_debut.',"'.$note.'","0")';
					$result = $db->query($sql);
					if($result){
						$message = "Avance associée à ce salarié";
						$id_avance = GETPOST("id_avance", "int");
						$sql = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$fk_user;
						$res_sal_user = $db->query($sql);
						$obj_sal_user = $res_sal_user ? $db->fetch_object($res_sal_user) : null;
						if (!$obj_sal_user) $obj_sal_user = (object) array('firstname' => '', 'lastname' => '');

						$sql_select = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$user->id;
						$res_user_log = $db->query($sql_select);
				$obj = $res_user_log ? $db->fetch_object($res_user_log) : null;
				if (!$obj) $obj = (object) array('firstname' => '', 'lastname' => '');


						//On garde la trace de l'action
						$action_effectue = "Ajout d'une Avance-Acompte (".$libelle.") montant (".$montant.") nombre de mois (".$nb_mois.") montant/mois(".$montant_apayer.") au compte du salarié ".$obj_sal_user->firstname."-".$obj_sal_user->lastname." id=".$fk_user." salarié de la société ".$obj_soc->name;
						$sql_log = 'INSERT INTO '.MAIN_DB_PREFIX.'log (fk_user, nom, prenom, quand, action_effectue, object_concerne)';
						$sql_log .= ' VALUES('.$user->id.', "'.$obj->lastname.'","'.$obj->firstname.'",now(),"'.$action_effectue.'","Ajout")';
						$db->query($sql_log);


				}else{
						$message = "Un problème est survenu";
				}
			}


		}
		if($action == "ajouter_avance"){
			print "<h3>Ajout Avance sur salaire</h3>";
				print ' <form name="ajouter" method="POST" action="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=salarie&id='.$fk_user.'&fk_salarie='.$fk_salarie.'&id_convention='.$id_convention.'&id_societe='.$id_societe.'">';
				print '<input type="hidden" name="token" value="'.newToken().'">';
				print '<input type="hidden" name="action" value="verification">';

				print "<table>";
				print "<tr class='fieldrequired'><td>Libellé</td><td><input type='text' name='libelle' id='libelle' value='".GETPOST("libelle", "alpha")."' autofocus></td></tr>";
				print "<tr class='fieldrequired'><td>Montant</td><td><input type='text' name='montant' id='montant' value='".GETPOST("montant", "int")."' ></td></tr>";

				print "<tr class='fieldrequired'><td>Type</td><td><select name='type' id='type'>
				<option value='avance'>Avance</option>
				<option value='acompte'>Acompte</option>
				</select>
				</td></tr>";

				print "<tr class='fieldrequired'><td style='padding: 5px; width: 100px;'>Sur combien de mois</td><td style='padding: 5px; width: 100px;'>
				<input type='number' min='1' max='12' value='".(GETPOST('nb_mois', 'int')?:1)."' name='nb_mois' id='nb_mois'>";

				print "</td></tr>";

				print "<tr class='fieldrequired'><td>Montant à payer/mois</td><td><input type='text' name='montant_apayer' min='1' max='10' id='montant_apayer' disabled value='".($montant_apayer)."' /></td></tr>";

				print "<tr class='fieldrequired'><td style='padding: 5px; width: 200px;'>Mois du début de paiement</td><td style='padding: 5px; width: 200px;'>
				<select name='mois_debut' id='mois_debut'><option value='0'></option>";
				$mois_d = date('m');
				$sql_verif = "SELECT DISTINCT mois FROM ".MAIN_DB_PREFIX."bulletin WHERE cloture='non' AND fk_societe=".$id_societe."";
				$res_verif = $db->query($sql_verif);
				if($res_verif && $db->num_rows($res_verif)){
					$obj_verif = $db->fetch_object($res_verif);
					$mois_d = $obj_verif->mois;
				}
				$mon_ann = GETPOST("annee", "int")?:date("Y");
				for ($i=0; $i < count($mois_tab); $i++) {
					if($mon_ann == date("Y")){
						if(($i+1)>=$mois_d)
						print "<option value='".($i + 1)."'>".$mois_tab[$i]."</option>";
					}elseif($mon_ann > date("Y"))
						print "<option value='".($i + 1)."'>".$mois_tab[$i]."</option>";
				}
				print "</select>";
				print "<select name='annee' id='annee'>";
				print "<option value='".((int)date("Y"))."' ".(GETPOST("annee", "int")==(int)date("Y")?'selected':'').">".((int)date("Y"))."</option>";
				print "<option value='".((int)date("Y")+1)."' ".(GETPOST("annee", "int")==((int)date("Y")+1)?'selected':'').">".((int)date("Y")+1)."</option>";
				print "<option value='".((int)date("Y")+2)."' ".(GETPOST("annee", "int")==((int)date("Y")+2)?'selected':'').">".((int)date("Y")+2)."</option>";
				print "</select>";


				print "<input type='hidden' name='mois_debut_accompte' id='mois_debut_accompte'>";
				print "<input type='hidden' name='annee_debut_accompte' id='annee_debut_accompte'>";

				print "</td></tr>";
				$info = 'petite note';
				print '<tr><td style="padding: 5px; width: 200px;">Note</td><td style="padding: 5px; width: 200px;"><input type="text" name="note" size="80" id="note" value="'.$note.'" /></td></tr>';
				//print "<td><input value='Ajouter' class='button' type='submit'/></td></tr>";
				print '<tr>';
				print '<td style=" padding-right: 30px; padding-bottom: 30px"></td><td style=" padding-bottom: 30px"><input class="button" type="submit" value="Ajouter">';
				print'</form>';
				print '<a href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=salarie&id='.$fk_user.'&fk_salarie='.$fk_salarie.'&id_convention='.$id_convention.'&id_societe='.$id_societe.'" class="button">Annuler</a></td></tr>';
				print '</table>';

				print '<script type="text/javascript">
				var montant = document.getElementById("montant");
				var type = document.getElementById("type");
					var nb_mois = document.getElementById("nb_mois");
					var montant_apayer = document.getElementById("montant_apayer");
					var mois_debut = document.getElementById("mois_debut");
					var annee = document.getElementById("annee");
					var mois_debut_accompte = document.getElementById("mois_debut_accompte");
					var annee_debut_accompte = document.getElementById("annee_debut_accompte");
					var libel = document.getElementById("libelle");
		

					montant.addEventListener("blur", function () {
						if(!isNaN(montant.value) && !isNaN(nb_mois.value) && montant.value != "" && nb_mois.value != "" ){
							montant_apayer.value = (parseInt(montant.value)/parseInt(nb_mois.value))
						}

					},
					false,
					);

					nb_mois.addEventListener("blur", function () {
						if(!isNaN(montant.value) && !isNaN(nb_mois.value) && montant.value != "" && nb_mois.value != ""){
							montant_apayer.value = (parseInt(montant.value)/parseInt(nb_mois.value))
						}
					},
					false,
					);


						annee.addEventListener("change", function () {
							var id_annee = annee.value;
							var mont = montant.value;
							var nb_moi = nb_mois.value;
							var libelle = libel.value;
							window.location.href = "'.$_SERVER["PHP_SELF"].'?id='.$fk_user.'&id_societe='.$id_societe.'&fk_salarie='.$fk_salarie.'&id_convention='.$id_convention.'&action=ajouter_avance&annee="+id_annee+"&libelle="+libelle+"&montant="+mont+"&nb_mois="+nb_moi;
						},
						false,
						);

						type.addEventListener("change", function () {
							if(type.value == "acompte"){
								mois_debut.selectedIndex = "1";
								annee.selectedIndex = "0";
								nb_mois.value = 1;

								nb_mois.disabled = true;
								montant_apayer.disabled = true;
								mois_debut.disabled = true;
								annee.disabled = true;
								mois_debut_accompte.value = mois_debut.value;
								annee_debut_accompte.value = annee.value;
								
							}else{
								nb_mois.disabled = false;
								montant_apayer.disabled = false;
								mois_debut.disabled = false;
								annee.disabled = false;
							}

						},
						false,
						);

					</script>';

			}else{
				if($user->rights->paiementsalaire->salarie->ecrireAvanceAcompte)
					print_barre_liste("", $page, $_SERVER["PHP_SELF"], "", "", "", "", "", "", 'bill', 0, dolGetButtonTitle("Ajouter une nouvelle avance/accompte", '', 'fa fa-plus-circle', $_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=salarie&id='.$fk_user.'&fk_salarie='.$fk_salarie.'&id_convention='.$id_convention.'&id_societe='.$id_societe.'&action=ajouter_avance' , '', 1), '', 0, 0, 0, 1);

			print "<h3>Avance à Remboursser</h3>";
			print "<table class='tagtable liste'>";
			print "<tr class='liste_titre'><td>Libelle</td><td>Montant</td><td>Payé</td><td>Nombre de mois</td><td>Montant/Mois";
			print "</td><td>Début paiement</td><td>Date création</td><td>Note</td><td style='padding: 10px; width: 10%;'>Suppression</td></tr>";
			$annee = date("Y");
			$mois = date("m");
			$sql = "SELECT * FROM ".MAIN_DB_PREFIX."salarie_avance WHERE fk_salarie=".$fk_salarie;
			$sql .= " AND CAST(montant_paye AS DECIMAL(20,2)) < CAST(montant_total AS DECIMAL(20,2)) ORDER BY mois_debut_paiement DESC";// OR  (montant_paye = montant_total AND ((annee_debut_paiement<=".$annee." AND mois_debut_paiement<=".$mois."))))";

			$result = $db->query($sql);
			if($result){
				$i = 0;
				$num = $db->num_rows($result);
				while ($i < $num){
					$obj = $db->fetch_object($result);

					$sql_detail = "SELECT COUNT(rowid) as nbcount
									FROM ".MAIN_DB_PREFIX."detail_avance
									WHERE fk_avance=".$obj->rowid;

						$result_detail = $db->query($sql_detail);

						$num_detail = 0;
						if ($result_detail) {
							$obj_count = $db->fetch_object($result_detail);
							$num_detail = (int) $obj_count->nbcount;
						}

						if ($num_detail < $obj->nombre_mois) {
							// votre code


							if($i % 2 == 0)
								$class='pair';
							else $class='impair';
								print "<tr class=".$class.">";
								print "<td ><a href='avance_information.php?mainmenu=paiementsalaire&leftmenu=salarie&id_societe=".$id_societe."&fk_salarie=".$fk_salarie."&id_convention=".$id_convention."&id=".$fk_user."&id_avance=".$obj->rowid."&action=information'>".($obj->libelle?:"N/A")."</a></td>";
								print "<td>".apres_virgule($db, $id_societe, $obj->montant_total)."</td>";
								print "<td>".apres_virgule($db, $id_societe, $obj->montant_paye)."</td>";
								print '<td>'.$obj->nombre_mois.'</td><td>'.apres_virgule($db, $id_societe, $obj->montant_par_mois).'</td>';
								print '<td>'.$mois_tab[$obj->mois_debut_paiement-1].' '.$obj->annee_debut_paiement.'</td>';
								print "<td>".$obj->date_affectation."</td>";
								print "<td>".$obj->note."</td>";

								/*$sql_bulletin = 'SELECT avance.fk_bulletin, avance.fk_avance, bul.rowid, bul.cloture FROM '.MAIN_DB_PREFIX.'bulletin_avance as avance';
								$sql_bulletin .= ' LEFT JOIN '.MAIN_DB_PREFIX.'bulletin as bul on avance.fk_bulletin=bul.rowid';
								$sql_bulletin .= ' WHERE fk_avance='.$obj->rowid.' AND bul.cloture="non"';
								$res_bulletin = $db->query($sql_bulletin);
								$trouve = false;
								if($db->num_rows($res_bulletin) > 0)
									$trouve = true;
								
								if($trouve)*/
									print "<td><a class='reposition editfielda button' href='avance.php?mainmenu=paiementsalaire&leftmenu=salarie&fk_salarie=".$fk_salarie."&id_societe=".$id_societe."&id=".$fk_user."&id_convention=".$id_convention."&action=supprimer_attention&id_avance=".$obj->rowid."'>Supprimer</a></td></tr>";
								//else print "<td><button class='button' disabled>Supprimer</button></td></tr>";
					}
					$i ++;
				}
			}


				$sql_verif = "SELECT annee, mois FROM ".MAIN_DB_PREFIX."bulletin WHERE cloture='non' AND fk_societe=".$id_societe." ORDER BY rowid DESC";
				$res_verif = $db->query($sql_verif);
				$rowid_bulletin = $res_verif ? $db->num_rows($res_verif) : 0;
				if($rowid_bulletin > 0){
					$obj_bul = $res_verif ? $db->fetch_object($res_verif) : null;

					$sql = "SELECT * FROM ".MAIN_DB_PREFIX."salarie_avance WHERE fk_salarie=".$fk_salarie;
					$sql .= " AND CAST(montant_paye AS DECIMAL(20,2)) >= CAST(montant_total AS DECIMAL(20,2)) AND annee_debut_paiement=".$obj_bul->annee;// OR  (montant_paye = montant_total AND ((annee_debut_paiement<=".$annee." AND mois_debut_paiement<=".$mois."))))";
					$result = $db->query($sql);

					$num2 = $result ? $db->num_rows($result) : 0;
					while ($result && ($obj = $db->fetch_object($result))){
						
						$sql_detail = "SELECT mois_paiement FROM ".MAIN_DB_PREFIX."detail_avance WHERE mois_paiement=".((int)$obj_bul->mois)." AND fk_avance=".$obj->rowid;
						$result_detail = $db->query($sql_detail);
						
						if($result_detail && ($av_detail = $db->fetch_object($result_detail))){

								if($i % 2 == 0)
									$class='pair';
								else $class='impair';
									print "<tr class=".$class.">";
									print "<td ><a href='avance_information.php?mainmenu=paiementsalaire&leftmenu=salarie&id_societe=".$id_societe."&fk_salarie=".$fk_salarie."&id_convention=".$id_convention."&id=".$fk_user."&id_avance=".$obj->rowid."&action=information'>".($obj->libelle?:"N/A")."</a></td>";
									print "<td>".apres_virgule($db, $id_societe, $obj->montant_total)."</td>";
									print "<td>".apres_virgule($db, $id_societe, $obj->montant_paye)."</td>";
									print '<td>'.$obj->nombre_mois.'</td><td>'.apres_virgule($db, $id_societe, $obj->montant_par_mois).'</td>';
									print '<td>'.$mois_tab[$obj->mois_debut_paiement-1].' '.$obj->annee_debut_paiement.'</td>';
									print "<td>".$obj->date_affectation."</td>";
									print "<td>".$obj->note."</td>";

									
										print "<td><a class='reposition editfielda button' href='avance.php?mainmenu=paiementsalaire&leftmenu=salarie&fk_salarie=".$fk_salarie."&id_societe=".$id_societe."&id=".$fk_user."&id_convention=".$id_convention."&action=supprimer_attention&id_avance=".$obj->rowid."'>Supprimer</a></td></tr>";
						}
					}
				}

				
			if($num <= 0)
				print "<tr><td align='center' colspan='9'>Aucune avance sur salaire pour ce salarié</td></tr>";
			
			print "</table>";
		print "<br><h3>Avances Rembourssées (10 dernières)</h3>";
		//Avance associé ce salarié
			print "<table class='tagtable liste'>";
			print "<tr class='liste_titre'><td>Libelle</td><td >Montant</td><td >Nombre de mois</td><td >Montant/Mois";
			print "</td><td >Début paiement</td><td>Date affectation</td><td >Note</td><td >Suppression</td></tr>";
			$date = date("Y-m-d");
			$mois_annee = explode("-", $date);
			$sql = "SELECT * FROM ".MAIN_DB_PREFIX."salarie_avance WHERE montant_paye = montant_total AND fk_salarie=".$fk_salarie." ORDER BY annee_debut_paiement DESC, mois_debut_paiement DESC";
			//$sql .= " AND CAST(montant_paye AS DECIMAL(20,2)) = CAST(montant_total AS DECIMAL(20,2)) ORDER BY mois_debut_paiement DESC";
			$result = $db->query($sql);
			if($result){
				$i = 0;
				$num = $db->num_rows($result);
				while ($i < $num && $i < 10){
					$obj = $db->fetch_object($result);
					if ($obj)
					{
						$sql_detail_avance  = "SELECT * FROM ".MAIN_DB_PREFIX."detail_avance WHERE fk_avance=".$obj->rowid;
						$sql_detail_avance .= " ORDER BY rowid DESC";
						$res_detail_avance  = $db->query($sql_detail_avance );
						if($res_detail_avance){
							$obj_detail_avance = $db->fetch_object($res_detail_avance );
							//if($obj_detail_avance->mois_paiement < $mois){
								if($i % 2 == 0)
									$class='pair';
								else $class='impair';
								print "<tr class=".$class.">";
								print "<td ><a href='avance_information.php?mainmenu=paiementsalaire&leftmenu=salarie&id_societe=".$id_societe."&fk_salarie=".$fk_salarie."&id_convention=".$id_convention."&id=".$fk_user."&id_avance=".$obj->rowid."&action=information'>".($obj->libelle?:"N/A")."</a></td>";
								print "<td>".apres_virgule($db, $id_societe, $obj->montant_total)."</td>";

								print '<td>'.$obj->nombre_mois.'</td><td>'.apres_virgule($db, $id_societe, $obj->montant_par_mois).'</td>';
								print '<td>'.$mois_tab[$obj->mois_debut_paiement-1].'</td>';
								print "<td>".$obj->date_affectation."</td>";
								print "<td>".$obj->note."</td>";

								$sql_verif = "SELECT DISTINCT annee, mois FROM ".MAIN_DB_PREFIX."bulletin WHERE cloture='non' AND fk_societe=".$id_societe." ORDER BY rowid DESC";
								$res_verif = $db->query($sql_verif);
								$rowid_bulletin = $res_verif ? $db->num_rows($res_verif) : 0;
								$obj_bul = $res_verif ? $db->fetch_object($res_verif) : null;

								if($obj_bul && (($obj->mois_debut_paiement == $obj_bul->mois && $obj->annee_debut_paiement == $obj_bul->annee) || ($obj->mois_debut_paiement == 13 && $obj->annee_debut_paiement == $obj_bul->annee)))
									print "<td><a class='reposition editfielda button' href='avance.php?mainmenu=paiementsalaire&leftmenu=salarie&fk_salarie=".$fk_salarie."&id_societe=".$id_societe."&id=".$fk_user."&id_convention=".$id_convention."&action=supprimer_attention&id_avance=".$obj->rowid."'>Supprimer</a></td></tr>";
								else
									print "<td><a class='reposition editfielda' href='".$_SERVER['PHP_SELF']."?mainmenu=paiementsalaire&leftmenu=salarie&fk_salarie=".$fk_salarie."&id_societe=".$id_societe."&id=".$fk_user."&id_convention=".$id_convention."&action=supprimer_attention&id_avance=".$obj->rowid."'><button class='button' disabled >Désaffecter</button></a></td></tr>";
							//}
						}

					}
					$i ++;
				}
				if($num == 0)
					print "<tr><td align='center' colspan='9'>Aucune avance sur salaire payée pour ce salarié</td></tr>";
			}else{
				print "<tr><td align='center' colspan='9'>Aucune avance sur salaire payée pour ce salarié</td></tr>";
			}
			print "</table>";
		}

	}

	if(!empty($message))
			print "<script>
			$.jnotify('".dol_escape_js($message)."', {delay : 5000, fadeSpeed: 500});
			</script>";
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
    return number_format((float) $valeur, (int) $decalage, $sep ?: '.', ' ');
  }

llxFooter();
$db->close();