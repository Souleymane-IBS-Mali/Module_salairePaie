<?php
/* Copyright (C) 2001-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2013 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2015 Regis Houssin        <regis.houssin@inodbox.com>
 * Copyright (C) 2015-2020 Juanjo Menent <jmenent@2byte.es>
 * Copyright (C) 2015      Jean-François Ferry <jfefe@aternatik.fr>
 * Copyright (C) 2015      Raphaël Doursenaud   <rdoursenaud@gpcsolutions.fr>
 * Copyright (C) 2016      Marcos García        <marcosgdf@gmail.com>
 * Copyright (C) 2019      Nicolas ZABOURI      <info@inovea-conseil.com>
 * Copyright (C) 2020      Tobias Sekan         <tobias.sekan@startmail.com>
 * Copyright (C) 2020      Josep Lluís Amador   <joseplluis@lliuretic.cat>
 * Copyright (C) 2021      Frédéric France      <frederic.france@netlogic.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 */

/**
 * \file       htdocs/compta/index.php
 * \ingroup    compta
 * \brief      Main page of accountancy area
 */

require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/custom/paiementsalaire/lib/paiementsalaire.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';

$form = new Form($db);

$action = GETPOST('action', 'alpha');
$confirm = GETPOST('confirm', 'alpha');

$fk_user = GETPOST('id', 'int');
$id_societe = GETPOST('id_societe', 'int');
$fk_salarie = GETPOST('fk_salarie', 'int');
$id_convention = GETPOST('id_convention', 'int');
$annee_rechercher = GETPOST('annee_rechercher', 'int');
$id_bull = 0;

// Nom du salarié
$nom = '';
$obj_nom_sal_sql = null;
$nom_sal_sql = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$fk_user;
$result_nom_sal_sql = $db->query($nom_sal_sql);
if ($result_nom_sal_sql) {
    $obj_nom_sal_sql = $db->fetch_object($result_nom_sal_sql);
    if ($obj_nom_sal_sql) {
        $nom = $obj_nom_sal_sql->lastname.'_'.$obj_nom_sal_sql->firstname;
    }
}

// Colonnes exportables
$export_fields = array(
    'fonction',
    'banque',
    'compte',
    'nb_jour_tr',
    'nb_heure_tr',
    'pourcentage',
    'categorie',
    'situation_matrimoniale',
    'heure_sup',
    'salaire_base',
    'sursalaire',
    'anciennete',
    'primes',
    'indemnites',
    'salaire_brut',
    'salaire_brut_imposable',
    'salaire_brut_cotisable',
    'inps',
    'amo',
    'its',
    'base_cfe',
    'montant_cfe',
    'base_tl',
    'montant_tl',
    'avance',
    'net_payer',
    'cout'
);

// Quand on clique sur "Récapitulatif par période", on recharge la page avec action=recap_periode.
// On garde les colonnes cochées pour les transmettre ensuite au fichier Excel par période.
if ($action == 'recap_periode') {
    $annees_disponibles = array();
    $sql_annees = "SELECT DISTINCT annee FROM ".MAIN_DB_PREFIX."bulletin WHERE fk_salarie=".$fk_salarie;
    if (!empty($id_societe)) {
        $sql_annees .= " AND fk_societe=".$id_societe;
    }
    $sql_annees .= " ORDER BY annee DESC";
    $res_annees = $db->query($sql_annees);
    if ($res_annees) {
        while ($obj_annee = $db->fetch_object($res_annees)) {
            $annees_disponibles[(string) $obj_annee->annee] = (string) $obj_annee->annee;
        }
    }

    $mois_options = array();
    for ($m = 1; $m <= 13; $m++) {
        $mois_options[(string) $m] = (string) $m;
    }

    $selected_fields_query = '';
    foreach ($export_fields as $field) {
        if (GETPOST($field, 'alpha')) {
            $selected_fields_query .= '&'.$field.'=on';
        }
    }

    $page_confirm = '../doc/recapitulatif_salarie_periode.php?id_societe='.$id_societe;
    $page_confirm .= '&fk_salarie='.$fk_salarie;
    $page_confirm .= '&id_convention='.$id_convention;
    $page_confirm .= '&nom='.urlencode($nom);
    $page_confirm .= $selected_fields_query;

    $formquestion = array(
        array(
            'type' => 'select',
            'name' => 'mois_debut',
            'label' => 'Mois début',
            'values' => $mois_options,
            'default' => '1'
        ),
        array(
            'type' => 'select',
            'name' => 'annee_debut',
            'label' => 'Année début',
            'values' => $annees_disponibles,
            'default' => !empty($annees_disponibles) ? array_key_first($annees_disponibles) : ''
        ),
        array(
            'type' => 'select',
            'name' => 'mois_fin',
            'label' => 'Mois fin',
            'values' => $mois_options,
            'default' => '12'
        ),
        array(
            'type' => 'select',
            'name' => 'annee_fin',
            'label' => 'Année fin',
            'values' => $annees_disponibles,
            'default' => !empty($annees_disponibles) ? array_key_first($annees_disponibles) : ''
        )
    );
}

