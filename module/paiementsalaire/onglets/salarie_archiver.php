<?php
require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/custom/paiementsalaire/lib/paiementsalaire.lib.php';

llxHeader("", "Paiement | Salaire");

// Titre
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

// Par defaut tous les salariés ont travaillé le maximum de jours du mois en cours
// salarie_nb_jour($db, $id_societe);

$head = paiementsalaireSocieteHead($id_societe, $id_convention);
print dol_get_fiche_head($head, 'liste', "", -1, '');

if ($user->rights->paiementsalaire->salarie->read) {

	$soc_sql = "SELECT * FROM ".MAIN_DB_PREFIX."societe WHERE rowid=".((int) $id_societe);
	$soc_res = $db->query($soc_sql);
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
	$recherche_anciennete = "";

	if ($action == "recherche") {
		$recherche_nom = GETPOST("recherche_nom", "alphanohtml");
		$recherche_prenom = GETPOST("recherche_prenom", "alphanohtml");
		$recherche_matricule = GETPOST("recherche_matricule", "alphanohtml");
		$recherche_anciennete = GETPOST("recherche_anciennete", "int");
	}

	$trouve = false;
	$obj_liste = array();

	// Désarchivage du salarié
	if ($action == 'desarchiver') {
		$rowid = GETPOST('fk_salarie', 'int');
		$id = GETPOST('id', 'int');

		if (!empty($rowid)) {
			$sql_edit = "UPDATE ".MAIN_DB_PREFIX."salarie SET archiver='non' WHERE rowid = ".((int) $rowid);

			if ($db->query($sql_edit)) {

				// Si le salarié trouve un bulletin non clôturé, on crée les jours travaillés si nécessaire
				$bulletin_sql = "SELECT mois, annee 
					FROM ".MAIN_DB_PREFIX."bulletin 
					WHERE cloture='non' 
					AND fk_societe=".((int) $id_societe)." 
					ORDER BY date_creation DESC";

				$res_bulletin = $db->query($bulletin_sql);

				if ($res_bulletin) {
					$obj_bulletin = $db->fetch_object($res_bulletin);

					if ($obj_bulletin) {
						$salSql = "SELECT rowid 
							FROM ".MAIN_DB_PREFIX."salarie_nombre_jour_travaille 
							WHERE fk_salarie=".((int) $rowid)." 
							AND annee=".((int) $obj_bulletin->annee)." 
							AND mois=".((int) $obj_bulletin->mois);

						$result = $db->query($salSql);
						$num = $result ? $db->num_rows($result) : 0;

						if ($num <= 0) {
							$jour = cal_days_in_month(CAL_GREGORIAN, (int) $obj_bulletin->mois, (int) $obj_bulletin->annee);

							$sql = 'INSERT INTO '.MAIN_DB_PREFIX.'salarie_nombre_jour_travaille 
								(fk_societe, fk_salarie, annee, mois, jour)';
							$sql .= ' VALUES(
								'.((int) $id_societe).',
								'.((int) $rowid).',
								'.((int) $obj_bulletin->annee).',
								'.((int) $obj_bulletin->mois).',
								'.((int) $jour).'
							)';

							$db->query($sql);
						}
					}
				}

				$message = "Salarié désarchivé avec succès";

				$sql_select = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".((int) $id);
				$obj_concern = $db->fetch_object($db->query($sql_select));

				$sql_select = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".((int) $user->id);
				$obj = $db->fetch_object($db->query($sql_select));

				$action_effectue = "Désarchivage d'un salarié ("
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
					"Désarchivage"
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
			u.rowid,
			u.lastname,
			u.firstname,
			u.dateemployment,
			ue.fk_object,
			ue.egp 
		FROM ".MAIN_DB_PREFIX."salarie as sal";

	$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."user as u ON u.rowid = sal.fk_user";
	$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."user_extrafields as ue ON u.rowid = ue.fk_object";
	$sql .= " WHERE ue.egp = ".((int) $id_societe)." AND sal.archiver = 'oui'";

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

	if ($recherche_anciennete !== '' && $recherche_anciennete !== null) {
		$annee = ((int) date("Y") - (int) $recherche_anciennete);
		$mois = (int) date("m");
		$jour = (int) date("d");

		$sql .= " AND YEAR(u.dateemployment)=".$annee;
		$sql .= " AND MONTH(u.dateemployment)<=".$mois;
		$sql .= " AND DAY(u.dateemployment)<=".$jour;
	}

	if (!empty($recherche_matricule)) {
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

			window.location.href = "'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=salarie&id_societe='.$id_societe.'&id_convention='.$id_convention.'&limit=" + limit + "&action=recherche&recherche_nom='.urlencode($recherche_nom).'&recherche_prenom='.urlencode($recherche_prenom).'&recherche_matricule='.urlencode($recherche_matricule).'&recherche_anciennete='.urlencode($recherche_anciennete).'";
		}, false);
	</script>';

	print "</div>";

	if ($zero) {
		$num = 0;
	}

	print "<br>";
	print '<div>';
	print '<table style="width: 100%;" class="tagtable liste">';
	print '<tr class="liste_titre">';

	print '<td align="center" style="padding: 5px; width: 20%;">
	<input style="padding:10px" type="text" placeholder="Nom" value="'.dol_escape_htmltag($recherche_nom).'" name="recherche_nom">
	<br><label>Nom</label></td>';

	print '<td align="center" style="padding: 5px; width: 20%;">
	<input style="padding:10px" type="text" placeholder="Prenom" value="'.dol_escape_htmltag($recherche_prenom).'" name="recherche_prenom">
	<br><label>Prenom</label></td>';

	print '<td align="center" style="padding: 5px; width: 20%;">
	<input style="padding:10px" type="text" placeholder="Matricule" value="'.dol_escape_htmltag($recherche_matricule).'" name="recherche_matricule">
	<br><label>Matricule</label></td>';

	print '<td align="center" style="padding: 5px; width: 20%;">
	<input style="padding:10px" type="text" placeholder="Ancienneté" value="'.dol_escape_htmltag($recherche_anciennete).'" name="recherche_anciennete">
	<br><label>Ancienneté</label></td>';

	print '<td align="center" style="padding: 5px; width: 20%;">';
	print '<input type="submit" class="button" value="Rechercher">';
	print "</form>";
	print '<br><a href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=salarie&id_societe='.$id_societe.'&id_convention='.$id_convention.'&action=annuler_recherche" class="button">Annuler</a>';
	print '</td></tr>';

	$num = count($obj_liste);
	$adresse = "";
	$i = $arret;

	while ($i < $num) {
		$class = "impair";

		if ($i % 2 == 0) {
			$class = "pair";
		}

		print '<tr class="'.$class.'">';
		print '<td align="center">'.dol_escape_htmltag($obj_liste[$i]->lastname).'</td>';
		print '<td align="center">'.dol_escape_htmltag($obj_liste[$i]->firstname).'</td>';
		print '<td align="center">'.dol_escape_htmltag($obj_liste[$i]->matricule).'</td>';

		// Calcul de l'ancienneté
		$anciennete = prime_anciennete($db, $obj_liste[$i]->salrowid, $id_convention, date('m'), date('Y'), $obj_liste[$i]->rowid);

		print '<td align="center">'.dol_escape_htmltag($anciennete[0]).'</td>';

		print '<td align="center">
			<a href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=societe&id_societe='.$id_societe.'&fk_salarie='.$obj_liste[$i]->salrowid.'&id='.$obj_liste[$i]->rowid.'&id_convention='.$id_convention.'&action=desarchiver">
				<button title="Ce bouton permet de désarchiver ce salarié" class="button">Désarchiver</button>
			</a>
		</td>';

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
			."&limit=".$limit_get
			."&action=recherche"
			."&recherche_nom=".urlencode($recherche_nom)
			."&recherche_prenom=".urlencode($recherche_prenom)
			."&recherche_matricule=".urlencode($recherche_matricule)
			."&recherche_anciennete=".urlencode($recherche_anciennete);

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
		print '<a class="button" target="_blank" href="../doc/liste_personnel.php?id_societe='.$id_societe.'">Générer la liste</a>';
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

llxFooter();
$db->free();