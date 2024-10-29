<?php
/*
Plugin Name: AjaxToDo
Plugin URI: http://maiux.com/wordpress/ajax-to-do-for-wordpress
Description: To-do's for administration panel via ajax.
Version: 0.1.3
Author: Fabio Maione
Author URI: http://maiux.com
*/

define('AJAXTODO_BASE_URL',plugin_dir_url(__FILE__));
// Function that output's the contents of the dashboard widget
function ajaxtodo_widget_function() {

	//delete_option('ajaxtodo_todos');

	$todos=get_option('ajaxtodo_todos');
/*	if (!$todos) {
		$todos=array(array('done' => 'no','important' => 'no', 'text' =>'To-do example'));
		update_option('ajaxtodo_todos',$todos);
	}*/
	echo '<form id="ajaxtodo_todo_form"><ul id="ajaxtodo_todo_list">';
	ajaxtodo_populate_list($todos);
	echo '</ul>';
	echo '<img border="0" id="ajaxtodo_todo_image_updating" src="'.AJAXTODO_BASE_URL.'wpspin_light.gif" style="visibility: hidden;" /><input type="text" id="ajaxtodo_new_todo" style="width:87%" /><input type="button" id="ajaxtodo_button_add" value="+"></form>';
}

// Function that beeng used in the action hook
function add_ajaxtodo_widget() {
	
	wp_add_dashboard_widget('ajaxtodo_widget', 'To Do', 'ajaxtodo_widget_function');
	
}

function ajaxtodo_populate_list($todos) {
	
	if (is_array($todos)) {
	
		foreach($todos as $todo_id => $todo) {
			
			echo '<li id="ajaxtodo_todo_'.$todo_id.'" style="font-weight:'.($todo['important']=='yes' ? 'bold':'normal').'">';
			
			echo '<input type="checkbox" id="ajaxtodo_todo_checkbox_done_'.$todo_id.'" class="ajaxtodo_todo_checkbox_done" '.($todo['done']=='yes' ? 'checked ':'').' />';
			
			echo '<img id="ajaxtodo_todo_image_important_'.$todo_id.'" style="vertical-align:middle;cursor:pointer;margin:2px;" src="'.AJAXTODO_BASE_URL.($todo['important']=='yes' ? 'important_enabled.png':'important_disabled.png').'" class="ajaxtodo_todo_image_important" />';
			
			echo '<img id="ajaxtodo_todo_image_delete_'.$todo_id.'" style="vertical-align:middle;cursor:pointer;margin:2px;" src="'.AJAXTODO_BASE_URL.'delete.png" class="ajaxtodo_todo_image_delete" />';
			
			echo '<span id="ajaxtodo_todo_text_'.$todo_id.'" style="text-decoration:'.($todo['done']=='yes' ? 'line-through':'none').'">'.$todo['text'].'</span>';
			
			echo '</li>';
		}
		
	}
		
}

function ajaxtodo_add_todo() {
	
	global $wpdb; 
	
	$todo=array('done' => 'no', 'important' => 'no', 'text' => $_POST["todo"]);
	
	$todos=get_option('ajaxtodo_todos');
	
	$todos[]=$todo;
		
	update_option('ajaxtodo_todos',$todos);
	
	die(ajaxtodo_populate_list($todos));
	
}

function ajaxtodo_update_todo_toggle_done() {
	
	global $wpdb; 
	
	$id=$_POST['id'];
	
	$todos=get_option('ajaxtodo_todos');
	
	$todo=$todos[$id];
	
	$todo['done']=($todo['done']=="no" ? "yes":"no");
			
	$todos[$id]=$todo;
			
	update_option('ajaxtodo_todos',$todos);
			
	die($todo['done']);
			
}

function ajaxtodo_update_todo_toggle_important() {
	
	global $wpdb; 
	
	$id=$_POST['id'];
	
	$todos=get_option('ajaxtodo_todos');
	
	$todo=$todos[$id];
	
	$todo['important']=($todo['important']=="no" ? "yes":"no");
			
	$todos[$id]=$todo;
			
	update_option('ajaxtodo_todos',$todos);
			
	die($todo['important']);
	
}

function ajaxtodo_update_todo_delete() {
	
	global $wpdb; 
	
	$id=$_POST['id'];
	
	$todos=get_option('ajaxtodo_todos');
	
	unset($todos[$id]);
	
	//print_r($todos);
	
	update_option('ajaxtodo_todos',$todos);
	
	die(ajaxtodo_populate_list($todos));
	
}

