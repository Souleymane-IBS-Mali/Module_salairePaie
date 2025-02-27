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

 $message = "";
 if(!empty(GETPOST("id_taxe", "int")))
 $id_taxe = GETPOST("id_taxe", "int");

if(!empty(GETPOST("bareme_row", "int")))
 $id_bareme = GETPOST("bareme_row", "int");

 $action = GETPOST('action','alpha');
if(empty($action))	
	$action = 'liste';


    if($action == "activer"){
        $id_grille_bareme = GETPOST('id_grille_bareme','int');

        $sql1 = "UPDATE ".MAIN_DB_PREFIX."bareme_taxe SET actif = 0";

        $sql2 = "UPDATE ".MAIN_DB_PREFIX."bareme_taxe SET actif = 1 WHERE rowid=".$id_grille_bareme;

        if($db->query($sql1) && $db->query($sql2)){
            $message .= 'Basculement sur un autre Barème avec succès';
            //On garde la trace de l'action
            $sql_select = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$user->id;
                $obj = $db->fetch_object($db->query($sql_select));

            $action_effectue = "Basculement sur un autre Barème Taxe";
            $sql_log = 'INSERT INTO '.MAIN_DB_PREFIX.'log (fk_user, nom, prenom, quand, action_effectue, object_concerne)';
            $sql_log .= ' VALUES('.$user->id.', "'.$obj->lastname.'","'.$obj->firstname.'",now(),"'.$action_effectue.'","Baculement")';
            $db->query($sql_log);

        }
            $action = "detail_taxe";

    }

    //ajout d'un barême
    if($action == "add_bareme"){
        if(GETPOST('type_bareme', 'int') == 2){
            $result = null;
            $id_taxe = GETPOST('id_taxe','int');
            $charge = GETPOST('charge','int');
            if($charge == 1){
                $charge_salariale = GETPOST('charge_salariale','int');
                if(empty($charge_salariale)){
                    $message = 'Le champ "CHARGE SALARIALE" est obligatoire<br>';
                }

                if(empty($message)){
                    $charge = GETPOST('charge', 'int');
                    $sql = "INSERT INTO ".MAIN_DB_PREFIX."bareme_taxe2 (fk_taxe, taux_salariale, charge) VALUES ('".$id_taxe."','".$charge_salariale."',".$charge.")";
                    $result = $db->query($sql);
                }

            }else if($charge == 2){
                $charge_patronale = GETPOST('charge_patronale','int');
                if(empty($charge_patronale)){
                    $message = 'Le champ "CHARGE PATRONALE" est obligatoire<br>';
                }

                if(empty($message)){
                    $charge = GETPOST('charge', 'int');
                    $sql = "INSERT INTO ".MAIN_DB_PREFIX."bareme_taxe2 (fk_taxe, taux_patronale, charge) VALUES ('".$id_taxe."','".$charge_patronale."',".$charge.")";
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
                    $sql = "INSERT INTO ".MAIN_DB_PREFIX."bareme_taxe2 (fk_taxe, taux_salariale, taux_patronale, charge) VALUES ('".$id_taxe."','".$charge_salariale."','".$charge_patronale."',".$charge.")";
                    $result = $db->query($sql);
                }

                
            }

            if(empty($message) && $result){
                $action = "detail_taxe";
                $message = 'Barême enregistré avec succès';

                //On garde la trace de l'action
                $sql_select = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$user->id;
                $obj = $db->fetch_object($db->query($sql_select));

                $action_effectue = "Ajout barème taxe taux : patro=".($charge_patronale?:0)." salariale=".($charge_salariale?:0);
                $sql_log = 'INSERT INTO '.MAIN_DB_PREFIX.'log (fk_user, nom, prenom, quand, action_effectue, object_concerne)';
                $sql_log .= ' VALUES('.$user->id.', "'.$obj->lastname.'","'.$obj->firstname.'",now(),"'.$action_effectue.'","Ajout barème taxe")';
                $db->query($sql_log);

            }else{
                if(empty($message))
                    $message = 'Un problème est survenu';
                $action = 'nouveau_bareme_form';
            }
        }else{
            $montant_debut = GETPOST('montant_debut','int');
            $montant_limit = GETPOST('montant_limit','alpha');
            $taux = GETPOST('taux','alpha');
            $type = GETPOST('id_taxe','int');
            $valeur = GETPOST('valeur','int');
            $id_grille_bareme = GETPOST('id_grille_bareme','int');

            if($montant_debut == ""){
                $message = 'Le champ "DE" est obligatoire<br>';
            }

            if($montant_limit==""){
                $message .= 'Le champ "A" est obligatoire<br>';

            }
            if($taux==""){
                $message .= 'Le champ "TAUX" est obligatoire et pas de %<br>';

            }
            if(empty($type)){
                $message .= 'Le champ "TYPE" est obligatoire<br>';

            }
            if($valeur ==''){
                $message .= 'Le champ "VALEUR" est obligatoire<br>';
            }
            if(empty($message)){
                $sql = "INSERT INTO ".MAIN_DB_PREFIX."taxe (montant_debut, montant_limit, taux, fk_type, valeur, fk_bareme) VALUES (".$montant_debut.",'".$montant_limit."','".$taux."',".$type.",".$valeur.",".$id_grille_bareme.")";
                $result = $db->query($sql);

                if($result){
                    $action = "detail_taxe";
                    $message = 'Barême enregistrer avec succès';
        
                    //On garde la trace de l'action
                    $sql_select = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$user->id;
                $obj = $db->fetch_object($db->query($sql_select));

                    $action_effectue = "Ajout Barème taxe de=".$montant_debut." à=".$montant_limit." taux=".$taux." valeur=".$valeur;
                    $sql_log = 'INSERT INTO '.MAIN_DB_PREFIX.'log (fk_user, nom, prenom, quand, action_effectue, object_concerne)';
                    $sql_log .= ' VALUES('.$user->id.', "'.$obj->lastname.'","'.$obj->firstname.'",now(),"'.$action_effectue.'","Ajout bareme taxe")';
                    $db->query($sql_log);
                }else{
                    $message = "Un problème est survenu";
                    $action = "nouveau_bareme_form";
                }
        
            }else {
                $action = "nouveau_bareme_form";

            }
        }

        
    }

    //Edition d'un Bareme
    if($action == "save_edit_bareme_row"){
        if(GETPOST("type_bareme", "int") != 2){
            $id_barem = GETPOST("bareme_row", "int");
            $montant_debut = GETPOST('montant_debut','int');
            $montant_limit = GETPOST('montant_limit','alpha');
            $taux = GETPOST('taux','alpha');
            $type = GETPOST('id_taxe','int');
            $valeur = GETPOST('valeur','alpha');
            $id_grille_bareme = GETPOST('id_grille_bareme','int');

            if($montant_debut == "0")
                $montant_debut = "0";
            if($montant_limit == "0")
                $montant_limit = "0";
            if($taux == "0")
                $taux = "00";

            if($montant_debut == ''){
                $message = 'Le champ "DE" est obligatoire<br>';
            }

            if($montant_limit ==''){
                $message .= 'Le champ "A" est obligatoire<br>';

            }
            if($taux == ''){
                $message .= 'Le champ "TAUX" est obligatoire<br>';

            }
            if(empty($type)){
                $message .= 'Le champ "TYPE" est obligatoire<br>';

            }
            if($valeur == ''){
                $message .= 'Le champ "VALEUR" est obligatoire<br>';
            }
            if(empty($message)){
                $sql_edit = "UPDATE ".MAIN_DB_PREFIX."taxe SET montant_debut=".$montant_debut.", montant_limit='".$montant_limit."', taux='".$taux."', valeur=".$valeur." WHERE rowid=".$id_barem;
                $result = $db->query($sql_edit);

                if($result){
                    $message = 'Barême modifié avec succès';
                    $action = "detail_taxe";

                    //On garde la trace de l'action
                    $sql_select = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$user->id;
                $obj = $db->fetch_object($db->query($sql_select));

                    $action_effectue = "Modification de barème Taxe à : de=".$montant_debut." à=".$montant_limit." taux=".$taux." valeur=".$valeur;
                    $sql_log = 'INSERT INTO '.MAIN_DB_PREFIX.'log (fk_user, nom, prenom, quand, action_effectue, object_concerne)';
                    $sql_log .= ' VALUES('.$user->id.', "'.$obj->lastname.'","'.$obj->firstname.'",now(),"'.$action_effectue.'","Modification de barème taxe")';
                    $db->query($sql_log);
                }
            }else{
                $action = "edit_bareme_form";

            }
        }else{
            $result = null;
            $id_taxe = GETPOST('id_taxe','int');
            $bareme_row = GETPOST('id_bareme','int');
            $charge = GETPOST('charge','int');

            if($charge == 1){
                $charge_salariale = GETPOST('charge_salariale','int');
            if(empty($charge_salariale)){
                $message = 'Le champ "CHARGE SALARIALE" est obligatoire<br>';
            }

            if(empty($message)){
                $sql = "UPDATE ".MAIN_DB_PREFIX."bareme_taxe2 SET charge=".$charge.", taux_salariale='".$charge_salariale."', taux_patronale='0' WHERE rowid=".$bareme_row;
                $result = $db->query($sql);
                
            }

            }else if($charge == 2){
                $charge_patronale = GETPOST('charge_patronale','int');
                if(empty($charge_patronale)){
                    $message = 'Le champ "CHARGE PATRONALE" est obligatoire<br>';
                }
                if(empty($message)){
                    $sql = "UPDATE ".MAIN_DB_PREFIX."bareme_taxe2 SET charge=".$charge.", taux_salariale='0', taux_patronale='".$charge_patronale."' WHERE rowid=".$bareme_row;
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
                    $sql = "UPDATE ".MAIN_DB_PREFIX."bareme_taxe2 SET charge=".$charge.", taux_salariale='".$charge_salariale."', taux_patronale='".$charge_patronale."' WHERE rowid=".$bareme_row;
                    $result = $db->query($sql);
                    
                }
            }

            if(empty($message) && $result){
                $action = "detail_taxe";
                $message = 'Barême modifié avec succès';

                $sql_select = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$user->id;
                $obj = $db->fetch_object($db->query($sql_select));
                //On garde la trace de l'action
                $action_effectue = "Ajout barème taxe taux : patro=".($charge_patronale?:0)." salariale=".($charge_salariale?:0);
                $sql_log = 'INSERT INTO '.MAIN_DB_PREFIX.'log (fk_user, nom, prenom, quand, action_effectue, object_concerne)';
                $sql_log .= ' VALUES('.$user->id.', "'.$obj->lastname.'","'.$obj->firstname.'",now(),"'.$action_effectue.'","Ajout barème taxe")';
                $db->query($sql_log);

            }else{
                if(empty($message))
                    $message = 'Un problème est survenu';
                else $action = "edit_bareme_row";
            }
        }
    
    }
    
    //suppression d'une taxe
    if($action == "supprimer_bareme_row"){
        $id_bareme = GETPOST("bareme_row", "int");

        if(!empty($id_bareme)){
            $sql_som_taxe = "SELECT * FROM ".MAIN_DB_PREFIX."taxe WHERE rowid=".$id_bareme;
            $obj_bar = $db->fetch_object($db->query($sql_som_taxe));

            $sql_som_taxe = "SELECT libelle, commentaire FROM ".MAIN_DB_PREFIX."type_taxe WHERE rowid=".$obj_bar->fk_type;
            $obj_taxe = $db->fetch_object($db->query($sql_som_taxe));

            $sql_delete = "DELETE FROM ".MAIN_DB_PREFIX."taxe WHERE rowid=".$id_bareme;

            $action_effectue = "Suppression du barème de=".$obj_bar->montant_debut." a=".$obj_bar->montant_limit." taux=".$obj_bar->taux." valeur=".$obj_bar->valeur." de la taxe ".$obj_taxe->libelle."(".$obj_taxe->commentaire.")";

        }else{
            $id_bareme = GETPOST("bareme_row2", "int");
            $sql_som_taxe = "SELECT * FROM ".MAIN_DB_PREFIX."bareme_taxe2 WHERE rowid=".$id_bareme;
            $obj_bar = $db->fetch_object($db->query($sql_som_taxe));

            $sql_som_taxe = "SELECT libelle, commentaire FROM ".MAIN_DB_PREFIX."type_taxe WHERE rowid=".$obj_bar->fk_taxe;
            $obj_taxe = $db->fetch_object($db->query($sql_som_taxe));

            $sql_delete = "DELETE FROM ".MAIN_DB_PREFIX."bareme_taxe2 WHERE rowid=".$id_bareme;

            $action_effectue = "Suppression du barème charge patro=".$obj_bar->taux_patronale." charge salarié=".$obj_bar->taux_salariale." de la taxe ".$obj_taxe->libelle."(".$obj_taxe->commentaire.")";

        }

      
        $result2 = $db->query($sql_delete);
        if($result2){
            $message = "Barême supprimer avec succès";

            $sql_select = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$user->id;
                $obj = $db->fetch_object($db->query($sql_select));

                //On garde la trace de l'action
                $sql_log = 'INSERT INTO '.MAIN_DB_PREFIX.'log (fk_user, nom, prenom, quand, action_effectue, object_concerne)';
                $sql_log .= ' VALUES('.$user->id.', "'.$obj->lastname.'","'.$obj->firstname.'",now(),"'.$action_effectue.'","Suppression bareme Taxe")';
                $db->query($sql_log);
        }else $message = "Un problème est survenu";
        $action = "detail_taxe";

    }

    //sauvegarde de la modification de la taxe
    if($action == "saveedit"){
        $id_rowid = GETPOST("id_taxe", 'int');

        $libelle = GETPOST('libelle', 'alpha');
        $fk_organisme = GETPOST('organisme', 'int');
        $desc = GETPOST('desc');
        $affiche_bulletin = GETPOST('affiche_bulletin', 'alpha');

        if(empty($libelle)){
            $message = 'Le champ "Libelle" est obligatoire<br>';
        }
        if(empty($fk_organisme) || $fk_organisme == 0){
            $message .= 'Le champ "ORGANISME" est obligatoire<br>';
        }

        if(empty($message)){
            $sql = "UPDATE ".MAIN_DB_PREFIX."type_taxe SET fk_organisme=".$fk_organisme.", libelle='".$libelle."', commentaire='".$desc."', affiche_bulletin='".$affiche_bulletin."' WHERE rowid=".$id_rowid;
		    $result = $db->query($sql);
            if($result){
                $message = 'Type Imôt modifié avec succès';
                $action = "info";
            }else{
                $message = 'Un problème est survenu';
                $action = "edit";
            }
        }
    }

    //affichage des informations de la taxe
