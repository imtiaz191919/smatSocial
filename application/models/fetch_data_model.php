<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Fetch_data_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }
	public function fetchDataModel($accessToken, $jsonArray, $fb) {
		
		header("Content-type: application/json");
		if (isset($jsonArray->page)) {
			$page = $jsonArray->page;
		} else {
			echo '{"errorMessage": "page field in request body is mandatory"}';
			return;
		}
		$count = 0;
		$permissions = [];
		if(isset($accessToken)) {
				$connection = $this->load->database();
				try {
				  $response = $fb->get('/'.$page.'?fields=id,name,posts{full_picture,id,likes.summary(true),comments,created_time,message,description}', $accessToken);
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
				if ($connection) {
			        $tables = $this->db->list_tables();
					if($this->db->table_exists(strtolower($result->name))){
						$query = $this->db->query('DROP TABLE '.strtolower($result->name));
					}
					$x = 'CREATE TABLE '.$result->name.' (page_id varchar(100), post_id varchar(250), title varchar(1500), description varchar(3000), image_url varchar(600), likes int, comments_count int, published_date date)';
					$query = $this->db->query($x);
				}
				else {
					echo "Error Connecting Database.";
				}
				$posts = $result->posts;
				$finalJson = '{"data": [';
				foreach ($posts->data as $row)
				{
				    $post_id = $row->id;
				   	if(isset($row->message)) {
				        $title = ($row->message);
				    } else {
				    	$title = null;
				    }
				   	if(isset($row->comments)) {
				   		$comments = $row->comments;
				   		$c= 0 ;
				   		foreach ($comments as $comment) {
				   			$c++;
				   		}
				   		$comments_count = $c;
				    } else {
				    	$comments_count = 0;
				    }
				    if (isset($row->likes)) {
				    	if (isset($row->likes->summary)) {
				    		$likes = $row->likes->summary->total_count;
				    	}
				    } else {
				    	$likes = 0;
				    }
				    if(isset($row->full_picture)) {
				    	$image_url = addslashes($row->full_picture);
				    } else {
				    	$image_url = "null";
				    }
				    if(isset($row->description)) {
				    	$description = ($row->description);
				    } else {
				    	$description ='null';
				    }
				    $published_date = $row->created_time;
				    $formattedDate = date(substr($published_date, 0, 10));
				    $currentDate = date("Y-m-d");
				    $formattedDateObject = date_create($formattedDate);
				    $currentDate = date_create($currentDate);
				    $diff = date_diff($formattedDateObject, $currentDate)->days;
				    if ($diff > 30) {
				    	$finalJson = substr($finalJson, 0, -1);
				    	$finalJson.="]}";
				    	echo $finalJson;
				    	return;
				    }

				    $finalJson .= '{"page_id": "'.$page_id.'","post_id": "'.$post_id.'","title": '.json_encode($title).',"description": '.json_encode($description)	.',"image_url": "'.$image_url.'", "likes": '.$likes.', "comments_count": '.$comments_count.', "published_date": '.json_encode($formattedDate).'},';
				    $query = 'INSERT INTO '.strtolower($result->name).'(page_id, post_id, title, description, image_url, likes, comments_count, published_date) VALUES ("'.$page_id.'","'.$post_id.'",'.json_encode($title).','.json_encode($description).',"'.$image_url.'",'.$likes.','.$comments_count.',"'.$formattedDate.'")';
				    $this->db->query($query);
				}
				if(isset($posts->paging->next)) {
					$count++;
					$finalJson = $this->getNextPageUrl(strtolower($result->name),$posts->paging->next, $count, $finalJson, $page_id);
					$finalJson = substr($finalJson, 0, -1);
					$finalJson.="]}";
					echo $finalJson;
				} else {
					$finalJson = substr($finalJson,0,-1);
					echo $finalJson;
				}
		}
	}


	public function getNextPageUrl($page_name, $url, $no_of_calls, $finalJson, $page) {
		$no_of_calls++;
		$contents = file_get_contents($url);
		$result = json_decode($contents);
		$page_id = $page;
		foreach ($result->data as $row)
		{
				    $post_id = $row->id;
				   	if(isset($row->message)) {
				        $title = ($row->message);
				    } else {
				    	$title = null;
				    }
				   	if(isset($row->comments)) {
				   		$comments = $row->comments;
				   		$c= 0 ;
				   		foreach ($comments as $comment) {
				   			$c++;
				   		}
				   		$comments_count = $c;
				    } else {
				    	$comments_count = 0;
				    }
				    if (isset($row->likes)) {
				    	if (isset($row->likes->summary)) {
				    		$likes = $row->likes->summary->total_count;
				    	}
				    } else {
				    	$likes = 0;
				    }
				    if(isset($row->full_picture)) {
				    	$image_url = addslashes($row->full_picture);
				    } else {
				    	$image_url = "null";
				    }
				    if(isset($row->description)) {
				    	$description = ($row->description);
				    } else {
				    	$description ='null';
				    }
				    $published_date = $row->created_time;
				    $formattedDate = date(substr($published_date, 0, 10));
				    $currentDate = date("Y-m-d");
				    $formattedDateObject = date_create($formattedDate);
				    $currentDate = date_create($currentDate);
				    $diff = date_diff($formattedDateObject, $currentDate)->days;
				    if ($diff > 30) {
				    	return $finalJson;
				    }

				    $finalJson .= '{"page_id": "'.$page_id.'","post_id": "'.$post_id.'","title": '.json_encode($title).',"description": '.json_encode($description)	.',"image_url": "'.$image_url.'", "likes": '.$likes.', "comments_count": '.$comments_count.', "published_date": '.json_encode($formattedDate).'},';
				    $query = 'INSERT INTO '.strtolower($page_name).'(page_id, post_id, title, description, image_url, likes, comments_count, published_date) VALUES ("'.$page_id.'","'.$post_id.'",'.json_encode($title).','.json_encode($description).',"'.$image_url.'",'.$likes.','.$comments_count.',"'.$formattedDate.'")';
				    $this->db->query($query);
			}
		if(isset($result->paging->next) && $no_of_calls < 3) {
			$finalJson = $this->getNextPageUrl($page_name, $result->paging->next, $no_of_calls, $finalJson);
		} 
		return $finalJson;
	}
}

?>