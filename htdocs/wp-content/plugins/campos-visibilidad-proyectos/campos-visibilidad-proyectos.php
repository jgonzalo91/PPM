<?php
/*
Plugin Name: Campos de Visibilidad - Proyectos Prioritarios
Description: Administra los campos visibles para el CPT 'proyecto_prioritario', genera IDs únicos y permite exportar a CSV.
Version: 1.0
Author: Jesus Gonzalez
*/


// Crear página de opciones
add_action('admin_menu', 'crear_menu_campos_visibilidad');
function crear_menu_campos_visibilidad() {
    add_menu_page(
        'Campos de Visibilidad',
        'Campos Visibilidad Proyectos Prioritarios',
        'manage_options',
        'campos-visibilidad',
        'campos_visibilidad_admin_page',
        'dashicons-visibility',
        100
    );
}

// Encolar jQuery UI Sortable en admin
add_action('admin_enqueue_scripts', 'enqueue_sortable_script');
function enqueue_sortable_script() {
    wp_enqueue_script('jquery-ui-sortable');
}

// Renderizar la página
function campos_visibilidad_admin_page() {
    $campos = get_option('campos_visibilidad_proyecto', array());

    if (isset($_POST['campos_visibilidad_nonce']) && wp_verify_nonce($_POST['campos_visibilidad_nonce'], 'guardar_campos_visibilidad')) {
        $nuevos_campos = array();

        if (!empty($_POST['campo_key']) && !empty($_POST['campo_label'])) {
            foreach ($_POST['campo_key'] as $i => $key) {
                $key = sanitize_text_field($key);
                $label = sanitize_text_field($_POST['campo_label'][$i]);
                if ($key && $label) {
                    $nuevos_campos[$key] = $label;
                }
            }
        }

        update_option('campos_visibilidad_proyecto', $nuevos_campos);
        $campos = $nuevos_campos;
        echo '<div class="updated"><p>Campos guardados correctamente.</p></div>';
    }
    ?>
    <div class="wrap">
        <h1>Editar campos de visibilidad</h1>
        <form method="post">
            <?php wp_nonce_field('guardar_campos_visibilidad', 'campos_visibilidad_nonce'); ?>
	        	    
	
	<table class="form-table" id="campos-table">
                <thead>
                    <tr>
                        <th>Nombre del campo (key)</th>
                        <th>Etiqueta (label)</th>
                        <th>Eliminar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($campos as $key => $label): ?>
                        <tr>
                            <td><input type="text" name="campo_key[]" value="<?php echo esc_attr($key); ?>"></td>
                            <td><input type="text" name="campo_label[]" value="<?php echo esc_attr($label); ?>"></td>
                            <td><button type="button" class="button eliminar-fila">✖</button></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td><input type="text" name="campo_key[]"></td>
                        <td><input type="text" name="campo_label[]"></td>
                        <td><button type="button" class="button eliminar-fila">✖</button></td>
                    </tr>
                </tbody>
            </table>
</div>



            <p><button type="button" class="button" id="agregar-fila">+ Agregar campo</button></p>
            <p><input type="submit" class="button button-primary" value="Guardar"></p>
        </form>
    </div>

    <script>
        jQuery(document).ready(function($){
            $('#campos-table tbody').sortable({
                axis: 'y',
                cursor: 'move',
                containment: 'parent',
                items: 'tr',
                opacity: 0.7
            });

            $('#agregar-fila').on('click', function () {
                var row = '<tr>' +
                          '<td><input type="text" name="campo_key[]"></td>' +
                          '<td><input type="text" name="campo_label[]"></td>' +
                          '<td><button type="button" class="button eliminar-fila">✖</button></td>' +
                          '</tr>';
                $('#campos-table tbody').append(row);
            });

            $(document).on('click', '.eliminar-fila', function () {
                $(this).closest('tr').remove();
            });
        });
    </script>
    <?php
}

// Metabox
add_action('add_meta_boxes', 'agregar_metabox_visibilidad');
function agregar_metabox_visibilidad() {
    add_meta_box(
        'visibilidad_campos',
        'Visibilidad de Campos',
        'render_visibilidad_campos_box',
        'proyecto_prioritario',
        'side',
        'high'
    );
}


function render_visibilidad_campos_box($post) {
    $campos = get_option('campos_visibilidad_proyecto', array());
	$url_editar = admin_url('admin.php?page=campos-visibilidad');


?>

<p>
<style>
    #marcar-todos:indeterminate {
        background-color: #1e1e1e;
        border-color: #1e1e1e;
        position: relative;
    }
    #marcar-todos:indeterminate::before {
        content: '';
        display: block;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 8px;
        height: 8px;
        background-color: white;
    }
