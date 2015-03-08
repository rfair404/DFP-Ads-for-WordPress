<?php
/*
* Plugin Name: DFP Ad Manager
* Plugin URI: http://q21.co/dfpadman-plugin/
* Author: Russell Fair
* Author URI: http://q21.co/
* Description: Adds the ability to add DFP Ad Code to your WordPress site. Note that only Genesis and Theme Hook Alliance compatable themes are currently supported.
* Version: 0.5
* Text Domain: dfpadman
* Domain Path: /languages
* License: GNU General Public License v2 or later
* License URI: http://www.gnu.org/licenses/gpl-2.0.html
*
* License
* This file is part of DFP Ad Manager.
*
* DFP Ad Manager is free software; you can redistribute it and/or modify it under
* the terms of the GNU General Public License version 2, as published by the
* Free Software Foundation.
*
* You may NOT assume that you can use any other version of the GPL.
*
* This program is distributed in the hope that it will be useful, but WITHOUT
* ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details
*
* You should have received a copy of the GNU General Public License along with
* this program; if not, write to:
*
*      Free Software Foundation, Inc.
*      51 Franklin St, Fifth Floor
*      Boston, MA  02110-1301  USA
*
* The license for this software can also likely be found here:
* http://www.gnu.org/licenses/gpl-2.0.html
*/

// translation support
load_plugin_textdomain( 'dfpadman', false, '/languages/' );

require_once('lib/init.php');

// load common class
require_once('lib/common.php');

//load admin class
require_once('lib/admin.php');

//load the theme integration class
require_once('lib/display.php');

//and go
new DFPADMAN_Init();

