<?php

/**
 * Libreria para construir clausulas SQL
 *
 * 2010/09/28 Documentación PHPDOC.
 *            Mejoras en sql_order, sql_add, sql_codifica con NULL.
 *            Añadido: sql_nueva.
 * 2010/09/29 sql_codifica devuelve casi siempre '' salvo enteros y numeros.
 * 2010/05/28 sql_codifica ya no se corrigen las fechas. Deben estar en YYYY/mm/dd.
 * 2010/05/13 sql_add con		 cadena por defecto.
 * 2008/02/13 ampliado sql_junta.
 * 2006/06/30 Añadido sql_esvacia.
 * 2005/03/10 Añadidas funciones para crear sentencias SQL.
 *           sql_crear, sql_add, slq_codifica, sql_sql y sql_wher.
 *
 * @version 2011/09/28
 * @package libreria-basica
 * @author Roger Martin <rg1024@gmail.com>
 */


/**
 * Construye una clausula AND,OR
 * @access private
 */

function sql_junta( $c1, $c2, $cUnion){
	if ($c1=="") {
		return $c2;
	} elseif ($c2=="") {
		return $c1 ;
	} 
	return  "$c1 $cUnion $c2" ;
}


/**
 * Construye una cláusula AND
 * @param string $c1 Primera parte
 * @param string $c1 Segunda  parte
 * @return string la cláusula
 */
function sql_or( $c1, $c2){
	return sql_junta ( $c1, $c2, "or");
}


/**
 * Construye una cláusula OR
 * @param string $c1 Primera parte
 * @param string $c1 Segunda  parte
 * @return string la cláusula
 */

function sql_and( $c1, $c2){
	return sql_junta ( $c1, $c2, "and");
}


/**
 * Introduce un paréntesis a una cláusula, siempre que no este vacia
 * @param string $clausula
 * @return string 
 */

function sql_parentesis($clausula){
	return  ( $clausula ? "($clausula)" : "" );
}


/*
 *
 * FUNCIONES PARA CREAR SENTENCIAS INSERT Y UPDATE
 *
 **********************************************************************************/


/**
 * Codifica un valor para ser insertado en una sentencia SQL.
 *
 * En las cadenas y fechas, toda ' no escapada se escapa.
 * Los valores NULL se devuelven como literal "NULL"
 * Los valores check se dejan a 0 o 1 (nunca NULL)
 * 
 *
 * @param string $cValor valor a codificar
 * @param string $cTipo fecha,checkbox,numero, entero, (cadena por defecto)
 * @return $string valor codificado
 */

function sql_codifica ( $valor, $cTipo, $lNULL= false ){

	$NULL = $lNULL ? "NULL" : 0 ;	
	$patron = '/(?<!\\\)\'/' ; // captura cualquier ' no escapada.
	
	switch ( strtolower ( $cTipo )){	
		case "fecha" :
		case "fechahoy":
			if ($valor!=''){
				return "'". preg_replace($patron,'\\\'', $valor). "'";            
			}
			return ($cTipo=="fecha" ? "NULL" : date("'Y/m/d 00:00:00'") );

		case "checkbox" :
			if ( is_null($valor)) {
				return $NULL;
			}
			return ($valor=="1"?  "1": "0");

		case "numero" :
			if ( is_null($valor)) {
				return $NULL;
			}
			return ($valor ? (string) $valor : 0 );
		
		case "entero" :
			if ( is_null($valor)) {
				return $NULL;
			}
			return ($valor ? (int) $valor : 0 );
	
		default:
			if ( is_null($valor)) {
				return  ( $lNULL ? "NULL" : "''") ;
			}
			return "'". preg_replace($patron,'\\\'', $valor) . "'" ;

	}
	return "";
}


/**
 * Crea un array que representa un sentencia SQL insert or UPDATE
 *
 * @see sql_add, sql_where, sql_sql
 *
 * @param string $cTipo INSERT | UPDATE (*)
 * @param string $cTabla tabla
 * @param string $cWhere Clausula WHERE para la sentencia UPDATE
 * @return $array La estructura interna
 */

function sql_crear( $cTipo, $cTabla ,  $cWhere="" ){  
	$cTipo = strtoUpper ( "$cTipo" );
	if ($cTipo=="INSERT") {
		$aReg= array ( "tipo" =>"INSERT",  "tabla"=>$cTabla, "campos" =>"", "values"=>"" );
	} else {
		$aReg= array ( "tipo" =>"UPDATE",  "tabla"=>$cTabla, "set" =>"", "where"=>"$cWhere" );
	}
	return $aReg;
}


