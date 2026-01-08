<?php

include_once('../sostenibilidad_class.php');
include_once('../fichas_class.php');
include_once('../bitacora.php');
include_once('../utilerias.php');
// load domPDF
include '../Classes/dompdf 0.6.2/dompdf_config.inc.php';
require '../../observatorio/phpqrcode/qrlib.php';
// load PDFMerger
include '../PDFMerger/PDFMerger.php';
include '../PDFMerger/tcpdf/tcpdf.php';
include_once("../conexion/conexion.php");

// Variables para manejo de errores
$error_occurred = false;
$error_message = '';
$php_errors = array();

// Configurar manejo de errores para capturar TODOS los errores y warnings
set_error_handler(function($errno, $errstr, $errfile, $errline) use (&$php_errors) {
    // Capturar todos los errores y warnings (excepto notices)
    if($errno !== E_NOTICE && $errno !== E_DEPRECATED && $errno !== E_STRICT) {
        $php_errors[] = array(
            'type' => $errno,
            'message' => $errstr,
            'file' => basename($errfile),
            'line' => $errline
        );
    }
    return false; // Continuar con el manejo de errores normal
});

// Envolver todo el proceso en try-catch para capturar excepciones
try {
    // Parametros
    $id_sostenibilidad = desencripta($_POST['id_sostenibilidad']);
    $id_post = desencripta($_POST['id_proyecto']);
    $pdf_final = $_POST['pdf_final'];

    $cone_ob2 = new conexion();
    $conn_ob = $cone_ob2->conexionMysql();
    set_time_limit(800);



// Datos generales de sostenibilidad
$query0 = "select  a.resumen, a.fecha_ultima_edicion , b.Titulo tit_proyecto, a.id_catalogo_etapa id_etapa_sost, ";
$query0 .= "c.Titulo tit_etapa_sost, dp.id_sector, s.DESCRIPCION_ES tit_sector, dp.id_subsector, su.DESCRIPCION_ES tit_subsector ";
$query0 .= "from tbl_fichas_sostenibilidad a ";
$query0 .= "inner join proyectos b on b.id=id_datos_proyecto  ";
$query0 .= "inner join datos_proyecto dp on dp.id_proyecto = a.id_datos_proyecto ";
$query0 .= "inner join tbl_catalogo_etapas c on c.id=a.id_catalogo_etapa ";
$query0 .= "left join tbl_catalogo_sectores s on s.ID= dp.id_sector ";
$query0 .= "left join tbl_catalogo_subsectores su on su.ID= dp.id_subsector ";
$query0 .= "where a.id=" . $id_sostenibilidad;
$result_datos = $conn_ob->query($query0);
$_row = $result_datos->fetch_array(MYSQLI_ASSOC);
$resumen_sostenibilidad_proy = $_row['resumen'];
if (mb_strlen($resumen_sostenibilidad_proy) > 407)
    $resumen_sostenibilidad_proy = substr($resumen_sostenibilidad_proy, 0, 407) . '<a target="_blank" href="'.(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]/?post_type=proyecto_inversion&#038;p=".$id_post."&so=1#collapseSostenibilidad".'">&nbsp;Mostrar m&aacute;s..</a>';
$fecha_sostenibilidad = $_row['fecha_ultima_edicion'];
$titulo = $_row['tit_proyecto'];
$id_etapa = $_row['id_etapa_sost'];
$titulo_etapa = $_row['tit_etapa_sost'];
$id_sector = $_row['id_sector'];
$titulo_sector = $_row['tit_sector'];
$id_subsector = $_row['id_subsector'];
$titulo_subsector = $_row['tit_subsector'];

// Fuentes de sostenibilidad
$fu = 0;
$fuentes = "";
$tot_fuentes = 0;
$result = $conn_ob->query("select documento from tbl_documentos_x_ficha where id_fichas_sostenibilidad = $id_sostenibilidad order by id");
$num_fuentes = $result->num_rows;
while ($row = mysqli_fetch_array($result)) {
    $fuente = $row['documento'];
    $fuentes = $fuentes . $fuente . "&nbsp; / &nbsp;";
    $fu++;
    if ($fu == $tot_fuentes) {
        break;
    }
}
$fuentes = substr($fuentes, 0, -8);
if (mb_strlen($fuentes) > 545) {
    $fuentes = substr($fuentes, 0, 545);
    $fuentes = $fuentes . ' <a target="_blank" href="'.(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]/?post_type=proyecto_inversion&#038;p=".$id_post."&so=1#collapseSostenibilidad".'">&nbsp;Mostrar m&aacute;s..</a>';
}

# DATOS DE PROTAL PROYECTOS MEXICO   
$url_qr_bid = _query("SELECT meta_value FROM wp_postmeta WHERE post_id=57797 and meta_key='url_qr_bid'");
$resumen_bid = _query("SELECT meta_value FROM wp_postmeta WHERE post_id=57797 and meta_key='resumen_bid'");
$metodologia_proyecto = _query("SELECT meta_value FROM wp_postmeta WHERE post_id=57797 and meta_key='metodologia_proyecto'");
$consulta_proyecto_PM = _query("SELECT meta_value FROM wp_postmeta WHERE post_id=57797 and meta_key='consulta_proyecto_PM'");
$marco_metodologico_bid = _query("SELECT meta_value FROM wp_postmeta WHERE post_id=57797 and meta_key='marco_metodologico_bid'");

# QR BID ESPANIOL
$ruta_obser = $_SERVER["DOCUMENT_ROOT"]."/observatorio/";
$ruta_content = $_SERVER["DOCUMENT_ROOT"]."/wp-content/";
$ruta_images_imagenesPdf = $_SERVER["DOCUMENT_ROOT"]."/observatorio/images/imagenes_pdf/";

# QRCODE

// Declaramos una carpeta temporal para guardar la imagenes generadas
$qrcode = $_SERVER["DOCUMENT_ROOT"]."/wp-content/cache/tmp/resources/qrcode/";

// Declaramos la ruta y nombre del archivo a generar
$filename = $qrcode.'ES_' . $id_post . '_BID.png';

// Parametros de Condiguracion
$tamao = 150; // Tamaño de Pixel
$level = 'L'; // Precision Baja
$framSize = 0; // Tamaño en blanco
$contenido = $url_qr_bid; // Texto

//Enviamos los parametros a la Funcion para generar codigo QR 
QRcode::png($contenido, $filename, $level, $tamao, $framSize);

$qr_bid = $filename;

// load html base template
$html = '
<html>
    <head>
        <meta charset="UTF-8">
        <title>SOSTENIBILIDAD</title>                
        <style>
            .break_page { page-break-before: always; }
            .break_page:last-child { page-break-after: never; }
        </style>
        <link rel="stylesheet" href="../../observatorio/sostenibilidad/css/custompdf.css">
    </head>

        <body>';
        // Obtienes pagina 1 y 2
include './htmlpdf_es.php';                

$html .= '
        </body>
</html>';

// File generation
$cur_dir = explode('\\', getcwd());
$output_file_name = "/wp-content/cache/tmp/pdf_sostenbilidad/";
$output_file_name_2 = $output_file_name."ES_" . $id_sostenibilidad . "_SOS_1.pdf";

// NOTA IMPORTANTE: Cuando estas en el 43, 44 y produccion debe estar descomentada la linea del cur_dir y el _SERVER debe estar comentada
// Cuando estas en el local debe estar descomentada el _SERVER y si estas en el 43, 44 y produccion debe estar comentada cur_dir
//$output_file_path = $cur_dir[count($cur_dir) - 1] . $output_file_name;
$output_file_path = $_SERVER['DOCUMENT_ROOT'] . $output_file_name_2;


    // Generar PDF con DOMPDF
    try {
        $dompdf = new DOMPDF();
        $dompdf->set_paper('Letter');
        $dompdf->set_option('enable_html5_parser', TRUE);
        $dompdf->load_html($html);
        $dompdf->render();
        $output = $dompdf->output();
        
        if($output === false || empty($output)){
            throw new Exception("Error al generar el contenido del PDF con DOMPDF.");
        }
        
        // Intentar escribir el archivo
        $write_result = @file_put_contents($output_file_path, $output);
        if($write_result === false){
            throw new Exception("Error al escribir el archivo PDF. Verifique permisos de escritura en la carpeta.");
        }
    } catch (Exception $e) {
        $error_occurred = true;
        $error_message = "Error al generar el PDF: " . $e->getMessage();
    }

} catch (Exception $e) {
    $error_occurred = true;
    $error_message = "Error general al generar el PDF: " . $e->getMessage();
} catch (Error $e) {
    $error_occurred = true;
    $error_message = "Error fatal al generar el PDF: " . $e->getMessage();
}

