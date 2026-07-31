<?php
/* Copyright (C) 2022 SuperAdmin
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file    paiementsalaire/lib/paiementsalaire.lib.php
 * \ingroup paiementsalaire
 * \brief   Library files with common functions for PaiementSalaire
 */

/**
 * Prepare admin pages header
 *
 * @return array
 */
function paiementsalaireAdminPrepareHead()
{
	global $langs, $conf;

	$langs->load("paiementsalaire@paiementsalaire");

	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath("/paiementsalaire/admin/setup.php", 1);
	$head[$h][1] = $langs->trans("Settings");
	$head[$h][2] = 'settings';
	$h++;

	/*
	$head[$h][0] = dol_buildpath("/paiementsalaire/admin/myobject_extrafields.php", 1);
	$head[$h][1] = $langs->trans("ExtraFields");
	$head[$h][2] = 'myobject_extrafields';
	$h++;
	*/

	$head[$h][0] = dol_buildpath("/paiementsalaire/admin/about.php", 1);
	$head[$h][1] = $langs->trans("About");
	$head[$h][2] = 'about';
	$h++;

	$head[$h][0] = dol_buildpath("/paiementsalaire/admin/convention.php", 1);
	$head[$h][1] = $langs->trans("Coventions");
	$head[$h][2] = 'convention';
	$h++;

	// Show more tabs from modules
	// Entries must be declared in modules descriptor with line
	//$this->tabs = array(
	//	'entity:+tabname:Title:@paiementsalaire:/paiementsalaire/mypage.php?id=__ID__'
	//); // to add new tab
	//$this->tabs = array(
	//	'entity:-tabname:Title:@paiementsalaire:/paiementsalaire/mypage.php?id=__ID__'
	//); // to remove a tab
	complete_head_from_modules($conf, $langs, null, $head, $h, 'paiementsalaire@paiementsalaire');

	complete_head_from_modules($conf, $langs, null, $head, $h, 'paiementsalaire@paiementsalaire', 'remove');

	return $head;
}


//les onglets de la convention
function paiementsalaireConventionHead($id_convention = 0)
{
	global $langs, $conf;

	$langs->load("paiementsalaire@paiementsalaire");

	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath("./paiementsalaire/convention/onglets/convention_information.php?mainmenu=paiementsalaire&leftmenu=convention&id_convention=".$id_convention, 1);
	$head[$h][1] = $langs->trans("Information");
	$head[$h][2] = 'information';
	$h++;

	$head[$h][0] = dol_buildpath("./paiementsalaire/convention/onglets/convention_categorie.php?mainmenu=paiementsalaire&leftmenu=convention&id_convention=".$id_convention, 1);
	$head[$h][1] = $langs->trans("Catégories");
	$head[$h][2] = 'categorie';
	$h++;

	/*$head[$h][0] = dol_buildpath("./paiementsalaire/convention/onglets/detail.php?mainmenu=paiementsalaire&leftmenu=convention&id_convention=".$id_convention, 1);
	$head[$h][1] = $langs->trans("");
	$head[$h][2] = 'categorie';
	$h++;*/

	$head[$h][0] = dol_buildpath("./paiementsalaire/convention/onglets/grille_salaire_base.php?mainmenu=paiementsalaire&leftmenu=convention&id_convention=".$id_convention, 1);
	$head[$h][1] = $langs->trans("Grilles Salaire de Base");
	$head[$h][2] = 'grille';
	$h++;

	$head[$h][0] = dol_buildpath("./paiementsalaire/convention/onglets/prime.php?mainmenu=paiementsalaire&leftmenu=convention&id_convention=".$id_convention, 1);
	$head[$h][1] = $langs->trans("Primes");
	$head[$h][2] = 'prime';
	$h++;

	$head[$h][0] = dol_buildpath("./paiementsalaire/convention/onglets/indemnite.php?mainmenu=paiementsalaire&leftmenu=convention&id_convention=".$id_convention, 1);
	$head[$h][1] = $langs->trans("Indemnités");
	$head[$h][2] = 'indemnite';
	$h++;

	$head[$h][0] = dol_buildpath("./paiementsalaire/convention/onglets/heure_sup.php?mainmenu=paiementsalaire&leftmenu=convention&id_convention=".$id_convention, 1);
	$head[$h][1] = $langs->trans("Heure Sup");
	$head[$h][2] = 'heure_sup';
	$h++;


	complete_head_from_modules($conf, $langs, null, $head, $h, 'paiementsalaire@paiementsalaire');

	complete_head_from_modules($conf, $langs, null, $head, $h, 'paiementsalaire@paiementsalaire', 'remove');

	return $head;
}


//les onglets des accord établissements
function paiementsalaireAccordHead($id_convention, $id_accord)
{
	global $langs, $conf;

	$langs->load("paiementsalaire@paiementsalaire");

	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath("./paiementsalaire/accord/onglets/accord_information.php?mainmenu=paiementsalaire&leftmenu=accord_etablissement&id_convention=".$id_convention."&id_accord=".$id_accord, 1);
	$head[$h][1] = $langs->trans("Information");
	$head[$h][2] = 'information';
	$h++;

	$head[$h][0] = dol_buildpath("./paiementsalaire/accord/onglets/accord_categorie.php?mainmenu=paiementsalaire&leftmenu=accord_etablissement&id_convention=".$id_convention."&id_accord=".$id_accord, 1);
	$head[$h][1] = $langs->trans("Catégories");
	$head[$h][2] = 'categorie';
	$h++;

	$head[$h][0] = dol_buildpath("./paiementsalaire/accord/onglets/grille_salaire_base.php?mainmenu=paiementsalaire&leftmenu=accord_etablissement&id_convention=".$id_convention."&id_accord=".$id_accord, 1);
	$head[$h][1] = $langs->trans("Grilles Salaire de Base");
	$head[$h][2] = 'grille';
	$h++;

	$head[$h][0] = dol_buildpath("./paiementsalaire/accord/onglets/prime.php?mainmenu=paiementsalaire&leftmenu=accord_etablissement&id_convention=".$id_convention."&id_accord=".$id_accord, 1);
	$head[$h][1] = $langs->trans("Primes");
	$head[$h][2] = 'prime';
	$h++;

	$head[$h][0] = dol_buildpath("./paiementsalaire/accord/onglets/indemnite.php?mainmenu=paiementsalaire&leftmenu=accord_etablissement&id_convention=".$id_convention."&id_accord=".$id_accord, 1);
	$head[$h][1] = $langs->trans("Indemnités");
	$head[$h][2] = 'indemnite';
	$h++;

	$head[$h][0] = dol_buildpath("./paiementsalaire/accord/onglets/heure_sup.php?mainmenu=paiementsalaire&leftmenu=accord_etablissement&id_convention=".$id_convention."&id_accord=".$id_accord, 1);
	$head[$h][1] = $langs->trans("Heure Sup");
	$head[$h][2] = 'heure_sup';
	$h++;

	complete_head_from_modules($conf, $langs, null, $head, $h, 'paiementsalaire@paiementsalaire');

	complete_head_from_modules($conf, $langs, null, $head, $h, 'paiementsalaire@paiementsalaire', 'remove');

	return $head;
}

function paiementsalaireSocieteHead($id_societe = 0, $id_convention)
{
	global $langs, $conf, $db;

	$langs->load("paiementsalaire@paiementsalaire");

	$h = 0;
	$head = array();
	$sql = "SELECT sal.rowid as salrowid, sal.matricule, sal.fk_user, u.rowid, u.lastname, u.firstname, u.dateemployment, ue.fk_object, ue.egp FROM ".MAIN_DB_PREFIX."salarie as sal";
	$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."user as u ON u.rowid=sal.fk_user";
	$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."user_extrafields as ue ON u.rowid=ue.fk_object";
	$sql .= " WHERE ue.egp=".$id_societe." AND ue.egp=".$id_societe." AND archiver='non'";
	$result = $db->query($sql);
	$num = $db->num_rows($result);

	$head[$h][0] = dol_buildpath("/paiementsalaire/onglets/liste_personnelle.php?mainmenu=paiementsalaire&leftmenu=societe&id_societe=".$id_societe."&id_convention=".$id_convention."&action=recherche", 1);
	$head[$h][1] = $langs->trans("Liste Salariés(".$num.")");
	$head[$h][2] = 'liste';
	$h++;

	$head[$h][0] = dol_buildpath("/paiementsalaire/onglets/societe_paies.php?mainmenu=paiementsalaire&leftmenu=societe&id_societe=".$id_societe."&id_convention=".$id_convention, 1);
	$head[$h][1] = $langs->trans("Salaires");
	$head[$h][2] = 'paies';
	$h++;

	$head[$h][0] = dol_buildpath("/paiementsalaire/onglets/bonus_paies.php?mainmenu=paiementsalaire&leftmenu=societe&id_societe=".$id_societe."&id_convention=".$id_convention, 1);
	$head[$h][1] = $langs->trans("Complements de salaires");
	$head[$h][2] = 'bonus';
	$h++;

	$head[$h][0] = dol_buildpath("/paiementsalaire/onglets/societe_taxe.php?mainmenu=paiementsalaire&leftmenu=societe&id_societe=".$id_societe."&id_convention=".$id_convention, 1);
	$head[$h][1] = $langs->trans("Taxes");
	$head[$h][2] = 'taxe';
	$h++;

	$head[$h][0] = dol_buildpath("/paiementsalaire/onglets/societe_cotisation.php?mainmenu=paiementsalaire&leftmenu=societe&id_societe=".$id_societe."&id_convention=".$id_convention, 1);
	$head[$h][1] = $langs->trans("Cotisations sociales");
	$head[$h][2] = 'cotisation';
	$h++;

	$head[$h][0] = dol_buildpath("/paiementsalaire/onglets/societe_prime.php?mainmenu=paiementsalaire&leftmenu=societe&id_societe=".$id_societe."&id_convention=".$id_convention, 1);
	$head[$h][1] = $langs->trans("Primes");
	$head[$h][2] = 'primes';
	$h++;

	$head[$h][0] = dol_buildpath("/paiementsalaire/onglets/societe_indemnite.php?mainmenu=paiementsalaire&leftmenu=societe&id_societe=".$id_societe."&id_convention=".$id_convention, 1);
	$head[$h][1] = $langs->trans("Indemnites");
	$head[$h][2] = 'indemnites';
	$h++;

	$head[$h][0] = dol_buildpath("/paiementsalaire/onglets/societe_avance.php?mainmenu=paiementsalaire&leftmenu=societe&id_societe=".$id_societe."&id_convention=".$id_convention, 1);
	$head[$h][1] = $langs->trans("Avances/Acomptes", 1);
	$head[$h][2] = 'avance';
	$h++;

	$head[$h][0] = dol_buildpath("/paiementsalaire/onglets/societe_heure_sup.php?mainmenu=paiementsalaire&leftmenu=societe&id_societe=".$id_societe."&id_convention=".$id_convention, 1);
	$head[$h][1] = $langs->trans("Heure sup");
	$head[$h][2] = 'heure_sup';
	$h++;

	$head[$h][0] = dol_buildpath("/paiementsalaire/onglets/simulation_societe.php?mainmenu=paiementsalaire&leftmenu=societe&id_societe=".$id_societe."&id_convention=".$id_convention, 1);
	$head[$h][1] = $langs->trans("Simulation");
	$head[$h][2] = 'simulation';
	$h++;

	$head[$h][0] = dol_buildpath("/paiementsalaire/onglets/validation_societe.php?mainmenu=paiementsalaire&leftmenu=societe&id_societe=".$id_societe."&id_convention=".$id_convention, 1);
	$head[$h][1] = $langs->trans("Validation");
	$head[$h][2] = 'validation';
	$h++;

	/*$head[$h][0] = dol_buildpath("/paiementsalaire/onglets/logo_societe.php?mainmenu=paiementsalaire&leftmenu=societe&id_societe=".$id_societe."&id_convention=".$id_convention, 1);
	$head[$h][1] = $langs->trans("Logo");
	$head[$h][2] = 'logo';
	$h++;

	/*$head[$h][0] = dol_buildpath("/paiementsalaire/onglets/simule_bulletin_societe.php?mainmenu=paiementsalaire&leftmenu=societe&id_societe=".$id_societe."&id_convention=".$id_convention, 1);
	$head[$h][1] = $langs->trans("Simulé bulletin");
	$head[$h][2] = 'simule_bulletin';
	$h++;*/



	// Show more tabs from modules
	// Entries must be declared in modules descriptor with line
	//$this->tabs = array(
	//	'entity:+tabname:Title:@paiementsalaire:/paiementsalaire/mypage.php?id=__ID__'
	//); // to add new tab
	//$this->tabs = array(
	//	'entity:-tabname:Title:@paiementsalaire:/paiementsalaire/mypage.php?id=__ID__'
	//); // to remove a tab
	complete_head_from_modules($conf, $langs, null, $head, $h, 'paiementsalaire@paiementsalaire');

	complete_head_from_modules($conf, $langs, null, $head, $h, 'paiementsalaire@paiementsalaire', 'remove');

	return $head;
}

//elle fournit un matricule aléatoirement
function get_matricule($longueur){
	$caracteres = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	$matricule_salarie = substr(str_shuffle(str_repeat($caracteres,$longueur)),0,$longueur);
  return $matricule_salarie;

}

function salaire_Head($fk_salarie, $id, $id_societe, $id_convention)
{
	global $langs, $conf;

	$langs->load("paiementsalaire@paiementsalaire");

	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath("/paiementsalaire/onglets/salarie_information.php?mainmenu=paiementsalaire&leftmenu=salarie&id_societe=".$id_societe."&fk_salarie=".$fk_salarie."&id_convention=".$id_convention."&id=".$id, 1);
	$head[$h][1] = $langs->trans("Informations");
	$head[$h][2] = 'information';
	$h++;

	$head[$h][0] = dol_buildpath("/paiementsalaire/onglets/contrat.php?mainmenu=paiementsalaire&leftmenu=salarie&id_societe=".$id_societe."&fk_salarie=".$fk_salarie."&id_convention=".$id_convention."&id=".$id, 1);
	$head[$h][1] = $langs->trans("Contrats");
	$head[$h][2] = 'contrat';
	$h++;

	$head[$h][0] = dol_buildpath("/paiementsalaire/onglets/salarie_hsup.php?mainmenu=paiementsalaire&leftmenu=salarie&id_societe=".$id_societe."&fk_salarie=".$fk_salarie."&id_convention=".$id_convention."&id=".$id, 1);
	$head[$h][1] = $langs->trans("Heures Sup");
	$head[$h][2] = 'hsup';
	$h++;

	$head[$h][0] = dol_buildpath("/paiementsalaire/onglets/prestation.php?mainmenu=paiementsalaire&leftmenu=salarie&id_societe=".$id_societe."&fk_salarie=".$fk_salarie."&id_convention=".$id_convention."&id=".$id, 1);
	$head[$h][1] = $langs->trans("Cotisations sociales");
	$head[$h][2] = 'prestation';
	$h++;
	$head[$h][0] = dol_buildpath("/paiementsalaire/onglets/taxe.php?mainmenu=paiementsalaire&leftmenu=salarie&id_societe=".$id_societe."&fk_salarie=".$fk_salarie."&id_convention=".$id_convention."&id=".$id, 1);
	$head[$h][1] = $langs->trans("Taxes");
	$head[$h][2] = 'taxe';
	$h++;

	$head[$h][0] = dol_buildpath("/paiementsalaire/onglets/salarie_prime.php?mainmenu=paiementsalaire&leftmenu=salarie&id_societe=".$id_societe."&fk_salarie=".$fk_salarie."&id_convention=".$id_convention."&id=".$id, 1);
	$head[$h][1] = $langs->trans("Primes");
	$head[$h][2] = 'primes';
	$h++;

	$head[$h][0] = dol_buildpath("/paiementsalaire/onglets/salarie_indemnite.php?mainmenu=paiementsalaire&leftmenu=salarie&id_societe=".$id_societe."&fk_salarie=".$fk_salarie."&id_convention=".$id_convention."&id=".$id, 1);
	$head[$h][1] = $langs->trans("Indemnités");
	$head[$h][2] = 'indemnites';
	$h++;
	$head[$h][0] = dol_buildpath("/paiementsalaire/onglets/avance.php?mainmenu=paiementsalaire&leftmenu=salarie&id_societe=".$id_societe."&fk_salarie=".$fk_salarie."&id_convention=".$id_convention."&id=".$id, 1);
	$head[$h][1] = $langs->trans("Avances/Acomptes");
	$head[$h][2] = 'avance';
	$h++;

	$head[$h][0] = dol_buildpath("/paiementsalaire/onglets/anciennete_nb_jours.php?mainmenu=paiementsalaire&leftmenu=salarie&id_societe=".$id_societe."&fk_salarie=".$fk_salarie."&id_convention=".$id_convention."&id=".$id, 1);
	$head[$h][1] = $langs->trans("Anc/Jours");
	$head[$h][2] = 'anciennete_nb_jours';
	$h++;

	$head[$h][0] = dol_buildpath("/paiementsalaire/onglets/conge.php?mainmenu=paiementsalaire&leftmenu=salarie&id_societe=".$id_societe."&fk_salarie=".$fk_salarie."&id_convention=".$id_convention."&id=".$id, 1);
	$head[$h][1] = $langs->trans("Congés");
	$head[$h][2] = 'conge';
	$h++;

	$head[$h][0] = dol_buildpath("/paiementsalaire/onglets/salarie_verification.php?mainmenu=paiementsalaire&leftmenu=salarie&id_societe=".$id_societe."&fk_salarie=".$fk_salarie."&id_convention=".$id_convention."&id=".$id, 1);
	$head[$h][1] = $langs->trans("Vérification");
	$head[$h][2] = 'verification';
	$h++;

	$head[$h][0] = dol_buildpath("/paiementsalaire/onglets/bulletin.php?mainmenu=paiementsalaire&leftmenu=salarie&id_societe=".$id_societe."&fk_salarie=".$fk_salarie."&id_convention=".$id_convention."&id=".$id, 1);
	$head[$h][1] = $langs->trans("Salaires");
	$head[$h][2] = 'bulletin';
	$h++;

	$head[$h][0] = dol_buildpath("/paiementsalaire/onglets/simulation.php?mainmenu=paiementsalaire&leftmenu=salarie&id_societe=".$id_societe."&fk_salarie=".$fk_salarie."&id_convention=".$id_convention."&id=".$id, 1);
	$head[$h][1] = $langs->trans("Simulation");
	$head[$h][2] = 'simulation';
	$h++;

	//); // to remove a tab
	complete_head_from_modules($conf, $langs, null, $head, $h, 'paiementsalaire@paiementsalaire');

	complete_head_from_modules($conf, $langs, null, $head, $h, 'paiementsalaire@paiementsalaire', 'remove');

	return $head;
}


//les onglets du prime et indemnité du configuration
function Prime_indm_Head($id, $p_i, $id_convention)
{
	global $langs, $conf;

	$langs->load("paiementsalaire@paiementsalaire");
	$id_pr_ind = "";
	$lefmenu="";
	$h = 0;
	$head = array();
	if($p_i == 1){
	 $id_pr_ind = "id_indemnite";
	 $lefmenu = 'indemnite';
	 $head[$h][0] = dol_buildpath("/paiementsalaire/listeindemnite.php?mainmenu=paiementsalaire&leftmenu=".$lefmenu."&id_convention=".$id_convention."&action=detail&".$id_pr_ind."=".$id, 1);
	$head[$h][1] = $langs->trans("information");
	$head[$h][2] = 'identifiant';
	$h++;
	$head[$h][0] = dol_buildpath("/paiementsalaire/listeindemnite.php?mainmenu=paiementsalaire&leftmenu=".$lefmenu."&id_convention=".$id_convention."&action=liste_condition&".$id_pr_ind."=".$id, 1);
	$head[$h][1] = $langs->trans("Barèmes");
	$head[$h][2] = 'information';
	$h++;
	}else{
		$id_pr_ind = "id_prime";
		$lefmenu = "prime";

		$head[$h][0] = dol_buildpath("/paiementsalaire/listeprime.php?mainmenu=paiementsalaire&leftmenu=".$lefmenu."&id_convention=".$id_convention."&action=detail&".$id_pr_ind."=".$id, 1);
		$head[$h][1] = $langs->trans("Identifiant");
		$head[$h][2] = 'identifiant';
		$h++;
		$head[$h][0] = dol_buildpath("/paiementsalaire/listeprime.php?mainmenu=paiementsalaire&leftmenu=".$lefmenu."&id_convention=".$id_convention."&action=liste_condition&".$id_pr_ind."=".$id, 1);
		$head[$h][1] = $langs->trans("Barèmes");
		$head[$h][2] = 'information';
		$h++;
 	}
	 complete_head_from_modules($conf, $langs, null, $head, $h, 'paiementsalaire@paiementsalaire');

	 complete_head_from_modules($conf, $langs, null, $head, $h, 'paiementsalaire@paiementsalaire', 'remove');

	 return $head;
}

//les onglets du prime et indemnité de la société
function Prime_indm_societe_Head($id, $p_i, $id_convention, $id_societe)
{
	global $langs, $conf;

	$langs->load("paiementsalaire@paiementsalaire");
	$id_pr_ind = "";
	$lefmenu="";
	$h = 0;
	$head = array();
	if($p_i == 1){
	 $id_pr_ind = "id_indemnite";
	 $lefmenu = 'societe';
	 $head[$h][0] = dol_buildpath("/paiementsalaire/onglets/societe_indemnite.php?mainmenu=paiementsalaire&leftmenu=".$lefmenu."&id_convention=".$id_convention."&action=detail&".$id_pr_ind."=".$id."&id_societe=".$id_societe, 1);
	$head[$h][1] = $langs->trans("information");
	$head[$h][2] = 'identifiant';
	$h++;
	$head[$h][0] = dol_buildpath("/paiementsalaire/onglets/societe_indemnite.php?mainmenu=paiementsalaire&leftmenu=".$lefmenu."&id_convention=".$id_convention."&action=liste_condition&".$id_pr_ind."=".$id."&id_societe=".$id_societe, 1);
	$head[$h][1] = $langs->trans("Barèmes");
	$head[$h][2] = 'information';
	$h++;
	}else{
		$id_pr_ind = "id_prime";
		$lefmenu = "societe";

		$head[$h][0] = dol_buildpath("/paiementsalaire/onglets/societe_prime.php?mainmenu=paiementsalaire&leftmenu=".$lefmenu."&id_convention=".$id_convention."&action=detail&".$id_pr_ind."=".$id."&id_societe=".$id_societe, 1);
	$head[$h][1] = $langs->trans("Identifiant");
	$head[$h][2] = 'identifiant';
	$h++;
	$head[$h][0] = dol_buildpath("/paiementsalaire/onglets/societe_prime.php?mainmenu=paiementsalaire&leftmenu=".$lefmenu."&id_convention=".$id_convention."&action=liste_condition&".$id_pr_ind."=".$id."&id_societe=".$id_societe, 1);
	$head[$h][1] = $langs->trans("Barèmes");
	$head[$h][2] = 'information';
	$h++;
	}

	complete_head_from_modules($conf, $langs, null, $head, $h, 'paiementsalaire@paiementsalaire');

	complete_head_from_modules($conf, $langs, null, $head, $h, 'paiementsalaire@paiementsalaire', 'remove');

	return $head;
}

//les onglets heures sup de la société
function Heure_sup_SocieteHead($id_societe = 0, $id_convention)
{
	global $langs, $conf;

	$langs->load("paiementsalaire@paiementsalaire");

	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath("/paiementsalaire/onglets/societe_heure_sup.php?mainmenu=paiementsalaire&leftmenu=societe&id_societe=".$id_societe."&id_convention=".$id_convention, 1);
	$head[$h][1] = $langs->trans("Heures sup");
	$head[$h][2] = 'hs_societe';
	$h++;

	$head[$h][0] = dol_buildpath("/paiementsalaire/onglets/societe_prime_heure_sup.php?mainmenu=paiementsalaire&leftmenu=societe&id_societe=".$id_societe."&id_convention=".$id_convention, 1);
	$head[$h][1] = $langs->trans("Primes HS");
	$head[$h][2] = 'hs_prime';
	$h++;

	$head[$h][0] = dol_buildpath("/paiementsalaire/onglets/societe_import_heure_sup.php?mainmenu=paiementsalaire&leftmenu=societe&id_societe=".$id_societe."&id_convention=".$id_convention, 1);
	$head[$h][1] = $langs->trans("Importer");
	$head[$h][2] = 'hs_import';
	$h++;

	// Show more tabs from modules
	// Entries must be declared in modules descriptor with line
	//$this->tabs = array(
	//	'entity:+tabname:Title:@paiementsalaire:/paiementsalaire/mypage.php?id=__ID__'
	//); // to add new tab
	//$this->tabs = array(
	//	'entity:-tabname:Title:@paiementsalaire:/paiementsalaire/mypage.php?id=__ID__'
	//); // to remove a tab
	complete_head_from_modules($conf, $langs, null, $head, $h, 'paiementsalaire@paiementsalaire');

	complete_head_from_modules($conf, $langs, null, $head, $h, 'paiementsalaire@paiementsalaire', 'remove');

	return $head;
}

//les onglets salariés de la société
function liste_salarie_SocieteHead($id_societe = 0, $id_convention)
{
	global $langs, $conf, $db;

	$langs->load("paiementsalaire@paiementsalaire");

	$h = 0;
	$head = array();

	$sql = "SELECT sal.rowid as salrowid, sal.matricule, sal.fk_user, u.rowid, u.lastname, u.firstname, u.dateemployment, ue.fk_object, ue.egp FROM ".MAIN_DB_PREFIX."salarie as sal";
	$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."user as u ON u.rowid=sal.fk_user";
	$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."user_extrafields as ue ON u.rowid=ue.fk_object";
	$sql .= " WHERE ue.egp=".$id_societe." AND ue.egp=".$id_societe." AND sal.archiver = 'non'";
	$result = $db->query($sql);
	$num = $db->num_rows($result);

	$head[$h][0] = dol_buildpath("/paiementsalaire/onglets/liste_personnelle.php?mainmenu=paiementsalaire&leftmenu=societe&id_societe=".$id_societe."&id_convention=".$id_convention, 1);
	$head[$h][1] = $langs->trans("Liste(".$num.")");
	$head[$h][2] = 'liste_salarie';
	$h++;

	$sql = "SELECT sal.rowid as salrowid, sal.matricule, sal.fk_user, u.rowid, u.lastname, u.firstname, u.dateemployment, ue.fk_object, ue.egp FROM ".MAIN_DB_PREFIX."salarie as sal";
	$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."user as u ON u.rowid=sal.fk_user";
	$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."user_extrafields as ue ON u.rowid=ue.fk_object";
	$sql .= " WHERE ue.egp=".$id_societe." AND ue.egp=".$id_societe." AND sal.archiver = 'oui'";
	$result = $db->query($sql);
	$num = $db->num_rows($result);

	$head[$h][0] = dol_buildpath("/paiementsalaire/onglets/salarie_archiver.php?mainmenu=paiementsalaire&leftmenu=societe&id_societe=".$id_societe."&id_convention=".$id_convention, 1);
	$head[$h][1] = $langs->trans("Archiver(".$num.")");
	$head[$h][2] = 'salarie_archiver';
	$h++;

	$head[$h][0] = dol_buildpath("/paiementsalaire/onglets/import_salarie.php?mainmenu=paiementsalaire&leftmenu=societe&id_societe=".$id_societe."&id_convention=".$id_convention, 1);
	$head[$h][1] = $langs->trans("Importer");
	$head[$h][2] = 'import_salarie';
	$h++;

	// Show more tabs from modules
	// Entries must be declared in modules descriptor with line
	//$this->tabs = array(
	//	'entity:+tabname:Title:@paiementsalaire:/paiementsalaire/mypage.php?id=__ID__'
	//); // to add new tab
	//$this->tabs = array(
	//	'entity:-tabname:Title:@paiementsalaire:/paiementsalaire/mypage.php?id=__ID__'
	//); // to remove a tab
	complete_head_from_modules($conf, $langs, null, $head, $h, 'paiementsalaire@paiementsalaire');

	complete_head_from_modules($conf, $langs, null, $head, $h, 'paiementsalaire@paiementsalaire', 'remove');

	return $head;
}


//les onglets du prime et indemnité de la convention
function Prime_indm_convention_Head($id, $p_i, $id_convention)
{
	global $langs, $conf;

	$langs->load("paiementsalaire@paiementsalaire");
	$id_pr_ind = "";
	$lefmenu="";
	$h = 0;
	$head = array();
	if($p_i == 1){
	 $id_pr_ind = "id_indemnite";
	 $lefmenu = 'convention';
	 $head[$h][0] = dol_buildpath("/paiementsalaire/convention/onglets/indemnite.php?mainmenu=paiementsalaire&leftmenu=".$lefmenu."&id_convention=".$id_convention."&action=detail&".$id_pr_ind."=".$id, 1);
	$head[$h][1] = $langs->trans("information");
	$head[$h][2] = 'identifiant';
	$h++;
	$head[$h][0] = dol_buildpath("/paiementsalaire/convention/onglets/indemnite.php?mainmenu=paiementsalaire&leftmenu=".$lefmenu."&id_convention=".$id_convention."&action=liste_condition&".$id_pr_ind."=".$id, 1);
	$head[$h][1] = $langs->trans("Barèmes");
	$head[$h][2] = 'information';
	$h++;
	}else{
		$id_pr_ind = "id_prime";
		$lefmenu = "convention";

		$head[$h][0] = dol_buildpath("/paiementsalaire/convention/onglets/prime.php?mainmenu=paiementsalaire&leftmenu=".$lefmenu."&id_convention=".$id_convention."&action=detail&".$id_pr_ind."=".$id, 1);
	$head[$h][1] = $langs->trans("Identifiant");
	$head[$h][2] = 'identifiant';
	$h++;
	$head[$h][0] = dol_buildpath("/paiementsalaire/convention/onglets/prime.php?mainmenu=paiementsalaire&leftmenu=".$lefmenu."&id_convention=".$id_convention."&action=liste_condition&".$id_pr_ind."=".$id, 1);
	$head[$h][1] = $langs->trans("Barèmes");
	$head[$h][2] = 'information';
	$h++;
	}

	complete_head_from_modules($conf, $langs, null, $head, $h, 'paiementsalaire@paiementsalaire');

	complete_head_from_modules($conf, $langs, null, $head, $h, 'paiementsalaire@paiementsalaire', 'remove');

	return $head;
}

//les onglets du prime et indemnité de l'accord d'établissement
function Prime_indm_accord_Head($id, $p_i, $id_convention, $id_accord)
{
	global $langs, $conf;

	$langs->load("paiementsalaire@paiementsalaire");
	$id_pr_ind = "";
	$lefmenu="";
	$h = 0;
	$head = array();
	if($p_i == 1){
	 $id_pr_ind = "id_indemnite";
	 $lefmenu = 'accord_etablissement';
	 $head[$h][0] = dol_buildpath("/paiementsalaire/accord/onglets/indemnite.php?mainmenu=paiementsalaire&leftmenu=".$lefmenu."&id_convention=".$id_convention."&action=detail&".$id_pr_ind."=".$id."&id_accord=".$id_accord, 1);
	$head[$h][1] = $langs->trans("information");
	$head[$h][2] = 'identifiant';
	$h++;
	$head[$h][0] = dol_buildpath("/paiementsalaire/accord/onglets/indemnite.php?mainmenu=paiementsalaire&leftmenu=".$lefmenu."&id_convention=".$id_convention."&action=liste_condition&".$id_pr_ind."=".$id."&id_accord=".$id_accord, 1);
	$head[$h][1] = $langs->trans("Barèmes");
	$head[$h][2] = 'information';
	$h++;
	}else{
		$id_pr_ind = "id_prime";
		$lefmenu = "accord_etablissement";

		$head[$h][0] = dol_buildpath("/paiementsalaire/accord/onglets/prime.php?mainmenu=paiementsalaire&leftmenu=".$lefmenu."&id_convention=".$id_convention."&action=detail&".$id_pr_ind."=".$id."&id_accord=".$id_accord, 1);
	$head[$h][1] = $langs->trans("Identifiant");
	$head[$h][2] = 'identifiant';
	$h++;
	$head[$h][0] = dol_buildpath("/paiementsalaire/accord/onglets/prime.php?mainmenu=paiementsalaire&leftmenu=".$lefmenu."&id_convention=".$id_convention."&action=liste_condition&".$id_pr_ind."=".$id."&id_accord=".$id_accord, 1);
	$head[$h][1] = $langs->trans("Barèmes");
	$head[$h][2] = 'information';
	$h++;
	}

	complete_head_from_modules($conf, $langs, null, $head, $h, 'paiementsalaire@paiementsalaire');

	complete_head_from_modules($conf, $langs, null, $head, $h, 'paiementsalaire@paiementsalaire', 'remove');

	return $head;
}


