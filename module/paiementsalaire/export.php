<?php
/* Copyright (C) 2001-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2013 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2015 Regis Houssin        <regis.houssin@inodbox.com>
 * Copyright (C) 2015-2020 Juanjo Menent	<jmenent@2byte.es>
 * Copyright (C) 2015      Jean-François Ferry	<jfefe@aternatik.fr>
 * Copyright (C) 2015      Raphaël Doursenaud   <rdoursenaud@gpcsolutions.fr>
 * Copyright (C) 2016      Marcos García        <marcosgdf@gmail.com>
 * Copyright (C) 2019      Nicolas ZABOURI      <info@inovea-conseil.com>
 * Copyright (C) 2020      Tobias Sekan         <tobias.sekan@startmail.com>
 * Copyright (C) 2020      Josep Lluís Amador   <joseplluis@lliuretic.cat>
 * Copyright (C) 2021      Frédéric France		<frederic.france@netlogic.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 *	\file       htdocs/compta/index.php
 *	\ingroup    compta
 *	\brief      Main page of accountancy area
 */

require_once './../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';



$form = new Form($db);
llxHeader('', "Paiement | Salaire");
//Titre 
print load_fiche_titre($langs->trans("Export Données"), '', '');
print '<hr>';
$message = '';
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

// Définir la valeur de la cellule fusionnée
//global $mois, $annee, $nom_soc;
$mois= GETPOST("mois", "int");
$annee= GETPOST("annee", "int");
$nom_soc= GETPOST("nom_soc", "int");
$id_societe= GETPOST("id_societe", "int");
$action = GETPOST("action", 'alpha');
if(empty($action))
    $action = "choix";

$mois_tab = array(" Janvier "," Février "," Mars "," Avril "," Mai "," Juin "," Juillet "," Août "," Septembre "," Octobre "," Novembre "," Décembre ");

if($action == "choix"){

    print '<div class="div-table-responsive-no-min">';
				print '<table class="noborder centpercent">';

				// Line for title
				print '<!-- line title to add new entry -->';
				print '<tr class="liste_titre">';
                print '<th>Exports</th><th></th><th></th>';
                print '</tr>';

                print '<tr class="oddeven nodrag nodrop nohover">';
                print '<td><a href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=importexportsociete&action=export_salaire" >Export salaires</a></td>';
                print '<td></td>';
                print '<td></td>';

                print '</tr>';

                print '<tr class="oddeven nodrag nodrop nohover">';
                print '<td><a href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=importexportsociete&action=export_inps" >Export Fiche INPS</a></td>';
                print '<td></td>';
                print '<td></td>';

                print '</tr>';
                /*print '<tr class="oddeven nodrag nodrop nohover">';
                print '<td></td>';
                print '<td></td>';
                print '<td></td>';

                print '</tr>';

                print '<tr class="oddeven nodrag nodrop nohover">';
                print '<td></td>';
                print '<td></td>';
                print '<td></td>';

                print '</tr>';*/


        print '</table>';
}

