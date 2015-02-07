<?php

namespace Webdesignby;

class RequestLog{
    
    /* **
     * 
     * $_SERVER
     * 
     * 'REQUEST_TIME'
     * The timestamp of the start of the request. Available since PHP 5.1.0.
     * 
     * 'REQUEST_TIME_FLOAT'
     * The timestamp of the start of the request, with microsecond precision. Available since PHP 5.4.0.
     * 
     * 'HTTP_USER_AGENT'
     * 
     * 'REQUEST_URI'
     * 'REQUEST_METHOD'
     * 

            CREATE TABLE `request_log` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `time_in` double(13,3) NOT NULL,
              `request_time` int(10) unsigned NOT NULL,
              `time_out` double(13,3) NOT NULL,
              `ip_address` int(11) unsigned NOT NULL,
              `memory_get_peak_usage` varchar(20) NOT NULL,
              `remote_host` varchar(255) NOT NULL,
              `http_referer` text NOT NULL,
              `http_host` varchar(255) NOT NULL,
              `request_uri` text NOT NULL,
              `http_user_agent` varchar(255) NOT NULL,
              `request_method` varchar(20) NOT NULL,
              `query_string` text NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;


     * 
     */
    
    private $db_host        = "localhost";
    private $db_name        = "";
    private $table_name     = "request_log";
    private $db_user        = "root";
    private $db_password    = "root";
    private $dbh;
    
    public $id;
    public $time_in                 = "";
    public $time_out                = "";
    public $memory_get_peak_usage   = "";
    public $ip_address              = "";
    public $remote_host             = "";
    public $http_referer            = "";
    public $request_time            = "";
    public $http_host               = "";
    public $request_uri             = "";
    public $http_user_agent         = "";
    public $request_method          = "";
    public $query_string            = "";
    
    public function __construct( $config = array() ) {
        
        if( ! empty($config)){
            $this->config($config);
        }
        
        $this->time_in = (float) \microtime(true);
        $this->ip_address = ip2long($_SERVER['REMOTE_ADDR']);
        if( isset($_SERVER['HTTP_REFERER']))
            $this->http_referer = $_SERVER['HTTP_REFERER'];
        if( ! empty($_SERVER['REQUEST_TIME']))
            $this->request_time = $_SERVER['REQUEST_TIME']; // date("Y-m-d H:i:s", $_SERVER['REQUEST_TIME']);
        $this->http_user_agent = $_SERVER['HTTP_USER_AGENT'];
        $this->http_host = $_SERVER['HTTP_HOST'];
        $this->request_uri = $_SERVER['REQUEST_URI'];
        if( isset($_SERVER['REMOTE_HOST']))
            $this->remote_host = $_SERVER['REMOTE_HOST'];
        $this->request_method = $_SERVER['REQUEST_METHOD'];
        $this->query_string = $_SERVER['QUERY_STRING'];
    }
    
    private function config($config=array()){
        if( ! empty($config['db_host'])){
            $this->db_host = $config['db_host'];
        }
        if( ! empty($config['db_user'])){
            $this->db_user = $config['db_user'];
        }
        if( ! empty($config['db_password'])){
            $this->db_password = $config['db_password'];
        }
        if( ! empty($config['db_name'])){
            $this->db_name = $config['db_name'];
        }
    }
    
    public function start(){
        
        $this->db_connect();
        
        $sql = "INSERT INTO `" . $this->table_name . "` "
                . "(`time_in`, `request_time`, `ip_address`, `remote_host`, `http_referer`, `http_host`, `request_uri`, `http_user_agent`, `request_method`, `query_string` ) "
                . "VALUES"
                . "(:time_in, :request_time, :ip_address, :remote_host, :http_referer, :http_host, :request_uri, :http_user_agent, :request_method, :query_string )";
        
        $q = $this->dbh->prepare($sql);
   
        $params = array(
                            ':time_in'          => $this->time_in,
                            ':request_time'     => $this->request_time,
                            ':ip_address'       => $this->ip_address,
                            ':remote_host'      => $this->remote_host,
                            ':http_referer'     => $this->http_referer,
                            ':http_host'        => $this->http_host,
                            ':request_uri'      => $this->request_uri,
                            ':http_user_agent'  => $this->http_user_agent,
                            ':request_method'   => $this->request_method,
                            ':query_string'     => $this->query_string,
        );
        
        try {
            $worked = $q->execute($params);
        }catch ( \Exception $ex) {
            throw new \Exception($ex->getMessage());
        }

        $this->id = $this->dbh->lastInsertId();
        
        return $this->id;
    }
    
    public function finish(){
        
        if( empty($this->dbh)){
            $this->db_connect();
        }
        
        $sql = "UPDATE `" . $this->table_name . "` SET `time_out` = :time_out, `memory_get_peak_usage` = :memory_get_peak_usage WHERE id = " . (int) $this->id;
        $memory_get_peak_usage = \memory_get_peak_usage();
        $params = array(
            ':time_out'     => (float) \microtime(true),
            ':memory_get_peak_usage' => $memory_get_peak_usage,
        );
        
        $q = $this->dbh->prepare($sql);
        $q->execute($params);
        
    }
    
    private function db_connect(){
        
        try
        {
            $this->dbh = new \PDO("mysql:host={$this->db_host};dbname={$this->db_name}", $this->db_user,$this->db_password);
        }catch( Exception $ex ){
            // echo "Connection failed";
        }

    }
    
    
}
