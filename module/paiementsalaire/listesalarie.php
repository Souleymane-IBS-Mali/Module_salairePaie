<?php
require '../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/custom/paiementsalaire/lib/paiementsalaire.lib.php';

llxHeader("", "Paiement | Salaire");
//Titre 
//print '<hr>';
/*$mail_sql = "UPDATE ".MAIN_DB_PREFIX."salarie SET date_anciennete=NULL WHERE rowid >= 311";191124023509
            $res_mail = $db->query($mail_sql);
			if($res_mail)
			print "ok";
		else print "non";*/
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

$limit = GETPOST('limit','alpha')?:20;
$arret = GETPOST('arret','int')?:0;
$nb_page = GETPOST('nbpage','int')?:1;

verification_suppression($db);

$recherche_nom = "";
			$recherche_prenom = "";
			$recherche_matricule = "";
			$recherche_societe = 0;
			$recherche_date_entree = 0;
			if($action = "recherche"){
				$recherche_nom = GETPOST("recherche_nom", "alpha");
				$recherche_prenom = GETPOST("recherche_prenom", "alpha");
				$recherche_matricule = GETPOST("recherche_matricule");
				$recherche_societe = GETPOST("recherche_societe", "int");
				$recherche_date_entree = GETPOST("recherche_date_entree", "int");
			}

			$categorie = GETPOST('categorie', 'alpha');
			$fonction = GETPOST('fonction');
			$anciennete_case = GETPOST('anciennete');
			$solde_conge = GETPOST('solde_conge');

