<?php

/**
 *
 * Libreria maquetador
 * @author    Roger
 * @copyright Saregune
 * @license   GPL
 * @version   28-Junio-2010
 *
 * 2010-06-29 a maquetador_ajax
 * 2010-06-29 c maquetador_carga_modulos.
 * 2010-06-28 maquetador_enlace: ahora el primer parámetro puede ser un array.
 * 2010-06-28 maquetador_estado
 * 2010-05-19 maquetador_enlace con nuevo parámetro
 * 2010-05-11 + maquetador_buscador
 * 2010-04-30 + maquetador_esHome
 * 2010-02-22 c ahora la variable global aEstado, tambien guarda los modulos.
                solo es necesario llamar a maquetador_genera().
 * 2010-02-26 + maquetador_script
 * 2010-02-25 + las plantillas de include permiten varios niveles y subcarpetas
 * 2010-02-10 a maquetador_evaluar_estado
 * 2008-02-28 a maquetador_enlace
 * 2007-01-17 + Añadida los include de plantillas (un solo nivel)
 */


/*
 * guarda y genera los script especiales.
 * Uso
 *    maquetador_script ( "añadir", "meta", "keys", "a, b,c");
 *    maquetador_script ( "añadir", "script", "googleglub", "http://www.google.com/glub.js");
 *    maquetador_script ( "genera", "todos" );
 */

function maquetador_script( $accion, $clave, $valor="", $adicional="media") {
    static $aDatos;
    if ( $accion=="añadir" ) {
    // primero añadimos las acciones,clave y valor
        $aDatos[$clave][$valor]= $adicional;
    } elseif ( $accion == "genera" ) {
    // ahora generamos cada acción, mediante una plantilla
    // para crear las etiquetas pertinentes.
        $aTemplates = array (
            "js"     =>'<script type="text/javascript">%2$s</script>\n',
            "script" =>'<script src="%2$s"  type="text/javascript"></script>\n',
            "style"  =>'<link rel="stylesheet" href="%s" type="text/css" media="%s">\n',
            "meta"   =>'<meta name="%s" content="%s">\n' );

        if  ( $clave=="todos") {
            foreach ( $aTemplates as $k=>$v ) {
                $cRet .= maquetador_script("genera",$k );
            }
            return $cRet;
        } elseif ( isset($aTemplates[$clave]) && isset($aDatos[$clave]) ) {
            $plantilla = $aTemplates[$clave];
            foreach ( $aDatos[$clave] as $k=>$v ) {
                $cRet .= sprintf ( $plantilla, $k, $v );
            }
            return $cRet;
        }
        return "";
    }

}



/*
 * pre-carga los modulos  
 */

function maquetador_precarga_modulos( $path="./modulos") {
    global $aEstado;
    $nlong = mb_strlen($path)+1;
    foreach ( glob ("$path/*.php") as $modulo ) {        
        if ( mb_substr($modulo,-9) =="_load.php" )  {
            $aEstado["modulos"][mb_substr($modulo,$nlong,-9)]= false;
            include_once ( $modulo );
        } else {
            $aEstado["modulos"][mb_substr($modulo,$nlong,-4)]= $modulo;
        }
    }
}


/*
 * carga todos los modulso que no se hayan cargado
 *  
 */
function maquetador_carga_modulos() {
    global $aEstado;
    if ( is_array( $aEstado["modulos"]) ){
	    foreach ( $aEstado["modulos"] as $cModulo=>$nombreReal  ) {
	        if ( $nombreReal ) {	             
	            include_once ( $nombreReal ); //se puede cargar sin if ya que es include_once
	            $aEstado["modulos"][$cModulo] = false;
	        }	        
	    }	    
	    return true;
	 }   
	 return false; 

}

/*
 * Genera la maqueta final. Lee la plantilla y va insertando las 
 * marcas.  
 */

