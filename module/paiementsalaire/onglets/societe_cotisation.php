<?php
require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/custom/paiementsalaire/lib/paiementsalaire.lib.php';


llxHeader("", "Paiement | Salaire");
//Titre 
//print load_fiche_titre($langs->trans("Les Cotisations de la societe"), '', '');
//print '<hr>';

$id_societe = GETPOST('id_societe','int');
$action =  GETPOST('action','alpha');
$id_convention = GETPOST('id_convention','int');

$message = "";
print load_fiche_titre($langs->trans("Les cotisations sociales des Compagnies"), '', '');

if(empty($action))
	$action = "annee_rechercher";
$head = paiementsalaireSocieteHead($id_societe, $id_convention);
print dol_get_fiche_head($head, 'cotisation', "", -1, '');

if(!empty($id_convention)){
$soc_sql = "SELECT * FROM ".MAIN_DB_PREFIX."societe WHERE rowid=".$id_societe;
$soc_res = $db->query($soc_sql);//= $db->query($covSql);
$obj_soc = $db->fetch_object($soc_res);
$obj_soc->name = $obj_soc->nom;
$obj_soc->element = "societe";			
$obj_soc->conv = $id_convention;

societe_preview_next($db, $id_societe, $obj_soc);
entete_societe($obj_soc, 'societe');

$mois_tab = array(" Janvier "," Février "," Mars "," Avril "," Mai "," Juin "," Juillet "," Août "," Septembre "," Octobre "," Novembre "," Décembre ");


if($action == 'annee_rechercher'){
	//Gestion des année et des dates pour l'historique => Gestion des actions recherche année
	$annee_rechercher = GETPOST("annee_rechercher", "int");
	$annee_courant = (int) date("Y");
	if(empty($annee_rechercher))
		$annee_rechercher = (int) date("Y");
	$mois_courant = (int) date("m");
	if($annee_rechercher != $annee_courant)
	print "<h2 style='align:center; display: inline'>Historique des cotisations de l'année ".$annee_rechercher."!</h2>";
else print "<h2 style='align:center;display: inline'>Cotisations de ".$annee_rechercher."!</h2>";
	print "<div style='float: right; display: inline''>";
	print '<form name="add" method="POST" action="'.$_SERVER['PHP_SELF'].'?mainmenu=paiementsalaire&leftmenu=salarie&id_societe='.$id_societe.'&id_convention='.$id_convention.'">';
	print '<input type="hidden" name="token" value="'.newToken().'">';
	print '<input type="hidden" name="action" value="annee_rechercher">';

	print "<select name='annee_rechercher'>";
				$sql_verif = "SELECT DISTINCT annee FROM ".MAIN_DB_PREFIX."bulletin WHERE fk_societe=".$id_societe." AND cloture='oui'";
	  			$res_verif = $db->query($sql_verif);
				if($res_verif){
					$num_all = $db->num_rows($res_verif);
					$i=0;
					$annee_tab = array();
					while ($i < $num_all) { 
						$obj_annee = $db->fetch_object($res_verif);
						$annee_tab[] = $obj_annee->annee;
						if($obj_annee->annee == $annee_rechercher)
							print "<option value='".($obj_annee->annee)."' selected >".($obj_annee->annee)."</option>";
						else print "<option value='".($obj_annee->annee)."'>".($obj_annee->annee)."</option>";

						
						$i ++;
					}
					if($num_all == 0){
						print "<option value='".date("Y")."' selected >".date("Y")."</option>";
					}elseif(!in_array(date("Y"), $annee_tab))
						if($annee_rechercher == $annee_courant)
							print "<option value='".date("Y")."' selected>".date("Y")."</option>";
						else print "<option value='".date("Y")."' >".date("Y")."</option>";


				}
				print "</select><input type='submit' value='Rechercher'class='button'></form>";

print "</div>";
$element_cotis = "";
$array_id_cotis = array();
$array_cotis_an = array();

$nb_cotisation = 1;
$sql_type_cotis = "SELECT rowid, code FROM ".MAIN_DB_PREFIX."type_prestation";
	$res_type_cotis = $db->query($sql_type_cotis);
	if($res_type_cotis){
		$nb_cotisation = $db->num_rows($res_type_cotis);
		$a = 0;
		while ($a < $nb_cotisation) {
			$obj_type_cotis = $db->fetch_object($res_type_cotis);
			$array_id_cotis[] = $obj_type_cotis->rowid;
			$array_cotis_an[] = 0;
			$element_cotis .= "<th>".$obj_type_cotis->code."</th>";
			$a ++;

		}
	}

	$sql = "SELECT COUNT(DISTINCT fk_salarie) AS nb_salaries FROM ".MAIN_DB_PREFIX."bulletin WHERE fk_societe = ".$id_societe." AND annee = ".$annee_rechercher;
		$res = $db->query($sql);
		if ($res) {
			$obj = $db->fetch_object($res);
			$nb_salaries_par_an = $obj->nb_salaries;
		}
print "<table class='tagtable liste'>";
	print "<thead>";
	print "<tr class='liste_titre'><th rowspan='2'>Mois</th>";
	print "<th rowspan='2'>salariés</th>";
	print "<th colspan=".$nb_cotisation." align='center'>Cotisations</th>";
	print "<th rowspan='2'>Opérations</tr>";

	print "<tr>";
	print $element_cotis;
	print "</tr></thead>";

		for ($i=0; $i < count($mois_tab); $i++) { 

			//Verifions s'il y a un bonus(compléments) pour ce mois
			$bonus = "";
			$sql_id_bulletin_bonus = "SELECT rowid FROM ".MAIN_DB_PREFIX."bulletin_bonus WHERE annee=".$annee_rechercher." AND mois=".($i + 1)." AND fk_societe=".$id_societe;
			$res_id_bulletin_bonus  = $db->query($sql_id_bulletin_bonus);
			$num_bonus = $db->num_rows($res_id_bulletin_bonus);
			if($num_bonus >0 )
				$bonus = info_admin("Un complément salaire est lié à ce mois", 1);


			print "<tr class='impair'>";
			$nb_j = (int) date("d");
				$sql_verif = "SELECT rowid, cloture FROM ".MAIN_DB_PREFIX."bulletin WHERE annee=".$annee_rechercher." AND mois=".($i + 1)." AND fk_societe=".$id_societe;
				  $res_verif = $db->query($sql_verif);
				$nb_salarie = $db->num_rows($res_verif);
				$obj_verif = $db->fetch_object($res_verif);

				if(!empty($obj_verif->cloture)){
					if($obj_verif->cloture == 'non')
						print "<td><b>".$mois_tab[$i]." ".info_admin('Veuillez cloturer ce mois pour Voir ou Télécharger',1)."</b></td>";
					else print "<td><b>".$mois_tab[$i]." ".$bonus."</b></td>";
					print "<td>".$nb_salarie."</td>";
							
					//Bulletin
							for ($j=0; $j < count($array_id_cotis); $j++) {
								$sql_id_bulletin = "SELECT rowid FROM ".MAIN_DB_PREFIX."bulletin WHERE annee=".$annee_rechercher." AND mois=".($i + 1)." AND fk_societe=".$id_societe;
								$res_id_bulletin  = $db->query($sql_id_bulletin);
								$num_k = $db->num_rows($res_id_bulletin);
								$k = 0;
								$somme_cotisation = 0;
								while ($k < $num_k){
									$obj_id_bulletin = $db->fetch_object($res_id_bulletin);
									$sql_som_cotisation = "SELECT SUM(montant_employe) as montant_employe, SUM(montant_employeur) as montant_employeur FROM ".MAIN_DB_PREFIX."bulletin_cotisation WHERE fk_cotisation=".$array_id_cotis[$j]." AND fk_bulletin=".$obj_id_bulletin->rowid;
									$res_som_cotisation  = $db->query($sql_som_cotisation);
									if($res_som_cotisation){
										$obj_som_cotisation = $db->fetch_object($res_som_cotisation);
										$somme_cotisation += $obj_som_cotisation->montant_employe + $obj_som_cotisation->montant_employeur;
										$a ++;
										
									}
									$k ++;
								}

								//Bulletin bonus complement salaire
									$k = 0;
									while ($k < $num_bonus){
										$obj_id_bulletin_bonus = $db->fetch_object($res_id_bulletin_bonus);
										$sql_som_cotisation_bonus = "SELECT SUM(montant_employe) as montant_employe, SUM(montant_employeur) as montant_employeur FROM ".MAIN_DB_PREFIX."bulletin_cotisation WHERE fk_cotisation=".$array_id_cotis[$j]." AND fk_bulletin=".$obj_id_bulletin->rowid;
										$res_som_cotisation_bonus  = $db->query($sql_som_cotisation_bonus);
										if($res_som_cotisation_bonus){
											$obj_som_cotisation_bonus = $db->fetch_object($res_som_cotisation_bonus);
											$somme_cotisation += $obj_som_cotisation_bonus->montant_employe + $obj_som_cotisation_bonus->montant_employeur;
											$a ++;
											
										}
										$k ++;
								}

								print "<td align='center'>".apres_virgule($db, $id_societe, $somme_cotisation?:0, 2)."</td>";
								$array_cotis_an[$j] += $somme_cotisation;
							}
					if($user->rights->paiementsalaire->salarie->voirDocument && $obj_verif->cloture=='oui')
						print "<td ><a style='text-decoration : none;' title='Voir' target='_blank' href='./../doc/fiche_cotisation.php?mainmenu=paiementsalaire&leftmenu=societe&id_societe=".$id_societe."&action=voir&annee=".$annee_rechercher."&mois=".($i+1)."'><span class='fa fa-search-plus'></span>&nbsp; &nbsp;</a>&nbsp;
						<a style='text-decoration : none;' title='Télécharger' target='_blank' href='./../doc/fiche_cotisation.php?mainmenu=paiementsalaire&leftmenu=societe&id_societe=".$id_societe."&action=telecharger&annee=".$annee_rechercher."&mois=".($i+1)."'><span class='fa fa-download'></span> &nbsp;</a>";
					else
						print "<td align='center'><span class='fa fa-search-plus' style='color: gray'></span> &nbsp;&nbsp;&nbsp;
						<span class='fa fa-download' style='color: gray'> &nbsp;&nbsp;&nbsp;";
					print "</td>";
					
				}else{
					print "<td ><b>".$mois_tab[$i]."</b></td><td colspan='".($nb_cotisation + 2)."'></td>";
			}

			print "</tr>";
		}
		print "</tbody>";

		$indice_info = info_admin("Il s'agit du nombre total des distincts salariés dont les salaires ont été générés en ".$annee_rechercher, 1);
        print "<tr class='liste_titre'><th>Total de l’année ".$annee_rechercher."</th>";
		print "<th>".$nb_salaries_par_an." ".$indice_info."</th>";
		for ($i=0; $i < count($array_cotis_an); $i++) { 
			print "<th align='center'>".apres_virgule($db, $id_societe, $array_cotis_an[$i]?:0, 2)."</th>";
		}
		print "<th align='center'></th>";

		print "</tr>";
		print "</table>";
		print "</form>";
		//print '<a class="button" target="_blank" href="./doc/fiche_taxe.php?id_societe='.$id_societe.'">Generer fiche taxe</a>';
		print "<br>";
	
//Gestion de l'action Generer Bulletin
//--------------------------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------------------------



$db->close();		

}else{
	print "<h2>Veuillez affecter une <b>convention</b> à cette société</h2>";
	}
	function apres_virgule($db, $id_societe, $valeur, $decalage){
		$sep = ".";
		$decalage = 2;
		$reglage_bulletin = "SELECT separateur, decalage FROM ".MAIN_DB_PREFIX."reglage_bulletin WHERE fk_societe=".$id_societe;
		  $result_reglage_bulletin = $db->query($reglage_bulletin);
		  if($db->num_rows($result_reglage_bulletin) > 0){
			$obj_reglage_bulletin = $db->fetch_object($result_reglage_bulletin);
			$sep = $obj_reglage_bulletin->separateur;
			$decalage = $obj_reglage_bulletin->decalage;
		  }
		return number_format($valeur, $decalage, $sep, ' ');
	  }
//$confirmation = "Voulez vous gernerer les bulettins de paies pour l ensemble des salariés pour le mois de ".$mois_tab[$mois-1]." ? ce processus peut prendre plusieurs minutes selon le nombre de salarié(e)s.";
print "<script>
var button_generer = document.getElementById('button_generer');
button_generer.addEventListener('click', myFunction);
defaut = button_generer.getAttribute('href');


let mois_table = [' janvier ',' février ',' mars ',' avril ',' mai ',' juin ',' juillet ',' août ',' septembre ',' octobre ',' novembre ',' décembre '];
function myFunction(){
	var date = new Date;
	var result = confirm('Voulez vous gernerer les bulettins de paies pour l ensemble des salariés pour le mois de '+mois_table[date.getMonth()]+' ? ce processus peut prendre plusieurs minutes selon le nombre de salarié(e)s.');
	if(result)
		button_generer.setAttribute('href', defaut);
	else
		button_generer.setAttribute('href', '#');
	
	  
}//e.preventdefault
</script>";
if(!empty($message))
print "<script>
$.jnotify('".$message."', {delay : 5000, fadeSpeed: 500});
</script>";