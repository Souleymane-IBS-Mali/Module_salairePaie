<?php
require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/custom/paiementsalaire/lib/paiementsalaire.lib.php';


llxHeader("", "Paiement | Salaire");
//Titre 
print load_fiche_titre($langs->trans("Imports avances/acomptes"), '', '');
//print '';
$id_convention = GETPOST('id_convention','int');
$id_societe = GETPOST('id_societe','int');
$action = GETPOST("action", "alpha");
$id_prime = GETPOST("id_prime","int");

require_once DOL_DOCUMENT_ROOT.'/includes/phpoffice/phpspreadsheet/src/autoloader.php';
require_once DOL_DOCUMENT_ROOT.'/includes/Psr/autoloader.php';
require_once PHPEXCELNEW_PATH.'Spreadsheet.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
$message = '';

$head = paiementsalaireSocieteHead($id_societe, $id_convention);
print dol_get_fiche_head($head, 'avance', "", -1, '');

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
			
			$monform = new Form($db);
	
	
			//Saving file
			if($action == 'saveFile'){
				$nomDossier = 'import_avance_acompte';
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
					$destination = './import_avance_acompte/'.date('d_m_y_h_i_s_').$nom_fichier;
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
				$directory = DOL_DOCUMENT_ROOT.'/paiementsalaire/onglets/import_avance_acompte/';
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

				print '<h4><b>Étape 1 :</b> Générer et sauvegarder le fichier Excel avec les ID des salariés sur votre ordinateur.</h4>';
				print etape($array_etape);
			}elseif($etape == 2){
				$array_etape[0][0] = "Etape 1";
				$array_etape[0][1] = $_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=societe&id_societe='.$id_societe.'&id_convention='.$id_convention.'&etape=1';
				
				$array_etape[1][0] = "Etape 2";
				$array_etape[1][1] = $_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=societe&id_societe='.$id_societe.'&id_convention='.$id_convention.'&etape=2';
				print '<h4><b>Étape 2 :</b>  Renseigner le fichier Excel avec les montants des avances que vous souhaitez allouer aux salariés (vous pouvez renseigner uniquement les salariés auxquels vous voulez affecter une avance). Ensuite, importez le fichier ainsi renseigné dans le module Salaire|Paie.</h4>';
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
				print '<td><label><a href="./../doc/exemple_avance.php?mainmenu=paiementsalaire&leftmenu=societe&id_societe='.$id_societe.'&id_convention='.$id_convention.'">Voir le fichier d\'exemple</a></label>';
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
				$nomDossier = 'import_avance_acompte';
				// Vérifier si le dossier n'existe pas déjà
				if (!file_exists($nomDossier))
					// Créer le dossier
					if (mkdir($nomDossier, 0777, true)) {
					}
				
				try{
					$directory = DOL_DOCUMENT_ROOT.'/paiementsalaire/onglets/import_avance_acompte/';

					$dossier = $directory; // Remplacez par le chemin du dossier



					$iterator = new DirectoryIterator($directory);
					foreach ($iterator as $fileinfo) {
						if (!$fileinfo->isDot()) {
							
							print '<tr>';
							print '<td style="width: 50%">'.img_picto_common('xlsx', 'mime/xls').'<label> <a title="Télécharger" target="_blank" href="'.$_SERVER["PHP_SELF"].'/../import_avance_acompte/'.$fileinfo->getFilename().'">'.$fileinfo->getFilename().'</label></a></td>';
							print '<td>'.$fileinfo->getSize().'Octets</label></td>';
							print '<td>'.date("d-m-Y H:i:s", $fileinfo->getCTime()).'</label></td>';
							print '<td align="right"><a href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=societe&id_societe='.$id_societe.'&id_convention='.$id_convention.'&etape=2&action=dialogue&id_fichier='.$fileinfo->getFilename().'">'.img_delete('Supprimer', '').'</a></label>';
							print '<td align="right"><label><a href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=societe&id_societe='.$id_societe.'&id_convention='.$id_convention.'&etape=3&id_fichier='.$fileinfo->getFilename().'">'.img_picto('Etape suivante', 'next', 'class="fa-15"').'</a></label>';
							print '</tr>';
	
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
					$directory = DOL_DOCUMENT_ROOT.'/paiementsalaire/onglets/import_avance_acompte/';
					$iterator = new DirectoryIterator($directory);
					foreach ($iterator as $fileinfo) {
						if (!$fileinfo->isDot()) {
							if($fileinfo->getFilename() == $id_fichier){
								print '<td style="width: 50%">Fichier source à importer<label></td><td><a title="Télécharger" target="_blank" href="'.$_SERVER["PHP_SELF"].'/../import_avance_acompte/'.$fileinfo->getFilename().'">'.$fileinfo->getFilename().img_picto($langs->trans("Download"), 'download', 'class="paddingleft opacitymedium"').'</label></a></td>';
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
				$targetFieldarray = array('ID salarie', 'Nom salarié', 'Prénom salarié', 'Libellé avance/acompte', 'Montant avance/acompte', 'A payer sur combien de mois', 'Mois debut paiement', 'Année debut paiement');
				$targetFieldHelparray = array('Ne modifiez pas les "ID" du fichier exemple',
											'Les modifications des Nom ne seront pas prisent en compte',
											'Les modifications des Préom ne seront pas prisent en compte',
											'Une chaîne de caractère qui décrit l\'avance/acompte',
											'Montant total de l\'avance/acompte',
											'Avance est à payée sur combien de mois',
											'Numéro du mois auquel le paiement doit commencé',
											'L\'année à laquelle le paiement doit commencé'
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
						$fk_salarie = 0;
						$trouve = true;
						$i = 0;
						for($col = 'A'; $col <= $highestColumn; ++$col){
							$cell = $sheet->getCell($col . $row);
							$value = $cell->getValue();
							$value = (int) $value;
							if($col == 'A'){
								if(is_integer($value)){ //Verifions si ID salarié est un entier
									$fk_salarie = $value;
									$sql = "SELECT * FROM ".MAIN_DB_PREFIX."salarie WHERE rowid=".$value;
									$res = $db->query($sql);
									if($db->num_rows($res) <= 0){ //Vérifions que ce salarié existe
										$line_non_ok ++;
										$trouve = false;
										$tab_line_non_ok[] = 'A';
									}
								}else{//ID salarié est un entier alors verifions si le salarié correspondant a cet ID existe
									$line_non_ok ++;
									$trouve = false;
									$tab_line_non_ok[] = "B";
								}
							}
							if($col == 'E' && !is_integer($value) && $trouve){//Verifions que le montant est un entier
								$line_non_ok ++;
								$trouve = false;
								$tab_line_non_ok[] = "E";
							}elseif($col == 'E'){
								$taux = 33;
								$salaire_net = 0;
								//verifions si un taux limit à été fixé aux avance/Acompte de cette société
								$regle_avance_acompte = "SELECT taux FROM ".MAIN_DB_PREFIX."regle_avance_acompte WHERE fk_societe=".$id_societe;
								$result_regle_avance_acompte = $db->query($regle_avance_acompte);
								if($db->num_rows($result_regle_avance_acompte) > 0){
									$obj_regle_avance_acompte = $db->fetch_object($result_regle_avance_acompte);
									$taux = $obj_regle_avance_acompte->taux;
								}

								//récupération du salaire net du salarié stipulé par le contrat
								$sql_contrat = "SELECT rowid FROM ".MAIN_DB_PREFIX."salarie_contrat WHERE fk_salarie=".$fk_salarie." AND active=1";
								$res_contrat = $db->query($sql_contrat);
								if($db->num_rows($res_contrat) > 0){
									$obj_contrat = $db->fetch_object($res_contrat);
									$sql_salaire_net  = "SELECT salaire_net FROM ".MAIN_DB_PREFIX."salarie_contrat_salaire_net WHERE active=1 AND fk_contrat=".$obj_contrat->rowid;
									$res_salaire_net  = $db->query($sql_salaire_net );
									$obj_salaire_net = $db->fetch_object($res_salaire_net);
									$salaire_net = $obj_salaire_net->salaire_net;
								}

								//les avances de ce salarié en cours de paiement
								$cel = $sheet->getCell("F" . $row);
								$val = $cel->getValue();
								$montant_total_par_mois = round($value/$val);
								$mois = date('m');
								$annee = date('Y');
								$sql = "SELECT rowid, montant_par_mois, montant_paye, montant_total FROM ".MAIN_DB_PREFIX."salarie_avance WHERE fk_salarie=".$fk_salarie;
								$sql .= " AND (montant_paye < montant_total OR  (montant_paye = montant_total AND ((annee_debut_paiement=".$annee." AND mois_debut_paiement<=".$mois."))))";
								$result = $db->query($sql);
								if($result){
									$i = 0;
									$num = $db->num_rows($result);
									while ($p < $num){
										$obj = $db->fetch_object($result);
										if($obj->montant_paye == $obj->montant_total){
											$sql_detail_avance  = "SELECT * FROM ".MAIN_DB_PREFIX."detail_avance WHERE annee_paiement=".$annee." AND mois_paiement=".$mois." AND fk_avance=".$obj->rowid;
											$res_detail_avance = $db->query($sql_detail_avance);
											if($res_detail_avance)
												if($db->num_rows($res_detail_avance) > 0){
													$montant_total_par_mois += $obj->montant_par_mois;
												}
										}else{
											$montant_total_par_mois += $obj->montant_par_mois;
										}
										
										$p ++;
									}
								}
								if(!empty($taux) && ($salaire_net*$taux/100) < $montant_total_par_mois && $trouve){
									$line_non_ok ++;
									$trouve = false;
									$tab_line_non_ok[] = $row.''.info_admin('Le taux autorisé est depassé. Total des avance ='.$montant_total_par_mois.'; la limite dans la configuration ='.($salaire_net*$taux/100) ,1);
								}
							}

							if($col == 'F' && !is_int($value) && $trouve){//Verifions que le nombre de mois est un entier
								$line_non_ok ++;
								$trouve = false;
								$tab_line_non_ok[] = $row;
							}

							if($col == 'G' && !is_int($value) && $trouve){//Verifions que le nombre de mois debut paiement est un entier
								$line_non_ok ++;
								$trouve = false;
								$tab_line_non_ok[] = $row;
							}

							if($col == 'H' && !is_int($value) && $trouve){//Verifions que l'année debut paiement est un entier
								$line_non_ok ++;
								$trouve = false;
								$tab_line_non_ok[] = $row;
							}
							
							$i ++;
						}
						
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
					if($indice != 8){//Si les champ du fichier ne correspondent pas aux champ de la table salarié Heure sup
						print '<h4>Le nombre de champ source est different du nombre de champ de la table cible. Corriger le fichier et revenir à l\'<a href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=societe&id_societe='.$id_societe.'&id_convention='.$id_convention.'&etape=2&id_fichier='.$id_fichier.'" class="button">Etape 2</a></h4>';
						print '<div style="text-align:right"><button class="button" disabled>Simuler</div>';
					}else
						if($action != 'simuler_ok'){
							print '<div style="text-align:right"><label><a href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=societe&id_societe='.$id_societe.'&id_convention='.$id_convention.'&etape=3&action=simuler&id_fichier='.$id_fichier.'" class="button">Simuler</a></label></div>';
						}else{// On va importer car la simulation s'est passée avec succès
							
						$import_key = date('dmyhis');
						for ($row = 2; $row <= $highestRow; $row ++) {//row = 1 correspond aux titres des colonnes
							$array_donnee = array();
							$trouve = true;
							$i = 0;
							for($col = 'A'; $col <= $highestColumn; ++$col){
								$cell = $sheet->getCell($col . $row);
								$value = $cell->getValue();
								$array_donnee[] = $value;
								$i ++;		
							}
							if(!empty($array_donnee[4])){
								$montant_apayer = round($array_donnee[4]/$array_donnee[5], 2);

								$sql = 'INSERT INTO '.MAIN_DB_PREFIX.'salarie_avance(fk_salarie, libelle, montant_total, montant_par_mois, nombre_mois, mois_debut_paiement, annee_debut_paiement, montant_paye, import_key)
								VALUES ("'.$array_donnee[0].'","'.$array_donnee[3].'","'.$array_donnee[4].'", "'.$montant_apayer.'",'.$array_donnee[5].', '.$array_donnee[6].','.$array_donnee[7].',"0","'.$import_key.'")';
								//print $sql;
								$result = $db->query($sql);
								print $db->error().'<br>';
							}			
						}
						if($db->error()){
							print '<h4>Importation effectuée avec succès '.img_picto('Succès', 'tick').'</h4>';
							$message = "Importation effectuée avec succès";

							$sql_select = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$user->id;
							$obj = $db->fetch_object($db->query($sql_select));

							//On garde la trace de l'action
							$action_effectue = "Import heure sup fichier(".$nom_fichier.") au compte de la société ".$obj_soc->nom;
							$sql_log = 'INSERT INTO '.MAIN_DB_PREFIX.'log (fk_user, nom, prenom, quand, action_effectue, object_concerne)';
							$sql_log .= ' VALUES('.$user->id.', "'.$obj->lastname.'","'.$obj->firstname.'",now(),"'.$action_effectue.'","Import")';
							$db->query($sql_log);
						}else{
							$message = "Il y a une erreur dans ton fichier Excel<br>";
							$message .= "Evitez de mettre des formule Excel dans le fichier importer<br>";
							$message .= "Par exemple mettez : 400 au lieu de =40*10<br>";
						}
							
					}
				}
			}

			
		}else
		 print "<h2>Cette société n'a aucun salarié</h2>";

	if(!empty($message))
			print "<script>
			$.jnotify('".$message."', {delay : 5000, fadeSpeed: 500});
			</script>";
}else{
	print "<h2>Veuillez affecter une <b>convention</b> à cette société</h2>";
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