function maquetador_genera($plantilla, $controladorDefecto=false, $accionDefecto=false) {
    global $aEstado ;
    
    $pendientes = false;

	 // precarga de modulos
	 if ( !is_array($aEstado) ){         
	    maquetador_evaluar_estado($controladorDefecto, $accionDefecto);	 
	 }
	 
	 if ( !isset($aEstado["modulos"]) ){
	    maquetador_precarga_modulos();
	 }

    // leer la plantilla
    if ( !file_exists($plantilla) ) {
      echo t("No exista la plantilla: [$plantilla]");
      return false;    
    }
    $html     = maquetador_insertar_include ( $plantilla );       
    $aGenerar = maquetador_extraer_marcas ( $html );

    foreach ( $aGenerar as $marca=>$contenido ) {
        $aDatos = maquetador_extrae_modulo ( $marca );

        // averiguar el modulo
        if ( $aDatos["modulo"] == "contenido") {
            $modulo          = $aEstado["controlador"];
            $aDatos["accion"]= ( $aDatos["accion"]=='' ? $aEstado["accion"] : $aDatos['accion']);
            $aDatos["id"]    = ( $aDatos["id"]    =='' ? $aEstado["id"]     : $aDatos['id']);
        } elseif ($aDatos["modulo"]=="maquetador") {
            $pendientes[$marca] = $aDatos["accion"];
            continue;
        } else {
            $modulo = $aDatos["modulo"];
        }

        // comprobamos que el modulo es correcto
        if ( $modulo == "t") {
            $aGenerar[$marca] = t($aDatos["accion"]);
        } else {
        // ver si es un modulo
            if ( $modulo!="PUT" and $modulo!="PHP" and !isset( $aEstado["modulos"][$modulo]) ) {
                $aGenerar[$marca] = "controlador desconocido: $modulo";
            } else {
            // si inserta el modulo si cumple la condición
                if ( $aDatos['condicional']=="" or maquetador_evalua ( $aDatos['condicional'])) {
                // hay que mostrar el modulo
                    switch ( $modulo ) {
                        case "PUT":
                            $aGenerar[$marca] = $aDatos["accion"];
                            break;
                        case "PHP":
                            $aGenerar[$marca] = eval("return " . $aDatos["accion"]. ";" ) ;
                            break;

                        default:
                            if ( $aEstado["modulos"][$modulo]) {
                                include_once $aEstado["modulos"][$modulo];
                                $aEstado["modulos"][$modulo]= false;
                            }
                            $aGenerar[$marca] = $modulo( $aDatos["accion"], $aDatos["id"] ) ;
                    }
                }
            }
        }  // else T
    } // for

    if ( $pendientes ) {
        foreach ( $pendientes as $k=>$accion) {
            $aGenerar[$k] = maquetador_script("genera", $accion );
        }
    }
    
    // ahorita solo queda calcular e imprimir el resultado    
    echo strtr ( $html, $aGenerar );

}

/* 
 *
 */
 
function maquetador_ajax ( $modulo, $accion , $id ){
	global $aEstado;
   // hacemos la petición via ajax   
   maquetador_precarga_modulos();   
   
   if ( !isset($aEstado["modulos"][$modulo]) ) {
   	return "controlador desconocido";   	 
   }
   
   $file = $aEstado["modulos"][$modulo];
   if ($file) {
      include_once ( $file );
   }
   return call_user_func ( $modulo, $accion, $id );    
}


/*
 * funciones auxiliares para maquetar enlaces y formularios 
 *   
 */

function maquetador_buscador ( $controlador, $accion, $i='i') {   
   if ( $value= limpiaRequest ($i) ){   
      $value =  "value='$value'";
   }
    
   return   "<form>" . t("Buscar") .": <input name='$i' type='text' $value>" .
            "<input type='hidden' name='c' value='$controlador'>\n". 
            "<input type='hidden' name='a' value='$accion'>\n".
            "</form>";
}


