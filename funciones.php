<?php

/**
  *
  * Librería general de funciones.
  * @author Roger
  * @version 2011-05-24
  * @licence GPL

  * 2011/05/24 - mInputListaClaves. Usar mInputLista
  *            m mControl
  * 2011/05/19 + mInputLista (2º parámetro es name/atributos
  *         + mInputListaSQL
  *         r mInputSiNo01 a mInputSiNo
  *         c mysql_query_lista acepta un tercer parametro: valores iniciales del array.
  *         - mysql_mlistaSQL. Usar mysql_query_lista
  *         + mExplode
  *         r lista_archicos listaArchivos
  * 2011/05/18 r renombrada plantilla_opcion a plantillaSegunCantidad.
  *         m plantillaSegunCantidad ahora acepta hasta 4 parametros
  *           (cantidad, caso_0, caso_1, caso_2_o_mas), o
  *           cantidad, "caso_0|caso_1|caso_2_o_mas".
  *           string con |.
  *         - suprimida lista. Usar implode
  *         - completaURL suprimida. Usar urladdHTTP
  *         - url_add_httpsuprimida. Usar  urladdHTTP
  *         c urlAddHTTP. Con un parámetro vacio devuelve vacio.
  * 2011/05/17 + mTablaSQL.
  *         - mFecha suprimido
  *         c mDate. Sin args devuelve hoy en dd/mm/aaaa. Con un solo argumento
  *           se puede definir el formato en,es,eu o fr.
  *           con dos, el primero es la fecha y el segundo el formato.
  * 2011/05/13 c funcion corta.
  * 2011/05/13 c mImplode
  * 2011/05/03 Añadido si_es_key
  * 2011/03/01 Añadido js_confirm (again)
  * 2010/09/29 Repaso de mqueryString.
  *         + mquerystringToArray
  *         + mquerystringFromArray
  *         c mquerystringAdd. Solo admite un argumento y devuelve string
  *         c mquerystringDel. Solo admite un argumento y devuelve string
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
  *         c fecha_php_mysql. detecta formato: MM/DD/AAAA y lo pasa a AAAA/MM/DD
  * 2008-03-13 + limpiaRequest
  * 2008-03-13 r listaSQL a mysql_mlistaSQL
  * 2008-03-13 r listaClavesSQL a mysql_mlistaClavesSQL
  * 2008-03-12 + mTabla
  * 2008-02-26 c mesDe, diaDe
  *         + mControl
  * 2008-02-20 + lista_archivos
  * 2008-01-28 + cerrar_etiquetas.
  * 2007-09-27 + mysql_plantilla
  * 2007-04-27 + in_lista
  * 2007-04-17 + mysql_query_lista añadido parámetro hash.
  * 2007-01-12 + m_split ( cadena, longitudlinea, cSaltoDeLinea, cPrefijo )
  * 2006-09-12 + ampliado extrae_ExtensionNombre() a path.
  * 2006-06-30 + mysql_CampoClave ()
  *         + extrae_ExtensionNombre()
  * 2006-06-23 c limpiaSQLinjectionTexto.
  * 2006-06-21 - booleantoSiNoConClase
  *         + añadido  mysql_query_registro
  * 2006-06-15 + mysql_query_lista
  *         + limpiaSQLinjectionTexto,Numero,Busca,ID
  * 2006-02-16 + fecha_mysql_php
  *         + fecha_php_mysql
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

function si_es_key ( &$array, $key, $default=""){
    return ( isset($array[$key]) ? $array[$key] : $default );
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
         $temp  = $regs[2];
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
        return (    $afecha[3] . '-' . $afecha[2] . '-' . $afecha[1] . ' 00:00:00' );
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
             'Abril', 'Mayo',   'Junio',
             'Julio', 'Agosto', 'Septiembre',
             'Octubre', 'Noviembre', 'Diciembre' ),
    'eu' => array (
            'Urtarila', 'Otsaila', 'Martxoa',
             'Aprila', 'Maiatza',   'Ekaina',
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
    case "h": return floor($dif/3600);  // horas
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
function mDate ($fecha=false, $idioma='es'){
   $formatos= array(
       "es"=>"d/m/Y",
       "fr"=>"d/m/Y",
       "eu"=>"Y/m/d",
       "en"=>"m/d/Y" );
   $argN = func_num_args();
   if ( $argN==0 ){
       return date("d/m/Y");
   }

   $fecha= ( func_get_arg(0) ? func_get_arg(0): "es");
   if ( $argN ==1 ){
      return ( isset($formatos[$fecha]) ?
                    date($formatos[$fecha]):
                    date($formatos["es"], strtotime($fecha)));
   }
   $idioma = func_get_arg(0);
   return ( !$fecha ? date($formato) : date($formato, strtotime($fecha)));
}



function urlAddHttp($url){
if ( !$url ) return $url;
return ( preg_match ("#^(https?://)#",  $url ) ? $url : "http://$url" );
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
*   F U N C I O N E S     DE      A V I S O
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
*   F U N C I O N E S     DE      C A D E N A
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


function corta ( $cadena, $longitud=80, $annadir =".."){
   $limpia = strip_tags($cadena);
   return ( strlen($limpia)>$longitud ?
                substr( $limpia,0,$longitud). $annadir :
                $limpia );
}


function plantillaSegunCantidad ( $cantidad, $caso_es_0, $caso_es_1="", $caso_2oMas="") {

 if ( strpos($caso_es_0,"|") !== false ){
     $capturas = explode("|",$caso_es_0);
     $caso_2oMas = count($capturas)==3 ? $capturas[2]:"";
     $caso_es_1  = count($capturas)>=2 ? $capturas[1]:"";
     $caso_es_0  = count($capturas)>=1 ? $capturas[0]:"";
 }

 $caso_2oMas = por_defecto ( $caso_2oMas, $caso_es_1, $caso_es_0);
 $caso_es_1  = por_defecto ( $caso_es_1, $caso_es_0);

 switch ( $cantidad ) {
    case 0 : return sprintf( $caso_es_0 , 0);
    case 1 : return sprintf( $caso_es_1 , 1);
    default: return sprintf( $caso_2oMas, $cantidad);
 }

}


function mImplode ($patronPrintf, $array, $separador = "") {
    $sep="";
    $ret="";
    foreach ($array as $k=>$valor) {
        $ret .= $sep . sprintf($patronPrintf, $k, $valor);
        $sep  = $separador;
    }

    return $ret;
}


function mExplode( $separadorPares, $separadorPar=false, $cadena=false) {
    if ( $cadena === false ){
        $cadena = $separadorPares;
        $separadorPares= "|";
        $separadorPar  = "=";
    }

    $ret      = array();
    $longitud = strlen($separadorPar);

    $pares = explode($separadorPares,$cadena);
    foreach ($pares as $par){
        $nPos = stripos($par, $separadorPar);
        if ( $nPos===false ) {
            $ret[] = $par;
        } elseif ( $nPos===0){
            $ret[] = substr($par,$longitud);
        } else {
            $ret[substr($par,0,$nPos)] = substr($par,$nPos+$longitud);
        }
    }
    return $ret;
}



/**
*
*     F U N C I O N E S   DE      FICHEROS
*
*/

