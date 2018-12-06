<?php
namespace service;

class Data_grid extends \core\Service{

    public function __construct($args){

       parent::__construct();
       $this->args=$args;
    }

    public function run($action){
        if($this->link && $this->isValidParams()){
            mysqli_set_charset($this->link, "utf8");
            if(!is_null($action)):
            call_user_func(array($this,$action));
            endif;

        }else{

         $this->write();
        }
    }


    public function write($rows=array()){
        $this->response['connection']=($this->link)?true:false;
        $this->response['args']=$this->args;
         //print_r($this->response);
          //echo "<hr>";
         echo json_encode($this->response);
    }
    public function getRows(){

        $real_query=str_replace("[[filters]]",$this->args['sql_args'], $this->args['sql']);
        $this->args['real_query']=$real_query;

     $data=mysqli_query($this->link,$real_query);
     $this->response['rows']=array();
    // $data=mysqli_query($this->link,$this->args['sql']);

     $index=(int) $this->args['index'];
      while($row=mysqli_fetch_object($data)):
          foreach ($row as $key => $value) {
              $row->{$key}=urldecode($value);
          }
         $row->_selected_row_=false;
         $row->_index=$index++;
        # $row->_hashkey=uniqid();
         array_push($this->response['rows'],$row);
     endwhile;

     $this->write();
    }

    private function isValidParams(){
        $success=0;

        if(isset($this->args['sql'])){

            $success++;
        }


        return $success==1;
    }
  public function getSize(){
        if(isset($this->args['use_default'])){
        $this->args['sql']=str_replace("[[filters]]",$this->args['sql_args'], $this->args['sql']);
        }else{
            $this->args["sql"]=str_replace("[[rows_size]]"," as rows_size ",$this->args["sql"]);
            $this->args["sql"]=str_replace("[[filters]]",$this->args['filters'],$this->args["sql"]);
        }
        $records=mysqli_query($this->link,$this->args['sql']);
        if(isset($this->args["use_default"])){
            $this->response['size']=($records)?mysqli_num_rows($records):0;
        }else{
              $this->response['size']=0;
            if($row=mysqli_fetch_object($records)):
            $this->response['size']=$row->rows_size;
            endif;
        }

        $size=($this->response['size']/$this->args['limit']);
        $dbl=$size-floor($size);
        $this->response['pageSize']=round($size, 0, PHP_ROUND_HALF_UP);
        if($dbl<0.5 && $this->response['pageSize']>0){
           $this->response['pageSize']+=1;
        }
        $this->write();
    }
}
//$_POST['query']="select * from  cotizacion";//call  sp_list_cotizacion (1,1,' limit 5')

 ?>
