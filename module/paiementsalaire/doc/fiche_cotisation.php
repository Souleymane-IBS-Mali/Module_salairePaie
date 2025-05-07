<?php


// Appel de la librairie FPDF
require '../../main.inc.php';

require("fpdf/fpdf.php");

$annee = GETPOST("annee", "int");
$mois = GETPOST("mois", "int");
$id_societe = GETPOST('id_societe','int');
$id_convention = GETPOST('id_convention','int');

$mois_tab = array(" janvier "," février "," mars "," avril "," mai "," juin "," juillet "," août "," septembre "," octobre "," novembre "," décembre ");

$action =  GETPOST("action", "aplha");
if($action == "telecharger")
 $mode = "D";
else $mode = "I";

$partie = "partie1";

global $partie;
// Connexion à la BDD (à personnaliser)

// Création de la class PDF
class PDF extends FPDF {
  
    // Header
    function Header() {
      // Logo : 8 >position à gauche du document (en mm), 2 >position en haut du document, 80 >largeur de l'image en mm). La hauteur est calculée automatiquement.
      //$this->Image('logo_agence.png',8,2);
      // Saut de ligne 20 mm
      //$this->Ln(20);
      
      $this->SetTitle("Déclaration INPS",true);
      // Titre gras (B) police Helbetica de 11
      $this->SetFont('Arial','B',11);
      // fond de couleur gris (valeurs en RGB)
      $this->setFillColor(230,230,230);
       // position du coin supérieur gauche par rapport à la marge gauche (mm)
       $this->SetX(120);
       $this->SetY(3);
      // Texte : 60 >largeur ligne, 8 >hauteur ligne. Premier 0 >pas de bordure, 1 >retour à la ligneensuite, C >centrer texte, 1> couleur de fond ok  
    
      return en_tete($this);
      // Saut de ligne 10 mm
     // $this->line(12,$this->GetY()+30,$this->GetPageWidth()-12,$this->GetY()+30);
      
    }

    
    // Footer
    function Footer() {

      // Positionnement à 1,5 cm du bas
      $this->SetY(-10);
      
      // Police Arial italique 8
      $this->SetFont('Arial','I',8);
      // Numéro de page, centré (C)
      $this->Cell(0,5,'Page '.$this->PageNo().'/{nb}',0,0,'C');
      //$this->line(12,$this->GetY(),$this->GetPageWidth()-12,$this->GetY());

    }

        // On active la classe une fois pour toutes les pages suivantes
    // Format portrait (>P) ou paysage (>L), en mm (ou en points > pts), A4 (ou A5, etc.)

    function Ellipse($x, $y, $rx, $ry, $style='D')
{
    if($style=='F')
        $op='f';
    elseif($style=='FD' || $style=='DF')
        $op='B';
    else
        $op='S';
    $lx=4/3*(M_SQRT2-1)*$rx;
    $ly=4/3*(M_SQRT2-1)*$ry;
    $k=$this->k;
    $h=$this->h;
    $this->_out(sprintf('%.2F %.2F m %.2F %.2F %.2F %.2F %.2F %.2F c',
        ($x+$rx)*$k,($h-$y)*$k,
        ($x+$rx)*$k,($h-($y-$ly))*$k,
        ($x+$lx)*$k,($h-($y-$ry))*$k,
        $x*$k,($h-($y-$ry))*$k));
    $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c',
        ($x-$lx)*$k,($h-($y-$ry))*$k,
        ($x-$rx)*$k,($h-($y-$ly))*$k,
        ($x-$rx)*$k,($h-$y)*$k));
    $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c',
        ($x-$rx)*$k,($h-($y+$ly))*$k,
        ($x-$lx)*$k,($h-($y+$ry))*$k,
        $x*$k,($h-($y+$ry))*$k));
    $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c %s',
        ($x+$lx)*$k,($h-($y+$ry))*$k,
        ($x+$rx)*$k,($h-($y+$ly))*$k,
        ($x+$rx)*$k,($h-$y)*$k,
        $op));
}

function Circle($x, $y, $r, $style='D')
{
    $this->Ellipse($x,$y,$r,$r,$style);
}

function SetCharSpacing($cs) { 
  $this->_out(sprintf('BT %.3F Tc ET',$cs*$this->k)); 
}

  }
    $pdf = new PDF('L','mm','A4');
    // Nouvelle page A4 (incluant ici logo, titre et pied de page)
    $pdf->AddPage();
    // Polices par défaut : Helvetica taille 9
    $pdf->SetFont('Arial','B',9);
    // Couleur par défaut : noir
    $pdf->SetTextColor(0);
    // Compteur de pages {nb}
    $pdf->AliasNbPages();


    $sql = "SELECT sc.*, sce.fk_object, sce.numero_inps FROM ".MAIN_DB_PREFIX."societe as sc";
    $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."societe_extrafields as sce ON sc.rowid=sce.fk_object";
    $sql .= " WHERE sc.rowid=".$id_societe;
    $res_verif = $db->query($sql);

    $sc = $db->fetch_object($res_verif);

    $y = $pdf->GetY()+2;
    $pdf->SetY($y);

    //1er rectangle-----------------------------------------------------------------
    $pdf->SetLeftMargin(5);
   $pdf->SetX(5);
   $pdf->Cell(142,30, "",1,0,'');

   //nom
   $pdf->SetFont('Arial','',10);
   $pdf->SetY($pdf->GetY()+2);
   $pdf->Cell(15,3, "Nom ",0,0,'L');
   $pdf->SetX(30);
   $pdf->MultiCell(70,3, utf8_decode(strtoupper($sc->nom)),0,'L');
   //adresse
   $pdf->SetY($pdf->GetY()+1);
   $pdf->Cell(15,3, "Adresse ",0,0,'L');
   $pdf->SetX(30);
   $pdf->MultiCell(115,3, utf8_decode(strtoupper($sc->address?:"")),0,'L');

   $pdf->SetY($pdf->GetY()+11);
   $pdf->Cell(15,3, "Tel ",0,0,'L');
   $pdf->SetX(30);
   $pdf->MultiCell(70,3, utf8_decode($sc->phone."".($sc->phone? ($sc->fax? $sc->fax."/" : "") : "")),0,'L');

   $pdf->SetY($pdf->GetY()+1);
   $pdf->Cell(15,3, "Email ",0,0,'L');
   $pdf->SetX(30);
   $pdf->MultiCell(70,3, utf8_decode($sc->email),0,'L');

   //2ème rectangle -------------------------------------------------------
   $pdf->SetY($y);
   $x = 149;
   $pdf->SetX($x);
   $pdf->Cell(56,15, "",1,0,'');

   $pdf->SetY($pdf->GetY()+1);
   $pdf->SetX($x + 1);
   $pdf->SetFont('Arial','',10);
   $pdf->MultiCell(64,3, utf8_decode("Période de versement : "),0,'L');

   $pdf->SetY($pdf->GetY()+3);
   $pdf->SetX($x + 1);
   $pdf->Cell(30,3, utf8_decode("Du 01/".$mois."/".$annee),0,0,'L');

   $au = cal_days_in_month(CAL_GREGORIAN, $mois, $annee);
   $pdf->SetX($x + 24);
   $pdf->MultiCell(30,3, utf8_decode("au ".$au."/".$mois."/".$annee),0,'L');

   $pdf->SetY($pdf->GetY()+8);
   $pdf->SetX($x + 1);
   $pdf->SetFont('Arial','',10);
   $pdf->MultiCell(64,3, utf8_decode("Date limite de déclaration "),0,'L');

   $pdf->SetY($pdf->GetY()+4);
   $pdf->SetX($x + 1);
   $pdf->MultiCell(64,3, utf8_decode("Date d'échéance de Paiement "),0,'L');
   
   //Numéro employeur-----------------------------------------

   $pdf->SetY($y);
   $pdf->SetX($x + 69);
   $pdf->SetFont('Arial','',10);
   $pdf->MultiCell(40,3, utf8_decode("N° Employeur : ".$sc->numero_inps),0,'C');

   $pdf->SetY($pdf->GetY()+3);
   $pdf->SetX($x + 80);
   $pdf->SetFont('Arial','B',10);
   $pdf->MultiCell(40,3, utf8_decode("COMMUNE I"),0,'C');

//4ème Rectangle-------------------------------------------------------
$pdf->SetFont('Arial','B',6);
   $pdf->SetLeftMargin(5);
   $pdf->SetX(5);
   $y = $pdf->GetY()+23;
    $pdf->SetY($y);
    $pdf->Cell(60,17, "",1,0,'');
    $sql_verif = "SELECT rowid, nom, prenom, matricule, salaire_brut_cotisable, inps, amo FROM ".MAIN_DB_PREFIX."bulletin WHERE annee=".$annee." AND mois=".$mois." AND fk_societe=".$id_societe;
    $res_verif = $db->query($sql_verif);

    $nb = 0;
    if($res_verif){
        $i = 0;
        $num = $db->num_rows($res_verif);
        $nb = $num;
        while ($i < $num){
            $rowid_bulletin[$i] = $db->fetch_object($res_verif);
          $i ++;
        }
    }
    global $nb, $liste_user, $liste_salarie, $rowid_bulletin;

    $pdf->SetFont('Arial','',8);
    $pdf->SetY($y+1);
   $pdf->SetX(5);
   $pdf->MultiCell(60,3, utf8_decode("Nombre de salariés occupés"),0,'L');
   
   $pdf->SetY($pdf->GetY()+1);
   $pdf->SetX(5);
   $pdf->Cell(30,3, utf8_decode("Permanents"),0,0,'L');
   $pdf->SetX(30);
   $pdf->MultiCell(20,3, utf8_decode($nb),0,'L');


   $pdf->SetY($pdf->GetY()+1);
   $pdf->SetX(5);
   $pdf->Cell(30,3, utf8_decode("Expatriés"),0,0,'L');
   $pdf->SetX(30);
   $pdf->MultiCell(5,3, utf8_decode(0),0,'L');

   $pdf->SetY($pdf->GetY()+1);
   $pdf->SetX(5);
   $pdf->Cell(30,3, utf8_decode("Occasionels"),0,0,'L');
   $pdf->SetX(30);
   $pdf->MultiCell(5,3, utf8_decode(0),0,'L');

