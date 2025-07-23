<?php
require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/custom/paiementsalaire/lib/paiementsalaire.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';

llxHeader("", "Paiement | Salaire");
//Titre
print load_fiche_titre($langs->trans("Heures supplémentaire"), '', '');
//print '<hr>';
	/*$salSql = "SELECT * FROM ".MAIN_DB_PREFIX."salarie where fk_salarie=".$fk_salarie;
		$result = $db->query($salSql);
		if(!empty($result)){
			$salarie = $db->fetch_object($result);*/
	$fk_user = GETPOST("id","int");
	$id_societe = GETPOST("id_societe","int");
	$fk_salarie = GETPOST("fk_salarie", "int");
	$id_convention = GETPOST("id_convention","int");
	$head = salaire_Head($fk_salarie, $fk_user, $id_societe, $id_convention);
	print dol_get_fiche_head($head, 'hsup', "", -1, '');

	$mois_tab = array(" Janvier "," Février "," Mars "," Avril "," Mai "," Juin "," Juillet "," Août "," Septembre "," Octobre "," Novembre "," Décembre ");

if($user->id !=1 && $user->id != $fk_user && !$user->rights->paiementsalaire->salarie->read){
	print "<h2> Vous n\'avez pas ce droit </h2>";
}else{

	$monform = new Form($db);

	$action = GETPOST("action", "alpha");

	$message = "";
	if($action == "save_heure_sup"){
		$type_heure_sup = GETPOST("type_heure_sup", 'int');
		$nb_heure_sup = GETPOST("nombre_heure_sup","int");
		$date = GETPOST("date");
		$note = GETPOST("note", "alpha");
		if(empty($type_heure_sup))
			$message = "Le Champ 'NOMBRE' est obligatoire<br>";

		if(empty($date))
			$date = date("Y-m-d");

		if(empty($message)){
			$mois_annee = explode("-", $date);
			$sql = "INSERT INTO ".MAIN_DB_PREFIX."salarie_heure_sup (fk_salarie, fk_heur_sup, nb_heure, jour, mois, annee, note)
			VALUES ('".$fk_salarie."',".$type_heure_sup.",".$nb_heure_sup.",'".$mois_annee[2]."','".$mois_annee[1]."','".$mois_annee[0]."','".$note."')";
			$result = $db->query($sql);
			print $db->error();
			if($result){
				$message = "Heure Sup associée à ce salarié";
				//Ajout dans la table log
				$heure_sup = "SELECT commentaire, taux FROM ".MAIN_DB_PREFIX."heure_sup WHERE rowid=".$type_heure_sup;
				$result_heure_sup = $db->query($heure_sup);
				$obj_hs = $db->fetch_object($result_heure_sup);

				$sql_select = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$user->id;
				$obj = $db->fetch_object($db->query($sql_select));

				$sql_select = "SELECT firstname, lastname, dateemployment FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$fk_user;
				$obj_u = $db->fetch_object($db->query($sql_select));

				$sql_select = "SELECT nom FROM ".MAIN_DB_PREFIX."societe WHERE rowid=".$id_societe;
				$obj_s = $db->fetch_object($db->query($sql_select));

				$action_effectue = "Ajout d'heure sup (desc=".$obj_hs->commentaire." taux=".$obj_hs->taux." nb_hs=".$nb_heure_sup." ".$mois_annee[2]."/".$mois_annee[1]."/".$mois_annee[0].") pour le salarié (N : ".$obj_u->firstname.", P : ".$obj_u->lastname.") de la société ".$obj_s->nom;
				$sql_log = 'INSERT INTO '.MAIN_DB_PREFIX.'log (fk_user, nom, prenom, quand, action_effectue, object_concerne)';
				$sql_log .= ' VALUES('.$user->id.', "'.$obj->lastname.'","'.$obj->firstname.'",now(),"'.$action_effectue.'","Ajout heure sup")';
				$db->query($sql_log);

			}else{
				$message = "Un problème est survenu";
				$action = "ajout_heure_sup";
			}
		}else $action = "ajout_heure_sup";

	}

	if($action == "supprimer_attention"){
		$id_heure = GETPOST("id_heure", "int");
			$mois = GETPOST("mois", "09");
			$annee = GETPOST("annee", "09");
			$url = $_SERVER['PHP_SELF']."?mainmenu=paiementsalaire&leftmenu=salarie&id_convention=".$id_convention."&fk_salarie=".$fk_salarie."&id_societe=".$id_societe."&id=".$fk_user."&id_convention=".$id_convention."&id_heure=".$id_heure;
			$formconfirm = $monform->formconfirm(
				$url,
				'Veuillez confirmer la suppression',
				$text,
				'supprimer',
				'',
				'',
				1,
				250,
				'25%'
			);
			print $formconfirm;
			//$action = 'annee_rechercher';
	
	}

	if($action == "supprimer"){
		$id_heure = GETPOST("id_heure", "int");

		$heure_sup = "SELECT fk_heur_sup, nb_heure, jour, mois, annee FROM ".MAIN_DB_PREFIX."salarie_heure_sup WHERE rowid=".$id_heure;
		$result_heure_sup = $db->query($heure_sup);
		$obj_sal_hs = $db->fetch_object($result_heure_sup);

		$heure_sup = "SELECT commentaire, taux FROM ".MAIN_DB_PREFIX."heure_sup WHERE rowid=".$obj_sal_hs->fk_heur_sup;
		$result_heure_sup = $db->query($heure_sup);
		$obj_hs = $db->fetch_object($result_heure_sup);

		$sql = "DELETE FROM ".MAIN_DB_PREFIX."salarie_heure_sup WHERE rowid=".$id_heure;
		$result2 = $db->query($sql);
		if($result){
			$message = "heure Sup dissociée avec succès";

			//Ajout dans la table log
			$sql_select = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$user->id;
			$obj = $db->fetch_object($db->query($sql_select));

			$sql_select = "SELECT firstname, lastname, dateemployment FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$fk_user;
			$obj_u = $db->fetch_object($db->query($sql_select));

			$sql_select = "SELECT nom FROM ".MAIN_DB_PREFIX."societe WHERE rowid=".$id_societe;
			$obj_s = $db->fetch_object($db->query($sql_select));

			$action_effectue = "Suppression d'heure sup (desc=".$obj_hs->commentaire." taux=".$obj_hs->taux." nb_hs=".$obj_sal_hs->nb_heure." ".$obj_sal_hs->jour."/".$obj_sal_hs->mois."/".$obj_sal_hs->annee.") pour le salarié (N : ".$obj_u->firstname.", P : ".$obj_u->lastname.") de la société ".$obj_s->nom;
			$sql_log = 'INSERT INTO '.MAIN_DB_PREFIX.'log (fk_user, nom, prenom, quand, action_effectue, object_concerne)';
			$sql_log .= ' VALUES('.$user->id.', "'.$obj->lastname.'","'.$obj->firstname.'",now(),"'.$action_effectue.'","Suppression heure sup")';
			$db->query($sql_log);
		}
	}

	if(empty($fk_salarie)){
		print "Page non Disponible";
	}else{
		$obj_soc = prepare_objet_entete($fk_salarie, $fk_user, $db, $id_societe, $id_convention);
		entete_societe($obj_soc, 'societe');

		if($action == 'config'){
			$head2 = salarie_heure_sup_head($fk_salarie, $fk_user, $id_societe, $id_convention);
			print dol_get_fiche_head($head2, 'hs_config', "", -1, '');

		}else{
			$head2 = salarie_heure_sup_head($fk_salarie, $fk_user, $id_societe, $id_convention);
			print dol_get_fiche_head($head2, 'hs_salarie', "", -1, '');
			if($action == "ajout_heure_sup"){
				print "<h3>Ajout Heure Sup</h3>";
					print ' <form name="ajouter" method="POST" action="'.$_SERVER['PHP_SELF'].'?mainmenu=paiementsalaire&leftmenu=salarie&id='.$fk_user.'&fk_salarie='.$fk_salarie.'&id_convention='.$id_convention.'&id_societe='.$id_societe.'">';
					print '<input type="hidden" name="token" value="'.newToken().'">';
					print '<input type="hidden" name="action" value="save_heure_sup">';
					print "<table>";
					print "<tr class='fieldrequired'><td>Nombre d'heure sup</td><td><input type='number' name='nombre_heure_sup' step='0.01' min='0.1' id='nombre_heure_sup' value=".(GETPOST("nombre_heure_sup", "09") ? :0)." autofocus></td></tr>";
					print "<tr class='fieldrequired'><td style='padding: 5px; width: 100px;'>Type</td><td style='padding: 5px; width: 100px;'><select name='type_heure_sup' id='type_heure_sup'>";
					$heure_sup_sql = "SELECT * FROM ".MAIN_DB_PREFIX."heure_sup WHERE fk_convention=".$id_convention;
					$result_heure_sup = $db->query($heure_sup_sql);//= $db->query($covSql);
					$taux = 0;
					if($result_heure_sup){
						$i = 0;
						$num = $db->num_rows($result_heure_sup);
						while ($i < $num){
							$obj_heure_sup = $db->fetch_object($result_heure_sup);
							if (!empty(GETPOST("id_type_heure_sup", "int"))){
								if (GETPOST("id_type_heure_sup", "int") == $obj_heure_sup->rowid){
									print '<option value="'.$obj_heure_sup->rowid.'" selected>'.$obj_heure_sup->commentaire.'</option>';
									$taux = $obj_heure_sup->taux;
								}else
									print '<option value="'.$obj_heure_sup->rowid.'">'.$obj_heure_sup->commentaire.'</option>';
							}else
								if($i == 0){
									$taux = $obj_heure_sup->taux;
									print '<option value="'.$obj_heure_sup->rowid.'">'.$obj_heure_sup->commentaire.'</option>';
								}else print '<option value="'.$obj_heure_sup->rowid.'">'.$obj_heure_sup->commentaire.'</option>';

							$i ++;
							}

						}

					print "</select></td></tr>";

					print "<tr class='fieldrequired'><td style='padding: 5px; width: 200px;'>Taux</td><td style='padding: 5px; width: 200px;'><input type='text' value=".$taux." disabled name='taux' id='taux'></td></tr>";
					print "<tr ><td style='padding: 5px; width: 200px;'>Date</td><td style='padding: 5px; width: 200px;'><input type='date' name='date' id='date' value=".!empty(GETPOST("date"))." ></td></tr>";
					print "<tr><td style='padding: 5px; width: 200px;'>Note</td><td style='padding: 5px; width: 200px;'><input type='text' name='note' placeholder='Note sur l heure sup' size='80' id='note' value='".GETPOST("note")."' ></td></tr>";
					//print "<td><input value='Ajouter' class='button' type='submit'></td></tr>";
					print '<tr>';
					print '<td style=" padding-right: 30px; padding-bottom: 30px"></td><td style=" padding-bottom: 30px"><input class="button" type="submit" value="Ajouter" name="">';
					print'</form>';
					print '<a href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=salarie&id='.$fk_user.'&fk_salarie='.$fk_salarie.'&id_convention='.$id_convention.'&id_societe='.$id_societe.'" class="button">Annuler</a></td></tr>';
					print '<table>';

					print '<script type="text/javascript">
					var nombre_heure_sup = document.getElementById("nombre_heure_sup");
						var date = document.getElementById("date");
						var note = document.getElementById("note");
						var type_heure_sup = document.getElementById("type_heure_sup");


							type_heure_sup.addEventListener("change", function () {
								var id_type_heure_sup = type_heure_sup.value;
								var val_nombre_heure_sup = nombre_heure_sup.value;
								var val_date = date.value;
								var val_note = note.value;
								window.location.href = "'.$_SERVER["PHP_SELF"].'?id='.$fk_user.'&id_societe='.$id_societe.'&fk_salarie='.$fk_salarie.'&id_convention='.$id_convention.'&nombre_heure_sup="+val_nombre_heure_sup+"&date="+val_date+"&note="+val_note+"&id_type_heure_sup="+id_type_heure_sup+"&action=ajout_heure_sup";
							},
							false,
							);
						</script>';

			}else{
				//A la recherche du mois et annee Actuelle
				$sql_verif = "SELECT mois, annee FROM ".MAIN_DB_PREFIX."bulletin WHERE cloture='non' AND fk_societe=".$id_societe;
				$res_verif = $db->query($sql_verif);
				if($res_verif){
					if($db->num_rows($res_verif)>0){
						$obj_verif_hs = $db->fetch_object($res_verif);
						$date = $obj_verif_hs->annee."-".$obj_verif_hs->mois."-01";
						$mois_annee = explode("-", $date);
					}else{
						$date = date("Y-m-d");
						$mois_annee = explode("-", $date);
					}
				}else{
					$date = date("Y-m-d");
					$mois_annee = explode("-", $date);
				}
				//var_dump($mois_annee);

				print_barre_liste("", $page, $_SERVER["PHP_SELF"], "", "", "", "", "", "", 'bill', 0, dolGetButtonTitle("Ajouter une nouvelle avance/accompte", '', 'fa fa-plus-circle', $_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=salarie&id='.$fk_user.'&fk_salarie='.$fk_salarie.'&id_convention='.$id_convention.'&id_societe='.$id_societe.'&action=ajout_heure_sup' , '', 1), '', 0, 0, 0, 1);
				print "<h3>Heures Non Payées <mark>".$mois_tab[($mois_annee[1]-1)]." ".$mois_annee[0]."</mark></h3>";
			//les heures sup associé ce salarié
				print "<table class='tagtable liste'>";
				print "<tr class='liste_titre'><td>Date création</td><td>Nombre Heure Sup</td><td>Type";
				print "</td><td>Taux %</td><td>Note</td><td>Suppression</td></tr>";
				
				$sql = "SELECT * FROM ".MAIN_DB_PREFIX."salarie_heure_sup WHERE fk_salarie='".$fk_salarie."' AND annee='".$mois_annee[0]."' AND mois=".$mois_annee[1];
				$sql .= " ORDER BY annee DESC, mois DESC";

				//print $sql;
				$result = $db->query($sql);
				$m = "(0";
				if($result){
					$i = 0;
					$num = $db->num_rows($result);
					while ($i < $num){
						$obj = $db->fetch_object($result);
						if ($obj)
						{
							$trouve = false;
							$type_heure_sup = "SELECT * FROM ".MAIN_DB_PREFIX."heure_sup WHERE rowid=".$obj->fk_heur_sup;
							$result_type_heure_sup = $db->query($type_heure_sup);
							$obj_type_heure_sup = $db->fetch_object($result_type_heure_sup);							

							$sql_verif_hs = "SELECT fk_heur_sup, fk_bulletin FROM ".MAIN_DB_PREFIX."bulletin_heure_sup WHERE fk_heur_sup=".$obj_type_heure_sup->rowid;
							$res_verif_hs = $db->query($sql_verif_hs);
							if($res_verif_hs)
								if($db->num_rows($res_verif_hs)>0){
									$obj_verif_hs = $db->fetch_object($res_verif_hs);
									$sql_verif = "SELECT rowid FROM ".MAIN_DB_PREFIX."bulletin WHERE cloture='non' AND rowid=".$obj_verif_hs->fk_bulletin;
									$res_verif = $db->query($sql_verif);
									if($res_verif)
										if($db->num_rows($res_verif)>0){
											$m .= ", ".$obj->fk_heur_sup;
											print "<tr class='impair'>";
											print "<td>".$obj->date_creation." ".info_admin($mois_tab[(int)$obj->mois -1]." ".$obj->annee, 1)."</td>";
											print '<td>'.$obj->nb_heure.'</td><td>'.$obj_type_heure_sup->commentaire.'</td>';
											print '<td>'.$obj_type_heure_sup->taux.'</td>';
											print "<td>".$obj->note."</td>";
											print "<td><a class='reposition editfielda button' href='".$_SERVER['PHP_SELF']."?mainmenu=paiementsalaire&leftmenu=salarie&id_convention=".$id_convention."&fk_salarie=".$fk_salarie."&id_societe=".$id_societe."&id=".$fk_user."&id_convention=".$id_convention."&action=supprimer_attention&id_heure=".$obj->rowid."'>Supprimer</a></td></tr>";

										}else{
											print "<tr class='impair'>";
											print "<td>".$obj->date_creation." ".info_admin($mois_tab[(int)$obj->mois -1]." ".$obj->annee, 1)."</td>";
											print '<td>'.$obj->nb_heure.'</td><td>'.$obj_type_heure_sup->commentaire.'</td>';
											print '<td>'.$obj_type_heure_sup->taux.'</td>';
											print "<td>".$obj->note."</td>";
											print "<td><a class='reposition editfielda button' href='".$_SERVER['PHP_SELF']."?mainmenu=paiementsalaire&leftmenu=salarie&id_convention=".$id_convention."&fk_salarie=".$fk_salarie."&id_societe=".$id_societe."&id=".$fk_user."&id_convention=".$id_convention."&action=supprimer_attention&id_heure=".$obj->rowid."'>Supprimer</a></td></tr>";
											$m .= ", ".$obj->fk_heur_sup;
										}
										
								}else{
									print "<tr class='impair'>";
									print "<td>".$obj->date_creation." ".info_admin($mois_tab[(int)$obj->mois -1]." ".$obj->annee, 1)."</td>";
									print '<td>'.$obj->nb_heure.'</td><td>'.$obj_type_heure_sup->commentaire.'</td>';
									print '<td>'.$obj_type_heure_sup->taux.'</td>';
									print "<td>".$obj->note."</td>";
									print "<td><a class='reposition editfielda button' href='".$_SERVER['PHP_SELF']."?mainmenu=paiementsalaire&leftmenu=salarie&id_convention=".$id_convention."&fk_salarie=".$fk_salarie."&id_societe=".$id_societe."&id=".$fk_user."&id_convention=".$id_convention."&action=supprimer_attention&id_heure=".$obj->rowid."'>Supprimer</a></td></tr>";
									$m .= ", ".$obj->fk_heur_sup;

								}

						}
						$i ++;
					}
					$m .= ")";
					if($num == 0)
						print "<tr><td align='center' colspan='6'>Pas d'heure sup pour ce salarié</td></tr>";

				}else{
					print "<tr><td align='center' colspan='6'>Pas d'heure sup pour ce salarié</td></tr>";
				}
				print "</table>";
			print "<h3>Heures sup Payées de ".date('Y')."</h3>";
			print "<table class='tagtable liste'>";
			//les heures sup associé ce salarié

				print "<tr class='liste_titre'><td>Type</td><td>Taux %</td><td>Nbre Heure --> Montant";
				print "</td><td>Mois</td><td>Note</td><td>Opération</td></tr>";
				$date = date("y-m-d");
				$mois_annee = explode("-", $date);
				//$sql = "SELECT * FROM ".MAIN_DB_PREFIX."salarie_heure_sup WHERE fk_salarie='".$fk_salarie."' AND (annee <>".date('Y')." OR (annee =".date('Y')." AND mois<>".$mois_annee[1]."))";
				$sql = "SELECT rowid, mois, annee FROM ".MAIN_DB_PREFIX."bulletin WHERE annee=".date('Y')." AND fk_salarie=".$fk_salarie." AND cloture='oui'";
				$sql .= " ORDER BY annee DESC, mois DESC";
				$result = $db->query($sql);
				if($result){
					$trouve = false;
					$i = 0;
					$num = $db->num_rows($result);

					while ($i < $num){
						$obj = $db->fetch_object($result);
						if ($obj)
						{
							$sql_bull_hs = "SELECT fk_heur_sup, libelle, base, taux, nombre_heure_sup, montant FROM ".MAIN_DB_PREFIX."bulletin_heure_sup WHERE fk_bulletin=".$obj->rowid;
							$result_bull_hs = $db->query($sql_bull_hs);
							$num_bull_hs = $db->num_rows($result_bull_hs);
							if($result_bull_hs && $num_bull_hs > 0){
								$trouve = true;
								$obj_bull_hs = $db->fetch_object($result_bull_hs);

								print "<tr class='impair'><td>";
								print ''.$obj_bull_hs->libelle.'</td><td>'.$obj_bull_hs->taux.'</td>';
								print '<td>'.$obj_bull_hs->nombre_heure_sup.'H --> '.$obj_bull_hs->montant.'</td>';

								$sql_hs = "SELECT note FROM ".MAIN_DB_PREFIX."salarie_heure_sup WHERE fk_heur_sup=".$obj_bull_hs->fk_heur_sup." AND annee=".$obj->annee." AND mois=".$obj->mois;
								$result_hs = $db->query($sql_hs);
								if($result_hs)
									$obj_hs = $db->fetch_object($result_hs);

								print "<td>".$mois_tab[(int)$obj->mois-1]." ".$obj->annee."</td>";
								print "<td>".$obj->note."</td>";
								print "<td><button class='button' disabled>Dissocier</button></td></tr>";
							}
						}
						$i ++;
					}
					if($num == 0 || $trouve == false)
						print "<tr><td align='center' colspan='6'>Pas d'heure sup payée pour ce salarié</td></tr>";

				}else{
					print "<tr><td align='center' colspan='6'>Pas d'heure sup payée pour ce salarié</td></tr>";
				}
				print "</table>";
			}
		}
	}
	$db->free();


	if($message != ""){
		print "<script>
		$.jnotify('".$message."', {delay : 5000, fadeSpeed: 500});
		</script>";
	}

}

