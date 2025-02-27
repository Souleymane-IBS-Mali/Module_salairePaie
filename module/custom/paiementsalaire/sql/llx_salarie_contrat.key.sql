-- ========================================================================
-- Copyright (C) 2021 Noé Cendrier <noe.cendrier@altairis.fr>
--
-- This program is free software; you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation; either version 3 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with this program. If not, see <https://www.gnu.org/licenses/>.
--
-- ========================================================================

ALTER TABLE llx_salarie_contrat ADD import_key varchar(14);
ALTER TABLE llx_salarie_contrat ADD salaire_brut VARCHAR(14);
--ALTER TABLE llx_salarie_contrat;
--drop table llx_salarie_contrat;
--ALTER TABLE llx_salarie_contrat MODIFY numero VARCHAR(25);
