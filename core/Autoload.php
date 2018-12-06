<?php

spl_autoload_register(function ($class){
  $className = str_replace(['/',"\\"], DIRECTORY_SEPARATOR, $class);
//echo __DIR__."/../".$className.'.php';
if(file_exists(__DIR__."/../".$className.'.php')):
//echo "<br> namespaced ".__DIR__."/../".$className;
require_once __DIR__."/../".$className.'.php';
else:
  echo "<br> not found ".$class;
endif;

});


 ?>
