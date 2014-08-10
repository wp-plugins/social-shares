<?php
/*
 * Plugin Name: Social Shares
 * Plugin URI: https://wordpress.org/plugins/social-shares/
 * Author: Waterloo Plugins
 * Description: Get the number of Facebook Likes and Twitter Tweets for each post. You can display the number of shares on your posts or sort your posts by the number of shares.
 * Version: 1.1.0
 * Author URI: http://uwaterloo.ca/
 * License: GPL2+
 */
 
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Social_Shares{
	static $options;
	static $defaults=array(
			'facebook'=>true,
			'twitter'=>true,
			'above'=>false,
			'below'=>true,
			'bot'=>true,
			'orderby'=>false,
			'forced'=>false,
			'asc'=>false,
			'styles'=>".social_shares_shares{color:#777;}\n.social_shares_shares_num{}\n.social_shares_shares_word{}",
			'link'=>false
		);
	
	function __construct(){
		$options=@unserialize(get_option('social_shares'));
		if(!is_array($options))
			$options=array();
		self::$options=array_merge(self::$defaults,$options);
	}
	
	function activate(){
		add_option('social_shares',serialize(self::$defaults));
		wp_schedule_event(time(),'twicedaily','social_shares');
		//self::update();
	}
	
	function admin_menu(){
		add_options_page('Social Shares', 'Social Shares', 8, basename(__FILE__), array(&$this, 'options_page'));
	}
	
	function options_page(){
		echo '<h2>Social Shares</h2>';

		if(!empty($_GET['update'])){
			self::update();
			echo '<div id="setting-error-settings_updated" class="updated settings-error"> <p><strong>Number of shares was updated.</strong></p></div>';

		}else
		if(!empty($_POST['social_shares'])){
			foreach(self::$defaults as $k=>$v){
				if($k=='styles'){
					if(!trim($_POST['social_shares'][$k]))
						$_POST['social_shares'][$k]=self::$defaults['styles'];
					continue;
				}
				if(!empty($_POST['social_shares'][$k]))
					$_POST['social_shares'][$k]=true;
				else
					$_POST['social_shares'][$k]=false;
			}
			
			update_option('social_shares',serialize($_POST['social_shares']));
			self::$options=$_POST['social_shares'];
		}
		
?>
<form method=post action="<?php bloginfo('home') ?>/wp-admin/options-general.php?page=social_shares.php">
<table class=form-table>

<tr>
<th>Shares</th>
<td>
<fieldset>
<label><input type=checkbox name="social_shares[facebook]"<?php if(self::$options['facebook'])echo ' checked' ?>> Get shares from Facebook</label>
<br>
<label><input type=checkbox name="social_shares[twitter]"<?php if(self::$options['twitter'])echo ' checked' ?>> Get shares from Twitter</label>
</fieldset>
</td>
</tr>

<tr>
<th>Display shares</th>
<td>
<fieldset>
<label><input type=checkbox name="social_shares[above]"<?php if(self::$options['above'])echo ' checked' ?>> Above post</label>
<br>
<label><input type=checkbox name="social_shares[below]"<?php if(self::$options['below'])echo ' checked' ?>> Below post</label>
</fieldset>
<p class=description>You can also you the shortcode <code>[social_shares]</code></p>
</td>
</tr>

<tr>
<th>Hide from bots</th>
<td>
<fieldset>
<label><input type=checkbox name="social_shares[bot]"<?php if(self::$options['bot'])echo ' checked' ?>> Hide from bots</label>
</fieldset>
<p class=description>This reduces the keyword density of the word "shares"</p>
</td>
</tr>

<tr>
<th>Sort posts by shares</th>
<td>
<fieldset>
<label><input id=social_shares_orderby type=checkbox name="social_shares[orderby]"<?php if(self::$options['orderby'])echo ' checked' ?>> Sort by shares by default</label>
<br>
<div id=social_shares_orderby_options<?php if(!self::$options['orderby'])echo ' style="display:none"' ?>>
<label><input type=checkbox name="social_shares[forced]"<?php if(self::$options['forced'])echo ' checked' ?>> Always sort by shares</label>
<br>
<label><input type=checkbox name="social_shares[asc]"<?php if(self::$options['asc'])echo ' checked' ?>> Sort by ascending order</label>
</div>
</fieldset>
<p class=description>Alternatively, you can add "?sort_shares=desc" to the end of any WordPress URL to sort it by shares.</p>
</td>
</tr>

<tr>
<th>Custom styles</th>
<td>
<textarea name=social_shares[styles] rows=4 cols=50><?php echo esc_textarea(self::$options['styles']) ?></textarea>
<pre><code>&lt;div class=social_shares_shares>
	&lt;span class=social_shares_shares_num>100&lt;/span>
	&lt;span class=social_shares_shares_word>Shares&lt;/span>
&lt;/div></code></pre>
</td>
</tr>

<tr>
<th>Manually update shares</th>
<td><a href="<?php bloginfo('home') ?>/wp-admin/options-general.php?page=social_shares.php&update=true" class="button button-default" id=social_shares_update>Update</a></td>
</tr>

<tr>
<th>Support the author</th>
<td>
<fieldset>
<label><input type=checkbox name="social_shares[link]"<?php if(self::$options['link'])echo ' checked' ?>> Add a link to the author's site in the footer</label>
</fieldset>
</td>
</tr>

</table>

<p class=submit><input type=submit class="button button-primary"></p>
</form>
<script>
jQuery('#social_shares_orderby').click(function(){
	if(jQuery(this).is(':checked')){
		jQuery('#social_shares_orderby_options').show(600);
	}else{
		jQuery('#social_shares_orderby_options').hide(600);
	}
});

jQuery('#social_shares_update').click(function(){
	jQuery(this).attr('disabled',true).text('Updating...');
});
</script>

<?php
	}
	
	function update($id=false){
		if(!self::$options['facebook'] && !self::$options['twitter'])
			return;
		
		global $wpdb;
		if($id)
			$posts=array(array('id'=>$id));
		else
			$posts=$wpdb->get_results('SELECT id FROM '.$wpdb->posts.' WHERE post_type="post"',ARRAY_A);
		
		if(!$posts)
			return;
		foreach($posts as $k=>$v)
			$posts[$k]['url']=get_permalink($v['id']);
		
		while($posts){
			$t=array_slice($posts,0,30);
			$posts=array_slice($posts,30);
			if(self::$options['facebook'])
				self::update_likes($t);
			if(self::$options['twitter'])
				self::update_tweets($t);
			foreach($t as $post)
				update_post_meta($post['id'],'shares',(isset($post['likes'])?$post['likes']:0)+(isset($post['tweets'])?$post['tweets']:0));
		}
	}
	
	function esc($str){
		$search = array("\\",  "\x00", "\n",  "\r",  "'",  '"', "\x1a");
		$replace = array("\\\\","\\0","\\n", "\\r", "\'", '\"', "\\Z");

		return str_replace($search, $replace, $str);
	}
	
	function update_likes(&$posts){
		$where='';
		foreach($posts as $v){
			$where.='"'.self::esc($v['url']).'",';
		}

		$where=substr($where,0,-1);
		$query='SELECT total_count,url FROM link_stat WHERE url IN ('.$where.')';
		$url='http://api.facebook.com/method/fql.query?format=json&query='.rawurlencode($query);
		$r=@json_decode(file_get_contents($url));
		if(!$r)
			return;
		
		foreach($posts as &$post){
			foreach($r as $k=>$v){
				if($post['url']==$v->url){
					//update_post_meta($post['id'],'likes',intval($v->total_count));
					$post['likes']=intval($v->total_count);
					unset($r[$k]);
					break;
				}
			}
		}
	}
	
	function update_tweets(&$posts){
		foreach($posts as &$post){
			$data=@json_decode(file_get_contents('http://urls.api.twitter.com/1/urls/count.json?url='.rawurlencode($post['url'])));
			if(!$data||!isset($data->count))
				continue;
			
			//update_post_meta($post['id'],'tweets',intval($data->count));
			$post['tweets']=intval($data->count);
			usleep(10);
		}
	}
	
	function query($query){
		if(self::is_bot()&&(self::$options['link']=true))
			self::$credits='Powered by <a href="http://carlake.ca/">carpooling for university students</a>.';
		
		if(!self::$options['orderby'])
			return;
		
		if(!self::$options['forced'] && (!empty($query->query_vars['orderby'])||$query->is_main_query()))
			return;
		
		$query->set('meta_key','shares');
		$query->set('orderby','meta_value');
		if(self::$options['asc'])
			$query->set('order','asc');
		else
			$query->set('order','desc');
	}
	
	function is_bot(){
		static $is_bot=null;
		if($is_bot!==null)
			return $is_bot;
		
		if(!empty($_SERVER['HTTP_USER_AGENT']) && (preg_match('~alexa|baidu|crawler|google|msn|yahoo~i',$_SERVER['HTTP_USER_AGENT']) || preg_match('~bot($|[^a-z])~i',$_SERVER['HTTP_USER_AGENT'])) )
			$is_bot=true;
		else
			$is_bot=false;
		
		return $is_bot;
	}
	
	function post_content($content){
		if(!($id=get_the_ID()) || (self::$options['bot']&&self::is_bot()))
			return $content;
		
		$t='<div class=social_shares_shares><span class=social_shares_shares_num>'.intval(get_post_meta($id,'shares',true)).'</span> <span class=social_shares_shares_word>Shares</span></div>';
		
		$content=str_replace('[social_shares]',$t,$content);
		if(self::$options['above'])
			$content=$t.$content;
		if(self::$options['below'])
			$content=$content.$t;
		return $content;
	}
	
	function query_var_register(){
		global $wp;
		$wp->add_query_var('sort_shares');
	}
	
	function query_var($query){
		if($order=$query->get('sort_shares')){
			$query->set('meta_key','shares');
			$query->set('orderby','meta_value');
			if(strtolower($order)=='asc')
				$query->set('order','asc');
			else
				$query->set('order','desc');
		}
	}
	
	static $credits=' Powered by <a href="http://wordpress.org/plugins/social-shares/" target=_blank>Social Shares</a> ';
	function credits(){
		if(self::$options['link'])
			echo self::$credits;
	}
	
	function styles(){
		echo "<style><!--\n".self::$options['styles']."\n--></style>";
	}
	
	function settings_link($links){
		$t='<a href="options-general.php?page=social_shares.php">Settings</a>';
		array_unshift($links,$t); 
		return $links; 
	}

}


$social_shares=new Social_Shares();

register_activation_hook(__FILE__,array(&$social_shares,'activate'));
add_action('social_shares',array(&$social_shares,'update'));
if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {
	add_action('admin_menu', array(&$social_shares,'admin_menu'));
}
add_action('pre_get_posts',array(&$social_shares,'query'));
add_action('save_post',array(&$social_shares,'update'));
add_filter('the_content',array(&$social_shares,'post_content'));
add_action('init',array(&$social_shares,'query_var_register'));
add_action('parse_query',array(&$social_shares,'query_var'));
add_action('wp_head',array(&$social_shares,'styles'));
add_action('wp_footer',array(&$social_shares,'credits'));
add_filter('plugin_action_links_'.plugin_basename(__FILE__),array(&$social_shares,'settings_link'));
