<?php

function oqp_step_slug_unique($name) {
        global $oqp_form_existing_step_slugs;

        $slug=sanitize_title($name);

        $newslugkey==0;
        if (in_array($slug,(array)$oqp_form_existing_step_slugs)) { //slug exists, create a unique one.
                foreach($oqp_form_existing_step_slugs as $existing_slug) {
                        if ($slug==$existing_slug) {
                                $newslugkey++;
                        }
                }
        }			
        if ($newslugkey) {
                $slug.='-'.$newslugkey;
        }
        $oqp_form_existing_step_slugs[]=$slug;
        return $slug;
}

function oqp_form_balloon_info($messages,$class='info') {
	if ($class=='error') {
		$icon = 'error.png';
	}else {
		$icon = 'lightbulb_off.png';
	}
	?>
	<span class="help">
		<img class="balloon-tip" src="<?php echo oqp_get_theme_file_url($icon,'_inc/images');?>"/>
		<span class="<?php echo $class;?>">
		<?php 
		foreach ((array)$messages as $message) {
			echo "<p class='desc'>".$message."</p>";
		}
		?>
		</span>
	</span>
	<?php
}
function oqp_form_balloon_warning($messages) {
	$type = 'error';
	oqp_form_balloon_info($messages,$type);
}


function oqp_admin_paypal_form() {
    global $oqp_form;
    


    if($_POST['oqp_donated']){
        update_option('oqp-donated',true);
    }
    
    $has_donated = get_option("oqp-donated");

    ?>
    <div id="donations"<?php if($has_donated)echo'class="donated"';?>>
        <p>
            <table>
                    <tr>
                            <td>
                                <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
                                <input type="hidden" name="cmd" value="_s-xclick">
                                <input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----
                                ">
                                <input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-but04.gif" border="0" name="submit" alt="PayPal">
                                <img alt="" border="0" src="https://www.paypal.com/fr_FR/i/scr/pixel.gif" width="1" height="1">
                                </form>
                            </td>
                                    <td>
                                    <?php _e('I spend a lot of time working on this plugin.  If you use it, please make a donation !', 'yclads'); ?>
                                    </td>
                    </tr>
            </table>

        </p>
        <p style="text-align:right">
            <form name="donations" method="post" action="<?php echo admin_url("admin.php?page=oqp-settings");?>">
                <input name="oqp_donated" type="checkbox" onclick="document.donations.submit();"/>
                <label for="oqp_donated"><?php _e('I have donated to help contribute for the development of this plugin.', 'yclads'); ?></label>
            </form>
        </p>
    </div>
    <?php
}

function oqp_admin_header(){
    ?>
    <a href="<?php echo OQP_WORDPRESS_URL;?>changelog/" target="_blank"><?php _e('Changelog', 'oqp'); ?></a> |
    <a href="<?php echo OQP_WORDPRESS_URL;?>faq/" target="_blank"><?php _e('FAQ', 'oqp'); ?></a> |
    <a href="<?php echo OQP_WORDPRESS_URL;?>" target="_blank"><?php _e('Rate This', 'oqp'); ?></a> |
    <a href="<?php echo OQP_SUPPORT_URL;?>" target="_blank"><?php _e('Support', 'oqp'); ?></a> |
    <a href="<?php echo OQP_DONATION_URL;?>" target="_blank"><?php _e('Donate', 'oqp'); ?></a>
    </p>
    

    <?php
    if (function_exists('get_transient')) {
        require_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );

        // First, try to access the data, check the cache.
        if (false === ($api = get_transient('oqp_info'))) {
            // The cache data doesn't exist or it's expired.

            $api = plugins_api('plugin_information', array('slug' => OQP_SLUG ));

            if ( !is_wp_error($api) ) {
                // cache isn't up to date, write this fresh information to it now to avoid the query for xx time.
                $myexpire = 60 * 15; // Cache data for 15 minutes
                set_transient('oqp_info', $api, $myexpire);
            }
        }
        if ( !is_wp_error($api) ) {
                $plugins_allowedtags = array('a' => array('href' => array(), 'title' => array(), 'target' => array()),
                                                                    'abbr' => array('title' => array()), 'acronym' => array('title' => array()),
                                                                    'code' => array(), 'pre' => array(), 'em' => array(), 'strong' => array(),
                                                                    'div' => array(), 'p' => array(), 'ul' => array(), 'ol' => array(), 'li' => array(),
                                                                    'h1' => array(), 'h2' => array(), 'h3' => array(), 'h4' => array(), 'h5' => array(), 'h6' => array(),
                                                                    'img' => array('src' => array(), 'class' => array(), 'alt' => array()));
                //Sanitize HTML
                foreach ( (array)$api->sections as $section_name => $content )
                    $api->sections[$section_name] = wp_kses($content, $plugins_allowedtags);
                foreach ( array('version', 'author', 'requires', 'tested', 'homepage', 'downloaded', 'slug') as $key )
                    $api->$key = wp_kses($api->$key, $plugins_allowedtags);

                if ( ! empty($api->downloaded) ) {
                    echo sprintf(__('Downloaded %s times', 'oqp'),number_format_i18n($api->downloaded));
                    echo '.';
                }
    ?>
                <?php if ( ! empty($api->rating) ) : ?>
                <div class="star-holder" title="<?php echo esc_attr(sprintf(__('(Average rating based on %s ratings)', 'oqp'),number_format_i18n($api->num_ratings))); ?>">
                <div class="star star-rating" style="width: <?php echo esc_attr($api->rating) ?>px"></div>

                <div class="star star5"><img src="<?php echo OQP_PLUGIN_URL;?>admin/_inc/images/star.png" alt="<?php printf(__('%d stars', 'oqp'),'5'); ?>" /></div>
                <div class="star star4"><img src="<?php echo OQP_PLUGIN_URL;?>admin/_inc/images/star.png" alt="<?php printf(__('%d stars', 'oqp'),'4'); ?>" /></div>
                <div class="star star3"><img src="<?php echo OQP_PLUGIN_URL;?>admin/_inc/images/star.png" alt="<?php printf(__('%d stars', 'oqp'),'3'); ?>" /></div>
                <div class="star star2"><img src="<?php echo OQP_PLUGIN_URL;?>admin/_inc/images/star.png" alt="<?php printf(__('%d stars', 'oqp'),'2'); ?>" /></div>
                <div class="star star1"><img src="<?php echo OQP_PLUGIN_URL;?>admin/_inc/images/star.png" alt="<?php printf(__('%d stars', 'oqp'),'1'); ?>" /></div>
                </div>
                <small><?php echo sprintf(__('(Average rating based on %s ratings)', 'oqp'),number_format_i18n($api->num_ratings)); ?> <a target="_blank" href="http://wordpress.org/extend/plugins/<?php echo $api->slug ?>/"> <?php _e('Rate This', 'oqp') ?></a></small>
                <?php endif;
    }// end if (function_exists('get_transient'
        } // if ( !is_wp_error($api)

   oqp_admin_paypal_form();
        
}

