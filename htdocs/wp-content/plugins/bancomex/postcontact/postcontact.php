<?php

function addStyles_PostContact() {
    // Register the style for the form
    if (is_page(array('registro-contactos', 'investor-registration'))) {
        wp_enqueue_style('submitform-style', plugin_dir_url(__FILE__) . '/css/post-contact-style.css');
        wp_enqueue_style('bootstrap', plugin_dir_url(__FILE__) . '../bootstrap-3.3.7/css/bootstrap.min.css');

        wp_register_script('post-contact-jquery', plugin_dir_url(__FILE__) . '/js/post-contact-js.js', array('jquery'));
        wp_enqueue_script('post-contact-jquery');
        wp_enqueue_script('my-bootstrap-js', plugin_dir_url(__FILE__) . '../bootstrap-3.3.7/js/bootstrap.min.js', array('jquery'), '3.3.7', true);
        wp_enqueue_script('jquery-validate', plugin_dir_url(__FILE__) . '/js/jquery.form-validator.min.js', array('jquery'));


        // AUTOCOMPLETE EMPRESA
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-autocomplete');
        wp_localize_script('dynamic_select-ajax', 'dynamic_select_vars', array(
            'ajax_url' => admin_url('admin-ajax.php')
        ));
    }
}

function PostContact_form($content = null) {
    global $post;

    // We're outputing a lot of html and the easiest way 
    // to do it is with output buffering from php.
    ob_start();
    if ('POST' == $_SERVER['REQUEST_METHOD'] && !empty($_POST['action']) && $_POST['action'] == 'post')
        PostContact_form_add_post();
    $lang = get_locale();
    ?>
    <script type="text/javascript">
        (function ($) {
            function getsupport(selectedtype)
            {

                document.supportform.submit();
            }
            $(document).ready(function () {
                // Retreive fields SELECT
                $('select#acf-field-pais_reg_inversionista').val('<?php echo $_POST['pais_reg_inversionista']; ?>');
                $('select#acf-field-tipo_de_contacto_reg_inversionista').val('<?php echo $_POST['tipo_de_contacto_reg_inversionista']; ?>');
                $('select#acf-field-interes_en_proyectos_reg_inversionista').val('<?php echo $_POST['interes_en_proyectos_reg_inversionista']; ?>');
                $('select#acf-field-tipos_de_proyecto_reg_inversionista').val('<?php echo $_POST['tipos_de_proyecto_reg_inversionista']; ?>');
                $('select#acf-field-tipos_de_proyecto_reg_inversionista').val('<?php echo $_POST['tipos_de_proyecto_reg_inversionista']; ?>');

                $(".custombtn2").on('click', function ()
                {
                    //alert('click');
                    var num = $("#checkboxes_sectores input:checkbox:checked").length;
                    var numero = $("#checkboxes_sectores input:checked").length;
                    if (numero == 0) {
                        //alert('0');+
                        $("#checkboxes_sectores input:checkbox:checked").addClass('validateme');
                        $(".validateme")
                                .valAttr('', 'validate_checkbox_group')
                                .valAttr('qty', '1-10')
                                .valAttr('error-msg', 'minímo 1 elemento');

                    } else {
                        //alert('1');
                        $("#checkboxes_sectores input:checkbox").removeClass('validateme');
                        $("#checkboxes_sectores input:checkbox").removeAttr('data-validation');
                        $("#checkboxes_sectores input:checkbox").toggleClass('valid');

                    }

                });
                $("[name='etapa_del_ciclo_de_inversion_reg_inversionista[]']:eq(0)")
                        .valAttr('', 'validate_checkbox_group')
                        .valAttr('qty', '1-10')
                //.valAttr('error-msg','minímo 1 elemento');
                $("[name='etapa_del_ciclo_de_inversion_reg_inversionista1[]']:eq(0)")
                        .valAttr('', 'validate_checkbox_group')
                        .valAttr('qty', '1-10')
                //.valAttr('error-msg','minímo 1 elemento');
                if ($("#infraestructura_social_reg_inversionista").prop('checked')) {
                    $("#infraestructura_social_reg_inversionista").toggleClass('chanfles');
                    //alert('done');
                }


                var myLanguage = {
                    requiredField: 'Este campo es obligatorio',
                    errorTitle: 'Form submission failed!',
                    badEmail: 'Correo no válido',
                    groupCheckedTooFewStart: 'Selecciona al menos ',
                    groupCheckedRangeStart: 'Selecciona de entre'
                };
                var myLanguage2 = {
                    errorTitle: 'Form submission failed!'
                };
                var mylang = '<?php echo $lang ?>';

                if (mylang == 'en_US') {
                    $.validate({
                        language: myLanguage2,
                        form: '#postcontact_new_post'

                    });
                } else {
                    $.validate({
                        language: myLanguage,
                        form: '#postcontact_new_post'

                    });
                }
            });



        })(jQuery);
    </script>
    <div id="postcontact_form" class="">
    <?php do_action('postcontact-form-notice');
    $cap = uniqid();
    $cap_code = substr($cap, 6, 12);
    $_SESSION['captcha_'] = $cap_code;

    ?>
        <div class="simple-fep-inputarea">

            <!-- <form id="postcontact_new_post" name="new_post" method="post" action="<?php the_permalink(); ?>"> -->
            <form id="postcontact_new_post" name="new_post" method="post" action="http://localhost:7080/registro-contactos/">
                <div class = "panel panel-default">
                    <div class = "panel-heading">
                        <h3 class = "panel-title">
    <?php echo __("Información General", 'postcontact'); ?>
                        </h3>
                    </div>

                    <div class = "panel-body">

                        <div class="form-group col-sm-7">
                            <div class="form-group">
                                <label for="post-title" class="col-sm-2 control-label"><?php echo __("Nombre", 'postcontact'); ?></label>
                                <div class="col-sm-10">
                                    <input type="text" id ="fep-post-title" class="form-control" name="post_title" value="<?php echo isset($_POST['post_title']) ? $_POST['post_title'] : ''; ?>" class="form-control" data-validation="required"/>
                                    <input type="hidden" id="acf-field-folio_reg_inversionista" class="id_catalog form-control" name="folio_reg_inversionista" value="" placeholder="">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="empresa_reg_inversionista" class="col-sm-2 control-label"><?php echo __("Empresa", 'postcontact'); ?></label>
                                <div class="col-sm-10">
                                  <!-- <input type="text" id="acf-field-empresa_reg_inversionista" data-type="Array" data-id="acf-field-empresa_reg_inversionista" class="dynamic_select ui-autocomplete-input" name="empresa_reg_inversionista" value="<?php if (isset($_POST['empresa_reg_inversionista'])) {
        echo $_POST['empresa_reg_inversionista'];
    } ?>" autocomplete="off"> -->
                                    <input type="text" id="acf-field-empresa_reg_inversionista" data-type="Array" data-id="acf-field-empresa_reg_inversionista" class="dynamic_select ui-autocomplete-input" name="empresa_reg_inversionista" value="<?php if (isset($_POST['empresa_reg_inversionista'])) {
        echo $_POST['empresa_reg_inversionista'];
    } ?>" autocomplete="off">
                                    <script type="text/javascript">
                                        (function ($) {
                                            $(function () {
                                                $('#acf-field-empresa_reg_inversionista').autocomplete({
                                                    source: function (request, response) {
                                                        var cat_opts;
                                                        var $term = request.term;

                                                        if ($term.length > 3) {
                                                            $.ajax({
                                                                url: dynamic_select_vars.ajax_url,
                                                                type: 'post',
                                                                data: {
                                                                    action: 'retrive_options',
                                                                    post_type: 'cat_empresa',
                                                                    text_search: $term
                                                                },
                                                                success: function (data) {
                                                                    cat_opts = JSON.parse(data);
                                                                    response(cat_opts);

                                                                }
                                                            });
                                                        }
                                                    }
                                                });
                                            });
                                        })(jQuery);
                                    </script>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="correo_registro_reg_inversionista" class="col-sm-2 control-label">E-mail</label>
                                <div class="col-sm-10">
                                    <input type="text" id="acf-field-correo_registro_reg_inversionista" class="text form-control" name="correo_registro_reg_inversionista" value="<?php echo (isset($_POST['correo_registro_reg_inversionista'])) ? $_POST['correo_registro_reg_inversionista'] : ''; ?>" data-validation="email">
                                </div>
                            </div>
			

                        </div>

                        <div class="form-group col-sm-5">
                            <div class="form-group">
                                <label for="pais_reg_inversionista" class="col-sm-2 control-label"><?php echo __("País", 'postcontact'); ?></label>
                                <div class="col-sm-10">
                                    <select id="acf-field-pais_reg_inversionista" data-type="pais_reg_inversionista" data-id="acf-field-pais_reg_inversionista" class="id_nested_select" name="pais_reg_inversionista" data-validation="required"><option value="">- Select -</option> <?php fill_dropdown_Postcontact('pais_de_origen'); ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="tipo_de_contacto_reg_inversionista" class="col-sm-6 control-label"><?php echo __("Tipo de Contacto", 'postcontact'); ?></label>
                                <div class="col-sm-6">
                                    <select id="acf-field-tipo_de_contacto_reg_inversionista" data-type="reg_inversionistas" data-id="acf-field-tipo_de_contacto_reg_inversionista" class="id_nested_select" name="tipo_de_contacto_reg_inversionista" data-validation="required"><option value="">- Select -</option><?php fill_dropdown_Postcontact('cat_tipo_contacto'); ?>
                                    </select>										
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-12">
                                    <p>&nbsp;</p>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <div class = "panel panel-default">
                    <div class = "panel-heading">
                        <h3 class = "panel-title">
                            <?php echo __("Criterios de Selección de Proyectos", 'postcontact'); ?>

                    </div>

                    <div class = "panel-body">

                        <div class="form-group col-sm-6">
                            <div class="form-group">
                                <label for="interes_en_proyectos_reg_inversionista" class="col-sm-4 control-label"><?php echo __("Interés en Proyectos", 'postcontact'); ?></label>
                                <div class="col-sm-8">
                                    <select id="acf-field-interes_en_proyectos_reg_inversionista" data-type="reg_inversionistas" data-id="acf-field-interes_en_proyectos_reg_inversionista" class="id_nested_select" name="interes_en_proyectos_reg_inversionista" data-validation="required"><option value="">- Select -</option><?php fill_dropdown_Postcontact_tipo_inversion('cat_tipo_inversion'); ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="tipos_de_proyecto_reg_inversionista" class="col-sm-4 control-label"><?php echo __("Tipos de Proyecto", 'postcontact'); ?></label>
                                <div class="col-sm-8">
                                    <select id="acf-field-tipos_de_proyecto_reg_inversionista" data-type="reg_inversionistas" data-id="acf-field-tipos_de_proyecto_reg_inversionista" class="id_nested_select" name="tipos_de_proyecto_reg_inversionista" data-validation="required"><option value="">- Select -</option><?php fill_dropdown_Postcontact('cat_tipo_proyecto'); ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!--Inicia código de Ximena-->
                        <!--Monto minimo(versión escritorio)-->

                        <div class="form-group col-sm-6">
                            <div class="form-group  hidden-xs">
                                <label for="monto_minimo_reg_inversionista" class="col-sm-4 control-label"><?php echo __("Monto Mínimo", 'postcontact'); ?>

                                </label>
                                <div class="col-sm-5 hidden-xs">
                                    <input type="text" id="number-acf-field-monto_minimo_reg_inversionista" class="currency_bo" name="monto_minimo_reg_inversionista" value="<?php if (isset($_POST['monto_minimo_reg_inversionista'])) {
                            echo $_POST['monto_minimo_reg_inversionista'];
                        } ?>" placeholder=""  data-validation="required" placeholder="Ej. 10">
                                </div>
                                <div class="col-sm-3 hidden-xs">
                                    <span class="floatcoinme"><?php echo __("millones USD", 'postcontact'); ?></span>
                                </div>
                            </div>

                            <!--Monto máximo(versión escritorio)-->

                            <div class="form-group hidden-xs">
                                <label for="monto_maximo_reg_inversionista" class="col-sm-4 control-label"><?php echo __("Monto Máximo", 'postcontact'); ?>

                                </label>


                                <div class="col-sm-5 hidden-xs">
                                    <input type="text" id="number-acf-field-monto_maximo_reg_inversionista" class="currency_bo" name="monto_maximo_reg_inversionista" value="<?php if (isset($_POST['monto_maximo_reg_inversionista'])) {
                            echo $_POST['monto_maximo_reg_inversionista'];
                        } ?>" placeholder=""  data-validation="required" placeholder="Ej. 100">
                                </div>
                                <div class="col-sm-3 hidden-xs">
                                    <span class="floatcoinme"><?php echo __("millones USD", 'postcontact'); ?></span>
                                </div>
                            </div>

                            <!--Versió mobil-->
                            <!--Para el monto minimo -->
                            <div class="form-group col-sm-6 visible-xs">

                                <label for="monto_minimo_reg_inversionista1" class="col-sm-4 control-label" style="padding:1px"><?php echo __("Monto Mínimo", 'postcontact'); ?></label>
                                <div class="row visible-xs">
                                    <div class="col-xs-8 visible-xs" style="padding-left:15px;"">
                                        <input type="text" id="number-acf-field-monto_minimo_reg_inversionista1" class="currency_bo" name="monto_minimo_reg_inversionista1" value="<?php if (isset($_POST['monto_minimo_reg_inversionista1'])) {
                            echo $_POST['monto_minimo_reg_inversionista1'];
                        } ?>" placeholder="" data-validation="required" placeholder="Ej. 100">

                                    </div>
                                    <div class="col-xs-3 visible-xs" style="">
                                        <span class="floatcoinme"><?php echo __("millones USD", 'postcontact'); ?></span>
                                    </div>
                                </div>
                            </div>


                            <!--Para el monto máximo -->
                            <div class="form-group visible-xs" >
                                <label for="monto_maximo_reg_inversionista1" class="col-sm-4 control-label"><?php echo __("Monto Máximo", 'postcontact'); ?></label>

                                <div class="row visible-xs">
                                    <div class="col-xs-8 visible-xs" style="padding-left:30px;"">
                                        <input type="text" id="number-acf-field-monto_maximo_reg_inversionista1" class="currency_bo" name="monto_maximo_reg_inversionista1" value="<?php if (isset($_POST['monto_maximo_reg_inversionista1'])) {
                            echo $_POST['monto_maximo_reg_inversionista1'];
                        } ?>" placeholder=""  data-validation="required" placeholder="Ej. 100">

                                    </div>
                                    <div class="col-xs-3 visible-xs" style="">
                                        <span class="floatcoinme"><?php echo __("millones USD", 'postcontact'); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!--Termina código de Ximena-->

                        </div>
                        <div class="form-group col-sm-12">
                            <div class="form-group">
                                <label for="etapa_del_ciclo_de_inversion_reg_inversionista" class="col-sm-3 control-label" ><?php echo __("Etapa del ciclo de Inversión", 'postcontact'); ?></label>


                                <div class="col-sm-9 " align="left">

    <?php fill_checkboxlist_Postcontact_etapas();
    ?>

    <?php fill_checkboxlist_Postcontact_etapas1();
    ?>



                                    <script type="text/javascript">
                                        (function ($) {
                                            $(document).ready(function () {
                                                var lang = '<?php echo $lang; ?>';
                                                if (lang == 'en_US') {
                                                    $('.mycheckboxitem:nth-child(1) label').text("All stages");
                                                    $('#acf-field-interes_en_proyectos_reg_inversionista option:nth-child(2)').text("All");
                                                    $('#acf-field-tipos_de_proyecto_reg_inversionista option:nth-child(2)').text("All");
                                                } else {
                                                    $('.mycheckboxitem:nth-child(1) label').text("Todas las etapas");
                                                    $('#acf-field-interes_en_proyectos_reg_inversionista option:nth-child(2)').text("Todos");
                                                    $('#acf-field-tipos_de_proyecto_reg_inversionista option:nth-child(2)').text("Todos");
                                                }

                                            });
                                        })(jQuery);
                                    </script>
                                </div>
                            </div>
                        </div>
						
						<?php
  // Forzar traducción por locale
  $is_en    = (get_locale() === 'en_US'); // ajusta si usas otro, p.ej. en_GB
  $lbl_grp  = $is_en ? 'New Project Notifications' : 'Notificación de Proyectos Nuevos';
  $lbl_item = $is_en ? 'New Projects'              : 'Proyectos Nuevos';
