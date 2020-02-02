<?php
/**
 * Przelewy24 comunication class
 *
 * @author DialCom24 Sp. z o.o.
 * @copyright DialCom24 Sp. z o.o.
 * @version 1.1
 * @since 2014-04-29
 */

/**
 *
 * Communication protol version
 * @var double
 */
define('P24_VERSION', '3.2');
if (class_exists('Przelewy24Class', false)!=true) {
class Przelewy24Class {
    /**
     * Live system URL address
     * @var string
     */
    private static $hostLive    = 'https://secure.przelewy24.pl/';
    /**
     * Sandbox system URL address
     * @var string
     */
    private static $hostSandbox = 'https://sandbox.przelewy24.pl/';
    /**
     * Use Live (false) or Sandbox (true) enviroment
     * @var bool
     */
    private $testMode           = false;
    /**
     * Merchant Id
     * @var int
     */
    private $merchantId         = 0;
    /**
     * Merchant posId
     * @var int
     */
    private $posId              = 0;
    /**
     * Salt to create a control sum (from P24 panel)
     * @var string
     */
    private $salt               = '';
    /**
     * API Key (from P24 panel)
     * @var string
     */
    private $api                = '';
    /**
     * Array of POST data
     * @var array
     */
    private $postData           = array();

    /**
     * The class to validate messages.
     *
     * @var P24_Message_Validator
     */
    private $message_validator;

    /**
     * Przelewy24Class constructor.
     * @param P24_Config_Accessor $config
     */
    public function __construct( P24_Config_Accessor $config ) {
        #TODO Refactor this out.
        $this->message_validator = new P24_Message_Validator();

        $config->access_mode_to_strict();
        $this->posId         = (int)trim($config->get_shop_id());
        $this->merchantId    = (int)trim($config->get_merchant_id());
        if ($this->merchantId === 0)
            $this->merchantId= $this->posId;
        $this->salt          = trim($config->get_salt());
        $this->testMode      = $config->is_p24_operation_mode( 'sandbox' );

        $this->addValue('p24_merchant_id', $this->merchantId);
        $this->addValue('p24_pos_id', $this->posId);
        $this->addValue('p24_api_version', P24_VERSION);

        $this->api = $config->get_p24_api();

        return true;
    }

    /**
     * Set key for API.
     *
     * @param string $apiKey The API key.
     */
    public function setApiKey($apiKey) {
		$this->api = $apiKey;
	}
    /**
     * Returns host URL
     */
    public function getHost() {
        return self::getHostStatic($this->testMode);
    }
    public static function getHostStatic($testMode) {
        if ($testMode) return self::$hostSandbox;
        return self::$hostLive;
    }
    /**
     * Returns URL for direct request (trnDirect)
     */
    public function trnDirectUrl() {
        return $this->getHost().'trnDirect';
    }
    /**
     *
     * Add value do post request
     * @param string $name Argument name
     * @param mixed $value Argument value
     */
    public function addValue($name, $value) {
        if ($this->validateField($name, $value))
            $this->postData[$name] = $value;
    }

    /**
     *
     * Function is testing a connection with P24 server
     * @return array Array(INT Error, Array Data), where data
     * @throws Exception
     */
    public function testConnection() {
        $crc = md5($this->posId."|".$this->salt);
        $ARG["p24_pos_id"] = $this->posId;
        $ARG["p24_sign"] = $crc;
        $RES = $this->callUrl("testConnection",$ARG);
        return $RES;
    }

    /**
     *
     * Prepare a transaction request
     * @param bool $redirect Set true to redirect to Przelewy24 after transaction registration
     * @return array array(INT Error code, STRING Token)
     * @throws Exception
     */
    public function trnRegister($redirect = false) {
        $this->addValue("p24_sign", $this->trnDirectSign($this->postData));
        $RES = $this->callUrl("trnRegister",$this->postData);
        if($RES["error"] == "0") {
            $token = $RES["token"];
        } else {
            return $RES;
        }
        if($redirect) {
            $this->trnRequest($token);
        }
        return array("error"=>0, "token"=>$token);
    }

    /**
     * Redirects or returns URL to a P24 payment screen
     * @param string $token Token
     * @param bool $redirect If set to true redirects to P24 payment screen. If set to false function returns URL to redirect to P24 payment screen
     * @return string URL to P24 payment screen
     */
    public function trnRequest($token, $redirect = true) {
        $url=$this->getHost().'trnRequest/'.$token;
        if($redirect) {
            header('Location: '.$url);
            return '';
        }
        return $url;
    }

    /**
     *
     * Function verify received from P24 system transaction's result.
     * @return array
     * @throws Exception
     */
    public function trnVerify() {
        $crc = md5($this->postData["p24_session_id"]."|".$this->postData["p24_order_id"]."|".$this->postData["p24_amount"]."|".$this->postData["p24_currency"]."|".$this->salt);
        $this->addValue('p24_sign', $crc);
        $RES = $this->callUrl('trnVerify',$this->postData);
        return $RES;
    }

    /**
     *
     * Function contect to P24 system
     * @param string $function Method name
     * @param array $ARG POST parameters
     * @return array array(INT Error code, ARRAY Result)
     * @throws Exception
     */
    private function callUrl($function, $ARG) {

        if(!in_array($function, array('trnRegister','trnRequest','trnVerify','testConnection'))) {

            return array('error'=>201,'errorMessage'=>'class:Method not exists');

        }
        if ($function!='testConnection') $this->checkMandatoryFieldsForAction($ARG, $function);

        $REQ = array();

        foreach($ARG as $k=>$v) $REQ[] = $k."=".urlencode($v);
        $url = $this->getHost().$function;
        $user_agent = 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)';
        if(($ch = curl_init())) {

            if(count($REQ)) {
                curl_setopt($ch, CURLOPT_POST,1);
                curl_setopt($ch, CURLOPT_POSTFIELDS,join("&",$REQ));
            }

            curl_setopt($ch, CURLOPT_URL,$url);
            if ($this->testMode) curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            else curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            if(($result = curl_exec ($ch))) {
                $INFO = curl_getinfo($ch);
                curl_close ($ch);

                if($INFO['http_code']!=200) {

                    return array('error'=>200,'errorMessage'=>'call:Page load error ('.$INFO['http_code'].')');

                } else {

                    $RES     = array();
     	            $X       = explode('&', $result);

     	            foreach($X as $val) {

                 		$Y           = explode('=', $val);
                 		$RES[trim($Y[0])] = urldecode(trim($Y[1]));
                 	}
             	    return $RES;

                }


            } else {
                curl_close ($ch);
                return array('error'=>203,'errorMessage'=>'call:Curl exec error');

            }

        } else {

            return array('error'=>202,'errorMessage'=>'call:Curl init error');

        }
    }

    /**
     *
     * @param string $field
     * @param mixed &$value
     * @return boolean
     */
    public function validateField($field, &$value) {
        return $this->message_validator->validate_field($field, $value);
    }

    /**
     * Filter value.
     *
     * @param string           $field The name of field.
     * @param string|float|int $value The value to test.
     * @return bool|string
     */
    private function filterValue($field, $value) {
        return $this->message_validator->filter_value($field, $value);
    }

    /**
     * Check if mandatory fields are set.
     *
     * @param $fieldsArray
     * @param $action
     *
     * @return bool
     * @throws Exception
     */
    public function checkMandatoryFieldsForAction($fieldsArray, $action) {
        $keys=array_keys($fieldsArray);
        $verification=($action === 'trnVerify');
        static $mandatory=array('p24_order_id',//verify
            'p24_sign','p24_merchant_id','p24_pos_id','p24_api_version','p24_session_id','p24_amount',//all
            'p24_currency','p24_description','p24_country','p24_url_return','p24_currency','p24_email');//register/direct

        for ($i=($verification?0:1); $i<($verification?4:count($mandatory)); $i++) {
            if(!in_array($mandatory[$i], $keys)) {
                throw new Exception('Field '.$mandatory[$i].' is required for '.$action.' request!');
            }
        }
        return true;
    }
    /**
     * Parse and validate POST response data from Przelewy24
     * @return array - valid response | false - invalid crc | null - not a Przelewy24 response
     */
    public function parseStatusResponse() {
        if (isset($_POST['p24_session_id'], $_POST['p24_order_id'], $_POST['p24_merchant_id'], $_POST['p24_pos_id'], $_POST['p24_amount'], $_POST['p24_currency'], $_POST['p24_method']/*, $_POST['p24_statement']*/, $_POST['p24_sign'])) {
            $session_id  = $this->filterValue('p24_session_id', $_POST['p24_session_id']);
            $merchant_id = $this->filterValue('p24_merchant_id', $_POST['p24_merchant_id']);
            $pos_id      = $this->filterValue('p24_pos_id', $_POST['p24_pos_id']);
            $order_id    = $this->filterValue('p24_order_id', $_POST['p24_order_id']);
            $amount      = $this->filterValue('p24_amount', $_POST['p24_amount']);
            $currency    = $this->filterValue('p24_currency', $_POST['p24_currency']);
            $method      = $this->filterValue('p24_method', $_POST['p24_method']);
            $sign        = $this->filterValue('p24_sign', $_POST['p24_sign']);

            if ($merchant_id!=$this->merchantId || $pos_id!=$this->posId || md5($session_id.'|'.$order_id.'|'.$amount.'|'.$currency.'|'.$this->salt)!=$sign) return false;

            return array(
                'p24_session_id'  => $session_id,
                'p24_order_id'    => $order_id,
                'p24_amount'      => $amount,
                'p24_currency'    => $currency,
                'p24_method'      => $method,
            );
        }
        return null;
    }
    public function trnVerifyEx($data=null) {
        $a=$this->parseStatusResponse();
        if ($a===null) return null;
        elseif ($a) {
            if ($data!=null) {
                foreach ($data as $field => $value) {
                    if ($a[$field]!=$value) return false;
                }
            }
            $this->postData=array_merge($this->postData,$a);
            $result=$this->trnVerify();
            return ($result['error']==0);
        }
        return false;
    }
    /*
     * Zwraca p24_sign dla trnDirect
     * */
    public function trnDirectSign($data) {
        return md5($data['p24_session_id'].'|'.$this->posId.'|'.$data['p24_amount'].'|'.$data['p24_currency'].'|'.$this->salt);
    }

	/*
	 * Zwraca listę kanałów płatności, którymi można płacić inną walutą niż PLN
	 * */
	public static function getChannelsNonPln() {
		return array(66,92,124,140,145,152,218);
	}
	/**
	 * Zwraca listę kanałów płatności ratalnej
	 */
	public static function getChannelsRaty() {
		return array(72,129,136);
	}

	/**
	 * Zwraca minimalną kwotę dla płatności ratalnych
	 */
	public static function getMinRatyAmount() {
		return 300;
	}

	/**
	 * Zwraca listę kanałów płatności kartą
	 *
	 */
	public static function getChannelsCard() {
		return array(140,142,145,218);
	}

	/**
	 * końcówka adresu WSDL
	 *
     */
    private function getWsdlService() {
		return 'external/'.(int)$this->merchantId.'.wsdl';
	}

	/**
	 * końcówka adresu WSDL dla kart
	 *
	 */
    static function getWsdlCCService() {
		return 'external/wsdl/charge_card_service.php?wsdl';
	}

	/**
	 * czy Merchant ma dostęp do rekurencji na kartach
	 */
	public function ccCheckRecurrency() {
		try {
			$s=new SoapClient($this->getHost().$this->getWsdlCCService(), array('trace'=>true, 'exceptions'=>true, 'connection_timeout' => 2));
			$res=$s->checkRecurrency($this->merchantId, $this->salt);
		} catch (Exception $e) {
			error_log(__METHOD__.' '.$e->getMessage());
		}
		return !!$res;
	}

	/**
	 * czy Merchant wpisał poprawny klucz api
	 */
	public function apiTestAccess() {
		if (empty($this->api)) return false;
		try {
			$s=new SoapClient($this->getHost().$this->getWsdlService(), array('trace'=>true, 'exceptions'=>true, 'connection_timeout' => 2));
			$res=$s->TestAccess($this->merchantId, $this->api);
		} catch (Exception $e) {
			error_log(__METHOD__.' '.$e->getMessage());
		}
		return !!$res;
	}

    /**
     * Zwraca listę kanałów płatności [id => etykieta,]
     *
     * @param bool $only24at7 płatności, które są w tej chwili aktywne - usuwa z wyników te nienatychmiastowe
     * @param string $currency ogranicza listę metod płatności do dostępnych dla wskazanej waluty
     * @param string $lang Etykiety kanałów płatności w wybranym języku
     * @return bool
     */
    public function availablePaymentMethods($only24at7 = true, $currency = 'PLN', $lang = 'pl')
    {
        if (empty($this->api)) {
            return false;
        }
        try {
            $s = new SoapClient(
                $this->getHost() . $this->getWsdlService(),
                array('trace' => true, 'exceptions' => true, 'connection_timeout' => 2)
            );
            $res = $s->PaymentMethods($this->merchantId, $this->api, $lang);
        } catch (Exception $e) {
            error_log(__METHOD__ . ' ' . $e->getMessage());
        }
        if (!empty($res) && isset($res->error) && $res->error->errorCode === 0) {
            if ($only24at7) {
                $there_is_218 = false;
                foreach ($res->result as $key => $item) {
                    if (218 === (int)$item->id) {
                        $there_is_218 = true;
                    }
                    if (!$item->status || in_array($item->id, array(1000))) {
                        unset($res->result[$key]);
                    }
                }
            }

            if ($currency !== 'PLN') {
                foreach ($res->result as $key => $item) {
                    if (!isset($there_is_218) && 218 === (int)$item->id) {
                        $there_is_218 = true;
                    }
                    if (!in_array($item->id, $this->getChannelsNonPln())) {
                        unset($res->result[$key]);
                    }
                }
                if (!isset($there_is_218)) {
                    $there_is_218 = false;
                }
            }

            if (!isset($there_is_218)) {
                $there_is_218 = false;
                foreach ($res->result as $key => $item) {
                    if (218 === (int)$item->id) {
                        $there_is_218 = true;
                        break;
                    }
                }
            }

            // filter method 142 and 145 when there is 218
            if ($there_is_218) {
                foreach ($res->result as $key => $item) {
                    if (in_array($item->id, array(142, 145))) {
                        unset($res->result[$key]);
                    }
                }
            }

            return $res->result;
        }

        return false;
    }

	public function availablePaymentMethodsSimple($only24at7 = true, $currency = 'PLN', $lang = 'pl') {
		$all = $this->availablePaymentMethods($only24at7, $currency, $lang);
		$result = array();
		if (is_array($all) && sizeof($all) > 0) {
			foreach ($all as $item) {
				$result[$item->id] = $item->name;
			}
		} else {
			$result = $all;
		}
		return $result;
	}

	public static function requestGet($url) {
		$isCurl = function_exists('curl_init')&&function_exists('curl_setopt')&&function_exists('curl_exec')&&function_exists('curl_close');

		if ($isCurl) {
			$userAgent = "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)";
			$curlConnection = curl_init();
			curl_setopt($curlConnection, CURLOPT_URL, $url);
			curl_setopt($curlConnection, CURLOPT_SSL_VERIFYHOST, 2);
			curl_setopt($curlConnection, CURLOPT_USERAGENT, $userAgent);
			curl_setopt($curlConnection, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($curlConnection, CURLOPT_SSL_VERIFYPEER, false);
			$result = curl_exec($curlConnection);
			curl_close($curlConnection);
			return $result;
		}
		return "";
	}
}
}
