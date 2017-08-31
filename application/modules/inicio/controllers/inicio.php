<?php if ( ! defined('BASEPATH')) exit('No puede acceder a este archivo');

class Inicio extends CI_Controller {
	
	function __construct(){
		parent::__construct();
		$this->layout->current = 1;
	}

	public function index(){
		#title
		$this->layout->title('Inicio');
		
		#metas
		$this->layout->setMeta('title','Inicio');
		$this->layout->setMeta('description','Inicio');
		$this->layout->setMeta('keywords','Inicio');

		$this->layout->view('inicio');
	}
	
}