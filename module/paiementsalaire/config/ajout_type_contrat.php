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

//les types de contrat
$sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."type_contrat";
$res = $db->query($sql);
if($res){
  $nb = $db->num_rows($res);
  if($nb == 0){
	$sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."type_contrat (libelle, commentaire) VALUES ('CDD','Contrat a Duré Déterminé.')";
	$result = $db->query($sql_insert);
  }
  if($nb < 2){
	$sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."type_contrat (libelle, commentaire) VALUES ('CDI','Contrat a Duré Indéterminé.')";
	$result = $db->query($sql_insert);
  }
  if($nb < 3){
	$sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."type_contrat (libelle, commentaire) VALUES ('Contrat de Prestation','Le contrat de prestation de services est un contrat commercial qui vise à formaliser les relations entre un prestataire de service (une entreprise) et son client.')";
	$result = $db->query($sql_insert);
  }
  if($nb < 4){
	$sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."type_contrat (libelle, commentaire) VALUES ('Contrat Saisonnier','Le travail saisonnier se caractérise par des missions amenées à se répéter chaque année à la même période.')";
	$result = $db->query($sql_insert);
  }
  if($nb < 5){
	$sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."type_contrat (libelle, commentaire) VALUES ('Contrat de travail temporaire','Contrat de travail temporaire.')";
	$result = $db->query($sql_insert);
  }
  if($nb < 6){
	$sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."type_contrat (libelle, commentaire) VALUES ('Contrat de travail intermittent','Contrat de travail intermittent.')";
	$result = $db->query($sql_insert);
  }
  if($nb < 7){
	$sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."type_contrat (libelle, commentaire) VALUES ('Contrat d'apprentissage','Contrat d'apprentissage.')";
	$result = $db->query($sql_insert);
  }
  if($nb < 8){
	$sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."type_contrat (libelle, commentaire) VALUES ('Contrat de professionnalisation','Contrat de professionnalisation.')";
	$result = $db->query($sql_insert);
  }
  if($nb < 9){
	$sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."type_contrat (libelle, commentaire) VALUES ('Contrat unique d'insertion (CUI)','Contrat unique d'insertion.')";
	$result = $db->query($sql_insert);
  }
}


if($action == "add_type_contrat"){
        $libelle = GETPOST('libelle','alpha');
        $desc = GETPOST('desc','alpha') ? GETPOST('desc', 'alpha'): "";
        if(empty($libelle)){
            $message = 'Le champ "Libelle" est obligatoire<br>';
        }
        

        $sql = 'INSERT INTO '.MAIN_DB_PREFIX.'type_contrat (libelle, commentaire) VALUES ("'.$libelle.'","'.$desc.'")';
		$result = $db->query($sql);

        if(empty($message) && $result){
            $message = "Un type de Contrat enregistré avec succès";
            $action = "liste";
        }else{
            $message = "Un problème est survenu";
            $action = "create";
        }

}

if($action == "supprimer"){
    $id_type_contrat = GETPOST("id_type_contrat", "int");
    $sql = "DELETE FROM ".MAIN_DB_PREFIX."type_contrat WHERE rowid=".$id_type_contrat;
	$result = $db->query($sql);
    if($result)
        $message = 'Type de contrat supprimé avec succès';
    else    $message = 'Un problème est survenu';
    $action = "liste";
    
}

if($action == "saveedit"){
    $id_type_contrat = GETPOST("id_type_contrat", "int");
    $libelle = GETPOST('libelle','alpha');
    $desc = GETPOST('desc','alpha') ? GETPOST('desc', 'alpha'): "";
    if(empty($libelle)){
        $message = 'Le champ "Libelle" est obligatoire<br>';
    }
    
    if(empty($message)){
        $sql = 'UPDATE '.MAIN_DB_PREFIX.'type_contrat SET libelle="'.$libelle.'", commentaire="'.$desc.'" WHERE rowid='.$id_type_contrat;
        $result = $db->query($sql);

        if($result){
            $action = 'liste';
            $message = 'Type de Contrat Modifié avec succès';
        }   else {
            $message = "Un problème est survenu";
            $action = "edit";
        }
    }

}

