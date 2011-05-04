<?php

/**
 *
 * Librería data-driven para gestionar y editar datos
 * Cambiada para gestión con el maquetador
 * @version 2011-05-04
 * @author  Roger
 * @todo Borrar imagenes y adjuntos.
 *
 * Correcciones
 * 2011/05/04 correciones variables sin declarar
 * 2010/09/30 Añadido (again) siguienteAccion en ddlib_edicion.
 * 2010/09/27 Corregido: paginacion ahora con querystring como variable.
 *             correciones en el password.
 * 20010/06/29 + ddlib_salvar. Como el guardar, pero con un orden lógico de argumentos.
 * 2010/06/13 Corregido: ddlib_consulta al manejar dd con campos sin acceso, calculaba
 *             mal la cabecera y no se ordenaba.
 * 2010/06/02 Corregido: opcionId, opcionID ahoa son opcionesID (por coherencia )
              en edición hay un nuevo tipo listavalores.
 * 2010/06/09 Corregido borrar imagenes y adjuntos. Falto borrar el fichero.
 * 2010/06/08 Ampliado: order pueder ser 1,con lo cual se asume que es campo.
              En consulta: la fecha sale en el formato adecuado, añadido campo siNO, SIno, SINO

 * 2010/06/02 Corregido: opcionId, opcionID o primer campo
 * 2010/06/02 Corregido: tablaID de edicion
 * 2010/05/28 Corregido: primer separador genera la tabla
 * 2010/05/25 corregido añadir, ordenactual en consultas
 * 2010/05/21 en edición se puede elegir el método, corregido htmldespues, checkboxes.
              boton volver oculto por defecto.
 * 2010/05/10 correciones: en consultar th con clase.
 * 2010/05/10 correciones menores
 * 2010/05/05 Correciones dd_addlib_request ¿borrador?
 *            campos html.
 * 2010/04/30 Correciones repetidas
 * 2010/03/09 Sigue refactorización: opcionesId, opcionesSeparador.
 * 2010/03/03 Sigue refactorización.
 * 2010/02/25
 * 2010/02/03 Correciones. Renombrado a ddlib_
 * 2009/11/24 Reinicio.
 * 2009/11/23 boton volver automático
 * 2009/11/21 añadir irudia a tabla de consulta
 * 2009/11/20 class=formato en tablas de edición
 * 2009/05/05 campo acceso y correcion infofuncion, verificaciones.
 * 2009/04/30 Documentación
 * 2009/19/01 Correción en listafuncionCadena.
 *
 */

include_once ("paginacion.php");
include_once ("imagenes.php");
include_once ("funciones.php");

/* experimento */
class ddlib_dd {
  public $tabla;
  public $id;
  public $idEesNumerico = true;

  public $consultas;
  public $edicion;

  function __construct ( $tabla, $id='', $idEsNumerico = true){
    $this->tabla       = $tabla;
    if ( $id=='' ){
       $this->id = mysql_campoClave($tabla) ;
    } else {
       $this->id = $id ;
    }
    $this->idEsNumerico= $idEsNumerico;
  }

  function edicion($dd) {
     $this->edicion = $dd;
  }

  function consultar($dd, $consulta="principal") {
     $this->consultas[$consulta]= $dd;
  }


}



/**
 * Construye una etiqueta xHTML con sus atributos, y contenido
 * @return etiqueta XHTML(Cadena)
 */


function ddlib_etiqueta_html ($etiqueta, $atributos=NULL, $contenido="" ) {
   $cRet = "";
   if ( $atributos ){
      foreach($atributos as $atributo=>$valor){
           $cRet .=  " $atributo='$valor'";
      }
   }
   $lCerrada = in_array ( $etiqueta, array ("input", "img", "link", "meta", "br", "hr" ));
   $cRet = "<$etiqueta$cRet" . ( $lCerrada ? " />" :  ">$contenido</$etiqueta>" );
   return $cRet;
}


/**
 * Obtiene el valor de un campo contenido en un array de datos
 * Se utiliza en consulta.
 * @return valor
 */

function ddlib_obtenerCampo ( &$aCampo, $aFila){

    if ( isset($aCampo["campos"]) && is_array ( $aCampo["campos"])){
        foreach ( $aCampo["campos"] as $campo => $tipo ){
            $aRet[$campo]= $aFila[$campo];
        }
        return $aRet;
    }

    $aParametros = explode  (" ", $aCampo["campo"]);

    switch ( strtolower($aParametros[0]) ) {

        case "funcionget" :  // depreceated. Usar tcampo.
        case "tcampo" :
            $idioma= ( isset($aParametros[2]) ? $aParametros[2] : "");
            return tCampo ( $aFila, $aParametros[1], $idioma) ;

        case "serialize":
        case "serializa":   //para castellano
            return unserialize ($aFila[$aParametros[1]] );

        case "funcion" :
            if ( count($aParametros) ==3 ) {
                return call_user_func($aParametros[1], $aFila[$aParametros[2]]) ;
            } else {
                return call_user_func($aParametros[1], $aFila ) ;
            }

        default:
            return $aFila[$aParametros[0]];
    }
    return "";
}


