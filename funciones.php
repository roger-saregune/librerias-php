<?php

/**
  *
  * Librería general de funciones.
  * @author Roger
  * @version 2011-03-1
  * @licence GPL

  * 2011/03/01 Añadio js_confirm (again)
  * 2010/09/29 Repaso de mqueryString. 
  *            + mquerystringToArray
  *            + mquerystringFromArray
  *            c mquerystringAdd. Solo admite un argumento y devuelve string
  *            c mquerystringDel. Solo admite un argumento y devuelve string
  * 2010/06/29 + mensajeHTML
  * 2010/06/28 corregido mimplode
  * 2010/05/21 correciones en mysql_template : /uims
  * 2010/05/19 ereg sustituido por preg_match
  * 2010/05/11 mprint tiene un 3º parametros opcional HTML=true
  * 2010/05/11 por_defecto ahora devuelve el ultimo argumento
  * 2010/05/04 recuperar funciones de fechas.
  * 2010/04/30 Correciones repetidas
  * 2010-03-22 + por_defecto
  * 2010-03-03 + filesize2bytes
  * 2010-03-03 + bytes2filesize
  * 2010-02-25 + add_querystring_var
  * 2010-02-25 + remove_querystring_var
  * 2010-02-03 c mysql_template.
  * 2010-01-30 + fecha_formato
  * 2010-01-30 - fecha_php_mysql, fecha_mysql_php
  * 2009-11-24 + mQueryStringDel()
  * 2009-11-24 + mQueryStringAdd()  
  * 2009-11-24 + urlAddHttp ..por nomenclatura
  * 2009-07-19 c msyql_template..ahora es incompatible con anterior.
  * 2009-06-25 + mimplode
  * 2009-06-14 m mysql_plantilla..ahora con %[campo]
  * 2009-06-14 + mysql_template
  * 2009-05-08 c mysql_plantilla      
  * 2009-06-14 + mysql_template
  * 2009-05-08 c mysql_plantilla
  * 2009-04-14 + url_add_http
  * 2009-01-00 + mdate_diff( $f, $desde, $hasta )1
  * 2008-11-17 + MesEof, MesBof.
  * 2008-10-20 + mDate
  *            c fecha_php_mysql. detecta formato: MM/DD/AAAA y lo pasa a AAAA/MM/DD
  * 2008-03-13 + limpiaRequest 
  * 2008-03-13 r listaSQL a mysql_mlistaSQL
  * 2008-03-13 r listaClavesSQL a mysql_mlistaClavesSQL
  * 2008-03-12 + mTabla
  * 2008-02-26 c mesDe, diaDe
  *            + mControl
  * 2008-02-20 + lista_archivos
  * 2008-01-28 + cerrar_etiquetas.
  * 2007-09-27 + mysql_plantilla
  * 2007-04-27 + in_lista
  * 2007-04-17 + mysql_query_lista añadido parámetro hash.
  * 2007-01-12 + m_split ( cadena, longitudlinea, cSaltoDeLinea, cPrefijo )
  * 2006-09-12 + ampliado extrae_ExtensionNombre() a path.
  * 2006-06-30 + mysql_CampoClave ()
  *            + extrae_ExtensionNombre()
  * 2006-06-23 c limpiaSQLinjectionTexto.
  * 2006-06-21 - booleantoSiNoConClase
  *            + añadido  mysql_query_registro
  * 2006-06-15 + mysql_query_lista
  *            + limpiaSQLinjectionTexto,Numero,Busca,ID
  * 2006-02-16 + fecha_mysql_php 
  *            + fecha_php_mysql
 */


/*
 * miscelania
 */

function mPrint($que,$level=1, $html=true){
   if (is_array($que)) {
      $level++;
      foreach( $que as $i=>$valor){
         echo ( $html ? "<br>": "\n" ), str_repeat( ($html?"&nbsp;": " "),$level*3) ,"$i = ";
         mPrint($valor,$level,$html);            
      }   
   } else {
        echo $que;
   }
}

function por_defecto(){
	foreach ( func_get_args() as $arg ){
  		if ($arg) {
  			return $arg;
  		}
	}
	return $arg;
}




/**
 * como date pero tiene en cuenta el idioma.
 *  
 * Las fechas tambien se pueden hacer con:
 * setlocale (LC_TIME, "sp");
 * strftime("%A, %d de %B."), Lunes, 5 de Septiembre
 * pero requiere tener instalado el idioma seleccionado en el servidor.
 *
 */


function fecha_formato ( $fecha, $idioma="es", $formato=""){
    switch ($idioma){
        case "es": 
            preg_match( '#([0-9]{1,2})[-/]([0-9]{1,2})[-/]([0-9]{1,4})#ui', $fecha, $temp);
            $dia    = $temp[1];
            $mes  = $temp[2];
            $anno = $temp[3];
            if ( !$formato ) {
                $formato="d/m/Y"; // DD-MM-AAAA
            }
            break;
        case "eu": 
            preg_match( '#([0-9]{2,4})[-/]([0-9]{1,2})[-/]([0-9]{1,2})#ui', $fecha, $temp);
            $dia    = $temp[3];
            $mes  = $temp[2];
            $anno = $temp[1];
            if ( !$formato ) {
                $formato="Y/m/d"; // AAAA-MM-DD
            }
            break;
        default  : 
            preg_match( '#([0-9]{1,2})[-/]([0-9]{1,2})[-/]([0-9]{1,4})#ui', $fecha, $temp);
            $dia    = $temp[2];
            $mes  = $temp[1];
            $anno = $temp[3];
            if ( !$formato ) {
                $formato="m/d/Y";  // MM/DD/AAAA
            }
   }
    return date($formato, mktime( 0,0,0, $dia, $mes, $anno));
}


