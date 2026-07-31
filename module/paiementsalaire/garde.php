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

$showtutorial = img_picto('', 'object_accounting');

   print load_fiche_titre(''. $showtutorial. "Les statistiques concernant l'utilisation de ce module", '', '')."\n";
    //print '<hr>';
//----------------------------------------------------------------------------------------------------------------------------------------------------------------------
// Insertion de la nouvelle version
$num_v = "1.5.0";
$statut = "stable";

$changelog = "Ajout de la gestion complète des congés : "
    ."initialisation et configuration des soldes, "
    ."acquisition mensuelle de 2,5 jours, "
    ."paiement partiel ou total des jours de congé, "
    ."historique des paiements, alertes de solde "
    ."et affichage des congés sur les bulletins de salaire.";

$compatible_dolibarr = "Ajout de la gestion des congés et amélioration "
    ."de la génération des bulletins avec enregistrement des jours acquis, "
    ."des jours payés et du solde de congé.";

$download_link = "https://dolipaie-ibs-mali.com";
$autheur = "Internet Business Services IBS-Mali";

$num = 0;
$nb = 0;

/*
 * Vérifier si cette version existe déjà.
 */
$soc_sql = 'SELECT rowid';
$soc_sql .= ' FROM '.MAIN_DB_PREFIX.'version_dolipaie';
$soc_sql .= ' WHERE numero_version="';
$soc_sql .= $db->escape($num_v).'"';

$soc_res = $db->query($soc_sql);

if ($soc_res) {
    $num = $db->num_rows($soc_res);
}

/*
 * Ajouter la version seulement si elle n'existe pas.
 */
if ($num <= 0) {
    $db->begin();

    /*
     * Désactiver les anciennes versions.
     */
    $soc_sql = 'UPDATE '.MAIN_DB_PREFIX.'version_dolipaie';
    $soc_sql .= ' SET active=0';
    $soc_sql .= ' WHERE active=1';

    $resDesactivation = $db->query($soc_sql);

    if (!$resDesactivation) {
        $db->rollback();
    } else {
        /*
         * Ajouter et activer la nouvelle version.
         */
        $sql_version = 'INSERT INTO ';
        $sql_version .= MAIN_DB_PREFIX.'version_dolipaie';

        $sql_version .= ' (';
        $sql_version .= 'numero_version,';
        $sql_version .= ' date_publication,';
        $sql_version .= ' statut,';
        $sql_version .= ' changelog,';
        $sql_version .= ' compatibilite_dolibarr,';
        $sql_version .= ' lien_telechargement,';
        $sql_version .= ' autheur,';
        $sql_version .= ' active';
        $sql_version .= ')';

        $sql_version .= ' VALUES (';
        $sql_version .= '"'.$db->escape($num_v).'",';
        $sql_version .= ' NOW(),';
        $sql_version .= '"'.$db->escape($statut).'",';
        $sql_version .= '"'.$db->escape($changelog).'",';
        $sql_version .= '"'.$db->escape(
            $compatible_dolibarr
        ).'",';
        $sql_version .= '"'.$db->escape(
            $download_link
        ).'",';
        $sql_version .= '"'.$db->escape(
            $autheur
        ).'",';
        $sql_version .= ' 1';
        $sql_version .= ')';

        $resVersion = $db->query($sql_version);

        if (!$resVersion) {
            $db->rollback();
        } else {
            $db->commit();
        }
    }
}
//-------------------------------------------------------------------------------------------------------------------------------------------------------------------------

/* Sociétés visibles par l'utilisateur connecté. */
$array_id_soc = "(0";
$sql = "SELECT fk_soc FROM ".MAIN_DB_PREFIX."societe_commerciaux";
$sql .= " WHERE fk_user=".(int) $user->id;
$result = $db->query($sql);
if ($result) {
    $i = 0;
    $num = $db->num_rows($result);
    while ($i < $num) {
        $objSocVisible = $db->fetch_object($result);
        if ($objSocVisible) {
            $array_id_soc .= ", ".(int) $objSocVisible->fk_soc;
        }
        $i++;
    }
}
$array_id_soc .= ")";

