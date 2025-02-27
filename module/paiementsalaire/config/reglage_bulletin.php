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

//Règles par défault par société
$sql = "SELECT sc.rowid as r1, sc.nom, sce.rowid as r2, sce.fk_object, sce.conv FROM ".MAIN_DB_PREFIX."societe as sc";
$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."societe_extrafields as sce ON sc.rowid=sce.fk_object WHERE sc.rowid NOT IN (SELECT fk_societe FROM ".MAIN_DB_PREFIX."reglage_bulletin) AND sce.grp=1";
$result = $db->query($sql);
if($result){
  $i = 0;
  $num = $db->num_rows($result);
  if($num == 0)
    while ($i < $num){
      $societe = $db->fetch_object($result);
        $sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'reglage_bulletin (separateur, fk_societe) VALUES (".","'.$societe->r1.'")';
        $db->query($sql_insert);
        $i ++;
    }
}


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

if($action == 'save_reglage'){
  if(empty(GETPOST("fk_societe", "int")))
    $message = "Veuillez selectionner une société<br>";

    if(empty(GETPOST("separateur", "alpha")) || GETPOST("separateur", "alpha") == "#")
      $message .= "Veuillez selectionner un séparateur";

    if(empty(GETPOST("decalage", "int")) && GETPOST("decalage", "int") != 0)
      $message .= "Veuillez choisir un décalage<br>";

  if(empty($message)){
    $sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."reglage_bulletin (separateur, fk_societe, decalage)";
    $sql_insert .= " VALUES('".GETPOST("separateur", "int")."',".GETPOST("fk_societe", "int").",".GETPOST('decalage', 'int').")";

   if($db->query($sql_insert))
      $message = "Reglage enregistré avec succès";

  }
}elseif($action == 'save_edit_reglage'){
  $sql_update = "UPDATE ".MAIN_DB_PREFIX."reglage_bulletin SET separateur='".GETPOST('separateur', 'int')."', fk_societe=".GETPOST('fk_societe', 'int').", decalage=".GETPOST('decalage', 'int')." WHERE rowid=".GETPOST('id','int');
  if($db->query($sql_update))
      $message = "Reglage modifié avec succès";
}


if($action == 'supprimer' && !empty(GETPOST('id', 'int'))){
  $sql_delete = "DELETE FROM ".MAIN_DB_PREFIX."reglage_bulletin WHERE rowid=".GETPOST('id', 'int');
  $result = $db->query($sql_delete);
  if($result)
      $message = "Reglace supprimé avec succès";
  else $message = "Un problème est survenu";
}
    print load_fiche_titre($langs->trans("Ajout de séparateur des chiffres"), '', '');
    print '<span style="float:right;"><a title="Reglages" href="./reglages.php?mainmenu=paiementsalaire&leftmenu=reglage">Retour</a></span><br>';
    print "<hr>";
    //print_barre_liste("", $page, $_SERVER["PHP_SELF"], "", "", "", "", "", "", 'bill', 0, dolGetButtonTitle('Ajouter un reglage', '', 'fa fa-plus-circle', $_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=bulletin&action=ajout_reglage', '', 1), '', 0, 0, 0, 1);
    
