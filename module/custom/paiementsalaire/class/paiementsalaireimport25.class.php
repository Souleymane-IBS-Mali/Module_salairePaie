<?php
/* Copyright (C) 2011       Laurent Destailleur <eldy@users.sourceforge.net>
 * Copyright (C) 2016       Raphaël Doursenaud  <rdoursenaud@gpcsolutions.fr>
 * Copyright (C) 2020		Ahmad Jamaly Rabib	<rabib@metroworks.co.jp>
 * Copyright (C) 2021		Frédéric France		<frederic.france@netlogic.fr>
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
 *	\file       htdocs/imports/class/import.class.php
 *	\ingroup    import
 *	\brief      File of class to manage imports
 */

/**
 *	Class to manage imports
 */
class PaiementSalaireImport
{


	/**
	 *    Constructor
	 *
	 *    @param  	DoliDB		$db		Database handler
	 */
	public function __construct($db)
	{
		$this->db = $db;
	}


	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/**
	 *  Load description int this->array_import_module, this->array_import_fields, ... of an importable dataset
	 *
	 *  @param		User	$user      	Object user making import
	 *  @param  	string	$filter		Load a particular dataset only. Index will start to 0.
	 *  @return		int					<0 if KO, >0 if OK
	 */
	public function load_arrays($user, $filter = '')
	{
	}



	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/**
	 *  Build an import example file.
	 *  Arrays this->array_export_xxx are already loaded for required datatoexport
	 *
	 *  @param      string	$model              Name of import engine ('csv', ...)
	 *  @param      string	$headerlinefields   Array of values for first line of example file
	 *  @param      string	$contentlinevalues	Array of values for content line of example file
	 *  @param		string	$datatoimport		Dataset to import
	 *  @return		string						<0 if KO, >0 if OK
	 */
	public function build_example_file($model, $headerlinefields, $contentlinevalues, $datatoimport)
	{
	}

	/**
	 *  Save an export model in database
	 *
	 *  @param		User	$user 	Object user that save
	 *  @return		int				<0 if KO, >0 if OK
	 */
	public function create($user)
	{
	}

	/**
	 *  Load an import profil from database
	 *
	 *  @param		int		$id		Id of profil to load
	 *  @return		int				<0 if KO, >0 if OK
	 */
	public function fetch($id)
	{
	}

	/**
	 *	Delete object in database
	 *
	 *	@param      User	$user        	User that delete
	 *  @param      int		$notrigger	    0=launch triggers after, 1=disable triggers
	 *	@return		int						<0 if KO, >0 if OK
	 */
	public function delete($user, $notrigger = 0)
	{
	}
}
