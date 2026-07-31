<?php
require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/custom/paiementsalaire/lib/paiementsalaire.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';
llxHeader("", "Paiement | Salaire");
//Titre
print load_fiche_titre($langs->trans("Edition des Primes"), '', '');

$fk_user = GETPOST("id","int");
$id_societe = GETPOST("id_societe","int");
$fk_salarie = GETPOST("fk_salarie", "int");
$id_convention = GETPOST("id_convention","int");
$head = salaire_Head($fk_salarie, $fk_user, $id_societe, $id_convention);
	print dol_get_fiche_head($head, 'primes', "", -1, '');

if($user->id !=1 && $user->id != $fk_user && !$user->rights->paiementsalaire->salarie->read){
	print "<h2> Vous n\'avez pas ce droit </h2>";
}else{

	$action = GETPOST("action", "alpha");
	$message ="";

	if(empty($fk_salarie)){
		print "Page non Disponible";
	}else{
		$obj_soc = prepare_objet_entete($fk_salarie, $fk_user, $db, $id_societe, $id_convention);
		entete_societe($obj_soc, 'societe');
		print '<hr>';

		//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------
		//les primes associer au salarié
		$monform = new Form($db);


	//Affectation d'une prime à un salarié
	if($action == "associer"){
		$fk_prime = GETPOST("fk_prime", "int");
		$sql = "DELETE FROM ".MAIN_DB_PREFIX."salarie_prime WHERE fk_salarie='".$fk_salarie."' AND fk_prime=".$fk_prime;
		$result2 = $db->query($sql);

		$sql = "INSERT INTO ".MAIN_DB_PREFIX."salarie_prime (fk_salarie, fk_prime, mois) VALUES ('".$fk_salarie."',".$fk_prime.", now())";
		$result2 = $db->query($sql);
		if($result)
			$message = "Prime associée a ce salarié";
		
	}


	if($action == "edit_flotant"){
		$fk_prime = GETPOST("fk_prime", "int");
		$sql1 = "SELECT * FROM ".MAIN_DB_PREFIX."primes WHERE rowid=".$fk_prime." AND active=1";
		$result1 = $db->query($sql1);
		$obj1 = $db->fetch_object($result1);
		$sql_fl = "SELECT * FROM ".MAIN_DB_PREFIX."salarie_prime_flottante WHERE fk_salarie='".$fk_salarie."' AND fk_prime=".$fk_prime;
		$result_fl = $db->query($sql_fl);
		if($result_fl)
			$obj_fl = $db->fetch_object($result_fl);

		print '<form name="add_flottante" method="POST" action="'.$_SERVER['PHP_SELF'].'?mainmenu=paiementsalaire&leftmenu=salarie&id_convention='.$id_convention.'&id_societe='.$id_societe.'&fk_salarie='.$fk_salarie.'&id='.$fk_user.'&fk_prime='.$fk_prime.'">';
		print '<fieldset>';
		print '<legend><h3>'.$obj1->libelle.'</h3></legend>';
		print '<input type="hidden" name="token" value="'.newToken().'">';
		print '<input type="hidden" name="action" value="saveeditValeurFlottante">';
		print '<table><tr><td>Type</td><td><div id="m1">Montant Fixe</div></td><td><div id="m2">Pourcentage %</div></td><td></td></tr>';
		print '<tr><td><select name="type" id="type">';
		$tab = explode('%', $obj_fl->montant);
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
		//print "<a class='reposition editfielda' href='".$_SERVER['PHP_SELF']."?mainmenu=paiementsalaire&leftmenu=salarie&id_convention=".$id_convention."&fk_salarie=".$fk_salarie."&id=".$fk_user."&fk_prime=".$fk_prime."'><button class='button'>Annuler</button></a></td></tr>";
		print '</table>';
		print '</fieldset>';
		print "</form>";
		print '<br>';
	}

	//Avertissement de suppression

	//Confirmer la suppression
	if($action == "supprimer_attention"){
		$fk_prime = GETPOST("fk_prime", "int");
		$url = $_SERVER['PHP_SELF']."?mainmenu=paiementsalaire&leftmenu=societe&id_societe=".$id_societe."&id_convention=".$id_convention."&fk_prime=".$fk_prime."&fk_salarie=".$fk_salarie."&id=".$fk_user;
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

		//Suppression apres confirmation
	if($action == "supprimer"){
		$fk_prime = GETPOST("fk_prime", "int");

		$result = $db->query("SELECT * FROM ".MAIN_DB_PREFIX."primes WHERE rowid=".$fk_prime);
		$obj_pr = $db->fetch_object($result);

		$sql_fl = "SELECT montant FROM ".MAIN_DB_PREFIX."salarie_prime_flottante WHERE fk_salarie='".$fk_salarie."' AND fk_prime=".$fk_prime;
		$result_fl = $db->query($sql_fl);
		$obj_pr_fl = $db->fetch_object($result_fl);

		$result = $db->query("SELECT * FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$fk_user);
		$obj_sal = $db->fetch_object($result);

		//sauvegarde des trace de l'action
		$sql_select = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$user->id;
		$obj = $db->fetch_object($db->query($sql_select));

		$action_effectue = "Désaffectation d'une prime flottante ".$obj_pr->libelle."(".$obj_pr_fl->montant.") au salarié ".$obj_sal->firstname." ".$obj_sal->lastname." id salarié=".$fk_salarie." de la société ".$obj_soc->name;
		$sql_log = 'INSERT INTO '.MAIN_DB_PREFIX.'log (fk_user, nom, prenom, quand, action_effectue, object_concerne)';
		$sql_log .= ' VALUES('.$user->id.', "'.$obj->lastname.'","'.$obj->firstname.'",now(),"'.$action_effectue.'","Désaffectation")';
		$db->query($sql_log);

		$sql = "DELETE FROM ".MAIN_DB_PREFIX."salarie_prime WHERE fk_salarie='".$fk_salarie."' AND fk_prime=".$fk_prime;
			$result2 = $db->query($sql);

		$fk_prime = GETPOST("fk_prime", "int");
		$sql = "DELETE FROM ".MAIN_DB_PREFIX."salarie_prime_flottante WHERE fk_salarie='".$fk_salarie."' AND fk_prime=".$fk_prime;
		$result2 = $db->query($sql);
		$message = "Prime dissociée de ce salarié avec succès";

	}

	//Confirmer la suppression
	if($action == "supprimer_attention_exceptionnelle"){
		$fk_prime = GETPOST("fk_prime", "int");
		$url = $_SERVER['PHP_SELF']."?mainmenu=paiementsalaire&leftmenu=societe&id_societe=".$id_societe."&id_convention=".$id_convention."&fk_prime=".$fk_prime."&fk_salarie=".$fk_salarie."&id=".$fk_user;
		$titre = "Voulez-vous vraiment supprimer cette prime Exceptionnelle";

		  $formconfirm = $monform->formconfirm(
			  $url, 
			  $titre, 
			  "", 
			  'supprimer_exceptionnelle', 
			  $array, 
			  '', 
			  1,
			  180,
			  '35%'
		  );
		  print $formconfirm;
	}

	if($action == "supprimer_exceptionnelle"){
		$fk_prime = GETPOST("fk_prime", "int");

		$result = $db->query("SELECT * FROM ".MAIN_DB_PREFIX."salarie_prime_exceptionnelle WHERE rowid=".$fk_prime);
			$obj_pr = $db->fetch_object($result);

			$result = $db->query("SELECT * FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$fk_user);
			$obj_sal = $db->fetch_object($result);

			//sauvegarde des trace de l'action
			$sql_select = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$user->id;
			$obj = $db->fetch_object($db->query($sql_select));

			$action_effectue = "Suppression d'une prime exceptionnelle ".$obj_pr->libelle." (montant ".$obj_pr->montant.", fin ".$obj_pr->date_limit.") au salarié ".$obj_sal->firstname." ".$obj_sal->lastname." id salarié=".$fk_salarie." de la société ".$obj_soc->name;
			$sql_log = 'INSERT INTO '.MAIN_DB_PREFIX.'log (fk_user, nom, prenom, quand, action_effectue, object_concerne)';
			$sql_log .= ' VALUES('.$user->id.', "'.$obj->lastname.'","'.$obj->firstname.'",now(),"'.$action_effectue.'","Suppression")';
			$db->query($sql_log);

		$sql = "DELETE FROM ".MAIN_DB_PREFIX."salarie_prime_exceptionnelle WHERE rowid=".$fk_prime;
		$result2 = $db->query($sql);

		$message = "Prime exceptionnelle supprimée avec succès";

	}

	//Enregistrement d'une prime flottantes
	if($action  == "save_flottante"){
		$fk_prime = GETPOST("fk_prime", "int");
		$type = GETPOST("type", "alpha");


		if($type=="montant_fixe" && GETPOST("montant_flottant", "int") == "")
			$message = 'Le champs "MONTANT" est obligatoire<br>';
		if($type=="pourcentage" && GETPOST("pourcentage", "int") == "")
			$message = 'Le champs "POURCENTAGE" est obligatoire<br>';

		if(empty($message)){
			$montant_flottant = GETPOST("montant_flottant", "int");
				$fk_prime = GETPOST("fk_prime", "int");
				if($type=="pourcentage")
					$montant_flottant = GETPOST("pourcentage", "int")."%";
				$sql = "DELETE FROM ".MAIN_DB_PREFIX."salarie_prime_flottante WHERE fk_salarie=".$fk_salarie." AND fk_prime='".$fk_prime."'";
				$result2 = $db->query($sql);
				$sql = "INSERT INTO ".MAIN_DB_PREFIX."salarie_prime_flottante (fk_salarie, fk_prime, montant, date_debut) VALUES (".$fk_salarie.",'".$fk_prime."','".$montant_flottant."',now())";
				$result2 = $db->query($sql);

				$sql = "DELETE FROM ".MAIN_DB_PREFIX."salarie_prime WHERE fk_salarie='".$fk_salarie."' AND fk_prime=".$fk_prime;
				$result2 = $db->query($sql);

				$sql = "INSERT INTO ".MAIN_DB_PREFIX."salarie_prime (fk_salarie, fk_prime, mois) VALUES ('".$fk_salarie."',".$fk_prime.", now())";
				$result = $db->query($sql);
				if($result && $result2){
					$message = "Prime Ajoutée uniquement au compte de ce salarié";

					$result = $db->query("SELECT * FROM ".MAIN_DB_PREFIX."primes WHERE rowid=".$fk_prime);
					$obj_pr = $db->fetch_object($result);

					$result = $db->query("SELECT * FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$fk_user);
					$obj_sal = $db->fetch_object($result);

					//sauvegarde des trace de l'action
					$sql_select = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$user->id;
					$obj = $db->fetch_object($db->query($sql_select));

					$action_effectue = "Affectation d'une prime flottante ".$obj_pr->libelle."(".$montant_flottant.") au salarié ".$obj_sal->firstname." ".$obj_sal->lastname." id salarié=".$fk_salarie." de la société ".$obj_soc->name;
					$sql_log = 'INSERT INTO '.MAIN_DB_PREFIX.'log (fk_user, nom, prenom, quand, action_effectue, object_concerne)';
					$sql_log .= ' VALUES('.$user->id.', "'.$obj->lastname.'","'.$obj->firstname.'",now(),"'.$action_effectue.'","Affectation")';
					$db->query($sql_log);
				}else $message = "Un problème est survenu";
			}else{
				$action = "ajout_flotant";
			}

	}
	//Formulaire d'ajout d'une Prime Flottante
	if($action == "ajout_flotant"){
		$fk_prime = GETPOST("fk_prime", "int");
		$sql1 = "SELECT * FROM ".MAIN_DB_PREFIX."primes WHERE rowid=".$fk_prime." AND active=1";
		$result1 = $db->query($sql1);
		$obj1 = $db->fetch_object($result1);
		$sql_fl = "SELECT * FROM ".MAIN_DB_PREFIX."salarie_prime_flottante WHERE fk_salarie='".$fk_salarie."' AND fk_prime=".$fk_prime;
		$result_fl = $db->query($sql_fl);
		if($result_fl)
			$obj_fl = $db->fetch_object($result_fl);

		print '<form name="add_flottante" method="POST" action="'.$_SERVER['PHP_SELF'].'?mainmenu=paiementsalaire&leftmenu=salarie&id_convention='.$id_convention.'&id_societe='.$id_societe.'&fk_salarie='.$fk_salarie.'&id='.$fk_user.'&fk_prime='.$fk_prime.'">';
		print '<fieldset>';
		print '<legend><h3>'.$obj1->libelle.'</h3></legend>';
		print '<input type="hidden" name="token" value="'.newToken().'">';
		print '<input type="hidden" name="action" value="save_flottante">';
		print '<table><tr><td>Type</td><td><div id="m1">Montan Fixe</div></td><td><div id="m2">Pourcentage %</div></td><td></td></tr>';
		print '<tr><td><select name="type" id="type">
		<option value="montant_fixe">Montant Fixe</option>
		<option value="pourcentage">Pourcentage</option>';
		print '<td><input type="text" name="montant_flottant" id="montant_fixe" value="'.GETPOST("montant_flottant", 'int').'" ></td>';
		print '<td><input type="number" name="pourcentage" id="pourcentage" min="1" max="100" value="'.GETPOST("pourcentage", "float").'">';

		print "<td><input type='submit' class='button' value='Ajouter' /></td></tr>";
		//print "<a class='reposition editfielda' href='".$_SERVER['PHP_SELF']."?mainmenu=paiementsalaire&leftmenu=salarie&id_convention=".$id_convention."&fk_salarie=".$fk_salarie."&id=".$fk_user."&fk_prime=".$fk_prime."'><button class='button'>Annuler</button></a></td></tr>";
		print '</table>';
		print '</fieldset>';
		print "</form>";
		print '<br>';
	}
	//sauvegarde de la modififcation d'une prime flottantes
	if($action  == "saveeditValeurFlottante"){
	$fk_prime = GETPOST("fk_prime", "int");
	$type = GETPOST("type", "alpha");

	if($type=="montant_fixe" && GETPOST("montant_flottant", "int") == "")
			$message = 'Le champs "MONTANT" est obligatoire<br>';
		if($type=="pourcentage" && GETPOST("pourcentage", "int") == "")
			$message = 'Le champs "POURCENTAGE" est obligatoire<br>';


	if(empty($message)){
		$montant_flottant = GETPOST("montant_flottant", "int");
		$fk_prime = GETPOST("fk_prime", "int");
			if($type=="pourcentage")
				$montant_flottant = GETPOST("pourcentage", "int")."%";

			$sql = "UPDATE ".MAIN_DB_PREFIX."salarie_prime_flottante SET montant='".$montant_flottant."', date_debut=now() WHERE fk_salarie=".$fk_salarie." AND fk_prime='".$fk_prime."'";
			$result2 = $db->query($sql);

			if($result2){
				$message = "Prime Modifiée avec succès";

				$result = $db->query("SELECT * FROM ".MAIN_DB_PREFIX."primes WHERE rowid=".$fk_prime);
					$obj_pr = $db->fetch_object($result);

					$result = $db->query("SELECT * FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$fk_user);
					$obj_sal = $db->fetch_object($result);

					//sauvegarde des trace de l'action
					$sql_select = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$user->id;
					$obj = $db->fetch_object($db->query($sql_select));

					$action_effectue = "Modification d'une prime flottante ".$obj_pr->libelle."(".$montant_flottant.") au salarié ".$obj_sal->firstname." ".$obj_sal->lastname." id salarié=".$fk_salarie." de la société ".$obj_soc->name;
					$sql_log = 'INSERT INTO '.MAIN_DB_PREFIX.'log (fk_user, nom, prenom, quand, action_effectue, object_concerne)';
					$sql_log .= ' VALUES('.$user->id.', "'.$obj->lastname.'","'.$obj->firstname.'",now(),"'.$action_effectue.'","Modification")';
					$db->query($sql_log);
			}
			else $message = "Un problème est survenu";
		}

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

	//Enregistrement d'une prime exceptionnele
	//Les primes exceptionnelles seront enregistrer en fonction du mois non cloturé sinon le mois en cours du calendrier

	if($action == "add_prime_exceptionnelle"){
		$soumis_impot = GETPOST('soumis_impot', 'alpha');
		$soumis_cotisation = GETPOST('soumis_cotisation', 'alpha');

		if(empty(GETPOST('libelle', 'alpha'))) {
			$message = 'Le champ "LIBELLE" est Obligatoire <br>';
		}
		if(empty(GETPOST('montant', 'int'))) {
			$message .= 'Le champ "MONTANT" est Obligatoire <br>';
		}
			if(empty($message)){
				//Récuperation du mois et l'année non cloturé du bulletin
				$sql_bulletin = "SELECT rowid, annee, mois FROM ".MAIN_DB_PREFIX."bulletin WHERE cloture='non' AND fk_societe=".$id_societe. " ORDER BY rowid DESC";
				$res_bulletin = $db->query($sql_bulletin);
				$nb_jours = cal_days_in_month(CAL_GREGORIAN, date('m'), date('Y'));
				$date = date("Y")."-".date("m")."-".$nb_jours;
				if($res_bulletin){
					$obj_bull = $db->fetch_object($res_bulletin);
					if(!empty($obj_bull)){
						$nb_jours = cal_days_in_month(CAL_GREGORIAN, $obj_bull->mois, $obj_bull->annee);

						if($obj_bull->mois < 10)
							$date = $obj_bull->annee."-0".$obj_bull->mois."-".$nb_jours;
						else $date = $obj_bull->annee."-".$obj_bull->mois."-".$nb_jours;
					}else{
						$sql_bulletin = "SELECT rowid, annee, mois FROM ".MAIN_DB_PREFIX."bulletin WHERE cloture='oui' AND fk_societe=".$id_societe. " ORDER BY rowid DESC";
						$res_bulletin = $db->query($sql_bulletin);
						if($res_bulletin){
							$obj_bull = $db->fetch_object($res_bulletin);
							if(!empty($obj_bull)){
								if((int)$obj_bull->mois == 12){
									$nb_jours = cal_days_in_month(CAL_GREGORIAN, 1, ($obj_bull->annee+1));
									$date = $date = ($obj_bull->annee+1)."-01"."-".$nb_jours;
								}
							}
						}
					}
				}

				$libelle = GETPOST('libelle', "alpha");
				$desc_i = GETPOST('commentaire', "alpha");
				$montant = GETPOST('montant', 'int');
				$affiche_bulletin = GETPOST("affiche_bulletin","alpha");
				$sql = 'INSERT INTO '.MAIN_DB_PREFIX.'salarie_prime_exceptionnelle (fk_salarie, libelle, commentaire, montant, date_limit, affiche_bulletin, soumis_impot, soumis_cotisation) VALUES ('.$fk_salarie.',"'.$libelle.'","'.$desc_i.'","'.$montant.'","'.$date.'","'.$affiche_bulletin.'","'.$soumis_impot.'","'.$soumis_cotisation.'")';
				$result = $db->query($sql);
				print $db->error();
				if($result){
					$message = "Prime Exceptionnelle enregistrée avec succès<br>";

					$result = $db->query("SELECT * FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$fk_user);
					$obj_sal = $db->fetch_object($result);

					//sauvegarde des trace de l'action
					$sql_select = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$user->id;
					$obj = $db->fetch_object($db->query($sql_select));

					$action_effectue = "Ajout d'une prime exceptionnelle ".$libelle."(montant ".$montant." fin ".$date.") au salarié ".$obj_sal->firstname." ".$obj_sal->lastname." id salarié=".$fk_salarie." de la société ".$obj_soc->name;
					$sql_log = 'INSERT INTO '.MAIN_DB_PREFIX.'log (fk_user, nom, prenom, quand, action_effectue, object_concerne)';
					$sql_log .= ' VALUES('.$user->id.', "'.$obj->lastname.'","'.$obj->firstname.'",now(),"'.$action_effectue.'","Ajout")';
					$db->query($sql_log);


				}else{
					$message = "Un problème est survenu !";
					$action = 'create';
				}
			}else $action = 'create';

	}

	//Formulaire de creation d'un Prime Exceptionnelle
	if($action == 'create'){
		print load_fiche_titre($langs->trans("Creation d'une Prime Exceptionnelle pour ce salarié"), '', '');
	print '<hr>';
		print '<div >';
		print '<form name="add_prime_exceptionnelle"  method="POST" action="'.$_SERVER['PHP_SELF'].'?mainmenu=paiementsalaire&leftmenu=salarie&id_convention='.$id_convention.'&id_societe='.$id_societe.'&fk_salarie='.$fk_salarie.'&id='.$fk_user.'">';
		print '<input type="hidden" name="token" value="'.newToken().'">';
		print '<input type="hidden" name="action" value="add_prime_exceptionnelle">';
		print '<table>';
		print '<tr ><td style="width: 200px; padding-top: 10px; padding-right: 30px" class="fieldrequired"><label>Libellé</label></td><td style="width: 500px" id="libelle" ><input style="width: 500px" name ="libelle" value="'.GETPOST("libelle", "alpha").'" size="30" type="text" /></td>';

		print '<tr><td style="width: 200px; padding-top: 10px; padding-right: 30px" class="fieldrequired"><label>Description</label></td><td style="padding-top: 10px"><textarea style="width: 500px" name="commentaire" wrap="soft" cols="50" rows="3">'.GETPOST("commentaire", "alpha").'</textarea>
		</td></tr>';
		print '<tr ><td style="width: 200px; padding-top: 10px; padding-right: 30px" class="fieldrequired"><label>Soumis aux Impôts</label></td><td style="padding-top: 10px">';

		print '<select style="width: 500px" name="soumis_impot" id="soumis_impot" >;
				<option value="Oui" selected >Oui</option>
				<option value="Non" >Non</option>
		</td>';
		print '</tr>';

		print '<tr ><td style="width: 200px; padding-top: 10px; padding-right: 30px" class="fieldrequired"><label>Soumis à Cotisation</label></td><td style="padding-top: 10px">';

		print '<select style="width: 500px" name="soumis_cotisation" id="soumis_cotisation" >;
				<option value="Oui" selected >Oui</option>
				<option value="Non" >Non</option>
		</td>';
		print '</tr>';
		print '<tr><td style="width: 200px; padding-top: 10px; padding-right: 30px" class="fieldrequired"><label>Montant</label></td><td style="padding-top: 10px"><input style="width: 500px" type=""text" value="'.GETPOST("montant", "int").'" name="montant" ></td></tr>';
		print '<tr ><td style="width: 200px; padding-top: 10px; padding-right: 30px" class="fieldrequired"><label>Afficher sur bulletin de paye</label></td><td style="padding-top: 10px">';
		print '<select name="affiche_bulletin" id="affiche_bulletin" style="width: 500px">';

			print '<option value="oui" selected>Oui</option>
					<option value="non" >Non</option>
			</td>';
		print '</tr>';
		print '<tr><td ><br></td></tr>';
		print '<tr><td style=" padding-right: 30px; padding-bottom: 30px"><td style=" padding-bottom: 30px"><input class="button" type="submit" value="Enregistrer" name=""/>';
		print'</form>';
		print '<a href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=prime&action=afficher" class="button">Annuler</a></td></tr>';
		print '</table>';
		print '</div>';
	}

	//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
	if($action != "create"){
		// button plus +
	print_barre_liste("", $page, $_SERVER["PHP_SELF"], "", "", "", "", "", "", 'bill', 0, dolGetButtonTitle("Créer une Prime Exceptionnelle pour ce salarié", '', 'fa fa-plus-circle', $_SERVER['PHP_SELF'].'?mainmenu=paiementsalaire&leftmenu=salarie&id_convention='.$id_convention.'&id_societe='.$id_societe.'&fk_salarie='.$fk_salarie.'&id='.$fk_user.'&fk_prime='.$fk_prime.'&action=create' , '', 1), '', 0, 0, 0, 1);

			print "<div>";
			print "<h3>Primes Associées</h3>";
			print "<table class='tagtable liste' style='width:100%;'>";
			print "<tr class='liste_titre'><td style='padding: 10px; width: 34%;'>Nom Prime</td><td style='padding: 10px; width: 33%;'>Type";
			print "</td><td style='padding: 10px; width: 33%;'>Opération</td></tr>";

			$array_flottant = array();
			$salSql = "SELECT * FROM ".MAIN_DB_PREFIX."salarie where rowid=".$fk_salarie;
			$result = $db->query($salSql);
			$tab = array();
			if(!empty($result))
				$salarie = $db->fetch_object($result);

				$sql_flottante = "SELECT fk_prime FROM ".MAIN_DB_PREFIX."condition_prime WHERE type_montant='flottante' ";
				$result_flottante = $db->query($sql_flottante);
				if($result_flottante){
					$i = 0;
					$num_flottante = $db->num_rows($result_flottante);
					while ($i < $num_flottante){
						$obj_flottante = $db->fetch_object($result_flottante);
						if ($obj_flottante)
						{
							$array_flottant[$i] = $obj_flottante->fk_prime;
						}
						$i ++;
					}

				}

				//les Primes obligatoires
			$sql = "SELECT * FROM ".MAIN_DB_PREFIX."primes WHERE type_prime='obligatoire' AND active=1 AND ((fk_societe=0 AND fk_convention=0) OR (fk_societe=".$id_societe." OR fk_convention=".$id_convention."))";
			$result = $db->query($sql);
			if($result){
				$i = 0;
				$num1 = $db->num_rows($result);
				while ($i < $num1){
					$obj = $db->fetch_object($result);
					if ($obj)
					{
							$tab[$i] = $obj->rowid;
							print "<tr class='impair'><td>";
							print ''.$obj->libelle.'</td><td>'.$obj->type_prime;
							print "</td><td>Automatique</td></tr>";
					}
					$i ++;
				}
			}
				//Primes Primes liées à la catégorie
			$sql = "SELECT fk_prime FROM ".MAIN_DB_PREFIX."categorie_prime WHERE fk_categorie=".$salarie->fk_categorie." AND fk_prime NOT IN (0";
			$a = 0;
				while ($a < count($tab)) {
						$sql .= ", ".$tab[$a]."";
					$a ++;
				}
				$sql .= ")";
			$result = $db->query($sql);
			if($result){
				$i = 0;
				$num2 = $db->num_rows($result);
				while ($i < $num2){
					$obj = $db->fetch_object($result);
					if ($obj)
					{
						$sql1 = "SELECT * FROM ".MAIN_DB_PREFIX."primes WHERE rowid=".$obj->fk_prime." AND active=1 AND ((fk_societe=0 AND fk_convention=0) OR (fk_societe=".$id_societe." OR fk_convention=".$id_convention."))";
						$result1 = $db->query($sql1);
						$obj1 = $db->fetch_object($result1);
						if($obj1){
							print "<tr class='impair'><td>";
							print ''.$obj1->libelle.'</td><td>'.$obj1->type_prime;
							print "</td><td>Ind Categorie</td></tr>";
						}
					}
					$i ++;
				}
			}




			$sql = "SELECT * FROM ".MAIN_DB_PREFIX."salarie_prime WHERE fk_salarie=".$fk_salarie;
			$result = $db->query($sql);
			if($result){
				$i = 0;
				$num = $db->num_rows($result);
				while ($i < $num){
					$obj = $db->fetch_object($result);
					if ($obj)
					{
						$sql1 = "SELECT * FROM ".MAIN_DB_PREFIX."primes WHERE rowid=".$obj->fk_prime." AND active=1 AND ((fk_societe=0 AND fk_convention=0) OR (fk_societe=".$id_societe." OR fk_convention=".$id_convention."))";
						$result1 = $db->query($sql1);
						$obj1 = $db->fetch_object($result1);
						if($obj1)

							if(in_array($obj1->rowid, $array_flottant)){//affichage des primes flottantes
								print "<tr class='impair'><td>";
								$sql_fl = "SELECT * FROM ".MAIN_DB_PREFIX."salarie_prime_flottante WHERE fk_salarie=".$fk_salarie." AND fk_prime=".$obj->fk_prime;
								$result_fl = $db->query($sql_fl);
								if($result_fl){
									$obj_fl = $db->fetch_object($result_fl);
										print $obj1->libelle.'</td><td><input type="text" disabled size="7" name="montant_flottant" value="'.apres_virgule($db, $id_societe, $obj_fl->montant?:0).'" >';

									print "<a class='reposition editfielda' href='".$_SERVER['PHP_SELF']."?mainmenu=paiementsalaire&leftmenu=salarie&id_convention=".$id_convention."&id_societe=".$id_societe."&fk_salarie=".$fk_salarie."&id=".$fk_user."&action=edit_flotant&fk_prime=".$obj->fk_prime."'>".img_edit()."</a></td>";
									print "<td><a class='reposition editfielda' href='".$_SERVER['PHP_SELF']."?mainmenu=paiementsalaire&leftmenu=salarie&id_convention=".$id_convention."&id_societe=".$id_societe."&fk_salarie=".$fk_salarie."&id=".$fk_user."&action=supprimer_attention&id_convention=".$id_convention."&fk_prime=".$obj->fk_prime."'><button class='button'>Dissocier</button></a></td></tr>";
								}
							}else{//affcihage des prime non flottantes
								print "<tr class='impair'><td>";
								print ''.$obj1->libelle.'</td><td>'.$obj1->type_prime;
								print "</td><td><a class='reposition editfielda' href='".$_SERVER['PHP_SELF']."?mainmenu=paiementsalaire&id_societe=".$id_societe."&leftmenu=salarie&fk_salarie=".$fk_salarie."&id=".$fk_user."&action=supprimer_attention&id_convention=".$id_convention."&fk_prime=".$obj->fk_prime."'><button class='button'>Dissocier</button></a></td></tr>";
						}


					}
					$i ++;
				}
			}
			//affichage des primes exceptionnelle
			$sql_pr_except = "SELECT * FROM ".MAIN_DB_PREFIX."salarie_prime_exceptionnelle WHERE fk_salarie=".$fk_salarie;
			$res_bulletin_pr  = $db->query($sql_pr_except);
			$num = $db->num_rows($res_bulletin_pr);
			$nb = 0;
			while( $nb < $num){
				$obj_except = $db->fetch_object($res_bulletin_pr);

				$sql_bulletin_pr_except = "SELECT fk_bulletin FROM ".MAIN_DB_PREFIX."bulletin_prime_exceptionnelle WHERE fk_prime=".$obj_except->rowid;
				$res_bulletin_pr_except  = $db->query($sql_bulletin_pr_except);
				$num_pr_except = $db->num_rows($res_bulletin_pr_except);
				//print $num_pr_except.'**';
				if($num_pr_except > 0) {
					$obj_bull_pr = $db->fetch_object($res_bulletin_pr_except);
					$sql_bulletin = "SELECT rowid, cloture FROM ".MAIN_DB_PREFIX."bulletin WHERE rowid=".$obj_bull_pr->fk_bulletin." AND cloture != 'oui'";
					$res_bulletin  = $db->query($sql_bulletin);

					$nbre = $db->num_rows($res_bulletin);
					//print $nbre;
					if( $nbre > 0){
						$obj_b = $db->fetch_object($res_bulletin);
						if($obj_b->cloture == 'non' || $obj_b->cloture == 'Non'){
							$var = "Prime Exceptionnelle : ".$obj_except->date_limit." Soumise au impôt ".$obj_except->soumis_impot." Soumise à cotisation ".$obj_except->soumis_cotisation." et afficher sur bulletin ".$obj_except->affiche_bulletin;
							print "<tr class='impair'><td>";
							print img_error($var).' <span>'.$obj_except->libelle.'</span></td><td>'.apres_virgule($db, $id_societe, $obj_except->montant);
							print "</td><td><a class='reposition editfielda' href='".$_SERVER['PHP_SELF']."?mainmenu=paiementsalaire&id_societe=".$id_societe."&leftmenu=salarie&fk_salarie=".$fk_salarie."&id=".$fk_user."&action=supprimer_attention_exceptionnelle&id_convention=".$id_convention."&fk_prime=".$obj_except->rowid."'><button class='button'>Dissocier</button></a></td></tr>";
						}else{
							/*$var = "Prime Exceptionnelle : ".$obj_except->date_limit." Soumise au impôt ".$obj_except->soumis_impot." Soumise à cotisation ".$obj_except->soumis_cotisation." et afficher sur bulletin ".$affiche_bulletin;
							print "<tr class='impair'><td>";
							print img_error($var).' <span>'.$obj_except->libelle.'</span></td><td>'.apres_virgule($db, $id_societe, $obj_except->montant);
							print "</td><td><a class='reposition editfielda' href='".$_SERVER['PHP_SELF']."?mainmenu=paiementsalaire&id_societe=".$id_societe."&leftmenu=salarie&fk_salarie=".$fk_salarie."&id=".$fk_user."&action=supprimer_attention_exceptionnelle&id_convention=".$id_convention."&fk_prime=".$obj_except->rowid."'><button class='button'>Dissocier</button></a></td></tr>";
						*/}

					}
				}else{
					$var = "Prime Exceptionnelle : ".$obj_except->date_limit." Soumise au impôt ".$obj_except->soumis_impot." Soumise à cotisation ".$obj_except->soumis_cotisation." et afficher sur bulletin ".$obj_except->affiche_bulletin;
					print "<tr class='impair'><td>";
					print img_error($var).' <span>'.$obj_except->libelle.'</span></td><td>'.apres_virgule($db, $id_societe, $obj_except->montant);
					print "</td><td><a class='reposition editfielda' href='".$_SERVER['PHP_SELF']."?mainmenu=paiementsalaire&id_societe=".$id_societe."&leftmenu=salarie&fk_salarie=".$fk_salarie."&id=".$fk_user."&action=supprimer_attention_exceptionnelle&id_convention=".$id_convention."&fk_prime=".$obj_except->rowid."'><button class='button'>Dissocier</button></a></td></tr>";
				}

				$nb ++;
			}

				/*if($trouve)
					print "<tr><td colspan='3' align='center'>Aucune Prime Archivée</td></tr>";*/
			print "</table></div>";


	//--------------------------------------------------------------------------------

				//Prime disponibles non associés
				print "<br>";
				print "<div>";
				print "<h3>Primes disponibles</h3>";
				print "<table class='tagtable liste' style='width:100%;'>";
				print "<tr class='liste_titre' ><td style='padding: 10px; width: 34%;'>Nom Prime</td><td style='padding: 10px; width: 33%;'>Type";
				print "</td><td style='padding: 10px; width: 33%;'>Opération</td></tr>";

				//recuperation des Primes associés
			$sql = "SELECT fk_prime FROM ".MAIN_DB_PREFIX."salarie_prime WHERE fk_salarie=".$fk_salarie;
			$result = $db->query($sql);
			$array = array();
			if($result){
				$i = 0;
				$num = $db->num_rows($result);
				while ($i < $num){
					$obj = $db->fetch_object($result);
					if($obj){
						$array[$i] = $obj->fk_prime;
					}
					$i ++;
				}
			}

			$sql = "SELECT * FROM ".MAIN_DB_PREFIX."primes Where type_prime='obligatoire' AND ((fk_societe=0 AND fk_convention=0) OR (fk_societe=".$id_societe." OR fk_convention=".$id_convention."))";
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

			$sql = "SELECT fk_prime FROM ".MAIN_DB_PREFIX."categorie_prime Where fk_categorie=".$salarie->fk_categorie;
			$result = $db->query($sql);
			$taille = count($array);
			if($result){
				$i = 0;
				$num = $db->num_rows($result);
				while ($i < $num){
					$obj = $db->fetch_object($result);
					if($obj){
						$array[$taille + $i] = $obj->fk_prime;
					}
					$i ++;
				}
			}
			$sql = "SELECT * FROM ".MAIN_DB_PREFIX."primes WHERE rowid NOT IN (0";
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
						print $obj->libelle.'</td><td>'.$obj->type_prime;

						$sql_fl = "SELECT * FROM ".MAIN_DB_PREFIX."condition_prime WHERE fk_prime=".$obj->rowid." AND type_montant='flottante'";
						$result_fl = $db->query($sql_fl);
						if($result_fl)
							$obj_fl = $db->fetch_object($result_fl);
						if($obj_fl){
							print "</td><td><a class='reposition editfielda' href='".$_SERVER['PHP_SELF']."?mainmenu=paiementsalaire&leftmenu=salarie&id_convention=".$id_convention."&id_societe=".$id_societe."&fk_salarie=".$fk_salarie."&id=".$fk_user."&action=ajout_flotant&fk_prime=".$obj->rowid."'><button id='associer' class='button'>Associer</button></a>".info_admin($langs->trans("Prime flottante dont la valeur depend du salarié"), 1)."</td></tr>";
						}else
						print "</td><td><a class='reposition editfielda' href='".$_SERVER['PHP_SELF']."?mainmenu=paiementsalaire&leftmenu=salarie&id_convention=".$id_convention."&id_societe=".$id_societe."&fk_salarie=".$fk_salarie."&id=".$fk_user."&action=associer&fk_prime=".$obj->rowid."'><button id='associer' class='button'>Associer</button></a></td></tr>";
					}
					$i ++;
				}
				if($num == 0 )
					print "<tr><td colspan='3' align='center'>Aucune Prime disponible</td></tr>";

			}else
			print "<tr><td colspan='3' align='center'>Aucune Prime disponible</td></tr>";




		print "</table></div>";



	//-------------------------------------------------------------------------------------------------------------------
	//Les primes Archivées
	print "<br><div>";
			print "<h3 >Primes Archivées</h3>";
			print "<table class='tagtable liste' style='width:100%;'>";
			print "<tr class='liste_titre'><td style='padding: 10px; width: 34%;'>Nom Prime</td><td style='padding: 10px; width: 33%;'>Type";
			print "</td><td style='padding: 10px; width: 33%;'>Opération</td></tr>";

			//affichage des primes exceptionnelle
			$trouve = false;
			$sql_bulletin = "SELECT rowid, mois, annee FROM ".MAIN_DB_PREFIX."bulletin WHERE cloture='oui' AND fk_salarie=".$fk_salarie." AND fk_societe=".$id_societe;
			$res_bulletin  = $db->query($sql_bulletin);
			$num_b = $db->num_rows($res_bulletin);
			$a = 0;
			while ($a < $num_b) {
				$obj = $db->fetch_object($res_bulletin);

				$sql_bulletin_pr_except = "SELECT * FROM ".MAIN_DB_PREFIX."bulletin_prime_exceptionnelle WHERE fk_bulletin=".$obj->rowid;
				$res_bulletin_pr_except  = $db->query($sql_bulletin_pr_except);
				$num_pr_except = $db->num_rows($res_bulletin_pr_except);
				$b = 0;
				while ($b < $num_pr_except) {
					$obj_bull_pr = $db->fetch_object($res_bulletin_pr_except);
			
						$trouve = true;
						$obj_except = $db->fetch_object($res_bulletin_pr);
						$var = "Prime Exceptionnelle : ".$obj->mois."/".$obj->annee;
						print "<tr class='impair'><td>";
						print img_error($var).' <span>'.$obj_except->libelle.'</span></td><td>'.apres_virgule($db, $id_societe, $obj_except->montant);
						print "</td><td><button disabled class='button'>Dissocier</button></td></tr>";

					$b ++;
				}

				$a ++;
			}

				if(!$trouve)
					print "<tr><td colspan='3' align='center'>Aucune Prime Archivée</td></tr>";
					print "</table></div>";
	}
	}
	$db->close();

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
