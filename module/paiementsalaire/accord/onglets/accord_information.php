<?php
require '../../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/custom/paiementsalaire/lib/paiementsalaire.lib.php';




llxHeader("", "Paiement | Salaire");
//Titre 
$id_convention = GETPOST("id_convention","int");
$id_accord = GETPOST("id_accord","int");

if(!$id_convention){
	$covSql = "SELECT fk_societe FROM ".MAIN_DB_PREFIX."accord_etablissement WHERE rowid=".$id_accord;
	$result = $db->query($covSql);//= $db->query($covSql);
	$obj = $db->fetch_object($result);

	$sql = "SELECT conv FROM ".MAIN_DB_PREFIX."societe_extrafields WHERE fk_object=".$obj->fk_societe." AND grp=1";
	$result = $db->query($sql);
	$obj = $db->fetch_object($result);

	$id_convention = $obj->conv;

}
$covSql = "SELECT * FROM ".MAIN_DB_PREFIX."accord_etablissement WHERE rowid=".$id_accord;
$result = $db->query($covSql);//= $db->query($covSql);
$nom_accord = "";
if($result){
	$obj = $db->fetch_object($result);
	$nom_accord = "<b><mark>".$obj->nom."</mark></b>";
}

$titre = "Information sur l'accord ".$nom_accord;
print load_fiche_titre($langs->trans($titre), '', '');

$covSql = "SELECT * FROM ".MAIN_DB_PREFIX."convention WHERE rowid=".$id_convention;
$result = $db->query($covSql);
if($result){
	$obj_conv = $db->fetch_object($result);
	if(!empty($obj_conv)){
$head = paiementsalaireAccordHead($id_convention, $id_accord);
print dol_get_fiche_head($head, 'information', "", -1, '');


print "<table>";

print '<tr ><td style="width: 150px; padding-top: 10px;" class="fieldrequired"><label>Nom</label></td><td style="width: 150px; padding-top: 10px" id="nom" ><label>'.$obj->nom.'</label></td></tr>';
print '<tr ><td style="width: 150px; padding-top: 10px;" class="fieldrequired"><label>Commentaire</label></td><td style="width: 150px; padding-top: 10px"><textarea name="commentaire" wrap="soft" disabled cols="50" rows="3">'.$obj->commentaire.'</textarea></td></tr>';
	print "<tr><td td style='width: 150px; padding-top: 10px;' class='fieldrequired'>Fichier</td><td><a title='Télécharger le fichier de la convention ".$obj->nom."' target='_blank' href='".$_SERVER["PHP_SELF"]."/../../../".$obj->fichier_accord."'>".img_picto('', 'title_document', 'class="paddingright pictofixedwidth valignmiddle"')."</a></td></tr>";

print "</table>";
}else{
	print "<h2> La convention mère n'existe pas</h2>";
}
}