<?php
namespace core;
/**
 *
 */
class Response
{
  private $data;
  function __construct()
  {
    $this->data=array('message'=>'','state'=>false);
  }
  public function set($key,$val){
    $this->data[$key]=$val;
  }
  public function add($key,$val){
  if(isset($this->data[$key])):
    array_push($this->data[$key],$val);
  else:
      $this->data[$key]=array($val);
  endif;
  }
  public function toJSON(){
    echo json_encode($this->data);
  }
  public function get($key){ 
    return $this->data[$key];
  }

}


 ?>