/**
 * Obtiene la representación html de un campo.
 * Se utiliza en consulta.
 * @return valor
 */

function ddlib_visualizarCampo( &$aCampo, $campo, $fila ){
    $idioma = tIdiomaPorDefecto();

    $aParametros = explode( ' ', $aCampo["tipo"] );
    $formato = $aParametros[0];

    switch ( $formato ) {
        case "adjunto":
        case "url" :
            if ( $campo ) {
                // @TODO verificar otros protocolos
                if ( substr($campo,0,4)!="http")  {
                    $cTemp      = ( isset($aParametros[1]) ? $aParametros[1] : "") . $campo;
                } else {
                    $cTemp  = $campo;
                }
                return "<a href='$cTemp'>" . corta($campo,25) . "</a>";
            } else {
                return "";
            }

        case "implode":
            return ( is_array($campo) ? implode( substr($aCampo["tipo"],8),$campo ): $campo);

        case "lista" :
            if ( isset($aCampo["lista"])) {
                return ( isset($aCampo["lista"][$campo]) ? $aCampo["lista"][$campo]: $campo );
            } elseif ( isset($aParametros[1]) ) {
                return mlista ($aParametros[1], $campo )  ;
            } else {
                return   $campo;
            }


        case "checkbox":

        case "SINO":
            return "<strong>". ( $campo ? tIdiomaLocale ("SI") : tIdiomaLocale ("NO") ) . "</strong>";
        case "siNO":
            return ( $campo ? tIdiomaLocale ("SI") : "<strong>".tIdiomaLocale ("NO"). "</strong>");
        case "SIno":
            return ( $campo ? "<strong>".tIdiomaLocale ("SI") . "</strong>": tIdiomaLocale ("NO"));

        case "sino":
            $campo = ( $campo ? "SI": "NO");
            return tIdiomaLocale ($campo ) ;


        case "si" :
            $campo = ( $campo ? "SI": "");
            return tIdiomaLocale ($campo ) ;

        case "no" :
            $campo = ( $campo ? "" : "NO");
            return tIdiomaLocale ($campo ) ;

        case "funcioncampo" :
            return call_user_func( $aParametros[1], $campo );

        case "funcion" :
        case "funcionregistro" :
            return  call_user_func( $aParametros[1], $fila );

        case "imagen":
        case "irudia":
            if ( $campo ) {
                $cTemp      = ( isset($aParametros[1]) ? $aParametros[1] ."/"  : "") . $campo;
                $atributos  = "";
                if ( isset($aCampo["ancho"]) ) {
                    $atributos  = " width='{$aCampo[ancho]}'";
                }
                if ( isset($aCampo["alto"]) ) {
                    $atributos  .= " height='{$aCampo[alto]}'";
                }
                return "<img src='$cTemp' alt='$campo'$atributos>";
            } else {
                return "";
            }

        case "fecha":
            return fecha_mysql_php ( $campo, tIdiomaLocale ("fecha") );

        default:
            return $campo;
    }
    return $campo;

}


function ddlib_formatoCampo( $aCelda ) {
   if ( isset( $aCelda["clase"]) ) {
      return $aCelda["clase"];
   }
   if ( isset( $aCelda["formato"]) ) {
      return $aCelda["formato"];
   }

   $aParametros = explode(" ",$aCelda["tipo"]);
	return $aParametros[0];
}


function _ddlib_consulta_cabecera( $aTabla, $querystring, $lHayOpciones, $cPaginacion, $order, $orderBy ){

   /* Dibujar las cabecera de la tabla */
   $nCont = 0;
   $lista = "";
   foreach ( $aTabla as  $aCelda) {
      if ( si_es_key($aCelda,"acceso")===false || isset($aCelda["filaextra"]) ) {
         $nCont++;
         continue;
      }

      $clase  = ddlib_formatoCampo( $aCelda );
      $orden  = ( $order == $nCont ? " ordenactual" : "");
      $lista .= "\n<th class='$clase$orden'>";

      $cNodo = remove_querystring_var ( "?". $querystring, "order");
   	  $cNodo = remove_querystring_var ( $cNodo, "orderby");
      if ( isset($aCelda["order"] )){
   	   	if ($nCont == $order) {
   	   		$lista .= "<a href='$cNodo&order=$nCont&orderby=".  ( $orderBy=="ASC" ? "DESC" : "ASC") .  "' class='orden$orderBy'>{$aCelda['cabecera']}</a>";
   	   	} else {
   	   		$lista .=  "<a href='$cNodo&order=$nCont&orderby=ASC' class='ordenASC'>{$aCelda['cabecera']}</a>";
   	   	}
      } else {
   	   	$lista .=  $aCelda["cabecera"];
      }
   	  $lista .=  "</th>";
   	  $nCont++;
   }

   if ( $lHayOpciones ) {
      $lista .= "\n  <th>" . t("Opciones") . "</th>";
      $nCont++;
   }

   $cResul = "<thead>\n <tr>$lista</tr>\n</thead>\n";
   if ( $cPaginacion ){
      $cResul .=  "<tfoot>\n <tr><td colspan='$nCont' >$cPaginacion</td></tr>\n</tfoot>";
   }

   return $cResul;

}


