<?php
namespace core;

class Service extends Mysql{
   public $args;
  public $response;
  function __construct(){
    parent::__construct();
     $this->response=array(
      'state'=>false,
      'message'=>''
    );
  }
  public function getNewId(){
  return mysqli_insert_id($this->link);
}

public function add($key,$value){
  if(isset($this->response[$key])):
  array_push($this->response[$key],$value);
else:
  $this->response[$key]=array();
  array_push($this->response[$key],$value);
endif;
}
public function set($key,$val){
  $this->response[$key]=$val;
}
public function toJSON(){
echo json_encode($this->response);
}

}

 ?>
