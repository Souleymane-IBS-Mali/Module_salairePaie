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
//require_once DOL_DOCUMENT_ROOT.'/core/class/html.formpaiementsalaire.class.php';
//require_once DOL_DOCUMENT_ROOT.'/custom/paiementsalaire/core/modules/modPaiementSalaire.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/paiementsalaire/lib/paiementsalaire.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';

//$PaiementSalaire = new modPaiementSalaire($db);

$monform = new Form($db);
llxHeader('', "Paiement | Salaire");
//Titre 
//table des champs et labels
$action = GETPOST('action','aZ09');
$message = '';
if(empty($action))	
	$action = 'create';

	if($action == "add"){
		$nom = GETPOST("nom");
		$desc = GETPOST("desc");

		if(empty($nom))
			$message = 'Le champ "NOM" est obligatoire<br>';
		//if(empty($message))
		$destination = "";
		if (isset($_FILES['document_convention']) && $_FILES['document_convention']['error'] == 0 && empty($message)) {
			$nom_file = $_FILES['document_convention']['name'];
			$chemin = $_FILES['document_convention']['tmp_name'];
			$extension = strrchr($nom_file,".");
			$extension_autorisees = array('.JPG','.jpg','.png','.PNG','.jpeg','.JPEG','.pdf','.PDF');
			$destination = './convention/documents/'.$nom.''.date('d_m_y_h_i_s').''.$extension;
			if(in_array($extension,$extension_autorisees)){
				if($_FILES['document_convention']['size']<=10000000){
					if(move_uploaded_file($chemin,$destination)){
						$sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'convention (nom, commentaire, document_convention) VALUES("'.$nom.'","'.$desc.'","'.$destination.'")';
						$res = $db->query($sql_insert);
						if($res){
							//--------------------------------------------
							$sql_select = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$user->id;
							$obj = $db->fetch_object($db->query($sql_select));

							//On garde la trace de l'action
							$action_effectue = "Création de convention ".$nom;
							$sql_log = 'INSERT INTO '.MAIN_DB_PREFIX.'log (fk_user, nom, prenom, quand, action_effectue, object_concerne)';
							$sql_log .= ' VALUES('.$user->id.', "'.$obj->lastname.'","'.$obj->firstname.'",now(),"'.$action_effectue.'","Création convention")';
							$db->query($sql_log);
							//--------------------------------------------
							$message = 'Convention enregistrée avec succès';
							$action = "afficher";
						}else{
							$message = 'Un problème est survenu';
							$action = "create";
						}
					}else $message .= "Un problème est intervenu lors du Chargement du fichier";
				}else $message .= "La taille du fichier doit être inférieur à 10Mo";

			}else $message .= "Extension de fichier non autorisée<br><br>Les extensions autorisées son : JPG, PNG, JPEG et PDF";
		}else{
			 $message .= 'Veuillez joindre un fichier';
			 $action = "create";
		}

	}

	if($action == "save_edit_convention"){

		$nom = GETPOST("nom");
		$desc = GETPOST("desc");
		$id_convention = GETPOST("id_convention");
		$encien_document_convention = GETPOST("encien_document_convention");

		$sql_old = "SELECT nom FROM ".MAIN_DB_PREFIX."convention WHERE rowid=".$id_convention;
		$obj_old = $db->fetch_object($db->query($sql_old));

		$sql_update = 'UPDATE '.MAIN_DB_PREFIX.'convention SET ';
		$mettre_virgule = 0;
		if(!empty($nom)){
			$sql_update .= 'nom="'.$nom.'"';
			$mettre_virgule ++;
		}else
			$message = 'Le champ "NOM" est obligatoire';
		
		if(!empty($desc))
			if($mettre_virgule > 0){
				$sql_update .= ', commentaire="'.$desc.'"';
			}else{
				$sql_update .= 'commentaire="'.$desc.'"';
				$mettre_virgule ++;
			}

		//if(empty($message))
		$destination = "";
		if (isset($_FILES['document_convention']) && $_FILES['document_convention']['error'] == 0) {
			$nom_file = $_FILES['document_convention']['name'];
			$chemin = $_FILES['document_convention']['tmp_name'];
			$extension = strrchr($nom_file,".");
			$extension_autorisees = array('.JPG','.jpg','.png','.PNG','.jpeg','.JPEG','.pdf','.PDF');
			$destination = './convention/documents/'.$nom.''.date('d_m_y_h_i_s').''.$extension;
			$nomDossier = './convention/documents';
				$true = false;
				// Vérifier si le dossier n'existe pas déjà
				if (!file_exists($nomDossier)) 
					if (mkdir($nomDossier, 0777, true)) {
						$trouve = true;
					}
			if(in_array($extension,$extension_autorisees)){
				if($_FILES['document_convention']['size']<=10000000){
					if(!empty($encien_document_convention) && $encien_document_convention != $destination)
						if(move_uploaded_file($chemin,$destination) && $trouve){
							if($mettre_virgule > 0){
								$sql_update .= ',document_convention="'.$destination.'"';
							}else{
								$sql_update .= 'document_convention="'.$destination.'"';
							}
							unlink($encien_fichier_accord);
						}else $message .= "Un problème est intervenu lors du Chargement du fichier";
				}else $message .= "La taille du fichier doit être inférieur à 10Mo";

			}else $message .= "Extension de fichier non autorisée<br><br>Les extensions autorisées son : JPG, PNG, JPEG et PDF";
		}//else $message .= "Veuillez joindre un fichier";

		if(empty($message)){
			$sql_update .= ' WHERE rowid='.$id_convention;
			if($db->query($sql_update)){
				//--------------------------------------------
				$sql_select = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$user->id;
				$obj = $db->fetch_object($db->query($sql_select));

				//On garde la trace de l'action
				$action_effectue = "Modification de la convention ".$obj_old->nom." par ".$nom;
				$sql_log = 'INSERT INTO '.MAIN_DB_PREFIX.'log (fk_user, nom, prenom, quand, action_effectue, object_concerne)';
				$sql_log .= ' VALUES('.$user->id.', "'.$obj->lastname.'","'.$obj->firstname.'",now(),"'.$action_effectue.'","Modification convention")';
				$db->query($sql_log);
				//--------------------------------------------

				$message = 'Convention Modifiée avec succès';
				$action = "afficher";
			}else{
				$message = 'Un problème est survenu';
				$action = "edit_convention";
			}
		}else $action = "edit_convention";
		
	}

	if($action == "create"){
		print load_fiche_titre($langs->trans("Création d'une Convention"), '', '');
		print '<span style="color: red">*</span> <i>Tous les champs sont obligatoires</i>';
		print "<hr><br>";
	 print '<table><form action="'.$_SERVER["PHP_SELF"].'" method="post" enctype="multipart/form-data">';
	 print '<input type="hidden" name="token" value="'.newToken().'">';
	 print '<input type="hidden" name="action" value="add">';
	 print '<tr>';
	 print '<td class="" style="width: 200px; padding-right: 30px; padding-bottom: 30px"><label>Nom Convention</label></td>';
	 print '<td style=" padding-right: 30px; width: 500px; padding-bottom: 30px"><input style= "width: 500px;" type="text" name="nom" value="'.GETPOST("nom").'"/>'. info_admin("Ce champ ne peut pas être vide", 1). ' </tr>';
	 print '<tr>';
	 print '<td class="" style="width: 200px; padding-right: 30px; padding-bottom: 30px"><label>Description</label>'. info_admin("Valeur par défaut \"Description\"", 1). '</td>';
	 print '<td style=" padding-right: 30px;width: 600px; padding-bottom: 30px"><textarea style="width: 550px; height: 50px" type="text" name="desc">'.GETPOST("desc").'</textarea></td>';
	 print '</tr>';
	 print '<tr>';
	 print '<td class="fieldrequired" style=" padding-right: 30px; padding-bottom: 30px"><label>Document</label>'. info_admin("Extensions autorisées : JPG, PNG, JPEG et PDF", 1). '</td>';
	 print '<td style=" padding-right: 30px; padding-bottom: 30px"><input type="file" name="document_convention" value="'.GETPOST("document_convention").'">'. info_admin("La taille maximale du fichier doit être inférieur à 10 Mo", 1). '</td>';
	 print '</tr>';
	 print '<tr>';
	print '<table>';
	 print '<hr>';
	 print '
		<div style="text-align: center; align-items: center; justify-content: center">
			<input class="button" type="submit" value="Ajouter" name=""/>
	 		</form>
	 		<a href="'.$_SERVER["PHP_SELF"].'?action=afficher" class="button">Annuler</a></td></tr>
		</div>';
	}

	if($action == "edit_convention"){
		print load_fiche_titre($langs->trans("Modification d'une Convention"), '', '');
		print '<span style="color: red">*</span> <i>Tous les champs sont obligatoires</i>';
		print "<hr><br>";
		$id_convention = GETPOST("id_convention", "09");
		$convSql = "SELECT nom, commentaire, document_convention FROM ".MAIN_DB_PREFIX."convention WHERE rowid=".$id_convention;
		$result = $db->query($convSql);//= $db->query($convSql);
		$obj = $db->fetch_object($result);

	 print '<table><form action="'.$_SERVER["PHP_SELF"].'?id_convention='.$id_convention.'" method="post">';
	 print '<input type="hidden" name="token" value="'.newToken().'">';
	print '<input type="hidden" name="action" value="save_edit_convention">';
	print '<input type="hidden" name="encien_document_convention" value="'.$obj->document_convention.'">';
	 print '<tr>';
	 print '<td class="" style="width: 200px; padding-right: 30px; padding-bottom: 30px"><label>Nom Convention</label></td>';
	 print '<td style=" padding-right: 30px; width: 500px padding-bottom: 30px"><input style="width: 500px" type="text" name="nom" value="'.$obj->nom.'"/>'. info_admin("Ce champ ne peut pas être vide", 1). '</tr>';
	 print '<tr>';
	 print '<td class="" style="width: 200px; padding-right: 30px; padding-bottom: 30px"><label>Description</label>'. info_admin("Valeur par défaut \"Description\"", 1). '</td>';
	 print '<td style=" padding-right: 30px; width: 600px; padding-bottom: 30px"><textarea style="width: 550px; height: 50px" type="text" name="desc">'.$obj->commentaire.'</textarea></td>';
	 print '</tr>';

	 print '<tr>';
	 print '<td class="fieldrequired" style=" padding-right: 30px; padding-bottom: 30px"><label>Document</label>'. info_admin("Extensions autorisées : JPG, PNG, JPEG et PDF", 1). '</td>';
	 print '<td style=" padding-right: 30px; padding-bottom: 30px"><input type="file" name="document_convention" >'. info_admin("La taille maximale du fichier doit être inférieur à 10 Mo", 1). '</td>';
	 print '</tr>';
	 print '</table>';
	 print '<hr>';
	 print '
	 	<div style="text-align: center; align-items: center; justify-content: center">
	 		<input class="button" type="submit" value="Modifier" name=""/>
	 		</form>
	 		<a href="'.$_SERVER["PHP_SELF"].'?action=afficher" class="button">Annuler</a></td></tr>
		</div>';
	 
	}

	if($action == 'supprimerConfirmation'){
		$id_convention = GETPOST('id_convention');
		$sql_old = "SELECT nom FROM ".MAIN_DB_PREFIX."convention WHERE rowid=".$id_convention;
		$obj_old = $db->fetch_object($db->query($sql_old));

		$text = "Voulez-vous vraiment supprimer ".$obj_old->nom."?";
		$formconfirm = $monform->formconfirm(
			$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=convention&id_convention='.$id_convention,
			'Confirmation',
			$text,
			'supprimer',
			'',
			'',
			1,
			300,
			'30%'
		);
				print $formconfirm;

		$action = 'afficher';
	
	}
