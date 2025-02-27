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


    $sql = "INSERT INTO ".MAIN_DB_PREFIX."convention (nom, description) VALUES('MINE', 'Dernière mise à jour de la Convention Mine')";
    $res = $db->query($sql);

    $sql = "INSERT INTO ".MAIN_DB_PREFIX."type_salarie (libelle, commentaire, convention) VALUES ('Personnel de direction','Personnel de direction',1)";
    $result = $db->query($sql);

    $sql = "INSERT INTO ".MAIN_DB_PREFIX."type_salarie (libelle, commentaire, convention) VALUES ('Ingénieurs et Assimiles','Ingénieurs et Assimiles',1)";
    $result = $db->query($sql);

    $sql = "INSERT INTO ".MAIN_DB_PREFIX."type_salarie (libelle, commentaire, convention) VALUES ('Techniciens Supérieurs','Techniciens Supérieurs',1)";
    $result = $db->query($sql);

    $sql = "INSERT INTO ".MAIN_DB_PREFIX."type_salarie (libelle, commentaire, convention) VALUES ('Agent de Maitrise et Assimiles','Agent de Maitrise et Assimiles',1)";
    $result = $db->query($sql);

    $sql = "INSERT INTO ".MAIN_DB_PREFIX."type_salarie (libelle, commentaire, convention) VALUES ('Ouvriers et Employes','Ouvriers et Employes',1)";
    $result = $db->query($sql);




    $sql = 'INSERT INTO '.MAIN_DB_PREFIX.'dcategories (code_categorie, nom_categorie, fk_type_salarie, fk_convention) VALUES ("'.$code.'","'.$nom.'","'.$type_salarie.'",'.$conv.')';
    $res = $db->query($sql);

