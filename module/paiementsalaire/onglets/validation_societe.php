<?php
require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/custom/paiementsalaire/lib/paiementsalaire.lib.php';


llxHeader("", "Paiement | Salaire");
$id_societe = GETPOST('id_societe','int');
$id_convention = GETPOST('id_convention','int');
$limit = GETPOST('limit','int')?:20;
$arret = GETPOST('arret','int')?:0;
$nb_page = GETPOST('nbpage','int')?:1;

$head = paiementsalaireSocieteHead($id_societe, $id_convention);
print dol_get_fiche_head($head, 'validation', "", -1, '');


if(!empty($id_convention)){
$soc_sql = "SELECT * FROM ".MAIN_DB_PREFIX."societe WHERE rowid=".$id_societe;
$soc_res = $db->query($soc_sql);//= $db->query($covSql);
if($soc_res){
	$obj_soc = $db->fetch_object($soc_res);
}
if(empty($obj_soc)){
	$obj_soc = new stdClass();
	$obj_soc->rowid = $id_societe;
	$obj_soc->nom = '';
}
$obj_soc->name = !empty($obj_soc->nom) ? $obj_soc->nom : '';
$obj_soc->element = "societe";			
$obj_soc->conv = $id_convention;

societe_preview_next($db, $id_societe, $obj_soc);
entete_societe($obj_soc, 'societe');
print "<hr>";
$message = "";
$sel15 = "";
$num_all = 0;
$tab_message = array();
$trouve = false;
$sql = "SELECT u.rowid, u.lastname, u.firstname, u.dateemployment, ue.fk_object, ue.egp FROM ".MAIN_DB_PREFIX."user as u";
	$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."user_extrafields as ue ON u.rowid=ue.fk_object Where ue.egp=".$id_societe;
	$result = $db->query($sql);
	if($result){
		$num = $db->num_rows($result);
		if($num > 0){
			$a = 0;
			while ($a < $num) {
				$obj = $db->fetch_object($result);
				$sql1 = "SELECT fk_user FROM ".MAIN_DB_PREFIX."salarie where fk_user=".$obj->rowid;
				$result1 = $db->query($sql1);
				if($result1){
					$obj1 = $db->fetch_object($result1);
					if($obj1)
					$trouve = true;
				}
				$a ++;
			}
			
			
		}
	}
	$obj_liste = array();
	$obj_message = array();
	$obj_url = array();
	$obj_info = array();

if($trouve == true){


$sql = "SELECT u.*, ue.fk_object, ue.egp FROM ".MAIN_DB_PREFIX."user as u";
$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."user_extrafields as ue ON u.rowid=ue.fk_object Where ue.egp=".$id_societe;

$result_user = $db->query($sql);
if($result_user){
	$num_all = $db->num_rows($result_user);
	$i = 0;
	while ($i < $num_all){
		//Objet Utilisateur
		$message = "";
		$obj_user = $db->fetch_object($result_user);			
		if(empty($obj_user)){
			$i++;
			continue;
		}
		$tab_message = array();
		$tab_url = array();
		$tab_info = array();
		//Objet Utilisateur
		$message = "";
		$sql_sal = "SELECT * FROM ".MAIN_DB_PREFIX."salarie WHERE fk_user=".$obj_user->rowid;
		$result_sal = $db->query($sql_sal);
			$obj_salarie = null;
			if($result_sal){
				$obj_salarie = $db->fetch_object($result_sal);
			}
			$virgule = 0;
		if($obj_salarie){
			$salaire_base = 0;
			$obj_grille = null;
			$objSalBase = null;
			$categorie_ok = ($obj_salarie->fk_categorie !== null && $obj_salarie->fk_categorie !== '' && (int) $obj_salarie->fk_categorie > 0);
			// Dans cette logique métier, fk_echelon = 0 est une valeur valide.
			$echelon_ok = ($obj_salarie->fk_echelon !== null && $obj_salarie->fk_echelon !== '');

			$grilleSql = "SELECT rowid FROM ".MAIN_DB_PREFIX."grille WHERE active=1 AND fk_convention=".$id_convention;
			$grilleResult = $db->query($grilleSql);//= $db->query($grilleSql);
			if($grilleResult){
				$obj_grille = $db->fetch_object($grilleResult);
			}

			if($obj_grille && $categorie_ok && $echelon_ok){
				$salBaseSql = "SELECT salaire_base FROM ".MAIN_DB_PREFIX."grille_categorie_echelon_salaire_base WHERE fk_grille=".$obj_grille->rowid." AND fk_categorie=".(int) $obj_salarie->fk_categorie." AND fk_echelon=".(int) $obj_salarie->fk_echelon;
				$salBaseResult = $db->query($salBaseSql);//= $db->query($covSql);
				if($salBaseResult){
					$objSalBase = $db->fetch_object($salBaseResult);
				}
			}

			$salaire_base_ok = ($objSalBase && $objSalBase->salaire_base !== null && $objSalBase->salaire_base !== '');
			$url = './salarie_information.php?mainmenu=paiementsalaire&leftmenu=salarie&fk_salarie='.$obj_salarie->rowid.'&id_societe='.$id_societe.'&id='.$obj_user->rowid.'&id_convention='.$id_convention.'&action=detail';

			if(!$obj_salarie->matricule){
				$tab_message[] = "Matricule";
				$tab_info[] = "Ce salarié n'a pas de matricule";
				$tab_url[] = $url;
			}

			if(!$categorie_ok){
				$tab_message[] = "Catégorie";
				$tab_url[] = $url;
				$tab_info[] = "Ce salarié n'a pas de catégorie";
			}

			if($categorie_ok && !$echelon_ok){
				$tab_message[] = "Echelon";
				$tab_url[] = $url;
				$tab_info[] = "Ce salarié n'a pas d'échelon";
			}

			if($categorie_ok && $echelon_ok && !$salaire_base_ok){
				$tab_message[] = "Salaire de Base";
				$tab_info[] = "Ce salarié n'a pas de salaire de base";
				$tab_url[] = $url;
			}
			
			if($obj_salarie->sursalaire == null){
				$tab_message[]= "Sursalaire";
				$tab_info[] = "Ce salarié n'a pas de sursalaire";
				$tab_url[] = './simulation.php?mainmenu=paiementsalaire&leftmenu=salarie&fk_salarie='.$obj_salarie->rowid.'&id_societe='.$id_societe.'&id='.$obj_user->rowid.'&id_convention='.$id_convention.'&action=detail';	
			}

			$annee = (int)date("Y");
			$mois = (int)date("m");
			$jour = (int)date("d");
			$sql_contrat1 = "SELECT * FROM ".MAIN_DB_PREFIX."salarie_contrat WHERE fk_salarie=".$obj_salarie->rowid." AND active=1 AND fk_type_contrat != 2";
			$sql_contrat1 .= " AND ( YEAR(date_fin)>".$annee;
			$sql_contrat1 .= " OR ((YEAR(date_fin) = ".$annee."  AND MONTH(date_fin) > ".$mois.") OR  (YEAR(date_fin) = ".$annee."  AND MONTH(date_fin) = ".$mois." AND DAY(date_fin) >= ".$jour.")))";
			$res_contrat1 = $db->query($sql_contrat1);
			

			if(!$res_contrat1 || $db->num_rows($res_contrat1) <= 0){
				$sql_contrat2 = "SELECT * FROM ".MAIN_DB_PREFIX."salarie_contrat WHERE fk_salarie=".$obj_salarie->rowid." AND active=1 AND fk_type_contrat = 2";
				$res_contrat2 = $db->query($sql_contrat2);
				if(!$res_contrat2 || $db->num_rows($res_contrat2) <= 0){
					$sql_contrat3 = "SELECT * FROM ".MAIN_DB_PREFIX."salarie_contrat WHERE fk_salarie=".$obj_salarie->rowid." AND active=1";
					$res_contrat3 = $db->query($sql_contrat3);
					if($res_contrat3 && $db->num_rows($res_contrat3) > 0){
						$tab_message[] = "Contrat expiré";
						$tab_info[] = "Ce salarié n'a pas de contrat";
						$tab_url[] = './contrat.php?mainmenu=paiementsalaire&leftmenu=salarie&fk_salarie='.$obj_salarie->rowid.'&id_societe='.$id_societe.'&id='.$obj_user->rowid.'&id_convention='.$id_convention.'&action=detail';

					}else{
						$tab_message[] = "Contrat";
						$tab_info[] = "Ce salarié n'a pas de contrat";
						$tab_url[] = './contrat.php?mainmenu=paiementsalaire&leftmenu=salarie&fk_salarie='.$obj_salarie->rowid.'&id_societe='.$id_societe.'&id='.$obj_user->rowid.'&id_convention='.$id_convention.'&action=detail';

					}
					
				}
			}

			if(!$obj_salarie->date_anciennete && !$obj_user->dateemployment){
				$tab_message[] = "Anciennete";
						$tab_info[] = "Ce salarié n'a de date pour calculer l'ancienneté";
						$tab_url[] = './contrat.php?mainmenu=paiementsalaire&leftmenu=salarie&fk_salarie='.$obj_salarie->rowid.'&id_societe='.$id_societe.'&id='.$obj_user->rowid.'&id_convention='.$id_convention.'&action=edit_anciennete';

			}
			

			if(!$obj_user->job){
				$tab_message[]= "Poste";
				$tab_info[] = "Poste est manquant";
				$tab_url[] = '../../user/card.php?id='.$obj_user->rowid.'&action=edit';	

			}

			if(!$obj_salarie->situation_familiale){
				$tab_message[] = "Statut Matrimoniale";
				$tab_info[] = "Vous devez préciser si ce salarié est : Célibatire, Marié ou Divorcé";
				$tab_url[] = './salarie_information.php?mainmenu=paiementsalaire&leftmenu=salarie&fk_salarie='.$obj_salarie->rowid.'&id_societe='.$id_societe.'&id='.$obj_user->rowid.'&id_convention='.$id_convention.'&action=detail';	
			}
			
			if($obj_salarie->nombre_enfant == null){
				$tab_message[] = "Nombre enfant";
				$tab_info[] = "S'il ce salarié n'a pas d'enfant, mettez le Nombre enfant à zéro(0)";
				$tab_url[] = './salarie_information.php?mainmenu=paiementsalaire&leftmenu=salarie&fk_salarie='.$obj_salarie->rowid.'&id_societe='.$id_societe.'&id='.$obj_user->rowid.'&id_convention='.$id_convention.'&action=detail';	
			}

			if($obj_salarie->nombre_enfant_hand == null){
				$tab_message[] = "Nombre enfant Handicapé";
				$tab_info[] = "S'il ce salarié n'a pas d'enfant Handicapé, mettez le Nombre enfant Hand à zéro(0)";
				$tab_url[] = './salarie_information.php?mainmenu=paiementsalaire&leftmenu=salarie&fk_salarie='.$obj_salarie->rowid.'&id_societe='.$id_societe.'&id='.$obj_user->rowid.'&id_convention='.$id_convention.'&action=detail';	

			}

			if(!empty($tab_message)){
				$obj_liste[] = "<a href=".$url.">".$obj_user->firstname." ".$obj_user->lastname."</a>";
				$obj_message[] = $tab_message;
				$obj_info[] = $tab_info;
				$obj_url[] = $tab_url;
			}
			//---------------------------------------------------------------------
		}else{
			$url = './salarie_information.php?mainmenu=paiementsalaire&leftmenu=salarie&id_societe='.$id_societe.'&id='.$obj_user->rowid.'&id_convention='.$id_convention.'&action=detail';
			$obj_liste[] = "<a href=".$url.">".$obj_user->firstname." ".$obj_user->lastname."</a>";
			$tab_message[] = "N'est pas enregistrer";
			$tab_info[] = "Aucun élement de salarié n'est renseigné";
			$tab_url[] = $url;
			$obj_message[] = $tab_message;
			$obj_info[] = $tab_info;
			$obj_url[] = $tab_url;
		}
		
			$i ++;
	}
}
	$num = count($obj_liste) == 0 ? 1 : count($obj_liste);
	$sel5 = "selected";
		$sel10 = "";
		$sel15 = "";
		$sel25 = "";
		$sel20 = "";
		$sel30 = "";
		$sel50 = "";
		$sel100 = "";
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
		else $sel100 = "selected";
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
				<option value='100' ".$sel100."><b>100</b></option>";
				print "</select><mark><b>".(GETPOST("nbpage","int")?:1)."</b></mark>/<mark><b>".(((int)($num%$limit))==0?((int)($num/$limit)):((int)($num/$limit)+1))."</b></mark>";
				print '<script type="text/javascript">
				var convention = document.getElementById("limit");
				convention.addEventListener("change", function () {
					var limit = convention.value;
					window.location.href = "'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenusociete&id_societe='.$id_societe.'&id_convention='.$id_convention.'&limit="+limit;
				},
				false,
				);
				</script>';
			print "</form></div>";
	if(count($obj_liste) < 1){
		print "<div><table class='tagtable liste'>";
		print "<tr class='liste_titre'><td>Resultat de la verification</td></tr>";
		print "<tr><td>Vérification effectuée avec succès ".img_picto("Tout est OK", "tick")."</td></tr>";
		print "</table> </div>";
	}else{
		$i = $arret;
		print "<h2>Verification des informations</h2>";
		while ($i < $num) { 

			print "<div><table class='tagtable liste'>";
			print "<tr class='liste_titre'><td colspan='3'>".$obj_liste[$i]."</td></tr>";
			$tab_message = $obj_message[$i];
			$tab_url = $obj_url[$i];
			$tab_info = $obj_info[$i];
			for ($j=0; $j < count($tab_message); $j++) {
				$class = "impair";
				if($j % 2 == 0)
					$class = "pair";
				print '<tr class='.$class.'>';
				print "<td>".img_picto($tab_info[$j],"error")." ".$tab_message[$j]."</td>";
				print "<td></td><td align='center'><a target= '_blank' href='".$tab_url[$j]."'>".img_picto("Mettre un ".$tab_message[$j],"edit")."</a></td>";
				print '</tr>';
			}
			print "</table></div><br>";
			if($i!= 0 && (($i+1)%$limit) == 0){
				$arret = $i;
				$i = $num;
			}else
				$i ++;
		}
	}

	print '<span style="float:right; margin-left: 20px;">';
			$nb = (((int)($num%$limit))==0?((int)($num/$limit)):((int)($num/$limit)+1));
	$page_link = "";
	if($num>$limit){

		if($nb_page!= 1)
			if($nb==0 && 1 < ($nb))
				$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=0&nbpage=1' style='padding: 5px'><b>Debut</b>    </a>&nbsp;&nbsp;";
			else if(1 < ($nb+1))
			$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=0&nbpage=1' style='padding: 5px'><b>Debut</b>    </a>&nbsp;&nbsp;";

		
		if($arret > $limit){

			
			if($nb_page-3>=0)
				$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=".($limit*($nb_page-3))."&nbpage=".($nb_page-2)."' style='padding: 5px'><b>".($nb_page -2)."</b></a>&nbsp;&nbsp;";

			if($nb_page-2>=0)
						$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=".($limit*($nb_page-2))."&nbpage=".($nb_page-1)."' style='padding: 5px'><b>".($nb_page-1)."</b></a>&nbsp;&nbsp;";
			
			
			if($nb_page-1>=0)
					$page_link .= "<a style='background-color: yellow;' href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=".($limit*($nb_page-1))."&nbpage=".($nb_page)."' style='padding: 5px'><b>".($nb_page)."</b></a>&nbsp;&nbsp;";

		

			
				if(	(($nb_page+1) <= ($nb)))
					$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=".($limit*$nb_page)."&nbpage=".($nb_page+1)."' style='padding: 5px'><b>".($nb_page + 1)."</b></a>&nbsp;&nbsp;";

			
				if((($nb_page+2) <= ($nb)))
					$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=".($limit*($nb_page +1))."&nbpage=".($nb_page+2)."' style='padding: 5px'><b>".($nb_page + 2)."</b></a>&nbsp;&nbsp;";
					
				
				if((($nb_page+3) <= ($nb)))
					$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=".($limit*($nb_page+2))."&nbpage=".($nb_page+3)."' style='padding: 5px'><b>".($nb_page + 3)."</b></a>&nbsp;&nbsp;";

					


		}else{

			
				if( 1 <= ($nb))
					
					$page_link .= "<a style='background-color: yellow;' href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=0&nbpage=1' style='padding: 5px'><b>1</b></a>&nbsp;&nbsp;";
			
			
				if(2 <= ($nb))
					
					$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=".$limit."&nbpage=2' style='padding: 5px'><b>2</b></a>&nbsp;&nbsp;";
			
			
				if(3 <= ($nb))
					
					$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=".($limit*2)."&nbpage=3' style='padding: 5px'><b>3</b></a>&nbsp;&nbsp;";
				
				if(4 <= ($nb))
					
					$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=".($limit*3)."&nbpage=4' style='padding: 5px'><b>4</b></a>&nbsp;&nbsp;";

				if(5 <= ($nb))
					
					$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=".($limit*4)."&nbpage=5' style='padding: 5px'><b>5</b></a>&nbsp;&nbsp;";



		}
		if($nb_page != ($nb)  )
				$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=".($limit*($nb-1))."&nbpage=".($nb)."' style='padding: 5px'>      <b>Fin</b></a>&nbsp;&nbsp;";

		
	}
			print $page_link.'</span>';
}else {
	print "<h2 style='align:center;'>Cette sociétée n'a aucun employé!";
}
// $db->free();

}else{
	print "<h2>Veuillez affecter une <b>convention</b> à cette société</h2>";
	}