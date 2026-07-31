<?php
/* Copyright (C) 2001-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2013 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2015 Regis Houssin        <regis.houssin@inodbox.com>
 * Copyright (C) 2015-2020 Juanjo Menent <jmenent@2byte.es>
 * Copyright (C) 2015      Jean-François Ferry <jfefe@aternatik.fr>
 * Copyright (C) 2015      Raphaël Doursenaud   <rdoursenaud@gpcsolutions.fr>
 * Copyright (C) 2016      Marcos García        <marcosgdf@gmail.com>
 * Copyright (C) 2019      Nicolas ZABOURI      <info@inovea-conseil.com>
 * Copyright (C) 2020      Tobias Sekan         <tobias.sekan@startmail.com>
 * Copyright (C) 2020      Josep Lluís Amador   <joseplluis@lliuretic.cat>
 * Copyright (C) 2021      Frédéric France      <frederic.france@netlogic.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 */

/**
 * \file       htdocs/compta/index.php
 * \ingroup    compta
 * \brief      Main page of accountancy area
 */

require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/custom/paiementsalaire/lib/paiementsalaire.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';

$info = '';
$licence = '';
$results = array('status' => '');
$action = GETPOST('action', 'alpha');
$mois_courant = (int) date('m');
$annee_courante = (int) date('Y');

//-------------------------------------------------------------------------------------------
// Verification de la licence
$sql_licence = "SELECT licence_status FROM ".MAIN_DB_PREFIX."dolipaie_type";
$result_licence = $db->query($sql_licence);
if ($result_licence) {
    $nb_row_licence = $db->num_rows($result_licence);
    if ($nb_row_licence > 0) {
        $licence_obj = $db->fetch_object($result_licence);
        if ($licence_obj) {
            $licence = $licence_obj->licence_status;
            if ($licence_obj->licence_status != 'Active') {
                $info = 'Veuillez renouveller votre licence';
            }
        } else {
            $info = 'Veuillez renouveller votre licence';
        }
    } else {
        $info = 'Veuillez renouveller votre licence';
    }
} else {
    $info = 'Veuillez renouveller votre licence';
}

//-----------------------------------------------------------------------------------------------------------------------------------------------------

$fk_user = GETPOST('id', 'int');
$id_societe = GETPOST('id_societe', 'int');
$fk_salarie = GETPOST('fk_salarie', 'int');
$id_convention = GETPOST('id_convention', 'int');
$annee_rechercher = GETPOST('annee_rechercher', 'int');
$id_bull = 0;

$form = new Form($db);
$periode_error = '';

$mois_options = array();
for ($m = 1; $m <= 13; $m++) {
    $mois_options[$m] = ($m == 13) ? '13è Mois' : (string) $m;
}

$annee_options = array();
$sql_annees_bulletin = "SELECT DISTINCT annee FROM ".MAIN_DB_PREFIX."bulletin WHERE fk_salarie=".$fk_salarie." ORDER BY annee DESC";
$res_annees_bulletin = $db->query($sql_annees_bulletin);
if ($res_annees_bulletin) {
    while ($obj_annee_bulletin = $db->fetch_object($res_annees_bulletin)) {
        if (!empty($obj_annee_bulletin->annee)) {
            $annee_options[(int) $obj_annee_bulletin->annee] = (string) $obj_annee_bulletin->annee;
        }
    }
}

// Redirection après validation de la boîte de dialogue "Bulletin par periode".
if ($action == 'bulletin_periode' && GETPOST('confirm', 'alpha') == 'yes') {
    $mois_debut = GETPOST('mois_debut', 'int');
    $annee_debut = GETPOST('annee_debut', 'int');
    $mois_fin = GETPOST('mois_fin', 'int');
    $annee_fin = GETPOST('annee_fin', 'int');

    $periode_debut = ($annee_debut * 100) + $mois_debut;
    $periode_fin = ($annee_fin * 100) + $mois_fin;

    if ($mois_debut < 1 || $mois_debut > 13 || $mois_fin < 1 || $mois_fin > 13 || empty($annee_debut) || empty($annee_fin)) {
        $periode_error = 'Veuillez sélectionner une période valide.';
        $action = 'bulletin_periode';
    } elseif (!array_key_exists($annee_debut, $annee_options) || !array_key_exists($annee_fin, $annee_options)) {
        $periode_error = 'Veuillez sélectionner uniquement les années disponibles pour ce salarié.';
        $action = 'bulletin_periode';
    } elseif ($periode_debut > $periode_fin) {
        $periode_error = 'La date de début doit être inférieure ou égale à la date de fin.';
        $action = 'bulletin_periode';
    } else {
        $url = '../doc/tous_bulletins_salarie_intervalle.php?id_societe='.$id_societe;
        $url .= '&fk_salarie='.$fk_salarie;
        $url .= '&id_convention='.$id_convention;
        $url .= '&mois_debut='.$mois_debut;
        $url .= '&annee_debut='.$annee_debut;
        $url .= '&mois_fin='.$mois_fin;
        $url .= '&annee_fin='.$annee_fin;
        $url .= '&action=voir';
        header('Location: '.$url);
        exit;
    }
}
llxHeader('', 'Paiement | Salaire');

