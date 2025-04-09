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
 require_once DOL_DOCUMENT_ROOT.'/custom/paiementsalaire/class/html.form.class.php';

 //require_once DOL_DOCUMENT_ROOT.'/core/class/html.formpaiementsalaire.class.php';
 //require_once DOL_DOCUMENT_ROOT.'/custom/paiementsalaire/core/modules/modPaiementSalaire.class.php';
 
 //$PaiementSalaire = new modPaiementSalaire($db);
 
 llxHeader("", "Paiement | Salaire");

 $message = "";

 $action = GETPOST('action','alpha');
if(empty($action))	
	$action = 'liste';

    //Ajout des nouveaux barèmes
    if($action == "save_edit"){
        $id_prestation = GETPOST('id_prestation','int');
        $societe = GETPOST('societe','alpha');//la valeur contient : _s
        $salarial = GETPOST('salarial','alpha');//la valeur contient : _t
        $patronal = GETPOST('patronal','alpha');//la valeur contient : _t
        if(!isset($id_prestation))
            $message = 'Aucune cotisation selectionnée<br>';
        if(!isset($societe) || count(explode('_', $societe)) == 1 )
            $message .= 'LE CHAMP "SOCIETE" EST OBLIGATOIRE<br>';

        if(!isset($salarial) || count(explode('_', $salarial)) == 1)
            $message .= 'LE CHAMP "TAUX SALARIALE" EST OBLIGATOIRE<br>';

        if(!isset($patronal) || count(explode('_', $patronal)) == 1)
            $message .= 'LE CHAMP "TAUX PATRONALE" EST OBLIGATOIRE';

        if(empty($message)){
            $id_societe = explode('_', $societe)[0];
            $taux_sal = explode('_', $salarial)[0];
            $taux_patro = explode('_', $patronal)[0];

            //Enregistrement des données dans la base de données
            $sql_particulier = 'INSERT INTO '.MAIN_DB_PREFIX.'taux_cotisation_societe (fk_prestation, fk_societe, taux_salariale, taux_patronale)';
			$sql_particulier .= ' VALUES('.$id_prestation.','.$id_societe.', '.$taux_sal.', '.$taux_patro.')';
            if($db->query($sql_particulier))
                $message = "Les nouveaux taux enregistrés avec succès";
            else $message = "Un problème est survenu ";

            print "<h2 style='background-color: red;'>".$db->error()."</h2>";
            //Trace
            $type_prest = "SELECT code FROM ".MAIN_DB_PREFIX."type_prestation WHERE rowid=".$id_prestation;
            $result_type_prest = $db->query($type_prest);
            $num = $db->num_rows($result_type_prest);
            if (0 < $num)
                $obj_prest_type = $db->fetch_object($result_type_prest);

            $soc = "SELECT nom FROM ".MAIN_DB_PREFIX."societe WHERE rowid=".$id_societe;
            $result_soc = $db->query($soc);
            $num = $db->num_rows($result_soc);
            if (0 < $num)
                $obj_soc = $db->fetch_object($result_soc);

             //Enregistrement dans le log
            $sql_select = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$user->id;
            $obj = $db->fetch_object($db->query($sql_select));

            $desc = "Ajout Taux Cotisation";
            $action_effectue = "Ajout d'un taux particulier (".$obj_prest_type->code.") charge salariale".$taux_sal.", charge patronale=".$taux_patro."à la société ".$obj_soc->nom;
            $sql_log = 'INSERT INTO '.MAIN_DB_PREFIX.'log (fk_user, nom, prenom, quand, action_effectue, object_concerne)';
            $sql_log .= ' VALUES('.$user->id.', "'.$obj->lastname.'","'.$obj->firstname.'",now(),"'.$action_effectue.'","'.$desc.'")';
            $db->query($sql_log);

            $action = "detail_cotisation";
            
        }else $action = "selection_societe";


    }


    if($action == "selection_societe"){
        $id_prestation = GETPOST('id_prestation','int');

        //Les sociétés dont l'utilisateurs est autorisé à voir
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

        //liste des sociétés qui ont déja un barème particulier pour cette cotisation
        $retrait = '(-1';
        $sql_particulier = 'SELECT fk_societe FROM '.MAIN_DB_PREFIX.'taux_cotisation_societe WHERE fk_prestation='.$id_prestation;
        $res_part = $db->query($sql_particulier);
        if($res_part){
            $i = 0;
            $num = $db->num_rows($res_part);
            while ($i < $num){
                $societe = $db->fetch_object($res_part);
                $retrait .= ", ".$societe->fk_societe;
               
                $i ++;
            }
        }
        $retrait .= ")";

            //Liste des sociétés
        $sql = "SELECT sc.rowid as r1, sc.nom FROM ".MAIN_DB_PREFIX."societe as sc";
        $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."societe_extrafields as sce ON sc.rowid=sce.fk_object WHERE sce.grp=1 AND sc.rowid NOT IN ".$retrait;
        if($user->id != 1)
            $sql .= " AND sc.rowid IN ".$array_id_soc;

        $sql .= " ORDER BY sc.rowid ASC";
        $result = $db->query($sql);
        if($result){
            $i = 0;
            $num = $db->num_rows($result);
            while ($i < $num){
                $societe = $db->fetch_object($result);
                $key[] = $societe->r1."_s";
                $val[] = $societe->nom;
                $i ++;
            }
        }


              
        $array1= array('label'=> 'Société','type'=> 'select', 'size'=>'', 'morecss'=>'', 'moreattr'=>'selected', 'name'=>'societe','values' => array_combine($key,$val));


        //Liste des Barèmes
        $sal_key = array();
        $sal_value = array();
        $patro_key = array();
        $patro_value = array();
        
        $prest_sql = "SELECT * FROM ".MAIN_DB_PREFIX."bareme_prestation WHERE fk_prestation=".$id_prestation;
        $result_prestation = $db->query($prest_sql);//= $db->query($covSql);
        if($result_prestation){
            $i = 0;
            $num = $db->num_rows($result_prestation);
            while ($i < $num){
                $obj_prestation = $db->fetch_object($result_prestation);

                $sal_key[] = $obj_prestation->taux_salariale."_t";
                $sal_value[] = $obj_prestation->taux_salariale;

                $patro_key[] = $obj_prestation->taux_patronale."_t";
                $patro_value[] = $obj_prestation->taux_patronale;

                $i ++;
            }
        }
        //Valeur salarial
        $array2= array('label'=> 'Taux salariale','type'=> 'select', 'size'=>'', 'morecss'=>'', 'moreattr'=>'selected', 'name'=>'salarial','values' => array_combine($sal_key,$sal_value));

        //Valeur patronal
        $array3= array('label'=> 'Taux patronale','type'=> 'select', 'size'=>'', 'morecss'=>'', 'moreattr'=>'selected', 'name'=>'patronal','values' => array_combine($patro_key,$patro_value));


        $gobal = array($array1, $array2, $array3);
        $type_prest = "SELECT code FROM ".MAIN_DB_PREFIX."type_prestation WHERE rowid=".$id_prestation;
        $result_type_prest = $db->query($type_prest);
        $num = $db->num_rows($result_type_prest);
        if (0 < $num){
            $obj_prest_type = $db->fetch_object($result_type_prest);

            $monform = new Form1($db);
            $url = $_SERVER['PHP_SELF']."?mainmenu=paiementsalaire&leftmenu=reglage&id_prestation=".$id_prestation;
            
            $formconfirm = $monform->formconfirm1(
                $url,
                'Cas particulier pour ('.$obj_prest_type->code.') Veuillez selectionner une société',
                "",
                'save_edit',
                $gobal,
                '',
                1,
                250,
                '40%'
            );
            print $formconfirm;
        }else $message = "Cotisation non trouvée!";
		$action = "liste";


	}

    /*if($action == "modifier"){

        $action = 'detail_cotisation';    
    }*/

    if($action == "supprimer"){
        $id_prestation = GETPOST('id_prestation','int');
        $id_societe = GETPOST('id_societe','int');
        $id_bareme = GETPOST('id_bareme','int');

        $monform = new Form1($db);
            $url = $_SERVER['PHP_SELF']."?mainmenu=paiementsalaire&leftmenu=reglage&id_prestation=".$id_prestation."&id_societe=".$id_societe."&id_bareme=".$id_bareme;
            
            $formconfirm = $monform->formconfirm(
                $url,
                'Voulez-vous vraiment effectué cette suppression',
                "",
                'suppression',
                '',
                '',
                1,
                150,
                '30%'
            );
            print $formconfirm;

            $action = 'detail_cotisation';
    }

    if($action == "suppression"){
        $id_prestation = GETPOST('id_prestation','int');
        $id_societe = GETPOST('id_societe','int');
        $id_bareme = GETPOST('id_bareme','int');

        //recuperation des anciennes valeur
        $sql_particulier = 'SELECT taux_salariale, taux_patronale FROM '.MAIN_DB_PREFIX.'taux_cotisation_societe WHERE rowid='.$id_bareme;
        $res_part = $db->query($sql_particulier);
        if($res_part)
            $obj_part = $db->fetch_object($res_part);

            //suppression
        $sql_del = "DELETE FROM ".MAIN_DB_PREFIX."taux_cotisation_societe WHERE rowid=".$id_bareme;
        if($db->query($sql_del)){
            $message = 'Suppression effectuée avec succès';

            //La trace
            $type_prest = "SELECT code FROM ".MAIN_DB_PREFIX."type_prestation WHERE rowid=".$id_prestation;
            $result_type_prest = $db->query($type_prest);
            $num = $db->num_rows($result_type_prest);
            if (0 < $num)
                $obj_prest_type = $db->fetch_object($result_type_prest);

            $soc = "SELECT nom FROM ".MAIN_DB_PREFIX."societe WHERE rowid=".$id_societe;
            $result_soc = $db->query($soc);
            $num = $db->num_rows($result_soc);
            if (0 < $num)
                $obj_soc = $db->fetch_object($result_type_prest);

             //Enregistrement dans le log
            $sql_select = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$user->id;
            $obj = $db->fetch_object($db->query($sql_select));

            $desc = "Suppression du taux particulier";
            $action_effectue = "Ajout d'un taux particulier (".$obj_prest_type->code.") avec (taux_salariale=".$obj_part->taux_salariale." et taux_patronale=".$obj_part->taux_patronale.") à la société ".$obj_soc->nom;
            $sql_log = 'INSERT INTO '.MAIN_DB_PREFIX.'log (fk_user, nom, prenom, quand, action_effectue, object_concerne)';
            $sql_log .= ' VALUES('.$user->id.', "'.$obj->lastname.'","'.$obj->firstname.'",now(),"'.$action_effectue.'","'.$desc.'")';
            $db->query($sql_log);
        }else{
            $message = 'Un problème est survenu';
            print "<h2 style='background-color: red;'>".$db->error()."</h2>";
        }
        $action = 'detail_cotisation';

    }
    //Affichage des détails du barème particulier de cette cotisation
    if($action == "detail_cotisation"){
        $id_prestation = GETPOST('id_prestation','int');

        $type_prest = "SELECT code FROM ".MAIN_DB_PREFIX."type_prestation WHERE rowid=".$id_prestation;
        $result_type_prest = $db->query($type_prest);
        $num = $db->num_rows($result_type_prest);
        if (0 < $num){
            $obj_prest_type = $db->fetch_object($result_type_prest);
        }
        
        print load_fiche_titre($langs->trans("Barèmes particuliers de <mark>".$obj_prest_type->code)."</mark>", '', '');

        print '<span style="float:right;"><a title="Reglages" href="./cotisation_societe_cas_particulier.php?mainmenu=paiementsalaire&leftmenu=reglage&action=liste">Retour</a></span><br>';

        print '<table class="tagtable liste">';
        print '<tr class="liste_titre"><td align=""><label>Sociétés</label></td>';
        print '<td align=""><label>Charge patronale</label></td>';
        //print '<td align=""><label>Nature</label></td>';
        print '<td align="right"><label>Charges salariale</label></td>';
        print '<td align="right"><label>Opérations</label></td>';


        print '</tr>';

        $sql_particulier = 'SELECT rowid, fk_societe, taux_salariale, taux_patronale FROM '.MAIN_DB_PREFIX.'taux_cotisation_societe WHERE fk_prestation='.$id_prestation;
        $res_part = $db->query($sql_particulier);
        if($res_part){
            $i = 0;
            $num = $db->num_rows($res_part);
            while ($i < $num){
                $obj_part = $db->fetch_object($res_part);

                //Recherche de la société
                $sql = "SELECT nom FROM ".MAIN_DB_PREFIX."societe WHERE rowid=".$obj_part->fk_societe;
                $result = $db->query($sql);
                $societe = $db->fetch_object($result);
                
                $class = "impair";
                if($i%2 == 0)
                    $class = "pair";
                print '<tr class='.$class.'><td align=""><label>'.$societe->nom.'</label></td>';
                print '<td align=""><label>'.$obj_part->taux_patronale.'</label></td>';
                print '<td align="right"><label>'.$obj_part->taux_salariale.'</label></td>';
                print '<td align="right">';
                //Modification non disponible
                //print "<a style='text-decoration : none;' href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenu=reglage&id_prestation=".$id_prestation."&id_societe=".$obj_part->fk_societe."&id_bareme=".$obj_part->rowid."&action=modifier'>".img_edit("Modifier le barème", "")."</a>&nbsp;&nbsp; &nbsp;";								
                print "<a style='text-decoration : none;' href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenu=reglage&id_prestation=".$id_prestation."&id_societe=".$obj_part->fk_societe."&id_bareme=".$obj_part->rowid."&action=supprimer'>".img_delete("Supprimer le barème", "")."</a>&nbsp;&nbsp; &nbsp;";								

                print '</td>';

                print '</tr>';

                $i ++;
            }

            if($num == 0){
                print '<tr class="pair"><td align="center" colspan=4><label>Aucun barème disponible</label></td></tr>';

            }
        }
    }

    if($action == "liste"){
        print load_fiche_titre($langs->trans("Liste des types de cotisations"), '', '');
        //print '<hr>';
        print '<span style="float:right;"><a title="Reglages" href="./reglages.php?mainmenu=paiementsalaire&leftmenu=reglage">Retour</a></span><br>';

        print '<table class="tagtable liste">';
        print '<tr class="liste_titre"><td align=""><label>Code</label></td>';
        print '<td align=""><label>Désignation</label></td>';
        //print '<td align=""><label>Nature</label></td>';
        print '<td align="right"><label>Fixer un nouveau barème</label></td>';


        $type_prest = "SELECT * FROM ".MAIN_DB_PREFIX."type_prestation";
        $result_type_prest = $db->query($type_prest);
        if($result_type_prest){
            $i = 0;
            $num = $db->num_rows($result_type_prest);
                while ($i < $num){
                    $obj_prest_type = $db->fetch_object($result_type_prest);


                    print '<tr class="impair">';
                    $sql_particulier = 'SELECT count(rowid) as nb FROM '.MAIN_DB_PREFIX.'taux_cotisation_societe WHERE fk_prestation='.$obj_prest_type->rowid;
                    if($db->query($sql_particulier))
                        if($db->fetch_object($db->query($sql_particulier))->nb > 0)
                            print affiche_long_texte(img_picto("", "statut7_blue", "class='paddingright pictofixedwidth'"), $obj_prest_type->code, 0, $_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=reglage&id_prestation='.$obj_prest_type->rowid.'&action=detail_cotisation', 'nom', '', '', '', '').'</a></td>';
                        else   print affiche_long_texte(img_picto("", "statut7_red", "class='paddingright pictofixedwidth'"), $obj_prest_type->code, 0, '', 'nom', '', '', '', '').'</a></td>';
                    else
                        print affiche_long_texte(img_picto("", "statut7_blue", "class='paddingright pictofixedwidth'"), $obj_prest_type->code, 0, '', 'nom', '', '', '', '').'</a></td>';

                    print affiche_long_texte('', $obj_prest_type->commentaire, 1, '', '', '', '', '', '');
                    //print ''.affiche_long_texte('', $obj_prest_type->nature, 0, '', 'nom_vide', '', '', '', '');

                    print "<td align='right'>";
                    if($db->query($sql_particulier)) //Affichage des détails
                        if($db->fetch_object($db->query($sql_particulier))->nb > 0) //le plus (+) pour ajouter est bleu
                            print "<a style='text-decoration : none;' title='Voir les détail' href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenu=reglage&id_prestation=".$obj_prest_type->rowid."&action=detail_cotisation'><span class='fa fa-search-plus'></span></a>&nbsp;&nbsp; &nbsp;";								
                        else //il est grisé
                            print "<span class='fa fa-search-plus' title='Aucun détail à voir' style='color: gray'></span> &nbsp;&nbsp;&nbsp;";
                    else //il est grisé
                        print "<span title='Aucun détail à voir' class='fa fa-search-plus' style='color: gray'></span> &nbsp;&nbsp;&nbsp;";
                    print "<a style='text-decoration : none;' title='Ajouter un nouveau taux pour une société' href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenu=reglage&id_prestation=".$obj_prest_type->rowid."&action=selection_societe'><span class='fa fa-plus' style='color: blue'></span></a>";
                    print "</td>";								

								
                    print "</tr>";								
                    $i ++;

                }
                $db->free($result_type_prest);

                if($num == 0)
                    print '<tr><td align="center" colspan="5">Auccun Salaire Catégorie disponible!</td></tr>';
        } else print '<tr><td align="center" colspan="5">Auccun Salaire Catégorie disponible!</td></tr>';
    } 

        print '</table>';


    if(!empty($message))
         print "<script>
         $.jnotify('".$message."', {delay : 5000, fadeSpeed: 500});
         </script>";