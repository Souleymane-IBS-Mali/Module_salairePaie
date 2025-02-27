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

CREATE TABLE llx_salarie_heure_sup
(
    rowid                   integer AUTO_INCREMENT PRIMARY KEY,
    fk_salarie              integer,
    fk_heur_sup             integer,
    nb_heure                float,
    jour                    VARCHAR(3),
    mois                    VARCHAR(3),
    annee                   VARCHAR(5),
    date_creation           timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    note                    VARCHAR(255),
    import_key              VARCHAR(14)
)ENGINE=innodb;