function _ddlib_consulta_cuerpo ( $aTabla, $cSQL, $aOpciones ){

   // calcular las funciones despues
   $after= false;
	foreach ( $aTabla as $aCelda ){
		if ( isset($aCelda["filaextra"]) ){
			$after[]= $aCelda["filaextra"];
		}
	}


  if ( $lHayOpciones = isset( $aOpciones["opciones"])){
     $opcionID          = por_defecto ( si_es_key($aOpciones,"opcionesId"), si_es_key($aOpciones,"opcionesID"), $aTabla[0]["campo"] );
     $separadorOpciones = por_defecto ( si_es_key($aOpciones,"opcionesSeparador"),"|");
  }

  $cResul ="\n<tbody>";
  $lPar = true;
  $rsConsulta= mysql_query( $cSQL);
  while ( $fila = mysql_fetch_array( $rsConsulta) ) {
	   $cResul .=  "\n <tr" . ($lPar ? "": " class='impar' " ). ">";

	   // dibujamos cada celda según su tipo //

	   foreach ( $aTabla as $aCelda) {
	      if ( si_es_key($aCelda,"acceso") === false ||
	           isset($aCelda["filaextra"]) ) {
	         continue;
	      }


         // calcular la visualizarión del campo
         $campo      = ddlib_obtenerCampo( $aCelda, $fila  );
	   	$visualizar = ddlib_visualizarCampo( $aCelda, $campo , $fila ) ;
	   	$formato    = ddlib_formatoCampo( $aCelda );

		   $classFormato = ( $formato ? " class='$formato'": '');

		   if ( isset( $aCelda["styling"])){
		      $cResul .= "<td$classFormato>" . call_user_func( $aCelda["styling"], $visualizar ) . "</td>";
		   } else {
		      $cResul .= "<td$classFormato>$visualizar</td>";
		   }
	   }

	   /* ahorita dibujamos las opciones */
	   if ( $lHayOpciones ) {
   	   $cResul .="<td class='opciones'>".
   	          	 str_ireplace( "%id%", $fila[$opcionID],
        	                is_array ( $aOpciones["opciones"]) ? implode ( $separadorOpciones,$aOpciones["opciones"])   :$aOpciones["opciones"]).
         	        "</td>";
	   }
   	$cResul .= "</tr>\n";
	   $lPar = ! $lPar;
	   if ( $after) {
	   	foreach ($after as $llamar) {
	   		$cResul .= call_user_func ($llamar, $fila);
	   	}
	   }
	}
   $cResul .= "\n</tbody>";

   mysql_free_result($rsConsulta);
   return $cResul;

}

/**
 * Consulta de varios registros en forma tabular
 * @param $aCampos   definiciones DD
 * @param $cSQL      consulta SQL sin LIMIT
 * @param $aOpciones menu para cada registro
 */



