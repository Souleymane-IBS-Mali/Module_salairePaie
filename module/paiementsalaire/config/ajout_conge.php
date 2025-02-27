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
	$action = 'create';


if($action == "add_type_conge"){
        $libelle = GETPOST('libelle', 'alpha');
        $desc = GETPOST('desc', 'alpha');
        if(empty($libelle)){
            $message = 'Le champ "Libelle" est obligatoire<br>';
        }
        

        if(empty($message)){
            $sql = "INSERT INTO ".MAIN_DB_PREFIX."conge (libelle, commentaire) VALUES ('".$libelle."','".$desc."')";
            $result = $db->query($sql);

            if($result){
                $message = "Un type de congé enregistrer avec succès";
                $action = "liste";
            }else{
                $message = "Un problème est survenu";
                $action = "create";
            }
            
        }else $action = "create";

}

if($action == "supprimer"){
    $id_conge = GETPOST("id_conge", "int");
    $sql = "DELETE FROM ".MAIN_DB_PREFIX."conge WHERE rowid=".$id_conge;
	$result = $db->query($sql);
    if($result)
        $message = 'Type congé supprimé avec succès';
    else    $message = 'Un problème est survenu';
    $action = "liste";
    
}

if($action == "saveedit"){
    $id_conge = GETPOST("id_conge", "int");
    $libelle = GETPOST('libelle','alpha');
    $desc = GETPOST('desc','alpha') ? GETPOST('desc', 'alpha'): "";
    if(empty($libelle)){
        $message = 'Le champ "Libelle" est obligatoire<br>';
    }
    
    $sql = "UPDATE ".MAIN_DB_PREFIX."conge SET libelle='".$libelle."', commentaire='".$desc."' WHERE rowid=".$id_conge;
    $result = $db->query($sql);

    if(empty($message) && $result)
        $message = 'Type congé Modifié avec succès';
    $action = 'liste';

}

if($action == "create"){
    print load_fiche_titre($langs->trans("Ajouter un nouveau type de congé"), '', '');
    print '<span style="color: red">*</span> <i>Tous les champs sont obligatoires</i>';
    print "<hr><br>";
 print '<table><form action="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=conge" method="post">';
 print '<input type="hidden" name="token" value="'.newToken().'">';
print '<input type="hidden" name="action" value="add_type_conge">';
 print '<tr>';
 print '<td style="width: 200px; padding-right: 30px" class=""><label>Libellé</label></td>';
 print '<td style="width: 500px; padding-right: 30px"><input style="width: 550px" type="text" value="'.GETPOST("libelle", "alpha").'" name="libelle"/></td>';
 print "</tr>";
 print "<tr>";
 print '<td style="width: 200px; padding-right: 30px; margin-top: 50px" class=""><label>Description</label></td>';
 print '<td style="width: 600px; padding-right: 30px; margin-top: 50px"><textarea style="width: 550px; height: 50px; margin-top: 10px;" type="text" name="desc">'.GETPOST("desc", "alpha").'</textarea></td>';
 print '</tr>';
 print '<tr>';
 print '</table>';
 print '<hr>';
 
 print '
    <div style="text-align: center"; align-items: center; justify-content: center">
        <td style=" padding-right: 30px; padding-bottom: 30px"></td><td style=" padding-bottom: 30px"><input class="button" type="submit" value="Ajouter" name=""/>
        </form>
        <a href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=conge&action=liste" class="button">Annuler</a>
    </div>
    ';
}
if($action == "liste"){
    print load_fiche_titre($langs->trans("Liste des types congés"), '', '');
    print "<hr>";
    print_barre_liste("", $page, $_SERVER["PHP_SELF"], "", "", "", "", "", "", 'bill', 0, dolGetButtonTitle("Créer un nouveau type de congé", '', 'fa fa-plus-circle', './ajout_conge.php?mainmenu=paiementsalaire&leftmenu=conge&action=create', '', 1), '', 0, 0, 0, 1);
     print '<table style="width: 100%" class="tagtable liste">';
 print '<tr class="liste_titre"></td>';
 print '<td style="width: 20%; padding : ; color: "><label>Libellé</label></td>';
 print '<td style="width: 70%; padding : ; color: "><label>Description</label></td>';
 print '<td style="width: 10%; padding : ; color: "><label>Opération</label></tr>';

 $type_salarie = "SELECT * FROM ".MAIN_DB_PREFIX."conge";
	$result_type_salarie = $db->query($type_salarie);//= $db->query($covSql);

	if($result_type_salarie){
		$i = 0;
		$num = $db->num_rows($result_type_salarie);
		while ($i < $num){
			$obj_conge = $db->fetch_object($result_type_salarie);

            print '<tr class="impair">';
            print ''.affiche_long_texte(img_picto("", "statut7_blue", "class='paddingright pictofixedwidth'"),$obj_conge->libelle, 0, '', 'nom', '', '', '', '').''.'</td>';
            print ''.affiche_long_texte('', $obj_conge->commentaire, 1, '', '', '', '', '', '').''.'</td>';
            print '<td>';
            print '<a class="reposition editfielda" href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=conge&id_conge='.$obj_conge->rowid.'&action=edit_conge">'.img_edit('Modifier','').'</a>';
            print '&nbsp;&nbsp;&nbsp;<a class="reposition editfielda" id="delete'.$i.'" onclick="myFunction('.$i.')" href="'.$_SERVER["PHP_SELF"].'?id_conge='.$obj_conge->rowid.'&action=supprimer">'.img_delete('Supprimer','').'&nbsp;&nbsp;&nbsp;</a>';
            print '</td>';
            print '</tr>';

            $i ++;
			}
            if($num == 0)
             print '<tr><td align="center" colspan="3">Aucune information disponible!</td></tr>';
			
             print "<script>
            function myFunction(e){
                var b = 'delete'+e;
                var button_generer = document.getElementById(b);
                if(!confirm('Click sur OK pour confirmer cette suppression')){
                    var lien = '".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenu=conge&action=liste';
                    button_generer.setAttribute('href', lien);
                
                }
            }
        
        </script>";
        
		}else print '<tr><td align="center" colspan="4">Aucune information disponible!</td></tr>';
	

 print'</table>';

}

