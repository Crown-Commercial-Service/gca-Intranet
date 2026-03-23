<?php
class TCMP_Singleton {
	var $lang;
	var $utils;
	var $form;
	var $check;
	var $options;
	var $log;
	var $cron;
	var $tracking;
	var $manager;
	var $ecommerce;
	var $plugin;
	var $tabs;
	var $body_written;

	function __construct() {
		$this->lang         = new TCMP_Language();
		$this->tabs         = new TCMP_Tabs();
		$this->utils        = new TCMP_Utils();
		$this->form         = new TCMP_Form();
		$this->check        = new TCMP_Check();
		$this->options      = new TCMP_Options();
		$this->log          = new TCMP_Logger();
		$this->cron         = new TCMP_Cron();
		$this->tracking     = new TCMP_Tracking();
		$this->manager      = new TCMP_Manager();
		$this->ecommerce    = new TCMP_Ecommerce();
		$this->plugin       = new TCMP_Plugin();
		$this->body_written = false;
	}
	public function init() {
		$this->lang->load( 'tcmp', TCMP_PLUGIN_DIR . 'languages/Lang.txt' );
		$this->tabs->init();
		$this->cron->init();
		$this->manager->init();
	}
}
