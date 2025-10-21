<?php

$he = new Fichas();
$he->lang='EN';
$he->id_proyecto=$id_post;
$he->id_ficha_sostenibilidad=$id_ficha_sostenibilidad;
$header = $he->GetHeaderSos();
$info_pro = '<div style="margin-left: 1px !important; margin-right: 1px !important">'.$he->GetInfoProyecto().'</div>';
$footer = '<div class="footer">'.$he->GetFooterSos().'</div>';

// Obtenemos la imagen en base64
if($Gradial==""){
    $resultado = $conn_ob->query("select grafica_radial from tbl_fichas_sostenibilidad where id=".$id_ficha_sostenibilidad);
    $img = $resultado->fetch_array(MYSQLI_ASSOC);
    $grafica_radial = $img['grafica_radial'];
}else{
    $grafica_radial = $Gradial;
}

#HOJA TRES
$html .= '
<!-- TERCERA PAGINA --->
'.$header.''.$info_pro;

$contendor = new fichas();
$contendor->id_texto=4;
$contendor->lang='EN';
$textoPDF_a = $contendor->GetTextoPDF();

$contendor->id_texto=5;
$contendor->lang='EN';
$textoPDF_b = $contendor->GetTextoPDF();

$contendor->id_texto=6;
$contendor->lang='EN';
$textoPDF_c = $contendor->GetTextoPDF();

$contendor->id_texto=7;
$contendor->lang='EN';
$textoPDF_d = $contendor->GetTextoPDF();

$contendor->id_texto=8;
$contendor->lang='EN';
$textoPDF_e = $contendor->GetTextoPDF();

$contendor->id_texto=9;
$contendor->lang='EN';
$textoPDF_f = $contendor->GetTextoPDF();

$contendor->id_texto=88;
$contendor->lang='EN';
$textoPDF_alineaciones = $contendor->GetTextoPDF();

$contendor->id_texto=14;
$contendor->lang='EN';
$textoPDF_g = $contendor->GetTextoPDF();

$contendor->id_texto=10;
$contendor->lang='EN';
$textoPDF_h = $contendor->GetTextoPDF();

$html .= '
<!-- Fila 1 -->
<div class="table" style="margin-left:12px;">
<div class="col-12 table-row">
<div class="table-cell" style="width:69%">
'.$textoPDF_a.'
</div>

<div class="LineaVertical">
<img src="'.$ruta_images_imagenesPdf.'/lineaverdevertical.png" width="0.7" height="60">
</div>

<div class="table-cell" height="60%" style="width:31%;border-collapse: collapse; position: relative; padding-left:0 !important;align:left">
<div align="center">'.$textoPDF_b.'</div>';

$depende = iconos($id_post, $id_ficha_sostenibilidad);

if( count($depende) == 1 ){

$html .= '
<div style="position: absolute; left: 50px; top: 28px; z-index: 1;"><img src="'.$ruta_images_imagenesPdf.'/SDG_en/'.$depende[0].'_en.png" width="63" height="63"></div>';
}

if( count($depende) == 2 ){

$html .= '
<div style="position: absolute; left: 50px; top: 28px; z-index: 1;"><img src="'.$ruta_images_imagenesPdf.'/SDG_en/'.$depende[0].'_en.png" width="63" height="63"></div>
<div style="position: absolute; left: 150px; top: 28px; z-index: 2;"><img src="'.$ruta_images_imagenesPdf.'/SDG_en/'.$depende[1].'_en.png" width="63" height="63"></div>
';
}

if( count($depende) == 3 ){

$html .= '
<div style="position: absolute; left: 27px; top: 28px; z-index: 1;"><img src="'.$ruta_images_imagenesPdf.'/SDG_en/'.$depende[0].'_en.png" width="63" height="63"></div>
<div style="position: absolute; left: 100px; top: 28px; z-index: 2;"><img src="'.$ruta_images_imagenesPdf.'/SDG_en/'.$depende[1].'_en.png" width="63" height="63"></div>
<div style="position: absolute; left: 174px; top: 28px; z-index: 3;"><img src="'.$ruta_images_imagenesPdf.'/SDG_en/'.$depende[2].'_en.png" width="63" height="63"></div>
';
}