function ajaxtodo_js() {
?>
<script type="text/javascript" >
jQuery(document).ready(function($) {
	
	$('#ajaxtodo_new_todo').keypress(function(event) {
		
		if (event.keyCode=='13') {
			event.preventDefault();
			$('#ajaxtodo_button_add').click();
		}
		
	});
	
	updateList('_init_');

	function updateList(content) {

		if (content!="_init_")
			$('#ajaxtodo_todo_list').html(content);

		$('#ajaxtodo_button_add').click(function() {
			
			var mytodo = $('#ajaxtodo_new_todo').val();
			
			if (mytodo!='') {
			
				$('#ajaxtodo_todo_image_updating').css('visibility','visible');
				
				var data = {
					action: 'ajaxtodo_add_todo',
					todo: mytodo
				};
				
				jQuery.post(ajaxurl, data, function(response) {
	
					updateList(response);
	
					$('#ajaxtodo_new_todo').val('');
	
					$('#ajaxtodo_todo_image_updating').css('visibility','hidden');
				
				});
				
			};
			
		});
	
		$('.ajaxtodo_todo_checkbox_done').click(function() {
			
			$('#ajaxtodo_todo_image_updating').css('visibility','visible');

			var myid=this.id.replace('ajaxtodo_todo_checkbox_done_','');
			var data = {
				action: 'ajaxtodo_update_todo_toggle_done',
				id: myid
			};
			jQuery.post(ajaxurl, data, function(response) {
				//alert('Risposta del server: ' + response);
				if (response=="yes") {
					$('#ajaxtodo_todo_text_'+myid).css('text-decoration','line-through');
				} else if (response=="no") {
					$('#ajaxtodo_todo_text_'+myid).css('text-decoration','none');
				} else {
					alert(response);
				}

				$('#ajaxtodo_todo_image_updating').css('visibility','hidden');
			});
			
		});
	
		$('.ajaxtodo_todo_image_important').click(function() {
			
			$('#ajaxtodo_todo_image_updating').css('visibility','visible');

			var myid= this.id.replace('ajaxtodo_todo_image_important_','');
			var data = {
				action: 'ajaxtodo_update_todo_toggle_important',
				id: myid
			};
			jQuery.post(ajaxurl, data, function(response) {
				//alert('Risposta del server: ' + response);
				if (response=="yes") {
					$('#ajaxtodo_todo_text_'+myid).css('font-weight','bold');
					var mysrc=$('#ajaxtodo_todo_image_important_'+myid).attr('src');
					$('#ajaxtodo_todo_image_important_'+myid).attr('src',mysrc.replace('disabled','enabled'));
				} else if (response=="no") {
					$('#ajaxtodo_todo_text_'+myid).css('font-weight','normal');
					var mysrc=$('#ajaxtodo_todo_image_important_'+myid).attr('src');
					$('#ajaxtodo_todo_image_important_'+myid).attr('src',mysrc.replace('enabled','disabled'));
				} else {
					alert(response);
				}
	
				$('#ajaxtodo_todo_image_updating').css('visibility','hidden');
		});
			
		});
	
		$('.ajaxtodo_todo_image_delete').click(function() {
			
			$('#ajaxtodo_todo_image_updating').css('visibility','visible');

			var myid = this.id.replace('ajaxtodo_todo_image_delete_','');
			var data = {
				action: 'ajaxtodo_update_todo_delete',
				id: myid
			};
			jQuery.post(ajaxurl, data, function(response) {
				updateList(response);

				$('#ajaxtodo_todo_image_updating').css('visibility','hidden');
			});
			
		});
	}
});
</script>
<?php
}

add_action('wp_dashboard_setup', 'add_ajaxtodo_widget' );
add_action('wp_ajax_ajaxtodo_add_todo', 'ajaxtodo_add_todo');
add_action('wp_ajax_ajaxtodo_update_todo_toggle_done', 'ajaxtodo_update_todo_toggle_done');
add_action('wp_ajax_ajaxtodo_update_todo_toggle_important', 'ajaxtodo_update_todo_toggle_important');
add_action('wp_ajax_ajaxtodo_update_todo_delete', 'ajaxtodo_update_todo_delete');
add_action('admin_head', 'ajaxtodo_js');
?>
