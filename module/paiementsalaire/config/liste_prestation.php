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

 $action = GETPOST('action','alpha');
if(empty($action))	
	$action = 'liste';

 //les prestations par defaut au Mali
 $sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."type_prestation";
 $res = $db->query($sql);
 if($res){
   $nb = $db->num_rows($res);
   if($nb < 1){
    //type prestations
    $sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."type_prestation (fk_organisme, code, commentaire, nature, affiche_bulletin, bilan) VALUES (3,'ATMP','Accidents de Travail et des Maladies Professionnelles','Obligatoire','Oui',1)";
    $db->query($sql_insert);
    //barème prestation
    $sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."bareme_prestation (fk_prestation, charge, taux_salariale, taux_patronale) VALUES(1,2,'0','4')";
    $db->query($sql_insert);
    $sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."bareme_prestation (fk_prestation, charge, taux_salariale, taux_patronale) VALUES(1,2,'0','2')";
    $db->query($sql_insert);

    //convention concernée par ces barèmes
    $sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."bareme_prestation_convention (fk_condition, fk_convention) VALUES(1, 1)";
    $db->query($sql_insert);
    $sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."bareme_prestation_convention (fk_condition, fk_convention) VALUES(2, 2)";
    $db->query($sql_insert);
    $sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."bareme_prestation_convention (fk_condition, fk_convention) VALUES(2, 3)";
    $db->query($sql_insert);
    $sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."bareme_prestation_convention (fk_condition, fk_convention) VALUES(2, 4)";
    $db->query($sql_insert);
    $sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."bareme_prestation_convention (fk_condition, fk_convention) VALUES(2, 5)";
    $db->query($sql_insert);
    
  }

  if($nb < 2){
    $sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."type_prestation (fk_organisme, code, commentaire, nature, affiche_bulletin, bilan) VALUES (3,'Prestations Familiales','Prestations Familiales','Obligatoire','Oui',1)";
    $db->query($sql_insert);

    $sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."bareme_prestation (fk_prestation, charge, taux_salariale, taux_patronale) VALUES(2,2,'0','8')";
    $db->query($sql_insert);

    //convention concernée par ce barème
    $sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."bareme_prestation_convention (fk_condition, fk_convention) VALUES(3, 0)";
    $db->query($sql_insert);
    
  }

  if($nb < 3){
    $sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."type_prestation (fk_organisme, code, commentaire, nature, affiche_bulletin, bilan) VALUES (3,'Retraite','Retraite','Obligatoire','Oui',1)";
    $db->query($sql_insert);

    $sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."bareme_prestation (fk_prestation, charge, taux_salariale, taux_patronale) VALUES(3,0,'3.6','3.4')";
    $db->query($sql_insert);

    //convention concernée par ce barème
    $sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."bareme_prestation_convention (fk_condition, fk_convention) VALUES(4, 0)";
    $db->query($sql_insert);

  }
  
   if($nb < 4){
    $sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."type_prestation (fk_organisme, code, commentaire, nature, affiche_bulletin, bilan) VALUES (3,'Invalidité – allocation de survivant','Invalidité – allocation de survivant','Obligatoire','Oui',1)";
    $db->query($sql_insert);

    $sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."bareme_prestation (fk_prestation, charge, taux_salariale, taux_patronale) VALUES(4,2,'0','2')";
    $db->query($sql_insert);
    //convention concernée par ce barème
    $sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."bareme_prestation_convention (fk_condition, fk_convention) VALUES(5, 0)";
    $db->query($sql_insert);

  }

   
   if($nb < 5){
    $sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'type_prestation (fk_organisme, code, commentaire, nature, affiche_bulletin, bilan) VALUES (3,"A.N.P.E","Agence Nationale Pour l\'Emploi","Obligatoire","Oui",1)';
    $db->query($sql_insert);

    $sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."bareme_prestation (fk_prestation, charge, taux_salariale, taux_patronale) VALUES(5,2,'0','1')";
    $db->query($sql_insert);

    //convention concernée par ce barème
    $sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."bareme_prestation_convention (fk_condition, fk_convention) VALUES(6, 0)";
    $db->query($sql_insert);
    
  }

  if($nb < 6){
    $sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."type_prestation (fk_organisme, code, commentaire, nature, affiche_bulletin, bilan) VALUES (3,'AMO','Assurance Maladie Obligatoire','Obligatoire','Oui',1)";
    $db->query($sql_insert);

    $sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."bareme_prestation (fk_prestation, charge, taux_salariale, taux_patronale) VALUES(6,0,'3.06','3.50')";
    $db->query($sql_insert);

    //convention concernée par ce barème
    $sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."bareme_prestation_convention (fk_condition, fk_convention) VALUES(7, 0)";
    $db->query($sql_insert);
  }
   
 }

    if($action == "suppression"){
        $id_prestation = GETPOST("id_prestation", "int");

            $sql_som_prest = "SELECT code, commentaire FROM ".MAIN_DB_PREFIX."type_prestation WHERE rowid=".$id_prestation;
            $obj_prest = $db->fetch_object($db->query($sql_som_prest));

            $sql_delete = "DELETE FROM ".MAIN_DB_PREFIX."type_prestation WHERE rowid=".$id_prestation;
            $result = $db->query($sql_delete);
            $sql_delete = "DELETE FROM ".MAIN_DB_PREFIX."bareme_prestation WHERE fk_type=".$id_prestation;
            $result2 = $db->query($sql_delete);
            if($result){
                $message = "Cotisation supprimer avec succès";

                
                $sql_select = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$user->id;
                $obj = $db->fetch_object($db->query($sql_select));

                //On garde la trace de l'action
                $sql_select = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$user->id;
                $obj = $db->fetch_object($db->query($sql_select));

                $action_effectue = "Suppression de la cotisation ".$obj_prest->code."(".$obj_prest->commentaire.")";
                $sql_log = 'INSERT INTO '.MAIN_DB_PREFIX.'log (fk_user, nom, prenom, quand, action_effectue, object_concerne)';
                $sql_log .= ' VALUES('.$user->id.', "'.$obj->lastname.'","'.$obj->firstname.'",now(),"'.$action_effectue.'","Suppression Cotisation")';
                $db->query($sql_log);
            }else $message = "Un problème est survenu";
            
        
        $action = "liste";

    }


    if($action == "add_type_prestation"){
        $code = GETPOST('code', 'alpha');

        $organisme = GETPOST('organisme','int');
        $desc = GETPOST('desc', 'alpha');
        $affiche_bulletin = GETPOST('affiche_bulletin', 'alpha');
        $nature = GETPOST('nature', 'alpha');
        $bilan = GETPOST('bilan', 'int');

        if(empty($code)){
            $message = "Le champ 'CODE' est obligatoire<br>";
        }
        
        if(empty($message)){
            $sql = "INSERT INTO ".MAIN_DB_PREFIX."type_prestation (fk_organisme, code, commentaire, nature, affiche_bulletin) VALUES ('".$organisme."','".$code."','".$desc."','".$nature."','".$affiche_bulletin."')";
            $result = $db->query($sql);
            //print $sql;
            print $db->error();
            if($result){

                $result = $db->query("SELECT LAST_INSERT_ID() as rowid;");
                $obj = $db->fetch_object($result);
                $rowid_prestation =  $obj->rowid;
                
                //On garde la trace de l'action
                $sql_select = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$user->id;
                $obj = $db->fetch_object($db->query($sql_select));

                $action_effectue = "Ajout Cotisation ".$code."(".$desc.")";
                $sql_log = 'INSERT INTO '.MAIN_DB_PREFIX.'log (fk_user, nom, prenom, quand, action_effectue, object_concerne)';
                $sql_log .= ' VALUES('.$user->id.', "'.$obj->lastname.'","'.$obj->firstname.'",now(),"'.$action_effectue.'","Ajout cotisation")';
                $db->query($sql_log);

                print "<script>
                    window.location.href = 'bareme_prestation?mainmenu=paiementsalaire&leftmenu=prestation&id_prestation=".$rowid_prestation."&action=info';
                </script>";
                $message = "Prestation sociale enregistrer avec succès";
            }else{
                $message = "Un problème est survenu";
                $action = "create";
            }
        }

    }

    

    
