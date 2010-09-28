<?php

// abrir la conexión con la base de datos
@mysql_connect ( "localhost", "test", "test") or
  die ("No se pudo conectar al servidor mysql");
@mysql_select_db ( "test") or
  die ("No se pudo conectar seleccionar la base de datos gida"  );

mysql_query ("SET CHARACTER SET utf8");
mysql_query ("SET time_zone = '+01:00'");

// simulamos las funciones t
function t($s) { return $s; };
function tIdiomaLocale(){return "pagina %s de %s";}
function tIdiomaPorDefecto(){return "es";}


// incluimos la libreria que vamos a testear.
include "funciones.php";
include "datadriven.php";
include "sql.php";


test_edicion();

function test_edicion(){
$datos[] = array ( "tipo"    =>"cadena 20 20",
                   "cabecera"=>"Nombre:",
                   "campo"   =>"usuario_nombre");
$datos[] = array ( "tipo"    =>"nuevopassword 8 8",
                   "cabecera"=>"Nuevo password:",
                   "campo"   =>"usuario_password" );
$datos[] = array ( "tipo"    =>"verificapassword 8 8 ",
                   "cabecera"=>"verifica password:",
                   "campo"   =>"usuarios_test",
                   "verifica"   =>"verifica usuario_password" );                   
                                      
if ( $_REQUEST["usuario_nombre"] ){
  echo ddlib_guardar( "usuarios" , "usuario_id=1", $datos) .  "<br>";
}                    
                   
                   
$opciones= array ("titulo"=>"froga");                   
echo ddlib_edicion( $datos,"select * from usuarios where usuario_id=1", $opciones);                   
}


// test de unidad //
// $datos = new ddlib_dd ("cursos", "curso_id");
// echo $datos->idEsNumerico;


function test_paginacion(){
$cursos = array (
  array ( "tipo"=>"numero",
          "clase"=>"hola",
          "campo"=>"visita_id" ));
 
echo ddlib_consulta ( $cursos, "SELECT * FROM visitas", array ("querystring"=>"contador=5", "registrosPorPagina"=>5,"paginas"=>5) );
}

