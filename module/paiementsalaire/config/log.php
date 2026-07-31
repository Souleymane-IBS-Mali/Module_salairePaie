<?php
/* Copyright (C) 2001-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2013 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2015 Regis Houssin        <regis.houssin@inodbox.com>
 * Copyright (C) 2015-2020 Juanjo Menent        <jmenent@2byte.es>
 * Copyright (C) 2015      Jean-Francois Ferry  <jfefe@aternatik.fr>
 * Copyright (C) 2015      Raphael Doursenaud   <rdoursenaud@gpcsolutions.fr>
 * Copyright (C) 2016      Marcos Garcia        <marcosgdf@gmail.com>
 * Copyright (C) 2019      Nicolas ZABOURI      <info@inovea-conseil.com>
 * Copyright (C) 2020      Tobias Sekan         <tobias.sekan@startmail.com>
 * Copyright (C) 2020      Josep Lluis Amador   <joseplluis@lliuretic.cat>
 * Copyright (C) 2021      Frederic France      <frederic.france@netlogic.fr>
 */

require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/custom/paiementsalaire/lib/paiementsalaire.lib.php';

llxHeader('', 'Paiement | Salaire');
print load_fiche_titre($langs->trans('Liste des importantes actions effectuees'), '', '');

$action = GETPOST('action', 'alpha');
$id_societe = GETPOST('id_societe', 'int');
$id_convention = GETPOST('id_convention', 'int');

$nom_prenom = '';
$object_concerne = '';
$date_action = '';
$description = '';
$message = '';
$obj_liste = array();

$limit_get = GETPOST('limit', 'alpha');
if ($limit_get === 'tout') {
    $limit = 0;
} else {
    $limit = GETPOST('limit', 'int');
    if ($limit <= 0) {
        $limit = 20;
    }
}

$arret = GETPOST('arret', 'int');
$nb_page = GETPOST('nbpage', 'int');
if ($arret < 0) {
    $arret = 0;
}
if ($nb_page < 1) {
    $nb_page = 1;
}

if ($action === 'recherche' || $action === 'rechercher') {
    $nom_prenom = GETPOST('nom_prenom', 'alphanohtml');
    $object_concerne = GETPOST('object_concerne', 'alphanohtml');
    $date_action = GETPOST('date_action', 'alphanohtml');
    $description = GETPOST('description', 'restricthtml');
}

$sql = 'SELECT rowid, fk_user, nom, prenom, quand, action_effectue, object_concerne';
$sql .= ' FROM '.MAIN_DB_PREFIX.'log';
$sql .= ' WHERE 1=1';

if (!empty($nom_prenom) && $nom_prenom !== '0') {
    $tab_nom = explode('_', $nom_prenom, 2);
    $nom = isset($tab_nom[0]) ? trim($tab_nom[0]) : '';
    $prenom = isset($tab_nom[1]) ? trim($tab_nom[1]) : '';

    if ($nom !== '' && $prenom !== '') {
        $sql .= " AND (nom LIKE '%".$db->escape($nom)."%' AND prenom LIKE '%".$db->escape($prenom)."%')";
    } elseif ($nom !== '') {
        $sql .= " AND (nom LIKE '%".$db->escape($nom)."%' OR prenom LIKE '%".$db->escape($nom)."%')";
    }
}

if (!empty($object_concerne) && $object_concerne !== '0') {
    $sql .= " AND object_concerne LIKE '%".$db->escape($object_concerne)."%'";
}

if (!empty($description)) {
    $sql .= " AND action_effectue LIKE '%".$db->escape($description)."%'";
}

if (!empty($date_action) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_action)) {
    $sql .= " AND DATE(quand) = '".$db->escape($date_action)."'";
}

$sql .= ' ORDER BY rowid DESC';

$res_log = $db->query($sql);
if ($res_log) {
    while ($obj_log = $db->fetch_object($res_log)) {
        if ($obj_log) {
            $obj_liste[] = $obj_log;
        }
    }
} else {
    $message = $db->lasterror();
}

$total_elements = count($obj_liste);
if ($limit_get === 'tout' || $limit <= 0) {
    $limit = $total_elements > 0 ? $total_elements : 1;
}

$total_pages = $total_elements > 0 ? (int) ceil($total_elements / $limit) : 1;
if ($nb_page > $total_pages) {
    $nb_page = $total_pages;
}

$expected_arret = ($nb_page - 1) * $limit;
if ($arret !== $expected_arret) {
    $arret = $expected_arret;
}

$sel5 = '';
$sel10 = '';
$sel15 = '';
$sel20 = '';
$sel30 = '';
$sel50 = '';
$sel100 = '';
$sel200 = '';
$sel500 = '';
$sel1000 = '';
$seltout = '';

