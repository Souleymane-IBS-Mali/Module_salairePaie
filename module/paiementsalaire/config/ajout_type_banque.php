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

//les types de banque
$sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."type_banque";
$res = $db->query($sql);
if($res){
  $nb = $db->num_rows($res);
  if($nb == 0){
	$sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'type_banque (libelle, commentaire) VALUES ("BDM s.a","La Banque de Développement du Mali.")';
	$result = $db->query($sql_insert);
  }
  if($nb < 2){
	$sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'type_banque (libelle, commentaire) VALUES ("BNDA","Banque Nationale de Développement Agricole.")';
	$result = $db->query($sql_insert);
  }
  if($nb < 3){
	$sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'type_banque (libelle, commentaire) VALUES ("Orange Money","Moyen de transfert d\'argent du SONATEL.")';
	$result = $db->query($sql_insert);
  }
  if($nb < 4){
	$sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'type_banque (libelle, commentaire) VALUES ("MobiCash ou MoovMoney","Moyen de transfert d\'argent du SOTELMA.")';
	$result = $db->query($sql_insert);
  }
  if($nb < 5){
	$sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'type_banque (libelle, commentaire) VALUES ("Banque Atlantique","Banque Atlantique")';
	$result = $db->query($sql_insert);
  }
  if($nb < 6){
	$sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'type_banque (libelle, commentaire) VALUES ("B.O.A","Banque Ouest Africa")';
	$result = $db->query($sql_insert);
  }
  if($nb < 7){
	$sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'type_banque (libelle, commentaire) VALUES ("BIM","BIM")';
	$result = $db->query($sql_insert);
  }

  if($nb < 8){
	$sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'type_banque (libelle, commentaire) VALUES ("B.I.C.I.M","Bicim")';
	$result = $db->query($sql_insert);
  }
  if($nb < 9){
	$sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'type_banque (libelle, commentaire) VALUES ("BMS","Banque Malienne de Solidarité")';
	$result = $db->query($sql_insert);
  }
  if($nb < 10){
	$sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'type_banque (libelle, commentaire) VALUES ("ECOBANK","Ecobank")';
	$result = $db->query($sql_insert);
  }
  if($nb < 11){
	$sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'type_banque (libelle, commentaire) VALUES ("U.B.A","United Bank for Africa. La banque numérique.")';
	$result = $db->query($sql_insert);
  }
  if($nb < 12){
	$sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'type_banque (libelle, commentaire) VALUES ("ORABANK","ORABANK Mali")';
	$result = $db->query($sql_insert);
  }
  if($nb < 13){
	$sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'type_banque (libelle, commentaire) VALUES ("CORIS BANK","Coris Bank")';
	$result = $db->query($sql_insert);
  }
  if($nb < 14){
	$sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'type_banque (libelle, commentaire) VALUES ("B.S.I.C","Banque Sahélo Saharienne pour l\'Investissement et le Commerce")';
	$result = $db->query($sql_insert);
  }
  if($nb < 15){
	$sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'type_banque (libelle, commentaire) VALUES ("SAMA money","Sama money")';
	$result = $db->query($sql_insert);
  }
}


if($action == "type_banque"){
        $libelle = GETPOST('libelle','alpha');
        $desc = GETPOST('desc','alpha') ? GETPOST('desc', 'alpha'): "";
        if(empty($libelle)){
            $message = 'Le champ "Libelle" est obligatoire<br>';
        }
        

        $sql = 'INSERT INTO '.MAIN_DB_PREFIX.'type_banque (libelle, commentaire) VALUES ("'.$libelle.'","'.$desc.'")';
		$result = $db->query($sql);

        if(empty($message) && $result){
            $message = "Un type Banque enregistré avec succès";

            $sql_select = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$user->id;
			$obj = $db->fetch_object($db->query($sql_select));

			//On garde la trace de l'action
			$action_effectue = "Ajout d'un type de banque (".$libelle.")";
			$sql_log = 'INSERT INTO '.MAIN_DB_PREFIX.'log (fk_user, nom, prenom, quand, action_effectue, object_concerne)';
			$sql_log .= ' VALUES('.$user->id.', "'.$obj->lastname.'","'.$obj->firstname.'",now(),"'.$action_effectue.'","Ajout")';
			$db->query($sql_log);

            $action = "liste";
        }else{
            $message = "Un problème est survenu";
            $action = "create";
        }

}

if($action == "supprimer"){
    $id_type_banque = GETPOST("id_type_banque", "int");
    $sql = "DELETE FROM ".MAIN_DB_PREFIX."type_banque WHERE rowid=".$id_type_banque;
	$result = $db->query($sql);
    if($result){
        $message = 'Type Banque supprimé avec succès';
    }else    $message = 'Un problème est survenu';
    $action = "liste";
    
}