/**
 * Convierte fecha de mysql a normal
 * 
 * Las fechas tambien se pueden hacer con:
 * setlocale (LC_TIME, "sp");
 * strftime("%A, %d de %B."), Lunes, 5 de Septiembre
 * pero requiere tener instalado el modulol idionma seleccionado en el servidor.
 *
 */

function fecha_mysql_php($fecha, $formato='Y/m/j'){
   $tempFecha = str_replace ( '/', '-', $fecha);
   preg_match( '#([0-9]{2,4})-([0-9]{1,2})-([0-9]{1,2})#ui', $tempFecha, $mifecha);
   /* TODO añadir mas marcadores */
   return strtr ( $formato,
       array (
           'Y' => $mifecha[1],
           'y' => substr($mifecha[1],2,2),
           'j' => ltrim($mifecha[3],'0') ,
           'd' => $mifecha[3],
           'n' => ltrim($mifecha[2],'0'),
           'm' => $mifecha[2] ));
}

/**
 * Convierte fecha de normal a mysql.
 */

function fecha_php_mysql($fecha, $formato='y/m/d'){
   $fecha   = str_replace ( '-', '/', $fecha);

   // intentamos una conversión simple entre MM/DD/AAAA a AAAA/MM/DD  
   if ( preg_match( "#([0-9]{1,2})/([0-9]{1,2})/([0-9]{4})#ui", $fecha, $regs ) ) {
      if ( $regs[2] > 12 ) {
         $temp    = $regs[2];
         $regs[2] = $regs[1];
         $regs[1] = $temp;
      }
      $fecha = "$regs[3]/$regs[2]/$regs[1]";
   }
  
          
   preg_match( '#([0-9]{1,4})/([0-9]{1,2})/([0-9]{1,2})#ui', $fecha, $mifecha);
   $cRet = strtr( $formato, array (
       '-'  => '/',
       'd' =>$mifecha[3],
       'j' =>$mifecha[3],
       'm' =>$mifecha[2],
       'n' =>$mifecha[2],
       'Y' =>$mifecha[1],
       'y' =>$mifecha[1] ));
   return $cRet;
}



/**
 * Convierte fecha de normal a mysql.
 */

// Pasa del formato DD/MM/AAAA al formato AAAA-MM-DD 00:00:0
function mmktime($fecha){
	if ( preg_match( '#([0-9]{1,4})[/-]([0-9]{1,4})[/-]([0-9]{1,4})#ui', $fecha, $afecha) ){
		return mktime (0,0,0,$afecha[2],$afecha[3],$afecha[1] );
	}
	return '';
}


function formatoFechaToDateTimeMySql($fecha){
	if ( preg_match( '#([0-9]{1,4})/([0-9]{1,4})/([0-9]{1,4})#ui', $fecha, $afecha) ){
		return ( 	$afecha[3] . '-' . $afecha[2] . '-' . $afecha[1] . ' 00:00:00' );
	}
	return '';
}


// Pasa del formato DD/MM/AA o DD/MM/AAAA al formato AAAA-MM-DD
function formatoFechaToDateMySql($fecha){
	if ( preg_match( '#([0-9]{1,2})/([0-9]{1,2})/([0-9]{1,4})#ui', $fecha, $afecha) ){
		return  (strlen($afecha[3])<3?'20':"") . $afecha[3] . '-' . $afecha[2] . '-' . $afecha[1] ;
	}
	return '';
}


// Pasa del formato AAAA-MM-DD a DD/MM/AA
function formatoMySqlToFecha($fecha){
	if ( preg_match( '#([0-9]{1,4})-([0-9]{1,2})-([0-9]{1,2})#ui', $fecha, $afecha) ){
		return  $afecha[3] . '-' . $afecha[2] . '-' .  substr($afecha[1] ,2,2);
	}
	return '';
}


/**
 * Devuelve el mes de castellano o euskera. 
 * Util, si no funcionan las locale.
 */
function mesDe( $fecha ='', $idioma='es' ){
   if ( is_numeric($fecha)) {
      $mes= $fecha; 
   } elseif ( $fecha=='') {
   	$mes = date('n');
   } else {
   	$mes = date('n', $fecha );
   };

   $aMeses= array ( 
      'es' => array (
               'Enero', 'Febrero', 'Marzo', 
   	         'Abril', 'Mayo',	'Junio', 
   	         'Julio', 'Agosto', 'Septiembre', 
   	         'Octubre', 'Noviembre', 'Diciembre' ),
   	'eu' => array (
   	      	'Urtarila', 'Otsaila', 'Martxoa', 
   	         'Aprila', 'Maiatza',	'Ekaina', 
   	         'Uztaila', 'Abuztua', 'Iraila', 
   	         'Urria', 'Azaroa', 'Abendua' ) );         
   return $aMeses[$idioma][($mes-1)];
}