// Info sur la licence
if (!empty($info)) {
    print '<mark><h3 id="avertissement" style="color:red;">'.$info.'</h3></mark>';
}

// Titre
print load_fiche_titre($langs->trans('Bulletin de paie'), '', '');

$head = salaire_Head($fk_salarie, $fk_user, $id_societe, $id_convention);
print dol_get_fiche_head($head, 'bulletin', '', -1, '');

if (!empty($periode_error)) {
    setEventMessages($periode_error, null, 'errors');
}

if ($action == 'bulletin_periode') {
    if (empty($annee_options)) {
        setEventMessages('Aucune année de bulletin trouvée pour ce salarié.', null, 'warnings');
        $annee_options[$annee_rechercher] = (string) $annee_rechercher;
    }

    $annee_default = array_key_exists($annee_rechercher, $annee_options) ? $annee_rechercher : key($annee_options);

    $formquestion = array(
        array('type' => 'select', 'name' => 'mois_debut', 'label' => 'Mois début', 'values' => $mois_options, 'default' => 1),
        array('type' => 'select', 'name' => 'annee_debut', 'label' => 'Année début', 'values' => $annee_options, 'default' => $annee_default),
        array('type' => 'select', 'name' => 'mois_fin', 'label' => 'Mois fin', 'values' => $mois_options, 'default' => 6),
        array('type' => 'select', 'name' => 'annee_fin', 'label' => 'Année fin', 'values' => $annee_options, 'default' => $annee_default),
    );

    $confirm_page = $_SERVER['PHP_SELF'].'?mainmenu=paiementsalaire&leftmenu=salarie&id='.$fk_user.'&fk_salarie='.$fk_salarie.'&id_societe='.$id_societe.'&id_convention='.$id_convention.'&annee_rechercher='.$annee_rechercher;
    print $form->formconfirm(
        $confirm_page,
        'Bulletin par periode',
        'Précisez la période de génération des bulletins.',
        'bulletin_periode',
        $formquestion,
        0,
        1,
        260,
        600
    );
}

