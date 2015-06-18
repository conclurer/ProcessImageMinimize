<?php

class MinimizeConnector extends Wire {
    protected $http, $licenseKey, $customerGUID;
    const MzServiceUrl = 'http://46.101.172.9';

    public function __construct($licenseKey, $customerGUID) {
        $this->http = new WireHttp();
    }

    public function pushImage($url) {
        $this->http->post(self::MzServiceUrl.'/queue3/push', array_merge(array(), $this->authHash()));
    }

    protected function authHash () {
        $nonce = array_map(function ($val) { $base = array_merge(range(0,9),range('a','z'),range('A','Z')); return $base[array_rand($base)]; }, range(0,6));
        return array(
            'nonce' => $nonce,
            'customer_guid' => $this->customerGUID,
            'auth' => hash('sha256', $this->customerGUID.$this->licenseKey.$nonce)
        );
    }
}