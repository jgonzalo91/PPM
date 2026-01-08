/* 
 * Funciones para el funcionamiento de las fichas de sostenibilidad
 */
function elegirBuenaPractica(pop,id,tipo,dim){

    var option = $(pop).is(":checked");
    var idvisual = $(pop).data("id");

    if(option){

        $(pop).prop( "checked", false );
        // No es palomeado
        $.confirm({
            title: '<img src=\"images/warning.png\">&nbsp;Advertencia!',
            content: 'Esta acciÃ³n desactivara las buenas practicas que coincidan con el pilar de la buena practica seleccionada',
            type: 'red',
            theme: 'modern',
            boxWidth: '500px',
            useBootstrap: false,
            typeAnimated: true,
            buttons: {
                ok: {
                    text: "ok!",
                    btnClass: 'btn-red',
                    keys: ['enter'],
                    action: function () {

                        // Cambiar las opciones que son del mismo pilar y actualizar la base de datos.
                        $("#Mejores_practicas input[type='checkbox']").each(function (){

                            if($(this).data("dimen")==dim){
                                
                                // Despalomear los de la misma dimencion
                                $(this).prop("checked",false);

                                // Tomar el Tipo de Practica BAtributo o BFicha
                                $y=$(this).data("tipo");

                                // Practica ID
                                $x=$(this).data("practica");
                                
                                // Tomar el IDvisual de la Practica
                                $idvisual2=$(this).data("id");

                                if($y=='BAtributo' && $idvisual2!=idvisual){
                                    $.post("fichas/guardar.php", {action: 'A504', id: $x, pop:0}, function () {
                                    });
                                }

                                if($y=='BFicha' && $idvisual2!=idvisual){
                                    $.post("fichas/guardar.php", {action: 'A505', id: $x, pop:0}, function () {
                                    });
                                }

                            }
                        });

                        // Activar el que si se selecciono.
                        $(pop).prop( "checked", true );
                        
                        // Cuando confirma !!!
                        if(tipo=='BAtributo'){
                            $.post("fichas/guardar.php", {action: 'A504', id: id, pop:1}, function () {
                            });
                        }

                        if(tipo=='BFicha'){
                            $.post("fichas/guardar.php", {action: 'A505', id: id, pop:1}, function () {
                            });
                        }

                    }
                },
                cancel: function () {
                }
            }
        });

    }else{

        $(pop).prop( "checked", true );
        // Es palomeado

        $.confirm({
            title: '<img src=\"images/warning.png\">&nbsp;Advertencia!',
            content: 'Debe existir al menos una selecciÃ³n por pilar en buenas practicas',
            type: 'red',
            theme: 'modern',
            boxWidth: '500px',
            useBootstrap: false,
            typeAnimated: true,
            buttons: {
                ok: {
                    text: "ok!",
                    btnClass: 'btn-red',
                    keys: ['enter'],
                    action: function () {

                        // Desactivar el que seleccionaron
                        $(pop).prop( "checked", false );

                        // Cuando desactiva
                        if(tipo=='BAtributo'){
                            $.post("fichas/guardar.php", {action: 'A504', id: id, pop:0}, function () {
                            });
                        }

                        // Cuando desactiva
                        if(tipo=='BFicha'){
                            $.post("fichas/guardar.php", {action: 'A505', id: id, pop:0}, function () {
                            });
                        }

                    }
                },
                cancel: function () {
                }
            }
        });

    }

}

function ListaFichas(id, proyecto, initial = 0) {
    $.post("fichas/ficha_ajax.php", {m: 1, id: id}, function (mensaje) {
        $("#ModalListaFicha").empty().html(mensaje);
        if (initial == 0) {
            if(proyecto==""){
                proyecto = $("#ui-id-1").html();
            }
            $('#ModalListaFicha').dialog({
                resizable: false,
                modal: true,
                height: 'auto',
                width: 'auto',
                title: proyecto,
            });
        }
        // Hacemos que siempre regrese al principio de la pagina
        window.scrollTo(0,0);
        $(".ui-dialog").css({'top':'0px'});
    });
    $(".1er_etapa").remove();
}

function AgregarFicha(id) {
    $.post("fichas/ficha_ajax.php", {m: 2, id: id}, function (mensaje) {
        $("#ModalListaFicha").empty().html(mensaje);
    });
}

function ModificarFicha(id_sos, id_pro, id_eta, subsector, etapa="") {
    $.post("fichas/modificar.php", {id_sos: id_sos, id_pro: id_pro, id_etapa: id_eta, id_subsector: subsector}, function (mensaje) {
        $("#ModalListaFicha").empty().html(mensaje);
        if(etapa!=""){
            $(".1er_etapa").remove();
            $("#ui-id-1").after("<div class='1er_etapa'><span >Etapa:"+etapa+"</span></div>");
            Ver();
        }
        // Si esta activado desactva los demas temas
        var ch0 = $("#ninguno").is(":checked");
        if(ch0==true){
            $(".TC").removeAttr("checked").attr("disabled", "disabled");
        }
        // Accion del ninguno
        $("#ninguno").change(function(){
            var ch = $(this).is(":checked");
            if(ch==true){
                $(".TC").removeAttr("checked").attr("disabled", "disabled");
            }else{
                $(".TC").removeAttr("disabled");
            }
        });
        
        $(".TC").click(function(){
            var ch2 = $(this).is(":checked");
            if(ch2==true)
                $("#ninguno").removeAttr("checked").attr("disabled", "disabled");
            else
                $("#ninguno").removeAttr("disabled");
        });
    });
}

function WhichButton(element) {
    var valor = $(element).attr("value");
    // Cuando esta en terminado y cambias a borrador
    if (valor == 1) {
        $(element).css({"backgroundColor": "#f0ad4e", "color": "#fff", "border-color": "#eea236"});
        $(element).attr("value", "0");
        $(element).text("Borrador");
        $("#GpublicadaEtiqueta").text("Ficha en borrador (no publicada)");
    }
    // Cuando esta en borrador y lo cambias a terminado
    if (valor == 0) {
        var f_ = $(element).data('f');
        var e_ = $(element).data('e');
        // verificamos si la auditoria esta limpia
        $.post("fichas/auditoria.php", {a: true, id_ficha_sostenibilidad: f_, id_etapa: e_}, function (mensaje) {
            
            if(mensaje=='0'){
                $(element).css({"backgroundColor": "#337ab7", "color": "#fff", "border-color": "#2e6da4"});
                $(element).attr("value", "1");
                $(element).text("Terminada");
                $("#GpublicadaEtiqueta").text("Ficha terminada (publicada)");
            }else{
                alert("Verifica la informacion de la auditoria!");
            }
        });
        
        
    }

}

function GuardarFichaProyecto(id, action) {
    var etapa = $("#alta_ficha_etapa option:selected").val();

    $.ajax({async: false,
        cache: false,
        dataType: "html",
        url: "fichas/guardar.php",
        type: "POST",
        data: {id_proyecto: id, id_etapa: etapa, action: 'A1'},
        success: function (data) {
            ListaFichas(id, '', 1);
            buscarFicha();
        }
    });
}

function PublicarxOmision() {
    $.confirm({
        title: '<img src=\"images/warning.png\">&nbsp;Advertencia!',
        content: '¿Esta seguro que desea publicar esta ficha?',
        type: 'red',
        theme: 'modern',
        boxWidth: '500px',
        useBootstrap: false,
        typeAnimated: true,
        buttons: {
            ok: {
                text: "ok!",
                btnClass: 'btn-red',
                keys: ['enter'],
                action: function () {
                    var fh = $("#publicar_x_omision option:selected").val();
                    $.post("fichas/guardar.php", {action: 'OM', id_ficha_sostenibilidad: fh}, function (mensaje) {

                    var objeto = JSON.parse(mensaje);
                    GenerarPDF(objeto.proyecto, objeto.ficha, objeto.etapa, objeto.action,1);
                    });

                    }
            },
            cancel: function () {
            }
        }
    });


}

