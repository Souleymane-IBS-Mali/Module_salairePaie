<?php


// Appel de la librairie FPDF
require '../../main.inc.php';

require("fpdf/fpdf.php");


// Connexion à la BDD (à personnaliser)
$id_societe = GETPOST('id_societe','int');
$annee_rechercher = GETPOST('annee','int');
$action = GETPOST("action", 'alpha');
$sql = "SELECT nom FROM ".MAIN_DB_PREFIX."societe Where rowid=".$id_societe;
	$result1 = $db->query($sql);
  $sc = $db->fetch_object($result1);

  global $sc, $annee_rechercher;
  
$id_societe = GETPOST('id_societe','int');

if($action == "tout_salarie"){

  // Création de la class PDF
  class PDF extends FPDF {
    
      // Header
      function Header() {
        // Logo : 8 >position à gauche du document (en mm), 2 >position en haut du document, 80 >largeur de l'image en mm). La hauteur est calculée automatiquement.
        //$this->Image('logo_agence.png',8,2);
        // Saut de ligne 20 mm
        //$this->Ln(20);
        if($this->PageNo() == 1){
          $this->SetTitle("Article29 ITS",true);
        }
          // Titre gras (B) police Helbetica de 11
          $this->SetFont('Helvetica','B',11);
          // fond de couleur gris (valeurs en RGB)
          $this->setFillColor(230,230,230);
          // position du coin supérieur gauche par rapport à la marge gauche (mm)
          
          // Texte : 60 >largeur ligne, 8 >hauteur ligne. Premier 0 >pas de bordure, 1 >retour à la ligneensuite, C >centrer texte, 1> couleur de fond ok
          $this->SetTextColor(3, 79, 132);
          $this->SetFont('Helvetica','B',11);
          global $sc, $annee_rechercher;
          $nom_soc = strtoupper($sc->nom);

          $y = 12;
          $this->SetY($y);
          $this->SetX(12);
          $this->Cell($this->GetPageWidth()-12,3,utf8_decode($nom_soc),0,0,'L',0);


          $this->SetX(105);
          $titre = "ETAT ANNUEL DES SALAIRES";
          $this->Cell(56,3,$titre,0,0,'C',0);

          $y += 7;
          $this->SetTextColor(0);
          $this->SetY($y);
          $this->SetX(110);
          $this->SetFont('Times','',9);
          $text = "/ INPS / ".$nom_soc;             
          $this->Cell(65,3,utf8_decode($text),0,0,'L',0);

          $this->SetX(175);
          $this->SetTextColor(3, 79, 132);
          $this->SetFont('Times','',11);
          $dis = "Distribué en :";
          $this->Cell(22,3,utf8_decode($dis),0,0,'L',0);

          $this->SetX(200);
          $this->SetTextColor(247, 103, 7);
          $this->SetFont('Times','B',10);
          $this->Cell(8,3,$annee_rechercher,0,0,'L',0);


          $this->SetX(210);
          $this->SetFont('Times','',11);
          $this->SetTextColor(0);
          $article = "Article 29 du Code Géneral des Impôts";
          $this->Cell(60,3,utf8_decode($article),0,0,'L',0);      
        // Saut de ligne 10 mm
      // $this->line(12,$this->GetY()+30,$this->GetPageWidth()-12,$this->GetY()+30);
        
      }

      
      // Footer
      function Footer() {

        // Positionnement à 1,5 cm du bas
        $this->SetY(-15);
        
        // Police Arial italique 8
        $this->SetFont('Helvetica','',9);
        // Numéro de page, centré (C)
        $this->SetX(12);
        $this->SetTextColor(3, 79, 132);
        $this->Cell(50,10,utf8_decode('Edité le : '.date('d/m/Y')),0,0,'L');

        $this->SetTextColor(0);
        $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'R');
        $this->SetX(12);
        $this->line(12,$this->GetY(),$this->GetPageWidth()-12,$this->GetY());

      }

          // On active la classe une fois pour toutes les pages suivantes
      // Format portrait (>P) ou paysage (>L), en mm (ou en points > pts), A4 (ou A5, etc.)
    }
      $pdf = new PDF('L','mm','A4');
      // Nouvelle page A4 (incluant ici logo, titre et pied de page)
      $pdf->AddPage();
      // Polices par défaut : Helvetica taille 9
      $pdf->SetFont('Helvetica','',9);
      // Couleur par défaut : noir
      $pdf->SetTextColor(0);
      // Compteur de pages {nb}
      $pdf->AliasNbPages();

    
      $pdf->SetLeftMargin(13);
      $pdf->SetRightMargin(15);
      $y = $pdf->GetY()+8;
      $pdf->SetY($y);
      
      $pdf->line(12,$pdf->GetY(),$pdf->GetPageWidth()-12,$pdf->GetY());
      $pdf->line(12,$y,12,$pdf->GetPageHeight()-15);
      $pdf->line($pdf->GetPageWidth()-12,$y,$pdf->GetPageWidth()-12,$pdf->GetPageHeight()-15);

      
  $y = $pdf->GetY();
    // $pdf->SetDrawColor(200, 200, 200);
    $pdf->setFillColor(230,230,230);
      $pdf->SetY($y);
      $pdf->SetX(13);
      //$pdf->Cell($pdf->GetPageWidth()-24,8, "",0,0,'','true');

      $pdf->SetFont('Helvetica','',8);
        $pdf->SetY($y);

        $pdf->Cell(18,9,utf8_decode("N° Mat."),0,'L');
        $pdf->SetFont('Helvetica','',9);

        $pdf->line(32, $y,32,$pdf->GetPageHeight()-15);

        $pdf->SetY($y);
        $pdf->SetX(33);
        $pdf->Cell(50,9,utf8_decode("Nom & Prenom"),0,'','C');
        $pdf->SetFont('Helvetica','',9);

        $pdf->line(84, $y,84,$y + 9);

        $pdf->SetX(85);
        $pdf->Cell(20,9,utf8_decode("Fonction"),0,'', 'R');

        $pdf->SetX(106);
        $pdf->MultiCell(18,4.5,utf8_decode("Nbre enf. au 31/12"),0,'R');
        $pdf->line(125, $y,125,$pdf->GetPageHeight()-15);

        $pdf->SetY($y);
        $pdf->SetX(126);
        $pdf->MultiCell(18,3,utf8_decode("Mt salaire et autres retrib.Brut"),0,'C');
        $pdf->line(144, $y,144,$pdf->GetPageHeight()-15);

        $pdf->SetY($y);
        $pdf->SetX(145);
        $pdf->Cell(15,9,utf8_decode("Retraite"),0,'', 'C');
        $pdf->line(160, $y,160,$pdf->GetPageHeight()-15);

        $pdf->SetY($y);
        $pdf->SetX(160);
        $pdf->SetFont('Helvetica','',7.5);
        $pdf->MultiCell(18,3,utf8_decode("Allocations & Indem. non Imposables"),0,'C');
        $pdf->line(178, $y,178,$pdf->GetPageHeight()-15);

        $pdf->SetY($y);
        $pdf->SetX(178);
        $pdf->MultiCell(19,3,utf8_decode("Montages réel   Avantages   En Nature"),0,'C');
        $pdf->line(197, $y,197,$pdf->GetPageHeight()-15);

        $pdf->SetFont('Helvetica','',9);
        $pdf->SetY($y);
        $pdf->SetX(197);
        $pdf->MultiCell(19,4,utf8_decode("Base Imposition"),0,'C');
        $pdf->line(216, $y,216,$pdf->GetPageHeight()-15);

        $pdf->SetY($y);
        $pdf->SetX(216);
        $pdf->MultiCell(19,4,utf8_decode("Impôt Retenu"),0,'C');
        $pdf->line(235, $y,235,$pdf->GetPageHeight()-15);

        $pdf->SetY($y);
        $pdf->SetX(235);
        $pdf->MultiCell(19,4,utf8_decode("Impôt Calculé"),0,'C');
        $pdf->line(254, $y,254,$pdf->GetPageHeight()-15);

        $pdf->SetY($y);
        $pdf->SetX(254);
        $pdf->MultiCell(19,4,utf8_decode("Solde Impôt"),0,'C');
        $pdf->line(273, $y,273,$pdf->GetPageHeight()-15);
        
        $pdf->SetY($y);
        $pdf->SetX(273);
        $pdf->MultiCell($pdf->GetPageWidth() - 13 - 273,4,utf8_decode("Nbre mois"),0,'C');


        $pdf->line(12,$y+9,$pdf->GetPageWidth()-12,$y+9);
            
        $y= $pdf->GetY() + 2;
        $pdf->SetY($y);

        $sql_reg = "SELECT DISTINCT fk_salarie FROM ".MAIN_DB_PREFIX."bulletin WHERE annee = ".$annee_rechercher;
        $sql_reg .= " ORDER BY nom, prenom";

        $res = $db->query($sql_reg);
      if($res) {
        $num_obj = $db->num_rows($res);
        if($num_obj == 0){
          $pdf->SetFont('Helvetica','B',18);
          $pdf->SetTextColor(150,0,0);
          $pdf->SetY($y+80);
          $pdf->SetX(95);
          $pdf->Cell(50,4,utf8_decode("Veuillez attendre Decembre ".$annee_rechercher),0,'C');
          $pdf->SetTextColor(0);
        }
          if($num_obj > 0){
            $somme_brut_annuel = 0;
            $somme_retraite = 0;
            $prime_indemnite_non_imposable = 0;
            $avantage_nature = 0;
            $somme_brut_imposable_annuel = 0;
            $somme_its_mois = 0;
            $its_annuel = 0;
            $difference = 0;
          while ($obj_fk_salarie = $db->fetch_object($res)) {
            $sal_its = "SELECT 
                reg.*, 
                bul.rowid, 
                bul.matricule, 
                bul.nom, 
                bul.prenom, 
                bul.situation_familiale, 
                bul.nombre_enfant, 
                bul.nombre_enfant_hand, 
                bul.fonction
            FROM ".MAIN_DB_PREFIX."bulletin_regularisation_its AS reg
            INNER JOIN ".MAIN_DB_PREFIX."bulletin AS bul
                ON bul.fk_salarie = reg.fk_salarie
            WHERE reg.fk_salarie = ".(int)$obj_fk_salarie->fk_salarie;


            $sal_res = $db->query($sal_its);
            if($sal_res)
              $article29_obj = $db->fetch_object($sal_res);
            if($article29_obj){
            if(($y + 24) >= $pdf->GetPageHeight()){
              $pdf->AddPage();
              // Polices par défaut : Helvetica taille 9
              $pdf->SetFont('Helvetica','',9);
              // Couleur par défaut : noir
              $pdf->SetTextColor(0);
              // Compteur de pages {nb}
              $pdf->AliasNbPages();

              $pdf->SetLeftMargin(13);
              $pdf->SetRightMargin(15);
              $y = $pdf->GetY()+8;
              $pdf->SetY($y);
              
              $pdf->line(12,$pdf->GetY(),$pdf->GetPageWidth()-12,$pdf->GetY());
              $pdf->line(12,$y,12,$pdf->GetPageHeight()-15);
              $pdf->line($pdf->GetPageWidth()-12,$y,$pdf->GetPageWidth()-12,$pdf->GetPageHeight()-15);

              
              $y = $pdf->GetY();
              // $pdf->SetDrawColor(200, 200, 200);
              $pdf->setFillColor(230,230,230);
              $pdf->SetY($y);
              $pdf->SetX(13);
              //$pdf->Cell($pdf->GetPageWidth()-24,8, "",0,0,'','true');

              $pdf->SetFont('Helvetica','',8);
                $pdf->SetY($y);

                $pdf->Cell(18,9,utf8_decode("N° Mat."),0,'L');
                $pdf->SetFont('Helvetica','',9);

                $pdf->line(32, $y,32,$pdf->GetPageHeight()-15);

                $pdf->SetY($y);
                $pdf->SetX(33);
                $pdf->Cell(50,9,utf8_decode("Nom & Prenom"),0,'','C');
                $pdf->SetFont('Helvetica','',9);

                $pdf->line(84, $y,84,$y + 9);

                $pdf->SetX(85);
                $pdf->Cell(20,9,utf8_decode("Fonction"),0,'', 'R');

                $pdf->SetX(106);
                $pdf->MultiCell(18,4.5,utf8_decode("Nbre enf. au 31/12"),0,'R');
                $pdf->line(125, $y,125,$pdf->GetPageHeight()-15);

                $pdf->SetY($y);
                $pdf->SetX(126);
                $pdf->MultiCell(18,3,utf8_decode("Mt salaire et autres retrib.Brut"),0,'C');
                $pdf->line(144, $y,144,$pdf->GetPageHeight()-15);

                $pdf->SetY($y);
                $pdf->SetX(145);
                $pdf->Cell(15,9,utf8_decode("Retraite"),0,'', 'C');
                $pdf->line(160, $y,160,$pdf->GetPageHeight()-15);

                $pdf->SetY($y);
                $pdf->SetX(160);
                $pdf->SetFont('Helvetica','',7.5);
                $pdf->MultiCell(18,3,utf8_decode("Allocations & Indem. non Imposables"),0,'C');
                $pdf->line(178, $y,178,$pdf->GetPageHeight()-15);

                $pdf->SetY($y);
                $pdf->SetX(178);
                $pdf->MultiCell(19,3,utf8_decode("Montages réel   Avantages   En Nature"),0,'C');
                $pdf->line(197, $y,197,$pdf->GetPageHeight()-15);

                $pdf->SetFont('Helvetica','',9);
                $pdf->SetY($y);
                $pdf->SetX(197);
                $pdf->MultiCell(19,4,utf8_decode("Base Imposition"),0,'C');
                $pdf->line(216, $y,216,$pdf->GetPageHeight()-15);

                $pdf->SetY($y);
                $pdf->SetX(216);
                $pdf->MultiCell(19,4,utf8_decode("Impôt Retenu"),0,'C');
                $pdf->line(235, $y,235,$pdf->GetPageHeight()-15);

                $pdf->SetY($y);
                $pdf->SetX(235);
                $pdf->MultiCell(19,4,utf8_decode("Impôt Calculé"),0,'C');
                $pdf->line(254, $y,254,$pdf->GetPageHeight()-15);

                $pdf->SetY($y);
                $pdf->SetX(254);
                $pdf->MultiCell(19,4,utf8_decode("Solde Impôt"),0,'C');
                $pdf->line(273, $y,273,$pdf->GetPageHeight()-15);
                
                $pdf->SetY($y);
                $pdf->SetX(273);
                $pdf->MultiCell($pdf->GetPageWidth() - 13 - 273,4,utf8_decode("Nbre mois"),0,'C');


                $pdf->line(12,$y+9,$pdf->GetPageWidth()-12,$y+9);
                    
                $y= $pdf->GetY() + 2;
                $pdf->SetY($y);

                
            }

            $pdf->SetFont('Times','',7.5);
            $pdf->SetY($y);
            $pdf->SetX(13);
            $pdf->Cell(18,4,utf8_decode($article29_obj->matricule),0,'L');

            
            if ($pdf->GetStringWidth($text) < 45) {
              $text = mb_strtoupper($article29_obj->nom.'   '.$article29_obj->prenom);
            }

          while ($pdf->GetStringWidth($text) > 52) {
              $text = substr($text, 0, -1);
            }

            $pdf->SetY($y);
            $pdf->SetX(32);
            $pdf->Cell(50,4,utf8_decode($text),0,'L');

            $pdf->SetFont('Times','',7.5);

            $text = mb_strtoupper($article29_obj->fonction);
            
            while ($pdf->GetStringWidth($text) > 28) {
              $text = substr($text, 0, -1);
            }

            $pdf->SetY($y);
            $pdf->SetX(85);
            $pdf->Cell(30,4,utf8_decode($text),0,'L');

            $pdf->SetFont('Times','',8);
            $st_m = "C";
            if($article29_obj->situation_familiale == "Marié")
              $st_m = "M";

            $pdf->SetY($y);
            $pdf->SetX(115);
            $pdf->Cell(8,4,utf8_decode(mb_strtoupper($st_m."  ".$article29_obj->nombre_enfant."/".$article29_obj->nombre_enfant_hand)),0,'L');

            $pdf->SetFont('Times','',7.5);
            $pdf->SetY($y);
            $pdf->SetX(126);
            $pdf->Cell(18,4,utf8_decode(number_format(round($article29_obj->somme_brut_annuel), 0, '', ' ')), 0, 0,'R');
            $somme_brut_annuel += round($article29_obj->somme_brut_annuel);

            $pdf->SetX(145);
            $pdf->Cell(15,4,utf8_decode(number_format(round($article29_obj->somme_retraite), 0, '', ' ')),0,'', 'R');
            $somme_retraite += round($article29_obj->somme_retraite);

            $pdf->SetX(160);
            $pdf->Cell(18,4,utf8_decode(number_format(round($article29_obj->prime_indemnite_non_imposable), 0, '', ' ')),0,'', 'R');
            $prime_indemnite_non_imposable += $article29_obj->prime_indemnite_non_imposable;

          $pdf->SetX(178);
            $pdf->Cell(19,4,utf8_decode(number_format(round($article29_obj->avantage_nature), 0, '', ' ')),0,'', 'R');
            $avantage_nature += $article29_obj->avantage_nature;


            $pdf->SetX(194);
            $pdf->Cell(22,4,utf8_decode(number_format(round($article29_obj->somme_brut_imposable_annuel), 0, '', ' ')), 0, 0,'R');
            $somme_brut_imposable_annuel += $article29_obj->somme_brut_imposable_annuel;

            $pdf->SetX(216);
            $pdf->Cell(19,4,utf8_decode(number_format(round($article29_obj->somme_its_mois), 0, '', ' ')), 0, 0,'R');
            $somme_its_mois += $article29_obj->somme_its_mois;

            $pdf->SetX(235);
            $pdf->Cell(19,4,utf8_decode(number_format(round($article29_obj->its_annuel), 0, '', ' ')), 0, 0,'R');
            $its_annuel += $article29_obj->somme_its_mois;

            $pdf->SetX(254);
            $pdf->Cell(19,4,utf8_decode(number_format(round($article29_obj->difference), 0, '', ' ')), 0, 0,'R');
            $difference += $article29_obj->difference;

            $pdf->SetX(273);
            if($article29_obj->nb_mois > 12)
              $pdf->Cell($pdf->GetPageWidth() - 13 - 273,4,12, 0, 0,'R');
            else $pdf->Cell($pdf->GetPageWidth() - 13 - 273,4,utf8_decode(number_format(round($article29_obj->nb_mois), 0, '', ' ')), 0, 0,'R');
            //$pdf->line(12,$y+4,$pdf->GetPageWidth()-12,$y+4);

            $y += 6;
          }
          }

          //Préparation pour affichage du total de l'année
          if(($y + 24) >= $pdf->GetPageHeight()){
              $pdf->AddPage();
              // Polices par défaut : Helvetica taille 9
              $pdf->SetFont('Helvetica','',9);
              // Couleur par défaut : noir
              $pdf->SetTextColor(0);
              // Compteur de pages {nb}
              $pdf->AliasNbPages();

              $pdf->SetLeftMargin(13);
              $pdf->SetRightMargin(15);
              $y = $pdf->GetY()+8;
              $pdf->SetY($y);
              
              $pdf->line(12,$pdf->GetY(),$pdf->GetPageWidth()-12,$pdf->GetY());
              $pdf->line(12,$y,12,$pdf->GetPageHeight()-15);
              $pdf->line($pdf->GetPageWidth()-12,$y,$pdf->GetPageWidth()-12,$pdf->GetPageHeight()-15);

              
              $y = $pdf->GetY();
              // $pdf->SetDrawColor(200, 200, 200);
              $pdf->setFillColor(230,230,230);
              $pdf->SetY($y);
              $pdf->SetX(13);
              //$pdf->Cell($pdf->GetPageWidth()-24,8, "",0,0,'','true');

              $pdf->SetFont('Helvetica','',8);
                $pdf->SetY($y);

                $pdf->Cell(18,9,utf8_decode("N° Mat."),0,'L');
                $pdf->SetFont('Helvetica','',9);

                $pdf->line(32, $y,32,$pdf->GetPageHeight()-15);

                $pdf->SetY($y);
                $pdf->SetX(33);
                $pdf->Cell(50,9,utf8_decode("Nom & Prenom"),0,'','C');
                $pdf->SetFont('Helvetica','',9);

                $pdf->line(84, $y,84,$y + 9);

                $pdf->SetX(85);
                $pdf->Cell(20,9,utf8_decode("Fonction"),0,'', 'R');

                $pdf->SetX(106);
                $pdf->MultiCell(18,4.5,utf8_decode("Nbre enf. au 31/12"),0,'R');
                $pdf->line(125, $y,125,$pdf->GetPageHeight()-15);

                $pdf->SetY($y);
                $pdf->SetX(126);
                $pdf->MultiCell(18,3,utf8_decode("Mt salaire et autres retrib.Brut"),0,'C');
                $pdf->line(144, $y,144,$pdf->GetPageHeight()-15);

                $pdf->SetY($y);
                $pdf->SetX(145);
                $pdf->Cell(15,9,utf8_decode("Retraite"),0,'', 'C');
                $pdf->line(160, $y,160,$pdf->GetPageHeight()-15);

                $pdf->SetY($y);
                $pdf->SetX(160);
                $pdf->SetFont('Helvetica','',7.5);
                $pdf->MultiCell(18,3,utf8_decode("Allocations & Indem. non Imposables"),0,'C');
                $pdf->line(178, $y,178,$pdf->GetPageHeight()-15);

                $pdf->SetY($y);
                $pdf->SetX(178);
                $pdf->MultiCell(19,3,utf8_decode("Montages réel   Avantages   En Nature"),0,'C');
                $pdf->line(197, $y,197,$pdf->GetPageHeight()-15);

                $pdf->SetFont('Helvetica','',9);
                $pdf->SetY($y);
                $pdf->SetX(197);
                $pdf->MultiCell(19,4,utf8_decode("Base Imposition"),0,'C');
                $pdf->line(216, $y,216,$pdf->GetPageHeight()-15);

                $pdf->SetY($y);
                $pdf->SetX(216);
                $pdf->MultiCell(19,4,utf8_decode("Impôt Retenu"),0,'C');
                $pdf->line(235, $y,235,$pdf->GetPageHeight()-15);

                $pdf->SetY($y);
                $pdf->SetX(235);
                $pdf->MultiCell(19,4,utf8_decode("Impôt Calculé"),0,'C');
                $pdf->line(254, $y,254,$pdf->GetPageHeight()-15);

                $pdf->SetY($y);
                $pdf->SetX(254);
                $pdf->MultiCell(19,4,utf8_decode("Solde Impôt"),0,'C');
                $pdf->line(273, $y,273,$pdf->GetPageHeight()-15);
                
                $pdf->SetY($y);
                $pdf->SetX(273);
                $pdf->MultiCell($pdf->GetPageWidth() - 13 - 273,4,utf8_decode("Nbre mois"),0,'C');


                $pdf->line(12,$y+9,$pdf->GetPageWidth()-12,$y+9);
                    
                $y= $pdf->GetY() + 2;
                $pdf->SetY($y);
            }
            //on affiche le total
            //-------------------------------------------------------------------------------------------
            $pdf->SetFont('Times','B',7.5);
            $pdf->SetY($y);
            $pdf->SetX(12);
            $pdf->setFillColor(216, 222, 233);
            $pdf->Cell(113,6,utf8_decode(mb_strtoupper("Total général")),1, 0, 'C', true);
            

            $pdf->SetFont('Times','',7.5);
            $pdf->SetY($y);
            $pdf->SetX(125.25);
            $pdf->Cell(18.5,6,utf8_decode(number_format(round($somme_brut_annuel), 0, '', ' ')),'TB', 0,'R', true);

            $pdf->SetX(144.25);
            $pdf->Cell(15.5,6,utf8_decode(number_format(round($somme_retraite), 0, '', ' ')),'TB',0, 'R', true);

            $pdf->SetX(160.25);
            $pdf->Cell(17.5,6,utf8_decode(number_format(round($prime_indemnite_non_imposable), 0, '', ' ')),'TB',0, 'R', true);

          $pdf->SetX(178.25);
            $pdf->Cell(18.5,6,utf8_decode(number_format(round($avantage_nature), 0, '', ' ')),'TB',0, 'R', true);


            $pdf->SetX(197.25);
            $pdf->Cell(18.5,6,utf8_decode(number_format(round($somme_brut_imposable_annuel), 0, '', ' ')),'TB', 0,'R', true);

            $pdf->SetX(216.25);
            $pdf->Cell(18.5,6,utf8_decode(number_format(round($somme_its_mois), 0, '', ' ')),'TB', 0,'R', true);

            $pdf->SetX(235.25);
            $pdf->Cell(18.5,6,utf8_decode(number_format(round($its_annuel), 0, '', ' ')),'TB', 0,'R', true);

            $pdf->SetX(254.25);
            $pdf->Cell(19,6,utf8_decode(number_format(round($difference), 0, '', ' ')),'TB', 0,'R', true);
            
            $pdf->SetX(273);
            $pdf->Cell($pdf->GetPageWidth() - 12 - 273,6,'', 1, 0,'R', true);

            //------------------------------------------------------------------------------------------------------------------
        }else{
          $pdf->SetFont('Helvetica','B',18);
          $pdf->SetTextColor(150,0,0);
          $pdf->SetY($y+80);
          $pdf->SetX(95);
          $pdf->Cell(50,4,utf8_decode("Veuillez attendre Decembre ".$annee_rechercher),0,'C');
          $pdf->SetTextColor(0);
        }


      }else{
          $pdf->SetFont('Helvetica','B',18);
          $pdf->SetTextColor(150,0,0);
          $pdf->SetY($y+80);
          $pdf->SetX(95);
          $pdf->Cell(50,4,utf8_decode("Veuillez attendre Decembre ".$annee_rechercher),0,'C');
          $pdf->SetTextColor(0);

      }
     
}else{

  // Création de la class PDF
  class PDF extends FPDF {
    
      // Header
      function Header() {
        // Logo : 8 >position à gauche du document (en mm), 2 >position en haut du document, 80 >largeur de l'image en mm). La hauteur est calculée automatiquement.
        //$this->Image('logo_agence.png',8,2);
        // Saut de ligne 20 mm
        //$this->Ln(20);
        if($this->PageNo() == 1){
          $this->SetTitle("Article29 ITS",true);
        }
          // Titre gras (B) police Helbetica de 11
          $this->SetFont('Helvetica','B',11);
          // fond de couleur gris (valeurs en RGB)
          $this->setFillColor(230,230,230);
          // position du coin supérieur gauche par rapport à la marge gauche (mm)
          
          // Texte : 60 >largeur ligne, 8 >hauteur ligne. Premier 0 >pas de bordure, 1 >retour à la ligneensuite, C >centrer texte, 1> couleur de fond ok
          $this->SetTextColor(3, 79, 132);
          $this->SetFont('Helvetica','B',11);
          global $sc, $annee_rechercher;
          $nom_soc = strtoupper($sc->nom);

          $y = 12;
          $this->SetY($y);
          $this->SetX(12);
          $this->Cell($this->GetPageWidth()-12,3,utf8_decode($nom_soc),0,0,'L',0);


          $this->SetX(105);
          $titre = "ETAT ANNUEL DES SALAIRES";
          $this->Cell(56,3,$titre,0,0,'C',0);

          $y += 7;
          $this->SetTextColor(0);
          $this->SetY($y);
          $this->SetX(110);
          $this->SetFont('Times','',9);
          $text = "/ INPS / ".$nom_soc;             
          $this->Cell(65,3,utf8_decode($text),0,0,'L',0);

          $this->SetX(175);
          $this->SetTextColor(3, 79, 132);
          $this->SetFont('Times','',11);
          $dis = "Distribué en :";
          $this->Cell(22,3,utf8_decode($dis),0,0,'L',0);

          $this->SetX(200);
          $this->SetTextColor(247, 103, 7);
          $this->SetFont('Times','B',10);
          $this->Cell(8,3,$annee_rechercher,0,0,'L',0);


          $this->SetX(210);
          $this->SetFont('Times','',11);
          $this->SetTextColor(0);
          $article = "Article 29 du Code Géneral des Impôts";
          $this->Cell(60,3,utf8_decode($article),0,0,'L',0);      
        // Saut de ligne 10 mm
      // $this->line(12,$this->GetY()+30,$this->GetPageWidth()-12,$this->GetY()+30);
        
      }

      
      // Footer
      function Footer() {

        // Positionnement à 1,5 cm du bas
        $this->SetY(-15);
        
        // Police Arial italique 8
        $this->SetFont('Helvetica','',9);
        // Numéro de page, centré (C)
        $this->SetX(12);
        $this->SetTextColor(3, 79, 132);
        $this->Cell(50,10,utf8_decode('Edité le : '.date('d/m/Y')),0,0,'L');

        $this->SetTextColor(0);
        $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'R');
        $this->SetX(12);
        $this->line(12,$this->GetY(),$this->GetPageWidth()-12,$this->GetY());

      }

          // On active la classe une fois pour toutes les pages suivantes
      // Format portrait (>P) ou paysage (>L), en mm (ou en points > pts), A4 (ou A5, etc.)
    }
      $pdf = new PDF('L','mm','A4');
      // Nouvelle page A4 (incluant ici logo, titre et pied de page)
      $pdf->AddPage();
      // Polices par défaut : Helvetica taille 9
      $pdf->SetFont('Helvetica','',9);
      // Couleur par défaut : noir
      $pdf->SetTextColor(0);
      // Compteur de pages {nb}
      $pdf->AliasNbPages();

    
      $pdf->SetLeftMargin(13);
      $pdf->SetRightMargin(15);
      $y = $pdf->GetY()+8;
      $pdf->SetY($y);
      
      $pdf->line(12,$pdf->GetY(),$pdf->GetPageWidth()-12,$pdf->GetY());
      $pdf->line(12,$y,12,$pdf->GetPageHeight()-15);
      $pdf->line($pdf->GetPageWidth()-12,$y,$pdf->GetPageWidth()-12,$pdf->GetPageHeight()-15);

      
  $y = $pdf->GetY();
    // $pdf->SetDrawColor(200, 200, 200);
    $pdf->setFillColor(230,230,230);
      $pdf->SetY($y);
      $pdf->SetX(13);
      //$pdf->Cell($pdf->GetPageWidth()-24,8, "",0,0,'','true');

      $pdf->SetFont('Helvetica','',8);
        $pdf->SetY($y);

        $pdf->Cell(18,9,utf8_decode("N° Mat."),0,'L');
        $pdf->SetFont('Helvetica','',9);

        $pdf->line(32, $y,32,$pdf->GetPageHeight()-15);

        $pdf->SetY($y);
        $pdf->SetX(33);
        $pdf->Cell(50,9,utf8_decode("Nom & Prenom"),0,'','C');
        $pdf->SetFont('Helvetica','',9);

        $pdf->line(84, $y,84,$y + 9);

        $pdf->SetX(85);
        $pdf->Cell(20,9,utf8_decode("Fonction"),0,'', 'R');

        $pdf->SetX(106);
        $pdf->MultiCell(18,4.5,utf8_decode("Nbre enf. au 31/12"),0,'R');
        $pdf->line(125, $y,125,$pdf->GetPageHeight()-15);

        $pdf->SetY($y);
        $pdf->SetX(126);
        $pdf->MultiCell(18,3,utf8_decode("Mt salaire et autres retrib.Brut"),0,'C');
        $pdf->line(144, $y,144,$pdf->GetPageHeight()-15);

        $pdf->SetY($y);
        $pdf->SetX(145);
        $pdf->Cell(15,9,utf8_decode("Retraite"),0,'', 'C');
        $pdf->line(160, $y,160,$pdf->GetPageHeight()-15);

        $pdf->SetY($y);
        $pdf->SetX(160);
        $pdf->SetFont('Helvetica','',7.5);
        $pdf->MultiCell(18,3,utf8_decode("Allocations & Indem. non Imposables"),0,'C');
        $pdf->line(178, $y,178,$pdf->GetPageHeight()-15);

        $pdf->SetY($y);
        $pdf->SetX(178);
        $pdf->MultiCell(19,3,utf8_decode("Montages réel   Avantages   En Nature"),0,'C');
        $pdf->line(197, $y,197,$pdf->GetPageHeight()-15);

        $pdf->SetFont('Helvetica','',9);
        $pdf->SetY($y);
        $pdf->SetX(197);
        $pdf->MultiCell(19,4,utf8_decode("Base Imposition"),0,'C');
        $pdf->line(216, $y,216,$pdf->GetPageHeight()-15);

        $pdf->SetY($y);
        $pdf->SetX(216);
        $pdf->MultiCell(19,4,utf8_decode("Impôt Retenu"),0,'C');
        $pdf->line(235, $y,235,$pdf->GetPageHeight()-15);

        $pdf->SetY($y);
        $pdf->SetX(235);
        $pdf->MultiCell(19,4,utf8_decode("Impôt Calculé"),0,'C');
        $pdf->line(254, $y,254,$pdf->GetPageHeight()-15);

        $pdf->SetY($y);
        $pdf->SetX(254);
        $pdf->MultiCell(19,4,utf8_decode("Solde Impôt"),0,'C');
        $pdf->line(273, $y,273,$pdf->GetPageHeight()-15);
        
        $pdf->SetY($y);
        $pdf->SetX(273);
        $pdf->MultiCell($pdf->GetPageWidth() - 13 - 273,4,utf8_decode("Nbre mois"),0,'C');


        $pdf->line(12,$y+9,$pdf->GetPageWidth()-12,$y+9);
            
        $y= $pdf->GetY() + 2;
        $pdf->SetY($y);

        $sql_reg = "SELECT DISTINCT fk_salarie FROM ".MAIN_DB_PREFIX."bulletin WHERE annee = ".$annee_rechercher." AND fk_societe = ".$id_societe;
        $sql_reg .= " ORDER BY nom";

        $res = $db->query($sql_reg);
      if($res) {
        $num_obj = $db->num_rows($res);
        if($num_obj == 0){
          $pdf->SetFont('Helvetica','B',18);
          $pdf->SetTextColor(150,0,0);
          $pdf->SetY($y+80);
          $pdf->SetX(95);
          $pdf->Cell(50,4,utf8_decode("Veuillez attendre Decembre ".$annee_rechercher),0,'C');
          $pdf->SetTextColor(0);
        }
          if($num_obj > 0){
            $somme_brut_annuel = 0;
            $somme_retraite = 0;
            $prime_indemnite_non_imposable = 0;
            $avantage_nature = 0;
            $somme_brut_imposable_annuel = 0;
            $somme_its_mois = 0;
            $its_annuel = 0;
            $difference = 0;
          while ($obj_fk_salarie = $db->fetch_object($res)) {
            $sal_its = "SELECT reg.*, bul.rowid, bul.matricule, bul.nom, bul.prenom, bul.situation_familiale, bul.nombre_enfant, bul.nombre_enfant_hand, bul.fonction";
            $sal_its .= " FROM ".MAIN_DB_PREFIX."bulletin_regularisation_its AS reg";
            $sal_its .= " INNER JOIN ".MAIN_DB_PREFIX."bulletin AS bul";
            $sal_its .= " ON bul.fk_salarie = reg.fk_salarie";
            $sal_its .= " WHERE reg.fk_salarie = ".$obj_fk_salarie->fk_salarie;

            $sal_res = $db->query($sal_its);
            if($sal_res)
              $article29_obj = $db->fetch_object($sal_res);

            if(($y + 24) >= $pdf->GetPageHeight()){
              $pdf->AddPage();
              // Polices par défaut : Helvetica taille 9
              $pdf->SetFont('Helvetica','',9);
              // Couleur par défaut : noir
              $pdf->SetTextColor(0);
              // Compteur de pages {nb}
              $pdf->AliasNbPages();

              $pdf->SetLeftMargin(13);
              $pdf->SetRightMargin(15);
              $y = $pdf->GetY()+8;
              $pdf->SetY($y);
              
              $pdf->line(12,$pdf->GetY(),$pdf->GetPageWidth()-12,$pdf->GetY());
              $pdf->line(12,$y,12,$pdf->GetPageHeight()-15);
              $pdf->line($pdf->GetPageWidth()-12,$y,$pdf->GetPageWidth()-12,$pdf->GetPageHeight()-15);

              
              $y = $pdf->GetY();
              // $pdf->SetDrawColor(200, 200, 200);
              $pdf->setFillColor(230,230,230);
              $pdf->SetY($y);
              $pdf->SetX(13);
              //$pdf->Cell($pdf->GetPageWidth()-24,8, "",0,0,'','true');

              $pdf->SetFont('Helvetica','',8);
                $pdf->SetY($y);

                $pdf->Cell(18,9,utf8_decode("N° Mat."),0,'L');
                $pdf->SetFont('Helvetica','',9);

                $pdf->line(32, $y,32,$pdf->GetPageHeight()-15);

                $pdf->SetY($y);
                $pdf->SetX(33);
                $pdf->Cell(50,9,utf8_decode("Nom & Prenom"),0,'','C');
                $pdf->SetFont('Helvetica','',9);

                $pdf->line(84, $y,84,$y + 9);

                $pdf->SetX(85);
                $pdf->Cell(20,9,utf8_decode("Fonction"),0,'', 'R');

                $pdf->SetX(106);
                $pdf->MultiCell(18,4.5,utf8_decode("Nbre enf. au 31/12"),0,'R');
                $pdf->line(125, $y,125,$pdf->GetPageHeight()-15);

                $pdf->SetY($y);
                $pdf->SetX(126);
                $pdf->MultiCell(18,3,utf8_decode("Mt salaire et autres retrib.Brut"),0,'C');
                $pdf->line(144, $y,144,$pdf->GetPageHeight()-15);

                $pdf->SetY($y);
                $pdf->SetX(145);
                $pdf->Cell(15,9,utf8_decode("Retraite"),0,'', 'C');
                $pdf->line(160, $y,160,$pdf->GetPageHeight()-15);

                $pdf->SetY($y);
                $pdf->SetX(160);
                $pdf->SetFont('Helvetica','',7.5);
                $pdf->MultiCell(18,3,utf8_decode("Allocations & Indem. non Imposables"),0,'C');
                $pdf->line(178, $y,178,$pdf->GetPageHeight()-15);

                $pdf->SetY($y);
                $pdf->SetX(178);
                $pdf->MultiCell(19,3,utf8_decode("Montages réel   Avantages   En Nature"),0,'C');
                $pdf->line(197, $y,197,$pdf->GetPageHeight()-15);

                $pdf->SetFont('Helvetica','',9);
                $pdf->SetY($y);
                $pdf->SetX(197);
                $pdf->MultiCell(19,4,utf8_decode("Base Imposition"),0,'C');
                $pdf->line(216, $y,216,$pdf->GetPageHeight()-15);

                $pdf->SetY($y);
                $pdf->SetX(216);
                $pdf->MultiCell(19,4,utf8_decode("Impôt Retenu"),0,'C');
                $pdf->line(235, $y,235,$pdf->GetPageHeight()-15);

                $pdf->SetY($y);
                $pdf->SetX(235);
                $pdf->MultiCell(19,4,utf8_decode("Impôt Calculé"),0,'C');
                $pdf->line(254, $y,254,$pdf->GetPageHeight()-15);

                $pdf->SetY($y);
                $pdf->SetX(254);
                $pdf->MultiCell(19,4,utf8_decode("Solde Impôt"),0,'C');
                $pdf->line(273, $y,273,$pdf->GetPageHeight()-15);
                
                $pdf->SetY($y);
                $pdf->SetX(273);
                $pdf->MultiCell($pdf->GetPageWidth() - 13 - 273,4,utf8_decode("Nbre mois"),0,'C');


                $pdf->line(12,$y+9,$pdf->GetPageWidth()-12,$y+9);
                    
                $y= $pdf->GetY() + 2;
                $pdf->SetY($y);

                
            }

            $pdf->SetFont('Times','',7.5);
            $pdf->SetY($y);
            $pdf->SetX(13);
            $pdf->Cell(18,4,utf8_decode($article29_obj->matricule),0,'L');

            
            if ($pdf->GetStringWidth($text) < 45) {
              $text = mb_strtoupper($article29_obj->nom.'   '.$article29_obj->prenom);
            }

          while ($pdf->GetStringWidth($text) > 52) {
              $text = substr($text, 0, -1);
            }

            $pdf->SetY($y);
            $pdf->SetX(32);
            $pdf->Cell(50,4,utf8_decode($text),0,'L');

            $pdf->SetFont('Times','',7.5);

            $text = mb_strtoupper($article29_obj->fonction);
            
            while ($pdf->GetStringWidth($text) > 28) {
              $text = substr($text, 0, -1);
            }

            $pdf->SetY($y);
            $pdf->SetX(85);
            $pdf->Cell(30,4,utf8_decode($text),0,'L');

            $pdf->SetFont('Times','',8);
            $st_m = "C";
            if($article29_obj->situation_familiale == "Marié")
              $st_m = "M";

            $pdf->SetY($y);
            $pdf->SetX(115);
            $pdf->Cell(8,4,utf8_decode(mb_strtoupper($st_m."  ".$article29_obj->nombre_enfant."/".$article29_obj->nombre_enfant_hand)),0,'L');

            $pdf->SetFont('Times','',7.5);
            $pdf->SetY($y);
            $pdf->SetX(126);
            $pdf->Cell(18,4,utf8_decode(number_format(round($article29_obj->somme_brut_annuel), 0, '', ' ')), 0, 0,'R');
            $somme_brut_annuel += round($article29_obj->somme_brut_annuel);

            $pdf->SetX(145);
            $pdf->Cell(15,4,utf8_decode(number_format(round($article29_obj->somme_retraite), 0, '', ' ')),0,'', 'R');
            $somme_retraite += round($article29_obj->somme_retraite);

            $pdf->SetX(160);
            $pdf->Cell(18,4,utf8_decode(number_format(round($article29_obj->prime_indemnite_non_imposable), 0, '', ' ')),0,'', 'R');
            $prime_indemnite_non_imposable += $article29_obj->prime_indemnite_non_imposable;

          $pdf->SetX(178);
            $pdf->Cell(19,4,utf8_decode(number_format(round($article29_obj->avantage_nature), 0, '', ' ')),0,'', 'R');
            $avantage_nature += $article29_obj->avantage_nature;


            $pdf->SetX(194);
            $pdf->Cell(22,4,utf8_decode(number_format(round($article29_obj->somme_brut_imposable_annuel), 0, '', ' ')), 0, 0,'R');
            $somme_brut_imposable_annuel += $article29_obj->somme_brut_imposable_annuel;

            $pdf->SetX(216);
            $pdf->Cell(19,4,utf8_decode(number_format(round($article29_obj->somme_its_mois), 0, '', ' ')), 0, 0,'R');
            $somme_its_mois += $article29_obj->somme_its_mois;

            $pdf->SetX(235);
            $pdf->Cell(19,4,utf8_decode(number_format(round($article29_obj->its_annuel), 0, '', ' ')), 0, 0,'R');
            $its_annuel += $article29_obj->somme_its_mois;

            $pdf->SetX(254);
            $pdf->Cell(19,4,utf8_decode(number_format(round($article29_obj->difference), 0, '', ' ')), 0, 0,'R');
            $difference += $article29_obj->difference;

            $pdf->SetX(273);
            if($article29_obj->nb_mois > 12)
              $pdf->Cell($pdf->GetPageWidth() - 13 - 273,4,12, 0, 0,'R');
            else $pdf->Cell($pdf->GetPageWidth() - 13 - 273,4,utf8_decode(number_format(round($article29_obj->nb_mois), 0, '', ' ')), 0, 0,'R');
            //$pdf->line(12,$y+4,$pdf->GetPageWidth()-12,$y+4);

            $y += 6;
          }

          //Préparation pour affichage du total de l'année
          if(($y + 24) >= $pdf->GetPageHeight()){
              $pdf->AddPage();
              // Polices par défaut : Helvetica taille 9
              $pdf->SetFont('Helvetica','',9);
              // Couleur par défaut : noir
              $pdf->SetTextColor(0);
              // Compteur de pages {nb}
              $pdf->AliasNbPages();

              $pdf->SetLeftMargin(13);
              $pdf->SetRightMargin(15);
              $y = $pdf->GetY()+8;
              $pdf->SetY($y);
              
              $pdf->line(12,$pdf->GetY(),$pdf->GetPageWidth()-12,$pdf->GetY());
              $pdf->line(12,$y,12,$pdf->GetPageHeight()-15);
              $pdf->line($pdf->GetPageWidth()-12,$y,$pdf->GetPageWidth()-12,$pdf->GetPageHeight()-15);

              
              $y = $pdf->GetY();
              // $pdf->SetDrawColor(200, 200, 200);
              $pdf->setFillColor(230,230,230);
              $pdf->SetY($y);
              $pdf->SetX(13);
              //$pdf->Cell($pdf->GetPageWidth()-24,8, "",0,0,'','true');

              $pdf->SetFont('Helvetica','',8);
                $pdf->SetY($y);

                $pdf->Cell(18,9,utf8_decode("N° Mat."),0,'L');
                $pdf->SetFont('Helvetica','',9);

                $pdf->line(32, $y,32,$pdf->GetPageHeight()-15);

                $pdf->SetY($y);
                $pdf->SetX(33);
                $pdf->Cell(50,9,utf8_decode("Nom & Prenom"),0,'','C');
                $pdf->SetFont('Helvetica','',9);

                $pdf->line(84, $y,84,$y + 9);

                $pdf->SetX(85);
                $pdf->Cell(20,9,utf8_decode("Fonction"),0,'', 'R');

                $pdf->SetX(106);
                $pdf->MultiCell(18,4.5,utf8_decode("Nbre enf. au 31/12"),0,'R');
                $pdf->line(125, $y,125,$pdf->GetPageHeight()-15);

                $pdf->SetY($y);
                $pdf->SetX(126);
                $pdf->MultiCell(18,3,utf8_decode("Mt salaire et autres retrib.Brut"),0,'C');
                $pdf->line(144, $y,144,$pdf->GetPageHeight()-15);

                $pdf->SetY($y);
                $pdf->SetX(145);
                $pdf->Cell(15,9,utf8_decode("Retraite"),0,'', 'C');
                $pdf->line(160, $y,160,$pdf->GetPageHeight()-15);

                $pdf->SetY($y);
                $pdf->SetX(160);
                $pdf->SetFont('Helvetica','',7.5);
                $pdf->MultiCell(18,3,utf8_decode("Allocations & Indem. non Imposables"),0,'C');
                $pdf->line(178, $y,178,$pdf->GetPageHeight()-15);

                $pdf->SetY($y);
                $pdf->SetX(178);
                $pdf->MultiCell(19,3,utf8_decode("Montages réel   Avantages   En Nature"),0,'C');
                $pdf->line(197, $y,197,$pdf->GetPageHeight()-15);

                $pdf->SetFont('Helvetica','',9);
                $pdf->SetY($y);
                $pdf->SetX(197);
                $pdf->MultiCell(19,4,utf8_decode("Base Imposition"),0,'C');
                $pdf->line(216, $y,216,$pdf->GetPageHeight()-15);

                $pdf->SetY($y);
                $pdf->SetX(216);
                $pdf->MultiCell(19,4,utf8_decode("Impôt Retenu"),0,'C');
                $pdf->line(235, $y,235,$pdf->GetPageHeight()-15);

                $pdf->SetY($y);
                $pdf->SetX(235);
                $pdf->MultiCell(19,4,utf8_decode("Impôt Calculé"),0,'C');
                $pdf->line(254, $y,254,$pdf->GetPageHeight()-15);

                $pdf->SetY($y);
                $pdf->SetX(254);
                $pdf->MultiCell(19,4,utf8_decode("Solde Impôt"),0,'C');
                $pdf->line(273, $y,273,$pdf->GetPageHeight()-15);
                
                $pdf->SetY($y);
                $pdf->SetX(273);
                $pdf->MultiCell($pdf->GetPageWidth() - 13 - 273,4,utf8_decode("Nbre mois"),0,'C');


                $pdf->line(12,$y+9,$pdf->GetPageWidth()-12,$y+9);
                    
                $y= $pdf->GetY() + 2;
                $pdf->SetY($y);
            }
            //on affiche le total
            //-------------------------------------------------------------------------------------------
            $pdf->SetFont('Times','B',7.5);
            $pdf->SetY($y);
            $pdf->SetX(12);
            $pdf->setFillColor(216, 222, 233);
            $pdf->Cell(113,6,utf8_decode(mb_strtoupper("Total général")),1, 0, 'C', true);
            

            $pdf->SetFont('Times','',7.5);
            $pdf->SetY($y);
            $pdf->SetX(125.25);
            $pdf->Cell(18.5,6,utf8_decode(number_format(round($somme_brut_annuel), 0, '', ' ')),'TB', 0,'R', true);

            $pdf->SetX(144.25);
            $pdf->Cell(15.5,6,utf8_decode(number_format(round($somme_retraite), 0, '', ' ')),'TB',0, 'R', true);

            $pdf->SetX(160.25);
            $pdf->Cell(17.5,6,utf8_decode(number_format(round($prime_indemnite_non_imposable), 0, '', ' ')),'TB',0, 'R', true);

          $pdf->SetX(178.25);
            $pdf->Cell(18.5,6,utf8_decode(number_format(round($avantage_nature), 0, '', ' ')),'TB',0, 'R', true);


            $pdf->SetX(197.25);
            $pdf->Cell(18.5,6,utf8_decode(number_format(round($somme_brut_imposable_annuel), 0, '', ' ')),'TB', 0,'R', true);

            $pdf->SetX(216.25);
            $pdf->Cell(18.5,6,utf8_decode(number_format(round($somme_its_mois), 0, '', ' ')),'TB', 0,'R', true);

            $pdf->SetX(235.25);
            $pdf->Cell(18.5,6,utf8_decode(number_format(round($its_annuel), 0, '', ' ')),'TB', 0,'R', true);

            $pdf->SetX(254.25);
            $pdf->Cell(19,6,utf8_decode(number_format(round($difference), 0, '', ' ')),'TB', 0,'R', true);
            
            $pdf->SetX(273);
            $pdf->Cell($pdf->GetPageWidth() - 12 - 273,6,'', 1, 0,'R', true);

            //------------------------------------------------------------------------------------------------------------------
        }else{
          $pdf->SetFont('Helvetica','B',18);
          $pdf->SetTextColor(150,0,0);
          $pdf->SetY($y+80);
          $pdf->SetX(95);
          $pdf->Cell(50,4,utf8_decode("Veuillez attendre Decembre ".$annee_rechercher),0,'C');
          $pdf->SetTextColor(0);
        }


      }else{
          $pdf->SetFont('Helvetica','B',18);
          $pdf->SetTextColor(150,0,0);
          $pdf->SetY($y+80);
          $pdf->SetX(95);
          $pdf->Cell(50,4,utf8_decode("Veuillez attendre Decembre ".$annee_rechercher),0,'C');
          $pdf->SetTextColor(0);

      }
     
}
     
    // affichage à l'écran...
    $pdf->Output('Regularisation ITS '.$annee_rechercher.' de '.$sc->nom.'.pdf','I');

    $db->close();

    /*Verification de l'artile 24 de l'its */
