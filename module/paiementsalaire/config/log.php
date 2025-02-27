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
print load_fiche_titre($langs->trans("Liste des importantes actions effectuées"), '', '');

// Recuperation des information après le clique sur l'onglet Salaire au niveau du module user
$action = GETPOST("action");


$salaire_base = 0;
$message = "";
$annee = date("Y");
$mois = (int)date("m");

$limit = GETPOST('limit', 'int')?:20;
$arret = GETPOST('arret', 'int')?:0;
$nb_page = GETPOST('nbpage', 'int')?:1;
$obj_liste = array();

			$nom_prenom = "";
			$object_concerne = "";

			if($action = "recherche"){
				$nom_prenom = GETPOST("nom_prenom");
				$object_concerne = GETPOST("object_concerne");
				$date_action = GETPOST("date_action");

			}
			//les contrats qui finissent dans 6 mois ue.egp=".$id_societe."
		$sql = 'SELECT * FROM '.MAIN_DB_PREFIX.'log';
		$sql .= ' WHERE 1=1';

		if(!empty(GETPOST("nom_prenom"))){
			$tab = explode('_', GETPOST("nom_prenom"));
			$sql .= " AND (nom LIKE '%".$tab[0]."%'";
			$sql .= " OR prenom LIKE '%".$tab[1]."%')";
		}


		if(!empty(GETPOST("object_concerne"))){
			$sql .= " AND object_concerne LIKE '%".GETPOST("object_concerne")."%'";
		}

		if(!empty(GETPOST("date_action"))){
			$sql .= " AND quand LIKE '%".$date_action."%'";
		}

		$sql .= ' ORDER BY rowid DESC';

			$res_contrat = $db->query($sql);
			if($res_contrat){
				$num = $db->num_rows($res_contrat);
				$i = 0;
				while($i < $num){
					$obj_liste[] = $db->fetch_object($res_contrat);
					$i ++;
				}

			}

	//print_barre_liste("", $page, $_SERVER["PHP_SELF"], "", "", "", "", "", "", 'bill', 0, dolGetButtonTitle("Ajouter un nouveau contrat", '', 'fa fa-plus-circle', $_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=salarie&id='.$fk_user.'&fk_salarie='.$fk_salarie.'&action=ajouter' , '', 1), '', 0, 0, 0, 1);
	//Partie affichage du Contrat ------------------------------------------------------------------------------------------------------------------------------------------
			$acts[0] = "activate";
			$acts[1] = "disable";
			$actl[0] = img_picto($langs->trans("Disabled"), 'switch_off', 'class="size15x"');
			$actl[1] = img_picto($langs->trans("Activated"), 'switch_on', 'class="size15x"');

		//les contrats expirés
		print "<hr><div>";
		$num = count($obj_liste) == 0 ? 1 : count($obj_liste);
		$sel5 = "selected";
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
			print '<form name="ajouter" method="POST" action="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=salarie">';
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
					if($limit == 'tout')
						$limit = $num;

					print "<mark><b>".(GETPOST("nbpage")?:1)."</b></mark>/<mark><b>".(((int)($num%$limit))==0?((int)($num/$limit)):((int)($num/$limit)+1))."</b></mark>";
					print '<script type="text/javascript">
					var convention = document.getElementById("limit");
					convention.addEventListener("change", function () {
						var limit = convention.value;
						window.location.href = "'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=configuration&id_societe='.$id_societe.'&id_convention='.$id_convention.'&limit="+limit+"&action=rechercher&nom_prenom='.$nom_prenom.'&object_concerne='.$object_concerne.'&date_action='.$date_action.'";
					},
					false,
					);
					</script>';
				print "</select></form>";
				print "</div>";

		print "<table class='tagtable liste'>";
		print '<form name="ajouter" method="POST" action="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=configuration">';
		print '<input type="hidden" name="token" value="'.newToken().'">';
		print '<input type="hidden" name="action" value="recherche">';
		//type contrat
		print "<tr class='liste_titre'>";

		print "<td >Utilisateurs<br><select name='nom_prenom'>";
		print "<option value=0></option>";

		$sql = 'SELECT DISTINCT nom, prenom FROM '.MAIN_DB_PREFIX.'log';
		if(!empty($object_concerne)){
			$sql .= ' WHERE object_concerne LIKE "%'.$object_concerne.'%"';
		}

		$result = $db->query($sql);

		if($result){
			$i = 0;
			$num = $db->num_rows($result);
			while ($i < $num){
				$users = $db->fetch_object($result);
				$value = $users->nom."_".$users->prenom;
				if($nom_prenom == ($users->nom."_".$users->prenom))
					print "<option value='".$value."' selected>".$users->nom." ".$users->prenom."</option>";
				else
					print "<option value='".$value."'>".$users->nom." ".$users->prenom."</option>";
				$i ++;
			}

		}

	print "</select></td>";
	print "<td >Action<br><select name='object_concerne'>";
	print "<option value=0></option>";

	$sql = "SELECT DISTINCT object_concerne FROM ".MAIN_DB_PREFIX."log";
	if(!empty(GETPOST("nom_prenom"))){
		$tab = explode('_', GETPOST("nom_prenom"));
		$sql .= " WHERE (nom LIKE '%".$tab[0]."%'";
		$sql .= " OR prenom LIKE '%".$tab[1]."%')";
	}

	$result = $db->query($sql);
	if($result){
		$i = 0;
		$num = $db->num_rows($result);
		while ($i < $num){
			$liste_log = $db->fetch_object($result);
			if($object_concerne == $liste_log->object_concerne)
				print "<option value=".$liste_log->object_concerne." selected>".$liste_log->object_concerne."</option>";
			else
				print "<option value=".$liste_log->object_concerne.">".$liste_log->object_concerne."</option>";
			$i ++;
		}

	}

