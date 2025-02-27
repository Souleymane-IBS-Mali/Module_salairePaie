<?php
require '../../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/custom/paiementsalaire/lib/paiementsalaire.lib.php';




llxHeader("", "Paiement | Salaire");
$id_convention = GETPOST("id_convention","int");
$id_accord = GETPOST("id_accord","int");

if(!$id_convention){
	$covSql = "SELECT fk_societe FROM ".MAIN_DB_PREFIX."accord_etablissement WHERE rowid=".$id_accord;
	$result = $db->query($covSql);//= $db->query($covSql);
	$obj = $db->fetch_object($result);

	$sql = "SELECT conv FROM ".MAIN_DB_PREFIX."societe_extrafields WHERE fk_object=".$obj->fk_societe." AND grp=1";
	$result = $db->query($sql);
	$obj = $db->fetch_object($result);

	$id_convention = $obj->conv;

}
$covSql = "SELECT * FROM ".MAIN_DB_PREFIX."accord_etablissement WHERE rowid=".$id_accord;
$result = $db->query($covSql);//= $db->query($covSql);
$nom_accord = "";
if($result){
	$obj = $db->fetch_object($result);
	$nom_accord = "<b><mark>".$obj->nom."</mark></b>";
}
$message = "";
$action = GETPOST('action','alpha');
if(empty($action))	
	$action = 'afficher';

	if($action == "add"){
		$nom_grille = GETPOST('nom_grille', 'alpha');
		$commentaire = GETPOST('commentaire', 'alpha');

		if(empty($nom_grille)) {
			$message .= 'Le champ "Nom Grille" est Obligatoire<br>';
		}
		if(empty($commentaire)){
			$message .= $message.'Le champ "Description" est Obligatoire<br>';
		} 			
$salaire_base = array();
$code_categ = array();
$id_echelon = array();

$indice = 0;
	$grilleSql = "SELECT rowid, nom FROM ".MAIN_DB_PREFIX."grille WHERE active=1 AND fk_convention=".$id_convention;
	$grilleResult = $db->query($grilleSql);//= $db->query($grilleSql);
	$obj_grille = $db->fetch_object($grilleResult);
				
	$covSql = "SELECT rowid, code_categorie FROM ".MAIN_DB_PREFIX."dcategories WHERE fk_convention=".$id_convention;
	$result = $db->query($covSql);//= $db->query($covSql);

	if(!empty($result)){
		$i = 0;
		$num = $db->num_rows($result);
		while ($i < $num){
			$line = array();
			$obj = $db->fetch_object($result);

			$echelonSql = "SELECT rowid, libelle FROM ".MAIN_DB_PREFIX."echelon WHERE fk_categorie=".$obj->rowid;
			$echelonResult = $db->query($echelonSql);//= $db->query($covSql);
			$numEch = $db->num_rows($echelonResult);
			if($numEch > 0){
				$j=0;
				while ($j < $numEch){
					$obj_ech = $db->fetch_object($echelonResult);
					$salBaseSql = "SELECT salaire_base FROM ".MAIN_DB_PREFIX."grille_categorie_echelon_salaire_base WHERE fk_echelon=".$obj_ech->rowid;
					$salBaseResult = $db->query($salBaseSql);//= $db->query($covSql);
					$objSalBase = $db->fetch_object($salBaseResult);

					$nom_sal_base = "sal_base".$obj->rowid."".$j;
					if($j==0){
						if(empty(GETPOST($nom_sal_base, 'int')))
							$message .= 'Le champ "'.$obj->code_categorie.'==>'.$obj_ech->libelle.'" est Obligatoire<br>';
						else {
							$salaire_base[count($salaire_base)] = GETPOST($nom_sal_base, 'int');
							$code_categ[count($code_categ)] = $obj->rowid;
							$id_echelon[count($id_echelon)] = $obj_ech->rowid;
						}
					}else{
						if(empty(GETPOST($nom_sal_base, 'int')))
						$message .= 'Le champ "'.$obj->code_categorie.'==>'.$obj_ech->libelle.'" est Obligatoire<br>';
						else {
							$salaire_base[count($salaire_base)] = GETPOST($nom_sal_base, 'int');
							$code_categ[count($code_categ)] = $obj->rowid;
							$id_echelon[count($id_echelon)] = $obj_ech->rowid;
						}
					}
					$j++;
				}
				
			}else{	
				$nom_sal_base = "sal_base".$obj->rowid."".$i;
				if(empty(GETPOST($nom_sal_base, 'int')))
				$message .= 'Le champ "'.$obj->code_categorie.'==>'.$obj_ech->libelle.'" est Obligatoire<br>';
				else {
					$salaire_base[count($salaire_base)] = GETPOST($nom_sal_base, 'int');
					$code_categ[count($code_categ)] = $obj->rowid;
					$id_echelon[count($id_echelon)] = 0;
				}												
			}
			$info[$indice] = $line;
			$indice ++;
			$i ++;
		}
	
	}

	if(empty($message)){		
		$sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'grille (fk_convention, nom, commentaire, active) VALUES ('.$id_convention.',"'.$nom_grille.'","'.$commentaire.'",0)';
		$res1 = $db->query($sql_insert);

		if($res1){
			$result = $db->query("SELECT LAST_INSERT_ID() as rowid;");
			$obj = $db->fetch_object($result);
			$id_grille =  $obj->rowid;

			$lim = count($code_categ);
			for ($i=0; $i< $lim; $i++){
				$sql = 'INSERT INTO '.MAIN_DB_PREFIX.'grille_categorie_echelon_salaire_base (fk_grille, fk_categorie, fk_echelon, salaire_base) VALUES ('.$id_grille.','.$code_categ[$i].','.$id_echelon[$i].',"'.$salaire_base[$i].'")';
				$res2 = $db->query($sql);
			}
		}
		
	}
	if ($res2){
		$action = "afficher";
		$message = "Une grille avec Categories, Echelons et Salaires de Base Enregistrer avec succès";
	}else {
		$action = "create";
		$message = "Un problème est survenu !";
	}
	}

	if($action == 'activate'){
		$id_convention = GETPOST('id_convention', 'int');

		$sqlEdit = "UPDATE ".MAIN_DB_PREFIX."grille SET active=0 WHERE fk_convention=".$id_convention;
		$result = $db->query($sqlEdit);

		$id_grille = GETPOST('id_grille', 'int');
		$sqlEdit = "UPDATE ".MAIN_DB_PREFIX."grille SET active=1 WHERE rowid=".$id_grille;
		$result = $db->query($sqlEdit);
		if($result)
			$message = 'Grille Activée avec succès';
		else $message = 'Un problème es survenu';
			$action = 'afficher';
	}
	if($action == "afficher"){
		$covSql = "SELECT * FROM ".MAIN_DB_PREFIX."convention WHERE rowid=".$id_convention;
		$result = $db->query($covSql);
		if($result){
			$obj_conv = $db->fetch_object($result);
			if(!empty($obj_conv)){
				$titre = "Grille de salaire de l'accord ".$nom_accord;

				print load_fiche_titre($langs->trans($titre), '', '');

			$head = paiementsalaireAccordHead($id_convention, $id_accord);

			print dol_get_fiche_head($head, 'grille', "", -1, '');
				print_barre_liste("", $page, $_SERVER["PHP_SELF"], "", "", "", "", "", "", 'bill', 0, dolGetButtonTitle("Créer une grille", '', 'fa fa-plus-circle', $_SERVER['PHP_SELF'].'?action=create&id_accord='.$id_accord.'&id_convention='.$id_convention.'' , '', 1), '', 0, 0, 0, 1);
			
				$acts[0] = "activate";
				$acts[1] = "disable";
				$actl[0] = img_picto($langs->trans("Disabled"), 'switch_off', 'class="size30x"');
				$actl[1] = img_picto($langs->trans("Activated"), 'switch_on', 'class="size30x"');
			
				print '<table>';
				print '<tr class="liste_titre"><td class="liste_titre" style="padding: 20px; width : 5%;" >Nom</td><td style="padding: 20px; width : 5%;" >Description</td><td class="liste_titre" style="padding: 20px; width : 5%;" >Opération</td></tr>';
				$covSql = "SELECT * FROM ".MAIN_DB_PREFIX."grille WHERE fk_convention=".$id_convention;
					$result = $db->query($covSql);//= $db->query($covSql);
					if($result){
						$i = 0;
						$num = $db->num_rows($result);
						while ($i < $num){
							$obj_grille = $db->fetch_object($result);
							if($num > 1){
								if($obj_grille->active == 1){
									print '<tr class="pair"><td class="pair" align="left" style="padding: 10px; width : 5%;"><b>'.$obj_grille->nom.'<b></td><td >'.$obj_grille->commentaire.'</td>';
									print'<td><a class="reposition" href="#">'.$actl[$obj_grille->active].'</a></td>';
								}else{
									print '<tr class="impair"><td class="impair" align="left" style="padding: 10px; width : 5%;"><b>'.$obj_grille->nom.'<b></td><td>'.$obj_grille->commentaire.'</td>';
									print'<td><a class="reposition" href="'.$url.'?mainmenu=paiementsalaire&leftmenu=accord_etablissement&id_accord='.$id_accord.'&action='.$acts[$obj_grille->active].'&id_convention='.$id_convention.'&id_grille='.$obj_grille->rowid.'&token='.newToken().'">'.$actl[$obj_grille->active].'</a></td>';
								}
							}else{
								print '<tr class="pair"><td class="pair" align="left" style="padding: 10px; width : 5%;"><b>'.$obj_grille->nom.'<b></td><td >'.$obj_grille->commentaire.'</td>';
								print'<td><a class="reposition" href="#">'.$actl[$obj_grille->active].'</a></td>';

							}
								print '</td></tr>';
							$i ++;
							
						}
					}else print '<tr><td colspan="3">Auccune convention disponible!</td></tr>';
			print '</table>';
			print "<hr>";

		}
	}
	//table des champs et labels
	$grilleSql = "SELECT rowid, nom FROM ".MAIN_DB_PREFIX."grille WHERE active=1 AND fk_convention=".$id_convention;
	$grilleResult = $db->query($grilleSql);//= $db->query($grilleSql);
	$obj_grille = $db->fetch_object($grilleResult);
print '<table>';
print '<tr class="liste_titre"><td class="liste_titre" style="padding: 20px; width : 5%;" >Code Categorie</td><td class="liste_titre" style="padding: 20px; width : 5%;" >Echelon</td><td class="liste_titre" style="padding: 20px; width : 5%;" >Salaire de Base</td></tr>';
$covSql = "SELECT rowid, code_categorie FROM ".MAIN_DB_PREFIX."dcategories WHERE fk_convention=".$id_convention;
	$result = $db->query($covSql);//= $db->query($covSql);

	if(!empty($result)){
		$i = 0;
		$num = $db->num_rows($result);
		while ($i < $num){
			$obj = $db->fetch_object($result);

			$echelonSql = "SELECT rowid, libelle FROM ".MAIN_DB_PREFIX."echelon WHERE fk_categorie=".$obj->rowid;
			$echelonResult = $db->query($echelonSql);//= $db->query($covSql);
			$numEch = $db->num_rows($echelonResult);
			if($numEch > 0){
				print '<tr class="pair"><td align="left" rowspan="'.($numEch ? : 1).'" style="padding: 3px; width : 5%;"><b>'.$obj->code_categorie.'<b></td>';
				$j=0;
				while ($j < $numEch){
					$obj_ech = $db->fetch_object($echelonResult);
					$salBaseSql = "SELECT salaire_base FROM ".MAIN_DB_PREFIX."grille_categorie_echelon_salaire_base WHERE fk_categorie=".$obj->rowid." AND fk_grille=".$obj_grille->rowid." AND fk_echelon=".$obj_ech->rowid;
					$salBaseResult = $db->query($salBaseSql);//= $db->query($covSql);
					$objSalBase = $db->fetch_object($salBaseResult);

					if($j==0){
						print '<td style="width : 5%">'.($obj_ech->libelle ? : "N/A");									
						print '</td><td>'.$objSalBase->salaire_base.'</td></tr>';
					}else{
						print '<tr class="pair"><td style="width : 5%">'.($obj_ech->libelle ? : "N/A");									
						print '</td><td>'.$objSalBase->salaire_base.'</td></tr>';
					}
					$j++;
				}
				
			}else{
					$salBaseSql = "SELECT salaire_base FROM ".MAIN_DB_PREFIX."grille_categorie_echelon_salaire_base WHERE fk_grille=".$obj_grille->rowid." AND fk_categorie=".$obj->rowid." AND fk_echelon=0";
						$salBaseResult = $db->query($salBaseSql);//= $db->query($covSql);
						$objSalBase = $db->fetch_object($salBaseResult);
						$n = $db->num_rows($salBaseResult);
						
				print '<tr class="pair"><td align="left" style="padding: 3px; width : 5%;"><b>'.$obj->code_categorie.'<b></td>';
				print '<td style="width : 5%">'.($objEch->libelle ? : "N/A");							
							
				print '</td><td>'.$objSalBase->salaire_base.'</td></tr>';
			}
			$i ++;
		}
		if($num == 0)
            print '<tr><td align="center" colspan="3">Auccun Salaire de Base disponible!</td></tr>';
	}else print '<tr><td align="center" colspan="3">Auccun Salaire de Base disponible!</td></tr>';

	print '</table>';

	}


	if($action == 'create'){	
		print load_fiche_titre($langs->trans("Ajout d'une grille à la convention ".$nom_accord), '', '');
		$head = paiementsalaireAccordHead($id_convention, $id_accord);

		print dol_get_fiche_head($head, 'grille', "", -1, '');		
		//Titre 
		print load_fiche_titre($langs->trans("Veuillez remplir les champs ci-dessous"), '', '');
		print '<div >';
		print '<form name="add"  method="POST" action="'.$_SERVER['PHP_SELF'].'?leftmenu=accord_etablissement&id_accord='.$id_accord.'&id_convention='.$id_convention.'">';
		print '<input type="hidden" name="token" value="'.newToken().'">';
		print '<input type="hidden" name="action" value="add">';
		print '<table>';
	
		print '<tr ><td style="padding-top: 10px; padding-right: 30px" class="fieldrequired"><label>Nom de la grille</label></td>
		
		<td style="padding-top: 10px" id="nom" ><input id="nom_grille" name ="nom_grille" Value="'.(GETPOST("nom_grille", "alpha")?:"").'" size="30" type="text" /></td>';
		print '<tr ><td style="padding-top: 10px; padding-right: 30px" class="fieldrequired"><label>Description</label></td>
		<td style="padding-top: 10px" id="nom" ><textarea id="commentaire" name ="commentaire">'.(GETPOST("commentaire", "alpha")?:"Avec la date de création").'</textarea></td>';
		print "</tr>";
	
		
		print "</tr>";
		PRINT "</table>";
print '<table>';
print '<tr class="liste_titre"><td class="liste_titre" style="padding: 20px; width : 5%;" >Code Categorie</td><td class="liste_titre" style="padding: 20px; width : 5%;" >Echelon</td><td class="liste_titre" style="padding: 20px; width : 5%;" >Salaire de Base</td></tr>';
$covSql = "SELECT rowid, code_categorie FROM ".MAIN_DB_PREFIX."dcategories WHERE fk_convention=".$id_convention;
	$result = $db->query($covSql);//= $db->query($covSql);

	if(!empty($result)){
		$i = 0;
		$num = $db->num_rows($result);
		while ($i < $num){
			$line = array();
			$obj = $db->fetch_object($result);

			$echelonSql = "SELECT rowid, libelle FROM ".MAIN_DB_PREFIX."echelon WHERE fk_categorie=".$obj->rowid;
			$echelonResult = $db->query($echelonSql);//= $db->query($covSql);
			$numEch = $db->num_rows($echelonResult);
			if($numEch > 0){
				print '<tr class="pair"><td align="left" rowspan="'.($numEch ? : 1).'" style="padding: 3px; width : 5%;"><b>'.$obj->code_categorie.'<b></td>';
				$j=0;
				while ($j < $numEch){
					$obj_ech = $db->fetch_object($echelonResult);
					$salBaseSql = "SELECT salaire_base FROM ".MAIN_DB_PREFIX."grille_categorie_echelon_salaire_base WHERE fk_echelon=".$obj_ech->rowid;
					$salBaseResult = $db->query($salBaseSql);//= $db->query($covSql);
					$objSalBase = $db->fetch_object($salBaseResult);

					$nom_sal_base = "sal_base".$obj->rowid."".$j;
					if($j==0){
						print '<td style="width : 5%">'.($obj_ech->libelle ? : "N/A");									
						print '</td><td><input type="text" name="'.$nom_sal_base.'" value="'.(GETPOST($nom_sal_base, "alpha")?:"").'" > </td></tr>';
					}else{
						print '<tr class="pair"><td style="width : 5%">'.($obj_ech->libelle ? : "N/A");									
						print '</td><td><input type="text" name="'.$nom_sal_base.'" value="'.(GETPOST($nom_sal_base, "alpha")?:"").'" > </td></tr>';
					}
					$line[$j] = $nom_sal_base;
					$j++;
				}
				
			}else{	
				$nom_sal_base = "sal_base".$obj->rowid."".$i;					
				print '<tr class="pair"><td align="left" style="padding: 3px; width : 5%;"><b>'.$obj->code_categorie.'<b></td>';
				print '<td style="width : 5%">'.($objEch->libelle ? : "N/A");							
							
				print '</td><td><input type="text" name="'.$nom_sal_base.'" value="'.(GETPOST($nom_sal_base, "alpha")?:"").'" > </td></tr>';
				$line[0] = $nom_sal_base;

			}
			$indice ++;
			$i ++;
		}

		if($num == 0)
            print '<tr><td align="center" colspan="3">Aucune catégorie disponible!</td></tr>';
	}else print '<tr><td align="center" colspan="3">Aucune catégorie disponible!</td></tr>';
	print '<tr><td><br></td><td><br></td></tr>';
	print '<tr><td colspan="2"><input class="button" type="submit" value="Enregistrer" >';
	print '</form>';
	print '<a class="button" href="./grille_salaire_base.php?mainmenu=paiementsalaire&leftmenu=accord_etablissement&id_accord='.$id_accord.'&id_convention='.$id_convention.'" >Annuler</a></td></tr>';
	print '</table>';
	}

	$db->free($result);
	$db->free($echelonResult);

	
if($message != ""){
	$action = 'create';
		
		print "<script>
		$.jnotify('".$message."', {delay : 10000, fadeSpeed: 500});
		</script>";
}
