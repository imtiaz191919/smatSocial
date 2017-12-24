<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Get_data_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }
	public function getData($accessToken, $page_id, $fb) {
		header("Content-type: application/json");
		try {
		  $response = $fb->get('/'.$page_id.'?fields=id,name', $accessToken);
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
		$query = "SELECT * FROM ".$page_name." ";
		if($this->db->table_exists($page_name)) {
			if($this->input->get('comments_count',TRUE)) {
				$comments_count = strtolower($this->input->get('comments_count',TRUE));
				$query.="WHERE comments_count >= ".$comments_count." ";
			}
			if($this->input->get('sort_by',TRUE)) {
				$sort = strtolower($this->input->get('sort_by',TRUE));
				$query.="ORDER BY likes ".$sort." ";
			}
			if ($this->input->get('posts_count', TRUE)){
				$limit = $this->input->get('posts_count',TRUE);
				$query.="LIMIT ".$limit;
			}
			$response = $this->db->query($query);
			$finalResponse = "{ \"data\": [";
			foreach ($response->result_array() as $row)
			{
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
			$finalResponse = substr($finalResponse, 0, -1);
			$finalResponse.=']}';
			echo $finalResponse;
		} else {
			echo "This page post's are not saved to databaase yet.";
		}
	}
}

?>