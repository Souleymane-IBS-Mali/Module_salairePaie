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
/* Page de gestion du solde de conge d'un salarie. */

require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/custom/paiementsalaire/lib/paiementsalaire.lib.php';

$id_societe = GETPOSTINT('id_societe');
$fk_user = GETPOSTINT('id');
$fk_salarie = GETPOSTINT('fk_salarie');
$id_convention = GETPOSTINT('id_convention');
$action = GETPOST('action', 'aZ09');

$canRead = !empty($user->admin) || !empty($user->rights->paiementsalaire->conges->read);
$canWrite = !empty($user->admin) || !empty($user->rights->paiementsalaire->conges->write);

if (!$canRead) {
    accessforbidden();
}

if (empty($fk_salarie)) {
    accessforbidden('Identifiant du salarie manquant');
}

$baseUrl = $_SERVER['PHP_SELF'].'?mainmenu=paiementsalaire&leftmenu=salarie'
    .'&id='.$fk_user
    .'&fk_salarie='.$fk_salarie
    .'&id_convention='.$id_convention
    .'&id_societe='.$id_societe;

/* Dates disponibles pour l'initialisation et la configuration. */
$dateAnciennete = '';
$dateEmployment = '';

$sqlDate = 'SELECT date_anciennete FROM '.MAIN_DB_PREFIX.'salarie';
$sqlDate .= ' WHERE rowid='.(int) $fk_salarie;
$resDate = $db->query($sqlDate);
if ($resDate && ($objDate = $db->fetch_object($resDate)) && !empty($objDate->date_anciennete)) {
    $dateAnciennete = substr($objDate->date_anciennete, 0, 10);
}

if (!empty($fk_user)) {
    $sqlDateUser = 'SELECT dateemployment FROM '.MAIN_DB_PREFIX.'user';
    $sqlDateUser .= ' WHERE rowid='.(int) $fk_user;
    $resDateUser = $db->query($sqlDateUser);
    if ($resDateUser && ($objDateUser = $db->fetch_object($resDateUser)) && !empty($objDateUser->dateemployment)) {
        $dateEmployment = substr($objDateUser->dateemployment, 0, 10);
    }
}

/*
 * Initialise le solde une seule fois, lors de la premiere ouverture.
 * Priorite : salarie.date_anciennete, puis user.dateemployment.
 * Regle : 2,5 jours par mois complet d'anciennete.
 */
$sqlExiste = 'SELECT rowid FROM '.MAIN_DB_PREFIX.'salarie_conge_solde';
$sqlExiste .= ' WHERE fk_salarie='.(int) $fk_salarie;
$sqlExiste .= ' AND entity='.(int) $conf->entity;
$resExiste = $db->query($sqlExiste);

if ($resExiste && $db->num_rows($resExiste) === 0) {
    $dateReference = !empty($dateAnciennete) ? $dateAnciennete : $dateEmployment;
    $sourceReference = !empty($dateAnciennete) ? 'anciennete' : 'employment';

    if (!empty($dateReference)) {
        try {
            $dateDebut = new DateTime(substr($dateReference, 0, 10));
            $aujourdhui = new DateTime(date('Y-m-d'));

            if ($dateDebut <= $aujourdhui) {
                $difference = $dateDebut->diff($aujourdhui);
                $moisComplets = ((int) $difference->y * 12) + (int) $difference->m;
                $soldeInitial = round($moisComplets * 2.5, 2);

                /* INSERT IGNORE evite un doublon en cas de deux ouvertures simultanees. */
                $sqlInit = 'INSERT IGNORE INTO '.MAIN_DB_PREFIX.'salarie_conge_solde';
                $sqlInit .= ' (fk_salarie, solde_jours, source_reference, date_reference, mois_calcules, date_creation, fk_user_creat, entity)';
                $sqlInit .= ' VALUES (';
                $sqlInit .= (int) $fk_salarie.', '.$soldeInitial.', "'.$db->escape($sourceReference).'", "'.$db->escape($dateDebut->format('Y-m-d')).'", '.$moisComplets;
                $sqlInit .= ', NOW(), '.(int) $user->id.', '.(int) $conf->entity.')';
                $db->query($sqlInit);
            }
        } catch (Exception $e) {
            setEventMessages('La date utilisee pour initialiser le solde de conge est invalide.', null, 'warnings');
        }
    } else {
        setEventMessages('Impossible d\'initialiser le solde : aucune date d\'anciennete ou date d\'embauche n\'est renseignee.', null, 'warnings');
    }
}

