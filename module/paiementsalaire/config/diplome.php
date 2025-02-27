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


if($action == "add_type_diplome"){
        $nom = GETPOST('nom', 'alpha');
        $desc = GETPOST('desc', 'alpha') ? GETPOST('desc', 'alpha'): "";
        if(empty($nom)){
            $message = 'Le champ "NOM" est obligatoire<br>';
        }
        
        if(empty($message)){
            $sql = "INSERT INTO ".MAIN_DB_PREFIX."diplome (nom, commentaire) VALUES ('".$nom."','".$desc."')";
		    $result = $db->query($sql);
    
            if($result){
                $message = "Diplôme Ajouté avec succès";
                $action = "liste";
            }else{
                $message = "Un problème est survenu";
                $action = "create";
            }
        }

}

if($action == "supprimer"){
    $id_diplome = GETPOST("id_diplome", "int");
    $sql = "DELETE FROM ".MAIN_DB_PREFIX."diplome WHERE rowid=".$id_diplome;
	$result = $db->query($sql);
    if($result)
        $message = 'Diplôme supprimé avec succès';
    else    $message = 'Un problème est survenu';
    $action = "liste";
}

if($action == "saveedit"){
    $id_diplome = GETPOST("id_diplome", "int");
    $nom = GETPOST('nom', 'alpha');
        $desc = GETPOST('desc', 'alpha') ? GETPOST('desc', 'alpha'): "";
        if(empty($nom)){
            $message = 'Le champ "NOM" est obligatoire<br>';
        }
    if(empty($message)){
        $sql = "UPDATE ".MAIN_DB_PREFIX."diplome SET nom='".$nom."', commentaire='".$desc."' WHERE rowid=".$id_diplome;
        $result = $db->query($sql);

        if($result){
            $message = "Diplôme modifié avec succès";
            $action = "liste";
        }else{
            $message = "Un problème est survenu";
            $action = "saveedit";
        }
    }
}

if($action == "create"){
    print load_fiche_titre($langs->trans("Ajouter et Liste des Diplômes"), '', '');
    print '<span style="color: red">*</span> <i>Tous les champs sont obligatoires</i>';
    print "<hr>";
 print '<table><form action="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=diplome" method="post">';
 print '<input type="hidden" name="token" value="'.newToken().'">';
 print '<input type="hidden" name="action" value="add_type_diplome">';
 print '<tr>';
 print '<td style="width: 200px; padding-right: 30px; padding-bottom:30px" class=""><label>Nom</label></td>';
 print '<td style="width: 500px; padding-right: 30px; padding-bottom:30px"><input style="width: 500px" type="text" value="'.GETPOST("nom_diplome").'" name="nom"/></td></tr>';
 print '</tr>';
 print '<td style="width: 200px; padding-right: 30px; padding-bottom:30px" class=""><label>Description</label></td>';
 print '<td style="width: 600px; padding-right: 30px; padding-bottom:30px"><textarea style="width: 550px; height: 50px; margin-top:" type="text" name="desc">'.GETPOST("desc").'</textarea></td></tr>';
 print '<tr>';
 print '</table>';
 print '<hr>';

 print '
    <div style="text-align: center"; align-items: center; justify-content: center">
        <td style=" padding-right: 30px; padding-bottom: 30px"></td><td style=" padding-bottom: 30px"><input class="button" type="submit" value="Ajouter" name=""/>
        </form>
        <a href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=diplome&action=liste" class="button">Annuler</a></td></tr>
    </div>
    ';
}

