<?php



function round_2pd($value='')
{
  return number_format((float)$value, 2, '.', '');
}

##
##
##

function AdditionalInformation($data=null)
{
  $monetary='';
  $property='';
  $total_gravada    = (isset($data['total_gravada'])) ? round_2pd($data['total_gravada']) : 0;
  $total_inafecta   = (isset($data['total_inafecta'])) ? round_2pd($data['total_inafecta']) : 0;
  $total_exonerada  = (isset($data['total_exonerada'])) ? round_2pd($data['total_exonerada']) : 0;
  $total_gratuita   = (isset($data['total_gratuita'])) ? round_2pd($data['total_gratuita']) : 0;
  $descuento_global = (isset($data['descuento_global'])) ? round_2pd($data['descuento_global']) : 0;
  $total_descuento  = (isset($data['total_descuento'])) ? round_2pd($data['total_descuento']) : 0;
  $descuentos_2005  = round_2pd($descuento_global+$total_descuento);

  if($total_gravada!=0)
  {
    $monetary .='
          <sac:AdditionalMonetaryTotal>
            <cbc:ID>1001</cbc:ID>
            <cbc:PayableAmount currencyID="'.$data['codmon'].'">'.$total_gravada.'</cbc:PayableAmount>
          </sac:AdditionalMonetaryTotal>';
  }
  if($total_inafecta!=0)
  {
    $monetary .='
          <sac:AdditionalMonetaryTotal>
            <cbc:ID>1002</cbc:ID>
            <cbc:PayableAmount currencyID="'.$data['codmon'].'">'.$total_inafecta.'</cbc:PayableAmount>
          </sac:AdditionalMonetaryTotal>';
  }
  if($total_exonerada!=0)
  {
    $monetary .='
          <sac:AdditionalMonetaryTotal>
            <cbc:ID>1003</cbc:ID>
            <cbc:PayableAmount currencyID="'.$data['codmon'].'">'.$total_exonerada.'</cbc:PayableAmount>
          </sac:AdditionalMonetaryTotal>';
  }
  if($total_gratuita!=0)
  {
    $monetary .='
          <sac:AdditionalMonetaryTotal>
            <cbc:ID>1004</cbc:ID>
            <cbc:PayableAmount currencyID="'.$data['codmon'].'">'.$total_gratuita.'</cbc:PayableAmount>
          </sac:AdditionalMonetaryTotal>';
  }
  if($descuentos_2005!=0)
  {
    $monetary .='
          <sac:AdditionalMonetaryTotal>
            <cbc:ID>2005</cbc:ID>
            <cbc:PayableAmount currencyID="'.$data['codmon'].'">'.$descuentos_2005.'</cbc:PayableAmount>
          </sac:AdditionalMonetaryTotal>';
  }
  if(isset($data['total']) && $data['total']!='')
  {
    $property .='
          <sac:AdditionalProperty>
            <cbc:ID>1000</cbc:ID>
            <cbc:Value>'.($data['total']).' '.$data['nommon'].'</cbc:Value>
          </sac:AdditionalProperty>';
  }
  if($total_gratuita!=0)
  {
    $property .='
          <sac:AdditionalProperty>
            <cbc:ID>1002</cbc:ID>
            <cbc:Value>TRANSFERENCIA GRATUITA DE UN BIEN Y/O SERVICIO PRESTADO GRATUITAMENTE</cbc:Value>
          </sac:AdditionalProperty>';
  }
  $this_cad = $monetary.$property;
  return $this_cad;
}

##
##
##

function LegalMonetaryTotal($data)
{
  $descuento_global = (isset($data['descuento_global'])) ? round_2pd($data['descuento_global']) : 0;
  $total_otros      = (isset($data['total_otros'])) ? round_2pd($data['total_otros']) : 0;
  $total            = (isset($data['total'])) ? round_2pd($data['total']) : 0;

  $lmt_cad = '
  <cac:LegalMonetaryTotal>';
  if($descuento_global!=0)
  {
    $lmt_cad.='
    <cbc:AllowanceTotalAmount currencyID="'.$data['codmon'].'">'.$descuento_global.'</cbc:AllowanceTotalAmount>';
  }
  if($total_otros!=0)
  {
    $lmt_cad.='
    <cbc:ChargeTotalAmount currencyID="'.$data['codmon'].'">'.$total_otros.'</cbc:ChargeTotalAmount>';
  }
  if($total!=0)
  {
    $lmt_cad.='
    <cbc:PayableAmount currencyID="'.$data['codmon'].'">'.$total.'</cbc:PayableAmount>';
  }

  $lmt_cad.='
  </cac:LegalMonetaryTotal>';
  return $lmt_cad;
}

