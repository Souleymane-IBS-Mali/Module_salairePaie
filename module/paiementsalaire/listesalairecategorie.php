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

llxHeader("", $langs->trans("Paiement | Salaire"));
//Titre 
print load_fiche_titre($langs->trans("La liste des salaire de Base/Catégories"), '', '');

$action = 'afficher';

if(!empty(GETPOST('action')))
	$action = GETPOST('action');

	if($action == "add"){

			$echelon = GETPOST('echelon', 'aZ0909');
			$fk_cat = GETPOST('categories', '09');
			$sal_cat = GETPOST('salaire_categorie', 'aZ09');

		if(empty($fk_cat)) {
			$message = 'Le champ "CATEGORIE" est Obligatoire<br>';
		}
		if(empty($echelon) || $echelon == "" || $echelon == 0)
			$echelon = 0;

		if(empty($sal_cat)){
			$message = $message.'Le champ "SALAIRE CATEGORIE" est Obligatoire<br>';
		}
		if(empty($message)){					
				
				$sql = "INSERT INTO ".MAIN_DB_PREFIX."salaire_categorie (fk_categorie, fk_echelon, salaire_categorie) VALUES (".$fk_cat.",".$echelon.",'".$sal_cat."')";
				$result = $db->query($sql);
				$message = "Salaire de base Enregistré avec succès";
			
		}
		$action = 'afficher';
		
	
	}



if($action == 'edit'){
	$id_salaire_categorie = GETPOST('id_salaire_categorie', '09');

	$sql2 = "SELECT * FROM ".MAIN_DB_PREFIX."salaire_categorie WHERE rowid='".$id_salaire_categorie."'";
	$result2 = $db->query($sql2);
	$obj2 = $db->fetch_object($result2);

	$covSql = "SELECT * FROM ".MAIN_DB_PREFIX."dcategories WHERE rowid=".$obj2->fk_categorie;
	$result = $db->query($covSql);//= $db->query($covSql);
	$obj = $db->fetch_object($result);
					
	print '<div >';
	print '<form name="add"  method="POST" action="'.$_SERVER['PHP_SELF'].'?leftmenu=categorie&id_salaire_categorie='.$id_salaire_categorie.'">';
	print '<input type="hidden" name="token" value="'.newToken().'">';
	print '<input type="hidden" name="action" value="saveedit">';
	print '<table>';
	print '<tr><td style="padding-top: 10px; padding-right: 30px; width:200px" class="fieldrequired"><label>Categorie</label></td>
			<td style="padding-top: 10px; padding-right: 30px; width:200px" class="fieldrequired"><label>Echelon</label></td>
			<td style="padding-top: 10px; padding-right: 30px; width:200px" class="fieldrequired"><label>Salaire Catégorie</label></td>
			<td style="padding-top: 10px; padding-left: 30px; width:200px" class="fieldrequired"></td></tr>';
	print '<tr>';
	print '<td><select name="categories" id="categories">';
	$catSql = "SELECT * FROM ".MAIN_DB_PREFIX."dcategories";

	$result = $db->query($catSql);//= $db->query($catSql);
	if($result){
		$i = 0;
		$num = $db->num_rows($result);
		while ($i < $num){
			$obj = $db->fetch_object($result);
			if($i==0)
				$id_categ = GETPOST("fk_categ","09") ? GETPOST("fk_categ","09") : $obj2->fk_categorie;

			if($id_categ == $obj->rowid)
				print '<option value="'.$obj->rowid.'" selected>'.$obj->code_categorie.'</option>';
			else print '<option value="'.$obj->rowid.'">'.$obj->code_categorie.'</option>';


			$i ++;
		}
	}
	print "</select>";

	print '<td><select name="echelon" id="echelon">';

	if(!empty($id_categ)){
	$echelonSql = "SELECT * FROM ".MAIN_DB_PREFIX."echelon WHERE fk_categorie=".$id_categ;
	$result = $db->query($echelonSql);
	if($result){
		$i = 0;
		$num = $db->num_rows($result);
		while ($i < $num){
			$obj_ech = $db->fetch_object($result);
				print '<option value="'.$obj_ech->rowid.'">'.$obj_ech->libelle.'</option>';

			$i ++;
		}
	}
}
//Rafraîchir la page
print "</select>";
print '<td style="padding-top: 10px"><input name="salaire_categorie" value="'.$obj2->salaire_categorie.'" type="text" /></td>';
print '<table></form>';
	
print "</tr>";
print '<script type="text/javascript">
var categorie = document.getElementById("categories");
categorie.addEventListener("change", function () {
	var fk_categ = categorie.value;
	window.location.href = "'.$_SERVER["PHP_SELF"].'?leftmenu=categorie&action=edit&id_salaire_categorie='.$id_salaire_categorie.'&fk_categ="+fk_categ;
  },
  false,
);
</script>';

	print '<tr><td colspan="2">'.$form->buttonsSaveCancel("Save").'</td></tr>';
	print '</table></form>';
	print '</div>';
}