/*
* Ejemplos de resultados.
* foo         => ext="",nombre="foo", tiene=false
* foo.       => ext="",nombre="foo", tiene=true
* foo.bar     => ext="bar",nombre="foo", tiene=true
* foo.bar.ex   => ext="ex",nombre="foo.bar", tiene=true
*/


function extrae_ExtensionNombre ( $cFileCompleto ){
    $lEsPathRelativo = true;

    $nAt = strrpos ( $cFileCompleto, "/");
    if ( $nAt === false ) {
      $cNombreFichero  = $cFileCompleto;
      $cPath           = "" ;
      $lHayPath     = false;
   } else {
      $cPath           = substr($cFileCompleto,0,$nAt+1);
      $cNombreFichero  = substr($cFileCompleto,$nAt+1);
      $lHayPath    = true;
      $lEsPathRelativo = ($nAt>0);
   }

   $nAt = strrpos ( $cNombreFichero, ".");
   if ( $nAt === false ) {
      $cNombre = $cNombreFichero;
      $lHayExtension = false;
      $cExt = "";
   } else {
    $lHayExtension = true;
      $cNombre = substr($cNombreFichero,0,$nAt);
      $cExt = substr($cNombreFichero,$nAt+1);
   }
   return array ( "path" => $cPath, "nombre" =>$cNombre , "ext" =>$cExt,
                  "hayPath"=>$lHayPath, "hayExt"=>$lHayExtension, "esPathRelativo"=>$lEsPathRelativo );
}


/*
 * devuelve un array con todos los ficheros de una determinada extensión
 * de un directorio. Acepta una extensión o varias: "jpg", "jpeg|jpg".
 * Parecida a glob.
 */
