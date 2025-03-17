<?php

$instSql = "SELECT niveau FROM ".MAIN_DB_PREFIX."installation";
$result = $db->query($instSql);//= $db->query($covSql);
$obj = $db->fetch_object($result);

if(!empty($obj) && $obj->niveau != 8){

	?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Progression des Paies</title>
    <style>
        #progress-container {
            width: 100%;
            background: #f3f3f3;
            border: 1px solid #ccc;
            height: 30px;
            position: relative;
        }
        #progress-bar {
            width: 0%;
            height: 100%;
            background: #4caf50;
            transition: width 0.5s;
        }
    </style>
</head>
<body>
    <h1 id="progressTitre" >Installation en cours...</h1>
    <div id="progress-container">
        <div id="progress-bar"></div>
    </div>
    <p id="progress-text">0%</p>

    <script>
        
        function updateProgress() {
            fetch('install_progression.php')

                .then(response => response.json())

                .then(data => {
                    const progressTitre = document.getElementById('progressTitre');
                    const progressBar = document.getElementById('progress-bar');
                    const progressText = document.getElementById('progress-text');
                    if(data.effectue == data.total)
                        progressTitre.textContent = 'Installation éffectuée avec succès';

                        //alert(data.effectue);
                    const percentage = Math.round((data.effectue / data.total) * 100);
                    progressBar.style.width = percentage + '%';
                    progressText.textContent = percentage + '% (' + data.effectue + '/' + data.total + ')';
                });
        }

        // Mettre à jour la progression toutes les 2 secondes
        setInterval(updateProgress, 500);
    </script>

</body>
</html>

<?php
}
$instSql = "SELECT niveau FROM ".MAIN_DB_PREFIX."installation";
$result = $db->query($instSql);//= $db->query($covSql);
$obj = $db->fetch_object($result);

