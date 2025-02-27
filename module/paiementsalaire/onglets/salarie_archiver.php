<?php
require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/custom/paiementsalaire/lib/paiementsalaire.lib.php';


llxHeader("", "Paiement | Salaire");
//Titre 
print load_fiche_titre($langs->trans("Liste des personnelles de la sociétés"), '', '');
//print '<hr>';

$id_societe = GETPOST('id_societe','int');
$id_convention = GETPOST('id_convention','int');
$limit = GETPOST('limit','int')?:20;
$arret = GETPOST('arret','int')?:0;
$nb_page = GETPOST('nbpage','int')?:1;
$action = GETPOST('action', 'alpha');


//Par defaut tous les salariés ont travaillé le maximum de jours du mois en cours
//salarie_nb_jour($db, $id_societe);
//--------------------------------

$head = paiementsalaireSocieteHead($id_societe, $id_convention);
print dol_get_fiche_head($head, 'liste', "", -1, '');

if($user->rights->paiementsalaire->salarie->read){
$soc_sql = "SELECT * FROM ".MAIN_DB_PREFIX."societe WHERE rowid=".$id_societe;
$soc_res = $db->query($soc_sql);//= $db->query($covSql);
$obj_soc = $db->fetch_object($soc_res);
$obj_soc->name = $obj_soc->nom;
$obj_soc->element = "societe";			
$obj_soc->conv = $id_convention;

societe_preview_next($db, $id_societe, $obj_soc);
entete_societe($obj_soc, 'societe');

$head2 = liste_salarie_SocieteHead($id_societe, $id_convention);
print dol_get_fiche_head($head2, 'salarie_archiver', "", -1, '');

$recherche_nom = "";
			$recherche_prenom = "";
			$recherche_matricule = "";
			$recherche_anciennete = 0;
			if($action == "recherche"){
				$recherche_nom = GETPOST("recherche_nom", "alpha");
				$recherche_prenom = GETPOST("recherche_prenom", "alpha");
				$recherche_matricule = GETPOST("recherche_matricule", "alpha");
				$recherche_anciennete = GETPOST("recherche_anciennete", "int");
			}

$trouve = false;
$obj_liste = array();
//$ordre_id = array();

//Desarchivage du salarié
$message = "";
if($action == 'desarchiver'){
	$rowid = GETPOST('fk_salarie', 'int');
	$id = GETPOST('id', 'int');
	if(!empty($rowid)){
		$sql_edit = "UPDATE ".MAIN_DB_PREFIX."salarie SET archiver='non' WHERE rowid = ".$rowid;
		if($db->query($sql_edit)){
			$message = "Salarie desarchiver avec succès";

			$sql_select = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$id;
			$obj_concern = $db->fetch_object($db->query($sql_select));

			$sql_select = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$user->id;
			$obj = $db->fetch_object($db->query($sql_select));

			$action_effectue = "Desarchivage d'un salarié (".$obj_concern->firstname." ".$obj_concern->lastname." id_user=".$id." et fk_salarié=".$rowid.") de la société ".$obj_soc->nom;
			$sql_log = 'INSERT INTO '.MAIN_DB_PREFIX.'log (fk_user, nom, prenom, quand, action_effectue, object_concerne)';
			$sql_log .= ' VALUES('.$user->id.', "'.$obj->lastname.'","'.$obj->firstname.'",now(),"'.$action_effectue.'","Desarchivage")';
			$db->query($sql_log);

		}else $message = "Un problème est survenu";
	}
}

if(!empty(GETPOST("recherche_matricule", "alpha"))){
	
	$sql = "SELECT sal.rowid as salrowid, sal.matricule, sal.fk_user, u.rowid, u.lastname, u.firstname, u.dateemployment, ue.fk_object, ue.egp FROM ".MAIN_DB_PREFIX."salarie as sal";
	$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."user as u ON u.rowid=sal.fk_user";
	$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."user_extrafields as ue ON u.rowid=ue.fk_object";
	$sql .= " WHERE ue.egp=".$id_societe." AND ue.egp=".$id_societe." AND sal.archiver='oui'";
	$sql .= " AND sal.matricule LIKE '%".GETPOST("recherche_matricule", "alpha")."%'";
}else{
	$sql = "SELECT sal.rowid as salrowid, sal.matricule, sal.fk_user, u.rowid, u.lastname, u.firstname, u.dateemployment, ue.fk_object, ue.egp FROM ".MAIN_DB_PREFIX."salarie as sal";
	$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."user as u ON u.rowid=sal.fk_user";
	$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."user_extrafields as ue ON u.rowid=ue.fk_object";
	$sql .= " WHERE ue.egp=".$id_societe." AND ue.egp=".$id_societe." AND sal.archiver='oui'";
	
}

	if(!empty(GETPOST("recherche_nom", "alpha"))){
		$sql .= " AND (u.lastname LIKE '%".GETPOST("recherche_nom", "alpha")."%'";
		$sql .= " OR u.firstname LIKE '%".GETPOST("recherche_nom", "alpha")."%')";
	}
	

	if(!empty(GETPOST("recherche_prenom", "alpha"))){
		$sql .= " AND (u.firstname LIKE '%".GETPOST("recherche_prenom", "alpha")."%'";
		$sql .= " OR u.lastname LIKE '%".GETPOST("recherche_prenom", "alpha")."%')";
	}

	if(GETPOST("recherche_anciennete", "int")!=''){
		$annee = ((int)date("Y")-GETPOST("recherche_anciennete", "int"));
		$mois = (int)date("m");
		$jour = (int)date("d");
		$sql .= " AND YEAR(u.dateemployment)=".$annee." AND MONTH(u.dateemployment)<=".$mois." AND DAY(u.dateemployment)<=".$jour;
	}

	if(!empty(GETPOST("recherche_matricule"))){
		$sql .= " ORDER BY sal.matricule";
	}else
		$sql .= " ORDER BY u.lastname";

	$result = $db->query($sql);
	if($result){
		$num = $db->num_rows($result);
		if($num > 0){
			$a = 0;
			while ($a < $num) {
				$obj_liste[] = $db->fetch_object($result);
				//$ordre_id[] = $obj_liste[count($obj_liste)-1]->rowid;
				$a ++;
			}
			
			
		}else{ 
			$zero = true;
			$num = 1;
		}
		//$db->free($result);

	}

	/*//Mise en ordre du resultat par Matricule
	$array_obj_list_ord = array();
	$array_obj_list_non_ord = array();
	$non_ordre_id = array();
	$ordre_sql = 'SELECT fk_user, matricule FROM '.MAIN_DB_PREFIX.'salarie WHERE fk_user IN (0';
	for ($i=0; $i < count($ordre_id); $i++) {
		$ordre_sql .= ', '.$ordre_id[$i]; 
	}
	$ordre_sql .= ') ORDER BY matricule';
	$res_ordre = $db->query($ordre_sql);
	if($res_ordre){
		$num_ordre = $db->num_rows($res_ordre);
		$ord = 0;
		while($ord < $num_ordre){
			
			$obj_ordre = $db->fetch_object($res_ordre);
			$non_ordre_id[] = $obj_ordre->fk_user;
			for ($i=0; $i < count($obj_liste); $i++) { 
				if($obj_ordre->fk_user == $obj_liste[$i]->rowid)
					$array_obj_list_ord[] = $obj_liste[$i];
			}
			$ord ++;
		}
	}

	for ($i=0; $i < count($obj_liste); $i++) { 
		if(!in_array($obj_liste[$i]->rowid, $non_ordre_id))
			$array_obj_list_non_ord[] = $obj_liste[$i];
	}

	$obj_liste = $array_obj_list_ord;
	for ($i=0; $i < count($array_obj_list_non_ord); $i++) { 
			$obj_liste[count($obj_liste)] = $array_obj_list_non_ord[$i];
	}
*/
		$num = count($obj_liste) == 0 ? 1 : count($obj_liste);
		$sel10 = "";
		$sel25 = "";
		$sel20 = "";
		$sel30 = "";
		$sel50 = "";
		$sel100 = "";
		$sel200 = "";
		$sel500 = "";
		$sel1000 = "";
		$seltout = "";
		if($limit == 5)
			$sel5 = "selected";
		elseif($limit == 10)
			$sel10 = "selected";
		elseif($limit == 15)
			$sel15 = "selected";
		elseif($limit == 20)
			$sel20 = "selected";
		elseif($limit == 30)
			$sel30 = "selected";
		elseif($limit == 50)
			$sel50 = "selected";
		elseif($limit == 100) 
			$sel100 = "selected";
		elseif($limit == 200)
			$sel200 = "selected";
		elseif($limit == 500)
			$sel500 = "selected";
		elseif($limit == 1000)
			$sel1000 = "selected";
		else $seltout = "selected";
		print "<div style='float:right; margin-right:20px;'>";
		print '<form name="ajouter" method="POST" action="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=salarie&id_convention='.$id_convention.'&id_societe='.$id_societe.'">';
		print '<input type="hidden" name="token" value="'.newToken().'">';
		print '<input type="hidden" name="action" value="recherche">';
		print"<select style='padding:10px' name='limit' id='limit' >";
		print "<option value='5' ".$sel5." ><b>5</b></option>
		<option value='10' ".$sel10."><b>10</b></option>
		<option value='15' ".$sel15."><b>15</b></option>
		<option value='20' ".$sel20."><b>20</b></option>
		<option value='30' ".$sel30."><b>30</b></option>
		<option value='50' ".$sel50."><b>50</b></option>
		<option value='100' ".$sel100."><b>100</b></option>
		<option value='200' ".$sel200."><b>200</b></option>
		<option value='500' ".$sel500."><b>500</b></option>
		<option value='1000' ".$sel1000."><b>1000</b></option>
		<option value='tout' ".$seltout."><b>tout</b></option>";
		
		print "</select>";

		if(!empty(GETPOST('limit', 'alpha')))
			$limit = $num;
		
		print "<mark><b>".(GETPOST("nbpage","int")?:1)."</b></mark>/<mark><b>".(((int)($num%$limit))==0?((int)($num/$limit)):((int)($num/$limit)+1))."</b></mark>";
				print '<script type="text/javascript">
				var convention = document.getElementById("limit");
				convention.addEventListener("change", function () {
					var limit = convention.value;
					window.location.href = "'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenusociete&id_societe='.$id_societe.'&id_convention='.$id_convention.'&limit="+limit+"&action=rechercher&recherche_nom='.$recherche_nom.'&recherche_prenom='.$recherche_prenom.'&recherche_matricule='.$recherche_matricule.'&recherche_anciennete='.$recherche_anciennete.'";
				},
				false,
				);
				</script>';
			
			print "</select>";
			print "</div>";

			if($zero)
				$num = 0;
			print "<br>";
			print '<div>';
			print '<table style="width: 100%;" class="tagtable liste">';
			print '<tr  class="liste_titre" >';
			print '<td align="center"style="padding: 5px; width: 20%;">
			<input style="padding:10px" type="text" Placeholder="Nom" value="'.$recherche_nom.'" name="recherche_nom" >
			<br><label>Nom</label></td>';
			print '<td align="center"style="padding: 5px; width: 20%;">
			<input style="padding:10px" type="text" Placeholder="Prenom" value="'.$recherche_prenom.'" name="recherche_prenom" >
			<br><label>Prenom</label></td>';
			print '<td align="center"style="padding: 5px; width: 20%;">
			<input style="padding:10px" type="text" Placeholder="Matricule" value="'.$recherche_matricule.'" name="recherche_matricule" >
			<br><label>Matricule</label></td>';
			print '<td align="center"style="padding: 5px; width: 20%;">
			<input style="padding:10px" type="text" Placeholder="Ancienneté" value="'.$recherche_anciennete.'" name="recherche_anciennete" >
			<br><label>Ancienneté</label></td>';

			print '<td align="center"style="padding: 5px; width: 20%;">';
			print '<input type="submit" class="button" value="Rechercher" >';
			print "</form>";
			print '<br>	<a href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=salarie&id_societe='.$id_societe.'&id_convention='.$id_convention.'&action=annuler_recherche" class="button" >Annuler</a>';


			print '</td></tr>';
			$num = count($obj_liste);
			$adresse = "";
				$i = $arret;
			while ($i < $num){
				$class = "impair";
				if($i%2 == 0)
					$class = "pair";
				print '<tr class="pair">';
				print '<td align="center" >'.$obj_liste[$i]->lastname.'</td>';
				print '<td align="center" >'.$obj_liste[$i]->firstname.'</td>';
				
				print '<td align="center" >'.$obj_liste[$i]->matricule.'</td>';

				//calcul de l'anciennete
				$anciennete = prime_anciennete($db, $obj_liste[$i]->salrowid, $id_convention, date('m'), date('Y'), $obj_liste[$i]->rowid);
				
				print '<td align="center">'.$anciennete[0].'</td>';
				
				print '<td align="center" ><a href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=societe&id_societe='.$id_societe.'&fk_salarie='.$obj_liste[$i]->salrowid.'&id='.$obj_liste[$i]->rowid.'&id_convention='.$id_convention.'&action=desarchiver"><button title="Ce bouton permet de desarchiver ce salarié" class="button">Desarchiver</button></a></td>';

				print '</tr>';    
				if($i!= 0 && (($i+1)%$limit) == 0){
					$arret = $i;
					$i = $num;
				}else
					$i ++;

			}
			if(count($obj_liste) ==0){
				print "<tr><td colspan='6' align='center'><style='align:center;'>Aucun salarié</td></tr>";
			}
		print '</table>';
 		print '</div><br><br>';
		print '<div>';

			print '<span style="float:right; margin-left: 20px;">';
			$nb = (((int)($num%$limit))==0?((int)($num/$limit)):((int)($num/$limit)+1));
	$page_link = "";
	if($num>$limit){

		if($nb_page!= 1)
			if($nb==0 && 1 < ($nb))
				$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=0&nbpage=1&action=recherche&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_matricule=".$recherche_matricule."&recherche_anciennete=".$recherche_anciennete."' style='padding: 5px'><b>Debut</b>    </a>&nbsp;&nbsp;";
			else if(1 < ($nb+1))
			$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=0&nbpage=1&action=recherche&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_matricule=".$recherche_matricule."&recherche_anciennete=".$recherche_anciennete."' style='padding: 5px'><b>Debut</b>    </a>&nbsp;&nbsp;";

		
		if($arret > $limit){

			
			if($nb_page-3>=0)
				$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=".($limit*($nb_page-3))."&nbpage=".($nb_page-2)."&action=recherche&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_matricule=".$recherche_matricule."&recherche_anciennete=".$recherche_anciennete."' style='padding: 5px'><b>".($nb_page -2)."</b></a>&nbsp;&nbsp;";

			if($nb_page-2>=0)
						$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=".($limit*($nb_page-2))."&nbpage=".($nb_page-1)."&action=recherche&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_matricule=".$recherche_matricule."&recherche_anciennete=".$recherche_anciennete."' style='padding: 5px'><b>".($nb_page-1)."</b></a>&nbsp;&nbsp;";
			
			
			if($nb_page-1>=0)
					$page_link .= "<a style='background-color: yellow;' href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=".($limit*($nb_page-1))."&nbpage=".($nb_page)."&action=recherche&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_matricule=".$recherche_matricule."&recherche_anciennete=".$recherche_anciennete."' style='padding: 5px'><b>".($nb_page)."</b></a>&nbsp;&nbsp;";

		

			
				if(	(($nb_page+1) <= ($nb)))
					$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=".($limit*$nb_page)."&nbpage=".($nb_page+1)."&action=recherche&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_matricule=".$recherche_matricule."&recherche_anciennete=".$recherche_anciennete."' style='padding: 5px'><b>".($nb_page + 1)."</b></a>&nbsp;&nbsp;";

			
				if((($nb_page+2) <= ($nb)))
					$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=".($limit*($nb_page +1))."&nbpage=".($nb_page+2)."&action=recherche&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_matricule=".$recherche_matricule."&recherche_anciennete=".$recherche_anciennete."' style='padding: 5px'><b>".($nb_page + 2)."</b></a>&nbsp;&nbsp;";
					
				
				if((($nb_page+3) <= ($nb)))
					$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=".($limit*($nb_page+2))."&nbpage=".($nb_page+3)."&action=recherche&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_matricule=".$recherche_matricule."&recherche_anciennete=".$recherche_anciennete."' style='padding: 5px'><b>".($nb_page + 3)."</b></a>&nbsp;&nbsp;";

					


		}else{

			
				if( 1 <= ($nb))
					
					$page_link .= "<a style='background-color: yellow;' href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=0&nbpage=1&action=recherche&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_matricule=".$recherche_matricule."&recherche_anciennete=".$recherche_anciennete."' style='padding: 5px'><b>1</b></a>&nbsp;&nbsp;";
			
			
				if(2 <= ($nb))
					
					$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=".$limit."&nbpage=2&action=recherche&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_matricule=".$recherche_matricule."&recherche_anciennete=".$recherche_anciennete."' style='padding: 5px'><b>2</b></a>&nbsp;&nbsp;";
			
			
				if(3 <= ($nb))
					
					$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=".($limit*2)."&nbpage=3&action=recherche&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_matricule=".$recherche_matricule."&recherche_anciennete=".$recherche_anciennete."' style='padding: 5px'><b>3</b></a>&nbsp;&nbsp;";
				
				if(4 <= ($nb))
					
					$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=".($limit*3)."&nbpage=4&action=recherche&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_matricule=".$recherche_matricule."&recherche_anciennete=".$recherche_anciennete."' style='padding: 5px'><b>4</b></a>&nbsp;&nbsp;";

				if(5 <= ($nb))
					
					$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=".($limit*4)."&nbpage=5&action=recherche&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_matricule=".$recherche_matricule."&recherche_anciennete=".$recherche_anciennete."' style='padding: 5px'><b>5</b></a>&nbsp;&nbsp;";



		}
		if($nb_page != ($nb)  )
				$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&limit=".$limit."&arret=".($limit*($nb-1))."&nbpage=".($nb)."&action=recherche&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_matricule=".$recherche_matricule."&recherche_anciennete=".$recherche_anciennete."' style='padding: 5px'>      <b>Fin</b></a>&nbsp;&nbsp;";

		
	}
			print $page_link.'</span>';
		if(count($obj_liste)>0)
		 print '<a class="button" target="_blank" href="../doc/liste_personnel.php?id_societe='.$id_societe.'">Generer la liste</a>';
		 print '</div>';

		 if($message != ""){		
			print "<script>
			$.jnotify('".$message."', {delay : 5000, fadeSpeed: 500});
			</script>";
		}

}else{
	print "<h2 style='align:center;'>Vous n'avez pas la permission voir cette liste</h2>";
}

