<?php
/*
Plugin Name: Plugin Framework
Plugin URI: http://memberbuddy.com/plugins/framework-api/
Description: A shared class of functions to be used as a base for creating other plugins
Author: Rob Holmes
Author URI: http://memberbuddy.com/people
Version: 0.0.1
Tags: Wordpress
License: GPL2

Copyright 2011  Rob Holmes (email : rob@onemanonelaptop.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

// define the abstract framework class, sadly this isn't enough to make sure its available to other plugins. 

include_once('framework.php');
?>