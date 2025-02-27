<?php
/* Copyright (C) 2004-2017 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2022 SuperAdmin
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

$verif_sql = "SELECT DISTINCT fk_convention FROM ".MAIN_DB_PREFIX."condition_anciennete";
$verif_res = $db->query($verif_sql);
$nb_obj = $db->num_rows($verif_res);
$array_fk_conv = array();
$a = 0;
if($verif_res){
   while ($a < $nb_obj) {
      $obj = $db->fetch_object($verif_res);
      $array_fk_conv[$a] = $obj->fk_convention;
      $a ++;
   }
}
//Convention Mine pour 15 ans de services ou plus id = 1; 17 enregistrements
if(!in_array(1,$array_fk_conv)){
   $annee = array("0","1","2","3","4","5","6","7","8","9","10","11","12","13","14","15","+");
   $taux = array("0","0","0","3","3","5","6","7","8","9","10","11","12","13","14","15","15");

   for ($i=0; $i < count($annee); $i++) { 
      $sql  = 'INSERT INTO '.MAIN_DB_PREFIX.'condition_anciennete (fk_convention, fk_prime, nombre_annee, taux) VALUES (1,1,"'.$annee[$i].'","'.$taux[$i].'")';
      $res = $db->query($sql);
      
}

}

// Convention banques & Assurances pour 46 ans de service (car il n'y a pas de plafond) id = 2; 47 enregistrements
if(!in_array(2,$array_fk_conv)){
   $annee = array("0","1","2","3","4","5","6","7","8","9","10","11","12","13","14","15","16","17","18","19","20","21","22","23","24","25","26","27","28","29","30","31","32","33","34","35","36","37","38","39","40","41","42","43","44","45","46");
   $taux = array("0","0","5","6.75","8.5","10.25","12","13.75","15.5","17.25","19","20.75","22.5","24.25","26","27.75","29.5","31.25","33","35.5","38","40.5","43","45.5","48","50.5","53","55.5","58","60.5","63","65.5","68","70.5","73","75.5","78","80.5","83","85.5","88","90.5","93","95.5","98","100.5","103");

   for ($i=0; $i < count($annee); $i++) { 
      $sql  = 'INSERT INTO '.MAIN_DB_PREFIX.'condition_anciennete (fk_convention, fk_prime, nombre_annee, taux) VALUES (2,1,"'.$annee[$i].'","'.$taux[$i].'")';
      $res = $db->query($sql);
      
   }
}



//Convention Commerce pour 15 ans de service ou plus id = 3; 17 enregistrements
if(!in_array(3,$array_fk_conv)){
   $annee = array("0","1","2","3","4","5","6","7","8","9","10","11","12","13","14","15","+");
   $taux = array("0","0","0","3","3","5","6","7","8","9","10","11","12","13","14","15","15");

   for ($i=0; $i < count($annee); $i++) { 
      $sql  = 'INSERT INTO '.MAIN_DB_PREFIX.'condition_anciennete (fk_convention, fk_prime, nombre_annee, taux) VALUES (3,1,"'.$annee[$i].'","'.$taux[$i].'")';
      $res = $db->query($sql);
      
   }
}


//Bâtiments & Traveaux public id = 4; 22 enregistrements
if(!in_array(4,$array_fk_conv)){
   $annee = array("0","1","2","3","4","5","6","7","8","9","10","11","12","13","14","15","16","17","18","19","20","+");
   $taux = array("0","0","0","4","4","6","7","8","9","10","11","12","13","14","15","16","17","18","19","20","21","21");

   for ($i=0; $i < count($annee); $i++) { 
      $sql  = 'INSERT INTO '.MAIN_DB_PREFIX.'condition_anciennete (fk_convention, fk_prime, nombre_annee, taux) VALUES (4,1,"'.$annee[$i].'","'.$taux[$i].'")';
      $res = $db->query($sql);
      
   }
}


//Industries Hoteliers id = 5; 21 enregistrements
if(!in_array(5,$array_fk_conv)){
      $annee = array("0","1","2","3","4","5","6","7","8","9","10","11","12","13","14","15","16","17","18","19","+");
      $taux = array("0","0","0","4","4","6","7","8","9","10","11","12","13","14","15","16","17","18","19","20","20");

      for ($i=0; $i < count($annee); $i++) { 
         $sql  = 'INSERT INTO '.MAIN_DB_PREFIX.'condition_anciennete (fk_convention, fk_prime, nombre_annee, taux) VALUES (5,1,"'.$annee[$i].'","'.$taux[$i].'")';
         $res = $db->query($sql);
         
   }
}


//Surveillance  id = 6
 //Pas de Prime d'anciennete

// Metallurgiie et Mécanique = id = 7; 22 enregistrements
if(!in_array(7,$array_fk_conv)){
   $annee = array("0","1","2","3","4","5","6","7","8","9","10","11","12","13","14","15","16","17","18","19","20","+");
   $taux = array("0","0","0","3","3","5","6","7","8","9","10","11","12","13","14","15","16","17","18","19","20","20");

   for ($i=0; $i < count($annee); $i++) { 
      $sql  = 'INSERT INTO '.MAIN_DB_PREFIX.'condition_anciennete (fk_convention, fk_prime, nombre_annee, taux) VALUES (7,1,"'.$annee[$i].'","'.$taux[$i].'")';
      $res = $db->query($sql);
      
   }
}