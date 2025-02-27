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

require '../main.inc.php'; //require_once '../lib/paiementsalaire.lib.php';

//require_once DOL_DOCUMENT_ROOT.'/core/class/html.formpaiementsalaire.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/paiementsalaire/lib/paiementsalaire.lib.php';

//$PaiementSalaire = new modPaiementSalaire($db);

llxHeader("", "Paiement | Salaire");
//Titre 
print load_fiche_titre($langs->trans("Edition des Salaires"), '', '');
print '<hr>';
//table des champs et labels
$action = GETPOST('action','aZ09');
$message = "";
if(empty($action))	
	$action = 'create';

$id_salarie = GETPOST("id","09");

if($action == "save_edit"){
	$existSql = "SELECT * FROM ".MAIN_DB_PREFIX."salarie WHERE fk_user=".$id_salarie;
	$existResult = $db->query($existSql);
	$existSalarie = $db->fetch_object($existResult);

	if(empty($existSalarie->matricule)){
		if(empty(GETPOST('statut_f', 'aZ'))) {
			$message = 'Le champ "STUATION FAMILIALE" est Obligatoire<br>';
		}
		if(empty(GETPOST('categories', 'aZ09'))) {
			$message .= 'Le champ "CATEGORIE" est Obligatoire<br>';
		}
		if(empty($message)){
			$categ = GETPOST("categories");
			$situation_f = GETPOST("statut_f");

			$nb_enfant = !empty(GETPOST("nb_enfant","09")) ? GETPOST("nb_enfant","09") : 0;
			$nb_conj = !empty(GETPOST("nb_conj","09")) ? GETPOST("nb_conj","09") : 0;
			$surSal = !empty(GETPOST("sursalaire","09")) ? GETPOST("sursalaire","09") : 0;
			$hsup25 = !empty(GETPOST("hsup25","09")) ? GETPOST("hsup25","09") : 0;
			$hSup50 = !empty(GETPOST("hsup50","09")) ? GETPOST("hsup50","09") : 0;

			$trouve = false;
			while($trouve == false){
				$mat = get_matricule_enfant(20);
				$existSql = "SELECT * FROM ".MAIN_DB_PREFIX."salarie WHERE matricule=".$mat;
				$existResult = $db->query($existSql);
				if(empty($existResult))
					$trouve = true;
			}
			//Insertion dans la table salarie
			
			$sql="INSERT INTO ".MAIN_DB_PREFIX."salarie (matricule, situation_familiale, nombre_enfant, nombre_conjoint, fk_user, fk_categorie , sursalaire)";
			$sql .= " VALUES('".$mat."','".$situation_f."',".$nb_enfant.",".$nb_conj.",'".$id_salarie."','".$categ."','".$sursal."')";
			$result = $db->query($sql);

			//insertion dans la table Salarié_primes
			$primes_arr = !empty(GETPOST('primes')) ? GETPOST('primes') : array();
					for($i =0; $i<count($primes_arr); $i++){
						$fk_prime = $primes_arr[$i];
						$sql = "INSERT INTO ".MAIN_DB_PREFIX."salarie_prime (matricule, fk_prime) VALUES ('".$mat."','".$fk_prime."')";
						$result2 = $db->query($sql);
					}
			//insertion dans la table Salarié_indemnités
			$indemnite_arr = !empty(GETPOST('indemnites')) ? GETPOST('indemnites') : array();
			for($i =0; $i<count($indemnite_arr); $i++){
				$fk_indemnite = $indemnite_arr[$i];
				$sql = "INSERT INTO ".MAIN_DB_PREFIX."salarie_indemnite (matricule, fk_indemnite) VALUES ('".$mat."','".$fk_indemnite."')";
				$result3 = $db->query($sql);
			}

			//insertion dans la table Salarié_HeureSup 
			if($hsup25 == 0 || $hsup50 == 0)
				$sql="INSERT INTO".MAIN_DB_PREFIX."salarie_heure_sup (matricule, heure_sup_25, heure_sup_50, mois) VALUES('".$mat."','".$hsup25."','".$hSup50."','now()')";
		
			$message = "SALARIE enregistrée avec succès";
			$action = 'detail';
		}
	}else{
			$categ = GETPOST("categories");
			$situation_f = GETPOST("statut_f");
			$mat = GETPOST("mat");


			$nb_enfant = GETPOST("nb_enfant","09");
			$nb_conj = GETPOST("nb_conj","09");
			$surSal = GETPOST("sursalaire","09");
			$hsup25 = GETPOST("hsup25","09");
			$hSup50 = GETPOST("hsup50","09");
			$virgule = 0;
			$sql = "UPDATE ".MAIN_DB_PREFIX."salarie SET";
			if(($salarie->matricule != $mat) && !empty($mat)){
				$sql .= " matricule='".$mat."'";
				$virgule ++;

				$sql_p= "UPDATE ".MAIN_DB_PREFIX."salarie_prime";
				$sql_i= "UPDATE ".MAIN_DB_PREFIX."salarie_indemnite";
				$sql_hsup= "UPDATE ".MAIN_DB_PREFIX."salarie_heure_sup";
			}
			if(!empty($situation_f)){
				if($virgule != 0)
					 $sql .= ", situation_familiale='".$situation_f."'";
				else $sql .= " situation_familiale='".$situation_f."'";
				$virgule ++;
			}
			if(!empty($nb_enfant)){
				if($virgule != 0)
					$sql .= ", nombre_enfant=".$nb_enfant."";
				else $sql .= " nombre_enfant=".$nb_enfant."";
				$virgule ++;

			}
			if(!empty($nb_conj)){
				if($virgule != 0)
					$sql .= ", nombre_conjoint=".$nb_conj."";
				else $sql .= " nombre_conjoint='".$nb_conj."'";
				$virgule ++;

			}

			if(!empty($categ)){
				if($virgule != 0)
					$sql .= ", fk_categorie='".$categ."'";
				else $sql .= " fk_categorie='".$categ."'";
				$virgule ++;
			}
			if(!empty($surSal)){
				if($virgule != 0)
					$sql .= ", sursalaire='".$surSal."'";
				else $sql .= " sursalaire='".$surSal."'";
				$virgule ++;

			}
			$sql .= " WHERE matricule='".$existSalarie->matricule."';";
			$result = $db->query($sql);
			if($result){
			 $message = "SALARIE modifié avec succès";
			 $action = 'detail';
			}else {
				$message = "non";
			 $action = 'detail';
			}
		}
}