?>
<div class="form-group col-sm-12">
  <div class="form-group">
    <label for="acf-field-proyectos_nuevos_fin-1" class="col-sm-3 control-label">
      <?php echo esc_html($lbl_grp); ?>
    </label>

    <div class="col-sm-9" style="text-align:left">
      <!-- Texto primero, checkbox después -->
      <label class="checkbox-inline" style="margin:0;">
        <?php echo esc_html($lbl_item); ?>
        <input
          id="acf-field-proyectos_nuevos_fin-1"
          type="checkbox"
          name="proyectos_nuevos_fin"
          value="1"
          class="validatenp"
          <?php
            if (isset($_POST['proyectos_nuevos_fin'])) {
              echo "checked='checked'";
            } else {
              $valor_guardado = get_field('proyectos_nuevos_fin', get_the_ID());
              if ($valor_guardado) echo "checked='checked'";
            }
          ?>
        />
      </label>
    </div>
  </div>
</div>


<style>
  /* Evita solapamiento de Bootstrap cuando el input va DESPUÉS del texto */
  #postcontact_form .checkbox-inline{
    display: inline-flex;       /* alinea texto y checkbox en una fila */
    align-items: center;
    gap: 8px;                   /* espacio entre texto y checkbox */
    padding-left: 0 !important; /* anula padding por defecto */
    white-space: normal;        /* permite salto de línea si lo necesita */
  }
  #postcontact_form .checkbox-inline input[type="checkbox"]{
    position: static !important; /* quita position:absolute de Bootstrap */
    float: none !important;
    margin: 0 !important;        /* sin desplazamientos negativos */
  }
