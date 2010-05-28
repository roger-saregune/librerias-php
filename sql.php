<?php

/**
 * funciones SQL para construir clausulas.
 *
 * @version: 2010/05/28
*    2010/05/28 sql_codifica ya no se corrigen las fechas. Deben estar en YYYY/mm/dd
 *   2010/05/13 sql_add con cadena por defecto
 *   2008/02/13 ampliado sql_junta
 *   2006/06/30 Añadido sql_esvacia
 *   2005/03/10 Añadidas funciones para crear sentencias SQL.
 *           sql_crear, sql_add, slq_codifica, sql_sql y sql_where
 *
 */

/*
* Construye una clausula AND
*/

function sql_junta( $c1, $c2, $cUnion){
	if ($c1=="")
   	return $c2;
	elseif ($c2=="")
   	return $c1 ;
	else
   	return  "$c1 $cUnion $c2" ;}


function sql_or( $c1, $c2){
  return sql_junta ( $c1, $c2, "or");
}


function sql_and( $c1, $c2){
  return sql_junta ( $c1, $c2, "and");
}

/**
* Introduce un paretensis a una clausula, siempre que no este vacia
*/
function sql_parentesis($c1){
if ($c1=="")
  $sql_parentesis= "";
else
  $sql_parentesis= "(" . $c1  . ")";
return  $sql_parentesis;
}


/**
*
* FUNCIONES PARA CREAR SENTENCIAS INSERT Y UPDATE
*
*/




function sql_codifica ( $cValor, $cTipo ){
// calcular el nuevo tipo

switch ( strtolower ( $cTipo )){
   case "listafuncioncadena":
	case "listavalores":		
	case "cadena":
	case "texto" :
      return "'$cValor'";
      break;
	
	case "fecha" :
	case "fechahoy":
		if ($cValor!=''){
		    return "'$cValor'";            
		}
		return ($cTipo=="fecha" ? "NULL" : date("'Y/m/d 00:00:00'") );
		break;
   
	case "checkbox" :
		return ($cValor=="1"?  "1": "0");
		break;
   
	case "numero" :
	case "entero" :
	default:
		return ($cValor=="" ? "0" : $cValor);
	break;
}
return "";
}


function sql_crear( $cTipo, $cTabla ,  $cWhere="" ){  
   $cTipo = strtoUpper ( "$cTipo" );
   if ($cTipo=="INSERT") {
   	$aReg= array ( "tipo" =>"INSERT",  "tabla"=>$cTabla, "campos" =>"", "values"=>"" );
   } else {
   	$aReg= array ( "tipo" =>"UPDATE",  "tabla"=>$cTabla, "set" =>"", "where"=>"$cWhere" );
   }
   return $aReg;
}


function sql_add ( &$aReg, $cCampo, $cValor, $cTipo="cadena"){
   $cTemp= sql_codifica ( $cValor, $cTipo );
   if ($aReg["tipo"]=="INSERT" ) {
      $aReg["campos"].= ($aReg["campos"]==""?"":",").  $cCampo;
      $aReg["values"].= ($aReg["values"]==""?"":",").  $cTemp;
   } else {
      $aReg["set"].= ($aReg["set"]==""? "":" ,"). "$cCampo=$cTemp" ;
   }
}


function sql_add_request( &$aReg, $cCampo, $cTipo){
sql_add ( $aReg, $cCampo, $_REQUEST[$cCampo], $cTipo );
}


function sql_where ( &$aReg, $cWhere ){
   $aReg["where"]= $cWhere;
}


function sql_esvacia( $aReg ){
   if ( !is_array($aReg) or !isset($aReg["tipo"]) ) {
      return true;
   }
   return ( $aReg["tipo"] ==  "INSERT" ? ($aReg["campos"]==""): ($aReg["set"]==""));
}


function sql_sql( $aReg ){
   if ($aReg["tipo"]=="INSERT" ) {
      return "INSERT INTO ". $aReg["tabla"] . " ( ". $aReg["campos"] . ") VALUES (" . $aReg["values"] . ")" ;
   } else {
      return "UPDATE ". $aReg["tabla"] . " SET ". $aReg["set"] . ($aReg["where"]==""?"" : " WHERE " . $aReg["where"]) ;
   }
}


function sql_order ( $campos, $order ) {
   $aCampos = explode ("," ,$campos);
   $cTemp = "";
   foreach ( $aCampos as $id =>$campo) {
      if ($campo!="")
         $cTemp .= ( $cTemp=="" ? "": ",") . "$campo $order";
   }
   return ($cTemp=="" ? "" :" order by $cTemp");
}
