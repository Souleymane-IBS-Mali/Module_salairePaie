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

CREATE TABLE llx_bulletin
(
    rowid                       integer AUTO_INCREMENT PRIMARY KEY,
    fk_salarie                  integer,
    nom                         VARCHAR(50),
    prenom                      VARCHAR(50),
    matricule                   VARCHAR(100),
    situation_familiale         VARCHAR(15),
    nombre_enfant               integer,
    nombre_enfant_hand          integer,
    calcul_salaire              VARCHAR(3),
    categorie                   VARCHAR(50),
    echelon                     VARCHAR(30),
    contrat                     VARCHAR(30),
    diplome                     VARCHAR(30),
    type_salarie                VARCHAR(30),
    fonction                    VARCHAR(255),
    date_embauche               date,
    inps                        VARCHAR(30),
    amo                         VARCHAR(30),
    banque                      VARCHAR(30),
    compte                      VARCHAR(50),
    sexe                        VARCHAR(15),
    pays                        VARCHAR(20),
    ville                       VARCHAR(20),
    addresse                    VARCHAR(255),
    tel                         VARCHAR(20),
    email                       VARCHAR(50),
    annee                       integer,
    mois                        integer,
    salaire_base                VARCHAR(50),
    sursalaire                  VARCHAR(50),
    salaire_brut                VARCHAR(50),
    salaire_brut_cotisable      VARCHAR(50),
    salaire_brut_imposable      VARCHAR(50),
    net_payer                   VARCHAR(50),
    cloture                     VARCHAR(4) DEFAULT "non",
    fk_societe                  integer,
    nom_societe                 VARCHAR(255),
    logo_societe                VARCHAR(255),
    nom_convention              VARCHAR(255),
    pourcentage                 VARCHAR(5),
    date_creation               timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)ENGINE=innodb;  