function GGeneralGuarda(id, id_proyecto) {

    var FechaUltimaEdicion = $("#FechaUltimaEdicion").val();
    var Gpublicada = $("#Gpublicada").val();
    var comentarios_internos = $("#ComentariosInternosFicha").val();
    var Resumen_es = $("#ResumenSostenibilidad_ES").val();
    var Resumen_en = $("#ResumenSostenibilidad_EN").val();
    var JustificacionAlineacionesSectOp = $("#JustificacionAlineacionesSectOp").val();
    var Titulo = $(".ui-dialog-title").html();
    var nin = false;
    if($("#ninguno").is(":checked"))
        nin = true;

    const cars = [];
    var $i = 1;
    $(".TC").each(function ()
    {
        if ($(this).prop('checked')) {
            cars[$i] = $(this).val();
        }
        $i++;
    });


    const obliga = [];
    var $i = 1;
    $(".obligatorias").each(function ()
    {
        if ($(this).prop('checked')) {
            obliga[$i] = $(this).val();
        }
        $i++;
    });
    
    var msgError = "<table ><tr><td><img src='images/warning32.png'></td><td><b>&nbsp;&nbsp;Por favor verifique la siguiente informacion:</b></td></tr><tr><td colspan='2'><hr><br></td></tr>";
    var banderaError = false;
    if(FechaUltimaEdicion==''){
        banderaError = true;
        msgError += "<tr><td colspan='2'>- Indispensable ingresar la fecha de &uacute;ltima edici&oacute;n-</td></tr>";
    }
    msgError += "</table>";
    if (banderaError == true) {
        $.alert({
            title: '<img src=\"images/warning32.png\">&nbsp;Advertencia!',
            content: msgError,
            type: 'brown',
            theme: 'modern',
            boxWidth: '500px',
            useBootstrap: true,
            btnClass: 'btn-blue',
            typeAnimated: true
        });
    }else{
    
        $.ajax({async: false,
            cache: false,
            dataType: "html",
            url: "fichas/guardar.php",
            type: "POST",
            data: {id: id, id_proyecto: id_proyecto, TC: cars, obligatorias: obliga, FechaUltimaEdicion: FechaUltimaEdicion, Gpublicada: Gpublicada, comentarios_internos: comentarios_internos, Resumen_es: Resumen_es, Resumen_en: Resumen_en, JustificacionAlineacionesSectOp: JustificacionAlineacionesSectOp, ninguno: nin, action: 'A5'},
            success: function (data) {
            },
            beforeSend: function (xhr) {
                
            },
            complete: function () {
                ListaFichas(id_proyecto, Titulo, initial = 0);
            }

        });
    }


}

function AlineacionesSectorialesOpcionales(id_sos, id_pro, id_eta, subsector) {
    $tipo = 1;
    $.post("fichas/modificar.php", {tipo: $tipo, id_sos: id_sos, id_pro: id_pro, id_etapa: id_eta, id_subsector:subsector}, function (mensaje) {
        $("#ModalListaFicha").empty().html(mensaje);
        $('#ModalListaFicha').dialog({
            width: 1024,
            height: 700
        });
    });

}

function JustificacionASOGuarda(id, id_proyecto, id_eta, subsecto) {

    var JustificacionASO = $("#JustificacionASO").val();
    const tren = [];

    var $i = 0;
    $(".ASGuarda").each(function ()
    {
        if ($(this).prop('checked')) {
            tren[$i] = $(this).val();
        }
        $i++;
    });

    $.ajax({async: false,
        cache: false,
        dataType: "html",
        url: "fichas/guardar.php",
        type: "POST",
        data: {id: id, id_proyecto: id_proyecto, tren: tren, action: 'A500', JustificacionASO: JustificacionASO},
        success: function (data) {
            //ModificarFicha(id,id_pro);
            ModificarFicha(id, id_proyecto, id_eta, subsecto);
        }
    });

}

function DocumentosxFicha(idficha, action = 'L', iddoc = 0, modal = '#Documentos', idproyecto) {
    var modal_ = $(modal);
    var titulo = $('.ui-dialog-title').html();
    // Listado Docs
    if (action == 'L') {
        $.post("fichas/ficha_ajax.php", {m: 'L_DC', id_ficha: idficha, idproyecto:idproyecto, titulo: titulo}, function (mensaje) {
            modal_.empty().html(mensaje);
        });
    }
    // Alta/Modificar Doc
    if (action == 'M') {
        $.post("fichas/ficha_ajax.php", {m: 'E_DC', id_ficha: idficha, id_documento: iddoc, idproyecto:idproyecto, titulo: titulo}, function (mensaje) {
            modal_.empty().html(mensaje);
        });
    }
    // Guardar Doc
    if (action == 'G') {
        var doc = $("#documento").val();
        var corto = $("#nombre_corto").val();
        var ext = $("#liga_ex").val();
        var int = $("#liga_in").val();

        var msgError = "<table ><tr><td><img src='images/warning32.png'></td><td><b>&nbsp;&nbsp;Por favor verifique la siguiente informacion:</b></td></tr><tr><td colspan='2'><hr><br></td></tr>";
        var banderaError = false;

        if (doc == 0) {
            banderaError = true;
            msgError += "<tr><td colspan='2'>- Indispensable ingresar un documento</td></tr>";
        }
        if (corto == 0) {
            banderaError = true;
            msgError += "<tr><td colspan='2'>- Indispensable ingresar un nombre corto</td></tr>";
        }
        /*if (ext == 0) {
            banderaError = true;
            msgError += "<tr><td colspan='2'>- Indispensable ingresar una liga rota</td></tr>";
        }*/
        if (int == 0) {
            banderaError = true;
            msgError += "<tr><td colspan='2'>- Indispensable ingresar una liga interna</td></tr>";
        }
        msgError += "</table>";
        if (banderaError == true) {
            $.alert({
                title: '<img src=\"images/warning32.png\">&nbsp;Advertencia!',
                content: msgError,
                type: 'brown',
                theme: 'modern',
                boxWidth: '500px',
                useBootstrap: true,
                btnClass: 'btn-blue',
                typeAnimated: true
            });
        } else {
            $.ajax({async: false,
                cache: false,
                dataType: "html",
                url: "fichas/guardar.php",
                type: "POST",
                data: $("#editdoc").serialize(),
                success: function (data) {
                    DocumentosxFicha(idficha, 'L', iddoc, modal, idproyecto);
                }
            });
        }
    }
    //Eliminar Doc
    if (action == 'E') {
        $.confirm({
            title: '<img src=\"images/warning.png\">&nbsp;Advertencia!',
            content: '¿Esta seguro que desea  eliminar el documento de la ficha?',
            type: 'red',
            theme: 'modern',
            boxWidth: '500px',
            useBootstrap: false,
            typeAnimated: true,
            buttons: {
                ok: {
                    text: "ok!",
                    btnClass: 'btn-red',
                    keys: ['enter'],
                    action: function () {
                        $.ajax({
                            url: "fichas/eliminar.php?",
                            type: "POST",
                            data: {id_documento: iddoc, action: 'E_DOC'},
                            success: function (data) {
                                DocumentosxFicha(idficha, 'L', iddoc, modal, idproyecto);
                            }
                        });
                    }
                },
                cancel: function () {
                    DocumentosxFicha(idficha);
                }
            }
        });
}
}

function BuenasPracticasGeneral(idficha, action = 'L', id_buenapractica = 0, modal = '#Mejores_practicas', idproyecto) {
    var _mod = $(modal);
    var titulo = $('.ui-dialog-title').html();
    // Listado buenas practicas
    if (action == 'L') {
        $.post("fichas/ficha_ajax.php", {m: 'L_BP', id_ficha: idficha, idproyecto:idproyecto,titulo:titulo}, function (mensaje) {
            _mod.empty().html(mensaje);
            ocultaBotonBuenasPracticas();
        });
    }
    // Alta/Modificar Buena practica por ficha
    if (action == 'M') {
        $.post("fichas/ficha_ajax.php", {m: 'E_BP', id_ficha: idficha, id_buenapractica: id_buenapractica, idproyecto:idproyecto,titulo:titulo}, function (mensaje) {
            _mod.empty().html(mensaje);
        });
    }
    // Guardar Buena practica por ficha
    if (action == 'G') {
        var dim_ = $("#id_dimension").val();
        var pract_ = $("#practica").val();
        var pract_en = $("#practica_en").val();

        var msgError = "<table ><tr><td><img src='images/warning32.png'></td><td><b>&nbsp;&nbsp;Por favor verifique la siguiente informacion:</b></td></tr><tr><td colspan='2'><hr><br></td></tr>";
        var banderaError = false;

        if (dim_ == 0) {
            banderaError = true;
            msgError += "<tr><td colspan='2'>- Seleccionar una dimensión</td></tr>";
        }
        if (pract_ == 0) {
            banderaError = true;
            msgError += "<tr><td colspan='2'>- Ingresar una buena pr&aacute;ctica</td></tr>";
        }
        msgError += "</table>";
        if (banderaError == true) {
            $.alert({
                title: '<img src=\"images/warning32.png\">&nbsp;Advertencia!',
                content: msgError,
                type: 'brown',
                theme: 'modern',
                boxWidth: '500px',
                useBootstrap: true,
                btnClass: 'btn-blue',
                typeAnimated: true
            });
        } else {
            $.ajax({async: false,
                cache: false,
                dataType: "html",
                url: "fichas/guardar.php",
                type: "POST",
                data: $("#editbuenapractica").serialize(),
                success: function (data) {
                    BuenasPracticasGeneral(idficha, 'L', 0, modal, idproyecto);
                }
            });
        }
    }
    //Eliminar Doc
    if (action == 'E') {
        $.confirm({
            title: '<img src=\"images/warning.png\">&nbsp;Advertencia!',
            content: '¿Esta seguro que desea  eliminar la buena práctica de la ficha?',
            type: 'red',
            theme: 'modern',
            boxWidth: '500px',
            useBootstrap: false,
            typeAnimated: true,
            buttons: {
                ok: {
                    text: "ok!",
                    btnClass: 'btn-red',
                    keys: ['enter'],
                    action: function () {
                        $.ajax({
                            url: "fichas/eliminar.php?",
                            type: "POST",
                            data: {id_buena_practica: id_buenapractica, action: 'E_BP'},
                            success: function (data) {
                                BuenasPracticasGeneral(idficha, 'L', 0, modal, idproyecto);
                            }
                        });
                    }
                },
                cancel: function () {
                    BuenasPracticasGeneral(idficha);
                }
            }
        });
    }

}

