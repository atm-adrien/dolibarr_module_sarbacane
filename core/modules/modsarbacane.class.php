<?php
/* Copyright (C) 2021 ATM Consulting <support@atm-consulting.fr>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * 	\defgroup   sarbacane     Module sarbacane
 *  \brief      Example of a module descriptor.
 *				Such a file must be copied into htdocs/sarbacane/core/modules directory.
 *  \file       htdocs/sarbacane/core/modules/modsarbacane.class.php
 *  \ingroup    sarbacane
 *  \brief      Description and activation file for module sarbacane
 */
include_once DOL_DOCUMENT_ROOT .'/core/modules/DolibarrModules.class.php';


/**
 *  Description and activation class for module sarbacane
 */
class modsarbacane extends DolibarrModules
{
	/**
	 *   Constructor. Define names, constants, directories, boxes, permissions
	 *
	 *   @param      DoliDB		$db      Database handler
	 */
	public function __construct($db)
	{
        global $langs,$conf;

        $this->db = $db;

		$this->editor_name = 'ATM-Consulting';
		$this->editor_url = 'https://www.atm-consulting.fr';

		// Id for module (must be unique).
		// Use here a free id (See in Home -> System information -> Dolibarr for list of used modules id).
		$this->numero = 104305; // 104000 to 104999 for ATM CONSULTING
		// Key text used to identify module (for permissions, menus, etc...)
		$this->rights_class = 'sarbacane';

		// Family can be 'crm','financial','hr','projects','products','ecm','technic','other'
		// It is used to group modules in module setup page
		$this->family = "ATM";
		// Module label (no space allowed), used if translation string 'ModuleXXXName' not found (where XXX is value of numeric property 'numero' of module)
		$this->name = preg_replace('/^mod/i', '', get_class($this));
		// Module description, used if translation string 'ModuleXXXDesc' not found (where XXX is value of numeric property 'numero' of module)
		$this->description = "Description of module sarbacane";
		// Possible values for version are: 'development', 'experimental', 'dolibarr' or version
		$this->version = '1.0.1';
		// Key used in llx_const table to save module status enabled/disabled (where SARBACANE is value of property name of module in uppercase)
		$this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);
		// Where to store the module in setup page (0=common,1=interface,2=others,3=very specific)
		$this->special = 0;
		// Name of image file used for this module.
		// If file is in theme/yourtheme/img directory under name object_pictovalue.png, use this->picto='pictovalue'
		// If file is in module/img directory under name object_pictovalue.png, use this->picto='pictovalue@module'
		$this->picto='sarbacane@sarbacane';

		// Defined all module parts (triggers, login, substitutions, menus, css, etc...)
		// for default path (eg: /sarbacane/core/xxxxx) (0=disable, 1=enable)
		// for specific path of parts (eg: /sarbacane/core/modules/barcode)
		// for specific css file (eg: /sarbacane/css/sarbacane.css.php)
		//$this->module_parts = array(
		//                        	'triggers' => 0,                                 	// Set this to 1 if module has its own trigger directory (core/triggers)
		//							'login' => 0,                                    	// Set this to 1 if module has its own login method directory (core/login)
		//							'substitutions' => 0,                            	// Set this to 1 if module has its own substitution function file (core/substitutions)
		//							'menus' => 0,                                    	// Set this to 1 if module has its own menus handler directory (core/menus)
		//							'theme' => 0,                                    	// Set this to 1 if module has its own theme directory (theme)
		//                        	'tpl' => 0,                                      	// Set this to 1 if module overwrite template dir (core/tpl)
		//							'barcode' => 0,                                  	// Set this to 1 if module has its own barcode directory (core/modules/barcode)
		//							'models' => 0,                                   	// Set this to 1 if module has its own models directory (core/modules/xxx)
		//							'css' => array('/sarbacane/css/sarbacane.css.php'),	// Set this to relative path of css file if module has its own css file
	 	//							'js' => array('/sarbacane/js/sarbacane.js'),          // Set this to relative path of js file if module must load a js on all pages
		//							'hooks' => array('hookcontext1','hookcontext2')  	// Set here all hooks context managed by module
		//							'dir' => array('output' => 'othermodulename'),      // To force the default directories names
		//							'workflow' => array('WORKFLOW_MODULE1_YOURACTIONTYPE_MODULE2'=>array('enabled'=>'! empty($conf->module1->enabled) && ! empty($conf->module2->enabled)', 'picto'=>'yourpicto@sarbacane')) // Set here all workflow context managed by module
		//                        );
		$this->module_parts = array(
			'hooks' => array(
				'contactcard'
			),
			'triggers' => 1
		);

