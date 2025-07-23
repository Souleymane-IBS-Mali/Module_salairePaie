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

    $sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."type_salarie";
    $res = $db->query($sql);

    if($res){
      $nb = $db->num_rows($res);
      if($nb == 0){
        $sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."type_salarie (libelle, commentaire) VALUES ('Aucun','')";
        $result = $db->query($sql_insert);

      }
      if($nb < 2){
        $sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."type_salarie (libelle, commentaire) VALUES ('Cadres','Les salariés de types Cadres.')";
        $result = $db->query($sql_insert);

      }
      if($nb < 3){
        $sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."type_salarie (libelle, commentaire) VALUES ('Non cadres','Les types salariés non Cadres')";
        $result = $db->query($sql_insert);

      }
      if($nb < 4){
        $sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."type_salarie (libelle, commentaire) VALUES ('Maitrise','Les types salarié de type Maitrise')";
        $result = $db->query($sql_insert);

      }
    }


if($action == "add_type_salarie"){
        $libelle = GETPOST('libelle');
        $convention = GETPOST('convention', 'int');
        $desc = GETPOST('desc', 'alpha') ? GETPOST('desc', 'alpha'): "";
        if(empty($libelle)){
            $message = 'Le champ "Libelle" est obligatoire<br>';
        }
        if(empty($convention)){
            $message .= 'Le champ "CONVENTION" est obligatoire<br>';
        }
        

        if(empty($message)){
            $sql = 'INSERT INTO '.MAIN_DB_PREFIX.'type_salarie (libelle, commentaire, convention) VALUES ("'.$libelle.'","'.$desc.'",'.$convention.')';
		    $result = $db->query($sql);
            if($result)
                $message = "Un type Salarié enregistrer avec succès";
            else $message = "Un problème est survenu";
            $action = "liste";
        }else{
            $action = "create";
        }

}

if($action == "supprimer"){
    $id_type_sal = GETPOST("id_type_sal", "int");


			$sql_2 = "SELECT rowid FROM ".MAIN_DB_PREFIX."dcategories WHERE fk_type_salarie=".$id_type_sal;
			$res_2 = $db->query($sql_2);
			if($res_2){
				$num_2 = $db->num_rows($res_2);
				$j = 0;
				while ($j < $num_2) {
					$obj_2 = $db->fetch_object($res_2);
					$sqlDel = "DELETE FROM ".MAIN_DB_PREFIX."echelon WHERE fk_categorie=".$obj_2->rowid;
					$result = $db->query($sqlDel);
					$j ++;
				}
			}
			$sqlDel = "DELETE FROM ".MAIN_DB_PREFIX."dcategories WHERE fk_type_salarie=".$id_type_sal;;
			$result = $db->query($sqlDel);

            $sql = "DELETE FROM ".MAIN_DB_PREFIX."type_salarie WHERE rowid=".$id_type_sal;
            $result = $db->query($sql);
    if($result)
        $message = 'Type salarié supprimé avec succès';
    else    $message = 'Un problème est survenu';
    $action = "liste";
    
}

if($action == "saveedit"){
    $id_type_sal = GETPOST("id_type_sal", "09");
    $libelle = GETPOST('libelle','alpha');
    $desc = GETPOST('desc','alpha') ? GETPOST('desc', 'alpha'): "";
    $convention = GETPOST('convention','alpha');

    if(empty($libelle)){
        $message = 'Le champ "Libelle" est obligatoire<br>';
    }

    if(empty($convention)){
        $message .= 'Le champ "CONVENTION" est obligatoire<br>';
    }
    $sql = "UPDATE ".MAIN_DB_PREFIX."type_salarie SET libelle='".$libelle."', commentaire='".$desc."', convention=".$convention." WHERE rowid=".$id_type_sal;
    $result = $db->query($sql);

    if(empty($message) && $result)
        $message = 'Type de Salarié Modifié avec succès';
    $action = 'liste';

}

