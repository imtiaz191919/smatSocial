<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class FetchData extends CI_Controller {

	public function index() {
		// $connection = $this->load->database();
		$this->load->model('Fetch_data_model');
		if(!session_id()) {
		    session_start();
		}
		require_once 'Facebook/autoload.php';
		$fb = new Facebook\Facebook([
				  'app_id' => '520454315003841',
				  'app_secret' => '50f7b40862fd1dc13299fae135624a92',
				  'default_graph_version' => 'v2.11',
				]);
		$accessToken = '520454315003841|50f7b40862fd1dc13299fae135624a92';
		$jsonArray = json_decode(file_get_contents('php://input')); 
		$this->Fetch_data_model->fetchDataModel($accessToken, $jsonArray, $fb);
	}
}
