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

CREATE TABLE llx_primes
(
  rowid                   integer            AUTO_INCREMENT PRIMARY KEY,
  libelle                 varchar(255)        NOT NULL,
  type_prime              varchar(20), --Obligatoire | facultative
  commentaire             varchar(255),
  appliquee               integer DEFAULT 0, --1=>salaire de base | 2=>salaire de base imposable | 3=>Montant fixe
  variable_prime          integer default 0,
  exonere                 varchar(5),
  active                  integer  DEFAULT 0 NOT NULL, --1=>activé | 0=>desactivé
  affiche_bulletin        VARCHAR(5),
  ajout_base_hs           VARCHAR(5) DEFAULT 'Non',--Si elle est prise en compte au calcul des heures suplémentaire
  soumis_impot            VARCHAR(5) DEFAULT 'Non',
  soumis_cotisation       VARCHAR(5) DEFAULT 'Non',
  fk_convention           integer DEFAULT 0,
  fk_societe              integer DEFAULT 0,
  fk_accord_etablissement integer DEFAULT 0
)ENGINE=innodb;