//5ème Rectangle--------------------------------------------------------------------------------
$x_rect2 = 66;
   $pdf->SetY($y);
   $pdf->SetLeftMargin($x_rect2 + 1);
   $pdf->SetX($x_rect2 + 1);
   $pdf->Cell(80,17, "",1,0,'');

   $pdf->SetY($y+1);
   $pdf->SetX($x_rect2 + 1);
   $pdf->MultiCell(80,3, utf8_decode("Masse Salariale soumlises è cotisation"),0,'L');

   $y_permant = $pdf->GetY()+1;
   $pdf->SetY($y_permant);
   $pdf->SetX($x_rect2 + 1);
   $pdf->Cell(30,3, utf8_decode("Permanents"),0,0,'L');

   $pdf->SetX($x_rect2 + 80-15);
   $pdf->MultiCell(13,3, utf8_decode(0),0,'L');

   $pdf->SetY($pdf->GetY()+1);
   $pdf->SetX($x_rect2 + 1);
   $pdf->Cell(30,3, utf8_decode("Expatriés"),0,0,'L');
   $pdf->SetX($x_rect2 + 80-15);
   $pdf->MultiCell(13,3, utf8_decode(0),0,'L');

   $pdf->SetY($pdf->GetY()+1);
   $pdf->SetX($x_rect2 + 1);
   $pdf->Cell(30,3, utf8_decode("Occasionels"),0,0,'L');
   $pdf->SetX($x_rect2 + 80-15);
   $pdf->MultiCell(13,3, utf8_decode(0),0,'L');



//6ème Rectangle------------------------------------------------------------------------------------
$pdf->SetFont('Arial','',8);
$y_coche = $y;
$x_coche = 151;
$pdf->SetY($y_coche);
$pdf->SetX($x_coche-2);
$pdf->Cell(56,17, "",1,0,'');

$pdf->SetY($y_coche+1);
$pdf->SetX($x_coche);
$pdf->MultiCell(52,3, utf8_decode("Mode de Paiement"),0,'C');

$pdf->SetFont('Arial','',8);
$y_coche += 6;
$pdf->SetY($y_coche);
$pdf->SetX($x_coche+2);
$pdf->Cell(6,4, "",1,0,'');


$pdf->SetX($x_coche+9);
$pdf->Cell(10,4, utf8_decode("Chèque"),0,0,'L');
$pdf->SetFont('Arial','B',8);
if($type_paiement == 'virement'){
  $y_cocher_valider = $y_coche+7;
  $x_cocher_valider = $x_coche+3;
}elseif($type_paiement == 'caisse'){
  $y_cocher_valider = $y_coche+1;
  $x_cocher_valider = $x_coche+28;

}elseif($type_paiement == 'autre'){
  $y_cocher_valider = $y_coche+7;
  $x_cocher_valider = $x_coche+28;
}else{
  $y_cocher_valider = $y_coche+1;
  $x_cocher_valider = $x_coche+3;
}

$pdf->SetY($y_cocher_valider);
$pdf->SetX($x_cocher_valider);
$pdf->Cell(2,2, "X",0,0,'');
$pdf->SetFont('Arial','',8);

$pdf->SetY($y_coche);
$pdf->SetX($x_coche+27);
$pdf->Cell(6,4, "",1,0,'');
$pdf->SetX($x_coche+34);
$pdf->Cell(10,4, utf8_decode("Caisses"),0,0,'L');


$pdf->SetY($y_coche+6);
$pdf->SetX($x_coche+2);
$pdf->Cell(6,4, "",1,0,'');
$pdf->SetX($x_coche+9);
$pdf->Cell(10,4, utf8_decode("Virement"),0,0,'L');

$pdf->SetX($x_coche+27);
$pdf->Cell(6,4, "",1,0,'');
$pdf->SetX($x_coche+34);
$pdf->Cell(10,4, utf8_decode("Autres"),0,0,'L');


   //L'ellipse
   $pdf->SetDrawColor(0, 0, 0);
   $pdf->Ellipse(248,$y,36,16,'D');
   $pdf->SetY($y-11);
   $pdf->SetX(235);
   $pdf->SetFont('Arial','B',10);
   $pdf->MultiCell(30,3, utf8_decode("EMPLOYEURS"),0,'C');

   $pdf->SetY($y-4);
   $pdf->SetX(221);
   $pdf->SetFont('Arial','',8);
   $pdf->MultiCell(55,4, utf8_decode("Les cotisations que vous versez à l'INPS servent à payer les prestations auxquelles ont droit vos travailleurs"),0,'L');

   //7ème rectangle Tableau -------------------------------------------------------------------------------------------------
   $pdf->SetFont('Arial','B',7);

   $y = $pdf->GetY()+11;
   $y_rect = $y;

   $pdf->SetY($y);
   $pdf->SetX(5);
   $pdf->Cell(19,10, utf8_decode("N° Assurés"),0,0,'C');

   $pdf->SetFont('Arial','B',10);
   $pdf->SetX(24);
   $pdf->Cell(58,10, utf8_decode("Nom"),0,0,'C');
   $pdf->SetFont('Arial','B',7);

   $pdf->SetX(82);
   $pdf->MultiCell(20,10, utf8_decode("Numéro AMO"),0,'C');

   $pdf->SetY($y);
   $pdf->SetX(102);
   $pdf->Cell(4,4, utf8_decode(""),0,0,'C');

   $pdf->SetX(106);
   $pdf->MultiCell(4,3, utf8_decode("Mois"),0,'C');

   $pdf->SetY($y);
   $pdf->SetX(110);
   $pdf->MultiCell(11,5, utf8_decode("Date entrée"),0,'C');

   $pdf->SetY($y);
   $pdf->SetX(121);
   $pdf->MultiCell(11,5, utf8_decode("Date sortie"),0,'C');

   $pdf->SetY($y);
   $pdf->SetX(132);
   $pdf->MultiCell(17,10, utf8_decode("Brut"),0,'C');

    $pdf->SetY($y);
   $pdf->SetX(149);
    if($id_convention == 1)
      $pdf->MultiCell(17,5, utf8_decode("Accident du Travail 4%"),0,'C');
    else  $pdf->MultiCell(17,5, utf8_decode("Accident du Travail 2%"),0,'C');


   $pdf->SetY($y);
   $pdf->SetX(166);
   $pdf->MultiCell(17,4, utf8_decode("Prestations Familiales 8%"),0,'C');

   $pdf->SetY($y);
   $pdf->SetX(183);
   $pdf->MultiCell(17,10, utf8_decode("Rétraite 7%"),0,'C');

   $pdf->SetY($y);
   $pdf->SetX(200);
   $pdf->MultiCell(17,3.5, utf8_decode("Ivalidité Allocations Survivant 2%"),0,'C');

   $pdf->SetY($y);
   $pdf->SetX(217);
   $pdf->MultiCell(17,10, utf8_decode("ANPE 1%"),0,'C');

   $pdf->SetY($y);
   $pdf->SetX(234);
   $pdf->MultiCell(17,5, utf8_decode("AMO employeur 3.5%"),0,'C');

   $pdf->SetY($y);
   $pdf->SetX(251);
   $pdf->MultiCell(17,5, utf8_decode("AMO employé 3.06%"),0,'C');

   $pdf->SetY($y);
   $pdf->SetX(268);
   $pdf->MultiCell(24,5, utf8_decode("Total Cotisations"),0,'C');
   
      $pdf->SetLineWidth(0.5);
      $pdf->line(5,$pdf->GetY()+10,$pdf->GetPageWidth()-5,$pdf->GetY()+10); 

   $total_brut = 0;
   $total_acc = 0;
   $total_prst = 0;
   $total_retraite = 0;
   $total_alloc = 0;
   $total_anpe = 0;
   $total_amo_sal = 0;
   $total_amo_patro = 0;
   $total_total_horizotal = 0;

   //Remplissage du tableau
   $pdf->SetFillColor(190, 190, 190);
   

    $y = $pdf->GetY() +12; 
    
