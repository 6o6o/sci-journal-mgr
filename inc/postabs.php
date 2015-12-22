<?
if(!empty($_POST['vol']) && count($_POST) > 2) {
	require_once(INC_DIR.'dbconn.php');
	if($user && $user['priv'] > 1) {
		$aid = $_POST['vol'].'('.$_POST['issue'].'): '.$_POST['page'].'-'.$_POST['end_page'];
		if(isset($_POST['update'])) {
			$res = $db->update(
				$_POST['update'],
				array_intersect_key($_POST, array_flip(array('vol', 'issue', 'page'))),
				TBL_CON
			);
			showAlert(
				'Record <i>'.implode(', ',array_keys($_POST['update'])).'</i> in '.
				$aid.($res ? ' successfully updated' : ' failed updating'),
				$res
			);
		} else {
			$res = $db->insert($_POST, TBL_CON);
			showAlert($res ? 'Article '.$aid.' added successfully.' : 'Failed adding article '.$aid.'. Duplicate entry?', $res);
		}
	} else showAlert('You do not have enough privileges to perform the action');
}