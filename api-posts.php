<?php
/**
 * Plugin Name:       Api Posts
 * Description:       Posts pulled in from an api
 * Version:           0.1.0
 * Requires at least: 6.7
 * Requires PHP:      7.4
 * Author:            Bryan Nance
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */

function register_options_page(){
    add_menu_page(
        'Posts API',            //page title
        'Posts API',            //menu title
        'manage_options',       //who can see this
        'posts_api_options',    //options page slug
        'posts_api_page_html'   //function to render the page
    );
}
add_action('admin_menu', 'register_options_page');

/**
 * post api options page html
 */
function posts_api_page_html(){
    if(!current_user_can('manage_options')){
        return;
    }

    if(isset($_GET['settings-updated'])){
        add_settings_error(
            'my_options_messages',
            'my_options_message',
            esc_html__('API URL saved', 'text_domain'),
            'updated'
        );
    }

    settings_errors('my_options_messages');

    ?>
        <div>
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form action="options.php" method="post">
                <?php
                    settings_fields('my_options_group');
                    do_settings_sections('my_options');
                    submit_button('Save Settings');
                ?>
            </form>
        </div>
    <?php
}

/**
 * Register settings
 */
function register_settings(){
    register_setting('my_options_group', 'my_options',[
        'sanitize_callback' => 'my_options_sanitize_fields',
        'default' => []
    ]);

    add_settings_section(
        'my_options_sections',
        false,
        false,
        'my_options'
    );

    add_settings_field(
        'my_option_1',
        esc_html__('Posts API URL', 'text_domain'),
        'render_api_url_field',
        'my_options',
        'my_options_sections',
        [
            'label_for' => 'my_option_1',
        ]
    );
}
add_action('admin_init', 'register_settings');

/**
 * render the my_option_1 field
 */
function render_api_url_field($args){
    $value = get_option('my_options')[$args['label_for']] ?? '';
    ?>
    <input
        type="text"
        id="<?php echo esc_attr($args['label_for']); ?>"
        name="my_options[<?php echo esc_attr($args['label_for']); ?>]"
        value="<?php echo esc_attr($value) ?>">
    <p class="description"><?php esc_html_e('URL for the posts API', 'text_domain'); ?></p>
    <?php
}

/**
 * sanitize options field
 * only save if field is valid url
 */
function my_options_sanitize_fields($value){
    $value['my_option_1'] = filter_var($value['my_option_1'], FILTER_VALIDATE_URL) ? $value['my_option_1'] : '';
    return array_map('sanitize_text_field', $value);
}


/**
 * load create post file
 */
require_once plugin_dir_path(__FILE__).'create-post.php';