//---------------------------------------------------------------------------
      //print load_fiche_titre($langs->trans("Ajout d'un réglage pour une société"), '', '');
      //print '<span style="color: red">*</span> <i>Tous les champs sont obligatoires</i>';
    if($action == 'edit' && !empty(GETPOST("id", "int"))){
      $reglage_bulletin = "SELECT * FROM ".MAIN_DB_PREFIX."reglage_bulletin WHERE rowid=".GETPOST("id", "int");
      $result_reglage_bulletin = $db->query($reglage_bulletin);//= $db->query($covSql);
      $obj_reglage_bulletin = $db->fetch_object($result_reglage_bulletin);
    
      print '<table><form action="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=reglage&id='.$obj_reglage_bulletin->rowid.'" method="post">';
      print '<input type="hidden" name="token" value="'.newToken().'">';
      print '<input type="hidden" name="action" value="save_edit_reglage">';
      print '<tr>';
      print '<td style="width: 250px; padding-right: 30px;"><label>Caractère à utiliser</label></td>';
      print '<td style="width: 250px; padding-right: 30px;"><label>Decalage</label></td>';
      print '<td style="width: 200px; padding-right: 10px;"><label>Societe</label></td>';
      print '<td style="width: 100px; "></td>';

      print '</tr>';

      $virgule = "";
      $point = "";
      print '<tr>';
      if($obj_reglage_bulletin->separateur == ',')
        $virgule = "selected";
      elseif($obj_reglage_bulletin->separateur == '.')
        $point = "selected";
      print '<td><select name="separateur" style="width: 200px; padding-right: 20px;">
            <option value="." '.$point.'>point (.)</option>
            <option value="," '.$virgule.'>virgule (,)</option>
    
          </select></td>';

          print '<td><input type="number" name="decalage" max=3 min=0 value="'.$obj_reglage_bulletin->decalage.'"></td>';
      print '<td><select name="fk_societe" style="width: 200px; padding-right: 20px;">';
    
      $sql = "SELECT sc.rowid as r1, sc.nom, sce.rowid as r2, sce.fk_object, sce.conv FROM ".MAIN_DB_PREFIX."societe as sc";
      $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."societe_extrafields as sce ON sc.rowid=sce.fk_object WHERE sc.rowid NOT IN (SELECT fk_societe FROM ".MAIN_DB_PREFIX."reglage_bulletin WHERE rowid <> ".GETPOST('id', '09').") AND sce.grp=1";
      if($user->id != 1)
        $sql .= " AND sc.rowid IN ".$array_id_soc;
     /*sc.rowid NOT IN (SELECT fk_societe FROM ".MAIN_DB_PREFIX."reglage_bulletin) AND*/
      $result = $db->query($sql);
    
      if($result){
        $i = 0;
        $num = $db->num_rows($result);
        while ($i < $num){
          $societe = $db->fetch_object($result);
            if($societe->r1 == $obj_reglage_bulletin->rowid)
              print '<option value="'.$societe->r1.'" selected>'.$societe->nom.'</option>';
            else print '<option value="'.$societe->r1.'">'.$societe->nom.'</option>';
            $i ++;
        }
      }
      print '</select></td>';

      print '<td style=" padding-right: 20px; "><td ><input class="button" type="submit" value="Enregistrer" name=""/>';
      print '</tr>';
    
      print '</table>';
    }else{
      print '<table><form action="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=reglage" method="post">';
      print '<input type="hidden" name="token" value="'.newToken().'">';
      print '<input type="hidden" name="action" value="save_reglage">';
      print '<tr>';
      print '<td style="width: 250px; padding-right: 30px;"><label>Séparateur décimal</label></td>';
      print '<td style="width: 250px; padding-right: 30px;"><label>Nombre de chiffre après le séparateur</label></td>';
      print '<td style="width: 200px; padding-right: 10px;"><label>Societe</label></td>';
      print '<td style="width: 100px; "></td>';

      print '</tr>';

      print '<tr>';
      
      print '<td><select name="separateur" style="width: 200px; padding-right: 20px;">
            <option value=".">point (.)</option>
            <option value=",">virgule (,)</option>
    
            </select></td>';

            print '<td><input type="number" name="decalage" max=3 min=0  value=2></td>';
      print '<td><select name="fk_societe" style="width: 200px; padding-right: 20px;">';
    
      $sql = "SELECT sc.rowid as r1, sc.nom, sce.rowid as r2, sce.fk_object, sce.conv FROM ".MAIN_DB_PREFIX."societe as sc";
      $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."societe_extrafields as sce ON sc.rowid=sce.fk_object WHERE sc.rowid NOT IN (SELECT fk_societe FROM ".MAIN_DB_PREFIX."reglage_bulletin) AND sce.grp=1";
      if($user->id != 1)
        $sql .= " AND sc.rowid IN ".$array_id_soc;
     /*sc.rowid NOT IN (SELECT fk_societe FROM ".MAIN_DB_PREFIX."reglage_bulletin) AND*/
      $result = $db->query($sql);
    
      if($result){
        $i = 0;
        $num = $db->num_rows($result);
        while ($i < $num){
          $societe = $db->fetch_object($result);
            print '<option value="'.$societe->r1.'">'.$societe->nom.'</option>';
            $i ++;
        }
      }
      print '</select></td>';

      print '<td style=" padding-right: 20px; "><td ><input class="button" type="submit" value="Ajouter" name=""/>';
      print '</tr>';
    
      print '</table>';
    }
      print '<br><hr>';
