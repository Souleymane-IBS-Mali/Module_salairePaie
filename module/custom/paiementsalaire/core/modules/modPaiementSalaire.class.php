<?php
/* Copyright (C) 2004-2018  Laurent Destailleur     <eldy@users.sourceforge.net>
 * Copyright (C) 2018-2019  Nicolas ZABOURI         <info@inovea-conseil.com>
 * Copyright (C) 2019-2020  Frédéric France         <frederic.france@netlogic.fr>
 * Copyright (C) 2022 SuperAdmin
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
 * 	\defgroup   paiementsalaire     Module PaiementSalaire
 *  \brief      PaiementSalaire module descriptor.
 *
 *  \file       htdocs/paiementsalaire/core/modules/modPaiementSalaire.class.php
 *  \ingroup    paiementsalaire
 *  \brief      Description and activation file for module PaiementSalaire
 */
include_once DOL_DOCUMENT_ROOT.'/core/modules/DolibarrModules.class.php';


/**
 *  Description and activation class for module PaiementSalaire
 */
class modPaiementSalaire extends DolibarrModules
{
	/**
	 * Constructor. Define names, constants, directories, boxes, permissions
	 *
	 * @param DoliDB $db Database handler
	 */
	public function __construct($db)
	{
		global $langs, $conf;
		$this->db = $db;
		// Id for module (must be unique).
		// Use here a free id (See in Home -> System information -> Dolibarr for list of used modules id).
		$this->numero = 500000; // TODO Go on page https://wiki.dolibarr.org/index.php/List_of_modules_id to reserve an id number for your module

		// Key text used to identify module (for permissions, menus, etc...)
		$this->rights_class = 'paiementsalaire';

		// Family can be 'base' (core modules),'crm','financial','hr','projects','products','ecm','technic' (transverse modules),'interface' (link with external tools),'other','...'
		// It is used to group modules by family in module setup page
		$this->family = "other";

		// Module position in the family on 2 digits ('01', '10', '20', ...)
		$this->module_position = '90';

		// Gives the possibility for the module, to provide his own family info and position of this family (Overwrite $this->family and $this->module_position. Avoid this)
		//$this->familyinfo = array('myownfamily' => array('position' => '01', 'label' => $langs->trans("MyOwnFamily")));
		// Module label (no space allowed), used if translation string 'ModulePaiementSalaireName' not found (PaiementSalaire is name of module).
		$this->name = preg_replace('/^mod/i', '', get_class($this));

		// Module description, used if translation string 'ModulePaiementSalaireDesc' not found (PaiementSalaire is name of module).
		$this->description = "Module de Paiement de Salaire";
		// Used only if file README.md and README-LL.md not found.
		$this->descriptionlong = "Ce module permet à la GRH la gestion du salaire des employés";

		// Author
		$this->editor_name = 'Internet Business Services IBS Mali';
		$this->editor_url = 'https://ibs-mali.com/';

		// Possible values for version are: 'development', 'experimental', 'dolibarr', 'dolibarr_deprecated' or a version string like 'x.y.z'
		$this->version = '1.0';
		// Url to the file with your last numberversion of this module
		//$this->url_last_version = 'http://www.example.com/versionmodule.txt';

		// Key used in llx_const table to save module status enabled/disabled (where PAIEMENTSALAIRE is value of property name of module in uppercase)
		$this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);

		// Name of image file used for this module.
		// If file is in theme/yourtheme/img directory under name object_pictovalue.png, use this->picto='pictovalue'
		// If file is in module/img directory under name object_pictovalue.png, use this->picto='pictovalue@module'
		// To use a supported fa-xxx css style of font awesome, use this->picto='xxx'
		$this->picto = 'salairepaie';

		// Define some features supported by module (triggers, login, substitutions, menus, css, etc...)
		$this->module_parts = array(
			// Set this to 1 if module has its own trigger directory (core/triggers)
			'triggers' => 0,
			// Set this to 1 if module has its own login method file (core/login)
			'login' => 0,
			// Set this to 1 if module has its own substitution function file (core/substitutions)
			'substitutions' => 0,
			// Set this to 1 if module has its own menus handler directory (core/menus)
			'menus' => 0,
			// Set this to 1 if module overwrite template dir (core/tpl)
			'tpl' => 0,
			// Set this to 1 if module has its own barcode directory (core/modules/barcode)
			'barcode' => 0,
			// Set this to 1 if module has its own models directory (core/modules/xxx)
			'models' => 0,
			// Set this to 1 if module has its own printing directory (core/modules/printing)
			'printing' => 0,
			// Set this to 1 if module has its own theme directory (theme)
			'theme' => 0,
			// Set this to relative path of css file if module has its own css file
			'css' => array(
				//    '/paiementsalaire/css/paiementsalaire.css.php',
			),
			// Set this to relative path of js file if module must load a js on all pages
			'js' => array(
				//   '/paiementsalaire/js/paiementsalaire.js.php',
			),
			// Set here all hooks context managed by module. To find available hook context, make a "grep -r '>initHooks(' *" on source code. You can also set hook context to 'all'
			'hooks' => array(
				//   'data' => array(
				//       'hookcontext1',
				//       'hookcontext2',
				//   ),
				//   'entity' => '0',
			),
			// Set this to 1 if features of module are opened to external users
			'moduleforexternal' => 0,
		);

		// Data directories to create when module is enabled.
		// Example: this->dirs = array("/paiementsalaire/temp","/paiementsalaire/subdir");
		$this->dirs = array("/paiementsalaire/temp");

		// Config pages. Put here list of php page, stored into paiementsalaire/admin directory, to use to setup module.
		$this->config_page_url = array("setup.php@paiementsalaire");

		// Dependencies
		// A condition to hide module
		$this->hidden = false;
		// List of module class names as string that must be enabled if this module is enabled. Example: array('always1'=>'modModuleToEnable1','always2'=>'modModuleToEnable2', 'FR1'=>'modModuleToEnableFR'...)
		$this->depends = array();
		$this->requiredby = array(); // List of module class names as string to disable if this one is disabled. Example: array('modModuleToDisable1', ...)
		$this->conflictwith = array(); // List of module class names as string this module is in conflict with. Example: array('modModuleToDisable1', ...)

		// The language file dedicated to your module
		$this->langfiles = array("paiementsalaire@paiementsalaire");

		// Prerequies
		$this->phpmin = array(5, 6); // Minimum version of PHP required by module
		$this->need_dolibarr_version = array(11, -3); // Minimum version of Dolibarr required by module

		// Messages at activation
		$this->warnings_activation = array(); // Warning to show when we activate module. array('always'='text') or array('FR'='textfr','MX'='textmx'...)
		$this->warnings_activation_ext = array(); // Warning to show when we activate an external module. array('always'='text') or array('FR'='textfr','MX'='textmx'...)
		//$this->automatic_activation = array('FR'=>'PaiementSalaireWasAutomaticallyActivatedBecauseOfYourCountryChoice');
		//$this->always_enabled = true;								// If true, can't be disabled

		// Constants
		// List of particular constants to add when module is enabled (key, 'chaine', value, desc, visible, 'current' or 'allentities', deleteonunactive)
		// Example: $this->const=array(1 => array('PAIEMENTSALAIRE_MYNEWCONST1', 'chaine', 'myvalue', 'This is a constant to add', 1),
		//                             2 => array('PAIEMENTSALAIRE_MYNEWCONST2', 'chaine', 'myvalue', 'This is another constant to add', 0, 'current', 1)
		// );
		$this->const = array();

		// Some keys to add into the overwriting translation tables
		/*$this->overwrite_translation = array(
			'en_US:ParentCompany'=>'Parent company or reseller',
			'fr_FR:ParentCompany'=>'Maison mère ou revendeur'
		)*/

		if (!isset($conf->paiementsalaire) || !isset($conf->paiementsalaire->enabled)) {
			$conf->paiementsalaire = new stdClass();
			$conf->paiementsalaire->enabled = 0;
		}

		//Interraction avec les modules User et Tiers
		// Array to add new pages in new tabs
		$this->tabs = array();
		// Example:
		$this->tabs[] = array('data'=>'user:+Salaire|paie:Salaire | Paie:mylangfile@paiementsalaire:1:/paiementsalaire/onglets/salarie_information.php?id=__ID__&mainmenu=paiementsalaire&leftmenu=salarie');
		$this->tabs[] = array('data'=>'thirdparty:+Salaire|paie:Salaire | Paie:mylangfile@paiementsalaire:1:/paiementsalaire/liste_societe.php?id=__ID__&mainmenu=paiementsalaire&leftmenu=societe');

		//$this->tabs[] = array('data'=>'product:+tabname1:m2:mylangfile@paiementsalaire:1:/paiementsalaire/listeconvention.php?id=__ID__');  					// To add a new tab identified by code tabname1
		// $this->tabs[] = array('data'=>'objecttype:+tabname2:SUBSTITUTION_Title2:mylangfile@paiementsalaire:$user->rights->othermodule->read:/paiementsalaire/mynewtab2.php?id=__ID__',  	// To add another new tab identified by code tabname2. Label will be result of calling all substitution functions on 'Title2' key.
		// $this->tabs[] = array('data'=>'objecttype:-tabname:NU:conditiontoremove');                                                     										// To remove an existing tab identified by code tabname
		//
		// Where objecttype can be
		// 'categories_x'	  to add a tab in category view (replace 'x' by type of category (0=product, 1=supplier, 2=customer, 3=member)
		// 'contact'          to add a tab in contact view
		// 'contract'         to add a tab in contract view
		// 'group'            to add a tab in group view
		// 'intervention'     to add a tab in intervention view
		// 'invoice'          to add a tab in customer invoice view
		// 'invoice_supplier' to add a tab in supplier invoice view
		// 'member'           to add a tab in fundation member view
		// 'opensurveypoll'	  to add a tab in opensurvey poll view
		// 'order'            to add a tab in customer order view
		// 'order_supplier'   to add a tab in supplier order view
		// 'payment'		  to add a tab in payment view
		// 'payment_supplier' to add a tab in supplier payment view
		// 'product'          to add a tab in product view
		// 'propal'           to add a tab in propal view
		// 'project'          to add a tab in project view
		// 'stock'            to add a tab in stock view
		// 'thirdparty'       to add a tab in third party view
		// 'user'             to add a tab in user view

		// Dictionaries
		//$this->dictionaries = array();
		// Example:
		$this->dictionaries=array(
			/*'langs'=>'paiementsalaire@paiementsalaire',
			// List of tables we want to see into dictonnary editor
			'tabname'=>array(MAIN_DB_PREFIX."c_secteur_activite", MAIN_DB_PREFIX."c_convention", MAIN_DB_PREFIX."c_accord_etablissement", MAIN_DB_PREFIX."c_categorie", MAIN_DB_PREFIX."c_prime", MAIN_DB_PREFIX."c_banque", MAIN_DB_PREFIX."c_fonction"),
			// Label of tables
			'tablib'=>array("Secteurs D'activités", "Conventions", "Accords d'établissement", "Catégorie", "Primes", "Banques", "Fonctions"),
			// Request to select fields
			'tabsql'=>array('SELECT f.rowid as rowid, f.nom, f.active FROM '.MAIN_DB_PREFIX.'c_secteur_activite as f', 'SELECT f.rowid as rowid, f.nom, f.active FROM '.MAIN_DB_PREFIX.'c_convention as f', 'SELECT f.rowid as rowid, f.nom, f.active FROM '.MAIN_DB_PREFIX.'c_accord_etablissement as f', 'SELECT f.rowid as rowid, f.nom, f.convention, f.active FROM '.MAIN_DB_PREFIX.'c_categorie as f', 'SELECT f.rowid as rowid, f.nom, f.categorie, f.active FROM '.MAIN_DB_PREFIX.'c_prime as f', 'SELECT f.rowid as rowid, f.nom, f.code, f.active FROM '.MAIN_DB_PREFIX.'c_banque as f', 'SELECT f.rowid as rowid, f.nom, f.active FROM '.MAIN_DB_PREFIX.'c_fonction as f'),
			// Sort order
			'tabsqlsort'=>array("rowid", "rowid", "rowid", "rowid", "rowid", "rowid", "rowid"),
			// List of fields (result of select to show dictionary)
			'tabfield'=>array("nom", "nom,nom_secteur", "nom", "nom,convention", "nom,categorie", "nom,code", "nom"),
			// List of fields (list of fields to edit a record)
			'tabfieldvalue'=>array("nom", "nom,nom_secteur", "nom", "nom,convention", "nom,categorie", "nom,code", "nom"),
			// List of fields (list of fields for insert)
			'tabfieldinsert'=>array("nom", "nom,nom_secteur", "nom", "nom,convention", "nom,categorie", "nom,code", "nom"),
			// Name of columns with primary key (try to always name it 'rowid')
			'tabrowid'=>array("rowid", "rowid", "rowid", "rowid", "rowid", "rowid", "rowid"),
			// Condition to show each dictionary
			'tabcond'=>array($conf->paiementsalaire->enabled, $conf->paiementsalaire->enabled, $conf->paiementsalaire->enabled, $conf->paiementsalaire->enabled, $conf->paiementsalaire->enabled, $conf->paiementsalaire->enabled, $conf->paiementsalaire->enabled)
		*/);


		// Boxes/Widgets
		// Add here list of php file(s) stored in paiementsalaire/core/boxes that contains a class to show a widget.
		$this->boxes = array(
			//  0 => array(
			//      'file' => 'paiementsalairewidget1.php@paiementsalaire',
			//      'note' => 'Widget provided by PaiementSalaire',
			//      'enabledbydefaulton' => 'Home',
			//  ),
			//  ...
		);

		// Cronjobs (List of cron jobs entries to add when module is enabled)
		// unit_frequency must be 60 for minute, 3600 for hour, 86400 for day, 604800 for week
		$this->cronjobs = array(
			//  0 => array(
			//      'label' => 'MyJob label',
			//      'jobtype' => 'method',
			//      'class' => '/paiementsalaire/class/myobject.class.php',
			//      'objectname' => 'MyObject',
			//      'method' => 'doScheduledJob',
			//      'parameters' => '',
			//      'comment' => 'Comment',
			//      'frequency' => 2,
			//      'unitfrequency' => 3600,
			//      'status' => 0,
			//      'test' => '$conf->paiementsalaire->enabled',
			//      'priority' => 50,
			//  ),
		);
		// Example: $this->cronjobs=array(
		//    0=>array('label'=>'My label', 'jobtype'=>'method', 'class'=>'/dir/class/file.class.php', 'objectname'=>'MyClass', 'method'=>'myMethod', 'parameters'=>'param1, param2', 'comment'=>'Comment', 'frequency'=>2, 'unitfrequency'=>3600, 'status'=>0, 'test'=>'$conf->paiementsalaire->enabled', 'priority'=>50),
		//    1=>array('label'=>'My label', 'jobtype'=>'command', 'command'=>'', 'parameters'=>'param1, param2', 'comment'=>'Comment', 'frequency'=>1, 'unitfrequency'=>3600*24, 'status'=>0, 'test'=>'$conf->paiementsalaire->enabled', 'priority'=>50)
		// );

