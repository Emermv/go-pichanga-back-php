<?php
namespace core;
require_once 'Config.php';
class Mysql{

public $link;

  public function __construct() {

    try {

      //$this->link=mysqli_connect(DEFAULT_DB['HOST'],DEFAULT_DB['USER'],DEFAULT_DB['PASSWORD'],DEFAULT_DB['NAME']);
     $this->link=mysqli_connect("localhost","go-pichanga","go-pichanga","go_pichanga");


   } catch (\Exception $e) {

        echo 'Error BD: ' . $e->getMessage();

      }

    }
   public function getLink(){
     return $this->link;
   }
   function builtSql($sql,$args){
    $values=explode(",","'".join(array_values($args),"','")."'");

    $query=str_replace(array_keys($args),$values,$sql);

     return $query;
  }
    public function execute($query, $params=NULL){

     $sql=empty($params)?$query:$this->builtSql($query,$params);
     //echo  $sql;
     return mysqli_query($this->link,$sql);

   }
    function toArray($request){
     $result=array();
     while($row=mysqli_fetch_object($request)):

       array_push($result,$row);
     endwhile;
     return $result;
   }
    function toObject($request){
     if($row=mysqli_fetch_object($request)){
       return $row;
     }
     return null;
   }
   function getNewId(){
    return mysql_insert_id($this->link);
   }

}

?>
