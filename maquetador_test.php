<?php
 
 /*
  * TEST DEL MAQUETADOR
  */
  
include "maquetador.php";


/** testeando maquetador_script **/  
maquetador_script ( "añadir", "meta", "keys", "a, b,c");
maquetador_script ( "añadir", "script", "googleglub", "http://www.google.com/glub.js");
maquetador_script ( "añadir", "js", "verifica-fechas", "$( funcion(){ $('input.fecha').datepicker()})");  
  
echo  maquetador_script ( "genera", "todos" );
  
  