/* Reconfiguration de la date de reference et recalcul du solde. */
if ($action === 'configurer_conge') {
    if (!$canWrite) {
        accessforbidden();
    }

    $postedToken = GETPOST('token', 'alphanohtml');
    $sessionToken = isset($_SESSION['newtoken']) ? (string) $_SESSION['newtoken'] : '';
    if (empty($postedToken) || empty($sessionToken) || !hash_equals($sessionToken, (string) $postedToken)) {
        accessforbidden('Jeton de securite invalide');
    }

    $sourceReference = GETPOST('source_reference', 'alpha');
    $datePersonnalisee = GETPOST('date_personnalisee', 'alphanohtml');
    $dateReference = '';

    if ($sourceReference === 'anciennete') {
        $dateReference = $dateAnciennete;
    } elseif ($sourceReference === 'employment') {
        $dateReference = $dateEmployment;
    } elseif ($sourceReference === 'personnalisee') {
        $dateReference = $datePersonnalisee;
    }

    if (empty($dateReference)) {
        setEventMessages('La date selectionnee n\'est pas renseignee.', null, 'errors');
        header('Location: '.$baseUrl);
        exit;
    }

    try {
        $dateDebut = new DateTime(substr($dateReference, 0, 10));
        $aujourdhui = new DateTime(date('Y-m-d'));

        if ($dateDebut > $aujourdhui) {
            throw new Exception('La date de reference ne peut pas etre dans le futur.');
        }

        $difference = $dateDebut->diff($aujourdhui);
        $moisComplets = ((int) $difference->y * 12) + (int) $difference->m;
        $droitsCalcules = round($moisComplets * 2.5, 2);

        $db->begin();

        $sqlVerrou = 'SELECT rowid FROM '.MAIN_DB_PREFIX.'salarie_conge_solde';
        $sqlVerrou .= ' WHERE fk_salarie='.(int) $fk_salarie;
        $sqlVerrou .= ' AND entity='.(int) $conf->entity.' FOR UPDATE';
        $resVerrou = $db->query($sqlVerrou);
        $objVerrou = $resVerrou ? $db->fetch_object($resVerrou) : null;

        $sqlDejaPaye = 'SELECT COALESCE(SUM(jours_payes), 0) AS total_paye';
        $sqlDejaPaye .= ' FROM '.MAIN_DB_PREFIX.'salarie_conge_paiement';
        $sqlDejaPaye .= ' WHERE fk_salarie='.(int) $fk_salarie;
        $sqlDejaPaye .= ' AND entity='.(int) $conf->entity;
        $resDejaPaye = $db->query($sqlDejaPaye);
        $objDejaPaye = $resDejaPaye ? $db->fetch_object($resDejaPaye) : null;
        $totalDejaPaye = $objDejaPaye ? round((float) $objDejaPaye->total_paye, 2) : 0;
        $nouveauSolde = max(0, round($droitsCalcules - $totalDejaPaye, 2));

        if ($objVerrou) {
            $sqlConfig = 'UPDATE '.MAIN_DB_PREFIX.'salarie_conge_solde SET';
            $sqlConfig .= ' solde_jours='.$nouveauSolde;
            $sqlConfig .= ', source_reference="'.$db->escape($sourceReference).'"';
            $sqlConfig .= ', date_reference="'.$db->escape($dateDebut->format('Y-m-d')).'"';
            $sqlConfig .= ', mois_calcules='.$moisComplets;
            $sqlConfig .= ', fk_user_modif='.(int) $user->id.', tms=NOW()';
            $sqlConfig .= ' WHERE rowid='.(int) $objVerrou->rowid;
        } else {
            $sqlConfig = 'INSERT INTO '.MAIN_DB_PREFIX.'salarie_conge_solde';
            $sqlConfig .= ' (fk_salarie, solde_jours, source_reference, date_reference, mois_calcules, date_creation, fk_user_creat, entity) VALUES (';
            $sqlConfig .= (int) $fk_salarie.', '.$nouveauSolde.', "'.$db->escape($sourceReference).'", "'.$db->escape($dateDebut->format('Y-m-d')).'", '.$moisComplets;
            $sqlConfig .= ', NOW(), '.(int) $user->id.', '.(int) $conf->entity.')';
        }

        $resConfig = $db->query($sqlConfig);

        $sqlSalarieLog = 'SELECT firstname, lastname FROM '.MAIN_DB_PREFIX.'user';
        $sqlSalarieLog .= ' WHERE rowid='.(int) $fk_user;
        $resSalarieLog = $db->query($sqlSalarieLog);
        $objSalarieLog = $resSalarieLog ? $db->fetch_object($resSalarieLog) : null;

        $sqlSocieteLog = 'SELECT nom FROM '.MAIN_DB_PREFIX.'societe';
        $sqlSocieteLog .= ' WHERE rowid='.(int) $id_societe;
        $resSocieteLog = $db->query($sqlSocieteLog);
        $objSocieteLog = $resSocieteLog ? $db->fetch_object($resSocieteLog) : null;

        $nomCompletSalarieLog = $objSalarieLog
            ? trim($objSalarieLog->lastname.' '.$objSalarieLog->firstname)
            : 'Nom non disponible';
        $nomSocieteLog = $objSocieteLog ? $objSocieteLog->nom : 'Societe non disponible';

        $actionEffectue = 'Configuration du solde conge du salarie #'.(int) $fk_salarie;
        $actionEffectue .= ' - '.$nomCompletSalarieLog;
        $actionEffectue .= ' - societe : '.$nomSocieteLog;
        $actionEffectue .= ' avec la date '.$dateDebut->format('d/m/Y');
        $actionEffectue .= '. Droits calcules : '.$droitsCalcules.' jour(s), deja payes : '.$totalDejaPaye.' jour(s), solde : '.$nouveauSolde.' jour(s)';
        $sqlLog = 'INSERT INTO '.MAIN_DB_PREFIX.'log (fk_user, nom, prenom, quand, action_effectue, object_concerne) VALUES (';
        $sqlLog .= (int) $user->id.', "'.$db->escape($user->lastname).'", "'.$db->escape($user->firstname).'", NOW(), "'.$db->escape($actionEffectue).'", "Configuration conge")';
        $resLogConfig = $resConfig ? $db->query($sqlLog) : false;

        if ($resConfig && $resLogConfig) {
            $db->commit();
            setEventMessages('Le solde a ete recalcule depuis le '.$dateDebut->format('d/m/Y').' : '.$nouveauSolde.' jour(s).', null, 'mesgs');
        } else {
            $db->rollback();
            setEventMessages($db->lasterror(), null, 'errors');
        }
    } catch (Exception $e) {
        if (isset($db) && method_exists($db, 'rollback')) {
            $db->rollback();
        }
        setEventMessages($e->getMessage(), null, 'errors');
    }

    header('Location: '.$baseUrl);
    exit;
}

