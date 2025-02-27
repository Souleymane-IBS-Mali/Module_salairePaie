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

CREATE TABLE llx_salarie_contrat
(
  rowid               integer AUTO_INCREMENT PRIMARY KEY,
  fk_salarie          integer,
  numero              VARCHAR(25),
  fk_type_contrat     integer,
  date_signature      DATE,
  date_embauche       DATE,
  date_fin            DATE,
  salaire_brut        VARCHAR(14),
  fichier_contrat     VARCHAR(255),
  active              integer,
  import_key	        VARCHAR(14)
)ENGINE=innodb;
