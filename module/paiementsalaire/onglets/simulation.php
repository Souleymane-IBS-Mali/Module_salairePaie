<?php
require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/custom/paiementsalaire/lib/paiementsalaire.lib.php';


llxHeader("", "Paiement | Salaire");
//Titre 
print load_fiche_titre($langs->trans("Simulation du Salaire Net"), '', '');
//print '<hr>';

$fk_user = GETPOST("id","int");
$id_societe = GETPOST("id_societe","int");
$fk_salarie = GETPOST("fk_salarie", "int");
$id_convention = GETPOST("id_convention","int");
$head = salaire_Head($fk_salarie, $fk_user, $id_societe, $id_convention);
	print dol_get_fiche_head($head, 'simulation', "", -1, '');
if($user->id !=1 && $user->id != $fk_user && !$user->rights->paiementsalaire->salarie->read){
	print "<h2> Vous n\'avez pas ce droit </h2>";
}else{
	$action = GETPOST("action", "alpha");
	if($user->rights->paiementsalaire->salarie->simuler){
		$salaire_net = str_replace(' ', '', preg_replace('/[^0-9. ]/', '', GETPOST('salaire_net', 'alpha')));
	$message = "";
	if($action == "save_edit"){
		$sursalaire = str_replace(' ', '',preg_replace('/[^0-9. ]/', '', GETPOST('sursalaire', 'alpha')));
		$sql = "UPDATE ".MAIN_DB_PREFIX."salarie SET";
			if($sursalaire != "" && $sursalaire>0){
					$sql .= " sursalaire=".round(str_replace(' ', '', $sursalaire))."";

			}else{
				$sursalaire = 0;
				$sql .= " sursalaire=0";
				$message = "Le SURSALAIRE négatif est remplacé par 0";
			}

			$sql .= " WHERE rowid=".$fk_salarie;
			
			//Modification du sursalaire
			$result = $db->query($sql);
			if($result){
				$sql_contrat = "SELECT rowid FROM ".MAIN_DB_PREFIX."salarie_contrat WHERE fk_salarie=".$fk_salarie." AND active=1";
				$res_contrat = $db->query($sql_contrat);
				$obj_contrat = $db->fetch_object($res_contrat);
				$id_contrat = $obj_contrat->rowid;

				$sql_salaire_net  = "SELECT salaire_net, sursalaire FROM ".MAIN_DB_PREFIX."salarie_contrat_salaire_net WHERE active=1 AND fk_contrat=".$obj_contrat->rowid;
				$res_salaire_net  = $db->query($sql_salaire_net );
				$obj_salaire_net = $db->fetch_object($res_salaire_net );
				$ancien_salaire_net = $obj_salaire_net->salaire_net;
				$ancien_sursal = $obj_salaire_net->sursalaire;

				//Recupération de l'ancien sursalaire avant la modification
				$sql_societe = "SELECT nom FROM ".MAIN_DB_PREFIX."societe WHERE rowid=".$id_societe;
				$nom_societe = $db->fetch_object($db->query($sql_societe))->nom;

				$sql_update = 'UPDATE '.MAIN_DB_PREFIX.'salarie_contrat_salaire_net SET active=0, date_limit=now() WHERE fk_contrat='.$id_contrat;
				if($db->query($sql_update)){
					$sql = 'INSERT INTO '.MAIN_DB_PREFIX.'salarie_contrat_salaire_net (fk_contrat, salaire_net, sursalaire, date_debut, active)';
					$sql .= 'VALUES('.$id_contrat.',"'.round(str_replace(' ', '', $salaire_net)).'","'.round(str_replace(' ', '', $sursalaire)).'",now(),1)';
					if($db->query($sql)){
						$sql_select = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$user->id;
						$obj = $db->fetch_object($db->query($sql_select));

						$sql_select_us = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$fk_user;
						$obj_us = $db->fetch_object($db->query($sql_select_us));

						//On garde la trace de l'action
						$action_effectue = "Modification du sursalaire et salaire net. Anciennes valeurs sursalaire ".number_format($ancien_sursal, 2, '.', ' ')." salaire net ".number_format($ancien_salaire_net, 2, '.', ' ')." ==> nouvelles valeurs (sursalaire : ".number_format($sursalaire, 2, '.', ' ').") et salaire net (salaire net ".number_format($salaire_net, 2, '.', ' ').") de ".$obj_us->firstname." ".$obj_us->lastname." de la société ".$nom_societe." après la simulation";
						$sql_log = 'INSERT INTO '.MAIN_DB_PREFIX.'log (fk_user, nom, prenom, quand, action_effectue, object_concerne)';
						$sql_log .= ' VALUES('.$user->id.', "'.$obj->lastname.'","'.$obj->firstname.'",now(),"'.$action_effectue.'","Modification")';
						$db->query($sql_log);
					}

				}

				$message .= '<br>Sursalaire modifié avec succès';
			}else {
				$message = 'Un problème est survenu';
			}

	}

	if(empty($fk_salarie)){
		print "<mark><strong>Il n'a pas encore de fk_salarie</strong></mark><br>";
		print "Page non Disponible";
	}else{

		$tab_prime_ind = array();
		$obj_soc = prepare_objet_entete($fk_salarie, $fk_user, $db, $id_societe, $id_convention);
		entete_societe($obj_soc, 'societe');
		//type simulation
		print '<mark><h3 id="avertissement" style="color:red;"></h3></mark>';
		print "<div style='display:flex; flex:2; flex-direction:row;'>";
		print "<div style='flex:1'>";
		if(((preg_replace('/[^0-9. ]/', '', GETPOST('salaire_brut', 'alpha')) == 0 || preg_replace('/[^0-9. ]/', '', GETPOST('salaire_brut', 'alpha')) < 0) && $type_simulation == "salaire_brut"))
			print "<mark><strong>Le champ 'SALAIRE BRUT' est obligatoire</strong></mark><br>";
		elseif(((preg_replace('/[^0-9. ]/', '', GETPOST('cout', 'alpha')) == 0 || preg_replace('/[^0-9. ]/', '', GETPOST('cout', 'alpha')) < 0) && $type_simulation == "cout"))
			print "<mark><strong>Le champ 'COUT TOTAL' est obligatoire</strong></mark><br>";
		elseif(((preg_replace('/[^0-9. ]/', '', GETPOST('salaire_net', 'alpha')) == 0 || preg_replace('/[^0-9. ]/', '', GETPOST('salaire_net', 'alpha')) < 0) && $type_simulation == "salaire_net"))
			print "<mark><strong>Le champ 'SALAIRE NET' est obligatoire</strong></mark><br>";

		print '<form name="add" method="POST" action="'.$_SERVER['PHP_SELF'].'?mainmenu=paiementsalaire&leftmenu=salarie&id_societe='.$id_societe.'&id_convention='.$id_convention.'&id='.$fk_user.'&fk_salarie='.$fk_salarie.'">';
		print '<input type="hidden" name="token" value="'.newToken().'">';
		print '<input type="hidden" name="action" value="">';	
		print '<input type="hidden" name="type_simulation" value="salaire_brut">';	
		print '<table class="tagtable liste" style="margin-bottom: 0px;">';
		print '<tr class="impair"><td style="padding: 10px; width: 200px;">Catégorie</td>';

		print '<td style="padding: 10px; width: 200px;">';
		$salSql = "SELECT * FROM ".MAIN_DB_PREFIX."salarie where rowid=".$fk_salarie;
		$result = $db->query($salSql);
			$salarie = $db->fetch_object($result);

		$grilleSql = "SELECT rowid FROM ".MAIN_DB_PREFIX."grille WHERE active=1 AND fk_convention=".$id_convention;
		$grilleResult = $db->query($grilleSql);//= $db->query($grilleSql);
		$obj_grille = $db->fetch_object($grilleResult);

		$salBaseSql = "SELECT salaire_base FROM ".MAIN_DB_PREFIX."grille_categorie_echelon_salaire_base WHERE fk_grille=".$obj_grille->rowid." AND fk_categorie=".$salarie->fk_categorie." AND fk_echelon=".$salarie->fk_echelon;
		$salBaseResult = $db->query($salBaseSql);//= $db->query($covSql);
		$objSalBase = $db->fetch_object($salBaseResult);
		$salaire_base = $objSalBase->salaire_base;
		$salaire_base_hs = $salaire_base;

		$categ_sql = "SELECT rowid, code_categorie FROM ".MAIN_DB_PREFIX."dcategories WHERE rowid=".$salarie->fk_categorie;
			$categ_res = $db->query($categ_sql);//= $db->query($categ_sql);
			if($categ_res)
				$obj_categ = $db->fetch_object($categ_res);
			$categ = $obj_categ->code_categorie;

			$echel_sql = "SELECT libelle FROM ".MAIN_DB_PREFIX."echelon WHERE rowid=".$salarie->fk_echelon;
			$res_echel = $db->query($echel_sql);//= $db->query($echel_sql);
			if($res_echel)
				$obj_echel = $db->fetch_object($res_echel);

			if(!empty($obj_echel->libelle))
				$categ .= '==>'.$obj_echel->libelle;

			print $categ.'</td></tr>';


		$ind_array = salarie_indemnite_simulation($db, $fk_salarie, $salaire_base, $obj_categ->rowid,0,$id_convention);
		foreach ($ind_array as $key => $value) {
		if(!empty($key) && !empty($value)){
			$sql = "SELECT * FROM ".MAIN_DB_PREFIX."indemnite WHERE rowid=".$key;
			$ind_res = $db->query($sql);
			if($ind_res){
				$ind = $db->fetch_object($ind_res);
				if($ind->exonere == "oui")//retiré du salaire de base
					$salaire_base -= $value;				

			//print "<br> Nom = ".$ind->libelle." afficher sur bulletin=".$ind->affiche_bulletin."=>".$value;

			}

		}
		}

		$pr_array = salarie_prime_simulation($db, $fk_salarie, $salaire_base, $obj_categ->rowid,0, $id_convention);
		foreach ($pr_array as $key => $value) {
		if(!empty($key) && !empty($value)){
			//$somme += $value;
			$sql = "SELECT * FROM ".MAIN_DB_PREFIX."primes WHERE rowid=".$key;
			$prime_res = $db->query($sql);
			if($prime_res){
				$pr = $db->fetch_object($prime_res);

				if($pr->exonere == "oui")//retiré du salaire de base
					$salaire_base -= $value;

				//print "<br> Nom = ".$pr->libelle." afficher sur bulletin=".$pr->affiche_bulletin."=>".$value;
			}
		}
		}

		$annee_d = date('Y');
		$mois_d = date('m');
		$sql_verif = "SELECT DISTINCT mois, annee FROM ".MAIN_DB_PREFIX."bulletin WHERE cloture='non' AND fk_societe=".$id_societe." ORDER BY rowid DESC";
		$res_verif = $db->query($sql_verif);
		if($res_verif && $db->num_rows($res_verif)){
			$obj_verif = $db->fetch_object($res_verif);
			$mois_d = $obj_verif->mois;
			$annee_d = $obj_verif->annee;
		}

		$anciennete_tab = prime_anciennete($db, $fk_salarie, $id_convention, $mois_d, $annee_d, $fk_user, $fk_user);
		$anciennete = $salaire_base*$anciennete_tab[1]/100;
		if($anciennete_tab[5] == "Oui")
		$salaire_base -= $anciennete;


		$annee = date("Y");
		$mois = date("m");
		/*$salSql = "SELECT jour FROM ".MAIN_DB_PREFIX."salarie_nombre_jour_travaille where annee=".$annee." AND mois=".$mois." AND fk_salarie=".$fk_salarie;
		$result = $db->query($salSql);
		$nb_jours = $db->fetch_object($result)->jour;
		$nb_total_jour = cal_days_in_month(CAL_GREGORIAN, $mois, $annee);*/
		$base_pourcentage = 1;
		/*if($nb_jours != $nb_total_jour){
			$sal_base = ($nb_jours*$salaire_base)/$nb_total_jour;
			$base_pourcentage = ($sal_base*100)/$salaire_base;
			$base_pourcentage = $base_pourcentage/100;
			$salaire_base = round($salaire_base*$base_pourcentage);
		}*/
		//print $salaire_base;

		print "<tr class='impair'>";
		print '<td style="padding: 10px; width: 200px;">Salaire de Base</td>';
		print '<td style="padding: 10px; width: 210px;"><input type="text" disabled id="salaire_base" name="salaire_base" value="'.apres_virgule($db, $id_societe, $salaire_base).'" ></td>';
		
		print '</tr>';

		print "<tr class='impair'>";
		print '<td style="padding: 10px; width: 200px;">Ancienneté</td>';
		print '<td style="padding: 10px; width: 210px;">';
		print $anciennete_tab[0].'an(s) ==> '.($salaire_base*$anciennete_tab[1]/100).'</td>';
		print '</tr>';

		//--------------------------------------------------------------------------------------------
		//print $anciennete_tab[0]." => ".$anciennete_tab[1];
		//les salaires
		$salaire_base = $salaire_base*$base_pourcentage;
		$salaire_brut_imposable = $salaire_base;
		$salaire_brut_cotisable = $salaire_base;
		$salaire_brut = $salaire_base;

		$salaire_net = 0;
		$retenu_prest_empl = 0;
		$retenu_prest_patro = 0;
		$retenu_taxe = 0;
		$retenu = 0;

		$salaire_brut += $salaire_base*$anciennete_tab[1]/100;

		if($anciennete_tab[3] == "Oui")//exonere cotisation ou non
			$salaire_brut_cotisable += $salaire_base*$anciennete_tab[1]/100;

		if($anciennete_tab[4] == "Oui")//exonere impôt ou non
			$salaire_brut_imposable += $salaire_base*$anciennete_tab[1]/100;

		//les primes qui doivent être affichés sur le billetin


		$pr_array = salarie_prime_simulation($db, $fk_salarie, $salaire_base, $obj_categ->rowid,0, $id_convention);
		foreach ($pr_array as $key => $value) {
		if(!empty($key) && !empty($value)){
			//$somme += $value;
			$sql = "SELECT * FROM ".MAIN_DB_PREFIX."primes WHERE rowid=".$key;
			$prime_res = $db->query($sql);
			if($prime_res){
				$pr = $db->fetch_object($prime_res);

					if($pr->soumis_cotisation=="Oui")
						$salaire_brut_cotisable += $value;

					if($pr->soumis_impot=="Oui")
					$salaire_brut_imposable += $value;

					$salaire_brut += $value;


					$tab_prime_ind[] = $pr->libelle."(".$value.")";
					//print $ind->libelle."*********";

				//print "<br> Nom = ".$pr->libelle." afficher sur bulletin=".$pr->affiche_bulletin."=>".$value;
			}
		}
		}

		
		

		//les primes flottantes de montant variable
		$pr_fl = prime_flottante($db, $fk_salarie);
		foreach ($pr_fl as $key => $value) {
			if(!empty($key) && !empty($value)){
				$sql = "SELECT * FROM ".MAIN_DB_PREFIX."primes WHERE rowid=".$key;
				$prime_res = $db->query($sql);
				if($prime_res){
					$pr = $db->fetch_object($prime_res);
					
					$val = $value;
					$pourc = 100;
				
					if(count(explode('%',$value."v")) > 1)
						$val = ($objSalBase->salaire_base*$base_pourcentage*explode('%',$value)[0])/100;
					if($val != $value)
						$pourc = explode('%',$value)[0];
		
					$val = $value;
					if(count(explode('%',$value."v")) > 1)
						$val = ($objSalBase->salaire_base*$base_pourcentage*explode('%',$value)[0])/100;
					$salaire_brut += $val*$base_pourcentage;
					if($pr->soumis_cotisation=="Oui")
						$salaire_brut_cotisable += $val*$base_pourcentage;

					if($pr->soumis_impot=="Oui")
						$salaire_brut_imposable += $val*$base_pourcentage;

					$tab_prime_ind[] = $pr->libelle."(".$value.")";

				}
			}
		}



		//les indemnités qui doivent être affichés sur le billetin
		//Indemnités
		$ind_array= salarie_indemnite_simulation($db, $fk_salarie, $salaire_base, $obj_categ->rowid,0, $id_convention);
		foreach ($ind_array as $key => $value) {
		if(!empty($key) && !empty($value)){
			$sql = "SELECT * FROM ".MAIN_DB_PREFIX."indemnite WHERE rowid=".$key;
			$ind_res = $db->query($sql);
			if($ind_res){
				$ind = $db->fetch_object($ind_res);
					$salaire_brut += $value;

					if($ind->soumis_cotisation=="Oui"){//les indemnités soumisent aux cotisations
						if(!empty($ind->porcentage_soumis_cotis))
							$salaire_brut_cotisable += ($value*$ind->porcentage_soumis_cotis)/100;
					}
					if($ind->soumis_impot=="Oui")////les indemnités soumisent aux impôt
						if(!empty($ind->porcentage_soumis_impot))
							$salaire_brut_imposable += ($value*$ind->porcentage_soumis_impot)/100;
		
					$tab_prime_ind[] = $ind->libelle."(".$value.")";
				//print "<br> Nom = ".$ind->libelle." afficher sur bulletin=".$ind->affiche_bulletin."=>".$value;
			}

		}
		}


		$ind_array = indemnite_flottante($db, $fk_salarie);
		foreach ($ind_array as $key => $value) {
		if(!empty($key) && !empty($value)){
			$sql = "SELECT * FROM ".MAIN_DB_PREFIX."indemnite WHERE rowid=".$key;
			$ind_res = $db->query($sql);
			if($ind_res){
				$ind = $db->fetch_object($ind_res);
				$val = $value;
				$pourc = 100;
				
				if(count(explode('%',$value."v")) > 1)
					$val = ($objSalBase->salaire_base*explode('%',$value)[0])/100;
				if($val != $value)
					$pourc = explode('%',$value)[0];			
								
				if($ind->soumis_cotisation=="Oui"){//les indemnités soumisent aux cotisations
					if(!empty($ind->porcentage_soumis_cotis))
						$salaire_brut_cotisable += ($val*$base_pourcentage*$ind->porcentage_soumis_cotis)/100;
				}
				if($ind->soumis_impot=="Oui")////les indemnités soumisent aux impôt
					if(!empty($ind->porcentage_soumis_impot))
						$salaire_brut_imposable += ($val*$base_pourcentage*$ind->porcentage_soumis_impot)/100;


				$salaire_brut += $val*$base_pourcentage;
				$tab_prime_ind[] = $ind->libelle."(".$value.")";
				$index ++;
			}
		}
		}


	//----------------------------------------------------------------------------------------------------------


		print '<tr class="impair"><td style="padding: 10px; width: 200px;">Types de Simulation</td>';

		$type_simulation = !empty(GETPOST('type_simulation', 'alpha'))?GETPOST('type_simulation', 'alpha'):"salaire_net";
		$select1 = "";
		$select2 = "";
		$select3 = "";
		if($type_simulation == 'salaire_brut')
			$select1 = "selected";
		elseif($type_simulation == 'salaire_net')
			$select2 = "selected";
		else $select3 = "selected";
		print '<td style="padding: 10px; width: 200px;"><select id="type_simulation" name="type_simulation">';
		print '<option value="salaire_brut" '.$select1.' >Salaire Brut</option>';
		print '<option value="salaire_net" '.$select2.' >Salaire Net</option>';
		print '<option value="cout" '.$select3.' >Cout total</option>';

		print '</select>';
		print '</td></tr>';
		print '</table>';
		print '<hr style="margin-top: 0px; margin-bottom: 0px;">';

		print '<tr>';
		print '<table class="tagtable liste" id="div_brut">';
			print '<tr class="impair"><td style="padding: 10px; width: 25%;">Salaire brut</td>';
			print '<td style="padding: 10px; width: 25%;">';
			print '<input type="text" id="salaire_brut" value="'.(preg_replace('/[^0-9. ]/', '', GETPOST('salaire_brut', 'alpha'))?:0).'" name="salaire_brut" ></td></tr>';
		print '</table>';

		print '<table class="tagtable liste" id="div_net">';
			print '<tr class="impair"><td style="padding: 10px; width: 25%;">Salaire net</td>';
			print '<td style="padding: 10px; width: 25%;">';
			print '<input type="text" id="salaire_net" value="'.(preg_replace('/[^0-9. ]/', '', GETPOST('salaire_net', 'alpha'))?:0).'" name="salaire_net" ></td></tr>';
		print '</table>';

		print '<table class="tagtable liste" id="div_cout_tot">';
			print '<tr class="impair"><td style="padding: 10px; width: 25%;">Cout Total</td>';
			print '<td style="padding: 10px; width: 25%;">';
			print '<input type="text" id="cout" value="'.(preg_replace('/[^0-9. ]/', '', GETPOST('cout', 'alpha'))?:0).'" name="cout" ></td></tr>';
		print '</table>';
	print '</tr>';
		print '<input style="margin-top:50px; margin-left:400px;" type="submit" class="button" value="Simuler" >';
		print '</form>';
		print "</div>";
		print "<div id='partie_cache' style=' margin-left: 20px; flex:1'>";
		

		print '<script type="text/javascript">
			var type_simulation = document.getElementById("type_simulation");

			var brut = document.getElementById("div_brut");
			var net = document.getElementById("div_net");
			var cout_tot = document.getElementById("div_cout_tot");
			//initialisation
			if(type_simulation.value == "salaire_brut"){
				brut.style.display = "inline";
				net.style.display = "none";
				cout_tot.style.display = "none";
			}else if(type_simulation.value=="salaire_net"){
				brut.style.display = "none";
				net.style.display = "inline";
				cout_tot.style.display = "none";

			}else{
				brut.style.display = "none";
				net.style.display = "none";
				cout_tot.style.display = "inline";
			}

			type_simulation.addEventListener("change", function () {
				if(type_simulation.value == "salaire_brut"){
					brut.style.display = "inline";
					net.style.display = "none";
					cout_tot.style.display = "none";
				}else if(type_simulation.value=="salaire_net"){
					brut.style.display = "none";
					net.style.display = "inline";
					cout_tot.style.display = "none";

				}else{
					brut.style.display = "none";
					net.style.display = "none";
					cout_tot.style.display = "inline";
				}
			
			},
			false,
			);

		</script>';
		print "<div id='partie_cache' style='flex:1'>";
		$m = "Les primes et indemnités utilisées : ";
		for ($i=0; $i < count($tab_prime_ind); $i++) { 
			$m .= ($i +1).") ".$tab_prime_ind[$i]."  ";
		}
		print info_admin($m, 1)."<br>";
		$mon_salaire_brut = $salaire_brut;
		$mon_brut_cotis = 0;
		$mon_brut_imp = 0;
		$mon_net = 0;
		$fin = false;

		$sursalaire = 0;
	if($type_simulation == 'salaire_brut'){

		$sursalaire = 0;
		$mon_salaire_brut  = str_replace(' ', '', preg_replace('/[^0-9. ]/', '', GETPOST('salaire_brut', 'alpha')));
		//round(str_replace(' ', '', GETPOST("salaire_brut", "int ")));

	if(!empty($mon_salaire_brut)){

		$sursalaire = $mon_salaire_brut - $salaire_brut;
		$cout = $mon_salaire_brut;
		$inps = 0;
		$retenu = 0;
		$mon_net = 0;
			$salaire_brut_cotisable += $sursalaire;
			$salaire_brut_imposable += $sursalaire;
			print "<table class='tagtable liste'>";
			$brut = "salaire brut calculé + sursalaire = ".apres_virgule($db, $id_societe, $salaire_brut)." + ".apres_virgule($db, $id_societe, $sursalaire)." = ".apres_virgule($db, $id_societe, $mon_salaire_brut);
			print '<tr class="impair"><td style="padding: 10px; width: 200px;"><label>Salaire brut'.info_admin("Le salaire brut obtenu après le calcul du salaire de ce salarié, ".$brut,1).'</label></td>';
			print '<td style="padding: 10px; width: 210px;"><input type="text" disabled id="salaire_net" name="salaire_net" value="'.(apres_virgule($db, $id_societe, $salaire_brut)).'"></td></tr>';					
			$index = 0;
				$global_cotis = salarie_prestation_simulation($db, $fk_salarie, $salaire_brut_cotisable, $id_convention);
				$cotis = $global_cotis[1];
				$taux_p = $global_cotis[0];
				foreach ($cotis as $key => $value) {
					$type_prest = "SELECT * FROM ".MAIN_DB_PREFIX."type_prestation WHERE rowid=".$key;
						$result_type_prest = $db->query($type_prest);
						$obj_prest_type = $db->fetch_object($result_type_prest);
						if($obj_prest_type){
							$retenu_prest_empl = $value*$salaire_brut_cotisable/100;
							$retenu_prest_patro = $taux_p[$index]*$salaire_brut_cotisable/100;
							$cout += $retenu_prest_patro;
							//print $retenu_prest_empl."<br>";

							print '<tr class="impair"><td style="padding: 10px; width: 200px;">'.$obj_prest_type->code.'</td>';
							print '<td style="padding: 10px; width: 210px;"><input type="text" disabled id="its" name="its" value="'.apres_virgule($db, $id_societe, $retenu_prest_patro).'"></td></tr>';
							print '<tr class="impair"><td style="padding: 10px; width: 200px;">'.$obj_prest_type->code.' employé</td>';
							print '<td style="padding: 10px; width: 210px;"><input type="text" disabled id="its" name="its" value="'.apres_virgule($db, $id_societe, $retenu_prest_empl).'*"></td></tr>';

							print "</tr>";	
							if($obj_prest_type->rowid != 6)
								$inps += $value*$salaire_brut_cotisable/100;
							
							$retenu	+= $value*$salaire_brut_cotisable/100;
							$index ++;
						}
						
				}
				
				$index = 0;
				$global_taxe2 = simulation_taxe2($db, $fk_salarie, $id_convention);
				$taxe2 = $global_taxe2[1];
				$taux_p = $global_taxe2[0];
				foreach ($taxe2 as $key => $value) {
					$type_taxe2 = "SELECT * FROM ".MAIN_DB_PREFIX."type_taxe WHERE rowid=".$key;
						$result_type_taxe2 = $db->query($type_taxe2);
						$obj_taxe2_type = $db->fetch_object($result_type_taxe2);
						if($obj_taxe2_type){
							$retenu_taxe2_empl = $value*$salaire_brut/100;
							$retenu_taxe2_patro = $taux_p[$index]*$salaire_brut/100;
							$cout += $retenu_taxe2_empl;
							$cout += $retenu_taxe2_patro;
							//print $retenu_taxe2_empl."<br>";

							print '<tr class="impair"><td style="padding: 10px; width: 200px;">'.$obj_taxe2_type->libelle.'</td>';
							print '<td style="padding: 10px; width: 210px;"><input type="text" disabled id="its" name="its" value="'.apres_virgule($db, $id_societe, $retenu_taxe2_patro).'"></td></tr>';
							print '<tr class="impair"><td style="padding: 10px; width: 200px;">'.$obj_taxe2_type->libelle.' employé</td>';
							print '<td style="padding: 10px; width: 210px;"><input type="text" disabled id="its" name="its" value="'.apres_virgule($db, $id_societe, $retenu_taxe2_empl).'*"></td></tr>';

							print "</tr>";	
							$index ++;
						}
						
				}

				$salaire_brut_imposable -= $inps;
				$its = its_salarie($db, $fk_salarie, $salaire_brut_imposable);
				$retenu += $its[2];
				$mon_net = $mon_salaire_brut - $retenu;

				//Avertissement sur le salaire net
				$sql_contrat = "SELECT rowid FROM ".MAIN_DB_PREFIX."salarie_contrat WHERE active=1 AND fk_salarie=".$fk_salarie;
				$res_contrat = $db->query($sql_contrat);
				if($res_contrat)
					if($db->num_rows($res_contrat)){
						$obj_contrat = $db->fetch_object($res_contrat);
						$sql_salaire_net  = "SELECT salaire_net FROM ".MAIN_DB_PREFIX."salarie_contrat_salaire_net WHERE active=1 AND fk_contrat=".$obj_contrat->rowid;
						$res_salaire_net  = $db->query($sql_salaire_net );
						
						if($db->num_rows($res_salaire_net) > 0){
							$obj_salaire_net = $db->fetch_object($res_salaire_net );
							if($obj_salaire_net->salaire_net > $mon_net)
							    print "<script>
								   var avertissement = document.getElementById('avertissement');
								   avertissement.textContent = 'L\'ancien salaire net (".apres_virgule($db, $id_societe, $obj_salaire_net->salaire_net).") est superieur à celui-ci (".apres_virgule($db, $id_societe, $mon_net).")';
								</script>";
							}
								
					}
		if($sursalaire >= 0){
			print '<tr class="impair"><td style="padding: 10px; width: 200px;">I.T.S(mensuel)</td>';
			print '<td style="padding: 10px; width: 210px;"><input type="text" disabled id="its" name="its" value="'.apres_virgule($db, $id_societe, $its[2]).'"></td></tr>';
			print "<tr class='impair'>";
			print '<form name="form" method="POST" action="'.$_SERVER['PHP_SELF'].'?mainmenu=paiementsalaire&leftmenu=salarie&id='.$fk_user.'&fk_salarie='.$fk_salarie.'&id_convention='.$id_convention.'&id_societe='.$id_societe.'">';
			print '<input type="hidden" name="token" value="'.newToken().'">';
			print '<input type="hidden" name="action" value="save_edit">';
			print '<input type="hidden" name="salaire_net" value="'.(round($mon_net)?:0).'">';
	
			print '<td style="padding: 10px; width: 200px;">Sursalaire</td>';
			//controle du sursalaire pour que même si l'utilisateur modifie la valeur la simulation ne le prend pas en compte
			print '<input type="hidden" value="'.(apres_virgule($db, $id_societe,$sursalaire?:0)).'" name="sursalaire" >';
			print '<td style="padding: 10px; width: 210px;"><input type="text" id="sursalaire" value="'.(apres_virgule($db, $id_societe, $sursalaire?:0)).'" ><input class="button" type="submit" value="Affecter à ce salarié" /></td>';
			print '</tr>';
			print '<tr class="impair"><td style="padding: 10px; width: 200px;"><label>Salaire net</label></td>';
			print '<td style="padding: 10px; width: 210px;"><input type="text" disabled name="salaireNet" value="'.apres_virgule($db, $id_societe, $mon_net).'"></td></tr>';					
			print '</form>';
			print '<tr class="impair"><td style="padding: 10px; width: 200px;"><label>Coût total</label></td>';
			print '<td style="padding: 10px; width: 210px;"><input type="text" disabled id="cout" name="cout" value="'.(apres_virgule($db, $id_societe, $cout)).'"></td></tr>';					
		
			print "</table>";
		}else{
			print "<script>
				var avertissement = document.getElementById('avertissement');
				avertissement.textContent = 'Avec ces informations ce salarié ne peut pas avoir ça (".apres_virgule($db, $id_societe, $mon_salaire_brut).") comme salaire brut car le sursalaire est négatif (".apres_virgule($db, $id_societe,$sursalaire?:0).")';
				</script>";
		}
			
	}
		
	}elseif($type_simulation == 'salaire_net'){
			$net  = str_replace(' ', '', preg_replace('/[^0-9. ]/', '', GETPOST('salaire_net', 'alpha')));
			//round(str_replace(' ', '', GETPOST("salaire_net", "int ")));
			while ($fin == false && $net){
				$mon_salaire_brut += $sursalaire;
				$mon_brut_cotis = $salaire_brut_cotisable + ($mon_salaire_brut - $salaire_brut);
				$mon_brut_imp = $salaire_brut_imposable + ($mon_salaire_brut - $salaire_brut);
				$retenu_prest_empl = 0;
				$retenu_prest_patro = 0;
				$inps = 0;
			
				$index = 0;
				$global_cotis = salarie_prestation_simulation($db, $fk_salarie, $mon_brut_cotis, $id_convention);
				$cotis = $global_cotis[1];
				$taux_p = $global_cotis[0];
				foreach ($cotis as $key => $value) {
					$type_prest = "SELECT * FROM ".MAIN_DB_PREFIX."type_prestation WHERE rowid=".$key;
						$result_type_prest = $db->query($type_prest);
						$obj_prest_type = $db->fetch_object($result_type_prest);
						if($obj_prest_type){
							$retenu_prest_empl += $value*$mon_brut_cotis/100;
							$retenu_prest_patro += $taux_p[$index]*$mon_brut_cotis/100;
						}
						//print $retenu_prest_empl."<br>";
						if($obj_prest_type->rowid != 6)
							$inps += $value*$mon_brut_cotis/100;
					$index ++;
				}



				$mon_brut_imp -= $inps;
				$its = its_salarie($db, $fk_salarie, $mon_brut_imp);
				$retenu_taxe = $its[2];

				$mon_net = $mon_salaire_brut - $retenu_prest_empl - $retenu_taxe;
				
					if(round($mon_net + 100000) < ((int)$net))
						$sursalaire =  50000;
					elseif(round($mon_net+ 10000) < ($net))
						$sursalaire =  5000;
					elseif(round($mon_net + 1000) < ($net))
						$sursalaire =  500;
					elseif(round($mon_net+ 100) < ($net))
						$sursalaire = 20;
					elseif(round($mon_net) < $net)
						$sursalaire = 5;
					elseif(round($mon_net) == round($net))
						$fin = true;
					elseif(round($mon_net) > ($net + 20000))
						$sursalaire = -1000;
					elseif(round($mon_net) > ($net + 1000))
						$sursalaire = -100;
					elseif(round($mon_net) > ($net + 500))
						$sursalaire = -50;
					else $sursalaire = -1;

			}

			if ($fin){
				$cout = $mon_salaire_brut;
				$salaire_brut_cotisable = $mon_brut_cotis ;
				$salaire_brut_imposable = $mon_brut_imp;
				$sursalaire = $mon_salaire_brut -  $salaire_brut;

				$salaire_brut = $mon_salaire_brut;
				$sql_contrat = "SELECT rowid FROM ".MAIN_DB_PREFIX."salarie_contrat WHERE active=1 AND fk_salarie=".$fk_salarie;
				$res_contrat = $db->query($sql_contrat);
				if($res_contrat)
					if($db->num_rows($res_contrat)){
						$obj_contrat = $db->fetch_object($res_contrat);
						$sql_salaire_net  = "SELECT salaire_net FROM ".MAIN_DB_PREFIX."salarie_contrat_salaire_net WHERE active=1 AND fk_contrat=".$obj_contrat->rowid;
						$res_salaire_net  = $db->query($sql_salaire_net );
						
						if($db->num_rows($res_salaire_net) > 0){
							$obj_salaire_net = $db->fetch_object($res_salaire_net );
							if($obj_salaire_net->salaire_net > $net)
							    print "<script>
								   var avertissement = document.getElementById('avertissement');
								   avertissement.textContent = 'L\'ancien salaire net (".apres_virgule($db, $id_societe, $obj_salaire_net->salaire_net).") est superieur à celui-ci (".apres_virgule($db, $id_societe, $net).")';
								</script>";
							}
								
					}
				print "<table class='tagtable liste'>";
				print '<tr class="impair"><td style="padding: 10px; width: 200px;"><label>Salaire brut</label></td>';
				print '<td style="padding: 10px; width: 210px;"><input type="text" disabled id="salaire_brut" name="salaire_brut" value="'.(apres_virgule($db, $id_societe, $salaire_brut)).'"></td></tr>';					
				$index = 0;
					$global_cotis = salarie_prestation_simulation($db, $fk_salarie, $salaire_brut_cotisable, $id_convention);
					$cotis = $global_cotis[1];
					$taux_p = $global_cotis[0];
					foreach ($cotis as $key => $value) {
						$type_prest = "SELECT * FROM ".MAIN_DB_PREFIX."type_prestation WHERE rowid=".$key;
							$result_type_prest = $db->query($type_prest);
							$obj_prest_type = $db->fetch_object($result_type_prest);
							if($obj_prest_type){
								$retenu_prest_empl = $value*$salaire_brut_cotisable/100;
								$retenu_prest_patro = $taux_p[$index]*$salaire_brut_cotisable/100;
								$cout += $retenu_prest_patro;
								//print $retenu_prest_empl."<br>";

								print '<tr class="impair"><td style="padding: 10px; width: 200px;">'.$obj_prest_type->code.'</td>';
								print '<td style="padding: 10px; width: 210px;"><input type="text" disabled id="its" name="its" value="'.apres_virgule($db, $id_societe, $retenu_prest_patro).'"></td></tr>';
								print '<tr class="impair"><td style="padding: 10px; width: 200px;">'.$obj_prest_type->code.' employé</td>';
								print '<td style="padding: 10px; width: 210px;"><input type="text" disabled id="its" name="its" value="'.apres_virgule($db, $id_societe, $retenu_prest_empl).'*"></td></tr>';

								print "</tr>";
								$index ++;	
							}
							
					}	

					//Les taxe tel que CFE et TL
					$index = 0;
				$global_taxe2 = simulation_taxe2($db, $fk_salarie, $id_convention);
				$taxe2 = $global_taxe2[1];
				$taux_p = $global_taxe2[0];
				foreach ($taxe2 as $key => $value) {
					$type_taxe2 = "SELECT * FROM ".MAIN_DB_PREFIX."type_taxe WHERE rowid=".$key;
						$result_type_taxe2 = $db->query($type_taxe2);
						$obj_taxe2_type = $db->fetch_object($result_type_taxe2);
						if($obj_taxe2_type){
							$retenu_taxe2_empl = $value*$salaire_brut/100;
							$retenu_taxe2_patro = $taux_p[$index]*$salaire_brut/100;
							$cout += $retenu_taxe2_empl;
							$cout += $retenu_taxe2_patro;
							//print $retenu_taxe2_empl."<br>";

							print '<tr class="impair"><td style="padding: 10px; width: 200px;">'.$obj_taxe2_type->libelle.'</td>';
							print '<td style="padding: 10px; width: 210px;"><input type="text" disabled id="its" name="its" value="'.apres_virgule($db, $id_societe, $retenu_taxe2_patro).'"></td></tr>';
							print '<tr class="impair"><td style="padding: 10px; width: 200px;">'.$obj_taxe2_type->libelle.' employé</td>';
							print '<td style="padding: 10px; width: 210px;"><input type="text" disabled id="its" name="its" value="'.apres_virgule($db, $id_societe, $retenu_taxe2_empl).'*"></td></tr>';

							print "</tr>";	
							$index ++;
						}
						
				}

				if($sursalaire >= 0){
					$its = its_salarie($db, $fk_salarie, $salaire_brut_imposable);
					print '<tr class="impair"><td style="padding: 10px; width: 200px;">I.T.S(mensuel)</td>';
					print '<td style="padding: 10px; width: 210px;"><input type="text" disabled id="its" name="its" value="'.apres_virgule($db, $id_societe,$its[2]).'"></td></tr>';
					print "<tr class='impair'>";
					print '<form name="form" method="POST" action="'.$_SERVER['PHP_SELF'].'?mainmenu=paiementsalaire&leftmenu=salarie&id='.$fk_user.'&fk_salarie='.$fk_salarie.'&id_convention='.$id_convention.'&id_societe='.$id_societe.'">';
					print '<input type="hidden" name="token" value="'.newToken().'">';
					print '<input type="hidden" name="action" value="save_edit">';		
					print '<td style="padding: 10px; width: 200px;">Sursalaire</td>';
					//controle du sursalaire pour que même si l'utilisateur modifie la valeur la simulation ne le prend pas en compte
					print '<input type="hidden" value="'.(apres_virgule($db, $id_societe,$sursalaire?:0)).'" name="sursalaire" >';
					print '<td style="padding: 10px; width: 210px;"><input type="text" id="sursalaire" value="'.(apres_virgule($db, $id_societe, $sursalaire?:0)).'" ><input class="button" type="submit" value="Affecter à ce salarié" /></td>';
					print '</tr>';
					print '<input type="hidden" name="salaire_net" value="'.(apres_virgule($db, $id_societe,$net?:0)).'">';
					print '</form>';
					print '<tr class="impair"><td style="padding: 10px; width: 200px;"><label>Coût total</label></td>';
					print '<td style="padding: 10px; width: 210px;"><input type="text" disabled id="cout" name="cout" value="'.(apres_virgule($db, $id_societe,$cout)).'"></td></tr>';					
					print "</table>";
				}else{
					print "<script>
					var avertissement = document.getElementById('avertissement');
					avertissement.textContent = 'Avec ces informations ce salarié ne peut pas avoir ça (".apres_virgule($db, $id_societe, $net).") comme salaire net car le sursalaire est négatif (".apres_virgule($db, $id_societe,$sursalaire?:0).")';
					</script>";
				}

			}

			
		}else{
			$cout_total  = str_replace(' ', '', preg_replace('/[^0-9. ]/', '', GETPOST('cout', 'alpha')));
			//round(str_replace(' ', '', GETPOST("cout", "int ")));

			while ($fin == false && $cout_total){
				$mon_salaire_brut += $sursalaire;
				$mon_cout = $mon_salaire_brut;

				$mon_brut_cotis = $salaire_brut_cotisable + ($mon_salaire_brut - $salaire_brut);
				$mon_brut_imp = $salaire_brut_imposable + ($mon_salaire_brut - $salaire_brut);
				$retenu_prest_empl = 0;
				$retenu_prest_patro = 0;
				$inps = 0;
			
				$index = 0;
				$global_cotis = salarie_prestation_simulation($db, $fk_salarie, $mon_brut_cotis, $id_convention);
				$cotis = $global_cotis[1];
				$taux_p = $global_cotis[0];
				foreach ($cotis as $key => $value) {
					$type_prest = "SELECT * FROM ".MAIN_DB_PREFIX."type_prestation WHERE rowid=".$key;
						$result_type_prest = $db->query($type_prest);
						$obj_prest_type = $db->fetch_object($result_type_prest);
						if($obj_prest_type){
							$retenu_prest_empl += $value*$mon_brut_cotis/100;
							$retenu_prest_patro += $taux_p[$index]*$mon_brut_cotis/100;
						}
						//print $retenu_prest_empl."<br>";
						if($obj_prest_type->rowid != 6)
							$inps += $value*$mon_brut_cotis/100;
					$index ++;
				}

				$index = 0;
				$global_taxe2 = simulation_taxe2($db, $fk_salarie, $id_convention);
				$taxe2 = $global_taxe2[1];
				$taux_p = $global_taxe2[0];
				foreach ($taxe2 as $key => $value) {
					$type_taxe2 = "SELECT * FROM ".MAIN_DB_PREFIX."type_taxe WHERE rowid=".$key;
						$result_type_taxe2 = $db->query($type_taxe2);
						$obj_taxe2_type = $db->fetch_object($result_type_taxe2);
						if($obj_taxe2_type){
							$retenu_taxe2_empl = $value*$salaire_brut/100;
							$retenu_taxe2_patro = $taux_p[$index]*$salaire_brut/100;
							$mon_cout += $retenu_taxe2_empl;
							$mon_cout += $retenu_taxe2_patro;
							//print $retenu_taxe2_empl."<br>";

							$index ++;
						}
						
				}

				$mon_brut_imp -= $inps;
				$its = its_salarie($db, $fk_salarie, $mon_brut_imp);
				$retenu_taxe = $its[2];
				$mon_cout += $retenu_prest_patro;
				$mon_net = $mon_salaire_brut - $retenu_prest_empl - $retenu_taxe;

				
					if(round($mon_cout + 100000) < ((int)$cout_total))
						$sursalaire =  50000;
					elseif(round($mon_cout+ 10000) < ($cout_total))
						$sursalaire =  5000;
					elseif(round($mon_cout + 1000) < ($cout_total))
						$sursalaire =  500;
					elseif(round($mon_cout+ 100) < ($cout_total))
						$sursalaire = 20;
					elseif(round($mon_cout) < $cout_total)
						$sursalaire = 5;
					elseif(round($mon_cout) == round($cout_total))
						$fin = true;
					elseif(round($mon_cout) > ($cout_total + 20000))
						$sursalaire = -1000;
					elseif(round($mon_cout) > ($cout_total + 1000))
						$sursalaire = -100;
					elseif(round($mon_cout) > ($cout_total + 500))
						$sursalaire = -50;
					else $sursalaire = -1;

			}

			if ($fin){

				$salaire_brut_cotisable = $mon_brut_cotis ;
				$salaire_brut_imposable = $mon_brut_imp;
				$sursalaire = $mon_salaire_brut -  $salaire_brut;
				$mon_cout = $salaire_brut + ($mon_salaire_brut -  $salaire_brut);

				//Avertissement sur le salaire net
				$sql_contrat = "SELECT rowid FROM ".MAIN_DB_PREFIX."salarie_contrat WHERE active=1 AND fk_salarie=".$fk_salarie;
				$res_contrat = $db->query($sql_contrat);
				if($res_contrat)
					if($db->num_rows($res_contrat)){
						$obj_contrat = $db->fetch_object($res_contrat);
						$sql_salaire_net  = "SELECT salaire_net FROM ".MAIN_DB_PREFIX."salarie_contrat_salaire_net WHERE active=1 AND fk_contrat=".$obj_contrat->rowid;
						$res_salaire_net  = $db->query($sql_salaire_net );
						
						if($db->num_rows($res_salaire_net) > 0){
							$obj_salaire_net = $db->fetch_object($res_salaire_net );
							if($obj_salaire_net->salaire_net > $mon_net)
							    print "<script>
								   var avertissement = document.getElementById('avertissement');
								   avertissement.textContent = 'L\'ancien salaire net (".apres_virgule($db, $id_societe, $obj_salaire_net->salaire_net).") est superieur à celui-ci (".apres_virgule($db, $id_societe, $mon_net).")';
								</script>";
							}
								
					}

				print "<table class='tagtable liste'>";
				print '<tr class="impair"><td style="padding: 10px; width: 200px;"><label>Salaire brut</label></td>';
				print '<td style="padding: 10px; width: 210px;"><input type="text" disabled id="salaire_brut" name="salaire_brut" value="'.($mon_salaire_brut).'"></td></tr>';					
				$index = 0;
					$global_cotis = salarie_prestation_simulation($db, $fk_salarie, $salaire_brut_cotisable, $id_convention);
					$cotis = $global_cotis[1];
					$taux_p = $global_cotis[0];
					foreach ($cotis as $key => $value) {
						$type_prest = "SELECT * FROM ".MAIN_DB_PREFIX."type_prestation WHERE rowid=".$key;
							$result_type_prest = $db->query($type_prest);
							$obj_prest_type = $db->fetch_object($result_type_prest);
							if($obj_prest_type){
								$retenu_prest_empl = $value*$salaire_brut_cotisable/100;
								$retenu_prest_patro = $taux_p[$index]*$salaire_brut_cotisable/100;
								$mon_cout += $retenu_prest_patro;
								//print $taux_p[$index]."--".$retenu_prest_patro."<br>";

								print '<tr class="impair"><td style="padding: 10px; width: 200px;">'.$obj_prest_type->code.'</td>';
								print '<td style="padding: 10px; width: 210px;"><input type="text" disabled id="its" name="its" value="'.apres_virgule($db, $id_societe, $retenu_prest_patro).'"></td></tr>';
								print '<tr class="impair"><td style="padding: 10px; width: 200px;">'.$obj_prest_type->code.' employé</td>';
								print '<td style="padding: 10px; width: 210px;"><input type="text" disabled id="its" name="its" value="'.apres_virgule($db, $id_societe, $retenu_prest_empl).'*"></td></tr>';

								print "</tr>";
								$index ++;
							}
							
					}	

				if($sursalaire >= 0){
					$its = its_salarie($db, $fk_salarie, $salaire_brut_imposable);
					print '<tr class="impair"><td style="padding: 10px; width: 200px;">I.T.S(mensuel)</td>';
					print '<td style="padding: 10px; width: 210px;"><input type="text" disabled id="its" name="its" value="'.apres_virgule($db, $id_societe, $its[2]).'"></td></tr>';
					print "<tr class='impair'>";
					print '<form name="form" method="POST" action="'.$_SERVER['PHP_SELF'].'?mainmenu=paiementsalaire&leftmenu=salarie&id='.$fk_user.'&fk_salarie='.$fk_salarie.'&id_convention='.$id_convention.'&id_societe='.$id_societe.'">';
					print '<input type="hidden" name="token" value="'.newToken().'">';
					print '<input type="hidden" name="action" value="save_edit">';	
					print '<input type="hidden" name="salaire_net" value="'.(apres_virgule($db, $id_societe, $mon_net?:0)).'">';	
					print '<td style="padding: 10px; width: 200px;">Sursalaire</td>';
					//controle du sursalaire pour que même si l'utilisateur modifie la valeur la simulation ne le prend pas en compte
					print '<input type="hidden" value="'.(apres_virgule($db, $id_societe,$sursalaire?:0)).'" name="sursalaire" >';
					print '<td style="padding: 10px; width: 210px;"><input type="text" id="sursalaire" value="'.(apres_virgule($db, $id_societe,$sursalaire?:0)).'" ><input class="button" type="submit" value="Affecter à ce salarié" /></td>';
					print '</tr>';
					print '<tr class="impair"><td style="padding: 10px; width: 200px;"><label>Salaire net</label></td>';
					print '<td style="padding: 10px; width: 210px;"><input type="text" disabled name="salaireNet" value="'.(apres_virgule($db, $id_societe, $mon_net)).'"></td></tr>';					

					print '</form>';
					print "</table>";
				}else{
					print "<script>
					var avertissement = document.getElementById('avertissement');
					avertissement.textContent = 'Avec ces informations ce salarié ne peut pas avoir ça (".apres_virgule($db, $id_societe, $cout_total).") comme cout total car le sursalaire est négatif (".apres_virgule($db, $id_societe,$sursalaire?:0).")';
					</script>";
				}

			}

		}

		print "</div>";
			print "</div>";
	}

	}else 	print "<h2> Permission manquante </h2>";

	if(!empty($message))
			print "<script>
			$.jnotify('".$message."', {delay : 5000, fadeSpeed: 500});
			</script>";
		/*
		
		*/
}

function apres_virgule($db, $id_societe, $valeur){
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