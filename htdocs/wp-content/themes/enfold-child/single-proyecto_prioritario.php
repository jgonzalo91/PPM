<?php /* Template Name: Hola Mundo con Campos */ get_header(); ?> <style>
    .tabla-scroll {
        overflow-x: auto;
        width: 100%;
        padding-left: 100px;
        padding-right: 100px;
    }
    table.tabla-etapa {
        margin-top: 25px;
        width: 100%;
        border-collapse: collapse;
        min-width: 600px;
    }
    table.tabla-etapa tbody tr:nth-child(odd) {
        background-color: #ffffff;
        color: #000000;
    }
    table.tabla-etapa tbody tr:nth-child(odd) a {
        color: #0066cc !important;
        text-decoration: underline;
    }
    table.tabla-etapa tbody tr:nth-child(even) {
        background-color: #008B8B;
        color: #ffffff;
    }
    table.tabla-etapa tbody tr:nth-child(even) a {
        color: #ffffff !important;
        text-decoration: underline;
    }
    table.tabla-etapa thead th {
        background-color: #008B8B !important;
        color: white !important;
    }
    table.tabla-etapa td,
    table.tabla-etapa th {
        padding: 8px;
        border: 1px solid #ddd;
    }
    .centrado {
        text-align: center;
    }
    .preservar-formato {
        white-space: pre-wrap;
        font-family: inherit;
    }
