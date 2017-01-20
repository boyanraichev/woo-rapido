<?php
/*
* Woo Rapido
*
* INCLUDES/ADMIN/INSTALL.PHP
* Runs upon plugin activation/deactivation
*/


if (!defined('ABSPATH')) die;

class Wrapido_Admin_Install {
		
	static function activate() {

		$db_version = 1;
		
		add_action( 'activated_plugin', ['Wrapido_Admin_Install','save_error'] );		
		
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		
		global $wpdb;
		
	    $wpdb->rapido_countries = "{$wpdb->prefix}rapido_countries";
	    $wpdb->rapido_cities = "{$wpdb->prefix}rapido_cities";
	    $wpdb->rapido_kvartal = "{$wpdb->prefix}rapido_kvartal";	    
	    $wpdb->rapido_streets = "{$wpdb->prefix}rapido_streets"; 
		
		global $charset_collate;
			
		// get version in wp
		$wp_db_version = get_option( 'wrapido_db_version' );	
			
		// create table for cities
		$sql_create_table = "CREATE TABLE {$wpdb->rapido_cities} (
	          id mediumint(6) unsigned NOT NULL,
	          postcode SMALLINT(6) unsigned,
	          name varchar(50) NOT NULL,          
	          PRIMARY KEY  (id),
	          KEY name (name)
	     ) $charset_collate; ";
	     
		dbDelta( $sql_create_table );
		
		// create table for kvartals
		$sql_create_table_4 = "CREATE TABLE {$wpdb->rapido_kvartal} (
	          id mediumint(6) unsigned NOT NULL,
	          city_id mediumint(6) unsigned NOT NULL,
	          name varchar(50) NOT NULL,
	          name_en varchar(50),     
	          PRIMARY KEY  (id),
	          KEY street (city_id, name)
	     ) $charset_collate; ";
	    
	    dbDelta( $sql_create_table_4 );
	    
		// create table for streets
		$sql_create_table_2 = "CREATE TABLE {$wpdb->rapido_streets} (
	          id mediumint(6) unsigned NOT NULL,
	          city_id mediumint(6) unsigned NOT NULL,
	          fullname varchar(50) NOT NULL,
	          fullname_en varchar(50),     
	          PRIMARY KEY  (id),
	          KEY street (city_id, fullname)
	     ) $charset_collate; ";
	     
		dbDelta( $sql_create_table_2 );
		
		// create table for streets
		$sql_create_table_3 = "CREATE TABLE {$wpdb->rapido_countries} (
	          id mediumint(6) unsigned NOT NULL,
	          code varchar(2) NOT NULL,
	          name varchar(40) NOT NULL,
	          name_en varchar(40),     
	          PRIMARY KEY  (id),
	          KEY street (code)
	     ) $charset_collate; ";
	     
		dbDelta( $sql_create_table_3 );
		
		if ($wp_db_version!=$db_version) {
			// update tables
		}
		
		update_option( 'wrapido_db_version', $db_version );

	}
	
	static function deactivate() {

	}	
	
	static function uninstall() {
		global $wpdb;
		// remove tables on deactivation if options is on
	    $wpdb->query("DROP TABLE IF EXISTS $wpdb->rapido_countries");
	    $wpdb->query("DROP TABLE IF EXISTS $wpdb->rapido_cities");
	    $wpdb->query("DROP TABLE IF EXISTS $wpdb->rapido_kvartal");	    
	    $wpdb->query("DROP TABLE IF EXISTS $wpdb->rapido_streets");    
	    //Remove the database version
	    delete_option('wrapido_db_version');
	}
	
	static function save_error() {
	    update_option( 'wrapido_activate_error',  ob_get_contents(), false );
	}	
	
}	