if($nb>0){
   for ($i=0; $i < $nb; $i++) {
     if(($pdf->GetY() + 30) > $pdf->GetPageHeight()){
      //print '////'.$pdf->GetPageHeight().'***'.$pdf->GetY();
      $y = $pdf->GetY()+1;
      $pdf->line(24,$y_rect,24,$y_rect+($y - $y_rect+5));
      $pdf->line(82,$y_rect,82,$y_rect+($y - $y_rect+5));
      $pdf->line(102,$y_rect,102,$y_rect+($y - $y_rect+5));
      $pdf->line(106,$y_rect,106,$y_rect+($y - $y_rect+5));
      $pdf->line(110,$y_rect,110,$y_rect+($y - $y_rect+5));
      $pdf->line(121,$y_rect,121,$y_rect+($y - $y_rect+5));
      $pdf->line(132,$y_rect,132,$y_rect+($y - $y_rect+5));
      $pdf->line(149,$y_rect,149,$y_rect+($y - $y_rect+5));
      $pdf->line(166,$y_rect,166,$y_rect+($y - $y_rect+5));
      $pdf->line(183,$y_rect,183,$y_rect+($y - $y_rect+5));
      $pdf->line(200,$y_rect,200,$y_rect+($y - $y_rect+5));
      $pdf->line(217,$y_rect,217,$y_rect+($y - $y_rect+5));
      $pdf->line(234,$y_rect,234,$y_rect+($y - $y_rect+5));
      $pdf->line(251,$y_rect,251,$y_rect+($y - $y_rect+5));
      $pdf->line(268,$y_rect,268,$y_rect+($y - $y_rect+5));


      //le rectangle en cas de changement de page
      $pdf->SetY($y_rect);
      $pdf->SetX(5);
     $pdf->Cell($pdf->GetPageWidth()-9.5,$y - $y_rect + 5, "",1,0,'');

     $partie = "partie4";
      $pdf->AddPage();
      $pdf->SetFont('Arial','B',7);

      $y = $pdf->GetY();
      $y_rect = $y;
    
      $pdf->SetY($y);
   $pdf->SetX(5);
   $pdf->Cell(19,10, utf8_decode("N° Assurés"),0,0,'C');

   $pdf->SetFont('Arial','B',10);
   $pdf->SetX(24);
   $pdf->Cell(58,10, utf8_decode("Nom"),0,0,'C');
   $pdf->SetFont('Arial','B',7);

   $pdf->SetX(82);
   $pdf->MultiCell(20,10, utf8_decode("Numéro AMO"),0,'C');

   $pdf->SetY($y);
   $pdf->SetX(102);
   $pdf->Cell(4,4, utf8_decode(""),0,0,'C');

   $pdf->SetX(106);
   $pdf->MultiCell(4,3, utf8_decode("Mois"),0,'C');

   $pdf->SetY($y);
   $pdf->SetX(110);
   $pdf->MultiCell(11,5, utf8_decode("Date entrée"),0,'C');

   $pdf->SetY($y);
   $pdf->SetX(121);
   $pdf->MultiCell(11,5, utf8_decode("Date sortie"),0,'C');

   $pdf->SetY($y);
   $pdf->SetX(132);
   $pdf->MultiCell(17,10, utf8_decode("Brut"),0,'C');

    $pdf->SetY($y);
   $pdf->SetX(149);
    if($id_convention == 1)
      $pdf->MultiCell(17,5, utf8_decode("Accident du Travail 4%"),0,'C');
    else  $pdf->MultiCell(17,5, utf8_decode("Accident du Travail 2%"),0,'C');


   $pdf->SetY($y);
   $pdf->SetX(166);
   $pdf->MultiCell(17,4, utf8_decode("Prestations Familiales 8%"),0,'C');

   $pdf->SetY($y);
   $pdf->SetX(183);
   $pdf->MultiCell(17,10, utf8_decode("Rétraite 7%"),0,'C');

   $pdf->SetY($y);
   $pdf->SetX(200);
   $pdf->MultiCell(17,3.5, utf8_decode("Ivalidité Allocations Survivant 2%"),0,'C');

   $pdf->SetY($y);
   $pdf->SetX(217);
   $pdf->MultiCell(17,10, utf8_decode("ANPE 1%"),0,'C');

   $pdf->SetY($y);
   $pdf->SetX(234);
   $pdf->MultiCell(17,5, utf8_decode("AMO employeur 3.5%"),0,'C');

   $pdf->SetY($y);
   $pdf->SetX(251);
   $pdf->MultiCell(17,5, utf8_decode("AMO employé 3.06%"),0,'C');

   $pdf->SetY($y);
   $pdf->SetX(268);
   $pdf->MultiCell(24,5, utf8_decode("Total Cotisations"),0,'C');
   
      $pdf->SetLineWidth(0.5);
      $pdf->line(5,$pdf->GetY()+10,$pdf->GetPageWidth()-5,$pdf->GetY()+10);
      $y = $pdf->GetY() +12; 
  }
              $pdf->SetY($y);

              $pdf->SetFont('Arial','',7);
              $pdf->SetX(5);
              $pdf->Cell(19,4, utf8_decode($rowid_bulletin[$i]->inps?:""),0,0,'L');

              $pdf->SetFont('Arial','',8);
              $pdf->SetX(24);
              $pdf->Cell(58,4, utf8_decode($rowid_bulletin[$i]->prenom." ".$rowid_bulletin[$i]->nom),0,0,'L');

              $pdf->SetFont('Arial','',7);
              $pdf->SetX(82);
              $pdf->Cell(58,4, utf8_decode($rowid_bulletin[$i]->amo?:""),0,0,'L');

              $pdf->SetFont('Arial','',7);
              $pdf->SetX(102);
              $pdf->Cell(4,4, utf8_decode("P"),0,0,'C');

              $pdf->SetX(106);
              $pdf->Cell(4,4, utf8_decode("1"),0,0,'C');
              
              $total_horizontal = 0;

              //AMTP
            $sql_cotis = "SELECT montant_employeur FROM ".MAIN_DB_PREFIX."bulletin_cotisation WHERE fk_bulletin=".$rowid_bulletin[$i]->rowid." AND fk_cotisation=1";//AND fk_cotisation=".$id_cotisation;
            $result_cotis = $db->query($sql_cotis);
            $cotis = $db->fetch_object($result_cotis);

            $pdf->SetFont('Arial','',8);

            $taux_accident = 2;
            if($id_convention == 1)
              $taux_accident = 4;
            $pdf->SetX(132);
              $pdf->Cell(17,4, utf8_decode(apres_virgule($rowid_bulletin[$i]->salaire_brut_cotisable,  0)),0,0,'R');
              $total_brut += $rowid_bulletin[$i]->salaire_brut_cotisable;
              $pdf->SetX(149);
              $pdf->Cell(17,4, utf8_decode(apres_virgule($cotis->montant_employeur,  0)),0,0,'R');
              $total_horizontal += $cotis->montant_employeur;
              $total_acc += $cotis->montant_employeur;

              //Prestation familialles
              $sql_cotis = "SELECT montant_employeur FROM ".MAIN_DB_PREFIX."bulletin_cotisation WHERE fk_bulletin=".$rowid_bulletin[$i]->rowid." AND fk_cotisation=2";//AND fk_cotisation=".$id_cotisation;
              $result_cotis = $db->query($sql_cotis);
              $cotis = $db->fetch_object($result_cotis);
              $pdf->SetX(166);
              $pdf->Cell(17,4, utf8_decode(apres_virgule($cotis->montant_employeur,  0)),0,0,'R');
              $total_horizontal += $cotis->montant_employeur;
              $total_prst += $cotis->montant_employeur;

              //Retraite
              $sql_cotis = "SELECT montant_employeur, montant_employe FROM ".MAIN_DB_PREFIX."bulletin_cotisation WHERE fk_bulletin=".$rowid_bulletin[$i]->rowid." AND fk_cotisation=3";//AND fk_cotisation=".$id_cotisation;
              $result_cotis = $db->query($sql_cotis);
              $cotis = $db->fetch_object($result_cotis);
              $pdf->SetX(183);
              $pdf->Cell(17,4, utf8_decode(apres_virgule($cotis->montant_employeur + $cotis->montant_employe,  0)),0,0,'R');
              $total_horizontal += $cotis->montant_employeur + $cotis->montant_employe;
              $total_retraite += $cotis->montant_employeur + $cotis->montant_employe;

              //Invalidité allocation
              $sql_cotis = "SELECT montant_employeur FROM ".MAIN_DB_PREFIX."bulletin_cotisation WHERE fk_bulletin=".$rowid_bulletin[$i]->rowid." AND fk_cotisation=4";//AND fk_cotisation=".$id_cotisation;
              $result_cotis = $db->query($sql_cotis);
              $cotis = $db->fetch_object($result_cotis);
              $pdf->SetX(200);
              $pdf->Cell(17,4, utf8_decode(apres_virgule($cotis->montant_employeur,  0)),0,0,'R');
              $total_horizontal += $cotis->montant_employeur;
              $total_alloc += $cotis->montant_employeur;;

              //ANPE
              $sql_cotis = "SELECT montant_employeur FROM ".MAIN_DB_PREFIX."bulletin_cotisation WHERE fk_bulletin=".$rowid_bulletin[$i]->rowid." AND fk_cotisation=5";//AND fk_cotisation=".$id_cotisation;
              $result_cotis = $db->query($sql_cotis);
              $cotis = $db->fetch_object($result_cotis);
              $pdf->SetX(217);
              $pdf->Cell(17,4, utf8_decode(apres_virgule($cotis->montant_employeur,  0)),0,0,'R');
              $total_horizontal += $cotis->montant_employeur;
              $total_anpe += $cotis->montant_employeur;

              //AMO
              $sql_cotis = "SELECT montant_employeur, montant_employe FROM ".MAIN_DB_PREFIX."bulletin_cotisation WHERE fk_bulletin=".$rowid_bulletin[$i]->rowid." AND fk_cotisation=6";//AND fk_cotisation=".$id_cotisation;
              $result_cotis = $db->query($sql_cotis);
              $cotis = $db->fetch_object($result_cotis);
              $pdf->SetX(234);
              $pdf->Cell(17,4, utf8_decode(apres_virgule($cotis->montant_employeur, 0)),0,0,'R');
              $total_horizontal += $cotis->montant_employeur;
              $total_amo_patro += $cotis->montant_employeur;

              $pdf->SetX(251);
              $pdf->Cell(17,4, utf8_decode(apres_virgule($cotis->montant_employe, 0)),0,0,'R');
              $total_horizontal += $cotis->montant_employe;
              $total_amo_sal += $cotis->montant_employe;

              $pdf->SetY($y-0.3);
              $pdf->SetX(268);
              $pdf->SetFont('Arial','B',8);

              $pdf->Cell(24,5, utf8_decode(apres_virgule($total_horizontal, 0)),0,0,'R',true);
              $total_total_horizotal += $total_horizontal;
            
              if(!(($pdf->GetY() + 30) > $pdf->GetPageHeight()))
                $pdf->line(5,$y+5,$pdf->GetPageWidth()-5,$y+5);
    
            $y += 6;
          
       

   }
   /*if(($pdf->GetPageWidth()-$y)<50){
     $pdf->AddPage();
      $y = $pdf->GetY()+6;
   }*/
    $pdf->SetY($y+1);
    $pdf->SetFont('Arial','',8);
    $pdf->SetX(5);
    $pdf->Cell(96,4, utf8_decode("Total"),0,0,'C');


    $pdf->SetFont('Arial','',6.5);
            $pdf->SetX(132);
              $pdf->Cell(16,5, utf8_decode(apres_virgule($total_brut,  0)),0,0,'R',true);

              $pdf->SetFont('Arial','B',8);

              $pdf->SetX(149);
              $pdf->Cell(16,5, utf8_decode(apres_virgule($total_acc,  0)),0,0,'R',true);

              $pdf->SetX(166);
              $pdf->Cell(16,5, utf8_decode(apres_virgule($total_prst,  0)),0,0,'R',true);

              $pdf->SetX(183);
              $pdf->Cell(16,5, utf8_decode(apres_virgule($total_retraite,  0)),0,0,'R',true);

              $pdf->SetX(200);
              $pdf->Cell(16,5, utf8_decode(apres_virgule($total_alloc,  0)),0,0,'R',true);

              $pdf->SetX(217);
              $pdf->Cell(16,5, utf8_decode(apres_virgule($total_anpe,  0)),0,0,'R',true);

              $pdf->SetX(234);
              $pdf->Cell(16,5, utf8_decode(apres_virgule($total_amo_sal, 0)),0,0,'R',true);

              $pdf->SetX(251);
              $pdf->Cell(16,5, utf8_decode(apres_virgule($total_amo_patro, 0)),0,0,'R',true);
        
              $pdf->SetX(268);
              $pdf->Cell(24,5, utf8_decode(apres_virgule($total_total_horizotal, 0)),0,0,'R',true);

                $y = $pdf->GetY()+6;
                $pdf->line(24,$y_rect,24,$y_rect+($y - $y_rect-8));
                $pdf->line(82,$y_rect,82,$y_rect+($y - $y_rect-8));
                $pdf->line(102,$y_rect,102,$y_rect+($y - $y_rect-8));
                $pdf->line(106,$y_rect,106,$y_rect+($y - $y_rect-8));
                $pdf->line(110,$y_rect,110,$y_rect+($y - $y_rect-8));
                $pdf->line(121,$y_rect,121,$y_rect+($y - $y_rect-8));
                $pdf->line(132,$y_rect,132,$y_rect+($y - $y_rect));
                $pdf->line(149,$y_rect,149,$y_rect+($y - $y_rect));
                $pdf->line(166,$y_rect,166,$y_rect+($y - $y_rect));
                $pdf->line(183,$y_rect,183,$y_rect+($y - $y_rect));
                $pdf->line(200,$y_rect,200,$y_rect+($y - $y_rect));
                $pdf->line(217,$y_rect,217,$y_rect+($y - $y_rect));
                $pdf->line(234,$y_rect,234,$y_rect+($y - $y_rect));
                $pdf->line(251,$y_rect,251,$y_rect+($y - $y_rect));
                $pdf->line(268,$y_rect,268,$y_rect+($y - $y_rect));


    $y = $pdf->GetY()+6;
    $pdf->SetY($y_rect);
    $pdf->SetX(5);
   $pdf->Cell($pdf->GetPageWidth()-9.5,$y - $y_rect, "",1,0,'');

}else{
  $pdf->SetY($y);
    $pdf->SetY($y+1);
    $pdf->SetFont('Arial','B',6.5);
    $pdf->SetX(5);
    $pdf->Cell(96,4, utf8_decode("Total"),0,0,'C');

    $pdf->SetFont('Arial','',9);
            $pdf->SetX(132);
              $pdf->Cell(16,5, utf8_decode(0),0,0,'R',true);

              $pdf->SetX(149);
              $pdf->Cell(16,5, utf8_decode(0),0,0,'R',true);

              $pdf->SetX(166);
              $pdf->Cell(16,5, utf8_decode(0),0,0,'R',true);

              $pdf->SetX(183);
              $pdf->Cell(16,5, utf8_decode(0),0,0,'R',true);

              $pdf->SetX(200);
              $pdf->Cell(16,5, utf8_decode(0),0,0,'R',true);

              $pdf->SetX(217);
              $pdf->Cell(16,5, utf8_decode(0),0,0,'R',true);

              $pdf->SetX(234);
              $pdf->Cell(16,5, utf8_decode(0),0,0,'R',true);

              $pdf->SetX(251);
              $pdf->Cell(16,5, utf8_decode(0),0,0,'R',true);
        
              $pdf->SetX(268);
              $pdf->Cell(24,5, utf8_decode(0),0,0,'R',true);

    $y = $pdf->GetY()+5;
    $pdf->line(24,$y_rect,24,$y_rect+($y - $y_rect-8));
                $pdf->line(82,$y_rect,82,$y_rect+($y - $y_rect-8));
                $pdf->line(102,$y_rect,102,$y_rect+($y - $y_rect-8));
                $pdf->line(106,$y_rect,106,$y_rect+($y - $y_rect-8));
                $pdf->line(110,$y_rect,110,$y_rect+($y - $y_rect-8));
                $pdf->line(121,$y_rect,121,$y_rect+($y - $y_rect-8));
                $pdf->line(132,$y_rect,132,$y_rect+($y - $y_rect));
                $pdf->line(149,$y_rect,149,$y_rect+($y - $y_rect));
                $pdf->line(166,$y_rect,166,$y_rect+($y - $y_rect));
                $pdf->line(183,$y_rect,183,$y_rect+($y - $y_rect));
                $pdf->line(200,$y_rect,200,$y_rect+($y - $y_rect));
                $pdf->line(217,$y_rect,217,$y_rect+($y - $y_rect));
                $pdf->line(234,$y_rect,234,$y_rect+($y - $y_rect));
                $pdf->line(251,$y_rect,251,$y_rect+($y - $y_rect));
                $pdf->line(268,$y_rect,268,$y_rect+($y - $y_rect));

    $pdf->SetY($y_rect);
    $pdf->SetX(5);
   $pdf->Cell($pdf->GetPageWidth()-9.5,$y - $y_rect, "",1,0,'');
}
$y = $y_rect+($y - $y_rect)+2;

