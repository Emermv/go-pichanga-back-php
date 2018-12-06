<?php
namespace core;

class Invoice {
private $response;
private $data;
public $DIR_CER;
public $DIR_TMP;
  function __construct($data=array(),$path=__DIR__){
  $this->data=$data;
  $this->DIR_CER="{$path}/{$data['empresa_codigo']}/cer/";
  $this->DIR_TMP="{$path}/{$data['empresa_codigo']}/tmp/";
  $this->response=new \core\Response;
  }

public function comprobante(){
 $document=new \template\Invoice_note($this->data);
$xml=$document->getXML();
if(!is_null($xml) && is_string($xml) && $xml!=""):
   $doc = str_pad($this->data['tipodoc'], 2, "0", STR_PAD_LEFT);
  $fname = $this->data['empresa_numero']."-".$doc."-".$this->data['serie']."-".$this->data['numero'];
  $file_manager=new \core\File_manager;
  $file=$file_manager->create(array(
    'file_name'=>$fname,
    'DIR_CER'=>$this->DIR_CER,
    'DIR_TMP'=>$this->DIR_TMP,
  ),$xml);
  //print_r($file);
  if(!isset($file['error'])):
    $objWs = new \service\Gofac($this->data,array(
      'DIR_TMP'=>$this->DIR_TMP,
    ));
    $objWs->setMetodo('sendBill');
    $objWs->setName($fname);
    $ws = $objWs->comprobante();
    $response = array_merge($file,$ws);
    $this->response->set('response',$response);
  else:
    $this->response->set('response',$file);
  endif;
endif;
return $this->response;
}

}


 ?>