function ddlib_consulta ( $aCampos,  $cSQL, $aOpciones=""  ){
   /*
   $aCampos array de campos
     campos
        acceso
        cabecera
        order
        campo
        tipo
        formato ( si no existe, se usa tipo ).
        styling
   $cSQL
     una SQL completa, o desde FROM, o nombre  de la tabla
   $apciones
     titulo
     tituloSinHTML
     menu
     opciones (array o string),
     		requiere opcionesId
     		opcional opcionesSeparador
     queryString
     paginacion
     registrosPorPagina 20
     paginas 10.
     tablaClase
     tablaID
     order   $aEstado["order"]
     orderby  $aEstado["orderby"]

   */

    // titulo
    $cResul  = "";
    if ( isset($aOpciones["titulo"]) ) {
        $cResul .=  ( si_es_key($aOpciones,"tituloSinHTML") ? $aOpciones["titulo"] : "<h2><span>{$aOpciones['titulo']}</span></h2>\n" );
    }

    // incluir el menu de Opciones
    if ( isset( $aOpciones["menu"] ) ) {
        $aMenu = $aOpciones["menu"];
        $lista = "";
        if ( is_array($aMenu) ) {
            foreach ( $aMenu as $unaOpcion ) {
                $lista .= "<li>$unaOpcion</li>\n" ;
            }
        } else {
            $lista .= "<li>$aMenu</li>\n" ;
        }
        $cResul .= "<div class='menuConsulta'>\n<ul>\n$lista</ul></div>";
    }

    // queryString
    $querystring= por_defecto ($aOpciones["querystring"], $_SERVER["QUERY_STRING"]);

    // Calcular cabecera las cabecera de la tabla
    $lHayOpciones = isset($aOpciones["opciones"]);


    // Ordenar los campos
    global $aEstado;
    $order   = por_defecto ( si_es_key($aEstado,"order")  , si_es_key($aOpciones,"order"), 0 );
    $orderby = por_defecto ( si_es_key($aEstado,"orderby"), si_es_key($aOpciones,"orderby"), "ASC");
    // completar SELECT
    if ( stripos($cSQL,"SELECT ")!== 0 ){
        foreach ( $aCampos as $dd ){
            if (isset($dd["campo"])) {
                if ( preg_match("/^(funcion |serializ[ea]|tcampo |funcionget)/i",$dd["campo"])){
                    $campos= array ("*");
                    break;
                } else {
                    $campos[]= $dd["campo"];
                }
            } elseif ( isset($dd["campos"])) {
                $campos= array_merge($campos,$dd["campos"]);
            }
        }
        // TODO REVISAR.
        $cSQL = "SELECT " . implode(",",$campos) . (stripos($cSQL,"FROM ")===0 ?  " " . $cSQL : " FROM $cSQL");
    }

    if ( stripos ( $cSQL, "order by") ){// NO PUEDE SER cero
    	  //if (preg_match ("#order by (.*)( LIMIT)?#uim", $cSQL, $aTemp )) Esta seria la buena
  		  if (preg_match ("#order by ([^ ,]*)#uim", $cSQL, $aTemp )){
  		     $leyenda= $aTemp[1] ;
  		  }
    } elseif ( isset($aCampos[$order]["order"])) {
        $cTempOrder = $aCampos[$order]["order"];
        if ( $cTempOrder == 1 ){
           $cTempOrder = $aCampos[$order]["campo"];
        }
        $cSQL .= sql_order( $cTempOrder , ( $orderby == "ASC" ? " ASC": " DESC"));
        $leyenda = $cTempOrder; // @TODO ordenes de varios campos
    }

    // Paginación.
    if ( !isset($aOpciones['paginacion']) || !$aOpciones['paginacion']) {
        $pags        = por_defecto ( si_es_key($aOpciones,"paginas"), 10);
        $regs        = por_defecto ( si_es_key($aOpciones,"registrosPorPagina"), 20);
        $aPaginacion = paginacion($cSQL, $leyenda, $regs, $pags, tIdiomaLocale("paginacion"), $querystring );
    } else {
        $aPaginacion = array("","",$cSQL,"");
    }

    // calcular la cabecera (pie incluido ) y luego el cuerpo.
    $atributosTabla =  "class='" . por_defecto ( si_es_key($aOpciones,"tablaClase") , "consulta") . "'" ;
    if ( isset( $aOpciones["tablaID"]) ) {
        $atributosTabla .= " id='{$aOpciones[tablaID]}";
    }

    $cResul .="\n<table $atributosTabla>\n";
    $cResul .= _ddlib_consulta_cabecera ( $aCampos, $querystring, $lHayOpciones, $aPaginacion[3], $order, $orderby);
    $cResul .= _ddlib_consulta_cuerpo   ( $aCampos, $aPaginacion[2], $aOpciones );
    $cResul .="\n</table>\n";
    return $cResul ;

}


function _ddlib_script (){
$aTemp = explode ( "/", $_SERVER["PHP_SELF"] );
if ( count($aTemp) > 0 )
	return $aTemp[ count( $aTemp)-1];
else
	return $_SERVER["PHP_SELF"] ;
}


/**
 * Tabla para editar/añdir un registro completo
 */

function _ddlib_option_adicionales ( $dd, $campo ){
   $cRet = "";

   if ( isset($dd["valoresAdicionales"] )) {
   	foreach ( $dd["valoresAdicionales"] as $value => $opcion ) {
   	   $atributos = ($value==$campo ? " selected='selected' ": "" );
         if ( substr($opcion,0,3)== "---") {
				$atributos .=  " disabled='disabled' ";
			}

			$cRet.= "<option value='$value'$atributos>$opcion</option>\n";
		}
   }

   return $cRet;
}


function _ddlib_atributos( &$dd, $id ="", $defecto=""  ){

  $clase = $dd["clase"] or $clase= $defecto["clase"];
  $id1   = $dd["id"]    or $id1  = $defecto["id"] or $id1=$id;

  $atributos  = ( $clase   ? " class='$clase'" :"");
  if ( $id1 ) {
     $atributos .= " id='$id1'";
  }
  if ( $dd["atributos"] ){
     $atributos .=  " ". $dd["atributos"];
  }

  if ( $dd['campo'] ){
     $atributos .= " name='{$dd[campo]}'";
  }
  return $atributos;
}


/*
 * ddlib_editarCampo
 *
 */