print "</select></td>";

	print '<td>Description</td>';

	print "<td >Date<br>";
	print "<input type='date' name='date_action' value=".$date_action.">";
	print "</td>";
		print "<td ><input type='submit' class='button' value='Rechercher' ><br>";

		print "</form>";
		print '<a class="button"href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=contrat" >Annuler</a>';
		print "</td></td></tr>";
		$num = count($obj_liste);
		$i = $arret;
		while ($i < $num){

			print '<tr class="impair"><td>'.$obj_liste[$i]->nom ." ".$obj_liste[$i]->prenom.'</td>';
			print '<td>'.$obj_liste[$i]->object_concerne.'</td>';
			print affiche_long_texte("", $obj_liste[$i]->action_effectue, 1, '', '', '','', '', '');
			print '<td>'.$obj_liste[$i]->quand.'</td><td></td></tr>';

			if($i!= 0 && (($i+1)%$limit) == 0){
				$arret = $i;
				$i = $num;
			}else
			$i ++;

		}
		if(count($obj_liste) ==0){
			print "<tr><td colspan='6' align='center'><style='align:center;'>Aucun salarié</td></tr>";
		}
	print '</table>';
	 print '</div><br><br>';
	print '<div>';

		print '<span style="float:right; margin-left: 20px;">';
		$nb = (((int)($num%$limit))==0?((int)($num/$limit)):((int)($num/$limit)+1));