		// Data directories to create when module is enabled.
		// Example: this->dirs = array("/sarbacane/temp");
		$this->dirs = array();

		// Config pages. Put here list of php page, stored into sarbacane/admin directory, to use to setup module.
		$this->config_page_url = array("sarbacane_setup.php@sarbacane");

		// Dependencies
		$this->hidden = false;			// A condition to hide module
		$this->depends =  array(
				"modMailing"
		);;		// List of modules id that must be enabled if this module is enabled
		$this->requiredby = array();	// List of modules id to disable if this one is disabled
		$this->conflictwith = array();	// List of modules id this module is in conflict with
		$this->phpmin = array(5,0);					// Minimum version of PHP required by module
		$this->need_dolibarr_version = array(3,0);	// Minimum version of Dolibarr required by module
		$this->langfiles = array("sarbacane@sarbacane");

		// Constants
		// List of particular constants to add when module is enabled (key, 'chaine', value, desc, visible, 'current' or 'allentities', deleteonunactive)
		// Example: $this->const=array(0=>array('SARBACANE_MYNEWCONST1','chaine','myvalue','This is a constant to add',1),
		//                             1=>array('SARBACANE_MYNEWCONST2','chaine','myvalue','This is another constant to add',0, 'current', 1)
		// );
		$this->const = array();

		// Array to add new pages in new tabs
		// Example: $this->tabs = array('objecttype:+tabname1:Title1:sarbacane@sarbacane:$user->rights->sarbacane->read:/sarbacane/mynewtab1.php?id=__ID__',  	// To add a new tab identified by code tabname1
        //                              'objecttype:+tabname2:Title2:sarbacane@sarbacane:$user->rights->othermodule->read:/sarbacane/mynewtab2.php?id=__ID__',  	// To add another new tab identified by code tabname2
        //                              'objecttype:-tabname:NU:conditiontoremove');                                                     						// To remove an existing tab identified by code tabname
		// where objecttype can be
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
        $this->tabs = array(
                'emailing:+tabSarbacaneSending:SarbacaneSending:sarbacane@sarbacane:$user->rights->mailing->creer:/sarbacane/sarbacane.php?id=__ID__'
                ,'contact:+tabSarbacaneSending:SarbacaneSending:sarbacane@sarbacane:$user->rights->mailing->creer:/sarbacane/contact_tab.php?id=__ID__'
		);

        // Dictionaries
	    if (! isset($conf->sarbacane->enabled))
        {
        	$conf->sarbacane=new stdClass();
        	$conf->sarbacane->enabled=0;
        }
		$this->dictionaries=array();
        /* Example:
        if (! isset($conf->sarbacane->enabled)) $conf->sarbacane->enabled=0;	// This is to avoid warnings
        $this->dictionaries=array(
            'langs'=>'sarbacane@sarbacane',
            'tabname'=>array(MAIN_DB_PREFIX."table1",MAIN_DB_PREFIX."table2",MAIN_DB_PREFIX."table3"),		// List of tables we want to see into dictonnary editor
            'tablib'=>array("Table1","Table2","Table3"),													// Label of tables
            'tabsql'=>array('SELECT f.rowid as rowid, f.code, f.label, f.active FROM '.MAIN_DB_PREFIX.'table1 as f','SELECT f.rowid as rowid, f.code, f.label, f.active FROM '.MAIN_DB_PREFIX.'table2 as f','SELECT f.rowid as rowid, f.code, f.label, f.active FROM '.MAIN_DB_PREFIX.'table3 as f'),	// Request to select fields
            'tabsqlsort'=>array("label ASC","label ASC","label ASC"),																					// Sort order
            'tabfield'=>array("code,label","code,label","code,label"),																					// List of fields (result of select to show dictionary)
            'tabfieldvalue'=>array("code,label","code,label","code,label"),																				// List of fields (list of fields to edit a record)
            'tabfieldinsert'=>array("code,label","code,label","code,label"),																			// List of fields (list of fields for insert)
            'tabrowid'=>array("rowid","rowid","rowid"),																									// Name of columns with primary key (try to always name it 'rowid')
            'tabcond'=>array($conf->sarbacane->enabled,$conf->sarbacane->enabled,$conf->sarbacane->enabled)												// Condition to show each dictionary
        );
        */

        // Boxes
		// Add here list of php file(s) stored in core/boxes that contains class to show a box.
        $this->boxes = array();			// List of boxes
		// Example:
		//$this->boxes=array(array(0=>array('file'=>'myboxa.php','note'=>'','enabledbydefaulton'=>'Home'),1=>array('file'=>'myboxb.php','note'=>''),2=>array('file'=>'myboxc.php','note'=>'')););

