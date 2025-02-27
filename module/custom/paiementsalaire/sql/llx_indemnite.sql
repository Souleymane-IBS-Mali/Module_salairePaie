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

CREATE TABLE llx_indemnite
(
  rowid                   integer            AUTO_INCREMENT PRIMARY KEY,
  libelle                 varchar(255)        NOT NULL,
  type_indemnite          varchar(20), --Obligatoire | facultative
  commentaire             varchar(255),
  appliquee               integer DEFAULT 0, --1=>salaire de base | 2=>salaire de base imposable | 3=>Montant fixe
  exonere                 varchar(5),       --oui=>exonérée | non=>non exonérée pour rétirer sa valeur du salaire de base
  active                  integer  DEFAULT 0 NOT NULL, --1=>activé | 0=>desactivé
  affiche_bulletin        VARCHAR(5),
  soumis_impot            VARCHAR(5) DEFAULT 'Non',
  porcentage_soumis_impot VARCHAR(5),
  soumis_cotisation       VARCHAR(5) DEFAULT 'Non',
  ajout_base_hs           VARCHAR(5) DEFAULT 'Non',--Si elle est prise en compte au calcul des heures suplémentaire
  porcentage_soumis_cotis VARCHAR(5),
  fk_convention           integer DEFAULT 0,
  fk_societe              integer DEFAULT 0,
  fk_accord_etablissement integer DEFAULT 0
)ENGINE=innodb;