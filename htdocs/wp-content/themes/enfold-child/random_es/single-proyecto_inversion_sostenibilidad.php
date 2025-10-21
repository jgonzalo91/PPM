<link rel="stylesheet" id="bootstrap-css" href="/wp-content/themes/enfold-child/sostenibilidad/Chart.min.css" type="text/css">
<script type="text/javascript" src="/wp-content/themes/enfold-child/sostenibilidad/Chart.min.js"></script>
<?php 
include './wp-content/themes/enfold-child/random/boton_class.php';
$id_proyect_ = get_the_ID();
$result_sos = $conn->prepare("SELECT id, resumen_en FROM tbl_fichas_sostenibilidad WHERE publicar_por_omision=1 AND publicada=1 AND id_datos_proyecto=?");
$result_sos->bind_param('i', $id_proyect_);
$result_sos->execute();
$result_sos->store_result();
$result_sos->bind_result($idficha, $resumenficha);
while ($result_sos->fetch()){
    $id_ficha_sostenibilidad=$idficha;
    $ingles=$resumenficha;
}

$result_ftns = $conn->prepare("SELECT documento_en, liga_externa FROM tbl_documentos_x_ficha WHERE id_fichas_sostenibilidad=? order by no_orden;");
$result_ftns->bind_param('i', $id_ficha_sostenibilidad);
$result_ftns->execute();
$result_ftns->store_result();
$result_ftns->bind_result($doc, $ext);
$n_botones=0;
if ($result_ftns) {
    $id_registro=0;
    while ($result_ftns->fetch()) {
        $btn[$id_registro] = new boton();
        $btn[$id_registro]->ingles = $doc;
        $btn[$id_registro]->url = $ext;
        $id_registro++;
    }
}

$n_botones = $id_registro;

if($n_botones>0){
    $_col = "col-sm-6";
    $col_ = "col-sm-6";
}else{
    $_col = "col-sm-12";
    $col_ = "hidden";
}

$pie_resumen_ingles = get_field('pie_resumen_ingles', 57797);
$titulo_ingles = get_field('titulo_resumen_sostenibilidad_ingles', 57797);
$titulo_fuentes_ingles = get_field('titulo_fuentes_sostenibilidad_ingles', 57797);
$mostrar_sostenibilidad = $_GET['so'];

?>

<style>
#canvas {
    width: 800px !important;
    height:400px !important
}
</style>
<canvas id="canvas" class="hidden"></canvas>


<div class="container">
    <input type="hidden" id="proyecto_selecionado" name="proyecto_selecionado" value="<?php echo $id_ficha_sostenibilidad ?>">
    <input type="hidden" id="mostar_sostenibilidad" name="mosta_rostenibilidad" value="<?php echo $mostrar_sostenibilidad ?>">
    <fieldset class="hub-proyecto">
        <div class="pull-left">
            <span class="glyphicon glyphicon-chevron-down" aria-hidden="true"
                  data-toggle="collapse" data-target="#collapseSostenibilidad"
                  ></span>
        </div>

        <legend align="center">
            <h4 id="tituloProyectosIndividual"><strong><?php if ($_GET['language'] == "en") echo "SUSTAINABLE INFORMATION";
