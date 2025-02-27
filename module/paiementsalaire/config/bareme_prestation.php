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

 require '../../main.inc.php';
 require_once DOL_DOCUMENT_ROOT.'/custom/paiementsalaire/lib/paiementsalaire.lib.php';

 //require_once DOL_DOCUMENT_ROOT.'/core/class/html.formpaiementsalaire.class.php';
 //require_once DOL_DOCUMENT_ROOT.'/custom/paiementsalaire/core/modules/modPaiementSalaire.class.php';
 
 //$PaiementSalaire = new modPaiementSalaire($db);
 
 $form = new Form($db);
 llxHeader("", "Paiement | Salaire");

 $message = '';
if(!empty(GETPOST("id_prestation", "int")))
    $id_type_prestation = GETPOST("id_prestation", "int");
if(!empty(GETPOST("bareme_row", "int")))
    $bareme_row = GETPOST("id_bareme", "int");

 $action = GETPOST('action','alpha');
if(empty($action))	
	$action = 'liste';

    if($action == "add_bareme"){
        $result = null;
        $id_prestation = GETPOST('id_prestation','int');
        $charge = GETPOST('charge','int');
        $prest = "SELECT * FROM ".MAIN_DB_PREFIX."type_prestation WHERE rowid=".$id_prestation;
        $result_prest = $db->query($prest);
        $obj_prestation = $db->fetch_object($result_prest);
        if($charge == 1){
            $charge_salariale = GETPOST('charge_salariale','int');
        if(empty($charge_salariale)){
            $message = 'Le champ "CHARGE SALARIALE" est obligatoire<br>';
        }

        if(empty($message)){
            $charge = GETPOST('charge', 'int');
            $sql = "INSERT INTO ".MAIN_DB_PREFIX."bareme_prestation (fk_prestation, taux_salariale, charge) VALUES ('".$id_prestation."','".$charge_salariale."',".$charge.")";
            $result = $db->query($sql);
        }

        }else if($charge == 2){
            $charge_patronale = GETPOST('charge_patronale','int');
            if(empty($charge_patronale)){
                $message = 'Le champ "CHARGE PATRONALE" est obligatoire<br>';
            }

            if(empty($message)){
                $charge = GETPOST('charge', 'int');
                $sql = "INSERT INTO ".MAIN_DB_PREFIX."bareme_prestation (fk_prestation, taux_patronale, charge) VALUES ('".$id_prestation."','".$charge_patronale."',".$charge.")";
                $result = $db->query($sql);
            }
        }else{
            $charge = GETPOST('charge', 'int');
            $charge_patronale = GETPOST('charge_patronale1','int');
            $charge_salariale = GETPOST('charge_salariale1','int');
            if(empty($charge_salariale)){
                $message = 'Le champ "CHARGE SALARIALE" est obligatoire<br>';
            }
            if(empty($charge_patronale)){
                $message = 'Le champ "CHARGE PATRONALE" est obligatoire<br>';
            }

            if(empty($message)){
                $sql = "INSERT INTO ".MAIN_DB_PREFIX."bareme_prestation (fk_prestation, taux_salariale, taux_patronale, charge) VALUES ('".$id_prestation."','".$charge_salariale."','".$charge_patronale."',".$charge.")";
                $result = $db->query($sql);
            }

            
        }

        $result = $db->query("SELECT LAST_INSERT_ID() as rowid;");
            $obj = $db->fetch_object($result);
            $rowidcondition =  $obj->rowid;
            $fk_convention = GETPOST('conventions', 'array');
            if(!empty($fk_convention)){
		        for($i =0; $i<count($fk_convention); $i++){
                    $fk_convention_i = $fk_convention[$i];
                    $sql = 'INSERT INTO '.MAIN_DB_PREFIX.'bareme_prestation_convention (fk_condition, fk_convention) VALUES ('.$rowidcondition.','.$fk_convention_i.')';
                    $result2 = $db->query($sql);
			}
		}else{
            $sql = 'INSERT INTO '.MAIN_DB_PREFIX.'bareme_prestation_convention (fk_condition, fk_convention) VALUES ('.$rowidcondition.',0)';
			$result2 = $db->query($sql);


		}

        if(empty($message) && $result){
            $action = "detail_prestation";
            $message = 'Barême enregistrer avec succès';

            //On garde la trace de l'action
            $sql_select = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$user->id;
            $obj = $db->fetch_object($db->query($sql_select));

            $action_effectue = "Ajout barème cotisation taux : patro=".($charge_patronale?:0)." salariale=".($charge_salariale?:0);
            $sql_log = 'INSERT INTO '.MAIN_DB_PREFIX.'log (fk_user, nom, prenom, quand, action_effectue, object_concerne)';
            $sql_log .= ' VALUES('.$user->id.', "'.$obj->lastname.'","'.$obj->firstname.'",now(),"'.$action_effectue.'","Ajout barème cotisation")';
            $db->query($sql_log);

        }else{
            if(empty($message))
                $message = 'Un problème est survenu';
            $action = 'nouveau_bareme_form';
        }


    }

    if($action == "edit_bareme"){
        $result = null;
        $id_prestation = GETPOST('id_prestation','int');
        $bareme_row = GETPOST('id_bareme','int');
        $charge = GETPOST('charge','int');

        $sql_delete = "DELETE FROM ".MAIN_DB_PREFIX."bareme_prestation_convention WHERE fk_condition=".$bareme_row;
	    $result2 = $db->query($sql_delete);

        $prest = "SELECT * FROM ".MAIN_DB_PREFIX."type_prestation WHERE rowid=".$id_prestation;
        $result_prest = $db->query($prest);
        
       $obj_prestation = $db->fetch_object($result_prest);
        if($charge == 1){
            $charge_salariale = GETPOST('charge_salariale','int');
        if(empty($charge_salariale)){
            $message = 'Le champ "CHARGE SALARIALE" est obligatoire<br>';
        }

        if(empty($message)){
            $sql = "UPDATE ".MAIN_DB_PREFIX."bareme_prestation SET charge=".$charge.", taux_salariale='".$charge_salariale."', taux_patronale='0' WHERE rowid=".$bareme_row;
            $result = $db->query($sql);
            
        }

        }else if($charge == 2){
            $charge_patronale = GETPOST('charge_patronale','int');
            if(empty($charge_patronale)){
                $message = 'Le champ "CHARGE PATRONALE" est obligatoire<br>';
            }
            if(empty($message)){
                $sql = "UPDATE ".MAIN_DB_PREFIX."bareme_prestation SET charge=".$charge.", taux_salariale='0', taux_patronale='".$charge_patronale."' WHERE rowid=".$bareme_row;
                $result = $db->query($sql);
                
            }
        }else{
            $charge_patronale = GETPOST('charge_patronale1','int');
            $charge_salariale = GETPOST('charge_salariale1','int');
            $charge = GETPOST('charge', 'int');

            if(empty($charge_salariale)){
                $message = 'Le champ "CHARGE SALARIALE" est obligatoire<br>';
            }
            if(empty($charge_patronale)){
                $message = 'Le champ "CHARGE PATRONALE" est obligatoire<br>';
            }

            if(empty($message)){
                $sql = "UPDATE ".MAIN_DB_PREFIX."bareme_prestation SET charge=".$charge.", taux_salariale='".$charge_salariale."', taux_patronale='".$charge_patronale."' WHERE rowid=".$bareme_row;
                $result = $db->query($sql);
                
            }
        }

        $rowidcondition =  $bareme_row;
        $fk_convention = GETPOST('conventions', 'array');
        if(!empty($fk_convention)){
		    for($i =0; $i<count($fk_convention); $i++){
				$fk_convention_i = $fk_convention[$i];
				$sql = 'INSERT INTO '.MAIN_DB_PREFIX.'bareme_prestation_convention (fk_condition, fk_convention) VALUES ('.$rowidcondition.','.$fk_convention_i.')';
				$result2 = $db->query($sql);
			}
		}else{

            $sql = 'INSERT INTO '.MAIN_DB_PREFIX.'bareme_prestation_convention (fk_condition, fk_convention) VALUES ('.$rowidcondition.',0)';
			$result2 = $db->query($sql);

		}

        if(empty($message) && $result){
            $action = "detail_prestation";
            $message = 'Barême modifié avec succès';

            //On garde la trace de l'action
            $sql_select = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$user->id;
            $obj = $db->fetch_object($db->query($sql_select));

            $action_effectue = "Modification barème cotisation taux : patro=".($charge_patronale?:0)." salariale=".($charge_salariale?:0);
            $sql_log = 'INSERT INTO '.MAIN_DB_PREFIX.'log (fk_user, nom, prenom, quand, action_effectue, object_concerne)';
            $sql_log .= ' VALUES('.$user->id.', "'.$obj->lastname.'","'.$obj->firstname.'",now(),"'.$action_effectue.'","Modification barème cotisation")';
            $db->query($sql_log);

        }else{
            if(empty($message))
                $message = 'Un problème est survenu';
            else $action = "edit_bareme_row";
        }

        
    }

    if($action == "supprimer_bareme_row"){
        $id_prestation = GETPOST("bareme_row", "int");
        $sql_delete = "DELETE FROM ".MAIN_DB_PREFIX."bareme_prestation WHERE rowid=".$id_prestation;
        $result2 = $db->query($sql_delete);
        if($result2){
            $message = "Barême supprimer avec succès";
            //On garde la trace de l'action
            $sql_select = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$user->id;
            $obj = $db->fetch_object($db->query($sql_select));

            $action_effectue = "Suppression d'un barème cotisation";
            $sql_log = 'INSERT INTO '.MAIN_DB_PREFIX.'log (fk_user, nom, prenom, quand, action_effectue, object_concerne)';
            $sql_log .= ' VALUES('.$user->id.', "'.$obj->lastname.'","'.$obj->firstname.'",now(),"'.$action_effectue.'","Sppression barème cotisation")';
            $db->query($sql_log);
        }
        $action = "detail_prestation";

    }

    if($action == "saveedit"){
        $id_rowid = GETPOST("id_prestation", 'int');

        $libelle = GETPOST('code','alpha');
        $fk_organisme = GETPOST('organisme','int');
        $nature = GETPOST('nature','alpha');

        $desc = GETPOST('desc', 'alpha');
        $affiche_bulletin = GETPOST('affiche_bulletin', 'alpha');
        $bilan = GETPOST('bilan', 'int');

        if(empty($libelle)){
            $message = 'Le champ "Libelle" est obligatoire<br>';
        }

        if(empty($message)){
            $sql = 'UPDATE '.MAIN_DB_PREFIX.'type_prestation SET fk_organisme="'.$fk_organisme.'", code="'.$libelle.'", commentaire="'.$desc.'", nature="'.$nature.'", affiche_bulletin="'.$affiche_bulletin.'", bilan='.$bilan.' WHERE rowid='.$id_rowid;
            $result = $db->query($sql);
            if($result){
                $message = 'Type cotisation modifié avec succès';
                $action = "info";

                //On garde la trace de l'action
            $sql_select = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$user->id;
            $obj = $db->fetch_object($db->query($sql_select));

            $action_effectue = "Modification cotisation ".$libelle."(".$libelle.")";
            $sql_log = 'INSERT INTO '.MAIN_DB_PREFIX.'log (fk_user, nom, prenom, quand, action_effectue, object_concerne)';
            $sql_log .= ' VALUES('.$user->id.', "'.$obj->lastname.'","'.$obj->firstname.'",now(),"'.$action_effectue.'","Modification cotisation")';
            $db->query($sql_log);

            }else{ $message = 'Un problème est survenu';
                $action = "edit";
            }
        }else $action = "edit";
        

    }
    if($action == "info"){
        $id_rowid = GETPOST("id_prestation", 'int');
        if($id_rowid){
            $prest_type = "SELECT * FROM ".MAIN_DB_PREFIX."type_prestation WHERE rowid=".$id_rowid;
            $result_prest = $db->query($prest_type);//= $db->query($covSql);
            $obj_prestation = $db->fetch_object($result_prest);
            $titre = "Edition de la prestation sociale ".$obj_prestation->code;
            print load_fiche_titre($langs->trans($titre), '', '');


            $head = prestation_head($id_rowid);
            print dol_get_fiche_head($head, 'identifiant', "", -1, '');

            $prest_sql = "SELECT * FROM ".MAIN_DB_PREFIX."bareme_prestation WHERE fk_prestation=".$id_rowid;
            $result_prestation = $db->query($prest_sql);//= $db->query($covSql);
            if($db->num_rows($result_prestation) == 0)
                print "<mark><b>Veuillez ajouter un barème à cette Prestation</b></mark>";
        
            print '<table><tr>';
            print '<td style="padding-right : 30px; padding-top: 20px" class="fieldrequired"><label>Code</label></td>';
            print '<td style="padding-top : 20px"><label>'.$obj_prestation->code.'</label></td>';
            print "</tr>";
            print "<tr>";
            print '<td style="padding-right : 30px; padding-top: 20px" class="fieldrequired"><label>Désignation</label></td>';
            print '<td style="padding-top : 20px"><label>'.$obj_prestation->commentaire.'</label></td>';
            print "</tr>";
            print "<tr>";
            print '<td style="padding-right : 30px; padding-top: 20px" class="fieldrequired"><label>Organisme</label></td>';
            print '<td style="padding-top : 20px"><label>';
            $sql = "SELECT * FROM ".MAIN_DB_PREFIX."organisme WHERE rowid=".$obj_prestation->fk_organisme;
            $result_org = $db->query($sql);
            if($result_org){                
                    $obj_org = $db->fetch_object($result_org);
                    print $obj_org->nom_organisme;
                }
            
            
            print '</label></td>';
            print "</tr>";
            print "<tr>";
            print '<td style="padding-right : 30px; padding-top: 20px" class="fieldrequired"><label>Nature</label></td>';
            print "<td style='padding-top : 20px'>".$obj_prestation->nature."</td>";
            print "</tr>";
            print "<tr>";
            print '<td style="padding-right : 30px; padding-top: 20px" class="fieldrequired"><label>Afficher sur bulletin</label></td>';
            print '<td style="padding-top : 20px" >'.$obj_prestation->affiche_bulletin.'</td>';
            print "</tr>";
            print "<tr>";
            print '<td style="padding-right : 30px; padding-top: 20px" class="fieldrequired">Périodicité de la déclaration</td>';
            $bilan = "Par mois";
            /*if($obj_prestation->bilan == 1){
                $bilan = "Par mois";
            }else if ($obj_prestation->bilan == 3){
                $bilan = "Par Trimestre";
            }else{
                $bilan = "Par Semestre";
            }*/
             print '<td style="padding-top : 20px">'.$bilan.'</td>';
            print "</tr>";          
           
            print "</tr>";
            print '<td></td><td style="padding-top : 30px;" ><a href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=prestation&id_prestation='.$obj_prestation->rowid.'&action=edit" class="button">Modifier</a></td>';
            print '</tr>';
            print'</table>';
        }
}

    if($action == "edit"){
        $id_rowid = GETPOST("id_prestation", 'int');
        if($id_rowid){
            print load_fiche_titre($langs->trans("Modification d'une Cotisation"), '', '');
            $head = prestation_head($id_rowid);
            print dol_get_fiche_head($head, 'identifiant', "", -1, '');
            
            $impot_type = "SELECT * FROM ".MAIN_DB_PREFIX."type_prestation WHERE rowid=".$id_rowid;
            $result_impot = $db->query($impot_type);//= $db->query($covSql);
            $obj_prestation = $db->fetch_object($result_impot);

            print '<table><form action="'.$_SERVER["PHP_SELF"].'?id_prestation='.$obj_prestation->rowid.'" method="post">';
            print '<input type="hidden" name="token" value="'.newToken().'">';
            print '<input type="hidden" name="action" value="saveedit">';
            print '<tr>';
            print '<td  style="width: 200px; padding-right: 30px; padding-top : 20px" class=""><label>Code</label></td>';
            print '<td  style="width: 500px; padding-top : 20px"><input style="width: 500px" type="text" name="code" value="'.$obj_prestation->code.'" /></td>';
            print "</tr>";
            print "<tr>";
            print '<td  style="width: 200px; padding-right: 30px; padding-top : 20px" class=""><label>Désignation</label></td>';
            print '<td  style="width: 600px; padding-top : 20px" ><textarea style="width: 550px; height: 50px" type="text" name="desc">'.$obj_prestation->commentaire.'</textarea></td>';
            print "</tr>";
            print "<tr>";
            print '<td  style="width: 200px; padding-right: 30px; padding-top : 20px" class=""><label>Organisme</label></td>';
            print '<td  style="width: 500px; padding-top : 20px" ><select style="width: 500px" name="organisme">';
        
            $sql = "SELECT * FROM ".MAIN_DB_PREFIX."organisme";
            $result_org = $db->query($sql);
            if($result_org){
                $i = 0;
                $num = $db->num_rows($result_org);
                while($i < $num){
                    $obj_org = $db->fetch_object($result_org);
                    if($obj_org->rowid == $obj_prestation->fk_organisme)
                        print "<option value='".$obj_org->rowid."' selected>".$obj_org->nom_organisme."</option>";
                    else print "<option value='".$obj_org->rowid."'>".$obj_org->nom_organisme."</option>";
                    $i++;
                }
            }
            
           print '</td>';
            print "</tr>";
            print "<tr>";
            print '<td  style="width: 200px; padding-right: 30px; padding-top : 20px" class=""><label>Nature</label></td>';
            $obli = "";
            $fac = "";
            if($obj_prestation->nature == "Obligatoire")
                    $obli = "checked";
                else $fac = "checked";
            print '<td  style="padding-top : 20px" > <input id="obli" type="radio" name="nature" value="Obligatoire" '.$obli.'><label for="obli">Obligatoire</label>'.'  ';
                print '<input type="radio" name="nature" id="fac" value="Facultative" '.$fac.'><label for="fac" >Facultative</label></td>';
            
            print "</tr>";
            print "<tr>";
            print '<td  style="width: 200px; padding-right: 30px; padding-top : 20px" class=""><label>Affiche bulletin</label></td>';
            print "<td style='padding-top : 20px' ><select style='width: 500px' name='affiche_bulletin' id='affiche_bulletin' >";
            print '<option value="Oui" selected >Oui</option>';
            /*if($obj_prestation->affiche_bulletin == "Oui")
                print '<option value="Oui" selected >Oui</option>
                    <option value="Non">Non</option>';
             else 
                print '<option value="Oui" >Oui</option>
                <option value="Non" selected >Non</option>';*/
            print "</select></td>";
            print "</tr>";
            print "<tr>";
            print '<td  style="width: 200px; padding-right: 30px; padding-top : 20px" class=""><label>Bilan</label></td>
            <td style="padding-top : 20px">';
            print '<select style="width: 500px;" name="bilan" id="bilan" >;
                    <option value="1" selected >Par Mois</option>';
            /*if($obj_prestation->bilan == 1){
                print '<select style="width: 500px;" name="bilan" id="bilan" >;
                    <option value="1" selected >Par Mois</option>
                    <option value="3">Par Trimestre</option>
                    <option value="6">Par Semestre</option>
            ';
            }else if($obj_prestation->bilan == 3){
                print '<select style="width: 500px;" name="bilan" id="bilan" >;
                <option value="1">Par Mois</option>
                <option value="3" selected>Par Trimestre</option>
                <option value="6">Par Semestre</option>
                ';
            }else{
                print '<select style="width: 500px;" name="bilan" id="bilan" >;
                <option value="1">Par Mois</option>
                <option value="3">Par Trimestre</option>
                <option value="6" selected>Par Semestre</option>
                ';
            }*/
            print '</select></td></tr>';
            
           
            
        
            print '<tr>';
            print '</table>';
            print '<hr>';
            print '
                <div style="text-align: center"; align-items: center; justify-content: center">
                    <input class="button" type="submit" value="Enregistrer" name=""/>
                    </form>
                    <a href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=prestation&action=info&id_prestation='.$id_rowid.'" class="button">Annuler</a></td></tr>
                </div>
                ';
        }
}

