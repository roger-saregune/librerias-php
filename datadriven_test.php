<?php

// abrir la conexión con la base de datos
mysql_connect ( "localhost", "gida", "web") or
  die ("No se pudo conectar al servidor mysql");

mysql_select_db ( "gida") or
  die ("No se pudo conectar seleccionar la base de datos gida"  );

mysql_query ("SET CHARACTER SET utf8");
mysql_query ("SET time_zone = '+01:00'");

// simulamos la funcion t
function t($s) { return $s; };

// incluimos la libreria que vamos a testear.

include "datadriven.php";

// test de unidad //
// $datos = new ddlib_dd ("cursos", "curso_id");
// echo $datos->idEsNumerico;

$cursos[] = array ( 
 "tipo"=>"fecha",
 "clase"=>"hola",
 "campo"=>"ninguna" );
 
 echo ddlib_edicion( $cursos, "" );



/*
 		 case "hidden":            			
       case "fijo" :
       case "htmldespues":
       case "separadortabla":
       case "separador":
         
       case "adjunto":
         

       case "imagen":
       case "irudia":
         
       // listas
       case "lista":
       case "listasql":
       case "listafuncion":

         
       case "checkbox":
          

       // funciones informativas
       case "infofijo":
       case "infofuncion":
          
       case "readonly":
       case "info":
       

       case "htmlfijo":
       

       case "htmlfuncion":
       case "nuevopassword":
       case "verificapassword":

           
       // textos y cadenas
       case "texto":

       case "cadena":
           

       default:
           return "<input type='text' $atributos value='$campo'/>";

 */