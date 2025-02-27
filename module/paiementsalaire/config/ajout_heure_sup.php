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
 
 llxHeader("", "Paiement | Salaire");

 $message = "";

 $action = GETPOST('action', 'alpha');

if(empty($action))	
	$action = 'create';


if($action == "add_heure_sup"){
        $taux = GETPOST('taux', 'float');
        $desc = GETPOST('desc', 'alpha');
        print GETPOST('desc2', 'alpha');

        if(empty($taux)){
            $message = 'Le champ "TAUX" est obligatoire<br>';
        }

        if(empty($message)){
            $sql = 'INSERT INTO '.MAIN_DB_PREFIX.'heure_sup (taux, commentaire, fk_convention, fk_accord_etablissement, fk_societe) VALUES ("'.$taux.'","'.$desc.'",0,0,0)';
		    $result = $db->query($sql);
            if($result){
                $message = "Heure sup enregistrée avec succès";
                $action = "liste";
            }else{ 
                $message = "Un problème est survenu";
                $action = "create";
            }
        }else{
            $action = "create";
        }

}

if($action == "supprimer"){
    $id_heure_sup = GETPOST("id_heure_sup", "int");

			$sqlDel = "DELETE FROM ".MAIN_DB_PREFIX."salarie_heure_up WHERE fk_heure_sup=".$id_heure_sup;;
			$result = $db->query($sqlDel);

            $sql = "DELETE FROM ".MAIN_DB_PREFIX."heure_sup WHERE rowid=".$id_heure_sup;
            $result = $db->query($sql);
    if($result)
        $message = 'Type heure sup supprimé avec succès';
    else    $message = 'Un problème est survenu';
    $action = "liste";
    
}

if($action == "saveedit"){
    $id_heure_sup = GETPOST("id_heure_sup", "int");
    $taux = GETPOST('taux', 'float');
    $desc = GETPOST('desc', 'alpha');
    $id_convention = GETPOST('convention', 'int');

    if(empty($taux)){
        $message = 'Le champ "TAUX" est obligatoire<br>';
    }

    if(empty($message) && $result){
        $sql = 'UPDATE '.MAIN_DB_PREFIX.'heure_sup SET taux="'.$taux.'", commentaire="'.$desc.'" WHERE rowid='.$id_heure_sup;
        $result = $db->query($sql);
        if($result){
            $message = 'Heure sup modifiée avec succès';
            $action = 'liste';
        }else{
            $message = 'Un problème est survenu';
            $action = 'edit_form';
        }
    }
}