</style>






                        <div class="form-group col-sm-12" id="checkboxes_sectores">
                            <p><strong><?php echo __("Sectores", 'postcontact'); ?></strong></p>
                            <div class="col-sm-4">

                                <div class="col-sm-12 containt">
                                    <label class="checkbox-inline underline"><input id="acf-field-electricidad_reg_inversionista-1" type="checkbox" class="checkme validateme" name="electricidad_reg_inversionista" value="1" <?php if (isset($_POST['electricidad_reg_inversionista'])) echo "checked='checked'"; ?>> <?php echo __("Electricidad", 'postcontact'); ?></label>
                                    <div class="sub-checkboxes"><?php fill_checkboxlist_Postcontact('4', 'electricidad_sub_reg_inversionista'); ?> <hr></div>
                                </div>

                                <div class="col-sm-12 containt">
                                    <label class="checkbox-inline underline"><input id="infraestructura_social_reg_inversionista" type="checkbox" name="infraestructura_social_reg_inversionista" class="validateme" value="1" <?php if (isset($_POST['infraestructura_social_reg_inversionista'])) echo "checked='checked'"; ?>> <?php echo __("Infraestructura Social", 'postcontact'); ?></label>
                                    <div class="sub-checkboxes"><?php fill_checkboxlist_Postcontact('3', 'infraestructura_sub-sectores'); ?><hr></div>
                                </div>

                                <div class="col-sm-12 containt">
                                    <label class="checkbox-inline underline"><input id="acf-field-mineria_reg_inversionista-1" type="checkbox" name="mineria_reg_inversionista" value="1" class="validateme" <?php if (isset($_POST['mineria_reg_inversionista'])) echo "checked='checked'"; ?>> <?php echo __("Minería", 'postcontact'); ?></label>
                                    <div class="sub-checkboxes"><?php fill_checkboxlist_Postcontact('9', 'mineria_sub-sectores'); ?><hr></div>
                                </div>

                            </div>
                            <div class="col-sm-4">

                                <div class="col-sm-12 containt">
                                    <label class="checkbox-inline underline"><input id="acf-field-hidrocarburos_reg_inversionista-1" type="checkbox" class="checkme validateme" name="hidrocarburos_reg_inversionista" value="1" <?php if (isset($_POST['hidrocarburos_reg_inversionista'])) echo "checked='checked'"; ?>> <?php echo __("Hidrocarburos", 'postcontact'); ?></label>
                                    <div class="sub-checkboxes"><?php fill_checkboxlist_Postcontact('7', 'hidrocarburos_sub-sectores'); ?><hr></div>
                                </div>

                                <div class="col-sm-12 containt">
                                    <label class="checkbox-inline underline"><input id="acf-field-agua_y_medio_ambiente_reg_inversionista-1" type="checkbox" name="agua_y_medio_ambiente_reg_inversionista" value="1" class="validateme" <?php if (isset($_POST['agua_y_medio_ambiente_reg_inversionista'])) echo "checked='checked'"; ?>> <?php echo __("Agua y Medio Ambiente", 'postcontact'); ?></label>
                                    <div class="sub-checkboxes"><?php fill_checkboxlist_Postcontact('5', 'agua_sub-sectores'); ?><hr></div>
                                </div>

                                <div class="col-sm-12 containt">
                                    <label class="checkbox-inline underline"><input id="acf-field-inmobiliario_y_turismo_reg_inversionista-1" type="checkbox" name="inmobiliario_y_turismo_reg_inversionista" value="1" class="validateme" <?php if (isset($_POST['inmobiliario_y_turismo_reg_inversionista'])) echo "checked='checked'"; ?>> <?php echo __("Inmobiliario y Turismo", 'postcontact'); ?></label>
                                    <div class="sub-checkboxes"><?php fill_checkboxlist_Postcontact('8', 'inmobiliario_sub-sectores'); ?><hr></div>
                                </div>

                            </div>
                            <div class="col-sm-4">

                                <div class="col-sm-12 containt">
                                    <label class="checkbox-inline underline"><input id="acf-field-transporte_reg_inversionista-1" type="checkbox" class="checkme validateme" name="transporte_reg_inversionista" value="1" <?php if (isset($_POST['transporte_reg_inversionista'])) echo "checked='checked'"; ?>> <?php echo __("Transporte", 'postcontact'); ?></label>
                                    <div class="sub-checkboxes"><?php fill_checkboxlist_Postcontact('6', 'transporte_sub-sectores'); ?><hr></div>
                                </div>

                                <div class="col-sm-12 containt">
                                    <label class="checkbox-inline underline"><input id="acf-field-industria_reg_inversionista-1" type="checkbox" name="industria_reg_inversionista" value="1" class="validateme" <?php if (isset($_POST['industria_reg_inversionista'])) echo "checked='checked'"; ?>> <?php echo __("Industria", 'postcontact'); ?></label>
                                    <div class="sub-checkboxes"><?php fill_checkboxlist_Postcontact('10', 'industria_sub-sectores'); ?><hr></div>
                                </div>

                                <div class="col-sm-12 containt">
                                    <label class="checkbox-inline underline"><input id="acf-field-telecomunicaciones_reg_inversionista-1" type="checkbox" name="telecomunicaciones_reg_inversionista" value="1" class="validateme" <?php if (isset($_POST['telecomunicaciones_reg_inversionista'])) echo "checked='checked'"; ?>> <?php echo __("Telecomunicaciones", 'postcontact'); ?></label>
                                    <div class="sub-checkboxes"><?php fill_checkboxlist_Postcontact('2', 'telecom_sub-sectores'); ?><hr></div>
                                </div>

                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="col-sm-4">&nbsp;</div>
                            <div class="col-sm-4 text-center">
                                
                                <p><strong><h5>CAPTCHA</h5></strong></p><br/>
                                <strong id="recaptcha" onselectstart="return false" oncontextmenu="return false" ><?php echo $cap_code;  ?></strong><br/>
                                <input name="captcha" type="text" class="form-control"  data-validation="required" >
                            </div>
                            <div class="col-sm-4">&nbsp;</div>
                        </div>
                        
                                            
                    
                    </div>
                </div>

                <div align="right">
                    <div class="col-sm-12 containt">
                        <label class="checkbox-inline underline"><input id="agree" type="checkbox" class="checkme" name="agree" value="1" data-validation="required"> <?php echo __("He leído y Acepto el", 'postcontact'); ?> 
    <?php if ($lang == 'en_US') { ?>
                                <a href="../notice-of-privacy/" target="_blank">
                    <?php } else { ?>
                                    <a href="../aviso-privacidad/" target="_blank">
    <?php } ?>	
                    <?php echo __("Aviso de Privacidad", 'postcontact'); ?></a>
                        </label>
                    </div>
                    <a id="sbmtbtn" class="avia-button  avia-icon_select-no avia-color-theme-color avia-size-small avia-position-right">
                        <input id="submit" type="submit" tabindex="3" class="btn custombtn2" value="<?php echo __("Registro", 'postcontact'); ?>" /></a>
                    <?php if ($lang == 'en_US') { ?>

                        <input id="acf-field-idioma_de_preferencia_reg_inversionista-en" type="hidden" name="idioma_de_preferencia_reg_inversionista" value="en" checked checked="checked" data-checked="checked">

    <?php } else { ?>
                        <input id="acf-field-idioma_de_preferencia_reg_inversionista-es" type="hidden" name="idioma_de_preferencia_reg_inversionista" value="es" checked checked="checked" data-checked="checked">
    <?php } ?>							
                    <input type="hidden" name="action" value="post" />
                    <input type="hidden" name="empty-description" id="empty-description" value="1"/>
    <?php wp_nonce_field('new-post'); ?>
                </div>
            </form>

        </div>

    </div> <!-- #simple-fep-postbox -->
    <?php
    // Output the content.
    $output = ob_get_contents();
    ob_end_clean();

    // Return only if we're inside a page. This won't list anything on a post or archive page. 
    if (is_page())
        return $output;
}