if($action == "info"){
    $id_rowid = GETPOST("id_taxe", "int");
    if($id_rowid){
        print load_fiche_titre($langs->trans("Information de base"), '', '');
        $head = taxe_head($id_rowid);
        print dol_get_fiche_head($head, 'identifiant', "", -1, '');
        
        $grille_bareme = "SELECT * FROM ".MAIN_DB_PREFIX."bareme_taxe WHERE fk_taxe=".$id_rowid;
        $result_grille_bareme = $db->query($grille_bareme);
        if($db->num_rows($result_grille_bareme) == 0){
            $grille_bareme = "SELECT * FROM ".MAIN_DB_PREFIX."bareme_taxe2 WHERE fk_taxe=".$id_rowid;
            $result_grille_bareme = $db->query($grille_bareme);
            if($db->num_rows($result_grille_bareme) == 0)
                print "<mark><b>Veuillez ajouter un barème à cette Taxe</b></mark>";
        }

        $impot_type = "SELECT * FROM ".MAIN_DB_PREFIX."type_taxe WHERE rowid=".$id_rowid;
        $result_impot = $db->query($impot_type);//= $db->query($covSql);
        $obj_impot = $db->fetch_object($result_impot);        
        print '<table class="">';
        print '<tr>';
        print '<td style="width: 200px; padding: 15px;" class="fieldrequired"><label>Libellé</label></td>';
        print '<td style="width: 200px;" ><label>'.$obj_impot->libelle.'</label></td>';
        print '</tr>';
        print '<tr>';
        print '<td style="width: 200px; padding: 15px;" class="fieldrequired"><label>Description</label></td>';
        print '<td style="width: 200px;" ><label>'.$obj_impot->commentaire.'</label></td>';
        print '</tr>';
        print '<tr><td style="width: 200px; padding: 15px;" class="fieldrequired">Type barème</td>';
        $type_bareme = "Liste";
        if($obj_impot->type_bareme == 2)
            $type_bareme = "Pourcentage";
        print '<td style="width: 200px;">'.$type_bareme.'</td></tr>';

        print '<tr>';
        print '<td style="width: 200px; padding: 15px;" class="fieldrequired"><label>Organisme</label></td>';
        print '<td style="width: 200px;" ><label>';
        $sql = "SELECT * FROM ".MAIN_DB_PREFIX."organisme WHERE rowid=".$obj_impot->fk_organisme;
        $result_org = $db->query($sql);
        $obj_org = $db->fetch_object($result_org);
        print $obj_org->nom_organisme;  
       print '</label></td>';
        print '</tr>';
        print '<tr>';
        print '<td style="width: 200px; padding: 15px;" class="fieldrequired"><label>Afficher sur bulletin</label></td>';
        print '<td>'.$obj_impot->affiche_bulletin.'</td>';
        print '</tr>';
        
        print '<tr>';
    
        print '<td></td><td style=" padding-top: 30px; width: 200px;"><a href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=taxe&id_taxe='.$obj_impot->rowid.'&action=edit" class="button">Modifier</a></td></tr>';
        print'</table></form>';
    }
}