function ocultaBotonBuenasPracticas() {
    // Ocultar el Boton de Agregar nueva practica en caso que ya existan 4 pilares en Practicas por Ficha.
    var $a=0;
    var $b=0;
    var $c=0;
    var $d=0;

    $("#BPF input[type='checkbox']").each(function (){ 
    
    if($(this).data("dimen")==1){$a=1;}
    if($(this).data("dimen")==2){$b=1;}
    if($(this).data("dimen")==3){$c=1;}
    if($(this).data("dimen")==4){$d=1;}

    });

    if( $a==1 && $b==1 && $c==1 && $d==1 ){
        $( "#BotonAgregarBPF").hide();
    }else{
        $( "#BotonAgregarBPF").show();
    }
       //# ------
}

function AtributosDeFichaxDimension(ficha, dimension, etapa, proyecto, subsector) {
    $(".principales").empty();
    var _content = $("#Dim_" + dimension);
    var titulo = $('.ui-dialog-title').html();
    $.ajax({
        async: true,
        cache: false,
        dataType: "html",
        url: "fichas/alineacion.php",
        type: "POST",
        data: {id_ficha: ficha, id_dimension: dimension, id_etapa: etapa, id_proyecto: proyecto, id_subsector: subsector, titulo: titulo,action: 'LATTR'},
        success: function (mensaje) {
            _content.empty().append(mensaje);
        },
        beforeSend: function (xhr) {
            _content.empty().append("<div class='row' style='margin-top:90px'><i class='icon-spinner3' style='font-size:50px;'></i> <div><b>Cargando...</b></div></div>");
        }
    });
}

function AsignarTier(ficha, attr, dim, etapa, id_attr_x_ficha, name_attr, IdTier, proyecto, subsector) {
    var Md = $("#Dim_" + dim);
    $.ajax({
        async: true,
        cache: false,
        dataType: "html",
        url: "fichas/alineacion.php",
        type: "POST",
        data: {id_atributo_x_ficha: id_attr_x_ficha, id_ficha: ficha, id_atributo: attr, id_etapa: etapa, id_dimension: dim, nombre_attr: name_attr, id_tier: IdTier, id_proyecto: proyecto, id_subsector: subsector, action: 'CAL_ATTR'},
        success: function (mensaje) {
            Md.empty().html(mensaje);
            Ver();
        },
        beforeSend: function (xhr) {
            Md.empty().html("<div class='row' style='margin-top:90px'><i class='icon-spinner3' style='font-size:50px;'></i> <div><b>Cargando...</b></div></div>");
        }
    });
}

function EvidenciasxAtributo(id_comen_evi_x_ficha, id_attr, name_attr = "", id_attr_x_ficha = 0, id_dim, ficha, id_etapa, action = 'M', modal = '#ModalComentariosEvidencia') {
    $(modal).dialog({
        resizable: false,
        modal: true,
        height: 500,
        width: 1225
    });
    var dim = 1;
    $(".principales").each(function(){ 
        var active = $(this).hasClass('active'); 
        if(active){ 
            dim = $(this).data('iddime');
        }; 
    });
    // Alta/Modificar Documentos x atributo
    if (action == 'M') {
        $.post("fichas/alineacion.php", {action: 'MOD_DOC_X_ATTR', id_comentario_evidencia_x_attr: id_comen_evi_x_ficha, id_atributo: id_attr, nombre_attr: name_attr, id_atributo_x_ficha: id_attr_x_ficha, id_dimension: id_dim, id_ficha: ficha, id_etapa: id_etapa}, function (mensaje) {
            
            //$(modal).height(500);
            //$(modal).width(1225);
            $(modal).empty().html(mensaje);
            $("#frmEvidencias").height(350);
            $("#frmEvidencias").width(1150);

        });
    }
    // Guardar el comentario de evidencia por atributo
    if (action == "G") {
        var id_doc_x_ficha = $("#doc_x_ficha").val();
        var ubicacion_evi = $("#ubicacion_evidencia").val();
        var comentario_evi = $("#comentario_evidencia").val();
        var IdTier = $("#id_tier_"+dim+" option:selected").val();
        var proyecto = $("#id_proyecto_").val();
        var id_subsector_ = $("#id_subsector_").val();

        var msgError = "<table ><tr><td><img src='images/warning32.png'></td><td><b>&nbsp;&nbsp;Por favor verifique la siguiente informacion:</b></td></tr><tr><td colspan='2'><hr><br></td></tr>";
        var banderaError = false;

        if (id_doc_x_ficha == 0) {
            banderaError = true;
            msgError += "<tr><td colspan='2'>- Indispensable seleccionar un documento</td></tr>";
        }
        if (ubicacion_evi == 0) {
            banderaError = true;
            msgError += "<tr><td colspan='2'>- Indispensable ingresar la ubicación</td></tr>";
        }
        if (comentario_evi == 0) {
            banderaError = true;
            msgError += "<tr><td colspan='2'>- Indispensable ingresar un comentario de la evidencia</td></tr>";
        }

        msgError += "</table>";
        if (banderaError == true) {
            $.alert({
                title: '<img src=\"images/warning32.png\">&nbsp;Advertencia!',
                content: msgError,
                type: 'brown',
                theme: 'modern',
                boxWidth: '500px',
                useBootstrap: true,
                btnClass: 'btn-blue',
                typeAnimated: true
            });
        } else {
            $.ajax({async: false,
                cache: false,
                dataType: "html",
                url: "fichas/guardar.php",
                type: "POST",
                data: $("#frmEvidencias").serialize(),
                success: function (data) {
                    
                    AsignarTier(ficha, id_attr,id_dim,id_etapa,id_attr_x_ficha, name_attr, IdTier, proyecto, id_subsector_);
                    $(modal).dialog('close');
                }
            });
        }
    }
}

