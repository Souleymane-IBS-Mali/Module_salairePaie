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

require '../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/accounting.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formother.class.php';
//require_once DOL_DOCUMENT_ROOT.'/core/class/html.formpaiementsalaire.class.php';
//require_once DOL_DOCUMENT_ROOT.'/custom/paiementsalaire/core/modules/modPaiementSalaire.class.php';

//$PaiementSalaire = new modPaiementSalaire($db);

llxHeader("", "Paiement | Salaire");
//include(DOL_DOCUMENT_ROOT.'/paiementsalaire/installateur.php');
//include(DOL_DOCUMENT_ROOT.'/paiementsalaire/onglets/whmcs.php');
//include('./versionnig.php');

$showtutorial = img_picto('', 'object_accounting',);

   print load_fiche_titre(''. $showtutorial. "Les statistiques concernant l'utilisation de ce module", '', '')."\n";
    //print '<hr>';
//----------------------------------------------------------------------------------------------------------------------------------------------------------------------
   //Insertion de la version
   $num_v = "1.4.0";
   $statut = "stable";
   $changelog = "Correction et Ajout";
   $compatible_dolibarr = "Correction de la gestion des avances et acomptes, avec ajout de détails visuels pour améliorer la lisibilité.";
   $download_link = "https://dolipaie-ibs-mali.com";
   $autheur = "Internet Business Services IBS-Mali";

   $soc_sql = "SELECT * FROM ".MAIN_DB_PREFIX."version_dolipaie WHERE numero_version='".$num_v."'";
   $soc_res = $db->query($soc_sql);//= $db->query($covSql);
   if($soc_res)
        $num = $db->num_rows($soc_res);

    if(0 >= $num ){

        $soc_sql = "SELECT * FROM ".MAIN_DB_PREFIX."version_dolipaie";
        $soc_res = $db->query($soc_sql);//= $db->query($covSql);
        if($soc_res)
            $nb = $db->num_rows($soc_res);

        if($nb > 0 ){
            $soc_sql = "UPDATE ".MAIN_DB_PREFIX."version_dolipaie SET active=0 WHERE active=1";
            $soc_res = $db->query($soc_sql);
        }

        $sql_version = 'INSERT INTO '.MAIN_DB_PREFIX.'version_dolipaie (numero_version, date_publication, statut, changelog, compatibilite_dolibarr, lien_telechargement, autheur, active)';
        $sql_version .= ' VALUES("'.$num_v.'",now(),"'.$statut.'","'.$changelog.'","'.$compatible_dolibarr.'","'.$download_link.'", "'.$autheur.'",1)';
        $db->query($sql_version);
        print $db->error();

    }