		// Permissions
		$this->rights = array();		// Permission array used by this module
		$r=0;

		// Add here list of permission defined by an id, a label, a boolean and two constant strings.
		// Example:
		// $this->rights[$r][0] = $this->numero . $r;	// Permission id (must not be already used)
		// $this->rights[$r][1] = 'Permision label';	// Permission label
		// $this->rights[$r][3] = 0; 					// Permission by default for new user (0/1)
		// $this->rights[$r][4] = 'level1';				// In php code, permission will be checked by test if ($user->rights->permkey->level1->level2)
		// $this->rights[$r][5] = 'level2';				// In php code, permission will be checked by test if ($user->rights->permkey->level1->level2)
		// $r++;

		$this->rights[$r][0] = $this->numero . $r;	// Permission id (must not be already used)
		$this->rights[$r][1] = 'sarbacane_read';	// Permission label
		$this->rights[$r][3] = 0; 					// Permission by default for new user (0/1)
		$this->rights[$r][4] = 'contact_tab_read';				// In php code, permission will be checked by test if ($user->rights->permkey->level1->level2)
		$this->rights[$r][5] = '';				// In php code, permission will be checked by test if ($user->rights->permkey->level1->level2)
		$r++;
/*
		$this->rights[$r][0] = $this->numero . $r;	// Permission id (must not be already used)
		$this->rights[$r][1] = 'sarbacane_write';	// Permission label
		$this->rights[$r][3] = 0; 					// Permission by default for new user (0/1)
		$this->rights[$r][4] = 'write';				// In php code, permission will be checked by test if ($user->rights->permkey->level1->level2)
		$this->rights[$r][5] = '';				// In php code, permission will be checked by test if ($user->rights->permkey->level1->level2)
		$r++;

		$this->rights[$r][0] = $this->numero . $r;	// Permission id (must not be already used)
		$this->rights[$r][1] = 'sarbacane_delete';	// Permission label
		$this->rights[$r][3] = 0; 					// Permission by default for new user (0/1)
		$this->rights[$r][4] = 'delete';		    // In php code, permission will be checked by test if ($user->rights->permkey->level1->level2)
		$this->rights[$r][5] = '';				// In php code, permission will be checked by test if ($user->rights->permkey->level1->level2)
		$r++;
*/

		// Main menu entries
		$this->menu = array();			// List of menus to add
		$r=0;

		// Add here entries to declare new menus
		//
		// Example to declare a new Top Menu entry and its Left menu entry:
		// $this->menu[$r]=array(	'fk_menu'=>'fk_mainmenu=sarbacane',		// Put 0 if this is a single top menu or keep fk_mainmenu to give an entry on left
		//							'type'=>'top',			                // This is a Top menu entry
		//							'titre'=>'sarbacane top menu',
		//							'mainmenu'=>'sarbacane',
		//							'leftmenu'=>'sarbacane_left',			// This is the name of left menu for the next entries
		//							'url'=>'/sarbacane/pagetop.php',
		//							'langs'=>'sarbacane@sarbacane',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
		//							'position'=>100,
		//							'enabled'=>'$conf->sarbacane->enabled',	// Define condition to show or hide menu entry. Use '$conf->sarbacane->enabled' if entry must be visible if module is enabled.
		//							'perms'=>'1',			                // Use 'perms'=>'$user->rights->sarbacane->level1->level2' if you want your menu with a permission rules
		//							'target'=>'',
		//							'user'=>0);				                // 0=Menu for internal users, 1=external users, 2=both
		// $r++;
		//
		// Example to declare a Left Menu entry into an existing Top menu entry:
		// $this->menu[$r]=array(	'fk_menu'=>'fk_mainmenu=sarbacane,fk_leftmenu=sarbacane_left',		    // Use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
		//							'type'=>'left',			                // This is a Left menu entry
		//							'titre'=>'sarbacane left menu',
		//							'mainmenu'=>'sarbacane',
		//							'leftmenu'=>'sarbacane_left',			// Goes into left menu previously created by the mainmenu
		//							'url'=>'/sarbacane/pagelevel2.php',
		//							'langs'=>'sarbacane@sarbacane',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
		//							'position'=>100,
		//							'enabled'=>'$conf->sarbacane->enabled',  // Define condition to show or hide menu entry. Use '$conf->sarbacane->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
		//							'perms'=>'1',			                // Use 'perms'=>'$user->rights->sarbacane->level1->level2' if you want your menu with a permission rules
		//							'target'=>'',
		//							'user'=>0);				                // 0=Menu for internal users, 1=external users, 2=both
		// $r++;