function CalificacionTier(_this, action = 'E', id_attr_ficha) {
    // campos
    var dim = 1;
    $(".principales").each(function(){ 
        var active = $(this).hasClass('active'); 
        if(active){ 
            dim = $(this).data('iddime');
        }; 
    });
    var id_t = $('#id_tier_'+dim);
    var jus = $('#justi_cal_tier_'+dim);
    var jus_ODS = $('#just_ods_'+dim);

    // Editamos los campos de la califiacion del tier
    if (action == 'E') {
        $(_this).hide();
        $('.icon_tier_save').show();
        $('.icon_tier_cancel').show();
        id_t.removeAttr('disabled');
        jus.removeAttr('disabled');
        // Ancho del textarea
        $(".tr4 textarea").height(100);
    }

    // Actualizamos o cancelamos de la califiacion del tier
    if (action == 'S' || action == 'C') {
        $(_this).hide();
        $('.icon_tier_edit').show();
        $('.icon_tier_save').hide();
        $('.icon_tier_cancel').hide();
        id_t.attr('disabled', 'disabled').removeAttr('style');
        jus.attr('disabled', 'disabled').removeAttr('style');
        if (action == 'S') {
            var msgError = "<table ><tr><td><img src='images/warning32.png'></td><td><b>&nbsp;&nbsp;Por favor verifique la siguiente informacion:</b></td></tr><tr><td colspan='2'><hr><br></td></tr>";
            var banderaError = false;

            if (id_t.val() == 0) {
                banderaError = true;
                msgError += "<tr><td colspan='2'>- Indispensable seleccionar un valor tier</td></tr>";
                id_t.removeAttr('disabled').css({'background': 'rgb(244 206 206)'});
            }
            if (jus.val() == "") {
                banderaError = true;
                msgError += "<tr><td colspan='2'>- Indispensable ingresar una justificación</td></tr>";
                jus.removeAttr('disabled').css({'background': 'rgb(244 206 206)'});
            }

            msgError += "</table>";
            if (banderaError == true) {
                $.alert({
                    title: '<img src=\"images/warning32.png\">&nbsp;Advertencia!',
                    content: msgError,
                    type: 'brown',
                    theme: 'modern',
                    boxWidth: '500px',
                    useBootstrap: true,
                    btnClass: 'btn-blue',
                    typeAnimated: true
                });
                $('.icon_tier_save').show();
                $('.icon_tier_cancel').show();
                $('.icon_tier_edit').hide();
            } else {
                var id_attr = $("#id_atributo_").val();
                var id_ficha = $("#id_ficha_").val();
                $.ajax({async: false,
                    cache: false,
                    dataType: "html",
                    url: "fichas/guardar.php",
                    type: "POST",
                    data: {id_tier: id_t.val(), justificacion_cal_tier: jus.val(), id_atributos_x_fichas: id_attr_ficha, id_fichas_sostenibilidad: id_ficha, id_atributo: id_attr, id_proyecto: $("#id_proyecto_").val(), action: 'JUS_CAL_TIER'},
                    success: function (data) {
                        $('.tr4').after('<tr class="actu"><td><div class="label-succes">Actualizado!!</td></tr>');
                        $("#id_attr_x_ficha_").val(data.replace("|", ""));
                        if (id_t.val() > 0) {
                            Alineaciones();
                        }
                    },
                    complete: function () {
                        $('.actu').remove();
                        Alineaciones();
                    }
                });
            }
        }

        // Ancho del textarea
        $(".tr4 textarea").height(100);
    }

    // Editamos los campos de la justifiacion ODS
    if (action == 'ODS_E') {
        $(_this).hide();
        $('.icon_ods_save').show();
        $('.icon_ods_cancel').show();
        jus_ODS.removeAttr('disabled');
    }
    if (action == 'ODS_S' || action == 'ODS_C') {
        $(_this).hide();
        $('.icon_ods_edit').show();
        $('.icon_ods_save').hide();
        $('.icon_ods_cancel').hide();
        jus_ODS.attr('disabled', 'disabled').removeAttr('style');
        if (action == 'ODS_S') {
            var msgError = "<table ><tr><td><img src='images/warning32.png'></td><td><b>&nbsp;&nbsp;Por favor verifique la siguiente informacion:</b></td></tr><tr><td colspan='2'><hr><br></td></tr>";
            var banderaError = false;
            if (jus_ODS.val() == "") {
                banderaError = true;
                msgError += "<tr><td colspan='2'>- Indispensable ingresar una justificación ODS</td></tr>";
                jus_ODS.removeAttr('disabled').css({'background': 'rgb(244 206 206)'});
            }

            msgError += "</table>";
            if (banderaError == true) {
                $.alert({
                    title: '<img src=\"images/warning32.png\">&nbsp;Advertencia!',
                    content: msgError,
                    type: 'brown',
                    theme: 'modern',
                    boxWidth: '500px',
                    useBootstrap: true,
                    btnClass: 'btn-blue',
                    typeAnimated: true
                });
            } else {
                $.ajax({async: false,
                    cache: false,
                    dataType: "html",
                    url: "fichas/guardar.php",
                    type: "POST",
                    data: {justificacion_ods: jus_ODS.val(), id_atributos_x_fichas: id_attr_ficha, action: 'JUS_ODS'},
                    success: function (data) {
                        $('.tr5').after('<tr class="actu5"><td><div class="label-succes">Actualizado!!</td></tr>');
                    },
                    complete: function () {
                        $('.actu5').remove();
                        Alineaciones();
                    }
                });
            }
        }
}

}

function Alineaciones(carga = 1) {
    var dim = 1;
    $(".principales").each(function(){ 
        var active = $(this).hasClass('active'); 
        if(active){ 
            dim = $(this).data('iddime');
        }; 
    });
    var _tier = $("#id_tier_"+dim+" option:selected").val();
    var _tier_valor = $("#id_tier_"+dim+" option:selected").data('value');
    var etap_ = $("#id_etapa_").val();
    var proyecto = $("#id_proyecto_").val();
    var subsector = $("#id_subsector_").val();
    var ficha = $("#id_ficha_").val();
    var dimension = $("#id_dimension_").val();
    var attr_x_ficha_ = $("#id_attr_x_ficha_").val();
    var attr_ = $("#id_atributo_").val();
    /*if (_tier_valor == '0' || _tier_valor=='') {
        $(".alinear_evaluador").show().empty().html("<div class='row text-center'><i class=' fa fa-6 fa-exclamation-triangle fa-5x text-warning'></i> <br/><h1><span class='label label-warning'>Debe calificar el tier para poder alinear!!</span></h1></div>");
        $("#BuenasPracticasATTR-tab").hide();
    } else {*/
        $.ajax({
            async: true,
            cache: false,
            dataType: "html",
            url: "fichas/alineacion.php",
            type: "POST",
            data: {id_ficha: ficha, id_tier: _tier, tier_valor:_tier_valor, id_dimension: dimension, id_etapa: etap_, id_proyecto: proyecto, id_subsector: subsector, id_atributo_x_ficha: attr_x_ficha_, id_atributo: attr_, action: 'LISTALI'},
            success: function (mensaje) {
                $(".alinear_evaluador").show().empty().html(mensaje);                
            },
            beforeSend: function (xhr) {
                if (carga == 1)
                    $(".alinear_evaluador").show().empty().html("<div class='row text-center'><i class='fa fa-spinner fa-spin' style='font-size:900%;'></i> <div><b>Cargando...</b></div></div>");
            },
            complete: function () {
                //$('div.dataTables_filter input').addClass("form-control");
                Ver();
            }
        });
        $("#BuenasPracticasATTR-tab").show();
    //}

    Ver();
}

function FrmAlinearAttr(alineacion = 0, idalineacionpam, meta, element) {
    $('#ModalComentariosEvidencia').dialog({
        width: 600,
        maxWidth: 600,
        height: 'auto',
        resizable: false,
        title: $('.ui-dialog-title').html(),
        modal: true
    });
    var id_dim = 1;
    $(".principales").each(function(){ 
        var active = $(this).hasClass('active'); 
        if(active){ 
            id_dim = $(this).data('iddime');
        }; 
    });
    var eta = $('#etapa_').val();
    var attr_name = $('#attr_').val();
    var attr = $('#id_atributo_').val();
    var attr_x_ficha = $('#id_attr_x_ficha_').val();
    var indicaciones = $(element).data("indicaciones");
    var tipo_ = $(element).data("tipo");
    var pregunta = $(element).data("pregunta");
    $.post("fichas/alineacion.php", {action: 'FRM_ALI', id_dimension:id_dim,etapa: eta, name_attr: attr_name, id_atributo: attr,
        idalineacionpam: idalineacionpam, id_atributo_x_ficha: attr_x_ficha, metaname: meta,
        idalineacionproyecto: alineacion,
        indicaciones: indicaciones, tipo: tipo_, pregunta:pregunta}, function (mensaje) {
        $("#ModalComentariosEvidencia").empty().html(mensaje);
    });
}

function AddAlineacionManual(element, alineacion=0){
    $('#ModalComentariosEvidencia').dialog({
        width: 600,
        maxWidth: 600,
        height: 'auto',
        resizable: false,
        title: $('.ui-dialog-title').html(),
        modal: true
    });
    var eta = $('#etapa_').val();
    var attr_name = $('#attr_').val();
    var attr = $('#id_atributo_').val();
    var attr_x_ficha = $('#id_attr_x_ficha_').val();
    $.post("fichas/alineacion.php", {action: 'FRM_ALI_MANUAL',etapa: eta, name_attr: attr_name, id_atributo:attr,
        idalineacionproyecto: alineacion, id_atributo_x_ficha: attr_x_ficha}, function (mensaje) {
        $("#ModalComentariosEvidencia").empty().html(mensaje);
        // muestra las metas del objetivo seleccionado
        $("#id_objetivo").change(function(){
            var val = $(this).val();
            if(val==0){
                $("#id_meta").find('option').not('first').hide();
            }else{
                
                $("#id_meta").find('option').not('first').hide();                
                $("#id_meta").find('option').each(function(i,e){
                   var dt = $(this).data('obj');
                   if(dt=='0' || dt==val){
                        $(this).show();   
                   }
                });
                $("#id_meta").prop("selectedIndex", 0);
            }
        });
    });
    
}