//les onglets du contrat
function salarie_contrat_Head($fk_salarie, $fk_user, $id_societe, $id_convention, $fk_contrat)
{
	global $langs, $conf;

	$langs->load("paiementsalaire@paiementsalaire");
	$id_pr_ind = "";
	$lefmenu="salarie";
	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath("/paiementsalaire/onglets/contrat_information.php?mainmenu=paiementsalaire&leftmenu=".$lefmenu."&id_convention=".$id_convention."&id_societe=".$id_societe."&fk_salarie=".$fk_salarie."&id=".$fk_user."&id_contrat=".$fk_contrat."&action=detail", 1);
	$head[$h][1] = $langs->trans("Information");
	$head[$h][2] = 'information';
	$h++;
	$head[$h][0] = dol_buildpath("/paiementsalaire/onglets/contrat_salaire_net.php?mainmenu=paiementsalaire&leftmenu=".$lefmenu."&id_convention=".$id_convention."&id_societe=".$id_societe."&fk_salarie=".$fk_salarie."&id=".$fk_user."&id_contrat=".$fk_contrat, 1);
	$head[$h][1] = $langs->trans("Salaire Net");
	$head[$h][2] = 'salaire_net';
	$h++;

	 complete_head_from_modules($conf, $langs, null, $head, $h, 'paiementsalaire@paiementsalaire');

	 complete_head_from_modules($conf, $langs, null, $head, $h, 'paiementsalaire@paiementsalaire', 'remove');

	 return $head;
}

//les onglets dans heures sup Salarié
function salarie_heure_sup_head($fk_salarie, $fk_user, $id_societe, $id_convention)
{
	global $langs, $conf;

	$langs->load("paiementsalaire@paiementsalaire");
	$id_pr_ind = "";
	$lefmenu="salarie";
	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath("/paiementsalaire/onglets/salarie_hsup.php?mainmenu=paiementsalaire&leftmenu=".$lefmenu."&id_convention=".$id_convention."&id_societe=".$id_societe."&fk_salarie=".$fk_salarie."&id=".$fk_user."&action=liste", 1);
	$head[$h][1] = $langs->trans("Heures sup");
	$head[$h][2] = 'hs_salarie';
	$h++;
	$head[$h][0] = dol_buildpath("/paiementsalaire/onglets/config_hsup_salarie.php?mainmenu=paiementsalaire&leftmenu=".$lefmenu."&id_convention=".$id_convention."&id_societe=".$id_societe."&fk_salarie=".$fk_salarie."&id=".$fk_user."&action=config", 1);
	$head[$h][1] = $langs->trans("Configuration");
	$head[$h][2] = 'hs_config';
	$h++;

	 complete_head_from_modules($conf, $langs, null, $head, $h, 'paiementsalaire@paiementsalaire');

	 complete_head_from_modules($conf, $langs, null, $head, $h, 'paiementsalaire@paiementsalaire', 'remove');

	 return $head;
}

//les onglets du avance
function salarie_avance_Head($fk_salarie, $fk_user, $id_societe, $id_convention, $fk_avance)
{
	global $langs, $conf;

	$langs->load("paiementsalaire@paiementsalaire");
	$id_pr_ind = "";
	$lefmenu="salarie";
	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath("/paiementsalaire/onglets/avance_information.php?mainmenu=paiementsalaire&leftmenu=".$lefmenu."&id_convention=".$id_convention."&id_societe=".$id_societe."&fk_salarie=".$fk_salarie."&id=".$fk_user."&id_avance=".$fk_avance."&action=detail", 1);
	$head[$h][1] = $langs->trans("Information");
	$head[$h][2] = 'information';
	$h++;
	$head[$h][0] = dol_buildpath("/paiementsalaire/onglets/avance_detail.php?mainmenu=paiementsalaire&leftmenu=".$lefmenu."&id_convention=".$id_convention."&id_societe=".$id_societe."&fk_salarie=".$fk_salarie."&id=".$fk_user."&id_avance=".$fk_avance, 1);
	$head[$h][1] = $langs->trans("Détail paiement");
	$head[$h][2] = 'detail';
	$h++;

	 complete_head_from_modules($conf, $langs, null, $head, $h, 'paiementsalaire@paiementsalaire');

	 complete_head_from_modules($conf, $langs, null, $head, $h, 'paiementsalaire@paiementsalaire', 'remove');

	 return $head;
}


//les onglets des taxes
function taxe_head($id)
{
	global $langs, $conf;

	$langs->load("paiementsalaire@paiementsalaire");
	$h = 0;
	$head = array();
	$head[$h][0] = dol_buildpath("/paiementsalaire/config/bareme_taxe.php?mainmenu=paiementsalaire&leftmenu=taxe&action=info&id_taxe=".$id, 1);
	$head[$h][1] = $langs->trans("information");
	$head[$h][2] = 'identifiant';
	$h++;
	$head[$h][0] = dol_buildpath("/paiementsalaire/config/bareme_taxe.php?mainmenu=paiementsalaire&leftmenu=taxe&action=detail_taxe&id_taxe=".$id, 1);
	$head[$h][1] = $langs->trans("Barêmes");
	$head[$h][2] = 'information';
	$h++;



	complete_head_from_modules($conf, $langs, null, $head, $h, 'paiementsalaire@paiementsalaire');

	complete_head_from_modules($conf, $langs, null, $head, $h, 'paiementsalaire@paiementsalaire', 'remove');

	return $head;
}

//les onglets des cotisations
function prestation_head($id)
{
	global $langs, $conf;

	$langs->load("paiementsalaire@paiementsalaire");
	$h = 0;
	$head = array();
	$head[$h][0] = dol_buildpath("/paiementsalaire/config/bareme_prestation.php?mainmenu=paiementsalaire&leftmenu=prestation&action=info&id_prestation=".$id, 1);
	$head[$h][1] = $langs->trans("information");
	$head[$h][2] = 'identifiant';
	$h++;
	$head[$h][0] = dol_buildpath("/paiementsalaire/config/bareme_prestation.php?mainmenu=paiementsalaire&leftmenu=prestation&action=detail_prestation&id_prestation=".$id, 1);
	$head[$h][1] = $langs->trans("Barêmes");
	$head[$h][2] = 'information';
	$h++;



	complete_head_from_modules($conf, $langs, null, $head, $h, 'paiementsalaire@paiementsalaire');

	complete_head_from_modules($conf, $langs, null, $head, $h, 'paiementsalaire@paiementsalaire', 'remove');

	return $head;
}

//---------------------------------------------------------------------------------------------------------------------------------------
//la fonction qui retourne l'its
function its_salarie($db, $fk_salarie, $salaire_brut, $situation_familiale = "celibataire", $nb_enfant = 0, $nb_enf_hand = 0){
	$taux_montant_its_annuel_mensuel = array();
		$salaire_brut = round($salaire_brut*12);
		//print "<br>Salaire brute par an = ".$salaire_brut;
		$mont = "".$salaire_brut;
	if($salaire_brut <= 250){
		return 0;
	}else{


		//substr('abcdef', 1, 3);  // bcd
		//print "<br> les 3 derniers chiffres :".substr($mont, strlen($mont)-3, strlen($mont)-1);

		$dern_ch = substr($mont, strlen($mont)-3, strlen($mont)-1);
		if($dern_ch >=0 && $dern_ch < 250){
			$mont = substr($mont, 0, strlen($mont)-3)."000";
			//print "br"
		}else if($dern_ch >=251 && $dern_ch < 500){

			$mont = substr($mont, 0, strlen($mont)-3)."250";
		}else if($dern_ch >=501 && $dern_ch < 750){
			$mont = substr($mont, 0, strlen($mont)-3)."500";
		}else if($dern_ch >=701 && $dern_ch <= 999){
			$mont = substr($mont, 0, strlen($mont)-3)."750";
		}


		//print "<br>Salire brute arrodi a 250 inférieur = ".$mont;
		//print "<br>-------------------------------";

		$ss = (int) $mont;
	//-----------------------------------------

		//for ($a=0; $a < count($id_taxe); $a++) {


			//print '<br>';
			$tab = 0;
			$grille_bareme = "SELECT rowid FROM ".MAIN_DB_PREFIX."bareme_taxe WHERE fk_taxe=1 AND actif=1";
            $result_grille_bareme = $db->query($grille_bareme);
            if($result_grille_bareme){
                $obj_grille_bareme = $db->fetch_object($result_grille_bareme);

				$sql_bareme = "SELECT * FROM ".MAIN_DB_PREFIX."taxe WHERE fk_bareme =".$obj_grille_bareme->rowid." AND fk_type=1 AND montant_debut<=".$ss." ORDER BY montant_debut ASC";
				$result_bareme = $db->query($sql_bareme);
				if($result_bareme){
					$i = 0;
					$num = $db->num_rows($result_bareme);
					while ($i < $num) {
						$bareme = $db->fetch_object($result_bareme);
						if($num >= 2)
							if($i == ($num - 1)){
								$tab = $tab + ((($ss - $bareme->montant_debut)*$bareme->taux)/100);
								//print $tab." i=".$i."<br>";
							}else if($i == ($num - 2)){
								$tab = $tab +  $bareme->valeur;
								//print $tab." i=".$i."<br>";
							}
						$i ++;
					}
					$taux = 0;
					if (!empty($fk_salarie)){
						$sql_sal = "SELECT situation_familiale, nombre_enfant, nombre_enfant_hand FROM ".MAIN_DB_PREFIX."salarie WHERE rowid=".$fk_salarie;
						$res_sal = $db->query($sql_sal);
						$salarie = $db->fetch_object($res_sal);

						if($salarie->situation_familiale == "marie")
							$taux = 10;
						if(($salarie->nombre_enfant + $salarie->nombre_enfant_hand) < 10){
							$taux = $taux + ($salarie->nombre_enfant - $salarie->nombre_enfant_hand)*2.5;
							$taux = $taux + $salarie->nombre_enfant_hand*10;
						}else{
							$taux = $taux + 10*2.5;
							//$taux = $taux + $salarie->nombre_enfant_hand*10;
						}

					}else{
						
						if($situation_familiale == "marie" || $situation_familiale == "marié" || $situation_familiale == "Marié")
							$taux = 10;

							//print $taux." = '".$taux." + (".$nb_enfant ."-". $nb_enf_hand.")*2.5";
							$taux = $taux + ($nb_enfant - $nb_enf_hand)*2.5;
							$taux = $taux + $nb_enf_hand*10;

					}
					$taux.'<br>';

					$its_brut = $tab;
					//print 'its_brut'.$its_brut.'<br>';
					//print "<br>ITS brute = ".round($its_brut);
					//print 'taux'.$taux.'<br>';

					$its_annuel_net = $its_brut - ($its_brut * $taux / 100);
					//print "<br>ITS annuel net=".round($its_annuel_net);

					$taux_its_annuel =  ($its_annuel_net/$ss)*100;
					//print 'taux_its_annuel'.$taux_its_annuel.'<br>';
					//print "<br>Taux ITS=".round($taux_its_annuel,2);
					$taux_its_reduit = $taux_its_annuel - 2;
					//print 'taux_its_reduit'.$taux_its_reduit.'<br>';

					if($taux_its_reduit < 0)
						$taux_its_reduit = 0;

						$taux_montant_its_annuel_mensuel[0] = $taux_its_reduit;//taux its
					//print "<br>Taux ITS annuel reduit=".round($taux_its_reduit,2);

					$its_annuel = ($taux_its_reduit*$ss)/100;
					//print "<br>ITS annuel reduit=".$its_annuel;
					//print "<br>ITS mensuel reduit=".$its_annuel/12;

					$taux_montant_its_annuel_mensuel[1] = round($its_annuel,2);//its annuel


					$taux_montant_its_annuel_mensuel[2] = round($its_annuel/12,2);
					//its mensuel
				//}

				$type_taxe = "SELECT libelle FROM ".MAIN_DB_PREFIX."type_taxe WHERE rowid=1";
            $result_type_taxe = $db->query($type_taxe);
            if($result_type_taxe)
                $obj_type_taxe = $db->fetch_object($result_grille_bareme);


				$taux_montant_its_annuel_mensuel[3] = $obj_type_taxe->libelle?:'I.T.S';
		}
		}
		return $taux_montant_its_annuel_mensuel;

	}

}

//ITS annuel article24.
function its_salarie_annuel($db, $salaire_brut, $situation_familiale = "celibataire", $nb_enfant = 0, $nb_enf_hand = 0){
	$taux_montant_its_annuel_mensuel = array();
		//print "<br>Salaire brute par an = ".$salaire_brut;
		$mont = "".$salaire_brut;
	if($salaire_brut <= 250){
		return 0;
	}else{


		//substr('abcdef', 1, 3);  // bcd
		//print "<br> les 3 derniers chiffres :".substr($mont, strlen($mont)-3, strlen($mont)-1);

		$dern_ch = substr($mont, strlen($mont)-3, strlen($mont)-1);
		if($dern_ch >=0 && $dern_ch < 250){
			$mont = substr($mont, 0, strlen($mont)-3)."000";
			//print "br"
		}else if($dern_ch >=251 && $dern_ch < 500){

			$mont = substr($mont, 0, strlen($mont)-3)."250";
		}else if($dern_ch >=501 && $dern_ch < 750){
			$mont = substr($mont, 0, strlen($mont)-3)."500";
		}else if($dern_ch >=701 && $dern_ch <= 999){
			$mont = substr($mont, 0, strlen($mont)-3)."750";
		}


		//print "<br>Salire brute arrodi a 250 inférieur = ".$mont;
		//print "<br>-------------------------------";

		$ss = (int) $mont;
	//-----------------------------------------

		//for ($a=0; $a < count($id_taxe); $a++) {


			//print '<br>';
			$tab = 0;
				$sql_bareme = "SELECT * FROM ".MAIN_DB_PREFIX."taxe WHERE fk_type=1 AND montant_debut<=".$ss." ORDER BY montant_debut ASC";
				$result_bareme = $db->query($sql_bareme);
				if($result_bareme){
					$i = 0;
					$num = $db->num_rows($result_bareme);
					while ($i < $num) {
						$bareme = $db->fetch_object($result_bareme);
						if($num >= 2)
							if($i == ($num - 1)){
								$tab = $tab + ((($ss - $bareme->montant_debut)*$bareme->taux)/100);
								//print $tab." i=".$i."<br>";
							}else if($i == ($num - 2)){
								$tab = $tab +  $bareme->valeur;
								//print $tab." i=".$i."<br>";
							}
						$i ++;
					}
					$taux = 0;
					if($situation_familiale == "marie")
						$taux = 10;
					$taux = $taux + ($nb_enfant - $nb_enf_hand)*2.5;
					$taux = $taux + $nb_enf_hand*10;



					$its_brut = $tab;
					//print "<br>ITS brute = ".round($its_brut);

					$its_annuel_net = $its_brut - ($its_brut * $taux / 100);
					//print "<br>ITS annuel net=".round($its_annuel_net);

					$taux_its_annuel =  ($its_annuel_net/$ss)*100;
					//print "<br>Taux ITS=".round($taux_its_annuel,2);
					$taux_its_reduit = $taux_its_annuel - 2;

					if($taux_its_reduit < 0)
						$taux_its_reduit = 0;

						$taux_montant_its_annuel_mensuel[0] = $taux_its_reduit;//taux its
					//print "<br>Taux ITS annuel reduit=".round($taux_its_reduit,2);

					$its_annuel = ($taux_its_reduit*$ss)/100;
					//print "<br>ITS annuel reduit=".$its_annuel;
					//print "<br>ITS mensuel reduit=".$its_annuel/12;




					//its mensuel
				//}

		}

		return $its_annuel;
	}

}


//la fonction qui retourne l'its
function taxe_salarie($db, $fk_salarie, $salaire_brut){
	if($salaire_brut <= 0){
		return 0;
	}else{
		$taux_montant_its_annuel_mensuel = array();
		$salaire_brut = round($salaire_brut*12);
		//print "<br>Salaire brute par an = ".$salaire_brut;
		$mont = "".$salaire_brut;
		//substr('abcdef', 1, 3);  // bcd
		//print "<br> les 3 derniers chiffres :".substr($mont, strlen($mont)-3, strlen($mont)-1);

		$dern_ch = substr($mont, strlen($mont)-3, strlen($mont)-1);
		if($dern_ch >=0 && $dern_ch < 250){
			$mont = substr($mont, 0, strlen($mont)-3)."000";
			//print "br"
		}else if($dern_ch >=251 && $dern_ch < 500){

			$mont = substr($mont, 0, strlen($mont)-3)."250";
		}else if($dern_ch >=501 && $dern_ch < 750){
			$mont = substr($mont, 0, strlen($mont)-3)."500";
		}else if($dern_ch >=701 && $dern_ch <= 999){
			$mont = substr($mont, 0, strlen($mont)-3)."750";
		}


		//print "<br>Salire brute arrodi a 250 inférieur = ".$mont;
		//print "<br>-------------------------------";

		$ss = (int) $mont;
	//-----------------------------------------

		//for ($a=0; $a < count($id_taxe); $a++) {


			//print '<br>';
				$sql_bareme = "SELECT * FROM ".MAIN_DB_PREFIX."taxe WHERE fk_type=1 AND montant_debut<=".$ss." ORDER BY montant_debut ASC";
				$result_bareme = $db->query($sql_bareme);
				if($result_bareme){
					$i = 0;
					$num = $db->num_rows($result_bareme);
					while ($i < $num) {
						$bareme = $db->fetch_object($result_bareme);
						if($num >= 2)
							if($i == ($num - 1)){
								$tab = $tab + ((($ss - $bareme->montant_debut)*$bareme->taux)/100);
							//print $tab." i=".$i."<br>";
							}else if($i == ($num - 2)){
								$tab = $tab +  $bareme->valeur;
								//print $tab." i=".$i."<br>";
							}
						$i ++;
					}


					$sql_sal = "SELECT * FROM ".MAIN_DB_PREFIX."salarie WHERE rowid=".$fk_salarie;
					$res_sal = $db->query($sql_sal);
					$salarie = $db->fetch_object($res_sal);


					if($salarie->situation_familiale == "marie")
						$taux = 10;
					$taux = $taux + ($salarie->nombre_enfant - $salarie->nombre_enfant_hand)*2.5;
					$taux = $taux + $salarie->nombre_enfant_hand*10;


					//print "<br>ITS brute = ".round($tab);
					$tab = $tab - ($tab * $taux / 100);


					//print "<br>ITS annuel net=".round($tab);
					$tab = ($tab/$ss)*100;

					//print "<br>Taux ITS=".round($tab,2);
					$tab = $tab - 2;
					$taux_montant_its_annuel_mensuel[0] = round($tab, 2);//taux its

					if($tab < 0)
						$tab = 0;
					//print "<br>Taux ITS annuel reduit=".round($tab,2);

					$tab = ($tab*$ss)/100;

					//print "<br>ITS annuel reduit=".$tab;
					//print "<br>ITS mensuel reduit=".$tab/12;


					$taux_montant_its_annuel_mensuel[1] = round($tab, 2);//its annuel


					$taux_montant_its_annuel_mensuel[2] = round($tab/12, 2);
					//its mensuel

				//}


		}

		return $taux_montant_its_annuel_mensuel;
	}

}

//Salarié Taxes dont le barème est de type "barème cotisation"
//Ces taxes ne seront pas affichée sur le bulletin
function salarie_taxe2($db, $fk_salarie, $id_convention){

	$valeur = 0;
	$id_taxe2 = array();
	$taux_salarial = array();
	$taux_patronal = array();


	$sql_taxe2 = "SELECT * FROM ".MAIN_DB_PREFIX."type_taxe WHERE rowid <>1 AND nature='obligatoire'";
		$result_taxe2 = $db->query($sql_taxe2);
		if($result_taxe2){
			$j = 0;
			$numj = $db->num_rows($result_taxe2);
			while ($j < $numj) {
				$taxe2 = $db->fetch_object($result_bareme);
				$id_taxe2[] = $taxe2->rowid;
				$j++;
			}
		}

	$sql_taxe2 = "SELECT * FROM ".MAIN_DB_PREFIX."salarie_taxe WHERE fk_salarie=".$fk_salarie;
		$result_taxe2 = $db->query($sql_taxe2);
		if($result_taxe2){
			$j = 0;
			$numj = $db->num_rows($result_taxe2);
			while ($j < $numj) {
				$taxe2 = $db->fetch_object($result_taxe2);
				if(!in_array($taxe2->fk_taxe, $id_taxe2))
					$id_taxe2[] = $taxe2->fk_taxe2;
				$j++;
			}
		}


	for ($a=0; $a < count($id_taxe2); $a++) {
		$sql_bareme = "SELECT * FROM ".MAIN_DB_PREFIX."bareme_taxe2 WHERE fk_taxe=".$id_taxe2[$a];
		$result_bareme = $db->query($sql_bareme);
		if($result_bareme){
			$num = $db->num_rows($result_bareme);
					$i = 0;
					$trouve = false;
				if($num > 0){
					$bareme = $db->fetch_object($result_bareme);
					if($bareme){
						if($bareme->charge == 1){
							$taux_salarial[] = $bareme->taux_salariale;
							$taux_patronal[] = 0;
						}else if($bareme->charge == 2){
							$taux_patronal[] = $bareme->taux_patronale;
							$taux_salarial[] = 0;
						}else{
							$taux_salarial[] = $bareme->taux_salariale;
							$taux_patronal[] = $bareme->taux_patronale;
						}
					}else{
						$taux_salarial[] = 0;
						$taux_patronal[] = 0;
					}

				}else{
					$taux_salarial[] = 0;
					$taux_patronal[] = 0;
				}

		}else{
			$taux_salarial[] = 0;
			$taux_patronal[] = 0;
		}

		//print $id_taxe2[$a]."--".$taux_patronal[$a].'--'.$taux_salarial[$a].'<br>';
	}
	$global = array();
	$global[0] = $taux_patronal;
	$global[1] = array_combine($id_taxe2, $taux_salarial);
	return $global;

}


function simulation_taxe2($db, $fk_salarie, $id_convention){

	$valeur = 0;
	$id_taxe2 = array();
	$taux_salarial = array();
	$taux_patronal = array();


	$sql_taxe2 = "SELECT * FROM ".MAIN_DB_PREFIX."type_taxe WHERE rowid <>1 AND nature='obligatoire'";
		$result_taxe2 = $db->query($sql_taxe2);
		if($result_taxe2){
			$j = 0;
			$numj = $db->num_rows($result_taxe2);
			while ($j < $numj) {
				$taxe2 = $db->fetch_object($result_bareme);
				$id_taxe2[] = $taxe2->rowid;
				$j++;
			}
		}

	$sql_taxe2 = "SELECT * FROM ".MAIN_DB_PREFIX."salarie_taxe WHERE fk_salarie=".$fk_salarie;
		$result_taxe2 = $db->query($sql_taxe2);
		if($result_taxe2){
			$j = 0;
			$numj = $db->num_rows($result_taxe2);
			while ($j < $numj) {
				$taxe2 = $db->fetch_object($result_taxe2);
				if(!in_array($taxe2->fk_taxe, $id_taxe2))
					$id_taxe2[] = $taxe2->fk_taxe2;
				$j++;
			}
		}


	for ($a=0; $a < count($id_taxe2); $a++) {
		$sql_bareme = "SELECT * FROM ".MAIN_DB_PREFIX."bareme_taxe2 WHERE fk_taxe=".$id_taxe2[$a];
		$result_bareme = $db->query($sql_bareme);
		if($result_bareme){
			$num = $db->num_rows($result_bareme);
					$i = 0;
					$trouve = false;
				if($num > 0){
					$bareme = $db->fetch_object($result_bareme);
					if($bareme){
						if($bareme->charge == 1){
							$taux_salarial[] = $bareme->taux_salariale;
							$taux_patronal[] = 0;
						}else if($bareme->charge == 2){
							$taux_patronal[] = $bareme->taux_patronale;
							$taux_salarial[] = 0;
						}else{
							$taux_salarial[] = $bareme->taux_salariale;
							$taux_patronal[] = $bareme->taux_patronale;
						}
					}else{
						$taux_salarial[] = 0;
						$taux_patronal[] = 0;
					}

				}else{
					$taux_salarial[] = 0;
					$taux_patronal[] = 0;
				}

		}else{
			$taux_salarial[] = 0;
			$taux_patronal[] = 0;
		}

		//print $id_taxe2[$a]."--".$taux_patronal[$a].'--'.$taux_salarial[$a].'<br>';
	}
	//print count($id_taxe2);
	$global = array();
	$global[0] = $taux_patronal;
	$global[1] = array_combine($id_taxe2, $taux_salarial);
	return $global;

}


//les prestation à afficher sur le bulletin
function salarie_prestation($db, $fk_salarie, $id_convention){

	$valeur = 0;
	$id_prestation = array();
	$taux_salarial = array();
	$taux_patronal = array();


	$sql_prestation = "SELECT * FROM ".MAIN_DB_PREFIX."type_prestation WHERE nature='obligatoire'";
		$result_prestation = $db->query($sql_prestation);
		if($result_prestation){
			$j = 0;
			$numj = $db->num_rows($result_prestation);
			while ($j < $numj) {
				$prestation = $db->fetch_object($result_bareme);
				$id_prestation[] = $prestation->rowid;
				$j++;
			}
		}

	$sql_prestation = "SELECT * FROM ".MAIN_DB_PREFIX."salarie_prestation_sociale WHERE fk_salarie=".$fk_salarie;
		$result_prestation = $db->query($sql_prestation);
		if($result_prestation){
			$j = 0;
			$numj = $db->num_rows($result_prestation);
			while ($j < $numj) {
				$prestation = $db->fetch_object($result_prestation);
				if(!in_array($prestation->fk_prestation_sociale, $id_prestation))
					$id_prestation[] = $prestation->fk_prestation_sociale;
				$j++;
			}
		}


	for ($a=0; $a < count($id_prestation); $a++) {

		$sql_bareme = "SELECT * FROM ".MAIN_DB_PREFIX."bareme_prestation WHERE fk_prestation=".$id_prestation[$a];
		$result_bareme = $db->query($sql_bareme);
		if($result_bareme){
			$num = $db->num_rows($result_bareme);
					$i = 0;
					$trouve = false;
				if($num > 0){
					while($i < $num && $trouve == false){
						$bareme = $db->fetch_object($result_bareme);
							$prest_conv_sql = "SELECT fk_convention FROM ".MAIN_DB_PREFIX."bareme_prestation_convention WHERE fk_convention=".$id_convention." AND fk_condition=".$bareme->rowid;
							$prest_conv_res = $db->query($prest_conv_sql);
							if($db->num_rows($prest_conv_res) > 0){
								$trouve = true;
							}
						$i ++;
					}
					if($bareme){
						if($bareme->charge == 1){
							$taux_salarial[] = $bareme->taux_salariale;
							$taux_patronal[] = 0;
						}else if($bareme->charge == 2){
							$taux_patronal[] = $bareme->taux_patronale;
							$taux_salarial[] = 0;
						}else{
							$taux_salarial[] = $bareme->taux_salariale;
							$taux_patronal[] = $bareme->taux_patronale;
						}
					}else{
						$taux_salarial[] = 0;
						$taux_patronal[] = 0;
					}

				}else{
					$taux_salarial[] = 0;
					$taux_patronal[] = 0;
				}

		}else{
			$taux_salarial[] = 0;
			$taux_patronal[] = 0;
		}

		//print $id_prestation[$a]."--".$taux_patronal[$a].'--'.$taux_salarial[$a].'<br>';
	}
	$global = array();
	$global[0] = $taux_patronal;
	$global[1] = array_combine($id_prestation, $taux_salarial);
	return $global;

}

//Les cotisations qui doivent être affichées par organisme
function salarie_prestation_organisme($db, $fk_salarie, $id_convention){

	$valeur = 0;
	$id_prestation = array();
	$taux_salarial = array();
	$taux_patronal = array();
	$id_organisme = "0";

	$organisme = "SELECT rowid FROM ".MAIN_DB_PREFIX."organisme WHERE affiche_detail_bulletin='non'";
	$result_organisme = $db->query($organisme);
	if($result_organisme){
		$j = 0;
		$numj = $db->num_rows($result_organisme);
		while ($j < $numj) {
			$obj_organisme = $db->fetch_object($result_organisme);
			$id_organisme .= ", ".$obj_organisme->rowid;
			$j++;
		}
	}

	$sql_prestation = "SELECT * FROM ".MAIN_DB_PREFIX."type_prestation WHERE nature='obligatoire' AND fk_organisme IN (".$id_organisme.") ORDER BY fk_organisme";
		$result_prestation = $db->query($sql_prestation);
		if($result_prestation){
			$j = 0;
			$numj = $db->num_rows($result_prestation);
			while ($j < $numj) {
				$prestation = $db->fetch_object($result_bareme);
				$id_prestation[] = $prestation->rowid;
				$j++;
			}
		}

	$sql_prestation = "SELECT * FROM ".MAIN_DB_PREFIX."salarie_prestation_sociale WHERE fk_salarie=".$fk_salarie." AND fk_organisme IN (".$id_organisme.") ORDER BY fk_organisme";
		$result_prestation = $db->query($sql_prestation);
		if($result_prestation){
			$j = 0;
			$numj = $db->num_rows($result_prestation);
			while ($j < $numj) {
				$prestation = $db->fetch_object($result_prestation);
				if(!in_array($prestation->fk_prestation_sociale, $id_prestation))
					$id_prestation[] = $prestation->fk_prestation_sociale;
				$j++;
			}
		}


	for ($a=0; $a < count($id_prestation); $a++) {

		$sql_bareme = "SELECT * FROM ".MAIN_DB_PREFIX."bareme_prestation WHERE fk_prestation=".$id_prestation[$a];
		$result_bareme = $db->query($sql_bareme);
		if($result_bareme){
			$num = $db->num_rows($result_bareme);
					$i = 0;
					$trouve = false;
				if($num > 0){
					while($i < $num && $trouve == false){
						$bareme = $db->fetch_object($result_bareme);
							$prest_conv_sql = "SELECT fk_convention FROM ".MAIN_DB_PREFIX."bareme_prestation_convention WHERE fk_convention=".$id_convention." AND fk_condition=".$bareme->rowid;
							$prest_conv_res = $db->query($prest_conv_sql);
							if($db->num_rows($prest_conv_res) > 0){
								$trouve = true;
							}
						$i ++;
					}
					if($bareme){
						if($bareme->charge == 1){
							$taux_salarial[] = $bareme->taux_salariale;
							$taux_patronal[] = 0;
						}else if($bareme->charge == 2){
							$taux_patronal[] = $bareme->taux_patronale;
							$taux_salarial[] = 0;
						}else{
							$taux_salarial[] = $bareme->taux_salariale;
							$taux_patronal[] = $bareme->taux_patronale;
						}
					}else{
						$taux_salarial[] = 0;
						$taux_patronal[] = 0;
					}

				}else{
					$taux_salarial[] = 0;
					$taux_patronal[] = 0;
				}

		}else{
			$taux_salarial[] = 0;
			$taux_patronal[] = 0;
		}

		//print $id_prestation[$a]."--".$taux_patronal[$a].'--'.$taux_salarial[$a].'<br>';
	}
	$global = array();
	$global[0] = $taux_patronal;
	$global[1] = array_combine($id_prestation, $taux_salarial);
	return $global;

}

