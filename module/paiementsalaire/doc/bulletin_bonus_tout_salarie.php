<?php

// Appel de la librairie FPDF
require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/custom/paiementsalaire/lib/paiementsalaire.lib.php';
require("fpdf/fpdf.php");


// Connexion à la BDD (à personnaliser)
$id_societe = GETPOST("id_societe","int");
$fk_user = GETPOST("id_salarie","int");

$action = GETPOST("action", "aplha");

if($action == "telecharger")
 $mode = "D";
else $mode = "I";

$mois = GETPOST("mois", "int");
$annee = GETPOST("annee", "int");
  




  $bulletin_sql = "SELECT * FROM ".MAIN_DB_PREFIX."bulletin_bonus WHERE annee=".$annee." AND mois=".$mois." AND fk_societe=".$id_societe;
    $res_bulletin = $db->query($bulletin_sql);
    $num_all = $db->num_rows($res_bulletin);
      if($num_all > 0){ //Pour le prémier salarié
        $obj_bulletin = $db->fetch_object($res_bulletin);
        $fk_salarie = $obj_bulletin->fk_salarie;

        $sql = "SELECT * FROM ".MAIN_DB_PREFIX."salarie WHERE rowid=".$fk_salarie;
        $res = $db->query($sql);
        $obj_salarie = $db->fetch_object($res);

          $user_Sql = "SELECT * FROM ".MAIN_DB_PREFIX."user where rowid=".$obj_salarie->fk_user;
          $user_Result = $db->query($user_Sql);
          $obj_user = $db->fetch_object($user_Result);

          $societe_Sql = "SELECT rowid, nom, name_alias, logo FROM ".MAIN_DB_PREFIX."societe where rowid=".$id_societe;
          $societe_Result = $db->query($societe_Sql);
          $societe_Salarie = $db->fetch_object($societe_Result);

          $sql_soc = "SELECT societe_mere FROM ".MAIN_DB_PREFIX."salairepaie_societe WHERE fk_societe=".$id_societe;
            $result_soc = $db->query($sql_soc);
            if($result_soc)
              $info_soc = $db->fetch_object($result_soc);

              $sql_select = "SELECT avant_cloture, apres_cloture FROM ".MAIN_DB_PREFIX."statut_cachet WHERE fk_societe=".$id_societe;
              $cachet_statut = $db->fetch_object($db->query($sql_select));
        global $fk_salarie, $fk_user, $societe_Salarie, $y, $mois, $annee, $id_accord_etab, $info_soc;


// Création de la class PDF
  class PDF extends FPDF {
  
    // Header
    function Header() {
      // Logo : 8 >position à gauche du document (en mm), 2 >position en haut du document, 80 >largeur de l'image en mm). La hauteur est calculée automatiquement.
      //$this->Image('logo_agence.png',8,2);
      // Saut de ligne 20 mm
      //$this->Ln(20);
      $this->SetTitle("Bulletin",true);
      // Titre gras (B) police Helbetica de 11
      $this->SetFont('Helvetica','B',11);
      // fond de couleur gris (valeurs en RGB)
      $this->setFillColor(230,230,230);
       // position du coin supérieur gauche par rapport à la marge gauche (mm)
       $this->SetX(70);
       //$this->SetY(20);
      // Texte : 60 >largeur ligne, 8 >hauteur ligne. Premier 0 >pas de bordure, 1 >retour à la ligneensuite, C >centrer texte, 1> couleur de fond ok  
      //$this->Cell(60,3,'Bulletin de Salaire',0,0,'R',0);
      //$this->line(12,$this->GetY()+5,$this->GetPageWidth()-12,$this->GetY()+5);
      //$this->line(12,40,$this->GetPageWidth()-12,40);

      // Saut de ligne 10 mm
      //$this->line(12,$this->GetY()+30,$this->GetPageWidth()-12,$this->GetY()+30);
      return pdf_pagehead_bonus($this, "");
    }

    
    // Footer
    function Footer() {

      // Positionnement à 1,5 cm du bas
          $this->SetY(-15);
      
      // Police Arial italique 8
      $this->SetFont('Helvetica','B',7);
      // Numéro de page, centré (C)
      //$this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
     // $this->line(12,$this->GetY(),$this->GetPageWidth()-12,$this->GetY());

      return pdf_ibspagefoot($this, 13, 15);

    }
        // On active la classe une fois pour toutes les pages suivantes
    // Format portrait (>P) ou paysage (>L), en mm (ou en points > pts), A4 (ou A5, etc.)
  }
    $pdf = new PDF('P','mm','A4');
    // Nouvelle page A4 (incluant ici logo, titre et pied de page)
    $pdf->AddPage();
    // Polices par défaut : Helvetica taille 9
    $pdf->SetFont('Helvetica','',9);
    // Couleur par défaut : noir
    $pdf->SetTextColor(0);
    // Compteur de pages {nb}
    $pdf->AliasNbPages();

    $i = 0;
    while ($i < ($num_all)){
      if($i != 0){
        $obj_bulletin = $db->fetch_object($res_bulletin);
        $fk_salarie = $obj_bulletin->fk_salarie;

      }

            //Objet Utilisateur
            if(!empty($obj_bulletin->fk_salarie )){
            $sql_sal = "SELECT * FROM ".MAIN_DB_PREFIX."salarie WHERE rowid=".$obj_bulletin->fk_salarie;
            $res_sal = $db->query($sql_sal);
            if($res_sal){
                $obj_salarie = $db->fetch_object($res_sal);
                $user_Result = $db->query($user_Sql);
                $id_salarie = $db->fetch_object($user_Result)->rowid;

                global $fk_salarie, $id_salarie, $y, $mois, $annee, $id_accord_etab;


                if($i > 0){
                  $pdf->AddPage();
                  $pdf->AliasNbPages();


                }
    //--------------------------------------------------------------------------------------------
    
    $retenu = 0;
    $somme_pr_ind = 0;

    //****************************************************************************** */
    $pdf->SetDrawColor(200, 200, 200);
    $y_apres_entete = 70;
    //Entête du tableau et traçage des Ligne verticales
    $pdf->line(12,$y_apres_entete +7,12,$pdf->GetPageHeight()-60);
    $pdf->line($pdf->GetPageWidth()-12,$y_apres_entete +7,$pdf->GetPageWidth()-12,$pdf->GetPageHeight()-60);

    $pdf->SetLeftMargin(12);
    $pdf->line(12,$y_apres_entete +7,$pdf->GetPageWidth()-12,$y_apres_entete +7);

    $pdf->SetLeftMargin(13);
    $y = $y_apres_entete +8;
    $pdf->SetY($y);
    $pdf->Cell(50,4, utf8_decode("Designation"),0,0,'C');
    $pdf->line(63,$y-1,63,$pdf->GetPageHeight()-60);
    
    $pdf->SetLeftMargin(63);
    $pdf->Cell(20,4, utf8_decode("Taux"),0,0,'C');
    $pdf->line(83,$y-1,83,$pdf->GetPageHeight()-60);


    $pdf->SetLeftMargin(83);
    $pdf->Cell(20,4, utf8_decode("Base"),0,0,'C');
    $pdf->line(103,$y-1,103,$pdf->GetPageHeight()-60);


    $pdf->SetLeftMargin(103);
    $pdf->Cell(30,4, utf8_decode("Montant"),0,0,'C');
    $pdf->line(133,$y-1,133,$pdf->GetPageHeight()-60);


    $pdf->SetLeftMargin(133);
    $pdf->Cell(30,4, utf8_decode("Retenue(s)"),0,0,'C');
    $pdf->line(163,$y-1,163,$pdf->GetPageHeight()-60);


    $pdf->SetLeftMargin(163);
    $pdf->MultiCell(34,4, utf8_decode("Gain(s)"),0,'C');

    $pdf->line(12,$y_apres_entete +13,$pdf->GetPageWidth()-12,$y_apres_entete +13);
    
    //jours travaillés
    $pdf->SetTextColor(0, 0, 0);   


      $pdf->SetLeftMargin(13);
      $pdf->SetX(13);
      $y = $pdf->GetY() +6;
      $pdf->SetY($y);
      $pdf->Cell(30,4, utf8_decode($obj_bulletin->libelle?:$obj_bulletin->nom_bonus),0,0,'L');

      $pdf->SetLeftMargin(63);
      $pdf->Cell(20,4, utf8_decode($obj_bulletin->pourcentage."%"),0,0,'R');
      $pdf->SetLeftMargin(83);
      $pdf->Cell(20,4, utf8_decode(apres_virgule($db, $id_societe, $obj_bulletin->base?:0, 2)),0,0,'R');

      $pdf->SetLeftMargin(103);
      $pdf->SetX(103);
      $pdf->Cell(30,4, utf8_decode(apres_virgule($db, $id_societe, $obj_bulletin->montant?:0, 2)),0,0,'R');

      $pdf->SetTextColor(0, 0, 70);
      $pdf->SetLeftMargin(13);
      $y = $pdf->GetY() +6;
      $pdf->SetY($y);
      $pdf->Cell(49,4, utf8_decode("==Primes et Indemnités=="),0,0,'L');

      $pdf->SetLeftMargin(163);
      $pdf->Cell(35,4, utf8_decode(apres_virgule($db, $id_societe, $somme_pr_ind + ($obj_bulletin->montant?:0), 2)),0,0,'R');


      
       
      $pdf->SetFont('Helvetica','',9);
      $y = $pdf->GetY()+5;
      $pdf->line(12,$y ,$pdf->GetPageWidth()-12,$y);

      //Salaire brut
      $pdf->SetTextColor(0, 0, 70);
      $pdf->SetLeftMargin(13);
      $y = $pdf->GetY() +6;
      $pdf->SetY($y);
      $pdf->Cell(49,4, utf8_decode("==Salaire Brut=="),0,0,'L');

      $pdf->SetX(103);
      $pdf->Cell(30,4, utf8_decode("--Charges Patro--"),0,0,'R');

      $pdf->SetLeftMargin(163);
      $pdf->Cell(35,4, utf8_decode(apres_virgule($db, $id_societe, $obj_bulletin->salaire_brut, 2)),0,0,'R');

      //les prestations à afficher par Organisme
      $id_organisme = array();
      $bulletin_prest_sql = "SELECT * FROM ".MAIN_DB_PREFIX."bulletin_bonus_organisme WHERE fk_bulletin=".$obj_bulletin->rowid;
      $bulletin_prest_res = $db->query($bulletin_prest_sql);
      $avance = false;
      if($bulletin_prest_res){
        $i = 0;
        $num = $db->num_rows($bulletin_prest_res);
        while ($i < $num){
          $obj_bulletin_prest = $db->fetch_object($bulletin_prest_res);
          $id_organisme[] = $obj_bulletin_prest->fk_organisme;

          if($i == 0)
          $y = $pdf->GetY() + 6;
        else $y += 6; 
                      $pdf->SetLeftMargin(13);
                      $pdf->SetY($y);
                      $pdf->Cell(49,4, utf8_decode($obj_bulletin_prest->nom_organisme),0,0,'L');
        
                      $pdf->SetLeftMargin(63);
                      $pdf->Cell(20,4, utf8_decode($obj_bulletin_prest->pourcentage."%"),0,0,'R');
        
                      $pdf->SetX(83);
                      $pdf->Cell(20,4, utf8_decode(apres_virgule($db, $id_societe, $obj_bulletin->salaire_brut_cotisable, 2)),0,0,'R');
        
                      $pdf->SetX(103);
                      $pdf->Cell(30,4, utf8_decode(apres_virgule($db, $id_societe, $obj_bulletin_prest->montant_employeur, 2)),0,0,'R');
        
                      $pdf->SetX(133);
                      $pdf->MultiCell(30,4, utf8_decode(apres_virgule($db, $id_societe, $obj_bulletin_prest->montant_employe, 2)),0,'R');
                      $avance = true;
                      $retenu += $obj_bulletin_prest->montant_employe;

            $i ++;
        }
        
      }

      

      //les prestations dont on doit afficher les détails
      $bulletin_prest_sql = "SELECT * FROM ".MAIN_DB_PREFIX."bulletin_bonus_cotisation WHERE fk_bulletin=".$obj_bulletin->rowid;
      $bulletin_prest_res = $db->query($bulletin_prest_sql);
      if($bulletin_prest_res){
        $i = 0;
        $num = $db->num_rows($bulletin_prest_res);
        while ($i < $num){
          $obj_bulletin_prest = $db->fetch_object($bulletin_prest_res);
          $sql_prestation = "SELECT fk_organisme FROM ".MAIN_DB_PREFIX."type_prestation WHERE nature='obligatoire'";
          $result_prestation = $db->query($sql_prestation);
          $prestation = $db->fetch_object($result_bareme);

          if(!in_array($prestation->fk_organisme, $id_organisme))            
            if($obj_bulletin_prest->affiche_bulletin == "Oui"){
              if($i == 0){
                if($avance)
                  $y = $pdf->GetY() + 2;
                else $y = $pdf->GetY() + 6;

              }else $y += 6;

              $pdf->SetLeftMargin(13);
              $pdf->SetY($y);
              $pdf->Cell(49,4, utf8_decode($obj_bulletin_prest->libelle),0,0,'L');

              $pdf->SetLeftMargin(63);
              $pdf->Cell(20,4, utf8_decode($obj_bulletin_prest->taux_employe."%"),0,0,'R');

              $pdf->SetX(83);
              $pdf->Cell(20,4, utf8_decode(apres_virgule($db, $id_societe, $obj_bulletin->salaire_brut_cotisable, 2)),0,0,'R');

              $pdf->SetX(103);
              $pdf->Cell(30,4, utf8_decode(apres_virgule($db, $id_societe, $obj_bulletin_prest->montant_employeur, 2)),0,0,'R');

              $pdf->SetX(133);
              $pdf->MultiCell(30,4, utf8_decode(apres_virgule($db, $id_societe, $obj_bulletin_prest->montant_employe, 2)),0,'R');
              $retenu += $obj_bulletin_prest->montant_employe;

           
          }
            $i ++;
        }
        
      }
      
      
      $salaire_brut = $obj_bulletin->salaire_brut;
      /*$pdf->SetLeftMargin(163);
      $pdf->Cell(35,4, utf8_decode(apres_virgule($db, $id_societe, $salaire_brut, 2)),0,0,'R');*/

      $retenu_its = 0;
      $bulletin_taxe_sql = "SELECT * FROM ".MAIN_DB_PREFIX."bulletin_bonus_taxe WHERE fk_bulletin=".$obj_bulletin->rowid;
      $bulletin_taxe_res = $db->query($bulletin_taxe_sql);
      if($bulletin_taxe_res){
        $i = 0;
        $num = $db->num_rows($bulletin_taxe_res);
        while ($i < $num){
          $obj_bulletin_taxe = $db->fetch_object($bulletin_taxe_res);
            if($obj_bulletin_taxe->affiche_bulletin == "Oui"){
              $y = $pdf->GetY() +2;
              $pdf->SetLeftMargin(13);
              $pdf->SetY($y);
              $pdf->Cell(49,4, utf8_decode($obj_bulletin_taxe->libelle),0,0,'L');

              $pdf->SetX(63);
              $pdf->Cell(20,4, utf8_decode($obj_bulletin_taxe->taux."%"),0,0,'R');

              $pdf->SetX(83);
              $pdf->Cell(20,4, utf8_decode(apres_virgule($db, $id_societe, $obj_bulletin->salaire_brut_imposable, 2)),0,0,'R');

              $pdf->SetX(133);
              $pdf->MultiCell(30,4, utf8_decode(apres_virgule($db, $id_societe, $obj_bulletin_taxe->montant, 2)),0,'R');
              $retenu += $obj_bulletin_taxe->montant;
              $retenu_its += $obj_bulletin_taxe->montant;
            
          }
            $i ++;
        }
        
      }


      

          //Salaire net
          $pdf->SetTextColor(0, 0, 70);
          $pdf->SetLeftMargin(13);
          $y = $pdf->GetY() +3;
          $pdf->SetY($y);
          $pdf->Cell(49,4, utf8_decode("==Salaire Net=="),0,0,'L');

          $salaire_net = $obj_bulletin->net_payer;
          $pdf->SetLeftMargin(163);
          $pdf->Cell(35,4, utf8_decode(apres_virgule($db, $id_societe, $salaire_net, 2)),0,0,'R');

          $pdf->SetTextColor(0, 0, 0);


          //Avance/acompte
          $somme_avance = 0;
          $bulletin_avance = "SELECT * FROM ".MAIN_DB_PREFIX."bulletin_avance WHERE fk_bulletin=".$obj_bulletin->rowid;
         $bulletin_avance = $db->query($bulletin_avance);
         if($bulletin_avance){
           $i = 0;
           $num = $db->num_rows($bulletin_avance);
           if($num > 0){
             $y = $pdf->GetY()+5;
             $pdf->line(12,$y ,$pdf->GetPageWidth()-12,$y);
           }
           while ($i < $num){
             $obj_bulletin_avance = $db->fetch_object($bulletin_avance);
    
             if($i == 0)
               $y = $pdf->GetY() +6;
             else $y += 4;
             $pdf->SetLeftMargin(13);
             $pdf->SetY($y);
             $pdf->Cell(49,4, utf8_decode($obj_bulletin_avance->libelle),0,0,'L');
    
             $pdf->SetX(103);
             $pdf->MultiCell(30,4, utf8_decode(apres_virgule($db, $id_societe, $obj_bulletin_avance->montant, 2)),0,'R');
             
             $somme_avance += $obj_bulletin_avance->montant;
             $i ++;
             //print $obj_bulletin_hs->montant."<br>";
           }
    
           if($num > 0){
             $pdf->SetFont('Helvetica','',9);
             $pdf->SetTextColor(0, 0, 70);
             $pdf->SetLeftMargin(13);
             $y = $pdf->GetY() +2;
             $pdf->SetY($y);
             $pdf->Cell(49,4, utf8_decode("==Avances/Acomptes=="),0,0,'L');
    
             $pdf->SetLeftMargin(133);
             $pdf->Cell(30,4, utf8_decode(apres_virgule($db, $id_societe, $somme_avance, 2)),0,0,'R');
           }
    
         }
          $y = $pdf->GetY()+5;
          $pdf->line(12,$y ,$pdf->GetPageWidth()-12,$y);

      $y = $y_apres_entete +14;

      $pdf->line(12,$pdf->GetPageHeight()-60 ,$pdf->GetPageWidth()-12,$pdf->GetPageHeight()-60);

      //informations en-dessous du tableau
      //à gauche
      $pdf->SetFont('Helvetica','',8);

      $y = $pdf->GetPageHeight()-58;
      $pdf->SetLeftMargin(13);
      $pdf->SetY($y);
      $pdf->SetFillColor(240, 240, 240);
      $pdf->MultiCell(65,4, utf8_decode("Virement : ".$obj_bulletin->banque),0,'L');

      $y += 4;
      $pdf->SetY($y);
      $pdf->SetFillColor(245, 245, 245);
      $pdf->MultiCell(65,4, utf8_decode("N° :".$obj_bulletin->compte),0,'L');

      $y += 4;
      $pdf->SetY($y);
      $pdf->SetFillColor(245, 245, 245);
      $pdf->MultiCell(65,4, utf8_decode("I.N.P.S : ".$obj_bulletin->inps),0,'L');

      $y += 4;
      $pdf->SetY($y);
      $pdf->SetFillColor(245, 245, 245);
      $pdf->MultiCell(65,4, utf8_decode("AMO : ".$obj_bulletin->amo),0,'L');
      
      //à droite
      //salaire Brut
      $y = $pdf->GetPageHeight()-57;
      $pdf->SetLeftMargin(133);
      $pdf->SetY($y);
      $pdf->SetFillColor(240, 240, 240);
      $pdf->Cell(28,4, utf8_decode("Salaire Brut :"),0,0,'L','true');

      $pdf->SetLeftMargin(161);
      $pdf->MultiCell(31,4, utf8_decode(apres_virgule($db, $id_societe, $salaire_net+$retenu, 2)),0,'R','true');

      //retenu
      $pdf->SetLeftMargin(133);
      $y += 4;
      $pdf->SetY($y);
      $pdf->SetFillColor(245, 245, 245);
      $pdf->Cell(28,4, utf8_decode("Retenue(s) :"),0,0,'L','true');
      $pdf->SetLeftMargin(161);
      $pdf->MultiCell(31,4, utf8_decode(apres_virgule($db, $id_societe, $retenu, 2)),0,'R','true');
      //salaire net
      $pdf->SetLeftMargin(133);
      $y += 4;
      $pdf->SetY($y);
      $pdf->SetFillColor(240, 240, 240);
      $pdf->Cell(28,4, utf8_decode("Salaire Net :"),0,0,'L','true');

      $pdf->SetLeftMargin(161);
      $pdf->MultiCell(31,4, utf8_decode(apres_virgule($db, $id_societe, $salaire_net, 2)),0,'R','true');
      //Avance
      $pdf->SetLeftMargin(133);
      $y += 4;
      $pdf->SetY($y);
      $pdf->SetFillColor(245, 245, 245);
      $pdf->Cell(28,4, utf8_decode("Avance/acompte :"),0,0,'L','true');

    //Avance
      $pdf->SetLeftMargin(161);
      $pdf->MultiCell(31,4, utf8_decode(apres_virgule($db, $id_societe, ($somme_avance?:0), 2)),0,'R','true');

      //net à payer
      $pdf->SetLeftMargin(133);
      $y += 4;
      $pdf->SetY($y);
      $pdf->SetFillColor(250, 250, 250);
      $pdf->Cell(28,4, utf8_decode("Net à payer :"),0,0,'L','true');

      $pdf->SetLeftMargin(161);
      $pdf->MultiCell(31,4, utf8_decode(apres_virgule($db, $id_societe, ($salaire_net - $somme_avance)?:0, 2)),0,'R','true');

      //*********************************************************************** */
      $directory = DOL_DOCUMENT_ROOT.'/paiementsalaire/config/cachet_societe/';
			if(is_readable($directory.$id_societe.'.png')){
				$file = $id_societe.'.png';
				$filePath = $directory.$file;
			}elseif(is_readable($directory.$id_societe.'.jpg')){
				$file = $id_societe.'.jpg';
				$filePath = $directory.$file;
			}elseif(is_readable($directory.$id_societe.'.jpeg')){
				$file = $id_societe.'.jpeg';
				$filePath = $directory.$file;
			}

      //On met le cachet sur le cadre à droite si le mois est cloturé et s'il y a un cachet
      //les cadres
      //à gauche
      $pdf->SetLeftMargin(13);
      $pdf->SetY($y+6);
      $pdf->MultiCell(59,14, "",1,'');

      $pdf->SetLeftMargin(133);
      $pdf->SetY($y+6);
      $pdf->MultiCell(59,14, "",1,'');
      if(is_readable($filePath) && $obj_bulletin->cloture == "oui" && $cachet_statut->apres_cloture == 1){
        $pdf->Image($filePath,150,$y+6, 40,19);
      }elseif(is_readable($filePath) && $obj_bulletin->cloture == "non" && $cachet_statut->avant_cloture == 1){
        $pdf->Image($filePath,150,$y+6, 40,19);
      }
  
    }

    
  }
    $i ++;
    }
    $pdf->Output('', $mode);

}
$db->free();

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
  $db->close();