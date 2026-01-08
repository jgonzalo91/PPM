<?php

$he = new Fichas();
$he->lang='EN';
$he->id_proyecto=$id_post;
$he->id_ficha_sostenibilidad=$id_sostenibilidad;
$header = $he->GetHeaderSos();
$info_pro = '<div style="margin-left: 1px !important; margin-right: 1px !important">'.$he->GetInfoProyecto().'</div>';
$footer_en = '<div class="footer">'.$he->GetFooterSos().'</div>';

$contendor = new fichas();
$contendor->id_texto=12;
$contendor->lang='EN';
$textoPDF_a = $contendor->GetTextoPDF();

$contendor->id_texto=13;
$textoPDF_b = $contendor->GetTextoPDF();

$contendor->id_texto=87;
$contendor->lang='EN';
$textoPDF_custom = $contendor->GetTextoPDF();

$contendor->id_texto=89;
$contendor->lang='EN';
$texto_sos_custom_en = $contendor->GetTextoPDF();

# HOJA UNO
$html .= '
<!-- PRIMER PAGINA --->
<div>
'.$header.'
'.$info_pro.'
<div>'.$textoPDF_b.'</div>
<!-- RESUMEN SOSTENIBILIDAD --->
<div style="height:1px; margin-top:5px">&nbsp;</div>
<div class="margenlr" style="background-color:#E6E6E6; color:#000; font-size:11px !important">			    	
    <b style="color:FFF"> Project’s sustainability summary: </b>
' . $resumen_sostenibilidad_proy_en . '
</div>

<hr class="margenlr" style="margin-top:10px; color:#008588; border-color:#008588;"/>  
' . Dimensiones($cone_ob2, $id_sostenibilidad, $id_etapa) . '
<hr class="margenlr" style="margin-top:4px; color:#008588;"/>';
// Si no hay fuentes no muestra la seccion
if ($num_fuentes > 0) {
$html .= '
<!-- FUENTES --->
<div class="margenlr" style="margin-top:4px; padding:6px;">			    	
<b>Source of this project:</b>&nbsp;' . $fuentes . '
</div>

<hr class="margenlr"  style="margin-top:4px; color:#008588;" />  ';
}

// 1. Clonar y Limpiar el texto para la VALIDACIÓN (para saber si tiene contenido)
$textoLimpio = trim(strip_tags($textoPDF_custom));

// 2. Comprobar si hay contenido real antes de intentar recortar
if ($textoLimpio !== '') {
    
    // 3. Definir el límite y el texto original
    // *** Límite actualizado a 500 caracteres ***
    $limite = 500;
    $textoOriginal = $textoPDF_custom;

    // 4. Aplicar el LÍMITE DE CARACTERES (usando mb_substr para caracteres especiales)
    $textoParaImprimir = mb_substr($textoOriginal, 0, $limite);

    // 5. Agregar puntos suspensivos si el texto original es más largo que el límite
    if (mb_strlen($textoOriginal) > $limite) {
        $textoParaImprimir .= '...';
    }

    // 6. Imprimir el bloque HTML en el PDF con el texto recortado
    // Se agregan un tag <hr>
    $html .= '<div class="margenlr" style="margin-top:4px padding:6px;">' // Estilo actualizado
           . $textoParaImprimir
           . '</div>'
           . '<hr class="margenlr" style="margin-bottom:4px; color:#008588;" />';
}

$html .= $footer_en;

# HOJA DOS 
# SI HAY GRAFICA AGREGA LA PAGINA DE LA GRAFICA
$cone_ob2 = new conexion();
$conn_ob = $cone_ob2->conexionMysql();
$resulta_grafica = $conn_ob->query("select distinct(id_proyecto) from tbl_general_attr_proyectos 
where id_subsector=(select id_subsector from tbl_general_attr_proyectos where id_sostenibilidad=$id_sostenibilidad limit 1) 
and id_macroetapa=(select id_macroetapa from tbl_general_attr_proyectos where id_sostenibilidad=$id_sostenibilidad limit 1) ;
");

$nuemro_proy=$resulta_grafica->num_rows;
if ( $nuemro_proy >=1 ){
if ($id_etapa == 1783|| $id_etapa== 1784|| $id_etapa == 1785|| $id_etapa == 1786) {
$result = $conn_ob->query(" select texto_en from variables where id= 1 ");
while ($row = mysqli_fetch_array($result)){
$texto1 = $row['texto_en'];
}
$result = $conn_ob->query(" select texto_en from variables where id= 5 ");
while ($row = mysqli_fetch_array($result)){
$texto2 = $row['texto_en'];
}

$html .= '
<!-- SEGUNDA PAGINA --->
<p class="break_page"></p>
<div>
'.$header.''.$info_pro.'
<!-- GRAFICA --->
<div class = "margenlr" style="margin-top:150px;font-size:14px !important;">
<div style = "text-align:center;">
<p  style = "text-align:center; font-size:16px !important;">' . $texto1 . '</p>
<p  style = "text-align:center;">(' . $texto2 . ' <b style="color:#037D7A">' . $nuemro_proy . '</b><b style="color:#000">)</b> </p> 
</div>
<div style = "text-align:center;">
';
// Verificar que el archivo de la gráfica exista antes de incluirlo
$ruta_grafica_en = $_SERVER["DOCUMENT_ROOT"].'/wp-content/themes/enfold-child/sostenibilidad/graficas/pdf_'.$id_sostenibilidad.'-en_.png';
if(file_exists($ruta_grafica_en)){
    $html .= '<img src="'.$ruta_content.'/themes/enfold-child/sostenibilidad/graficas/pdf_'.$id_sostenibilidad.'-en_.png" width="576">';
} else {
    // Si el archivo no existe, intentar con ruta alternativa o mostrar mensaje
    $html .= '<!-- Gráfica no disponible aún -->';
}
$html .= '
</div>
<div style="text-align:center;">
<img src="'.$ruta_content.'/themes/enfold-child/sostenibilidad/graficas/piegrafica_en.PNG" width="280" height="35">
</div>
</div>';

$html .= '
<div class="margenlr" style="margin-top:5px !important;font-size:12px !important; text-align: center;">
    
    <div style="margin-top: 30px;"> 
        ' . $texto_sos_custom_en . '
    </div>
    
    <div style="margin-top: -15px; margin-bottom: 25px;">
        
        <table style="margin: 0 auto; text-align: left; border-collapse: collapse;">
            <tr>
                <td style="padding: 0 10px; vertical-align: middle;">
                    <a href="'.$url_qr_bid_en.'">
                        <img class="img_border" width="60" height="60" src="' . $qr_bid_en . '" title="QR Bid" alt="QR Bid"/>
                    </a>
                </td>
                
                <td style="padding: 0 10px; vertical-align: middle;">
                    <div>' . $marco_metodologico_bid_en . '</div>
                    <a href="'.$url_qr_bid_en.'">'.$textoPDF_a.'</a>
                </td>
            </tr>
        </table>
        </div>

</div>
' . $footer_en . '
</div>
</div>';
}
}

?>
