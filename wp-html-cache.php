<?php
/*
Plugin Name: WP html cache
Plugin URI: http://www.wpxue.com/wp-html-cache
Description: WP html cache is a plugin to accelerate the speed of your wordpress, by generating real html cache file, WP html cache will automatically generate real html files for posts when they are loaded for the first time, when the post published and a comment posted automatically update the html cache file.it is Very fast
Version: 1.0
Author: wpxue
Author URI:http://www.wpxue.com/
*/
/*

				
*/
/* config */
define('IS_INDEX',0);// false = do not create home page cache 

/*end of config*/

define('wphcVERSION','2.7.3');

require_once(ABSPATH . 'wp-admin/includes/file.php');
/* end of config */
$sm_locale = get_locale();

$sm_mofile = dirname(__FILE__) . "/wphcbeta-$sm_locale.mo";
load_textdomain('wphcbeta', $sm_mofile);
$wphcsithome = get_option('home');
$script_uri = rtrim( "http://".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"]  ,"/");
$home_path = get_home_path();

define('SCRIPT_URI',$script_uri);
define('wphcSiteHome',$wphcsithome);
define('wphcBlogPath', $home_path);
define("wphcMETA","<!--this is a real static html file created at ".date("Y-m-d H:i:s")." by wphc-html-cache ".wphcVERSION." -->");
//生成html的函数，可以生成目录形式的url aaa.com/html/ 会自动生成html文件夹，在html文件夹下生成index.html文件
function WPxueCreateHtmlFile($FilePath,$Content){
	$FilePath = preg_replace('/[ <>\'\"\r\n\t\(\)]/', '', $FilePath);

	// if there is http:// $FilePath will return its bas path
	$dir_array = explode("/",$FilePath);

	//split the FilePath
	$max_index = count($dir_array) ;
	$i = 0;
	$path = $_SERVER['DOCUMENT_ROOT']."/";

	while( $i < $max_index ){
		$path .= "/".$dir_array[$i];
		$path = str_replace("//","/",$path);

		if( $dir_array[$i] == "" ){
			$i ++ ;
			continue;
		}

		if( substr_count($path, '&') ) return true;
		if( substr_count($path, '?') ) return true;
		if( !substr_count($path, '.htm') ){
			//if is a directory
			if( !file_exists( $path ) ){
				@mkdir( $path, 0777);
				@chmod( $path, 0777 );
			}
		}
		$i ++;
	}

    if( is_dir( $path ) ){
		$path = $path."/index.html";
	}
	if ( !strstr( strtolower($Content), '</html>' ) ) return;

	//if sql error ignore...
	$fp = @fopen( $path , "w+" );
	if( $fp ){
		@chmod($path, 0666 ) ;
		@flock($fp ,LOCK_EX );

		// write the file。
		fwrite( $fp , $Content );
		@flock($fp, LOCK_UN);
		fclose($fp);
	 }
}

/* read the content from output buffer */
$is_buffer = false;

//如果是伪静态形式的开始检测判断，是否生成html
if( substr_count($_SERVER['REQUEST_URI'], '.htm') || ( SCRIPT_URI == wphcSiteHome))
{

	if(  substr_count($_SERVER['REQUEST_URI'], '?'))  $is_buffer = false;
	//if(  substr_count($_SERVER['REQUEST_URI'], '../'))  $is_buffer = false;
	
	//如果文件不存在，则设置生成状态为真，准备生成
	if(!file_exists($_SERVER['REQUEST_URI']))
	{ 
		$is_buffer = true;
	}

}

		
//如果是目录，写入生成状态为真，准备后面生成html
	if(substr($_SERVER['REQUEST_URI'], -1)=="/") { 	
	$is_buffer = true;	
	//echo substr($_SERVER['REQUEST_URI'], -1);
	/*登陆判断，太诡异，已删除
	if( strlen( $_COOKIE['wordpress_logged_in_'.COOKIEHASH] ) < 4 ){
		$is_buffer = true;
	}
	*/
	}		

	
	
if( $is_buffer ){
//如果状态为真的话，调用wphc_cache_ob_callback函数开始生成
	ob_start('wphc_cache_ob_callback');
	register_shutdown_function('wphc_cache_shutdown_callback');//PHP提供register_shutdown_function()这个函数，能够在脚本终止前回调注册的函数, 这里调用清除缓冲区
}

function wphc_cache_ob_callback($buffer){

	$buffer = preg_replace('/(<\s*input[^>]+?(name=["\']author[\'"])[^>]+?value=(["\']))([^"\']+?)\3/i', '\1\3', $buffer);

	$buffer = preg_replace('/(<\s*input[^>]+?value=)([\'"])[^\'"]+\2([^>]+?name=[\'"]author[\'"])/i', '\1""\3', $buffer);
	
	$buffer = preg_replace('/(<\s*input[^>]+?(name=["\']url[\'"])[^>]+?value=(["\']))([^"\']+?)\3/i', '\1\3', $buffer);

	$buffer = preg_replace('/(<\s*input[^>]+?value=)([\'"])[^\'"]+\2([^>]+?name=[\'"]url[\'"])/i', '\1""\3', $buffer);
	
	$buffer = preg_replace('/(<\s*input[^>]+?(name=["\']email[\'"])[^>]+?value=(["\']))([^"\']+?)\3/i', '\1\3', $buffer);

	$buffer = preg_replace('/(<\s*input[^>]+?value=)([\'"])[^\'"]+\2([^>]+?name=[\'"]email[\'"])/i', '\1""\3', $buffer);

	if( !substr_count($buffer, '<!--wphc-html-cache-safe-tag-->') ) return  $buffer;//不存在标记的不生成
	if( substr_count($buffer, 'post_password') > 0 ) return  $buffer;//to check if post password protected 
	$wppasscookie = "wp-postpass_".COOKIEHASH;
	if( strlen( $_COOKIE[$wppasscookie] ) > 0 ) return  $buffer;//to check if post password protected 

	
	
	
	elseif( SCRIPT_URI == wphcSiteHome) {// creat homepage
		$fp = @fopen( wphcBlogPath."index.bak" , "w+" );
		if( $fp ){
			@flock($fp ,LOCK_EX );
			// write the file。
			fwrite( $fp , $buffer.wphcMETA );
			@flock($fp, LOCK_UN);
			fclose($fp);
		 }
		if(IS_INDEX)
			@rename(wphcBlogPath."index.bak",wphcBlogPath."index.html");
	}
	elseif(is_404())	{/*如果是404页面的话，不生成*/	}
	else
		WPxueCreateHtmlFile($_SERVER['REQUEST_URI'],$buffer.wphcMETA );
	return $buffer;
}

function wphc_cache_shutdown_callback(){
	ob_end_flush();
	flush();
}

if( !function_exists('DelCacheByUrl') ){
	function DelCacheByUrl($url) {
		$url = wphcBlogPath.str_replace( wphcSiteHome,"",$url );
		$url = str_replace("//","/", $url );
		 if( file_exists( $url )){
			 if( is_dir( $url )) {@unlink( $url."/index.html" );@rmdir($url);}
			 else @unlink( $url );
		 }
	}
}

if( !function_exists('htmlCacheDel') ){
	// create single html
	function htmlCacheDel($post_ID) {
		if( $post_ID == "" ) return true;
		$uri = get_permalink($post_ID);
		DelCacheByUrl($uri );
	}
}

if( !function_exists('htmlCacheDelNb') ){
	// delete nabour posts
	function htmlCacheDelNb($post_ID) {
		if( $post_ID == "" ) return true;

		$uri = get_permalink($post_ID);
		DelCacheByUrl($uri );
		global $wpdb;
		$postRes=$wpdb->get_results("SELECT `ID`  FROM `" . $wpdb->posts . "` WHERE post_status = 'publish'   AND   post_type='post'   AND  ID < ".$post_ID." ORDER BY ID DESC LIMIT 0,1;");
		$uri1 = get_permalink($postRes[0]->ID);
		DelCacheByUrl($uri1 );
		$postRes=$wpdb->get_results("SELECT `ID`  FROM `" . $wpdb->posts . "` WHERE post_status = 'publish'  AND   post_type='post'    AND ID > ".$post_ID."  ORDER BY ID DESC  LIMIT 0,1;");
		if( $postRes[0]->ID != '' ){
			  $uri2  = get_permalink($postRes[0]->ID);
			  DelCacheByUrl($uri2 );
		}
	}
}

//create index.html
if( !function_exists('createIndexHTML') ){
	function createIndexHTML($post_ID){
		if( $post_ID == "" ) return true;
		//[menghao]@rename(ABSPATH."index.html",ABSPATH."index.bak");
		@rename(wphcBlogPath."index.html",wphcBlogPath."index.bak");//[menghao]

	}
}

if(!function_exists("htmlCacheDel_reg_admin")) {
	/**
	* Add the options page in the admin menu
	*/
	function htmlCacheDel_reg_admin() {
		if (function_exists('add_options_page')) {
			add_options_page('html-cache-creator', 'WP Html Cache',8, basename(__FILE__), 'wphcHtmlOption');
			//add_options_page($page_title, $menu_title, $access_level, $file).
		}
	}
}

add_action('admin_menu', 'htmlCacheDel_reg_admin');

if(!function_exists("wphcHtmlOption")) {
function wphcHtmlOption(){
	do_wphc_html_cache_action();
?>
	<div class="wrap" style="padding:10px 0 0 10px;text-align:left">
	<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
	<p>
	<?php _e("Click the button bellow to delete all the html cache files","wphcbeta");?></p>
	<p><?php _e("Note:this will Not  delete data from your databases","wphcbeta");?></p>
	<p><?php _e("If you want to rebuild all cache files, you should delete them first,and then the cache files will be built when post or page first visited","wphcbeta");?></p>

	<p><b><?php _e("specify a post ID or Title to to delete the related cache file","wphcbeta");?></b> <input type="text" id="cache_id" name="cache_id" value="" /> <?php _e("Leave blank if you want to delete all caches","wphcbeta");?></p>
	<p><input type="submit" value="<?php _e("Delete Html Cache files","wphcbeta");?>" id="htmlCacheDelbt" name="htmlCacheDelbt" onClick="return checkcacheinput(); " />
	</form>
 
	</div>

	<SCRIPT LANGUAGE="JavaScript">
	<!--
		function checkcacheinput(){
		document.getElementById('htmlCacheDelbt').value = 'Please Wait...';
		return true;
	}
	//-->
	</SCRIPT>
<?php
	}
}
/*
end of get url
*/
// deal with rebuild or delete
function do_wphc_html_cache_action(){
	if( !empty($_POST['htmlCacheDelbt']) ){
		@rename(wphcBlogPath."index.html",wphcBlogPath."index.bak");
		@chmod( wphcBlogPath."index.bak", 0666 );
		global $wpdb;
		if( $_POST['cache_id'] * 1 > 0 ){
			//delete cache by id
			 DelCacheByUrl(get_permalink($_POST['cache_id']));
			 $msg = __('the post cache was deleted successfully: ID=','wphcbeta').$_POST['cache_id'];
		}
		else if( strlen($_POST['cache_id']) > 2  ){
			$postRes=$wpdb->get_results("SELECT `ID`  FROM `" . $wpdb->posts . "` WHERE post_title like '%".$_POST['cache_id']."%' LIMIT 0,1 ");
			DelCacheByUrl( get_permalink( $postRes[0]->ID ) );
			$msg = __('the post cache was deleted successfully: Title=','wphcbeta').$_POST['cache_id'];
		}
		else{
		$postRes=$wpdb->get_results("SELECT `ID`  FROM `" . $wpdb->posts . "` WHERE post_status = 'publish' AND ( post_type='post' OR  post_type='page' )  ORDER BY post_modified DESC ");
		foreach($postRes as $post) {
			DelCacheByUrl(get_permalink($post->ID));
			}
			$msg = __('HTML Caches were deleted successfully','wphcbeta');
		}
	}
	if($msg)
	echo '<div class="updated"><strong><p>'.$msg.'</p></strong></div>';
}
$is_add_comment_is = true;
/*
 * with ajax comments
 */
 
 
if ( !function_exists("wphc_comments_js") ){
	function wphc_comments_js($postID){
		global $is_add_comment_is;
		if( $is_add_comment_is ){
			$is_add_comment_is = false;
		?>
		
		<script language="JavaScript" type="text/javascript" src="<?php echo wphcSiteHome;?>/wp-content/plugins/wphc-html-cache/common.js.php?hash=<?php echo COOKIEHASH;?>"></script>
		<script language="JavaScript" type="text/javascript">
		//<![CDATA[
		var hash = "<?php echo COOKIEHASH;?>";
		var author_cookie = "comment_author_" + hash;
		var email_cookie = "comment_author_email_" + hash;
		var url_cookie = "comment_author_url_" + hash; 
		var adminmail = "<?php  echo str_replace('@','{_}',get_option('admin_email'));?>";
		var adminurl = "<?php  echo  get_option('siteurl') ;?>";
		setCommForm();
		//]]>
		</script>
	<?php
		}
	}
}


function wphcSafeTag(){
	if   ( is_single() || is_category()	||	is_tag()	||	(is_home() && IS_INDEX) )  {
		echo "<!--wphc-html-cache-safe-tag-->";
	}
}
function clearCommentHistory(){
global $comment_author_url,$comment_author_email,$comment_author;
$comment_author_url='';
$comment_author_email='';
$comment_author='';
}
//add_action('comments_array','clearCommentHistory');
add_action('get_footer', 'wphcSafeTag');
add_action('comment_form', 'wphc_comments_js');

/* end of ajaxcomments*/
if(IS_INDEX)	add_action('publish_post', 'createIndexHTML');
add_action('publish_post', 'htmlCacheDelNb');

if(IS_INDEX)	add_action('delete_post', 'createIndexHTML');
add_action('delete_post', 'htmlCacheDelNb');

//if comments add
add_action('edit_post', 'htmlCacheDel');
if(IS_INDEX) add_action('edit_post', 'createIndexHTML');

//删除文件夹
function deldir($dir) {
  $dh=opendir($dir);
  while ($file=readdir($dh)) {
    if($file!="." && $file!="..") {
      $fullpath=$dir."/".$file;
      if(!is_dir($fullpath)) {
          unlink($fullpath);
      } else {
          deldir($fullpath);
      }
    }
  }

  closedir($dh);
  
  if(rmdir($dir)) {
    return true;
  } else {
    return false;
  }
} 
//删除栏目html文件夹，等待更新, 通过url参数更新 例如：查看 /index.php?del=c  删除/index.php?del=c&ok=1
if($_GET['del']=='c') {//c为目录页缓存文件夹
echo get_option('category_base');
if($_GET['ok']=='1'){@deldir(get_option('category_base'));exit( "删除成功");}
}

elseif($_GET['del']=='t') {//t为标签页缓存文件夹
echo get_option('tag_base');
	if($_GET['ok']=='1'){@deldir(get_option('tag_base'));exit( "删除成功");}
}

elseif($_GET['del']) {//指定文件夹
echo $_GET['del'];
	if($_GET['ok']=='1'){@deldir($_GET['del']);exit( "删除成功");}
}
?>