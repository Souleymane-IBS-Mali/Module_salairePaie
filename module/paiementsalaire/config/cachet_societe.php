<?php
require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/custom/paiementsalaire/lib/paiementsalaire.lib.php';


llxHeader("", "Paiement | Salaire");
$action =  GETPOST('action');
$id_societe =  GETPOST('id_societe', 'int');

if(empty($action))
	$action = 'liste';

	$array_id_soc = "(0";
	$sql = "SELECT fk_soc FROM ".MAIN_DB_PREFIX."societe_commerciaux";
	$sql .= " WHERE fk_user=".$user->id;
	$result = $db->query($sql);
	if($result){
		$i = 0;
		$num = $db->num_rows($result);
		while ($i < $num){
			$array_id_soc .= ", ".$db->fetch_object($result)->fk_soc;
			$i ++;
		}
	}
	$array_id_soc .= ")";


	$nomDossier = 'cachet_societe';
	if($action == "save_cachet"){
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
		$info = "accun cachet auparavant";
		if($trouve){ //Le dossier est crée
			$nom_fichier = $_FILES['fichierAimporter']['name'];
			$chemin = $_FILES['fichierAimporter']['tmp_name'];
			$extension = strrchr($nom_fichier,".");
			$extension_autorisees = array('.png', '.jpg', '.jpeg');
			$destination = './cachet_societe/'.$id_societe.$extension;

			$directory = DOL_DOCUMENT_ROOT.'/paiementsalaire/config/cachet_societe/';
			if(file_exists($directory.$id_societe.'.png')){
				$file = $id_societe.'.png';
				$filePath = $directory.$file;
				$info = "un ancien existait déjà";
				unlink($filePath);
			}elseif(file_exists($directory.$id_societe.'.jpg')){
				$file = $id_societe.'.jpg';
				$filePath = $directory.$file;
				$info = "un ancien existait déjà";
				unlink($filePath);
			}elseif(file_exists($directory.$id_societe.'.jpeg')){
				$file = $id_societe.'.jpeg';
				$filePath = $directory.$file;
				$info = "un ancien existait déjà";

				unlink($filePath);
			}
			//$destination = DOL_DOCUMENT_ROOT.'/paiementsalaire/onglets/documents_contrat/contrat'.$fk_salarie.'__'.date('d_m_y_h_i_s').''.$extension;
			if(in_array($extension,$extension_autorisees)){
				if($_FILES['fichierAimporter']['size']<=1000000){
					if(move_uploaded_file($chemin,$destination)){
						$message .= "Cachet ajouté avec succès<br>Il apparaîtra sur les bulletin après la cloture du mois";
						$sql_select = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$user->id;
						$obj = $db->fetch_object($db->query($sql_select));

						//On garde la trace de l'action
						$sql = "SELECT nom FROM ".MAIN_DB_PREFIX."societe WHERE rowid=".$id_societe;
						$res = $db->query($sql);
						$nom_societe = $db->fetch_object($res)->nom;

						$action_effectue = "Ajout cachet (".$nom_fichier.") à la société ".$nom_societe." ==>".$info;
						$sql_log = 'INSERT INTO '.MAIN_DB_PREFIX.'log (fk_user, nom, prenom, quand, action_effectue, object_concerne)';
						$sql_log .= ' VALUES('.$user->id.', "'.$obj->lastname.'","'.$obj->firstname.'",now(),"'.$action_effectue.'","Ajout")';
						$db->query($sql_log);

						$action = 'liste';
					}else $message .= "Un problème est survenu";
				}else $message .= 'Taille depassée';
			}else $message .= 'Extension non autorisés';
		}else $message .= 'Dossier de destination manquant.';
	}