		// Permissions provided by this module
		$this->rights = array();
		$r = 0;
		// Add here entries to declare new permissions
		/* BEGIN MODULEBUILDER PERMISSIONS */
		$this->rights[$r][0] = $this->numero . sprintf("%02d", $r + 1); // Permission id (must not be already used)
		$this->rights[$r][1] = 'Accès à Salaire | Paie'; // Permission label
		$this->rights[$r][4] = 'salairepaie';
		$this->rights[$r][5] = 'read';
		$r++;

		/*$this->rights[$r][0] = $this->numero . sprintf("%02d", $r + 1); // Permission id (must not be already used)
		$this->rights[$r][1] = 'Un Admin du Salaire | Paie'; // Permission label
		$this->rights[$r][4] = 'salairepaie';
		$this->rights[$r][5] = 'admin';
		$r++;*/

		$this->rights[$r][0] = $this->numero . sprintf("%02d", $r + 1); // Permission id (must not be already used)
		$this->rights[$r][1] = 'Lister les sociétés (Penser à donner le droit de Lecture des Tiers)'; // Permission label
		$this->rights[$r][4] = 'societe';
		$this->rights[$r][5] = 'read'; // In php code, permission will be checked by test if ($user->rights->paiementsalaire->myobject->read)
		$r++;

		$this->rights[$r][0] = $this->numero . sprintf("%02d", $r + 1); // Permission id (must not be already used)
		$this->rights[$r][1] = 'Creer/Modifier les sociétés (Penser à donner les droits de Création/Modification des Tiers)'; // Permission label
		$this->rights[$r][4] = 'societe';
		$this->rights[$r][5] = 'write'; // In php code, permission will be checked by test if ($user->rights->paiementsalaire->myobject->read)
		$r++;

		$this->rights[$r][0] = $this->numero . sprintf("%02d", $r + 1); // Permission id (must not be already used)
		$this->rights[$r][1] = 'Générer les bulletins'; // Permission label
		$this->rights[$r][4] = 'societe';
		$this->rights[$r][5] = 'genererBulletin'; // In php code, permission will be checked by test if ($user->rights->paiementsalaire->myobject->delete)
		$r++;

		$this->rights[$r][0] = $this->numero . sprintf("%02d", $r + 1); // Permission id (must not be already used)
		$this->rights[$r][1] = 'Voir/Télécharger bulletin de paie/taxes/cotisations (sociétés)'; // Permission label
		$this->rights[$r][4] = 'salarie';
		$this->rights[$r][5] = 'voirDocument'; // In php code, permission will be checked by test if ($user->rights->paiementsalaire->myobject->write)
		$r++;

		$this->rights[$r][0] = $this->numero . sprintf("%02d", $r + 1); // Permission id (must not be already used)
		$this->rights[$r][1] = 'Lister les salaries'; // Permission label
		$this->rights[$r][4] = 'salarie';
		$this->rights[$r][5] = 'read'; // In php code, permission will be checked by test if ($user->rights->paiementsalaire->myobject->delete)
		$r++;

		$this->rights[$r][0] = $this->numero . sprintf("%02d", $r + 1); // Permission id (must not be already used)
		$this->rights[$r][1] = 'Creer/Modifier les Salariés (Penser à donner les droits de Creation/Modification d\'un utilisateur)'; // Permission label
		$this->rights[$r][4] = 'salarie';
		$this->rights[$r][5] = 'write'; // In php code, permission will be checked by test if ($user->rights->paiementsalaire->myobject->delete)
		$r++;

		$this->rights[$r][0] = $this->numero . sprintf("%02d", $r + 1); // Permission id (must not be already used)
		$this->rights[$r][1] = 'Voir/Télécharger Bulletin (salariés)'; // Permission label
		$this->rights[$r][4] = 'salarie';
		$this->rights[$r][5] = 'voirBulletin'; // In php code, permission will be checked by test if ($user->rights->paiementsalaire->myobject->delete)
		$r++;

		$this->rights[$r][0] = $this->numero . sprintf("%02d", $r + 1); // Permission id (must not be already used)
		$this->rights[$r][1] = 'Faire une simulation (salariés)'; // Permission label
		$this->rights[$r][4] = 'salarie';
		$this->rights[$r][5] = 'simuler'; // In php code, permission will be checked by test if ($user->rights->paiementsalaire->myobject->delete)
		$r++;

		$this->rights[$r][0] = $this->numero . sprintf("%02d", $r + 1); // Permission id (must not be already used)
		$this->rights[$r][1] = 'Voir Avance/Acompte'; // Permission label
		$this->rights[$r][4] = 'salarie';
		$this->rights[$r][5] = 'lireAvanceAcompte'; // In php code, permission will be checked by test if ($user->rights->paiementsalaire->myobject->delete)
		$r++;

		$this->rights[$r][0] = $this->numero . sprintf("%02d", $r + 1); // Permission id (must not be already used)
		$this->rights[$r][1] = 'Creer/Modifier Avance/Acompte'; // Permission label
		$this->rights[$r][4] = 'salarie';
		$this->rights[$r][5] = 'ecrireAvanceAcompte'; // In php code, permission will be checked by test if ($user->rights->paiementsalaire->myobject->delete)
		$r++;

		$this->rights[$r][0] = $this->numero . sprintf("%02d", $r + 1); // Permission id (must not be already used)
		$this->rights[$r][1] = 'Lister les Contrats'; // Permission label
		$this->rights[$r][4] = 'contrats';
		$this->rights[$r][5] = 'read'; // In php code, permission will be checked by test if ($user->rights->paiementsalaire->contrats->read)
		$r++;

		$this->rights[$r][0] = $this->numero . sprintf("%02d", $r + 1); // Permission id (must not be already used)
		$this->rights[$r][1] = 'Creer/Modifier contrats'; // Permission label
		$this->rights[$r][4] = 'contrats';
		$this->rights[$r][5] = 'write'; // In php code, permission will be checked by test if ($user->rights->paiementsalaire->contrats->write)
		$r++;

		$this->rights[$r][0] = $this->numero . sprintf("%02d", $r + 1); // Permission id (must not be already used)
		$this->rights[$r][1] = 'Acceder à la configuration'; // Permission label
		$this->rights[$r][4] = 'configuration';
		$this->rights[$r][5] = 'read'; // In php code, permission will be checked by test if ($user->rights->paiementsalaire->configuration->read)
		$r++;

		$this->rights[$r][0] = $this->numero . sprintf("%02d", $r + 1); // Permission id (must not be already used)
		$this->rights[$r][1] = 'Modifier les élements de la configuration'; // Permission label
		$this->rights[$r][4] = 'configuration';
		$this->rights[$r][5] = 'write'; // In php code, permission will be checked by test if ($user->rights->paiementsalaire->myobject->delete)
		$r++;

		$this->rights[$r][0] = $this->numero . sprintf("%02d", $r + 1); // Permission id (must not be already used)
		$this->rights[$r][1] = 'Modifier les conventions'; // Permission label
		$this->rights[$r][4] = 'configuration';
		$this->rights[$r][5] = 'convention'; // In php code, permission will be checked by test if ($user->rights->paiementsalaire->myobject->delete)
		$r++;

		/* END MODULEBUILDER PERMISSIONS */

		// Main menu entries to add
		$this->menu = array();
		$r = 0;
		// Add here entries to declare new menus
		/* BEGIN MODULEBUILDER TOPMENU */
		$this->menu[$r++] = array(
			'fk_menu'=>0, // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'top', // This is a Top menu entry
			'titre'=>'Salaires | Paie',
			'prefix' => img_picto('', 'salairepaie', 'class="paddingright pictofixedwidth"'),
			'mainmenu'=>'paiementsalaire',
			'leftmenu'=>'',
			'url'=>'/paiementsalaire/garde.php',
			'langs'=>'paiementsalaire@paiementsalaire', // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>1000 + $r,
			'enabled'=>'$conf->paiementsalaire->enabled', // Define condition to show or hide menu entry. Use '$conf->paiementsalaire->enabled' if entry must be visible if module is enabled.
			'perms'=>'$user->rights->paiementsalaire->salairepaie->read', // Use 'perms'=>'$user->rights->paiementsalaire->myobject->read' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2, // 0=Menu for internal users, 1=external users, 2=both
		);

		$this->menu[$r++] = array(
			'fk_menu'=>'fk_mainmenu=paiementsalaire', // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'left',
			'titre'=>'Sociétés',
			'prefix' => img_picto('', 'company', 'class="paddingright pictofixedwidth"'),
			'mainmenu'=>'paiementsalaire',
			'leftmenu'=>'societe',
			'url'=>'/paiementsalaire/onglets/garde_societe.php?mainmenu=paiementsalaire&leftmenu=societe',
			'langs'=>'paiementsalaire@paiementsalaire', // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>1000 + $r,
			'enabled'=>'$conf->paiementsalaire->enabled', // Define condition to show or hide menu entry. Use '$conf->paiementsalaire->enabled' if entry must be visible if module is enabled.
			'perms'=>'$user->rights->paiementsalaire->societe->read || $user->rights->paiementsalaire->societe->write', // Use 'perms'=>'$user->rights->paiementsalaire->myobject->read' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2, // 0=Menu for internal users, 1=external users, 2=both
		);
		$this->menu[$r++] = array(
			'fk_menu'=>'fk_mainmenu=paiementsalaire', // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'left',
			'titre'=>'Salariés',
			'prefix' => img_picto('', 'group', 'class="paddingright pictofixedwidth valignmiddle"'),
			'mainmenu'=>'paiementsalaire',
			'leftmenu'=>'salarie',
			'url'=>'/paiementsalaire/onglets/garde.php?mainmenu=paiementsalaire&leftmenu=salarie&idmenu=13420',
			'langs'=>'paiementsalaire@paiementsalaire', // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>1000 + $r,
			'enabled'=>'$conf->paiementsalaire->enabled', // Define condition to show or hide menu entry. Use '$conf->paiementsalaire->enabled' if entry must be visible if module is enabled.
			'perms'=>'$user->rights->paiementsalaire->salarie->read || $user->rights->paiementsalaire->salarie->write', // Use 'perms'=>'$user->rights->paiementsalaire->myobject->read' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2, // 0=Menu for internal users, 1=external users, 2=both
		);

		$this->menu[$r++] = array(
			'fk_menu'=>'fk_mainmenu=paiementsalaire', // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'left',
			'titre'=>'Contrats',
			'prefix' => img_picto('', 'contrat', 'class="paddingright pictofixedwidth valignmiddle"'),
			'mainmenu'=>'paiementsalaire',
			'leftmenu'=>'contrats',
			'url'=>'/paiementsalaire/contrat/garde.php?mainmenu=paiementsalaire&leftmenu=contrat&action=liste',
			'langs'=>'paiementsalaire@paiementsalaire', // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>1000 + $r,
			'enabled'=>'$conf->paiementsalaire->enabled', // Define condition to show or hide menu entry. Use '$conf->paiementsalaire->enabled' if entry must be visible if module is enabled.
			'perms'=>'$user->rights->paiementsalaire->contrats->read', // Use 'perms'=>'$user->rights->paiementsalaire->myobject->read' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2, // 0=Menu for internal users, 1=external users, 2=both
		);

		$this->menu[$r++] = array(
			'fk_menu'=>'fk_mainmenu=paiementsalaire', // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'left',
			'titre'=>'Configuration',
			'prefix' => img_picto('', 'setup', 'class="paddingright pictofixedwidth valignmiddle"'),
			'mainmenu'=>'paiementsalaire',
			'leftmenu'=>'configuration',
			'url'=>'/paiementsalaire/configuration.php?mainmenu=paiementsalaire&leftmenu=configuration',
			'langs'=>'paiementsalaire@paiementsalaire', // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>1000 + $r,
			'enabled'=>'$conf->paiementsalaire->enabled', // Define condition to show or hide menu entry. Use '$conf->paiementsalaire->enabled' if entry must be visible if module is enabled.
			'perms'=>'$user->rights->paiementsalaire->configuration->read', // Use 'perms'=>'$user->rights->paiementsalaire->myobject->read' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2, // 0=Menu for internal users, 1=external users, 2=both
		);

		// Sous menu de société
		$this->menu[$r++] = array(
			'fk_menu'=>'fk_mainmenu=paiementsalaire,fk_leftmenu=societe', // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'left',
			'titre'=>'Nouvelle Société',
			'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
			'mainmenu'=>'paiementsalaire',
			'leftmenu'=>'nouvellesociete',
			'url'=>'/societe/card.php?mainmenu=paiementsalaire&leftmenu=societe&action=create',
			'langs'=>'paiementsalaire@paiementsalaire', // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>1000 + $r,
			'enabled'=>'$leftmenu=="societe" || $leftmenu=="importexportsociete"', // Define condition to show or hide menu entry. Use '$conf->paiementsalaire->enabled' if entry must be visible if module is enabled.
			'perms'=>'$user->rights->paiementsalaire->societe->write', // Use 'perms'=>'$user->rights->paiementsalaire->myobject->read' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2, // 0=Menu for internal users, 1=external users, 2=both
		);

		$this->menu[$r++] = array(
			'fk_menu'=>'fk_mainmenu=paiementsalaire,fk_leftmenu=societe', // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'left',
			'titre'=>'Liste',
			'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
			'mainmenu'=>'paiementsalaire',
			'leftmenu'=>'listesociete',
			'url'=>'/paiementsalaire/liste_societe.php?mainmenu=paiementsalaire&leftmenu=societe',
			'langs'=>'paiementsalaire@paiementsalaire', // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>1000 + $r,
			'enabled'=>'$leftmenu=="societe" || $leftmenu=="importexportsociete"', // Define condition to show or hide menu entry. Use '$conf->paiementsalaire->enabled' if entry must be visible if module is enabled.
			'perms'=>'$user->rights->paiementsalaire->societe->read', // Use 'perms'=>'$user->rights->paiementsalaire->myobject->read' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2, // 0=Menu for internal users, 1=external users, 2=both
		);

