<?php 
global $wpdb;   

if (isset($_POST['date_start1']) && !empty($_POST['date_start1'])) {
    $date_start1 = date('Y-m-d', strtotime($_POST['date_start1']));
    $date_end1   = date('Y-m-d', strtotime($_POST['date_end1']));
} else {
    $date_end1   = date('Y-m-d', strtotime(' +1 day'));
    $date_start1 = date('Y-m-d', strtotime(' -29 day'));
}

$table_name = $wpdb->prefix . 'bancomext_users';
$table_posts = $wpdb->prefix . 'posts';
$table_meta  = $wpdb->prefix . 'postmeta';

echo esc_html($date_start1) . '-----' . esc_html($date_end1);

/**
 * OPTIMIZACIÓN: SQL JOIN
 * Traemos los datos del usuario y la información del post relacionada en una sola consulta.
 * Esto evita ejecutar WP_Query un chorro de veces.
 */
$sql = $wpdb->prepare("
    SELECT 
        u.time, 
        u.email, 
        u.follow, 
        u.lang, 
        p.ID as project_id, 
        p.post_title as project_title 
    FROM $table_name u
    LEFT JOIN $table_meta m ON (m.meta_key = 'ID_PROYECTO' AND TRIM(m.meta_value) = TRIM(u.follow))
    LEFT JOIN $table_posts p ON (p.ID = m.post_id AND p.post_type = 'proyecto_inversion')
    WHERE u.time BETWEEN %s AND %s
    GROUP BY u.email, u.follow, u.time
", $date_start1, $date_end1);

$check = $wpdb->get_results($sql);
?>
<div class="wrap">
    <h1>Bienvenido a los Registros de Usuarios de Seguimiento</h1>
    <div class="about-text wpem-welcome">
        Los registros aqui mostrados inicialmente corresponden al lapso de 1 mes.
    </div>

    <div class="changelog">
        <form method="post">
            <table border="0" cellspacing="5" cellpadding="5">
                <tbody>
                    <tr>
                        <td>Fecha Inicio:</td>
                        <td><input type="date" name="date_start1" id="date_start1" value="<?php echo $date_start1 ?>" /></td>
                    </tr>
                    <tr>
                        <td>Fecha Fin:</td>
                        <td><input type="date" name="date_end1" id="date_end1" value="<?php echo $date_end1 ?>" /></td>
                    </tr>
                    <tr>
                        <td colspan="2"><input type="submit" value="Consultar" /></td>
                    </tr>
                </tbody>
            </table>
        </form>

        <table id="users-list" class="table table-striped hover table-bordered compact order-column" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>Correo Usuario</th>
                    <th>Proyectos Notificados</th>
                    <th>Idioma</th>
                    <th>Fecha</th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th>Correo Usuario</th>
                    <th>Proyectos Notificados</th>
                    <th>Idioma</th>
                    <th>Fecha</th>
                </tr>
            </tfoot>
            <tbody> 
            <?php 
            if ($check) {
                foreach ($check as $item) {
                    $mail_follow = $item->email;
                    $idioma      = $item->lang;
                    $fecha       = $item->time;
                    $id_follow   = $item->follow;
                    
                    // Datos obtenidos mediante el JOIN
                    $TitlePost   = ($item->project_title) ? $item->project_title : "No encontrado";
                    $This_id     = ($item->project_id) ? $item->project_id : "";
                    $permalink   = ($This_id) ? get_permalink($This_id) : "#";
            ?> 
                <tr>
                    <td class="tooltipme"><?php echo esc_html($mail_follow); ?></td>
                    <td>
                        <?php echo esc_html($id_follow); ?> - 
                        <a href="<?php echo esc_url($permalink); ?>" target="_blank">
                            <?php echo esc_html($TitlePost); ?>
                        </a>
                    </td>
                    <td><?php echo esc_html($idioma); ?></td>
                    <td><?php echo esc_html($fecha); ?></td>
                </tr>
            <?php
                }
            }
            ?>
            </tbody>
        </table>
    </div>
</div>