$pdf->SetFont('Arial','',9.5);
   $y_rect = $y;
   $pdf->SetY($y);
   $pdf->SetLeftMargin(5);
   $pdf->SetX(5);
   $pdf->Cell(55,3, utf8_decode("Total des Cotisations (permanents et expatriés) :"),0,0,'L');

   $pdf->SetY($y);
   $pdf->SetLeftMargin(10);
   $pdf->SetX(80);
   $pdf->Cell(25,3, utf8_decode(apres_virgule($total_total_horizotal, 0)),0,0,'R'); 

   $y = $pdf->GetY();
   $pdf->SetY($y+5);
   $pdf->SetX(5);
   $pdf->Cell(55,3, utf8_decode("Total des Cotisations (occasionnels) :"),0,0,'L');

   $pdf->SetX(80);
   $pdf->Cell(25,3, utf8_decode("0"),0,0,'R');

   $y = $pdf->GetY();
   $pdf->SetY($y+5);
   $pdf->SetX(5);
   $pdf->Cell(55,3, utf8_decode("Débit ou crédit antérieur :"),0,0,'L');

   $pdf->SetX(80);
   $pdf->Cell(25,3, utf8_decode("0"),0,0,'R');

   $y = $pdf->GetY();
   $pdf->SetY($y+5);
   $pdf->SetX(5);
   $pdf->Cell(55,3, utf8_decode("Montant versement :"),0,0,'L');

   $pdf->SetX(80);
   $pdf->Cell(25,3, utf8_decode(apres_virgule($total_total_horizotal, 0)),0,0,'R');

   $sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."inps WHERE mois='".$mois."' AND annee='".$annee."'";
   $resultat = $db->query($sql);
   if($resultat){
    $num = $db->num_rows($resultat);
    if($num>0){
      $inps_obj = $db->fetch_object($resultat);
      $sql = "UPDATE ".MAIN_DB_PREFIX."inps SET montant='".$total_total_horizotal."', date_insert=now() WHERE rowid=".$inps_obj->rowid;
      $resultat = $db->query($sql);
    }else{
      $sql = "INSERT INTO ".MAIN_DB_PREFIX."inps (fk_societe, mois, annee, montant, date_insert) VALUES(".$id_societe.",'".$mois."','".$annee."','".$total_total_horizotal."', now())";
      $resultat = $db->query($sql);
    }
   }else{
    $sql = "INSERT INTO ".MAIN_DB_PREFIX."inps (fk_societe, mois, annee, montant, date_insert) VALUES(".$id_societe.",'".$mois."','".$annee."','".$total_total_horizotal."', now())";
    $resultat = $db->query($sql);
   }
//------------------------------------------------------------------------------------
$pdf->SetFont('Arial','',9);
   $pdf->SetY($y_permant);
   //$pdf->SetX(71);
   //$pdf->MultiCell(13,3, utf8_decode(apres_virgule($total_brut, 0)),0,'L');

   $pdf->SetLineWidth(0.3);

   //8ème rectagle petit tableau
   $pdf->SetY($y_rect);
    $pdf->SetX(128);
   $pdf->Cell(60,15, "",1,0,'');
   $y = $y_rect;
   $pdf->SetY($y+1);
   $pdf->SetX(128);
   $pdf->Cell(30,3, utf8_decode("Perm. + Expat"),0,0,'L');

   $pdf->SetX(158);
   $pdf->Cell(30,3, utf8_decode("Occasionnels"),0,0,'L');

   $pdf->line(158,$y,158,$y+15);

   $pdf->line(108,$y+5,108,$y+15);
   
   $pdf->line(108,$y+5,188,$y+5);

   $pdf->line(108,$y+10,188,$y+10);

   $pdf->line(108,$y+15,138,$y+15);

   //-------------
   $pdf->SetY($y+6);
    $pdf->SetX(118);
    $pdf->Cell(20,3, utf8_decode("Eff."),0,0,'L');

    $pdf->SetX(128);
    $pdf->Cell(30,3, utf8_decode($nb),0,0,'C');

    $pdf->SetX(158);
    $pdf->Cell(30,3, utf8_decode(0),0,0,'C');

    //------------
    $pdf->SetY($y+11);
    $pdf->SetX(118);
    $pdf->Cell(10,3, utf8_decode("Brut."),0,0,'L');

    $pdf->SetX(128);
    $pdf->Cell(30,3, utf8_decode(apres_virgule($total_brut, 0)),0,0,'R');

    $pdf->SetX(158);
    $pdf->Cell(30,3, utf8_decode(0),0,0,'R');

    $pdf->SetFont('Arial','B',9);
    //Affichage montant d'INPS et d'AMO
    //INPS
    $pdf->SetFillColor(190, 190, 190);
    $pdf->SetY($y-1);
    $pdf->SetX(198);
    $pdf->Cell(45,5, utf8_decode("INPS :    ".apres_virgule($total_total_horizotal-$total_amo_sal-$total_amo_sal, 0)),0,0,'L', true);

    //AMO
    $pdf->SetY($y-1);
    $pdf->SetX(245);
    $pdf->MultiCell(45,5, utf8_decode("AMO :   ".apres_virgule($total_amo_sal+$total_amo_sal, 0)),0,'L', true);

    //Exactitude de la déclaration
    $pdf->SetFont('Arial','',10);
    $pdf->SetY($y+9);
    $pdf->SetX(198);
    $pdf->MultiCell(95,4, utf8_decode("Je soussigne, certifie exacte la présente déclaration "),0,'L', false);


    //NB
    $pdf->SetFont('Arial','B',9);
    $y = $pdf->GetY() + 12;
    $y_rect = $y;
    $pdf->SetY($y);
    $pdf->SetLeftMargin(5);
    $pdf->SetX(5);
    $pdf->Cell(10,3, utf8_decode("N.B :"),0,0,'L');

    $pdf->SetFont('Arial','B',7);
    $pdf->SetX(15);
    $pdf->MultiCell(120,3, utf8_decode("- Exceptés les frais professionnels, toute rémunération (espèces, avantage en nature) portée dans la comptabilité sous quelque rubrique que ce soit doit être soumise à cotisations."),0,'L');
    
    $pdf->SetY($y+6);
    $pdf->SetLeftMargin(5);
    $pdf->SetX(15);
    $pdf->MultiCell(120,3, utf8_decode("- Le non paiement des cotisations à l'échéance ne dispense pas de l'employeur de la déclarations des cotisations dues. Art. 191 du CPS, art 107 du décret 09-552 P-RM du 12 Octobre 2009."),0,'L');


    $pdf->SetFont('Arial','',10);
    $pdf->SetY($y_rect-3);
    $pdf->SetLeftMargin(5);
    $pdf->SetX(198);
    $d = date("d/m/Y");
    $pdf->MultiCell(50,3, utf8_decode("Fait à Bamako le ".$d),0,'L');

    $pdf->SetY($y_rect+2);
    $pdf->SetLeftMargin(5);
    $pdf->SetX(220);
    $pdf->MultiCell(60,3, utf8_decode("Signature et Cachet de l'employeur"),0,'L');