		$this->menu[$r++] = array(
			'fk_menu'=>'fk_mainmenu=paiementsalaire,fk_leftmenu=societe', // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'left',
			'titre'=>'Import|Export',
			'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
			'mainmenu'=>'paiementsalaire',
			'leftmenu'=>'importexportsociete',
			'url'=>'/paiementsalaire/import_export_societe.php?mainmenu=paiementsalaire&leftmenu=importexportsociete',
			'langs'=>'paiementsalaire@paiementsalaire', // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>1000 + $r,
			'enabled'=>'$leftmenu=="societe" || $leftmenu=="importexportsociete"', // Define condition to show or hide menu entry. Use '$conf->paiementsalaire->enabled' if entry must be visible if module is enabled.
			'perms'=>'$user->rights->paiementsalaire->societe->read', // Use 'perms'=>'$user->rights->paiementsalaire->myobject->read' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2, // 0=Menu for internal users, 1=external users, 2=both
		);

		//Sous menu import export
		$this->menu[$r++] = array(
			'fk_menu'=>'fk_mainmenu=paiementsalaire,fk_leftmenu=importexportsociete', // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'left',
			'titre'=>'Import',
			'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
			'mainmenu'=>'paiementsalaire',
			'leftmenu'=>'import',
			'url'=>'/paiementsalaire/import.php?mainmenu=paiementsalaire&leftmenu=importexportsociete',
			'langs'=>'paiementsalaire@paiementsalaire', // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>1000 + $r,
			'enabled'=>'$leftmenu=="importexportsociete"', // Define condition to show or hide menu entry. Use '$conf->paiementsalaire->enabled' if entry must be visible if module is enabled.
			'perms'=>'$user->rights->paiementsalaire->societe->read', // Use 'perms'=>'$user->rights->paiementsalaire->myobject->read' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2, // 0=Menu for internal users, 1=external users, 2=both
		);
		$this->menu[$r++] = array(
			'fk_menu'=>'fk_mainmenu=paiementsalaire,fk_leftmenu=importexportsociete', // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'left',
			'titre'=>'Export',
			'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
			'mainmenu'=>'paiementsalaire',
			'leftmenu'=>'export',
			'url'=>'/paiementsalaire/export.php?mainmenu=paiementsalaire&leftmenu=importexportsociete',
			'langs'=>'paiementsalaire@paiementsalaire', // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>1000 + $r,
			'enabled'=>'$leftmenu=="importexportsociete"', // Define condition to show or hide menu entry. Use '$conf->paiementsalaire->enabled' if entry must be visible if module is enabled.
			'perms'=>'$user->rights->paiementsalaire->societe->read', // Use 'perms'=>'$user->rights->paiementsalaire->myobject->read' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2, // 0=Menu for internal users, 1=external users, 2=both
		);

		//Les sous menu du salariés

		$this->menu[$r++] = array(
			'fk_menu'=>'fk_mainmenu=paiementsalaire,fk_leftmenu=salarie', // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'',
			'titre'=>'Nouveau Salarié',
			'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
			'mainmenu'=>'paiementsalaire',
			'leftmenu'=>'nouveausalarie',
			'url'=>'/user/card.php?mainmenu=paiementsalaire&leftmenu=salarie&action=create',
			'langs'=>'paiementsalaire@paiementsalaire', // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>1000 + $r,
			'enabled'=>'$leftmenu=="salarie"', // Define condition to show or hide menu entry. Use '$conf->paiementsalaire->enabled' if entry must be visible if module is enabled.
			'perms'=>'$user->rights->paiementsalaire->salarie->write', // Use 'perms'=>'$user->rights->paiementsalaire->myobject->read' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2, // 0=Menu for internal users, 1=external users, 2=both
		);
		$this->menu[$r++] = array(
			'fk_menu'=>'fk_mainmenu=paiementsalaire,fk_leftmenu=salarie', // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'',
			'titre'=>'Liste',
			'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
			'mainmenu'=>'paiementsalaire',
			'leftmenu'=>'listesalarie',
			'url'=>'/paiementsalaire/listesalarie.php?mainmenu=paiementsalaire&leftmenu=salarie',
			'langs'=>'paiementsalaire@paiementsalaire', // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>1000 + $r,
			'enabled'=>'$leftmenu=="salarie"', // Define condition to show or hide menu entry. Use '$conf->paiementsalaire->enabled' if entry must be visible if module is enabled.
			'perms'=>'$user->rights->paiementsalaire->salarie->read', // Use 'perms'=>'$user->rights->paiementsalaire->myobject->read' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2, // 0=Menu for internal users, 1=external users, 2=both
		);

		//Contrats
		$this->menu[$r++] = array(
			'fk_menu'=>'fk_mainmenu=paiementsalaire,fk_leftmenu=contrats', // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'left',
			'titre'=>'Liste contrats',
			'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
			'mainmenu'=>'paiementsalaire',
			'leftmenu'=>'liste_contrats',
			'url'=>'/paiementsalaire/contrat/liste_contrat.php?mainmenu=paiementsalaire&leftmenu=contrat&action=liste',
			'langs'=>'paiementsalaire@paiementsalaire', // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>1000 + $r,
			'enabled'=>'$leftmenu=="contrat"', // Define condition to show or hide menu entry. Use '$conf->paiementsalaire->enabled' if entry must be visible if module is enabled.
			'perms'=>1, // Use 'perms'=>'$user->rights->paiementsalaire->myobject->read' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2, // 0=Menu for internal users, 1=external users, 2=both
		);


//Configuration
	$this->menu[$r++] = array(
		'fk_menu'=>'fk_mainmenu=paiementsalaire,fk_leftmenu=configuration', // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
		'type'=>'left',
		'titre'=>'Conventions',
		'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
		'mainmenu'=>'paiementsalaire',
		'leftmenu'=>'convention',
		'url'=>'/paiementsalaire/convention.php?mainmenu=paiementsalaire&leftmenu=convention&action=afficher',
		'langs'=>'paiementsalaire@paiementsalaire', // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
		'position'=>1000 + $r,
			'enabled'=>'$leftmenu=="configuration" || $leftmenu=="convention" || $leftmenu=="prime" || $leftmenu=="indemnite" || $leftmenu=="organisme" || $leftmenu=="taxe" || $leftmenu=="prestation" || $leftmenu=="heuresup" || $leftmenu=="diplome" || $leftmenu=="conge" || $leftmenu=="autre" || $leftmenu=="accord_etablissement" || $leftmenu=="log" || $leftmenu=="reglage"' , // Define condition to show or hide menu entry. Use '$conf->paiementsalaire->enabled' if entry must be visible if module is enabled.
		'perms'=>1, // Use 'perms'=>'$user->rights->paiementsalaire->myobject->read' if you want your menu with a permission rules
		'target'=>'',
		'user'=>2, // 0=Menu for internal users, 1=external users, 2=both
	);

	$this->menu[$r++] = array(
		'fk_menu'=>'fk_mainmenu=paiementsalaire,fk_leftmenu=convention', // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
		'type'=>'',
		'titre'=>'Nouvelle Convention',
		'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
		'mainmenu'=>'paiementsalaire',
		'leftmenu'=>'nouvelleconvention',
		'url'=>'/paiementsalaire/convention.php?mainmenu=paiementsalaire&leftmenu=convention&action=create',
		'langs'=>'paiementsalaire@paiementsalaire', // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
		'position'=>1000 + $r,
		'enabled'=>'$leftmenu=="convention"', // Define condition to show or hide menu entry. Use '$conf->paiementsalaire->enabled' if entry must be visible if module is enabled.
		'perms'=>1, // Use 'perms'=>'$user->rights->paiementsalaire->myobject->read' if you want your menu with a permission rules
		'target'=>'',
		'user'=>2, // 0=Menu for internal users, 1=external users, 2=both
	);
	$this->menu[$r++] = array(
		'fk_menu'=>'fk_mainmenu=paiementsalaire,fk_leftmenu=convention', // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
		'type'=>'',
		'titre'=>'Liste',
		'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
		'mainmenu'=>'paiementsalaire',
		'leftmenu'=>'listeconvention',
		'url'=>'/paiementsalaire/convention.php?mainmenu=paiementsalaire&leftmenu=convention&action=afficher',
		'langs'=>'paiementsalaire@paiementsalaire', // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
		'position'=>1000 + $r,
		'enabled'=>'$leftmenu=="convention"', // Define condition to show or hide menu entry. Use '$conf->paiementsalaire->enabled' if entry must be visible if module is enabled.
		'perms'=>1, // Use 'perms'=>'$user->rights->paiementsalaire->myobject->read' if you want your menu with a permission rules
		'target'=>'',
		'user'=>2, // 0=Menu for internal users, 1=external users, 2=both
	);

//Accord Etablissement
	$this->menu[$r++] = array(
		'fk_menu'=>'fk_mainmenu=paiementsalaire,fk_leftmenu=configuration', // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
		'type'=>'left',
		'titre'=>'Accord Etablissement',
		'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
		'mainmenu'=>'paiementsalaire',
		'leftmenu'=>'accord_etablissement',
		'url'=>'/paiementsalaire/accord_etablissement.php?mainmenu=paiementsalaire&leftmenu=accord_etablissement&action=afficher',
		'langs'=>'paiementsalaire@paiementsalaire', // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
		'position'=>1000 + $r,
			'enabled'=>'$leftmenu=="configuration" || $leftmenu=="convention" || $leftmenu=="prime" || $leftmenu=="indemnite" || $leftmenu=="organisme" || $leftmenu=="taxe" || $leftmenu=="prestation" || $leftmenu=="heuresup" || $leftmenu=="diplome" || $leftmenu=="conge" || $leftmenu=="autre" || $leftmenu=="accord_etablissement" || $leftmenu=="log" || $leftmenu=="reglage"' , // Define condition to show or hide menu entry. Use '$conf->paiementsalaire->enabled' if entry must be visible if module is enabled.
		'perms'=>1, // Use 'perms'=>'$user->rights->paiementsalaire->myobject->read' if you want your menu with a permission rules
		'target'=>'',
		'user'=>2, // 0=Menu for internal users, 1=external users, 2=both
	);


	$this->menu[$r++] = array(
		'fk_menu'=>'fk_mainmenu=paiementsalaire,fk_leftmenu=accord_etablissement', // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
		'type'=>'',
		'titre'=>'Nouvel accord etablissement',
		'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
		'mainmenu'=>'paiementsalaire',
		'leftmenu'=>'nouvelaccord_etablissement',
		'url'=>'/paiementsalaire/accord_etablissement.php?mainmenu=paiementsalaire&leftmenu=accord_etablissement&action=create',
		'langs'=>'paiementsalaire@paiementsalaire', // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
		'position'=>1000 + $r,
		'enabled'=>'$leftmenu=="accord_etablissement"', // Define condition to show or hide menu entry. Use '$conf->paiementsalaire->enabled' if entry must be visible if module is enabled.
		'perms'=>1, // Use 'perms'=>'$user->rights->paiementsalaire->myobject->read' if you want your menu with a permission rules
		'target'=>'',
		'user'=>2, // 0=Menu for internal users, 1=external users, 2=both
	);
	$this->menu[$r++] = array(
		'fk_menu'=>'fk_mainmenu=paiementsalaire,fk_leftmenu=accord_etablissement', // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
		'type'=>'',
		'titre'=>'Liste',
		'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
		'mainmenu'=>'paiementsalaire',
		'leftmenu'=>'liste_accord_etablissement.php',
		'url'=>'/paiementsalaire/accord_etablissement.php?mainmenu=paiementsalaire&leftmenu=accord_etablissement&action=afficher',
		'langs'=>'paiementsalaire@paiementsalaire', // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
		'position'=>1000 + $r,
		'enabled'=>'$leftmenu=="accord_etablissement"', // Define condition to show or hide menu entry. Use '$conf->paiementsalaire->enabled' if entry must be visible if module is enabled.
		'perms'=>1, // Use 'perms'=>'$user->rights->paiementsalaire->myobject->read' if you want your menu with a permission rules
		'target'=>'',
		'user'=>2, // 0=Menu for internal users, 1=external users, 2=both
	);


//Primes
		$this->menu[$r++] = array(
			'fk_menu'=>'fk_mainmenu=paiementsalaire,fk_leftmenu=configuration', // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'left',
			'titre'=>'Primes',
			'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
			'mainmenu'=>'paiementsalaire',
			'leftmenu'=>'prime',
			'url'=>'/paiementsalaire/listeprime.php?mainmenu=paiementsalaire&leftmenu=prime',
			'langs'=>'paiementsalaire@paiementsalaire', // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>1000 + $r,
			'enabled'=>'$leftmenu=="configuration" || $leftmenu=="convention" || $leftmenu=="prime" || $leftmenu=="indemnite" || $leftmenu=="organisme" || $leftmenu=="taxe" || $leftmenu=="prestation" || $leftmenu=="heuresup" || $leftmenu=="diplome" || $leftmenu=="conge" || $leftmenu=="autre" || $leftmenu=="accord_etablissement" || $leftmenu=="log" || $leftmenu=="reglage"' , // Define condition to show or hide menu entry. Use '$conf->paiementsalaire->enabled' if entry must be visible if module is enabled.
			'perms'=>1, // Use 'perms'=>'$user->rights->paiementsalaire->myobject->read' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2, // 0=Menu for internal users, 1=external users, 2=both
		);
		$this->menu[$r++] = array(
			'fk_menu'=>'fk_mainmenu=paiementsalaire,fk_leftmenu=prime', // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'left',
			'titre'=>'Nouvelle Prime',
			'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
			'mainmenu'=>'paiementsalaire',
			'leftmenu'=>'nouveauprime',
			'url'=>'/paiementsalaire/listeprime.php?mainmenu=paiementsalaire&leftmenu=prime&action=create',
			'langs'=>'paiementsalaire@paiementsalaire', // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>1000 + $r,
			'enabled'=>'$leftmenu=="prime"', // Define condition to show or hide menu entry. Use '$conf->paiementsalaire->enabled' if entry must be visible if module is enabled.
			'perms'=>1, // Use 'perms'=>'$user->rights->paiementsalaire->myobject->read' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2, // 0=Menu for internal users, 1=external users, 2=both
		);
		$this->menu[$r++] = array(
			'fk_menu'=>'fk_mainmenu=paiementsalaire,fk_leftmenu=prime', // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'left',
			'titre'=>'Liste',
			'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
			'mainmenu'=>'paiementsalaire',
			'leftmenu'=>'listeprime',
			'url'=>'/paiementsalaire/listeprime.php?mainmenu=paiementsalaire&leftmenu=prime',
			'langs'=>'paiementsalaire@paiementsalaire', // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>1000 + $r,
			'enabled'=>'$leftmenu=="prime"', // Define condition to show or hide menu entry. Use '$conf->paiementsalaire->enabled' if entry must be visible if module is enabled.
			'perms'=>1, // Use 'perms'=>'$user->rights->paiementsalaire->myobject->read' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2, // 0=Menu for internal users, 1=external users, 2=both
		);


//Indemnités
		$this->menu[$r++] = array(
			'fk_menu'=>'fk_mainmenu=paiementsalaire,fk_leftmenu=configuration', // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'left',
			'titre'=>'Indemnités',
			'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
			'mainmenu'=>'paiementsalaire',
			'leftmenu'=>'indemnite',
			'url'=>'/paiementsalaire/listeindemnite.php?mainmenu=paiementsalaire&leftmenu=indemnite',
			'langs'=>'paiementsalaire@paiementsalaire', // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>1000 + $r,
			'enabled'=>'$leftmenu=="configuration" || $leftmenu=="convention" || $leftmenu=="prime" || $leftmenu=="indemnite" || $leftmenu=="organisme" || $leftmenu=="taxe" || $leftmenu=="prestation" || $leftmenu=="heuresup" || $leftmenu=="diplome" || $leftmenu=="conge" || $leftmenu=="autre" || $leftmenu=="accord_etablissement" || $leftmenu=="log" || $leftmenu=="reglage"' , // Define condition to show or hide menu entry. Use '$conf->paiementsalaire->enabled' if entry must be visible if module is enabled.
			'perms'=>1, // Use 'perms'=>'$user->rights->paiementsalaire->myobject->read' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2, // 0=Menu for internal users, 1=external users, 2=both
		);

