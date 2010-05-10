<?php 
/**
 * Modulo Traducción
 * 
 * @version 0.02
 * 2010-05-10 + tIdiomaDefecto, tIdiomaBase, tIdiomaTabla
 * 2010-05-07 quitado ereg para sustituir por preg_match.
 * 
 * nueva versión
 * @TODO. Filosofia!!
 *        hacerlo solo ¿eu/es? o más general basado en... 
 *        se desarrolla en un idioma base
 *        se consulta en otro idioma,
 *        las traducciones estan en.... 
 */



global $TIDIOMA_DEFECTO; // el idioma por defecto al que se traduce
global $TIDIOMA_BASE;    // el idioma en que esta escrito el programa.
global $TIDIOMA_TABLA; // la tabla para las traducciones
$TIDIOMA_TABLA = "locale";


function tIdiomaBase($idiomaBase){
   global $TIDIOMA_BASE;

   $TIDIOMA_BASE   = $idiomaBase;
}


function tIdiomaPorDefecto($idioma ){
   global $TIDIOMA_DEFECTO; 
         
   $TIDIOMA_DEFECTO= $idioma;
}


function tIdiomaTabla($tb ){
   global $TIDIOMA_TABLA; 
         
   $TIDIOMA_TABLA= $tb;
}



function tData($cFecha, $cSeparador= "-"){
   global $TIDIOMA_DEFECTO;
   if ( !preg_match( "#([0-9]{2,4})[-/]([0-9]{1,2})[-/]([0-9]{1,2})#", $cFecha, $aFecha) ) {
      return "";      
   }
      
   // devolver según país.   
   if ( $TIDIOMA_DEFECTO == "es") {     
      return $aFecha[3] . $cSeparador . $aFecha[2] . $cSeparador . $aFecha[1];
   } else {
      return  $aFecha[1] . $cSeparador . $aFecha[2] . $cSeparador . $aFecha[3];  
   }   
   
}


function tTime($cFecha){
  if ( ! preg_match( "#([0-9]{2,4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})#", $cFecha, $aFecha)){
     return "";
  }
  return $aFecha[4] . ":" . $aFecha[5] . ":" . $aFecha[6];
}


function tDataUnix( $unixTime) {
   global $TIDIOMA_DEFECTO;
   return date( $unixTime, ($TIDIOMA_DEFECTO=="eu" ? "Y/m/d" : "d/m/Y"));    
}



function t ( $string, $args=0 ){

   /**
   * @TODO Implementar el sistema completo de traducción al euskera. De momento, es una simulación para no tener que hacer cambios luego.
   */

   global $TIDIOMA_DEFECTO, $TIDIOMA_BASE, $TIDIOMA_TABLA;

   $traduccion= $string;
   
   if ( $TIDIOMA_DEFECTO != $TIDIOMA_BASE  &&     
            $temp = mysql_mlookup ("SELECT $TIDIOMA_DEFECTO FROM $TIDIOMA_TABLA WHERE $TIDIOMA_BASE='$string'") ){            
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
   global $TIDIOMA_DEFECTO;
   if ( $TIDIOMA_DEFECTO == "es" ) {
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
     global $TIDIOMA_DEFECTO;
     $idioma = $TIDIOMA_DEFECTO;
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


function mysql_mlookupGetEuEs ( $cSQL, $idioma = "") {
   if ( $idioma==""){
        global $TIDIOMA_DEFECTO;
        $idioma = $TIDIOMA_DEFECTO;
   }   
   
   $aDatos = mysql_query_registro ( $cSQL );
   if ( $idioma == "eu" ) {
      return ( $aDatos[0]!= "" ? $aDatos[0] : $aDatos[1] );
   }
   else {
      return ( $aDatos[1]!= "" ? $aDatos[1] : $aDatos[0] );
   }
}
