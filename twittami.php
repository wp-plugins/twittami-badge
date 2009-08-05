<?php
/*
Plugin Name: Twittami!
Author: Nicola Greco
Author URI: http://nicolagreco.com/
Plugin URI: http://twittami.com/badge
Description: Aggiunge gli strumenti Twittami.com
Version: 0.4
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

add_action( 'init', 'twittami_buttons_init' );
add_action( 'init', 'twittami_button_get' );

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

function twittami_buttons_init () {

	global $twittami;

	wp_enqueue_script('jquery');

	$twittami->site = 'http://twittami.com';
	$default = array(
		'button_position' => '1',
		'at' => 'retwittami',
		'request' => 'ajax',
		'suggestion'=> 'Se ti &egrave; piaciuto, retwittami!',
		'striscia_size' => '2'
	);

	$get_twittami = get_option( 'twittami_settings' );
	if ( is_array( $get_twittami ) ) 
		foreach ( $get_twittami as $key => $value )
			$twittami->settings->{$key} = $value;
	else
		add_option( 'twittami_settings', $default );

	switch ( $twittami->settings->button_type ) {
		case '1':
			$twittami->options->button_type = 'normal';
		break;
		case '2':
			$twittami->options->button_type = 'normal';
		break;
		case '3':
			$twittami->options->button_type = 'compact';
		break;
		case '4':
			$twittami->options->button_type = 'compact';
		break;
		case '5':
			$twittami->options->button_type = 'normal';
		break;
	}

	switch ( $twittami->settings->button_type ) {
		case '1':
			$twittami->options->button_align = 'destra';
			break;
		case '2':
			$twittami->options->button_align = 'sinistra';
			break;
		case '3':
			$twittami->options->button_align = 'destra';
			break;
		case '4':
			$twittami->options->button_align = 'sinistra';
			break;
		case '5':
			$twittami->options->button_align = 'sinistra';
			break;
		case '6':
			$twittami->options->button_align = 'manuale';
			break;
	}

	switch ( $twittami->settings->button_type ) {
		case '1':
			$twittami->options->button_position = 'alto';
			break;
		case '2':
			$twittami->options->button_position = 'alto';
			break;
		case '3':
			$twittami->options->button_position = 'basso';
			break;
		case '4':
			$twittami->options->button_position = 'basso';
			break;
		case '5':
			$twittami->options->button_position = 'box';
			break;
		case '6':
			$twittami->options->button_position = 'manuale';
			break;
	}

	switch ( $twittami->settings->striscia_size ) {
		case '1':
			$twittami->options->striscia_size = ' tiny';
			break;
		case '2':
			$twittami->options->striscia_size = '';
			break;
	}

	$twittami->options->at = $twittami->settings->at;
	$twittami->options->suggestion = $twittami->settings->suggestion;

	if ( !is_feed() ) {
		if ( 'manuale' != $twittami->options->button_position and 'box' != $twittami->options->button_position )
			add_filter('the_content', 'twittami_button' );
		elseif ( 'box' == $twittami->options->button_position )
			add_filter('the_content', 'twittami_button_box' );
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

			$(".twittami_count a").load("/?vote_id=" + uid);

			$this.ajaxStop(function(r,s) {  
				$(".twittami_badge .loader").hide();
				$(".twittami_count a").fadeIn("slow");
			});
		});
	});
	-->
</script>
<!-- Twittami.com | Script JS - End -->
	<?php

}

function twittami_settings_init (){

	if ( !is_admin() )
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

		switch ( $twittami->settings->button_type ) {
			case '1':
				$btn1_1 = 'checked="true"';
			break;
			case '2':
				$btn1_2 = 'checked="true"';
			break;
			case '3':
				$btn1_3 = 'checked="true"';
			break;
			case '4':
				$btn1_4 = 'checked="true"';
			break;
			case '5':
				$btn1_5 = 'checked="true"';
			break;
		}
		switch ( $twittami->settings->striscia_size ) {
			case '1':
				$btn2_1 = 'checked="true"';
			break;
			case '2':
				$btn2_2 = 'checked="true"';
			break;
		}
?>
<div class="wrap">
	<div class="icon32" id="icon-options-general"><br/></div>
	<script>
	jQuery(document).ready(function($) {
	});
	</script>
	<h2>Configurazione Twittami</h2>
	<form action='' method='post' id='twittami'>

		<table cellspacing="20" width="80%">
			<tr>
				<td valign="top">Scegli un badge</td>
				<td>
					<input type='radio' name='twittami_settings[button_type]' value='1' <?php echo $btn1_1 ?>/>
					Badge in alto a destra (standard)
					<br/><br>
					<img src="http://twittami.com/e/images/badge_nd-demo.png"/>

					<input type='radio' name='twittami_settings[button_type]' value='2' <?php echo $btn1_2 ?>/>
					Badge in alto a sinistra
					<br/><br>
					<img src="http://twittami.com/e/images/badge_ns-demo.png"/>

					<input type='radio' name='twittami_settings[button_type]' value='3' <?php echo $btn1_3 ?>/>
					Compatto in basso a destra
					<br/><br>
					<img src="http://twittami.com/e/images/compact_bd-demo.png"/>

					<input type='radio' name='twittami_settings[button_type]' value='4' <?php echo $btn1_4 ?>/>
					Compatto in basso a sinistra
					<br/><br>
					<img src="http://twittami.com/e/images/compact_bs-demo.png"/>

					<input type='radio' name='twittami_settings[button_type]' value='5' <?php echo $btn1_5 ?>/>
					Striscia dopo il post (consigliato)
					<br/>
					<img src="http://twittami.com/e/images/striscia-demo.png"/>
					<input type='radio' name='twittami_settings[striscia_size]' value='1' <?php echo $btn2_1 ?>/>Piccola 
					<input type='radio' name='twittami_settings[striscia_size]' value='2' <?php echo $btn2_2 ?>/>Normale
					<br/>
					<input type='text' name='twittami_settings[suggestion]' value='<?php echo $twittami->settings->suggestion ?>'/><br>
					<small>Questo è il testo da aggiungere nel box affianco al badge</small>
					<br/>

					<input type='radio' name='twittami_settings[button_position]' value='3' <?php echo $btn2_3 ?>/>
					Posizione manuale
					<br/><br/>
					<small>L'ultima voce &egrave; per i pi&ugrave; esperti, basta posizionare <code><?php echo htmlentities( '<?php echo twittami_button() ?>' ) ?></code> dove vuoi aggiungere il badge, se vuoi aggiungere il box a fine post, usa <code><?php echo htmlentities( '<?php echo twittami_button_box() ?>' ) ?></code></small>

				</td>
			</tr>
			<tr>
				<td width="20%" valign="top">Preview del tweet</td>
				<td>
					<p style="border-top-width:1px; font-family:Georgia,serif; font-size:1em; font-style:italic;">
						RT @<b><input type='text' name='twittami_settings[at]' value='<?php echo $twittami->settings->at ?>' size="15" style="display:inline; margin-bottom:10px; margin-left:5px;margin-right:5px; margin-top:0" /></b>: Il mio post su Twittami <?php echo get_bloginfo('url') ?>
					</p>
			  </td>

			</tr>
		</table>

	  <p class="submit">
		<input type='submit' name='twittami-save' value='Salva impostazioni'/>
	</p>
	</form>
</div>
<?php

}

function twittami_settings_stats () { ?>

	<h3>Statistiche</h3>
	<p>Questa feature sarà aggiunta nella prossima versione</p>

<?php }

function twittami_button ( $content = false, $options = array() ) {

	global $twittami, $post;

	$options['post_id'] = $post->ID;

	$link = twittami_link_generate( $options );

	$button = '<div id="box-' . $post->ID . '" class="twittami_badge twittami_badge_' . $twittami->options->button_type . ' ' . $twittami->options->button_align . '">';
	$button .= '<img src="' . WP_PLUGIN_URL . '/twittami-badge/images/ajax-loader.gif' . '" class="loader"/>';
	$button .= '<div class="twittami_count">';
	$button .= '<a href="' . $link . '" title="' . __( 'Pubblica la notizia su twitter e fai girare la voce', 'twittami' ) . '" rel="twittamibox"></a>';
	$button .=  '</div><!-- end .twittami_count -->';
	$button .= '<div class="twittami_button">';
	$button .= '<a href="' . $link . '" title="' . __( 'Pubblica la notizia su twitter e fai girare la voce', 'twittami' ) . '" rel="twittamibox">twittami</a>';
	$button .= '</div><!-- end .twittami_button -->';

	$button .= '</div>';

	if ( 'alto' == $twittami->options->button_position )
		$content = $button . $content;
	elseif ( 'basso' )
		$content = $content . $button;

	return $content;
}

function twittami_button_code ( $content = false, $data = array(), $options = array() ) {

	global $twittami;

	$data = array_merge( $data, $options );

	if ( !function_exists( 'twittami_hook_init' ) )
		$vote = twittami_query( 'twittami.getCount', array( 'id', array( (int)$data['post_id'], get_bloginfo('url') ) ) );
	else
		$vote = get_comments_number( $data['post_id'] );

	if ( $vote === false )
		$vote = '?';

	return $vote;
}

function twittami_button_box ( $content ) {

	global $post, $twittami;

	$button = twittami_button();

	// $html .= '<a rel="twittamibox" title="Pubblica la notizia su twitter e fai girare la voce" href="http://nicolamac.local/2009/07/30/twittami-bestof-numero-2/?n=20">twittami</a>';
	$html .= '<div class="twittami_box' . $twittami->options->striscia_size . '">';
	$html .= $button;
	$html .= '<div class="suggestion">' . $twittami->options->suggestion . '</div>';
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