function salarie_prestation_simulation($db, $fk_salarie, $salaire_brut, $id_convention){

	$valeur = 0;
	$id_prestation = array();
	$taux_salarial = array();
	$taux_patronal = array();

	$sql_prestation = "SELECT * FROM ".MAIN_DB_PREFIX."type_prestation WHERE nature='obligatoire'";
		$result_prestation = $db->query($sql_prestation);
		if($result_prestation){
			$j = 0;
			$numj = $db->num_rows($result_prestation);
			while ($j < $numj) {
				$prestation = $db->fetch_object($result_bareme);
				$id_prestation[] = $prestation->rowid;
				$j++;
			}
		}

	$sql_prestation = "SELECT * FROM ".MAIN_DB_PREFIX."salarie_prestation_sociale WHERE fk_salarie=".$fk_salarie;
		$result_prestation = $db->query($sql_prestation);
		if($result_prestation){
			$j = 0;
			$numj = $db->num_rows($result_prestation);
			while ($j < $numj) {
				$prestation = $db->fetch_object($result_prestation);
				if(!in_array($prestation->fk_prestation_sociale, $id_prestation))
					$id_prestation[] = $prestation->fk_prestation_sociale;
				$j++;
			}
		}


	for ($a=0; $a < count($id_prestation); $a++) {

		$sql_bareme = "SELECT * FROM ".MAIN_DB_PREFIX."bareme_prestation WHERE fk_prestation=".$id_prestation[$a];
		$result_bareme = $db->query($sql_bareme);
		if($result_bareme){
			$num = $db->num_rows($result_bareme);
					$i = 0;
					$trouve = false;
				if($num > 0){
					while($i < $num && $trouve == false){
						$bareme = $db->fetch_object($result_bareme);
							$prest_conv_sql = "SELECT fk_convention FROM ".MAIN_DB_PREFIX."bareme_prestation_convention WHERE fk_convention=".$id_convention." AND fk_condition=".$bareme->rowid;
							$prest_conv_res = $db->query($prest_conv_sql);
							if($db->num_rows($prest_conv_res) > 0){
								$trouve = true;
							}
						$i ++;
					}
					if($bareme){
						if($bareme->charge == 1){
							$taux_salarial[] = $bareme->taux_salariale;
							$taux_patronal[] = 0;
						}else if($bareme->charge == 2){
							$taux_patronal[] = $bareme->taux_patronale;
							$taux_salarial[] = 0;
						}else{
							$taux_salarial[] = $bareme->taux_salariale;
							$taux_patronal[] = $bareme->taux_patronale;
						}
					}else{
						$taux_salarial[] = 0;
						$taux_patronal[] = 0;
					}

				}else{
					$taux_salarial[] = 0;
					$taux_patronal[] = 0;
				}

		}else{
			$taux_salarial[] = 0;
			$taux_patronal[] = 0;
		}

		//print $id_prestation[$a]."--".$taux_patronal[$a].'--'.$taux_salarial[$a].'<br>';
	}
	$global = array();
	$global[0] = $taux_patronal;
	$global[1] = array_combine($id_prestation, $taux_salarial);
	return $global;

}
//la fonction qui calcul l'indemnité du salarié
//-------------------------------------------------------------------------------------------------------------------------------------------
function salarie_indemnite($db, $fk_salarie, $salaire_base, $id_convention, $id_societe, $id_accord_etab){
	//le salarié
	$sql = "SELECT * FROM ".MAIN_DB_PREFIX."salarie WHERE rowid=".$fk_salarie;
	$res = $db->query($sql);
	$obj = $db->fetch_object($res);

	$all_indemnite_rowid = array(); //toutes les indemnités(categorie, associés)
	$all_indemnite_unique_rowid = array();//la table avec non dedoublons du premier

	$indemnite_rowid = array();
	$all_indemnite_montant = array();
	$all_indemnite_pourcentage = array();


	//Recupération de toutes les indémnités obligatoires
	$sql = "SELECT * FROM ".MAIN_DB_PREFIX."indemnite WHERE type_indemnite='obligatoire' AND active=1 AND ((fk_convention=".$id_convention." AND fk_societe=0 AND fk_accord_etablissement=0) OR (fk_convention=0 AND fk_societe=".$id_societe." AND fk_accord_etablissement=0) OR (fk_convention=0 AND fk_societe=0 AND fk_accord_etablissement=".$id_accord_etab."))";
	$oblig_indemnite = $db->query($sql);
	$prime = array();
	if($oblig_indemnite){
		$num = $db->num_rows($oblig_indemnite);
		$i = 0;
		while ($i < $num) {
			$obj_oblig_indemnite = $db->fetch_object($oblig_indemnite);
			$all_indemnite_rowid[$i] = $obj_oblig_indemnite->rowid;
			$i ++;
		}
	}

	//Récupération des indemnités liés à la catégorie
	$sql = "SELECT * FROM ".MAIN_DB_PREFIX."categorie_indemnite WHERE fk_categorie=".$obj->fk_categorie;
	$categ_indemnite = $db->query($sql);
	$taille = count($all_indemnite_rowid);
	if($categ_indemnite){
		$num = $db->num_rows($categ_indemnite);
		$i = 0;
		while ($i < $num) {
			$obj_categ_indemnite = $db->fetch_object($categ_indemnite);
			$all_indemnite_rowid[$taille + $i] = $obj_categ_indemnite->fk_indemnite;
			$i ++;
		}
	}
	//primes individuelles
	$sql = "SELECT * FROM ".MAIN_DB_PREFIX."salarie_indemnite WHERE fk_salarie=".$fk_salarie;
	$sal_indemnite = $db->query($sql);
	$taille = count($all_indemnite_rowid);
	if($sal_indemnite){
		$num = $db->num_rows($sal_indemnite);
		$i = 0;
		while ($i < $num) {

			$obj_sal_indemnite = $db->fetch_object($sal_indemnite);
			$all_indemnite_rowid[$taille + $i] = $obj_sal_indemnite->fk_indemnite;
			$i ++;
		}
	}

	$taille = count($all_indemnite_rowid);
	$all_indemnite_unique_rowid [0] = $all_indemnite_rowid[0];
	$a = 1;
	$trouve = false;
	while ($a < $taille) {
		$i = 0;
		$tail = count($all_indemnite_unique_rowid);
		while ($i < $tail) {
			if($all_indemnite_unique_rowid[$i] == $all_indemnite_rowid[$a])
				$trouve = true;
				$i ++;
			}
			if($trouve == false){
				$all_indemnite_unique_rowid[$tail] = $all_indemnite_rowid[$a];
		}
		//print "....".$all_indemnite_rowid[$a];
		$a ++;
	}

	//Traitements sur indemnités------------------------------------------
//Indemnités
$taille = count($all_indemnite_unique_rowid);
for($i = 0; $i < $taille; $i++){
	$sql = "SELECT * FROM ".MAIN_DB_PREFIX."indemnite WHERE rowid=".$all_indemnite_unique_rowid[$i]." AND active=1";
	$indemnite_res = $db->query($sql);
	if($indemnite_res){
		$indemnite = $db->fetch_object($indemnite_res);

		$cond_indemnite_sql = "SELECT * FROM ".MAIN_DB_PREFIX."condition_indemnite WHERE fk_indemnite=".$indemnite->rowid;
		$result_cond_indemnite = $db->query($cond_indemnite_sql);
		if($result_cond_indemnite){
			$num = $db->num_rows($result_cond_indemnite);
			$j = 0;
			while ($j < $num) {
				$correspond = false;
				$cond_indemnite = $db->fetch_object($result_cond_indemnite);
			//si pas de type salarié correspondant on verifie la catégorie
					$cond_categ_sql = "SELECT * FROM ".MAIN_DB_PREFIX."condition_categorie_indemnite WHERE fk_condition=".$cond_indemnite->rowid." AND fk_categorie=".($obj->fk_categorie?:0);
					$result_cond_categ = $db->query($cond_categ_sql);
					$cond_categ = $db->fetch_object($result_cond_categ);
					if(!$cond_categ->rowid){
						$cond_categ_sql = "SELECT * FROM ".MAIN_DB_PREFIX."condition_categorie_indemnite WHERE fk_condition=".$cond_indemnite->rowid." AND fk_categorie=0";
						$result_cond_categ = $db->query($cond_categ_sql);
						$cond_categ = $db->fetch_object($result_cond_categ);
					}

					if($cond_categ->rowid){ //verification de la catégorie
						$correspond = true;

					}

				if($correspond == true){
					//correspondace avec type salarié ou catégorie
					$tail = count($indemnite_rowid);
					if($cond_indemnite->superieur > 0){
						if($salaire_base < $cond_indemnite->superieur){
							if($cond_indemnite->type_montant == "forfait"){
								$indemnite_rowid[$tail] = $indemnite->rowid;
								$all_indemnite_montant[$tail] = $cond_indemnite->forfait;
								$all_indemnite_pourcentage[$tail] = "100";

							}
						}else if(($cond_indemnite->pourcentage*$salaire_base/100) < $cond_indemnite->superieur)
							if($cond_indemnite->type_montant == "pourcentage"){
								if(($cond_indemnite->pourcentage*$salaire_base/100) < $cond_indemnite->minimum_perception){
									$indemnite_rowid[$tail] = $indemnite->rowid;
									$all_indemnite_montant[$tail] = $cond_indemnite->minimum_perception;
									$all_indemnite_pourcentage[$tail] = "100";

								}else{
									$indemnite_rowid[$tail] = $indemnite->rowid;
									$all_indemnite_montant[$tail] = $cond_indemnite->pourcentage*$salaire_base/100;
									$all_indemnite_pourcentage[$tail] = $cond_indemnite->pourcentage;

								}
							}
					}else{
						if($cond_indemnite->type_montant == "forfait"){
							$indemnite_rowid[$tail] = $indemnite->rowid;
							$all_indemnite_montant[$tail] = $cond_indemnite->forfait;
							$all_indemnite_pourcentage[$tail] = "100";

						}else{
							if(($cond_indemnite->pourcentage*$salaire_base/100) < $cond_indemnite->minimum_perception){
								$indemnite_rowid[$tail] = $indemnite->rowid;
								$all_indemnite_montant[$tail] = $cond_indemnite->minimum_perception;
								$all_indemnite_pourcentage[$tail] = "100";
							}else{
								//print $cond_indemnite->pourcentage*$salaire_base/100;
								$indemnite_rowid[$tail] = $indemnite->rowid;
								$all_indemnite_montant[$tail] = $cond_indemnite->pourcentage*$salaire_base/100;
								$all_indemnite_pourcentage[$tail] = $cond_indemnite->pourcentage;

							}
						}
					}
						$j = $num;
				}
		$j++;
	}
}
	}
}

	$tableau[0] = $all_indemnite_pourcentage;
	$tableau[1] = array_combine($indemnite_rowid, $all_indemnite_montant);
	return $tableau;

}

//la fonction qui calcul la prime du salarié
//------------------------------------------------------------------------------------------------------------------------------------------------
function salarie_prime($db, $fk_salarie, $salaire_base, $id_convention, $id_societe, $id_accord_etab){
	//le salarié
	$sql = "SELECT * FROM ".MAIN_DB_PREFIX."salarie WHERE rowid=".$fk_salarie;
	$res = $db->query($sql);
	$obj = $db->fetch_object($res);

	$all_prime_rowid = array(); //toutes les indemnités(categorie, associés)
	$all_prime_unique_rowid = array();//la table avec non dedoublons du premier

	$prime_rowid = array();
	$all_prime_montant = array();
	$all_prime_pourcentage = array();


	//Recupération de toutes les indémnités obligatoires
	$sql = "SELECT * FROM ".MAIN_DB_PREFIX."primes WHERE type_prime='obligatoire' AND active=1 AND ((fk_convention=".$id_convention." AND fk_societe=0 AND fk_accord_etablissement=0) OR (fk_convention=0 AND fk_societe=".$id_societe." AND fk_accord_etablissement=0) OR (fk_convention=0 AND fk_societe=0 AND fk_accord_etablissement=".$id_accord_etab."))";
	$oblig_prime = $db->query($sql);
	$prime = array();
	if($oblig_prime){
		$num = $db->num_rows($oblig_prime);
		$i = 0;
		while ($i < $num) {
			$obj_oblig_prime = $db->fetch_object($oblig_prime);
			$all_prime_rowid[$i] = $obj_oblig_prime->rowid;
			$i ++;
		}

	}

	//Récupération des indemnités liés à la catégorie
	$sql = "SELECT * FROM ".MAIN_DB_PREFIX."categorie_prime WHERE fk_categorie=".$obj->fk_categorie;
	$categ_prime = $db->query($sql);
	$taille = count($all_prime_rowid);
	if($categ_prime){
		$num = $db->num_rows($categ_prime);
		$i = 0;
		while ($i < $num) {
			$obj_categ_prime = $db->fetch_object($categ_prime);
			$all_prime_rowid[$taille + $i] = $obj_categ_prime->fk_prime;
			$i ++;
		}
	}
	//primes individuelles
	$sql = "SELECT * FROM ".MAIN_DB_PREFIX."salarie_prime WHERE fk_salarie=".$fk_salarie;
	$sal_prime = $db->query($sql);
	$taille = count($all_prime_rowid);
	if($sal_prime){
		$num = $db->num_rows($sal_prime);
		$i = 0;
		while ($i < $num) {

			$obj_sal_prime = $db->fetch_object($sal_prime);
			$all_prime_rowid[$taille + $i] = $obj_sal_prime->fk_prime;
			$i ++;
		}
	}

	$taille = count($all_prime_rowid);
	$all_prime_unique_rowid [0] = $all_prime_rowid[0];
	$a = 1;
	$trouve = false;
	while ($a < $taille) {
		$i = 0;
		$tail = count($all_prime_unique_rowid);
		while ($i < $tail) {
			if($all_prime_unique_rowid[$i] == $all_prime_rowid[$a])
				$trouve = true;
				$i ++;
			}
			if($trouve == false){
				$all_prime_unique_rowid[$tail] = $all_prime_rowid[$a];
		}
		//print "....".$all_prime_rowid[$a];
		$a ++;
	}

	//Traitements sur indemnités------------------------------------------
//Indemnités
$taille = count($all_prime_unique_rowid);
for($i = 0; $i < $taille; $i++){
	$sql = "SELECT * FROM ".MAIN_DB_PREFIX."primes WHERE rowid=".$all_prime_unique_rowid[$i]." AND active=1";
	$prime_res = $db->query($sql);
	if($prime_res){
		$primes = $db->fetch_object($prime_res);
		//print $primes->libelle."-----";

		$cond_prime_sql = "SELECT * FROM ".MAIN_DB_PREFIX."condition_prime WHERE fk_prime=".$primes->rowid;
		$result_cond_prime = $db->query($cond_prime_sql);
		if($result_cond_prime){
			$num = $db->num_rows($result_cond_prime);
			$j = 0;
			while ($j < $num) {
				$correspond = false;
				$cond_prime = $db->fetch_object($result_cond_prime);
			//si pas de type salarié correspondant on verifie la catégorie
					$cond_categ_sql = "SELECT * FROM ".MAIN_DB_PREFIX."condition_categorie_prime WHERE fk_condition=".$cond_prime->rowid." AND fk_categorie=".($obj->fk_categorie?:0);
					$result_cond_categ = $db->query($cond_categ_sql);
					$cond_categ = $db->fetch_object($result_cond_categ);
					if(!$cond_categ->rowid){
						$cond_categ_sql = "SELECT * FROM ".MAIN_DB_PREFIX."condition_categorie_prime WHERE fk_condition=".$cond_prime->rowid." AND fk_categorie=0";
						$result_cond_categ = $db->query($cond_categ_sql);
						$cond_categ = $db->fetch_object($result_cond_categ);
					}

					if($cond_categ->rowid){ //verification de la catégorie
						$correspond = true;

					}

				if($correspond == true){
					//correspondace avec type salarié ou catégorie
					$tail = count($prime_rowid);
					if($cond_prime->superieur > 0){
						if($salaire_base < $cond_prime->superieur){
							if($cond_prime->type_montant == "forfait"){
								$prime_rowid[$tail] = $primes->rowid;
								$all_prime_montant[$tail] = $cond_prime->forfait;
								$all_prime_pourcentage[$tail] = "100";

							}
						}else if(($cond_prime->pourcentage*$salaire_base/100) < $cond_prime->superieur)
							if($cond_prime->type_montant == "pourcentage"){
								if(($cond_prime->pourcentage*$salaire_base/100) < $cond_prime->minimum_perception){
									$prime_rowid[$tail] = $primes->rowid;
									$all_prime_montant[$tail] = $cond_prime->minimum_perception;
									$all_prime_pourcentage[$tail] = "100";

								}else{
									$prime_rowid[$tail] = $primes->rowid;
									$all_prime_montant[$tail] = $cond_prime->pourcentage*$salaire_base/100;
									$all_prime_pourcentage[$tail] = $cond_prime->pourcentage;

								}
							}
					}else{
						if($cond_prime->type_montant == "forfait"){
							$prime_rowid[$tail] = $primes->rowid;
							$all_prime_montant[$tail] = $cond_prime->forfait;
							$all_prime_pourcentage[$tail] = "100";

						}else{
							if(($cond_prime->pourcentage*$salaire_base/100) < $cond_prime->minimum_perception){
								$prime_rowid[$tail] = $primes->rowid;
								$all_prime_montant[$tail] = $cond_prime->minimum_perception;
								$all_prime_pourcentage[$tail] = "100";
							}else{
								//print $cond_prime->pourcentage*$salaire_base/100;
								$prime_rowid[$tail] = $primes->rowid;
								$all_prime_montant[$tail] = $cond_prime->pourcentage*$salaire_base/100;
								$all_prime_pourcentage[$tail] = $cond_prime->pourcentage;

							}
						}
					}
						$j = $num;
				}
		$j++;
	}
}
	}
}

	$tableau[0] = $all_prime_pourcentage;
	$tableau[1] = array_combine($prime_rowid, $all_prime_montant);
	return $tableau;

}

//la fonction qui calcul l'heure supplémentaire du salarié
//-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------
function salarie_heure_sup($db, $fk_salarie, $mois, $annee){
	//le salarié
	$sql = "SELECT * FROM ".MAIN_DB_PREFIX."salarie WHERE rowid=".$fk_salarie;
	$res = $db->query($sql);
	$obj = $db->fetch_object($res);

	//Heures sup
	$array_taux_hsup = array(); //taux heures sup
	$array_nbre_hsup = array(); //nombre heures sup
	$array_id_hsup = array(); //rowid type heures sup

	//$mois_nbre_hsup = array(); //le mois dans lequel les heures sup ont été éffectuées
	//Récuperation des heures Supplémentaires
	$sql = "SELECT * FROM ".MAIN_DB_PREFIX."salarie_heure_sup WHERE fk_salarie=".$fk_salarie." AND annee=".$annee." AND mois=".$mois;
	$sal_heure_sup = $db->query($sql);
	if($sal_heure_sup){
		$num = $db->num_rows($sal_heure_sup);
		$i = 0;
		while ($i < $num) {
			$obj_sal_heure_sup = $db->fetch_object($sal_heure_sup);
			$hs_sql = "SELECT * FROM ".MAIN_DB_PREFIX."heure_sup WHERE rowid=".$obj_sal_heure_sup->fk_heur_sup;
			$type_sal_heure_sup = $db->query($hs_sql);
			$obj_type_sal_heure_sup = $db->fetch_object($type_sal_heure_sup);

			$array_taux_hsup[$i] = str_replace(array( "%"), '', $obj_type_sal_heure_sup->taux);
			//print $array_taux_hsup[$i]."*****<br>";
			$array_id_hsup[$i] = $obj_type_sal_heure_sup->rowid;
			$array_nbre_hsup[$i] = $obj_sal_heure_sup->nb_heure;
			$i ++;
		}
	}

	$tableau[0] = $array_id_hsup;
	//print count($array_id_hsup);
	$tableau[1] = $array_taux_hsup;
	$tableau[2] = $array_nbre_hsup;


	return $tableau;
}

function prime_anciennete($db, $id_salarie, $id_convention, $mois='', $annee='', $fk_salarie){

	$obj = null;
	if(empty($mois)){
		$mois = (int) date('m');
	}
	if(empty($annee)){
		$annee = (int) date('Y');
	}
				$verif_sql = "SELECT * FROM ".MAIN_DB_PREFIX."primes WHERE rowid=1";
				$verif_result = $db->query($verif_sql);
				if($verif_result){
					$verif_obj = $db->fetch_object($verif_result);
					if($verif_obj && $id_salarie){
						$covSql = "SELECT date_anciennete FROM ".MAIN_DB_PREFIX."salarie WHERE rowid=".$id_salarie." AND date_anciennete IS NOT NULL";
						$result = $db->query($covSql);//= $db->query($covSql);
						print $db->error();
						if($db->num_rows($result) > 0){
							$obj = $db->fetch_object($result);

						}elseif(!empty($fk_salarie)){

							$covSql = "SELECT dateemployment as date_anciennete FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$fk_salarie." AND dateemployment IS NOT NULL";
								$result = $db->query($covSql);//= $db->query($covSql);
								$obj = $db->fetch_object($result);

						}

						$anciennete = 0;
						if($obj){

							$nb_j = cal_days_in_month(CAL_GREGORIAN, $mois, $annee);
							$date_donnee = new DateTime($obj->date_anciennete); // Date donnée
							$aujourdhui = new DateTime($annee."-".$mois."-".$nb_j); // Date d'appele

							
							$interval = $date_donnee->diff($aujourdhui);
							$jours = $interval->days;
							$anciennete =  floor($jours/365);						

						}

						if($anciennete == 0){
							$taux = 0;
						}else{

							$v_sql = "SELECT * FROM ".MAIN_DB_PREFIX."condition_anciennete WHERE fk_convention=".$id_convention." AND nombre_annee=".$anciennete;
							$v_result = $db->query($v_sql);
							$v_obj = $db->fetch_object($v_result);
							if($v_obj){
								$taux = $v_obj->taux;
							}else{

								$v_sql = "SELECT * FROM ".MAIN_DB_PREFIX."condition_anciennete WHERE fk_convention=".$id_convention." AND nombre_annee='+'";
								$v_result = $db->query($v_sql);
								$v_obj = $db->fetch_object($v_result);
								$taux = $v_obj->taux;
							}
						}
				}
				//print "<br> Anciennete = ".$anciennete."<br>";
				//print "Prime liée à l'ancienneté à un taux de = ".$taux."%";
			}
			$anciennete_taux = array();
			$anciennete_taux[0] = $anciennete?:0;
			$anciennete_taux[1] = $taux?:0;
			$anciennete_taux[2] = $verif_obj->affiche_bulletin?:"Oui";
			$anciennete_taux[3] = $verif_obj->soumis_cotisation?:"Oui";
			$anciennete_taux[4] = $verif_obj->soumis_impot?:"Oui";

			$anciennete_taux[5] = $verif_obj->exonere?:"Oui";//retiré du salaire de base
			$anciennete_taux[6] = $verif_obj->libelle?:"Oui";

			return $anciennete_taux;
}

function indemnite_flottante($db, $fk_salarie){
	$all_indemnite_id = array();
	$all_indemnite_montant = array();

	$somme = 0;
	$sql_fl = "SELECT fk_indemnite, montant FROM ".MAIN_DB_PREFIX."salarie_indemnite_flottante WHERE fk_salarie=".$fk_salarie;
	$result_fl = $db->query($sql_fl);
	if($result_fl){
		$i = 0;
		$num = $db->num_rows($result_fl);
		while ($i < $num) {
			$obj_fl = $db->fetch_object($result_fl);
			$sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."indemnite WHERE rowid=".$obj_fl->fk_indemnite." AND active=1";
			$result = $db->query($sql);
			if($result){
				$obj = $db->fetch_object($result);
				$all_indemnite_id[$i] = $obj->rowid;

				$all_indemnite_montant[$i] = $obj_fl->montant;
			}
			$i ++;
		}
	}
	return array_combine($all_indemnite_id, $all_indemnite_montant);
}

//prime flottante du salarié
function prime_flottante($db, $fk_salarie){
	$all_prime_id = array();
	$all_prime_montant = array();
	$somme = 0;
	$sql_fl = "SELECT fk_prime, montant FROM ".MAIN_DB_PREFIX."salarie_prime_flottante WHERE fk_salarie=".$fk_salarie;
	$result_fl = $db->query($sql_fl);
	if($result_fl){
		$i = 0;
		$num = $db->num_rows($result_fl);
		while ($i < $num) {
			$obj_fl = $db->fetch_object($result_fl);
			$sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."primes WHERE rowid=".$obj_fl->fk_prime." AND active=1";
			$result = $db->query($sql);
			if($result){
				$obj = $db->fetch_object($result);
					$all_prime_id[$i] = $obj->rowid;
					$all_prime_montant[$i] = $obj_fl->montant;
			}
			$i ++;
		}
	}
	return array_combine($all_prime_id, $all_prime_montant);
}

//prime exceptionnelle du salarié

function salarie_prime_exceptionnelle($db, $fk_salarie, $mois, $annee){
	$all_prime_info = array();
	$all_prime_montant = array();
	$somme = 0;
	$sql_except = "SELECT * FROM ".MAIN_DB_PREFIX."salarie_prime_exceptionnelle WHERE fk_salarie=".$fk_salarie;
	$sql_except .= " AND (YEAR(date_limit)=".$annee." AND MONTH(date_limit)=".$mois.")";
	$result_except = $db->query($sql_except);

	if($result_except){
		$i = 0;
		$num = $db->num_rows($result_except);
		while ($i < $num) {
			$obj_except = $db->fetch_object($result_except);
			$all_prime_info[$i][0] = $obj_except->rowid;
			$all_prime_info[$i][1] = $obj_except->montant;
			$all_prime_info[$i][2] = $obj_except->affiche_bulletin;
			$all_prime_info[$i][3] = 100;
			$all_prime_info[$i][4] = $obj_except->libelle;
			$all_prime_info[$i][5] = $obj_except->soumis_impot;
			$all_prime_info[$i][6] = $obj_except->soumis_cotisation;
			$i ++;

		}
	}
	return $all_prime_info;
}

//Avance/Acompte du salarié
function salarie_avance_acompte_avec_save($db, $fk_salarie, $mois, $annee){
	$montant_avance_paye = array();
	$id_avance = array();
	if(!empty($fk_salarie)){
		$sql_avance = "SELECT rowid, montant_par_mois, montant_paye FROM ".MAIN_DB_PREFIX."salarie_avance WHERE fk_salarie=".$fk_salarie." AND CONVERT(montant_paye, float) < CONVERT(montant_total, float) AND ((annee_debut_paiement=".(int)$annee." AND mois_debut_paiement<=".(int)$mois.") OR (annee_debut_paiement <".(int)$annee."))";
		$res_avance = $db->query($sql_avance);
		if($res_avance){
			$nb_avance = $db->num_rows($res_avance);
			$i = 0;
			while($i < $nb_avance){

				$obj_avance = $db->fetch_object($res_avance);
				$detail_avance_sql = "SELECT fk_avance, rowid, montant_paye FROM ".MAIN_DB_PREFIX."detail_avance WHERE fk_avance=".$obj_avance->rowid." AND mois_paiement=".((int)$mois);
				$detail_avance_res = $db->query($detail_avance_sql);
				$nb_detail_avance = $db->num_rows($detail_avance_res);
				if($nb_detail_avance == 0){
						//paiement du montant à payer par mois de l'avance/acompte

						$sql_paiement = "INSERT INTO ".MAIN_DB_PREFIX."detail_avance (fk_avance,annee_paiement,mois_paiement,montant_paye)
						VALUES(".$obj_avance->rowid.",".((int)$annee).",".((int)$mois).",'".$obj_avance->montant_par_mois."')";

						$res_paiement = $db->query($sql_paiement);

						//Mise à jour de l'avance/acompte
						$sql_update = "UPDATE ".MAIN_DB_PREFIX."salarie_avance SET montant_paye=".($obj_avance->montant_paye + $obj_avance->montant_par_mois)."
						 WHERE rowid=".$obj_avance->rowid;
						$res_update = $db->query($sql_update);


					$montant_avance_paye[] = $obj_avance->montant_par_mois;
					$id_avance[] = $obj_avance->rowid;

				}else{

					$obj_detail_avance = $db->fetch_object($detail_avance_res);
					$montant_avance_paye[] = $obj_detail_avance->montant_paye;
					$id_avance[] = $obj_detail_avance->fk_avance;
				}
				$i ++;
			}


				$sql_avance = "SELECT rowid FROM ".MAIN_DB_PREFIX."salarie_avance WHERE fk_salarie=".$fk_salarie." AND montant_paye = montant_total";
				$res_avance = $db->query($sql_avance);
				$nb_avance = $db->num_rows($res_avance);
				$i = 0;
					while($i < $nb_avance){
						$obj_avance = $db->fetch_object($res_avance);
						$detail_avance_sql = "SELECT montant_paye, mois_paiement FROM ".MAIN_DB_PREFIX."detail_avance WHERE fk_avance=".$obj_avance->rowid." AND mois_paiement=".$mois;
						$detail_avance_res = $db->query($detail_avance_sql);
						if($db->num_rows($detail_avance_res) > 0){
							$obj_detail_avance = $db->fetch_object($detail_avance_res);
							$montant_avance_paye[] = $obj_detail_avance->montant_paye;
							$id_avance[] = $obj_avance->rowid;
						}
						$i ++;
					}


		}
	}
	$avance = array_combine($id_avance, $montant_avance_paye);
	return $avance;
}

function salarie_avance_acompte_sans_save($db, $fk_salarie, $mois, $annee){
	$montant_avance_paye = array();
	$id_avance = array();
	$sql_verif = "SELECT rowid FROM ".MAIN_DB_PREFIX."bulletin WHERE fk_salarie=".$fk_salarie." AND annee=".$annee." AND mois=".$mois;
	$res_verif = $db->query($sql_verif);
	$rowid_bulletin = $db->num_rows($res_verif);
	if($rowid_bulletin == 0){
		if(!empty($fk_salarie)){
			$sql_avance = "SELECT rowid, montant_par_mois, montant_paye FROM ".MAIN_DB_PREFIX."salarie_avance WHERE fk_salarie=".$fk_salarie." AND montant_paye < montant_total AND ((annee_debut_paiement=".(int)$annee." AND mois_debut_paiement<=".(int)$mois.") OR (annee_debut_paiement >".(int)$annee."))";
			$res_avance = $db->query($sql_avance);
			if($res_avance){
				$nb_avance = $db->num_rows($res_avance);
				$i = 0;
				while($i < $nb_avance){
					$obj_avance = $db->fetch_object($res_avance);
					$montant_avance_paye[] = $obj_avance->montant_par_mois;
					$id_avance[] = $obj_avance->rowid;

					$i ++;
				}
					$sql_avance = "SELECT rowid FROM ".MAIN_DB_PREFIX."salarie_avance WHERE fk_salarie=".$fk_salarie." AND montant_paye = montant_total";
					$res_avance = $db->query($sql_avance);
					$nb_avance = $db->num_rows($res_avance);
					$i = 0;
						while($i < $nb_avance){
							$obj_avance = $db->fetch_object($res_avance);

							$detail_avance_sql = "SELECT montant_paye FROM ".MAIN_DB_PREFIX."detail_avance WHERE fk_avance=".$obj_avance->rowid." AND mois_paiement=".((int)date("m"));
							$detail_avance_res = $db->query($detail_avance_sql);
							$nb_detail_avance = $db->num_rows($detail_avance_res);
							if($nb_detail_avance > 0){
								$obj_detail_avance = $db->fetch_object($detail_avance_res);
								$montant_avance_paye[] = $obj_detail_avance->montant_paye;
								$id_avance[] = $obj_avance->rowid;

							}
						$i ++;
					}

			}
		}
	}else{
		if(!empty($fk_salarie)){
			$sql_avance = "SELECT rowid, montant_par_mois, montant_paye FROM ".MAIN_DB_PREFIX."salarie_avance WHERE fk_salarie=".$fk_salarie." AND CONVERT(montant_paye, float) < CONVERT(montant_total, float) AND ((annee_debut_paiement=".(int)$annee." AND mois_debut_paiement<=".(int)$mois.") OR (annee_debut_paiement <".(int)$annee."))";
			$res_avance = $db->query($sql_avance);
			if($res_avance){
				$nb_avance = $db->num_rows($res_avance);
				$i = 0;
				while($i < $nb_avance){
					$obj_avance = $db->fetch_object($res_avance);
					$detail_avance_sql = "SELECT fk_avance, rowid, montant_paye FROM ".MAIN_DB_PREFIX."detail_avance WHERE fk_avance=".$obj_avance->rowid." AND mois_paiement=".((int)$mois);
					$detail_avance_res = $db->query($detail_avance_sql);
					$nb_detail_avance = $db->num_rows($detail_avance_res);
					if($nb_detail_avance == 0){
							//paiement du montant à payer par mois de l'avance/acompte
	
							$sql_paiement = "INSERT INTO ".MAIN_DB_PREFIX."detail_avance (fk_avance,annee_paiement,mois_paiement,montant_paye)
							VALUES(".$obj_avance->rowid.",".((int)$annee).",".((int)$mois).",'".$obj_avance->montant_par_mois."')";
	
							$res_paiement = $db->query($sql_paiement);
	
							//Mise à jour de l'avance/acompte
							$sql_update = "UPDATE ".MAIN_DB_PREFIX."salarie_avance SET montant_paye=".($obj_avance->montant_paye + $obj_avance->montant_par_mois)."
							 WHERE rowid=".$obj_avance->rowid;
							$res_update = $db->query($sql_update);
	
	
						$montant_avance_paye[] = $obj_avance->montant_par_mois;
						$id_avance[] = $obj_avance->rowid;
	
					}else{
	
						$obj_detail_avance = $db->fetch_object($detail_avance_res);
						$montant_avance_paye[] = $obj_detail_avance->montant_paye;
						$id_avance[] = $obj_detail_avance->fk_avance;
					}
					$i ++;
				}
	
	
					$sql_avance = "SELECT rowid FROM ".MAIN_DB_PREFIX."salarie_avance WHERE fk_salarie=".$fk_salarie." AND montant_paye = montant_total";
					$res_avance = $db->query($sql_avance);
					$nb_avance = $db->num_rows($res_avance);
					$i = 0;
						while($i < $nb_avance){
							$obj_avance = $db->fetch_object($res_avance);
							$detail_avance_sql = "SELECT montant_paye, mois_paiement FROM ".MAIN_DB_PREFIX."detail_avance WHERE fk_avance=".$obj_avance->rowid." AND mois_paiement=".$mois;
							$detail_avance_res = $db->query($detail_avance_sql);
							if($db->num_rows($detail_avance_res) > 0){
								$obj_detail_avance = $db->fetch_object($detail_avance_res);
								$montant_avance_paye[] = $obj_detail_avance->montant_paye;
								$id_avance[] = $obj_avance->rowid;
							}
							$i ++;
						}
	
	
			}
		}
	}
	$avance = array_combine($id_avance, $montant_avance_paye);
	return $avance;
}


