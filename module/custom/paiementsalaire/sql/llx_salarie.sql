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

CREATE TABLE llx_salarie
(
  rowid                   integer AUTO_INCREMENT PRIMARY KEY,
  matricule               VARCHAR(100),
  situation_familiale     VARCHAR(15),
  nombre_enfant           integer,
  nombre_enfant_hand      integer,
  calcul_salaire          VARCHAR(3) DEFAULT 'oui',
  archiver                VARCHAR(3) DEFAULT 'non',
  fk_user                 integer,
  fk_categorie            integer,
  fk_echelon              integer DEFAULT 0,
  sursalaire              integer,
  type_salarie            integer,
  type_contrat            integer,
  fk_diplome              integer,
  inps                    VARCHAR(30),
  amo                     VARCHAR(30),
  fk_type_banque          integer,
  compte                  VARCHAR(30),
  date_modification       timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  date_anciennete         date,
  import_key	            VARCHAR(14)
)ENGINE=innodb;
