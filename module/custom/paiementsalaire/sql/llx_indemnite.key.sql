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

--ALTER TABLE llx_indemnite ADD affiche_bulletin VARCHAR(10) DEFAULT 'oui';
ALTER TABLE llx_indemnite ADD porcentage_soumis_impot VARCHAR(5);
ALTER TABLE llx_indemnite ADD porcentage_soumis_cotis VARCHAR(5);
ALTER TABLE llx_indemnite ADD ajout_base_hs VARCHAR(5) DEFAULT 'Non';

--ALTER TABLE llx_indemnite;
--drop table llx_indemnite;