/**entete */
function pdf_pagehead(&$pdf, $onglet_salarie){

 if($onglet_salarie == ''){
	global $mysoc,$conf, $db, $fk_salarie, $fk_user, $mois, $annee, $id_accord_etab, $id_convention, $societe_Salarie, $info_soc;

	$bulletin_sql = "SELECT * FROM ".MAIN_DB_PREFIX."bulletin WHERE fk_salarie=".$fk_salarie." AND annee='".$annee."' AND mois='".$mois."'";
		$rest_bulletin = $db->query($bulletin_sql);//= $db->query($covSql);
		$bulletin_obj = $db->fetch_object($rest_bulletin);
		$salaire_base = $bulletin_obj->salaire_base;

		//Entête Droit information sur le bulletin
		$mois_tab = array(" janvier "," février "," mars "," avril "," mai "," juin "," juillet "," août "," septembre "," octobre "," novembre "," décembre ");
		$mois_courant = $mois ? : (int) date("m");
		/*$annee_courant = date("Y");

		$entete_droit = " du".$mois[$mois_courant-1]."".$annee_courant;*/
		$y = $pdf->GetY()+8;
		$pdf->SetY($y);
		$debut = DOL_DOCUMENT_ROOT;
		$tab = explode("/",$debut);
		$logodir = $conf->mycompany->dir_output;
		$logo_server = $logodir.'/logos/'.$mysoc->logo;
		if($info_soc->societe_mere == 0){
			$logo_1 = $tab[0].'/'.$tab[1].'/'.$tab[2].'/'.$tab[3].($tab[4]?'/'.$tab[4]:'').'/documents/societe/'.$bulletin_obj->fk_societe.'/logos/'.$bulletin_obj->logo_societe;
        	$logo_2 = $tab[0].'/'.$tab[1].'/dolibarr_documents/societe/'.$bulletin_obj->fk_societe.'/logos/'.($bulletin_obj->logo_societe?$bulletin_obj->logo_societe:"vide.png");

			if(is_readable($logo_2)){
				///home/dolites/public_html
				$pdf->Image($logo_2,20,12, 40,19);
			}else if(is_readable($logo_1)){
				$pdf->Image($logo_1,20,12, 40,19);
	
			}else{
	
				
				$pdf->SetFont('Helvetica','B',16);
				$pdf->SetY(12);
				$pdf->SetX(20);
				$pdf->MultiCell(40,19,utf8_decode("Logo"),0,'C');
			}

			/*$img = '../config/logo_societe/'.$bulletin_obj->fk_societe;
			if(file_exists($img.'.png')){
				$img .= '.png';
			}elseif(file_exists($img.'.jpg')){
				$img .= '.jpg';
			}else{
				$img .= '.jpeg';
			}

			if(is_readable($img)){
				$pdf->Image($logo_server,20,12, 40,19);
			}else{
				$pdf->SetFont('Helvetica','B',16);
				$pdf->SetY(12);
				$pdf->SetX(20);
				$pdf->MultiCell(40,19,utf8_decode("Logo"),0,'C');
			}*/

		}else{
				$logodir = $conf->mycompany->dir_output;
				if (!empty($conf->mycompany->multidir_output[$object->entity])) {
					$logodir = $conf->mycompany->multidir_output[$object->entity];
				}
				if (empty($conf->global->MAIN_PDF_USE_LARGE_LOGO)) {
					$logo = $logodir.'/logos/thumbs/'.$mysoc->logo_small;
				} else {
					$logo = $logodir.'/logos/'.$mysoc->logo;
				}
			
			if(is_readable($logo)){
				///home/dolites/public_html
				$pdf->Image($logo,20,12, 40,19);
			}else{
	
				
				$pdf->SetFont('Helvetica','B',16);
				$pdf->SetY(12);
				$pdf->SetX(20);
				$pdf->MultiCell(40,19,utf8_decode("Logo"),0,'C');
			}
		}

		
		$date = "Bulletin De Paie :".$mois_tab[$mois_courant-1]." ".($annee ? : date("Y"));
		$pdf->SetTextColor(0, 0, 60);
		$pdf->SetFont('Helvetica','B',16);

		$x = 104;
		$pdf->SetY($y);
		$pdf->SetX($x);
		$pdf->MultiCell(96,5,utf8_decode($date),0,'R');

		/*$pdf->SetFont('Helvetica','',9);
		$pdf->SetX($pdf->GetX());
		$pdf->Cell(18,7,utf8_decode($entete_droit),0,0,'C');*/

		$y += 5;
		$pdf->SetTextColor(0, 0, 70);
		$pdf->SetFont('Helvetica','',8);
		$x = 175;
		$pdf->SetY($y);
		$pdf->SetX($x);

		$du = "01-".$mois."-".$annee;
		$au = cal_days_in_month(CAL_GREGORIAN, $mois, $annee);
		$pdf->MultiCell(24,3,utf8_decode("du : ".$du),0,'R');

		$y += 3;
		$pdf->SetTextColor(0, 0, 70);
		$pdf->SetFont('Helvetica','',8);
		$x = 175;
		$pdf->SetY($y);
		$pdf->SetX($x);
		$pdf->MultiCell(24,3,utf8_decode("au : ".$au."-".$mois."-".$annee),0,'R');

		$y += 4;
		$pdf->SetTextColor(0, 0, 70);
		$pdf->SetFont('Helvetica','B',8);
		  $x = 150;
		  $pdf->SetY($y);
		  $pdf->SetX($x);
		  $pdf->MultiCell(49,3,utf8_decode("Société : ".$bulletin_obj->nom_societe),0,'R');

		$pdf->SetY(37);
		//--------------------------------------------------------------
		//Rectangle Employé
		$y = $pdf->GetY()+2;
		//$pdf->SetFillColor(200, 200, 200);

	   $pdf->SetX(12);
	   $pdf->Cell(61,40, "",0,0,'','true');

	   $pdf->SetX(73);
	   $pdf->Cell(65,40, "",0,0,'','true');

	   $pdf->SetX(138);
	   $pdf->MultiCell(60,40, "",0,'','true');
	   //--------------------------------------------------------------------------------

		$pdf->SetTextColor(0, 0, 70);
		$pdf->SetFont('Helvetica','B',8);


		$pdf->SetX(13);
		$pdf->SetY($y);
		$y_align = $y;
		$pdf->MultiCell(1,1,"",0,'C');

		$pdf->SetLeftMargin(13);
		$pdf->MultiCell(60,4, utf8_decode("Matricule : ".$bulletin_obj->matricule),0,'');


		$y = $pdf->GetY()+1;
		$pdf->SetY($y);
		$pdf->MultiCell(60,4, utf8_decode("Prénom : ".$bulletin_obj->prenom),0,'');


		$y = $pdf->GetY()+1;
		$pdf->SetY($y);
		$pdf->MultiCell(60,4, utf8_decode("Nom : ".$bulletin_obj->nom),0,'');


		$y = $pdf->GetY()+1;
		$pdf->SetY($y);
		$pdf->MultiCell(60,4, utf8_decode("Sexe : ".$bulletin_obj->sexe),0,'');

		$y = $pdf->GetY()+1;
	$pdf->SetY($y);
	$pdf->MultiCell(60,4, utf8_decode("Pays : ".$bulletin_obj->pays),0,'');


	$y = $pdf->GetY()+1;
	$pdf->SetY($y);
	$pdf->MultiCell(60,4, utf8_decode("Ville : ".$bulletin_obj->ville),0,'');


	$y = $pdf->GetY()+1;
	$pdf->SetY($y);
	$pdf->MultiCell(60,4, utf8_decode("Tel : ".$bulletin_obj->tel),0,'');



		$y_apres_entete = $y = $pdf->GetY();
	//******************************************************************************************** */
	// Adresse et Contact
	$pdf->SetY($y_align+1);
	$pdf->SetLeftMargin(73);

	//$pdf->MultiCell(60,4, utf8_decode("Addresse : ".$bulletin_obj->addresse),0,'');

	$y = $pdf->GetY()+1;
	$pdf->SetY($y);
	$pdf->MultiCell(60,4, utf8_decode("Situation familiale : ".$bulletin_obj->situation_familiale),0,'');

		$y = $pdf->GetY()+1;
		$pdf->SetY($y);
		$pdf->MultiCell(60,4, utf8_decode("Nombre enfant : ".$bulletin_obj->nombre_enfant."/".$bulletin_obj->nombre_enfant_hand),0,'');

		$y = $pdf->GetY()+1;
		$pdf->SetY($y);
      $pdf->SetFillColor(245, 245, 245);
      $pdf->MultiCell(65,4, utf8_decode("I.N.P.S : ".$bulletin_obj->inps),0,'L');

	  $y = $pdf->GetY()+1;
      $pdf->SetY($y);
      $pdf->SetFillColor(245, 245, 245);
      $pdf->MultiCell(65,4, utf8_decode("AMO : ".$bulletin_obj->amo),0,'L');


	//$y = $pdf->GetY()+1;
	//$pdf->SetY($y);
	//$pdf->MultiCell(60,4, utf8_decode("E-mail : ".$bulletin_obj->email),0,'');




	//********************************************************************************************** */
	//Information sur l'emploi
	$salaire_base = 0;
		$pdf->SetY($y_align+1);

		$categ = $bulletin_obj->categorie;
		if(!empty($bulletin_obj->echelon))
			$categ .= '==>'.$bulletin_obj->echelon;
		$pdf->SetLeftMargin(138);
		$pdf->MultiCell(60,4, utf8_decode("Categorie : ".$categ),0,'');
		$salaire_base = $bulletin_obj->salaire_base;

		$y = $pdf->GetY()+1;
		$pdf->SetY($y);
		$pdf->MultiCell(60,4, utf8_decode("Niveau salarié : ".$bulletin_obj->type_salarie),0,'');

		$y = $pdf->GetY()+1;
		$pdf->SetY($y);
		$pdf->MultiCell(60,4, utf8_decode("Type de contrat : ".$bulletin_obj->contrat),0,'');


		$y = $pdf->GetY()+1;
		$pdf->SetY($y);
		$pdf->MultiCell(60,4, utf8_decode("Fonction : ".$bulletin_obj->fonction),0,'');

		$y = $pdf->GetY()+1;
		$pdf->SetY($y);
		$pdf->MultiCell($pdf->GetX(),4, utf8_decode("Salaire de base : ".$bulletin_obj->salaire_base),0,'');

		$y = $pdf->GetY()+1;
		$pdf->SetY($y);
		$pdf->MultiCell($pdf->GetX(),4, utf8_decode("Date d'embauche : ".$bulletin_obj->date_embauche),0,'');
	}else{
		global $mysoc, $conf, $db, $id_societe, $obj_salarie, $obj_user, $societe_Salarie, $y, $mois, $annee, $id_accord_etab, $id_convention, $info_soc;

		//Entête Droit information sur le bulletin
		$mois_tab = array(" janvier "," février "," mars "," avril "," mai "," juin "," juillet "," août "," septembre "," octobre "," novembre "," décembre ");
		$mois_courant = $mois ? : (int) date("m");
		/*$annee_courant = date("Y");

		$entete_droit = " du".$mois[$mois_courant-1]."".$annee_courant;*/
		$y = $pdf->GetY()+8;
		$pdf->SetY($y);
		$debut = DOL_DOCUMENT_ROOT;
		$tab = explode("/",$debut);
		$logodir = $conf->mycompany->dir_output;
		if($info_soc->societe_mere == 0){
			$logo_1 = $tab[0].'/'.$tab[1].'/'.$tab[2].'/'.$tab[3].'/documents/societe/'.$societe_Salarie->rowid.'/logos/'.$societe_Salarie->logo;
			$logo_2 = $tab[0].'/'.$tab[1].'/dolibarr_documents/societe/'.$societe_Salarie->rowid.'/logos/'.($societe_Salarie->logo?$societe_Salarie->logo:"vide.png");

			if(is_readable($logo_2)){
				///home/dolites/public_html
				$pdf->Image($logo_2,20,12, 40,19);
			}else if(is_readable($logo_1)){
				$pdf->Image($logo_1,20,12, 40,19);
	
			}else{
	
				
				$pdf->SetFont('Helvetica','B',16);
				$pdf->SetY(12);
				$pdf->SetX(20);
				$pdf->MultiCell(40,19,utf8_decode("Logo"),0,'C');
			}

			/*$img = '../config/logo_societe/'.$bulletin_obj->fk_societe;
			if(file_exists($img.'.png')){
				$img .= '.png';
			}elseif(file_exists($img.'.jpg')){
				$img .= '.jpg';
			}else{
				$img .= '.jpeg';
			}

			if(is_readable($img)){
				$pdf->Image($logo_server,20,12, 40,19);
			}else{
				$pdf->SetFont('Helvetica','B',16);
				$pdf->SetY(12);
				$pdf->SetX(20);
				$pdf->MultiCell(40,19,utf8_decode("Logo"),0,'C');
			}*/

		}else{
				$logodir = $conf->mycompany->dir_output;
				if (!empty($conf->mycompany->multidir_output[$object->entity])) {
					$logodir = $conf->mycompany->multidir_output[$object->entity];
				}
				if (empty($conf->global->MAIN_PDF_USE_LARGE_LOGO)) {
					$logo = $logodir.'/logos/thumbs/'.$mysoc->logo_small;
				} else {
					$logo = $logodir.'/logos/'.$mysoc->logo;
				}
			
			if(is_readable($logo)){
				///home/dolites/public_html
				$pdf->Image($logo,20,12, 40,19);
			}else{
	
				
				$pdf->SetFont('Helvetica','B',16);
				$pdf->SetY(12);
				$pdf->SetX(20);
				$pdf->MultiCell(40,19,utf8_decode("Logo"),0,'C');
			}
		}
		$date = "Bulletin De Paie :".$mois_tab[$mois_courant-1]." ".($annee ? : date("Y"));
		$pdf->SetTextColor(0, 0, 60);
		$pdf->SetFont('Helvetica','B',16);

		$x = 104;
		$pdf->SetY($y);
		$pdf->SetX($x);
		$pdf->MultiCell(96,5,utf8_decode($date),0,'R');

		/*$pdf->SetFont('Helvetica','',9);
		$pdf->SetX($pdf->GetX());
		$pdf->Cell(18,7,utf8_decode($entete_droit),0,0,'C');*/

		$y += 5;
		$pdf->SetTextColor(0, 0, 70);
		$pdf->SetFont('Helvetica','',8);
		$x = 175;
		$pdf->SetY($y);
		$pdf->SetX($x);

		$du = "01-".$mois."-".$annee;
		$au = cal_days_in_month(CAL_GREGORIAN, $mois, $annee);
		$pdf->MultiCell(24,3,utf8_decode("du : ".$du),0,'R');

		$y += 3;
		$pdf->SetTextColor(0, 0, 70);
		$pdf->SetFont('Helvetica','',8);
		$x = 175;
		$pdf->SetY($y);
		$pdf->SetX($x);
		$pdf->MultiCell(24,3,utf8_decode("au : ".$au."-".$mois."-".$annee),0,'R');

		$y += 4;
		$pdf->SetTextColor(0, 0, 70);
		$pdf->SetFont('Helvetica','B',8);
		  $x = 150;
		  $pdf->SetY($y);
		  $pdf->SetX($x);
		  $pdf->MultiCell(49,3,utf8_decode("Société : ".$societe_Salarie->nom),0,'R');

		$pdf->SetY(37);
		//--------------------------------------------------------------
		//Rectangle Employé
		$y = $pdf->GetY()+2;
		//$pdf->SetFillColor(200, 200, 200);

	   $pdf->SetX(12);
	   $pdf->Cell(61,40, "",0,0,'','true');

	   $pdf->SetX(73);
	   $pdf->Cell(65,40, "",0,0,'','true');

	   $pdf->SetX(138);
	   $pdf->MultiCell(60,40, "",0,'','true');
	   //--------------------------------------------------------------------------------

		$pdf->SetTextColor(0, 0, 70);
		$pdf->SetFont('Helvetica','B',8);


		$pdf->SetX(13);
		$pdf->SetY($y);
		$y_align = $y;
		$pdf->MultiCell(1,1,"",0,'C');

		$pdf->SetLeftMargin(13);
		$pdf->MultiCell(60,4, utf8_decode("Matricule : ".$obj_salarie->matricule),0,'');


		$y = $pdf->GetY()+1;
		$pdf->SetY($y);
		$pdf->MultiCell(60,4, utf8_decode("Prénom : ".$obj_user->firstname),0,'');


		$y = $pdf->GetY()+1;
		$pdf->SetY($y);
		$pdf->MultiCell(60,4, utf8_decode("Nom : ".$obj_user->lastname),0,'');


		$y = $pdf->GetY()+1;
		$pdf->SetY($y);
		$sexe = "Masculin";
		if($obj_user->gender == 'woman')
			$sexe = "Feminin";
		elseif($obj_user->gender != 'man')
			$sexe = "Autre";

		$pdf->MultiCell(60,4, utf8_decode("Sexe : ".$sexe),0,'');

		$pays_Sql = "SELECT * FROM ".MAIN_DB_PREFIX."c_country where rowid=".$obj_user->fk_country;
	$pays_Result = $db->query($pays_Sql);
	$pays = "N/A";
	if(!empty($obj_user->fk_country))
		$pays = $db->fetch_object($pays_Result)->label;

	$y = $pdf->GetY()+1;
	$pdf->SetY($y);
	$pdf->MultiCell(60,4, utf8_decode("Pays : ".$pays),0,'');


	$y = $pdf->GetY()+1;
	$pdf->SetY($y);
	$pdf->MultiCell(60,4, utf8_decode("Ville : ".$obj_user->town),0,'');


	$y = $pdf->GetY()+1;
	$pdf->SetY($y);
	$pdf->MultiCell(60,4, utf8_decode("Tel : ".$obj_user->user_mobile),0,'');






		$y_apres_entete = $y = $pdf->GetY();
	//******************************************************************************************** */
	// Adresse et Contact
	$pdf->SetY($y_align+1);
	$pdf->SetLeftMargin(73);

	//$pdf->MultiCell(60,4, utf8_decode("Addresse : ".$obj_user->address),0,'');

	$pays_Sql = "SELECT * FROM ".MAIN_DB_PREFIX."c_country where rowid=".$obj_user->fk_country;
	$pays_Result = $db->query($pays_Sql);
	$pays = "N/A";
	if(!empty($obj_user->fk_country))
		$pays = $db->fetch_object($pays_Result)->label;

	$y = $pdf->GetY()+1;
	$pdf->MultiCell(60,4, utf8_decode("Situation familiale : ".$obj_salarie->situation_familiale),0,'');


		$y = $pdf->GetY()+1;
		$pdf->SetY($y);
		$pdf->MultiCell(60,4, utf8_decode("Nombre enfant : ".$obj_salarie->nombre_enfant."/".$obj_salarie->nombre_enfant_hand),0,'');

		$y = $pdf->GetY()+1;
		$pdf->SetY($y);
		$pdf->MultiCell(60,4, utf8_decode("I.N.P.S : ".$obj_salarie->inps),0,'');

		$y = $pdf->GetY()+1;
		$pdf->SetY($y);
		$pdf->MultiCell(60,4, utf8_decode("A.M.O : ".$obj_salarie->amo),0,'');


	$y = $pdf->GetY()+1;
	$pdf->SetY($y);
	//$pdf->MultiCell(60,4, utf8_decode("E-mail : ".$obj_user->email),0,'');




	//********************************************************************************************** */
	//Information sur l'emploi
	$salaire_base = 0;
		$pdf->SetY($y_align+1);

		$grilleSql = "SELECT code_categorie FROM ".MAIN_DB_PREFIX."dcategories WHERE rowid=".$obj_salarie->fk_categorie;
		$grilleResult = $db->query($grilleSql);//= $db->query($grilleSql);
		if($grilleResult)
			$obj_grille = $db->fetch_object($grilleResult);
		$categ = $obj_grille->code_categorie;

		$grilleSql = "SELECT libelle FROM ".MAIN_DB_PREFIX."echelon WHERE rowid=".$obj_salarie->fk_echelon;
		$grilleResult = $db->query($grilleSql);//= $db->query($grilleSql);
		if($grilleResult)
			$obj_grille = $db->fetch_object($grilleResult);

		if(!empty($obj_grille->libelle))
			$categ .= '==>'.$obj_grille->libelle;
		$pdf->SetLeftMargin(138);
		$pdf->MultiCell(60,4, utf8_decode("Categorie : ".$categ),0,'');
		//----------------------------------------------------------------------------------------------------
		$salaire_base = 0;
		$grilleSql = "SELECT rowid FROM ".MAIN_DB_PREFIX."grille WHERE active=1 AND fk_convention=".$id_convention;
		$grilleResult = $db->query($grilleSql);//= $db->query($grilleSql);
		$obj_grille = $db->fetch_object($grilleResult);

		$salBaseSql = "SELECT salaire_base FROM ".MAIN_DB_PREFIX."grille_categorie_echelon_salaire_base WHERE fk_grille=".$obj_grille->rowid." AND fk_categorie=".$obj_salarie->fk_categorie." AND fk_echelon=".$obj_salarie->fk_echelon;
		$salBaseResult = $db->query($salBaseSql);//= $db->query($covSql);
		$objSalBase = $db->fetch_object($salBaseResult);
		$salaire_base = $objSalBase->salaire_base;
		$retrait = 0;
		$tab_info_ind = salarie_indemnite($db, $obj_salarie->rowid, 0, $id_convention, $id_societe, $id_accord_etab);
								$pourcentage_ind = $tab_info_ind[0];
								$ind = $tab_info_ind[1];
								foreach ($ind as $key => $value) {
								if(!empty($key) && !empty($value)){
									//$somme += $value;
									$sql = "SELECT * FROM ".MAIN_DB_PREFIX."indemnite WHERE rowid=".$key;
									$ind_res = $db->query($sql);
									if($ind_res){
										$ind = $db->fetch_object($ind_res);
										if($ind->exonere == "oui"){//retiré du salaire de base
											$retrait += $value;
										}

										//print "<br> Nom = ".$ind->libelle." afficher sur bulletin=".$ind->affiche_bulletin."=>".$value;
									}

								}
								}
								$anciennete_tab = prime_anciennete($db, $obj_salarie->rowid, $id_convention, $mois, $annee, $obj_user->rowid);
								$anciennete = $salaire_base*$anciennete_tab[0]/100;
								if($anciennete_tab[5] == "Oui")
								  $retrait += $anciennete;

								$salaire_base -= $retrait;

								$salSql = "SELECT jour FROM ".MAIN_DB_PREFIX."salarie_nombre_jour_travaille where annee=".$annee." AND mois=".$mois." AND fk_salarie=".$obj_salarie->rowid;
								$result = $db->query($salSql);
								$nb_jours = $db->fetch_object($result)->jour;
								$nb_total_jour = cal_days_in_month(CAL_GREGORIAN, $mois, $annee);
								$base_pourcentage = 1;
								if($nb_jours != $nb_total_jour){
									$sal_base = ($nb_jours*$salaire_base)/$nb_total_jour;
									$base_pourcentage = ($sal_base*100)/$salaire_base;
									$base_pourcentage = $base_pourcentage/100;
									$salaire_base = round($salaire_base*$base_pourcentage, 2);
								}

					$type_sal_SQL = "SELECT libelle FROM ".MAIN_DB_PREFIX."type_salarie where rowid=".$obj_salarie->type_salarie;
					$type_sal_Result = $db->query($type_sal_SQL);
					$type_salarie = "N/A";
					if($db->num_rows($type_sal_Result))
						$type_Salarie = $db->fetch_object($type_sal_Result)->libelle;

					$type_contrat_SQL = "SELECT fk_type_contrat FROM ".MAIN_DB_PREFIX."salarie_contrat where active=1 AND fk_salarie=".$obj_salarie->rowid;
					$type_contrat_Result = $db->query($type_contrat_SQL);
					$contrat = "N/A";

					if($db->num_rows($type_contrat_Result)){
						$type_contrat_SQL = "SELECT libelle FROM ".MAIN_DB_PREFIX."type_contrat where rowid=".$db->fetch_object($type_contrat_Result)->fk_type_contrat;
						$type_contrat_Result = $db->query($type_contrat_SQL);
						if($db->num_rows($type_contrat_Result))
							$contrat = ($db->fetch_object($type_contrat_Result))->libelle;
					}


		$y = $pdf->GetY()+1;
		$pdf->SetY($y);
		$pdf->MultiCell(60,4, utf8_decode("Niveau salarié : ".$type_salarie),0,'');

		$y = $pdf->GetY()+1;
		$pdf->SetY($y);
		$pdf->MultiCell(60,4, utf8_decode("Type de contrat : ".$contrat),0,'');


		$y = $pdf->GetY()+1;
		$pdf->SetY($y);
		$pdf->MultiCell(60,4, utf8_decode("Fonction : ".$obj_user->job),0,'');

		$y = $pdf->GetY()+1;
		$pdf->SetY($y);
		$pdf->MultiCell($pdf->GetX(),4, utf8_decode("Salaire de base : ".$salaire_base),0,'');

		$y = $pdf->GetY()+1;
		$pdf->SetY($y);
		$pdf->MultiCell($pdf->GetX(),4, utf8_decode("Date d'embauche : ".$obj_user->dateemployment),0,'');
		}
}


//Entête de bulletion du modèle moyen

