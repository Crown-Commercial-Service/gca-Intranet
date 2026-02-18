<?php

global $error, $password_protected_errors, $is_iphone;
$options = get_option( ASENHA_SLUG_U, array() );
$password_field_label = __( 'Password', 'admin-site-enhancements' );
$button_label = __( 'View Content', 'admin-site-enhancements' );
/**
 * WP Shake JS
 */
if ( !function_exists( 'wp_shake_js' ) ) {
    function wp_shake_js() {
        if ( isset( $is_iphone ) ) {
            if ( $is_iphone ) {
                return;
            }
        }
        ?>
		<script>
		addLoadEvent = function(func){if(typeof jQuery!="undefined")jQuery(document).ready(func);else if(typeof wpOnload!='function'){wpOnload=func;}else{var oldonload=wpOnload;wpOnload=function(){oldonload();func();}}};
		function s(id,pos){g(id).left=pos+'px';}
		function g(id){return document.getElementById(id).style;}
		function shake(id,a,d){c=a.shift();s(id,c);if(a.length>0){setTimeout(function(){shake(id,a,d);},d);}else{try{g(id).position='static';wp_attempt_focus();}catch(e){}}}
		addLoadEvent(function(){ var p=new Array(15,30,15,0,-15,-30,-15,0);p=p.concat(p.concat(p));var i=document.forms[0].id;g(i).position='relative';shake(i,p,20);});
		</script>
		<?php 
    }

}
nocache_headers();
header( 'Content-Type: ' . get_bloginfo( 'html_type' ) . '; charset=' . get_bloginfo( 'charset' ) );
// Maybe show error message above login form
$shake_error_codes = array('empty_password', 'incorrect_password');
if ( $password_protected_errors->get_error_code() && in_array( $password_protected_errors->get_error_code(), $shake_error_codes ) ) {
    add_action( 'asenha_password_protection_login_head', 'wp_shake_js', 12 );
}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" <?php 
language_attributes();
?>>
<head>
	<meta http-equiv="Content-Type" content="<?php 
bloginfo( 'html_type' );
?>; charset=<?php 
bloginfo( 'charset' );
?>" />
	<meta name="viewport" content="width=device-width" />
	<meta name="robots" content="noindex">
	<title><?php 
bloginfo( 'name' );
?></title>
	<?php 
wp_admin_css( 'login', true );
do_action( 'asenha_password_protection_login_head' );
?>
	    <style type="text/css" id="protected-page-login-style">
	    	#login_error {
				box-sizing: border-box;
	    		width: 287px;
				border-left: 4px solid #d63638;
				padding: 12px;
				margin-top: 20px;
				margin-bottom: 0;
				background-color: #fff;
				box-shadow: 0 1px 1px 0 rgba(0, 0, 0, .1);
				word-wrap: break-word;
	    		color: #3c434a;
	    	}
	    </style>
		<?php 
?>
</head>
<body class="login protected-page-login wp-core-ui">

<div id="login">
	<?php 
do_action( 'asenha_password_protection_error_messages' );
?>
	<form name="loginform" id="loginform" action="<?php 
echo esc_url( add_query_arg( 'protected-page', 'view', home_url( '/' ) ) );
?>" method="post">
		<label for="protected_page_pwd"><?php 
echo esc_html( $password_field_label );
?></label>
		<input type="password" name="protected_page_pwd" id="protected_page_pwd" class="input" value="" size="20" />
		<p class="submit">
			<input type="submit" name="wp-submit" id="wp-submit" class="button button-primary button-large" value="<?php 
echo esc_attr( $button_label );
?>" />
			<input type="hidden" name="protected-page" value="view" />
			<input type="hidden" name="source" value="<?php 
echo esc_attr( ( !empty( $_REQUEST['source'] ) ? $_REQUEST['source'] : '' ) );
?>" />
		</p>
	</form>
</div>

<?php 
do_action( 'login_footer' );
?>

</body>
</html>