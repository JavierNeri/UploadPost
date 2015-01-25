<?php
/*
Plugin Name: UploadPost
Plugin URI: http://www.ndblogs.com
Description: Permitimos enviar posts a los usuarios
Author: Javier Neri
Version: 1.0
Author URI: http://www.matamorosweb.com
*/


function save_form() {
	global $wpdb;
	if (isset($_POST['contenido']) && isset($_POST['titulo']) && isset($_POST['user']) && get_option('col_userID') != '') {

		// Controlamos la URL
		if (isset($_POST['url']) && $_POST['url'] != '') $user = '<a href="'.apply_filters('pre_comment_author_url', $_POST['url']).'">'.sanitize_user($_POST['user']).'</a>';
		else $user = sanitize_user($_POST['user']);
		
		// Insertamos informacion del colaborador
		$contenido = $_POST['contenido'].'<p class="autor">'.get_option('col_text').': '.$user.'</p>';

		$post_author = get_option('col_userID');
		$post_date = '';
		$post_date_gmt = '';
		$post_content = apply_filters('content_save_pre',   $contenido);
		$post_content_filtered = '';
		$post_title = apply_filters('title_save_pre', $_POST['titulo']);
		$post_excerpt = '';
		$post_status = 'draft';
		$post_type = 'post';
		$comment_status = get_option('default_comment_status');
		$ping_status = get_option('default_ping_status');
		$post_password = '';
		$post_name = '';
		$to_ping = '';
		$pinged = '';
		$post_date = '';
		$post_date_gmt = '';
		$post_parent = '0';
		$menu_order = '0';
		$post_mime_type = '';
		
		// Insertamos el post.		
		$wpdb->query(
				"INSERT IGNORE INTO $wpdb->posts
				(post_author, post_date, post_date_gmt, post_content, post_content_filtered, post_title, post_excerpt,  post_status, post_type, comment_status, ping_status, post_password, post_name, to_ping, pinged, post_modified, post_modified_gmt, post_parent, menu_order, post_mime_type)
				VALUES
				('$post_author', '$post_date', '$post_date_gmt', '$post_content', '$post_content_filtered', '$post_title', '$post_excerpt', '$post_status', '$post_type', '$comment_status', '$ping_status', '$post_password', '$post_name', '$to_ping', '$pinged', '$post_date', '$post_date_gmt', '$post_parent', '$menu_order', '$post_mime_type')");


	if (get_option('col_mail') == '1') {
		//Enviamos un mail a todos los administradores
		$cabeceras  = 'MIME-Version: 1.0' . "\r\n";
		$cabeceras .= 'Content-type: text/html; charset=utf-8' . "\r\n";
		$cabeceras .= 'From:' .get_option('admin_email'). "\r\n";
		//Datos personalizados del mail
		$to = get_option('col_email_to');
		$asunto = get_option('col_asunto');
		$post_ID = $wpdb->insert_id;
		$home = get_option('home');
		$options = '<p>'.__("Opciones").'<a href="'.$home.'/wp-admin/post.php?action=edit&post='.$post_ID.'" >'.__("Ir al POST").'</a>| <a href="'.$home.'?p='.$post_ID.'&preview=true">'.__("Preview").'</a></p>';
		
		mail($to,$asunto.': '.$post_title,$post_content.$options, $cabeceras);
	}
	return '<p class="info">'.get_option('col_OK').'</p>';
			} else {
	return '<p class="info">'.get_option('col_KO').'</p>';
	}
}


function uploadpost_form($content = '') {

	$msg = '';
	if (isset($_POST['colabora']) && strstr($content,'<!--colabora-->'))
			$msg = save_form();

$salida = $msg.'
<script type="text/javascript" src="http://js.nicedit.com/nicEdit-latest.js"></script> <script type="text/javascript">
//<![CDATA[
bkLib.onDomLoaded(function() { nicEditors.allTextAreas() });
//]]>
</script>
<form action="" method="post" id="colabora">
<fieldset style="border:none">
	<label for="titulo">
	'.__('T&iacute;tulo').': 
	<input name="titulo" id="titulo" size="75" type="text" />	
	</label>
</fieldset>
<fieldset style="border:none">
	<label for="contenido">
	'.__('Contenido').': 
	<textarea name="contenido" id="contenido" cols="60" rows="30"></textarea>
	</label>
</fieldset>
<fieldset style="border:none">
	<label for="user">
	'.__('Nombre').':
	<input name="user" id="user" size="28" type="text" />	
	</label>
	<label for="url">
	'.__('Url').':
	<input name="url" id="url" size="28" type="text" />	
	</label>
</fieldset>
<fieldset style="border:none">
	<p style="text-align:center;"><input type="submit" name="colabora" value="'.__('Enviar').'" /></p>
</fieldset>
</form>';
	return str_replace("<!--colabora-->",$salida,$content);
}

/* PANEL DE ADMINISTRACION */

function uploadpost_admin_menu(){
	if (get_option('col_text') == '') {
		update_option('col_text', "Colaborador");
		update_option('col_mail', 1);
		update_option('col_email_to', "admin@tusitio.com");
		update_option('col_asunto', "Nueva colaboracion");
		update_option('col_userID', 1);
		update_option('col_OK', "Post enviado, gracias por tu colaboración");
		update_option('col_KO', "Ha ocurrido un error, vuelve a intentarlo.");
	}
	add_options_page('Colabora', 'Colabora', 9, __FILE__, 'uploadpost_admin') ;
}
add_action('admin_menu', 'uploadpost_admin_menu'); 

function uploadpost_admin() {
	if ($_POST['action'] == 'actualiza') {
		update_option('col_text', $_POST['col_text']);
		update_option('col_mail', $_POST['col_mail']);
		update_option('col_email_to', $_POST['col_email_to']);
		update_option('col_asunto', $_POST['col_asunto']);
		update_option('col_userID', $_POST['col_userID']);
		update_option('col_OK', $_POST['col_OK']);
		update_option('col_KO', $_POST['col_KO']);
		
		
	}
$col_text = stripslashes(get_option('col_text'));
$col_mail = (get_option('col_mail') == 1)?'checked="checked"':"";
$col_email_to = stripslashes(get_option('col_email_to'));
$col_asunto = stripslashes(get_option('col_asunto'));
$col_userID = stripslashes(get_option('col_userID'));
$col_OK = stripslashes(get_option('col_OK'));
$col_KO = stripslashes(get_option('col_KO'));

?>
<style type="text/css">
#colabora input[type="text"] { width:98%; margin:.6em; }
#colabora label { display:block; margin:.5em;}
</style>
<div class="wrap">
  <h2><?php _e('Configuraci&oacute;n del plugin Colabora'); ?> </h2>
  <p>
  	<?php _e('Colabora, es un plugin que te permite abrir las puertas a tus usuarios y permitirles que envien artículos directamente desde tu página. Lo podrán hacer sin tener que estár registrado en el blog. Todos los artículos se almacenarán como borradores del usuario definido como recipiente. En este estado podrás editarlos, revisarlos y posteriormente si crees conveniente publicarlo.<br />
	Para que esto funcione correctamente debes configurar las siguienes opciones antes de empezar a usarlo.');?>
  </p>
  <form id="colabora" name="form1" method="post" action="">
        <input type="hidden" name="action" value="actualiza" />
		<fieldset>
		<label for="col_text">
			<?php _e("Introduce el nombre que aparecerá al lado del colaborador que envie el comentario"); ?>: <input name="col_text" type="text" value="<?=$col_text?>" />
		</label>
			<p style="background-color:#CCCCCC;"><strong>Ejemplo: </strong><br />
			<em>Colaborador</em>: <a href="">Usuario</a>
		</p>
		<label for="col_mail">
		<?php _e("¿Activar envio de mail en cada colaboración?")?> <input name="col_mail" type="checkbox" <?=$col_mail?> value="1" />
		</label>
		<label for="col_email_to">
		<?php _e("Mail al que llegarán las notificaciones de colaboraciones"); ?> <input name="col_email_to" type="text" value="<?=$col_email_to?>" />
		</label>
		<label for="col_asunto">
		<?php _e("Asunto que se añadirá al título enviado por el colaborador");?>	<input name="col_asunto" type="text" value="<?=$col_asunto?>" />
		</label>
			<p style="background-color:#CCCCCC;"><strong>Ejemplo: </strong><br />
			Asunto: <em>Nueva colaboración</em>: Esto es una colaboracion
		</p>
		<label for="col_userID">
		<?php _e("ID del usuario recipiente, este usuario será el que aparecerá como autor de la entrada"); ?>.	<input name="col_userID" type="text" value="<?=$col_userID?>" />
		</label>
		<label for="col_OK">
		<?php _e("Mensaje de que todo ha ido correctamente"); ?>.	<input name="col_OK" type="text" value="<?=$col_OK?>" />
		</label>
		<label for="col_KO">
		<?php _e("Mensaje en caso de ocurrir un error"); ?>.	<input name="col_KO" type="text" value="<?=$col_KO?>" />
		</label>
		</fieldset>
		<input type="submit" value="<?php _e('Enviar');?>" />
	</form>
	<h3><?php _e('¿Como funciona?'); ?></h3>
	<p><?php _e('En cualquier página o post, debes añadir el siguiente código para que este se reemplace por el formulario de envio.'); ?></p>
	<pre style="background-color:#EEE;"><code>&lt;!--colabora--&gt;</code></pre>
	<p><?php _e('Una vez hecho esto aparecerá un formulario que podrás modificar estéticamente desde tu propio CSS. Todos los elementos tienen un ID para que puedas personalizarlo a tu gusto.');?></p>
</div>
<?php
}

add_action('the_content','uploadpost_form');  
?>