		$this->menu[$r++] = array(
			'fk_menu'=>'fk_mainmenu=paiementsalaire,fk_leftmenu=indemnite', // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'',
			'titre'=>'Nouvelle indemnite',
			'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
			'mainmenu'=>'paiementsalaire',
			'leftmenu'=>'nouveauindemnite',
			'url'=>'/paiementsalaire/listeindemnite.php?mainmenu=paiementsalaire&leftmenu=indemnite&action=create',
			'langs'=>'paiementsalaire@paiementsalaire', // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>1000 + $r,
			'enabled'=>'$leftmenu=="indemnite"', // Define condition to show or hide menu entry. Use '$conf->paiementsalaire->enabled' if entry must be visible if module is enabled.
			'perms'=>1, // Use 'perms'=>'$user->rights->paiementsalaire->myobject->read' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2, // 0=Menu for internal users, 1=external users, 2=both
		);

		$this->menu[$r++] = array(
			'fk_menu'=>'fk_mainmenu=paiementsalaire,fk_leftmenu=indemnite', // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'left',
			'titre'=>'Liste',
			'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
			'mainmenu'=>'paiementsalaire',
			'leftmenu'=>'listeindemnite',
			'url'=>'/paiementsalaire/listeindemnite.php?mainmenu=paiementsalaire&leftmenu=indemnite',
			'langs'=>'paiementsalaire@paiementsalaire', // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>1000 + $r,
			'enabled'=>'$leftmenu=="indemnite"', // Define condition to show or hide menu entry. Use '$conf->paiementsalaire->enabled' if entry must be visible if module is enabled.
			'perms'=>1, // Use 'perms'=>'$user->rights->paiementsalaire->myobject->read' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2, // 0=Menu for internal users, 1=external users, 2=both
		);
//Organismes
		$this->menu[$r++] = array(
			'fk_menu'=>'fk_mainmenu=paiementsalaire,fk_leftmenu=configuration', // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'left',
			'titre'=>'Organismes',
			'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
			'mainmenu'=>'paiementsalaire',
			'leftmenu'=>'organisme',
			'url'=>'/paiementsalaire/config/organisme.php?mainmenu=paiementsalaire&leftmenu=organisme&action=liste',
			'langs'=>'paiementsalaire@paiementsalaire', // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>1000 + $r,
			'enabled'=>'$leftmenu=="configuration" || $leftmenu=="convention" || $leftmenu=="prime" || $leftmenu=="indemnite" || $leftmenu=="organisme" || $leftmenu=="taxe" || $leftmenu=="prestation" || $leftmenu=="heuresup" || $leftmenu=="diplome" || $leftmenu=="conge" || $leftmenu=="autre" || $leftmenu=="accord_etablissement" || $leftmenu=="log" || $leftmenu=="reglage"' , // Define condition to show or hide menu entry. Use '$conf->paiementsalaire->enabled' if entry must be visible if module is enabled.
			'perms'=>1, // Use 'perms'=>'$user->rights->paiementsalaire->myobject->read' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2, // 0=Menu for internal users, 1=external users, 2=both
		);
		$this->menu[$r++] = array(
			'fk_menu'=>'fk_mainmenu=paiementsalaire,fk_leftmenu=organisme', // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'',
			'titre'=>'Nouvel Organisme',
			'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
			'mainmenu'=>'paiementsalaire',
			'leftmenu'=>'nouvelle_organisme',
			'url'=>'/paiementsalaire/config/organisme.php?mainmenu=paiementsalaire&leftmenu=organisme&action=create',
			'langs'=>'paiementsalaire@paiementsalaire', // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>1000 + $r,
			'enabled'=>'$leftmenu=="organisme"', // Define condition to show or hide menu entry. Use '$conf->paiementsalaire->enabled' if entry must be visible if module is enabled.
			'perms'=>1, // Use 'perms'=>'$user->rights->paiementsalaire->myobject->read' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2, // 0=Menu for internal users, 1=external users, 2=both
		);

		$this->menu[$r++] = array(
			'fk_menu'=>'fk_mainmenu=paiementsalaire,fk_leftmenu=organisme', // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'left',
			'titre'=>'Liste',
			'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
			'mainmenu'=>'paiementsalaire',
			'leftmenu'=>'liste_organisme',
			'url'=>'/paiementsalaire/config/organisme.php?mainmenu=paiementsalaire&leftmenu=organisme&action=liste',
			'langs'=>'paiementsalaire@paiementsalaire', // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>1000 + $r,
			'enabled'=>'$leftmenu=="organisme"', // Define condition to show or hide menu entry. Use '$conf->paiementsalaire->enabled' if entry must be visible if module is enabled.
			'perms'=>1, // Use 'perms'=>'$user->rights->paiementsalaire->myobject->read' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2, // 0=Menu for internal users, 1=external users, 2=both
		);
//Taxes
		$this->menu[$r++] = array(
			'fk_menu'=>'fk_mainmenu=paiementsalaire,fk_leftmenu=configuration', // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'left',
			'titre'=>'Taxes',
			'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
			'mainmenu'=>'paiementsalaire',
			'leftmenu'=>'taxe',
			'url'=>'/paiementsalaire/config/liste_taxe.php?mainmenu=paiementsalaire&leftmenu=taxe',
			'langs'=>'paiementsalaire@paiementsalaire', // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>1000 + $r,
			'enabled'=>'$leftmenu=="configuration" || $leftmenu=="convention" || $leftmenu=="prime" || $leftmenu=="indemnite" || $leftmenu=="organisme" || $leftmenu=="taxe" || $leftmenu=="prestation" || $leftmenu=="heuresup" || $leftmenu=="diplome" || $leftmenu=="conge" || $leftmenu=="autre" || $leftmenu=="accord_etablissement" || $leftmenu=="log" || $leftmenu=="reglage"' , // Define condition to show or hide menu entry. Use '$conf->paiementsalaire->enabled' if entry must be visible if module is enabled.
			'perms'=>1, // Use 'perms'=>'$user->rights->paiementsalaire->myobject->read' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2, // 0=Menu for internal users, 1=external users, 2=both
		);
		$this->menu[$r++] = array(
			'fk_menu'=>'fk_mainmenu=paiementsalaire,fk_leftmenu=taxe', // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'left',
			'titre'=>'Nouvelle taxe',
			'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
			'mainmenu'=>'paiementsalaire',
			'leftmenu'=>'nouvelle_taxe',
			'url'=>'/paiementsalaire/config/liste_taxe.php?mainmenu=paiementsalaire&leftmenu=taxe&action=create',
			'langs'=>'paiementsalaire@paiementsalaire', // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>1000 + $r,
			'enabled'=>'$leftmenu=="taxe"', // Define condition to show or hide menu entry. Use '$conf->paiementsalaire->enabled' if entry must be visible if module is enabled.
			'perms'=>1, // Use 'perms'=>'$user->rights->paiementsalaire->myobject->read' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2, // 0=Menu for internal users, 1=external users, 2=both
		);

		$this->menu[$r++] = array(
			'fk_menu'=>'fk_mainmenu=paiementsalaire,fk_leftmenu=taxe', // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'left',
			'titre'=>'Liste',
			'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
			'mainmenu'=>'paiementsalaire',
			'leftmenu'=>'listetaxe',
			'url'=>'/paiementsalaire/config/liste_taxe.php?mainmenu=paiementsalaire&leftmenu=taxe&action=liste',
			'langs'=>'paiementsalaire@paiementsalaire', // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>1000 + $r,
			'enabled'=>'$leftmenu=="taxe"', // Define condition to show or hide menu entry. Use '$conf->paiementsalaire->enabled' if entry must be visible if module is enabled.
			'perms'=>1, // Use 'perms'=>'$user->rights->paiementsalaire->myobject->read' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2, // 0=Menu for internal users, 1=external users, 2=both
		);
//Cotisations ou Prestations
		$this->menu[$r++] = array(
			'fk_menu'=>'fk_mainmenu=paiementsalaire,fk_leftmenu=configuration', // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'left',
			'titre'=>'Cotisations',
			'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
			'mainmenu'=>'paiementsalaire',
			'leftmenu'=>'prestation',
			'url'=>'/paiementsalaire/config/liste_prestation.php?mainmenu=paiementsalaire&leftmenu=prestation',
			'langs'=>'paiementsalaire@paiementsalaire', // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>1000 + $r,
			'enabled'=>'$leftmenu=="configuration" || $leftmenu=="convention" || $leftmenu=="prime" || $leftmenu=="indemnite" || $leftmenu=="organisme" || $leftmenu=="taxe" || $leftmenu=="prestation" || $leftmenu=="heuresup" || $leftmenu=="diplome" || $leftmenu=="conge" || $leftmenu=="autre" || $leftmenu=="accord_etablissement" || $leftmenu=="log" || $leftmenu=="reglage"' , // Define condition to show or hide menu entry. Use '$conf->paiementsalaire->enabled' if entry must be visible if module is enabled.
			'perms'=>1, // Use 'perms'=>'$user->rights->paiementsalaire->myobject->read' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2, // 0=Menu for internal users, 1=external users, 2=both
		);
		$this->menu[$r++] = array(
			'fk_menu'=>'fk_mainmenu=paiementsalaire,fk_leftmenu=prestation', // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'left',
			'titre'=>'Nouvelle Cotisation',
			'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
			'mainmenu'=>'paiementsalaire',
			'leftmenu'=>'nouveauprestation',
			'url'=>'/paiementsalaire/config/liste_prestation.php?mainmenu=paiementsalaire&leftmenu=prestation&action=create',
			'langs'=>'paiementsalaire@paiementsalaire', // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>1000 + $r,
			'enabled'=>'$leftmenu=="prestation"', // Define condition to show or hide menu entry. Use '$conf->paiementsalaire->enabled' if entry must be visible if module is enabled.
			'perms'=>1, // Use 'perms'=>'$user->rights->paiementsalaire->myobject->read' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2, // 0=Menu for internal users, 1=external users, 2=both
		);

		$this->menu[$r++] = array(
			'fk_menu'=>'fk_mainmenu=paiementsalaire,fk_leftmenu=prestation', // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'left',
			'titre'=>'Liste',
			'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
			'mainmenu'=>'paiementsalaire',
			'leftmenu'=>'listeprestation',
			'url'=>'/paiementsalaire/config/liste_prestation.php?mainmenu=paiementsalaire&leftmenu=prestation&action=liste',
			'langs'=>'paiementsalaire@paiementsalaire', // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>1000 + $r,
			'enabled'=>'$leftmenu=="prestation"', // Define condition to show or hide menu entry. Use '$conf->paiementsalaire->enabled' if entry must be visible if module is enabled.
			'perms'=>1, // Use 'perms'=>'$user->rights->paiementsalaire->myobject->read' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2, // 0=Menu for internal users, 1=external users, 2=both
		);
//Heures sup
		$this->menu[$r++] = array(
			'fk_menu'=>'fk_mainmenu=paiementsalaire,fk_leftmenu=configuration', // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'left',
			'titre'=>'Heure Sup',
			'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
			'mainmenu'=>'paiementsalaire',
			'leftmenu'=>'heuresup',
			'url'=>'/paiementsalaire/config/ajout_heure_sup.php?mainmenu=paiementsalaire&leftmenu=heuresup&action=liste',
			'langs'=>'paiementsalaire@paiementsalaire', // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>1000 + $r,
			'enabled'=>'$leftmenu=="configuration" || $leftmenu=="convention" || $leftmenu=="prime" || $leftmenu=="indemnite" || $leftmenu=="organisme" || $leftmenu=="taxe" || $leftmenu=="prestation" || $leftmenu=="heuresup" || $leftmenu=="diplome" || $leftmenu=="conge" || $leftmenu=="autre" || $leftmenu=="accord_etablissement" || $leftmenu=="log" || $leftmenu=="reglage"' , // Define condition to show or hide menu entry. Use '$conf->paiementsalaire->enabled' if entry must be visible if module is enabled.
			'perms'=>1, // Use 'perms'=>'$user->rights->paiementsalaire->myobject->read' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2, // 0=Menu for internal users, 1=external users, 2=both
		);

