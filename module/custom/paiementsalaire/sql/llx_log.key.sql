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

--ALTER TABLE llx_log;
--drop table llx_log; 
--TINYTEXT (255 caractères max)
--TEXT (65 535 caractères max)
--MEDIUMTEXT (16 777 215 caractères max)
--LONGTEXT (4 294 967 295 caractères max)

    ALTER TABLE llx_log MODIFY nom VARCHAR(50);
    ALTER TABLE llx_log MODIFY prenom VARCHAR(50);
    ALTER TABLE llx_log MODIFY action_effectue MEDIUMTEXT;


    ALTER TABLE llx_log MODIFY quand timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
