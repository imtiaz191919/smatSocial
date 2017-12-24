<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class GetData extends CI_Controller {

	public function index() {
		$connection = $this->load->database();
		if(!session_id()) {
		    session_start();
		}
		require_once 'Facebook/autoload.php';
		$fb = new Facebook\Facebook([
				  'app_id' => '520454315003841',
				  'app_secret' => '50f7b40862fd1dc13299fae135624a92',
				  'default_graph_version' => 'v2.11',
				]);
		$count = 0;
		$permissions = [];
		$page_id = $this->input->get('id', TRUE);
		$accessToken = '520454315003841|50f7b40862fd1dc13299fae135624a92';
		$this->load->model('Get_data_model');
		$this->Get_data_model->getData($accessToken, $page_id, $fb);
	}
}