function GuardarAlineacionManual(){
    var meta = $("#id_meta option:selected").val();
    var jus = $("#justificacion_manual").val();
    
    var msgError = "<table ><tr><td><img src='images/warning32.png'></td><td><b>&nbsp;&nbsp;Por favor verifique la siguiente informacion:</b></td></tr><tr><td colspan='2'><hr><br></td></tr>";
    var banderaError = false;
    
    if(meta=='0'){
        banderaError = true;
        msgError += "<tr><td colspan='2'>- Indispensable seleccionar una meta-</td></tr>";
    }
    
    if(jus==""){
        banderaError = true;
        msgError += "<tr><td colspan='2'>- Indispensable agregar una justificac&oacute;n-</td></tr>";  
    }
    
    // Recooremos las metas alineadas y si una es igual a la que se seleeciona mandamos mensaje
    $(".metasalineadas").each(function(){
       var vl_meta = $(this).val();
       if(vl_meta==meta){
           banderaError = true;
            msgError += "<tr><td colspan='2'>- Ya existe una meta alineada en automatica o pendiente</td></tr>";
       }
    });
    msgError += "</table>";
    if (banderaError == true) {
        $.alert({
            title: '<img src=\"images/warning32.png\">&nbsp;Advertencia!',
            content: msgError,
            type: 'brown',
            theme: 'modern',
            boxWidth: '500px',
            useBootstrap: true,
            btnClass: 'btn-blue',
            typeAnimated: true
        });
    }else{
        $.ajax({
            async: true,
            cache: false,
            dataType: "html",
            url: "fichas/guardar.php",
            type: "POST",
            data: $("#frm_ali_man").serialize(),
            success: function (mensaje) {
                Alineaciones(0);
                $("#ModalComentariosEvidencia").dialog('close');
            }
        });
    }
}

function DeleteAlineacionManual(manual){
    
    $.confirm({
        title: '<img src=\"images/warning.png\">&nbsp;Advertencia!',
        content: '¿Esta seguro que desea  eliminar la alineacion manual?',
        type: 'red',
        theme: 'modern',
        boxWidth: '500px',
        useBootstrap: false,
        typeAnimated: true,
        buttons: {
            ok: {
                text: "ok!",
                btnClass: 'btn-red',
                keys: ['enter'],
                action: function () {
                    $.post("fichas/eliminar.php", {action: 'E_ALI_MAN', id_manual: manual}, function (mensaje) {
                        Alineaciones(0);
                    });
                }
            },
            cancel: function () {
                Alineaciones(0);
            }
        }
    });
    
    
}

function AlinearAttrxFicha() {

    var jus = $("#justificacion_ali").val();
    var tp = $("#tp").val();
    if(tp==1 && jus==""){
        $("#justificacion_ali").css('background-color', 'rgb(222 162 162 / 58%)');
        return;   
    }else{
        $("#justificacion_ali").css('background-color', 'white');
        $.ajax({
            async: true,
            cache: false,
            dataType: "html",
            url: "fichas/guardar.php",
            type: "POST",
            data: $("#frmalinear").serialize(),
            success: function (mensaje) {
                Alineaciones(0);
                $("#ModalComentariosEvidencia").dialog('close');
            }
        });
    }
}

function Evaluador(etapa, proyecto, subsector) {
    $.ajax({
        async: true,
        cache: false,
        dataType: "html",
        url: "fichas/alineacion.php",
        type: "POST",
        data: {idetapa: etapa, idproyecto: proyecto, idsubsector: subsector},
        success: function (mensaje) {
            $("#alineaciones_meta_atributo").empty().html(mensaje);
        },
        beforeSend: function (xhr) {
            $("#alineaciones_meta_atributo").empty().html("<div class='row' style='margin-top:90px'><i class='icon-spinner3' style='font-size:50px;'></i> <div><b>Cargando...</b></div></div>");
        },
        complete: function () {
            $('div.dataTables_filter input').addClass("form-control");
        }
    });
}
function buscarFicha() {
    $.post("tabla_fichas.php", {}, function (mensaje) {
        $("#contenedor").html(mensaje);
    });
}

function nuevaFicha() {
    $.post("fichas/modificar.php?je=cA==", {je: "cA=="}, function (mensaje) {
        $("#divEdit").html(mensaje);
    });
    $('#divEdit').dialog({
        show: "scale",
        hide: "scale",
        width: "80%",
        resizable: "false",
        title: "Agregar alineación",
        dialogClass: "edialog",
        close: function () {
            buscar();
        },
        modal: true
    });
}

function actualizaTablaFichas() {
    $.ajax({
        url: "tabla_fichas.php",
        type: "POST",
        data: $("#formulario").serialize(),
        success: function (data) {
            $("#contenedor").html(data);
        }
    });
}

function EliminarFicha(id, check) {
    $.confirm({
        title: '<img src=\"images/warning.png\">&nbsp;Advertencia!',
        content: '¿Esta seguro que desea  eliminarla de la base de datos?',
        type: 'red',
        theme: 'modern',
        boxWidth: '500px',
        useBootstrap: false,
        typeAnimated: true,
        buttons: {
            ok: {
                text: "ok!",
                btnClass: 'btn-red',
                keys: ['enter'],
                action: function () {
                    $.ajax({
                        url: "fichas/eliminar.php?je=" + id,
                        type: "POST",
                        data: {id: id},
                        success: function (data) {
                            buscarFicha('%');
                        }
                    });
                }
            },
            cancel: function () {
                check.checked = false;
            }
        }
    });

}

function EliminarPracticas_x_atributo(id_practicas_x_atributo, id_atrr_x_ficha, check) {

    $.confirm({
        title: '<img src=\"images/warning.png\">&nbsp;Advertencia!',
        content: 'Â¿Esta seguro que desea  eliminarla de la base de datos?',
        type: 'red',
        theme: 'modern',
        boxWidth: '500px',
        useBootstrap: false,
        typeAnimated: true,
        buttons: {
            ok: {
                text: "ok!",
                btnClass: 'btn-red',
                keys: ['enter'],
                action: function () {
                    $.ajax({async: false, cache: false,
                        dataType: "html",
                        url: "fichas/eliminar.php",
                        type: "POST",
                        data: {action: 'XL3', id_practicas_x_atributo: id_practicas_x_atributo},
                        success: function (data) {
                            RefrescarPracticas_x_atributo(id_atrr_x_ficha);
                        }
                    });
                }
            },
            cancel: function () {
                check.checked = false;
            }
        }
    });
}

function RefrescarPracticas_x_atributo(id_atrr_x_ficha) {

    var modal = '#ModalComentariosEvidencia';
    var dim = 1;
    $(".principales").each(function(){ 
        var active = $(this).hasClass('active'); 
        if(active){ 
            dim = $(this).data('iddime');
        }; 
    });
    $.post("fichas/guardar.php", {action: 'A501', id_atrr_x_ficha: id_atrr_x_ficha}, function (mensaje) {
        $("#BuenasPracticasATTR"+dim).find("#TablaBuenasPracticasATTR").empty().html(mensaje);
        $(modal).empty().html();
    });

}

function EditarPracticas_x_atributo(id_practicas_x_atributo, id_atrr_x_ficha) {

    var modal = '#ModalComentariosEvidencia';

    var eta = $("#etapa_").val();
    var attr = $("#attr_").val();
    $.post("fichas/guardar.php", {action: 'A502', id_practicas_x_atributo: id_practicas_x_atributo, id_atrr_x_ficha: id_atrr_x_ficha}, function (mensaje) {
        $(modal).empty().html(mensaje);
        $(".eta_").empty().append(eta);
        $(".attr_").empty().append(attr);
    });

    $(modal).dialog({
        resizable: false,
        modal: true,
        width: 'auto'
    });

}

function GuardaPracticas_x_atributo(id_practicas_x_atributo, id_atrr_x_ficha) {

    var buena_practica = $("#buena_practica_xy").val();
    var buena_practica_en = $("#buena_practica_en_xy").val();
    var publicada = $("#publicada_xy").prop('checked');
    
    // Validamos que tengan algo los campos
    if(buena_practica==''){
        $("#buena_practica_xy").css('background-color','#ffc4c4');
        return;
    }
    else
        $("#buena_practica_xy").removeAttr('style');
    

    $.post("fichas/guardar.php", {action: 'A503', id_practicas_x_atributo: id_practicas_x_atributo, id_atrr_x_ficha: id_atrr_x_ficha, buena_practica: buena_practica, buena_practica_en: buena_practica_en, publicada: publicada}, function (mensaje) {
        CerrarModalPracticas_x_atributo();
        RefrescarPracticas_x_atributo(id_atrr_x_ficha);
    });

}

function auditoria(ficha, date, etapa, id_proyecto) {

    $.post("fichas/auditoria.php", {action: 'A01', id_ficha_sostenibilidad: ficha, fecha: date, id_etapa: etapa, id_proyecto:id_proyecto}, function (mensaje) {
        $("#ModalListaFicha").empty().html(mensaje);
        $(document).ready(function () {
            $('#tbl_C1').DataTable({
                searching: false,
                lengthChange: false

            });
        });
    });

}