function ddlib_editarCampo ( &$dd, &$aDatos, $id ) {

   $aParametros = explode  (" ", $dd["tipo"] );
   $tipo        = strtolower($aParametros[0]);
	$campo       = ddlib_obtenerCampo ( &$dd, $aDatos);

   // atributos por defecto
   $defectos= array (
      "adjunto"     => array ("clase"=>"boton"),
      "infofuncion" => array ("clase"=>"campo-informativo"),
      "readonly"    => array ("clase"=>"campo-informativo"),
		"info"        => array ("clase"=>"campo-informativo"));

   $atributos = _ddlib_atributos ( $dd, $id, $defectos[$tipo] );

   switch ( $tipo ){
       case "hidden":
       case "fijo" :
       case "htmldespues":
       case "separadortabla":
       case "separador":
           return "";

       case "adjunto":
           if ( $dd["maximo"] ) {
               $maximo = min ( $dd["maximo"] , ini_get("upload_max_filesize"));
           } else {
               $maximo = ini_get("upload_max_filesize");
           }

           $adicional = "<span class='tamanno-max'>(MAX: $maximo bytes)</span>" ;
           if ( $campo ) {
               $visualizar  = "<a href='{$aParametros[1]}/$campo'>" ;
               $visualizar .= corta($campo,50) . "</a><br>";
               $visualizar .= t("Cambiar fichero adjunto: ") . "<input type='file' $atributos file='$maximo'/>$adicional";
               $visualizar .= "<br>" . ("Borrar adjunto: ")  . "<input type='checkbox' name='{$dd[campo]}_BORRAR' value='1' />";
           } else {
               $visualizar  = t("Adjuntar nuevo archivo: "). "<input type='file' $atributos />$adicional";
           }
           return $visualizar;

       case "imagen":
       case "irudia":
            if ( $dd["maximo"] ) {
               $maximo = min ( $dd["maximo"] , ini_get("upload_max_filesize"));
            } else {
               $maximo = ini_get("upload_max_filesize");
            }
            $adicional = "<span class='tamanno-max'>(MAX: $maximo bytes)</span>" ;
       
           if ( $campo ) {
               $visualizar = "<img src='./{$aParametros[1]}/$campo' class='irudia' /><br/>";
               $visualizar.=  t("Cambiar  imagen: "). "<input type='file' $atributos />\n</br/>";
               $visualizar.=  t("Borrar imagen: "). "<input type='checkbox' name='{$dd[campo]}_BORRAR' value='1' />";
           } else {
               $visualizar.= t("Sin imagen") ."<br/>";
               $visualizar.= t("Nueva  imagen: ").  "<input type='file' $atributos file='$maximo'/>$adicional";
           }
           return $visualizar;

       // listas
       case "lista":
       case "listavalores":
       case "listasql":
       case "listafuncion":

           $visualizar = "<select $atributos>\n";
           //@TODO $visualizar .= _ddlib_option_adicionales ( $dd, $campo );

           switch ( $tipo ) {
               case "listasql":
                   $lista = mysql_mlistaClavesSQL( substr($dd["tipo"], 8))  ;
                   break;
               case "lista":
                   $lista= $dd["lista"];
                   break;
               case "listavalores":
                   foreach ( $dd["lista"] as $valor) {
                      $lista[$valor] = $valor;
                   };
                   break;
               case "listafuncion":
                   $lista= call_user_func($aParametros[1]);
           }

           switch ( $dd["formatolista"] ) {
               case "checkbox":
                   // @TODO no funciona CHECKBOX
                   foreach ( $lista as $value=>$opcion ) {
                       $selected   = ($value==$campo ? " checked='checked' ": "" );
                       $visualizar.= "<input $atributos type='checkbox' value='$value'$selected/>$opcion\n";
                   }

               case "radio":
                   // @TODO no funciona RADIO
                   foreach ( $lista as $value=>$opcion ) {
                       $selected   = ($value==$campo ? " checked='checked' ": "" );
                       $visualizar.= "<input $atributo type='radio' value='$value'$selected/>$opcion\n";
                   }

               default:
                   $visualizar = "<select $atributos>\n" .
                                  _ddlib_option_adicionales ( $dd, $campo );
                   foreach ( $lista as $value=>$opcion ) {

                       $selected   = ($value==$campo ? " selected='selected' ": "" );
                       $visualizar.= "<option value='$value'$selected>$opcion</option>\n";
                   }
                   $visualizar .= "</select>\n";
           }
           return $visualizar;

       case "checkbox":
           $checked     = ( $campo ? " checked='checked' ": "") ;
           return "<input type='checkbox' $atributos $checked value='1' />";

		 case "checkboxes":
		     if ( isset ( $dd["campos"]) && isset( $dd["etiquetas"] ) ){
		     		$nCont=1;
		     		foreach ( $dd["campos"] as $tcampo=>$tipo ){
		     			$atributos = ( $aDatos[$tcampo] ? " checked='checked' ": "") ;
		     			$atributos .= " name='$tcampo' id='$id-$nCont' {$dd[atributos]} value='1'";
		     			$cRet .= "<input type='checkbox' $atributos> ";
		     			$cRet .= "<label for='$id-$nCont'>".  $dd["etiquetas"][$tcampo]. "</label> \n";
		     			$nCont++;
		     		}
		     		return $cRet;
		     }

           return "";


       // funciones informativas
       case "infofijo":
       case "infofuncion":
           $campo = ($tipo=="infofijo" ? substr( $dd["tipo"],9 ) : call_user_func($aParametros[1], $aDatos));
       case "readonly":
       case "info":
           return "<input type='text' $atributos value='$campo' disabled='disabled' >";
           break;

       case "htmlfijo":
           return substr ($dd["tipo"],9);

		 case "html":
       case "htmlfuncion":
           return call_user_func ( $aParametros[1] , $aDatos);

       // contraseña
       case "nuevopassword":
       case "verificapassword":
           $size =  $aParametros[1] or $size=40;
           $max  =  $aParametros[2] or $max= $size;
           return "<input type='password' value='' $atributos size='$size' maxlength='$max'>";

       // textos y cadenas
       case "texto":
           $rows = $aParametros[2] or $rows=8;
           $cols = $aParametros[1] or $cols=40;
           return "<textarea $atributos rows='$rows' cols='$cols'>$campo</textarea>";

       case "cadena":
           $size =  $aParametros[1] or $size=40;
           $max  =  $aParametros[2] or $max=$size;
           return "<input type='text' $atributos value='$campo' size='$size' maxlenght='$max' />";
		 // los campos fecha se generan mediante el atributo.

       default:
           return "<input type='text' $atributos value='$campo'/>";
   }
   return $visualizar;
}