if($action == 'detail'){
	//recupétration des information sur l'utilisateur dans la table user Dolibarr
    $userSQL = "SELECT * FROM ".MAIN_DB_PREFIX."user where rowid=".$id_salarie;
	$result = $db->query($userSQL);
	$userD = $db->fetch_object($result);

	//Recupération des information dans la table salarié
	$salSql = "SELECT * FROM ".MAIN_DB_PREFIX."salarie where fk_user=".$id_salarie;
	$result = $db->query($salSql);
	if(!empty($result)){
		$salarie = $db->fetch_object($result);

		$head = salaire_Head($salarie->matricule,$id_salarie,"detail");
		print dol_get_fiche_head($head, 'information', "", -1, '');

	//recupération de la catégorie de l'utilisateur dans la table Salarie
		$CatSQL = "SELECT * FROM ".MAIN_DB_PREFIX."dcategories where rowid=".$salarie->fk_categorie;
		$catResult = $db->query($CatSQL);
		if($catResult){
			$catSalarie = $db->fetch_object($catResult);
			$categ = $catSalarie->code_categorie;
		}else $categ = "Non associé";
	}else $categ = "Non associé";
	
	print '<div>';
	print '<table>';
	print '<tr >';
	print '<td class="pair" style="padding: 10px; width: 200px;">Matricule</td>';
	print '<td class="pair" style="padding: 10px; width: 200px;">'.$salarie->matricule.'</td>';
	print '<td style = "width: 20px"></td><td class="impair" style="padding: 10px; width: 200px;">Catégorie</td>';
	print '<td class="impair" style="padding: 10px; width: 200px;">';	
	print $categ;
	print '</td>';
	print '</tr>';
	print '<tr>';
	print '<td class="pair" style="padding: 10px; width: 200px;">Nom</td>';
	print '<td class="pair" style="padding: 10px; width: 200px;">'.$userD->lastname.'</td>';
	print '<td></td><td class="impair" style="padding: 10px; width: 200px;">Genre</td>';
	$genre = "";
	if($userD->gender == "man")
	   $genre = "homme";
	else if($userD->gender == "man")
		$genre = "femme";
	else if($userD->gender == "other")
		$genre = "autre";
	else $genre = "non précisé";
	print '<td class="impair" style="padding: 10px; width: 200px;">'.$genre.'</td>';
	print '</tr>';

	print '<tr>';
	print '<td class="pair" style="padding: 10px; width: 200px;">Prenom</td>';
	print '<td class="pair" style="padding: 10px; width: 200px;">'.$userD->firstname.'</td>';
	print '<td style = "width: 20px"></td><td class="impair" style="padding: 10px; width: 200px;">Situation familiale</td>';
	if(empty($salarie->situation_familiale))
	   $situation_fam = "non précisé";
	else $situation_fam = $salarie->situation_familiale;
	print '<td class="impair" style="padding: 10px; width: 200px;">'.$situation_fam.'</td>';
	print '</tr>';

	print '<tr>';
	print '<td class="pair" style="padding: 10px; width: 200px;">Adresse</td>';
	print '<td class="pair" style="padding: 10px; width: 200px;">'.$userD->address.'</td>';
	print '<td style = "width: 20px"></td><td class="impair" style="padding: 10px; width: 200px;">Nombre Conjoint</td>';
	if(empty($salarie->nombre_conjoint))
	   $nombre_conjoint = "non précisé";
	else $nombre_conjoint = $salarie->nombre_conjoint;
	print '<td class="impair" style="padding: 10px; width: 200px;">'.$nombre_conjoint.'</td>';
	print '</tr>';

	print '<tr>';
	print '<td class="pair" style="padding: 10px; width: 200px;">Tel/Fax</td>';
	print '<td class="pair" style="padding: 10px; width: 200px;">'.$userD->office_phone.'<br>'.$userD->office_fax.'<br>'.$userD->user_mobile.'</td>';
	print '<td style = "width: 20px"></td><td class="impair" style="padding: 10px; width: 200px;">Nombre enfant</td>';
	if(empty($salarie->nombre_enfant))
	   $nombre_enfant = "non précisé";
	else $nombre_enfant = $salarie->nombre_enfant;
	print '<td class="impair" style="padding: 10px; width: 200px;">'.$nombre_enfant.'</td>';
	print '</tr>';

	print '<tr>';
	print '<td class="pair" style="padding: 10px; width: 200px;">Email</td>';
	print '<td class="pair" style="padding: 10px; width: 200px;">'.$userD->email.'<br>'.$userD->personal_email.'</td>';
	print '<td style = "width: 20px"></td><td class="impair" style="padding: 10px; width: 200px;">Poste/Fonction</td>';
	print '<td class="impair" style="padding: 10px; width: 200px;">'.$userD->job.'</td>';
	print '</tr>';

	print '<tr>';
	print '<td class="pair" style="padding: 10px; width: 200px;">Adresse</td>';
	print '<td class="pair" style="padding: 10px; width: 200px;">'.$userD->town.'/'.$userD->address.'</td>';
	print '<td style = "width: 20px"></td><td class="impair" style="padding: 10px; width: 200px;">Date Embauche</td>';
	print '<td class="impair" style="padding: 10px; width: 200px;">'.$userD->dateemployment.'</td>';
	print '</tr>';
    print '<tr><td colspan="5" style="padding: 10px; width: 200px;"></td></tr><tr><td align="center" colspan="5"><a href="'.$_SERVER["PHP_SELF"].'?id='.$userD->rowid.'&action=edit"><input class="button" type="button" value="Modifier"/></a><td>';

print '</table>';

print '</div>';
}