//-------------------------------------------------------------------------------------------------------------------------------------------------------------------------
print "<div style='display:flex; flex:2; flex-direction:row;'>";
print "<div style='flex:1; border-bottom: 1px solid #f0ecec;'>";
    print '<table class="tagtable liste">';
    print "<tr><td style='background: #f0ecec; padding: 10px;' align='center' colspan='3'>Données gérées par le module
    </td></tr>";
	print '<tr  class="pair">';
    print '<td style="width: 10%;">Nombre de <b>Sociétés</b></td>';
	print '<td style="width: 10%;">Nombre de <b>Salariés</b></td>';
    print '<td style="width: 10%;"><b>Mois</b> du dernier salaire Traité</b></td>';

	print '</tr>';

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

    $sql = "SELECT sc.rowid as r1, sc.nom, sc.name_alias, sc.phone, sce.rowid as r2, sce.fk_object FROM ".MAIN_DB_PREFIX."societe as sc";
	$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."societe_extrafields as sce ON sc.rowid=sce.fk_object";
    if($user->id != 1)
        $sql .= " WHERE sc.rowid IN ".$array_id_soc." AND sce.grp=1";
    else   
        $sql .= " WHERE sce.grp=1";
    
	$result = $db->query($sql);
		
    $num_societe = 0;
    $ligne = 0;
	if($result){
        $num_societe = $db->num_rows($result);
		$i = 0;
		$sql_salarie = "SELECT count(rowid) as nb FROM ".MAIN_DB_PREFIX."salarie";
        $res = $db->query($sql_salarie);
        if($res){
            $ligne = $db->fetch_object($res)->nb;
        }
    }

    $sql_verif = "SELECT annee, mois FROM ".MAIN_DB_PREFIX."bulletin WHERE cloture='oui' ORDER BY annee DESC, mois DESC";
	$res_verif = $db->query($sql_verif);
    $mois_tab = array(" Janvier "," Février "," Mars "," Avril "," Mai "," Juin "," Juillet "," Août "," Septembre "," Octobre "," Novembre "," Décembre ");

    $date_aff = "N/A ".img_picto('', 'calendar', 'class="paddingright pictofixedwidth"');
    if($res_verif){
     $date = $db->fetch_object($res_verif);
     if($date)
        $date_aff = $mois_tab[$date->mois - 1]."".$date->annee ." ".img_picto('', 'calendar', 'class="paddingright pictofixedwidth"');
    }

    print "<tr class='impair'><td align='center'>".$num_societe." ".img_picto('', 'company', 'class="paddingright pictofixedwidth"')."</b></td><td align='center'>".$ligne." ".img_picto('', 'user', 'class="paddingright pictofixedwidth"')."</td><td align='center'>".$date_aff."</td></tr>";
    print "</table>";
    print "</div>";


    print "<div style='flex:1; margin-left: 3%; border-bottom: 1px solid #f0ecec;'>";
        print '<table class="tagtable liste">';
        print "<tr><td align='center' colspan='3' style='background: #f0ecec; padding: 10px;'>Masse salariale par société</td></tr>";
        print '<tr  class="pair">';
        print '<td style="width: 10%;">Nom <b>Sociétés</b></td>';
        print '<td style="width: 10%;">Masse <b>Salariale brut</b></td>';
        print '<td style="width: 10%;"><b>Mois</b> du dernier traitement</b></td>';

	print '</tr>';

    $tab = array();
    $sql_societe = "SELECT sc.rowid, sc.nom, sc.name_alias, sc.phone, sce.rowid as r2, sce.fk_object FROM ".MAIN_DB_PREFIX."societe as sc";
	$sql_societe .= " LEFT JOIN ".MAIN_DB_PREFIX."societe_extrafields as sce ON sc.rowid=sce.fk_object";
    if($user->id != 1)
        $sql_societe .= " WHERE sc.rowid IN ".$array_id_soc." AND sce.grp=1";
    else   
        $sql_societe .= " WHERE sce.grp=1";

	$result = $db->query($sql_societe);

    if($result){
        $num = $db->num_rows($result);
        $i = 0;

        while ($i < $num) {
            
            $masse_salariale = 0;
            $societe = $db->fetch_object($result);
            $sql_masse_sal = "SELECT mois, annee, date_creation FROM ".MAIN_DB_PREFIX."bulletin";
            $sql_masse_sal .= " WHERE fk_societe=".$societe->rowid." AND cloture='oui' ORDER BY annee DESC, mois DESC";

            if($db->query($sql_masse_sal) && $db->num_rows($db->query($sql_masse_sal))){
                $obj_bull = $db->fetch_object($db->query($sql_masse_sal));                
                $sql_masse_sal = "SELECT SUM(salaire_brut) as masse_salariale FROM ".MAIN_DB_PREFIX."bulletin";
                $sql_masse_sal .= " WHERE fk_societe=".$societe->rowid." AND mois=".$obj_bull->mois." AND annee=".$obj_bull->annee." ORDER BY annee DESC, mois DESC";
                $result1 = $db->query($sql_masse_sal);

                if($result1 && $db->num_rows($result1)){
                    $masse_salariale_obj = $db->fetch_object($result1);                
                    $masse_salariale = $masse_salariale_obj->masse_salariale;

                    $t = count($tab);
                    $tab[$t][0] = apres_virgule($db, $societe->rowid, $masse_salariale);
                    $tab[$t][1] = $societe->nom;
                    $tab[$t][2] = $mois_tab[$obj_bull->mois-1]." ".$obj_bull->annee;
                    $tab[$t][3] = $obj_bull->date_creation;

                }
            }

            $i ++;
        }
        if(count($tab) > 1)
            for ($i=0; $i < count($tab)-1; $i++) { 
                if($tab[$i][3] > $tab[$i+1][3]){
                        $m1 = $tab[$i][0];
                        $m2 = $tab[$i][1];
                        $m3 = $tab[$i][2];
                        $m4 = $tab[$i][3];

                        $tab[$i][0] = $tab[$i + 1][0];
                        $tab[$i][1] = $tab[$i + 1][1];
                        $tab[$i][2] = $tab[$i + 1][2];
                        $tab[$i][3] = $tab[$i + 1][3];
                        

                        $tab[$i + 1][0] = $m1;
                        $tab[$i + 1][1] = $m2;
                        $tab[$i + 1][2] = $m3;
                        $tab[$i + 1][3] = $m4;
                }
            }

        for ($i=0; $i < count($tab); $i++) { 
            if($i < 5)
            print "<tr class='impair' style='background: none'><td> ".img_picto('', 'company', 'class="paddingright pictofixedwidth"')."".$tab[$i][1]."</td><td align='' style='background: none'>".$tab[$i][0]." ".img_picto('', 'bill', 'class="paddingright pictofixedwidth"')."</td><td align=''>".$tab[$i][2]."</td></tr>";

        }

    }


    print '</table>';
    print "</div>";
