<?php
header("Access-Control-Allow-Origin: *");
error_reporting(0);
include "core/Autoload.php";
$router = new \core\Router;

$router->get('/',function(){
  echo "index";
});
$router->get('/test/(.*)',function($ruc){
  $crypt=new \core\Encrypt;

  echo "meyhod:{$crypt->ENC_METHOD}<br />";
  echo  $str= $crypt->encrypt(json_encode(array('ruc'=>$ruc)));
  echo "<br />".strlen($str);
  echo "<br />".$crypt->decrypt($str);

});

$router->post('/component/(\w+)/(.*)',function ($service,$action){
  //echo $service;
 $grid=new \service\Data_grid($_REQUEST); 
 $grid->{$action}();

 // $api->set('state',$api->{$action}($_REQUEST)?true:false);
});

$router->all('/(\w+)/(.*)',function ($service,$action){

  $service="\\service\\".ucfirst($service);
     $api=new $service;
  $api->set('state',$api->{$action}($_REQUEST)?true:false);
  $api->toJSON();
});



$router->set404(function(){
    header('HTTP/1.1 404 Not Found');
    $response=new \core\Response;
  $response->set('message','Página no ecnontrada!');
   $response->toJSON();
});
$router->run();

 ?>