//la deuxième page----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
    $partie = "partie2";
    
    $pdf->AddPage();
    $pdf->SetLineWidth(0.5);
    //1er rectangle-----------------------------------------
    $pdf->SetY($pdf->GetY()+3);
    $pdf->SetLeftMargin(5);
   $pdf->SetX(8);
   $pdf->Cell(125,30, "",1,0,'');
   $y_rect = $pdf->GetY();
   //nom
   $pdf->SetFont('Arial','',10);
   $pdf->SetY($pdf->GetY()+2);
   $pdf->SetX(8);
   $pdf->Cell(15,3, "Nom ",0,0,'L');
   $pdf->SetX(30);
   $pdf->MultiCell(90,3, utf8_decode($sc->nom),0,'L');
   //adresse
   $pdf->SetY($pdf->GetY()+1);
   $pdf->SetX(8);
   $pdf->Cell(15,3, "Adresse ",0,0,'L');
   $pdf->SetX(30);
   $pdf->MultiCell(90,3, utf8_decode($sc->address?:""),0,'L');

   $pdf->SetY($pdf->GetY()+9);
   $pdf->SetX(8);
   $pdf->Cell(15,3, "Tel ",0,0,'L');
   $pdf->SetX(30);
   $pdf->MultiCell(90,3, utf8_decode($sc->phone."".($sc->phone? ($sc->fax? $sc->fax."/" : "") : "")),0,'L');

   $pdf->SetY($pdf->GetY()+1);
   $pdf->SetX(8);
   $pdf->Cell(15,3, "Email ",0,0,'L');
   $pdf->SetX(30);
   $pdf->MultiCell(90,3, utf8_decode($sc->email),0,'L');

   //2ème rectangle -------------------------------------------------------
   $x = 135;
   $pdf->SetY($y_rect);
   $pdf->SetX($x+5);
   $pdf->Cell(60,17, "",1,0,'');
   //Numéro employeur-----------------------------------------
   $y = $y_rect;
   $pdf->SetY($y+1);

   $pdf->SetX($x+5);
   $pdf->SetFont('Arial','',10);
   $pdf->MultiCell(52,3, utf8_decode("N° Employeur : ".$sc->numero_inps),0,'L');

   $pdf->SetY($pdf->GetY()+3);
   $pdf->SetX($x+5);
   $pdf->SetFont('Arial','B',10);
   $pdf->MultiCell(52,3, utf8_decode("COMMUNE I"),0,'C');

