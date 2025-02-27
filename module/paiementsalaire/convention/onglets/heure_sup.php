<?php
require '../../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/custom/paiementsalaire/lib/paiementsalaire.lib.php';




llxHeader("", "Paiement | Salaire");
$id_convention = GETPOST("id_convention","int");
$action = "liste";
if(!empty(GETPOST("action")))
	$action = GETPOST("action");
//Titre 
$covSql = "SELECT * FROM ".MAIN_DB_PREFIX."convention WHERE rowid=".$id_convention;
$result = $db->query($covSql);//= $db->query($covSql);
$nom_convention = "";
if($result){
	$obj = $db->fetch_object($result);
	$nom_convention = "<b><mark>".$obj->nom."</mark></b>";
}

$titre = "Heures Supplémentaire de la convention ".$nom_convention;

print load_fiche_titre($langs->trans($titre), '', '');
//print '<hr>';

	$head = paiementsalaireConventionHead($id_convention);
	print dol_get_fiche_head($head, 'heure_sup', "", -1, '');

	if($id_convention == 1){

	}else if($id_convention == 2){
		$heure_sup = "SELECT * FROM ".MAIN_DB_PREFIX."heure_sup WHERE fk_convention=2";
		$result_heure_sup = $db->query($heure_sup);//= $db->query($covSql);
		$num = $db->num_rows($result_heure_sup);
		if($num == 0){
			$taux = array("15%","40%","75%","75%","100%");
			$description = array("41è à 48è heure de lma journée","48è + de la journé","la nuit","jours feriés ou répos pendant la journé","jours feriés ou répos pendant la nuit");
			for ($i=0; $i < count($taux); $i++) { 
				$sql = "INSERT INTO ".MAIN_DB_PREFIX."heure_sup (fk_convention, taux, commentaire) VALUES (".$id_convention.",'".$taux[$i]."','".$description[$i]."')";
				$result = $db->query($sql);
			}
		}
	}else if($id_convention == 3){
		$heure_sup = "SELECT * FROM ".MAIN_DB_PREFIX."heure_sup WHERE fk_convention=3";
		$result_heure_sup = $db->query($heure_sup);//= $db->query($covSql);
		$num = $db->num_rows($result_heure_sup);
		if($num == 0){
			$taux = array("10%","35%","50%","100%");
			$description = array("41è à 48è heure de la journée","48è + de la journé","jours feriés ou répos pendant la journé","jours feriés ou répos pendant la nuit");
			for ($i=0; $i < count($taux); $i++) { 
				$sql = "INSERT INTO ".MAIN_DB_PREFIX."heure_sup (fk_convention, taux, commentaire) VALUES (".$id_convention.",'".$taux[$i]."','".$description[$i]."')";
				$result = $db->query($sql);
			}
		}
	}else if($id_convention == 4){
		$heure_sup = "SELECT * FROM ".MAIN_DB_PREFIX."heure_sup WHERE fk_convention=4";
		$result_heure_sup = $db->query($heure_sup);//= $db->query($covSql);
		$num = $db->num_rows($result_heure_sup);
		if($num == 0){
			$taux = array("20%","40%","60%","120%");
			$description = array("41è à 48è heure de la journée","48è + de la journé","jours feriés ou répos pendant la journé","jours feriés ou répos pendant la nuit");
			for ($i=0; $i < count($taux); $i++) { 
				$sql = "INSERT INTO ".MAIN_DB_PREFIX."heure_sup (fk_convention, taux, commentaire) VALUES (".$id_convention.",'".$taux[$i]."','".$description[$i]."')";
				$result = $db->query($sql);
			}
		}
	}else if($id_convention == 5){
		$heure_sup = "SELECT * FROM ".MAIN_DB_PREFIX."heure_sup WHERE fk_convention=5";
		$result_heure_sup = $db->query($heure_sup);//= $db->query($covSql);
		$num = $db->num_rows($result_heure_sup);
		if($num == 0){
			$taux = array("30%","50%","50%","100%");
			$description = array("45è pour cusiniers et 50è pour les personnels","nuit","jours feriés ou répos pendant la journé","jours feriés ou répos pendant la nuit");
			for ($i=0; $i < count($taux); $i++) { 
				$sql = "INSERT INTO ".MAIN_DB_PREFIX."heure_sup (fk_convention, taux, commentaire) VALUES (".$id_convention.",'".$taux[$i]."','".$description[$i]."')";
				$result = $db->query($sql);
			}
		}
	}else if($id_convention == 6){
		
	}else if($id_convention == 7){
		$heure_sup = "SELECT * FROM ".MAIN_DB_PREFIX."heure_sup WHERE fk_convention=7";
		$result_heure_sup = $db->query($heure_sup);//= $db->query($covSql);
		$num = $db->num_rows($result_heure_sup);
		if($num == 0){
			$taux = array("20%","35%","50%","100%");
			$description = array("41è à 48è heures","48é heures +","jours feriés ou répos pendant la journé","jours feriés ou répos pendant la nuit");
			for ($i=0; $i < count($taux); $i++) { 
				$sql = "INSERT INTO ".MAIN_DB_PREFIX."heure_sup (fk_convention, taux, commentaire) VALUES (".$id_convention.",'".$taux[$i]."','".$description[$i]."')";
				$result = $db->query($sql);
			}
		}
	}

	if($action == "add_heure_sup"){
		$taux = GETPOST('taux');
		$desc = GETPOST('desc');
		print GETPOST('desc2');

		if(empty($taux)){
			$message = 'Le champ "TAUX" est obligatoire<br>';
		}

		if(empty($message)){
			$sql = 'INSERT INTO '.MAIN_DB_PREFIX.'heure_sup (taux, commentaire, fk_convention, fk_accord_etablissement, fk_societe) VALUES ("'.$taux.'","'.$desc.'",'.$id_convention.',0,0)';
			$result = $db->query($sql);
			if($result){
				$message = "Heure sup enregistrée avec succès";
				$action = "liste";
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
	if($result)
		$message = 'Type heure sup supprimé avec succès';
	else    $message = 'Un problème est survenu';
	$action = "liste";
	
}

if($action == "saveedit"){
	$id_heure_sup = GETPOST("id_heure_sup", "int");
	$taux = GETPOST('taux');
	$desc = GETPOST('desc');
	$id_convention = GETPOST('convention');

	if(empty($taux)){
		$message = 'Le champ "TAUX" est obligatoire<br>';
	}

	if(empty($message) && $result){
		$sql = "UPDATE ".MAIN_DB_PREFIX."heure_sup SET taux='".$taux."', commentaire='".$desc."' WHERE rowid=".$id_heure_sup;
		$result = $db->query($sql);
		if($result){
			$message = 'Heure sup modifiée avec succès';
			$action = 'liste';
		}else{
			$message = 'Un problème est survenu';
			$action = 'edit';
		}
	}
}
if($action == "create"){
	print '<form name="add_heure_supl"  method="POST" action="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=convention&id_convention='.$id_convention.'">';
	
 print '<table>';
 print '<input type="hidden" name="token" value="'.newToken().'">';
 print '<input type="hidden" name="action" value="add_heure_sup">';
 print '<tr>';
 print '<td style=" padding-right: 30px; padding-bottom: 30px" class="fieldrequired"><label>Taux</label></td>';
 print '<td style=" padding-right: 30px; padding-bottom: 30px"><input type="text" name="taux" value="'.GETPOST("taux").'"/></td> </tr>';
 print '<tr><td style=" padding-right: 30px; padding-bottom: 30px" class="fieldrequired"><label>Description</label></td>';
 print '<td style=" padding-right: 30px; padding-bottom: 30px"><textarea type="text" name="desc">'.GETPOST("desc").'</textarea></td></tr>';

 print '</tr>';
 print '<tr><td style=" padding-right: 30px; padding-bottom: 30px"><td style=" padding-bottom: 30px"><input class="button" type="submit" value="Ajouter" >';
 print'</form>';
 print '<a href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=convention&action=liste&id_convention='.$id_convention.'" class="button">Annuler</a></td></tr>';
 print '</table>';
}

if($action == "liste"){
	$id_convention = GETPOST("id_convention", "int");
	print_barre_liste("", $page, $_SERVER["PHP_SELF"], "", "", "", "", "", "", 'bill', 0, dolGetButtonTitle("Créer un nouveau type d'heure sup", '', 'fa fa-plus-circle', $_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=convention&action=create&id_convention='.$id_convention.'', '', 1), '', 0, 0, 0, 1);
	print '<table class="tagtable liste" style="width : 100%">';
 print '<tr class="liste_titre">';
 print '<td style="25%; color: darkblue; padding:20px" align="center"><label>Taux</label></td>';
 print '<td style="25%; color: darkblue; padding:20px" align="center"><label>Description</label></td>';
 print '<td style="25%; color: darkblue; padding:20px" align="center"><label>Opération</label></tr>';


	$heure_sup = "SELECT * FROM ".MAIN_DB_PREFIX."heure_sup WHERE fk_convention=".$id_convention;
	$result_heure_sup = $db->query($heure_sup);//= $db->query($covSql);
	if($result_heure_sup){
		$i = 0;
		$num = $db->num_rows($result_heure_sup);
		while ($i < $num){
			$obj_heure_sup = $db->fetch_object($result_heure_sup);

			print '<tr class="impair">';
			print '<td align="center">'.$obj_heure_sup->taux.'</td>';
			print '<td align="center">'.$obj_heure_sup->commentaire.'</td>';

			print '<td align="center"><a class="reposition editfielda" href="'.$_SERVER["PHP_SELF"].'?id_heure_sup='.$obj_heure_sup->rowid.'&action=edit_form&id_convention='.$id_convention.'">'.img_edit('Modifier','').'</a>';
			print '&nbsp;&nbsp;<a class="reposition editfielda" id="delete'.$i.'" onclick="myFunction('.$i.')" href="'.$_SERVER["PHP_SELF"].'?id_heure_sup='.$obj_heure_sup->rowid.'&action=supprimer&id_convention='.$id_convention.'">'.img_delete('Supprimer','').'&nbsp;&nbsp;&nbsp;</a>';
			print '</td>';
			print '</tr>';

			$i ++;
			}
			if($num == 0)
				print '<tr><td align="center" colspan="4">Aucun type de salarié disponible"</td></tr>';
		}else print '<tr><td align="center" colspan="4">Aucun type de salarié disponible"</td></tr>';
	

 print'</table>';
 print "<script>
 function myFunction(e){
	var b = 'delete'+e;
	var button_generer = document.getElementById(b);
	if(!confirm('Cette suppression entraînera la suppression de :\\n toutes categories liées\\n Tous les echelons liés\\n Par conséquent les salaires de base liés')){
		var lien = '".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenu=convention&action=liste&id_convention=".$id_convention."&id_societe=".$id_societe."';
		button_generer.setAttribute('href', lien);
	
	}
   }
 
 </script>";
}

if($action == "edit_form"){
	$id_heure_sup = GETPOST("id_heure_sup", "int");
	print load_fiche_titre($langs->trans("Modification d'un type"), '', '');
 print '<table><form action="'.$_SERVER["PHP_SELF"].'?leftmenu=convention&id_heure_sup='.$id_heure_sup.'&id_convention='.$id_convention.'" method="post">';
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
 print '<a href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=convention&action=liste&id_convention='.$id_convention.'" class="button">Annuler</a></td></tr>';
 print '</table>';
}
$db->free($result_heure_sup);

if(!empty($message))
	print "<script>
	$.jnotify('".$message."', {delay : 5000, fadeSpeed: 500});
	</script>";