print "</div><br>";
print "<div style='display:flex; flex:2; flex-direction:row;'>";
print "<div style='flex:2; border-top: 1px border-bottom: 1px solid #f0ecec;'>";
    print '<table class="tagtable liste">';
    print "<tr><td style='background: #f0ecec; padding: 15px;' align='center' colspan='4'>Dernier montant I.N.P.S déclaré
    </td></tr>";
	print '<tr  class="pair">';
	print '<td style="width: 8%;">Nom <b>Sociétés</b></td>';
	print '<td style="width: 8%;">Nombre de <b>Salariés</b></td>';
    print '<td style="width: 8%;"><b>Montant</b></td>';
    print '<td style="width: 16%;"><b>Mois</b> du dernier traitement</b></td>';

	print '</tr>';

    $sql_societe = "SELECT sc.rowid, sc.nom, sc.name_alias, sc.phone, sce.rowid as r2, sce.fk_object FROM ".MAIN_DB_PREFIX."societe as sc";
	$sql_societe .= " LEFT JOIN ".MAIN_DB_PREFIX."societe_extrafields as sce ON sc.rowid=sce.fk_object";
    if($user->id != 1)
        $sql_societe .= " WHERE sc.rowid IN ".$array_id_soc." AND sce.grp=1";
    else   
        $sql_societe .= " WHERE sce.grp=1";
	$result = $db->query($sql_societe);

    if($result){
        $num = $db->num_rows($result);
        $i = 0;
        while ($i < $num) {
            $somme_cotisation = 0;
			$somme_cotisation_employe = 0;
			$somme_cotisation_employeur = 0;
            $masse_salariale = 0;
            $nb_salarie = 0;
            $societe = $db->fetch_object($result);
            $sql_masse_sal = "SELECT mois, annee, cloture FROM ".MAIN_DB_PREFIX."bulletin";
            $sql_masse_sal .= " WHERE fk_societe=".$societe->rowid." AND cloture='oui' ORDER BY annee DESC, mois DESC";

            if($db->query($sql_masse_sal)){
                $obj_bull = $db->fetch_object($db->query($sql_masse_sal));  
                $sql_masse_sal = "SELECT SUM(salaire_brut) as masse_salariale FROM ".MAIN_DB_PREFIX."bulletin";
                $sql_masse_sal .= " WHERE fk_societe=".$societe->rowid." AND mois=".$obj_bull->mois." AND annee=".$obj_bull->annee." ORDER BY annee DESC, mois DESC";
                $result1 = $db->query($sql_masse_sal);

                if($result1 && $db->num_rows($result1)){
                    $masse_salariale_obj = $db->fetch_object($result1);                
                    $masse_salariale = $masse_salariale_obj->masse_salariale;

                    $sql_nb_sal = "SELECT DISTINCT fk_salarie, rowid FROM ".MAIN_DB_PREFIX."bulletin";
                    $sql_nb_sal .= " WHERE fk_societe=".$societe->rowid." AND mois=".$obj_bull->mois." AND annee=".$obj_bull->annee;
                    $result_nb_sal = $db->query($sql_nb_sal);
                    if($result_nb_sal){
                        $nb_salarie = $db->num_rows($result_nb_sal);
                        $a = 0;
                        while ($a <= $nb_salarie) {

                            $obj_id_bulletin = $db->fetch_object($result_nb_sal);
                            $sql_som_taxe = "SELECT SUM(montant) as montant FROM ".MAIN_DB_PREFIX."bulletin_taxe WHERE fk_bulletin=".$obj_id_bulletin->rowid;
                            $res_som_taxe  = $db->query($sql_som_taxe);
                            if($res_som_taxe){
                                $obj_som_taxe = $db->fetch_object($res_som_taxe);
                                $somme_taxe += $obj_som_taxe->montant;
                            }

                            $sql_som_cotisation = "SELECT SUM(montant_employe) as som_empl, SUM(montant_employeur) as som_patro FROM ".MAIN_DB_PREFIX."bulletin_cotisation WHERE fk_bulletin=".$obj_id_bulletin->rowid;
                            $res_som_cotisation  = $db->query($sql_som_cotisation);
                            if($res_som_cotisation){
                                $obj_som_cotisation = $db->fetch_object($res_som_cotisation);
                                $somme_cotisation_employe += $obj_som_cotisation->som_empl;
                                $somme_cotisation_employeur += $obj_som_cotisation->som_patro;
                            }
                            
                            $a ++;
                        }
                        $somme_cotisation += $somme_cotisation_employe + $somme_cotisation_employeur;
						//$total += $somme_taxe + $somme_cotisation;
                    }

                    print "<tr class='impair' style='background: none'><td> ".img_picto('', 'company', 'class="paddingright pictofixedwidth"')."".$societe->nom."</td><td align='' style='background: none'>".$nb_salarie." ".img_picto('', 'user', 'class="paddingright pictofixedwidth"')."</td><td>".apres_virgule($db, $societe->rowid, $somme_cotisation)." ".img_picto('', 'bill', 'class="paddingright pictofixedwidth"')."</td><td align=''>".$mois_tab[$obj_bull->mois-1]." ".$obj_bull->annee."</td></tr>";

                }
            }

            $i ++;
        }

    }

    print "</table>";
    print "</div>";
    
    
        
    /*print "<div style='flex:1; margin-left: 3%; border: 1px solid green;'>";
        print '<table>';
        print "<tr><td align='center' colspan='2'>".load_fiche_titre("Masse salariale par société", '', '')."
    </td></tr>";
        print '<tr  class="pair">';
        print '<td style="width: 10%;">Nom <b>Sociétés</b></td>';
        print '<td style="width: 10%;">Masse <b>Salariés</b></td>';
        print '<td style="width: 10%;"><b>Mois</b> du dernier traitement</b></td>';

	print '</tr>';

    $sql_societe = "SELECT sc.rowid as r1, sc.nom, sc.name_alias, sc.phone, sce.rowid as r2, sce.fk_object FROM ".MAIN_DB_PREFIX."societe as sc";
	$sql_societe .= " LEFT JOIN ".MAIN_DB_PREFIX."societe_extrafields as sce ON sc.rowid=sce.fk_object WHERE sce.grp=1";
	$result = $db->query($sql_societe);

    if($result){
        $masse_salaire = 0;
        $num = $db->num_rows($result);
        $i = 0;
        while ($i < $num) {
            $societe = $db->fetch_object($result);
            $sql_user = "SELECT u.rowid, u.lastname, u.firstname, u.dateemployment, ue.fk_object, ue.egp FROM ".MAIN_DB_PREFIX."user as u";
            $sql_user .= " LEFT JOIN ".MAIN_DB_PREFIX."user_extrafields as ue ON u.rowid=ue.fk_object Where ue.egp=".$societe->r1;
            $result1 = $db->query($sql_user);
            if($result1){
                $numj = $db->num_rows($result1);
                $j = 0;
                while ($j < $numj){
                    $user = $db->fetch_object($result1);

                    $sql_salarie = "SELECT * FROM ".MAIN_DB_PREFIX."salarie WHERE fk_user=".$user->rowid." AND matricule!=''";
                    $res = $db->query($sql_salarie);
                    if($res){
                        $obj_salarie = $db->fetch_object($res);
                        $bulletin_sql = "SELECT * FROM ".MAIN_DB_PREFIX."bulletin where matricule='".$obj_salarie->matricule."' AND annee=".$date->annee." AND mois=".$date->mois;
                        $res_bulletin = $db->query($bulletin_sql);
                        if($res_bulletin){
                            $obj_bulletin = $db->fetch_object($res_bulletin);
                            $masse_salaire += $obj_bulletin->salaire_brut;
                        }
                    }
                    $j ++;
                }

            }
            print "<tr class='impair'><td align='center'><b>".$societe->nom."</b></td><td align='center'><b>".round($masse_salaire)."</b></td><td align='center'><b>".$date_aff."</td></tr>";

            $i ++;
        }

    }

    print '</table>';
    print "</div>";
*/
print "</div>";
$db->free();

//print '<button><a href="../afrik_emploi_send_mail_friday.php" class="button">Envoyer Mail</a></button>';

function apres_virgule($db, $id_societe, $valeur){
    $sep = ".";
    $decalage = 2;
    $reglage_bulletin = "SELECT separateur, decalage FROM ".MAIN_DB_PREFIX."reglage_bulletin WHERE fk_societe=".$id_societe;
      $result_reglage_bulletin = $db->query($reglage_bulletin);
      if($db->num_rows($result_reglage_bulletin) > 0){
        $obj_reglage_bulletin = $db->fetch_object($result_reglage_bulletin);
        $sep = $obj_reglage_bulletin->separateur;
        $decalage = $obj_reglage_bulletin->decalage;
      }
    return number_format($valeur, $decalage, $sep, ' ');
  }