if($action == "create"){
	print '<form name="add_prime"  method="POST" action="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=heuresup">';

 print '<table>';
 print '<input type="hidden" name="token" value="'.newToken().'">';
 print '<input type="hidden" name="action" value="add_heure_sup">';
 print '<tr>';
 print '<td style="width: 200px; padding-right: 30px; padding-bottom: 30px" class=""><label>Taux</label></td>';
 print '<td style="width: 500px; padding-right: 30px; padding-bottom: 30px"><input style="width: 500px" type="text" name="taux" value="'.GETPOST("taux", "float").'"/></td> </tr>';
 print '<tr><td style="width: 200px; padding-right: 30px; padding-bottom: 30px" class=""><label>Description</label></td>';
 print '<td style="width: 600px; padding-right: 30px; padding-bottom: 30px"><textarea style="width: 550px; height: 50px" type="text" name="desc">'.GETPOST("desc", "alpha").'</textarea></td></tr>';

 
    print '<tr ><td style="width: 200px; padding-top: 10px; padding-right: 30px" class="fieldrequired"><label>Convention</label></td><td style="padding-top: 10px">
    <select style="width: 500px; margin-bottom: 10px" name="id_convention" disabled>';
     print '<option value="0" selected>Toutes les Convention</option>';
    
 print '</select></td>';
 print '</tr>';
 print '</table>';
 print '<hr>';
 
 print '
    <div style="text-align: center; align-items: center; justify-content: center">
        <input class="button" type="submit" value="Ajouter" >
        </form>
        <a href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=heuresup&action=liste" class="button">Annuler</a></td></tr>
    </div>  
        ';
}
if($action == "liste"){
    print load_fiche_titre($langs->trans("Liste des heures sup"), '', '');
    print "<hr>";
    print_barre_liste("", $page, $_SERVER["PHP_SELF"], "", "", "", "", "", "", 'bill', 0, dolGetButtonTitle("Créer un nouveau type d'heure sup", '', 'fa fa-plus-circle', './ajout_heure_sup.php?mainmenu=paiementsalaire&leftmenu=heuresup&action=create', '', 1), '', 0, 0, 0, 1);
     print '<table style="width: 100%" class="tagtable liste">';
 print '<tr class="liste_titre">';
 print '<td style="width: 20%; color: darkblue; padding-left: 80px" align=""><label>Taux</label></td>';
 print '<td style="width: 30%; color: darkblue; padding:" align=""><label>Description</label></td>';
 print '<td style="width: 20%; color: darkblue; padding:" align=""><label>Entité</label></td>';
 print '<td style="width: 10%; color: darkblue; padding:" align=""><label>Opération</label></tr>';


    $heure_sup = "SELECT * FROM ".MAIN_DB_PREFIX."heure_sup WHERE fk_convention=0 AND fk_accord_etablissement=0 AND fk_societe=0";
    $result_heure_sup = $db->query($heure_sup);//= $db->query($covSql);
	if($result_heure_sup){
		$i = 0;
		$num = $db->num_rows($result_heure_sup);
		while ($i < $num){
			$obj_heure_sup = $db->fetch_object($result_heure_sup);

            print '<tr class="impair">';
            print '<td align="" style="padding-left: 80px">'.$obj_heure_sup->taux.'%'.'</td>';
            print ''.affiche_long_texte('',  $obj_heure_sup->commentaire, 1, '', '', '', '', '', '').'';

            
            print '<td align="">';
            $conv = "";
            
            if($obj_heure_sup->id_accord_etablissement != 0){
                $sql_accord_etablissement = "SELECT nom FROM ".MAIN_DB_PREFIX."accord_etablissement WHERE rowid=".($obj_heure_sup->fk_accord_etablissement);
                $result_accord_etablissement = $db->query($sql_accord_etablissement);
                $obj_accord_etablissement = $db->fetch_object($result_accord_etablissement);
                $conv = $obj_accord_etablissement->nom;
            }elseif($obj_heure_sup->fk_societe !=0){
                $sql_societe = "SELECT nom FROM ".MAIN_DB_PREFIX."societe WHERE rowid=".($obj_heure_sup->fk_societe);
                $result_societe = $db->query($sql_societe);
                $obj_societe = $db->fetch_object($result_societe);
                $conv = $obj_societe->nom;
            }elseif($obj_heure_sup->fk_convention != 0){
                $sql_conv = "SELECT nom FROM ".MAIN_DB_PREFIX."convention WHERE rowid=".($obj_heure_sup->fk_convention);
                $result_conv = $db->query($sql_conv);
                $obj_conv = $db->fetch_object($result_conv);
                $conv = $obj_conv->nom;
            }else $conv = "Toutes les conventions";

            print $conv;

            print '</td>';
            print '<td align=""><a class="reposition editfielda" href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=heuresup&id_heure_sup='.$obj_heure_sup->rowid.'&action=edit_form">'.img_edit('Modifier','').'</a>';
            print '&nbsp;&nbsp;<a class="reposition editfielda" id="delete'.$i.'" onclick="myFunction('.$i.')" href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=heuresup&id_heure_sup='.$obj_heure_sup->rowid.'&action=supprimer">'.img_delete('Supprimer','').'&nbsp;&nbsp;&nbsp;</a>';
            print '</td>';
            print '</tr>';

            $i ++;
			}
            $db->free($result_heure_sup);

            if($num == 0)
                print '<tr><td align="center" colspan="4">Aucun type de salarié disponible"</td></tr>';
			
		}else print '<tr><td align="center" colspan="4">Aucun type de salarié disponible"</td></tr>';
	

 print'</table>';
 print "<script>
 function myFunction(e){
    var b = 'delete'+e;
    var button_generer = document.getElementById(b);
    if(!confirm('Cette suppression entraînera la suppression de :\\n toutes categories liées\\n Tous les echelons liés\\n Par conséquent les salaires de base liés')){
        var lien = '".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenu=heuresup&action=liste';
        button_generer.setAttribute('href', lien);
    
    }
   }
 
 </script>";
}