//edition de la taxe
if($action == "edit"){
    $id_rowid = GETPOST("id_taxe", "int");
    if($id_rowid){
        print load_fiche_titre($langs->trans("Modification d'une taxe"), '', '');
        $head = taxe_head($id_rowid);
        print dol_get_fiche_head($head, 'identifiant', "", -1, '');
        
        $impot_type = "SELECT * FROM ".MAIN_DB_PREFIX."type_taxe WHERE rowid=".$id_rowid;
        $result_impot = $db->query($impot_type);//= $db->query($covSql);
        $obj_impot = $db->fetch_object($result_impot);

        print '<br><table><form action="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=taxe&id_taxe='.$obj_impot->rowid.'" method="post">';
        print '<input type="hidden" name="token" value="'.newToken().'">';
        print '<input type="hidden" name="action" value="saveedit">';
        print '<tr>';
        print '<td style="width: 200px; padding-bottom: 20px; padding-right: 30px" class=""><label>Libellé</label></td>';
        print '<td style="width: 500px; padding-bottom: 20px; padding-right: 30px"><input style="width: 500px" type="text" name="libelle" value="'.$obj_impot->libelle.'" /></td>';
        print '</tr>';
        print '<tr>';
        print '<td style="width: 200px; padding-bottom: 20px; padding-right: 30px" class=""><label>Description</label></td>';
        print '<td style="width: 600px; padding-bottom: 20px; padding-right: 30px"><textarea style="width: 550px; height: 50px" type="text" name="desc">'.$obj_impot->commentaire.'</textarea></td>';
        print '</tr>';
        print '<tr>';
        print '<td style="width : 200px; padding-bottom: 20px; padding-right: 30px" class=""><label>Organisme</label></td>';
        print '<td style="width: 500px; padding-bottom: 20px; padding-right: 30px"><select style="width: 500px" name="organisme">';
    
        $sql = "SELECT * FROM ".MAIN_DB_PREFIX."organisme";
        $result_org = $db->query($sql);
        if($result_org){
            $i = 0;
            $num = $db->num_rows($result_org);
            while($i < $num){
                $obj_org = $db->fetch_object($result_org);
                if($obj_org->rowid == $obj_impot->fk_organisme)
                    print "<option value='".$obj_org->rowid."' selected>".$obj_org->nom_organisme."</option>";
                else print "<option value='".$obj_org->rowid."'>".$obj_org->nom_organisme."</option>";
                $i++;
            }
        }
        
       print '</td>';
        print '</tr>';
        print '<tr>';
        print '<td style="width: 200px; padding-bottom: 20px; padding-right: 30px" class=""><label>Afficher sur bulletin</label></td>';
        print "<td><select style='width: 500px' name='affiche_bulletin' id='affiche_bulletin' >";
        if($obj_impot->affiche_bulletin == "Oui")
         print '<option value="Oui" selected >Oui</option>
                 <option value="Non">Non</option>';
     else 
         print '<option value="Oui" >Oui</option>
             <option value="Non" selected >Non</option>';
     print "</select></td>";

        print '</tr>';
        print '</table>';
        print '<hr>';
        print '
            <div style="text-align: center; align-items: center; justify-content: center">
                <input class="button" type="submit" value="Enregistrer" name=""/>
                </form>
                <a href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=taxe&action=info&id_taxe='.$id_rowid.'" class="button">Annuler</a></td></tr>
            </div>
        ';
    }
}

