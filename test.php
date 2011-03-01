<?php

/*
 * Test
 * 
 * Pequeña utilidad para hacer test de unidad. No pretende ser un sustituto de herramientas como PHPunit
 * sino la inclusión con fines didácticas de la 'cultura del test'.
 *
 * Ejemplo de utilización
 * 
 * 
 * include "test.php";
 * include "maquetador.php";
 * $test= new test("console", "Test maquetador<hr>");
 * $test->test(1, maquetador_enlace("t",1,2 )  , "<a href='?c=1&amp;a=2'>t</a>" );
 * $test->test(2, maquetador_enlace("t",1,2,3 ), "<a href='?c=1&amp;a=2&amp;i=3'>t</a>");
 * 
 * 
 */


class test {
	private $newline;
    
    private $begin;
    private $nTests;
    private $nErrors;
    

	function __construct( $tipo ="console", $titulo="") {
		$this->newline = ( strtolower($tipo) =="html" ? "<br/>" : "\n") ;
		if ( $titulo ) {
			 $this->mecho ($titulo);
		}
        $this->nErrors = 0;
        $this->nTests  = 0;
        $this->begin   = microtime(true);
	}	


    function __destruct  (){                                      
        $this->mEcho ( array ( ($this->newline=="<br/>" ? "<hr>" : "-----------------------------------------"),
                              "end test. " . ( $this->nErrors ? " ERRORS!!" :"all ok"),
                              "time:"      . round( microtime(true) - $this->begin,6) ."sec",
                              "test made: ". $this->nTests ,
                              "   errors: ". $this->nErrors ,
                              "   ok    : ". ($this->nTests -  $this->nErrors )    ));
        
    }
	
    
	function setEcho( $tipo ) {
		 $this->newline = ( strtolower($tipo) =="html" ? "<br/>" : "\n") ;
	}
	
    
	function mecho($text){
        if ( is_array ($text)){
            $text = implode ($this->newline, $text );
        }
        if ( $this->newline != "<html>" ) {
            $text = str_replace ( "<hr>","\n=========================================", $text);
        }
        echo $this->newline,$text,$this->newline;
	}
	
	function test( $number, $a, $b) {
        //
        $this->nTests++;
		if ( $a===$b ){
			echo "test $number: OK", $this->newline;
		} else {
			echo "test $number: ERROR ", $a, " expected ", $b, $this->newline ;
            $this->nErrors++;
		}	
	}
    
    

}