		$this->menu[$r++] = array(
			'fk_menu'=>'fk_mainmenu=paiementsalaire,fk_leftmenu=heuresup', // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'',
			'titre'=>'Nouvelle heure sup',
			'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
			'mainmenu'=>'paiementsalaire',
			'leftmenu'=>'nouvelleheuresup',
			'url'=>'/paiementsalaire/config/ajout_heure_sup.php?mainmenu=paiementsalaire&leftmenu=heuresup&action=create',
			'langs'=>'paiementsalaire@paiementsalaire', // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>1000 + $r,
			'enabled'=>'$leftmenu=="heuresup"', // Define condition to show or hide menu entry. Use '$conf->paiementsalaire->enabled' if entry must be visible if module is enabled.
			'perms'=>1, // Use 'perms'=>'$user->rights->paiementsalaire->myobject->read' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2, // 0=Menu for internal users, 1=external users, 2=both
		);
		$this->menu[$r++] = array(
			'fk_menu'=>'fk_mainmenu=paiementsalaire,fk_leftmenu=heuresup', // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'',
			'titre'=>'Liste',
			'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
			'mainmenu'=>'paiementsalaire',
			'leftmenu'=>'listeheuresup',
			'url'=>'/paiementsalaire/config/ajout_heure_sup.php?mainmenu=paiementsalaire&leftmenu=heuresup&action=liste',
			'langs'=>'paiementsalaire@paiementsalaire', // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>1000 + $r,
			'enabled'=>'$leftmenu=="heuresup"', // Define condition to show or hide menu entry. Use '$conf->paiementsalaire->enabled' if entry must be visible if module is enabled.
			'perms'=>1, // Use 'perms'=>'$user->rights->paiementsalaire->myobject->read' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2, // 0=Menu for internal users, 1=external users, 2=both
		);
//Diplômes
		$this->menu[$r++] = array(
			'fk_menu'=>'fk_mainmenu=paiementsalaire,fk_leftmenu=configuration', // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'left',
			'titre'=>'Diplome',
			'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
			'mainmenu'=>'paiementsalaire',
			'leftmenu'=>'diplome',
			'url'=>'/paiementsalaire/config/diplome.php?mainmenu=paiementsalaire&leftmenu=diplome&action=liste',
			'langs'=>'paiementsalaire@paiementsalaire', // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>1000 + $r,
			'enabled'=>'$leftmenu=="configuration" || $leftmenu=="convention" || $leftmenu=="prime" || $leftmenu=="indemnite" || $leftmenu=="organisme" || $leftmenu=="taxe" || $leftmenu=="prestation" || $leftmenu=="heuresup" || $leftmenu=="diplome" || $leftmenu=="conge" || $leftmenu=="autre" || $leftmenu=="accord_etablissement" || $leftmenu=="log" || $leftmenu=="reglage"' , // Define condition to show or hide menu entry. Use '$conf->paiementsalaire->enabled' if entry must be visible if module is enabled.
			'perms'=>1, // Use 'perms'=>'$user->rights->paiementsalaire->myobject->read' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2, // 0=Menu for internal users, 1=external users, 2=both
		);
		$this->menu[$r++] = array(
			'fk_menu'=>'fk_mainmenu=paiementsalaire,fk_leftmenu=diplome', // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'left',
			'titre'=>'Ajouter Diplôme',
			'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
			'mainmenu'=>'paiementsalaire',
			'leftmenu'=>'nouveau_taux',
			'url'=>'/paiementsalaire/config/diplome.php?mainmenu=paiementsalaire&leftmenu=diplome&action=create',
			'langs'=>'paiementsalaire@paiementsalaire', // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>1000 + $r,
			'enabled'=>'$leftmenu=="diplome"', // Define condition to show or hide menu entry. Use '$conf->paiementsalaire->enabled' if entry must be visible if module is enabled.
			'perms'=>1, // Use 'perms'=>'$user->rights->paiementsalaire->myobject->read' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2, // 0=Menu for internal users, 1=external users, 2=both
		);
		$this->menu[$r++] = array(
			'fk_menu'=>'fk_mainmenu=paiementsalaire,fk_leftmenu=diplome', // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'left',
			'titre'=>'Liste Diplôme',
			'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
			'mainmenu'=>'paiementsalaire',
			'leftmenu'=>'liste_taux',
			'url'=>'/paiementsalaire/config/diplome.php?mainmenu=paiementsalaire&leftmenu=diplome&action=liste',
			'langs'=>'paiementsalaire@paiementsalaire', // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>1000 + $r,
			'enabled'=>'$leftmenu=="diplome"', // Define condition to show or hide menu entry. Use '$conf->paiementsalaire->enabled' if entry must be visible if module is enabled.
			'perms'=>1, // Use 'perms'=>'$user->rights->paiementsalaire->myobject->read' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2, // 0=Menu for internal users, 1=external users, 2=both
		);
//Congés
		$this->menu[$r++] = array(
			'fk_menu'=>'fk_mainmenu=paiementsalaire,fk_leftmenu=configuration', // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'left',
			'titre'=>'Congés',
			'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
			'mainmenu'=>'paiementsalaire',
			'leftmenu'=>'conge',
			'url'=>'/paiementsalaire/config//ajout_conge.php?mainmenu=paiementsalaire&leftmenu=conge&action=liste',
			'langs'=>'paiementsalaire@paiementsalaire', // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>1000 + $r,
			'enabled'=>'$leftmenu=="configuration" || $leftmenu=="convention" || $leftmenu=="prime" || $leftmenu=="indemnite" || $leftmenu=="organisme" || $leftmenu=="taxe" || $leftmenu=="prestation" || $leftmenu=="heuresup" || $leftmenu=="diplome" || $leftmenu=="conge" || $leftmenu=="autre" || $leftmenu=="accord_etablissement" || $leftmenu=="log" || $leftmenu=="reglage"' , // Define condition to show or hide menu entry. Use '$conf->paiementsalaire->enabled' if entry must be visible if module is enabled.
			'perms'=>1, // Use 'perms'=>'$user->rights->paiementsalaire->myobject->read' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2, // 0=Menu for internal users, 1=external users, 2=both
		);
		$this->menu[$r++] = array(
			'fk_menu'=>'fk_mainmenu=paiementsalaire,fk_leftmenu=conge', // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'left',
			'titre'=>'Nouveau congé',
			'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
			'mainmenu'=>'paiementsalaire',
			'leftmenu'=>'nouveauconge',
			'url'=>'/paiementsalaire/config/ajout_conge.php?mainmenu=paiementsalaire&leftmenu=conge&action=create',
			'langs'=>'paiementsalaire@paiementsalaire', // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>1000 + $r,
			'enabled'=>'$leftmenu=="conge"', // Define condition to show or hide menu entry. Use '$conf->paiementsalaire->enabled' if entry must be visible if module is enabled.
			'perms'=>1, // Use 'perms'=>'$user->rights->paiementsalaire->myobject->read' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2, // 0=Menu for internal users, 1=external users, 2=both
		);

		$this->menu[$r++] = array(
			'fk_menu'=>'fk_mainmenu=paiementsalaire,fk_leftmenu=conge', // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'left',
			'titre'=>'Liste congé',
			'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
			'mainmenu'=>'paiementsalaire',
			'leftmenu'=>'listeconge',
			'url'=>'/paiementsalaire/config/ajout_conge.php?mainmenu=paiementsalaire&leftmenu=conge&action=liste',
			'langs'=>'paiementsalaire@paiementsalaire', // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>1000 + $r,
			'enabled'=>'$leftmenu=="conge"', // Define condition to show or hide menu entry. Use '$conf->paiementsalaire->enabled' if entry must be visible if module is enabled.
			'perms'=>1, // Use 'perms'=>'$user->rights->paiementsalaire->myobject->read' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2, // 0=Menu for internal users, 1=external users, 2=both
		);

		//Bulletins
		$this->menu[$r++] = array(
			'fk_menu'=>'fk_mainmenu=paiementsalaire,fk_leftmenu=configuration', // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'left',
			'titre'=>'Avance/Acompte',
			'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
			'mainmenu'=>'paiementsalaire',
			'leftmenu'=>'avance_acompte',
			'url'=>'/paiementsalaire/config/regle_avance_acompte.php?mainmenu=paiementsalaire&leftmenu=configuration',
			'langs'=>'paiementsalaire@paiementsalaire', // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>1000 + $r,
			'enabled'=>'$leftmenu=="configuration" || $leftmenu=="convention" || $leftmenu=="prime" || $leftmenu=="indemnite" || $leftmenu=="organisme" || $leftmenu=="taxe" || $leftmenu=="prestation" || $leftmenu=="heuresup" || $leftmenu=="diplome" || $leftmenu=="conge" || $leftmenu=="autre" || $leftmenu=="accord_etablissement" || $leftmenu=="log" || $leftmenu=="reglage"' , // Define condition to show or hide menu entry. Use '$conf->paiementsalaire->enabled' if entry must be visible if module is enabled.
			'perms'=>1, // Use 'perms'=>'$user->rights->paiementsalaire->myobject->read' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2, // 0=Menu for internal users, 1=external users, 2=both
		);

		//Bulletins
		$this->menu[$r++] = array(
			'fk_menu'=>'fk_mainmenu=paiementsalaire,fk_leftmenu=configuration', // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'left',
			'titre'=>'Reglages',
			'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
			'mainmenu'=>'paiementsalaire',
			'leftmenu'=>'reglage',
			'url'=>'/paiementsalaire/config/reglages.php?mainmenu=paiementsalaire&leftmenu=configuration',
			'langs'=>'paiementsalaire@paiementsalaire', // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>1000 + $r,
			'enabled'=>'$leftmenu=="configuration" || $leftmenu=="convention" || $leftmenu=="prime" || $leftmenu=="indemnite" || $leftmenu=="organisme" || $leftmenu=="taxe" || $leftmenu=="prestation" || $leftmenu=="heuresup" || $leftmenu=="diplome" || $leftmenu=="conge" || $leftmenu=="autre" || $leftmenu=="accord_etablissement" || $leftmenu=="log" || $leftmenu=="reglage"' , // Define condition to show or hide menu entry. Use '$conf->paiementsalaire->enabled' if entry must be visible if module is enabled.
			'perms'=>1, // Use 'perms'=>'$user->rights->paiementsalaire->myobject->read' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2, // 0=Menu for internal users, 1=external users, 2=both
		);
		/*$this->menu[$r++] = array(
			'fk_menu'=>'fk_mainmenu=paiementsalaire,fk_leftmenu=bulletin', // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'left',
			'titre'=>'Modèle Bulletin',
			'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
			'mainmenu'=>'paiementsalaire',
			'leftmenu'=>'modelebulletin',
			'url'=>'/paiementsalaire/config/modele_bulletin.php?mainmenu=paiementsalaire&leftmenu=bulletin',
			'langs'=>'paiementsalaire@paiementsalaire', // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>1000 + $r,
			'enabled'=>'$leftmenu=="bulletin"', // Define condition to show or hide menu entry. Use '$conf->paiementsalaire->enabled' if entry must be visible if module is enabled.
			'perms'=>1, // Use 'perms'=>'$user->rights->paiementsalaire->myobject->read' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2, // 0=Menu for internal users, 1=external users, 2=both
		);
		$this->menu[$r++] = array(
			'fk_menu'=>'fk_mainmenu=paiementsalaire,fk_leftmenu=bulletin', // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'left',
			'titre'=>'Reglage',
			'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
			'mainmenu'=>'paiementsalaire',
			'leftmenu'=>'reglagebulletin',
			'url'=>'/paiementsalaire/config/reglage_bulletin.php?mainmenu=paiementsalaire&leftmenu=bulletin',
			'langs'=>'paiementsalaire@paiementsalaire', // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>1000 + $r,
			'enabled'=>'$leftmenu=="bulletin"', // Define condition to show or hide menu entry. Use '$conf->paiementsalaire->enabled' if entry must be visible if module is enabled.
			'perms'=>1, // Use 'perms'=>'$user->rights->paiementsalaire->myobject->read' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2, // 0=Menu for internal users, 1=external users, 2=both
		);*/

//Autres
		$this->menu[$r++] = array(
			'fk_menu'=>'fk_mainmenu=paiementsalaire,fk_leftmenu=configuration', // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'left',
			'titre'=>'Autres',
			'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
			'mainmenu'=>'paiementsalaire',
			'leftmenu'=>'autre',
			'url'=>'/paiementsalaire/config/ajout_type_salarie.php?mainmenu=paiementsalaire&leftmenu=autre&action=liste',
			'langs'=>'paiementsalaire@paiementsalaire', // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>1000 + $r,
			'enabled'=>'$leftmenu=="configuration" || $leftmenu=="convention" || $leftmenu=="prime" || $leftmenu=="indemnite" || $leftmenu=="organisme" || $leftmenu=="taxe" || $leftmenu=="prestation" || $leftmenu=="heuresup" || $leftmenu=="diplome" || $leftmenu=="conge" || $leftmenu=="autre" || $leftmenu=="accord_etablissement"' , // Define condition to show or hide menu entry. Use '$conf->paiementsalaire->enabled' if entry must be visible if module is enabled.
			'perms'=>1, // Use 'perms'=>'$user->rights->paiementsalaire->myobject->read' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2, // 0=Menu for internal users, 1=external users, 2=both
		);
		//types salariés
		$this->menu[$r++] = array(
			'fk_menu'=>'fk_mainmenu=paiementsalaire,fk_leftmenu=autre', // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'left',
			'titre'=>'Nouveau type salarié',
			'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
			'mainmenu'=>'paiementsalaire',
			'leftmenu'=>'nouveau_typ_sal',
			'url'=>'/paiementsalaire/config/ajout_type_salarie.php?mainmenu=paiementsalaire&leftmenu=autre&action=create',
			'langs'=>'paiementsalaire@paiementsalaire', // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>1000 + $r,
			'enabled'=>'$leftmenu=="autre"', // Define condition to show or hide menu entry. Use '$conf->paiementsalaire->enabled' if entry must be visible if module is enabled.
			'perms'=>1, // Use 'perms'=>'$user->rights->paiementsalaire->myobject->read' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2, // 0=Menu for internal users, 1=external users, 2=both
		);