/*
 * Initialise les soldes manquants de tous les salariés de toutes les sociétés.
 * Cette fonction ne modifie jamais un solde déjà existant.
 */
$resultatInitialisationConge = initialiser_soldes_conge_tous_salaries(
    $db,
    (int) $conf->entity,
    (int) $user->id,
    $array_id_soc
);

if ($resultatInitialisationConge['crees'] > 0) {
    $messageInitialisation = $resultatInitialisationConge['crees'].' solde(s) de congé initialisé(s).';
    if ($resultatInitialisationConge['ignores'] > 0) {
        $messageInitialisation .= ' '.$resultatInitialisationConge['ignores'].' salarié(s) ignoré(s) faute de date valide.';
    }
    setEventMessages($messageInitialisation, null, 'mesgs');
}

/* Crédit mensuel de 2,5 jours, une seule fois par salarié et par mois, à partir du 25. */
$resultatMiseAJourConge = mettre_a_jour_soldes_conge_le_25(
    $db,
    (int) $conf->entity,
    (int) $user->id,
    $array_id_soc
);

if ($resultatMiseAJourConge['credites'] > 0) {
    setEventMessages(
        $resultatMiseAJourConge['credites'].' solde(s) de congé mis à jour pour '.$resultatMiseAJourConge['periode'].'.',
        null,
        'mesgs'
    );
}

/*
 * Alerte congé : solde projeté à la fin du mois courant >= 30 jours.
 * Les paiements déjà archivés sont déduits du calcul.
 */
$alertesConge = array();
$sqlConge = "SELECT s.rowid AS fk_salarie, s.fk_user, u.firstname, u.lastname,";
$sqlConge .= " ue.egp AS fk_societe, sc.nom AS nom_societe, sce.conv AS id_convention,";
$sqlConge .= " cs.solde_jours";
$sqlConge .= " FROM ".MAIN_DB_PREFIX."salarie_conge_solde AS cs";
$sqlConge .= " INNER JOIN ".MAIN_DB_PREFIX."salarie AS s ON s.rowid=cs.fk_salarie";
$sqlConge .= " LEFT JOIN ".MAIN_DB_PREFIX."user AS u ON u.rowid=s.fk_user";
$sqlConge .= " LEFT JOIN ".MAIN_DB_PREFIX."user_extrafields AS ue ON ue.fk_object=u.rowid";
$sqlConge .= " LEFT JOIN ".MAIN_DB_PREFIX."societe AS sc ON sc.rowid=ue.egp";
$sqlConge .= " LEFT JOIN ".MAIN_DB_PREFIX."societe_extrafields AS sce ON sce.fk_object=sc.rowid";
$sqlConge .= " WHERE cs.entity=".(int) $conf->entity." AND sce.grp=1";
if ($user->id != 1) {
    $sqlConge .= " AND sc.rowid IN ".$array_id_soc;
}
$sqlConge .= " ORDER BY cs.solde_jours DESC";

$resConge = $db->query($sqlConge);
if ($resConge) {
    while ($objConge = $db->fetch_object($resConge)) {
        $soldeActuelConge = round((float) $objConge->solde_jours, 2);
        /* Avant le 25, afficher la projection incluant le prochain crédit mensuel. */
        $soldeFinMoisConge = $soldeActuelConge + (((int) date('d') < 25) ? 2.5 : 0);

        if ($soldeFinMoisConge >= 30) {
            $alertesConge[] = array(
                'fk_salarie' => (int) $objConge->fk_salarie,
                'fk_user' => (int) $objConge->fk_user,
                'fk_societe' => (int) $objConge->fk_societe,
                'id_convention' => (int) $objConge->id_convention,
                'nom' => trim($objConge->firstname.' '.$objConge->lastname),
                'societe' => $objConge->nom_societe,
                'solde_actuel' => $soldeActuelConge,
                'solde_fin_mois' => $soldeFinMoisConge
            );
        }
    }
}

