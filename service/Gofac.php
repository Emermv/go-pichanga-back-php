<?php
namespace service;
class Gofac{
	private $data;
	private $metodo;
	private $fname;
	private $wsdl;
	private $args;
	private $ticket;
  private $DIR_TMP;

	/**
	* @param array
	*/

	function setMetodo($val){
		$this->metodo=$val;
	}

	function setName($val){
		$this->fname=$val;
	}

	function setTicket($val){
		$this->ticket=$val;
	}

	function __construct($data=array(),$args){
		$this->data = $data;
    $this->DIR_TMP=$args['DIR_TMP'];
	}

	/**
	 * @access private
	 * @return array / detalle de la factura y su estado
	 * */

	function comprobante(){
		$beta 	= 'https://e-beta.sunat.gob.pe/ol-ti-itcpfegem-beta/billService?wsdl';

		//$prod 	= __DIR__.'/wsdl/sunatws.wsdl';
    	$prod="https://www.sunat.gob.pe/ol-ti-itcpgem-sqa/billService?wsdl";
		$this->wsdl = ($this->data['produccion']==0) ? $beta : $prod;
		$this->args = $this->_generalArgs();
		$res = $this->_soapConexion();

		if (isset($res['soap']))
		{
			$xml_res = $this->unzipByteArray($res['soap']->applicationResponse);
			$response = $this->_generalResArray($xml_res);
			unset($res['soap']);
			$res = array_merge($res,$response);
		}

		return $res;
	}

	/**
	 * anular / boletas
	 * @access private
	 * @return array / ticket de la operación
	 * */

	function resumen_documentos()
	{
		$beta 	= 'https://e-beta.sunat.gob.pe/ol-ti-itcpfegem-beta/billService?wsdl';
		//$prod 	= $this->data['dirTmp'].'../ModWebServSunat/wsdl/sunat/sunatws.wsdl';
		$prod 	= __DIR__.'/wsdl/sunatws.wsdl';
		$this->wsdl = ($this->data['produccion']==0) ? $beta : $prod;
		$this->args = $this->_generalArgs();
		$res = $this->_soapConexion();

		if (isset($res['soap']))
		{
			$ticket = $res['soap']->ticket;
			$res['sunat_ticket'] = $ticket;
			unset($res['soap']);
		}

		return $res;
	}


	/**
	 * consultar el estado de los tickets
	 * @access private
	 * @return array
	 * */

	function consultar_ticket()
	{
		$beta 	= 'https://e-beta.sunat.gob.pe/ol-ti-itcpfegem-beta/billService?wsdl';
		//$prod 	= $this->data['dirTmp'].'../ModWebServSunat/wsdl/sunat/sunatws.wsdl';
		$prod 	= __DIR__.'/wsdl/sunatws.wsdl';
		$this->wsdl = ($this->data['produccion']==0) ? $beta : $prod;
		$this->args = array(array('ticket'=>$this->data['ticket']));
		$res = $this->_soapConexion();

		if (isset($res['soap']))
		{
			if ($res['soap']->status->statusCode!=98) {
				$xml_res	= $this->crearZip($res['soap']->status->content,$this->data['ticket']);
				$response 	= $this->_generalResArray($xml_res);
				unset($res['soap']);
				$res = array_merge($res,$response);
			}
			$res['sunat_ticket'] = $this->data['ticket'];
			$res['sunat_code_response'] = $res['soap']->status->statusCode;
		}

		return $res;
	}


	/**
	 * consulta estado de los comprobantes
	 * @access private
	 * @return array
	 * */

	function consultar_estado()
	{
		$this->wsdl = 'https://e-factura.sunat.gob.pe/ol-it-wsconscpegem/billConsultService?wsdl';
		$tipodoc = str_pad($this->data['tipodoc'], 2, "0", STR_PAD_LEFT);
		$etiqueta = ($this->data['cdr']==0) ? 'status' : 'statusCdr';
		$this->args = array(
			array(
				'rucComprobante'    => $this->data['empresa_numero'],
				'tipoComprobante' 	=> $tipodoc,
				'serieComprobante' 	=> $this->data['serie'],
				'numeroComprobante' => $this->data['numero']
			)
		);

		$res = $this->_soapConexion();

		if (isset($res['soap']))
		{
			$soapx = $res['soap'];
		 	if($soapx->statusCdr->content!=''){
		        $fname = $this->data['empresa_numero'].$this->data['serie'].$this->data['numero'];
		        $xml_res	= $this->crearZip($soapx->statusCdr->content,$fname);
				$response 	= $this->_generalResArray($xml_res);
				unset($res['soap']);
				$res = array_merge($res,$response);
		    }

	        $res['statusCode'] = $soapx->$etiqueta->statusCode;
	        $res['statusMessage'] = $soapx->$etiqueta->statusMessage;
		}

		return $res;
	}



	private function _generalArgs(){
		$filename = $this->fname.'.zip';
		$argumentos = array(
			array(
				'fileName' 		=> $filename,
				'contentFile' 	=> file_get_contents($this->DIR_TMP.$filename),
				'partyType' 	=> ''
			)
		);
		return $argumentos;
	}