if($action == "saveedit"){
    $id_type_banque = GETPOST("id_type_banque", "int");
    $libelle = GETPOST('libelle','alpha');
    $desc = GETPOST('desc','alpha') ? GETPOST('desc', 'alpha'): "";
    if(empty($libelle)){
        $message = 'Le champ "Libelle" est obligatoire<br>';
    }
    
    if(empty($message)){
        $sql = 'UPDATE '.MAIN_DB_PREFIX.'type_banque SET libelle="'.$libelle.'", commentaire="'.$desc.'" WHERE rowid='.$id_type_banque;
        $result = $db->query($sql);

        if($result){
            $action = 'liste';
            $message = 'Type Banque Modifié avec succès';

            $sql_select = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$user->id;
			$obj = $db->fetch_object($db->query($sql_select));

			//On garde la trace de l'action
			$action_effectue = "Modification d'un tupe de banque (".$libelle.", ".$desc.")";
			$sql_log = 'INSERT INTO '.MAIN_DB_PREFIX.'log (fk_user, nom, prenom, quand, action_effectue, object_concerne)';
			$sql_log .= ' VALUES('.$user->id.', "'.$obj->lastname.'","'.$obj->firstname.'",now(),"'.$action_effectue.'","Modification")';
			$db->query($sql_log);

        }else {
            $message = "Un problème est survenu";
            $action = "edit";
        }
    }

}

if($action == "create"){
    print load_fiche_titre($langs->trans("Ajouter un nouveau type de Banque"), '', '');
    print '<span style="color: red">*</span> <i>Tous les champs sont obligatoires</i>';
    print '<hr><br>';
 print '<table><form action="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=autre" method="post">';
 print '<input type="hidden" name="token" value="'.newToken().'">';
print '<input type="hidden" name="action" value="type_banque">';
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
    print load_fiche_titre($langs->trans("Liste des types de Banques"), '', '');
    print "<hr>";
    print_barre_liste("", $page, $_SERVER["PHP_SELF"], "", "", "", "", "", "", 'bill', 0, dolGetButtonTitle("Créer un nouveau type de Banque", '', 'fa fa-plus-circle', './ajout_type_banque.php?mainmenu=paiementsalaire&leftmenu=autre&action=create', '', 1), '', 0, 0, 0, 1);
     print '<table style="width: 100%" class="tagtable liste">';
 print '<tr class="liste_titre">';
 print '<td style="width: 20%; color: darkblue; padding:" align=""><label>Libellé</label></td>';
 print '<td style="width: 70%; color: darkblue; padding:" align=""><label>Description</label></td>';
 print '<td style="width: 10%; color: darkblue; padding:" align=""><label>Opération</label></tr>';

    $type_banque = "SELECT * FROM ".MAIN_DB_PREFIX."type_banque";
	$result_type_banque = $db->query($type_banque);//= $db->query($covSql);

	if($result_type_banque){
		$i = 0;
		$num = $db->num_rows($result_type_banque);
		while ($i < $num){
			$obj_type_banque = $db->fetch_object($result_type_banque);

            print '<tr class="impair">';
            print ''.affiche_long_texte(img_picto("", "statut7_blue", "class='paddingright pictofixedwidth'"), $obj_type_banque->libelle, 0, '', 'nom', '', '', '', '').'</td>';
            print ''.affiche_long_texte('', $obj_type_banque->commentaire, 1, '', '', '', '', '', '').'</td>';
            print '<td align=""><a class="reposition editfielda" href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=autre&id_type_banque='.$obj_type_banque->rowid.'&action=edit_form">'.img_edit('Modifier','').'</a>';
            if($user->id == 1)
                print '&nbsp;&nbsp;<a class="reposition editfielda" class="reposition editfielda" id="delete'.$i.'" onclick="myFunction('.$i.')" href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=autre&id_type_banque='.$obj_type_banque->rowid.'&action=supprimer">'.img_delete('Supprimer','').'&nbsp;&nbsp;&nbsp;</a>';          
            print '</td>';
            print '</tr>';

            $i ++;
			}
            $db->free($result_type_banque);
			
		}else print '<tr><td align="center" colspan="4">Aucun type banque disponible"</td></tr>';
	
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
    $id_type_banque = GETPOST("id_type_banque", "int");
print load_fiche_titre($langs->trans("Ajouter un nouveau type de Banque"), '', '');
print '<span style="color: red">*</span> <i>Tous les champs sont obligatoires</i>';
print '<hr><br>';
 print '<table ><form action="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=autre&id_type_banque='.$id_type_banque.'" method="post">';
 print '<input type="hidden" name="token" value="'.newToken().'">';
print '<input type="hidden" name="action" value="saveedit">';
$sql = "SELECT * FROM ".MAIN_DB_PREFIX."type_banque WHERE rowid=".$id_type_banque;
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