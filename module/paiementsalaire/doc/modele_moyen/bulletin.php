<?php
// Appel de la librairie FPDF
require '../../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/custom/paiementsalaire/lib/paiementsalaire.lib.php';
require("../fpdf/fpdf.php");


// Connexion à la BDD (à personnaliser)
$id_societe = GETPOST("id_societe","int");
$fk_user = GETPOST("id_salarie","int");

$action = GETPOST("action", "aplha");

if($action == "telecharger")
 $mode = "D";
else $mode = "I";

$mois = GETPOST("mois", "int");
$annee = GETPOST("annee", "int");
$fk_user = GETPOST("fk_user", "int");
$fk_salarie = GETPOST("fk_salarie", "int");
$id_convention = GETPOST("id_convention", "int");

if($fk_user && $fk_salarie){
  $id_accord_etab = 0;
  $accord_sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."accord_etablissement WHERE fk_socite=".$id_societe;
  $accord_res = $db->query($accord_sql);
  if($accord_res){
    if($db->num_row($accord_res) >0){
      $obj_accord = $db->fetch_object($accord_res);
      $id_accord_etab = $obj_accord->rowid;
    }
  }

$sql = "SELECT * FROM ".MAIN_DB_PREFIX."salarie WHERE rowid=".$fk_salarie;
$res = $db->query($sql);
$obj_salarie = $db->fetch_object($res);

if($action == "no_save"){
  $sql = "SELECT * FROM ".MAIN_DB_PREFIX."salarie WHERE rowid=".$fk_salarie;
$res = $db->query($sql);
$obj_salarie = $db->fetch_object($res);


  $user_Sql = "SELECT * FROM ".MAIN_DB_PREFIX."user where rowid=".$fk_user;
	$user_Result = $db->query($user_Sql);
	$obj_user = $db->fetch_object($user_Result);

  $societe_Sql = "SELECT * FROM ".MAIN_DB_PREFIX."societe where rowid=".$id_societe;
	$societe_Result = $db->query($societe_Sql);
	$societe_Salarie = $db->fetch_object($societe_Result);

  global $id_societe, $obj_salarie, $obj_user, $societe_Salarie, $y, $mois, $annee, $id_accord_etab, $id_convention;
  $sal_categ = 0;
}else{
  $user_Sql = "SELECT * FROM ".MAIN_DB_PREFIX."user where rowid=".$fk_user;
	$user_Result = $db->query($user_Sql);
	$obj_user = $db->fetch_object($user_Result);

  $societe_Sql = "SELECT * FROM ".MAIN_DB_PREFIX."societe where rowid=".$id_societe;
	$societe_Result = $db->query($societe_Sql);
	$societe_Salarie = $db->fetch_object($societe_Result);

  global $fk_salarie, $fk_user, $y, $mois, $annee, $id_accord_etab, $societe_Salarie;
}
// Création de la class PDF
if($action == "no_save"){
  $annee = date("Y");
	$mois = (int)date("m");

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
      return pdf_pagehead_moyen($this, "onglet_salarie");
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
    $pdf->Cell(30,4, utf8_decode("Retenu"),0,0,'C');
    $pdf->line(163,$y-1,163,$pdf->GetPageHeight()-60);


    $pdf->SetLeftMargin(163);
    $pdf->MultiCell(34,4, utf8_decode("Gain"),0,'C');

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
    $pdf->Cell(30,4, utf8_decode("Heures travaillés"),0,0,'L');

    $pdf->SetLeftMargin(63);
    $nb_total_jour = cal_days_in_month(CAL_GREGORIAN, $mois, $annee);
    $heur_normal = 173.33;
    if($nb_jours != $nb_total_jour)
      $heur_normal = round(($nb_jours*$heur_normal)/$nb_total_jour, 2);
    $pdf->Cell(20,4, utf8_decode($heur_normal),0,0,'R');

    //Salaire de base normale
    $pdf->SetLeftMargin(13);
    $y = $pdf->GetY() +6;
    $pdf->SetY($y);
    $pdf->Cell(35,4, utf8_decode("Salaire de base normale"),0,0,'L');

    //Categorie du salarié et son salaire de base
    $salaire_base = 0;
    $grilleSql = "SELECT rowid FROM ".MAIN_DB_PREFIX."grille WHERE active=1 AND fk_convention=".$id_convention;
    $grilleResult = $db->query($grilleSql);//= $db->query($grilleSql);
    $obj_grille = $db->fetch_object($grilleResult);

    $salBaseSql = "SELECT salaire_base FROM ".MAIN_DB_PREFIX."grille_categorie_echelon_salaire_base WHERE fk_grille=".$obj_grille->rowid." AND fk_categorie=".$obj_salarie->fk_categorie." AND fk_echelon=".$obj_salarie->fk_echelon;
    $salBaseResult = $db->query($salBaseSql);//= $db->query($covSql);
    $objSalBase = $db->fetch_object($salBaseResult);
    $salaire_base = $objSalBase->salaire_base;

    $tab_info_ind = salarie_indemnite($db, $obj_salarie->rowid, 0, $id_convention, $id_societe, $id_accord_etab);
							$pourcentage_ind = $tab_info_ind[0];
							$ind = $tab_info_ind[1];
							foreach ($ind as $key => $value) {
							if(!empty($key) && !empty($value)){
								//$somme += $value;
								$sql = "SELECT * FROM ".MAIN_DB_PREFIX."indemnite WHERE rowid=".$key;
								$ind_res = $db->query($sql);
								if($ind_res){
									$ind = $db->fetch_object($ind_res);
									if($ind->exonere == "oui")//retiré du salaire de base
										$retrait += $value;

									//print "<br> Nom = ".$ind->libelle." afficher sur bulletin=".$ind->affiche_bulletin."=>".$value;
								}

							}
							}

              $tab_info_pr = salarie_indemnite($db, $obj_salarie->rowid, 0, $id_convention, $id_societe, $id_accord_etab);
							$pourcentage_ind = $tab_info_pr[0];
							$pr = $tab_info_pr[1];
							foreach ($pr as $key => $value) {
							if(!empty($key) && !empty($value)){
								//$somme += $value;
								$sql = "SELECT * FROM ".MAIN_DB_PREFIX."primes WHERE rowid=".$key;
								$pr_res = $db->query($sql);
								if($pr_res){
									$pr = $db->fetch_object($pr_res);
									if($pr->exonere == "oui")//retiré du salaire de base
										$retrait += $value;

									//print "<br> Nom = ".$pr->libelle." afficher sur bulletin=".$pr->affiche_bulletin."=>".$value;
								}

							}
							}

              $salaire_base -= $retrait;

              $anciennete_tab = prime_anciennete($db, $obj_salarie->rowid, $id_convention, date('m'), date('Y'), $fk_user);
              $anciennete = $salaire_base*$anciennete_tab[1]/100;
              if($anciennete_tab[5] == "Oui")
                $salaire_base -= $anciennete;

								$base_pourcentage = 1;
								if($nb_jours != $nb_total_jour){
									$sal_base = ($nb_jours*$salaire_base)/$nb_total_jour;
									$base_pourcentage = ($sal_base*100)/$salaire_base;
									$base_pourcentage = $base_pourcentage/100;
									$salaire_base = round($salaire_base*$base_pourcentage, 2);
								}

    $pdf->SetLeftMargin(103);
      $pdf->Cell(30,4, utf8_decode(apres_virgule($db, $id_societe, $salaire_base?:0, 2)),0,0,'R');

      $sursalaire = ($obj_salarie->sursalaire*$base_pourcentage)?:0;

      //Salaire de base Majorés
      $pdf->SetTextColor(0, 0, 70);
      $pdf->SetLeftMargin(13);
      $y = $pdf->GetY() +6;
      $pdf->SetY($y);
      $pdf->line(12,$y-1 ,$pdf->GetPageWidth()-12,$y-1);
      $pdf->Cell(50,4, utf8_decode("Salaire de base Majorés"),0,0,'L');

      $pdf->SetLeftMargin(163);
      $pdf->Cell(35,4, utf8_decode(apres_virgule($db, $id_societe, $salaire_base, 2)),0,0,'R');

      $y = $pdf->GetY()+5;
      $pdf->line(12,$y ,$pdf->GetPageWidth()-12,$y);
      //Les Primes et Indemnités
      $somme  = 0;
      $salaire_brut_imposable = $salaire_base*$base_pourcentage + $sursalaire;
			$salaire_brut_cotisable = $salaire_base*$base_pourcentage + $sursalaire;
			$salaire_brut = $salaire_base*$base_pourcentage + $sursalaire;


			$salaire_net = 0;
			$retenu_prest_empl = 0;
			$retenu_prest_patro = 0;
			$retenu_taxe = 0;
			$retenu = 0;
      $array_pr_ind_hs = array();//Cette table contient les primes et indemnités à ajouter à la base heure sup

      //Primes
      $pdf->SetTextColor(0, 0, 0);

      $pdf->SetLeftMargin(13);
      $y = $pdf->GetY() + 6;
      $pdf->SetY($y);
      $pdf->Cell(30,4, utf8_decode("P.Anciennété"),0,0,'L');

			  $salaire_brut += $anciennete;

      //if($anciennete_tab[3] == "Oui")//exonere ou non
      if($anciennete_tab[3] == "Oui")
					$salaire_brut_cotisable += $anciennete;

			if($anciennete_tab[4] == "Oui")
					$salaire_brut_imposable += $anciennete;

      $pdf->SetLeftMargin(63);
      $pdf->Cell(20,4, utf8_decode($anciennete_tab[1]."%"),0,0,'R');

      $pdf->SetLeftMargin(83);
      $pdf->Cell(20,4, utf8_decode(apres_virgule($db, $id_societe, $salaire_base*$base_pourcentage, 2)),0,0,'R');


      $pdf->SetLeftMargin(103);
      $pdf->Cell(30,4, utf8_decode(apres_virgule($db, $id_societe, $anciennete, 2)),0,0,'R');

      $somme_pr_ind = 0;


      $tab_info_pr = salarie_prime($db, $obj_salarie->rowid, $salaire_base*$base_pourcentage, $id_convention, $id_societe, $id_accord_etab);
		$pourcentage_pr = $tab_info_pr[0];
		$pr = $tab_info_pr[1];
		$index = 0;

		foreach ($pr as $key => $value) {

		if(!empty($key) && !empty($value)){

			//$somme += $value;
			$sql = "SELECT * FROM ".MAIN_DB_PREFIX."primes WHERE rowid=".$key;
			$prime_res = $db->query($sql);
			if($prime_res){
                  $pr = $db->fetch_object($prime_res);

					$salaire_brut += $value;
					if($pr->soumis_cotisation=="Oui")
						$salaire_brut_cotisable += $value;

					if($pr->soumis_impot=="Oui")
					$salaire_brut_imposable += $value;

                    $pdf->SetLeftMargin(13);

                    $y = $pdf->GetY() + 6;
                    $pdf->SetY($y);
                    $pdf->Cell(30,4, utf8_decode($pr->libelle),0,0,'L');

                    $pdf->SetLeftMargin(63);
                    $pdf->Cell(20,4, utf8_decode($pourcentage_pr[$index]."%"),0,0,'R');

                    $pdf->SetLeftMargin(83);
                    $pdf->Cell(20,4, utf8_decode(apres_virgule($db, $id_societe, $value, 2)),0,0,'R');

                    $pdf->SetLeftMargin(103);
                    $pdf->Cell(30,4, utf8_decode(apres_virgule($db, $id_societe, $value*$base_pourcentage, 2)),0,0,'R');

                    $somme_pr_ind += $value;
                    $index ++;

					}
				}
			}




				$pr_fl = prime_flottante($db, $obj_salarie->rowid);
				foreach ($pr_fl as $key => $value) {
				if(!empty($key) && !empty($value)){
					$sql = "SELECT * FROM ".MAIN_DB_PREFIX."primes WHERE rowid=".$key;
					$prime_res = $db->query($sql);
					if($prime_res){
						$pr = $db->fetch_object($prime_res);

                  $val = $value;
				$pourc = 100;

					if(count(explode('%',$value."v")) > 1)
						$val = ($objSalBase->salaire_base*$base_pourcentage*explode('%',$value)[0])/100;
					if($val != $value)
						$pourc = explode('%',$value)[0];

					$pdf->SetLeftMargin(13);

                    $y = $pdf->GetY() + 6;
                    $pdf->SetY($y);
                    $pdf->Cell(30,4, utf8_decode($pr->libelle),0,0,'L');

                    $pdf->SetLeftMargin(63);
                    $pdf->Cell(20,4, utf8_decode($pourc."%"),0,0,'R');

                    $pdf->SetLeftMargin(83);
                    $pdf->Cell(20,4, utf8_decode(apres_virgule($db, $id_societe, $val, 2)),0,0,'R');

                    $pdf->SetLeftMargin(103);
                    $pdf->Cell(30,4, utf8_decode(apres_virgule($db, $id_societe, $val*$base_pourcentage, 2)),0,0,'R');

                    if($pr->soumis_cotisation=="Oui")
						$salaire_brut_cotisable += $val*$base_pourcentage;

					if($pr->soumis_impot=="Oui")
						$salaire_brut_imposable += $val*$base_pourcentage;

                    $salaire_brut += $val*$base_pourcentage;
                    $somme_pr_ind += $val*$base_pourcentage;

					}
				}
			}

              $array_prime_exceptionnelle = salarie_prime_exceptionnelle($db, $obj_salarie->rowid, $mois, $annee);
				for ($e=0; $e < count($array_prime_exceptionnelle); $e++) {
                    if($array_prime_exceptionnelle[$e][2] == 'oui'){
                      $pdf->SetLeftMargin(13);
                      $y = $pdf->GetY() + 6;
                      $pdf->SetY($y);
                      $pdf->Cell(30,4, utf8_decode($array_prime_exceptionnelle[$e][4]),0,0,'L');
                      $pdf->SetLeftMargin(63);
                      $pdf->Cell(20,4, utf8_decode($array_prime_exceptionnelle[$e][3]."%"),0,0,'R');

                      $pdf->SetLeftMargin(83);
                      $pdf->Cell(20,4, utf8_decode(apres_virgule($db, $id_societe, $array_prime_exceptionnelle[$e][1]*$base_pourcentage, 2)),0,0,'R');

                      $pdf->SetLeftMargin(103);
                      $pdf->Cell(30,4, utf8_decode(apres_virgule($db, $id_societe, $array_prime_exceptionnelle[$e][1]*$base_pourcentage, 2)),0,0,'R');
                      $salaire_brut += $array_prime_exceptionnelle[$e][1]*$base_pourcentage;
                      $somme_pr_ind += $array_prime_exceptionnelle[$e][1]*$base_pourcentage;
                  }
                    $j ++;
                }

              $index = 0;
				$tab_info_ind = salarie_indemnite($db, $obj_salarie->rowid, $salaire_base*$base_pourcentage, $id_convention, $id_societe, $id_accord_etab);
				$pourcentage_ind = $tab_info_ind[0];
				$ind = $tab_info_ind[1];
				foreach ($ind as $key => $value) {
				if(!empty($key) && !empty($value)){
					//$somme += $value;
					$sql = "SELECT * FROM ".MAIN_DB_PREFIX."indemnite WHERE rowid=".$key;
					$ind_res = $db->query($sql);
					if($ind_res){
                  $ind = $db->fetch_object($ind_res);
						//retiré du salaire de base
					$salaire_brut += $value;

					if($ind->soumis_cotisation=="Oui")
						$salaire_brut_cotisable += $value;

					if($ind->soumis_impot=="Oui")
						$salaire_brut_imposable += $value;

                      $pdf->SetLeftMargin(13);

                  $y = $pdf->GetY() + 6;
                  $pdf->SetY($y);
                  $pdf->Cell(30,4, utf8_decode($ind->libelle),0,0,'L');

                  $pdf->SetLeftMargin(63);
                  $pdf->Cell(20,4, utf8_decode($pourcentage_ind[$index]."%"),0,0,'R');

                  $pdf->SetLeftMargin(83);
                  $pdf->Cell(20,4, utf8_decode(apres_virgule($db, $id_societe, $value, 2)),0,0,'R');

                  $pdf->SetLeftMargin(103);
                  $pdf->Cell(30,4, utf8_decode(apres_virgule($db, $id_societe*$base_pourcentage, $value, 2)),0,0,'R');

                  $somme_pr_ind += $value;
                  $index ++;
				}

			}
		}

              $ind = indemnite_flottante($db, $obj_salarie->rowid);
				foreach ($ind as $key => $value) {
				if(!empty($key) && !empty($value)){
					$sql = "SELECT * FROM ".MAIN_DB_PREFIX."indemnite WHERE rowid=".$key;
					$ind_res = $db->query($sql);
					if($ind_res){
                  $ind = $db->fetch_object($ind_res);

                  $val = $value;
					$pourc = 100;

					if(count(explode('%',$value."v")) > 1)
						$val = ($objSalBase->salaire_base*$base_pourcentage*explode('%',$value)[0])/100;
					if($val != $value)
						$pourc = explode('%',$value)[0];


                  $pdf->SetLeftMargin(13);
                  $y = $pdf->GetY() + 6;
                  $pdf->SetY($y);
                  $pdf->Cell(30,4, utf8_decode($ind->libelle),0,0,'L');

                  $pdf->SetLeftMargin(63);
                  $pdf->Cell(20,4, utf8_decode($pourc."%"),0,0,'R');

                  $pdf->SetLeftMargin(83);
                  $pdf->Cell(20,4, utf8_decode(apres_virgule($db, $id_societe, $val, 2)),0,0,'R');

                  $pdf->SetLeftMargin(103);
                  $pdf->Cell(30,4, utf8_decode(apres_virgule($db, $id_societe, $val*$base_pourcentage, 2)),0,0,'R');

                  if($ind->soumis_cotisation=="Oui")
					$salaire_brut_cotisable += $val*$base_pourcentage;

				  if($ind->soumis_impot=="Oui")
					$salaire_brut_imposable += $val*$base_pourcentage;

                  $salaire_brut += $val*$base_pourcentage;
                  $somme_pr_ind += $val*$base_pourcentage;

				}
			}
		}

              $pdf->SetLeftMargin(13);
              $pdf->SetX(13);
              $y = $pdf->GetY() +6;
              $pdf->SetY($y);
              $pdf->Cell(30,4, utf8_decode("Sursalaire"),0,0,'L');

              $pdf->SetLeftMargin(63);
              $pdf->Cell(20,4, utf8_decode("100%"),0,0,'R');

              $pdf->SetLeftMargin(83);
              $pdf->Cell(20,4, utf8_decode(apres_virgule($db, $id_societe, $sursalaire, 2)),0,0,'R');

              $pdf->SetLeftMargin(103);
              $pdf->Cell(30,4, utf8_decode(apres_virgule($db, $id_societe, $sursalaire*$base_pourcentage, 2)),0,0,'R');

              $pdf->SetTextColor(0, 0, 70);
              $pdf->SetLeftMargin(13);
              $y = $pdf->GetY() +6;
              $pdf->SetY($y);
              $pdf->line(12,$y-1 ,$pdf->GetPageWidth()-12,$y-1);
              $pdf->Cell(49,4, utf8_decode("Primes et Indemnités"),0,0,'L');

              $pdf->SetLeftMargin(163);
              $pdf->Cell(35,4, utf8_decode(apres_virgule($db, $id_societe, $somme_pr_ind + $anciennete + ($sursalaire?:0), 2)),0,0,'R');

                $y = $pdf->GetY()+5;
                $pdf->line(12,$y ,$pdf->GetPageWidth()-12,$y);

                $pdf->SetFont('Helvetica','',7);
                $pdf->SetTextColor(0, 0, 0);

                $base = $salaire_base*$base_pourcentage/173.33;

                //Heures Supplémentaires
								$base = ($salaire_base + ($retrait*$base_pourcentage))/173.33; //base des heures sup
								$salSql = "SELECT * FROM ".MAIN_DB_PREFIX."societe_prime_heure_sup where fk_societe=".$id_societe;
								$result = $db->query($salSql);
								if($result){ //Verification d'une ou plusieur prime d'heure sup
									$obj = $db->fetch_object($result);
									if($obj->salaire_base == 'oui'){
										$base += $salaire_base/173.33;
									}

									if($obj->sursalaire == 'oui'){
										$base += $sursalaire/173.33;
									}

									if($obj->anciennete == 'oui'){
										$base += $anciennete/173.33;
									}

									if($obj->montant && $obj->montant != 0){
										$base = $obj->montant;
									}

								}

                $somme_pr_ind_hs = 0;
                //recuperations des montant primes et indemnités à ajouter à la base heure sup
								for ($m=0; $m < count($array_pr_ind_hs); $m++) {
									$somme_pr_ind_hs += $array_pr_ind_hs[$m];
								}

                $trouve = false;
                //Verifions si le salarié à une configuration particulière pour les heure sup
								$sql_spec = "SELECT taux, base FROM ".MAIN_DB_PREFIX."salarie_config_heure_sup WHERE fk_salarie=".$obj_salarie->rowid;
								$res_spec = $db->query($sql_spec);
								$specail_base = 0;
								$special_taux = 0;
								if($db->num_rows($res_spec)){
									$trouve = true;
									$obj_spec = $db->fetch_object($res_spec);
									$specail_base = $obj_spec->base;
									$special_taux = $obj_spec->taux;
								}

                $base += $somme_pr_ind_hs/173.33;


                $index = 0;
                $tableau = salarie_heure_sup($db, $obj_salarie->rowid, $mois, $annee);
                $id_array = $tableau[0];
								$array_hs_taux = $tableau[1];
								$array_nb_hs = $tableau[2];

                $array_heure_sup = $tab_hs[1];
                for($index = 0; $index < count($array_hs_taux); $index ++){
                  $taux = $array_hs_taux[$index];
                  $nb_heure_sup = $array_nb_hs[$index];
                  $ma_base = $base + $base*$taux/100;
									if($trouve){
										$ma_base = $specail_base;
										$taux = $special_taux;
									}
                  //$taux est le taux d'heure sup
                  //$nb_heure_sup est le nombre d'heure sup effectuée
                    $hs_sql = "SELECT * FROM ".MAIN_DB_PREFIX."heure_sup WHERE rowid=".$id_array[$index];
                    $type_sal_heure_sup = $db->query($hs_sql);
                    $obj_sal_heure_sup = $db->fetch_object($sal_heure_sup);

                //affichage des heures sup
                      if($index == 0)
                        $y = $pdf->GetY() +6;
                      else $y += 4;
                      $pdf->SetLeftMargin(13);
                      $pdf->SetY($y);
                      $pdf->Cell(49,4, utf8_decode($nb_heure_sup.'HS '.$obj_sal_heure_sup->commentaire),0,0,'L');

                      $pdf->SetLeftMargin(63);
                      $pdf->Cell(20,4, utf8_decode($taux."%"),0,0,'R');

                      $pdf->SetX(83);
                      $pdf->Cell(20,4, utf8_decode(apres_virgule($db, $id_societe, $ma_base, 2)),0,0,'R');

                      $valeur_heur_sup = $ma_base*$nb_heure_sup;
                      $pdf->SetX(103);
                      $pdf->MultiCell(30,4, utf8_decode(apres_virgule($db, $id_societe, $valeur_heur_sup, 2)),0,'R');

                      $salaire_brut += $valeur_heur_sup;
                      $salaire_brut_cotisable += $valeur_heur_sup;
                      $salaire_brut_imposable += $valeur_heur_sup;

              }

              if($index > 0){
                $pdf->SetFont('Helvetica','',9);
                $pdf->SetTextColor(0, 0, 70);
                $pdf->SetLeftMargin(13);
                $y = $pdf->GetY() +2;
                $pdf->SetY($y);
                $pdf->line(12,$y-1 ,$pdf->GetPageWidth()-12,$y-1);
                $pdf->Cell(49,4, utf8_decode("Heures Supplémentaires"),0,0,'L');

                $pdf->SetLeftMargin(163);
                $pdf->Cell(35,4, utf8_decode(apres_virgule($db, $id_societe, $valeur_heur_sup, 2)),0,0,'R');
                $y = $pdf->GetY()+5;
                $pdf->line(12,$y ,$pdf->GetPageWidth()-12,$y);
              }
              $pdf->SetFont('Helvetica','',9);

                $pdf->SetTextColor(0, 0, 70);
                $pdf->SetLeftMargin(13);
                $y = $pdf->GetY() + 6;
                $pdf->SetY($y);
                $pdf->line(12,$y-1 ,$pdf->GetPageWidth()-12,$y-1);
                $pdf->Cell(49,4, utf8_decode("Salaire Brut"),0,0,'L');

                $pdf->SetX(103);
                $pdf->Cell(30,4, utf8_decode("--Charges Patro--"),0,0,'R');

                $pdf->SetLeftMargin(163);
                $pdf->Cell(35,4, utf8_decode(apres_virgule($db, $id_societe, $salaire_brut, 2)),0,0,'R');


                $index = 0;
								$global_cotis = salarie_prestation_organisme($db, $obj_salarie->rowid, $salaire_brut_cotisable, $id_convention);
								$cotis = $global_cotis[1];
								$taux_p = $global_cotis[0];
                $old_fk_orga = 0;
                $array_id_org = array();
                $nom_organisme = array();
                $montant_org_sal = array();
                $montant_org_patro = array();
                $pourcentage_org = array();
                $avant = false;
								foreach ($cotis as $key => $value) {

									$type_prest = "SELECT * FROM ".MAIN_DB_PREFIX."type_prestation WHERE rowid=".$key;
										$result_type_prest = $db->query($type_prest);
										$obj_prest_type = $db->fetch_object($result_type_prest);

                    if($obj_prest_type->fk_organisme != $old_fk_orga){
                        $old_fk_orga = $obj_prest_type->fk_organisme;
                        $organisme = "SELECT nom_organisme FROM ".MAIN_DB_PREFIX."organisme WHERE rowid=".$old_fk_orga;
                        $result_organisme = $db->query($organisme);
                        $obj_organisme = $db->fetch_object($result_organisme);
                        $array_id_org[] = $old_fk_orga;
                        $nom_organisme[] = $obj_organisme->nom_organisme;
                        $montant_org_sal[] = $value*$salaire_brut_cotisable/100;
                        $montant_org_patro[] = $taux_p[$index]*$salaire_brut_cotisable/100;
                        $pourcentage_org[] = $value;
                    }else{
                      $montant_org_sal[(count($montant_org_sal) - 1)] += $value*$salaire_brut_cotisable/100;
                      $montant_org_patro[(count($montant_org_patro) - 1)] += $taux_p[$index]*$salaire_brut_cotisable/100;

                      $pourcentage_org[count($pourcentage_org)-1] += $value;
                    }

										if($obj_prest_type->rowid != 6)
              					$inps += $value*$salaire_brut_cotisable/100;
                    $index ++;
								}

              for ($i=0; $i < count($nom_organisme); $i++) {
                      if($i == 0)
                        $y = $pdf->GetY() +6;
                      else $y += 6;
                      $avance = true;
                      $pdf->SetLeftMargin(13);
                      $pdf->SetY($y);
                      $pdf->Cell(49,4, utf8_decode($nom_organisme[$i]),0,0,'L');

                      $pdf->SetLeftMargin(63);
                      $pdf->Cell(20,4, utf8_decode($pourcentage_org[$i]."%"),0,0,'R');

                      $pdf->SetX(83);
                      $pdf->Cell(20,4, utf8_decode(apres_virgule($db, $id_societe, $salaire_brut_cotisable, 2)),0,0,'R');

                      $pdf->SetX(103);
                      $pdf->Cell(30,4, utf8_decode(apres_virgule($db, $id_societe, $montant_org_patro[$i], 2)),0,0,'R');

                      $pdf->SetX(133);
                      $pdf->MultiCell(30,4, utf8_decode(apres_virgule($db, $id_societe, $montant_org_sal[$i], 2)),0,'R');

                      $retenu_prest_empl += $montant_org_sal[$i];
                      $avance = true;
              }


							$index = 0;
								$global_cotis = salarie_prestation($db, $obj_salarie->rowid, $salaire_brut_cotisable, $id_convention);
								$cotis = $global_cotis[1];
								$taux_p = $global_cotis[0];

								foreach ($cotis as $key => $value) {
									$type_prest = "SELECT * FROM ".MAIN_DB_PREFIX."type_prestation WHERE rowid=".$key;
										$result_type_prest = $db->query($type_prest);
										$obj_prest_type = $db->fetch_object($result_type_prest);

                    if(!in_array($obj_prest_type->fk_organisme, $array_id_org))
                      if($obj_prest_type->affiche_bulletin == "Oui"){
                        if($index == 0){
                          if($avance)
                            $y = $pdf->GetY() + 2;
                          else $y = $pdf->GetY() + 6;
                        }else $y += 6;
                        $pdf->SetLeftMargin(13);
                        $pdf->SetY($y);
                        $pdf->Cell(49,4, utf8_decode($obj_prest_type->code),0,0,'L');

                        $pdf->SetLeftMargin(63);
                        $pdf->Cell(20,4, utf8_decode($value."%"),0,0,'R');

                        $pdf->SetX(83);
                        $pdf->Cell(20,4, utf8_decode(apres_virgule($db, $id_societe, $salaire_brut_cotisable, 2)),0,0,'R');

                        $pdf->SetX(103);
                        $pdf->Cell(30,4, utf8_decode(apres_virgule($db, $id_societe, $taux_p[$index]*$salaire_brut_cotisable/100, 2)),0,0,'R');

                        $pdf->SetX(133);
                        $pdf->MultiCell(30,4, utf8_decode(apres_virgule($db, $id_societe, $value*$salaire_brut_cotisable/100, 2)),0,'R');
                        $retenu_prest_empl += $value*$salaire_brut_cotisable/100;


                      if($obj_prest_type->rowid != 6)
                          $inps += $value*$salaire_brut_cotisable/100;
                      $index ++;
                  }
              }

              $salaire_brut_imposable -= $inps;

      $retenu_its = 0;
      $its = its_salarie($db, $obj_salarie->rowid, $salaire_brut_imposable);
			$retenu_taxe = $its[2];

            $sql_taxe = "SELECT * FROM ".MAIN_DB_PREFIX."type_taxe WHERE rowid=1";
            $result_taxe = $db->query($sql_taxe);
            $obj_taxe = $db->fetch_object($result_taxe);
            if($obj_taxe->affiche_bulletin == "Oui"){
              $y = $pdf->GetY() +2;
              $pdf->SetLeftMargin(13);
              $pdf->SetY($y);
              $pdf->Cell(49,4, utf8_decode($obj_taxe->libelle),0,0,'L');

              $pdf->SetX(63);
              $pdf->Cell(20,4, utf8_decode(round($its[0],2)."%"),0,0,'R');

              $pdf->SetX(83);
              $pdf->Cell(20,4, utf8_decode(apres_virgule($db, $id_societe, $salaire_brut_imposable, 2)),0,0,'R');

              $pdf->SetX(133);
              $pdf->MultiCell(30,4, utf8_decode(apres_virgule($db, $id_societe, $its[2], 2)),0,'R');


          }

          //Salaire net
          $pdf->SetTextColor(0, 0, 70);
          $pdf->SetLeftMargin(13);
          $y = $pdf->GetY() +3;
          $pdf->SetY($y);
          $pdf->line(12,$y-1 ,$pdf->GetPageWidth()-12,$y-1);
          $pdf->Cell(49,4, utf8_decode("Salaire Net"),0,0,'L');

          $salaire_net = $salaire_brut - $retenu_prest_empl - $retenu_taxe;
          $retenu = $retenu_prest_empl + $retenu_taxe;
          $pdf->SetLeftMargin(163);
          $pdf->Cell(35,4, utf8_decode(apres_virgule($db, $id_societe, round($salaire_net), 2)),0,0,'R');

          $pdf->SetTextColor(0, 0, 0);

          //Avance/acompte
          $somme_avance = 0;
          $avance = salarie_avance_acompte_sans_save($db, $fk_salarie, $mois, $annee);
          $i = 0;
          foreach ($avance as $key => $value) {
            $sql_avance = "SELECT libelle FROM ".MAIN_DB_PREFIX."salarie_avance WHERE rowid=".$key;
              $result_avance = $db->query($sql_avance);
              $obj_avance = $db->fetch_object($result_avance);

            $num = $db->num_rows($bulletin_avance);
            if($i == 0){
              $y = $pdf->GetY()+5;
              $pdf->line(12,$y ,$pdf->GetPageWidth()-12,$y);
            }


             if($i == 0)
               $y = $pdf->GetY() +6;
             else $y += 4;
             $pdf->SetLeftMargin(13);
             $pdf->SetY($y);
             $pdf->Cell(49,4, utf8_decode($obj_avance->libelle),0,0,'L');

             $pdf->SetX(103);
             $pdf->MultiCell(30,4, utf8_decode(apres_virgule($db, $id_societe, $value, 2)),0,'R');

              $somme_avance += $value;
              $i ++;
          }

          if($i > 0){
            $pdf->SetFont('Helvetica','',9);
            $pdf->SetTextColor(0, 0, 70);
            $pdf->SetLeftMargin(13);
            $y = $pdf->GetY() +2;
            $pdf->SetY($y);
            $pdf->line(12,$y-1 ,$pdf->GetPageWidth()-12,$y-1);
            $pdf->Cell(49,4, utf8_decode("Avances/Acomptes"),0,0,'L');

            $pdf->SetLeftMargin(163);
            $pdf->Cell(35,4, utf8_decode(apres_virgule($db, $id_societe, $somme_avance, 2)),0,0,'R');
          }

          $y = $pdf->GetY()+5;
          $pdf->line(12,$y ,$pdf->GetPageWidth()-12,$y);

      $y = $y_apres_entete +14;

      $pdf->line(12,$pdf->GetPageHeight()-60 ,$pdf->GetPageWidth()-12,$pdf->GetPageHeight()-60);

      //à gauche
      $y = $pdf->GetPageHeight()-58;
      $pdf->SetLeftMargin(13);
      $pdf->SetY($y);
      $pdf->SetFont('Helvetica','',12);
      $pdf->SetTextColor(200, 0, 0);
      $pdf->MultiCell(80,8, utf8_decode("Attention : Ce bulletin est juste un aperçu"),0,'L');

      //à droite
      //salaire net
      $pdf->SetFont('Helvetica','',9);
      $y = $pdf->GetPageHeight()-58;
      $pdf->SetLeftMargin(133);
      $pdf->SetY($y);
      $pdf->SetFillColor(255, 255, 255);
      $pdf->SetTextColor(0, 0, 0);
      $pdf->Cell(30,4, utf8_decode("Salaire Net :"),0,0,'L','true');

      $pdf->SetLeftMargin(163);
      $pdf->MultiCell(35,4, utf8_decode(apres_virgule($db, $id_societe, round($salaire_net+$retenu), 2)),0,'R','true');

      //retenu
      $pdf->SetLeftMargin(133);
      $y += 4;
      $pdf->SetY($y);
      $pdf->SetFillColor(255, 255, 255);
      $pdf->Cell(30,4, utf8_decode("Retenu :"),0,0,'L','true');
      $pdf->SetLeftMargin(163);
      $pdf->MultiCell(35,4, utf8_decode(apres_virgule($db, $id_societe, round($retenu), 2)),0,'R','true');

      $pdf->SetLeftMargin(133);
      $y += 4;
      $pdf->SetY($y);
      $pdf->SetFillColor(245, 245, 245);
      $pdf->Cell(30,4, utf8_decode("Avance :"),0,0,'L','true');
      $pdf->SetLeftMargin(163);
      $pdf->MultiCell(35,4, utf8_decode(apres_virgule($db, $id_societe, $somme_avance, 2)),0,'R','true');

      //net à payer
      $pdf->SetLeftMargin(133);
      $y += 4;
      $pdf->SetY($y);
      $pdf->SetFillColor(245, 245, 245);
      //$pdf->SetTextColor(255, 255, 255);
      //$pdf->SetAlpha(0.1);
      $pdf->Cell(30,4, utf8_decode("Net à payer :"),0,0,'L','true');
      $pdf->SetLeftMargin(163);
      $pdf->MultiCell(35,4, utf8_decode(apres_virgule($db, $id_societe, round($salaire_net-$somme_avance), 2)),0,'R','true');

      //*********************************************************************** */
      //les cadres
      $pdf->SetLeftMargin(13);
      $pdf->SetY($y+7);
      $pdf->MultiCell(59,17, "",1,'');

      $pdf->SetLeftMargin(133);
      $pdf->SetY($y+7);
      $pdf->MultiCell(59,17, "",1,'');
      $pdf->Output('test',$mode);

}else{
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
    //--------------------------------------------------------------------------------------------
    $bulletin_sql = "SELECT * FROM ".MAIN_DB_PREFIX."bulletin where fk_salarie=".$obj_salarie->rowid." AND annee=".$annee." AND mois=".$mois;
    $res_bulletin = $db->query($bulletin_sql);
    $obj_bulletin = $db->fetch_object($res_bulletin);
    if($obj_bulletin->rowid){
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
    $pdf->Cell(30,4, utf8_decode("Retenu"),0,0,'C');
    $pdf->line(163,$y-1,163,$pdf->GetPageHeight()-60);


    $pdf->SetLeftMargin(163);
    $pdf->MultiCell(34,4, utf8_decode("Gain"),0,'C');

    $pdf->line(12,$y_apres_entete +13,$pdf->GetPageWidth()-12,$y_apres_entete +13);

    //jours travaillés
    $pdf->SetTextColor(0, 0, 0);

    $pdf->SetLeftMargin(13);
    $y = $pdf->GetY() + 2;
    $pdf->SetY($y);
    $pdf->Cell(30,4, utf8_decode("Jours travaillés"),0,0,'L');
    //nombre de de jour du mois
    $salSql = "SELECT jour FROM ".MAIN_DB_PREFIX."salarie_nombre_jour_travaille where annee=".$obj_bulletin->annee." AND mois=".$obj_bulletin->mois." AND fk_salarie=".$obj_salarie->rowid;
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
    $y = $pdf->GetY() +6;
    $pdf->SetY($y);
    $pdf->Cell(35,4, utf8_decode("Salaire de base normale"),0,0,'L');

      $pdf->SetLeftMargin(103);
      $pdf->Cell(30,4, utf8_decode(apres_virgule($db, $id_societe, $obj_bulletin->salaire_base?:0, 2)),0,0,'R');

      //Salaire de base Majorés
      $pdf->SetTextColor(0, 0, 70);
      $pdf->SetLeftMargin(13);
      $y = $pdf->GetY() +6;
      $pdf->SetY($y);

      $pdf->line(12,$y-1 ,$pdf->GetPageWidth()-12,$y-1);

      $pdf->Cell(50,4, utf8_decode("Salaire de base Majorés"),0,0,'L');

      $pdf->SetLeftMargin(163);
      $pdf->Cell(35,4, utf8_decode(apres_virgule($db, $id_societe, $obj_bulletin->salaire_base, 2)),0,0,'R');

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

      $somme_pr_ind += $obj_bulletin->salaire_base*$obj_bulletin_anc->taux/100;
      $pdf->SetLeftMargin(103);
      $pdf->Cell(30,4, utf8_decode(apres_virgule($db, $id_societe, $obj_bulletin->salaire_base*$obj_bulletin_anc->taux/100, 2)),0,0,'R');

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
            $i ++;
        }
      }

      $bulletin_pr_except_sql = "SELECT * FROM ".MAIN_DB_PREFIX."bulletin_prime_exceptionnelle WHERE fk_bulletin=".$obj_bulletin->rowid;
              $bulletin_pr_except_res = $db->query($bulletin_pr_except_sql);
              if($bulletin_pr_except_res){
                $j = 0;
                $num = $db->num_rows($bulletin_pr_except_res);
                while ($j < $num){
                  $obj_bulletin_pr = $db->fetch_object($bulletin_pr_except_res);
                  if ($obj_bulletin_pr->affiche_bulletin == 'oui')
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
              }
      $bulletin_ind_sql = "SELECT * FROM ".MAIN_DB_PREFIX."bulletin_indemnite WHERE fk_bulletin=".$obj_bulletin->rowid;
      $bulletin_ind_res = $db->query($bulletin_ind_sql);
      if($bulletin_ind_res){
        $i = 0;
        $num = $db->num_rows($bulletin_ind_res);
        while ($i < $num){
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
            $i ++;
        }

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
        $i = 0;
        $num = $db->num_rows($bulletin_hs_res);
        if($num > 0){
          $y = $pdf->GetY()+5;
          $pdf->line(12,$y ,$pdf->GetPageWidth()-12,$y);
        }
        while ($i < $num){
          $obj_bulletin_hs = $db->fetch_object($bulletin_hs_res);

          if($i == 0)
            $y = $pdf->GetY() +6;
          else $y += 4;
          $pdf->SetLeftMargin(13);
          $pdf->SetY($y);
          $pdf->Cell(49,4, utf8_decode($obj_bulletin_hs->nombre_heure_sup.' '.$obj_bulletin_hs->libelle),0,0,'L');

          $pdf->SetLeftMargin(63);
          $pdf->Cell(20,4, utf8_decode($obj_bulletin_hs->taux."%"),0,0,'R');

          $pdf->SetX(83);
          $pdf->Cell(20,4, utf8_decode(apres_virgule($db, $id_societe, $obj_bulletin_hs->base, 2)),0,0,'R');

          $pdf->SetX(103);
          $pdf->MultiCell(30,4, utf8_decode(apres_virgule($db, $id_societe, $obj_bulletin_hs->montant, 2)),0,'R');

          $valeur_heur_sup += $obj_bulletin_hs->montant;
          $i ++;
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
      $pdf->SetTextColor(0, 0, 70);
      $pdf->SetLeftMargin(13);
      $y = $pdf->GetY() +6;
      $pdf->SetY($y);
      $pdf->line(12,$y-1 ,$pdf->GetPageWidth()-12,$y-1);
      $pdf->Cell(49,4, utf8_decode("Salaire Brut"),0,0,'L');

      $pdf->SetX(103);
      $pdf->Cell(30,4, utf8_decode("--Charges Patro--"),0,0,'R');

      $pdf->SetLeftMargin(163);
      $pdf->Cell(35,4, utf8_decode(apres_virgule($db, $id_societe, $obj_bulletin->salaire_brut, 2)),0,0,'R');

      //les prestations à afficher par Organisme
      $id_organisme = array();
      $bulletin_prest_sql = "SELECT * FROM ".MAIN_DB_PREFIX."bulletin_organisme WHERE fk_bulletin=".$obj_bulletin->rowid;
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
      $bulletin_taxe_sql = "SELECT * FROM ".MAIN_DB_PREFIX."bulletin_taxe WHERE fk_bulletin=".$obj_bulletin->rowid;
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
             $pdf->line(12,$y-1 ,$pdf->GetPageWidth()-12,$y-1);
             $pdf->Cell(49,4, utf8_decode("Avances/Acomptes"),0,0,'L');

             $pdf->SetLeftMargin(163);
             $pdf->Cell(35,4, utf8_decode(apres_virgule($db, $id_societe, $somme_avance, 2)),0,0,'R');
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
      $pdf->Cell(28,4, utf8_decode("Retenu :"),0,0,'L',true);
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
      //les cadres
      $pdf->SetLeftMargin(13);
      $pdf->SetY($y+7);
      $pdf->MultiCell(59,14, "",1,'');

      $pdf->SetLeftMargin(133);
      $pdf->SetY($y+7);
      $pdf->MultiCell(59,14, "",1,'');

    }
    $doc = $obj_bulletin->nom.'_'.$obj_bulletin->prenom.'_'.$mois.'_'.$annee;
    $pdf->Output($doc, $mode);

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

  $db->close();
