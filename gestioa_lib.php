<?php 

/**
 *
 * Librería data-driven para gestionar y editar datos
 * Cambiada para gestión con el maquetador
 * @versión 19-enero-2008 
 * @author  Roger
 *
 * Correciones
 * 2009/05/05 campo acceso y correcion infofuncion, verificaciones.
 * 2009/04/30 Documentación
 * 2009/19/01 Correción en listafuncionCadena.
 *
 * dd_consulta
   cabecera:  th
   order: si existe para ordenar
   
 */


include_once ("paginacion.php");
include_once ("imagenes.php");


/**
 * Construye una etiqueta xHTML con sus atributos, y contenido 
 * @return etiqueta XHTML(Cadena)
 */


/**
 * Consulta de varios registros 
 * @param $cTitulo   titulo (h2)
 * @param $aTable    definiciones DD
 * @param $cSQL      consulta SQL sin LIMIT
 * @param $aNodo     información sobre controlador,accion e id.
 * @param $aOpciones menu para cada registro
 * @param $aMenu     Menu de opciones (puede ser una cadena) 
 */

function tablaConsulta ( $cTitulo, $aTabla,  $cSQL, $aNodo, $aOpciones="", $aMenu=NULL  ){
   global $aEstado, $hizkuntza; 
   
   $order   = $aEstado["order"];
   $orderBy = $aEstado["orderby"];

   $lHayOpciones = is_array($aOpciones) and count($aOpciones);

   if ( $cTitulo !="") {
      $cResul .= "<h2><span>$cTitulo</span></h2>\n";
   } else {
      $cResul  = "";
   }

   // Ordenar los campos
   if ($aEstado["order"] !="" and stripos ( $cSQL, "order by")===false ) {	   
   	 $cSQL .= sql_order( $aTabla[$aEstado["order"]]["order"] , ( $aEstado["orderby"] == "ASC" ? " ASC": " DESC"));					
   } 


   // incluir el menu de Opciones
   if ( !is_null($aMenu) ){		
		if ( is_array($aMenu) ) {
   		foreach ( $aMenu as $unaOpcion ){
   			$lista .= "<li>$unaOpcion</li>\n" ;
   		}
		} else {
		   $lista .= "<li>$aMenu</li>\n" ;
		}
		
		$cResul .= "<div id='menuConsulta'>\n<ul>\n$lista</ul></div>";
   }

   $aPaginacion = paginacion($cSQL, "leyenda", 20, 10, $hizkuntza, $aNodo);

   $cResul .="<table class='consulta'>\n";   

   /* Dibujar las cabeceras de datos */
   $nCont = 0;
   $lista = "";
   foreach ( $aTabla as  $oCelda) {
      if ( $oCelda["acceso"] !== false ){ 
   	   $lista .= "<th>";
   	   $cNodo = "c={$aNodo[c]}&amp;a={$aNodo[a]}";
      	if ( isset($oCelda["order"] )){
   	   	if ($nCont == $order) {
   	   		$lista .= "<a href='?$cNodo&amp;orderby=". ( $orderBy=="ASC" ? "DESC" : "ASC") .
   	   		          "&amp;order=$nCont' class='orden$orderBy'>{$oCelda[cabecera]}</a>";
   	   	} else {
   	   		$lista .=  "<a href='?$cNodo&amp;orderby=ASC&amp;order=$nCont' class='ordenASC'>" . $oCelda["cabecera"] . "</a>";
   	   	}
      	} else {
   	   	$lista .=  $oCelda["cabecera"];
      	}			
   	   $lista .=  "</th>\n";	   
   	   $nCont++;
	   }
   }
   if ( $lHayOpciones ) {
      $lista .= "<th>" . t("Opciones") . "</th>";
   }
   $cResul .= "<thead>\n<tr>\n$lista</tr>\n</thead>";
   
   /* Dibujar el pie de la pagina */       
   $cResul .="<tfoot><tr><td colspan='". (count($aTabla)+( $lHayOpciones ? 1 : 0 )). "' >".  $aPaginacion[3]."</td></tr></tfoot>\n";

   $cResul .="<tbody>\n";

   /* dibujar los datos */
   $lPar = true;
   $rsEmaitza= mysql_query($aPaginacion[2]);
   while ( $lerroa = mysql_fetch_array ( $rsEmaitza )) {
	   $cResul .=  "\n<tr" . ($lPar ? "": " class='impar' " ). ">";
	
	   // dibujamos cada celda según su tipo //
	   foreach ( $aTabla as  $oCelda) {
	      if ( $oCelda["acceso"] === false ){
	         continue;
	      }
	   	$aParametros = explode  (" ", $oCelda["campo"] );	
	   	// ------- Primero calcular el campo ----------
	   	switch ( $aParametros[0] ){
	   		case "lista" :								
	   			if ( isset($aParametros[2]) and isset($aParametros[1]) ) {
	   				$campo =   mlista ($aParametros[2], $lerroa[$aParametros[1]]) ;
	   			} else {
   					$campo   =  "";
	   			}
				break;
			
			   case "idioma" :
		   	case "funcionget" :
		   		$campo = tCampo ( $lerroa, $aParametros[1], $aParametros[2]) ;
		   		break;
			
		   	case "funcion" :
		   	   if ( isset($aParametros[2]) ) {
		   			$campo =   call_user_func( $aParametros[1], $lerroa[$aParametros[2]]) ;
		   		} else {
					   $campo =   call_user_func( $aParametros[1], $lerroa ) ;
   				}   
   				break;
			
			   default:
	   			$campo = $lerroa[ $aParametros[0] ];
	   	}
		
		   // -------- segundo, calcular su representación ------------
   		$aParametros = explode  (" ", $oCelda["tipo"] );		
	   	$formato = $aParametros[0];
	   	switch ( $formato ) {
	   		/* Los parametros pueden ser:
	   			- el nombre del campo
	   			- una función que se denotara funcion NombreFuncion [Campo]
	   			- una lista campo cadenalista
	   		*/
		   	case "adjunto":
            case "url" : 	
               $cTempBase = ( isset($aParametros[1]) ? $aParametros[1] : "");		   
               $visualizar = "<a href='$cTempBase/$campo'>" . corta($campo,25) . "</a>";
               break;

		   	case "lista" :
		   		if ( isset($aParametros[1]) ) {
		   			$visualizar .=   mlista ($aParametros[1], $campo )  ;
		   		} else {
   					$visualizar .=  $campo;
	   			}
	   			break;
            
            case "sino":                        
               $idiomas   = ( $hizkuntza=="es" ?  array("No","Si"): array("Ez", "Bai") );
               $visualizar=  $idiomas[$campo] ;
               break;            
            
            case "si" :   
               $idiomas    = ( $hizkuntza=="es" ?  array("","Si"): array("", "Bai") );
               $visualizar =  $idiomas[$campo]; 
               break;            
               
            case "no" :   
               $idiomas    = ( $hizkuntza=="es" ?  array("No",""): array("Ez", "") );
               $visualizar =   $idiomas[$campo]; 
               break;  
            
               
	   		case "funcion" :
	   		   $visualizar =  call_user_func( $aParametros[1], (isset($aParametros[2]) ?  $campo: $lerroa));
	   		   break;  
	   				
   			default:
				   $visualizar = $campo;
		   }

         $class=  (isset($oCelda["class"]) ? $oCelda["class"] : $formato );		   
		   
		   if ( isset( $oCelda["styling"])){		      
		      $cResul .= "<td class='$class'>" . call_user_func( $oCelda["styling"], $visualizar ) . "</td>"; 
		   } else {
		      $cResul .= "<td class='$class'>$visualizar</td>";
		   }
	}
		
	   /* ahorita dibujamos las opciones */
	   if ( $lHayOpciones ) {
   	   $cResul .="<td class='opciones'>";
         foreach ( $aOpciones as $aOpcion ){
            if ( is_array ( $aOpcion ) ){
	      	   $cResul .= strtr( $aOpcion["enlace"],  array ( "%ID%" =>$lerroa[$aOpcion["ID"]]  ) )  . " ";
	      	} else {
	      	   $cResul .= sprintf ( $aOpcion, $lerroa[0] );	      	   
	      	}
        	}
	      $cResul .= "</td>";
	   }
   	$cResul .= "</tr>";
	   $lPar = ! $lPar;
	}
   $cResul .= "</tbody>\n";
   $cResul .="</table>\n";
	
   mysql_free_result($rsEmaitza);
   return $cResul ;
}