/**entete */
function pdf_pagehead_moyen(&$pdf, $onglet_salarie){

	global $mysoc,$conf, $db, $fk_salarie, $fk_user, $mois, $annee, $id_accord_etab, $id_convention, $societe_Salarie, $info_soc;


if($onglet_salarie){

	global $id_societe, $obj_salarie, $obj_user;

	$y = $pdf->GetY();
      $debut = DOL_DOCUMENT_ROOT;
      $debut = $conf->mycompany->dir_output;
      $tab = explode("/",$debut);
      //$logo_server = $logodir.'/logos/'.$bulletin_obj->logo_societe;

	  $nom_logo = $bulletin_obj->logo_societe;
	  if(empty($societe_Salarie->logo_societe)){
		$bulletin_soc = "SELECT * FROM ".MAIN_DB_PREFIX."societe WHERE rowid=".$id_societe;
		$rest_bulletin_soc = $db->query($bulletin_soc);//= $db->query($covSql);
		$societe_Salarie = $db->fetch_object($rest_bulletin_soc);
		$nom_logo = $societe_Salarie->logo;
	  }

		   $img = '../../config/logo_societe/'.$id_societe;
		if(file_exists($img.'.png')){
			$img .= '.png';
		}elseif(file_exists($img.'.jpg')){
			$img .= '.jpg';
		}else{
			$img .= '.jpeg';
		}

		if(is_readable($img)){
			$logo_server = $img;

		}else{
			$logo_server = $tab[0].'/'.$tab[1].'/'.$tab[2].'/'.$tab[3].'/documents/societe/'.$bulletin_obj->fk_societe.'/logos/'.$nom_logo;
			$logo_local_pc = $tab[0].'/'.$tab[1].'/dolibarr_documents/societe/'.$societe_Salarie->rowid.'/logos/'.($societe_Salarie->logo?$societe_Salarie->logo:"vide.png");
		}
	  $pdf->SetFillColor(143, 39, 51);
	  $pdf->SetY(4);
	   $pdf->SetX(0);
	   $pdf->Cell($pdf->getPageWidth(),16, "",1,0,0,true);

	   //Cadre blanc pour logo
	   $pdf->SetFillColor(255, 255, 255);
      $pdf->SetY(5);
      $pdf->SetX(20);
      $pdf->Cell(35,13, "",1,0,0,true);

	  if(is_readable($logo_local_pc)){
        $pdf->Image($logo_local_pc,20,5, 35,13);
        $y = $pdf->GetY()+2;
      }elseif(is_readable($logo_server)){
		$pdf->Image($logo_server,20,5, 35,13);
        $y = $pdf->GetY()+2;
	  }else{
        $pdf->SetFont('Helvetica','B',16);
        $pdf->SetY($y-4);
        $pdf->SetX(20);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->MultiCell(25,11,utf8_decode("Logo"),0,'C', true);
        $y += 2;

      }

	  $y = 12;
		$y_salarie = $y;
		$x = 12;

		//espace pris par le logo
		$y += 12;
		//petit rectangle à gauche
		$pdf->SetFillColor(246, 246, 246);
		$pdf->SetLineWidth(0.1);
		$pdf->SetDrawColor(50, 50, 50);
		//$y += 6;
		$pdf->SetY($y);
	   $pdf->SetX($x);
	   $pdf->Cell(90,45, "",0,0,0,true);


	   //Informations dans le rectangle
	   $x = 12;
	   $y += 2;
	   $pdf->SetTextColor(0, 0, 0);
	   $pdf->SetFont('Helvetica','B',9);
		$pdf->SetY($y);
		$pdf->SetX($x);
		$pdf->MultiCell(89,5,utf8_decode("Etablissement : ".$societe_Salarie->nom),0,'L');

		$x = 12;
		$y = $pdf->getY() + 1;
		$pdf->SetTextColor(0, 0, 0);
	   $pdf->SetFont('Helvetica','',9);
		$pdf->SetY($y);
		$pdf->SetX($x);
		$pdf->MultiCell(89,1,utf8_decode($societe_Salarie->address),0,'L');

	   $convention = $obj_conv->nom;
		$pays_Sql = "SELECT label FROM ".MAIN_DB_PREFIX."c_country where rowid=".$societe_Salarie->fk_pays;
		$pays_Result = $db->query($pays_Sql);
		if(!empty($obj_user->fk_country))
			$pays = $db->fetch_object($pays_Result)->label;

   $y += 5;
	$pdf->SetTextColor(0, 0, 0);
	$pdf->SetY($y);
	$pdf->SetX($x);
	$pdf->MultiCell(89,5,utf8_decode($societe_Salarie->town." ".$pays),0,'L');

	if($societe_Salarie->siren){
		$y += 5;
		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetY($y);
		$pdf->SetX($x);
		$pdf->MultiCell(89,5,utf8_decode($societe_Salarie->siren),0,'L');
	}

	$tel = $societe_Salarie->phone;
	if(empty($tel))
		$tel = $societe_Salarie->fax;
	else $tel .= " / ".$societe_Salarie->fax;

	if($tel){
		$y += 5;
		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetY($y);
		$pdf->SetX($x);
		$pdf->Cell(89,5,utf8_decode("Tel : ".$tel),0,'L');
	}

	if($societe_Salarie->email){
		$y += 5;
		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetY($y);
		$pdf->SetX($x);
		$pdf->Cell(89,5,utf8_decode("Email : ".$societe_Salarie->email),0,'L');
	}
	if($societe_Salarie->url){
		$y += 5;
		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetY($y);
		$pdf->SetX($x);
		$pdf->Cell(89,5,utf8_decode("Web : ".$societe_Salarie->url),0,'L');
	}

	$sql_conv = "SELECT nom FROM ".MAIN_DB_PREFIX."convention WHERE rowid=".$id_convention;
	$res_conv = $db->query($sql_conv);//= $db->query($sql_conv);
	$obj_conv = $db->fetch_object($res_conv);
	$convention = $obj_conv->nom;
   $y += 8;
	$pdf->SetTextColor(0, 0, 0);
	$pdf->SetY($y);
	$pdf->SetX($x);
	$pdf->MultiCell(100,3,utf8_decode("Conv. Coll. : ".$convention),0,'L');

		//information à gauche en bas du petit rectangle
		/*$pdf->SetFont('Helvetica','B',8);
	   $y = 69;
		$au = cal_days_in_month(CAL_GREGORIAN, $mois, $annee);
		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetY($y);
		$pdf->SetX($x);
		$pdf->MultiCell(100,3,utf8_decode("Payé le : ".$au."-".$mois."-".$annee." par Virement"),0,'L');

		$type_bank = "";
		$banque = "SELECT libelle FROM ".MAIN_DB_PREFIX."type_banque WHERE rowid=".$obj_salarie->fk_type_banque;
		$result_banque = $db->query($banque);
		if($result_banque){

			$obj_type_banque = $db->fetch_object($result_banque);
			$type_bank =  $obj_type_banque->libelle;

		}

	   $y += 4;
	   if(!empty($type_bank)){
			$pdf->SetTextColor(0, 0, 0);
			$pdf->SetY($y);
			$pdf->SetX($x);
			$pdf->MultiCell(100,3,utf8_decode($type_bank." : ".$obj_salarie->compte),0,'L');
	   }*/
		//Les information sur le salarié
		$x = 100 + 3;
		$pdf->SetY($y_salarie-4);
		$pdf->SetX($x);
		$pdf->SetTextColor(255, 255, 255);
		$pdf->SetFont('Helvetica','B',18);

		$pdf->MultiCell($pdf->GetPageWidth() - 100 - 12 - 5,5,utf8_decode("Bulletin De Paie"),0,'C');

	   $pdf->SetFont('Helvetica','',8);
		$pdf->SetY($y_salarie+4);
		$pdf->SetX($x);
		$du = "01-".$mois."-".$annee;
		$au = cal_days_in_month(CAL_GREGORIAN, $mois, $annee);
		$pdf->MultiCell($pdf->GetPageWidth() - 100 - 12 - 5,3,utf8_decode("Période du :   ".$du."   au ".$au."-".$mois."-".$annee),0,'C');


		$y_salarie = $pdf->getY() +6;
		$x = 100 + 7;
		$pdf->SetY($y_salarie);
		$pdf->SetX($x);
		$pdf->SetTextColor(0, 0, 70);
		$pdf->SetFont('Helvetica','B',14);
		$pdf->MultiCell($pdf->GetPageWidth() - 100 - 12 - 5,3,utf8_decode($obj_user->firstname." ".$obj_user->lastname),0,'L');

		$x = 100 + 7;
		$y_salarie += 6;
		$pdf->SetY($y_salarie);
		$pdf->SetX($x);
		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetFont('Helvetica','B',8);
		$pdf->MultiCell($pdf->GetPageWidth() - 100 - 12 -12 - 8,3,utf8_decode($obj_user->address),0,'L');


		$pays_Sql = "SELECT label FROM ".MAIN_DB_PREFIX."c_country where rowid=".$societe_Salarie->fk_pays;
		$pays_Result = $db->query($pays_Sql);
		if(!empty($obj_user->fk_country))
			$pays = $db->fetch_object($pays_Result)->label;

		$y_salarie += 5;
		$pdf->SetY($y_salarie);
		$pdf->SetX($x);
		$pdf->SetTextColor(0, 0, 0);
		$pdf->MultiCell($pdf->GetPageWidth() - 100 - 12 -12 - 8,3,utf8_decode($obj_user->town." ".$pays),0,'L');

//ecart entre information
	//$y_salarie += 6;

		$x = 100 + 7;
		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetFont('Helvetica','',9);
		$y_salarie += 5;
	   	$pdf->SetY($y_salarie);
	   	$pdf->SetX($x);
		$pdf->Cell(35 - 5,3,utf8_decode("Matricule "),0,0,'L');
		$pdf->SetTextColor(0, 0, 70);
		$pdf->SetFont('Helvetica','',9);
		$pdf->SetX($x +45);
		$pdf->Cell($pdf->GetPageWidth() - 100 - 12 -12 - 5,3,utf8_decode($obj_salarie->matricule),0,1,'L');

		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetFont('Helvetica','',9);
		$y_salarie += 6;
	   	$pdf->SetY($y_salarie);

	   	$pdf->SetX($x);
		$pdf->Cell(35 - 5,3,utf8_decode("Catégorie "),0,0,'L');
		$grilleSql = "SELECT code_categorie FROM ".MAIN_DB_PREFIX."dcategories WHERE rowid=".$obj_salarie->fk_categorie;
		$grilleResult = $db->query($grilleSql);//= $db->query($grilleSql);
		if($grilleResult)
			$obj_grille = $db->fetch_object($grilleResult);
		$categ = $obj_grille->code_categorie;

		$grilleSql = "SELECT libelle FROM ".MAIN_DB_PREFIX."echelon WHERE rowid=".$obj_salarie->fk_echelon;
		$grilleResult = $db->query($grilleSql);//= $db->query($grilleSql);
		if($grilleResult)
			$obj_grille = $db->fetch_object($grilleResult);

		if(!empty($obj_grille->libelle))
			$categ .= '==>'.$obj_grille->libelle;

		$pdf->SetTextColor(0, 0, 70);
		$pdf->SetFont('Helvetica','',9);
		$pdf->SetX($x +45);
		$pdf->Cell($pdf->GetPageWidth() - 100 - 12 -12 - 5,3,utf8_decode($categ),0,1,'L');

		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetFont('Helvetica','',9);
		$y_salarie += 6;
	   	$pdf->SetY($y_salarie);
	   	$pdf->SetX($x);
		$pdf->Cell(35 - 5,3,utf8_decode("Fonction "),0,0,'L');
		$fonction = $obj_user->job;
		$pdf->SetTextColor(0, 0, 70);
		$pdf->SetFont('Helvetica','',9);
		$pdf->SetX($x +45);
		$pdf->Cell($pdf->GetPageWidth() - 100 - 12 -12 - 5,3,utf8_decode($fonction),0,1,'L');

		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetFont('Helvetica','',9);
		$y_salarie += 6;
	   	$pdf->SetY($y_salarie);
	   	$pdf->SetX($x);
		$pdf->Cell(35 - 5,3,utf8_decode("Date embauche "),0,0,'L');
		$date_embauche = $obj_user->dateemployment;
		$pdf->SetTextColor(0, 0, 70);
		$pdf->SetFont('Helvetica','',9);
		$pdf->SetX($x +45);
		$pdf->Cell($pdf->GetPageWidth() - 100 - 12 -12 - 5,3,utf8_decode($date_embauche),0,1,'L');


		$sql_contrat3 = "SELECT * FROM ".MAIN_DB_PREFIX."type_salarie WHERE rowid=".$obj_salarie->type_salarie;
		$res_contrat3 = $db->query($sql_contrat3);
		$contrat = "N/A";
		if($res_contrat3){

			$contrat = $db->fetch_object($restype_contrat)->libelle;
		}

		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetFont('Helvetica','',9);
		$y_salarie += 6;
	   	$pdf->SetY($y_salarie);
	   	$pdf->SetX($x);
		$pdf->Cell(35 - 5,3,utf8_decode("Niveau salarié "),0,0,'L');
		$date_embauche = $obj_user->dateemployment;
		$pdf->SetTextColor(0, 0, 70);
		$pdf->SetFont('Helvetica','',9);
		$pdf->SetX($x +45);
		$pdf->Cell($pdf->GetPageWidth() - 100 - 12 -12 - 5,3,utf8_decode($contrat),0,1,'L');


}else{
	$bulletin_sql = "SELECT * FROM ".MAIN_DB_PREFIX."bulletin WHERE fk_salarie=".$fk_salarie." AND annee='".$annee."' AND mois='".$mois."'";
	$rest_bulletin = $db->query($bulletin_sql);//= $db->query($covSql);
	$bulletin_obj = $db->fetch_object($rest_bulletin);


	$y = $pdf->GetY();
      $debut = DOL_DOCUMENT_ROOT;
      //$debut = $conf->mycompany->dir_output;
      $tab = explode("/",$debut);
      $logo_server = $logodir.'/logos/'.$bulletin_obj->logo_societe;

	  if($info_soc->societe_mere == 0){
		$logo_1 = $tab[0].'/'.$tab[1].'/'.$tab[2].'/'.$tab[3].'/documents/societe/'.$bulletin_obj->fk_societe.'/logos/'.$bulletin_obj->logo_societe;
		$logo_2 = $tab[0].'/'.$tab[1].'/dolibarr_documents/societe/'.$societe_Salarie->rowid.'/logos/'.($societe_Salarie->logo?$societe_Salarie->logo:"vide.png");

		if(is_readable($logo_2)){
			///home/dolites/public_html
			$pdf->Image($logo_2,20,12, 40,19);
		}else if(is_readable($logo_1)){
			$pdf->Image($logo_1,20,12, 40,19);

		}else{

			
			$pdf->SetFont('Helvetica','B',16);
			$pdf->SetY(12);
			$pdf->SetX(20);
			$pdf->MultiCell(40,19,utf8_decode("Logo"),0,'C');
		}

		/*$img = '../config/logo_societe/'.$bulletin_obj->fk_societe;
		if(file_exists($img.'.png')){
			$img .= '.png';
		}elseif(file_exists($img.'.jpg')){
			$img .= '.jpg';
		}else{
			$img .= '.jpeg';
		}

		if(is_readable($img)){
			$pdf->Image($logo_server,20,12, 40,19);
		}else{
			$pdf->SetFont('Helvetica','B',16);
			$pdf->SetY(12);
			$pdf->SetX(20);
			$pdf->MultiCell(40,19,utf8_decode("Logo"),0,'C');
		}*/

	}else{
			$logodir = $conf->mycompany->dir_output;
			if (!empty($conf->mycompany->multidir_output[$object->entity])) {
				$logodir = $conf->mycompany->multidir_output[$object->entity];
			}
			if (empty($conf->global->MAIN_PDF_USE_LARGE_LOGO)) {
				$logo = $logodir.'/logos/thumbs/'.$mysoc->logo_small;
			} else {
				$logo = $logodir.'/logos/'.$mysoc->logo;
			}
		
		if(is_readable($logo)){
			///home/dolites/public_html
			$pdf->Image($logo,20,12, 40,19);
		}else{

			
			$pdf->SetFont('Helvetica','B',16);
			$pdf->SetY(12);
			$pdf->SetX(20);
			$pdf->MultiCell(40,19,utf8_decode("Logo"),0,'C');
		}
	}

	  $pdf->SetFillColor(143, 39, 51);
	  $pdf->SetY(4);
	   $pdf->SetX(0);
	   $pdf->Cell($pdf->getPageWidth(),16, "",1,0,0,true);

	   //cadre blanc pour logo
	   $pdf->SetFillColor(255, 255, 255);
      $pdf->SetY(5);
      $pdf->SetX(20);
      $pdf->Cell(35,13, "",1,0,0,true);

	  if(is_readable($logo_local_pc)){
        $pdf->Image($logo_local_pc,20,5, 35,13);
        $y = $pdf->GetY()+2;
      }elseif(is_readable($logo_server)){
		$pdf->Image($logo_server,20,5, 35,13);
        $y = $pdf->GetY()+2;
	  }else{
        $pdf->SetFont('Helvetica','B',16);
        $pdf->SetY($y-4);
        $pdf->SetX(20);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->MultiCell(25,11,utf8_decode("Logo"),0,'C', true);
        $y += 2;

      }

	  $y = 12;
	$y_salarie = $y;
	$x = 12;

	//espace pris par le logo
	$y += 12;
	//petit rectangle à gauche
	$pdf->SetFillColor(246, 246, 246);
	$pdf->SetLineWidth(0.1);
	$pdf->SetDrawColor(50, 50, 50);
	$pdf->SetY($y);
   $pdf->SetX($x);
   $pdf->MultiCell(90,45, "",0,0,true);

   //Informations dans le rectangle
   $y += 2;
   $pdf->SetTextColor(0, 0, 0);
   $pdf->SetFont('Helvetica','B',9);
	$pdf->SetY($y);
	$pdf->SetX($x);
	$pdf->MultiCell(89,5,utf8_decode("Etablissement : ".$bulletin_obj->nom_societe),0,'L');

   $y += 7;
	$pdf->SetTextColor(0, 0, 0);
   $pdf->SetFont('Helvetica','',9);
	$pdf->SetY($y);
	$pdf->SetX($x);
	$pdf->MultiCell(89,5,utf8_decode($societe_Salarie->address),0,'L');
		$pays_Sql = "SELECT label FROM ".MAIN_DB_PREFIX."c_country where rowid=".$societe_Salarie->fk_pays;
		$pays_Result = $db->query($pays_Sql);
		if(!empty($obj_user->fk_country))
			$pays = $db->fetch_object($pays_Result)->label;

			$y = $pdf->getY();
			$pdf->SetTextColor(0, 0, 0);
	$pdf->SetY($y);
	$pdf->SetX($x);
	$pdf->MultiCell(89,5,utf8_decode($societe_Salarie->town." ".$pays),0,'L');

	if($societe_Salarie->siren){
		$y += 5;
		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetY($y);
		$pdf->SetX($x);
		$pdf->MultiCell(89,5,utf8_decode($societe_Salarie->siren),0,'L');
	}

	$tel = $societe_Salarie->phone;
	if(empty($tel))
		$tel = $societe_Salarie->fax;
	else $tel .= " / ".$societe_Salarie->fax;

	if($tel){
		$y += 5;
		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetY($y);
		$pdf->SetX($x);
		$pdf->Cell(89,5,utf8_decode("Tel : ".$tel),0,'L');
	}

	if($societe_Salarie->email){
		$y += 5;
		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetY($y);
		$pdf->SetX($x);
		$pdf->Cell(89,5,utf8_decode("Email : ".$societe_Salarie->email),0,'L');
	}
	if($societe_Salarie->url){
		$y += 5;
		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetY($y);
		$pdf->SetX($x);
		$pdf->Cell(89,5,utf8_decode("Web : ".$societe_Salarie->url),0,'L');
	}


   $convention = $bulletin_obj->nom_convention;
   $y += 5;
	$pdf->SetTextColor(0, 0, 0);
	$pdf->SetY($y);
	$pdf->SetX($x);
	$pdf->MultiCell(100,3,utf8_decode("Conv. Coll. : ".$convention),0,'L');

	//information à gauche en bas du petit rectangle
   $y = 69;
   /*$pdf->SetFont('Helvetica','B',8);

	$pdf->SetTextColor(0, 0, 0);
	$pdf->SetY($y);
	$pdf->SetX($x);
	$au = cal_days_in_month(CAL_GREGORIAN, $mois, $annee);
	$pdf->MultiCell(100,3,utf8_decode("Payé le : ".$au."-".$mois."-".$annee." par Virement"),0,'L');

   $y += 4;
   if(!empty($bulletin_obj->banque)){
	$pdf->SetTextColor(0, 0, 0);
	$pdf->SetY($y);
	$pdf->SetX($x);
	$pdf->MultiCell(100,3,utf8_decode($bulletin_obj->banque." : ".$bulletin_obj->compte),0,'L');
   }*/
	//Les information sur le salarié
   //$y += 4;
	$x = 100 + 3;
	$pdf->SetY($y_salarie-4);
	$pdf->SetX($x);
	$pdf->SetTextColor(255, 255, 255);
	$pdf->SetFont('Helvetica','B',18);

	$pdf->MultiCell($pdf->GetPageWidth() - 100 - 12-5,5,utf8_decode("Bulletin De Paie"),0,'C');


	$pdf->SetTextColor(255, 255, 255);
	$pdf->SetY($y_salarie+4);
	$pdf->SetX($x);
	$du = "01-".$mois."-".$annee;
	$au = cal_days_in_month(CAL_GREGORIAN, $mois, $annee);
	$pdf->SetFont('Helvetica','',8);
	$pdf->MultiCell($pdf->GetPageWidth() - 100 - 12-5,3,utf8_decode("Période du :   ".$du."   au ".$au."-".$mois."-".$annee),0,'C');


	$x = 100 + 7;
	$y_salarie = $pdf->getY() + 6;
	$pdf->SetY($y_salarie);
	$pdf->SetX($x);
	$pdf->SetTextColor(0, 0, 70);
	$pdf->SetFont('Helvetica','B',14);
	$pdf->MultiCell($pdf->GetPageWidth() - 100 - 12 -12 - 5,3,utf8_decode($bulletin_obj->prenom." ".$bulletin_obj->nom),0,'L');

	$x = 100 + 7;
	$y_salarie += 6;
	$pdf->SetY($y_salarie);
	$pdf->SetX($x);
	$pdf->SetTextColor(0, 0, 0);
	$pdf->SetFont('Helvetica','B',8);
	$pdf->MultiCell($pdf->GetPageWidth() - 100 - 12 -12 - 8,3,utf8_decode($bulletin_obj->addresse),0,'L');


	$y_salarie += 5;
	$pdf->SetY($y_salarie);
	$pdf->SetX($x);
	$pdf->SetTextColor(0, 0, 0);
	$pdf->MultiCell($pdf->GetPageWidth() - 100 - 12 -12 - 8,3,utf8_decode($bulletin_obj->ville." ".$bulletin_obj->pays),0,'L');

//ecart entre information
//$y_salarie += 6;


$x = 100 + 7;
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('Helvetica','',9);
$y_salarie += 5;
   $pdf->SetY($y_salarie);
   $pdf->SetX($x);
$pdf->Cell(35 - 5,3,utf8_decode("Matricule "),0,0,'L');
$pdf->SetTextColor(0, 0, 70);
$pdf->SetFont('Helvetica','',9);
$pdf->SetX($x + 45);
$pdf->Cell($pdf->GetPageWidth() - 100 - 12 -12 - 5,3,utf8_decode($bulletin_obj->matricule),0,1,'L');

$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('Helvetica','',9);
$y_salarie += 6;
$pdf->SetY($y_salarie);
$pdf->SetX($x);
$pdf->Cell(35 - 5,3,utf8_decode("Catégorie "),0,0,'L');
$categ = $bulletin_obj->categorie."".($bulletin_obj->echelon?"==>".$bulletin_obj->echelon:"");

$pdf->SetTextColor(0, 0, 70);
$pdf->SetFont('Helvetica','',9);
$pdf->SetX($x + 45);
$pdf->Cell($pdf->GetPageWidth() - 100 - 12 -12 - 5,3,utf8_decode($categ),0,1,'L');
	$pdf->SetTextColor(0, 0, 0);
	$pdf->SetFont('Helvetica','',9);
	$y_salarie += 6;
	   $pdf->SetY($y_salarie);
	   $pdf->SetX($x);
	$pdf->Cell(35 - 5,3,utf8_decode("Fonction "),0,0,'L');
	$fonction = $obj_user->job;
	$pdf->SetTextColor(0, 0, 70);
	$pdf->SetFont('Helvetica','',9);
	$pdf->SetX($x + 45);
	$pdf->Cell($pdf->GetPageWidth() - 100 - 12 -12 - 5,3,utf8_decode($bulletin_obj->fonction),0,1,'L');

	$pdf->SetTextColor(0, 0, 0);
	$pdf->SetFont('Helvetica','',9);
	$y_salarie += 6;
	   $pdf->SetY($y_salarie);
	   $pdf->SetX($x);
	$pdf->Cell(35 - 5,3,utf8_decode("Date embauche "),0,0,'L');
	$date_embauche = $obj_user->dateemployment;
	$pdf->SetTextColor(0, 0, 70);
	$pdf->SetFont('Helvetica','',9);
	$pdf->SetX($x + 45);
	$pdf->Cell($pdf->GetPageWidth() - 100 - 12 -12 - 5,3,utf8_decode($bulletin_obj->date_embauche),0,1,'L');

	/*$pdf->SetTextColor(0, 0, 0);
	$pdf->SetFont('Helvetica','',9);
	$y_salarie += 6;
	   $pdf->SetY($y_salarie);
	   $pdf->SetX($x);
	$pdf->Cell(35 - 5,3,utf8_decode("Contrat "),0,0,'L');
	$date_embauche = $obj_user->dateemployment;
	$pdf->SetTextColor(0, 0, 70);
	$pdf->SetFont('Helvetica','',9);
	$pdf->SetX($x + 45);
	$pdf->Cell($pdf->GetPageWidth() - 100 - 12 -12 - 5,3,utf8_decode($bulletin_obj->contrat),0,1,'L');
	*/
	$pdf->SetTextColor(0, 0, 0);
	$pdf->SetFont('Helvetica','',9);
	$y_salarie += 6;
	   $pdf->SetY($y_salarie);
	   $pdf->SetX($x);
	$pdf->Cell(35 - 5,3,utf8_decode("Niveau salarié "),0,0,'L');
	$date_embauche = $obj_user->dateemployment;
	$pdf->SetTextColor(0, 0, 70);
	$pdf->SetFont('Helvetica','',9);
	$pdf->SetX($x + 45);
	$pdf->Cell($pdf->GetPageWidth() - 100 - 12 -12 - 5,3,utf8_decode($bulletin_obj->type_salarie),0,1,'L');

}
}


//Entête de bulletin du modele avance

function pdf_pagehead_avance(&$pdf, $onglet_salarie){

	global $mysoc,$conf, $db, $fk_salarie, $fk_user, $mois, $annee, $id_accord_etab, $id_convention, $societe_Salarie;


if($onglet_salarie){

	global $id_societe, $obj_salarie, $obj_user;

	$y = $pdf->GetY();
      //$debut = DOL_DOCUMENT_ROOT;
      $debut = $conf->mycompany->dir_output;
      $tab = explode("/",$debut);
      //$logo_server = $logodir.'/logos/'.$bulletin_obj->logo_societe;

		   $img = '../../config/logo_societe/'.$societe_Salarie->rowid;
		if(file_exists($img.'.png')){
			$img .= '.png';
		}elseif(file_exists($img.'.jpg')){
			$img .= '.jpg';
		}else{
			$img .= '.jpeg';
		}

		if(is_readable($img)){
			$logo_server = $img;
		}else{
			$logo_server = $tab[0].'/'.$tab[1].'/'.$tab[2].'/'.$tab[3].'/documents/societe/'.$societe_Salarie->rowid.'/logos/'.$societe_Salarie->logo;
			$logo_local_pc = $tab[0].'/'.$tab[1].'/dolibarr_documents/societe/'.$societe_Salarie->rowid.'/logos/'.($societe_Salarie->logo?$societe_Salarie->logo:"vide.png");
		}
	  if(is_readable($logo_local_pc)){
        $pdf->Image($logo_local_pc,30,3, 40,19);
        $y = $pdf->GetY()+2;
      }elseif(is_readable($logo_server)){
		$pdf->Image($logo_server,30,3, 40,19);
        $y = $pdf->GetY()+2;
	  }else{
        $pdf->SetFont('Helvetica','B',16);
        $pdf->SetY($y);
        $pdf->SetX(30);
        $pdf->MultiCell(40,6,utf8_decode("Logo"),1,'C');
        $y += 2;

      }

		$y_salarie = $y;
		$x = 12;

		//espace pris par le logo
		$y += 12;
		//petit rectangle à gauche
		$pdf->SetFillColor(246, 246, 246);
		$pdf->SetLineWidth(0.1);
		$pdf->SetDrawColor(50, 50, 50);
		//$y += 6;
		$pdf->SetY($y);
	   $pdf->SetX($x);
	   $pdf->Cell(90,42, "",0,0,0,true);

	   //Informations dans le rectangle
	   $x = 12;
	   $y += 2;
	   $pdf->SetTextColor(0, 0, 0);
	   $pdf->SetFont('Helvetica','B',9);
		$pdf->SetY($y);
		$pdf->SetX($x);
		$pdf->MultiCell(100,5,utf8_decode("Etablissement : ".$societe_Salarie->nom),0,'L');

		$x = 12;
	   $y += 7;
		$pdf->SetTextColor(0, 0, 0);
	   $pdf->SetFont('Helvetica','',9);
		$pdf->SetY($y);
		$pdf->SetX($x);
		$pdf->MultiCell(100,5,utf8_decode($societe_Salarie->address),0,'L');

	   $convention = $obj_conv->nom;
		$pays_Sql = "SELECT label FROM ".MAIN_DB_PREFIX."c_country where rowid=".$societe_Salarie->fk_pays;
		$pays_Result = $db->query($pays_Sql);
		if(!empty($obj_user->fk_country))
			$pays = $db->fetch_object($pays_Result)->label;

   $y += 5;
	$pdf->SetTextColor(0, 0, 0);
	$pdf->SetY($y);
	$pdf->SetX($x);
	$pdf->MultiCell(89,5,utf8_decode($societe_Salarie->town." ".$pays),0,'L');

	if($societe_Salarie->siren){
		$y += 5;
		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetY($y);
		$pdf->SetX($x);
		$pdf->MultiCell(89,5,utf8_decode($societe_Salarie->siren),0,'L');
	}

	$tel = $societe_Salarie->phone;
	if(empty($tel))
		$tel = $societe_Salarie->fax;
	else $tel .= " / ".$societe_Salarie->fax;

	if($tel){
		$y += 5;
		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetY($y);
		$pdf->SetX($x);
		$pdf->Cell(89,5,utf8_decode("Tel : ".$tel),0,'L');
	}

	if($societe_Salarie->email){
		$y += 5;
		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetY($y);
		$pdf->SetX($x);
		$pdf->Cell(89,5,utf8_decode("Email : ".$societe_Salarie->email),0,'L');
	}
	if($societe_Salarie->url){
		$y += 5;
		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetY($y);
		$pdf->SetX($x);
		$pdf->Cell(89,5,utf8_decode("Web : ".$societe_Salarie->url),0,'L');
	}

	$sql_conv = "SELECT nom FROM ".MAIN_DB_PREFIX."convention WHERE rowid=".$id_convention;
	$res_conv = $db->query($sql_conv);//= $db->query($sql_conv);
	$obj_conv = $db->fetch_object($res_conv);
	$convention = $obj_conv->nom;
   $y += 8;
	$pdf->SetTextColor(0, 0, 0);
	$pdf->SetY($y);
	$pdf->SetX($x);
	$pdf->MultiCell(100,3,utf8_decode("Conv. Coll. : ".$convention),0,'L');

		//information à gauche en bas du petit rectangle
	   $y = 69;
	   $pdf->SetFont('Helvetica','B',9);
		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetY($y);
		$pdf->SetX($x);
		$du = "01-".$mois."-".$annee;
		$au = cal_days_in_month(CAL_GREGORIAN, $mois, $annee);
		$pdf->MultiCell(100,3,utf8_decode("Période du :   ".$du."   au ".$au."-".$mois."-".$annee),0,'L');


	   $y += 4;
		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetY($y);
		$pdf->SetX($x);
		$pdf->MultiCell(100,3,utf8_decode("Payé le : ".$au."-".$mois."-".$annee." par Virement"),0,'L');

		$type_bank = "";
		$banque = "SELECT libelle FROM ".MAIN_DB_PREFIX."type_banque WHERE rowid=".$obj_salarie->fk_type_banque;
		$result_banque = $db->query($banque);
		if($result_banque){

			$obj_type_banque = $db->fetch_object($result_banque);
			$type_bank =  $obj_type_banque->libelle;

		}

	   $y += 4;
	   if(!empty($type_bank)){
			$pdf->SetTextColor(0, 0, 0);
			$pdf->SetY($y);
			$pdf->SetX($x);
			$pdf->MultiCell(100,3,utf8_decode($type_bank." : ".$obj_salarie->compte),0,'L');
	   }
		//Les information sur le salarié
		$x = 100 + 3;
		$pdf->SetY($y_salarie-4);
		$pdf->SetX($x);
		$pdf->SetTextColor(0, 0, 70);
		$pdf->SetFont('Helvetica','B',18);

		$pdf->MultiCell($pdf->GetPageWidth() - 100 - 12 - 5,5,utf8_decode("Bulletin De Paie"),0,'C');

		$y_salarie = $pdf->getY() + 12;
		$x = 100 + 7;
		$pdf->SetY($y_salarie);
		$pdf->SetX($x);
		$pdf->SetTextColor(0, 0, 70);
		$pdf->SetFont('Helvetica','B',14);
		$pdf->MultiCell($pdf->GetPageWidth() - 100 - 12 - 5,3,utf8_decode($obj_user->firstname." ".$obj_user->lastname),0,'L');

		$x = 100 + 7;
		$y_salarie += 6;
		$pdf->SetY($y_salarie);
		$pdf->SetX($x);
		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetFont('Helvetica','B',9);
		$pdf->MultiCell($pdf->GetPageWidth() - 100 - 12 -12 - 8,3,utf8_decode($obj_user->address),0,'L');


		$pays_Sql = "SELECT label FROM ".MAIN_DB_PREFIX."c_country where rowid=".$societe_Salarie->fk_pays;
		$pays_Result = $db->query($pays_Sql);
		if(!empty($obj_user->fk_country))
			$pays = $db->fetch_object($pays_Result)->label;

		$y_salarie += 5;
		$pdf->SetY($y_salarie);
		$pdf->SetX($x);
		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetFont('Helvetica','B',9);
		$pdf->MultiCell($pdf->GetPageWidth() - 100 - 12 -12 - 8,3,utf8_decode($obj_user->town." ".$pays),0,'L');

//ecart entre information
	$y_salarie += 6;

		$x = 100 + 7;
		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetFont('Helvetica','',10);
		$y_salarie += 5;
	   	$pdf->SetY($y_salarie);
	   	$pdf->SetX($x);
		$pdf->Cell(35 - 5,3,utf8_decode("Matricule "),0,0,'L');
		$pdf->SetTextColor(0, 0, 70);
		$pdf->SetFont('Helvetica','B',10);
		$pdf->SetX($x +45);
		$pdf->Cell($pdf->GetPageWidth() - 100 - 12 -12 - 5,3,utf8_decode($obj_salarie->matricule),0,1,'L');

		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetFont('Helvetica','',10);
		$y_salarie += 6;
	   	$pdf->SetY($y_salarie);

	   	$pdf->SetX($x);
		$pdf->Cell(35 - 5,3,utf8_decode("Catégorie "),0,0,'L');
		$grilleSql = "SELECT code_categorie FROM ".MAIN_DB_PREFIX."dcategories WHERE rowid=".$obj_salarie->fk_categorie;
		$grilleResult = $db->query($grilleSql);//= $db->query($grilleSql);
		if($grilleResult)
			$obj_grille = $db->fetch_object($grilleResult);
		$categ = $obj_grille->code_categorie;

		$grilleSql = "SELECT libelle FROM ".MAIN_DB_PREFIX."echelon WHERE rowid=".$obj_salarie->fk_echelon;
		$grilleResult = $db->query($grilleSql);//= $db->query($grilleSql);
		if($grilleResult)
			$obj_grille = $db->fetch_object($grilleResult);

		if(!empty($obj_grille->libelle))
			$categ .= '==>'.$obj_grille->libelle;

		$pdf->SetTextColor(0, 0, 70);
		$pdf->SetFont('Helvetica','B',10);
		$pdf->SetX($x +45);
		$pdf->Cell($pdf->GetPageWidth() - 100 - 12 -12 - 5,3,utf8_decode($categ),0,1,'L');

		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetFont('Helvetica','',10);
		$y_salarie += 6;
	   	$pdf->SetY($y_salarie);
	   	$pdf->SetX($x);
		$pdf->Cell(35 - 5,3,utf8_decode("Fonction "),0,0,'L');
		$fonction = $obj_user->job;
		$pdf->SetTextColor(0, 0, 70);
		$pdf->SetFont('Helvetica','B',10);
		$pdf->SetX($x +45);
		$pdf->Cell($pdf->GetPageWidth() - 100 - 12 -12 - 5,3,utf8_decode($fonction),0,1,'L');

		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetFont('Helvetica','',10);
		$y_salarie += 6;
	   	$pdf->SetY($y_salarie);
	   	$pdf->SetX($x);
		$pdf->Cell(35 - 5,3,utf8_decode("Date embauche "),0,0,'L');
		$date_embauche = $obj_user->dateemployment;
		$pdf->SetTextColor(0, 0, 70);
		$pdf->SetFont('Helvetica','B',10);
		$pdf->SetX($x +45);
		$pdf->Cell($pdf->GetPageWidth() - 100 - 12 -12 - 5,3,utf8_decode($date_embauche),0,1,'L');


		$sql_contrat3 = "SELECT * FROM ".MAIN_DB_PREFIX."type_salarie WHERE rowid=".$obj_salarie->type_salarie;
		$res_contrat3 = $db->query($sql_contrat3);
		$contrat = "N/A";
		if($res_contrat3){

			$contrat = $db->fetch_object($restype_contrat)->libelle;
		}

		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetFont('Helvetica','',10);
		$y_salarie += 6;
	   	$pdf->SetY($y_salarie);
	   	$pdf->SetX($x);
		$pdf->Cell(35 - 5,3,utf8_decode("Niveau salarié "),0,0,'L');
		$date_embauche = $obj_user->dateemployment;
		$pdf->SetTextColor(0, 0, 70);
		$pdf->SetFont('Helvetica','B',10);
		$pdf->SetX($x +45);
		$pdf->Cell($pdf->GetPageWidth() - 100 - 12 -12 - 5,3,utf8_decode($contrat),0,1,'L');


}else{
	$bulletin_sql = "SELECT * FROM ".MAIN_DB_PREFIX."bulletin WHERE fk_salarie=".$fk_salarie." AND annee='".$annee."' AND mois='".$mois."'";
	$rest_bulletin = $db->query($bulletin_sql);//= $db->query($covSql);
	$bulletin_obj = $db->fetch_object($rest_bulletin);


	$y = $pdf->GetY();
      $debut = DOL_DOCUMENT_ROOT;
      //$debut = $conf->mycompany->dir_output;
      $tab = explode("/",$debut);
      $logo_server = $logodir.'/logos/'.$bulletin_obj->logo_societe;

		   $img = '../../config/logo_societe/'.$bulletin_obj->fk_societe;
		if(file_exists($img.'.png')){
			$img .= '.png';
		}elseif(file_exists($img.'.jpg')){
			$img .= '.jpg';
		}else{
			$img .= '.jpeg';
		}

		if(is_readable($img)){
			$logo_server = $img;

		}else{
			$logo_server = $tab[0].'/'.$tab[1].'/'.$tab[2].'/'.$tab[3].'/documents/societe/'.$societe_Salarie->rowid.'/logos/'.$rest_bulletin->logo_societe;
			$logo_local_pc = $tab[0].'/'.$tab[1].'/dolibarr_documents/societe/'.$societe_Salarie->rowid.'/logos/'.($societe_Salarie->logo?$societe_Salarie->logo:"vide.png");
		}

	  if(is_readable($logo_local_pc)){
        $pdf->Image($logo_local_pc,30,3, 40,19);
        $y = $pdf->GetY()+2;
      }elseif(is_readable($logo_server)){
		$pdf->Image($logo_server,30,3, 40,19);
        $y = $pdf->GetY()+2;
	  }else{
        $pdf->SetFont('Helvetica','B',16);
        $pdf->SetY($y);
        $pdf->SetX(30);
        $pdf->MultiCell(40,6,utf8_decode("Logo"),1,'C');
        $y += 2;

      }
	$y_salarie = $y;
	$x = 12;

	//espace pris par le logo
	$y += 12;
	//petit rectangle à gauche
	$pdf->SetFillColor(246, 246, 246);
	$pdf->SetLineWidth(0.1);
	$pdf->SetDrawColor(50, 50, 50);
	$pdf->SetY($y);
   $pdf->SetX($x);
   $pdf->MultiCell(90,42, "",0,0,true);

   //Informations dans le rectangle
   $y += 2;
   $pdf->SetTextColor(0, 0, 0);
   $pdf->SetFont('Helvetica','B',9);
	$pdf->SetY($y);
	$pdf->SetX($x);
	$pdf->MultiCell(100,5,utf8_decode("Etablissement : ".$bulletin_obj->nom_societe),0,'L');

   $y += 7;
	$pdf->SetTextColor(0, 0, 0);
   $pdf->SetFont('Helvetica','',9);
	$pdf->SetY($y);
	$pdf->SetX($x);
	$pdf->MultiCell(100,5,utf8_decode($societe_Salarie->address),0,'L');
		$pays_Sql = "SELECT label FROM ".MAIN_DB_PREFIX."c_country where rowid=".$societe_Salarie->fk_pays;
		$pays_Result = $db->query($pays_Sql);
		if(!empty($obj_user->fk_country))
			$pays = $db->fetch_object($pays_Result)->label;

   $y += 5;
	$pdf->SetTextColor(0, 0, 0);
	$pdf->SetY($y);
	$pdf->SetX($x);
	$pdf->MultiCell(89,5,utf8_decode($societe_Salarie->town." ".$pays),0,'L');

	if($societe_Salarie->siren){
		$y += 5;
		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetY($y);
		$pdf->SetX($x);
		$pdf->MultiCell(89,5,utf8_decode($societe_Salarie->siren),0,'L');
	}

	$tel = $societe_Salarie->phone;
	if(empty($tel))
		$tel = $societe_Salarie->fax;
	else $tel .= " / ".$societe_Salarie->fax;

	if($tel){
		$y += 5;
		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetY($y);
		$pdf->SetX($x);
		$pdf->Cell(89,5,utf8_decode("Tel : ".$tel),0,'L');
	}

	if($societe_Salarie->email){
		$y += 5;
		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetY($y);
		$pdf->SetX($x);
		$pdf->Cell(89,5,utf8_decode("Email : ".$societe_Salarie->email),0,'L');
	}
	if($societe_Salarie->url){
		$y += 5;
		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetY($y);
		$pdf->SetX($x);
		$pdf->Cell(89,5,utf8_decode("Web : ".$societe_Salarie->url),0,'L');
	}


   $convention = $bulletin_obj->nom_convention;
   $y += 8;
	$pdf->SetTextColor(0, 0, 0);
	$pdf->SetY($y);
	$pdf->SetX($x);
	$pdf->MultiCell(100,3,utf8_decode("Conv. Coll. : ".$convention),0,'L');

	//information à gauche en bas du petit rectangle
   $y = 67;
   $pdf->SetFont('Helvetica','B',9);
	$pdf->SetTextColor(0, 0, 0);
	$pdf->SetY($y);
	$pdf->SetX($x);
	$du = "01-".$mois."-".$annee;
	$au = cal_days_in_month(CAL_GREGORIAN, $mois, $annee);
	$pdf->MultiCell(100,3,utf8_decode("Période du :   ".$du."   au ".$au."-".$mois."-".$annee),0,'L');


   $y += 4;
	$pdf->SetTextColor(0, 0, 0);
	$pdf->SetY($y);
	$pdf->SetX($x);
	$pdf->MultiCell(100,3,utf8_decode("Payé le : ".$au."-".$mois."-".$annee." par Virement"),0,'L');

   $y += 4;
   if(!empty($bulletin_obj->banque)){
	$pdf->SetTextColor(0, 0, 0);
	$pdf->SetY($y);
	$pdf->SetX($x);
	$pdf->MultiCell(100,3,utf8_decode($bulletin_obj->banque." : ".$bulletin_obj->compte),0,'L');
   }
	//Les information sur le salarié
	$x = 100 + 3;
	$pdf->SetY($y_salarie-4);
	$pdf->SetX($x);
	$pdf->SetTextColor(0, 0, 70);
	$pdf->SetFont('Helvetica','B',18);

	$pdf->MultiCell($pdf->GetPageWidth() - 100 - 12-5,5,utf8_decode("Bulletin De Paie"),0,'C');

	$x = 100 + 7;
	$y_salarie = $pdf->getY() + 12;
	$pdf->SetY($y_salarie);
	$pdf->SetX($x);
	$pdf->SetTextColor(0, 0, 70);
	$pdf->SetFont('Helvetica','B',14);
	$pdf->MultiCell($pdf->GetPageWidth() - 100 - 12 -12 - 5,3,utf8_decode($bulletin_obj->prenom." ".$bulletin_obj->nom),0,'L');

	$x = 100 + 7;
	$y_salarie += 6;
	$pdf->SetY($y_salarie);
	$pdf->SetX($x);
	$pdf->SetTextColor(0, 0, 0);
	$pdf->SetFont('Helvetica','B',9);
	$pdf->MultiCell($pdf->GetPageWidth() - 100 - 12 -12 - 8,3,utf8_decode($bulletin_obj->addresse),0,'L');


	$y_salarie += 5;
	$pdf->SetY($y_salarie);
	$pdf->SetX($x);
	$pdf->SetTextColor(0, 0, 0);
	$pdf->SetFont('Helvetica','B',9);
	$pdf->MultiCell($pdf->GetPageWidth() - 100 - 12 -12 - 8,3,utf8_decode($bulletin_obj->ville." ".$bulletin_obj->pays),0,'L');

//ecart entre information
$y_salarie += 6;


$x = 100 + 7;
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('Helvetica','',10);
$y_salarie += 5;
   $pdf->SetY($y_salarie);
   $pdf->SetX($x);
$pdf->Cell(35 - 5,3,utf8_decode("Matricule "),0,0,'L');
$pdf->SetTextColor(0, 0, 70);
$pdf->SetFont('Helvetica','B',10);
$pdf->SetX($x + 45);
$pdf->Cell($pdf->GetPageWidth() - 100 - 12 -12 - 5,3,utf8_decode($bulletin_obj->matricule),0,1,'L');

$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('Helvetica','',10);
$y_salarie += 6;
$pdf->SetY($y_salarie);
$pdf->SetX($x);
$pdf->Cell(35 - 5,3,utf8_decode("Catégorie "),0,0,'L');
$categ = $bulletin_obj->categorie."".($bulletin_obj->echelon?"==>".$bulletin_obj->echelon:"");

$pdf->SetTextColor(0, 0, 70);
$pdf->SetFont('Helvetica','B',10);
$pdf->SetX($x + 45);
$pdf->Cell($pdf->GetPageWidth() - 100 - 12 -12 - 5,3,utf8_decode($categ),0,1,'L');
	$pdf->SetTextColor(0, 0, 0);
	$pdf->SetFont('Helvetica','',10);
	$y_salarie += 6;
	   $pdf->SetY($y_salarie);
	   $pdf->SetX($x);
	$pdf->Cell(35 - 5,3,utf8_decode("Fonction "),0,0,'L');
	$fonction = $obj_user->job;
	$pdf->SetTextColor(0, 0, 70);
	$pdf->SetFont('Helvetica','B',10);
	$pdf->SetX($x + 45);
	$pdf->Cell($pdf->GetPageWidth() - 100 - 12 -12 - 5,3,utf8_decode($bulletin_obj->fonction),0,1,'L');

	$pdf->SetTextColor(0, 0, 0);
	$pdf->SetFont('Helvetica','',10);
	$y_salarie += 6;
	   $pdf->SetY($y_salarie);
	   $pdf->SetX($x);
	$pdf->Cell(35 - 5,3,utf8_decode("Date embauche "),0,0,'L');
	$date_embauche = $obj_user->dateemployment;
	$pdf->SetTextColor(0, 0, 70);
	$pdf->SetFont('Helvetica','B',10);
	$pdf->SetX($x + 45);
	$pdf->Cell($pdf->GetPageWidth() - 100 - 12 -12 - 5,3,utf8_decode($bulletin_obj->date_embauche),0,1,'L');

	/*$pdf->SetTextColor(0, 0, 0);
	$pdf->SetFont('Helvetica','',10);
	$y_salarie += 6;
	   $pdf->SetY($y_salarie);
	   $pdf->SetX($x);
	$pdf->Cell(35 - 5,3,utf8_decode("Contrat "),0,0,'L');
	$date_embauche = $obj_user->dateemployment;
	$pdf->SetTextColor(0, 0, 70);
	$pdf->SetFont('Helvetica','B',10);
	$pdf->SetX($x + 45);
	$pdf->Cell($pdf->GetPageWidth() - 100 - 12 -12 - 5,3,utf8_decode($bulletin_obj->contrat),0,1,'L');
	*/
	$pdf->SetTextColor(0, 0, 0);
	$pdf->SetFont('Helvetica','',10);
	$y_salarie += 6;
	   $pdf->SetY($y_salarie);
	   $pdf->SetX($x);
	$pdf->Cell(35 - 5,3,utf8_decode("Niveau salarié "),0,0,'L');
	$date_embauche = $obj_user->dateemployment;
	$pdf->SetTextColor(0, 0, 70);
	$pdf->SetFont('Helvetica','B',10);
	$pdf->SetX($x + 45);
	$pdf->Cell($pdf->GetPageWidth() - 100 - 12 -12 - 5,3,utf8_decode($bulletin_obj->type_salarie),0,1,'L');

}
}