$trouve = false;
$obj_liste = array();
//$ordre_id = array();


	$sql = "SELECT u.rowid, u.lastname, u.firstname, u.dateemployment, u.job, ue.fk_object, ue.egp, soc.rowid as id_societe, soc.nom, sce.fk_object, sce.conv FROM ".MAIN_DB_PREFIX."user as u";
	$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."user_extrafields as ue ON u.rowid=ue.fk_object";
	$sql .= ' LEFT JOIN '.MAIN_DB_PREFIX.'societe as soc ON soc.rowid=ue.egp';
	$sql .= ' LEFT JOIN '.MAIN_DB_PREFIX.'societe_extrafields as sce ON soc.rowid=sce.fk_object';
	if($user->id != 1)
        $sql .= " WHERE soc.rowid IN ".$array_id_soc." AND sce.grp=1";
    else   
        $sql .= " WHERE sce.grp=1";


	if(!empty(GETPOST("recherche_nom", "alpha"))){
		$sql .= " AND (u.lastname LIKE '%".GETPOST("recherche_nom")."%'";
		$sql .= " OR u.firstname LIKE '%".GETPOST("recherche_nom")."%')";
	}
	

	if(!empty(GETPOST("recherche_prenom", "alpha"))){
		$sql .= " AND (u.firstname LIKE '%".GETPOST("recherche_prenom")."%'";
		$sql .= " OR u.lastname LIKE '%".GETPOST("recherche_prenom")."%')";
	}
	if(!empty(GETPOST("recherche_societe", "int"))){
		$sql .= " AND soc.rowid=".GETPOST("recherche_societe");
	}
	if(GETPOST("recherche_date_entree", "int")!=''){
		$annee = ((int)date("Y")-GETPOST("recherche_date_entree"));
		$mois = (int)date("m");
		$jour = (int)date("d");
		$sql .= " AND YEAR(u.dateemployment)=".$annee." AND MONTH(u.dateemployment)<=".$mois." AND DAY(u.dateemployment)<=".$jour;
	}

	$sql .= " ORDER BY u.lastname";
	$result = $db->query($sql);
	if($result){
		$num = $db->num_rows($result);
		if($num > 0){
			$a = 0;
			while ($a < $num) {
				$obj_liste[] = $db->fetch_object($result);
				//$ordre_id[] = $obj_liste[count($obj_liste)-1]->rowid;	
				$a ++;
			}
			
			
		}
		//$db->free($result);

	}

	print load_fiche_titre($langs->trans("Liste de tous les salariés(".count($obj_liste).")"), '', '');

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
				if($limit == 'tout')
					$limit = $num;
				print "<mark><b>".(GETPOST("nbpage","int")?:1)."</b></mark>/<mark><b>".(((int)($num%$limit))==0?((int)($num/$limit)):((int)($num/$limit)+1))."</b></mark>";
				print '<script type="text/javascript">
				var convention = document.getElementById("limit");
				convention.addEventListener("change", function () {
					var limit = convention.value;
					window.location.href = "'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenusociete&id_societe='.$id_societe.'&id_convention='.$id_convention.'&limit="+limit+"&action=rechercher&recherche_societe='.$recherche_societe.'&recherche_nom='.$recherche_nom.'&recherche_prenom='.$recherche_prenom.'&recherche_matricule='.$recherche_matricule.'&recherche_date_entree='.$recherche_date_entree.'";
				},
				false,
				);
				</script>';
			print "</select>";
			print "</div>";

	
			print "<br>";
			print '<div>';
			print '<table style="width: 100%;" class="tagtable liste">';
			$largeur = "20%";
				$nb_col = 0;
				if($categorie == "on")
					$nb_col ++;
				
				if($fonction == "on")
					$nb_col ++;

				if($solde_conge == "on")
					$nb_col ++;

				if($nb_col == 1){ //une colonne plus les 6 affichées par defaut = 7;
					$largeur = "14%";

				}elseif($nb_col == 2){ //nombre de colonne = 8
					$largeur = "12%";

				}elseif($nb_col == 3){
					$largeur = "11%";

				}elseif($nb_col == 4){
					$largeur = "10%";

				}
			
			//Les champs des Titres du tableau
			print '<tr  class="liste_titre" >';
			print '<td align="center"style="padding: 5px; width: '.$largeur.';">
			<input style="padding:10px" type="text" Placeholder="Matricule" value="'.$recherche_matricule.'" name="recherche_matricule" ></td>';
			print '<td align="center"style="padding: 5px; width: '.$largeur.';">
			<input style="padding:10px" type="text" Placeholder="Nom" value="'.$recherche_nom.'" name="recherche_nom" ></td>';
			print '<td align="center"style="padding: 5px; width: '.$largeur.';">
			<input style="padding:10px" type="text" Placeholder="Prenom" value="'.$recherche_prenom.'" name="recherche_prenom" ></td>';
			
			print '<td align="center"style="padding: 5px; width: '.$largeur.';">
			<select name="recherche_societe" ><option value=0></option>';
			$sql_soc = "SELECT sc.rowid, sc.nom, sce.rowid as r2, sce.fk_object FROM ".MAIN_DB_PREFIX."societe as sc";
			$sql_soc .= " LEFT JOIN ".MAIN_DB_PREFIX."societe_extrafields as sce ON sc.rowid=sce.fk_object";
			if($user->id != 1)
				$sql_soc .= " WHERE sc.rowid IN ".$array_id_soc." AND sce.grp=1";
			else   
				$sql_soc .= " WHERE sce.grp=1";
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
			print '<td align="center"style="padding: 5px; width: '.$largeur.';">
			<input style="padding:10px" type="date" value="'.$recherche_date_entree.'" name="recherche_date_entree" ></td>';

			//Rien dans la première ligne du titre pour les colonnes dynamiques 
			if($categorie == "on")
				print '<td align="center" style=" width: '.$largeur.';"></td>';
			
			if($fonction == "on")
				print '<td align="center" style=" width: '.$largeur.';"></td>';
			
			if($anciennete_case == "on")
				print '<td align="center" style=" width: '.$largeur.';"></td>';

			if($solde_conge == "on")
				print '<td align="center" style=" width: '.$largeur.';"></td>';

			print '<td align="center"style="padding: 5px; width: '.$largeur.';">';
			print '<input type="submit" class="button" value="Rechercher" >';
			print "</form>";
			print '<br>	<a href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=salarie" class="button" >Annuler</a>';
			print '</td></tr>';
			

			//Les titres du tableau
			print '<tr  class="liste_titre" >';
			print '<td align="center"style="padding: 5px; width: '.$largeur.';"><label>Matricule</label></td>';
			print '<td align="center"style="padding: 5px; width: '.$largeur.';"><label>Nom</label></td>';
			print '<td align="center"style="padding: 5px; width: '.$largeur.';"><label>Prenom</label></td>';

			print '<td align="center"style="padding: 5px; width: '.$largeur.';"><label>Société</label></td>';
			print '<td align="center"style="padding: 5px; width: '.$largeur.';"><label>Date entrée</label></td>';

			$checked_categ = "";
				$checked_fonction = "";
				$checked_solde = "";
				$checked_anc = "";

				if($categorie == "on"){
					print '<td align="center" style=" width: '.$largeur.';">Catégorie</td>';
					$checked_categ = "checked";
				}

				if($fonction == "on"){
					print '<td align="center" style=" width: '.$largeur.';">Fonction</td>';
					$checked_fonction = "checked";
				}

				if($anciennete_case == "on"){
					print '<td align="center" style=" width: '.$largeur.';">Anciennété</td>';
					$checked_anc = "checked";
				}

				if($solde_conge == "on"){
					print '<td align="center" style=" width: '.$largeur.';">Solde congé</td>';
					$checked_solde = "checked";
				}

				//Les colonnes dynamiques
				print '<td align="center" colspan="2"><label><span id="colonne_plus">'.img_picto('Ajouter ou enlever des colonnes', 'list').'</span></label>';
				$url = $_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenusociete&id_societe='.$id_societe.'&recherche_societe='.$recherche_societe.'&limit='.$limit.'&recherche_nom='.$recherche_nom.'&recherche_prenom='.$recherche_prenom.'&recherche_matricule='.$recherche_matricule.'&date_entree='.$date_entree;
				print '<form name="ajouter_colonne" method="POST" action="'.$url.'">
				<div id="colonne" style="display:flex; flex-direction:column; position: absolute; border: solid 1px white; border-radius:5px; background-color:white; text-align:left; padding: 10px; width: 8%; box-shadow: 4px 4px 10px; margin-left: 80px">';
				print '<input type="hidden" name="token" value="'.newToken().'">';
				print '<input type="hidden" name="action" value="recherche">';
				print '<div><input type="checkbox" name="categorie" id="categorie" '.$checked_categ.'><label for="categorie">Catégorie</label></div>';
				print '<div><input type="checkbox" name="fonction" id="fonction" '.$checked_fonction.'><label for="fonction">Fonction</label></div>';
				print '<div><input type="checkbox" name="anciennete" id="anciennete" '.$checked_anc.'><label for="anciennete">Anciennété</label></div>';
				print '<div><input type="checkbox" name="solde_conge" id="solde_conge" '.$checked_solde.'><label for="solde_conge">Solde congé</label></div>';
				print '<div><input class="button" type="submit" name="valider" value="Valider"></div>';
				print '</div></form>';
				print '</td>';

			print '</td></tr>';
			print '<script type="text/javascript">
			var convention = document.getElementById("limit");
			var colonne_plus = document.getElementById("colonne_plus");
			var colonne = document.getElementById("colonne");

			const categorie = document.getElementById("categorie");
			const fonction = document.getElementById("fonction");
			const solde_conge = document.getElementById("solde_conge");
			const anciennete = document.getElementById("anciennete");


			colonne.style.display = "none";
			colonne_plus.addEventListener("click", function () {
				if(colonne.style.display == "inline" || colonne.style.display == "block"){
					colonne.style.display = "none";
				}else{
					colonne.style.display = "block";
				}
			},
			false,
			);


			</script>';

			$adresse = "";
			$num = count($obj_liste);
				$i = $arret;
			$verif = false;
			$tab_verif = array();
			if(!empty(GETPOST("recherche_matricule", "alpha"))){
				$sql_sal = "SELECT rowid, matricule, fk_user, date_anciennete FROM ".MAIN_DB_PREFIX."salarie WHERE";
				$sql_sal .= " matricule LIKE '%".GETPOST("recherche_matricule", "alpha")."%'";
				$res_sal = $db->query($sql_sal);
				$num_sal = $db->num_rows($res_sal);
				$s = 0;
				while ($s < $num_sal){
					$salarie = $db->fetch_object($res_sal);
					$tab_verif[] = $salarie->fk_user;
					$s ++;
				}
				$verif = true;
			}

			while ($i < $num){
				if($verif){
					if(in_array($obj_liste[$i]->rowid, $tab_verif)){
						$sql_sal = "SELECT rowid, matricule, fk_categorie, fk_echelon, fk_user FROM ".MAIN_DB_PREFIX."salarie WHERE fk_user=".$obj_liste[$i]->rowid;
						$res_sal = $db->query($sql_sal);
						
						$res_sal = $db->query($sql_sal);
						$num_sal = $db->num_rows($res_sal);

						if($res_sal)
							$salarie = $db->fetch_object($res_sal);
						$class = "impair";
						if($i%2 == 0)
							$class = "pair";
							print '<tr class="'.$class.'">';
							print '<td align="center" style="padding: 0px;"><a href="./onglets/salarie_information.php?mainmenu=paiementsalaire&leftmenu=salarie&fk_salarie='.$salarie->rowid.'&id_societe='.$obj_liste[$i]->id_societe.'&id='.$obj_liste[$i]->rowid.'&id_convention='.$obj_liste[$i]->conv.'&action=detail">'.$salarie->matricule.'</a></td>';
							print '<td align="center" style="padding: 0px;"><a href="./onglets/salarie_information.php?mainmenu=paiementsalaire&leftmenu=salarie&fk_salarie='.$salarie->rowid.'&id_societe='.$obj_liste[$i]->id_societe.'&id='.$obj_liste[$i]->rowid.'&id_convention='.$obj_liste[$i]->conv.'&action=detail">'.$obj_liste[$i]->lastname.'</a></td>';
							print '<td align="center" style="padding: 0px;"><a href="./onglets/salarie_information.php?mainmenu=paiementsalaire&leftmenu=salarie&fk_salarie='.$salarie->rowid.'&id_societe='.$obj_liste[$i]->id_societe.'&id='.$obj_liste[$i]->rowid.'&id_convention='.$obj_liste[$i]->conv.'&action=detail">'.$obj_liste[$i]->firstname.'</a></td>';

						print '<td align="center" >'.$obj_liste[$i]->nom.'</td>';

						//calcul de l'anciennete
						$covSql = "SELECT date_anciennete FROM ".MAIN_DB_PREFIX."salarie WHERE rowid=".$salarie->rowid." AND date_anciennete IS NOT NULL";
						$result = $db->query($covSql);//= $db->query($covSql);
						if($db->num_rows($result) > 0){
							$obj = $db->fetch_object($result);

						}else{

							$covSql = "SELECT dateemployment as date_anciennete FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$obj_liste[$i]->rowid." AND dateemployment IS NOT NULL";
								$result = $db->query($covSql);//= $db->query($covSql);
								$obj = $db->fetch_object($result);

						}

						$anciennete = 0;
						if($obj){

							$date_donnee = new DateTime($obj->date_anciennete); // Date donnée
							$aujourdhui = new DateTime(); // Date d'aujourd'hui

							$interval = $date_donnee->diff($aujourdhui);
							$jours = $interval->days;
							$anciennete =  floor($jours/365);

							

						}

							print '<td align="center">'.$obj_liste[$i]->dateemployment.'</td>';


							//Valeur des champs dynamiques
							if($categorie == "on"){
								$categorie_Sql = "SELECT code_categorie FROM ".MAIN_DB_PREFIX."dcategories WHERE rowid=".$salarie->fk_categorie;
								$categorie_Result = $db->query($categorie_Sql);
								$categorie_Salarie = $db->fetch_object($categorie_Result);
								$categ = $categorie_Salarie->code_categorie?:"N/A";
	
								if($obj_salarie->fk_echelon !=0){
									$echelon_Sql = "SELECT libelle FROM ".MAIN_DB_PREFIX."echelon where rowid=".($salarie->fk_echelon?:0);
									$echelon_Result = $db->query($echelon_Sql);
									$echelon_Salarie = $db->fetch_object($echelon_Result);
									$categ .= "==>".$echelon_Salarie->libelle;
								}
								print '<td align="center" style="padding: 5px; width: 20%;">'.$categ.'</td>';
							}
	
							if($fonction == "on" && $obj_liste[$i]->dateemployment)
								print '<td align="center" style="padding: 5px; width: 20%;">'.$obj_liste[$i]->job.'</td>';
							
							if($anciennete_case == "on")
								print '<td align="center">'.$anciennete.'an(s)</td>';
	
							
	
							if($solde_conge == "on"){
								$date_donnee = new DateTime($obj_liste[$i]->dateemployment); // Date donnée
								$aujourdhui = new DateTime(); // Date d'aujourd'hui
	
								$interval = $date_donnee->diff($aujourdhui);
								$jours = $interval->days;
								$nb_conge_eff = $jours;
								while ($jours > 365) {
									$jours = $jours - 365;
								}
								$solde =  (int)(floor($jours/30)*2.5);
								print '<td align="center" style="padding: 5px; width: 20%;">'.$solde.'</td>';
							}

						print '<td align="center" ><a href="../user/card.php?id='.$obj_liste[$i]->rowid.'&save_lastsearch_values=1"><button class="button">Modifier</button></a></td>';
						print '</tr>';
					}
				}else{
					$sql_sal = "SELECT rowid, matricule, fk_categorie, fk_echelon, fk_user, date_anciennete FROM ".MAIN_DB_PREFIX."salarie WHERE fk_user=".$obj_liste[$i]->rowid;
					$res_sal = $db->query($sql_sal);
					
					$res_sal = $db->query($sql_sal);
					$num_sal = $db->num_rows($res_sal);

					if($res_sal)
						$salarie = $db->fetch_object($res_sal);
					$class = "impair";
					if($i%2 == 0)
						$class = "pair";
						print '<tr class="'.$class.'">';
						print '<td align="center" style="padding: 0px;"><a href="./onglets/salarie_information.php?mainmenu=paiementsalaire&leftmenu=salarie&fk_salarie='.$salarie->rowid.'&id_societe='.$obj_liste[$i]->id_societe.'&id='.$obj_liste[$i]->rowid.'&id_convention='.$obj_liste[$i]->conv.'&action=detail">'.$salarie->matricule.'</a></td>';
						print '<td align="center" style="padding: 0px;"><a href="./onglets/salarie_information.php?mainmenu=paiementsalaire&leftmenu=salarie&fk_salarie='.$salarie->rowid.'&id_societe='.$obj_liste[$i]->id_societe.'&id='.$obj_liste[$i]->rowid.'&id_convention='.$obj_liste[$i]->conv.'&action=detail">'.$obj_liste[$i]->lastname.'</a></td>';
						print '<td align="center" style="padding: 0px;"><a href="./onglets/salarie_information.php?mainmenu=paiementsalaire&leftmenu=salarie&fk_salarie='.$salarie->rowid.'&id_societe='.$obj_liste[$i]->id_societe.'&id='.$obj_liste[$i]->rowid.'&id_convention='.$obj_liste[$i]->conv.'&action=detail">'.$obj_liste[$i]->firstname.'</a></td>';


					print '<td align="center" >'.$obj_liste[$i]->nom.'</td>';

					//calcul de l'anciennete
					//calcul de l'anciennete
					$covSql = "SELECT date_anciennete FROM ".MAIN_DB_PREFIX."salarie WHERE rowid=".$salarie->rowid." AND date_anciennete IS NOT NULL";
					$result = $db->query($covSql);//= $db->query($covSql);

					if($db->num_rows($result) > 0){
						$obj = $db->fetch_object($result);

					}else{

						$covSql = "SELECT dateemployment as date_anciennete FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$obj_liste[$i]->rowid." AND dateemployment IS NOT NULL";
						$result = $db->query($covSql);//= $db->query($covSql);
						$obj = $db->fetch_object($result);

					}

					$anciennete = 0;
					if($obj){

						$date_donnee = new DateTime($obj->date_anciennete); // Date donnée
						$aujourdhui = new DateTime(); // Date d'aujourd'hui

						$interval = $date_donnee->diff($aujourdhui);
						$jours = $interval->days;
						$anciennete =  floor($jours/365);

						

					}
						print '<td align="center">'.$obj_liste[$i]->dateemployment.'</td>';

						//Valeurs des champs dynamiques
						if($categorie == "on"){
							$categorie_Sql = "SELECT code_categorie FROM ".MAIN_DB_PREFIX."dcategories WHERE rowid=".$salarie->fk_categorie;
							$categorie_Result = $db->query($categorie_Sql);
							$categorie_Salarie = $db->fetch_object($categorie_Result);
							$categ = $categorie_Salarie->code_categorie?:"N/A";

							if($obj_salarie->fk_echelon !=0){
								$echelon_Sql = "SELECT libelle FROM ".MAIN_DB_PREFIX."echelon where rowid=".($salarie->fk_echelon?:0);
								$echelon_Result = $db->query($echelon_Sql);
								$echelon_Salarie = $db->fetch_object($echelon_Result);
								$categ .= "==>".$echelon_Salarie->libelle;
							}
							print '<td align="center" style="padding: 5px; width: 20%;">'.$categ.'</td>';
						}

						if($fonction == "on" && $obj_liste[$i]->dateemployment)
							print '<td align="center" style="padding: 5px; width: 20%;">'.$obj_liste[$i]->job.'</td>';
						
						if($anciennete_case == "on")
							print '<td align="center">'.$anciennete.'an(s)</td>';

						

						if($solde_conge == "on"){
							$date_donnee = new DateTime($obj_liste[$i]->dateemployment); // Date donnée
							$aujourdhui = new DateTime(); // Date d'aujourd'hui

							$interval = $date_donnee->diff($aujourdhui);
							$jours = $interval->days;
							$nb_conge_eff = $jours;
							while ($jours > 365) {
								$jours = $jours - 365;
							}
							$solde =  (int)(floor($jours/30)*2.5);
							print '<td align="center" style="padding: 5px; width: 20%;">'.$solde.'</td>';
						}

					print '<td align="center" ><a href="../user/card.php?id='.$obj_liste[$i]->rowid.'&save_lastsearch_values=1"><button class="button">Modifier</button></a></td>';

					print '</tr>';
				}
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
				$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=0&nbpage=1&action=recherche&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_matricule=".$recherche_matricule."&recherche_date_entree=".$recherche_date_entree."&recherche_societe=".$recherche_societe."' style='padding: 5px'><b>Debut</b>    </a>&nbsp;&nbsp;";
			else if(1 < ($nb+1))
			$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=0&nbpage=1&action=recherche&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_matricule=".$recherche_matricule."&recherche_date_entree=".$recherche_date_entree."&recherche_societe=".$recherche_societe."' style='padding: 5px'><b>Debut</b>    </a>&nbsp;&nbsp;";

		
		if($arret > $limit){

			
			if($nb_page-3>=0)
				$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=".($limit*($nb_page-3))."&nbpage=".($nb_page-2)."&action=recherche&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_matricule=".$recherche_matricule."&recherche_date_entree=".$recherche_date_entree."&recherche_societe=".$recherche_societe."' style='padding: 5px'><b>".($nb_page -2)."</b></a>&nbsp;&nbsp;";

			if($nb_page-2>=0)
						$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=".($limit*($nb_page-2))."&nbpage=".($nb_page-1)."&action=recherche&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_matricule=".$recherche_matricule."&recherche_date_entree=".$recherche_date_entree."&recherche_societe=".$recherche_societe."' style='padding: 5px'><b>".($nb_page-1)."</b></a>&nbsp;&nbsp;";
			
			
			if($nb_page-1>=0)
					$page_link .= "<a style='background-color: yellow;' href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=".($limit*($nb_page-1))."&nbpage=".($nb_page)."&action=recherche&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_matricule=".$recherche_matricule."&recherche_date_entree=".$recherche_date_entree."&recherche_societe=".$recherche_societe."' style='padding: 5px'><b>".($nb_page)."</b></a>&nbsp;&nbsp;";

		

			
				if(	(($nb_page+1) <= ($nb)))
					$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=".($limit*$nb_page)."&nbpage=".($nb_page+1)."&action=recherche&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_matricule=".$recherche_matricule."&recherche_date_entree=".$recherche_date_entree."&recherche_societe=".$recherche_societe."' style='padding: 5px'><b>".($nb_page + 1)."</b></a>&nbsp;&nbsp;";

			
				if((($nb_page+2) <= ($nb)))
					$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=".($limit*($nb_page +1))."&nbpage=".($nb_page+2)."&action=recherche&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_matricule=".$recherche_matricule."&recherche_date_entree=".$recherche_date_entree."&recherche_societe=".$recherche_societe."' style='padding: 5px'><b>".($nb_page + 2)."</b></a>&nbsp;&nbsp;";
					
				
				if((($nb_page+3) <= ($nb)))
					$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=".($limit*($nb_page+2))."&nbpage=".($nb_page+3)."&action=recherche&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_matricule=".$recherche_matricule."&recherche_date_entree=".$recherche_date_entree."&recherche_societe=".$recherche_societe."' style='padding: 5px'><b>".($nb_page + 3)."</b></a>&nbsp;&nbsp;";

					


		}else{

			
				if( 1 <= ($nb))
					
					$page_link .= "<a style='background-color: yellow;' href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=0&nbpage=1&action=recherche&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_matricule=".$recherche_matricule."&recherche_date_entree=".$recherche_date_entree."&recherche_societe=".$recherche_societe."' style='padding: 5px'><b>1</b></a>&nbsp;&nbsp;";
			
			
				if(2 <= ($nb))
					
					$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=".$limit."&nbpage=2&action=recherche&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_matricule=".$recherche_matricule."&recherche_date_entree=".$recherche_date_entree."&recherche_societe=".$recherche_societe."' style='padding: 5px'><b>2</b></a>&nbsp;&nbsp;";
			
			
				if(3 <= ($nb))
					
					$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=".($limit*2)."&nbpage=3&action=recherche&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_matricule=".$recherche_matricule."&recherche_date_entree=".$recherche_date_entree."&recherche_societe=".$recherche_societe."' style='padding: 5px'><b>3</b></a>&nbsp;&nbsp;";
				
				if(4 <= ($nb))
					
					$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=".($limit*3)."&nbpage=4&action=recherche&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_matricule=".$recherche_matricule."&recherche_date_entree=".$recherche_date_entree."&recherche_societe=".$recherche_societe."' style='padding: 5px'><b>4</b></a>&nbsp;&nbsp;";

				if(5 <= ($nb))
					
					$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=".($limit*4)."&nbpage=5&action=recherche&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_matricule=".$recherche_matricule."&recherche_date_entree=".$recherche_date_entree."&recherche_societe=".$recherche_societe."' style='padding: 5px'><b>5</b></a>&nbsp;&nbsp;";



		}
		if($nb_page != ($nb)  )
				$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=".($limit*($nb-1))."&nbpage=".($nb)."&action=recherche&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_matricule=".$recherche_matricule."&recherche_date_entree=".$recherche_date_entree."&recherche_societe=".$recherche_societe."' style='padding: 5px'>      <b>Fin</b></a>&nbsp;&nbsp;";

		
		/*if($limit == ($arret +1))
			$page_link .= "<a style='background-color: yellow; padding: 5px' href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=".(($arret +1) -$limit)."'>".((int)(($arret+1)/$limit))."</a>";
		else $page_link .= ($arret +1 - $limit)>?"<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=".(($arret +1) -$limit)."' style='padding: 5px'><b>".((int)(($arret+1)/$limit))."</b></a>&nbsp;&nbsp;":"";
		$page_link .= ((($arret +1)*2 -$limit) < $num)?"<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=".(($arret +1)*2 -$limit)."' style='padding: 5px'><b>".(((int)((($arret+1)*2)/$limit)))."</b></a>&nbsp;&nbsp;":"";
		$page_link .= ((($arret +1)*3 -$limit) < $num)?"<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=".(($arret +1)*3 -$limit)."' style='padding: 5px'><b>".(((int)((($arret+1)*3)/$limit)))."</b></a>&nbsp;&nbsp;":"";
		$page_link .= "<a style='padding: 5px' href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=".(($arret +1)*2 -$limit)."'><b>></b>&nbsp;&nbsp;</a>";
	*/}
	print $page_link.'</span>';


