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
$id_categ = GETPOST('id_categorie', 'int');
$covSql = "SELECT * FROM ".MAIN_DB_PREFIX."convention WHERE rowid=".$id_convention;
$result = $db->query($covSql);//= $db->query($covSql);
//Titre 
$nom_convention = "";
if($result){
	$obj = $db->fetch_object($result);
	$nom_convention = "<b><mark>".$obj->nom."</mark></b>";
}


$catSql = "SELECT * FROM ".MAIN_DB_PREFIX."dcategories WHERE rowid=".$id_categ;
	$result = $db->query($catSql);//= $db->query($catSql);
	$obj = $db->fetch_object($result);

$titre = "Ajout d'echelon à la catégorie <b><mark>".$obj->code_categorie."</mark></b> de la convention ".$nom_convention;
print load_fiche_titre($langs->trans($titre), '', '');
$head = paiementsalaireConventionHead($id_convention);
print dol_get_fiche_head($head, 'categorie', "", -1, '');

$db->free($result);

 $message = "";

 $action = GETPOST('action');
if(empty($action))	
	$action = 'create';


if($action == "add"){
        $libelle = GETPOST('libelle','aZint._/');
        $desc = GETPOST('desc','aZintéèê ') ? GETPOST('desc'): "";
        if(empty($libelle)){
            $message = 'Le champ "LIBELLE" est obligatoire<br>';
        }
        $fk_categorie = GETPOST('fk_categorie','int');
        if(empty($fk_categorie)){
            $message .= 'Le champ "CATEGORIE" est obligatoire<br>';
        }
        

        $sql = "INSERT INTO ".MAIN_DB_PREFIX."echelon (fk_categorie, libelle, commentaire) VALUES (".$fk_categorie.",'".$libelle."','".$desc."')";
		$result = $db->query($sql);

        if(empty($message) && $result)
            $message = "Un échelon enregistrer avec succès";
        else $message = "Un problème est survenu !";

        /*$nom_echelon = GETPOST('nom_echelon', 'int')?:0;
		$salaire_base = GETPOST('salaire_base', 'int')?:0;

		$sql = 'INSERT INTO '.MAIN_DB_PREFIX.'echelon (libelle, commentaire, fk_categorie) VALUES ("'.$nom_echelon.'","'.$nom_echelon.'",'.$idcateg.')';
		//$result = $db->query($sql);
		$result = $db->query("SELECT LAST_INSERT_ID() as rowid;");
			$obj = $db->fetch_object($result);
			$rowid_echelon =  $obj->rowid;

			$sql = 'INSERT INTO '.MAIN_DB_PREFIX.'grille_categorie_echelon_salaire_base (fk_grille, fk_categorie, fk_echelon, salaire_base) VALUES ('.$fk_grille.','.$id_categ.','.$rowid_echelon.',"'.$salaire_base.'")';
			//$result = $db->query($sql);*/

			header('Location: ./detail.php?idmenu=4399&mainmenu=paiementsalaire&leftmenu=categorie&action=detailcategorie&id_categ='.$id_categ.'&id_convention='.$id_convention.'message='.$message);

}

if($action == "create"){
	print load_fiche_titre($langs->trans("Veuillez remplir les champs ci-dessous"), '', '');
 print '<table><form action="'.$_SERVER["PHP_SELF"].'" method="post">';
 print '<input type="hidden" name="token" value="'.newToken().'">';
print '<input type="hidden" name="action" value="add">';
 print '<tr>';
 print '<td class="fieldrequired" style=" padding-right: 30px"><label>Libellé</label></td>';
 print '<td style=" padding-right: 30px"><input type="text" name="libelle"></td></tr>';

 print '<tr><td class="fieldrequired" style=" padding-right: 30px"><label>Description</label></td>';
 print '<td style=" padding-right: 30px"><textarea type="text" name="desc"></textarea></td></tr>';

 print '<tr><td class="fieldrequired" style=" padding-right: 30px"><label>Salaire de Base</label></td>';
 print '<td style=" padding-right: 30px"><input type="text" name="salaire_base" ></td></tr>';

 print '<tr><td><br></td><td><br></td></tr>';
 print '<tr><td colspan="2"><input class="button" type="submit" value="Enregistrer" >';

 print '</form>';
 print '<a class="button" href="./detail.php?idmenu=4399&mainmenu=paiementsalaire&leftmenu=categorie&action=detailcategorie&id_categ='.$id_categ.'&id_convention='.$id_convention.'" >Annuler</a></td></tr>';
 print '</table>';

 print'</table></form>';
}


if(!empty($message))
	print "<script>
	$.jnotify('".$message."', {delay : 5000, fadeSpeed: 500});
	</script>";