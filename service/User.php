<?php
namespace service;
class User extends \core\Service{

public function __construct(){
parent::__construct();
}
public function autenticate($args){

   $sql="select u.usuario,u.perfil,u.rol,u.tipo_usuario,p.* from usuario as u join persona as p on p.id=u.id_persona where u.usuario=:usuario and u.contrasenia=:password";
  $user=$this->execute($sql,array(
    ':usuario'=>$args['user'],
    ':password'=>$args['password']
  )); 
  if($row=mysqli_fetch_object($user)):
      $this->set('user',$row);
      return true;
  endif;
  return false;
}

public function create($args){
//$args=file_get_contents('php://input');


//$args=json_decode($args,true);

$sql="insert into persona(nombre,fecha_nacimiento,genero,biografia) values(:nombre,:fecha_nacimiento,:genero,:biografia)";
$this->set("args",$args);

$this->execute($sql,array(
   ':nombre'=>urlencode($args['nombre']),
   ':fecha_nacimiento'=>$args['fecha_nacimiento'],
   ':genero'=>$args['genero'],
   ':biografia'=>urlencode($args['biografia']),

));
$new_id=$this->getNewId();
$sql="insert into usuario(id_persona,usuario,contrasenia,perfil,rol,tipo_usuario) values(:id_persona,:usuario,:contrasenia,:perfil,:rol,:tipo_usuario)";

$path='assets/'.$new_id.'-'.$_FILES['perfil']['name'];
$uri="http://localhost/go-api/".$path;
$this->execute($sql,array(
   ':id_persona'=>$new_id,
   ':usuario'=>$args['usuario'],
   ':contrasenia'=>$args['contrasenia'],
   ':perfil'=>$uri,
   ':rol'=>$args['rol'],
   ':tipo_usuario'=>$args['tipo_usuario']
));

if(move_uploaded_file($_FILES['perfil']['tmp_name'], $path)):
return true;
endif;
return false;

}
}


 ?>