$page_link = "";
if($num>$limit){
	if($nb_page!= 1)
		if($nb==0 && 1 < ($nb))
			$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenu=configuration&limit=".$limit."&arret=0&nbpage=1&action=recherche&nom_prenom=".$nom_prenom."&object_concerne=".$object_concerne."&date_action=".$date_action."' style='padding: 5px'><b>Debut</b>    </a>&nbsp;&nbsp;";
		else if(1 < ($nb+1))
		$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenu=configuration&limit=".$limit."&arret=0&nbpage=1&action=recherche&nom_prenom=".$nom_prenom."&object_concerne=".$object_concerne."&date_action=".$date_action."' style='padding: 5px'><b>Debut</b>    </a>&nbsp;&nbsp;";


	if($arret > $limit){


		if($nb_page-3>=0)
			$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenu=configuration&limit=".$limit."&arret=".($limit*($nb_page-3))."&nbpage=".($nb_page-2)."&action=recherche&nom_prenom=".$nom_prenom."&object_concerne=".$object_concerne."&date_action=".$date_action."' style='padding: 5px'><b>".($nb_page -2)."</b></a>&nbsp;&nbsp;";

		if($nb_page-2>=0)
					$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenu=configuration&limit=".$limit."&arret=".($limit*($nb_page-2))."&nbpage=".($nb_page-1)."&action=recherche&nom_prenom=".$nom_prenom."&object_concerne=".$object_concerne."&date_action=".$date_action."' style='padding: 5px'><b>".($nb_page-1)."</b></a>&nbsp;&nbsp;";


		if($nb_page-1>=0)
				$page_link .= "<a style='background-color: yellow;' href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenu=configuration&limit=".$limit."&arret=".($limit*($nb_page-1))."&nbpage=".($nb_page)."&action=recherche&nom_prenom=".$nom_prenom."&object_concerne=".$object_concerne."&date_action=".$date_action."' style='padding: 5px'><b>".($nb_page)."</b></a>&nbsp;&nbsp;";




			if(	(($nb_page+1) <= ($nb)))
				$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenu=configuration&limit=".$limit."&arret=".($limit*$nb_page)."&nbpage=".($nb_page+1)."&action=recherche&nom_prenom=".$nom_prenom."&object_concerne=".$object_concerne."&date_action=".$date_action."' style='padding: 5px'><b>".($nb_page + 1)."</b></a>&nbsp;&nbsp;";


			if((($nb_page+2) <= ($nb)))
				$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenu=configuration&limit=".$limit."&arret=".($limit*($nb_page +1))."&nbpage=".($nb_page+2)."&action=recherche&nom_prenom=".$nom_prenom."&object_concerne=".$object_concerne."&date_action=".$date_action."' style='padding: 5px'><b>".($nb_page + 2)."</b></a>&nbsp;&nbsp;";


			if((($nb_page+3) <= ($nb)))
				$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenu=configuration&limit=".$limit."&arret=".($limit*($nb_page+2))."&nbpage=".($nb_page+3)."&action=recherche&nom_prenom=".$nom_prenom."&object_concerne=".$object_concerne."&date_action=".$date_action."' style='padding: 5px'><b>".($nb_page + 3)."</b></a>&nbsp;&nbsp;";




	}else{


			if( 1 <= ($nb))

				$page_link .= "<a style='background-color: yellow;' href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenu=configuration&limit=".$limit."&arret=0&nbpage=1&action=recherche&nom_prenom=".$nom_prenom."&object_concerne=".$object_concerne."&date_action=".$date_action."' style='padding: 5px'><b>1</b></a>&nbsp;&nbsp;";


			if(2 <= ($nb))

				$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenu=configuration&limit=".$limit."&arret=".$limit."&nbpage=2&action=recherche&nom_prenom=".$nom_prenom."&object_concerne=".$object_concerne."&date_action=".$date_action."' style='padding: 5px'><b>2</b></a>&nbsp;&nbsp;";


			if(3 <= ($nb))

				$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenu=configuration&limit=".$limit."&arret=".($limit*2)."&nbpage=3&action=recherche&nom_prenom=".$nom_prenom."&object_concerne=".$object_concerne."&date_action=".$date_action."' style='padding: 5px'><b>3</b></a>&nbsp;&nbsp;";

			if(4 <= ($nb))

				$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenu=configuration&limit=".$limit."&arret=".($limit*3)."&nbpage=4&action=recherche&nom_prenom=".$nom_prenom."&object_concerne=".$object_concerne."&date_action=".$date_action."' style='padding: 5px'><b>4</b></a>&nbsp;&nbsp;";

			if(5 <= ($nb))

				$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenu=configuration&limit=".$limit."&arret=".($limit*4)."&nbpage=5&action=recherche&nom_prenom=".$nom_prenom."&object_concerne=".$object_concerne."&date_action=".$date_action."' style='padding: 5px'><b>5</b></a>&nbsp;&nbsp;";



	}
	if($nb_page != ($nb)  )
			$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenu=configuration&limit=".$limit."&arret=".($limit*($nb-1))."&nbpage=".($nb)."&action=recherche&nom_prenom=".$nom_prenom."&object_concerne=".$object_concerne."&date_action=".$date_action."' style='padding: 5px'>      <b>Fin</b></a>&nbsp;&nbsp;";
}
			print $page_link.'</span>';
