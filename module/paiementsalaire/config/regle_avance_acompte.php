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
/*
  //Règles par défault par société
  $sql = "SELECT sc.rowid as r1, sc.nom, sce.rowid as r2, sce.fk_object, sce.conv FROM ".MAIN_DB_PREFIX."societe as sc";
      $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."societe_extrafields as sce ON sc.rowid=sce.fk_object WHERE sc.rowid NOT IN (SELECT fk_societe FROM ".MAIN_DB_PREFIX."regle_avance_acompte) AND sce.grp=1";
      $result = $db->query($sql);
      if($result){
        $i = 0;
        $num = $db->num_rows($result);
        if($num == 0)
          while ($i < $num){
            $societe = $db->fetch_object($result);
              $sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'regle_avance_acompte (taux, fk_societe) VALUES ("33","'.$societe->r1.'")';
              $db->query($sql_insert);
              $i ++;
          }
      }
*/

if($action == 'save_regle'){
  if(empty(GETPOST("fk_societe", "int")))
    $message = "Veuillez selectionner une société<br>";

    if(empty(GETPOST("taux", "alpha")) || GETPOST("taux", "alpha") == "#")
      $message .= "Veuillez remplir le champ 'TAUX'";

  if(empty($message)){
    $sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."regle_avance_acompte (taux, fk_societe)";
    $sql_insert .= " VALUES('".GETPOST("taux", "int")."',".GETPOST("fk_societe", "int").")";

   if($db->query($sql_insert))
      $message = "Règle enregistré avec succès";

  }
}elseif($action == 'save_edit_regle'){
  $sql_update = "UPDATE ".MAIN_DB_PREFIX."regle_avance_acompte SET taux='".GETPOST('taux', 'int')."', fk_societe=".GETPOST('fk_societe', 'int')." WHERE rowid=".GETPOST('id','int');
  if($db->query($sql_update))
      $message = "Règle modifié avec succès";
}


if($action == 'supprimer' && !empty(GETPOST('id', 'int'))){
  $sql_delete = "DELETE FROM ".MAIN_DB_PREFIX."regle_avance_acompte WHERE rowid=".GETPOST('id', 'int');
  $result = $db->query($sql_delete);
  if($result)
      $message = "Reglace supprimé avec succès";
  else $message = "Un problème est survenu";
}
    print load_fiche_titre($langs->trans("Règle à appliquer aux avances/acomptes"), '', '');
    print "<hr>";
    //print_barre_liste("", $page, $_SERVER["PHP_SELF"], "", "", "", "", "", "", 'bill', 0, dolGetButtonTitle('Ajouter un regle', '', 'fa fa-plus-circle', $_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=bulletin&action=ajout_regle', '', 1), '', 0, 0, 0, 1);
    