function oqp_admin_menu() {
	add_menu_page( __( 'OQP', 'oqp' ), __( 'One Quick Post', 'oqp' ),
		'manage_options','oqp-settings', 'oqp_settings_page' );//OQP_SLUG
        
        //SETTINGS
	add_submenu_page('oqp-settings',__( 'One Quick Post Settings', 'oqp' ),__( 'Settings', 'oqp' ),
		'manage_options','oqp-settings', 'oqp_settings_page' );


        ///FORM PAGES
        
        $forms_page_ids=oqp_get_forms_page_ids();
        foreach ((array)$forms_page_ids as $page_id) {
            $page = get_post($page_id);
            if(!$page) continue;
            add_submenu_page('oqp-settings',$page->post_title,$page->post_title,
            'manage_options','oqp-form-'.$page_id, 'oqp_admin_form_settings' );
        }

}

function oqp_admin_is_settings(){
    global $plugin_page;
    if($plugin_page=='oqp-settings')return true;
}

function oqp_admin_get_edit_form(){
    global $plugin_page;
    $split = explode('oqp-form-',$plugin_page);
    $form_id = $split[1];
    $oqp_form = new Oqp_Form($form_id);
    
    return $oqp_form->page_id;
}

function oqp_admin_is_form(){
    $form_id = oqp_admin_get_edit_form();
    if(oqp_admin_get_edit_form()) return true;
}



function oqp_admin_enqueue_scripts() {
	global $plugin_page;

	if (!oqp_admin_is_settings() && !oqp_admin_is_form() ) return;

	//wp_enqueue_script( 'thickbox' );
        wp_enqueue_script('common');
        wp_enqueue_script('wp-lists');
        wp_enqueue_script('postbox');
        wp_enqueue_script( 'jquery-ui-tabs' );
        wp_enqueue_script( 'oqp-admin', OQP_PLUGIN_URL.'admin/_inc/js/oqp-admin.js',array('jquery'), OQP_VERSION );
        
        
        ///
        wp_enqueue_style('oqp-admin', OQP_PLUGIN_URL.'admin/_inc/css/style.css');
        
        //tabs CSS
        wp_enqueue_style('oqp-jquery-css','http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css');
        
        

}

function oqp_admin_init(){

	if((!oqp_admin_is_form())&&(!oqp_admin_is_settings()))return false;

	remove_all_actions('oqp_populated_steps');

	if(oqp_admin_is_form()){
		require_once( OQP_PLUGIN_DIR . 'admin/includes/admin-form.php');
	}elseif(oqp_admin_is_settings()){
		require_once( OQP_PLUGIN_DIR . 'admin/includes/admin-settings.php');
	}
}


function oqp_admin_plugin_settings_action( $links, $file ) {
    //Static so we don't call plugin_basename on every plugin row.
	static $this_plugin;


	if ( ! $this_plugin ) $this_plugin = OQP_DIRNAME . 'loader.php';

	if ( $file == $this_plugin ){
        $settings_link = '<a href="options-general.php?page='.OQP_SLUG.'">' . __( 'Settings', 'oqp' ) . '</a>';
	    array_unshift( $links, $settings_link ); // before other links
	}
	return $links;
}

function oqp_plugin_settings_action( $links, $file ) {
    //Static so we don't call plugin_basename on every plugin row.
	static $this_plugin;


	if ( ! $this_plugin ) $this_plugin = OQP_DIRNAME . 'loader.php';

	if ( $file == $this_plugin ){
        $settings_link = '<a href="options-general.php?page='.OQP_SLUG.'">' . __( 'Settings', 'oqp' ) . '</a>';
	    array_unshift( $links, $settings_link ); // before other links
	}
	return $links;
}

add_filter( 'plugin_action_links', 'oqp_admin_plugin_settings_action',10,2);
add_action( 'admin_init', 'oqp_admin_init' );
add_action( 'admin_menu', 'oqp_admin_menu', 9 ); //add admin menu
add_action( 'admin_enqueue_scripts', 'oqp_admin_enqueue_scripts' );
add_filter( 'plugin_action_links', 'oqp_admin_plugin_settings_action',10,2);//add "settings" in plugin list


?>
