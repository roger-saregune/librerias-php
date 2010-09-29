<?php

/**
*
* libreria de imagenes
* @author Roger
* @licence GPL
* @version 2006-09-12
*
* 2006-09-12
* Creado con las funciones 
*  - tipoImagen, extrae el tipo (jpg,png,gif) a partir del nombre
	- mImageCreatFrom
		Crea una imagen a partir de un fichero jpg,gif o png.
	- mImagenResize
		Crea una nueva imagen,		
*  - crearThumbnail (idea de phpslideShow).
*   	Crear un thumbmail. El thumbnail es siempre un jpg. 
*		Se puede especificar del directorio, el sufijo, la calidad.
*/

include_once "funciones.php";

function tipoImagen( $cFile) {
   if (preg_match('/(jpg|jpeg)$/i',$cFile,$matches)) {
      return "jpg";
      }
   if (preg_match('/(png)$/i',$cFile, $matches)) { 
      return "png";      
     }
   if (preg_match('/(gif)$/i',$cFile)){   
      return "gif";
     }
   return "ND";
}


function mImageCreateFrom ( $cFile )	{	  
 	switch ( tipoImagen ( $cFile) ) { 
      case "jpg": 
         return imageCreatefromjpeg($cFile);
         break;
      case "gif":
         return imageCreatefromgif($cFile);
         break;
      case "png":
         return  imageCreatefrompng($cFile);
         break;
      default:
         return false;
    }                  
}


function mImageResize ($sFileNameFrom, $sFileNameTo, $KEEP_PROPORTIONS, $iProp1, $iProp2=0) {
	/* idea original de malam extraido de zend codex */
	$aProportions = array ('DO_NOT_KEEP_PROPORTIONS', 'KEEP_PROPORTIONS_ON_WIDTH', 'KEEP_PROPORTIONS_ON_HEIGHT', 'KEEP_PROPORTIONS_ON_BIGGEST', 'KEEP_PROPORTIONS_ON_SMALLEST');
	
	// comprobación de parametros correctos.
	if (!file_exists ($sFileNameFrom) ) {
	   echo "error parámetros: no hay fichero\n";     
   	return false;
	} 
	
   if ( empty ($KEEP_PROPORTIONS) || !in_array ($KEEP_PROPORTIONS, $aProportions ) ) {
	   echo "error parámetros: falta método. Se esperaba (DO_NOT_KEEP_PROPORTIONS, KEEP_PROPORTIONS_ON_WIDTH, KEEP_PROPORTIONS_ON_HEIGHT, KEEP_PROPORTIONS_ON_BIGGEST, KEEP_PROPORTIONS_ON_SMALLEST)\n";     
   	return false;
	} 	

   if ( !is_int($iProp1)) {
	   echo "error parámetros: la proporción no es número\n";     
   	return false;
	} 
	
	$aImg = @getimagesize ($sFileNameFrom);
   if (false === $aImg) {
     echo "error: [$sFileNameFrom] no es una imagen";
     return false;
   }
   
   // comprobar extensión 
	$aTypes = array (1 => 'gif', 2 => 'jpeg', 3 => 'png');
   if (!in_array ($aImg[2], array_keys ($aTypes))) {
       echo "error:  [$sFileNameFrom] no es png, jpg, o gif";
       return false;
   }
   
   switch ($KEEP_PROPORTIONS){
   	case 'KEEP_PROPORTIONS_ON_WIDTH' :
      	$width = $iProp1;
         $height = round ( $width * ($aImg[1]/$aImg[0]));
         break;
          
      case 'KEEP_PROPORTIONS_ON_HEIGHT' :
         $height = $iProp1;         
         $width = round ( $height * ($aImg[0]/$aImg[1]) );
         break;
      
      case 'KEEP_PROPORTIONS_ON_BIGGEST' :
         if ($aImg[0] >= $aImg[1]) {
         	$width = $iProp1;
            $height = round ( $width * ($aImg[1]/$aImg[0]));         
         } else {
            $height = $iProp1;
            $width = round ( $height * ($aImg[0]/$aImg[1]) );
         }
         break;

      case 'KEEP_PROPORTIONS_ON_SMALLEST' :
         if ($aImg[0] <= $aImg[1]) {
              $width = $iProp1;
              $height = round ( $width * ($aImg[1]/$aImg[0]));
         } else {
              $height = $iProp1;
              $width = round ( $height * ($aImg[0]/$aImg[1]) );
         }
         break;
      
     case 'DO_NOT_KEEP_PROPORTIONS':
         if ( !is_int ($iProp2)) {
               return false;
         }
         $width  = $iProp1;
         $height = $iProp2;
         break;
     }
     
     // primero se crea la función
     $im = mImageCreateFrom ($sFileNameFrom);
     if ( $im === false )
     		return ;
     $image_p = imagecreatetruecolor($width, $height);     
     imagecopyresampled($image_p, $im, 0, 0, 0, 0, $width, $height, $aImg[0], $aImg[1]);
	  
	  // ahora salvamos     
     $saveImg = create_function ('$img, $sFileNameTo', 'return @image'.$aTypes[$aImg[2]].'($img, $sFileNameTo);');
     if ($saveImg ($image_p, $sFileNameTo)) {
      	return true;
     } else {
         return false;
     }      
} 


