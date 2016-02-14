<?php
include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../init.php');

$status = 'success';
$message = '';

include_once(dirname(__FILE__).'/ph_simpleblog.php');
include_once(dirname(__FILE__).'/models/SimpleBlogPost.php');

$action = Tools::getValue('action');

switch ($action){

	case 'addRating':
		$simpleblog_post_id = Tools::getValue('simpleblog_post_id');
		$reply = SimpleBlogPost::changeRating('up', (int)$simpleblog_post_id);		
		$message = $reply[0]["likes"];
	break;

	case 'removeRating':
		$simpleblog_post_id = Tools::getValue('simpleblog_post_id');
		$reply = SimpleBlogPost::changeRating('down', (int)$simpleblog_post_id);		
		$message = $reply[0]["likes"];
	break;

	default:
		$status = 'error';
		$message = 'Unknown parameters!';
	break;
}
$response = new stdClass();
$response->status = $status;
$response->message = $message;
$response->action = $action;
echo json_encode($response);