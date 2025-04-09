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

CREATE TABLE llx_version_dolipaie
(
  rowid                     integer AUTO_INCREMENT PRIMARY KEY,
  numero_version            VARCHAR(20), --numéro de la version 1.0.0
  date_publication          DATE, --date de publication de la version
  statut                    VARCHAR(20), --dev, beta, stable
  changelog                 TEXT, --Description des modification
  compatibilite_dolibarr    VARCHAR(255),  --Compatibilité avec les version de dolibarr
  lien_telechargement       VARCHAR(255),  --le lien de téléchargement de la version
  autheur                   VARCHAR(100),  --Nom du dévéloppeur ou de l'équipe ou de l'entreprise
  active                    integer,  --la version active (1==>active et 0==>non active)
  date_mise_a_jour          DATE,
  mise_a_jour               VARCHAR(20),  --version de la mise à jour
  lien_mise_a_jour          VARCHAR(255)  --lien de téléchargement de la mise à jour
)ENGINE=innodb;