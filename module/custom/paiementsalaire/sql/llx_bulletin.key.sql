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
ALTER TABLE llx_bulletin ADD inps VARCHAR(30);
ALTER TABLE llx_bulletin ADD amo VARCHAR(30);
ALTER TABLE llx_bulletin ADD banque VARCHAR(30);
ALTER TABLE llx_bulletin ADD compte VARCHAR(30);
ALTER TABLE llx_bulletin DROP nombre_conjoint;
ALTER TABLE llx_bulletin ADD calcul_salaire VARCHAR(3);
ALTER TABLE llx_bulletin DROP avance;
ALTER TABLE llx_bulletin ADD pourcentage VARCHAR(5);
ALTER TABLE llx_bulletin MODIFY email varchar(50);
ALTER TABLE llx_bulletin MODIFY nom_societe varchar(255);
ALTER TABLE llx_bulletin MODIFY fonction varchar(255);
ALTER TABLE llx_bulletin MODIFY compte varchar(50);
ALTER TABLE llx_bulletin ADD date_creation timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;


--ALTER TABLE llx_bulletin MODIFY annee integer;

--drop table llx_bulletin;
--drop table llx_bulletin_prime;
--drop table llx_bulletin_indemnite;
--drop table llx_bulletin_cotisation;
--drop table llx_bulletin_organisme;
--drop table llx_bulletin_anciennete;
--drop table llx_bulletin_taxe;
--drop table llx_bulletin_prime_exceptionnelle;
--drop table llx_bulletin_avance;
--drop table llx_bulletin_heure_sup;
--drop table llx_bulletin_taxe2;





