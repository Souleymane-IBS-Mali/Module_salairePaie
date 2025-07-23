<?php
require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/custom/paiementsalaire/lib/paiementsalaire.lib.php';




llxHeader("", "Paiement | Salaire");
//Titre
print load_fiche_titre($langs->trans("Information de base du salarié"), '', '');


//print '<hr>';
$info_urgente="";
$id_societe = GETPOST("id_societe","int");
$id_salarie = GETPOST("id_salarie","int");

$array_id_soc = array();
	$sql = "SELECT fk_soc FROM ".MAIN_DB_PREFIX."societe_commerciaux";
	$sql .= " WHERE fk_user=".$user->id;
	$result = $db->query($sql);
	if($result){
		$i = 0;
		$num = $db->num_rows($result);
		while ($i < $num){
			$array_id_soc[] = $db->fetch_object($result)->fk_soc;
			$i ++;
		}
	}
if($user->id == 1)
	$array_id_soc[] = $id_societe;

$fk_user = GETPOST("id","int");
if(empty($fk_user))
	$fk_user = GETPOST("fk_user","int");

$id_convention = GETPOST("id_convention","int");
$action = GETPOST('action','alpha')?:"detail";
$message = "";
if($user->id !=1 && $user->id != $fk_user && !$user->rights->paiementsalaire->salarie->read && in_array($id_societe, $array_id_soc)){
	print "<h2> Vous n\'avez pas ce droit </h2>";
}else{
	if(empty($id_convention) || empty($id_societe)){
		$sql = "SELECT egp FROM ".MAIN_DB_PREFIX."user_extrafields WHERE fk_object=".$fk_user;
		$result = $db->query($sql);
		$obj1 = $db->fetch_object($result);
		if(empty($obj1->egp))
			die("Ce salarié n'a pa de société");
		//Par defaut tous les salariés ont travaillé le maximum de jours du mois en cours
		salarie_nb_jour($db, $id_societe);
		//--------------------------------
		$sql = "SELECT conv FROM ".MAIN_DB_PREFIX."societe_extrafields WHERE fk_object=".$obj1->egp;
		$result = $db->query($sql);
		$obj2 = $db->fetch_object($soc_res);

		$sql = "SELECT matricule FROM ".MAIN_DB_PREFIX."salarie WHERE fk_user=".$fk_user;
		$result = $db->query($sql);
		$obj3 = $db->fetch_object($result);


		//initialisation
		$id_societe = $obj1->egp;
		$id_convention = $obj2->conv;
		$matricule_salarie = $obj3->matricule;

	}
	$monform = new Form($db);

	//Confirmation de la suppression
	if($action == "supprimer"){

		//Verification des avance accompte
		$sql = "SELECT * FROM ".MAIN_DB_PREFIX."salarie_avance WHERE fk_salarie=".$fk_salarie;
		$sql .= " AND CONVERT(montant_paye, float) < CONVERT(montant_total, float)";// OR  (montant_paye = montant_total AND ((annee_debut_paiement<=".$annee." AND mois_debut_paiement<=".$mois."))))";
		$num = $db->num_rows($db->query($sql));

		if($num > 0){
			$id_salarie = GETPOST("fk_salarie", "int");
			$text = "Avance/accompte en cours de paiement pour ce salarié";
			$formconfirm = $monform->formconfirm(
				$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=salarie&id_societe='.$id_societe.'&id_convention='.$id_convention.'&fk_user='.$fk_user.'&id_salarie='.$id_salarie,
				'Suppression impossible !',
				$text,
				'detail',
				'',
				'',
				1,
				40,
				'30%'
			);
		}else{
			$id_salarie = GETPOST("fk_salarie", "int");
			$formconfirm = $monform->formconfirm(
				$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=salarie&id_societe='.$id_societe.'&id_convention='.$id_convention.'&fk_user='.$fk_user.'&id_salarie='.$id_salarie,
				'Veuillez confirmer cette suppression?',
				$text,
				'supprimer_ok',
				'',
				'',
				1,
				40,
				'30%'
			);
		}
		$action = 'detail';
				print $formconfirm;
	}
	//suppression du salarié
	if($action == "supprimer_ok"){
		$id_salarie = GETPOST('id_salarie', 'int');

		//suppression dans salarié prime flottante
		$sql = "DELETE FROM ".MAIN_DB_PREFIX."salarie_prime WHERE fk_salarie='".$id_salarie."'";
		$result1 = $db->query($sql);
		//suppression dans salarié indemnité flottante
		$sql = "DELETE FROM ".MAIN_DB_PREFIX."salarie_indemnite WHERE fk_salarie='".$id_salarie."'";
		$result2 = $db->query($sql);
		//suppression dans salarié prime exceptionnelle
		$sql = "DELETE FROM ".MAIN_DB_PREFIX."salarie_prime_exceptionnelle WHERE fk_salarie=".$id_salarie;
		$result3 = $db->query($sql);
		//suppression dans salarié heure sup
		$sql = "DELETE FROM ".MAIN_DB_PREFIX."salarie_heure_sup WHERE fk_salarie=".$id_salarie;
		$result4 = $db->query($sql);
		//Avance accompte

		//
			$sql_del = "DELETE FROM ".MAIN_DB_PREFIX."salarie WHERE rowid=".$id_salarie;
			if($db->query($sql_del))
				$message = 'Salarié supprimé avec succès';
			else $message = "Un problème est survenu";

		$action = 'detail';
	}

	if($action == "save_edit"){

		$existSql = "SELECT rowid, matricule FROM ".MAIN_DB_PREFIX."salarie WHERE fk_user=".$fk_user;
		$existResult = $db->query($existSql);
		$existSalarie = $db->fetch_object($existResult);

		if(empty($existSalarie->matricule)){

			if(!GETPOST("matricule", "alpha"))
				$message = 'Le champ "MATRICULE" est obligatoire<br>';
			if(!GETPOST("categories"))
				$message .= 'Le champ "CATEGORIE" est obligatoire<br>';
			if(!GETPOST("statut_f", "alpha"))
				$message .= 'Le champ "SITUATION FAMILIALE" est obligatoire<br>';

			if(GETPOST("nb_enfant","int") == null)
				$message .= 'Le champ "NOMBRE ENFANT" est obligatoire<br>';

			if(GETPOST("nombre_enfant_hand", "int") == null)
				$message .= 'Le champ "NOMBRE ENFANT HANDICAPE" est obligatoire<br>';
			
			$inps = GETPOST("inps","alpha");
			$compte = GETPOST("compte","alpha");
			$amo = GETPOST("amo","alpha");
			if(!empty($compte) && $compte[0] != 'M' && $compte[1] != 'L')
				$message .= 'Le "NUMERO DE COMPTE" doit commencer par "ML"<br>';
			if(!empty($inps) && strlen($inps) != 11)
				$message .= 'Le numéro "INPS" doit être exactement 11 caractère<br>';
			if(!empty($amo) && strlen($amo) != 13)
				$message .= 'Le numéro "AMO" doit être exactement 13 caractère<br>';

			if(empty($message)){
				$tab_cat_ech = explode("/",GETPOST("categories"));
				$categ = $tab_cat_ech[0];
				if(count($tab_cat_ech)>1)
					$echel = $tab_cat_ech[1];
				else $echel = 0;


					$situation_f = GETPOST("statut_f", "alpha");
					$diplome = GETPOST("diplome","int");

					$type = GETPOST("type_salarie", 'int');

					$nb_enfant = GETPOST("nb_enfant", "int");
					$calcul_salaire = GETPOST("calcul_salaire", "alpha");

					$inps = GETPOST("inps","alpha");
					$compte = GETPOST("compte","alpha");
					$fk_type_banque = GETPOST("fk_type_banque","int");
					$nombre_enfant_hand = GETPOST("nombre_enfant_hand","int") ;
					$type_contrat = GETPOST("type_contrat","int") ;
					$amo = GETPOST("amo","alpha");

					$trouve = false;
					$mat = GETPOST("matricule", "alpha");

					$existSql =' SELECT * FROM '.MAIN_DB_PREFIX.'salarie WHERE matricule="'.$mat.'"';
					$existResult = $db->query($existSql);
					$num = $db->num_rows($existResult);
					if($num > 0)
						$message = "Ce MATRICULE (".$mat.") existe déjà";

					//Insertion dans la table salarie
					if(empty($message)){

						$sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'salarie (matricule, situation_familiale, nombre_enfant, nombre_enfant_hand, calcul_salaire, fk_user, fk_categorie, fk_echelon';
						$sql_values .= ' VALUES("'.$mat.'","'.$situation_f.'",'.$nb_enfant.','.$nombre_enfant_hand.',"'.$calcul_salaire.'",'.$fk_user.','.$categ.','.$echel;

						if($type > 0){
							$sql_insert .= ', type_salarie';
							$sql_values .= ','.$type;
						}

						if($diplome > 0){
							$sql_insert .= ', fk_diplome';
							$sql_values .= ','.$diplome;
						}

						if(!empty($fk_type_banque)){
							$sql_insert .= ', fk_type_banque';
							$sql_values .= ','.$fk_type_banque;
						}
						if(!empty($inps)){
							$sql_insert .= ', inps';
							$sql_values .= ',"'.$inps.'"';
						}
						if(!empty($amo)){
							$sql_insert .= ', amo';
							$sql_values .= ',"'.$amo.'"';
						}
						if(!empty($compte)){
							$sql_insert .= ', compte';
							$sql_values .= ',"'.$compte.'"';
						}

						$sql_insert .= ')';
						$sql_values .= ')';

						$sql = $sql_insert."".$sql_values;

						$result = $db->query($sql);
						if($result){

							$sql_select = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$user->id;
							$obj = $db->fetch_object($db->query($sql_select));

							$sql_select = "SELECT firstname, lastname, dateemployment FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$fk_user;
							$obj_u = $db->fetch_object($db->query($sql_select));

							$sql_select = "SELECT nom FROM ".MAIN_DB_PREFIX."societe WHERE rowid=".$id_societe;
							$obj_s = $db->fetch_object($db->query($sql_select));

							$action_effectue = "Ajout d'un salarié (N : ".$obj_u->lastname.", P : ".$obj_u->firstname.", Date_emb : ".$obj_u->dateemployment.", mat/sf/nb_enf/nb_enf_hand/nb_conj/categ/echel : ".$mat."/".$situation_f."/".$nb_enfant."/".$nombre_enfant_hand."/".$nb_conj."/".$categ."/".$echel.") de la société ".$obj_s->nom;
							$sql_log = 'INSERT INTO '.MAIN_DB_PREFIX.'log (fk_user, nom, prenom, quand, action_effectue, object_concerne)';
							$sql_log .= ' VALUES('.$user->id.', "'.$obj->lastname.'","'.$obj->firstname.'",now(),"'.$action_effectue.'","Ajout Salarié")';
							$db->query($sql_log);
							$message = 'SALARIE enregistrée avec succès';
							$action = 'detail';
						}else{
							$message = 'Un problème est Survenu';
							$action = 'edit';
						}

					}else $action = 'edit';

			}else $action = 'edit';

		}else{
			if(!GETPOST("matricule", "alpha"))
				$message = 'Le champ "MATRICULE" est obligatoire<br>';
			if(empty(GETPOST("categories")))
				$message .= 'Le champ "CATEGORIE" est obligatoire<br>';

			if(empty(GETPOST("statut_f", "alpha")))
				$message .= 'Le champ "SITUATION FAMILIALE" est obligatoire<br>';


			if(GETPOST("nb_enfant","int") == null)
				$message .= 'Le champ "NOMBRE ENFANT" est obligatoire<br>';

			if(GETPOST("nombre_enfant_hand", "int") == null)
				$message .= 'Le champ "NOMBRE ENFANT HANDICAPE" est obligatoire<br>';

			$inps = GETPOST("inps","alpha");
			$compte = GETPOST("compte","alpha");
			$amo = GETPOST("amo","alpha");
			if(!empty($compte))
				if( strlen($compte) != 24 && strlen($compte) != 25 && strlen($compte) != 26 && strlen($compte) != 27 && strlen($compte) != 8 && strlen($compte) != 11){
					$message .= 'Le "NUMERO DE COMPTE" est icorrect<br>';
				}else{

					if(strlen($compte) != 8 && strlen($compte) != 11){
						$prefixe = $compte[0].$compte[1].$compte[2].$compte[3].$compte[4];
						$code = ["ML016", "ML041", "ML043", "ML045", "ML089", "ML090", "ML102", "ML109", "ML135", "ML173", "ML206", "ML181"];
						if(!in_array($prefixe, $code))
							$message .= 'Le "NUMERO DE COMPTE" est incorrect<br>';
					}
				}
				if(!empty($inps) && strlen($inps) != 11)
					$message .= 'Le numéro "INPS" doit être exactement 11 caractère<br>';
				if(!empty($amo) && strlen($amo) != 13)
					$message .= 'Le numéro "AMO" doit être exactement 13 caractère<br>';

			if(empty($message)){
				$mat = GETPOST("matricule", "alpha");
				if(strcmp($mat, $existSalarie->matricule) == 0){
					$existSql ='SELECT rowid FROM '.MAIN_DB_PREFIX.'salarie WHERE matricule="'.$mat.'"';
					$existResult = $db->query($existSql);

					$num = $db->num_rows($existResult);
					if($num > 1)
						$message = "Ce MATRICULE (".$mat.") existe déjà";

				}

				if(empty($message)){
					$tab_cat_ech = explode("/",GETPOST("categories"));

					$categ = $tab_cat_ech[0];
					if(count($tab_cat_ech)>1)
						$echel = $tab_cat_ech[1];
					else $echel = 0;

					$situation_f = GETPOST("statut_f", "alpha");
					$nb_enfant = GETPOST("nb_enfant","int");
					$calcul_salaire = GETPOST("calcul_salaire","alpha") ;
					$surSal = 0;
					$type_salarie = GETPOST("type_salarie","int");
					$nombre_enfant_hand = GETPOST("nombre_enfant_hand","int") ;
					$type_contrat = GETPOST("type_contrat","int") ;
					$diplome = GETPOST("diplome","int");
					$inps = GETPOST("inps","alpha");
					$amo = GETPOST("amo","alpha");
					$archiver = GETPOST("archiver","alpha");

					$compte = GETPOST("compte","alpha");
					$fk_type_banque = GETPOST("fk_type_banque","int");


					$virgule = 0;
					$sql = 'UPDATE '.MAIN_DB_PREFIX.'salarie SET calcul_salaire="'.$calcul_salaire.'", archiver="'.$archiver.'"';

					if(!empty($mat)){
						$sql .= ', matricule="'.$mat.'"';
					}
					if(!empty($situation_f)){
						$sql .= ', situation_familiale="'.$situation_f.'"';
					}
					if($nb_enfant != ''){
							$sql .= ', nombre_enfant='.$nb_enfant;

					}
					if($nombre_enfant_hand != ''){
						$sql .= ', nombre_enfant_hand='.$nombre_enfant_hand;
					}


					if(!empty($categ)){
						$sql .= ', fk_categorie='.$categ;
					}

					$sql .= ', fk_echelon='.$echel;
					if(!empty($fk_type_banque))
						$sql .= ', fk_type_banque='.$fk_type_banque;
					if(!empty($inps))
						$sql .= ', inps="'.$inps.'"';

					if(!empty($amo))
						$sql .= ', amo="'.$amo.'"';

					if(!empty($compte))
						$sql .= ', compte="'.$compte.'"';

					$sql .= ', type_salarie='.($type_salarie?:0);

					if(!empty($type_contrat)){
						$sql .= ', type_contrat='.$type_contrat;
					}

					$sql .= ', fk_diplome='.($diplome?:0);


					$sql .= ' WHERE rowid='.$existSalarie->rowid;

					//Récuperation des ancienne information avant modification
					$existSql ='SELECT * FROM '.MAIN_DB_PREFIX.'salarie WHERE matricule="'.$mat.'"';
					$existResult1 = $db->query($existSql);
					$existSalarie1 = $db->fetch_object($existResult1);

					$result_edit = $db->query($sql);
					
					if($result_edit){
						
						$sql_select = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$user->id;
						$obj = $db->fetch_object($db->query($sql_select));

						$sql_select = "SELECT firstname, lastname, dateemployment FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$fk_user;
						$obj_u = $db->fetch_object($db->query($sql_select));

						$sql_select = "SELECT nom FROM ".MAIN_DB_PREFIX."societe WHERE rowid=".$id_societe;
						$obj_s = $db->fetch_object($db->query($sql_select));

						// de
						$action_effectue .= "Modifiacation d'un salarié (N : ".$obj_u->firstname.", P : ".$obj_u->lastname.", Date_emb : ".$obj_u->dateemployment.", archiver : ".$existSalarie1->archiver."/matricule : ".$existSalarie1->matricule."/statut_mat : ".$existSalarie1->situation_familiale."/nb_enf : ".$existSalarie1->nombre_enfant."/nb_enf_hand : ".$existSalarie1->nombre_enfant_hand."/statut_actif: ".$existSalarie1->calcul_salaire."/categ : ".$existSalarie1->fk_categorie."/echel : ".$existSalarie1->fk_echelon."/amo : ".$existSalarie1->amo."/inps : ".$existSalarie1->inps."/compte : ".$existSalarie1->compte.") de la société ".$obj_s->nom;

						//à
						$action_effectue .= " ==> Modifiacation d'un salarié (N : ".$obj_u->firstname.", P : ".$obj_u->lastname.", Date_emb : ".$obj_u->dateemployment.", archiver : ".$archiver."/matricule : ".$mat."statut_mat".$situation_f."/nb_enf : ".$nb_enfant."/nb_enf_hand : ".$nombre_enfant_hand."/statut_actif : ".$calcul_salaire."/categ : ".$categ."/echel : ".$echel."/amo : ".$amo."/inps : ".$inps."/compte : ".$compte.") de la société ".$obj_s->nom;
						$sql_log = 'INSERT INTO '.MAIN_DB_PREFIX.'log (fk_user, nom, prenom, quand, action_effectue, object_concerne)';
						$sql_log .= ' VALUES('.$user->id.', "'.$obj->lastname.'","'.$obj->firstname.'",now(),"'.$action_effectue.'","Modification Salarié")';
						$db->query($sql_log);
						$message = 'SALARIE modifié avec succès';
						$action = 'detail';
					}else {
						$message = 'Un problème est survenu';
						$action = 'edit';
					}

				}else $action = 'edit';

			}else $action = 'edit';
			}

	}

	$mois_tab = array(" Janvier "," Février "," Mars "," Avril "," Mai "," Juin "," Juillet "," Août "," Septembre "," Octobre "," Novembre "," Décembre ");

	//recupétration des information sur l'utilisateur dans la table user Dolibarr
	$userSQL = "SELECT * FROM ".MAIN_DB_PREFIX."user where rowid=".$fk_user;
	$result = $db->query($userSQL);
	$userD = $db->fetch_object($result);

	//Recupération des information dans la table salarié
	$salSql = "SELECT * FROM ".MAIN_DB_PREFIX."salarie where fk_user=".$fk_user;
	$result = $db->query($salSql);
	$info = "";
	if(!empty($result)){
		$salarie = $db->fetch_object($result);

		$head = salaire_Head($salarie->rowid,$fk_user, $id_societe, $id_convention);
		print dol_get_fiche_head($head, 'information', "", -1, '');
		if($salarie->rowid){
			$annee = date('Y');
			$mois = date('m');
			$salSql = "SELECT * FROM ".MAIN_DB_PREFIX."salarie_nombre_jour_travaille where fk_salarie=".$salarie->rowid." AND annee=".$annee." AND mois=".$mois;
			$result = $db->query($salSql);
			$num = $db->num_rows($result);
			if($num == 0){
				$jour = cal_days_in_month(CAL_GREGORIAN, $mois, $annee);
				
				$sql = 'INSERT INTO '.MAIN_DB_PREFIX.'salarie_nombre_jour_travaille (fk_societe, fk_salarie, annee, mois, jour)';
				$sql .= ' VALUES('.$id_societe.','.$salarie->rowid.','.$annee.','.$mois.','.$jour.')';
				$db->query($sql);
			}


			$soc_sql = "SELECT * FROM ".MAIN_DB_PREFIX."societe WHERE rowid=".$id_societe;
			$soc_res = $db->query($soc_sql);//= $db->query($covSql);
			$obj_soc = $db->fetch_object($soc_res);

			$obj_soc = prepare_objet_entete($salarie->rowid, $fk_user, $db, $id_societe, $id_convention);
			entete_societe($obj_soc, 'societe');
			
		}else $info = "<br><b><mark>Merci de compléter les informations obligatoires avant de pouvoir générer le salaire pour ce salarié</mark></b><br>";
			if($salarie->rowid)
				verification_contrat_salarie($db, $salarie->rowid);
			print $info;

				if(empty($info) && empty($salarie->rowid))
					print "<mark>Ce salarié n'est pas enregistré</mark><br>";
			
	}else{
		$info = "<br><b><mark>Merci de compléter les informations obligatoires avant de pouvoir générer le salaire pour ce salarié</mark></b><br>";
		print $info;
	}
	if(empty($action))
		$action = 'detail';

	if($action == "detail"){

		//recupération de la catégorie de l'utilisateur dans la table Salarie
			$CatSQL = "SELECT * FROM ".MAIN_DB_PREFIX."dcategories where rowid=".$salarie->fk_categorie;
			$catResult = $db->query($CatSQL);
			if($catResult){
				$catSalarie = $db->fetch_object($catResult);
				$categ = $catSalarie->code_categorie;
			}else $categ = "Non associé";
	//----------------------------------------------------------------------------------
			print '<div class="fichecenter">';
			print '<div class="fichehalfleft">';
			print '<div class="underbanner clearboth"></div>';
			print '<table class="border tableforfield centpercent">';
				print "<tr>";
				print '<td class="titlefield">Matricule</td>';
				print '<td>'.$salarie->matricule.'</td>';
				print '</tr>';

				print '<tr>';
				print '<td>Nom</td>';
				print '<td>'.$userD->lastname.'</td>';
				print "</tr>";

				print "<tr>";
				print '<td>Prenom</td>';
				print '<td>'.$userD->firstname.'</td>';
				print "</tr>";

				print "<tr>";
				print '<td>Adresse</td>';
				print '<td>'.$userD->address.'</td>';

				print "</tr>";

				print "<tr>";
				$telephone_info = ($userD->office_phone?$userD->office_phone:"").''.($userD->office_fax?"==>".$userD->office_fax:"").''.($userD->user_mobile?"==>".$userD->user_mobile:"");
				print '<td>Tel/Fax'.info_admin($langs->trans($telephone_info), 1).'</td>';
				$telephone = $userD->office_phone;
				if(empty($telephone))
					$telephone = $userD->office_fax;
				if(empty($telephone))
					($userD->user_mobile?$userD->user_mobile."<br>":"");
				print '<td>'.$telephone.'</td>';
				print "</tr>";

				print "<tr>";
				print '<td>Email</td>';
				print '<td>'.$userD->email.'<br>'.$userD->personal_email.'</td>';
				print "</tr>";

				print "<tr>";
				print '<td>Date Embauche</td>';
				$date = "Ce salarié n'a pas de date d'embauche";
				if($salarie->date_anciennete)
					$date = $salarie->date_anciennete;
				elseif($userD->dateemployment && $salarie->rowid)
					$date = $userD->dateemployment;
				print '<td>'.$date.'</td>';
				print "</tr>";

				print '<tr>';
				$anciennete_tab = prime_anciennete($db, $salarie->rowid, $id_convention, date('m'), date('Y'), $fk_user);
				$anciennete = $anciennete_tab[0];

				print '<td>Ancienneté</td>';
				print '<td>'.$anciennete.'</td>';
				print '</tr>';

				print '<tr class="impair">';
				print '<td style="padding: 10px; width: 200px;">I.N.P.S</td>';
				print '<td style="padding: 10px; width: 200px;">'.$salarie->inps.'</td>';
				print '</tr>';

				print '<tr class="impair">';
				print '<td style="padding: 10px; width: 200px;">AMO</td>';
				print '<td style="padding: 10px; width: 200px;">'.$salarie->amo.'</td>';
				print '</tr>';

				print '</table>';
				print '</div>';
	//---------------------------------------------------
				print '<div class="fichehalfright">';
				print '<div class="underbanner clearboth"></div>';
				print '<table class="border tableforfield centpercent">';
				print "<tr>";

				print '<td>Catégorie</td>';
				print '<td>';
				if(!empty($salarie->fk_echelon)){
					$echelon_SQL = "SELECT libelle FROM ".MAIN_DB_PREFIX."echelon WHERE rowid=".$salarie->fk_echelon;
					$echelon_result = $db->query($echelon_SQL);
					$obj_echelon = $db->fetch_object($echelon_SQL);
					$categ .= "==>".$obj_echelon->libelle;
				}
				print $categ;
				print '</td>';
				print "</tr>";

				print "<tr>";
				print '<td>Genre</td>';
				$genre = "";
				if($userD->gender == "man")
				$genre = "homme";
				else if($userD->gender == "woman")
					$genre = "femme";
				else if($userD->gender == "other")
					$genre = "autre";
				else $genre = "";
				print '<td>'.$genre.'</td>';
				print "</tr>";

				print "<tr>";
				print '<td>Situation familiale</td>';
				if($salarie->situation_familiale == "")
					$situation_fam = "";
				elseif($salarie->situation_familiale == "marie")
					$situation_fam = "Marié(e)";
				elseif($salarie->situation_familiale == "divorce")
					$situation_fam = "Divorcé(e)";
				else $situation_fam = $salarie->situation_familiale;
				print '<td>'.$situation_fam.'</td>';
				print "</tr>";

				print "<tr>";
				print '</td><td>Nombre enfant(s)/dont handicapé(s)</td>';

				print '<td>'.$salarie->nombre_enfant.'/'.$salarie->nombre_enfant_hand.'</td>';
				print "</tr>";

				print "<tr>";
				print '</td><td>Fonction</td>';
				print '<td>'.$userD->job.'</td>';
				print "</tr>";

				/*print '<tr>';
				print '<td>Type de contrant</td>';
				$contratSalarie = "";
				$ContratSQL = "SELECT * FROM ".MAIN_DB_PREFIX."type_contrat where rowid=".$salarie->type_contrat;
					$contratResult = $db->query($ContratSQL);
					if($contratResult)
						$contratSalarie = $db->fetch_object($contratResult)?:"";

				print '<td>'.$contratSalarie->libelle.'</td>';
				print '</tr>';*/

				print '<tr>';
				print '<td>Poste '.info_admin($langs->trans("Ou type salarié"), 1).'</td>';
				$type = "";
				$type_sal_SQL = "SELECT * FROM ".MAIN_DB_PREFIX."type_salarie where rowid=".$salarie->type_salarie;

				$type_sal_Result = $db->query($type_sal_SQL);

				if($type_sal_Result){
					$type_Salarie = $db->fetch_object($type_sal_Result);
				$type = $type_Salarie->libelle;
				}
				print '<td>'.$type.'</td>';
				print '</tr>';
				print '<tr>';
				print '<td>Diplôme</td>';
				$diplomeSQL = "SELECT * FROM ".MAIN_DB_PREFIX."diplome where rowid=".$salarie->fk_diplome;
				$diplomeResult = $db->query($diplomeSQL);
				if($diplomeResult)
					$diplomeSalarie = $db->fetch_object($diplomeResult);

				print '<td>'.$diplomeSalarie->nom.'</td>';
				print '</tr>';
				print '<tr class="impair">';
				print '</td><td style="padding: 10px; width: 200px;">Moyen de paiement'.info_admin("Banques, Orange Money ou MoovMoney(MobiCash)",1).'</td>';
				print '<td style="padding: 10px; width: 200px;">';
					$banque = "SELECT libelle FROM ".MAIN_DB_PREFIX."type_banque WHERE rowid=".$salarie->fk_type_banque;
					$result_banque = $db->query($banque);
					if($result_banque){

						$obj_type_banque = $db->fetch_object($result_banque);
						print $obj_type_banque->libelle.'<br>'.$salarie->compte;

					}

				print '</td>';
				print '</tr>';

				print "<tr>";
				print '</td><td>Statut<br>';
				print 'Archiver</td>';
				//calcul salaire
				$cal_sal = "Inactif";
				if($salarie->calcul_salaire != 'non')
					$cal_sal = "Actif";
				//archiver
				$arch = "Non";
				if($salarie->archiver == 'oui')
					$arch = "Oui";
				print '<td>'.$cal_sal.'<br>'.$arch.'</td>';
				print "</tr>";
				print '</table>';
			print '</div>';
	//--------------------------------------------------------
			print '</div>';
			print '<div style="clear:both"></div>';
			print '<div class="tabsAction">'."\n";

			if($user->rights->paiementsalaire->salarie->write){
				print '<a class="butAction" title="Modifier les informations du salarié dans Salaire|Paie" href="'.$_SERVER["PHP_SELF"].'?id_societe='.$id_societe.'&id_convention='.$id_convention.'&fk_salarie='.$salarie->rowid.'&id='.$userD->rowid.'&action=edit">Modifier</a>';
				print '<a class="butAction" title="Modifier les informations utilisateurs dans user" href="../../user/card.php?id='.$userD->rowid.'">Utilisateur</a>';

			}else{
				print '<button class="butActionRefused" title="Vous n\'avez pas cette permission" >Modifier</button>';
				print '<button class="butActionRefused" title="Vous n\'avez pas cette permission" >Utilisateur</button>';

			}
			if($salarie->rowid)
					if($user->rights->paiementsalaire->salarie->write)
						print '<a class="butActionDelete" href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=salarie&id_societe='.$id_societe.'&id_convention='.$id_convention.'&id='.$userD->rowid.'&fk_salarie='.$salarie->rowid.'&action=supprimer">Supprimer</a>';
					else
						print '<button class="butActionRefused" title="Vous n\'avez pas cette permission" >Supprimer</button>';

			print '</div>';
			print '</div>';


	}

	if($action == 'edit'){
		
		/*$salSql = "SELECT * FROM ".MAIN_DB_PREFIX."user where rowid=".$fk_user;
		$result = $db->query($salSql);
		$obj = $db->fetch_object($result);*/

		$sql_comp_conv = "SELECT conv FROM ".MAIN_DB_PREFIX."societe_extrafields WHERE fk_object=".$id_societe;
		$result_comp_conv = $db->query($sql_comp_conv);
		$comp_conv = $db->fetch_object($result_comp_conv);

		$userSQL = "SELECT * FROM ".MAIN_DB_PREFIX."user where rowid=".$fk_user;
		$result = $db->query($userSQL);
		$userD = $db->fetch_object($result);

		//Recupération des trois(3) prémières lettres de la sociétés
		$salSql_soc = "SELECT * FROM ".MAIN_DB_PREFIX."societe where rowid=".$id_societe;
		$result_soc = $db->query($salSql_soc);
		$obj_soc = $db->fetch_object($result_soc);

		//Informations du salarié
		$salSql = "SELECT * FROM ".MAIN_DB_PREFIX."salarie where fk_user=".$fk_user;
		$result = $db->query($salSql);
		$salarie = $db->fetch_object($result);

		//Récupérations de l'identifiant suivant
		$salSql_propose = "SELECT rowid FROM ".MAIN_DB_PREFIX."salarie ORDER BY rowid DESC";
		$result_propose = $db->query($salSql_propose);
		$salarie_rowid_suiv = $db->fetch_object($result_propose);

		$mat_propose = ($obj_soc->nom[0]?:"A")."".($obj_soc->nom[1]?:"B")."".($obj_soc->nom[2]?:"C")."-";
		$i = 0;
		for ($i=0; $i < (4-strlen("".$salarie_rowid_suiv->rowid)); $i++) {
			$mat_propose .= "0";
		}
		$mat_propose .= $salarie_rowid_suiv->rowid+1;

		print '<div><form name="add" method="POST" action="'.$_SERVER['PHP_SELF'].'?mainmenu=paiementsalaire&leftmenu=salarie&id_societe='.$id_societe.'&id='.$fk_user.'&id_convention='.$id_convention.'">';
		print '<input type="hidden" name="token" value="'.newToken().'">';
		print '<input type="hidden" name="action" value="save_edit">';

		print '<table class="tagtable liste">';
		print '<tr class="pair">';
		print '<td class="fieldrequired" style="padding: 10px; width: 200px;">Matricule'.info_admin($langs->trans("Ce Champ est obligatoire"), 1).'</td>';
		print '<td style="padding: 10px; width: 200px;"><input type="text" name="matricule" value="'.(GETPOST("matricule", "alpha")?:$salarie->matricule?:$mat_propose).'"></td>';

		print '</td><td class="fieldrequired" style="padding: 10px; width: 200px;">Catégorie</td>';
		print '<td style="padding: 10px; width: 200px;"><select name="categories">';
		print '<option value="0"></option>';
		if(!empty(GETPOST("categories"))){
			$tab_cat_ech = explode("/",GETPOST("categories"));

			$categ = $tab_cat_ech[0];
			if(count($tab_cat_ech)>1)
				$echel = $tab_cat_ech[1];
			else $echel = 0;

			$catSql = "SELECT * FROM ".MAIN_DB_PREFIX."dcategories WHERE fk_convention=".$comp_conv->conv;
			$result = $db->query($catSql);

			$aff = true;
			if($result){
				$i = 0;
				$num = $db->num_rows($result);
				while ($i < $num){
					$obj1 = $db->fetch_object($result);

					if(empty($echel))
						$echelon_SQL = "SELECT * FROM ".MAIN_DB_PREFIX."echelon WHERE fk_categorie=".$obj1->rowid;
					else
						$echelon_SQL = "SELECT * FROM ".MAIN_DB_PREFIX."echelon WHERE rowid=".$salarie->fk_echelon;

					$echelon_result = $db->query($echelon_SQL);
					$num_echelon = $db->num_rows($echelon_result);
					if($num_echelon > 0){
						$a = 0;
						while ($a < $num_echelon) {
							$obj_echelon = $db->fetch_object($echelon_result);

							if($echel == $obj_echelon->rowid)
								print '<option value="'.$obj1->rowid.'/'.$obj_echelon->rowid.'" selected>'.$obj1->code_categorie.' ==> '.$obj_echelon->libelle.'</option>';
							else
								print '<option value="'.$obj1->rowid.'/'.$obj_echelon->rowid.'">'.$obj1->code_categorie.' ==> '.$obj_echelon->libelle.'</option>';
							$a++;
						}
					}else
						if($categ == $obj1->rowid)
							print '<option value="'.$obj1->rowid.'" selected>'.$obj1->code_categorie.'</option>';

						else
							print '<option value="'.$obj1->rowid.'">'.$obj1->code_categorie.'</option>';
					$i ++;
				}
				print '</select></td>';

			}
		}else{

			$catSql = "SELECT * FROM ".MAIN_DB_PREFIX."dcategories WHERE fk_convention=".$comp_conv->conv;
			$result = $db->query($catSql);

			$aff = true;
			if($result){
				$i = 0;
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

							if($salarie->fk_categorie == $obj1->rowid && $salarie->fk_echelon == $obj_echelon->rowid)
								print '<option value="'.$obj1->rowid.'/'.$obj_echelon->rowid.'" selected>'.$obj1->code_categorie.' ==> '.$obj_echelon->libelle.'</option>';
							else
								print '<option value="'.$obj1->rowid.'/'.$obj_echelon->rowid.'">'.$obj1->code_categorie.' ==> '.$obj_echelon->libelle.'</option>';
							$a++;
						}
					}else
						if($salarie->fk_categorie == $obj1->rowid)
							print '<option value="'.$obj1->rowid.'" selected>'.$obj1->code_categorie.'</option>';

						else
							print '<option value="'.$obj1->rowid.'">'.$obj1->code_categorie.'</option>';
					$i ++;
				}
				print '</select></td>';
			}
		}

		print '</tr>';
		print '<tr class="impair">';
		print '<td class="fieldrequired" style="padding: 10px; width: 200px;">Situation Familiale</td>';
		$marie = "";
		$divorce = "";
		$celibat = "";
		if(!empty(GETPOST("statut_f", "alpha"))){
			if(GETPOST("statut_f", "alpha") == "marie")
				$marie = "selected";
			else if(GETPOST("statut_f", "alpha") == "divorce")
				$divorce = "selected";
			else if(GETPOST("statut_f", "alpha") == "celibataire")
				$celibat = "selected";
			else $autre = "selected";
		}elseif($salarie->situation_familiale == "marie")
			$marie = "selected";
		else if($salarie->situation_familiale == "divorce")
			$divorce = "selected";
		else if($salarie->situation_familiale == "celibataire")
			$celibat = "selected";
		else $autre = "selected";
		print '<td style="padding: 10px; width: 200px;"><select id="statut_f" name="statut_f">

		<option value="" '.$autre.'></option>
			<option value="marie"'.$marie.'>Marié(e)</option>
			<option value="divorce" '.$divorce.'>Divorcé(e)</option>
			<option value="celibataire" '.$celibat.'>Célibataire</option>
		</select></td>';



		print '<td style="padding: 10px; width: 200px;">Type de salarié</td>';
		print '<td style="padding: 10px; width: 200px;">';
		print '<select name="type_salarie">';
		if(!empty(GETPOST("type_salarie", "int"))){
			$id_type_sal = GETPOST("type_salarie", "int");
			$type_salarie = "SELECT * FROM ".MAIN_DB_PREFIX."type_salarie";
			$result_type_s = $db->query($type_salarie);
			if($result_type_s){
				$i = 0;
				$num = $db->num_rows($result_type_s);
				while ($i < $num){
					$obj_type_s = $db->fetch_object($result_type_s);
					if($id_type_sal == $obj_type_s->rowid){
						if($obj_type_s->rowid == 1)
							print '<option value="'.$obj_type_s->rowid.'" selected></option>';
						else
							print '<option value="'.$obj_type_s->rowid.'" selected>'.$obj_type_s->libelle.'</option>';
					}else{
						if($obj_type_s->rowid == 1)
							print '<option value="'.$obj_type_s->rowid.'" selected></option>';
						else
							print '<option value="'.$obj_type_s->rowid.'">'.$obj_type_s->libelle.'</option>';
					}
					$i++;
				}

			}
		}else{
			$type_salarie = "SELECT * FROM ".MAIN_DB_PREFIX."type_salarie";
			$result_type_s = $db->query($type_salarie);
			if($result_type_s){
				$i = 0;
				$num = $db->num_rows($result_type_s);
				while ($i < $num){
					$obj_type_s = $db->fetch_object($result_type_s);
					if($salarie->type_salarie == $obj_type_s->rowid)
						print '<option value="'.$obj_type_s->rowid.'" selected>'.$obj_type_s->libelle.'</option>';
					else
						print '<option value="'.$obj_type_s->rowid.'">'.$obj_type_s->libelle.'</option>';
					$i++;
				}

			}
		}



		print '</select>';
		print '</td></tr>';
		print '<tr class="impair">';

		print '<td class="fieldrequired" style="padding: 10px; width: 200px;">Nombre enfant à charge</td>';
		print '<td style="padding: 10px; width: 200px;"><input id="nombre_enfant" name="nb_enfant" type="number" min="0" max="10" size="5" value="'.(GETPOST("nb_enfant", "int")?:$salarie->nombre_enfant).'">
		</td>';
		print '<td style="padding: 10px; width: 200px;">Diplôme</td>';
		print '<td style="padding: 10px; width: 200px;">';
		print '<select name="diplome">';
		print '<option value=""></option>';
		if(!empty(GETPOST("diplome", "int"))){
			$id_dip = GETPOST("diplome", "int");
			$diplome = "SELECT * FROM ".MAIN_DB_PREFIX."diplome";
			$result_diplome = $db->query($diplome);
			if($result_diplome){
				$i = 0;
				$num = $db->num_rows($result_diplome);
				while ($i < $num){
					$obj_diplome = $db->fetch_object($result_diplome);
					if($id_dip == $obj_diplome->rowid)
						print '<option value="'.$obj_diplome->rowid.'" selected>'.$obj_diplome->nom.'</option>';
					else
						print '<option value="'.$obj_diplome->rowid.'">'.$obj_diplome->nom.'</option>';
					$i++;
				}

			}
		}else{
			$diplome = "SELECT * FROM ".MAIN_DB_PREFIX."diplome";
			$result_diplome = $db->query($diplome);
			if($result_diplome){
				$i = 0;
				$num = $db->num_rows($result_diplome);
				while ($i < $num){
					$obj_diplome = $db->fetch_object($result_diplome);
					if($salarie->fk_diplome == $obj_diplome->rowid)
						print '<option value="'.$obj_diplome->rowid.'" selected>'.$obj_diplome->nom.'</option>';
					else
						print '<option value="'.$obj_diplome->rowid.'">'.$obj_diplome->nom.'</option>';
					$i++;
				}

			}
		}
		print '</select>';
		print '</td>';
		print '</tr>';


		print '<tr class="pair">';


		print '<td class="fieldrequired" style="padding: 10px; width: 200px;">Nombre enfant Handicap</td>';
		print '<td style="padding: 10px; width: 200px;"><input id="nombre_enfant_hand" name="nombre_enfant_hand" type="number" min="0" max="10" size="5" value="'.(GETPOST("nombre_enfant_hand", "int")?:$salarie->nombre_enfant_hand).'" ></td>';

		print '<td style="padding: 10px; width: 200px;">I.N.P.S<br>AMO</td></td>';
		print '<td style="padding: 10px; width: 200px;"><input id="inps" name="inps" placeholder="numéro I.N.P.S" type="tel" value="'.(GETPOST("inps", "int")?:$salarie->inps).'" >';
		print "<input type='text' name='amo' placeholder='numéro AMO' value='".(GETPOST("amo", "int")?:$salarie->amo)."'>";
		print '</td>';

		print '</tr>';

		print '<tr class="impair">';
		print '</td><td class="fieldrequired" style="padding: 10px; width: 250px;">Statut<br><br>Archiver</td>';
		print '<td style="padding: 10px; width: 200px;"><select name="calcul_salaire" >';
		if($salarie->calcul_salaire != 'non'){
			print '<option value="oui" selected>Actif</option>';
			print '<option value="non" >Inactif</option>';
		}else{
			print '<option value="oui" >Actif</option>';
			print '<option value="non" selected>Inactif</option>';
		}
		print '</select><br>';

		print '<select name="archiver" >';
		if($salarie->archiver == 'oui'){
			print '<option value="oui" selected>Oui</option>';
			print '<option value="non" >Non</option>';
		}else{
			print '<option value="oui" >Oui</option>';
			print '<option value="non" selected>Non</option>';
		}
		print '</select>';
		print '</td>';

		print '<td style="padding: 10px; width: 200px;">Moyen de paiement'.info_admin("Banques, Orange Money ou MoovMoney(MobiCash)",1).'</td>';
		print '<td style="padding: 10px; width: 200px;">';

		print '<select name="fk_type_banque">';
		if(!empty(GETPOST("banque", "int"))){
			$id_dip = GETPOST("banque", "int");
			$banque = "SELECT * FROM ".MAIN_DB_PREFIX."type_banque";
			$result_banque = $db->query($banque);
			if($result_banque){
				$i = 0;
				$num = $db->num_rows($result_banque);
				while ($i < $num){
					$obj_type_banque = $db->fetch_object($result_banque);
					if($id_dip == $obj_type_banque->rowid)
						print '<option value="'.$obj_type_banque->rowid.'" selected>'.$obj_type_banque->libelle.'</option>';
					else
						print '<option value="'.$obj_type_banque->rowid.'">'.$obj_type_banque->libelle.'</option>';
					$i++;
				}

			}
		}else{
			$banque = "SELECT * FROM ".MAIN_DB_PREFIX."type_banque";
			$result_banque = $db->query($banque);
			if($result_banque){
				$i = 0;
				$num = $db->num_rows($result_banque);
				while ($i < $num){
					$obj_type_banque = $db->fetch_object($result_banque);
					if($salarie->fk_type_banque == $obj_type_banque->rowid)
						print '<option value="'.$obj_type_banque->rowid.'" selected>'.$obj_type_banque->libelle.'</option>';
					else
						print '<option value="'.$obj_type_banque->rowid.'">'.$obj_type_banque->libelle.'</option>';
					$i++;
				}

			}
		}
		print '</select>';
		print '<input id="compte" name="compte" type="tel" placeholder="numéro compte" value="'.(GETPOST("compte", "alpha")?:$salarie->compte).'" >';
		print '</td>';
		print '</tr>';


		print '<tr ><td align="center"  colspan="5"><input  style="margin-top:50px;" type="submit" value="Enregistrer"  class="button">';
		print '</form><a href="'.$_SERVER['PHP_SELF'].'?mainmenu=paiementsalaire&leftmenu=salarie&id_societe='.$id_societe.'&id='.$fk_user.'&id_convention='.$id_convention.'&action=detail"><input type="button" value="Annuler"  class="button"></a><td>';

		print '</table>';


		print '</div>';
		}
	$db->free($result);

}
	//if(!empty($message))
	print "<script>
		const nombre_enfant_hand = document.getElementById('nombre_enfant_hand');
		const nombre_enfant = document.getElementById('nombre_enfant');
		const nb_conj = document.getElementById('nb_conj');
		const statut_f = document.getElementById('statut_f');

		if(statut_f.value != 'marie'){
			nb_conj.value = 0;
			nb_conj.style.display = 'none';
		}

		nombre_enfant_hand.addEventListener('change',typeApplique2);
		function typeApplique2(){
			if(parseInt(nombre_enfant_hand.value) > parseInt(nombre_enfant.value)){
				nombre_enfant_hand.value = parseInt(nombre_enfant.value);
			}
		}

		statut_f.addEventListener('change',marie);
		function marie(){
			if(statut_f.value == 'marie'){
				nb_conj.value = 1;
				nb_conj.setAttribute('min', 1);
				nb_conj.style.display = 'block';
			}else{
				nb_conj.value = 0;
				nb_conj.style.display = 'none';
			}
		}

		</script>";

		if(!empty($message)){
			$action = 'create';

				print "<script>
				$.jnotify('".$message."', {delay : 5000, fadeSpeed: 500});
				</script>";
		}
