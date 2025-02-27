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
$action = GETPOST('action', 'alpha');

if(!empty(GETPOST('action', 'alpha')))
	$action = GETPOST('action', 'alpha');

	$id_convention = GETPOST('id_convention','int')?:0;

if($action == "detailprime"){
	print load_fiche_titre($langs->trans("Informations sur cette Prime"), '', '');

	print_barre_liste("", $page, $_SERVER["PHP_SELF"], "", "", "", "", "", "", 'bill', 0, dolGetButtonTitle("Créer une nouvelle Prime", '', 'fa fa-plus-circle', './listeprime.php?idmenu=4399&mainmenu=paiementsalaire&leftmenu=prime&action=create' , '', 1), '', 0, 0, 0, 1);
	print '<hr>';	
	$id_prime = GETPOST('id_prime', 'int')?:0;
	print "<div><table>";
	print '<tr class="liste_titre"><td align="center" class="liste_titre" colspan="4" style="padding: 10px; width : 5%;" >Primes</td></tr>';
	print '<tr class="liste_titre"><td class="liste_titre" style="padding: 10px; width : 5%;" >Libellé</td><td class="liste_titre" style="padding: 10px; width : 5%;" >Type</td><td class="liste_titre" style="padding: 10px; width : 5%;" >Valeur</td><td class="liste_titre" style="padding: 10px; width : 5%;" >Appliquée au</td></tr>';

	//Informations liées à la prime
	$primeSql = "SELECT * FROM ".MAIN_DB_PREFIX."primes WHERE rowid=".$id_prime;
	$result = $db->query($primeSql);
	$obj = $db->fetch_object($result);
	$type = "Obligatoire";
	if($obj->type_prime=="facultative" )
		$type = "Facultative";
	
	if($obj->appliquee==1){
		$appliquee = "Salaire de base";
	}elseif($obj->appliquee==2){
		$appliquee = "Salaire de base Imposable";
	}else $appliquee = "Montatnt Fixe";

	print '<tr class="pair"><td style="padding: 10px;">'.$obj->libelle.'</td><td>'.$type.'</td><td>----</td><td>'.$appliquee.'</td></tr>';
	print '<tr ><td align="center"  colspan="4" style="padding: 10px; width : 5%;" ></td></tr>';
	

	//les convention qui beneficient cette prime
	print '<tr class="liste_titre"><td align="center" class="liste_titre" colspan="4" style="padding: 10px; width : 5%;" >Conventions</td></tr>';
	print '<tr class="liste_titre"><td align="center" class="liste_titre" colspan="2" style="padding: 10px; width : 5%;" >Nom</td><td align="center" class="liste_titre" colspan="2" style="padding: 10px; width : 5%;" >Commentaires</td></tr>';
	if($obj->fk_convention != 0){
		$fk_Sql = "SELECT DISTINCT nom, commentaire FROM ".MAIN_DB_PREFIX."convention WHERE fk_prime=".$obj->fk_convention;
		$result1 = $db->query($fk_Sql);
		if($result1){
			$j = 0;
			$num = $db->num_rows($result1);
			while ($j < $num){
				$obj1 = $db->fetch_object($result1);
				
						print '<tr><td style="padding: 10px;" align="center" class="pair" colspan="2">'.$obj1->nom.'</td>';
						print '<td style="padding: 10px;" align="center" class="pair" colspan="2">'.$obj2->commentaire.'</td></tr>';
				$j++;
			}
			if($num == 0)
				print "<tr><td align='center' colspan='4'>Aucune précisée</td></tr>";
		}else print "<tr><td align='center' colspan='4'>Aucune précisée</td></tr>";

	}else{
			print '<tr><td style="padding: 10px;" align="center" class="pair" colspan="2">Toutes les conventions</td>';
			print '<td style="padding: 10px;" align="center" class="pair" colspan="2">Prime globale à toutes les conventions</td></tr>';
	}
	print '<tr ><td align="center"  colspan="4" style="padding: 10px; width : 5%;" ></td></tr>';

	//les catégories qui beneficient cette prime
	print '<tr class="liste_titre"><td align="center" class="liste_titre" colspan="4" style="padding: 10px; width : 5%;" >Catégories</td></tr>';
	print '<tr class="liste_titre"><td align="center" class="liste_titre" colspan="2" style="padding: 10px; width : 5%;" >Code</td><td align="center" class="liste_titre" colspan="2" style="padding: 10px; width : 5%;" >Noms</td></tr>';
		$fk_Sql = "SELECT DISTINCT rowid FROM ".MAIN_DB_PREFIX."condition_prime WHERE fk_prime=".$id_prime;
		$result1 = $db->query($fk_Sql);
		if($result1){
			$j = 0;
			$num = $db->num_rows($result1);
			while ($j < $num){
				$obj1 = $db->fetch_object($result1);

				$categ_pr_sql = "SELECT DISTINCT fk_categorie FROM ".MAIN_DB_PREFIX."condition_categorie_prime WHERE fk_condition=".$obj1->rowid;
				$res_categ_pr = $db->query($categ_pr_sql);
				if($res_categ_pr){
					
					$k = 0;
					$num_categ_pr = $db->num_rows($res_categ_pr);
					$cat = "";
					while ($k < $num_categ_pr){
						$obj_categ_pr = $db->fetch_object($res_categ_pr);
						if($obj_categ_pr->fk_categorie != 0){
							$categSql = "SELECT * FROM ".MAIN_DB_PREFIX."dcategories WHERE rowid=".$obj_categ_pr->fk_categorie;
							$result_categ = $db->query($categSql);
							if($result_categ){
									$obj_categ = $db->fetch_object($result_categ);
									if($cat != $obj_categ->code_categorie){
										$cat = $obj_categ->code_categorie;
										print '<tr><td style="padding: 10px;" align="center" class="pair" colspan="2">'.$obj_categ->code_categorie.'</td>';
										print '<td style="padding: 10px;" align="center" class="pair" colspan="2">'.$obj_categ->nom_categorie.'</td></tr>';
									}
							}
						}else{
							print '<tr><td style="padding: 10px;" align="center" class="pair" colspan="4">Toutes les catégories</td>';
							$k = $num_categ_pr;
							$j = $num;
						}
						$k ++;
					}
				}
				$j++;
			}
			if($num == 0)
				print "<tr><td align='center' colspan='4'>Aucune catégorie pour cette prime</td></tr>";
		}else print "<tr><td align='center' colspan='4'>Aucune catégorie pour cette prime</td></tr>";
		print '<tr ><td align="center"  colspan="4" style="padding: 10px; width : 5%;" ></td></tr>';

		print "</table></div>";

	
}
if($action == "detailindemnite"){
	print load_fiche_titre($langs->trans("Informations sur cette Indemnité"), '', '');

	print_barre_liste("", $page, $_SERVER["PHP_SELF"], "", "", "", "", "", "", 'bill', 0, dolGetButtonTitle("Créer une nouvelle indemnité", '', 'fa fa-plus-circle', './listeindemnite.php?idmenu=4399&mainmenu=paiementsalaire&leftmenu=indemnite&action=create' , '', 1), '', 0, 0, 0, 1);
	print '<hr>';	
	$id_indemnite = GETPOST('id_indemnite', 'int');
	print "<div><table>";
	print '<tr class="liste_titre"><td class="liste_titre" style="padding: 10px; width : 5%;" >Libellé</td><td class="liste_titre" style="padding: 10px; width : 5%;" >Type</td><td class="liste_titre" style="padding: 10px; width : 5%;" >Valeur</td><td class="liste_titre" style="padding: 10px; width : 5%;" >Appliquée au</td></tr>';
	//les informations de l'indemnité
	$primeSql = "SELECT * FROM ".MAIN_DB_PREFIX."indemnite WHERE rowid=".$id_indemnite;
	$result = $db->query($primeSql);
	$obj = $db->fetch_object($result);
	$type = "Obligatoire";
	if($obj->type_indemnite == "facultative")
		$type = "Facultative";
	
	if($obj->appliquee==1){
		$appliquee = "Salaire de base";
	}elseif($obj->appliquee==2){
		$appliquee = "Salaire de base Imposable";
	}else $appliquee = "Montatnt Fixe";
	print '<tr class="pair"><td style="padding: 10px;" >'.$obj->libelle.'</td><td>'.$type.'</td><td>'.$obj->valeur_indemnite.'</td><td>'.$appliquee.'</td></tr>';
	print '<tr ><td align="center"  colspan="4" style="padding: 10px; width : 5%;" ></td></tr>';

	//les convention qui beneficient cette prime
	print '<tr class="liste_titre"><td align="center" class="liste_titre" colspan="4" style="padding: 10px; width : 5%;" >Conventions</td></tr>';
	print '<tr class="liste_titre"><td align="center" class="liste_titre" colspan="2" style="padding: 10px; width : 5%;" >Nom</td><td align="center" class="liste_titre" colspan="2" style="padding: 10px; width : 5%;" >Commentaires</td></tr>';
	if($obj->fk_convention != 0){
		$fk_Sql = "SELECT DISTINCT nom, commentaire FROM ".MAIN_DB_PREFIX."convention WHERE rowid=".$obj->fk_convention;
		$result1 = $db->query($fk_Sql);
		if($result1){
			$j = 0;
			$num = $db->num_rows($result1);
			while ($j < $num){
				$obj1 = $db->fetch_object($result1);
				
						print '<tr><td style="padding: 10px;" align="center" class="pair" colspan="2">'.$obj1->nom.'</td>';
						print '<td style="padding: 10px;" align="center" class="pair" colspan="2">'.$obj1->commentaire.'</td></tr>';
				$j++;
			}
			if($num == 0)
				print "<tr><td align='center' colspan='4'>Aucune convention précisée</td></tr>";
		}else print "<tr><td align='center' colspan='4'>Aucune convention précisée</td></tr>";

	}else{
			print '<tr><td style="padding: 10px;" align="center" class="pair" colspan="2">Toutes les conventions</td>';
			print '<td style="padding: 10px;" align="center" class="pair" colspan="2">Prime globale à toutes les conventions</td></tr>';
	}
	print '<tr ><td align="center"  colspan="4" style="padding: 10px; width : 5%;" ></td></tr>';

	//les catégories qui beneficient cette prime
	print '<tr class="liste_titre"><td align="center" class="liste_titre" colspan="4" style="padding: 10px; width : 5%;" >Catégories</td></tr>';
	print '<tr class="liste_titre"><td align="center" class="liste_titre" colspan="2" style="padding: 10px; width : 5%;" >Code</td><td align="center" class="liste_titre" colspan="2" style="padding: 10px; width : 5%;" >Noms</td></tr>';
		$fk_Sql = "SELECT DISTINCT rowid FROM ".MAIN_DB_PREFIX."condition_indemnite WHERE fk_indemnite=".$id_indemnite;
		$result1 = $db->query($fk_Sql);
		if($result1){
			$j = 0;
			$num = $db->num_rows($result1);
			while ($j < $num){
				$obj1 = $db->fetch_object($result1);

				$categ_ind_sql = "SELECT DISTINCT fk_categorie FROM ".MAIN_DB_PREFIX."condition_categorie_indemnite WHERE fk_condition=".$obj1->rowid;
				$res_categ_ind = $db->query($categ_ind_sql);
				if($res_categ_ind){
					
					$k = 0;
					$num_categ_ind = $db->num_rows($res_categ_ind);
					$cat = "";
					while ($k < $num_categ_ind){
						$obj_categ_ind = $db->fetch_object($res_categ_ind);
						if($obj_categ_ind->fk_categorie != 0){
							$categSql = "SELECT * FROM ".MAIN_DB_PREFIX."dcategories WHERE rowid=".$obj_categ_ind->fk_categorie;
							$result_categ = $db->query($categSql);
							if($result_categ){
									$obj_categ = $db->fetch_object($result_categ);
									if($cat != $obj_categ->code_categorie){
										$cat = $obj_categ->code_categorie;
										print '<tr><td style="padding: 10px;" align="center" class="pair" colspan="2">'.$obj_categ->code_categorie.'</td>';
										print '<td style="padding: 10px;" align="center" class="pair" colspan="2">'.$obj_categ->nom_categorie.'</td></tr>';
									}
							}
						}else{
							print '<tr><td style="padding: 10px;" align="center" class="pair" colspan="4">Toutes les catégories</td>';
							$k = $num_categ_ind;
							$j = $num;
						}
						$k ++;
					}
				}
				$j++;
			}
			if($num == 0)
				print "<tr><td align='center' colspan='4'>Aucune catégorie pour cette indemnite</td></tr>";
		}else print "<tr><td align='center' colspan='4'>Aucune catégorie pour cette indemnite</td></tr>";
		print '<tr ><td align="center"  colspan="4" style="padding: 10px; width : 5%;" ></td></tr>';

		print "</table></div>";
}
$db->free();