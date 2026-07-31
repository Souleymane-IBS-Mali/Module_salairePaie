<?php
require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/custom/paiementsalaire/lib/paiementsalaire.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';

llxHeader("", "Paiement | Salaire");
//Titre 
print load_fiche_titre($langs->trans("Edition des Indemnités"), '', '');
//print '<hr>';
$id_societe = GETPOST("id_societe","int");
	$fk_user = GETPOST("id","int");
	$fk_salarie = GETPOST("fk_salarie", "int");
	$id_convention = GETPOST("id_convention","int");

$head = salaire_Head($fk_salarie, $fk_user, $id_societe, $id_convention);
	print dol_get_fiche_head($head, 'indemnites', "", -1, '');

if($user->id !=1 && $user->id != $fk_user && !$user->rights->paiementsalaire->salarie->read){
	print "<h2> Vous n\'avez pas ce droit </h2>";
}else{
	$action = GETPOST("action", "alpha");
	$message ="";
	/*$salSql = "SELECT * FROM ".MAIN_DB_PREFIX."salarie where fk_salarie=".$fk_salarie;
		$result = $db->query($salSql);
		if(!empty($result)){
			$salarie = $db->fetch_object($result);*/

	//Recuperation de l'action   
	

	if(empty($fk_salarie)){
		print "Page non Disponible";
	}else{
		$obj_soc = prepare_objet_entete($fk_salarie, $fk_user, $db, $id_societe, $id_convention);
		entete_societe($obj_soc, 'societe');


		$monform = new Form($db);

		if($action == "associer"){
			$fk_indemnite = GETPOST("fk_indemnite", "int");
			$sql = "DELETE FROM ".MAIN_DB_PREFIX."salarie_indemnite WHERE fk_salarie='".$fk_salarie."' AND fk_indemnite=".$fk_indemnite;
			$result2 = $db->query($sql);
	
			$sql = "INSERT INTO ".MAIN_DB_PREFIX."salarie_indemnite (fk_salarie, fk_indemnite, mois) VALUES ('".$fk_salarie."',".$fk_indemnite.", now())";
			$result2 = $db->query($sql);
			if($result)
				$message = "Indemnité ajoutée a ce salarié";
		}


		if($action == "ajout_flotant"){
			$fk_indemnite = GETPOST("fk_indemnite", "int");
			$sql1 = "SELECT * FROM ".MAIN_DB_PREFIX."indemnite WHERE rowid=".$fk_indemnite." AND active=1";
			$result1 = $db->query($sql1);
			$obj1 = $db->fetch_object($result1);
			$sql_fl = "SELECT * FROM ".MAIN_DB_PREFIX."salarie_indemnite_flottante WHERE fk_salarie='".$fk_salarie."' AND fk_indemnite=".$fk_indemnite;
			$result_fl = $db->query($sql_fl);
			if($result_fl)
				$obj_fl = $db->fetch_object($result_fl);
		
			print '<form name="add_flottante" method="POST" action="'.$_SERVER['PHP_SELF'].'?mainmenu=paiementsalaire&leftmenu=salarie&id_convention='.$id_convention.'&id_societe='.$id_societe.'&fk_salarie='.$fk_salarie.'&id='.$fk_user.'&fk_indemnite='.$fk_indemnite.'">';
			print '<fieldset>';
			print '<legend><h3>'.$obj1->libelle.'</h3></legend>';
			print '<input type="hidden" name="token" value="'.newToken().'">';
			print '<input type="hidden" name="action" value="save_flottante">';

			print '<table><tr><td>Type</td><td><div id="m1">Montan Fixe</div></td><td><div id="m2">Pourcentage %</div></td><td></td></tr>';
			print '<tr><td><select name="type" id="type">
			<option value="montant_fixe">Montant Fixe</option>
			<option value="pourcentage">Pourcentage</option>';
			print '<td><input type="text" name="montant_flottant" id="montant_fixe" value="'.GETPOST("montant_flottant", "int").'"></td>';
			print '<td><input type="number" name="pourcentage" id="pourcentage" value="'.GETPOST("pourcentage", "float").'" min="1" max="100">';
			print "<td><input type='submit' class='button' value='Ajouter' /></td></tr>";
			//print "<a class='reposition editfielda' href='".$_SERVER['PHP_SELF']."?mainmenu=paiementsalaire&leftmenu=salarie&id_convention=".$id_convention."&fk_salarie=".$fk_salarie."&id=".$fk_user."&fk_indemnite=".$fk_indemnite."'><button class='button'>Annuler</button></a></td></tr>";
			print '</table>';
			print '</fieldset>';
			print "</form>";
			print '<br>';
	}
	
	if($action == "edit_flotant"){
		$fk_indemnite = GETPOST("fk_indemnite", "int");
		$sql1 = "SELECT * FROM ".MAIN_DB_PREFIX."indemnite WHERE rowid=".$fk_indemnite." AND active=1";
		$result1 = $db->query($sql1);
		$obj1 = $db->fetch_object($result1);
		$sql_fl = "SELECT * FROM ".MAIN_DB_PREFIX."salarie_indemnite_flottante WHERE fk_salarie='".$fk_salarie."' AND fk_indemnite=".$fk_indemnite;
		$result_fl = $db->query($sql_fl);
		if($result_fl)
			$obj_fl = $db->fetch_object($result_fl);
	
		print '<form name="add_flottante" method="POST" action="'.$_SERVER['PHP_SELF'].'?mainmenu=paiementsalaire&id_convention='.$id_convention.'&id_societe='.$id_societe.'&leftmenu=salarie&fk_salarie='.$fk_salarie.'&id='.$fk_user.'&fk_indemnite='.$fk_indemnite.'">';
		print '<fieldset>';
		print '<legend><h3>'.$obj1->libelle.'</h3></legend>';
		print '<input type="hidden" name="token" value="'.newToken().'">';
		print '<input type="hidden" name="action" value="saveedit_flotant">';
		print '<table><tr><td>Type</td><td><div id="m1">Montant Fixe</div></td><td><div id="m2">Pourcentage %</div></td><td></td></tr>';
		print '<tr><td><select name="type" id="type">';
		$tab = explode('%', $obj_fl->montant."a");
		if(count($tab) > 1){
			print '<option value="montant_fixe">Montant Fixe</option>
			<option value="pourcentage" selected>Pourcentage</option>';
			print '</selected></td>';
			print '<td><input type="number" name="pourcentage" id="pourcentage" min="1" max="100" value="'.(explode("%", $obj_fl->montant)[0]).'">';

		}else{
			print '<option value="montant_fixe" selected>Montant Fixe</option>
			<option value="pourcentage">Pourcentage</option>';
			print '<td><input type="text" name="montant_flottant" id="montant_fixe" value="'.$obj_fl->montant.'" ></td>';

		}
		
		print "<td><input type='submit' class='button' value='Ajouter' /></td></tr>";
		//print "<a class='reposition editfielda' href='".$_SERVER['PHP_SELF']."?mainmenu=paiementsalaire&leftmenu=salarie&id_convention=".$id_convention."&fk_salarie=".$fk_salarie."&id=".$fk_user."&fk_indemnite=".$fk_indemnite."'><button class='button'>Annuler</button></a></td></tr>";
		print '</table>';
		print '</fieldset>';
		print "</form>";
		print '<br>';
	}

	print "<script>
				//impôt
					var type = document.getElementById('type');
					var montant_fixe = document.getElementById('montant_fixe');
					var pourcentage = document.getElementById('pourcentage');
					var m1 = document.getElementById('m1');
					var m2 = document.getElementById('m2');
					if(type.value == 'pourcentage'){
						m1.style.display='none';
						m2.style.display='inline';
						pourcentage.style.display='inline';
						montant_fixe.style.display='none';
					}else{
						m1.style.display='inline';
						montant_fixe.style.display='inline';
						m2.style.display='none';
						pourcentage.style.display='none';
					}				
					type.addEventListener('change',type_fonction);
					function type_fonction(){
						if(type.value == 'pourcentage'){
							m1.style.display='none';
							m2.style.display='inline';
							pourcentage.style.display='inline';
							montant_fixe.style.display='none';
						}else{
							m1.style.display='inline';
							montant_fixe.style.display='inline';
							m2.style.display='none';
							pourcentage.style.display='none';
						}	
					}
				
				</script>";


		//Confirmer la suppression
	if($action == "supprimer_attention"){
		$fk_indemnite = GETPOST("fk_indemnite", "int");
		$url = $_SERVER['PHP_SELF']."?mainmenu=paiementsalaire&leftmenu=societe&id_societe=".$id_societe."&id_convention=".$id_convention."&fk_indemnite=".$fk_indemnite."&fk_salarie=".$fk_salarie."&id=".$fk_user;
		$titre = "Voulez-vous vraiment supprimer cette prime";

		  $formconfirm = $monform->formconfirm(
			  $url, 
			  $titre, 
			  "", 
			  'supprimer', 
			  $array, 
			  '', 
			  1,
			  180,
			  '35%'
		  );
		  print $formconfirm;
	}
	
	//Suppression après confirmation
	if($action == "supprimer"){
		$fk_indemnite = GETPOST("fk_indemnite", "int");


		$result = $db->query("SELECT * FROM ".MAIN_DB_PREFIX."indemnite WHERE rowid=".$fk_indemnite);
		$obj_ind = $db->fetch_object($result);

		$sql_fl = "SELECT montant FROM ".MAIN_DB_PREFIX."salarie_indemnite_flottante WHERE fk_salarie='".$fk_salarie."' AND fk_indemnite=".$fk_indemnite;
		$result_fl = $db->query($sql_fl);
		$obj_ind_fl = $db->fetch_object($result_fl);

		$result = $db->query("SELECT * FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$fk_user);
		$obj_sal = $db->fetch_object($result);

		//sauvegarde des trace de l'action
		$sql_select = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$user->id;
		$obj = $db->fetch_object($db->query($sql_select));

		$action_effectue = "Désaffectation d'une indemnité flottante ".$obj_ind->libelle."(".$obj_ind_fl->montant.") au salarié ".$obj_sal->firstname." ".$obj_sal->lastname." id salarié=".$fk_salarie." de la société ".$obj_soc->name;
		$sql_log = 'INSERT INTO '.MAIN_DB_PREFIX.'log (fk_user, nom, prenom, quand, action_effectue, object_concerne)';
		$sql_log .= ' VALUES('.$user->id.', "'.$obj->lastname.'","'.$obj->firstname.'",now(),"'.$action_effectue.'","Désaffectation")';
		$db->query($sql_log);

		//suppression
		$sql = "DELETE FROM ".MAIN_DB_PREFIX."salarie_indemnite WHERE fk_salarie='".$fk_salarie."' AND fk_indemnite=".$fk_indemnite;
		$result2 = $db->query($sql);
	
		$fk_indemnite = GETPOST("fk_indemnite", "int");
		$sql = "DELETE FROM ".MAIN_DB_PREFIX."salarie_indemnite_flottante WHERE fk_salarie='".$fk_salarie."' AND fk_indemnite=".$fk_indemnite;
		$result2 = $db->query($sql);
		$message = "Indemnité dissocier de ce salarié avec succès";
			
	}
	if($action  == "save_flottante"){
		$fk_indemnite = GETPOST("fk_indemnite", "int");
		$type = GETPOST("type", "alpha");

		if($type=="montant_fixe" && GETPOST("montant_flottant", "int") == "")
			$message = 'Le champs "MONTANT" est obligatoire<br>';
		if($type=="pourcentage" && GETPOST("pourcentage", "int") == "")
			$message = 'Le champs "POURCENTAGE" est obligatoire<br>';
			
		if(empty($message)){
			$montant_flottant = GETPOST("montant_flottant", "int");

			if($type=="pourcentage")
				$montant_flottant = GETPOST("pourcentage", "int")."%";
				$fk_indemnite = GETPOST("fk_indemnite", "int");
				$sql = "DELETE FROM ".MAIN_DB_PREFIX."salarie_indemnite_flottante WHERE fk_salarie=".$fk_salarie." AND fk_indemnite='".$fk_indemnite."'";
				$result2 = $db->query($sql);
				$sql = 'INSERT INTO '.MAIN_DB_PREFIX.'salarie_indemnite_flottante (fk_salarie, fk_indemnite, montant, date_debut) 
				VALUES ('.$fk_salarie.',"'.$fk_indemnite.'","'.$montant_flottant.'",now())';
				$result2 = $db->query($sql);
				$sql = "DELETE FROM ".MAIN_DB_PREFIX."salarie_indemnite WHERE fk_salarie='".$fk_salarie."' AND fk_indemnite=".$fk_indemnite;
				$result2 = $db->query($sql);
	
				$sql = "INSERT INTO ".MAIN_DB_PREFIX."salarie_indemnite (fk_salarie, fk_indemnite, mois) VALUES ('".$fk_salarie."',".$fk_indemnite.", now())";
				$result = $db->query($sql);

				if($result && $result2){
					$message = "Indemnité Ajoutée uniquement au compte de ce salarié";

					$result = $db->query("SELECT * FROM ".MAIN_DB_PREFIX."indemnite WHERE rowid=".$fk_indemnite);
					$obj_ind = $db->fetch_object($result);

					$result = $db->query("SELECT * FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$fk_user);
					$obj_sal = $db->fetch_object($result);

					//sauvegarde des trace de l'action
					$sql_select = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$user->id;
					$obj = $db->fetch_object($db->query($sql_select));

					$action_effectue = "Affectation d'une indemnité flottante ".$obj_ind->libelle."(".$montant_flottant.") au salarié ".$obj_sal->firstname." ".$obj_sal->lastname." id salarié=".$fk_salarie." de la société ".$obj_soc->name;
					$sql_log = 'INSERT INTO '.MAIN_DB_PREFIX.'log (fk_user, nom, prenom, quand, action_effectue, object_concerne)';
					$sql_log .= ' VALUES('.$user->id.', "'.$obj->lastname.'","'.$obj->firstname.'",now(),"'.$action_effectue.'","Affectation")';
					$db->query($sql_log);
				}else $message = "Un problème est survenu";
			}
	
	}
	
	if($action  == "saveedit_flotant"){
		$fk_indemnite = GETPOST("fk_indemnite", "int");
		$type = GETPOST("type", "alpha");
			
		if($type=="montant_fixe" && GETPOST("montant_flottant", "int") == "")
			$message = 'Le champs "MONTANT" est obligatoire<br>';
		if($type=="pourcentage" && GETPOST("pourcentage", "int") == "")
			$message = 'Le champs "POURCENTAGE" est obligatoire<br>';
		
	
		if(empty($message)){
				$fk_indemnite = GETPOST("fk_indemnite", "int");
				$montant_flottant = GETPOST("montant_flottant", "int");

				if($type=="pourcentage")
					$montant_flottant = GETPOST("pourcentage", "int")."%";

				$sql_fl = "SELECT * FROM ".MAIN_DB_PREFIX."salarie_indemnite_flottante WHERE fk_salarie=".$fk_salarie." AND fk_indemnite='".$fk_indemnite."'";
				$result_fl = $db->query($sql_fl);
				if($result_fl){
					$obj_fl = $db->fetch_object($result_fl);
					if($obj_fl){
						$sql = "UPDATE ".MAIN_DB_PREFIX."salarie_indemnite_flottante SET montant='".$montant_flottant."', date_debut=now() WHERE fk_salarie=".$fk_salarie." AND fk_indemnite='".$fk_indemnite."'";
						$result2 = $db->query($sql);

						if($result2){
							$message = "Indemnité Modifiée avec succès";

							$result = $db->query("SELECT * FROM ".MAIN_DB_PREFIX."indemnite WHERE rowid=".$fk_indemnite);
							$obj_ind = $db->fetch_object($result);

							$result = $db->query("SELECT * FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$fk_user);
							$obj_sal = $db->fetch_object($result);

							//sauvegarde des trace de l'action
							$sql_select = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$user->id;
							$obj = $db->fetch_object($db->query($sql_select));

							$action_effectue = "Modification d'une indemnité flottante ".$obj_ind->libelle."(".$montant_flottant.") au salarié ".$obj_sal->firstname." ".$obj_sal->lastname." id salarié=".$fk_salarie." de la société ".$obj_soc->name;
							$sql_log = 'INSERT INTO '.MAIN_DB_PREFIX.'log (fk_user, nom, prenom, quand, action_effectue, object_concerne)';
							$sql_log .= ' VALUES('.$user->id.', "'.$obj->lastname.'","'.$obj->firstname.'",now(),"'.$action_effectue.'","Affectation")';
							$db->query($sql_log);
						}
						else $message = "Un problème est survenu";
					}else{
					//----------------------------
					$sql = "INSERT INTO ".MAIN_DB_PREFIX."salarie_indemnite_flottante (fk_salarie, fk_indemnite, montant, date_debut) 
					VALUES (".$fk_salarie.",'".$fk_indemnite."','".$montant_flottant."',now())";
					$result2 = $db->query($sql);
					if($result2)
						$message = "Indemnité Modifiée avec succès";
					else $message = "Un problème est survenu";
					//------------------------------
				}
				}
			}
	
	}
	
		//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------
		//les indemnites associer au salarié
		print "<hr>";
			print "<div>";
			print "<h3 >Indemnités Associées</h3>";
			print "<table class='tagtable liste' style='width:100%;'>";
			print "<tr class='liste_titre'><td style='padding: 10px; width: 34%;'>Nom indemnite</td><td style='padding: 10px; width: 33%;'>Type";
			print "</td><td style='padding: 10px; width: 33%;'>Opération</td></tr>";

			$array_flottant = array();	
			$salSql = "SELECT * FROM ".MAIN_DB_PREFIX."salarie where fk_salarie='".$fk_salarie."'";
			$result = $db->query($salSql);
			$tab = array();
			if(!empty($result))
				$salarie = $db->fetch_object($result);

				$sql_flottante = "SELECT fk_indemnite FROM ".MAIN_DB_PREFIX."condition_indemnite WHERE type_montant='flottante' ";
				$result_flottante = $db->query($sql_flottante);
				if($result_flottante){
					$i = 0;
					$num_flottante = $db->num_rows($result_flottante);
					while ($i < $num_flottante){
						$obj_flottante = $db->fetch_object($result_flottante);
						if ($obj_flottante)
						{
							$array_flottant[$i] = $obj_flottante->fk_indemnite;
						}
						$i ++;
					}
					
				}

				//les indemnités obligatoires
			$sql = "SELECT * FROM ".MAIN_DB_PREFIX."indemnite WHERE type_indemnite='obligatoire' AND active=1 AND ((fk_societe=0 AND fk_convention=0) OR (fk_societe=".$id_societe." OR fk_convention=".$id_convention."))";
			$result = $db->query($sql);
			if($result){
				$i = 0;
				$num2 = $db->num_rows($result);
				while ($i < $num2){
					$obj = $db->fetch_object($result);
					if ($obj)
					{
							$tab[$i] = $obj->rowid;
							print "<tr class='pair'><td>";
							print ''.$obj->libelle.'</td><td>'.$obj->type_indemnite;
							print "</td><td>Affectée</td></tr>";
					}
					$i ++;
				}
			}
				//indemnités indemnités liées à la catégorie
			$sql = "SELECT fk_indemnite FROM ".MAIN_DB_PREFIX."categorie_indemnite WHERE fk_categorie=".$salarie->fk_categorie." AND fk_indemnite NOT IN (0";
			$a = 0;
				while ($a < count($tab)) {
						$sql .= ", ".$tab[$a]."";
					$a ++;
				}
				$sql .= ")";
			$result = $db->query($sql);
			if($result){
				$i = 0;
				$num1 = $db->num_rows($result);
				while ($i < $num1){
					$obj = $db->fetch_object($result);
					if ($obj)
					{
						$sql1 = "SELECT * FROM ".MAIN_DB_PREFIX."indemnite WHERE rowid=".$obj->fk_indemnite." AND active=1 AND ((fk_societe=0 AND fk_convention=0) OR (fk_societe=".$id_societe." OR fk_convention=".$id_convention."))";
						$result1 = $db->query($sql1);
						$obj1 = $db->fetch_object($result1);
						if($obj1){
							print "<tr class='pair'><td>";
							print ''.$obj1->libelle.'</td><td>'.$obj1->type_indemnite;
							print "</td><td>Affectée</td></tr>";
						}
					}
					$i ++;
				}
				
			}




			$sql = "SELECT * FROM ".MAIN_DB_PREFIX."salarie_indemnite WHERE fk_salarie=".$fk_salarie;
			$result = $db->query($sql);
			if($result){
				$i = 0;
				$num = $db->num_rows($result);
				while ($i < $num){
					$obj = $db->fetch_object($result);
					if ($obj)
					{
						$sql1 = "SELECT * FROM ".MAIN_DB_PREFIX."indemnite WHERE rowid=".$obj->fk_indemnite." AND active=1 AND ((fk_societe=0 AND fk_convention=0) OR (fk_societe=".$id_societe." OR fk_convention=".$id_convention."))";
						$result1 = $db->query($sql1);
						$obj1 = $db->fetch_object($result1);
						if($obj1)
						
							if(in_array($obj1->rowid, $array_flottant)){
								print "<tr class='pair'><td>";
								$sql_fl = "SELECT * FROM ".MAIN_DB_PREFIX."salarie_indemnite_flottante WHERE fk_salarie='".$fk_salarie."' AND fk_indemnite=".$obj->fk_indemnite;
								$result_fl = $db->query($sql_fl);
								if($result_fl)
									$obj_fl = $db->fetch_object($result_fl);
									print $obj1->libelle.'</td><td><input type="text" disabled size="7" name="montant_flottant" value="'.apres_virgule($db, $id_societe, $obj_fl->montant?:0).'" >';
									
									print "<a class='reposition editfielda' href='".$_SERVER['PHP_SELF']."?mainmenu=paiementsalaire&leftmenu=salarie&id_convention=".$id_convention."&id_societe=".$id_societe."&fk_salarie=".$fk_salarie."&id=".$fk_user."&action=edit_flotant&fk_indemnite=".$obj->fk_indemnite."'>".img_edit()."</a></td>";
									print "<td><a class='reposition editfielda' href='".$_SERVER['PHP_SELF']."?mainmenu=paiementsalaire&leftmenu=salarie&id_convention=".$id_convention."&id_societe=".$id_societe."&fk_salarie=".$fk_salarie."&id=".$fk_user."&action=supprimer_attention&id_convention=".$id_convention."&fk_indemnite=".$obj->fk_indemnite."'><button class='button'>Dissocier</button></a></td></tr>";
								
							}else{
								print "<tr><td>";
								print ''.$obj1->libelle.'</td><td>'.$obj1->type_indemnite;
								print "</td><td><a class='reposition editfielda' href='".$_SERVER['PHP_SELF']."?mainmenu=paiementsalaire&leftmenu=salarie&id_convention=".$id_convention."&id_societe=".$id_societe."&fk_salarie=".$fk_salarie."&id=".$fk_user."&action=supprimer_attention&id_convention=".$id_convention."&fk_indemnite=".$obj->fk_indemnite."'><button class='button'>Dissocier</button></a></td></tr>";
						}
					}
					$i ++;
				}
				if($num == 0 && $num1 == 0 && $num2 == 0)
					print "<tr><td colspan='3' align='center'>Aucune Indemnité disponible</td></tr>";

				}else
				print "<tr><td colspan='3' align='center'>Aucune Indemnité disponible</td></tr>";

				print "</table></div>";

	//------------------------------------------------------------------------------------------------------------------------------
				//Indéminités disponibles non associés
				print "<br><div>";
				print "<h3>Indemnités disponibles</h3>";
				print "<table class='tagtable liste' style='width:100%;'>";
				print "<tr class='liste_titre'><td style='padding: 10px; width: 34%;'>Nom indemnite</td><td style='padding: 10px; width: 33%;'>Type";
				print "</td><td style='padding: 10px; width: 33%;'>Opération</td></tr>";
				
				//recuperation des indemnités associés
			$sql = "SELECT fk_indemnite FROM ".MAIN_DB_PREFIX."salarie_indemnite WHERE fk_salarie=".$fk_salarie;
			$result = $db->query($sql);
			$array = array();
			if($result){
				$i = 0;
				$num = $db->num_rows($result);
				while ($i < $num){
					$obj = $db->fetch_object($result);
					if($obj){
						$array[$i] = $obj->fk_indemnite;
					}
					$i ++;
				}
			}

			$sql = "SELECT * FROM ".MAIN_DB_PREFIX."indemnite Where type_indemnite='obligatoire' AND ((fk_societe=0 AND fk_convention=0) OR (fk_societe=".$id_societe." OR fk_convention=".$id_convention."))";
			$result = $db->query($sql);
			$taille = count($array);
			if($result){
				$i = 0;
				$num = $db->num_rows($result);
				while ($i < $num){
					$obj = $db->fetch_object($result);
					if($obj){
						$array[$taille + $i] = $obj->rowid;
					}
					$i ++;
				}
			}

			$sql = "SELECT fk_indemnite FROM ".MAIN_DB_PREFIX."categorie_indemnite Where fk_categorie=".$salarie->fk_categorie;
			$result = $db->query($sql);
			$taille = count($array);
			if($result){
				$i = 0;
				$num = $db->num_rows($result);
				while ($i < $num){
					$obj = $db->fetch_object($result);
					if($obj){
						$array[$taille + $i] = $obj->fk_indemnite;
					}
					$i ++;
				}
			}
			$sql = "SELECT * FROM ".MAIN_DB_PREFIX."indemnite WHERE rowid NOT IN (0";
			$a = 0;
				while ($a < count($array)) {
						$sql .= ", ".$array[$a]."";
					$a ++;
				}
				
				$sql .= ") AND active=1 AND ((fk_societe=0 AND fk_convention=0) OR (fk_societe=".$id_societe." OR fk_convention=".$id_convention."))";
				$result = $db->query($sql);
			if($result){
				$i = 0;
				$num = $db->num_rows($result);
				while ($i < $num){
					$obj = $db->fetch_object($result);
					if($obj){
						print "<tr class='pair'><td>";
						print $obj->libelle.'</td><td>'.$obj->type_indemnite;

						$sql_fl = "SELECT * FROM ".MAIN_DB_PREFIX."condition_indemnite WHERE fk_indemnite=".$obj->rowid." AND type_montant='flottante'";
						$result_fl = $db->query($sql_fl);
						if($result_fl)
							$obj_fl = $db->fetch_object($result_fl);
						if($obj_fl){
							print "</td><td><a class='reposition editfielda' href='".$_SERVER['PHP_SELF']."?mainmenu=paiementsalaire&leftmenu=salarie&id_convention=".$id_convention."&id_societe=".$id_societe."&fk_salarie=".$fk_salarie."&id=".$fk_user."&action=ajout_flotant&fk_indemnite=".$obj->rowid."'><button id='associer' class='button'>Associer</button></a>".info_admin($langs->trans("Indemnité flottante dont la valeur depend du salarié"), 1)."</td></tr>";
						}else
						print "</td><td><a class='reposition editfielda' href='".$_SERVER['PHP_SELF']."?mainmenu=paiementsalaire&leftmenu=salarie&id_convention=".$id_convention."&id_societe=".$id_societe."&fk_salarie=".$fk_salarie."&id=".$fk_user."&action=associer&fk_indemnite=".$obj->rowid."'><button id='associer' class='button'>Associer</button></a></td></tr>";
					}
					$i ++;
				}
				if($num == 0)
					print "<tr><td colspan='3' align='center'>Aucune Indemnité disponible</td></tr>";
			}else
				print "<tr><td colspan='3' align='center'>Aucune Indemnité disponible</td></tr>";
		print "</table></div>";


		//-------------------------------------------------------------------------------------------------------------------------------
		//Indemnités Archivées
		print "<br><div>";
		print "<h3>Indemnités Archivées</h3>";
		print "<table class='tagtable liste' style='width:100%;'>";
		print "<tr class='liste_titre'><td style='padding: 10px; width: 34%;'>Nom Indemnité</td><td style='padding: 10px; width: 33%;'>Type";
		print "</td><td style='padding: 10px; width: 33%;'>Opération</td></tr>";
		/*$tab_id_indemnite = array();
		$sql_flottante = "SELECT fk_indemnite FROM ".MAIN_DB_PREFIX."condition_indemnite WHERE type_montant='flottante' ";
			$result_flottante = $db->query($sql_flottante);
			if($result_flottante){
				$i = 0;
				$num_flottante = $db->num_rows($result_flottante);
				while ($i < $num_flottante){
					$obj_flottante = $db->fetch_object($result_flottante);
					if ($obj_flottante)
					{
						$tab_id_indemnite[$i] = $obj_flottante->fk_indemnite;
					}
					$i ++;
				}
			}
				$sql_fl = "SELECT * FROM ".MAIN_DB_PREFIX."salarie_indemnite_flottante WHERE fk_indemnite IN (0";
				for ($i=0; $i < count($tab_id_indemnite); $i++) { 
					$sql_fl .= ", ".$tab_id_indemnite[$i]."";
				}
				$sql_fl .= ")";
				$sql_fl .= " AND ( YEAR(date_limit)<".$annee." OR ( YEAR(date_limit)=".$annee." AND MONTH(date_limit)>".$mois."))";
				$result_fl = $db->query($sql_fl);
				if($result_fl){
					$num_arch1 = $db->num_rows($result_fl);
					$i = 0;
					while($i < $num_arch1){
						$obj_fl = $db->fetch_object($result_fl);
						$var = "Indemnité Flottante date d'expiration : ".$obj_except->date_limit;
						print "<tr class='impair'><td>";
						print img_error("indemnite expirée".$var).' <span>'.$obj_except->libelle.'</span></td><td>'.$obj_except->montant;
						print "</td><td><button disabled class='button'>Dissocier</button></td></tr>";

						$i ++;
					}

				}

				if($num_arch1 == 0)
					print "<tr><td colspan='3' align='center'>Aucune Indemnité Archivée</td></tr>";*/
					print "</table></div>";
	}
	$db->free();


	if($message != ""){		
		print "<script>
		$.jnotify('".$message."', {delay : 5000, fadeSpeed: 500});
		</script>";
	}

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