/* Paiement d'un nombre de jours. */
if ($action === 'payer_conge') {
    if (!$canWrite) {
        accessforbidden();
    }

    /* Compatibilite avec les versions de Dolibarr sans fonction checkToken(). */
    $postedToken = GETPOST('token', 'alphanohtml');
    $sessionToken = isset($_SESSION['newtoken']) ? (string) $_SESSION['newtoken'] : '';
    if (empty($postedToken) || empty($sessionToken) || !hash_equals($sessionToken, (string) $postedToken)) {
        accessforbidden('Jeton de securite invalide');
    }

    $joursPayes = (float) GETPOST('nombre_jours', 'alphanohtml');
    $joursPayes = round($joursPayes, 2);

    if ($joursPayes <= 0) {
        setEventMessages('Le nombre de jours doit etre superieur a zero.', null, 'errors');
        header('Location: '.$baseUrl);
        exit;
    }

    $db->begin();

    $sql = 'SELECT rowid, solde_jours';
    $sql .= ' FROM '.MAIN_DB_PREFIX.'salarie_conge_solde';
    $sql .= ' WHERE fk_salarie='.(int) $fk_salarie;
    $sql .= ' AND entity='.(int) $conf->entity;
    $sql .= ' FOR UPDATE';
    $resql = $db->query($sql);

    if (!$resql) {
        $db->rollback();
        setEventMessages($db->lasterror(), null, 'errors');
        header('Location: '.$baseUrl);
        exit;
    }

    $solde = $db->fetch_object($resql);
    $ancienSolde = $solde ? round((float) $solde->solde_jours, 2) : 0;

    if (!$solde) {
        $db->rollback();
        setEventMessages('Aucun solde de conge n\'est enregistre pour ce salarie.', null, 'errors');
    } elseif ($ancienSolde <= 0) {
        $db->rollback();
        setEventMessages('Le solde de conge est nul.', null, 'errors');
    } elseif ($joursPayes > $ancienSolde) {
        $db->rollback();
        setEventMessages('Le nombre de jours a payer ne peut pas depasser le solde disponible.', null, 'errors');
    } else {
        $nouveauSolde = round($ancienSolde - $joursPayes, 2);

        $sqlUpdate = 'UPDATE '.MAIN_DB_PREFIX.'salarie_conge_solde';
        $sqlUpdate .= ' SET solde_jours='.$nouveauSolde;
        $sqlUpdate .= ', fk_user_modif='.(int) $user->id;
        $sqlUpdate .= ', tms=NOW()';
        $sqlUpdate .= ' WHERE rowid='.(int) $solde->rowid;

        $resUpdate = $db->query($sqlUpdate);

        $sqlArchive = 'INSERT INTO '.MAIN_DB_PREFIX.'salarie_conge_paiement';
        $sqlArchive .= ' (fk_salarie, ancien_solde, jours_payes, nouveau_solde, date_paiement, fk_user, entity)';
        $sqlArchive .= ' VALUES (';
        $sqlArchive .= (int) $fk_salarie.', '.$ancienSolde.', '.$joursPayes.', '.$nouveauSolde;
        $sqlArchive .= ', NOW(), '.(int) $user->id.', '.(int) $conf->entity.')';

        $resArchive = $resUpdate ? $db->query($sqlArchive) : false;

        /* Enregistrement de la trace dans llx_log. */
        $resLog = false;
        if ($resUpdate && $resArchive) {
            $sqlAdmin = 'SELECT firstname, lastname FROM '.MAIN_DB_PREFIX.'user';
            $sqlAdmin .= ' WHERE rowid='.(int) $user->id;
            $resAdmin = $db->query($sqlAdmin);
            $objAdmin = $resAdmin ? $db->fetch_object($resAdmin) : null;

            $sqlSalarie = 'SELECT firstname, lastname FROM '.MAIN_DB_PREFIX.'user';
            $sqlSalarie .= ' WHERE rowid='.(int) $fk_user;
            $resSalarie = $db->query($sqlSalarie);
            $objSalarie = $resSalarie ? $db->fetch_object($resSalarie) : null;

            $sqlSociete = 'SELECT nom FROM '.MAIN_DB_PREFIX.'societe';
            $sqlSociete .= ' WHERE rowid='.(int) $id_societe;
            $resSociete = $db->query($sqlSociete);
            $objSociete = $resSociete ? $db->fetch_object($resSociete) : null;

            $nomSalarie = $objSalarie
                ? trim($objSalarie->lastname.' '.$objSalarie->firstname)
                : 'Nom non disponible';
            $nomSociete = $objSociete ? $objSociete->nom : 'societe #'.(int) $id_societe;

            $actionEffectue = 'Paiement de '.$joursPayes.' jour(s) de conge du salarie #'.(int) $fk_salarie;
            $actionEffectue .= ' - '.$nomSalarie;
            $actionEffectue .= ' - societe : '.$nomSociete;
            $actionEffectue .= '. Ancien solde : '.$ancienSolde.' jour(s), nouveau solde : '.$nouveauSolde.' jour(s)';

            $nomAdmin = $objAdmin ? $objAdmin->lastname : $user->lastname;
            $prenomAdmin = $objAdmin ? $objAdmin->firstname : $user->firstname;

            $sqlLog = 'INSERT INTO '.MAIN_DB_PREFIX.'log';
            $sqlLog .= ' (fk_user, nom, prenom, quand, action_effectue, object_concerne)';
            $sqlLog .= ' VALUES (';
            $sqlLog .= (int) $user->id;
            $sqlLog .= ', "'.$db->escape($nomAdmin).'"';
            $sqlLog .= ', "'.$db->escape($prenomAdmin).'"';
            $sqlLog .= ', NOW()';
            $sqlLog .= ', "'.$db->escape($actionEffectue).'"';
            $sqlLog .= ', "Paiement conge")';
            $resLog = $db->query($sqlLog);
        }

        if ($resUpdate && $resArchive && $resLog) {
            $db->commit();
            setEventMessages('Paiement de '.$joursPayes.' jour(s) enregistre. Nouveau solde : '.$nouveauSolde.' jour(s).', null, 'mesgs');
        } else {
            $db->rollback();
            setEventMessages($db->lasterror(), null, 'errors');
        }
    }

    header('Location: '.$baseUrl);
    exit;
}

