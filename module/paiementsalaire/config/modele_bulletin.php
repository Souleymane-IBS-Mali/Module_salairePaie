<?php
/* Copyright (C) 2004-2017 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2022 SuperAdmin
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file    paiementsalaire/admin/about.php
 * \ingroup paiementsalaire
 * \brief   About page of module PaiementSalaire.
 */

// Libraries
require_once "./../../main.inc.php";
require_once DOL_DOCUMENT_ROOT.'/custom/paiementsalaire/lib/paiementsalaire.lib.php';


llxHeader("", "Paiement | Salaire");
print '<h3>Modèles de bulletins disponibles</h3>';
print '<span style="float:right;"><a title="Reglages" href="./reglages.php?mainmenu=paiementsalaire&leftmenu=reglage">Retour</a></span><br>';

$action = GETPOST('action', 'alpha')?:'liste';
$id_societe = GETPOST('id_societe', 'int');
//print '<span style="float:right;"><a title="Reglages" href="./reglages.php?mainmenu=paiementsalaire&leftmenu=reglage">Retour</a></span><br>';
//les types de bulletins
$sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."modele_bulletin";
$res = $db->query($sql);
if($res){
  $nb = $db->num_rows($res);
  if($nb == 0){
	$sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'modele_bulletin (libelle, commentaire, actif) VALUES ("Base","Le bulletin de base de ce module (Recommandé).",1)';
	$result = $db->query($sql_insert);
  }
  if($nb < 2){
	$sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'modele_bulletin (libelle, commentaire, actif) VALUES ("Moyen","Bulletin ajusté.",0)';
	$result = $db->query($sql_insert);
  }
  if($nb < 3){
	$sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'modele_bulletin (libelle, commentaire, actif) VALUES ("Avancé","Modèle de bulletin avancé & commerciale",0)';
	$result = $db->query($sql_insert);
  }
}


$monform = new Form($db);
if($action == "activation"){
  $id_modele_bulletin = GETPOST("id_modele_bulletin", "int");

  //selection des informations du modèle à activer
  $modele_bulletin = "SELECT libelle FROM ".MAIN_DB_PREFIX."modele_bulletin WHERE rowid=".$id_modele_bulletin;
	$result_modele_bulletin = $db->query($modele_bulletin);//= $db->query($covSql);
	if($result_modele_bulletin)
			$obj_modele_bulletin = $db->fetch_object($result_modele_bulletin);

      $url = $_SERVER['PHP_SELF']."?mainmenu=paiementsalaire&leftmenu=reglage&id_modele_bulletin=".$id_modele_bulletin."&action=activer";
      $titre = 'Voulez-vous vraiment activer le modèle : '.$obj_modele_bulletin->libelle.' ?';
        $formconfirm = $monform->formconfirm(
        $url, 
        $titre, 
        "", 
        'activer', 
        $array, 
        '', 
        1,
        200,
        '30%'
        );
        print $formconfirm;
        $action = "liste";
}

if($action == 'activer'){
    $id_modele_bulletin = GETPOST("id_modele_bulletin", "int");
    $sql = 'UPDATE '.MAIN_DB_PREFIX.'modele_bulletin SET actif=0';
    $result = $db->query($sql);
    
    $sql = 'UPDATE '.MAIN_DB_PREFIX.'modele_bulletin SET actif=1 WHERE rowid='.$id_modele_bulletin;
    $result = $db->query($sql);

    if($result)
        $message = 'Modèle activé avec succès';
    else
        $message = "Un problème est survenu";
    
        $action = 'liste';

}

//Initialisation
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

	$sql = "SELECT societe_mere FROM ".MAIN_DB_PREFIX."salairepaie_societe WHERE fk_societe=".$id_societe;
	$res = $db->query($sql);
	$num = $db->num_rows($res);
	if($i <= 0){
		$sql = "SELECT sc.rowid as r1, sc.nom, sc.name_alias, sc.phone, sc.fax, sc.code_client, sc.zip, sce.rowid as r2, sce.fk_object, sce.conv FROM ".MAIN_DB_PREFIX."societe as sc";
		$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."societe_extrafields as sce ON sc.rowid=sce.fk_object WHERE sce.grp=1";
		if($user->id != 1)
			$sql .= " AND sc.rowid IN ".$array_id_soc;

		$sql .= " ORDER BY sc.rowid ASC";
		$result = $db->query($sql);

		if($result){
			$i = 0;
			$num = $db->num_rows($result);

			while ($i < $num){
				$societe = $db->fetch_object($result);
				$sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."salairepaie_societe (fk_societe, societe_mere, afficher_regularisation_its) VALUES(".$societe->r1.", 0, 1)";
				$res = $db->query($sql_insert);

				$i ++;
			}
		}
	}

