<?php
require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/custom/paiementsalaire/lib/paiementsalaire.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';

//Titre 
//print load_fiche_titre($langs->trans("Les Cotisations de la societe"), '', '');
//print '<hr>';
$id_societe = GETPOST('id_societe','int');
$action =  GETPOST('action','alpha');
$id_convention = GETPOST('id_convention','int');

$message = "";


llxHeader("", "Paiement | Salaire");
print load_fiche_titre($langs->trans("Les taxes(impôts, ...) des compagnies"), '', '');

if(empty($action))
	$action = "annee_rechercher";
$head = paiementsalaireSocieteHead($id_societe, $id_convention);
print dol_get_fiche_head($head, 'taxe', "", -1, '');

if(!empty($id_convention)){
if(empty($action))
	$action = "annee_rechercher";
$soc_sql = "SELECT * FROM ".MAIN_DB_PREFIX."societe WHERE rowid=".$id_societe;
$soc_res = $db->query($soc_sql);//= $db->query($covSql);
$obj_soc = $db->fetch_object($soc_res);
$obj_soc->name = $obj_soc->nom;
$obj_soc->element = "societe";			
$obj_soc->conv = $id_convention;


societe_preview_next($db, $id_societe, $obj_soc);
entete_societe($obj_soc, 'societe');
$db->free($soc_res);

$mois_tab = array(" Janvier "," Février "," Mars "," Avril "," Mai "," Juin "," Juillet "," Août "," Septembre "," Octobre "," Novembre "," Décembre ");
$form = new Form($db);

if($action == "article29_periode"){
    $mois_tab = array(" Janvier "," Février "," Mars "," Avril "," Mai "," Juin "," Juillet "," Août "," Septembre "," Octobre "," Novembre "," Décembre ", " 13è Mois ");

    //Annee dont les bulletins sont générés
    $sql_verif = "SELECT DISTINCT annee, mois FROM ".MAIN_DB_PREFIX."bulletin WHERE cloture='oui' AND fk_societe=".$id_societe." ORDER BY annee ASC";
    $res_verif = $db->query($sql_verif);
    $num = $db->num_rows($sql_verif);
    $a = 1;
    $key = array();
    $val = array();
    while ($a <= $num) {
        $obj_verif = $db->fetch_object($res_verif);
        $key[] = $obj_verif->mois.'_'.$obj_verif->annee;
        $val[] = $mois_tab[$obj_verif->mois - 1].''.$obj_verif->annee;
        $a ++;
    }

	$array[] = array('label'=> 'Date début=> ','type'=> 'select', 'size'=>'', 'morecss'=>'', 'moreattr'=>'required', 'name'=>'date_debut','values' => array_combine($key,$val));
	//$array[] = array('label'=> 'mois début=> ','type'=> 'select', 'size'=>'', 'morecss'=>'', 'moreattr'=>'selected', 'name'=>'mois_debut','values' => array_combine($key,$val));

	$array[] = array('label'=> 'Date fin=> ','type'=> 'select', 'size'=>'', 'morecss'=>'', 'moreattr'=>'required', 'name'=>'date_fin','values' => array_combine($key,$val));
	//$array[] = array('label'=> 'Annee fin=> ','type'=> 'select', 'size'=>'', 'morecss'=>'', 'moreattr'=>'selected', 'name'=>'mois_fin','values' => array_combine($key,$val));

    $url = $_SERVER['PHP_SELF']."?mainmenu=paiementsalaire&leftmenu=societe&id_societe=".$id_societe."&id_convention=".$id_convention;
    $titre = 'Article 29 I.T.S';

    $formconfirm = $form->formconfirm(
        $url, 
        $titre, 
        img_picto('','warning')." Attention : Les valeurs de cette exportation ne seront pas sauvegardées", 
        'validation', 
        $array,
        '',
        1,
        240,
        '30%'
    );
    print $formconfirm;
	$action = 'annee_rechercher';
}


if($action == 'annee_rechercher'){
	//Gestion des année et des dates pour l'historique => Gestion des actions recherche année
	$annee_rechercher = GETPOST("annee_rechercher", "int");
	$annee_courant = (int) date("Y");
	if(empty($annee_rechercher))
		$annee_rechercher = (int) date("Y");
	$mois_courant = (int) date("m");
	if($annee_rechercher != $annee_courant)
	print "<h2 style='align:center; display: inline'>Historique des taxes de l'année ".$annee_rechercher."!</h2>";
else print "<h2 style='align:center;display: inline'>Taxes de ".$annee_rechercher."!</h2>";
 
	print "&ensp;&ensp;<span class='button'><label for='article29_its'>Article 29 I.T.S</label>&ensp;<select id='article29_its' name='article29_its'>";
	print "<option value='0' ></option>";
	print "<option value='pdf' >PDF</option>";
	print "<option value='excel' selected >Excel</option>";
	print "</select></span>";

	print '<script type="text/javascript">
				var article29_its = document.getElementById("article29_its");
				article29_its.addEventListener("change", function () {
					var article29_its_val = article29_its.value;
					if(article29_its_val == "excel"){
						alert("Pas encors disponible !!!");
						//window.location.href = "../doc/excel_article29_its.php?id_societe='.$id_societe.'&annee='.$annee_rechercher.'";
					}else if(article29_its_val == "pdf"){
						window.location.href = "../doc/article29_its.php?id_societe='.$id_societe.'&annee='.$annee_rechercher.'";
					}
				},
				false,
				);
				</script>';


	print "<div style='float: right; display: inline''>";
	print '<form name="add" method="POST" action="'.$_SERVER['PHP_SELF'].'?mainmenu=paiementsalaire&leftmenu=salarie&id_societe='.$id_societe.'&id_convention='.$id_convention.'">';
	print '<input type="hidden" name="token" value="'.newToken().'">';
	print '<input type="hidden" name="action" value="annee_rechercher">';

	print "<select name='annee_rechercher'>";
				$sql_verif = "SELECT DISTINCT annee FROM ".MAIN_DB_PREFIX."bulletin WHERE fk_societe=".$id_societe;
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
$element_taxe = "";
$array_id_taxe = array();
$array_taxe_an = array();
$nb_taxe = 1;
$sql_type_taxe = "SELECT rowid, libelle as code FROM ".MAIN_DB_PREFIX."type_taxe";
	$res_type_taxe = $db->query($sql_type_taxe);
	if($res_type_taxe){
		$nb_taxe = $db->num_rows($res_type_taxe);
		$a = 0;
		while ($a < $nb_taxe) {
			$obj_type_taxe = $db->fetch_object($res_type_taxe);
			$array_id_taxe[] = $obj_type_taxe->rowid;
			$array_taxe_an[] = 0;
			$element_taxe .= "<th>".$obj_type_taxe->code."</th>";
			$a ++;

		}
		$db->free($res_type_taxe);

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
	print "<th rowspan='2'>Nb salariés</th>";
	print "<th colspan=".$nb_taxe." align='center'>Taxes</th>";
	print "<th rowspan='2' align='center'>Opérations</tr>";

	print "<tr>";
	print $element_taxe;
	print "</tr></thead>";

		for ($i=0; $i < count($mois_tab); $i++) { 
			print "<tr class='impair'>";
			$nb_j = (int) date("d");
				$sql_verif = "SELECT rowid, cloture FROM ".MAIN_DB_PREFIX."bulletin WHERE annee=".$annee_rechercher." AND mois=".($i + 1)." AND fk_societe=".$id_societe;
				$res_verif = $db->query($sql_verif);
				$nb_salarie = $db->num_rows($res_verif);
				$obj_verif = $db->fetch_object($res_verif);

				if(($obj_verif->cloture=="oui")){

					//Avertissons qu'il y a un complément salaire (bonus)
					$sql_id_bulletin = "SELECT rowid FROM ".MAIN_DB_PREFIX."bulletin_bonus WHERE annee=".$annee_rechercher." AND mois=".($i + 1)." AND fk_societe=".$id_societe;
					$res_id_bulletin  = $db->query($sql_id_bulletin);
					$num_k_bonus = $db->num_rows($res_id_bulletin);
					$bonus = "";
					if($num_k_bonus > 0)
						$bonus = info_admin("Un complément salaire est lié à ce mois", 1);
					print "<td><b>".$mois_tab[$i]." ".$bonus."</b></td>";
					print "<td>".$nb_salarie."</td>";
							
					for ($j=0; $j < count($array_id_taxe); $j++) {
						
						//Bulletin
						$sql_id_bulletin = "SELECT rowid FROM ".MAIN_DB_PREFIX."bulletin WHERE annee=".$annee_rechercher." AND mois=".($i + 1)." AND fk_societe=".$id_societe;
						$res_id_bulletin  = $db->query($sql_id_bulletin);
						$num_k = $db->num_rows($res_id_bulletin);
						$somme_taxe = 0;
						$k = 0;
						while ($k < $num_k){
							$obj_id_bulletin = $db->fetch_object($res_id_bulletin);
							$sql_som_taxe = "SELECT montant FROM ".MAIN_DB_PREFIX."bulletin_taxe WHERE fk_taxe=".$array_id_taxe[$j]." AND fk_bulletin=".$obj_id_bulletin->rowid;
							$res_som_taxe  = $db->query($sql_som_taxe);
							$type = 1;
							if($db->num_rows($res_som_taxe) == 0){
								$sql_som_taxe = "SELECT * FROM ".MAIN_DB_PREFIX."bulletin_taxe2 WHERE fk_taxe=".$array_id_taxe[$j]." AND fk_bulletin=".$obj_id_bulletin->rowid;
								$res_som_taxe  = $db->query($sql_som_taxe);
								$type = 2;
							}
							if($res_som_taxe){
								$num_bul = $db->num_rows($res_som_taxe);
								$a = 0;
								while ($a < $num_bul) {
											$obj_som_taxe = $db->fetch_object($res_som_taxe);
											if($type == 1){
												$somme_taxe += $obj_som_taxe->montant;
											}else{
												$somme_taxe += $obj_som_taxe->montant_employe;
												$somme_taxe += $obj_som_taxe->montant_employeur;
											}
											$a ++;
								}
							}
							$k ++;
						}

						

						//Bulletin bonus
						$sql_id_bulletin = "SELECT rowid FROM ".MAIN_DB_PREFIX."bulletin_bonus WHERE annee=".$annee_rechercher." AND mois=".($i + 1)." AND fk_societe=".$id_societe;
						$res_id_bulletin  = $db->query($sql_id_bulletin);
						$num_k_bonus = $db->num_rows($res_id_bulletin);
						$k_bonus = 0;
						while ($k_bonus < $num_k_bonus){
							$obj_id_bulletin = $db->fetch_object($res_id_bulletin);
							$sql_som_taxe = "SELECT montant FROM ".MAIN_DB_PREFIX."bulletin_bonus_taxe WHERE fk_taxe=".$array_id_taxe[$j]." AND fk_bulletin=".$obj_id_bulletin->rowid;
							$res_som_taxe  = $db->query($sql_som_taxe);

							$type = 1;
							if($db->num_rows($res_som_taxe) == 0){

								$sql_som_taxe = "SELECT montant_employe, montant_employeur FROM ".MAIN_DB_PREFIX."bulletin_bonus_taxe2 WHERE fk_taxe=".$array_id_taxe[$j]." AND fk_bulletin=".$obj_id_bulletin->rowid;
								$res_som_taxe  = $db->query($sql_som_taxe);
								$type = 2;

							}

							if($res_som_taxe){
								$num_bul = $db->num_rows($res_som_taxe);
								$a = 0;
								while ($a < $num_bul) {

											$obj_som_taxe = $db->fetch_object($res_som_taxe);
											if($type == 1){
												$somme_taxe += $obj_som_taxe->montant;
											}else{
												$somme_taxe += $obj_som_taxe->montant_employe;
												$somme_taxe += $obj_som_taxe->montant_employeur;
											}
											$a ++;
								}
							}
								$k_bonus ++;
						}
						$db->free($res_id_bulletin);
						print "<td align='center'>".apres_virgule($db, $id_societe, $somme_taxe?:0, 2)."</td>";
						$array_taxe_an[$j] += $somme_taxe; 

					}

					if($user->rights->paiementsalaire->salarie->voirDocument)
						print "<td align='center'><a style='text-decoration : none;' title='Voir' target='_blank' href='./../doc/fiche_cotisation.php?mainmenu=paiementsalaire&leftmenu=societe&id_societe=".$id_societe."&action=voir&annee=".$annee_rechercher."&mois=".($i+1)."'><span class='fa fa-search-plus'></span>&nbsp; &nbsp;</a>&nbsp;
						<a style='text-decoration : none;' title='Télécharger' target='_blank' href='./../doc/fiche_cotisation.php?mainmenu=paiementsalaire&leftmenu=societe&id_societe=".$id_societe."&action=telecharger&annee=".$annee_rechercher."&mois=".($i+1)."'><span class='fa fa-download'></span> &nbsp;</a>";
					else
						print "<td align='center'><span class='fa fa-search-plus' style='color: gray'></span> &nbsp;&nbsp;&nbsp;
						<span class='fa fa-download' style='color: gray'> &nbsp;&nbsp;&nbsp;";
					print "</td>";
					
				}else if(($obj_verif->cloture=="non")){
					print "<td><b>".$mois_tab[$i]." ".info_admin('Veuillez cloturer le mois pour Voir ou Télécharger', 1)."</b></td>";
					print "<td>".$nb_salarie."</td>";
												
					for ($j=0; $j < count($array_id_taxe); $j++) {
						//Bulletin
						$sql_id_bulletin = "SELECT rowid FROM ".MAIN_DB_PREFIX."bulletin WHERE annee=".$annee_rechercher." AND mois=".($i + 1)." AND fk_societe=".$id_societe;
						$res_id_bulletin  = $db->query($sql_id_bulletin);
						$num_k = $db->num_rows($res_id_bulletin);
						$somme_taxe = 0;
						$k = 0;
						while ($k < $num_k){
							$obj_id_bulletin = $db->fetch_object($res_id_bulletin);
							$sql_som_taxe = "SELECT montant FROM ".MAIN_DB_PREFIX."bulletin_taxe WHERE fk_taxe=".$array_id_taxe[$j]." AND fk_bulletin=".$obj_id_bulletin->rowid;
							$res_som_taxe  = $db->query($sql_som_taxe);
							$type = 1;
							if($db->num_rows($res_som_taxe) == 0){
								$sql_som_taxe = "SELECT montant_employe, montant_employeur FROM ".MAIN_DB_PREFIX."bulletin_taxe2 WHERE fk_taxe=".$array_id_taxe[$j]." AND fk_bulletin=".$obj_id_bulletin->rowid;
								$res_som_taxe  = $db->query($sql_som_taxe);
								$type = 2;
							}
							if($res_som_taxe){
								$num_bul = $db->num_rows($res_som_taxe);
								$a = 0;
								while ($a < $num_bul) {
											$obj_som_taxe = $db->fetch_object($res_som_taxe);
											if($type == 1){
												$somme_taxe += $obj_som_taxe->montant;
											}else{
												$somme_taxe += $obj_som_taxe->montant_employe;
												$somme_taxe += $obj_som_taxe->montant_employeur;

											}
											$a ++;
								}
							}
							$k ++;
						}						

						//Bulletin bonus
						$sql_id_bulletin = "SELECT rowid FROM ".MAIN_DB_PREFIX."bulletin_bonus WHERE annee=".$annee_rechercher." AND mois=".($i + 1)." AND fk_societe=".$id_societe;
						$res_id_bulletin  = $db->query($sql_id_bulletin);
						$num_k_bonus = $db->num_rows($res_id_bulletin);
						$k_bonus = 0;
						while ($k_bonus < $num_k_bonus){
							$obj_id_bulletin = $db->fetch_object($res_id_bulletin);
							$sql_som_taxe = "SELECT montant FROM ".MAIN_DB_PREFIX."bulletin_bonus_taxe WHERE fk_taxe=".$array_id_taxe[$j]." AND fk_bulletin=".$obj_id_bulletin->rowid;
							$res_som_taxe  = $db->query($sql_som_taxe);
							$type = 1;
							if($db->num_rows($res_som_taxe) == 0){
								$sql_som_taxe = "SELECT fk_taxe, montant_employe, montant_employeur FROM ".MAIN_DB_PREFIX."bulletin_bonus_taxe2 WHERE fk_taxe=".$array_id_taxe[$j]." AND fk_bulletin=".$obj_id_bulletin->rowid;
								$res_som_taxe  = $db->query($sql_som_taxe);
								$type = 2;
							}

							if($res_som_taxe){
								$num_bul = $db->num_rows($res_som_taxe);
								$a = 0;
								while ($a < $num_bul) {
									
											$obj_som_taxe = $db->fetch_object($res_som_taxe);
											if($type == 1){
												$somme_taxe += $obj_som_taxe->montant;
											}else{
												$somme_taxe += $obj_som_taxe->montant_employe;
												$somme_taxe += $obj_som_taxe->montant_employeur;
											}
											$a ++;
								}
							}
								$k_bonus ++;
						}
						$db->free($res_id_bulletin);
						print "<td align='center'>".apres_virgule($db, $id_societe, $somme_taxe?:0, 2)."</td>";

						$array_taxe_an[$j] += $somme_taxe; 
					}
					
						print "<td align='center'><span class='fa fa-search-plus' style='color: gray'></span> &nbsp;&nbsp;&nbsp;
						<span class='fa fa-download' style='color: gray'> &nbsp;&nbsp;&nbsp;";
					print "</td>";
				}else{
					print "<td ><b>".$mois_tab[$i]."</b></td><td colspan='".($nb_taxe + 2)."'></td>";
				}
			$db->free($res_verif);


			print "</tr>";
		}
		print "</tbody>";

		$indice_info = info_admin("Il s'agit du nombre total des distincts salariés dont les salaires ont été générés en ".$annee_rechercher, 1);
        print "<tr class='liste_titre'><th>Total de l’année ".$annee_rechercher."</th>";
		print "<th>".$nb_salaries_par_an." ".$indice_info."</th>";
		for ($i=0; $i < count($array_taxe_an); $i++) { 
			print "<th align='center'>".apres_virgule($db, $id_societe, $array_taxe_an[$i]?:0, 2)."</th>";
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

//Si la société n'a pas de salarié
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

