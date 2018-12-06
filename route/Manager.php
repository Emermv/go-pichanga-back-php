<?php
namespace route;

class Manager{

  function __construct(){
  }

   public function validate($action,$token){
 $response=new \core\Response;
     $raw=file_get_contents('php://input');
     if(!is_null($raw) && $raw!=""):
       $data=json_decode($raw,true);
       //print_r($data);
       $client=new \service\Client;

       $company=$client->is_client($token);

     if(is_object($company)):
       $client->setBasic($data,$company);

       $invoice=new \core\Invoice($data,__DIR__.'/../assets');
           if(method_exists($invoice,$action)):

           $request=$invoice->{$action}();
          
            $response->set('data',$request->get('response'));
        else:
          $response->add('errors','El método invocado no existe!');
       endif;

    else:
    $response->add('errors','No es cliente de gofacturas :(');
    endif;
  else:
    $response->add('errors','Parámetros incorrectos!');
   endif;
   $response->toJSON();
  }
}


 ?>