function mScript (){
$aTemp = explode ( "/", $_SERVER["PHP_SELF"] );
if ( count($aTemp) > 0 ) 
	return $aTemp[ count( $aTemp)-1];
else
	return $_SERVER["PHP_SELF"] ;
} 


/**
 * Tabla para editar/añdir  un registro completo 
*/

function tablaEdicion ( $cTitulo, $aDatos,  $cSQL="", $aHidden="", $cEnviar="", $aItzuli="" ){
$aAfter  = array();

$cResul  = $cTitulo  ? "<h2><span>$cTitulo</span></h2>\n" : "" ;
$cResul .= "\n<form name='editar' method='post' action='". mScript() . "'  enctype='multipart/form-data'  >\n<div>";

// Buscamos el primero campo obligatorio.
foreach ( $aDatos as $fila ) {
   if (  $fila["acceso"] !== false and isset($fila["verifica"]) and $fila["verifica"]=="no_vacio" ) {
      $cResul .= "<div class='obligatorio'>*" . t("beherrezkoa") . "</div>";
      break;
   }
}


$cHidden  = "";
if (is_array($aHidden)) {
	foreach ( $aHidden as $name => $value )  {
		$cHidden .= "<input type='hidden' name='$name' value='$value' />\n";
	}
}
 

$cTabla   = "<table class='edicion'>\n";

if ( $cSQL != ""  ) {
	/* en un modificación los valores se obtienen por una consulta SQL */	
	$lerroa    = mysql_query_registro ( $cSQL);
} else {
	/* en una adición de obtienen con los valores por defecto */
	$lerroa    = array();
	foreach ( $aDatos as $fila ) {
		$lerroa[$fila["campo"] ]= (isset($fila["defecto"])? $fila["defecto"] : "" );
	}
}

$nCont = 1;
if ( $lerroa ) {
	foreach ( $aDatos as $fila ){
	   if ( $fila["acceso"]=== false ) {
	      continue;
	   }
		$aParametros = explode  (" ", $fila["tipo"] );
		$cTipo  = strtolower($aParametros[0]);
		if ( $cTipo != "hidden" and $cTipo!="separador" and $cTipo!="codigodirecto" and $cTipo!="fijo") {
			$cTabla .= "\n<tr>\n";
			if ( isset($fila["verifica"]) and $fila["verifica"]=="no_vacio") {
            $cTabla .= "<th><label for='campo_$nCont' class='obligatorio'>". $fila["cabecera"] . "*</label></th>\n";			
			} else { 
			   $cTabla .= "<th><label for='campo_$nCont'>". $fila["cabecera"].  "</label></th>\n";
			}			   
			$cTabla .= "<td>";
		}
	
	   $generaTDTR= true;
		switch ( $cTipo ){
			/* @TODO Traducir literales */
			case "fijo":
			   break;			
			
			case "adjunto":
				if ( $lerroa[$fila["campo"]]) {
					$cTabla .= "<a href='". $aParametros[1] . "/". $lerroa[$fila["campo"]] ;
					$cTabla .= "' > " . corta($lerroa[$fila["campo"]],50) . "</a><br>";
					$cTabla .= t("Cambiar fichero adjunto: ") . "<input type='file' name='". $fila["campo"]  . "' class='botoiak' />";
				}	else {
					$cTabla .= t("Adjuntar nuevo archivo: "). "<input type='file' name='". $fila["campo"]  . "' class='botoiak' />";
				}
				$fila["adicional"] .=  ( $fila["adicional"] ? "<br/>": "" ) .  "(MAX: ". ini_get("upload_max_filesize")  ."bytes)" ;
				break;			
			
			case "imagen":
			case "irudia":	
				if ( $lerroa[$fila["campo"]]) {
					$cTabla .= "<img src='./". $aParametros[1] . "/". $lerroa[$fila["campo"]]. "' class='irudia' />";
					$cTabla .= t("Cambiar  imagen: "). "<input type='file' name='". $fila["campo"]  . "' class='botoiak' />\n";
					if ( $fila["adicional"] ) {
					   $cTabla .= "<span class='adicional'>". $fila["adicional"] . "</span>"; 
					}
					$cTabla .= "<br/>". t("Borrar imagen: "). "<input type='checkbox' name='{$fila[campo]}_EZABATU' value='1' />";
				}	else {
					$cTabla .= t("Sin imagen") ."<br/>";
					$cTabla .= t("Nueva  imagen: ").  "<input type='file' name='". $fila["campo"]  . "' class='botoiak' />";
				}
				break;
			
			case "separador":
				$cTabla .= "\n<tr>\n";
				$cTabla .= "<th colspan='2' class='separador'>".$fila["cabecera"] ."</th></tr>\n";
				$generaTDTR = false;
				break;

         case "codigodirecto":
            $aAfter[]= $fila["funcion"];
            break;				

			case "hidden":
				$cHidden .= "<input type='hidden' value='". (isset($fila["value"]) ?  $fila["value"] : $lerroa[$fila["campo"]] ) ."' name='". $fila["cabecera"] ."' />";
				$generaTDTR = false;
				break;
			
			case "listavalores":
			case "lista":
				$cTabla .= "<select name='" . $fila["campo"] . "'  id='campo_$nCont' >\n";		
				if ( isset($fila["valoresAdicionales"] )) {
					foreach ( $fila["valoresAdicionales"] as $adicionalID => $adicionalValor ) {
						$cTabla .= "<option value='". $adicionalID . "'" . ($adicionalID==$lerroa[$fila["campo"]] ? " SELECTED ": "" ) .">" ;
						$cTabla .= $adicionalValor. "</option>\n";
					}
				}				
				$aLista = explode ( "|"  , substr( $fila["tipo"] , ( $cTipo== "lista" ? 6 : 13) ));				
				$nParametros= 0;
				while ( $aLista[$nParametros] ) {	
					if ( $cTipo == "lista" ) {
						$cTabla .= "<option value='$nParametros'" .  ($lerroa[$fila["campo"]]==$nParametros ? " SELECTED ": "" )." >" ;
					} else {
						$cTabla .= "<option value='". $aLista[$nParametros] . "'"  ;
						$cTabla .= ($lerroa[$fila["campo"]] == $aLista[$nParametros] ? " SELECTED ": "" ) ." >" ;	
					}
					$cTabla .=  $aLista[$nParametros]. "</option>\n";
					$nParametros++;
				}					
				$cTabla .= "</select>\n";
				break;
			
			case "listafuncion":
			case "listafuncioncadena":
			case "listasql":
				$cTabla .= "<select name='" . $fila["campo"] . "' id='campo_$nCont'  " . ( isset($fila["clase"]) ? " class='" . $fila["clase"] ."'" :""). ">\n";		
				if ( isset($fila["valoresAdicionales"] )) {
					foreach ( $fila["valoresAdicionales"] as $adicionalID => $adicionalValor ) {
						$cTabla .= "<option value='". $adicionalID . "'" ;
						if ( substr($adicionalValor,0,3)== "---") {
   						$cTabla .=  " disabled='disabled' "; 
   					}   						
						if  ( $adicionalID==$lerroa[$fila["campo"]] ) {
						    $cTabla .= " selected='selected' ";
						}
						$cTabla .=  ">" . $adicionalValor. "</option>\n";
					}
				}
				if ( $cTipo == "listasql" ) {
					$rsTemporal = mysql_query ( substr ( $fila["tipo"], 8 ) );
					while ( $lerroaTemp = mysql_fetch_array( $rsTemporal) ) {
						$cTabla .= "<option value='". $lerroaTemp[0]. "'" .  ($lerroaTemp[0]==$lerroa[$fila["campo"]] ? " SELECTED ": "" );
						$cTabla .= " >" . $lerroaTemp[1]. "</option>\n";
					}
					mysql_free_result ( $rsTemporal);
				} else {
					$aTemporal = call_user_func ($aParametros[1])  ;
					foreach ( $aTemporal as $clave => $valor )  {
						$cTabla .= "<option value='". $clave. "'" . ($clave == $lerroa[$fila["campo"]] ? " SELECTED ": "" );
						$cTabla .= " >" . $valor. "</option>\n";
					}
				}	
				$cTabla .= "</select>\n";
				break;
			
			case "checkbox": 
				$cTabla .= "<input type='checkbox'  id='campo_$nCont' name='". $fila["campo"]. "'";
				$cTabla .= ( $lerroa[$fila["campo"]]==1 ? " checked ": "") . " value='1' />";	
				break;
			
			case "info":
				$cTabla .= "<input type='text'  name='". $fila["campo"]. "' value='".   $lerroa[$fila["campo"]] . "'" ;
				$cTabla .= " disabled='disabled' class='campoInformativo'  id='campo_$nCont' />";	
				break;

         case "html":         				
				if ( isset($aParametros[2]) ) {
					$cTabla .= call_user_func ( $aParametros[1] , $lerroa[$aParametros[2]])  ;
				} else {
					$cTabla .= call_user_func ( $aParametros[1] ,$lerroa )  ;
				}				
									
				break;
			
			case "infofuncion":
				$cTabla .= "<input type='text' ";
				if (isset ($fila['campo'])) {
					$cTabla .= " name='". $fila["campo"].  "'" ;
				}
				if ( isset($aParametros[2]) ) {
					$cTabla .= " value='". call_user_func ( $aParametros[1] , $lerroa[$aParametros[2]]) . "'" ;
				} else {
					$cTabla .= "  value='". call_user_func ( $aParametros[1] ,$lerroa ) . "'" ;
				}				
				$cTabla .= " disabled='disabled' class='campoInformativo'  id='campo_$nCont'  />";					
				break;
			
			case "nuevopassword": 
			case "verificapassword":
				$cTabla .= "<input type='password' id='campo_$nCont'  value='' name='". $fila["campo"] . "'";
				if ( count($aParametros)  == 1)
						$cTabla .= " size='". $aParametros[1]."' maxlength='". $aParametros[1]."'/>";
				elseif (count($aParametros)  == 2)
						$cTabla .= " size='". $aParametros[1]."' maxlength='". $aParametros[2]."'/>";
				else
					    $cTabla .= " >";
				break;
			
			case "texto": 
		
            if ( defined("FCKEDITOR") and $aParametros[1]>4) {
               $oFCKeditor = new FCKeditor($fila["campo"]) ;
               $oFCKeditor->BasePath = FCKEDITOR;
               $oFCKeditor->Value    = $lerroa[$fila["campo"]];
                                
               $oFCKeditor->Height  = $aParametros[1]*16;
               $oFCKeditor->Width   = $aParametros[2]*6;
               $cTabla .= $oFCKeditor->Create() ;                             
                
               $generaTDTR = false;
            } else {             			
   				$cTabla .= "<textarea id='campo_$nCont' {$fila[WYSIWYG]} rows='". $aParametros[1] . "' cols='". $aParametros[2]. "' name='". $fila["campo"]. "'>";
	   			$cTabla .= $lerroa[$fila["campo"]] ."</textarea>";
	   		}
				break;
				
      	case "readonly":
      	   $cTabla .=  ddlib_etiqueta_html ("input", 
      	                 array( "type"    => "text", 
      	                         "id"      => "campo_$nCont" , 
      	                         "value"   => $lerroa[$fila["campo"]], 
      	                         "name"    => $fila["campo"],      	                                	              
      	                         "readonly"=>'readonly' ,
      	                         "size"    => $aParametros[1]) ) ;      	                                	              
      	   break;										
				
			case "cadena":
				$cTabla .= "<input type='text' id='campo_$nCont'  value='". $lerroa[$fila["campo"]] ."' name='". $fila["campo"] . "'";
				switch ( count($aParametros) ) {
				   case 2 : 
						$cTabla .= " size='". $aParametros[1]."' maxlength='". $aParametros[1]."'/>";
						break;
					case 3 :
					   $cTabla .= " size='". $aParametros[1]."' maxlength='". $aParametros[2]."'/>";
						break;
				   default:
				      $cTabla .= "  />"; 
				      break;
			   }
				break;
				
			case "fecha":
			  $cTabla .= sprintf ( "<input type='text' id='campo_$nCont'  value='%s' name='%s' size='8'  onclick=\"mostrarCalendario('campo_$nCont','1','2010')\">", 
			                       $lerroa[$fila["campo"]],
			                       $fila["campo"]  ) .
			             "<div  id='calendario'  style='visibility:hidden;position:relative;'  class='arrastrable' ></div>";
			  break;           
	   
			
			default: 
				$cTabla .= "<input type='text' id='campo_$nCont'  value='". $lerroa[$fila["campo"]] ."' name='". $fila["campo"]. "'/>";
				break;		
		}
		if ( $generaTDTR) {
		   // dibujar la información adicional si procede
			if ( $fila["adicional"] and $cTipo != "imagen" and $cTipo !="irudia") {
				$cTabla .= "<span class='adicional'>". $fila["adicional"] . "</span>";
			}
			$cTabla  .= "</td></tr>\n";
		}
		$nCont++;
	} // del foreach
	
	// botones de Enviar y Volver. 
	$cTabla .= "<tr><td class='botoiak' colspan='2'><input type='submit' value='$cEnviar' class='botoiak' />";
	if ( $aItzuli ) {
		$cTabla .= "<input type='reset' value ='".t("Volver") . "' onclick=\"location.href='?c={$aItzuli[c]}&amp;a={$aItzuli[a]}'\" class='botoiak' />";
	}
	
	$cTabla .= "</td></tr>\n";
	$cResul .= $cHidden . "\n" . $cTabla;
	
}	
$cResul .= "</table>\n";	
$cResul .= "</div></form>\n";

foreach ( $aAfter as $i=>$c) {
   $cResul .= call_user_func($c,$lerroa );
}

return $cResul;
}


