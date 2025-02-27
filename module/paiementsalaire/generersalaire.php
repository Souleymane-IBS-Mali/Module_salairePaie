<?php
require '../main.inc.php';

llxHeader("", "Paiement | Salaire");
//Titre 
print load_fiche_titre($langs->trans("Création de nouveau Prime"), '', '');
print '<hr>';

$id_salarie = GETPOST("id_salarie", 'int');
$sql = "SELECT rowid, nom, client FROM ".MAIN_DB_PREFIX."societe";
$sql .= " Where rowid=".$id_salarie;

$result = $db->query($sql);
$obj1 = $db->fetch_object($result);
print '<table>';
print '<tr align"center"><td class="pair" style=" padding: 20px;">Matricule &emsp;&emsp;</td>';
print '<td class="pair" style="padding: 20px;">'.$obj1->rowid.'</td><td>&emsp;&emsp;</td>';
print '<td class="impair" style="padding: 20px;" >Catégorie &emsp;&emsp;</td><td class="impair" style="width: 30%;"><select>';
$allcat = array();
$allcatRowid = array();

$covSql = "SELECT * FROM ".MAIN_DB_PREFIX."dcategories";
$result = $db->query($covSql);//= $db->query($covSql);

if($result){
	$i = 0;
	$num = $db->num_rows($result);
	while ($i < $num){
		$obj = $db->fetch_object($result);
		if ($obj)
		{
			print '<option value="'.$obj->rowid.'">'.$obj->code_categorie.'</option>';
			
			
			

		}
		$i ++;
	}
}
print '</select></td></tr>';






print '<tr><td class="pair" style=" padding: 20px;">Nom&emsp;&emsp;</td>';
print '<td class="pair" style="padding: 20px;">'.$obj1->nom.'</td><td>&emsp;&emsp;</td>';

print '<td class="impair" style="padding: 20px;">Primes &emsp;&emsp;</td>';
$allcat = array();
$allcatRowid = array();

$allprime = array();
$allprimeRowid = array();

$covSql = "SELECT * FROM ".MAIN_DB_PREFIX."primes";
$result = $db->query($covSql);//= $db->query($covSql);

if($result){
	$i = 0;
	$num = $db->num_rows($result);
	while ($i < $num){
		$obj = $db->fetch_object($result);
		if ($obj)
		{
			$allprimeRowid[$i] = $obj->rowid;
			$allprime[$i] =  $obj->nom_prime;
			
			
			

		}
		$i ++;
	}
}
$allprime = array_combine($allprimeRowid, $allprime);
$monform = new Form($db);
print '<td class="impair" style="padding: 20px;">';
print $monform->multiselectarray('primes', $allprime, GETPOST('primes', 'array'), null, 0, 'quatrevingtpercent widthcentpercentminusx', 0, 0);
print '</td></tr>';

print '<tr><td class="pair" style=" padding: 20px;">Prenom&emsp;&emsp;</td>';
print '<td class="pair" style="padding: 20px;">'.$obj1->prenom.'</td><td>&emsp;&emsp;</td>';

print '<td style=" padding: 20px;">Indemnités &emsp;&emsp;</td>';
$allindemnite = array();
	$allindemniteRowid = array();

	$covSql = "SELECT * FROM ".MAIN_DB_PREFIX."indemnites";
	$result = $db->query($covSql);//= $db->query($covSql);

	if($result){
		$i = 0;
		$num = $db->num_rows($result);
		while ($i < $num){
			$obj = $db->fetch_object($result);
			if ($obj)
			{
				$allindemniteRowid[$i] = $obj->rowid;
				$allindemnite[$i] =  $obj->nom_indemnite;
				
				
				

			}
			$i ++;
		}
	}
	$allindemnite = array_combine($allindemniteRowid, $allindemnite);
	$monform = new Form($db);
print '<td style=" padding: 20px;">';
print $monform->multiselectarray('indemnites', $allindemnite, GETPOST('indemnites', 'array'), null, 0, 'quatrevingtpercent widthcentpercentminusx', 0, 0);

print '<tr><td class="pair" style=" padding: 20px;">Statut Familialle&emsp;&emsp;</td>';
print '<td class="pair" style="padding: 20px;">';
print '<select>
<option>Marié</option>
<option>Divorcé</option>
<option>Célibataire</option>
</select>';

print '</td><td>&emsp;&emsp;</td>';

print '<td style=" padding: 20px;">Nombre enfants &emsp;&emsp;</td>';
print '<td style=" padding: 20px;"><input type="number" min="0"/>';




print '<tr><td class="pair" style=" padding: 20px;">Statut Familialle&emsp;&emsp;</td>';
print '<td class="pair" style="padding: 20px;">';
print '<select>
<option>Marié</option>
<option>Divorcé</option>
<option>Célibataire</option>
</select>';

print '</td><td>&emsp;&emsp;</td>';

print '<td style=" padding: 20px;">Nombre enfants &emsp;&emsp;</td>';
print '<td style=" padding: 20px;"><input type="number" min="0"/>';

print '<tr><td class="pair" style=" padding: 20px;">Téléphone&emsp;&emsp;</td>';
print '<td class="pair" style="padding: 20px;"></td><td>&emsp;&emsp;</td>';

print '<td style=" padding: 20px;">Fax</td>';
print '<td style=" padding: 20px;"><input type="number" min="0"/>';

print '<tr><td class="pair" style=" padding: 20px;">Email&emsp;&emsp;</td>';
print '<td class="pair" style="padding: 20px;"></td><td>&emsp;&emsp;</td>';

print '<td style=" padding: 20px;">Site Web</td>';
print '<td style=" padding: 20px;"><input type="number" min="0"/>';


print '</td></tr></table>';
$db->free();