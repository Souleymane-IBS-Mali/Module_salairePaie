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
$action = GETPOST("action", "alpha");
if(empty($action))	
	$action = 'create';

    //les organismes par defaut au Mali
$sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."organisme";
$res = $db->query($sql);
if($res){
  $nb = $db->num_rows($res);
  if($nb == 0){
	$sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."organisme (nom_organisme, commentaire) VALUES ('CANAM','Caisse Nationale Assurance Maladie.')";
	$db->query($sql_insert);
  }
  if($nb < 2){
	$sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."organisme (nom_organisme, commentaire) VALUES ('Impôts','Organisme des impôts')";
	$db->query($sql_insert);

  }

  if($nb < 2){
	$sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."organisme (nom_organisme, commentaire) VALUES ('I.N.P.S','Institut National de Prévoyance Sociale')";
	$db->query($sql_insert);

  }

}

    //Stockage de la création
if($action == "add_organisme"){
    $nom_organisme = GETPOST('nom_organisme', 'alpha');
    $desc = GETPOST('desc', 'alpha');
    if(empty($nom_organisme)){
        $message = 'Le champ "NOM ORGANISE" est obligatoire<br>';
    }
        
    $affiche_detail = GETPOST('affiche_detail', 'alpha');
    if(empty($affiche_detail))
        $affiche_detail = 'non';

    if(empty($message)){
        $sql = 'INSERT INTO '.MAIN_DB_PREFIX.'organisme (nom_organisme, commentaire, affiche_detail_bulletin) VALUES ("'.$nom_organisme.'","'.$desc.'","'.$affiche_detail.'")';
	    $result = $db->query($sql);
        if($result){
            $message = 'Organisme Créé avec succès';
            $action = "liste";
        }else{
        $message = 'Un problème est survenu';
        $action = "create";

        }
    }else $action = "create";

}

// Suppression
    if($action == "supprimer"){
        $id_organisme = GETPOST("id_organisme", "int");
        $sql_delete = "DELETE FROM ".MAIN_DB_PREFIX."organisme WHERE rowid=".$id_organisme;
        $result = $db->query($sql_delete);
        if($result)
            $message = "Organisme supprimé avec succès";
        else $message = "Un problème est survenu";
        $action = "liste";
    }

    // Modification
    if($action == "saveedit"){
        $id_organisme = GETPOST("id_organisme", 'int');

        $nom_organisme = GETPOST('nom_organisme', 'alpha');
        $desc = GETPOST('desc');
        $affiche_detail = GETPOST('affiche_detail', 'alpha');
        if(empty($nom_organisme)){
            $message = 'Le champ "NOM ORGANISME" est obligatoire<br>';
        }

        if(empty($message)){
            $sql = 'UPDATE '.MAIN_DB_PREFIX.'organisme SET nom_organisme="'.$nom_organisme.'", commentaire="'.$desc.'", affiche_detail_bulletin="'.$affiche_detail.'" WHERE rowid='.$id_organisme;
		    $result = $db->query($sql);
            if($result){
                $message = 'Organisme modifié avec succès';
                $action = "liste";
            }else{
                $message = 'Un problème est survenu';
                $action = "saveedit";
            }
        }
    }

    //formulaire de création
if($action == "create"){
        print load_fiche_titre($langs->trans("Ajout d'un nouvel organisme"), '', '');
        print '<span style="color: red">*</span> <i>Tous les champs sont obligatoires</i>';
        print '<hr><br>';
        print '<table><form action="'.$_SERVER["PHP_SELF"].'" method="post">';
        print '<input type="hidden" name="token" value="'.newToken().'">';
        print '<input type="hidden" name="action" value="add_organisme">';
        print '<tr>';
        print '<td style="width: 200px; padding-right: 30px; padding-bottom : 30px" class="" ><label>Nom organisme</label></td>';
        print '<td style="width: 500px; padding-right: 30px; padding-bottom : 30px"><input style="width: 500px;" type="text" name="nom_organisme" value="'.GETPOST("nom_organisme", "alpha").'" /></td>';
        print '</tr>';
        print '<tr><td style="width: 200px; padding-right: 30px; padding-bottom : 30px" class="" ><label>Description</label></td>';
        print '<td style="width: 600px; padding-right: 30px; padding-bottom : 30px"><textarea style="width: 550px; heigth: 50px" type="text" name="desc">'.GETPOST("desc", "alpha").'</textarea></td>';
        print '</tr>';
        print '<tr>';
        print '<td style="width: 200px; padding-right: 30px; padding-top: 20px"><label>Afficher détail sur bulletin</label></td>';
        print '<td style="width: 600px; padding-right: 30px; padding-top: 20px"><input type="radio" name="affiche_detail" id="affiche_detail_oui" value="oui"><label for="affiche_detail_oui">Oui<label>';
        print '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" name="affiche_detail" id="affiche_detail_non" value="non"><label for="affiche_detail_non">Non<label></td>';
        print '</tr>';
        print '</table>';
        print '<hr>';
        print '
            <div style="text-align: center; align-items: center; justify-content: center"
                <tr><td style=" padding-right: 30px; padding-bottom: 30px"><td style=" padding-bottom: 30px"><input class="button" type="submit" value="Ajouter" name=""/>
                </form>
                <a href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=organisme&action=liste" class="button">Annuler</a></td></tr>
            </div>';
}

