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

llxHeader("", "Paiement | Salaire");
$id_convention = GETPOST("id_convention","int");
$id_accord = GETPOST("id_accord","int");
$id_categ = GETPOST('id_categ', 'int');

$covSql = "SELECT * FROM ".MAIN_DB_PREFIX."accord_etablissement WHERE rowid=".$id_accord;
$result = $db->query($covSql);//= $db->query($covSql);
$nom_accord = "";
if($result){
	$obj = $db->fetch_object($result);
	$nom_accord = "<b><mark>".$obj->nom."</mark></b>";
}

$action= GETPOST("action", "alpha") ? : "afficher";

if($action == "add"){
	$nom_echelon = GETPOST('libelle', 'alpha');
	$salaire_base = GETPOST('salaire_base', 'int');

	$desc = GETPOST('desc', 'alpha') ? : "";
	if(empty($nom_echelon)){
		$message = 'Le champ "LIBELLE" est obligatoire<br>';
	}
	if(empty($salaire_base)){
		$message .= 'Le champ "SALAIRE DE BASE" est obligatoire<br>';
	}
	$result = "";
	if(empty($message)){

		$sql = 'INSERT INTO '.MAIN_DB_PREFIX.'echelon (libelle, commentaire, fk_categorie) VALUES ("'.$nom_echelon.'","'.$desc.'",'.$id_categ.')';
		$result = $db->query($sql);
			$result = $db->query("SELECT LAST_INSERT_ID() as rowid;");
			$obj = $db->fetch_object($result);
			$rowid_echelon =  $obj->rowid;

			$grilleSql = "SELECT rowid FROM ".MAIN_DB_PREFIX."grille WHERE active=1 AND fk_convention=".$id_convention;
			$grilleResult = $db->query($grilleSql);
			$obj_grille = $db->fetch_object($grilleResult);
			$fk_grille = $obj_grille->rowid;

			$sql = 'INSERT INTO '.MAIN_DB_PREFIX.'grille_categorie_echelon_salaire_base (fk_grille, fk_categorie, fk_echelon, salaire_base) VALUES ('.$fk_grille.','.$id_categ.','.$rowid_echelon.',"'.$salaire_base.'")';
			$result = $db->query($sql);
	}
		if($result){
			$message = "Un échelon enregistrer avec succès";
			$action = "detailcategorie";
		}else{
			$message = "Un problème est survenu !";
			$action = "create";
		}
}

