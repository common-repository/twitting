<?php
/*
Plugin Name: Twitting
Description: Envia para o Twitter os posts do blog (como twitterfeed) utilizando sua própria Twitter OAuth. Suporta posts agendados.
Version: 0.0.1
Author: FR3AKSTEIN
Author URI: http://fr3akste.in
License: GPLv3
*/

/*
This program is free software; you can redistribute it and/or modify 
it under the terms of the GNU General Public License as published by 
the Free Software Foundation; version 2 of the License.

This program is distributed in the hope that it will be useful, 
but WITHOUT ANY WARRANTY; without even the implied warranty of 
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the 
GNU General Public License for more details. 

You should have received a copy of the GNU General Public License 
along with this program; if not, write to the Free Software 
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA 
*/

require_once 'OAuth.php';
function twitting_config(){
if(!current_user_can('manage_options')) {
	wp_die('Não autorizado!');
}
?>
<div class="wrap">
	<div id="icon-options-general" class="icon32"><br /></div>
	<h2>Configuração</h2>
	<?php
	if(isset($_POST['submit'])){
		$options['consumerkey']		= trim($_POST['set_ckey']);
		$options['consumersecret']	= trim($_POST['set_csec']);
		$options['token']			= trim($_POST['set_token']);
		$options['secret']			= trim($_POST['set_secret']);
		$options['format']			= trim($_POST['set_formatpost']);
		if(trim($_POST['type_short']) == 'migreme'){
			$options['shorturl']	= array('migreme' => 'http://migre.me/api.txt?url=%link%');
		}
		if(trim($_POST['type_short']) == 'tinyurl'){
			$options['shorturl']    = array('tinyurl' => 'http://tinyurl.com/api-create.php?url=%link%');
		}
		if(trim($_POST['type_short']) == 'bitly'){
			$options['shorturl']    = array('bitly' => 'http://api.bit.ly/v3/shorten?login='.trim($_POST['login']).'&apiKey='.trim($_POST['apikey']).'&longUrl=%link%&format=txt',
											'login' => trim($_POST['login']),
											'apiKey' => trim($_POST['apikey']));
		}
		if(trim($_POST['type_short']) == 'custom'){
			$options['shorturl']    = array('custom' => trim($_POST['custom_short']));
		}
		update_option('twitting_options', $options);
	?>
    <div class="updated">
    	<p><strong><?php echo 'Atualizado!';?></strong></p>
    </div>
    <?php
	}
	$options	= get_option('twitting_options');
	if(!empty($options)){
		$shorturl	= array_keys($options['shorturl']);
		$shortapi	= array_values($options['shorturl']);
		if((!empty($shortapi[1]))AND(!empty($shortapi[2]))){
			$login	= $shortapi[1];
			$apikey	= $shortapi[2];
		}
	}
	?>
	<table border="0" cellpadding="5" cellspacing="10" width="800">
		<tr>
			<td valign="top" width="50%">
				<form method="post">
					<div id="poststuff">
						<div class="postbox">
							<h3 class="hndle">OAuth Keys
								<div class="tmpUpBt">
									<input type="submit" name="submit" class="button-primary" value="Salvar" />
								</div>
							</h3>
							<div class="inside">
								<p>Consumer Key: <br/>
								<input type="password" name="set_ckey" size="60" value="<?php echo $options['consumerkey']; ?>" /></p>
								<p>Consumer Secret: <br/>
								<input type="password" name="set_csec" size="60" value="<?php echo $options['consumersecret']; ?>" /></p>
								<p>Access Token: <br/>
								<input type="password" name="set_token" size="60" value="<?php echo $options['token']; ?>" /></p>
								<p>Access Token Secret: <br/>
								<input type="password" name="set_secret" size="60" value="<?php echo $options['secret']; ?>" /></p>
							</div>
						</div>
					</div>
					<div id="poststuff">
						<div class="postbox">
							<h3 class="hndle">Tweet -> Formato
									<div class="tmpUpBt">
										<input type="submit" name="submit" class="button-primary" value="Salvar" />
									</div>
								</h3>
							<div class="inside">
								<p>
								<input type="text" name="set_formatpost" size="60" value="<?php if(empty($options)){echo('%title% - %link%');}else{echo($options['format']);}?>"/><br/>
								<small>
								%title% - Título do artigo/post
								<br/>
								%link% - URL do artigo/post
								<br/>
								%cat% - Catégoria do artigo/post
								</small>
								</p>
							</div>
						</div>
					</div>
					<div id="poststuff">
						<div class="postbox">
							<h3 class="hndle">Encurtador
									<div class="tmpUpBt">
										<input type="submit" name="submit" class="button-primary" value="Salvar" />
									</div>
								</h3>
							<div class="inside">
								<table class="form-table">
									<tr>
										<td><label><input name="type_short" type="radio" value="tinyurl" onclick="document.getElementById('customapi').style.display='none'; document.getElementById('bitly').style.display='none';" <?php if(($shorturl[0] == 'tinyurl')OR(empty($options))){echo'checked';}?>/>TinyURL</label></td>
										<td><label><input name="type_short" type="radio" value="migreme" onclick="document.getElementById('customapi').style.display='none'; document.getElementById('bitly').style.display='none';" <?php if($shorturl[0] == 'migreme'){echo'checked';}?>/>Migre.me</label></td>
									</tr>
									<tr>
										<td><label><input name="type_short" type="radio" value="bitly" onclick="document.getElementById('customapi').style.display='none'; document.getElementById('bitly').style.display='block';" <?php if($shorturl[0] == 'bitly'){echo'checked';}?>/>Bitly</label></td>
										<td><label><input name="type_short" type="radio" value="custom" onclick="document.getElementById('bitly').style.display='none'; document.getElementById('customapi').style.display='block';" <?php if($shorturl[0] == 'custom'){echo'checked';}?>/>Personalizado</label></td>
									</tr>
								</table>
								<table class="form-table">
									<tr>
										<td><div id="customapi" style="display:none">
												<label style="display:inline-block;width:35px;"><strong>API </strong></label> <input name="custom_short" size="45" type="text" value="<?php if($shorturl[0] == 'custom'){echo $shortapi[0];}?>" />
												<p>
													<small>ex: http://tinyurl.com/api-create.php?url=<strong>%link%</strong></small>
														<br />
													<small>%link% - URL do artigo/post</small>
												</p>
											</div>
											<div id="bitly" style="display:none">
												<label style="display:inline-block;width:125px;"><strong>Bitly Login</strong></label> <input name="login" size="25" type="text" value="<?php echo($login);?>" />
													<br />
												<label style="display:inline-block;width:125px;"><strong>Bitly API Key</strong></label> <input name="apikey" size="25" type="text" value="<?php echo($apikey);?>" />
											</div>
										</td>
									</tr>
								</table>
							</div>
						</div>
					</div>
				</form>
			</td>
			<td valign="top" width="50%">
				<div id="poststuff">
					<div class="postbox">
						<h3 class="hndle">Como me ajudar?
							<div class="tmpUpBt">
								<input type="button" onClick="window.open('http://fr3akste.in/ask')" class="button-primary" value="Entre em Contato" />
							</div>
						</h3>
						<div class="inside">
							<p>
								<strong>Me ajude a melhor o plugin ;)</strong>
									<br/>
								Envie-me relatórios de bugs, correções, modificações de código ou suas ideias.
							</p>
							<p>
								<strong>Compartilhe!</strong>
									<br/>
								<a href="http://twitter.com/share" class="twitter-share-button" data-url="http://wordpress.org/extend/plugins/twitting/" data-text="Eu uso o Twitting e recomendo #wp #plugin" data-count="horizontal">Tweet</a><script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script>
								<iframe src="//www.facebook.com/plugins/like.php?href=http%3A%2F%2Fwordpress.org%2Fextend%2Fplugins%2Ftwitting%2F&amp;send=false&amp;layout=button_count&amp;width=150&amp;show_faces=false&amp;action=recommend&amp;colorscheme=light&amp;font&amp;height=21" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:150px; height:21px;" allowTransparency="true"></iframe>
							</p>
							<p>
								<strong>Sigam-me os bons!</strong>
									<br/>
								<a href="https://twitter.com/fr3akstein" class="twitter-follow-button" data-show-count="false" data-size="large">Follow @FR3AKSTEIN</a>
								<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
								<iframe src="//www.facebook.com/plugins/like.php?href=http%3A%2F%2Fwww.facebook.com%2Fpages%2FFR3AKSTEIN%2F256949434380852&amp;send=false&amp;layout=button_count&amp;width=200&amp;show_faces=false&amp;action=like&amp;colorscheme=light&amp;font&amp;height=21&amp;appId=264202633641968" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:200px; height:21px;" allowTransparency="true"></iframe>
							</p>
						</div>
					</div>
				</div>
				<div id="poststuff">
					<div class="postbox">
						<h3 class="hndle">Precisa de Ajuda?
							<div class="tmpUpBt">
								<input type="button" onClick="window.open('http://youtu.be/y9BROm8TW4Y?hd=1')" class="button-primary" value="Veja no YouTube" />
							</div>
						</h3>
						<div class="inside">
							<p>
								<strong>
								Já tem a Access Token?
								</strong>
									<br/>
								Registre sua <a href="http://dev.twitter.com/apps/new" target="_blank">aplicação no Twitter</a>.
							</p>
							<p>
								<iframe width="350" height="230" src="http://www.youtube.com/embed/y9BROm8TW4Y?rel=0" frameborder="0" allowfullscreen></iframe>
							</p>
						</div>
					</div>
				</div>
			</td>
		</tr>
	</table>
