<?php
require '../../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/custom/paiementsalaire/lib/paiementsalaire.lib.php';




llxHeader("", "Paiement | Salaire");
//Titre 
$id_convention = GETPOST("id_convention","int");

$covSql = "SELECT * FROM ".MAIN_DB_PREFIX."convention WHERE rowid=".$id_convention;
$result = $db->query($covSql);//= $db->query($covSql);
$nom_convention = "";
if($result){
	$obj = $db->fetch_object($result);
	$nom_convention = "<b><mark>".$obj->nom."</mark></b>";
}

$titre = "Information sur la Convention ".$nom_convention;
print load_fiche_titre($langs->trans($titre), '', '');

$head = paiementsalaireConventionHead($id_convention);
print dol_get_fiche_head($head, 'information', "", -1, '');


print "<table>";

print '<tr ><td style="width: 150px; padding-top: 10px;" class="fieldrequired"><label>Nom</label></td><td style="width: 150px; padding-top: 10px" id="nom" ><label>'.$obj->nom.'</label></td></tr>';
print '<tr ><td style="width: 150px; padding-top: 10px;" class="fieldrequired"><label>Commentaire</label></td><td style="width: 150px; padding-top: 10px"><textarea name="commentaire" wrap="soft" disabled cols="50" rows="3">'.$obj->commentaire.'</textarea></td></tr>';

if($id_convention < 7)
	print "<tr><td td style='width: 150px; padding-top: 10px;' class='fieldrequired'>Fichier</td><td><a title='Télécharger le fichier de la convention ".$obj->nom."' target='_blank' href='".$_SERVER["PHP_SELF"]."/../../documents/".$obj->nom.".pdf'>".img_picto('', 'title_document', 'class="paddingright pictofixedwidth valignmiddle"')."</a></td></tr>";
else 
	print "<tr><td td style='width: 150px; padding-top: 10px;' class='fieldrequired'>Fichier</td><td><a title='Télécharger le fichier de la convention ".$obj->nom."' target='_blank' href='".$_SERVER["PHP_SELF"]."/../../../".$obj->document_convention."'>".img_picto('', 'title_document', 'class="paddingright pictofixedwidth valignmiddle"')."</a></td></tr>";

print "</table>";
$db->free($result);

$sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."grille WHERE fk_convention=".$id_convention;
$res = $db->query($sql);
if($res){
  $nb = $db->num_rows($res);
  if($id_convention ==1 && $nb == 0){
	//Mine 
	//id_conv = 1; id_grille = 1; id_categ = [1-33](30categ)
	$sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'grille (fk_convention, nom, commentaire, active) VALUES ('.$id_convention.',"Grille Actuelle","Covention Collective des Sociétés et Entreprises Minières, Geologiques et Hydrogeologiques",1)';
	$db->query($sql_insert);

	$code_categorie = array("HC1","HC2","A10","A9","A8","A7","A6","A5","A4","A3","A2","A1","B7","B6","B5","B4","B3","B2","B1","C6","C5","C4","C3","C2","C1","D5","D4","D3","D2","D1","E3","E2","E1");
	$lim = count($code_categorie);
	for ($i=0; $i< $lim; $i++){
		$sql = 'INSERT INTO '.MAIN_DB_PREFIX.'dcategories (code_categorie, nom_categorie, fk_convention) VALUES ("'.$code_categorie[$i].'","'.$code_categorie[$i].'",'.$id_convention.')';
		$db->query($sql);
	}

	$salaire_base = array("630474","618659","616821","581377","569562","510489","475045","424010","372314","342680","309930","276906","361211","337582","298083","268232","238513","222670","179969","233735","216013","196199","177284","159271","141259","168179","153364","135351","125068","110056","87989","81259","74815");
	$lim = count($salaire_base);
	for ($i=0; $i< $lim; $i++){
		$sql = 'INSERT INTO '.MAIN_DB_PREFIX.'grille_categorie_echelon_salaire_base (fk_grille, fk_categorie, fk_echelon, salaire_base) VALUES (1,'.($i+1).',0,"'.$salaire_base[$i].'")';
		$db->query($sql);
	}
  }

//---------------------------------Banque, Assurance et Finance
//id_conv = 2; id_grille = 2; id_categ = [34-77](44categ)
if($id_convention == 2 && $nb == 0){

	$sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'grille (fk_convention, nom, commentaire, active) VALUES ('.$id_convention.',"Grille Actuelle 01-01-2021","Convention Collective des Banques, Assurances et Etablissements Financiers du Mali",0)';
	$db->query($sql_insert);
	//----grille 1 des banques
	$code_categorie = array("CATEGORIE 1","CATEGORIE 2","CATEGORIE 3","CATEGORIE 4","CATEGORIE 5","CATEGORIE 6","CATEGORIE 7A","CATEGORIE 7B","CATEGORIE 7C","CATEGORIE 7D","CATEGORIE 7E","CATEGORIE 7F","CATEGORIE 7G"
	,"CLASSE 1A","CLASSE 1B","CLASSE 2A","CLASSE 2B","CLASSE 3A","CLASSE 3B","CLASSE 4A","CLASSE 4B","CLASSE 4C","CLASSE 5A","CLASSE 5B","CLASSE 5C","CLASSE 6A","CLASSE 6B","CLASSE 6C","CLASSE 6D","CLASSE 7A","CLASSE 7B"
	,"CLASSE 7C","CLASSE 7D","CLASSE 8A","CLASSE 8B","CLASSE 8C","CLASSE 8D","CLASSE 8E","CLASSE 8F","CLASSE 8G","HORS CLASSE A","HORS CLASSE B","HORS CLASSE C","HORS CLASSE D");
	$lim = count($code_categorie);
	for ($i=0; $i< $lim; $i++){
		$sql = 'INSERT INTO '.MAIN_DB_PREFIX.'dcategories (code_categorie, nom_categorie, fk_convention) VALUES ("'.$code_categorie[$i].'","'.$code_categorie[$i].'",'.$id_convention.')';
		$db->query($sql);
	}

	$salaire_base = array("69826","76832","82749","91484","100218","108944","116646","141076","165870","192438","219306","246178","272446","219306","246178","272746","284753","299037","323551","348063","372577","397085","406860"
	,"430509","438096","448131","470538","486668","502350","518934","533725","551195","567334","58346","99594","615731","631867","647996","663677","680260","696391","712522","738078","776408");
	$lim = count($salaire_base);
	for ($i=0; $i< $lim; $i++){
		$sql = 'INSERT INTO '.MAIN_DB_PREFIX.'grille_categorie_echelon_salaire_base (fk_grille, fk_categorie, fk_echelon, salaire_base) VALUES (2,'.(33+$i+1).',0,"'.$salaire_base[$i].'")';
		$db->query($sql);
	}

	$sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'grille (fk_convention, nom, commentaire, active) VALUES ('.$id_convention.',"Grille Actuelle 01-01-2023","Convention Collective des Banques, Assurances et Etablissements Financiers du Mali",1)';
	$db->query($sql_insert);
	//----grille 2 des banques
	//id_conv = 2; id_grille = 3; id_categ = [31-74](les catégorie ne change pas car c'est la même convention)
	$salaire_base = array("73318","80674","86887","96058","105229","114391","122479","148129","174164","202060","230271","258487","286383","230271","258487","286383","298991","313989","339728","365466","391206","416939","427203"
	,"452034","460001","470537","494065","511001","527467","544881","560411","578755","595701","612641","629574","646517","663460","680395","696861","714273","731210","748149","774982","815228");
	$lim = count($salaire_base);
	for ($i=0; $i< $lim; $i++){
		$sql = 'INSERT INTO '.MAIN_DB_PREFIX.'grille_categorie_echelon_salaire_base (fk_grille, fk_categorie, fk_echelon, salaire_base) VALUES (3,'.(33+$i+1).',0,"'.$salaire_base[$i].'")';
		$db->query($sql);
	}
}
if($id_convention ==3 && $nb == 0){
//----------------------------------------------Commerce
//id_conv = 3; id_grille = 4; id_categ = [78-88](11 categ)(les catégorie ne change pas car c'est la même convention)
	$sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'grille (fk_convention, nom, commentaire, active) VALUES ('.$id_convention.',"Grille Actuelle","Convention Collective du Commerce",1)';
	$db->query($sql_insert);

	$code_categorie = array("1ERE CATEG","2EME CATEG","3EME CATEG","4EME CATEG","5EME CATEG","6EME CATEG","7EME CATEG","8EME CATEG","9EME CATEG","10EME CATEG","11EME CATEG");
	$lim = count($code_categorie);
	for ($i=0; $i< $lim; $i++){
		$sql = 'INSERT INTO '.MAIN_DB_PREFIX.'dcategories (code_categorie, nom_categorie, fk_convention) VALUES ("'.$code_categorie[$i].'","'.$code_categorie[$i].'",'.$id_convention.')';
		$db->query($sql);
	}
	//id_echelon = [1-12] (on a 12 echelons)
	$libelle_echelon = array("ECH-A","ECH-B"); //les 2 premiers liés a categ 78
	for($i = 0; $i < 2; $i ++){
		$sql = "INSERT INTO ".MAIN_DB_PREFIX."echelon (fk_categorie, libelle, commentaire) VALUES (78,'".$libelle_echelon[$i]."','".$libelle_echelon[$i]."')";
		$db->query($sql);
	}
	$libelle_echelon = array("ECH-A","ECH-B"); //3 et 4 liés a categ 84
	for($i = 0; $i < 2; $i ++){
		$sql = "INSERT INTO ".MAIN_DB_PREFIX."echelon (fk_categorie, libelle, commentaire) VALUES (84,'".$libelle_echelon[$i]."','".$libelle_echelon[$i]."')";
		$db->query($sql);
	}
	$libelle_echelon = array("ECH-A","ECH-B","ECH-C"); //5,6 et 7 liés a categ 85
	for($i = 0; $i < 3; $i ++){
		$sql = "INSERT INTO ".MAIN_DB_PREFIX."echelon (fk_categorie, libelle, commentaire) VALUES (85,'".$libelle_echelon[$i]."','".$libelle_echelon[$i]."')";
		$db->query($sql);
	}
	$libelle_echelon = array("ECH-A","ECH-B"); //8 et 9 liés a categ 86
	for($i = 0; $i < 2; $i ++){
		$sql = "INSERT INTO ".MAIN_DB_PREFIX."echelon (fk_categorie, libelle, commentaire) VALUES (86,'".$libelle_echelon[$i]."','".$libelle_echelon[$i]."')";
		$db->query($sql);
	}
	$libelle_echelon = array("ECH-A","ECH-B","ECH-C"); //10, 11 et 12 liés a categ 87
	for($i = 0; $i < 3; $i ++){
		$sql = "INSERT INTO ".MAIN_DB_PREFIX."echelon (fk_categorie, libelle, commentaire) VALUES (87,'".$libelle_echelon[$i]."','".$libelle_echelon[$i]."')";
		$db->query($sql);
	}

	$salaire_base = array("24251","25980","27291","28324","30852","33886","35720","42744","46957","47741","50363","62517","54978","60105","61793","67906","73461","79016");
	$id_categ = array(78,78,79,80,81,82,83,84,84,85,85,85,86,86,87,87,87,88);
	$id_echelon = array(/*echelon pour categ 75*/1,2,/*76-80*/0,0,0,0,0,/*81*/3,4,/*82*/5,6,7,/*83*/8,9,/*84*/10,11,12,/*85*/0);

		$fk_cat = 75;
		$trouve = 1;
	$lim = count($salaire_base);
	for ($i=0; $i< $lim; $i++){
				$sql = 'INSERT INTO '.MAIN_DB_PREFIX.'grille_categorie_echelon_salaire_base (fk_grille, fk_categorie, fk_echelon, salaire_base) VALUES (4,'.$id_categ[$i].','.$id_echelon[$i].',"'.$salaire_base[$i].'")';
		$db->query($sql);
	}
}

if($id_convention ==4 && $nb == 0){
//------------------------------------------- Bâtiments 
//id_conv = 4; id_grille = 5; id_categ = [89-113]

	$sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'grille (fk_convention, nom, commentaire, active) VALUES ('.$id_convention.',"Grille Actuelle","Convention Collective des Entreprises du Bâtiments et des Travaux Publics",1)';
	$db->query($sql_insert);

	$code_categorie = array("1ère Cat","2ème Cat","3ème Cat","4ème Cat","5ème Cat","6ème Cat","H Cat","1ère Cat","2ème Cat","3ème Cat","4ème Cat","5ème Cat","6ème Cat","7ème Cat","M1","M2","M3","M4","M5","Ing, P1","Ing, P2","Ing, P3");
	$lim = count($code_categorie);
	for ($i=0; $i< $lim; $i++){
		$sql = 'INSERT INTO '.MAIN_DB_PREFIX.'dcategories (code_categorie, nom_categorie, fk_convention) VALUES ("'.$code_categorie[$i].'","'.$code_categorie[$i].'",'.$id_convention.')';
		$db->query($sql);
	}

	$libelle_echelon = array("1er ECH","2è ECH"); //les 2 premiers liés a categ 89
	for($i = 0; $i < 2; $i ++){
		$sql = "INSERT INTO ".MAIN_DB_PREFIX."echelon (fk_categorie, libelle, commentaire) VALUES (89,'".$libelle_echelon[$i]."','".$libelle_echelon[$i]."')";
		$db->query($sql);
	}
	$libelle_echelon = array("1er ECH","2è ECH"); //3 et 4 liés a categ 92
	for($i = 0; $i < 2; $i ++){
		$sql = "INSERT INTO ".MAIN_DB_PREFIX."echelon (fk_categorie, libelle, commentaire) VALUES (92,'".$libelle_echelon[$i]."','".$libelle_echelon[$i]."')";
		$db->query($sql);
	}
	$libelle_echelon = array("1er ECH","2è ECH"); //5 et 6 liés a categ 93
	for($i = 0; $i < 2; $i ++){
		$sql = "INSERT INTO ".MAIN_DB_PREFIX."echelon (fk_categorie, libelle, commentaire) VALUES (93,'".$libelle_echelon[$i]."','".$libelle_echelon[$i]."')";
		$db->query($sql);
	}

	$salaire_base = array("35526","42464","48708","51608","60009","63996","67828","73610","79840","86013","51438","59176","68803","76581","83398","95205","104417","97027","110133","133398","156460","175852","200340","235271","302368");
	$id_categ = array(89,89,90,91,92,92,93,93,94,95,96,97,98,99,100,101,102,103,104,105,106,107,108,109,110);
	$id_echelon = array(/*echelon pour categ 75*/13,14,/*76-80*/0,0,15,16,17,18,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);

	$lim = count($salaire_base);
	for ($i=0; $i< $lim; $i++){
		$sql = 'INSERT INTO '.MAIN_DB_PREFIX.'grille_categorie_echelon_salaire_base (fk_grille, fk_categorie, fk_echelon, salaire_base) VALUES (5,'.$id_categ[$i].','.$id_echelon[$i].',"'.$salaire_base[$i].'")';
		$db->query($sql);
	}
}

if($id_convention ==5 && $nb == 0){
//-------------------------------------------Industrie Hoteliers
//id_conv = 5; id_grille = 6; id_categ = [1014-117]
	$sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'grille (fk_convention, nom, commentaire, active) VALUES ('.$id_convention.',"Grille Actuelle","Convention des Industries Hotélières du Mali",1)';
	$db->query($sql_insert);

    $code_categorie = array("CATEGORIE E","CATEGORIE D","CATEGORIE C","CATEGORIE B", "CATEGORIE A", "HORS CATEGORIE");

    $lim = count($code_categorie);
	for ($i=0; $i< $lim; $i++){
		$sql = 'INSERT INTO '.MAIN_DB_PREFIX.'dcategories (code_categorie, nom_categorie, fk_convention) VALUES ("'.$code_categorie[$i].'","'.$code_categorie[$i].'",'.$id_convention.')';
		$db->query($sql);
	}

        //id_ech id commence à 19
    $libelle_echelon = array("E1","E2","E3","E4","E5","E6","E7","E8","E9","E10");     //les 10 premiers liés a categ 111
	for($i = 0; $i < count($libelle_echelon); $i ++){
		$sql = "INSERT INTO ".MAIN_DB_PREFIX."echelon (fk_categorie, libelle, commentaire) VALUES (111,'".$libelle_echelon[$i]."','".$libelle_echelon[$i]."')";
		$db->query($sql);
	}
	$libelle_echelon = array("D1","D2","D3","D4","D5","D6","D7","D8","D9","D10"); //les 10 deuxièmes echelons liés à la categ 112
	for($i = 0; $i < count($libelle_echelon); $i ++){
		$sql = "INSERT INTO ".MAIN_DB_PREFIX."echelon (fk_categorie, libelle, commentaire) VALUES (112,'".$libelle_echelon[$i]."','".$libelle_echelon[$i]."')";
		$db->query($sql);
	}
    $libelle_echelon = array("C1","C2","C3","C4","C5","C6","C7","C8","C9","C10"); //les 10 Troisièmes echelons liés à la categ 113
	for($i = 0; $i < count($libelle_echelon); $i ++){
		$sql = "INSERT INTO ".MAIN_DB_PREFIX."echelon (fk_categorie, libelle, commentaire) VALUES (113,'".$libelle_echelon[$i]."','".$libelle_echelon[$i]."')";
		$db->query($sql);
	}
	$libelle_echelon = array("B1","B2","B3","B4","B5","B6","B7","B8","B9","B10"); //les 10 deuxiquatrièmes echelons liés à la categ 114
	for($i = 0; $i < count($libelle_echelon); $i ++){
		$sql = "INSERT INTO ".MAIN_DB_PREFIX."echelon (fk_categorie, libelle, commentaire) VALUES (114,'".$libelle_echelon[$i]."','".$libelle_echelon[$i]."')";
		$db->query($sql);
	}

	$libelle_echelon = array("A1","A2","A3","A4","A5","A6","A7","A8","A9","A10"); //les 10 deuxiquatrièmes echelons liés à la categ 114
	for($i = 0; $i < count($libelle_echelon); $i ++){
		$sql = "INSERT INTO ".MAIN_DB_PREFIX."echelon (fk_categorie, libelle, commentaire) VALUES (115,'".$libelle_echelon[$i]."','".$libelle_echelon[$i]."')";
		$db->query($sql);
	}

	$libelle_echelon = array("HC1", "HC2"); //les 10 deuxiquatrièmes echelons liés à la categ 114
	for($i = 0; $i < count($libelle_echelon); $i ++){
		$sql = "INSERT INTO ".MAIN_DB_PREFIX."echelon (fk_categorie, libelle, commentaire) VALUES (116,'".$libelle_echelon[$i]."','".$libelle_echelon[$i]."')";
		$db->query($sql);
	}

    $salaire_base = array("43200","44200","46700","48200","49700","51200","52700","54200","56700","57200","47775","51275","52775","54275","55775","57275","58775","60275","61775","63275","53260","56500","61000","66500"
            ,"72800","80750","81850","82760","84500","85250","66300","72500","80350","84750","91350","92800","93500","95650","105750","107350", "86500", "97600", "101500", "109700", "119650", "128800", "141500", "152600", "167650", "177750", "200400", "231300");

	$id_categ = array(111,111,111,111,111,111,111,111,111,111,112,112,112,112,112,112,112,112,112,112,113,113,113,113,113,113,113,113,113,113,114,114,114,114,114,114,114,114,114,114,115,115,115,115,115,115,115,115,115,115,116,116);

	$lim = count($salaire_base);
	for ($i=0; $i< $lim; $i++){
		$sql = 'INSERT INTO '.MAIN_DB_PREFIX.'grille_categorie_echelon_salaire_base (fk_grille, fk_categorie, fk_echelon, salaire_base) VALUES (6,'.$id_categ[$i].','.(18+$i+1).',"'.$salaire_base[$i].'")';
		$db->query($sql);
	}
}
if($id_convention == 6 && $nb == 0){
//----------------------------------- Surveillance
	$sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'grille (fk_convention, nom, commentaire, active) VALUES ('.$id_convention.',"Grille Actuelle","Convention Collective des Personnels des Sociétés de Surveillance, de Gardiennage et de Prestations de Service",1)';
	$db->query($sql_insert);
}
if($id_convention ==7 && $nb == 0){
//--------------------------------------Métallurgie

	$sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'grille (fk_convention, nom, commentaire, active) VALUES ('.$id_convention.',"Grille Actuelle","Convention Collective de la Méttallurgie et des Industries de la Mécanique Générale",1)';
	$db->query($sql_insert);

	
  }

}