/**
* Devuelve el literal dia de una fecha (de hoy si se omite)
*/
function diaDe( $fecha = '', $idioma='es'){
   if ( is_numeric ($fecha) ){
      $dia = $fecha ;
   } elseif ( $fecha =='' ) {
      $dia = date('w');
   } else {
      $dia = date('w', $fecha );
   }   
   
   $aDias= array (
      'es' => array ('Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sabado'),
      'eu' => array ('Igandea', 'Astelehena', 'Asteartea', 'Asteazkena', 'Osteguna', 'Ostirala', 'Larunbata' ) );
   return $aDias[$dia];
}


function MesEof($fecha,$mes="") {
   if ( $mes=="" ){
     $anno= date("Y",$fecha);
     $mes = date("n",$fecha);
   } else {
      $anno= $fecha;
      $fecha= mktime(0,0,0,$mes,1,$anno);       
   }

   return mktime(0,0,0,$mes, date("t",$fecha), $anno );
}


function MesBof($fecha,$mes="") {
   if ( $mes=="" ){
     $anno= date("Y",$fecha);
     $mes = date("n",$fecha);
   } else {
      $anno= $fecha;          
   }

   return mktime(0,0,0,$mes, 1, $anno );
}


function mdate_diff( $f, $desde, $hasta ){
   if ( $desde > $hasta ) {
     $temp = $desde;
     $desde = $hasta;
     $hasta = $temp;
   }
   $dif = $hasta - $desde;
   switch ($f) {
    case "d": return floor($dif/3600*24); // dias
    case "h": return floor($dif/3600);    // horas
    case "i": return floor($dif/60);      // segundos
    case "s": return $dif;
    case "i:s"  : return floor($dif/60). ":" . $dif%60; // minutos:segundos
    case "h:i:s": return floor($dif/3600) . ":" . floor(($dif%3600)/60). ":" . $dif%60; // horas:minutos:segundo
   }
   return 0;
}






/**
* Pasa un valor 0/1 a No Si (0 es NO, y otro valor SI)
* @return Si | No 
*/
function booleanToSiNo( $Valor, $ClaseSI='', $ClaseNO=''){
   if ($Valor==1) {
   	return ( $ClaseSI =='' ? 'Si' : "<span class='$ClaseSI'>Si</span>" );
   } else {
   	return ( $ClaseNO =='' ? 'No' : "<span class='$ClaseNO'>No</span>" );
   }
}


/**
* Devuelve la fecha dada o hoy en formato aaaa/mm/dd.
*/ 
function mDate ($cCual='', $idioma='es'){
   switch ($idioma){
     case "es": $formato = "d/m/Y"; break;
     case "eu": $formato = "Y/m/d"; break;
     default  : $formato = "d/m/Y"; break;   
   }
   return ($cCual==''? date($formato) : date($formato, strtotime($cCual)));
}


/**
* Devuelve la fecha dada o vacio  en formato aaaa/mm/dd.
*/ 
function mFecha ($cCual){
   return ($cCual==0? '&nbsp;': date('Y/m/d', strtotime($cCual)));
}


/***/

function url_add_http($url){
return ( preg_match ("|^(https?://)|",  $url ) ? $url : "http://$url" );
}

function urlAddHttp($url){
return ( preg_match ("|^(https?://)|",  $url ) ? $url : "http://$url" );
}

/**
 * manejo variables de sessión y request.
 */
function mRequest($cCual){
   $cTemp='';
   if (isset( $_REQUEST[$cCual])) {
      $cTemp= $_REQUEST[$cCual];
   	$_SESSION[$cCual]= $cTemp;
   } elseif (isset( $_SESSION[$cCual])){
      $cTemp= $_SESSION[$cCual];
   }
   return $cTemp;
}


function mQueryStringToArray ( $q=-1) {
    if ( $q===-1) {
        $q= $_SERVER["QUERY_STRING"];
    }
    $resul= array();
    if ( strpos ($q,"=") !== false ){
        $pares= explode("&",$q);    
        foreach ($pares as $par ){
            list($variable,$valor)=explode("=",$par);
            $resul[$variable] = urldecode($valor);
        }
    }
    return $resul;
}

function mQueryStringFromArray ($pares) {
    $resul ="";    
    foreach ( $pares as $i=>$v){      
        $resul .= ( $resul ? "&" : "" ) . $i . "=". urlencode($v);                
    }
    return $resul;
    }

function mQueryStringAdd ($nuevas, $query=-1){   
   $q= array_merge (mQueryStringToArray($query), $nuevas );      
   return mQueryStringFromArray($q);
}

function mQueryStringDel ($borrar, $query=-1){
   $q= mQueryStringToArray($query);
   foreach ($borrar as $clave ){
        unset( $q[$clave] );
   }
   return mQueryStringFromArray($q);
}


 function add_querystring_var($url, $key, $value) {
     // FUENTE: http://snipplr.com/view/8323/add-and-remove-querystring-variables/
     $url = preg_replace('/(.*)(\?|&)' . $key . '=[^&]+?(&)(.*)/i', '$1$2$4', $url . '&');
     $url = substr($url, 0, -1);
     if (strpos($url, '?') === false) {
     	return ($url . '?' . $key . '=' . $value);
     } else {
     	return ($url . '&' . $key . '=' . $value);
     }
}


