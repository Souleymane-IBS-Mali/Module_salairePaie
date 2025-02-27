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
print load_fiche_titre($langs->trans("Sociétés"), '', '');

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
$dataseries_cotis = array();
$dataseries_taxe = array();

$num_soc_paiementsalaire = 0;
$num_autre_soc = 0;
$tab_id = "(0";
	$sql_societe = "SELECT sc.rowid as r1, sc.nom, sc.name_alias, sc.phone, sc.fax, sc.code_client, sc.zip, sce.rowid as r2, sce.fk_object, sce.conv FROM ".MAIN_DB_PREFIX."societe as sc";
	$sql_societe .= " LEFT JOIN ".MAIN_DB_PREFIX."societe_extrafields as sce ON sc.rowid=sce.fk_object";
	if($user->id != 1)
    $sql_societe .= " WHERE sc.rowid IN ".$array_id_soc." AND sce.grp=1";
else   
    $sql_societe .= " WHERE sce.grp=1";

	$res_societe = $db->query($sql_societe);
	if($res_societe){
		$num_soc_paiementsalaire = $db->num_rows($res_societe);
		$a = 0;
		while ($a < $num_soc_paiementsalaire) {
			$tab_id .= ", ".$db->fetch_object($res_societe)->r1;
			$a ++;
		}
	}

	/*$tab_id .= ")";
	$sql_societe = "SELECT rowid , nom, name_alias, phone, fax, code_client, zip FROM ".MAIN_DB_PREFIX."societe WHERE rowid NOT IN ".$tab_id;
	//$sql_societe .= " LEFT JOIN ".MAIN_DB_PREFIX."societe_extrafields as sce ON sc.rowid=sce.fk_object WHERE sce.grp !=1";
	$res_societe = $db->query($sql_societe);
	if($res_societe){
		$num_autre_soc = $db->num_rows($res_societe);
	}*/
	$dataseries[] = array("Societe gérée par Salaire|Paie", $num_soc_paiementsalaire);
	//$dataseries[] = array("autres", $num_autre_soc);

	$total = $num_soc_paiementsalaire; //+ $num_autre_soc;

$salarie_societe_graph = '<div class="div-table-responsive-no-min">';
	$salarie_societe_graph .= '<table class="noborder nohover centpercent">'."\n";
	$salarie_societe_graph .= '<tr class="liste_titre"><th colspan="2">'.$langs->trans("Nombre de société").'</th></tr>';
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

$db->free($res_societe);