function send_mail_new_contact($content = null) {

    ob_start();

    $email = $_GET['mail'];
    $lang = $_GET['lang'];

    if ($email) {
        $args = array(
            'post_type' => 'reg_inversionistas',
            'meta_key' => 'correo_registro_reg_inversionista',
            'meta_value' => $email,
            'post_status' => array('draft', 'publish')
        );
        $the_query = new WP_Query($args);
    }
    //var_dump($matching_post);
    foreach ($the_query as $post) {
        $TitleContact = $post->post_title;
        $This_id = $post->ID;
    
    if ($TitleContact) {
        $empresa = get_field('empresa_reg_inversionista', $This_id);
        $args2 = array(
            'post_type' => 'cat_empresa',
            'meta_key' => 'id',
            'meta_value' => $empresa
        );
        $the_query2 = new WP_Query($args2);
        foreach ($the_query2 as $posts) {
            $TitleEmpresa = $posts->post_title;
        }

        echo new_contact_body($lang, $TitleContact, $TitleEmpresa, $puesto);
    }
    }
    // Output the content.
    $output = ob_get_contents();
    ob_end_clean();

    // Return only if we're inside a page. This won't list anything on a post or archive page. 
    return $output;
}

function new_contact_body($language, $name, $company, $puesto) {


    $pm = 'Proyectos México';
    $Wel = get_value_notifications('', '', '', 'alta_registro_0_cuerpo_bienvenido_0_espaniol');
    $tit = get_value_notifications('', '', '', 'alta_registro_0_cuerpo_0_espaniol');
    $per = get_value_notifications('', '', '', 'alta_registro_0_personalizado_0_espaniol');

    if ($Wel == '')
        $Wel = 'Bienvenido';
    if ($tit == '')
        $tit = ' Gabriel Su información de contacto y preferencias se han dado de alta satisfactoriamente:';
    $lbl_name = 'Nombre';
    $lbl_company = 'Empresa';
    $lbl_title = 'Puesto';
    $url = 'https://www.proyectosmexico.gob.mx/';
    $footer = 'Si desea modificar sus preferencias o dejar de recibir notificaciones, puede solicitarlo al correo <a href="mailto:proyectosmexico@banobras.gob.mx">proyectosmexico@banobras.gob.mx</a>';
    if ($language == 'en') {
        $pm = 'Mexico Projects Hub';
        $Wel = get_value_notifications('', '', '', 'alta_registro_0_cuerpo_bienvenido_0_ingles');
        $tit = get_value_notifications('', '', '', 'alta_registro_0_cuerpo_0_ingles');
        $per = get_value_notifications('', '', '', 'alta_registro_0_personalizado_0_ingles');
        if ($Wel == '')
            $Wel = 'Welcome';
        if ($tit == '')
            $tit = 'Your contact information and preferences have been successfully registered:';
        $lbl_name = 'Name';
        $lbl_company = 'Company';
        $lbl_title = 'Title';
        $url = 'https://www.proyectosmexico.gob.mx/en/home/';
        $footer = 'If you need to change your preferences or stop future notifications, please contact <a href="mailto:proyectosmexico@banobras.gob.mx">proyectosmexico@banobras.gob.mx</a>';
    }



    $header = '
		<table class="email_table" width="100%" border="0" cellspacing="0" cellpadding="0" style="box-sizing: border-box;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;width: 100%;min-width: 100%;">
	        <tbody>
	            <tr>
	                <td class="email_body tc" style="box-sizing: border-box;vertical-align: top;line-height: 100%;text-align: center;background-color: #dbe5ea;font-size: 0 !important;">
	                    <div class="email_container" style="box-sizing: border-box;font-size: 0;display: inline-block;width: 100%;vertical-align: top;text-align: center;line-height: inherit;min-width: 0 !important;">
	                        <table class="content_section" width="100%" border="0" cellspacing="0" cellpadding="0" style="box-sizing: border-box;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;width: 100%;min-width: 100%;">
	                            <tbody>
	                                <tr>
	                                    <td class="content_cell" style="box-sizing: border-box;vertical-align: top;width: 100%;background-color: #ffffff;font-size: 0;text-align: center;padding-left: 16px;padding-right: 16px;line-height: inherit;min-width: 0 !important;">
	                                        <div class="email_row" style="box-sizing: border-box;font-size: 0;display: block;width: 100%;vertical-align: top;margin: 0 auto;text-align: center;clear: both;line-height: inherit;min-width: 0 !important;">
	                                            <div class="col_6" style="box-sizing: border-box;font-size: 0;display: inline-block;width: 100%;vertical-align: top;max-width: 100%;line-height: inherit;min-width: 0 !important;">
	                                                <table class="column" width="100%" border="0" cellspacing="0" cellpadding="0" style="box-sizing: border-box;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;width: 100%;min-width: 100%;">
	                                                    <tbody>
	                                                        <tr>
	                                                            <td class="column_cell px pte tc" style="box-sizing: border-box;vertical-align: top;width: 100%;min-width: 100%;padding-top: 0;padding-bottom: 16px;font-family: Helvetica, Arial, sans-serif;font-size: 16px;line-height: 23px;color: #616161;mso-line-height-rule: exactly;text-align: center;padding-left: 16px;padding-right: 16px;">
	                                                            	<h1 class="mb_xxs" style="color: #3e484d;margin-left: 0;margin-right: 0;margin-top: 20px;margin-bottom: 4px;padding: 0;font-weight: bold;font-size: 32px;line-height: 42px;">' . $pm . '</h1>
	                                                            </td>
	                                                        </tr>
	                                                    </tbody>
	                                                </table>
	                                            </div>
	                                        </div>
	                                    </td>
	                                </tr>
	                            </tbody>
	                        </table>
	                    </div>
	                </td>
	            </tr>
	        </tbody>
	    </table>';
    if ($per != '') {
        $header .= '
	    	<table class="email_table" width="100%" border="0" cellspacing="0" cellpadding="0" style="box-sizing: border-box;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;width: 100%;min-width: 100%;">
		        <tbody>
		            <tr>
		                <td class="email_body tc" style="box-sizing: border-box;vertical-align: top;line-height: 100%;background-color: #dbe5ea;font-size: 0 !important;">
		                    <div class="email_container" style="box-sizing: border-box;font-size: 0;display: inline-block;width: 100%;vertical-align: top;text-align: center;line-height: inherit;min-width: 0 !important;">
		                        <table class="content_section" width="100%" border="0" cellspacing="0" cellpadding="0" style="box-sizing: border-box;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;width: 100%;min-width: 100%;">
		                            <tbody>
		                                <tr>
		                                    <td class="" style="box-sizing: border-box;vertical-align: top;width: 100%;background-color: #ffffff;font-size: 0;line-height: inherit;min-width: 0 !important;">
		                                        <div class="" style="box-sizing: border-box;font-size: 0;display: block;width: 100%;vertical-align: top;margin: 0 auto;clear: both;line-height: inherit;min-width: 0 !important;">
		                                            <div class="" style="box-sizing: border-box;font-size: 0;display: inline-block;width: 100%;vertical-align: top;line-height: inherit;min-width: 0 !important;">
		                                                <table class="column" width="100%" border="0" cellspacing="0" cellpadding="0" style="box-sizing: border-box;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;width: 100%;min-width: 100%;">
		                                                    <tbody>
		                                                        <tr>
		                                                            <td class="column_cell px pte tc" style="box-sizing: border-box;vertical-align: top;width: 100%;min-width: 100%;padding-top: 0;padding-bottom: 16px;font-family: Helvetica, Arial, sans-serif;font-size: 16px;line-height: 23px;color: #616161;mso-line-height-rule: exactly;text-align: left;padding-left: 16px;padding-right: 16px;">
		                                                            	<small>' . $per . '</small>
		                                                            </td>
		                                                        </tr>
		                                                    </tbody>
		                                                </table>
		                                            </div>
		                                        </div>
		                                    </td>
		                                </tr>
		                            </tbody>
		                        </table>
		                    </div>
		                </td>
		            </tr>
		        </tbody>
		    </table>';
    }
    $header .= '
		<table class="email_table" width="100%" border="0" cellspacing="0" cellpadding="0" style="box-sizing: border-box;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;width: 100%;min-width: 100%;">
	        <tbody>
	            <tr>
	                <td class="email_body tc" style="box-sizing: border-box;vertical-align: top;line-height: 100%;text-align: center;background-color: #dbe5ea;font-size: 0 !important;">
	                    <div class="email_container" style="box-sizing: border-box;font-size: 0;display: inline-block;width: 100%;vertical-align: top;text-align: center;line-height: inherit;min-width: 0 !important;">
	                        <table class="content_section" width="100%" border="0" cellspacing="0" cellpadding="0" style="box-sizing: border-box;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;width: 100%;min-width: 100%;">
	                            <tbody>
	                                <tr>
	                                    <td class="content_cell" style="box-sizing: border-box;vertical-align: top;width: 100%;background-color: #ffffff;font-size: 0;text-align: center;padding-left: 16px;padding-right: 16px;line-height: inherit;min-width: 0 !important;">
	                                        <div class="email_row" style="box-sizing: border-box;font-size: 0;display: block;width: 100%;vertical-align: top;margin: 0 auto;text-align: center;clear: both;line-height: inherit;min-width: 0 !important;">
	                                            <div class="col_6" style="box-sizing: border-box;font-size: 0;display: inline-block;width: 100%;vertical-align: top;max-width: 100%;line-height: inherit;min-width: 0 !important;">
	                                                <table class="column" width="100%" border="0" cellspacing="0" cellpadding="0" style="box-sizing: border-box;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;width: 100%;min-width: 100%;">
	                                                    <tbody>
	                                                        <tr>
	                                                            <td class="column_cell px pte tc" style="box-sizing: border-box;vertical-align: top;width: 100%;min-width: 100%;padding-top: 0;padding-bottom: 16px;font-family: Helvetica, Arial, sans-serif;font-size: 16px;line-height: 23px;color: #616161;mso-line-height-rule: exactly;text-align: center;padding-left: 16px;padding-right: 16px;">';
    if ($Wel != '') {
        $header .= '<h1 class="mb_xxs" style="color: #ffffff;margin-left: 0;margin-right: 0;margin-top: 20px;margin-bottom: 4px;padding: 0;font-weight: bold;font-size: 28px;line-height: 42px; text-align:left; background-color:#35BEC5; padding-left:20px;">' . $Wel . '</h1>';
    }
    if ($tit != '') {
        $header .= '
	                                                                	<p class="lead" style="font-family: Helvetica, Arial, sans-serif;font-size: 19px;line-height: 27px;color: #a7b1b6;mso-line-height-rule: exactly;display: block;margin-top: 0;margin-bottom: 16px;">' . $tit . ' </p>';
    }
    $header .= '
	                                                            </td>
	                                                        </tr>
	                                                    </tbody>
	                                                </table>
	                                            </div>
	                                        </div>
	                                    </td>
	                                </tr>
	                            </tbody>
	                        </table>
	                    </div>
	                </td>
	            </tr>
	        </tbody>
	    </table>';

    $header .= '
	    <table class="email_table" width="100%" border="0" cellspacing="0" cellpadding="0" style="box-sizing: border-box;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;width: 100%;min-width: 100%;">
	        <tbody>
	            <tr>
	                <td class="email_body tc" style="box-sizing: border-box;vertical-align: top;line-height: 100%;text-align: center;background-color: #dbe5ea;font-size: 0 !important;">
	                    <div class="email_container" style="box-sizing: border-box;font-size: 0;display: inline-block;width: 100%;vertical-align: top;text-align: left;line-height: inherit;min-width: 0 !important;">
	                        <table class="content_section" width="100%" border="0" cellspacing="0" cellpadding="0" style="box-sizing: border-box;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;width: 100%;min-width: 100%;">
	                            <tbody>
	                                <tr>
	                                    <td class="content_cell" style="box-sizing: border-box;vertical-align: top;width: 100%;background-color: #ffffff;font-size: 0;text-align: center;padding-left: 16px;padding-right: 16px;line-height: inherit;min-width: 0 !important;">
	                                        <div class="email_row" style="box-sizing: border-box;font-size: 0;display: block;width: 100%;vertical-align: top;margin: 0 auto;text-align: center;clear: both;line-height: inherit;min-width: 0 !important;">
	                                            <div class="col_6" style="box-sizing: border-box;font-size: 0;display: inline-block;width: 100%;vertical-align: top;max-width:100%;line-height: inherit;min-width: 0 !important;">
	                                                <table class="column" width="100%" border="0" cellspacing="0" cellpadding="0" style="box-sizing: border-box;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;width: 100%;min-width: 100%;">
	                                                    <tbody>
	                                                        <tr>
	                                                            <td class="column_cell px pte tc" style="box-sizing: border-box;vertical-align: top;width: 100%;min-width: 100%;padding-top: 0;padding-bottom: 16px;font-family: Helvetica, Arial, sans-serif;font-size: 16px;line-height: 23px;color: #616161;mso-line-height-rule: exactly;text-align: left;padding-left: 16px;padding-right: 16px;">
	                                                            	<strong>' . $lbl_name . '</strong><br>
                                                                        ' . $name . '
                                                                        <br>
                                                                        <strong>' . $lbl_company . '</strong><br>
                                                                        ' . $company . '
                                                                        <br>
	                                                            </td>
	                                                        </tr>
	                                                    </tbody>
	                                                </table>
	                                            </div>
	                                        </div>
	                                    </td>
	                                </tr>
	                            </tbody>
	                        </table>
	                    </div>
	                </td>
	            </tr>
	        </tbody>
	    </table>
	    <table class="email_table" width="100%" border="0" cellspacing="0" cellpadding="0" style="box-sizing: border-box;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;width: 100%;min-width: 100%;">
	        <tbody>
	            <tr>
	                <td class="email_body email_end tc" style="box-sizing: border-box;vertical-align: top;line-height: 100%;text-align: center;background-color: #dbe5ea;font-size: 0 !important;">
	                    <!--[if (mso)|(IE)]><table width="632" border="0" cellspacing="0" cellpadding="0" align="center" style="vertical-align:top;width:100%;Margin:0 auto;"><tbody><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
	                    <div class="email_container" style="box-sizing: border-box;font-size: 0;display: inline-block;width: 100%;vertical-align: top;text-align: center;line-height: inherit;min-width: 0 !important;">
	                        <table class="content_section" width="100%" border="0" cellspacing="0" cellpadding="0" style="box-sizing: border-box;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;width: 100%;min-width: 100%;">
	                            <tbody>
	                                <tr>
	                                    <td class="content_cell footer_c default_b pt pb" style="box-sizing: border-box;vertical-align: top;width: 100%;background-color: #475359;font-size: 0;text-align: center;padding-left: 16px;padding-right: 16px;padding-top: 16px;padding-bottom: 16px;line-height: inherit;min-width: 0 !important;">
	                                        <div class="email_row" style="box-sizing: border-box;font-size: 0;display: block;width: 100%;vertical-align: top;margin: 0 auto;text-align: center;clear: both;line-height: inherit;min-width: 0 !important;max-width: 100%">
	                                        <!--[if (mso)|(IE)]><table  border="0" cellspacing="0" cellpadding="0" align="center" style="vertical-align:top;width:100%;Margin:0 auto;"><tbody><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
	                                            <div class="col_6" style="box-sizing: border-box;font-size: 0;display: inline-block;width: 100%;vertical-align: top;max-width:100%;line-height: inherit;min-width: 0 !important;">
	                                                <table class="column" width="100%" border="0" cellspacing="0" cellpadding="0" style="box-sizing: border-box;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;width: 100%;min-width: 100%;">
	                                                    <tbody>
	                                                        <tr>
	                                                            <td class="column_cell px pt_xs pb_0 tc sc" style="box-sizing: border-box;vertical-align: top;width: 100%;min-width: 100%;padding-top: 8px;padding-bottom: 0;font-family: Helvetica, Arial, sans-serif;font-size: 16px;line-height: 23px;color: #ffffff;mso-line-height-rule: exactly;text-align: center;padding-left: 16px;padding-right: 16px;">
	                                                                <p class="imgr imgr44 mb_xs" style="font-family: Helvetica, Arial, sans-serif;font-size: 0;line-height: 100%;color: #ffffff;mso-line-height-rule: exactly;display: block;margin-top: 0;margin-bottom: 8px;clear: both;"><a href="' . $url . '" style="text-decoration: underline;line-height: 1;color: #ffffff;"><img src="https://www.proyectosmexico.gob.mx/wp-content/uploads/2018/04/logo-dark.png" width="44" height="44" alt="' . $pm . '" style="outline: none;border: 0;text-decoration: none;-ms-interpolation-mode: bicubic;clear: both;line-height: 100%;max-width: 44px;margin-left: auto;margin-right: auto;width: 100% !important;height: auto !important;"/></a>
	                                                                </p>
	                                                                <p class="small mb_xxs" style="font-family: Helvetica, Arial, sans-serif;font-size: 14px;line-height: 20px;color: #ffffff;mso-line-height-rule: exactly;display: block;margin-top: 0;margin-bottom: 4px;">
                                                                    ' . $footer . '
                                                                    </p>
	                                                                <p class="small mb_xxs" style="font-family: Helvetica, Arial, sans-serif;font-size: 14px;line-height: 20px;color: #ffffff;mso-line-height-rule: exactly;display: block;margin-top: 0;margin-bottom: 4px;"><span style="line-height: inherit;color: #ffffff;">® BANOBRAS ' . date('Y') . '</span> <span style="line-height: inherit;">&nbsp; • &nbsp;</span> <a href="' . $url . '" style="text-decoration: underline;line-height: inherit;color: #ffffff;"><span style="text-decoration: underline;line-height: inherit;color: #6dd6db;">' . $pm . '</span></a> <span style="line-height: inherit;"></span></p>
	                                                            </td>
	                                                        </tr>
	                                                    </tbody>
	                                                </table>
	                                            </div>
	                                        <!--[if (mso)|(IE)]></td></tr></tbody></table><![endif]-->
	                                        </div>
	                                    </td>
	                                </tr>
	                            </tbody>
	                        </table>
	                    </div>
	                    <!--[if (mso)|(IE)]></td></tr></tbody></table><![endif]-->
	                </td>
	            </tr>
	        </tbody>
	    </table>';
    return $header;
}