if ($user->id != 1 && $user->id != $fk_user && empty($user->rights->paiementsalaire->salarie->voirBulletin)) {
    print "<h2> Vous n'avez pas ce droit </h2>";
} else {
    if (empty($fk_salarie)) {
        print "<mark><strong>Il n'a pas encore de fk_salarie</strong></mark><br>";
        print 'Page non Disponible';
    } else {
        $obj_soc = prepare_objet_entete($fk_salarie, $fk_user, $db, $id_societe, $id_convention);
        entete_societe($obj_soc, 'societe');
        print '<hr>';

        if (empty($annee_rechercher)) {
            $annee_rechercher = $annee_courante;
        }

        $modele_bulletin = "SELECT rowid FROM ".MAIN_DB_PREFIX."modele_bulletin WHERE actif=1";
        $result_modele_bulletin = $db->query($modele_bulletin);
        if ($result_modele_bulletin) {
            $obj_modele_bulletin = $db->fetch_object($result_modele_bulletin);
            if ($obj_modele_bulletin) {
                $id_bull = (int) $obj_modele_bulletin->rowid;
            }
        }
        if (empty($id_bull)) {
            $id_bull = 1;
        }

        $mois_tab = array(' Janvier ', ' Février ', ' Mars ', ' Avril ', ' Mai ', ' Juin ', ' Juillet ', ' Août ', ' Septembre ', ' Octobre ', ' novembre ', ' Décembre ', ' 13è Mois ');

        print "<div style='float: right; margin-right:30px'>";
        print '<form name="add" method="POST" action="'.$_SERVER['PHP_SELF'].'?mainmenu=paiementsalaire&leftmenu=salarie&id='.$fk_user.'&fk_salarie='.$fk_salarie.'&id_societe='.$id_societe.'&id_convention='.$id_convention.'">';
        print '<input type="hidden" name="token" value="'.newToken().'">';
        print '<input type="hidden" name="action" value="save_edit">';

        $info_annee = "Les années affichées sont les années auquelles ce salarié à au moins un bulletin";
        print info_admin($langs->trans($info_annee), 1)."<select style='font-size: 24px; font-weight: bold;' name='annee_rechercher' id='annee_rechercher'><option value='0'></option>";

        $sql_verif = "SELECT DISTINCT annee FROM ".MAIN_DB_PREFIX."bulletin WHERE fk_salarie=".$fk_salarie;
        $res_verif = $db->query($sql_verif);
        if ($res_verif) {
            $i = 0;
            $nb = $db->num_rows($res_verif);
            $annee_tab = array();
            while ($i < $nb) {
                $obj_verif = $db->fetch_object($res_verif);
                if ($obj_verif) {
                    $annee_tab[] = $obj_verif->annee;
                    if ($obj_verif->annee == $annee_rechercher) {
                        print "<option value='".$obj_verif->annee."' selected>".$obj_verif->annee."</option>";
                    } else {
                        print "<option value='".$obj_verif->annee."'>".$obj_verif->annee."</option>";
                    }
                }
                $i++;
            }

            if ($nb == 0) {
                print "<option value='".$annee_courante."' selected>".$annee_courante."</option>";
            } elseif (!in_array($annee_courante, $annee_tab)) {
                if ($annee_rechercher == $annee_courante) {
                    print "<option value='".$annee_courante."' selected>".$annee_courante."</option>";
                } else {
                    print "<option value='".$annee_courante."'>".$annee_courante."</option>";
                }
            }
        }
        print '</select>';
        print '<input class="button" type="submit" value="Afficher">';
        print '</form>';
        print '</div>';

        // Partie d'affichage du tableau
        print '</div>';
        print "<table class='tagtable liste'>";
        print '<thead>';
        print "<tr class='liste_titre'><th align='center'>Mois</th>";
        print '<th>Salaire brut</th>';
        print '<th>Salaire net</th>';
        print '<th>I.T.S</th>';
        print '<th>Total Cotisations</th>';
        print '<th>Total Retenues</th>';
        print "<th align='center'>Opérations</th></tr>";
        print '</thead>';
        print '<tbody>';

        $total_brut = 0;
        $total_net = 0;
        $total_its = 0;
        $total_cotisation = 0;
        $total_retenue = 0;

        for ($i = 0; $i < count($mois_tab); $i++) {
            $mois = $i + 1;
            $total = 0;
            $somme_taxe = 0;
            $somme_cotisation = 0;
            $sal_brut = 0;
            $sal_net = 0;
            $rowid_bulletin = 0;

            print "<tr class='impair'>";

            $sql_verif = "SELECT rowid FROM ".MAIN_DB_PREFIX."bulletin WHERE annee=".$annee_rechercher." AND mois=".$mois." AND fk_salarie=".$fk_salarie;
            $res_verif = $db->query($sql_verif);
            if ($res_verif) {
                $obj_verif = $db->fetch_object($res_verif);
                if ($obj_verif) {
                    $rowid_bulletin = (int) $obj_verif->rowid;
                }

                $sql_som_salaire = "SELECT salaire_brut as sal_brut, net_payer as sal_net FROM ".MAIN_DB_PREFIX."bulletin WHERE annee=".$annee_rechercher." AND mois=".$mois." AND fk_salarie=".$fk_salarie;
                $res_som_salaire = $db->query($sql_som_salaire);
                if ($res_som_salaire) {
                    $obj_som_salaire = $db->fetch_object($res_som_salaire);
                    if ($obj_som_salaire) {
                        $sal_brut = $obj_som_salaire->sal_brut ?: 0;
                        $sal_net = $obj_som_salaire->sal_net ?: 0;
                    }
                }

                $doc_path = '../doc/bulletin.php';
                $all_doc_path = '../doc/tous_bulletins_salarie.php';
                if ($id_bull == 2) {
                    $doc_path = '../doc/modele_moyen/bulletin.php';
                    $all_doc_path = '../doc/modele_moyen/tous_bulletins_salarie.php';
                } elseif ($id_bull == 3) {
                    $doc_path = '../doc/modele_avance/bulletin.php';
                    $all_doc_path = '../doc/modele_avance/tous_bulletins_salarie.php';
                }

                if ($annee_rechercher == $annee_courante && $mois == $mois_courant) {
                    $message_info = 'Vous pourrez télécharger le bulletin de paie une fois le mois cloturé';
                    if (verification($db, $fk_salarie, $fk_user, $id_convention)) {
                        if ($licence == 'Active') {
                            print "<td><b>".$mois_tab[$i]."</b></td>
                            <td align='center' colspan='6'>".info_admin($langs->trans($message_info), 1)."<a class='button' target='_blank' href='".$doc_path."?id_societe=".$id_societe."&fk_user=".$fk_user."&fk_salarie=".$fk_salarie."&id_convention=".$id_convention."&mois=".$mois."&annee=".$annee_rechercher."&action=no_save'>Voir un Aperçu du Bulletin</a></td>";
                        } else {
                            print "<td><b>".$mois_tab[$i]."</b></td>
                            <td align='center' colspan='6'>".info_admin("Impossible d'afficher les bulletins", 1)."<button>".$results['status']."</button></td>";
                        }
                    } else {
                        if ($licence == 'Active') {
                            print "<td><b>".$mois_tab[$i]."</b></td>
                            <td align='center' colspan='6'>".info_admin($langs->trans($message_info), 1)."<a class='button' target='_blank' href='./salarie_verification.php?mainmenu=paiementsalaire&leftmenu=salarie&id_societe=".$id_societe."&fk_salarie=".$fk_salarie."&id_convention=".$id_convention."&id=".$fk_user."'>Voir Onglet vérification</a></td>";
                        } else {
                            print "<td><b>".$mois_tab[$i]."</b></td>
                            <td align='center' colspan='6'>".info_admin("Impossible d'afficher les bulletins", 1)."<button>".$results['status']."</button></td>";
                        }
                    }
                } elseif ($rowid_bulletin > 0) {
                    $sql_som_taxe = "SELECT SUM(montant) as som_taxe FROM ".MAIN_DB_PREFIX."bulletin_taxe WHERE fk_bulletin=".$rowid_bulletin;
                    $res_som_taxe = $db->query($sql_som_taxe);
                    if ($res_som_taxe) {
                        $obj_som_taxe = $db->fetch_object($res_som_taxe);
                        if ($obj_som_taxe) {
                            $somme_taxe = $obj_som_taxe->som_taxe ?: 0;
                        }
                    }

                    $sql_som_cotisation = "SELECT SUM(montant_employe) as som_cotisation FROM ".MAIN_DB_PREFIX."bulletin_cotisation WHERE fk_bulletin=".$rowid_bulletin;
                    $res_som_cotisation = $db->query($sql_som_cotisation);
                    if ($res_som_cotisation) {
                        $obj_som_cotisation = $db->fetch_object($res_som_cotisation);
                        if ($obj_som_cotisation) {
                            $somme_cotisation = $obj_som_cotisation->som_cotisation ?: 0;
                        }
                    }

                    $total = $somme_taxe + $somme_cotisation;

                    print '<td><b>'.$mois_tab[$i].'</b></td>';
                    print '<td>'.apres_virgule($db, $id_societe, $sal_brut).'</td><td>'.apres_virgule($db, $id_societe, $sal_net).'</td><td>'.apres_virgule($db, $id_societe, $somme_taxe).'</td><td>'.apres_virgule($db, $id_societe, $somme_cotisation).'</td><td>'.apres_virgule($db, $id_societe, $total).'</td>';
                    print "<td align='center'><a style='text-decoration : none;' title='Voir' target='_blank' href='".$doc_path."?id_societe=".$id_societe."&fk_user=".$fk_user."&fk_salarie=".$fk_salarie."&id_convention=".$id_convention."&mois=".$mois."&annee=".$annee_rechercher."&action=voir'><span class='fa fa-search-plus'></span>&nbsp; &nbsp;</a>&nbsp;
                    <a style='text-decoration : none;' title='Télécharger' target='_blank' href='".$doc_path."?id_societe=".$id_societe."&fk_user=".$fk_user."&fk_salarie=".$fk_salarie."&id_convention=".$id_convention."&mois=".$mois."&annee=".$annee_rechercher."&action=telecharger'><span class='fa fa-download'></span> &nbsp;</a>&nbsp;</td>";
                } elseif ($mois < $mois_courant) {
                    print '<td><b>'.$mois_tab[$i].'</b></td>';
                    print '<td>'.apres_virgule($db, $id_societe, 0).'</td><td>'.apres_virgule($db, $id_societe, 0).'</td><td>'.apres_virgule($db, $id_societe, 0).'</td><td>'.apres_virgule($db, $id_societe, 0).'</td><td>'.apres_virgule($db, $id_societe, 0).'</td>';
                    print "<td align='center'>
                    <span class='fa fa-search-plus' style='color: gray'></span> &nbsp;&nbsp;&nbsp;
                    <span class='fa fa-download' style='color: gray'></span> &nbsp;&nbsp;&nbsp;
                    </td>";
                } else {
                    print '<td><b>'.$mois_tab[$i].'</b></td>';
                    print '<td>'.apres_virgule($db, $id_societe, 0).'</td><td>'.apres_virgule($db, $id_societe, 0).'</td><td>'.apres_virgule($db, $id_societe, 0).'</td><td>'.apres_virgule($db, $id_societe, 0).'</td><td>'.apres_virgule($db, $id_societe, 0).'</td>';
                    print "<td align='center'>
                    <span class='fa fa-search-plus' style='color: gray'></span>&nbsp;&nbsp;
                    <span class='fa fa-download'></span> &nbsp;&nbsp;
                    </td>";
                }
            } else {
                print '<td><b>'.$mois_tab[$i].'</b></td>';
                print '<td></td><td></td><td></td><td></td><td></td>';
                print "<td align='center'>
                <span class='fa fa-search-plus' style='color: gray'></span>&nbsp;&nbsp;
                <span class='fa fa-download'></span> &nbsp;&nbsp;
                </td>";
            }

            print '</tr>';

            $total_net += $sal_net;
            $total_brut += $sal_brut;
            $total_its += $somme_taxe;
            $total_cotisation += $somme_cotisation;
            $total_retenue += $total;
        }

        print '<tr>';
        print "<td style='background-color: lightgray;'><b>Total".info_admin("Attention : le mois en cours n'est ajouté", 1).'</b></td>';
        print "<td style='background-color: lightgray;'>".apres_virgule($db, $id_societe, $total_brut)."</td><td style='background-color: lightgray;'>".apres_virgule($db, $id_societe, $total_net)."</td><td style='background-color: lightgray;'>".apres_virgule($db, $id_societe, $total_its)."</td><td style='background-color: lightgray;'>".apres_virgule($db, $id_societe, $total_cotisation)."</td><td style='background-color: lightgray;'>".apres_virgule($db, $id_societe, $total_retenue).'</td>';
        print "<td style='background-color: lightgray;'></td>";
        print '</tr>';

        print '</tbody>';
        print '</table>';

        print '</div>';
        print "<div style='text-align:left; margin-top:10px'>";
        print '</div>';

        $sql_verif = "SELECT rowid FROM ".MAIN_DB_PREFIX."bulletin WHERE fk_salarie=".$fk_salarie." AND annee=".$annee_rechercher;
        $res_verif = $db->query($sql_verif);
        if ($res_verif) {
            $num = $db->num_rows($res_verif);
            if ($action == 'telecharger') {
                $bouton = 'Télécharger pour tous les ('.$num.') salariés';
            } else {
                $bouton = 'Les ('.$num.') bulletin(s) du '.$annee_rechercher;
            }

            if ($id_bull == 1) {
                print "<a target='_blank' href='../doc/tous_bulletins_salarie.php?id_societe=".$id_societe."&fk_salarie=".$fk_salarie."&id_convention=".$id_convention."&annee=".$annee_rechercher."&action=voir' style='float: right;' class='button'>".$bouton.'</a>';
            } elseif ($id_bull == 2) {
                print "<a target='_blank' href='../doc/modele_moyen/tous_bulletins_salarie.php?id_societe=".$id_societe."&fk_salarie=".$fk_salarie."&id_convention=".$id_convention."&annee=".$annee_rechercher."&action=voir' style='float: right;' class='button'>".$bouton.'</a>';
            } elseif ($id_bull == 3) {
                print "<a target='_blank' href='../doc/modele_avance/tous_bulletins_salarie.php?id_societe=".$id_societe."&fk_salarie=".$fk_salarie."&id_convention=".$id_convention."&annee=".$annee_rechercher."&action=voir' style='float: right;' class='button'>".$bouton.'</a>';
            }

            print "<a href='".$_SERVER['PHP_SELF']."?mainmenu=paiementsalaire&leftmenu=salarie&id=".$fk_user."&fk_salarie=".$fk_salarie."&id_societe=".$id_societe."&id_convention=".$id_convention."&annee_rechercher=".$annee_rechercher."&action=bulletin_periode' style='float: right; margin-right: 8px;' class='button'>Bulletin par periode</a>";
        }
    }
}