</style>
<label>
<input type="checkbox" id="marcar-todos"/>Marcar Todos
</label>
</p>

 <div id="contenedor-campos-visibilidad" style="max-height: 250px; overflow-y: auto; border: 1px solid #ccc; padding: 5px; background: #fff;">
<?php

    foreach ($campos as $clave => $etiqueta) {
        $valor = get_post_meta($post->ID, "_mostrar_$clave", true);
        $checked = ($valor === '1') ? 'checked="checked"' : '';

        echo '<p><label>';
        echo '<input class="checkbox-individual" type="checkbox" name="mostrar_' . esc_attr($clave) . '" value="1" ' . $checked . '> ';
        echo esc_html($etiqueta);
        echo '</label></p>';
    }

	?>
</div>


<p style="text-align: right; margin-top: 10px;">
        <a href="<?php echo esc_url($url_editar); ?>" class="button button-secondary" target="_blank">Editar</a>
    </p>


    <script>
        jQuery(document).ready(function($){
            // Función para actualizar el estado del checkbox "Marcar Todos"
            function actualizarMarcarTodos() {
                var total = $('.checkbox-individual').length;
                var marcados = $('.checkbox-individual:checked').length;
                
                $('#marcar-todos').prop('indeterminate', false);
                
                if (marcados === 0) {
                    $('#marcar-todos').prop('checked', false);
                } else if (marcados === total) {
                    $('#marcar-todos').prop('checked', true);
                } else {
                    $('#marcar-todos').prop('indeterminate', true);
                }
            }

            // Evento para "Marcar Todos"
            $('#marcar-todos').on('change', function(){
                $('.checkbox-individual').prop('checked', this.checked);
                actualizarMarcarTodos();
            });

            // Evento para checkboxes individuales
            $('.checkbox-individual').on('change', function(){
                actualizarMarcarTodos();
            });

            // Actualizar estado inicial
            actualizarMarcarTodos();
        });
    </script>
    <?php

}


// Guardar metabox
add_action('save_post', 'guardar_campos_visibilidad_post');
add_action('save_post_proyecto_prioritario', 'guardar_campos_visibilidad_post');
function guardar_campos_visibilidad_post($post_id) {





    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

	if (!current_user_can('edit_post', $post_id)) return;

    $campos = get_option('campos_visibilidad_proyecto', array());

    foreach ($campos as $clave => $etiqueta) {
        $meta_key = "_mostrar_$clave";

        if (isset($_POST["mostrar_$clave"])) {
            update_post_meta($post_id, $meta_key, '1');
        } else {
            update_post_meta($post_id, $meta_key, '0');
        }
    }
}


// se crea pagina Exporta Poryectos Prioritarios y se agrega al menu del admin de wordpress
/*add_action('admin_menu', 'pagina_exportar_proyectos_csv'); 

function pagina_exportar_proyectos_csv() {
    add_menu_page(
    'Exportar Proyectos',               // Título de la página (aparece en la parte superior de la página)
    'Exportar Proyectos Prioritarios',  // Título del menú (lo que ves en el menú lateral)
    'manage_options',                   // solo admin puede verla
    'exportar-proyectos-csv',          // Slug del menú (se usa como identificador)
    'mostrar_pagina_exportacion',      // Función que muestra el contenido de la página
    'dashicons-download',              // Icono del menú (de la librería Dashicons)
    20                                  // Posición en el menú
    );
}*/


// se crea el boton para exportar los datos y se manda a llamar admin-post para procesar la peticios de creacion del CSV(si no se manda, la pagina se cae)
/*function mostrar_pagina_exportacion() {
    ?>
    <div class="wrap">
        <h1>Exportar Proyectos Prioritarios</h1>
        <p>Haz clic en el botón para descargar los proyectos prioritarios en formato CSV.</p>
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
            <input type="hidden" name="action" value="exportar_proyectos_csv">
            <?php submit_button('Exportar a CSV', 'primary'); ?>
        </form>
    </div>
    <?php
}*/


//creacion del hook
add_action('restrict_manage_posts', 'boton_exportar_proyectos_prioritarios');
function boton_exportar_proyectos_prioritarios($post_type) {
    if ($post_type === 'proyecto_prioritario') {
        $url = admin_url('admin-post.php?action=exportar_proyectos_csv');
        echo '<a href="' . esc_url($url) . '" class="button button-primary" style="margin-left:10px;">Exportar a CSV</a>';
    }
}