function maquetador_superenlace( $texto, $opciones, $marcador="" , $adicional="") {
    $mGet = array();
    foreach ( $_REQUEST as $i=>$v) {
        $mGet[$i] = $v ;
    }

    $cRet =  "<a href='?";
    if ( is_array ( $opciones)) {
        foreach ( $opciones as $i=>$v ) {
            $mGet[$i] = $v ;
        }
    } elseif ( $opciones ) {
        $cRet .= "&amp;$opciones";
    }

    $amp="";
    foreach ( $mGet as $i=>$v ) {
        $cRet .= "$amp$i=$v";
        $amp = "&amp;";
    }

    $cRet .= "' $adicional >$texto</a>";

    if ( $marcador !='') {
        $cRet = cerrar_etiquetas( $marcador, $cRet);
    }
    return $cRet;
}



/*
 * Construir un enlace 
 */


function maquetador_enlace( $texto, $c="", $a="", $i="", $marcador="" , $adicional="", $paras="") {
    if ( is_array( $texto) ){
		     
       $tempPara      = ( is_array($texto['parametros']) ? 
       							mImplode( "%s=%s",$texto['parametros'], "&amp;" ):      
         					   $texto['parametros'] );
		 $tempAdicional = ( is_array($texto['adicional']) ? 
       							mImplode( "%s='%s' ",$texto['adicional'] ):      
         					   $texto['adicional'] );         					   						
    	 
    	 $cRet =  sprintf ("<a href='%s?%s%s%s%s'%s>%s</a>" ,
    	 			 ( isset($texto['pagina'])     ? "{$texto[pagina]}" : "" ),
    	          ( isset($texto['controlador'])? "c={$texto[controlador]}" : "" ),
					 ( isset($texto['accion'])     ? "&amp;a={$texto[accion]}" : "" ),    	              	       
    	          ( isset($texto['id'])         ? "&amp;i={$texto[id]}"     : "" ) ,
    	          ( isset($texto['parametros']) ? "&amp;$tempPara" : "" ), 
    	          ( isset($texto['adicional'])  ? " $tempAdicional" : "" ),
    	          ( isset($texto['texto'])      ? "{$texto[texto]}" : "" ) );  	          
	    if ( isset ($texto["etiqueta"]) ) {
	        $cRet = cerrar_etiquetas( $texto["etiqueta"], $cRet);
	    }
    
    } else {
	    $cRet =  "<a href='?c=$c&amp;a=$a" . ( $i!="" ? "&amp;i=$i" : "" ) . "&amp;$paras' $adicional >$texto</a>";
	    if ( $marcador !='') {
	        $cRet = cerrar_etiquetas( $marcador, $cRet);
	    }
    }
    return $cRet;
}


/*
 * Campos input necesarios para que un formulario se rediriga 
 */

function maquetador_form( $c, $a, $i="") {
    return "<input type='hidden' name='c' value='$c' />\n" .
        "<input type='hidden' name='a' value='$a' />\n" .
        ( $i=="" ? "" : "<input type='hidden' name='i' value='$i' />\n");
}


/*
 * array para datadriven
 * @deprecated 
 */
function maquetador_array( $c, $a, $i="" ) {
    $aTemp = array ( "c" =>$c, "a" => $a  );
    if ( $i!="") {
        $aTemp["i"] = $i ;
    }
    return $aTemp;
}



/*
 * resto son funciones internas
 *
 */



/*
 * evaluar el estado: decidir controlador, accion e id.
 * interna
 */

function maquetador_estado( $cual){
   global $aEstado;
   return $aEstado[$cual];
}

function maquetador_evaluar_estado( $controlador=false, $accion=false) {
    global $aEstado;

    // Cargar el estado o petición
    $aEstado = array (
        "controlador" => por_defecto($_REQUEST["c"], $controlador, $_GLOBALS["HOME_CONTROLADOR"]),
        "accion"      => por_defecto($_REQUEST["a"], $accion     , $_GLOBALS["HOME_ACCION"]),
        "id" 	      => $_REQUEST["i"],
        "pagina"      => $_REQUEST["p"],
        "order"       => $_REQUEST["order"],
        "orderby"     => $_REQUEST["orderby"],
        "esHome"      => $_REQUEST["c"]==""  );
}

/* 
 * Devuelve un array con todas las marcas de inserción (<%..%>) 
 * que contiene una cadena.
 * interna
 */

