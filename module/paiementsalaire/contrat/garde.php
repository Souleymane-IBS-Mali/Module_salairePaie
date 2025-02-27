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
print load_fiche_titre($langs->trans("Contrats"), '', '');

// Recuperation des information après le clique sur l'onglet Salaire au niveau du module user
$action = GETPOST("action");


$salaire_base = 0;
$message = "";
$annee = date("Y");
$mois = (int)date("m");
$jours = date("d");
$total = 0;


//Graphe des types de contrats
$dataseries = array();
$sql_contrat = "SELECT rowid, libelle FROM ".MAIN_DB_PREFIX."type_contrat";
$res_contrat = $db->query($sql_contrat);
if($res_contrat){
	$nb_contrat_type = $db->num_rows($res_contrat);
	$i = 0;
	while($i < $nb_contrat_type){
		$nb = 0;
		$obj_contrat = $db->fetch_object($res_contrat);
		$sql_contrat_nb = "SELECT count(rowid) as nb FROM ".MAIN_DB_PREFIX."salarie_contrat WHERE fk_type_contrat=".$obj_contrat->rowid;
		$res_contrat_nb = $db->query($sql_contrat_nb);
		$nb = $db->fetch_object($res_contrat_nb)->nb;
		$total += $nb;
		$dataseries[] = array($obj_contrat->libelle.' ('.$nb.')', $nb);

		$i++;
	}
}
//Les legendes et pourcentages

$typ_contrat_graph = '<div class="div-table-responsive-no-min">';
$typ_contrat_graph .= '<table class="noborder nohover centpercent">'."\n";
$typ_contrat_graph .= '<tr class="liste_titre"><th colspan="2">'.$langs->trans("Répartition par type de contrats").'</th></tr>';
$dolgraph = new DolGraph();
$dolgraph->SetData($dataseries);
$dolgraph->setShowLegend(2);
$dolgraph->setShowPercent(1);
$dolgraph->SetType(array('pie'));
$dolgraph->setHeight('200');
$dolgraph->draw('idgraphcontrattype');
$typ_contrat_graph .= '<tr><td>'.$dolgraph->show();
$typ_contrat_graph .= '</td></tr>';
$typ_contrat_graph .= '<tr class="liste_total"><td>Nombre total de contrat(s)</td><td class="right">';
$typ_contrat_graph .= '<b>'.$total.'</b>';
$typ_contrat_graph .= '</td></tr>';
$typ_contrat_graph .= '</table>';
$typ_contrat_graph .= '</div>';


//Graphe par nombre de mois d'espiration
$expire = 0;
$mois_3 = 0;
$mois_6 = 0;	
$mois_9 = 0;	