function CerrarModalPracticas_x_atributo() {
    $("#ModalComentariosEvidencia").dialog('close');
}
$('li').click(function () {
    $('li').removeClass('active');
    $(this).addClass('active');
});

function Ver(){
    $(".ver1").click(function () {
    var tr = '.' + $(this).data('tr');
    var ic = '.' + $(this).data('icono');
    if ($(tr).is(":visible")) {
        $(tr).hide();
        $(ic).removeClass("fa-chevron-up").addClass("fa-chevron-down");
    } else {
        $(tr).show();
        $(ic).removeClass("fa-chevron-down").addClass("fa-chevron-up");
    }
});
}
function reporteInternoFichaSostenibilidad(id_ficha_sostenibilidad, fecha, id_etapa) {

    $.post("fichas/reporteInternoFichaSostenibilidad.php", {ficha: id_ficha_sostenibilidad, fecha: fecha, etapa: id_etapa}, function (mensaje) {
        $("#ModalListaFicha").empty().html(mensaje);
    });

}

function GenerarPDF(project, ficha, etapa, action, OmisionyTerminado){
    
    if(OmisionyTerminado==1){ // Se general la Hoja 2

        // Crear el modal
        if ($("#ModaldeProceso").length == 0) {
            $("body").append('<!-- Modal --> <div id="ModaldeProceso" class="modal fade"> <div class="modal-dialog"> <div class="modal-content"> <div class="modal-body text-center"> <div class="spinner-border" role="status"> <span class="sr-only">Loading...</span> </div> <p id="modalMessage">Iniciando solicitud...</p> </div> </div> </div> </div>');
          }

        // Get modal elements
        var modal = document.getElementById("ModaldeProceso");
        var message = document.getElementById("modalMessage");

        // Mostrar Modal y bloquear ususario
        $(modal).modal({
        backdrop: 'static',
        keyboard: false
        });

        var xhr = new XMLHttpRequest();
        xhr.open("GET", "../../wp-content/themes/enfold-child/sostenibilidad_proceso.php", true);

        xhr.onreadystatechange = function() {
        if (xhr.readyState === XMLHttpRequest.OPENED) {
            message.innerHTML = "Iniciando proceso para generar la Hoja 2, Comparación de proyectos...";
        } else if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
            message.innerHTML = "Actualización tbl_general_attr_proyectos correctamente.";
            // Actualiza tabla de fichas
            ListaFichas(project, $("#ui-id-1").html());
                // Proceso para generar el PDF
                var mensaje="Deseas generar el PDF?";
                if(action=='A')
                    mensaje="Deseas actualizar el PDF?";
                $.confirm({
                    title: '<img src=\"images/warning.png\">&nbsp;Advertencia!',
                    content: mensaje,
                    type: 'red',
                    theme: 'modern',
                    boxWidth: '500px',
                    useBootstrap: false,
                    typeAnimated: true,
                    buttons: {
                        ok: {
                            text: "ok!",
                            btnClass: 'btn-red',
                            keys: ['enter'],
                            action: function () {
                                grafica("es", 0, project, ficha, etapa);
                            }
                        },
                        cancel: function () {
                        }
                    }
                });
            //GenerarPDF(objeto.proyecto, objeto.ficha, objeto.etapa, objeto.action);
            } else {
            message.innerHTML = "Error en la solicitud. Código de estado: " + xhr.status;
            }
            // Close the modal after the request has completed
            $(modal).modal('hide');
        }
        };

        xhr.send();
        // Fin

    }else{ 

        // Proceso para generar el PDF
        var mensaje="Deseas generar el PDF?";
        if(action=='A')
            mensaje="Deseas actualizar el PDF?";
        $.confirm({
            title: '<img src=\"images/warning.png\">&nbsp;Advertencia!',
            content: mensaje,
            type: 'red',
            theme: 'modern',
            boxWidth: '500px',
            useBootstrap: false,
            typeAnimated: true,
            buttons: {
                ok: {
                    text: "ok!",
                    btnClass: 'btn-red',
                    keys: ['enter'],
                    action: function () {
                        grafica("es", 0, project, ficha, etapa);
                    }
                },
                cancel: function () {
                }
            }
        });

    }

}

function grafica(lan='', pdf_final=0, proyecto, sostenibilidad, etapa){
    var url = "fichas/ficha_ajax.php"
    var proyectos_select = proyecto;
    if(proyectos_select!=""){
        jQuery.ajax({
            url : url,
            type : 'POST',
            async: true,
            data : { m : 'GRAFICA1', id_proyecto: proyectos_select,id_ficha:sostenibilidad, lang:lan },       
            beforeSend: function (xhr) {
                proceso("#proceso","show");
                actualizarProgreso(5, "Obteniendo datos de la gráfica...");
            },
            success : function ( data, textStatus, jqXHR) { 
                actualizarProgreso(10, "Preparando gráfica de sostenibilidad...");
                grafica_soste(data, 0,lan, pdf_final, proyectos_select, sostenibilidad, etapa);
            },
            complete:function(data){
            }
        })
    }
}

function grafica_soste(data, destroye=0,lan, pdf_final=0, proyecto, sostenibilidad, etapa){
    actualizarProgreso(15, "Creando gráfica de sostenibilidad...");
    setTimeout(function(){
        actualizarProgreso(20, "Renderizando gráfica...");
        var g = JSON.parse(data);
        var prom_x_pro = g['promedio_x_proyecto'];
        var prom_x_pro_x_sub = g['promedio_x_proyecto_subsector']; 
        var dimension = g['dimensiones'];
        var borderWidth = 10;
        var yAxes = 40;
        var xAxes = 40;
        var tamañoPunto = 22;
        var chartData = {
            //labels: dimension,
            datasets: [{
            type: 'bubble',
            borderColor: '#FACC2E',
            backgroundColor: '#FACC2E',
            hoverBorderColor: '#FACC2E',
            hoverBackgroundColor: '#FACC2E',
            borderWidth: borderWidth,
            radius: tamañoPunto,
            usePointStyle:true,
            data: prom_x_pro,        
            label:'Proyecto',        
            pointStyle:'circle',
            //pointRadius: 10
        }, {
            type: 'bar',
            label: 'Promedio del subsector',
            borderColor: [
              "#015E5C",
              "#037D7A",
              "#029591",
              "#02B3AF" 
            ],
            hoverBorderColor : [
              "#015E5C",
              "#037D7A",
              "#029591",
              "#02B3AF" 
            ],
            backgroundColor: [
              "#015E5C",
              "#037D7A",
              "#029591",
              "#02B3AF" 
            ],
            hoverBackgroundColor: [
              "#015E5C",
              "#037D7A",
              "#029591",
              "#02B3AF" 
            ],
            borderWidth: 0,
            data: prom_x_pro_x_sub,
    }],
        };                

        var ctx = "";
        if(lan=='en'){
            ctx = document.getElementById('canvas_en');
            $("#canvas_en").show();
        }
        else{
            ctx = document.getElementById('canvas');
            $("#canvas").show();
        }
        window.myMixedChart = new Chart(ctx, {
            type: 'bar',
            data: chartData,
            options: {
                responsive: true,
                animation: {
                    onComplete: function(animation){                                
                        actualizarProgreso(30, "Guardando gráfica de sostenibilidad...");
                        save_grafica(this.toBase64Image(),lan, pdf_final, proyecto, sostenibilidad, etapa);
                    }
                },
                title: {
                    display: true,
                    text: ''
                },
                tooltips: {
                    mode: 'index',
                    intersect: true
                },
                scales: {
                    yAxes: [{                    
                        ticks: {
                            fontSize: yAxes,
                            fontFamily: 'Helvetica Neue,Helvetica,Arial,sans-serif',
                            min:0,
                            max:3                        
                        }
                    }],
                    xAxes: [{
                        labels: dimension,
                        ticks: {                        
                            fontSize: xAxes,
                            fontFamily: 'Helvetica Neue,Helvetica,Arial,sans-serif',
                            maxRotation:0,
                            minRotation:0,
                            callback: function(label, index, labels) {
                                if (/\s/.test(label)) {
                                  return label.split("|");
                                }else{
                                  return label;
                                }              
                            }
                        },                    
                    }]
                },
                layout: {
                    padding: {
                        top: 10,                    
                    }
                },
                legend: {
                        display: false,
                }
           }
        });
    }, 3000);

}

