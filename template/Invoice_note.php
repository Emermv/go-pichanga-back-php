<?php
namespace template;

class Invoice_note{
  private $data;
  function __construct($data){
    $this->data=$data;
    // code...
  }
  public function getXML(){
    $xml="";
    include 'invoice.php';

    return getInvoice($this->data);
  }


}

 ?>