function maquetador_extraer_marcas( $cadena ) {
    $aRet = array();
    $aRet["<%contenido%>"] = ""; // contenido debe ser la primera acción a realizar
	/* @TODO usar preg_match_all */
    while ( preg_match ( "/<%(.*)*%>/Ui", $cadena, $aTemp )) {
        $aRet[$aTemp[0]]="";
        $cadena = str_replace ( $aTemp[0], "", $cadena );
    }
    return $aRet;
}




/*
 * Devuelve un array con las claves modulo, accion y id extraidas 
 * de una marca de inserción ( por ej: <%modulo(accion,id)%> ).
 */

function maquetador_extrae_modulo( $cadena ) {
   /* @TODO mejorar expresiones regulares para dobles espacios */
    $cadena = strtr ( $cadena, array ("<%"=>"", "%>"=>"" ));
    $cuando = "";

    if ( preg_match ( '/WHEN ([^ ]*) (PUT|PHP) (.*)/i' , $cadena, $aTemp ) ) {
        return array (
        "modulo"      => $aTemp[2],
        "accion"      => $aTemp[3],
        "condicional" => $aTemp[1] );
    } elseif ( preg_match ( "/WHEN (CONTROLADOR:(?:[^ ]*)(?: ACCION:(?:[^ ].*))?) (.*)/i", $cadena, $aTemp ) ) {
        $cuando = $aTemp[1];
        $cadena = $aTemp[2];
    } elseif ( preg_match ( '/WHEN ([^ ]*) (.*)/i' , $cadena, $aTemp ) ) {
        $cuando = $aTemp[1];
        $cadena = $aTemp[2];
    } elseif ( preg_match ( '/HOME (.*)/i' , $cadena, $aTemp ) ) {
        $cuando = "home";
        $cadena = $aTemp[1];
    } elseif ( preg_match ( '/ONCE (.*)/i' , $cadena, $aTemp ) ) {
        $cuando = "once " .$aTemp[1] ;
        $cadena = $aTemp[1];}

    $aTemp =  preg_split ( "#[,\(\)]#", $cadena);
    return array (
    "modulo"      => $aTemp[0],
    "accion"      => $aTemp[1],
    "id"          => $aTemp[2],
    "condicional" => $cuando );
}


/* 
 * includes 
 */

function maquetador_lee_fichero ( $path, $cual ) {
    if  ( preg_match ("/<%include [\'\"]?([^ \'\"]*)[\'\"]? ?%>/", $cual, $aTemp )) {
        return file_get_contents(( $path ? "$path/": "") . $aTemp[1]);
    }
    return "";
}



function maquetador_insertar_include ( $plantilla ) {
    static $path;
    if ( is_array($plantilla) ) {
        $plantilla = $plantilla[1];
    }
    if ( !$path ) {
        $path      = dirname($plantilla);
        $plantilla = basename($plantilla);
    }
    $cadena   = file_get_contents( "$path/$plantilla" );
    return preg_replace_callback ( "/<%include ([^%]*)%>/i", "maquetador_insertar_include",  &$cadena );
}


/*
 * Evalua una clausula WHEN que puede ser:
 * home, 'modulo', o 'funcion'()
 */

function maquetador_evalua( $condicion) {
    global $aEstado;
    if ( $condicion=="home" ) {
        return $aEstado["esHome"];
    }

    if ( substr( $condicion,0,5)=="once " ) {
        return ($_SESSION["ONCE"][substr( $condicion,5)]++ == 0 ) ;
    }


    if (  preg_match ( "/controlador:([^ ]*)( accion:([^ ].*))?/", $condicion, $aTemp )) {
        switch (count($aTemp)) {
            case 2: return $aEstado["controlador"]==$aTemp[1];
            case 4: return $aEstado["controlador"]==$aTemp[1] and $aEstado["accion"]==$aTemp[3];
            default: return false;
        }
    }
    if ( preg_match( "/(.*)\(\)$/", $condicion, $aTemp)) {
        $condicion=$aTemp[1];
        return $condicion();
    }
    return $aEstado['controlador']== $condicion;
}



function maquetador_esHome(){
 global $aEstado;
 return $aEstado["esHome"];
}
