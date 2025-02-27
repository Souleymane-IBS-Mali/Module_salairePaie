<?php
require '../../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/custom/paiementsalaire/lib/paiementsalaire.lib.php';




llxHeader("", "Paiement | Salaire");
$id_convention = GETPOST("id_convention","int");
$id_accord = GETPOST("id_accord","int");

$covSql = "SELECT * FROM ".MAIN_DB_PREFIX."accord_etablissement WHERE rowid=".$id_accord;
$result = $db->query($covSql);//= $db->query($covSql);
$nom_accord = "";
if($result){
	$obj = $db->fetch_object($result);
	$nom_accord = "<b><mark>".$obj->nom."</mark></b>";
}
$action= GETPOST("action", "alpha") ? : "afficher";

if($action == "add"){
	if(empty(GETPOST('code_categorie', 'int'))) {
		$message = "Le champ 'Code catégorie' est Obligatoire<br>";
	}
	if(empty(GETPOST('nom_categorie', 'alpha'))){
		$message .= $message."Le champ 'Nom catégorie' est Obligatoire<br>";
	} 	


	if(empty($message)){
		$code = GETPOST('code_categorie', 'int');
		$nom = GETPOST('nom_categorie', 'alpha');
		
		$sql = 'INSERT INTO '.MAIN_DB_PREFIX.'dcategories (code_categorie, nom_categorie, fk_convention) VALUES ("'.$code.'","'.$nom.'",'.$id_convention.')';
		$result = $db->query($sql);
		if($result){
			$message = "Catégorie enregistrée avec succès";
			$action = "afficher";
		}else{
			$message = "Un problème est survenu";
			$action = "create";
		}

		
	
	}
	
}

if($action == "afficher"){
$titre = "Catégories de l'accord ".$nom_accord;
print load_fiche_titre($langs->trans($titre), '', '');
$covSql = "SELECT * FROM ".MAIN_DB_PREFIX."convention WHERE rowid=".$id_convention;
$result = $db->query($covSql);
if($result){
	$obj_conv = $db->fetch_object($result);
	if(!empty($obj_conv)){

$head = paiementsalaireAccordHead($id_convention, $id_accord);

print dol_get_fiche_head($head, 'categorie', "", -1, '');

	
	$grilleSql = "SELECT rowid, nom FROM ".MAIN_DB_PREFIX."grille WHERE active=1 AND fk_convention=".$id_convention;
	$grilleResult = $db->query($grilleSql);//= $db->query($grilleSql);
	$obj_grille = $db->fetch_object($grilleResult);
	

//table des champs et labels
print_barre_liste("", $page, $_SERVER["PHP_SELF"], "", "", "", "", "", "", 'bill', 0, dolGetButtonTitle("Créer une Catégorie", '', 'fa fa-plus-circle', $_SERVER['PHP_SELF'].'?action=create&id_convention='.$id_convention.'' , '', 1), '', 0, 0, 0, 1);
print '<table>';
print '<tr class="liste_titre"><td class="liste_titre" style="padding: 20px; width : 5%;" >Code Categorie</td><td class="liste_titre" style="padding: 20px; width : 5%;" >Echelon</td><td class="liste_titre" style="padding: 20px; width : 5%;" >Salaire de Base de la grille : <mark><b>'.$obj_grille->nom.'</b></mark></div></td></tr>';
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
				print '<tr class="pair"><td align="left" rowspan="'.($numEch ? : 1).'" style="padding: 3px; width : 5%;"><a href="./detail.php?idmenu=4399&mainmenu=paiementsalaire&leftmenu=categorie&action=detailcategorie&id_categ='.$obj->rowid.'&id_convention='.$id_convention.'"><b>'.$obj->code_categorie.'<b></a></td>';
				$j=0;
				while ($j < $numEch){
					$obj_ech = $db->fetch_object($echelonResult);
					$salBaseSql = "SELECT salaire_base FROM ".MAIN_DB_PREFIX."grille_categorie_echelon_salaire_base WHERE fk_echelon=".$obj_ech->rowid;
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
						
				print '<tr class="pair"><td align="left" style="padding: 3px; width : 5%;"><a href="./detail.php?idmenu=4399&mainmenu=paiementsalaire&leftmenu=categorie&action=detailcategorie&id_categ='.$obj->rowid.'&id_convention='.$id_convention.'"><b>'.$obj->code_categorie.'<b></A></td>';
				print '<td style="width : 5%">'.($objEch->libelle ? : "N/A");							
							
				print '</td><td>'.$objSalBase->salaire_base.'</td></tr>';
			}
			$i ++;
		}
		$db->free($result);

		if($num == 0)
            print '<tr><td align="center" colspan="3">Auccun Salaire de Base disponible!</td></tr>';
	}else print '<tr><td align="center" colspan="3">Auccun Salaire de Base disponible!</td></tr>';

print '</table></form>';
}else{
	print "<h2> La convention mère n'existe pas</h2>";
}
}
}

if($action == 'create'){	
	print load_fiche_titre($langs->trans("Ajout d'une catégorie à la convention ".$nom_accord), '', '');
$head = paiementsalaireAccordHead($id_convention, $id_accord);

print dol_get_fiche_head($head, 'categorie', "", -1, '');		
	//Titre 
	print load_fiche_titre($langs->trans("Veuillez remplir les champs ci-dessous"), '', '');
	print '<div >';
	print '<form name="add"  method="POST" action="'.$_SERVER['PHP_SELF'].'?id_convention='.$id_convention.'">';
	print '<input type="hidden" name="token" value="'.newToken().'">';
	print '<input type="hidden" name="action" value="add">';
	print '<table>';

	print '<tr ><td style="padding-top: 10px; padding-right: 30px" class="fieldrequired"><label>Code Catégorie</label></td>
	
	<td style="padding-top: 10px" id="nom" ><input id="code_categorie" name ="code_categorie" size="30" type="text" value="'.(GETPOST("code_categorie", "int")?:"").'" /></td>';
	print '<tr ><td style="padding-top: 10px; padding-right: 30px" class="fieldrequired"><label>Nom Catégorie</label></td>
	<td style="padding-top: 10px" id="nom" ><input id="nom_categorie" name ="nom_categorie" value="'.(GETPOST("nom_categorie", "alpha")?:"").'" size="30" type="text" /></td>';
	print "</tr>";

	
	print "</tr>";


	print '<tr><td><br></td><td><br></td></tr>';
	print '<tr><td colspan="2"><input class="button" type="submit" value="Enregistrer" >';
	print '</form>';
	print '<a class="button" href="./convention_categorie.php?mainmenu=paiementsalaire&leftmenu=convention&id_convention='.$id_convention.'" >Annuler</a></td></tr>';
	print '</table></div>';
}



if($message != ""){
	$action = 'create';
		
		print "<script>
		$.jnotify('".$message."', {delay : 5000, fadeSpeed: 500});
		</script>";
}
