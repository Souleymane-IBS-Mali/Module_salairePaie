<?php
require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/custom/paiementsalaire/lib/paiementsalaire.lib.php';


llxHeader("", "Paiement | Salaire");
//Titre 
print load_fiche_titre($langs->trans("Heures suplémentaires"), '', '');
//print '<hr>';
$id_societe = GETPOST('id_societe','int');
$id_convention = GETPOST('id_convention','int');
$action = "liste";
if(!empty(GETPOST("action", "alpha")))
	$action = GETPOST("action", "alpha");

$message = '';

$head = paiementsalaireSocieteHead($id_societe, $id_convention);
print dol_get_fiche_head($head, 'heure_sup', "", -1, '');

if(!empty($id_convention)){
$soc_sql = "SELECT * FROM ".MAIN_DB_PREFIX."societe WHERE rowid=".$id_societe;
$soc_res = $db->query($soc_sql);//= $db->query($covSql);
$obj_soc = $db->fetch_object($soc_res);
$obj_soc->name = $obj_soc->nom;
$obj_soc->element = "societe";			
$obj_soc->conv = $id_convention;

societe_preview_next($db, $id_societe, $obj_soc);
entete_societe($obj_soc, 'societe');

$trouve = false;
$sql = "SELECT u.rowid, u.lastname, u.firstname, u.dateemployment, ue.fk_object, ue.egp FROM ".MAIN_DB_PREFIX."user as u";
	$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."user_extrafields as ue ON u.rowid=ue.fk_object Where ue.egp=".$id_societe;
	$result = $db->query($sql);
	if($result){
		$num = $db->num_rows($result);
		if($num > 0){
			$a = 0;
			while ($a < $num) {
				$obj = $db->fetch_object($result);
				$sql1 = "SELECT fk_user FROM ".MAIN_DB_PREFIX."salarie where fk_user=".$obj->rowid;
				$result1 = $db->query($sql1);
				if($result1){
					$obj1 = $db->fetch_object($result1);
					if($obj1)
					$trouve = true;
				}
				$a ++;
			}
			
			
		}
	}

	if($trouve == true){

		if($action == "add_heure_sup"){
			$taux = GETPOST('taux', 'float');
			$desc = GETPOST('desc', 'alpha');
			print GETPOST('desc2', "alpha");
	
			if(empty($taux)){
				$message = 'Le champ "TAUX" est obligatoire<br>';
			}
	
			if(empty($message)){
				$sql = 'INSERT INTO '.MAIN_DB_PREFIX.'heure_sup (taux, commentaire, fk_convention, fk_accord_etablissement, fk_societe) VALUES ("'.$taux.'","'.$desc.'",0,0,'.$id_societe.')';
				$result = $db->query($sql);
				if($result){
					$message = "Heure sup enregistrée avec succès";
					$action = "liste";

					//sauvegarde des trace de l'action
					$sql_select = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$user->id;
					$obj = $db->fetch_object($db->query($sql_select));

					$action_effectue = "Ajout type heure sup ".$desc."(".$taux.") à la société ".$obj_soc->nom;
					$sql_log = 'INSERT INTO '.MAIN_DB_PREFIX.'log (fk_user, nom, prenom, quand, action_effectue, object_concerne)';
					$sql_log .= ' VALUES('.$user->id.', "'.$obj->lastname.'","'.$obj->firstname.'",now(),"'.$action_effectue.'","Ajout heure sup")';
					$db->query($sql_log);
				}else{ 
					$message = "Un problème est survenu";
					$action = "create";
				}
			}else{
				$action = "create";
			}
	
	}
	
	if($action == "supprimer"){
		$id_heure_sup = GETPOST("id_heure_sup", "int");
	
				$sqlDel = "DELETE FROM ".MAIN_DB_PREFIX."salarie_heure_up WHERE fk_heure_sup=".$id_heure_sup;;
				$result = $db->query($sqlDel);
	
				$sql = "DELETE FROM ".MAIN_DB_PREFIX."heure_sup WHERE rowid=".$id_heure_sup;
				$result = $db->query($sql);
		if($result){
			$message = 'Type heure sup supprimé avec succès';

			//sauvegarde des trace de l'action
			$heure_sup = "SELECT * FROM ".MAIN_DB_PREFIX."heure_sup WHERE rowid=".$id_heure_sup;
			$result_heure_sup = $db->query($heure_sup);
			$obj_hs = $db->fetch_object($result_heure_sup);

			$sql_select = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$user->id;
			$obj = $db->fetch_object($db->query($sql_select));

			$action_effectue = "Suppression type heure sup ".$obj_hs->commentaire."(".$obj_hs->taux.") à la société ".$obj_soc->nom;
			$sql_log = 'INSERT INTO '.MAIN_DB_PREFIX.'log (fk_user, nom, prenom, quand, action_effectue, object_concerne)';
			$sql_log .= ' VALUES('.$user->id.', "'.$obj->lastname.'","'.$obj->firstname.'",now(),"'.$action_effectue.'","Suppression heure sup")';
			$db->query($sql_log);
		}else    $message = 'Un problème est survenu';
		$action = "liste";
		
	}
	
	if($action == "saveedit"){
		$id_heure_sup = GETPOST("id_heure_sup", "int");
		$taux = GETPOST('taux', 'float');
		$desc = GETPOST('desc', "alpha");
	
		if(empty($taux)){
			$message = 'Le champ "TAUX" est obligatoire<br>';
		}
	
		if(empty($message) && $result){
			$heure_sup = "SELECT * FROM ".MAIN_DB_PREFIX."heure_sup WHERE rowid=".$id_heure_sup;
			$result_heure_sup = $db->query($heure_sup);
			$obj_hs = $db->fetch_object($result_heure_sup);

			$sql = "UPDATE ".MAIN_DB_PREFIX."heure_sup SET taux='".$taux."', commentaire='".$desc."' WHERE rowid=".$id_heure_sup;
			$result = $db->query($sql);
			if($result){
				$message = 'Heure sup modifiée avec succès';
				$action = 'liste';

				//sauvegarde des trace de l'action

			$sql_select = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$user->id;
			$obj = $db->fetch_object($db->query($sql_select));

			$action_effectue = "Modification type heure sup ".$obj_hs->commentaire."(de ".$obj_hs->taux." à ".$taux.") à la société ".$obj_soc->nom;
			$sql_log = 'INSERT INTO '.MAIN_DB_PREFIX.'log (fk_user, nom, prenom, quand, action_effectue, object_concerne)';
			$sql_log .= ' VALUES('.$user->id.', "'.$obj->lastname.'","'.$obj->firstname.'",now(),"'.$action_effectue.'","Modification heure sup")';
			$db->query($sql_log);
			}else{
				$message = 'Un problème est survenu';
				$action = 'edit';
			}
		}
	}
		
		
		$head2 = Heure_sup_SocieteHead($id_societe, $id_convention);
		print dol_get_fiche_head($head2, 'hs_societe', "", -1, '');

		if($action == "create"){
			print '<form name="add_heure_supl"  method="POST" action="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=societe&id_convention='.$id_convention.'&id_societe='.$id_societe.'">';
			print '<table >';
			print '<input type="hidden" name="token" value="'.newToken().'">';
			print '<input type="hidden" name="action" value="add_heure_sup">';
			print '<tr>';
			print '<td style=" padding-right: 30px; padding-bottom: 30px" class="fieldrequired"><label>Taux</label></td>';
			print '<td style=" padding-right: 30px; padding-bottom: 30px"><input type="text" name="taux" value="'.GETPOST("taux", "float").'"/></td> </tr>';
			print '<tr><td style=" padding-right: 30px; padding-bottom: 30px" class="fieldrequired"><label>Description</label></td>';
			print '<td style=" padding-right: 30px; padding-bottom: 30px"><textarea type="text" name="desc">'.GETPOST("desc", "alpha").'</textarea></td></tr>';
			
			print '</tr>';
			print '<tr><td style=" padding-right: 30px; padding-bottom: 30px"><td style=" padding-bottom: 30px"><input class="button" type="submit" value="Ajouter" >';
			print'</form>';
			print '<a href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=societe&action=liste&id_convention='.$id_convention.'&id_societe='.$id_societe.'" class="button">Annuler</a></td></tr>';
			print '</table>';
		}

		if($action == "liste"){
			if($user->rights->paiementsalaire->salarie->write)
				print_barre_liste("", $page, $_SERVER["PHP_SELF"], "", "", "", "", "", "", 'bill', 0, dolGetButtonTitle("Créer un nouveau type d'heure sup", '', 'fa fa-plus-circle', $_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=societe&action=create&id_convention='.$id_convention.'&id_societe='.$id_societe.'', '', 1), '', 0, 0, 0, 1);
				print '<table style="width : 100%" class="tagtable liste">';
				print '<tr class="liste_titre">';
				print '<td><label>Taux</label></td>';
				print '<td><label>Description</label></td>';
				print '<td><label>Opération</label></tr>';
					$heure_sup = "SELECT * FROM ".MAIN_DB_PREFIX."heure_sup WHERE fk_societe=".$id_societe;
					$result_heure_sup = $db->query($heure_sup);//= $db->query($covSql);
					if($result_heure_sup){
						$i = 0;
						$num = $db->num_rows($result_heure_sup);
						if($user->rights->paiementsalaire->salarie->write){
							while ($i < $num){
								$obj_heure_sup = $db->fetch_object($result_heure_sup);
					
								print '<tr class="impair">';
								print '<td align="center">'.$obj_heure_sup->taux.'</td>';
								print '<td align="center">'.$obj_heure_sup->commentaire.'</td>';

								print '<td align="center"><a class="reposition editfielda" href="'.$_SERVER["PHP_SELF"].'?id_heure_sup='.$obj_heure_sup->rowid.'&action=edit_form&id_convention='.$id_convention.'&id_societe='.$id_societe.'">'.img_edit('Modifier','').'</a>';
								print '&nbsp;&nbsp;<a class="reposition editfielda" id="delete'.$i.'" onclick="myFunction('.$i.')" href="'.$_SERVER["PHP_SELF"].'?id_heure_sup='.$obj_heure_sup->rowid.'&action=supprimer&id_convention='.$id_convention.'&id_societe='.$id_societe.'">'.img_delete('Supprimer','').'&nbsp;&nbsp;&nbsp;</a>';
								print '</td>';
								print '</tr>';
					
								$i ++;
							}
						}else{
							while ($i < $num){
								$obj_heure_sup = $db->fetch_object($result_heure_sup);
					
								print '<tr class="impair">';
								print '<td align="center">'.$obj_heure_sup->taux.'</td>';
								print '<td align="center">'.$obj_heure_sup->commentaire.'</td>';

								print '<td align="center">';
								print '</td>';
								print '</tr>';
					
								$i ++;
							}
						}
							if($num == 0)
								print '<tr><td align="center" colspan="4">Aucun type d\'heure sup disponible</td></tr>';
							
						}else print '<tr><td align="center" colspan="4">Aucun type Aucun type d\'heure sup disponible</td></tr>';
				
				print'</table>';
				print "<script>
				function myFunction(e){
					var b = 'delete'+e;
					var button_generer = document.getElementById(b);
					if(!confirm('Cette suppression entraînera la suppression de :\\n toutes categories liées\\n Tous les echelons liés\\n Par conséquent les salaires de base liés')){
						var lien = '".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenu=societe&action=liste&id_convention=".$id_convention."&id_societe=".$id_societe."';
						button_generer.setAttribute('href', lien);
					
					}
				}
				
				</script>";
	}
		
		if($action == "edit_form"){
			$id_heure_sup = GETPOST("id_heure_sup", "int");
			print load_fiche_titre($langs->trans("Modification d'un type"), '', '');
			print '<table><form action="'.$_SERVER["PHP_SELF"].'?id_heure_sup='.$id_heure_sup.'&id_convention='.$id_convention.'&id_societe='.$id_societe.'" method="post">';
			print '<input type="hidden" name="token" value="'.newToken().'">';
			print '<input type="hidden" name="action" value="saveedit">';
			$sql = "SELECT * FROM ".MAIN_DB_PREFIX."heure_sup WHERE rowid=".$id_heure_sup;
			$result = $db->query($sql);
			$obj = $db->fetch_object($result);
			print '<tr>';
			print '<td style=" padding-right: 30px"><label>Taux</label></td>';
			print '<td style=" padding-right: 30px"><input type="text" value="'.$obj->taux.'" name="taux"/></td></tr>';
			print '<tr><td style=" padding-right: 30px"><label>Description</label></td>';
			print '<td style=" padding-right: 30px"><textarea type="text" name="desc">'.$obj->commentaire.'</textarea></td></tr>';
			print '<td style=" padding-right: 30px">';
			
			print '<tr><td style=" padding-right: 30px; padding-bottom: 30px"><td style=" padding-bottom: 30px"><input class="button" type="submit" value="Enregistrer" name=""/>';
			print'</form>';
			print '<a href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=societe&action=liste&id_convention='.$id_convention.'&id_societe='.$id_societe.'" class="button">Annuler</a></td></tr>';
			print '</table>';
		}
		$db->free();

		if(!empty($message))
			print "<script>
			$.jnotify('".$message."', {delay : 5000, fadeSpeed: 500});
			</script>";

		
}
}else{
	print "<h2>Veuillez affecter une <b>convention</b> à cette société</h2>";

}