// Verificar si hubo errores de PHP capturados (warnings, notices convertidos a errores)
if(!empty($php_errors) && !$error_occurred){
    $error_occurred = true;
    $ultimo_error = end($php_errors);
    $mensaje_error = $ultimo_error['message'];
    
    // Mensajes específicos según el tipo de error
    if(strpos($mensaje_error, 'Permission denied') !== false || 
       strpos($mensaje_error, 'failed to open stream') !== false) {
        $error_message = "Error de permisos: El archivo PDF puede estar abierto en otro programa. Por favor, cierre el archivo PDF e intente nuevamente.";
    } else if(strpos($mensaje_error, 'file_put_contents') !== false) {
        $error_message = "Error al escribir el archivo PDF. Verifique permisos de escritura o que el archivo no esté abierto.";
    } else {
        $error_message = "Error al generar el PDF: " . $mensaje_error;
    }
}

// Restaurar el manejador de errores
restore_error_handler();

// Si hay error, enviar respuesta JSON con el error
if($error_occurred){
    header('Content-Type: application/json');
    echo json_encode(array('error' => true, 'message' => $error_message));
    exit;
}

function Dimensiones_es($conexion, $id, $etapa) {
    $conn_ob = $conexion->conexionMysql();
    # Obtenemos todos las dimensiones (pilares)
    $html = "";
    $rsl_0 = $conn_ob->query("SELECT Id, Titulo FROM  tbl_catalogo_dimensiones order by Id");
    while ($row_0 = $rsl_0->fetch_assoc()) {
        $id_dimension = $row_0['Id'];
        switch ($id_dimension) {
            case 1: $img = "Financiera.png";
                break;
            case 2: $img = "Ambiental.png";
                break;
            case 3: $img = "Social.png";
                break;
            case 4: $img = "Institucional.png";
                break;
        }
        $border = 'border-bottom:1px solid #008588;';
        if ($id_dimension == 4)
            $border = '';
        
            # Obtenemos las practicas;
        $rsl_1 = $conn_ob->query("SELECT practica FROM tbl_buenas_practicas_x_ficha WHERE id_fichas_sostenibilidad=" . $id . " AND id_catalogo_dimensiones=" . $id_dimension);
        $buenas_practicas = $rsl_1->fetch_array(MYSQLI_ASSOC);
        $html .= "
        <div class='margenlr' style='background-color:#FFF;'>
            <table style='border-collapse: separate; width:100%; margin-top:5px;'>
                <tr>
                    <td style='position: sticky;width:100px;'>					
                        <div>
                            <img src='../../wp-content/themes/enfold-child/assets/csspdf/img/" . $img . "' width='240px' height='60px'>
                        </div>
                        <div style='padding:5px;border-right:1px solid #008588;'>
                            <b style='color:#008588; text-transform:uppercase;'>EJEMPLO DE BUENAS PRACTICAS</b>					
                            <div>" . $buenas_practicas['practica'] . "</div>
                        </div>
                    </td>
                    <td style='width:555px;" . $border . "'>
                        " . Atributos_es($conn_ob, $id, $id_dimension) . "
                    </td>
                </tr>
            </table>
        </div>";
    }
    return $html;
}