function pdf_ibspagefoot(&$pdf, $marge_droite, $marge_basse)
{
	global $db, $conf, $user, $mysoc, $hookmanager, $id_societe, $info_soc;
	//$formcompany = new FormCompany($db);

	$line = '';
	$reg = array();


	$line1 = "";
	$lineTel = "";
	$lineEmail = "";
	$lineAdress = "";
	$line3 = "";
	$line4 = "";
	$identProf1 = "";
	$identProf2 = "";
	$identProf3 = "";
	$identProf4 = "";
	//!empty($conf->global->MAIN_INFO_SOCIETE_NOM) ? $conf->global->MAIN_INFO_SOCIETE_NOM : ''.'"'.(empty($conf->global->MAIN_INFO_SOCIETE_NOM)

	$lineAdress = !empty($mysoc->address) ? $mysoc->address : '';
	//$lineAdress .= ($lineAdress? "\n":"").!empty($mysoc->zip) ? $mysoc->zip : '';
	//$lineAdress .= ($lineAdress? "\n":"").!empty($mysoc->town) ? $mysoc->town : '';

	$lineTel .= ($lineTel ? "\n":"").(!empty($mysoc->phone) ? "Tel :".$mysoc->phone : '');
	$lineTel .= ($lineTel ? "\n":"").(!empty($mysoc->fax) ? "Fax :".$mysoc->fax : '');
	$lineEmail.= ($lineEmail? "\n":"").(!empty($mysoc->email) ? $mysoc->email : '');
	$lineEmail.= ($lineEmail? "\n":"").(!empty($mysoc->url) ? $mysoc->url : '');

	// ProfId1
	   $identProf1 .= dol_escape_htmltag(!empty($mysoc->idprof1) ? $mysoc->idprof1 : '');


	// ProfId2
		$identProf2 .= dol_escape_htmltag(!empty($mysoc->idprof2) ? $mysoc->idprof2 : '');

	// ProfId3
		$identProf3 .= dol_escape_htmltag(!empty($mysoc->idprof3) ? $mysoc->idprof3 : '');

		$identProf4 = ($identProf1?$identProf1."\n":"").($identProf2?$identProf2."\n":"").($identProf3?$identProf3."\n":"");
//(!empty($conf->global->MAIN_INFO_SOCIETE_GENCODE) ? $conf->global->MAIN_INFO_SOCIETE_GENCODE : '')))
		$id_bull = 1;
			$modele_bulletin = "SELECT rowid FROM ".MAIN_DB_PREFIX."modele_bulletin WHERE actif=1";
			$result_modele_bulletin = $db->query($modele_bulletin);//= $db->query($covSql);
			if($result_modele_bulletin){
				$obj_modele_bulletin = $db->fetch_object($result_modele_bulletin);
				$id_bull = $obj_modele_bulletin->rowid;
			}
			if($id_bull == 2){
				$pdf->SetFillColor(143, 39, 51);
				$pdf->SetLeftMargin(0);
				$pdf->SetY($pdf->GetY());
				$pdf->MultiCell($pdf->getPageWidth(), 13, "",0,'',true);
				$pdf->SetTextColor(255, 255, 255);

			}/*elseif($id_bull == 3){
				$pdf->SetFillColor(145, 124, 35);
				$pdf->SetLeftMargin(0);
				$pdf->SetY($pdf->GetY());
				$pdf->MultiCell($pdf->getPageWidth(), 13, "",0,'',true);
				$pdf->SetTextColor(255, 255, 255);
			}*/

			$pdf->SetFont('Helvetica','',7);

			$pdf->SetY(-$marge_basse);
			$y = $pdf->GetY()+1;
			$pdf->line(12,$pdf->GetY(),$pdf->GetPageWidth()-12,$pdf->GetY());

			

			if($info_soc->societe_mere == 0){
				$societe_Sql = "SELECT * FROM ".MAIN_DB_PREFIX."societe where rowid=".$id_societe;
				$societe_Result = $db->query($societe_Sql);
				$societe_obj = $db->fetch_object($societe_Result);
				$lineTel = $societe_obj->phone?$societe_obj->phone."\n":"";
				$lineTel .= $societe_obj->fax?$societe_obj->fax."\n":"";
				$lineEmail= $societe_obj->email?$societe_obj->email."\n":"";
				$lineEmail .= $societe_obj->url;
				$lineAdress = !empty($societe_obj->address) ? $societe_obj->address : '';
				$identProf4 = ($societe_obj->identProf1?$societe_obj->identProf1."\n":"").($societe_obj->identProf2?$societe_obj->identProf2:"");
			}

		if($id_bull == 2){
		  $pdf->Image(DOL_DOCUMENT_ROOT.'/paiementsalaire/doc/icone_folder/phone.png',13,$y+1, 6,6);
		  $pdf->SetLeftMargin($marge_droite+6);
		  $pdf->SetY($y);
		  $pdf->MultiCell(38,4, utf8_decode($lineTel),0,'L');

		  $pdf->Image(DOL_DOCUMENT_ROOT.'/paiementsalaire/doc/icone_folder/envelope.png',58,$y+1, 6,6);
		  $pdf->SetLeftMargin(64);
		  $pdf->SetY($y);
		  $pdf->MultiCell(38,4, utf8_decode($lineEmail),0,'L');

		  $pdf->Image(DOL_DOCUMENT_ROOT.'/paiementsalaire/doc/icone_folder/locationMap.png',103,$y+1, 6,6);
		  $pdf->SetLeftMargin(109);
		  $pdf->SetY($y);
		  $pdf->MultiCell(38,4, utf8_decode($lineAdress),0,'L');

		  $pdf->Image(DOL_DOCUMENT_ROOT.'/paiementsalaire/doc/icone_folder/autre.png',148,$y+1, 6,6);
		  $pdf->SetLeftMargin(154);
		  $pdf->SetY($y);
		  $pdf->MultiCell(40,4, utf8_decode($identProf4),0,'L');
		}else{
			$pdf->Image(DOL_DOCUMENT_ROOT.'/paiementsalaire/doc/icone_folder/phone.jpg',13,$y+1, 6,6);
		  $pdf->SetLeftMargin($marge_droite+6);
		  $pdf->SetY($y);
		  $pdf->MultiCell(38,4, utf8_decode($lineTel),0,'L');

		  $pdf->Image(DOL_DOCUMENT_ROOT.'/paiementsalaire/doc/icone_folder/envelope.jpg',58,$y+1, 6,6);
		  $pdf->SetLeftMargin(64);
		  $pdf->SetY($y);
		  $pdf->MultiCell(38,4, utf8_decode($lineEmail),0,'L');

		  $pdf->Image(DOL_DOCUMENT_ROOT.'/paiementsalaire/doc/icone_folder/locationMap.jpg',103,$y+1, 6,6);
		  $pdf->SetLeftMargin(109);
		  $pdf->SetY($y);
		  $pdf->MultiCell(38,4, utf8_decode($lineAdress),0,'L');

		  $pdf->Image(DOL_DOCUMENT_ROOT.'/paiementsalaire/doc/icone_folder/autre.jpg',148,$y+1, 6,6);
		  $pdf->SetLeftMargin(154);
		  $pdf->SetY($y);
		  $pdf->MultiCell(40,4, utf8_decode($identProf4),0,'L');
		}

}

//---------------------------------------------------------------------------------------------
/**
 *  Show tab footer of a card.
 *  Note: $object->next_prev_filter can be set to restrict select to find next or previous record by $form->afficher.
 *
 *  @param	Object	$object			Object to show
 *  @param	string	$paramid   		Name of parameter to use to name the id into the URL next/previous link
 *  @param	string	$morehtml  		More html content to output just before the nav bar
 *  @param	int		$shownav	  	Show Condition (navigation is shown if value is 1)
 *  @param	string	$fieldid   		Nom du champ en base a utiliser pour select next et previous (we make the select max and min on object field). Use 'none' for no prev/next search.
 *  @param	string	$fieldref   	Nom du champ objet ref (object->ref) a utiliser pour select next et previous
 *  @param	string	$morehtmlref  	More html to show after the ref (see $morehtmlleft for before)
 *  @param	string	$moreparam  	More param to add in nav link url.
 *	@param	int		$nodbprefix		Do not include DB prefix to forge table name
 *	@param	string	$morehtmlleft	More html code to show before the ref (see $morehtmlref for after)
 *	@param	string	$morehtmlstatus	More html code to show under navigation arrows
 *  @param  int     $onlybanner     Put object to 1, if the card will contains only a banner (object add css 'arearefnobottom' on div)
 *	@param	string	$morehtmlright	More html code to show before navigation arrows
 *  @return	void
 */
function entete_societe($object, $paramid, $morehtml = '', $shownav = 1, $fieldid = 'rowid', $fieldref = 'ref', $morehtmlref = '', $moreparam = '', $nodbprefix = 0, $morehtmlleft = '', $morehtmlstatus = '', $onlybanner = 0, $morehtmlright = '')
{
	global $conf, $form, $user, $langs, $hookmanager, $action;

	$error = 0;

	$maxvisiblephotos = 1;
	$showimage = 1;
	$entity = (empty($object->entity) ? $conf->entity : $object->entity);
	$showbarcode = empty($conf->barcode->enabled) ? 0 : (empty($object->barcode) ? 0 : 1);
	if (!empty($conf->global->MAIN_USE_ADVANCED_PERMS) && empty($user->rights->barcode->lire_advance)) {
		$showbarcode = 0;
	}
	$modulepart = 'unknown';

	if ($object->element == 'societe' || $object->element == 'contact' || $object->element == 'product' || $object->element == 'ticket') {
		$modulepart = $object->element;
	} elseif ($object->element == 'member') {
		$modulepart = 'memberphoto';
	} elseif ($object->element == 'user') {
		$modulepart = 'userphoto';
	}

	if (class_exists("Imagick")) {
		if ($object->element == 'expensereport' || $object->element == 'propal' || $object->element == 'commande' || $object->element == 'facture' || $object->element == 'supplier_proposal') {
			$modulepart = $object->element;
		} elseif ($object->element == 'fichinter') {
			$modulepart = 'ficheinter';
		} elseif ($object->element == 'contrat') {
			$modulepart = 'contract';
		} elseif ($object->element == 'order_supplier') {
			$modulepart = 'supplier_order';
		} elseif ($object->element == 'invoice_supplier') {
			$modulepart = 'supplier_invoice';
		}
	}

	/*$img = './../config/logo_societe/'.$object->rowid;
		if(file_exists($img.'.png')){
			$img .= '.png';
		}elseif(file_exists($img.'.jpg')){
			$img .= '.jpg';
		}else{
			$img .= '.jpeg';
		}*/

	if(is_readable($img)){
		$phototoshow = '<div class="photoref">';
		$phototoshow .= '<img height="60" class="photo photowithborder" src='.$img.'>';
		$phototoshow .= '</div>';
		$morehtmlleft .= '<div class="floatleft inline-block valignmiddle divphotoref">';
		$morehtmlleft .= $phototoshow;
		$morehtmlleft .= '</div>';

	}elseif ($showimage) {
			if ($modulepart != 'unknown') {
				$phototoshow = '';
				// Check if a preview file is available
				if (in_array($modulepart, array('propal', 'commande', 'facture', 'ficheinter', 'contract', 'supplier_order', 'supplier_proposal', 'supplier_invoice', 'expensereport')) && class_exists("Imagick")) {
					$objectref = dol_sanitizeFileName($object->ref);
					$dir_output = (empty($conf->$modulepart->multidir_output[$entity]) ? $conf->$modulepart->dir_output : $conf->$modulepart->multidir_output[$entity])."/";
					if (in_array($modulepart, array('invoice_supplier', 'supplier_invoice'))) {
						$subdir = get_exdir($object->id, 2, 0, 1, $object, $modulepart);
						$subdir .= ((!empty($subdir) && !preg_match('/\/$/', $subdir)) ? '/' : '').$objectref; // the objectref dir is not included into get_exdir when used with level=2, so we add it at end
					} else {
						$subdir = get_exdir($object->id, 0, 0, 1, $object, $modulepart);
					}
					if (empty($subdir)) {
						$subdir = 'errorgettingsubdirofobject'; // Protection to avoid to return empty path
					}

					$filepath = $dir_output.$subdir."/";

					$filepdf = $filepath.$objectref.".pdf";
					$relativepath = $subdir.'/'.$objectref.'.pdf';

					// Define path to preview pdf file (preview precompiled "file.ext" are "file.ext_preview.png")
					$fileimage = $filepdf.'_preview.png';
					$relativepathimage = $relativepath.'_preview.png';

					$pdfexists = file_exists($filepdf);

					// If PDF file exists
					if ($pdfexists) {
						// Conversion du PDF en image png si fichier png non existant
						if (!file_exists($fileimage) || (filemtime($fileimage) < filemtime($filepdf))) {
							if (empty($conf->global->MAIN_DISABLE_PDF_THUMBS)) {		// If you experience trouble with pdf thumb generation and imagick, you can disable here.
								include_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
								$ret = dol_convert_file($filepdf, 'png', $fileimage, '0'); // Convert first page of PDF into a file _preview.png
								if ($ret < 0) {
									$error++;
								}
							}
						}
					}

					if ($pdfexists && !$error) {
						$heightforphotref = 80;
						if (!empty($conf->dol_optimize_smallscreen)) {
							$heightforphotref = 60;
						}
						// If the preview file is found
						if (file_exists($fileimage)) {
							$phototoshow = '<div class="photoref">';
							$phototoshow .= '<img height="'.$heightforphotref.'" class="photo photowithmargin photowithborder" src="'.DOL_URL_ROOT.'/viewimage.php?modulepart=apercu'.$modulepart.'&amp;file='.urlencode($relativepathimage).'">';
							$phototoshow .= '</div>';
						}
					}
				} elseif (!$phototoshow) { // example if modulepart = 'societe' or 'photo'
					$phototoshow .= $form->showphoto($modulepart, $object, 0, 0, 0, 'photowithmargin photoref', 'small', 1, 0, $maxvisiblephotos);
				}

				if ($phototoshow) {
					$morehtmlleft .= '<div class="floatleft inline-block valignmiddle divphotoref">';
					$morehtmlleft .= $phototoshow;
					$morehtmlleft .= '</div>';
				}
			}

			if (empty($phototoshow)) {      // Show No photo link (picto of object)
				if ($object->element == 'action') {
					$width = 80;
					$cssclass = 'photorefcenter';
					$nophoto = img_picto('No photo', 'title_agenda');
				} else {
					$width = 14;
					$cssclass = 'photorefcenter';
					$picto = $object->picto;
					if ($object->element == 'project' && !$object->public) {
						$picto = 'project'; // instead of projectpub
					}
					$nophoto = img_picto('No photo', 'object_'.$picto);
				}
				$morehtmlleft .= '<!-- No photo to show -->';
				$morehtmlleft .= '<div class="floatleft inline-block valignmiddle divphotoref"><div class="photoref">';
				$morehtmlleft .= $nophoto;
				$morehtmlleft .= '</div></div>';
			}
		}



	if ($object->element == 'societe') {
		if (!empty($conf->use_javascript_ajax) && $user->rights->societe->creer && !empty($conf->global->MAIN_DIRECT_STATUS_UPDATE)) {
			$morehtmlstatus .= ajax_object_onoff($object, 'status', 'status', 'InActivity', 'ActivityCeased');
		} else {
			//$morehtmlstatus .= $object->getLibStatut(6);
		}
	}

	// Add if object was dispatched "into accountancy"
	if (!empty($conf->accounting->enabled) && in_array($object->element, array('bank', 'paiementcharge', 'facture', 'invoice', 'invoice_supplier', 'expensereport', 'payment_various'))) {
		// Note: For 'chargesociales', 'salaries'... object is the payments that are dispatched (so element = 'bank')
		if (method_exists($object, 'getVentilExportCompta')) {
			$accounted = $object->getVentilExportCompta();
			$langs->load("accountancy");
			$morehtmlstatus .= '</div><div class="statusref statusrefbis"><span class="opacitymedium">'.($accounted > 0 ? $langs->trans("Accounted") : $langs->trans("NotYetAccounted")).'</span>';
		}
	}

	// Add alias for thirdparty
	if (!empty($object->name_alias)) {
		$morehtmlref .= '<div class="refidno">'.$object->name_alias.'</div>';
	}

	// Add label
	if (in_array($object->element, array('product', 'bank_account', 'project_task'))) {
		if (!empty($object->label)) {
			$morehtmlref .= '<div class="refidno">'.$object->label.'</div>';
		}
	}

		$moreaddress = getAdressComplet($object);
		if ($moreaddress) {
			if($object->element=='user')
			 $moreaddress = explode("-", $moreaddress)[0];

			$morehtmlref .= '<div class="refidno">';
			$morehtmlref .= $moreaddress;
			$morehtmlref .= '</div>';
		}
	if (!empty($conf->global->MAIN_SHOW_TECHNICAL_ID) && ($conf->global->MAIN_SHOW_TECHNICAL_ID == '1' || preg_match('/'.preg_quote($object->element, '/').'/i', $conf->global->MAIN_SHOW_TECHNICAL_ID)) && !empty($object->id)) {
		$morehtmlref .= '<div style="clear: both;"></div>';
		$morehtmlref .= '<div class="refidno">';
		$morehtmlref .= $langs->trans("TechnicalID").': '.$object->id;
		$morehtmlref .= '</div>';
	}

	$parameters=array('morehtmlref'=>$morehtmlref);
	$reshook = $hookmanager->executeHooks('formDolBanner', $parameters, $object, $action);
	if ($reshook < 0) {
		setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
	} elseif (empty($reshook)) {
		$morehtmlref .= $hookmanager->resPrint;
	} elseif ($reshook > 0) {
		$morehtmlref = $hookmanager->resPrint;
	}


	print '<div class="'.($onlybanner ? 'arearefnobottom ' : 'arearef ').'heightref valignmiddle centpercent">';
	print afficher($object, $paramid, $morehtml, $shownav, $fieldid, $fieldref, $morehtmlref, $moreparam, $nodbprefix, $morehtmlleft, $morehtmlstatus, $morehtmlright);
	print '</div>';
	print '<div class="underrefbanner clearboth"></div>';
}