</style> <main id="main-content">
    <h1><?php echo esc_html(get_the_title()); ?></h1>
    <?php
    $post_id = get_the_ID();
    $campos = get_option('campos_visibilidad_proyecto', array());
    if (!empty($campos)) {
        echo '<div class="tabla-scroll">';
        echo '<table class="tabla-etapa">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>Campo</th>';
        echo '<th>Información</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        foreach ($campos as $clave => $etiqueta) {
            // Debug: Ver el valor original almacenado en cada campo personalizado
            $valor_original = get_post_meta($post_id, $clave, true);
            error_log("Campo '$clave' => " . print_r($valor_original, true));
            
            // Si es un repeater, vamos a ver su estructura interna
            if (is_array($valor_original)) {
                error_log("Estructura del array para '$clave':");
                error_log(print_r($valor_original, true));
            }
            
            $mostrar = get_post_meta($post_id, "_mostrar_$clave", true);
            if ($mostrar === '1') {
                $valor = get_post_meta($post_id, $clave, true);
                
                // Debug: Ver el valor original almacenado en cada campo personalizado
                error_log("Campo '$clave' => " . (is_array($valor) ? count($valor) : $valor));
                
                // Obtener información del campo ACF
                $field_object = get_field_object($clave, $post_id);
                
                // Debug: Mostrar estructura completa del campo ACF
                error_log("Campo '$clave' - Objeto ACF: " . print_r($field_object, true));
                
                // Si es un array, vamos a ver su estructura interna
                if (is_array($valor)) {
                    error_log("Estructura del array para '$clave':");
                    error_log(print_r($valor, true));
                }
                
                if ($field_object && $field_object['type'] === 'repeater' && !empty($field_object['value'])) {
                    echo '<tr>';
                    echo '<td style="border:1px solid #ccc; padding:8px;">' . esc_html($etiqueta) . '</td>';
                    echo '<td style="border:1px solid #ccc; padding:8px;">';
                    echo '<ol style="margin: 0; padding-left: 20px;">';
                    
                    foreach ($field_object['value'] as $row) {
                        echo '<li style="margin-bottom: 10px;">';
                        
                        // Manejar diferentes estructuras de repeater según el campo
                        switch ($field_object['name']) {
                            case 'compras_mx_proyecto':
                                if (!empty($row['claves_compras_mx'])) {
                                    echo '<strong>Clave:</strong> ' . esc_html($row['claves_compras_mx']);
                                }
                                if (!empty($row['descripcion'])) {
                                    echo '<br><strong>Descripción:</strong> ' . esc_html($row['descripcion']);
                                }
                                break;
                                
                            case 'dato_por_fuente':
                                // Mostrar fuente como enlace si es una URL
                                if (!empty($row['fuente'])) {
                                    if (filter_var($row['fuente'], FILTER_VALIDATE_URL)) {
                                        echo '<strong>Fuente:</strong> <a href="' . esc_url($row['fuente']) . '" target="_blank" style="color: 
white; text-decoration: underline;">' . esc_html($row['fuente']) . '</a>';
                                    } else {
                                        echo '<strong>Fuente:</strong> ' . esc_html($row['fuente']);
                                    }
                                }
                                
                                // Mostrar comentarios si existen
                                if (!empty($row['comentarios'])) {
                                    echo '<br><strong>Comentarios:</strong> ' . esc_html($row['comentarios']);
                                }
                                
                                // Mostrar datos relacionados si existen
                                if (!empty($row['dato'])) {
                                    echo '<br><strong>Datos:</strong> ';
                                    if (is_array($row['dato'])) {
                                        $datos = array();
                                        foreach ($row['dato'] as $dato) {
                                            if (is_object($dato)) {
                                                $datos[] = esc_html($dato->post_title);
                                            } else {
                                                $datos[] = esc_html($dato);
                                            }
                                        }
                                        echo implode(', ', $datos);
                                    } else {
                                        echo esc_html($row['dato']);
                                    }
                                }
                                break;
                            case 'ejes_objeticos_y_estrategias':
                                // Mostrar Eje PND
                                if (!empty($row['eje_pnd'])) {
                                    $eje_post = get_post($row['eje_pnd']);
                                    if ($eje_post) {
                                        echo '<strong>Eje PND:</strong> ' . esc_html($eje_post->post_title);
                                    }
                                }
                                
                                // Mostrar Objetivo
                                if (!empty($row['objetivo'])) {
                                    $objetivo_post = get_post($row['objetivo']);
                                    if ($objetivo_post) {
                                        echo '<br><strong>Objetivo:</strong> ' . esc_html($objetivo_post->post_title);
                                    }
                                }
                                
                                // Mostrar Estrategia
                                if (!empty($row['estrategia'])) {
                                    $estrategia_post = get_post($row['estrategia']);
                                    if ($estrategia_post) {
                                        echo '<br><strong>Estrategia:</strong> ' . esc_html($estrategia_post->post_title);
                                    }
                                }
                                break;
                                case 'mia_por_proyecto':
                                    //mostar mia por proyecto
                                    if(!empty($row['clave_rpoyecto'])){
                                            echo '<strong>Clave:</strong>' . esc_html($row['clave_rpoyecto']);
                                    }
                                    if (!empty($row['descripcion_mia_proyecto'])) {
                                        echo '<br><strong>Descripción:</strong> ' . esc_html($row['descripcion_mia_proyecto']);
                                    }
                                    break;
                                    case 'plan_pertenece':
                                        if (!empty($row['plan_programa_o_presentacion']) && $row['mostrar_plan'] == 1) {
                                            $plan_post = get_post($row['plan_programa_o_presentacion']);
                                            if ($plan_post) {
                                                echo '<div style="margin-bottom: 10px;">';
                                                echo '<strong>Plan/Programa:</strong> ' . esc_html($plan_post->post_title);
                                                echo '</div>';
                                            }
                                        }
                                        break;
                                    case 'otras_ligas_por_proyecto':
                                        if (!empty($row['identificador_descripcion'])) {
                                            echo '<div style="margin-bottom: 10px;">';
                                            echo '<strong>Identificador:</strong> ' . esc_html($row['identificador_descripcion']);
                                            
                                            if (!empty($row['url_otras'])) {
                                                if (filter_var($row['url_otras'], FILTER_VALIDATE_URL)) {
                                                    echo '<br><strong>URL:</strong> <a href="' . esc_url($row['url_otras']) . '" target="_blank" 
style="color: white; text-decoration: underline;">' . esc_html($row['url_otras']) . '</a>';
                                                } else {
                                                    echo '<br><strong>URL:</strong> ' . esc_html($row['url_otras']);
                                                }
                                            }
                                            echo '</div>';
                                        }
                                        break;
                                    
                                
                            default:
                                // Para otros repeaters, mostrar todos los campos disponibles
                                foreach ($row as $key => $value) {
                                    if (!empty($value)) {
                                        echo '<strong>' . esc_html($key) . ':</strong> ' . esc_html($value) . '<br>';
                                    }
                                }
                                break;
                        }
                        
                        echo '</li>';
                    }
                    
                    echo '</ol>';
                    echo '</td>';
                    echo '</tr>';
                    continue;
                }
                
                // Procesar el resto de campos normalmente...
                // Intentar decodificar JSON
                if (is_string($valor)) {
                    $posible_array = json_decode($valor, true);
                    if (is_array($posible_array)) {
                        $valor = $posible_array;
                    } elseif (strpos($valor, ',') !== false) {
                        $valor = array_map('trim', explode(',', $valor));
                    }
                }
                // Mostrar subcampos si es un repeater (array de arrays)
                if (is_array($valor) && isset($valor[0]) && is_array($valor[0])) {
                    // Mostrar como lista para repeater
                    echo '<tr>';
                    echo '<td style="border:1px solid #ccc; padding:8px;">' . esc_html($etiqueta) . '</td>';
                    echo '<td style="border:1px solid #ccc; padding:8px;">';
                    echo '<ol style="margin: 0; padding-left: 20px;">';
                    foreach ($valor as $fila) {
                        echo '<li style="margin-bottom: 10px;">';
                        foreach ($fila as $subcampo => $subvalor) {
                            if (!empty($subvalor)) {
                                echo '<strong>' . esc_html($subcampo) . ':</strong> ';
                                if (is_array($subvalor)) {
                                    $nombres = array();
                                    foreach ($subvalor as $v) {
                                        $nombres[] = esc_html(obtener_nombre_por_id($v));
                                    }
                                    echo implode(', ', $nombres);
                                } else {
                                    // Si el valor parece contener HTML (posible wysiwyg)
                                    if (is_string($subvalor) && (strpos($subvalor, '<') !== false || strpos($subvalor, '&lt;') !== false)) {
                                        echo wp_kses_post($subvalor);
                                    } else {
                                        echo esc_html(obtener_nombre_por_id($subvalor));
                                    }
                                }
                                echo '<br>';
                            }
                        }
                        echo '</li>';
                    }
                    echo '</ol>';
                    echo '</td>';
                    echo '</tr>';
                } else {
                    // Procesar arrays simples
                    if (is_array($valor)) {
                        $resultado = array();
                        foreach ($valor as $v) {
                            $resultado[] = obtener_nombre_por_id($v);
                        }
                        $valor = implode(', ', $resultado);
                    } else {
                        // Si es un campo wysiwyg o contiene HTML
                        if (is_string($valor) && (strpos($valor, '<') !== false || strpos($valor, '&lt;') !== false)) {
                            $valor = wp_kses_post($valor);
                        } else {
                            $valor = obtener_nombre_por_id($valor);
                        }
                    }
                    // Mostrar valor simple
                    if (!empty($valor)) {
                        echo '<tr>';
                        echo '<td style="border:1px solid #ccc; padding:8px;">' . esc_html($etiqueta) . '</td>';
                        echo '<td style="border:1px solid #ccc; padding:8px;">' . $valor . '</td>';
                        echo '</tr>';
                    }
                }
            }
        }
        echo '</tbody>';
        echo '</table>';
        echo '</div>';
    }
    // Función auxiliar
    function obtener_nombre_por_id($id) {
        if (!is_numeric($id)) return $id;
        $term = get_term($id);
        if (!is_wp_error($term) && $term && isset($term->name)) {
            return $term->name;
        }
        $post_obj = get_post($id);
        if (!is_wp_error($post_obj) && $post_obj && isset($post_obj->post_title)) {
            return $post_obj->post_title;
        }
        return $id;
    }
    ?> </main> <?php get_footer(); ?>
