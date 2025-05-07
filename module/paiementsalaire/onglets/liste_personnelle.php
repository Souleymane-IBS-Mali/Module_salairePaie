<?php
require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/custom/paiementsalaire/lib/paiementsalaire.lib.php';
$sql = "SELECT * FROM ".MAIN_DB_PREFIX."const";
$result = $db->query($sql);
/*
if($result){
  $i = 0;
  $num = $db->num_rows($result);
  while ($i < $num){
  $societe = $db->fetch_object($result);
  print $societe->name.'----'.$societe->entity.'-----'.$societe->value.'-----'.$societe->type.'-----'.$societe->visible.'-----'.$societe->note.'-----'.$societe->tms.'<br>';
  $i ++;
  }
}
*/
llxHeader("", "Paiement | Salaire");
//Titre 
print load_fiche_titre($langs->trans("Liste des personnelles de la sociétés"), '', '');
//print '<hr>';

$id_societe = GETPOST('id_societe','int');
$id_convention = GETPOST('id_convention','int');
$limit = GETPOST('limit','alpha')?:20;
$arret = GETPOST('arret','int')?:0;
$nb_page = GETPOST('nbpage','int')?:1;
$action = GETPOST('action', 'alpha');

//Par defaut tous les salariés ont travaillé le maximum de jours du mois en cours
salarie_nb_jour($db, $id_societe);
//--------------------------------
if($id_convention){
	$head = paiementsalaireSocieteHead($id_societe, $id_convention);
	print dol_get_fiche_head($head, 'liste', "", -1, '');

	if($user->rights->paiementsalaire->salarie->read){
		
	$soc_sql = "SELECT * FROM ".MAIN_DB_PREFIX."societe WHERE rowid=".$id_societe;
	$soc_res = $db->query($soc_sql);//= $db->query($covSql);
	$obj_soc = $db->fetch_object($soc_res);
	$obj_soc->name = $obj_soc->nom;
	$obj_soc->element = "societe";			
	$obj_soc->conv = $id_convention;

	societe_preview_next($db, $id_societe, $obj_soc);
	entete_societe($obj_soc, 'societe');

	$head2 = liste_salarie_SocieteHead($id_societe, $id_convention);
	print dol_get_fiche_head($head2, 'liste_salarie', "", -1, '');

	$recherche_nom = "";
				$recherche_prenom = "";
				$recherche_matricule = "";
				$date_entree = 0;
				if($action == "recherche"){
					$recherche_nom = GETPOST("recherche_nom", "alpha");
					$recherche_prenom = GETPOST("recherche_prenom", "alpha");
					$recherche_matricule = GETPOST("recherche_matricule", "alpha");
					$date_entree = GETPOST("date_entree", "int");
				}
	$tri = GETPOST('tri', 'alpha');

	$categorie = GETPOST('categorie', 'alpha');
	$fonction = GETPOST('fonction');
	$anciennete_case = GETPOST('anciennete');
	$solde_conge = GETPOST('solde_conge');

	$trouve = false;
	$obj_liste = array();
	//$ordre_id = array();

	//Archivage du salarié
	$message = "";
	if($action == "archiver"){
		$rowid = GETPOST('fk_salarie', 'int');
		$id = GETPOST('id', 'int');

		if(!empty($rowid)){
			$sql_edit = "UPDATE ".MAIN_DB_PREFIX."salarie SET archiver='oui' WHERE rowid=".$rowid;
			if($db->query($sql_edit)){
				$message = "Salarie archivé avec succès";

				$sql_select = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$id;
				$obj_concern = $db->fetch_object($db->query($sql_select));

				$sql_select = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$user->id;
				$obj = $db->fetch_object($db->query($sql_select));

				$action_effectue = "Archivage d'un salarié (".$obj_concern->firstname." ".$obj_concern->lastname." id_user=".$id." et fk_salarié=".$rowid.") de la société ".$obj_soc->nom;
				$sql_log = 'INSERT INTO '.MAIN_DB_PREFIX.'log (fk_user, nom, prenom, quand, action_effectue, object_concerne)';
				$sql_log .= ' VALUES('.$user->id.', "'.$obj->lastname.'","'.$obj->firstname.'",now(),"'.$action_effectue.'","Archivage")';
				$db->query($sql_log);

			}else $message = "Un problème est survenu";
		}
	}

	if(!empty(GETPOST("recherche_matricule", "alpha"))){
		
		$sql = "SELECT sal.rowid as salrowid, sal.matricule, sal.fk_user, sal.fk_categorie, sal.fk_echelon, u.rowid, u.lastname, u.firstname, u.dateemployment, u.job, ue.fk_object, ue.egp FROM ".MAIN_DB_PREFIX."salarie as sal";
		$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."user as u ON u.rowid=sal.fk_user";
		$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."user_extrafields as ue ON u.rowid=ue.fk_object";
		$sql .= " WHERE ue.egp=".$id_societe." AND ue.egp=".$id_societe." AND sal.archiver = 'non'";
		$sql .= " AND sal.matricule LIKE '%".GETPOST("recherche_matricule")."%'";
	}else{
		$sql = "SELECT sal.rowid as salrowid, sal.matricule, sal.fk_user, sal.fk_categorie, sal.fk_echelon, u.rowid, u.lastname, u.firstname, u.dateemployment, u.job, ue.fk_object, ue.egp FROM ".MAIN_DB_PREFIX."user as u";
		$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."salarie as sal ON u.rowid=sal.fk_user";
		$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."user_extrafields as ue ON u.rowid=ue.fk_object";
		$sql .= " WHERE ue.egp=".$id_societe." AND ue.egp=".$id_societe." AND (sal.archiver != 'oui' OR sal.archiver IS NULL )";
		
	}

	//Les champ de recherche

		if(!empty(GETPOST("recherche_nom", "alpha"))){
			$sql .= " AND (u.lastname LIKE '%".GETPOST("recherche_nom", "alpha")."%'";
			$sql .= " OR u.firstname LIKE '%".GETPOST("recherche_nom", "alpha")."%')";
		}
		

		if(!empty(GETPOST("recherche_prenom", "alpha"))){
			$sql .= " AND (u.firstname LIKE '%".GETPOST("recherche_prenom", "alpha")."%'";
			$sql .= " OR u.lastname LIKE '%".GETPOST("recherche_prenom", "alpha")."%')";
		}

		if(!empty(GETPOST("date_entree", "int"))){
			$annee = ((int)date("Y")-GETPOST("date_entree", "int"));
			$mois = (int)date("m");
			$jour = (int)date("d");
			$sql .= " AND YEAR(u.dateemployment)=".$annee." AND MONTH(u.dateemployment)<=".$mois;
		}elseif(!empty(GETPOST("date_entree"))){
			$sql .= " AND u.dateemployment='".GETPOST("date_entree")."'";
		}


		if($tri == 'nom'){
			$sql .= " ORDER BY u.lastname";
		}elseif($tri == 'prenom'){
			$sql .= " ORDER BY u.firstname";
		}elseif($tri == 'matricule'){
			$sql .= " ORDER BY sal.matricule";
		}elseif($tri == 'anciennete'){
			$sql .= " ORDER BY sal.date_anciennete";
		}elseif(!empty(GETPOST("recherche_matricule", "alpha"))){
			$sql .= " ORDER BY sal.matricule";
		}else
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
				
				
			}else{ 
				$zero = true;
				$num = 1;
			}
			//$db->free($result);

		}

		/*//Mise en ordre du resultat par Matricule
		$array_obj_list_ord = array();
		$array_obj_list_non_ord = array();
		$non_ordre_id = array();
		$ordre_sql = 'SELECT fk_user, matricule FROM '.MAIN_DB_PREFIX.'salarie WHERE fk_user IN (0';
		for ($i=0; $i < count($ordre_id); $i++) {
			$ordre_sql .= ', '.$ordre_id[$i]; 
		}
		$ordre_sql .= ') ORDER BY matricule';
		$res_ordre = $db->query($ordre_sql);
		if($res_ordre){
			$num_ordre = $db->num_rows($res_ordre);
			$ord = 0;
			while($ord < $num_ordre){
				
				$obj_ordre = $db->fetch_object($res_ordre);
				$non_ordre_id[] = $obj_ordre->fk_user;
				for ($i=0; $i < count($obj_liste); $i++) { 
					if($obj_ordre->fk_user == $obj_liste[$i]->rowid)
						$array_obj_list_ord[] = $obj_liste[$i];
				}
				$ord ++;
			}
		}

		for ($i=0; $i < count($obj_liste); $i++) { 
			if(!in_array($obj_liste[$i]->rowid, $non_ordre_id))
				$array_obj_list_non_ord[] = $obj_liste[$i];
		}

		$obj_liste = $array_obj_list_ord;
		for ($i=0; $i < count($array_obj_list_non_ord); $i++) { 
				$obj_liste[count($obj_liste)] = $array_obj_list_non_ord[$i];
		}
	*/
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

			if($limit == 'tout')
				$limit = $num;
			
			print "<mark><b>".(GETPOST("nbpage","int")?:1)."</b></mark>/<mark><b>".(((int)($num%$limit))==0?((int)($num/$limit)):((int)($num/$limit)+1))."</b></mark>";
					print '<script type="text/javascript">
					var convention = document.getElementById("limit");
					convention.addEventListener("change", function () {
						var limit = convention.value;
						window.location.href = "'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenusociete&id_societe='.$id_societe.'&id_convention='.$id_convention.'&limit="+limit+"&action=rechercher&recherche_nom='.$recherche_nom.'&recherche_prenom='.$recherche_prenom.'&recherche_matricule='.$recherche_matricule.'&date_entree='.$date_entree.'";
					},
					false,
					);
					</script>';
				
				print "</select>";
				print "</div>";

				if($zero)
					$num = 0;
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

				if($nb_col == 1){ //une colonne plus les 5 affichées par defaut = 6;
					$largeur = "16%";

				}elseif($nb_col == 2){ //nombre de colonne = 7
					$largeur = "14%";

				}elseif($nb_col == 3){
					$largeur = "12%";

				}elseif($nb_col == 4){
					$largeur = "11%";

				}
				//1 ligne de titre
				print '<tr  class="liste_titre" >';
				print '<td align="center" style=" width: '.$largeur.';">
				<input style="padding:10px" type="text" size="'.$largeur.'" Placeholder="Matricule" value="'.$recherche_matricule.'" name="recherche_matricule" ></td>';
				print '<td align="center" style=" width: '.$largeur.';">
				<input style="padding:10px" type="text" size="'.$largeur.'" Placeholder="Nom" value="'.$recherche_nom.'" name="recherche_nom" ></td>';
				print '<td align="center" style=" width: '.$largeur.';">
				<input style="padding:10px" type="text" size="'.$largeur.'" Placeholder="Prenom" value="'.$recherche_prenom.'" name="recherche_prenom" ></td>';
				print '<td align="center" style=" width: '.$largeur.';">
				<input style="padding:10px" type="date" size="'.$largeur.'" Placeholder="Ancienneté" value="'.$date_entree.'" name="date_entree" ></td>';

				if($categorie == "on")
					print '<td align="center" style=" width: '.$largeur.';"></td>';
				
				if($fonction == "on")
					print '<td align="center" style=" width: '.$largeur.';"></td>';
				
				if($anciennete_case == "on")
					print '<td align="center" style=" width: '.$largeur.';"></td>';

				if($solde_conge == "on")
					print '<td align="center" style=" width: '.$largeur.';"></td>';


				print '<td align="center" colspan="2" style=" width: '.$largeur.';">';
				print '<input type="submit" class="button" value="Rechercher" >';
				print "</form>";
				print '<br>	<a href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=salarie&id_societe='.$id_societe.'&id_convention='.$id_convention.'&action=annuler_recherche" class="button" >Annuler</a>';
				print '</td></tr>';

				//2 ligne de titre
				print '<tr  class="liste_titre" >';
				print '<td align="center"><label><a title="Trié par le matricule" href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenusociete&id_societe='.$id_societe.'&id_convention='.$id_convention.'&limit='.$limit.'&action=rechercher&recherche_nom='.$recherche_nom.'&recherche_prenom='.$recherche_prenom.'&recherche_matricule='.$recherche_matricule.'&date_entree='.$date_entree.'&tri=matricule">Matricule</a></label></td>';
				print '<td align="center"><label><a title="Trié par le nom" href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenusociete&id_societe='.$id_societe.'&id_convention='.$id_convention.'&limit='.$limit.'&action=rechercher&recherche_nom='.$recherche_nom.'&recherche_prenom='.$recherche_prenom.'&recherche_matricule='.$recherche_matricule.'&date_entree='.$date_entree.'&tri=nom"> Nom</a></label></td>';
				print '<td align="center"><label><a title="Trié par le prénom" href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenusociete&id_societe='.$id_societe.'&id_convention='.$id_convention.'&limit='.$limit.'&action=rechercher&recherche_nom='.$recherche_nom.'&recherche_prenom='.$recherche_prenom.'&recherche_matricule='.$recherche_matricule.'&date_entree='.$date_entree.'&tri=prenom">Prenom</a></label></td>';
				print '<td align="center"><label><a title="Trié par le anciennété" href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenusociete&id_societe='.$id_societe.'&id_convention='.$id_convention.'&limit='.$limit.'&action=rechercher&recherche_nom='.$recherche_nom.'&recherche_prenom='.$recherche_prenom.'&recherche_matricule='.$recherche_matricule.'&date_entree='.$date_entree.'&tri=anciennete">Date d\'entrée</a></label></td>';
				
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

				//Affichage des colonnes supplémentaire
				print '<td align="center" colspan="2"><label><span id="colonne_plus">'.img_picto('Ajouter ou enlever des colonnes', 'list').'</span></label>';
				$url = $_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenusociete&id_societe='.$id_societe.'&id_convention='.$id_convention.'&limit='.$limit.'&recherche_nom='.$recherche_nom.'&recherche_prenom='.$recherche_prenom.'&recherche_matricule='.$recherche_matricule.'&date_entree='.$date_entree;
				print '<form name="ajouter_colonne" method="POST" action="'.$url.'">
				<div id="colonne" style="display:flex; flex-direction:column; position: absolute; border: solid 1px white; border-radius:5px; background-color:white; text-align:left; padding: 10px; width: 8%; box-shadow: 4px 4px 10px; margin-left: 100px">';
				print '<input type="hidden" name="token" value="'.newToken().'">';
				print '<input type="hidden" name="action" value="recherche">';
				print '<div><input type="checkbox" name="categorie" id="categorie" '.$checked_categ.'><label for="categorie">Catégorie</label></div>';
				print '<div><input type="checkbox" name="fonction" id="fonction" '.$checked_fonction.'><label for="fonction">Fonction</label></div>';
				print '<div><input type="checkbox" name="anciennete" id="anciennete" '.$checked_anc.'><label for="anciennete">Anciennété</label></div>';
				print '<div><input type="checkbox" name="solde_conge" id="solde_conge" '.$checked_solde.'><label for="solde_conge">Solde congé</label></div>';
				print '<div><input class="button" type="submit" name="valider" value="Valider"></div>';
				print '</div></form>';
				print '</td>';


				print '</tr>';

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
				
				print "</select>";
				print "</div>";

				$num = count($obj_liste);
				$adresse = "";
					$i = $arret;
				while ($i < $num){
					$class = "impair";
					if($i%2 == 0)
						$class = "pair";
					if(!empty($obj_liste[$i]->salrowid)){
						print '<tr class="pair">';
						print '<td align="center" >'.$obj_liste[$i]->matricule.'</td>';
						print '<td align="center" >'.$obj_liste[$i]->lastname.'</td>';
						print '<td align="center" >'.$obj_liste[$i]->firstname.'</td>';

						//calcul de l'anciennete
						$anciennete = prime_anciennete($db, $obj_liste[$i]->salrowid, $id_convention, date('m'), date('Y'), $obj_liste[$i]->rowid);
						
						print '<td align="center">'.$obj_liste[$i]->dateemployment.'</td>';
						if($categorie == "on"){
							$categorie_Sql = "SELECT code_categorie FROM ".MAIN_DB_PREFIX."dcategories WHERE rowid=".$obj_liste[$i]->fk_categorie;
							$categorie_Result = $db->query($categorie_Sql);
							$categorie_Salarie = $db->fetch_object($categorie_Result);
							$categ = $categorie_Salarie->code_categorie?:"N/A";

							if($obj_salarie->fk_echelon !=0){
								$echelon_Sql = "SELECT libelle FROM ".MAIN_DB_PREFIX."echelon where rowid=".($obj_liste[$i]->fk_echelon?:0);
								$echelon_Result = $db->query($echelon_Sql);
								$echelon_Salarie = $db->fetch_object($echelon_Result);
								$categ .= "==>".$echelon_Salarie->libelle;
							}
							print '<td align="center" style="padding: 5px; width: 20%;">'.$categ.'</td>';
						}

						if($fonction == "on" && $obj_liste[$i]->dateemployment)
							print '<td align="center" style="padding: 5px; width: 20%;">'.$obj_liste[$i]->job.'</td>';
						
						if($anciennete_case == "on")
							print '<td align="center">'.$anciennete[0].'an(s)</td>';

						

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

						print '<td align="center" ><a href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=societe&id_societe='.$id_societe.'&fk_salarie='.$obj_liste[$i]->salrowid.'&id='.$obj_liste[$i]->rowid.'&id_convention='.$id_convention.'&action=archiver"><button title="Ce bouton permet d\'archiver ce salarié" class="button">Archiver</button></a></td>';
						print '<td align="center" ><a title="Voir détails" href="./salarie_information.php?mainmenu=paiementsalaire&leftmenu=societe&fk_salarie='.$obj_liste[$i]->salrowid.'&id_societe='.$id_societe.'&id='.$obj_liste[$i]->rowid.'&id_convention='.$id_convention.'&action=detail"><button class="button">Détails</button></a></td>';
					}else{
						print '<tr class="pair">';
						print '<td align="center" >'.$obj_liste[$i]->matricule.'</td>';
						print '<td align="center" ><mark>'.$obj_liste[$i]->lastname.'</mark>'.info_admin("Ce salarié n'est pas enregistré", 1).'</td>';
						print '<td align="center" ><mark>'.$obj_liste[$i]->firstname.'</mark></td>';

						//calcul de l'anciennete
						$anciennete = prime_anciennete($db, $obj_liste[$i]->salrowid, $id_convention, date('m'), date('Y'), $obj_liste[$i]->rowid);
						
						print '<td align="center">'.$obj_liste[$i]->dateemployment.'</td>';

						if($categorie == "on"){
							$categorie_Sql = "SELECT code_categorie FROM ".MAIN_DB_PREFIX."dcategories WHERE rowid=".$obj_liste[$i]->fk_categorie;
							$categorie_Result = $db->query($categorie_Sql);
							$categorie_Salarie = $db->fetch_object($categorie_Result);
							$categ = $categorie_Salarie->code_categorie?:"N/A";

							if($obj_salarie->fk_echelon !=0){
								$echelon_Sql = "SELECT libelle FROM ".MAIN_DB_PREFIX."echelon where rowid=".($obj_liste[$i]->fk_echelon?:0);
								$echelon_Result = $db->query($echelon_Sql);
								$echelon_Salarie = $db->fetch_object($echelon_Result);
								$categ .= "==>".$echelon_Salarie->libelle;
							}
							print '<td align="center" style="padding: 5px; width: 20%;">'.$categ.'</td>';
						}

						if($fonction == "on" && $obj_liste[$i]->dateemployment)
							print '<td align="center" style="padding: 5px; width: 20%;">'.$obj_liste[$i]->job.'</td>';
						
						if($anciennete_case == "on")
							print '<td align="center">'.$anciennete[0].'an(s)</td>';

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

						print '<td align="center" ><a href="./salarie_information.php?mainmenu=paiementsalaire&leftmenu=societe&id_societe='.$id_societe.'&fk_salarie='.$obj_liste[$i]->salrowid.'&id='.$obj_liste[$i]->rowid.'&id_convention='.$id_convention.'&action=edit"><button title="Ce bouton permet d\'enregistrer ce salarié" class="button">Enregistrer</button></a></td>';
						print '<td align="center" ><a title="Voir détails" href="./salarie_information.php?mainmenu=paiementsalaire&leftmenu=societe&fk_salarie='.$obj_liste[$i]->salrowid.'&id_societe='.$id_societe.'&id='.$obj_liste[$i]->rowid.'&id_convention='.$id_convention.'&action=detail"><button class="button">Détails</button></a></td>';
					}
					print '</tr>';    
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
					$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=0&nbpage=1&action=recherche&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_matricule=".$recherche_matricule."&date_entree=".$date_entree."' style='padding: 5px'><b>Debut</b>    </a>&nbsp;&nbsp;";
				else if(1 < ($nb+1))
				$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=0&nbpage=1&action=recherche&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_matricule=".$recherche_matricule."&date_entree=".$date_entree."' style='padding: 5px'><b>Debut</b>    </a>&nbsp;&nbsp;";

			
			if($arret > $limit){

				
				if($nb_page-3>=0)
					$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=".($limit*($nb_page-3))."&nbpage=".($nb_page-2)."&action=recherche&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_matricule=".$recherche_matricule."&date_entree=".$date_entree."&tri=".$tri."' style='padding: 5px'><b>".($nb_page -2)."</b></a>&nbsp;&nbsp;";

				if($nb_page-2>=0)
							$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=".($limit*($nb_page-2))."&nbpage=".($nb_page-1)."&action=recherche&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_matricule=".$recherche_matricule."&date_entree=".$date_entree."&tri=".$tri."' style='padding: 5px'><b>".($nb_page-1)."</b></a>&nbsp;&nbsp;";
				
				
				if($nb_page-1>=0)
						$page_link .= "<a style='background-color: yellow;' href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=".($limit*($nb_page-1))."&nbpage=".($nb_page)."&action=recherche&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_matricule=".$recherche_matricule."&date_entree=".$date_entree."&tri=".$tri."' style='padding: 5px'><b>".($nb_page)."</b></a>&nbsp;&nbsp;";

			

				
					if(	(($nb_page+1) <= ($nb)))
						$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=".($limit*$nb_page)."&nbpage=".($nb_page+1)."&action=recherche&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_matricule=".$recherche_matricule."&date_entree=".$date_entree."&tri=".$tri."' style='padding: 5px'><b>".($nb_page + 1)."</b></a>&nbsp;&nbsp;";

				
					if((($nb_page+2) <= ($nb)))
						$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=".($limit*($nb_page +1))."&nbpage=".($nb_page+2)."&action=recherche&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_matricule=".$recherche_matricule."&date_entree=".$date_entree."&tri=".$tri."' style='padding: 5px'><b>".($nb_page + 2)."</b></a>&nbsp;&nbsp;";
						
					
					if((($nb_page+3) <= ($nb)))
						$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=".($limit*($nb_page+2))."&nbpage=".($nb_page+3)."&action=recherche&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_matricule=".$recherche_matricule."&date_entree=".$date_entree."&tri=".$tri."' style='padding: 5px'><b>".($nb_page + 3)."</b></a>&nbsp;&nbsp;";

						


			}else{

				
					if( 1 <= ($nb))
						
						$page_link .= "<a style='background-color: yellow;' href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=0&nbpage=1&action=recherche&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_matricule=".$recherche_matricule."&date_entree=".$date_entree."&tri=".$tri."' style='padding: 5px'><b>1</b></a>&nbsp;&nbsp;";
				
				
					if(2 <= ($nb))
						
						$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=".$limit."&nbpage=2&action=recherche&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_matricule=".$recherche_matricule."&date_entree=".$date_entree."&tri=".$tri."' style='padding: 5px'><b>2</b></a>&nbsp;&nbsp;";
				
				
					if(3 <= ($nb))
						
						$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=".($limit*2)."&nbpage=3&action=recherche&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_matricule=".$recherche_matricule."&date_entree=".$date_entree."&tri=".$tri."' style='padding: 5px'><b>3</b></a>&nbsp;&nbsp;";
					
					if(4 <= ($nb))
						
						$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=".($limit*3)."&nbpage=4&action=recherche&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_matricule=".$recherche_matricule."&date_entree=".$date_entree."&tri=".$tri."' style='padding: 5px'><b>4</b></a>&nbsp;&nbsp;";

					if(5 <= ($nb))
						
						$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=".($limit*4)."&nbpage=5&action=recherche&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_matricule=".$recherche_matricule."&date_entree=".$date_entree."&tri=".$tri."' style='padding: 5px'><b>5</b></a>&nbsp;&nbsp;";



			}
			if($nb_page != ($nb)  )
					$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=".($limit*($nb-1))."&nbpage=".($nb)."&action=recherche&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_matricule=".$recherche_matricule."&date_entree=".$date_entree."&tri=".$tri."' style='padding: 5px'>      <b>Fin</b></a>&nbsp;&nbsp;";

			
		}
				print $page_link.'</span>';
			if(count($obj_liste)>0)
			print '<a class="button" target="_blank" href="../doc/liste_personnel.php?id_societe='.$id_societe.'">Generer la liste</a>';
			print '</div>';

			if($message != ""){		
				print "<script>
				$.jnotify('".$message."', {delay : 5000, fadeSpeed: 500});
				</script>";
			}

	}else{
		print "<h2 style='align:center;'>Vous n'avez pas la permission voir cette liste</h2>";
	}
}else{
	print "<h2 style='align:center;'>Veuillez affecter une convention à cette société</h2>";

}
