<?php
/*
Plugin Name: Twittami!
Author: Nicola Greco
Author URI: http://nicolagreco.com/
Plugin URI: http://twittami.com/badge
Description: Aggiunge gli strumenti Twittami.com
Version: 0.3
*/

/*
Copyright (c) 2009-2011, Nicola Greco (mail: notsecurity@gmail.com | website: http://nicolagreco.com).

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

The complete license is in this folder, in the file called license.txt

*/

/* Actions */
function twittami_button_get () {

	global $twittami, $post;

	if ( isset( $_GET['n'] ) && $_GET['n'] ) {

		$post_id = (int)$_GET['n'];
		unset( $_GET['n'] );

		$postObj = get_post( $post_id );

		if ( !$postObj )
			wp_die( "Ops! ci deve essere stato un errore con il post!" );

		$data['title'] = $postObj->post_title;
		$data['at'] = $twittami->settings->at;
		$data['link2post'] = get_permalink( $post_id );
		$data['content'] = $postObj->post_content;
		$data['post_id'] = $post_id;
		$data['blog_url'] = get_bloginfo( 'url' );

		$url = twittami_link_generate( $data, true );

		?><iframe src="<?php echo $url ?>" width="500px" height="146px" border="no"></iframe><?php

		die();
	}
	if ( isset( $_GET['vote_id'] ) && $_GET['vote_id'] ) {
		$post_id = (int)$_GET['vote_id'];
		$button = twittami_button_code( false, array( 'post_id' => $post_id ) );
		echo $button;
		die();
	}
}

add_action( 'init', 'twittami_buttons_init' );
add_action( 'init', 'twittami_button_get' );

function twittami_buttons_init () {

	global $twittami;

	$twittami->site = 'http://twittami.com';

	$default = array(
		'button_position' => '2',
		'button_type' => '1',
		'at' => 'retwittami',
		'request' => 'ajax'
	);

	$get_twittami = get_option( 'twittami_settings' );
	if ( is_array( $get_twittami ) ) 
		foreach ( $get_twittami as $key => $value )
			$twittami->settings->{$key} = $value;
	else
		add_option( 'twittami_settings', $default );

	if ( '1' == $twittami->settings->button_type )
		$twittami->options->button_type = 'normal';
	elseif ( '2' == $twittami->settings->button_type )
		$twittami->options->button_type = 'compact';

	if ( '1' == $twittami->settings->button_position )
		$twittami->options->button_position = 'destra';
	elseif ( '2' == $twittami->settings->button_position )
		$twittami->options->button_position = 'sinistra';
	elseif ( '3' == $twittami->settings->button_position )
		$twittami->options->button_position = 'manuale';
	elseif ( '4' == $twittami->settings->button_position )
		$twittami->options->button_position = 'basso';

	$twittami->options->at = $twittami->settings->at;

	if ( 'ajax' == $twittami->settings->request )
		$twittami->options->request = 'destra';
	elseif ( 'popup' == $twittami->settings->request )
		$twittami->options->request = 'sinistra';

	wp_enqueue_script('jquery');

	if ( !is_feed() ) {
		if ( 'manuale' != $twittami->options->button_position and 'basso' != $twittami->options->button_position )
			add_filter('the_content', 'twittami_button' );
		elseif ( 'basso' == $twittami->options->button_position )
			add_filter('the_content', 'twittami_button_bottom' );
	}

	add_action( 'admin_menu', 'twittami_settings_init' );
	add_action( 'wp_footer', 'twittami_footer_js', 12 );
	add_action( 'wp_head', 'twittami_header_css', 11 );

}

function twittami_header_css () {

	global $twittami; ?>

	<link href="<?php echo WP_PLUGIN_URL ?>/twittami-badge/twittami.css" media="screen" rel="stylesheet" type="text/css"/>

<?php }

