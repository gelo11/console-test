<?php 

error_reporting('E_ALL');
ini_set('display_errors', 1);

header('Content-Type: text/html; charset=utf-8');

$path = dirname(__FILE__);
$json_file = dirname(__FILE__) . "/test.json";

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_base = 'test';

$db = mysql_connect($db_host, $db_user, $db_pass) or die('Coudn`t connect db.');
mysql_select_db($db_base, $db) or die('Select db error');
mysql_query('SET NAMES utf8') or die('err');


if(file_exists($json_file)) {
	$string = file_get_contents(dirname(__FILE__) . "/test.json");
	
	if($json_a = json_decode($string, true)) {
//		print_r($json_a);

		get_data($json_a);
		
	} else {
		echo 'Wrong json format';
	}
} else {
	echo 'File not exists!';
}

function get_data($data, $parent_id = 0) {
	foreach($data as $val) {
		if(!$val['active'])
			break;
		
		save_data(array('category' => array('id' => $val['id'], 'parent_id' => $parent_id, 'name' => $val['name']), 'news' => $val['news']));
		
		if(isset($val['subcategories'])) {
			get_data($val['subcategories'], $val['id']);
		}
	}
}

function save_data($data) {
	$sql = "insert into `categories` (id, parent_id, name) values (" . $data['category']['id'] . ", " . $data['category']['parent_id'] . ", '" . $data['category']['name'] . "');";
	mysql_query($sql) or die('Category insert error');
	if(isset($data['news'])) {
		$sql = "insert into `news` (id, category_id, title, image, description, text, created) values ";
		$is_news = false;
		foreach($data['news'] as $news) {
			if($news['active']) {
				$sql .= "(";
				$sql .= $news['id'] . ", ";
				$sql .= $data['category']['id'] . ", ";
				$sql .= "'" . $news['title'] . "', ";
				$sql .= "'" . $news['image'] . "', ";
				$sql .= "'" . $news['description'] . "', ";
				$sql .= "'" . $news['text'] . "', ";
				$sql .= "'" . $news['created'] . "' ";
				$sql .= "),";
				
				$is_news = true;
			}
		}
		
		if($is_news) {
			mysql_query(rtrim($sql, ','));
		}
	}
}

mysql_close();