if($action == "liste"){
    print load_fiche_titre($langs->trans("Liste des Diplômes"), '', '');
print "<hr>";
print_barre_liste("", $page, $_SERVER["PHP_SELF"], "", "", "", "", "", "", 'bill', 0, dolGetButtonTitle("Créer un nouveau nom d'Diplome", '', 'fa fa-plus-circle', './diplome.php?mainmenu=paiementsalaire&leftmenu=diplome&action=create', '', 1), '', 0, 0, 0, 1);
 print '<table style="width : 100%" class="tagtable liste">';
 print '<tr class="liste_titre">';
 print '<td style="width: 33%; color: darkblue; padding:" align=""><label>Nom</label></td>';
 print '<td style="width: 34%; color: darkblue; padding:" align=""><label>Description</label></td>';
 print '<td style="width: 33%; color: darkblue; padding:" align="center"><label>Opération</label></td>';
 print '</tr>';

 $diplome = "SELECT * FROM ".MAIN_DB_PREFIX."diplome";
	$result_diplome = $db->query($diplome);//= $db->query($covSql);

	if($result_diplome){
		$i = 0;
		$num = $db->num_rows($result_diplome);
		while ($i < $num){
			$obj_diplome = $db->fetch_object($result_diplome);

            print '<tr class="impair">';
            print ''.affiche_long_texte(img_picto("", "statut7_blue", "class='paddingright pictofixedwidth'"), $obj_diplome->nom, 0, '', 'nom', '', '', '', '').'</td>';
            print ''.affiche_long_texte('', $obj_diplome->commentaire, 1, '', '', '', '', '', '').'</td><td align="center">';
            print '<a class="reposition editfielda" href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=diplome&id_diplome='.$obj_diplome->rowid.'&action=edit_form">'.img_edit('Modifier','').'</a>';
            print '&nbsp;&nbsp;&nbsp;&nbsp; <a class="reposition editfielda" id="delete'.$i.'" onclick="myFunction('.$i.')" href="'.$_SERVER["PHP_SELF"].'?id_diplome='.$obj_diplome->rowid.'&action=supprimer">'.img_delete('Supprimer','').'</a>';
            print '</td>';
            print '</tr>';

            $i ++;
			}
			if($num == 0)
                print '<tr><td align="center" colspan="4">Auccun Salaire Catégorie disponible!</td></tr>';
            print "<script>
            function myFunction(e){
                var b = 'delete'+e;
                var button_generer = document.getElementById(b);
                if(!confirm('Click sur OK pour confirmer cette suppression')){
                    var lien = '".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenu=diplome&action=liste';
                    button_generer.setAttribute('href', lien);
                
                }
            }
            
            </script>";
		}else print '<tr><td align="center" colspan="4">Aucune Diplome disponible"</td></tr>';
	

    print'</table>';

}

    if($action == "edit_form"){
        $id_diplome = GETPOST("id_diplome", "int");
        print load_fiche_titre($langs->trans("Modification d'un diplôme"), '', '');
        print '<span style="color: red">*</span> <i>Tous les champs sont obligatoires</i>';
        print "<hr><br>";
        print '<table><form action="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=diplome&id_diplome='.$id_diplome.'" method="post">';
        print '<input type="hidden" name="token" value="'.newToken().'">';
        print '<input type="hidden" name="action" value="saveedit">';
        $sql = "SELECT * FROM ".MAIN_DB_PREFIX."diplome WHERE rowid=".$id_diplome;
        $result = $db->query($sql);
        $obj = $db->fetch_object($result);
        print '<tr>';
        print '<td style="width: 200px; padding-right: 30px" class=""><label>Nom</label></td>';
        print '<td style="width: 500px; padding-right: 30px"><input style="width: 500px" type="text" value="'.$obj->nom.'" name="nom"/></td></tr>';
        print '<tr><td style="width: 200px; padding-right: 30px" class=""><label>Description</label></td>';
        print '<td style="width: 600px; padding-right: 30px"><textarea style="width: 550px; height: 50px; margin-top: 10px" type="text" name="desc">'.$obj->commentaire.'</textarea></td></tr>';
        print '</table>';
        print '<hr>';
        print '
            <div style="text-align: center"; align-items: center; justify-content: center">
                <tr><td style=" padding-right: 30px; padding-bottom: 30px"><td style=" padding-bottom: 30px"><input class="button" type="submit" value="Ajouter" name=""/>
                </form>
                <a href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=diplome&action=liste" class="button">Annuler</a></td></tr>
            </div>
                ';
}

    //Les type de diplôimes
$sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."diplome";
$res = $db->query($sql);
if($res){
  $nb = $db->num_rows($res);
  if($nb == 0){
	$sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."diplome (nom, commentaire) VALUES ('Doctorat','Tout iplôme de doctorat')";
	$result = $db->query($sql_insert);
  }
  if($nb < 2){
	$sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."diplome (nom, commentaire) VALUES ('Master','Tout diplome de BAC + 5 ou Correspondant')";
	$result = $db->query($sql_insert);
  }
  if($nb < 3){
	$sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."diplome (nom, commentaire) VALUES ('Licence','Tout diplome de BAC + 3 ou Correspondant')";
	$result = $db->query($sql_insert);
  }
  if($nb < 4){
	$sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."diplome (nom, commentaire) VALUES ('BAC','Tout diplome de BAC ou Correspondant')";
	$result = $db->query($sql_insert);

  }
}
$db->free();

if(!empty($message))
	print "<script>
	$.jnotify('".$message."', {delay : 5000, fadeSpeed: 500});
	</script>";