##
##
##

function InvoiceLine($items,$codmon,$tag,$tag2)
{
  // print_r($items); 
  $line_cad="";
  foreach ($items as $key => $row)
  {
  $line     = $key+1;
  $descuento= (isset($row['descuento']))        ? round_2pd($row['descuento']) : 0;
  $subtotal = (isset($row['subtotal']))         ? round_2pd($row['subtotal']) : 0;
  $v_uni    = (isset($row['valor_unitario']))   ? round_2pd($row['valor_unitario']) : 0;
  $p_uni    = (isset($row['precio_unitario']))  ? round_2pd($row['precio_unitario']) : 0;
  $cantidad = (isset($row['cantidad']))         ? round_2pd($row['cantidad']) : 0;
  $igv      = (isset($row['igv']))              ? round_2pd($row['igv']) : 0;
  $tipo_igv = (isset($row['tipo_igv']))         ? $row['tipo_igv'] : '10';

  $cadena   = '10203040';
  $it_gr    = strpos($cadena, $tipo_igv);
  if ($it_gr === false) //no exista
  {
    $it_gr_val = $v_uni;
    $v_uni = $p_uni = $igv  = round(0);
  }

  $line_cad .= '
  <cac:'.$tag.'Line>
    <cbc:ID>'.$line.'</cbc:ID>
    <cbc:'.$tag2.' unitCode="'.$row['unidad'].'">'.$cantidad.'</cbc:'.$tag2.'>
    <cbc:LineExtensionAmount currencyID="'.$codmon.'">'.$subtotal.'</cbc:LineExtensionAmount>
    <cac:PricingReference>
      <cac:AlternativeConditionPrice>
        <cbc:PriceAmount currencyID="'.$codmon.'">'.$p_uni.'</cbc:PriceAmount>
        <cbc:PriceTypeCode>01</cbc:PriceTypeCode>
      </cac:AlternativeConditionPrice>';

  $line_cad .= ($it_gr === false) ? '
      <cac:AlternativeConditionPrice>
        <cbc:PriceAmount currencyID="'.$codmon.'">'.$it_gr_val.'</cbc:PriceAmount>
        <cbc:PriceTypeCode>02</cbc:PriceTypeCode>
      </cac:AlternativeConditionPrice>' : '';

  $line_cad .= '
    </cac:PricingReference>';
  $line_cad .= ($descuento>0) ? '
    <cac:AllowanceCharge>
      <cbc:ChargeIndicator>false</cbc:ChargeIndicator>
      <cbc:Amount currencyID="'.$codmon.'">'.$descuento.'</cbc:Amount>
    </cac:AllowanceCharge>' : '';

  $line_cad .= '
    <cac:TaxTotal>
      <cbc:TaxAmount currencyID="'.$codmon.'">'.$igv.'</cbc:TaxAmount>
      <cac:TaxSubtotal>
        <cbc:TaxAmount currencyID="'.$codmon.'">'.$igv.'</cbc:TaxAmount>
        <cac:TaxCategory>
          <cbc:TaxExemptionReasonCode>'.$tipo_igv.'</cbc:TaxExemptionReasonCode>
          <cac:TaxScheme>
            <cbc:ID>1000</cbc:ID>
            <cbc:Name>IGV</cbc:Name>
            <cbc:TaxTypeCode>VAT</cbc:TaxTypeCode>
          </cac:TaxScheme>
        </cac:TaxCategory>
      </cac:TaxSubtotal>
    </cac:TaxTotal>
    <cac:Item>
      <cbc:Description><![CDATA['.$row['descripcion'].']]></cbc:Description>
    </cac:Item>
    <cac:Price>
      <cbc:PriceAmount currencyID="'.$codmon.'">'.$v_uni.'</cbc:PriceAmount>
    </cac:Price>
  </cac:'.$tag.'Line>';
  }
  return $line_cad;
}

##
##
##

