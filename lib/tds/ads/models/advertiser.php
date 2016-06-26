<?php
namespace TDS\Ads\Models;

class Advertiser {
    private $data = array();

    public function __construct($id = null) {
        if (intval($id) > 0) {
            $this->load($id);
        }
    }

    public function __get($k) {
        return isset($this->data[$k]) ? $this->data[$k] : null;
    }

    public function __set($k, $v) {
        $this->data[$k] = $v;
    }

    public function load($id) {

    }
}
