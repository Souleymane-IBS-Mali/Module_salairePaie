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




llxHeader("", "Paiement | Salaire");
//Titre
print load_fiche_titre($langs->trans("Jours Travaillés en ".date('Y')), '', '');
$id_societe = GETPOST("id_societe","int");
$fk_user = GETPOST("id","int");
$fk_salarie = GETPOST("fk_salarie", "int");
$id_convention = GETPOST("id_convention","int");
$action = GETPOST("action", "alpha");
$message = "";
$head = salaire_Head($fk_salarie, $fk_user, $id_societe, $id_convention);
    print dol_get_fiche_head($head, 'anciennete_nb_jours', "", -1, '');
if($user->id !=1 && $user->id != $fk_user && !$user->rights->paiementsalaire->salarie->read){
	print "<h2> Vous n\'avez pas ce droit </h2>";
}else{

    $mois_tab = array(" Janvier "," Février "," Mars "," Avril "," Mai "," Juin "," Juillet "," Août "," Septembre "," Octobre "," Novembre "," Décembre ");
    salarie_nb_jour($db, $id_societe);

    //Si le salarié est venu trouvé que le mois est déjà généré mais pas cloturé mais qu'on est dans un autre mois
    $bulletin_sql = "SELECT mois, annee FROM ".MAIN_DB_PREFIX."bulletin where cloture='non' AND fk_salarie=".$fk_salarie." ORDER BY date_creation DESC";
	$res_bulletin = $db->query($bulletin_sql);
    if($res_bulletin){
        $obj_bulletin = $db->fetch_object($res_bulletin);
        if($obj_bulletin){
            $salSql = "SELECT * FROM ".MAIN_DB_PREFIX."salarie_nombre_jour_travaille where fk_salarie=".$fk_salarie." AND annee=".$obj_bulletin->annee." AND mois=".$obj_bulletin->mois;
            $result = $db->query($salSql);
            $num = $db->num_rows($result);
            if($num <= 0){
                $jour = cal_days_in_month(CAL_GREGORIAN, $obj_bulletin->mois, $obj_bulletin->annee);
                $sql = 'INSERT INTO '.MAIN_DB_PREFIX.'salarie_nombre_jour_travaille (fk_societe, fk_salarie, annee, mois, jour)';
                $sql .= ' VALUES('.$id_societe.','.$fk_salarie.','.$obj_bulletin->annee.','.$obj_bulletin->mois.','.$jour.')';
                $db->query($sql);
            }
        }
    }
/*
$sql = 'INSERT INTO '.MAIN_DB_PREFIX.'salarie_nombre_jour_travaille (fk_societe, fk_salarie, annee, mois, jour)';
$sql .= ' VALUES('.$id_societe.','.$fk_salarie.','.$annee.','.$id_mois.','.$jour.')';
$db->query($sql);*/

    if(empty($fk_salarie)){
        print "<mark><strong>Il n'a pas encore de Matricule</strong></mark><br>";
        print "Page non Disponible";
    }else{
        $obj_soc = prepare_objet_entete($fk_salarie, $fk_user, $db, $id_societe, $id_convention);
        entete_societe($obj_soc, 'societe');
        print '<hr>';
        /*
        $sql_update = 'UPDATE '.MAIN_DB_PREFIX.'bulletin SET cloture="non" WHERE mois=2 AND annee=2025 and fk_societe=1';
        $db->query($sql_update);*/

        if($action == "save_nombre_jours"){
            $nb_jours = GETPOST("nb_jours", "int")?:0;
			$id_rowid = GETPOST("id_rowid", "int");
            $mois = date('m');
            if(empty($nb_jours) && $nb_jours != 0){
                $message = "Veuillez saisir 'LE NOMBRE DE JOUR TRAVAILLE'";
            }
            if(empty($message)){
                $sql_update = 'UPDATE '.MAIN_DB_PREFIX.'salarie_nombre_jour_travaille SET jour='.$nb_jours.' WHERE rowid='.$id_rowid.' AND fk_salarie='.$fk_salarie;
                if($db->query($sql_update)){
                    $message = "Nombre de jour travaillé modifié avec succès";
                    $action = "";

                    $annee = date('Y');
                    $salSql = "SELECT * FROM ".MAIN_DB_PREFIX."salarie_nombre_jour_travaille where fk_salarie=".$salarie->rowid." AND annee=".$annee." AND mois=".$id_mois;
                    $result = $db->query($salSql);
                    $num = $db->num_rows($result);
                    if($num == 0){
                        $jour = cal_days_in_month(CAL_GREGORIAN, $mois, $annee);
                        
                        $sql = 'INSERT INTO '.MAIN_DB_PREFIX.'salarie_nombre_jour_travaille (fk_societe, fk_salarie, annee, mois, jour)';
                        $sql .= ' VALUES('.$id_societe.','.$fk_salarie.','.$annee.','.$mois.','.$jour.')';
                        $db->query($sql);
                    }

                    //La trace
                    $sql_select = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$user->id;
					$obj = $db->fetch_object($db->query($sql_select));

					$sql_select = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$fk_user;
					$obj_user = $db->fetch_object($db->query($sql_select));

					$soc_sql = "SELECT nom FROM ".MAIN_DB_PREFIX."societe WHERE rowid=".$id_societe;
					$soc_res = $db->query($soc_sql);//= $db->query($covSql);
					$obj_soc = $db->fetch_object($soc_res);

					$action_effectue = "Modification du nombre de jour travaillé par ".$obj_user->firstname." ".$obj_user->lastname." de la société ".$obj_soc->nom;
					$sql_log = 'INSERT INTO '.MAIN_DB_PREFIX.'log (fk_user, nom, prenom, quand, action_effectue, object_concerne)';
					$sql_log .= ' VALUES('.$user->id.', "'.$obj->lastname.'","'.$obj->firstname.'",now(),"'.$action_effectue.'","Jours travaillés")';
					$db->query($sql_log);
                }else{
                    
                    $message = "Un problème est survenu";
                    $action = "edit_nombre_jours";
                }
            }else $action = "edit_nombre_jours";
        }

        $salSql = "SELECT date_anciennete, sursalaire FROM ".MAIN_DB_PREFIX."salarie where rowid=".$fk_salarie;
        $result = $db->query($salSql);
        $salarie = $db->fetch_object($result);
        print "<br><h3>Nombre de jour travaillé en ".date('Y')." par mois</h3>";

        //class="tagtable liste"
        print '<table class="tagtable liste">';

        $annee = date('Y');
        //$mois = date('m');
		$trouve = 0;
		for ($i=1; $i <= 12; $i++) {
			$salSql = "SELECT rowid, jour FROM ".MAIN_DB_PREFIX."salarie_nombre_jour_travaille where annee=".$annee." AND mois=".$i." AND fk_salarie=".$fk_salarie;
            $result = $db->query($salSql);
            if($result)
            $salarie = $db->fetch_object($result);
			$jour = cal_days_in_month(CAL_GREGORIAN, $i, $annee);

			if($action == ("edit_nombre_jours_".$i)){
				print '<div><form name="add" method="POST" action="'.$_SERVER['PHP_SELF'].'?mainmenu=paiementsalaire&leftmenu=salarie&id_societe='.$id_societe.'&fk_salarie='.$fk_salarie.'&id='.$fk_user.'&id_convention='.$id_convention.'&id_rowid='.$salarie->rowid.'">';
				print '<input type="hidden" name="token" value="'.newToken().'">';
				print '<input type="hidden" name="action" value="save_nombre_jours">';
				print '<tr>';
				print '</td><td style="padding: 10px; width: 200px;"><b>'.$mois_tab[$i-1].'</b></td>';
				print '<td style="padding: 10px; width: 200px;"><input type="number" name="nb_jours" min="0" max="'.cal_days_in_month(CAL_GREGORIAN, $i, date("Y")).'" value="'.($salarie->jour?:0).'">
				<input class="button" type="submit" value="Valider" >';

				print '</form>';
				print '<a class="reposition editfielda button" href="'.$_SERVER['PHP_SELF'].'?mainmenu=paiementsalaire&leftmenu=salarie&id_societe='.$id_societe.'&fk_salarie='.$fk_salarie.'&id='.$fk_user.'&id_convention='.$id_convention.'">Annuler</a>';
				print '</td></tr>';
			}else{
				print '<tr class="impair">';
				print '</td><td style="padding: 10px; width: 200px;"><b>'.$mois_tab[$i-1].'</b></td>';
				print '<td style="padding: 10px; width: 200px;"><input type="text" value="'.$salarie->jour.'" disabled>';

				$act = "edit_nombre_jours_".$i;
				if($user->rights->paiementsalaire->salarie->write){
					$bulletin_sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."bulletin where cloture='oui' AND fk_salarie=".$fk_salarie." AND annee=".$annee." AND mois=".$i;
					$res_bulletin = $db->query($bulletin_sql);
					$obj_bulletin = $db->fetch_object($res_bulletin);
					if(empty($obj_bulletin->rowid)){
						$trouve ++;
						$info = '';
						if($trouve == 1){
							$bulletin_sql1 = "SELECT rowid FROM ".MAIN_DB_PREFIX."bulletin where fk_salarie=".$fk_salarie." AND annee=".$annee." AND mois=".$i;
							$res_bulletin1 = $db->query($bulletin_sql1);
							$obj_bulletin1 = $db->fetch_object($res_bulletin1);
						if($obj_bulletin1->rowid)
							$info = img_picto("Mois en cours du bulletin", "tick");
						}
						if($i > date('m'))
							print $info = img_picto("Attention on est pas encore dans ce mois", "error");
						else{
							$bulletin_sql2 = "SELECT rowid FROM ".MAIN_DB_PREFIX."bulletin where fk_salarie=".$fk_salarie." AND annee=".$annee." AND mois=".$i;
							$res_bulletin2 = $db->query($bulletin_sql2);
							$obj_bulletin2 = $db->fetch_object($res_bulletin2);
							if($obj_bulletin2->rowid || (int) date('m') == $i)
								print $info.'<a class="reposition editfielda" href="'.$_SERVER['PHP_SELF'].'?mainmenu=paiementsalaire&leftmenu=salarie&id_societe='.$id_societe.'&fk_salarie='.$fk_salarie.'&id='.$fk_user.'&id_convention='.$id_convention.'&action='.$act.'&id_mois='.$i.'">'.img_edit('Modifier','').'</a>';
						}
					}
				}else print img_edit('Permission manquantes','');

				print '</td>';
				print '</tr>';
			}
		}
        print '</table>';


    }

    /*print '<table>';
    print '<tr id="cache1" ><td>1</td></tr>';
    print '<tr id="cache2" ><td>2</td></tr>';
    print '<tr id="cache3" ><td>3</td></tr>';
    print '</table>';
    print '<button id="bouton" onClick=cacher("cache1") >Cache1</button>';

    print '<script>
     var cache1 = document.getElementById("cache1");
     var cache2 = document.getElementById("cache2");
     var cache3 = document.getElementById("cache3");

     //var bouton = document.getElementById("bouton");

     function cacher(e) {
        var cache1 = document.getElementById(e);
        if(cache1.style.display=="none"){
            cache1.style.display="block"
        }else{
            cache1.style.display="none"
        }
    }

    </script>';*/
    $db->free();
    if(!empty($message)){
        $action = 'create';

            print "<script>
            $.jnotify('".$message."', {delay : 5000, fadeSpeed: 500});
            </script>";
    }
}
