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
    
   /* $sql_update = 'UPDATE '.MAIN_DB_PREFIX.'salarie SET sursalaire="89701" WHERE rowid=47';
    if($db->query($sql_update))
    print "okkkk";*/

    //les taxes par defaut au Mali
    $sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."type_taxe";
    $res = $db->query($sql);
    if($res){
      $obj = $db->num_rows($res);
      if($obj == 0){
        $sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."type_taxe (fk_organisme, libelle, commentaire, nature, type_bareme, affiche_bulletin) VALUES (2,'I.T.S','Impôts sur le traitement de salaires','Obligatoire',1,'Oui')";
        $db->query($sql_insert);
        
    
        $sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'bareme_taxe (fk_taxe, libelle, actif) VALUES (1,"juillet 2015 barème", 1)';
        $db->query($sql_insert);

        $sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'bareme_taxe (fk_taxe, libelle, actif) VALUES (1,"Février 2024", 0)';
        $db->query($sql_insert);
    
        $bareme = array();
        $bareme[0] = array(00, "330000", "0", 00, 1);
        $bareme[1] = array(330001, "578400", "5", 12420, 1);
        $bareme[2] = array(578401, "1176400", "12", 84100, 1);
        $bareme[3] = array(1176401, "1789733", "18", 194580, 1);
        $bareme[4] = array(1789734, "2384195", "26", 349193, 1);
        $bareme[5] = array(2384196, "3494130", "31", 693219, 1);
        $bareme[6] = array(3494131, "+", "37", 00, 1);

        $bareme[7] = array(0, "330000", "0", 00, 2);
        $bareme[8] = array(330001, "1200000", "2", 17400, 2);
        $bareme[9] = array(1200001, "1800000", "10", 77400, 2);
        $bareme[10] = array(1800001, "2600000", "26", 285400, 2);
        $bareme[11] = array(2600001, "3500000", "33", 582400, 2);
        $bareme[12] = array(3500001, "4100000", "36", 798400, 2);
        $bareme[13] = array(4100001, "+", "40", 00, 2);
    
        for ($i=0; $i < count($bareme); $i++) { 
            $sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."taxe (montant_debut, montant_limit, taux, fk_type, valeur, fk_bareme) VALUES (".$bareme[$i][0].",'".$bareme[$i][1]."','".$bareme[$i][2]."',1,".$bareme[$i][3].",".$bareme[$i][4].")";
            $result = $db->query($sql_insert);
        }
    

        $sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."type_taxe (fk_organisme, libelle, commentaire, nature, type_bareme, affiche_bulletin) VALUES (2,'CFE','Contribution forfaitaire à la charge des employeurs','Obligatoire',2,'Oui')";
        $db->query($sql_insert);

        $sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."type_taxe (fk_organisme, libelle, commentaire, nature, type_bareme, affiche_bulletin) VALUES (2,'TL','Taxe de logement','Obligatoire',2,'Oui')";
        $db->query($sql_insert);

        $sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'bareme_taxe2 (fk_taxe, taux_salariale, taux_patronale, charge) VALUES (2,"0","3.5",2)';
        $db->query($sql_insert);

        $sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'bareme_taxe2 (fk_taxe, taux_salariale, taux_patronale, charge) VALUES (3,"0","1",2)';
        $db->query($sql_insert);

      }
    }
    
    if($action == "supprimer"){
        $id_taxe = GETPOST("id_taxe", "int");
        $sql_som_taxe = "SELECT libelle, commentaire FROM ".MAIN_DB_PREFIX."type_taxe WHERE rowid=".$id_taxe;
        $obj_taxe = $db->fetch_object($db->query($sql_som_taxe));

            $sql_delete = "DELETE FROM ".MAIN_DB_PREFIX."type_taxe WHERE rowid=".$id_taxe;
            $result = $db->query($sql_delete);
            $sql_delete = "DELETE FROM ".MAIN_DB_PREFIX."taxe WHERE fk_type=".$id_taxe;
            $result2 = $db->query($sql_delete);
            if($result){
                $message = "Taxe supprimer avec succès";

                $sql_select = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$user->id;
                $obj = $db->fetch_object($db->query($sql_select));

                //On garde la trace de l'action
                $action_effectue = "Suppression de la taxe ".$obj_taxe->libelle."(".$obj_taxe->commentaire.")";
                $sql_log = 'INSERT INTO '.MAIN_DB_PREFIX.'log (fk_user, nom, prenom, quand, action_effectue, object_concerne)';
                $sql_log .= ' VALUES('.$user->id.', "'.$obj->lastname.'","'.$obj->firstname.'",now(),"'.$action_effectue.'","Suppression Taxe")';
                $db->query($sql_log);
            }else{
                $message = "Un problème est survenu";
            }
        

        $action = "liste";
    }
    if(empty($action))	
	    $action = 'create';

    if($action == "add_type_taxe"){
        $libelle = GETPOST('libelle', 'alpha');
        $fk_organisme = GETPOST('organisme','int');
        $desc = GETPOST('desc', 'alpha');
        $affiche_bulletin = GETPOST('affiche_bulletin', 'alpha');
        $nature = GETPOST('nature', 'alpha');
        $type_bareme = GETPOST('type_bareme', 'int');


        if(empty($libelle)){
            $message = 'Le champ "Libelle" est obligatoire<br>';
        }
        
        if(empty($fk_organisme)){
            $message = 'Le champ "ORGANISME" est obligatoire<br>';
        }
        

        if(empty($message)){
            $sql = 'INSERT INTO '.MAIN_DB_PREFIX.'type_taxe (fk_organisme, libelle, commentaire, nature, type_bareme, affiche_bulletin) VALUES ("'.$fk_organisme.'","'.$libelle.'","'.$desc.'","'.$nature.'",'.$type_bareme.',"'.$affiche_bulletin.'")';
            $result = $db->query($sql);

            if( $result){
                $message = 'Taxe enregistrée avec succès';

                $result = $db->query("SELECT LAST_INSERT_ID() as rowid;");
                $obj = $db->fetch_object($result);
                $rowid_taxe =  $obj->rowid;
                
                $sql_select = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$user->id;
                $obj = $db->fetch_object($db->query($sql_select));

                //On garde la trace de l'action
                $action_effectue = "Ajout Taxe ".$libelle."(".$desc.")";
                $sql_log = 'INSERT INTO '.MAIN_DB_PREFIX.'log (fk_user, nom, prenom, quand, action_effectue, object_concerne)';
                $sql_log .= ' VALUES('.$user->id.', "'.$obj->lastname.'","'.$obj->firstname.'",now(),"'.$action_effectue.'","Ajout Taxe")';
                $db->query($sql_log);

                print "<script>
                    window.location.href = 'bareme_taxe.php?mainmenu=paiementsalaire&leftmenu=taxe&id_taxe=".$rowid_taxe."&action=info';
                </script>";

            }else{
                $message = 'Un problème est survenu';
                $action = "create";
            }
        }

    }

    

    