if(empty($obj)){
    $sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'installation (niveau, total) VALUES (0, 8)';
	$db->query($sql_insert);

    $instSql = "SELECT niveau FROM ".MAIN_DB_PREFIX."installation";
$result = $db->query($instSql);//= $db->query($covSql);
$obj = $db->fetch_object($result);
}
if($obj->niveau == 0){
    
    print "<h2>Installation des conventions : Mines et Banques & Assurances</h2>";
	//Mine 
	//id_conv = 1; id_grille = 1; id_categ = [1-33](30categ)
	$sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'grille (fk_convention, nom, commentaire, active) VALUES (1,"Grille Actuelle","Covention Collective des Sociétés et Entreprises Minières, Geologiques et Hydrogeologiques",1)';
	$db->query($sql_insert);

	$code_categorie = array("HC1","HC2","A10","A9","A8","A7","A6","A5","A4","A3","A2","A1","B7","B6","B5","B4","B3","B2","B1","C6","C5","C4","C3","C2","C1","D5","D4","D3","D2","D1","E3","E2","E1");
	$lim = count($code_categorie);
	for ($i=0; $i< $lim; $i++){
		$sql = 'INSERT INTO '.MAIN_DB_PREFIX.'dcategories (code_categorie, nom_categorie, fk_convention) VALUES ("'.$code_categorie[$i].'","'.$code_categorie[$i].'",1)';
		$db->query($sql);
	}

	$salaire_base = array("630474","618659","616821","581377","569562","510489","475045","424010","372314","342680","309930","276906","361211","337582","298083","268232","238513","222670","179969","233735","216013","196199","177284","159271","141259","168179","153364","135351","125068","110056","87989","81259","74815");
	$lim = count($salaire_base);
	for ($i=0; $i< $lim; $i++){
		$sql = 'INSERT INTO '.MAIN_DB_PREFIX.'grille_categorie_echelon_salaire_base (fk_grille, fk_categorie, fk_echelon, salaire_base) VALUES (1,'.($i+1).',0,"'.$salaire_base[$i].'")';
		$db->query($sql);
	}
  

//---------------------------------Banque, Assurance et Finance
//id_conv = 2; id_grille = 2; id_categ = [34-77](44categ)
	$sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'grille (fk_convention, nom, commentaire, active) VALUES (2,"Grille Actuelle 01-01-2021","Convention Collective des Banques, Assurances et Etablissements Financiers du Mali",0)';
	$db->query($sql_insert);
	//----grille 1 des banques
	$code_categorie = array("CATEGORIE 1","CATEGORIE 2","CATEGORIE 3","CATEGORIE 4","CATEGORIE 5","CATEGORIE 6","CATEGORIE 7A","CATEGORIE 7B","CATEGORIE 7C","CATEGORIE 7D","CATEGORIE 7E","CATEGORIE 7F","CATEGORIE 7G"
	,"CLASSE 1A","CLASSE 1B","CLASSE 2A","CLASSE 2B","CLASSE 3A","CLASSE 3B","CLASSE 4A","CLASSE 4B","CLASSE 4C","CLASSE 5A","CLASSE 5B","CLASSE 5C","CLASSE 6A","CLASSE 6B","CLASSE 6C","CLASSE 6D","CLASSE 7A","CLASSE 7B"
	,"CLASSE 7C","CLASSE 7D","CLASSE 8A","CLASSE 8B","CLASSE 8C","CLASSE 8D","CLASSE 8E","CLASSE 8F","CLASSE 8G","HORS CLASSE A","HORS CLASSE B","HORS CLASSE C","HORS CLASSE D");
	$lim = count($code_categorie);
	for ($i=0; $i< $lim; $i++){
		$sql = 'INSERT INTO '.MAIN_DB_PREFIX.'dcategories (code_categorie, nom_categorie, fk_convention) VALUES ("'.$code_categorie[$i].'","'.$code_categorie[$i].'",2)';
		$db->query($sql);
	}

	$salaire_base = array("69826","76832","82749","91484","100218","108944","116646","141076","165870","192438","219306","246178","272446","219306","246178","272746","284753","299037","323551","348063","372577","397085","406860"
	,"430509","438096","448131","470538","486668","502350","518934","533725","551195","567334","58346","99594","615731","631867","647996","663677","680260","696391","712522","738078","776408");
	$lim = count($salaire_base);
	for ($i=0; $i< $lim; $i++){
		$sql = 'INSERT INTO '.MAIN_DB_PREFIX.'grille_categorie_echelon_salaire_base (fk_grille, fk_categorie, fk_echelon, salaire_base) VALUES (2,'.(33+$i+1).',0,"'.$salaire_base[$i].'")';
		$db->query($sql);
	}

	$sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'grille (fk_convention, nom, commentaire, active) VALUES (2,"Grille Actuelle 01-01-2023","Convention Collective des Banques, Assurances et Etablissements Financiers du Mali",1)';
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

    $sql_insert = 'UPDATE '.MAIN_DB_PREFIX.'installation SET niveau=1';
	$db->query($sql_insert);

    print "<a class='button' title='Continuer l'installation' href='".$_SERVER["PHP_SELF"]."'>Suivant</a>";
    die("Cliquez sur suivant!");

}elseif($obj->niveau == 1){
    print "<h2>Installations des conventions : Commerces et Bâtiments & Travaux publics</h2>";

//----------------------------------------------Commerce
//id_conv = 3; id_grille = 4; id_categ = [78-88](11 categ)(les catégorie ne change pas car c'est la même convention)
	$sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'grille (fk_convention, nom, commentaire, active) VALUES (3,"Grille Actuelle","Convention Collective du Commerce",1)';
	$db->query($sql_insert);

	$code_categorie = array("1ERE CATEG","2EME CATEG","3EME CATEG","4EME CATEG","5EME CATEG","6EME CATEG","7EME CATEG","8EME CATEG","9EME CATEG","10EME CATEG","11EME CATEG");
	$lim = count($code_categorie);
	for ($i=0; $i< $lim; $i++){
		$sql = 'INSERT INTO '.MAIN_DB_PREFIX.'dcategories (code_categorie, nom_categorie, fk_convention) VALUES ("'.$code_categorie[$i].'","'.$code_categorie[$i].'",3)';
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


//------------------------------------------- Bâtiments 
//id_conv = 4; id_grille = 5; id_categ = [89-113]

	$sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'grille (fk_convention, nom, commentaire, active) VALUES (4,"Grille Actuelle","Convention Collective des Entreprises du Bâtiments et des Travaux Publics",1)';
	$db->query($sql_insert);

	$code_categorie = array("1ère Cat","2ème Cat","3ème Cat","4ème Cat","5ème Cat","6ème Cat","H Cat","1ère Cat","2ème Cat","3ème Cat","4ème Cat","5ème Cat","6ème Cat","7ème Cat","M1","M2","M3","M4","M5","Ing, P1","Ing, P2","Ing, P3");
	$lim = count($code_categorie);
	for ($i=0; $i< $lim; $i++){
		$sql = 'INSERT INTO '.MAIN_DB_PREFIX.'dcategories (code_categorie, nom_categorie, fk_convention) VALUES ("'.$code_categorie[$i].'","'.$code_categorie[$i].'",4)';
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

    $sql_insert = 'UPDATE '.MAIN_DB_PREFIX.'installation SET niveau=2';
	$db->query($sql_insert);

    print "<a class='button' title='Continuer l'installation' href='".$_SERVER["PHP_SELF"]."'>Suivant</a>";
    die("Cliquez sur suivant!");
}elseif($obj->niveau == 2){
    print "<h2>Installations des conventions : Hotels, Surveillance & Gardiennage et Métallurgies</h2>";

//-------------------------------------------Industrie Hoteliers
//id_conv = 5; id_grille = 6; id_categ = [1014-117]
	$sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'grille (fk_convention, nom, commentaire, active) VALUES (5,"Grille Actuelle","Convention des Industries Hotélières du Mali",1)';
	$db->query($sql_insert);

    $code_categorie = array("CATEGORIE E","CATEGORIE D","CATEGORIE C","CATEGORIE B", "CATEGORIE A", "HORS CATEGORIE");

    $lim = count($code_categorie);
	for ($i=0; $i< $lim; $i++){
		$sql = 'INSERT INTO '.MAIN_DB_PREFIX.'dcategories (code_categorie, nom_categorie, fk_convention) VALUES ("'.$code_categorie[$i].'","'.$code_categorie[$i].'",5)';
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



//----------------------------------- Surveillance
	$sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'grille (fk_convention, nom, commentaire, active) VALUES (6,"Grille Actuelle","Convention Collective des Personnels des Sociétés de Surveillance, de Gardiennage et de Prestations de Service",1)';
	$db->query($sql_insert);


//--------------------------------------Métallurgie

	$sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'grille (fk_convention, nom, commentaire, active) VALUES (7,"Grille Actuelle","Convention Collective de la Méttallurgie et des Industries de la Mécanique Générale",1)';
	$db->query($sql_insert);

    $sql_insert = 'UPDATE '.MAIN_DB_PREFIX.'installation SET niveau=3';
	$db->query($sql_insert);

    print "<a class='button' title='Continuer l'installation' href='".$_SERVER["PHP_SELF"]."'>Suivant</a>";
    die("Cliquez sur suivant!");
}elseif($obj->niveau == 3){
    print "<h2>Installation des primes Obligatoires</h2>";

	//Prime d'anciennété
	$sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."primes";
	$res = $db->query($sql);
	if($res){
		$obj = $db->num_rows($res);
		if($obj == 0){
	
			$sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'primes (libelle, type_prime, commentaire, appliquee, exonere, affiche_bulletin, fk_convention, fk_societe, fk_accord_etablissement, active) VALUES ("Ancienneté","obligatoire","Cette prime rémunère l enciennété des Salarié",1,"Non","Oui",0,0,0,1)';
		  $db->query($sql_insert);
		}
	}

	//Installation des conditions d'anciennété par concention
	
$verif_sql = "SELECT DISTINCT fk_convention FROM ".MAIN_DB_PREFIX."condition_anciennete";
$verif_res = $db->query($verif_sql);
$nb_obj = $db->num_rows($verif_res);
$array_fk_conv = array();
$a = 0;
if($verif_res){
   while ($a < $nb_obj) {
      $obj = $db->fetch_object($verif_res);
      $array_fk_conv[$a] = $obj->fk_convention;
      $a ++;
   }
}
//Convention Mine pour 15 ans de services ou plus id = 1; 17 enregistrements
if(!in_array(1,$array_fk_conv)){
   $annee = array("0","1","2","3","4","5","6","7","8","9","10","11","12","13","14","15","+");
   $taux = array("0","0","0","3","3","5","6","7","8","9","10","11","12","13","14","15","15");

   for ($i=0; $i < count($annee); $i++) { 
      $sql  = 'INSERT INTO '.MAIN_DB_PREFIX.'condition_anciennete (fk_convention, fk_prime, nombre_annee, taux) VALUES (1,1,"'.$annee[$i].'","'.$taux[$i].'")';
      $res = $db->query($sql);
      
}

}

// Convention banques & Assurances pour 46 ans de service (car il n'y a pas de plafond) id = 2; 47 enregistrements
if(!in_array(2,$array_fk_conv)){
   $annee = array("0","1","2","3","4","5","6","7","8","9","10","11","12","13","14","15","16","17","18","19","20","21","22","23","24","25","26","27","28","29","30","31","32","33","34","35","36","37","38","39","40","41","42","43","44","45","46");
   $taux = array("0","0","5","6.75","8.5","10.25","12","13.75","15.5","17.25","19","20.75","22.5","24.25","26","27.75","29.5","31.25","33","35.5","38","40.5","43","45.5","48","50.5","53","55.5","58","60.5","63","65.5","68","70.5","73","75.5","78","80.5","83","85.5","88","90.5","93","95.5","98","100.5","103");

   for ($i=0; $i < count($annee); $i++) { 
      $sql  = 'INSERT INTO '.MAIN_DB_PREFIX.'condition_anciennete (fk_convention, fk_prime, nombre_annee, taux) VALUES (2,1,"'.$annee[$i].'","'.$taux[$i].'")';
      $res = $db->query($sql);
      
   }
}



//Convention Commerce pour 15 ans de service ou plus id = 3; 17 enregistrements
if(!in_array(3,$array_fk_conv)){
   $annee = array("0","1","2","3","4","5","6","7","8","9","10","11","12","13","14","15","+");
   $taux = array("0","0","0","3","3","5","6","7","8","9","10","11","12","13","14","15","15");

   for ($i=0; $i < count($annee); $i++) { 
      $sql  = 'INSERT INTO '.MAIN_DB_PREFIX.'condition_anciennete (fk_convention, fk_prime, nombre_annee, taux) VALUES (3,1,"'.$annee[$i].'","'.$taux[$i].'")';
      $res = $db->query($sql);
      
   }
}


//Bâtiments & Traveaux public id = 4; 22 enregistrements
if(!in_array(4,$array_fk_conv)){
   $annee = array("0","1","2","3","4","5","6","7","8","9","10","11","12","13","14","15","16","17","18","19","20","+");
   $taux = array("0","0","0","4","4","6","7","8","9","10","11","12","13","14","15","16","17","18","19","20","21","21");

   for ($i=0; $i < count($annee); $i++) { 
      $sql  = 'INSERT INTO '.MAIN_DB_PREFIX.'condition_anciennete (fk_convention, fk_prime, nombre_annee, taux) VALUES (4,1,"'.$annee[$i].'","'.$taux[$i].'")';
      $res = $db->query($sql);
      
   }
}


//Industries Hoteliers id = 5; 21 enregistrements
if(!in_array(5,$array_fk_conv)){
      $annee = array("0","1","2","3","4","5","6","7","8","9","10","11","12","13","14","15","16","17","18","19","+");
      $taux = array("0","0","0","4","4","6","7","8","9","10","11","12","13","14","15","16","17","18","19","20","20");

      for ($i=0; $i < count($annee); $i++) { 
         $sql  = 'INSERT INTO '.MAIN_DB_PREFIX.'condition_anciennete (fk_convention, fk_prime, nombre_annee, taux) VALUES (5,1,"'.$annee[$i].'","'.$taux[$i].'")';
         $res = $db->query($sql);
         
   }
}


//Surveillance  id = 6
 //Pas de Prime d'anciennete

// Metallurgiie et Mécanique = id = 7; 22 enregistrements
if(!in_array(7,$array_fk_conv)){
   $annee = array("0","1","2","3","4","5","6","7","8","9","10","11","12","13","14","15","16","17","18","19","20","+");
   $taux = array("0","0","0","3","3","5","6","7","8","9","10","11","12","13","14","15","16","17","18","19","20","20");

   for ($i=0; $i < count($annee); $i++) { 
      $sql  = 'INSERT INTO '.MAIN_DB_PREFIX.'condition_anciennete (fk_convention, fk_prime, nombre_annee, taux) VALUES (7,1,"'.$annee[$i].'","'.$taux[$i].'")';
      $res = $db->query($sql);
      
   }
}

    $sql_insert = 'UPDATE '.MAIN_DB_PREFIX.'installation SET niveau=4';
	$db->query($sql_insert);
    print "<a class='button' title='Continuer l'installation' href='".$_SERVER["PHP_SELF"]."'>Suivant</a>";
    die("Cliquez sur suivant!");
}elseif($obj->niveau == 4){
    print "<h2>Installation des indemnités Obligatoires</h2>";

	
//Les indemnité obligatoire au Mali
$sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."indemnite";
$res = $db->query($sql);
if($res){
  $num = $db->num_rows($res);
  if($num == 0){

	//Ind Spéciale 1973
	$sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'indemnite (libelle, type_indemnite, commentaire, appliquee, exonere, affiche_bulletin, fk_convention) VALUES ("Ind Spéciale 1973","obligatoire","Indemnité Spéciale obligatoire de 1973",1,"Non","Oui", 0)';
	$db->query($sql_insert);

	$sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'condition_indemnite (fk_indemnite, type_montant, forfait, pourcentage, inferieur, superieur, minimum_perception)';
	$sql_insert .= 'VALUES(1, "forfait", "1000", "0", "0", "0", "0")';
	$db->query($sql_insert);
	
	$sql = 'INSERT INTO '.MAIN_DB_PREFIX.'condition_categorie_indemnite (fk_condition, fk_categorie) VALUES (1,0)';
	$result2 = $db->query($sql);
  }
  if($num <2){
	//Ind Cherté de 1974
	$sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'indemnite (libelle, type_indemnite, commentaire, appliquee, exonere, affiche_bulletin, fk_convention) VALUES ("Ind Cherté de 1974","obligatoire","Indemnité Cherté obligatoire de 1974",1,"Non","Oui", 0)';
	$db->query($sql_insert);

	$sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'condition_indemnite (fk_indemnite, type_montant, forfait, pourcentage, inferieur, superieur, minimum_perception)';
	$sql_insert .= 'VALUES(2, "pourcentage", "0", "10", "0", "0", "2250")';
	$db->query($sql_insert);

	$sql = 'INSERT INTO '.MAIN_DB_PREFIX.'condition_categorie_indemnite (fk_condition, fk_categorie) VALUES (2,0)';
	$result2 = $db->query($sql);

	$sql1 = 'INSERT INTO '.MAIN_DB_PREFIX.'condition_type_salarie_indemnite (fk_condition, fk_type_salarie) VALUES (2,2)';
	$result2 = $db->query($sql1);
	

	
  }
  if($num <3){
	//Ind Spéciale 1982
	$sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'indemnite (libelle, type_indemnite, commentaire, appliquee, exonere, affiche_bulletin, fk_convention) VALUES ("Ind Spéciale 1982","obligatoire","Indemnité spéciale obligatoire 1982",1,"Non","Oui", 0)';
	$db->query($sql_insert);

	$sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'condition_indemnite (fk_indemnite, type_montant, forfait, pourcentage, inferieur, superieur, minimum_perception)';
	$sql_insert .= 'VALUES(3, "forfait", "1000", "0", "0", "25000", "0")';
	$db->query($sql_insert);

	$sql = 'INSERT INTO '.MAIN_DB_PREFIX.'condition_categorie_indemnite (fk_condition, fk_categorie) VALUES (3,0)';
	$result2 = $db->query($sql);

	$sql2 = 'INSERT INTO '.MAIN_DB_PREFIX.'condition_type_salarie_indemnite (fk_condition, fk_type_salarie) VALUES (3,3)';
	$result2 = $db->query($sql2);
	
	
  }
  if($num <4){
	//Ind Solidarité 1991
	$sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'indemnite (libelle, type_indemnite, commentaire, appliquee, exonere, affiche_bulletin, fk_convention) VALUES ("Ind de Solidarité 1991","obligatoire","Indemnité de solidarité obligatoire de 1991",1,"Non","Oui",0)';
	$db->query($sql_insert);
	
	
	
//cond pour cadres
	$sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'condition_indemnite (fk_indemnite, type_montant, forfait, pourcentage, inferieur, superieur, minimum_perception)';
	$sql_insert .= 'VALUES(4, "forfait", "2000", "0", "0", "0", "0")';
	$db->query($sql_insert);
	//cond pour les maitrises
	$sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'condition_indemnite (fk_indemnite, type_montant, forfait, pourcentage, inferieur, superieur, minimum_perception)';
	$sql_insert .= 'VALUES(4, "forfait", "5000", "0", "0", "0", "0")';
	$db->query($sql_insert);
	//cond pour les non cadres
	$sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'condition_indemnite (fk_indemnite, type_montant, forfait, pourcentage, inferieur, superieur, minimum_perception)';
	$sql_insert .= 'VALUES(4, "forfait", "6500", "0", "0", "0", "0")';
	$db->query($sql_insert);
	$sql = 'INSERT INTO '.MAIN_DB_PREFIX.'condition_categorie_indemnite (fk_condition, fk_categorie) VALUES (4,0)';
	$result2 = $db->query($sql);
	$sql = 'INSERT INTO '.MAIN_DB_PREFIX.'condition_categorie_indemnite (fk_condition, fk_categorie) VALUES (5,0)';
	$result2 = $db->query($sql);
	$sql = 'INSERT INTO '.MAIN_DB_PREFIX.'condition_categorie_indemnite (fk_condition, fk_categorie) VALUES (6,0)';
	$result2 = $db->query($sql);

	$sql4 = 'INSERT INTO '.MAIN_DB_PREFIX.'condition_type_salarie_indemnite (fk_condition, fk_type_salarie) VALUES (4,0)';
	$result2 = $db->query($sql4);
	$sql5 = 'INSERT INTO '.MAIN_DB_PREFIX.'condition_type_salarie_indemnite (fk_condition, fk_type_salarie) VALUES (5,0)';
	$result2 = $db->query($sql5);
	$sql6 = 'INSERT INTO '.MAIN_DB_PREFIX.'condition_type_salarie_indemnite (fk_condition, fk_type_salarie) VALUES (6,0)';
	$result2 = $db->query($sql6);

	$sql3 = 'INSERT INTO '.MAIN_DB_PREFIX.'condition_type_salarie_indemnite (fk_condition, fk_type_salarie) VALUES (4,4)';
	$result2 = $db->query($sql3);
  }

  }


    $sql_insert = 'UPDATE '.MAIN_DB_PREFIX.'installation SET niveau=5';
	$db->query($sql_insert);
    print "<a class='button' title='Continuer l'installation' href='".$_SERVER["PHP_SELF"]."'>Suivant</a>";
    die("Cliquez sur suivant!");
}elseif($obj->niveau == 5){

    print "<h2>Installation des Organismes, Taxes et Cotisations</h2>";

				//les organismes par defaut au Mali
			$sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."organisme";
			$res = $db->query($sql);
			if($res){
			$nb = $db->num_rows($res);
			if($nb == 0){
				$sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."organisme (nom_organisme, commentaire) VALUES ('CANAM','Caisse Nationale Assurance Maladie.')";
				$db->query($sql_insert);
			}
			if($nb < 2){
				$sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."organisme (nom_organisme, commentaire) VALUES ('Impôts','Organisme des impôts')";
				$db->query($sql_insert);

			}

			if($nb < 2){
				$sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."organisme (nom_organisme, commentaire) VALUES ('I.N.P.S','Institut National de Prévoyance Sociale')";
				$db->query($sql_insert);

			}

			}


		//les taxes par defaut au Mali
		$sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."type_taxe";
		$res = $db->query($sql);
		if($res){
		  $obj = $db->num_rows($res);
		  if($obj == 0){
			$sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."type_taxe (fk_organisme, libelle, commentaire, nature, type_bareme, affiche_bulletin) VALUES (2,'I.T.S','Impôts sur le traitement de salaires','Obligatoire',1,'Oui')";
			$db->query($sql_insert);
			
		
			$sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'bareme_taxe (fk_taxe, libelle, actif) VALUES (1,"juillet 2015 barème", 1)';
			$db->query($sql_insert);
	
			$sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'bareme_taxe (fk_taxe, libelle, actif) VALUES (1,"Février 2024", 0)';
			$db->query($sql_insert);
		
			$bareme = array();
			$bareme[0] = array(00, "330000", "0", 00, 1);
			$bareme[1] = array(330001, "578400", "5", 12420, 1);
			$bareme[2] = array(578401, "1176400", "12", 84100, 1);
			$bareme[3] = array(1176401, "1789733", "18", 194580, 1);
			$bareme[4] = array(1789734, "2384195", "26", 349193, 1);
			$bareme[5] = array(2384196, "3494130", "31", 693219, 1);
			$bareme[6] = array(3494131, "+", "37", 00, 1);
	
			$bareme[7] = array(0, "330000", "0", 00, 2);
			$bareme[8] = array(330001, "1200000", "2", 17400, 2);
			$bareme[9] = array(1200001, "1800000", "10", 77400, 2);
			$bareme[10] = array(1800001, "2600000", "26", 285400, 2);
			$bareme[11] = array(2600001, "3500000", "33", 582400, 2);
			$bareme[12] = array(3500001, "4100000", "36", 798400, 2);
			$bareme[13] = array(4100001, "+", "40", 00, 2);
		
			for ($i=0; $i < count($bareme); $i++) { 
				$sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."taxe (montant_debut, montant_limit, taux, fk_type, valeur, fk_bareme) VALUES (".$bareme[$i][0].",'".$bareme[$i][1]."','".$bareme[$i][2]."',1,".$bareme[$i][3].",".$bareme[$i][4].")";
				$result = $db->query($sql_insert);
			}
		
	
			$sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."type_taxe (fk_organisme, libelle, commentaire, nature, type_bareme, affiche_bulletin) VALUES (2,'CFE','Contribution forfaitaire à la charge des employeurs','Obligatoire',2,'Oui')";
			$db->query($sql_insert);
	
			$sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."type_taxe (fk_organisme, libelle, commentaire, nature, type_bareme, affiche_bulletin) VALUES (2,'TL','Taxe de logement','Obligatoire',2,'Oui')";
			$db->query($sql_insert);
	
			$sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'bareme_taxe2 (fk_taxe, taux_salariale, taux_patronale, charge) VALUES (2,"0","3.5",2)';
			$db->query($sql_insert);
	
			$sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'bareme_taxe2 (fk_taxe, taux_salariale, taux_patronale, charge) VALUES (3,"0","1",2)';
			$db->query($sql_insert);
	
		  }
		}
			
		//les prestations par defaut au Mali
		$sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."type_prestation";
		$res = $db->query($sql);
		if($res){
		$nb = $db->num_rows($res);
		if($nb < 1){
			//type prestations
			$sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."type_prestation (fk_organisme, code, commentaire, nature, affiche_bulletin, bilan) VALUES (3,'ATMP','Accidents de Travail et des Maladies Professionnelles','Obligatoire','Oui',1)";
			$db->query($sql_insert);
			//barème prestation
			$sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."bareme_prestation (fk_prestation, charge, taux_salariale, taux_patronale) VALUES(1,2,'0','4')";
			$db->query($sql_insert);
			$sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."bareme_prestation (fk_prestation, charge, taux_salariale, taux_patronale) VALUES(1,2,'0','2')";
			$db->query($sql_insert);

			//convention concernée par ces barèmes
			$sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."bareme_prestation_convention (fk_condition, fk_convention) VALUES(1, 1)";
			$db->query($sql_insert);
			$sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."bareme_prestation_convention (fk_condition, fk_convention) VALUES(2, 2)";
			$db->query($sql_insert);
			$sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."bareme_prestation_convention (fk_condition, fk_convention) VALUES(2, 3)";
			$db->query($sql_insert);
			$sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."bareme_prestation_convention (fk_condition, fk_convention) VALUES(2, 4)";
			$db->query($sql_insert);
			$sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."bareme_prestation_convention (fk_condition, fk_convention) VALUES(2, 5)";
			$db->query($sql_insert);
			
		}

		if($nb < 2){
			$sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."type_prestation (fk_organisme, code, commentaire, nature, affiche_bulletin, bilan) VALUES (3,'Prestations Familiales','Prestations Familiales','Obligatoire','Oui',1)";
			$db->query($sql_insert);

			$sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."bareme_prestation (fk_prestation, charge, taux_salariale, taux_patronale) VALUES(2,2,'0','8')";
			$db->query($sql_insert);

			//convention concernée par ce barème
			$sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."bareme_prestation_convention (fk_condition, fk_convention) VALUES(3, 0)";
			$db->query($sql_insert);
			
		}

		if($nb < 3){
			$sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."type_prestation (fk_organisme, code, commentaire, nature, affiche_bulletin, bilan) VALUES (3,'Retraite','Retraite','Obligatoire','Oui',1)";
			$db->query($sql_insert);

			$sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."bareme_prestation (fk_prestation, charge, taux_salariale, taux_patronale) VALUES(3,0,'3.6','3.4')";
			$db->query($sql_insert);

			//convention concernée par ce barème
			$sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."bareme_prestation_convention (fk_condition, fk_convention) VALUES(4, 0)";
			$db->query($sql_insert);

		}
		
		if($nb < 4){
			$sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."type_prestation (fk_organisme, code, commentaire, nature, affiche_bulletin, bilan) VALUES (3,'Invalidité – allocation de survivant','Invalidité – allocation de survivant','Obligatoire','Oui',1)";
			$db->query($sql_insert);

			$sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."bareme_prestation (fk_prestation, charge, taux_salariale, taux_patronale) VALUES(4,2,'0','2')";
			$db->query($sql_insert);
			//convention concernée par ce barème
			$sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."bareme_prestation_convention (fk_condition, fk_convention) VALUES(5, 0)";
			$db->query($sql_insert);

		}

		
		if($nb < 5){
			$sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'type_prestation (fk_organisme, code, commentaire, nature, affiche_bulletin, bilan) VALUES (3,"A.N.P.E","Agence Nationale Pour l\'Emploi","Obligatoire","Oui",1)';
			$db->query($sql_insert);

			$sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."bareme_prestation (fk_prestation, charge, taux_salariale, taux_patronale) VALUES(5,2,'0','1')";
			$db->query($sql_insert);

			//convention concernée par ce barème
			$sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."bareme_prestation_convention (fk_condition, fk_convention) VALUES(6, 0)";
			$db->query($sql_insert);
			
		}

		if($nb < 6){
			$sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."type_prestation (fk_organisme, code, commentaire, nature, affiche_bulletin, bilan) VALUES (3,'AMO','Assurance Maladie Obligatoire','Obligatoire','Oui',1)";
			$db->query($sql_insert);

			$sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."bareme_prestation (fk_prestation, charge, taux_salariale, taux_patronale) VALUES(6,0,'3.06','3.50')";
			$db->query($sql_insert);

			//convention concernée par ce barème
			$sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."bareme_prestation_convention (fk_condition, fk_convention) VALUES(7, 0)";
			$db->query($sql_insert);
		}
		
		}

    $sql_insert = 'UPDATE '.MAIN_DB_PREFIX.'installation SET niveau=6';
	$db->query($sql_insert);
    print "<a class='button' title='Continuer l'installation' href='".$_SERVER["PHP_SELF"]."'>Suivant</a>";
    die("Cliquez sur suivant!");
}elseif($obj->niveau == 6){
    print "<h2>Installations d'autres informations supplémentaires</h2>";

	//Les type de diplôimes
		$sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."diplome";
		$res = $db->query($sql);
		if($res){
		$nb = $db->num_rows($res);
		if($nb == 0){
			$sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."diplome (nom, commentaire) VALUES ('Doctorat','Tout iplôme de doctorat')";
			$result = $db->query($sql_insert);
		}
		if($nb < 2){
			$sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."diplome (nom, commentaire) VALUES ('Master','Tout diplome de BAC + 5 ou Correspondant')";
			$result = $db->query($sql_insert);
		}
		if($nb < 3){
			$sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."diplome (nom, commentaire) VALUES ('Licence','Tout diplome de BAC + 3 ou Correspondant')";
			$result = $db->query($sql_insert);
		}
		if($nb < 4){
			$sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."diplome (nom, commentaire) VALUES ('BAC','Tout diplome de BAC ou Correspondant')";
			$result = $db->query($sql_insert);

		}
		}

		$sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."type_salarie";
		$res = $db->query($sql);
	
		if($res){
		  $nb = $db->num_rows($res);
		  if($nb == 0){
			$sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."type_salarie (libelle, commentaire) VALUES ('Aucun','')";
			$result = $db->query($sql_insert);
	
		  }
		  if($nb < 2){
			$sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."type_salarie (libelle, commentaire) VALUES ('Cadres','Les salariés de types Cadres.')";
			$result = $db->query($sql_insert);
	
		  }
		  if($nb < 3){
			$sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."type_salarie (libelle, commentaire) VALUES ('Non cadres','Les types salariés non Cadres')";
			$result = $db->query($sql_insert);
	
		  }
		  if($nb < 4){
			$sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."type_salarie (libelle, commentaire) VALUES ('Maitrise','Les types salarié de type Maitrise')";
			$result = $db->query($sql_insert);
	
		  }
		}
		

		//les types de contrat
		$sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."type_contrat";
		$res = $db->query($sql);
		if($res){
		$nb = $db->num_rows($res);
		if($nb == 0){
			$sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."type_contrat (libelle, commentaire) VALUES ('CDD','Contrat a Duré Déterminé.')";
			$result = $db->query($sql_insert);
		}
		if($nb < 2){
			$sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."type_contrat (libelle, commentaire) VALUES ('CDI','Contrat a Duré Indéterminé.')";
			$result = $db->query($sql_insert);
		}
		if($nb < 3){
			$sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."type_contrat (libelle, commentaire) VALUES ('Contrat de Prestation','Le contrat de prestation de services est un contrat commercial qui vise à formaliser les relations entre un prestataire de service (une entreprise) et son client.')";
			$result = $db->query($sql_insert);
		}
		if($nb < 4){
			$sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."type_contrat (libelle, commentaire) VALUES ('Contrat Saisonnier','Le travail saisonnier se caractérise par des missions amenées à se répéter chaque année à la même période.')";
			$result = $db->query($sql_insert);
		}
		if($nb < 5){
			$sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."type_contrat (libelle, commentaire) VALUES ('Contrat de travail temporaire','Contrat de travail temporaire.')";
			$result = $db->query($sql_insert);
		}
		if($nb < 6){
			$sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."type_contrat (libelle, commentaire) VALUES ('Contrat de travail intermittent','Contrat de travail intermittent.')";
			$result = $db->query($sql_insert);
		}
		if($nb < 7){
			$sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."type_contrat (libelle, commentaire) VALUES ('Contrat d'apprentissage','Contrat d'apprentissage.')";
			$result = $db->query($sql_insert);
		}
		if($nb < 8){
			$sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."type_contrat (libelle, commentaire) VALUES ('Contrat de professionnalisation','Contrat de professionnalisation.')";
			$result = $db->query($sql_insert);
		}
		if($nb < 9){
			$sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."type_contrat (libelle, commentaire) VALUES ('Contrat unique d'insertion (CUI)','Contrat unique d'insertion.')";
			$result = $db->query($sql_insert);
		}
		}

    $sql_insert = 'UPDATE '.MAIN_DB_PREFIX.'installation SET niveau=7';
	$db->query($sql_insert);
    print "<a class='button' title='Continuer l'installation' href='".$_SERVER["PHP_SELF"]."'>Suivant</a>";
    die("Cliquez sur suivant!");
}elseif($obj->niveau == 7){
    print "<h2>Installation des informations des Banques</h2>";


			//les types de banque
			$sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."type_banque";
			$res = $db->query($sql);
			if($res){
			$nb = $db->num_rows($res);
			if($nb == 0){
				$sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'type_banque (libelle, commentaire) VALUES ("BDM s.a","La Banque de Développement du Mali.")';
				$result = $db->query($sql_insert);
			}
			if($nb < 2){
				$sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'type_banque (libelle, commentaire) VALUES ("BNDA","Banque Nationale de Développement Agricole.")';
				$result = $db->query($sql_insert);
			}
			if($nb < 3){
				$sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'type_banque (libelle, commentaire) VALUES ("Orange Money","Moyen de transfert d\'argent du SONATEL.")';
				$result = $db->query($sql_insert);
			}
			if($nb < 4){
				$sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'type_banque (libelle, commentaire) VALUES ("MobiCash ou MoovMoney","Moyen de transfert d\'argent du SOTELMA.")';
				$result = $db->query($sql_insert);
			}
			if($nb < 5){
				$sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'type_banque (libelle, commentaire) VALUES ("Banque Atlantique","Banque Atlantique")';
				$result = $db->query($sql_insert);
			}
			if($nb < 6){
				$sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'type_banque (libelle, commentaire) VALUES ("B.O.A","Banque Ouest Africa")';
				$result = $db->query($sql_insert);
			}
			if($nb < 7){
				$sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'type_banque (libelle, commentaire) VALUES ("BIM","BIM")';
				$result = $db->query($sql_insert);
			}
	
			if($nb < 8){
				$sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'type_banque (libelle, commentaire) VALUES ("B.I.C.I.M","Bicim")';
				$result = $db->query($sql_insert);
			}
			if($nb < 9){
				$sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'type_banque (libelle, commentaire) VALUES ("BMS","Banque Malienne de Solidarité")';
				$result = $db->query($sql_insert);
			}
			if($nb < 10){
				$sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'type_banque (libelle, commentaire) VALUES ("ECOBANK","Ecobank")';
				$result = $db->query($sql_insert);
			}
			if($nb < 11){
				$sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'type_banque (libelle, commentaire) VALUES ("U.B.A","United Bank for Africa. La banque numérique.")';
				$result = $db->query($sql_insert);
			}
			if($nb < 12){
				$sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'type_banque (libelle, commentaire) VALUES ("ORABANK","ORABANK Mali")';
				$result = $db->query($sql_insert);
			}
			if($nb < 13){
				$sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'type_banque (libelle, commentaire) VALUES ("CORIS BANK","Coris Bank")';
				$result = $db->query($sql_insert);
			}
			if($nb < 14){
				$sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'type_banque (libelle, commentaire) VALUES ("B.S.I.C","Banque Sahélo Saharienne pour l\'Investissement et le Commerce")';
				$result = $db->query($sql_insert);
			}
			if($nb < 15){
				$sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'type_banque (libelle, commentaire) VALUES ("SAMA money","Sama money")';
				$result = $db->query($sql_insert);
			}
			}

			
    $sql_insert = 'UPDATE '.MAIN_DB_PREFIX.'installation SET niveau=8';
	$db->query($sql_insert);
    print "<a class='button' title='Continuer l'installation' href='./garde.php'>Terminer</a>";
    //die("Cliquez sur suivant!");
}/*elseif($obj->niveau == 8){
    print "<h2>Etape 9</h2>";


    $sql_insert = 'UPDATE '.MAIN_DB_PREFIX.'installation SET niveau=9';
	$db->query($sql_insert);
    print "<a class='button' title='Continuer l'installation' href='".$_SERVER["PHP_SELF"]."'>Suivant</a>";
}elseif($obj->niveau == 9){
    print "<h2>Etape 10</h2>";


    $sql_insert = 'UPDATE '.MAIN_DB_PREFIX.'installation SET niveau=10';
	$db->query($sql_insert);
}*/