/* 
 * Verificar un campo
 */

function dd_verificaCampo( $aDatos ){
   if ( !isset($aDatos["verifica"])) {
      return "" ; 
   } 
   
	$aErrores = explode ( "|" , $aDatos["verifica"]);  
  	$cCampo   = $_REQUEST[$aDatos["campo"]];
  	  	  	  		
   switch ( trim(strtolower($aErrores[0]))){
   	case "no_vacio":
   	case "no_nulo":
   		if ( $cCampo=="" ){
   			return (isset($aErrores[1])? $aErrores[1] : "El campo ". $aDatos["cabecera"] . " no puede estar vacio.");
   		}
   		break;			

   	case ">"	:
			if ( $cCampo > $aErrores[1])
				return (isset($aErrores[2])? $aErrores[2]: "El campo ". $aDatos["cabecera"] . " tienen que ser mayor que ". $aErrores[1]);
			break;
	
		case ">=":
			if ( $cCampo >= $aErrores[1])
				return (isset($aErrores[2])? $aErrores[2]: "El campo ". $aDatos["cabecera"] .
			                   " tienen que ser mayor o igual que ". $aErrores[1]);
		   break;
	
		case "<":
			if ( $cCampo < $aErrores[1])
  				return (isset($aErrores[2])? $aErrores[2]: "El campo ". $aDatos["cabecera"] . " tienen que ser menor ". $aErrores[1]);
   		break;
	
		case "<=":
   		if ( $cCampo < $aErrores[1])
				return (isset($aErrores[2])? $aErrores[2]: "El campo ". $aDatos["cabecera"] . " tienen que ser menor o igual ". $aErrores[1]);
			break;
	
		case "!=":
   		if ( $cCampo != $aErrores[1])
  				return (isset($aErrores[2])? $aErrores[2]: "El campo ". $aDatos["cabecera"] . " tienen que ser menor o igual ". $aErrores[1]);
   		break;
	
		case "between":
		case "entre":
		   if ( $cCampo >= $aErrores[1] and $cCampo <= $aErrores[2])
			   return (isset($aErrores[3])? $aErrores[3]: "El campo ". $aDatos["cabecera"] .
			                  " tienen que estar entre ". $aErrores[1] . " y " . $aErrores[2] );
		   break;

		case "verifica": 			// comprobar que dos campos coinciden		 		
 		   if ( $cCampo != $_REQUEST[$aErrores[1]])
			   return (isset($aErrores[2]) ? $aErrores[2] : "Verificación incorrecta");
	      break;

		case "funcion":
 			if ( call_user_func( $aErrores[1], $cCampo, ($cWhere=="" ? "INSERT" : "UPDATE") ))
			   return (isset($aErrores[2])? $aErrores[2] : "El campo no cumple la condicion");
		   break;

  		case "funcioncompleta":
	   	/* @TODO ¿pasamos todo el request? */
	   	if ( call_user_func ( $aErrores[1], $_REQUEST ))
	   		return  (isset($aErrores[2])? $aErrores[2]: "El campo no cumple la condicion");
	   	break;		
	}
	return "";	   
}