function ddlib_edicion ( $aTabla, $cSQL="", $aOpciones  ){
/*

 aTabla
    acceso
    defecto
    verifica
    tipo
       separadortabla
       separador
       hidden
       fijo
       htmldespues

       adjunto directorio
	    imagen directorio

		 lista   ->[lista]
		 listasql SQL
		 listafuncion funcion_a_llamar
						$formatolista
       checkbox
       checkboxes
       		utiliza el campo etiquetas

       infofijo información
       infofuncion funcion
       readonly
		 info

       htmlfijo HTML-bruto
       htmlfuncion funcion

		 nuevopassword size max

		 textos cols=40 fila=8
		 cadena size=40 max=(size or 40)

		 -->sino un input=text
	 adicional
	 atributos
	 id
	 clase

 cSQL
    si existe, es una modificación
    sino una inserción (registro nuevo

 aOpciones
    titulo
    tituloSinHTML
    metodo
    enviar
    hidden
    tablaID
    tablaClase
    prefijoId (campo)
    volver
 */


$prefijoID = si_es_key( $aOpciones, "prefijoID", "campo" ) . "_";

// titulo
$cResul  = "";
if ( isset($aOpciones["titulo"]) ) {
   $cResul .=  ( $aOpciones["tituloSinHTML"]==true ? $aOpciones["titulo"] : "<h2><span>{$aOpciones[titulo]}</span></h2>\n" );
}

// Se usará mas adelante
$cEnviar = por_defecto ( $aOpciones['enviar'], t("Enviar") );
$cMetodo = por_defecto ( $aOpciones["metodo"], "post" );
$cEnctype= ( $cMetodo =="post" ?  'multipart/form-data' : 'application/x-www-form-urlencoded' );
$cResul .= "\n<form name='editar' method='$cMetodo' action='". _ddlib_script() . "'  enctype='$cEnctype'  >\n";

$cHidden  = "";
$aAfter   = array();
if ( isset($aOpciones["hidden"]) ) {
	foreach ( $aOpciones["hidden"] as $name => $value )  {
		$cHidden .= "<input type='hidden' name='$name' value='$value' />\n";
	}
}

if ( isset($aOpciones["siguienteAccion"]) ) {
   $temp = array ( "c", "a", "i", "order", "PaginaActual") ;
	foreach ( $aOpciones["siguienteAccion"] as $i=>$value )  {
		$cHidden .= "<input type='hidden' name='". $temp[$i] . "' value='$value' />\n";
	}
}


// se revisan los campos para ver ocultos, obligatorios y demás
foreach ( $aTabla as $k=>$dd) {
	list($tipo) = explode ( " ", $dd["tipo"] );
	$tipo = strtolower($tipo);
   // campos hidden
	if ( $tipo=="hidden" ) {
		$cHidden .= sprintf( "<input type='hidden' value='%s' name='%s'/>",
		                     $dd["value"] || $aDatos[$dd["campo"]],
		                     $dd['campo'] ) ;
	}
	if ( $tipo=="htmldespues" ) {
      $aAfter[] = por_defecto ($dd["funcion"], substr($dd["tipo"],12) );
	}
   // Buscamos campos obligatorios
   if (  $aCelda["acceso"] !== false and $aCelda["verifica"]=="no_vacio" ) {
      $lObligatorio = true ;
   }

}

if ( $lObligatorio ){
   $cResul .= "<div class='obligatorio'>*" . t("beherrezkoa") . "</div>";
}

// calcular la cabecera (pie incluido ) y luego el cuerpo.
$atributosTabla =  "class='" . si_es_key( $aOpciones, "tablaClase", "edicion") . "'" ;
$ultimaTabla="";

// obtener los valores
if ( $cSQL != ""  ) {
	/* en un modificación los valores se obtienen por una consulta SQL */
	$aDatos = mysql_query_registro($cSQL);
} else {
	/* en una adición de obtienen con los valores por defecto */
	$aDatos = array();
	foreach ( $aTabla as $dd ) {
            if ( isset($dd["campo"])) {
                $aDatos[ $dd["campo"] ]= (isset($dd["defecto"])? $dd["defecto"] : "" );
            } else {
                if ( is_array( $dd["campos"]) && is_array ($dd["defectos"])){
                    foreach ( $dd["campos"] as $campo=>$tipo){
                        $aDatos[ $campo ]=$dd["defectos"]["campo"];
                    }
                }
            }

	}
}


// empieza el bucle para dibujar los valores
$nCont = 1;
$nTabla= 1;
if ( $aDatos ) {
   $cTable = "";

	foreach ( $aTabla as $k=>$dd ){

	   if ( $dd["acceso"]=== false ) {
	      continue;
	   }

      // obtener el tipo de campo
		$aParametros = explode  (" ", $dd["tipo"] );
		$cTipo       = strtolower($aParametros[0]);

      switch ( $cTipo ) {
         // casos especiales
         case "htmldespues":
         case "hidden":
         case "fijo" :
            break;

         // separador
         case "separadortabla":
            if ( $ultimaTabla ){
               $cTabla .= "\n</table>";
            }
            $ultimaTabla= por_defecto ( $aOpciones["tablaID"], "tabla-$nCont");
            $nTabla++;
            $cTabla .= "\n<table $atributosTabla id='$ultimaTabla'>\n";

			case "separador":
            if ( !$ultimaTabla ){
                 $ultimaTabla= por_defecto ( $aOpciones["tablaID"], "tabla-$nCont");
                 $cTabla .= "\n<table $atributosTabla id='$ultimaTabla'>\n";
            }

			   $atributos   = _ddlib_atributos   ( $dd, "$prefijoID$nCont"  ) ;
			   $cTabla .= "\n<tr class='separador'>\n";
            $cTabla .= "<th colspan='2' $atributos>{$dd[cabecera]}</th></tr>\n";
				break;

         // adjuntos e imagenes
			default:
			   if ( $visualizar = ddlib_editarCampo ( $dd, $aDatos, "$prefijoID$nCont" )){

               if ( !$ultimaTabla ){
                  $ultimaTabla= por_defecto ( $aOpciones["tablaID"], "tabla-$nCont");
                  $cTabla .= "\n<table $atributosTabla id='$ultimaTabla'>\n";
               }

      			$obligatorio = ( $dd["verifica"]=="no_vacio" ? " class='obligatorio'" : "" );
      			$adicional   = ( $dd["adicional"] ? "<span class='adicional'>{$dd[adicional]}</span>" : "");


					if ( isset( $dd["campos"])  ){ // los campos multiples no tienen etiqueta
						$cTabla    .= "\n <tr><th $obligatorio>{$dd[cabecera]}</th>";
					}else {
               	$cTabla    .= "\n <tr><th><label for='$prefijoID$nCont' $obligatorio>{$dd[cabecera]}</label></th>";
               }

      			$cTabla    .= "\n     <td>$visualizar $adicional</td></tr>";

           }
		}
		$nCont++;

	} // fin del bucle foreach del dd

	// botones de Enviar y Volver.
	$cTabla .= "<tr><td class='botones-enviar' colspan='2'><input type='submit' value='{$cEnviar}' class='boton' />";
	if ( $aOpciones["volver"]===true ){
	   $cTabla .= "<a href='" . strtr( $_SERVER["HTTP_REFERER"], array("&"=>"&amp;") ) ."' class='boton'>Volver</a>";
	}

	$cTabla .= "</td></tr>\n";

}

$cResul .= $cTabla .
           "</table>\n" .
           $cHidden . "\n" .
           "</form>\n";

foreach ( $aAfter as $funcion) {
   $cResul .= call_user_func($funcion, $aDatos );
}

return $cResul;
}


