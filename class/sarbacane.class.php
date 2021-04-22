<?php
/* Copyright (C) 2021 ATM Consulting <support@atm-consulting.fr>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

class Sarbacane extends CommonObject {
    public $api_key;
    public $account_key;
    public $base_url;
    public $timeout;
    public $curl_opts = array();

    public function __construct($base_url, $api_key, $account_key, $timeout = '') {
        if(! function_exists('curl_init')) {
            throw new RuntimeException('Sarbacane requires cURL module');
        }
        $this->base_url = $base_url;
        $this->api_key = $api_key;
        $this->account_key = $account_key;
        $this->timeout = $timeout;
    }

    /**
     * Do CURL request with authorization
     */
    private function do_request($resource, $method, $input) {
        $called_url = $this->base_url."/".$resource;
        $ch = curl_init($called_url);
        $apikey = 'apiKey:'.$this->api_key;
        $accountid = 'accountId:'.$this->account_key;
        $content_header = "Content-Type:application/json";
        $timeout = ($this->timeout != '') ? ($this->timeout) : 30000; //default timeout: 30 secs
        if($timeout != '' && ($timeout <= 0 || $timeout > 60000)) {
            throw new Exception('value not allowed for timeout');
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, array($apikey,$accountid, $content_header));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, $timeout);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        if(!empty($input)) {
            curl_setopt($ch, CURLOPT_POST, count($input));
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($input));
        }
        $data = curl_exec($ch);
        $info = curl_getinfo($ch);
        if(curl_errno($ch)) {
            throw new RuntimeException('cURL error: '.curl_error($ch));
        }
        if($info['http_code'] > 400) {
            throw new RuntimeException($data);
        }
        curl_close($ch);
        return json_decode($data, true);
    }

    public function get($resource, $input) {
        return $this->do_request($resource, "GET", $input);
    }

    public function put($resource, $input) {
        return $this->do_request($resource, "PUT", $input);
    }

    public function post($resource, $input) {
        return $this->do_request($resource, "POST", $input);
    }

    public function delete($resource, $input) {
        return $this->do_request($resource, "DELETE", $input);
    }
}