if($action == 'edit'){
    /*$salSql = "SELECT * FROM ".MAIN_DB_PREFIX."user where rowid=".$id_salarie;
	$result = $db->query($salSql);
	$obj = $db->fetch_object($result);*/

	$userSQL = "SELECT * FROM ".MAIN_DB_PREFIX."user where rowid=".$id_salarie;
	$result = $db->query($userSQL);
	$userD = $db->fetch_object($result);

	//Recupération des information dans la table salarié
	$salSql = "SELECT * FROM ".MAIN_DB_PREFIX."salarie where fk_user=".$id_salarie;
	$result = $db->query($salSql);
	$salarie = $db->fetch_object($result);

	print '<div><form name="add" method="POST" action="'.$_SERVER['PHP_SELF'].'?id='.$id_salarie.'">';
	print '<input type="hidden" name="token" value="'.newToken().'">';
	print '<input type="hidden" name="action" value="save_edit">';
	
	print '<table>';
	print '<tr>';
	print '<td class="pair" style="padding: 10px; width: 200px;">'.$userD->firstname.'</td>';
	print '<td class="pair" style="padding: 10px; width: 200px;">'.$userD->lastname.'</td>';
	print '<td style = "width: 20px"></td><td class="impair" style="padding: 10px; width: 200px;">Catégorie</td>';
	print '<td class="impair" style="padding: 10px; width: 200px;"><select name="categories">';

	$catSql = "SELECT * FROM ".MAIN_DB_PREFIX."dcategories";

	$result = $db->query($catSql);
	$aff = true;
	if($result){
		$i = 0;
		$num = $db->num_rows($result);
		while ($i < $num){
			$obj1 = $db->fetch_object($result);
			if($salarie->fk_categorie == $obj1->rowid)
				print '<option value="'.$obj1->rowid.'" selected>'.$obj1->code_categorie.'</option>';
			else
				print '<option value="'.$obj1->rowid.'">'.$obj1->code_categorie.'</option>';
			$i ++;
		}
	}

	print '</select></td>';
	print '</tr>';
	print '<tr >';
	print '<td class="pair" style="padding: 10px; width: 200px;">Identifiant</td>';
	print '<td class="pair" style="padding: 10px; width: 200px;"><input type="text" name="mat" value="'.$salarie->matricule.'"/></td>';
	print '<td style = "width: 20px"></td><td class="impair" style="padding: 10px; width: 200px;">Nombre de Conjoint</td>';
	print '<td class="impair" style="padding: 10px; width: 200px;"><input name="nb_conj" type="number" max="4" min="0"size="5" placeholder="'.$salarie->nombre_conjoint.'"></td>';
	
	print '</tr>';
	print '<tr >';
	print '<td class="pair" style="padding: 10px; width: 200px;">Genre</td>';
	$genre = "";
	if($userD->gender == "man")
	   $genre = "homme";
	else if($userD->gender == "man")
		$genre = "femme";
	else
	$genre = "autre";
	print '<td class="pair" style="padding: 10px; width: 200px;">'.$genre.'</td>';
	print '<td style = "width: 20px"></td><td class="impair" style="padding: 10px; width: 200px;">Primes individuelles</td>';
	print '<td class="impair" style="padding: 10px; width: 200px;">';

	$allprime = array();
	$allprimeRowid = array();

	$covSql = "SELECT * FROM ".MAIN_DB_PREFIX."primes";
	$result = $db->query($covSql);//= $db->query($covSql);

	if($result){
		$i = 0;
		$num = $db->num_rows($result);
		while ($i < $num){
			$obj = $db->fetch_object($result);
			if ($obj)
			{
				$allprimeRowid[$i] = $obj->rowid;
				$allprime[$i] =  $obj->nom_prime;
				/*print $allprimeRowid[$i].",";
				print $allprime[$i].",";*/
				
				

			}
			$i ++;
		}
	}
	$allprime = array_combine($allprimeRowid, $allprime);
	$monform = new Form($db);
	print $monform->multiselectarray('primes', $allprime, GETPOST('primes', 'array'), null, 0, 'quatrevingtpercent widthcentpercentminusx', 0, 0);

print '</td>';
	print '</tr>';

	print '<table>';
	print '<tr>';
	print '<td class="pair" style="padding: 10px; width: 200px;">Situation Familiale</td>';
	$marie = "";
	$divorce = "";
	$celibat = "";
	if($salarie->situation_familiale == "marié")
	 	$marie = "selected";
	if($salarie->situation_familiale == "divorce")
	 	$divorce = "selected";
	if($salarie->situation_familiale == "celibataire")
	 	$celibat = "selected";
	print '<td class="pair" style="padding: 10px; width: 200px;"><select name="statut_f">
	
		<option value="marie"'.$marie.'>Marié</option>
		<option value="divorce" '.$divorce.'>Divorcé</option>
		<option value="celibataire" '.$celibat.'>Célibataire</option>
	</select></td>';
	print '<td style = "width: 20px"></td><td class="impair" style="padding: 10px; width: 200px;">Indemnités individuelles</td>';
	print '<td class="impair" style="padding: 10px; width: 200px;">';

	$allindemnite = array();
	$allindemniteRowid = array();

	$covSql = "SELECT * FROM ".MAIN_DB_PREFIX."indemnites";
	$result = $db->query($covSql);//= $db->query($covSql);

	if($result){
		$i = 0;
		$num = $db->num_rows($result);
		while ($i < $num){
			$obj = $db->fetch_object($result);
			if ($obj)
			{
				$allindemniteRowid[$i] = $obj->rowid;
				$allindemnite[$i] =  $obj->nom_indemnite;
				/*print $allindemniteRowid[$i].",";
				print $allindemnite[$i].",";*/
				
				

			}
			$i ++;
		}
	}
	$allindemnite = array_combine($allindemniteRowid, $allindemnite);
	$monform = new Form($db);
	print $monform->multiselectarray('indemnites', $allindemnite, GETPOST('indemnites', 'array'), null, 0, 'quatrevingtpercent widthcentpercentminusx', 0, 0);

	print '</td>';
	print '</tr>';
	
	print '<tr >';
	print '<td class="pair" style="padding: 10px; width: 200px;">Nombre enfant</td>';
	print '<td class="pair" style="padding: 10px; width: 200px;"><input name="nb_enfant" type="number" min="0" max="5" size="5" placeholder="'.$salarie->nombre_enfant.'"></td>';
	print '<td style = "width: 20px"></td><td class="impair" style="padding: 10px; width: 200px;">Sursalaire</td>';
	print '<td class="impair" style="padding: 10px; width: 200px;"><input name="sursalaire" type="text" placeholder="'.$salarie->sursalaire.'"></td>';
	print '</tr>';
	
	print '<tr >';
	print '<td class="pair" style="padding: 10px; width: 200px;">Nb HeureSup25%</td>';
	print '<td class="pair" style="padding: 10px; width: 200px;"><input name="hsup25" type="number" min="0" size="5" placeholder="0"></td>';
	print '<td style = "width: 20px"></td><td class="impair" style="padding: 10px; width: 200px;">Nb HeureSup50%</td>';
	print '<td class="impair" style="padding: 10px; width: 200px;"><input name="hsup50" type="text" placeholder="Montant"></td>';
	print '</tr>';

	print '<tr><td colspan="5" style="padding: 10px; width: 200px;"></td></tr><tr><td align="center" colspan="5">'.$form->buttonsSaveCancel("Save").'</a><td>';

	print '</table><br><hr>';

	print '<table>'; 


	print '</table></form>';

	print '</div>';
	}


	$db->free();
if($message != ''){		
		print "<script>
		$.jnotify('".$message."', {delay : 5000, fadeSpeed: 500});
		</script>";
}

/*
print "<script type='text/javascript'>  

$(document).ready(function() {
	alert('salarie');
	$('#salarie').change(function() {
		//$(\'input[name='action']\').val(\'create\');
		$('#add').submit();

		
	});
});
</script>";
if(!empty($message)){		
		print "<script>
		$.jnotify('".$message."', {delay : 5000, fadeSpeed: 500});
		</script>";
}
//header('Location: ./compta/facture/card.php');*/