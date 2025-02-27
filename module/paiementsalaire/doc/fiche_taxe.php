<?php


// Appel de la librairie FPDF
require '../../main.inc.php';

require("fpdf/fpdf.php");


// Connexion à la BDD (à personnaliser)
$id_societe = GETPOST('id_societe','int');
$sql = "SELECT nom FROM ".MAIN_DB_PREFIX."societe Where rowid=".$id_societe;
	$result1 = $db->query($sql);
  $sc = $db->fetch_object($result1);

  $sql = "SELECT * FROM ".MAIN_DB_PREFIX."type_taxe";
	$result = $db->query($sql);
	$obj_type_taxe = $db->fetch_object($result);
$nom = array();
$prenom = array();
$somme_taxe = array();
$somme_taxe = array();


$id_societe = GETPOST('id_societe','int');
$id_taxe = GETPOST("id_taxe", "int");
$annee = GETPOST("annee", "int");
$mois = GETPOST("mois", "int");
$mois_tab = array(" janvier "," février "," mars "," avril "," mai "," juin "," juillet "," août "," septembre "," octobre "," novembre "," décembre ");

global $sc, $obj_type_taxe, $annee, $mois, $mois_tab;


      $sql = "SELECT u.rowid, u.lastname, u.firstname, u.dateemployment, ue.fk_object, ue.egp FROM ".MAIN_DB_PREFIX."user as u";
      $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."user_extrafields as ue ON u.rowid=ue.fk_object Where ue.egp=".$id_societe;
      $user_result = $db->query($sql);

      

// Création de la class PDF
class PDF extends FPDF {
  