if($action == 'liste'){
	print load_fiche_titre($langs->trans("Liste des société"), '', '');
	print '<span style="float:right;"><a title="Reglages" href="./reglages.php?mainmenu=paiementsalaire&leftmenu=reglage">Retour</a></span><br>';

	print '<table class="tagtable liste">';
	print '<tr class="liste_titre">';
	print '<td >Sociétés</td>';
	print '<td>Cachets</td>';


	print '</tr>';

	//societe:nom:rowid::rowid=($SEL$ fk_object from llx_societe_extrafields where grp=1)
   // $sql = "SELECT sce.fk_object from ".MAIN_DB_PREFIX."societe_extrafields as sce where grp=1";
   //recupération des société qui ont cochés géré paye; (case à cocher dans tiers)
	$sql = "SELECT sc.rowid as r1, sc.nom, sc.name_alias, sc.phone, sc.fax, sc.code_client, sc.zip, sce.rowid as r2, sce.fk_object, sce.conv FROM ".MAIN_DB_PREFIX."societe as sc";
	$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."societe_extrafields as sce ON sc.rowid=sce.fk_object WHERE sce.grp=1";
	if($user->id != 1)
		$sql .= " AND sc.rowid IN ".$array_id_soc;

	$sql .= " ORDER BY sc.rowid ASC";
	$result = $db->query($sql);

	if($result){
		$i = 0;
		$num = $db->num_rows($result);
		while ($i < $num){
		$societe = $db->fetch_object($result);

		print '<tr  class="pair">';
		print '<td> <a href="">'.$societe->nom.'</a></td>';

		$extension = '';
		if (file_exists('./cachet_societe/'.$societe->r1.'.png'))
			$extension = '.png';
		else if(file_exists('./cachet_societe/'.$societe->r1.'.jpeg'))
			$extension ='.jpeg';
		else $extension = '.jpg';

		if (file_exists('./cachet_societe/'.$societe->r1.'.png') || file_exists('./cachet_societe/'.$societe->r1.'.jpeg') || file_exists('./cachet_societe/'.$societe->r1.'.jpg'))
			print '<td aligh="right"><img height=20 src="./cachet_societe/'.$societe->r1.$extension.'" /><a href="./cachet_societe.php?id_societe='.$societe->r1.'&action=modifier_cachet"> '.img_edit('Modifier logo').'</a></td>';
		else print '<td aligh="right" ><a href="./cachet_societe.php?id_societe='.$societe->r1.'&action=modifier_cachet">'.img_edit('Modifier logo').'</a></td>';





		print '</tr>';
	$i++;
	$ligne ++;
		}
}

print '</table>';
}else{
	$sql = "SELECT nom FROM ".MAIN_DB_PREFIX."societe WHERE rowid=".$id_societe;
	$result = $db->query($sql);
	$societe = $db->fetch_object($result);

	//Titre
	print load_fiche_titre($langs->trans($societe->nom), '', '');
	print '<span style="float:right;"><a title="Reglages" href="./reglages.php?mainmenu=paiementsalaire&leftmenu=reglage">Retour</a></span><br>';

	//print '<hr>';

			print '<table class="tagtable liste">';
				print '<div><form name="add" method="POST" action="'.$_SERVER['PHP_SELF'].'?mainmenu=paiementsalaire&leftmenu=reglage&id_societe='.$id_societe.'&id_convention='.$id_convention.'" enctype="multipart/form-data">';
				print '<input type="hidden" name="token" value="'.newToken().'">';
				print '<input type="hidden" name="action" value="save_cachet">';
				print '<tr>';

				if (!file_exists($nomDossier))
					if (mkdir($nomDossier, 0777, true)) {
						$trouve = true;
					}

					$extension = '';
				if (file_exists('./cachet_societe/'.$id_societe.'.png'))
					$extension = '.png';
				else if(file_exists('./cachet_societe/'.$id_societe.'.jpeg'))
					$extension ='.jpeg';
				else $extension = '.jpg';

				if (file_exists('./cachet_societe/'.$id_societe.'.png') || file_exists('./cachet_societe/'.$id_societe.'.jpeg') || file_exists('./cachet_societe/'.$id_societe.'.jpg'))
					print '<td style="padding: 10px; width: 200px;">Modifier le logo <img height=20 src="./cachet_societe/'.$id_societe.$extension.'" /></td>';
				else print '<td style="padding: 10px; width: 200px;">Ajouter un logo</td>';
				//print '<div class="marginbottomonly">';
				print '<td><input type="file" name="fichierAimporter" size="20" maxlength="80" required /> &nbsp;'.info_admin('Les extensions autoriséees sont : PNG, JPG et JPEG',1).' &nbsp; ';
				print '<input type="submit" class="button small" value="'.$langs->trans("Ajouter").'"'.$out.' name="sendit">';
				print '<a class="reposition editfielda button" href="'.$_SERVER['PHP_SELF'].'?mainmenu=paiementsalaire&leftmenu=reglage">Annuler</a>';


				print '</form>';
				print '</td></tr>';
}

if(!empty($message))
		print "<script>
		$.jnotify('".$message."', {delay : 5000, fadeSpeed: 500});
		</script>";