/* Lecture du solde courant. */
$soldeJours = 0;
$sourceActuelle = '';
$dateReferenceActuelle = '';
$sqlSolde = 'SELECT solde_jours, source_reference, date_reference FROM '.MAIN_DB_PREFIX.'salarie_conge_solde';
$sqlSolde .= ' WHERE fk_salarie='.(int) $fk_salarie;
$sqlSolde .= ' AND entity='.(int) $conf->entity;
$resSolde = $db->query($sqlSolde);
if ($resSolde && ($objSolde = $db->fetch_object($resSolde))) {
    $soldeJours = round((float) $objSolde->solde_jours, 2);
    $sourceActuelle = !empty($objSolde->source_reference) ? $objSolde->source_reference : '';
    $dateReferenceActuelle = !empty($objSolde->date_reference) ? substr($objSolde->date_reference, 0, 10) : '';
}

$formToken = newToken();

llxHeader('', 'Solde conge | Salaire');

print load_fiche_titre('Solde conge', '', 'holiday');

$head = salaire_Head($fk_salarie, $fk_user, $id_societe, $id_convention);
print dol_get_fiche_head($head, 'conge', '', -1, '');

$objSoc = prepare_objet_entete($fk_salarie, $fk_user, $db, $id_societe, $id_convention);
entete_societe($objSoc, 'societe');
print '<hr>';