//3ème Rectangle-------------------------------------------------------------------------------
  $pdf->SetY($y_rect);
  $y = $y_rect;
  $x = 198 + 8;
   $pdf->SetX($x);
   $pdf->Cell(60,17, "",1,0,'');
   
   $pdf->SetFont('Arial','',10);
   $pdf->SetY($y+1);
   $pdf->SetX($x);
   $pdf->MultiCell(60,3, utf8_decode("Mode de Paiement"),0,'C');

   $pdf->SetFont('Arial','',8);
   $pdf->SetY($y+6);
   $pdf->SetX($x+2);
   $pdf->Cell(6,4, "",1,0,'');
   $pdf->SetX($x+10);
   $pdf->Cell(10,4, utf8_decode("Chèque"),0,0,'L');

   //cochés la case chèque, virement, caisse, autres
   $type_paiement = 'autre';
  if($type_paiement == 'virement'){
    $x_cocher_valider = $x+3.5;
    $y_cocher_valider = $y+12;
  }elseif($type_paiement == 'caisse'){
    $x_cocher_valider = $x+32.5;
    $y_cocher_valider = $pdf->GetY();
  
  }elseif($type_paiement == 'autre'){
    $x_cocher_valider = $x+32.5;
    $y_cocher_valider = $y+12;
  }else{
    $x_cocher_valider = $x+3.5;
    $y_cocher_valider = $y+6;
  }

  //$y_cocher_valider = $x+3.5;
    //$x_cocher_valider = $y+10;

   $pdf->SetFont('Arial','',8);
   $pdf->SetY($y_cocher_valider);
   $pdf->SetX($x_cocher_valider);
   $pdf->Cell(3,5, "X",0,0,'C');

   $pdf->SetY($y + 6);
   $pdf->SetX($x+31);
   $pdf->Cell(6,4, "",1,0,'');
   $pdf->SetX($x+39);
   $pdf->Cell(10,4, utf8_decode("Caisses"),0,0,'L');


   $pdf->SetY($y+12);
   $pdf->SetX($x+2);
   $pdf->Cell(6,4, "",1,0,'');
   $pdf->SetX($x+10);
   $pdf->Cell(10,4, utf8_decode("Virement"),0,0,'L');

   $pdf->SetX($x+31);
   $pdf->Cell(6,4, "",1,0,'');
   $pdf->SetX($x+39);
   $pdf->Cell(10,4, utf8_decode("Autres"),0,0,'L');

    $y = $pdf->GetY();
    $y+= 22;
    $pdf->SetY($y);

    $pdf->SetFont('Arial','B',10);
    $titre = "1. Cotisation au titre du régime du code de prévoyance sociale";
    $pdf->SetLeftMargin(5);
    $pdf->MultiCell(120,3, utf8_decode($titre),0,'L',0);

    $pdf->SetFont('Arial','',10);
    $y = $pdf->GetY()+2;
    $pdf->SetY($y+2);
    $pdf->SetX(17);
    $pdf->Cell(100,3, utf8_decode("- Taux des cotisations :       20% (compris entre 19% et 22%"),0,0,'L',0);

    $pdf->SetX(110);
    $pdf->MultiCell(70,3, utf8_decode("- Période déclarée : "),0,'L',0);

    $y = $pdf->GetY()+2;
    $pdf->SetY($y+2);
    $pdf->SetX(17);
    $pdf->Cell(70,3, utf8_decode("- Effectif Salariés                   ".$nb),0,0,'L',0);

    $pdf->SetX(110);
    $pdf->MultiCell(70,3, utf8_decode("- Date limite de déclaration : "),0,'L',0);

    $y = $pdf->GetY()+2;
    $pdf->SetY($y+2);
    $pdf->SetX(17);
    $pdf->Cell(70,3, utf8_decode("- Effectifs de salariés occasionnels :   0"),0,0,'L',0);

    $pdf->SetX(110);
    $pdf->MultiCell(70,3, utf8_decode("- Date d'échéance de paiement "),0,'L',0);

    $y = $pdf->GetY();
    $y+= 4;
    $y_rect = $y;

    //Tableau-----------------------------------------------------------
    $pdf->SetFont('Arial','',9);

   $pdf->SetY($y+2);
   $pdf->SetX(5);
   $pdf->MultiCell(40,4, utf8_decode("Libellé des cotisations sociales"),0,'C');
   
   $pdf->SetY($y+2);
   $pdf->SetX(45);
   $pdf->MultiCell(20,4, utf8_decode("Effectif"),0,'C');

   $pdf->SetY($y+2);
   $pdf->SetX(65);
   $pdf->MultiCell(25,4, utf8_decode("Masse salariale"),0,'C');


   $pdf->SetY($y+2);
   $pdf->SetX(90);
   $pdf->MultiCell(25,4, utf8_decode("Rétraite 7%"),0,'C');

   $pdf->SetY($y+2);
   $pdf->SetX(115);
   $pdf->MultiCell(25,3, utf8_decode("Ivalidité Allocations Survivant 2%"),0,'C');

   $pdf->SetY($y+2);
   $pdf->SetX(140);
   $pdf->MultiCell(25,3, utf8_decode("Prestations Familiales 8%"),0,'C');

   $pdf->SetY($y+2);
   $pdf->SetX(165);
   $pdf->MultiCell(25,3, utf8_decode("Accident du travail 2%"),0,'C');

   $pdf->SetY($y+2);
   $pdf->SetX(190);
   $pdf->MultiCell(25,3, utf8_decode("ANPE 1%"),0,'C');

   $pdf->SetY($y+2);
   $pdf->SetX(215);
   $pdf->MultiCell(50,3, utf8_decode("Montant des Cotisations"),0,'C');

   $pdf->SetLineWidth(0.5);
   $pdf->SetFont('Arial','',8);

   $pdf->line(5,$pdf->GetY()+6,$pdf->GetPageWidth()-5,$pdf->GetY()+6);
   //line 1---------
   $y = $pdf->GetY() + 7;
   $pdf->SetY($y);
   $pdf->SetX(5);
   $pdf->MultiCell(40,4, utf8_decode("Taux de cotisations"),0,'C');
   
   $pdf->SetY($y);
   $pdf->SetX(45);
   $pdf->MultiCell(20,4, utf8_decode(""),0,'R');

   $pdf->SetY($y);
   $pdf->SetX(65);
   $pdf->MultiCell(25,4, utf8_decode(""),0,'R');


   $pdf->SetY($y);
   $pdf->SetX(90);
   $pdf->MultiCell(25,4, utf8_decode("7%"),0,'R');

   $pdf->SetY($y);
   $pdf->SetX(115);
   $pdf->MultiCell(25,3, utf8_decode("2%"),0,'R');

   $pdf->SetY($y);
   $pdf->SetX(140);
   $pdf->MultiCell(25,3, utf8_decode("8%"),0,'R');

   $pdf->SetY($y);
   $pdf->SetX(165);
   $pdf->MultiCell(25,3, utf8_decode("2%"),0,'R');

   $pdf->SetY($y);
   $pdf->SetX(190);
   $pdf->MultiCell(20,3, utf8_decode("1%"),0,'R');

   $pdf->SetY($y);
   $pdf->SetX(215);
   $pdf->MultiCell(70,3, utf8_decode("20%"),0,'R');
   $pdf->line(5,$pdf->GetY()+2,$pdf->GetPageWidth()-5,$pdf->GetY()+2);

   //line 2-------------
   $y = $pdf->GetY() + 3;
   $pdf->SetY($y);
   $pdf->SetX(5);
   $pdf->MultiCell(40,4, utf8_decode("Salariés permanents"),0,'C');
   
   $pdf->SetY($y);
   $pdf->SetX(45);
   $pdf->MultiCell(20,4, utf8_decode($nb),0,'R');

   $pdf->SetY($y);
   $pdf->SetX(65);
   $pdf->MultiCell(25,4, utf8_decode(apres_virgule($total_brut, 0)),0,'R');


   $pdf->SetY($y);
   $pdf->SetX(90);
   $pdf->MultiCell(25,4, utf8_decode(apres_virgule($total_retraite, 0)),0,'R');

   $pdf->SetY($y);
   $pdf->SetX(115);
   $pdf->MultiCell(25,3, utf8_decode(apres_virgule($total_alloc, 0)),0,'R');

   $pdf->SetY($y);
   $pdf->SetX(140);
   $pdf->MultiCell(25,3, utf8_decode(apres_virgule($total_prst, 0)),0,'R');

   $pdf->SetY($y);
   $pdf->SetX(165);
   $pdf->MultiCell(25,3, utf8_decode(apres_virgule($total_acc, 0)),0,'R');

   $pdf->SetY($y);
   $pdf->SetX(190);
   $pdf->MultiCell(25,3, utf8_decode(apres_virgule($total_anpe, 0)),0,'R');

   $pdf->SetY($y);
   $pdf->SetX(215);
   $pdf->MultiCell(70,3, utf8_decode(apres_virgule($total_total_horizotal-$total_amo_sal-$total_amo_sal, 0)),0,'R');
   $pdf->line(5,$pdf->GetY()+2,$pdf->GetPageWidth()-5,$pdf->GetY()+2);

   //line 3-------------
   $y = $pdf->GetY() + 3;
   $pdf->SetY($y);
   $pdf->SetX(5);
   $pdf->MultiCell(40,4, utf8_decode("Salariés occasionnels"),0,'C');
   
   $pdf->SetY($y);
   $pdf->SetX(45);
   $pdf->MultiCell(20,4, utf8_decode(0),0,'R');

   $pdf->SetY($y);
   $pdf->SetX(65);
   $pdf->MultiCell(25,4, utf8_decode(0),0,'R');


   $pdf->SetY($y);
   $pdf->SetX(90);
   $pdf->MultiCell(25,4, utf8_decode(0),0,'R');

   $pdf->SetY($y);
   $pdf->SetX(115);
   $pdf->MultiCell(25,3, utf8_decode(0),0,'R');

   $pdf->SetY($y);
   $pdf->SetX(140);
   $pdf->MultiCell(25,3, utf8_decode(0),0,'R');

   $pdf->SetY($y);
   $pdf->SetX(165);
   $pdf->MultiCell(25,3, utf8_decode(0),0,'R');

   $pdf->SetY($y);
   $pdf->SetX(190);
   $pdf->MultiCell(25,3, utf8_decode(0),0,'R');

   $pdf->SetY($y);
   $pdf->SetX(215);
   $pdf->MultiCell(70,3, utf8_decode(0),0,'R');
   $pdf->line(5,$pdf->GetY()+2,$pdf->GetPageWidth()-5,$pdf->GetY()+2);

   //line 4-------------
   $y = $pdf->GetY() + 3;
   $pdf->SetY($y);
   $pdf->SetX(5);
   $pdf->MultiCell(40,4, utf8_decode("Total des cotisations(1)"),0,'C');
   
   $pdf->SetY($y);
   $pdf->SetX(45);
   $pdf->MultiCell(20,4, utf8_decode($nb),0,'R');

   $pdf->SetY($y);
   $pdf->SetX(65);
   $pdf->MultiCell(25,4, utf8_decode(apres_virgule($total_brut, 0)),0,'R');


   $pdf->SetY($y);
   $pdf->SetX(90);
   $pdf->MultiCell(25,4, utf8_decode(apres_virgule($total_retraite, 0)),0,'R');

   $pdf->SetY($y);
   $pdf->SetX(115);
   $pdf->MultiCell(25,3, utf8_decode(apres_virgule($total_alloc, 0)),0,'R');

   $pdf->SetY($y);
   $pdf->SetX(140);
   $pdf->MultiCell(25,3, utf8_decode(apres_virgule($total_prst, 0)),0,'R');

   $pdf->SetY($y);
   $pdf->SetX(165);
   $pdf->MultiCell(25,3, utf8_decode(apres_virgule($total_acc, 0)),0,'R');

   $pdf->SetY($y);
   $pdf->SetX(190);
   $pdf->MultiCell(25,3, utf8_decode(apres_virgule($total_anpe, 0)),0,'R');

   $pdf->SetY($y);
   $pdf->SetX(215);
   $pdf->MultiCell(70,3, utf8_decode(apres_virgule($total_total_horizotal-$total_amo_sal-$total_amo_sal, 0)),0,'R');

   $y = $pdf->GetY()+2;
   $pdf->line(45,$y_rect,45,$y_rect+($y - $y_rect));
    $pdf->line(65,$y_rect,65,$y_rect+($y - $y_rect));
    $pdf->line(90,$y_rect,90,$y_rect+($y - $y_rect));
    $pdf->line(115,$y_rect,115,$y_rect+($y - $y_rect));
    $pdf->line(140,$y_rect,140,$y_rect+($y - $y_rect));
    $pdf->line(165,$y_rect,165,$y_rect+($y - $y_rect));
    $pdf->line(190,$y_rect,190,$y_rect+($y - $y_rect));
    $pdf->line(215,$y_rect,215,$y_rect+($y - $y_rect));

    $pdf->SetY($y_rect);
    $pdf->SetX(5);
    $pdf->Cell($pdf->GetPageWidth()-10,$y - $y_rect, "",1,0,'');

    //Note en dessous du tableau
    $pdf->SetFont('Arial','',8);
    $y += 3;
    $pdf->SetY($y);
    $pdf->SetX(5);
    $pdf->Cell(110,4, utf8_decode("Total des cotisations dues au titre des régimes du code de prévoyance sociale :          ".apres_virgule($total_total_horizotal-$total_amo_sal-$total_amo_sal, 0)),0,0,'L');
    $pdf->SetX(120);
    $pdf->MultiCell(20,4, utf8_decode(""),0,'L');

    $y = $pdf->GetY() + 2;
    $pdf->SetY($y);
    $pdf->SetX(5);
    $pdf->Cell(110,4, utf8_decode("Montant du versement au titre des régimes du code de prévoyance sociale :          ".apres_virgule($total_total_horizotal-$total_amo_sal-$total_amo_sal, 0)),0,0,'L');
    $pdf->SetX(120);
    $pdf->MultiCell(20,4, utf8_decode(""),0,'L');

    //NB
    $y = $pdf->GetY() + 3;
    $pdf->SetFont('Arial','',6);
    $pdf->Cell(100,4, utf8_decode("NB : le non paiement des cotisation à l'échéance ne dispense pas l'employeur de la déclaration des cotisations dues. Art 190 du CPS."),0,0,'L');
    $pdf->SetX(120);




