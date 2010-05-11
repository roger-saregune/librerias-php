<?php

/*
 * Paginación de resultados SQL. 
 * funciona solo sobre Mysql.
 *
 * @version 2010/05/11 
 *
 * 2010/05/11 funciona el campo leyenda.
 *             suprimidas las funciones referer, calculaAlt, mEnlaceOrden
 *	
 */



function _paginacion_base(){
   
   $cQuery= preg_replace("#(&?paginaActual=[0-9]*)#ui","", $_SERVER['QUERY_STRING']);             
   $cUrl= pathinfo( $_SERVER['PHP_SELF']);
   
   return $cUrl["basename"]. "?". ($cQuery==""? "" : $cQuery."&amp;" );
}



/*
 * función de paginación 
 */



function paginacion( $cSql, $leyenda, $tamPagina=10, $maxIndex=10, $leyendaPaginas="Página %s de %s" ){
	/* devuelve un array con ( n reg, n paginas, cSQL limitada, navegación ) */

	$paginaActual= mRequest("PaginaActual");
	if ($paginaActual=="") {
		$paginaActual=1;	
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
      
      // hay mas de una página       
      $totalPaginas = ceil($totalReg/$tamPagina);
      if ( $paginaActual > $totalPaginas ) {
			$paginaActual= $totalPaginas;
		}	
      
      // calculamos las leyendas si hay 
      if ( $leyenda != "" ) {                  
         $npag= 1;        
         $aCampos = explode (",", $leyenda);         
         
         while ( $npag < $totalPaginas && $aTemp=mysql_fetch_array($rsTemp) ){
            $cTemp = "";
            foreach ($aCampos as $tCampo) {
               $cTemp .= $aTemp[ trim ( $tCampo ) ] . " ";
            }
            $aLeyendas[$npag-1] .= " - $cTemp";            
            $aLeyendas[$npag] = $cTemp;                                    
            mysql_data_seek ( $rsTemp, $npag*$tamPagina);
            $npag++;
         }       
      }

		if ( strstr ( strtoupper($cSql), " LIMIT " ) === false ) {
		   $cSql .= " LIMIT ". max(0,(($paginaActual-1)*$tamPagina)) . ",$tamPagina";
		}
		
		/* Construir barra de navegacion  */
		$webBase= _paginacion_base();
		if ($totalPaginas <= $maxIndex){
			for( $nCont = 1 ; $nCont <= $totalPaginas ; $nCont++ ) {
			   $temp = ( $leyenda ? " title='{$aLeyendas[$nCont]}'" : "" );
				$cNavega .= "&nbsp;<a href='{$webBase}PaginaActual=$nCont'$temp >$nCont</a>";
			}
		}	else { /*construir barra limitada */
		        
			$nDesde     = max (1, floor($paginaActual/$maxIndex)*$maxIndex ); 			
			$nHasta     = min ($totalPaginas, $nDesde + $maxIndex - ($nDesde==1?1:0));
		   $nSiguiente = min ( $totalPaginas, ($nDesde==1? $maxIndex: $nDesde+$maxIndex ));
			$nAnterior  = max ( 1, $nDesde-$maxIndex);
			
			// boton de ir al principio (siempre 1)
			if ($nDesde>1) {
				   $temp = ( $leyenda ? " title='{$aLeyendas[1]}' " : "" );
		   		$cNavega.= "<a href='{$webBase}PaginaActual=1'$temp;>&lt;&lt;</a>|";
		   }			
			// boton de ir al anterior
		   if ( $nDesde!=1) {/* boton anterior */
		   	   $temp = ( $leyenda ? " title='{$aLeyendas[$nAnterior]}' " : "" );
		   		$cNavega.= "<a href='{$webBase}PaginaActual=$nAnterior'$temp>&lt;</a>| ";
         }
		   // poner las paginas 	
			for( $nCont = $nDesde; $nCont< $nHasta; $nCont++){
				 $temp = ( $leyenda ? " title='{$aLeyendas[$nCont]}' " : "");
		    	 $cNavega.= "&nbsp;<a href='{$webBase}PaginaActual=$nCont'$temp>$nCont</a> ";
		   }			

			// botón de siguiente
		   if ( $nHasta<$totalPaginas) {
		   	 $temp = ( $leyenda ? " title='{$aLeyendas[$nSiguiente]}' " : "" );
		   	 $cNavega.= "&nbsp;|<a href='{$webBase}PaginaActual=$nSiguiente' $temp'>&gt;</a>";
		   }
			// botón de ultimo
		   if ( $nHasta<$totalPaginas) { 
		   	$temp = ( $leyenda ? " title='{$aLeyendas[$ntotalPaginas]}' " : "" );
		   	$cNavega.= "|<a href='{$webBase}PaginaActual=$totalPaginas' $temp>&gt;&gt;</a>|";
		   }			 

		}	 
	}

	if ($totalPaginas<=1) {
		$cPagina="&nbsp;";
	} else {
		$cPagina = sprintf($leyendaPaginas, $paginaActual,  $totalPaginas) . " |". $cNavega;			
	}	
    
	$aTemp = array( $totalReg, $totalPaginas, $cSql, $cPagina ); 
	return $aTemp;
}
