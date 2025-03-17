<?php
require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/custom/paiementsalaire/lib/paiementsalaire.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';


llxHeader("", "Paiement | Salaire");
//Titre 
//print load_fiche_titre($langs->trans("Les Cotisations de la societe"), '', '');
//print '<hr>';

$id_societe = GETPOST('id_societe','int');
$action =  GETPOST('action','alpha');
$id_convention = GETPOST('id_convention','int');


/*if(!$user->rights->paiementsalaire->societe->genererBulletin && $user->rights->paiementsalaire->salarie->voirDocument){
	print "<h2 style='align:center;'>Vous n'avez ni la permission voir ni la permission de génerer les bulletins!</h2>";
}elseif(!$user->rights->paiementsalaire->societe->genererBulletin){
	print "<h2 style='align:center;'>Vous n'avez pas la permission de génerer les bulletins!</h2>";
}*/

//Test du droit de lecture
if($user->rights->paiementsalaire->societe->read){ 
    $message = "";
    print load_fiche_titre($langs->trans("Génération des bulletins de paie"), '', '');

    if(empty($action))
        $action = "annee_rechercher";

    $head = paiementsalaireSocieteHead($id_societe, $id_convention);
    print dol_get_fiche_head($head, 'bonus', "", 0, '');

    if(!empty($id_convention)){
        $conv_sql = 'SELECT nom FROM '.MAIN_DB_PREFIX.'convention WHERE rowid='.$id_convention;
        $res_conv = $db->query($conv_sql);
        $obj_conv = $db->fetch_object($res_conv);
        $soc_sql = "SELECT * FROM ".MAIN_DB_PREFIX."societe WHERE rowid=".$id_societe;
        $soc_res = $db->query($soc_sql);//= $db->query($covSql);
        $obj_soc = $db->fetch_object($soc_res);
        $obj_soc->name = $obj_soc->nom;
        $obj_soc->element = "societe";			
        $obj_soc->conv = $id_convention;

        societe_preview_next($db, $id_societe, $obj_soc);
        entete_societe($obj_soc, 'societe');
        $id_bull = date('dmyhis');


        $mois_tab = array(" Janvier "," Février "," Mars "," Avril "," Mai "," Juin "," Juillet "," Août "," Septembre "," Octobre "," Novembre "," Décembre ");
        

        $annee = date("Y");
        $mois = (int) date("m");
        $trouve = false;

        $sql = "SELECT sal.fk_user, u.rowid, u.lastname, u.firstname, u.dateemployment, ue.fk_object, ue.egp FROM ".MAIN_DB_PREFIX."salarie as sal";
        $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."user as u ON sal.fk_user=u.rowid";
        $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."user_extrafields as ue ON u.rowid=ue.fk_object Where ue.egp=".$id_societe;

        //$sql = "SELECT u.rowid, u.lastname, u.firstname, u.dateemployment, ue.fk_object, ue.egp FROM ".MAIN_DB_PREFIX."user as u";
        //$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."user_extrafields as ue ON u.rowid=ue.fk_object Where ue.egp=".$id_societe;
        $result = $db->query($sql);
        if($result){
            $num = $db->num_rows($result);
            if($num > 0){
                $trouve = true;
            }
        }
        $monform = new Form($db);
        if($trouve == true){

          if($action == "type_bonus"){
              if(GETPOST('modele_bonus') == 1){
                $action = "special_ajoutbonus";
              }else{
                $action = "ajoutbonus";
              }
          }

          if($action == "creation_bonus"){

           
            $key[] = 0;
            $val[] = "Le même montant pour tout les salariés";

            $key[] = 1;
            $val[] = "Different montant pour chaque salarié";

              
          //$array[] = array('label'=> '','type'=> 'text', 'size'=>'', 'morecss'=>'', 'moreattr'=>'', 'name'=>'','values' => '');
          $array[] = array('label'=> 'Type de bonus','type'=> 'select', 'size'=>'', 'morecss'=>'', 'moreattr'=>'selected', 'name'=>'modele_bonus','values' => array_combine($key,$val));
  

            $mois = GETPOST("mois", "int");
              $annee = GETPOST("annee", "int");
              $id_societe = GETPOST("id_societe", "int");
                $url = $_SERVER['PHP_SELF']."?mainmenu=paiementsalaire&leftmenu=societe&id_societe=".$id_societe."&id_convention=".$id_convention."&annee=".$annee."&mois=".$mois;
                $titre = 'Veillez Choisir le type';

                  $formconfirm = $monform->formconfirm(
                      $url, 
                      $titre, 
                      "", 
                      'type_bonus', 
                      $array, 
                      '', 
                      1,
                      200,
                      '30%'
                  );
                  print $formconfirm;
                  $action = "annee_rechercher";
          }

          if($action == "attente_suppression"){
            $mois = GETPOST("mois", "int");
              $annee = GETPOST("annee", "int");
              $id_societe = GETPOST("id_societe", "int");
                $url = $_SERVER['PHP_SELF']."?mainmenu=paiementsalaire&leftmenu=societe&id_societe=".$id_societe."&id_convention=".$id_convention."&annee=".$annee."&mois=".$mois;
                $titre = 'Veillez confirmer la suppression';

                  $formconfirm = $monform->formconfirm(
                      $url, 
                      $titre, 
                      "", 
                      'supprimer', 
                      $array, 
                      '', 
                      1,
                      300,
                      '30%'
                  );
                  print $formconfirm;
                  $action = "annee_rechercher";
          }
          //Nettoyage des bulletin bonus
          if($action == "supprimer"){
            $mois = GETPOST("mois", "int");
              $annee = GETPOST("annee", "int");
              $id_societe = GETPOST("id_societe", "int");
            //Préparation de la base de donnée
            $sql_verif = "SELECT rowid FROM ".MAIN_DB_PREFIX."bulletin_bonus WHERE annee=".$annee." AND mois=".$mois." AND fk_societe=".$id_societe;
            $res_verif = $db->query($sql_verif);
            if($res_verif){
                              
              $d = 0;
              $dnum = $db->num_rows($res_verif);
              while ($d < $dnum) {
                                  
                $obj_verif = $db->fetch_object($res_verif);

                //suppression
                $sql_del = "DELETE FROM ".MAIN_DB_PREFIX."bulletin_bonus_prime WHERE fk_bulletin=".$obj_verif->rowid;
                $res_del = $db->query($sql_del);

                $sql_del = "DELETE FROM ".MAIN_DB_PREFIX."bulletin_bonus_prime_exceptionnelle WHERE fk_bulletin=".$obj_verif->rowid;
                $res_del = $db->query($sql_del);

                $sql_del = "DELETE FROM ".MAIN_DB_PREFIX."bulletin_bonus_indemnite WHERE fk_bulletin=".$obj_verif->rowid;
                $res_del = $db->query($sql_del);

                $sql_del = "DELETE FROM ".MAIN_DB_PREFIX."bulletin_bonus_taxe WHERE fk_bulletin=".$obj_verif->rowid;
                $res_del = $db->query($sql_del);

                $sql_del = "DELETE FROM ".MAIN_DB_PREFIX."bulletin_bonus_taxe2 WHERE fk_bulletin=".$obj_verif->rowid;
                $res_del = $db->query($sql_del);

                $sql_del = "DELETE FROM ".MAIN_DB_PREFIX."bulletin_bonus_cotisation WHERE fk_bulletin=".$obj_verif->rowid;
                $res_del = $db->query($sql_del);

                $sql_del = "DELETE FROM ".MAIN_DB_PREFIX."bulletin_bonus_anciennete WHERE fk_bulletin=".$obj_verif->rowid;
                $res_del = $db->query($sql_del);

                $sql_del = "DELETE FROM ".MAIN_DB_PREFIX."bulletin_bonus_organisme WHERE fk_bulletin=".$obj_verif->rowid;
                $res_del = $db->query($sql_del);

                $sql_del = "DELETE FROM ".MAIN_DB_PREFIX."bulletin_bonus_heure_sup WHERE fk_bulletin=".$obj_verif->rowid;
                $res_del = $db->query($sql_del);

                $sql_del = "DELETE FROM ".MAIN_DB_PREFIX."bulletin_bonus_avance WHERE fk_bulletin=".$obj_verif->rowid;
                $res_del = $db->query($sql_del);

                $sql_del = "DELETE FROM ".MAIN_DB_PREFIX."bulletin_bonus WHERE rowid=".$obj_verif->rowid;
                $res_del = $db->query($sql_del);


                $d ++;
              }
            }

            if($res_del){
              $message = "Complément de salaire de ".$mois_tab[$mois-1].$annee." supprimer avec succès";

              $sql_select = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$user->id;
              $obj = $db->fetch_object($db->query($sql_select));

              $action_effectue = "Suppression du complément salaire de ".$mois_tab[$mois-1]." ".$annee." de la société ".$obj_soc->nom;
              $sql_log = 'INSERT INTO '.MAIN_DB_PREFIX.'log (fk_user, nom, prenom, quand, action_effectue, object_concerne)';
              $sql_log .= ' VALUES('.$user->id.', "'.$obj->lastname.'","'.$obj->firstname.'",now(),"'.$action_effectue.'","Complements salaires")';
              $db->query($sql_log);
            }else{
              $message = "Un problème est survenu<br>";
              $message .= $db->error();
            }
            $action = "annee_rechercher";

          }

//Enregistrement du bonus
          if($action == 'ajouterbonusValider'){
              $mois = GETPOST("mois", "int");
              $annee = GETPOST("annee", "int");
              $nom_bonus = GETPOST("libelle", "alpha");
              $type = GETPOST("type","alpha");

              //Préparation de la base de donnée
              $sql_verif = "SELECT rowid FROM ".MAIN_DB_PREFIX."bulletin_bonus WHERE annee=".$annee." AND mois=".$mois." AND fk_societe=".$id_societe;
							$res_verif = $db->query($sql_verif);
							if($res_verif){
                                
								$d = 0;
								$dnum = $db->num_rows($res_verif);
								while ($d < $dnum) {
                                    
									$obj_verif = $db->fetch_object($res_verif);

									//suppression
									$sql_del = "DELETE FROM ".MAIN_DB_PREFIX."bulletin_bonus_prime WHERE fk_bulletin=".$obj_verif->rowid;
									$res_del = $db->query($sql_del);

									$sql_del = "DELETE FROM ".MAIN_DB_PREFIX."bulletin_bonus_prime_exceptionnelle WHERE fk_bulletin=".$obj_verif->rowid;
									$res_del = $db->query($sql_del);

									$sql_del = "DELETE FROM ".MAIN_DB_PREFIX."bulletin_bonus_indemnite WHERE fk_bulletin=".$obj_verif->rowid;
									$res_del = $db->query($sql_del);

									$sql_del = "DELETE FROM ".MAIN_DB_PREFIX."bulletin_bonus_taxe WHERE fk_bulletin=".$obj_verif->rowid;
									$res_del = $db->query($sql_del);

									$sql_del = "DELETE FROM ".MAIN_DB_PREFIX."bulletin_bonus_taxe2 WHERE fk_bulletin=".$obj_verif->rowid;
									$res_del = $db->query($sql_del);

									$sql_del = "DELETE FROM ".MAIN_DB_PREFIX."bulletin_bonus_cotisation WHERE fk_bulletin=".$obj_verif->rowid;
									$res_del = $db->query($sql_del);

									$sql_del = "DELETE FROM ".MAIN_DB_PREFIX."bulletin_bonus_anciennete WHERE fk_bulletin=".$obj_verif->rowid;
									$res_del = $db->query($sql_del);

									$sql_del = "DELETE FROM ".MAIN_DB_PREFIX."bulletin_bonus_organisme WHERE fk_bulletin=".$obj_verif->rowid;
									$res_del = $db->query($sql_del);

									$sql_del = "DELETE FROM ".MAIN_DB_PREFIX."bulletin_bonus_heure_sup WHERE fk_bulletin=".$obj_verif->rowid;
									$res_del = $db->query($sql_del);

									$sql_del = "DELETE FROM ".MAIN_DB_PREFIX."bulletin_bonus_avance WHERE fk_bulletin=".$obj_verif->rowid;
									$res_del = $db->query($sql_del);

									$sql_del = "DELETE FROM ".MAIN_DB_PREFIX."bulletin_bonus WHERE rowid=".$obj_verif->rowid;
									$res_del = $db->query($sql_del);


									$d ++;
								}
							}

                  if(GETPOST('tout_salarie', "alpha") == 'oui'){
                    $sql_verif = "SELECT * FROM ".MAIN_DB_PREFIX."bulletin WHERE annee=".$annee." AND mois=".$mois." AND fk_societe=".$id_societe;
                    $res_verif = $db->query($sql_verif);
                    if($res_verif){
                      $a = 0;
                      $num = $db->num_rows($res_verif);
                      while ($a < $num) {
                        $obj_verif = $db->fetch_object($res_verif);

                          //Calcul Enregistrement dans le bulletin Bonus
                          //Calcul
                          $salaire_brut = 0;
                            $salaire_brut_imposable = 0;
                            $salaire_brut_cotisable = 0;
                            $base = 0;
                            if($type == 'fixe'){
                              $montant_fixe = GETPOST("fixe","int");
                              $base = $montant_fixe;
                              $salaire_brut = $montant_fixe;
                              $salaire_brut_imposable = $salaire_brut;
                              $salaire_brut_cotisable = $salaire_brut;
                              $montant_pourcentage = "100";
                            }elseif($type == 'pourcentage'){
                              $montant_pourcentage = explode('%',GETPOST("pourcentage","int"))[0];
                              $base = $obj_verif->net_payer;
                              $salaire_brut = $base*$montant_pourcentage/100;
                              $montant_fixe = $salaire_brut;
                              $salaire_brut_imposable = $salaire_brut;
                              $salaire_brut_cotisable = $salaire_brut;
                            }
                          
                          $salaire_net = 0;
                          $retenu_prest_empl = 0;
                          $retenu_prest_patro = 0;
                          $retenu_taxe = 0;
                          $retenu = 0;
                          $inps = 0;

                          $old_fk_orga = 0;
                          $nom_organisme = array();
                          $id_organisme = array();
                          $montant_org_sal = array();
                          $montant_org_patro = array();
                          $pourcentage_org = array();

                          $index = 0;
                          $global_cotis = salarie_prestation_organisme($db, $obj_verif->fk_salarie, $id_convention);
                          $cotis = $global_cotis[1];
                          $taux_p = $global_cotis[0];
                          foreach ($cotis as $key => $value) {
                            $type_prest = "SELECT * FROM ".MAIN_DB_PREFIX."type_prestation WHERE rowid=".$key;
                            $result_type_prest = $db->query($type_prest);
                            $obj_prest_type = $db->fetch_object($result_type_prest);

                            if($obj_prest_type->fk_organisme != $old_fk_orga){
                              $old_fk_orga = $obj_prest_type->fk_organisme;
                              $organisme = "SELECT rowid, nom_organisme FROM ".MAIN_DB_PREFIX."organisme WHERE rowid=".$old_fk_orga;
                              $result_organisme = $db->query($organisme);
                              $id_organisme[] = $old_fk_orga;
                              $obj_organisme = $db->fetch_object($result_organisme);
                              $nom_organisme[] = $obj_organisme->nom_organisme;
                              $montant_org_sal[] = $value*$salaire_brut_cotisable/100;
                              $montant_org_patro[] = $taux_p[$index]*$salaire_brut_cotisable/100;
                              $pourcentage_org[] = $value;

                              $retenu_prest_empl += $value*$salaire_brut_cotisable/100;
                              $retenu_prest_patro += $taux_p[$index]*$salaire_brut_cotisable/100;
                            }else{
                              $retenu_prest_empl += $value*$salaire_brut_cotisable/100;
                              $retenu_prest_patro += $taux_p[$index]*$salaire_brut_cotisable/100;

                              $montant_org_sal[(count($montant_org_sal) - 1)] += $value*$salaire_brut_cotisable/100;
                              $montant_org_patro[(count($montant_org_patro) - 1)] += $taux_p[$index]*$salaire_brut_cotisable/100;
                              $pourcentage_org[count($pourcentage_org)-1] += $value;
                            }
                            
                            if($obj_prest_type->rowid != 6)
                              $inps += $value*$salaire_brut_cotisable/100;

                            $index ++;
                          }

                            //les prestations à afficher sur le bulletin
                            $index = 0;
                              $global_cotis = salarie_prestation($db, $obj_verif->fk_salarie, $id_convention);
                              $cotis = $global_cotis[1];
                              $taux_p = $global_cotis[0];
                              foreach ($cotis as $key => $value) {
                                $type_prest = "SELECT rowid, fk_organisme, code, affiche_bulletin FROM ".MAIN_DB_PREFIX."type_prestation WHERE rowid=".$key;
                                  $result_type_prest = $db->query($type_prest);
                                  $obj_prest_type = $db->fetch_object($result_type_prest);
                            
                                  $array_prestation[$index][0] = $key;
                                  $array_prestation[$index][1] = $obj_prest_type->affiche_bulletin;
                                  $array_prestation[$index][2] = $value*$salaire_brut_cotisable/100;
                                  $array_prestation[$index][3] = $taux_p[$index]*$salaire_brut_cotisable/100;
                                  $array_prestation[$index][4] = $value;
                                  $array_prestation[$index][5] = $taux_p[$index];
                                  $array_prestation[$index][6] = $obj_prest_type->code;

                                  $index ++;
                                  if(!in_array($obj_prest_type->fk_organisme, $id_organisme)){
                                    $retenu_prest_empl += apres_virgule($db, $id_societe, $value*$salaire_brut_cotisable/100, 2);
                                    $retenu_prest_patro += apres_virgule($db, $id_societe, $taux_p[$index]*$salaire_brut_cotisable/100, 2);
                                    if($obj_prest_type->rowid != 6) //juste AMO
                                      $inps += $value*$salaire_brut_cotisable/100; 
                                  }
                              }//A par amo les autres detail de l'INPS ne sont pas soumis aux impôt

                              //les taxes qui ont comme barème : barème cotisation
                        $index = 0;
                        $global_taxe = salarie_taxe2($db, $obj_salarie->rowid, $id_convention);
                        $taxe = $global_taxe[1];
                        $taux_t = $global_taxe[0];
                        foreach ($taxe as $key => $value) {
                          $type_taxe = "SELECT rowid, libelle, fk_organisme, affiche_bulletin FROM ".MAIN_DB_PREFIX."type_taxe WHERE rowid=".$key;
                            $result_type_taxe = $db->query($type_taxe);
                            $obj_taxe_type = $db->fetch_object($result_type_taxe);

                            $array_taxe[$index][0] = $key;
                            $array_taxe[$index][1] = $obj_taxe_type->affiche_bulletin;
                            $array_taxe[$index][2] = $value*$salaire_brut/100;
                            $array_taxe[$index][3] = $taux_t[$index]*$salaire_brut/100;
                            $array_taxe[$index][4] = $value;
                            $array_taxe[$index][5] = $taux_t[$index];
                            $array_taxe[$index][6] = $obj_taxe_type->libelle;

                            $index ++;
                        }

                              $salaire_brut_imposable -= $inps;
                              //tratement de l'its
                              $its = its_salarie($db, "", $salaire_brut_imposable, $obj_verif->situation_familiale, $obj_verif->nombre_enfant, $obj_verif->nombre_enfant_hand);
                              $retenu_taxe = $its[2];

                              $retenu = $retenu_prest_empl + $retenu_taxe;
                              //calcul du salaire net
                              $salaire_net = $salaire_brut - $retenu_prest_empl - $retenu_taxe;
                              //print $obj_verif->nom."  ".$obj_verif->nom." =".$salaire_brut_imposable." BC=".$salaire_brut_cotisable." SN=".$salaire_net." R=".$retenu."<br>";

                              $sql_bulletin = 'Insert into '.MAIN_DB_PREFIX.'bulletin_bonus (id_bull, nom_bonus, nom, prenom, fk_salarie, matricule, situation_familiale, nombre_enfant, nombre_enfant_hand, categorie
                          , echelon, contrat, diplome, type_salarie, fonction, date_embauche, sexe, pays, ville, addresse, tel, email, annee, mois, salaire_brut, salaire_brut_cotisable,
                          salaire_brut_imposable, net_payer, fk_societe, nom_societe, logo_societe, nom_convention,inps,amo,banque,compte,montant,pourcentage, base) 
                          VALUES("'.$id_bull.'","'.$nom_bonus.'","'.$obj_verif->nom.'","'.$obj_verif->prenom.'",'.$obj_verif->fk_salarie.',"'.$obj_verif->matricule.'","'.$obj_verif->situation_familiale.'",'.$obj_verif->nombre_enfant.','.$obj_verif->nombre_enfant_hand.',
                          "'.$obj_verif->categorie.'","'.$obj_verif->echelon.'","'.$obj_verif->contrat.'","'.$obj_verif->diplome.'","'.$obj_verif->type_salarie.'","'.$obj_verif->fonction.'","'.$obj_verif->date_embauche.'",
                          "'.$obj_verif->sexe.'","'.$obj_verif->pays.'","'.$obj_verif->ville.'","'.$obj_verif->addresse.'","'.$obj_verif->tel.'","'.$obj_verif->email.'",
                          '.$annee.','.$mois.',"'.round($salaire_brut, 2).'","'.round($salaire_brut_cotisable, 2).'","'.round($salaire_brut_imposable, 2).'","'.round($salaire_net).'",'.$obj_verif->fk_societe.',"'.$obj_verif->nom_societe.'","'.$obj_verif->logo_societe.'",
                          "'.$obj_verif->nom_convention.'","'.$obj_verif->inps.'","'.$obj_verif->amo.'","'.$obj_verif->banque.'","'.$obj_verif->compte.'","'.$montant_fixe.'","'.$montant_pourcentage.'","'.$base.'")';

                              $res_bulletin = $db->query($sql_bulletin);
                              if($res_bulletin){
                                $sql_verif_bonus = "SELECT rowid FROM ".MAIN_DB_PREFIX."bulletin_bonus WHERE fk_salarie=".$obj_verif->fk_salarie." AND annee=".$annee." AND mois=".$mois;
                                $res_verif_bonus = $db->query($sql_verif_bonus);
                                $obj_last = $db->fetch_object($res_verif_bonus);
                                $rowid_bulletin = $obj_last->rowid;

                        
                            //insertion dans la table bulletin taxe
                            if($rowid_bulletin){
                              $fk_taxe = 1;
                              $montant = $its[2];
                              $libelle = $its[3];
                              $affiche_bulletin = "Oui";
                              $sql_bulletin = 'Insert into '.MAIN_DB_PREFIX.'bulletin_bonus_taxe (fk_bulletin, fk_taxe, libelle, taux, montant, affiche_bulletin)';
                              $sql_bulletin .= ' VALUES('.$rowid_bulletin.','.$fk_taxe.',"'.$libelle.'","'.round($its[0], 2).'","'.round($montant).'","'.$affiche_bulletin.'")';
                              $res_bulletin = $db->query($sql_bulletin);
                            }

                            //CFE et TL
                            for ($g=0; $g < count($array_taxe); $g++) {
                              $fk_taxe = $array_taxe[$g][0];
                              $affiche_bulletin = $array_taxe[$g][1];
                              $montant_employe = $array_taxe[$g][2]?:0;
                              $montant_employeur = $array_taxe[$g][3]?:0;
                              $taux_employe = $array_taxe[$g][4]?:0;
                              $taux_employeur = $array_taxe[$g][5]?:0;
                              $libelle = $array_taxe[$g][6];
                              //insertion dans la table bulletin cotisations
                              $sql_bulletin = 'Insert into '.MAIN_DB_PREFIX.'bulletin_bonus_taxe2 (fk_bulletin, fk_taxe, libelle, taux_employe, taux_employeur, montant_employe, montant_employeur, affiche_bulletin)';
                              $sql_bulletin .= ' VALUES('.$rowid_bulletin.','.$fk_taxe.',"'.$libelle.'","'.$taux_employe.'","'.$taux_employeur.'","'.round($montant_employe).'","'.round($montant_employeur).'","'.$affiche_bulletin.'")';
                              $res_bulletin = $db->query($sql_bulletin);
                              //if($res_bulletin)
                               // print $sql_bulletin.'<br>';
                            }

                            for ($g=0; $g < count($array_prestation); $g++) { 
                              $fk_cotisation = $array_prestation[$g][0];
                              $affiche_bulletin = $array_prestation[$g][1];
                              $montant_employe = $array_prestation[$g][2]?:0;
                              $montant_employeur = $array_prestation[$g][3]?:0;
                              $taux_employe = $array_prestation[$g][4]?:0;
                              $taux_employeur = $array_prestation[$g][5]?:0;
                              $libelle = $array_prestation[$g][6];
                              //insertion dans la table bulletin cotisations
                              $sql_bulletin = 'Insert into '.MAIN_DB_PREFIX.'bulletin_bonus_cotisation (fk_bulletin, fk_cotisation, libelle, taux_employe, taux_employeur, montant_employe, montant_employeur, affiche_bulletin)';
                              $sql_bulletin .= ' VALUES('.$rowid_bulletin.','.$fk_cotisation.',"'.$libelle.'","'.$taux_employe.'","'.$taux_employeur.'","'.round($montant_employe).'","'.round($montant_employeur).'","'.$affiche_bulletin.'")';
                              $res_bulletin = $db->query($sql_bulletin);
                            }

                            for ($g=0; $g < count($nom_organisme); $g++) { 
                              $sql_bulletin = 'Insert into '.MAIN_DB_PREFIX.'bulletin_bonus_organisme (fk_bulletin, fk_organisme, nom_organisme, pourcentage, montant_employe, montant_employeur)';
                              $sql_bulletin .= ' VALUES('.$rowid_bulletin.','.$id_organisme[$g].',"'.$nom_organisme[$g].'","'.$pourcentage_org[$g].'","'.round($montant_org_sal[$g]).'","'.round($montant_org_patro[$g]).'")';
                              $res_bulletin = $db->query($sql_bulletin);
                      
                            }
                          }
                        $a ++;
                      }
                      if($res_bulletin){
                        $message = 'Complement de salaire "'.$nom_bonus.'" généré et </br>';
                        $message .= 'lié au mois de '.$mois_tab[($mois-1)].'-'.$annee;
                        //La trace
                        $sql_select = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$user->id;
                        $obj = $db->fetch_object($db->query($sql_select));

                        $action_effectue = "Génération des complements de Salaire ".$mois_tab[$mois-1]." ".$annee." de la société ".$obj_soc->nom;
                        $sql_log = 'INSERT INTO '.MAIN_DB_PREFIX.'log (fk_user, nom, prenom, quand, action_effectue, object_concerne)';
                        $sql_log .= ' VALUES('.$user->id.', "'.$obj->lastname.'","'.$obj->firstname.'",now(),"'.$action_effectue.'","Complements salaires")';
                        $db->query($sql_log);
                      }
                    }else $message = 'Bulletin non généré alors impossiblee de generer le bulletin bonus';
                  }elseif(GETPOST('tout_salarie', "alpha") == 'non'){

                    $sql_verif = "SELECT * FROM ".MAIN_DB_PREFIX."bulletin WHERE annee=".$annee." AND mois=".$mois." AND fk_societe=".$id_societe;
                    $res_verif = $db->query($sql_verif);
                    if($res_verif){
                      $a = 0;
                      $num = $db->num_rows($res_verif);
                      while ($a < $num) {
                        $obj_verif = $db->fetch_object($res_verif);
                        $name = 'salarie'.$obj_verif->fk_salarie;
                        $fk_sal = explode('salarie', $name)[1]; //explode('salarie', $name)[0] = 'salarie'
                          //Calcu et Enregistrement dans le bulletin Bonus                 
                        if(GETPOST($name,'alpha') == 'on'){
                          //Calcul
                          $salaire_brut = 0;
                            $salaire_brut_imposable = 0;
                            $salaire_brut_cotisable = 0;
                            $base = 0;
                            if($type == 'fixe'){
                              $montant_fixe = GETPOST("fixe","int");
                              $base = $montant_fixe;
                              $salaire_brut = $montant_fixe;
                              $salaire_brut_imposable = $salaire_brut;
                              $salaire_brut_cotisable = $salaire_brut;
                              $montant_pourcentage = "100";
                            }elseif($type == 'pourcentage'){
                              $montant_pourcentage = explode('%',GETPOST("pourcentage", "int"))[0];
                              $base = $obj_verif->net_payer;
                              $salaire_brut = $base*$montant_pourcentage/100;
                              $montant_fixe = $salaire_brut;
                              $salaire_brut_imposable = $salaire_brut;
                              $salaire_brut_cotisable = $salaire_brut;
                            }
                          
                          $salaire_net = 0;
                          $retenu_prest_empl = 0;
                          $retenu_prest_patro = 0;
                          $retenu_taxe = 0;
                          $retenu = 0;
                          $inps = 0;

                          $old_fk_orga = 0;
                          $nom_organisme = array();
                          $id_organisme = array();
                          $montant_org_sal = array();
                          $montant_org_patro = array();
                          $pourcentage_org = array();

                          $index = 0;
                          $global_cotis = salarie_prestation_organisme($db, $obj_verif->fk_salarie, $id_convention);
                          $cotis = $global_cotis[1];
                          $taux_p = $global_cotis[0];
                          foreach ($cotis as $key => $value) {
                            $type_prest = "SELECT * FROM ".MAIN_DB_PREFIX."type_prestation WHERE rowid=".$key;
                            $result_type_prest = $db->query($type_prest);
                            $obj_prest_type = $db->fetch_object($result_type_prest);

                            if($obj_prest_type->fk_organisme != $old_fk_orga){
                              $old_fk_orga = $obj_prest_type->fk_organisme;
                              $organisme = "SELECT rowid, nom_organisme FROM ".MAIN_DB_PREFIX."organisme WHERE rowid=".$old_fk_orga;
                              $result_organisme = $db->query($organisme);
                              $id_organisme[] = $old_fk_orga;
                              $obj_organisme = $db->fetch_object($result_organisme);
                              $nom_organisme[] = $obj_organisme->nom_organisme;
                              $montant_org_sal[] = $value*$salaire_brut_cotisable/100;
                              $montant_org_patro[] = $taux_p[$index]*$salaire_brut_cotisable/100;
                              $pourcentage_org[] = $value;

                              $retenu_prest_empl += $value*$salaire_brut_cotisable/100;
                              $retenu_prest_patro += $taux_p[$index]*$salaire_brut_cotisable/100;
                            }else{
                              $retenu_prest_empl += $value*$salaire_brut_cotisable/100;
                              $retenu_prest_patro += $taux_p[$index]*$salaire_brut_cotisable/100;

                              $montant_org_sal[(count($montant_org_sal) - 1)] += $value*$salaire_brut_cotisable/100;
                              $montant_org_patro[(count($montant_org_patro) - 1)] += $taux_p[$index]*$salaire_brut_cotisable/100;
                              $pourcentage_org[count($pourcentage_org)-1] += $value;
                            }
                            
                            if($obj_prest_type->rowid != 6)
                              $inps += $value*$salaire_brut_cotisable/100;

                            $index ++;
                          }

                            //les prestations à afficher sur le bulletin
                            $index = 0;
                              $global_cotis = salarie_prestation($db, $obj_verif->fk_salarie, $id_convention);
                              $cotis = $global_cotis[1];
                              $taux_p = $global_cotis[0];
                              foreach ($cotis as $key => $value) {
                                $type_prest = "SELECT rowid, fk_organisme, code, affiche_bulletin FROM ".MAIN_DB_PREFIX."type_prestation WHERE rowid=".$key;
                                  $result_type_prest = $db->query($type_prest);
                                  $obj_prest_type = $db->fetch_object($result_type_prest);
                            
                                  $array_prestation[$index][0] = $key;
                                  $array_prestation[$index][1] = $obj_prest_type->affiche_bulletin;
                                  $array_prestation[$index][2] = $value*$salaire_brut_cotisable/100;
                                  $array_prestation[$index][3] = $taux_p[$index]*$salaire_brut_cotisable/100;
                                  $array_prestation[$index][4] = $value;
                                  $array_prestation[$index][5] = $taux_p[$index];
                                  $array_prestation[$index][6] = $obj_prest_type->code;

                                  $index ++;
                                  if(!in_array($obj_prest_type->fk_organisme, $id_organisme)){
                                    $retenu_prest_empl += apres_virgule($db, $id_societe, $value*$salaire_brut_cotisable/100, 2);
                                    $retenu_prest_patro += apres_virgule($db, $id_societe, $taux_p[$index]*$salaire_brut_cotisable/100, 2);
                                    if($obj_prest_type->rowid != 6)
                                      $inps += $value*$salaire_brut_cotisable/100; 
                                  }
                              }
                              
                              //les taxes qui ont comme barème : barème cotisation
                            $index = 0;
                            $global_taxe = salarie_taxe2($db, $obj_salarie->rowid, $id_convention);
                            $taxe = $global_taxe[1];
                            $taux_t = $global_taxe[0];
                            foreach ($taxe as $key => $value) {
                              $type_taxe = "SELECT rowid, libelle, fk_organisme, affiche_bulletin FROM ".MAIN_DB_PREFIX."type_taxe WHERE rowid=".$key;
                                $result_type_taxe = $db->query($type_taxe);
                                $obj_taxe_type = $db->fetch_object($result_type_taxe);

                                $array_taxe[$index][0] = $key;
                                $array_taxe[$index][1] = $obj_taxe_type->affiche_bulletin;
                                $array_taxe[$index][2] = $value*$salaire_brut/100;
                                $array_taxe[$index][3] = $taux_t[$index]*$salaire_brut/100;
                                $array_taxe[$index][4] = $value;
                                $array_taxe[$index][5] = $taux_t[$index];
                                $array_taxe[$index][6] = $obj_taxe_type->libelle;

                                $index ++;
                            }

                              //A par amo les autres detail de l'INPS ne sont pas soumis aux impôt
                              $salaire_brut_imposable -= $inps;
                              //tratement de l'its
                              $its = its_salarie($db, "", $salaire_brut_imposable, $obj_verif->situation_familiale, $obj_verif->nombre_enfant, $obj_verif->nombre_enfant_hand);

                              //print $obj_verif->nom."  ".$obj_verif->nom." =".$salaire_brut_imposable." BC=".$salaire_brut_cotisable." SN=".$salaire_net." R=".$retenu."<br>";

                              $retenu_taxe = $its[2];

                              $retenu = $retenu_prest_empl + $retenu_taxe;
                              //calcul du salaire net
                              $salaire_net = $salaire_brut - $retenu_prest_empl - $retenu_taxe;

                          $sql_bulletin = 'Insert into '.MAIN_DB_PREFIX.'bulletin_bonus (id_bull, nom_bonus, nom, prenom, fk_salarie, matricule, situation_familiale, nombre_enfant, nombre_enfant_hand, categorie
                          , echelon, contrat, diplome, type_salarie, fonction, date_embauche, sexe, pays, ville, addresse, tel, email, annee, mois, salaire_brut, salaire_brut_cotisable,
                          salaire_brut_imposable, net_payer, fk_societe, nom_societe, logo_societe, nom_convention,inps,amo,banque,compte,montant,pourcentage, base) 
                          VALUES("'.$id_bull.'","'.$nom_bonus.'","'.$obj_verif->nom.'","'.$obj_verif->prenom.'",'.$obj_verif->fk_salarie.',"'.$obj_verif->matricule.'","'.$obj_verif->situation_familiale.'",'.$obj_verif->nombre_enfant.','.$obj_verif->nombre_enfant_hand.',
                          "'.$obj_verif->categorie.'","'.$obj_verif->echelon.'","'.$obj_verif->contrat.'","'.$obj_verif->diplome.'","'.$obj_verif->type_salarie.'","'.$obj_verif->fonction.'","'.$obj_verif->date_embauche.'",
                          "'.$obj_verif->sexe.'","'.$obj_verif->pays.'","'.$obj_verif->ville.'","'.$obj_verif->addresse.'","'.$obj_verif->tel.'","'.$obj_verif->email.'",
                          '.$annee.','.$mois.',"'.round($salaire_brut, 2).'","'.round($salaire_brut_cotisable, 2).'","'.round($salaire_brut_imposable, 2).'","'.round($salaire_net).'",'.$obj_verif->fk_societe.',"'.$obj_verif->nom_societe.'","'.$obj_verif->logo_societe.'",
                          "'.$obj_verif->nom_convention.'","'.$obj_verif->inps.'","'.$obj_verif->amo.'","'.$obj_verif->banque.'","'.$obj_verif->compte.'","'.$montant_fixe.'","'.$montant_pourcentage.'","'.$base.'")';

                          $res_bulletin = $db->query($sql_bulletin);
                          if($res_bulletin){
                            $sql_verif_bonus = "SELECT rowid FROM ".MAIN_DB_PREFIX."bulletin_bonus WHERE fk_salarie=".$obj_verif->fk_salarie." AND annee=".$annee." AND mois=".$mois;
                            $res_verif_bonus = $db->query($sql_verif_bonus);
                            $obj_last = $db->fetch_object($res_verif_bonus);
                            $rowid_bulletin = $obj_last->rowid;

                        
                            //insertion dans la table bulletin taxe
                            if($rowid_bulletin){
                              $fk_taxe = 1;
                              $montant = $its[2];
                              $libelle = $its[3];
                              $affiche_bulletin = "Oui";
                              $sql_bulletin = 'Insert into '.MAIN_DB_PREFIX.'bulletin_bonus_taxe (fk_bulletin, fk_taxe, libelle, taux, montant, affiche_bulletin)';
                              $sql_bulletin .= ' VALUES('.$rowid_bulletin.','.$fk_taxe.',"'.$libelle.'","'.round($its[0], 2).'","'.round($montant).'","'.$affiche_bulletin.'")';
                              $res_bulletin = $db->query($sql_bulletin);
                            }

                            //CFE et TL
                            for ($g=0; $g < count($array_taxe); $g++) {
                              $fk_taxe = $array_taxe[$g][0];
                              $affiche_bulletin = $array_taxe[$g][1];
                              $montant_employe = $array_taxe[$g][2]?:0;
                              $montant_employeur = $array_taxe[$g][3]?:0;
                              $taux_employe = $array_taxe[$g][4]?:0;
                              $taux_employeur = $array_taxe[$g][5]?:0;
                              $libelle = $array_taxe[$g][6];
                              //insertion dans la table bulletin cotisations
                              $sql_bulletin = 'Insert into '.MAIN_DB_PREFIX.'bulletin_bonus_taxe2 (fk_bulletin, fk_taxe, libelle, taux_employe, taux_employeur, montant_employe, montant_employeur, affiche_bulletin)';
                              $sql_bulletin .= ' VALUES('.$rowid_bulletin.','.$fk_taxe.',"'.$libelle.'","'.$taux_employe.'","'.$taux_employeur.'","'.round($montant_employe).'","'.round($montant_employeur).'","'.$affiche_bulletin.'")';
                              $res_bulletin = $db->query($sql_bulletin);
                              //print $sql_bulletin;
                            }

                            for ($g=0; $g < count($array_prestation); $g++) { 
                              $fk_cotisation = $array_prestation[$g][0];
                              $affiche_bulletin = $array_prestation[$g][1];
                              $montant_employe = $array_prestation[$g][2]?:0;
                              $montant_employeur = $array_prestation[$g][3]?:0;
                              $taux_employe = $array_prestation[$g][4]?:0;
                              $taux_employeur = $array_prestation[$g][5]?:0;
                              $libelle = $array_prestation[$g][6];
                              //insertion dans la table bulletin cotisations
                              $sql_bulletin = 'Insert into '.MAIN_DB_PREFIX.'bulletin_bonus_cotisation (fk_bulletin, fk_cotisation, libelle, taux_employe, taux_employeur, montant_employe, montant_employeur, affiche_bulletin)';
                              $sql_bulletin .= ' VALUES('.$rowid_bulletin.','.$fk_cotisation.',"'.$libelle.'","'.$taux_employe.'","'.$taux_employeur.'","'.round($montant_employe).'","'.round($montant_employeur).'","'.$affiche_bulletin.'")';
                              $res_bulletin = $db->query($sql_bulletin);
                            }

                            for ($g=0; $g < count($nom_organisme); $g++) { 
                              $sql_bulletin = 'Insert into '.MAIN_DB_PREFIX.'bulletin_bonus_organisme (fk_bulletin, fk_organisme, nom_organisme, pourcentage, montant_employe, montant_employeur)';
                              $sql_bulletin .= ' VALUES('.$rowid_bulletin.','.$id_organisme[$g].',"'.$nom_organisme[$g].'","'.$pourcentage_org[$g].'","'.round($montant_org_sal[$g]).'","'.round($montant_org_patro[$g]).'")';
                              $res_bulletin = $db->query($sql_bulletin);
                      
                            }
                          }
                        }
                        $a ++;
                      }
                      if($res_bulletin){
                        $message = 'Complement de salaire "'.$nom_bonus.'" généré et </br>';
                        $message .= 'lié au mois de '.$mois_tab[($mois-1)].'-'.$annee;

                        $sql_select = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$user->id;
                        $obj = $db->fetch_object($db->query($sql_select));

                        $action_effectue = "Génération des complements de Salaire ".$mois_tab[$mois-1]." ".$annee." de la société ".$obj_soc->nom;
                        $sql_log = 'INSERT INTO '.MAIN_DB_PREFIX.'log (fk_user, nom, prenom, quand, action_effectue, object_concerne)';
                        $sql_log .= ' VALUES('.$user->id.', "'.$obj->lastname.'","'.$obj->firstname.'",now(),"'.$action_effectue.'","Complements salaires")';
                        $db->query($sql_log);
                      } 
                    }else $message = 'Bulletin non généré alors impossible de generer le bulletin bonus';
              }
              $action = "annee_rechercher";
        }

        if($action == 'save_specialbonus'){

          $mois = GETPOST("mois", "int");
              $annee = GETPOST("annee", "int");
              $nom_bonus = GETPOST("libelle", "alpha");

          if(empty(GETPOST("libelle", "alpha")))
              $message = 'Veuillez définir le champ "LIBELLE"<br>';
          if(empty($message)){

            //Préparation de la base de donnée
            $sql_verif = "SELECT rowid FROM ".MAIN_DB_PREFIX."bulletin_bonus WHERE annee=".$annee." AND mois=".$mois." AND fk_societe=".$id_societe;
            $res_verif = $db->query($sql_verif);
            if($res_verif){
                              
              $d = 0;
              $dnum = $db->num_rows($res_verif);
              while ($d < $dnum) {
                                  
                $obj_verif = $db->fetch_object($res_verif);

                //suppression
                $sql_del = "DELETE FROM ".MAIN_DB_PREFIX."bulletin_bonus_prime WHERE fk_bulletin=".$obj_verif->rowid;
                $res_del = $db->query($sql_del);

                $sql_del = "DELETE FROM ".MAIN_DB_PREFIX."bulletin_bonus_prime_exceptionnelle WHERE fk_bulletin=".$obj_verif->rowid;
                $res_del = $db->query($sql_del);

                $sql_del = "DELETE FROM ".MAIN_DB_PREFIX."bulletin_bonus_indemnite WHERE fk_bulletin=".$obj_verif->rowid;
                $res_del = $db->query($sql_del);

                $sql_del = "DELETE FROM ".MAIN_DB_PREFIX."bulletin_bonus_taxe WHERE fk_bulletin=".$obj_verif->rowid;
                $res_del = $db->query($sql_del);

                $sql_del = "DELETE FROM ".MAIN_DB_PREFIX."bulletin_bonus_taxe2 WHERE fk_bulletin=".$obj_verif->rowid;
                $res_del = $db->query($sql_del);

                $sql_del = "DELETE FROM ".MAIN_DB_PREFIX."bulletin_bonus_cotisation WHERE fk_bulletin=".$obj_verif->rowid;
                $res_del = $db->query($sql_del);

                $sql_del = "DELETE FROM ".MAIN_DB_PREFIX."bulletin_bonus_anciennete WHERE fk_bulletin=".$obj_verif->rowid;
                $res_del = $db->query($sql_del);

                $sql_del = "DELETE FROM ".MAIN_DB_PREFIX."bulletin_bonus_organisme WHERE fk_bulletin=".$obj_verif->rowid;
                $res_del = $db->query($sql_del);

                $sql_del = "DELETE FROM ".MAIN_DB_PREFIX."bulletin_bonus_heure_sup WHERE fk_bulletin=".$obj_verif->rowid;
                $res_del = $db->query($sql_del);

                $sql_del = "DELETE FROM ".MAIN_DB_PREFIX."bulletin_bonus_avance WHERE fk_bulletin=".$obj_verif->rowid;
                $res_del = $db->query($sql_del);

                $sql_del = "DELETE FROM ".MAIN_DB_PREFIX."bulletin_bonus WHERE rowid=".$obj_verif->rowid;
                $res_del = $db->query($sql_del);


                $d ++;
              }
            }
            //Insertion
              $sql_verif = "SELECT * FROM ".MAIN_DB_PREFIX."bulletin WHERE annee=".$annee." AND mois=".$mois." AND fk_societe=".$id_societe;
                    $res_verif = $db->query($sql_verif);
                    if($res_verif){
                      $a = 0;
                      $num = $db->num_rows($res_verif);
                      while ($a < $num) {
                        $obj_verif = $db->fetch_object($res_verif);
                        $name = 'salarie'.$obj_verif->fk_salarie;
                        $fk_sal = explode('salarie', $name)[1]; //explode('salarie', $name)[0] = 'salarie'
                          //Calcu et Enregistrement dans le bulletin Bonus                 
                        if(!empty(GETPOST($name,'int'))){
                          //Calcul
                          $salaire_brut = 0;
                            $salaire_brut_imposable = 0;
                            $salaire_brut_cotisable = 0;
                            $base = 0;
                              $montant_fixe = GETPOST($name,'int');
                              $base = $montant_fixe;
                              $salaire_brut = $montant_fixe;
                              $salaire_brut_imposable = $salaire_brut;
                              $salaire_brut_cotisable = $salaire_brut;
                              $montant_pourcentage = "100";
                          
                          $salaire_net = 0;
                          $retenu_prest_empl = 0;
                          $retenu_prest_patro = 0;
                          $retenu_taxe = 0;
                          $retenu = 0;
                          $inps = 0;

                          $old_fk_orga = 0;
                          $nom_organisme = array();
                          $id_organisme = array();
                          $montant_org_sal = array();
                          $montant_org_patro = array();
                          $pourcentage_org = array();

                          $index = 0;
                          $global_cotis = salarie_prestation_organisme($db, $obj_verif->fk_salarie, $id_convention);
                          $cotis = $global_cotis[1];
                          $taux_p = $global_cotis[0];
                          foreach ($cotis as $key => $value) {
                            $type_prest = "SELECT * FROM ".MAIN_DB_PREFIX."type_prestation WHERE rowid=".$key;
                            $result_type_prest = $db->query($type_prest);
                            $obj_prest_type = $db->fetch_object($result_type_prest);

                            if($obj_prest_type->fk_organisme != $old_fk_orga){
                              $old_fk_orga = $obj_prest_type->fk_organisme;
                              $organisme = "SELECT rowid, nom_organisme FROM ".MAIN_DB_PREFIX."organisme WHERE rowid=".$old_fk_orga;
                              $result_organisme = $db->query($organisme);
                              $id_organisme[] = $old_fk_orga;
                              $obj_organisme = $db->fetch_object($result_organisme);
                              $nom_organisme[] = $obj_organisme->nom_organisme;
                              $montant_org_sal[] = $value*$salaire_brut_cotisable/100;
                              $montant_org_patro[] = $taux_p[$index]*$salaire_brut_cotisable/100;
                              $pourcentage_org[] = $value;

                              $retenu_prest_empl += $value*$salaire_brut_cotisable/100;
                              $retenu_prest_patro += $taux_p[$index]*$salaire_brut_cotisable/100;
                            }else{
                              $retenu_prest_empl += $value*$salaire_brut_cotisable/100;
                              $retenu_prest_patro += $taux_p[$index]*$salaire_brut_cotisable/100;

                              $montant_org_sal[(count($montant_org_sal) - 1)] += $value*$salaire_brut_cotisable/100;
                              $montant_org_patro[(count($montant_org_patro) - 1)] += $taux_p[$index]*$salaire_brut_cotisable/100;
                              $pourcentage_org[count($pourcentage_org)-1] += $value;
                            }
                            
                            if($obj_prest_type->rowid != 6)
                              $inps += $value*$salaire_brut_cotisable/100;

                            $index ++;
                          }

                            //les prestations à afficher sur le bulletin
                            $index = 0;
                              $global_cotis = salarie_prestation($db, $obj_verif->fk_salarie, $id_convention);
                              $cotis = $global_cotis[1];
                              $taux_p = $global_cotis[0];
                              foreach ($cotis as $key => $value) {
                                $type_prest = "SELECT rowid, fk_organisme, code, affiche_bulletin FROM ".MAIN_DB_PREFIX."type_prestation WHERE rowid=".$key;
                                  $result_type_prest = $db->query($type_prest);
                                  $obj_prest_type = $db->fetch_object($result_type_prest);
                            
                                  $array_prestation[$index][0] = $key;
                                  $array_prestation[$index][1] = $obj_prest_type->affiche_bulletin;
                                  $array_prestation[$index][2] = $value*$salaire_brut_cotisable/100;
                                  $array_prestation[$index][3] = $taux_p[$index]*$salaire_brut_cotisable/100;
                                  $array_prestation[$index][4] = $value;
                                  $array_prestation[$index][5] = $taux_p[$index];
                                  $array_prestation[$index][6] = $obj_prest_type->code;

                                  $index ++;
                                  if(!in_array($obj_prest_type->fk_organisme, $id_organisme)){
                                    $retenu_prest_empl += $value*$salaire_brut_cotisable/100;
                                    $retenu_prest_patro += $taux_p[$index]*$salaire_brut_cotisable/100;
                                    if($obj_prest_type->rowid != 6)
                                      $inps += $value*$salaire_brut_cotisable/100; 
                                  }
                              }
                              
                              //les taxes qui ont comme barème : barème cotisation
                            $index = 0;
                            $global_taxe = salarie_taxe2($db, $obj_salarie->rowid, $id_convention);
                            $taxe = $global_taxe[1];
                            $taux_t = $global_taxe[0];
                            foreach ($taxe as $key => $value) {
                              $type_taxe = "SELECT rowid, libelle, fk_organisme, affiche_bulletin FROM ".MAIN_DB_PREFIX."type_taxe WHERE rowid=".$key;
                                $result_type_taxe = $db->query($type_taxe);
                                $obj_taxe_type = $db->fetch_object($result_type_taxe);

                                $array_taxe[$index][0] = $key;
                                $array_taxe[$index][1] = $obj_taxe_type->affiche_bulletin;
                                $array_taxe[$index][2] = $value*$salaire_brut/100;
                                $array_taxe[$index][3] = $taux_t[$index]*$salaire_brut/100;
                                $array_taxe[$index][4] = $value;
                                $array_taxe[$index][5] = $taux_t[$index];
                                $array_taxe[$index][6] = $obj_taxe_type->libelle;

                                $index ++;
                            }

                              //A par amo les autres detail de l'INPS ne sont pas soumis aux impôt
                              $salaire_brut_imposable -= $inps;
                              //tratement de l'its
                              $its = its_salarie($db, "", $salaire_brut_imposable, $obj_verif->situation_familiale, $obj_verif->nombre_enfant, $obj_verif->nombre_enfant_hand);

                              //print $obj_verif->nom."  ".$obj_verif->nom." =".$salaire_brut_imposable." BC=".$salaire_brut_cotisable." SN=".$salaire_net." R=".$retenu."<br>";

                              $retenu_taxe = $its[2];

                              $retenu = $retenu_prest_empl + $retenu_taxe;
                              //calcul du salaire net
                              $salaire_net = $salaire_brut - $retenu_prest_empl - $retenu_taxe;

                          $sql_bulletin = 'Insert into '.MAIN_DB_PREFIX.'bulletin_bonus (id_bull, nom_bonus, nom, prenom, fk_salarie, matricule, situation_familiale, nombre_enfant, nombre_enfant_hand, categorie
                          , echelon, contrat, diplome, type_salarie, fonction, date_embauche, sexe, pays, ville, addresse, tel, email, annee, mois, salaire_brut, salaire_brut_cotisable,
                          salaire_brut_imposable, net_payer, fk_societe, nom_societe, logo_societe, nom_convention,inps,amo,banque,compte,montant,pourcentage, base) 
                          VALUES("'.$id_bull.'","'.$nom_bonus.'","'.$obj_verif->nom.'","'.$obj_verif->prenom.'",'.$obj_verif->fk_salarie.',"'.$obj_verif->matricule.'","'.$obj_verif->situation_familiale.'",'.$obj_verif->nombre_enfant.','.$obj_verif->nombre_enfant_hand.',
                          "'.$obj_verif->categorie.'","'.$obj_verif->echelon.'","'.$obj_verif->contrat.'","'.$obj_verif->diplome.'","'.$obj_verif->type_salarie.'","'.$obj_verif->fonction.'","'.$obj_verif->date_embauche.'",
                          "'.$obj_verif->sexe.'","'.$obj_verif->pays.'","'.$obj_verif->ville.'","'.$obj_verif->addresse.'","'.$obj_verif->tel.'","'.$obj_verif->email.'",
                          '.$annee.','.$mois.',"'.round($salaire_brut, 2).'","'.round($salaire_brut_cotisable, 2).'","'.round($salaire_brut_imposable, 2).'","'.round($salaire_net).'",'.$obj_verif->fk_societe.',"'.$obj_verif->nom_societe.'","'.$obj_verif->logo_societe.'",
                          "'.$obj_verif->nom_convention.'","'.$obj_verif->inps.'","'.$obj_verif->amo.'","'.$obj_verif->banque.'","'.$obj_verif->compte.'","'.$montant_fixe.'","'.$montant_pourcentage.'","'.$base.'")';

                          $res_bulletin = $db->query($sql_bulletin);
                          if($res_bulletin){
                            $sql_verif_bonus = "SELECT rowid FROM ".MAIN_DB_PREFIX."bulletin_bonus WHERE fk_salarie=".$obj_verif->fk_salarie." AND annee=".$annee." AND mois=".$mois;
                            $res_verif_bonus = $db->query($sql_verif_bonus);
                            $obj_last = $db->fetch_object($res_verif_bonus);
                            $rowid_bulletin = $obj_last->rowid;

                        
                            //insertion dans la table bulletin taxe
                            if($rowid_bulletin){
                              $fk_taxe = 1;
                              $montant = $its[2];
                              $libelle = $its[3];
                              $affiche_bulletin = "Oui";
                              $sql_bulletin = 'Insert into '.MAIN_DB_PREFIX.'bulletin_bonus_taxe (fk_bulletin, fk_taxe, libelle, taux, montant, affiche_bulletin)';
                              $sql_bulletin .= ' VALUES('.$rowid_bulletin.','.$fk_taxe.',"'.$libelle.'","'.round($its[0], 2).'","'.round($montant).'","'.$affiche_bulletin.'")';
                              $res_bulletin = $db->query($sql_bulletin);
                            }

                            //CFE et TL
                            for ($g=0; $g < count($array_taxe); $g++) {
                              $fk_taxe = $array_taxe[$g][0];
                              $affiche_bulletin = $array_taxe[$g][1];
                              $montant_employe = $array_taxe[$g][2]?:0;
                              $montant_employeur = $array_taxe[$g][3]?:0;
                              $taux_employe = $array_taxe[$g][4]?:0;
                              $taux_employeur = $array_taxe[$g][5]?:0;
                              $libelle = $array_taxe[$g][6];
                              //insertion dans la table bulletin cotisations
                              $sql_bulletin = 'Insert into '.MAIN_DB_PREFIX.'bulletin_bonus_taxe2 (fk_bulletin, fk_taxe, libelle, taux_employe, taux_employeur, montant_employe, montant_employeur, affiche_bulletin)';
                              $sql_bulletin .= ' VALUES('.$rowid_bulletin.','.$fk_taxe.',"'.$libelle.'","'.$taux_employe.'","'.$taux_employeur.'","'.round($montant_employe).'","'.round($montant_employeur).'","'.$affiche_bulletin.'")';
                              $res_bulletin = $db->query($sql_bulletin);
                              //print $sql_bulletin;
                            }

                            for ($g=0; $g < count($array_prestation); $g++) { 
                              $fk_cotisation = $array_prestation[$g][0];
                              $affiche_bulletin = $array_prestation[$g][1];
                              $montant_employe = $array_prestation[$g][2]?:0;
                              $montant_employeur = $array_prestation[$g][3]?:0;
                              $taux_employe = $array_prestation[$g][4]?:0;
                              $taux_employeur = $array_prestation[$g][5]?:0;
                              $libelle = $array_prestation[$g][6];
                              //insertion dans la table bulletin cotisations
                              $sql_bulletin = 'Insert into '.MAIN_DB_PREFIX.'bulletin_bonus_cotisation (fk_bulletin, fk_cotisation, libelle, taux_employe, taux_employeur, montant_employe, montant_employeur, affiche_bulletin)';
                              $sql_bulletin .= ' VALUES('.$rowid_bulletin.','.$fk_cotisation.',"'.$libelle.'","'.$taux_employe.'","'.$taux_employeur.'","'.round($montant_employe).'","'.round($montant_employeur).'","'.$affiche_bulletin.'")';
                              $res_bulletin = $db->query($sql_bulletin);
                            }

                            for ($g=0; $g < count($nom_organisme); $g++) { 
                              $sql_bulletin = 'Insert into '.MAIN_DB_PREFIX.'bulletin_bonus_organisme (fk_bulletin, fk_organisme, nom_organisme, pourcentage, montant_employe, montant_employeur)';
                              $sql_bulletin .= ' VALUES('.$rowid_bulletin.','.$id_organisme[$g].',"'.$nom_organisme[$g].'","'.$pourcentage_org[$g].'","'.round($montant_org_sal[$g]).'","'.round($montant_org_patro[$g]).'")';
                              $res_bulletin = $db->query($sql_bulletin);
                      
                            }
                          }
                        }
                        $a ++;
                      }
                      if($res_bulletin){
                        $message = 'Complement de salaire "'.$nom_bonus.'" généré et </br>';
                        $message .= 'lié au mois de '.$mois_tab[($mois-1)].'-'.$annee;

                        $sql_select = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$user->id;
                        $obj = $db->fetch_object($db->query($sql_select));

                        $action_effectue = "Génération des complements de Salaire ".$mois_tab[$mois-1]." ".$annee." de la société ".$obj_soc->nom;
                        $sql_log = 'INSERT INTO '.MAIN_DB_PREFIX.'log (fk_user, nom, prenom, quand, action_effectue, object_concerne)';
                        $sql_log .= ' VALUES('.$user->id.', "'.$obj->lastname.'","'.$obj->firstname.'",now(),"'.$action_effectue.'","Complements salaires")';
                        $db->query($sql_log);
                      }
                      $action = "annee_rechercher"; 
                    }else $message = 'Bulletin non généré alors impossible de generer le bulletin bonus';
                  }else{
                    $action = "special_ajoutbonus";
                  }
        }

          //Confirmation de la Cofiguration du Bonus
          if($action == "ajouterbonus"){
            if(GETPOST("type") == 'fixe'){
              if(empty(GETPOST("montant_fixe", "int")))
                $message = 'Veuillez définir le champ "VALEUR"<br>';
            }else{
              if(empty(GETPOST("montant_pourcentage", "int")))
              $message = 'Veuillez définir le champ "VALEUR"<br>';
            }
            if(empty(GETPOST("libelle", "alpha")))
                $message = 'Veuillez définir le champ "LIBELLE"<br>';

            if(!empty(GETPOST('mois', 'int'))){
              $mois = GETPOST('mois', 'int');
              $annee = GETPOST('annee', 'int');
              $sql_verif = "SELECT rowid FROM ".MAIN_DB_PREFIX."bulletin WHERE fk_societe=".$id_societe." AND annee=".$annee." AND mois=".$mois;
              $res_verif = $db->query($sql_verif);
              if($res_verif)
                $num_all = $db->num_rows($res_verif);
              if($num_all <= 0)
                $message .= 'Les bulletin mois de '.$mois_tab[($mois - 1)].' '.$annee.' n"est pas générer<br>';
            }else $message .= 'Veuillez choisir un "MOIS"<br>';

            if(!empty($message)){
              $action = "ajoutbonus";
            }else{
              $tout_salarie = GETPOST('salarie', 'alpha');
              $titre = 'La configuration du bulletin bonus';
                $url = $_SERVER['PHP_SELF']."?mainmenu=paiementsalaire&leftmenu=societe&id_societe=".$id_societe."&id_convention=".$id_convention."&annee=".$annee."&mois=".$mois;
                
                $array_type = array();
                $fixe_pourc = array();
                if(GETPOST("type", "alpha") == 'fixe'){
                  $array_type = array('label'=> 'type','type'=> 'text', 'size'=>'', 'morecss'=>'', 'moreattr'=>'disabled', 'name'=>'type','value'=>'fixe');
                  $fixe_pourc = array('label'=> 'Montant','type'=> 'text', 'size'=>'', 'morecss'=>'', 'moreattr'=>'disabled', 'name'=>'fixe','value'=>GETPOST("montant_fixe", "int"));
                }else{  
                  $array_type = array('label'=> 'type','type'=> 'text', 'size'=>'', 'morecss'=>'', 'moreattr'=>'disabled', 'name'=>'type','value'=>'pourcentage');
                  $fixe_pourc = array('label'=> '% Pourcentage','type'=> 'text', 'size'=>'', 'morecss'=>'', 'moreattr'=>'disabled', 'name'=>'pourcentage','value'=>GETPOST("montant_pourcentage", "int"));
                
                }

              if($tout_salarie == 'oui'){
                
                  $array = array(
                  array('label'=> 'libelle','type'=> 'text', 'size'=>'', 'morecss'=>'', 'moreattr'=>'disabled', 'name'=>'libelle','value'=>GETPOST("libelle", "alpha")),
                  array('label'=> 'Mois','type'=> 'text', 'size'=>'', 'morecss'=>'', 'moreattr'=>'disabled', 'name'=>'moisinfo','value'=>$mois_tab[($mois-1)]),
                  array('label'=> '','type'=> 'hidden', 'size'=>'', 'morecss'=>'', 'moreattr'=>'disabled', 'name'=>'mois','value'=>$mois),
                  array('label'=> 'Année','type'=> 'text', 'size'=>'', 'morecss'=>'', 'moreattr'=>'disabled', 'name'=>'annee','value'=>$annee),
                  $array_type,
                  $fixe_pourc,
                  array('label'=> 'Pour tous les salariés','type'=> 'text', 'size'=>'', 'morecss'=>'', 'moreattr'=>'disabled', 'name'=>'tout_salarie','value'=>$tout_salarie),

                );
    
                  $formconfirm = $monform->formconfirm(
                      $url, 
                      $titre, 
                      $text, 
                      'ajouterbonusValider', 
                      $array, 
                      '', 
                      1,
                      300,
                      '30%'
                  );
                  print $formconfirm;
                  $action = "annee_rechercher";

              }else{

                $array_type = array();
                $fixe_pourc = array();
                if(GETPOST("type", "alpha") == 'fixe'){
                  $array_type = array('label'=> 'type','type'=> 'text', 'size'=>'', 'morecss'=>'', 'moreattr'=>'disabled', 'name'=>'type','value'=>'fixe');
                  $fixe_pourc = array('label'=> 'Montant','type'=> 'text', 'size'=>'', 'morecss'=>'', 'moreattr'=>'disabled', 'name'=>'fixe','value'=>GETPOST("montant_fixe", "int"));
                }else{  
                  $array_type = array('label'=> 'type','type'=> 'text', 'size'=>'', 'morecss'=>'', 'moreattr'=>'disabled', 'name'=>'type','value'=>'pourcentage');
                  $fixe_pourc = array('label'=> '% Pourcentage','type'=> 'text', 'size'=>'', 'morecss'=>'', 'moreattr'=>'disabled', 'name'=>'pourcentage','value'=>GETPOST("montant_pourcentage", "int"));
                
                }
                  $array = array(
                  array('label'=> 'libelle','type'=> 'text', 'size'=>'', 'morecss'=>'', 'moreattr'=>'disabled', 'name'=>'libelle','value'=>GETPOST("libelle", "alpha")),
                  array('label'=> 'Mois','type'=> 'text', 'size'=>'', 'morecss'=>'', 'moreattr'=>'disabled', 'name'=>'moisinfo','value'=>$mois_tab[($mois-1)]),
                  array('label'=> '','type'=> 'hidden', 'size'=>'', 'morecss'=>'', 'moreattr'=>'disabled', 'name'=>'mois','value'=>$mois),
                  array('label'=> 'Année','type'=> 'text', 'size'=>'', 'morecss'=>'', 'moreattr'=>'disabled', 'name'=>'annee','value'=>$annee),
                  $array_type,
                  $fixe_pourc,
                  array('label'=> 'Pour tous les salariés','type'=> 'text', 'size'=>'', 'morecss'=>'', 'moreattr'=>'disabled', 'name'=>'tout_salarie','value'=>$tout_salarie),
                  );
    
                  $sql = "SELECT * FROM ".MAIN_DB_PREFIX."bulletin WHERE annee=".$annee." AND mois=".$mois." AND fk_societe=".$id_societe;
                  $result = $db->query($sql);
                  if($result){
                    $a = 0;
                    $num = $db->num_rows($result);
                    $titre = 'Veuillez cocher les Salariés ('.$num.')';
                    while ($a < $num) {
                      $obj_sal = $db->fetch_object($result);
                      $nom_p = $obj_sal->prenom." ".$obj_sal->nom;
                      $name = 'salarie'.$obj_sal->fk_salarie;
                      $valeur = $obj_sal->rowid;
                      if($a == 0)
                        $salarie = array('label'=> $nom_p,'type'=> 'checkbox', 'size'=>'', 'morecss'=>'', 'moreattr'=>'', 'name'=>$name,'value'=>1);
                      else $salarie = array('label'=> $nom_p,'type'=> 'checkbox', 'size'=>'', 'morecss'=>'', 'moreattr'=>'', 'name'=>$name,'value'=>0);
                      $array []= $salarie;
                      $a ++;
                    }
                  }else $titre = 'Aucun salariés ('.$num.')';

                  $formconfirm = $monform->formconfirm(
                      $url, 
                      $titre, 
                      $text, 
                      'ajouterbonusValider', 
                      $array, 
                      '', 
                      1,
                      400,
                      '40%'
                  );
                  print $formconfirm;
                  $action = "annee_rechercher";
                }
              }
          }

          //formulaire d'ajout Bonus
             if($action == "ajoutbonus"){
                $titre = "Veuillez Configurer";
                $mois = GETPOST("mois", "int");
                $annee = GETPOST("annee", "int");
                $url = $_SERVER['PHP_SELF']."?mainmenu=paiementsalaire&leftmenu=societe&id_societe=".$id_societe."&id_convention=".$id_convention."&annee=".$annee."&mois=".$mois;

                //formulaire
                print ' <form name="ajout" method="POST" action="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=salarie&id='.$fk_user.'&fk_salarie='.$fk_salarie.'&id_convention='.$id_convention.'&id_societe='.$id_societe.'" enctype="multipart/form-data">';
                print '<input type="hidden" name="token" value="'.newToken().'">';
                print '<input type="hidden" name="action" value="ajouterbonus">';

                print "<table>";
                print "<tr><td>Libelle du bonus : </td><td><input type='text' name='libelle' placeholder='Bonus ".$mois_tab[($mois-1)]." ".$annee."' autofocus ></td></tr>";
                print "<tr><td>Reference en mois : </td>";
                print "<td><select name='mois'>";
                    print "<option value='".($mois)."' selected>".$mois_tab[$mois-1]."</option>";
                
                print "</select> 
                <select name='annee'>

                  <option value='".($annee)."'>".$annee."</option>
                </select></td></tr>";

                print "<tr><td>Type de valeur : </td>";
                print "<td><select name='type' id='type'>";
                print "<option value='pourcentage' ".(GETPOST('type', 'alpha')=='pourcentage'?'selected':'').">Pourcentage</option>";
                print "<option value='fixe' ".(GETPOST('type', 'alpha')=='fixe'?'selected':'').">Montant Fixe</option>";
                print "</select></td></tr>";
                print "<tr><td>valeur : </td><td><div id='montant_pourcentage'><input type='number' name='montant_pourcentage' value='".GETPOST("montant_pourcentage", "float")."' min='10' max='100'> %</div>
                       <input type='text' value='".GETPOST("montant_fixe", "int")."' name='montant_fixe' id='montant_fixe'>
                </td></tr>";

                print "<tr><td>Tous les salariés : </td>";
                print "<td><select name='salarie' id='type'>";
                print "<option value='oui' ".(GETPOST('salarie', "alpha")=='oui'?'selected':'').">Oui</option>";
                print "<option value='non' ".(GETPOST('salarie', "alpha")=='non'?'selected':'').">Non</option>";
                print "</select></td></tr>";

                print '<tr>';
                print '<td style=" padding-right: 30px; padding-bottom: 30px"></td><td style="padding-top: 30px; width: 300px;"><input onclick="MonSubmitForm()" class="button" type="submit" value="Enregistrer">';
                print'</form>';
                print '<a href="'.$url.'" class="button">Annuler</a></td></tr>';
                print '</table>';
                
                //Javascript
                print '<script type="text/javascript">
                var type = document.getElementById("type");
                var montant_fixe = document.getElementById("montant_fixe");
                var montant_pourcentage = document.getElementById("montant_pourcentage");
               
                if(type.value == "fixe"){
                  montant_pourcentage.style.display = "none";
                  montant_fixe.style.display = "inline";
                }else{
                  montant_pourcentage.style.display = "inline";
                  montant_fixe.style.display = "none";

                }
                type.addEventListener("change", function () {
                  if(type.value == "fixe"){
                    montant_pourcentage.style.display = "none";
                    montant_fixe.style.display = "inline";
                  }else{
                    montant_pourcentage.style.display = "inline";
                    montant_fixe.style.display = "none";
      
                  }
                },
                false,
                );
                </script>';

            }elseif($action == "special_ajoutbonus"){
                
                $titre = "<h3><mark>Mettez le montant uniquement pour les salariés concernés<mark></h3>";
                print $titre;
                $mois = GETPOST("mois", "int");
                $annee = GETPOST("annee", "int");
                $url = $_SERVER['PHP_SELF']."?mainmenu=paiementsalaire&leftmenu=societe&id_societe=".$id_societe."&id_convention=".$id_convention."&annee=".$annee."&mois=".$mois;

                //formulaire
                print ' <form name="ajout" method="POST" action="'.$url.'">';
                print '<input type="hidden" name="token" value="'.newToken().'">';
                print '<input type="hidden" name="action" value="save_specialbonus">';

                print "<table>";
                print "<tr class='liste_titre'><td>Libelle du bonus : </td><td><input type='text' name='libelle' placeholder='Bonus ".$mois_tab[($mois-1)]." ".$annee."' autofocus ></td></tr>";
                print "<tr class='liste_titre'><td>Reference en mois : </td>";
                print "<td><select name='mois'>";
                    print "<option value='".($mois)."' selected>".$mois_tab[$mois-1]."</option>";
                
                print "</select> 
                <select name='annee'>

                  <option value='".($annee)."'>".$annee."</option>
                </select></td></tr>";

                $sql = "SELECT * FROM ".MAIN_DB_PREFIX."bulletin WHERE annee=".$annee." AND mois=".$mois." AND fk_societe=".$id_societe;

                  $result = $db->query($sql);
                  if($result){
                    $a = 0;
                    $num = $db->num_rows($result);
                    $titre = 'Mettez le montant pour les Salariés ('.$num.') concernés';
                    $array [] = $salarie = array('label'=> 'Nom Salarié','type'=> 'text', 'size'=>'', 'morecss'=>'', 'moreattr'=>'', 'name'=>'','value'=>'Montant');
                    while ($a < $num) {
                      $obj_sal = $db->fetch_object($result);
                      $nom_p = $obj_sal->prenom." ".$obj_sal->nom;
                      $name = 'salarie'.$obj_sal->fk_salarie;
                      if($a == 0){
                        print '<tr><td><label>'.$nom_p.'</label></td><td><input type="text" placeholder="Montant" name="'.$name.'" value="'.GETPOST($name, "int").'"></td></tr>';
                      }else print '<tr><td><label>'.$nom_p.'</label></td><td><input type="text" placeholder="Montant" name="'.$name.'" value="'.GETPOST($name, "int").'"></td></tr>';

                      $a ++;
                    }
                  }
                  print '<td style=" padding-right: 30px; padding-bottom: 30px"></td><td style="padding-top: 30px; width: 300px;"><input onclick="MonSubmitForm()" class="button" type="submit" value="Enregistrer">';
                print'</form>';
                print '<a href="'.$url.'" class="button">Annuler</a></td></tr>';
                print '</table>';

            
            }else{
              //print_barre_liste("", $page, $_SERVER["PHP_SELF"], "", "", "", "", "", "", 'bill', 0, dolGetButtonTitle("Créer un Bonus", '', 'fa fa-plus-circle', $url , '', 1), '', 0, 0, 0, 1);

              //Gestion des année et des dates pour l'historique => Gestion des actions recherche année
			if($action == 'annee_rechercher'){
        $url = $_SERVER['PHP_SELF']."?mainmenu=paiementsalaire&leftmenu=societe&id_societe=".$id_societe."&id_convention=".$id_convention."&action=creation_bonus";
				$annee_rechercher = GETPOST("annee_rechercher", "int");
				$annee_courant = (int) date("Y");
				if(empty($annee_rechercher))
					$annee_rechercher = (int) date("Y");
				$mois_courant = (int) date("m");

				if($annee_rechercher != $annee_courant)
					print "<h2 style='align:center; display: inline'>Historique de l'année ".$annee_rechercher."!</h2>";
				else print "<h2 style='align:center;display: inline'>Bulletins du ".$annee_rechercher."!</h2>";
				print "<div style='float: right; display: inline''>";
				print '<form name="add" method="POST" action="'.$_SERVER['PHP_SELF'].'?mainmenu=paiementsalaire&leftmenu=societe&id_societe='.$id_societe.'&id_convention='.$id_convention.'">';
					print '<input type="hidden" name="token" value="'.newToken().'">';
					print '<input type="hidden" name="action" value="annee_rechercher">';

					print "<select name='annee_rechercher'>";
					$sql_verif = "SELECT DISTINCT annee FROM ".MAIN_DB_PREFIX."bulletin WHERE fk_societe=".$id_societe;
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
				print "<table class='tagtable liste'>";
					print "<thead>";
					print "<tr class='liste_titre'><th rowspan='2'>Mois</th>";	
					print "<th rowspan='2'>Nb salarié</th>";
					print "<th rowspan='2'>Masse salariale brute</th>";
					print "<th rowspan='2'>Masse salariale net</th>";
					print "<th rowspan='2'>Total I.T.S</th>";
					print "<th colspan='2' align='center'>Total Cotisation</th>";
					print "<th rowspan='2' align='center' >Opérations</tr>";
					print "<tr><th>Employé</th><th>Employeur</th></tr>";
					print "</thead>";
        
				if($annee_courant == $annee_rechercher){
					print "<tbody>";

					for ($i=0; $i < count($mois_tab); $i++) {
						print "<tr class='impair'>";
								$sql_verif = "SELECT rowid, nom_bonus FROM ".MAIN_DB_PREFIX."bulletin_bonus WHERE annee=".$annee_rechercher." AND mois=".($i + 1)." AND fk_societe=".$id_societe;
								$res_verif = $db->query($sql_verif);
								if($res_verif){
									$nb_salarie = $db->num_rows($res_verif);

									if($nb_salarie > 0){
                     $j = 0;

											$obj_verif = $db->fetch_object($res_verif);
											$sql_som_salaire = "SELECT SUM(salaire_brut) as sal_brut, SUM(net_payer) as sal_net FROM ".MAIN_DB_PREFIX."bulletin_bonus WHERE annee=".$annee_rechercher." AND mois=".($i + 1)." AND fk_societe=".$id_societe;
											$res_som_salaire  = $db->query($sql_som_salaire);
											$obj_som_salaire = $db->fetch_object($res_som_salaire);

											//$total += $obj_som_salaire->sal_brut + $obj_som_salaire->sal_net;

                        if(($obj_verif->rowid)){
                          $total = 0;
                          $a = 0;
                          $somme_taxe = 0;
                          $somme_cotisation = 0;
                          $somme_cotisation_employe = 0;
                          $somme_cotisation_employeur = 0;
                          $sql_id_bulletin = "SELECT rowid FROM ".MAIN_DB_PREFIX."bulletin_bonus WHERE annee=".$annee_rechercher." AND mois=".($i + 1)." AND fk_societe=".$id_societe;
                          $res_id_bulletin  = $db->query($sql_id_bulletin);
                          $num_k = $db->num_rows($res_id_bulletin);
                          while ($a < $num_k){
                            $obj_id_bulletin = $db->fetch_object($res_id_bulletin);
                            $sql_som_taxe = "SELECT SUM(montant) as montant FROM ".MAIN_DB_PREFIX."bulletin_bonus_taxe WHERE fk_bulletin=".$obj_id_bulletin->rowid;
                            $res_som_taxe  = $db->query($sql_som_taxe);
                            if($res_som_taxe){
                              $obj_som_taxe = $db->fetch_object($res_som_taxe);
                              $somme_taxe += $obj_som_taxe->montant;
                            }

                            $sql_som_cotisation = "SELECT SUM(montant_employe) as som_empl, SUM(montant_employeur) as som_patro FROM ".MAIN_DB_PREFIX."bulletin_bonus_cotisation WHERE fk_bulletin=".$obj_id_bulletin->rowid;
                            $res_som_cotisation  = $db->query($sql_som_cotisation);
                            if($res_som_cotisation){
                              $obj_som_cotisation = $db->fetch_object($res_som_cotisation);
                              $somme_cotisation_employe += $obj_som_cotisation->som_empl;
                              $somme_cotisation_employeur += $obj_som_cotisation->som_patro;
                            }
                            $a ++;
                          }
                          $db->free($res_id_bulletin);
                          $somme_cotisation += $somme_cotisation_employe + $somme_cotisation_employeur;
                          $total += $somme_taxe + $somme_cotisation;
                          print "<td style='padding: 0px' ><b>".$obj_verif->nom_bonus." ".info_admin("Moi de ".$mois_tab[$i], 1)."</b></td>";
                          print "<td>".$nb_salarie."</td><td>".apres_virgule($db, $id_societe, $obj_som_salaire->sal_brut?:0, 2)."</td><td>".apres_virgule($db, $id_societe, $obj_som_salaire->sal_net?:0, 2)."</td><td>".apres_virgule($db, $id_societe, $somme_taxe?:0, 2)."</td><td>".apres_virgule($db, $id_societe, $somme_cotisation_employe, 2)."</td><td>".apres_virgule($db, $id_societe, $somme_cotisation_employeur, 2)."</td>";

                          print "<td style='padding: 0px' ><span class='fa fa-plus' style='color: gray'></a>&nbsp;&nbsp;&nbsp;";
                          if($user->rights->paiementsalaire->salarie->voirDocument)
                            print "<a style='text-decoration : none;' title='Voir' href='".$_SERVER['PHP_SELF']."?mainmenu=paiementsalaire&leftmenu=societe&id_convention=".$id_convention."&id_societe=".$id_societe."&action=voir&annee=".$annee_rechercher."&mois=".($i + 1)."'><span class='fa fa-search-plus'></span>&nbsp; &nbsp;</a>&nbsp;
                            <a style='text-decoration : none;' title='Télécharger' href='".$_SERVER['PHP_SELF']."?mainmenu=paiementsalaire&leftmenu=societe&id_convention=".$id_convention."&id_societe=".$id_societe."&action=telecharger&annee=".$annee_rechercher."&mois=".($i + 1)."'><span class='fa fa-download'></span> &nbsp;</a>&nbsp;
                            <a href='./../doc/export_bonus.php?mainmenu=paiementsalaire&leftmenu=societe&id_convention=".$id_convention."&id_societe=".$id_societe."&action=voir&annee=".$annee_rechercher."&mois=".($i + 1)."&nom_soc=".$obj_soc->name."&action=exporter'><span class='file-export'>".img_picto('Exporter', 'logout', 'class="paddingright pictofixedwidth valignmiddle"')."</span></a>&nbsp;&nbsp;
                            <a style='text-decoration : none;' title='Supprimer' href='".$_SERVER['PHP_SELF']."?mainmenu=paiementsalaire&leftmenu=societe&id_convention=".$id_convention."&id_societe=".$id_societe."&action=attente_suppression&annee=".$annee_rechercher."&mois=".($i + 1)."'>".img_delete("", "")."</a>&nbsp;";
                          else 
                            print "<span class='fa fa-search-plus' style='color: gray'></span> &nbsp;&nbsp;&nbsp;
                            <span class='fa fa-download' style='color: gray'> &nbsp;&nbsp;&nbsp;";
                          print "</td>";

                        }else{
                          print "<td style='padding: 0px' ><b>".$mois_tab[$i]."</b></td>";
                          print "<td>0</td><td>".apres_virgule($db, $id_societe, 0, 2)."</td><td>".apres_virgule($db, $id_societe, 0, 2)."</td><td>".apres_virgule($db, $id_societe, 0, 2)."</td><td>".apres_virgule($db, $id_societe, 0, 2)."</td><td>".apres_virgule($db, $id_societe, 0, 2)."</td>";
                          print "<td style='padding: 0px' >";
                          $sql_bull = "SELECT rowid, cloture FROM ".MAIN_DB_PREFIX."bulletin WHERE annee=".$annee_rechercher." AND mois=".($i + 1)." AND fk_societe=".$id_societe;
                          $res_bull = $db->query($sql_bull);
                          if($res_bull){
                            $nb_bull = $db->num_rows($res_bull);
                            if($nb_bull > 0){
                              $obj_b = $db->fetch_object($res_bull);
                              if($obj_b->cloture == 'non'){
                                if($user->rights->paiementsalaire->societe->genererBulletin)
                                  print "<a href='".$url."&mois=".($i +1)."&annee=".($annee_rechercher)."' ><span class='fa fa-plus' style='color: blue'></span></a>&nbsp;&nbsp;&nbsp;";
                                else print "<span class='fa fa-plus' style='color: gray'></a>&nbsp;&nbsp;&nbsp;";

                                print "<span class='fa fa-search-plus' style='color: gray'></span> &nbsp;&nbsp;&nbsp;
                                <span class='fa fa-download' style='color: gray'> &nbsp;&nbsp;&nbsp;";

                                print "</td>";	
                                }else{
                                  print "<span class='fa fa-plus' style='color: gray'>&nbsp;&nbsp;&nbsp;";
                                  print "<span class='fa fa-search-plus' style='color: gray'></span> &nbsp;&nbsp;&nbsp;
                                  <span class='fa fa-download' style='color: gray'> &nbsp;&nbsp;&nbsp;";
                                  print "</td>";
                                }		
                            }else{
                                print "<span class='fa fa-plus' style='color: gray'>&nbsp;&nbsp;&nbsp;";
                                  print "<span class='fa fa-search-plus' style='color: gray'></span> &nbsp;&nbsp;&nbsp;
                                  <span class='fa fa-download' style='color: gray'> &nbsp;&nbsp;&nbsp;";
                                  print "</td>";
                              }				
                          }else{

                            print "<span class='fa fa-plus' style='color: gray'>&nbsp;&nbsp;&nbsp;";
                            print "<span class='fa fa-search-plus' style='color: gray'></span> &nbsp;&nbsp;&nbsp;
                            <span class='fa fa-download' style='color: gray'> &nbsp;&nbsp;&nbsp;";

                              print "</td>";
                        }
                      }
										}else{
                      print "<td style='padding: 0px' ><b>".$mois_tab[$i]."</b></td>";
                      print "<td>0</td><td>".apres_virgule($db, $id_societe, 0, 2)."</td><td>".apres_virgule($db, $id_societe, 0, 2)."</td><td>".apres_virgule($db, $id_societe, 0, 2)."</td><td>".apres_virgule($db, $id_societe, 0, 2)."</td><td>".apres_virgule($db, $id_societe, 0, 2)."</td>";
                      print "<td style='padding: 0px' >";
                      $sql_bull = "SELECT rowid, cloture FROM ".MAIN_DB_PREFIX."bulletin WHERE annee=".$annee_rechercher." AND mois=".($i + 1)." AND fk_societe=".$id_societe;
                      $res_bull = $db->query($sql_bull);
                      if($res_bull){
                        $nb_bull = $db->num_rows($res_bull);
                        $obj_b = $db->fetch_object($res_bull);
                        if($obj_b->cloture == 'non'){
                          if($nb_bull > 0){
                            if($user->rights->paiementsalaire->societe->genererBulletin)
                            print "<a href='".$url."&mois=".($i +1)."&annee=".($annee_rechercher)."' ><span class='fa fa-plus' style='color: blue'></span></a>&nbsp;&nbsp;&nbsp;";
                          else print "<span class='fa fa-plus' style='color: gray'></a>&nbsp;&nbsp;&nbsp;";

                            print "<span class='fa fa-search-plus' style='color: gray'></span> &nbsp;&nbsp;&nbsp;
                            <span class='fa fa-download' style='color: gray'> &nbsp;&nbsp;&nbsp;";

                            print "</td>";	
                          }else{
                            
                            print "<span class='fa fa-plus' style='color: gray'>&nbsp;&nbsp;&nbsp;";

                            print "<span class='fa fa-search-plus' style='color: gray'></span> &nbsp;&nbsp;&nbsp;
                            <span class='fa fa-download' style='color: gray'> &nbsp;&nbsp;&nbsp;";

                            print "</td>";
                          }
                        }else{
                          print "<span class='fa fa-plus' style='color: gray'>&nbsp;&nbsp;&nbsp;";

                            print "<span class='fa fa-search-plus' style='color: gray'></span> &nbsp;&nbsp;&nbsp;
                            <span class='fa fa-download' style='color: gray'> &nbsp;&nbsp;&nbsp;";

                            print "</td>";
                        }					
                      }else{

                        print "<span class='fa fa-plus' style='color: gray'>&nbsp;&nbsp;&nbsp;";

                          print "<span class='fa fa-search-plus' style='color: gray'></span> &nbsp;&nbsp;&nbsp;
                          <span class='fa fa-download' style='color: gray'> &nbsp;&nbsp;&nbsp;";

                          print "</td>";
                    }
                    }
                    print "</tr>";

                }else {
                  print "<tr><td align='center' colspan='8'>Un problème persiste</td></tr>";
                  $i = 12;
                }
										

							

					}
					
				}
					print "</tbody>";
					print "</table>";
			//Gestion de l'action Generer Bulletin
			//--------------------------------------------------------------------------------------------------------------------------------------
			}else if($action == "voir" || $action == "telecharger"){ 
				$annee_rech = GETPOST("annee", 'int');
				$mois_rech = (int) GETPOST("mois", 'int');
				$object_liste = array();
				$limit = GETPOST('limit','int')?:20;
				$arret = GETPOST('arret','int')?:0;
				$nb_page = GETPOST('nbpage','int')?:1;

				$recherche_nom = "";
				$recherche_prenom = "";
				$recherche_nom = GETPOST("recherche_nom", "alpha");
				$recherche_prenom = GETPOST("recherche_prenom", "alpha");
			

				$sql_verif = "SELECT sal.rowid , sal.fk_user, u.firstname, u.lastname, u.rowid as id_user, bul.rowid as id_bulletin, bul.fk_salarie as mat, bul.annee, bul.mois, bul.fk_societe  FROM ".MAIN_DB_PREFIX."salarie as sal";
				$sql_verif .= " LEFT JOIN ".MAIN_DB_PREFIX."bulletin_bonus as bul ON bul.fk_salarie=sal.rowid";
				$sql_verif .= " LEFT JOIN ".MAIN_DB_PREFIX."user as u ON sal.fk_user=u.rowid";
				$sql_verif .= " WHERE bul.annee=".$annee_rech." AND bul.mois=".$mois_rech." AND bul.fk_societe=".$id_societe;

				if(!empty(GETPOST("recherche_nom", "alpha"))){
					$sql_verif .= " AND (u.lastname LIKE '%".GETPOST("recherche_nom", "alpha")."%'";
					$sql_verif .= " OR u.firstname LIKE '%".GETPOST("recherche_nom", "alpha")."%')";

				}
			
				if(!empty(GETPOST("recherche_prenom", "alpha"))){
					$sql_verif .= " AND (u.firstname LIKE '%".GETPOST("recherche_prenom", "alpha")."%'";
					$sql_verif .= " OR u.lastname LIKE '%".GETPOST("recherche_prenom", "alpha")."%')";

				}
				$zero = false;
				$res_verif = $db->query($sql_verif);
				if($res_verif){
					$num = $db->num_rows($res_verif);
					if($num > 0){
						
					
						$a = 0;
						while ($a < $num) {
								
							$object_liste[count($object_liste)] = $db->fetch_object($res_verif);  							
								$a ++;
						}
					}
				//Gestion des action voir et telcharger
				//----------------------------------------------------------------------------------
				$num = count($object_liste) == 0 ? 1 : count($object_liste);
				$sel10 = "";
				$sel25 = "";
				$sel20 = "";
				$sel30 = "";
				$sel50 = "";
				$sel100 = "";
				$sel200 = "";
				$sel500 = "";
				$sel1000 = "";
				$seltout = "";
				if($limit == 5)
					$sel5 = "selected";
				elseif($limit == 10)
					$sel10 = "selected";
				elseif($limit == 15)
					$sel15 = "selected";
				elseif($limit == 20)
					$sel20 = "selected";
				elseif($limit == 30)
					$sel30 = "selected";
				elseif($limit == 50)
					$sel50 = "selected";
				elseif($limit == 100) 
					$sel100 = "selected";
				elseif($limit == 200)
					$sel200 = "selected";
				elseif($limit == 500)
					$sel500 = "selected";
				elseif($limit == 1000)
					$sel1000 = "selected";
				else $seltout = "selected";
			print '<form name="ajouter" method="POST" action="'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenu=societe&id_convention='.$id_convention.'&id_societe='.$id_societe.'&annee='.$annee_rech.'&mois='.$mois_rech.'&action='.$action.'">';
			print '<input type="hidden" name="token" value="'.newToken().'">';
			print '<input type="hidden" name="action" value="'.$action.'">';

			print "<div style='float:right; padding-top: 5px; margin-right:20px;'>";
			print"<select style='padding:10px' name='limit' id='limit' >";
			print "<option value='5' ".$sel5." ><b>5</b></option>
			<option value='10' ".$sel10."><b>10</b></option>
			<option value='15' ".$sel15."><b>15</b></option>
			<option value='20' ".$sel20."><b>20</b></option>
			<option value='30' ".$sel30."><b>30</b></option>
			<option value='50' ".$sel50."><b>50</b></option>
			<option value='100' ".$sel100."><b>100</b></option>
			<option value='200' ".$sel200."><b>200</b></option>
			<option value='500' ".$sel500."><b>500</b></option>
			<option value='1000' ".$sel1000."><b>1000</b></option>
			<option value='tout' ".$seltout."><b>tout</b></option>";
			
			print "</select>";
			if(!empty(GETPOST("limit", "alpha")))
				$limit = $num;

			print "<mark><b>".(GETPOST("nbpage", 'int')?:1)."</b></mark> sur <mark><b>".(((int)($num%$limit))==0?((int)($num/$limit)):((int)($num/$limit)+1))."</b></mark>";
					print '<script type="text/javascript">
					var convention = document.getElementById("limit");
					convention.addEventListener("change", function () {
						var limit = convention.value;
						window.location.href = "'.$_SERVER["PHP_SELF"].'?mainmenu=paiementsalaire&leftmenusociete&id_societe='.$id_societe.'&id_convention='.$id_convention.'&limit="+limit+"&action='.$action.'&annee='.$annee_rech.'&mois='.$mois_rech.'&recherche_nom='.$recherche_nom.'&recherche_prenom='.$recherche_prenom.'&recherche_fk_salarie='.$recherche_fk_salarie.'&recherche_anciennete='.$recherche_anciennete.'";
					},
					false,
					);
					</script>';
				
				print "</div><br><br>";
				$num = count($object_liste);

        //$bouton = "";
					/*if($action == "telecharger")
						$bouton = "Télécharger pour tous les salariés";
					else $bouton = "Voir pour tous les salariés";
					/*if($id_bull == 1)
						$url = "../doc/bulletin_tout_salarie.php?id_societe=".$id_societe."&id_convention=".$id_convention."&mois=".$mois_rech."&annee=".$annee_rech."&action=".$action;
					elseif($id_bull == 2)
						$url = "../doc/modele_moyen/bulletin_tout_salarie.php?id_societe=".$id_societe."&id_convention=".$id_convention."&mois=".$mois_rech."&annee=".$annee_rech."&action=".$action;
					elseif($id_bull == 3)
						$url = "../doc/modele_avance/bulletin_tout_salarie.php?id_societe=".$id_societe."&id_convention=".$id_convention."&mois=".$mois_rech."&annee=".$annee_rech."&action=".$action;
*/
					print "<h2>Les bulletins de paie du ".$mois_tab[$mois_rech-1]." ".$annee_rech;//."<a target='_blank' href='#' style='float: right;' class='button'>".$bouton."</a>
          print "</h2>";

					//Tableau
					print "<table class='tagtable liste' style='width:100%;'>";
					print "<tr class='liste_titre'><td style='width:25%; padding: 15px; text-align:center;'>Nom<br>
					<input style='padding:10px' type='text' Placeholder='Nom' value='".$recherche_nom."' name='recherche_nom' ></td>

					<td style='width:25%; padding: 15px; text-align:center;'>Prénom <br><input style='padding:10px' type='text' Placeholder='Prenom' value='".$recherche_prenom."' name='recherche_prenom' >
					</td><td style='width:25%; padding: 15px; text-align:center;'>Bulletins (".$num.")<br>
					<input type='submit' class='button' value='Rechercher' >
					</form>
					<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenu=societe&id_societe=".$id_societe."&id_convention=".$id_convention."&mois=".$mois_rech."&annee=".$annee_rech."&action=".$action."' class='button' >Annuler</a></td></tr>";

					print "<tbody>";
					if($num > 0){

							$i = $arret;
							while($i < $num){
								if($action == "telecharger"){
                    $class = "impair";
                    if(($i % 2) == 0)
                      $class = "pair";
									  print '<tr class="'.$class.'" ><td style="text-align:center; padding:0px">'.$object_liste[$i]->lastname.'</><td style="text-align:center; padding:0px">'.$object_liste[$i]->firstname.'</td>';
										print '<td style="text-align:center; padding:0px"><a class="button" target="_blank" href="../doc/bulletin_bonus.php?id_societe='.$id_societe.'&fk_user='.$object_liste[$i]->id_user.'&fk_salarie='.$object_liste[$i]->rowid.'&id_convention='.$id_convention.'&mois='.$mois_rech.'&annee='.$annee_rech.'&action='.$action.'">Télécharger</a></td></tr>';
									
								}else{
									$class = "impair";
                    if(($i % 2) == 0)
                      $class = "pair";
									  print '<tr class="'.$class.'" ><td style="text-align:center; padding:0px">'.$object_liste[$i]->lastname.'</><td style="text-align:center; padding:0px">'.$object_liste[$i]->firstname.'</td>';
										print '<td style="text-align:center; padding:0px"><a class="button" target="_blank" href="../doc/bulletin_bonus.php?id_societe='.$id_societe.'&fk_user='.$object_liste[$i]->id_user.'&fk_salarie='.$object_liste[$i]->rowid.'&id_convention='.$id_convention.'&mois='.$mois_rech.'&annee='.$annee_rech.'&action='.$action.'">Voir</a></td></tr>';
								}
									if($i!= 0 && (($i+1)%$limit) == 0){
										$arret = $i;
										$i = $num;
									}else
										$i ++;
							}
							
		
						
					}
					if(count($object_liste) ==0){
						print "<tr><td colspan='3' align='center'><style='align:center;'>Aucun salarié</td></tr>";
					}
					print "</tbody>";
					print "</table>";
					
				}else print "<h2 style='align:center;'>Pas d'historique pour le ".$mois_tab[$mois_rech-1]."".$annee_rech."!";

				print '<span style="float:right; margin-left: 20px;">';
		$nb = (((int)($num%$limit))==0?((int)($num/$limit)):((int)($num/$limit)+1));
		$page_link = "";
		if($num>$limit){

			if($nb_page!= 1)
				if($nb==0 && 1 < ($nb))
					$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&annee=".$annee."&mois=".$mois."&limit=".$limit."&arret=0&nbpage=1&action=".$action."&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_fk_salarie=".$recherche_fk_salarie."&recherche_anciennete=".$recherche_anciennete."' style='padding: 5px'><b>Debut</b>    </a>&nbsp;&nbsp;";
				else if(1 < ($nb+1))
				$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&annee=".$annee."&mois=".$mois."&limit=".$limit."&arret=0&nbpage=1&action=".$action."&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_fk_salarie=".$recherche_fk_salarie."&recherche_anciennete=".$recherche_anciennete."' style='padding: 5px'><b>Debut</b>    </a>&nbsp;&nbsp;";

			
			if($arret > $limit){

				
				if($nb_page-3>=0)
					$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&annee=".$annee."&mois=".$mois."&limit=".$limit."&arret=".($limit*($nb_page-3))."&nbpage=".($nb_page-2)."&action=".$action."&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_fk_salarie=".$recherche_fk_salarie."&recherche_anciennete=".$recherche_anciennete."' style='padding: 5px'><b>".($nb_page -2)."</b></a>&nbsp;&nbsp;";

				if($nb_page-2>=0)
							$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&annee=".$annee."&mois=".$mois."&limit=".$limit."&arret=".($limit*($nb_page-2))."&nbpage=".($nb_page-1)."&action=".$action."&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_fk_salarie=".$recherche_fk_salarie."&recherche_anciennete=".$recherche_anciennete."' style='padding: 5px'><b>".($nb_page-1)."</b></a>&nbsp;&nbsp;";
				
				
				if($nb_page-1>=0)
						$page_link .= "<a style='background-color: yellow;' href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&annee=".$annee."&mois=".$mois."&limit=".$limit."&arret=".($limit*($nb_page-1))."&nbpage=".($nb_page)."&action=".$action."&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_fk_salarie=".$recherche_fk_salarie."&recherche_anciennete=".$recherche_anciennete."' style='padding: 5px'><b>".($nb_page)."</b></a>&nbsp;&nbsp;";

			

				
					if(	(($nb_page+1) <= ($nb)))
						$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&annee=".$annee."&mois=".$mois."&limit=".$limit."&arret=".($limit*$nb_page)."&nbpage=".($nb_page+1)."&action=".$action."&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_fk_salarie=".$recherche_fk_salarie."&recherche_anciennete=".$recherche_anciennete."' style='padding: 5px'><b>".($nb_page + 1)."</b></a>&nbsp;&nbsp;";

				
					if((($nb_page+2) <= ($nb)))
						$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&annee=".$annee."&mois=".$mois."&limit=".$limit."&arret=".($limit*($nb_page +1))."&nbpage=".($nb_page+2)."&action=".$action."&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_fk_salarie=".$recherche_fk_salarie."&recherche_anciennete=".$recherche_anciennete."' style='padding: 5px'><b>".($nb_page + 2)."</b></a>&nbsp;&nbsp;";
						
					
					if((($nb_page+3) <= ($nb)))
						$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&annee=".$annee."&mois=".$mois."&limit=".$limit."&arret=".($limit*($nb_page+2))."&nbpage=".($nb_page+3)."&action=".$action."&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_fk_salarie=".$recherche_fk_salarie."&recherche_anciennete=".$recherche_anciennete."' style='padding: 5px'><b>".($nb_page + 3)."</b></a>&nbsp;&nbsp;";

						


			}else{

				
					if( 1 <= ($nb))
						
						$page_link .= "<a style='background-color: yellow;' href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&annee=".$annee."&mois=".$mois."&limit=".$limit."&arret=0&nbpage=1&action=".$action."&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_fk_salarie=".$recherche_fk_salarie."&recherche_anciennete=".$recherche_anciennete."' style='padding: 5px'><b>1</b></a>&nbsp;&nbsp;";
				
				
					if(2 <= ($nb))
						
						$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&annee=".$annee."&mois=".$mois."&limit=".$limit."&arret=".$limit."&nbpage=2&action=".$action."&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_fk_salarie=".$recherche_fk_salarie."&recherche_anciennete=".$recherche_anciennete."' style='padding: 5px'><b>2</b></a>&nbsp;&nbsp;";
				
				
					if(3 <= ($nb))
						
						$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&annee=".$annee."&mois=".$mois."&limit=".$limit."&arret=".($limit*2)."&nbpage=3&action=".$action."&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_fk_salarie=".$recherche_fk_salarie."&recherche_anciennete=".$recherche_anciennete."' style='padding: 5px'><b>3</b></a>&nbsp;&nbsp;";
					
					if(4 <= ($nb))
						
						$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&annee=".$annee."&mois=".$mois."&limit=".$limit."&arret=".($limit*3)."&nbpage=4&action=".$action."&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_fk_salarie=".$recherche_fk_salarie."&recherche_anciennete=".$recherche_anciennete."' style='padding: 5px'><b>4</b></a>&nbsp;&nbsp;";

					if(5 <= ($nb))
						
						$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&annee=".$annee."&mois=".$mois."&limit=".$limit."&arret=".($limit*4)."&nbpage=5&action=".$action."&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_fk_salarie=".$recherche_fk_salarie."&recherche_anciennete=".$recherche_anciennete."' style='padding: 5px'><b>5</b></a>&nbsp;&nbsp;";



			}
			if($nb_page != ($nb)  )
					$page_link .= "<a href='".$_SERVER["PHP_SELF"]."?mainmenu=paiementsalaire&leftmenusociete&id_societe=".$id_societe."&id_convention=".$id_convention."&annee=".$annee."&mois=".$mois."&limit=".$limit."&arret=".($limit*($nb-1))."&nbpage=".($nb)."&action=".$action."&recherche_nom=".$recherche_nom."&recherche_prenom=".$recherche_prenom."&recherche_fk_salarie=".$recherche_fk_salarie."&recherche_anciennete=".$recherche_anciennete."' style='padding: 5px'>      <b>Fin</b></a>&nbsp;&nbsp;";


		}
		print $page_link.'</span>';
	}

            }
            
            //Si la société n'a pas de salarié
        }else {
            print "<h2 style='align:center;'>Cette sociétée n'a aucun employé!";
        }
        $db->close();

    }else{
        print "<h2>Veuillez affecter une <b>convention</b> à cette société</h2>";
    }

}else{
	print "<h2 style='align:center;'>Vous n'avez pas la permission de voir cette page!";

}