switch ($limit_get === 'tout' ? 'tout' : (string) $limit) {
    case '5': $sel5 = 'selected'; break;
    case '10': $sel10 = 'selected'; break;
    case '15': $sel15 = 'selected'; break;
    case '20': $sel20 = 'selected'; break;
    case '30': $sel30 = 'selected'; break;
    case '50': $sel50 = 'selected'; break;
    case '100': $sel100 = 'selected'; break;
    case '200': $sel200 = 'selected'; break;
    case '500': $sel500 = 'selected'; break;
    case '1000': $sel1000 = 'selected'; break;
    default: $seltout = 'selected'; break;
}

print '<hr><div>';

print "<div style='float:right; margin-right:20px;'>";
print '<form name="limit_form" method="GET" action="'.dol_escape_htmltag($_SERVER['PHP_SELF']).'">';
print '<input type="hidden" name="mainmenu" value="paiementsalaire">';
print '<input type="hidden" name="leftmenu" value="configuration">';
print '<input type="hidden" name="action" value="recherche">';
print '<input type="hidden" name="nom_prenom" value="'.dol_escape_htmltag($nom_prenom).'">';
print '<input type="hidden" name="object_concerne" value="'.dol_escape_htmltag($object_concerne).'">';
print '<input type="hidden" name="date_action" value="'.dol_escape_htmltag($date_action).'">';
print '<input type="hidden" name="description" value="'.dol_escape_htmltag($description).'">';
if ($id_societe > 0) {
    print '<input type="hidden" name="id_societe" value="'.$id_societe.'">';
}
if ($id_convention > 0) {
    print '<input type="hidden" name="id_convention" value="'.$id_convention.'">';
}

print "<select style='padding:10px' name='limit' id='limit'>";
print "<option value='5' $sel5>5</option>";
print "<option value='10' $sel10>10</option>";
print "<option value='15' $sel15>15</option>";
print "<option value='20' $sel20>20</option>";
print "<option value='30' $sel30>30</option>";
print "<option value='50' $sel50>50</option>";
print "<option value='100' $sel100>100</option>";
print "<option value='200' $sel200>200</option>";
print "<option value='500' $sel500>500</option>";
print "<option value='1000' $sel1000>1000</option>";
print "<option value='tout' $seltout>tout</option>";
print '</select>';
print ' <mark><b>'.$nb_page.'</b></mark>/<mark><b>'.$total_pages.'</b></mark>';
print '</form>';
print '</div>';

print '<script type="text/javascript">';
print 'var limitSelect = document.getElementById("limit");';
print 'if (limitSelect && limitSelect.form) {';
print '  limitSelect.addEventListener("change", function () {';
print '    this.form.submit();';
print '  }, false);';
print '}';
print '</script>';

print "<table class='tagtable liste'>";
print '<form name="recherche_log" method="GET" action="'.dol_escape_htmltag($_SERVER['PHP_SELF']).'">';
print '<input type="hidden" name="mainmenu" value="paiementsalaire">';
print '<input type="hidden" name="leftmenu" value="configuration">';
print '<input type="hidden" name="action" value="recherche">';
print '<input type="hidden" name="limit" value="'.dol_escape_htmltag($limit_get !== '' ? $limit_get : (string) $limit).'">';
if ($id_societe > 0) {
    print '<input type="hidden" name="id_societe" value="'.$id_societe.'">';
}
if ($id_convention > 0) {
    print '<input type="hidden" name="id_convention" value="'.$id_convention.'">';
}

print "<tr class='liste_titre'>";
print '<td>Utilisateurs<br><select name="nom_prenom">';
print '<option value="0"></option>';

$sql_users = 'SELECT DISTINCT nom, prenom FROM '.MAIN_DB_PREFIX.'log WHERE 1=1';
if (!empty($object_concerne) && $object_concerne !== '0') {
    $sql_users .= " AND object_concerne LIKE '%".$db->escape($object_concerne)."%'";
}
$sql_users .= ' ORDER BY nom, prenom';
$res_users = $db->query($sql_users);
if ($res_users) {
    while ($users = $db->fetch_object($res_users)) {
        if (!$users) continue;
        $value = trim((string) $users->nom).'_'.trim((string) $users->prenom);
        $selected = ($nom_prenom === $value) ? ' selected' : '';
        print '<option value="'.dol_escape_htmltag($value).'"'.$selected.'>'.dol_escape_htmltag(trim($users->nom.' '.$users->prenom)).'</option>';
    }
}
print '</select></td>';

print '<td>Action<br><select name="object_concerne">';
print '<option value="0"></option>';

