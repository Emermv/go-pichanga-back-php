<?php
namespace core;
class File_manager{

      public function firmar($args=array()){

      $fname=$args['file_name'];
  		$ubi_lnx_tmp = $args['DIR_TMP'];
  		$ubicacion_certificado= $args['DIR_CER'].'certificado.pem';

  		$ubicacion_documento = $ubi_lnx_tmp."/".$fname.".xml";
  		$ubicacion_documento_temp = $ubi_lnx_tmp."/".$fname."Temp.xml";
  		# Creamos el comando para firmar el documento y lo ejecutarmos
  		$comando_firmado = 'xmlsec1 --sign --privkey-pem '.$ubicacion_certificado.' '.$ubicacion_documento.' > '.$ubicacion_documento_temp;

      	passthru($comando_firmado,$bool);
  		unlink($ubicacion_documento);


  		if($bool)
  		{
  			return false;
  		}
  		else
  		{

  			rename($ubicacion_documento, $ubicacion_documento."borrar");
  			rename($ubicacion_documento_temp, $ubicacion_documento);
  			chmod($ubicacion_documento, 0777);

  			$documento = new \DOMDocument("1.0", "utf-8");
  			$documento->formatOutput = true;
  			$documento->load($ubicacion_documento);
  			$X509Data = $documento->getElementsByTagName("X509Data")->item(0);
  	    	$X509SubjectName = $documento->createElement("ds:X509SubjectName");
  	    	$X509Certificate = $documento->createElement("ds:X509Certificate");
  	    	$X509Data->appendChild($X509SubjectName);
  	    	$X509Data->appendChild($X509Certificate);
  	    	$DigestValue 	= $documento->getElementsByTagName("DigestValue")->item(0)->nodeValue;
  	    	$SignatureValue = $documento->getElementsByTagName("SignatureValue")->item(0)->nodeValue;
  	    	// Agregamos la informacion personal del firmante
  	    	$usuario = $documento->getElementsByTagName("ds:X509SubjectName")->item(0)->nodeValue = $this->_InformacionPersonal($args);
  	    	// Agregamos el certificado digital
  	    	$usuario = $documento->getElementsByTagName("ds:X509Certificate")->item(0)->nodeValue = $this->_CertificadoDigital($args);

  	    	$documento->save($ubicacion_documento);
  	    	return array(
  	    		'xml_hash' => $DigestValue,
  	    		'xml_signature' => $SignatureValue
  			);
  		}
  	}

  private function zip($args){

		$ubicacion_xml = $args['DIR_TMP']. $args['file_name'].".xml";
		$ubicacion_zip = $args['DIR_TMP']. $args['file_name'].".zip";

		$zip = new \ZipArchive();
		if($zip->open($ubicacion_zip, \ZipArchive::CREATE)===TRUE)
		{
			$zip->addFile($ubicacion_xml, $args['file_name'].".xml");
			$zip->close();

			$zip->open($ubicacion_zip);
			$conten_file = $zip->getFromName($args['file_name'].".xml");
			$zip->close();

			return array(
	    		'xml_b64' => base64_encode($conten_file)
			);
		}
		else
		{
			return false;
		}
	}
      public function create($args,$content){
      	$res_file = array();
      	try	{
  	    	 $path_file = $args['DIR_TMP'].$args['file_name'].'.xml';

  		    if ( ($archivo_xml = fopen($path_file, "w+"))!==false ) {
  		      	fwrite($archivo_xml, $content);

  				fclose($archivo_xml);

  				if(!$firma = $this->firmar($args))
  				{
  					throw new \Exception('Sucedió un error al firmar el documento');
  				}
  				else
  				{
  					$res_file = $firma;
  					if(!$comp = $this->zip($args))
  					{
  						throw new \Exception('Sucedió un error al comprimir');
  					}
  					else
  					{
  						$res_file = array_merge($res_file,$comp);
  						$res_file['xml_name'] = $args['file_name'];
  					}
  				}
  		    }
  		    else
  		    {
  				throw new \Exception('Sucedió un error al crear el archivo xml');
  		    }
      	} catch (\Exception $e)
      	{
      		$res_file['error'] = $e->getMessage();
      	}

      	return $res_file;
      }
      private function _InformacionPersonal($args){
    		$ubicacion_certificado= $args['DIR_CER'].'certificado.pem';
    		$certificado = file_get_contents($ubicacion_certificado);
    		$contenido = openssl_x509_read($certificado);
    		$contenido_x509 = openssl_x509_parse($contenido);

    		$obj_x509 = $contenido_x509['subject'];
    		$nombre = 'CN='.$obj_x509['CN'];
    		$organizacion = 'O='.$obj_x509['O'];
    		$desarrollo = 'UO='.$obj_x509['OU'][0];
    		$correo = 'E='.$obj_x509['emailAddress'];
    		$ciudad = 'C='.$obj_x509['C'];
    		$usuario_certificado = $nombre.", ".$organizacion.", ".$desarrollo.", ".$correo.", ".$ciudad;
    		return $usuario_certificado;
    	}

    	private function _CertificadoDigital($args){
    		$ubicacion_certificado= $args['DIR_CER'].'certificado_firma.pem';

    		$certificado = file_get_contents($ubicacion_certificado);
    		$cabecera = "-----BEGIN CERTIFICATE-----";
    		$pie = "-----END CERTIFICATE-----";

    		$nuevo_certificado = str_replace($cabecera, '', $certificado);
    		$nuevo_certificado = str_replace($pie, '', $nuevo_certificado);

    		$certificado_simplificado = trim($nuevo_certificado);
    		return $certificado_simplificado;
    	}
}

 ?>