$sql = "SELECT * FROM ".MAIN_DB_PREFIX."salarie_contrat WHERE active=1 OR ";
	$sql .= " AND ( YEAR(date_fin)<".$annee;
	$sql .= " OR (YEAR(date_fin) = ".$annee."  AND MONTH(date_fin) < ".$mois." OR  (MONTH(date_fin) = ".$mois." AND DAY(date_fin) < ".$jours.") ))";
	//$res_contrat = $db->query($sql_contrat);
	$res_contrat = $db->query($sql);
	if($res_contrat){
		$expire = $db->num_rows($res_contrat);
	}

	$sql = "SELECT * FROM ".MAIN_DB_PREFIX."salarie_contrat WHERE active=1";
	$sql .= " AND (( YEAR(date_fin)>".$annee." AND (MONTH(date_fin) + 12 - ".$mois.") <= 3 AND ( MONTH(date_fin) +12 - ".$mois.") > 0)";
	$sql .= " OR (YEAR(date_fin) = ".$annee."  AND MONTH(date_fin) >= ".$mois." AND  (MONTH(date_fin) - ".$mois." <= 3) ))";
	//$res_contrat = $db->query($sql_contrat);
	$res_contrat = $db->query($sql);
	if($res_contrat){
		$mois_3 = $db->num_rows($res_contrat);
	}

	$sql = "SELECT * FROM ".MAIN_DB_PREFIX."salarie_contrat WHERE active=1";
	$sql .= " AND (( YEAR(date_fin)>".$annee." AND (MONTH(date_fin) + 12 - ".$mois.") <= 6 AND ( MONTH(date_fin) +12 - ".$mois.") > 3)";
	$sql .= " OR (YEAR(date_fin) = ".$annee."  AND MONTH(date_fin) >= ".$mois." AND  (MONTH(date_fin) - ".$mois." <= 6) AND  (MONTH(date_fin) - ".$mois." > 3)  ))";
	$res_contrat = $db->query($sql);
	if($res_contrat){
		$mois_6 = $db->num_rows($res_contrat);
	}

	//Les legendes et pourcentages
	$dataseries = array();
	$dataseries[] = array("Contrat(s) Expiré(s) (".$expire.")", $mois_3);
	$dataseries[] = array("Expire dans 3 mois (".$mois_3.")", $mois_3);
	$dataseries[] = array("Expire dans 6 mois (".$mois_6.")", $mois_6);
	$dataseries[] = array("Expire dans 9 mois (".$mois_9.")", $mois_9);
	$total = $expire + $mois_3 + $mois_6 + $mois_9;

	$dure_contrat_graph = '<div class="div-table-responsive-no-min">';
	$dure_contrat_graph .= '<table class="noborder nohover centpercent">'."\n";
	$dure_contrat_graph .= '<tr class="liste_titre"><th colspan="2">'.$langs->trans("Contrats prochent de l'expiration").'</th></tr>';
	$dolgraph = new DolGraph();
	$dolgraph->SetData($dataseries);
	$dolgraph->setShowLegend(2);
	$dolgraph->setShowPercent(1);
	$dolgraph->SetType(array('pie'));
	$dolgraph->setHeight('200');
	$dolgraph->draw('idgraphcontratmois');
	$dure_contrat_graph .= '<tr><td>'.$dolgraph->show();
	$dure_contrat_graph .= '</td></tr>';
	$dure_contrat_graph .= '<tr class="liste_total"><td>Nombre total de contrat(s)</td><td class="right">';
	$dure_contrat_graph .= '<b>'.$total.'</b>';
	$dure_contrat_graph .= '</td></tr>';
	$dure_contrat_graph .= '</table>';
	$dure_contrat_graph .= '</div>';

	$actl[0] = img_picto($langs->trans("Disabled"), 'switch_off', 'class="size15x"');
	$actl[1] = img_picto($langs->trans("Activated"), 'switch_on', 'class="size15x"');
	//Les contrats qui expired dans 3 mois
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

	$sql .= " AND (( YEAR(c.date_fin)>".$annee." AND (MONTH(c.date_fin) + 12 - ".$mois.") <= 3 AND ( MONTH(c.date_fin) +12 - ".$mois.") > 0)";
	$sql .= " OR (YEAR(c.date_fin) = ".$annee."  AND MONTH(c.date_fin) >= ".$mois." AND  (MONTH(c.date_fin) - ".$mois." <= 3)  ))";
	$sql .= " ORDER BY sal.rowid";

	$res_contrat = $db->query($sql);
	if($res_contrat){

		$liste_expire_3mois = "\n<!-- last thirdparties modified -->\n";
		$liste_expire_3mois .= '<div class="div-table-responsive-no-min">';
		$liste_expire_3mois .= '<table class="noborder centpercent">';

		$liste_expire_3mois .= '<tr class="liste_titre"><th colspan="2">Contrats qui expirent dans 3 mois</th>';
		$liste_expire_3mois .= '<th>&nbsp;</th>';
		$liste_expire_3mois .= '<th class="right"><a href="'.DOL_URL_ROOT.'/paiementsalaire/contrat/liste_complete.php?leftmenu=contrat&nb_mois=3">'.$langs->trans("FullList").'</th>';
		$liste_expire_3mois .= '</tr>'."\n";
		$num = $db->num_rows($res_contrat);
		if($num > 15)
			$num = 15;
		$i = 0;
		while($i <$num){
			$obj_mixte = $db->fetch_object($res_contrat);				

			$liste_expire_3mois .= '<tr class="oddeven">';
			// Numéro contrat
			$liste_expire_3mois .= '<td class="nowrap tdoverflowmax200">';
			$liste_expire_3mois .= "<a href='../onglets/contrat_information.php?mainmenu=paiementsalaire&leftmenu=contrat&id_societe=".$obj_mixte->id_societe."&fk_salarie=".$obj_mixte->id_salarie."&id_convention=".$obj_mixte->conv."&id=".$obj_mixte->id_user."&id_contrat=".$obj_mixte->rowid."'>".($obj_mixte->numero?:"N/A")."</a>";
			$liste_expire_3mois .= "</td>\n";
			// Type
			$sql_type_contrat = "SELECT libelle FROM ".MAIN_DB_PREFIX."type_contrat WHERE rowid=".$obj_mixte->fk_type_contrat;
			$restype_contrat = $db->query($sql_type_contrat);
			if($restype_contrat)
				$obj_type_contrat = $db->fetch_object($res_type_contrat);

			$liste_expire_3mois .= '<td class="center">';
			$liste_expire_3mois .= $obj_type_contrat->libelle;
			$liste_expire_3mois .= '</td>';
			// Last modified date
			$liste_expire_3mois .= '<td class="right tddate">';
			$liste_expire_3mois .= $obj_mixte->date_fin;
			$liste_expire_3mois .= "</td>";
			$liste_expire_3mois .= '<td class="right nowrap">';
			$liste_expire_3mois .= $actl[$obj_mixte->active];
			$liste_expire_3mois .= "</td>";

			$liste_expire_3mois .= "</tr>\n";
			$i++;
		}

		$db->free($res_contrat);

		$liste_expire_3mois .= "</table>\n";
		$liste_expire_3mois .= '</div>';
		$liste_expire_3mois .= "<!-- End last thirdparties modified -->\n";
	}





	//les contrats qui expires dans 6 mois
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
			
	$sql .= " AND (( YEAR(c.date_fin)>".$annee." AND (MONTH(c.date_fin) + 12 - ".$mois.") <= 6 AND ( MONTH(c.date_fin) +12 - ".$mois.") > 3)";
	$sql .= " OR (YEAR(c.date_fin) = ".$annee."  AND MONTH(c.date_fin) >= ".$mois." AND  (MONTH(c.date_fin) - ".$mois." <= 6) AND  (MONTH(c.date_fin) - ".$mois." > 3)  ))";


	$res_contrat = $db->query($sql);
	if($res_contrat){

		$liste_expire_6mois = "\n<!-- last thirdparties modified -->\n";
		$liste_expire_6mois .= '<div class="div-table-responsive-no-min">';
		$liste_expire_6mois .= '<table class="noborder centpercent">';

		$liste_expire_6mois .= '<tr class="liste_titre"><th colspan="2">Contrats qui expirent entre 3 à 6 mois</th>';
		$liste_expire_6mois .= '<th>&nbsp;</th>';
		$liste_expire_6mois .= '<th class="right"><a href="'.DOL_URL_ROOT.'/paiementsalaire/contrat/liste_complete.php?leftmenu=contrat&nb_mois=6">'.$langs->trans("FullList").'</th>';
		$liste_expire_6mois .= '</tr>'."\n";
		$num = $db->num_rows($res_contrat);
		if($num > 15)
			$num = 15;
		$i = 0;
		while($i <$num){
			$obj_mixte = $db->fetch_object($res_contrat);				

			$liste_expire_6mois .= '<tr class="oddeven">';
			// Numéro contrat
			$liste_expire_6mois .= '<td class="nowrap tdoverflowmax200">';
			$liste_expire_6mois .= "<a href='../onglets/contrat_information.php?mainmenu=paiementsalaire&leftmenu=contrat&id_societe=".$obj_mixte->id_societe."&fk_salarie=".$obj_mixte->id_salarie."&id_convention=".$obj_mixte->conv."&id=".$obj_mixte->id_user."&id_contrat=".$obj_mixte->rowid."'>".($obj_mixte->numero?:"N/A")."</a>";
			$liste_expire_6mois .= "</td>\n";
			// Type
			$sql_type_contrat = "SELECT libelle FROM ".MAIN_DB_PREFIX."type_contrat WHERE rowid=".$obj_mixte->fk_type_contrat;
			$restype_contrat = $db->query($sql_type_contrat);
			if($restype_contrat)
				$obj_type_contrat = $db->fetch_object($res_type_contrat);

			$liste_expire_6mois .= '<td class="center">';
			$liste_expire_6mois .= $obj_type_contrat->libelle;
			$liste_expire_6mois .= '</td>';
			// Last modified date
			$liste_expire_6mois .= '<td class="right tddate">';
			$liste_expire_6mois .= $obj_mixte->date_fin;
			$liste_expire_6mois .= "</td>";
			$liste_expire_6mois .= '<td class="right nowrap">';
			$liste_expire_6mois .= $actl[$obj_mixte->active];
			$liste_expire_6mois .= "</td>";

			$liste_expire_6mois .= "</tr>\n";
			$i++;
		}

		$db->free($res_contrat);

		$liste_expire_6mois .= "</table>\n";
		$liste_expire_6mois .= '</div>';
		$liste_expire_6mois .= "<!-- End last thirdparties modified -->\n";
	}





print '<div class="clearboth"></div>';
print '<div class="fichecenter fichecenterbis">';

$boxlist = '<div class="twocolumns">';

$boxlist .= '<div class="firstcolumn fichehalfleft boxhalfleft" id="boxhalfleft">';
$boxlist .=$typ_contrat_graph;
$boxlist .= '</div>'."\n";


$boxlist .= '<div class="secondcolumn fichehalfright boxhalfright" id="boxhalfright">';
$boxlist .=$liste_expire_3mois;
$boxlist .= '</div>'."\n";

$boxlist .= '</div>';


$boxlist.= '</div>';

print $boxlist;



print '<div class="clearboth"></div>';
print '<div class="fichecenter fichecenterbis">';

$boxlist = '<div class="twocolumns">';

$boxlist .= '<div class="firstcolumn fichehalfleft boxhalfleft" id="boxhalfleft">';
$boxlist .=$dure_contrat_graph;
$boxlist .= '</div>'."\n";


$boxlist .= '<div class="secondcolumn fichehalfright boxhalfright" id="boxhalfright">';
$boxlist .=$liste_expire_6mois;
$boxlist .= '</div>'."\n";

$boxlist .= '</div>';


$boxlist.= '</div>';

print $boxlist;
$db->close();