<?php

/*
 * Experimento: hacer una librería datadriven capaz de manejar varias tablas.
 * @version .01 (experimental)
 * @author Roger
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
 * function interna para calcular los hijos de un registro 
 */


function bd_leer_registro ( $bd, $tabla, $id ) { 
   if ( !isset($bd[$tabla]) ){
      return false;
   }      
   // leer el registro principal
   $ret = _bd_leer_registro(&$bd, $tabla, $id);
   
   // leer claves secundarias / hijos / nietos /...
   $vid= _bd_codifica( $id, $bd[$tabla]["tipo"] );
   if ( is_array($bd[$tabla]["hijos"]) ){      
      
   	foreach ( $bd[$tabla]["hijos"] as $campo=>$hijo ){
   	   // obtenemos la lista de los hijos
   	   $lista = mysql_query_lista("SELECT " . $bd[$hijo]["id"] . " FROM $hijo WHERE $campo=$vid");
   	   if ( is_array($lista) ){
         	foreach ( $lista as $hijoID ) {
         		$ret[$hijo][] = bd_leer_registro(&$bd,$hijo,$hijoID);
         	}   	   
         }
   	   
      }   
   }

   return $ret;
}


function _bd_campo_cadena ( $valor, $accion ){
	switch ( $accion ){
		case "consulta": return $valor;
		case "sql"     : return "'$valor'";
		case "edicion_campo" : return "<input type='text'>"
		case "edicion_label" : return "cadena";
	}
}

function _bd_campo_entero ( $valor, $accion ){
	switch ( $accion ){
		case "consulta": return $valor;
		case "sql"     : return $valor;
		case "edicion_campo" : return "<input type='text'>"
		case "edicion_label" : return "cadena";
	}
}



function bd_campo ( $valor, $tipo, $accion ) {
	switch ( $tipo ){
		"cadena": return _bd_campo_cadena($valor,$accion)
		"entero": return _bd_campo_numero($valor,$accion)
	}

}


function bd_registro_plantilla ($reg, $plantilla ){




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
   "hijos"   => array ( "comentario_item_id" =>"comentarios" ),
   "tipo" =>"entero");

$bd["tipos"] = array (
   "id"   =>"tipo_id" );      

$bd["comentarios"] = array (
   "id"     =>"comentario_id",
   "codigos"=>array ( "comentario_usuario_id"=>"usuarios"),
   "hijos"  =>array ( "voto_comentario_id" => "votos" ) );
          
$bd["votos"] = array (
	"id" =>"voto_id" );
	       
$bd["usuarios"] = array (
   "id"     =>"usuario_id" );   
             
          
$temp = bd_leer_registro($bd,"item",1);  
print_r ( $temp);

 