function fill_dropdown_Postcontact_ciudad($value, $key) {
    $args = array(
        'post_type' => $value,
        'posts_per_page' => -1,
        'meta_query' => array(
            array(
                'key' => 'parent_id', // name of custom field
                'value' => $key, // matches exaclty "123", not just 123. This prevents a match for "1234"
                'compare' => 'LIKE'
            )
        ),
        'orderby' => 'title',
        'order' => 'ASC'
    );
    //$my_query = null;
    $query = new WP_Query($args);
    foreach ($query->posts as $post) {
        //$values[$post->ID ] = get_the_title( $post->ID );
        echo '<option value="' . get_field('id', $post->ID) . '">' . get_the_title($post->ID) . '</option>';
    }
}

function fill_dropdown_Postcontact($value) {
    $args = array(
        'post_type' => $value,
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC'
    );
    //$my_query = null;
    $query = new WP_Query($args);
    foreach ($query->posts as $post) {
        //$values[$post->ID ] = get_the_title( $post->ID );
        echo '<option value="' . get_field('id', $post->ID) . '">' . get_the_title($post->ID) . '</option>';
    }
}

function fill_dropdown_Postcontact_tipo_inversion($value) {
    $args = array(
        'post_type' => $value,
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC'
    );
    //$my_query = null;
    $query = new WP_Query($args);
    foreach ($query->posts as $post) {
        if (get_field('id', $post->ID) != 4) {
            //$values[$post->ID ] = get_the_title( $post->ID );
            echo '<option value="' . get_field('id', $post->ID) . '">' . get_the_title($post->ID) . '</option>';
        }
    }
}

