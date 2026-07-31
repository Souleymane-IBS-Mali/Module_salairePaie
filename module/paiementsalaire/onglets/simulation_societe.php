<?php
require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/custom/paiementsalaire/lib/paiementsalaire.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';


llxHeader("", "Paiement | Salaire");
//Titre 
print load_fiche_titre($langs->trans("Simulation du Salaire Net"), '', '');
//print '<hr>';

		$id_societe = GETPOST('id_societe','int');
		$id_convention = GETPOST('id_convention','int');
		$id_salaire_base = GETPOST("categories","alpha") ? : 0;
		$action = GETPOST("action","alpha") ? : "";
		$type_simulation = !empty(GETPOST('type_simulation', 'alpha')) ? GETPOST('type_simulation', 'alpha') : 'salaire_net';
		$id_salarie = 0;
		$matricule_salarie = '';
		$fk_salarie = 0;
		$obj_salarie = (object) array('fk_salarie' => 0);
		$array = array();
		$atmp_patro = 0;
		$atmp_salarie = 0;
		$prestation_familiale_patro = 0;
		$prestation_familiale_salarie = 0;
		$retraite_patro = 0;
		$retraite_salarie = 0;
		$invalidite_allocation_survivant_patro = 0;
		$invalidite_allocation_survivant_salarie = 0;
		$anpe_patro = 0;
		$anpe_salarie = 0;
		$amo_patro = 0;
		$amo_salarie = 0;


		
		$head = paiementsalaireSocieteHead($id_societe, $id_convention);
		print dol_get_fiche_head($head, 'simulation', "", -1, '');
		
		$soc_sql = "SELECT * FROM ".MAIN_DB_PREFIX."societe WHERE rowid=".$id_societe;
$soc_res = $db->query($soc_sql);//= $db->query($covSql);
$obj_soc = null;
if ($soc_res) {
	$obj_soc = $db->fetch_object($soc_res);
}
if (!$obj_soc) {
	$obj_soc = new stdClass();
	$obj_soc->rowid = $id_societe;
	$obj_soc->nom = "";
}
$obj_soc->name = $obj_soc->nom;
$obj_soc->element = "societe";			
$obj_soc->conv = $id_convention;