if($action == "edit_conge"){
    $id_conge = GETPOST("id_conge", "int");
print load_fiche_titre($langs->trans("Ajouter un nouveau Type congé"), '', '');
print '<span style="color: red">*</span> <i>Tous les champs sont obligatoires</i>';
print'<hr><br>';
 print '<table><form action="'.$_SERVER["PHP_SELF"].'?id_conge='.$id_conge.'" method="post">';
 print '<input type="hidden" name="token" value="'.newToken().'">';
print '<input type="hidden" name="action" value="saveedit">';
$sql = "SELECT * FROM ".MAIN_DB_PREFIX."conge WHERE rowid=".$id_conge;
$result = $db->query($sql);
$obj = $db->fetch_object($result);
 print '<tr>';
 print '<td style="width: 200px; padding-right: 30px"><label>Libellé</label></td>';
 print '<td style="width: 500px; padding-right: 30px"><input style="width: 500px" type="text" value="'.$obj->libelle.'" name="libelle"/></td>';
 print '</tr>';
 print '<tr>';
 print '<td style="width: 200px; padding-right: 30px"><label>Description</label></td>';
 print '<td style="width: 600px; padding-right: 30px"><textarea style="width: 550px; height: 50px; margin-top: 10px" type="text" name="desc">'.$obj->commentaire.'</textarea></td>';
 print '</tr>';
 print '<tr>';
 print '</table>';
 print '<hr>';
 print '
    <div style="text-align: center"; align-items: center; justify-content: center">
        <td style=" padding-right: 30px; padding-bottom: 30px"></td><td style=" padding-bottom: 30px"><input class="button" type="submit" value="Ajouter" name=""/>
        </form>
        <a href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=conge&action=liste" class="button">Annuler</a></td></tr>
    </div>    
        ';

}

$db->free();


if(!empty($message))
	print "<script>
	$.jnotify('".$message."', {delay : 5000, fadeSpeed: 500});
	</script>";