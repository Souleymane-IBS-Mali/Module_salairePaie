<?php
require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/custom/paiementsalaire/lib/paiementsalaire.lib.php';

llxHeader("", "Paiement | Salaire");

print load_fiche_titre($langs->trans("Liste des personnels de la société"), '', '');

$id_societe = GETPOST('id_societe', 'int');
$id_convention = GETPOST('id_convention', 'int');

$limit_get = GETPOST('limit', 'alpha');
if ($limit_get == 'tout') {
	$limit = 0;
} else {
	$limit = GETPOST('limit', 'int') ?: 20;
}

$arret = GETPOST('arret', 'int') ?: 0;
$nb_page = GETPOST('nbpage', 'int') ?: 1;
$action = GETPOST('action', 'alpha');

if ($arret < 0) $arret = 0;
if ($nb_page < 1) $nb_page = 1;

$zero = false;
$message = "";

salarie_nb_jour($db, $id_societe);

if ($id_convention) {

	$head = paiementsalaireSocieteHead($id_societe, $id_convention);
	print dol_get_fiche_head($head, 'liste', "", -1, '');

	if ($user->rights->paiementsalaire->salarie->read) {

		$soc_sql = "SELECT * FROM ".MAIN_DB_PREFIX."societe WHERE rowid=".((int) $id_societe);
		$soc_res = $db->query($soc_sql);
		$obj_soc = ($soc_res ? $db->fetch_object($soc_res) : null);
		if (!$obj_soc) {
			$obj_soc = new stdClass();
			$obj_soc->rowid = (int) $id_societe;
			$obj_soc->nom = '';
		}

		$obj_soc->name = $obj_soc->nom;
		$obj_soc->element = "societe";
		$obj_soc->conv = $id_convention;

		societe_preview_next($db, $id_societe, $obj_soc);
		entete_societe($obj_soc, 'societe');

		$head2 = liste_salarie_SocieteHead($id_societe, $id_convention);
		print dol_get_fiche_head($head2, 'liste_salarie', "", -1, '');

		$recherche_nom = "";
		$recherche_prenom = "";
		$recherche_matricule = "";
		$recherche_fonction = "";
		$date_entree = "";

		if ($action == "recherche") {
			$recherche_nom = GETPOST("recherche_nom", "alphanohtml");
			$recherche_fonction = GETPOST("recherche_fonction", "alphanohtml");
			$recherche_prenom = GETPOST("recherche_prenom", "alphanohtml");
			$recherche_matricule = GETPOST("recherche_matricule", "alphanohtml");
			$date_entree = GETPOST("date_entree", "alphanohtml");
		}

		$tri = GETPOST('tri', 'alpha');

		$categorie = GETPOST('categorie', 'alpha');
		$fonction = GETPOST('fonction', 'alpha');
		$anciennete_case = GETPOST('anciennete', 'alpha');
		$solde_conge = GETPOST('solde_conge', 'alpha');

		$trouve = false;
		$obj_liste = array();

		// Archivage du salarié
		if ($action == "archiver") {
			$rowid = GETPOST('fk_salarie', 'int');
			$id = GETPOST('id', 'int');

			if (!empty($rowid)) {
				$sql_edit = "UPDATE ".MAIN_DB_PREFIX."salarie SET archiver='oui' WHERE rowid=".((int) $rowid);

				if ($db->query($sql_edit)) {
					$message = "Salarié archivé avec succès";

					$sql_select = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".((int) $id);
					$res_concern = $db->query($sql_select);
					$obj_concern = ($res_concern ? $db->fetch_object($res_concern) : null);
					if (!$obj_concern) {
						$obj_concern = (object) array('firstname' => '', 'lastname' => '');
					}

					$sql_select = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".((int) $user->id);
					$res_user_log = $db->query($sql_select);
					$obj = ($res_user_log ? $db->fetch_object($res_user_log) : null);
					if (!$obj) {
						$obj = (object) array('firstname' => '', 'lastname' => '');
					}

					$action_effectue = "Archivage d'un salarié ("
						.$db->escape($obj_concern->firstname)." "
						.$db->escape($obj_concern->lastname)
						." id_user=".((int) $id)
						." et fk_salarié=".((int) $rowid)
						.") de la société ".$db->escape($obj_soc->nom);

					$sql_log = 'INSERT INTO '.MAIN_DB_PREFIX.'log 
						(fk_user, nom, prenom, quand, action_effectue, object_concerne)';
					$sql_log .= ' VALUES(
						'.((int) $user->id).',
						"'.$db->escape($obj->lastname).'",
						"'.$db->escape($obj->firstname).'",
						NOW(),
						"'.$db->escape($action_effectue).'",
						"Archivage"
					)';

					$db->query($sql_log);

				} else {
					$message = "Un problème est survenu";
				}
			}
		}

		// Recherche
		$sql = "SELECT 
				sal.rowid as salrowid,
				sal.matricule,
				sal.fk_user,
				sal.fk_categorie,
				sal.fk_echelon,
				u.rowid,
				u.lastname,
				u.firstname,
				u.dateemployment,
				u.job,
				ue.fk_object,
				ue.egp 
			FROM ".MAIN_DB_PREFIX."user as u";

		$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."salarie as sal ON u.rowid = sal.fk_user";
		$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."user_extrafields as ue ON u.rowid = ue.fk_object";
		$sql .= " WHERE ue.egp = ".((int) $id_societe)." AND (sal.archiver != 'oui' OR sal.archiver IS NULL)";

		if (!empty($recherche_matricule)) {
			$sql .= " AND sal.matricule LIKE '%".$db->escape($recherche_matricule)."%'";
		}

		if (!empty($recherche_nom)) {
			$sql .= " AND (u.lastname LIKE '%".$db->escape($recherche_nom)."%' 
				OR u.firstname LIKE '%".$db->escape($recherche_nom)."%')";
		}

		if (!empty($recherche_prenom)) {
			$sql .= " AND (u.firstname LIKE '%".$db->escape($recherche_prenom)."%' 
				OR u.lastname LIKE '%".$db->escape($recherche_prenom)."%')";
		}

		if (!empty($date_entree)) {
			if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_entree)) {
				$sql .= " AND u.dateemployment = '".$db->escape($date_entree)."'";
			} elseif (is_numeric($date_entree)) {
				$annee = ((int) date("Y") - (int) $date_entree);
				$mois = (int) date("m");

				$sql .= " AND YEAR(u.dateemployment)=".$annee;
				$sql .= " AND MONTH(u.dateemployment)<=".$mois;
			}
		}

		if (!empty($recherche_fonction)) {
			$sql .= " AND u.job LIKE '%".$db->escape($recherche_fonction)."%'";
		}

		if ($tri == 'nom') {
			$sql .= " ORDER BY u.lastname";
		} elseif ($tri == 'prenom') {
			$sql .= " ORDER BY u.firstname";
		} elseif ($tri == 'matricule') {
			$sql .= " ORDER BY sal.matricule";
		} elseif ($tri == 'anciennete') {
			$sql .= " ORDER BY u.dateemployment";
		} elseif (!empty($recherche_matricule)) {
			$sql .= " ORDER BY sal.matricule";
		} else {
			$sql .= " ORDER BY u.lastname";
		}

		$result = $db->query($sql);

		if ($result) {
			$num = $db->num_rows($result);

			if ($num > 0) {
				$a = 0;

				while ($a < $num) {
					$obj_liste[] = $db->fetch_object($result);
					$a++;
				}
			} else {
				$zero = true;
				$num = 1;
			}
		}

		$num = count($obj_liste) == 0 ? 1 : count($obj_liste);

		$sel5 = "";
		$sel10 = "";
		$sel15 = "";
		$sel20 = "";
		$sel30 = "";
		$sel50 = "";
		$sel100 = "";
		$sel200 = "";
		$sel500 = "";
		$sel1000 = "";
		$seltout = "";

		if ($limit == 5) {
			$sel5 = "selected";
		} elseif ($limit == 10) {
			$sel10 = "selected";
		} elseif ($limit == 15) {
			$sel15 = "selected";
		} elseif ($limit == 20) {
			$sel20 = "selected";
		} elseif ($limit == 30) {
			$sel30 = "selected";
		} elseif ($limit == 50) {
			$sel50 = "selected";
		} elseif ($limit == 100) {
			$sel100 = "selected";
		} elseif ($limit == 200) {
			$sel200 = "selected";
		} elseif ($limit == 500) {
			$sel500 = "selected";
		} elseif ($limit == 1000) {
			$sel1000 = "selected";
		} else {
			$seltout = "selected";
		}

		if ($limit_get == 'tout' || $limit <= 0) {
			$limit = ($num > 0) ? $num : 1;
		}

		print "<div style='float:right; margin-right:20px;'>";
		print '<form name="ajouter" method="POST" action="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=salarie&id_convention='.$id_convention.'&id_societe='.$id_societe.'">';
		print '<input type="hidden" name="token" value="'.newToken().'">';
		print '<input type="hidden" name="action" value="recherche">';

		print "<select style='padding:10px' name='limit' id='limit'>";
		print "<option value='5' ".$sel5."><b>5</b></option>
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

		$total_pages = (((int) ($num % $limit)) == 0 ? ((int) ($num / $limit)) : ((int) ($num / $limit) + 1));

		print "<mark><b>".$nb_page."</b></mark>/<mark><b>".$total_pages."</b></mark>";

		print '<script type="text/javascript">
			var convention = document.getElementById("limit");

			convention.addEventListener("change", function () {
				var limit = convention.value;

				window.location.href = "'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=salarie&id_societe='.$id_societe.'&id_convention='.$id_convention.'&limit=" + limit + "&action=recherche&recherche_nom='.urlencode($recherche_nom).'&recherche_prenom='.urlencode($recherche_prenom).'&recherche_matricule='.urlencode($recherche_matricule).'&date_entree='.urlencode($date_entree).'&recherche_fonction='.urlencode($recherche_fonction).'&categorie='.$categorie.'&fonction='.$fonction.'&anciennete='.$anciennete_case.'&solde_conge='.$solde_conge.'&tri='.$tri.'";
			}, false);
		</script>';

		print "</div>";

		if ($zero) {
			$num = 0;
		}

		print "<br>";
		print '<div>';
		print '<table style="width: 100%;" class="tagtable liste">';

		$largeur = "20%";
		$nb_col = 0;

		if ($categorie == "on") $nb_col++;
		if ($fonction == "on") $nb_col++;
		if ($anciennete_case == "on") $nb_col++;
		if ($solde_conge == "on") $nb_col++;

		if ($nb_col == 1) {
			$largeur = "16%";
		} elseif ($nb_col == 2) {
			$largeur = "14%";
		} elseif ($nb_col == 3) {
			$largeur = "12%";
		} elseif ($nb_col == 4) {
			$largeur = "11%";
		}

		print '<tr class="liste_titre">';
		print '<td align="center" style="width: '.$largeur.';">
			<input style="padding:10px" type="text" size="'.$largeur.'" placeholder="Matricule" value="'.dol_escape_htmltag($recherche_matricule).'" name="recherche_matricule"></td>';

		print '<td align="center" style="width: '.$largeur.';">
			<input style="padding:10px" type="text" size="'.$largeur.'" placeholder="Nom" value="'.dol_escape_htmltag($recherche_nom).'" name="recherche_nom"></td>';

		print '<td align="center" style="width: '.$largeur.';">
			<input style="padding:10px" type="text" size="'.$largeur.'" placeholder="Prenom" value="'.dol_escape_htmltag($recherche_prenom).'" name="recherche_prenom"></td>';

		print '<td align="center" style="width: '.$largeur.';">
			<input style="padding:10px" type="date" size="'.$largeur.'" placeholder="Ancienneté" value="'.dol_escape_htmltag($date_entree).'" name="date_entree"></td>';

		if ($categorie == "on") {
			print '<td align="center" style="width: '.$largeur.';"></td>';
		}

		if ($fonction == "on") {
			print '<td align="center" style="width: '.$largeur.';">
				<input type="hidden" name="fonction" value="'.$fonction.'">
				<input style="padding:10px" type="text" size="'.$largeur.'" placeholder="Fonction" value="'.dol_escape_htmltag($recherche_fonction).'" name="recherche_fonction"></td>';
		}

		if ($anciennete_case == "on") {
			print '<td align="center" style="width: '.$largeur.';"></td>';
		}

		if ($solde_conge == "on") {
			print '<td align="center" style="width: '.$largeur.';"></td>';
		}

		print '<td align="center" colspan="2" style="width: '.$largeur.';">';
		print '<input type="submit" class="button" value="Rechercher">';
		print "</form>";
		print '<br><a href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=salarie&id_societe='.$id_societe.'&id_convention='.$id_convention.'&action=annuler_recherche" class="button">Annuler</a>';
		print '</td></tr>';

		$base_sort_url = $_SERVER["PHP_SELF"]
			.'?mainmenu=paiementsalaire'
			.'&leftmenu=salarie'
			.'&id_societe='.$id_societe
			.'&id_convention='.$id_convention
			.'&limit='.urlencode($limit_get)
			.'&action=recherche'
			.'&recherche_nom='.urlencode($recherche_nom)
			.'&recherche_prenom='.urlencode($recherche_prenom)
			.'&recherche_matricule='.urlencode($recherche_matricule)
			.'&date_entree='.urlencode($date_entree)
			.'&recherche_fonction='.urlencode($recherche_fonction)
			.'&categorie='.$categorie
			.'&fonction='.$fonction
			.'&anciennete='.$anciennete_case
			.'&solde_conge='.$solde_conge;

		print '<tr class="liste_titre">';
		print '<td align="center"><label><a title="Trié par le matricule" href="'.$base_sort_url.'&tri=matricule">Matricule</a></label></td>';
		print '<td align="center"><label><a title="Trié par le nom" href="'.$base_sort_url.'&tri=nom">Nom</a></label></td>';
		print '<td align="center"><label><a title="Trié par le prénom" href="'.$base_sort_url.'&tri=prenom">Prenom</a></label></td>';
		print '<td align="center"><label><a title="Trié par l\'ancienneté" href="'.$base_sort_url.'&tri=anciennete">Date d\'entrée</a></label></td>';

		$checked_categ = "";
		$checked_fonction = "";
		$checked_solde = "";
		$checked_anc = "";

		if ($categorie == "on") {
			print '<td align="center" style="width: '.$largeur.';">Catégorie</td>';
			$checked_categ = "checked";
		}

		if ($fonction == "on") {
			print '<td align="center" style="width: '.$largeur.';">Fonction</td>';
			$checked_fonction = "checked";
		}

		if ($anciennete_case == "on") {
			print '<td align="center" style="width: '.$largeur.';">Ancienneté</td>';
			$checked_anc = "checked";
		}

		if ($solde_conge == "on") {
			print '<td align="center" style="width: '.$largeur.';">Solde congé</td>';
			$checked_solde = "checked";
		}

		print '<td align="center" colspan="2"><label><span id="colonne_plus">'.img_picto('Ajouter ou enlever des colonnes', 'list').'</span></label>';

		$url = $_SERVER["PHP_SELF"]
			.'?mainmenu=paiementsalaire'
			.'&leftmenu=salarie'
			.'&id_societe='.$id_societe
			.'&id_convention='.$id_convention
			.'&limit='.urlencode($limit_get)
			.'&recherche_nom='.urlencode($recherche_nom)
			.'&recherche_prenom='.urlencode($recherche_prenom)
			.'&recherche_matricule='.urlencode($recherche_matricule)
			.'&date_entree='.urlencode($date_entree)
			.'&recherche_fonction='.urlencode($recherche_fonction)
			.'&tri='.$tri;

		print '<form name="ajouter_colonne" method="POST" action="'.$url.'">
			<div id="colonne" style="display:flex; flex-direction:column; position: absolute; border: solid 1px white; border-radius:5px; background-color:white; text-align:left; padding: 10px; width: 8%; box-shadow: 4px 4px 10px; margin-left: 100px">';

		print '<input type="hidden" name="token" value="'.newToken().'">';
		print '<input type="hidden" name="action" value="recherche">';
		print '<div><input type="checkbox" name="categorie" id="categorie" '.$checked_categ.'><label for="categorie">Catégorie</label></div>';
		print '<div><input type="checkbox" name="fonction" id="fonction" '.$checked_fonction.'><label for="fonction">Fonction</label></div>';
		print '<div><input type="checkbox" name="anciennete" id="anciennete" '.$checked_anc.'><label for="anciennete">Ancienneté</label></div>';
		print '<div><input type="checkbox" name="solde_conge" id="solde_conge" '.$checked_solde.'><label for="solde_conge">Solde congé</label></div>';
		print '<div><input class="button" type="submit" name="valider" value="Valider"></div>';
		print '</div></form>';
		print '</td>';
		print '</tr>';

		print '<script type="text/javascript">
			var colonne_plus = document.getElementById("colonne_plus");
			var colonne = document.getElementById("colonne");

			colonne.style.display = "none";

			colonne_plus.addEventListener("click", function () {
				if (colonne.style.display == "inline" || colonne.style.display == "block") {
					colonne.style.display = "none";
				} else {
					colonne.style.display = "block";
				}
			}, false);
		</script>';

		$num = count($obj_liste);
		$adresse = "";
		$i = $arret;

		while ($i < $num) {
			$class = "impair";

			if ($i % 2 == 0) {
				$class = "pair";
			}

			if (!empty($obj_liste[$i]->salrowid)) {
				print '<tr class="'.$class.'">';
				print '<td align="center">'.dol_escape_htmltag($obj_liste[$i]->matricule).'</td>';
				print '<td align="center">'.dol_escape_htmltag($obj_liste[$i]->lastname).'</td>';
				print '<td align="center">'.dol_escape_htmltag($obj_liste[$i]->firstname).'</td>';

				$anciennete = prime_anciennete($db, $obj_liste[$i]->salrowid, $id_convention, date('m'), date('Y'), $obj_liste[$i]->rowid);
				if (!is_array($anciennete)) {
					$anciennete = array(0);
				}
				if (!isset($anciennete[0])) {
					$anciennete[0] = 0;
				}

				print '<td align="center">'.dol_escape_htmltag($obj_liste[$i]->dateemployment).'</td>';

				if ($categorie == "on") {
					$categ = "N/A";

					$categorie_Sql = "SELECT code_categorie FROM ".MAIN_DB_PREFIX."dcategories WHERE rowid=".((int) $obj_liste[$i]->fk_categorie);
					$categorie_Result = $db->query($categorie_Sql);

					if ($categorie_Result) {
						$categorie_Salarie = $db->fetch_object($categorie_Result);
						if ($categorie_Salarie && !empty($categorie_Salarie->code_categorie)) {
							$categ = $categorie_Salarie->code_categorie;
						}
					}

					if ($obj_liste[$i]->fk_echelon !== null && $obj_liste[$i]->fk_echelon !== '') {
						$echelon_Sql = "SELECT libelle FROM ".MAIN_DB_PREFIX."echelon WHERE rowid=".((int) $obj_liste[$i]->fk_echelon);
						$echelon_Result = $db->query($echelon_Sql);

						if ($echelon_Result) {
							$echelon_Salarie = $db->fetch_object($echelon_Result);
							if ($echelon_Salarie && !empty($echelon_Salarie->libelle)) {
								$categ .= " ==> ".$echelon_Salarie->libelle;
							}
						}
					}

					print '<td align="center" style="padding: 5px; width: 20%;">'.dol_escape_htmltag($categ).'</td>';
				}

				if ($fonction == "on") {
					print '<td align="center" style="padding: 5px; width: 20%;">'.dol_escape_htmltag($obj_liste[$i]->job).'</td>';
				}

				if ($anciennete_case == "on") {
					print '<td align="center">'.dol_escape_htmltag($anciennete[0]).' an(s)</td>';
				}

				if ($solde_conge == "on") {
					$solde = 0;

					if (!empty($obj_liste[$i]->dateemployment)) {
						$date_donnee = new DateTime($obj_liste[$i]->dateemployment);
						$aujourdhui = new DateTime();

						$interval = $date_donnee->diff($aujourdhui);
						$jours = $interval->days;

						$jours = $interval->days % 365;

						$solde = (int) (floor($jours / 30) * 2.5);
					}

					print '<td align="center" style="padding: 5px; width: 20%;">'.$solde.'</td>';
				}

				print '<td align="center"><a href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=societe&id_societe='.$id_societe.'&fk_salarie='.$obj_liste[$i]->salrowid.'&id='.$obj_liste[$i]->rowid.'&id_convention='.$id_convention.'&action=archiver"><button title="Ce bouton permet d\'archiver ce salarié" class="button">Archiver</button></a></td>';

				print '<td align="center"><a title="Voir détails" href="./salarie_information.php?mainmenu=paiementsalaire&leftmenu=societe&fk_salarie='.$obj_liste[$i]->salrowid.'&id_societe='.$id_societe.'&id='.$obj_liste[$i]->rowid.'&id_convention='.$id_convention.'&action=detail"><button class="button">Détails</button></a></td>';

			} else {
				print '<tr class="'.$class.'">';
				print '<td align="center">'.dol_escape_htmltag($obj_liste[$i]->matricule).'</td>';
				print '<td align="center"><mark>'.dol_escape_htmltag($obj_liste[$i]->lastname).'</mark>'.info_admin("Ce salarié n'est pas enregistré", 1).'</td>';
				print '<td align="center"><mark>'.dol_escape_htmltag($obj_liste[$i]->firstname).'</mark></td>';

				$anciennete = prime_anciennete($db, $obj_liste[$i]->salrowid, $id_convention, date('m'), date('Y'), $obj_liste[$i]->rowid);
				if (!is_array($anciennete)) {
					$anciennete = array(0);
				}
				if (!isset($anciennete[0])) {
					$anciennete[0] = 0;
				}

				print '<td align="center">'.dol_escape_htmltag($obj_liste[$i]->dateemployment).'</td>';

				if ($categorie == "on") {
					print '<td align="center" style="padding: 5px; width: 20%;">N/A</td>';
				}

				if ($fonction == "on") {
					print '<td align="center" style="padding: 5px; width: 20%;">'.dol_escape_htmltag($obj_liste[$i]->job).'</td>';
				}

				if ($anciennete_case == "on") {
					print '<td align="center">'.dol_escape_htmltag($anciennete[0]).' an(s)</td>';
				}

				if ($solde_conge == "on") {
					$solde = 0;

					// Priorité à la date d'ancienneté du salarié
					if (!empty($obj_liste[$i]->salrowid)) {

						$sql_sal = "SELECT date_anciennete
									FROM ".MAIN_DB_PREFIX."salarie
									WHERE rowid=".(int) $obj_liste[$i]->salrowid;

						$req_sal = $db->query($sql_sal);

						if ($req_sal && ($obj_sal = $db->fetch_object($req_sal))) {
							if (!empty($obj_sal->date_anciennete)) {
								$date_anciennete = $obj_sal->date_anciennete;
							}
						}
					}
					// Si aucune date d'ancienneté n'existe, utiliser la date d'embauche
					if (empty($date_anciennete) && !empty($obj_liste[$i]->dateemployment)) {
						$date_anciennete = $obj_liste[$i]->dateemployment;
					}

					if (empty($date_anciennete))
            			$date_anciennete = date('Y-m-d');

					// Calcul du solde
					if (!empty($date_anciennete)) {

						$date_donnee = new DateTime($date_anciennete);
						$aujourdhui = new DateTime();

						$interval = $date_donnee->diff($aujourdhui);
						$jours = $interval->days % 365;

						$solde = (int) floor(($jours / 30) * 2.5);
					}

					print '<td align="center" style="padding: 5px; width: 20%;">'.$solde.'</td>';
				}

				print '<td align="center"><a href="./salarie_information.php?mainmenu=paiementsalaire&leftmenu=societe&id_societe='.$id_societe.'&fk_salarie='.$obj_liste[$i]->salrowid.'&id='.$obj_liste[$i]->rowid.'&id_convention='.$id_convention.'&action=edit"><button title="Ce bouton permet d\'enregistrer ce salarié" class="button">Enregistrer</button></a></td>';

				print '<td align="center"><a title="Voir détails" href="./salarie_information.php?mainmenu=paiementsalaire&leftmenu=societe&fk_salarie='.$obj_liste[$i]->salrowid.'&id_societe='.$id_societe.'&id='.$obj_liste[$i]->rowid.'&id_convention='.$id_convention.'&action=detail"><button class="button">Détails</button></a></td>';
			}

			print '</tr>';

			if ($i != 0 && (($i + 1) % $limit) == 0) {
				$arret = $i;
				$i = $num;
			} else {
				$i++;
			}
		}

		if (count($obj_liste) == 0) {
			print "<tr><td colspan='6' align='center'>Aucun salarié</td></tr>";
		}

		print '</table>';
		print '</div><br><br>';
		print '<div>';

		print '<span style="float:right; margin-left: 20px;">';

		if ($limit <= 0) {
			$limit = 1;
		}

		$nb = (((int) ($num % $limit)) == 0 ? ((int) ($num / $limit)) : ((int) ($num / $limit) + 1));

		$page_link = "";

		if ($num > $limit) {
			$base_url = $_SERVER["PHP_SELF"]
				."?mainmenu=paiementsalaire"
				."&leftmenu=salarie"
				."&id_societe=".$id_societe
				."&id_convention=".$id_convention
				."&limit=".urlencode($limit_get)
				."&action=recherche"
				."&recherche_nom=".urlencode($recherche_nom)
				."&recherche_prenom=".urlencode($recherche_prenom)
				."&recherche_matricule=".urlencode($recherche_matricule)
				."&date_entree=".urlencode($date_entree)
				."&recherche_fonction=".urlencode($recherche_fonction)
				."&categorie=".$categorie
				."&fonction=".$fonction
				."&anciennete=".$anciennete_case
				."&solde_conge=".$solde_conge
				."&tri=".$tri;

			if ($nb_page != 1) {
				$page_link .= "<a href='".$base_url."&arret=0&nbpage=1' style='padding: 5px'><b>Début</b></a>&nbsp;&nbsp;";
			}

			if ($arret > $limit) {
				if ($nb_page - 2 >= 1) {
					$page_link .= "<a href='".$base_url."&arret=".($limit * ($nb_page - 3))."&nbpage=".($nb_page - 2)."' style='padding: 5px'><b>".($nb_page - 2)."</b></a>&nbsp;&nbsp;";
				}

				if ($nb_page - 1 >= 1) {
					$page_link .= "<a href='".$base_url."&arret=".($limit * ($nb_page - 2))."&nbpage=".($nb_page - 1)."' style='padding: 5px'><b>".($nb_page - 1)."</b></a>&nbsp;&nbsp;";
				}

				$page_link .= "<a href='".$base_url."&arret=".($limit * ($nb_page - 1))."&nbpage=".$nb_page."' style='background-color: yellow; padding: 5px'><b>".$nb_page."</b></a>&nbsp;&nbsp;";

				if (($nb_page + 1) <= $nb) {
					$page_link .= "<a href='".$base_url."&arret=".($limit * $nb_page)."&nbpage=".($nb_page + 1)."' style='padding: 5px'><b>".($nb_page + 1)."</b></a>&nbsp;&nbsp;";
				}

				if (($nb_page + 2) <= $nb) {
					$page_link .= "<a href='".$base_url."&arret=".($limit * ($nb_page + 1))."&nbpage=".($nb_page + 2)."' style='padding: 5px'><b>".($nb_page + 2)."</b></a>&nbsp;&nbsp;";
				}

				if (($nb_page + 3) <= $nb) {
					$page_link .= "<a href='".$base_url."&arret=".($limit * ($nb_page + 2))."&nbpage=".($nb_page + 3)."' style='padding: 5px'><b>".($nb_page + 3)."</b></a>&nbsp;&nbsp;";
				}
			} else {
				if (1 <= $nb) {
					$page_link .= "<a href='".$base_url."&arret=0&nbpage=1' style='background-color: yellow; padding: 5px'><b>1</b></a>&nbsp;&nbsp;";
				}

				if (2 <= $nb) {
					$page_link .= "<a href='".$base_url."&arret=".$limit."&nbpage=2' style='padding: 5px'><b>2</b></a>&nbsp;&nbsp;";
				}

				if (3 <= $nb) {
					$page_link .= "<a href='".$base_url."&arret=".($limit * 2)."&nbpage=3' style='padding: 5px'><b>3</b></a>&nbsp;&nbsp;";
				}

				if (4 <= $nb) {
					$page_link .= "<a href='".$base_url."&arret=".($limit * 3)."&nbpage=4' style='padding: 5px'><b>4</b></a>&nbsp;&nbsp;";
				}

				if (5 <= $nb) {
					$page_link .= "<a href='".$base_url."&arret=".($limit * 4)."&nbpage=5' style='padding: 5px'><b>5</b></a>&nbsp;&nbsp;";
				}
			}

			if ($nb_page != $nb) {
				$page_link .= "<a href='".$base_url."&arret=".($limit * ($nb - 1))."&nbpage=".$nb."' style='padding: 5px'><b>Fin</b></a>&nbsp;&nbsp;";
			}
		}

		print $page_link.'</span>';

		if (count($obj_liste) > 0) {
			print '<a class="button" target="_blank" href="../doc/liste_personnel.php?id_societe='.$id_societe.'">Générer la liste PDF</a>';
			print '&nbsp;<a class="button" target="_blank" href="../doc/liste_personnel_excel.php?id_societe='.$id_societe.'&id_convention='.$id_convention.'">Générer la liste excel</a>';
		}

		print '</div>';

		if ($message != "") {
			print "<script>
				$.jnotify('".dol_escape_js($message)."', {delay : 5000, fadeSpeed: 500});
			</script>";
		}

	} else {
		print "<h2 style='align:center;'>Vous n'avez pas la permission de voir cette liste</h2>";
	}

} else {
	print "<h2 style='align:center;'>Veuillez affecter une convention à cette société</h2>";
}

llxFooter();
$db->free();