function remove_querystring_var($url, $key) {
    // FUENTE: http://snipplr.com/view/8323/add-and-remove-querystring-variables/
      $url = preg_replace('/(.*)(\?|&)' . $key . '=[^&]+?(&)(.*)/i', '$1$2$4', $url . '&');
      $url = substr($url, 0, -1);
      return ($url);
      }


function mRequest_id($cCual='ID'){
   if (isset($_REQUEST[$cCual])) {
      return limpiaSQLinjectionID($_REQUEST[$cCual]);
   }
   return false;
}





/**
*
*   F U N C I O N E S      DE      A V I S O
*
*/

function js_confirm ($texto) {
   return " onclick=\"return confirm('". t($texto) . "');\" ";
}

function paginaHTML($cMensaje, $title='ERROR', $css=''){
   return "<html><head><title>$title</title>" .
          ($css ? "<link rel='stylesheet' href='$css'>" : "" ) ."</head>".
          "<body>$cMensaje</body></html>";
}

function mensajeHTML ( $texto, $tipo="OK" ) {
   return  "\n<div class='mensaje-{$tipo}'>$texto</div>\n";                         
}


/** 
*
*    F U N C I O N E S      DE      C A D E N A
*
*/

function m_split ( $s , $nMax, $br = "\n", $pre="&lt; " ) {
  
  $a= explode( " ", $s);
  $ret = $pre;
  $actual = 0;
  
  foreach ( $a as $c) {    
    if (strlen($c)+$actual > $nMax ) {
       $actual = 0 ;
       $ret .= "$br$pre$c";
    } else {
       $ret .= ( $actual==0 ? "" : " ") . $c;
    }
    $actual += strlen($c); 
  }
  return $ret;
}


function corta ( $cadena, $longitud=80., $annadir =".."){
   return substr( strip_tags("".$cadena.""),0,$longitud).  (strlen($cadena)>$longitud? $annadir :"" );
}


function completaUrl ( $cURL ) {
   if ( substr($cURL,0,7)=='http://' or substr($cURL,0,8)=='https://' ) {
      return $cURL ;
   } else {
      return 'http://'. $cURL;
   }
}

function plantilla_opcion ( $valor, $plantilla0, $plantilla1="", $plantilla2="") {
 
 $plantilla2= por_defecto ( $plantilla2, $plantilla1, $plantilla0);
 $plantilla1= por_defecto ( $plantilla1, $plantilla0);
   
 switch ( $valor ) {
    case 0 : return strtr( $plantilla0, array ("%0"=>$valor ) );
    case 1 : return strtr( $plantilla1, array ("%0"=>$valor ) );
    default: return strtr( $plantilla2, array ("%0"=>$valor ) );  
 }

}

function lista(){
   foreach ( func_get_args() as $i=>$valor ) {
      if ($i==0) {
         $separador=$valor;
      } else {
         $resultado.= ( $i==1 ? "" : $separador ) . $valor;
      }
   }   
   return $resultado;
}


function mimplode ($patron, $array, $separador = "") {

    foreach ($array as $k=>$valor) {
        $cRet .= $sep . sprintf($patron, $k, $valor);
        $sep = $separador;
    }
    
    return $cRet;
}

/**
*
*      F U N C I O N E S      DE      FICHEROS
*
*/

/*
* Ejemplos de resultados.
* foo          => ext="",nombre="foo", tiene=false
* foo.         => ext="",nombre="foo", tiene=true
* foo.bar      => ext="bar",nombre="foo", tiene=true
* foo.bar.ex   => ext="ex",nombre="foo.bar", tiene=true
*/


function extrae_ExtensionNombre ( $cFileCompleto ){
	$lEsPathRelativo = true;
  	
  	$nAt = strrpos ( $cFileCompleto, "/");
  	if ( $nAt === false ) {
      $cNombreFichero  = $cFileCompleto;
      $cPath           = "" ;
      $lHayPath        = false;            
   } else {
      $cPath           = substr($cFileCompleto,0,$nAt+1);
      $cNombreFichero  = substr($cFileCompleto,$nAt+1);
      $lHayPath		  = true;      
      $lEsPathRelativo = ($nAt>0);        
   }
        
   $nAt = strrpos ( $cNombreFichero, ".");
   if ( $nAt === false ) {
      $cNombre = $cNombreFichero;
      $lHayExtension = false;
      $cExt    = "";
   } else {
   	$lHayExtension = true;
      $cNombre = substr($cNombreFichero,0,$nAt);
      $cExt    = substr($cNombreFichero,$nAt+1);     
   }
   return array ( "path" => $cPath, "nombre" =>$cNombre , "ext" =>$cExt, 
   					"hayPath"=>$lHayPath, "hayExt"=>$lHayExtension, "esPathRelativo"=>$lEsPathRelativo );
}


/* 
 * devuelve un array con todos los ficheros de una determinada extensión
 * de un directorio. Acepta una extensión o varias: "jpg", "jpeg|jpg".
 * Con PHP 4 se puede utilizar glob.
 */