function twittami_footer_js () {

	global $twittami;

	?>

<!-- Twittami.com | Script JS - Start -->
<script src="<?php echo WP_PLUGIN_URL ?>/twittami-badge/facebox/facebox.js" type="text/javascript"></script>
<script type="text/javascript">
	<!--
	jQuery(document).ready(function($) {
		$('a[rel*=twittami]').twittamibox();
		<?php do_action( 'twittami_jquery' ) ?>
	});
	-->
</script>
<script type="text/javascript">
	<!--
	jQuery(document).ready(function($) {
		$(".twittami_badge").each(function() {
			var $this = $(this);
			var uid = $this.attr('id').substring(4);

			$this.load("/?vote_id=" + uid);

			$this.ajaxStop(function(r,s) {  
				$(".twittami_badge .loader").fadeOut("fast");
			});
		});
	});
	-->
</script>
<!-- Twittami.com | Script JS - End -->
	<?php

}

function twittami_settings_init (){

	if ( !is_site_admin() )
		return false;

	add_menu_page(
		'Twittami',
		'Twittami',
		2, 
		'twittami', 
		'twittami_settings',
		WP_PLUGIN_URL . '/twittami-badge/images/twittami_bullet.png'
	);

	add_submenu_page(
		'twittami',
		'Statistiche',
		'Statistiche',
		1,
		__FILE__,
		'twittami_settings_stats'
	);

}

function twittami_settings() {

	global $twittami;

		// Salva i settaggi
		if ( isset( $_POST["twittami-save"] ) )
			if ( count( $_POST["twittami_settings"] == 5  ) )
				if ( update_option( 'twittami_settings', $_POST["twittami_settings"] ) )
					$error = "Ops! I dati non sono stati salvati correttamente";
		
		// Prendi i settaggi
		$get_twittami = get_option( 'twittami_settings' );
		if ( is_array( $get_twittami ) ) 
			foreach ( $get_twittami as $key => $value )
				$twittami->settings->{$key} = $value;

		if ( '1' == $twittami->settings->button_type )
			$btn1_1 = 'checked="true"';
		else
			$btn1_2 = 'checked="true"';

		if ( '1' == $twittami->settings->button_position )
			$btn2_1 = 'checked="true"';
		elseif ( '2' == $twittami->settings->button_position )
			$btn2_2 = 'checked="true"';
		elseif ( '3' == $twittami->settings->button_position )
			$btn2_3 = 'checked="true"';
		elseif ( '4' == $twittami->settings->button_position )
			$btn2_4 = 'checked="true"';

		if ( 'ajax' == $twittami->settings->request )
			$btn3_1 = 'checked="true"';
		else
			$btn3_2 = 'checked="true"';

?>

	<h3>Configurazione Twittami</h3>
	<form action='' method='post' id='twittami'>
	<table cellspacing="20" width="70%">
		<tr>
			<td valign="top">Scegli un badge</td>
			<td>
				<input type='radio' name='twittami_settings[button_type]' value='1' <?php echo $btn1_1 ?>/> <img src="http://twittami.com/e/images/badge_normal_demo.png" /><br/><br/>
				<input type='radio' name='twittami_settings[button_type]' value='2' <?php echo $btn1_2 ?>/> <img src="http://twittami.com/e/images/badge_compact_demo.png" />
		  </td>
		</tr>
		<tr>
			<td valign="top">Posizione del badge</td>
			<td>
				<input type='radio' name='twittami_settings[button_position]' value='1' <?php echo $btn2_1 ?>/> In alto a destra del post<br/><br/>
				<input type='radio' name='twittami_settings[button_position]' value='2' <?php echo $btn2_2 ?>/> In alto a sinistra del post<br/><br/>
				<input type='radio' name='twittami_settings[button_position]' value='4' <?php echo $btn2_4 ?>/> In fondo al post (consigliato)<br/><br/>
				<input type='radio' name='twittami_settings[button_position]' value='3' <?php echo $btn2_3 ?>/> Posizione manuale<br/><br/>
				<small>L'ultima voce &egrave; per i pi&ugrave; esperti, basta posizionare <code><?php echo htmlentities( '<?php echo twittami_button() ?>' ) ?></code> dove vuoi aggiungere il badge</small>
		  </td>
		</tr>
		<tr>
			<td width="20%" valign="top">Composizione del Twit</td>
			<td>
				<p style="border-top-width:1px; font-family:Georgia,serif; font-size:1em; font-style:italic;">
					RT @<input type='text' name='twittami_settings[at]' value='<?php echo $twittami->settings->at ?>' size="15" style="display:inline; margin-bottom:10px; margin-left:5px;margin-right:5px; margin-top:0" />: Il mio post su Twittami su <?php echo get_bloginfo('url') ?>
				</p>
				<small>Default: <code>retwittami</code></small>
		  </td>
		</tr>
		<tr>
			<td valign="top">Tipo di richiesta</td>
			<td>
				<input type='radio' name='twittami_settings[request]' value='ajax' <?php echo $btn3_1 ?>/> Ajax
				<small>| Migliore integrazione con la grafica del blog con eleganti effetti ajax</small><br/><br/>
				<input type='radio' name='twittami_settings[request]' value='popup' <?php echo $btn3_2 ?>/> Pop-up
				<small>| Maggiore livello di sicurezza e supporto ai browser un po vecchiotti</small>
		  </td>
		</tr>
		<tr>
			<td valign="top">Statistiche</td>
			<td>
				<p>Verr&agrave; aggiunto con la prossima versione</p>
			</td>
		</tr>
		<tr>
			<td valign="top">Twittami Blog Widget</td>
			<td>
				<p>Verr&agrave; aggiunto con la prossima versione</p>
			</td>
		</tr>
	  </table>
	  <p class="submit">
		<input type='submit' name='twittami-save' value='Save Settings' />
	</p>
	</form>

<?php

}

