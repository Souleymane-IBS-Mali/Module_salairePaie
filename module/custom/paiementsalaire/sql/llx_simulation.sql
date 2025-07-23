-- ========================================================================
-- Copyright (C) 2012-2017      Noé Cendrier  <noe.cendrier@altairis.fr>
--
-- This program is free software; you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation; either version 2 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with this program. If not, see <http://www.gnu.org/licenses/>.
--
-- ========================================================================

CREATE TABLE llx_simulation
(
    rowid                                           integer AUTO_INCREMENT PRIMARY KEY,
    libelle                                         VARCHAR(255),
    situation_familiale                             VARCHAR(15),
    nombre_enfant                                   integer,
    nombre_enfant_hand                              integer,
    categorie                                       VARCHAR(50),
    echelon                                         VARCHAR(30),
    fonction                                        VARCHAR(255),
    date_embauche                                   date,
    salaire_base                                    VARCHAR(50),
    sursalaire                                      VARCHAR(50),
    anciennete                                      VARCHAR(20),
    salaire_brut                                    VARCHAR(50),
    salaire_brut_cotisable                          VARCHAR(50),
    salaire_brut_imposable                          VARCHAR(50),
    net_payer                                       VARCHAR(50),
    fk_societe                                      integer,
    nom_societe                                     VARCHAR(255),
    nom_convention                                  VARCHAR(255),
    atmp_patro                                      VARCHAR(15),
    atmp_salarie                                    VARCHAR(15),
    prestation_familiale_patro                      VARCHAR(15),
    prestation_familiale_salarie                    VARCHAR(15),
    reatraite_patro                                 VARCHAR(15),
    retraite_salarie                                VARCHAR(15),
    invalidite_allocation_survivant_patro           VARCHAR(15),
    invalidite_allocation_survivant_salarie         VARCHAR(15),
    anpe_patro                                      VARCHAR(15),
    anpe_salarie                                    VARCHAR(15),
    amo_patro                                       VARCHAR(15),
    amo_salarie                                     VARCHAR(15),
    primesindemnites                                TEXT,
    its                                             VARCHAR(15),
    cout                                            VARCHAR(15),
    date_creation               timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)ENGINE=innodb;  