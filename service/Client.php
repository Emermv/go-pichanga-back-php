<?php
namespace service;
class Client extends \core\Service{

public function __construct(){
parent::__construct();
}
public function is_client($token){
  $crypt=new \core\Encrypt;

  $str=$crypt->decrypt($token);

  if(!is_null($str) && is_string($str) && $str!=""):
    $access=json_decode($str);
    $company=$this->execute("select ic.user,ic.password,ic.production_mode,ic.certificate_path,ic.state,c.code,c.name,c.ruc,c.ubigeo,c.database_name,c.direction,c.anexo,u.Departamento,u.Provincia,u.Distrito from companys as c left join invoice_config as ic on ic.company_id=c.id left join ubigeo as u on c.ubigeo=u.IdUbigeo where c.ruc=:ruc",
        array(':ruc'=>$access->ruc));
    if($company):

     return $this->toObject($company);
    endif;
  endif;
  return false;
}
public function getCoin($id){
  $sql="select code,name from coins where id=:id";

  return $this->toObject($this->execute($sql,array(':id'=>$id)));
}
public function setBasic(&$data,$company){

       if(isset($data['moneda'])):

        $coin=$this->getCoin($data['moneda']);

         $data['codmon']=$coin->code;
         $data['nommon']=$coin->name;
        endif;
       $data['empresa_id']=$company->empresa_id;
       $data['produccion']=$company->production_mode;
       $data['enviar_sunat']=(is_null($company->state) or $company->state==0 or abs($data['tipodoc'])==3)?"false":"true";
       $data['empresa_codigo']=$company->code;
       $data['empresa_numero']=$company->ruc;
       //$data['empresa_tipo_doc']=6;
       $data['empresa_nombre']=$company->name;
       $data['empresa_direccion']=$company->direction;
       $data['sunat_usuario']=$company->user;
       $data['sunat_clave']=$company->password;
       $data['local_ubigeo']=array(
        "ubi_codigo"=>$company->ubigeo,
        "ubi_ciudad"=>$company->Departamento,
        "ubi_provincia"=>$company->Provincia,
        "ubi_distrito"=>$company->Distrito,
        "anexo"=>$company->anexo
       );
}
}


 ?>
