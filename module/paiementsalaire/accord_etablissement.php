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
//$PaiementSalaire = new modPaiementSalaire($db);

$form = new Form($db);
llxHeader('', "Paiement | Salaire");
//Titre 
//table des champs et labels
$action = GETPOST('action','aZ09');
$message = '';
if(empty($action))	
	$action = 'afficher';

	if($action == "add"){

		$nom = GETPOST("nom");
		$desc = GETPOST("desc");
		$fk_societe = GETPOST("fk_societe");

		if(empty($nom))
			$message = 'Le champ "NOM" est obligatoire<br>';
		//if(empty($message))
		$destination = "";
		if (isset($_FILES['fichier_accord']) && $_FILES['fichier_accord']['error'] == 0 && empty($message)) {
			$nom_file = $_FILES['fichier_accord']['name'];
			$chemin = $_FILES['fichier_accord']['tmp_name'];
			$extension = strrchr($nom_file,".");
			$extension_autorisees = array('.JPG','.jpg','.png','.PNG','.jpeg','.JPEG','.pdf','.PDF');
			$destination = './accord/documents/'.$nom.''.date('d_m_y_h_i_s').''.$extension;
			if(in_array($extension,$extension_autorisees)){
				if($_FILES['fichier_accord']['size']<=10000000){
					if(move_uploaded_file($chemin,$destination)){
						$sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'accord_etablissement (fk_societe, nom, commentaire, fichier_accord) VALUES('.$fk_societe.',"'.$nom.'","'.$desc.'","'.$destination.'")';
						$res = $db->query($sql_insert);
					if($res){
						$message = 'Accord d"établissement créé avec succès';
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

	if($action == "enregistrer_modif"){

		$nom = GETPOST("nom");
		$desc = GETPOST("desc");
		$fk_societe = GETPOST("fk_societe", "09");
		$encien_fichier_accord = GETPOST("encien_fichier_accord");

		$id_accord = GETPOST("id_accord");

		$sql_update = 'UPDATE '.MAIN_DB_PREFIX.'accord_etablissement SET ';
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

			if(!empty($fk_societe))
			if($mettre_virgule > 0){
				$sql_update .= ', fk_societe='.$fk_societe;
			}else{
				$sql_update .= 'fk_societe='.$fk_societe;
				$mettre_virgule ++;
			}
		//if(empty($message))
		$destination = "";
		if (isset($_FILES['fichier_accord']) && $_FILES['fichier_accord']['error'] == 0) {
			$nom_file = $_FILES['fichier_accord']['name'];
			$chemin = $_FILES['fichier_accord']['tmp_name'];
			$extension = strrchr($nom_file,".");
			$extension_autorisees = array('.JPG','.jpg','.png','.PNG','.jpeg','.JPEG','.pdf','.PDF');
			$destination = './accord/documents/'.$nom.''.date('d_m_y_h_i_s').''.$extension;
			if(in_array($extension,$extension_autorisees)){
				if($_FILES['fichier_accord']['size']<=10000000){
					if(!empty($encien_fichier_accord) && $encien_fichier_accord != $destination)
						if(move_uploaded_file($chemin,$destination)){
							if($mettre_virgule > 0){
								$sql_update .= ', fichier_accord="'.$destination.'"';
							}else{
								$sql_update .= 'fichier_accord="'.$destination.'"';
							}
							unlink($encien_fichier_accord);
						}else $message .= "Un problème est intervenu lors du Chargement du fichier";
				}else $message .= "La taille du fichier doit être inférieur à 10Mo";

			}else $message .= "Extension de fichier non autorisée<br><br>Les extensions autorisées son : JPG, PNG, JPEG et PDF";
		}//else $message .= "Veuillez joindre un fichier";

		if(empty($message)){
			$sql_update .= ' WHERE rowid='.$id_accord;
			if($db->query($sql_update)){
				$message = 'Accord d"établissement Modifié avec succès';
				$action = "afficher";
			}else{
				$message = 'Un problème est survenu';
				$action = "modif_accord";
			}
		}else $action = "modif_accord";
		
	}

	if($action == "create"){
		print load_fiche_titre($langs->trans("Création d'un accord d'établissement"), '', '');
		print '<span style="color: red">*</span> <i>Tous les champs sont obligatoires</i>';
		print "<hr><br>";
	 print '<table><form action="'.$_SERVER["PHP_SELF"].'" method="post" enctype="multipart/form-data">';
	 print '<input type="hidden" name="token" value="'.newToken().'">';
	print '<input type="hidden" name="action" value="add">';
	 print '<tr>';
	 print '<td class="" style="width: 200px; padding-right: 30px; padding-bottom: 30px"><label>Nom accord établissement</label></td>';
	 print '<td style="width: 500px; padding-right: 30px; padding-bottom: 30px"><input style="width: 500px" type="text" name="nom" value="'.GETPOST("nom").'"/>'. info_admin("Ce champ ne peut pas être vide", 1). '</tr>';
	 print '<tr>';
	 print '<td class="" style="width: 200px; padding-right: 30px; padding-bottom: 30px"><label>Description</label>'. info_admin("Valeur par défaut \"Description\"", 1). '</td>';
	 print '<td style="width: 600px; padding-right: 30px; padding-bottom: 30px"><textarea style="width: 550px; height: 50px" type="text" name="desc">'.GETPOST("desc").'</textarea></td>';
	 print '</tr>';

	$sql = "SELECT sc.rowid as r1, sc.nom, sce.rowid as r2, sce.fk_object, sce.conv FROM ".MAIN_DB_PREFIX."societe as sc";
	$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."societe_extrafields as sce ON sc.rowid=sce.fk_object WHERE sce.grp=1";
	$result = $db->query($sql);
	print '<td class="" style="width: 200px; padding-right: 30px; padding-bottom: 30px"><label>Société</label></td>';
	print '<td class="" style="width: 500px; padding-right: 30px; padding-bottom: 30px"><select style="width: 500px" name="fk_societe" >';
	if($result){
		$i = 0;
		$num = $db->num_rows($result);
		while ($i < $num){
		$societe = $db->fetch_object($result);
		print "<option value='".$societe->r1."'>".$societe->nom."</option>";
		$i ++;
		}
	}else print "<option value='0'></option>";
	print '</select></d></tr>';

	 print '<tr>';
	 print '<td class="fieldrequired" style="width: 200px; padding-right: 30px; padding-bottom: 30px"><label>Document</label>'. info_admin("La taille maximale du fichier doit être inférieur à 10 Mo", 1). '</td>';
	 print '<td style="width: 500px; padding-right: 30px; padding-bottom: 30px"><input style="width: 500px" type="file" name="fichier_accord" value="'.GETPOST("fichier_accord").'">'. info_admin("Extensions autorisées : JPG, PNG, JPEG et PDF", 1). '</td>';
	 print '</tr>';
	 print '<tr>';
	 print '</table>';
	 print '<hr>';
	 print '
	 	<div style="text-align: center; align-items: center; justify-content: center">
	 		<input class="button" type="submit" value="Ajouter" name=""/>
	        </form>
	 		<a href="'.$_SERVER["PHP_SELF"].'?action=afficher" class="button">Annuler</a></td></tr>
		</div>	
			';
	 
	}

	if($action == "modif_accord"){
		print load_fiche_titre($langs->trans("Modification d'un accord d'établissement"), '', '');
		print '<span style="color: red">*</span> <i>Tous les champs sont obligatoires</i>';
		print "<hr><br>";
		$id_accord = GETPOST("id_accord", "09");
		$convSql = "SELECT * FROM ".MAIN_DB_PREFIX."accord_etablissement WHERE rowid=".$id_accord;
		$result = $db->query($convSql);//= $db->query($convSql);
		$obj = $db->fetch_object($result);

	 print '<table><form action="'.$_SERVER["PHP_SELF"].'?id_accord='.$id_accord.'" method="post" enctype="multipart/form-data">';
	 print '<input type="hidden" name="token" value="'.newToken().'">';
	 print '<input type="hidden" name="action" value="enregistrer_modif">';
	 print '<input type="hidden" name="encien_fichier_accord" value="'.$obj->fichier_accord.'">';
	 print '<tr>';
	 print '<td class="" style="width: 200px; padding-right: 30px; padding-bottom: 30px"><label>Nom accord établissement</label></td>';
	 print '<td style="width: 500px; padding-right: 30px; padding-bottom: 30px"><input style="width: 500px" type="text" name="nom" value="'.$obj->nom.'"/>'. info_admin("Ce champ ne peut pas être vide", 1). '</tr>';
	 print '<tr>';
	 print '<td class="" style="width: 200px; padding-right: 30px; padding-bottom: 30px"><label>Description</label>'. info_admin("Valeur par défaut \"Description\"", 1). '</td>';
	 print '<td style="width: 600px; padding-right: 30px; padding-bottom: 30px"><textarea style="width: 550px; height: 50px" type="text" name="desc">'.$obj->commentaire.'</textarea></td>';
	print '<tr><td class="" style="width: 200px; padding-right: 30px; padding-bottom: 30px"><label>Société</label></td>';
	print '<td class="" style="width: 500px; padding-right: 30px; padding-bottom: 30px"><select style="width: 500px" name="fk_societe" >';
	$sql = "SELECT sc.rowid, sc.nom, sce.rowid as r2, sce.fk_object, sce.conv FROM ".MAIN_DB_PREFIX."societe as sc";
	$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."societe_extrafields as sce ON sc.rowid=sce.fk_object WHERE sce.grp=1";
	$result1 = $db->query($sql);
	if($result1){
		$i = 0;
		$num = $db->num_rows($result1);
		while ($i < $num){
			$societe = $db->fetch_object($result1);
			if($obj->fk_societe == $societe->rowid)
				print "<option value='".$societe->rowid."' selected>".$societe->nom."</option>";
			else
				print "<option value='".$societe->rowid."'>".$societe->nom."</option>";
			$i ++;
		}
	}else print "<option value='0'></option>";
	print '</select></d></tr>';

	print '<tr>';
	 print '<td class="fieldrequired" style="width: 200px; padding-right: 30px; padding-bottom: 30px"><label>Document</label>'. info_admin("La taille maximale du fichier doit être inférieur à 10 Mo", 1). '</td>';
	 print '<td style="width: 500px; padding-right: 30px; padding-bottom: 30px"><input style="width: 500px" type="file" name="fichier_accord" >'. info_admin("Extensions autorisées : JPG, PNG, JPEG et PDF", 1). '</td>';
	 print '</tr>';

	 print '</tr>';
	 print '<tr>';
	 print '</table>';
	 print '<hr>';
	 print '
	 	<div style="text-align: center; align-items: center; justify-content: center">
	 		<input class="button" type="submit" value="Enregistrer" name=""/>
	 		</form>
	 		<a href="'.$_SERVER["PHP_SELF"].'?action=afficher" class="button">Annuler</a>
		</div>
	 ';
	}

if($action == 'disable'){
	$id_accord = GETPOST('id_accord');
	$sqlEdit = "UPDATE ".MAIN_DB_PREFIX."accord_etablissement SET active=0 WHERE rowid=".$id_accord;
	$result = $db->query($sqlEdit);
	if($result)
		$message = 'Accord d"établissement desactivé avec succès';
	else $message = 'Un problème es survenu';
		$action = 'afficher';
}
if($action == 'activate'){
	$id_accord = GETPOST('id_accord');
	$sqlEdit = "UPDATE ".MAIN_DB_PREFIX."accord_etablissement SET active=1 WHERE rowid=".$id_accord;
	$result = $db->query($sqlEdit);
	if($result)
		$message = 'Accord d"établissement activé avec succès';
	else $message = 'Un problème es survenu';
		$action = 'afficher';
}


if($action == 'supprimer'){
	$id_accord = GETPOST('id_accord');
	$sqlEdit = "DELETE FROM ".MAIN_DB_PREFIX."accord_etablissement WHERE rowid=".$id_accord;
	$result = $db->query($sqlEdit);
	if($result)
		$message = 'Accord d"établissement supprimer avec succès';
	else $message = 'Un problème est survenu';
		$action = 'afficher';
}

//--------------------------------------------
//affichage des ligne(liste) des conventions action = afficher
if($action == 'afficher'){
	print load_fiche_titre($langs->trans("La liste des Accords d'Etablissement"), '', '');
print_barre_liste("", $page, $_SERVER["PHP_SELF"], "", "", "", "", "", "", 'bill', 0, dolGetButtonTitle("Créer une Convention", '', 'fa fa-plus-circle', $_SERVER['PHP_SELF'].'?action=create' , '', 1), '', 0, 0, 0, 1);
	$acts[0] = "activate";
	$acts[1] = "disable";
	$actl[0] = img_picto($langs->trans("Disabled"), 'switch_off', 'class="size30x"');
	$actl[1] = img_picto($langs->trans("Activated"), 'switch_on', 'class="size30x"');

print '<table class="tagtable liste">';
print '<tr class="liste_titre" style="height: 1px"><td class="liste_titre" style="color: #6f89bd; padding: ; width : 20%;" >Nom accord établissement</td><td style="color: #6f89bd; padding: ; width : 28%;" >Description</td><td class="liste_titre" style="color: #6f89bd; padding: ; width : 15%;" >Date de création</td><td class="liste_titre" style="color: #6f89bd; padding: ; width : 0%;" >Activée/Desactivée</td></tr>';
$covSql = "SELECT * FROM ".MAIN_DB_PREFIX."accord_etablissement";
	$result = $db->query($covSql);//= $db->query($covSql);
	if($result){
		$i = 0;
		$num = $db->num_rows($result);
		while ($i < $num){
			$obj = $db->fetch_object($result);
			if($obj->active == 1){
				if ($i%2!= 0) {
                	print '<tr class="pair">'.affiche_long_texte(img_picto("", "statut7", "class='paddingright pictofixedwidth'"), $obj->nom, 0, './accord/onglets/accord_information.php?mainmenu=paiementsalaire&leftmenu=accord_etablissement&action=afficher&id_accord='.$obj->rowid, 'nom', '','', '', '').'</a></td>'.affiche_long_texte("", $obj->commentaire, 1, '', '', '','', '', '').'<td style="width : 5%">'.$obj->date_creation.' '.img_picto("", "calendar", "class='paddingright pictofixedwidth'").'</td><td style="width : 5%">';
					print'<a class="reposition" href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=accord_etablissement&action='.$acts[$obj->active].'&id_accord='.$obj->rowid.'&token='.newToken().'">'.$actl[$obj->active].'</a>';
				}else{					
					
					print '<tr class="pair">'.affiche_long_texte(img_picto("", "statut7", "class='paddingright pictofixedwidth'"), $obj->nom, 0, './accord/onglets/accord_information.php?mainmenu=paiementsalaire&leftmenu=accord_etablissement&action=afficher&id_accord='.$obj->rowid, 'nom', '','', '', '').'</a></td>'.affiche_long_texte("", $obj->commentaire, 1, '', '', '','', '', '').'<td style="width : 5%">'.$obj->date_creation.' '.img_picto("", "calendar", "class='paddingright pictofixedwidth'").'</td><td style="width : 5%">';
					print'<a class="reposition" href="'.$url.'?mainmenu=paiementsalaire&leftmenu=accord_etablissement&action='.$acts[$obj->active].'&id_accord='.$obj->rowid.'&token='.newToken().'">'.$actl[$obj->active].'</a>';

				}
			}else{
				if ($i%2!= 0) {
                	print '<tr class="pair">'.affiche_long_texte(img_picto("", "statut7_red", "class='paddingright pictofixedwidth'"), $obj->nom, 0, '', 'nom', '','', '', '').'</td>'.affiche_long_texte("", $obj->commentaire, 1, '', '', '','', '', '').'<td style="width : 5%">'.$obj->date_creation.' '.img_picto("", "calendar", "class='paddingright pictofixedwidth'").'</td><td style="width : 5%">';
					print'<a class="reposition" href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=accord_etablissement&action='.$acts[$obj->active].'&id_accord='.$obj->rowid.'&token='.newToken().'">'.$actl[$obj->active].'</a>';
				}else{					
					
					print '<tr class="pair">'.affiche_long_texte(img_picto("", "statut7_red", "class='paddingright pictofixedwidth'"), $obj->nom, 0, '', 'nom', '','', '', '').'</td>'.affiche_long_texte("", $obj->commentaire, 1, '', '', '','', '', '').'<td style="width : 5%">'.$obj->date_creation.' '.img_picto("", "calendar", "class='paddingright pictofixedwidth'").'</td><td style="width : 5%">';
					print'<a class="reposition" href="'.$url.'?mainmenu=paiementsalaire&leftmenu=accord_etablissement&action='.$acts[$obj->active].'&id_accord='.$obj->rowid.'&token='.newToken().'">'.$actl[$obj->active].'</a>';

				}
			}

				print '&nbsp;&nbsp;&nbsp;<a class="reposition editfielda" href="'.$url.'?action=modif_accord&id_accord='.$obj->rowid.'">'.img_edit().'</a>';	
				print '&nbsp;&nbsp;&nbsp;<a class="reposition editfielda" id="delete'.$i.'" onclick="myFunction('.$i.')" href="'.$url.'?action=supprimer&id_accord='.$obj->rowid.'">'.img_delete().'</a>&nbsp;';
			
				print '</td></tr>';
			$i ++;
		}
	}else print '<tr><td colspan="4" align="center">Auccun accord disponible!</td></tr>';
print '</table>';
$db->free();

print "<script>
 function myFunction(e){
    var b = 'delete'+e;
    var button_generer = document.getElementById(b);
    if(!confirm('Cette suppression entraînera la suppression de les informations liées à cet accord')){
        var lien = '".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenu=accord_etablissement&action=afficher';
        button_generer.setAttribute('href', lien);
    
    }
   }
 
 </script>";
}
//header('Location: ./compta/facture/card.php');


if(!empty($message)){		
		print "<script>
		$.jnotify('".$message."', {delay : 5000, fadeSpeed: 500});
		</script>";
}