add_action('admin_post_exportar_proyectos_csv', 'exportar_proyectos_csv');

function exportar_proyectos_csv() {
    if (!current_user_can('manage_options')) {
        wp_die('No tienes permisos suficientes para exportar.');
    }

    nocache_headers();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=proyectos_publicados.csv');
    header('Pragma: no-cache');
    header('Expires: 0');

    $output = fopen('php://output', 'w');

    // codificación UTF-8 BOM
    fwrite($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

    $proyectos = get_posts(array(
        'post_type' => 'proyecto_prioritario',
        'post_status' => 'any',
        'posts_per_page' => -1
    ));

    if (empty($proyectos)) {
        fputcsv($output, array('No hay proyectos publicados'));
        fclose($output);
        exit;
    }

    
    
    

    $meta_keys = [];
    $subcampos_repeaters = [];

    foreach ($proyectos as $p) {
        $metas = get_post_meta($p->ID);
       // detectar repeaters
foreach ($metas as $key => $value) {
    if ($key[0] === '_') continue;

    $val = maybe_unserialize($value[0]);

    // PHP 5.4 no tiene array_key_first(), así que hacemos esto:
    $primer_indice = null;
    if (is_array($val)) {
        foreach ($val as $k => $v) {
            $primer_indice = $k;
            break;
        }
    }

    if (is_array($val) && is_numeric($primer_indice)) {
        // Es probable que sea un repeater
        foreach ($val as $fila) {
            if (is_array($fila)) {
                foreach ($fila as $subkey => $subval) {
                    $full_key = $key . '_' . $subkey;
                    if (!in_array($full_key, $meta_keys)) {
                        $meta_keys[] = $full_key;
                        $subcampos_repeaters[$full_key] = array($key, $subkey);
                    }
                }
            }
        }
    } else {
        if (!in_array($key, $meta_keys)) {
            $meta_keys[] = $key;
        }
    }
}

    }

// 🔴 Eliminar columnas no deseadas del CSV
$meta_keys = array_diff($meta_keys, ['id_unico_proyecto', 'id_unico_proyecto_base','compras_mx_proyecto','mia_por_proyecto','registros_ui_por_proyecto','otras_ligas_por_proyecto','plan_pertenece','ejes_objeticos_y_estrategias','dato_por_fuente']);


/*foreach ($meta_keys as $key) {
    $header[] = isset($etiquetas_amigables[$key]) ? $etiquetas_amigables[$key] : $key;
}*/

$etiquetas_visibilidad = get_option('campos_visibilidad_proyecto', []);
$header = ['ID Único Proyecto', 'Proyecto | Iniciativa'];


foreach ($meta_keys as $key) {
    $etiqueta_final = $key; // Valor por defecto

    // Detectar si es un subcampo de repeater: algo_0_algo
    if (preg_match('/^([a-zA-Z0-9_]+)_\d+_[a-zA-Z0-9_]+$/', $key, $matches)) {
        $campo_principal = $matches[1];

        // Intentar encontrar su label en la config de visibilidad
        if (isset($etiquetas_visibilidad[$campo_principal])) {
            $etiqueta_final = $etiquetas_visibilidad[$campo_principal];
        } elseif (isset($etiquetas_amigables[$campo_principal])) {
            $etiqueta_final = $etiquetas_amigables[$campo_principal];
        } else {
            $etiqueta_final = $campo_principal;
        }
    } 
    // Si no es repeater
    else {
        if (isset($etiquetas_visibilidad[$key])) {
            $etiqueta_final = $etiquetas_visibilidad[$key];
        } elseif (isset($etiquetas_amigables[$key])) {
            $etiqueta_final = $etiquetas_amigables[$key];
        }
    }

    $header[] = $etiqueta_final;
}





    fputcsv($output, $header);

    foreach ($proyectos as $proyecto) {
        $row = [
            //$proyecto->ID,
		 //get_post_meta($proyecto->ID, 'id_unico_proyecto', true),
"\t" . get_post_meta($proyecto->ID, 'id_unico_proyecto', true),            
get_the_title($proyecto->ID),
        ];

        foreach ($meta_keys as $key) {
            if (isset($subcampos_repeaters[$key])) {
                list($parent_key, $subkey) = $subcampos_repeaters[$key];
                $val = get_post_meta($proyecto->ID, $parent_key, true);

                $val = maybe_unserialize($val);
                $valores = [];

                if (is_array($val)) {
                    foreach ($val as $fila) {
                        if (isset($fila[$subkey])) {
                            // Aquí usamos convertir_valor_para_csv para manejar subarrays o limpieza
                            $valores[] = convertir_valor_para_csv($fila[$subkey]);
                        }
                    }
                }

                // Unimos con salto de línea para mejor lectura en Excel (multi-línea en celda)
                $row[] = implode("\n", $valores);
            } else {
                $val = get_post_meta($proyecto->ID, $key, true);
                $row[] = convertir_valor_para_csv($val);
            }
        }

        fputcsv($output, $row);
    }

    fclose($output);
    exit;
}


// funcion para limpiar campos y manejar arrays anidados (repeater también)
function convertir_valor_para_csv($val) {
    if (is_array($val)) {
        // Si es un array de arrays (repeater o similar)
        if (isset($val[0]) && is_array($val[0])) {
            $filas = [];
            foreach ($val as $fila) {
                $subcampos = [];
                foreach ($fila as $subkey => $subval) {
                    $subcampos[] = $subkey . ': ' . convertir_valor_para_csv($subval);
                }
                $filas[] = implode(' | ', $subcampos);
            }
            return implode("\n", $filas);
        } else {
            // Array simple
            return implode(', ', array_map('convertir_valor_para_csv', $val));
        }
    }

    if (is_numeric($val) && get_post_status($val)) {
        $post = get_post($val);
        return $post ? $post->post_title : $val;
    }

    if (is_string($val)) {
        $val = wp_strip_all_tags($val);
        return trim(preg_replace('/\s+/', ' ', $val));
    }

    return $val;
}


/*add_action('restrict_manage_posts', 'boton_exportar_proyectos_prioritarios');
function boton_exportar_proyectos_prioritarios($post_type) {
    if ($post_type === 'proyecto_prioritario') {
        $url = admin_url('admin-post.php?action=exportar_proyectos_prioritarios');
        echo '<a href="' . esc_url($url) . '" class="button button-primary" style="margin-left:10px;">Exportar a CSV</a>';
    }
}*/


// Forzar ID único al guardar proyecto_prioritario
add_action('acf/save_post', 'forzar_id_unico_proyecto_final', 20);


function forzar_id_unico_proyecto_final($post_id) {
    if (get_post_type($post_id) !== 'proyecto_prioritario') return;

    // Obtener los códigos directamente de los campos ACF
    $id_sector = get_field('sector_proyecto', $post_id) ?: '00';
    $id_subsector = get_field('subsector_proyecto', $post_id) ?: '00';

    $codigo_sector = str_pad((string) get_field('id_sector', $id_sector), 2, '0', STR_PAD_LEFT);
    $codigo_subsector = str_pad((string) get_field('id_subsector', $id_subsector), 2, '0', STR_PAD_LEFT);

    $base_id_actual = $codigo_sector . $codigo_subsector;

    // Obtener TODOS los proyectos para encontrar el consecutivo global máximo, excluyendo el actual
    $proyectos = get_posts([
        'post_type' => 'proyecto_prioritario',
        'posts_per_page' => -1,
        //'post_status' => ['publish', 'draft', 'pending', 'private', 'future'],
		'post_status' => ['publish'],
        'post__not_in' => [$post_id],
        'meta_query' => [
            [
                'key' => 'id_unico_proyecto',
                'compare' => 'EXISTS'
            ]
        ]
    ]);

    $max_consecutivo = 0;
    foreach ($proyectos as $p) {
        $id = get_post_meta($p->ID, 'id_unico_proyecto', true);
        if (preg_match('/^[0-9]{4}(\d{3})01$/', $id, $match)) {
            $num = intval($match[1]);
            if ($num > $max_consecutivo) $max_consecutivo = $num;
        }
    }

    $consecutivo_nuevo = str_pad($max_consecutivo + 1, 3, '0', STR_PAD_LEFT);
    $id_generado = $base_id_actual . $consecutivo_nuevo . '01';

    // Guardar ID único
    update_post_meta($post_id, 'id_unico_proyecto', $id_generado);
    update_post_meta($post_id, 'id_unico_proyecto_base', $base_id_actual);
}




// Mostrar ID único en el listado del admin
add_filter('manage_proyecto_prioritario_posts_columns', function($columns) {
    $columns['id_unico_proyecto'] = 'ID Único Proyecto';
    return $columns;
});

add_action('manage_proyecto_prioritario_posts_custom_column', function($column, $post_id) {
    if ($column === 'id_unico_proyecto') {
        $id = get_post_meta($post_id, 'id_unico_proyecto', true);
        echo $id ? esc_html($id) : '<em style="color:#888;">Sin ID</em>';
    }
}, 10, 2);



// Pasar códigos (id_sector e id_subsector) a JavaScript desde ACF, para el post actual
add_action('admin_footer-post.php', 'pasar_codigos_acf_a_js');
add_action('admin_footer-post-new.php', 'pasar_codigos_acf_a_js');
function pasar_codigos_acf_a_js() {
    global $post;
    if (!$post || get_post_type($post) !== 'proyecto_prioritario') return;

    // Obtener valores actuales de id_sector e id_subsector (códigos)
    $codigo_sector = get_field('id_sector', $post->ID) ?: '00';
    $codigo_subsector = get_field('id_subsector', $post->ID) ?: '00';

    ?>
    <script>
    window.codigoSector = "<?php echo esc_js($codigo_sector); ?>";
    window.codigoSubsector = "<?php echo esc_js($codigo_subsector); ?>";
    </script>
    <?php
}

// Mostrar ID sugerido en vivo basado en campos ACF id_sector e id_subsector
add_action('acf/input/admin_footer', 'actualizar_id_en_vivo_acf');
function actualizar_id_en_vivo_acf() {
    global $post;
    if (!$post || get_post_type($post) !== 'proyecto_prioritario') return;

    echo '<div id="id-unico-generado" class="acf-field"><p><strong>ID sugerido:</strong> <span>Esperando selección válida...</span></p></div>';
    ?>
    <script>
    (function($){
        function actualizarID() {
            // Leer valores de campos ACF id_sector e id_subsector en el DOM
            var codigoSector = $('[name="acf[id_sector]"]').val() || '00';
            var codigoSubsector = $('[name="acf[id_subsector]"]').val() || '00';

            if (codigoSector === '00' || codigoSubsector === '00') {
                mostrarID('Esperando selección válida...');
                return;
            }

            var base = codigoSector + codigoSubsector;

            $.post(ajaxurl, { action: 'obtener_consecutivo_id'}, function(res) {
                let consecutivo = parseInt(res) + 1;
                let nuevoID = base + ('000' + consecutivo).slice(-4) + '01';
                mostrarID(nuevoID);
            }).fail(function() {
                mostrarID('Error al obtener ID.');
            });
        }

        function mostrarID(idTexto) {
            let contenedor = $('#id-unico-generado');
            if (!contenedor.length) {
                contenedor = $('<div id="id-unico-generado" class="acf-field"><p><strong>ID sugerido:</strong> <span></span></p></div>');
                $('.acf-field[data-name="id_subsector"]').after(contenedor);
            }
            contenedor.find('span').text(idTexto);
        }

        $(document).ready(function(){
            actualizarID();
            // Actualizar ID cuando cambian los campos id_sector o id_subsector
            $('[name="acf[id_sector]"], [name="acf[id_subsector]"]').on('change input', actualizarID);
        });
    })(jQuery);
    </script>
    <?php
}

// AJAX para obtener el mayor consecutivo basado en prefijo (id_sector + id_subsector)
add_action('wp_ajax_obtener_consecutivo_id', 'obtener_consecutivo_id_acf');
function obtener_consecutivo_id_acf() {
    $prefijo = sanitize_text_field($_POST['prefijo']);
    $posts = get_posts([
        'post_type' => 'proyecto_prioritario',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'meta_query' => [
            [
                'key' => 'id_unico_proyecto',
                'value' => "^{$prefijo}[0-9]{3}01$",
                'compare' => 'REGEXP'
            ]
        ]
    ]);

    $numeros = [];
    foreach ($posts as $p) {
        $id = get_post_meta($p->ID, 'id_unico_proyecto', true);
        if (preg_match("/^{$prefijo}(\d{3})01$/", $id, $m)) {
            $numeros[] = intval($m[1]);
        }
    }
    echo !empty($numeros) ? max($numeros) : 0;
    wp_die();
}


add_action('wp_ajax_obtener_consecutivo_global', 'obtener_consecutivo_global');
function obtener_consecutivo_global() {
    $posts = get_posts([
        'post_type' => 'proyecto_prioritario',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'meta_query' => [
            [
                'key' => 'id_unico_proyecto',
                'compare' => 'EXISTS'
            ]
        ]
    ]);

    $numeros = [];
    foreach ($posts as $p) {
        $id = get_post_meta($p->ID, 'id_unico_proyecto', true);
        if (preg_match("/^[0-9]{4}(\d{3})01$/", $id, $m)) {
            $numeros[] = intval($m[1]);
        }
    }

    echo !empty($numeros) ? max($numeros) : 0;
    wp_die();
}