if($action == "export_salaire"){
    print '<div class="div-table-responsive-no-min">';
				print '<table class="noborder centpercent">';

				// Line for title
                print '<form action="export_salaire.php?mainmenu=paiementsalaire&leftmenu=importexportsociete">';
                print '<input type="hidden" name="token" value="'.newToken().'">';
                print '<input type="hidden" name="action" value="exporter">';
				print '<!-- line title to add new entry -->';
				print '<tr class="liste_titre">';
                print '<th>Veuillez cocher les colonnes à exporter</th><th></th><th></th><th><a id="tout_cocher" onClick="toutCocher()" href="#">Tout décocher</a></th>';
                print '</tr>';

                print '<tr class="oddeven nodrag nodrop nohover">';
                print '<td><input type="checkbox" name="prenom" checked disabled> <label>Prénom</label></td>';
                print '<td><input type="checkbox" name="nom" checked disabled> <label>Nom</label></td>';
                print '<td><input type="checkbox" id="date_entree" name="date_entree" checked> <label>Date Entrée</label></td>';
                print '<td><input type="checkbox" id="fonction" name="fonction" checked> <label>Fonction</label></td>';

                print '</tr>';

                print '<tr class="oddeven nodrag nodrop nohover">';
                print '<td><input type="checkbox" id="banque" name="banque" checked> <label>Banque</label></td>';
                print '<td><input type="checkbox" id="compte" name="compte" checked> <label>N° Compte</label></td>';
                print '<td><input type="checkbox" id="nb_jour_tr" name="nb_jour_tr" checked > <label>Nombre de jour travaillé</label></td>';
                print '<td><input type="checkbox" id="nb_heure_tr" name="nb_heure_tr" checked > <label>Nombre d\'heure travaillé</label></td>';

                print '</tr>';

                print '<tr class="oddeven nodrag nodrop nohover">';
                print '<td><input type="checkbox" id="pourcentage" name="pourcentage" checked > <label>Pourcentage(Taux)</label></td>';
                print '<td><input type="checkbox" id="categorie" name="categorie" checked > <label>Catégorie</label></td>';
                print '<td><input type="checkbox" id="situation_matrimoniale" name="situation_matrimoniale" checked><label>Situation Matrimoniale '.info_admin('Avec nombre d\'enfant', '1').'</label></td>';
                print '<td><input type="checkbox" id="salaire_base" name="salaire_base" checked > <label>Salaire de base</label></td>';

                print '</tr>';
                print '<tr class="oddeven nodrag nodrop nohover">';
                print '<td><input type="checkbox" id="sursalaire" name="sursalaire" checked > <label>Sursalaire</label></td>';
                print '<td><input type="checkbox" id="anciennete" name="anciennete" checked > <label>Anciennété</label></td>';
                print '<td><input type="checkbox" id="primes" name="primes" checked > <label>Primes</label></td>';
                print '<td><input type="checkbox" id="indemnites" name="indemnites" checked > <label>Indemnités</label></td>';

                print '</tr>';

                print '<tr class="oddeven nodrag nodrop nohover">';
                print '<td><input type="checkbox" id="salaire_brut" name="salaire_brut" checked > <label>Salaire brut</label></td>';
                print '<td><input type="checkbox" id="salaire_brut_imposable" name="salaire_brut_imposable" checked > <label>Salaire brut imposable</label></td>';
                print '<td><input type="checkbox" id="salaire_brut_cotisable" name="salaire_brut_cotisable" checked > <label>Salaire brut cotisable</label></td>';
                print '<td><input type="checkbox" id="inps_employe" name="inps_employe" checked><label>I.N.P.S Employé</label></td>';

                print '</tr>';

                print '<tr class="oddeven nodrag nodrop nohover">';
                print '<td><input type="checkbox" id="inps_employeur" name="inps_employeur" checked > <label>I.N.P.S Patro</label></td>';
                print '<td><input type="checkbox" id="amo_employe" name="amo_employe" checked> <label>AMO Salarié</label></td>';
                print '<td><input type="checkbox" id="amo_employeur" name="amo_employeur" checked> <label>AMO Patro</label></td>';
                print '<td><input type="checkbox" id="its" name="its" checked> <label>I.T.S</label></td>';
                print '</tr>';

                print '<tr class="oddeven nodrag nodrop nohover">';
                print '<td><input type="checkbox" id="base_cfe" name="base_cfe" checked> <label>Base CFE</label></td>';
                print '<td><input type="checkbox" id="montant_cfe" name="montant_cfe" checked> <label>Montant CFE</label></td>';
                print '<td><input type="checkbox" id="base_tl" name="base_tl" checked> <label>Base TL</label></td>';
                print '<td><input type="checkbox" id="montant_tl" name="montant_tl" checked> <label>Montant TL</label></td>';

                print '</tr>';

                print '<tr class="oddeven nodrag nodrop nohover">';
                print '<td><input type="checkbox" id="avance" name="avance" checked> <label>Total Avance</label></td>';
                print '<td><input type="checkbox" id="net_payer" name="net_payer" checked> <label>Net à payer</label></td>';
                print '<td><input type="checkbox" id="cout" name="cout" checked> <label>Coût</label></td>';
                print '<td></td>';
                print '</tr>';

                $mois_tab = array(" Janvier "," Février "," Mars "," Avril "," Mai "," Juin "," Juillet "," Août "," Septembre "," Octobre "," Novembre "," Décembre ");

                print '<tr class="oddeven nodrag nodrop nohover">';
                print '<td><br><br><label>Mois</label>&nbsp;<select name="mois" required>
                        <option value=""></option>';
                    for ($i=0; $i < count($mois_tab); $i++) { 
                        print '<option value="'.($i+1).'">'.$mois_tab[$i].'</option>';
                    }
                print '</select></td>';
                    print '<td><br><br><label>Année</label>&nbsp;<select name="annee" required>
                            <option value=""></option>';
                    $sql_bull = "SELECT DISTINCT annee FROM ".MAIN_DB_PREFIX."bulletin";
                    if($user->id != 1)
                        $sql_bul .= " WHERE fk_societe IN ".$array_id_soc." AND sce.grp=1";
                    else   
                        $sql_bul .= " WHERE sce.grp=1";


                    $res_bull = $db->query($sql_bull);
                    if($res_bull){
                        $nb = $db->num_rows($res_bull);
                        $i = 0;
                        while($i < $nb){
                            $obj_bull = $db->fetch_object($res_bull);
                            print '<option value="'.$obj_bull->annee.'">'.$obj_bull->annee.'</option>';
                        $i ++;
                        }
                    }
                    print '</select></td>';                
                print "<td colspan=2><br><br><label>Société</label><select name='id_societe' required>";
                print "<option value='' ></option>";
                $sql = "SELECT sc.rowid as r1, sc.nom, sc.name_alias, sc.phone, sc.fax, sc.code_client, sc.zip, sce.rowid as r2, sce.fk_object, sce.conv FROM ".MAIN_DB_PREFIX."societe as sc";
                $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."societe_extrafields as sce ON sc.rowid=sce.fk_object";
                if($user->id != 1)
                    $sql .= " WHERE sc.rowid IN ".$array_id_soc." AND sce.grp=1";
                else   
                    $sql .= " WHERE sce.grp=1";

                $result = $db->query($sql);
        
                if($result){
                    $i = 0;
                    $num = $db->num_rows($result);
                    while ($i < $num){
                        $societe = $db->fetch_object($result);
                        if($id_societe == $societe->r1)
                            print "<option value=".$societe->r1." selected>".$societe->nom."</option>";
                        else print "<option value=".$societe->r1.">".$societe->nom."</option>";
                        $i ++;
                    }
                }
        
            print "</select></td>";                
            //print '<td></td>';

                print '</tr>';

                print '</table>';
                print '<div style="float: right"><input class="button" type="submit" value="Exporter" ></div>';
                print '</form>';

                //Partie controle JS
                print '<script>
                    var tout_cocher = document.getElementById("tout_cocher");
                    var tableau = ["date_entree", "fonction", "banque", "compte", "nb_jour_tr", "nb_heure_tr","pourcentage", "categorie","situation_matrimoniale","salaire_base","sursalaire",
                    "anciennete","primes","indemnites","salaire_brut","salaire_brut_imposable","salaire_brut_cotisable","inps_employe","inps_employeur", "amo_employe", "amo_employeur",
                    "its","base_cfe","montant_cfe","base_tl","montant_tl","avance","net_payer","cout"];
                    function toutCocher(){
                        //alert();
                        if(tout_cocher.innerText == "Tout cocher"){
                            tout_cocher.innerText = "Tout décocher";
                            for(let i=0; i<tableau.length; i ++){
                                var checkbox = document.getElementById(tableau[i]);
                                checkbox.checked = true;
                            }
                        }else{
                            tout_cocher.innerText = "Tout cocher";
                            for(let i=0; i<tableau.length; i ++){
                                var checkbox = document.getElementById(tableau[i]);
                                checkbox.checked = false;
                            }
                        }

                        
                    }
                </script>';
}