if($action == "detail_prestation"){
       // $id_type_prestation = GETPOST("id_prestation", "int");
    $id_prestation = GETPOST("id_prestation", "int");
    $type_taxe = "SELECT rowid, code, nature FROM ".MAIN_DB_PREFIX."type_prestation WHERE rowid=".$id_prestation;
    $result_type_taxe = $db->query($type_taxe);
    $obj_prestation_type = $db->fetch_object($result_type_taxe);

    $titre = "Listes des Barêmes de <mark>".$obj_prestation_type->code."<mark>";
    $nature_prest = $obj_prestation_type->nature;
    print load_fiche_titre($langs->trans($titre), '', '');
    $head = prestation_head($id_prestation);
        print dol_get_fiche_head($head, 'information', "", -1, '');
        print_barre_liste("", $page, $_SERVER["PHP_SELF"], "", "", "", "", "", "", 'bill', 0, dolGetButtonTitle("Créer un nouveau barême", '', 'fa fa-plus-circle', './bareme_prestation.php?mainmenu=paiementsalaire&leftmenu=prestation&idmenu=20077&action=nouveau_bareme_form&id_prestation='.$id_prestation , '', 1), '', 0, 0, 0, 1);

    print '<table style="width : 100%" class="tagtable liste">';
    print '<tr class="liste_titre" >';
    print '<td><label>Charge Salariale</label></td>';
    print '<td ><label>Charge Patronale</label></td>';
    print '<td ><label>Type</label></td>';
    print '<td ><label>Conventions</label></td>';
    print '<td ><label>Opération</label></td></tr>';


    $prest_sql = "SELECT * FROM ".MAIN_DB_PREFIX."bareme_prestation WHERE fk_prestation=".$id_prestation;
        $result_prestation = $db->query($prest_sql);//= $db->query($covSql);

        if($result_prestation){
            $i = 0;
            $num = $db->num_rows($result_prestation);
            while ($i < $num){
                $obj_prestation = $db->fetch_object($result_prestation);

                print '<td >'.$obj_prestation->taux_salariale.'%</td>';
                print '<td >'.$obj_prestation->taux_patronale.'%</td>';
                print '<td >'.$nature_prest.'</td>';
                $condition_type_salarie = "SELECT * FROM ".MAIN_DB_PREFIX."bareme_prestation_convention WHERE fk_condition=".$obj_prestation->rowid;
                $result_condition_categorie = $db->query($condition_type_salarie);
                $convention_array = "";
                if($result_condition_categorie){
                    $j = 0;
                    $jum = $db->num_rows($result_condition_categorie);
                    while($j < $jum){
                        $obj_condition_categorie = $db->fetch_object($result_condition_categorie);
                        if($obj_condition_categorie->fk_convention != 0){
                            $convSql = "SELECT * FROM ".MAIN_DB_PREFIX."convention WHERE rowid=".$obj_condition_categorie->fk_convention;
                            $convResult = $db->query($convSql);
                            $conv = $db->fetch_object($convResult);
                            if($jum == 1 || $j == 0)
                                $convention_array = $conv->nom;
                            else $convention_array = $convention_array."; ".$conv->nom;
                        }else $convention_array = "Toutes";
                    $j ++;
                    }
                    if($jum == 0)
                        $convention_array = "Toutes";
                }
                print '<td  ><label>'.$convention_array.'</label></td>';

                print '<td >';
                if($id_prestation > 5)
                    print '<a class="reposition editfielda" href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=prestation&id_prestation='.$obj_prestation_type->rowid.'&bareme_row='.$obj_prestation->rowid.'&action=supprimer_bareme_row">'.img_delete('', '').'&nbsp;&nbsp;&nbsp;</a>';
                print '<a class="reposition editfielda" href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=prestation&id_prestation='.$obj_prestation_type->rowid.'&bareme_row='.$obj_prestation->rowid.'&action=edit_bareme_row">'.img_edit('', '').'</a></td></tr>';

                $i ++;
                }
            if($num == 0)
                print "<tr><td colspan='5'>Aucun Barême n'est disponible pour cette prestation</td></tr>";
            }else print "<tr><td colspan='5'>Aucun Barême n'est disponible pour cette prestation</td></tr>";
        

    print'</table>';

 }
