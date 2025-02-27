<?php
require '../../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/custom/paiementsalaire/lib/paiementsalaire.lib.php';




llxHeader("", "Paiement | Salaire");
$id_convention = GETPOST("id_convention","int");
$id_convention = GETPOST("id_convention", "int");

$id_accord = GETPOST("id_accord","int");
$id_prime = GETPOST("id_prime","int");

$action = "afficher";
if(!empty(GETPOST("action", "alpha")))
	$action = GETPOST("action", "alpha");

	if(!$id_convention){
		$covSql = "SELECT fk_societe FROM ".MAIN_DB_PREFIX."accord_etablissement WHERE rowid=".$id_accord;
		$result = $db->query($covSql);//= $db->query($covSql);
		$obj = $db->fetch_object($result);
	
		$sql = "SELECT conv FROM ".MAIN_DB_PREFIX."societe_extrafields WHERE fk_object=".$obj->fk_societe." AND grp=1";
		$result = $db->query($sql);
		$obj = $db->fetch_object($result);
	
		$id_convention = $obj->conv;
	
	}
//Titre 
$covSql = "SELECT nom FROM ".MAIN_DB_PREFIX."accord_etablissement WHERE rowid=".$id_accord;
$result = $db->query($covSql);//= $db->query($covSql);
$nom_accord = "";
if($result){
	$obj = $db->fetch_object($result);
	$nom_accord = "<b><mark>".$obj->nom."</mark></b>";
}