if($action=="modifier_regularisation_its"){//Mettre "utilisé les informations de la société mère à oui ou à non
		//On garde la trace de l'action
	
		$rep = 0;
		$ch = "Non";
		$sql = "SELECT societe_mere, afficher_regularisation_its FROM ".MAIN_DB_PREFIX."salairepaie_societe WHERE fk_societe=".$id_societe;
		$res = $db->query($sql);
		if($res){
			$rep = $db->fetch_object($res)->afficher_regularisation_its;
	
			if(empty($db->fetch_object($res))){
				$sql_insert = "INSERT INTO ".MAIN_DB_PREFIX."salairepaie_societe (fk_societe, societe_mere, afficher_regularisation_its) VALUES(".$id_societe.", 1, 1)";
				$res = $db->query($sql_insert);

			}else{
				if($rep == 1){
					$rep = 0;
					$ch = "Non";
				}else{ 
					$rep = 1;
					$ch = "Oui";
				}
		
				$sql = "UPDATE ".MAIN_DB_PREFIX."salairepaie_societe SET afficher_regularisation_its=".$rep." WHERE fk_societe=".$id_societe;
				$res = $db->query($sql);
		
				$sql_select = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$user->id;
				$obj = $db->fetch_object($db->query($sql_select));

				$sql = "SELECT nom FROM ".MAIN_DB_PREFIX."societe WHERE rowid=".$id_societe;
				$res = $db->query($sql);
				$nom_societe = $db->fetch_object($res)->nom;
		
				$action_effectue = "Mise à (".$ch.") de la variable : afficher_regularisation_its de la société ".$nom_societe;
				$sql_log = 'INSERT INTO '.MAIN_DB_PREFIX.'log (fk_user, nom, prenom, quand, action_effectue, object_concerne)';
				$sql_log .= ' VALUES('.$user->id.', "'.$obj->lastname.'","'.$obj->firstname.'",now(),"'.$action_effectue.'","Modification")';
				$db->query($sql_log);
		
				$message = 'Modification effectuée avec succès';
			}
		}else
			$message = 'Un problème est survenu';
			print $db->error();

		
		$action = "liste";
	
	}

if($action == "liste"){
    //print_barre_liste("", $page, $_SERVER["PHP_SELF"], "", "", "", "", "", "", 'bill', 0, dolGetButtonTitle("Créer un nouveau type de Banque", '', 'fa fa-plus-circle', './ajout_modele_bulletin.php?mainmenu=paiementsalaire&leftmenu=autre&action=create', '', 1), '', 0, 0, 0, 1);
    print '<table style="width: 100%" class="tagtable liste">';
 print '<tr class="liste_titre">';
 print '<td style="width: 20%; color: darkblue; padding:" align=""><label>Libellé</label></td>';
 print '<td style="width: 70%; color: darkblue; padding:" align=""><label>Description</label></td>';
 print '<td style="width: 10%; color: darkblue; padding:" align=""><label>Activé/Désactivé</label></tr>';
  $modele_bulletin = "SELECT * FROM ".MAIN_DB_PREFIX."modele_bulletin";
	$result_modele_bulletin = $db->query($modele_bulletin);//= $db->query($covSql);

	if($result_modele_bulletin){
		$i = 0;
		$num = $db->num_rows($result_modele_bulletin);
		while ($i < $num){
			$obj_modele_bulletin = $db->fetch_object($result_modele_bulletin);

            print '<tr class="impair">';
            print ''.affiche_long_texte(img_picto("", "statut7_blue", "class='paddingright pictofixedwidth'"), $obj_modele_bulletin->libelle, 0, '', 'nom', '', '', '', '').'</td>';
            print ''.affiche_long_texte('', $obj_modele_bulletin->commentaire, 1, '', '', '', '', '', '').'</td>';
            if($obj_modele_bulletin->actif == 1)
                print '<td align=""><input type="radio" name="drone" id="radio'.$i.'" checked />';
            else
                print '<td align=""><input type="radio" name="drone" onclick="myFunction('.$obj_modele_bulletin->rowid.')" id="radio'.$i.'" />';

            print '</td>';
            print '</tr>';

            $i ++;
			}
            $db->free($result_modele_bulletin);
			
		}else print '<tr><td align="center" colspan="4">Aucun type bulletin disponible</td></tr>';
	
        print'</table>';
        print "<script>
        function myFunction(e){
               window.location.href = '".$_SERVER["PHP_SELF"]."?action=activation&id_modele_bulletin='+e;
          }
        
        </script>";

 print'</table>';

}


