<?php

class RPC {

    private $address;

    public function __construct($server_name) {
        $this->address = config('server.'.$server_name);
    }

    public function dial($class_name) {
        if (strrpos($this->address, '/') !== strlen($this->address) -1 ) {
            $this->address .= '/';
        }
        $this->address .= $class_name;
        return $this;
    }

    /**
     * @param      $method
     * @param null $params
     *
     * @return Response
     */
    public function call($method, $params = null) {
        $res = new Response();
        try {
            $client = new Yar_Client($this->address);
            $result = $client->$method($params);
            $res->put($result);
        } catch (Exception $e) {
            $res->error('server_connect_err');
        }
        return $res;
    }
}