</div>
<?php
}
$options = get_option('twitting_options');
$TwittingOAuth = new tmhOAuth(array(
	'consumer_key'		=> $options['consumerkey'],
	'consumer_secret'	=> $options['consumersecret'],
	'user_token'		=> $options['token'],
	'user_secret'		=> $options['secret'],
));
function twitting_shorturl($url){
	global $options;
	$shortapi	= array_values($options['shorturl']);
	$api_url	= str_replace('%link%',$url,$shortapi[0]);
	$ch			= curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_URL, $api_url);
	$s_url		= curl_exec($ch);
	curl_close($ch);
	return trim($s_url);
}
function twitting($postID){
	global $options,$TwittingOAuth;
	$post		= get_post($postID);
	$title		= $post->post_title;
	$post_url	= twitting_shorturl(get_permalink($post->ID));
	$category	= wp_get_post_categories($post->ID);
	$category	= get_cat_name($category[0]);
	$now		= array($title,$category,$post_url);
	$search		= array('%title%','%cat%','%link%');
	$strSearch	= isset($_POST['twitting_post'])?$_POST['twitting_post']:$options['format'];
	$tweet		= str_replace($search,$now,$strSearch);
	$tweet		= $TwittingOAuth->request('POST', $TwittingOAuth->url('1/statuses/update'), array('status'=>$tweet));
	if ($tweet == 200) {
		$resposta	= json_decode($TwittingOAuth->response['response']);
		$resposta	= array(
							"url"	=> "http://twitter.com/".$resposta->user->screen_name."/statuses/".$resposta->id_str,
							"data"	=> $resposta->created_at,
							"tweet"	=> $resposta->text,
		);
		add_post_meta($postID, 'twitting', $resposta);
	} else {
		$resposta	= json_decode($TwittingOAuth->response['response']);
		$resposta	= array('erro'	=> $resposta->error);
		add_post_meta($postID, 'twitting', $resposta);
	}
}
function twitting_menu(){
	if(function_exists('add_menu_page')){
		add_menu_page(__('Twitting &lsaquo; Configuração', 'twitting'), 'Twitting', 'manage_options', 'twitting', 'twitting_config');
	}
}
function twitting_init(){
	wp_register_style('twitting_style', WP_PLUGIN_URL . '/twitting/css/style.css');
}
function twitting_style(){
	wp_enqueue_style('twitting_style');
}
function twitting_warning(){
	echo '<div class="error"><p><a href="'.get_bloginfo('wpurl').'/wp-admin/admin.php?page=twitting">Por favor, configure o <strong>Twitting</strong>.</a></p></div>';
}
add_action('admin_menu','twitting_add_box');
function twitting_add_box(){
	if(function_exists('add_meta_box')){
		add_meta_box('twitting','Twitting','twitting_box','post','side','high');
	}
}
function twitting_box(){
	global	$post,$options;
	$twt	= get_post_meta($post->ID, 'twitting', true);
	//print_r($twt);
	if($twt['tweet']){
		echo	<<<TWEETADO
<p>
	<label>
		<a href='{$twt['url']}' target='_blank'
		style='color:green;text-decoration:none;font-weight:700;'>Tweetado ({$twt['data']})</a>
			<br />
		<input id="twitting_post" size="38" type="text" name="twitting_post" value="{$twt['tweet']}" />
	</label>
<p>
TWEETADO;
	}
	elseif($twt['erro']){
		echo	<<<ERROR
<p>
	<label>
		<span style='color:red;text-decoration:none;font-weight:700;'>
			Erro ao tweetar:
		</span>
			<br />
		<input id="twitting_post" size="38" type="text" name="twitting_post" value="{$twt['erro']}"/>
	</label>
</p>
ERROR;
	}
	else{
		if(($post->post_status) == 'publish'){
			echo	<<<PROMO
<p>
<a href="https://twitter.com/fr3akstein" class="twitter-follow-button" data-show-count="false" data-size="large">Follow @fr3akstein</a>
<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
<iframe src="//www.facebook.com/plugins/like.php?href=http%3A%2F%2Fwww.facebook.com%2Ffr3akstein&amp;send=false&amp;layout=button_count&amp;width=200&amp;show_faces=false&amp;action=like&amp;colorscheme=light&amp;font&amp;height=21&amp;appId=264202633641968" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:200px; height:21px;" allowTransparency="true"></iframe>
</p>
PROMO;
		}
		else{
			echo	<<<TWEET
<p>
	<label>
		<input id="wptwitting" type="checkbox" name="wptwitting" value="1" checked />
		Tweetar?
	</label>
	<label>
		<strong>Tweet</strong>
			<br />
		<input id="twitting_post" size="38" type="text" name="twitting_post" value="{$options['format']}" />
	</label>
	<small>
	%title% - Título do artigo/post
		<br/>
	%link% - URL do artigo/post
		<br/>
	%cat% - Catégoria do artigo/post
	</small>
</p>
TWEET;
		}
	}
}
add_action('admin_init', 'twitting_init');
add_action('admin_menu', 'twitting_menu');
add_action('admin_print_styles', 'twitting_style');
add_action('publish_future_post', 'twitting',10,1);
if($_POST['wptwitting'] == '1'){
	add_action('publish_post', 'twitting');
}
if(empty($options)){
	add_action('admin_notices', 'twitting_warning');
}
?>