function save_grafica(img,lan, pdf_final=0, proyecto, sostenibilidad, etapa){
    var url = "fichas/ficha_ajax.php";        
    $.ajax({
        url : url,
        type : 'POST',
        async: true,
        data : { m : 'SAVE1', imggrafica: img, id_proyecto: proyecto, id_ficha: sostenibilidad, lang:lan, pdf:'pdf_'},           
        success : function ( data, textStatus, jqXHR) { 
            actualizarProgreso(35, "Gráfica guardada. Verificando guardado...");
        },
        complete:function(){
            // Pequeño delay para asegurar que el archivo se haya escrito completamente en el disco
            setTimeout(function(){
                actualizarProgreso(38, "Generando primer PDF...");
                pdf_sos_1er(pdf_final, proyecto, sostenibilidad, lan, etapa);
                var ctx = "";
                if(lan=='en')
                    $('#canvas_en').hide();
                else
                    $('#canvas').hide();
            }, 500); // Esperar 500ms para que el archivo se guarde
        }
    })
}

function pdf_sos_1er(pdf_final=0, proyecto, sostenibilidad, lang='es', etapa){
    var id_sostennibility = sostenibilidad;                  
    var proyecto_id = proyecto;
    var url = "fichas/pdfsos.php";
    if(lang=='en')
        url = "fichas/pdfsos_en.php";
    $.ajax({
        async: true, // indica que seguira trabajando sin esperar a que termine
        cache: false, // No guarda en cache
        dataType: "html",                    
        type: 'POST',
        data: {id_sostenibilidad: id_sostennibility,id_proyecto:proyecto_id, pdf_final: pdf_final },
        url: url,
        beforeSend: function() {
            actualizarProgreso(40, "Generando primer PDF (SOS_1.pdf)...");
        },
        success: function (respuesta){
            actualizarProgreso(50, "Primer PDF generado. Preparando gráfica radial...");
        },
        complete: function () {
            GraficaRadialGetValue(proyecto_id, etapa, id_sostennibility, lang);
        },
        error: function (objXMLHttpRequest) {}
    });
}

// Generar la grafica radial para guardarla
function GraficaRadialGetValue(idproyecto, etapa, id_ficha, lang) {
    $.ajax({async: true,
        cache: false,
        dataType: "html",
        url: "./fichas/SGRadial.php",
        type: "POST",
        data: {id_proyecto: idproyecto, id_etapa: etapa, id_ficha_sostenibilidad:id_ficha,get_values: 1},
        beforeSend: function (xhr) {
            $("#chartdiv_es").show();
            actualizarProgreso(55, "Obteniendo datos de la gráfica radial...");
        },
        success: function (data) {
            actualizarProgreso(60, "Creando gráfica radial...");
            var datos2 = JSON.parse(data);
            GraficaRadial_Crear(datos2, idproyecto, etapa, id_ficha, lang);
        },
        complete: function () {
            
        }
    });
}

function GraficaRadial_Crear(datos, idproyecto, etapa, idficha, lang) {
    am4core.useTheme(am4themes_animated);
    var chart = am4core.create("chartdiv_es", am4charts.RadarChart);

    chart.data = datos;
    chart.radius = am4core.percent(80);
    chart.startAngle = 199.5;
    chart.endAngle = 390.5;

    /* Add inner radius */
    chart.innerRadius = am4core.percent(20);
    /* Add cursor */
    chart.cursor = new am4charts.RadarCursor();

    var categoryAxis = chart.xAxes.push(new am4charts.CategoryAxis());
    categoryAxis.dataFields.category = "category";
    //categoryAxis.dataFields.imagen = "imagen";
    categoryAxis.renderer.minGridDistance = 45;
    categoryAxis.renderer.grid.template.strokeOpacity = 0.2;
    categoryAxis.renderer.labels.template.location = 100;
    categoryAxis.renderer.grid.template.location = 1;
    categoryAxis.interactionsEnabled = false;
    categoryAxis.disabled = false;
    // Ponemos transparente el nombre de las categorias
    //categoryAxis.renderer.inside = false;
    categoryAxis.renderer.labels.template.fill = am4core.color("#FFFFFF80");
    categoryAxis.renderer.maxLabelPosition = 0;

    var valueAxis = chart.yAxes.push(new am4charts.ValueAxis());
    valueAxis.tooltip.disabled = true;
    valueAxis.renderer.minGridDistance = 20;
    valueAxis.renderer.grid.template.strokeOpacity = 0.2;
    valueAxis.renderer.labels.template.fontSize = 14;
    valueAxis.renderer.labels.template.stroke = am4core.color("#000");
    valueAxis.interactionsEnabled = false;
    valueAxis.renderer.axisAngle = 10;
    //valueAxis.renderer.gridType = "polygons";
    valueAxis.min = 0;
    valueAxis.max = 10;
    //pra los numeros
    chart.seriesContainer.zIndex = -5;

    var series1 = chart.series.push(new am4charts.RadarColumnSeries());
    series1.columns.template.width = am4core.percent(100);
    series1.name = "Tier 1";
    series1.dataFields.valueY = "value1";
    series1.strokeWidth = 0;
    series1.dataFields.categoryX = "category";
    series1.columns.template.tooltipText = "{name}: {valueY.value}";
    series1.stacked = true;
    series1.columns.template.stroke = am4core.color("#09ECF1");
    series1.columns.template.fill = am4core.color("#09ECF1");

    var series2 = chart.series.push(new am4charts.RadarColumnSeries());
    series2.columns.template.width = am4core.percent(100);
    series2.columns.template.tooltipText = "{name}: {valueY.value}";
    series2.name = "Tier 2";
    series2.strokeWidth = 0;
    series2.dataFields.categoryX = "category";
    series2.dataFields.valueY = "value2";
    series2.stacked = true;
    series2.columns.template.stroke = am4core.color("#0BC9CD");
    series2.columns.template.fill = am4core.color("#0BC9CD");

    var series3 = chart.series.push(new am4charts.RadarColumnSeries());
    series3.columns.template.width = am4core.percent(100);
    series3.columns.template.tooltipText = "{name}: {valueY.value}";
    series3.name = "Tier 3";
    series3.strokeWidth = 0;
    series3.dataFields.categoryX = "category";
    series3.dataFields.valueY = "value3";
    series3.stacked = true;
    series3.columns.template.stroke = am4core.color("#119DA0");
    series3.columns.template.fill = am4core.color("#119DA0");

    /* Create and configure series */
    var series = chart.series.push(new am4charts.RadarSeries());
    series.dataFields.valueY = "rango";
    series.dataFields.categoryX = "category";
    series.name = "Rango";
    series.stroke = am4core.color("#DCDCDC");
    series.strokeOpacity = 100;
    series.fillOpacity = 0;
    series.strokeWidth = 1;
    //series.propertyFields.strokeDasharray = "lineDash";
    var circleBullet = series.bullets.create(am4charts.CircleBullet);
    series.dataItems.template.locations.categoryX = 0.5; // Poner en medio el circulo
    circleBullet.circle.radius = 5;
    circleBullet.circle.stroke = am4core.color("#DCDCDC");
    circleBullet.circle.fillOpacity = 5;
    circleBullet.circle.fill = am4core.color("#DCDCDC");
    circleBullet.circle.strokeOpacity = 5;
    circleBullet.circle.strokeWidth = 0;


    var slider = chart.createChild(am4core.Slider);
    slider.start = 1.8;
    slider.exportable = false;
    slider.events.on("rangechanged", function () {

        var start = slider.start;

        chart.startAngle = 91 - start * 179;
        chart.endAngle = 91 + start * 179;

        valueAxis.renderer.axisAngle = chart.startAngle;
    });

    // Actualizar progreso mientras se crea el gráfico (incremento más lento)
    var progresoInterval = setInterval(function(){
        var currentProgress = parseInt($("#proceso-porcentaje").text().replace('%', '')) || 60;
        if(currentProgress < 75){
            actualizarProgreso(currentProgress + 1, "Renderizando gráfica radial...");
        } else {
            clearInterval(progresoInterval);
        }
    }, 800);
    
    setTimeout(function(){
        clearInterval(progresoInterval);
        actualizarProgreso(75, "Exportando imagen de la gráfica radial...");
        let options = chart.exporting.getFormatOptions("png");
        options.quality = 1;
        options.scale = 4.5;
        chart.exporting.getImage("png", options).then(function (imgData) {
            actualizarProgreso(80, "Guardando gráfica radial...");
            // Asegurar que la imagen esté en formato base64 completo
            if(!imgData.startsWith('data:image/png;base64,')){
                imgData = 'data:image/png;base64,' + imgData;
            }
            
            $.ajax({async: true,
                cache: false,
                dataType: "html",
                url: "fichas/SGRadial.php",
                type: "POST",
                data: {id_proyecto: idproyecto, img_grafica: imgData, id_ficha_sostenibilidad:idficha, get_values: 0},
                before: function () {},
                success: function (data) {
                    actualizarProgreso(85, "Gráfica radial guardada. Verificando guardado...");
                },
                complete: function () {
                    // Pequeño delay para asegurar que la gráfica radial se haya guardado en la BD
                    setTimeout(function(){
                        actualizarProgreso(88, "Generando segundo PDF...");
                        // Pasar la imagen directamente para evitar problemas de sincronización
                        var gradialParam = imgData;
                        if(lang=='es')
                            pdf_giz_2('ES', idproyecto, etapa, idficha, gradialParam);
                        else
                            pdf_giz_2('EN', idproyecto, etapa, idficha, gradialParam);
                    }, 800); // Esperar 800ms para que se guarde en la BD
                }
            });
        });
    }, 10000);


}

