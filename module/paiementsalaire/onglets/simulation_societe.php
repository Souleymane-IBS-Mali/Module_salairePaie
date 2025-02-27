<?php
require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/custom/paiementsalaire/lib/paiementsalaire.lib.php';


llxHeader("", "Paiement | Salaire");
//Titre 
print load_fiche_titre($langs->trans("Simulation du Salaire Net"), '', '');
//print '<hr>';

		$id_societe = GETPOST('id_societe','int');
		$id_convention = GETPOST('id_convention','int');
		$id_salaire_base = GETPOST("categories","alpha") ? : 0;

		
		$head = paiementsalaireSocieteHead($id_societe, $id_convention);
		print dol_get_fiche_head($head, 'simulation', "", -1, '');
		
		$soc_sql = "SELECT * FROM ".MAIN_DB_PREFIX."societe WHERE rowid=".$id_societe;
$soc_res = $db->query($soc_sql);//= $db->query($covSql);
$obj_soc = $db->fetch_object($soc_res);
$obj_soc->name = $obj_soc->nom;
$obj_soc->element = "societe";			
$obj_soc->conv = $id_convention;

societe_preview_next($db, $id_societe, $obj_soc);
entete_societe($obj_soc, 'societe');
if($user->rights->paiementsalaire->societe->write){
	print "<div style='display:flex; flex:2; flex-direction:row;'>";
		print "<div style='flex:1;margin-right: 20px;'>";
		if(((GETPOST("salaire_brut", "int") == 0 || GETPOST("salaire_brut", "int") < 0) && $id_salaire_base !=0 && $type_simulation == "salaire_brut"))
			print "<mark><strong>Le champ 'SALAIRE BRUT' est obligatoire</strong></mark><br>";
		elseif(((GETPOST("cout", "int") == 0 || GETPOST("cout", "int") < 0) && $id_salaire_base !=0 && $type_simulation == "cout"))
			print "<mark><strong>Le champ 'COUT TOTAL' est obligatoire</strong></mark><br>";
		elseif(((GETPOST("salaire_net", "int") == 0 || GETPOST("salaire_net", "int") < 0) && $id_salaire_base !=0 && $type_simulation == "salaire_net"))
			print "<mark><strong>Le champ 'SALAIRE NET' est obligatoire</strong></mark><br>";
		print '<form id="add_form" method="POST" action="'.$_SERVER['PHP_SELF'].'?mainmenu=paiementsalaire&leftmenu=salarie&id_societe='.$id_societe.'&id_convention='.$id_convention.'&id='.$id_salarie.'&matricule='.$matricule_salarie.'">';
		print '<input type="hidden" name="token" value="'.newToken().'">';
		print '<input type="hidden" name="action" value="">';	
		print '<table class="tagtable liste" style="margin-bottom: 0px;">';
		print '<tr>';	
		print '<tr class="impair" ><td style="padding: 10px; width: 200px;">Catégorie</td>';

		print '<td style="padding: 10px; width: 200px;"><select id="categories" name="categories">';

		$catSql = "SELECT * FROM ".MAIN_DB_PREFIX."dcategories WHERE fk_convention=".$id_convention;

		$result = $db->query($catSql);

		$aff = true;
		if($result){
			$i = 0;
			print $catSql;
			$num = $db->num_rows($result);
			while ($i < $num){
				$obj1 = $db->fetch_object($result);

				$echelon_SQL = "SELECT * FROM ".MAIN_DB_PREFIX."echelon WHERE fk_categorie=".$obj1->rowid;
				$echelon_result = $db->query($echelon_SQL);
				$num_echelon = $db->num_rows($echelon_result);
				if($num_echelon > 0){
					$a = 0;
					while ($a < $num_echelon) {
						$obj_echelon = $db->fetch_object($echelon_result);
						if($id_salaire_base == 0){
							if($i == 0 && $a == 0){
								print '<option value="'.$obj1->rowid.'_'.$obj_echelon->rowid.'" selected>'.$obj1->code_categorie.' ==> '.$obj_echelon->libelle.'</option>';
								if($aff){
									$aff = false;
									$id_salaire_base = $obj1->rowid.'_'.$obj_echelon->rowid;
								}
							}else{
								print '<option value="'.$obj1->rowid.'_'.$obj_echelon->rowid.'">'.$obj1->code_categorie.' ==> '.$obj_echelon->libelle.'</option>';
								if($aff){
									$aff = false;
									$id_salaire_base = $obj1->rowid.'_'.$obj_echelon->rowid;
								}
							}
						}else{
							$tab = explode('_', $id_salaire_base);
							if(count($tab) > 1){
								if($obj1->rowid == $tab[0] && $obj_echelon->rowid == $tab[1])
									print '<option value="'.$obj1->rowid.'_'.$obj_echelon->rowid.'" selected>'.$obj1->code_categorie.' ==>'.$obj_echelon->libelle.'</option>';
								else
									print '<option value="'.$obj1->rowid.'_'.$obj_echelon->rowid.'">'.$obj1->code_categorie.' ==>'.$obj_echelon->libelle.'</option>';
						
							}else{
									print '<option value="'.$obj1->rowid.'_'.$obj_echelon->rowid.'">'.$obj1->code_categorie.' ==>'.$obj_echelon->libelle.'</option>';
							}
						}
						$a++;
					}
				}else
				if($id_salaire_base == 0){
					if($i == 0){
						print '<option value="'.$obj1->rowid.'" selected>'.$obj1->code_categorie.'</option>';
						if($aff){
							$aff = false;
							$id_salaire_base = $obj1->rowid;
						}
		
					}else{
						print '<option value="'.$obj1->rowid.'">'.$obj1->code_categorie.'</option>';
						if($aff){
							$aff = false;
							$id_salaire_base = $obj1->rowid;
						}
					}
				}else 
						if($obj1->rowid == $id_salaire_base){
							print '<option value="'.$obj1->rowid.'" selected>'.$obj1->code_categorie.'</option>';		
						}else{
							print '<option value="'.$obj1->rowid.'">'.$obj1->code_categorie.'</option>';
						}
				$i ++;
			}
		}

		print '</select></td>';
		print '</tr>';
		$tab = explode('_', $id_salaire_base);
	//Recherche du salaire de base
		$grilleSql = "SELECT rowid FROM ".MAIN_DB_PREFIX."grille WHERE active=1 AND fk_convention=".$id_convention;
		$grilleResult = $db->query($grilleSql);//= $db->query($grilleSql);
		$obj_grille = $db->fetch_object($grilleResult);

		$tab = explode('_', $id_salaire_base);
		$echelon = 0;
		$categ = 0;
		if(count($tab) == 2){
			$categ = $tab[0];
			$echelon = $tab[1];
		}else $categ = $tab[0];

		$salBaseSql = "SELECT salaire_base FROM ".MAIN_DB_PREFIX."grille_categorie_echelon_salaire_base WHERE fk_grille=".$obj_grille->rowid." AND fk_categorie=".$categ." AND fk_echelon=".$echelon;
		$salBaseResult = $db->query($salBaseSql);//= $db->query($covSql);
		$objSalBase = $db->fetch_object($salBaseResult);
		$salaire_base = $objSalBase->salaire_base;

		$ind_array = salarie_indemnite_simulation($db, '', $salaire_base, $tab[0],0,$id_convention);
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

		$pr_array = salarie_prime_simulation($db, 0, $salaire_base, $tab[0],0, $id_convention);
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

		print '<tr class="impair">';
		print '<td style="padding: 10px; width: 200px;">Salaire de Base</td>';
		print '<td style="padding: 10px; width: 210px;"><input type="text" disabled id="salaire_base" name="salaire_base" value="'.$salaire_base.'" ></td>';
		
		print '</tr>';

		$val_aciennete = GETPOST("anciennete", "int")?:0;
		//les salaires
		$salaire_brut_imposable = 0;
		$salaire_brut_cotisable = 0;
		$salaire_brut = 0;

		$verif_sql = "SELECT * FROM ".MAIN_DB_PREFIX."primes WHERE rowid=1";
		$verif_result = $db->query($verif_sql);
		if($verif_result){
			$verif_obj = $db->fetch_object($verif_result);
			if($verif_obj->exonere == "Oui" || $verif_obj->exonere == "oui")
				$salaire_base -= $val_aciennete;

				$salaire_brut += $val_aciennete;

			if($verif_obj->soumis_cotisation == "Oui" || $verif_obj->soumis_cotisation == "oui")
				$salaire_brut_imposable += $val_aciennete;
			if($verif_obj->soumis_impot == "Oui" || $verif_obj->soumis_impot == "oui")
				$salaire_brut_imposable += $val_aciennete;

		}

		print '<tr class="impair">';
		print '<td style="padding: 10px; width: 200px;">Ancienneté</td>';
		print '<td style="padding: 10px; width: 200px;"><select id="anciennete" name="anciennete" >'; 
		for ($i=0; $i < 20; $i++) { 
			if($val_aciennete != 0 && ((anciennete_valeur($db, $i, $id_convention)*$salaire_base/100) == $val_aciennete)){
				print "<option value='".(anciennete_valeur($db, $i, $id_convention)*$salaire_base/100)."' selected>".$i."an(s) = ".(anciennete_valeur($db, $i, $id_convention)*$salaire_base/100)."</option>";
			}else 
			print "<option value='".(anciennete_valeur($db, $i, $id_convention)*$salaire_base/100)."'>".$i."an(s) (".anciennete_valeur($db, $i, $id_convention)."%".$salaire_base.") = ".(anciennete_valeur($db, $i, $id_convention)*$salaire_base/100)."</option>";
		}
		print "</select>";
		print '</td></tr>';
		print '<tr class="impair" >';
		$info = "Une prime|indemnité est divisée en trois(3) parties séparés par ( _ ): valeur, soumies aux impôts et soumis à cotisation; Si elle est soumise aux impôts mettez 1 sinon 0; Si elle est soumise aux cotisations mettez 1 sinon 0;         ex: 2000_1_0; 8000_0_0; 5000_0_1;   pour trois(3) prime|indemnite";
		print '<td style="padding: 10px; width: 200px;">Primes|Indemnités</td>';
		print '<td style="padding: 10px; width: 200px;"><textarea name="array_prime_indemnite" size="5" rows="1" placeholder="ex: 5000_1_1; 12000_1_0;" cols="30">'.GETPOST("array_prime_indemnite").'</textarea>'.info_admin($info, 1);
		print '</td></tr>';

		$marie ="";
		$celib = "";
		$divorce = "";
		if(GETPOST("statut_f", "alpha") == "marie")
			$marie = "selected";
		else if(GETPOST("statut_f", "alpha") == "divorce")
			$divorce = "selected";
		else $celib = "selected";

		print '<tr class="impair">';
		print '<td style="padding: 10px; width: 200px;">Situation Familiale</td>';
		print '<td style="padding: 10px; width: 200px;"><select id="statut_f" name="statut_f">
			<option value="marie" '.$marie.'>Marié</option>
			<option value="divorce" '.$divorce.' >Divorcé</option>
			<option value="celibataire" '.$celib.' >Célibataire</option>
		</select></td>';
		print '</tr>';

		print '<tr class="impair" >';
		print '<td style="padding: 10px; width: 200px;">Nombre enfant à charge</td>';
		print '<td style="padding: 10px; width: 200px;"><input id="nb_enfant" value="'.(GETPOST("nb_enfant", "int")?:"0").'" name="nb_enfant" type="number" min="0" max="10" size="5"></td>';
		print '</tr>';

		print '<tr class="impair" >';
		print '<td style="padding: 10px; width: 200px;">Nombre enfant Handicapé</td>';
		print '<td style="padding: 10px; width: 200px;"><input id="nombre_enfant_hand" value="'.(GETPOST("nombre_enfant_hand", "int")?:"0").'" name="nombre_enfant_hand" type="number" min="0" max="10" size="5" ></td>';
		print '</tr>';
		//--------------------------------------------------------------------------------------------
		$salaire_brut_imposable += $salaire_base;
		$salaire_brut_cotisable += $salaire_base;
		$salaire_brut += $salaire_base;
		
		$salaire_net = 0;
		$retenu_prest_empl = 0;
		$retenu_prest_patro = 0;
		$retenu_taxe = 0;
		$retenu = 0;


		$pr_array = salarie_prime_simulation($db, 0, $salaire_base, $tab[0],0, $id_convention);
		foreach ($pr_array as $key => $value) {
		if(!empty($key) && !empty($value)){
			//$somme += $value;
			$sql = "SELECT * FROM ".MAIN_DB_PREFIX."primes WHERE rowid=".$key;
			$prime_res = $db->query($sql);
			if($prime_res){
				$pr = $db->fetch_object($prime_res);

				if($pr->exonere == "oui"){
					if($pr->soumis_cotisation=="Oui")
						$salaire_brut_cotisable += $value;

					if($pr->soumis_impot=="Oui")
					$salaire_brut_imposable += $value;

				}else{
					if($pr->soumis_cotisation=="Oui")
						$salaire_brut_cotisable += $value;

					if($pr->soumis_impot=="Oui")
					$salaire_brut_imposable += $value;

					$salaire_brut += $value;

				}

				//print "<br> Nom = ".$pr->libelle." afficher sur bulletin=".$pr->affiche_bulletin."=>".$value;
			}
		}
		}

		
		

		$pr_fl = prime_flottante($db, $obj_salarie->fk_salarie);
		foreach ($pr_fl as $key => $value) {
		if(!empty($key) && !empty($value)){
			$sql = "SELECT * FROM ".MAIN_DB_PREFIX."indemnite WHERE rowid=".$key;
			$prime_res = $db->query($sql);
			if($prime_res){
				$pr = $db->fetch_object($prime_res);

				$salaire_brut += $value;

				//print "<br> Nom = ".$pr->libelle." afficher sur bulletin=".$pr->affiche_bulletin."=>".$value;
			}
		}
		}



		//les indemnités qui doivent être affichés sur le billetin
		//Indemnités
		$ind_array = salarie_indemnite_simulation($db, '', $salaire_base, $tab[0],0, $id_convention);
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
				//print "<br> Nom = ".$ind->libelle." afficher sur bulletin=".$ind->affiche_bulletin."=>".$value;
			}

		}
		}


		$ind_array = indemnite_flottante($db, $obj_salarie->fk_salarie);
		foreach ($ind_array as $key => $value) {
		if(!empty($key) && !empty($value)){
			$sql = "SELECT * FROM ".MAIN_DB_PREFIX."indemnite WHERE rowid=".$key;
			$ind_res = $db->query($sql);
			if($ind_res){

				$ind = $db->fetch_object($ind_res);
				if($ind->soumis_cotisation=="Oui"){//les indemnités soumisent aux cotisations
					if(!empty($ind->porcentage_soumis_cotis))
						$salaire_brut_cotisable += ($value*$ind->porcentage_soumis_cotis)/100;
				}
				if($ind->soumis_impot=="Oui")////les indemnités soumisent aux impôt
					if(!empty($ind->porcentage_soumis_impot))
						$salaire_brut_imposable += ($value*$ind->porcentage_soumis_impot)/100;

				$salaire_brut += $value;
				//print "<br> Nom = ".$ind->libelle." afficher sur bulletin=".$ind->affiche_bulletin."=>".$value;
			}
		}
		}
	//Primes et indemnites simulation
	if(!empty(GETPOST("array_prime_indemnite"))){
		$all_pr_ind = explode(";", GETPOST("array_prime_indemnite"));
		for ($i=0; $i < count($all_pr_ind); $i++) {
			$prime_ind = explode("_", $all_pr_ind[$i]);
			if(!empty($all_pr_ind[$i])){
				$salaire_brut += $prime_ind[0];
				if($prime_ind[1] == 1){
					$salaire_brut_imposable += $prime_ind[0];
					//print $prime_ind[0]."-i--".$prime_ind[1]."---".$prime_ind[2]."<br>";

				}
				if($prime_ind[2] == 1){
					$salaire_brut_cotisable += $prime_ind[0];
					//print $prime_ind[0]."--c-".$prime_ind[1]."---".$prime_ind[2]."<br>";

				}
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
		print '<table class="tagtable liste" id="div_brut" >';
			print '<tr class="impair"><td style="padding: 10px; width: 25%;">Salaire brut</td>';
			print '<td style="padding: 10px; width: 25%;">';
			print '<input type="text" id="salaire_brut" value="'.(GETPOST("salaire_brut", "int")?:0).'" name="salaire_brut" ></td></tr>';
		print '</table>';

		print '<table class="tagtable liste" id="div_net">';
			print '<tr class="impair"><td style="padding: 10px; width: 25%;">Salaire net</td>';
			print '<td style="padding: 10px; width: 25%;">';
			print '<input type="text" id="salaire_net" value="'.(GETPOST("salaire_net", "int")?:0).'" name="salaire_net" ></td></tr>';
		print '</table>';

		print '<table class="tagtable liste" id="div_cout_tot">';
			print '<tr class="impair"><td style="padding: 10px; width: 25%;">Cout Total</td>';
			print '<td style="padding: 10px; width: 25%;">';
			print '<input type="text" id="cout" value="'.(GETPOST("cout", "int")?:0).'" name="cout" ></td></tr>';
		print '</table>';
		print '<input style="margin-top:40px; margin-left:400px;" type="submit" class="button" value="Simuler" >';
		
		print '</form>';
		print "</div>";


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
		$mon_salaire_brut = $salaire_brut;
		$mon_brut_cotis = 0;
		$mon_brut_imp = 0;
		$mon_net = 0;
		$fin = false;

		$sursalaire = 0;

	if($type_simulation == 'salaire_brut'){
		$mon_salaire_brut  = GETPOST("salaire_brut", "int");
		if(!empty(GETPOST("salaire_brut", "int"))){
			$sursalaire = $mon_salaire_brut - $salaire_brut;
			$inps = 0;
			$retenu = 0;
			$cout = $mon_salaire_brut;
				$salaire_brut_cotisable += $sursalaire;
				$salaire_brut_imposable += $sursalaire;
				print "<table class='tagtable liste'>";
				$brut = "salaire brut calculé + sursalaire = ".$salaire_brut." + ".$sursalaire." = ".$mon_salaire_brut;
				print '<tr class="impair"><td style="padding: 10px; width: 200px;"><label>Salaire brut'.info_admin("Le salaire brut obtenu après le calcul du salaire de ce salarié, ".$brut,1).'</label></td>';
				print '<td style="padding: 10px; width: 210px;"><input type="text" disabled id="salaire_net" name="salaire_net" value="'.($salaire_brut).'"></td></tr>';					
				$index = 0;
					$global_cotis = salarie_prestation_simulation($db, $fk_salarie, $salaire_brut_cotisable, $id_convention);
					$cotis = $global_cotis[1];
					$taux_p = $global_cotis[0];
					foreach ($cotis as $key => $value) {
						$type_prest = "SELECT * FROM ".MAIN_DB_PREFIX."type_prestation WHERE rowid=".$key;
							$result_type_prest = $db->query($type_prest);
							$obj_prest_type = $db->fetch_object($result_type_prest);
							if($obj_prest_type){
								$retenu_prest_empl = round($value*$salaire_brut_cotisable/100, 2);
								$retenu_prest_patro = round($taux_p[$index]*$salaire_brut_cotisable/100, 2);
								$cout += $retenu_prest_patro;
								//print $retenu_prest_empl."<br>";
		
								print '<tr class="impair"><td style="padding: 10px; width: 200px;">'.$obj_prest_type->code.'</td>';
								print '<td style="padding: 10px; width: 210px;"><input type="text" disabled id="its" name="its" value="'.$retenu_prest_patro.'"></td></tr>';
								print '<tr class="impair"><td style="padding: 10px; width: 200px;">'.$obj_prest_type->code.' employé</td>';
								print '<td style="padding: 10px; width: 210px;"><input type="text" disabled id="its" name="its" value="'.$retenu_prest_empl.'*"></td></tr>';
		
								print "</tr>";	
								if($obj_prest_type->rowid != 6)
									$inps += $value*$salaire_brut_cotisable/100;
								
								$retenu	+= $value*$salaire_brut_cotisable/100;
								$index ++;
							}
							
					}	
		
					$salaire_brut_imposable -= $inps;
					$its = $its = its_salarie($db, $fk_salarie, $mon_brut_imp, GETPOST("statut_f", "alpha"), GETPOST("nb_enfant", "int"), GETPOST("nombre_enfant_hand", "int"));;
					$retenu += $its[2];
					$mon_net = $mon_salaire_brut - $retenu;
				print '<tr class="impair"><td style="padding: 10px; width: 200px;">I.T.S(mensuel)</td>';
				print '<td style="padding: 10px; width: 210px;"><input type="text" disabled id="its" name="its" value="'.round($its[2]).'"></td></tr>';
				print "<tr class='impair'>";
						
				print '<td style="padding: 10px; width: 200px;">Sursalaire</td>';
				print '<td style="padding: 10px; width: 210px;"><input type="text" id="sursalaire" value="'.($sursalaire?:0).'" name="sursalaire" ></td>';
				print '</tr>';
				print '<tr class="impair"><td style="padding: 10px; width: 200px;"><label>Salaire net</label></td>';
				print '<td style="padding: 10px; width: 210px;"><input type="text" disabled id="salaire_net" name="salaire_net" value="'.round($mon_net).'"></td></tr>';					
				print '<tr class="impair"><td style="padding: 10px; width: 200px;"><label>Coût total</label></td>';
				print '<td style="padding: 10px; width: 210px;"><input type="text" disabled id="salaire_net" name="salaire_net" value="'.($cout).'"></td></tr>';					
				print "</table>";
		
				}
		
	}elseif($type_simulation == 'salaire_net'){

		$net  = GETPOST("salaire_net", "int");
		while ($fin == false && $net){
			$mon_salaire_brut += $sursalaire;
			$mon_brut_cotis = $salaire_brut_cotisable + ($mon_salaire_brut - $salaire_brut);
			$mon_brut_imp = $salaire_brut_imposable + ($mon_salaire_brut - $salaire_brut);
			$retenu_prest_empl = 0;
			$retenu_prest_patro = 0;
			$inps = 0;
		
			$index = 0;
			$global_cotis = salarie_prestation_simulation($db, $obj_salarie->fk_salarie, $mon_brut_cotis, $id_convention);
			$cotis = $global_cotis[1];
			$taux_p = $global_cotis[0];
			foreach ($cotis as $key => $value) {
				$type_prest = "SELECT * FROM ".MAIN_DB_PREFIX."type_prestation WHERE rowid=".$key;
					$result_type_prest = $db->query($type_prest);
					$obj_prest_type = $db->fetch_object($result_type_prest);
					if($obj_prest_type){
						$retenu_prest_empl += round($value*$mon_brut_cotis/100, 2);
						$retenu_prest_patro += round($taux_p[$index]*$mon_brut_cotis/100, 2);
						//print $retenu_prest_empl."<br>";
					}
					if($obj_prest_type->rowid != 6)
						$inps += $value*$mon_brut_cotis/100;
					
			}
			$mon_brut_imp -= $inps;
			$its = its_salarie($db, $fk_salarie, $mon_brut_imp, GETPOST("statut_f", "alpha"), GETPOST("nb_enfant", "int"), GETPOST("nombre_enfant_hand", "int"));
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
		$retenu_prest_empl = 0;
		$retenu_prest_patro = 0;
		if ($fin){
			$salaire_brut_cotisable = $mon_brut_cotis ;
			$salaire_brut_imposable = $mon_brut_imp;
			$sursalaire = $mon_salaire_brut -  $salaire_brut;
			$salaire_brut = $mon_salaire_brut;
			$cout = $mon_salaire_brut;
			print "<table class='tagtable liste'>";
			print '<tr class="impair"><td style="padding: 10px; width: 200px;"><label>Salaire brut</label></td>';
			print '<td style="padding: 10px; width: 210px;"><input type="text" disabled id="salaire_brut" name="salaire_brut" value="'.($salaire_brut).'"></td></tr>';					
			$index = 0;
				$global_cotis = salarie_prestation_simulation($db, $obj_salarie->fk_salarie, $salaire_brut_cotisable, $id_convention);
				$cotis = $global_cotis[1];
				$taux_p = $global_cotis[0];
				foreach ($cotis as $key => $value) {
					$type_prest = "SELECT * FROM ".MAIN_DB_PREFIX."type_prestation WHERE rowid=".$key;
						$result_type_prest = $db->query($type_prest);
						$obj_prest_type = $db->fetch_object($result_type_prest);
						if($obj_prest_type){
							$retenu_prest_empl = round($value*$salaire_brut_cotisable/100, 2);
							$retenu_prest_patro = round($taux_p[$index]*$salaire_brut_cotisable/100, 2);
							$cout += $retenu_prest_patro;
							//print $retenu_prest_empl."<br>";


							print '<tr class="impair"><td style="padding: 10px; width: 200px;">'.$obj_prest_type->code.'</td>';
							print '<td style="padding: 10px; width: 210px;"><input type="text" disabled id="its" name="its" value="'.$retenu_prest_patro.'"></td></tr>';
							print '<tr class="impair"><td style="padding: 10px; width: 200px;">'.$obj_prest_type->code.' employé</td>';
							print '<td style="padding: 10px; width: 210px;"><input type="text" disabled id="its" name="its" value="'.$retenu_prest_empl.'*"></td></tr>';

							print "</tr>";	
						}
						
				}	


				$its = its_salarie($db, $fk_salarie, $mon_brut_imp, GETPOST("statut_f", "alpha"), GETPOST("nb_enfant", "int"), GETPOST("nombre_enfant_hand", "int"));
				print '<tr class="impair"><td style="padding: 10px; width: 200px;">I.T.S(mensuel)</td>';
			print '<td style="padding: 10px; width: 210px;"><input type="text" disabled id="its" name="its" value="'.round($its[2]).'"></td></tr>';
			print "<tr class='impair'>";
			print '<td style="padding: 10px; width: 200px;">Sursalaire</td>';
			print '<td style="padding: 10px; width: 210px;"><input type="text" id="sursalaire" value="'.($sursalaire?:0).'" name="sursalaire" ></td>';
			print '</tr>';
			print '<tr class="impair"><td style="padding: 10px; width: 200px;"><label>Coût total</label></td>';
			print '<td style="padding: 10px; width: 210px;"><input type="text" disabled id="salaire_net" name="salaire_net" value="'.($cout).'"></td></tr>';					
			
			print "</table>";

		}

		

		
	}else{
			$cout_total  = GETPOST("cout", "int");

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
							$retenu_prest_empl += round($value*$mon_brut_cotis/100, 2);
							$retenu_prest_patro += round($taux_p[$index]*$mon_brut_cotis/100, 2);
						}
						//print $retenu_prest_empl."<br>";
						if($obj_prest_type->rowid != 6)
							$inps += $value*$mon_brut_cotis/100;
					$index ++;
				}

				$mon_brut_imp -= $inps;
				$its = $its = its_salarie($db, $fk_salarie, $mon_brut_imp, GETPOST("statut_f", "alpha"), GETPOST("nb_enfant", "int"), GETPOST("nombre_enfant_hand", "int"));;
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
								$retenu_prest_empl = round($value*$salaire_brut_cotisable/100, 2);
								$retenu_prest_patro = round($taux_p[$index]*$salaire_brut_cotisable/100, 2);
								$mon_cout += $retenu_prest_patro;
								//print $taux_p[$index]."--".$retenu_prest_patro."<br>";

								print '<tr class="impair"><td style="padding: 10px; width: 200px;">'.$obj_prest_type->code.'</td>';
								print '<td style="padding: 10px; width: 210px;"><input type="text" disabled id="its" name="its" value="'.$retenu_prest_patro.'"></td></tr>';
								print '<tr class="impair"><td style="padding: 10px; width: 200px;">'.$obj_prest_type->code.' employé</td>';
								print '<td style="padding: 10px; width: 210px;"><input type="text" disabled id="its" name="its" value="'.$retenu_prest_empl.'*"></td></tr>';

								print "</tr>";
								$index ++;
							}
							
					}	


					$its = $its = its_salarie($db, $fk_salarie, $mon_brut_imp, GETPOST("statut_f", "alpha"), GETPOST("nb_enfant", "int"), GETPOST("nombre_enfant_hand", "int"));;
				print '<tr class="impair"><td style="padding: 10px; width: 200px;">I.T.S(mensuel)</td>';
				print '<td style="padding: 10px; width: 210px;"><input type="text" disabled id="its" name="its" value="'.round($its[2]).'"></td></tr>';
				print "<tr class='impair'>";	
				print '<td style="padding: 10px; width: 200px;">Sursalaire</td>';
				print '<td style="padding: 10px; width: 210px;"><input type="text" id="sursalaire" value="'.($sursalaire?:0).'" name="sursalaire" ></td>';
				print '</tr>';
				print '<tr class="impair"><td style="padding: 10px; width: 200px;"><label>Salaire net</label></td>';
				print '<td style="padding: 10px; width: 210px;"><input type="text" disabled id="salaire_net" name="salaire_net" value="'.($mon_net).'"></td></tr>';					

				print '</form>';
				print "</table>";
	}
	}
	print "</div>";
		print "</div>";
}else 	print "<h2> Permission manquante </h2>";

	
		
