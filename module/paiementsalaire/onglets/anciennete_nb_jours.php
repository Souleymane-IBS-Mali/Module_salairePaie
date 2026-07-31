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
/*$bulletin_sql = "INSERT INTO ".MAIN_DB_PREFIX."bulletin (fk_salarie, cloture, mois, annee, fk_societe) VALUES(704, 'non', 5, 2026, 488)";
	if($db->query($bulletin_sql))
        print "OK";
    else print $db->error();*/
    
        $obj_soc = prepare_objet_entete($fk_salarie, $fk_user, $db, $id_societe, $id_convention);
		entete_societe($obj_soc, 'societe');
		print '<hr>';
    $mois_tab = array(" Janvier "," Février "," Mars "," Avril "," Mai "," Juin "," Juillet "," Août "," Septembre "," Octobre "," Novembre "," Décembre ");
    salarie_nb_jour($db, $id_societe);

    //Si le salarié est venu trouvé que le mois est déjà généré mais pas cloturé mais qu'on est dans un autre mois
    $bulletin_sql = "SELECT mois, annee FROM ".MAIN_DB_PREFIX."bulletin where cloture='non' AND fk_societe=".$id_societe." ORDER BY date_creation DESC";
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


        if($action == "save_nombre_jours"){
            $nb_jours = GETPOST("nb_jours", "int")?:0;
			$id_rowid = GETPOST("id_rowid", "int");
            $mois = GETPOST("mois", "int")?:date('m');
            
            if(empty($nb_jours) && $nb_jours != 0){
                $message = "Veuillez saisir 'LE NOMBRE DE JOUR TRAVAILLE'";
            }
            if(empty($message)){
                $ancien_val = 0;
                $salSql = "SELECT jour FROM ".MAIN_DB_PREFIX."salarie_nombre_jour_travaille where rowid=".$id_rowid;
                $result = $db->query($salSql);
                $num = $db->num_rows($result);
                
                if($num > 0)
                    $ancien_val = ($db->fetch_object($result))->jour;

                $sql_update = 'UPDATE '.MAIN_DB_PREFIX.'salarie_nombre_jour_travaille SET jour='.$nb_jours.' WHERE rowid='.$id_rowid.' AND fk_salarie='.$fk_salarie;
                if($db->query($sql_update)){
                    $message = "Nombre de jour travaillé modifié avec succès";
                    $action = "";

                    //Récuperation du mois
                    $sql_select = "SELECT mois FROM ".MAIN_DB_PREFIX."salarie_nombre_jour_travaille WHERE rowid=".$id_rowid." AND fk_salarie=".$fk_salarie;
					$obj_mois = $db->fetch_object($db->query($sql_select));

                    //La trace
                    $sql_select = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$user->id;
					$obj = $db->fetch_object($db->query($sql_select));

					$sql_select = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$fk_user;
					$obj_user = $db->fetch_object($db->query($sql_select));

					$soc_sql = "SELECT nom FROM ".MAIN_DB_PREFIX."societe WHERE rowid=".$id_societe;
					$soc_res = $db->query($soc_sql);//= $db->query($covSql);
					$obj_soc = $db->fetch_object($soc_res);

                    $m = "du ".$obj_mois->mois."è mois";
                    if(!empty($mois_tab[$obj_mois->mois-1]))
                        $m = "de ".$mois_tab[$obj_mois->mois-1];
					$action_effectue = "Modification du nombre de jour travaillé de ".$obj_user->firstname." ".$obj_user->lastname." ".$m." de la société ".$obj_soc->nom." de ".$ancien_val." à ".$nb_jours;
					$sql_log = 'INSERT INTO '.MAIN_DB_PREFIX.'log (fk_user, nom, prenom, quand, action_effectue, object_concerne)';
					$sql_log .= ' VALUES('.$user->id.', "'.$obj->lastname.'","'.$obj->firstname.'",now(),"'.$action_effectue.'","Jours travaillés")';
					$db->query($sql_log);
                }else{
                    $annee = GETPOST("annee", "int")?:date('Y');
                    $salSql = "SELECT * FROM ".MAIN_DB_PREFIX."salarie_nombre_jour_travaille where fk_salarie=".$fk_salarie." AND annee=".$annee." AND mois=".$id_mois;
                    $result = $db->query($salSql);
                    $num = $db->num_rows($result);
                    if($num == 0){
                        $jour = cal_days_in_month(CAL_GREGORIAN, $mois, $annee);
                        
                        $sql = 'INSERT INTO '.MAIN_DB_PREFIX.'salarie_nombre_jour_travaille (fk_societe, fk_salarie, annee, mois, jour)';
                        $sql .= ' VALUES('.$id_societe.','.$fk_salarie.','.$annee.','.$mois.','.$jour.')';
                        $db->query($sql);
                    }

                    $message = "Nombre de jour travaillé modifié avec succès";
                    $action = "";
                }
            }else $action = "edit_nombre_jours";
        }


        //-------------------------------------------------------------------------------------------
        print "<div style='float: right; display: inline''>";
        $annee_rechercher = GETPOST("annee_rechercher", "int");
        if(empty($annee_rechercher)){

            $sql_verif = "SELECT annee FROM ".MAIN_DB_PREFIX."bulletin WHERE fk_societe=".$id_societe." AND cloture='non'";
            $res_verif = $db->query($sql_verif);
            $num_all = $db->num_rows($res_verif);
            if($res_verif && 0 < $num_all){
                    $obj_annee = $db->fetch_object($res_verif);
                    $annee_rechercher = $obj_annee->annee;
                }

            if(empty($annee_rechercher))
                $annee_rechercher = (int) date("Y");
        }    

	print '<form name="add" method="POST" action="'.$_SERVER['PHP_SELF'].'?mainmenu=paiementsalaire&leftmenu=salarie&id_societe='.$id_societe.'&id='.$fk_user.'&fk_salarie='.$fk_salarie.'&id_convention='.$id_convention.'">';
	print '<input type="hidden" name="token" value="'.newToken().'">';
	print '<input type="hidden" name="action" value="annee_rechercher">';

	print "<select name='annee_rechercher'>";
				$sql_verif = "SELECT DISTINCT annee FROM ".MAIN_DB_PREFIX."bulletin WHERE fk_societe=".$id_societe." AND cloture='oui'";
	  			$res_verif = $db->query($sql_verif);
				if($res_verif){
					$num_all = $db->num_rows($res_verif);
					$i=0;
					$annee_tab = array();
					while ($i < $num_all) { 
						$obj_annee = $db->fetch_object($res_verif);
						$annee_tab[] = $obj_annee->annee;
						if($obj_annee->annee == $annee_rechercher)
							print "<option value='".($obj_annee->annee)."' selected >".($obj_annee->annee)."</option>";
						else print "<option value='".($obj_annee->annee)."'>".($obj_annee->annee)."</option>";

						
						$i ++;
					}
					if($num_all == 0){
						print "<option value='".date("Y")."' selected >".date("Y")."</option>";
					}elseif(!in_array(date("Y"), $annee_tab))
						if($annee_rechercher == $annee_courant)
							print "<option value='".date("Y")."' selected>".date("Y")."</option>";
						else print "<option value='".date("Y")."' >".date("Y")."</option>";


				}
				print "</select><input type='submit' value='Rechercher'class='button'></form>";

