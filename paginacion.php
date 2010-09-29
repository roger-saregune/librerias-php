<?php

/*
 * Paginación de resultados SQL. 
 * funciona solo sobre Mysql.
 *
 * @version 2010/09/26 
 *
 * 
 * Cambios
 * 2010/09/29 querystring
 * 2010/09/27 correciones y ampliaciones.
 *            clases para los enlaces de paginación.
 *            paginacion ahora admite un nuevo argumento: querystring.
 *            corregido la páginación de la leyenda (incompleto en los dos último)   
 * 2010/09/26 Cambios en la paginación. 
              Corregido error de ultima página.
 * 2010/05/26 PaginaActual con REQUEST.
 *
 * 2010/05/11 funciona el campo leyenda.
 *             suprimidas las funciones referer, calculaAlt, mEnlaceOrden
 *	
 */

include_once "funciones.php";

function _paginacion_base($queryString ="" ){
   
   if ( $queryString ) {
       if ( is_string ($queryString) ){
           $queryString = mQueryStringToArray($queryString);
       }
       $q= mQueryStringAdd ($queryString);
   } else {
       $q= $_SERVER['QUERY_STRING'];
   }
   
   $cQuery = preg_replace("#(&?paginaActual=[0-9]*)#ui","", $q );   
   $cUrl= pathinfo( $_SERVER['PHP_SELF']);
   
   return $cUrl["basename"]. "?". ($cQuery==""? "" : $cQuery."&amp;" );
}



/*
 * función de paginación 
 */