/**
	 *    Return a HTML area with the reference of object and a navigation bar for a business object
	 *    Note: To complete search with a particular filter on select, you can set $object->next_prev_filter set to define SQL criterias.
	 *
	 *    @param	object	$object			Object to show.
	 *    @param	string	$paramid   		Name of parameter to use to name the id into the URL next/previous link.
	 *    @param	string	$morehtml  		More html content to output just before the nav bar.
	 *    @param	int		$shownav	  	Show Condition (navigation is shown if value is 1).
	 *    @param	string	$fieldid   		Name of field id into database to use for select next and previous (we make the select max and min on object field compared to $object->ref). Use 'none' to disable next/prev.
	 *    @param	string	$fieldref   	Name of field ref of object (object->ref) to show or 'none' to not show ref.
	 *    @param	string	$morehtmlref  	More html to show after ref.
	 *    @param	string	$moreparam  	More param to add in nav link url. Must start with '&...'.
	 *	  @param	int		$nodbprefix		Do not include DB prefix to forge table name.
	 *	  @param	string	$morehtmlleft	More html code to show before ref.
	 *	  @param	string	$morehtmlstatus	More html code to show under navigation arrows (status place).
	 *	  @param	string	$morehtmlright	More html code to show after ref.
	 * 	  @return	string    				Portion HTML with ref + navigation buttons
	 */
	function afficher($object, $paramid, $morehtml = '', $shownav = 1, $fieldid = 'rowid', $fieldref = 'ref', $morehtmlref = '', $moreparam = '', $nodbprefix = 0, $morehtmlleft = '', $morehtmlstatus = '', $morehtmlright = '')
	{
		global $conf, $langs, $hookmanager, $extralanguages;

		$ret = '';
		if (empty($fieldid)) {
			$fieldid = 'rowid';
		}
		if (empty($fieldref)) {
			$fieldref = 'ref';
		}

		// Preparing gender's display if there is one
		$addgendertxt = '';
		if (property_exists($object, 'gender') && !empty($object->gender)) {
			$addgendertxt = ' ';
			switch ($object->gender) {
				case 'man':
					$addgendertxt .= '<i class="fas fa-mars"></i>';
					break;
				case 'woman':
					$addgendertxt .= '<i class="fas fa-venus"></i>';
					break;
				case 'other':
					$addgendertxt .= '<i class="fas fa-genderless"></i>';
					break;
			}
		}
		/*
		$addadmin = '';
		if (property_exists($object, 'admin')) {
			if (!empty($conf->multicompany->enabled) && !empty($object->admin) && empty($object->entity)) {
				$addadmin .= img_picto($langs->trans("SuperAdministratorDesc"), "redstar", 'class="paddingleft"');
			} elseif (!empty($object->admin)) {
				$addadmin .= img_picto($langs->trans("AdministratorDesc"), "star", 'class="paddingleft"');
			}
		}*/

		// Add where from hooks
		if (is_object($hookmanager)) {
			$parameters = array();
			$reshook = $hookmanager->executeHooks('printFieldListWhere', $parameters, $object); // Note that $action and $object may have been modified by hook
			$object->next_prev_filter .= $hookmanager->resPrint;
		}
		$previous_ref = $next_ref = '';
		if ($shownav) {
			//print "paramid=$paramid,morehtml=$morehtml,shownav=$shownav,$fieldid,$fieldref,$morehtmlref,$moreparam";
			//$object->load_previous_next_ref((isset($object->next_prev_filter) ? $object->next_prev_filter : ''), $fieldid, $nodbprefix);

			$navurl = $object->retour;

			// Special case for project/task page
			if ($paramid == 'project_ref') {
				if (preg_match('/\/tasks\/(task|contact|note|document)\.php/', $navurl)) {     // TODO Remove object when nav with project_ref on task pages are ok
					$navurl = preg_replace('/\/tasks\/(task|contact|time|note|document)\.php/', '/tasks.php', $navurl);
					$paramid = 'ref';
				}
			}

			// accesskey is for Windows or Linux:  ALT + key for chrome, ALT + SHIFT + KEY for firefox
			// accesskey is for Mac:               CTRL + key for all browsers
			$stringforfirstkey = $langs->trans("KeyboardShortcut");
			if ($conf->browser->name == 'chrome') {
				$stringforfirstkey .= ' ALT +';
			} elseif ($conf->browser->name == 'firefox') {
				$stringforfirstkey .= ' ALT + SHIFT +';
			} else {
				$stringforfirstkey .= ' CTL +';
			}

			$previous_ref = $object->retour ? '<a accesskey="p" title="'.$stringforfirstkey.' p" class="classfortooltip" href="'.$navurl.'"><b>Retour liste</a></b>' : '<span class="inactive">Retour liste</span>';
			$previous_ref .= $object->ref_previous ?'<a title="'.$object->nom_precedent.'" href="'.$object->ref_previous.'"><i class="fa fa-chevron-left"></i></a>' : '<span class="inactive"><i class="fa fa-chevron-left opacitymedium"></i></span>';
			$next_ref     = $object->ref_next ? '<a title="'.$object->nom_suivant.'" href="'.$object->ref_next.'" ><i class="fa fa-chevron-right"></i></a>' : '<span class="inactive"><i class="fa fa-chevron-right opacitymedium"></i></span>';

			//$next_ref     = $object->ref_next ? '<a accesskey="n" title="'.$stringforfirstkey.' n" class="classfortooltip" href="'.$navurl.'?'.$paramid.'='.urlencode($object->ref_next).$moreparam.'"><i class="fa fa-chevron-right"></i></a>' : '<span class="inactive"><i class="fa fa-chevron-right opacitymedium"></i></span>';
		}

		//print "xx".$previous_ref."x".$next_ref;
		$ret .= '<!-- Start banner content --><div style="vertical-align: middle">';

		// Right part of banner
		if ($morehtmlright) {
			$ret .= '<div class="inline-block floatleft">'.$morehtmlright.'</div>';
		}

		if ($previous_ref || $next_ref || $morehtml) {
			$ret .= '<div class="pagination paginationref"><ul class="right">';
		}
		if ($morehtml) {
			$ret .= '<li class="noborder litext'.(($shownav && $previous_ref && $next_ref) ? ' clearbothonsmartphone' : '').'">'.$morehtml.'</li>';
		}
		if ($shownav && ($previous_ref || $next_ref)) {
			$ret .= '<li class="pagination">'.$previous_ref.'</li>';
			$ret .= '<li class="pagination">'.$next_ref.'</li>';
		}
		if ($previous_ref || $next_ref || $morehtml) {
			$ret .= '</ul></div>';
		}

		$parameters = array();
		$reshook = $hookmanager->executeHooks('moreHtmlStatus', $parameters, $object); // Note that $action and $object may have been modified by hook
		if (empty($reshook)) {
			$morehtmlstatus .= $hookmanager->resPrint;
		} else {
			$morehtmlstatus = $hookmanager->resPrint;
		}
		if ($morehtmlstatus) {
			$ret .= '<div class="statusref">'.$morehtmlstatus.'</div>';
		}

		$parameters = array();
		$reshook = $hookmanager->executeHooks('moreHtmlRef', $parameters, $object); // Note that $action and $object may have been modified by hook
		if (empty($reshook)) {
			$morehtmlref .= $hookmanager->resPrint;
		} elseif ($reshook > 0) {
			$morehtmlref = $hookmanager->resPrint;
		}

		// Left part of banner
		if ($morehtmlleft) {
			if ($conf->browser->layout == 'phone') {
				$ret .= '<!-- morehtmlleft --><div class="floatleft">'.$morehtmlleft.'</div>'; // class="center" to have photo in middle
			} else {
				$ret .= '<!-- morehtmlleft --><div class="inline-block floatleft">'.$morehtmlleft.'</div>';
			}
		}

		//if ($conf->browser->layout == 'phone') $ret.='<div class="clearboth"></div>';
		$ret .= '<div class="inline-block floatleft valignmiddle maxwidth750 marginbottomonly refid'.(($shownav && ($previous_ref || $next_ref)) ? ' refidpadding' : '').'">';

		// For thirdparty, contact, user, member, the ref is the id, so we show something else
		if ($object->element == 'societe') { //Affichage du nom de la société si on est dans société
			$ret .= "<a title='Voir les informations de la société dans Tiers' href='../../societe/card.php?socid=".$object->rowid."'>".dol_htmlentities($object->nom)."</a>";
			// List of extra languages
			$arrayoflangcode = array();
			if (!empty($conf->global->PDF_USE_ALSO_LANGUAGE_CODE)) {
				$arrayoflangcode[] = $conf->global->PDF_USE_ALSO_LANGUAGE_CODE;
			}

			if (is_array($arrayoflangcode) && count($arrayoflangcode)) {
				if (!is_object($extralanguages)) {
					include_once DOL_DOCUMENT_ROOT.'/core/class/extralanguages.class.php';
					$extralanguages = new ExtraLanguages($object->db);
				}
				$extralanguages->fetch_name_extralanguages('societe');

				if (!empty($extralanguages->attributes['societe']['name'])) {
					$object->fetchValuesForExtraLanguages();

					$htmltext = '';
					// If there is extra languages
					foreach ($arrayoflangcode as $extralangcode) {
						$htmltext .= picto_from_langcode($extralangcode, 'class="pictoforlang paddingright"');
						if ($object->array_languages['name'][$extralangcode]) {
							$htmltext .= $object->array_languages['name'][$extralangcode];
						} else {
							$htmltext .= '<span class="opacitymedium">'.$langs->trans("SwitchInEditModeToAddTranslation").'</span>';
						}
					}
					$ret .= '<!-- Show translations of name -->'."\n";
					$ret .= $object->textwithpicto('', $htmltext, -1, 'language', 'opacitymedium paddingleft');
				}
			}
		} elseif ($object->element == 'member') {
			$ret .= $object->ref.'<br>';
			$fullname = $object->getFullName($langs);
			if ($object->morphy == 'mor' && $object->societe) {
				$ret .= dol_htmlentities($object->societe).((!empty($fullname) && $object->societe != $fullname) ? ' ('.dol_htmlentities($fullname).$addgendertxt.')' : '');
			} else {
				$ret .= dol_htmlentities($fullname).$addgendertxt.((!empty($object->societe) && $object->societe != $fullname) ? ' ('.dol_htmlentities($object->societe).')' : '');
			}
		} elseif (in_array($object->element, array('contact', 'usergroup'))) {
			$ret .= dol_htmlentities($object->getFullName($langs));
		}
		elseif($object->element=='user'){//Affichage du nom de la société dans salarié
			$ret .= "<a title='Voir la société dans Salaire|paie' href='liste_personnelle.php?mainmenu=paiementsalaire&leftmenu=societe&id_societe=".$object->socid."&id_convention=".$object->conv."&action=recherche'>".dol_htmlentities($object->name)."</a>";
		} elseif (in_array($object->element, array('action', 'agenda'))) {
			$ret .= $object->ref.'<br>'.$object->label;
		} elseif (in_array($object->element, array('adherent_type'))) {
			$ret .= $object->label;
		} elseif ($object->element == 'ecm_directories') {
			$ret .= '';
		} elseif ($fieldref != 'none') {
			$ret .= dol_htmlentities($object->$fieldref);
		}

		if ($morehtmlref) {
			// don't add a additional space, when "$morehtmlref" starts with a HTML div tag
			if (substr($morehtmlref, 0, 4) != '<div') {
				$ret .= ' ';
			}

			$ret .= $morehtmlref;
		}

		$ret .= '</div>';

		$ret .= '</div><!-- End banner content -->';

		return $ret;
	}

	/**
	 *$object   doit avoir la propriété address
	 */

function getAdressComplet($object){
		$out = '';

		$outdone = 0;
		$coords = $object->address;
		if ($coords) {
			if (!empty($conf->use_javascript_ajax)) {
				// Add picto with tooltip on map
				$namecoords = '';
				if ($object->element == 'contact' && !empty($conf->global->MAIN_SHOW_COMPANY_NAME_IN_BANNER_ADDRESS)) {
					$namecoords .= $object->name.'<br>';
				}
				$namecoords .= $object->getFullName($langs, 1).'<br>'.$coords;
				// hideonsmatphone because copyToClipboard call jquery dialog that does not work with jmobile
				$out .= '<a href="#" class="hideonsmartphone" onclick="return copyToClipboard(\''.dol_escape_js($namecoords).'\',\''.dol_escape_js($langs->trans("HelpCopyToClipboard")).'\');">';
				$out .= img_picto($langs->trans("Address"), 'map-marker-alt');
				$out .= '</a> ';
			}
			$out .= dol_print_address($coords, 'address_'.$htmlkey.'_'.$object->id, $object->element, $object->id, 1, ', ');
			$outdone++;
			$outdone++;

			// List of extra languages
			$arrayoflangcode = array();
			if (!empty($conf->global->PDF_USE_ALSO_LANGUAGE_CODE)) {
				$arrayoflangcode[] = $conf->global->PDF_USE_ALSO_LANGUAGE_CODE;
			}

			if (is_array($arrayoflangcode) && count($arrayoflangcode)) {
				if (!is_object($extralanguages)) {
					include_once DOL_DOCUMENT_ROOT.'/core/class/extralanguages.class.php';
					$extralanguages = new ExtraLanguages($object->db);
				}
				$extralanguages->fetch_name_extralanguages($elementforaltlanguage);

				if (!empty($extralanguages->attributes[$elementforaltlanguage]['address']) || !empty($extralanguages->attributes[$elementforaltlanguage]['town'])) {
					$out .= "<!-- alternatelanguage for '".$elementforaltlanguage."' set to fields '".join(',', $extralanguages->attributes[$elementforaltlanguage])."' -->\n";
					$object->fetchValuesForExtraLanguages();
					if (!is_object($form)) {
						$form = new Form($object->db);
					}
					$htmltext = '';
					// If there is extra languages
					foreach ($arrayoflangcode as $extralangcode) {
						$s = picto_from_langcode($extralangcode, 'class="pictoforlang paddingright"');
						$coords = $object->getFullAddress(1, ', ', $conf->global->MAIN_SHOW_REGION_IN_STATE_SELECT, $extralangcode);
						$htmltext .= $s.dol_print_address($coords, 'address_'.$htmlkey.'_'.$object->id, $object->element, $object->id, 1, ', ');
					}
					$out .= $form->textwithpicto('', $htmltext, -1, 'language', 'opacitymedium paddingleft');
				}
			}
		}

			if (!empty($conf->global->MAIN_SHOW_REGION_IN_STATE_SELECT) && $conf->global->MAIN_SHOW_REGION_IN_STATE_SELECT == 1 && $object->region) {
				$out .= ($outdone ? ' - ' : '').$object->region.' - '.$object->state;
			} else {
				$out .= ($outdone ? ' - ' : '').$object->state;
			}
			$outdone++;


		if (!empty($object->phone) || !empty($object->phone_pro) || !empty($object->phone_mobile) || !empty($object->phone_perso) || !empty($object->fax) || !empty($object->office_phone) || !empty($object->user_mobile) || !empty($object->office_fax)) {
			$out .= ($outdone ? '<br>' : '');
		}
		if (!empty($object->phone) && empty($object->phone_pro)) {		// For objects that store pro phone into ->phone
			$out .= dol_print_phone($object->phone, $object->country_code, $contactid, $thirdpartyid, 'AC_TEL', '&nbsp;', 'phone', "");
			$outdone++;
		}
		if (!empty($object->phone_pro)) {
			$out .= dol_print_phone($object->phone_pro, $object->country_code, $contactid, $thirdpartyid, 'AC_TEL', '&nbsp;', 'phone', "");
			$outdone++;
		}
		if (!empty($object->phone_mobile)) {
			$out .= dol_print_phone($object->phone_mobile, $object->country_code, $contactid, $thirdpartyid, 'AC_TEL', '&nbsp;', 'mobile', "");
			$outdone++;
		}
		if (!empty($object->phone_perso)) {
			$out .= dol_print_phone($object->phone_perso, $object->country_code, $contactid, $thirdpartyid, 'AC_TEL', '&nbsp;', 'phone', "");
			$outdone++;
		}
		if (!empty($object->office_phone)) {
			$out .= dol_print_phone($object->office_phone, $object->country_code, $contactid, $thirdpartyid, 'AC_TEL', '&nbsp;', 'phone', "");
			$outdone++;
		}
		if (!empty($object->user_mobile)) {
			$out .= dol_print_phone($object->user_mobile, $object->country_code, $contactid, $thirdpartyid, 'AC_TEL', '&nbsp;', 'mobile', "");
			$outdone++;
		}
		if (!empty($object->fax)) {
			$out .= dol_print_phone($object->fax, $object->country_code, $contactid, $thirdpartyid, 'AC_FAX', '&nbsp;', 'fax', "");
			$outdone++;
		}
		if (!empty($object->office_fax)) {
			$out .= dol_print_phone($object->office_fax, $object->country_code, $contactid, $thirdpartyid, 'AC_FAX', '&nbsp;', 'fax', "");
			$outdone++;
		}

		if ($out) {
			$out .= '<div style="clear: both;"></div>';
		}
		$outdone = 0;
		if (!empty($object->email)) {
			$out .= dol_print_email($object->email, $object->id, $object->id, 'AC_EMAIL', 0, 0, 1);
			$outdone++;
		}
		if (!empty($object->url)) {
			//$out.=dol_print_url($object->url,'_goout',0,1);//steve changed to blank
			$out .= dol_print_url($object->url, '_blank', 0, 1);
	}
	if($out)
		return $out;
	else return '';

}

/**
 * cette fonction prepare l'objet entête salarié a afficher.
 * Et après cet objet sera donnée à la fonction entete_societe
 */
function prepare_objet_entete($fk_salarie, $id_salarie, $db, $id_societe, $id_convention){

	/*$existSql = "SELECT * FROM ".MAIN_DB_PREFIX."salarie WHERE rowid=".$fk_salarie;
	$existResult = $db->query($existSql);
	$existSalarie = $db->fetch_object($existResult);

	$CatSQL = "SELECT * FROM ".MAIN_DB_PREFIX."dcategories where rowid=".$existSalarie->fk_categorie;
	$catResult = $db->query($CatSQL);
	$catSalarie = $db->fetch_object($catResult);
	$echelon = "";
	if($existSalarie->fk_echelon != 0){
		$echelon_SQL = "SELECT libelle FROM ".MAIN_DB_PREFIX."echelon WHERE rowid=".$existSalarie->fk_echelon;
		$echelon_result = $db->query($echelon_SQL);
		$obj_echelon = $db->fetch_object($echelon_SQL);
		$echelon .= $obj_echelon->libelle;
	}*/

	$soc_sql = "SELECT * FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$id_salarie;
	$soc_res = $db->query($soc_sql);//= $db->query($covSql);
	$obj_soc = $db->fetch_object($soc_res);

	$societe_Sql = "SELECT rowid, nom FROM ".MAIN_DB_PREFIX."societe WHERE rowid=".$id_societe;
	$societe_Result = $db->query($societe_Sql);
	$societe_Salarie = $db->fetch_object($societe_Result);

	$obj_soc->name = $societe_Salarie->nom;
	$obj_soc->element = "user";
	$obj_soc->socid = $societe_Salarie->rowid; //id de la société
	$info = $obj_soc->firstname."  ".$obj_soc->lastname."<br> Fonction : ".$obj_soc->job."".($obj_soc->office_phone?("<br> Tel : ".$obj_soc->office_phone):($obj_soc->office_fax?("<br> Tel : ".$obj_soc->office_fax) : ($obj_soc->user_mobile? ("<br> Tel : ".$obj_soc->user_mobile):"")))."";//"Matricule : ".$existSalarie->identifiant."<br>Catégorie : ".$catSalarie->code_categorie."".($echelon?" ==> ".$echelon:"");
	$obj_soc->address = $info;
	$obj_soc->retour = '../listesalarie.php?mainmenu=paiementsalaire&leftmenu=salarie&id_societe='.$id_societe.'&id_convention='.$id_convention.'&action=recherche';

	//preparation du salarié suivant et precedent
	$sql_prev = "SELECT sal.rowid as id_salarie, sal.matricule, sal.fk_user, u.rowid, u.lastname, u.firstname, u.dateemployment, ue.fk_object, ue.egp FROM ".MAIN_DB_PREFIX."salarie as sal";
	$sql_prev .= " LEFT JOIN ".MAIN_DB_PREFIX."user as u ON u.rowid=sal.fk_user";
	$sql_prev .= " LEFT JOIN ".MAIN_DB_PREFIX."user_extrafields as ue ON u.rowid=ue.fk_object Where ue.egp=".$id_societe." AND sal.rowid<".$fk_salarie;
	$sql_prev .= " ORDER BY sal.rowid DESC";
	$soc_res_prev = $db->query($sql_prev);//= $db->query($covSql);
	$nom_prenom_prev = "";
	if($soc_res_prev)
		if($db->num_rows($soc_res_prev)>0){
			$obj_prev = $db->fetch_object($soc_res_prev);
			$obj_soc->ref_previous = './salarie_information.php?mainmenu=paiementsalaire&leftmenu=salarie&fk_salarie='.$obj_prev->id_salarie.'&id_societe='.$id_societe.'&id='.$obj_prev->rowid.'&id_convention='.$id_convention.'&action=detail';
			$nom_prenom_prev = $obj_prev->firstname." ".$obj_prev->lastname;

		}
	$sql_next = "SELECT sal.rowid as id_salarie, sal.matricule, sal.fk_user, u.rowid, u.lastname, u.firstname, u.dateemployment, ue.fk_object, ue.egp FROM ".MAIN_DB_PREFIX."salarie as sal";
	$sql_next .= " LEFT JOIN ".MAIN_DB_PREFIX."user as u ON u.rowid=sal.fk_user";
	$sql_next .= " LEFT JOIN ".MAIN_DB_PREFIX."user_extrafields as ue ON u.rowid=ue.fk_object Where ue.egp=".$id_societe." AND sal.rowid>".$fk_salarie;
	$sql_next .= " ORDER BY sal.rowid";
	$soc_res_next = $db->query($sql_next);//= $db->query($covSql);
	$nom_prenom_next = "";
	if($soc_res_next)
		if($db->num_rows($soc_res_next)>0){
			$obj_next = $db->fetch_object($soc_res_next);
			$obj_soc->ref_next = './salarie_information.php?mainmenu=paiementsalaire&leftmenu=salarie&fk_salarie='.$obj_next->id_salarie.'&id_societe='.$id_societe.'&id='.$obj_next->rowid.'&id_convention='.$id_convention.'&action=detail';
			$nom_prenom_next = $obj_next->firstname." ".$obj_next->lastname;
		}

	$obj_soc->conv = $id_convention;
	$obj_soc->nom_precedent = $nom_prenom_prev;
	$obj_soc->nom_suivant = $nom_prenom_next;

	return $obj_soc;
}

function anciennete_valeur($db, $anciennete, $id_convention){
	$taux = 0;
			if($anciennete == 0){
				$taux = 0;
			}else{
				$v_sql = "SELECT * FROM ".MAIN_DB_PREFIX."condition_anciennete WHERE fk_convention=".$id_convention." AND nombre_annee=".$anciennete;
				$v_result = $db->query($v_sql);
				$v_obj = $db->fetch_object($v_result);
				if($v_obj){
					$taux = $v_obj->taux;
				}else{
					$v_sql = "SELECT * FROM ".MAIN_DB_PREFIX."condition_anciennete WHERE fk_convention=".$id_convention." AND nombre_annee='+'";
					$v_result = $db->query($v_sql);
					$v_obj = $db->fetch_object($v_result);
					$taux = $v_obj->taux;
				}
			}

	return $taux;
}


/**
 * cette fonction verifie et supprime s'il un utilisateur dans Paiement|Salaire qui n'est pas dans User & Groupe
 */
function verification_suppression($db){

	$sql = "DELETE FROM ".MAIN_DB_PREFIX."salarie WHERE fk_user NOT IN ( SELECT rowid FROM ".MAIN_DB_PREFIX."user)";
	$res = $db->query($sql);
}


function salarie_indemnite_simulation($db, $fk_salarie='', $salaire_base, $fk_categorie, $type_salarie=0, $id_convention){

	$all_indemnite_rowid = array(); //toutes les indemnités(categorie, associés)
	$all_indemnite_unique_rowid = array();//la table avec non dedoublons du premier

	$indemnite_rowid = array();
	$all_indemnite_montant = array();

	if(!empty($fk_salarie)){
		$sql = "SELECT * FROM ".MAIN_DB_PREFIX."salarie WHERE rowid=".$fk_salarie;
		$res = $db->query($sql);
		$obj = $db->fetch_object($res);
	}

	//Recupération de toutes les indémnités obligatoires
	$sql = "SELECT * FROM ".MAIN_DB_PREFIX."indemnite WHERE type_indemnite='obligatoire' AND active=1 AND (fk_convention=".$id_convention." OR fk_convention=0) AND fk_societe = 0 AND fk_accord_etablissement = 0";

	$oblig_indemnite = $db->query($sql);
	$indemnite = array();
	if($oblig_indemnite){
		$num = $db->num_rows($oblig_indemnite);
		$i = 0;
		while ($i < $num) {
			$obj_oblig_indemnite = $db->fetch_object($oblig_indemnite);
			$all_indemnite_rowid[$i] = $obj_oblig_indemnite->rowid;
			$i ++;
		}
	}

	//Récupération des indemnités liés à la catégorie
	$sql = "SELECT * FROM ".MAIN_DB_PREFIX."categorie_indemnite WHERE fk_categorie=".$obj->fk_categorie;
	$categ_indemnite = $db->query($sql);
	if($categ_indemnite){
		$num = $db->num_rows($categ_indemnite);
		$i = 0;
		while ($i < $num) {
			$obj_categ_indemnite = $db->fetch_object($categ_indemnite);
			$all_indemnite_rowid[] = $obj_categ_indemnite->fk_indemnite;
			$i ++;
		}
	}
	//primes individuelles
	$sql = "SELECT * FROM ".MAIN_DB_PREFIX."salarie_indemnite WHERE fk_salarie=".$fk_salarie;
	$sal_indemnite = $db->query($sql);

	if($sal_indemnite){
		$num = $db->num_rows($sal_indemnite);
		$i = 0;
		while ($i < $num) {

			$obj_sal_indemnite = $db->fetch_object($sal_indemnite);

			$all_indemnite_rowid[] = $obj_sal_indemnite->fk_indemnite;
			$i ++;
		}
	}
	$taille = count($all_indemnite_rowid);
	$all_indemnite_unique_rowid [0] = $all_indemnite_rowid[0];
	$a = 1;
	$trouve = false;
	while ($a < $taille) {
		$i = 0;
		$tail = count($all_indemnite_unique_rowid);
		while ($i < $tail) {
			if($all_indemnite_unique_rowid[$i] == $all_indemnite_rowid[$a])
				$trouve = true;
				$i ++;
			}
			if($trouve == false){
				$all_indemnite_unique_rowid[] = $all_indemnite_rowid[$a];
		}
		//print "....".$all_indemnite_rowid[$a];
		$a ++;
	}

	//Traitements sur indemnités------------------------------------------
//Indemnités

$taille = count($all_indemnite_unique_rowid);
for($i = 0; $i < $taille; $i++){
	$sql = "SELECT * FROM ".MAIN_DB_PREFIX."indemnite WHERE rowid=".$all_indemnite_unique_rowid[$i]." AND active=1";
	$indemnite_res = $db->query($sql);

	if($indemnite_res){
		$ind = $db->fetch_object($indemnite_res);
		$cond_ind_sql = "SELECT * FROM ".MAIN_DB_PREFIX."condition_indemnite WHERE fk_indemnite=".$ind->rowid;
		$result_cond_ind = $db->query($cond_ind_sql);

		if($result_cond_ind){

			$num = $db->num_rows($result_cond_ind);
			$j = 0;
			while ($j < $num) {
				$correspond = false;
				$cond_ind = $db->fetch_object($result_cond_ind);
			//if($ind->type_indemnite == "obligatoire"){ //Indemnités obligatoires
				$cond_type_salarie_sql = "SELECT * FROM ".MAIN_DB_PREFIX."condition_type_salarie_indemnite WHERE fk_condition=".$cond_ind->rowid." AND fk_type_salarie=".$type_salarie;
				$result_cond_type_salarie = $db->query($cond_type_salarie_sql);
				$cond_type_salarie = $db->fetch_object($result_cond_type_salarie);

				if(!$cond_type_salarie->rowid){
					$cond_type_salarie_sql = "SELECT * FROM ".MAIN_DB_PREFIX."condition_type_salarie_indemnite WHERE fk_condition=".$cond_ind->rowid." AND fk_type_salarie=0";
					$result_cond_type_salarie = $db->query($cond_type_salarie_sql);
					$cond_type_salarie = $db->fetch_object($result_cond_type_salarie);
				}
				//print $cond_type_salarie->fk_type_salarie."-".$j."-";
				if($cond_type_salarie->rowid){ //Verification du type salarié
					$correspond = true;
				}else{//si pas de type salarié correspondant on verifie la catégorie
					$cond_categ_sql = "SELECT * FROM ".MAIN_DB_PREFIX."condition_categorie_indemnite WHERE fk_condition=".$cond_ind->rowid." AND fk_categorie=".$fk_categorie;
					$result_cond_categ = $db->query($cond_categ_sql);
					$cond_categ = $db->fetch_object($result_cond_categ);
					if(!$cond_categ->rowid){
						$cond_categ_sql = "SELECT * FROM ".MAIN_DB_PREFIX."condition_categorie_indemnite WHERE fk_condition=".$cond_ind->rowid." AND fk_categorie=0";
						$result_cond_categ = $db->query($cond_categ_sql);
						$cond_categ = $db->fetch_object($result_cond_categ);
					}

					if($cond_categ->rowid){ //verification de la catégorie
						$correspond = true;
					}
				}
				if($correspond == true){
					//correspondace avec type salarié ou catégorie
					if($cond_ind->superieur > 0){
						if($salaire_base < $cond_ind->superieur){
							if($cond_ind->type_montant == "forfait"){
								$indemnite_rowid[$i] = $ind->rowid;
								$all_indemnite_montant[$i] = $cond_ind->forfait?:0;


							}
						}else if(($cond_ind->pourcentage*$salaire_base/100) < $cond_ind->superieur)
							if($cond_ind->type_montant == "pourcentage"){
								if(($cond_ind->pourcentage*$salaire_base/100) < $cond_ind->minimum_perception){
									$indemnite_rowid[$i] = $ind->rowid;
									$all_indemnite_montant[$i] = $cond_ind->minimum_perception;
								}else{
									$indemnite_rowid[$i] = $ind->rowid;
									$all_indemnite_montant[$i] = $cond_ind->pourcentage*$salaire_base/100;

								}
							}
					}else{
						if($cond_ind->type_montant == "forfait"){
							$indemnite_rowid[$i] = $ind->rowid;
							$all_indemnite_montant[$i] = $cond_ind->forfait;

						}else{
							if(($cond_ind->pourcentage*$salaire_base/100) < $cond_ind->minimum_perception){
								$indemnite_rowid[$i] = $ind->rowid;
								$all_indemnite_montant[$i] = $cond_ind->minimum_perception;

							}else{
								$indemnite_rowid[$i] = $ind->rowid;
								$all_indemnite_montant[$i] = $cond_ind->pourcentage*$salaire_base/100;

							}
						}
					}
						$j = $num;
				}
				/*if(empty($all_indemnite_montant[$i] ))
					$all_indemnite_montant[$i] = 0;*/
		$j++;
	}
}
	}
}
	/*for ($i=0; $i < count($indemnite_rowid); $i++) {
		print "<br>".$indemnite_rowid[$i]." = ".$all_indemnite_montant[$i];

	}*/
	//print count($indemnite_rowid)."==".count($all_indemnite_montant);

	return array_combine($indemnite_rowid, $all_indemnite_montant);

}

//la fonction qui calcul la prime du salarié
//------------------------------------------------------------------------------------------------------------------------------------------------
function salarie_prime_simulation($db, $fk_salarie= 0, $salaire_base, $fk_categorie, $type_salarie, $id_convention){

	$all_prime_rowid = array(); //toutes les indemnités(categorie, associés)
	$all_prime_unique_rowid = array();//la table avec non dedoublons du premier

	$prime_rowid = array();
	$all_prime_montant = array();


	//Recupération de toutes les indémnités obligatoires
	$sql = "SELECT * FROM ".MAIN_DB_PREFIX."primes WHERE type_prime='obligatoire' AND active=1 AND (fk_convention=".$id_convention." OR fk_convention=0) AND fk_societe = 0 AND fk_accord_etablissement = 0";
	$oblig_prime = $db->query($sql);
	$prime = array();
	if($oblig_prime){
		$num = $db->num_rows($oblig_prime);
		$i = 0;
		while ($i < $num) {
			$obj_oblig_prime = $db->fetch_object($oblig_prime);
			$all_prime_rowid[$i] = $obj_oblig_prime->rowid;
			$i ++;
		}
	}

	$sql = "SELECT * FROM ".MAIN_DB_PREFIX."salarie_prime WHERE fk_salarie=".$fk_salarie;
	$sal_prime = $db->query($sql);

	if($sal_prime){
		$num = $db->num_rows($sal_prime);
		$i = 0;
		while ($i < $num) {

			$obj_sal_prime = $db->fetch_object($sal_prime);

			$all_prime_rowid[] = $obj_sal_prime->fk_prime;
			$i ++;
		}
	}
	$taille = count($all_prime_rowid);
	$all_prime_unique_rowid [0] = $all_prime_rowid[0];
	$a = 1;
	$trouve = false;
	while ($a < $taille) {
		$i = 0;
		$tail = count($all_prime_unique_rowid);
		while ($i < $tail) {
			if($all_prime_unique_rowid[$i] == $all_prime_rowid[$a])
				$trouve = true;
				$i ++;
			}
			if($trouve == false){
				$all_prime_unique_rowid[$tail] = $all_prime_rowid[$a];
		}
		//print "....".$all_prime_rowid[$a];
		$a ++;
	}

	//Traitements sur indemnités------------------------------------------
//Indemnités
$taille = count($all_prime_unique_rowid);
for($i = 0; $i < $taille; $i++){
	$sql = "SELECT * FROM ".MAIN_DB_PREFIX."primes WHERE rowid=".$all_prime_unique_rowid[$i]." AND active=1 ";
	$prime_res = $db->query($sql);
	if($prime_res){
		$pr = $db->fetch_object($prime_res);
		$cond_prime_sql = "SELECT * FROM ".MAIN_DB_PREFIX."condition_prime WHERE fk_prime=".$pr->rowid;
		$result_cond_prime = $db->query($cond_prime_sql);
		if($result_cond_prime){
			$num = $db->num_rows($result_cond_prime);
			$j = 0;
			$categ = $fk_categorie;
			while ($j < $num) {

				$correspond = false;
				$cond_prime = $db->fetch_object($result_cond_prime);
				//if($pr->type_prime == "obligatoire"){ //Indemnités obligatoires
				$cond_type_salarie_sql = "SELECT * FROM ".MAIN_DB_PREFIX."condition_type_salarie_prime WHERE fk_condition=".$cond_prime->rowid." AND (fk_type_salarie=0 OR fk_type_salarie=".$type_salarie.")";
				$result_cond_type_salarie = $db->query($cond_type_salarie_sql);
				$cond_type_salarie = $db->fetch_object($result_cond_type_salarie);

				if(!$cond_type_salarie->rowid){
					$cond_type_salarie_sql = "SELECT * FROM ".MAIN_DB_PREFIX."condition_type_salarie_prime WHERE fk_condition=".$cond_prime->rowid." AND fk_type_salarie=0";
					$result_cond_type_salarie = $db->query($cond_type_salarie_sql);
					$cond_type_salarie = $db->fetch_object($result_cond_type_salarie);
				}
				//print $cond_type_salarie->fk_type_salarie."-".$j."-";
				if($cond_type_salarie->rowid){ //Verification du type salarié
					$correspond = true;
				}else{//si pas de type salarié correspondant on verifie la catégorie
					$cond_categ_sql = "SELECT * FROM ".MAIN_DB_PREFIX."condition_categorie_prime WHERE fk_condition=".$cond_prime->rowid." AND fk_categorie=".$categ;
					$result_cond_categ = $db->query($cond_categ_sql);

						$cond_categ = $db->fetch_object($result_cond_categ);
						if(!$cond_categ->rowid){
							$cond_categ_sql = "SELECT * FROM ".MAIN_DB_PREFIX."condition_categorie_prime WHERE fk_condition=".$cond_prime->rowid." AND fk_categorie=0";
							$result_cond_categ = $db->query($cond_categ_sql);
							$cond_categ = $db->fetch_object($result_cond_categ);
						}


					if($cond_categ->rowid){ //verification de la catégorie
						$correspond = true;
					}
				}
				if($correspond == true){
					//correspondace avec type salarié ou catégorie

					if($cond_prime->superieur > 0){
						if($salaire_base < $cond_prime->superieur){
							if($cond_prime->type_montant == "forfait"){
								$prime_rowid[$i] = $pr->rowid;
								$all_prime_montant[$i] = $cond_prime->forfait;

							}
						}else if(($cond_prime->pourcentage*$salaire_base/100) < $cond_prime->superieur)
							if($cond_prime->type_montant == "pourcentage"){
								if(($cond_prime->pourcentage*$salaire_base/100) < $cond_prime->minimum_perception){
									$prime_rowid[$i] = $pr->rowid;
									$all_prime_montant[$i] = $cond_prime->minimum_perception;

								}else{
									$prime_rowid[$i] = $pr->rowid;
									$all_prime_montant[$i] = $cond_prime->pourcentage*$salaire_base/100;

								}
							}
					}else{
						if($cond_prime->type_montant == "forfait"){
							$prime_rowid[$i] = $pr->rowid;
							$all_prime_montant[$i] = $cond_prime->forfait;

						}else{
							if(($cond_prime->pourcentage*$salaire_base/100) < $cond_prime->minimum_perception){
								$prime_rowid[$i] = $pr->rowid;
								$all_prime_montant[$i] = $cond_prime->minimum_perception;

							}else{
								$prime_rowid[$i] = $pr->rowid;
								$all_prime_montant[$i] = $cond_prime->pourcentage*$salaire_base/100;


							}
						}
					}
						$j = $num;
				}
		$j++;
	}
}
	}
}
/*
for ($i=0; $i < count($prime_rowid); $i++) {
	print "<br>".$prime_rowid[$i]." = ".$all_prime_montant[$i];

}*/
	return array_combine($prime_rowid, $all_prime_montant);

}



