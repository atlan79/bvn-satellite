<?php
/**
 * BVN Satellite
 * 
 * PHP SDK for interacting with the BVN.ch API
 * 
 * @author Thomas Winter
 * @license https://github.com/atlan79/bvn-satellite/blob/master/LICENSE.md MIT
 * @version 1.2
 */
class BvnchSDK {

    /** @var string The default language to return results */
    public $lang = 'de';

    /** @var string The BVN.ch API Version to call */
    public $api_version = 'v1';

	/** @var integer Set timeout default. */
    public $timeout = 30;

	/** @var integer Set connect timeout */
    public $connect_timeout = 30;

 	/** @var boolean Verify SSL Cert */
    public $ssl_verifypeer = false;

	/** @var integer Contains the last HTTP status code returned */
    public $http_code = 0;

	/** @var array Contains the last Server headers returned */
    public $http_header = array();

	/** @var array Contains the last HTTP headers returned */
    public $http_info = array();

	/** @var boolean Throw cURL errors */
    public $return_curl_errors = true;

	/** @var string Set the useragent */
	private $useragent = 'oesi BvnchSDK 0.1 beta';

	
	/** 
	 *	BvnchAPI URL's 
	 */
	const URL_API = 'http://api.bvn.ch/';

	const URL_MATCHES_LIST_REALM = 'list.php?format=json';
	const URL_MATCHES_LIST_LEAGUE = 'list.php?format=json&realm=liga';
	const URL_MATCHES_LIST_CLUB = 'list.php?format=json&realm=club';
	const URL_MATCHES_LIST_TEAM = 'list.php?format=json&realm=team';
	
	const URL_MATCHES_LIST_LIGA_TEAM = 'list.php?format=json&realm=liga&id=%s';
	const URL_MATCHES_LIST_CLUB_TEAM = 'list.php?format=json&realm=club&id=%s';

	const URL_RANKING = 'ranking.php?format=json&league_id=%s';
	const URL_MATCHES = 'matches.php?format=json&realm=%s&id=%s&view=%s';
	const URL_MATCHES_TODAY = 'matches.php?format=json&realm=today';

    /**
     * SDK constructor
     *
     */
    public function __construct() {
        if (!in_array('curl', get_loaded_extensions())) {
            return array( 'error' => 'cURL extension is not installed and is required' );
        }
    }	


	/**
     * Get Realm List for Schedule
	 *
     */
    public function getRealmList () {
    	return $this->request(sprintf(self::URL_MATCHES_LIST_REALM));
    }


	/**
     * Get League List
	 * 
     */
    public function getLeagueList () {
    	return $this->request(sprintf(self::URL_MATCHES_LIST_LEAGUE));
    }


	/**
     * Get Club List for Schedule
	 *
     */
    public function getClubList () {
    	return $this->request(sprintf(self::URL_MATCHES_LIST_CLUB));
    }
	
	/**
     * Get Team List for Schedule
	 *
     */
    public function getTeamList () {
    	return $this->request(sprintf(self::URL_MATCHES_LIST_TEAM));
    }


	/**
     * Get Team List of Liga for Schedule
	 *
     */
    public function getTeamListLiga ($league) {
    	return $this->request(sprintf(self::URL_MATCHES_LIST_LIGA_TEAM. $league));
    }
	
	/**
     * Get Team List of Club for Schedule
	 *
     */
    public function getTeamListClub ($club) {
    	return $this->request(sprintf(self::URL_MATCHES_LIST_CLUB_TEAM, $club));
    }



	/**
     * Get Ranking
	 * @param string $league_id The League ID (e.g. 'H2L')
     */
    public function getRanking($league_id) {
    	return $this->request(sprintf(self::URL_RANKING, $league_id));
    }

    /** 
     * Get Matches by Realm
	 * @param string $realm  The Realm Qualifier (e.g. 'liga', 'club', 'team')
	 * @param string $id     The Selection ID (e.g. 'H2L', 'bc-muenchenstein', 'h2l-bc-münchenstein')
     */
    public function getMatches($realm, $id, $view) {
    	return $this->request(sprintf(self::URL_MATCHES, $realm, $id, $view));
    }

    /** 
     * Get Matches of todate
	 *
     */
    public function getTodayMatches()
    {
    	return $this->request(sprintf(self::URL_MATCHES_TODAY));
    }



 	/**
     * BvnchAPI request
     * @param   string $uri The URI portion of the API URL
     * @param   string $method GET or POST (post is untested)
     * @param   string $postfields Optional postfields (untested)
     * @param 	bool $assoc return as an associated array rather than an object
     * @return  mixed
     */
    private function request($uri, $method = 'GET', $postfields = null, $assoc = false) {
		
    	$request_url = self::URL_API . $this->api_version . '/' . $uri;

        $this->http_info = array();

        $crl = curl_init();
        curl_setopt($crl, CURLOPT_USERAGENT, $this->useragent);
        curl_setopt($crl, CURLOPT_CONNECTTIMEOUT, $this->connect_timeout);
        curl_setopt($crl, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($crl, CURLOPT_HTTPHEADER, array('Expect:'));
        curl_setopt($crl, CURLOPT_SSL_VERIFYPEER, $this->ssl_verifypeer);
        curl_setopt($crl, CURLOPT_HEADERFUNCTION, array($this, 'getHeader'));
        curl_setopt($crl, CURLOPT_HEADER, false);

        switch ($method) {
            case 'POST':
                curl_setopt($crl, CURLOPT_POST, true);
                if (!is_null($postfields)) {
                    curl_setopt($crl, CURLOPT_POSTFIELDS, ltrim($postfields, '?'));
                }
                break;
            case 'DELETE':
                curl_setopt($crl, CURLOPT_CUSTOMREQUEST, 'DELETE');
                if (!is_null($postfields)) {
                    $request_url = self::URL_API . $this->api_version . '/' . $uri . $postfields;
                }
        }

        curl_setopt($crl, CURLOPT_URL, $request_url);

        $response = curl_exec($crl);

        $this->http_code = curl_getinfo($crl, CURLINFO_HTTP_CODE);
        $this->http_info = array_merge($this->http_info, curl_getinfo($crl));
		
        if (curl_errno($crl) && $this->return_curl_errors === true) {
            return array( 'error' => array ( curl_error($crl), curl_errno($crl) ));
        }

        curl_close($crl);
		
        return json_decode($response, $assoc);
    } 

	/**
     * Get the header info to store
     */
    private function getHeader($ch, $header)
    {
        $i = strpos($header, ':');
        if (!empty($i)) {
            $key = str_replace('-', '_', strtolower(substr($header, 0, $i)));
            $value = trim(substr($header, $i + 2));
            $this->http_header[$key] = $value;
        }

        return strlen($header);
    } 
    

}