/**
 * Función que construye la SQL para insertar o actualizar los datos y los guarda.
*/

function datuakGorde ( $cTabla, $cWhere, $aEdicion, $aMensajes = NULL ){
   $cError="";
   $aRet  ="";

   
   //  Primero verificamos las condiciones de cada campo 
   foreach ( $aEdicion as $aDatos ) {	      
	   $cError .= ( isset($aDatos["verifica"]) ? dd_verificaCampo ($aDatos) : ""); 	  
	} 
      
   // Revisar los errores
   if ( $cError !="") {
	   if ( !is_null($aMensajes )  and isset($aMensajes["gaizki"] ) ){
	   	return mensajes ( $cError, "gaizki" ) ;
	   }
   	return FALSE;
   }
   
   $aSQL = sql_crear ( ($cWhere ? "update" : "insert" ) , $cTabla,  $cWhere );
  
   // al terminar la verificación revisar los errores
   foreach ( $aEdicion as $aDatos ) {
   	$aParametros = explode (" ", $aDatos["tipo"]);
   	switch ( strtolower($aParametros[0] )){
   		case "hidden":
   		case "info":
   		case "codigodirecto":
   		case "infofuncion":
   		case "separador":
   		case "verificapassword":
   		case "readonly":

   		// case "html":  quitado por Roger...PELMA!!
   			break;

		   case "adjunto":
		      $lPendiente = true;
		      break;
   		
   		case "imagen" :
   		case "irudia" :   
            if ( $_REQUEST[ $aDatos["campo"]."_EZABATU"]=='1'){
               sql_add($aSQL,  $aDatos["campo"], "", "cadena");               
            } else {   		
				  $lPendiente = true;
				}  
   			break;
		
   		case "nuevopassword":
   			if ( $_REQUEST[$aDatos["campo"]] !="" ){
   			   if ( isset($aParametros[3]) and $aParametros[3]=="md5")
   			      sql_add( $aSQL, $aDatos["campo"], md5($_REQUEST[$aDatos["campo"]]), "cadena");
   			   else
   				   sql_add_request( $aSQL,  $aDatos["campo"], "cadena");
   			}
   			break;

         case "fijo":             
   			sql_add($aSQL,  $aDatos["campo"], $aDatos["valor"], $aParametros[1]);
            break;
         
   		case "listafuncion":
   			sql_add_request ( $aSQL,  $aDatos["campo"], $aParametros[2]);
   			break;
		
        case "html":
            // añadido por RO 05/05/2010
            if ( isset($aDatos["campo"])) {
                sql_add_request ( $aSQL,  $aDatos["campo"], $aParametros[0]);
            } elseif ( isset($aDatos["campos"])) {
                foreach ( $aDatos["campos"] as $tcampo=>$tipo) {
                  sql_add_request ( $aSQL,  $tcampo, $tipo );
                }
            }
            break;
   		default:
			   sql_add_request ( $aSQL,  $aDatos["campo"], $aParametros[0]);
   	}
   }

   // si hemos recogido campos, salvamos (puede ser un caso de solo imágenes )
	if ( sql_esvacia($aSQL) ) {
      $lResul = true ;
	} else {	   
   	$lResul = mysql_query( sql_sql ( $aSQL ) );
   }

	if ( $lResul and $lPendiente ){
		// hacemos una seguna pasada para la imágenes, los adjuntos y demás.		
		// preparamos la nueva ID		
		if ( $cWhere == ""){
			$mID     = mysql_insert_id();
			$campoID = mysql_campoClave($cTabla);
			$aSQL    = sql_crear ("update", $cTabla, "$campoID=$mID" );			
		} else {
			$campoID = mysql_campoClave($cTabla);
			$mID     = mysql_mlookup("SELECT $campoID FROM $cTabla WHERE $cWhere" );
			$aSQL    = sql_crear ("update", $cTabla, $cWhere );
		}
		
      // ahorita buscamos imágenes y demás      
		foreach ( $aEdicion as $aDatos ) {
			$aParametros = explode (" ", $aDatos["tipo"]);
			$cTipo       = strtolower($aParametros[0]);
			$cFile       = $aDatos["campo"];
			         			         			         						         			
			if ( ($cTipo == "adjunto" or  $cTipo == "imagen" or $cTipo == "irudia" ) 
			     and isset($_FILES[$cFile]) and $_FILES[$cFile]["name"]  
			     and isset($aParametros[1]) ){
				if ( isset($aParametros[2]) ) {
					$aTempFile    = extrae_ExtensionNombre ($_FILES[$cFile]["name"]);
					$cNuevoNombre = strtr ( $aParametros[2], array("%i"=>$mID, "%n"=>$aTempFile["nombre"], "%e"=>$aTempFile["ext"]));
				} else {
					$cNuevoNombre = $_FILES[$cFile]["name"];
				}
				
				if ( !move_uploaded_file($_FILES[$cFile]["tmp_name"], $aParametros[1] . "/" . $cNuevoNombre) ){
              echo sprintf("<div class='gaizkiTxiki'>Error al subir el fichero (campo %s). ¿tiene más de %sbytes?</div>\n", $aDatos["campo"],ini_get("upload_max_filesize") ) ;
            } else {				   
					sql_add($aSQL, $aDatos["campo"], $cNuevoNombre, "cadena");
					
					// ahora vemos si hay que hacer thumbnail 
					if (  ($cTipo == "imagen" or $cTipo == "irudia") and isset($aDatos["thumbnail"]) ){
					   $aThumb = explode ( " ", $aDatos["thumb"] );
					   switch ( count($aThumb)) {
					     case "0" : break;
					     case "1" : $aThumb[1] = $aThumb[0];
					     case "2" : $aThumb[2] = 75;
					     case "3" : $aThumb[3] = "thumb";
					     case "4" : $aThumb[4] = "nocrop";					     
					     default  :
					         crearThumbnail ( $aParametros[1], $cNuevoNombre, $aThumb[3], 
      					      $aThumb[0], $aThumb[1], $aThumb[2], $aThumb[4] ) ;   			   
					   }					   					
					}
					// ahora vermos si hay que ajustar la imagen
               if (  ($cTipo == "imagen" or $cTipo == "irudia") and isset($aDatos["ajustar"]) ){
					   $aThumb[0]= (int) $aDatos["ajustar"];					   
					   mImageResize( $aParametros[1] . "/" . $cNuevoNombre, $aParametros[1] . "/" . $cNuevoNombre, "KEEP_PROPORTIONS_ON_BIGGEST", $aThumb[0]);					   				      			   		   					   					
					}														 		         	
				} 				          
			}
   	}
   	// grabamos si es que hemos podido mover   	
   	if ( !sql_esvacia($aSQL) ) {
     		mysql_query( sql_sql($aSQL) );
     	}
    
   	
	}

   // DEBUG 
   if  ( !$lResul )  
   	echo "<h3>Error</h3>" . sql_sql( $aSQL ) . "<br>" . mysql_error();
  
   if ( !is_null($aMensajes )) {
	   if ( $lResul ) {
	   	echo "<div class='ondo'>". $aMensajes["ondo"] . "</div>\n";
	  	} else {
   		echo "<div class='gaizki'>". $aMensajes["gaizki"] . "<p>SQL:" . sql_sql($aSQL) . "</p><p>MYSQL:". mysql_error() . "</p></div>\n";
		}
   }
   return $lResul ;
}