function twittami_settings_stats () { ?>

	<h3>Statistiche</h3>
	<p>Questa feature sarà aggiunta nella prossima versione</p>

<?php }

function twittami_button ( $content = false, $options = array() ) {

	global $twittami, $post;

	$button = '<div id="box-' . $post->ID . '" class="twittami_badge twittami_badge_' . $twittami->options->button_type . ' ' . $twittami->options->button_position . '">';
	$button .= '<img src="' . WP_PLUGIN_URL . '/twittami-badge/images/ajax-loader.gif' . '" class="loader"/>';
	$button .= '</div>';

	$content = $button . $content ;
	return $content;
}

function twittami_button_code ( $content = false, $data = array(), $options = array() ) {

	global $twittami;

	$data = array_merge( $data, $options );

	$link = twittami_link_generate( $data );

	if ( !function_exists( 'twittami_hook_init' ) )
		$vote = twittami_query( 'twittami.getCount', array( 'id', array( (int)$data['post_id'], get_bloginfo('url') ) ) );
	else
		$vote = get_comments_number( $data['post_id'] );

	if ( $vote === false )
		$vote = '?';

	$button .= '<div class="twittami_count">';
	$button .= '<a href="' . $link . '" title="' . __( 'Pubblica la notizia su twitter e fai girare la voce', 'twittami' ) . '" rel="twittamibox">' . $vote . '</a>';
	$button .=  '</div><!-- end .twittami_count -->';
	$button .= '<div class="twittami_button">';
	$button .= '<a href="' . $link . '" title="' . __( 'Pubblica la notizia su twitter e fai girare la voce', 'twittami' ) . '" rel="twittamibox">twittami</a>';
	$button .= '</div><!-- end .twittami_button -->';

	$content = $button . $content ;
	return $content;
}

function twittami_button_bottom ( $content ) {

	global $post;

	$button = twittami_button();

	$html .= '<div id="twittami_box">';
	$html .= $button;
	$html .= '</div><!-- end #twittami_box -->';

	return $content . $html;

}

