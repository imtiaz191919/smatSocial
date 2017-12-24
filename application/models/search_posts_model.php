<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Search_posts_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }
	public function searchPosts($accessToken, $jsonArray, $fb) {
		header("Content-type: application/json");
		if (isset($jsonArray->page)) {
			$page = $jsonArray->page;
		} else {
			echo '{"errorMessage": "page_id is mandatory field"}';
			return;
		}
		$given_description = "";
		if (isset($jsonArray->search_string)) {
			$given_description = $jsonArray->search_string;
		}
		try {
		  $response = $fb->get('/'.strtolower($page).'?fields=id,name', $accessToken);
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
		$finalResponse = '{"data": [';
		if($this->db->table_exists($page_name)) {
			$sql_response = $this->db->query($query);
			foreach ($sql_response->result_array() as $row) {
				if (strpos($row['description'], $given_description) !== false) {
					$page_id = $row['page_id'];
					$post_id = $row['post_id'];
					$title = $row['title'];
					$description = $row['description'];
					$image_url = $row['image_url'];
					$likes = $row['likes'];
					$comments_count = $row['comments_count'];
					$published_date = $row['published_date'];
					$finalResponse.='{"page_id": "'.$page_id.'", "post_id": "'.$post_id.'", "title": '.json_encode($title).', "description": '.json_encode($description).', "image_url": "'.$image_url.'", "likes": "'.$likes.'","comments_count": "'.$comments_count.'", "published_date": "'.$published_date.'"},';
				}
			}
			if ($finalResponse == '{"data": [') {
				$finalResponse.="]}";
				echo $finalResponse;
				return;
			} else {
				$finalResponse = substr($finalResponse, 0, -1);
				$finalResponse.="]}";
				echo $finalResponse;
				return;
			}
		} else {
			echo "This page post's are not saved to databaase yet.";
		}

	}
}

?>