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

 require '../../../main.inc.php';
 require_once DOL_DOCUMENT_ROOT.'/custom/paiementsalaire/lib/paiementsalaire.lib.php';

//$PaiementSalaire = new modPaiementSalaire($db);

llxHeader("", "Paiement | Salaire");
$id_convention = GETPOST("id_convention","int");
$covSql = "SELECT * FROM ".MAIN_DB_PREFIX."convention WHERE rowid=".$id_convention;
$result = $db->query($covSql);//= $db->query($covSql);
//Titre 
$nom_convention = "";
if($result){
	$obj = $db->fetch_object($result);
	$nom_convention = "<b><mark>".$obj->nom."</mark></b>";
}

print load_fiche_titre($langs->trans("Ajout d'une grille à la convention ".$nom_convention), '', '');
$head = paiementsalaireConventionHead($id_convention);
print dol_get_fiche_head($head, 'grille', "", -1, '');

$db->free($result);

//table des champs et labels
$action = GETPOST('action');
if(empty($action))	
	$action = 'create';
$messages="";

if($action == "add"){
	if(empty(GETPOST('code_categorie'))) {
		$message = "Le champ 'Nom Grille' est Obligatoire<br>";
	}
	if(empty(GETPOST('nom_categorie'))){
		$message .= $message."Le champ 'Description' est Obligatoire<br>";
	} 	


	if(empty($message)){
		$nom_grille = GETPOST('nom_grille');
		$commentaire = GETPOST('commentaire');
		
		$sql = 'INSERT INTO '.MAIN_DB_PREFIX.'grille (nom, commentaire, fk_convention, active) VALUES ("'.$nom_grille.'","'.$commentaire.'",'.$id_convention.',0)';
		$result = $db->query($sql);
		$action = 'afficher';

		if($result)
			$message = "Une grille créée avec succès";
		else{
			 $message = "Un problème est survenu";
			 $action = "create"
		}

	
	}
	
}

if($action == 'create'){	
	print load_fiche_titre($langs->trans("Ajout d'une grille à la convention ".$nom_convention), '', '');
$head = paiementsalaireConventionHead($id_convention);
print dol_get_fiche_head($head, 'grille', "", -1, '');		
	//Titre 
	print load_fiche_titre($langs->trans("Veuillez remplir les champs ci-dessous"), '', '');
	print '<div >';
	print '<form name="add"  method="POST" action="'.$_SERVER['PHP_SELF'].'?id_convention='.$id_convention.'">';
	print '<input type="hidden" name="token" value="'.newToken().'">';
	print '<input type="hidden" name="action" value="add">';
	print '<table>';

	print '<tr ><td style="padding-top: 10px; padding-right: 30px" class="fieldrequired"><label>Nom de la grille</label></td>
	
	<td style="padding-top: 10px" id="nom" ><input id="nom_grille" name ="nom_grille" size="30" type="text" value="'.GETPOST("nom_grille").'" /></td>';
	print '<tr ><td style="padding-top: 10px; padding-right: 30px" class="fieldrequired"><label>Description</label></td>
	<td style="padding-top: 10px" id="nom" ><input id="commentaire" name ="commentaire" placeholder="Avec la date de création" value="'.GETPOST("commentaire").'" size="30" type="text" /></td>';
	print "</tr>";

	
	print "</tr>";

	


	print '<tr><td><br></td><td><br></td></tr>';
	print '<tr><td colspan="2"><input class="button" type="submit" value="Enregistrer" >';
	print '</form>';
	print '<a class="button" href="./grille_salaire_base.php?mainmenu=paiementsalaire&leftmenu=convention&id_convention='.$id_convention.'" >Annuler</a></td></tr>';
	print '</table></div>';
}

//--------------------------------------------------------------------------------------------------------------------------------------------------------

if($message != ''){		
		print "<script>
		$.jnotify('".$message."', {delay : 5000, fadeSpeed: 500});
		</script>";
}