function listaArchivos ( $path, $extension ){
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
*     F U N C I O N E S   DE      L I S T A Y   SQL
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
  $cRet   = "" ;
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
  $par     = true;
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
     $lerroa [ "par"]         =  ( $par ? "par" : "impar" ) ;
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
    $rsCampos = mysql_query("SHOW COLUMNS FROM $tabla");
    $clave    = "";
    while ( $campo = mysql_fetch_array($rsCampos) ){
        if ( $campo["Key"] == "PRI" ) {
            $clave = $campo[0];
            break;
        }
    }
    mysql_free_result ( $rsCampos );
    return $clave;
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
 * funcion para determinar si un consulta devuelve datos
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
 * función para obtener el primer registro de un sentencia SQL.
 * @param  Sentencia SQL-SELECT
 * @return El primer registro o FALSE
 */
function mysql_query_registro($cSql){
    $rsEmaitza = mysql_query($cSql);
    $lerroa = mysql_fetch_array($rsEmaitza);
    mysql_free_result ( $rsEmaitza);
    return $lerroa;
}


/**
 * función para obtener todos los registros de un sentencia SQL.
 * @param  Sentencia SQL-SELECT
 * @return Todos los registros o FALSE
 */
function mysql_query_registros($cSql, $modo= MYSQL_BOTH){
    $rsEmaitza = mysql_query($cSql);
    $temp     = false;
    while  ( $temp[]= mysql_fetch_array($rsEmaitza,$modo) ){
    }
    mysql_free_result ( $rsEmaitza);
    return $temp;
}


/**
 * función para obtener una lista/array a partir de una sentencia SQL.
 * @param  Sentencia SQL-SELECT
 * @return array con lista.
 */
function mysql_query_lista($cSql, $hash = false, $ret= false ){
   $rsConsulta = mysql_query( $cSql);
   while ( $fila = mysql_fetch_array($rsConsulta)){
      if ( $hash ) {
         $ret[$fila[0]]= $fila[1];
      } else {
         $ret[]= $fila[0];
      }
   }
   mysql_free_result ( $rsConsulta);
   return $ret;
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
*
*   F U N C I O N E S     DE      I N P U T S
*
*/


/**
* devuelve un cadena HTML para un Select de una lista de valores
*/
function mInputLista( &$valores, &$atributos, $defecto=false ) {
   
   if ( !is_array($atributos) ){
      $ret="<select name='$atributos'>\n";
   } else {
      $ret="<select" . mImplode(" %s='%s'", $atributos) .">\n";
   }
   
   foreach( $valores as $key=>$value ){
      $ret.= sprintf("<option value='$key'%s>$value</option>\n",
                       ($defecto==$key?" selected": "") );
   }
   $ret.="</select>\n" ;
   return $ret;
}


function mInputSiNo ( $atributos, $defecto=false ){
   $valores= array("1"=>"Si","0"=>"No");
   $defecto= ( $defecto ? 1 : 0 );
   return mInputLista ( $valores, $atributos, $defecto);
}


/**
* Construye un input a partir de una sentencia Select SQL que devuelve clave, valor.
*/
function mInputSQL ( $cSQL, $atributos, $cValorInicial,  $opcionInicial=false){
   $valores = is_array($opcionInicial) ? $opcionInicial: array($opcionInicial) ;
   $valores = mysql_query_lista($cSQL, true, $valores);
   return mInputLista ( $TempList, $atributos, $cValorInicial );
}


/**
* Select de un entero booleano (0/1 pasa a NO/SI)
*/
function mInputCheckbox( $atributos, $defecto=0, $valor=1){
   return "<input type='checkbox' value='$valor'" . ( $defecto ? " checked='checked'" : "").
          (!is_array($atributos) ? " name='$atributos'" : mImplode(" %s='%s'", $atributos))  .">";
}


/**
* Devuelve un control según el tipo especificado)
*/

function mControl ( $dd, $opciones=false){
   if ( !$opciones) {
      $opciones = array();
   }

   $id   =  ( isset($dd['id']) ? $dd['id'] : $dd['campo'] );
   $valor= isset($opciones['valor']) ? $opciones['valor'] : false;
   
   $parametros = explode(" ", $dd["tipo"]);
   $tipo = $parametros[0];
   
   $atributos = array("id"=>$id, "name"=>$dd['campo']) ;
   
   if ( isset($dd['clase'])) { $atributos['class']= $dd['clase'];  }
   if ( isset($opciones['value']) && $tipo!="texto" ) { $atributos['value']= $opciones['value'];}
   
   if ( isset($dd['atributos'])  && is_array( $dd['atributos']) ){
      $atributos= $atributos + $dd["atributos"];
   }
   $cAtributos= mImplode(" %s='%s'", $atributos );
   
   $resto = substr( $dd["tipo"], strpos( $dd["tipo"]," ")+1);
   switch ( $tipo ){
      case "numero":
         if ( isset($parametros[1]) ){
            $min = si_es_key($parametros,1,0);
            $max = si_es_key($parametros,2,10);
            $campo = "<select $cAtributos>\n";
            for ( $opcion=$min; $opcion<$max;$opcion++){
               $selected = ( $valor== $opcion ? ' selected' : '' );
               $campo .= "<option value='$opcion'$selected>$opcion</option>\n";
            }
            $campo .= "</select>\n";
         } else {
            $campo = "<input type='text' $cAtributos>";
         }
         break;
      
      case "url":
         $max = si_es_key($parametros,1,30);
         $size= si_es_key($parametros,2,$max);
         $campo = "<input type='text' size='$size' maxlength='$max' $cAtributos >";
         break;
      
      case "cadena":
         $max = si_es_key($parametros,1,30);
         $size= si_es_key($parametros,2,$max);
         $campo = "<input type='text' size='$size' maxlength='$max' $cAtributos >";
         break;
      
      case "texto":
         $cols = si_es_key($parametros,1,60);
         $filas= si_es_key($parametros,2,8);
         $campo = "<textarea cols='$cols' rows='$filas' $cAtributos>$valor</textarea>";
         break;
      
      case "lista":
         $lista = mExplode("|",":",$resto);
         if ( count($lista) < 5) {
            $campo="";
            unset($atributos['id']);
            $cAtributos= mImplode(" %s='%s'", $atributos );
            foreach ( $lista as $opcion=>$opcionLabel){
               $checked = $valor== $opcion ? ' checked' : '';
               $campo .= "<input value='$opcion' type='radio'$checked $cAtributos><span class='radio-label'> $opcionLabel </span>";
            }
         } else { 
            $campo = "<select $cAtributos>\n";
            foreach ( $lista as $opcion=>$opcionLabel){
               $selected =  ($valor== $opcion ? ' selected' : '' );
               $campo .= "<option value='$opcion'$selected>$opcionLabel</option>\n";
            }
         $campo .= "</select>\n";
         }
         break;
         
      case "checkbox":
      case "booleano":
         $value = si_es_key($aOpciones,"value",1);
         $campo = "<input type='checkbox' $atributos" . ($valor? " selected ": "" ) . ">";
         break;
   }

   if ( isset($dd["adicional"] )) {
      $campo .= " <span class='control-adicional'>{$dd['adicional']}</span>";
   }

   // construir la label
   $etiqueta= isset($dd["cabecera"]) ? $dd["cabecera"]: $dd["campo"] ;
   if ( isset($dd["obligatorio"] )) {
      $label = "<label for='$id' class='control-obligatorio'>$etiqueta<span class='control-obligatorio'>(*)</span></label>";
   } else {
      $label = "<label for='$id'>$etiqueta</label>";
   }

   // maquetar label y control según estilo
   $estilos = array (
      "dl"  => "<dt>%s</dt>\n<dd>%s</dd>",
      "p"    => '<p>%s %s</p>',
      "p-br"  => '<p>%s <br> %s</p>',
      "table" => '<tr><th>%s</th><td>%s</td></tr>');
    if ( !isset($opciones["estilo"]) || !isset($estilos[$opciones['estilo']])) {
        $estilo = $estilos['dl'];
    } else {
        $estilo = $estilos[$opciones['estilo']];
    }
   return "\n". sprintf( $estilo, $label,$campo ) ;

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


/*
 * TABLAS
 */

function mTablaSQL( $SQL, $adicionales="" ){
// dibujar los datos
$rs= mysql_query($SQL);
$par   = true;
$cabecera = false;
$output = "";
while ( $fila = mysql_fetch_assoc($rs)){
    if ( !$cabecera ){
        $output = sprintf ( "<table%s%s>\n<thead>\n<tr>",
               ( isset($adicionales["id"])  ? " id='{$adicionales[id]}'" : "" ),
               ( isset($adicionales["class"]) ? " class='{" . $adicionales['class'] . "'"  : "" ) );
        foreach ( $fila as $th=>$v){
            $output .= "<th>$th</th>";
        }
        $output .="</tr>\n<tbody>\n";
        $cabecera = true;
    }
    $output .= sprintf ( "<tr %s>", ( $par ? " class='par' " : "" ) ) ;
    foreach ($fila as $i=>$td ){
       $output .= "<td>$td</td>";
    }
    $output .= "</tr>\n";
    $par = !$par;
}

if ( $cabecera){
    $output .= "</tbody></table>\n";
} else {
    $output ="";
}
return $output;
}


function mTabla ( $cabeceras, $datos, $adicionales="" ){
  $output = sprintf ( "<table %s %s>\n<thead><tr>",
               ( isset($adicionales["id"])  ? " id='{$adicionales[id]}'" : "" ),
               ( isset($adicionales["class"]) ? " class='{" . $adicionales['class'] . "'"  : "" ) );

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
       $output .= "<td". $claseTD[$j] .">$columna</td>";
    }
    $output .= "</tr>\n";
    $par = !$par;
}

 $output .= "</tbody></table>\n";
 return $output;
}
