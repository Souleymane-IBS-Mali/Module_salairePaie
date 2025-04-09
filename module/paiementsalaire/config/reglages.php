<?php
/* Copyright (C) 2001-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2013 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2015 Regis Houssin        <regis.houssin@inodbox.com>
 * Copyright (C) 2015-2020 Juanjo Menent	<jmenent@2byte.es>
 * Copyright (C) 2015      Jean-François Ferry	<jfefe@aternatik.fr>
 * Copyright (C) 2015      Raphaël Doursenaud   <rdoursenaud@gpcsolutions.fr>
 * Copyright (C) 2016      Marcos García        <marcosgdf@gmail.com>
 * Copyright (C) 2019      Nicolas ZABOURI      <info@inovea-conseil.com>
 * Copyright (C) 2020      Tobias Sekan         <tobias.sekan@startmail.com>
 * Copyright (C) 2020      Josep Lluís Amador   <joseplluis@lliuretic.cat>
 * Copyright (C) 2021      Frédéric France		<frederic.france@netlogic.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 *	\file       htdocs/compta/index.php
 *	\ingroup    compta
 *	\brief      Main page of accountancy area
 */

require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/custom/paiementsalaire/lib/paiementsalaire.lib.php';

//require_once DOL_DOCUMENT_ROOT.'/core/class/html.formpaiementsalaire.class.php';
//require_once DOL_DOCUMENT_ROOT.'/custom/paiementsalaire/core/modules/modPaiementSalaire.class.php';

//$PaiementSalaire = new modPaiementSalaire($db);

$form = new Form($db);
llxHeader("", "Paiement | Salaire");

    print load_fiche_titre($langs->trans("Réglages"), '', '');
    print '<hr>';

    print '<div class="div-table-responsive-no-min">';
				print '<table class="noborder centpercent">';

				// Line for title
				print '<!-- line title to add new entry -->';
				print '<tr class="liste_titre">';
                print '<th>Reglages</th><th></th><th></th>';
                print '</tr>';

                /*print '<tr class="oddeven nodrag nodrop nohover">';
                print '<td><a href="./modele_bulletin.php?mainmenu=paiementsalaire&leftmenu=reglage" >Modèles bulletins</a></td>';
                print '<td></td>';
                print '<td></td>';*/

                print '</tr>';
                print '<tr class="oddeven nodrag nodrop nohover">';
                print '<td><a title="Constutition des chiffres sur le bulletin" href="reglage_bulletin.php?mainmenu=paiementsalaire&leftmenu=reglage" >Separateurs decimals</a></td>';
                print '<td></td>';
                print '<td></td>';

                print '</tr>';

                print '<tr class="oddeven nodrag nodrop nohover">';
                print '<td><a title="Pouvoir importer un logo de la société" href="logo_societe.php?mainmenu=paiementsalaire&leftmenu=reglage" >Logo société</a></td>';
                print '<td></td>';
                print '<td></td>';

                print '</tr>';

                print '<tr class="oddeven nodrag nodrop nohover">';
                print '<td><a title="Pouvoir mettre un cachet sur les documents" href="cachet_societe.php?mainmenu=paiementsalaire&leftmenu=reglage" >Cacheter les documents</a></td>';
                print '<td></td>';
                print '<td></td>';

                print '</tr>';

                $info = "Utilisez cette fonctionnalité uniquement si : Une cotisation à un taux différent que celui indiqué dans la configuration>cotisation. Une fois un barème particulier ajouté à une cotisation pour une société, lors de la génération des salaires les baremes pour cette cotisation(configuration>cotisation) séra ignoré pour la société en question (le logiciel va automatique utilisé le barème particulier)";
                print '<tr class="oddeven nodrag nodrop nohover">';
                print '<td><a Tite="Cas particulier pour les taux des cotisations" href="cotisation_societe_cas_particulier.php?mainmenu=paiementsalaire&leftmenu=reglage" >Cotisations (Cas particulier)</a> '.info_admin($info, 1).'</td>';
                print '<td></td>';
                print '<td></td>';

                print '</tr>';


        print '</table>';