$sql_last_modif = "SELECT sc.rowid, sc.nom, sc.name_alias, sc.phone, sc.tms, sce.fk_object, sce.conv FROM ".MAIN_DB_PREFIX."societe as sc";
	$sql_last_modif .= " LEFT JOIN ".MAIN_DB_PREFIX."societe_extrafields as sce ON sc.rowid=sce.fk_object";
	if($user->id != 1)
    	$sql_last_modif .= " WHERE sc.rowid IN ".$array_id_soc." AND sce.grp=1 ORDER BY sc.tms DESC";
	else   
    	$sql_last_modif .= " WHERE sce.grp=1 ORDER BY sc.tms DESC";


	$res_last_modif = $db->query($sql_last_modif);
	if($res_last_modif){
		$last_modif = "\n<!-- last thirdparties modified -->\n";
		$last_modif .= '<div class="div-table-responsive-no-min">';
		$last_modif .= '<table class="noborder centpercent">';

		$last_modif .= '<tr class="liste_titre"><th colspan="2">Liste des 15 dernières sociétés modifiées</th>';
		$last_modif .= '<th>&nbsp;</th>';
		$last_modif .= '<th class="right"><a href="../liste_societe.php?mainmenu=paiementsalaire&leftmenu=societe">'.$langs->trans("FullList").'</th>';
		$last_modif .= '</tr>'."\n";
		$num = $db->num_rows($res_last_modif);
		if($num > 15)
			$num = 15;
		$i = 0;
		while($i <$num){
			$obj_mixte = $db->fetch_object($res_last_modif);

			$last_modif .= '<tr class="oddeven">';
			$last_modif .= '<td class="nowrap tdoverflowmax200">';
			$last_modif .= "<a href='./liste_personnelle.php?mainmenu=paiementsalaire&leftmenusociete&id_convention=".$obj_mixte->conv."&id_societe=".$obj_mixte->rowid."'>".($obj_mixte->nom?:"N/A")."</a>";
			$last_modif .= "</td>\n";

			$last_modif .= '<td class="center">';
			$last_modif .= "<a href='../../societe/card.php?socid=".$obj_mixte->rowid."'>".($obj_mixte->name_alias?:"N/A")."</a>";
			$last_modif .= '</td>';
			// Last modified date
			$last_modif .= '<td class="right tddate">';
			$sql_conv = 'SELECT nom FROM '.MAIN_DB_PREFIX."convention WHERE rowid=".$obj_mixte->conv;
			$res_con = $db->query($sql_conv);
			$obj_conv = $db->fetch_object($res_con);

			$last_modif .= "<a href='../convention/onglets/convention_information.php?mainmenu=paiementsalaire&leftmenu=convention&id_convention=".$obj_mixte->conv."'>".($obj_conv->nom?:"N/A")."</a>";
			$last_modif .= "</td>";
			$last_modif .= '<td class="right nowrap">';
			$last_modif .= $obj_mixte->tms;
			$last_modif .= "</td>";

			$last_modif .= "</tr>\n";
			$i++;
		}

		$last_modif .= "</table>\n";
		$last_modif .= '</div>';
		$last_modif .= "<!-- End last thirdparties modified -->\n";
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
$db->free($res_last_modif);
/*
$mois_tab = array(" Janvier "," Février "," Mars "," Avril "," Mai "," Juin "," Juillet "," Août "," Septembre "," Octobre "," Novembre "," Décembre ");
$array_grap = array();
print "<div>";
for ($i=0; $i < count($mois_tab); $i++) {
	$sql_societe = "SELECT sc.rowid, sc.nom, sce.rowid as r2, sce.fk_object FROM ".MAIN_DB_PREFIX."societe as sc";
	$sql_societe .= " LEFT JOIN ".MAIN_DB_PREFIX."societe_extrafields as sce ON sc.rowid=sce.fk_object WHERE sce.grp=1";
	$res_societe = $db->query($sql_societe);
	if($res_societe){
		$s = 0;
		$num_soc = $db->num_rows($res_societe);
		while($s < $num_soc){
			$obj_soc = $db->fetch_object($res_societe);

				$sql_id_bulletin = "SELECT rowid FROM ".MAIN_DB_PREFIX."bulletin WHERE annee=".date('Y')." AND mois=".($i + 1)." AND fk_societe=".$obj_soc->rowid;
				$res_id_bulletin  = $db->query($sql_id_bulletin);
				if($res_id_bulletin){
					$num_k = $db->num_rows($res_id_bulletin);
					$total = 0;
					$a = 0;
					$somme_taxe = 0;
					$somme_cotisation = 0;
					$somme_cotisation_employe = 0;
					$somme_cotisation_employeur = 0;
					while ($a < $num_k){
						$obj_id_bulletin = $db->fetch_object($res_id_bulletin);
						$sql_som_taxe = "SELECT SUM(montant) FROM ".MAIN_DB_PREFIX."bulletin_taxe WHERE fk_bulletin=".$obj_id_bulletin->rowid;
						$res_som_taxe  = $db->query($sql_som_taxe);
						if($res_som_taxe){
							$obj_som_taxe = $db->fetch_object($res_som_taxe);
							$somme_taxe += $obj_som_taxe->montant;
						}

						$sql_som_cotisation = "SELECT SUM(montant_employe) as som_empl, SUM(montant_employeur) as som_patro FROM ".MAIN_DB_PREFIX."bulletin_cotisation WHERE fk_bulletin=".$obj_id_bulletin->rowid;
						$res_som_cotisation  = $db->query($sql_som_cotisation);
						if($res_som_cotisation){
							$obj_som_cotisation = $db->fetch_object($res_som_cotisation);
							$somme_cotisation_employe += $obj_som_cotisation->som_empl;
							$somme_cotisation_employeur += $obj_som_cotisation->som_patro;
						}
						$a ++;
					}
					$db->free($res_id_bulletin);
					$somme_cotisation += $somme_cotisation_employe + $somme_cotisation_employeur;
					$total += $somme_taxe + $somme_cotisation;
					$dataseries_cotis[] = array($obj_soc->nom, $somme_cotisation);
					$dataseries_taxe[] = array($obj_soc->nom, $taxe);

					$cotisation_societe_graph = '<div class="div-table-responsive-no-min">';
					$cotisation_societe_graph .= '<table class="noborder nohover centpercent">'."\n";
					$cotisation_societe_graph .= '<tr class="liste_titre"><th colspan="2">Cotisations</th></tr>';
					$dolgraph = new DolGraph();
					$dolgraph->SetData($dataseries_cotis);
					$dolgraph->setShowLegend(2);
					$dolgraph->setShowPercent(1);
					$dolgraph->SetType(array('pie'));
					$dolgraph->setHeight('200');
					$dolgraph->draw("cotisation".$s);
					$array_grap[] = "cotisation".$s;
					$cotisation_societe_graph .= '<tr><td>'.$dolgraph->show();
					$cotisation_societe_graph .= '</td></tr>';
					$cotisation_societe_graph .= '<tr class="liste_total"><td>Nombre total de société</td><td class="right">';
					$cotisation_societe_graph .= $num_soc;
					$cotisation_societe_graph .= '</td></tr>';
					$cotisation_societe_graph .= '</table>';
					$cotisation_societe_graph .= '</div>';
					print $cotisation_societe_graph;

					$taxe_societe_graph = '<div class="div-table-responsive-no-min">';
					$taxe_societe_graph .= '<table class="noborder nohover centpercent">'."\n";
					$taxe_societe_graph .= '<tr class="liste_titre"><th colspan="2">Taxes</th></tr>';
					$dolgraph = new DolGraph();
					$dolgraph->SetData($dataseries_taxe);
					$dolgraph->setShowLegend(2);
					$dolgraph->setShowPercent(1);
					$dolgraph->SetType(array('pie'));
					$dolgraph->setHeight('200');
					$dolgraph->draw("taxe".$s);
					$array_grap[] = "taxe".$s;
					$taxe_societe_graph .= '<tr><td>'.$dolgraph->show();
					$taxe_societe_graph .= '</td></tr>';
					$taxe_societe_graph .= '<tr class="liste_total"><td>Nombre total de société</td><td class="right">';
					$taxe_societe_graph .= $num_soc;
					$taxe_societe_graph .= '</td></tr>';
					$taxe_societe_graph .= '</table>';
					$taxe_societe_graph .= '</div>';
					print $taxe_societe_graph;


			}
			$s ++;
		}
}
}
print '</div>';
*/
