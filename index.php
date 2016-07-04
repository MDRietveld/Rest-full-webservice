<?php
include "connection.php";

$method = $_SERVER['REQUEST_METHOD'];
$accept = $_SERVER['HTTP_ACCEPT'];
$decks = mysqli_query($db, "SELECT * FROM hsdeck") or die(mysqli_connect_error());
$url = "https://stud.hosted.hr.nl/0894536/REST_0894536/";

switch ($method) {
	case 'GET':
	if ($accept == "application/json"){
		header("Content-Type: application/json");

    $getinhere = [];
    $hsdeck = [];
    // $link = [];
    $pagination = [];
    if(isset($_GET['id'])){
      $id = $_GET['id'];
      $detaildeck = mysqli_query($db, "SELECT * FROM hsdeck WHERE id = $id");
      if(mysqli_num_rows($detaildeck) != 0){
        while ($row = mysqli_fetch_assoc($detaildeck)){
            $hsdetail = array(
            'id' => $row['id'],
            'title' => $row['title'],
            'description' => $row['description'],
            'link' => $row['link'], 
            'links' =>  array(array('rel' => 'self', 'href' => $url . $row['id']), 
            array('rel' => 'collection', 'href' => $url)));
          echo json_encode($hsdetail);

        }
      }else{
        http_response_code(404);
      }
      
    }else{


      //hsdeck
      if(isset($_GET['limit'])){
        $limit = $_GET['limit'];
        $decklimit = mysqli_query($db, "SELECT * FROM hsdeck LIMIT $limit") or die(mysqli_connect_error());
        while ($row = mysqli_fetch_assoc($decklimit)){
        array_push($hsdeck, array(
          'id' => $row['id'],
          'title' => $row['title'],
          'description' => $row['description'],
          'link' => $row['link'], 
          'links' =>  array(array('rel' => 'self', 'href' => $url . $row['id']), 
          array('rel' => 'collection', 'href' => $url))));
        }
      }else{
        while ($row = mysqli_fetch_assoc($decks)){
        array_push($hsdeck, array(
          'id' => $row['id'],
          'title' => $row['title'],
          'description' => $row['description'],
          'link' => $row['link'], 
          'links' =>  array(array('rel' => 'self', 'href' => $url . $row['id']), 
          array('rel' => 'collection', 'href' => $url))));
        }
      }

      

      //Link
      $link = array(array('rel' => 'self', 'href' => $url));

      //Pagination
      $total = mysqli_num_rows($decks);
      if(isset($_GET['limit'])){
        $limit = $_GET['limit'];
      }else{
        $limit = $total;
      }
      // $limit = 5;
      $pages = ceil($total / $limit);
      $page = min($pages, filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, array(
          'options' => array(
              'default'   => 1,
              'min_range' => 1,
          ),
      )));
      $offset = ($page - 1)  * $limit;
      $end = min(($offset + $limit), $total);

      
      if(isset($_GET['start'])){
        $page = ceil($_GET['start'] / $limit);
        if($page == $pages){
          $next = $pages;
          $nextamount = ($pages - 1) * $limit + 1;
        }else{
          $nextamount = $_GET['start'] + $limit;
          $next = $page + 1;
        }
        if($page - 1 == 0){
          $previous = 1;
          $prevamount =1;
        }else{
          $prevamount = $_GET['start'] - $limit; 
          $previous = $page - 1; 
        }
      }else{
        $nextamount = 1 + $limit;
        $next = 2;
        $previous = 1;
        $prevamount = 1;
      }

      


      


      if(isset($_GET['limit'])){
        $last = ($pages - 1) * $limit + 1;
        $p_links = array(array('rel' => 'first',
          'page' => '1',
          'href' => $url . '?start=1&limit=' . $limit),
          array('rel' => 'last',
          'page' => $pages,
          'href' => $url . '?start=' . $last . '&limit=' . $limit),
          array('rel' => 'previous',
          'page' => $previous,
          'href' => $url . '?start=' . $prevamount . '&limit=' . $limit),
          array('rel' => 'next',
          'page' => $next,
          'href' => $url . '?start=' . $nextamount . '&limit=' . $limit));
      }else{
        $p_links = array(array('rel' => 'first',
          'page' => '1',
          'href' => $url),
          array('rel' => 'last',
          'page' => $pages,
          'href' => $url),
          array('rel' => 'previous',
          'page' => $previous,
          'href' => $url),
          array('rel' => 'next',
          'page' => $next,
          'href' => $url));
      }
      

      $pagination['currentPage'] = $page;
      $pagination['currentItems'] = $end;
      $pagination['totalPages'] = $pages;
      $pagination['totalItems'] = $total;

      
      $pagination['links'] = $p_links;

      
      $getinhere['items'] = $hsdeck;
      $getinhere['links'] = $link;
      $getinhere['pagination'] = $pagination;
      echo json_encode($getinhere);

    }



	} else if ($accept == "application/xml"){
    header("Content-Type: application/xml");




    $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";

    $xml .= "<notes><items>";
    while($row = mysqli_fetch_assoc($decks)){
      $xml .= "<item>";
      foreach($row as $type => $value){
        $xml .= "<$type>" . "$value" . "</$type>";

      }
      $self = $url . $row['id'];
      $xml .= "<links><link><rel>self</rel><href>$self</href></link><link><rel>collection</rel><href>$url</href></link></links>";

      $xml .= "</item>";
    }
    $xml .= "</items><links>";
    //links
    $xml .= "<link><rel>self</rel><href>$url</href></link>";
    $xml .= "</links>";
    //pagination
    // $total = mysqli_num_rows($decks);
    // $limit = 5;
    // $pages = ceil($total / $limit);
    // $page = min($pages, filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, array(
    //     'options' => array(
    //         'default'   => 1,
    //         'min_range' => 1,
    //     ),
    // )));
    // $offset = ($page - 1)  * $limit;
    // $end = min(($offset + $limit), $total);
    // $xml .= "</pagination>
    //   <currentPage>$page</currentPage>
    //   <currentItems>$end</currentItems>
    //   <totalPages>$pages</totalPages>
    //   <totalItems>$total_items</totalItems>
    //   <links>
    //     <link><rel>first</rel><page>1</page><href>$url</href></link>
    //     <link><rel>last</rel><page>1</page><href></href></link>
    //     <link><rel>previous</rel><page>1</page><href></href></link>
    //     <link><rel>next</rel><page>1</page><href></href></link>
    //   </links></pagination>";
    $xml .= "</notes>";


    echo $xml;
  } else{
    die (http_response_code(406));
  }


	break;
	
	case 'POST':
  if($_SERVER["CONTENT_TYPE"] == "application/json"){
    header("Content-type: application/json");
    http_response_code(201);
    $data = json_decode(file_get_contents("php://input"), true);
    $fields = array("title", "description", "link");
    foreach($fields as $field){
      if(!isset($data[$field]) || empty($data[$field])){
        http_response_code(400);
        echo $field . " is empty";
        exit;
      }
    }
    
    $sql = "INSERT INTO hsdeck (title, description, link) VALUES (
      '{$data['title']}', 
      '{$data['description']}', 
      '{$data['link']}')";
    mysqli_query($db, $sql) or die(mysqli_connect_error());
  }elseif (empty($_POST)){
    die (http_response_code(400));
  }elseif(!empty($_POST)){
    header("Content-type: application/x-www-form-urlencoded");
    http_response_code(201);

    $query = "INSERT INTO hsdeck (title, description, link) VALUES
    ('" . $_POST['title'] . "',
    '" . $_POST['description'] . "',
    '" . $_POST['link'] . "'  
    )";
    $sql = mysqli_query($db, $query) or die(mysqli_connect_error());
  }
  
	


	break;

  case 'OPTIONS':
  if(isset($_GET['id'])){
    header("Allow: GET, OPTIONS, DELETE, PUT");
  }else{
    header("Allow: GET, POST, OPTIONS");
  }
  break;

  case 'DELETE':
  if(isset($_GET['id'])){
    $id = $_GET['id'];
    $detaildeck = mysqli_query($db, "SELECT * FROM hsdeck WHERE id = $id");
    $row = mysqli_num_rows($detaildeck);
    if($row != 0){
      http_response_code(204);
      $query = "DELETE FROM hsdeck WHERE id = $id";  
      $sql = mysqli_query($db, $query) or die(mysqli_connect_error());
    }else{
      http_response_code(404);
    }
    

  }else{
    http_response_code(405);
  }
  // die (http_response_code(406));
  break;
  case 'PUT':
  if(isset($_GET['id'])) {
    header("Content-type: application/json");
    http_response_code(200);

    $id = $_GET['id'];
    $data = json_decode(file_get_contents("php://input"), true);
    $fields = array("title", "description", "link");
    foreach($fields as $field){
      if(!isset($data[$field]) || empty($data[$field])){
        http_response_code(400);
        echo $field . " is empty";
        exit;
      }
    }
    
    $sql = "UPDATE hsdeck SET title= '{$data['title']}', description='{$data['description']}', link='{$data['link']}' WHERE id = $id";
    mysqli_query($db, $sql) or die(mysqli_connect_error());
  }
  else{
      http_response_code(405);
  }
  break;
  
  	default:
  	# code...
	break;
}




/*if(isset($_POST['submit'])){ // button name
  $response = $client->post('', [
   'form_params' => [
   'title' => $_POST["title"],
   'description' => $_POST["desc"],
   'link' => $_POST["link"] ]
  ]);
}
  
$response = $client->get('');
	$json = json_decode($response->getBody());
foreach ($json as $list){
  $title = $list->title;
  ?><h2><?php print_r($title); ?></h2><?php
  $body = $list->body;
  ?><p><?php print_r($body); ?></p>
  <?php $id = $list->id;?>
  <a href="post.php/?id=<?php print_r($id); ?>">See page</a><?php
}*/

?>
