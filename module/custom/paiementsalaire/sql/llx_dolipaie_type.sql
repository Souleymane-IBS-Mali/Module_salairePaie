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
CREATE TABLE llx_dolipaie_type
(
  rowid                 integer AUTO_INCREMENT PRIMARY KEY,
  licensekey            VARCHAR(50) NOT NULL,
  local_key             MEDIUMTEXT,
  nb_salarie            integer,
  nb_societe            integer,
  licence_status        VARCHAR(20),
  proprietaire          VARCHAR(255),
  societe               VARCHAR(255),
  email                 VARCHAR(50),
  nom_produit           VARCHAR(255),
  date_activation       DATE,
  date_expiration       DATE,
  type_abonnement       VARCHAR(50)
)ENGINE=innodb;