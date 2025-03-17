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
require_once '../lib/paiementsalaire.lib.php';

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
$page_name = "Liste des types Bulletins";

llxHeader('', $langs->trans($page_name), $help_url);

// Subheader
$linkback = '<a href="'.($backtopage ? $backtopage : DOL_URL_ROOT.'/admin/modules.php?restore_lastsearch_values=1').'">'.$langs->trans("BackToModuleList").'</a>';

print load_fiche_titre($langs->trans($page_name), $linkback, 'title_setup');

// Configuration header
$head = paiementsalaireAdminPrepareHead();
print dol_get_fiche_head($head, 'modele_bulletin', $langs->trans($page_name), 0, 'paiementsalaire@paiementsalaire');
 $message = "";

 $action = GETPOST('action','alpha');
if(empty($action))	
	$action = 'liste';

//print '<span style="float:right;"><a title="Reglages" href="./reglages.php?mainmenu=paiementsalaire&leftmenu=reglage">Retour</a></span><br>';
//les types de bulletins
$sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."modele_bulletin";
$res = $db->query($sql);
if($res){
  $nb = $db->num_rows($res);
  if($nb == 0){
	$sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'modele_bulletin (libelle, commentaire, actif) VALUES ("Base","Le bulletin de base de ce module (Recommandé).",1)';
	$result = $db->query($sql_insert);
  }
  if($nb < 2){
	$sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'modele_bulletin (libelle, commentaire, actif) VALUES ("Moyen","Bulletin ajusté.",0)';
	$result = $db->query($sql_insert);
  }
  if($nb < 3){
	$sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'modele_bulletin (libelle, commentaire, actif) VALUES ("Avancé","Modèle de bulletin avancé & commerciale",0)';
	$result = $db->query($sql_insert);
  }
}

if($action == 'activer'){
    $id_modele_bulletin = GETPOST("id_modele_bulletin", "int");
    $sql = 'UPDATE '.MAIN_DB_PREFIX.'modele_bulletin SET actif=0';
    $result = $db->query($sql);
    
    $sql = 'UPDATE '.MAIN_DB_PREFIX.'modele_bulletin SET actif=1 WHERE rowid='.$id_modele_bulletin;
    $result = $db->query($sql);

    if($result)
        $message = 'Modèle activé avec succès';
    else
        $message = "Un problème est survenu";
    
        $action = 'liste';

}
if($action == "liste"){
    //print_barre_liste("", $page, $_SERVER["PHP_SELF"], "", "", "", "", "", "", 'bill', 0, dolGetButtonTitle("Créer un nouveau type de Banque", '', 'fa fa-plus-circle', './ajout_modele_bulletin.php?mainmenu=paiementsalaire&leftmenu=autre&action=create', '', 1), '', 0, 0, 0, 1);
    print '<table style="width: 100%" class="tagtable liste">';
 print '<tr class="liste_titre">';
 print '<td style="width: 20%; color: darkblue; padding:" align=""><label>Libellé</label></td>';
 print '<td style="width: 70%; color: darkblue; padding:" align=""><label>Description</label></td>';
 print '<td style="width: 10%; color: darkblue; padding:" align=""><label>Activé/Désactivé</label></tr>';
  $modele_bulletin = "SELECT * FROM ".MAIN_DB_PREFIX."modele_bulletin";
	$result_modele_bulletin = $db->query($modele_bulletin);//= $db->query($covSql);

	if($result_modele_bulletin){
		$i = 0;
		$num = $db->num_rows($result_modele_bulletin);
		while ($i < $num){
			$obj_modele_bulletin = $db->fetch_object($result_modele_bulletin);

            print '<tr class="impair">';
            print ''.affiche_long_texte(img_picto("", "statut7_blue", "class='paddingright pictofixedwidth'"), $obj_modele_bulletin->libelle, 0, '', 'nom', '', '', '', '').'</td>';
            print ''.affiche_long_texte('', $obj_modele_bulletin->commentaire, 1, '', '', '', '', '', '').'</td>';
            if($obj_modele_bulletin->actif == 1)
                print '<td align=""><input type="radio" name="drone" id="radio'.$i.'" checked />';
            else
                print '<td align=""><input type="radio" name="drone" onclick="myFunction('.$obj_modele_bulletin->rowid.')" id="radio'.$i.'" />';

            print '</td>';
            print '</tr>';

            $i ++;
			}
            $db->free($result_modele_bulletin);
			
		}else print '<tr><td align="center" colspan="4">Aucun type bulletin disponible</td></tr>';
	
        print'</table>';
        print "<script>
        function myFunction(e){
           if(confirm('Voulez-vous Vraiment activer ce modèle')){
               window.location.href = '".$_SERVER["PHP_SELF"]."?action=activer&id_modele_bulletin='+e;
           
           }else{
               window.location.href = '".$_SERVER["PHP_SELF"]."';           
           }
          }
        
        </script>";

 print'</table>';

}

if(!empty($message))
	print "<script>
	$.jnotify('".$message."', {delay : 5000, fadeSpeed: 500});
	</script>";