$sql_objects = 'SELECT DISTINCT object_concerne FROM '.MAIN_DB_PREFIX.'log WHERE object_concerne IS NOT NULL AND object_concerne <> ""';
if (!empty($nom_prenom) && $nom_prenom !== '0') {
    $tab_nom = explode('_', $nom_prenom, 2);
    $nom = isset($tab_nom[0]) ? trim($tab_nom[0]) : '';
    $prenom = isset($tab_nom[1]) ? trim($tab_nom[1]) : '';
    if ($nom !== '' && $prenom !== '') {
        $sql_objects .= " AND nom LIKE '%".$db->escape($nom)."%' AND prenom LIKE '%".$db->escape($prenom)."%'";
    }
}
$sql_objects .= ' ORDER BY object_concerne';
$res_objects = $db->query($sql_objects);
if ($res_objects) {
    while ($liste_log = $db->fetch_object($res_objects)) {
        if (!$liste_log) continue;
        $value = (string) $liste_log->object_concerne;
        $selected = ($object_concerne === $value) ? ' selected' : '';
        print '<option value="'.dol_escape_htmltag($value).'"'.$selected.'>'.dol_escape_htmltag($value).'</option>';
    }
}
print '</select></td>';

print '<td>Description<br><input type="text" name="description" value="'.dol_escape_htmltag($description).'" placeholder="Rechercher dans la description"></td>';
print '<td>Date<br><input type="date" name="date_action" value="'.dol_escape_htmltag($date_action).'"></td>';
print '<td><input type="submit" class="button" value="Rechercher"><br>';
print '<a class="button" href="'.dol_escape_htmltag($_SERVER['PHP_SELF']).'?mainmenu=paiementsalaire&leftmenu=configuration">Annuler</a>';
print '</td></tr>';
print '</form>';

$start = $arret;
$end = min($start + $limit, $total_elements);
for ($i = $start; $i < $end; $i++) {
    $class = ($i % 2 === 0) ? 'pair' : 'impair';
    $log = $obj_liste[$i];

    print '<tr class="'.$class.'">';
    print '<td>'.dol_escape_htmltag(trim((string) $log->nom.' '.(string) $log->prenom)).'</td>';
    print '<td>'.dol_escape_htmltag((string) $log->object_concerne).'</td>';
    print affiche_long_texte('', (string) $log->action_effectue, 1, '', '', '', '', '', '');
    print '<td>'.dol_escape_htmltag((string) $log->quand).'</td>';
    print '<td></td>';
    print '</tr>';
}

if ($total_elements === 0) {
    print '<tr><td colspan="5" align="center">Aucune action trouvee</td></tr>';
}

print '</table>';
print '</div><br><br>';
print '<div>';
print '<span style="float:right; margin-left:20px;">';

$page_link = '';
if ($total_pages > 1) {
    $base_url = $_SERVER['PHP_SELF'].'?mainmenu=paiementsalaire&leftmenu=configuration';
    $base_url .= '&limit='.urlencode($limit_get !== '' ? $limit_get : (string) $limit);
    $base_url .= '&action=recherche';
    $base_url .= '&nom_prenom='.urlencode($nom_prenom);
    $base_url .= '&object_concerne='.urlencode($object_concerne);
    $base_url .= '&date_action='.urlencode($date_action);
    $base_url .= '&description='.urlencode($description);
    if ($id_societe > 0) {
        $base_url .= '&id_societe='.$id_societe;
    }
    if ($id_convention > 0) {
        $base_url .= '&id_convention='.$id_convention;
    }

    if ($nb_page > 1) {
        $page_link .= '<a href="'.$base_url.'&arret=0&nbpage=1" style="padding:5px"><b>Debut</b></a>&nbsp;&nbsp;';
    }

    $page_start = max(1, $nb_page - 2);
    $page_end = min($total_pages, $nb_page + 3);

    for ($p = $page_start; $p <= $page_end; $p++) {
        $style = ($p === $nb_page) ? 'background-color:yellow; padding:5px' : 'padding:5px';
        $offset = ($p - 1) * $limit;
        $page_link .= '<a href="'.$base_url.'&arret='.$offset.'&nbpage='.$p.'" style="'.$style.'"><b>'.$p.'</b></a>&nbsp;&nbsp;';
    }

    if ($nb_page < $total_pages) {
        $last_offset = ($total_pages - 1) * $limit;
        $page_link .= '<a href="'.$base_url.'&arret='.$last_offset.'&nbpage='.$total_pages.'" style="padding:5px"><b>Fin</b></a>&nbsp;&nbsp;';
    }
}

print $page_link.'</span>';
print '</div>';

if (!empty($message)) {
    print '<script>$.jnotify("'.dol_escape_js($message).'", {delay:5000, fadeSpeed:500});</script>';
}

if ($res_log) {
    $db->free($res_log);
}
if (isset($res_users) && $res_users) {
    $db->free($res_users);
}
if (isset($res_objects) && $res_objects) {
    $db->free($res_objects);
}

llxFooter();
$db->close();
