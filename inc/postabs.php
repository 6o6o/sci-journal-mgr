<?
if(!empty($_POST['vol']) && count($_POST) > 2) {
	require_once(INC_DIR.'dbconn.php');
	if($user && $user['priv'] > 1) {
		if(isset($_POST['update'], $_POST['pg'])) {
			$res = $db->update(
				$_POST['update'],
				array('vol' => $_POST['vol'], 'start_page' => $_POST['pg']),
				TBL_CON
			);
			showAlert(
				'Record <i>'.implode(', ',array_keys($_POST['update'])).'</i> in '.
				$_POST['vol'].':'.$_POST['pg'].($res ? ' successfully updated' : ' failed updating'),
				$res
			);
		} else {
			$res = $db->insert($_POST, TBL_CON);
			showAlert($res ? 'Abstract added successfully.' : 'Failed adding an abstract. Duplicate entry?', $res);
		}
	} else showAlert('You do not have enough privileges to perform the action');
}