if($action == 'saveedit'){
	$id_salaire_categorie = GETPOST('id_salaire_categorie', '09');
		$echelon = GETPOST('echelon', '09');
		$fk_cat = GETPOST('categories');
		$sal_cat = GETPOST('salaire_categorie', '09');

		if(empty($fk_cat)) {
			$message = 'Le champ "CATEGORIE" est Obligatoire<br>';
		}
		if(empty($echelon) || $echelon == "" || $echelon == 0)
			$echelon = 0;
		if(empty($sal_cat)){
			$message = $message.'Le champ "SALAIRE CATEGORIE" est Obligatoire<br>';
		}
		if(empty($message)){
			$sql_update = "UPDATE ".MAIN_DB_PREFIX."salaire_categorie SET fk_categorie=".$fk_cat.", fk_echelon=".$echelon.", salaire_categorie='".$sal_cat."' WHERE rowid=".$id_salaire_categorie;
			$result = $db->query($sql_update);
			$message = "Modification effectuée avec succès";
			$action = 'afficher';
			if(!$result)
				$message  = 'Un problème est survenu<br>';
		}

}
if($action == 'delete'){
	$id_salaire_categorie = GETPOST('id_salaire_categorie');

	$sqlDel = "DELETE FROM ".MAIN_DB_PREFIX."salaire_categorie WHERE rowid=".$id_salaire_categorie;
	$result = $db->query($sqlDel);
	if($result)
		$message = "Suppression effectuée avec succès";
	else $message = "Un problème est survenu";
	$action = 'afficher';
}

