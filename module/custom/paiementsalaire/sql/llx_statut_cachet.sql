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

CREATE TABLE llx_statut_cachet
(
    rowid               integer AUTO_INCREMENT PRIMARY KEY,
    fk_societe          integer,
    avant_cloture       integer, --0=>pas de cachet sur les documents avant la cloture du mois | 1=>Cacheter les documents avant la cloture du mois
    apres_cloture       integer   --0=>pas de cachet sur les documents même après la cloture du mois | 1=>Cacheter les documents après la cloture du mois
)ENGINE=innodb;