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
print "PaiementSalaire : 1.0.0";

print "<h2>Documentation</h2>";
print "Le guide d'utilisation se trouve <a href='".DOL_URL_ROOT."/paiementsalaire/config/manuel_utilisation.docx' target='_blank'><b>Manuel</b></a><br>";
// Page end
print dol_get_fiche_end();
llxFooter();
$db->close();