if($action == "create"){
        print load_fiche_titre($langs->trans("Création d'une nouvelle Cotisation"), '', '');
        print '<span style="color: red">*</span> <i>Tous les champs sont obligatoires</i>';
        print '<hr><br>';
        print '<table><form action="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=prestation" method="post">';
        print '<input type="hidden" name="token" value="'.newToken().'">';
        print '<input type="hidden" name="action" value="add_type_prestation">';
        print '<tr>';
        print '<td style="width: 200px; padding-bottom: 20px; padding: "><label>Code</label></td>';
        print '<td style="width: 500px; padding-bottom: 20px; padding: "><input style="width: 500px" type="text" name="code" value="'.GETPOST("code", "alpha").'"/></td></tr>';

        print '<tr><td style="width: 200px; padding-bottom: 20px; padding: "><label>Désignation</label></td>';
        print '<td style="width: 600px; padding-bottom: 20px; padding: "><textarea style="width: 550px; height: 50px"type="text" name="desc">'.GETPOST("desc", "alpha").'</textarea></td></tr>';
        print '<tr><td style="width: 200px; padding-bottom: 20px; padding-right: "><label>Nature</label></td>';
        print '<td style="width: 500px; padding-bottom: 20px; padding-right: "> <input style="width: " id="obli" type="radio" name="nature" value="obligatoire" checked><label for="obli">Obligatoire</label>'. '     ';
        print '<input type="radio" name="nature" id="fac" value="facultative"><label for="fac">Facultative</label></td>';

        print '</tr>';

        print '<tr><td style="width: 200px; padding-bottom: 20px; padding: "><label>Organisme</label></td>';
        print '<td style="width: 500px; padding-bottom: 20px; padding: "><select style="width: 500px; margin-bottom: 10px" name="organisme">';
        
        $sql = "SELECT * FROM ".MAIN_DB_PREFIX."organisme";
        $result_org = $db->query($sql);
        if($result_org){
            $i = 0;
            $num = $db->num_rows($result_org);
            while($i < $num){
                $obj_org = $db->fetch_object($result_org);
                if($i == 0)
                    print "<option value='".$obj_org->rowid."' selected>".$obj_org->nom_organisme."</option>";
                else print "<option value='".$obj_org->rowid."'>".$obj_org->nom_organisme."</option>";
                $i++;
            }
        }
        
        
       print '</td></tr>';

       print '<tr ><td style="width: 200px; padding-bottom: 20px;padding-top: 10px; padding-right: 30px" ><label>Afficher sur bulletin de paye</label></td><td style=" padding-bottom: 20px;padding-top: 10px">';
	
	print '<select style="width: 500px;" name="affiche_bulletin" id="affiche_bulletin" >;
			<option value="Oui" selected >Oui</option>
            <option value="Non">Non</option>';
			
	print '</td></tr>';

    print '</table>';
    print '<hr>';

    print '
        <div style="text-align: center"; align-items: center; justify-content: center">
            <td style=" padding-right: 30px;  padding-top: 30px"></td><td style=" padding-top: 30px"><input class="button" type="submit" value="Enregistrer" name=""/>
            </form>
            <a href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=prestation&action=liste" class="button">Annuler</a></td></tr>
        </div>
            ';
}

    if($action == "liste"){
        print load_fiche_titre($langs->trans("Liste des types de cotisations"), '', '');
        print '<hr>';      
        print_barre_liste("", $page, $_SERVER["PHP_SELF"], "", "", "", "", "", "", 'bill', 0, dolGetButtonTitle("Créer une nouvelle Prestation", '', 'fa fa-plus-circle', './liste_prestation.php?mainmenu=paiementsalaire&leftmenu=prestation&action=create' , '', 1), '', 0, 0, 0, 1);
        print '<table class="tagtable liste">';
        print '<tr class="liste_titre"><td align=""><label>Code</label></td>';
        print '<td align=""><label>Désignation</label></td>';
        print '<td align=""><label>Nature</label></td>';
        print '<td align=""><label>Organisme</label></td>';
        print '<td align="center"><label>Opérations</label></td></tr>';

        $type_prest = "SELECT * FROM ".MAIN_DB_PREFIX."type_prestation";
        $result_type_prest = $db->query($type_prest);
        if($result_type_prest){
            $i = 0;
            $num = $db->num_rows($result_type_prest);
                while ($i < $num){
                    $obj_prest_type = $db->fetch_object($result_type_prest);
                    print '<tr class="impair">';
                    print affiche_long_texte(img_picto("", "statut7_blue", "class='paddingright pictofixedwidth'"), $obj_prest_type->code, 0, 'bareme_prestation.php?mainmenu=paiementsalaire&leftmenu=prestation&id_prestation='.$obj_prest_type->rowid.'&action=info', 'nom', '', '', '', '').'</a></td>';
                    print affiche_long_texte('', $obj_prest_type->commentaire, 1, '', '', '', '', '', '');
                    print ''.affiche_long_texte('', $obj_prest_type->nature, 0, '', 'nom_vide', '', '', '', '');

                    $sql = "SELECT * FROM ".MAIN_DB_PREFIX."organisme WHERE rowid=".$obj_prest_type->fk_organisme;
                    $result_org = $db->query($sql);
                    $obj_org = $db->fetch_object($result_org);

                    print affiche_long_texte('', $obj_org->nom_organisme, 0, '', 'nom_vide', '', '', '', '');

                    print '<td align="center">';
                    print '<a class="reposition editfielda" href="bareme_prestation.php?mainmenu=paiementsalaire&leftmenu=prestation&id_prestation='.$obj_prest_type->rowid.'&action=info">'.img_edit('Modifier', '').'</a>';
                    if($i>5)
                        print '&nbsp;&nbsp;&nbsp;<a class="reposition editfielda" id="delete'.$i.'" onclick="myFunction('.$i.')" href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=prestation&id_prestation='.$obj_prest_type->rowid.'&action=suppression">'.img_delete('Supprimer', '').'&nbsp;</a></td>';
                    else print '</td>';							
								
                    print "</tr>";								
                    $i ++;

                }
                $db->free($result_type_prest);

                if($num == 0)
                    print '<tr><td align="center" colspan="5">Auccun Salaire Catégorie disponible!</td></tr>';
        } else print '<tr><td align="center" colspan="5">Auccun Salaire Catégorie disponible!</td></tr>';
        
        print "<script>
        function myFunction(e){
           var b = 'delete'+e;
           var button_generer = document.getElementById(b);
           if(!confirm('Click sur OK pour confirmer cette suppression')){
               var lien = '".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenu=prestation&action=liste';
               button_generer.setAttribute('href', lien);
           
           }
          }
        
        </script>";

        print '</table>';
    }

    if(!empty($message))
         print "<script>
         $.jnotify('".$message."', {delay : 5000, fadeSpeed: 500});
         </script>";