		$this->menu[$r++] = array(
			'fk_menu'=>'fk_mainmenu=paiementsalaire,fk_leftmenu=autre', // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'left',
			'titre'=>'Liste type salarié',
			'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
			'mainmenu'=>'paiementsalaire',
			'leftmenu'=>'liste_type_sal',
			'url'=>'/paiementsalaire/config/ajout_type_salarie.php?mainmenu=paiementsalaire&leftmenu=autre&action=liste',
			'langs'=>'paiementsalaire@paiementsalaire', // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>1000 + $r,
			'enabled'=>'$leftmenu=="autre"', // Define condition to show or hide menu entry. Use '$conf->paiementsalaire->enabled' if entry must be visible if module is enabled.
			'perms'=>1, // Use 'perms'=>'$user->rights->paiementsalaire->myobject->read' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2, // 0=Menu for internal users, 1=external users, 2=both
		);
		$this->menu[$r++] = array(
			'fk_menu'=>'fk_mainmenu=paiementsalaire,fk_leftmenu=autre', // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'left',
			'titre'=>'Nouveau type Contrat',
			'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
			'mainmenu'=>'paiementsalaire',
			'leftmenu'=>'nouveau_typ_sal',
			'url'=>'/paiementsalaire/config/ajout_type_contrat.php?mainmenu=paiementsalaire&leftmenu=autre&action=create',
			'langs'=>'paiementsalaire@paiementsalaire', // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>1000 + $r,
			'enabled'=>'$leftmenu=="autre"', // Define condition to show or hide menu entry. Use '$conf->paiementsalaire->enabled' if entry must be visible if module is enabled.
			'perms'=>1, // Use 'perms'=>'$user->rights->paiementsalaire->myobject->read' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2, // 0=Menu for internal users, 1=external users, 2=both
		);
		$this->menu[$r++] = array(
			'fk_menu'=>'fk_mainmenu=paiementsalaire,fk_leftmenu=autre', // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'left',
			'titre'=>'Liste type contrat',
			'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
			'mainmenu'=>'paiementsalaire',
			'leftmenu'=>'liste_type_contrat',
			'url'=>'/paiementsalaire/config/ajout_type_contrat.php?mainmenu=paiementsalaire&leftmenu=autre&action=liste',
			'langs'=>'paiementsalaire@paiementsalaire', // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>1000 + $r,
			'enabled'=>'$leftmenu=="autre"', // Define condition to show or hide menu entry. Use '$conf->paiementsalaire->enabled' if entry must be visible if module is enabled.
			'perms'=>1, // Use 'perms'=>'$user->rights->paiementsalaire->myobject->read' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2, // 0=Menu for internal users, 1=external users, 2=both
		);

		$this->menu[$r++] = array(
			'fk_menu'=>'fk_mainmenu=paiementsalaire,fk_leftmenu=autre', // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'left',
			'titre'=>'Nouveau type Banque',
			'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
			'mainmenu'=>'paiementsalaire',
			'leftmenu'=>'nouveau_typ_banque',
			'url'=>'/paiementsalaire/config/ajout_type_banque.php?mainmenu=paiementsalaire&leftmenu=autre&action=create',
			'langs'=>'paiementsalaire@paiementsalaire', // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>1000 + $r,
			'enabled'=>'$leftmenu=="autre"', // Define condition to show or hide menu entry. Use '$conf->paiementsalaire->enabled' if entry must be visible if module is enabled.
			'perms'=>1, // Use 'perms'=>'$user->rights->paiementsalaire->myobject->read' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2, // 0=Menu for internal users, 1=external users, 2=both
		);
		$this->menu[$r++] = array(
			'fk_menu'=>'fk_mainmenu=paiementsalaire,fk_leftmenu=autre', // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'left',
			'titre'=>'Liste type Banque',
			'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
			'mainmenu'=>'paiementsalaire',
			'leftmenu'=>'liste_type_banque',
			'url'=>'/paiementsalaire/config/ajout_type_banque.php?mainmenu=paiementsalaire&leftmenu=autre&action=liste',
			'langs'=>'paiementsalaire@paiementsalaire', // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>1000 + $r,
			'enabled'=>'$leftmenu=="autre"', // Define condition to show or hide menu entry. Use '$conf->paiementsalaire->enabled' if entry must be visible if module is enabled.
			'perms'=>1, // Use 'perms'=>'$user->rights->paiementsalaire->myobject->read' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2, // 0=Menu for internal users, 1=external users, 2=both
		);

		//Log
		$this->menu[$r++] = array(
			'fk_menu'=>'fk_mainmenu=paiementsalaire,fk_leftmenu=configuration', // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'left',
			'titre'=>'Log',
			'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
			'mainmenu'=>'paiementsalaire',
			'leftmenu'=>'log',
			'url'=>'/paiementsalaire/config/log.php?mainmenu=paiementsalaire&leftmenu=configuration',
			'langs'=>'paiementsalaire@paiementsalaire', // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>1000 + $r,
			'enabled'=>'$leftmenu=="configuration" || $leftmenu=="convention" || $leftmenu=="prime" || $leftmenu=="indemnite" || $leftmenu=="organisme" || $leftmenu=="taxe" || $leftmenu=="prestation" || $leftmenu=="heuresup" || $leftmenu=="diplome" || $leftmenu=="conge" || $leftmenu=="autre" || $leftmenu=="accord_etablissement" || $leftmenu=="log" || $leftmenu=="reglage"' , // Define condition to show or hide menu entry. Use '$conf->paiementsalaire->enabled' if entry must be visible if module is enabled.
			'perms'=>1, // Use 'perms'=>'$user->rights->paiementsalaire->myobject->read' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2, // 0=Menu for internal users, 1=external users, 2=both
		);

		//A propos
		$this->menu[$r++] = array(
			'fk_menu'=>'fk_mainmenu=paiementsalaire,fk_leftmenu=configuration', // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'left',
			'titre'=>'A propos',
			'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
			'mainmenu'=>'paiementsalaire',
			'leftmenu'=>'apropos',
			'url'=>'/paiementsalaire/config/apropos.php?mainmenu=paiementsalaire&leftmenu=configuration',
			'langs'=>'paiementsalaire@paiementsalaire', // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>1000 + $r,
			'enabled'=>'$leftmenu=="configuration" || $leftmenu=="convention" || $leftmenu=="prime" || $leftmenu=="indemnite" || $leftmenu=="organisme" || $leftmenu=="taxe" || $leftmenu=="prestation" || $leftmenu=="heuresup" || $leftmenu=="diplome" || $leftmenu=="conge" || $leftmenu=="autre" || $leftmenu=="accord_etablissement" || $leftmenu=="log" || $leftmenu=="reglage"' , // Define condition to show or hide menu entry. Use '$conf->paiementsalaire->enabled' if entry must be visible if module is enabled.
			'perms'=>1, // Use 'perms'=>'$user->rights->paiementsalaire->myobject->read' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2, // 0=Menu for internal users, 1=external users, 2=both
		);


		//END MODULEBUILDER LEFTMENU MYOBJECT
		// Exports profiles provided by this module
		$r = 1;
		/* BEGIN MODULEBUILDER EXPORT MYOBJECT */

		$langs->load("paiementsalaire@paiementsalaire");
		$this->export_code[$r]=$this->rights_class.'_'.$r;
		$this->export_label[$r]='Salariés';	// Translation key (used only if key ExportDataset_xxx_z not found)
		$this->export_icon[$r]='group';
		// Define $this->export_fields_array, $this->export_TypeFields_array and $this->export_entities_array
		$keyforclass = 'PaiementSalaireExport'; $keyforclassfile='/paiementsalaire/class/paiementsalaireexport.class.php'; $keyforelement='myobject@paiementsalaire';
		//include DOL_DOCUMENT_ROOT.'/core/commonfieldsinexport.inc.php';
		//$this->export_fields_array[$r]['t.fieldtoadd']='FieldToAdd'; $this->export_TypeFields_array[$r]['t.fieldtoadd']='Text';
		//unset($this->export_fields_array[$r]['t.fieldtoremove']);
		//$keyforclass = 'MyObjectLine'; $keyforclassfile='/paiementsalaire/class/myobject.class.php'; $keyforelement='myobjectline@paiementsalaire'; $keyforalias='tl';
		//include DOL_DOCUMENT_ROOT.'/core/commonfieldsinexport.inc.php';
		$keyforselect='paiementsalaireexport'; $keyforaliasextra='extra'; $keyforelement='myobject@paiementsalaire';
		include DOL_DOCUMENT_ROOT.'/core/extrafieldsinexport.inc.php';
		//$keyforselect='myobjectline'; $keyforaliasextra='extraline'; $keyforelement='myobjectline@paiementsalaire';
		//include DOL_DOCUMENT_ROOT.'/core/extrafieldsinexport.inc.php';
		//$this->export_dependencies_array[$r] = array('myobjectline'=>array('tl.rowid','tl.ref')); // To force to activate one or several fields if we select some fields that need same (like to select a unique key if we ask a field of a child to avoid the DISTINCT to discard them, or for computed field than need several other fields)
		//$this->export_special_array[$r] = array('t.field'=>'...');
		//$this->export_examplevalues_array[$r] = array('t.field'=>'Example');
		//$this->export_help_array[$r] = array('t.field'=>'FieldDescHelp');

		//export des salariés
		$this->import_tables_array[$r]['u'] = 'llx_user';
		$this->import_tables_array[$r]['salrie'] = 'llx_salarie';
		$this->import_tables_array[$r]['tbank'] = 'llx_type_banque';

		$this->import_tables_array[$r]['ue'] = 'llx_user_extrafields';
		$this->import_tables_array[$r]['sc'] = 'llx_societe';
		$this->import_tables_array[$r]['sce'] = 'llx_societe_extrafields';

		$this->export_fields_array[$r]['salrie.matricule']='MATRICULE'; $this->export_TypeFields_array[$r]['salrie.matricule']='Text';
		$this->export_fields_array[$r]['u.lastname']='NOM'; $this->export_TypeFields_array[$r]['u.lastname']='Text';
		$this->export_fields_array[$r]['u.firstname']='PRENOM'; $this->export_TypeFields_array[$r]['u.firstname']='Text';
		$this->export_fields_array[$r]['u.gender']='SEXE'; $this->export_TypeFields_array[$r]['bul.gender']='Text';

		$this->export_fields_array[$r]['salrie.situation_familiale']='SITUATION MAT'; $this->export_TypeFields_array[$r]['salrie.situation_familiale']='Text';
		$this->export_fields_array[$r]['salrie.nombre_enfant']='ENFANT'; $this->export_TypeFields_array[$r]['salrie.nombre_enfant']='integer';
		$this->export_fields_array[$r]['salrie.nombre_enfant_hand']='ENFANT HAND'; $this->export_TypeFields_array[$r]['salrie.nombre_enfant_hand']='integer';
		$this->export_fields_array[$r]['u.dateemployment']='DATE EMB'; $this->export_TypeFields_array[$r]['u.dateemployment']='date';
		$this->export_fields_array[$r]['cat.code_categorie']='CATEGORIE'; $this->export_TypeFields_array[$r]['cat.code_categorie']='text';
		$this->export_fields_array[$r]['ech.libelle']='ECHELON'; $this->export_TypeFields_array[$r]['ech.libelle']='text';
		$this->export_fields_array[$r]['u.job']='FONCTION'; $this->export_TypeFields_array[$r]['u.job']='text';

		$this->export_fields_array[$r]['salrie.inps']='N° SEC.SOCIAL'; $this->export_TypeFields_array[$r]['salrie.inps']='Text';
		$this->export_fields_array[$r]['tbank.libelle']='BANQUE'; $this->export_TypeFields_array[$r]['tbank.libelle']='Text';
		$this->export_fields_array[$r]['salrie.compte']='N° COMPTE'; $this->export_TypeFields_array[$r]['salrie.compte']='Text';
		$this->export_fields_array[$r]['soc.nom']='SOCIETE'; $this->export_TypeFields_array[$r]['soc.nom']='Text';
		$this->export_sql_start[$r]='SELECT DISTINCT ';

		$this->export_sql_end[$r]  =" FROM ".MAIN_DB_PREFIX."salarie as salrie";
		$this->export_sql_end[$r] .= " LEFT JOIN ".MAIN_DB_PREFIX."dcategories as cat ON salrie.fk_categorie=cat.rowid";
		$this->export_sql_end[$r] .= " LEFT JOIN ".MAIN_DB_PREFIX."echelon as ech ON salrie.fk_echelon=ech.rowid";

