<?php
require '../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/custom/paiementsalaire/lib/paiementsalaire.lib.php';


llxHeader("", "Paiement | Salaire");
//Titre 
print load_fiche_titre($langs->trans("Liste des personnelles de la sociétés"), '', '');
//print '<hr>';

$limit = GETPOST('limit','09')?:5;
$arret = GETPOST('arret','09')?:0;
$nb_page = GETPOST('nbpage','09')?:1;




$recherche_nom = "";
			$recherche_prenom = "";
			$recherche_matricule = "";
			$recherche_societe = 0;
			$recherche_anciennete = 0;
			if($action = "recherche"){
				$recherche_nom = GETPOST("recherche_nom", "alpha");
				$recherche_prenom = GETPOST("recherche_prenom", "alpha");
				$recherche_matricule = GETPOST("recherche_matricule");
				$recherche_societe = GETPOST("recherche_societe", "09");
				$recherche_anciennete = GETPOST("recherche_anciennete", "09");
			}

$trouve = false;
$obj_liste = array();

	$sql = "SELECT u.rowid, u.lastname, u.firstname, u.dateemployment, ue.fk_object, ue.egp, soc.rowid as id_societe, sce.fk_object FROM ".MAIN_DB_PREFIX."user as u";
	$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."user_extrafields as ue ON u.rowid=ue.fk_object";
	$sql .= ' LEFT JOIN '.MAIN_DB_PREFIX.'societe as soc ON soc.rowid=ue.egp';
	$sql .= ' LEFT JOIN '.MAIN_DB_PREFIX.'societe_extrafields as sce ON soc.rowid=sce.fk_object';
	$sql .= ' WHERE sce.grp=1';


	if(!empty(GETPOST("recherche_nom", "aZ"))){
		$sql .= " AND (u.lastname LIKE '%".GETPOST("recherche_nom")."%'";
		$sql .= " OR u.firstname LIKE '%".GETPOST("recherche_nom")."%')";
	}
	

	if(!empty(GETPOST("recherche_prenom", "aZ"))){
		$sql .= " AND (u.firstname LIKE '%".GETPOST("recherche_prenom")."%'";
		$sql .= " AND u.lastname LIKE '%".GETPOST("recherche_prenom")."%')";
	}
	if(!empty(GETPOST("recherche_societe", "09"))){
		$sql .= " AND soc.rowid=".GETPOST("recherche_societe");
	}
	if(GETPOST("recherche_anciennete", "09")!=''){
		$annee = ((int)date("Y")-GETPOST("recherche_anciennete"));
		$mois = (int)date("m");
		$jour = (int)date("d");
		$sql .= " AND YEAR(u.dateemployment)=".$annee." AND MONTH(u.dateemployment)<=".$mois." AND DAY(u.dateemployment)<=".$jour;
	}
	if(!empty(GETPOST("recherche_matricule"))){
		$sql .= " AND sal.matricule LIKE '%".GETPOST("recherche_matricule")."%'";
	}
	$result = $db->query($sql);
	if($result){
		$num = $db->num_rows($result);
		if($num > 0){
			$a = 0;
			while ($a < $num) {
				$obj_liste[count($obj_liste)] = $db->fetch_object($result);;						
				$a ++;
			}
			
			
		}
	}

		$num = count($obj_liste);
		$sel5 = "selected";
		$sel10 = "";
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
				print "</select><mark><b>".(GETPOST("nbpage","06")?:1)."</b></mark>/<mark><b>".(((int)($num%$limit))==0?((int)($num/$limit)):((int)($num/$limit)+1))."</b></mark>";
				print '<script type="text/javascript">
				var convention = document.getElementById("limit");
				convention.addEventListener("change", function () {
					var limit = convention.value;
					window.location.href = "'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenusociete&id_societe='.$id_societe.'&id_convention='.$id_convention.'&limit="+limit+"&id_prime='.$id_prime.'&action=rechercher&recherche_nom='.$recherche_nom.'&recherche_prenom='.$recherche_prenom.'&recherche_matricule='.$recherche_matricule.'&recherche_anciennete='.$recherche_anciennete.'";
				},
				false,
				);
				</script>';
			print "</select>";
			print "</div>";

	
			print "<br>";
			print '<div>';
			print '<table style="width: 100%;" class="tagtable liste">';
			print '<tr  class="liste_titre" >';
			print '<td align="center"style="padding: 5px; width: 20%;">
			<input style="padding:10px" type="text" Placeholder="Nom" value="'.$recherche_nom.'" name="recherche_nom" >
			<br><label>Nom</label></td>';
			print '<td align="center"style="padding: 5px; width: 20%;">
			<input style="padding:10px" type="text" Placeholder="Prenom" value="'.$recherche_prenom.'" name="recherche_prenom" >
			<br><label>Prenom</label></td>';
			print '<td align="center"style="padding: 5px; width: 20%;">
			<input style="padding:10px" type="text" Placeholder="Matricule" value="'.$recherche_matricule.'" name="recherche_matricule" >
			<br><label>Matricule</label></td>';
			print '<td align="center"style="padding: 5px; width: 20%;"><label>Société</label><br>
			<select name="recherche_societe" ><option value=0></option>';
			$sql_soc = "SELECT sc.rowid, sc.nom, sce.rowid as r2, sce.fk_object FROM ".MAIN_DB_PREFIX."societe as sc";
			$sql_soc .= " LEFT JOIN ".MAIN_DB_PREFIX."societe_extrafields as sce ON sc.rowid=sce.fk_object WHERE sce.grp=1";
			$result_soc = $db->query($sql_soc);

			if($result_soc){
				$j = 0;
				$num_soc = $db->num_rows($result_soc);
				while ($j < $num_soc){
					$societe = $db->fetch_object($result_soc);
					if($recherche_societe == $societe->rowid)
						print '<option value="'.$societe->rowid.'" selected>'.$societe->nom.'</option>';
					else print '<option value="'.$societe->rowid.'">'.$societe->nom.'</option>';
					
					$j ++;
				}
			}
			
			print '</select></td>';
			print '<td align="center"style="padding: 5px; width: 20%;">
			<input style="padding:10px" type="text" Placeholder="Ancienneté" value="'.$recherche_anciennete.'" name="recherche_anciennete" >
			<br><label>Ancienneté</label></td>';

			print '<td align="center"style="padding: 5px; width: 20%;">';
			print '<input type="submit" class="button" value="Rechercher" >';
			print "</form>";
			print '<br>	<a href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=salarie&id_societe='.$id_societe.'&id_convention='.$id_convention.'&limit='.$limit.'&action=annuler_recherche" class="button" >Annuler</a>';


			print '</td></tr>';
			$adresse = "";
				$i = $arret;
			while ($i < $num){
				$class = "impair";
				if($i%2 == 0)
					$class = "pair";
				print '<tr class="pair">';
				print '<td align="center" >'.$obj_liste[$i]->lastname.'</td>';
				print '<td align="center" >'.$obj_liste[$i]->firstname.'</td>';

				$sql_sal = "SELECT rowid, matricule, fk_user FROM ".MAIN_DB_PREFIX."salarie WHERE fk_user=".$obj_liste[$i]->rowid;
				$res_sal = $db->query($sql_sal);

				if($res_sal)
					$salarie = $db->fetch_object($res_sal);

				print '<td align="center" >'.$salarie->matricule.'</td>';
				print '<td align="center" >'.$societe->nom.'</td>';

				//calcul de l'anciennete
				$anciennete = 0;
				if(!empty($obj_liste[$i]->dateemployment)){
					$covSql1 = "SELECT (MONTH(NOW()) - MONTH(dateemployment)) AS mois, (YEAR(NOW()) - YEAR(dateemployment)) AS annee FROM ".MAIN_DB_PREFIX."user Where rowid=".$obj_liste[$i]->rowid;
					$result1 = $db->query($covSql1);//= $db->query($covSql);
					$obj1 = $db->fetch_object($result1);
					if($obj1){
						$anciennete = $obj1->annee;
						if($obj1->mois < 0){
						if($anciennete > 0)
								$anciennete -= 1;
							else $anciennete = 0;  
						}     
					}
					print '<td align="center">'.$anciennete.'</td>';

				}else print '<td align="center" style="padding: 10px;">0</td>';
				print '<td align="center" ><a href="../../user/card.php?id='.$obj_liste[$i]->rowid.'&save_lastsearch_values=1"><button class="button">Modifier</button></a></td>';

				print '</tr>';
				if($i!= 0 && (($i+1)%$limit) == 0){
					$arret = $i;
					$i = $num;
				}else
					$i ++;

			}
			if(count($obj_liste) ==0){
				print "<tr><td colspan='5' align='center'><style='align:center;'>Aucun salarié</td></tr>";
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
				$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=0&nbpage=1&action=recherche&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_matricule=".$recherche_matricule."&recherche_anciennete=".$recherche_anciennete."' style='padding: 5px'><b>Debut</b>    </a>&nbsp;&nbsp;";
			else if(1 < ($nb+1))
			$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=0&nbpage=1&action=recherche&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_matricule=".$recherche_matricule."&recherche_anciennete=".$recherche_anciennete."' style='padding: 5px'><b>Debut</b>    </a>&nbsp;&nbsp;";

		
		if($arret > $limit){

			
			if($nb_page-3>=0)
				$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=".($limit*($nb_page-3))."&nbpage=".($nb_page-2)."&action=recherche&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_matricule=".$recherche_matricule."&recherche_anciennete=".$recherche_anciennete."' style='padding: 5px'><b>".($nb_page -2)."</b></a>&nbsp;&nbsp;";

			if($nb_page-2>=0)
						$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=".($limit*($nb_page-2))."&nbpage=".($nb_page-1)."&action=recherche&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_matricule=".$recherche_matricule."&recherche_anciennete=".$recherche_anciennete."' style='padding: 5px'><b>".($nb_page-1)."</b></a>&nbsp;&nbsp;";
			
			
			if($nb_page-1>=0)
					$page_link .= "<a style='background-color: yellow;' href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=".($limit*($nb_page-1))."&nbpage=".($nb_page)."&action=recherche&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_matricule=".$recherche_matricule."&recherche_anciennete=".$recherche_anciennete."' style='padding: 5px'><b>".($nb_page)."</b></a>&nbsp;&nbsp;";

		

			
				if(	(($nb_page+1) <= ($nb)))
					$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=".($limit*$nb_page)."&nbpage=".($nb_page+1)."&action=recherche&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_matricule=".$recherche_matricule."&recherche_anciennete=".$recherche_anciennete."' style='padding: 5px'><b>".($nb_page + 1)."</b></a>&nbsp;&nbsp;";

			
				if((($nb_page+2) <= ($nb)))
					$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=".($limit*($nb_page +1))."&nbpage=".($nb_page+2)."&action=recherche&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_matricule=".$recherche_matricule."&recherche_anciennete=".$recherche_anciennete."' style='padding: 5px'><b>".($nb_page + 2)."</b></a>&nbsp;&nbsp;";
					
				
				if((($nb_page+3) <= ($nb)))
					$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=".($limit*($nb_page+2))."&nbpage=".($nb_page+3)."&action=recherche&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_matricule=".$recherche_matricule."&recherche_anciennete=".$recherche_anciennete."' style='padding: 5px'><b>".($nb_page + 3)."</b></a>&nbsp;&nbsp;";

					


		}else{

			
				if( 1 <= ($nb))
					
					$page_link .= "<a style='background-color: yellow;' href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=0&nbpage=1&action=recherche&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_matricule=".$recherche_matricule."&recherche_anciennete=".$recherche_anciennete."' style='padding: 5px'><b>1</b></a>&nbsp;&nbsp;";
			
			
				if(2 <= ($nb))
					
					$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=".$limit."&nbpage=2&action=recherche&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_matricule=".$recherche_matricule."&recherche_anciennete=".$recherche_anciennete."' style='padding: 5px'><b>2</b></a>&nbsp;&nbsp;";
			
			
				if(3 <= ($nb))
					
					$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=".($limit*2)."&nbpage=3&action=recherche&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_matricule=".$recherche_matricule."&recherche_anciennete=".$recherche_anciennete."' style='padding: 5px'><b>3</b></a>&nbsp;&nbsp;";
				
				if(4 <= ($nb))
					
					$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=".($limit*3)."&nbpage=4&action=recherche&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_matricule=".$recherche_matricule."&recherche_anciennete=".$recherche_anciennete."' style='padding: 5px'><b>4</b></a>&nbsp;&nbsp;";

				if(5 <= ($nb))
					
					$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=".($limit*4)."&nbpage=5&action=recherche&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_matricule=".$recherche_matricule."&recherche_anciennete=".$recherche_anciennete."' style='padding: 5px'><b>5</b></a>&nbsp;&nbsp;";



		}
		if($nb_page != ($nb)  )
				$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=".($limit*($nb-1))."&nbpage=".($nb)."&action=recherche&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_matricule=".$recherche_matricule."&recherche_anciennete=".$recherche_anciennete."' style='padding: 5px'>      <b>Fin</b></a>&nbsp;&nbsp;";

		
		/*if($limit == ($arret +1))
			$page_link .= "<a style='background-color: yellow; padding: 5px' href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=".(($arret +1) -$limit)."'>".((int)(($arret+1)/$limit))."</a>";
		else $page_link .= ($arret +1 - $limit)>?"<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=".(($arret +1) -$limit)."' style='padding: 5px'><b>".((int)(($arret+1)/$limit))."</b></a>&nbsp;&nbsp;":"";
		$page_link .= ((($arret +1)*2 -$limit) < $num)?"<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=".(($arret +1)*2 -$limit)."' style='padding: 5px'><b>".(((int)((($arret+1)*2)/$limit)))."</b></a>&nbsp;&nbsp;":"";
		$page_link .= ((($arret +1)*3 -$limit) < $num)?"<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=".(($arret +1)*3 -$limit)."' style='padding: 5px'><b>".(((int)((($arret+1)*3)/$limit)))."</b></a>&nbsp;&nbsp;":"";
		$page_link .= "<a style='padding: 5px' href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=".(($arret +1)*2 -$limit)."'><b>></b>&nbsp;&nbsp;</a>";
	*/}


	$db->free();