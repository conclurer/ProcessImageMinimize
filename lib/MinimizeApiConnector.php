<?php

class MinimizeApiConnector extends Wire {

    private $baseUrl = 'https://minimize.pw';
    private $version = 150;
    private $licenseKey;

    public function __construct ($license) { $this->licenseKey = $license; }

    public function pushToMinimize ($imageUrls = array()) {
        return $this->apiCall('/queue/push', array('urls' => $imageUrls));
    }

    private function apiCall($node, $data = array(), $submitLicense = true, $post = true, $json = true)
    {
        if ($submitLicense) $data = array_merge($data, array('license' => $this->licenseKey));

        $context =  stream_context_create(array(
            'http' => array(
                'method' => ($post) ? 'POST' : 'GET',
                'header' => array(
                    "Host: minimize.pw",
                    'Content-type: application/x-www-form-urlencoded',
                    // Additional information for licence validating / quality improvement purposes
                    // For privacy information, please read our terms and conditions.
                    "Referer: " . $this->config->httpHost . "," . $_SERVER['SERVER_ADDR'],
                    "User-Agent: ProcessWire/". $this->config->version ."/" . $this->version
                ),
                'content' => http_build_query($data)
            )
        ));

        // Perform Request
        try {
            $result = file_get_contents($this->baseUrl . $node, false, $context);
            if ($json) {
                $result = json_decode($result, true);
            }
            return $result;
        } catch (Exception $e) {
            #todo log
            if ($json) return array('error' => true);
            return '';
        }
    }

} 