$titre = "Primes de l'accord ".$nom_accord;
print load_fiche_titre($langs->trans($titre), '', '');
//print '<hr>';
$covSql = "SELECT * FROM ".MAIN_DB_PREFIX."convention WHERE rowid=".$id_convention;
$result = $db->query($covSql);
if($result){
	$obj_conv = $db->fetch_object($result);
	if(!empty($obj_conv)){
$head = paiementsalaireAccordHead($id_convention, $id_accord);
	print dol_get_fiche_head($head, 'prime', "", -1, '');
	
	$id_prime = GETPOST('id_prime', 'int');
	if(!empty(GETPOST("rowid_id", "int")))
		$rowid = GETPOST("rowid_id", "int");
	
	
		if($action == "add_prime"){
			if(empty(GETPOST('libelle'))) {
				$message = 'Le champ "LIBELLE" est Obligatoire <br>';
			}
			if(empty($message)){
				$libelle = GETPOST('libelle', 'alpha');
				$exonere = GETPOST('exonere');
				$affiche_bulletin = GETPOST('affiche_bulletin', 'alpha');
				$montant = 0;
				$type_i = GETPOST('type', 'alpha');
				$applique = GETPOST('applique', 'int');
				$desc_i = empty(GETPOST('commentaire', 'alpha'))? "" : GETPOST('commentaire', 'alpha');
				$soumis_impot = GETPOST('soumis_impot', 'alpha');
				$soumis_cotisation = GETPOST('soumis_cotisation', 'alpha');	
				
				$sql = 'INSERT INTO '.MAIN_DB_PREFIX.'primes (libelle, type_prime, commentaire, appliquee, exonere, affiche_bulletin, soumis_impot, soumis_cotisation, fk_convention, fk_societe, fk_accord_etablissement, active) VALUES ("'.$libelle.'","'.$type_i.'","'.$desc_i.'",'.$applique.',"'.$exonere.'","'.$affiche_bulletin.'","'.$soumis_impot.'","'.$soumis_cotisation.'",0,0,'.$id_accord.',0)';
				$result = $db->query($sql);
				if($result){
					$message = "Prime Enregistrée avec succès<br><br>Elle doit être configurée dans la liste des Primes";
					$action = 'detail';
					$result = $db->query("SELECT LAST_INSERT_ID() as rowid;");
					$obj = $db->fetch_object($result);
					$id_prime =  $obj->rowid;
				}else{
					$message = "Un problème est survenu !";
								
				}
			}else $action = 'create';
							
		}
			if($action == 'supprimer'){
				$id_prime = GETPOST('id_prime', 'int');
				$sqlDel = "DELETE FROM ".MAIN_DB_PREFIX."primes WHERE rowid=".$id_prime;
				$result = $db->query($sqlDel);
			
				$id_prime = GETPOST('id_prime', 'int');
				$sqlDel = "DELETE FROM ".MAIN_DB_PREFIX."condition_prime WHERE fk_prime=".$id_prime;
				$result = $db->query($sqlDel);
			
				if($result)
					$message = "Prime supprimée avec succès";
				else $message = "Un problème es survenu";
			
				$action = 'afficher';
			}
			
			if($action == 'disable'){
				$id_prime = GETPOST('id_prime', 'int');
			
				
				$sqlEdit = "UPDATE ".MAIN_DB_PREFIX."primes SET active=0 WHERE rowid=".$id_prime;
				$result = $db->query($sqlEdit);
				if($result)
					$message = 'Prime desactivée avec succès';
				else $message = 'Un problème es survenu';
					$action = 'afficher';
				
			}
			if($action == 'activate'){
				$id_prime = GETPOST('id_prime', 'int');
				$sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."condition_prime WHERE fk_prime=".$id_prime;
			
				$res = $db->query($sql);
				$num = $db->num_rows($res);
				if($num>0){
					$sqlEdit = "UPDATE ".MAIN_DB_PREFIX."primes SET active=1 WHERE rowid=".$id_prime;
					$result = $db->query($sqlEdit);
					if($result)
						$message = 'Prime réactivée avec succès';
					else $message = 'Un problème es survenu';
				}else $message = 'Veuillez ajouter au moins un barème pour activer cette prime';
			
				$action = 'afficher';
			
			}
			
			if($action == 'create'){
				print load_fiche_titre($langs->trans("Creation d'une Prime"), '', '');
			print '<hr>';
		
			print '<form name="add_prime"  method="POST" action="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=accord_etablissement&id_accord='.$id_accord.'&id_convention='.$id_convention.'">';
				print '<input type="hidden" name="token" value="'.newToken().'">';
				print '<input type="hidden" name="action" value="add_prime">';
				print '<table>';
				print '<tr ><td style="padding-top: 10px; padding-right: 30px" class="fieldrequired"><label>Libellé</label></td><td style="padding-top: 10px" id="libelle" ><input name ="libelle" value="'.GETPOST("libelle").'" size="30" type="text" /></td>';
			
				print '<tr ><td style="padding-top: 10px; padding-right: 30px" class="fieldrequired"><label>Type</label></td><td style="padding-top: 10px">';
				print '<input type="radio" value="obligatoire" name="type" id="oblig" ><label for="oblig" >Obligatoire&ensp;</label>
				<input type="radio" value="facultative" name="type" id="fac" checked><label for="fac" >Facultative&ensp;</label>';
			
				print '<tr><td style="padding-top: 10px; padding-right: 30px" class="fieldrequired"><label>Commentaire</label></td><td style="padding-top: 10px"><textarea name="commentaire" wrap="soft" cols="50" rows="3">'.GETPOST("commentaire").'</textarea>
				</td>';
				print "<tr ><td style='padding-top: 10px; padding-right: 30px' class='fieldrequired'><label>S'applique au </label></td><td style='padding-top: 10px'>";
				
				print '<select name="applique" id="applique" >;
						<option value="0"></option>
						<option value="1">Salaire de Base</option>
						<option value="2">Salaire de base Imposable</option>
						<option value="3">Montant Fixe</option>
				</td>';
				print '</tr>';	
			
				print '<tr ><td style="padding-top: 10px; padding-right: 30px" class="fieldrequired"><label>Retiré du Sal. Base</label></td><td style="padding-top: 10px">';
				print '<input type="radio" value="oui" name="exonere" id="oui" checked><label for="oui" >Oui&ensp;</label>
					<input type="radio" value="non" name="exonere" id="non" ><label for="non" >Non&ensp;</label></td></tr>';
				
				print '<tr ><td style="padding-top: 10px; padding-right: 30px" class="fieldrequired"><label>Afficher sur bulletin de paye</label></td><td style="padding-top: 10px">';
				
				print '<select name="affiche_bulletin" id="affiche_bulletin" >;
						<option value="Oui" selected >Oui</option>
						<option value="Non">Non</option>
				</td>';
				print '</tr>';	
			
				print '<tr ><td style="padding-top: 10px; padding-right: 30px" class="fieldrequired"><label>Soumis aux Impôts</label></td><td style="padding-top: 10px">';
				
				print '<select name="soumis_impot" id="soumis_impot" >;
						<option value="Oui" selected >Oui</option>
						<option value="Non" >Non</option>
				</td>';
				print '</tr>';	
			
				print '<tr ><td style="padding-top: 10px; padding-right: 30px" class="fieldrequired"><label>Soumis à Cotisation</label></td><td style="padding-top: 10px">';
				
				print '<select name="soumis_cotisation" id="soumis_cotisation" >;
						<option value="Oui" selected >Oui</option>
						<option value="Non" >Non</option>
				</td>';
				print '</tr>';	
			
				print '<tr><td ><br></td></tr>';
				print '<tr><td style=" padding-right: 30px; padding-bottom: 30px"><td style=" padding-bottom: 30px"><input class="button" type="submit" value="Enregistrer" name=""/>';
				print'</form>';
				print '<a href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=accord_etablissement&action=afficher" class="button">Annuler</a></td></tr>';
				print '</table>';
				print '</div>';
			}
			if($action == 'afficher'){
				$acts[0] = "activate";
				$acts[1] = "disable";
				$actl[0] = img_picto($langs->trans("Disabled"), 'switch_off', 'class="size15x"');
				$actl[1] = img_picto($langs->trans("Activated"), 'switch_on', 'class="size15x"');
				$url = $_SERVER["PHP_SELF"];
				//table des champs et labels
				print_barre_liste("", $page, $_SERVER["PHP_SELF"], "", "", "", "", "", "", 'bill', 0, dolGetButtonTitle("Créer une nouvelle Prime", '', 'fa fa-plus-circle', $_SERVER["PHP_SELF"].'?idmenu=4399&mainmenu=paiementsalaire&leftmenu=accord_etablissement&action=create&id_convention='.$id_convention.'&id_accord='.$id_accord , '', 1), '', 0, 0, 0, 1);
			
				print '<table class="tagtable liste" >';
				print '<tr class="liste_titre"><td class="liste_titre" style="padding: 15px; width : 5%;" >Libellé</td>
				<td class="liste_titre" style="padding: 15px; width : 5%;" >Type</td>
				<td class="liste_titre" style="padding: 15px; width : 5%;" >Applique au</td>
				<td class="liste_titre" style="padding: 15px; width : 5%;" >Opération</td></tr>';
				$indSql = "SELECT * FROM ".MAIN_DB_PREFIX."primes WHERE fk_convention=".$id_convention." AND  active=1";
				$result = $db->query($indSql);//= $db->query($covSql);
					
			if($result){
				$i = 0;
				$num = $db->num_rows($result);
				while ($i < $num){
					$obj = $db->fetch_object($result);							
					//si elle est active ou non
					if($obj->active == 1)
						print '<tr class="pair"><td align="left" style="padding: 10px; width : 5%;"><a href="./detail_pr_ind.php?idmenu=4399&mainmenu=paiementsalaire&leftmenu=accord_etablissement&id_convention='.$id_convention.'&id_accord='.$id_accord.'&action=detailprime&id_prime='.$obj->rowid.'"><b>'.$obj->libelle.'<b></a></td>';
					else
						print '<tr class="pair"><td align="left" style="padding: 10px; width : 5%;"><b>'.$obj->libelle.'<b></td>';

					if($obj->type_prime == "obligatoire")					
						print '<td style="width : 5%">Obligatoire</td>';
					else
						print '<td style="width : 5%">Facultative</td>';
					if($obj->appliquee == 1)
						print '<td style="width : 5%">Salaire de Base</td>';
					elseif ($obj->appliquee == 2) 
						print '<td style="width : 5%">Salaire de Base Imposable</td>';
					else
						print '<td style="width : 5%">Montant fixe</td>';
					
				
						
						print '<td>';																			
				print $actl[$obj->active].'&nbsp;';
				print '&nbsp;&nbsp;&nbsp;'.img_edit('Impossible','').'';	
				print '&nbsp;&nbsp;&nbsp;'.img_delete('Impossible','').'&nbsp;';
				print "</td></tr>";
					$i ++;
				}
			}
			$indSql = "SELECT * FROM ".MAIN_DB_PREFIX."primes WHERE fk_accord_etablissement=".$id_accord." ORDER BY active DESC";
				$result = $db->query($indSql);//= $db->query($covSql);
					
			if($result){
				$i = 0;
				$num2 = $db->num_rows($result);
				while ($i < $num2){
					$obj = $db->fetch_object($result);							
					//si elle est active ou non
					if($obj->active == 1)
						print '<tr class="pair"><td align="left" style="padding: 10px; width : 5%;"><a href="./detail_pr_ind.php?idmenu=4399&mainmenu=paiementsalaire&leftmenu=accord_etablissement&id_convention='.$id_convention.'&id_accord='.$id_accord.'&action=detailprime&id_prime='.$obj->rowid.'"><b>'.$obj->libelle.'<b></a></td>';
					else
						print '<tr class="pair"><td align="left" style="padding: 10px; width : 5%;"><b>'.$obj->libelle.'<b></td>';

					if($obj->type_prime == "obligatoire")					
						print '<td style="width : 5%">Obligatoire</td>';
					else
						print '<td style="width : 5%">Facultative</td>';
					if($obj->appliquee == 1)
						print '<td style="width : 5%">Salaire de Base</td>';
					elseif ($obj->appliquee == 2) 
						print '<td style="width : 5%">Salaire de Base Imposable</td>';
					else
						print '<td style="width : 5%">Montant fixe</td>';
					
				
						
					print '<td>';																			
					print'<a class="reposition" href="'.$url.'?mainmenu=paiementsalaire&leftmenu=accord_etablissement&id_accord='.$id_accord.'&id_convention='.$id_convention.'&action='.$acts[$obj->active].'&id_prime='.$obj->rowid.'&token='.newToken().'">'.$actl[$obj->active].'</a>&nbsp;';
					print '&nbsp;&nbsp;&nbsp;<a class="reposition editfielda" href="'.$url.'?mainmenu=paiementsalaire&leftmenu=accord_etablissement&id_accord='.$id_accord.'&id_convention='.$id_convention.'&action=detail&id_prime='.$obj->rowid.'">'.img_edit().'</a>';	
					print '&nbsp;&nbsp;&nbsp;<a class="reposition editfielda" id="delete'.$i.'" onclick="myFunction('.$i.')" href="'.$url.'?mainmenu=paiementsalaire&leftmenu=accord_etablissement&id_accord='.$id_accord.'&id_convention='.$id_convention.'&action=supprimer&id_prime='.$obj->rowid.'">'.img_delete().'</a>&nbsp;';
					print "</td></tr>";
					$i ++;
				}
				if($num == 0 && $num2 == 0) print "<tr><td align='center' colspan='4'>Auccune Prime</td></tr>";

				print "<script>
				function myFunction(e){
				   var b = 'delete'+e;
				   var button_generer = document.getElementById(b);
				   if(!confirm('Click sur OK pour confirmer cette suppression')){
					   var lien = '".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenu=accord_etablissement&id_accord=".$id_accord."&action=afficher';
					   button_generer.setAttribute('href', lien);
				   
				   }
				  }
				
				</script>";
			}else print '<tr><td align="center" colspan="3">Auccune prime Créée!</td></tr>';
		}
			
			print '</table>';
			
			
			
			//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
			//Partie condition prime
			
			if($action == "saveedit_prime"){
				$id_prime = GETPOST('id_prime', 'int');
				$exonere = GETPOST('exonere', 'alpha');
				$soumis_impot = GETPOST('soumis_impot', 'alpha');
				$soumis_cotisation = GETPOST('soumis_cotisation', 'alpha');
				if(empty(GETPOST('libelle', 'alpha'))) {
					$message = 'Le champ "LIBELLE" est Obligatoire <br>';
				}
					if(empty($message)){
						$libelle = GETPOST('libelle', 'alpha');
						$montant = 0;
						$type_i = GETPOST('type', 'alpha');
						$affiche_bulletin = GETPOST('affiche_bulletin', 'alpha');
			
						
						$applique = GETPOST('applique', 'alpha');
							$desc_i = empty(GETPOST('commentaire', 'alpha'))? "" : GETPOST('commentaire', 'alpha');
							$convention = GETPOST('convention', 'int');
							$sql = 'UPDATE '.MAIN_DB_PREFIX.'primes SET libelle="'.$libelle.'", type_prime="'.$type_i.'", commentaire="'.$desc_i.'", appliquee="'.$applique.'", exonere="'.$exonere.'", affiche_bulletin="'.$affiche_bulletin.'", soumis_impot="'.$soumis_impot.'", soumis_cotisation="'.$soumis_cotisation.'" WHERE rowid='.$id_prime;
							$result = $db->query($sql);
					
							if($result){
								$message = "Prime Modifiée avec succès";
								$action = "detail";
							}else{
								$message = "Un problème est survenu !";
								$action = "saveedit_prime";
							}
						
					}else $action = "saveedit_prime";
				
			}
			   
				if($action == 'detail'){
			
					$id = $id_prime;
					$head = Prime_indm_accord_Head($id, 2, $id_convention, $id_accord);
					print dol_get_fiche_head($head, 'identifiant', "", -1, '');
				
					$sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."condition_prime WHERE fk_prime=".$id_prime;
					$res = $db->query($sql);
					$obj_cond = $db->fetch_object($res);
					if(!$obj_cond->rowid && $id_prime!= 1)
						print "<mark><b>Prime ajoutée, veuillez ajouter les conditions d'utilisations dans l'onglet Barèmes.</b></mark>";
			
					$covSql = "SELECT * FROM ".MAIN_DB_PREFIX."primes WHERE rowid=".$id_prime;
					$result = $db->query($covSql);//= $db->query($covSql);
					$obj = $db->fetch_object($result);
					
					print '<div >';
					print '<table>';
					print '<tr ><td style="width: 250px; padding-top: 10px;" class="fieldrequired"><label>Libellé</label></td><td style="width: 150px; padding-top: 10px" id="nom" ><label>'.$obj->libelle.'</label></td>';
				
					print '<tr ><td style="width: 250px; padding-top: 10px;" class="fieldrequired"><label>Type</label></td><td style="width: 150px; padding-top: 10px">';
					$type_pr = "";
					if($obj->type_prime == "obligatoire"){				
						$type_pr = "Obligatoire";
					}else{
						$type_pr = "Facultative";
					}
					print $type_pr.'</td></tr>';
					print '<tr ><td style="width: 250px; padding-top: 10px;" class="fieldrequired"><label>Commentaire</label></td><td style="width: 150px; padding-top: 10px"><textarea name="commentaire" wrap="soft" disabled cols="50" rows="3">'.$obj->commentaire.'</textarea>
					</td></tr>';
					$appl = "";
					if($obj->appliquee == 1)
						$appl = "Salaire de Base";
					elseif ($obj->appliquee == 2) 
						$appl = "Salaire de Base Imposable";
					else
						$appl = "Montant fixe";
					print '<tr ><td style="width: 250px; padding-top: 10px;" class="fieldrequired"><label>S\'applique au</label></td><td style="width: 150px; padding-top: 10px">'.$appl.'</td></tr>';
					
					print '<tr ><td style="width: 250px; padding-top: 10px;" class="fieldrequired"><label>Retiré du Sal. Base</label></td><td style="width: 150px; padding-top: 10px">'.$obj->exonere.'</td></tr>';
			
					if($obj->active == 1)
						$active = "Activé";
					else
						$active = "Desactivé";
			
					print '<tr ><td style="width: 250px; padding-top: 10px;" class="fieldrequired"><label>Statut</label></td><td style="width: 150px; padding-top: 10px">'.$active.'</td></tr>';
			
				
					print '<tr ><td style="width: 250px; padding-top: 10px;" class="fieldrequired"><label>Afficher sur Bulletin</label></td><td style="width: 150px; padding-top: 10px">'.$obj->affiche_bulletin.'</td></tr>';
					print '<tr ><td style="width: 250px; padding-top: 10px;" class="fieldrequired"><label>Soumis aux Impôts</label></td><td style="width: 150px; padding-top: 10px">'.$obj->soumis_impot.'</td></tr>';
					print '<tr ><td style="width: 250px; padding-top: 10px;" class="fieldrequired"><label>Soumis à Cotisation</label></td><td style="width: 150px; padding-top: 10px">'.$obj->soumis_cotisation.'</td></tr>';
					$conv = "Toutes conventions";
					if($obj->fk_societe != 0){
						$sql_accord_etablissement = "SELECT nom FROM ".MAIN_DB_PREFIX."accord_etablissement WHERE rowid=".($obj->fk_societe);
						$result_accord_etablissement = $db->query($sql_accord_etablissement);
						$obj_accord_etablissement = $db->fetch_object($result_accord_etablissement);
						$conv = $obj_accord_etablissement->nom;
						print '<tr ><td style="width: 250px; padding-top: 10px;" class="fieldrequired"><label>Accord établissement</label></td><td style="width: 150px; padding-top: 10px">'.$conv.'</td></tr>';
					}elseif($obj->fk_societe !=0){
						$sql_societe = "SELECT nom FROM ".MAIN_DB_PREFIX."societe WHERE rowid=".($obj->fk_societe);
						$result_societe = $db->query($sql_societe);
						$obj_societe = $db->fetch_object($result_societe);
						$conv = $obj_societe->nom;
						print '<tr ><td style="width: 250px; padding-top: 10px;" class="fieldrequired"><label>Societe</label></td><td style="width: 150px; padding-top: 10px">'.$conv.'</td></tr>';
					}elseif($obj->fk_convention != 0){
						$sql_conv = "SELECT nom FROM ".MAIN_DB_PREFIX."convention WHERE rowid=".($obj->fk_convention);
						$result_conv = $db->query($sql_conv);
						$obj_conv = $db->fetch_object($result_conv);
						$conv = $obj_conv->nom;
						print '<tr ><td style="width: 250px; padding-top: 10px;" class="fieldrequired"><label>Convention</label></td><td style="width: 150px; padding-top: 10px">'.$conv.'</td></tr>';
			
					}
					print '<tr ><td style="width: 250px; padding-top: 10px;" class="fieldrequired"><a class="button" href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=accord_etablissement&id_accord='.$id_accord.'&id_prime='.$obj->rowid.'&action=edit_prime">Modifier</a></td></tr>';
					print "</table>";
			}
			
			//modification indemnité
			
			if($action == 'edit_prime'){
			
				$id = $id_prime;
				$head = Prime_indm_accord_Head($id, 2, $id_convention, $id_accord);
				print dol_get_fiche_head($head, 'identifiant', "", -1, '');
			
				$covSql = "SELECT * FROM ".MAIN_DB_PREFIX."primes WHERE rowid=".$id_prime;
				$result = $db->query($covSql);//= $db->query($covSql);
				$obj = $db->fetch_object($result);
				
				print '<div >';
				print '<form name="add"  method="POST" action="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=accord_etablissement&id_prime='.$obj->rowid.'&id_accord='.$id_accord.'&id_convention='.$id_convention.'">';
				print '<input type="hidden" name="token" value="'.newToken().'">';
				print '<input type="hidden" name="action" value="saveedit_prime">';
				print '<table>';
				print '<tr ><td style="width: 250px; padding-top: 10px;" class="fieldrequired"><label>Libellé</label></td><td style="width: 150px; padding-top: 10px" id="nom" ><input type="text" name="libelle" value="'.$obj->libelle.'"/></td>';
			
				print '<tr ><td style="width: 250px; padding-top: 10px;" class="fieldrequired"><label>Type</label></td><td style="width: 150px; padding-top: 10px">';
				print '';
				$type_pr = "";
				if($obj->type_prime == "obligatoire"){				
					print '<input type="radio" value="obligatoire" name="type" id="oblig" checked><label for="oblig" >Obligatoire&ensp;</label>
				<input type="radio" value="facultative" name="type" id="fac"><label for="fac" >Facultative&ensp;</label>';
				}else{
					print '<input type="radio" value="obligatoire" name="type" id="oblig" ><label for="oblig" >Obligatoire&ensp;</label>
				<input type="radio" value="facultative" name="type" id="fac" checked><label for="fac" >Facultative&ensp;</label>';
				}
				
				print $type_pr.'</td></tr>';
				print '<tr ><td style="width: 250px; padding-top: 10px;" class="fieldrequired"><label>Commentaire</label></td><td style="width: 150px; padding-top: 10px"><textarea name="commentaire" wrap="soft" cols="50" rows="3">'.$obj->commentaire.'</textarea>
				</td></tr>';
				print '<tr ><td style="width: 250px; padding-top: 10px;" class="fieldrequired"><label>S\'applique au</label></td><td style="width: 150px; padding-top: 10px">';
				print '<select name="applique" id="applique" >';
				if($obj->appliquee == 1){
					print'<option value="1" selected>Salaire de Base</option>
					<option value="2">Salaire de base Imposable</option>
					<option value="3">Montant Fixe</option>';
				}elseif ($obj->appliquee == 2){ 
						print '<option value="1">Salaire de Base</option>
						<option value="2" selected>Salaire de base Imposable</option>
						<option value="3">Montant Fixe</option>';
				}else{
					print '<option value="1">Salaire de Base</option>
						<option value="2">Salaire de base Imposable</option>
						<option value="3" selected>Montant Fixe</option>';
				}
				print '</select>';
				print'</td></tr>';
				if($obj->exonere == "oui"){
					print '<tr ><td style="padding-top: 10px; padding-right: 30px" class="fieldrequired"><label>Retiré du Sal. Base</label></td><td style="padding-top: 10px">';
					print '<input type="radio" value="oui" name="exonere" id="oui" checked><label for="oui" >Oui&ensp;</label>
					<input type="radio" value="non" name="exonere" id="non" ><label for="non" >Non&ensp;</label>';
				}else{
					print '<tr ><td style="padding-top: 10px; padding-right: 30px" class="fieldrequired"><label>Retiré du Sal. Base</label></td><td style="padding-top: 10px">';
					print '<input type="radio" value="oui" name="exonere" id="oui"><label for="oui" >Oui&ensp;</label>
					<input type="radio" value="non" name="exonere" id="non" checked><label for="non" >Non&ensp;</label>';
				}
			
				print '<tr ><td style="padding-top: 10px; padding-right: 30px" class="fieldrequired"><label>Afficher sur bulletin de paye</label></td><td style="padding-top: 10px">';
				print '<select name="affiche_bulletin" id="affiche_bulletin" >';
			
				if($obj->affiche_bulletin == "Oui")
					print '<option value="Oui" selected >Oui</option>
							<option value="Non">Non</option>
					</td>';
				else 
					print '<option value="Oui" >Oui</option>
						<option value="Non" selected >Non</option>
					</td>';
				print '</tr>';
			
				print '<tr ><td style="padding-top: 10px; padding-right: 30px" class="fieldrequired"><label>Soumis aux Impôts</label></td><td style="padding-top: 10px">';
				print '<select name="soumis_impot" id="soumis_impot" >';
			
				if($obj->soumis_impot == "Oui")
					print '<option value="Oui" selected >Oui</option>
							<option value="Non">Non</option>
					</td>';
				else 
					print '<option value="Oui" >Oui</option>
						<option value="Non" selected >Non</option>
					</td>';
				print '</tr>';
			
				print '<tr ><td style="padding-top: 10px; padding-right: 30px" class="fieldrequired"><label>Soumis à Cotisation</label></td><td style="padding-top: 10px">';
				print '<select name="soumis_cotisation" id="soumis_cotisation" >';
			
				if($obj->soumis_cotisation == "Oui")
					print '<option value="Oui" selected >Oui</option>
							<option value="Non">Non</option>
					</td>';
				else 
					print '<option value="Oui" >Oui</option>
						<option value="Non" selected >Non</option>
					</td>';
				print '</tr>';
			
				print '<tr ><td style="padding-top: 10px; padding-right: 30px" class="fieldrequired"><label>Convention</label></td><td style="padding-top: 10px">
				<select name="convention" disabled>';
				if($obj->fk_convention == 0)
					print "<option value='0'>Toutes conventions</option>";
				else{
					$sql_conv = "SELECT * FROM ".MAIN_DB_PREFIX."convention WHERE rowid=".$obj->fk_convention;
					$result_conv = $db->query($sql_conv);
					if($result_conv){
						$obj_conv = $db->fetch_object($result_conv);
						print '<option value="'.$obj_conv->rowid.'" selected >'.$obj_conv->nom.'</option>';
						
					}
				}
			
				print '</select></td></tr>';
			
			
				print '<tr><td style=" padding-right: 30px; padding-top: 30px"><td style=" padding-top: 30px"><input class="button" type="submit" value="Enregistrer" name=""/>';
				print'</form>';
				print '<a href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=accord_etablissement&action=detail&id_prime='.$id_prime.'" class="button">Annuler</a></td></tr>';
				print '</table>';
			
			}
			if($action == "ajout_bareme"){
					$categ = GETPOST("categorie");
					$type_salarie = GETPOST("type_salarie");
					$type_montant = GETPOST("type_montant", "alpha");
					$forfait = GETPOST("forfait", "int");
					$pourcentage = GETPOST("pourcentage", "int");
					$inferieur = GETPOST("inferieur","int");
					$superieur = GETPOST("superieur", "int");
					$minimum_perception = GETPOST("minimum_perception", "int");
			
					if($type_montant == "forfait")
						if($forfait == "")
							$message = 'Le champs "FORFAIT" est obligatoire<br>';
			
					if($type_montant == "pourcentage"){
						if($pourcentage == "")
							$message = $message.'Le champs "POURCENTAGE" est obligatoire<br>';
						else if($pourcentage > 60)
							$message = $message.'Le champs "POURCENTAGE" doit être inférieur a 60<br>';
					}
					
			
					$sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."condition_prime (fk_prime";
					$sql_valeur = "";
				if(empty($message)){
					$sql_valeur = "VALUES (".$id_prime."";
						if($type_montant){
							$sql_insert .= ", type_montant";
							$sql_valeur .= ",'".$type_montant."'";
						}
			
						if($forfait && ($type_montant == "forfait")){
							$sql_insert .= ", forfait";
							$sql_valeur .= ",'".$forfait."'";
						}
					
						if($pourcentage && ($type_montant == "pourcentage")){
							$sql_insert .= ", pourcentage";
							$sql_valeur .= ",'".$pourcentage."'";
			
						}
						if($inferieur){
							$sql_insert .= ", inferieur";
							$sql_valeur .= ",'".$inferieur."'";
			
						}
						if($superieur){
							$sql_insert .= ", superieur";
							$sql_valeur .= ",'".$superieur."'";
			
						}
						if($minimum_perception){
							$sql_insert .= ", minimum_perception";
							$sql_valeur .= ",'".$minimum_perception."'";
			
						}
						$sql_insert .= ") ";
						$sql_valeur .= ")";
						
						$sql = $sql_insert."".$sql_valeur;
						$result = $db->query($sql);
						if($result){
							$message = "Condition enregistrée avec succès";
							$action = "liste_condition";
			
						}else{
							$message = "Un problème est survenu";
							$action = "nouvelle_condition_form";
						}
			
			
						$result = $db->query("SELECT LAST_INSERT_ID() as rowid;");
						$obj = $db->fetch_object($result);
						$rowidcondition =  $obj->rowid;
						$type_salarie = GETPOST('type_salaries', 'array');
						if(!empty($type_salarie)){
							for($i =0; $i<count($type_salarie); $i++){
								$type_salarie_i = $type_salarie[$i];
								$sql = "INSERT INTO ".MAIN_DB_PREFIX."condition_type_salarie_prime (fk_condition, fk_type_salarie) VALUES (".$rowidcondition.",".$type_salarie_i.")";
								$result2 = $db->query($sql);
							}
						}else{
							$sql = "INSERT INTO ".MAIN_DB_PREFIX."condition_type_salarie_prime (fk_condition, fk_type_salarie) VALUES (".$rowidcondition.",0)";
							$result2 = $db->query($sql);
			
						}
							$categorie_cond = GETPOST('categories', 'array');
							if(!empty($categorie_cond)){
								for($i =0; $i<count($categorie_cond); $i++){
									$categorie_cond_i = $categorie_cond[$i];
									$sql = "INSERT INTO ".MAIN_DB_PREFIX."condition_categorie_prime (fk_condition, fk_categorie) VALUES (".$rowidcondition.",".$categorie_cond_i.")";
									$result2 = $db->query($sql);
								}
							}else{
								$sql = "INSERT INTO ".MAIN_DB_PREFIX."condition_categorie_prime (fk_condition, fk_categorie) VALUES (".$rowidcondition.",0)";
								$result2 = $db->query($sql);
			
							}
						
						$action = "liste_condition";
					}else {
						$action = "nouvelle_condition_form";
					}
				
			}
			
			if($action == "saveedit_bareme"){
					$categ = GETPOST("categorie");
					$type_salarie = GETPOST("type_salarie");
					$type_montant = GETPOST("type_montant", "alpha");
					$forfait = GETPOST("forfait", "int");
					$pourcentage = GETPOST("pourcentage", "int");
					$inferieur = GETPOST("inferieur","int");
					$superieur = GETPOST("superieur", "int");
					$minimum_perception = GETPOST("minimum_perception", "int");
			
					if($type_montant == "forfait")
						if($forfait == "")
							$message = 'Le champs "FORFAIT" est obligatoire<br>';
			
					if($type_montant == "pourcentage"){
						if($pourcentage == "")
							$message = $message.'Le champs "POURCENTAGE" est obligatoire<br>';
						else if($pourcentage > 60)
							$message = $message.'Le champs "POURCENTAGE" doit être inférieur a 60<br>';
					}
					
					
				if(empty($message)){
				//--------------------
				$rowid = GETPOST("rowid_id", "int");
					$sql_delete = "DELETE FROM ".MAIN_DB_PREFIX."condition_type_salarie_prime WHERE fk_condition=".$rowid;
					$result1 = $db->query($sql_delete);
			
					$sql_delete = "DELETE FROM ".MAIN_DB_PREFIX."condition_categorie_prime WHERE fk_condition=".$rowid;
					$result2 = $db->query($sql_delete);
			
					//$sql_delete = "DELETE FROM ".MAIN_DB_PREFIX."condition_prime WHERE rowid=".$rowid;
					//$result = $db->query($sql_delete);
				//-------------------------------
					$sql_insert = "UPDATE ".MAIN_DB_PREFIX."condition_prime SET fk_prime=".$id_prime;
						if($type_montant){
							$sql_insert .= ", type_montant='".$type_montant."'";
						}
			
						if($forfait && ($type_montant == "forfait")){
							$sql_insert .= ", forfait='".$forfait."'";
						}
					
						if($pourcentage && ($type_montant == "pourcentage")){
							$sql_insert .= ", pourcentage='".$pourcentage."'";
			
						}
						if($inferieur){
							$sql_insert .= ", inferieur='".$inferieur."'";
			
						}
						if($superieur){
							$sql_insert .= ", superieur='".$superieur."'";
			
						}
						if($minimum_perception){
							$sql_insert .= ", minimum_perception='".$minimum_perception."'";
			
						}
						$sql_insert .= " WHERE rowid=".$rowid;
						$result = $db->query($sql_insert);
						if($result){
							$message = "Condition Modifiée avec succès";
							$action = "liste_condition";
			
						}else{
							$action = "nouvelle_condition_form";
						}
			
						$rowidcondition =  $rowid;
						$type_salarie = GETPOST('type_salaries', 'array');
						if(!empty($type_salarie)){
							for($i =0; $i<count($type_salarie); $i++){
								$type_salarie_i = $type_salarie[$i];
								$sql = "INSERT INTO ".MAIN_DB_PREFIX."condition_type_salarie_prime (fk_condition, fk_type_salarie) VALUES (".$rowidcondition.",".$type_salarie_i.")";
								$result2 = $db->query($sql);
							}
						}else{
							$sql = "INSERT INTO ".MAIN_DB_PREFIX."condition_type_salarie_prime (fk_condition, fk_type_salarie) VALUES (".$rowidcondition.",0)";
							$result2 = $db->query($sql);
			
						}
							$categorie_cond = GETPOST('categories', 'array');
							if(!empty($categorie_cond)){
								for($i =0; $i<count($categorie_cond); $i++){
									$categorie_cond_i = $categorie_cond[$i];
									$sql = "INSERT INTO ".MAIN_DB_PREFIX."condition_categorie_prime (fk_condition, fk_categorie) VALUES (".$rowidcondition.",".$categorie_cond_i.")";
									$result2 = $db->query($sql);
								}
							}else{
								$sql = "INSERT INTO ".MAIN_DB_PREFIX."condition_categorie_prime (fk_condition, fk_categorie) VALUES (".$rowidcondition.",0)";
								$result2 = $db->query($sql);
			
							}
						
			
			
			
						$action = "liste_condition";
					}else {
						$action = "edit";
					}
				
				//$message = "save edit";
			}
			
			if($action == 'edit_bareme'){
				if(empty(GETPOST("rowid_id", "int")))
					$rowid = GETPOST("rowid_id", "int");
				$id = $id_prime;
				$head = Prime_indm_accord_Head($id, 2, $id_convention, $id_accord);
				print dol_get_fiche_head($head, 'information', "", -1, '');
			
				$verif_sql = "SELECT * FROM ".MAIN_DB_PREFIX."primes WHERE rowid=".$id_prime;
				$verif_result = $db->query($verif_sql);
				$verif_obj = $db->fetch_object($verif_result);
			
				print '<div >';
				print '<form name="add"  method="POST" action="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=accord_etablissement&id_prime='.$id_prime.'&rowid_id='.$rowid.'&id_convention='.$id_convention.'&id_accord='.$id_accord.'">';
				print '<input type="hidden" name="token" value="'.newToken().'">';
				print '<input type="hidden" name="action" value="saveedit_bareme">';
				print '<table>';
			
				//type salarie
					$condition_type_salarie_prime = "SELECT * FROM ".MAIN_DB_PREFIX."condition_type_salarie_prime WHERE fk_condition=".$rowid;
						$result_condition_type_salarie_prime = $db->query($condition_type_salarie_prime);
						$type_salarie_array = array();
						if($result_condition_type_salarie_prime){
							$j = 0;
							$jum = $db->num_rows($result_condition_type_salarie_prime);
							while($j < $jum){
								$obj_condition_type_salarie_prime_type_sal = $db->fetch_object($result_condition_type_salarie_prime);
								$type_salarie_array[$j] = $obj_condition_type_salarie_prime_type_sal->fk_type_salarie;
							$j ++;
							}
						}
			
					$alltype = array();
					$alltypeRowid = array();
					$alltypeRowid[0] = 0;
					$alltype[0] =  "Tous";
			
					$covSql = "SELECT * FROM ".MAIN_DB_PREFIX."type_salarie";
					$result = $db->query($covSql);//= $db->query($covSql);
					
					if($result){
						$i = 0;
						$num = $db->num_rows($result);
						while ($i < $num){
							$obj = $db->fetch_object($result);
							if ($obj)
							{
								$alltypeRowid[$i+1] = $obj->rowid;
								$alltype[$i+1] =  $obj->libelle;
								/*print $alltypeRowid[$i].",";
								print $alltype[$i].",";*/
								
								
			
							}
							$i ++;
						}
					}
					$alltype = array_combine($alltypeRowid, $alltype);
					$monform = new Form($db);
					print '<tr><td  style="padding-top: 20px; padding-right: 30px" class="fieldrequired"><label>Type salarie</label></td><td style="width: 300px;">';
					print $monform->multiselectarray('type_salaries', $alltype, $type_salarie_array, null, 0, 'quatrevingtpercent widthcentpercentminusx', 0, 0);
					print '</td></tr>';
			
					//categorie
			
					$condition_type_salarie_prime = "SELECT * FROM ".MAIN_DB_PREFIX."condition_categorie_prime WHERE fk_condition=".$rowid;
						$result_condition_categorie_prime = $db->query($condition_type_salarie_prime);
						$categorie_array = array();
						if($result_condition_categorie_prime){
							$j = 0;
							$jum = $db->num_rows($result_condition_categorie_prime);
							while($j < $jum){
								$obj_condition_categorie_prime = $db->fetch_object($result_condition_categorie_prime);
								$categorie_array[$j] = $obj_condition_categorie_prime->fk_categorie;
							$j ++;
							}
						}
			
					$allcategorie = array();
					$allcategorieRowid = array();
					$allcategorieRowid[0] = 0;
					$allcategorie[0] =  "Toutes";
			
					
					$covSql = "SELECT * FROM ".MAIN_DB_PREFIX."dcategories WHERE fk_convention=".$id_convention;
			
					$result = $db->query($covSql);//= $db->query($covSql);
			
					
					if($result){
						$i = 0;
						$num = $db->num_rows($result);
						while ($i < $num){
							$obj = $db->fetch_object($result);
							if ($obj)
							{
			
								$allcategorieRowid[$i+1] = $obj->rowid;
								$allcategorie[$i+1] =  $obj->code_categorie;
								/*print $allcategorieRowid[$i].",";
								print $allcategorie[$i].",";*/
								
								
			
							}
							$i ++;
						}
					}
					$allcategorie = array_combine($allcategorieRowid, $allcategorie);
					$monform = new Form($db);
					print '<tr><td  style="padding-top: 20px; padding-right: 30px" class="fieldrequired"><label>Categories</label></td><td style="width: 300px;">';
					print $monform->multiselectarray('categories', $allcategorie, $categorie_array, null, 0, 'quatrevingtpercent widthcentpercentminusx', 0, 0);
					print '</td></tr>';
			
					//print '<tr><td colspan="2" class="fieldrequired"><hr></td>';
					print '<tr><td class="fieldrequired style="padding-top: 20px; padding-right: 30px""><label>Type montant</label></td>';	
					print '<td style="padding-top: 20px; padding-right: 30px"><label><select name="type_montant" id="type_montant">';
			
					$cond_ind = "SELECT * FROM ".MAIN_DB_PREFIX."condition_prime WHERE rowid=".$rowid;
					$result_ind = $db->query($cond_ind);//= $db->query($cond_ind);
					$condit = $db->fetch_object($result_ind);
					if($condit->type_montant == "forfait"){
						print '<option value="forfait" selected>Forfait</option>';
						print '<option value="pourcentage">Pourcentage</option><option value="flottante" >Flottante</option></td></td></tr>';
					}else if($condit->type_montant == "pourcentage"){
						print '<option value="forfait">Forfait</option>';
						print '<option value="pourcentage" selected>Pourcentage</option><option value="flottante" >Flottante</option></td></td></tr>';
					}else{
						print '<option value="forfait">Forfait</option>';
						print '<option value="pourcentage" selected>Pourcentage</option><option value="flottante" selected>Flottante</option></td></td></tr>';
					}
			
					print '<tr class="pair"><td align="center" colspan="2" style="padding:20px;" class="fieldrequired"><label>Conditions</label></td></tr>';
					print '</table>';
			
					//salaire entre interval
					print '<table id="interval">';
					print '<tr ><td class="impair" style="width: 100px;"><label>Salaire entre</label></td>';
					print '<td class="impair" class="fieldrequired"><input style="border: 1px solid blue;" type="text" name="inferieur" disabled placeholder="min=0"/> et <input style="border: 1px solid blue;" type="text" name="superieur" value='.$condit->superieur.' /></td></tr>';
					print '</table>';
					//POUCENTAGE
					print '<table id="pourcentage">';
					print '<tr ><td class="impair" style="width: 100px;"><label>%Poucentage</label></td>';
					print '<td class="impair" class="fieldrequired"><input style="border: 1px solid blue;" type="text" name="pourcentage" value='.$condit->pourcentage.' /></td></tr>';
					print '</table>';
					//Forfait
					print '<table id="forfait">';
					print '<tr ><td class="impair" style="width: 100px;"><label>Montant</label></td>';
					print '<td class="impair" class="fieldrequired"><input style="border: 1px solid blue;" type="text" name="forfait" value='.$condit->forfait.' /></td></tr>';
					print '</table>';
					//Minimum de perception
					print '<table id="min_percu">';
					print '<tr ><td class="impair" style="width: 100px;"><label>Minimum de perception</label></td>';
					print '<td class="impair" class="fieldrequired"><input style="border: 1px solid blue;" type="text" name="minimum_perception" value='.$condit->minimum_perception.' /></td></tr>';
					print '</table>';
			
					
					print '<table>';
				print '<tr><td ><br></td></tr>';
				print '<tr><td style=" padding-right: 30px; padding-bottom: 30px"><td style=" padding-bottom: 30px"><input class="button" type="submit" value="Enregistrer" name=""/>';
				print'</form>';
				print '<a href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=accord_etablissement&action=liste_condition&id_prime='.$id_prime.'" class="button">Annuler</a></td></tr>';
				print '</table>';
				
			
			}
			
			if($action == 'supprimer_bareme'){
				$rowid = GETPOST("rowid_id", "int");
				$sql_delete = "DELETE FROM ".MAIN_DB_PREFIX."condition_type_salarie_prime WHERE fk_condition=".$rowid;
				$result1 = $db->query($sql_delete);
			
				$sql_delete = "DELETE FROM ".MAIN_DB_PREFIX."condition_categorie_prime WHERE fk_condition=".$rowid;
				$result2 = $db->query($sql_delete);
			
				$sql_delete = "DELETE FROM ".MAIN_DB_PREFIX."condition_prime WHERE rowid=".$rowid;
				$result = $db->query($sql_delete);
				if($result && $result1 && $result2)
					$message = "Condition supprimée avec succès";
				else $message = "Un problème est survenu";

				$sql_verif = "SELECT rowid FROM ".MAIN_DB_PREFIX."condition_prime WHERE rowid=".$rowid;
			$result = $db->query($sql_verif);
			if(empty($db->fetch_object($result))){
				$sqlEdit = "UPDATE ".MAIN_DB_PREFIX."prime SET active=0 WHERE rowid=".$rowid;
				$result = $db->query($sqlEdit);
			}

				$action = "liste_condition";
			}
			
			
			if($action == 'nouvelle_condition_form'){
				$id = $id_prime;
				$head = Prime_indm_accord_Head($id, 2, $id_convention, $id_accord);
				print dol_get_fiche_head($head, 'information', "", -1, '');
			
				$verif_sql = "SELECT * FROM ".MAIN_DB_PREFIX."primes WHERE rowid=".$id_prime;
				$verif_result = $db->query($verif_sql);
				$verif_obj = $db->fetch_object($verif_result);	
						print '<div >';
						print '<form name="add"  method="POST" action="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=accord_etablissement&id_prime='.$id_prime.'&id_convention='.$id_convention.'&id_accord='.$id_accord.'">';
						print '<input type="hidden" name="token" value="'.newToken().'">';
						print '<input type="hidden" name="action" value="ajout_bareme">';
						print '<table>';	
						$alltype = array();
						$alltypeRowid = array();
			
						$covSql = "SELECT * FROM ".MAIN_DB_PREFIX."type_salarie";
						$result = $db->query($covSql);//= $db->query($covSql);
			
						$alltypeRowid[0] = 0;
						$alltype[0] =  "Tout";
						if($result){
							$i = 0;
							$num = $db->num_rows($result);
							while ($i < $num){
								$obj = $db->fetch_object($result);
								if ($obj)
								{
									$alltypeRowid[$i+1] = $obj->rowid;
									$alltype[$i+1] =  $obj->libelle;
									/*print $alltypeRowid[$i].",";
									print $alltype[$i].",";*/
									
									
			
								}
								$i ++;
							}
						}
						$alltype = array_combine($alltypeRowid, $alltype);
						$monform = new Form($db);
						print '<tr><td  style="padding-top: 20px; padding-right: 30px" class="fieldrequired"><label>Type salarie</label></td><td style="width: 300px;">';
						print $monform->multiselectarray('type_salaries', $alltype, GETPOST('prime_salarie', 'array'), null, 0, 'quatrevingtpercent widthcentpercentminusx', 0, 0);
						print '</td></tr>';
			
						$allcategorie = array();
						$allcategorieRowid = array();
						$allcategorieRowid[0] = 0;
						$allcategorie[0] =  "Toutes";
			
						
						$covSql = "SELECT * FROM ".MAIN_DB_PREFIX."dcategories WHERE fk_convention=".$id_convention;
					$result = $db->query($covSql);//= $db->query($covSql);
			
						$id = 0;
						$nom = "";
						if($result){
							$i = 0;
							$num = $db->num_rows($result);
							while ($i < $num){
								$obj = $db->fetch_object($result);
								if ($obj)
								{
									
				
									$allcategorieRowid[$i+1] = $obj->rowid;
									$allcategorie[$i+1] =  $obj->code_categorie;
									/*print $allcategorieRowid[$i].",";
									print $allcategorie[$i].",";*/
									
									
			
								}
								$i ++;
							}
						}
						$allcategorie = array_combine($allcategorieRowid, $allcategorie);
						$monform = new Form($db);
						print '<tr><td  style="padding-top: 20px; padding-right: 30px" class="fieldrequired"><label>Categories</label></td><td style="width: 300px;">';
						print $monform->multiselectarray('categories', $allcategorie, GETPOST('categorie', 'array'), null, 0, 'quatrevingtpercent widthcentpercentminusx', 0, 0);
						print '</td></tr>';
					
					//print '<tr><td colspan="2" class="fieldrequired"><hr></td>';
					print '<tr><td class="fieldrequired" style="padding-top: 20px; padding-right: 30px"><label>Type montant</label></td>';	
					print '<td style="padding-top: 20px; padding-right: 30px"><label><select name="type_montant" id="type_montant"> <option value="forfait" selected>Forfait</option>
					<option value="pourcentage">Pourcentage</option>
					<option value="flottante">Flottante</option></td></tr>';
			
					print '<tr class="pair"><td align="center" colspan="2" style="padding:20px;" class="fieldrequired"><label>Conditions</label></td></tr>';
					print '</table>';
			
					//salaire entre interval
					print '<table id="interval">';
					print '<tr ><td class="impair" style="width: 100px;"><label>Salaire entre</label></td>';
					print '<td class="impair" class="fieldrequired"><input style="border: 1px solid blue;" type="text" name="inferieur" disabled placeholder="min=0"/> et <input style="border: 1px solid blue;" type="text" name="superieur" placeholder="&#8734;"/></td></tr>';
					print '</table>';
					//POUCENTAGE
					print '<table id="pourcentage">';
					print '<tr ><td class="impair" style="width: 100px;"><label>%Poucentage</label></td>';
					print '<td class="impair" class="fieldrequired"><input style="border: 1px solid blue;" type="text" name="pourcentage" placeholder="%"/></td></tr>';
					print '</table>';
					//Forfait
					print '<table id="forfait">';
					print '<tr ><td class="impair" style="width: 100px;"><label>Montant</label></td>';
					print '<td class="impair" class="fieldrequired"><input style="border: 1px solid blue;" type="text" name="forfait" placeholder="000"/></td></tr>';
					print '</table>';
					
					//Minimum de perception
					print '<table id="min_percu">';
					print '<tr ><td class="impair" style="width: 100px;"><label>Minimum de perception</label></td>';
					print '<td class="impair" class="fieldrequired"><input style="border: 1px solid blue;" type="text" name="minimum_perception" placeholder="000"/></td></tr>';
					print '</table>';
			
					print '<table>';
				print '<tr><td ><br></td></tr>';
				print '<tr><td style=" padding-right: 30px; padding-bottom: 30px"><td style=" padding-bottom: 30px"><input class="button" type="submit" value="Enregistrer" name=""/>';
				print'</form>';
				print '<a href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=accord_etablissement&action=liste_condition&id_prime='.$id_prime.'" class="button">Annuler</a></td></tr>';
				print '</table>';
					print '</div>';
				
			}
			
			if($action == "liste_condition"){
				print_barre_liste("", $page, $_SERVER["PHP_SELF"], "", "", "", "", "", "", 'bill', 0, dolGetButtonTitle("Créer une nouvelle condition", '', 'fa fa-plus-circle', $_SERVER["PHP_SELF"].'?idmenu=4399&mainmenu=paiementsalaire&leftmenu=accord_etablissement&action=nouvelle_condition_form&id_accord='.$id_accord.'&id_convention='.$id_convention.'&id_prime='.$id_prime , '', 1), '', 0, 0, 0, 1);
				$id = $id_prime;
				$head = Prime_indm_accord_Head($id, 2, $id_convention, $id_accord);
				print dol_get_fiche_head($head, 'information', "", -1, '');
			
						print "<table>";
						print "<tr class='pair'>";
						print "<td align='center' style='width: 40px; padding:20px;'>N°</td>";
						print "<td align='center' style='width: 130px; padding:20px;'>S'applique au</td>";
						print "<td align='center' style='width: 130px; padding:20px;'>Type Salarié</td>";
						print "<td align='center' style='width: 130px; padding:20px;'>Catégorie</td>";
						print "<td align='center' style='width: 130px; padding:20px;'>Conditions</td>";
						print "<td align='center' style='width: 130px; padding:20px;'>Min Peçu</td>";
						print "<td align='center' style='width: 130px; padding:20px;'>Montant</td>";
						print "<td align='center' style='width: 130px; padding:20px;'>Operation</td>";
						print "<tr>";
						$cond_ind = "SELECT * FROM ".MAIN_DB_PREFIX."condition_prime WHERE fk_prime=".$id_prime;
						$result_ind = $db->query($cond_ind);//= $db->query($cond_ind);
						$num = $db->num_rows($result_ind);
						$i = 0;
						$numero = 1;
						if($result_ind)
						while ($i < $num) {
								//information de base d'indemnité
								$covSql = "SELECT * FROM ".MAIN_DB_PREFIX."primes WHERE rowid=".$id_prime;
								$result = $db->query($covSql);//= $db->query($covSql);
								$obj = $db->fetch_object($result);
								$appl = "";
								if($obj->appliquee == 1)
									$appl = "Salaire de Base";
								elseif ($obj->appliquee == 2) 
									$appl = "Salaire de Base Imposable";
								else
									$appl = "Montant fixe";
							
							$obj_ind = $db->fetch_object($result_ind);
			
				//Type Salarié
				//------------------------------------------------------------------------------------------------------------------------------------------
						$condition_type_salarie_prime = "SELECT * FROM ".MAIN_DB_PREFIX."condition_type_salarie_prime WHERE fk_condition=".$obj_ind->rowid;
						$result_condition_type_salarie_prime = $db->query($condition_type_salarie_prime);
						$type_salarie = "";
						if($result_condition_type_salarie_prime){
							$j = 0;
							$jum = $db->num_rows($result_condition_type_salarie_prime);
							while($j < $jum){
								$obj_condition_type_salarie_prime_type_sal = $db->fetch_object($result_condition_type_salarie_prime);
							
				//------------------------------------------------------------------------------------------------------------------------------------------
								if($obj_condition_type_salarie_prime_type_sal->fk_type_salarie == 0){
									$type_salarie = "Toutes";
								}else{
									$sql_type_s = "SELECT * FROM ".MAIN_DB_PREFIX."type_salarie WHERE rowid=".$obj_condition_type_salarie_prime_type_sal->fk_type_salarie;
									$result_typ_sal = $db->query($sql_type_s);//= $db->query($type_s);
									$obj_type_sal = $db->fetch_object($result_typ_sal);
									$type_salarie = $type_salarie."".$obj_type_sal->libelle."<br>";
								}
								$j ++;
			
							}
						}
			
							//Recupération de la catégorie concerné
							$condition_categorie_prime = "SELECT * FROM ".MAIN_DB_PREFIX."condition_categorie_prime WHERE fk_condition=".$obj_ind->rowid;
							$result_condition_categorie_prime = $db->query($condition_categorie_prime);
							$categorie = "";
							if($result_condition_categorie_prime){
								$j = 0;
								$jum = $db->num_rows($result_condition_categorie_prime);
								while($j < $jum){
									$obj_condition_categorie_prime = $db->fetch_object($result_condition_categorie_prime);
					//------------------------------------------------------------------------------------------------------------------------------------------
									if($obj_condition_categorie_prime->fk_categorie == 0){
										$categorie = "Toutes";
									}else{
										$sql_type_s = "SELECT * FROM ".MAIN_DB_PREFIX."dcategories WHERE rowid=".$obj_condition_categorie_prime->fk_categorie;
										$result_typ_sal = $db->query($sql_type_s);//= $db->query($type_s);
										$obj_type_sal = $db->fetch_object($result_typ_sal);
										$categorie = $categorie."".$obj_type_sal->code_categorie."<br>";
									}
									$j ++;
			
								}
							}
			
							if($obj_ind->fk_categorie > 0){
								$sql_categ = "SELECT * FROM ".MAIN_DB_PREFIX."dcategories WHERE rowid=".$obj_ind->fk_categorie;
								$result_categ = $db->query($sql_categ);//= $db->query($type_s);
								$obj_categ = $db->fetch_object($result_categ);
								$categorie = $obj_categ->code_categorie;
							}
			
							print "<tr class='impair'>";
							print "<td align='center' style='width: 40px; padding:10px;'>".$numero."</td>";
							print "<td align='center' style='width: 150px; padding:10px;'>".$appl."</td>";
							print "<td align='center' style='width: 150px; padding:10px;'>".$type_salarie."</td>";
							print "<td align='center' style='width: 150px; padding:10px;'>".$categorie."</td>";
							$conditions = "";
							if($obj_ind->type_condition == "forfait"){
								$conditions = $obj_ind->montant;
							}else if($obj_ind->type_condition == "pourcentage"){
								$conditions = $obj_ind->pourcentage;
							}else{
								if($obj_ind->superieur == 0)
									$conditions = "".$obj_ind->inferieur." à &#8734;";
								else $conditions = "".$obj_ind->inferieur." à ".$obj_ind->superieur;
							}
							print "<td align='center' style='width: 150px; padding:10px;'>".$conditions."</td>";
							$min = "N/A";
							if($obj_ind->minimum_perception != 0)
								$min = $obj_ind->minimum_perception;
							print "<td align='center' style='width: 130px; padding:20px;'>".$min."</td>";
			
							if($obj_ind->type_montant == "forfait"){
								print "<td align='center' style='width: 150px; padding:10px;'>".$obj_ind->forfait."</td>";
							}else print "<td align='center' style='width: 150px; padding:10px;'>".$obj_ind->pourcentage."%</td>";
			
							print '<td align="center"><a class="reposition editfielda" href="'.$_SERVER["PHP_SELF"].'?action=supprimer_bareme&rowid_id='.$obj_ind->rowid.'&id_prime='.$obj->rowid.'&id_accord='.$id_accord.'&id_convention'.$id_convention.'">'.img_delete('', '').'&nbsp;</a>';								
							print '<a class="reposition editfielda" href="'.$_SERVER["PHP_SELF"].'?action=edit_bareme&id_accord='.$id_accord.'&id_convention'.$id_convention.'&rowid_id='.$obj_ind->rowid.'&id_prime='.$obj->rowid.'">'.img_edit('', '').'</a></td>';								
							print "<tr>";
							$numero ++;
							$i ++;
					}
					if($num == 0) print "<tr><td align='center' colspan='8'>Aucun barème disponible cette prime.<br>
					Veuillez cliquer sur plus(+) pour ajouter un nouveau barème</td></tr>";			
			
			}
			$db->free();
			
			//if(!empty($message))
			print "<script>
			
			var type_montant = document.getElementById('type_montant');
			const pourcentage = document.getElementById('pourcentage');
			const forfait = document.getElementById('forfait');
			const interval = document.getElementById('interval');
			const min_percu = document.getElementById('min_percu');
				
				
				if(type_montant.value == 'forfait'){
					forfait.style.display = 'block';
					pourcentage.style.display = 'none';
					min_percu.style.display = 'none';
					interval.style.display = 'block';
					
				}
				if(type_montant.value == 'pourcentage'){
					forfait.style.display = 'none';
					interval.style.display = 'block';
				}
				if(type_montant.value == 'flottante'){
					forfait.style.display = 'none';
					pourcentage.style.display = 'none';
					min_percu.style.display = 'none';
					interval.style.display = 'none';
				}
			
			type_montant.addEventListener('change',typeApplique);
			function typeApplique(){
				if(type_montant.value == 'pourcentage'){
					pourcentage.style.display = 'block';
					min_percu.style.display = 'block';
					forfait.style.display = 'none';
					interval.style.display = 'block';
				}else if(type_montant.value == 'forfait'){ 
					forfait.style.display = 'block';
					pourcentage.style.display = 'none';
					min_percu.style.display = 'none';
					interval.style.display = 'block';
			
				}else{
					
					pourcentage.style.display = 'none';
					min_percu.style.display = 'none';
					forfait.style.display = 'none';
					interval.style.display = 'none';
			
				}
			
				if(type_montant.value == 'forfait'){
					forfait.style.display = 'block';
					pourcentage.style.display = 'none';
					
				}else if(type_montant.value == 'pourcentage'){ 
					forfait.style.display = 'none';
					
				}else{
					forfait.style.display = 'none';
					pourcentage.style.display = 'none';
					interval.style.display = 'none';
			
				}
			}
			
			</script>";
			//-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
			if(!empty($message))
				print "<script>
				$.jnotify('".$message."', {delay : 5000, fadeSpeed: 500});
				</script>";
			}else{
				print "<h2> La convention mère n'existe pas</h2>";
			}
		}