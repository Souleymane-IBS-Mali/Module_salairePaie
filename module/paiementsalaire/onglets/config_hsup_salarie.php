<?php
require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/custom/paiementsalaire/lib/paiementsalaire.lib.php';

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
	$fk_salarie = GETPOST("fk_salarie");
	$id_convention = GETPOST("id_convention","int");
	$head = salaire_Head($fk_salarie, $fk_user, $id_societe, $id_convention);
	print dol_get_fiche_head($head, 'hsup', "", -1, '');

if($user->id !=1 && $user->id != $fk_user && !$user->rights->paiementsalaire->salarie->read){
	print "<h2> Vous n\'avez pas ce droit </h2>";
}else{

	$action = GETPOST("action", "alpha");

	$message = "";

	if(empty($fk_salarie)){
		print "Page non Disponible";
	}else{
		$obj_soc = prepare_objet_entete($fk_salarie, $fk_user, $db, $id_societe, $id_convention);
		entete_societe($obj_soc, 'societe');

		$head2 = salarie_heure_sup_head($fk_salarie, $fk_user, $id_societe, $id_convention);
		print dol_get_fiche_head($head2, 'hs_config', "", -1, '');

		//Réinitialiser la configuration
		if($action == 'reinitialiser'){
			$sql = "SELECT taux, base FROM ".MAIN_DB_PREFIX."salarie_config_heure_sup WHERE fk_salarie=".$fk_salarie;
			$obj_config = $db->fetch_object($db->query($sql));

			$sql = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$fk_user;
			$obj_sal_user = $db->fetch_object($db->query($sql));

			$sql = "SELECT nom FROM ".MAIN_DB_PREFIX."societe WHERE rowid=".$id_societe;
			$obj_soc = $db->fetch_object($db->query($sql));

			$sql = "DELETE FROM ".MAIN_DB_PREFIX."salarie_config_heure_sup WHERE fk_salarie=".$fk_salarie;
			$result2 = $db->query($sql);
			if($result2){
				$message = "Configuration réinitialisée avec succès";
				$sql_select = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$user->id;
				$obj = $db->fetch_object($db->query($sql_select));

				//On garde la trace de l'action
				$action_effectue = "Réinitialisation d'une Configuration heure sup (taux=".$obj_config->taux.", base=".$obj_config->base.") au compte du salarié ".$obj_sal_user->firstname."-".$obj_sal_user->lastname." id=".$fk_user." salarié de la société ".$obj_soc->nom;
				$sql_log = 'INSERT INTO '.MAIN_DB_PREFIX.'log (fk_user, nom, prenom, quand, action_effectue, object_concerne)';
				$sql_log .= ' VALUES('.$user->id.', "'.$obj->lastname.'","'.$obj->firstname.'",now(),"'.$action_effectue.'","Réinitialisation config")';
				$db->query($sql_log);
			}

		}

		//Enregistrement de la configuration
		if($action == 'save_config'){
			if(empty(GETPOST('taux', 'float')))
				$message .= 'Veuillez remplir le champ "TAUX"';
			if(empty(GETPOST('base', 'int')))
				$message .= 'Veuillez remplir le champ "BASE"';

			if(empty($message)){
				//suppression des espace
				$taux = str_replace(' ', '', GETPOST('taux', 'float'));
				$base = str_replace(' ', '', GETPOST('base', 'int'));

				//suppression des du pourcentage au cas
				$taux = str_replace('%', '', $taux);

				$sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."salarie_config_heure_sup (fk_salarie, taux, base)";
				$sql_insert .= " VALUES(".$fk_salarie.",'".$taux."','".$base."')";

				if($db->query($sql_insert)){
					$message = "Configuration enregistrée avec succès";

						$sql = "SELECT nom FROM ".MAIN_DB_PREFIX."societe WHERE rowid=".$id_societe;
						$obj_soc = $db->fetch_object($db->query($sql));

						$sql = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$fk_user;
						$obj_sal_user = $db->fetch_object($db->query($sql));

						$sql_select = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$user->id;
						$obj = $db->fetch_object($db->query($sql_select));

						//On garde la trace de l'action
						$action_effectue = "Ajout d'une Configuration heure sup (taux=".$taux.", base=".$base.") au compte du salarié ".$obj_sal_user->firstname."-".$obj_sal_user->lastname." id=".$fk_user." salarié de la société ".$obj_soc->nom;
						$sql_log = 'INSERT INTO '.MAIN_DB_PREFIX.'log (fk_user, nom, prenom, quand, action_effectue, object_concerne)';
						$sql_log .= ' VALUES('.$user->id.', "'.$obj->lastname.'","'.$obj->firstname.'",now(),"'.$action_effectue.'","Ajout config")';
						$db->query($sql_log);
				}
			}else{
				$action = 'ajouter_config';
			}

		}

		//Modification de la configuration
		if($action == 'save_edit_config'){
			if(empty(GETPOST('taux', 'float')))
				$message .= 'Veuillez remplir le champ "TAUX"';
			if(empty(GETPOST('base', 'int')))
				$message .= 'Veuillez remplir le champ "BASE"';

			if(empty($message)){
				//suppression des espace
				$taux = str_replace(' ', '', GETPOST('taux', 'int'));
				$base = str_replace(' ', '', GETPOST('base', 'int'));

				//suppression des du pourcentage au cas
				$taux = str_replace('%', '', $taux);

				$sql_update = "UPDATE ".MAIN_DB_PREFIX."salarie_config_heure_sup SET taux='".$taux."', base='".$base."'";
				if($db->query($sql_update)){
					$message = "Configuration modifiée avec succès";

					$sql = "SELECT nom FROM ".MAIN_DB_PREFIX."societe WHERE rowid=".$id_societe;
					$obj_soc = $db->fetch_object($db->query($sql));

						$sql = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$fk_user;
						$obj_sal_user = $db->fetch_object($db->query($sql));

						$sql_select = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$user->id;
						$obj = $db->fetch_object($db->query($sql_select));

						//On garde la trace de l'action
						$action_effectue = "Modification d'une Configuration heure sup (taux=".$taux.", base=".$base.") au compte du salarié ".$obj_sal_user->firstname."-".$obj_sal_user->lastname." id=".$fk_user." salarié de la société ".$obj_soc->nom;
						$sql_log = 'INSERT INTO '.MAIN_DB_PREFIX.'log (fk_user, nom, prenom, quand, action_effectue, object_concerne)';
						$sql_log .= ' VALUES('.$user->id.', "'.$obj->lastname.'","'.$obj->firstname.'",now(),"'.$action_effectue.'","Modification config")';
						$db->query($sql_log);
				}
			}else{
				$action = 'modifier_config';
			}

		}

		if($action != 'ajouter_config'){
			if($action != 'modifier_config'){
				$sql = "SELECT * FROM ".MAIN_DB_PREFIX."salarie_config_heure_sup WHERE fk_salarie=".$fk_salarie;
				$result = $db->query($sql);
				if($result){
					$num = $db->num_rows($result);
					if ($num < 1){
						print_barre_liste("", $page, $_SERVER["PHP_SELF"], "", "", "", "", "", "", 'bill', 0, dolGetButtonTitle("Ajouter une configuration particulière", '', 'fa fa-plus-circle', $_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=salarie&id='.$fk_user.'&fk_salarie='.$fk_salarie.'&id_convention='.$id_convention.'&id_societe='.$id_societe.'&action=ajouter_config' , '', 1), '', 0, 0, 0, 1);
						print "<table class='tagtable liste'>";
						print "<tr class='liste_titre'><td>Taux</td><td>Base de calcul</td><td>Opération</td></tr>";
						print "<tr><td colspan=3 align='center'>Aucune configuration définie</td></tr>";
					}else{
						print "<table class='tagtable liste'>";
						print "<tr class='liste_titre'><td>Taux</td><td>Base de calcul</td><td>Opération</td></tr>";
						$obj = $db->fetch_object($result);
					//Affichage de la cofiguration
						print "<tr class='fieldrequired' ><td style='width: 300px;'>".$obj->taux."%</td><td style='width: 300px;'>".apres_virgule($db, $id_societe, $obj->base)."</td>";
						print '<td>';
						print '<a href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=salarie&id='.$fk_user.'&fk_salarie='.$fk_salarie.'&id_convention='.$id_convention.'&id_societe='.$id_societe.'&action=modifier_config" class="button">Modifier</a>';
						print '<a href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=salarie&id='.$fk_user.'&fk_salarie='.$fk_salarie.'&id_convention='.$id_convention.'&id_societe='.$id_societe.'&action=reinitialiser" class="button">Réinitialiser</a></td></tr>';
						print '</td></tr>';
						print '<table>';
					}
				}else{
					print "<table class='tagtable liste'>";
					print "<tr class='liste_titre'><td>Taux</td><td>Base de calcul</td><td>Opération</td></tr>";
					print "<tr><td colspan=3 align='center'>Aucune configuration définie</td></tr>";
				}
			}else{
				$sql = "SELECT * FROM ".MAIN_DB_PREFIX."salarie_config_heure_sup WHERE fk_salarie=".$fk_salarie;
				$result = $db->query($sql);
				$obj = $db->fetch_object($result);

				print "<h3>Modification de la configuration</h3>";
			//Formulaire de modification de configuration
				print ' <form name="ajouter" method="POST" action="'.$_SERVER['PHP_SELF'].'?mainmenu=paiementsalaire&leftmenu=salarie&id='.$fk_user.'&fk_salarie='.$fk_salarie.'&id_convention='.$id_convention.'&id_societe='.$id_societe.'">';
				print '<input type="hidden" name="token" value="'.newToken().'">';
				print '<input type="hidden" name="action" value="save_edit_config">';
				print "<table>";

				print "<tr class='fieldrequired'><td style='padding: 5px; width: 200px;'>Taux</td><td style='padding: 5px; width: 300px;'><input type='number' name='taux' id='taux' placeholder='entre 1 et 100' min='1' max='100' value='".$obj->taux."' autofocus >%</td></tr>";
				print "<tr class='fieldrequired' ><td style='padding: 5px; width: 200px;'>Base de calcul</td><td style='padding: 5px; width: 300px;'><input type='text' name='base' id='base' value='".$obj->base."' ></td></tr>";
				print '<tr>';
				print '<td style=" padding-right: 30px; padding-bottom: 30px"></td><td style=" padding-bottom: 30px"><input class="button" type="submit" value="Valider">';
				print'</form>';
			}
		}else{//$action == 'ajouter_config'
			print "<h3>Configuration particulière de calcul</h3>";
			//Formulaire d'ajout de configuration
				print ' <form name="ajouter" method="POST" action="'.$_SERVER['PHP_SELF'].'?mainmenu=paiementsalaire&leftmenu=salarie&id='.$fk_user.'&fk_salarie='.$fk_salarie.'&id_convention='.$id_convention.'&id_societe='.$id_societe.'">';
				print '<input type="hidden" name="token" value="'.newToken().'">';
				print '<input type="hidden" name="action" value="save_config">';
				print "<table>";

				print "<tr class='fieldrequired'><td style='padding: 5px; width: 200px;'>Taux</td><td style='padding: 5px; width: 300px;'><input type='number' value='' name='taux' id='taux' placeholder='entre 1 et 100' min='1' max='100' autofocus>%</td></tr>";
				print "<tr class='fieldrequired' ><td style='padding: 5px; width: 200px;'>Base de calcul</td><td style='padding: 5px; width: 300px;'><input type='text' name='base' id='base' value=".!empty(GETPOST("base", "int"))." ></td></tr>";
				print '<tr>';
				print '<td style=" padding-right: 30px; padding-bottom: 30px"></td><td style=" padding-bottom: 30px"><input class="button" type="submit" value="Enregistrer">';
				print'</form>';
				print '<table>';
		}


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