llxHeader('', 'Paiement | Salaire');

print load_fiche_titre($langs->trans('Bulletin de paye'), '', '');

$head = salaire_Head($fk_salarie, $fk_user, $id_societe, $id_convention);
print dol_get_fiche_head($head, 'recap', '', -1, '');

if ($action == 'recap_periode') {
    if (empty($annees_disponibles)) {
        print '<div class="warning">Aucune année disponible pour ce salarié.</div>';
    } else {
        print $form->formconfirm(
            $page_confirm,
            'Récapitulatif par période',
            'Veuillez préciser la période du récapitulatif',
            'confirm_recap_periode',
            $formquestion,
            '',
            1,
            300,
            600
        );
    }
}

if ($user->id != 1 && $user->id != $fk_user && empty($user->rights->paiementsalaire->salarie->voirBulletin)) {
    print "<h2> Vous n'avez pas ce droit </h2>";
} else {
    if (empty($fk_salarie)) {
        print "<mark><strong>Il n'a pas encore de fk_salarie</strong></mark><br>";
        print 'Page non Disponible';
    } else {
        $obj_soc = prepare_objet_entete($fk_salarie, $fk_user, $db, $id_societe, $id_convention);
        entete_societe($obj_soc, 'societe');
        print '<hr>';

        print '<div class="div-table-responsive-no-min">';
        print '<table class="noborder centpercent">';

        print '<form method="post" action="../doc/export_recapitulatif.php?id_societe='.$id_societe.'&fk_salarie='.$fk_salarie.'&nom='.urlencode($nom).'">';
        print '<input type="hidden" name="token" value="'.newToken().'">';
        print '<input type="hidden" name="id_societe" value="'.$id_societe.'">';
        print '<input type="hidden" name="fk_salarie" value="'.$fk_salarie.'">';
        print '<input type="hidden" name="nom" value="'.dol_escape_htmltag($nom).'">';

        print '<tr class="liste_titre">';
        print '<th>Veuillez cocher les colonnes à exporter</th><th></th><th></th><th><a id="tout_cocher" onClick="toutCocher(); return false;" href="#">Tout décocher</a></th>';
        print '</tr>';

        print '<tr class="oddeven nodrag nodrop nohover">';
        print '<td><input type="checkbox" id="fonction" name="fonction" checked> <label>Fonction</label></td>';
        print '<td><input type="checkbox" id="banque" name="banque" checked> <label>Banque</label></td>';
        print '<td><input type="checkbox" id="compte" name="compte" checked> <label>N° Compte</label></td>';
        print '<td><input type="checkbox" id="nb_jour_tr" name="nb_jour_tr" checked> <label>Nombre de jour travaillé</label></td>';
        print '</tr>';

        print '<tr class="oddeven nodrag nodrop nohover">';
        print '<td><input type="checkbox" id="nb_heure_tr" name="nb_heure_tr" checked> <label>Nombre d\'heure travaillé</label></td>';
        print '<td><input type="checkbox" id="pourcentage" name="pourcentage" checked> <label>Pourcentage(Taux)</label></td>';
        print '<td><input type="checkbox" id="categorie" name="categorie" checked> <label>Catégorie</label></td>';
        print '<td><input type="checkbox" id="situation_matrimoniale" name="situation_matrimoniale" checked> <label>Situation Matrimoniale '.info_admin('Avec nombre d\'enfant', '1').'</label></td>';
        print '</tr>';

        print '<tr class="oddeven nodrag nodrop nohover">';
        print '<td><input type="checkbox" id="heure_sup" name="heure_sup" checked> <label>Heure Sup</label></td>';
        print '<td><input type="checkbox" id="salaire_base" name="salaire_base" checked> <label>Salaire de base</label></td>';
        print '<td><input type="checkbox" id="sursalaire" name="sursalaire" checked> <label>Sursalaire</label></td>';
        print '<td><input type="checkbox" id="anciennete" name="anciennete" checked> <label>Anciennété</label></td>';
        print '</tr>';

        print '<tr class="oddeven nodrag nodrop nohover">';
        print '<td><input type="checkbox" id="prime" name="primes" checked> <label>Primes</label></td>';
        print '<td><input type="checkbox" id="indemnite" name="indemnites" checked> <label>Indemnités</label></td>';
        print '<td><input type="checkbox" id="salaire_brut" name="salaire_brut" checked> <label>Salaire brut</label></td>';
        print '<td><input type="checkbox" id="salaire_brut_imposable" name="salaire_brut_imposable" checked> <label>Salaire brut imposable</label></td>';
        print '</tr>';

        print '<tr class="oddeven nodrag nodrop nohover">';
        print '<td><input type="checkbox" id="salaire_brut_cotisable" name="salaire_brut_cotisable" checked> <label>Salaire brut cotisable</label></td>';
        print '<td><input type="checkbox" id="inps" name="inps" checked> <label>I.N.P.S Employé & Patro</label></td>';
        print '<td><input type="checkbox" id="amo" name="amo" checked> <label>AMO Salarié & Patro</label></td>';
        print '<td><input type="checkbox" id="its" name="its" checked> <label>I.T.S</label></td>';
        print '</tr>';

        print '<tr class="oddeven nodrag nodrop nohover">';
        print '<td><input type="checkbox" id="base_cfe" name="base_cfe" checked> <label>Base CFE</label></td>';
        print '<td><input type="checkbox" id="montant_cfe" name="montant_cfe" checked> <label>Montant CFE</label></td>';
        print '<td><input type="checkbox" id="base_tl" name="base_tl" checked> <label>Base TL</label></td>';
        print '<td><input type="checkbox" id="montant_tl" name="montant_tl" checked> <label>Montant TL</label></td>';
        print '</tr>';

        print '<tr class="oddeven nodrag nodrop nohover">';
        print '<td><input type="checkbox" id="avances" name="avance" checked> <label>Total Avance</label></td>';
        print '<td><input type="checkbox" id="net_payer" name="net_payer" checked> <label>Net à payer</label></td>';
        print '<td><input type="checkbox" id="cout" name="cout" checked> <label>Coût</label></td>';
        print '<td></td>';
        print '</tr>';

        print '<tr class="oddeven nodrag nodrop nohover">';
        print '<td><br><br><label>Année</label>&nbsp;<select name="annee" required><option value=""></option>';

        $sql_bull = "SELECT DISTINCT annee FROM ".MAIN_DB_PREFIX."bulletin WHERE fk_salarie=".$fk_salarie;
        if (!empty($id_societe)) {
            $sql_bull .= " AND fk_societe=".$id_societe;
        }
        $sql_bull .= " ORDER BY annee DESC";

        $res_bull = $db->query($sql_bull);
        if ($res_bull) {
            while ($obj_bull = $db->fetch_object($res_bull)) {
                print '<option value="'.$obj_bull->annee.'">'.$obj_bull->annee.'</option>';
            }
        }
        print '</select></td>';
        print '<td colspan="3" align="right"><input class="button" type="submit" value="Exporter par année"></td>';
        print '</tr>';

        print '</form>';
        // Bouton tout en bas : il utilise le même formulaire pour garder les colonnes cochées.
        print '<tr class="oddeven nodrag nodrop nohover">';
        print '<td colspan="4" align="right" style="padding-top: 15px;">';
        print '<a class="button" href="'.$_SERVER['PHP_SELF'].'?mainmenu=paiementsalaire&leftmenu=salarie&id='.$fk_user.'&fk_salarie='.$fk_salarie.'&id_societe='.$id_societe.'&id_convention='.$id_convention.'&action=recap_periode">Récapitulatif par période</a>';
        print '</td>';
        print '</tr>';

        print '</table>';
        print '</div>';

        print '<script>
            var tout_cocher = document.getElementById("tout_cocher");
            var tableau = ["fonction", "banque", "compte", "nb_jour_tr", "nb_heure_tr", "pourcentage", "categorie", "situation_matrimoniale", "salaire_base", "sursalaire", "anciennete", "prime", "indemnite", "salaire_brut", "salaire_brut_imposable", "salaire_brut_cotisable", "inps", "amo", "heure_sup", "its", "base_cfe", "montant_cfe", "base_tl", "montant_tl", "avances", "net_payer", "cout"];
            function toutCocher(){
                if(tout_cocher.innerText == "Tout cocher"){
                    tout_cocher.innerText = "Tout décocher";
                    for(let i = 0; i < tableau.length; i++){
                        var checkbox = document.getElementById(tableau[i]);
                        if (checkbox) checkbox.checked = true;
                    }
                }else{
                    tout_cocher.innerText = "Tout cocher";
                    for(let i = 0; i < tableau.length; i++){
                        var checkbox = document.getElementById(tableau[i]);
                        if (checkbox) checkbox.checked = false;
                    }
                }
            }
        </script>';
    }
}

$db->close();