function verification_contrat_salarie($db, $fk_salarie){
	$sql_contrat = "SELECT * FROM ".MAIN_DB_PREFIX."salarie_contrat WHERE fk_salarie=".$fk_salarie." AND active=1";
	$res_contrat = $db->query($sql_contrat);
			if($res_contrat){
				$num = $db->num_rows($res_contrat);
				if($num > 0){
					$obj_contrat = $db->fetch_object($res_contrat);
					if(!empty($obj_contrat->date_fin)){
						$date = $obj_contrat->date_fin;
						$annee = $date[0].''.$date[1].''.$date[2].''.$date[3];
						$mois = $date[5].''.$date[6];
						if($annee < date("Y")){
							print "<mark>Le contrat de ce salarié est expiré</mark>".info_admin("Date fin : ".$obj_contrat->date_fin, 1);
						}else if($annee == date("Y")){
							if($mois < date("m"))
								print "<mark>Le contrat de ce salarié est expiré</mark>".info_admin("Date fin : ".$obj_contrat->date_fin, 1);
							elseif($mois > date("m"))
								print "<mark>Le contrat de ce salarié expire dans ".($mois - (int)date("m"))." mois</mark>".info_admin("Date fin : ".$obj_contrat->date_fin, 1);
							else print "<mark>Le contrat de ce salarié expire dans ce mois</mark>".info_admin("Date fin : ".$obj_contrat->date_fin, 1);
						}
					}
				}else print "<mark>Ce salarié n'a pas de contrat</mark>";

			}else print "<mark>Ce salarié n'a pas de contrat</mark>";
}


/**
 *	Get title line of an array
 *
 *	@param	string	$name        		Translation key of field to show or complete HTML string to show
 *	@param	int		$thead		 		0=To use with standard table format, 1=To use inside <thead><tr>, 2=To use with <div>
 *	@param	string	$file        		Url used when we click on sort picto
 *	@param	string	$field       		Field to use for new sorting. Empty if this field is not sortable. Example "t.abc" or "t.abc,t.def"
 *	@param	string	$begin       		("" by defaut)
 *	@param	string	$moreparam   		Add more parameters on sort url links ("" by default)
 *	@param  string	$moreattrib  		Add more attributes on th ("" by defaut, example: 'align="center"'). To add more css class, use param $prefix.
 *	@param  string	$sortfield   		Current field used to sort (Ex: 'd.datep,d.id')
 *	@param  string	$sortorder   		Current sort order (Ex: 'asc,desc')
 *  @param	string	$prefix		 		Prefix for css. Use space after prefix to add your own CSS tag, for example 'mycss '.
 *  @param	string	$disablesortlink	1=Disable sort link
 *  @param	string	$tooltip	 		Tooltip
 *  @param	string	$forcenowrapcolumntitle		No need for use 'wrapcolumntitle' css style
 *	@return	string
 */
function affiche_long_texte($image="", $name, $thead, $file, $field = "", $begin = "", $moreparam = "", $moreattrib = "", $sortfield = "", $sortorder = "", $prefix = "", $disablesortlink = 0, $tooltip = "", $forcenowrapcolumntitle = 0)
{
	global $conf, $langs, $form;
	//print "$name, $file, $field, $begin, $options, $moreattrib, $sortfield, $sortorder<br>\n";

	if ($moreattrib == 'class="right"') {
		$prefix .= 'right '; // For backward compatibility
	}

	$sortorder = strtoupper($sortorder);
	$out = '';
	$sortimg = '';

	$tag = 'td';
	if ($thead == 2) {
		$tag = 'div';
	}

	$tmpsortfield = explode(',', $sortfield);
	$sortfield1 = trim($tmpsortfield[0]); // If $sortfield is 'd.datep,d.id', it becomes 'd.datep'
	$tmpfield = explode(',', $field);
	$field1 = trim($tmpfield[0]); // If $field is 'd.datep,d.id', it becomes 'd.datep'

	if (empty($conf->global->MAIN_DISABLE_WRAPPING_ON_COLUMN_TITLE) && empty($forcenowrapcolumntitle)) {
		$prefix = 'wrapcolumntitle '.$prefix;
	}

	//var_dump('field='.$field.' field1='.$field1.' sortfield='.$sortfield.' sortfield1='.$sortfield1);
	// If field is used as sort criteria we use a specific css class liste_titre_sel
	// Example if (sortfield,field)=("nom","xxx.nom") or (sortfield,field)=("nom","nom")
	$liste_titre = 'liste_titre';
	if ($field1 && ($sortfield1 == $field1 || $sortfield1 == preg_replace("/^[^\.]+\./", "", $field1))) {
		$liste_titre = 'liste_titre_sel';
	}

	$out .= '<'.$tag.' class="'.$prefix.$liste_titre.'" '.$moreattrib;
	//$out .= (($field && empty($conf->global->MAIN_DISABLE_WRAPPING_ON_COLUMN_TITLE) && preg_match('/^[a-zA-Z_0-9\s\.\-:&;]*$/', $name)) ? ' title="'.dol_escape_htmltag($langs->trans($name)).'"' : '');
	$out .= ($name && empty($conf->global->MAIN_DISABLE_WRAPPING_ON_COLUMN_TITLE) && empty($forcenowrapcolumntitle) && !dol_textishtml($name)) ? ' title="'.dol_escape_htmltag($langs->trans($name)).'"' : '';
	$out .= '>';

	if (empty($thead) && $field && empty($disablesortlink)) {    // If this is a sort field
		$options = preg_replace('/sortfield=([a-zA-Z0-9,\s\.]+)/i', '', (is_scalar($moreparam) ? $moreparam : ''));
		$options = preg_replace('/sortorder=([a-zA-Z0-9,\s\.]+)/i', '', $options);
		$options = preg_replace('/&+/i', '&', $options);
		if (!preg_match('/^&/', $options)) {
			$options = '&'.$options;
		}

		$sortordertouseinlink = '';
		if ($field1 != $sortfield1) { // We are on another field than current sorted field
			if (preg_match('/^DESC/i', $sortorder)) {
				$sortordertouseinlink .= str_repeat('desc,', count(explode(',', $field)));
			} else // We reverse the var $sortordertouseinlink
			{
				$sortordertouseinlink .= str_repeat('asc,', count(explode(',', $field)));
			}
		} else // We are on field that is the first current sorting criteria
		{
			if (preg_match('/^ASC/i', $sortorder)) {	// We reverse the var $sortordertouseinlink
				$sortordertouseinlink .= str_repeat('desc,', count(explode(',', $field)));
			} else {
				$sortordertouseinlink .= str_repeat('asc,', count(explode(',', $field)));
			}
		}
		$sortordertouseinlink = preg_replace('/,$/', '', $sortordertouseinlink);
		$out .= $image." ";
		if(!empty($file)){
			$out .=' <a class="reposition" href="'.$file.'"';
			//$out .= (empty($conf->global->MAIN_DISABLE_WRAPPING_ON_COLUMN_TITLE) ? ' title="'.dol_escape_htmltag($langs->trans($name)).'"' : '');
			$out .= '>';
		}
	}
	if ($tooltip) {
		// You can also use 'TranslationString:keyfortooltiponlick' for a tooltip on click.
		$tmptooltip = explode(':', $tooltip);
		$out .= $form->textwithpicto($langs->trans($name), $langs->trans($tmptooltip[0]), 1, 'help', '', 0, 3, (empty($tmptooltip[1]) ? '' : 'extra_'.str_replace('.', '_', $field).'_'.$tmptooltip[1]));
	} else {
		$out .= $langs->trans($name);
	}

	if (empty($thead) && $field && empty($disablesortlink)) {    // If this is a sort field
		$out .= '</a>';
	}

	if (empty($thead) && $field) {    // If this is a sort field
		$options = preg_replace('/sortfield=([a-zA-Z0-9,\s\.]+)/i', '', (is_scalar($moreparam) ? $moreparam : ''));
		$options = preg_replace('/sortorder=([a-zA-Z0-9,\s\.]+)/i', '', $options);
		$options = preg_replace('/&+/i', '&', $options);
		if (!preg_match('/^&/', $options)) {
			$options = '&'.$options;
		}

		if (!$sortorder || $field1 != $sortfield1) {
			//$out.= '<a href="'.$file.'?sortfield='.$field.'&sortorder=asc&begin='.$begin.$options.'">'.img_down("A-Z",0).'</a>';
			//$out.= '<a href="'.$file.'?sortfield='.$field.'&sortorder=desc&begin='.$begin.$options.'">'.img_up("Z-A",0).'</a>';
		} else {
			if (preg_match('/^DESC/', $sortorder)) {
				//$out.= '<a href="'.$file.'?sortfield='.$field.'&sortorder=asc&begin='.$begin.$options.'">'.img_down("A-Z",0).'</a>';
				//$out.= '<a href="'.$file.'?sortfield='.$field.'&sortorder=desc&begin='.$begin.$options.'">'.img_up("Z-A",1).'</a>';
				$sortimg .= '<span class="nowrap">'.img_up("Z-A", 0, 'paddingleft').'</span>';
			}
			if (preg_match('/^ASC/', $sortorder)) {
				//$out.= '<a href="'.$file.'?sortfield='.$field.'&sortorder=asc&begin='.$begin.$options.'">'.img_down("A-Z",1).'</a>';
				//$out.= '<a href="'.$file.'?sortfield='.$field.'&sortorder=desc&begin='.$begin.$options.'">'.img_up("Z-A",0).'</a>';
				$sortimg .= '<span class="nowrap">'.img_down("A-Z", 0, 'paddingleft').'</span>';
			}
		}
	}

	$out .= $sortimg;

	$out .= '</'.$tag.'>';

	return $out;
}

function verification_contrat_tout_salarie($db){
	$sql_salarie = "SELECT matricule FROM ".MAIN_DB_PREFIX."salarie";
	$res_salarie = $db->query($sql_salarie);
	$nb_contrat_expire = 0;
	$nb_contrat_proche_expire = 0;
	$nb_salarie_pas_contrat = 0;
	if($res_salarie){
		$nb_salarie = $db->num_rows($res_salarie);
		$i = 0;
		while($i < $nb_salarie){
			$obj_salarie = $db->fetch_object($res_salarie);
			$sql_contrat = "SELECT date_limit FROM ".MAIN_DB_PREFIX."salarie_contrat WHERE matricule='".$obj_salarie->matricule."' AND fk_type_contrat <> 2";
			$res_contrat = $db->query($sql_contrat);
					if($res_contrat){
						$num = $db->num_rows($res_contrat);
						if($num > 0){
							$obj_contrat = $db->fetch_object($res_contrat);
							$date = $obj_contrat->date_limit;
							$annee = $date[1].''.$date[2].''.$date[1].''.$date[1];
							$mois = $date[5].''.$date[6];

							if($annee < date("Y")){
								$nb_contrat_expire ++;
							}else if($annee == date("Y")){
								if($mois < date("m")){
									$nb_contrat_expire ++;
								}elseif($mois > date("m")){
									$nb_contrat_proche_expire ++;
								}else $nb_contrat_proche_expire;
							}
						}else $nb_salarie_pas_contrat ++;
				}else $nb_salarie_pas_contrat ++;
			$i ++;
		}
	}
	$tab_contrat[0] = $nb_salarie_pas_contrat;
	$tab_contrat[1] = $nb_contrat_expire;
	$tab_contrat[2] = $nb_contrat_proche_expire;

	return $tab_contrat;
}

//Reglage de "Retour liste" et les flèches <> (preview et next)
function societe_preview_next($db, $id_societe, $obj_soc){
	global $user;
//preparation de la sociéte suivante et precedente de la liste
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
	
$sql_prev = "SELECT sc.rowid, sc.nom, sce.fk_object, sce.conv FROM ".MAIN_DB_PREFIX."societe as sc";
$sql_prev .= " LEFT JOIN ".MAIN_DB_PREFIX."societe_extrafields as sce ON sc.rowid=sce.fk_object WHERE sce.grp=1 AND sc.rowid<".$id_societe;
if($user->id != 1)
    $sql_prev .= " AND sc.rowid IN ".$array_id_soc;

$sql_prev .= " ORDER BY sc.rowid DESC";

$soc_res_prev = $db->query($sql_prev);//= $db->query($covSql);
$nom_societe_prev = "";
if($soc_res_prev)
	if($db->num_rows($soc_res_prev)>0){
		$obj_prev = $db->fetch_object($soc_res_prev);
		$obj_soc->ref_previous = './liste_personnelle.php?mainmenu=paiementsalaire&leftmenu=societe&id_societe='.$obj_prev->rowid.'&id_convention='.$obj_prev->conv;
		$nom_societe_prev = $obj_prev->nom;

	}

$sql_next = "SELECT sc.rowid, sc.nom, sce.fk_object, sce.conv FROM ".MAIN_DB_PREFIX."societe as sc";
$sql_next .= " LEFT JOIN ".MAIN_DB_PREFIX."societe_extrafields as sce ON sc.rowid=sce.fk_object WHERE sce.grp=1 AND sc.rowid>".$id_societe;
if($user->id != 1)
    $sql_next .= " AND sc.rowid IN ".$array_id_soc;

$sql_next .= " ORDER BY sc.rowid";

$soc_res_next = $db->query($sql_next);//= $db->query($covSql);
$nom_societe_next = "";
if($soc_res_next)
	if($db->num_rows($soc_res_next)>0){
		$obj_next = $db->fetch_object($soc_res_next);
		$obj_soc->ref_next = './liste_personnelle.php?mainmenu=paiementsalaire&leftmenu=societe&id_societe='.$obj_next->rowid.'&id_convention='.$obj_next->conv;
		$nom_societe_next = $obj_next->nom;
	}
$obj_soc->retour = '../liste_societe.php?leftmenu=societe';
$obj_soc->nom_precedent = $nom_societe_prev;
$obj_soc->nom_suivant = $nom_societe_next;

}

/*Verification de l'artile 24 de l'its */
function article24_its($db, $id_societe, $annee_rechercher){

$sql = "SELECT nom FROM ".MAIN_DB_PREFIX."societe Where rowid=".$id_societe;
	$result1 = $db->query($sql);
  $sc = $db->fetch_object($result1);

  $somme_brut = 0;
  $somme_brut_imposable = 0;
  $somme_its = 0;
  $obj_array = array();
	$sql_verif_parent = "SELECT DISTINCT fk_salarie FROM ".MAIN_DB_PREFIX."bulletin WHERE annee=".$annee_rechercher." AND fk_societe=".$id_societe;
	$res_verif_parent = $db->query($sql_verif_parent);
	if($res_verif_parent){
		$num = $db->num_rows($res_verif_parent);
		$a = 0;
		while ($a < $num) {
			$obj_verif_parent = $db->fetch_object($res_verif_parent);
			$sql_verif = "SELECT count(salaire_brut) as brut, count(salaire_brut_imposable) as brut_imposable FROM ".MAIN_DB_PREFIX."bulletin WHERE fk_salarie=".$obj_verif_parent->fk_salarie;
			$res_verif = $db->query($sql_verif);
			if($res_verif){
				$obj_verif = $db->fetch_object($res_verif);
				$somme_brut = $obj_verif->brut;
				$somme_brut_imposable = $obj_verif->brut_imposable;
			}

			$sql_bul = "SELECT rowid, matricule, nom, prenom, situation_familiale, nombre_enfant, nombre_enfant_hand, sexe FROM ".MAIN_DB_PREFIX."bulletin WHERE fk_salarie=".$obj_verif_parent->fk_salarie;
			$res_bul = $db->query($sql_bul);
			if($res_bul){
				$num_bul = $db->num_rows($res_bul);
				$j = 0;
				while($j < $num_bul){
					$obj_bul = $db->fetch_object($res_bul);
					$sql_taxe = "SELECT montant FROM ".MAIN_DB_PREFIX."bulletin_taxe WHERE fk_bulletin=".$obj_bul->rowid;
					$res_taxe = $db->query($sql_taxe);
					if($res_taxe){
						$obj_taxe = $db->fetch_object($res_taxe);
						$somme_its += $obj_taxe->montant;
					}
					$j ++;
				}

				$obj_array[$a]["matricule"] = $obj_bul->matricule;
				$obj_array[$a]["nom"] = $obj_bul->nom;
				$obj_array[$a]["prenom"] = $obj_bul->prenom;
				$obj_array[$a]["sexe"] = $obj_bul->sexe;
				$obj_array[$a]["situation_familiale"] = $obj_bul->situation_familiale;
				$obj_array[$a]["nombre_enfant"] = $obj_bul->nombre_enfant;
				$obj_array[$a]["nombre_enfant_hand"] = $obj_bul->nombre_enfant_hand;
				$obj_array[$a]["somme_brut"] = $somme_brut;
				$obj_array[$a]["somme_brut_imposable"] = $somme_brut_imposable;
				$obj_array[$a]["somme_its"] = $somme_its;
				$its_annuel = its_salarie_annuel($db, $somme_brut, $obj_bul->situation_familiale, $obj_bul->nombre_enfant, $obj_bul->nombre_enfant_hand);
				$obj_array[$a]["its_annuelle"] = $its_annuel;
				$obj_array[$a]["differece"] = $its_annuel - $somme_its;

			}
			$a ++;
		}
	}
	return $obj_array;
}

function salarie_nb_jour($db, $id_societe){

	$sql = "SELECT count(u.rowid) FROM ".MAIN_DB_PREFIX."user as u";
		$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."salarie as sal ON u.rowid=sal.fk_user";
		$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."user_extrafields as ue ON u.rowid=ue.fk_object";
		$sql .= ' LEFT JOIN '.MAIN_DB_PREFIX.'societe as soc ON soc.rowid=ue.egp';
		$sql .= ' LEFT JOIN '.MAIN_DB_PREFIX.'societe_extrafields as sce ON soc.rowid=sce.fk_object';
		$sql .= ' WHERE sce.grp=1';

		$sql .= " ORDER BY u.rowid";
		$result1 = $db->query($sql);
		$num_total = $db->num_rows($result1);
	$annee = date('Y');
	//$mois = date('m');
	$mois = date('m');
	$salSql = "SELECT * FROM ".MAIN_DB_PREFIX."salarie_nombre_jour_travaille where fk_societe=".$id_societe." AND annee=".$annee." AND mois=".$mois;
	$result2 = $db->query($salSql);
	$num = $db->num_rows($result2);
	$jour = cal_days_in_month(CAL_GREGORIAN, $mois, $annee);
	if($num < $num_total){
		$salSql = "DELETE FROM ".MAIN_DB_PREFIX."salarie_nombre_jour_travaille where fk_societe=".$id_societe." AND annee=".$annee." AND mois=".$mois;
		$result3 = $db->query($salSql);
		$sql = "SELECT u.rowid, sal.rowid as id_salarie, ue.fk_object, ue.egp, soc.rowid as id_societe, soc.nom, sce.fk_object, sce.conv FROM ".MAIN_DB_PREFIX."user as u";
		$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."salarie as sal ON u.rowid=sal.fk_user";
		$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."user_extrafields as ue ON u.rowid=ue.fk_object";
		$sql .= ' LEFT JOIN '.MAIN_DB_PREFIX.'societe as soc ON soc.rowid=ue.egp';
		$sql .= ' LEFT JOIN '.MAIN_DB_PREFIX.'societe_extrafields as sce ON soc.rowid=sce.fk_object';
		$sql .= ' WHERE sce.grp=1';

		$sql .= " ORDER BY u.rowid";
		$result = $db->query($sql);
		if($result){

			$num = $db->num_rows($result);
			if($num > 0){

				$a = 0;
				while ($a < $num) {
					$salarie = $db->fetch_object($result);
						if($salarie->id_salarie){
							if(!$id_societe)
								$id_societe = $salarie->id_societe;
							$sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'salarie_nombre_jour_travaille (fk_societe, fk_salarie, annee, mois, jour)';
							$sql_insert .= ' VALUES('.$id_societe.','.$salarie->id_salarie.','.$annee.','.$mois.','.$jour.')';
							$db->query($sql_insert);
							
						}
					$a ++;
				}
			}

		}
	}
}

/**entete */
function pdf_pagehead_bonus(&$pdf, $onglet_salarie){

	   global $mysoc,$conf, $db, $fk_salarie, $societe_Salarie, $fk_user, $mois, $annee, $id_accord_etab, $id_convention, $info_soc;

	   $bulletin_sql = "SELECT * FROM ".MAIN_DB_PREFIX."bulletin_bonus WHERE fk_salarie=".$fk_salarie." AND annee='".$annee."' AND mois='".$mois."'";
		   $rest_bulletin = $db->query($bulletin_sql);//= $db->query($covSql);
		   $bulletin_obj = $db->fetch_object($rest_bulletin);
		   $salaire_base = $bulletin_obj->salaire_base;


		   //Entête Droit information sur le bulletin
		   $mois_tab = array(" janvier "," février "," mars "," avril "," mai "," juin "," juillet "," août "," septembre "," octobre "," novembre "," décembre ");
		   $mois_courant = $mois ? : (int) date("m");
		   /*$annee_courant = date("Y");

		   $entete_droit = " du".$mois[$mois_courant-1]."".$annee_courant;*/
		   $y = $pdf->GetY()+8;
		   $pdf->SetY($y);
		   $debut = DOL_DOCUMENT_ROOT;
		$tab = explode("/",$debut);
		$logodir = $conf->mycompany->dir_output;
		$logo_server = $logodir.'/logos/'.$mysoc->logo;
		if($info_soc->societe_mere == 0){
			$logo_1 = $tab[0].'/'.$tab[1].'/'.$tab[2].'/'.$tab[3].($tab[4]?'/'.$tab[4]:'').'/documents/societe/'.$bulletin_obj->fk_societe.'/logos/'.$bulletin_obj->logo_societe;
        	$logo_2 = $tab[0].'/'.$tab[1].'/dolibarr_documents/societe/'.$bulletin_obj->fk_societe.'/logos/'.($bulletin_obj->logo_societe?$bulletin_obj->logo_societe:"vide.png");

			if(is_readable($logo_2)){
				///home/dolites/public_html
				$pdf->Image($logo_2,20,12, 40,19);
			}else if(is_readable($logo_1)){
				$pdf->Image($logo_1,20,12, 40,19);
	
			}else{
	
				
				$pdf->SetFont('Helvetica','B',16);
				$pdf->SetY(12);
				$pdf->SetX(20);
				$pdf->MultiCell(40,19,utf8_decode("Logo"),0,'C');
			}

			/*$img = '../config/logo_societe/'.$bulletin_obj->fk_societe;
			if(file_exists($img.'.png')){
				$img .= '.png';
			}elseif(file_exists($img.'.jpg')){
				$img .= '.jpg';
			}else{
				$img .= '.jpeg';
			}

			if(is_readable($img)){
				$pdf->Image($logo_server,20,12, 40,19);
			}else{
				$pdf->SetFont('Helvetica','B',16);
				$pdf->SetY(12);
				$pdf->SetX(20);
				$pdf->MultiCell(40,19,utf8_decode("Logo"),0,'C');
			}*/

		}else{
				$logodir = $conf->mycompany->dir_output;
				if (!empty($conf->mycompany->multidir_output[$object->entity])) {
					$logodir = $conf->mycompany->multidir_output[$object->entity];
				}
				if (empty($conf->global->MAIN_PDF_USE_LARGE_LOGO)) {
					$logo = $logodir.'/logos/thumbs/'.$mysoc->logo_small;
				} else {
					$logo = $logodir.'/logos/'.$mysoc->logo;
				}
			
			if(is_readable($logo)){
				///home/dolites/public_html
				$pdf->Image($logo,20,12, 40,19);
			}else{
	
				
				$pdf->SetFont('Helvetica','B',16);
				$pdf->SetY(12);
				$pdf->SetX(20);
				$pdf->MultiCell(40,19,utf8_decode("Logo"),0,'C');
			}
		}

		   //Pour vérifier la prémière le logo enregistrer dans le bulletin en premier
		   /*$bulletin_soc = "SELECT * FROM ".MAIN_DB_PREFIX."societe WHERE rowid=".$bulletin_obj->fk_societe;
			$rest_bulletin_soc = $db->query($bulletin_soc);//= $db->query($covSql);
			$societe_Salarie = $db->fetch_object($rest_bulletin_soc);


			$logo_server = $tab[0].'/'.$tab[1].'/'.$tab[2].'/'.$tab[3].'/documents/societe/'.$societe_Salarie->rowid.'/logos/'.$rest_bulletin->logo_societe;
			$logo_local_pc = $tab[0].'/'.$tab[1].'/dolibarr_documents/societe/'.$societe_Salarie->rowid.'/logos/'.($societe_Salarie->logo?$societe_Salarie->logo:"vide.png");
		if(is_readable($logo_server)){
			   ///home/dolites/public_html
			   $pdf->Image($logo_server,20,12, 40,19);
		   }elseif(is_readable($logo_local_pc)){
			   $pdf->Image($logo_local_pc,20,12, 40,19);

		   }else{
					$img = '../config/logo_societe/'.$bulletin_obj->fk_societe;
				if(file_exists($img.'.png')){
					$img .= '.png';
				}elseif(file_exists($img.'.jpg')){
					$img .= '.jpg';
				}else{
					$img .= '.jpeg';
				}

				if(is_readable($img)){
					$logo_server = $img;
					$pdf->Image($logo_server,20,12, 40,19);

				}else{
					$pdf->SetFont('Helvetica','B',16);
					$pdf->SetY(12);
					$pdf->SetX(20);
					$pdf->MultiCell(40,19,utf8_decode($logo_societe->nom_societe),0,'C');
		   		}
			}*/
		   $date = "Complement de Paie :".$mois_tab[$mois_courant-1]." ".($annee ? : date("Y"));
		   $pdf->SetTextColor(0, 0, 60);
		   $pdf->SetFont('Helvetica','B',16);

		   $x = 104;
		   $pdf->SetY($y);
		   $pdf->SetX($x);
		   $pdf->MultiCell(96,5,utf8_decode($date),0,'R');

		   /*$pdf->SetFont('Helvetica','',9);
		   $pdf->SetX($pdf->GetX());
		   $pdf->Cell(18,7,utf8_decode($entete_droit),0,0,'C');*/

		   $y += 5;
		   $pdf->SetTextColor(0, 0, 70);
		   $pdf->SetFont('Helvetica','',8);
		   $x = 175;
		   $pdf->SetY($y);
		   $pdf->SetX($x);

		   $du = "01-".$mois."-".$annee;
		   $au = cal_days_in_month(CAL_GREGORIAN, $mois, $annee);
		   $pdf->MultiCell(24,3,utf8_decode("du : ".$du),0,'R');

		   $y += 3;
		   $pdf->SetTextColor(0, 0, 70);
		   $pdf->SetFont('Helvetica','',8);
		   $x = 175;
		   $pdf->SetY($y);
		   $pdf->SetX($x);
		   $pdf->MultiCell(24,3,utf8_decode("au : ".$au."-".$mois."-".$annee),0,'R');

		   $y += 4;
		   $pdf->SetTextColor(0, 0, 70);
		   $pdf->SetFont('Helvetica','B',8);
			 $x = 150;
			 $pdf->SetY($y);
			 $pdf->SetX($x);
			 $pdf->MultiCell(49,3,utf8_decode("Société : ".$bulletin_obj->nom_societe),0,'R');

		   $pdf->SetY(37);
		   //--------------------------------------------------------------
		   //Rectangle Employé
		   $y = $pdf->GetY()+2;
		   //$pdf->SetFillColor(200, 200, 200);

		  $pdf->SetX(12);
		  $pdf->Cell(61,40, "",0,0,'','true');

		  $pdf->SetX(73);
		  $pdf->Cell(65,40, "",0,0,'','true');

		  $pdf->SetX(138);
		  $pdf->MultiCell(60,40, "",0,'','true');
		  //--------------------------------------------------------------------------------

		   $pdf->SetTextColor(0, 0, 70);
		   $pdf->SetFont('Helvetica','B',8);


		   $pdf->SetX(13);
		   $pdf->SetY($y);
		   $y_align = $y;
		   $pdf->MultiCell(1,1,"",0,'C');

		   $pdf->SetLeftMargin(13);
		   $pdf->MultiCell(60,4, utf8_decode("Matricule : ".$bulletin_obj->matricule),0,'');


		   $y = $pdf->GetY()+1;
		   $pdf->SetY($y);
		   $pdf->MultiCell(60,4, utf8_decode("Prénom : ".$bulletin_obj->prenom),0,'');


		   $y = $pdf->GetY()+1;
		   $pdf->SetY($y);
		   $pdf->MultiCell(60,4, utf8_decode("Nom : ".$bulletin_obj->nom),0,'');


		   $y = $pdf->GetY()+1;
		   $pdf->SetY($y);

		   $pdf->MultiCell(60,4, utf8_decode("Sexe : ".$bulletin_obj->sexe),0,'');

		   $y = $pdf->GetY()+1;
	   $pdf->SetY($y);
	   $pdf->MultiCell(60,4, utf8_decode("Tel : ".$bulletin_obj->tel),0,'');

	   $y = $pdf->GetY()+1;
	   $pdf->SetY($y);
	   $pdf->MultiCell(60,4, utf8_decode("Pays : ".$bulletin_obj->pays),0,'');


	   $y = $pdf->GetY()+1;
	   $pdf->SetY($y);
	   $pdf->MultiCell(60,4, utf8_decode("Ville : ".$bulletin_obj->ville),0,'');

	   //******************************************************************************************** */
	   // Adresse et Contact
	   $pdf->SetY($y_align+1);
	   $pdf->SetLeftMargin(73);


	   $y = $pdf->GetY()+1;



	   $pdf->MultiCell(60,4, utf8_decode("Situation familiale : ".$bulletin_obj->situation_familiale),0,'');


	   $y = $pdf->GetY()+1;
	   $pdf->SetY($y);
	   $pdf->MultiCell(60,4, utf8_decode("Nombre enfant : ".$bulletin_obj->nombre_enfant."/".$bulletin_obj->nombre_enfant_hand),0,'');

	   $y = $pdf->GetY()+1;
		$pdf->SetY($y);
      $pdf->SetFillColor(245, 245, 245);
      $pdf->MultiCell(65,4, utf8_decode("I.N.P.S : ".$bulletin_obj->inps),0,'L');

	  $y = $pdf->GetY()+1;
      $pdf->SetY($y);
      $pdf->SetFillColor(245, 245, 245);
      $pdf->MultiCell(65,4, utf8_decode("AMO : ".$bulletin_obj->amo),0,'L');

	   $y_apres_entete = $y = $pdf->GetY();




	   //********************************************************************************************** */
	   //Information sur l'emploi
	   $salaire_base = 0;
		   $pdf->SetY($y_align+1);

		   $categ = $bulletin_obj->categorie;
		   if(!empty($bulletin_obj->echelon))
			   $categ .= '==>'.$bulletin_obj->echelon;
		   $pdf->SetLeftMargin(138);
		   $pdf->MultiCell(60,4, utf8_decode("Categorie : ".$categ),0,'');
		   $salaire_base = $bulletin_obj->salaire_base;

		   $y = $pdf->GetY()+1;
		   $pdf->SetY($y);
		   $pdf->MultiCell(60,4, utf8_decode("Niveau salarié : ".$bulletin_obj->type_salarie),0,'');

		   $y = $pdf->GetY()+1;
		   $pdf->SetY($y);
		   $pdf->MultiCell(60,4, utf8_decode("Type de contrat : ".$bulletin_obj->contrat),0,'');


		   $y = $pdf->GetY()+1;
		   $pdf->SetY($y);
		   $pdf->MultiCell(60,4, utf8_decode("Fonction : ".$bulletin_obj->fonction),0,'');

		   $y = $pdf->GetY()+1;
		   $pdf->SetY($y);
		   $pdf->MultiCell($pdf->GetX(),4, utf8_decode("Salaire de base : 0"),0,'');

		   $y = $pdf->GetY()+1;
		   $pdf->SetY($y);
		   $pdf->MultiCell($pdf->GetX(),4, utf8_decode("Date d'embauche : ".$bulletin_obj->date_embauche),0,'');

   }

