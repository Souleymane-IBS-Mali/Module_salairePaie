<?php
require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/custom/paiementsalaire/lib/paiementsalaire.lib.php';


llxHeader("", "Paiement | Salaire");
//Titre 
print load_fiche_titre($langs->trans("Verification des éléments de salaire de ce salarié"), '', '');
//print '<hr>';
$fk_user = GETPOST("id","int");
$id_societe = GETPOST("id_societe","int");
$fk_salarie = GETPOST("fk_salarie", "int");
$id_convention = GETPOST("id_convention","int");
$head = salaire_Head($fk_salarie, $fk_user, $id_societe, $id_convention);
	print dol_get_fiche_head($head, 'verification', "", -1, '');


if($user->id !=1 && $user->id != $fk_user && !$user->rights->paiementsalaire->salarie->read){
	print "<h2> Vous n\'avez pas ce droit </h2>";
}else{
	
	$action = GETPOST("action", "alpha");

	if(empty($fk_salarie)){
		print "Page non Disponible";
	}else{
		$obj_soc = prepare_objet_entete($fk_salarie, $fk_user, $db, $id_societe, $id_convention);
		entete_societe($obj_soc, 'societe');
		print "<hr>";
		$tab_message = array();
		$tab_message_statut = array();
		$tab_aide = array();

			//Objet Utilisateur
			$sql_sal = "SELECT * FROM ".MAIN_DB_PREFIX."salarie WHERE rowid=".$fk_salarie;
			$result_sal = $db->query($sql_sal);
				$obj_salarie = $db->fetch_object($result_sal);
				$virgule = 0;
				
				$salaire_base = 0;
				$grilleSql = "SELECT rowid FROM ".MAIN_DB_PREFIX."grille WHERE active=1 AND fk_convention=".$id_convention;
				$grilleResult = $db->query($grilleSql);//= $db->query($grilleSql);
				$obj_grille = $db->fetch_object($grilleResult);

				$salBaseSql = "SELECT salaire_base FROM ".MAIN_DB_PREFIX."grille_categorie_echelon_salaire_base WHERE fk_grille=".$obj_grille->rowid." AND fk_categorie=".$obj_salarie->fk_categorie." AND fk_echelon=".$obj_salarie->fk_echelon;
				$salBaseResult = $db->query($salBaseSql);//= $db->query($covSql);
				if($salBaseResult){
					$objSalBase = $db->fetch_object($salBaseResult);
					if($objSalBase->salaire_base != null){
						$tab_message[] = "Salaire de Base";
						$tab_message_statut[] = "OK";
						$tab_aide[] = "Aucun problème";

						$tab_message[] = "Catégorie";
						$tab_message_statut[] = "OK";
						$tab_aide[] = "Aucun problème";
					}else
						if(!$objSalBase->salaire_base){
							$tab_message[] = "Catégorie";
							$tab_message_statut[] = "Non OK";
							$tab_aide[] = "Veuillez affecter une catégorie à ce salarié";

							$tab_message[] = "Salaire de Base";
							$tab_message_statut[] = "Non OK";
							$tab_aide[] = "Ce salarié n'a pas de salaire de base verifiez sa catégorie";
						}
					}else{
							$tab_message[] = "Catégorie";
							$tab_message_statut[] = "Non OK";
							$tab_aide[] = "Veuillez affecter une catégorie à ce salarié";

							$tab_message[] = "Salaire de Base";
							$tab_message_statut[] = "Non OK";
							$tab_aide[] = "Ce salarié n'a pas de salaire de base verifiez sa catégorie";
					}
				
				if($obj_salarie->sursalaire != null){
					$tab_message[]= "Sursalaire";
					$tab_message_statut[] = "OK";
					$tab_aide[] = "Aucun problème";
		
				}else{
					$tab_message[] = "Sursalaire";
					$tab_message_statut[] = "Non OK";
					$tab_aide[] = "Veuillez définir un sursalaire pour ce salarié";
		
				}

				$annee = (int)date("Y");
				$mois = (int)date("m");
				$jour = (int)date("d");
				$sql_contrat = "SELECT * FROM ".MAIN_DB_PREFIX."salarie_contrat WHERE fk_salarie=".$obj_salarie->rowid." AND active=1 AND fk_type_contrat != 2";
				$sql_contrat .= " AND ( YEAR(date_fin)>".$annee;
				$sql_contrat .= " OR ((YEAR(date_fin) = ".$annee."  AND MONTH(date_fin) > ".$mois.") OR  (YEAR(date_fin) = ".$annee."  AND MONTH(date_fin) = ".$mois." AND DAY(date_fin) >= ".$jour.")))";
				$res_contrat = $db->query($sql_contrat);

				if($db->num_rows($res_contrat) > 0){
					$obj_contrat = $db->fetch_object($res_contrat);
					$tab_message[] = "Contrat";
					$tab_message_statut[] = "OK";
					$tab_aide[] = "Aucun problème";

				}else{
					$sql_contrat = "SELECT * FROM ".MAIN_DB_PREFIX."salarie_contrat WHERE fk_salarie=".$obj_salarie->rowid." AND active=1 AND fk_type_contrat = 2";
					$res_contrat = $db->query($sql_contrat);
					if($db->num_rows($res_contrat) > 0){
						$tab_message[] = "Contrat";
						$tab_message_statut[] = "OK";
						$tab_aide[] = "Aucun problème";

					}else{
						$sql_contrat = "SELECT * FROM ".MAIN_DB_PREFIX."salarie_contrat WHERE fk_salarie=".$obj_salarie->rowid." AND active=1";
						$res_contrat = $db->query($sql_contrat);
						if($db->num_rows($res_contrat) > 0){
							$tab_message[] = "Contrat Expiré";
							$tab_message_statut[] = "Non OK";
							$tab_aide[] = "le contrat de ce salarié a expiré. Veuillez renouveller son contrat";
						}else{
							$tab_message[] = "Contrat";
							$tab_message_statut[] = "Non OK";
							$tab_aide[] = "Ce salarié n'a pas de contrat";
						}

					}
				}
				$sql = "SELECT * FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$obj_salarie->fk_user;			
				$result_user = $db->query($sql);
				if($result_user)
					$obj_user = $db->fetch_object($result_user);			

				if(!$obj_salarie->date_anciennete && !$obj_user->dateemployment){
					$tab_message[]= "Date d'embauche pour calculer l'anciennété";
					$tab_message_statut[] = "Non OK";
					$tab_aide[] = "Veuillez définir une date d'entrée pour ce salarié";

				}else{
					$tab_message[]= "Date d'embauche pour calculer l'anciennété";
					$tab_message_statut[] = "OK";
					$tab_aide[] = "Aucun problème";

				}

				if(!$obj_user->job){
					$tab_message[]= "Poste/Fonction";
					$tab_message_statut[] = "Non OK";
					$tab_aide[] = "Veuillez donner un poste à ce salarié";

				}else{
					$tab_message[] = "Poste/Fonction";
					$tab_message_statut[] = "OK";
					$tab_aide[] = "Aucun problème";

				}

				if($obj_salarie->situation_familiale){
					$tab_message[] = "Statut Matrimoniale";
					$tab_message_statut[] = "OK";
					$tab_aide[] = "Aucun problème";
					
				}else{
					$tab_message[] = "Statut Matrimoniale";
					$tab_message_statut[] = "Non OK";
					$tab_aide[] = "Veuillez définir si ce salarié est : Célibataire, Marié ou Divorcé";
					
				}
				
				if($obj_salarie->nombre_enfant != null){
					$tab_message[] = "Nombre enfant";
					$tab_message_statut[] = "OK";
					$tab_aide[] = "Aucun problème";
				}else{
					$tab_message[] = "Nombre enfant";
					$tab_message_statut[] = "Non OK";
					$tab_aide[] = "Veuillez définir le nombre d'enfant de ce salarié";

				}

				if($obj_salarie->nombre_enfant_hand != null){
					$tab_message[] = "Nombre enfant Handicapé";
					$tab_message_statut[] = "OK";
					$tab_aide[] = "Aucun problème";

				}else{
					$tab_message[] = "Nombre enfant Handicapé";
					$tab_message_statut[] = "Non OK";
					$tab_aide[] = "Veuillez définir le nombre d'enfant Handicapé de ce salarié";

				}

				//---------------------------------------------------------------------
					
				print "<h2>Verification des informations</h2>";
			print "<table class='tagtable liste'>";
			print "<tr class='liste_titre'><td>Elements du salaire</td><td align='center'>Statut</td>";
			for ($i=0; $i < count($tab_message); $i++) {
				$class = "pair";
				if($i % 2 == 0)
					$class = "impair";
				print '<tr class='.$class.'><td>';
				if($tab_message_statut[$i] == "OK") 
					print $tab_message[$i]."</td><td align='center'>".img_picto($tab_aide[$i],"tick");
				else print $tab_message[$i]."</td><td align='center'>".img_picto($tab_aide[$i],"error");
				print '</td></tr>';
			}
			print "</table>";

		
	}
	$db->close();



}