function Atributos_es($conexion, $id, $id_dimension) {
    //$conn_ob = $conexion->conexionMysql();
    # Colores
    $C1 = "#DDDDDD";
    $C2 = "#008588";
    $C3 = "#FFF";
    $border_td = "#808080";
    $html = "";
    $tier = 0;

    $html .= "
    <table>
        <tr style='border:6px solid #fff'>
            <td style='border-left:2px; text-align:center; width:455px;'><strong style='color:#008588;'>Criterios de sostenibilidad</strong></td>";
            // Query para obtener el alias de los TIERS ND,T1,T2,T3
            $queryt = "SELECT alias, Valor FROM tbl_catalogo_tiers WHERE Valor > -1 order by Valor";
            $rqt = $conexion->query($queryt);
            while ($rw = mysqli_fetch_array($rqt)){
                $aliast = $rw['alias'];
                $valt = $rw['Valor'];
                    if($valt==0){ //ND
                        $html .= "<td style='text-align:center;border-left:1px dashed " . $border_td . ";border-right:1px dashed " . $border_td . ";'> ".$aliast." </td>";
                    }else{
                        if($valt==3) // T3
                            $html .= "<td style='text-align:center;'> ".$aliast." </td>";
                        else // T1,T2
                            $html .= "<td style='text-align:center;border-right:1px dashed " . $border_td . "'> ".$aliast." </td>";
                    }
            }
    $html .="
         </tr>";

    
    // Query que solo muestra los TIERS ND,T1,T2,T3
    $query = "select atr.nombre as attr , ti.Valor as valor   
        from tbl_fichas_sostenibilidad s 
        inner join tbl_atributos_x_fichas ats on (s.id = ats.id_fichas_sostenibilidad)  
        inner join tbl_catalogo_atributos atr on (ats.id_catalogo_atributo = atr.id)
        inner join tbl_catalogo_tiers as ti on (ats.id_catalogo_tier = ti.id)
        where ti.Valor > -1 and s.id_catalogo_etapa = atr.etapaid and  atr.dimensionid = $id_dimension
        and  s.Id =$id order by atr.No_Orden";
    $rsl_2 = $conexion->query($query);
    while ($row = mysqli_fetch_array($rsl_2)) {
        $title = $row['attr'];
        $val = $row['valor'];
        //$key = $row['tier'];
        $tier = $row['valor'];
        if ($tier == 0) {
            $trND = $C1;
            $tr1 = $C3;
            $tr2 = $C3;
            $tr3 = $C3;
        }
        if ($tier == 1) {
            $trND = $C3;
            $tr1 = $C2;
            $tr2 = $C3;
            $tr3 = $C3;
        }
        if ($tier == 2) {
            $trND = $C3;
            $tr1 = $C2;
            $tr2 = $C2;
            $tr3 = $C3;
        }
        if ($tier == 3) {
            $trND = $C3;
            $tr1 = $C2;
            $tr2 = $C2;
            $tr3 = $C2;
        }

        $html .= "
                <tr style='border:6px solid #fff'>
                    <td style='text-align:right;;'><b>" . $title . "</b></td>
                    <td width='5px' style='background-color:" . $trND . ";border-left:1px dashed " . $border_td . "; border-right:1px dashed " . $border_td . ";'></td>
                    <td width='5px' style='background-color:" . $tr1 . ";border-right:1px dashed " . $border_td . ";'></td>
                    <td width='5px' style='background-color:" . $tr2 . ";border-right:1px dashed " . $border_td . ";'></td>
                    <td width='5px' style='background-color:" . $tr3 . ";'></td>
                </tr>";
    }

    $html .= "
    </table>";
    return $html;
}

function _query($query, $campo = '') {
    $rsl_campo = '';
    if (empty($campo))
        $campo = 'meta_value';
    if (!empty($query)) {
        $cone_ob2 = new conexion();
        $conn_PM = $cone_ob2->conexion_pm();
        $rsl = $conn_PM->query($query);
        $row = $rsl->fetch_array(MYSQLI_ASSOC);
        $rsl_campo = $row["$campo"];
        $conn_PM->close();
    }

    return $rsl_campo;
}

function _query2($query) {
    $row = '';
    if (!empty($query)) {
        $cone_ob2 = new conexion();
        $conn_PM = $cone_ob2->conexion_pm();
        $rsl = $conn_PM->query($query);
        $row = $rsl->fetch_array(MYSQLI_ASSOC);
        $conn_PM->close();
    }
    return $row;
}

?>