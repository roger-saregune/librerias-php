<?php 
/**
 * Modulo Traducción
 * 
 * @version 0.01
 * 2010-05-07 quitado ereg para sustituir por preg_match.
 * 
 * nueva versión
 * 
 */





function tData($cFecha, $cSeparador= "-"){
   global $hizkuntza ;
   if ( !preg_match( "#([0-9]{2,4})[-/]([0-9]{1,2})[-/]([0-9]{1,2})#", $cFecha, $aFecha) ) {
      return "";      
   }
      
   // devolver según país.   
   if ( $hizkuntza == "es") {     
      return $aFecha[3] . $cSeparador . $aFecha[2] . $cSeparador . $aFecha[1];
   } else {
      return  $aFecha[1] . $cSeparador . $aFecha[2] . $cSeparador . $aFecha[3];  
   }   
   
}


function tTime($cFecha){
  global $hizkuntza ;
  if ( ! preg_match( "#([0-9]{2,4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})#", $cFecha, $aFecha)){
     return "";
  }
  return $aFecha[4] . ":" . $aFecha[5] . ":" . $aFecha[6];
}


function tDataUnix( $unixTime) {
   global $hizkuntza;
   return date( $unixTime, ($hizkuntza=="eu" ? "Y/m/d" : "d/m/Y"));    
}


function t ( $string, $args=0 ){
/**
 * @TODO Implementar el sistema completo de traducción al euskera. De momento, es una simulación para no tener que hacer cambios luego.
 */

   global $hizkuntza;   
   

   $traduccion= $string;
   
   if ( $hizkuntza == "eu" && ( $temp = mysql_mlookup ("SELECT eu FROM locale WHERE es='$string'"))){         
	   $traduccion= $temp  ;
   } 

   if ( !$args) {
	   return $traduccion;
   } else {
	   return strtr( $traduccion, $args );
   }

}


/**
 * Funciones de base de datos
 */

function tSqlCampo( $campo, $alias=""){
  return tifsql( $campo, $alias="");
}

function tSqlCampos( ){
  $campos= func_get_args() ;
  foreach ($campos as $campo) {
	$lista .=  ( $lista == ""  ? "": "," ) . tifsql( $campo, $alias="");
  }
  return $lista;
}

function tifsql( $campo, $alias=""){
   global $hizkuntza;
   if ( $hizkuntza == "es" ) {
      $ext0 = "_es";
      $ext1 = "_eu";
   } else {
      $ext0 = "_eu";
      $ext1 = "_es";
   }
   if ( $alias =="#" ) {
     $cAlias= "";
   } else {
     $cAlias= " as " . ( $alias == "" ? $campo : $alias );    
   }   
   
   return "if($campo$ext0='',$campo$ext1,$campo$ext0)$cAlias";
}



function tCampo ( $datos, $campo, $idioma="" ) {
   /**
    * obtener un campo preferentemente en el idioma del usuario.
    */
   
   if ( $idioma==""){
     global $hizkuntza;
     $idioma = $hizkuntza;
   }   

   if ( !is_array($datos) ){
      $rsEmaitza = mysql_query($datos);
      $datos     = mysql_fetch_array($rsEmaitza);
      mysql_free_result($rsEmaitza);
      if (!$datos) {
         return "";
      }
   }

   if ( $datos[$campo."_". $idioma] == "" ){
	   return $datos[$campo. ($idioma == "es" ? "_eu" : "_es")];
   } else {
   	return $datos[$campo."_". $idioma];
   }

}


function mysql_mlookupGetEuEs ( $cSQL, $hizkuntza = "eu") {
$aDatos = mysql_query_registro ( $cSQL );
if ( $hizkuntza == "eu" )
   return ( $aDatos[0]!= "" ? $aDatos[0] : $aDatos[1] );
else
   return ( $aDatos[1]!= "" ? $aDatos[1] : $aDatos[0] );
}
