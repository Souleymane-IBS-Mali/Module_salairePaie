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
                print '<td><a href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=importexportsociete&action=export_reg_its" >Export Fiche INPS</a></td>';
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


//la redirection de fiche_cotisation_affiliation 
if($action == "export_fiche_inps"){
    $numero = GETPOST("numero_affiliation", "alpha");
    $annee = GETPOST("annee", "int");
    $mois = GETPOST("mois", "int");
    $id_societe = GETPOST("id_societe", "int");
        
    $message .= 'Tous les "CHAMPS" sont obligatoire';
        $action = "export_reg_its";
    
}


if($action == "export_reg_its"){
    $mois_tab = array(" Janvier "," Février "," Mars "," Avril "," Mai "," Juin "," Juillet "," Août "," Septembre "," Octobre "," Novembre "," Décembre ");

    //N° d'affiliation
    //$array[] = array('label'=> 'N° d\'affiliation => ','type'=> 'text', 'size'=>'', 'morecss'=>'', 'moreattr'=>'', 'name'=>'numero_affiliation','value' => GETPOST("numero_affiliation","alpha"));

    //Annee dont les bulletins sont générés
    $sql_verif = "SELECT DISTINCT annee FROM ".MAIN_DB_PREFIX."bulletin_regularisation_its ORDER BY annee DESC";
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

    $url = "./doc/article29_its.php?mainmenu=paiementsalaire&leftmenu=societe";
    $titre = 'Article 29 I.T.S';

    $formconfirm = $form->formconfirm(
        $url, 
        $titre, 
        "", 
        'tout_salarie', 
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