function getInvoice($data)
{
  switch ($data['tipodoc']) {
    case '1':
      $tag = 'Invoice';
      $tag2 = 'InvoicedQuantity';
      break;
    case '3':
      $tag = 'Invoice';
      $tag2 = 'InvoicedQuantity';
      break;
    case '7':
      $tag = 'CreditNote';
      $tag2 = 'CreditedQuantity';
      break;
    case '8':
      $tag = 'DebitNote';
      $tag2 = 'DebitedQuantity';
      break;
  }
  $fe_e = date('Y-m-d',strtotime($data['fecha_emision']));
  $fe_v = date('Y-m-d',strtotime($data['fecha_vencimiento']?$data['fecha_vencimiento']:$data['fecha_emision']));

  $cadena = '<?xml version="1.0" encoding="UTF-8"?>';
  $cadena.= '<'.$tag.' xmlns="urn:oasis:names:specification:ubl:schema:xsd:'.$tag.'-2" xmlns:sac="urn:sunat:names:specification:ubl:peru:schema:xsd:SunatAggregateComponents-1" xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2" xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2" xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2" xmlns:ds="http://www.w3.org/2000/09/xmldsig#">
  <ext:UBLExtensions>
    <ext:UBLExtension>
      <ext:ExtensionContent>
        <sac:AdditionalInformation>';

  $cadena.= AdditionalInformation($data);

  $cadena.= '
        </sac:AdditionalInformation>
      </ext:ExtensionContent>
    </ext:UBLExtension>
    <ext:UBLExtension>
      <ext:ExtensionContent>
        <ds:Signature Id="GOFACTURAS">
          <ds:SignedInfo>
            <ds:CanonicalizationMethod Algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315"/>
            <ds:SignatureMethod Algorithm="http://www.w3.org/2000/09/xmldsig#rsa-sha1"/>
            <ds:Reference URI="">
              <ds:Transforms>
                <ds:Transform Algorithm="http://www.w3.org/2000/09/xmldsig#enveloped-signature"/>
              </ds:Transforms>
              <ds:DigestMethod Algorithm="http://www.w3.org/2000/09/xmldsig#sha1"/>
              <ds:DigestValue></ds:DigestValue>
            </ds:Reference>
          </ds:SignedInfo>
          <ds:SignatureValue></ds:SignatureValue>
          <ds:KeyInfo>
            <ds:X509Data>
            </ds:X509Data>
          </ds:KeyInfo>
        </ds:Signature>
      </ext:ExtensionContent>
    </ext:UBLExtension>
  </ext:UBLExtensions>
  <cbc:UBLVersionID>2.0</cbc:UBLVersionID>
  <cbc:CustomizationID>1.0</cbc:CustomizationID>
  <cbc:ID>'.$data['serie'].'-'.$data['numero'].'</cbc:ID>
  <cbc:IssueDate>'.$fe_e.'</cbc:IssueDate>';

  $cadena.= (isset($data['documento_tipo']) && $data['documento_tipo'] != '') ? '' : '
  <cbc:InvoiceTypeCode>0'.$data['tipodoc'].'</cbc:InvoiceTypeCode>';

  $cadena.= '
  <cbc:DocumentCurrencyCode>'.$data['codmon'].'</cbc:DocumentCurrencyCode>';
  //<cbc:ExpiryDate>'.$fe_v.'</cbc:ExpiryDate>';

  $tipo_nota      = str_pad($data['tipo_nota'], 2, "0", STR_PAD_LEFT);
  $tipodoc        = str_pad($data['tipodoc'], 2, "0", STR_PAD_LEFT);
  $documento_tipo = str_pad($data['documento_tipo'], 2, "0", STR_PAD_LEFT);

  $cadena.= (isset($data['documento_tipo']) && $data['documento_tipo'] != '') ? '
  <cac:DiscrepancyResponse>
    <cbc:ReferenceID>'.$data['documento_serie'].'-'.$data['documento_numero'].'</cbc:ReferenceID>
    <cbc:ResponseCode>'.$tipo_nota.'</cbc:ResponseCode>
    <cbc:Description>'.$data['tipo_nota_des'].'</cbc:Description>
  </cac:DiscrepancyResponse>
  <cac:BillingReference>
    <cac:InvoiceDocumentReference>
      <cbc:ID>'.$data['documento_serie'].'-'.$data['documento_numero'].'</cbc:ID>
      <cbc:DocumentTypeCode>'.$documento_tipo.'</cbc:DocumentTypeCode>
    </cac:InvoiceDocumentReference>
  </cac:BillingReference>' : '';

  $cadena.= ($data['orden_compra'] != '') ? '
  <cac:OrderReference>
    <cbc:ID>'.$data['orden_compra'].'</cbc:ID>
  </cac:OrderReference>' : '';

  $cadena.='
  <cac:Signature>
    <cbc:ID>'.$data['empresa_numero'].'</cbc:ID>
    <cac:SignatoryParty>
      <cac:PartyIdentification>
        <cbc:ID>'.$data['empresa_numero'].'</cbc:ID>
      </cac:PartyIdentification>
      <cac:PartyName>
        <cbc:Name><![CDATA['.$data['empresa_nombre'].']]></cbc:Name>
      </cac:PartyName>
    </cac:SignatoryParty>
    <cac:DigitalSignatureAttachment>
      <cac:ExternalReference>
        <cbc:URI>'.$data['empresa_numero'].'</cbc:URI>
      </cac:ExternalReference>
    </cac:DigitalSignatureAttachment>
  </cac:Signature>
  <cac:AccountingSupplierParty>
    <cbc:CustomerAssignedAccountID>'.$data['empresa_numero'].'</cbc:CustomerAssignedAccountID>
    <cbc:AdditionalAccountID>6</cbc:AdditionalAccountID>
    <cac:Party>
      <cac:PartyName>
        <cbc:Name><![CDATA['.$data['empresa_nombre'].']]></cbc:Name>
      </cac:PartyName>
      <cac:PostalAddress>
        <cbc:ID>'.$data['local_ubigeo']['ubi_codigo'].'</cbc:ID>
        <cbc:StreetName><![CDATA['.$data['empresa_direccion'].']]></cbc:StreetName>
        <cbc:CityName>'.$data['local_ubigeo']['ubi_ciudad'].'</cbc:CityName>
        <cbc:CountrySubentity>'.$data['local_ubigeo']['ubi_provincia'].'</cbc:CountrySubentity>
        <cbc:District>'.$data['local_ubigeo']['ubi_distrito'].'</cbc:District>
        <cac:Country>
          <cbc:IdentificationCode>PE</cbc:IdentificationCode>
        </cac:Country>
      </cac:PostalAddress>
      <cac:PartyLegalEntity>
        <cbc:RegistrationName><![CDATA['.$data['empresa_nombre'].']]></cbc:RegistrationName>
      </cac:PartyLegalEntity>
    </cac:Party>
  </cac:AccountingSupplierParty>
  <cac:AccountingCustomerParty>
    <cbc:CustomerAssignedAccountID>'.$data['cliente_numero'].'</cbc:CustomerAssignedAccountID>
    <cbc:AdditionalAccountID>'.$data['cliente_tipodoc'].'</cbc:AdditionalAccountID>
    <cac:Party>
      <cac:PartyLegalEntity>
        <cbc:RegistrationName><![CDATA['.$data['cliente_nombre'].']]></cbc:RegistrationName>
      </cac:PartyLegalEntity>
    </cac:Party>
  </cac:AccountingCustomerParty>
  <cac:TaxTotal>
    <cbc:TaxAmount currencyID="'.$data['codmon'].'">'.round_2pd($data['total_igv']).'</cbc:TaxAmount>
    <cac:TaxSubtotal>
      <cbc:TaxAmount currencyID="'.$data['codmon'].'">'.round_2pd($data['total_igv']).'</cbc:TaxAmount>
      <cac:TaxCategory>
        <cac:TaxScheme>
          <cbc:ID>1000</cbc:ID>
          <cbc:Name>IGV</cbc:Name>
          <cbc:TaxTypeCode>VAT</cbc:TaxTypeCode>
        </cac:TaxScheme>
      </cac:TaxCategory>
    </cac:TaxSubtotal>
  </cac:TaxTotal>';
  $cadena.= LegalMonetaryTotal($data);
  $cadena.= InvoiceLine($data['items'],$data['codmon'],$tag,$tag2);
  $cadena.= '
</'.$tag.'>';
// echo htmlentities($cadena);
  return $cadena;
}

?>