//la Troisième page----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
   $partie = "partie3";
   $pdf->AddPage();

   //Tableau ----------------------------------------------------------------
   $pdf->SetFont('Arial','B',9);

   $y = $pdf->GetY()+6;
   $y_rect = $y-4;
   $pdf->SetY($y);
   $pdf->SetX(10);
   $pdf->MultiCell(65,4, utf8_decode("Libellé des cotisations sociales"),0,'L');
   
   $pdf->SetY($y);
   $pdf->SetX(75);
   $pdf->MultiCell(22,4, utf8_decode("Effectif"),0,'C');

   $pdf->SetY($y);
   $pdf->SetX(97);
   $pdf->MultiCell(40,4, utf8_decode("Masse salariale"),0,'C');


   $pdf->SetY($y);
   $pdf->SetX(137);
   $pdf->MultiCell(40,4, utf8_decode("AMO employe 3.06%"),0,'C');

   $pdf->SetY($y);
   $pdf->SetX(177);
   $pdf->MultiCell(40,4, utf8_decode("AMO employeur 3.5%"),0,'C');

   $pdf->SetY($y);
   $pdf->SetX(217);
   $pdf->MultiCell(70,4, utf8_decode("Montant des cotisations"),0,'C');

   $pdf->SetLineWidth(0.5);
   $pdf->SetFont('Arial','',7);

   $pdf->line(10,$pdf->GetY()+6,$pdf->GetPageWidth()-10,$pdf->GetY()+6);

   //line 1
   $pdf->SetFont('Arial','',9);
   $y = $pdf->GetY()+8;
   $pdf->SetY($y);
   $pdf->SetX(10);
   $pdf->MultiCell(65,4, utf8_decode("Taux de cotisations"),0,'L');
   
   $pdf->SetY($y);
   $pdf->SetX(75);
   $pdf->MultiCell(22,4, utf8_decode(""),0,'R');

   $pdf->SetY($y);
   $pdf->SetX(97);
   $pdf->MultiCell(40,4, utf8_decode(""),0,'R');


   $pdf->SetY($y);
   $pdf->SetX(137);
   $pdf->MultiCell(40,4, utf8_decode("3.06%"),0,'R');

   $pdf->SetY($y);
   $pdf->SetX(177);
   $pdf->MultiCell(40,3, utf8_decode("3.5%"),0,'R');

   $pdf->SetY($y);
   $pdf->SetX(217);
   $pdf->MultiCell(70,3, utf8_decode(""),0,'R');
   $pdf->line(10,$pdf->GetY()+3,$pdf->GetPageWidth()-10,$pdf->GetY()+3);

   //line 2
   $total_amo = 0;
   $y = $pdf->GetY()+5;
   $pdf->SetY($y);
   $pdf->SetX(10);
   $pdf->MultiCell(65,4, utf8_decode("Salariés adhérents"),0,'L');
   
   $pdf->SetY($y);
   $pdf->SetX(75);
   $pdf->MultiCell(22,4, utf8_decode($nb),0,'R');

   $pdf->SetY($y);
   $pdf->SetX(97);
   $pdf->MultiCell(40,4, utf8_decode(apres_virgule($total_brut, 0)),0,'R');


   $pdf->SetY($y);
   $pdf->SetX(137);
   $pdf->MultiCell(40,4, utf8_decode(apres_virgule($total_brut*3.06/100, 0)),0,'R');
   $total_amo += $total_brut*3.06/100;

   $pdf->SetY($y);
   $pdf->SetX(177);
   $pdf->MultiCell(177,3, utf8_decode(apres_virgule($total_brut*3.5/100, 0)),0,'R');
   $total_amo += $total_brut*3.5/100;

   $pdf->SetY($y);
   $pdf->SetX(217);
   $pdf->MultiCell(70,3, utf8_decode(apres_virgule($total_amo, 0)),0,'R');
   $pdf->line(10,$pdf->GetY()+3,$pdf->GetPageWidth()-10,$pdf->GetY()+3);

   //line 3
   $y = $pdf->GetY()+5;
   $pdf->SetY($y);
   $pdf->SetX(10);
   $pdf->MultiCell(65,4, utf8_decode("Salariés non adhérents"),0,'L');
   
   $pdf->SetY($y);
   $pdf->SetX(75);
   $pdf->MultiCell(22,4, utf8_decode(0),0,'R');

   $pdf->SetY($y);
   $pdf->SetX(97);
   $pdf->MultiCell(40,4, utf8_decode(0),0,'R');


   $pdf->SetY($y);
   $pdf->SetX(137);
   $pdf->MultiCell(40,4, utf8_decode(0),0,'R');

   $pdf->SetY($y);
   $pdf->SetX(177);
   $pdf->MultiCell(40,3, utf8_decode(0),0,'R');

   $pdf->SetY($y);
   $pdf->SetX(217);
   $pdf->MultiCell(70,3, utf8_decode(0),0,'R');
   $pdf->line(10,$pdf->GetY()+3,$pdf->GetPageWidth()-10,$pdf->GetY()+3);
   
   //line 4
   $y = $pdf->GetY()+5;
   $pdf->SetY($y);
   $pdf->SetX(10);
   $pdf->MultiCell(65,4, utf8_decode("Total des cotisations(2)"),0,'L');
   
   $pdf->SetY($y);
   $pdf->SetX(75);
   $pdf->MultiCell(22,4, utf8_decode($nb),0,'R');

   $pdf->SetY($y);
   $pdf->SetX(97);
   $pdf->MultiCell(40,4, utf8_decode(apres_virgule($total_brut, 0)),0,'R');


   $pdf->SetY($y);
   $pdf->SetX(137);
   $pdf->MultiCell(40,4, utf8_decode(apres_virgule($total_brut*3.06/100, 0)),0,'R');

   $pdf->SetY($y);
   $pdf->SetX(177);
   $pdf->MultiCell(40,3, utf8_decode(apres_virgule($total_brut*3.5/100, 0)),0,'R');

   $pdf->SetY($y);
   $pdf->SetX(217);
   $pdf->MultiCell(70,3, utf8_decode(apres_virgule($total_amo, 0)),0,'R');


   $y = $pdf->GetY()+2;
   $pdf->line(75,$y_rect + 2,75,$y_rect + 1 +($y - $y_rect));
    $pdf->line(97,$y_rect + 2,97,$y_rect + 1 +($y - $y_rect));
    $pdf->line(137,$y_rect + 2,137,$y_rect + 1 +($y - $y_rect));
    $pdf->line(177,$y_rect + 2,177,$y_rect + 1 +($y - $y_rect));
    $pdf->line(217,$y_rect +2,217,$y_rect +($y - $y_rect));
    

    $pdf->SetY($y_rect+2);
    $pdf->SetX(10);
    $pdf->Cell($pdf->GetPageWidth()-20,$y - $y_rect-1, "",1,0,'');

    //Note en dessous du tableau
    $y += 6;
    $pdf->SetY($y);
    $pdf->SetX(10);
    $pdf->Cell(120,4, utf8_decode("Total des cotisations dues au titre du régime de l'Assurance Maladie Obligatoire :"),0,0,'L');
    $pdf->SetX(170);
    $pdf->MultiCell(20,4, utf8_decode(apres_virgule($total_amo, 0)),0,'L');

    $y = $pdf->GetY() + 4;
    $pdf->SetY($y);
    $pdf->SetX(10);
    $pdf->Cell(120,4, utf8_decode("Montant du versement du titre du régime de l'Assurance Maladie Obligatoire :"),0,0,'L');
    $pdf->SetX(170);
    $pdf->MultiCell(20,4, utf8_decode(apres_virgule($total_amo, 0)),0,'L');

    $y = $pdf->GetY() + 5;
    $y_mode = $y;
    $pdf->SetY($y);
    $pdf->SetFont('Arial','B',9);
    $pdf->SetLeftMargin(10);
    $pdf->MultiCell(100,3, utf8_decode("3. Paiements :"),0,'L',0);

    $y = $pdf->GetY() + 8;
    $y_coche = $y;
    $pdf->SetY($y);
    $pdf->SetLeftMargin(10);
    $pdf->Cell(160,7, utf8_decode("Total cotisation dues (1) + (2) :       ".apres_virgule($total_total_horizotal, 0)),1,0,'L',0);

    $y = $pdf->GetY() + 13;
    $pdf->SetY($y);
    $pdf->SetLeftMargin(10);
    $pdf->Cell(160,7, utf8_decode("Montant total versé (1) + (2) :       ".apres_virgule($total_total_horizotal, 0)),1,0,'L',0);

    //cochés la case chèque
    $pdf->SetY($y_mode+4);
    $pdf->SetFont('Arial','B',9);
    $pdf->SetLeftMargin(10);
    $pdf->SetX(205);
    $pdf->MultiCell(50,3, utf8_decode("4. Mode de Paiement :"),0,'L',0);

    $pdf->SetFont('Arial','',9);
    $x_coche = 205;
    $pdf->SetY($y_coche);
   $pdf->SetX($x_coche);
   $pdf->Cell(61,18, "",1,0,'');
   
   $pdf->SetY($y_coche+1);
   $pdf->SetX($x_coche);
   $pdf->MultiCell(52,3, utf8_decode("Mode de Paiement"),0,'C');

   $y_coche += 6;
   $pdf->SetY($y_coche);
   $pdf->SetX($x_coche+2);
   $pdf->Cell(6,4, "",1,0,'');
   
   $pdf->SetX($x_coche+9);
   $pdf->Cell(10,4, utf8_decode("Chèque"),0,0,'L');
   $pdf->SetFont('Arial','B',8);
   $pdf->SetY($y_coche+1);
   $pdf->SetX($x_coche+3);
   $pdf->Cell(2,2, "X",0,0,'');

   $pdf->SetFont('Arial','',9);
   $pdf->SetY($y_coche);
   $pdf->SetX($x_coche+32);
   $pdf->Cell(6,4, "",1,0,'');
   $pdf->SetX($x_coche+39);
   $pdf->Cell(10,4, utf8_decode("Caisses"),0,0,'L');


   $pdf->SetY($y_coche+6);
   $pdf->SetX($x_coche+2);
   $pdf->Cell(6,4, "",1,0,'');
   $pdf->SetX($x_coche+9);
   $pdf->Cell(10,4, utf8_decode("Virement"),0,0,'L');

   $pdf->SetX($x_coche+32);
   $pdf->Cell(6,4, "",1,0,'');
   $pdf->SetX($x_coche+39);
   $pdf->Cell(10,4, utf8_decode("Autres"),0,0,'L');


    //NB

    $pdf->SetFont('Arial','',7);
    $pdf->SetCharSpacing(0.3);
    $y = $pdf->GetY() + 12;
    $y_rect = $y;
    $pdf->SetY($y);
    $pdf->SetLeftMargin(5);
    $pdf->SetX(10);
    $pdf->Cell(10,3, utf8_decode("N.B :"),0,0,'L');

    $pdf->SetX(20);
    $pdf->MultiCell(277,3, utf8_decode("- Le non paiement des cotisations à l'échéance ne dispense pas l'employeur de la déclarations des cotisations dues, décret 09-552 P-RM du 12 octobre 2009."),0,'L');
    
    $pdf->SetY($y+6);
    $pdf->SetLeftMargin(5);
    $pdf->SetX(20);
    $pdf->MultiCell(277,6, utf8_decode("- Le relevé nominatif des rémunérations soumises à cotisation est fourni par le employeurs selon la même périodicité et les mêmes modalités que le versement \n   des cotisations. Art 112 du N°09-552 P-RM du 12 octobre 2009."),0,'L');

    $pdf->SetCharSpacing(0);

    $y = $pdf->GetY() + 8;
    $y_rect = $y;
    $pdf->SetY($y);
   $pdf->SetX(13);
   $pdf->MultiCell(130,21, utf8_decode(""),1,'R');

   $pdf->SetFont('Arial','B',9);
   $y = $pdf->GetY() -20;
   $pdf->SetY($y);
    $pdf->SetLeftMargin(5);
    $pdf->SetX(10);
    $pdf->Cell(130,3, utf8_decode("ATTENTION :"),0,0,'C');

    $pdf->SetFont('Arial','B',10);
    $y = $pdf->GetY() + 6;
    $pdf->SetY($y);
   $pdf->SetX(13);
   $pdf->MultiCell(130,5, utf8_decode("Le paiement par virement bancaire ne dispense pas l'employeur \nde la fourniture de la déclaration des cotisations sociales"),0,'L');


   $pdf->SetFont('Arial','',9);
   $pdf->SetY($y_rect);
    $pdf->SetLeftMargin(5);
    $pdf->SetX(200);
    $pdf->Cell(27,3, utf8_decode("Certifié exacte le :"),0,0,'L');

    $pdf->line(227,$y_rect+3,237,$y_rect+3);
    $pdf->SetX(238);
    $pdf->Cell(2,3, utf8_decode("/"),0,0,'C');
    $pdf->line(240.5,$y_rect+3,250.5,$y_rect+3);
    $pdf->SetX(250.5);
    $pdf->Cell(2,3, utf8_decode("/"),0,0,'C');
    $pdf->line(252.5,$y_rect+3,272.5,$y_rect+3);


    $pdf->SetY($y_rect + 8);
    $pdf->SetLeftMargin(10);
    $pdf->SetX(220);
    $pdf->Cell(50,3, utf8_decode("Signature et cachet de l'employeur"),0,0,'L');
    

   $pdf->SetLineWidth(0.5);

   //
  //Enregistrement dans les log || Traçabilité
  $mois_tab = array(" Janvier "," Février "," Mars "," Avril "," Mai "," Juin "," Juillet "," Août "," Septembre "," Octobre "," Novembre "," Décembre ");