//Les Regularisation ITS

print '<br><h3>Affichage sur Bulletin</h3>';
	print '<table class="tagtable liste">';
	print '<tr class="liste_titre">';
	print '<td >Sociétés</td>';
	print '<td >Afficher la regularisation I.T.S sur le bulletin'.info_admin("Visible après la génération des salaires de Décembre", 1).'</td>';

$sql = "SELECT sc.rowid as r1, sc.nom, sc.name_alias, sc.phone, sc.fax, sc.code_client, sc.zip, sce.rowid as r2, sce.fk_object, sce.conv FROM ".MAIN_DB_PREFIX."societe as sc";
	$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."societe_extrafields as sce ON sc.rowid=sce.fk_object WHERE sce.grp=1";

	if($user->id != 1)
        $sql .= " AND sc.rowid IN ".$array_id_soc;
		
	$sql .= " ORDER BY sc.rowid ASC";
	$result = $db->query($sql);

	if($result){
		$i = 0;
		$num = $db->num_rows($result);
		while ($i < $num){
		$societe = $db->fetch_object($result);

		print '<tr  class="pair">';
		print '<td> <a href="../../societe/card.php?socid='.$societe->r1.'">'.$societe->nom.'</a></td>';
		$sql_soc = "SELECT afficher_regularisation_its FROM ".MAIN_DB_PREFIX."salairepaie_societe WHERE fk_societe=".$societe->r1;
		$result_soc = $db->query($sql_soc);
		if($result_soc)
			$info_soc = $db->fetch_object($result_soc);

		$rep = "Non";
		if($info_soc->afficher_regularisation_its == 1)
			$rep = "Oui";
		print '<td><mark>'.$rep.'</mark>  <a href="./modele_bulletin.php?mainmenu=paiementsalaire&leftmenu=reglage&id_societe='.$societe->r1.'&action=modifier_regularisation_its">'.img_edit('Modifier logo').'</a></td>';

		/*$extension = '';
		if (file_exists('./logo_societe_soc/'.$societe->r1.'.png'))
			$extension = '.png';
		else if(file_exists('./logo_societe/'.$societe->r1.'.jpeg'))
			$extension ='.jpeg';
		else $extension = '.jpg';

		if($info_soc->societe_mere == 1){
			if (file_exists('./logo_societe/'.$societe->r1.'.png') || file_exists('./logo_societe/'.$societe->r1.'.jpeg') || file_exists('./logo_societe/'.$societe->r1.'.jpg'))
				print '<td aligh="right"><img height=20 src="./logo_societe/'.$societe->r1.$extension.'" ><a href="./logo_societe.php?mainmenu=paiementsalaire&leftmenu=reglage&id_societe='.$societe->r1.'&action=modifier_logo"> '.img_edit('Modifier logo').'</a></td>';
			else print '<td aligh="right" ><a href="./logo_societe.php?mainmenu=paiementsalaire&leftmenu=reglage&id_societe='.$societe->r1.'&action=modifier_logo">'.img_edit('Modifier logo').'</a></td>';
		}else{
			print '<td>'.img_edit('Veuillez mettre -->Utilisés les informations de la société mère<-- à Oui').'</td>';
		}*/




		print '</tr>';
	$i++;
	$ligne ++;
		}
}

if(!empty($message))
	print "<script>
	$.jnotify('".$message."', {delay : 5000, fadeSpeed: 500});
	</script>";