if($action == "create"){
    print load_fiche_titre($langs->trans("Ajouter un nouveau type de Contrat"), '', '');
    print '<span style="color: red">*</span> <i>Tous les champs sont obligatoires</i>';
    print '<hr><br>';
 print '<table><form action="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=autre" method="post">';
 print '<input type="hidden" name="token" value="'.newToken().'">';
print '<input type="hidden" name="action" value="add_type_contrat">';
 print '<tr>';
 print '<td class="" style="width: 200px; padding-right: 30px; padding-bottom: 30px"><label>Libellé</label></td>';
 print '<td style="width: 500px; padding-right: 30px; padding-bottom: 30px"><input style="width: 500px" type="text" value="'.GETPOST("libelle", "alpha").'" name="libelle"/></tr>';
 print '<tr>';
 print '<td class="" style="width: 200px; padding-right: 30px; padding-bottom: 30px"><label>Description</label></td>';
 print '<td style="width: 600px; padding-right: 30px; padding-bottom: 30px"><textarea style="width: 550px; height: 50px" type="text" name="desc">'.GETPOST("desc", "alpha").'</textarea></td>';
 print '</tr>';
 print '<tr>';
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
    print load_fiche_titre($langs->trans("Liste des types de Contrat"), '', '');
    print "<hr>";
    print_barre_liste("", $page, $_SERVER["PHP_SELF"], "", "", "", "", "", "", 'bill', 0, dolGetButtonTitle("Créer un nouveau type de Contrat", '', 'fa fa-plus-circle', './ajout_type_contrat.php?mainmenu=paiementsalaire&leftmenu=autre&action=create', '', 1), '', 0, 0, 0, 1);
     print '<table style="width: 100%" class="tagtable liste">';
 print '<tr class="liste_titre">';
 print '<td style="width: 20%; color: darkblue; padding:" align=""><label>Libellé</label></td>';
 print '<td style="width: 70%; color: darkblue; padding:" align=""><label>Description</label></td>';
 print '<td style="width: 10%; color: darkblue; padding:" align=""><label>Opération</label></tr>';

    $type_contrat = "SELECT * FROM ".MAIN_DB_PREFIX."type_contrat";
	$result_type_contrat = $db->query($type_contrat);//= $db->query($covSql);

	if($result_type_contrat){
		$i = 0;
		$num = $db->num_rows($result_type_contrat);
		while ($i < $num){
			$obj_type_contrat = $db->fetch_object($result_type_contrat);

            print '<tr class="impair">';
            print ''.affiche_long_texte(img_picto("", "statut7_blue", "class='paddingright pictofixedwidth'"), $obj_type_contrat->libelle, 0, '', 'nom', '', '', '', '').'</td>';
            print ''.affiche_long_texte('', $obj_type_contrat->commentaire, 1, '', '', '', '', '', '').'</td>';
            print '<td align=""><a class="reposition editfielda" href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=autre&id_type_contrat='.$obj_type_contrat->rowid.'&action=edit_form">'.img_edit('Modifier','').'</a>';
            print '&nbsp;&nbsp;<a class="reposition editfielda" class="reposition editfielda" id="delete'.$i.'" onclick="myFunction('.$i.')" href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=autre&id_type_contrat='.$obj_type_contrat->rowid.'&action=supprimer">'.img_delete('Supprimer','').'&nbsp;&nbsp;&nbsp;</a>';          
            print '</td>';
            print '</tr>';

            $i ++;
			}
            $db->free($result_type_contrat);
			
		}else print '<tr><td align="center" colspan="4">Aucun type de Contrat disponible"</td></tr>';
	
        print'</table>';
        print "<script>
        function myFunction(e){
           var b = 'delete'+e;
           var button_generer = document.getElementById(b);
           if(!confirm('Voulez-vous Vraiment effectué cette suppression')){
               var lien = '".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenu=autre&action=liste';
               button_generer.setAttribute('href', lien);
           
           }
          }
        
        </script>";

 print'</table>';

}

if($action == "edit_form"){
    $id_type_contrat = GETPOST("id_type_contrat", "int");
print load_fiche_titre($langs->trans("Ajouter un nouveau type de Contrat"), '', '');
print '<span style="color: red">*</span> <i>Tous les champs sont obligatoires</i>';
print '<hr><br>';
 print '<table ><form action="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=autre&id_type_contrat='.$id_type_contrat.'" method="post">';
 print '<input type="hidden" name="token" value="'.newToken().'">';
print '<input type="hidden" name="action" value="saveedit">';
$sql = "SELECT * FROM ".MAIN_DB_PREFIX."type_contrat WHERE rowid=".$id_type_contrat;
$result = $db->query($sql);
$obj = $db->fetch_object($result);
 print '<tr>';
 print '<td style="width: 200px; padding-right: 30px"><label>Libellé</label></td>';
 print '<td style="width: 500px; padding-right: 30px"><input style="width: 500px" type="text" value="'.$obj->libelle.'" name="libelle"/></td></tr>';
 print '<tr><td style=" padding-right: 30px"><label>Description</label></td>';
 print '<td style="width: 600px; padding-right: 30px"><textarea style="width: 550px; height: 50px; margin-top: 10px" type="text" name="desc">'.$obj->commentaire.'</textarea></td>';
 print '</tr>'; 
 print '<tr>';
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



if(!empty($message))
	print "<script>
	$.jnotify('".$message."', {delay : 5000, fadeSpeed: 500});
	</script>";