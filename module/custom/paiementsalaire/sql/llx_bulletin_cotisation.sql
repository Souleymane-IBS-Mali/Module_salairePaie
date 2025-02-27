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

CREATE TABLE llx_bulletin_cotisation
(
    rowid               integer AUTO_INCREMENT PRIMARY KEY,
    fk_bulletin         integer,
    fk_cotisation       integer,
    libelle             VARCHAR(150),
    taux_employe        VARCHAR(5),
    taux_employeur      VARCHAR(5),
    montant_employe     VARCHAR(11),
    montant_employeur   VARCHAR(11),
    affiche_bulletin    VARCHAR(5)
)ENGINE=innodb;  