//Suppression d'une convention action = delete (clique sur la corbeille dans la liste convention)
if($action == 'supprimer'){

	$id_convention = GETPOST('id_convention');

	$sql_old = "SELECT nom FROM ".MAIN_DB_PREFIX."convention WHERE rowid=".$id_convention;
	$obj_old = $db->fetch_object($db->query($sql_old));
	$sql_select = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$user->id;
	$obj = $db->fetch_object($db->query($sql_select));

	//On garde la trace de l'action
	$action_effectue = "Suppression de la convention ".$obj_old->nom;
	$sql_log = 'INSERT INTO '.MAIN_DB_PREFIX.'log (fk_user, nom, prenom, quand, action_effectue, object_concerne)';
	$sql_log .= ' VALUES('.$user->id.', "'.$obj->lastname.'","'.$obj->firstname.'",now(),"'.$action_effectue.'","Suppression convention")';
	$db->query($sql_log);

	//suppression des types salariés, des catégories, des echelons liés à cette convention
	$sqlDel = "DELETE FROM ".MAIN_DB_PREFIX."convention WHERE rowid=".$id_convention;
	$result = $db->query($sqlDel);

	$action = 'afficher';
}


if($action == 'disable'){
	$id_convention = GETPOST('id_convention');
	$sqlEdit = "UPDATE ".MAIN_DB_PREFIX."convention SET active=0 WHERE rowid=".$id_convention;
	$result = $db->query($sqlEdit);
	if($result)
		$message = 'Convention desactivée avec succès';
	else $message = 'Un problème es survenu';
		$action = 'afficher';
}
if($action == 'activate'){
	$id_convention = GETPOST('id_convention');
	$sqlEdit = "UPDATE ".MAIN_DB_PREFIX."convention SET active=1 WHERE rowid=".$id_convention;
	$result = $db->query($sqlEdit);
	if($result)
		$message = 'Convention activée avec succès';
	else $message = 'Un problème es survenu';
		$action = 'afficher';
}