    // Header
    function Header() {
      // Logo : 8 >position à gauche du document (en mm), 2 >position en haut du document, 80 >largeur de l'image en mm). La hauteur est calculée automatiquement.
      //$this->Image('logo_agence.png',8,2);
      // Saut de ligne 20 mm
      //$this->Ln(20);
      
      $this->SetTitle("taxes",true);
      // Titre gras (B) police Helbetica de 11
      $this->SetFont('Helvetica','B',11);
      // fond de couleur gris (valeurs en RGB)
      $this->setFillColor(230,230,230);
       // position du coin supérieur gauche par rapport à la marge gauche (mm)
       $this->SetX(70);
       //$this->SetY(20);
      // Texte : 60 >largeur ligne, 8 >hauteur ligne. Premier 0 >pas de bordure, 1 >retour à la ligneensuite, C >centrer texte, 1> couleur de fond ok  
    
      return en_tete($this);
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

     $pdf->SetFont('Helvetica','',8);
      $pdf->SetY($y);

      $pdf->Cell(15,4,utf8_decode("N°"),0,'L');
      $pdf->SetFont('Helvetica','',9);

      $pdf->line(24,15,24,$pdf->GetPageHeight()-15);

      $pdf->SetX(25);
      $pdf->Cell(56,4,utf8_decode("Prenom"),0,'L');
      $pdf->SetFont('Helvetica','',9);

      $pdf->line(82,15,82,$pdf->GetPageHeight()-30);


      $pdf->SetX(83);
      $pdf->Cell(56,4,utf8_decode("Nom"),0,'L');

      $pdf->line(139,15,139,$pdf->GetPageHeight()-15);


      $pdf->SetX(140);
      $pdf->Cell(44,4,utf8_decode("Montant"),0,'L');

      /*$pdf->line(166,15,166,$pdf->GetPageHeight()-15);

      $pdf->SetX(167);
      $pdf->MultiCell(31,4,utf8_decode("Employeur"),0,'L');*/

      $pdf->line(12,$y+4,$pdf->GetPageWidth()-12,$y+4);
      $y += 6;
      $numero = 1;
      if($user_result){
        $i = 0;
        $num = $db->num_rows($user_result);
        while ($i < $num){
          $liste = $db->fetch_object($user_result);
          $sql_salarie = "SELECT matricule FROM ".MAIN_DB_PREFIX."salarie WHERE fk_user=".$liste->rowid;
          $result_salarie = $db->query($sql_salarie);
          $salarie = $db->fetch_object($result_salarie);

        $sql_bulletin = "SELECT rowid FROM ".MAIN_DB_PREFIX."bulletin WHERE matricule='".$salarie->matricule."' AND annee='".$annee."' AND mois='".$mois."'";
        $result_bulletin = $db->query($sql_bulletin);
        $nombre = $db->num_rows($result_bulletin);

        if($nombre>0){
          $bulletin = $db->fetch_object($result_bulletin);
        $sql_cotis = "SELECT * FROM ".MAIN_DB_PREFIX."bulletin_taxe WHERE fk_bulletin=".$bulletin->rowid." AND fk_taxe=".$id_taxe;
        
        $result_cotis = $db->query($sql_cotis);
        $taxe = $db->fetch_object($result_cotis);


        $pdf->SetFont('Helvetica','',8);
        $pdf->SetY($y);
        $pdf->SetX(13);
        $pdf->Cell(10,4,utf8_decode($numero),0,'L');

        $pdf->SetX(25);
        $pdf->Cell(45,4,utf8_decode($liste->firstname),0,'L');

      
        $pdf->SetX(83);
        $pdf->Cell(47,4,utf8_decode($liste->lastname),0,'L');

        $pdf->SetX(140);
        $pdf->Cell(44,4,utf8_decode(apres_virgule($taxe->montant, 2)),0,'L');

        //$pdf->SetX(167);
        //$pdf->MultiCell(31,4,utf8_decode(apres_virgule($taxe->montant_employeur, 2)),0,'L');

        $somme_taxe[$i] = $taxe->montant;

        $pdf->line(12,$y+4,$pdf->GetPageWidth()-12,$y+4);

        $y += 6;
       
        if($pdf->GetY()+6 >= ($pdf->GetPageHeight()-30)){
          $pdf->AddPage();
          // Polices par défaut : Helvetica taille 9
          $pdf->SetFont('Helvetica','',9);
          // Couleur par défaut : noir
          $pdf->SetTextColor(0);
          // Compteur de pages {nb}
          $pdf->AliasNbPages();
        }
        $numero ++;
      }

      $i ++;
    }
  }
    if(($pdf->GetY()+5) < ($pdf->GetPageHeight()-30)){
      $pdf->line(12,$pdf->GetPageHeight()-30,$pdf->GetPageWidth()-12,$pdf->GetPageHeight()-30);
      $pdf->SetX(13);
      $pdf->SetY($pdf->GetPageHeight()-25);
      $pdf->Cell(10,4,utf8_decode("Total"),0,'L');
      $s1 = 0;
      for ($i=0; $i < count($somme_taxe); $i++) { 
        $s2 += $somme_taxe[$i];
      }
      $pdf->SetX(123);
      $pdf->Cell(44,4,utf8_decode(round($s1, 3)),0,'L');

      $pdf->SetX(170);
      $pdf->MultiCell(31,4,utf8_decode(round($s2, 3)),0,'L');

    }
      /*$pdf->SetX(100);

      $date = date("d/m/Y");
      $pdf->Cell(10,3,utf8_decode($date),0,'C');*/


    /*// ...ou export sur le serveur dans un dossier "fic"
    $pdf->Output('F', '../fic/test.pdf');
    ?>*/
     
    // affichage à l'écran...
    $pdf->Output('listePersonnel.pdf','I');

    $db->free();


    function en_tete($pdf){
      global $sc, $obj_type_taxe, $mois, $mois_tab, $annee;
      $titre = "Fiche ".$obj_type_taxe->libelle." de ".$sc->nom."".$mois_tab[$mois-1]."".$annee;
      $pdf->Cell(60,3, $titre,0,0,'R',0);
      $pdf->line(12,$pdf->GetY()+5,$pdf->GetPageWidth()-12,$pdf->GetY()+5);
    }


    function apres_virgule($valeur, $decalage){
      $val_retour = "";
      $tab = explode('.',$valeur);
      if(count($tab) > 1){
        $chif = $tab[1];
        $val_retour = $tab[0].".";
        
        if((strlen($chif))>$decalage){
        for ($i=0; $i < $decalage; $i++) { 
          $val_retour .= $chif[$i];
        }
        }else{
        for ($i=0; $i < strlen($chif); $i++) { 
          $val_retour .= $chif[$i];
        }
        for ($i=0; $i < ($decalage - strlen($chif)); $i++) { 
          $val_retour .= "0";
        }
        }
      }else{
        $val_retour = $valeur.".";
        for ($i=0; $i < $decalage; $i++) { 
        $val_retour .= "0";
        }
      }
    
      return $val_retour;
      }