<?php 
include_once('../conexion/conexion.php'); 
include_once('../catalogo.php'); 
include_once('../area.php'); 
include_once('../menu.php'); 
include_once('../utilerias.php'); 
include_once('../permiso.php'); 
include_once('../alineacionpodsattr_class.php'); 
include_once('../fichas_class.php'); 
// Validación de permiso de acceso 
error_reporting(0);
if (!isset($_SESSION))
    session_start();

$id = desencripta($_REQUEST['id']);
$modal = $_REQUEST['m'];
$idMenu = 618;
$p = new permiso();
$per = $p->verificaPermiso((int) desencripta($_SESSION['idUsuario']), $idMenu);
if ($per->id_permiso == 0)
    header('Location: sinPermiso.php');
// Conexion a base de datos
$_sql = new conexion();
$_sql1 = $_sql->conexionMysql();
$id_proyecto = desencripta($_REQUEST['id']);
// El primer modal es la lista de fichas
if($modal==1){
    $id_etapa = desencripta($_REQUEST['id_etapa']);
    $Query  = "SELECT a.id, b.Titulo etapa,  a.publicada terminada, date_format(a.fecha_ultima_edicion, \"%d/%m/%Y\") fecha_edicion, ";
    $Query .= "CONCAT(b.Titulo, \" \",date_format(a.fecha_ultima_edicion, \"%d/%m/%Y\")) selects, publicar_por_omision, id_catalogo_etapa, id_subsector, id_datos_proyecto  ";
    $Query .= "FROM tbl_fichas_sostenibilidad a ";
    $Query .= "INNER JOIN tbl_catalogo_etapas b on a.id_catalogo_etapa=b.Id ";
    $Query .= "INNER JOIN datos_proyecto c on a.id_datos_proyecto=c.id_proyecto ";
    $Query .= "WHERE a.id_datos_proyecto=?";
    $pre1 = $_sql1->prepare($Query);
    $pre1->bind_param('i', $id_proyecto);
    $pre1->execute();
    $pre1->store_result();
    $pre1->bind_result($a,$b,$c,$d, $e, $f, $g, $h, $j);
    $pre1->num_rows;
    $table  = "<div class='col col-sm-12'>";
    $table .= "<div class='col col-sm-4'><strong>Ficha visible de manera predeterminada:</strong></div>";
    $table .= "<div class='col col-sm-5'><select class='form-control' name='publicar_x_omision' id='publicar_x_omision'><option value='0'>-Selecciona una ficha-</option>";
    $tr_1="";
    $tr_pdf_anterior = "";
    $contador = 1;
    while ($pre1->fetch()) {
        // verificamos auditoria
        $k=  new Fichas();
        $k->id_ficha_sostenibilidad = $a;
        $k->id_etapa = $g;
        $k->out = 0;
        $tot_resumen_en = $k->Auditoria_Resumen();
        $tot_attr = $k->Auditoria_Attr_NULL();
        $tot_ali_pen = $k->Auditoria_Ali_Pend();
        $tot_practicas = $k->Auditoria_Practicas_Publicas();
        $audi = 0;
        // Si falta algo en la auditoria actualizamos estatus
        if($tot_resumen_en==true || $tot_attr>0 || $tot_practicas>0 || $tot_ali_pen>0){
            $audi = 1;
        }
        
        $terminada = "Borrador";
        if($audi==0 && $c==1){
            $terminada = "Terminada";
        }else{
            $q = $_sql1->prepare("UPDATE tbl_fichas_sostenibilidad SET publicada=0 WHERE id=?");
            $q->bind_param("i", $a);
            $q->execute();
        }
        $selected = "";
        $color_x_omision_bg = "";
        $color_x_omision_letter = "";
        $OmisionyTerminado=0;
        if($c==1 && $f==1 ){
            $selected = "selected";
            $color_x_omision_bg = "#1b9b9e";
            $color_x_omision_letter = "#FFF";
            $OmisionyTerminado=1;
        }
        
        $n = $b.'&nbsp;'.$d;
        
        if($audi==0 && $c==1)
            $table .= "<option value='". encripta($a)."' ".$selected.">".$n."</option>";
        
        
        $tr_1 .= "<tr style='background-color:".$color_x_omision_bg.";color:".$color_x_omision_letter.";'>";
            $tr_1 .= "<td>".$contador."</td>";
            $tr_1 .= "<td>".$b."</td>";
            $tr_1 .= "<td>".$terminada."</td>";
            if($d!="")
                $tr_1 .= "<td>".$d."</td>";
            else
                $tr_1 .= "<td>Null</td>";
            
            // Verificamos si tiene pdf y obtenemos la fecha de creacion
            $fecha_pdf = "";
            if($audi==0 && $c==1){
                $existe = 0;
                // Primero verificamos con id ficha
                $serv = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
                $pdf  = $_SERVER['DOCUMENT_ROOT'].'/wp-content/cache/tmp/pdf_sostenbilidad/ES_'.$a.'_SOS.pdf';
                $pdf_en  = $_SERVER['DOCUMENT_ROOT'].'/wp-content/cache/tmp/pdf_sostenbilidad/EN_'.$a.'_SOS.pdf';
                if(file_exists($pdf) && file_exists($pdf_en)){
                    $date1 = new DateTime(date('Y-m-d H:i:s'));
                    $date2 = new DateTime(date('Y-m-d H:i:s',filemtime($pdf)));
                    $diff = $date2->diff($date1);
                    $fecha_pdf=get_format($diff);
                    $existe =1;
                    $urlPdf=$serv.'/wp-content/cache/tmp/pdf_sostenbilidad/ES_'.$a.'_SOS.pdf';
                    $urlPdf_en=$serv.'/wp-content/cache/tmp/pdf_sostenbilidad/EN_'.$a.'_SOS.pdf';
                }
            }
            $tr_1 .= "<td>".$fecha_pdf." </td>";
            $tr_1 .= "<td class='text-center'>";
                $tr_1 .= "<a title='Modificar Ficha' href='javascript:ModificarFicha(\"".encripta($a)."\",\"".encripta($id)."\",\"".encripta($g)."\",\"".encripta($h)."\",\"".$b."\" )' style='padding:5px;' data-toggle='tooltip' data-placement='top' title='Tooltip on top'><i class='fa-solid fa-pencil' style='font-size:20px;cursor: pointer;'></i>&nbsp;</a>";
                $tr_1 .= "<a title='Auditoria' href='javascript:auditoria(\"". encripta($a)."\", \"". $d."\", \"". encripta($g)."\", \"". encripta($id_proyecto)."\")' style='padding:5px;'><i class='fa-solid fa-chalkboard-user' style='font-size:20px;cursor: pointer;'></i>&nbsp;</a>";
                $tr_1 .= "<a title='Reporte Interno de la Ficha de Sostenibilidad' href='javascript:reporteInternoFichaSostenibilidad(\"". encripta($a)."\", \"". $d."\", \"". encripta($g)."\")' style='padding:5px;'><i class='fa-solid fa-file-contract' style='font-size:20px;cursor: pointer;color:gray;'></i></a>";

                if($audi==0 && $c==1){
                    if($existe==1) // actualiza
                        $tr_1 .= "<a title='Actualizar PDF' href='javascript:GenerarPDF(\"".encripta($j)."\", \"".encripta($a)."\", \"".encripta($g)."\", \"A\",".$OmisionyTerminado.");' style='padding:5px;'><i class='fa-brands fa-react' style='font-size:20px;cursor: pointer;color:gray;'></i></a>";
                    else // Genera
                        $tr_1 .= "<a title='Generar PDF' href='javascript:GenerarPDF(\"".encripta($j)."\", \"".encripta($a)."\", \"".encripta($g)."\", \"G\",".$OmisionyTerminado.");' style='padding:5px;'><i class='fa-brands fa-react' style='font-size:20px;cursor: pointer;color:gray;'></i></a>";
                }

            // Descarga PDF   
            if($existe==1 && $audi==0 && $c==1){ // actualiza
                $tr_1 .= "<a title='PDF Espa&ntilde;ol' href='$urlPdf' style='padding:5px;' target='_blank'><i class='fa fa-file-pdf-o' style='font-size:20px;cursor: pointer;color:red;'></i></a>";
                $tr_1 .= "<a title='PDF Ingl&eacute;s' href='$urlPdf_en' style='padding:5px;' target='_blank'><i class='fa fa-file-pdf-o' style='font-size:20px;cursor: pointer;color:blue;'></i></a>";
            }

            $tr_1 .= "</td>";
        $tr_1 .= "</tr>";
        
        $contador++;
    }
    // Fichas generadas en la version anterior que se buscan por el post o proyecto
    $pdf_pro  = $_SERVER['DOCUMENT_ROOT'].'/wp-content/cache/tmp/pdf_sostenbilidad/ES_'.$j.'_SOS.pdf';
    $pdf_pro_en  = $_SERVER['DOCUMENT_ROOT'].'/wp-content/cache/tmp/pdf_sostenbilidad/EN_'.$j.'_SOS.pdf';
    $contador_ant=$contador;
    if(file_exists($pdf_pro) || file_exists($pdf_pro_en)){
        $date1_ant = new DateTime(date('Y-m-d H:i:s'));
        $date2_ant = new DateTime(date('Y-m-d H:i:s',filemtime($pdf_pro)));
        $diff_ant = $date2_ant->diff($date1_ant);
        $fecha_pdf_ant=get_format($diff_ant);
        $existe_ant=1;
        $urlPdf_ant=$serv.'/wp-content/cache/tmp/pdf_sostenbilidad/ES_'.$j.'_SOS.pdf';
        $urlPdf_en_ant=$serv.'/wp-content/cache/tmp/pdf_sostenbilidad/EN_'.$j.'_SOS.pdf';
        $tr_pdf_anterior .= "<tr>";
        $tr_pdf_anterior .= "<td>".$contador_ant."</td>";
        $tr_pdf_anterior .= "<td>&nbsp;</td>";
        $tr_pdf_anterior .= "<td>&nbsp;</td>";
        $tr_pdf_anterior .= "<td>&nbsp;</td>";
        $tr_pdf_anterior .= "<td>".$fecha_pdf_ant."</td>";
        $tr_pdf_anterior .= "<td class='text-center'>";
        if($existe_ant==1 ){ // actualiza
            $tr_pdf_anterior .= "<a title='PDF Espa&ntilde;ol' href='$urlPdf_ant' style='padding:5px;' target='_blank'><i class='fa fa-file-pdf-o' style='font-size:20px;cursor: pointer;color:red;'></i></a>";
            $tr_pdf_anterior .= "<a title='PDF Ingl&eacute;s' href='$urlPdf_en_ant' style='padding:5px;' target='_blank'><i class='fa fa-file-pdf-o' style='font-size:20px;cursor: pointer;color:blue;'></i></a>";
        }
        $tr_pdf_anterior .= "</td>";
        $tr_pdf_anterior .= "</tr>";
        $contador_ant++;
    }
    $table .= "</select></div>";
    $table .= "<div class='col col-sm-3'><a class='btn btn-primary' style='color:#FFF;' onclick='PublicarxOmision();'> Guardar</a></div>";
    $table .= "</div><br><hr/>";
    $table .= "<div class='col col-sm-12'>";
    $table .= "<table class='table' style='width: 100%;'>";
        $table .= "<thead style='background-color: #337ab7; color: #FFF;'>";
        $table .= "<tr>";
            $table .= "<th>No.</th>";
            $table .= "<th>Etapa</th>";
            $table .= "<th>Status</th>";
            $table .= "<th>&Uacute;ltima edici&oacute;n</th>";
            $table .= "<th>&Uacute;ltima Act. PDF</th>";
            $table .= "<th>Opciones</th>";
        $table .= "</tr>";
        $table .= "</thead>";
        $table .= "<tbody>";
        $table .= "<tbody>";
        // Obtenemos la cantidad de fichas
        $table .= $tr_1;
        // Mostramos las fichas generadas en la version anterior
        if($existe_ant){
        $table .= "<tr class='text-center'><td colspan=6><strong>Fichas generadas en la versi&oacute;n anterior de sostenibilidad</strong></td></tr>";
        $table .= $tr_pdf_anterior;
        }
        $table .= "</tbody>";
    $table .= "</table>";
    $table .= "</div><br/>";
    $table .= "<div class='col col-sm-12 text-right'>";
    $table .= "<a href='javascript:AgregarFicha(\"".encripta($id_proyecto)."\");' class='btn btn-success' style='color:#FFF;'> Agregar Ficha</a>";
    echo $table .= "</div><div id='chartdiv_es' style='height: 600px; width: 560px;display:none;'></div>";
}
else{
    
    if($modal==2){
        $alta= "";
        $alta .= "<div class='content'>";
            $alta .= "<div class='row>'>";
                $alta .= "<div class='col-sm-12'>";
                    $alta .= "<strong><h5>Nueva ficha de sostenibilidad</h5></strong>";
                $alta .= "</div>";
                $alta .= "<div class='col-sm-12'>";
                    $alta .= "<div class='col col-sm-4'><label>Etapa de la ficha:</label></div>";
                    $alta .= "<div class='col col-sm-4'>";
                        $alta .= "<select class='form-control' name='alta_ficha_etapa' id='alta_ficha_etapa'>";
                        $Qu = "SELECT Id, Titulo FROM tbl_catalogo_etapas WHERE Sostenibilidad=1 order by Id ";
                        $rs = $_sql1->query($Qu);
                        while ($row = $rs->fetch_assoc()) {
                            $alta .= "<option value='". encripta($row['Id'])."'>".$row['Titulo']."</option>";
                        }
                        $alta .= "</select>";
                    $alta .= "</div>";
                    $alta .= "<div class='col col-sm-2'><a href='javascript:GuardarFichaProyecto(\"". encripta($id_proyecto)."\")' class='btn btn-success'>Alta ficha</a></div>";
                    $alta .= "<div class='col col-sm-2'><a href='javascript:ListaFichas(\"".encripta($id_proyecto)."\", \"\", 1);' class='btn btn-danger'>Cancelar</a></div>";
                $alta .= "</div>";
                $alta .= "<div class='col col-sm-12'>&nbsp;</div>";
            $alta .= "</div>";
        echo $alta .= "</div>";
    }else{
        $id_ficha_sostenibilidad = desencripta($_REQUEST['id_ficha']);
        $idproyecto = desencripta($_REQUEST['idproyecto']);
        $titulo = $_REQUEST['titulo'];
        // Listado de documentos por fichas
        if($modal=='L_DC'){
            //$documents  = "<div class='col col-sm-12'>";
                $documents  = "<div class='row' style='padding: 5px;'><div class='col col-sm-12'>";
                    $documents  .= "<table class=\"table\">";
                        $documents  .= "<thead style='background-color: #337ab7; color: #FFF;'>";
                            $documents  .= "<tr>";
                                $documents  .= "<th>&nbsp;</th>";
                                $documents  .= "<th>Orden</th>";
                                $documents  .= "<th>Documento</th>";
                                $documents  .= "<th>&nbsp;</th>";
                            $documents  .= "</tr>";
                        $documents  .= "</thead>";
                        $documents  .= "<tbody>";
                        $QueriDOcs = "SELECT documento, id, liga_externa, no_orden FROM tbl_documentos_x_ficha WHERE id_fichas_sostenibilidad=? order by no_orden";
                        $preDoc = $_sql1->prepare($QueriDOcs);
                        $preDoc->bind_param('i', $id_ficha_sostenibilidad);
                        $preDoc->execute();
                        $preDoc->store_result();
                        $preDoc->bind_result($documento, $iddoc, $liga_ext, $no_orden);
                        //$cont_docs=1;
                        while ($preDoc->fetch()) {
                            $documents .= "<tr>";
                            $documents .= "<td><i class='fa fa-pencil-square-o' style='font-size:20px;cursor: pointer;' onclick='DocumentosxFicha(\"". encripta($id_ficha_sostenibilidad)."\", \"M\", \"".encripta($iddoc)."\", \"#Documentos\", \"".encripta($idproyecto)."\")'></i></td>";
                            $documents .= "<td>".$no_orden."</td>";
                            if($liga_ext!='')
                                $documents .= "<td><a href='".$liga_ext."' target='_blank'>".$documento."</a></td>";
                            else
                                $documents .= "<td>".$documento."</td>";
                            $documents .= "<td>";
                                $documents .= "<i class='fa fa-trash' style='font-size:20px;cursor: pointer;' onclick='DocumentosxFicha(\"". encripta($id_ficha_sostenibilidad)."\", \"E\", \"".encripta($iddoc)."\", \"#Documentos\", \"".encripta($idproyecto)."\")'></i>&nbsp;";
                            $documents .= "</td>";
                            $documents .= "</tr>";
                            //$cont_docs++;
                        }
                        $documents .= "<tr><td colspan=3 class='text-right'><i class='fa fa-plus-circle' style='font-size:25px;cursor: pointer;color: #2bad30;' onclick='DocumentosxFicha(\"". encripta($id_ficha_sostenibilidad)."\", \"M\", 0, \"#Documentos\", \"".encripta($idproyecto)."\")'></i></td></tr>";
                        $documents .= "</tbody>";
                    $documents  .= "</table>";
                    $documents  .= '<div class="text-right"><a class="btn btn-danger" style="color:#FFF;margin-right: 1%;font-size: initial" onclick=\'ListaFichas("'.encripta($idproyecto).'","'.$titulo.'");\'> Cancelar</a></div>';
                echo $documents  .= "</div></div>";
            //echo $documents .= "</div>";
        }
        // Forma para editar o agregar el documento por ficha
        if($modal=='E_DC'){
            $id_documento = desencripta($_REQUEST['id_documento']);
            $doc = "";
            $doc_en = "";
            $liga_ex = "";
            $liga_in = "";
            $nombre_corto = "";
            $comentarios = "";
            $doc_publico = 0;
            if($id_documento>0){
                $Q_doc  = "SELECT documento, documento_en, liga_externa, liga_interna, nombre_corto, comentarios_internos, publico, no_orden ";
                $Q_doc .= "FROM tbl_documentos_x_ficha WHERE id=?";
                $preDocEdit = $_sql1->prepare($Q_doc);
                $preDocEdit->bind_param('i', $id_documento);
                $preDocEdit->execute();
                $preDocEdit->store_result();
                $preDocEdit->bind_result($d, $de, $le, $li, $nc, $ci, $pb , $no_orden);
                while ($preDocEdit->fetch()) {
                    $doc = $d;
                    $doc_en = $de;
                    $liga_ex = $le;
                    $liga_in = $li;
                    $nombre_corto = $nc;
                    $comentarios = $ci;
                    $doc_publico = $pb;
                    $num = $no_orden;
                }
            }
            
            $EditDoc = "<div class='row' style='padding: 5px;'>";
                $EditDoc .= "<form id='editdoc' name='editdoc'>";
                $EditDoc .= "<input type='hidden' name='id_documento' value='".encripta($id_documento)."'>";
                $EditDoc .= "<input type='hidden' name='action' value='G_DC'>";
                $EditDoc .= "<input type='hidden' name='id_ficha_sostenibilidad' value='". encripta($id_ficha_sostenibilidad)."'>";
                    $EditDoc .= "<div class='col col-sm-12'>";
                    $EditDoc .= "<table width='100%' class='table'>";
                        $EditDoc .= "<thead style='background-color: #337ab7; color: #FFF;'>";
                        $EditDoc .= "<tr>";
                            $EditDoc .= "<td colspan=2> Edici&oacute;n de documentos</td>";
                        $EditDoc .= "</tr>";
                        $EditDoc .= "</thead>";
                        $EditDoc .= "<tbody>";
                            $EditDoc .= "<tr>";
                                $EditDoc .= "<td><strong>Orden: </strong></td>";
                                $EditDoc .= "<td><input type='text' name='no_orden' id='no_orden' value='".$num."' class='form-control'></td>";
                            $EditDoc .= "</tr>";
                            $EditDoc .= "<tr>";
                                $EditDoc .= "<td><strong>Documento*: </strong></td>";
                                $EditDoc .= "<td><input type='text' name='documento' id='documento' value='".$doc."' class='form-control'></td>";
                            $EditDoc .= "</tr>";
                            $EditDoc .= "<tr>";
                                $EditDoc .= "<td><strong>Documento(ingles): </strong></td>";
                                $EditDoc .= "<td><input type='text' name='documento_en' id='documento_en' value='".$doc_en."' class='form-control'></td>";
                            $EditDoc .= "</tr>";
                            $EditDoc .= "<tr>";
                                $EditDoc .= "<td><strong>Nombre corto*: </strong></td>";
                                $EditDoc .= "<td><input type='text' name='nombre_corto' id='nombre_corto' value='".$nombre_corto."' class='form-control'></td>";
                            $EditDoc .= "</tr>";
                            $EditDoc .= "<tr>";
                                $EditDoc .= "<td><strong>Liga externa: </strong></td>";
                                $EditDoc .= "<td><input type='text' name='liga_ex' id='liga_ex' value='".$liga_ex."' class='form-control'></td>";
                            $EditDoc .= "</tr>";
                            $EditDoc .= "<tr>";
                                $EditDoc .= "<td><strong>Liga interna*: </strong></td>";
                                $EditDoc .= "<td><input type='text' name='liga_in' id='liga_in' value='".$liga_in."' class='form-control'></td>";
                            $EditDoc .= "</tr>";
                            $EditDoc .= "<tr>";
                                $EditDoc .= "<td><strong>Comentarios internos: </strong></td>";
                                $EditDoc .= "<td><textarea name='comentarios_in' id='comentarios_in' class='form-control' rows=5>".$comentarios."</textarea></td>";
                            $EditDoc .= "</tr>";
                            $EditDoc .= "<tr hidden>";
                                $EditDoc .= "<td><strong>Documento público: </strong></td>";
                                if($doc_publico==0)
                                    $EditDoc .= "<td><input type='checkbox' name='doc_publico' id='doc_publico' /></td>";
                                else
                                    $EditDoc .= "<td><input type='checkbox' name='doc_publico' id='doc_publico' checked /></td>";
                            $EditDoc .= "</tr>";
                            $EditDoc .= "<tr><td colspan='2' class='text-right'>";
                                $EditDoc .= "<a href='javascript:DocumentosxFicha(\"". encripta($id_ficha_sostenibilidad)."\", \"G\",\"". encripta($id_documento)."\",\"#Documentos\",\"". encripta($idproyecto)."\" )' class='btn btn-primary edit_doc' style='color:#FFF;'>Guardar</a>";
                                $EditDoc .= "<a href='javascript:DocumentosxFicha(\"". encripta($id_ficha_sostenibilidad)."\", \"L\",0,\"#Documentos\",\"". encripta($idproyecto)."\")' class='btn btn-danger cancel_doc' style='color:#FFF;'>Cancelar</a>";
                            $EditDoc .= "</td></tr>";
                        $EditDoc .= "</tbody>";
                    $EditDoc .= "</table>";
                    $EditDoc .= "</div>";
                $EditDoc .= "</form>";
            echo $EditDoc .= "</div>";
        }
        // Listado de buenas practicas por ficha
        if($modal=='L_BP'){
            $id_pro = desencripta($_REQUEST['idproyecto']);
            $titulo = $_REQUEST['titulo'];
                $practicas  = "<div class='row' style='padding: 5px;'><div class='col col-sm-12'>";
                    // Buenas practicas por atributo
                    $ij=1;
                    $practicas  .= "<table class='table'>";
                        $practicas  .= "<thead style='background-color: #337ab7; color: #FFF;'>";
                            $practicas  .= "<tr>";
                                $practicas  .= "<th>No.</th>";
                                $practicas  .= "<th>Pilar</th>";
                                $practicas  .= "<th>Atributo</th>";
                                $practicas  .= "<th>Buenas prácticas detectadas en atributos</th>";
                                $practicas  .= "<th>Publicada</th>";
                            $practicas  .= "</tr>";
                        $practicas  .= "</thead>";
                        $practicas  .= "<tbody>";
                        // Query para buenas practicas por atributo
                        $Q_BP_X_ATTR = "SELECT b.id, c.Titulo, d.buena_practica, d.publicada, d.id, c.Id, b.nombre_corto, b.nombre	";
                        $Q_BP_X_ATTR .= "FROM tbl_atributos_x_fichas a ";
                        $Q_BP_X_ATTR .= "INNER JOIN tbl_catalogo_atributos b on a.id_catalogo_atributo=b.id ";
                        $Q_BP_X_ATTR .= "INNER JOIN tbl_catalogo_dimensiones c on b.dimensionid=c.id ";
                        $Q_BP_X_ATTR .= "INNER JOIN tbl_buenas_practicas_x_atributo d on a.id=d.id_atributos_x_fichas ";
                        $Q_BP_X_ATTR .= "WHERE a.id_fichas_sostenibilidad=?";
                        $preBPxA = $_sql1->prepare($Q_BP_X_ATTR);
                        $preBPxA->bind_param('i', $id_ficha_sostenibilidad);
                        $preBPxA->execute();
                        $preBPxA->store_result();
                        $preBPxA->bind_result($id_buenaxattr, $dimensionxattr, $practicaxattr, $publicarxattr, $idbuenaspracticas, $idDimension, $nombreCortoAtributo, $nombreAtributo);
                        $cont_buenasxattr=1;
                        while ($preBPxA->fetch()) {
                            $practicas .= "<tr>";
                                $practicas .= "<td>".$cont_buenasxattr."</td>";
                                $practicas .= "<td>".str_replace('|', '', $dimensionxattr)."</td>";
                                $practicas .= "<td>".$nombreCortoAtributo."&nbsp;".$nombreAtributo."</td>";
                                $practicas .= "<td>".$practicaxattr."</td>";
                                if($publicarxattr==1)
                                    $practicas .= "<td><input type='checkbox' data-id='".$ij."' data-dimen='".$idDimension."' data-practica='".encripta($idbuenaspracticas)."' data-tipo=BAtributo checked onclick=\"elegirBuenaPractica(this,'".encripta($idbuenaspracticas)."','BAtributo',".$idDimension.")\"></></td>";
                                else
                                    $practicas .= "<td><input type='checkbox' data-id='".$ij."' data-dimen='".$idDimension."' data-practica='".encripta($idbuenaspracticas)."' data-tipo=BAtributo onclick=\"elegirBuenaPractica(this,'".encripta($idbuenaspracticas)."','BAtributo',".$idDimension.")\"></td>";
                            $practicas .= "</tr>";
                            $cont_buenasxattr++;
                            $ij++;
                        }
                        $practicas .= "</tbody>";
                    $practicas  .= "</table>";
                    $practicas  .= "<br/>";
                    // Buenas practicas por ficha
                    $practicas  .= "<table class='table' id='BPF'>";
                        $practicas  .= "<thead class='encabezado-fichas'>";
                            $practicas  .= "<tr>";
                                $practicas  .= "<th style='width:5%;'>No.</th>";
                                $practicas  .= "<th style='width:20%;'>Pilar</th>";
                                $practicas  .= "<th style='width:50%;'>Buenas prácticas agregadas manualmente</th>";
                                $practicas  .= "<th style='width:10%;' class='text-center'>Opciones</th>";
                                $practicas  .= "<th style='width:5%;' class='text-center'>Usar</th>";
                            $practicas  .= "</tr>";
                        $practicas  .= "</thead>";
                        $practicas  .= "<tbody>";
                            // Query para buenas practicas por ficha
                            $Q_BP  = "SELECT a.id, b.Titulo, a.practica, a.publicada, b.Id FROM tbl_buenas_practicas_x_ficha a ";
                            $Q_BP .= "INNER JOIN tbl_catalogo_dimensiones b on a.id_catalogo_dimensiones=b.id ";
                            $Q_BP .= "WHERE a.id_fichas_sostenibilidad=? ";
                            $preBPxF = $_sql1->prepare($Q_BP);
                            $preBPxF->bind_param('i', $id_ficha_sostenibilidad);
                            $preBPxF->execute();
                            $preBPxF->store_result();
                            $preBPxF->bind_result($id_buena, $dimension, $practica, $publicar, $id_dimension);
                            $cont_buenasxficha=1;
                            while ($preBPxF->fetch()) {
                                $practicas .= "<tr>";
                                    $practicas .= "<td>".$cont_buenasxficha."</td>";
                                    $practicas .= "<td>".str_replace('|', '',$dimension)."</td>";
                                    $practicas .= "<td>".$practica."</td>";
                                    $practicas .= "<td class='text-center'>";
                                        $practicas .= "<i class='fa fa-pencil-square-o' style='font-size:20px;cursor: pointer;' onclick='BuenasPracticasGeneral(\"". encripta($id_ficha_sostenibilidad)."\", \"M\", \"".encripta($id_buena)."\", \"#Mejores_practicas\", \"".encripta($idproyecto)."\")'></i>&nbsp;";
                                        $practicas .= "<i class='fa fa-trash' style='font-size:20px;cursor: pointer;' onclick='BuenasPracticasGeneral(\"". encripta($id_ficha_sostenibilidad)."\", \"E\", \"".encripta($id_buena)."\", \"#Mejores_practicas\", \"".encripta($idproyecto)."\")'></i>&nbsp;";
                                    $practicas .= "</td>";
                                    if($publicar)
                                        $practicas .= "<td class='text-center'><input type='checkbox' data-id='".$ij."' data-dimen='".$id_dimension."' data-practica=".encripta($id_buena)." data-tipo=BFicha checked onclick=\"elegirBuenaPractica(this,'".encripta($id_buena)."','BFicha',".$id_dimension.")\"></td>";
                                    else
                                        $practicas .= "<td class='text-center'><input type='checkbox' data-id='".$ij."' data-dimen='".$id_dimension."' data-practica=".encripta($id_buena)." data-tipo=BFicha onclick=\"elegirBuenaPractica(this,'".encripta($id_buena)."','BFicha',".$id_dimension.")\"></td>";
                                $practicas .= "</tr>";
                                $cont_buenasxficha++;
                                $ij++;
                            }
                        $practicas .= "</tbody>";
                    $practicas  .= "</table>";
                    $practicas  .= "<div id='BotonAgregarBPF' class='col col-sm-12 text-right'><i class='fa fa-plus-circle' onclick='BuenasPracticasGeneral(\"". encripta($id_ficha_sostenibilidad)."\", \"M\", \"0\", \"#Mejores_practicas\", \"".encripta($idproyecto)."\")' style='font-size:25px;cursor: pointer;color: #2bad30;'></i></div>";
                    $practicas  .= '<div class="text-right"><a class="btn btn-danger" style="color:#FFF;margin-right: 1%;font-size: initial" onclick=\'ListaFichas("'.encripta($id_pro).'","'.$titulo.'");\'> Cancelar</a><div>';
                echo $practicas  .= "</div></div>";
        }
        // Forma para editar o agregar une buena practica
        if($modal=='E_BP'){
            $id_buenapractica = desencripta($_REQUEST['id_buenapractica']);
            $id_dimension = 0;
            $buena_practica = "";
            $buena_practica_en = "";
            $publicar = 0;
            if($id_buenapractica>0){
                $Q_BP0 = "SELECT id_catalogo_dimensiones, practica, practica_en, publicada FROM tbl_buenas_practicas_x_ficha WHERE id=?";
                $preBPxA1 = $_sql1->prepare($Q_BP0);
                $preBPxA1->bind_param('i', $id_buenapractica);
                $preBPxA1->execute();
                $preBPxA1->store_result();
                $preBPxA1->bind_result($dim, $pra, $pra_en, $pub);
                while ($preBPxA1->fetch()) {
                    $id_dimension = $dim;
                    $buena_practica = $pra;
                    $buena_practica_en = $pra_en;
                    $publicar = $pub;
                }
            }
            $EditBP = "<div class='row' style='padding: 5px;'>";
                $EditBP .= "<form id='editbuenapractica' name='editbuenapractica'>";
                $EditBP .= "<input type='hidden' name='id_buena_practica' value='".encripta($id_buenapractica)."'>";
                $EditBP .= "<input type='hidden' name='action' value='G_BP'>";
                $EditBP .= "<input type='hidden' name='id_ficha_sostenibilidad' value='". encripta($id_ficha_sostenibilidad)."'>";
                    $EditBP .= "<div class='col col-sm-12'>";
                    $EditBP .= "<table width='100%' class='table'>";
                        $EditBP .= "<thead style='background-color: #acd9e7'>";
                        $EditBP .= "<tr>";
                            $EditBP .= "<td colspan=2> Edici&oacute;n de una buena pr&aacute;ctica de sostenibilidad del proyecto</td>";
                        $EditBP .= "</tr>";
                        $EditBP .= "<tr>";
                        $EditBP .= "<td colspan=2>Se muestra solo los pilares que estan dispinibles para agregar/modificar una buena practica por ficha</td>";
                        $EditBP .= "</tr>";
                        $EditBP .= "</thead>";
                        $EditBP .= "<tbody>";
                            $EditBP .= "<tr>";
                                $EditBP .= "<td><strong>Pilar*: </strong></td>";
                                $EditBP .= "<td><select name='id_dimension' id='id_dimension'  class='form-control'><option value='0'>--Selecciona Dimesión</option>";
                                $Q_Dim = "SELECT a.id, a.Titulo FROM tbl_catalogo_dimensiones a "
                                        . "WHERE NOT EXISTS(select id_fichas_sostenibilidad from tbl_buenas_practicas_x_ficha b where b.id_fichas_sostenibilidad=? AND b.id_catalogo_dimensiones=a.id) ";
                                if($id_buenapractica>0){
                                    $Q_Dim .= "OR a.id=? ";
                                }
                                
                                $p = $_sql1->prepare($Q_Dim);
                                if($id_buenapractica>0)
                                    $p->bind_param('ii', $id_ficha_sostenibilidad, $id_dimension);
                                else
                                    $p->bind_param('i', $id_ficha_sostenibilidad);
                                $p->execute();
                                $p->store_result();
                                $p->bind_result($iddimP, $dimensionP);
                                while ($p->fetch()) {
                                    if($iddimP==$id_dimension)
                                        $EditBP .= "<option value='". encripta($iddimP)."' selected>".$dimensionP."</option>";
                                    else
                                        $EditBP .= "<option value='". encripta($iddimP)."'>".$dimensionP."</option>";
                                }
                                $EditBP .= "</select></td>";
                            $EditBP .= "</tr>";
                            $EditBP .= "<tr>";
                                $EditBP .= "<td><strong>Buena pr&aacute;ctica en espa&ntilde;ol: </strong></td>";
                                $EditBP .= "<td><textarea name='practica' id='practica' class='form-control' rows=5>".$buena_practica."</textarea></td>";
                            $EditBP .= "</tr>";
                            $EditBP .= "<tr>";
                                $EditBP .= "<td><strong>Buena pr&aacute;ctica en ingl&eacute;s: </strong></td>";
                                $EditBP .= "<td><textarea name='practica_en' id='practica_en' class='form-control' rows=5>".$buena_practica_en."</textarea></td>";
                            $EditBP .= "</tr>";
                            $EditBP .= "<tr hidden>";
                                $EditBP .= "<td><strong>Usar: </strong></td>";
                                if($publicar==0)
                                    $EditBP .= "<td><input type='checkbox' name='publicar' id='publicar' /></td>";
                                else
                                    $EditBP .= "<td><input type='checkbox' name='publicar' id='publicar' checked /></td>";
                            $EditBP .= "</tr>";
                            $EditBP .= "<tr><td colspan='2' class='text-right'>";
                                $EditBP .= "<a href='javascript:BuenasPracticasGeneral(\"". encripta($id_ficha_sostenibilidad)."\", \"G\",\"". encripta($id_buenapractica)."\",\"#Mejores_practicas\",\"". encripta($idproyecto)."\")' class='btn btn-primary edit_doc' style='color:#FFF;'>Guardar</a>";
                                $EditBP .= "<a href='javascript:BuenasPracticasGeneral(\"". encripta($id_ficha_sostenibilidad)."\", \"L\",0,\"#Mejores_practicas\",\"". encripta($idproyecto)."\")' class='btn btn-danger cancel_doc' style='color:#FFF;'>Cancelar</a>";
                            $EditBP .= "</td></tr>";
                        $EditBP .= "</tbody>";
                    $EditBP .= "</table>";
                    $EditBP .= "</div>";
                $EditBP .= "</form>";
            echo $EditBP .= "</div>";
            
            
        }
        // Datos para la grafica de barras
        if($modal=='GRAFICA1'){
            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);
            error_reporting(E_ALL);
            $choices =array();

            $id_proyect = desencripta($_POST['id_proyecto']);
            $id_ficha_sostenibilidad = desencripta($_POST['id_ficha']);

            // Filtros Eliminar cadenas y que sea numerico
            // Remplazamos cualquier caracter y que sean puros numericos
            $id_proyect = filter_var($id_proyect, FILTER_SANITIZE_NUMBER_INT);
            $id_ficha_sostenibilidad = filter_var($id_ficha_sostenibilidad, FILTER_SANITIZE_NUMBER_INT);
            $lang = $_POST['lang'];
            $promedio_x_proyecto = "";
            $id_sector=0;
            $id_subsector=0;
            $id_macroetapa=0;
            // utilizamos la paraetrizacion de consultas
            $sql = "select id_sector, id_subsector, id_macroetapa, promedio from tbl_proyecto_x_dimension where id_sostenibilidad=? Order By id_dimension ";
            $stmt = $_sql1->prepare($sql); 
            $stmt->bind_param("i", $id_ficha_sostenibilidad);
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($a,$b,$c,$d);
            $array = array();
            while ($stmt->fetch()) {
                $id_sector = $a;
                $id_subsector = $b;
                $id_macroetapa = $c;
                array_push($array, (float)round($d,2));
            }
            $choices['promedio_x_proyecto']= $array;

            $promedio_x_proyecto_x_subsector = "";
            $query2 = "select promedio from tbl_proyecto_x_sector_x_dimension where id_sector=? and id_subsector=? and id_macroetapa=? order by id_dimension;";
            $result2 = $_sql1->prepare($query2);
            $result2->bind_param("iii", $id_sector, $id_subsector, $id_macroetapa);
            $result2->execute();
            $result2->store_result();
            $result2->bind_result($z);
            $array2 = array();
            while ($result2->fetch()) {
                array_push($array2, (float) round($z,2));
            }
            $choices['promedio_x_proyecto_subsector']=$array2;


            $tit_dim = "Titulo";
            if($lang=='en')
                $tit_dim = "Titulo_En";

            $rls2 = $_sql1->query("SELECT ".$tit_dim.", Id, color FROM tbl_catalogo_dimensiones order by Id");    
            $array3 = array();
            while($row3 = $rls2->fetch_assoc()){        
                array_push($array3, "".htmlspecialchars($row3["".$tit_dim.""])."");
            }
            $choices['dimensiones']=$array3;
            //$choices['query3'] = $query2;
            
            $choices['url_project']= (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]/?p=".$id_proyect;
            if($lang=='en'){
                $choices['url_project']= (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]/?language=en&p=".$id_proyect;
            }
            

            // total de proyectos con el subector y la macroetapa    
            $rls3 = $_sql1->prepare("select count(*) tot from tbl_general_attr_proyectos WHERE id_subsector=? and id_macroetapa=? and valor>=0 group by id_proyecto; ");
            $rls3->bind_param("ii", $id_subsector, $id_macroetapa );
            $rls3->execute();
            $rls3->store_result();
            $rls3->bind_result($i);
            $tot_pro = 0;
            while ($rls3->fetch()) {
                $tot_pro++;
            }

            $choices['total_proyectos'] = $tot_pro;

            echo json_encode($choices);
            
        }
        // Guardamos la grafica de barra
        if($modal=='SAVE1'){
            $pdf = $_POST['pdf'];
            if(sizeof($pdf)==0)
                $pdf = '';
            $img= $_POST['imggrafica'];
            $id_proyecto= $pdf.desencripta($_POST['id_proyecto']);
            $id_ficha_sostenibilidad= $pdf.desencripta($_POST['id_ficha']);
            $lang = $_POST['lang'];
            if($lang=='en'){
                $id_proyecto = $pdf.desencripta($_POST['id_proyecto']).'-en';
                $id_ficha_sostenibilidad= $pdf.desencripta($_POST['id_ficha']).'-en';
            }
            $img = str_replace('data:image/png;base64,', '', $img);
            $img = str_replace(' ', '+', $img);
            $data = base64_decode($img);
            $file = $_SERVER["DOCUMENT_ROOT"].'/wp-content/themes/enfold-child/sostenibilidad/graficas/'.$id_ficha_sostenibilidad.'_.png';
            $success = file_put_contents($file, $data);
        }
    }
}


function get_format($df) {

    $str = '';
    $str .= ($df->invert == 1) ? ' - ' : '';
    if ($df->y > 0) {
        // years
        $str .= ($df->y > 1) ? $df->y . ' A�os ' : $df->y . ' A�o ';
    } if ($df->m > 0) {
        // month
        $str .= ($df->m > 1) ? $df->m . ' Meses ' : $df->m . ' Mes ';
    } if ($df->d > 0) {
        // days
        $str .= ($df->d > 1) ? $df->d . ' D&iacute;as ' : $df->d . ' D&iacute;a ';
    } if ($df->h > 0) {
        // hours
        $str .= ($df->h > 1) ? $df->h . ' Hrs ' : $df->h . ' Hr ';
    } if ($df->i > 0) {
        // minutes
        $str .= ($df->i > 1) ? $df->i . ' Mins ' : $df->i . ' Min ';
    } if ($df->s > 0) {
        // seconds
        $str .= ($df->s > 1) ? $df->s . ' Seg ' : $df->s . ' Seg ';
    }

    return $str;
}

?>