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
print load_fiche_titre($langs->trans("Liste des Contrats"), '', '');

// Recuperation des information après le clique sur l'onglet Salaire au niveau du module user
			$action = GETPOST("action", "alpha");


			$salaire_base = 0;
			$message = "";
			$annee = date("Y");
			$mois = (int)date("m");

			$numero_contrat = GETPOST("numero_contrat", "alpha");
			$type_contrat = GETPOST("type_contrat", "int");
			$nom_prenom = GETPOST("nom_prenom", "alpha");
			$id_societe = GETPOST("id_societe", "int");
			$date_fin = GETPOST("date_fin", "int");
			$statut = GETPOST("statut", "int");
	//print_barre_liste("", $page, $_SERVER["PHP_SELF"], "", "", "", "", "", "", 'bill', 0, dolGetButtonTitle("Ajouter un nouveau contrat", '', 'fa fa-plus-circle', $_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=salarie&id='.$fk_user.'&fk_salarie='.$fk_salarie.'&id_convention='.$id_convention.'&id_societe='.$id_societe.'&action=ajouter' , '', 1), '', 0, 0, 0, 1);
	//Partie affichage du Contrat ------------------------------------------------------------------------------------------------------------------------------------------
			$acts[0] = "activate";
			$acts[1] = "disable";
			$actl[0] = img_picto($langs->trans("Disabled"), 'switch_off', 'class="size15x"');
			$actl[1] = img_picto($langs->trans("Activated"), 'switch_on', 'class="size15x"');
			$array_id_soc = "(0";
			$sql = "SELECT fk_soc FROM ".MAIN_DB_PREFIX."societe_commerciaux";
			$sql .= " WHERE fk_user=".$user->id;
			$result = $db->query($sql);
			if($result){
				$i = 0;
				$num = $db->num_rows($result);
				while ($i < $num){
					$array_id_soc .= ", ".$db->fetch_object($result)->fk_soc;
					$i ++;
				}
			}
			$array_id_soc .= ")";
		//les contrats expirés
		print "<hr><div>";
		print "<h3 >Tous les contrats</h3>";
		print "<table class='tagtable liste'>";

		print '<form name="ajouter" method="POST" action="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=contrat">';
		print '<input type="hidden" name="token" value="'.newToken().'">';
		print '<input type="hidden" name="action" value="recherche">';
		//type contrat
		print "<tr class='liste_titre'><td >N° Contrat<br><input type='text' name='numero_contrat' value='".$numero_contrat."' size='10'>";
		print "</td><td >Type<br><select name='type_contrat'>";
		print "<option value=0></option>";

		$sql = "SELECT rowid, libelle FROM ".MAIN_DB_PREFIX."type_contrat";
		$result = $db->query($sql);

		if($result){
			$i = 0;
			$num = $db->num_rows($result);
			while ($i < $num){
				$typ_contrat = $db->fetch_object($result);
				if($type_contrat == $typ_contrat->rowid)
					print "<option value=".$typ_contrat->rowid." selected>".$typ_contrat->libelle."</option>";
				else
					print "<option value=".$typ_contrat->rowid.">".$typ_contrat->libelle."</option>";
				$i ++;
			}

		}

	print "</select>";
		print "</td><td >Salarié<br><input type='text' name='nom_prenom' value='".$nom_prenom."' size='10'>";

		//Société
		print "</td><td>Société<br><select name='id_societe'>";
		print "<option value=0 ></option>";
		$sql = "SELECT sc.rowid as r1, sc.nom, sc.name_alias, sc.phone, sc.fax, sc.code_client, sc.zip, sce.rowid as r2, sce.fk_object, sce.conv FROM ".MAIN_DB_PREFIX."societe as sc";
		$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."societe_extrafields as sce ON sc.rowid=sce.fk_object";
		if($user->id != 1)
			$sql .= " WHERE sc.rowid IN ".$array_id_soc." AND sce.grp=1";
		else   
			$sql .= " WHERE sce.grp=1";

		$result = $db->query($sql);

		if($result){
			$i = 0;
			$num = $db->num_rows($result);
			while ($i < $num){
				$societe = $db->fetch_object($result);
				if($id_societe == $societe->r1)
					print "<option value=".$societe->r1." selected>".$societe->nom."</option>";
				else print "<option value=".$societe->r1.">".$societe->nom."</option>";
				$i ++;
			}
		}

	print "</select>";

	//Date fin
	$sel3 ="";
	$sel6 ="";
	$sel9 ="";

		print "</td><td>Date fin</td><td >Mois restant(s)<br><select name='date_fin'>";
		if($date_fin == 3)
			$sel3 = "selected";
		elseif($date_fin == 6)
			$sel6 = "selected";
		elseif($date_fin == 9)
			$sel9 = "selected";
		print "<option value=0 ></option>";
		print "<option value=3 ".$sel3." >3 mois</option>";
		print "<option value=6 ".$sel6." >6 mois</option>";
		print "<option value=9 ".$sel9." >9 mois</option>";
		print "</select>";

		//statut
		print "</td><td >Statut <input type='submit' class='button' value='Rechercher' ><br><select name='statut'>";
		$actif = "";
		$expire = "";
		if($statut == 1)
			$actif = "selected";
		else if($statut == 2)
			$expire = "selected";

		print "<option value='' ></option>";
		print "<option value=1 ".$actif." >Actif</option>";
		print "<option value=2 ".$expire." >Expiré</option>";
		print "</select>";
		print "</form>";
		print '<a class="button"href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=contrat" >Annuler</a>';
		print "</td></td></tr>";

		$annee = (int)date("Y");
		$mois = (int)date("m");
		$jour = (int)date("d");

		//les contrats qui finissent dans 6 mois ue.egp=".$id_societe."
		$sql = 'SELECT sal.rowid as id_salarie, sal.matricule, sal.fk_user, u.rowid as id_user, u.lastname, u.firstname, u.dateemployment, ue.fk_object, ue.egp, soc.nom, soc.rowid as id_societe, sce.conv, sce.fk_object, sce.grp, c.*';
			$sql .= ' FROM '.MAIN_DB_PREFIX.'salarie as sal';
			$sql .= ' LEFT JOIN '.MAIN_DB_PREFIX.'user as u ON u.rowid=sal.fk_user';
			$sql .= ' LEFT JOIN '.MAIN_DB_PREFIX.'user_extrafields as ue ON u.rowid=ue.fk_object';
			$sql .= ' LEFT JOIN '.MAIN_DB_PREFIX.'societe as soc ON soc.rowid=ue.egp';
			$sql .= ' LEFT JOIN '.MAIN_DB_PREFIX.'societe_extrafields as sce ON soc.rowid=sce.fk_object';
			$sql .= ' LEFT JOIN '.MAIN_DB_PREFIX.'salarie_contrat as c ON c.fk_salarie=sal.rowid';

			if($user->id != 1)
				$sql .= ' WHERE soc.rowid IN '.$array_id_soc.' AND sce.grp=1 AND c.numero != ""';
			else   
				$sql .= ' WHERE sce.grp=1 AND c.numero != ""';


			//Ajout des champs de recherche à la requête
			if(!empty($numero_contrat))
				$sql .= ' AND c.numero LIKE "%'.$numero_contrat.'%"';

			if(!empty($type_contrat))
				$sql .= ' AND c.fk_type_contrat='.$type_contrat;

			if(!empty($nom_prenom)){
				$sql .= ' AND (u.lastname LIKE "%'.$nom_prenom.'%"';
				$sql .= ' OR u.firstname LIKE "%'.$nom_prenom.'%"';
				$tableau = explode(" ", $nom_prenom);
				if(count($tableau) > 1){
					$sql .= ' OR u.lastname LIKE "%'.$tableau[0].'%"';
					$sql .= ' OR u.firstname LIKE "%'.$tableau[1].'%"';
					$sql .= ' OR u.lastname LIKE "%'.$tableau[1].'%"';
					$sql .= ' OR u.firstname LIKE "%'.$tableau[0].'%")';
				}else $sql .= ')';


			}

			if(!empty($id_societe))
				$sql .= " AND soc.rowid=".$id_societe;

			if ($statut == 2)
				$sql .= " AND c.active=0";
			else if($statut == 1)
				$sql .= " AND c.active=1";

			if(!empty($date_fin)){
				$annee = (int)date("Y");
				$mois = (int)date("m");
				$sql .= " AND (( YEAR(c.date_fin)>".$annee." AND (MONTH(c.date_fin) + 12 - ".$mois.") <= ".$date_fin." AND ( MONTH(c.date_fin) +12 - ".$mois.") > 0)";
				$sql .= " OR (YEAR(c.date_fin) = ".$annee."  AND MONTH(c.date_fin) >= ".$mois." AND  (MONTH(c.date_fin) - ".$mois." <= ".$date_fin.")  ))";
			}
		if(empty($date_fin))
			$sql .= " ORDER BY c.numero ASC";
		/*else
			$sql .= " ORDER BY c.date_fin DESC";*/

			//$res_contrat = $db->query($sql_contrat);
			$res_contrat = $db->query($sql);

			$actl[0] = img_picto("actif", 'switch_off', 'class="size15x"');
			$actl[1] = img_picto("expiré", 'switch_on', 'class="size15x"');
			if($res_contrat){
				$num = $db->num_rows($res_contrat);
				$i = 0;
				while($i <$num){
					$obj_mixte = $db->fetch_object($res_contrat);
					$d_f = "";
					if(!empty($obj_mixte->date_fin)){
						$tab = explode("-", $obj_mixte->date_fin);

						$d_f = $tab[2]."-".$tab[1]."-".$tab[0];

						if($tab[0] == date("Y")){
							if($tab[1] > ((int)date("m")+1))
								$d_f = ($tab[1] - (int)date("m"))." mois";
							else if($tab[1]== date("m")){
								 if($tab[2] > date("d"))
								 	$d_f = ($tab[2] - (int)date("d"))." jour(s)";
								 else if($tab[2] == date("d"))
								 	$d_f = "Aujourd'hui";
								 else $d_f = "Expiré";
							}else $d_f = "Expiré";
						}else if($tab[0] > date("Y")){
							$d_f = (($tab[0] - (int)date("Y"))*12 + $tab[1] - (int)date("m"))." mois";
						}else $d_f = "Expiré";
					}
					print "<tr class='fieldrequired'><td ><a href='../onglets/contrat_information.php?mainmenu=paiementsalaire&leftmenu=salarie&id_societe=".$obj_mixte->id_societe."&fk_salarie=".$obj_mixte->id_salarie."&id_convention=".$obj_mixte->conv."&id=".$obj_mixte->id_user."&id_contrat=".$obj_mixte->rowid."'>".($obj_mixte->numero?:"N/A")."</a></td>";

						$sql_type_contrat = "SELECT libelle FROM ".MAIN_DB_PREFIX."type_contrat WHERE rowid=".$obj_mixte->fk_type_contrat;
						$restype_contrat = $db->query($sql_type_contrat);
						if($restype_contrat)
						$obj_type_contrat = $db->fetch_object($res_type_contrat);

						print "<td>".($obj_type_contrat->libelle?:"N/A")."</td>";
						$intutile = $obj_mixte->firstname." ".$obj_mixte->lastname;
						print "<td >".($intutile ?:"N/A")."</td>";
						print "<td >".($obj_mixte->nom)."</td>";
						print "<td >".($obj_mixte->date_fin?:"&#8734;")."</td>";
						print "<td >".($d_f?:"&#8734;")."</td>";

						print "<td >".$actl[$obj_mixte->active]."</a></td>";
						//print "<td></td>";

						print '</tr>';

					$i ++;
				}
				if($num == 0)
					print "<tr><td align='center' colspan=7> Pas de Contrat</td></tr>";

			}else print "<tr><td align='center' colspan=7> Pas de Contrat</td></tr>";
			print "</table></div>";
			$db->free();
