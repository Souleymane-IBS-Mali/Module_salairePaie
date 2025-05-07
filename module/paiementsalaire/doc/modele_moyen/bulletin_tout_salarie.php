<?php


// Appel de la librairie FPDF
require '../../../main.inc.php';
//require_once DOL_DOCUMENT_ROOT.'/custom/paiementsalaire/lib/paiementsalaire.lib.php';
require("../fpdf/fpdf.php");


// Connexion à la BDD (à personnaliser)
$id_societe = GETPOST("id_societe","int");
$id_salarie = GETPOST("id_salarie","int");
$id_convention = GETPOST("id_convention","int");

$action = GETPOST("action", "aplha");

if($action == "telecharger")
 $mode = "D";
else $mode = "I";
$mois = GETPOST("mois", "int");
$annee = GETPOST("annee", "int");
$id_convention = GETPOST("id_convention", "int");


if($id_societe){
  $bulletin_sql = "SELECT * FROM ".MAIN_DB_PREFIX."bulletin WHERE annee=".$annee." AND mois=".$mois." AND fk_societe=".$id_societe;
  $bulletin_sql .= " ORDER BY rowid";
    $res_bulletin = $db->query($bulletin_sql);
        if($res_bulletin){
          $num_all = $db->num_rows($res_bulletin);
          if ($num_all > 0){
            //Objet Utilisateur
            $obj_bulletin = $db->fetch_object($res_bulletin);
            $fk_salarie = $obj_bulletin->fk_salarie;
            $sql_sal = "SELECT * FROM ".MAIN_DB_PREFIX."salarie WHERE rowid=".$fk_salarie;
            $res_sal = $db->query($sql_sal);
            if($res_sal){
              $obj_salarie = $db->fetch_object($res_sal);

              $sql_soc = "SELECT societe_mere FROM ".MAIN_DB_PREFIX."salairepaie_societe WHERE fk_societe=".$id_societe;
                $result_soc = $db->query($sql_soc);
                if($result_soc)
                  $info_soc = $db->fetch_object($result_soc);

                  $sql_select = "SELECT avant_cloture, apres_cloture FROM ".MAIN_DB_PREFIX."statut_cachet WHERE fk_societe=".$id_societe;
                $cachet_statut = $db->fetch_object($db->query($sql_select));

                $user_Sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."user where rowid=".$obj_salarie->fk_user;
                $user_Result = $db->query($user_Sql);
                $id_salarie = $db->fetch_object($user_Result)->rowid;





    global $fk_salarie, $id_salarie, $y, $mois, $annee, $id_accord_etab, $info_soc, $cachet_statut;
              }


// Création de la class PDF
  class PDF extends FPDF {

    // Header
    function Header() {
      // Logo : 8 >position à gauche du document (en mm), 2 >position en haut du document, 80 >largeur de l'image en mm). La hauteur est calculée automatiquement.
      //$this->Image('logo_agence.png',8,2);
      // Saut de ligne 20 mm
      //$this->Ln(20);
      $this->SetTitle("Bulletins",true);
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
      return pdf_pagehead_moyen($this, "");
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
    function SetPage($num) {
      $this->page = $num;
  }
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
    // Nouvelle page A4 (incluant ici logo, titre et pied de page)
          $i = 0;
          $num_all = $db->num_rows($res_bulletin);
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


    $sal_categ = 0;


   //--------------------------------------------------------------------------------------------
    $retenu = 0;
    $somme_pr_ind = 0;

    //****************************************************************************** */
    $pdf->SetDrawColor(200, 200, 200);
    $y_apres_entete = 64;
    //Entête du tableau et traçage des Ligne verticales
    $pdf->line(12,$y_apres_entete +7,12,$pdf->GetPageHeight()-60);
    $pdf->line($pdf->GetPageWidth()-12,$y_apres_entete +7,$pdf->GetPageWidth()-12,$pdf->GetPageHeight()-60);

    $pdf->SetLeftMargin(12);
    $pdf->line(12,$y_apres_entete +7,$pdf->GetPageWidth()-12,$y_apres_entete +7);

    $pdf->SetLeftMargin(13);
    $y = $y_apres_entete +8;
    $pdf->SetY($y);
    $pdf->Cell(50,4, utf8_decode("Constituant du salaire"),0,0,'C');
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
    $y = $pdf->GetY() + 2;
    $pdf->SetY($y);
    $pdf->Cell(30,4, utf8_decode("Jours travaillés"),0,0,'L');

    //nombre de de jour du mois
    $salSql = "SELECT jour FROM ".MAIN_DB_PREFIX."salarie_nombre_jour_travaille where annee=".$annee." AND mois=".$mois." AND fk_salarie=".$obj_salarie->rowid;
    $result = $db->query($salSql);
    $nb_jours = $db->fetch_object($result)->jour;
    //$nb_jours = cal_days_in_month(CAL_GREGORIAN, $mois, $annee);
    $pdf->SetLeftMargin(63);
    $pdf->Cell(20,4, utf8_decode($nb_jours),0,0,'R');

    //heures normales
    $pdf->SetLeftMargin(13);
    $y = $pdf->GetY() +6;
    $pdf->SetY($y);
    $pdf->Cell(30,4, utf8_decode("Heures normales"),0,0,'L');
    $pdf->SetLeftMargin(63);
    $nb_total_jour = cal_days_in_month(CAL_GREGORIAN, $mois, $annee);
    $heur_normal = 173.33;
    if($nb_jours != $nb_total_jour)
      $heur_normal = round(($nb_jours*$heur_normal)/$nb_total_jour, 2);
    $pdf->Cell(20,4, utf8_decode($heur_normal),0,0,'R');
    //Salaire de base normale
    $pdf->SetLeftMargin(13);
    $pdf->SetX(13);
    $y = $pdf->GetY() +6;
    $pdf->SetY($y);
    $pdf->Cell(35,4, utf8_decode("Salaire de base normale"),0,0,'L');


      $pdf->SetLeftMargin(103);
      $pdf->SetX(103);
      $pdf->Cell(30,4, utf8_decode(apres_virgule($db, $id_societe, $obj_bulletin->salaire_base?:0, 2)),0,0,'R');

      //Salaire de base Majorés
      $pdf->SetTextColor(0, 0, 70);
      $pdf->SetLeftMargin(13);
      $y = $pdf->GetY() +6;
      $pdf->SetY($y);
      $pdf->line(12,$y-1 ,$pdf->GetPageWidth()-12,$y-1);
      $pdf->Cell(50,4, utf8_decode("Salaire de base Majorés"),0,0,'L');

      $pdf->SetLeftMargin(163);
      $pdf->Cell(35,4, utf8_decode(apres_virgule($db, $id_societe, (int)$obj_bulletin->salaire_base, 2)),0,0,'R');

      $y = $pdf->GetY()+5;
      $pdf->line(12,$y ,$pdf->GetPageWidth()-12,$y);
      //Les Primes et Indemnités
      $somme_pr_ind  = 0;
      //Primes
      $pdf->SetTextColor(0, 0, 0);

      $bulletin_anc_sql = "SELECT * FROM ".MAIN_DB_PREFIX."bulletin_anciennete WHERE fk_bulletin=".$obj_bulletin->rowid;
      $res_bulletin_anc = $db->query($bulletin_anc_sql);
      $obj_bulletin_anc = $db->fetch_object($res_bulletin_anc);

      $pdf->SetLeftMargin(13);
      $y = $pdf->GetY() + 6;
      $pdf->SetY($y);
      $pdf->Cell(30,4, utf8_decode($obj_bulletin_anc->libelle),0,0,'L');

      $pdf->SetLeftMargin(63);
      $pdf->Cell(20,4, utf8_decode($obj_bulletin_anc->taux."%"),0,0,'R');

      $pdf->SetLeftMargin(83);
      $pdf->Cell(20,4, utf8_decode(apres_virgule($db, $id_societe, $obj_bulletin->salaire_base, 2)),0,0,'R');

      $salaire_brut_non_exonere = $obj_bulletin->salaire_brut_non_exonere;

      $somme_pr_ind += (int)$obj_bulletin->salaire_base*$obj_bulletin_anc->taux/100;
      $pdf->SetLeftMargin(103);
      $pdf->Cell(30,4, utf8_decode(apres_virgule($db, $id_societe, (int)$obj_bulletin->salaire_base*$obj_bulletin_anc->taux/100, 2)),0,0,'R');

      //les primes qui doivent être affichés sur le billetin
      $bulletin_pr_sql = "SELECT * FROM ".MAIN_DB_PREFIX."bulletin_prime WHERE fk_bulletin=".$obj_bulletin->rowid;;
      $bulletin_pr_res = $db->query($bulletin_pr_sql);
      if($bulletin_pr_res){
        $j = 0;
        $num = $db->num_rows($bulletin_pr_res);
        while ($j < $num){
          $obj_bulletin_pr = $db->fetch_object($bulletin_pr_res);
          if ($obj_bulletin_pr->affiche_bulletin)
          {
            $pdf->SetLeftMargin(13);
            $y = $pdf->GetY() + 6;
            $pdf->SetY($y);
            $pdf->Cell(30,4, utf8_decode($obj_bulletin_pr->libelle),0,0,'L');

            $pdf->SetLeftMargin(63);
            $pdf->Cell(20,4, utf8_decode($obj_bulletin_pr->pourcentage."%"),0,0,'R');

            $pdf->SetLeftMargin(83);
            $pdf->Cell(20,4, utf8_decode(apres_virgule($db, $id_societe, $obj_bulletin_pr->montant*100/$obj_bulletin_pr->pourcentage, 2)),0,0,'R');

            $pdf->SetLeftMargin(103);
            $pdf->Cell(30,4, utf8_decode(apres_virgule($db, $id_societe, $obj_bulletin_pr->montant, 2)),0,0,'R');

            $somme_pr_ind += $obj_bulletin_pr->montant;
          }
            $j ++;
        }
        $db->free($bulletin_pr_res);

      }

      //les primes exceptionnelles
      $bulletin_pr_except_sql = "SELECT * FROM ".MAIN_DB_PREFIX."bulletin_prime_exceptionnelle WHERE fk_bulletin=".$obj_bulletin->rowid;
      $bulletin_pr_except_res = $db->query($bulletin_pr_except_sql);
      if($bulletin_pr_except_res){
        $j = 0;
        $num = $db->num_rows($bulletin_pr_except_res);
        while ($j < $num){
          $obj_bulletin_pr = $db->fetch_object($bulletin_pr_except_res);
          if ($obj_bulletin_pr->affiche_bulletin)
          {
            $pdf->SetLeftMargin(13);
            $y = $pdf->GetY() + 6;
            $pdf->SetY($y);
            $pdf->Cell(30,4, utf8_decode($obj_bulletin_pr->libelle),0,0,'L');

            $pdf->SetLeftMargin(63);
            $pdf->Cell(20,4, utf8_decode($obj_bulletin_pr->pourcentage."%"),0,0,'R');

            $pdf->SetLeftMargin(83);
            $pdf->Cell(20,4, utf8_decode(apres_virgule($db, $id_societe, $obj_bulletin_pr->montant, 2)),0,0,'R');

            $pdf->SetLeftMargin(103);
            $pdf->Cell(30,4, utf8_decode(apres_virgule($db, $id_societe, $obj_bulletin_pr->montant, 2)),0,0,'R');

            $somme_pr_ind += $obj_bulletin_pr->montant;
          }
            $j ++;
        }
        $db->free($bulletin_pr_except_res);

      }

     //les indemnités
     $bulletin_ind_sql = "SELECT * FROM ".MAIN_DB_PREFIX."bulletin_indemnite WHERE fk_bulletin=".$obj_bulletin->rowid;
      $bulletin_ind_res = $db->query($bulletin_ind_sql);
      if($bulletin_ind_res){
        $j = 0;
        $num = $db->num_rows($bulletin_ind_res);
        while ($j < $num){
          $obj_bulletin_ind = $db->fetch_object($bulletin_ind_res);
          if ($obj_bulletin_ind->affiche_bulletin = "Oui")
          {

            $pdf->SetLeftMargin(13);
            $y = $pdf->GetY() + 6;
            $pdf->SetY($y);
            $pdf->Cell(30,4, utf8_decode($obj_bulletin_ind->libelle),0,0,'L');

            $pdf->SetLeftMargin(63);
            $pdf->Cell(20,4, utf8_decode($obj_bulletin_ind->pourcentage."%"),0,0,'R');

            $pdf->SetLeftMargin(83);
            $pdf->Cell(20,4, utf8_decode(apres_virgule($db, $id_societe, $obj_bulletin_ind->montant*100/$obj_bulletin_ind->pourcentage, 2)),0,0,'R');

            $pdf->SetLeftMargin(103);
            $pdf->Cell(30,4, utf8_decode(apres_virgule($db, $id_societe, $obj_bulletin_ind->montant, 2)),0,0,'R');

            $somme_pr_ind += $obj_bulletin_ind->montant;
          }
            $j ++;
        }
        $db->free($bulletin_ind_res);
      }

      $pdf->SetLeftMargin(13);
      $pdf->SetX(13);
      $y = $pdf->GetY() +6;
      $pdf->SetY($y);
      $pdf->Cell(30,4, utf8_decode("Sursalaire"),0,0,'L');

	  if($obj_bulletin->pourcentage*100 == 100)
        $p = 100;
      else $p = round($obj_bulletin->pourcentage*100, 2);

      $pdf->SetLeftMargin(63);
      $pdf->Cell(20,4, utf8_decode($p."%"),0,0,'R');

      $pdf->SetLeftMargin(83);
      $pdf->Cell(20,4, utf8_decode(apres_virgule($db, $id_societe, ($obj_bulletin->sursalaire*100/$p)?:0, 2)),0,0,'R');

      $pdf->SetLeftMargin(103);
      $pdf->SetX(103);
      $pdf->Cell(30,4, utf8_decode(apres_virgule($db, $id_societe, $obj_bulletin->sursalaire?:0, 2)),0,0,'R');

      $pdf->SetTextColor(0, 0, 70);
      $pdf->SetLeftMargin(13);
      $y = $pdf->GetY() +6;
      $pdf->SetY($y);
      $pdf->line(12,$y-1 ,$pdf->GetPageWidth()-12,$y-1);
      $pdf->Cell(49,4, utf8_decode("Primes et Indemnités"),0,0,'L');

      $pdf->SetLeftMargin(163);
      $pdf->Cell(35,4, utf8_decode(apres_virgule($db, $id_societe, $somme_pr_ind + ($obj_bulletin->sursalaire?:0), 2)),0,0,'R');

      //affichage des heures sup
      $pdf->SetFont('Helvetica','',7);
      $pdf->SetTextColor(0, 0, 0);
      $valeur_heur_sup = 0;
      $base = 0;
      $bulletin_hs_sql = "SELECT * FROM ".MAIN_DB_PREFIX."bulletin_heure_sup WHERE fk_bulletin=".$obj_bulletin->rowid;
     $bulletin_hs_res = $db->query($bulletin_hs_sql);
     if($bulletin_hs_res){
       $j = 0;
       $num = $db->num_rows($bulletin_hs_res);
       if($num > 0){
        $y = $pdf->GetY()+5;
        $pdf->line(12,$y ,$pdf->GetPageWidth()-12,$y);
      }
      while ($j < $num){
        $obj_bulletin_hs = $db->fetch_object($bulletin_hs_res);
        if($j == 0)
          $y = $pdf->GetY() +6;
        else $y += 4;
        $pdf->SetLeftMargin(13);
        $pdf->SetY($y);
        $pdf->Cell(49,4, utf8_decode($obj_bulletin_hs->nombre_heure_sup.'HS '.$obj_bulletin_hs->libelle),0,0,'L');

        $pdf->SetLeftMargin(63);
        $pdf->Cell(20,4, utf8_decode($obj_bulletin_hs->taux."%"),0,0,'R');

        $pdf->SetX(83);
        $pdf->Cell(20,4, utf8_decode(apres_virgule($db, $id_societe, $obj_bulletin_hs->base, 2)),0,0,'R');

        $pdf->SetX(103);
        $pdf->MultiCell(30,4, utf8_decode(apres_virgule($db, $id_societe, $obj_bulletin_hs->montant, 2)),0,'R');

        $valeur_heur_sup += $obj_bulletin_hs->montant;
        $j ++;
        //print $obj_bulletin_hs->montant."<br>";
      }
      if($num > 0){
        $pdf->SetFont('Helvetica','',9);
        $pdf->SetTextColor(0, 0, 70);
        $pdf->SetLeftMargin(13);
        $y = $pdf->GetY() +2;
        $pdf->SetY($y);
        $pdf->line(12,$y-1 ,$pdf->GetPageWidth()-12,$y-1);
        $pdf->Cell(49,4, utf8_decode("Heures Supplémentaires"),0,0,'L');

        $pdf->SetLeftMargin(163);
        $pdf->Cell(35,4, utf8_decode(apres_virgule($db, $id_societe, $valeur_heur_sup, 2)),0,0,'R');
      }
     }
     $pdf->SetFont('Helvetica','',9);
      $y = $pdf->GetY()+5;
      $pdf->line(12,$y ,$pdf->GetPageWidth()-12,$y);

      //Salaire brut
      $inps = 0;
      $pdf->SetTextColor(0, 0, 70);
      $pdf->SetLeftMargin(13);
      $y = $pdf->GetY() +6;
      $pdf->SetY($y);
      $pdf->line(12,$y-1 ,$pdf->GetPageWidth()-12,$y-1);
      $pdf->Cell(49,4, utf8_decode("Salaire Brut"),0,0,'L');

      $pdf->SetX(103);
      $pdf->Cell(30,4, utf8_decode("--Charges Patro--"),0,0,'R');

      //$obj_bulletin->salaire_brut += $somme_pr_ind;
      $pdf->SetLeftMargin(163);
      $pdf->Cell(35,4, utf8_decode(apres_virgule($db, $id_societe, $obj_bulletin->salaire_brut, 2)),0,0,'R');

       //les prestations à afficher par Organisme
       $id_organisme = array();
       $bulletin_prest_sql = "SELECT * FROM ".MAIN_DB_PREFIX."bulletin_organisme WHERE fk_bulletin=".$obj_bulletin->rowid;
       $bulletin_prest_res = $db->query($bulletin_prest_sql);
       $avance = false;
       if($bulletin_prest_res){

         $j = 0;
         $num = $db->num_rows($bulletin_prest_res);
         while ($j < $num){
           $obj_bulletin_prest = $db->fetch_object($bulletin_prest_res);
           $id_organisme[] = $obj_bulletin_prest->fk_organisme;
           if($j == 0)
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
             $j ++;
         }

       }

      $bulletin_prest_sql = "SELECT * FROM ".MAIN_DB_PREFIX."bulletin_cotisation WHERE fk_bulletin=".$obj_bulletin->rowid;
      $bulletin_prest_res = $db->query($bulletin_prest_sql);
      if($bulletin_prest_res){
        $j = 0;
        $num = $db->num_rows($bulletin_prest_res);
        while ($j < $num){
          $obj_bulletin_prest = $db->fetch_object($bulletin_prest_res);

          $sql_prestation = "SELECT fk_organisme FROM ".MAIN_DB_PREFIX."type_prestation WHERE nature='obligatoire'";
          $result_prestation = $db->query($sql_prestation);
          $prestation = $db->fetch_object($result_bareme);

            if(!in_array($prestation->fk_organisme, $id_organisme))
                if($obj_bulletin_prest->affiche_bulletin == "Oui"){
                  if($j == 0){
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
            $j ++;
        }
        $db->free($bulletin_prest_res);


      }


      //$salaire_brut = $obj_bulletin->salaire_brut;
      /*$pdf->SetLeftMargin(163);
      $pdf->Cell(35,4, utf8_decode(apres_virgule($db, $id_societe, $salaire_brut, 2)),0,0,'R');*/

      $bulletin_taxe_sql = "SELECT * FROM ".MAIN_DB_PREFIX."bulletin_taxe WHERE fk_bulletin=".$obj_bulletin->rowid;
      $bulletin_taxe_res = $db->query($bulletin_taxe_sql);
      if($bulletin_taxe_res){
        $j = 0;
        $num = $db->num_rows($bulletin_taxe_res);
        while ($j < $num){
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

          }
            $j ++;
        }
        $db->free($bulletin_taxe_res);

      }

          //Salaire net
          $pdf->SetTextColor(0, 0, 70);
          $pdf->SetLeftMargin(13);
          $y = $pdf->GetY() +3;
          $pdf->SetY($y);
          $pdf->line(12,$y-1 ,$pdf->GetPageWidth()-12,$y-1);
          $pdf->Cell(49,4, utf8_decode("Salaire Net"),0,0,'L');

          $salaire_net = $obj_bulletin->net_payer;
          $pdf->SetLeftMargin(163);
          $pdf->Cell(35,4, utf8_decode(apres_virgule($db, $id_societe, $salaire_net, 2)),0,0,'R');

          $pdf->SetTextColor(0, 0, 0);


          //Avance/acompte
          $somme_avance = 0;
          $bulletin_avance = "SELECT * FROM ".MAIN_DB_PREFIX."bulletin_avance WHERE fk_bulletin=".$obj_bulletin->rowid;
         $bulletin_avance = $db->query($bulletin_avance);
         if($bulletin_avance){
           $k = 0;
           $num = $db->num_rows($bulletin_avance);
           if($num > 0){
             $y = $pdf->GetY()+5;
             $pdf->line(12,$y ,$pdf->GetPageWidth()-12,$y);
           }
           while ($k < $num){
             $obj_bulletin_avance = $db->fetch_object($bulletin_avance);

             if($k == 0)
               $y = $pdf->GetY() +6;
             else $y += 4;
             $pdf->SetLeftMargin(13);
             $pdf->SetY($y);
             $pdf->Cell(49,4, utf8_decode($obj_bulletin_avance->libelle),0,0,'L');

             $pdf->SetX(103);
             $pdf->MultiCell(30,4, utf8_decode(apres_virgule($db, $id_societe, $obj_bulletin_avance->montant, 2)),0,'R');

             $somme_avance += $obj_bulletin_avance->montant;
             $k ++;
             //print $obj_bulletin_hs->montant."<br>";
           }

           if($num > 0){
             $pdf->SetFont('Helvetica','',9);
             $pdf->SetTextColor(0, 0, 70);
             $pdf->SetLeftMargin(13);
             $y = $pdf->GetY() +2;
             $pdf->SetY($y);
             $pdf->line(12,$y-1 ,$pdf->GetPageWidth()-12,$y-1);
             $pdf->Cell(49,4, utf8_decode("Avances/Acomptes"),0,0,'L');

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
      $pdf->SetLeftMargin(15);
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
      //salaire net
      $pdf->SetFont('Helvetica','',8);

      $y = $pdf->GetPageHeight()-56;
      $pdf->SetLeftMargin(133);
      $pdf->SetY($y);
      $pdf->SetFillColor(255, 255, 255);
      $pdf->Cell(28,4, utf8_decode("Salaire Net :"),0,0,'L',true);

      $pdf->SetLeftMargin(161);
      $pdf->MultiCell(31,4, utf8_decode(apres_virgule($db, $id_societe, $salaire_net+$retenu, 2)),0,'R',true);

      //retenu
      $pdf->SetLeftMargin(133);
      $y += 4;
      $pdf->SetY($y);
      $pdf->SetFillColor(245, 245, 245);
      $pdf->Cell(28,4, utf8_decode("Retenue(s) :"),0,0,'L',true);
      $pdf->SetLeftMargin(61);
      $pdf->MultiCell(31,4, utf8_decode(apres_virgule($db, $id_societe, $retenu, 2)),0,'R',true);
      //Avance
      $pdf->SetLeftMargin(133);
      $y += 4;
      $pdf->SetY($y);
      $pdf->SetFillColor(245, 245, 245);
      $pdf->Cell(28,4, utf8_decode("Avance/acompte :"),0,0,'L',true);

    //Avance
      $pdf->SetLeftMargin(161);
      $pdf->MultiCell(31,4, utf8_decode(apres_virgule($db, $id_societe, ($somme_avance?:0), 2)),0,'R',true);

      //net à payer
      $pdf->SetLeftMargin(133);
      $y += 4;
      $pdf->SetY($y);
      $pdf->SetFillColor(245, 245, 245);
      //$pdf->SetTextColor(255, 255, 255);
      $pdf->Cell(28,4, utf8_decode("Net à payer :"),0,0,'L',true);

      $pdf->SetLeftMargin(161);
      $pdf->MultiCell(31,4, utf8_decode(apres_virgule($db, $id_societe, ($salaire_net - $somme_avance)?:0, 2)),0,'R',true);

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
      $pdf->SetY($y+7);
      $pdf->MultiCell(59,13, "",1,'');

      //à droite
      $pdf->SetLeftMargin(133);
      $pdf->SetY($y+7);
      $pdf->MultiCell(59,13, "",1,'');

      if(is_readable($filePath) && $obj_bulletin->cloture == "oui" && $cachet_statut->apres_cloture == 1){
        $pdf->Image($filePath,150,$y+7, 40,19);
      }elseif(is_readable($filePath) && $obj_bulletin->cloture == "non" && $cachet_statut->avant_cloture == 1){
        $pdf->Image($filePath,150,$y+7, 40,19);
      }

    

  }


            }
  $i++;
          }
          $pdf->Output('de tous les salariés', $mode);
  }
}
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

  $db->free($res_bulletin);
  $db->close();


  //entete des bulletins
function pdf_pagehead_moyen(&$pdf, $onglet_salarie){
    global $mysoc,$conf, $db, $fk_salarie, $id_salarie, $mois, $annee, $id_accord_etab, $info_soc;


    $bulletin_sql = "SELECT * FROM ".MAIN_DB_PREFIX."bulletin WHERE fk_salarie=".$fk_salarie." AND annee=".$annee." AND mois=".$mois;
      $rest_bulletin = $db->query($bulletin_sql);//= $db->query($covSql);
      $bulletin_obj = $db->fetch_object($rest_bulletin);

      $bulletin_soc = "SELECT * FROM ".MAIN_DB_PREFIX."societe WHERE rowid=".$bulletin_obj->fk_societe;
      $rest_bulletin_soc = $db->query($bulletin_soc);//= $db->query($covSql);
      $societe_Salarie = $db->fetch_object($rest_bulletin_soc);

      $y = $pdf->GetY();
      $debut = DOL_DOCUMENT_ROOT;
      $tab = explode("/",$debut);
      $logodir = $conf->mycompany->dir_output;
      $logo_server = $logodir.'/logos/'.$mysoc->logo;
      if($info_soc->societe_mere == 0){
        $logo_1 = $tab[0].'/'.$tab[1].'/'.$tab[2].'/'.$tab[3].($tab[4]?'/'.$tab[4]:'').'/documents/societe/'.$bulletin_obj->fk_societe.'/logos/'.$bulletin_obj->logo_societe;
        $logo_2 = $tab[0].'/'.$tab[1].'/dolibarr_documents/societe/'.$bulletin_obj->fk_societe.'/logos/'.($bulletin_obj->logo_societe?$bulletin_obj->logo_societe:"vide.png");
    
        $pdf->SetFillColor(143, 39, 51);
        $pdf->SetY(4);
         $pdf->SetX(0);
         $pdf->Cell($pdf->getPageWidth(),16, "",1,0,0,true);
    
         //cadre blanc pour logo
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetY(5);
        $pdf->SetX(20);
        $pdf->Cell(35,13, "",1,0,0,true);
    
        if(is_readable($logo_2)){
          $pdf->Image($logo_2,20,5, 35,13);
          $y = $pdf->GetY()+2;
        }elseif(is_readable($logo_1)){
          $pdf->Image($logo_1,20,5, 35,13);
          $y = $pdf->GetY()+2;
        }else{
          $pdf->SetFont('Helvetica','B',16);
          $pdf->SetY($y-4);
          $pdf->SetX(20);
          $pdf->SetFillColor(255, 255, 255);
          $pdf->MultiCell(25,11,utf8_decode("Logo"),0,'C', true);
          $y += 2;
        }
    
    
      }else{
          $logodir = $conf->mycompany->dir_output;
          if (!empty($conf->mycompany->multidir_output[$object->entity])) {
            $logodir = $conf->mycompany->multidir_output[$object->entity];
          }
          if (empty($conf->global->MAIN_PDF_USE_LARGE_LOGO)) {
            $logo = $logodir.'/logos/thumbs/'.$mysoc->logo_small;
          } else {
            $logo = $logodir.'/logos/'.$mysoc->logo;
          }
        
          $pdf->SetFillColor(143, 39, 51);
          $pdf->SetY(4);
           $pdf->SetX(0);
           $pdf->Cell($pdf->getPageWidth(),16, "",1,0,0,true);
        
           //cadre blanc pour logo
            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetY(5);
            $pdf->SetX(20);
            $pdf->Cell(35,13, "",1,0,0,true);
        
            if(is_readable($logo)){
              $pdf->Image($logo,20,5, 35,13);
              $y = $pdf->GetY()+2;
            }else{
              $pdf->SetFont('Helvetica','B',16);
              $pdf->SetY($y-4);
              $pdf->SetX(20);
              $pdf->SetFillColor(255, 255, 255);
              $pdf->MultiCell(25,11,utf8_decode("Logo"),0,'C', true);
              $y += 2;
            }
      }
	  $y = 12;
	$y_salarie = $y;
	$x = 12;

	//espace pris par le logo
	$y += 12;
	//petit rectangle à gauche
	$pdf->SetFillColor(246, 246, 246);
	$pdf->SetLineWidth(0.1);
	$pdf->SetDrawColor(50, 50, 50);
	$pdf->SetY($y);
   $pdf->SetX($x);
   $pdf->MultiCell(90,45, "",0,0,true);

   //Informations dans le rectangle
   $y += 2;
   $pdf->SetTextColor(0, 0, 0);
   $pdf->SetFont('Helvetica','B',9);
	$pdf->SetY($y);
	$pdf->SetX($x);
	$pdf->MultiCell(89,5,utf8_decode("Etablissement : ".$bulletin_obj->nom_societe),0,'L');

   $y += 7;
	$pdf->SetTextColor(0, 0, 0);
   $pdf->SetFont('Helvetica','',9);
	$pdf->SetY($y);
	$pdf->SetX($x);
	$pdf->MultiCell(89,3,utf8_decode($societe_Salarie->address),0,'L');
		$pays_Sql = "SELECT label FROM ".MAIN_DB_PREFIX."c_country where rowid=".$societe_Salarie->fk_pays;
		$pays_Result = $db->query($pays_Sql);
		if(!empty($obj_user->fk_country))
			$pays = $db->fetch_object($pays_Result)->label;

   $y = $pdf->getY();
	$pdf->SetTextColor(0, 0, 0);
	$pdf->SetY($y);
	$pdf->SetX($x);
	$pdf->MultiCell(89,5,utf8_decode($societe_Salarie->town." ".$pays),0,'L');

	if($societe_Salarie->siren){
		$y += 5;
		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetY($y);
		$pdf->SetX($x);
		$pdf->MultiCell(89,5,utf8_decode($societe_Salarie->siren),0,'L');
	}

	$tel = $societe_Salarie->phone;
	if(empty($tel))
		$tel = $societe_Salarie->fax;
	else $tel .= " / ".$societe_Salarie->fax;

	if($tel){
		$y += 5;
		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetY($y);
		$pdf->SetX($x);
		$pdf->Cell(89,5,utf8_decode("Tel : ".$tel),0,'L');
	}

	if($societe_Salarie->email){
		$y += 5;
		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetY($y);
		$pdf->SetX($x);
		$pdf->Cell(89,5,utf8_decode("Email : ".$societe_Salarie->email),0,'L');
	}
	if($societe_Salarie->url){
		$y += 5;
		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetY($y);
		$pdf->SetX($x);
		$pdf->Cell(89,5,utf8_decode("Web : ".$societe_Salarie->url),0,'L');
	}


   $convention = $bulletin_obj->nom_convention;
   $y += 5;
	$pdf->SetTextColor(0, 0, 0);
	$pdf->SetY($y);
	$pdf->SetX($x);
	$pdf->MultiCell(100,3,utf8_decode("Conv. Coll. : ".$convention),0,'L');

	//information à gauche en bas du petit rectangle
   $y = 69;
   /*$pdf->SetFont('Helvetica','B',8);

	$pdf->SetTextColor(0, 0, 0);
	$pdf->SetY($y);
	$pdf->SetX($x);
	$au = cal_days_in_month(CAL_GREGORIAN, $mois, $annee);
	$pdf->MultiCell(100,3,utf8_decode("Payé le : ".$au."-".$mois."-".$annee." par Virement"),0,'L');

   $y += 4;
   if(!empty($bulletin_obj->banque)){
	$pdf->SetTextColor(0, 0, 0);
	$pdf->SetY($y);
	$pdf->SetX($x);
	$pdf->MultiCell(100,3,utf8_decode($bulletin_obj->banque." : ".$bulletin_obj->compte),0,'L');
   }*/
	//Les information sur le salarié
   //$y += 4;
	$x = 100 + 3;
	$pdf->SetY($y_salarie-4);
	$pdf->SetX($x);
	$pdf->SetTextColor(255, 255, 255);
	$pdf->SetFont('Helvetica','B',18);

	$pdf->MultiCell($pdf->GetPageWidth() - 100 - 12-5,5,utf8_decode("Bulletin De Paie"),0,'C');


	$pdf->SetTextColor(255, 255, 255);
	$pdf->SetY($y_salarie+4);
	$pdf->SetX($x);
	$du = "01-".$mois."-".$annee;
  $au = cal_days_in_month(CAL_GREGORIAN, $mois, $annee);
	$pdf->SetFont('Helvetica','',8);
	$pdf->MultiCell($pdf->GetPageWidth() - 100 - 12-5,3,utf8_decode("Période du :   ".$du."   au ".$au."-".$mois."-".$annee),0,'C');


	$x = 100 + 7;
	$y_salarie = $pdf->getY() + 6;
	$pdf->SetY($y_salarie);
	$pdf->SetX($x);
	$pdf->SetTextColor(0, 0, 70);
	$pdf->SetFont('Helvetica','B',14);
	$pdf->MultiCell($pdf->GetPageWidth() - 100 - 12 -12 - 5,3,utf8_decode($bulletin_obj->prenom." ".$bulletin_obj->nom),0,'L');

	$x = 100 + 7;
	$y_salarie += 6;
	$pdf->SetY($y_salarie);
	$pdf->SetX($x);
	$pdf->SetTextColor(0, 0, 0);
	$pdf->SetFont('Helvetica','B',8);
	$pdf->MultiCell($pdf->GetPageWidth() - 100 - 12 -12 - 8,3,utf8_decode($bulletin_obj->addresse),0,'L');


	$y_salarie += 5;
	$pdf->SetY($y_salarie);
	$pdf->SetX($x);
	$pdf->SetTextColor(0, 0, 0);
	$pdf->MultiCell($pdf->GetPageWidth() - 100 - 12 -12 - 8,3,utf8_decode($bulletin_obj->ville." ".$bulletin_obj->pays),0,'L');

//ecart entre information
//$y_salarie += 6;


$x = 100 + 7;
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('Helvetica','',9);
$y_salarie += 5;
   $pdf->SetY($y_salarie);
   $pdf->SetX($x);
$pdf->Cell(35 - 5,3,utf8_decode("Matricule "),0,0,'L');
$pdf->SetTextColor(0, 0, 70);
$pdf->SetFont('Helvetica','',9);
$pdf->SetX($x + 45);
$pdf->Cell($pdf->GetPageWidth() - 100 - 12 -12 - 5,3,utf8_decode($bulletin_obj->matricule),0,1,'L');

$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('Helvetica','',9);
$y_salarie += 6;
$pdf->SetY($y_salarie);
$pdf->SetX($x);
$pdf->Cell(35 - 5,3,utf8_decode("Catégorie "),0,0,'L');
$categ = $bulletin_obj->categorie."".($bulletin_obj->echelon?"==>".$bulletin_obj->echelon:"");

$pdf->SetTextColor(0, 0, 70);
$pdf->SetFont('Helvetica','',9);
$pdf->SetX($x + 45);
$pdf->Cell($pdf->GetPageWidth() - 100 - 12 -12 - 5,3,utf8_decode($categ),0,1,'L');
	$pdf->SetTextColor(0, 0, 0);
	$pdf->SetFont('Helvetica','',9);
	$y_salarie += 6;
	   $pdf->SetY($y_salarie);
	   $pdf->SetX($x);
	$pdf->Cell(35 - 5,3,utf8_decode("Fonction "),0,0,'L');
	$fonction = $obj_user->job;
	$pdf->SetTextColor(0, 0, 70);
	$pdf->SetFont('Helvetica','',9);
	$pdf->SetX($x + 45);
	$pdf->Cell($pdf->GetPageWidth() - 100 - 12 -12 - 5,3,utf8_decode($bulletin_obj->fonction),0,1,'L');

	$pdf->SetTextColor(0, 0, 0);
	$pdf->SetFont('Helvetica','',9);
	$y_salarie += 6;
	   $pdf->SetY($y_salarie);
	   $pdf->SetX($x);
	$pdf->Cell(35 - 5,3,utf8_decode("Date embauche "),0,0,'L');
	$date_embauche = $obj_user->dateemployment;
	$pdf->SetTextColor(0, 0, 70);
	$pdf->SetFont('Helvetica','',9);
	$pdf->SetX($x + 45);
	$pdf->Cell($pdf->GetPageWidth() - 100 - 12 -12 - 5,3,utf8_decode($bulletin_obj->date_embauche),0,1,'L');

	/*$pdf->SetTextColor(0, 0, 0);
	$pdf->SetFont('Helvetica','',9);
	$y_salarie += 6;
	   $pdf->SetY($y_salarie);
	   $pdf->SetX($x);
	$pdf->Cell(35 - 5,3,utf8_decode("Contrat "),0,0,'L');
	$date_embauche = $obj_user->dateemployment;
	$pdf->SetTextColor(0, 0, 70);
	$pdf->SetFont('Helvetica','',9);
	$pdf->SetX($x + 45);
	$pdf->Cell($pdf->GetPageWidth() - 100 - 12 -12 - 5,3,utf8_decode($bulletin_obj->contrat),0,1,'L');
	*/
	$pdf->SetTextColor(0, 0, 0);
	$pdf->SetFont('Helvetica','',9);
	$y_salarie += 6;
	   $pdf->SetY($y_salarie);
	   $pdf->SetX($x);
	$pdf->Cell(35 - 5,3,utf8_decode("Niveau salarié "),0,0,'L');
	$date_embauche = $obj_user->dateemployment;
	$pdf->SetTextColor(0, 0, 70);
	$pdf->SetFont('Helvetica','',9);
	$pdf->SetX($x + 45);
	$pdf->Cell($pdf->GetPageWidth() - 100 - 12 -12 - 5,3,utf8_decode($bulletin_obj->type_salarie),0,1,'L');


  }


  function pdf_ibspagefoot(&$pdf, $marge_droite, $marge_basse)
  {
    global $db, $conf, $user, $mysoc, $hookmanager, $id_societe;
    //$formcompany = new FormCompany($db);

    $societe_Sql = "SELECT * FROM ".MAIN_DB_PREFIX."societe where rowid=".$id_societe;
    $societe_Result = $db->query($societe_Sql);
    $societe_obj = $db->fetch_object($societe_Result);

    $line = '';
    $reg = array();


    $line1 = "";
    $lineTel = "";
    $lineEmail = "";
    $lineAdress = "";
    $line3 = "";
    $line4 = "";
    $identProf1 = "";
    $identProf2 = "";
    $identProf3 = "";
    $identProf4 = "";
    //!empty($conf->global->MAIN_INFO_SOCIETE_NOM) ? $conf->global->MAIN_INFO_SOCIETE_NOM : ''.'"'.(empty($conf->global->MAIN_INFO_SOCIETE_NOM)

    $lineAdress = !empty($mysoc->address) ? $mysoc->address : '';
	//$lineAdress .= ($lineAdress? "\n":"").!empty($mysoc->zip) ? $mysoc->zip : '';
	//$lineAdress .= ($lineAdress? "\n":"").!empty($mysoc->town) ? $mysoc->town : '';

	$lineTel .= ($lineTel ? "\n":"").(!empty($mysoc->phone) ? "Tel :".$mysoc->phone : '');
	$lineTel .= ($lineTel ? "\n":"").(!empty($mysoc->fax) ? "Fax :".$mysoc->fax : '');
	$lineEmail.= ($lineEmail? "\n":"").(!empty($mysoc->email) ? $mysoc->email : '');
	$lineEmail.= ($lineEmail? "\n":"").(!empty($mysoc->url) ? $mysoc->url : '');

	// ProfId1
	   $identProf1 .= dol_escape_htmltag(!empty($mysoc->idprof1) ? $mysoc->idprof1 : '');


	// ProfId2
		$identProf2 .= dol_escape_htmltag(!empty($mysoc->idprof2) ? $mysoc->idprof2 : '');

	// ProfId3
		$identProf3 .= dol_escape_htmltag(!empty($mysoc->idprof3) ? $mysoc->idprof3 : '');

		$identProf4 = ($identProf1?$identProf1."\n":"").($identProf2?$identProf2."\n":"").($identProf3?$identProf3."\n":"");
  //(!empty($conf->global->MAIN_INFO_SOCIETE_GENCODE) ? $conf->global->MAIN_INFO_SOCIETE_GENCODE : '')))
  $pdf->SetFillColor(143, 39, 51);
				$pdf->SetLeftMargin(0);
				$pdf->SetY($pdf->GetY());
				$pdf->MultiCell($pdf->getPageWidth(), 13, "",0,'',true);
				$pdf->SetTextColor(255, 255, 255);

    $pdf->SetY(-$marge_basse);
    $y = $pdf->GetY()+1;
    $pdf->line(12,$pdf->GetY(),$pdf->GetPageWidth()-12,$pdf->GetY());

        $pdf->Image(DOL_DOCUMENT_ROOT.'/paiementsalaire/doc/icone_folder/phone.png',13,$y+1, 6,6);
        $pdf->SetLeftMargin($marge_droite+6);
        $pdf->SetY($y);
        $pdf->MultiCell(38,4, utf8_decode($lineTel),0,'L');

        $pdf->Image(DOL_DOCUMENT_ROOT.'/paiementsalaire/doc/icone_folder/envelope.png',58,$y+1, 6,6);
        $pdf->SetLeftMargin(64);
        $pdf->SetY($y);
        $pdf->MultiCell(38,4, utf8_decode($lineEmail),0,'L');

        $pdf->Image(DOL_DOCUMENT_ROOT.'/paiementsalaire/doc/icone_folder/locationMap.png',103,$y+1, 6,6);
        $pdf->SetLeftMargin(109);
        $pdf->SetY($y);
        $pdf->MultiCell(38,4, utf8_decode($lineAdress),0,'L');

        $pdf->Image(DOL_DOCUMENT_ROOT.'/paiementsalaire/doc/icone_folder/autre.png',148,$y+1, 6,6);
        $pdf->SetLeftMargin(154);
        $pdf->SetY($y);
        $pdf->MultiCell(40,4, utf8_decode($identProf4),0,'L');


  }
