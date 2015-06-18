<?php

class MinimizeConnector extends Wire {
    protected $http, $licenseKey, $customerGUID;
    const MzServiceUrl = 'http://46.101.172.9:3000';

    public function __construct($licenseKey, $customerGUID) {
        $this->http = new WireHttp();
    }

    public function pushImage($url) {
        $this->http->post(self::MzServiceUrl.'/queue3/push', array_merge(array(), $this->authHash(), $this->systemInformationHash()));
    }

    public function getCustomerGUID($email, $license) {
        $this->http->get(self::MzServiceUrl.'/queue3/get_customer', array_merge(array('mail' => $email, 'license_key' => $license), $this->systemInformationHash()));
    }

    protected function authHash () {
        $nonce = array_map(function ($val) { $base = array_merge(range(0,9),range('a','z'),range('A','Z')); return $base[array_rand($base)]; }, range(0,6));
        return array(
            'nonce' => $nonce,
            'customer_guid' => $this->customerGUID,
            'auth' => hash('sha256', $this->customerGUID.$this->licenseKey.$nonce)
        );
    }

    protected function systemInformationHash () {
        return array(
            'hostname' => $_SERVER['SERVER_NAME'],
            'system' => 'ProcessWire',
            'system_version' => $this->config->version,
            'client_version' => ProcessImageMinimize::ClientLibraryVersion
        );
    }
}