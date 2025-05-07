<?php
/* Copyright (C) 2004-2017 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2022 SuperAdmin
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
 * \file    paiementsalaire/admin/about.php
 * \ingroup paiementsalaire
 * \brief   About page of module PaiementSalaire.
 */

// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) {
	$res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
}
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME']; $tmp2 = realpath(__FILE__); $i = strlen($tmp) - 1; $j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
	$i--; $j--;
}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1))."/main.inc.php")) {
	$res = @include substr($tmp, 0, ($i + 1))."/main.inc.php";
}
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php")) {
	$res = @include dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php";
}
// Try main.inc.php using relative path
if (!$res && file_exists("../../main.inc.php")) {
	$res = @include "../../main.inc.php";
}
if (!$res && file_exists("../../../main.inc.php")) {
	$res = @include "../../../main.inc.php";
}
if (!$res) {
	die("Include of main fails");
}

// Libraries
require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
require_once DOL_DOCUMENT_ROOT.'/custom/paiementsalaire/lib/paiementsalaire.lib.php';

// Translations
$langs->loadLangs(array("errors", "admin", "paiementsalaire@paiementsalaire"));

// Access control
if (!$user->admin) {
	accessforbidden();
}

// Parameters
$action = GETPOST('action', 'aZ09');
$backtopage = GETPOST('backtopage', 'alpha');


/*
 * Actions
 */

// None


/*
 * View
 */

$form = new Form($db);

$help_url = '';
$page_name = "A propos";

llxHeader('', $langs->trans($page_name), $help_url);

print load_fiche_titre($langs->trans($page_name), "", 'title_setup');

// Configuration header
//$head = paiementsalaireAdminPrepareHead();
//print dol_get_fiche_head($head, 'Menu2', $langs->trans($page_name), 0, 'paiementsalaire@paiementsalaire');

dol_include_once('/paiementsalaire/core/modules/modPaiementSalaire.class.php');
$tmpmodule = new modPaiementSalaire($db);
//print $tmpmodule->getDescLong();


print "<h1>PAIEMENTSALAIRE POUR <a href='https://www.dolibarr.org/' target='_blank'>DOLIBARR ERP CRM</a></h1>";

print "<h2>Description</h2>";
print "Le Module Salaire | Paie est un module conçu pour gérer la <b>Paie</b> des employés d'une ou plusieurs sociétés Maliennes.<br><br>";
print "Ce module de paie gère aussi : les taxes, les contisations toutes en respectant les conventions établies par les secteurs d'activités<br>ou même les accord d'établissement mise en place par une société.";
print "<h2>Licences</h2>";
print "licence est payante";

print "<h2>Versions</h2>";
print "<table class='tagtable liste'>";
//Les en-têtes
print "<tr class='liste_titre'>";
print "<td>Les versions</td>";
print "<td>Status</td>";
print "<td>Change log</td>";
print "<td>Compatibilité avec Dolibarr";
print "<td>Lien de téléchargement</td>";
print "</tr>";

$soc_sql = "SELECT * FROM ".MAIN_DB_PREFIX."version_dolipaie";
$soc_res = $db->query($soc_sql);//= $db->query($covSql);
$num = $db->num_rows($soc_res);
if($soc_res){
	$a = 0;
	while ($a < $num) {
		$obj = $db->fetch_object($soc_res);
		print "<tr>";
		print "<td>".$obj->numero_version."</td>";
		print "<td>".$obj->statut."</td>";
		print "<td>$obj->changelog</td>";
		print "<td>".$obj->compatibilite_dolibarr."</td>";
		print "<td><a href='".$obj->lien_telechargement."'>".$obj->lien_telechargement."</a></td>";
		print "</tr>";
		$a++;
	}
}

print "</table>";

//Les information sur la version active
$soc_sql = "SELECT * FROM ".MAIN_DB_PREFIX."version_dolipaie WHERE active=1";
$soc_res = $db->query($soc_sql);//= $db->query($covSql);
if($soc_res)
	$obj = $db->fetch_object($soc_res);

print "<h2>Version active</h2>";
print "PaiementSalaire : ".$obj->numero_version;

print "<h2>Documentation</h2>";
print "Le guide d'utilisation se trouve <a href='".DOL_URL_ROOT."/paiementsalaire/config/manuel_utilisation.docx' target='_blank'><b>Manuel</b></a><br>";