function verification($db, $fk_salarie, $fk_user, $id_convention)
{
    $incorrect = 0;

    $sql_sal = "SELECT * FROM ".MAIN_DB_PREFIX."salarie WHERE rowid=".$fk_salarie;
    $result_sal = $db->query($sql_sal);
    if (!$result_sal) {
        return false;
    }

    $obj_salarie = $db->fetch_object($result_sal);
    if (!$obj_salarie) {
        return false;
    }

    $grilleSql = "SELECT rowid FROM ".MAIN_DB_PREFIX."grille WHERE active=1 AND fk_convention=".$id_convention;
    $grilleResult = $db->query($grilleSql);
    if ($grilleResult) {
        $obj_grille = $db->fetch_object($grilleResult);
        if ($obj_grille) {
            $salBaseSql = "SELECT salaire_base FROM ".MAIN_DB_PREFIX."grille_categorie_echelon_salaire_base WHERE fk_grille=".$obj_grille->rowid." AND fk_categorie=".$obj_salarie->fk_categorie." AND fk_echelon=".$obj_salarie->fk_echelon;
            $salBaseResult = $db->query($salBaseSql);
            if ($salBaseResult) {
                $objSalBase = $db->fetch_object($salBaseResult);
                if (!$objSalBase || $objSalBase->salaire_base == null) {
                    $incorrect++;
                }
            } else {
                $incorrect++;
            }
        } else {
            $incorrect++;
        }
    } else {
        $incorrect++;
    }

    if ($obj_salarie->sursalaire == null) {
        $incorrect++;
    }

    $annee = (int) date('Y');
    $mois = (int) date('m');
    $jour = (int) date('d');

    $sql_contrat = "SELECT * FROM ".MAIN_DB_PREFIX."salarie_contrat WHERE fk_salarie=".$obj_salarie->rowid." AND active=1 AND fk_type_contrat != 2";
    $sql_contrat .= " AND (YEAR(date_fin)>".$annee;
    $sql_contrat .= " OR ((YEAR(date_fin) = ".$annee." AND MONTH(date_fin) > ".$mois.") OR (YEAR(date_fin) = ".$annee." AND MONTH(date_fin) = ".$mois." AND DAY(date_fin) >= ".$jour.")))";
    $res_contrat = $db->query($sql_contrat);

    if (!$res_contrat || $db->num_rows($res_contrat) <= 0) {
        $sql_contrat = "SELECT * FROM ".MAIN_DB_PREFIX."salarie_contrat WHERE fk_salarie=".$obj_salarie->rowid." AND active=1 AND fk_type_contrat = 2";
        $res_contrat = $db->query($sql_contrat);
        if (!$res_contrat || $db->num_rows($res_contrat) <= 0) {
            $incorrect++;
        }
    }

    $sql = "SELECT * FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$obj_salarie->fk_user;
    $result_user = $db->query($sql);
    $obj_user = null;
    if ($result_user) {
        $obj_user = $db->fetch_object($result_user);
    }

    if (!$obj_user || (!$obj_user->dateemployment && !$obj_salarie->date_anciennete)) {
        $incorrect++;
    }

    if (!$obj_user || !$obj_user->job) {
        $incorrect++;
    }

    if ($obj_salarie->situation_familiale == null) {
        $incorrect++;
    }

    if ($obj_salarie->nombre_enfant == null) {
        $incorrect++;
    }

    if ($obj_salarie->nombre_enfant_hand == null) {
        $incorrect++;
    }

    return ($incorrect == 0);
}

function apres_virgule($db, $id_societe, $valeur)
{
    $sep = '.';
    $decalage = 2;
    $valeur = $valeur ?: 0;

    $reglage_bulletin = "SELECT separateur, decalage FROM ".MAIN_DB_PREFIX."reglage_bulletin WHERE fk_societe=".$id_societe;
    $result_reglage_bulletin = $db->query($reglage_bulletin);
    if ($result_reglage_bulletin && $db->num_rows($result_reglage_bulletin) > 0) {
        $obj_reglage_bulletin = $db->fetch_object($result_reglage_bulletin);
        if ($obj_reglage_bulletin) {
            $sep = $obj_reglage_bulletin->separateur;
            $decalage = $obj_reglage_bulletin->decalage;
        }
    }

    return number_format($valeur, $decalage, $sep, ' ');
}

$db->close();