/*
 @TODO revisado hasta aquí.
 */



/*
 * Verificar un campo
 */

function dd_verificaCampo( $aDatos ){
   if ( !isset($aDatos["verifica"]) ) {
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

function ddlib_salvar( $dd, $cTabla, $cWhere="",$aOpciones= NULL ){
	return ddlib_guardar ( $cTabla, $cWhere, $dd, $aOpciones);
}


function ddlib_guardar ( $cTabla, $cWhere, $aEdicion, $aOpciones = NULL ){
   $cError= "";
   $aRet  = "";
   $cNuevo= ($cWhere=="" ? "INSERT" : "UPDATE");

   //  Primero verificamos las condiciones de cada campo
   foreach ( $aEdicion as $aDatos ) {
	   $cError .= ( isset($aDatos["verifica"]) ? ddlib_verificaCampo ($aDatos) : "");
	}

   // Revisar los errores
   if ( $cError !="") {
      if ( isset( $aOpciones["errorverificacion"])  ){
			$cError =  $aOpciones["errorverificacion"] . $cError;
      }
      return mensajeHTML ( $cError, "error " . ($cNuevo ? "añadir" : "guardar") ) ;
   }

   $aSQL = sql_crear( $cNuevo, $cTabla, $cWhere );

   // al terminar la verificación revisar los errores
   foreach ( $aEdicion as $aDatos ) {
	   if ( isset($aDatos["acceso"]) and !$aDatos["acceso"]){
        	continue;
      }

   	$aParametros = explode (" ", $aDatos["tipo"]);
   	switch ( strtolower($aParametros[0] )){
   		case "hidden":
   		case "info":
   		case "htmldespues":
   		case "infofuncion":
   		case "separador":
   		case "separadortabla":
   		case "verificapassword":
   		case "readonly":
   			break;

		case "adjunto":
   		case "imagen" :
   		case "irudia" :
            if ( $_REQUEST[ $aDatos["campo"]."_BORRAR"]=='1' || $_REQUEST[ $aDatos["campo"]."_EZABATU"]=='1' ){
               sql_add($aSQL,  $aDatos["campo"], "", "cadena");
            } else {
				   $lPendiente = true;
			}
   			break;

   		case "nuevopassword":
            if ( $_REQUEST[$aDatos["campo"]] !="" ){
                if ( isset($aParametros[3]) and $aParametros[3]=="md5")
                    sql_add( $aSQL, $aDatos["campo"], md5($_REQUEST[$aDatos["campo"]]), "cadena");
                elseif ( isset($aParametros[3]) and $aParametros[3]=="sha1")
                    sql_add( $aSQL, $aDatos["campo"], sha1($_REQUEST[$aDatos["campo"]]), "cadena");
                else
                    sql_add( $aSQL, $aDatos["campo"], $_REQUEST[$aDatos["campo"]], "cadena");
            }
            break;

         case "fijo":
   			sql_add($aSQL,  $aDatos["campo"], $aDatos["valor"], $aParametros[1]);
            break;

         case "listavalores":
   			sql_add_request( $aSQL,  $aDatos["campo"], por_defecto($aParametros[2],"cadena") );
   			break;

   		case "listafuncion":
   			sql_add_request( $aSQL,  $aDatos["campo"], $aParametros[2]);
   			break;

   		default:

   		   if ( isset($aDatos["campos"]) ) {
   		      foreach ($aDatos["campos"] as $tcampo => $tipo){
                  sql_add_request ( $aSQL,  $tcampo, $tipo);
   		      }
   		   } elseif (isset($aDatos["campo"]))  {
   		      // el campo puede ser de diversos tipos.
   		      list($tipoCampo, $tcampo, $tmas) = explode (" ",$aDatos["campo"]);
   		      switch ( strtolower($aDatos["campo"]) ) {
   		         case "tcampo":
   		         case "funcionget":
   		         case "funcion":
   		             // estos son campos de consulta no se pueden escribir
   		             break;

   		         case "serialize":
   		         case "serializa":
   		            sql_add_request ( $aSQL,  serialize($aDatos["campo"]), $tcampo);
   		            break;

                  default:
      		         sql_add_request ( $aSQL,  $aDatos["campo"],$aParametros[0]);
   		      }
            }

   	}
   }

   // si hemos recogido campos, salvamos (puede ser un caso de solo imágenes )
	if ( sql_esvacia($aSQL) ) {
      $lResul = true ;
	} else {
	   $lResul = mysql_query( sql_sql ( $aSQL ) );
   }

	if ( $lResul and $lPendiente ){
	   ddlib_guardarAdjuntos( $cTabla, $cWhere, $aEdicion);
	}


   // Devolver resultado
   if ( $lResul) {
	   	$lResul = ( $aOpciones["ok"] ?  "<div class='guardarOk'   >{$aOpciones[ok]}</div>": true );
	} else {
	  	   $lResul = ( $aOpciones["ok"] ?  "<div class='guardarError'>{$aOpciones[error]}</div>" : false );
	}

   return $lResul ;
}



function ddlib_guardarAdjuntos( $cTabla, $cWhere, $aEdicion) {
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

			if ( ($cTipo == "adjunto" ||  $cTipo == "imagen" || $cTipo == "irudia" )
			     and isset($_FILES[$cFile]) and $_FILES[$cFile]["name"]
			     and isset($aParametros[1]) ){
				if ( isset($aParametros[2]) ) {
				   // mascara para guardar el nombre del nuevo fichero
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
					if (  $cTipo == "imagen" || $cTipo == "irudia") {
   					if ( isset($aDatos["thumbnail"]) ){
   					   $aThumb = explode ( " ", $aDatos["thumbnail"] );
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
                   if ( isset($aDatos["ajustar"]) ){
   					   $aThumb[0]= (int) $aDatos["ajustar"];
   					   mImageResize( $aParametros[1] . "/" . $cNuevoNombre, $aParametros[1] . "/" . $cNuevoNombre, "KEEP_PROPORTIONS_ON_BIGGEST", $aThumb[0]);
   					 }
					}
				}
			}
   	}
   	// grabamos si es que hemos podido mover
   	if ( !sql_esvacia($aSQL) ) {
     		mysql_query( sql_sql($aSQL) );
     	}

}


/**
* funciones para mostrar resultados
*/

function dd1_mostrarActivo ( $nValor) {
   return  ( $nValor==1 ? "Bai": "");
}


function ddlib_tablaEdicionConsulta ( $cTitulo, $aTabla,  $cSQL, $aHidden=NULL, $nMax=5 ){
   static $nId;
   global $aEstado;

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


