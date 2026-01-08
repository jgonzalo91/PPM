<?php
include_once('../fichas_class.php');
include_once('../sostenibilidad_class.php');
include_once('../bitacora.php');
include_once('../utilerias.php');

// load domPDF
include '../Classes/dompdf 0.6.2/dompdf_config.inc.php';
require '../../observatorio/phpqrcode/qrlib.php';

// load PDFMerger
include '../PDFMerger/PDFMerger.php';
include '../PDFMerger/tcpdf/tcpdf.php';

include_once("../conexion/conexion.php");
$cone_ob2 = new conexion();
$conn_ob = $cone_ob2->conexionMysql();
set_time_limit(0);

// Parametros
$id_post = desencripta($_POST['id_proyecto']);
$id_ficha_sostenibilidad = desencripta($_POST['id_ficha_sostenibilidad']);
$Gradial= $_POST['radial'];

// Rutas
$ruta_images_imagenesPdf = $_SERVER["DOCUMENT_ROOT"]."/observatorio/images/imagenes_pdf/";
$ruta_obser = $_SERVER["DOCUMENT_ROOT"]."/observatorio/";
$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";

// load html base template
$html = '
<html>
    <head>
        <meta charset="UTF-8">
        <title>SOSTENIBILIDAD</title>                
        <style>
            .break_page { page-break-after: always; }
            .break_page:last-child { page-break-after: never; }
        </style>
        <link rel="stylesheet" href="../sostenibilidad/css/custompdf.css">
    </head>

        <body>';
          
include './plantilla_2_es.php';                

$html .= '
        </body>
</html>';

// File generation
$cur_dir = explode('\\', getcwd());
$output_file_name = "/wp-content/cache/tmp/pdf_sostenbilidad/ES_" . $id_ficha_sostenibilidad . "_SOS_2.pdf";
// NOTA IMPORTANTE: Cuando estas en el 43, 44 y produccion debe estar descomentada la linea del cur_dir y el _SERVER debe estar comentada
// Cuando estas en el local debe estar descomentada el _SERVER y si estas en el 43, 44 y produccion debe estar comentada cur_dir
//$output_file_path = $cur_dir[count($cur_dir) - 1] . $output_file_name;
$output_file_path = $_SERVER['DOCUMENT_ROOT'] . $output_file_name;

$dompdf = new DOMPDF();
$dompdf->set_paper('Letter');
$dompdf->set_option('enable_html5_parser', TRUE);
$dompdf->load_html($html);
$dompdf->render();
$output = $dompdf->output();
file_put_contents($output_file_path, $output);


// Unir el archivo de BID y GIZ
$pdf_bid = $_SERVER['DOCUMENT_ROOT'].'/wp-content/cache/tmp/pdf_sostenbilidad/ES_'.$id_ficha_sostenibilidad.'_SOS_1.pdf';
if(file_exists($pdf_bid)){
    $unir = new PDFMerger();
    $unir->addPDF($pdf_bid, 'all');
    $unir->addPDF($output_file_path, 'all');
    $file_proyecto  = $_SERVER['DOCUMENT_ROOT'].'/wp-content/cache/tmp/pdf_detalle_proyectos/ES_'.$id_post.'.pdf';
    if(file_exists($file_proyecto)){
        $unir->addPDF($file_proyecto, 'all');
    }
    $final = $_SERVER['DOCUMENT_ROOT'].'/wp-content/cache/tmp/pdf_sostenbilidad/ES_'.$id_ficha_sostenibilidad.'_SOS.pdf';
    
    // Intentar eliminar el archivo si existe, pero no fallar si está en uso
    if(file_exists($final)){
        @unlink($final); // El @ suprime el error si el archivo está abierto
    }
    
    $unir->merge('file', $final); // generate the file
}



function iconos($post, $ficha) {
    
    $cone_ob2 = new conexion();
    $conn_ob = $cone_ob2->conexionMysql();
    $MisIcons = array();
    $gabo = $conn_ob->prepare("select b.id_ods from tbl_alineaciones_sectoriales_x_ficha a inner join tbl_objetivos_subsectores b on a.id_objetivos_subsectores=b.idalineacionpsods  WHERE id_fichas_sostenibilidad=?");
    $gabo->bind_param('i', $ficha);
    $gabo->execute();
    $gabo->store_result();
    $gabo->bind_result($idods);
    while ($gabo->fetch()) {
        $MisIcons[] = $idods;
    }
    $conn_ob->close();
    return $MisIcons;
}
?>