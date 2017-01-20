<?php
/*
* Woo Rapido
*
* INCLUDES/RAPIDO.PHP
* Handles the communication with Rapido
*/

if (!defined('ABSPATH')) die;


class Wrapido_Rapido {
	
	var $test = true;
	
	var $test_url = 'https://www.rapido.bg/testsystem/schema.wsdl';

	var $api_url = 'https://www.rapido.bg/rsystem2/schema.wsdl';
		
	var $api_user = '';
	
	var $api_pass = '';
	
	var $location = '';
	
	var $service1 = '';
	
	var $service2 = '';
	
	var $loginParam = null;
	
	var $client = null;
	

	/** @var The single instance of the class */
	private static $_instance = null;	
	
	// Don't load more than one instance of the class
	public static function get_instance() {
		if ( null == self::$_instance ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    /**
     * Constructor
     */
    public function __construct() {
    
    }
    
    /**
     * set up data
     */
    public function setUpSoapFromDB() {
    	
    	$settings = get_option('woocommerce_rapido_settings');
    	
    	$mode = ( isset($settings['test']) ? $settings['test'] : 'yes' );
    	$user = ( isset($settings['api_user']) ? $settings['api_user'] : '' );
    	$pass = ( isset($settings['api_pass']) ? $settings['api_pass'] : '' );
    	$location = ( isset($settings['location']) ? $settings['location'] : 68134 );
    	$service1 = ( isset($settings['service1']) ? $settings['service1'] : 5 );
    	$service2 = ( isset($settings['service2']) ? $settings['service2'] : 9 );
    	
    	$this->setUpSoap($mode,$user,$pass,$location,$service1,$service2);
    	
    }
    
	/**
     * set up data
     */
    public function setUpSoap($mode,$user,$pass,$location=68134,$service1=5,$service2=9) {
    	if (empty($this->client)) {
	    	$this->test = ( $mode=='yes' ? true : false );
	    	$this->api_user = sanitize_text_field($user);
	    	$this->api_pass = sanitize_text_field($pass);
	    	
	    	$this->location = intval($location);
	    	$this->service1 = intval($service1);
	    	$this->service2 = intval($service2);
	    	
	    	$this->loginParam = new stdClass();
			$this->loginParam->user = $this->api_user;   
			$this->loginParam->pass = $this->api_pass;
			
			if ($this->test) {
				$this->client = @new SoapClient($this->test_url);
			} else {
				$this->client = @new SoapClient($this->api_url);
			}
		}
    }
    
    /**
     * Get all countries
     */
    public function getCountries() {
    	$countries = $this->client->getCountries($this->loginParam);
    	if ($countries) {
    		return $countries;
    	}
    	return [];
    }
    
    /**
     * Get all cities for Bulgaria
     */
    public function getCities($term='') {
    	$cities = $this->client->getCityes($this->loginParam,100);
    	if ($cities) {
    		return $cities;
    	}
    	return [];
    }
    
    /**
     * Get all cities for Bulgaria
     */
    public function getStreets($siteid=0) {
    	// $streets = $this->client->getStreets($this->loginParam,$siteid);
    	if ($streets) {
    		return $streets;
    	}
    	return [];
    }
    
    /**
     * Get all cities for Bulgaria
     */
    public function getOffices($city_id=68134) {
    	$offices = $this->client->getOfficesCity($this->loginParam,$city_id);
    	if ($offices) {
    		return $offices;
    	}
    	return [];
    }
    
    /**
     * Get all services
     */
    public function getServices() {
	    return $this->client->getServices($this->loginParam);
	}
	   
	/**
     * Get all subservices
     */
    public function getSubServices($service) {
	    return $this->client->getSubServices($this->loginParam,$service);
	}
     
    /**
     * Calculate price
     */
    public function calculatePrice($total,$weight,$service) {
    	/*
    	София - service 1 + subservice 18 (48 ч) или 5 (24 ч)
    	Провинция - service 2 + subservice 9 пакети икономична
    	Чужбина - service 3 
    	Поща - service 4 + subservice 13 48ч подпис
    	*/
    	$myPriceArray = [];
    	switch ($service) {
    		case '1':
    			$myPriceArray['service']=1;
    			$myPriceArray['subservice']=$this->service1; // 24 часа 
    			break;
    		case '2':
    		default:	
    			$myPriceArray['service']=2;
    			$myPriceArray['subservice']=$this->service2; // пакет експрес
    			break;
    		case '3':
				$myPriceArray['service']=3;
    			$myPriceArray['subservice']=0;
    			break;
    	}
		$myPriceArray['fix_chas']=0; // 0 или 1 (Съответно ако е 1 ще се ползва фиксиран час, а ако е 0 няма да е със фиксиран час.
		$myPriceArray['return_receipt']=0; // 0 или 1 (Съответно ако е 1 ще се иска обратна разписка, и 0 за без
		$myPriceArray['return_doc']=0; // 0 или 1 (Съответно ако е 1 ще има обратни документи, и 0 за без
		$myPriceArray['nal_platej']=$total; // Сума за наложен платеж. Оставя се 0 ако е без наложен платеж. За десетичната запетая се използва символа точка "."
		$myPriceArray['zastrahovka']=0; // Сума за застраховка. Оставя се 0 ако е без застраховка. За десетичната запетая се използва символа точка "."
		$myPriceArray['teglo']=$weight; // Тегло. За десетичната запетая се използва символа точка "."
		//$myPriceArray['country_b']=100; // ID на държава(по ISO). Подава се само ако е международна пратка.
		$result = $this->client->calculate($this->loginParam,$myPriceArray);
		
		if ($result['PERROR']=='0') {
			return $result['TOTAL']; 
		}
		return 0;

    }
    
    
    public function createShipment($address,$weight=1,$due=0,$insurance='',$fragile=0,$content='стоки') {

		$order_info = [];

		if ( isset($address['country_id']) AND $address['country_id']!=100 ) {
			$order_info['service']=3;
			$order_info['subservice']=0;
		} else {
	    	if ( isset($address['city_id']) AND $address['city_id']==$this->location ) {
	    		$order_info['service']=1;
				$order_info['subservice']=$this->service1;
	    	} else {
	    		$order_info['service']=2;
				$order_info['subservice']=$this->service2;
	    	}
		}    	

		$order_info['nal_platej'] = $due; //[NUMERIC 8,2] Сума за наложен платеж. Оставя се 0 ако е без наложен платеж
		$order_info['zastrahovka'] = $insurance; //[NUMERIC 8,2] Сума за застраховка. Оставя се празно ако е без застраховка
		$order_info['CONTENT'] = $content; //[R] [VARCHAR 200] Съдържание на партката
		$order_info['CHUPLIVO'] = $fragile; //[SMALLINT] 0 или 1 (Съответно ако е 1 е чупливо)
		$order_info['teglo'] = $weight; //[R] [NUMERIC 8,3] Тегло в кг. (примерно 0.25 са 250 грама)
		
		$order_info['RECEIVER'] = $address['receiver']; //[R] [VARCHAR 150] Получател
		$order_info['COUNTRY_B'] = $address['country_id']; //[R] [INT32] ID на държава(по ISO). България е 100
		$order_info['CITY_B'] = $address['city']; //[R] [VARCHAR 50] Име на населено място
		$order_info['SITEID_B'] = $address['city_id']; //[R] [INT32] ID на населено място
		$order_info['PK_B'] = $address['zip']; //[R] [VARCHAR 5] Пощенски код на населено място

		if (!empty($address['office'])) {
			$order_info['TAKEOFFICE'] = $address['office'];
		} elseif (!empty($address['street_id'])) {
			$order_info['STREET_B'] = $address['street']; //[VARCHAR 50] Наименование на улица
			$order_info['STREETB_ID'] = $address['street_id']; //[INT32] ID на улица
			$order_info['STREETB_TYPE'] = $address['street_type']; //[INT32] тип на улица (1 - улица; 2 - квартал)
			$order_info['STREET_NO_B'] = $address['street_no']; //[VARCHAR 5] Номер на улица
			$order_info['BLOCK_B'] = $address['building_no']; //[VARCHAR 5] Номер на блок
			$order_info['ENTRANCE_B'] = $address['building_en']; //[VARCHAR 3] Вход
			$order_info['FLOOR_B'] = $address['building_fl']; //[VARCHAR 10] Етаж
			$order_info['APARTMENT_B'] = $address['building_ap']; //[VARCHAR 3] Апартамент
		}

		$order_info['ADDITIONAL_INFO_B'] = $address['info']; //[VARCHAR 250] Допълнителни уточнения
		$order_info['PHONE_B'] = $address['phone']; //[R] [VARCHAR 40] Телефон
		$order_info['CPERSON_B'] = ''; //[VARCHAR 100] Лице за контакт
		$order_info['fix_chas'] = ''; //[VARCHAR 40] Фиксиран час. Оставя се празно '' ако не се ползва услуга "фиксиран час"
		$order_info['return_receipt'] = 0; //[SMALLINT] Обратна разписка: 0 или 1 (Съответно 1 за ДА)
		$order_info['return_doc'] = 0; //[SMALLINT] Обратни документи: 0 или 1 (Съответно 1 за ДА)
		$order_info['PACK_COUNT'] = 1; //[R] [SMALLINT] Брой пакети
		$order_info['CENOVA_LISTA'] = 1; // по ценова листа на изпращача
		$order_info['SUBOTEN_RAZNOS'] = 0; // [SMALLINT] [SMALLINT] Съботен разнос: 0 или 1 (Съответно 1 за ДА)
		$order_info['CHECK_BEFORE_PAY'] = 0; // [SMALLINT] [SMALLINT] Провери преди да платиш: 0 или 1 (Съответно 1 за ДА)
		$order_info['CLIENT_REF1'] = $address['ref']; //[VARCHAR 30] Клиентски референтен номер. Примерно номер на поръчка във вашата система / номер на ваша фактура / номер на модел или тип стока
		// print_r($order_info); die();
		$result = $this->client->create_order($this->loginParam,$order_info);

		return $result;
    }
    
    public function printShipment($tracking) {
    
    	if (!empty($tracking)) {
			
			$result = $this->client->print_pdf($this->loginParam,$tracking);
			
			if (!empty($result)) {
				
				$pdf = base64_decode($result);

				header('Content-Description: File Transfer');
				header('Content-Type: application/pdf');
				header('Content-Disposition: attachment; filename="rapido.pdf"');
				header('Pragma: public');
				//header('Content-Length: ' . filesize($pdf));
				header('Cache-Control: no-cache, must-revalidate');
				header('Expires: 0');
				
				echo $pdf;
			}
			
		}
    	
    	die('error shipment number');
    }
    
    public function del_shipment($tracking) {
    	
    } 
    
}