if($action == "create"){
    print load_fiche_titre($langs->trans("Ajouter un nouveau type de salarié"), '', '');
    print '<span style="color: red">*</span> <i>Tous les champs sont obligatoires</i>';
    print "<hr><br>";
 print '<table><form action="'.$_SERVER["PHP_SELF"].'" method="post">';
 print '<input type="hidden" name="token" value="'.newToken().'">';
print '<input type="hidden" name="action" value="add_type_salarie">';
 print '<tr>';
 print '<td style="width: 200px; padding-right: 30px; padding-bottom: 10px" class=""><label>Libelle</label></td>';
 print '<td style="width: 500px; padding-right: 30px; padding-bottom: 10px"><input style="width: 500px" type="text" name="libelle"/></td> </tr>';
 print '<tr><td style="width: 200px; padding-right: 30px; padding-bottom: 10px" class=""><label>Description</label></td>';
 print '<td style="width: 600px; padding-right: 30px; padding-bottom: 10px"><textarea style="width: 550px; height: 50px" type="text" name="desc"></textarea></td></tr>';

 print '<tr><td style="width: 200px; padding-right: 30px; padding-bottom: 10px" class=""><label>Convention</label></td>';

 print '<td style="width: 500px; padding-right: 30px; padding-bottom: 10px"><select style="width: 500px" name="convention">';
 $sql = "SELECT * FROM ".MAIN_DB_PREFIX."convention";
 $res = $db->query($sql);
 if($res){
    $num = $db->num_rows($res);
    $i = 0;
    while ($i < $num) {
        $obj_conv = $db->fetch_object($res);
         print "<option value='".$obj_conv->rowid."'>".$obj_conv->nom."</option>";

        $i++;
    }
 }
 print '</select></td>';
 print '</tr>';
 print '</table>';
 print '<hr>';
 print '
    <div style="text-align: center"; align-items: center; justify-content: center">
        <input class="button" type="submit" value="Ajouter" name=""/>
        </form>
        <a href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=autre&action=liste" class="button">Annuler</a></td></tr>
    </div>
    ';
}


if($action == "liste"){
    print load_fiche_titre($langs->trans("Liste des types salariés"), '', '');
    print "<hr>";
    print_barre_liste("", $page, $_SERVER["PHP_SELF"], "", "", "", "", "", "", 'bill', 0, dolGetButtonTitle("Créer un nouveau type de salarié", '', 'fa fa-plus-circle', './ajout_type_salarie.php?mainmenu=paiementsalaire&leftmenu=autre&action=create', '', 1), '', 0, 0, 0, 1);
     print '<table style="width : 100%" class="tagtable liste">';
 print '<tr class="liste_titre">';
 print '<td style="25%; color: darkblue; padding:" align=""><label>Libellé</label></td>';
 print '<td style="25%; color: darkblue; padding:" align=""><label>Description</label></td>';
 print '<td style="25%; color: darkblue; padding:" align=""><label>Convention</label></td>';
 print '<td style="25%; color: darkblue; padding:" align="center"><label>Opération</label></tr>';

 $type_salarie = "SELECT * FROM ".MAIN_DB_PREFIX."type_salarie";
	$result_type_salarie = $db->query($type_salarie);//= $db->query($covSql);

	if($result_type_salarie){
		$i = 0;
		$num = $db->num_rows($result_type_salarie);
		while ($i < $num){
			$obj_type_salarie = $db->fetch_object($result_type_salarie);
            if($obj_type_salarie->rowid != 1){
                print '<tr class="impair">';
                print ''.affiche_long_texte(img_picto("", "statut7_blue", "class='paddingright pictofixedwidth'"), $obj_type_salarie->libelle, 0, '', 'nom', '', '', '', '').'';
                print ''.affiche_long_texte('',  $obj_type_salarie->commentaire, 1, '', '', '', '', '', '');

                $sql = "SELECT * FROM ".MAIN_DB_PREFIX."convention WHERE rowid=".$obj_type_salarie->convention;
                $res = $db->query($sql);
                if($res){
                    $obj_conv = $db->fetch_object($res);
                    print affiche_long_texte('', $obj_conv->nom, 1, '', 'nom', '', '', '', '');

                }else print affiche_long_texte('', "Toutes les conventions", 1, '', 'Toutes les conventions', '', '', '', '');
                print '<td align="center"><a class="reposition editfielda" href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=autre&id_type_sal='.$obj_type_salarie->rowid.'&action=edit_form">'.img_edit('Modifier','').'</a>';
                print '&nbsp;&nbsp;<a class="reposition editfielda" id="delete'.$i.'" onclick="myFunction('.$i.')" href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=autre&id_type_sal='.$obj_type_salarie->rowid.'&action=supprimer">'.img_delete('Supprimer','').'&nbsp;&nbsp;&nbsp;</a>';
                print '</td>';
                print '</tr>';
            }

            $i ++;
			}
			
		}else print '<tr><td align="center" colspan="4">Aucun type de salarié disponible"</td></tr>';
	

 print'</table>';
 print "<script>
 function myFunction(e){
    var b = 'delete'+e;
    var button_generer = document.getElementById(b);
    if(!confirm('Cette suppression entraînera la suppression de :\\n toutes categories liées\\n Tous les echelons liés\\n Par conséquent les salaires de base liés')){
        var lien = '".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenu=autre&action=liste';
        button_generer.setAttribute('href', lien);
    
    }
   }
 
 </script>";
}

