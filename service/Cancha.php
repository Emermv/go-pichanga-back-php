<?php
namespace service;
class Cancha extends \core\Service{

public function __construct(){
parent::__construct();
}

public function  get(){
 $sql="select id,nombre,descripcion,tipo,direccion from cancha";
 $request=$this->execute($sql);
 $rows=$this->toArray($request);
 foreach($rows as $key=>$value):
  $value->nombre=urldecode($value->nombre);
 endforeach;
  $this->set('rows',$rows);
  return $request;

}
public function create($args){

$sql="insert into cancha(nombre,descripcion,tipo,direccion,latitud,longitud,altitud,id_usuario) values(:nombre,:descripcion,:tipo,:direccion,:latitud,:longitud,:altitud,:id_usuario)";
 
 $this->execute($sql,array(
   ':nombre'=>urlencode($args['nombre']),
   ':descripcion'=>urlencode($args['descripcion']),
   ':tipo'=>$args['tipo'],
   ':direccion'=>urlencode($args['direccion']),
   ':latitud'=>$args['latitud'],
   ':longitud'=>$args['longitud'],
   ':altitud'=>$args['altitud'],
   ':id_usuario'=>$args['id_usuario']

));   
$new_id=$this->getNewId();
$sql="insert into cancha_imagen(id_cancha,imagen) values(:id_cancha,:imagen)";
$size=abs($args['image_size']);
$image_states=0;
for($i=0;$i<$size;$i++):
$path='assets/canchas/'.$new_id.'-'.$_FILES['image-'.$i]['name'];
$uri="http://142.93.52.143/go-api/".$path;
$request=$this->execute($sql,array(
   ':id_cancha'=>$new_id,
   ':imagen'=>$uri
));
if(move_uploaded_file($_FILES['image-'.$i]['tmp_name'], $path) && $request):
$image_states++;
endif;
endfor;

if($image_states==$size):
  $this->set('meesage',"Cancha {$args['nombre']} ha sido creado correctamente!");
  return true;
endif;
$this->set('meesage',"Ocurrió un error inesperado, por favor intentelo nuevamente!");
return false;

}
}

 ?>
