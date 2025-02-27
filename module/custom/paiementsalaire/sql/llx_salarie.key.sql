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

ALTER TABLE llx_salarie MODIFY date_modification timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

ALTER TABLE llx_salarie ADD inps VARCHAR(30);
ALTER TABLE llx_salarie ADD amo VARCHAR(30);
ALTER TABLE llx_salarie ADD fk_type_banque integer;
ALTER TABLE llx_salarie ADD compte VARCHAR(30);
ALTER TABLE llx_salarie ADD date_anciennete date;
ALTER TABLE llx_salarie ADD calcul_salaire VARCHAR(3) DEFAULT 'oui';
ALTER TABLE llx_salarie ADD archiver VARCHAR(3) DEFAULT 'non';


--ALTER TABLE llx_salarie DROP nombre_conjoint;


--ALTER TABLE llx_salarie DROP PRIMARY KEY;
--ALTER TABLE llx_salarie MODIFY matricule VARCHAR(50);
--ALTER TABLE llx_salarie DROP identifiant;
--ALTER TABLE llx_salarie ADD rowid integer AUTO_INCREMENT PRIMARY KEY;
--ALTER TABLE llx_salarie DROP fk_societe;
--ALTER TABLE llx_salarie ADD date_modification date;
--drop table llx_salarie;
ALTER TABLE llx_salarie ADD import_key	varchar(14);