//formulaire d'edition
if($action == "edit"){
    $id_organisme = GETPOST("id_organisme", "int");
    if($id_organisme){
        $organisme = "SELECT * FROM ".MAIN_DB_PREFIX."organisme WHERE rowid=".$id_organisme;
        $result_organisme = $db->query($organisme);//= $db->query($covSql);
        $obj_organisme = $db->fetch_object($result_organisme);

        print load_fiche_titre($langs->trans("Modification d'un Organisme"), '', '');
        print '<span style="color: red">*</span> <i>Tous les champs sont obligatoires</i>';
        print '<hr>';
        print '<br>';
        print '<table><form action="'.$_SERVER["PHP_SELF"].'?id_organisme='.$obj_organisme->rowid.'" method="post">';
        print '<input type="hidden" name="token" value="'.newToken().'">';
        print '<input type="hidden" name="action" value="saveedit">';
        print '<tr>';
        print '<td style="width: 200px; padding-right: 30px"><label>Nom Organisme</label></td>';
        print '<td style="width: 500px padding-right: 30px"><input style="width: 500px" type="text" name="nom_organisme" value="'.$obj_organisme->nom_organisme.'" /></td>';
        print '</tr>';
        print '<tr>';
        print '<td style="width: 200px; padding-right: 30px; padding-top: 20px"><label>Description</label></td>';
        print '<td style="width: 600px; padding-right: 30px; padding-top: 20px"><textarea style="width: 550px; height: 50px" type="text" name="desc">'.$obj_organisme->commentaire.'</textarea></td>';
        print '</tr>';

        print '<tr>';
        print '<td style="width: 200px; padding-right: 30px; padding-top: 20px"><label>Afficher détail sur bulletin</label></td>';
        print '<td style="width: 600px; padding-right: 30px; padding-top: 20px"><input type="radio" name="affiche_detail" id="affiche_detail_oui" '.($obj_organisme->affiche_detail_bulletin=="oui"?"checked":"" ).' value="oui"><label for="affiche_detail_oui">Oui<label>';
        print '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" name="affiche_detail" id="affiche_detail_non" '.($obj_organisme->affiche_detail_bulletin=="non"?"checked":"").' value="non"><label for="affiche_detail_non">Non<label></td>';

        print '</tr>';

        print '</table>';
        print '<hr>';       
        print '
            <div style="text-align: center; align-items: center; justify-content: center">
                <input class="button" type="submit" value="Ajouter" name="">
                </form>
                <a href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=organisme&action=liste" class="button">Annuler</a></td></tr>
            </div>
            ';
    }
}

//Liste
if($action == "liste"){
    print load_fiche_titre($langs->trans("Liste des Organismes"), '', '');
    print '<hr>';

    print_barre_liste("", $page, $_SERVER["PHP_SELF"], "", "", "", "", "", "", 'bill', 0, dolGetButtonTitle("Créer un nouveau type de salarié", '', 'fa fa-plus-circle', './organisme.php?mainmenu=paiementsalaire&leftmenu=organisme&action=create', '', 1), '', 0, 0, 0, 1);
    print '<table class="tagtable liste">';
        print '<tr class="liste_titre"><td style="width: 20%; align=""><label>Nom Organisme</label></td>';
        print '<td style="width: 20%;" align=""><label>Description</label></td>';
        print '<td style="width: 20%;" align="center"><label>Afficher détail sur bulletin</label></td>';
        print '<td style="width: 20%;" align="center"><label>Opérations</label></td></tr>';

        $organisme = "SELECT * FROM ".MAIN_DB_PREFIX."organisme";
        $result_organisme = $db->query($organisme);
        if($result_organisme){
            $i = 0;
            $num = $db->num_rows($result_organisme);
            if($num > 0)
                while ($i < $num){
                    $obj_organisme = $db->fetch_object($result_organisme);
                    print '<tr class="impair">'.affiche_long_texte(img_picto("", "statut7_blue", "class='paddingright pictofixedwidth'"), $obj_organisme->nom_organisme, 0, '', 'nom', '', '', '', '').'';
                    print ''.affiche_long_texte('', $obj_organisme->commentaire, 1, '', '', '', '', '', '').'';
                    print '<td align="center">'.$obj_organisme->affiche_detail_bulletin.'</td>';
                    print '<td align="center">';
                    print '<a class="reposition editfielda" href="'.$_SERVER["PHP_SELF"].'?id_organisme='.$obj_organisme->rowid.'&action=edit">'.img_edit('Modifier', '').'</a>';			
                    print '&nbsp;&nbsp;&nbsp;<a class="reposition editfielda" id="delete'.$i.'" onclick="myFunction('.$i.')" href="'.$_SERVER["PHP_SELF"].'?id_organisme='.$obj_organisme->rowid.'&action=supprimer">'.img_delete('Supprimer', '').'&nbsp;</a>';					
                    print "</td></tr>";
                    $i ++;

                }
            else print "<tr><td align='center' colspan='3' style='padding:20px;'>Aucun Organisme disponible</td></tr>";
            print "<script>
            function myFunction(e){
            var b = 'delete'+e;
            var button_generer = document.getElementById(b);
            if(!confirm('Click sur OK pour confirmer cette suppression')){
                var lien = '".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenu=organisme&action=liste';
                button_generer.setAttribute('href', lien);
            
            }
            }
        
        </script>";
        }
        print '</table>';

}

$db->free();

//affichage des messages(notification)
if(!empty($message))
print "<script>
$.jnotify('".$message."', {delay : 5000, fadeSpeed: 500});
</script>";
    