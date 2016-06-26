<?php
namespace TDS\Ads\Admin\Pages;

use TDS\Ads\Admin;
use GW;

class Dashboard {
    public function __construct() {

    }

    public function process() {
        // Process any possible data payloads
        $this->notice = array('type' => 'success');
    }

    public function render() {
        $data = array();
        if (isset($this->notice)) $data['notice'] = $this->notice;

        Admin\View::render('dashboard', $data);
    }
}
