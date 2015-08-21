<?php
session_start();
require_once "../config.php";
require_once filepath() . "app/sql.php";


/* Handles calls from the edit page.

		get - Gets items from project
		delete - Delets an itme from the project

*/
header('Content-Type: application/json');

/* Check variable */
if(!isset($_GET['action']))
{
  $errors = array("source"=>"main","error"=>"Action not set, exiting");
  throwError($errors);
}
if(!isset($_SESSION['project']))
{
  $errors = array("source"=>"main","error"=>"Session not set, exiting");
  throwError($errors);
}


$action = $_GET['action'];

switch($action)
{
  case 'get':
    getItems();
    break;
   case 'delete':
   	delItem();
   	break;


  default:
    $errors = array("source"=>"main","error"=>"Action not valid, exiting");
    throwError($errors);

}


function getItems()
{
	$project = $_SESSION['project'];
	$query = "SELECT o.irn, o.image_url,  o.accession_no, o.title FROM objectProject op 
	 LEFT JOIN objects o ON (op.object_irn = o.irn AND op.object_holder = o.holder)
	 WHERE op.project_id = " . sqlSafe($project);

	$res = readQuery($query);

	$return = array("objects" => array());

	 while ($row = $res->fetch_row()) 
	 {
        $jr = array();

        $jr['irn'] = $row[0];
        $jr['image_url'] = $row[1];
        $jr['accession_no'] = $row[2];
        $jr['title'] = $row[3];

        array_push($return['objects'], $jr);
    }

    print json_encode($return);

}
function delItem()
{
	if(!isset($_GET['irn']))
	{
	  $errors = array("source"=>"main","error"=>"IRN not set, exiting");
	  throwError($errors);
	}

	$project = $_SESSION['project'];
	$irn = $_GET['irn'];

	$query = "DELETE FROM objectProject WHERE object_irn = " . sqlSafe($irn) . " AND project_id = " . sqlSafe($project);
	writeQuery($query);

	 if(hasSQLerrors())
  	{
    	throwError(getSQLerrors());
  	}

	success();

}

function success()
{
  $response = array('success' => true);
  print json_encode($response);
  exit(0);
}


function throwError($errors)
{
  $response = array('success' => false, 'errors' => $errors);
  print json_encode($response);
  exit(-1);
}


?>