$message = '';
societe_preview_next($db, $id_societe, $obj_soc);
entete_societe($obj_soc, 'societe');
if($user->rights->paiementsalaire->societe->write){

	$monform = new Form($db);
	//Confirmer la suppression
	if($action == "tout_effacer"){
		$url = $_SERVER['PHP_SELF']."?mainmenu=paiementsalaire&leftmenu=societe&id_societe=".$id_societe."&id_convention=".$id_convention;
		$titre = "Voulez-vous vraiment tout éffacer";

		  $formconfirm = $monform->formconfirm(
			  $url, 
			  $titre, 
			  "", 
			  'tout_effacer_oui', 
			  $array, 
			  '', 
			  1,
			  180,
			  '35%'
		  );
		  print $formconfirm;
	}

	if($action == "tout_effacer_oui"){
		$sql_del = "DELETE FROM ".MAIN_DB_PREFIX."simulation";
		if($db->query($sql_del))
			$message = "Toutes les simulations éffacées avec succès";
		else $message = $db->error();
	}

	//Confirmer la suppression
	if($action == "supprimer"){
		$id = GETPOST("id_simulation", "int");
		$url = $_SERVER['PHP_SELF']."?mainmenu=paiementsalaire&leftmenu=societe&id_societe=".$id_societe."&id_convention=".$id_convention."&id_simulation=".$id;
		$titre = "Voulez-vous vraiment cet enregistrement ?";

		  $formconfirm = $monform->formconfirm(
			  $url, 
			  $titre, 
			  "", 
			  'supprimer_oui', 
			  $array, 
			  '', 
			  1,
			  180,
			  '35%'
		  );
		  print $formconfirm;
	}

	if($action == "supprimer_oui"){
		$id = GETPOST("id_simulation", "int");

		$sql_del = "DELETE FROM ".MAIN_DB_PREFIX."simulation WHERE rowid=".$id;
		if($db->query($sql_del))
			$message = "Simulation supprimer avec succès";
		else $message = $db->error();
	}


	//Enregistrement de la simulation
	if($action == "enregistrer_simulation"){
		$libelle = GETPOST("nom_simulation", 'alpha');

		$id_categ = GETPOST("categorie", "int");
		$id_echel = GETPOST("echelon", "int");
		$anc = GETPOST("anciennete", "alpha");
		$statu_f = GETPOST("statut_f", "alpha");
		$salaire_base = GETPOST("salaire_base", "alpha");
		$nb_enfant = GETPOST("nb_enfant", "int");
		$nb_enfant_hand = GETPOST("nombre_enfant_hand", "int");

		$code_categ = "";
		$echelon = "";
		$catSql = "SELECT code_categorie FROM ".MAIN_DB_PREFIX."dcategories WHERE rowid=".$id_categ;
		$result = $db->query($catSql);
		$num = ($result ? $db->num_rows($result) : 0);
		if (0 < $num){
			$obj_code_categ = $db->fetch_object($result);
			$code_categ = $obj_code_categ ? $obj_code_categ->code_categorie : "";
			$echelon_SQL = "SELECT libelle FROM ".MAIN_DB_PREFIX."echelon WHERE rowid=".$id_echel;
			$echelon_result = $db->query($echelon_SQL);
			$num_echelon = ($echelon_result ? $db->num_rows($echelon_result) : 0);
			if($num_echelon > 0){
				$obj_echelon_save = $db->fetch_object($echelon_result);
				$echelon = $obj_echelon_save ? $obj_echelon_save->libelle : "";
			}
		}

		$sursalaire = GETPOST("sursalaire", "alpha");
		$salaire_net = GETPOST("salaire_net", "alpha");
		$salaire_brut = GETPOST("salaire_brut", "alpha");
		$salaire_brut_cotisable = GETPOST("salaire_brut_cotisable", "alpha");
		$salaire_brut_imposable = GETPOST("salaire_brut_imposable", "alpha");
		$cout_total = GETPOST("cout", "alpha");
		$its = GETPOST("its", "alpha");
		$primesindemnites = GETPOST("primesindemnites", "alpha");
		$montant_cfe = GETPOST("cfe", "alpha");
		$montant_tl = GETPOST("tl", "alpha");

		
		$global_cotis = salarie_prestation_simulation($db, $fk_salarie, $salaire_brut_cotisable?:0, $id_convention);
		$cotis = $global_cotis[1];
		$taux_p = $global_cotis[0];
		$index = 0;
		foreach ($cotis as $key => $value) {
			$type_prest = "SELECT * FROM ".MAIN_DB_PREFIX."type_prestation WHERE rowid=".$key;
			$result_type_prest = $db->query($type_prest);
			$obj_prest_type = $db->fetch_object($result_type_prest);
			if($obj_prest_type){
				//print $retenu_prest_empl."<br>";
				$code_employeur = $obj_prest_type->rowid.'employeur';
				$code_employe = $obj_prest_type->rowid.'employe';
				if($index == 0){
					$atmp_patro = GETPOST($code_employeur, "int");                            
					$atmp_salarie = GETPOST($code_employe, "int");                           
				}elseif($index == 1){
					$prestation_familiale_patro = GETPOST($code_employeur, "int");            
					$prestation_familiale_salarie = GETPOST($code_employe, "int");
				}elseif($index == 2){
					$retraite_patro = GETPOST($code_employeur, "int");                       
					$retraite_salarie = GETPOST($code_employe, "int");
				}elseif($index == 3){
					$invalidite_allocation_survivant_patro = GETPOST($code_employeur, "int"); 
					$invalidite_allocation_survivant_salarie = GETPOST($code_employe, "int");
				}elseif($index == 4){
					$anpe_patro = GETPOST($code_employeur, "int");                            
					$anpe_salarie = GETPOST($code_employe, "int");
				}elseif($index == 5){
					$amo_patro = GETPOST($code_employeur, "int");                             
					$amo_salarie = GETPOST($code_employe, "int"); 
				}
				
			}
			$index ++;

		}

		if(empty($libelle)) {
			$message = 'Le champ "NOM SIMULATION" est obligatoire';
		}

		if(empty($salaire_net)) {
			$message .= 'Le champ "SALAIRE NET" est obligatoire';
		}
		if(empty($salaire_brut)) {
			$message .= 'Le champ "SALAIRE BRUT" est obligatoire';
		}
		if(empty($sursalaire)) {
			$message .= 'Le champ "SURSALAIRE" est obligatoire';
		}
		if(empty($cout_total)) {
			$message .= 'Le champ "COUT TOTAL" est obligatoire';
		}
		/*if(empty($its)) {
			$message .= 'Le champ "ITS" est obligatoire';
		}
		if(empty($libelle)) {
			$message .= 'Le champ "NOM SIMULATION" est obligatoire';
		}
		if(empty($libelle)) {
			$message .= 'Le champ "NOM SIMULATION" est obligatoire';
		}*/
		if(empty($message)){
			$sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."simulation (libelle, situation_familiale, nombre_enfant, nombre_enfant_hand, categorie, echelon, fonction,";
			$sql_insert .= " salaire_base, sursalaire, anciennete, salaire_brut, salaire_brut_cotisable, salaire_brut_imposable, net_payer, fk_societe, nom_societe, nom_convention,";
			$sql_insert .= " atmp_patro, atmp_salarie, prestation_familiale_patro, prestation_familiale_salarie, retraite_patro, retraite_salarie, invalidite_allocation_survivant_patro,";
			$sql_insert .= " invalidite_allocation_survivant_salarie, anpe_patro, anpe_salarie, amo_patro, amo_salarie, its, cout, primesindemnites, montant_cfe, montant_tl)";
			$sql_insert .= " VALUES('".$libelle."','".$statu_f."',".$nb_enfant.",".$nb_enfant_hand.",'".$code_categ."','".$echelon."','A venir', '".$salaire_base."','".$sursalaire."','".$anc."','".$salaire_brut."','".$salaire_brut_cotisable."', '".$salaire_brut_imposable."', '".$salaire_net."', '".$id_societe."', '".$obj_soc->nom."', '".$id_convention."',
							'".$atmp_patro."', '".$atmp_salarie."', '".$prestation_familiale_patro."', '".$prestation_familiale_salarie."', '".$retraite_patro."', '".$retraite_salarie."', '".$invalidite_allocation_survivant_patro."',
							'".$invalidite_allocation_survivant_salarie."', '".$anpe_patro."', '".$anpe_salarie."', '".$amo_patro."', '".$amo_salarie."', '".$its."', '".$cout_total."', '".$primesindemnites."', '".$montant_cfe."', '".$montant_tl."')";
			
			//print $sql_insert;				
			if($db->query($sql_insert)){
				//print $sql_insert;
				$message = "Simulation enregistrée avec succès";
				//header("Location: ".$_SERVER["PHP_SELF"].'?id_societe='.$id_societe.'&id_convention='.$id_convention);
			}else print $db->error();
		}
	}
	print "<div style='display:flex; flex:2; flex-direction:row;'>";
		print "<div style='flex:1;margin-right: 20px;'>";
		if(((GETPOST("salaire_brut", "int") == 0 || GETPOST("salaire_brut", "int") < 0) && $id_salaire_base !=0 && $type_simulation == "salaire_brut"))
			print "<mark><strong>Le champ 'SALAIRE BRUT' est obligatoire</strong></mark><br>";
		elseif(((GETPOST("cout", "int") == 0 || GETPOST("cout", "int") < 0) && $id_salaire_base !=0 && $type_simulation == "cout"))
			print "<mark><strong>Le champ 'COUT TOTAL' est obligatoire</strong></mark><br>";
		elseif(((GETPOST("salaire_net", "int") == 0 || GETPOST("salaire_net", "int") < 0) && $id_salaire_base !=0 && $type_simulation == "salaire_net"))
			print "<mark><strong>Le champ 'SALAIRE NET' est obligatoire</strong></mark><br>";
		print '<form id="add_form" method="POST" action="'.$_SERVER['PHP_SELF'].'?mainmenu=paiementsalaire&leftmenu=salarie&id_societe='.$id_societe.'&id_convention='.$id_convention.'&id='.$id_salarie.'">';
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
			$num = ($result ? $db->num_rows($result) : 0);
			while ($i < $num){
				$obj1 = $db->fetch_object($result);

				$echelon_SQL = "SELECT * FROM ".MAIN_DB_PREFIX."echelon WHERE fk_categorie=".$obj1->rowid;
				$echelon_result = $db->query($echelon_SQL);
				$num_echelon = ($echelon_result ? $db->num_rows($echelon_result) : 0);
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
		$tab = explode('_', (string) $id_salaire_base);
	//Recherche du salaire de base
		$grilleSql = "SELECT rowid FROM ".MAIN_DB_PREFIX."grille WHERE active=1 AND fk_convention=".(int) $id_convention;
		$grilleResult = $db->query($grilleSql);//= $db->query($grilleSql);
		$obj_grille = ($grilleResult ? $db->fetch_object($grilleResult) : null);

		$tab = explode('_', (string) $id_salaire_base);
		$categ = isset($tab[0]) ? (int) $tab[0] : 0;
		// Dans ta logique, fk_echelon = 0 est valide.
		$echelon = (isset($tab[1]) && $tab[1] !== '') ? (int) $tab[1] : 0;
		$fk_grille = ($obj_grille ? (int) $obj_grille->rowid : 0);

		$objSalBase = null;
		$salaire_base = 0;
		if ($fk_grille > 0 && $categ > 0) {
			$salBaseSql = "SELECT salaire_base FROM ".MAIN_DB_PREFIX."grille_categorie_echelon_salaire_base WHERE fk_grille=".$fk_grille." AND fk_categorie=".$categ." AND fk_echelon=".$echelon." LIMIT 1";
			$salBaseResult = $db->query($salBaseSql);//= $db->query($covSql);
			$objSalBase = ($salBaseResult ? $db->fetch_object($salBaseResult) : null);

			// Si aucun salaire n'est trouvé, on retente explicitement avec fk_echelon = 0.
			if (!$objSalBase) {
				$salBaseSql = "SELECT salaire_base FROM ".MAIN_DB_PREFIX."grille_categorie_echelon_salaire_base WHERE fk_grille=".$fk_grille." AND fk_categorie=".$categ." AND fk_echelon=0 LIMIT 1";
				$salBaseResult = $db->query($salBaseSql);
				$objSalBase = ($salBaseResult ? $db->fetch_object($salBaseResult) : null);
			}

			$salaire_base = ($objSalBase && $objSalBase->salaire_base !== null) ? (float) $objSalBase->salaire_base : 0;
		}
		if (!isset($tab[0]) || $tab[0] === '') {
			$tab[0] = $categ;
		}

		$ind_array = salarie_indemnite_simulation($db, '', $salaire_base, $tab[0],0,$id_convention);
		foreach ($ind_array as $key => $value) {
		if(!empty($key) && !empty($value)){
			$sql = "SELECT * FROM ".MAIN_DB_PREFIX."indemnite WHERE rowid=".$key;
			$ind_res = $db->query($sql);
			if($ind_res){
				$ind = $db->fetch_object($ind_res);
				if($ind && $ind->exonere == "oui")//retiré du salaire de base
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

				if($pr && $pr->exonere == "oui")//retiré du salaire de base
					$salaire_base -= $value;

				//print "<br> Nom = ".$pr->libelle." afficher sur bulletin=".$pr->affiche_bulletin."=>".$value;
			}
		}
		}

		print '<tr class="impair">';
		print '<td style="padding: 10px; width: 200px;">Salaire de Base</td>';
		print '<td style="padding: 10px; width: 210px;"><input type="hidden" id="salaire_base" name="salaire_base" value="'.$salaire_base.'" >
		<input type="text" id="salaire_b" disabled name="salaire_b" value="'.$salaire_base.'" ></td>';
		
		
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
			if($verif_obj && ($verif_obj->exonere == "Oui" || $verif_obj->exonere == "oui"))
				$salaire_base -= $val_aciennete;

				$salaire_brut += $val_aciennete;

			if($verif_obj && ($verif_obj->soumis_cotisation == "Oui" || $verif_obj->soumis_cotisation == "oui"))
				$salaire_brut_cotisable += $val_aciennete;
			if($verif_obj && ($verif_obj->soumis_impot == "Oui" || $verif_obj->soumis_impot == "oui"))
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
		print '<td style="padding: 10px; width: 200px;"><input type="number" id="nb_enfant" name="nb_enfant" min="0" max="10" size="5" required></td>';
		print '</tr>';

		print '<tr class="impair" >';
		print '<td style="padding: 10px; width: 200px;">Nombre enfant Handicapé</td>';
		print '<td style="padding: 10px; width: 200px;"><input type="number" id="nombre_enfant_hand" name="nombre_enfant_hand" min="0" max="10" size="5" required></td>';
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


		$tab_prime_ind = array();
		$pr_array = salarie_prime_simulation($db, 0, $salaire_base, $tab[0],0, $id_convention);
		foreach ($pr_array as $key => $value) {
		if(!empty($key) && !empty($value)){
			//$somme += $value;
			$sql = "SELECT * FROM ".MAIN_DB_PREFIX."primes WHERE rowid=".$key;
			$prime_res = $db->query($sql);
			if($prime_res){
				$pr = $db->fetch_object($prime_res);

				if($pr && $pr->exonere == "oui"){
					if($pr && $pr->soumis_cotisation=="Oui")
						$salaire_brut_cotisable += $value;

					if($pr && $pr->soumis_impot=="Oui")
					$salaire_brut_imposable += $value;

				}else{
					if($pr && $pr->soumis_cotisation=="Oui")
						$salaire_brut_cotisable += $value;

					if($pr && $pr->soumis_impot=="Oui")
					$salaire_brut_imposable += $value;


				}
					$salaire_brut += $value;

					$tab_prime_ind[] = ($pr ? $pr->libelle : '')."(".$value.")";
				//print "<br> Nom = ".$pr->libelle." afficher sur bulletin=".$pr->affiche_bulletin."=>".$value;
			}
		}
		}

		
		

		$pr_fl = prime_flottante($db, $fk_salarie);
		foreach ($pr_fl as $key => $value) {
		if(!empty($key) && !empty($value)){
			$sql = "SELECT * FROM ".MAIN_DB_PREFIX."indemnite WHERE rowid=".$key;
			$prime_res = $db->query($sql);
			if($prime_res){
				$pr = $db->fetch_object($prime_res);
				if($pr && $pr->exonere == "oui"){
					if($pr && $pr->soumis_cotisation=="Oui")
						$salaire_brut_cotisable += $value;

					if($pr && $pr->soumis_impot=="Oui")
					$salaire_brut_imposable += $value;

				}else{
					if($pr && $pr->soumis_cotisation=="Oui")
						$salaire_brut_cotisable += $value;

					if($pr && $pr->soumis_impot=="Oui")
					$salaire_brut_imposable += $value;

					$salaire_brut += $value;

				}
				$salaire_brut += $value;
				$tab_prime_ind[] = ($pr ? $pr->libelle : '')."(".$value.")";

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
					if($ind && $ind->soumis_cotisation=="Oui"){//les indemnités soumisent aux cotisations
						if(!empty($ind->porcentage_soumis_cotis))
							$salaire_brut_cotisable += ($value*$ind->porcentage_soumis_cotis)/100;
					}
					if($ind && $ind->soumis_impot=="Oui")////les indemnités soumisent aux impôt
						if(!empty($ind->porcentage_soumis_impot))
							$salaire_brut_imposable += ($value*$ind->porcentage_soumis_impot)/100;
				//print "<br> Nom = ".$ind->libelle." afficher sur bulletin=".$ind->affiche_bulletin."=>".$value;
				$tab_prime_ind[] = ($ind ? $ind->libelle : '')."(".$value.")";

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

				$constr = "Prime_sim".$i."(".$prime_ind[0].") ";

				if(isset($prime_ind[1]) && $prime_ind[1] == 1){
					$salaire_brut_imposable += $prime_ind[0];
					$constr .= "imposable ";
					//print $prime_ind[0]."-i--".$prime_ind[1]."---".$prime_ind[2]."<br>";

				}else $constr .= "non imposable ";

				if(isset($prime_ind[2]) && $prime_ind[2] == 1){
					$salaire_brut_cotisable += $prime_ind[0];
					$constr .= "cotisable ";
					//print $prime_ind[0]."--c-".$prime_ind[1]."---".$prime_ind[2]."<br>";

				}else $constr .= "non cotisable ";
				$tab_prime_ind[] = $constr;

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

		//s'il n'y a pas de resultat de simulation à afficher on affiche les simulation enregistrer
	if(empty(GETPOST("type_simulation", "alpha")) || $action == "enregistrer_simulation"){
		print "<div id='partie_cache' style='flex:1; border:solid black 2px; margin-left:30px; width:50%'>";
		print "<H2 align='center'> Les simulations enregistrées</H2>";
		print "<table class='tagtable liste' style='width 100%'>";
		print '<tr class="liste_titre"><td style="padding: 10px; width: 200px;"><label>Libellé</label></td>';
		print '<td >Date</td>';					
		print '<td>Opération</td></tr>';
		
		//Formulaire de coche
		print '<form id="add_simulation" method="POST" action="../doc/export_simulation.php?mainmenu=paiementsalaire&leftmenu=salarie&id_societe='.$id_societe.'&id_convention='.$id_convention.'">';
		print '<input type="hidden" name="token" value="'.newToken().'">';
		print '<input type="hidden" name="action" value="cocher">';
		$sql_select = "SELECT rowid, libelle, date_creation FROM ".MAIN_DB_PREFIX."simulation WHERE fk_societe = ".$id_societe." ORDER BY rowid DESC";
		$res_select = $db->query($sql_select);
		$nb = ($res_select ? $db->num_rows($res_select) : 0);
		$a = 0;
		while ($a < $nb && $a < 10) {
			$obj_select = $db->fetch_object($res_select);
			$cle = "simulation".$obj_select->rowid;
			print '<tr class="impair"><td style="padding: 10px; width: 200px;"><label>'.($a + 1).'°) '.$obj_select->libelle.'</label></td>';
			print '<td >'.$obj_select->date_creation.'</td>';					
			print '<td align=center><input type="checkbox" name="'.$cle.'">&nbsp;&nbsp;<a href="'.$_SERVER['PHP_SELF'].'?mainmenu=paiementsalaire&leftmenu=salarie&id_societe='.$id_societe.'&id_convention='.$id_convention.'&id_simulation='.$obj_select->rowid.'&action=supprimer" >'.img_picto("Supprimer", "delete").'</a></td></tr>';

			$a ++;
		}

		if($nb <= 0 )
			print '<tr class="impair"><td align=center colspan=3 style="padding: 10px; width: 200px;"><label>Aucune simulation trouvée</label></td></tr>';		
		
			//les boutons
		print '<div align="right"><input class="button" type="submit" value="Cocher & Exporter" >';
		print "</form>";
		print '&nbsp;<a class="button" href="../doc/export_simulation.php?mainmenu=paiementsalaire&leftmenu=salarie&id_societe='.$id_societe.'&id_convention='.$id_convention.'&action=tout_exporter" >Tout exporter</a>';
		if($nb > 0)
			print '&nbsp;<a class="button" href="'.$_SERVER['PHP_SELF'].'?mainmenu=paiementsalaire&leftmenu=salarie&id_societe='.$id_societe.'&id_convention='.$id_convention.'&action=tout_effacer" >Tout éffacer</a>';
		print '</div>';
		print "</table>";

		print "</div>";

	}else{

		print "<div id='partie_cache' style='flex:1'>";

		//Affichage des primes indemnités utilités dans la simulation
		$m = "Les primes et indemnités utilisées : ";
		for ($i=0; $i < count($tab_prime_ind); $i++) { 
			$m .= ($i +1).") ".$tab_prime_ind[$i]." | ";
		}
		print info_admin($m, 1)."<br>";

		$mon_salaire_brut = $salaire_brut;
		$mon_brut_cotis = 0;
		$mon_brut_imp = 0;
		$mon_net = 0;
		$fin = false;

		$sursalaire = 0;
	print '<form id="add_simulation" method="POST" action="'.$_SERVER['PHP_SELF'].'?mainmenu=paiementsalaire&leftmenu=salarie&id_societe='.$id_societe.'&id_convention='.$id_convention.'">';
	print '<input type="hidden" name="token" value="'.newToken().'">';
	print '<input type="hidden" name="action" value="enregistrer_simulation">';
 	print '<input type="hidden" id="salaire_base" name="salaire_base" value="'.$salaire_base.'" >';
	print '<input type="hidden" id="type_simulation" name="type_simulation" value="'.$type_simulation.'">';
	print '<input type="hidden" id="categorie" name="categorie" value="'.$categ.'">';
	print '<input type="hidden" id="echelon" name="echelon" value="'.$echelon.'">';
	print '<input type="hidden" id="anciennete" name="anciennete" value="'.$val_aciennete.'">';
	print '<input type="hidden" id="statut_f" name="statut_f" value="'.GETPOST("statut_f", "alpha").'">';
	print '<input type="hidden" id="nb_enfant" name="nb_enfant" value="'.GETPOST("nb_enfant", "int").'">';
	print '<input type="hidden" id="nombre_enfant_hand" name="nombre_enfant_hand" value="'.GETPOST("nombre_enfant_hand", "int").'">';
	print '<input type="hidden" id="primesindemnites" name="primesindemnites" value="'.$m.'">';

	if($type_simulation == 'salaire_brut' && empty(GETPOST("nom_simulation", "alpha"))){
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
				print '<td style="padding: 10px; width: 210px;"><input type="text" id="salaire_b" name="salaire_b" value="'.($salaire_brut).'"></td></tr>';					
				$index = 0;
					$global_cotis = salarie_prestation_simulation($db, $fk_salarie, $salaire_brut_cotisable, $id_convention);
					$cotis = $global_cotis[1];
					$taux_p = $global_cotis[0];
					foreach ($cotis as $key => $value) {
						$type_prest = "SELECT * FROM ".MAIN_DB_PREFIX."type_prestation WHERE rowid=".$key;
							$result_type_prest = $db->query($type_prest);
							$obj_prest_type = $db->fetch_object($result_type_prest);
							if($obj_prest_type){
								$retenu_prest_empl = round($value*$salaire_brut_cotisable/100);
								$retenu_prest_patro = round($taux_p[$index]*$salaire_brut_cotisable/100);
								$cout += $retenu_prest_patro;
								//print $retenu_prest_empl."<br>";
		
								print '<tr class="impair"><td style="padding: 10px; width: 200px;">'.$obj_prest_type->code.'</td>';
								print '<td style="padding: 10px; width: 210px;"><input type="text" id="'.$obj_prest_type->rowid.'employeur" name="'.$obj_prest_type->rowid.'employeur" value="'.$retenu_prest_patro.'"></td></tr>';
								print '<tr class="impair"><td style="padding: 10px; width: 200px;">'.$obj_prest_type->code.'employé</td>';
								print '<td style="padding: 10px; width: 210px;"><input type="text" id="'.$obj_prest_type->rowid.'employeur" name="'.$obj_prest_type->rowid.'employe" value="'.$retenu_prest_empl.'"></td></tr>';
		
								print "</tr>";	
								if($obj_prest_type && $obj_prest_type->rowid != 6)
									$inps += $value*$salaire_brut_cotisable/100;
								
								$retenu	+= $value*$salaire_brut_cotisable/100;
							}
							$index ++;
							
					}	
		
					$salaire_brut_imposable -= $inps;
					$mon_brut_imp = $salaire_brut_imposable;
					$its = its_salarie($db, $fk_salarie, $mon_brut_imp, GETPOST("statut_f", "alpha"), GETPOST("nb_enfant", "int")?:0, GETPOST("nombre_enfant_hand", "int")?:0);
					$retenu += $its[2];
					$mon_net = $mon_salaire_brut - $retenu;
					print '<input type="hidden" id="salaire_brut" name="salaire_brut" value="'.($salaire_brut+$sursalaire).'">';
					print '<input type="hidden" id="salaire_brut_cotisable" name="salaire_brut_cotisable" value="'.($salaire_brut_cotisable).'">';
					print '<input type="hidden" id="salaire_brut_imposable" name="salaire_brut_imposable" value="'.($salaire_brut_imposable).'">';

				print '<tr class="impair"><td style="padding: 10px; width: 200px;">I.T.S(mensuel)</td>';
				print '<td style="padding: 10px; width: 210px;"><input type="text" id="its" name="its" value="'.round($its[2]).'"></td></tr>';

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

							if($index == 0){
								print '<tr class="impair"><td style="padding: 10px; width: 200px;">'.$obj_taxe2_type->libelle.'</td>';
								print '<td style="padding: 10px; width: 210px;"><input type="text" id="cfe" name="cfe" value="'.$retenu_taxe2_patro.'"></td></tr>';
								print '<tr class="impair"><td style="padding: 10px; width: 200px;">'.$obj_taxe2_type->libelle.' employé</td>';
								print '<td style="padding: 10px; width: 210px;"><input type="text" id="'.$obj_taxe2_type->libelle.'1" name="'.$obj_taxe2_type->libelle.'1" value="'.$retenu_taxe2_empl.'*"></td></tr>';
							}else{
								print '<tr class="impair"><td style="padding: 10px; width: 200px;">'.$obj_taxe2_type->libelle.'</td>';
								print '<td style="padding: 10px; width: 210px;"><input type="text" id="tl" name="tl" value="'.$retenu_taxe2_patro.'"></td></tr>';
								print '<tr class="impair"><td style="padding: 10px; width: 200px;">'.$obj_taxe2_type->libelle.' employé</td>';
								print '<td style="padding: 10px; width: 210px;"><input type="text" id="'.$obj_taxe2_type->libelle.'1" name="'.$obj_taxe2_type->libelle.'1" value="'.$retenu_taxe2_empl.'*"></td></tr>';

							}

							print "</tr>";	
							$index ++;
						}
						
				}
				
				print "<tr class='impair'>";
						
				print '<td style="padding: 10px; width: 200px;">Sursalaire</td>';
				print '<td style="padding: 10px; width: 210px;"><input type="text" id="sursalaire" value="'.($sursalaire?:0).'" name="sursalaire" ></td>';
				print '</tr>';
				print '<tr class="impair"><td style="padding: 10px; width: 200px;"><label>Salaire net</label></td>';
				print '<td style="padding: 10px; width: 210px;"><input type="text" id="salaire_net" name="salaire_net" value="'.round($mon_net).'"></td></tr>';					
				print '<tr class="impair"><td style="padding: 10px; width: 200px;"><label>Coût total</label></td>';
				print '<td style="padding: 10px; width: 210px;"><input type="text" id="cout" name="cout" value="'.($cout).'"></td></tr>';
	
				print '<tr class="impair"><td style="padding: 10px; width: 200px;"><b>Nom de la simulation</b></td>';
				print '<td style="padding: 10px; width: 210px;"><input style="border-color:blue" autofocus type="text" required id="nom_simulation" name="nom_simulation" value=""></td></tr>';

				print '<tr><td colspan=2 style="padding: 10px; width: 200px; text-align:right"><input style="margin-top:10px;" type="submit" class="button" value="Enregistrer" ></td></tr>';
				
				print "</table>";
		
				}
		
	}elseif($type_simulation == 'salaire_net' && empty(GETPOST("nom_simulation", "alpha"))){

		$net  = GETPOST("salaire_net", "int");

		$iteration = 0;
		while ($fin == false && $net && $iteration < 200000){
			$iteration++;
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
						//print $obj_prest_type->code.'='.$value."-".$taux_p[$index]."****";
						$retenu_prest_empl += round($value*$mon_brut_cotis/100, 2);
						$retenu_prest_patro += round($taux_p[$index]*$mon_brut_cotis/100, 2);
						//print $retenu_prest_empl."<br>";
					}
					if($obj_prest_type && $obj_prest_type->rowid != 6)
						$inps += $value*$mon_brut_cotis/100;
				$index ++;
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
			print '<td style="padding: 10px; width: 210px;"><input type="text" id="salaire_brut" name="salaire_brut" value="'.($salaire_brut).'"></td></tr>';					
			$index = 0;
				$global_cotis = salarie_prestation_simulation($db, $fk_salarie, $salaire_brut_cotisable, $id_convention);
				$cotis = $global_cotis[1];
				$taux_p = $global_cotis[0];
				foreach ($cotis as $key => $value) {
					$type_prest = "SELECT * FROM ".MAIN_DB_PREFIX."type_prestation WHERE rowid=".$key;
						$result_type_prest = $db->query($type_prest);
						$obj_prest_type = $db->fetch_object($result_type_prest);
						if($obj_prest_type){
							$retenu_prest_empl = round($value*$salaire_brut_cotisable/100);
							$retenu_prest_patro = round($taux_p[$index]*$salaire_brut_cotisable/100);
							$cout += $retenu_prest_patro;
							
							// Debug supprimé pour éviter un affichage parasite
							// print $obj_prest_type->code.'/'.$taux_p[$index]."//".$retenu_prest_patro;
							//print $retenu_prest_empl."<br>";


							print '<tr class="impair"><td style="padding: 10px; width: 200px;">'.$obj_prest_type->code.'</td>';
							print '<td style="padding: 10px; width: 210px;"><input type="text" id="'.$obj_prest_type->rowid.'employeur" name="'.$obj_prest_type->rowid.'employeur" value="'.$retenu_prest_patro.'"></td></tr>';
							print '<tr class="impair"><td style="padding: 10px; width: 200px;">'.$obj_prest_type->code.' employé</td>';
							print '<td style="padding: 10px; width: 210px;"><input type="text" id="'.$obj_prest_type->rowid.'employe" name="'.$obj_prest_type->rowid.'employe" value="'.$retenu_prest_empl.'"></td></tr>';

							print "</tr>";	
						}
						$index ++;
						
				}	


				$its = its_salarie($db, $fk_salarie, $mon_brut_imp, GETPOST("statut_f", "alpha"), GETPOST("nb_enfant", "int"), GETPOST("nombre_enfant_hand", "int"));
				
				print '<input type="hidden" id="salaire_net" name="salaire_net" value="'.($net).'">';
				print '<input type="hidden" id="salaire_brut_cotisable" name="salaire_brut_cotisable" value="'.($salaire_brut_cotisable).'">';
				print '<input type="hidden" id="salaire_brut_imposable" name="salaire_brut_imposable" value="'.($salaire_brut_imposable).'">';

				print '<tr class="impair"><td style="padding: 10px; width: 200px;">I.T.S(mensuel)</td>';
				print '<td style="padding: 10px; width: 210px;"><input type="text" id="its" name="its" value="'.round($its[2]).'"></td></tr>';

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

							if($index == 0){
								print '<tr class="impair"><td style="padding: 10px; width: 200px;">'.$obj_taxe2_type->libelle.'</td>';
								print '<td style="padding: 10px; width: 210px;"><input type="text" id="cfe" name="cfe" value="'.$retenu_taxe2_patro.'"></td></tr>';
								print '<tr class="impair"><td style="padding: 10px; width: 200px;">'.$obj_taxe2_type->libelle.' employé</td>';
								print '<td style="padding: 10px; width: 210px;"><input type="text" id="'.$obj_taxe2_type->libelle.'1" name="'.$obj_taxe2_type->libelle.'1" value="'.$retenu_taxe2_empl.'*"></td></tr>';
							}else{
								print '<tr class="impair"><td style="padding: 10px; width: 200px;">'.$obj_taxe2_type->libelle.'</td>';
								print '<td style="padding: 10px; width: 210px;"><input type="text" id="tl" name="tl" value="'.$retenu_taxe2_patro.'"></td></tr>';
								print '<tr class="impair"><td style="padding: 10px; width: 200px;">'.$obj_taxe2_type->libelle.' employé</td>';
								print '<td style="padding: 10px; width: 210px;"><input type="text" id="'.$obj_taxe2_type->libelle.'1" name="'.$obj_taxe2_type->libelle.'1" value="'.$retenu_taxe2_empl.'*"></td></tr>';

							}


							print "</tr>";	
							$index ++;
						}
						
				}

			print "<tr class='impair'>";
			print '<td style="padding: 10px; width: 200px;">Sursalaire</td>';
			print '<td style="padding: 10px; width: 210px;"><input type="text" id="sursalaire" value="'.($sursalaire?:0).'" name="sursalaire" ></td>';
			print '</tr>';
			print '<tr class="impair"><td style="padding: 10px; width: 200px;"><label>Coût total</label></td>';
			print '<td style="padding: 10px; width: 210px;"><input type="text" id="cout" name="cout" value="'.($cout).'"></td></tr>';
			
			print '<tr class="impair"><td style="padding: 10px; width: 200px;"><b>Nom de la simulation</b></td>';
			print '<td style="padding: 10px; width: 210px;"><input style="border-color:blue" autofocus type="text" required id="nom_simulation" name="nom_simulation" value=""></td></tr>';
			print '<tr><td colspan=2 style="padding: 10px; width: 200px; text-align:right"><input style="margin-top:10px;" type="submit" class="button" value="Enregistrer" ></td></tr>';
			//print '<input type="hidden" name="salaire_net" value="'.$net.'">';

			print "</table>";

		}

		

		
	}elseif(empty(GETPOST("nom_simulation", "alpha"))){

			$cout_total  = GETPOST("cout", "int");

			$iteration = 0;
			while ($fin == false && $cout_total && $iteration < 200000){
				$iteration++;
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
						if($obj_prest_type && $obj_prest_type->rowid != 6)
							$inps += $value*$mon_brut_cotis/100;
					$index ++;
				}

				$mon_brut_imp -= $inps;
				$its = its_salarie($db, $fk_salarie, $mon_brut_imp, GETPOST("statut_f", "alpha"), GETPOST("nb_enfant", "int"), GETPOST("nombre_enfant_hand", "int"));
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
				print '<td style="padding: 10px; width: 210px;"><input type="text" id="salaire_brut" name="salaire_brut" value="'.($mon_salaire_brut).'"></td></tr>';					
				$index = 0;
					$global_cotis = salarie_prestation_simulation($db, $fk_salarie, $salaire_brut_cotisable, $id_convention);
					$cotis = $global_cotis[1];
					$taux_p = $global_cotis[0];
					foreach ($cotis as $key => $value) {
						$type_prest = "SELECT * FROM ".MAIN_DB_PREFIX."type_prestation WHERE rowid=".$key;
							$result_type_prest = $db->query($type_prest);
							$obj_prest_type = $db->fetch_object($result_type_prest);
							if($obj_prest_type){
								$retenu_prest_empl = round($value*$salaire_brut_cotisable/100);
								$retenu_prest_patro = round($taux_p[$index]*$salaire_brut_cotisable/100);
								$mon_cout += $retenu_prest_patro;
								//print $taux_p[$index]."--".$retenu_prest_patro."<br>";

								print '<tr class="impair"><td style="padding: 10px; width: 200px;">'.$obj_prest_type->code.'</td>';
								print '<td style="padding: 10px; width: 210px;"><input type="text" id="'.$obj_prest_type->rowid.'employeur" name="'.$obj_prest_type->rowid.'employeur" value="'.$retenu_prest_patro.'"></td></tr>';
								print '<tr class="impair"><td style="padding: 10px; width: 200px;">'.$obj_prest_type->code.' employé</td>';
								print '<td style="padding: 10px; width: 210px;"><input type="text" id="'.$obj_prest_type->rowid.'employe" name="'.$obj_prest_type->rowid.'employe" value="'.$retenu_prest_empl.'"></td></tr>';

								print "</tr>";
					
							}
							$index ++;
							
					}	


					$its = its_salarie($db, $fk_salarie, $mon_brut_imp, GETPOST("statut_f", "alpha"), GETPOST("nb_enfant", "int"), GETPOST("nombre_enfant_hand", "int"));

					print '<input type="hidden" id="cout" name="cout" value="'.($cout_total).'">';
					print '<input type="hidden" id="salaire_brut_cotisable" name="salaire_brut_cotisable" value="'.($salaire_brut_cotisable).'">';
					print '<input type="hidden" id="salaire_brut_imposable" name="salaire_brut_imposable" value="'.($salaire_brut_imposable).'">';

				print '<tr class="impair"><td style="padding: 10px; width: 200px;">I.T.S(mensuel)</td>';
				print '<td style="padding: 10px; width: 210px;"><input type="text" id="its" name="its" value="'.round($its[2]).'"></td></tr>';

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

							if($index == 0){
								print '<tr class="impair"><td style="padding: 10px; width: 200px;">'.$obj_taxe2_type->libelle.'</td>';
								print '<td style="padding: 10px; width: 210px;"><input type="text" id="cfe" name="cfe" value="'.$retenu_taxe2_patro.'"></td></tr>';
								print '<tr class="impair"><td style="padding: 10px; width: 200px;">'.$obj_taxe2_type->libelle.' employé</td>';
								print '<td style="padding: 10px; width: 210px;"><input type="text" id="'.$obj_taxe2_type->libelle.'1" name="'.$obj_taxe2_type->libelle.'1" value="'.$retenu_taxe2_empl.'*"></td></tr>';
							}else{
								print '<tr class="impair"><td style="padding: 10px; width: 200px;">'.$obj_taxe2_type->libelle.'</td>';
								print '<td style="padding: 10px; width: 210px;"><input type="text" id="tl" name="tl" value="'.$retenu_taxe2_patro.'"></td></tr>';
								print '<tr class="impair"><td style="padding: 10px; width: 200px;">'.$obj_taxe2_type->libelle.' employé</td>';
								print '<td style="padding: 10px; width: 210px;"><input type="text" id="'.$obj_taxe2_type->libelle.'1" name="'.$obj_taxe2_type->libelle.'1" value="'.$retenu_taxe2_empl.'*"></td></tr>';

							}


							print "</tr>";	
							$index ++;
						}
						
				}
				print "<tr class='impair'>";	
				print '<td style="padding: 10px; width: 200px;">Sursalaire</td>';
				print '<td style="padding: 10px; width: 210px;"><input type="text" id="sursalaire" value="'.($sursalaire?:0).'" name="sursalaire" ></td>';
				print '</tr>';
				print '<tr class="impair"><td style="padding: 10px; width: 200px;"><label>Salaire net</label></td>';
				print '<td style="padding: 10px; width: 210px;"><input type="text" id="salaire_net" name="salaire_net" value="'.($mon_net).'"></td></tr>';					
				
				print '<tr class="impair"><td style="padding: 10px; width: 200px;"><b>Nom de la simulation</b></td>';
				print '<td style="padding: 10px; width: 210px;"><input style="border-color:blue" autofocus type="text" required id="nom_simulation" name="nom_simulation" value=""></td></tr>';

				print '<tr><td colspan=2 style="padding: 10px; width: 200px; text-align:right"><input style="margin-top:10px;" type="submit" class="button" value="Enregistrer" ></td></tr>';

				print "</table>";
	}
	}
}
	print "</div>";
		print "</div>";
			print "</form>";

}else 	print "<h2> Permission manquante </h2>";
if($message != ""){
		print "<script>
		$.jnotify('".dol_escape_js($message)."', {delay : 5000, fadeSpeed: 500});
		</script>";
	}
	
		
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

		if (categories && type_simulation) {
			categories.addEventListener("change", function () {
				var typ_sim = type_simulation.value;
				var id_salaire_base = categories.value;
				window.location.href = "'.$_SERVER["PHP_SELF"].'?id='.$id_salarie.'&id_societe='.$id_societe.'&matricule='.$matricule_salarie.'&id_convention='.$id_convention.'&categories="+id_salaire_base+"&type_simulation="+typ_sim;
			}, false);
		}
		if (anciennete && categories && type_simulation) {
			anciennete.addEventListener("change", function () {
				var typ_sim = type_simulation.value;
				var id_salaire_base = categories.value;
				var val_anciennet = anciennete.value;
				window.location.href = "'.$_SERVER["PHP_SELF"].'?id='.$id_salarie.'&id_societe='.$id_societe.'&matricule='.$matricule_salarie.'&id_convention='.$id_convention.'&categories="+id_salaire_base+"&anciennete="+val_anciennet+"&type_simulation="+typ_sim;
			}, false);
		}

		/*function ajoutPrimeIndemnite(e){
			
			alert(e);
		}*/
	</script>';
	/*
	 
	*/