//Declaramos una carpeta temporal para guardar la imagenes generadas
$qrcode = "QR/";

//Declaramos la ruta y nombre del archivo a generar
$filename = $qrcode.'QR_'.$id_post.'_ALINEACION_EN.png';

//Parametros de Condiguracion
$tamao = 150; //Tamaño de Pixel
$level = 'L'; //Precision Baja
$framSize = 0; //Tamaño en blanco
$contenido = $actual_link."/observatorio/fichas/create_pdf_3.php?p=".encripta($id_post)."&f=". encripta($id_ficha_sostenibilidad)."&l=EN"; //Texto

//Enviamos los parametros a la Funcion para generar codigo QR
QRcode::png($contenido, $filename, $level, $tamao, $framSize);

$qr_alineacion_name = 'QR_'.$id_post.'_ALINEACION_EN.png';
$QR_alineacion_meta_atributo = $_SERVER["DOCUMENT_ROOT"].'/observatorio/fichas/QR/'.'QR_'.$id_post.'_ALINEACION_EN.png';
$url_alineacion = $contenido;

$html .= '
</div>
</div>
</div>

<!-- Fila 2 -->
<div class="table">
<div class="col-12 table-row">
<img src="'.$ruta_images_imagenesPdf.'/lineaverde.png" width="779px" height=".8" style="margin-left:20px; margin-right:20px;">
</div>
</div>

<!-- Fila 4 -->
<div class="table" style="margin-left: 20px;">
<div class="col-12 table-row">

<div class="col-8 table-cell">
    <table>
        <tr>
        <th align="left">'.$textoPDF_c.'</th>
        </tr>
        <tr>
        <td align="center">
        <div style="position: absolute; left: 0px; top: 28.5px; z-index: 1;"><img src="'.$grafica_radial.'" width="540" height="571.5"></div>
        <div id="contenedor-imagen" style="position: absolute; left: 0px; top: 35px; z-index: 1;"><img src="'.$ruta_images_imagenesPdf.'/GRadial_en.jpg" width="540" height="530"></div>
        <div style="position: absolute; left: 0px; top: 551px; z-index: 1;"><img src="'.$ruta_images_imagenesPdf.'/ParcheBlanco.png" width="80" height="50"></div>
        </td>
        </tr>
    </table>
</div>

<div class="col-4 table-cell">
    <div style="margin-top:100px;">
        <table style="width:100%">
            <tr>
                <td>            
                    <div height="60%" style="position: relative; width:135; margin-left: 15; height:40; border:#008588 2px solid; border-right-width: 3mm; padding-bottom:10;padding-left: 12;padding-right:9;border-right-style: solid;">
                        <div style="position: absolute; left: 5px; top: 0px; z-index: 1;width:120 ;text-align:left">'.$textoPDF_e.'</div>
                        <div style="position: absolute; left: 80px; top: 35px; z-index: 3;width:100"><a href="'.$url_alineacion.'" target="_blank">'.$textoPDF_h.'</a></div>
                        <div style="position: absolute; left: 140px; top: 4px; z-index: 2;width:100"><img class="img_border" width="60" height="60" src="'.$QR_alineacion_meta_atributo.'"/> </div>
                    </div>
                </td>
            </tr>

            <tr>
                <td align="center"> 
                    <div style="margin-top:16px;padding-right:12px">'.$textoPDF_f.'
                    </div>
                </td>
            </tr>
            
            <tr>
<td align="center"> 
<div style="margin-top:3px;padding-right:12px">'.$textoPDF_alineaciones.'</div>
</td>
</tr>

        </table>
    </div> 
</div>


<!-- Fila 5 -->
<div class="table">
    <div class="col-12 table-row">
        <div style="padding-left:4px !important; padding-right:9px !important; margin-top:160px !important">
        '.$textoPDF_g.'
        </div>
    </div>
</div>

</div>
</div>
'.$footer;

?>
