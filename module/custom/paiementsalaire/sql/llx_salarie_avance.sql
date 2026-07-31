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

CREATE TABLE llx_salarie_avance
(
    rowid                       integer AUTO_INCREMENT PRIMARY KEY,
    fk_salarie                  integer,
    annee_debut_paiement        integer,
    mois_debut_paiement         integer,
    date_etablissement          timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    libelle                     VARCHAR(50),
    montant_total               VARCHAR(20),
    montant_paye                VARCHAR(20),
    montant_par_mois            VARCHAR(10),
    nombre_mois                 integer,
    note                        VARCHAR(255),
    date_affectation            timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    import_key              VARCHAR(14)
)ENGINE=innodb;  