if (!empty($alertesConge)) {
    usort($alertesConge, function ($a, $b) {
        return $b['solde_fin_mois'] <=> $a['solde_fin_mois'];
    });
    $alertesConge = array_slice($alertesConge, 0, 10);

    print '<style>
    .conge-alert{margin:18px 0 24px;border:1px solid #f59e0b;border-left:5px solid #f59e0b;border-radius:8px;background:#fffbeb;overflow:hidden}
    .conge-alert-head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:15px 18px;color:#92400e;font-weight:700}
    .conge-alert-count{display:inline-flex;align-items:center;justify-content:center;min-width:26px;height:26px;padding:0 7px;border-radius:20px;background:#f59e0b;color:#fff}
    .conge-alert table{margin:0;width:100%;background:#fff}.conge-alert .projection{color:#b45309;font-weight:700}
    </style>';

    print '<div class="conge-alert">';
    print '<div class="conge-alert-head"><span>'.img_picto('', 'warning').' Alerte : soldes de congé atteignant au moins 30 jours à la fin du mois</span>';
    print '<span class="conge-alert-count">'.count($alertesConge).'</span></div>';
    print '<div class="div-table-responsive"><table class="tagtable liste">';
    print '<tr class="liste_titre"><td>Salarié</td><td>Société</td><td class="right">Solde actuel</td><td class="right">Solde à la fin du mois</td></tr>';

    foreach ($alertesConge as $alerteConge) {
        $urlConge = dol_buildpath('/paiementsalaire/onglets/conge.php', 1);
        $urlConge .= '?mainmenu=paiementsalaire&leftmenu=salarie';
        $urlConge .= '&id='.(int) $alerteConge['fk_user'];
        $urlConge .= '&fk_salarie='.(int) $alerteConge['fk_salarie'];
        $urlConge .= '&id_societe='.(int) $alerteConge['fk_societe'];
        $urlConge .= '&id_convention='.(int) $alerteConge['id_convention'];

        print '<tr class="oddeven">';
        print '<td><a href="'.dol_escape_htmltag($urlConge).'">'.dol_escape_htmltag($alerteConge['nom'] ?: 'Salarié #'.$alerteConge['fk_salarie']).'</a></td>';
        print '<td>'.dol_escape_htmltag($alerteConge['societe'] ?: 'N/A').'</td>';
        print '<td class="right">'.price2num($alerteConge['solde_actuel'], 'MT').' jour(s)</td>';
        print '<td class="right projection">'.price2num($alerteConge['solde_fin_mois'], 'MT').' jour(s)</td>';
        print '</tr>';
    }

    print '</table></div></div>';
}

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
    $mois_tab = array(" Janvier "," Février "," Mars "," Avril "," Mai "," Juin "," Juillet "," Août "," Septembre "," Octobre "," Novembre "," Décembre ", " 13è Mois ");

    $date_aff = "N/A ".img_picto('', 'calendar', 'class="paddingright pictofixedwidth"');
    if($res_verif){
     $date = $db->fetch_object($res_verif);
     if($date)
        $date_aff = (isset($mois_tab[(int) $date->mois - 1]) ? $mois_tab[(int) $date->mois - 1] : $date->mois)."".$date->annee ." ".img_picto('', 'calendar', 'class="paddingright pictofixedwidth"');
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
            if (!$societe) {
                $i++;
                continue;
            }
            $sql_masse_sal = "SELECT mois, annee, date_creation FROM ".MAIN_DB_PREFIX."bulletin";
            $sql_masse_sal .= " WHERE fk_societe=".$societe->rowid." AND cloture='oui' ORDER BY annee DESC, mois DESC";

            $res_masse_periode = $db->query($sql_masse_sal);
            if($res_masse_periode && $db->num_rows($res_masse_periode) > 0){
                $obj_bull = $db->fetch_object($res_masse_periode);                
                $sql_masse_sal = "SELECT SUM(salaire_brut) as masse_salariale FROM ".MAIN_DB_PREFIX."bulletin";
                $sql_masse_sal .= " WHERE fk_societe=".$societe->rowid." AND mois=".$obj_bull->mois." AND annee=".$obj_bull->annee." ORDER BY annee DESC, mois DESC";
                $result1 = $db->query($sql_masse_sal);

                if($result1 && $db->num_rows($result1)){
                    $masse_salariale_obj = $db->fetch_object($result1);                
                    $masse_salariale = $masse_salariale_obj->masse_salariale;

                    $t = count($tab);
                    $tab[$t][0] = apres_virgule($db, $societe->rowid, $masse_salariale);
                    $tab[$t][1] = $societe->nom;
                    $tab[$t][2] = (isset($mois_tab[(int) $obj_bull->mois - 1]) ? $mois_tab[(int) $obj_bull->mois - 1] : $obj_bull->mois)." ".$obj_bull->annee;
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
            $somme_taxe = 0;
			$somme_cotisation_employe = 0;
			$somme_cotisation_employeur = 0;
            $masse_salariale = 0;
            $nb_salarie = 0;
            $societe = $db->fetch_object($result);
            if (!$societe) {
                $i++;
                continue;
            }
            $sql_masse_sal = "SELECT mois, annee, cloture FROM ".MAIN_DB_PREFIX."bulletin";
            $sql_masse_sal .= " WHERE fk_societe=".$societe->rowid." AND cloture='oui' ORDER BY annee DESC, mois DESC";

            $res_masse_periode = $db->query($sql_masse_sal);
            if($res_masse_periode && $db->num_rows($res_masse_periode) > 0){
                $obj_bull = $db->fetch_object($res_masse_periode);  
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
                        while ($a < $nb_salarie) {

                            $obj_id_bulletin = $db->fetch_object($result_nb_sal);
                            if (!$obj_id_bulletin) {
                                $a++;
                                continue;
                            }
                            $sql_som_taxe = "SELECT SUM(montant) as montant FROM ".MAIN_DB_PREFIX."bulletin_taxe WHERE fk_bulletin=".$obj_id_bulletin->rowid;
                            $res_som_taxe  = $db->query($sql_som_taxe);
                            if($res_som_taxe){
                                $obj_som_taxe = $db->fetch_object($res_som_taxe);
                                $somme_taxe += (float) ($obj_som_taxe->montant ?? 0);
                            }

                            $sql_som_cotisation = "SELECT SUM(montant_employe) as som_empl, SUM(montant_employeur) as som_patro FROM ".MAIN_DB_PREFIX."bulletin_cotisation WHERE fk_bulletin=".$obj_id_bulletin->rowid;
                            $res_som_cotisation  = $db->query($sql_som_cotisation);
                            if($res_som_cotisation){
                                $obj_som_cotisation = $db->fetch_object($res_som_cotisation);
                                $somme_cotisation_employe += (float) ($obj_som_cotisation->som_empl ?? 0);
                                $somme_cotisation_employeur += (float) ($obj_som_cotisation->som_patro ?? 0);
                            }
                            
                            $a ++;
                        }
                        $somme_cotisation += $somme_cotisation_employe + $somme_cotisation_employeur;
						//$total += $somme_taxe + $somme_cotisation;
                    }

                    print "<tr class='impair' style='background: none'><td> ".img_picto('', 'company', 'class="paddingright pictofixedwidth"')."".$societe->nom."</td><td align='' style='background: none'>".$nb_salarie." ".img_picto('', 'user', 'class="paddingright pictofixedwidth"')."</td><td>".apres_virgule($db, $societe->rowid, $somme_cotisation)." ".img_picto('', 'bill', 'class="paddingright pictofixedwidth"')."</td><td align=''>".(isset($mois_tab[(int) $obj_bull->mois - 1]) ? $mois_tab[(int) $obj_bull->mois - 1] : $obj_bull->mois)." ".$obj_bull->annee."</td></tr>";

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
llxFooter();

//print '<button><a href="../afrik_emploi_send_mail_friday.php" class="button">Envoyer Mail</a></button>';

function apres_virgule($db, $id_societe, $valeur){
    $sep = ".";
    $decalage = 2;
    $reglage_bulletin = "SELECT separateur, decalage FROM ".MAIN_DB_PREFIX."reglage_bulletin WHERE fk_societe=".$id_societe;
      $result_reglage_bulletin = $db->query($reglage_bulletin);
      if($result_reglage_bulletin && $db->num_rows($result_reglage_bulletin) > 0){
        $obj_reglage_bulletin = $db->fetch_object($result_reglage_bulletin);
        $sep = $obj_reglage_bulletin->separateur;
        $decalage = $obj_reglage_bulletin->decalage;
      }
    return number_format((float) $valeur, (int) $decalage, $sep, ' ');
  }

/**
 * Crée le solde de congé de tous les salariés qui n'en ont pas encore.
 * Date utilisée : salarie.date_anciennete, sinon user.dateemployment.
 * Calcul : 2,5 jours par mois complet écoulé.
 *
 * @param DoliDB $db Connexion à la base Dolibarr
 * @param int $entity Entité Dolibarr
 * @param int $fkAdmin Utilisateur ayant déclenché l'initialisation
 * @param string $arrayIdSoc Liste SQL des sociétés visibles, par exemple (0, 1, 2)
 * @return array{crees:int, ignores:int, erreurs:int}
 */
function initialiser_soldes_conge_tous_salaries($db, $entity, $fkAdmin, $arrayIdSoc)
{
    $resultat = array(
        'crees' => 0,
        'ignores' => 0,
        'erreurs' => 0
    );

    /* Même sélection que la page de liste des salariés. */
    $sql = 'SELECT s.rowid AS fk_salarie, s.date_anciennete, u.dateemployment';
    $sql .= ' FROM '.MAIN_DB_PREFIX.'user AS u';
    $sql .= ' LEFT JOIN '.MAIN_DB_PREFIX.'user_extrafields AS ue ON u.rowid=ue.fk_object';
    $sql .= ' LEFT JOIN '.MAIN_DB_PREFIX.'societe AS soc ON soc.rowid=ue.egp';
    $sql .= ' LEFT JOIN '.MAIN_DB_PREFIX.'societe_extrafields AS sce ON soc.rowid=sce.fk_object';
    $sql .= ' INNER JOIN '.MAIN_DB_PREFIX.'salarie AS s ON s.fk_user=u.rowid';
    $sql .= ' LEFT JOIN '.MAIN_DB_PREFIX.'salarie_conge_solde AS cs';
    $sql .= ' ON cs.fk_salarie=s.rowid AND cs.entity='.(int) $entity;
    $sql .= ' WHERE cs.rowid IS NULL AND sce.grp=1';
    if ($fkAdmin != 1) {
        $sql .= ' AND soc.rowid IN '.$arrayIdSoc;
    }
    $sql .= ' ORDER BY u.lastname ASC, u.firstname ASC';

    $resql = $db->query($sql);
    if (!$resql) {
        $resultat['erreurs']++;
        return $resultat;
    }

    $aujourdhui = new DateTime(date('Y-m-d'));

    while ($salarieConge = $db->fetch_object($resql)) {
        $dateReference = '';
        $sourceReference = '';

        if (!empty($salarieConge->date_anciennete)) {
            $dateReference = substr($salarieConge->date_anciennete, 0, 10);
            $sourceReference = 'anciennete';
        } elseif (!empty($salarieConge->dateemployment)) {
            $dateReference = substr($salarieConge->dateemployment, 0, 10);
            $sourceReference = 'employment';
        }

        if (empty($dateReference)) {
            $resultat['ignores']++;
            continue;
        }

        try {
            $dateDebut = new DateTime($dateReference);
            if ($dateDebut > $aujourdhui) {
                $resultat['ignores']++;
                continue;
            }

            $difference = $dateDebut->diff($aujourdhui);
            $moisComplets = ((int) $difference->y * 12) + (int) $difference->m;
            $soldeInitial = round($moisComplets * 2.5, 2);

            $sqlInsert = 'INSERT IGNORE INTO '.MAIN_DB_PREFIX.'salarie_conge_solde';
            $sqlInsert .= ' (fk_salarie, solde_jours, source_reference, date_reference, mois_calcules,';
            $sqlInsert .= ' date_creation, fk_user_creat, entity) VALUES (';
            $sqlInsert .= (int) $salarieConge->fk_salarie.', '.$soldeInitial;
            $sqlInsert .= ', "'.$db->escape($sourceReference).'"';
            $sqlInsert .= ', "'.$db->escape($dateDebut->format('Y-m-d')).'"';
            $sqlInsert .= ', '.$moisComplets.', NOW(), '.(int) $fkAdmin.', '.(int) $entity.')';

            if ($db->query($sqlInsert)) {
                $resultat['crees']++;

            } else {
                $resultat['erreurs']++;
            }
        } catch (Exception $e) {
            $resultat['ignores']++;
        }
    }

    /* Une seule trace récapitulative pour l'initialisation globale. */
    if ($resultat['crees'] > 0) {
        $sqlAdmin = 'SELECT firstname, lastname FROM '.MAIN_DB_PREFIX.'user WHERE rowid='.(int) $fkAdmin;
        $resAdmin = $db->query($sqlAdmin);
        $admin = $resAdmin ? $db->fetch_object($resAdmin) : null;
        $nomAdmin = $admin ? $admin->lastname : '';
        $prenomAdmin = $admin ? $admin->firstname : '';

        $actionEffectue = 'Initialisation automatique des soldes de congé de '.$resultat['crees'].' salarié(s)';
        $actionEffectue .= ' à raison de 2,5 jours par mois complet';

        $sqlLog = 'INSERT INTO '.MAIN_DB_PREFIX.'log';
        $sqlLog .= ' (fk_user, nom, prenom, quand, action_effectue, object_concerne) VALUES (';
        $sqlLog .= (int) $fkAdmin.', "'.$db->escape($nomAdmin).'", "'.$db->escape($prenomAdmin).'", NOW(), ';
        $sqlLog .= '"'.$db->escape($actionEffectue).'", "Initialisation soldes congé")';
        $db->query($sqlLog);
    }

    return $resultat;
}

/**
 * Met les soldes à jour à partir du 25 de chaque mois en utilisant
 * la colonne mois_calcules déjà présente dans salarie_conge_solde.
 * Les mois éventuellement manqués sont rattrapés automatiquement.
 *
 * @param DoliDB $db Connexion Dolibarr
 * @param int $entity Entité Dolibarr
 * @param int $fkAdmin Utilisateur ayant déclenché le traitement
 * @param string $arrayIdSoc Sociétés accessibles à l'utilisateur
 * @return array{credites:int, deja_faits:int, erreurs:int, periode:string}
 */
function mettre_a_jour_soldes_conge_le_25($db, $entity, $fkAdmin, $arrayIdSoc)
{
    $dateTraitement = new DateTime(date('Y-m-d'));
    $datePeriode = new DateTime(date('Y-m-25'));
    if ((int) $dateTraitement->format('d') < 25) {
        $datePeriode->modify('-1 month');
    }

    $resultat = array(
        'credites' => 0,
        'deja_faits' => 0,
        'erreurs' => 0,
        'periode' => $datePeriode->format('Y-m')
    );

    $sql = 'SELECT cs.rowid AS fk_solde, cs.fk_salarie, cs.date_reference, cs.mois_calcules,';
    $sql .= ' u.firstname, u.lastname, soc.nom AS nom_societe';
    $sql .= ' FROM '.MAIN_DB_PREFIX.'user AS u';
    $sql .= ' LEFT JOIN '.MAIN_DB_PREFIX.'user_extrafields AS ue ON u.rowid=ue.fk_object';
    $sql .= ' LEFT JOIN '.MAIN_DB_PREFIX.'societe AS soc ON soc.rowid=ue.egp';
    $sql .= ' LEFT JOIN '.MAIN_DB_PREFIX.'societe_extrafields AS sce ON soc.rowid=sce.fk_object';
    $sql .= ' INNER JOIN '.MAIN_DB_PREFIX.'salarie AS s ON s.fk_user=u.rowid';
    $sql .= ' INNER JOIN '.MAIN_DB_PREFIX.'salarie_conge_solde AS cs';
    $sql .= ' ON cs.fk_salarie=s.rowid AND cs.entity='.(int) $entity;
    $sql .= ' WHERE sce.grp=1';
    if ($fkAdmin != 1) {
        $sql .= ' AND soc.rowid IN '.$arrayIdSoc;
    }
    $sql .= ' ORDER BY u.lastname ASC, u.firstname ASC';

    $resql = $db->query($sql);
    if (!$resql) {
        $resultat['erreurs']++;
        return $resultat;
    }

    $sqlAdmin = 'SELECT firstname, lastname FROM '.MAIN_DB_PREFIX.'user WHERE rowid='.(int) $fkAdmin;
    $resAdmin = $db->query($sqlAdmin);
    $admin = $resAdmin ? $db->fetch_object($resAdmin) : null;
    $nomAdmin = $admin ? $admin->lastname : '';
    $prenomAdmin = $admin ? $admin->firstname : '';

    while ($obj = $db->fetch_object($resql)) {
        if (empty($obj->date_reference)) {
            $resultat['erreurs']++;
            continue;
        }

        try {
            $dateReference = new DateTime(substr($obj->date_reference, 0, 10));
        } catch (Exception $e) {
            $resultat['erreurs']++;
            continue;
        }

        if ($dateReference > $datePeriode) {
            $resultat['deja_faits']++;
            continue;
        }

        /*
         * Nombre de mois calendaires entre la date de référence et
         * la dernière période devenue exigible le 25.
         */
        $moisCibles = (((int) $datePeriode->format('Y') - (int) $dateReference->format('Y')) * 12)
            + ((int) $datePeriode->format('m') - (int) $dateReference->format('m'));
        $moisDejaCalcules = (int) $obj->mois_calcules;
        $moisAAjouter = $moisCibles - $moisDejaCalcules;

        if ($moisAAjouter <= 0) {
            $resultat['deja_faits']++;
            continue;
        }

        $joursAAjouter = round($moisAAjouter * 2.5, 2);
        $db->begin();

        $sqlUpdate = 'UPDATE '.MAIN_DB_PREFIX.'salarie_conge_solde SET';
        $sqlUpdate .= ' solde_jours=solde_jours+'.$joursAAjouter;
        $sqlUpdate .= ', mois_calcules='.$moisCibles;
        $sqlUpdate .= ', fk_user_modif='.(int) $fkAdmin.', tms=NOW()';
        $sqlUpdate .= ' WHERE rowid='.(int) $obj->fk_solde;
        $sqlUpdate .= ' AND mois_calcules='.(int) $moisDejaCalcules;
        $resUpdate = $db->query($sqlUpdate);

        $ligneMiseAJour = 0;
        if ($resUpdate) {
            $resRowCount = $db->query('SELECT ROW_COUNT() AS nb');
            $objRowCount = $resRowCount ? $db->fetch_object($resRowCount) : null;
            $ligneMiseAJour = $objRowCount ? (int) $objRowCount->nb : 0;
        }

        if (!$resUpdate || $ligneMiseAJour !== 1) {
            $db->rollback();
            $resultat['deja_faits']++;
            continue;
        }

        $resLog = false;
        if ($ligneMiseAJour === 1) {
            $nomCompletSalarie = trim($obj->lastname.' '.$obj->firstname);
            $nomSociete = !empty($obj->nom_societe) ? $obj->nom_societe : 'Société non disponible';
            $actionEffectue = 'Mise à jour mensuelle de '.$joursAAjouter.' jour(s) du salarié #'.(int) $obj->fk_salarie;
            $actionEffectue .= ' - '.$nomCompletSalarie.' - société : '.$nomSociete;
            $actionEffectue .= ' - période : '.$resultat['periode'];
            $actionEffectue .= ' - '.$moisAAjouter.' mois ajouté(s)';

            $sqlLog = 'INSERT INTO '.MAIN_DB_PREFIX.'log';
            $sqlLog .= ' (fk_user, nom, prenom, quand, action_effectue, object_concerne) VALUES (';
            $sqlLog .= (int) $fkAdmin.', "'.$db->escape($nomAdmin).'", "'.$db->escape($prenomAdmin).'", NOW(), ';
            $sqlLog .= '"'.$db->escape($actionEffectue).'", "Mise à jour mensuelle congé")';
            $resLog = $db->query($sqlLog);
        }

        if ($resUpdate && $resLog) {
            $db->commit();
            $resultat['credites']++;
        } else {
            $db->rollback();
            $resultat['erreurs']++;
        }
    }

    return $resultat;
}

$db->close();
