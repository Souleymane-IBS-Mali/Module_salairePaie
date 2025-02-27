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
		$salSql = "SELECT rowid FROM ".MAIN_DB_PREFIX."societe_prime_heure_sup where fk_societe=".$id_societe;
		$result = $db->query($salSql);
		if($result)
			if($db->num_rows($result) < 1){
				$sql = 'INSERT INTO '.MAIN_DB_PREFIX.'societe_prime_heure_sup (fk_societe, salaire_base, sursalaire, anciennete, montant)';
				$sql .= ' VALUES ('.$id_societe.',"non","non","non","0")';
				$db->query($sql);
			}


		//Onglet config
		$head2 = Heure_sup_SocieteHead($id_societe, $id_convention);
		print dol_get_fiche_head($head2, 'hs_prime', "", -1, '');

		if($action == "save_anciennete"){
			$rep = GETPOST("anciennete", "alpha");
			if(empty($rep)){
				$message = "Veuillez Choisir";
			}
			if(empty($message)){
				$sql_update = 'UPDATE '.MAIN_DB_PREFIX.'societe_prime_heure_sup SET anciennete="'.$rep.'" WHERE fk_societe='.$id_societe;
				if($db->query($sql_update)){
					$message = "Modification pris en compte";
					$action = "";
				}else{
					$message = "Un problème est survenu";
					$action = "edit_anciennete";
				}
			}else $action = "edit_anciennete";
		}
	
		if($action == "save_sursalaire"){
			$sursalaire = GETPOST("sursalaire", "alpha");
			if(empty($sursalaire)){
				$message = "Veuillez Choisir";
			}
			if(empty($message)){
				$sql_update = 'UPDATE '.MAIN_DB_PREFIX.'societe_prime_heure_sup SET sursalaire="'.$sursalaire.'" WHERE fk_societe='.$id_societe;
				if($db->query($sql_update)){
					$message = "Modification pris en compte";
					$action = "";
				}else{
					$message = "Un problème est survenu";
					$action = "edit_sursalaire";
				}
			}else $action = "edit_sursalaire";
		}
	
		/*if($action == "save_salaire_base"){
			$salaire_base = GETPOST("salaire_base", "aZ");
			if(empty($salaire_base)){
				$message = "Veuillez Choisir";
			}
			if(empty($message)){
				$sql_update = 'UPDATE '.MAIN_DB_PREFIX.'societe_prime_heure_sup SET salaire_base="'.$salaire_base.'" WHERE fk_societe='.$id_societe;
				if($db->query($sql_update)){
					$message = "Modification pris en compte";
					$action = "";
				}else{
					$message = "Un problème est survenu";
					$action = "edit_salaire_base";
				}
			}else $action = "edit_salaire_base";
		}*/

		if($action == "save_montant"){
			$montant = GETPOST("montant", "int");
			if(empty($montant) && $montant != 0){
				$message = "Veuillez saisir un nombre";
			}

			if(empty($message)){
				$sql_update = 'UPDATE '.MAIN_DB_PREFIX.'societe_prime_heure_sup SET montant="'.$montant.'" WHERE fk_societe='.$id_societe;
				if($db->query($sql_update)){
					$message = "Modification pris en compte";
					$action = "";
				}else{
					$message = "Un problème est survenu";
					$action = "edit_montant";
				}
			}else $action = "edit_montant";
		}

		$salSql = "SELECT * FROM ".MAIN_DB_PREFIX."societe_prime_heure_sup where fk_societe=".$id_societe;
		$result = $db->query($salSql);
		if($result)
			$obj = $db->fetch_object($result);
		//class="tagtable liste"

		print '<table class="tagtable liste">';
		/*if($action == "edit_salaire_base"){
			print '<div><form name="add" method="POST" action="'.$_SERVER['PHP_SELF'].'?mainmenu=paiementsalaire&leftmenu=salarie&id_societe='.$id_societe.'&id='.$fk_user.'&id_convention='.$id_convention.'">';
			print '<input type="hidden" name="token" value="'.newToken().'">';
			print '<input type="hidden" name="action" value="save_salaire_base">';
			print '<tr>';
			print '</td><td style="padding: 10px; width: 200px;">Salaire de base</td>';
			print '<td style="padding: 10px; width: 200px;"><select name="salaire_base" >';
			if($obj->salaire_base == 'oui'){
				print '<option value="oui" selected>Oui</option>';
				print '<option value="non" >Non</option>';
			}else{
				print '<option value="oui" >Oui</option>';
				print '<option value="non" selected>Non</option>';
			}
			print '</select>
			<input class="button" type="submit" value="Valider" >';
	
			print '</form>';
			print '<a class="reposition editfielda button" href="'.$_SERVER['PHP_SELF'].'?mainmenu=paiementsalaire&leftmenu=salarie&id_societe='.$id_societe.'&id='.$fk_user.'&id_convention='.$id_convention.'">Annuler</a>';
			print '</td></tr>';
		}else{
			print '<tr class="impair">';
			print '</td><td style="padding: 10px; width: 200px;">Salaire de base</td>';
			$rep = 'Non';
			if($obj->salaire_base == 'oui')
				$rep = 'Oui';
			print '<td style="padding: 10px; width: 200px;">'.$rep;
			if($user->rights->paiementsalaire->societe->genererBulletin)
				print '<a class="reposition editfielda" href="'.$_SERVER['PHP_SELF'].'?mainmenu=paiementsalaire&leftmenu=salarie&id_societe='.$id_societe.'&id='.$fk_user.'&id_convention='.$id_convention.'&action=edit_salaire_base">&nbsp;&nbsp;'.img_edit('Modifier','').'</a>';
			else
				print img_edit('Vous n\'avez pas cette permission','');

			print '</td>';
			print '</tr>';
		}*/

		$info = 'Si vous mettez cette valeur à "OUI", les primes des heures supplémentaires seront calculées sur la base du salaire de base et du sursalaire.';
		if($action == "edit_sursalaire"){
			print '<div><form name="add" method="POST" action="'.$_SERVER['PHP_SELF'].'?mainmenu=paiementsalaire&leftmenu=salarie&id_societe='.$id_societe.'&id='.$fk_user.'&id_convention='.$id_convention.'">';
			print '<input type="hidden" name="token" value="'.newToken().'">';
			print '<input type="hidden" name="action" value="save_sursalaire">';
			print '<tr>';
			print '</td><td style="padding: 10px; width: 200px;">Ajouter le Sursalaire à la base des heures sup? '.info_admin($info,1).'</td>';
			print '<td style="padding: 10px; width: 200px;"><select name="sursalaire" >';
			if($obj->sursalaire == 'oui'){
				print '<option value="oui" selected>Oui</option>';
				print '<option value="non" >Non</option>';
			}else{
				print '<option value="oui" >Oui</option>';
				print '<option value="non" selected>Non</option>';
			}
			print '</select>
			</select>
			<input class="button" type="submit" value="Valider" >';

			print '</form>';
			print '<a class="reposition editfielda button" href="'.$_SERVER['PHP_SELF'].'?mainmenu=paiementsalaire&leftmenu=salarie&id_societe='.$id_societe.'&id='.$fk_user.'&id_convention='.$id_convention.'">Annuler</a>';
			print '</td></tr>';
		}else{
			print '<tr class="impair">';
			print '</td><td style="padding: 10px; width: 200px;">Ajouter le Sursalaire à la base des heures sup? '.info_admin($info,1).'</td>';
			$rep = 'Non';
			if($obj->sursalaire == 'oui')
				$rep = 'Oui';
			print '<td style="padding: 10px; width: 200px;">'.$rep;

			if($user->rights->paiementsalaire->societe->genererBulletin)
				print '<a class="reposition editfielda" href="'.$_SERVER['PHP_SELF'].'?mainmenu=paiementsalaire&leftmenu=salarie&id_societe='.$id_societe.'&id='.$fk_user.'&id_convention='.$id_convention.'&action=edit_sursalaire">&nbsp;&nbsp;'.img_edit('Modifier','').'</a>';
			else
				print img_edit('Vous n\'avez pas cette permission','');

			print '</td>';
			print '</tr>';
		}
		
		$info = 'Si vous mettez cette valeur à "OUI", les primes des heures supplémentaires seront calculées sur la base du salaire de base majorée de la prime d\'ancienneté';
		if($action == "edit_anciennete"){
			print '<div><form name="add" method="POST" action="'.$_SERVER['PHP_SELF'].'?mainmenu=paiementsalaire&leftmenu=salarie&id_societe='.$id_societe.'&id='.$fk_user.'&id_convention='.$id_convention.'">';
			print '<input type="hidden" name="token" value="'.newToken().'">';
			print '<input type="hidden" name="action" value="save_anciennete">';
			print '<tr class="pair">';
			print '<td style="padding: 10px; width: 200px;">Ajoter l\'ancienneté à la base des heures sup? '.info_admin($info,1).'</td>';
			print '<td style="padding: 10px; width: 200px;"><select name="anciennete" >';
			if($obj->anciennete == 'oui'){
				print '<option value="oui" selected>Oui</option>';
				print '<option value="non" >Non</option>';
			}else{
				print '<option value="oui" >Oui</option>';
				print '<option value="non" selected>Non</option>';
			}
			print '</select>
			</select>
			<input class="button" type="submit" value="Valider" >';
	
			print '</form>';
			print '<a class="reposition editfielda button" href="'.$_SERVER['PHP_SELF'].'?mainmenu=paiementsalaire&leftmenu=salarie&id_societe='.$id_societe.'&id='.$fk_user.'&id_convention='.$id_convention.'">Annuler</a>';
			print '</td></tr>';
	
		}else{
			print '<tr class="impair">';
			print '<td style="padding: 10px; width: 200px;">Ajoter l\'ancienneté à la base des heures sup? '.info_admin($info,1).'</td>';
			$rep = 'Non';
			if($obj->anciennete == 'oui')
				$rep = 'Oui';
			print '<td style="padding: 10px; width: 200px;">'.$rep;

			if($user->rights->paiementsalaire->societe->genererBulletin)
				print '<a class="reposition editfielda" href="'.$_SERVER['PHP_SELF'].'?mainmenu=paiementsalaire&leftmenu=salarie&id_societe='.$id_societe.'&id='.$fk_user.'&id_convention='.$id_convention.'&action=edit_anciennete">&nbsp;&nbsp;'.img_edit('Modifier','').'</a>';
			else
				print img_edit('Vous n\'avez pas cette permission','');

			print '</td>';
			print '</tr>';
		}

		if($action == "edit_montant"){
			print '<div><form name="add" method="POST" action="'.$_SERVER['PHP_SELF'].'?mainmenu=paiementsalaire&leftmenu=salarie&id_societe='.$id_societe.'&id='.$fk_user.'&id_convention='.$id_convention.'">';
			print '<input type="hidden" name="token" value="'.newToken().'">';
			print '<input type="hidden" name="action" value="save_montant">';
			print '<tr>';
			print '</td><td style="padding: 10px; width: 200px;">Appliquer ce montant fixe pour totes les heures sup</td>';
			print '<td style="padding: 10px; width: 200px;"><input type="text" name="montant" value="'.($obj->montant?:GETPOST("montant", "int")).'">';
			print '<input class="button" type="submit" value="Valider" >';

			print '</form>';
			print '<a class="reposition editfielda button" href="'.$_SERVER['PHP_SELF'].'?mainmenu=paiementsalaire&leftmenu=salarie&id_societe='.$id_societe.'&id='.$fk_user.'&id_convention='.$id_convention.'">Annuler</a>';
			print '</td></tr>';
		}else{
			$info = 'Si vous fixez ce montant, les taux horaires des heures supplémentaires ne seront plus pris en compte. À la place, le système utilisera ce montant unique pour chaque heure supplémentaire travaillée, quelle que soit l\'heure à laquelle elle a été effectuée.';
			print '<tr class="impair">';
			print '</td><td style="padding: 10px; width: 200px;">Appliquer ce montant fixe pour toutes les heures sup? '.info_admin($info,1).'</td>';
			print '<td style="padding: 10px; width: 200px;"><input typ="text" value="'.$obj->montant.'" disabled >';

			if($user->rights->paiementsalaire->societe->genererBulletin)
				print '<a class="reposition editfielda" href="'.$_SERVER['PHP_SELF'].'?mainmenu=paiementsalaire&leftmenu=salarie&id_societe='.$id_societe.'&id='.$fk_user.'&id_convention='.$id_convention.'&action=edit_montant">&nbsp;&nbsp;'.img_edit('Modifier','').'</a>';
			else
				print img_edit('Vous n\'avez pas cette permission','');

			print '</td>';
			print '</tr>';
		}
	
	
		print '</table>';
	
	if(!empty($message))
		print "<script>
		$.jnotify('".$message."', {delay : 5000, fadeSpeed: 500});
		</script>";

	}
}else{
	print "<h2>Veuillez affecter une <b>convention</b> à cette société</h2>";

}