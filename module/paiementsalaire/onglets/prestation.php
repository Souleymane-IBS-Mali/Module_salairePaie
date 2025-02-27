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


// L'espace compta/treso doit toujours etre actif car c'est un espace partage
// par de nombreux modules (banque, facture, commande a facturer, etc...) independamment
// de l'utilisation de la compta ou non. C'est au sein de cet espace que chaque sous fonction
// est protegee par le droit qui va bien du module concerne.
//if (!$user->rights->compta->general->lire)
//  accessforbidden();

llxHeader("", "Paiement | Salaire");
//Titre 
print load_fiche_titre($langs->trans("Les Prestations Sociales"), '', '');
$fk_user = GETPOST("id","int");
$id_societe = GETPOST("id_societe","int");
$fk_salarie = GETPOST("fk_salarie", "int");
$id_convention = GETPOST("id_convention","int");
$head = salaire_Head($fk_salarie, $fk_user, $id_societe, $id_convention);
	print dol_get_fiche_head($head, 'prestation', "", -1, '');
if($user->id !=1 && $user->id != $fk_user && !$user->rights->paiementsalaire->salarie->read){
	print "<h2> Vous n\'avez pas ce droit </h2>";
}else{
	

	$message ="";
	$action = GETPOST("action","alpha");

	//Recuperation de l'action   
	if($action == "associer"){
		$fk_prestation_sociale = GETPOST("fk_prestation_sociale", "int");
		$sql = "INSERT INTO ".MAIN_DB_PREFIX."salarie_prestation_sociale (fk_salarie, fk_prestation_sociale) VALUES ('".$fk_salarie."',".$fk_prestation_sociale.")";
		$result = $db->query($sql);
		if($result)
			$message = "Prestation sociale associée à ce salarié";
	}

	if($action == "dissocier"){
		$fk_prestation_sociale = GETPOST("fk_prestation_sociale", "int");
		$sql = "DELETE FROM ".MAIN_DB_PREFIX."salarie_prestation_sociale WHERE fk_salarie='".$fk_salarie."' AND fk_prestation_sociale=".$fk_prestation_sociale;
			$result = $db->query($sql);
			$message = "Prestation sociale dissociée de ce salarié";
	}

	if(empty($fk_salarie)){
		print "Page non Disponible";
	}else{
		$obj_soc = prepare_objet_entete($fk_salarie, $fk_user, $db, $id_societe, $id_convention);
		entete_societe($obj_soc, 'societe');
		print '<hr>';

		//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------
		//les prestations sociales associer au salarié

			print "<div>";
			print "<h3>Les Prestations sociales Associées</h3>";
			print "<table class='tagtable liste'>";
			print "<tr class='liste_titre'><td align='center' rowspan='2'>Code</td><td align='center' colspan='2'>Charge";
			print "</td><td align='center' rowspan='2'>Opération</td></tr>";
			print "<tr class='pair'><td align='center'>salariale</td><td align='center'>patronale";
			print "</td>";
			// prestation sociale obligatoire
			$sql = "SELECT * FROM ".MAIN_DB_PREFIX."type_prestation WHERE nature='obligatoire'";
			$result = $db->query($sql);
			if($result){
				$i = 0;
				$num1 = $db->num_rows($result);
				while ($i < $num1){
					$obj = $db->fetch_object($result);
					if ($obj)
					{
							print "<tr class='impair'><td align='center'>";
							$taux_prest_sql = "SELECT * FROM ".MAIN_DB_PREFIX."bareme_prestation WHERE fk_prestation=".$obj->rowid;
							$result_taux_prest = $db->query($taux_prest_sql);
							if($result_taux_prest){
								$num = $db->num_rows($result_taux_prest);
								$i = 0;
								$trouve = false;
								while($i < $num && $trouve == false){
									$taux_prest = $db->fetch_object($result_taux_prest);
										$prest_conv_sql = "SELECT fk_convention FROM ".MAIN_DB_PREFIX."bareme_prestation_convention WHERE fk_convention=".$id_convention." AND fk_condition=".$taux_prest->rowid;
										$prest_conv_res = $db->query($prest_conv_sql);
										$prest_conv_obj = $db->fetch_object($prest_conv_res);
										if($db->num_rows($prest_conv_res) > 0){
											$trouve = true;
											print ''.$obj->code.'</td><td align="center">'.$taux_prest->taux_salariale.'<td align="center">'.$taux_prest->taux_patronale;
											print "</td><td align='center'>Affectée</td></tr>";
										}
									$i ++;
								}
								if($trouve == false){
									print ''.$obj->code.'</td><td align="center">'.$taux_prest->taux_salariale.'<td align="center">'.$taux_prest->taux_patronale;
									print "</td><td align='center'>Affectée</td></tr>";
								}
							}
					
					}
					$i ++;
				}
			}



			$salSql = "SELECT * FROM ".MAIN_DB_PREFIX."salarie where fk_salarie='".$fk_salarie."'";
			$result = $db->query($salSql);
			if(!empty($result))
				$salarie = $db->fetch_object($result);
			$sql = "SELECT * FROM ".MAIN_DB_PREFIX."salarie_prestation_sociale WHERE fk_salarie='".$fk_salarie."'";
			$result = $db->query($sql);
			if($result){
				$i = 0;
				$num = $db->num_rows($result);
				while ($i < $num){
					$obj = $db->fetch_object($result);
					if ($obj)
					{
						$sql1 = "SELECT * FROM ".MAIN_DB_PREFIX."type_prestation WHERE rowid=".$obj->fk_prestation_sociale;
						$result1 = $db->query($sql1);
						$obj1 = $db->fetch_object($result1);
						if($result1 && $obj1){
							print "<tr class='impair'><td align='center'>";
							$taux_prest_sql = "SELECT * FROM ".MAIN_DB_PREFIX."bareme_prestation WHERE fk_prestation=".$obj->fk_prestation_sociale;
							$result_taux_prest = $db->query($taux_prest_sql);
							$taux_prest = $db->fetch_object($result_taux_prest);
							print ''.$obj1->code.'</td><td align="center">'.$taux_prest->taux_salariale.'<td align="center">'.$taux_prest->taux_patronale;
							print "</td><td align='center'><a class='reposition editfielda' href='".$_SERVER['PHP_SELF']."?mainmenu=paiementsalaire&leftmenu=salarie&id_convention=".$id_convention."&id_societe=".$id_societe."&fk_salarie=".$fk_salarie."&id=".$fk_user."&action=dissocier&fk_prestation_sociale=".$obj->fk_prestation_sociale."'><button class='button'>Dissocier</button></a></td></tr>";
					
						}
					}
					$i ++;
				}
				if($num == 0 && $num1 == 0)
					print "<tr><td colspan='3' align='center'>Aucune Cotisation disponibles</td></tr>";
			} else print "<tr><td align='center' colspan='3' align='center'>Accune Cotisation sociales disponible</td></tr>";
			print "</table></div>";

				//Prestations sociales disponibles non associés
				print "<div >";
				print "<h3>Les Prestations sociales disponibles</h3>";
				print "<table class='tagtable liste' >";
				print "<tr class='liste_titre'><td align='center' rowspan='2'>Code</td><td align='center' colspan='2'>Charge";
				print "</td><td align='center' rowspan='2'>Opération</td></tr>";
				print "<tr class='pair'><td align='center'>salariale</td><td align='center'>patronale";
				print "</td></tr>";			
				
			$sql = "SELECT * FROM ".MAIN_DB_PREFIX."salarie_prestation_sociale WHERE fk_salarie=".$fk_salarie;
			$result = $db->query($sql);
			$array = array();
			if($result){
				$i = 0;
				$num1 = $db->num_rows($result);
				while ($i < $num1){
					$obj = $db->fetch_object($result);
					if($obj){
						$array[$i] = $obj->fk_prestation_sociale;
					}
					$i ++;
				}
			}
			$sql = "SELECT * FROM ".MAIN_DB_PREFIX."type_prestation WHERE rowid NOT IN (0";
			$a = 0;
				while ($a < count($array)) {
						$sql .= ", ".$array[$a]."";
					$a ++;
				}
				$sql .= ") AND nature='facultative'";
				$result = $db->query($sql);
			if($result){
				$i = 0;
				$num = $db->num_rows($result);
				while ($i < $num){
					$obj = $db->fetch_object($result);
					if($obj){
						print "<tr class='pair'><td align='center'>";
						$taux_prest_sql = "SELECT * FROM ".MAIN_DB_PREFIX."bareme_prestation WHERE fk_prestation=".$obj->rowid;
							$result_taux_prest = $db->query($taux_prest_sql);
							$taux_prest = $db->fetch_object($result_taux_prest);
						print $obj->code.'</td><td align="center">'.$taux_prest->taux_salariale.'<td align="center">'.$taux_prest->taux_patronale;
						if($user->rights->paiementsalaire->salarie->write)
							print "</td><td align='center'><a class='reposition editfielda' href='".$_SERVER['PHP_SELF']."?mainmenu=paiementsalaire&leftmenu=salarie&id_convention=".$id_convention."&id_societe=".$id_societe."&fk_salarie=".$fk_salarie."&id=".$fk_user."&action=associer&fk_prestation_sociale=".$obj->rowid."'><button class='button'>Associer</button></a></td></tr>";
						else print "</td><td align='center'><button class='butActionRefused' title='Permission manquante'>Associer</button></td></tr>";
					}
					$i ++;
				}
				if($num == 0)
					print "<tr><td colspan='4' align='center'>Aucune Cotisation disponibles</td></tr>";
			}else print "<tr><td colspan='4' align='center'>Aucune Cotisation disponibles</td></tr>";
				print "</table></div>";
		
	}

	$db->close();



	if($message != ""){		
		print "<script>
		$.jnotify('".$message."', {delay : 5000, fadeSpeed: 500});
		</script>";
	}


}