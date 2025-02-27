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
//require_once DOL_DOCUMENT_ROOT.'/core/class/html.formpaiementsalaire.class.php';
//require_once DOL_DOCUMENT_ROOT.'/custom/paiementsalaire/core/modules/modPaiementSalaire.class.php';

//$PaiementSalaire = new modPaiementSalaire($db);


llxHeader("", "Paiement | Salaire");



$id_societe = GETPOST('id_societe','int');
$id_convention = GETPOST('id_convention','int');
$limit = GETPOST('limit','alpha')?:20;
$arret = GETPOST('arret','int')?:0;
$nb_page = GETPOST('nbpage','int')?:1;
$action = GETPOST('action', 'alpha');

$action = GETPOST('action','alpha');
if(empty($action))
	$action = 'listeSociete';

	$obj_liste = array();

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

	$sql = "SELECT sc.rowid as r1, sc.nom, sc.name_alias, sc.phone, sc.fax, sc.code_client, sc.zip, sce.rowid as r2, sce.fk_object, sce.conv, c.rowid as id_conv, c.nom as nom_conv FROM ".MAIN_DB_PREFIX."societe as sc";
	$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."societe_extrafields as sce ON sc.rowid=sce.fk_object";
	$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."convention as c ON sce.conv=c.rowid";

	if($user->id != 1)
        $sql .= " WHERE sc.rowid IN ".$array_id_soc." AND sce.grp=1";
    else   
        $sql .= " WHERE sce.grp=1";
	$sql .= " ORDER BY sc.rowid ASC";
	$result = $db->query($sql);

	if($result){
		$i = 0;
		$num = $db->num_rows($result);
		while ($i < $num){
			$obj_liste[$i] = $db->fetch_object($result);
			$i ++;
		}
	}
	print load_fiche_titre($langs->trans("Liste des sociétés(".count($obj_liste).")"), '', '');

	print '<hr>';

	$num = count($obj_liste) == 0 ? 1 : count($obj_liste);
		$sel10 = "";
		$sel25 = "";
		$sel20 = "";
		$sel30 = "";
		$sel50 = "";
		$sel100 = "";
		$sel200 = "";
		$sel500 = "";
		$sel1000 = "";
		$seltout = "";
		if($limit == 5)
			$sel5 = "selected";
		elseif($limit == 10)
			$sel10 = "selected";
		elseif($limit == 15)
			$sel15 = "selected";
		elseif($limit == 20)
			$sel20 = "selected";
		elseif($limit == 30)
			$sel30 = "selected";
		elseif($limit == 50)
			$sel50 = "selected";
		elseif($limit == 100) 
			$sel100 = "selected";
		elseif($limit == 200)
			$sel200 = "selected";
		elseif($limit == 500)
			$sel500 = "selected";
		elseif($limit == 1000)
			$sel1000 = "selected";
		else $seltout = "selected";
		print "<div style='float:right; margin-right:20px;'>";
		print '<form name="ajouter" method="POST" action="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=salarie&id_convention='.$id_convention.'&id_societe='.$id_societe.'">';
		print '<input type="hidden" name="token" value="'.newToken().'">';
		print '<input type="hidden" name="action" value="recherche">';
		print"<select style='padding:10px' name='limit' id='limit' >";
		print "<option value='5' ".$sel5." ><b>5</b></option>
		<option value='10' ".$sel10."><b>10</b></option>
		<option value='15' ".$sel15."><b>15</b></option>
		<option value='20' ".$sel20."><b>20</b></option>
		<option value='30' ".$sel30."><b>30</b></option>
		<option value='50' ".$sel50."><b>50</b></option>
		<option value='100' ".$sel100."><b>100</b></option>
		<option value='200' ".$sel200."><b>200</b></option>
		<option value='500' ".$sel500."><b>500</b></option>
		<option value='1000' ".$sel1000."><b>1000</b></option>
		<option value='tout' ".$seltout."><b>tout</b></option>";
		
		print "</select>";

		print "</form>";

		if($limit == 'tout')
			$limit = $num;
		
		print "<mark><b>".(GETPOST("nbpage","int")?:1)."</b></mark>/<mark><b>".(((int)($num%$limit))==0?((int)($num/$limit)):((int)($num/$limit)+1))."</b></mark>";
				print '<script type="text/javascript">
				var convention = document.getElementById("limit");
				convention.addEventListener("change", function () {
					var limit = convention.value;
					window.location.href = "'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=societe&limit="+limit+"&action=rechercher&recherche_nom='.$recherche_nom.'&recherche_prenom='.$recherche_prenom.'&recherche_matricule='.$recherche_matricule.'&recherche_anciennete='.$recherche_anciennete.'";
				},
				false,
				);
				</script>';
			
			print "</select>";
			print '<div style="display: inline-block;>';
				print_barre_liste("", $page, $_SERVER["PHP_SELF"], "", "", "", "", "", "", 'bill', 0, dolGetButtonTitle("Créer une nouvelle Société", '', 'fa fa-plus-circle', '../societe/card.php?action=create&leftmenu=societe' , '', 1), '', 0, 0, 0, 1);
			print '</div>';
			print "</div>";


	print '<table class="tagtable liste">';
	print '<tr class="liste_titre">';
	print '<td style="padding: 20px; width: 150px;">Nom du Tiers</td>';
	print '<td style="padding: 20px; width: 150px;">Nom alternatif</td>';
	print '<td style="padding: 20px; width: 150px;">Code Client</td>';
	print '<td style="padding: 20px; width: 150px;">Code Postal</td>';
	print '<td style="padding: 20px; width: 150px;">Téléphone</td>';
	print '<td style="padding: 20px; width: 150px;">Gérer la paie</td>';
	print '<td style="padding: 20px; width: 150px;">Convention</td>';
	print '<td style="padding: 20px; width: 150px;">Opération</td>';


	print '</tr>';

	$i = $arret;
	while ($i < $num){

		print '<tr  class="pair">';
		//print '<td style="padding: 0px; width: 50px;"><a href="./onglets/liste_personnelle.php?mainmenu=paiementsalaire&leftmenu=societe&id_societe='.$societe->fk_object.'">'.($i+1).'</a></td>';

		print '<td style="padding: 15px; width: 100px;" align="center"><a href="./onglets/liste_personnelle.php?mainmenu=paiementsalaire&leftmenu=societe&id_convention='.$obj_liste[$i]->id_conv.'&id_societe='.$obj_liste[$i]->r1.'">'.dol_escape_htmltag($obj_liste[$i]->nom).'</a></td>';
		print '<td style="padding: 15px; width: 150px;" align="center"><a href="./onglets/liste_personnelle.php?mainmenu=paiementsalaire&leftmenu=societe&id_convention='.$obj_liste[$i]->id_conv.'&id_societe='.$obj_liste[$i]->r1.'">'.dol_escape_htmltag($obj_liste[$i]->name_alias).'</a></td>';
		print '<td style="padding: 15px; width: 150px;" align="center">'.$obj_liste[$i]->code_client.'</td>';
		print '<td style="padding: 15px; width: 150px;" align="center">'.$obj_liste[$i]->zip.'</td>';
		print '<td style="padding: 15px; width: 150px;" align="center">'.($obj_liste[$i]->phone?$obj_liste[$i]->phone:$obj_liste[$i]->fax).'</td>';
		print '<td style="padding: 15px; width: 150px;" align="center">Oui</td>';

		print '<td style="padding: 15px; width: 150px;" align="center">'.$obj_liste[$i]->nom_conv.'</td>';
		print '<td style="padding: 15px; width: 150px;" align="center"><a title="Voir détails dans Tier" target="_blank" href="../societe/card.php?socid='.$obj_liste[$i]->r1.'&save_lastsearch_values=1"><button class="button">Détails</button></a></td>';

		print '</tr>';
		if($i!= 0 && (($i+1)%$limit) == 0){
			$arret = $i;
			$i = $num;
		}else
			$i ++;
	}