print "</div>";

        $annee = $annee_rechercher;
        $salSql = "SELECT date_anciennete, sursalaire FROM ".MAIN_DB_PREFIX."salarie where rowid=".$fk_salarie;
        $result = $db->query($salSql);
        $salarie = $db->fetch_object($result);
        print "<br><h3>Nombre de jour travaillé en <mark>".$annee."</mark> par mois</h3>";

        //class="tagtable liste"
        print '<table class="tagtable liste">';

        //$mois = date('m');
		$trouve = 0;
        $treize = 0;
		// Récupération du premier mois non clôturé
$premier_mois_non_cloture = 0;

for ($m = 1; $m <= 12; $m++) {
    $sql_check = "SELECT rowid 
                  FROM ".MAIN_DB_PREFIX."bulletin 
                  WHERE fk_societe = ".((int) $id_societe)." 
                  AND annee = ".((int) $annee)." 
                  AND mois = ".((int) $m)." 
                  AND cloture = 'oui'";

    $res_check = $db->query($sql_check);
    $obj_check = null;

    if ($res_check) {
        $obj_check = $db->fetch_object($res_check);
    }

    if (empty($obj_check->rowid)) {
        $premier_mois_non_cloture = $m;
        break;
    }
}

for ($i = 1; $i <= 12; $i++) {

    $salSql = "SELECT rowid, jour 
               FROM ".MAIN_DB_PREFIX."salarie_nombre_jour_travaille 
               WHERE annee = ".((int) $annee)." 
               AND mois = ".((int) $i)." 
               AND fk_salarie = ".((int) $fk_salarie);

    $result = $db->query($salSql);

    $salarie = null;

    if ($result) {
        $salarie = $db->fetch_object($result);
    }

    $jour = cal_days_in_month(CAL_GREGORIAN, $i, $annee);
    $nb_jours = !empty($salarie->jour) ? $salarie->jour : 0;
    $id_rowid = !empty($salarie->rowid) ? $salarie->rowid : 0;

    $bulletin_sql = "SELECT rowid 
                     FROM ".MAIN_DB_PREFIX."bulletin 
                     WHERE cloture = 'oui' 
                     AND fk_societe = ".((int) $id_societe)." 
                     AND annee = ".((int) $annee)." 
                     AND mois = ".((int) $i);

    $res_bulletin = $db->query($bulletin_sql);
    $obj_bulletin = null;

    if ($res_bulletin) {
        $obj_bulletin = $db->fetch_object($res_bulletin);
    }

    $mois_cloture = !empty($obj_bulletin->rowid);
    $mois_futur = ($annee == (int) date('Y') && $i > (int) date('m'));
    $mois_apres_premier_non_cloture = (
        $premier_mois_non_cloture > 0 
        && $i > $premier_mois_non_cloture
    );

    $modifiable = (
        !$mois_cloture
        && !$mois_futur
        && !$mois_apres_premier_non_cloture
    );

    if ($action == "edit_nombre_jours_".$i && $modifiable) {

        print '<div>';
        print '<form name="add" method="POST" action="'.$_SERVER['PHP_SELF'].'?mainmenu=paiementsalaire&leftmenu=salarie&id_societe='.$id_societe.'&fk_salarie='.$fk_salarie.'&id='.$fk_user.'&id_convention='.$id_convention.'&id_rowid='.$id_rowid.'">';
        print '<input type="hidden" name="token" value="'.newToken().'">';
        print '<input type="hidden" name="action" value="save_nombre_jours">';
        print '<input type="hidden" name="mois" value="'.$i.'">';
        print '<input type="hidden" name="annee" value="'.$annee.'">';

        print '<tr>';
        print '<td style="padding: 10px; width: 200px;"><b>'.$mois_tab[$i - 1].'</b></td>';
        print '<td style="padding: 10px; width: 200px;">';
        print '<input type="number" name="nb_jours" min="0" max="'.$jour.'" value="'.$nb_jours.'">';
        print '<input class="button" type="submit" value="Valider">';

        print '</form>';

        print '<a class="reposition editfielda button" href="'.$_SERVER['PHP_SELF'].'?mainmenu=paiementsalaire&leftmenu=salarie&id_societe='.$id_societe.'&fk_salarie='.$fk_salarie.'&id='.$fk_user.'&id_convention='.$id_convention.'">Annuler</a>';

        print '</td>';
        print '</tr>';
        print '</div>';

    } else {

        print '<tr class="impair">';
        print '<td style="padding: 10px; width: 200px;"><b>'.$mois_tab[$i - 1].'</b></td>';
        print '<td style="padding: 10px; width: 200px;">';
        print '<input type="text" value="'.$nb_jours.'" disabled>';

        $act = "edit_nombre_jours_".$i;

        if ($user->rights->paiementsalaire->salarie->write) {

            if ($modifiable) {

                print '<a class="reposition editfielda" href="'.$_SERVER['PHP_SELF'].'?mainmenu=paiementsalaire&leftmenu=salarie&id_societe='.$id_societe.'&fk_salarie='.$fk_salarie.'&id='.$fk_user.'&id_convention='.$id_convention.'&action='.$act.'&id_mois='.$i.'">'.img_edit('Modifier', '').'</a>';

            } else {

                if ($mois_cloture) {
                    print img_picto("Bulletin clôturé, modification impossible", "lock");
                } elseif ($mois_futur) {
                    print img_picto("Mois futur, modification impossible", "error");
                } elseif ($mois_apres_premier_non_cloture) {
                    print img_picto("Les mois suivants ne sont pas encore modifiables", "lock");
                }
            }

        } else {

            print img_edit('Permission manquante', '');
        }

        print '</td>';
        print '</tr>';
    }
}

        if ($treize) {
            $salSql = "SELECT rowid, jour FROM ".MAIN_DB_PREFIX."salarie_nombre_jour_travaille where annee=".$annee." AND mois=13 AND fk_salarie=".$fk_salarie;
            $result = $db->query($salSql);
            if($result)
                $salarie = $db->fetch_object($result);

			if($action == ("edit_nombre_jours_13")){
				print '<div><form name="add" method="POST" action="'.$_SERVER['PHP_SELF'].'?mainmenu=paiementsalaire&leftmenu=salarie&id_societe='.$id_societe.'&fk_salarie='.$fk_salarie.'&id='.$fk_user.'&id_convention='.$id_convention.'&id_rowid='.$salarie->rowid.'">';
				print '<input type="hidden" name="token" value="'.newToken().'">';
				print '<input type="hidden" name="action" value="save_nombre_jours">';
                print '<input type="hidden" name="mois" value=13>';
                print '<input type="hidden" name="annee" value='.$annee.'>';
				print '<tr>';
				print '</td><td style="padding: 10px; width: 200px;"><b>13è Mois</b></td>';
				print '<td style="padding: 10px; width: 200px;"><input type="number" name="nb_jours" min="0" max="30" value="'.($salarie->jour?:0).'">
				<input class="button" type="submit" value="Valider" >';

				print '</form>';
				print '<a class="reposition editfielda button" href="'.$_SERVER['PHP_SELF'].'?mainmenu=paiementsalaire&leftmenu=salarie&id_societe='.$id_societe.'&fk_salarie='.$fk_salarie.'&id='.$fk_user.'&id_convention='.$id_convention.'">Annuler</a>';
				print '</td></tr>';
			}else{
				print '<tr class="impair">';
				print '</td><td style="padding: 10px; width: 200px;"><b>13è Mois</b></td>';
				print '<td style="padding: 10px; width: 200px;"><input type="text" value="'.($salarie->jour?:0).'" disabled>';

				$act = "edit_nombre_jours_13";
				if($user->rights->paiementsalaire->salarie->write){
					$bulletin_sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."bulletin where cloture='oui' AND fk_salarie=".$fk_salarie." AND annee=".$annee." AND mois=13";
					$res_bulletin = $db->query($bulletin_sql);
					$obj_bulletin = $db->fetch_object($res_bulletin);
					if(empty($obj_bulletin->rowid)){
						$trouve ++;
						$info = '';
						if($trouve == 1){
							$bulletin_sql1 = "SELECT rowid FROM ".MAIN_DB_PREFIX."bulletin where fk_salarie=".$fk_salarie." AND annee=".$annee." AND mois=13";
							$res_bulletin1 = $db->query($bulletin_sql1);
							$obj_bulletin1 = $db->fetch_object($res_bulletin1);
						if($obj_bulletin1->rowid)
							$info = img_picto("Mois en cours du bulletin", "tick");
						}
						print $info.'<a class="reposition editfielda" href="'.$_SERVER['PHP_SELF'].'?mainmenu=paiementsalaire&leftmenu=salarie&id_societe='.$id_societe.'&fk_salarie='.$fk_salarie.'&id='.$fk_user.'&id_convention='.$id_convention.'&action='.$act.'&id_mois=13">'.img_edit('Modifier','').'</a>';
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