if($action == 'afficher'){
	print '<div >';
	print '<form name="add"  method="POST" action="'.$_SERVER['PHP_SELF'].'?leftmenu=categorie">';
	print '<input type="hidden" name="token" value="'.newToken().'">';
	print '<input type="hidden" name="action" value="add">';
	print '<table>';
	print '<tr><td style="padding-top: 10px; padding-right: 30px; width:200px" class="fieldrequired"><label>Categorie</label></td>
			<td style="padding-top: 10px; padding-right: 30px; width:200px" class="fieldrequired"><label>Echelon</label></td>
			<td style="padding-top: 10px; padding-right: 30px; width:200px" class="fieldrequired"><label>Salaire Catégorie</label></td>
			<td style="padding-top: 10px; padding-left: 30px; width:200px" class="fieldrequired"></td></tr>';
	print '<tr>';
	print '<td><select name="categories" id="categories">';
	$catSql = "SELECT * FROM ".MAIN_DB_PREFIX."dcategories";

	$result = $db->query($catSql);//= $db->query($catSql);
	if($result){
		$i = 0;
		$num = $db->num_rows($result);
		while ($i < $num){
			$obj = $db->fetch_object($result);
			if($i==0)
				$id_categ = GETPOST("fk_categ","09") ? GETPOST("fk_categ","09") : $obj->rowid;
			/*$catSqlFiltre = "SELECT DISTINCT fk_categorie FROM ".MAIN_DB_PREFIX."salaire_categorie;";
			$resultFiltre = $db->query($catSqlFiltre);
			if($resultFiltre){
				$j = 0;
				$numFiltre = $db->num_rows($resultFiltre);
				while ($j < $numFiltre){
					$objFiltre = $db->fetch_object($resultFiltre);
					if($objFiltre->fk_categorie==$obj->rowid)
						$aff = false;
						$j ++;
				}
				if($aff == true){
					$aff = true;
					print '<option value="'.$obj->rowid.'">'.$obj->code_categorie.'</option>';
				}else $aff = true;
			}else 	print '<option value="'.$obj->rowid.'">ok</option>';*/
			if($id_categ == $obj->rowid)
				print '<option value="'.$obj->rowid.'" selected>'.$obj->code_categorie.'</option>';
			else print '<option value="'.$obj->rowid.'">'.$obj->code_categorie.'</option>';


			$i ++;
		}
	}
	print "</select>";

	print '<td><select name="echelon" id="echelon">';

	if(!empty($id_categ)){
	$echelonSql = "SELECT * FROM ".MAIN_DB_PREFIX."echelon WHERE fk_categorie=".$id_categ;
	$result = $db->query($echelonSql);
	$aff = true;
	if($result){
		$i = 0;
		$num = $db->num_rows($result);
		while ($i < $num){
			$obj_ech = $db->fetch_object($result);
			
			$objFiltre = $db->fetch_object($resultFiltre);

			$echelonSqlFiltre = "SELECT fk_echelon FROM ".MAIN_DB_PREFIX."salaire_categorie WHERE fk_echelon=".$obj_ech->rowid;
			$resultFiltre = $db->query($echelonSqlFiltre);
			$num_ech = $db->num_rows($resultFiltre);
			if($num_ech == 0)
				print '<option value="'.$obj_ech->rowid.'">'.$obj_ech->libelle.'</option>';

			$i ++;
		}
	}
}
//Rafraîchir la page
print "</select>";
print '<td style="padding-top: 10px"><input name="salaire_categorie" type="text" /></td>';
	print '<td style="padding-top: 10px"><input type="submit" class="button button-add" name="add" value="'.$langs->trans("Add").'"/></td>';	
	print '<table></form>';
	
print "</tr>";
print '<script type="text/javascript">
var categorie = document.getElementById("categories");
categorie.addEventListener("change", function () {
	var fk_categ = categorie.value;
	window.location.href = "'.$_SERVER["PHP_SELF"].'?action=afficher&fk_categ="+fk_categ;
  },
  false,
);
</script>';

	print "</tr>";	
	print '</div>';
	print '<br>';
	print '<hr>';
print '<br>';
//table des champs et labels
print '<form ><table>';
print '<tr class="liste_titre"><td class="liste_titre" style="padding: 20px; width : 5%;" >Code Categorie</td><td class="liste_titre" style="padding: 20px; width : 5%;" >Echelon</td><td class="liste_titre" style="padding: 20px; width : 5%;" >Salaire Catégorie</td><td class="liste_titre" style="padding: 20px; width : 5%;" >Opération</td></tr>';
$covSql = "SELECT * FROM ".MAIN_DB_PREFIX."salaire_categorie";
	$result = $db->query($covSql);//= $db->query($covSql);

	if(!empty($result)){
		$i = 0;
		$num = $db->num_rows($result);
		while ($i < $num){
			$obj = $db->fetch_object($result);
                	
					$sql2 = "SELECT code_categorie FROM ".MAIN_DB_PREFIX."dcategories WHERE rowid=".$obj->fk_categorie;
					$result2 = $db->query($sql2);
					$obj2 = $db->fetch_object($result2);
							

					print '<tr class="pair"><td align="left" style="padding: 10px; width : 5%;"><a href="#"><b>'.$obj2->code_categorie.'<b></a></td>';
					print '<td style="width : 5%">';

					$echelonSql = "SELECT * FROM ".MAIN_DB_PREFIX."echelon WHERE rowid=".$obj->fk_echelon;
					$result_ech = $db->query($echelonSql);
							
					$obj_ech = $db->fetch_object($result_ech);
					print $obj_ech->libelle;							
						
					print '</td><td>'.$obj->salaire_categorie.'</td>';
					print '<td align="left" style="padding: 10px; width : 5%;"><a class="reposition editfielda" href="'.$_SERVER['PHP_SELF'].'?leftmenu=categorie&action=edit&id_salaire_categorie='.$obj->rowid.'">'.img_edit().'</a>';
					print '&nbsp;&nbsp;&nbsp;<a class="reposition editfielda" id="delete'.$i.'" onclick="myFunction('.$i.')" href="'.$_SERVER['PHP_SELF'].'?action=delete&id_salaire_categorie='.$obj->rowid.'">'.img_delete('', 'class="marginleftonly"');
					print "</td></tr>";
			$i ++;
		}
		if($num == 0)
            print '<tr><td align="center" colspan="3">Auccun Salaire Catégorie disponible!</td></tr>';
	}else print '<tr><td align="center" colspan="3">Auccun Salaire Catégorie disponible!</td></tr>';

	print "<script>
        function myFunction(e){
           var b = 'delete'+e;
           var button_generer = document.getElementById(b);
           if(!confirm('Click sur OK pour confirmer cette suppression')){
               var lien = '".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenu=categorie&action=afficher';
               button_generer.setAttribute('href', lien);
           
           }
          }
        
        </script>";
}
print '</table></form>';

if($message != ''){
	$action = 'create';
		
		print "<script>
		$.jnotify('".$message."', {delay : 5000, fadeSpeed: 500});
		</script>";
}