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
print load_fiche_titre($langs->trans("Avance"), '', '');
$id_societe = GETPOST("id_societe","int");
$fk_user = GETPOST("id","int");
$fk_salarie = GETPOST("fk_salarie", "int");
$id_convention = GETPOST("id_convention","int");
$id_avance = GETPOST("id_avance","int");


// Recuperation des information après le clique sur l'onglet Salaire au niveau du module user

$head = salaire_Head($fk_salarie, $fk_user, $id_societe, $id_convention);
print dol_get_fiche_head($head, 'avance', "", -1, '');
$action = GETPOST("action", "alpha")?: "information";


$salaire_base = 0;
$message = "";
$annee = date("Y");
$mois = (int)date("m");

if(empty($fk_salarie) || empty($id_avance)){
	print "<mark><strong>Il n'est pas enregistré</strong></mark><br>";
    print "Page non Disponible";
}else{

	$obj_soc = prepare_objet_entete($fk_salarie, $fk_user, $db, $id_societe, $id_convention);
	entete_societe($obj_soc, 'societe');
	//Modification d'un contrat
	$mois_tab = array(" Janvier "," Février "," Mars "," Avril "," Mai "," Juin "," Juillet "," Août "," Septembre "," Octobre "," novembre "," Décembre ");

	$head_avance = salarie_avance_Head($fk_salarie, $fk_user, $id_societe, $id_convention, $id_avance);
	print dol_get_fiche_head($head_avance, 'information', "", -1, '');
		$id_avance = GETPOST("id_avance", "int");

		$sql_avance = "SELECT * FROM ".MAIN_DB_PREFIX."salarie_avance WHERE rowid=".$id_avance;
		$res_avance = $db->query($sql_avance);
		$obj_avance = $db->fetch_object($res_avance);
		print "<h2> Avance : <b>".$obj_avance->libelle."<b></h2>";
		print "<table class='tagtable liste'>";
		print "<tr class='fieldrequired'><td style='width: 200px; padding-top: 15px;'>Montant</td><td style='width: 200px; padding-top: 15px;'>".apres_virgule($db, $id_societe, $obj_avance->montant_total)."</td></tr>";
		print "<tr class='fieldrequired'><td style='width: 200px; padding-top: 15px;'>Payé</td><td style='width: 200px; padding-top: 15px;'>".apres_virgule($db, $id_societe, $obj_avance->montant_paye)."</td>";
		print "<tr class='fieldrequired'><td style='width: 200px; padding-top: 15px;'>A payer dans</td><td style='width: 200px; padding-top: 15px;'>".$obj_avance->nombre_mois." mois</td>";
		print "<tr class='fieldrequired'><td style='width: 200px; padding-top: 15px;'>Montant/Mois</td><td style='width: 200px; padding-top: 15px;'>".$obj_avance->montant_par_mois."</td>";
		print "<tr class='fieldrequired'><td style='width: 200px; padding-top: 15px;'>Début paiement</td><td style='width: 200px; padding-top: 15px;'>".$mois_tab[$obj_avance->mois_debut_paiement-1]." ".$obj_avance->annee_debut_paiement."</td>";
		print "<tr class='fieldrequired'><td style='width: 200px; padding-top: 15px;'>Date affectation</td><td style='width: 200px; padding-top: 15px;'>".$obj_avance->date_affectation."</td>";
		print "<tr class='fieldrequired'><td style='width: 200px; padding-top: 15px;'>Note</td><td style='width: 200px; padding-top: 15px;'>".$obj_avance->note."</td>";
		print '</table>';



}
$db->free();
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