function lista_archivos ( $path, $extension ){
    $aRet = array();
    if ( is_dir ($path) ) {
        $rsDir = opendir ($path);
        while (($archivo = readdir($rsDir)) !== false) {
            if ( preg_match ( "/\.$extension\$/i", $archivo ) ) {
                $aRet[]= $archivo;
            }
        }
        closedir($rsDir);
    }
    return $aRet;
}

/*
 * @author Svetoslav Marinov (con modificaciones )
 */
function filesize2bytes($str) {
    $bytes = 0;

    $bytes_array = array("B"=>0,"K"=>1,"M"=>2,"G"=>3, "T"=>4, "P"=>5 );

    $bytes = floatval($str);
    $str =  str_ireplace ( "bytes","B", $str);
    $str =  str_ireplace ( "byte","B", $str);

    if (preg_match('#([BKMGTP])B?$#si', $str, $matches) && !empty($bytes_array[$matches[1]])) {
        $bytes *= pow(1024,$bytes_array[$matches[1]]);
    }

    $bytes = intval(round($bytes, 2));

    return $bytes;
}

/*
 * @author: nak5ive@gmail.com
 */
function bytes2filesize($bytes, $precision = 2) {
    //
    $units = array('B', 'KB', 'MB', 'GB', 'TB');

    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);

    $bytes /= pow(1024, $pow);

    return round($bytes, $precision) . ' ' . $units[$pow];
}



/**
*
*      F U N C I O N E S      DE      L I S T A    Y    SQL             
*
*/


/**
* formatear errores de Mysql
*/  
function mysql_merror(){
   return "<div style='align:left'><B>ERROR MYSQL</B><BR>C&oacute;digo: ". mysql_errno(). "<br>Descripci&oacute;n: " . mysql_error() . "</div>";
}


/**
 * función para generar una plantilla combinada con una consulta SQL. La consulta
 * debe devolver entre 1 y 10 valores.
 * @deprecated usar mysql_template
 * @return plantilla generada
 */
 
function mysql_plantilla( $cSQL , $cPlantilla, $cInicio="", $cNoHay="" ){
   if ( $cInicio ) {
     $cTemplate = cerrar_etiquetas ( $cInicio,"{%REPEAT" . $cPlantilla. "REPEAT-END%}" );
   } else { 
     $cTemplate = $cPlantilla;
   }
   $cTemplate = preg_replace ( "/%([0-9])/Uis", '%[$1]',$cTemplate );
   return mysql_template ( $cSQL, $cTemplate, $cNoHay);
}

 
function mysql_template( $cSQL , $cTemplate, $cNoHay="" ){
  $cRet      = "" ;
  $cTemp     = "" ; 
  $rsEmaitza = mysql_query ( $cSQL );
   
  // examinamos la template para extrar la plantilla repetitiva
  if ( $hayTemplate= preg_match ( "/{%REPEAT(.*)REPEAT-END%}/Uis", $cTemplate, $aResul) ){
    $cPlantilla = $aResul[1];   
  } else {
    $cPlantilla= $cTemplate;
  }
  
  if ( $hayWhen = stripos ( $cPlantilla, "{%WHEN" )!== false ){
     $cPlantilla = preg_replace ("/{%WHEN ([^:]*):(.*)WHEN-END%}/Uim", '{%WHEN $1%:%$2WHEN-END%}', $cPlantilla );         
  }
         
  
  // examinamos las sumas
  if ( preg_match_all ( "/%suma([0-9])/",$cTemplate, $temp) ){
    foreach ( $temp[1] as $valor) {
       $sumas[$valor]=0;
    }   
  } else {
    $sumas = false;
  }  
  
  // calculos la plantilla repetitiva
  $contador  = 0;  
  $par       = true;
  while ( $lerroa = mysql_fetch_array ( $rsEmaitza, MYSQL_BOTH ) ) {
     $contador++;
     $par = !$par;
     
     //sumas 
     if ( $sumas ) {
       foreach ($sumas as $key => $value ) {
          $sumas[$key] += $lerroa[$key] ;
       }
     }     
     
     $lerroa [ "contador"] =  $contador ;
     $lerroa [ "par"]          =  ( $par ? "par" : "impar" ) ;      
     foreach ( $lerroa as $k=>$valor ){                  
       $sustituciones [ "%[$k]" ] = $valor ;                     
     }       
     
     $cRet .= strtr ( $cPlantilla, $sustituciones );
  
  }
  mysql_free_result ($rsEmaitza);
  if ( !$contador and $cNoHay ){
    return $cNoHay;
  }
  
  // insertar resultado repetitivo en template.
  if ( $hayTemplate ){
    $cRet = str_replace ( $aResul[0], $cRet, $cTemplate );
  }
  $cRet = strtr ($cRet, array ("%total"=>$contador ) );
  
  // sumas 
  if ( $sumas ) {
     foreach ($sumas as $key => $value ) {
        $rSumas["%suma$key"] = $value ;
     }
     $cRet = strtr( $cRet, $rSumas );       
  }  
  
  // quitamos las condicionales (When)
  if ( $hayWhen ) {
     //quitar las condicionales vacias.
     //$cRet = preg_replace ("/{%WHEN[ ]*%:%.*WHEN-END%}/Uims",  "", $cRet );
     $cRet = preg_replace ("#{%WHEN [-\ 0]*%:%.*WHEN-END%}#Uims",  "", $cRet );

     //quitar los WHEN...  
     // este filtro no funciona si no se han eliminado todas las condificones vaciones.   
     $cRet = preg_replace ("/{%WHEN (.)+%:%(.*)WHEN-END%}/Uims",  '$2', $cRet );
  }

  // ahora solo quedan las traducciones
  if ( preg_match_all ( "/%t\[([^\]]*)\]/", $cRet, $ts )){    
    foreach ( $ts[1] as $t ){
       $tsus["%t[$t]"]= t($t);       
    } 
    $cRet = strtr ( $cRet, $tsus );         
  }  
  
  /* Correcciones finales */ 
  $cRet = str_replace  ("%%" , "%", $cRet );  
    
  return $cRet;
} 