//Bareme d'une taxe
if($action == "detail_taxe"){
        $id_type_taxe = GETPOST("id_taxe", "int");
    $id_taxe = GETPOST("id_taxe", "int");
    $type_taxe = "SELECT * FROM ".MAIN_DB_PREFIX."type_taxe WHERE rowid=".$id_taxe;
    $result_type_taxe = $db->query($type_taxe);
    $obj_impot_type = $db->fetch_object($result_type_taxe);

    $titre = "Listes des Barêmes de <mark>".$obj_impot_type->libelle."<mark>";

    print load_fiche_titre($langs->trans($titre), '', '');
    $head = taxe_head($id_type_taxe);
        print dol_get_fiche_head($head, 'information', "", -1, '');
    if($obj_impot_type->type_bareme != 2){
        print '<table class="tagtable liste">';
        print '<tr class="liste_titre"><td class="liste_titre" style="padding: 20px; width : 5%;" >Nom</td><td class="liste_titre" style="padding: 20px; width : 5%;" >Opération</td></tr>';

            $actl[0] = img_picto($langs->trans("Disabled"), 'switch_off', 'class="size15x"');
            $actl[1] = img_picto("Activé", 'switch_on', 'class="size15x"');
            $acts[0] = "activer";
            $acts[1] = "desactiver";
            $url = $_SERVER["PHP_SELF"];

            $grille_bareme = "SELECT * FROM ".MAIN_DB_PREFIX."bareme_taxe WHERE fk_taxe=".$id_taxe;
            $result_grille_bareme = $db->query($grille_bareme);
            if($result_grille_bareme){
                $a = 0;
                $num_b = $db->num_rows($result_grille_bareme);
                while ($a < $num_b) {
                    $obj_grille_bareme = $db->fetch_object($result_grille_bareme);
                    if( $obj_grille_bareme->actif == 1)
                        $id_grille_bareme = $obj_grille_bareme->rowid;
                        if($num_b > 1){
                            if($obj_grille_bareme->actif == 1){
                                print '<tr class="impair"><td align="left" style="padding: 10px; width : 5%;"><b>'.$obj_grille_bareme->libelle.'<b></td>';
                                print'<td>'.$actl[$obj_grille_bareme->actif].'</td>';
                            }else{
                                print '<tr class="impair"><td align="left" style="padding: 10px; width : 5%;"><b>'.$obj_grille_bareme->libelle.'<b></td>';
                                print'<td><a class="reposition" href="'.$url.'?mainmenu=paiementsalaire&leftmenu=taxe&action='.$acts[$obj_grille_bareme->actif].'&id_taxe='.$id_taxe.'&id_grille_bareme='.$obj_grille_bareme->rowid.'&token='.newToken().'">'.$actl[$obj_grille_bareme->actif].'</a></td>';
                            }
                        }else{
                            print '<tr class="impair"><td align="left" style="padding: 10px; width : 5%;"><b>'.$obj_grille_bareme->libelle.'<b></td>';
                            print'<td><a class="reposition" href="#">'.$actl[$obj_grille_bareme->actif].'</a></td>';
    
                        }
                            print '</td></tr>'; 
                        $a ++;
                }
            }
            print '</table>';

        print_barre_liste("", $page, $_SERVER["PHP_SELF"], "", "", "", "", "", "", 'bill', 0, dolGetButtonTitle("Créer un nouveau barême", '', 'fa fa-plus-circle', './bareme_taxe.php?mainmenu=paiementsalaire&leftmenu=taxe&idmenu=20074&action=nouveau_bareme_form&id_taxe='.$id_taxe.'&id_grille_bareme='.$id_grille_bareme , '', 1), '', 0, 0, 0, 1);

    print '<table class="tagtable liste">';
    print '<tr class="pair"><td align="center" style="background-color: #6f89bd; color: white; width: 15%;"><label>Tranches</label></td>';
    print '<td align="center" style="width: 20%; background-color: #6f89bd; color: white;"><label>De</label></td>';
    print '<td align="center" style="width: 20%; background-color: #6f89bd; color: white;"><label>A</label></td>';
    print '<td align="center" style="width: 20%; background-color: #6f89bd; color: white;"><label>Taux(%)</label></td>';
    print '<td align="center" style="width: 20%; background-color: #6f89bd; color: white;"><label>Valeur</label></td>';
    print '<td align="center" style="width: 20%; background-color: #6f89bd; color: white;"><label>Opération</label></td></tr>';

    
    $impot = "SELECT * FROM ".MAIN_DB_PREFIX."taxe WHERE fk_type=".$id_taxe." AND fk_bareme=".$id_grille_bareme;
    $result_impot = $db->query($impot);//= $db->query($covSql);

        if($result_impot){
            $i = 0;
            $num = $db->num_rows($result_impot);
            while ($i < $num){
                $obj_impot = $db->fetch_object($result_impot);

                print '<tr class="impair"><td align="center">'.($i + 1).'</td>';
                print '<td align="center">'.$obj_impot->montant_debut.'</td>';
                print '<td align="center">'.$obj_impot->montant_limit.'</td>';
                print '<td align="center">'.$obj_impot->taux.'</td>';
                print '<td align="center">'.$obj_impot->valeur.'</td>';


                $type_taxe = "SELECT * FROM ".MAIN_DB_PREFIX."type_taxe WHERE rowid=".$obj_impot->fk_type;
                $result_type_taxe = $db->query($type_taxe);
                $obj_impot_type = $db->fetch_object($result_type_taxe);
                print '<td align="center">';
                print '<a class="reposition editfielda" href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=taxe&id_taxe='.$obj_impot_type->rowid.'&bareme_row='.$obj_impot->rowid.'&id_grille_bareme='.$id_grille_bareme.'&action=edit_bareme_form">'.img_edit('Modifier', '').'&nbsp;&nbsp;</a>';
                if($i>5)
                    print '<a class="reposition editfielda" href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=taxe&id_taxe='.$obj_impot_type->rowid.'&bareme_row='.$obj_impot->rowid.'&action=supprimer_bareme_row">'.img_delete('Supprime', '').'</a>';
                
                print '</tr>';


                $i ++;
                }
                
            }else print "<tr><td colspan='6'>Aucun Barême n'est disponible pour cette taxe</td></tr>";
        

    print'</table>';
        }else{
            print_barre_liste("", $page, $_SERVER["PHP_SELF"], "", "", "", "", "", "", 'bill', 0, dolGetButtonTitle("Créer un nouveau barême", '', 'fa fa-plus-circle', './bareme_taxe.php?mainmenu=paiementsalaire&leftmenu=taxe&idmenu=20074&action=nouveau_bareme_form&id_taxe='.$id_taxe.'&id_grille_bareme='.$id_grille_bareme , '', 1), '', 0, 0, 0, 1);

            print '<table style="width : 100%" class="tagtable liste">';
            print '<tr class="liste_titre" >';
            print '<td><label>Charge Salariale</label></td>';
            print '<td ><label>Charge Patronale</label></td>';
            print '<td ><label>Opération</label></td></tr>';


            $prest_sql = "SELECT * FROM ".MAIN_DB_PREFIX."bareme_taxe2 WHERE fk_taxe=".$id_taxe;
                $result_taxe = $db->query($prest_sql);//= $db->query($covSql);

                if($result_taxe){
                    $i = 0;
                    $num = $db->num_rows($result_taxe);
                    while ($i < $num){
                        $obj_taxe = $db->fetch_object($result_taxe);

                        print '<td >'.$obj_taxe->taux_salariale.'%</td>';
                        print '<td >'.$obj_taxe->taux_patronale.'%</td>';
                        print '<td >';
                        if($i>0)
                            print '<a class="reposition editfielda" href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=taxe&id_taxe='.$id_taxe.'&bareme_row2='.$obj_taxe->rowid.'&action=supprimer_bareme_row">'.img_delete('', '').'&nbsp;&nbsp;&nbsp;</a>';
                        print '<a class="reposition editfielda" href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=taxe&id_taxe='.$id_taxe.'&bareme_row='.$obj_taxe->rowid.'&action=edit_bareme_form">'.img_edit('', '').'</a></td></tr>';

                        $i ++;
                        }
                    //if($num == 0)
                       // print "<tr><td colspan='5'>Aucun Barême n'est disponible pour cette taxe</td></tr>";
                    }else print "<tr><td colspan='4'>Aucun Barême n'est disponible pour cette taxe</td></tr>";
                

            print'</table>';
        }

 }