function paginacion( $cSql, $leyenda, $tamPagina=10, $maxIndex=10, $leyendaPaginas="Página %s de %s", $queryString=""){
   /* devuelve un array con ( n reg, n paginas, cSQL limitada, navegación ) */

   if ( $queryString ) {
     $nTemp = strpos( $queryString,"PaginaActual=");
     if ( $nTemp > 0 ) {
        $paginaActual = (int) substr($queryString,$nTemp+13 );
     }   
   } 

   if ( !$paginaActual ){ 
      $paginaActual= $_REQUEST["PaginaActual"] OR  $paginaActual= mRequest("PaginaActual");    
	   if ($paginaActual=="") {
		   $paginaActual=1;	
	   }
	}   
	
	$rsTemp  = mysql_query( $cSql );
	$totalReg= mysql_num_rows ( $rsTemp) ;
	$cNavega ="";
	
	if ($totalReg==0) {
        // no hay páginas
		$totalPaginas= 0;
		$paginaActual= 0;

    } elseif ( $totalReg <= $tamPagina ){
        // solo hay una página   
   	    $totalPaginas=1;
        $paginaActual=1;   	
	} else {
        // hay más de una página       
        $totalPaginas = ceil($totalReg/$tamPagina);
        if ( $paginaActual > $totalPaginas ) {
			$paginaActual= $totalPaginas;
		}
      	      
        // calculamos las leyendas si hay 
        if ( $leyenda != "" ) {                  
            $npag= 1;        
            $aCampos = explode (",", $leyenda);         
             
            while ( $npag <= $totalPaginas && $aTemp=mysql_fetch_array($rsTemp) ){
                $cTemp = "";
                foreach ($aCampos as $tCampo) {
                   $cTemp .= $aTemp[ trim ( $tCampo ) ] . " ";
                }
                $aLeyendas[$npag-1] .= " - $cTemp";            
                $aLeyendas[$npag] = $cTemp;
                /* TODO mirar error */                                    
                @mysql_data_seek ( $rsTemp, $npag*$tamPagina);
                $npag++;
            }
            
            // falta el ultimo
            mysql_data_seek ( $rsTemp, $totalReg-1);
            $aTemp=mysql_fetch_array($rsTemp,MYSQL_ASSOC);
            $cTemp = "";
            foreach ($aCampos as $tCampo) {
                   $cTemp .= $aTemp[ trim ( $tCampo ) ] . " ";
            }
            $aLeyendas[$totalPaginas] .= " - $cTemp";  
             
        }

        // cambiamos la SQL.
		if ( strstr ( strtoupper($cSql), " LIMIT " ) === false ) {
		   $cSql .= " LIMIT ". max(0,(($paginaActual-1)*$tamPagina)) . ",$tamPagina";
		}
		
		// Construir barra de navegacion  
		$webBase= _paginacion_base($queryString);
		if ($totalPaginas <= $maxIndex){
		    // todas las páginas caben. 
			for( $nCont = 1 ; $nCont <= $totalPaginas ; $nCont++ ) {
			    $temp = ( $leyenda ? " title='{$aLeyendas[$nCont]}'" : "" );
				$cNavega .= "&nbsp;<a href='{$webBase}PaginaActual=$nCont'$temp >$nCont</a>\n";
			}
		} else { 
		    // construir barra limitada	
		    if ( $paginaActual > $totalPaginas-$maxIndex ){
		        $nDesde= $totalPaginas-$maxIndex;
		        $nHasta= $totalPaginas;
		    } else {	        
			    $nDesde = floor($paginaActual/$maxIndex)*$maxIndex+1;
			    $nHasta = $nDesde+$maxIndex-1;
			}									
            
		    $nSiguiente = min ( $totalPaginas, ($nDesde==1? $maxIndex: $nDesde+$maxIndex ));
			$nAnterior  = max ( 1, $nDesde-$maxIndex);
			
			// boton de ir al principio (siempre 1)
			if ($nDesde>1) {
			   $temp = ( $leyenda ? " title='{$aLeyendas[1]}' " : "" );
		   	   $cNavega.= "<a class='paginacion_bottom' href='{$webBase}PaginaActual=1'$temp;>&lt;&lt;</a>";
		    }			
			
			// boton de ir al anterior
		    if ( $nDesde!=1) {/* boton anterior */
		   	    $temp = ( $leyenda ? " title='{$aLeyendas[$nAnterior]}' " : "" );
		   	    $cNavega.= "&nbsp;<a class='paginacion_anterior' href='{$webBase}PaginaActual=$nAnterior'$temp>&lt;</a>";
            }
		    
		    // poner las paginas
		    $cNavega .= "<span class='paginacion_paginas'>\n" ;	
			for( $nCont = $nDesde; $nCont <= $nHasta; $nCont++){
				$temp = ( $leyenda ? " title='{$aLeyendas[$nCont]}' " : "");
		    	$cNavega.= "&nbsp;<a href='{$webBase}PaginaActual=$nCont'$temp>$nCont</a>\n";
		    }			
		    $cNavega .= "</span>\n";

			// botón de siguiente
		    if ( $nHasta<$totalPaginas) {
		   	    $temp = ( $leyenda ? " title='{$aLeyendas[$nSiguiente]}' " : "" );
		   	    $cNavega.= "&nbsp;<a class='paginacion_siguiente' href='{$webBase}PaginaActual=$nSiguiente' $temp'>&gt;</a>\n";
		    }
			// botón de ultimo
		    if ( $nHasta<$totalPaginas) {
		   	    $temp = ( $leyenda ? " title='{$aLeyendas[$ntotalPaginas]}' " : "" );
		   	    $cNavega.= "&nbsp;<a class='paginacion_top' href='{$webBase}PaginaActual=$totalPaginas' $temp>&gt;&gt;</a>\n";
		    }			 

		}	 
	}

	if ($totalPaginas<=0) {
		$cPagina="&nbsp;";
	} else {
	    // se genera la paginación. Ej: 5 de 6 
		$cPagina = sprintf($leyendaPaginas, $paginaActual,  $totalPaginas) . " |". $cNavega;			
	}	
    
	$aTemp = array( $totalReg, $totalPaginas, $cSql, $cPagina ); 
	return $aTemp;
}