print '</table>';



print '<span style="float:right; margin-left: 20px;">';
			$nb = (((int)($num%$limit))==0?((int)($num/$limit)):((int)($num/$limit)+1));
	$page_link = "";
	if($num>$limit){

		if($nb_page!= 1)
			if($nb==0 && 1 < ($nb))
				$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenu=societe&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=0&nbpage=1&action=recherche&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_matricule=".$recherche_matricule."&recherche_anciennete=".$recherche_anciennete."' style='padding: 5px'><b>Debut</b>    </a>&nbsp;&nbsp;";
			else if(1 < ($nb+1))
			$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenu=societe&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=0&nbpage=1&action=recherche&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_matricule=".$recherche_matricule."&recherche_anciennete=".$recherche_anciennete."' style='padding: 5px'><b>Debut</b>    </a>&nbsp;&nbsp;";

		
		if($arret > $limit){

			
			if($nb_page-3>=0)
				$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenu=societe&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=".($limit*($nb_page-3))."&nbpage=".($nb_page-2)."&action=recherche&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_matricule=".$recherche_matricule."&recherche_anciennete=".$recherche_anciennete."&tri=".$tri."' style='padding: 5px'><b>".($nb_page -2)."</b></a>&nbsp;&nbsp;";

			if($nb_page-2>=0)
						$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenu=societe&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=".($limit*($nb_page-2))."&nbpage=".($nb_page-1)."&action=recherche&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_matricule=".$recherche_matricule."&recherche_anciennete=".$recherche_anciennete."&tri=".$tri."' style='padding: 5px'><b>".($nb_page-1)."</b></a>&nbsp;&nbsp;";
			
			
			if($nb_page-1>=0)
					$page_link .= "<a style='background-color: yellow;' href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenu=societe&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=".($limit*($nb_page-1))."&nbpage=".($nb_page)."&action=recherche&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_matricule=".$recherche_matricule."&recherche_anciennete=".$recherche_anciennete."&tri=".$tri."' style='padding: 5px'><b>".($nb_page)."</b></a>&nbsp;&nbsp;";

		

			
				if(	(($nb_page+1) <= ($nb)))
					$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenu=societe&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=".($limit*$nb_page)."&nbpage=".($nb_page+1)."&action=recherche&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_matricule=".$recherche_matricule."&recherche_anciennete=".$recherche_anciennete."&tri=".$tri."' style='padding: 5px'><b>".($nb_page + 1)."</b></a>&nbsp;&nbsp;";

			
				if((($nb_page+2) <= ($nb)))
					$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenu=societe&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=".($limit*($nb_page +1))."&nbpage=".($nb_page+2)."&action=recherche&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_matricule=".$recherche_matricule."&recherche_anciennete=".$recherche_anciennete."&tri=".$tri."' style='padding: 5px'><b>".($nb_page + 2)."</b></a>&nbsp;&nbsp;";
					
				
				if((($nb_page+3) <= ($nb)))
					$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenu=societe&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=".($limit*($nb_page+2))."&nbpage=".($nb_page+3)."&action=recherche&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_matricule=".$recherche_matricule."&recherche_anciennete=".$recherche_anciennete."&tri=".$tri."' style='padding: 5px'><b>".($nb_page + 3)."</b></a>&nbsp;&nbsp;";

					


		}else{

			
				if( 1 <= ($nb))
					
					$page_link .= "<a style='background-color: yellow;' href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenu=societe&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=0&nbpage=1&action=recherche&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_matricule=".$recherche_matricule."&recherche_anciennete=".$recherche_anciennete."&tri=".$tri."' style='padding: 5px'><b>1</b></a>&nbsp;&nbsp;";
			
			
				if(2 <= ($nb))
					
					$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenu=societe&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=".$limit."&nbpage=2&action=recherche&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_matricule=".$recherche_matricule."&recherche_anciennete=".$recherche_anciennete."&tri=".$tri."' style='padding: 5px'><b>2</b></a>&nbsp;&nbsp;";
			
			
				if(3 <= ($nb))
					
					$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenu=societe&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=".($limit*2)."&nbpage=3&action=recherche&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_matricule=".$recherche_matricule."&recherche_anciennete=".$recherche_anciennete."&tri=".$tri."' style='padding: 5px'><b>3</b></a>&nbsp;&nbsp;";
				
				if(4 <= ($nb))
					
					$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenu=societe&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=".($limit*3)."&nbpage=4&action=recherche&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_matricule=".$recherche_matricule."&recherche_anciennete=".$recherche_anciennete."&tri=".$tri."' style='padding: 5px'><b>4</b></a>&nbsp;&nbsp;";

				if(5 <= ($nb))
					
					$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenu=societe&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=".($limit*4)."&nbpage=5&action=recherche&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_matricule=".$recherche_matricule."&recherche_anciennete=".$recherche_anciennete."&tri=".$tri."' style='padding: 5px'><b>5</b></a>&nbsp;&nbsp;";



		}
		if($nb_page != ($nb)  )
				$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenu=societe&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=".($limit*($nb-1))."&nbpage=".($nb)."&action=recherche&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_matricule=".$recherche_matricule."&recherche_anciennete=".$recherche_anciennete."&tri=".$tri."' style='padding: 5px'>      <b>Fin</b></a>&nbsp;&nbsp;";

		
	}
			print $page_link.'</span>';