//---------------------------------------------------------------------------
      //print load_fiche_titre($langs->trans("Ajout d'un réglage pour une société"), '', '');
      //print '<span style="color: red">*</span> <i>Tous les champs sont obligatoires</i>';
    if($action == 'edit' && !empty(GETPOST("id", "int"))){
      $regle_avance_acompte = "SELECT * FROM ".MAIN_DB_PREFIX."regle_avance_acompte WHERE rowid=".GETPOST("id", "int");
      $result_regle_avance_acompte = $db->query($regle_avance_acompte);//= $db->query($covSql);
      $obj_regle_avance_acompte = $db->fetch_object($result_regle_avance_acompte);
    
      print '<table><form action="'.$_SERVER["PHP_SELF"].'?id='.$obj_regle_avance_acompte->rowid.'" method="post">';
      print '<input type="hidden" name="token" value="'.newToken().'">';
      print '<input type="hidden" name="action" value="save_edit_regle">';
      print '<tr>';
      print '<td style="width: 250px; padding-right: 30px;"><label>Taux maximum</label></td>';
      print '<td style="width: 200px; padding-right: 10px;"><label>Societe</label></td>';
      print '<td style="width: 100px; "></td>';

      print '</tr>';

      print '<td><input type="text" name="taux" placeholder="33" value="'.$obj_regle_avance_acompte->taux.'" style="width: 200px; padding-right: 20px;">%</td>';

      print '<td><select name="fk_societe" style="width: 200px; padding-right: 20px;">';
    
      $sql = "SELECT sc.rowid as r1, sc.nom, sce.rowid as r2, sce.fk_object, sce.conv FROM ".MAIN_DB_PREFIX."societe as sc";
      $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."societe_extrafields as sce ON sc.rowid=sce.fk_object WHERE sc.rowid NOT IN (SELECT fk_societe FROM ".MAIN_DB_PREFIX."regle_avance_acompte WHERE rowid <> ".GETPOST('id', 'int').") AND sce.grp=1";
      if($user->id != 1)
        $sql .= " AND sc.rowid IN ".$array_id_soc;
     /*sc.rowid NOT IN (SELECT fk_societe FROM ".MAIN_DB_PREFIX."regle_avance_acompte) AND*/
      $result = $db->query($sql);
    
      if($result){
        $i = 0;
        $num = $db->num_rows($result);
        while ($i < $num){
          $societe = $db->fetch_object($result);
            if($societe->r1 == $obj_regle_avance_acompte->rowid)
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
      print '<table><form action="'.$_SERVER["PHP_SELF"].'" method="post">';
      print '<input type="hidden" name="token" value="'.newToken().'">';
      print '<input type="hidden" name="action" value="save_regle">';
      print '<tr>';
      print '<td style="width: 250px; padding-right: 30px;"><label>Taux maximum</label></td>';
      print '<td style="width: 200px; padding-right: 10px;"><label>Societe</label></td>';
      print '<td style="width: 100px; "></td>';

      print '</tr>';

      print '<tr>';
      
      print '<td><input type="text" name="taux" placeholder="33" style="width: 200px; padding-right: 20px;">%</td>';

      print '<td><select name="fk_societe" style="width: 200px; padding-right: 20px;">';
    
      $sql = "SELECT sc.rowid as r1, sc.nom, sce.rowid as r2, sce.fk_object, sce.conv FROM ".MAIN_DB_PREFIX."societe as sc";
      $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."societe_extrafields as sce ON sc.rowid=sce.fk_object WHERE sc.rowid NOT IN (SELECT fk_societe FROM ".MAIN_DB_PREFIX."regle_avance_acompte) AND sce.grp=1";
      if($user->id != 1)
        $sql .= " AND sc.rowid IN ".$array_id_soc;
        
     /*sc.rowid NOT IN (SELECT fk_societe FROM ".MAIN_DB_PREFIX."regle_avance_acompte) AND*/
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
print "<h4>Toute Soicété sans règle sera soumise au taux de 33% du salaire net</h4>";
  //---------------------------------------------------------------
  $info_max = "Indiquez ici le taux maximal du salaire net à ne pas dépasser pour les avances et acomptes de l'employé; ex: un salarié qui touche 100000F net, ne doit pas depasser en remboursement sur 1 mois 33%  des 100000 F soit 33000 F chaque mois.";

  print '<table style="width: 100%" class="tagtable liste">';
 print '<tr class="liste_titre">';
 print '<td style="width: 70%; color: darkblue; padding:" align=""><label>Taux maximum'.info_admin($info_max, 1).'</label></td>';
 print '<td style="width: 10%; color: darkblue; padding:" align=""><label>Société</label>';
 print '<td style="width: 10%; color: darkblue; padding:" align=""><label>Opération</label></tr>';

  $regle_avance_acompte = "SELECT * FROM ".MAIN_DB_PREFIX."regle_avance_acompte";
	$result_regle_avance_acompte = $db->query($regle_avance_acompte);//= $db->query($covSql);

	if($result_regle_avance_acompte){
		$i = 0;
		$num = $db->num_rows($result_regle_avance_acompte);
		while ($i < $num){
			$obj_regle_avance_acompte = $db->fetch_object($result_regle_avance_acompte);
            print '<tr class="impair">';
            print '<td>'.$obj_regle_avance_acompte->taux.'%</td>';

            $sql = "SELECT nom FROM ".MAIN_DB_PREFIX."societe WHERE rowid=".$obj_regle_avance_acompte->fk_societe;
            $obj_soc = $db->fetch_object($db->query($sql));
            print '<td>'.$obj_soc->nom.'</td>';
            print '<td>';
              print '<a class="reposition editfielda" href="'.$_SERVER["PHP_SELF"].'?id='.$obj_regle_avance_acompte->rowid.'&action=edit">'.img_edit('Modifier', '').'</a>';			
              print '&nbsp;&nbsp;&nbsp;<a class="reposition editfielda" id="delete'.$i.'" onclick="myFunction('.$obj_regle_avance_acompte->rowid.')" href="#">'.img_delete('Supprimer', '').'&nbsp;</a>';
            print '</td>';					
            print '</tr>';

            $i ++;
			}
            $db->free($result_regle_avance_acompte);
			if($num == 0)
        print '<tr><td align="center" colspan="4">Aucun règle disponible</td></tr>';

		}else print '<tr><td align="center" colspan="4">Aucun règle disponible</td></tr>';
	
        print'</table>';
        print "<script>
        function myFunction(e){
           if(confirm('Voulez-vous Vraiment supprimer ce regle')){
               window.location.href = '".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenu=bulletin&action=supprimer&id='+e;
           
           }
          }
        
        </script>";

 print'</table>';


if(!empty($message))
	print "<script>
	$.jnotify('".$message."', {delay : 5000, fadeSpeed: 500});
	</script>";