/* borrar julio de 2009
function anadir_orden ( $cSQL , $cOrden, $order, $orderBy )  {
	if ($cOrden!="") {			
			$cSQL .= " order by " . $cOrden . " " .  $orderBy ;	
	}
   return $cSQL;
} 
*/


/**
* funciones para mostrar resultados 
*/

function dd_mostrarActivo ( $nValor) {
   return  ( $nValor==1 ? "Bai": "");
}


function tablaEdicionConsulta ( $cTitulo, $aTabla,  $cSQL, $aHidden=NULL, $nMax=5 ){
   static $nId;
   global $aEstado, $hizkuntza; 
   
   $nId++;
   $cResul  = ( $cTitulo !="" ?  "<h3><span>$cTitulo</span></h3>\n" : "" );
      
   $cResul .= "<form action='?'>\n";
   
   if ( is_array ($aHidden) ){
      foreach ( $aHidden as $var => $valor ) {
         $cResul .= "<input type='hidden' name='$var' value='$valor'>\n";
      }
   }
       
   $cResul .="<table class='consulta'>\n";
   $cResul .="<thead>\n<tr>";

   /* Dibujar las cabeceras de datos */
   foreach ( $aTabla as  $oCelda) {
	   $cResul .= "\n   <th>{$oCelda[cabecera]}</th>";
  	}				
   $cResul .= "\n   <th></th>";
     
   $cResul .="</tr>\n</thead>\n";   
   $cResul .="<tbody>\n";

   /* dibujar los datos */
   $onChange = "onchange='change_$nId()'";   
   $cHidden  = "";
   $rsEmaitza= mysql_query($cSQL);      
   for ( $nCont=0, $lHayDatos = true; $nCont < $nMax ; $nCont++) {
      
      if ($lHayDatos ) {
         $lerroa = mysql_fetch_array ( $rsEmaitza );
         if ( !$lerroa ) {
            $lHayDatos = false;
            $lerroa = array();
         }
      }
      
	   $cResul .=  "   <tr class='". ($lHayDatos ? "datos" : "nuevo") . ($nCont%2 ? ' impar' : "" ) .     "' >";
	
	   // dibujamos cada celda según su tipo
	   $cPrimero = " class='primero'";
	   foreach ( $aTabla as  $oCelda) {
	      $campo = $oCelda['campo'];
	      $valor = $lerroa[$campo];
	      if ( $oCelda["tipo"] == "hidden" ) {
	         $cHidden .= "<input type='hidden' name='$campo$nCont' value='$valor'>\n";
	      } else {
	         $cResul .= "<td$cPrimero><input tpye='text' name='$campo$nCont' value='$valor' $onChange></td>";
	         $cPrimero ="";
	      }
	   }
		$cResul .= "</tr>\n";
		 				
	}
   $cResul .= "</tbody>\n";
   $cResul .= "</table>\n";
   
   $cResul .= $cHidden;   

   $cResul .= "<input type='submit' value='". t("GordeAldaketak"). "' id='actualizar_$nId' class='actualizar'>\n";
   $cResul .= "<script type='text/javascript'>
      function change_$nId(){ document.getElementById('actualizar_$nId').disabled=false;};
      document.getElementById('actualizar_$nId').disabled=true;</script>\n";   
   
   $cResul .= "</form>\n";	
   mysql_free_result($rsEmaitza);
   return $cResul;
}


/**
 * Test de unidad
 */
 
/*
echo ddlib_etiqueta_html ("input",    array ( "class"=>"hola" ), "NOO" );
echo ddlib_etiqueta_html ("textarea", array ( "class"=>"hola" ), "valor" );
*/



?>