/**
 * función para devuelve el nombre del campo clave primaria de una tabla
 * @return nombre campo clave
 */
function mysql_campoClave ( $tabla ) {
	$rsEmaitza = mysql_query("SHOW COLUMNS FROM $tabla");
	$aurkitu      = "";
	while ( $lerroa = mysql_fetch_array ( $rsEmaitza)  ){
		if ( $lerroa["Key"] =="PRI" ) {
			$aurkitu = $lerroa[0];
			break;
		}
	}
	mysql_free_result ( $rsEmaitza );
	return $aurkitu;
}


/**
 * funcion para localizar rapidamente un dato mediante SQL.
 * @param  Sentencia SQL-SELECT
 * @return El primer campo select, o NULL si no devuelve registros.
 */
function mysql_mlookup( $cSql){
	$ret = NULL;
	$rsEmaitza = mysql_query($cSql);
	if ( $rsEmaitza ) {
		if ( $temp= mysql_fetch_array($rsEmaitza) ) {
      	$ret = $temp[0];
      }
     	mysql_free_result($rsEmaitza);
	}
   return $ret;
}


/**
 * funcion para localizar rapidamente un dato mediante SQL.
 * @param  Sentencia SQL-SELECT
 * @return El primer campo select, o NULL si no devuelve registros.
 */
function mysql_mexiste( $cSql){
   $ret = false;
	$rsEmaitza = mysql_query( $cSql);
	if ($rsEmaitza and mysql_fetch_array($rsEmaitza)){
      $ret= true;
	}
	mysql_free_result ( $rsEmaitza);
   return $ret;
}


/**
 * función para obtener una lista a partir de una sentencia SQL.
 * @param  Sentencia SQL-SELECT
 * @return array con lista.
 */
function mysql_query_lista($cSql, $hash = false){   
   $aTemp = false;
	$rsEmaitza = mysql_query( $cSql);
	while ( $lerroa = mysql_fetch_array($rsEmaitza)){
	   if ( $hash )
	   	$aTemp[$lerroa[0]]= $lerroa[1];
	   else 
      	$aTemp[]= $lerroa[0];
	}
	mysql_free_result ( $rsEmaitza);
   return $aTemp;
}


/**
 * función para obtener el primer registro de un sentencia SQL.
 * @param  Sentencia SQL-SELECT
 * @return El primer registro o FALSE
 */
function mysql_query_registro($cSql){
	$rsEmaitza = mysql_query($cSql);
	$lerroa    = mysql_fetch_array($rsEmaitza);
	mysql_free_result ( $rsEmaitza);
   return $lerroa;
}


/**
 * función para obtener todos los registros de un sentencia SQL.
 * @param  Sentencia SQL-SELECT
 * @return Todos los registros o FALSE 
 */
function mysql_query_registros($cSql){
	$rsEmaitza = mysql_query($cSql);
	$temp      = false;
	while  ( $temp[]= mysql_fetch_array($rsEmaitza) ){ 
   }	
	mysql_free_result ( $rsEmaitza);	
   return $temp;
}


/*
* Construye un array a partir de una sentencia SQL
*/
function mysql_mlistaSQL ( $cSQL, $aValores= array() ){
   $resultado= mysql_query($cSQL);
   while ( $fila = mysql_fetch_array($resultado)){
   	$aValores[]= $fila[0];	
   }
   mysql_free_result ( $resultado );
   return $aValores;	
}		


/**
* construye un array hash a partir de una sentencia SQL, que devuelve primero la clave
* y luego el valor.
*/
function mysql_mlistaClavesSQL ( $cSQL, $aValores= array() ){
   $resultado= mysql_query( $cSQL);
   while ( $fila = mysql_fetch_array($resultado)){
   	$aValores[ $fila[0] ] = $fila[1];	
   	}
   mysql_free_result ( $resultado );
   return $aValores;	
}		


/**
 * algunas funciones para hacer limpieza .
 */
function limpiaRequest($cCual){
   return ( isset($_REQUEST[$cCual]) ? limpiaSQLinjectionTexto($_REQUEST[$cCual]) : "" );
}

function limpiaSQLinjectionTexto ( $cTexto ){
   return strtr($cTexto, array("'"=>"\"", "$" =>""));
}

