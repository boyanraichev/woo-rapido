<?php
/*
* Woo Rapido
*
* INCLUDES/DB.PHP
* Handles the db tables and operations
*/


if (!defined('ABSPATH')) die;

class Wrapido_DB {

	/** @var The single instance of the class */
	private static $_instance = null;	
	
	// Don't load more than one instance of the class
	public static function get_instance() {
		if ( null == self::$_instance ) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }
    
	/**
     * Constructor
     */
    public function __construct() {
	


	}
	
	public function importCountries() {
		global $wpdb;
		$delete = $wpdb->query("TRUNCATE TABLE $wpdb->rapido_countries");
		$path = WRAPIDO_DIR . 'assets/db/countries.csv';
		$handle = fopen($path, "r");
		
		while ($csvRow = fgetcsv($handle, 1000, ",")) {
			$inserted = $wpdb->insert( 
				$wpdb->rapido_countries, 
				[ 
					'id' => $csvRow[0], 
					'code' => $csvRow[4],
					'name' => $csvRow[2],
					'name_en' => $csvRow[3],
				], 
				[ 
					'%d',
					'%s',
					'%s',
					'%s',
				]
			);
			if (!$inserted) { echo $csvRow[2]; }
		}
		
		fclose($handle);

	}
	
	public function importCities($cities=[]) {
		global $wpdb;
		$delete = $wpdb->query("TRUNCATE TABLE $wpdb->rapido_cities");
		if ($cities) {
			foreach ($cities as $city) {
				$inserted = $wpdb->insert( 
					$wpdb->rapido_cities, 
					[ 
						'id' => $city['SITEID'], 
						'postcode' => $city['POSTCODE'],
						'name' => $city['NAME'],
					], 
					[ 
						'%d',
						'%d',
						'%s',
					]
				);
			}
		}
	}
	
	public function importStreets() {
		// INSERT INTO STREETS (SITEID, STREETID, STREETTYPE, STREETNAME, STREETFULLNAME, STREETTYPE_EN, STREETNAME_EN, STREETFULLNAME_EN, ACTUAL)
		global $wpdb;
		$delete = $wpdb->query("TRUNCATE TABLE $wpdb->rapido_streets");
		$path = WRAPIDO_DIR . 'assets/db/streets.csv';
		$handle = fopen($path, "r");
		
		while ($csvRow = fgetcsv($handle, 250, ",", "'")) {
			$inserted = $wpdb->insert( 
				$wpdb->rapido_streets, 
				[ 
					'id' => $csvRow[1], 
					'city_id' => $csvRow[0],
					'fullname' => $csvRow[4],
					'fullname_en' => $csvRow[7],
				], 
				[ 
					'%d',
					'%d',
					'%s',
					'%s',
				]
			);
			if (!$inserted) { echo $csvRow[4]; }
		}
		
		fclose($handle);
	}
	
	public function importKvartal() {
		//INSERT INTO KVARTALS (SITEID, KVARTALID, KVARTALTYPE, KVARTALNAME, KVARTALFULLNAME, KVARTALTYPE_EN, KVARTALNAME_EN, KVARTALFULLNAME_EN)
		global $wpdb;
		$delete = $wpdb->query("TRUNCATE TABLE $wpdb->rapido_kvartal");
		$path = WRAPIDO_DIR . 'assets/db/kvartal.csv';
		$handle = fopen($path, "r");
		
		while ($csvRow = fgetcsv($handle, 250, ",", "'")) {
			$inserted = $wpdb->insert( 
				$wpdb->rapido_kvartal, 
				[ 
					'id' => $csvRow[1], 
					'city_id' => $csvRow[0],
					'name' => $csvRow[4],
					'name_en' => $csvRow[7],
				], 
				[ 
					'%d',
					'%d',
					'%s',
					'%s',
				]
			);
			if (!$inserted) { echo $csvRow[4]; }
		}
		
		fclose($handle);
	}
	
	public function searchCountryID($code) {
		global $wpdb;

		$resultsql = $wpdb->get_results ( $wpdb->prepare("SELECT id FROM {$wpdb->rapido_countries} WHERE code = '%s'", $code ), 'ARRAY_A');
	
		if ($resultsql) {
			// return 
			return $resultsql[0]['id'];
		} else {
			return false;
		}
	}
	
	public function searchCity($term) {
		global $wpdb;

		$resultsql = $wpdb->get_results ( $wpdb->prepare("SELECT * FROM {$wpdb->rapido_cities} WHERE name LIKE %s LIMIT 5",'%' . $wpdb->esc_like($term) . '%' ), 'ARRAY_A');
	
		if ($resultsql) {
			// return 
			return $resultsql;
		} else {
			return false;
		}
	}
	
	public function searchPostcode($term) {
		global $wpdb;

		$resultsql = $wpdb->get_results ( $wpdb->prepare("SELECT * FROM {$wpdb->rapido_cities} WHERE postcode LIKE %s LIMIT 5", $wpdb->esc_like($term) . '%' ), 'ARRAY_A');
	
		if ($resultsql) {
			// return 
			return $resultsql;
		} else {
			return false;
		}
	}
	
	public function searchKvartal($term,$city_id=68134) {
		global $wpdb;
		
		$resultsql = $wpdb->get_results ( $wpdb->prepare("SELECT * FROM {$wpdb->rapido_kvartal} WHERE city_id = '%d' AND name LIKE %s LIMIT 3", $city_id, '%' . $wpdb->esc_like($term) . '%'), 'ARRAY_A');
	
		if ($resultsql) {
			// return 
			return $resultsql;
		} else {
			return false;
		}
	}
	
	public function searchStreet($term,$city_id=68134) {
		global $wpdb;
		
		$resultsql = $wpdb->get_results ( $wpdb->prepare("SELECT * FROM {$wpdb->rapido_streets} WHERE city_id = '%d' AND fullname LIKE %s LIMIT 4", $city_id, '%' . $wpdb->esc_like($term) . '%' ), 'ARRAY_A');
	
		if ($resultsql) {
			// return 
			return $resultsql;
		} else {
			return false;
		}
	}
	
}


					