if($action == "detailcategorie"){
$catSql = "SELECT * FROM ".MAIN_DB_PREFIX."dcategories WHERE rowid=".$id_categ;
	$result = $db->query($catSql);//= $db->query($catSql);
	$obj = $db->fetch_object($result);

$titre = "Detail de la catégorie <b><mark>".$obj->code_categorie."</mark></b> de l'accord ".$nom_accord;
$covSql = "SELECT * FROM ".MAIN_DB_PREFIX."convention WHERE rowid=".$id_convention;
$result = $db->query($covSql);
if($result){
	$obj_conv = $db->fetch_object($result);
	if(!empty($obj_conv)){
print load_fiche_titre($langs->trans($titre), '', '');
$head = paiementsalaireAccordHead($id_convention, $id_accord);

print dol_get_fiche_head($head, 'categorie', "", -1, '');


//Titre 
$ech_sql = "SELECT DISTINCT * FROM ".MAIN_DB_PREFIX."echelon WHERE fk_categorie=".$id_categ;
	$ech_res = $db->query($ech_sql);
	if($ech_res){
		$num = $db->num_rows($ech_res);
		$obj_ech_verif = $db->fetch_object($ech_res);
		if($obj_ech_verif->rowid > 0)
			print_barre_liste("", $page, $_SERVER["PHP_SELF"], "", "", "", "", "", "", 'bill', 0, dolGetButtonTitle("Ajouter un echélon", '', 'fa fa-plus-circle', $_SERVER["PHP_SELF"].'?leftmenu=convention&action=create&id_convention='.$id_convention.'&id_categ='.$id_categ.'' , '', 1), '', 0, 0, 0, 1);
		else if ($obj_ech_verif->rowid == 0){
			$grilleSql = "SELECT rowid FROM ".MAIN_DB_PREFIX."grille WHERE active=1 AND fk_convention=".$id_convention;
			$grilleResult = $db->query($grilleSql);
			$obj_grille = $db->fetch_object($grilleResult);

			$salBaseSql = "SELECT rowid FROM ".MAIN_DB_PREFIX."grille_categorie_echelon_salaire_base WHERE fk_grille=".$obj_grille->rowid." AND fk_categorie=".$id_categ." AND fk_echelon=0";
			$salBaseResult = $db->query($salBaseSql);
			if($salBaseResult){
				$num1 = $db->num_rows($salBaseResult);
				$sal_base = $db->fetch_object($salBaseResult);

				if($num1 > 0 && $sal_base->fk_echelon != 0)
					print_barre_liste("", $page, $_SERVER["PHP_SELF"], "", "", "", "", "", "", 'bill', 0, dolGetButtonTitle("Ajouter un echélon", '', 'fa fa-plus-circle', $_SERVER["PHP_SELF"].'?leftmenu=convention&action=create&id_convention='.$id_convention.'&id_categ='.$id_categ.'' , '', 1), '', 0, 0, 0, 1);
				else if($num1 > 0 && $sal_base->fk_echelon == 0)
		 	 		print "<br><b><mark> Cette catégorie ne contient aucun échelon donc vous ne pouvez pas en ajouter</mark></b><br>";
				else
					print_barre_liste("", $page, $_SERVER["PHP_SELF"], "", "", "", "", "", "", 'bill', 0, dolGetButtonTitle("Ajouter un echélon", '', 'fa fa-plus-circle', $_SERVER["PHP_SELF"].'?leftmenu=convention&action=create&id_convention='.$id_convention.'&id_categ='.$id_categ.'' , '', 1), '', 0, 0, 0, 1);

				
			}
		}else
			print_barre_liste("", $page, $_SERVER["PHP_SELF"], "", "", "", "", "", "", 'bill', 0, dolGetButtonTitle("Ajouter un echélon", '', 'fa fa-plus-circle', $_SERVER["PHP_SELF"].'?action=create&id_convention='.$id_convention.'&id_categ='.$id_categ.'' , '', 1), '', 0, 0, 0, 1);

		}
	print '<div>';
	//table des champs et labels des informations concernant la catégorie
	print '<table>';
	print '<tr class="liste_titre"><td class="liste_titre" style="padding: 10px; width : 5%;" >Code Categorie</td><td class="liste_titre" style="padding: 10px; width : 5%;" >Nom Catégorie</td><td class="liste_titre" style="padding: 10px; width : 5%;" >Convention</td></tr>';

	$convSql = "SELECT * FROM ".MAIN_DB_PREFIX."convention WHERE rowid=".$obj->fk_convention;
	$result3 = $db->query($convSql);
	$obj3 = $db->fetch_object($result3);

	print '<tr class="pair"><td align="left" style="padding: 10px; width : 5%;"><b>'.$obj->code_categorie.'<b></a></td>';
	print '<td style="width : 5%">'.$obj->nom_categorie.'</td><td>'.$obj3->nom.'</td>';

	print '<tr ><td align="center"  colspan="3" style="padding: 10px; width : 5%;" ></td></tr>';
	print '<tr class="liste_titre"><td align="center" class="liste_titre" colspan="3" style="padding: 10px; width : 5%;" >Echélons</td></tr>';
	print '<tr class="liste_titre"><td align="center" class="liste_titre" style="padding: 10px; width : 5%;" >Libellé</td><td align="center" class="liste_titre" style="padding: 10px; width : 5%;" >Description</td><td align="center" class="liste_titre" style="padding: 10px; width : 5%;" >Salaire de base</td></tr>';


	$convSql = "SELECT DISTINCT * FROM ".MAIN_DB_PREFIX."echelon WHERE fk_categorie=".$id_categ;
	$result3 = $db->query($convSql);
	if($result3){
		$i = 0;
		$num = $db->num_rows($result3);
		while ($i < $num){
			$obj_echelon = $db->fetch_object($result3);

			$grilleSql = "SELECT rowid FROM ".MAIN_DB_PREFIX."grille WHERE active=1 AND fk_convention=".$id_convention;
			$grilleResult = $db->query($grilleSql);
			$obj_grille = $db->fetch_object($grilleResult);

			$salBaseSql = "SELECT salaire_base FROM ".MAIN_DB_PREFIX."grille_categorie_echelon_salaire_base WHERE fk_grille=".$obj_grille->rowid." AND fk_categorie=".$obj->rowid." AND fk_echelon=".$obj_echelon->rowid;
			$salBaseResult = $db->query($salBaseSql);
			$objSalBase = $db->fetch_object($salBaseResult);
			$salaire_base = $objSalBase->salaire_base;
			print '<tr class="pair"><td style="padding: 10px;" align="center"  >'.$obj_echelon->libelle.'</td>';
			print '<td style="padding: 10px;" align="center" >'.$obj_echelon->commentaire.'</td>';
			
			print '<td style="padding: 10px;" align="center" >'.$salaire_base.'</td></tr>';

				//}
			$i ++;
		}
		if($num == 0){
			$grilleSql = "SELECT rowid FROM ".MAIN_DB_PREFIX."grille WHERE active=1 AND fk_convention=".$id_convention;
			$grilleResult = $db->query($grilleSql);
			$obj_grille = $db->fetch_object($grilleResult);

			$salBaseSql = "SELECT salaire_base FROM ".MAIN_DB_PREFIX."grille_categorie_echelon_salaire_base WHERE fk_grille=".$obj_grille->rowid." AND fk_categorie=".$obj->rowid." AND fk_echelon=0";
			$salBaseResult = $db->query($salBaseSql);
			$objSalBase = $db->fetch_object($salBaseResult);
			$salaire_base = $objSalBase->salaire_base;
			print '<tr class="pair"><td style="padding: 10px;" colspan="2" align="center" >Aucun Echélon</td>';
			
			print '<td style="padding: 10px;" align="center" >'.$salaire_base.'</td></tr>';
		}
	}else print '<tr style="padding: 10px;"><td style="padding: 10px; width : 5%;" align="center" colspan="3">Acune primes liée a cette Catégorie</td></tr>';


	//indemnités liées à cette categorie
	print '<tr ><td align="center"  colspan="3" style="padding: 10px; width : 5%;" ></td></tr>';
	print '<tr class="liste_titre"><td align="center" class="liste_titre" colspan="3" style="padding: 10px; width : 5%;" >Indemnite</td></tr>';
	print '<tr class="liste_titre"><td align="center" class="liste_titre" style="padding: 10px; width : 5%;" >Libellé</td><td align="center" class="liste_titre" style="padding: 10px; width : 5%;" >Valeur</td><td align="center" class="liste_titre" style="padding: 10px; width : 5%;" >Type</td></tr>';

	$convSql = "SELECT DISTINCT fk_condition FROM ".MAIN_DB_PREFIX."condition_categorie_indemnite WHERE fk_categorie=".$id_categ;
	$result3 = $db->query($convSql);
	if($result3){
		$i = 0;
		$num = $db->num_rows($result3);
		while ($i < $num){
			$obj3 = $db->fetch_object($result3);

			$cond_ind = "SELECT DISTINCT fk_indemnite FROM ".MAIN_DB_PREFIX."condition_indemnite WHERE rowid=".$obj3->fk_condition;
			$result_ind = $db->query($cond_ind);//= $db->query($cond_ind);
			$condit = $db->fetch_object($result_ind);

			$indemnite = "SELECT * FROM ".MAIN_DB_PREFIX."indemnite WHERE rowid='".$condit->fk_indemnite."'";
			$result3_p = $db->query($indemnite);
			$obj3_p = $db->fetch_object($result3_p);

			print '<tr><td style="padding: 10px;" align="center" class="pair" >'.$obj3_p->libelle.'</td>';
			print '<td style="padding: 10px;" align="center" class="pair" >----</td>';
			
			print '<td style="padding: 10px;" align="center" class="pair" >'.$obj3_p->type_indemnite.'</td></tr>';

				//}
			$i ++;
		}
	}else print '<tr style="padding: 10px;"><td style="padding: 10px; width : 5%;" class="pair" align="center" colspan="3">Aucune Indemnité liée a cette Catégorie</td></tr>';

	print '<tr ><td align="center"  colspan="3" style="padding: 10px; width : 5%;" ></td></tr>';
	print '<tr class="liste_titre"><td align="center" class="liste_titre" colspan="3" style="padding: 10px; width : 5%;" >Primes</td></tr>';
	print '<tr class="liste_titre"><td align="center" class="liste_titre" style="padding: 10px; width : 5%;" >Libellé</td><td align="center" class="liste_titre" style="padding: 10px; width : 5%;" >Valeur</td><td align="center" class="liste_titre" style="padding: 10px; width : 5%;" >Type</td></tr>';
	
	//primes liées à cette catégorie
	$convSql = "SELECT DISTINCT fk_condition FROM ".MAIN_DB_PREFIX."condition_categorie_prime WHERE fk_categorie=".$id_categ;
	$result3 = $db->query($convSql);
	if($result3){
		$i = 0;
		$num = $db->num_rows($result3);
		while ($i < $num){
			$obj3 = $db->fetch_object($result3);

			$cond_ind = "SELECT DISTINCT fk_prime FROM ".MAIN_DB_PREFIX."condition_prime WHERE rowid=".$obj3->fk_condition;
			$result_ind = $db->query($cond_ind);//= $db->query($cond_ind);
			$condit = $db->fetch_object($result_ind);

			
			$prime = "SELECT * FROM ".MAIN_DB_PREFIX."primes WHERE rowid='".$condit->fk_prime."'";
			$result3_p = $db->query($prime);
			$obj3_p = $db->fetch_object($result3_p);

			print '<tr><td style="padding: 10px;" align="center" class="pair" >'.$obj3_p->libelle.'</td>';
			print '<td style="padding: 10px;" align="center" class="pair" >--------</td>';
			
			print '<td style="padding: 10px;" align="center" class="pair" >'.$obj3_p->type_prime.'</td></tr>';

				//}
			$i ++;
		}
	}else print '<tr style="padding: 10px;"><td style="padding: 10px; width : 5%;" class="pair" align="center" colspan="3">Acune primes liée a cette Catégorie</td></tr>';


print '</table></div>';
}else{
	print "<h2> La convention mère n'existe pas</h2>";
}
}
}