if($action == "edit_form"){
    $id_type_sal = GETPOST("id_type_sal", "int");
print load_fiche_titre($langs->trans("Ajouter un nouveau type de salarié"), '', '');
print '<span style="color: red">*</span> <i>Tous les champs sont obligatoires</i>';
print '<hr><br>';
 print '<table><form action="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=autre&id_type_sal='.$id_type_sal.'" method="post">';
 print '<input type="hidden" name="token" value="'.newToken().'">';
print '<input type="hidden" name="action" value="saveedit">';
$sql = "SELECT * FROM ".MAIN_DB_PREFIX."type_salarie WHERE rowid=".$id_type_sal;
$result = $db->query($sql);
$obj = $db->fetch_object($result);
 print '<tr>';
 print '<td style="width: 200px; padding-right: 30px"><label>Libellé</label></td>';
 print '<td style="width: 500px; padding-right: 30px"><input style="width: 500px" type="text" value="'.$obj->libelle.'" name="libelle"/></td></tr>';
 print '<tr><td style="width: 200px; padding-right: 30px"><label>Description</label></td>';
 print '<td style="width: 600px; padding-right: 30px"><textarea style="width: 550px; height: 50px" type="text" name="desc">'.$obj->commentaire.'</textarea></td></tr>';
 print '<tr><td style="width: 200px; padding-right: 30px"><label>Convention</label></td>';
 print '<td style="width: 500px; padding-right: 30px; padding-bottom: 10px"><select name="convention">';
 $sql = "SELECT * FROM ".MAIN_DB_PREFIX."convention";
 $res = $db->query($sql);
 if($res){
    $num = $db->num_rows($res);
    $i = 0;
    while ($i < $num) {
        $obj_conv = $db->fetch_object($res);
        if($obj_conv==$obj->convention)
            print "<option value='".$obj_conv->rowid."' select>".$obj_conv->nom."</option>";
        else print "<option value='".$obj_conv->rowid."'>".$obj_conv->nom."</option>";

        $i++;
    }
 }
 print '</select></td></tr>';
 print '</table>';
 print '<hr>';
 
 print '
    <div style="text-align: center"; align-items: center; justify-content: center">
        <tr><td style=" padding-right: 30px; padding-bottom: 30px"><td style=" padding-bottom: 30px"><input class="button" type="submit" value="Ajouter" name=""/>
        </form>
        <a href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=autre&action=liste" class="button">Annuler</a></td></tr>
    </div>
    ';
}

if(!empty($message))
	print "<script>
	$.jnotify('".$message."', {delay : 5000, fadeSpeed: 500});
	</script>";