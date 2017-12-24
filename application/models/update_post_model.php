<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Update_post_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }
	public function updatePost($accessToken, $jsonArray, $fb) {
		
		header("Content-type: application/json");
		if (isset($jsonArray->post_id)) {
			$post_id = $jsonArray->post_id;
		} else {
			echo '{"errorMessage": "post_id is mandatory field"}';
			return;
		}
		$given_title = "";
		$given_description = "";
		if (isset($jsonArray->title)) {
			$given_title = $jsonArray->title;
		}
		if (isset($jsonArray->description)) {
			$given_description = $jsonArray->description;
		}
		$x = explode('_',$post_id);
		try {
		  $response = $fb->get('/'.$x[0].'?fields=id,name', $accessToken);
		} catch(Facebook\Exceptions\FacebookResponseException $e) {
		  echo 'Graph returned an error: ' . $e->getMessage();
		  exit;
		} catch(Facebook\Exceptions\FacebookSDKException $e) {
		  echo 'Facebook SDK returned an error: ' . $e->getMessage();
		  exit;
		}
		$result = $response->getBody();
		$result = json_decode($result);
		$page_id = $result->id;
		$page_name = strtolower($result->name);
		$connection = $this->load->database();
		$query = "SELECT * FROM ".$page_name." ";
		if($this->db->table_exists($page_name)) {
			$query = 'UPDATE '.$page_name.' SET ';
			if ($given_title!='') {
				$query.=' title = '.json_encode($given_title).',';
			}
			if ($given_description!='') {
				$query.=' description = '.json_encode($given_description).',';
			}
			$query = substr($query, 0, -1);
			$query.=' WHERE post_id = "'.$post_id.'"';
			$response = $this->db->query($query);
			if($this->db->affected_rows()==0){
				echo '{"error_message": "Internal Server Error"}';
			} else {
				echo'{"status": "success"}';
			}
		} else {
			echo "This page post's are not saved to databaase yet.";
		}
	}
}

?>