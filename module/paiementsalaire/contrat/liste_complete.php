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
print load_fiche_titre($langs->trans("Contrats"), '', '');

// Recuperation des information après le clique sur l'onglet Salaire au niveau du module user
$action = GETPOST("action", "aplha");
$nb_mois = GETPOST("nb_mois", "int");

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

	//print_barre_liste("", $page, $_SERVER["PHP_SELF"], "", "", "", "", "", "", 'bill', 0, dolGetButtonTitle("Ajouter un nouveau contrat", '', 'fa fa-plus-circle', $_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=salarie&id='.$fk_user.'&fk_salarie='.$fk_salarie.'&id_convention='.$id_convention.'&id_societe='.$id_societe.'&action=ajouter' , '', 1), '', 0, 0, 0, 1);
	//Partie affichage du Contrat ------------------------------------------------------------------------------------------------------------------------------------------
			
		//les contrats expirés
		print "<hr><div>";
		print "<h3 >Les contrats qui Expirent dans ".$nb_mois." mois</h3>";
		print "<table class='tagtable liste'>";
		print "<tr class='liste_titre'><td >N° Contrat</td><td >Type</td><td >Salarié";
		print "</td><td >Société</td><td >Date fin</td><td >Opération</td></tr>";

		$annee = (int)date("Y");
		$mois = (int)date("m");
		$jour = (int)date("d");

		//les contrats qui finissent dans 3 mois ue.egp=".$id_societe."
		$sql = "SELECT sal.rowid as id_salarie, sal.matricule, sal.fk_user, u.rowid as id_user, u.lastname, u.firstname, u.dateemployment, ue.fk_object, ue.egp, soc.nom, soc.rowid as id_societe, sce.conv, sce.fk_object, sce.grp, c.*";
			$sql .= " FROM ".MAIN_DB_PREFIX."salarie as sal";
			$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."user as u ON u.rowid=sal.fk_user";
			$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."user_extrafields as ue ON u.rowid=ue.fk_object";
			$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."societe as soc ON soc.rowid=ue.egp";
			$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."societe_extrafields as sce ON soc.rowid=sce.fk_object";
			$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."salarie_contrat as c ON c.fk_salarie=sal.rowid";
			$sql .= " WHERE sce.grp=1 AND c.active=1 AND c.fk_type_contrat != 2";

			if($user->id != 1)
        		$sql .= " AND soc.rowid IN ".$array_id_soc;

		$sql .= " AND (( YEAR(c.date_fin)>".$annee." AND (MONTH(c.date_fin) + 12 - ".$mois.") <= ".$nb_mois." AND ( MONTH(c.date_fin) +12 - ".$mois.") > (".$nb_mois."-3) )";
		$sql .= " OR (YEAR(c.date_fin) = ".$annee."  AND MONTH(c.date_fin) >= ".$mois." AND  (MONTH(c.date_fin) - ".$mois." > ".$nb_mois." -3)  ))";
		$sql .= " ORDER BY sal.rowid";

			//$res_contrat = $db->query($sql_contrat);
			$res_contrat = $db->query($sql);
			$acts[0] = "activate";
			$acts[1] = "disable";
			$actl[0] = img_picto($langs->trans("Disabled"), 'switch_off', 'class="size15x"');
			$actl[1] = img_picto($langs->trans("Activated"), 'switch_on', 'class="size15x"');
			if($res_contrat){
				$num = $db->num_rows($res_contrat);
				$i = 0;
				while($i <$num){
					$obj_mixte = $db->fetch_object($res_contrat);				
					print "<tr class='fieldrequired'><td ><a href='../onglets/contrat_information.php?mainmenu=paiementsalaire&leftmenu=salarie&id_societe=".$obj_mixte->id_societe."&fk_salarie=".$obj_mixte->id_salarie."&id_convention=".$obj_mixte->conv."&id=".$obj_mixte->id_user."&id_contrat=".$obj_mixte->rowid."'>".($obj_mixte->numero?:"N/A")."</a></td>";

						$sql_type_contrat = "SELECT libelle FROM ".MAIN_DB_PREFIX."type_contrat WHERE rowid=".$obj_mixte->fk_type_contrat;
						$restype_contrat = $db->query($sql_type_contrat);
						if($restype_contrat)
						$obj_type_contrat = $db->fetch_object($res_type_contrat);
						
						print "<td>".($obj_type_contrat->libelle?:"N/A")."</td>";
						$intutile = $obj_mixte->firstname." ".$obj_mixte->lastname;
						print "<td >".($intutile ?:"N/A")."</td>";
						print "<td >".($obj_mixte->nom)."</td>";
						print "<td >".($obj_mixte->date_fin?:"N/A")."</td>";

						/*$sql_salaire_net  = "SELECT * FROM ".MAIN_DB_PREFIX."salarie_contrat_salaire_net WHERE fk_contrat=".$obj_mixte->rowid;
						$res_salaire_net  = $db->query($sql_salaire_net );
						$obj_salaire_net = $db->fetch_object($res_salaire_net );
						print "<td >".($obj_salaire_net->salaire_net)."</td>";*/
						print "<td >".$actl[$obj_mixte->active]."</a></td>";



						print '</tr>';

					$i ++;
				}
				if($num == 0)
					print "<tr><td align='center' colspan=6> Pas de Contrat</td></tr>";

			}else print "<tr><td align='center' colspan=6> Pas de Contrat</td></tr>";
			print "</table></div>";
			$db->free();