if($action == "create"){
        print load_fiche_titre($langs->trans("Ajouter une nouvelle type de Taxe (impôt)"), '', '');
        print '<span style="color: red">*</span> <i>Tous les champs sont obligatoires</i>';
        print "<hr>";
        print'<br>';
        print '<table><form action="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=taxe" method="post">';
        print '<input type="hidden" name="token" value="'.newToken().'">';
        print '<input type="hidden" name="action" value="add_type_taxe">';
        print '<tr>';
        print '<td style="width : 200px; padding-bottom : 20px"><label>Libellé</label></td>';
        print '<td style="width: 500px; padding-right: 30px; padding-bottom : 20px"><input style="width: 500px" type="text" value ="'.GETPOST("libelle", "alpha").'"name="libelle"/></td></tr>';

        print '<tr><td style=" width: 200px; padding-bottom : 20px"><label>Description</label></td>';
        print '<td style="width: 600px padding-right: 30px;  padding-bottom : 20px"><textarea style="width: 550px; height: 50px" type="text" name="desc">'.GETPOST("desc", "alpha").'</textarea></td></tr>';

        print '<tr><td style=" width : 200px; padding-bottom : 20px"><label>Nature</label></td>';
        print '<td style="width: 500px padding-right: 30px;  padding-bottom : 20px"> <input id="obli" type="radio" name="nature" value="obligatoire" checked><label for="obli">Obligatoire   </label>';
        print '<input type="radio" name="nature" id="fac" value="facultative"><label for="fac">Facultative</label></td></tr>';

        print '<tr><td style=" width : 200px; padding-bottom : 20px"><label>Type de barème</label></td>';
        print '<td><select name="type_bareme">
                <option value=1 selected>Liste</option>
                <option value=2>Pourcentage</option>';
                print '</td></tr>';
        print '<tr><td style=" width : 200px; padding-bottom : 20px"><label> Organisme</label></td>';
        print '<td style="width: 500px padding-right: 30px;  padding-bottom : 20px"><select style="width: 500px" name="organisme">';
        
        $sql = "SELECT * FROM ".MAIN_DB_PREFIX."organisme";
        $result_org = $db->query($sql);
        if($result_org){
            $i = 0;
            $num = $db->num_rows($result_org);
            while($i < $num){
                $obj_org = $db->fetch_object($result_org);
                print "<option value='".$obj_org->rowid."'>".$obj_org->nom_organisme."</option>";
                $i++;
            }
        }
        
       print '</select></td></tr>';

        print '<tr><td style=" width : 200px; padding-bottom : 20px"><label>Afficher sur bulletin</label></td>';
        print '<td style="width: 50ppx; padding-right: 30px;  padding-bottom : 20px"><select style="width: 500px" name="affiche_bulletin" id="affiche_bulletin" >;
			<option value="Oui" selected >Oui</option>
            <option value="Non">Non</option>';
	print '</td></tr>';
			
	    print '</td></tr>';

    print '<tr>';
    print '</table>';
    print '<hr>';

 print '
    <div style="text-align: center; align-items: center; justify-content: center">    
        <input class="button" type="submit" value="Enregistrer" name=""/>
        </form>
        <a href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=taxe&action=liste" class="button">Annuler</a>
    </div>';
 
        
 
}
    if($action == "liste"){
        print load_fiche_titre($langs->trans("Liste des types de taxe"), '', '');

        print '<hr>';     
        print_barre_liste("", $page, $_SERVER["PHP_SELF"], "", "", "", "", "", "", 'bill', 0, dolGetButtonTitle("Créer un nouveau barême", '', 'fa fa-plus-circle', './liste_taxe.php?mainmenu=paiementsalaire&leftmenu=taxe&action=create' , '', 1), '', 0, 0, 0, 1); 
        print '<table class="tagtable liste">';
        print '<tr class="liste_titre"><td style="width: 100px; align=""><label>Type Impots</label></td>';
        print '<td style="width: 250px; align=""><label>Description</label></td>';
        print '<td style="width: 100px; align=""><label>Nature</label></td>';
        print '<td style=" width : 200px; padding-bottom : 20px"><label>Type de barème</label></td>';
        print '<td style="width: 100px; align=""><label>Organisme</label></td>';
        print '<td style="width: 100px; align="center"><label>Opérations</label></td></tr>';
        
        $type_impot = "SELECT * FROM ".MAIN_DB_PREFIX."type_taxe";
        $result_type_impot = $db->query($type_impot);
        if($result_type_impot){
            $i = 0;
            $num = $db->num_rows($result_type_impot);
                while ($i < $num){
                    $obj_impot_type = $db->fetch_object($result_type_impot);
                    print '<tr class="impair">'.affiche_long_texte(img_picto("", "statut7_blue", "class='paddingright pictofixedwidth'"), $obj_impot_type->libelle, 0, 'bareme_taxe.php?id_taxe='.$obj_impot_type->rowid.'&action=info', 'nom', '', '', '', '').'';
                    print ''.affiche_long_texte('', $obj_impot_type->commentaire, 1, '', '', '', '', '', '').'';
                    print '<td align="">'.$obj_impot_type->nature.'</td>';

                    $type_bareme = "Liste";
                    if($obj_impot_type->type_bareme == 2)
                        $type_bareme = "Pourcentage";
                    print '<td>'.$type_bareme.'</td>';
                    $sql = "SELECT * FROM ".MAIN_DB_PREFIX."organisme WHERE rowid=".$obj_impot_type->fk_organisme;
                    $result_org = $db->query($sql);
                    $obj_org = $db->fetch_object($result_org);

                    print '<td align="">'.$obj_org->nom_organisme.'</td>';
                    print '<td align="">';
                    print '<a class="reposition editfielda" href="bareme_taxe.php?id_taxe='.$obj_impot_type->rowid.'&action=info">'.img_edit('Modifier', '').'</a>';	
                    if($i>2)
                        print '&nbsp;&nbsp;&nbsp;<a class="reposition editfielda" id="delete'.$i.'" onclick="myFunction('.$i.')" href="'.$_SERVER["PHP_SELF"].'?id_taxe='.$obj_impot_type->rowid.'&action=supprimer">'.img_delete('Supprimer', '').'&nbsp;</a></td>';
                    else print '</td>';							
                    print "</tr>";								
                    $i ++;

                }
                $db->free($result_type_impot);

                if($num == 0)
                    print '<tr><td align="center" colspan="5">Aucune taxe disponible!</td></tr>';
        }else print '<tr><td align="center" colspan="5">Aucune taxe disponible!</td></tr>';

        print "<script>
        function myFunction(e){
           var b = 'delete'+e;
           var button_generer = document.getElementById(b);
           if(!confirm('Click sur OK pour confirmer cette suppression')){
               var lien = '".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenu=taxe&action=liste';
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