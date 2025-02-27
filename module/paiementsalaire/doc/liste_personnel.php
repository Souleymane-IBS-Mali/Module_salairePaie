<?php


// Appel de la librairie FPDF
require '../../main.inc.php';

require("fpdf/fpdf.php");


// Connexion à la BDD (à personnaliser)
$id_societe = GETPOST('id_societe','int');
$sql = "SELECT nom FROM ".MAIN_DB_PREFIX."societe Where rowid=".$id_societe;
	$result1 = $db->query($sql);
  $sc = $db->fetch_object($result1);

  
$id_societe = GETPOST('id_societe','int');
      $sql = "SELECT u.rowid, u.lastname, u.firstname, u.dateemployment, u.office_phone, u.email, u.office_fax, ue.fk_object, ue.egp, sal.fk_user, sal.matricule FROM ".MAIN_DB_PREFIX."user as u";
      $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."user_extrafields as ue ON u.rowid=ue.fk_object";
      $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."salarie as sal ON sal.fk_user=u.rowid WHERE ue.egp=".$id_societe;
      $sql .= " ORDER BY u.lastname";
      $result = $db->query($sql);
      $num = $db->num_rows($result);


// Création de la class PDF
class PDF extends FPDF {
  
    // Header
    function Header() {
      // Logo : 8 >position à gauche du document (en mm), 2 >position en haut du document, 80 >largeur de l'image en mm). La hauteur est calculée automatiquement.
      //$this->Image('logo_agence.png',8,2);
      // Saut de ligne 20 mm
      //$this->Ln(20);
      
      $this->SetTitle("Personnels",true);
      // Titre gras (B) police Helbetica de 11
      $this->SetFont('Helvetica','B',11);
      // fond de couleur gris (valeurs en RGB)
      $this->setFillColor(230,230,230);
       // position du coin supérieur gauche par rapport à la marge gauche (mm)
       $this->SetX(70);
       //$this->SetY(20);
      // Texte : 60 >largeur ligne, 8 >hauteur ligne. Premier 0 >pas de bordure, 1 >retour à la ligneensuite, C >centrer texte, 1> couleur de fond ok  
    
      $titre = "Liste des Personnels".$sc->nom;
      $this->Cell(60,3,$titre,0,0,'R',0);
      $this->line(12,$this->GetY()+5,$this->GetPageWidth()-12,$this->GetY()+5);
      // Saut de ligne 10 mm
     // $this->line(12,$this->GetY()+30,$this->GetPageWidth()-12,$this->GetY()+30);
      
    }

    
    // Footer
    function Footer() {

      // Positionnement à 1,5 cm du bas
      $this->SetY(-15);
      
      // Police Arial italique 8
      $this->SetFont('Helvetica','I',9);
      // Numéro de page, centré (C)
      $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
      $this->line(12,$this->GetY(),$this->GetPageWidth()-12,$this->GetY());

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

    $pdf->SetY(41);
    $pdf->SetLeftMargin(13);
    $pdf->SetRightMargin(15);
    $pdf->line(12,15,12,$pdf->GetPageHeight()-15);
    $pdf->line($pdf->GetPageWidth()-12,15,$pdf->GetPageWidth()-12,$pdf->GetPageHeight()-15);

    $y = 15;

   // $pdf->SetDrawColor(200, 200, 200);
   $pdf->setFillColor(230,230,230);
    $pdf->SetY($y);
    $pdf->SetX(12);
    $pdf->Cell($pdf->GetPageWidth()-24,4, "",0,0,'','true');

     $pdf->SetFont('Helvetica','',8);
      $pdf->SetY($y);

      $pdf->Cell(10,4,utf8_decode("N°"),0,'L');
      $pdf->SetFont('Helvetica','',9);

      $pdf->line(24,15,24,$pdf->GetPageHeight()-15);

      $pdf->SetX(25);
      $pdf->Cell(45,4,utf8_decode("Prenom"),0,'L');
      $pdf->SetFont('Helvetica','',9);

      $pdf->line(71,15,71,$pdf->GetPageHeight()-15);


      $pdf->SetX(72);
      $pdf->Cell(47,4,utf8_decode("Nom"),0,'L');

      $pdf->line(120,15,120,$pdf->GetPageHeight()-15);


      $pdf->SetX(121);
      $pdf->Cell(44,4,utf8_decode("Matricule"),0,'L');

      $pdf->line(166,15,166,$pdf->GetPageHeight()-15);

      $pdf->SetX(167);
      $pdf->MultiCell(31,4,utf8_decode("Tel"),0,'L');

      $pdf->line(12,$y+4,$pdf->GetPageWidth()-12,$y+4);
     

      $y=20;
      $pdf->SetY($y);
    if($result){
      $i = 0;
      $num = $db->num_rows($result);
      while ($i < $num){

        $liste = $db->fetch_object($result);

        $pdf->SetFont('Helvetica','',8);
        $pdf->SetY($y);
        $pdf->SetX(13);
        $pdf->Cell(10,4,utf8_decode($i+1),0,'L');

        $pdf->SetX(25);
        $pdf->Cell(45,4,utf8_decode($liste->firstname),0,'L');

      
        $pdf->SetX(72);
        $pdf->Cell(47,4,utf8_decode($liste->lastname),0,'L');

        $pdf->SetX(121);
        $pdf->Cell(44,4,utf8_decode($liste->matricule),0,'L');

        $pdf->SetX(167);
        $pdf->MultiCell(31,4,utf8_decode(($liste->office_phone?$liste->office_phone:$liste->office_fax)),0,'L');

        $pdf->line(12,$y+4,$pdf->GetPageWidth()-12,$y+4);

        $y += 6;
        $i ++;
      }
    }
      /*$pdf->SetX(100);

      $date = date("d/m/Y");
      $pdf->Cell(10,3,utf8_decode($date),0,'C');*/


    /*// ...ou export sur le serveur dans un dossier "fic"
    $pdf->Output('F', '../fic/test.pdf');
    ?>*/
     
    // affichage à l'écran...
    $pdf->Output('listePersonnel.pdf','I');

    $db->close();