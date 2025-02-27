<?php
require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/custom/paiementsalaire/lib/paiementsalaire.lib.php';


llxHeader("", "Paiement | Salaire");
//Titre 
print load_fiche_titre($langs->trans("Importer les salariés de cette société"), '', '');
//print '<hr>';

$id_societe = GETPOST('id_societe','int');
$id_convention = GETPOST('id_convention','int');
$limit = GETPOST('limit','int')?:20;
$arret = GETPOST('arret','int')?:0;
$nb_page = GETPOST('nbpage','int')?:1;
$action = GETPOST('action', 'alpha');

require_once DOL_DOCUMENT_ROOT.'/includes/phpoffice/phpspreadsheet/src/autoloader.php';
require_once DOL_DOCUMENT_ROOT.'/includes/Psr/autoloader.php';
require_once PHPEXCELNEW_PATH.'Spreadsheet.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

/*$mail_sql = "DELETE FROM ".MAIN_DB_PREFIX."salarie WHERE import_key=300125010906";
            $res_mail = $db->query($mail_sql);
			if($res_mail)
			print "ok";
		else print "non";

		$mail_sql = "DELETE FROM ".MAIN_DB_PREFIX."user WHERE import_key=300125010906";
            $res_mail = $db->query($mail_sql);
			if($res_mail)
			print "ok";
		else print "non";
		*/
//Par defaut tous les salariés ont travaillé le maximum de jours du mois en cours
salarie_nb_jour($db, $id_societe);
//--------------------------------

$head = paiementsalaireSocieteHead($id_societe, $id_convention);
print dol_get_fiche_head($head, 'liste', "", -1, '');