if($action == "edit_form"){
    $id_heure_sup = GETPOST("id_heure_sup", "int");
print load_fiche_titre($langs->trans("Modification d'une Heure Sup"), '', '');
print '<span style="color: red">*</span> <i>Tous les champs sont obligatoires</i>';
    print "<hr><br>";
 print '<table><form action="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=heuresup&id_heure_sup='.$id_heure_sup.'" method="post">';
 print '<input type="hidden" name="token" value="'.newToken().'">';
print '<input type="hidden" name="action" value="saveedit">';
$sql = "SELECT * FROM ".MAIN_DB_PREFIX."heure_sup WHERE rowid=".$id_heure_sup;
$result = $db->query($sql);
$obj = $db->fetch_object($result);
 print '<tr>';
 print '<td style="width: 200px; padding-right: 30px"><label>Taux</label></td>';
 print '<td style="width: 500px; padding-right: 30px"><input style="width: 500px;" type="text" value="'.$obj->taux.'" name="taux"/></td></tr>';
 print '<tr><td style="width: 200px; padding-right: 30px"><label>Description</label></td>';
 print '<td style="width: 600px; padding-right: 30px"><textarea style="width: 550px; height: 50px; margin-top: 10px" type="text" name="desc">'.$obj->commentaire.'</textarea></td></tr>';
 print '<td style=" padding-right: 30px">';
 $conv = "";
 if($obj_heure_sup->fk_accord_etablissement != 0){
    $sql_accord_etablissement = "SELECT nom FROM ".MAIN_DB_PREFIX."accord_etablissement WHERE rowid=".($obj_heure_sup->fk_accord_etablissement);
    $result_accord_etablissement = $db->query($sql_accord_etablissement);
    $obj_accord_etablissement = $db->fetch_object($result_accord_etablissement);
    $conv = $obj_accord_etablissement->nom;
}elseif($obj_heure_sup->fk_societe !=0){
    $sql_societe = "SELECT nom FROM ".MAIN_DB_PREFIX."societe WHERE rowid=".($obj_heure_sup->fk_societe);
    $result_societe = $db->query($sql_societe);
    $obj_societe = $db->fetch_object($result_societe);
    $conv = $obj_societe->nom;
}elseif($obj_heure_sup->fk_convention != 0){
    $sql_conv = "SELECT nom FROM ".MAIN_DB_PREFIX."convention WHERE rowid=".($obj_heure_sup->fk_convention);
    $result_conv = $db->query($sql_conv);
    $obj_conv = $db->fetch_object($result_conv);
    $conv = $obj_conv->nom;
}
print $conv;
 print '</select></td></tr>';
 print '</table>';
 print '<hr>';
 print '
    <div style="text-align: center; align-items: center; justify-content: center">
        <input class="button" type="submit" value="Ajouter" name=""/>
        </form>
        <a href="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=heuresup&action=liste" class="button">Annuler</a>
    </div>
        ';
 
}


if(!empty($message))
	print "<script>
	$.jnotify('".$message."', {delay : 5000, fadeSpeed: 500});
	</script>";