if($action == "edit_bareme_row"){
    if(empty(GETPOST("id_prestation", "int")))
        $id_type_prestation = GETPOST("id_prestation", "int");
    if(empty(GETPOST("id_bareme", "int")))
        $bareme_row = GETPOST("bareme_row", "int");

    $type_taxe = "SELECT * FROM ".MAIN_DB_PREFIX."type_prestation WHERE rowid=".$id_type_prestation;
    $result_type_taxe = $db->query($type_taxe);
    $obj_prestation_type = $db->fetch_object($result_type_taxe);

    $titre = "Modification d'un bareme ".$obj_prestation_type->code;

    print load_fiche_titre($langs->trans($titre), '', '');
    $head = prestation_head($id_type_prestation);
    print dol_get_fiche_head($head, 'identifiant', "", -1, '');
    print '<table><form action="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=prestation&id_prestation='.$id_type_prestation.'&id_bareme='.$bareme_row.'" method="post">';
    print '<input type="hidden" name="token" value="'.newToken().'">';
    print '<input type="hidden" name="action" value="edit_bareme">'; 
    
    $condition_type_salarie = "SELECT * FROM ".MAIN_DB_PREFIX."bareme_prestation_convention WHERE fk_condition=".$bareme_row;
		$result_condition_categorie = $db->query($condition_type_salarie);
		$convention_array = array();
		if($result_condition_categorie){
			$j = 0;
			$jum = $db->num_rows($result_condition_categorie);
			while($j < $jum){
				$obj_condition_categorie = $db->fetch_object($result_condition_categorie);
				$convention_array[$j] = $obj_condition_categorie->fk_convention;
			$j ++;
			}
		}

    $bar_sql = "SELECT * FROM ".MAIN_DB_PREFIX."bareme_prestation WHERE rowid=".$bareme_row;
    $result_bar = $db->query($bar_sql);
    $obj_bar = $db->fetch_object($result_bar);

    $alltype = array();
    $alltypeRowid = array();

    $covSql = "SELECT * FROM ".MAIN_DB_PREFIX."convention";
    $result = $db->query($covSql);//= $db->query($covSql);

    $alltypeRowid[0] = 0;
    $alltype[0] =  "Tous";
    if($result){
        $i = 0;
        $num = $db->num_rows($result);
        while ($i < $num){
            $obj = $db->fetch_object($result);
            if ($obj)
            {
                $alltypeRowid[$i+1] = $obj->rowid;
                $alltype[$i+1] =  $obj->nom;
                /*print $alltypeRowid[$i].",";
                print $alltype[$i].",";*/
                
                

            }
            $i ++;
        }
    }
    $alltype = array_combine($alltypeRowid, $alltype);
    $monform = new Form($db);
    print '<tr><td  style="padding-top: 20px; padding-right: 30px; padding-bottom: 20px" class="fieldrequired"><label>Conventions</label></td><td style="width: 300px;">';
    print $monform->multiselectarray('conventions', $alltype, $convention_array, null, 0, 'quatrevingtpercent widthcentpercentminusx', 0, 0);
    print '</td></tr>';


    

if($obj_bar->charge == 1){
    print '<tr><td style=" padding-right: 30px"><label>Charge</label></td>';
    print '<td style=" padding: 8px"><select name="charge" id="charge">';
    print '<option value="1" selected >Salariale</option>';
    print '<option value="2">Patronale</option>';
    print '<option value="0">Tous</option></td></tr>';
    
}else if($obj_bar->charge == 2){
    print '<tr><td style=" padding-right: 30px"><label>Charge</label></td>';
    print '<td style=" padding: 8px"><select name="charge" id="charge">';
    print '<option value="1">Salariale</option>';
    print '<option value="2" selected >Patronale</option>';
    print '<option value="0">Tous</option></td></tr>';

}else{
    print '<tr><td style=" padding-right: 30px"><label>Charge</label></td>';
    print '<td style=" padding: 8px"><select name="charge" id="charge">';
    print '<option value="1">Salariale</option>';
    print '<option value="2">Patronale</option>';
    print '<option value="0" selected >Tous</option></td></tr>';
}
print '<tr class="pair"><td align="center" colspan="2" style="padding:20px;" class="fieldrequired"><label>Conditions(taux en pourcentage)</label></td></tr>';

print "</table>";

    print "<table id='salariale'>";
    print '<tr><td style=" padding-right: 30px; padding-bottom: 20px;"><label>Charge Salariale</label></td>';
    print '<td style=" padding-right: 30px; padding-bottom: 20px"><input type="text" name="charge_salariale" value="'.$obj_bar->taux_salariale.'" /></td></tr>';
    print "</table>";

    print "<table id='patronale'>";
    print '<tr><td style=" padding-right: 30px; padding-bottom: 20px;"><label>Charge Patronale</label></td>';
    print '<td style=" padding-right: 30px; padding-bottom: 20px"><input type="text" name="charge_patronale" value="'.$obj_bar->taux_patronale.'" /></td></tr>';
    print "</table>";

    print "<table id='salariale_patronale'>";
    print '<tr><td style=" padding-right: 30px; padding-bottom: 20px;"><label>Charge Salariale</label></td>';
    print '<td style=" padding-right: 30px; padding-bottom: 20px"><input type="text" name="charge_salariale1" value="'.$obj_bar->taux_salariale.'" /></td></tr>';
    print '<td style=" padding-right: 30px; padding-bottom: 20px;"><label>Charge Patronale</label></td>';
    print '<td style=" padding-right: 30px; padding-bottom: 20px"><input type="text" name="charge_patronale1" value="'.$obj_bar->taux_patronale.'" /></td></tr>';
    print "</table>";

    print '<tr>';
    print '<td style=" padding-right: 30px;  padding-top: 30px"></td><td style=" padding-top: 30px"><input class="button" type="submit" value="Enregistrer" name=""/>';
    print'</form>';
    print '<a href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=prestation&action=detail_prestation&id_prestation='.$id_type_prestation.'" class="button">Annuler</a></td></tr>';
    print '<table>';
 }

 if($action == "nouveau_bareme_form"){
    print load_fiche_titre($langs->trans("Ajouter les Barêmes de cette cotisation"), '', '');
    print load_fiche_titre($langs->trans("Information de base"), '', '');
    $id_type_prestation = GETPOST("id_prestation", "int");
    $head = prestation_head($id_type_prestation);
    print dol_get_fiche_head($head, 'information', "", -1, '');
    print '<table><form action="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=prestation&id_prestation='.$id_type_prestation.'" method="post">';
    print '<input type="hidden" name="token" value="'.newToken().'">';
    print '<input type="hidden" name="action" value="add_bareme">';
    
        $alltype = array();
		$alltypeRowid = array();

		$covSql = "SELECT * FROM ".MAIN_DB_PREFIX."convention";
		$result = $db->query($covSql);//= $db->query($covSql);

		$alltypeRowid[0] = 0;
		$alltype[0] =  "Tous";
		if($result){
			$i = 0;
			$num = $db->num_rows($result);
			while ($i < $num){
				$obj = $db->fetch_object($result);
				if ($obj)
				{
					$alltypeRowid[$i+1] = $obj->rowid;
					$alltype[$i+1] =  $obj->nom;
					/*print $alltypeRowid[$i].",";
					print $alltype[$i].",";*/
					
					

				}
				$i ++;
			}
		}
		$alltype = array_combine($alltypeRowid, $alltype);
		$monform = new Form($db);
		print '<tr><td  style="padding-top: 20px; padding-right: 30px; padding-bottom: 20px" class="fieldrequired"><label>Conventions</label></td><td style="width: 300px;">';
		print $monform->multiselectarray('conventions', $alltype, GETPOST('prime_salarie', 'array'), null, 0, 'quatrevingtpercent widthcentpercentminusx', 0, 0);
		print '</td></tr>';

        print '<tr><td style=" padding-right: 30px"><label>Charge</label></td>';
        print '<td style=" padding: 8px"><select name="charge" id="charge">';
        print '<option value="1">Salariale</option>';
        print '<option value="2">Patronale</option>';
        print '<option value="0">Tous</option></td></tr>';
        print '<tr class="pair"><td align="center" colspan="2" style="padding:20px;" class="fieldrequired"><label>Conditions(taux en pourcentage)</label></td></tr>';
        print "</table>";

        /*$prest = "SELECT * FROM ".MAIN_DB_PREFIX."type_prestation WHERE rowid=".$id_type_prestation;
        $result_prest = $db->query($prest);
        $obj_prestation = $db->fetch_object($result_prest);*/

    print "<table id='salariale'>";
        print '<tr><td style=" padding-right: 30px; padding-bottom: 20px;"><label>Charge Salariale</label></td>';
        print '<td style=" padding-right: 30px; padding-bottom: 20px"><input type="text" name="charge_salariale"/></td></tr>';
    print "</table>";
    print "<table id='patronale'>";
        print '<tr><td style=" padding-right: 30px; padding-bottom: 20px;"><label>Charge Patronale</label></td>';
        print '<td style=" padding-right: 30px; padding-bottom: 20px"><input type="text" name="charge_patronale"/></td></tr>';
    print "</table>";
    print "<table id='salariale_patronale'>";
        print '<tr><td style=" padding-right: 30px; padding-bottom: 20px;"><label>Charge Salariale</label></td>';
        print '<td style=" padding-right: 30px; padding-bottom: 20px"><input type="text" name="charge_salariale1"/></td></tr>';

        print '<td style=" padding-right: 30px; padding-bottom: 20px;"><label>Charge Patronale</label></td>';
        print '<td style=" padding-right: 30px; padding-bottom: 20px"><input type="text" name="charge_patronale1"/></td></tr>';
        print "</table>";
       


   
        print '<tr>';
        print '<td style=" padding-right: 30px;  padding-top: 30px"></td><td style=" padding-top: 30px"><input class="button" type="submit" value="Ajouter" name=""/>';
        print'</form>';
        print '<a href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=prestation&action=detail_prestation&id_prestation='.$id_type_prestation.'" class="button">Annuler</a></td></tr>';
        print '<table>';
 }

 
 print "<script>
        var salariale = document.getElementById('salariale');
	    const patronale = document.getElementById('patronale');
	    const salariale_patronale = document.getElementById('salariale_patronale');
        const charge = document.getElementById('charge');

        if(charge.value ==  '1'){
            salariale.style.display = 'block';
            patronale.style.display = 'none';
            salariale_patronale.style.display = 'none';
        }else if(charge.value ==  '2'){
            salariale.style.display = 'none';
            patronale.style.display = 'block';
            salariale_patronale.style.display = 'none';
        }else if(charge.value ==  '0'){
            salariale.style.display = 'none';
            patronale.style.display = 'none';
            salariale_patronale.style.display = 'block';
        }
        
        charge.addEventListener('change',typeApplique);

	    function typeApplique(){
            if(charge.value ==  '1'){
                salariale.style.display = 'block';
                patronale.style.display = 'none';
                salariale_patronale.style.display = 'none';
            }else if(charge.value ==  '2'){
                salariale.style.display = 'none';
                patronale.style.display = 'block';
                salariale_patronale.style.display = 'none';
            }else if(charge.value ==  '0'){
                salariale.style.display = 'none';
                patronale.style.display = 'none';
                salariale_patronale.style.display = 'block';
            }
        }
 
 
 </script>";
    if(!empty($message))
        print "<script>
        $.jnotify('".$message."', {delay : 5000, fadeSpeed: 500});
        </script>";