function crearThumbnail ( $cDir, $cFile, $cSufijo, $thumbnail_max_width, $thumbnail_max_height,
 $thumbnail_quality=75, $thumbnail_style = "nocrop"){
 
    
 	$source_id = mImageCreateFrom( ($cDir!="" ? $cDir. "/" : "") . $cFile);
   if ( $source_id === false)
   	return false;
 
   $source_width  = imagesx($source_id);
   $source_height = imagesy($source_id);   
      
   //------------ Escalar la imagen  ------------------
	$scale = max($thumbnail_max_width/$source_width, $thumbnail_max_height/$source_height);
   if($scale < 1) {      
      $thumbnail_actual_width  = floor($scale * $source_width);
      $thumbnail_actual_height = floor($scale * $source_height);
	   $target_id= imagecreatetruecolor($thumbnail_actual_width, $thumbnail_actual_height);
      if(function_exists('imagecopyresampled')) {
    		 $target_pic=imagecopyresampled($target_id,$source_id,0,0,0,0,$thumbnail_actual_width,$thumbnail_actual_height,$source_width,$source_height);
      } else {
		    $target_pic=imagecopyresized($target_id,$source_id,0,0,0,0,$thumbnail_actual_width,$thumbnail_actual_height,$source_width,$source_height);
      }                
      imagedestroy($source_id);
      $source_id = $target_id;
   }
            
   if($thumbnail_style == "crop") {
	   $target_id=imagecreatetruecolor($thumbnail_max_width, $thumbnail_max_height);
      if(function_exists('imagecopyresampled')) {
    	    $target_pic=imagecopyresampled($target_id,$source_id,0,0,0,0,$thumbnail_max_width,$thumbnail_max_height,$thumbnail_max_width,$thumbnail_max_height);
      } else {
    	    $target_pic=imagecopyresized($target_id,$source_id,0,0,0,0,$thumbnail_max_width,$thumbnail_max_height,$thumbnail_max_width,$thumbnail_max_height);
      }
      imagedestroy($source_id);
      $source_id = $target_id;
   }
      
   $aNombre = extrae_ExtensionNombre($cFile);
   imagejpeg ($source_id, ($cDir!="" ? $cDir. "/" : "") . $aNombre["nombre"].$cSufijo .".jpg" , $thumbnail_quality);
   return ($cDir!="" ?  $cDir. "/" : ""). $aNombre["nombre"].$cSufijo .".jpg"; 
}

/* TEST DE UNIDAD 
mImageResize  ( "lema01.gif", "nuevo.gif", "KEEP_PROPORTIONS_ON_WIDTH", 253,50);
crearThumbnail ( "", "lema01.gif", "_thumb", 50,50,75 )
*/

 

?>
