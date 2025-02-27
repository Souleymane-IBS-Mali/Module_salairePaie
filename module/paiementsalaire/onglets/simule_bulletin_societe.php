<?php
require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/custom/paiementsalaire/lib/paiementsalaire.lib.php';


llxHeader("", "Paiement | Salaire");
$id_societe = GETPOST('id_societe','int');
$id_convention = GETPOST('id_convention','int');
$limit = GETPOST('limit','int')?:20;
$arret = GETPOST('arret','int')?:0;
$nb_page = GETPOST('nbpage','int')?:1;

$head = paiementsalaireSocieteHead($id_societe, $id_convention);
print dol_get_fiche_head($head, 'simule_bulletin', "", -1, '');

$soc_sql = "SELECT * FROM ".MAIN_DB_PREFIX."societe WHERE rowid=".$id_societe;
$soc_res = $db->query($soc_sql);//= $db->query($covSql);
$obj_soc = $db->fetch_object($soc_res);
$obj_soc->name = $obj_soc->nom;
$obj_soc->element = "societe";			
$obj_soc->conv = $id_convention;

societe_preview_next($db, $id_societe, $obj_soc);
entete_societe($obj_soc, 'societe');
print "<hr>";
$num_all = 0;
$tab_message = array();
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
	$obj_liste = 0;

if($trouve == true){


$sql = "SELECT u.*, ue.fk_object, ue.egp FROM ".MAIN_DB_PREFIX."user as u";
$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."user_extrafields as ue ON u.rowid=ue.fk_object Where ue.egp=".$id_societe;

$result_user = $db->query($sql);
if($result_user){
	$num_all = $db->num_rows($result_user);
	$i = 0;
	while ($i < $num_all){
		//Objet Utilisateur
		$obj_user = $db->fetch_object($result_user);			
		
		//Objet Utilisateur
		$sql_sal = "SELECT * FROM ".MAIN_DB_PREFIX."salarie WHERE fk_user=".$obj_user->rowid;
		$result_sal = $db->query($sql_sal);
			$obj_salarie = $db->fetch_object($result_sal);
			$virgule = 0;
		if($obj_salarie){
			$salaire_base = 0;
			$grilleSql = "SELECT rowid FROM ".MAIN_DB_PREFIX."grille WHERE active=1 AND fk_convention=".$id_convention;
			$grilleResult = $db->query($grilleSql);//= $db->query($grilleSql);
			$obj_grille = $db->fetch_object($grilleResult);

			$salBaseSql = "SELECT salaire_base FROM ".MAIN_DB_PREFIX."grille_categorie_echelon_salaire_base WHERE fk_grille=".$obj_grille->rowid." AND fk_categorie=".$obj_salarie->fk_categorie." AND fk_echelon=".$obj_salarie->fk_echelon;
			$salBaseResult = $db->query($salBaseSql);//= $db->query($covSql);
			$objSalBase = null;
			if($salBaseResult){
				$objSalBase = $db->fetch_object($salBaseResult);
			}

			if($obj_salarie->matricule == null){
				$obj_liste ++;
			}

			if($objSalBase->salaire_base == null){
				$obj_liste ++;

			}
			
			if($obj_salarie->sursalaire == null){
				$obj_liste ++;
			}

			$annee = (int)date("Y");
			$mois = (int)date("m");
			$jour = (int)date("d");
			$sql_contrat = "SELECT * FROM ".MAIN_DB_PREFIX."salarie_contrat WHERE fk_salarie=".$obj_salarie->rowid." AND active=1 AND fk_type_contrat != 2";
			$sql_contrat .= " AND ( YEAR(date_fin)>".$annee;
			$sql_contrat .= " OR ((YEAR(date_fin) = ".$annee."  AND MONTH(date_fin) > ".$mois.") OR  (YEAR(date_fin) = ".$annee."  AND MONTH(date_fin) = ".$mois." AND DAY(date_fin) >= ".$jour.")))";
			$res_contrat = $db->query($sql_contrat);
			

			if($db->num_rows($res_contrat) <= 0){
				$sql_contrat = "SELECT * FROM ".MAIN_DB_PREFIX."salarie_contrat WHERE fk_salarie=".$obj_salarie->rowid." AND active=1 AND fk_type_contrat = 2";
				$res_contrat = $db->query($sql_contrat);
				if($db->num_rows($res_contrat) <= 0){
					$obj_liste ++;
				}
			}


			if(!$obj_salarie->date_anciennete && !$obj_user->dateemployment){
				$obj_liste ++;

			}

			if(!$obj_user->job){
				$obj_liste ++;	

			}

			if(!$obj_salarie->situation_familiale){
				$obj_liste ++;
			}
			
			if($obj_salarie->nombre_enfant == null){
				$obj_liste ++;
			}

			if($obj_salarie->nombre_enfant_hand == null){
				$obj_liste ++;
			}

			//---------------------------------------------------------------------
		}else{
			$obj_liste ++;
		}
		$i ++;
	}
}

	if($obj_liste < 1){
		print "<div><table class='tagtable liste'>";
		print "<tr class='liste_titre'><td>Resultat de la simulation</td></tr>";
		print "<tr><td>Vérification effectuée avec succès".img_picto("OK", "tick")."</td></tr>";
		print "<tr><td>Simulation effectuée avec succès".img_picto("OK", "tick")."</td></tr>";
		print "</table> </div>";
	}else{
		print "<div><table class='tagtable liste'>";
			print "<tr class='liste_titre'><td>Resultat de la verification</td></tr>";
			print "<tr><td><h2>Echec de la phase de verification".img_picto("Informatins de certains salariés sont manquantes", "error")."</h2></td></tr>";
			print "<tr><td>".img_picto("Aide", "help")." Veuillez completer les informations des salariés dans l'onglet <a href='./validation_societe.php?mainmenu=paiementsalaire&leftmenu=societe&id_societe=".$id_societe."&id_convention=".$id_convention."' ><b>Validation</b><a/></td></tr>";
			print "</table> </div>";
}

	
}else {
	print "<h2 style='align:center;'>Cette sociétée n'a aucun employé!";
}
$db->free();