//Ajou de bareme
 if($action == "nouveau_bareme_form"){
    $id_taxe = GETPOST("id_taxe", "int");
    $id_grille_bareme = GETPOST("id_grille_bareme", "int");

    $type_taxe = "SELECT * FROM ".MAIN_DB_PREFIX."type_taxe WHERE rowid=".$id_taxe;
    $result_type_taxe = $db->query($type_taxe);
    $obj_impot_type = $db->fetch_object($result_type_taxe);

    $titre = "Ajouter des Barêmes ".$obj_impot_type->libelle;
    print load_fiche_titre($langs->trans($titre), '', '');
        print load_fiche_titre($langs->trans("Information de base"), '', '');
        $id_type_taxe = GETPOST("id_taxe", "int");
        $head = taxe_head($id_type_taxe);
        print dol_get_fiche_head($head, 'information', "", -1, '');
    if($obj_impot_type->type_bareme != 2){
        print '<table><form action="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=taxe&id_taxe='.$id_type_taxe.'&id_grille_bareme='.$id_grille_bareme.'" method="post">';
        print '<input type="hidden" name="token" value="'.newToken().'">';
        print '<input type="hidden" name="action" value="add_bareme">';
        print '<tr><td style=" padding-right: 20px;"><label>De</label></td>';
        print '<td style=" padding-right: 20px"><label>A</label></td>';
        print '<td style=" padding-right: 20px"><label>Taux</label></td>';
        print '<td style=" padding-right: 20px"><label>Valeur</label></td>';
        print '<td style=" padding-right: 20px"></td></tr>';
        print '<tr><td style=" padding-right: 20px"><input type="text" name="montant_debut"/></td>';
        print '<td style=" padding-right: 20px"><input type="text" name="montant_limit"/></td>';
        print '<td style=" padding-right: 20px"><input type="text" name="taux"/></td>';
        print '<td style=" padding-right: 20px"><input type="text" name="valeur"/></td>';

        print '<td style=" padding-right: 30px;  padding-top: 30px"></td><td style=" padding-top: 30px"><input class="button" type="submit" value="Enregistrer" name=""/>';
        print'</form>';
        print '<a href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=taxe&action=detail_taxe&id_taxe='.$id_taxe.'&id_grille_bareme='.$id_grille_bareme.'" class="button">Annuler</a></td></tr>';
        print '<table>';
    }else{
        print '<table><form action="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=taxe&id_taxe='.$id_taxe.'" method="post">';
    print '<input type="hidden" name="token" value="'.newToken().'">';
    print '<input type="hidden" name="action" value="add_bareme">';
    print '<input type="hidden" name="type_bareme" value=2>';
    
        print '<tr><td style=" padding-right: 30px"><label>Charge</label></td>';
        print '<td style=" padding: 8px"><select name="charge" id="charge">';
        print '<option value="1">Salariale</option>';
        print '<option value="2">Patronale</option>';
        print '<option value="0">Tous</option></td></tr>';
        print '<tr class="pair"><td align="center" colspan="2" style="padding:20px;" class="fieldrequired"><label>Conditions(taux en pourcentage)</label></td></tr>';
        print "</table>";

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
        print '<a href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=taxe&action=detail_prestation&id_prestation='.$id_type_prestation.'" class="button">Annuler</a></td></tr>';
        print '<table>';
    }

 }

 //Ajou de bareme
 if($action == "edit_bareme_form"){
    if(!empty(GETPOST("id_taxe", "int")))
        $id_taxe = GETPOST("id_taxe", "int");
        $id_grille_bareme = GETPOST("id_grille_bareme", "int");

    if(!empty(GETPOST("bareme_row", "int")))
        $id_bareme = GETPOST("bareme_row", "int");

    $type_taxe = "SELECT * FROM ".MAIN_DB_PREFIX."type_taxe WHERE rowid=".$id_taxe;
    $result_type_taxe = $db->query($type_taxe);
    $obj_impot_type = $db->fetch_object($result_type_taxe);
    $titre = "Modifications d'un Barême ".$obj_impot_type->libelle;
    print load_fiche_titre($langs->trans($titre), '', '');
        print load_fiche_titre($langs->trans("Information de base"), '', '');
        $id_type_taxe = GETPOST("id_taxe", "int");
        $head = taxe_head($id_type_taxe);
        print dol_get_fiche_head($head, 'information', "", -1, '');
    if($obj_impot_type->type_bareme !=2){
        print '<table><form action="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=taxe&id_taxe='.$id_type_taxe.'&bareme_row='.$id_bareme.'&id_grille_bareme='.$id_grille_bareme.'" method="post">';
        print '<input type="hidden" name="token" value="'.newToken().'">';
        print '<input type="hidden" name="action" value="save_edit_bareme_row">';
        print '<tr><td style=" padding-right: 20px;"><label>De</label></td>';
        print '<td style=" padding-right: 20px"><label>A</label></td>';
        print '<td style=" padding-right: 20px"><label>Taux</label></td>';
        print '<td style=" padding-right: 20px"><label>Valeur</label></td>';
        print '<td style=" padding-right: 20px"></td></tr>';

        $sql_bar = "SELECT * FROM ".MAIN_DB_PREFIX."taxe WHERE rowid=".$id_bareme." AND fk_bareme=".$id_grille_bareme;
        $result_bar = $db->query($sql_bar);
        $obj_bareme = $db->fetch_object($result_bar);
        print '<tr><td style=" padding-right: 20px"><input type="text" value="'.$obj_bareme->montant_debut.'" name="montant_debut"/></td>';
        print '<td style=" padding-right: 20px"><input type="text" value="'.$obj_bareme->montant_limit.'" name="montant_limit"/></td>';
        print '<td style=" padding-right: 20px"><input type="text" value="'.$obj_bareme->taux.'" name="taux"/></td>';
        print '<td style=" padding-right: 20px"><input type="text" value="'.$obj_bareme->valeur.'" name="valeur"/></td>';

        print '<td style=" padding-right: 30px;  padding-top: 30px"></td><td style=" padding-top: 30px"><input class="button" type="submit" value="Enregistrer" name=""/>';
        print'</form>';
        print '<a href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=taxe&action=detail_taxe&id_taxe='.$id_taxe.'&id_grille_bareme='.$id_grille_bareme.'" class="button">Annuler</a></td></tr>';
        print '<table>';
    }else{
            print '<table><form action="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=taxe&id_taxe='.$id_taxe.'&id_bareme='.$id_bareme.'" method="post">';
            print '<input type="hidden" name="token" value="'.newToken().'">';
            print '<input type="hidden" name="action" value="save_edit_bareme_row">';
            print '<input type="hidden" name="type_bareme" value=2>'; 


            $bar_sql = "SELECT * FROM ".MAIN_DB_PREFIX."bareme_taxe2 WHERE rowid=".$id_bareme;
            $result_bar = $db->query($bar_sql);
            $obj_bar = $db->fetch_object($result_bar);           

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
            print '<a href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=taxe&action=detail_prestation&id_prestation='.$id_type_prestation.'" class="button">Annuler</a></td></tr>';
            print '<table>';
    }
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
 //affichage des notification
    if(!empty($message))
        print "<script>
        $.jnotify('".$message."', {delay : 5000, fadeSpeed: 500});
        </script>";