<?php

/*
 * Experimento: hacer una librería datadriven capaz de manejar varias tablas.
 */ 
  

/* 
 * función interna para codificar valores con comillas o no
 */

function _bd_codifica ( $valor, $tipo="entero"){
   switch ($tipo) {
      case "cadena" :
      case "fecha"  :
      case "date"   :
      case "varchar": return "'$valor'";
      default       : return $valor;
   }   
}


/* 
 * función interna para la lectura física de un registro
 */

function _bd_query_registro($SQL){
	$rs   = mysql_query($SQL);
	$fila = mysql_fetch_array($rs, MYSQL_ASSOC);
	mysql_free_result ($rs);
   return $fila;
}



/* 
 * función interna para lectura lógica de un registro un registro
 */
function _bd_leer_registro($bd, $tabla, $id){
   $join ="";
   if ( isset( $bd[$tabla]["codigos"] )){      
      foreach ( $bd[$tabla]["codigos"] as $campo =>$donde) {
         $join = " LEFT JOIN $donde ON $campo =" . $bd[$donde]["id"]; 
      }        
   }
   $SQL = "SELECT * FROM $tabla$join WHERE " . $bd[$tabla]["id"] . "=" . _bd_codifica($id, $bd[$tabla]["tipo"]);   
   return _bd_query_registro ( $SQL);   
} 


/* 
 * función interna para la lectura física de registros. Devuelve un array.
 */

function _bd_query_registros($SQL){
	$rs   = mysql_query($SQL);
	while ( $fila = mysql_fetch_array($rs, MYSQL_ASSOC) ){
	   $filas[] = $fila;	   
	}
	mysql_free_result ($rs);
   return $filas;
}


/* 
 * función interna para la lectura lógica de un registros. Devuelve un array.
 */

function _bd_leer_registros($bd, $tabla, $vid){
   $join ="";
   if ( isset( $bd[$tabla]["codigos"] )){      
      foreach ( $bd[$tabla]["codigos"] as $campo =>$donde) {
         $join = " LEFT JOIN $donde ON $campo =" . $bd[$donde]["id"]; 
      }        
   }
   $SQL = "SELECT * FROM $tabla$join WHERE " . $bd[$tabla]["padreID"] . "=$vid";      
   return _bd_query_registros ( $SQL);   
} 


/* 
 * function interna para calcular los hijos de un registro 
 */
function bd_hijos_de( $bd, $padre ){
   $aRet=array();
   foreach ( $bd as $i=>$tabla ) {
      if ( $tabla["padre"] == $padre ) {
         $aRet[] = $i;
      }
   }
   return $aRet;
}


function bd_leer_registro ( $bd, $tabla, $id ) { 
   if ( !isset($bd[$tabla]) ){
      return false;
   }      
   // leer el registro principal
   $ret[$tabla] = _bd_leer_registro($bd, $tabla, $id);
   // leer claves secundarias
   $vid= _bd_codifica( $id, $bd[$tabla]["tipo"] );
   foreach ( bd_hijos_de ($bd, $tabla) as $hijo ){
      $ret[$hijo] = _bd_leer_registros ( $bd, $hijo, $vid );
   }   
   
   return $ret;
}


/*******************************************************************
 *
 * TEST de UNIDAD
 * Tiene que existir la bd y la comprobación es manual.
 * 
 **/  

mysql_connect("localhost", "test", "test");
mysql_select_db("test");
mysql_query ("SET CHARACTER SET utf8"); 
mysql_query ("SET time_zone = '+01:00'");

include "funciones.php";

/*
$bd["itag"] = array (
   "id"   =>"itag_es",
   "tipo" =>"cadena");
$temp = bd_leer_registro($bd,"itag","hola");
echo $temp["itag"]["itag_eu"],"\n"; */  


$bd["item"] = array (
   "id"   =>"item_id",
   "codigos" => array ( "item_tipo"=>"tipos"),
   "tipo" =>"entero");

$bd["tipos"] = array (
   "id"   =>"tipo_id" );      

$bd["comentarios"] = array (
   "id"     =>"comentario_id",
   "codigos"=>array ( "comentario_usuario_id"=>"usuarios"),
   "padreID"=>"comentario_item_id",
   "padre"  =>"item");
          
$bd["usuarios"] = array (
   "id"     =>"usuario_id" );   
             
          
$temp = bd_leer_registro($bd,"item",1);  
print_r ( $temp);