print '<script type="text/javascript">
		var categories = document.getElementById("categories");
		var salaire_base = document.getElementById("salaire_base");
		var sursalaire = document.getElementById("sursalaire");
		var salaire_net = document.getElementById("salaire_net");
		var cotisation = document.getElementById("cotisation");
		var salaire_brut = document.getElementById("salaire_brut");
		var anciennete = document.getElementById("anciennete");
		var its = document.getElementById("its");		
		var type_simulation = document.getElementById("type_simulation");
		var form = document.getElementById("add_form");

		categories.addEventListener("change", function () {
			var typ_sim = type_simulation.value;
			var id_salaire_base = categories.value;
			window.location.href = "'.$_SERVER["PHP_SELF"].'?id='.$id_salarie.'&id_societe='.$id_societe.'&matricule='.$matricule_salarie.'&id_convention='.$id_convention.'&categories="+id_salaire_base+"&type_simulation="+typ_sim;
		},
		false,
		);
		anciennete.addEventListener("change", function () {
			var typ_sim = type_simulation.value;
			var id_salaire_base = categories.value;
			var val_anciennet = anciennete.value;
			window.location.href = "'.$_SERVER["PHP_SELF"].'?id='.$id_salarie.'&id_societe='.$id_societe.'&matricule='.$matricule_salarie.'&id_convention='.$id_convention.'&categories="+id_salaire_base+"&anciennete="+val_anciennet+"&type_simulation="+typ_sim;


		},
		false,
		);

		/*function ajoutPrimeIndemnite(e){
			
			alert(e);
		}*/
	</script>';
	/*
	 
	*/