if($action == 'create'){
	$titre = "Ajout d'échélon à la catégorie <b><mark>".$obj->code_categorie."</mark></b> de la convention ".$nom_accord;
print load_fiche_titre($langs->trans($titre), '', '');
$head = paiementsalaireAccordHead($id_convention, $id_accord);

print dol_get_fiche_head($head, 'categorie', "", -1, '');			
	//Titre 
	print load_fiche_titre($langs->trans("Veuillez remplir les champs ci-dessous"), '', '');
 print '<table><form action="'.$_SERVER["PHP_SELF"].'?id_convention='.$id_convention.'&id_categ='.$id_categ.'" method="post">';
 print '<input type="hidden" name="token" value="'.newToken().'">';
print '<input type="hidden" name="action" value="add">';
 print '<tr>';
 print '<td class="fieldrequired" style=" padding-right: 30px"><label>Libellé</label></td>';
 print '<td style=" padding-right: 30px"><input type="text" name="libelle" value="'.(GETPOST("libelle", "alpha")?:"").'"></td></tr>';

 print '<tr><td class="fieldrequired" style=" padding-right: 30px"><label>Description</label></td>';
 print '<td style=" padding-right: 30px"><textarea type="text" name="desc" value="'.(GETPOST("desc", "alpha")?:"").'"></textarea></td></tr>';

 print '<tr><td class="fieldrequired" style=" padding-right: 30px"><label>Salaire de Base</label></td>';
 print '<td style=" padding-right: 30px"><input type="text" name="salaire_base" value="'.(GETPOST("salaire_base", "int")?:"").'"></td></tr>';

 print '<tr><td><br></td><td><br></td></tr>';
 print '<tr><td colspan="2"><input class="button" type="submit" value="Enregistrer" >';

 print '</form>';
 print '<a class="button" href="./detail.php?idmenu=4399&mainmenu=paiementsalaire&leftmenu=categorie&action=detailcategorie&id_categ='.$id_categ.'&id_convention='.$id_convention.'" >Annuler</a></td></tr>';
 print '</table>';

 print'</table></form>';
}

$db->free();

if($message != ""){
	$action = 'create';
		
		print "<script>
		$.jnotify('".$message."', {delay : 5000, fadeSpeed: 500});
		</script>";
}
