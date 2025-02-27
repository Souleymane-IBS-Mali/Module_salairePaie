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




llxHeader("", "Paiement | Salaire");
//Titre
print load_fiche_titre($langs->trans("Contrat"), '', '');
$id_societe = GETPOST("id_societe","int");
$fk_user = GETPOST("id","int");
$fk_salarie = GETPOST("fk_salarie", "int");
$id_convention = GETPOST("id_convention","int");
$id_contrat = GETPOST("id_contrat","int");

if($user->id !=1 && !$user->rights->paiementsalaire->contrats->write){
	print "<h2> Vous n\'avez pas ce droit </h2>";
}else{
	// Recuperation des information après le clique sur l'onglet Salaire au niveau du module user

	$info = "";
	$head = salaire_Head($fk_salarie, $fk_user, $id_societe, $id_convention);
	print dol_get_fiche_head($head, 'contrat', "", -1, '');
	$action = GETPOST("action", "alpha")?: "detail";


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
		//cloture du contrat
		if($action == "cloturer_contrat"){
			$id_contrat = GETPOST("id_contrat", "int");
			$sql_update = 'UPDATE '.MAIN_DB_PREFIX.'salarie_contrat SET active=0';
			$sql_update .= ' WHERE rowid='.$id_contrat;
			if($db->query($sql_update)){
				$message = "Contrat cloturé avec succès";
			}else{
				$message = "Un problème est survenu";
			}
		}
		//Modification d'un contrat
		if($action == "save_modifier_contrat"){
			$id_contrat = GETPOST("id_contrat", "int");
			$numero = GETPOST("numero_contrat", "alpha");
			$type = GETPOST("type_contrat", "int");
			$date_emb = GETPOST("date_embauche");
			$date_sign = GETPOST("date_signature");
			$date_fin = GETPOST("date_fin");
			$salaire_net = GETPOST("salaire_net", "int");
			$encien_document_contrat = GETPOST("encien_document_contrat");
			
			if(!empty($numero)){
				$result = $db->query("SELECT rowid FROM ".MAIN_DB_PREFIX."salarie_contrat WHERE numero = '".$numero."';");
				$exist = $db->fetch_object($result);
				if(!empty($exist->rowid) && $exist->rowid !=  $id_contrat)
					$message = 'Ce "NUMERO CONTRAT" '.$numero.' existe déjà<br>';
			}
			if(empty($message)){
				$tab = simulation_sursalaire($db, $fk_salarie, $fk_user, $salaire_net, $id_convention, $id_societe);
				$sursalaire = $tab[0];
				$text = $tab[1];
				$salaire_brut = $tab[2];

				if($sursalaire < 0)
					$info = "<h3><mark>Pour avoir ce salaire, il faut un sursalaire négative.<br>Le sursalaire a été mis à zéro 0. Il faut faire une simulation</h3></mark>";

				$sursalaire = $tab[0]>0?$tab[0]:0;

				$sql_update = 'UPDATE '.MAIN_DB_PREFIX.'salarie_contrat SET salaire_brut='.$salaire_brut.', ';
				$mettre_virgule = 0;
				if(!empty($numero)){
					$sql_update .= 'numero="'.$numero.'"';
					$mettre_virgule ++;
				}else $message .= 'Le champ "NUMERO CONTRAT" est obligatoire<br>';

				if(!empty($type) && $type != '0'){
					if($mettre_virgule > 0){
						$sql_update .= ', fk_type_contrat='.$type;
					}else{
						$sql_update .= 'fk_type_contrat='.$type;
						$mettre_virgule ++;
					}
				}else $message .= 'Le champ "TYPE CONTRAT" est obligatoire<br>';

				if(!empty($date_sign)){
					if($mettre_virgule > 0){
						$sql_update .= ', date_signature="'.$date_sign.'"';
					}else{
						$sql_update .= 'date_signature="'.$date_sign.'"';
						$mettre_virgule ++;
					}
				}else $message .= 'Le champ "DATE SIGNATURE" est obligatoire<br>';

				if(!empty($date_emb)){
					if($mettre_virgule > 0){
						$sql_update .= ', date_embauche="'.$date_emb.'"';
					}else{
						$sql_update .= 'date_embauche="'.$date_emb.'"';
						$mettre_virgule ++;
					}
				}else $message .= 'Le champ "DATE EMBAUCHE" est obligatoire<br>';

				if($type == "2")
					unset($date_fin);

				if(!empty($date_fin)){
					if($mettre_virgule > 0){
						$sql_update .= ', date_fin="'.$date_fin.'"';
					}else{
						$sql_update .= 'date_fin="'.$date_fin.'"';
						$mettre_virgule ++;
					}
				}else if($type != 2)
					$message .= 'Le champ "DATE FIN" est obligatoire Tant que le Type n\'est CDI<br>';

					$sql_update .= ' WHERE rowid='.$id_contrat;
					if(empty($message)){
		
						if($db->query($sql_update)){
							
							$message = "Contrat modifié avec succès";
		
							//Dateemployement
							$sql_update_user = 'UPDATE '.MAIN_DB_PREFIX.'user SET dateemployment="'.$date_emb.'" WHERE rowid='.$fk_user;
							$db->query($sql_update_user);

							//desactivation de l'ancien salaire net et sursalaire
							$sql_update_sal_net = 'UPDATE '.MAIN_DB_PREFIX.'salarie_contrat_salaire_net SET active=0, date_limit=now() WHERE fk_contrat='.$id_contrat;
							$db->query($sql_update_sal_net);

							//Insertion du nouveau salaire net et sursalaire
							$sql = 'INSERT INTO '.MAIN_DB_PREFIX.'salarie_contrat_salaire_net (fk_contrat, salaire_net, sursalaire, date_debut, active)';
							$sql .= 'VALUES('.$id_contrat.',"'.round(str_replace(' ', '', $salaire_net)).'","'.round(str_replace(' ', '', $sursalaire)).'",now(),1)';
							$db->query($sql);

							//modification de sursalaire dans la table salarié
							$sql_update_sur = 'UPDATE '.MAIN_DB_PREFIX.'salarie SET sursalaire="'.round(str_replace(' ', '', $sursalaire)).'" WHERE rowid='.$fk_salarie;
							$db->query($sql_update_sur);

							$sql_select = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$user->id;
									$obj = $db->fetch_object($db->query($sql_select));

									$sql_select_us = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$fk_user;
									$obj_us = $db->fetch_object($db->query($sql_select_us));

									$soc_sql = "SELECT nom FROM ".MAIN_DB_PREFIX."societe WHERE rowid=".$id_societe;
									$soc_res = $db->query($soc_sql);//= $db->query($covSql);
									$obj_soc = $db->fetch_object($soc_res);
									//On garde la trace de l'action
									$action_effectue = "Modification du contrat : sursalaire (".$sursalaire.") et salaire net (".$salaire_net.") de ".$obj_us->firstname." ".$obj_us->lastname." de la société ".$obj_soc->nom;
									$sql_log = 'INSERT INTO '.MAIN_DB_PREFIX.'log (fk_user, nom, prenom, quand, action_effectue, object_concerne)';
									$sql_log .= ' VALUES('.$user->id.', "'.$obj->lastname.'","'.$obj->firstname.'",now(),"'.$action_effectue.'","Modification contrat")';
									$db->query($sql_log);
							$action = 'detail';
						}else{
							$action = "modifier_contrat";
							$message = "Un problème est survenu";
		
						}
						print $db->error();
					}else $action = "modifier_contrat";

			}else{
				$message .=  $db->error().'<br>';
				$action = "modifier_contrat";
			}
		}

//Enregistrement du fichier du contrat
		if($action == "save_file"){
			$id_contrat = GETPOST("id_contrat", "int");
			if (isset($_FILES['fichier_contrat']) && $_FILES['fichier_contrat']['error'] == 0) {
				$nom = $_FILES['fichier_contrat']['name'];
				$chemin = $_FILES['fichier_contrat']['tmp_name'];
				$extension = strrchr($nom,".");
				$extension_autorisees = array('.JPG','.jpg','.png','.PNG','.jpeg','.JPEG','.pdf','.PDF');
				$destination = './documents_contrat/contrat'.$fk_salarie.'__'.date('d_m_y_h_i_s').''.$extension;
				$nomDossier = 'documents_contrat';
				$true = false;
				// Vérifier si le dossier n'existe pas déjà
				if (!file_exists($nomDossier))
					if (mkdir($nomDossier, 0777, true)) {
						$trouve = true;
					}
				if(in_array($extension,$extension_autorisees)){
					if($_FILES['fichier_contrat']['size']<=1000000){
						if(move_uploaded_file($chemin,$destination) && $trouve){

							$sql_contrat = "SELECT numero, fichier_contrat FROM ".MAIN_DB_PREFIX."salarie_contrat WHERE rowid=".$id_contrat;
							$res_contrat = $db->query($sql_contrat);
							$obj_contrat = $db->fetch_object($res_contrat);
							unlink($obj_contrat->fichier_contrat);

							$sql_update = 'UPDATE '.MAIN_DB_PREFIX.'salarie_contrat SET fichier_contrat="'.$destination.'"';
							$sql_update .= ' WHERE rowid='.$id_contrat;
							if($db->query($sql_update)){
								$action = "detail";
								$message = "Fichier du N°".$obj_contrat->numero." est modifié avec succès";

								$sql_select = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$user->id;
								$obj = $db->fetch_object($db->query($sql_select));

								$sql_select_us = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$fk_user;
								$obj_us = $db->fetch_object($db->query($sql_select_us));

								//On garde la trace de l'action
								$action_effectue = "Modification du fichier du contrat à (".$destination.") de ".$obj_us->firstname." ".$obj_us->lastname." Dans le contrat";
								$sql_log = 'INSERT INTO '.MAIN_DB_PREFIX.'log (fk_user, nom, prenom, quand, action_effectue, object_concerne)';
								$sql_log .= ' VALUES('.$user->id.', "'.$obj->lastname.'","'.$obj->firstname.'",now(),"'.$action_effectue.'","Modification")';
								$db->query($sql_log);
							}else{
								$action = "modifier_fichier";
								$message = "Un problème est survenu";
							}
						}
					}else $message .= "Un problème est intervenu lors du Chargement du fichier";
					}else $message .= "La taille du fichier doit être inférieur à 1Mo";

				}else $message .= "Extension de fichier non autorisée<br><br>Les extensions autorisées son : JPG, PNG, JPEG et PDF";
		}

		//Enregistrement de l'anciennété
		if(GETPOST('action1', 'alpha') == "save_anciennete"){
            $salSql = "SELECT date_anciennete FROM ".MAIN_DB_PREFIX."salarie where rowid=".$fk_salarie;
            $result = $db->query($salSql);
            $salarie = $db->fetch_object($result);
            $date = GETPOST("date_anciennete");
            if(empty($date)){
                $message = "Veuillez saisir une date";
            }
            if(empty($message)){
                $sql_update = 'UPDATE '.MAIN_DB_PREFIX.'salarie SET date_anciennete="'.$date.'" WHERE rowid='.$fk_salarie;
                if($db->query($sql_update)){
                    $message = "Date d\'ancienneté modifiée avec succès";

					$sql_select = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$user->id;
					$obj = $db->fetch_object($db->query($sql_select));

					$sql_select_us = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$fk_user;
					$obj_us = $db->fetch_object($db->query($sql_select_us));

					//On garde la trace de l'action
					$action_effectue = "Modification Ancienneté (".$salarie->date_anciennete." à ".$date.") de ".$obj_us->firstname." ".$obj_us->lastname." Dans le contrat";
					$sql_log = 'INSERT INTO '.MAIN_DB_PREFIX.'log (fk_user, nom, prenom, quand, action_effectue, object_concerne)';
					$sql_log .= ' VALUES('.$user->id.', "'.$obj->lastname.'","'.$obj->firstname.'",now(),"'.$action_effectue.'","Modification")';
					$db->query($sql_log);
                }else{
                    $message = "Un problème est survenu";
                }
            }

			$action = "detail";
        }

		$head_contrat = salarie_contrat_Head($fk_salarie, $fk_user, $id_societe, $id_convention, $id_contrat);
		print dol_get_fiche_head($head_contrat, 'information', "", -1, '');
		print $info;
		if($action == "detail"){
			$id_contrat = GETPOST("id_contrat", "int");
			$sql_contrat = "SELECT * FROM ".MAIN_DB_PREFIX."salarie_contrat WHERE rowid=".$id_contrat;
			$res_contrat = $db->query($sql_contrat);
			$obj_contrat = $db->fetch_object($res_contrat);

			print "<table class='tagtable liste'>";
			print "<tr class='fieldrequired'><td style='width: 200px; padding-top: 15px;'>Numéro Contrat</td><td style='width: 200px; padding-top: 20px;'>".$obj_contrat->numero."</td></tr>";

			print "<tr class='fieldrequired'><td style='width: 200px; padding-top: 15px;'>Type Contrat</td><td>";
			$sql_type_contrat = "SELECT rowid,libelle FROM ".MAIN_DB_PREFIX."type_contrat WHERE rowid=".$obj_contrat->fk_type_contrat;
			$restype_contrat = $db->query($sql_type_contrat);
			if($restype_contrat){
				$nb = $db->num_rows($restype_contrat);
				if($nb > 0){
					$obj_typ_cont = $db->fetch_object($restype_contrat);
					print $obj_typ_cont->libelle;

				}
			}
			print"</td></tr>";

			//Récupération de l'anciennété
			$salSql = "SELECT date_anciennete FROM ".MAIN_DB_PREFIX."salarie where rowid=".$fk_salarie;
			$result = $db->query($salSql);
			$salarie = $db->fetch_object($result);

			print "<tr class='fieldrequired'><td style='width: 200px; padding-top: 15px;'>Date D'embauche</td><td style='width: 200px; padding-top: 20px;'>".$obj_contrat->date_embauche."</td></tr>";
			print "<tr class='fieldrequired'><td style='width: 200px; padding-top: 15px;'>Date Signature</td><td style='width: 200px; padding-top: 20px;'>".$obj_contrat->date_signature."</td></tr>";
			if(GETPOST('action1', 'alpha') == "edit_anciennete"){
				print '<div><form name="add" method="POST" action="'.$_SERVER['PHP_SELF'].'?mainmenu=paiementsalaire&leftmenu=salarie&id_societe='.$id_societe.'&fk_salarie='.$fk_salarie.'&id='.$fk_user.'&id_convention='.$id_convention.'&id_contrat='.$obj_contrat->rowid.'">';
				print '<input type="hidden" name="token" value="'.newToken().'">';
				print '<input type="hidden" name="action1" value="save_anciennete">';
				print '<tr class="pair">';
				print '<td style="padding: 10px; width: 200px;">Date Ancienneté'.info_admin($langs->trans("La date avec laquelle se calcul l'ancienneté"), 1).'</td>';
				print '<td style="padding: 10px; width: 200px;"><input type="date" name="date_anciennete" value="'.(GETPOST("date_anciennete")?:$salarie->date_anciennete).'" autofocus>
				<input class="button" type="submit" value="Valider" >';
				print '</form>';
				print '<a class="reposition editfielda button" href="'.$_SERVER['PHP_SELF'].'?mainmenu=paiementsalaire&leftmenu=salarie&id_societe='.$id_societe.'&fk_salarie='.$fk_salarie.'&id='.$fk_user.'&id_convention='.$id_convention.'">Annuler</a>';
				print '</td></tr>';

			}else{
				print '<tr class="impair">';
				print '<td style="padding: 10px; width: 200px;">Date Ancienneté'.info_admin($langs->trans("La date avec laquelle l'ancienneté doit être calculé"), 1).'</td>';
				print '<td style="padding: 10px; width: 200px;"><input type="date" value="'.$salarie->date_anciennete.'" disabled>';

				if($user->rights->paiementsalaire->contrats->write)
					print '<a class="reposition editfielda" href="'.$_SERVER['PHP_SELF'].'?mainmenu=paiementsalaire&leftmenu=salarie&id_societe='.$id_societe.'&fk_salarie='.$fk_salarie.'&id='.$fk_user.'&id_convention='.$id_convention.'&id_contrat='.$obj_contrat->rowid.'&aciton=detail&action1=edit_anciennete">'.img_edit('Modifier','').'</a>';
				else print img_edit('Permission manquantes','');

					print '</td>';
				print '</tr>';
			}
			print "<tr class='fieldrequired'><td style='width: 200px; padding-top: 15px;'>Date Fin</td><td style='width: 200px; padding-top: 20px;'>".($obj_contrat->date_fin?:"&#8734;")."</td></tr>";
			print "<tr class='fieldrequired'><td style='width: 200px; padding-top: 15px;'>Salaire brut</td><td style='width: 200px; padding-top: 20px;'>".apres_virgule($db, $id_societe, round($obj_contrat->salaire_brut?:0,2))."</td></tr>";

			$sql_salaire_net  = "SELECT * FROM ".MAIN_DB_PREFIX."salarie_contrat_salaire_net WHERE active = 1 AND fk_contrat=".$obj_contrat->rowid;
			$res_salaire_net  = $db->query($sql_salaire_net );
			$obj_salaire_net = $db->fetch_object($res_salaire_net );
			print "<tr class='fieldrequired'><td style='width: 200px; padding-top: 15px;'>Salaire net</td><td style='width: 200px; padding-top: 20px;'>".apres_virgule($db, $id_societe, $obj_salaire_net->salaire_net?:0)."</td></tr>";

			print "<tr class='fieldrequired'><td style='width: 200px; padding-top: 15px;'>Fichier du Contrat</td><td style='width: 200px; padding-top: 20px;'><a target='_blank' href='./".$obj_contrat->fichier_contrat."'>".img_picto('Fichier du Contrat N° '.$obj_contrat->numero, 'title_document', 'class="paddingright pictofixedwidth valignmiddle"')."</a>";
			if($obj_contrat->active == 1)
				print "&nbsp; &nbsp; <a title='Changer le fichier de contrat' href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenu=salarie&id=".$fk_user."&fk_salarie=".$fk_salarie."&id_convention=".$id_convention."&id_societe=".$id_societe."&id_contrat=".$obj_contrat->rowid."&action=modifier_fichier'>".img_picto('','upload', 'class="paddingright pictofixedwidth valignmiddle"')."</a></td></tr>";
			print '</table>';

			if($user->rights->paiementsalaire->contrats->write){
				if($obj_contrat->active == 1){
					print '<a class="butActionDelete" style="float: right; margin-right: 10px" href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=salarie&id='.$fk_user.'&fk_salarie='.$fk_salarie.'&id_convention='.$id_convention.'&id_societe='.$id_societe.'&id_contrat='.$obj_contrat->rowid.'&action=cloturer_contrat" class="button">Cloturer</a>';
					print '<a class="butAction" style="float: right; margin-right: 10px" href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=salarie&id='.$fk_user.'&fk_salarie='.$fk_salarie.'&id_convention='.$id_convention.'&id_societe='.$id_societe.'&id_contrat='.$obj_contrat->rowid.'&action=modifier_contrat" class="button">Modifier</a>';
				}
			}else
				print '<button class="butActionRefused" style="margin-top:20px; float: right; margin-right: 10px" title="Vous n\'avez pas cette permission" >Modifier</button>';
	}elseif($action == "modifier_contrat"){
			$id_contrat = GETPOST("id_contrat", "int");
			print ' <form name="ajouter" method="POST" action="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=salarie&id='.$fk_user.'&fk_salarie='.$fk_salarie.'&id_convention='.$id_convention.'&id_societe='.$id_societe.'&id_contrat='.$id_contrat.'">';
			print '<input type="hidden" name="token" value="'.newToken().'">';
			print '<input type="hidden" name="action" value="save_modifier_contrat">';


			$sql_contrat = "SELECT * FROM ".MAIN_DB_PREFIX."salarie_contrat WHERE rowid=".$id_contrat;
			$res_contrat = $db->query($sql_contrat);
			$obj_contrat = $db->fetch_object($res_contrat);

			print "<table>";
			print "<tr class='fieldrequired'><td style='width: 200px; padding-top: 15px;'>Numéro Contrat</td><td style='width: 200px; padding-top: 20px;'><input type='text' name='numero_contrat' id='numero_contrat' value='".$obj_contrat->numero."' autofocus></td></tr>";

			print "<tr class='fieldrequired'><td style='width: 200px; padding-top: 15px;'>Type Contrat</td><td><select
			name='type_contrat' id='type_contrat' value='".GETPOST("type_contrat", "int")."' >";
			print "<option value='0'></option>";
			$sql_type_contrat = "SELECT rowid,libelle FROM ".MAIN_DB_PREFIX."type_contrat";
			$restype_contrat = $db->query($sql_type_contrat);
			if($restype_contrat){
				$nb = $db->num_rows($restype_contrat);
				$i =0;
				while ($i < $nb) {
					$obj_typ_cont = $db->fetch_object($restype_contrat);
					if($obj_contrat->fk_type_contrat == $obj_typ_cont->rowid)
						print "<option value='".$obj_typ_cont->rowid."' selected>".$obj_typ_cont->libelle."</option>";
					else
						print "<option value='".$obj_typ_cont->rowid."'>".$obj_typ_cont->libelle."</option>";
					$i ++;
				}
			}
			print"</td></tr>";
			print "<tr class='fieldrequired'><td style='width: 200px; padding-top: 15px;'>Date D'embauche</td><td style='width: 200px; padding-top: 20px;'>
			<input type='date' value='".$obj_contrat->date_embauche."' name='date_embauche' id='date_embauche'>";
			print "</td></tr>";
			print "<tr class='fieldrequired'><td style='width: 200px; padding-top: 15px;'>Date Signature</td><td style='width: 200px; padding-top: 20px;'>
			<input type='date' value='".$obj_contrat->date_signature."' name='date_signature' id='date_signature'>";
			print "</td></tr>";
			print "<tr class='fieldrequired'><td style='width: 200px; padding-top: 15px;'>Date Fin</td><td style='width: 200px; padding-top: 20px;'>
			<input type='date' value='".$obj_contrat->date_fin."' name='date_fin' id='date_fin'>";
			print "</td></tr>";

			$sql_salaire_net  = "SELECT * FROM ".MAIN_DB_PREFIX."salarie_contrat_salaire_net WHERE active=1 AND fk_contrat=".$obj_contrat->rowid;
			$res_salaire_net  = $db->query($sql_salaire_net );
			$obj_salaire_net = $db->fetch_object($res_salaire_net );
			print '<input type="hidden" name="salaire_net_active" value="'.$obj_salaire_net->salaire_net.'">';
			print "<tr class='fieldrequired'><td style='width: 200px; padding-top: 15px;'>Salaire net</td><td style='width: 200px; padding-top: 20px;'>
				<input type='text' value='".$obj_salaire_net->salaire_net."' name='salaire_net' id='salaire_net'>";
			print "</td></tr>";

			print '<tr>';
			print '<td style=" padding-right: 30px; padding-bottom: 30px"></td><td style="padding-top: 30px; width: 300px;"><input class="button" type="submit" value="Enregistrer">';
			print'</form>';
			print '<a href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=salarie&id='.$fk_user.'&fk_salarie='.$fk_salarie.'&id_convention='.$id_convention.'&id_societe='.$id_societe.'&id_contrat='.$id_contrat.'&action=detail" class="button">Annuler</a></td></tr>';
			print '</table>';
	}elseif($action == "modifier_fichier"){
			$id_contrat = GETPOST("id_contrat", "int");
			print '<table>';
			print ' <form name="ajouter" method="POST" action="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=salarie&id='.$fk_user.'&fk_salarie='.$fk_salarie.'&id_convention='.$id_convention.'&id_societe='.$id_societe.'&id_contrat='.$id_contrat.'" enctype="multipart/form-data">';
			print '<input type="hidden" name="token" value="'.newToken().'">';
			print '<input type="hidden" name="action" value="save_file">';
			print "<tr class='fieldrequired'><td >Fichier du Contrat&nbsp;&nbsp;&nbsp;</td><td><input type='file' name='fichier_contrat' id='fichier_contrat' ><1Mo</td></tr>";

			print '<tr>';
			print '<td style=" padding-right: 30px; padding-bottom: 30px"></td><td style="padding-top: 30px; width: 300px;"><input class="button" type="submit" value="Enregistrer">';
			print'</form>';
			print '<a href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=salarie&id='.$fk_user.'&fk_salarie='.$fk_salarie.'&id_convention='.$id_convention.'&id_societe='.$id_societe.'&id_contrat='.$id_contrat.'" class="button">Annuler</a></td></tr>';
			print '</table>';
	}else{
		if($user->rights->paiementsalaire->contrats->write)
			print_barre_liste("", $page, $_SERVER["PHP_SELF"], "", "", "", "", "", "", 'bill', 0, dolGetButtonTitle("Ajouter un nouveau contrat", '', 'fa fa-plus-circle', $_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=salarie&id='.$fk_user.'&fk_salarie='.$fk_salarie.'&id_convention='.$id_convention.'&id_societe='.$id_societe.'&action=ajouter' , '', 1), '', 0, 0, 0, 1);

		print "<div>";
			print "<h3 >Contrat en cours</h3>";
			print "<table class='tagtable liste' style='width:100%;'>";
			print "<tr class='liste_titre'><td style='padding: 10px;'>N° Contrat</td><td style='padding: 10px;'>Type</td><td style='padding: 10px;'>Date debut";
			print "</td><td style='padding: 10px;'>date fin</td><td style='padding: 10px;'>Salaire Net</td><td style='padding: 10px;'>Opération</td></tr>";

		//Partie affichage du Contrat ------------------------------------------------------------------------------------------------------------------------------------------
				$sql_contrat = "SELECT * FROM ".MAIN_DB_PREFIX."salarie_contrat WHERE fk_salarie=".$fk_salarie." AND active=1";
				$res_contrat = $db->query($sql_contrat);
				$actl[0] = img_picto("actif", 'switch_off', 'class="size15x"');
				$actl[1] = img_picto("expiré", 'switch_on', 'class="size15x"');
				if($res_contrat){
					$obj_contrat = $db->fetch_object($res_contrat);
					if($obj_contrat->rowid){
						print "<tr class='fieldrequired'><td style='width: 200px; padding-top: 15px;'>".($obj_contrat->numero?:"N/A")."</td>";

						$sql_type_contrat = "SELECT libelle FROM ".MAIN_DB_PREFIX."type_contrat WHERE rowid=".$obj_contrat->fk_type_contrat;
						$restype_contrat = $db->query($sql_type_contrat);
						if($restype_contrat)
						$obj_type_contrat = $db->fetch_object($res_type_contrat);

						print "<td>".($obj_type_contrat->libelle?:"N/A")."</td>";
						print "<td style='padding-top: 20px;'>".($obj_contrat->date_embauche?:"N/A")."</td>";
						print "<td style='padding-top: 20px;'>".($obj_contrat->date_fin?:"&#8734;")."</td>";

						$sql_salaire_net  = "SELECT * FROM ".MAIN_DB_PREFIX."salarie_contrat_salaire_net WHERE fk_contrat=".$obj_contrat->rowid;
						$res_salaire_net  = $db->query($sql_salaire_net );
						$obj_salaire_net = $db->fetch_object($res_salaire_net );
						print "<td style='padding-top: 20px;'>".($obj_salaire_net->salaire_net)."</td>";
						print "<td style='padding-top: 20px;'>".$actl[$obj_contrat->active];
						if($user->rights->paiementsalaire->contrats->write)
							print '<a href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=salarie&id='.$fk_user.'&fk_salarie='.$fk_salarie.'&id_convention='.$id_convention.'&id_societe='.$id_societe.'&action=modifier_contrat&id_contrat='.$obj_contrat->rowid.'" >    '.img_edit("Modifier ce contrat").'</a></td></tr>';
						else
							print '<button class="butActionRefused" title="Vous n\'avez pas cette permission" >Modifier</button>';

						print '</tr>';
					}else print "<tr><td align='center' colspan=6> Pas de Contrat</td></tr>";
				}else print "<tr><td align='center' colspan=6> Pas de Contrat</td></tr>";
			print "</table></div>";


			//les contrats expirés

			print "<br><div>";
			print "<h3 >Contrat Expirés</h3>";
			print "<table class='tagtable liste' style='width:100%;'>";
			print "<tr class='liste_titre'><td style='padding: 10px;'>N° Contrat</td><td style='padding: 10px;'>Type</td><td style='padding: 10px;'>Date debut";
			print "</td><td style='padding: 10px;'>date fin</td><td style='padding: 10px;'>Salaire Net</td><td style='padding: 10px;'>Opération</td></tr>";

				$sql_contrat = "SELECT * FROM ".MAIN_DB_PREFIX."salarie_contrat WHERE fk_salarie=".$fk_salarie." AND active=0";
				$res_contrat = $db->query($sql_contrat);
				$acts[0] = "activate";
				$acts[1] = "disable";
				$actl[0] = img_picto($langs->trans("Disabled"), 'switch_off', 'class="size15x"');
				$actl[1] = img_picto($langs->trans("Activated"), 'switch_on', 'class="size15x"');
				if($res_contrat){
					$num = $db->num_rows($res_contrat);
					$i = 0;
					while($i <$num){
						$obj_contrat = $db->fetch_object($res_contrat);
							print "<tr class='fieldrequired'><td style='width: 200px; padding-top: 15px;'><a href='contrat_information.php?mainmenu=paiementsalaire&leftmenu=salarie&id_societe=".$id_societe."&fk_salarie=".$fk_salarie."&id_convention=".$id_convention."&id=".$fk_user."&id_contrat=".$obj_contrat->rowid."&action=detail'>".($obj_contrat->numero?:"N/A")."</a></td>";

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
							print "<td style='padding-top: 20px;'>".apres_virgule($db, $id_societe, $obj_salaire_net->salaire_net?:0)."</td>";
							print "<td style='padding-top: 20px;'>".$actl[$obj_contrat->active]."</a></td>";



							print '</tr>';

						$i ++;
					}
					if($num == 0)
						print "<tr><td align='center' colspan=6> Pas de Contrat</td></tr>";

				}else print "<tr><td align='center' colspan=6> Pas de Contrat</td></tr>";
				print "</table></div>";


	}

	$db->free();

	}

	print '<script>
	var type_contrat = document.getElementById("type_contrat");
	var date_fin = document.getElementById("date_fin");

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
	</script>';


	if(!empty($message))
			print "<script>
			$.jnotify('".$message."', {delay : 5000, fadeSpeed: 500});
			</script>";
}




function simulation_sursalaire($db, $fk_salarie, $fk_user, $contrat_salaire_net, $id_convention, $id_societe){
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
	$net  = $contrat_salaire_net;
	while ($fin == false && $net){
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
			$text .= '<td style="background-color: gray;"><input type="text" id="sursalaire" value="'.(apres_virgule($db, $id_societe, $sursalaire?:0)).'" name="sursalaire" ></td>';
			$text .= '</tr>';
			$text .= "</table>";


		}

		$text .= "</div>";
		$array = array();
		$array[] = $sursalaire;
		$array[] = $text;
		$array[] = $mon_salaire_brut;
		return $array;
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
