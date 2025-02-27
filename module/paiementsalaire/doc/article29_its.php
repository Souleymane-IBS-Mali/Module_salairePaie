<?php


// Appel de la librairie FPDF
require '../../main.inc.php';

require("fpdf/fpdf.php");


// Connexion à la BDD (à personnaliser)
$id_societe = GETPOST('id_societe','int');
$annee_rechercher = GETPOST('annee','int');

$sql = "SELECT nom FROM ".MAIN_DB_PREFIX."societe Where rowid=".$id_societe;
	$result1 = $db->query($sql);
  $sc = $db->fetch_object($result1);

  global $sc;
  
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
      
      $this->SetTitle("Article24 ITS",true);
      // Titre gras (B) police Helbetica de 11
      $this->SetFont('Helvetica','B',11);
      // fond de couleur gris (valeurs en RGB)
      $this->setFillColor(230,230,230);
       // position du coin supérieur gauche par rapport à la marge gauche (mm)
       $this->SetX(70);
       //$this->SetY(20);
      // Texte : 60 >largeur ligne, 8 >hauteur ligne. Premier 0 >pas de bordure, 1 >retour à la ligneensuite, C >centrer texte, 1> couleur de fond ok  
      global $sc;
      $titre = "I.T.S ".$sc->nom;
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
    $pdf = new PDF('L','mm','A4');
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

      $pdf->Cell(25,4,utf8_decode("Matricule"),0,'L');
      $pdf->SetFont('Helvetica','',9);

      $pdf->line(36,15,36,$pdf->GetPageHeight()-15);

      $pdf->SetX(37);
      $pdf->Cell(30,4,utf8_decode("Prenom"),0,'L');
      $pdf->SetFont('Helvetica','',9);

      $pdf->line(68,15,68,$pdf->GetPageHeight()-15);


      $pdf->SetX(69);
      $pdf->Cell(30,4,utf8_decode("Nom"),0,'L');

      $pdf->line(100,15,100,$pdf->GetPageHeight()-15);


      $pdf->SetX(101);
      $pdf->Cell(15,4,utf8_decode("Sexe"),0,'L');

      $pdf->line(117,15,117,$pdf->GetPageHeight()-15);

      $pdf->SetX(118);
      $pdf->Cell(25,4,utf8_decode("Situation F."),0,'L');
      $pdf->line(144,15,144,$pdf->GetPageHeight()-15);

      $pdf->SetX(145);
      $pdf->Cell(20,4,utf8_decode("Enfant/Hand"),0,'L');
      $pdf->line(166,15,166,$pdf->GetPageHeight()-15);

      $pdf->SetX(167);
      $pdf->Cell(25,4,utf8_decode("Sal. Brut"),0,'L');
      $pdf->line(193,15,193,$pdf->GetPageHeight()-15);

      $pdf->SetX(194);
      $pdf->Cell(22,4,utf8_decode("Brut Imp."),0,'L');
      $pdf->line(217,15,217,$pdf->GetPageHeight()-15);

      $pdf->SetX(218);
      $pdf->Cell(20,4,utf8_decode("Somme its"),0,'L');
      $pdf->line(239,15,239,$pdf->GetPageHeight()-15);

      $pdf->SetX(240);
      $pdf->Cell(22,4,utf8_decode("ITS annuel"),0,'L');
      $pdf->line(263,15,263,$pdf->GetPageHeight()-15);

      $pdf->SetX(264);
      $pdf->MultiCell(20,4,utf8_decode("Difference"),0,'L');
      //$pdf->line(285,15,285,$pdf->GetPageHeight()-15);


      $pdf->line(12,$y+4,$pdf->GetPageWidth()-12,$y+4);
     
      $article24_obj = article24_its($db, $id_societe, $annee_rechercher);
      $y=20;
      $pdf->SetY($y);
    if(count($article24_obj) > 0){
      for ($i=0; $i < count($article24_obj); $i++) { 

        $pdf->SetFont('Helvetica','',8);
        $pdf->SetY($y);
        $pdf->SetX(13);
        $pdf->Cell(25,4,utf8_decode($article24_obj[$i]["matricule"]),0,'L');

        $pdf->SetX(37);
        $pdf->Cell(30,4,utf8_decode($article24_obj[$i]["nom"]),0,'L');

      
        $pdf->SetX(69);
        $pdf->Cell(30,4,utf8_decode($article24_obj[$i]["prenom"]),0,'L');

        $pdf->SetX(101);
        $pdf->Cell(10,4,utf8_decode($article24_obj[$i]["sexe"]),0,'L');

        $pdf->SetX(118);
        $pdf->Cell(25,4,utf8_decode($article24_obj[$i]["situation_familiale"]),0,'L');

        $pdf->SetX(145);
        $pdf->Cell(20,4,utf8_decode($article24_obj[$i]["nombre_enfant"]."/".$article24_obj[$i]["nombre_enfant_hand"]), 0, 0,'R');
        $pdf->line(166,15,166,$pdf->GetPageHeight()-15);

        $pdf->SetX(167);
        $pdf->Cell(25,4,utf8_decode(apres_virgule($article24_obj[$i]["somme_brut"], 2)), 0, 0,'R');
        $pdf->line(193,15,193,$pdf->GetPageHeight()-15);

        $pdf->SetX(194);
        $pdf->Cell(22,4,utf8_decode(apres_virgule($article24_obj[$i]["somme_brut_imposable"], 2)), 0, 0,'R');
        $pdf->line(217,15,217,$pdf->GetPageHeight()-15);

        $pdf->SetX(218);
        $pdf->Cell(20,4,utf8_decode(apres_virgule($article24_obj[$i]["somme_its"], 2)), 0, 0,'R');
        $pdf->line(239,15,239,$pdf->GetPageHeight()-15);

        $pdf->SetX(240);
        $pdf->Cell(22,4,utf8_decode(apres_virgule($article24_obj[$i]["its_annuelle"], 2)), 0, 0,'R');
        $pdf->line(263,15,263,$pdf->GetPageHeight()-15);

        $pdf->SetX(264);
        $pdf->MultiCell(20,4,utf8_decode(apres_virgule($article24_obj[$i]["difference"], 2)), 0, 0,'R');
        $pdf->line(12,$y+4,$pdf->GetPageWidth()-12,$y+4);

        $y += 6;
       }
    }else{
        $pdf->SetFont('Helvetica','B',18);
        $pdf->SetTextColor(150,0,0);
        $pdf->SetY($y+80);
        $pdf->SetX(95);
        $pdf->Cell(50,4,utf8_decode("Aucun mois de ".$annee_rechercher." n'est cloturé"),0,'C');
        $pdf->SetTextColor(0);

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

    /*Verification de l'artile 24 de l'its */
function article24_its($db, $id_societe, $annee_rechercher){

  $sql = "SELECT nom FROM ".MAIN_DB_PREFIX."societe Where rowid=".$id_societe;
    $result1 = $db->query($sql);
    $sc = $db->fetch_object($result1);
  
    $somme_brut = 0;
    $somme_brut_imposable = 0;
    $somme_its = 0;
    $obj_array = array();
    $sql_verif_parent = "SELECT DISTINCT fk_salarie FROM ".MAIN_DB_PREFIX."bulletin WHERE annee=".$annee_rechercher." AND fk_societe=".$id_societe." AND cloture='oui'";
    $res_verif_parent = $db->query($sql_verif_parent);
    if($res_verif_parent){	  
      $num = $db->num_rows($res_verif_parent);
      $a = 0;
      while ($a < $num) {
        $somme_brut = 0;
        $somme_brut_imposable = 0;
        $somme_its = 0;
        //bulletin
        $obj_verif_parent = $db->fetch_object($res_verif_parent);
        $sql_verif = "SELECT SUM(salaire_brut) as brut, SUM(salaire_brut_imposable) as brut_imposable FROM ".MAIN_DB_PREFIX."bulletin WHERE fk_salarie=".$obj_verif_parent->fk_salarie;
        $res_verif = $db->query($sql_verif);
        if($res_verif){
          $obj_verif = $db->fetch_object($res_verif);
          $somme_brut = $obj_verif->brut;
          $somme_brut_imposable = $obj_verif->brut_imposable;
        }

       //Bulletin bonus
        $sql_verif = "SELECT SUM(salaire_brut) as brut, SUM(salaire_brut_imposable) as brut_imposable FROM ".MAIN_DB_PREFIX."bulletin_bonus WHERE annee=".$annee_rechercher." AND fk_salarie=".$obj_verif_parent->fk_salarie;
        $res_verif = $db->query($sql_verif);
        if($res_verif){
          $obj_verif = $db->fetch_object($res_verif);
          $somme_brut += $obj_verif->brut;
          $somme_brut_imposable += $obj_verif->brut_imposable;
        }
  
        //bulletin
        $sql_bul = "SELECT rowid, matricule, nom, prenom, situation_familiale, nombre_enfant, nombre_enfant_hand, sexe FROM ".MAIN_DB_PREFIX."bulletin WHERE fk_salarie=".$obj_verif_parent->fk_salarie;
        $res_bul = $db->query($sql_bul);
        if($res_bul){
          $num_bul = $db->num_rows($res_bul);
          $j = 0;
          while($j < $num_bul){
            $obj_bul = $db->fetch_object($res_bul);
            $sql_taxe = "SELECT montant FROM ".MAIN_DB_PREFIX."bulletin_taxe WHERE fk_bulletin=".$obj_bul->rowid;
            $res_taxe = $db->query($sql_taxe);
            if($res_taxe){
              $obj_taxe = $db->fetch_object($res_taxe);
              $somme_its += $obj_taxe->montant;
            }
            $j ++;
          }
        

        //bulletin bonus
        $sql_bul_bonus = "SELECT rowid FROM ".MAIN_DB_PREFIX."bulletin_bonus WHERE annee=".$annee_rechercher." AND fk_salarie=".$obj_verif_parent->fk_salarie;
        $res_bul_bonus = $db->query($sql_bul_bonus);
        if($res_bul_bonus){
          $num_bul_bonus = $db->num_rows($res_bul_bonus);
          $j = 0;
          while($j < $num_bul_bonus){
            $obj_bul_bonus = $db->fetch_object($res_bul_bonus);
            $sql_taxe = "SELECT montant FROM ".MAIN_DB_PREFIX."bulletin_bonus_taxe WHERE fk_bulletin=".$obj_bul_bonus->rowid;
            $res_taxe = $db->query($sql_taxe);
            if($res_taxe){
              $obj_taxe = $db->fetch_object($res_taxe);
              $somme_its += $obj_taxe->montant;
            }
            $j ++;
          }
        }
  
          $obj_array[$a]["matricule"] = $obj_bul->matricule;
          $obj_array[$a]["nom"] = $obj_bul->nom;
          $obj_array[$a]["prenom"] = $obj_bul->prenom;
          $obj_array[$a]["sexe"] = $obj_bul->sexe;
          $obj_array[$a]["situation_familiale"] = $obj_bul->situation_familiale;
          $obj_array[$a]["nombre_enfant"] = $obj_bul->nombre_enfant;
          $obj_array[$a]["nombre_enfant_hand"] = $obj_bul->nombre_enfant_hand;
          $obj_array[$a]["somme_brut"] = $somme_brut;
          $obj_array[$a]["somme_brut_imposable"] = $somme_brut_imposable;
          $obj_array[$a]["somme_its"] = $somme_its;
          $its_annuel = its_salarie_annuel($db, $somme_brut, $obj_bul->situation_familiale, $obj_bul->nombre_enfant, $obj_bul->nombre_enfant_hand);
          $obj_array[$a]["its_annuelle"] = $its_annuel;
          $obj_array[$a]["difference"] = $its_annuel - $somme_its;
  
        }
        $a ++;
      }
    }
    return $obj_array;
  }

  //ITS annuel article24.
function its_salarie_annuel($db, $salaire_brut, $situation_familiale = "celibataire", $nb_enfant = 0, $nb_enf_hand = 0){
	$taux_montant_its_annuel_mensuel = array();
		$mont = "".$salaire_brut;
	if($salaire_brut <= 250){
		return 0;
	}else{
		
		$dern_ch = substr($mont, strlen($mont)-3, strlen($mont)-1);
		if($dern_ch >=0 && $dern_ch < 250){
			$mont = substr($mont, 0, strlen($mont)-3)."000";
		}else if($dern_ch >=251 && $dern_ch < 500){

			$mont = substr($mont, 0, strlen($mont)-3)."250";
		}else if($dern_ch >=501 && $dern_ch < 750){
			$mont = substr($mont, 0, strlen($mont)-3)."500";
		}else if($dern_ch >=701 && $dern_ch <= 999){
			$mont = substr($mont, 0, strlen($mont)-3)."750";
		}


		
		$ss = (int) $mont;
	//-----------------------------------------
				
			
		
			$tab = 0;
				$sql_bareme = "SELECT * FROM ".MAIN_DB_PREFIX."taxe WHERE fk_type=1 AND montant_debut<=".$ss." ORDER BY montant_debut ASC";
				$result_bareme = $db->query($sql_bareme);
				if($result_bareme){
					$i = 0;
					$num = $db->num_rows($result_bareme);
					while ($i < $num) {
						$bareme = $db->fetch_object($result_bareme);
						if($num >= 2)
							if($i == ($num - 1)){
								$tab = $tab + ((($ss - $bareme->montant_debut)*$bareme->taux)/100);
							}else if($i == ($num - 2)){
								$tab = $tab +  $bareme->valeur;
							}
						$i ++;
					}
					$taux = 0;
					if($situation_familiale == "marie")
						$taux = 10;
					$taux = $taux + ($nb_enfant - $nb_enf_hand)*2.5;
					$taux = $taux + $nb_enf_hand*10;

										

					$its_brut = $tab;

					$its_annuel_net = $its_brut - ($its_brut * $taux / 100);

					$taux_its_annuel =  ($its_annuel_net/$ss)*100;
					$taux_its_reduit = $taux_its_annuel - 2;

					if($taux_its_reduit < 0)
						$taux_its_reduit = 0;


					$its_annuel = ($taux_its_reduit*$ss)/100;
					
				
		}

		return $its_annuel;
	}
		
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