function fill_checkboxlist_Postcontact($value, $name) {
    /* if ( defined( 'POLYLANG_VERSION' ) ) {
      $Dlang = 'es_MX';
      } */
    $args = array(
        'post_type' => 'cat_subsector_inv',
        'posts_per_page' => -1,
        /* 'lang' => $Dlang, */
        'meta_query' => array(
            array(
                'key' => 'parent_id', // name of custom field
                'value' => $value, // matches exaclty "123", not just 123. This prevents a match for "1234"
                'compare' => 'LIKE'
            )
        ),
        'order_by' => 'title',
        'order' => 'ASC',
    );
    //$my_query = null;
    $query = new WP_Query($args);
    foreach ($query->posts as $post) {
        echo '<div class="checkbox">';
        echo '<label><input type="checkbox" id="' . $name . '[]" name="' . $name . '[]" class="validateme" value="' . get_field('id', $post->ID) . '" >' . get_the_title($post->ID) . '</label>';
        echo '</div>';
    }
}

/* este es el que voy a ocupar */

function fill_checkboxlist_Postcontact_etapas() {
    $args = array(
        'post__not_in' => array(1787, 1788), //mover pospuesto y cancelado
        'post_type' => 'catalogo_etapas',
        'posts_per_page' => -1,
        'order_by' => 'title',
        'order' => 'DESC',
    );

    //$my_query = null; Version Escritorio
    $query = new WP_Query($args);

    foreach ($query->posts as $post) {


        echo '

		<div style="display:inline-block;" class="mycheckboxitem hidden-xs">
			<label class="mycheckbox">
				' . get_the_title($post->ID) . '

			</label>
			<input type="checkbox" id="etapa_del_ciclo_de_inversion_reg_inversionista[]" name="etapa_del_ciclo_de_inversion_reg_inversionista[]" 
					data-validation="checkbox_group"  

    				value="' . get_field('id', $post->ID) . '">&nbsp; | &nbsp;
			
			<span class="innercheck ' . $post->ID . '"></span>

		</div>
			';
    }
}

/* este es el que voy a ocupar */

function fill_checkboxlist_Postcontact_etapas1() {
    $args1 = array(
        'post__not_in' => array(1787, 1788), //mover pospuesto y cancelado
        'post_type' => 'catalogo_etapas',
        'posts_per_page' => -1,
        'order_by' => 'title',
        'order' => 'DESC',
    );


    //$my_query = null; Para la version mobil

    $query1 = new WP_Query($args1);

    echo '<div class="col-sm-12 containt visible-xs" >';

    foreach ($query1->posts as $post) {
        echo '

				<div class="mycheckboxitem" style="display:inline; width:400px; hight: 300px; position: relative;" class="mycheckboxitem hidden-xs" >
			 	<input type="checkbox" id="etapa_del_ciclo_de_inversion_reg_inversionista[]" name="etapa_del_ciclo_de_inversion_reg_inversionista1[]" data-validation="checkbox_group" value="' . get_field('id', $post->ID) . '">	
				<label class="mycheckbox" style=" hight: 700px; padding:2px" >' . get_the_title($post->ID) . '</label><span class="innercheck ' . $post->ID . '"></span>
			 <br>

				</div>
			

				
			';
    }
    echo '</div>';
}

// if(isset($_POST["tape"]) && is_array($_POST["tape"]) && in_array(get_the_title($post->ID), $_POST["tape"])) echo "checked" ;
function PostContact_form_errors() {
    global $error_array;
    foreach ($error_array as $error) {
        echo '<p class="PostContact-form-error">' . $error . '</p>';
    }
}

function PostContact_form_notices() {
    global $notice_array;
    foreach ($notice_array as $notice) {
        echo '<p class="postcontact-form-notice">' . $notice . '</p>';
    }
}

