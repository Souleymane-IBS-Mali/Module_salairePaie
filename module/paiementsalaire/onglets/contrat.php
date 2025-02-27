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
print load_fiche_titre($langs->trans("Contrats"), '', '');
$id_societe = GETPOST("id_societe","int");
$fk_user = GETPOST("id","int");
$fk_salarie = GETPOST("fk_salarie", "int");
$id_convention = GETPOST("id_convention","int");
$info = "";
$head = salaire_Head($fk_salarie, $fk_user, $id_societe, $id_convention);
	print dol_get_fiche_head($head, 'contrat', "", -1, '');

// Recuperation des information après le clique sur l'onglet Salaire au niveau du module user
if($user->id !=1 && $user->id != $fk_user && !$user->rights->paiementsalaire->contrats->read){
	print "<h2> Vous n\'avez pas ce droit </h2>";
}else{
	$action = GETPOST("action", "alpha");


	$salaire_base = 0;
	$message = "";
	$annee = date("Y");
	$mois = (int)date("m");

	if(empty($fk_salarie)){
		print "<mark><strong>Il n'est pas enregistré</strong></mark><br>";
		print "Page non Disponible";
	}else{
		$monform = new Form($db);
		$obj_soc = prepare_objet_entete($fk_salarie, $fk_user, $db, $id_societe, $id_convention);
		entete_societe($obj_soc, 'societe');
		print '<hr>';

		//verification_contrat_salarie($db, $fk_salarie);
		//ajout d'un contrat
		if($action == "ajouter_contrat"){

			$numero = GETPOST("numero_contrat", "alpha");
			$type = GETPOST("type_contrat", "int");
			$date_emb = GETPOST("date_embauche");
			$date_sign = GETPOST("date_signature");
			$date_fin = GETPOST("date_fin");
			$salaire_brut = GETPOST("salaire_brut", "int");
			$salaire_net = GETPOST("salaire_net", "int");


			if(empty($numero)){
				$message = 'Le champ "NUMERO CONTRAT" est obligatoire<br>';
			}else{
				$result = $db->query("SELECT rowid FROM ".MAIN_DB_PREFIX."salarie_contrat WHERE numero = '".$numero."';");
				$exist = $db->num_rows($result);
				if($exist > 0)
					$message = 'Ce "NUMERO CONTRAT" '.$numero.' existe déjà<br>';
			}
			if(empty($type) || $type == '0')
				$message .= 'Le champ "TYPE CONTRAT" est obligatoire<br>';
			if(empty($date_sign))
				$message .= 'Le champ "DATE SIGNATURE" est obligatoire<br>';
			if(empty($date_emb))
				$message .= 'Le champ "DATE EMBAUCHE" est obligatoire<br>';
			if($type == "1" && empty($date_fin))
				$message .= 'Le champ "DATE FIN" est obligatoire pour le type CDD<br>';

			if($type == "2")
				unset($date_fin);

			if(empty($salaire_net) && empty($salaire_brut))
				$message .= 'Le champ "SALAIRE (brut ou net)" est obligatoire<br>';

				$destination = "";

			if(empty($message))
				if(!empty($date_sign) && !empty($date_fin) && empty($date_emb)){
					if($date_sign > $date_fin)
						$message .= 'La "DATE SIGNATURE" ne peut pas être supérieur à la date "DATE FIN"<br>';
					if($date_emb > $date_fin)
						$message .= 'La "DATE EMBAUCHE" ne peut pas être supérieur à la date "DATE FIN"<br>';
				}

				//if(empty($message))
			if (isset($_FILES['fichier_contrat']) && $_FILES['fichier_contrat']['error'] == 0 && empty($message)) {
				$nom = $_FILES['fichier_contrat']['name'];
				$chemin = $_FILES['fichier_contrat']['tmp_name'];
				$extension = strrchr($nom,".");
				$extension_autorisees = array('.JPG','.jpg','.png','.PNG','.jpeg','.JPEG','.pdf','.PDF');
				$destination = './documents_contrat/contrat'.$fk_salarie.'__'.date('d_m_y_h_i_s').''.$extension;
				$nomDossier = 'documents_contrat';
				// Vérifier si le dossier n'existe pas déjà
				if (!file_exists($nomDossier))
					if (mkdir($nomDossier, 0777, true)) {
					}

				//$destination = DOL_DOCUMENT_ROOT.'/paiementsalaire/onglets/documents_contrat/contrat'.$fk_salarie.'__'.date('d_m_y_h_i_s').''.$extension;
				if(in_array($extension,$extension_autorisees)){
					if($_FILES['fichier_contrat']['size']<=6000000){
						if(move_uploaded_file($chemin,$destination)){

							//Simulation----------------------------------------------------------------------------
							$tab = simulation_sursalaire($db, $fk_salarie, $fk_user, $salaire_brut, $salaire_net, $id_convention, $id_societe);
							$sursalaire = $tab[0];


							if($sursalaire < 0)
								$info = "<h3><mark>Pour avoir ce salaire, il faut un sursalaire négative.<br>Le sursalaire a été mis à zéro 0. Il faut faire une simulation</h3></mark>";

							$sursalaire = $tab[0]>0?$tab[0]:0;
							$text = $tab[1];
							if(empty($salaire_net))
								$salaire_net = $tab[2];

							if(empty($salaire_brut))
								$salaire_brut = $tab[1];
							//------------------------------------------------------------------------------------------
							$sql_update = 'UPDATE '.MAIN_DB_PREFIX.'salarie_contrat SET active=0 WHERE fk_salarie='.$fk_salarie;
							$db->query($sql_update);
							$sql_insert = "";
							if(empty($date_fin))
								$sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'salarie_contrat (fk_salarie,numero,fk_type_contrat,date_signature,date_embauche, salaire_brut, fichier_contrat, active)
								VALUES ('.$fk_salarie.',"'.$numero.'",'.$type.',"'.$date_sign.'","'.$date_emb.'","'.$salaire_brut.'","'.$destination.'",1)';
							else
								$sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'salarie_contrat (fk_salarie,numero,fk_type_contrat,date_signature,date_embauche,date_fin, salaire_brut, fichier_contrat, active)
								VALUES ('.$fk_salarie.',"'.$numero.'",'.$type.',"'.$date_sign.'","'.$date_emb.'","'.($date_fin?:"now()").'","'.$salaire_brut.'","'.$destination.'",1)';
							if($db->query($sql_insert)){

								//Dateemployement
								$sql_update_user = 'UPDATE '.MAIN_DB_PREFIX.'user SET dateemployment="'.$date_emb.'" WHERE rowid='.$fk_user;
								$db->query($sql_update_user);

								$result = $db->query("SELECT LAST_INSERT_ID() as rowid;");
								$obj = $db->fetch_object($result);
								$id_contrat =  $obj->rowid;


								$sql = 'INSERT INTO '.MAIN_DB_PREFIX.'salarie_contrat_salaire_net (fk_contrat, salaire_net, sursalaire, date_debut, active)';
								$sql .= 'VALUES('.$id_contrat.',"'.$salaire_net.'","'.$sursalaire.'",now(),1)';
								$db->query($sql);

								//modification de sursalaire dans la table salarié
								$sql_update = 'UPDATE '.MAIN_DB_PREFIX.'salarie SET sursalaire="'.$sursalaire.'" WHERE rowid='.$fk_salarie;
								$db->query($sql_update);

									if(empty($sursalaire))
										$message = "Le 'SURSALAIRE' doit être des chiffres";
									if(empty($message)){
										$sql = "UPDATE ".MAIN_DB_PREFIX."salarie SET";
											if($sursalaire != "" && $sursalaire>0){
													$sql .= " sursalaire=".$sursalaire."";
											}else{
												$sql .= " sursalaire=0";
											}

											$sql .= " WHERE rowid=".$fk_salarie;
											$result = $db->query($sql);
											if($result){
												$message .= 'Sursalaire modifié avec succès<br>';
												$message .= "Contrat affecté à ce salarié avec succès";

												$sql_select = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$user->id;
												$obj = $db->fetch_object($db->query($sql_select));

												$sql_select = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$fk_user;
												$obj_user = $db->fetch_object($db->query($sql_select));

												$soc_sql = "SELECT nom FROM ".MAIN_DB_PREFIX."societe WHERE rowid=".$id_societe;
												$soc_res = $db->query($soc_sql);//= $db->query($covSql);
												$obj_soc = $db->fetch_object($soc_res);

												$action_effectue = "Création du contrat de ".$obj_user->firstname." ".$obj_user->lastname." de la société ".$obj_soc->nom;
												$sql_log = 'INSERT INTO '.MAIN_DB_PREFIX.'log (fk_user, nom, prenom, quand, action_effectue, object_concerne)';
												$sql_log .= ' VALUES('.$user->id.', "'.$obj->lastname.'","'.$obj->firstname.'",now(),"'.$action_effectue.'","Ajout contrat")';
												$db->query($sql_log);


											}else {
												$message = 'Un problème est survenu';
											}

									}

								/*$formconfirm = $monform->formconfirm(
									$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=salarie&id='.$fk_user.'&fk_salarie='.$fk_salarie.'&id_convention='.$id_convention.'&id_societe='.$id_societe.'&sursalaire='.$sursalaire,
									'Affecter Sursalaire?',
									$text,
									'save_edit_sursalaire',
									'',
									'',
									1,
									700,
									'70%'
								);
										print $formconfirm;*/

								//----------------------------------------------------------------------------

							}else{
								$message = "Un problème est survenu";
								$action = "ajouter";
							}
						}else{
							$action = "ajouter";
							$message .= "Un problème est intervenu lors du Chargement du fichier";
						}
					}else{
						$action = "ajouter";
						$message .= "La taille du fichier doit être inférieur à 1Mo";
					}

				}else{
					$action = "ajouter";
					$message .= "Extension de fichier non autorisée<br><br>Les extensions autorisées son : JPG, PNG, JPEG et PDF";
				}
			}else{ $action = "ajouter";
				if(empty($message))
					$message .= "Veuillez joindre un fichier";
			}

		}

		/*if($action == "save_edit_sursalaire"){
			$sursalaire = GETPOST("sursalaire", "09");
			if(empty($sursalaire))
				$message = "Le 'SURSALAIRE' doit être des chiffres";
			if(empty($message)){
				$sql = "UPDATE ".MAIN_DB_PREFIX."salarie SET";
					if($sursalaire != "" && $sursalaire>0){
							$sql .= " sursalaire=".$sursalaire."";

					}else{
						$sql .= " sursalaire=0";
					}

					$sql .= " WHERE rowid=".$fk_salarie;
					$result = $db->query($sql);
					if($result){
					$message = 'Sursalaire modifié avec succès';
					}else {
						$message = 'Un problème est survenu';
					}

			}
		}*/


		if($action == "ajouter"){
			print ' <form name="ajout" method="POST" action="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=salarie&id='.$fk_user.'&fk_salarie='.$fk_salarie.'&id_convention='.$id_convention.'&id_societe='.$id_societe.'" enctype="multipart/form-data">';
			print '<input type="hidden" name="token" value="'.newToken().'">';
				print '<input type="hidden" name="action" value="ajouter_contrat">';

			print "<table>";
			print "<tr class='fieldrequired'><td style='width: 200px; padding-top: 20px;'>Numéro Contrat</td><td style='width: 200px; padding-top: 20px;'><input type='text' name='numero_contrat' id='numero_contrat' value='".GETPOST("numero_contrat", "alpha")."' autofocus></td></tr>";

			print "<tr class='fieldrequired'><td style='width: 200px; padding-top: 20px;'>Type Contrat</td><td><select
			name='type_contrat' id='type_contrat'>";
			print "<option value='0'></option>";
			$sql_type_contrat = "SELECT rowid, libelle FROM ".MAIN_DB_PREFIX."type_contrat";
			$restype_contrat = $db->query($sql_type_contrat);
			if($restype_contrat){
				$nb = $db->num_rows($restype_contrat);
				$i =0;
				while ($i < $nb) {
					$obj_typ_cont = $db->fetch_object($restype_contrat);
					if(!empty(GETPOST("type_contrat", "int")) && GETPOST("type_contrat", "int") == $obj_typ_cont->rowid)
						print "<option value='".$obj_typ_cont->rowid."' selected>".$obj_typ_cont->libelle."</option>";
					else
						print "<option value='".$obj_typ_cont->rowid."'>".$obj_typ_cont->libelle."</option>";
					$i ++;
				}
			}

			$sql_select = "SELECT dateemployment FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$fk_user;
			$obj = $db->fetch_object($db->query($sql_select));
			print"</td></tr>";
			print "<tr class='fieldrequired'><td style='width: 200px; padding-top: 20px;'>Date D'embauche</td><td style='width: 200px; padding-top: 20px;'>
			<input type='date' value='".($obj->dateemployment?:(GETPOST('date_embauche')))."' name='date_embauche' id='date_embauche'>";
			print "</td></tr>";
			print "<tr class='fieldrequired'><td style='width: 200px; padding-top: 20px;'>Date Signature</td><td style='width: 200px; padding-top: 20px;'>
			<input type='date' value='".(GETPOST('date_signature'))."' name='date_signature' id='date_signature'>";
			print "</td></tr>";
			print "<tr class='fieldrequired'><td style='width: 200px; padding-top: 20px;'>Date Fin</td><td style='width: 200px; padding-top: 20px;'>
			<input type='date' value='".(GETPOST('date_fin'))."' name='date_fin' id='date_fin'>";
			print "</td></tr>";
			//salaire net
			print "<tr class='fieldrequired'><td style='width: 200px; padding-top: 20px;'>Salaire brut</td><td style='width: 200px; padding-top: 20px;'>
			<input type='text' value='".(GETPOST('salaire_brut', 'int'))."' name='salaire_brut' id='salaire_brut'>";
			print "</td></tr>";
			//salaire brut
			print "<tr class='fieldrequired'><td style='width: 200px; padding-top: 20px;'>Salaire net</td><td style='width: 200px; padding-top: 20px;'>
			<input type='text' value='".(GETPOST('salaire_net', 'int'))."' name='salaire_net' id='salaire_net'>";
			print "</td></tr>";

			print "<tr class='fieldrequired'><td style='width: 200px; padding-top: 20px;'>Fichier du Contrat</td><td style='width: 200px; padding-top: 20px;'><input type='file' name='fichier_contrat' id='fichier_contrat' ><1Mo</td></tr>";

			print '<tr>';
			print '<td style=" padding-right: 30px; padding-bottom: 30px"></td><td style="padding-top: 30px; width: 300px;"><input onclick="MonSubmitForm()" class="button" type="submit" value="Enregistrer">';
			print'</form>';
			print '<a href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=salarie&id='.$fk_user.'&fk_salarie='.$fk_salarie.'&id_convention='.$id_convention.'&id_societe='.$id_societe.'" class="button">Annuler</a></td></tr>';
			print '</table>';
	}else{
		print $info;
		if($user->rights->paiementsalaire->contrats->write)
			print_barre_liste("", $page, $_SERVER["PHP_SELF"], "", "", "", "", "", "", 'bill', 0, dolGetButtonTitle("Ajouter un nouveau contrat", '', 'fa fa-plus-circle', $_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=salarie&id='.$fk_user.'&fk_salarie='.$fk_salarie.'&id_convention='.$id_convention.'&id_societe='.$id_societe.'&action=ajouter' , '', 1), '', 0, 0, 0, 1);
			print "<div>";
			print "<h3 >Contrat en cours</h3>";
			print "<table class='tagtable liste' style='width:100%;'>";
			print "<tr class='liste_titre'><td style='padding: 10px;'>N° Contrat</td><td style='padding: 10px;'>Type</td><td style='padding: 10px;'>Date debut";
			print "</td><td style='padding: 10px;'>date fin</td><td style='padding: 10px;'>Salaire Brut(Net)</td><td style='padding: 10px;'>Statut</td></tr>";

		//Partie affichage du Contrat ------------------------------------------------------------------------------------------------------------------------------------------
			//contrat actif
				$sql_contrat = "SELECT * FROM ".MAIN_DB_PREFIX."salarie_contrat WHERE fk_salarie=".$fk_salarie." AND active=1";
				$res_contrat = $db->query($sql_contrat);
				$actl[0] = img_picto("expiré", 'switch_off', 'class="size15x"');
				$actl[1] = img_picto("actif", 'switch_on', 'class="size15x"');
				if($res_contrat){
					$obj_contrat = $db->fetch_object($res_contrat);
					if($obj_contrat->rowid){
						print "<tr class='fieldrequired'>";
						if($user->rights->paiementsalaire->contrats->write)
							print "<td style='width: 200px; padding-top: 20px;'><a href='contrat_information.php?mainmenu=paiementsalaire&leftmenu=salarie&id_societe=".$id_societe."&fk_salarie=".$fk_salarie."&id_convention=".$id_convention."&id=".$fk_user."&id_contrat=".$obj_contrat->rowid."&action=detail'>".($obj_contrat->numero?:"N/A")."</a></td>";
						else print "<td style='width: 200px; padding-top: 20px;'>".($obj_contrat->numero?:"N/A")."</td>";
						$sql_type_contrat = "SELECT libelle FROM ".MAIN_DB_PREFIX."type_contrat WHERE rowid=".$obj_contrat->fk_type_contrat;
						$restype_contrat = $db->query($sql_type_contrat);
						if($restype_contrat)
						$obj_type_contrat = $db->fetch_object($res_type_contrat);

						print "<td>".($obj_type_contrat->libelle?:"N/A")."</td>";
						print "<td style='padding-top: 20px;'>".($obj_contrat->date_embauche?:"N/A")."</td>";
						print "<td style='padding-top: 20px;'>".($obj_contrat->date_fin?:"&#8734;")."</td>";

						$sql_salaire_net  = "SELECT * FROM ".MAIN_DB_PREFIX."salarie_contrat_salaire_net WHERE active=1 AND fk_contrat=".$obj_contrat->rowid;
						$res_salaire_net  = $db->query($sql_salaire_net );
						$obj_salaire_net = $db->fetch_object($res_salaire_net );
						print "<td style='padding-top: 20px;'>".apres_virgule($db, $id_societe,$obj_contrat->salaire_brut)."(".apres_virgule($db, $id_societe, $obj_salaire_net->salaire_net).")</td>";
						print '<td>'.$actl[$obj_contrat->active].'</td></tr>';



						print '</tr>';
					}else print "<tr><td align='center' colspan=6> Pas de Contrat</td></tr>";
				}else print "<tr><td align='center' colspan=6> Pas de Contrat</td></tr>";
			print "</table></div>";


			//les contrats expirés
			print "<br><div>";
			print "<h3 >Contrat Expirés</h3>";
			print "<table class='tagtable liste' style='width:100%;'>";
			print "<tr class='liste_titre'><td style='padding: 10px;'>N° Contrat</td><td style='padding: 10px;'>Type</td><td style='padding: 10px;'>Date debut";
			print "</td><td style='padding: 10px;'>date fin</td><td style='padding: 10px;'>Salaire Net</td><td style='padding: 10px;'>Statut</td></tr>";

				$sql_contrat = "SELECT * FROM ".MAIN_DB_PREFIX."salarie_contrat WHERE fk_salarie=".$fk_salarie." AND active=0";
				$sql_contrat .= " ORDER BY rowid DESC";

				$res_contrat = $db->query($sql_contrat);
				if($res_contrat){
					$num = $db->num_rows($res_contrat);
					$i = 0;
					while($i <$num){
						$obj_contrat = $db->fetch_object($res_contrat);
							print "<tr class='fieldrequired'><td style='width: 200px; padding-top: 20px;'><a href='contrat_information.php?mainmenu=paiementsalaire&leftmenu=salarie&id_societe=".$id_societe."&fk_salarie=".$fk_salarie."&id_convention=".$id_convention."&id=".$fk_user."&id_contrat=".$obj_contrat->rowid."&action=detail'>".($obj_contrat->numero?:"N/A")."</a></td>";

							$sql_type_contrat = "SELECT libelle FROM ".MAIN_DB_PREFIX."type_contrat WHERE rowid=".$obj_contrat->fk_type_contrat;

							$restype_contrat = $db->query($sql_type_contrat);
							if($restype_contrat)
							$obj_type_contrat = $db->fetch_object($res_type_contrat);

							print "<td>".($obj_type_contrat->libelle?:"N/A")."</td>";
							print "<td style='padding-top: 20px;'>".($obj_contrat->date_embauche?:"N/A")."</td>";
							print "<td style='padding-top: 20px;'>".($obj_contrat->date_fin?:"&#8734;")."</td>";

							$sql_salaire_net  = "SELECT * FROM ".MAIN_DB_PREFIX."salarie_contrat_salaire_net WHERE fk_contrat=".$obj_contrat->rowid;
							$sql_salaire_net .= " ORDER BY rowid DESC";
							$res_salaire_net  = $db->query($sql_salaire_net );
							$obj_salaire_net = $db->fetch_object($res_salaire_net );
							print "<td style='padding-top: 20px;'>".apres_virgule($db, $id_societe, $obj_salaire_net->salaire_net)."</td>";
							print '<td>'.$actl[$obj_contrat->active].'</td></tr>';



							print '</tr>';

						$i ++;
					}
					if($num == 0)
						print "<tr><td align='center' colspan=6> Pas de Contrat</td></tr>";

				}else print "<tr><td align='center' colspan=6> Pas de Contrat</td></tr>";
				print "</table></div>";

	}
	}
	$db->free($res_contrat);

	print '<script type="text/javascript">

	var type_contrat = document.getElementById("type_contrat");
	var salaire_brut = document.getElementById("salaire_brut");
	var salaire_net = document.getElementById("salaire_net");
	var date_fin = document.getElementById("date_fin");
	
	if(salaire_brut.value.length == 0){
		salaire_brut.disabled = false;
		salaire_net.disabled = false;
	}else if(salaire_brut.value.length != 0){
		salaire_brut.disabled = false;
		salaire_net.disabled = true;
	}else{
		if(salaire_net.value.length == 0){
			salaire_brut.disabled = false;
			salaire_net.disabled = false;
		}else if(salaire_net.value.length != 0){
			salaire_brut.disabled = true;
			salaire_net.disabled = false;
		}
	}

	function MonSubmitForm() {
		document.monformulaire.submit();
	}

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
			},
			false,
			);

			salaire_brut.addEventListener("change", function () {
				if(salaire_brut.value.length == 0){
					salaire_brut.disabled = false;
					salaire_net.disabled = false;
				}else if(salaire_brut.value.length != 0){
					salaire_brut.disabled = false;
					salaire_net.disabled = true;
				}
			},
			false,
			);

			salaire_net.addEventListener("change", function () {
				if(salaire_net.value.length == 0){
					salaire_brut.disabled = false;
					salaire_net.disabled = false;
				}else if(salaire_net.value.length != 0){
					salaire_brut.disabled = true;
					salaire_net.disabled = false;
				}
			},
			false,
			);

	</script>';

	if(!empty($message))
			print "<script>
			$.jnotify('".$message."', {delay : 5000, fadeSpeed: 500});
			</script>";
}