function twittami_button_iframe ( $content ) {

	global $post;

	if ( !is_single() )
		return $content;

	$data['title'] = $post->post_title;
	$data['categories'] = $post->post_category;
	$data['link2post'] = get_permalink();
	$data['content'] = $post->post_content;
	$data['blog_url'] = get_bloginfo('url');
	$data['post_id'] = $post->ID;

	$link = twittami_link_generate( $data, true );

	$html = '<iframe src="' . $link . '" width="100%" height="70px" border="no"></iframe>';

	return $content . $html;

}

function twittami_link_generate ( $data, $direct = false ) {

	global $twittami;

	extract( $data );

	if ( $title )
		$title = urlencode( $title );
	if ( $link2post )
		$link2post = urlencode( $link2post );
	if ( $categories )
		$categories = urlencode( $categories );
	if ( $content ) {
		$content = strip_tags( $content );

		if ( strlen( $content ) > 200 ) {
			$content_cut = substr( $content, 0, 200 );
			$last_space = strrpos( $content_cut, " ");
			$content_ok = substr( $content_cut, 0,$last_space );
			$content = $content_ok . "...";
		}
		$content = urlencode( $content );
	}

	// t= post
	// i= (item)titolo del post
	// at= @nicolagreco
	// l= link di provenienza
	// c= category
	// e= excerpt
	// b= blog url
	// n= post number

	if ( function_exists( 'twittami_hook_init' ) or $direct ) {
		$return = $twittami->site . '/twit.php?t=post';
	} else {
		$return = get_permalink( $post_id ) . "?";
	}

	if ( $direct )
		$return .= '&';

		$return .= "n={$post_id}"; // n

	if ( $title )
		$return .= "&i={$title}"; // i

	if ( $at )
		$return .= "&at={$at}";
	elseif ( $direct )
		$return .= "&at={$twittami->options->at}"; // at

	if ( $link2post )
		$return .= "&l={$link2post}"; // l

	if ( $categories )
		$return .= "&c={$categories}"; // c

	if ( $content )
		$return .= "&e={$content}"; // e

	if ( $blog_url )
		$return .= "&b={$blog_url}"; // b

//	if ( !$direct )
//		$return .= "&twittami=frame"; // n

	return apply_filters( 'twittami_link_generate', $return, $data );

}

function twittami_query ( $method, $args = array(), $debug = false, $type = "wp_http" ) {

	global $twittami;

	if ( !$method )
		return false;

	if ( 'xmlrpc' == $type ) {

		$xmlrpc_url = $twittami->site . '/xmlrpc.php';
		$client = new IXR_Client( $xmlrpc_url );
		$client->debug = $debug;
		$client->timeout = 3;
		$client->useragent .= ' -- Twittami XMLRPC Client /0.3';

		if ( !is_array( $args ) )
			$args = array();

		if ( !$client->query( $method, $args ) )
			// wp_die( 'Something went wrong - ' . $client->getErrorCode() . ' : ' . $client->getErrorMessage() );
			return '?';

		return $client->getResponse();

	} elseif ( 'wp_http' == $type ) {

		$twittami->cache->post_{$args[1][0]} = wp_cache_get( 'post_' . $args[1][0], $twittami->cache->post_{$args[1][0]}, 'twittami' );
		if ( false === $twittami->cache->post_{$args[1][0]} ) {

			$http_url = $twittami->site . "/twit.php?t=count";
			$http_url .= "&method={$args[0]}";

			if ( isset( $args[1][0] ) )
				$http_url .= "&arg_0={$args[1][0]}";
			if ( isset( $args[1][1] ) )
				$http_url .= "&arg_1={$args[1][1]}";

			$return = wp_remote_request( $http_url, array( 'timeout' => 2 ) );

			if ( is_array( $return ) ) {
				$return = $return['body'];
				$twittami->cache->post_{$args[1][0]} = $return;
				wp_cache_set( 'post_' . $args[1][0], $twittami->cache->post_{$args[1][0]}, 'twittami' );
			} else {
				return '?';
			}

		}
		return $twittami->cache->post_{$args[1][0]};

	}

}