function limpiaSQLinjectionBuscar ( $cTexto ){
   return strtr($cTexto, array("'"=>"\"", "\"" =>"", "$" =>"" , "*" =>""));
}

function limpiaSQLinjectionID ( $cTexto ){
   return ( (int) $cTexto );
}

function limpiaSQLinjectionNumero ( $cTexto ){
   return ( is_numeric($cTexto) ? $cTexto : -1 );
}


/**
* funcion para devolver el literal de la lista
* @param  aLista, aValor
* @return el literal de la lista o aValor si no se encuentra.
* @deprecated by in_array (php 4)
*/
function in_lista ( $aguja ){
	$aDatos = func_get_args();
	foreach($aDatos as $i=>$valor ){
		if ( $i > 0 and $aguja==$valor ){
		   return true;
		}
	}
	return false;
}


/**
 * Examina si clave esta en lista. Esta pued
 * ser un array o una cadena separada por "|"
 */

function mLista(  $aLista, $clave){	
	if ( is_array ( $aLista) ) {
		$cTemp = $aLista[$clave];
	} else {
		$aOtro = explode("|", $aLista);
		$cTemp = $aOtro[$clave];
	}	   
	return ( $cTemp ?  $cTemp :  $clave );
}

		

/**
*
*    F U N C I O N E S      DE      I N P U T S
*
*/


/**
* devuelve un cadena HTML para un Select de una lista de valores
*/
function mInputLista( $aValores, $cName, $cDefecto="", $cEstilo="" ){
   $cRet="<select name='$cName' class='$cEstilo'>\n";
   foreach( $aValores as $unValor ){
     $cRet.= "<option value='". $unValor . "' ". ($cDefecto==$unValor?"selected": "") .">$unValor</option>\n";     
     }
   $cTemp.="</select>\n" ;
   return $cRet;
}


function mInputSiNo01 ( $cName , $cInicial, $cClass=""){
	$cRet  ="<select name='$cName'";
	if ( $cClass!="") {
	   if ( is_array($cClass) ){
	      foreach ( $cClass as $atributo => $valor ) {
	         $cRet .= " $atributo='$valor'"; 
	      }
      } else {	   
		   $cRet .= " class='$cClass' ";
		}
	}
	$cRet .= ">" ;
	$cRet .= "<option value='0' ". ($cInicial==0?" SELECTED ": "") .">No</option>\n" ;
	$cRet .= "<option value='1' ". ($cInicial==1?" SELECTED ": "") ." >Si</option>\n" ;
	$cRet .= "</select>";
	return $cRet;
}


/**
* devuelve un cadena HTML para un Select de una lista hash (clave, valor). 
*/
function mInputListaClaves( $aValores, $cName, $valorInicial, $atributos=""){
$cTemp ="\n<select name='$cName' ". ( $cEstilo!="" ? "class='$cEstilo' " : "" ) . ">\n";
foreach( $aValores as $clave => $valor ){
  $cTemp.= sprintf( "<option value='$clave'%s>$valor</option>\n",
                 ($clave==$valorInicial?" selected='selected'": "" ));
  }
$cTemp.="</select>\n" ;
return $cTemp;
}


/**
* Construye un input a partir de una sentecni Select SQL que devuelve clave, valor.
*/
function mInputSQL ( $cSQL, $cName, $cValorInicial, $cEstilo="", $opcionInicial=""){
   if ( $opcionInicial) {
   	if ( is_array ( $opcionInicial ) ) {
   	   $TempList = $opcionInicial;
   	} else {
    		$TempList =  array ( $opcionInicial );
    	}
   	$TempList = mysql_mlistaClavesSQL($cSQL, $TempList);
   } else {
   	$TempList = mysql_mlistaClavesSQL($cSQL);
   }
   $cEstilo = ( $cEstilo ? " class='$cEstilo'" :"" );//@TODO revisar
   return mInputListaClaves ( $TempList, $cName, $cValorInicial, $cEstilo );
}


/**
* Select de un entero booleano (0/1 pasa a NO/SI)
*/
function mInputCheckbox( $cName, $cInicial, $cValue=1){
   return "<input type='checkbox' name='$cName' value='$cValue'" . ($cInicial==$cValue ? " checked='checked' ": "" ) . ">";
}


/**
* Devuelve un control según el tipo especificado)
*/