print "<h4>Toutes Soicétés sans séparateur précisé Heriteront du séparateur point '.'</h4>";
  //---------------------------------------------------------------
  
  print '<table style="width: 100%" class="tagtable liste">';
 print '<tr class="liste_titre">';
 print '<td style="width: 70%; color: darkblue; padding:" align=""><label>Caractère utilisé</label></td>';
 print '<td style="width: 70%; color: darkblue; padding:" align=""><label>Nombre de chiffre après le séparateur décimal</label></td>';
 print '<td style="width: 10%; color: darkblue; padding:" align=""><label>Société</label>';
 print '<td style="width: 10%; color: darkblue; padding:" align=""><label>Opération</label></tr>';

  $reglage_bulletin = "SELECT * FROM ".MAIN_DB_PREFIX."reglage_bulletin";
	$result_reglage_bulletin = $db->query($reglage_bulletin);//= $db->query($covSql);

	if($result_reglage_bulletin){
		$i = 0;
		$num = $db->num_rows($result_reglage_bulletin);
		while ($i < $num){
			$obj_reglage_bulletin = $db->fetch_object($result_reglage_bulletin);
      
      if($obj_reglage_bulletin->separateur == ',')
        $separateur = 'virgule';
      elseif($obj_reglage_bulletin->separateur == '.')
        $separateur = 'point';
      else $separateur = 'espace';
            print '<tr class="impair">';
            print '<td>'.$separateur.'</td>';
            print '<td>'.$obj_reglage_bulletin->decalage.' chiffre(s) après le séparateur '.$separateur.'</td>';
            $sql = "SELECT nom FROM ".MAIN_DB_PREFIX."societe WHERE rowid=".$obj_reglage_bulletin->fk_societe;
            $obj_soc = $db->fetch_object($db->query($sql));
            print '<td>'.$obj_soc->nom.'</td>';
            print '<td>';
              print '<a class="reposition editfielda" href="'.$_SERVER["PHP_SELF"].'?id='.$obj_reglage_bulletin->rowid.'&action=edit">'.img_edit('Modifier', '').'</a>';			
              print '&nbsp;&nbsp;&nbsp;<a class="reposition editfielda" id="delete'.$i.'" onclick="myFunction('.$obj_reglage_bulletin->rowid.')" href="#">'.img_delete('Supprimer', '').'&nbsp;</a>';
            print '</td>';					
            print '</tr>';

            $i ++;
			}
            $db->free($result_reglage_bulletin);
			if($num == 0)
        print '<tr><td align="center" colspan="4">Aucun reglage disponible</td></tr>';

		}else print '<tr><td align="center" colspan="4">Aucun reglage disponible</td></tr>';
	
        print'</table>';
        print "<script>
        function myFunction(e){
           if(confirm('Voulez-vous Vraiment supprimer ce reglage')){
               window.location.href = '".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenu=reglage&action=supprimer&id='+e;
           
           }
          }
        
        </script>";

 print'</table>';


if(!empty($message))
	print "<script>
	$.jnotify('".$message."', {delay : 5000, fadeSpeed: 500});
	</script>";