if($obj->mise_a_jour  == '1.2.0'){
	print "<h2>Téléchargez la nouvelle version : <mark>".$obj->mise_a_jour."</mark></h2>";
	print "Vous pouvez télécharger la nouvelle version de <b>Dolipaie</b> sur la plate-forme d'IBS en vous connectant</a><br>";

	print "<h2>Ajouts, modifications & corrections par rapport à la version <mark>V1.1.0</mark></h2>";
	print "<div>1- Ajouts
        <ul>
            <li class='item1'>Ajout d'une table de gestion des versions</li>
            <li class='item2'>Gestion des version à travers WHMCS</li>
            
        </ul>
    </div>";

	print "<div>2- Modifications
        <ul>
            <li class='item1'>Amelioration de sql dans la page de garde (garde.php) pour l'affichage des information du dernier mois traité</li>
            <li class='item2'>Amelioration de sql dans la page de garde (garde.php) pour l'affichage des information du dernier inps mois traité</li>
            <li class='item3'>ajout de la fonctinnalité : possibilité de mettre un taux particulier pour les cotisations en fonction des sociétés :<br>
				-Modification de la fonction salarie_prestation(..., $id_societe) dans paiementsalaire.lib.php<br>
				-Modification generation_salaire.php<br>
				-Modification de bulletin (no_save ou apperçu du bulletin)</li>
            
        </ul>
    </div>";

	print "<div>3- Correction
		<ul>
			<li id='identifiant1'>Correction de bug dans société_paie.php(onglet salaires dans société)</li>
			<li id='identifiant2'>correction de bug dans bulletin.php(salariés onglet salaire)</li>
			<li id='identifiant3'>Correction de bug complements salaire (on ne plus supprimer un complement après la cloture du mois)</li>
			<li id='identifiant4'>Correction de bug societe_prime.php création de prime exceptionnelle à tous les salariés (société ongle prime, ajout d'une prime exceptionnelle)</li>
			<li id='identifiant5'>Correction de bug valeur du type de contrat sur bulletin</li>
			
		</ul>
	</div>";

}elseif($obj->mise_a_jour  == '1.3.0'){
	print "<h2>Téléchargez la nouvelle version : <mark>".$obj->mise_a_jour."</mark></h2>";
	print "Vous pouvez télécharger la nouvelle version de <b>Dolipaie</b> sur la plate-forme d'IBS en vous connectant</a><br>";

	print "<h2>Ajouts, modifications & corrections par rapport à la version <mark>V1.1.0</mark></h2>";
	print "<div>1- Ajouts
        <ul>
            <li class='item1'>1- Ajout de la fonctionnalité d'export de 'Récapitulatif' d'un salarié en fonction de l'année (Onglet export)</li>
            <li class='item2'>2- Ajout de la flexibilité du nombre de colonne à afficher dans liste_personelle.php (catégorie, fonction, solde congé, ...)</li>
			<li class='item2'>3- Voir ou télécharger les bulletins de tous les salariés (compléménts salaires)</li>
            <li class='item2'>4- Pouvoir modifier individuellement les compléments salaires</li>

            
        </ul>
    </div>";

	print "<div>2- Modifications
        <ul>
            <li class='item1'>1- Sur le fichier d’exportation du complément Salaire le montant coût total n’est pas affiché.</li>
            <li class='item2'>2- Mentionné dans le fichier d’exportation le nombre d’heures sup suivi du montant d’heures sup.</li>
            
        </ul>
    </div>";

	print "<div>3- Correction
		<ul>
			<li id='identifiant1'>1- correction de nombre enfant handicapé dans validation société</li>
			<li id='identifiant2'>2- correction de tout bulletin salarié (pourcentage des primes exceptionnelle)</li>
			<li id='identifiant3'>3- correction de l'affichage des montants et pourcentage dans bulletin et tout_bulletin_salarie</li>
			<li id='identifiant4'>4- Possibilité de voir l'onglet salaire même si tous les salarié sont archivés</li>
			<li id='identifiant5'>5- Exportation commune ou par groupe de société.</li>
			<li id='identifiant5'>6- Les primes se répètent dans le fichier d’exportation.</li>
			<li id='identifiant5'>7- Les heures sup se répètent dans le fichier d'Exportation</li>

			
		</ul>
	</div>";
}else{
	print "<h2>Aucune mise à jour disponible!</h2>";
}
// Page end
print dol_get_fiche_end();
llxFooter();
$db->close();