	private function _generalResArray($xml_res)
	{
		$leer_resul = $this->LeerCdr($xml_res);
		$codigo_sunat = (int) $leer_resul['sunat_codigo'];
		$status = ($codigo_sunat==0) ? 1 : 2;
		$leer_resul['xml_cdr_b64'] = base64_encode($xml_res);
		$leer_resul['sunat_aceptado'] = $status;
		return $leer_resul;
	}

	/**
	 * _soapConexion()
	 *
	 * @access private
	 * @return string xml
	 *
	 * */

	private function _soapConexion()
	{
		$res = array(
			'sunat_notas' 		=> '',
			'sunat_codigo' 		=> '',
			'sunat_descripcion' => '',
			'fault_descrip'		=> '',
			'xml_cdr_b64' 		=> '',
			'sunat_aceptado' 	=> '0'
		);

		if($this->data['enviar_sunat']=="false")
		{
			return $res;
		}

		try
		{
			#Header
			$soap = new \SoapClient($this->wsdl, [
				'trace' 		=> TRUE ,
				//'cache_wsdl' 	=> WSDL_CACHE_NONE,
				'cache_wsdl' => WSDL_CACHE_MEMORY,
				'soap_version' 	=> SOAP_1_1 ]
			);

			# WSEE, php soap no tiene soporte para wsee, hay que generarlo manualmente.
			$WSHeader 	= $this->wseeHeader();
			$oasis 		= "http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd";
			$headers 	= new \SoapHeader($oasis, 'Security', new \SoapVar($WSHeader, XSD_ANYXML));

			$ws = $soap->__soapCall($this->metodo,$this->args,null,$headers);
			$res['soap'] = $ws;
		}
		catch(\Exception $e)
		{
			$res['fault_descrip'] = ($e->getMessage());
			$res['fault_code'] = $e->faultcode;
		}

		return $res;
    }

	/**
	 * wseeHeader()
	 *
	 * @access private
	 * @return string xml
	 *
	 * */

	private function wseeHeader()
	{
		# Credeciales para autentificacion ws
    	$cwsse = '<wsse:Security xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
    				<wsse:UsernameToken>
    					<wsse:Username>' . $this->data['empresa_numero'] . $this->data['sunat_usuario'] . '</wsse:Username>
    					<wsse:Password>' . $this->data['sunat_clave'] . '</wsse:Password>
					</wsse:UsernameToken>
				</wsse:Security>';

		return $cwsse;
    }


    /**
	 * unzipByteArray(x)
	 *
	 * @access private
	 * @param string
	 * @return string xml
	 *
	 * */

    private function unzipByteArray($data)
    {
		/*this firts is a directory*/
		$head = unpack("Vsig/vver/vflag/vmeth/vmodt/vmodd/Vcrc/Vcsize/Vsize/vnamelen/vexlen", substr($data,0,30));
		$filename = substr($data,30,$head['namelen']);
		$if=30+$head['namelen']+$head['exlen']+$head['csize'];
		/*this second is the actua file*/
		$head = unpack("Vsig/vver/vflag/vmeth/vmodt/vmodd/Vcrc/Vcsize/Vsize/vnamelen/vexlen", substr($data,$if,30));
		$raw = gzinflate(substr($data,$if+$head['namelen']+$head['exlen']+30,$head['csize']));
		/*you can create a loop and continue decompressing more files if the were*/
		return $raw;
	}

	/**
	 * crearZip(x,y)
	 *
	 * @access private
	 * @param string
	 * @param string
	 * @return array
	 *
	 * */

	private function crearZip($cont_xml,$nombre_file){
		$file_zp = $this->DIR_TMP.'R-'.$nombre_file.'.zip';
		$archivo = fopen($file_zp,'w+');
		fputs($archivo, $cont_xml);
		fclose($archivo);

		$zip = new \ZipArchive;
		if ($zip->open($file_zp))
		{
		    $indice  = $zip->numFiles-1;
		    $xmlFile = $zip->getNameIndex($indice);
		    $content = $zip->getFromName($xmlFile);
		    $zip->close();
		    return $content;
		} else {
		    echo 'zip open failed failed';
		}
	}


	/**
	 * LeerCdr(x)
	 *
	 * @access private
	 * @param string
	 * @return array
	 *
	 * */

	private function LeerCdr($data_xml)
	{
		$xml_cdr 	= addslashes($data_xml);
		$cdr 		= preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $data_xml);
		$leer_xml 	= simplexml_load_string($cdr);
		$notas_str 	= '';
		if(isset($leer_xml->cbcNote))
		{
			$notas = $leer_xml->cbcNote;
			foreach ($notas as $key => $value) {
				$notas_str.= (string)$value."/ ";
			}
			$notas_str=substr($notas_str,0,-2);
		}
		$codigo 	= (string)$leer_xml->cacDocumentResponse->cacResponse->cbcResponseCode;
		$mensaje 	= (string)$leer_xml->cacDocumentResponse->cacResponse->cbcDescription;

		$data = array(
			'sunat_notas' 		=> $notas_str,
			'sunat_codigo' 		=> $codigo,
			'sunat_descripcion' => $mensaje
		);
		return($data);
	}

}

?>