else echo "SOSTENIBILIDAD"; ?></strong></h4>
        </legend>




        <div class="collapse" id="collapseSostenibilidad" style="padding: 10px;">
            <div class="row">                         
                <div class="col-sm-6">
                    <div class="col-sm-6" style="text-align:left;">
                        <img src="/wp-content/themes/enfold-child/assets/csspdf/img/proyectos-sostenibilidad.png" style="width:75%;vertical-align: middle; " class="img-fluid" alt="imagen sostentabilidad"><br>								
                    </div>
                    <div class="col-sm-6" style="padding-top:20px; vertical-align: middle;">       
                        <?php
                        $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]/wp-content/cache/tmp/pdf_sostenbilidad/EN_".$id_ficha_sostenibilidad."_SOS.pdf";
                        $ruta_pdf = './wp-content/cache/tmp/pdf_sostenbilidad/EN_'.$id_ficha_sostenibilidad.'_SOS.pdf';
                        if (file_exists($ruta_pdf)) {
                            echo '<a href="'.$actual_link.'"  onclick="ga(\'send\', \'event\' , \'sostenibilidad\' , \'descargaPDF\' ,\''.$proyecto.'\');" class="btn btn-primary btn-lg btn-sm btn-block" target="_blank" style="text-align: center;background-color: #008688;height: 30px; width: 267.33px; vertical-align: middle; color:#fff;padding: 6px; font-size: 12px;">Sustainability Datasheet </a>';
                        }else{
                            $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]/wp-content/cache/tmp/pdf_sostenbilidad/EN_".$id_proyect_."_SOS.pdf";
                            $ruta_pdf = './wp-content/cache/tmp/pdf_sostenbilidad/EN_'.$id_proyect_.'_SOS.pdf';
                            echo '<a href="'.$actual_link.'" onclick="ga(\'send\', \'event\' , \'sostenibilidad\' , \'descargaPDF\' ,\''.$proyecto.'\');" class="btn btn-primary btn-lg btn-sm btn-block" target="_blank" style="text-align: center;background-color: #008688;height: 30px; width: 267.33px; vertical-align: middle; color:#fff;padding: 6px; font-size: 12px;"> Sustainability Datasheet </a>';
                        }
                        ?>
                        <!--<form method="post" target="_blank">
                            <button type="submit" id="print-sostenibilidad" class="btn btn-primary btn-lg btn-sm btn-block" style="text-align: center;background-color: #008688;height: 30px; width: 267.33px; vertical-align: middle; color:#fff;padding: 6px; font-size: 12px;"> Sustainability Datasheet </button>
                            <input type="hidden" name="sostenbilidad_en_pdf" value="submitted">
                            <input type="hidden" name="id_post" value="<?php echo $id_proyect_; ?>">
                            <input type="hidden" name="id_sostenibilidad" value="<?php echo $id_ficha_sostenibilidad; ?>">
                            <input type="hidden" name="imgM" id="imgMm2" value="">
                            <input type="hidden" name="imgMap" id="imgMmap2" value="">
                        </form>-->
                    </div>
                </div>
            </div>
            <div class="row">                         
                <div class="<?php echo $_col; ?>">
                    <p style=" font-weight: bold;color: #008688;text-transform: uppercase;"><?php echo $titulo_ingles; ?></p>
                    <p style="color: black; text-align: justify; font-weight: normal; padding-right: 20px;"><?php echo $ingles; ?></p> 
                    <p style=" font-style: italic; font-size: .8em; text-align: justify; ">
                        <?php echo $pie_resumen_ingles; ?>
                    </p>
                </div>         
                <div class="<?php echo $col_; ?>">
                    <div class="col-sm-12"style="font-weight: bold;color: #008688;">
                        <p style=" font-weight: bold;color: #008688;">
                            <?php echo $titulo_fuentes_ingles; ?></P>                        
                    </div>
                    <div style="text-decoration: underline; vertical-align: top; padding-left: 2.5%;color:#6aa5d9;font-size: .82em;font-family: 'Open Sans', 'HelveticaNeue', 'Helvetica Neue', Helvetica, Arial, sans-serif; letter-spacing: 1px;  ">
                        <?php
                        $nn = 5;
                        $for_i = 0;
                        if ($n_botones <= 5){
                            $nn = $n_botones;
                            $for_i = 0;
                        }
                        for ($i = $for_i; $i < $nn; $i++) {
                            if(strlen($btn[$i]->url)==0){
                                if ($_GET['language'] == "en") echo"- " . $btn[$i]->ingles; else echo"- " . $btn[$i]->sp; echo "<br>";
                            }else{
                        ?>
                            <a style=" text-decoration: underline;font-family: 'Open Sans', 'HelveticaNeue', 'Helvetica Neue', Helvetica, Arial, sans-serif;  font-weight: normal;" target="_blank" 
                                href="<?php echo $btn[$i]->url ?>">
                                <?php 
                                    if ($_GET['language'] == "en") echo"- " . $btn[$i]->ingles;
                                else 
                                    echo"- " . $btn[$i]->sp; ?></a><br>
                        <?php
                            }}
                        if ($n_botones > 5)
                            echo "<div id='lbl_mostrar_mas'>"
                            . "<a href='#collapseSostenibilidad'> "
                                . "<span class='glyphicon glyphicon-menu-down' aria-hidden='true' data-toggle='collapse' data-target='#ver_mas'  onclick='mostrar_mas();'> "
                                    . "<font style='font-family:arial; color: #008688;'>Read more ...</font>"
                                . "</span>"
                            . "</a>  </div>";
                        ?>      

                        <div  id="ver_mas" class="collapse" name="ver_mas" style=" text-decoration: underline; letter-spacing: 1px; font-family: 'Open Sans', 'HelveticaNeue', 'Helvetica Neue', Helvetica, Arial, sans-serif;  font-weight: normal; " >
                            <?php
                            if ($n_botones > 5)
                                for ($i = 5; $i < $n_botones; $i++) {
                                    if(strlen($btn[$i]->url)==0){
                                if ($_GET['language'] == "en") echo"- " . $btn[$i]->ingles; else echo"- " . $btn[$i]->sp; echo "<br>";
                            }else{
                                    ?>
                                    <a style=" text-decoration: underline; letter-spacing: 1px; font-family: 'Open Sans', 'HelveticaNeue', 'Helvetica Neue', Helvetica, Arial, sans-serif;  font-weight: normal;" class='a' target="_blank" href="<?php echo $btn[$i]->url ?>"><?php if ($_GET['language'] == "en") echo"- " . $btn[$i]->ingles;
                                    else echo"- " . $btn[$i]->sp; ?></a><br>
                                <?php }} ?>
                        </div>
                        <div id='lbl_mostrar_menos' style="display: none;">
                            <a href="#collapseSostenibilidad">
                                <span class='glyphicon glyphicon-menu-up' aria-hidden='true' data-toggle='collapse' style='color: #008688;' data-target='#ver_mas' onclick='mostrar_mas();'>
                                    <font style='font-family:arial; color: #008688;'>Read less ...</font>
                                </span>
                            </a>
                        </div>

                    </div>
                </div>
                <div class="col-sm-5" >

                </div>

            </div> 
        </div>      
    </fieldset>
</div>

<script>// Función que permite switchear las etiquetas de mostrar mas o mostrar menos en el apartado de sostenibilidad By JlReyes S 10/May/2019
    function mostrar_mas()
    {
        if (jQuery("#ver_mas").is(":hidden")) {
            jQuery('#lbl_mostrar_mas').hide();
            jQuery('#lbl_mostrar_menos').show();
        } else {
            jQuery('#lbl_mostrar_menos').hide();
            jQuery('#lbl_mostrar_mas').show();
        }
    }
    
</script>