		$this->export_sql_end[$r] .= " LEFT JOIN ".MAIN_DB_PREFIX."type_banque as tbank ON salrie.fk_type_banque=tbank.rowid";
		$this->export_sql_end[$r] .= " LEFT JOIN ".MAIN_DB_PREFIX."user as u ON u.rowid=salrie.fk_user";
		$this->export_sql_end[$r] .= " LEFT JOIN ".MAIN_DB_PREFIX."user_extrafields as ue ON u.rowid=ue.fk_object";
		$this->export_sql_end[$r] .= " LEFT JOIN ".MAIN_DB_PREFIX."societe as soc ON soc.rowid=ue.egp";
		$this->export_sql_end[$r] .= " LEFT JOIN ".MAIN_DB_PREFIX."societe_extrafields as sce ON soc.rowid=sce.fk_object";
		$this->export_sql_end[$r] .= " WHERE salrie.matricule <> '' AND sce.grp=1 ORDER BY soc.rowid";
/*
//export des données du bulletins
		//-----------------------------------------
		$this->import_tables_array[$r]['u'] = 'llx_user';
		$this->import_tables_array[$r]['bul'] = 'llx_bulletin';
		$this->import_tables_array[$r]['salrie'] = 'llx_salarie';
		$this->import_tables_array[$r]['soc'] = 'llx_societe';
		$this->import_tables_array[$r]['cotis'] = 'llx_bulletin_cotisation';
		$this->import_tables_array[$r]['its'] = 'llx_bulletin_taxe';

		$this->export_fields_array[$r]['bul.matricule']='MATRICULE'; $this->export_TypeFields_array[$r]['bul.matricule']='Text';
		$this->export_fields_array[$r]['bul.nom']='NOM'; $this->export_TypeFields_array[$r]['bul.nom']='Text';
		$this->export_fields_array[$r]['bul.prenom']='PRENOM'; $this->export_TypeFields_array[$r]['bul.prenom']='Text';
		$this->export_fields_array[$r]['bul.sexe']='SEXE'; $this->export_TypeFields_array[$r]['bul.sexe']='Text';
		$this->export_fields_array[$r]['bul.situation_familiale']='SITUATION MAT'; $this->export_TypeFields_array[$r]['bul.situation_familiale']='Text';
		$this->export_fields_array[$r]['bul.nombre_enfant']='ENFANT'; $this->export_TypeFields_array[$r]['bul.nombre_enfant']='integer';
		$this->export_fields_array[$r]['bul.nombre_enfant_hand']='ENFANT HAND'; $this->export_TypeFields_array[$r]['bul.nombre_enfant_hand']='integer';
		$this->export_fields_array[$r]['u.dateemployment']='DATE EMB'; $this->export_TypeFields_array[$r]['u.dateemployment']='date';
		$this->export_fields_array[$r]['bul.categorie']='CATEGORIE'; $this->export_TypeFields_array[$r]['bul.categorie']='text';
		$this->export_fields_array[$r]['bul.echelon']='ECHELON'; $this->export_TypeFields_array[$r]['bul.echelon']='text';
		$this->export_fields_array[$r]['u.job']='FONCTION'; $this->export_TypeFields_array[$r]['u.job']='text';
		$this->export_fields_array[$r]['bul.salaire_base']='SAL BASE'; $this->export_TypeFields_array[$r]['bul.salaire_base']='text';
		$this->export_fields_array[$r]['bul.sursalaire']='SURSALAIRE'; $this->export_TypeFields_array[$r]['bul.sursalaire']='text';
		$this->export_fields_array[$r]['bul.salaire_brut']='SAL BRUT'; $this->export_TypeFields_array[$r]['bul.salaire_brut']='text';
		$this->export_fields_array[$r]['bul.salaire_brut_cotisable']='BRUT INPS'; $this->export_TypeFields_array[$r]['bul.salaire_brut_cotisable']='text';
		$this->export_fields_array[$r]['cotis.montant_employe']='INPS EMPLOYE'; $this->export_TypeFields_array[$r]['cotis.montant_employe']='text';
		$this->export_fields_array[$r]['cotis.montant_employe']='INPS EMPLOYEUR'; $this->export_TypeFields_array[$r]['cotis.montant_employe']='text';

		$this->export_fields_array[$r]['bul.salaire_brut_imposable']='BRUT ITS'; $this->export_TypeFields_array[$r]['bul.salaire_brut_imposable']='text';
		$this->export_fields_array[$r]['its.montant']='INPS EMPLOYE'; $this->export_TypeFields_array[$r]['its.montant']='text';

		$this->export_fields_array[$r]['bul.avance']='AVANCE'; $this->export_TypeFields_array[$r]['bul.avance']='text';
		$this->export_fields_array[$r]['bul.net_payer']='NET A PAYER'; $this->export_TypeFields_array[$r]['bul.net_payer']='text';


		$this->export_fields_array[$r]['bul.inps']='N° SEC.SOCIAL'; $this->export_TypeFields_array[$r]['bul.inps']='Text';
		$this->export_fields_array[$r]['bul.banque']='BANQUE'; $this->export_TypeFields_array[$r]['bul.banque']='Text';
		$this->export_fields_array[$r]['bul.compte']='N° COMPTE'; $this->export_TypeFields_array[$r]['bul.compte']='Text';
		$this->export_fields_array[$r]['soc.nom']='SOCIETE'; $this->export_TypeFields_array[$r]['soc.nom']='Text';
		$this->export_fields_array[$r]['bul.mois']='MOIS'; $this->export_TypeFields_array[$r]['bul.mois']='Text';
		$this->export_fields_array[$r]['bul.annee']='ANNEE'; $this->export_TypeFields_array[$r]['bul.annee']='Text';

		$this->export_sql_start[$r]='SELECT DISTINCT ';

		$this->export_sql_end[$r]  =" FROM ".MAIN_DB_PREFIX."bulletin as bul";
		$this->export_sql_end[$r] .= " LEFT JOIN ".MAIN_DB_PREFIX."bulletin_cotisation as cotis ON bul.rowid=cotis.fk_bulletin";
		$this->export_sql_end[$r] .= " LEFT JOIN ".MAIN_DB_PREFIX."bulletin_taxe as its ON bul.rowid=its.fk_bulletin";

		$this->export_sql_end[$r] .= " LEFT JOIN ".MAIN_DB_PREFIX."salarie as salrie ON salrie.rowid=bul.fk_salarie";
		$this->export_sql_end[$r] .= " LEFT JOIN ".MAIN_DB_PREFIX."user as u ON u.rowid=salrie.fk_user";
		$this->export_sql_end[$r] .= " LEFT JOIN ".MAIN_DB_PREFIX."societe as soc ON soc.rowid=bul.fk_societe";
		$this->export_sql_end[$r] .= " WHERE bul.matricule <> '' AND bul.cloture='oui'";*/

		//$this->export_sql_end[$r]  =' LEFT JOIN '.MAIN_DB_PREFIX.'myobject_line as tl ON tl.fk_myobject = t.rowid';
		//$this->export_sql_end[$r] .=' WHERE 1 = 1';
		//$this->export_sql_end[$r] .=' AND sal.entity IN ('.getEntity('myobject').')';
		$r++;
		/* END MODULEBUILDER EXPORT MYOBJECT */
/*$langs->load("paiementsalaire@paiementsalaire");
		$this->export_code[$r]=$this->rights_class.'_'.$r;
		$this->export_label[$r]='Salariés';	// Translation key (used only if key ExportDataset_xxx_z not found)
		$this->export_icon[$r]='group';
		// Define $this->export_fields_array, $this->export_TypeFields_array and $this->export_entities_array
		$keyforclass = 'PaiementSalaireExport'; $keyforclassfile='/paiementsalaire/class/paiementsalaireexport.class.php'; $keyforelement='myobject@paiementsalaire';
		//include DOL_DOCUMENT_ROOT.'/core/commonfieldsinexport.inc.php';
		$this->import_tables_array[$r]['u'] = 'llx_user';
		$this->import_tables_array[$r]['salrie'] = 'llx_salarie';
		$this->import_tables_array[$r]['ue'] = 'llx_user_extrafields';
		$this->import_tables_array[$r]['sc'] = 'llx_societe';
		$this->import_tables_array[$r]['sce'] = 'llx_societe_extrafields';

		$this->export_fields_array[$r]['salrie.matricule']='matricule'; $this->export_TypeFields_array[$r]['salrie.matricule']='Text';
		$this->export_fields_array[$r]['u.lastname']='lastname'; $this->export_TypeFields_array[$r]['u.lastname']='Text';
		$this->export_fields_array[$r]['u.firstname']='firstname'; $this->export_TypeFields_array[$r]['u.firstname']='Text';
		$this->export_fields_array[$r]['soc.nom']='nom'; $this->export_TypeFields_array[$r]['soc.nom']='Text';

		//unset($this->export_fields_array[$r]['t.fieldtoremove']);
		//$keyforclass = 'MyObjectLine'; $keyforclassfile='/paiementsalaire/class/myobject.class.php'; $keyforelement='myobjectline@paiementsalaire'; $keyforalias='tl';
		//include DOL_DOCUMENT_ROOT.'/core/commonfieldsinexport.inc.php';
		//$keyforselect='paiementsalaireexport'; $keyforaliasextra='extra'; $keyforelement='myobject@paiementsalaire';
		//include DOL_DOCUMENT_ROOT.'/core/extrafieldsinexport.inc.php';
		//$keyforselect='myobjectline'; $keyforaliasextra='extraline'; $keyforelement='myobjectline@paiementsalaire';
		//include DOL_DOCUMENT_ROOT.'/core/extrafieldsinexport.inc.php';
		//$this->export_dependencies_array[$r] = array('myobjectline'=>array('tl.rowid','tl.ref')); // To force to activate one or several fields if we select some fields that need same (like to select a unique key if we ask a field of a child to avoid the DISTINCT to discard them, or for computed field than need several other fields)
		//$this->export_special_array[$r] = array('t.field'=>'...');
		//$this->export_examplevalues_array[$r] = array('t.field'=>'Example');
		//$this->export_help_array[$r] = array('t.field'=>'FieldDescHelp');
		$this->export_sql_start[$r]=' ';
		$this->export_sql_end[$r] = "SELECT u.rowid, u.lastname, u.firstname, u.dateemployment, salrie.matricule, salrie.fk_user, ue.fk_object, ue.egp, soc.rowid as id_societe, soc.nom, sce.fk_object, sce.conv FROM ".MAIN_DB_PREFIX."user as u";
		$this->export_sql_end[$r] .= " LEFT JOIN ".MAIN_DB_PREFIX."salarie as salrie ON u.rowid=salrie.fk_user";
		$this->export_sql_end[$r] .= " LEFT JOIN ".MAIN_DB_PREFIX."user_extrafields as ue ON u.rowid=ue.fk_object";
		$this->export_sql_end[$r] .= ' LEFT JOIN '.MAIN_DB_PREFIX.'societe as soc ON soc.rowid=ue.egp';
		$this->export_sql_end[$r] .= ' LEFT JOIN '.MAIN_DB_PREFIX.'societe_extrafields as sce ON soc.rowid=sce.fk_object';
		$this->export_sql_end[$r] .= ' WHERE sce.grp=1';
*/
		// Imports profiles provided by this module
		$r = 1;


		 /*$langs->load("paiementsalaire@paiementsalaire");
		 $this->import_code[$r]=$this->rights_class.'_'.$r;
		 $this->import_label[$r]='Convention';	// Translation key (used only if key importDataset_xxx_z not found)
		 $this->import_icon[$r]='company';
		 $keyforclass = 'PaiementSalaireImport'; $keyforclassfile='/paiementsalaire/class/paiementsalaireimport.class.php'; $keyforelement='myobject@paiementsalaire';
		 $this->import_fields_array[$r]['t.nom']='nom'; $this->import_TypeFields_array[$r]['t.nom']='Text';
		 $this->import_fields_array[$r]['t.commentaire']='Commentaire'; $this->import_TypeFields_array[$r]['t.commentaire']='Text';
		 $this->import_fields_array[$r]['t.fichier']='Fichier'; $this->import_TypeFields_array[$r]['fichier']='Text';*/

		 $this->import_code[$r]=$this->rights_class.'_'.$r;
		 $this->import_label[$r]='Salariés';	// Translation key (used only if key importDataset_xxx_z not found)
		 $this->import_icon[$r]='group';
		 $keyforclass = 'paiementsalaire_salarie_import'; $keyforclassfile='/paiementsalaire/class/paiementsalaire_salarie_import.class.php'; $keyforelement='myobject@paiementsalaire';
		 $this->import_tables_array[$r]['salrie'] = 'llx_salarie';
		 $this->import_fields_array[$r]['salrie.matricule']='matricule'; $this->import_TypeFields_array[$r]['salrie.matricule']='Text'; $this->import_examplevalues_array[$r]['salrie.matricule'] = 'SOC-0001';
		 $this->import_fields_array[$r]['salrie.situation_familiale']='situation_familiale'; $this->import_TypeFields_array[$r]['salrie.situation_familiale']='Text'; $this->import_examplevalues_array[$r]['salrie.situation_familiale'] = 'marie ou divorce ou celibataire';
		 $this->import_fields_array[$r]['salrie.nombre_enfant']='nombre_enfant'; $this->import_TypeFields_array[$r]['salrie.nombre_enfant']='Integer'; $this->import_examplevalues_array[$r]['salrie.nombre_enfant'] = '0 ou 1 ou 2 ou ...';
		 $this->import_fields_array[$r]['salrie.nombre_enfant_hand']='nombre_enfant_hand'; $this->import_TypeFields_array[$r]['salrie.nombre_enfant_hand']='Integer'; $this->import_examplevalues_array[$r]['salrie.nombre_enfant_hand'] = '0 ou 1 ou 2 ou ...';
		 $this->import_fields_array[$r]['salrie.fk_user']='fk_user'; $this->import_TypeFields_array[$r]['salrie.fk_user']='Integer'; $this->import_examplevalues_array[$r]['salrie.fk_user'] = 'identifiant utilisateur';
		 $this->import_fields_array[$r]['salrie.fk_categorie']='fk_categorie'; $this->import_TypeFields_array[$r]['salrie.fk_categorie']='Integer'; $this->import_examplevalues_array[$r]['salrie.fk_categorie'] = 'id_categorie';
		 $this->import_fields_array[$r]['salrie.fk_echelon']='fk_echelon'; $this->import_TypeFields_array[$r]['salrie.fk_echelon']='Integer'; $this->import_examplevalues_array[$r]['salrie.fk_echelon'] = 'id_echelon sinon 0';
		 $this->import_fields_array[$r]['salrie.sursalaire']='sursalaire'; $this->import_TypeFields_array[$r]['salrie.sursalaire']='Integer'; $this->import_examplevalues_array[$r]['salrie.sursalaire'] = 'sursalaire sinon 0';
		 $this->import_fields_array[$r]['salrie.type_salarie']='type_salarie'; $this->import_TypeFields_array[$r]['salrie.type_salarie']='Integer'; $this->import_examplevalues_array[$r]['salrie.type_salarie'] = '0 => aucun';
		 $this->import_fields_array[$r]['salrie.type_contrat']='type_contrat'; $this->import_TypeFields_array[$r]['salrie.type_contrat']='Integer'; $this->import_examplevalues_array[$r]['salrie.type_contrat'] = ' 1=> CDD, 2=> CDI, ..';
		 $this->import_fields_array[$r]['salrie.fk_diplome']='fk_diplome'; $this->import_TypeFields_array[$r]['salrie.fk_diplome']='Integer'; $this->import_examplevalues_array[$r]['salrie.fk_diplome'] = '1=> Doctorat, 2=> Master, 3=> Licence, 4=> BAC';
		 $this->import_fields_array[$r]['salrie.date_modification']='date_modification'; $this->import_TypeFields_array[$r]['salrie.date_modification']='date'; $this->import_examplevalues_array[$r]['salrie.date_modification'] = 'date';
		 $this->import_fields_array[$r]['salrie.inps']='inps'; $this->import_TypeFields_array[$r]['salrie.inps']='text'; $this->import_examplevalues_array[$r]['salrie.inps'] = 'n° inps';
		 $this->import_fields_array[$r]['salrie.fk_type_banque']='fk_type_banque'; $this->import_TypeFields_array[$r]['salrie.fk_type_banque']='Integer'; $this->import_examplevalues_array[$r]['salrie.fk_type_banque'] = '1=>BDM, 2=>BNDA, 3=>Orange Money, 4=>Moov Money';
		 $this->import_fields_array[$r]['salrie.compte']='compte'; $this->import_TypeFields_array[$r]['salrie.compte']='text'; $this->import_examplevalues_array[$r]['salrie.compte'] = 'N° de compte ou N° orange money ou N° moov mooney';
		 $this->import_fields_array[$r]['salrie.date_anciennete']='date_anciennete'; $this->import_TypeFields_array[$r]['salrie.date_anciennete']='date'; $this->import_examplevalues_array[$r]['salrie.date_anciennete'] = 'Date avec laquelle on calcul l\'encienneté';


