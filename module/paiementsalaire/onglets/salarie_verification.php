<?php
require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/custom/paiementsalaire/lib/paiementsalaire.lib.php';

llxHeader("", "Paiement | Salaire");

// Titre
print load_fiche_titre($langs->trans("Verification des éléments de salaire de ce salarié"), '', '');

$fk_user = GETPOST("id", "int");
$id_societe = GETPOST("id_societe", "int");
$fk_salarie = GETPOST("fk_salarie", "int");
$id_convention = GETPOST("id_convention", "int");
$action = GETPOST("action", "alpha");

$head = salaire_Head($fk_salarie, $fk_user, $id_societe, $id_convention);
print dol_get_fiche_head($head, 'verification', "", -1, '');

if ($user->id != 1 && $user->id != $fk_user && empty($user->rights->paiementsalaire->salarie->read)) {
    print "<h2> Vous n'avez pas ce droit </h2>";
} else {
    if (empty($fk_salarie)) {
        print "Page non Disponible";
    } else {
        $obj_soc = prepare_objet_entete($fk_salarie, $fk_user, $db, $id_societe, $id_convention);
        entete_societe($obj_soc, 'societe');
        print "<hr>";

        $tab_message = array();
        $tab_message_statut = array();
        $tab_aide = array();

        // Objet salarié
        $obj_salarie = null;
        $sql_sal = "SELECT * FROM ".MAIN_DB_PREFIX."salarie WHERE rowid=".(int) $fk_salarie;
        $result_sal = $db->query($sql_sal);
        if ($result_sal) {
            $obj_salarie = $db->fetch_object($result_sal);
        }

        if (!$obj_salarie) {
            $tab_message[] = "Salarié";
            $tab_message_statut[] = "Non OK";
            $tab_aide[] = "Salarié introuvable";
        } else {
            // ------------------------------------------------------------------
            // Vérification Catégorie / Échelon / Salaire de base
            // ------------------------------------------------------------------
            $categorie_ok = false;
            $echelon_ok = false;
            $grille_ok = false;
            $salaire_base_ok = false;
            $salaire_base = 0;

            if (!empty($obj_salarie->fk_categorie)) {
                $categorie_ok = true;
            }

            if ($obj_salarie->fk_echelon !== null && $obj_salarie->fk_echelon !== '') {
                $echelon_ok = true;
            }

            if ($categorie_ok) {
                $tab_message[] = "Catégorie";
                $tab_message_statut[] = "OK";
                $tab_aide[] = "Aucun problème";
            } else {
                $tab_message[] = "Catégorie";
                $tab_message_statut[] = "Non OK";
                $tab_aide[] = "Veuillez affecter une catégorie à ce salarié";
            }

            if ($echelon_ok) {
                $tab_message[] = "Échelon";
                $tab_message_statut[] = "OK";
                $tab_aide[] = "Aucun problème";
            } else {
                $tab_message[] = "Échelon";
                $tab_message_statut[] = "Non OK";
                $tab_aide[] = "Veuillez affecter un échelon à ce salarié";
            }

            $obj_grille = null;
            if (!empty($id_convention)) {
                $grilleSql = "SELECT rowid FROM ".MAIN_DB_PREFIX."grille WHERE active=1 AND fk_convention=".(int) $id_convention;
                $grilleResult = $db->query($grilleSql);
                if ($grilleResult) {
                    $obj_grille = $db->fetch_object($grilleResult);
                }
            }

            if ($obj_grille && !empty($obj_grille->rowid)) {
                $grille_ok = true;
            }

            if ($categorie_ok && $grille_ok) {
                $salBaseSql = "SELECT salaire_base FROM ".MAIN_DB_PREFIX."grille_categorie_echelon_salaire_base";
                $salBaseSql .= " WHERE fk_grille=".(int) $obj_grille->rowid;
                $salBaseSql .= " AND fk_categorie=".(int) $obj_salarie->fk_categorie;
                $salBaseSql .= " AND fk_echelon=".(int) $obj_salarie->fk_echelon;

                $salBaseResult = $db->query($salBaseSql);
                if ($salBaseResult) {
                    $objSalBase = $db->fetch_object($salBaseResult);
                    if ($objSalBase && $objSalBase->salaire_base !== null && $objSalBase->salaire_base !== '' && (float) $objSalBase->salaire_base > 0) {
                        $salaire_base_ok = true;
                        $salaire_base = (float) $objSalBase->salaire_base;
                    }
                }
            }

            if ($salaire_base_ok) {
                $tab_message[] = "Salaire de Base";
                $tab_message_statut[] = "OK";
                $tab_aide[] = "Aucun problème";
            } else {
                $tab_message[] = "Salaire de Base";
                $tab_message_statut[] = "Non OK";

                if (!$grille_ok) {
                    $tab_aide[] = "Aucune grille active trouvée pour cette convention";
                } elseif (!$categorie_ok) {
                    $tab_aide[] = "Impossible de trouver le salaire de base : catégorie manquante";
                } elseif (!$echelon_ok) {
                    $tab_aide[] = "Impossible de trouver le salaire de base : échelon manquant";
                } else {
                    $tab_aide[] = "Aucun salaire de base trouvé pour cette catégorie et cet échelon dans la grille active";
                }
            }

            // Sursalaire
            if ($obj_salarie->sursalaire !== null) {
                $tab_message[] = "Sursalaire";
                $tab_message_statut[] = "OK";
                $tab_aide[] = "Aucun problème";
            } else {
                $tab_message[] = "Sursalaire";
                $tab_message_statut[] = "Non OK";
                $tab_aide[] = "Veuillez définir un sursalaire pour ce salarié";
            }

            // Contrat
            $annee = (int) date("Y");
            $mois = (int) date("m");
            $jour = (int) date("d");

            $contrat_ok = false;
            $contrat_expire = false;

            $sql_contrat = "SELECT rowid FROM ".MAIN_DB_PREFIX."salarie_contrat WHERE fk_salarie=".(int) $obj_salarie->rowid." AND active=1 AND fk_type_contrat != 2";
            $sql_contrat .= " AND (YEAR(date_fin)>".$annee;
            $sql_contrat .= " OR ((YEAR(date_fin) = ".$annee." AND MONTH(date_fin) > ".$mois.") OR (YEAR(date_fin) = ".$annee." AND MONTH(date_fin) = ".$mois." AND DAY(date_fin) >= ".$jour.")))";
            $res_contrat = $db->query($sql_contrat);
            if ($res_contrat && $db->num_rows($res_contrat) > 0) {
                $contrat_ok = true;
            } else {
                $sql_contrat = "SELECT rowid FROM ".MAIN_DB_PREFIX."salarie_contrat WHERE fk_salarie=".(int) $obj_salarie->rowid." AND active=1 AND fk_type_contrat = 2";
                $res_contrat = $db->query($sql_contrat);
                if ($res_contrat && $db->num_rows($res_contrat) > 0) {
                    $contrat_ok = true;
                } else {
                    $sql_contrat = "SELECT rowid FROM ".MAIN_DB_PREFIX."salarie_contrat WHERE fk_salarie=".(int) $obj_salarie->rowid." AND active=1";
                    $res_contrat = $db->query($sql_contrat);
                    if ($res_contrat && $db->num_rows($res_contrat) > 0) {
                        $contrat_expire = true;
                    }
                }
            }

            if ($contrat_ok) {
                $tab_message[] = "Contrat";
                $tab_message_statut[] = "OK";
                $tab_aide[] = "Aucun problème";
            } elseif ($contrat_expire) {
                $tab_message[] = "Contrat Expiré";
                $tab_message_statut[] = "Non OK";
                $tab_aide[] = "Le contrat de ce salarié a expiré. Veuillez renouveler son contrat";
            } else {
                $tab_message[] = "Contrat";
                $tab_message_statut[] = "Non OK";
                $tab_aide[] = "Ce salarié n'a pas de contrat";
            }

            // Objet utilisateur
            $obj_user = null;
            $sql = "SELECT * FROM ".MAIN_DB_PREFIX."user WHERE rowid=".(int) $obj_salarie->fk_user;
            $result_user = $db->query($sql);
            if ($result_user) {
                $obj_user = $db->fetch_object($result_user);
            }

            if (!$obj_salarie->date_anciennete && (!$obj_user || !$obj_user->dateemployment)) {
                $tab_message[] = "Date d'embauche pour calculer l'anciennété";
                $tab_message_statut[] = "Non OK";
                $tab_aide[] = "Veuillez définir une date d'entrée pour ce salarié";
            } else {
                $tab_message[] = "Date d'embauche pour calculer l'anciennété";
                $tab_message_statut[] = "OK";
                $tab_aide[] = "Aucun problème";
            }

            if (!$obj_user || !$obj_user->job) {
                $tab_message[] = "Poste/Fonction";
                $tab_message_statut[] = "Non OK";
                $tab_aide[] = "Veuillez donner un poste à ce salarié";
            } else {
                $tab_message[] = "Poste/Fonction";
                $tab_message_statut[] = "OK";
                $tab_aide[] = "Aucun problème";
            }

            if ($obj_salarie->situation_familiale) {
                $tab_message[] = "Statut Matrimoniale";
                $tab_message_statut[] = "OK";
                $tab_aide[] = "Aucun problème";
            } else {
                $tab_message[] = "Statut Matrimoniale";
                $tab_message_statut[] = "Non OK";
                $tab_aide[] = "Veuillez définir si ce salarié est : Célibataire, Marié ou Divorcé";
            }

            if ($obj_salarie->nombre_enfant !== null) {
                $tab_message[] = "Nombre enfant";
                $tab_message_statut[] = "OK";
                $tab_aide[] = "Aucun problème";
            } else {
                $tab_message[] = "Nombre enfant";
                $tab_message_statut[] = "Non OK";
                $tab_aide[] = "Veuillez définir le nombre d'enfant de ce salarié";
            }

            if ($obj_salarie->nombre_enfant_hand !== null) {
                $tab_message[] = "Nombre enfant Handicapé";
                $tab_message_statut[] = "OK";
                $tab_aide[] = "Aucun problème";
            } else {
                $tab_message[] = "Nombre enfant Handicapé";
                $tab_message_statut[] = "Non OK";
                $tab_aide[] = "Veuillez définir le nombre d'enfant Handicapé de ce salarié";
            }
        }

        print "<h2>Verification des informations</h2>";
        print "<table class='tagtable liste'>";
        print "<tr class='liste_titre'><td>Elements du salaire</td><td align='center'>Statut</td>";

        for ($i = 0; $i < count($tab_message); $i++) {
            $class = "pair";
            if ($i % 2 == 0) {
                $class = "impair";
            }

            print '<tr class='.$class.'><td>';
            if ($tab_message_statut[$i] == "OK") {
                print $tab_message[$i]."</td><td align='center'>".img_picto($tab_aide[$i], "tick");
            } else {
                print $tab_message[$i]."</td><td align='center'>".img_picto($tab_aide[$i], "error");
            }
            print '</td></tr>';
        }

        print "</table>";
    }

    $db->close();
}