function PostContact_form_add_post() {
    if ('POST' == $_SERVER['REQUEST_METHOD'] && !empty($_POST['action']) && $_POST['action'] == 'post') {

        $bmxt_defaults = array(
            'post_type' => 'reg_inversionistas',
            'posts_per_page' => 1,
            'meta_key' => 'folio_reg_inversionista',
            'orderby' => 'meta_value_num',
            'post_status' => array('draft', 'publish'),
            'order' => 'DESC'
        );
        $bmxt_query = new WP_Query($bmxt_defaults);
        $first_id = $bmxt_query->post->ID;
        $new_value = get_post_meta($first_id, 'folio_reg_inversionista', true);
        $value_folio = intval($new_value) + 1;

        $r_ = $_SESSION['captcha_'];
        $user_id = $current_user->ID;
        $post_title = $_POST['post_title'];
        //$folio     		= $_POST['folio_reg_inversionista'];
        $empresa_d = $_POST['empresa_reg_inversionista'];
        $correo = $_POST['correo_registro_reg_inversionista'];
        $tipo_contacto = $_POST['tipo_de_contacto_reg_inversionista'];
        $pais = $_POST['pais_reg_inversionista'];
        $interes_proyectos = $_POST['interes_en_proyectos_reg_inversionista'];
        $tipos_proyecto = $_POST['tipos_de_proyecto_reg_inversionista'];
        $etapa_ciclo = $_POST['etapa_del_ciclo_de_inversion_reg_inversionista'];
        $etapa_ciclo1 = $_POST['etapa_del_ciclo_de_inversion_reg_inversionista1'];
        $monto_min = $_POST['monto_minimo_reg_inversionista']; //lo que captura el usuario
        $monto_max = $_POST['monto_maximo_reg_inversionista'];
        $monto_min1 = $_POST['monto_minimo_reg_inversionista1'];
        $monto_max1 = $_POST['monto_maximo_reg_inversionista1'];
        $check_electricidad = $_POST['electricidad_reg_inversionista'];
        $elec_subsec = $_POST['electricidad_sub_reg_inversionista'];
        $check_infra = $_POST['infraestructura_social_reg_inversionista'];
        $check_mineria = $_POST['mineria_reg_inversionista'];
        $check_hidro = $_POST['hidrocarburos_reg_inversionista'];
        $hid_subsec = $_POST['hidrocarburos_sub-sectores'];
        $aguaamgiente = $_POST['agua_y_medio_ambiente_reg_inversionista'];
        $inmobiturismo = $_POST['inmobiliario_y_turismo_reg_inversionista'];
        $transporte = $_POST['transporte_reg_inversionista'];
        $trans_sub = $_POST['transporte_sub-sectores'];
        $industria = $_POST['industria_reg_inversionista'];
        $telecom = $_POST['telecomunicaciones_reg_inversionista'];
        // FICHA INVERSIONISTA
        $t_inversionista = $_POST['tipo_de_inversionista'];
        $rol_pot_inv = $_POST['rol_potencial_inv_reg_inversionista'];
        $t_parti_inv = $_POST['tipo_de_participacion_inv_reg_inversionista'];
        $em_hol_inv = $_POST['empresa_holding_inv_reg_inversionista'];
        $pl_max_inv = $_POST['plazo_max_inversion_inv_reg_inversionista'];
        //FICHA DESARROLLADOR
        $t_desarrollador = $_POST['tipo_de_desarrollador'];
        $em_hol_de = $_POST['empresa_holding_de_reg_inversionista'];
        $t_parti_de = $_POST['tipo_de_participacion_de_reg_inversionista'];
        $pl_max_de = $_POST['plazo_max_inversion_de_reg_inversionista'];
        //FICHA CONSULTOR

        $t_consultor = $_POST['tipo_de_consultor'];
        $em_hol_consul = $_POST['empresa_holding_consul_reg_inversionista'];
        //FICHA BANCOS
        $t_banco = $_POST['tipo_de_banco_reg_inversionista'];
        $par_trav_banco = $_POST['participacion_a_traves_de_banco_reg_inversionista'];
        $em_hol_banco = $_POST['empresa_holding_banco_reg_inversionista'];
        $rol_pot_banco = $_POST['rol_potencial_banco_reg_inversionista'];
        $t_parti_banco = $_POST['tipo_de_participacion_banco_reg_inversionista'];
        $pl_max_banco = $_POST['plazo_maximo_credito_banco_reg_inversionista'];
        // FICHA OTROS
        $t_persona = $_POST['tipo_de_persona_reg_inversionista'];
        $em_hol_otros = $_POST['empresa_holding_otros_reg_inversionista'];

        $observaciones = $_POST['observaciones_reg_inversionista'];
        $idioma_pref = $_POST['idioma_de_preferencia_reg_inversionista'];
        $captcha = $_POST['captcha'];
		
		// Leer el checkbox
$proyectos_nuevos_fin = isset($_POST['proyectos_nuevos_fin']) ? true : false;

        
        global $error_array;
        $error_array = array();
        $lang = get_locale();
        // Validamos los campos
        $expresion = "/[$\+*()|&!'\":;.%<>@\[\]{}]/";
        $txt_es = "solo acepta letras y n&uacute;meros.";
        $txt_en = "only accepts letters and numbers."; 
		
		
		
		//proyectos Nuevos
		
        if(preg_match($expresion, $post_title)){
            if($lang == 'en_US')
                $error_array[] = 'The name only accepts letters';
            else
                $error_array[] = 'El nombre solo acepta letras';
        }
        //if(!preg_match($expresion, $empresa_d)){
        if(preg_match($expresion, $empresa_d)){
            if($lang == 'en_US')
                $error_array[] = 'Company '.$txt_en;
            else
                $error_array[] = 'Empresa '.$txt_es;
        }
        if (empty($tipo_contacto)){
            if($lang == 'en_US')
                $error_array[] = 'Select a type of contact.';
            else
                $error_array[] = 'Selecciona tipo de contacto.';
        }
        if (empty($pais)){
            if($lang == 'en_US')
                $error_array[] = 'Select Country';
            else
                $error_array[] = 'Selecciona Pa&iacute;s';
        }
        if (empty($correo)){
            if($lang == 'en_US')
                $error_array[] = 'Add an email';
            else
                $error_array[] = 'Agrega un correo electr&oacute;nico';
        }

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            if($lang == 'en_US')
                $error_array[] = "Invalid email";
            else
                $error_array[] = "Correo invalido";
        }
        if (empty($empresa_d)){
            if($lang == 'en_US')
                $error_array[] = 'Add the Company field.';
            else
                $error_array[] = 'Agrega el campo Empresa ';
        }
        if (empty($post_title)){
            if($lang == 'en_US')
                $error_array[] = 'Add a Contact name.';
            else
                $error_array[] = 'Agrega un nombre de Contacto.';
        }
        if ($monto_min<0 || !is_numeric($monto_min)){
            if($lang == 'en_US')
                $error_array[] = 'Error in the minimum amount';
            else
                $error_array[] = 'Error en el monto m&iacute;nimo';
        }
        if ($monto_max<0 || $monto_max<=$monto_min || !is_numeric($monto_max)){
            if($lang == 'en_US')
                $error_array[] = 'Error in the maximum amount';
            else
                $error_array[] = 'Error en el monto maximo';
        }
        
        $checkmail = array(
            'post_type' => 'reg_inversionistas',
            'post_status' => 'draft',
            'meta_key' => 'correo_registro_reg_inversionista',
            'meta_value' => $correo
        );
        $checkmail_query = new WP_Query($checkmail);
        foreach ($checkmail_query as $postme) {
            if($postme->ID>0)
                $myid = $postme->ID;
        }

        $campo = get_field('correo_registro_reg_inversionista', $myid);
        //var_dump($campo);
        
        if ($myid>0){
            if($lang == 'en_US')
                $error_array[] = 'email used.';
            else
                $error_array[] = 'email usado.';
        }
        if($r_!=$captcha){
            if($lang == 'en_US')
                    $error_array[] = 'Incorrect captcha. ';
                else
                    $error_array[] = 'Captcha Incorrecto. ';
        }

        if (count($error_array) == 0) {

            $post_id = wp_insert_post(array(
                'post_title' => wp_strip_all_tags($post_title),
                'post_type' => 'reg_inversionistas',
                'post_status' => 'draft'
                    ));
					
					// Guardar en ACF (usar el key correcto, si tienes la función get_acf_key úsala)
update_field(get_acf_key('proyectos_nuevos_fin', 'Ficha Registro Contacto/Inversionista'), $proyectos_nuevos_fin, $post_id);

            update_field(get_acf_key('folio_reg_inversionista', 'Ficha Registro Contacto/Inversionista'), $value_folio, $post_id);

            //update_field('folio_reg_inversionista', $value_folio, $post_id);
            //update_field('empresa_reg_inversionista', $empresa_d, $post_id);

            update_field('field_5893f576347b0', wp_strip_all_tags($empresa_d), $post_id);
            update_field(get_acf_key('correo_registro_reg_inversionista', 'Ficha Registro Contacto/Inversionista'), $correo, $post_id);
            update_field(get_acf_key('tipo_de_contacto_reg_inversionista', 'Ficha Registro Contacto/Inversionista'), $tipo_contacto, $post_id);
            update_field(get_acf_key('pais_reg_inversionista', 'Ficha Registro Contacto/Inversionista'), $pais, $post_id);
            update_field(get_acf_key('interes_en_proyectos_reg_inversionista', 'Ficha Registro Contacto/Inversionista'), $interes_proyectos, $post_id);
            update_field(get_acf_key('tipos_de_proyecto_reg_inversionista', 'Ficha Registro Contacto/Inversionista'), $tipos_proyecto, $post_id);
            /* Sección de etapas */
            if ($etapa_ciclo != "") {
                $etapa_ciclo == $etapa_ciclo1;
                update_field(get_acf_key('etapa_del_ciclo_de_inversion_reg_inversionista', 'Ficha Registro Contacto/Inversionista'), $etapa_ciclo, $post_id);
            } else {

                $etapa_ciclo1 == $etapa_ciclo;
                update_field(get_acf_key('etapa_del_ciclo_de_inversion_reg_inversionista', 'Ficha Registro Contacto/Inversionista'), $etapa_ciclo1, $post_id);
            }
            /* Para los montos máximos y minimos */
            if ($monto_min == "") {
                //$monto_min = $monto_min1;
                update_field(get_acf_key('monto_minimo_reg_inversionista', 'Ficha Registro Contacto/Inversionista'), $monto_min1, $post_id);
            } else {
                $monto_min1 = $monto_min;
                update_field(get_acf_key('monto_minimo_reg_inversionista', 'Ficha Registro Contacto/Inversionista'), wp_strip_all_tags($monto_min), $post_id);
            }


            if ($monto_max == "") {
                //$monto_max = $monto_max1;
                update_field(get_acf_key('monto_maximo_reg_inversionista', 'Ficha Registro Contacto/Inversionista'), $monto_max1, $post_id);
            } else {

                $monto_max1 = $monto_max;
                update_field(get_acf_key('monto_maximo_reg_inversionista', 'Ficha Registro Contacto/Inversionista'), wp_strip_all_tags($monto_max), $post_id);
            }

            /* if ($monto_min == "") {
              $monto_min = $monto_min1 ;
              update_field(get_acf_key('monto_maximo_reg_inversionista','Ficha Registro Contacto/Inversionista'),$monto_min,$post_id);



              } */






            update_field(get_acf_key('electricidad_reg_inversionista', 'Ficha Registro Contacto/Inversionista'), $check_electricidad, $post_id);
            update_field(get_acf_key('electricidad_sub_reg_inversionista', 'Ficha Registro Contacto/Inversionista'), $elec_subsec, $post_id);

            update_field(get_acf_key('infraestructura_social_reg_inversionista', 'Ficha Registro Contacto/Inversionista'), $check_infra, $post_id);
            update_field(get_acf_key('mineria_reg_inversionista', 'Ficha Registro Contacto/Inversionista'), $check_mineria, $post_id);
            update_field(get_acf_key('hidrocarburos_reg_inversionista', 'Ficha Registro Contacto/Inversionista'), $check_hidro, $post_id);
            update_field(get_acf_key('hidrocarburos_sub-sectores', 'Ficha Registro Contacto/Inversionista'), $hid_subsec, $post_id);
            update_field(get_acf_key('agua_y_medio_ambiente_reg_inversionista', 'Ficha Registro Contacto/Inversionista'), $aguaamgiente, $post_id);
            update_field(get_acf_key('inmobiliario_y_turismo_reg_inversionista', 'Ficha Registro Contacto/Inversionista'), $inmobiturismo, $post_id);
            update_field(get_acf_key('transporte_reg_inversionista', 'Ficha Registro Contacto/Inversionista'), $transporte, $post_id);
            update_field(get_acf_key('transporte_sub-sectores', 'Ficha Registro Contacto/Inversionista'), $trans_sub, $post_id);
            update_field(get_acf_key('industria_reg_inversionista', 'Ficha Registro Contacto/Inversionista'), $industria, $post_id);
            update_field(get_acf_key('telecomunicaciones_reg_inversionista', 'Ficha Registro Contacto/Inversionista'), $telecom, $post_id);

            // FICHA INVERSIONISTA
            update_field(get_acf_key('tipo_de_inversionista', 'Ficha Registro Contacto/Inversionista'), $t_inversionista, $post_id);
            update_field(get_acf_key('rol_potencial_inv_reg_inversionista', 'Ficha Registro Contacto/Inversionista'), $rol_pot_inv, $post_id);
            update_field(get_acf_key('tipo_de_participacion_inv_reg_inversionista', 'Ficha Registro Contacto/Inversionista'), $t_parti_inv, $post_id);
            update_field(get_acf_key('empresa_holding_inv_reg_inversionista', 'Ficha Registro Contacto/Inversionista'), $em_hol_inv, $post_id);
            update_field(get_acf_key('plazo_max_inversion_inv_reg_inversionista', 'Ficha Registro Contacto/Inversionista'), $pl_max_inv, $post_id);
            // FICHA DESARROLLADOR
            update_field(get_acf_key('tipo_de_desarrollador', 'Ficha Registro Contacto/Inversionista'), $t_desarrollador, $post_id);
            update_field(get_acf_key('empresa_holding_de_reg_inversionista', 'Ficha Registro Contacto/Inversionista'), $em_hol_de, $post_id);
            update_field(get_acf_key('tipo_de_participacion_de_reg_inversionista', 'Ficha Registro Contacto/Inversionista'), $t_parti_de, $post_id);
            update_field(get_acf_key('plazo_max_inversion_de_reg_inversionista', 'Ficha Registro Contacto/Inversionista'), $pl_max_de, $post_id);
            //FICHA CONSULTOR
            update_field(get_acf_key('tipo_de_consultor', 'Ficha Registro Contacto/Inversionista'), $t_consultor, $post_id);
            update_field(get_acf_key('empresa_holding_consul_reg_inversionista', 'Ficha Registro Contacto/Inversionista'), $em_hol_consul, $post_id);
            //FICHA BANCOS
            update_field(get_acf_key('tipo_de_banco_reg_inversionista', 'Ficha Registro Contacto/Inversionista'), $tipo_de_banco_reg_inversionista, $post_id);
            update_field(get_acf_key('participacion_a_traves_de_banco_reg_inversionista', 'Ficha Registro Contacto/Inversionista'), $participacion_a_traves_de_banco_reg_inversionista, $post_id);
            update_field(get_acf_key('empresa_holding_banco_reg_inversionista', 'Ficha Registro Contacto/Inversionista'), $empresa_holding_banco_reg_inversionista, $post_id);
            update_field(get_acf_key('rol_potencial_banco_reg_inversionista', 'Ficha Registro Contacto/Inversionista'), $rol_potencial_banco_reg_inversionista, $post_id);
            update_field(get_acf_key('tipo_de_participacion_banco_reg_inversionista', 'Ficha Registro Contacto/Inversionista'), $tipo_de_participacion_banco_reg_inversionista, $post_id);
            update_field(get_acf_key('plazo_maximo_credito_banco_reg_inversionista', 'Ficha Registro Contacto/Inversionista'), $plazo_maximo_credito_banco_reg_inversionista, $post_id);

            // FICHA OTROS
            update_field(get_acf_key('tipo_de_persona_reg_inversionista', 'Ficha Registro Contacto/Inversionista'), $t_persona, $post_id);
            update_field(get_acf_key('empresa_holding_otros_reg_inversionista', 'Ficha Registro Contacto/Inversionista'), $em_hol_otros, $post_id);

            update_field(get_acf_key('observaciones_reg_inversionista', 'Ficha Registro Contacto/Inversionista'), $observaciones, $post_id);
            update_field(get_acf_key('recibir_notificaciones_reg_inversionista', 'Ficha Registro Contacto/Inversionista'), 'a:1:{i:0;s:2:"Si";}', $post_id);
            update_field('idioma_de_preferencia_reg_inversionista', $idioma_pref, $post_id);
            update_field('recibir_notificaciones_reg_inversionista', 'Si', $post_id);

            // SEND MAIL NOTIFICATION
            $to = $correo;
            $headers = 'From: Banobras <notificacion-proyectos@banobras.gob.mx>';
            
            $network_site_url = 'https://www.proyectosmexico.gob.mx';
            if ($lang == 'en_US') {
                //$message = wp_remote_get(network_site_url() . '/notificacion-alta-de-contacto-en/?mail=' . $correo . '&lang=en');
                $message = new_contact_body('en', $post_title, $empresa_d, $puesto);
                $subject = get_value_notifications('', '', '', 'alta_registro_0_asunto_0_ingles');
                if ($subject == '')
                    $subject = "Your registry on " . get_bloginfo('name');

                //wp_mail($to, $subject, $message['body'], $headers);
                wp_mail($to, $subject, $message, $headers);                
                wp_redirect($network_site_url . '/successful-registration/');
                //header("Location: " . $network_site_url . "/successful-registration/");
                echo "<script type='text/javascript'>window.top.location='" . $network_site_url . "/successful-registration/';</script>";
                /*wp_redirect(network_site_url() . '/successful-registration/');
                header("Location: " . network_site_url() . "/successful-registration/");
                echo "<script type='text/javascript'>window.top.location='" . network_site_url() . "/successful-registration/';</script>";*/
                exit;
            }else {
                //$message = wp_remote_get(network_site_url() . '/notificacion-alta-de-contacto/?mail=' . $correo . '');
                $message = new_contact_body('', $post_title, $empresa_d, $puesto);
                $subject = get_value_notifications('', '', '', 'alta_registro_0_asunto_0_espaniol');
                if ($subject == '')
                    $subject = "Su alta de Ficha en " . get_bloginfo('name');

                
                //wp_mail($to, $subject, $message['body'], $headers);
                wp_mail($to, $subject, $message, $headers);
                wp_redirect($network_site_url . '/registro-exitoso/');
                //header("Location: ".network_site_url()."/registro-exitoso/");
                echo "<script type='text/javascript'>window.top.location='" . $network_site_url . "/registro-exitoso/';</script>";
                exit;
                /*wp_redirect(network_site_url() . '/registro-exitoso/');
                //header("Location: ".network_site_url()."/registro-exitoso/");
                echo "<script type='text/javascript'>window.top.location='" . network_site_url() . "/registro-exitoso/';</script>";
                exit;*/
                //exit();
            }
            add_action('postcontact-form-notice', 'PostContact_form_notices');
        } else {
            add_action('postcontact-form-notice', 'PostContact_form_errors');
        }
    }
}

//add_action('init','PostContact_form_add_post');

