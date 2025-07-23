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
//$fk_salarie = GETPOST("fk_salarie");
$id_convention = GETPOST("id_convention", "int");


if($id_societe){

  $mois_tab = array(" janvier "," février "," mars "," avril "," mai "," juin "," juillet "," août "," septembre "," octobre "," novembre "," décembre ");

  $societe_Sql = "SELECT * FROM ".MAIN_DB_PREFIX."societe where rowid=".$id_societe;
	$societe_Result = $db->query($societe_Sql);
	$societe_Salarie = $db->fetch_object($societe_Result);

  $sql_soc = "SELECT societe_mere FROM ".MAIN_DB_PREFIX."salairepaie_societe WHERE fk_societe=".$id_societe;
		$result_soc = $db->query($sql_soc);
		if($result_soc)
			$info_soc = $db->fetch_object($result_soc);
  
  $bulletin_sql = "SELECT * FROM ".MAIN_DB_PREFIX."bulletin WHERE annee=".$annee." AND mois=".$mois." AND fk_societe=".$id_societe;
  $bulletin_sql .= " ORDER BY rowid";
    $res_bulletin = $db->query($bulletin_sql);
        if($res_bulletin){
          $num_all = $db->num_rows($res_bulletin);
          if ($num_all > 0){            

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
      return pdf_pagehead_avance($this, "");
    }


    // Footer
    function Footer() {

      // Positionnement à 1,5 cm du bas
      $this->SetY(-4);

      // Police Arial italique 8
      $this->SetFont('Helvetica','B',7);
      // Numéro de page, centré (C)
      //$this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
     // $this->line(12,$this->GetY(),$this->GetPageWidth()-12,$this->GetY());

      return pdf_ibspagefoot_avance($this, 13, 4);

    }
        // On active la classe une fois pour toutes les pages suivantes
    // Format portrait (>P) ou paysage (>L), en mm (ou en points > pts), A4 (ou A5, etc.)
    function SetPage($num) {
      $this->page = $num;
  }
  }

  $pdf = new PDF('P','mm','A4');
  // Nouvelle page A4 (incluant ici logo, titre et pied de page)
  //$pdf->AddPage();
  // Polices par défaut : Helvetica taille 9
  $pdf->SetFont('Helvetica','',9);
  // Couleur par défaut : noir
  $pdf->SetTextColor(0);
  // Compteur de pages {nb}
  $pdf->AliasNbPages();
  
          $suivant = 0;
          $num_all = $db->num_rows($res_bulletin);
          while ($suivant < ($num_all)){
              $obj_bulletin = $db->fetch_object($res_bulletin);
              $fk_salarie = $obj_bulletin->fk_salarie;

              //Objet Utilisateur
            if(!empty($obj_bulletin->fk_salarie )){
              $sql_sal = "SELECT * FROM ".MAIN_DB_PREFIX."salarie WHERE rowid=".$obj_bulletin->fk_salarie;
              $res_sal = $db->query($sql_sal);
              if($res_sal){
                  $obj_salarie = $db->fetch_object($res_sal);
                  $user_Result = $db->query($user_Sql);
                  $obj_user = $db->fetch_object($user_Result);
  
                  $user_Sql = "SELECT * FROM ".MAIN_DB_PREFIX."user where rowid=".$obj_salarie->fk_user;
                $user_Result = $db->query($user_Sql);
                $obj_user = $db->fetch_object($user_Result);

            //Le grand rectangle
            $pdf->SetLineWidth(0.2);
            $pdf->SetDrawColor(50, 50, 50);
            $pdf->SetLeftMargin(4);
            $pdf->SetTopMargin(4);
            $pdf->SetRightMargin(4);

            //Grand cadre
            $pdf->SetFillColor(254, 254, 254);
            $x = 4;
            $y = 4;
            $pdf->SetY($y);
            $pdf->SetX($x);
            $pdf->MultiCell($pdf->GetPageWidth()-8, $pdf->GetPageHeight()-8, "",1,0,true);

            $pdf->SetLineWidth(0.1);

                /*if($suivant > 0){
                  $pdf->AddPage();
                  $pdf->AliasNbPages();


                }*/

                //Information de la société
     //Premier rectangle "Bulletin de paie
     $pdf->SetFillColor(173, 206, 230);
     $pdf->SetTextColor(0);
     $pdf->SetFont('Helvetica','B',7);
     $x = 4.5;
     $y = 4;
     $pdf->SetY($y);
     $pdf->SetX($x);
     $pdf->MultiCell($pdf->GetPageWidth()-9,5, "BULLETIN DE PAIE",0,"C",true);

     global $mysoc,$conf;
     $debut = DOL_DOCUMENT_ROOT;
     $tab = explode("/",$debut);
     $logodir = $conf->mycompany->dir_output;
     $logo_server = $logodir.'/logos/'.$mysoc->logo;
     if($info_soc->societe_mere == 0){
       $logo_1 = $tab[0].'/'.$tab[1].'/'.$tab[2].'/'.$tab[3].($tab[4]?'/'.$tab[4]:'').'/documents/societe/'.$obj_bulletin->fk_societe.'/logos/'.$obj_bulletin->logo_societe;
           $logo_2 = $tab[0].'/'.$tab[1].'/dolibarr_documents/societe/'.$obj_bulletin->fk_societe.'/logos/'.($obj_bulletin->logo_societe?$obj_bulletin->logo_societe:"vide.png");
 
       if(is_readable($logo_2)){
         ///home/dolites/public_html
         $pdf->Image($logo_2,4.5,13, 50,25);
       }else if(is_readable($logo_1)){
         $pdf->Image($logo_1,4.5,13, 50,25);
   
       }else{
   
         //print $logo_1.'***'.$logo_2;
 
         $pdf->SetFont('Helvetica','B',16);
         $pdf->SetY(13);
         $pdf->SetX(4.5);
         $pdf->MultiCell(50,25,utf8_decode("Logo"),0,'C');
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
       
       if(is_readable($logo)){
         ///home/dolites/public_html
         $pdf->Image($logo,4.5,13, 50,25);
       }else{
   
         
         $pdf->SetFont('Helvetica','B',16);
         $pdf->SetY(13);
         $pdf->SetX(4.5);
         $pdf->MultiCell(50,25,utf8_decode("Logo"),0,'C');
       }
     }
 
     $x = 4.5;
     $y = 13;
     $pdf->SetY($y);
     $pdf->SetX($x);
     $pdf->MultiCell(50, 25, "",0,0,false);
 
     //Information de la société
     //Premier rectangle "Bulletin de paie
     $x = 125;
     //Nom
     $pdf->SetTextColor(0);
     $pdf->SetFont('Helvetica','B',7);
     $y = 20;
     $pdf->SetY($y);
     $pdf->SetX($x);
     $pdf->MultiCell(120,5, utf8_decode(mb_strtoupper($obj_bulletin->nom_societe)),0,"L",false);
 
     //Adresse
     if($societe_Salarie->adress){
       $y += 5;
       $pdf->SetY($y);
       $pdf->SetX($x);
       $pdf->MultiCell(120,5, utf8_decode(mb_strtoupper($societe_Salarie->adress)),0,"L",false);
     }
 
     //Tel
     $tel = $societe_Salarie->fax;
     if($tel){
       if($societe_Salarie->phone)
         $tel .= " | ".$societe_Salarie->phone;
     }else if($societe_Salarie->phone)
           $tel = $societe_Salarie->phone;
 
     
     if($tel){
       $y += 5;
       $pdf->SetY($y);
       $pdf->SetX($x);
       $pdf->MultiCell(120,5, utf8_decode($tel),0,"L",false);
     }
 
     //Email
     if($societe_Salarie->email){
       $y += 5;
       $pdf->SetY($y);
       $pdf->SetX($x);
       $pdf->MultiCell(120,5, utf8_decode($societe_Salarie->email),0,"L",false);
     }


    //Information du salarié partie 1
    //$pdf->SetFont('Helvetica','B',9);

    $x = 4;
    $y = 43;
    $pdf->SetY($y);
    $pdf->SetX($x);
    $pdf->MultiCell(120,5, utf8_decode("MATRICULE : " .mb_strtoupper($obj_bulletin->matricule)),0,"L",false);

    //Emploi
    $y += 4;
    $pdf->SetY($y);
    $pdf->SetX($x);
    $pdf->MultiCell(120,5, utf8_decode("EMPLOI  : " .mb_strtoupper($obj_bulletin->fonction)),0,"L",false);

    //Statut
    $statut = "Actif";
    if(empty($obj_bulletin->calcul_salaire) || $obj_bulletin->calcul_salaire == "non" || $obj_bulletin->calcul_salaire == "Non")
      $statut = "Non actif";
    $y += 4;
    $pdf->SetY($y);
    $pdf->SetX($x);
    $pdf->MultiCell(120,5, utf8_decode("STATUT : " .mb_strtoupper($statut)),0,"L",false);

    //Ancienneté
    $date = null;
    if($obj_user->dateemployment)
      $date = $obj_user->dateemployment;

    if($obj_salarie->date_anciennete)
      $date = $obj_salarie->date_anciennete;

      $date_debut = new DateTime($date); 
      $date_fin = new DateTime($annee."-".$mois."-01"); //date du bulletin
      
      $interval = $date_debut->diff($date_fin);
      
    $anciennete = "Nouveau";
    if($interval->y != 0)
      $anciennete = $interval->y." an(s)";
    if($interval->m != 0 && $interval->y != 0)
      $anciennete .= " et ".$interval->m." mois";
    elseif($interval->m != 0)
      $anciennete = $interval->m." mois";

    $y += 4;
    $pdf->SetY($y);
    $pdf->SetX($x);
    $pdf->MultiCell(120,5, utf8_decode("ANCIENNETE : " .mb_strtoupper($anciennete)),0,"L",false);

  //Horaire
    $salSql = "SELECT jour FROM ".MAIN_DB_PREFIX."salarie_nombre_jour_travaille where annee=".$annee." AND mois=".$mois." AND fk_salarie=".$obj_bulletin->fk_salarie;
    $result = $db->query($salSql);
    $nb_jours = $db->fetch_object($result)->jour;
    //$nb_jours = cal_days_in_month(CAL_GREGORIAN, $mois, $annee);:
    $y += 4;
    $pdf->SetY($y);
    $pdf->SetX($x);
    $pdf->MultiCell(120,5, utf8_decode("JOURS TRAVAILLÉS: " .$nb_jours),0,"L",false);

    $pdf->SetLeftMargin(63);
    $nb_total_jour = cal_days_in_month(CAL_GREGORIAN, $mois, $annee);
    $heur_normal = 173.33;
    if($nb_jours != $nb_total_jour)
      $heur_normal = round(($nb_jours*$heur_normal)/$nb_total_jour, 2);

    $y += 4;
    $y_limit = $pdf->GetY();
    $pdf->SetY($y);
    $pdf->SetX($x);
    $pdf->MultiCell(120,5, utf8_decode("HORAIRE MENSUEL: " .$heur_normal. " H"),0,"L",false);
    //Information du salarié partie 2
    $x = 125;
    $y = 10;
    $pdf->SetY($y);
    $pdf->SetX($x);
    $pdf->MultiCell(120,5, utf8_decode(mb_strtoupper($mois_tab[$mois-1]." ".$annee)),0,"L",false);

    //salarie
    $sexe = "MR";
    if($obj_bulletin->sexe == "feminin" || $obj_bulletin->sexe == "Feminin" || $obj_bulletin->sexe == "féminin" || $obj_bulletin->sexe == "Féminin")
      $sexe = "MME";
    if($sexe == "MME" && ($obj_bulletin->situation_familiale == "Célibataire" || $obj_bulletin->situation_familiale == "Celibataire" || $obj_bulletin->situation_familiale == "celibataire" || $obj_bulletin->situation_familiale == "célibataire" 
      || $obj_bulletin->situation_familiale == "divorce" || $obj_bulletin->situation_familiale == "divorcé" || $obj_bulletin->situation_familiale == "Divorcé" || $obj_bulletin->situation_familiale == "Divorce"))
      $sexe = "MLLE";
    
    $y = 43;
    $pdf->SetY($y);
    $pdf->SetX($x);
    $pdf->MultiCell(120,5, utf8_decode(mb_strtoupper($sexe." ".$obj_bulletin->nom." ".$obj_bulletin->prenom)),0,"L",false);

    //Adresse
    $y += 4;
    $pdf->SetY($y);
    $pdf->SetX($x);
    $pdf->MultiCell(120,5, utf8_decode(mb_strtoupper($obj_bulletin->adresse." ".$obj_bulletin->ville)),0,"L",false);

    //Téléphone
    $y += 4;
    $pdf->SetY($y);
    $pdf->SetX($x);
    $pdf->MultiCell(120,5, utf8_decode(mb_strtoupper($obj_bulletin->tel)),0,"L",false);

    $categ = $obj_bulletin->categorie."".($obj_bulletin->echelon?"==>".$obj_bulletin->echelon:"");
    $y += 4;
    $pdf->SetY($y);
    $pdf->SetX($x);
    $pdf->MultiCell(120,5, utf8_decode(mb_strtoupper($categ)),0,"L",false);

    $enfant = "";
    if($obj_bulletin->nombre_enfant != 0 || $obj_bulletin->nombre_enfant_hand != 0)
      $enfant .= ($obj_bulletin->nombre_enfant + $obj_bulletin->nombre_enfant_hand)." enfant(s)";

    $y += 4;
    $pdf->SetY($y);
    $pdf->SetX($x);
    $pdf->MultiCell(120,5, utf8_decode(mb_strtoupper($obj_bulletin->situation_familiale." ".$enfant)),0,"L",false);

    $sal_categ = 0;

//Deuxième rectangle
$pdf->SetFillColor(173, 206, 230);
$pdf->SetTextColor(0);
$pdf->SetFont('Helvetica','B',7);
$x = 4.5;
$y_rec = $y_limit + 4;

$y = $y_limit + 4;
$pdf->SetY($y);
$pdf->SetX($x);
$pdf->Cell(($pdf->GetPageWidth()-8)/2-0.5,5, utf8_decode("ELEMENTS DE REMUNERATION"),0, "", "L",true);

$y = $y_limit + 4;
$y_limit = $pdf->GetY();
$pdf->SetY($y);
//$pdf->SetDrawColor(255, 255, 255);
$x = ($pdf->GetPageWidth()-8)/2 + 4.25;
$pdf->SetX($x);
$pdf->Cell(25,5, "Base",0, "", "C",true);

$x += 25.25;
$pdf->SetX($x);
$pdf->Cell(25,5, "Taux",0, "", "C",true);

$x += 25.25;
$pdf->SetX($x);
$pdf->Cell(49.5,5, "Montant",0, "", "C",true);


//Salaire de base
    $x = 4;
    $pdf->SetTextColor(0);
    $pdf->SetFont('Helvetica','B',7);
    $y += 5;
    $pdf->SetY($y);
    $pdf->SetX($x);
    $pdf->Cell(($pdf->GetPageWidth()-8)/2,5, utf8_decode(mb_strtoupper("Salaire de Base")),0, "", "L",false);

    $pdf->SetTextColor(0);

    $pdf->SetY($y);
    $x = ($pdf->GetPageWidth()-8)/2 + 4.25;
    $pdf->SetX($x);
    $pdf->Cell(25,5, apres_virgule($db, $id_societe, $obj_bulletin->salaire_base, 2),0, "", "C",false);

    $x += 25.25;
    $pdf->SetX($x);
    $pdf->Cell(25,5, ($obj_bulletin->pourcentage*100)."%",0, "", "C",false);

    $x += 25.25;
    $pdf->SetX($x);
    $pdf->Cell(50.5,5, apres_virgule($db, $id_societe, $obj_bulletin->salaire_base*$obj_bulletin->pourcentage, 2),0, "", "C",false);
    
    //Anciennete
    $bulletin_anc_sql = "SELECT * FROM ".MAIN_DB_PREFIX."bulletin_anciennete WHERE fk_bulletin=".$obj_bulletin->rowid;
      $res_bulletin_anc = $db->query($bulletin_anc_sql);
      $obj_bulletin_anc = $db->fetch_object($res_bulletin_anc);

    $x = 4;
    $y += 5;
    $pdf->SetY($y);
    $pdf->SetX($x);
    $pdf->Cell(($pdf->GetPageWidth()-8)/2,5, utf8_decode(mb_strtoupper($obj_bulletin_anc->libelle)),0, "", "L",false);

    $x = ($pdf->GetPageWidth()-8)/2 + 4.25;
    $pdf->SetX($x);
    $pdf->Cell(25,5, utf8_decode(apres_virgule($db, $id_societe, $obj_bulletin->salaire_base, 2)),0, "", "C",false);

    $x += 25.25;
    $pdf->SetX($x);
    $pdf->Cell(25,5, utf8_decode($obj_bulletin_anc->taux."%"),0, "", "C",false);

    $x += 25.25;
    $pdf->SetX($x);
    $pdf->Cell(50.5,5, apres_virgule($db, $id_societe, $obj_bulletin->salaire_base*$obj_bulletin_anc->taux/100, 2),0, "", "C",false);

      $somme_pr_ind += $obj_bulletin->salaire_base*$obj_bulletin_anc->taux/100;

    //les primes qui doivent être affichés sur le billetin
    $bulletin_pr_sql = "SELECT * FROM ".MAIN_DB_PREFIX."bulletin_prime WHERE fk_bulletin=".$obj_bulletin->rowid;;
    $bulletin_pr_res = $db->query($bulletin_pr_sql);
    if($bulletin_pr_res){
      $i = 0;
      $num = $db->num_rows($bulletin_pr_res);
      while ($i < $num){
        $obj_bulletin_pr = $db->fetch_object($bulletin_pr_res);
        if ($obj_bulletin_pr->affiche_bulletin)
        {
          $pr_sql = "SELECT * FROM ".MAIN_DB_PREFIX."primes WHERE rowid=".$obj_bulletin_pr->fk_prime;
          $pr_result = $db->query($pr_sql);
          $obj_pr = $db->fetch_object($pr_result);

          $x = 4;
          $y += 5;
          $pdf->SetY($y);
          $pdf->SetX($x);
          $pdf->Cell(($pdf->GetPageWidth()-8)/2,5, utf8_decode(mb_strtoupper($obj_bulletin_pr->libelle)),0, "", "L",false);

          $x = ($pdf->GetPageWidth()-8)/2 + 4.25;
          $pdf->SetX($x);
          $pdf->Cell(25,5, utf8_decode(apres_virgule($db, $id_societe, $obj_bulletin_pr->montant, 2)),0, "", "C",false);

          $x += 25.25;
          $pdf->SetX($x);
          $pdf->Cell(25,5, utf8_decode($obj_bulletin_pr->pourcentage."%"),0, "", "C",false);

          $x += 25.25;
          $pdf->SetX($x);
          $pdf->Cell(50.5,5, apres_virgule($db, $id_societe, $obj_bulletin_pr->montant*100/$obj_bulletin_pr->pourcentage, 2),0, "", "C",false);

          $somme_pr_ind += $obj_bulletin_pr->montant;
        }
          $i ++;
      }
    }


    //Primes Exceptionnelles
    $bulletin_pr_except_sql = "SELECT * FROM ".MAIN_DB_PREFIX."bulletin_prime_exceptionnelle WHERE fk_bulletin=".$obj_bulletin->rowid;
    $bulletin_pr_except_res = $db->query($bulletin_pr_except_sql);
    if($bulletin_pr_except_res){
      $j = 0;
      $num = $db->num_rows($bulletin_pr_except_res);
      while ($j < $num){
        $obj_bulletin_pr = $db->fetch_object($bulletin_pr_except_res);
        if ($obj_bulletin_pr->affiche_bulletin == 'oui' || $obj_bulletin_pr->affiche_bulletin == 'Oui')
        {

          $x = 4;
          $y += 5;
          $pdf->SetY($y);
          $pdf->SetX($x);
          $pdf->Cell(($pdf->GetPageWidth()-8)/2,5, utf8_decode(mb_strtoupper($obj_bulletin_pr->libelle)),0, "", "L",false);

          $x = ($pdf->GetPageWidth()-8)/2 + 4.25;
          $pdf->SetX($x);
          $pdf->Cell(25,5, utf8_decode(apres_virgule($db, $id_societe, $obj_bulletin_pr->montant, 2)),0, "", "C",false);

          $x += 25.25;
          $pdf->SetX($x);
          $pdf->Cell(25,5, utf8_decode($obj_bulletin_pr->pourcentage."%"),0, "", "C",false);

          $x += 25.25;
          $pdf->SetX($x);
          $pdf->Cell(50.5,5, apres_virgule($db, $id_societe, $obj_bulletin_pr->montant*100/$obj_bulletin_pr->pourcentage, 2),0, "", "C",false);

          $somme_pr_ind += $obj_bulletin_pr->montant;
        }
          $j ++;
      }
    }


    //Indemnités
    $bulletin_ind_sql = "SELECT * FROM ".MAIN_DB_PREFIX."bulletin_indemnite WHERE fk_bulletin=".$obj_bulletin->rowid;
      $bulletin_ind_res = $db->query($bulletin_ind_sql);
      if($bulletin_ind_res){
        $i = 0;
        $num = $db->num_rows($bulletin_ind_res);
        while ($i < $num){
          $obj_bulletin_ind = $db->fetch_object($bulletin_ind_res);
          if ($obj_bulletin_ind->affiche_bulletin = "Oui")
          {

            $x = 4;
          $y += 5;
          $pdf->SetY($y);
          $pdf->SetX($x);
          $pdf->Cell(($pdf->GetPageWidth()-8)/2,5, utf8_decode(mb_strtoupper($obj_bulletin_ind->libelle)),0, "", "L",false);

          $x = ($pdf->GetPageWidth()-8)/2 + 4.25;
          $pdf->SetX($x);
          $pdf->Cell(25,5, utf8_decode(apres_virgule($db, $id_societe, $obj_bulletin_ind->montant, 2)),0, "", "C",false);

          $x += 25.25;
          $pdf->SetX($x);
          $pdf->Cell(25,5, utf8_decode($obj_bulletin_ind->pourcentage."%"),0, "", "C",false);

          $x += 25.25;
          $pdf->SetX($x);
          $pdf->Cell(50.5,5, apres_virgule($db, $id_societe, $obj_bulletin_ind->montant*100/$obj_bulletin_ind->pourcentage, 2),0, "", "C",false);

            $somme_pr_ind += $obj_bulletin_ind->montant;
          }
            $i ++;
        }

      }


      //Sursalaire
      $y += 5;
      $x = 4;
    $pdf->SetY($y);
    $pdf->SetX($x);
    $pdf->Cell(($pdf->GetPageWidth()-8)/2,5, utf8_decode(mb_strtoupper("SURSALAIRE")),0, "", "L",false);

    $pdf->SetY($y);
    $x = ($pdf->GetPageWidth()-8)/2 + 4.25;
    $pdf->SetX($x);
    $pdf->Cell(25,5, apres_virgule($db, $id_societe, $obj_bulletin->sursalaire, 2),0, "", "C",false);

    $x += 25.25;
    $pdf->SetX($x);
    $pdf->Cell(25,5, ($obj_bulletin->pourcentage*100)."%",0, "", "C",false);

    $x += 25.25;
    $pdf->SetX($x);
    $pdf->Cell(50.5,5, apres_virgule($db, $id_societe, $obj_bulletin->sursalaire*$obj_bulletin->pourcentage, 2),0, "", "C",false);

    $valeur_heur_sup = 0;
       $base = 0;
       $bulletin_hs_sql = "SELECT * FROM ".MAIN_DB_PREFIX."bulletin_heure_sup WHERE fk_bulletin=".$obj_bulletin->rowid;
      $bulletin_hs_res = $db->query($bulletin_hs_sql);
      if($bulletin_hs_res){
        $m = 0;
        $num = $db->num_rows($bulletin_hs_res);
        while ($m < $num){
          $obj_bulletin_hs = $db->fetch_object($bulletin_hs_res);

          $x = 4;
          $y += 5;
          $pdf->SetY($y);
          $pdf->SetX($x);
          $pdf->Cell(($pdf->GetPageWidth()-8)/2,5, utf8_decode(mb_strtoupper($obj_bulletin_hs->nombre_heure_sup.'HS '.$obj_bulletin_hs->libelle)),0, "", "L",false);

          $x = ($pdf->GetPageWidth()-8)/2 + 4.25;
          $pdf->SetX($x);
          $pdf->Cell(25,5, utf8_decode(apres_virgule($db, $id_societe, $obj_bulletin_hs->base, 2)),0, "", "C",false);

          $x += 25.25;
          $pdf->SetX($x);
          $pdf->Cell(25,5, utf8_decode($obj_bulletin_hs->taux."%"),0, "", "C",false);

          $x += 25.25;
          $pdf->SetX($x);
          $pdf->Cell(50.5,5, apres_virgule($db, $id_societe, $obj_bulletin_hs->montant, 2),0, "", "C",false);

          $valeur_heur_sup += $obj_bulletin_hs->montant;
          $m ++;
          //print $obj_bulletin_hs->montant."<br>";
        }
      }

    $x = 4;
    $y = $pdf->GetY() + 5;
    $pdf->SetY($y_rec);
    $pdf->SetX($x);
    $pdf->Cell($pdf->GetPageWidth()-8, $y - $y_rec, "",1, "", "C",false);

  
/*
    $y += 5;
    $pdf->SetY($y);
    $pdf->SetX($x);
    $pdf->Cell(($pdf->GetPageWidth()-8)/2,5, utf8_decode("Congés payés"),0, "", "L",false);

    $y += 5;
    $pdf->SetY($y);
    $pdf->SetX($x);
    $pdf->Cell(($pdf->GetPageWidth()-8)/2,5, "Primes",0, "", "L",false);
*/
    


    //Montant brut
    $pdf->SetTextColor(0);
    $x = 4;
    $y += 1;
    $pdf->SetY($y);
    $pdf->SetX($x);
    $pdf->Cell(($pdf->GetPageWidth()-8)/2,5, "MONTANT BRUT", "TB", "", "L",false);

    //Valeur:
    $pdf->SetTextColor(0);
    $x += ($pdf->GetPageWidth()-8)/2;
    $pdf->SetY($y);
    $pdf->SetX($x);
    $pdf->Cell(($pdf->GetPageWidth()-8)/2,5, utf8_decode(apres_virgule($db, $id_societe, $obj_bulletin->salaire_brut, 2)), "TB", "", "C",false);

    //Cotisations sociales Obligatoires
    $pdf->SetTextColor(0);
    $x = 4.5;
    $y = $pdf->GetY() + 6;
    $pdf->SetY($y);
    $pdf->SetX($x);
    $pdf->Cell(($pdf->GetPageWidth()-8)/2-0.5,7, "COTISATIONS ET CONTRIBUTIONS SOCIALES OBLIGATOIRES",0, "", "L",true);

    //$pdf->SetDrawColor(255, 255, 255);
    $x = ($pdf->GetPageWidth()-8)/2 + 4.25;
    $pdf->SetX($x);
    $pdf->Cell(25,7, "Base",0, "", "C",true);

    $x += 25.25;
      $x_empl = $x;
    $pdf->SetX($x);
    $pdf->Cell(37.5,4, utf8_decode(mb_strtoupper("Employé")),0, "", "C",true);

    $x += 37.75;
    $pdf->SetX($x);
    $pdf->Cell(37,4, "EMPLOYEUR",0, "", "C",true);

    //Taux et montant de l'employer
    $x = $x_empl;
    $y += 4;
    $pdf->SetY($y);
        $pdf->SetX($x);
    $pdf->Cell(16,3, "Taux",0, "", "C",true);

    $x += 16.5;
    $pdf->SetX($x);
    $pdf->Cell(21,3, "Montant",0, "", "C",true);

    //Taux et montant de l'employeur
    $x = $x_empl + 37.75 ;
    $pdf->SetX($x);
    $pdf->Cell(16,3, "Taux",0, "", "C",true);

    $x += 16.5;
    $pdf->SetX($x);
    $pdf->Cell(20.5,3, "Montant",0, "", "C",true);


    //les prestations dont on doit afficher les détails
    $pdf->SetTextColor(0);
    $patro = 0;
    $bulletin_prest_sql = "SELECT * FROM ".MAIN_DB_PREFIX."bulletin_cotisation WHERE fk_bulletin=".$obj_bulletin->rowid;
    $bulletin_prest_res = $db->query($bulletin_prest_sql);
    if($bulletin_prest_res){
      $i = 0;
      $num = $db->num_rows($bulletin_prest_res);
      while ($i < $num){
        $obj_bulletin_prest = $db->fetch_object($bulletin_prest_res);
        $sql_prestation = "SELECT fk_organisme FROM ".MAIN_DB_PREFIX."type_prestation WHERE nature='obligatoire'";
        $result_prestation = $db->query($sql_prestation);
        $prestation = $db->fetch_object($result_bareme);
          
        $x = 4;
        $y = $pdf->GetY() + 5;
        $pdf->SetY($y);
        $pdf->SetX($x);
        $pdf->Cell(($pdf->GetPageWidth()-8)/2,5, utf8_decode(mb_strtoupper($obj_bulletin_prest->libelle)),0, "", "L",false);

        //$pdf->SetDrawColor(255, 255, 255);
        $x = ($pdf->GetPageWidth()-8)/2 + 4.25;
        $pdf->SetX($x);
        $pdf->Cell(25,5, utf8_decode(apres_virgule($db, $id_societe, $obj_bulletin->salaire_brut_cotisable, 2)),0, "", "R",false);


        //Taux et montant de l'employer
    $x = $x_empl;
    $pdf->SetX($x);
    $pdf->Cell(16,3, utf8_decode($obj_bulletin_prest->taux_employe."%"),0, "", "R",false);

    $x += 16.5;
    $pdf->SetX($x);
    $pdf->Cell(21,3, utf8_decode(apres_virgule($db, $id_societe, $obj_bulletin_prest->montant_employe, 2)),0, "", "R",false);

    //Taux et montant de l'employeur
    $x = $x_empl + 37.75 ;
    $pdf->SetX($x);
    $pdf->Cell(16,3, utf8_decode($obj_bulletin_prest->taux_employeur."%"),0, "", "R",false);

    $x += 16.5;
    $pdf->SetX($x);
    $pdf->Cell(20.5,3, utf8_decode(apres_virgule($db, $id_societe, $obj_bulletin_prest->montant_employeur, 2)),0, "", "R",false);


        /*$x += 25.25;
        $pdf->SetX($x);
        $pdf->Cell(25,5, utf8_decode($obj_bulletin_prest->taux_employe."%"),0, "", "R",false);

        $x += 25.25;
        $pdf->SetX($x);
        $pdf->Cell(25,5, utf8_decode(apres_virgule($db, $id_societe, $obj_bulletin_prest->montant_employe, 2)),0, "", "R",false);

        $x += 25.25;
        $pdf->SetX($x);
        $pdf->Cell(25,5, utf8_decode(apres_virgule($db, $id_societe, $obj_bulletin_prest->montant_employeur, 2)),0, "", "R",false);*/
        $retenu += $obj_bulletin_prest->montant_employe;
        $patro += $obj_bulletin_prest->montant_employeur;


          $i ++;
      }

    }

    //Total des cotisations
    $pdf->SetTextColor(0);
    $x = 4;
    $y += 5;
    $pdf->SetY($y);
    $pdf->SetX($x);
    $pdf->Cell(($pdf->GetPageWidth()-8)/2,5, "TOTAL DES COTISATIONS ET CONTRIBUTIONS SOCIALES OBLIGATOIRES", "TB", "", "L",false);

    //Valeur:
    $x += ($pdf->GetPageWidth()-8)/2 ;
    $pdf->SetY($y);
    $pdf->SetX($x);
    $pdf->Cell(63,5,  utf8_decode("-".apres_virgule($db, $id_societe, $retenu, 2)), "TB", "", "R",false);

    $x += 63;
    $pdf->SetY($y);
    $pdf->SetX($x);
    $pdf->Cell(38,5,  utf8_decode(apres_virgule($db, $id_societe, $patro, 2)), "TB", "", "R",false);

    /*//Cotisations sociales Facultatives
    $pdf->SetTextColor(255, 255, 255);
    $x = 4;
    $y = $pdf->GetY() + 6;
    $pdf->SetY($y);
    $pdf->SetX($x);
    $pdf->Cell(($pdf->GetPageWidth()-8)/2,5, "COTISATIONS ET CONTRIBUTIONS SOCIALES OBLIGATOIRES",0, "", "L",true);

    //$pdf->SetDrawColor(255, 255, 255);
    $x = ($pdf->GetPageWidth()-8)/2 + 4.25;
    $pdf->SetX($x);
    $pdf->Cell(25,5, "Base",0, "", "C",true);

    $x += 25.25;
    $pdf->SetX($x);
    $pdf->Cell(25,5, "Taux",0, "", "C",true);

    $x += 25.25;
    $pdf->SetX($x);
    $pdf->Cell(25,5, utf8_decode(mb_strtoupper("Employé")),0, "", "C",true);

    $x += 25.25;
    $pdf->SetX($x);
    $pdf->Cell(25,5, "EMPLOYEUR",0, "", "C",true);*/


    $retenu_its = 0;
      $bulletin_taxe_sql = "SELECT montant FROM ".MAIN_DB_PREFIX."bulletin_taxe WHERE fk_bulletin=".$obj_bulletin->rowid;
      $bulletin_taxe_res = $db->query($bulletin_taxe_sql);
      if($bulletin_taxe_res){
        $i = 0;
        $num = $db->num_rows($bulletin_taxe_res);
        while ($i < $num){
          $obj_bulletin_taxe = $db->fetch_object($bulletin_taxe_res);
              $retenu_its += $obj_bulletin_taxe->montant;

            $i ++;
        }

      }


    //Montant net AVANT ITS
    $pdf->SetTextColor(0);
    $x = 4;
    $y += 6;
    $pdf->SetY($y);
    $pdf->SetX($x);
    $pdf->Cell(($pdf->GetPageWidth()-8)/2,5, "MONTANT NET AVANT IMPOT SUR LE REVENU", "TB", "", "L",false);

    //Valeur:
    $x += ($pdf->GetPageWidth()-8)/2 + 0.25;
    $pdf->SetY($y);
    $pdf->SetX($x);
    $pdf->Cell(62.75,5,  utf8_decode(apres_virgule($db, $id_societe, $obj_bulletin->net_payer + $retenu_its, 2)), "TBR", "", "R",false);



  //Impot sur le revenu
  $pdf->SetTextColor(0);
      $x = 4.5;
      $y = $pdf->GetY() + 6;
      $pdf->SetY($y);
      $pdf->SetX($x);
      $pdf->Cell(($pdf->GetPageWidth()-8)/2,7, "IMPOT SUR LE REVENU",0, "", "L",true);

      //$pdf->SetDrawColor(255, 255, 255);
      $x = ($pdf->GetPageWidth()-8)/2 + 4.75;
      $pdf->SetX($x);
      $pdf->Cell(25,7, "Base",0, "", "C",true);

      $x += 25.25;
      $x_empl = $x;
      $pdf->SetX($x);
      $pdf->Cell(37.5,4, utf8_decode(mb_strtoupper("Employé")),0, "", "C",true);

      $y += 4;
      $pdf->SetY($y);
      $pdf->SetX($x_empl);
      $pdf->Cell(16,3, "Taux",0, "", "C",true);

      $x = $x_empl + 16.5;
      $pdf->SetX($x);
      $pdf->Cell(21,3, "Montant",0, "", "C",true);


      $pdf->SetTextColor(0);
      $bulletin_taxe_sql = "SELECT * FROM ".MAIN_DB_PREFIX."bulletin_taxe WHERE fk_bulletin=".$obj_bulletin->rowid;
      $bulletin_taxe_res = $db->query($bulletin_taxe_sql);
      if($bulletin_taxe_res){
        $i = 0;
        $num = $db->num_rows($bulletin_taxe_res);
        while ($i < $num){
          $obj_bulletin_taxe = $db->fetch_object($bulletin_taxe_res);

              $x = 4;
          $y = $pdf->GetY() + 5;
          $pdf->SetY($y);
          $pdf->SetX($x);
          $pdf->Cell(($pdf->GetPageWidth()-8)/2,5, utf8_decode(mb_strtoupper($obj_bulletin_taxe->libelle)),0, "", "L",false);

          //$pdf->SetDrawColor(255, 255, 255);
          $x = ($pdf->GetPageWidth()-8)/2 + 4.25;
          $pdf->SetX($x);
          $pdf->Cell(25,5, utf8_decode(apres_virgule($db, $id_societe, $obj_bulletin->salaire_brut_imposable, 2)),0, "", "R",false);

          $x += 25.25;
          $pdf->SetX($x);
          $pdf->Cell(16,5, utf8_decode($obj_bulletin_taxe->taux."%"),0, "", "R",false);

          $x += 16.5;
          $pdf->SetX($x);
          $pdf->Cell(21,5, utf8_decode(apres_virgule($db, $id_societe, $obj_bulletin_taxe->montant, 2)),0, "", "R",false);

            $i ++;
        }

      }

    $pdf->SetTextColor(0);
    $x = 4;
    $y += 6;
    $pdf->SetY($y);
    $pdf->SetX($x);
    $pdf->Cell(($pdf->GetPageWidth()-8)/2,5, "TOTAL DES IMPOTS SUR LE REVENU", "B", "", "L",false);

    //Valeur:
    $x += ($pdf->GetPageWidth()-8)/2 + 0.25;
    $pdf->SetY($y);
    $pdf->SetX($x);
    $pdf->Cell(62.5,5,  "-".utf8_decode(apres_virgule($db, $id_societe, $retenu_its, 2)), "B", "", "R",false);

//Remboursements et Avances/acompte
    $bulletin_avance = "SELECT * FROM ".MAIN_DB_PREFIX."bulletin_avance WHERE fk_bulletin=".$obj_bulletin->rowid;
   $bulletin_avance = $db->query($bulletin_avance);
   if($bulletin_avance){
      $num = $db->num_rows($bulletin_avance);
      if($num > 0){

      $pdf->SetTextColor(0);
      $x = 4.5;
      $y = $pdf->GetY() + 6;
      $pdf->SetY($y);
      $pdf->SetX($x);
      $pdf->Cell(($pdf->GetPageWidth()-8)/2,5, "REMBOURSEMENTS ET AVANCES/ACOMPTES",0, "", "L",true);

      $x = ($pdf->GetPageWidth()-8)/2 + 4.75;
      $pdf->SetX($x);
      $pdf->Cell(25,5, "Base",0, "", "C",true);

      $x += 25.25;
        $x_empl = $x;
      $pdf->SetX($x);
      $pdf->Cell(37.5,5, utf8_decode(mb_strtoupper("Employé")),0, "", "C",true);

      $x += 37.75;
      $pdf->SetX($x);
      $pdf->Cell(37,5, "EMPLOYEUR",0, "", "C",true);

      //Avance/acompte
      $pdf->SetTextColor(0);
      $somme_avance = 0;
      $i = 0;     
      while ($i < $num){
        $obj_bulletin_avance = $db->fetch_object($bulletin_avance);


        $x = 4;
          $y = $pdf->GetY() + 5;
          $pdf->SetY($y);
          $pdf->SetX($x);
          $pdf->Cell(($pdf->GetPageWidth()-8)/2,5, utf8_decode(mb_strtoupper($obj_bulletin_avance->libelle)),0, "", "L",false);

          //$pdf->SetDrawColor(255, 255, 255);
          $x = ($pdf->GetPageWidth()-8)/2 + 4.25;
          /*$pdf->SetX($x);
          $pdf->Cell(25,5, utf8_decode(apres_virgule($db, $id_societe, $obj_bulletin->salaire_brut_cotisable, 2)),0, "", "R",false);*/

          $x += 25.25;
          /*$pdf->SetX($x);
          $pdf->Cell(25,5, utf8_decode($obj_bulletin_prest->taux_employe."%"),0, "", "R",false);*/

          $pdf->SetX($x);
          $pdf->Cell(37.5,5, utf8_decode(apres_virgule($db, $id_societe, $obj_bulletin_avance->montant, 2)),0, "", "R",false);

          $x += 37.75;
          $pdf->SetX($x);
          $pdf->Cell(37,5, utf8_decode(apres_virgule($db, $id_societe, 0, 2)),0, "", "R",false);

        $somme_avance += $obj_bulletin_avance->montant;
        $i ++;
        //print $obj_bulletin_hs->montant."<br>";
      }
    
      //Total des Rembourssements ou avances/acomptes
      $pdf->SetTextColor(0);
      $x = 4;
      $y += 6;
      $pdf->SetY($y);
      $pdf->SetX($x);
      $pdf->Cell(($pdf->GetPageWidth()-8)/2,5, "TOTAL DES REMBOURSEMENTS ET AVANCES/ACOMPTES", "TB", "", "L",false);

      //Valeur:
      $x = ($pdf->GetPageWidth()-8)/2 + 4.25;
      $pdf->SetY($y);
      $pdf->SetX($x);
      $pdf->Cell(62.75,5,  utf8_decode("-".apres_virgule($db, $id_societe, $somme_avance, 2)), "TB", "", "R",false);

      $x += 62.75;
      $pdf->SetY($y);
      $pdf->SetX($x);
      $pdf->Cell(38,5,  utf8_decode(apres_virgule($db, $id_societe, 0, 2)), "TB", "", "R",false);
    }
  }

    //Net à payer
    $x = 4;
    $y += 6;
    $pdf->SetY($y);
    $pdf->SetX($x);
    $pdf->Cell(($pdf->GetPageWidth()-8)/2,5, utf8_decode(mb_strtoupper("MONTANT NET à PAYER")), "TB", "", "L",false);

    //Valeur:
    $x = ($pdf->GetPageWidth()-8)/2 + 4.25;
    $pdf->SetY($y);
    $pdf->SetX($x);
    $pdf->Cell(62.75,5,  utf8_decode(apres_virgule($db, $id_societe, $obj_bulletin->net_payer - $somme_avance, 2)), "TBR", "", "R",false);
  
      //Cout total
      $pdf->SetTextColor(0);
      $x = 4;
      $y += 6;
      $pdf->SetY($y);
      $pdf->SetX($x);
      $pdf->Cell(($pdf->GetPageWidth()-8)/2,5, utf8_decode(mb_strtoupper("Cout Total du Salaire")), "TB", "", "L",false);
  
      //Valeur:
      $x += ($pdf->GetPageWidth()-8)/2 + 0.25;
      $pdf->SetY($y);
      $pdf->SetX($x);
      $pdf->Cell(($pdf->GetPageWidth()-8)/2-0.5,5,  utf8_decode(apres_virgule($db, $id_societe, $obj_bulletin->net_payer + $retenu_its + $patro, 2)), "TB", "", "R",false);
  
  
      //Les informations de Banque
      $pdf->SetTextColor(0);
      $x = 6;
      $y += 8;
      $pdf->SetY($y);
      $pdf->SetX($x);
      $pdf->Cell(50,5, utf8_decode(mb_strtoupper("Mode de paiement : Par virement")),"", "", "L",false);
  
      $y += 6;
      $pdf->SetY($y);
      $pdf->SetX($x);
      $pdf->Cell(50,5, utf8_decode(mb_strtoupper("Type : ".$obj_bulletin->banque)),"", "", "L",false);
  
      $y += 6;
      $pdf->SetY($y);
      $pdf->SetX($x);
      $pdf->Cell(70,5, utf8_decode(mb_strtoupper("Numéro de compte : ".$obj_bulletin->compte)),"", "", "L",false);
  
  
      $pdf->SetFillColor(173, 206, 230);
      $pdf->SetTextColor(0);
      //Les zones de signature
      //employé
      $x = 25;
      $y = $pdf->GetPageHeight() - 30;
      $pdf->SetY($y);
  
      $pdf->SetX($x);
      $pdf->Cell(25,5, utf8_decode(mb_strtoupper("Employé")), "B", "", "C",true);
  
      //Employeur
      $x = $pdf->GetPageWidth() - 50;
      $y = $pdf->GetPageHeight() - 30;
      $pdf->SetY($y);
      $pdf->SetX($x);
      $pdf->Cell(25,5, utf8_decode(mb_strtoupper("Employeur")), "B", "", "C",true);
  }


            }
  $suivant ++;
          }
          $pdf->Output('de tous les salariés', $mode);
  }
}
}

//Entête de bulletin du modele avance

function pdf_pagehead_avance(&$pdf){

}

function pdf_ibspagefoot_avance(&$pdf, $marge_droite, $marge_basse)
{

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

  $db->close();