		 //include DOL_DOCUMENT_ROOT.'/core/commonfieldsinimport.inc.php';
		 //$keyforselect='PaiementSalaireImport'; $keyforaliasextra='extra'; $keyforelement='myobject@paiementsalaire';
		 //include DOL_DOCUMENT_ROOT.'/core/extrafieldsinimport.inc.php';
		 //$this->import_dependencies_array[$r]=array('mysubobject'=>'ts.rowid', 't.myfield'=>array('t.myfield2','t.myfield3')); // To force to activate one or several fields if we select some fields that need same (like to select a unique key if we ask a field of a child to avoid the DISTINCT to discard them, or for computed field than need several other fields)
		 //$this->import_sql_start[$r]='INSERT INTO ';
		 //$this->import_sql_end[$r]  = MAIN_DB_PREFIX.'salarie (matricule, situation_familial, nombre_enfant, nombre_enfant_hand, nombre_conjoint, fk_user, fk_categorie, fk_echelon, sursalaire)';
		 //$this->import_sql_end[$r] .=' WHERE 1 = 1';
		 //$this->import_sql_end[$r] .=' AND t.entity IN ('.getEntity('myobject').')';

		 $r ++;
		 $this->import_code[$r]=$this->rights_class.'_'.$r;
		 $this->import_label[$r]='Contrat';	// Translation key (used only if key importDataset_xxx_z not found)
		 $this->import_icon[$r]='group';
		 $keyforclass = 'paiementsalaire_salarie_import'; $keyforclassfile='/paiementsalaire/class/paiementsalaire_salarie_import.class.php'; $keyforelement='myobject@paiementsalaire';
		 $this->import_tables_array[$r]['contrat'] = 'llx_salarie_contrat';

		 $this->import_fields_array[$r]['contrat.fk_salarie']='fk_salarie'; $this->import_TypeFields_array[$r]['contrat.fk_salarie']='integer'; $this->import_examplevalues_array[$r]['contrat.fk_salarie'] = 'id du salarié';
		 $this->import_fields_array[$r]['contrat.numero']='numero'; $this->import_TypeFields_array[$r]['contrat.numero']='text'; $this->import_examplevalues_array[$r]['contrat.numero'] = 'SOC-0001';
		 $this->import_fields_array[$r]['contrat.fk_type_contrat']='fk_type_contrat'; $this->import_TypeFields_array[$r]['contrat.fk_type_contrat']='integer'; $this->import_examplevalues_array[$r]['contrat.fk_type_contrat'] = 'ID (1=>CDD, 2=>CDI, 3=> ...)';
		 $this->import_fields_array[$r]['contrat.date_signature']='date_signature'; $this->import_TypeFields_array[$r]['contrat.date_signature']='date'; $this->import_examplevalues_array[$r]['contrat.date_signature'] = '2024-01-30';
		 $this->import_fields_array[$r]['contrat.date_embauche']='date_embauche'; $this->import_TypeFields_array[$r]['contrat.date_embauche']='date'; $this->import_examplevalues_array[$r]['contrat.date_embauche'] = '2024-01-30';
		 $this->import_fields_array[$r]['contrat.date_fin']='date_fin'; $this->import_TypeFields_array[$r]['contrat.date_fin']='date'; $this->import_examplevalues_array[$r]['contrat.date_fin'] = 'pas de date fin pour CDI';
		 $this->import_fields_array[$r]['contrat.fichier_contrat']='fichier_contrat'; $this->import_TypeFields_array[$r]['contrat.fichier_contrat']='text'; $this->import_examplevalues_array[$r]['contrat.fichier_contrat'] = '';
		 $this->import_fields_array[$r]['contrat.active']='active'; $this->import_TypeFields_array[$r]['contrat.active']='integer'; $this->import_examplevalues_array[$r]['contrat.active'] = '0 ou 1';
		 $r ++;

		/* END MODULEBUILDER IMPORT MYOBJECT */
	}

	/**
	 *  Function called when module is enabled.
	 *  The init function add constants, boxes, permissions and menus (defined in constructor) into Dolibarr database.
	 *  It also creates data directories
	 *
	 *  @param      string  $options    Options when enabling module ('', 'noboxes')
	 *  @return     int             	1 if OK, 0 if KO
	 */
	public function init($options = '')
	{
		global $conf, $langs;

		//$result = $this->_load_tables('/install/mysql/tables/', 'paiementsalaire');
		$result = $this->_load_tables('/paiementsalaire/sql/');
		if ($result < 0) {
			return -1; // Do not activate module if error 'not allowed' returned when loading module SQL queries (the _load_table run sql with run_sql with the error allowed parameter set to 'default')
		}
		//----------------------------------------------------------------------------------------------------------------
		$sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."convention";
		$res = $this->db->query($sql);
		if($res){
		$nb = $this->db->num_rows($res);
		if($nb < 1){
			//Mine
			//id_conv = 1; id_grille = 1; id_categ = [1-30](30categ)
			$sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'convention (nom, commentaire, active) VALUES ("Minière, Géologique et Hydrogeologique","Covention Collective des Sociétés et Entreprises Minières, Geologiques et Hydrogeologiques",1)';
			$this->db->query($sql_insert);
		}

		//---------------------------------Banque, Assurance et Finance
		//id_conv = 2; id_grille = 2; id_categ = [33-76](44categ)
		if($nb < 2){
			$sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'convention (nom, commentaire, active) VALUES ("Banque, Assurances & Finance","Convention Collective des Banques, Assurances et Etablissements Financiers du Mali",1)';
			$this->db->query($sql_insert);
		}
		if($nb < 3){
		//----------------------------------------------Commerce
		//id_conv = 3; id_grille = 4; id_categ = [75-85](11 categ)(les catégorie ne change pas car c'est la même convention)
			$sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'convention (nom, commentaire, active) VALUES ("Commerce","Convention Collective du Commerce",1)';
			$this->db->query($sql_insert);
		}

		if($nb < 4){
		//------------------------------------------- Bâtiments
		//id_conv = 4; id_grille = 5; id_categ = [89-110]
		$sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'convention (nom, commentaire, active) VALUES ("Bâtiment et Travaux Publics","Convention Collective des Entreprises du Bâtiments et des Travaux Publics",1)';
			$this->db->query($sql_insert);
		}
		if($nb < 5){
		//-------------------------------------------Industrie Hoteliers
		//id_conv = 5; id_grille = 6; id_categ = [1011-114]
			$sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'convention (nom, commentaire, active) VALUES ("Industrie Hotelière","Convention des Industries Hotélières du Mali",1)';
			$this->db->query($sql_insert);
		}
		if($nb < 6){
		//----------------------------------- Surveillance
			$sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'convention (nom, commentaire, active) VALUES ("Surveillance, Gardiennage et Prestation de Service","Convention Collective des Personnels des Sociétés de Surveillance, de Gardiennage et de Prestations de Service",1)';
			$this->db->query($sql_insert);
		}
		if($nb < 7){
		//--------------------------------------Métallurgie

			$sql_insert = 'INSERT INTO '.MAIN_DB_PREFIX.'convention (nom, commentaire, active) VALUES ("Métallurgie et Mécanique","Convention Collective de la Méttallurgie et des Industries de la Mécanique Générale",1)';
			$this->db->query($sql_insert);


		}
		}

		// Create extrafields during init
		include_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';
		$extrafields = new ExtraFields($this->db);
		$result4=$extrafields->addExtraField('conv', "Convention", 'sellist',  1,  5, 'thirdparty',   0, 0, '---', array('options'=>array('convention:nom:rowid::rowid'=>'SELECT rowid, nom FROM llx_convention')), 1,'', 1, 0, '', '', 'paiementsalaire@paiementsalaire', '$conf->paiementsalaire->enabled');
		$result4=$extrafields->addExtraField('grp', "Gérer la paie", 'select',  1,  10, 'thirdparty',   0, 0, '0', array('options'=>array('1'=>'Oui','2'=>'Non')), 1,'', 1, 0, '', '', 'paiementsalaire@paiementsalaire', '$conf->paiementsalaire->enabled');
		$result4=$extrafields->addExtraField('numero_inps', "Numéro d'affiliation I.N.P.S", 'varchar',  1,  15, 'thirdparty',   0, 0, '', '', 1,'', 1, 0, '', '', 'paiementsalaire@paiementsalaire', '$conf->paiementsalaire->enabled');

		$result4=$extrafields->addExtraField('egp', "Entreprise (géré paie)", 'sellist',  1,  5, 'user',   0, 0, '', array('options'=>array('societe:nom:rowid::(main.rowid =extra.fk_object and extra.grp=1)'=>'SELECT sc.rowid, nom, sce.grp, sce.fk_object FROM llx_societe as sc  LEFT JOIN llx_societe_extrafields as sce ON sc.rowid=sce.fk_object WHERE sce.grp=1')), 1,'', 1, 0, '', '', 'paiementsalaire@paiementsalaire', '$conf->paiementsalaire->enabled');

		//$result_=$extrafields->addExtraField('code', "libellé de l'attribut", 'select_list', 1, taille, 'Module', unique (0 non, 1 oui), requis(0 non, 1 oui), 'valeur par defaut (base de donnée)',array('options'=>array('convention:nom:rowid::rowid')), peut toujours etre edité (0 non, 1 oui),'1', visible(0 non, 1oui), afficher sur pdf(0 non,1oui), 'champ calculé (1 oui 0 non)', '1 ou 0', 'paiementsalaire@paiementsalaire(fichier de langue)', '$conf->paiementsalaire->enabled');

		//$p = array();
		//$p['options'][''] = '';
		//$result1=$extrafields->addExtraField('series', "type", 'type', 100, '', 'thirdparty',   0, 1, '', $p, 1, '', 1, 0, '', '1', '', '');

		//$result1=$extrafields->addExtraField('paiementsalaire_myattr1', "New Attr 1 label", 'boolean', 1,  3, 'thirdparty',   0, 0, '', '', 1, '', 0, 0, '', '', 'paiementsalaire@paiementsalaire', '$conf->paiementsalaire->enabled');
		//$result2=$extrafields->addExtraField('paiementsalaire_myattr2', "New Attr 2 label", 'varchar', 1, 10, 'project',      0, 0, '', '', 1, '', 0, 0, '', '', 'paiementsalaire@paiementsalaire', '$conf->paiementsalaire->enabled');
		//$result3=$extrafields->addExtraField('paiementsalaire_myattr3', "New Attr 3 label", 'varchar', 1, 10, 'bank_account', 0, 0, '', '', 1, '', 0, 0, '', '', 'paiementsalaire@paiementsalaire', '$conf->paiementsalaire->enabled');
		//$result4=$extrafields->addExtraField('paiementsalaire_myattr4', "New Attr 4 label", 'select',  1,  3, 'thirdparty',   0, 1, '', array('options'=>array('code1'=>'Val1','code2'=>'Val2','code3'=>'Val3')), 1,'', 0, 0, '', '', 'paiementsalaire@paiementsalaire', '$conf->paiementsalaire->enabled');
		//$result5=$extrafields->addExtraField('paiementsalaire_myattr5', "New Attr 5 label", 'text',    1, 10, 'user',         0, 0, '', '', 1, '', 0, 0, '', '', 'paiementsalaire@paiementsalaire', '$conf->paiementsalaire->enabled');

		/*if (type == 'date')
			else if (type == 'datetime')
			else if (type == 'double')
			else if (type == 'int')
			else if (type == 'text')
			else if (type == 'html')
			else if (type == 'varchar')
			else if (type == 'password')
			else if (type == 'boolean')
			else if (type == 'price')
			else if (type == 'select')
			else if (type == 'sellist')   valeur ==> array('options'=>array('convention:nom:rowid::rowid'=>'SELECT rowid, nom FROM llx_convention'))
			else if (type == 'radio')
			else if (type == 'checkbox')
			else if (type == 'chkbxlst')
			else if (type == 'link')
			else if (type == 'separate')*/
		// Permissions
		$this->remove($options);

		$sql = array();

		// Document templates
		$moduledir = dol_sanitizeFileName('paiementsalaire');
		$myTmpObjects = array();
		$myTmpObjects['MyObject'] = array('includerefgeneration'=>0, 'includedocgeneration'=>0);

		foreach ($myTmpObjects as $myTmpObjectKey => $myTmpObjectArray) {
			if ($myTmpObjectKey == 'MyObject') {
				continue;
			}
			if ($myTmpObjectArray['includerefgeneration']) {
				$src = DOL_DOCUMENT_ROOT.'/install/doctemplates/'.$moduledir.'/template_myobjects.odt';
				$dirodt = DOL_DATA_ROOT.'/doctemplates/'.$moduledir;
				$dest = $dirodt.'/template_myobjects.odt';

				if (file_exists($src) && !file_exists($dest)) {
					require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
					dol_mkdir($dirodt);
					$result = dol_copy($src, $dest, 0, 0);
					if ($result < 0) {
						$langs->load("errors");
						$this->error = $langs->trans('ErrorFailToCopyFile', $src, $dest);
						return 0;
					}
				}

				$sql = array_merge($sql, array(
					"DELETE FROM ".MAIN_DB_PREFIX."document_model WHERE nom = 'standard_".strtolower($myTmpObjectKey)."' AND type = '".$this->db->escape(strtolower($myTmpObjectKey))."' AND entity = ".((int) $conf->entity),
					"INSERT INTO ".MAIN_DB_PREFIX."document_model (nom, type, entity) VALUES('standard_".strtolower($myTmpObjectKey)."', '".$this->db->escape(strtolower($myTmpObjectKey))."', ".((int) $conf->entity).")",
					"DELETE FROM ".MAIN_DB_PREFIX."document_model WHERE nom = 'generic_".strtolower($myTmpObjectKey)."_odt' AND type = '".$this->db->escape(strtolower($myTmpObjectKey))."' AND entity = ".((int) $conf->entity),
					"INSERT INTO ".MAIN_DB_PREFIX."document_model (nom, type, entity) VALUES('generic_".strtolower($myTmpObjectKey)."_odt', '".$this->db->escape(strtolower($myTmpObjectKey))."', ".((int) $conf->entity).")"
				));
			}
		}

		return $this->_init($sql, $options);
	}

	/**
	 *  Function called when module is disabled.
	 *  Remove from database constants, boxes and permissions from Dolibarr database.
	 *  Data directories are not deleted
	 *
	 *  @param      string	$options    Options when enabling module ('', 'noboxes')
	 *  @return     int                 1 if OK, 0 if KO
	 */
	public function remove($options = '')
	{
		$sql = array();
		return $this->_remove($sql, $options);
	}

}