//la redirection de fiche_cotisation_affiliation 
if($action == "export_fiche_inps"){
    $numero = GETPOST("numero_affiliation", "alpha");
    $annee = GETPOST("annee", "int");
    $mois = GETPOST("mois", "int");
    $id_societe = GETPOST("id_societe", "int");
        
    $message .= 'Tous les "CHAMPS" sont obligatoire';
        $action = "export_inps";
    
}


if($action == "export_inps"){
    $mois_tab = array(" Janvier "," Février "," Mars "," Avril "," Mai "," Juin "," Juillet "," Août "," Septembre "," Octobre "," Novembre "," Décembre ");

    //N° d'affiliation
    $array[] = array('label'=> 'N° d\'affiliation => ','type'=> 'text', 'size'=>'', 'morecss'=>'', 'moreattr'=>'', 'name'=>'numero_affiliation','value' => GETPOST("numero_affiliation","alpha"));

    //Annee dont les bulletins sont générés
    $sql_verif = "SELECT DISTINCT annee FROM ".MAIN_DB_PREFIX."bulletin ORDER BY annee DESC";
    $res_verif = $db->query($sql_verif);
    $num = $db->num_rows($sql_verif);
    $a = 1;
    $key = array();
    $val = array();
    while ($a <= $num) {
        $obj_verif = $db->fetch_object($res_verif);
        $key[] = $obj_verif->annee;
        $val[] = $obj_verif->annee;
        $a ++;
    }
	$array[] = array('label'=> 'Annee => ','type'=> 'select', 'size'=>'', 'morecss'=>'', 'moreattr'=>'selected', 'name'=>'annee','values' => array_combine($key,$val));


    //Les douze mois de l'année
    $key = array();
    $val = array();
    for ($i=1; $i < 13; $i++) { 
        $key[] = $i;
        $val[] = $mois_tab[($i - 1)];
    }
	$array[] = array('label'=> 'Mois => ','type'=> 'select', 'size'=>'', 'morecss'=>'', 'moreattr'=>'selected', 'name'=>'mois','values' => array_combine($key,$val));


    //la société mère
    $key = array();
    $val = array();
    $sql = "SELECT sc.rowid as r1, sc.nom, sce.rowid as r2, sce.fk_object FROM ".MAIN_DB_PREFIX."societe as sc";
                $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."societe_extrafields as sce ON sc.rowid=sce.fk_object";
                if($user->id != 1)
                    $sql .= " WHERE sc.rowid IN ".$array_id_soc." AND sce.grp=1";
                else   
                    $sql .= " WHERE sce.grp=1";

                $result = $db->query($sql);
        
                if($result){
                    $i = 0;
                    $num = $db->num_rows($result);
                    while ($i < $num){
                        $societe = $db->fetch_object($result);
                        $key[] = $societe->r1;
                        $val[] = $societe->nom;
                        $i ++;
                    }
                }
                $array[] = array('label'=> 'Société mère => ','type'=> 'select', 'size'=>'', 'morecss'=>'', 'moreattr'=>'selected', 'name'=>'id_societe','values' => array_combine($key,$val));

    $url = "./doc/fiche_cotisation_affiliation.php?mainmenu=paiementsalaire&leftmenu=societe";
    $titre = 'Exort de fiche I.N.P.S par N° d\'affiliation';

    $formconfirm = $form->formconfirm(
        $url, 
        $titre, 
        "", 
        'export_fiche_inps', 
        $array, 
        '', 
        1,
        240,
        '30%'
    );
    print $formconfirm;
}

if(!empty($message))
		print "<script>
		$.jnotify('".$message."', {delay : 5000, fadeSpeed: 500});
		</script>";