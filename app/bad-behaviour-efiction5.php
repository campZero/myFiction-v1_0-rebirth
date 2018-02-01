<?php
/*
Bad Behaviour loader for the eFiction 5 script

*/
/*
Bad Behavior - detects and blocks unwanted Web accesses
Copyright (C) 2005,2006,2007,2008,2009,2010,2011,2012 Michael Hampton

Bad Behavior is free software; you can redistribute it and/or modify it under
the terms of the GNU Lesser General Public License as published by the Free
Software Foundation; either version 3 of the License, or (at your option) any
later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE. See the GNU Lesser General Public License for more details.

You should have received a copy of the GNU Lesser General Public License along
with this program. If not, see <http://www.gnu.org/licenses/>.

Please report any problems to bad . bots AT ioerror DOT us
http://bad-behavior.ioerror.us/
*/

###############################################################################
###############################################################################

define('BB2_CWD', realpath( dirname(__FILE__) . "/../lib/" ) );

// Bad Behavior callback functions.

// Return current time in the format preferred by your database.
function bb2_db_date() {
	return gmdate('Y-m-d H:i:s');	// Example is MySQL format
}

// Return affected rows from most recent query.
function bb2_db_affected_rows() {
	global $bb_db;
	return (int)$bb_db->exec("SELECT FOUND_ROWS() as found")[0]['found'];
	return false;
}

// No need to escape strings, we use PDO
function bb2_db_escape($string) {
	return $string;
}

// Return the number of rows in a particular query.
function bb2_db_num_rows($result) {
	if ($result !== FALSE)
		return count($result);
	return 0;
}

// Run a query and return the results, if any.
// Should return FALSE if an error occurred.
// Bad Behavior will use the return value here in other callbacks.
function bb2_db_query($query) {
	global $bb_db;
	
	if ( is_array( $query) )
		return $bb_db->exec($query[0], $query[1]);
	else
		return $bb_db->exec($query);
	//return FALSE;
}

// Return all rows in a particular query.
// Should contain an array of all rows generated by calling mysql_fetch_assoc()
// or equivalent and appending the result of each call to an array.
function bb2_db_rows($result) {
	return $result;
}

// Create the SQL query for inserting a record in the database.
function bb2_insert($settings, $package, $key)
{
	if (!$settings['logging']) return "";

	$sql = "INSERT INTO `{$settings['log_table']}`
		(`ip`, `date`, `request_method`, `request_uri`, `server_protocol`, `http_headers`, `user_agent`, `request_entity`, `key`) VALUES
		(:ip, :date, :request_method, :request_uri, :server_protocol, :headers, :user_agent, :request_entity, :key)";
	$assigns = 
	[
		"ip"				=> bb2_db_escape($package['ip']),
		"date"				=> bb2_db_date(),
		"request_method"	=> bb2_db_escape($package['request_method']),
		"request_uri"		=> bb2_db_escape($package['request_uri']),
		"server_protocol"	=> bb2_db_escape($package['server_protocol']),
		"headers"			=> NULL,
		"user_agent"		=> bb2_db_escape($package['user_agent']),
		"request_entity"	=> "",
		"key"				=> $key,
	];

	$assigns['headers'] = "{$assigns['request_method']} {$assigns['request_uri']} {$assigns['server_protocol']}\n";

	foreach ($package['headers'] as $h => $v) {
		$assigns['headers'] .= bb2_db_escape("$h: $v\n");
	}
	$assigns['request_entity'] = "";
	if (!strcasecmp($assigns['request_method'], "POST")) {
		foreach ($package['request_entity'] as $h => $v) {
			$assigns['request_entity'] .= bb2_db_escape("$h: $v\n");
		}
	}
	
	return [ $sql, $assigns ];
}

// Return emergency contact email address.
function bb2_email() {
	global $config;
	return $config->page_mail;
}

// retrieve whitelist
function bb2_read_whitelist() {
	return parse_ini_file(dirname(BB2_CORE) . "/bb-whitelist.ini");
}

// retrieve settings from database
function bb2_read_settings() {
	global $config;
	$bb_settings = $config->bb2;
	$bb_settings['log_table'] = $config->prefix . 'bad_behavior';
	return $bb_settings;
}

// write settings to database
// this happens in the eFi5 Admin Panel
function bb2_write_settings($bb_settings) {
	return false;
}

// Our log table structure
function bb2_table_structure($name)
{
	$sql = "CREATE TABLE IF NOT EXISTS :name (
		`id` INT(11) NOT NULL auto_increment,
		`ip` TEXT NOT NULL,
		`date` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`request_method` TEXT NOT NULL,
		`request_uri` TEXT NOT NULL,
		`server_protocol` TEXT NOT NULL,
		`http_headers` TEXT NOT NULL,
		`user_agent` TEXT NOT NULL,
		`request_entity` TEXT NOT NULL,
		`key` TEXT NOT NULL,
		INDEX (`ip`(15)),
		INDEX (`user_agent`(10)),
		PRIMARY KEY (`id`) );";	// TODO: INDEX might need tuning
	$assigns = [ ":name" => $name ];
	
	return [ $sql, $assigns ];
}

// installation
function bb2_install() {
	global $bb_settings;
	$sql = bb2_table_structure($bb_settings['log_table']);
	return false;
}

// This will put the bb2 screener into the f3 hive key for javascript
function bb2_insert_head() {
	global $bb2_javascript;
	$f3->set('bb2_javascript', $bb2_javascript);
	return TRUE;
}

// Write stats into the $f3 hive
function bb2_insert_stats($force = false) {
	global $bb_settings, $f3;

	if ($force || $bb_settings['display_stats']) {
		$blocked = bb2_db_query("SELECT COUNT(*) FROM " . $bb_settings['log_table'] . " WHERE `key` NOT LIKE '00000000'");
		if ($blocked !== FALSE) {
			$f3->set('bb2_stats', $blocked[0]["COUNT(*)"]);
		}
	}
}

// Return the top-level relative path of wherever we are (for cookies)
function bb2_relative_path() {
	return \Base::instance()->get('BASE').'/';
}

// Calls inward to Bad Behavor itself.
require_once(BB2_CWD . "/bad-behavior/core.inc.php");

$bb_db = $f3->get('DB');
$bb_settings = bb2_read_settings();

bb2_install();

bb2_start($bb_settings);
// eFiction 5 specific
bb2_insert_stats();