/**
 * Crea un array que representa un sentencia SQL INSERT or UPDATE
 *
 * Si no se proporciona clausula WHERE se asume que es una sentencia
 * INSERT. No es necesarios incluir el literel "WHERE " en la clausula.
 * @see sql_add, sql_where, sql_sql
 *
 * @param string $cTabla tabla
 * @param string $cWhere Clausula WHERE 
 * @return $array La estructura interna
 */

function sql_nueva( $cTabla ,  $cWhere="" ){  	
	if ($cWhere=="") {
		$aReg= array ( "tipo" =>"INSERT",  "tabla"=>$cTabla, "campos" =>"", "values"=>"" );
	} else {
		$aReg= array ( "tipo" =>"UPDATE",  "tabla"=>$cTabla, "set" =>"", "where"=>"$cWhere" );
	}
	return $aReg;
}


/**
 * Añadir un campo y un valor a una sentencia-array de SQL
 *
 * @see sql_add, sql_where, sql_sql, sql_crear
 *
 * @param array $aReg Sentencia creado con sql_crear
 * @param string $cCampo Campo
 * @param string $cValor Valor del campo. 
 * @param string $cTipo ("cadena"). Tipo de campos: numero, fecha, cadena, checkbox, entero.
 * @param boolean $NULL (false) Se permiten valores NULL
 * @return boolean True si se ha añadido el campo.
 */

function sql_add ( &$aReg, $cCampo, $cValor, $cTipo="cadena", $NULL=false){
	if ( !is_array($aReg) ) {
		return false;
	}

	$cTemp= sql_codifica ( $cValor, $cTipo, $NULL );
	if ($aReg["tipo"]=="INSERT" ) {
		$aReg["campos"].= ($aReg["campos"]==""?"":",").  $cCampo;
		$aReg["values"].= ($aReg["values"]==""?"":",").  $cTemp;
	} else {
		$aReg["set"].= ($aReg["set"]==""? "":" ,"). "$cCampo=$cTemp" ;
	}
	return true;
}


/**
 * Añadir un campo, obteniendo su valor via request a una sentencia array de SQL.
 *
 * @see sql_add, sql_where, sql_sql, sql_crear
 *
 * @param array $aReg Sentencia creado con sql_crear
 * @param string $cCampo Campo
 * @param string $cValor Valor del campo. 
 * @param string $cTipo ("cadena"). Tipo de campos: numero, fecha, cadena, checkbox, entero.
 * @param boolean $NULL (false) Se permiten valores NULL.
 * @return void
 */

function sql_add_request( &$aReg, $cCampo, $cTipo, $NULL=false){
	sql_add ( $aReg, $cCampo, $_REQUEST[$cCampo], $cTipo, $NULL );
}


/**
 * Establece la cláusula where de una sentencia-array 
 *
 * @param array $aReg sentencia-array
 * @param string $cWhere Condiciones a añadir
 * @return void
 * @see sql_add, sql_sql, sql_crear
 */
function sql_where ( &$aReg, $cWhere ){
	$aReg["where"]= $cWhere;
}


/**
 * chequea si la sentencia-arrray esta vacio
 *
 * @param array $aReg sentencia-array
 * @return boolean true si esta vacio
 * @see sql_add, sql_sql, sql_crear, sql_where, sql_order
 */

function sql_esvacia( $aReg ){
	if ( !is_array($aReg) or !isset($aReg["tipo"]) ) {
		return true;
	}
	return ( $aReg["tipo"] ==  "INSERT" ? ($aReg["campos"]==""): ($aReg["set"]==""));
}


/**
 * Genera una sentencia SQL a partir de una sentencia array 
 *
 * @param array $aReg sentencia-array
 * @return string Sentencia SQL
 * @see sql_add, sql_where, sql_sql, sql_crear
 */

function sql_sql( $aReg ){
	if ($aReg["tipo"]=="INSERT" ) {
		return "INSERT INTO {$aReg['tabla']}  ({$aReg['campos']}) VALUES ({$aReg['values']})" ;
	} else {
		return "UPDATE {$aReg['tabla']} SET {$aReg['set']}". ($aReg["where"]==""?"" : " WHERE " . $aReg["where"]) ;
	}
}


/**
 * Añade el order a una serie de campos
 *
 * @param array $aReg sentencia-array
 * @param string $order ASC(defecto)|DESC
 * @return string Clausula ORDER BY o cadena vacia
 * @see sql_add, sql_where, sql_sql, sql_crear
 */

function sql_order ( $campos, $orden="ASC") {

	if ( $orden != "ASC" ) {
		$orden="DESC";	
	}

	if ( is_array($campos) ) {
		$campos = implode (",",$campos);
	}

	if ( !trim($campos) ){
		return false;
	}

	return " ORDER BY " . str_replace (",", " $orden,", $campos) . " $orden";
}

