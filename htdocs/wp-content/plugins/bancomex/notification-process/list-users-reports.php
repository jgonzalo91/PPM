<?php
global $wpdb;
$table_name = $wpdb->prefix . 'bancomext_users_reports';

/* ====== Valores por defecto de fechas (para inputs y consulta inicial) ====== */
$date_end   = date('Y-m-d', strtotime('+1 day'));
$date_start = date('Y-m-d', strtotime('-7 day'));

/* ====== Normalizar POST ====== */
$posted_date_start = isset($_POST['date_start1']) ? trim((string)$_POST['date_start1']) : '';
$posted_date_end   = isset($_POST['date_end1'])   ? trim((string)$_POST['date_end1'])   : '';
$posted_email      = isset($_POST['email'])       ? trim((string)$_POST['email'])       : '';
$posted_proceso    = isset($_POST['proceso'])     ? trim((string)$_POST['proceso'])     : '';

/* ====== Resolver consulta según filtros ====== */
if ($posted_date_start !== '' && $posted_date_end !== '') {
    // Filtro por fechas
    $date_start = date('Y-m-d', strtotime($posted_date_start));
    $date_end   = date('Y-m-d', strtotime($posted_date_end));
    $check = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE report BETWEEN %s AND %s ORDER BY ID DESC LIMIT 5000",
            $date_start, $date_end
        )
    );
} elseif ($posted_email !== '') {
    // Filtro por email (exacto)
    $check = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE email = %s ORDER BY ID DESC LIMIT 5000",
            $posted_email
        )
    );
} elseif ($posted_proceso !== '') {
    // Filtro por proceso
    $check = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE proceso = %s ORDER BY ID DESC LIMIT 5000",
            $posted_proceso
        )
    );
} else {
    // Últimos 7 días (por defecto)
    $check = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE report BETWEEN %s AND %s ORDER BY ID DESC LIMIT 5000",
            $date_start, $date_end
        )
    );
}

/* Para selects de filtros */
$emails   = $wpdb->get_col("SELECT DISTINCT email   FROM {$table_name} ORDER BY email ASC");
$procesos = $wpdb->get_col("SELECT DISTINCT proceso FROM {$table_name} ORDER BY proceso ASC");
?>
<div class="wrap">
    <h1>Bienvenido a las Notificaciones de Usuarios</h1>

    <div class="about-text wpem-welcome">
        El resultado Inicial mostrado corresponde a los últimos 7 días.
    </div>

    <div class="changelog point-releases"></div>

    <div class="changelog">
        <!-- Filtro por fechas -->
        <form method="post" style="display: inline-block;">
            <table border="0" cellspacing="5" cellpadding="5">
                <tbody>
                    <tr>
                        <td>Fecha Inicio:</td>
                        <td><input class="date" type="date" name="date_start1" id="date_start1" value="<?php echo esc_attr($date_start); ?>" /></td>
                    </tr>
                    <tr>
                        <td>Fecha Fin:</td>
                        <td><input class="date" type="date" name="date_end1" id="date_end1" value="<?php echo esc_attr($date_end); ?>" /></td>
                    </tr>
                    <tr>
                        <td colspan="2" align="center"><input type="submit" value="Consultar Fechas" /></td>
                    </tr>
                </tbody>
            </table>
        </form>

        <!-- Filtro por correo -->
        <form method="post" style="display: inline-block;">
            <table border="0" cellspacing="5" cellpadding="5">
                <tbody>
                    <tr>
                        <td>Correo Electrónico</td>
                        <td>
                            <select name="email">
                                <?php if (!empty($emails)) { foreach ($emails as $em) { ?>
                                    <option value="<?php echo esc_attr($em); ?>"><?php echo esc_html($em); ?></option>
                                <?php } } ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" align="center"><input type="submit" value="Consultar Email" /></td>
                    </tr>
                    <tr><td>&nbsp;</td></tr>
                </tbody>
            </table>
        </form>

        <!-- Filtro por proceso -->
        <form method="post" style="display: inline-block;">
            <table border="0" cellspacing="5" cellpadding="5">
                <tbody>
                    <tr>
                        <td>Proceso</td>
                        <td>
                            <select name="proceso">
                                <?php if (!empty($procesos)) { foreach ($procesos as $pr) { ?>
                                    <option value="<?php echo esc_attr($pr); ?>"><?php echo esc_html($pr); ?></option>
                                <?php } } ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" align="center"><input type="submit" value="Consultar Proceso" /></td>
                    </tr>
                    <tr><td>&nbsp;</td></tr>
                </tbody>
            </table>
        </form>

        <table id="users-list" class="table table-striped hover table-bordered compact order-column" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th title="" data-container="body">Idioma</th>
                    <th title="" data-container="body">Correo Usuario</th>
                    <th title="" data-container="body">Proyectos Notificados</th>
                    <th title="" data-container="body">Notificación/Update</th>
                    <th title="" data-container="body">Proceso</th>
                    <th title="" data-container="body">Status</th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th>Idioma</th>
                    <th>Correo Usuario</th>
                    <th>Proyectos Notificados</th>
                    <th>Notificación/Update</th>
                    <th>Proceso</th>
                    <th>Status</th>
                </tr>
            </tfoot>
            <tbody>
            <?php
            if (!empty($check)) {
                foreach ($check as $item) {
                    // --- Datos base del renglón ---
                    $idioma        = isset($item->idioma)  ? (string)$item->idioma  : '';
                    $id_follow     = isset($item->follow)  ? intval($item->follow)  : 0;
                    $mail_follow   = isset($item->email)   ? (string)$item->email   : '';
                    $report_update = isset($item->report)  ? (string)$item->report  : '';
                    $proceso       = isset($item->proceso) ? (string)$item->proceso : '';
                    $status        = isset($item->status)  ? (string)$item->status  : '';

                    // --- Resolver título/ID/permalink del proyecto seguido ---
                    $titlePost = '';
                    $This_id   = 0;
                    $perma     = '';

                    if ($id_follow > 0) {
                        $p = get_post($id_follow); // WP_Post o null
                        if ($p instanceof WP_Post) {
                            $titlePost = $p->post_title;
                            $This_id   = $p->ID;
                            $perma     = get_permalink($This_id);
                        } else {
                            if (function_exists('pmx_log')) { pmx_log('[REPORTS] ID de follow no encontrado: '.$id_follow); }
                        }
                    }
                    ?>
                    <tr>
                        <td><?php echo $idioma !== '' ? esc_html($idioma) : 'es_MX'; ?></td>
                        <td data-container="body" class="tooltipme"><?php echo esc_html($mail_follow); ?></td>

                        <?php if ($This_id === 0) { ?>
                            <td> - </td>
                        <?php } else { ?>
                            <td>
                                <?php echo esc_html($This_id); ?> -
                                <a href="<?php echo esc_url($perma); ?>" target="_blank">
                                    <?php echo esc_html($titlePost !== '' ? $titlePost : '(sin título)'); ?>
                                </a>
                            </td>
                        <?php } ?>

                        <td><?php echo esc_html($report_update); ?></td>
                        <td><?php echo esc_html($proceso); ?></td>
                        <td><?php echo esc_html($status); ?></td>
                    </tr>
                    <?php
                }
            }
            ?>
            </tbody>
        </table>
    </div>
</div>