$soc_sql = "SELECT nom FROM ".MAIN_DB_PREFIX."societe WHERE rowid=".$id_societe;
      $res_soc = $db->query($soc_sql);
      if($res_soc)
            $nom_soc = $db->fetch_object($res_soc)->nom;

$sql_select = "SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$user->id;
$obj = $db->fetch_object($db->query($sql_select));

//On garde la trace de l'action
$action_effectue = "Fiche I.N.P.S de la société ".$nom_soc." du mois de ".$mois_tab[$mois - 1]." ".$annee." dans société Salaire";
$sql_log = 'INSERT INTO '.MAIN_DB_PREFIX.'log (fk_user, nom, prenom, quand, action_effectue, object_concerne)';
$sql_log .= ' VALUES('.$user->id.', "'.$obj->lastname.'","'.$obj->firstname.'",now(),"'.$action_effectue.'","Exportation")';
$db->query($sql_log);
   
    // affichage à l'écran...
    $titre = "fiche cotisation-".$mois."-".$annee.".pdf";
     $pdf->Output($titre,$mode);
     $db->free();







































    function en_tete($pdf){
      global $sc, $obj_type_cotis, $mois, $mois_tab, $annee, $nb, $partie;

      $pdf->SetY($pdf->GetY()+5);
      
      if($partie == "partie1"){
          $titre = "INSTITUTION NATIONAL DE PREVOYANCE SOCIAL DE LA REPUBLIQUE DE MALI";
          $pdf->SetLeftMargin(63);
          $pdf->MultiCell(160,3, utf8_decode($titre),0,'C',0);

          
          $pdf->SetLeftMargin(13);
          $pdf->Image("./logo_inps.jpg",8,8, 25,28);

          $pdf->SetFont('Arial','',7);
          $y = $pdf->GetY()+2;
          $pdf->SetY($y+4);
          $pdf->SetX(35);
          $pdf->Cell(7,3, "Tel : ",0,0,'L',0);
          $pdf->MultiCell(40,4, "00  (223)  20  21  31  58\n00  (223)  20  21  25  54",0,'L',0);

          $y = $pdf->GetY()+1;
          $pdf->SetY($y);
          $pdf->SetX(35);
          $pdf->Cell(7,3, "Fax : ",0,0,'L',0);
          $pdf->MultiCell(40,3, "00  (223)  20  21  47  31",0,'L',0);
          $pdf->SetY($y+4);
          $pdf->SetX(35);
          $pdf->MultiCell(30,3, "BP 53 - Bamako",0,'L',0);

          $pdf->SetFont('Arial','B',9);
          $pdf->SetLeftMargin(91);
          $y = $pdf->GetY() - 15;
          $pdf->SetY($y);
          $sous_titre  = "ORGANISE GESTIONNAIRE DELEGUE DE LA CAISSE NATIONALE D'ASSURANCE MALADIE OBLIGATOIRE";
          $pdf->MultiCell(105,3, utf8_decode($sous_titre),0,'C',0);

          $pdf->SetFont('Arial','',7);
          $pdf->SetY($y+4);
          $x_tel = 230;
          $pdf->SetX($x_tel);
          $pdf->Cell(7,3, "Tel : ",0,0,'L',0);
          $pdf->MultiCell(30,3, "00 (223) 20 72 91 85",0,'L',0);
          $pdf->SetY($y+10);
          $pdf->SetX($x_tel);
          $pdf->MultiCell(30,3, "BPE 362 - Bamako ",0,'L',0);

          $pdf->Image("./logo_canam.png",260,8, 28,28);
          $pdf->SetFont('Arial','B',10);

          $pdf->SetLeftMargin(81);
          $y = $pdf->GetY()+2;
          $pdf->SetY($y+4);
          $Declaration = "DECLARATION NOMINATIVE DE VERSEMENT DES COTISATIONS";
          $pdf->MultiCell(125,3, utf8_decode($Declaration),0,'C',0);
      }else if($partie == "partie2"){
        //$pdf->SetFont('Arial','',7);
        $titre = "INPS";
        $pdf->SetY(5);
          $pdf->SetLeftMargin(40);
          $pdf->MultiCell(15,4, utf8_decode($titre),0,'L',0);

          
          $pdf->SetLeftMargin(13);
          $pdf->Image("./logo_inps.jpg",8,6, 28,28);

          $pdf->SetFont('Arial','',7);
          $y = $pdf->GetY()+2;
          $pdf->SetY($y);
          $pdf->SetX(40);
          $pdf->Cell(7,3, "Tel : ",0,0,'L',0);
          $pdf->MultiCell(30,3, "00 (223) 20 21 31 58\n00 (223) 20 21 25 54",0,'L',0);
          $pdf->SetX(40);
          $pdf->Cell(7,3, "Fax : ",0,0,'L',0);
          $pdf->MultiCell(30,3, "00 (223) 20 21 47 31",0,'L',0);
          $pdf->SetX(40);
          $pdf->MultiCell(30,3, "BP 53 - Bamako",0,'L',0);

          $y = $pdf->GetY()+5;
          $pdf->SetY($y);
          $pdf->SetX(40);
          $pdf->MultiCell(70,3, "ORGANISME GESTIONNAIRE DELEGUE DE LA CAISSE NATIONALE D'ASSURANCE MALADIE OBLIGATOIRE",0,'L',0);

          $pdf->SetFont('Arial','B',10);
          $titre = "CANAM";
          $pdf->SetY(5);
          $pdf->SetLeftMargin(215);
          $pdf->MultiCell(20,4, utf8_decode($titre),0,'L',0);

          //$pdf->SetAlpha(0.5);
          $pdf->SetFont('Arial','',7);
          $pdf->SetY(9);
          $pdf->SetLeftMargin(215);
          $pdf->MultiCell(25,3, utf8_decode("CAISSE NATIONALE D'ASSURANCE MALADIE OBLIGATOIRE"),0,'L',0);

         // $pdf->SetFont('Arial','B',5);
         //$pdf->SetWordSpacing(3);
         
          $y = $pdf->GetY()+1;
          $pdf->SetY($y);
          $pdf->SetX(215);
          $pdf->Cell(7,3, "Tel : ",0,0,'L',0);
          $pdf->MultiCell(40,3, "00  (223)  20  72  91  85",0,'L',0);
          $y = $pdf->GetY()+1;
          $pdf->SetY($y);
          $pdf->SetX(215);
          $pdf->MultiCell(30,3, "BPE  362 - Bamako ",0,'L',0);


          $pdf->SetFont('Arial','',11);
          $pdf->SetLeftMargin(106);
          $y = 6;
          $pdf->SetY($y+5);
          $sous_titre  = "Ministère de la solidarité, de l'action Humanitaire et de la Reconstruction du Nord";
          $pdf->MultiCell(88,5, utf8_decode($sous_titre),0,'C',0);

          $pdf->SetFont('Arial','B',9);
          $pdf->SetLeftMargin(106);
          $y = $pdf->GetY()+10;
          $pdf->SetY($y+7);
          $Declaration = "DECLARATION DE COTISATIONS SOCIALES";
          $pdf->MultiCell(80,3, $Declaration,0,'C',0);
      }else if($partie == "partie3"){
        $pdf->SetFont('Arial','B',10);
        $titre = "2. Cotisation au titre du régime de l'assurance maladie obligatoire";
          $pdf->SetLeftMargin(8);
          $pdf->MultiCell(120,3, utf8_decode($titre),0,'L',0);

          $pdf->SetFont('Arial','',10);
          $y = $pdf->GetY()+7;
          $pdf->SetY($y);
          $pdf->SetX(10);
          $pdf->MultiCell(100,3, utf8_decode("- Taux des cotisations : 6.56%"),0,'L',0);

          $y = $pdf->GetY()+4;
          $pdf->SetY($y);
          $pdf->SetX(10);
          $pdf->MultiCell(100,3, utf8_decode("- Période déclarée :        Du           au          "),0,'L',0);

          $y = $pdf->GetY()+4;
          $pdf->SetY($y);
          $pdf->SetX(10);
          $pdf->MultiCell(100,3, utf8_decode("- Nombre des adhérents :   ".$nb),0,'L',0);
      }



      //$pdf->line(12,$pdf->GetY()+5,$pdf->GetPageWidth()-12,$pdf->GetY()+5);
    }


    function apres_virgule($valeur, $decalage){
        $val_retour = number_format($valeur, 0, '.', ' ');
      return $val_retour;
      }