function simulation_sursalaire($db, $fk_salarie, $fk_user, $contrat_salaire_brut, $contrat_salaire_net, $id_convention, $id_societe){
	$salarie_Sql = "SELECT fk_categorie, fk_echelon FROM ".MAIN_DB_PREFIX."salarie WHERE rowid=".$fk_salarie;
	$salarie_Result = $db->query($salarie_Sql);//= $db->query($salarie_Sql);
	$obj_salarie = $db->fetch_object($salarie_Result);

	//Recherche du salaire de base
	$grilleSql = "SELECT rowid FROM ".MAIN_DB_PREFIX."grille WHERE active=1 AND fk_convention=".$id_convention;
	$grilleResult = $db->query($grilleSql);//= $db->query($grilleSql);
	$obj_grille = $db->fetch_object($grilleResult);

	$salBaseSql = "SELECT salaire_base FROM ".MAIN_DB_PREFIX."grille_categorie_echelon_salaire_base WHERE fk_grille=".$obj_grille->rowid." AND fk_categorie=".$obj_salarie->fk_categorie." AND fk_echelon=".$obj_salarie->fk_echelon;
	$salBaseResult = $db->query($salBaseSql);//= $db->query($covSql);
	$objSalBase = $db->fetch_object($salBaseResult);
	$salaire_base = $objSalBase->salaire_base;

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

	if(!empty($contrat_salaire_brut)){
			$sursalaire = $contrat_salaire_brut - $mon_salaire_brut;
			$mon_brut_cotis = $salaire_brut_cotisable + $sursalaire;
			$mon_brut_imp = $salaire_brut_imposable + $sursalaire;
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
					if($obj_prest_type->rowid != 6)
						$inps += $value*$mon_brut_cotis/100;

			}
			$mon_brut_imp -= $inps;
			$its = its_salarie($db, $fk_salarie, $mon_brut_imp);
			$retenu_taxe = $its[2];

			$mon_net = $contrat_salaire_brut - $retenu_prest_empl - $retenu_taxe;

			//preparation pour l'envoie des données
			$salaire_brut_cotisable = $mon_brut_cotis ;
		$salaire_brut_imposable = $mon_brut_imp;
		$salaire_brut = $mon_salaire_brut;

		//envoie des donnée
	$array = array();

	$array[] = ($sursalaire > 0 ? $sursalaire :0);
	$array[] = $contrat_salaire_brut;
	$array[] = $mon_net;

	return $array;

	}else{
		$net  = $contrat_salaire_net;
		while ($fin == false && $contrat_salaire_net){
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
					if($obj_prest_type->rowid != 6)
						$inps += $value*$mon_brut_cotis/100;

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
		$sursalaire = $mon_salaire_brut -  $salaire_brut;
		$array = array();
		$array[] = $sursalaire;
		$array[] = $mon_salaire_brut;
		$array[] = $net;

		return $array;
	}

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