    $this->menu[$r] = array(
				'fk_menu' => 0,
				'type' => 'top',
				'titre' => 'Module104305Name',
				'mainmenu' => 'sarbacane',
				'leftmenu' => '1',
				'url' => '/sarbacane/list.php',
				'langs' => 'sarbacane@sarbacane',
				'position' => 100,
				'enabled' => '1',
				'perms' => '$user->rights->mailing->lire',
				'target' => '',
				'user' => 2
		);
		$r ++;
		$this->menu[$r] = array(
				'fk_menu' => 'fk_mainmenu=sarbacane',
				'type' => 'left',
				'titre' => 'Module104305Name',
				'leftmenu' => 'SarbacaneMenu',
				'url' => '/sarbacane/list.php',
				'langs' => 'sarbacane@sarbacane',
				'position' => 100,
				'enabled' => '$user->rights->mailing->lire',
				'perms' => '$user->rights->mailing->lire',
				'target' => '',
				'user' => 2
		);
		$r ++;

		$this->menu[$r] = array(
				'fk_menu' => 'fk_mainmenu=sarbacane,fk_leftmenu=SarbacaneMenu',
				'type' => 'left',
				'titre' => 'SarbacaneDestList',
				'url' => '/sarbacane/list.php',
				'langs' => 'sarbacane@sarbacane',
				'position' => 101,
				'enabled' => '$user->rights->mailing->lire',
				'perms' => '$user->rights->mailing->lire',
				'target' => '',
				'user' => 2
		);
		$r ++;

		$this->menu[$r] = array(
				'fk_menu' => 'fk_mainmenu=sarbacane,fk_leftmenu=SarbacaneMenu',
				'type' => 'left',
				'titre' => 'NewMailing',
				'url' =>'/comm/mailing/card.php?leftmenu=mailing&action=create',
				'langs' => 'mails',
				'position' => 105,
				'enabled' => '$user->rights->mailing->creer',
				'perms' => '$user->rights->mailing->creer',
				'target' => '',
				'user' => 2
		);
		$r ++;

		// Exports
		$r=1;

		// Example:
		// $this->export_code[$r]=$this->rights_class.'_'.$r;
		// $this->export_label[$r]='CustomersInvoicesAndInvoiceLines';	// Translation key (used only if key ExportDataset_xxx_z not found)
        // $this->export_enabled[$r]='1';                               // Condition to show export in list (ie: '$user->id==3'). Set to 1 to always show when module is enabled.
		// $this->export_permission[$r]=array(array("facture","facture","export"));
		// $this->export_fields_array[$r]=array('s.rowid'=>"IdCompany",'s.nom'=>'CompanyName','s.address'=>'Address','s.zip'=>'Zip','s.town'=>'Town','s.fk_pays'=>'Country','s.phone'=>'Phone','s.siren'=>'ProfId1','s.siret'=>'ProfId2','s.ape'=>'ProfId3','s.idprof4'=>'ProfId4','s.code_compta'=>'CustomerAccountancyCode','s.code_compta_fournisseur'=>'SupplierAccountancyCode','f.rowid'=>"InvoiceId",'f.facnumber'=>"InvoiceRef",'f.datec'=>"InvoiceDateCreation",'f.datef'=>"DateInvoice",'f.total'=>"TotalHT",'f.total_ttc'=>"TotalTTC",'f.tva'=>"TotalVAT",'f.paye'=>"InvoicePaid",'f.fk_statut'=>'InvoiceStatus','f.note'=>"InvoiceNote",'fd.rowid'=>'LineId','fd.description'=>"LineDescription",'fd.price'=>"LineUnitPrice",'fd.tva_tx'=>"LineVATRate",'fd.qty'=>"LineQty",'fd.total_ht'=>"LineTotalHT",'fd.total_tva'=>"LineTotalTVA",'fd.total_ttc'=>"LineTotalTTC",'fd.date_start'=>"DateStart",'fd.date_end'=>"DateEnd",'fd.fk_product'=>'ProductId','p.ref'=>'ProductRef');
		// $this->export_entities_array[$r]=array('s.rowid'=>"company",'s.nom'=>'company','s.address'=>'company','s.zip'=>'company','s.town'=>'company','s.fk_pays'=>'company','s.phone'=>'company','s.siren'=>'company','s.siret'=>'company','s.ape'=>'company','s.idprof4'=>'company','s.code_compta'=>'company','s.code_compta_fournisseur'=>'company','f.rowid'=>"invoice",'f.facnumber'=>"invoice",'f.datec'=>"invoice",'f.datef'=>"invoice",'f.total'=>"invoice",'f.total_ttc'=>"invoice",'f.tva'=>"invoice",'f.paye'=>"invoice",'f.fk_statut'=>'invoice','f.note'=>"invoice",'fd.rowid'=>'invoice_line','fd.description'=>"invoice_line",'fd.price'=>"invoice_line",'fd.total_ht'=>"invoice_line",'fd.total_tva'=>"invoice_line",'fd.total_ttc'=>"invoice_line",'fd.tva_tx'=>"invoice_line",'fd.qty'=>"invoice_line",'fd.date_start'=>"invoice_line",'fd.date_end'=>"invoice_line",'fd.fk_product'=>'product','p.ref'=>'product');
		// $this->export_sql_start[$r]='SELECT DISTINCT ';
		// $this->export_sql_end[$r]  =' FROM ('.MAIN_DB_PREFIX.'facture as f, '.MAIN_DB_PREFIX.'facturedet as fd, '.MAIN_DB_PREFIX.'societe as s)';
		// $this->export_sql_end[$r] .=' LEFT JOIN '.MAIN_DB_PREFIX.'product as p on (fd.fk_product = p.rowid)';
		// $this->export_sql_end[$r] .=' WHERE f.fk_soc = s.rowid AND f.rowid = fd.fk_facture';
		// $this->export_sql_order[$r] .=' ORDER BY s.nom';
		// $r++;