function apres_virgule($db, $id_societe, $valeur, $decalage){
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
//$confirmation = "Voulez vous gernerer les bulettins de paies pour l ensemble des salariés pour le mois de ".$mois_tab[$mois-1]." ? ce processus peut prendre plusieurs minutes selon le nombre de salarié(e)s.";
	  print "<script>
	  var button_generer = document.getElementById('button_generer');
	  button_generer.addEventListener('click', myFunction);

	  var ancien_button_generer = document.getElementById('ancien_button_generer');
	  ancien_button_generer.addEventListener('click', ancien_myFunction);



	  let mois_table = [' janvier ',' février ',' mars ',' avril ',' mai ',' juin ',' juillet ',' août ',' septembre ',' octobre ',' novembre ',' décembre '];
		function myFunction(){
			var date = new Date;
			var result = confirm('Voulez vous gernerer les bulettins de paies pour l ensemble des salariés pour le mois de '+mois_table[date.getMonth()]+' ? ce processus peut prendre plusieurs minutes selon le nombre de salarié(e)s.');
			if(result)
				button_generer.setAttribute('href', defaut);
			else
				button_generer.setAttribute('href', '#');
			
              
		}//e.preventdefault

		function ancien_myFunction(){
			var date = new Date;
			var result = confirm('Voulez vous modifié les bulletins deu'+mois_table[date.getMonth()-1]+' ? ce processus peut prendre plusieurs minutes selon le nombre de salarié(e)s.');
			if(result)
				ancien_button_generer.setAttribute('href', defaut);
			else
				ancien_button_generer.setAttribute('href', '#');
			
              
		}
		</script>";
		print "<style>
			a{text-decoration : none;

			}
		</style>";
	  if(!empty($message))
		print "<script>
		$.jnotify('".$message."', {delay : 5000, fadeSpeed: 500});
		</script>";