print '<style>
.conge-card{display:flex;align-items:center;justify-content:space-between;gap:24px;padding:26px 30px;margin:20px 0 28px;border:1px solid #d8dee8;border-radius:12px;background:#fff}
.conge-label{color:#596579;font-size:15px;margin-bottom:5px}.conge-value{font-size:32px;font-weight:700;color:#26354a}.conge-unit{font-size:16px;font-weight:500;color:#596579}
.conge-empty{padding:28px;text-align:center;color:#6b7280}.conge-modal::backdrop{background:rgba(15,23,42,.55)}
.conge-modal{width:min(460px,calc(100% - 32px));border:0;border-radius:12px;padding:0;box-shadow:0 20px 55px rgba(15,23,42,.25)}
.conge-modal-head{padding:20px 24px;border-bottom:1px solid #e5e7eb;font-size:20px;font-weight:700}.conge-modal-body{padding:22px 24px}.conge-modal-actions{display:flex;justify-content:flex-end;gap:10px;padding:16px 24px;border-top:1px solid #e5e7eb;background:#f8fafc}
.conge-modal input[type=number],.conge-modal input[type=date]{width:100%;box-sizing:border-box;margin-top:8px;padding:10px}.conge-help{margin-top:8px;color:#6b7280;font-size:13px}
.conge-buttons{display:flex;align-items:center;gap:10px;flex-wrap:wrap}.conge-choice{display:flex;align-items:flex-start;gap:10px;padding:12px 0;border-bottom:1px solid #edf0f4}.conge-choice:last-of-type{border-bottom:0}.conge-choice label{cursor:pointer;flex:1}.conge-choice small{display:block;color:#6b7280;margin-top:3px}
@media(max-width:600px){.conge-card{align-items:flex-start;flex-direction:column}.conge-card .button{width:100%;text-align:center}.conge-value{font-size:27px}}
</style>';

print '<div class="conge-card">';
print '<div><div class="conge-label">Solde de conge disponible</div>';
print '<div class="conge-value">'.price2num($soldeJours, 'MT').' <span class="conge-unit">jour(s)</span></div>';
if (!empty($dateReferenceActuelle)) {
    print '<div class="conge-help">Calcule depuis le '.dol_print_date($db->jdate($dateReferenceActuelle), 'day').'</div>';
}
print '</div><div class="conge-buttons">';

if ($canWrite && $soldeJours > 0) {
    print '<button type="button" class="button button-save" id="openCongeModal">Payer des jours</button>';
} elseif ($canWrite) {
    print '<button type="button" class="button" disabled title="Aucun jour disponible">Payer des jours</button>';
}
if ($canWrite) {
    print '<button type="button" class="button" id="openConfigModal">Configurer</button>';
}
print '</div></div>';

print '<h3>Archives des paiements</h3>';
print '<div class="div-table-responsive">';
print '<table class="tagtable liste" style="width:100%">';
print '<tr class="liste_titre">';
print '<td>Date de paiement</td><td class="right">Ancien solde</td><td class="right">Jours payes</td><td class="right">Nouveau solde</td><td>Effectue par</td>';
print '</tr>';

$sqlArchive = 'SELECT p.date_paiement, p.ancien_solde, p.jours_payes, p.nouveau_solde,';
$sqlArchive .= ' p.fk_user, u.firstname, u.lastname';
$sqlArchive .= ' FROM '.MAIN_DB_PREFIX.'salarie_conge_paiement AS p';
$sqlArchive .= ' LEFT JOIN '.MAIN_DB_PREFIX.'user AS u ON u.rowid=p.fk_user';
$sqlArchive .= ' WHERE p.fk_salarie='.(int) $fk_salarie;
$sqlArchive .= ' AND p.entity='.(int) $conf->entity;
$sqlArchive .= ' ORDER BY p.date_paiement DESC, p.rowid DESC';
$resArchive = $db->query($sqlArchive);
$nbArchives = $resArchive ? $db->num_rows($resArchive) : 0;

if ($resArchive && $nbArchives > 0) {
    while ($archive = $db->fetch_object($resArchive)) {
        $nomAdmin = trim($archive->firstname.' '.$archive->lastname);
        print '<tr class="oddeven">';
        print '<td>'.dol_print_date($db->jdate($archive->date_paiement), 'dayhour').'</td>';
        print '<td class="right">'.price2num($archive->ancien_solde, 'MT').' jour(s)</td>';
        print '<td class="right"><strong>'.price2num($archive->jours_payes, 'MT').' jour(s)</strong></td>';
        print '<td class="right">'.price2num($archive->nouveau_solde, 'MT').' jour(s)</td>';
        print '<td>'.dol_escape_htmltag($nomAdmin ?: 'Utilisateur #'.$archive->fk_user).'</td>';
        print '</tr>';
    }
} else {
    print '<tr><td colspan="5" class="conge-empty">Aucun paiement de conge archive pour ce salarie.</td></tr>';
}
print '</table></div>';

if ($canWrite && $soldeJours > 0) {
    print '<dialog id="congeModal" class="conge-modal">';
    print '<form method="POST" action="'.dol_escape_htmltag($baseUrl).'">';
    print '<input type="hidden" name="token" value="'.$formToken.'">';
    print '<input type="hidden" name="action" value="payer_conge">';
    print '<div class="conge-modal-head">Payer des jours de conge</div>';
    print '<div class="conge-modal-body">';
    print '<div>Solde disponible : <strong>'.price2num($soldeJours, 'MT').' jour(s)</strong></div>';
    print '<label for="nombre_jours" style="display:block;margin-top:18px;font-weight:600">Nombre de jours a payer</label>';
    print '<input type="number" name="nombre_jours" id="nombre_jours" min="0.01" max="'.$soldeJours.'" step="0.01" required autofocus>';
    print '<div class="conge-help">Le nombre saisi ne doit pas depasser le solde disponible.</div>';
    print '</div>';
    print '<div class="conge-modal-actions"><button type="button" class="button" id="closeCongeModal">Annuler</button><button type="submit" class="button button-save">Payer</button></div>';
    print '</form></dialog>';

}

if ($canWrite) {
    $choixActuel = !empty($sourceActuelle)
        ? $sourceActuelle
        : (!empty($dateAnciennete) ? 'anciennete' : (!empty($dateEmployment) ? 'employment' : 'personnalisee'));
    $datePersoValue = ($sourceActuelle === 'personnalisee') ? $dateReferenceActuelle : '';

    print '<dialog id="configCongeModal" class="conge-modal">';
    print '<form method="POST" action="'.dol_escape_htmltag($baseUrl).'">';
    print '<input type="hidden" name="token" value="'.$formToken.'">';
    print '<input type="hidden" name="action" value="configurer_conge">';
    print '<div class="conge-modal-head">Configurer le solde de conge</div>';
    print '<div class="conge-modal-body">';
    print '<div class="conge-help" style="margin:0 0 12px">Choisissez la date de depart du calcul. Le solde sera recalcule a raison de 2,5 jours par mois complet.</div>';

    print '<div class="conge-choice"><input type="radio" name="source_reference" id="source_anciennete" value="anciennete"'.($choixActuel === 'anciennete' ? ' checked' : '').(empty($dateAnciennete) ? ' disabled' : '').'>';
    print '<label for="source_anciennete"><strong>Date d\'anciennete</strong><small>'.(!empty($dateAnciennete) ? dol_print_date($db->jdate($dateAnciennete), 'day') : 'Non renseignee').'</small></label></div>';

    print '<div class="conge-choice"><input type="radio" name="source_reference" id="source_employment" value="employment"'.($choixActuel === 'employment' ? ' checked' : '').(empty($dateEmployment) ? ' disabled' : '').'>';
    print '<label for="source_employment"><strong>Date d\'embauche (dateemployment)</strong><small>'.(!empty($dateEmployment) ? dol_print_date($db->jdate($dateEmployment), 'day') : 'Non renseignee').'</small></label></div>';

    print '<div class="conge-choice"><input type="radio" name="source_reference" id="source_personnalisee" value="personnalisee"'.($choixActuel === 'personnalisee' ? ' checked' : '').'>';
    print '<label for="source_personnalisee"><strong>Saisir une autre date</strong>';
    print '<input type="date" name="date_personnalisee" id="date_personnalisee" max="'.date('Y-m-d').'" value="'.dol_escape_htmltag($datePersoValue).'"'.($choixActuel !== 'personnalisee' ? ' disabled' : '').'></label></div>';
    print '</div>';
    print '<div class="conge-modal-actions"><button type="button" class="button" id="closeConfigModal">Annuler</button><button type="submit" class="button button-save">Enregistrer et recalculer</button></div>';
    print '</form></dialog>';
}

print '<script>
(function () {
    function bindDialog(modalId, openId, closeId) {
        var modal = document.getElementById(modalId);
        var openButton = document.getElementById(openId);
        var closeButton = document.getElementById(closeId);
        if (!modal || !openButton || !closeButton) return;
        openButton.addEventListener("click", function () { modal.showModal(); });
        closeButton.addEventListener("click", function () { modal.close(); });
        modal.addEventListener("click", function (event) { if (event.target === modal) modal.close(); });
    }
    bindDialog("congeModal", "openCongeModal", "closeCongeModal");
    bindDialog("configCongeModal", "openConfigModal", "closeConfigModal");

    var radios = document.querySelectorAll("input[name=source_reference]");
    var customDate = document.getElementById("date_personnalisee");
    Array.prototype.forEach.call(radios, function (radio) {
        radio.addEventListener("change", function () {
            if (!customDate) return;
            customDate.disabled = this.value !== "personnalisee";
            customDate.required = this.value === "personnalisee";
            if (this.value === "personnalisee") customDate.focus();
        });
    });
}());
</script>';

print dol_get_fiche_end();
llxFooter();
$db->close();