		include_once DOL_DOCUMENT_ROOT . '/cron/class/cronjob.class.php';
		// CRON jobs
		$this->cronjobs = array(
			array(
				'jobtype' => 'method',
				'class' => 'sarbacane/class/dolsarbacane.class.php',
				'objectname' => 'DolSarbacane',
				'frequency' => 24,
				'unitfrequency' => 3600,
				'status' => Cronjob::STATUS_DISABLED,
				'test' => true,
				'method' => 'CRONupdateCampaignRecipientStats',
				'comment' => $langs->trans('SarbacaneUpdateRecipientStats'),
				'label' => $langs->trans('SarbacaneUpdateRecipientStats'),
				'datestart' => strtotime('tomorrow 00:00:01'),
			)
		);

	}

	/**
	 *		Function called when module is enabled.
	 *		The init function add constants, boxes, permissions and menus (defined in constructor) into Dolibarr database.
	 *		It also creates data directories
	 *
     *      @param      string	$options    Options when enabling module ('', 'noboxes')
	 *      @return     int             	1 if OK, 0 if KO
	 */
	public function init($options = '')
	{
		global $langs;
		$sql = array();

		if (version_compare($this->version, '1.0.1') < 0) {
			$sql[] = "ALTER TABLE `".MAIN_DB_PREFIX."sarbacane_campaign_contact` ADD `statut` integer NOT NULL DEFAULT 0, ADD `nb_click` INT NOT NULL DEFAULT '0' AFTER `statut`, ADD `nb_open` INT NOT NULL DEFAULT '0' AFTER `nb_click`, ADD `npai` VARCHAR(255) AFTER `nb_open`, ADD `unsubscribe` BOOLEAN NOT NULL DEFAULT FALSE AFTER `npai`, ADD `unsubscribed_email` VARCHAR(255) NULL AFTER `unsubscribe`, ADD `used_blacklist` VARCHAR(255) NULL AFTER `unsubscribed_email`;";
			$sql[] = "ALTER TABLE `".MAIN_DB_PREFIX."sarbacane` ADD `sarbacane_blacklistid` VARCHAR(200) NOT NULL DEFAULT 'DEFAULT_BLACKLIST' AFTER `sarbacane_listid`;";
		}

		$e = new ExtraFields($this->db);
		$ret = $e->addExtraField('average_status', 'SarbAverageStatus', 'varchar', '100', '255', 'socpeople', 0, 0, '', '', 1, '', 5);
		$ret = $e->addExtraField('sarb_npai', 'NPAI', 'boolean', '410', '', 'socpeople', 0, 0, '', '', 1, '', 1);

		define('INC_FROM_DOLIBARR', true);

		$result=$this->_load_tables('/sarbacane/sql/');

		return $this->_init($sql, $options);
	}

	/**
	 *		Function called when module is disabled.
	 *      Remove from database constants, boxes and permissions from Dolibarr database.
	 *		Data directories are not deleted
	 *
     *      @param      string	$options    Options when enabling module ('', 'noboxes')
	 *      @return     int             	1 if OK, 0 if KO
	 */
    public function remove($options = '')
    {
		$sql = array();

		return $this->_remove($sql, $options);
    }

}
