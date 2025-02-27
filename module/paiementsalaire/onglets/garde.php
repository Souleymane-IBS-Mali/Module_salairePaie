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
 * Copyright (C) 2020      Josep Lluís Amador   <joseplluis@lliureticat>
 * Copyright (C) 2021      Frédéric France		<frederifrance@netlogifr>
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
include_once DOL_DOCUMENT_ROOT.'/core/class/dolgraph.class.php';




llxHeader("", "Paiement | Salaire");
//Titre 
print load_fiche_titre($langs->trans("Salariés"), '', '');


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

// Recuperation des information après le clique sur l'onglet Salaire au niveau du module user
$total = 0;


//Graphe des types de contrats
$dataseries = array();

$sql_societe = "SELECT sc.rowid, sc.nom, sce.rowid as r2, sce.fk_object FROM ".MAIN_DB_PREFIX."societe as sc";
$sql_societe .= " LEFT JOIN ".MAIN_DB_PREFIX."societe_extrafields as sce ON sc.rowid=sce.fk_object";		
if($user->id != 1)
    $sql_societe .= " WHERE sc.rowid IN ".$array_id_soc." AND sce.grp=1";
else   
    $sql_societe .= " WHERE sce.grp=1";

$res_societe = $db->query($sql_societe);
			if($res_societe){
				$nb_societe = $db->num_rows($res_societe);
				$total = $nb_societe;
				$i = 0;
				while($i < $nb_societe){
					$obj_societe = $db->fetch_object($res_societe);
					$sql = "SELECT sal.fk_user, u.rowid, ue.fk_object, ue.egp FROM ".MAIN_DB_PREFIX."salarie as sal";
					$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."user as u ON u.rowid=sal.fk_user";
					$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."user_extrafields as ue ON u.rowid=ue.fk_object Where ue.egp=".$obj_societe->rowid;
					$res_salarie = $db->query($sql);
					$nb_salarie = $db->num_rows($res_salarie);

					$dataseries[] = array($obj_societe->nom." (".$nb_salarie.")", $nb_salarie);


					$i++;
				}
			}


$salarie_societe_graph = '<div class="div-table-responsive-no-min">';
	$salarie_societe_graph .= '<table class="noborder nohover centpercent">'."\n";
	$salarie_societe_graph .= '<tr class="liste_titre"><th colspan="2">'.$langs->trans("Nombre de salarié par société").'</th></tr>';
	$dolgraph = new DolGraph();
	$dolgraph->SetData($dataseries);
	$dolgraph->setShowLegend(2);
	$dolgraph->setShowPercent(1);
	$dolgraph->SetType(array('pie'));
	$dolgraph->setHeight('200');
	$dolgraph->draw('idgraphsalariesociete');
	$salarie_societe_graph .= '<tr><td>'.$dolgraph->show();
	$salarie_societe_graph .= '</td></tr>';
	$salarie_societe_graph .= '<tr class="liste_total"><td>Nombre total de société</td><td class="right">';
	$salarie_societe_graph .= $total;
	$salarie_societe_graph .= '</td></tr>';
	$salarie_societe_graph .= '</table>';
	$salarie_societe_graph .= '</div>';

print '<div class="clearboth"></div>';
print '<div class="fichecenter fichecenterbis">';



$sql_last_modif = "SELECT sal.rowid as id_salarie, sal.matricule, sal.fk_user, sal.date_modification, u.rowid as id_user, u.lastname, u.firstname, u.dateemployment, ue.fk_object, ue.egp, sc.rowid as id_societe, sc.nom as nom_societe, sce.fk_object, sce.conv FROM ".MAIN_DB_PREFIX."salarie as sal";

$sql_last_modif .= " LEFT JOIN ".MAIN_DB_PREFIX."user as u ON u.rowid=sal.fk_user";
$sql_last_modif .= " LEFT JOIN ".MAIN_DB_PREFIX."user_extrafields as ue ON u.rowid=ue.fk_object";
$sql_last_modif .= " LEFT JOIN ".MAIN_DB_PREFIX."societe as sc on ue.egp=sc.rowid";
$sql_last_modif .= " LEFT JOIN ".MAIN_DB_PREFIX."societe_extrafields as sce ON sc.rowid=sce.fk_object";
if($user->id != 1)
    $sql_last_modif .= " WHERE sc.rowid IN ".$array_id_soc." AND sce.grp=1 ORDER BY sal.date_modification DESC";
else   
    $sql_last_modif .= " WHERE sce.grp=1 ORDER BY sal.date_modification DESC";

	$res_last_modif = $db->query($sql_last_modif);
	if($res_last_modif){
		$last_modif = "\n<!-- last thirdparties modified -->\n";
		$last_modif .= '<div class="div-table-responsive-no-min">';
		$last_modif .= '<table class="noborder centpercent">';

		$last_modif .= '<tr class="liste_titre"><th colspan="2">Liste des 15 derniers salariés modifiés</th>';
		$last_modif .= '<th>&nbsp;</th>';
		$last_modif .= '<th class="right"><a href="../listesalarie.php?leftmenu=salarie">'.$langs->trans("FullList").'</th>';
		$last_modif .= '</tr>'."\n";
		$num = $db->num_rows($res_last_modif);
		if($num > 15)
			$num = 15;
		$i = 0;
		while($i <$num){
			$obj_mixte = $db->fetch_object($res_last_modif);				

			$last_modif .= '<tr class="oddeven">';
			// Numéro contrat
			$last_modif .= '<td class="nowrap tdoverflowmax200">';
			$last_modif .= "<a href='./salarie_information.php?mainmenu=paiementsalaire&leftmenu=salarie&id_societe=".$obj_mixte->id_societe."&fk_salarie=".$obj_mixte->id_salarie."&id_convention=".$obj_mixte->conv."&id=".$obj_mixte->id_user."'>".($obj_mixte->firstname?:"N/A")."</a>";
			$last_modif .= "</td>\n";
			

			$last_modif .= '<td class="center">';
			$last_modif .= "<a href='./salarie_information.php?mainmenu=paiementsalaire&leftmenu=salarie&id_societe=".$obj_mixte->id_societe."&fk_salarie=".$obj_mixte->id_salarie."&id_convention=".$obj_mixte->conv."&id=".$obj_mixte->id_user."'>".($obj_mixte->lastname?:"N/A")."</a>";
			$last_modif .= '</td>';
			// Last modified date
			$last_modif .= '<td class="right tddate">';
			$last_modif .= "<a href='../../societe/card.php?socid=".$obj_mixte->id_societe."'>".($obj_mixte->nom_societe?:"N/A")."</a>";
			$last_modif .= "</td>";
			$last_modif .= '<td class="right nowrap">';
			$last_modif .= $obj_mixte->date_modification;
			$last_modif .= "</td>";

			$last_modif .= "</tr>\n";
			$i++;
		}

		$db->free($res_contrat);

		$liste_expire_3mois .= "</table>\n";
		$liste_expire_3mois .= '</div>';
		$liste_expire_3mois .= "<!-- End last thirdparties modified -->\n";
	}
$boxlist = '<div class="twocolumns">';

$boxlist .= '<div class="firstcolumn fichehalfleft boxhalfleft" id="boxhalfleft">';
$boxlist .=$salarie_societe_graph;
$boxlist .= '</div>'."\n";


$boxlist .= '<div class="secondcolumn fichehalfright boxhalfright" id="boxhalfright">';
$boxlist .=$last_modif;
$boxlist .= '</div>'."\n";

$boxlist .= '</div>';


$boxlist.= '</div>';

print $boxlist;