function mControl ( $cTipo, $cEtiqueta, $cName, $cValor="", $cEstilo="dl", $aOpciones="" ){
   
   $cId =  ( isset($aOpciones['id']) ? $aOpciones['id'] : $cName );
   $cAdicional = "id='$cId'" ;
   if ( isset($aOpciones['class'])){
      $cAdicional .= " class='{ " . $aOpciones["class"] . "}'" ;
   }    
         
   // construir el input      
   $cLabel = "<label for='$cId'>$cEtiqueta</label>";   
   switch ( $cTipo ){
      case "cadena":
            $campo = "<input type='text' name='$cName' value='$cValor' $cAdicional >";
            break;
      
      case "texto" :            
            $campo = "<textarea name='$cName' $cAdicional>$cValor</textarea>";         
            break;

      case "boolean": 
      case "booleano":
            $cValue = ( isset($aOpciones['value']) ? $aOpciones['value'] : 1 );      
            $cCampo = "<input type='radio' name='$cName' $cAdicional value='$value'" . ($cValor? " selected ": "" ) . ">";            
            break;
   }

   if ( isset($aOpciones["adicional"] )) {
      $Campo .= ' ' . $aOpciones["adicional"];
   }
   
   // construir la label
   if ( isset($aOpciones["obligatorio"] )) {
      $cLabel = "<label for='$cId' class='obligatorio'>$cEtiqueta <span class='obligatorio'>(*)</span></label>";
   } else {
      $cLabel = "<label for='$cId'>$cEtiqueta</label>";   
   }

   // maquetar label y control según estilo
   $aEstilo = array (
      "dl"    => '<dd>%s</dd><dt>%s</dt>',
      "p"     => '<p>%s %s</p>',
      "p-br"  => '<p>%s <br> %s</p>',
      "table" => '<tr><th>%s</th><td>%s</td></tr>');   
   
   return sprintf( $aEstilo[$cEstilo], $cLabel,$cCampo ) . "\n";
            
}

/*
 * funciones para el manejo de etiquetas
 */

/**
* cierra las etiquetas dadas.
*/
function cerrar_etiquetas( $etiquetas, $texto="" ){
   $cRet = '';
   if (preg_match_all ("/(?:<(.*)>)/Ui" , $etiquetas,$aTemp)){
      $aTemp = array_reverse( $aTemp[1] );
      foreach ( $aTemp as $i=>$v ){
         preg_match ("/([^ ]*)/i" , $v,$aWord);
         $cRet .= "</{$aWord[1]}>";
      }            
   }
   return $etiquetas.$texto.$cRet;   
}


/**
* para manejar XHTML hay tres funciones
* xhtml_set, xhtml_get y xhtml_atributo, implementadas a través de
* _mxhtml,
*/


function _mxhtml( $accion, $atributo='' ){
   static $xhtml = true;
   
   switch ( $accion ) {
      case 'set':
         $xhtml = $atributo;
      
      case 'get': 
         return $xhtml;
          
      case 'atributo':
         return ( $xhtml ? " $atributo='$atributo' " : " $atributo " );  
   } 
}

function xhtml_set ($valor = true){
  return _mxhtml ( 'set', $valor);
}

function xhtml_get (){
  return _mxhtml ( 'get');
}


function xhtml_atributo ( $atributo, $valor=''){
  if ( $valor !== '') {
     _mxhtml ( 'set', $valor);
  }
  return _mxhtml ( 'atributo', $atributo);
}


function mTabla ( $cabeceras, $datos, $adicionales="" ){
  $output = sprintf ( "<table %s %s>\n<thead><tr>", 
               ( isset($adicionales["id"])    ? " id='{$adicionales[id]}'" : "" ),
               ( isset($adicionales["class"]) ? " class='{" . $adicionales['class'] . "'"  : "" ) );
 
 // dibujar cabeceras 
 foreach ( $cabeceras as $i=>$th ){
    if ( is_array ($th) ){
       $temp = "";
       foreach ( $th as $i => $v ) {
         if ( $i != "th" ){
             $temp .= " $i=\"$v\"";
         }
       }       
       $output .= "<th$temp >{$th[th]}</th>";
       $claseTD[] =   ( $th["class"] ? " class='". $th["class"]. "'" : "");       
    } else {   
       $output .= "<th>$th</th>";
       $claseTD[] =  "";
    }    
 } 
 $output .= "</tr></thead>\n<tbody>\n";               
               
 
 // dibujar los datos              
 $par   = true;
 foreach ($datos as $i=>$fila ){
    $output .= sprintf ( "<tr %s>", ( $par ? " class='par' " : "" ) ) ;
    foreach ($fila as $j=>$columna ){
       $output .= "<td{$claseTD[j]}>$columna</td>";       
    }
    $output .= "</tr>\n";
    $par = !$par;function por_defecto(){
	foreach ( func_get_args() as $arg ){
  		if ($arg) {
  			return $arg;
  		}
	}
	return false;
}

 }
 
 
 $output .= "</tbody></table>\n";
 return $output;
} 




/*
 * test de unidad
 */


/* 
$fecha= "10/12-5";
echo fecha_formato( $fecha, "es"),"<br>";
echo fecha_formato( $fecha, "eu"),"<br>";
echo fecha_formato( $fecha, "eu","c"),"<br>";


echo mTabla ( array("Nombre","Apellidos", array ( "th"=>"fecha", "class"=>"flecha") ),
              array (
              array ("roger", "Martin", "15-68"),
              array ("fermin", "Lopez", "68")),
              array ( "id"=>"Personal"));
*/

/* cerrar_etiquetas  
echo cerrar_etiquetas ( '<li class=\'algo\'><a>','');
echo '\n';
echo cerrar_etiquetas ( '<li class=\'algo\'><a>','texto'); */ 

/*
echo mControl ("texto","Nombre","nombre","nada","dl");
echo mControl ("cadena","Nombre","nombre","nada","dl"); 

mprint (mQueryStringAdd ());
mprint (mQueryStringAdd (array("hola"=>"1&1"))); 
mprint (mQueryStringDel (array("hola"),false)); */