//--------------------------------------------
//affichage des ligne(liste) des conventions action = afficher
if($action == 'afficher'){
	print load_fiche_titre($langs->trans("Les Conventions prises en charge par Salaire|Paie"), '', '');
	print_barre_liste("", $page, $_SERVER["PHP_SELF"], "", "", "", "", "", "", 'bill', 0, dolGetButtonTitle("Créer une Convention", '', 'fa fa-plus-circle', $_SERVER['PHP_SELF'].'?action=create' , '', 1), '', 0, 0, 0, 1);
	$acts[0] = "activate";
	$acts[1] = "disable";
	$actl[0] = img_picto($langs->trans("Disabled"), 'switch_off', 'class="size30x"');
	$actl[1] = img_picto($langs->trans("Activated"), 'switch_on', 'class="size30x"');

print '<table class="tagtable liste" style="width: 100%">';
print '<tr class="liste_titre" style="height: 1px"><td class="liste_titre" style="color: #6f89bd; padding: ; width : 20%;" >Nom convention</td><td style="color: #6f89bd; padding: ; width : 20%;" >Description</td><td class="liste_titre" style="color: #6f89bd; padding: ; width : 10%;" >Date de création</td><td class="liste_titre" style="color: #6f89bd; padding: ; width : 5%;" >Activée/Desactivée</td></tr>';
$covSql = "SELECT * FROM ".MAIN_DB_PREFIX."convention";
	$result = $db->query($covSql);//= $db->query($covSql);
	if($result){
		$i = 0;
		$num = $db->num_rows($result);
		while ($i < $num){
			$obj = $db->fetch_object($result);
			if($obj->active == 1){ //si elle est active ou non
				if ($i%2!= 0) {
                	print '<tr class="pair">'.affiche_long_texte(img_picto("", "statut7", "class='paddingright pictofixedwidth'"), $obj->nom, 0, './convention/onglets/convention_information.php?mainmenu=paiementsalaire&leftmenu=convention&action=afficher&id_convention='.$obj->rowid, 'nom', '','', '', '').''.affiche_long_texte("", $obj->commentaire, 1, '', '', '','', '', '').'<td style="width : 5%"> '.$obj->date_creation.' '.img_picto("", "calendar", "class='paddingright pictofixedwidth'").'</td><td style="width : 5%">';
					print'<a class="reposition" href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=convention&action='.$acts[$obj->active].'&id_convention='.$obj->rowid.'&token='.newToken().'">'.$actl[$obj->active].'</a>';
				}else{					
					
                	print '<tr class="pair">'.affiche_long_texte(img_picto("", "statut7", "class='paddingright pictofixedwidth'"), $obj->nom, 0, './convention/onglets/convention_information.php?mainmenu=paiementsalaire&leftmenu=convention&action=afficher&id_convention='.$obj->rowid, 'nom', '','', '', '').''.affiche_long_texte("", $obj->commentaire, 1, '', '', '','', '', '').'<td style="width : 5%"> '.$obj->date_creation.' '.img_picto("", "calendar", "class='paddingright pictofixedwidth'").'</td><td style="width : 5%">';
					print'<a class="reposition" href="'.$url.'?mainmenu=paiementsalaire&leftmenu=convention&action='.$acts[$obj->active].'&id_convention='.$obj->rowid.'&token='.newToken().'">'.$actl[$obj->active].'</a>';
					
				}
			}else{
				if ($i%2!= 0) {
                	print '<tr class="pair">'.affiche_long_texte(img_picto("", "statut7_red", "class='paddingright pictofixedwidth'"), $obj->nom, 0, '', 'nom', '','', '', '').''.affiche_long_texte("", $obj->commentaire, 1, '', '', '','', '', '').'<td style="width : 5%">'.$obj->date_creation.' '.img_picto("", "calendar", "class='paddingright pictofixedwidth'").'</td><td style="width : 5%">';
					print'<a class="reposition" href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=convention&action='.$acts[$obj->active].'&id_convention='.$obj->rowid.'&token='.newToken().'">'.$actl[$obj->active].'</a>';
				}else{					
					
					print '<tr class="impair">'.affiche_long_texte(img_picto("", "statut7_red", "class='paddingright pictofixedwidth'"), $obj->nom, 0, '', 'nom', '','', '', '').''.affiche_long_texte("", $obj->commentaire, 1, '', '', '','', '', '').'<td style="width : 5%"> '.$obj->date_creation.' '.img_picto("", "calendar", "class='paddingright pictofixedwidth'").'</td><td style="width : 5%">';
					print'<a class="reposition" href="'.$url.'?mainmenu=paiementsalaire&leftmenu=convention&action='.$acts[$obj->active].'&id_convention='.$obj->rowid.'&token='.newToken().'">'.$actl[$obj->active].'</a>';

				}
			}

			if($i>6){
				print '&nbsp;&nbsp;&nbsp;<a class="reposition editfielda" href="'.$url.'?action=edit_convention&id_convention='.$obj->rowid.'">'.img_edit().'</a>';	
				print '&nbsp;&nbsp;&nbsp;<a class="reposition editfielda" id="delete'.$i.'" onclick="myFunction('.$i.')" href="'.$url.'?action=supprimerConfirmation&id_convention='.$obj->rowid.'">'.img_delete().'</a>&nbsp;';
			}
				print '</td></tr>';
			$i ++;
		}
	}else print '<tr><td colspan="4" align="center">Auccune convention disponible!</td></tr>';
print '</table>';
}
//header('Location: ./compta/facture/card.php');
if($message != ''){		
	print "<script>
	$.jnotify('".$message."', {delay : 5000, fadeSpeed: 500});
	</script>";
}