function pdf_giz_2(lang, id_pro, id_eta, id_ficha, Gradial="") {
    var proyecto_id = id_pro;
    var etapa_id = id_eta;
    var file = "create_pdf_2_es.php";
    if (lang == "EN")
        file = "create_pdf_2_en.php";
    $.ajax({
        async: true, // indica que seguira trabajando sin esperar a que termine
        cache: false, // No guarda en cache
        dataType: "html",
        type: 'POST',
        data: {id_proyecto: proyecto_id, id_etapa: etapa_id, id_ficha_sostenibilidad:id_ficha, radial: Gradial, lang: lang},
        url: "fichas/" + file,
        beforeSend: function () {
            $("#chartdiv_es").empty().hide();
            actualizarProgreso(90, "Generando segundo PDF (SOS_2.pdf)...");
        },
        success: function (respuesta){
            actualizarProgreso(95, "Uniendo archivos PDF...");
        },
        complete: function () {
            if(lang=='EN'){
                actualizarProgreso(100, "¡PDF generado exitosamente!");
                setTimeout(function(){
                    proceso("#proceso", "hide");
                    ListaFichas(id_pro);
                }, 500);
            }else{
                // Para español, generar también el PDF en inglés, reiniciando el progreso
                actualizarProgreso(100, "PDF en español completado. Iniciando PDF en inglés...");
                setTimeout(function(){
                    // Reiniciar progreso para el proceso en inglés
                    actualizarProgreso(0, "Iniciando generación de PDF en inglés...");
                    grafica("en", 0, id_pro, id_ficha, id_eta);
                }, 1000);
            }
        },
        error: function (objXMLHttpRequest) {}
    });
}

function proceso(item='#proceso',action='show'){
    if(action=='hide')
        $(item).modal(action);
    else {
        // Reiniciar el porcentaje cuando se muestra el modal
        actualizarProgreso(0, "Iniciando proceso...");
        $(item).modal({backdrop: 'static', keyboard: false, action: action});
    }
}

// Función para actualizar el porcentaje del proceso
function actualizarProgreso(porcentaje, mensaje){
    porcentaje = Math.min(100, Math.max(0, porcentaje)); // Asegurar que esté entre 0 y 100
    $("#proceso-porcentaje").text(porcentaje + "%");
    if(mensaje){
        $("#proceso-mensaje-paso").text(mensaje);
    }
}

function myCollapse(element){

        if ($(element).hasClass('fa-minus-square')){
            $(element).removeClass("fa-minus-square").addClass("fa-plus-square");
        }else{
            $(element).removeClass("fa-plus-square").addClass("fa-minus-square");
        }

}

function myCollapseG(){
    
    $('#TableInternaDatos .collapse').collapse('hide');
    $( "#i, #collapseNormal").each(function() {
        $(this).removeClass("fa-minus-square").addClass("fa-plus-square");
        $(this).collapse('hide');
      });
      
}

// Advertencia de caracteres que se mostraran en el PDF en EspaÃ±ol
$(document).ready(function() {
    if($("#ResumenSostenibilidad_ES").length){
        var len = $("#ResumenSostenibilidad_ES").val().length;
        $('#adCount_ES').text(394 - len);
        if(len>394){
            $("#adCount_ES").css("color","#9E360C");
            $('#ad_ES').text("Advertencia, los Ãºltimos "+((394-len )* -1)+" caracteres del Resumen de la ficha en espaÃ±ol, no serÃ¡n visibles en el PDF");
            $("#ad_ES").css("color","#8C360F");
        }else{
            $("#adCount_ES").css("color","#587023");
            $('#ad_ES').text("");
        }
    }
});

$( "#ResumenSostenibilidad_ES" ).keyup(function() {
    var len = $(this).val().length
    $('#adCount_ES').text(394-len);
    if(len>394){
        $("#adCount_ES").css("color","#9E360C");
        $('#ad_ES').text("Advertencia, los Ãºltimos "+((394-len )* -1)+" caracteres del Resumen de la ficha en espaÃ±ol, no serÃ¡n visibles en el PDF");
        $("#ad_ES").css("color","#8C360F");
    }else{
        $("#adCount_ES").css("color","#587023");
        $('#ad_ES').text("");
    }
});

// Advertencia de caracteres que se mostraran en el PDF en Ingles
$(document).ready(function() {
    if($("#ResumenSostenibilidad_EN").length){
        var len = $("#ResumenSostenibilidad_EN").val().length
        $('#adCount_EN').text(394 - len);
        if(len>394){
            $("#adCount_EN").css("color","#9E360C");
            $('#ad_EN').text("Advertencia, los Ãºltimos "+((394-len )* -1)+" caracteres del Resumen de la ficha en ingles, no serÃ¡n visibles en el PDF");
            $("#ad_EN").css("color","#8C360F");
        }else{
            $("#adCount_EN").css("color","#587023");
            $('#ad_EN').text("");
        }
    }
});

$( "#ResumenSostenibilidad_EN" ).keyup(function() {
    var len = $(this).val().length
    $('#adCount_EN').text(394 - len);
    if(len>394){
        $("#adCount_EN").css("color","#9E360C");
        $('#ad_EN').text("Advertencia, los Ãºltimos "+((394-len )* -1)+" caracteres del Resumen de la ficha en ingles, no serÃ¡n visibles en el PDF");
        $("#ad_EN").css("color","#8C360F");
    }else{
        $("#adCount_EN").css("color","#587023");
        $('#ad_EN').text("");
    }
});

$(document).ready(function(){
   $(".tab-content .btn-primary").click(function(){
        $.post("fichas/auditoria.php", {a: true, id_ficha_sostenibilidad: f_, id_etapa: e_}, function (mensaje) {
            if(mensaje=='0'){
                $("#Gpublicada").css({"backgroundColor": "#337ab7", "color": "#fff", "border-color": "#2e6da4"});
                $("#Gpublicada").attr("value", "1");
                $("#Gpublicada").text("Terminada");
                $("#GpublicadaEtiqueta").text("Ficha terminada (publicada)");
            }else{
                $("#Gpublicada").css({"backgroundColor": "#f0ad4e", "color": "#fff", "border-color": "#eea236"});
                $("#Gpublicada").attr("value", "0");
                $("#Gpublicada").text("Borrador");
                alert("Tu ficha Cambio a borrador!");
                // Ejecutar update del estatus de la ficha a borrador
            }
        }); 
     });
});

function exportToHtml(Ficha) {
    // Obtener el contenido del elemento a exportar
    var content = document.getElementById('ModalListaFicha').innerHTML;

    // Eliminar los elementos que no deseas exportar
    content = content.replace(/<button.*<\/button>/g, '');
  
    // Agregar el encabezado y pie de página
    // https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css
    content = '<!DOCTYPE html>\n<html>\n<head>\n<meta charset="utf-8">\n<title>Título de la página</title>\n<link rel="stylesheet" href="https://www.proyectosmexico.gob.mx/observatorio/css/bootstrap/bootstrapv3.3.7.min.css">\n</head>\n<body>\n' + content + '\n<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>\n</body>\n</html>';

    // Codificar el contenido en una cadena de consulta
    var encodedContent = encodeURIComponent(content);
    
    // Crear un enlace de descarga
    var link = document.createElement('a');
    link.setAttribute('href', 'data:text/html;charset=utf-8,' + encodedContent);

  
    // Nombre de la Fucha
    const fecha = new Date();
    const dia = fecha.getDate();
    const mes = fecha.getMonth() + 1; // Los meses comienzan en 0, por lo que hay que sumar 1
    const fechaFormateada = `${dia}_${mes}_${fecha.getFullYear()}`;
    var nombre = Ficha+"_"+fechaFormateada+".html";

    // Personalizar el nombre del archivo de descarga
    link.setAttribute('download', nombre);
    
    // Agregar el enlace al cuerpo del documento
    document.body.appendChild(link);
    
    // Hacer clic en el enlace para descargar el archivo
    link.click();
    
    // Eliminar el enlace del cuerpo del documento
    document.body.removeChild(link);
  }
  
  
  
  