if($user->rights->paiementsalaire->salarie->read){
	if($user->id == 1){
	
	$soc_sql = "SELECT * FROM ".MAIN_DB_PREFIX."societe WHERE rowid=".$id_societe;
	$soc_res = $db->query($soc_sql);//= $db->query($covSql);
	$obj_soc = $db->fetch_object($soc_res);
	$obj_soc->name = $obj_soc->nom;
	$obj_soc->element = "societe";			
	$obj_soc->conv = $id_convention;

	societe_preview_next($db, $id_societe, $obj_soc);
	entete_societe($obj_soc, 'societe');

	$head2 = liste_salarie_SocieteHead($id_societe, $id_convention);
	print dol_get_fiche_head($head2, 'import_salarie', "", -1, '');


			
	$monform = new Form($db);

	/*$sql = "DELETE FROM ".MAIN_DB_PREFIX."user where import_key = 300125094208";
	$result = $db->query($sql);

	$sql = "DELETE FROM ".MAIN_DB_PREFIX."salarie where import_key = 300125094208";
	$result = $db->query($sql);*/

	//Insertion des indemnite
	/*$sql = "SELECT sal.rowid FROM ".MAIN_DB_PREFIX."salarie as sal";
	$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."user as u ON u.rowid=sal.fk_user";
	$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."user_extrafields as ue ON u.rowid=ue.fk_object";
	$sql .= " WHERE ue.egp=".$id_societe." AND sal.archiver = 'non'";
	$result = $db->query($sql);
	if($result){
		$num = $db->num_rows($result);
		if($num > 0){
			print $num;
			$a = 0;
			while ($a < $num) {
				$obj = $db->fetch_object($result);
				
					$sql2 = 'INSERT INTO '.MAIN_DB_PREFIX.'salarie_indemnite_flottante (fk_salarie, fk_indemnite, montant, date_debut) 
					VALUES ('.$obj->rowid.',"40","20000",now())';
					$result2 = $db->query($sql2);
			

					$sql1 = "INSERT INTO ".MAIN_DB_PREFIX."salarie_indemnite (fk_salarie, fk_indemnite, mois) VALUES ('".$obj->rowid."',40, now())";
					$result1 = $db->query($sql1);

					$a ++;
			}
		}
	}

	if($result1 && $result2)
		print "OK";

*/

	/*$sql = "SELECT * FROM ".MAIN_DB_PREFIX."user_extrafields";
							$res = $db->query($sql);
							$nb = $db->num_rows($res);
							$a = 0;
							while($a < $nb){ 
								$obj = $db->fetch_object($res);
								print "tms : ".$obj->tms." fk_object : ".$obj->fk_object." import_key : ".$obj->import_key." Egp : ".$obj->egp."<br>";
								$a ++;
							}*/

if($user->rights->paiementsalaire->configuration->read){
	
	//Saving file
	if($action == 'saveFile'){
		$nomDossier = 'import_salarie';
		$true = false;
		// Vérifier si le dossier n'existe pas déjà
		if (!file_exists($nomDossier)) {
			// Créer le dossier
			if (mkdir($nomDossier, 0777, true)) {
				$trouve = true;
			}
		}else{
			$trouve = true;
		}

		if($trouve){ //Le dossier est crée
			$nom_fichier = $_FILES['fichierAimporter']['name'];
			$chemin = $_FILES['fichierAimporter']['tmp_name'];
			$extension = strrchr($nom_fichier,".");
			$extension_autorisees = array('.xlsx');
			$destination = './import_salarie/'.date('d_m_y_h_i_s_').$nom_fichier;
			//$destination = DOL_DOCUMENT_ROOT.'/paiementsalaire/onglets/documents_contrat/contrat'.$fk_salarie.'__'.date('d_m_y_h_i_s').''.$extension;
			if(in_array($extension,$extension_autorisees)){
				if($_FILES['fichierAimporter']['size']<=1000000){
					if(move_uploaded_file($chemin,$destination)){
						$message .= "Fichier ajouté avec succès";
						$sql_select = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$user->id;
							$obj = $db->fetch_object($db->query($sql_select));

							//On garde la trace de l'action
							$action_effectue = "Ajout de fichier import (".$nom_fichier.") au compte de la société ".$obj_soc->nom;
							$sql_log = 'INSERT INTO '.MAIN_DB_PREFIX.'log (fk_user, nom, prenom, quand, action_effectue, object_concerne)';
							$sql_log .= ' VALUES('.$user->id.', "'.$obj->lastname.'","'.$obj->firstname.'",now(),"'.$action_effectue.'","Ajout")';
							$db->query($sql_log);
					}else $message .= "Un problème est survenu";
				}else $message .= 'Taille depassée';
			}else $message .= 'Extension non autorisés';
		}else $message .= 'Dossier de destination manquant.'; 
	}

	//Confirmation de la suppression
	if($action == 'dialogue'){
		$id_fichier = GETPOST("id_fichier", "alpha");
		$formconfirm = $monform->formconfirm(
			$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=societe&id_societe='.$id_societe.'&id_convention='.$id_convention.'&etape=2&id_fichier='.$id_fichier,
			'Veuillez confirmer cette suppression?',
			$text,
			'supprimer',
			'',
			'',
			1,
			40,
			'30%'
		);
				print $formconfirm;
	}

	if($action == 'supprimer'){
		$id_fichier = GETPOST("id_fichier", "alpha");
		$inode = $id_fichier;
		$directory = DOL_DOCUMENT_ROOT.'/paiementsalaire/onglets/import_salarie/';
		if ($handle = opendir($directory)) {
			// Lire les fichiers un par un
			while (false !== ($file = readdir($handle))) {
				// Ignorer les entrées spéciales '.' et '..'
				if ($file !== '.' && $file !== '..') {
					$filePath = $directory . '/' . $file;
					
					// Vérifier si l'inode du fichier correspond à celui recherché
					if ($file == $inode) {
						$nom_fichier = $file;
						// Supprimer le fichier
						if (unlink($filePath)) {
							$message .= "Fichier supprimé avec succès.";

							$sql_select = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$user->id;
							$obj = $db->fetch_object($db->query($sql_select));

							//On garde la trace de l'action
							$action_effectue = "Suppression de fichier import (".$nom_fichier.") au compte de la société ".$obj_soc->nom;
							$sql_log = 'INSERT INTO '.MAIN_DB_PREFIX.'log (fk_user, nom, prenom, quand, action_effectue, object_concerne)';
							$sql_log .= ' VALUES('.$user->id.', "'.$obj->lastname.'","'.$obj->firstname.'",now(),"'.$action_effectue.'","Suppression")';
							$db->query($sql_log);

						} else {
							$message .= "Erreur lors de la suppression du fichier.";
						}
					}
				}
			}
		} else {
			$message .= "Impossible d'ouvrir le répertoire.";
		}
	}

	$etape = GETPOST("etape", "int");
	if(empty($etape))
		$etape = 1;

	$array_etape = array();
	//Gestion des etapes

	if($etape == 1){	
		$array_etape[0][0] = "Etape 1";
		$array_etape[0][1] = $_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=societe&id_societe='.$id_societe.'&id_convention='.$id_convention.'&etape=1';

		print '<h2>Vous pouvez importer les salariés de <b>'.$obj_soc->nom.'</b></h2>';
		print '<h4><b>Étape 1 :</b> Générer et sauvegarder le fichier Excel avec les ID des salariés sur votre ordinateur.</h4>';
		print etape($array_etape);
	}elseif($etape == 2){
		$array_etape[0][0] = "Etape 1";
		$array_etape[0][1] = $_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=societe&id_societe='.$id_societe.'&id_convention='.$id_convention.'&etape=1';
		
		$array_etape[1][0] = "Etape 2";
		$array_etape[1][1] = $_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=societe&id_societe='.$id_societe.'&id_convention='.$id_convention.'&etape=2';

		print '<h4><b>Étape 2 :</b>  Renseigner chaque cellule du fichier fichier Excel. Ensuite, importez le fichier ainsi renseigné dans le module Salaire|Paie.</h4>';
		print etape($array_etape);
	}elseif($etape == 3){
		$array_etape[0][0] = "Etape 1";
		$array_etape[0][1] = $_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=societe&id_societe='.$id_societe.'&id_convention='.$id_convention.'&etape=1';
		
		$array_etape[1][0] = "Etape 2";
		$array_etape[1][1] = $_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=societe&id_societe='.$id_societe.'&id_convention='.$id_convention.'&etape=2';
					
		$array_etape[2][0] = "Etape 3";
		$id_fichier = GETPOST('id_fichier', 'alpha');
		$array_etape[2][1] = $_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=societe&id_societe='.$id_societe.'&id_convention='.$id_convention.'&etape=3&id_fichier='.$id_fichier;

		print "<h4><b>Étape 3 :</b>  Vérification et importation effective des données en cliquant sur 'Simuler'. Cela permet de détecter des erreurs et de les corriger, ou d'importer les données si la simulation est réussie.</h4>";
		print etape($array_etape);
	}
	print '<br><br>';

	//Tables des informations	
	if($etape == 1){	
		print '<div class="div-table-responsive-no-min">'; // You can use div-table-responsive-no-min if you dont need reserved height for your table
		print '<table class="noborder centpercent" cellpadding="4">';
		print '<tr class="liste_titre">';
		print '<td><label>Liste des formats autorisés</label></td><td></td><td></td></tr>';
		print '<tr>';
		print '<td><label>'.img_picto_common('xlsx', 'mime/xls').' Excel(.xlsx)</label></td>';
		print '<td><label><a href="./../doc/exemple_salarie.php?mainmenu=paiementsalaire&leftmenu=societe&id_societe='.$id_societe.'&id_convention='.$id_convention.'">Voir le fichier d\'exemple</a></label>';
		print '<td align="right"><label><a href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=societe&id_societe='.$id_societe.'&id_convention='.$id_convention.'&etape=2">'.img_picto('Importer', 'next', 'class="fa-15"').'</a></label>';
		print '</tr>';
		print '</table></div>';
		
	}elseif($etape == 2){
		$text = "Ajouter le fichier Excel".img_picto_common('xlsx', 'mime/xls')." à importer puis cliquer sur ".img_picto('', 'next');
		print '<span class="opacitymedium">';
			print $text;
		print '</span><br><br>';

		print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'?mainmenu=paiementsalaire&leftmenu=societe&id_convention='.$id_convention.'&id_societe='.$id_societe.'" enctype="multipart/form-data">';
		print '<input type="hidden" name="token" value="'.newToken().'">';
		print '<input type="hidden" name="action" value="saveFile">';
		print '<input type="hidden" name="etape" value="2">';
		print '<div class="marginbottomonly">';
		print '<input type="file" name="fichierAimporter" size="20" maxlength="80" required /> &nbsp; &nbsp; ';
		print '<input type="submit" class="button small" value="'.$langs->trans("AddFile").'"'.$out.' name="sendit">';
		print '</div>';
		print '</form>';
		print '<div class="div-table-responsive-no-min">'; // You can use div-table-responsive-no-min if you dont need reserved height for your table
		print '<table class="noborder centpercent" cellpadding="4">';

		//création du dossier
		$nomDossier = 'import_salarie';
		// Vérifier si le dossier n'existe pas déjà
		if (!file_exists($nomDossier))
			// Créer le dossier
			if (mkdir($nomDossier, 0777, true)) {
			}
		
		try{
			$directory = DOL_DOCUMENT_ROOT.'/paiementsalaire/onglets/import_salarie/';
			$iterator = new DirectoryIterator($directory);
			foreach ($iterator as $fileinfo) {
				if (!$fileinfo->isDot()) {
					$stat = stat($directory);
					echo '<tr>';
			
					// Colonne 1 : Nom du fichier avec lien de téléchargement
					echo '<td style="width: 50%">';
					echo img_picto_common('xlsx', 'mime/xls');
					echo '<label><a title="Télécharger" target="_blank" href="' . $_SERVER["PHP_SELF"] . '/../import_salarie/' . htmlspecialchars($fileinfo->getFilename()) . '">';
					echo htmlspecialchars($fileinfo->getFilename()) . '</a></label>';
					echo '</td>';
			
					// Colonne 2 : Taille du fichier
					echo '<td>' . $fileinfo->getSize() . ' Octets</td>';
			
					// Colonne 3 : Date de création du fichier
					echo '<td>' . date("d-m-Y H:i:s", $fileinfo->getCTime()) . '</td>';
			
					// Colonne 4 : Lien pour supprimer
					echo '<td align="right">';
					echo '<a href="' . htmlspecialchars($_SERVER["PHP_SELF"] . '?mainmenu=paiementsalaire&leftmenu=societe&id_societe=' . $id_societe . '&id_convention=' . $id_convention . '&etape=2&action=dialogue&id_fichier=' . $fileinfo->getFilename()) . '">';
					echo img_delete('Supprimer', '');
					echo '</a>';
					echo '</td>';
			
					// Colonne 5 : Lien pour l'étape suivante
					echo '<td align="right">';
					echo '<label><a href="' . htmlspecialchars($_SERVER["PHP_SELF"] . '?mainmenu=paiementsalaire&leftmenu=societe&id_societe=' . $id_societe . '&id_convention=' . $id_convention . '&etape=3&id_fichier=' . $fileinfo->getFilename()) . '">';
					echo img_picto('Etape suivante', 'next', 'class="fa-15"');
					echo '</a></label>';
					echo '</td>';
			
					echo '</tr>';
				}
			}
		} catch (Exception $e) {
			echo "Erreur : " . $e->getMessage();
		}
		print '</table></div>';
		
	}elseif($etape == 3){

		print '<div class="div-table-responsive-no-min">'; // You can use div-table-responsive-no-min if you dont need reserved height for your table
		print '<table class="noborder centpercent" cellpadding="4">';
		print '<tr>';
		$id_fichier = GETPOST('id_fichier', 'alpha');
		$chemin = "";
		$nom_fichier = "";
		try{
			$directory = DOL_DOCUMENT_ROOT.'/paiementsalaire/onglets/import_salarie/';
			$iterator = new DirectoryIterator($directory);
			foreach ($iterator as $fileinfo) {
				if (!$fileinfo->isDot()) {
					if($fileinfo->getFilename() == $id_fichier){
						print '<td style="width: 50%">Fichier source à importer<label></td><td><a title="Télécharger" target="_blank" href="'.$_SERVER["PHP_SELF"].'/../import_salarie/'.$fileinfo->getFilename().'">'.$fileinfo->getFilename().img_picto($langs->trans("Download"), 'download', 'class="paddingleft opacitymedium"').'</label></a></td>';
						print '</td>';
						$chemin = $directory.$fileinfo->getFilename();
						$nom_fichier = $fileinfo->getFilename();
					}
					

				}
			}
		} catch (Exception $e) {
			echo "Erreur : " . $e->getMessage();
		}

		//nombre de ligne à importer
		$spreadsheet = IOFactory::load($chemin);
		$sheet = $spreadsheet->getActiveSheet();
		$highestRow = $sheet->getHighestRow();
		$highestColumn = $sheet->getHighestColumn();
		$arry_champ_source = array();
		print '<tr>';
		print '<td style="width: 50%">Nombre de ligne à importer<label></td><td>'.($highestRow - 1).'</a></td>';
		print '</td></tr>';
		print '</table></div><br>';


		print '<div class="div-table-responsive-no-min">'; // You can use div-table-responsive-no-min if you dont need reserved height for your table
		print '<table class="noborder centpercent" cellpadding="4">';
		print '<tr class="liste_titre">';
		print '<td><label>Les champs dans le fichier source</label></td><td>Champ cible dans la base de donnée</td></tr>';
		//Lecture du fichier Excel(.xlsx)
		$row1 = 1;
		$row2 = 2;
		$targetFieldarray = array('Identifiant', 'Nom salarié', 'Prénom salarié', 'Genre', 'Poste', 'Matricule', 'Catégorie', 'Echelon', 'Situation familiale', 'Nb enfant', 'Nb enfant handicapé', 'N° I.N.P.S', 'N° A.M.O', 'N° compte', 'Sursalaire', 'Date anciennete', 'ID type banque');
		$targetFieldHelparray = array('Usernam (nom d\'utilisateur)',
									'Nom de famille du salarié',
									'Prénom du salarié',
									'Homme(man) ou Femme(woman)',
									'Poste ou Fonction occupée',
									'Matricule qui sera utilisé par SalairePaie',
									'Identifiant de la catégorié (dans le fichier Excel categorie.xlsx)',
									'Identifiant de l\échelon (dans le fichier Excel categorie.xlsx)',
									'marie, divorce, celibataire',
									'Nombre d\'enfant sans handicap',
									'Nombre enfant handicapé',
									'Numéro I.N.P.S',
									'Numéro A.M.O',
									'Numéro du compte (banque, orange money, etc.)',
									'Le sursalaire',
									'Date avec laquelle l\'anciennete serat calculé sinon on le calcul avec la date d\embauche(entrée)',
									'Identifioant du type de banque'
								);

		try{
			$indice = 0;
			for ($col = 'A'; $col <= $highestColumn; ++$col) {
				print '<tr >';
				$cell1 = $sheet->getCell($col . $row1);
				$cell2 = $sheet->getCell($col . $row2);
				print "<td class='fieldrequired'>".$cell1->getValue()."</td><td>".$targetFieldarray[$indice]."<span style='float:right'>".info_admin($targetFieldHelparray[$indice], 1)."</span></td>";
				print '</tr>';
				$indice ++;

			}
		} catch (Exception $e) {
			echo 'Erreur lors du chargement du fichier : ',  $e->getMessage();
		}
		print '</table></div>';

		

		$line_non_ok = 0;
		$tab_line_non_ok = array();
		if($action == 'simuler'){
		try{
			for ($row = 2; $row <= $highestRow; $row ++) {//row = 1 correspond aux titres des colonnes
				$trouve = true;
				$i = 0;
				$sql_user = "INSERT INTO llx_user (login, lastname, firstname, gender, job, dateemployement)";
				$sql_user .= "VALUES(";


				for($col = 'A'; $col <= $highestColumn; ++$col){

					$cell = $sheet->getCell($col . $row);
					$value = $cell->getValue();

					if($i <=5 ){
						if($col == 'A')
							$sql_user .= "'".$value."'";
						else $sql_user .= ",'".$value."'";
					}else{

					}

					/*if($col == 'A' && !is_int($value)){//Verifions si ID hs est un entier
						$line_non_ok ++;
						$trouve = false;
						$tab_line_non_ok[] = $row;
					}else{//Verifions si l'id du catégorie fait partie des catégories de la convention de la société
						$sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."dcotegories WHERE rowid=".$value." AND fk_convention=".$id_convention;
						$res = $db->query($sql);
						if($db->num_rows($res) <= 0){ //Vérifions que ce salarié existe
							$line_non_ok ++;
							$trouve = false;
							$tab_line_non_ok[] = $row;
						}

					}

					if($col == 'H' && !is_int($value) && $trouve){//Verifions si ID hs est un entier
						$line_non_ok ++;
						$trouve = false;
						$tab_line_non_ok[] = $row;
					}

					if($col == 'J' && !is_int($value) && $trouve){//Verifions si ID hs est un entier
						$line_non_ok ++;
						$trouve = false;
						$tab_line_non_ok[] = $row;
					}

					if($col == 'K' && !is_int($value) && $trouve){//Verifions si ID hs est un entier
						$line_non_ok ++;
						$trouve = false;
						$tab_line_non_ok[] = $row;
					}*/
					
					$i ++;
				}
				$sql_user .= ")";

				//print $sql_user;
				
			}

			$info_line = "Line : ";
			if(count($tab_line_non_ok)){
				for ($i=0; $i < count($tab_line_non_ok); $i++) { 
					if($i == 0)
						$info_line .= $tab_line_non_ok[$i];
					else $info_line .= ",".$tab_line_non_ok[$i];
				}
				
			}
			print '<br>';
			print '<div class="info">';
			print '<div class=""><b>Resultat de la simulation</b></div>';
			print "Nombre de lignes à inserer : ".($highestRow - $line_non_ok-1)."<br>";
			print "Nombre de lignes à ignorer : ".$line_non_ok."<br>";
			if(count($tab_line_non_ok))
				print "Les lignes contenant des erreurs : ".$info_line."<br>";
			print '</div>';
			print '<br>';
			} catch (Exception $e) {
				echo 'Erreur lors du chargement du fichier : ',  $e->getMessage();
			}
			if($line_non_ok ==  0){
				print '<div style="text-align:right"><label><a href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=societe&id_societe='.$id_societe.'&id_convention='.$id_convention.'&etape=3&action=simuler_ok&id_fichier='.$id_fichier.'" class="button">Importer les données</a></label></div>';
			}else{
				print '<h4>La simulation à échouer, veuillez corriger les lignes non correctes ou les supprimer du fichier excel et reprendre à partir de l\'étape N°2</h4>';
				print '<a href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=societe&id_societe='.$id_societe.'&id_convention='.$id_convention.'&etape=2&id_fichier='.$id_fichier.'" class="button">Etape 2</a>';

			}
		}else{
			if($indice != 17){//Si les champ du fichier ne correspondent pas aux champ de la table salarié Heure sup
						print '<h4>Le nombre de champ source est different du nombre de champ de la table cible. Corriger le fichier et revenir à l\'<a href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=societe&id_societe='.$id_societe.'&id_convention='.$id_convention.'&etape=2&id_fichier='.$id_fichier.'" class="button">Etape 2</a></h4>';
				print '<a href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=societe&id_societe='.$id_societe.'&id_convention='.$id_convention.'&etape=2&id_fichier='.$id_fichier.'" class="button">Etape 2</a>';
				print '<div style="text-align:right"><button class="button" disabled>Simuler</div>';
			}else
				if($action != 'simuler_ok'){
					print '<div style="text-align:right"><label><a href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=societe&id_societe='.$id_societe.'&id_convention='.$id_convention.'&etape=3&action=simuler&id_fichier='.$id_fichier.'" class="button">Simuler</a></label></div>';
				}else{// On va importer car la simulation s'est passée avec succès
					
				$array_donnee = array();
				$import_key = date('dmyhis');
				print $import_key;
				$j = 1;
				for ($row = 2; $row <= $highestRow; $row ++) {//row = 1 correspond aux titres des colonnes
					$trouve = true;
					$i = 0;
					for($col = 'A'; $col <= $highestColumn; ++$col){
						$cell = $sheet->getCell($col . $row);
						$value = $cell->getValue();
						//print $col.$row.'='.$value.'<br>';
						$array_donnee[$i] = $value;
						

						$i ++;	
					}
					
					$sex = 'man';
					if(strtolower($array_donnee[3]) != 'man' && strtolower($array_donnee[3]) != 'm' && strtolower($array_donnee[3]) != 'men')
						$sex = 'woman';
					//$montant_apayer = round($array_donnee[4]/$array_donnee[5], 2);
					if (is_numeric($array_donnee[5])) {
						// Convertir la date Excel au format PHP
						$date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($array_donnee[5]);
						$array_donnee[5] = $date->format('Y-m-d');
					}
				
					$sql = 'INSERT INTO '.MAIN_DB_PREFIX.'user (login, lastname, firstname, gender, job, dateemployment, import_key)
					VALUES ("'.$array_donnee[0].'","'.$array_donnee[1].'","'.$array_donnee[2].'", "'.$sex.'","'.$array_donnee[4].'","'.$array_donnee[5].'","'.$import_key.'")';
					$result = $db->query($sql);
					print $db->lasterror().'<br>';
					//Récupération de l'id du salarié qu'on vient d'enregistrer
					if($result){
						$result = $db->query("SELECT LAST_INSERT_ID() as rowid;");
						$obj = $db->fetch_object($result);
						$id_user =  $obj->rowid;

						//Affectation du salarié à la société courante
						$sql = 'INSERT INTO '.MAIN_DB_PREFIX.'user_extrafields (fk_object, egp, import_key)
						VALUES ("'.$id_user.'","'.$id_societe.'","'.$import_key.'")';

						$result = $db->query($sql);
						print $db->lasterror().'<br>';

						if($result){ //Enregistrement du salarié dans salairePaie
							
							/*$id_categ = 0;
							$id_echel = 0;
							$sql_categ = "SELECT rowid FROM ".MAIN_DB_PREFIX."dcategories WHERE fk_convention=".$id_convention." AND categorie LIKE '%".$array_donnee[6]."%'";
							$result = $db->query($sql_categ);
							if($db->num_rows($result) >0 ){
								$obj = $db->fetch_object($result);
								$id_categ = $obj->rowid;
								$sql_categ = "SELECT rowid FROM ".MAIN_DB_PREFIX."echelon WHERE fk_categorie".$obj->rowid." AND echelon LIKE '%".$array_donnee[7]."%'";
								$result = $db->query($sql_categ);
								if($db->num_rows($result) >0 ){
									$obj = $db->fetch_object($result);
									$id_echel = $obj->rowid;
								}
							}*/

							$situ_fam = 'celibataire';
							if(strtolower($array_donnee[8]) == 'm' || strtolower($array_donnee[8]) == 'marie' || strtolower($array_donnee[8]) == 'marié'){
								$situ_fam = 'marie';
							}

							if (!empty($array_donnee[15]) && is_numeric($array_donnee[15])) {
								// Convertir la date Excel au format PHP
								$date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($array_donnee[15]);
								$array_donnee[15] = $date->format('Y-m-d');
							};
							/*
							$sql = 'INSERT INTO '.MAIN_DB_PREFIX.'salarie (fk_user, matricule, fk_categorie, fk_echelon, situation_familiale, nombre_enfant, nombre_enfant_hand, inps, amo, compte, calcul_salaire, sursalaire, import_key)
							VALUES ('.$id_user.',"'$array_donnee[0].'",'.$id_categ.','.$id_echel.',"'.$situ_fam.'",'.$array_donnee[9].','.$array_donnee[10].',"'.$array_donnee[11].'","'.$array_donnee[12].'","'.$array_donnee[13].'","oui","'.$import_key.'")';

							*/
							$sql = 'INSERT INTO '.MAIN_DB_PREFIX.'salarie (fk_user, matricule, fk_categorie, fk_echelon, situation_familiale, nombre_enfant, nombre_enfant_hand, inps, amo, compte, sursalaire, calcul_salaire, import_key, fk_type_banque';

							if(!empty($array_donnee[15]))
								$sql .= ', date_anciennete';
							$sql .= ')';
							$sql .= ' VALUES ('.$id_user.',"'.$array_donnee[0].'",'.$array_donnee[6].','.$array_donnee[7].',"'.$situ_fam.'",'.$array_donnee[9].','.$array_donnee[10].',"'.$array_donnee[11].'","'.$array_donnee[12].'","'.$array_donnee[13].'","'.$array_donnee[14].'","oui","'.$import_key.'",'.$array_donnee[16];

							if(!empty($array_donnee[15]))
								$sql .= ',"'.$array_donnee[15].'"';

							$sql .= ')';
							$result = $db->query($sql);
							print $db->lasterror().'<br>';

							if($result){//Création d'un contrat pour lui

								$result = $db->query("SELECT LAST_INSERT_ID() as rowid;");
								$obj = $db->fetch_object($result);
								$fk_salarie =  $obj->rowid;

								$sql = 'INSERT INTO '.MAIN_DB_PREFIX.'salarie_contrat (fk_salarie,numero,fk_type_contrat,date_signature,date_embauche, active)
								VALUES ('.$fk_salarie.',"'.$array_donnee[0].'",2,"'.$array_donnee[5].'","'.$array_donnee[5].'",1)';
								$result = $db->query($sql);
								$db->lasterror().'<br>';

							}
							

						}
					}
					


					//print $sql.'<br>';
					$j ++;			
				}
				if($db->lasterror()){
					print '<h4>Echec d\'importation '.img_picto('Erreur', 'error').'</h4>';
					$message = $db->lasterror();
				}else{
					print '<h4>Importation effectuée avec succès '.img_picto('Succès', 'tick').'</h4>';
					$message = "Importation effectuée avec succès";

					$sql_select = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$user->id;
					$obj = $db->fetch_object($db->query($sql_select));

					//On garde la trace de l'action
					$action_effectue = "Import de ".$highestRow." salarié(s) de fichier salarié(".$nom_fichier.") au compte de la société ".$obj_soc->nom;
					$sql_log = 'INSERT INTO '.MAIN_DB_PREFIX.'log (fk_user, nom, prenom, quand, action_effectue, object_concerne)';
					$sql_log .= ' VALUES('.$user->id.', "'.$obj->lastname.'","'.$obj->firstname.'",now(),"'.$action_effectue.'","Import")';
					$db->query($sql_log);
				}


					
				}
		}
	}

	if(!empty($message))
		print "<script>
		$.jnotify('".$message."', {delay : 5000, fadeSpeed: 500});
		</script>";
	}else{
		print "<h2 style='align:center;'>Vous n'avez pas la permission voir cette liste</h2>";
	}

}else
	print "<h2 style='align:center;'>Fonctinnalité desactivée. Veuillez contancter le superAdmin</h2>";

}else{
	print "<h2 style='align:center;'>Vous n'avez pas la permission voir cette liste</h2>";
}



function etape($array_etape){

	$nb = count($array_etape);
	$text = "";
	for ($i=0; $i < $nb; $i++) {
		if($i < ($nb - 1)){
			$text .= "<a href='".$array_etape[$i][1]."'><button>".$array_etape[$i][0]."</button></a>";
			$text .= "&nbsp;>>";
		}else{
			$text .= "<a href='".$array_etape[$i][1]."'><button>".$array_etape[$i][0]."</button></a>";
		}
	}
	
	return $text;
}