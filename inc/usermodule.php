<?
$timespan = 604800; // week
$host = $_SERVER['SERVER_NAME'];
function mkPass($s,$p) { return hash('sha256',$s.$p); }
function genRand() { return bin2hex(openssl_random_pseudo_bytes(32)); } // mcrypt_create_iv
function mailuser($a) { mail($a[0], $a[1], implode("\r\n", $a[2]), 'From: do-not-reply@'.$a[3]); }

if(!empty($_POST['username'])) {
	require_once(INC_DIR.'dbconn.php');
	if(count($_POST) > 3) { // user creation
		if(preg_match("/^[\w\-.']{3,30}$/", $_POST['username'])) {
		if(preg_match("/\w+@[\w-]+\.\w{2,}/", $_POST['email'])) {
		if(strlen($_POST['pass']) > 5 && $_POST['pass'] == $_POST['passconf']) {
			$mail = $_POST['email'];
			$salt = genRand();
			$pass = mkPass($salt,$_POST['pass']);
			$token = $db->mkToken();
			$res = $db->insert(array(
				'created' => $db->prop('NOW()'),
				'name' => $_POST['username'],
				'email' => $mail,
				'pass' => $pass,
				'salt' => $salt,
				'token' => $token
			));
			if($res) {
				$subj = "Welcome to the members of $host, ".$_POST['username'];
				$msg = array(
					'To activate your new account please follow this link:',
					'http://'.$host.'/login?token='.$token,
					'',
					'Best wishes,',
					$host
				);
				mailuser(array($_POST['email'], $subj, $msg, $host));
				showAlert("Account created successfully. An e-mail has been sent to $mail with activation code.", $res);
			} else showAlert(ucfirst(preg_replace("/.+'(.+)'.+'(.+)'/", '$2 $1 is already registered', $mysqli->error)));
		} else showAlert('Passwords must match and be over 5 symbols long');
		} else showAlert('Not a valid e-mail');
		} else showAlert('Username must be 3 to 30 <em>alphanumeric</em> (or ._-\') characters long');
	} else { // user authorization
		$expr = 'TIMESTAMPDIFF(SECOND, last_login, NOW()) as elapsed';
		$user = $db->getRow(array('name' => $_POST['username']), TBL_USR, $expr);
		if($user) {
			if($user['priv']) {
				$pass = mkPass($user['salt'],$_POST['pass']);
				if($pass == $user['pass']) {
					$authtoken = $user['elapsed'] > $timespan ? $db->mkToken() : $user['token'];
					setcookie('token', $authtoken, time() + $timespan);
					$db->update(
						array('token' => $authtoken, 'last_login' => $db->prop('NOW()')),
						$user['id']
					);
					showAlert('Welcome back, '.$user['name'].'!',1);
				} else showAlert('Wrong info provided');
			} else showAlert('User not activated');
		} else showAlert('Wrong info provided');
	}
} elseif(!empty($_GET['token'])) { // user activation
	require_once(INC_DIR.'dbconn.php');
	if($user = $db->getRow($_GET['token'])) {
		if(!$user['priv']) {
			$res = $db->update(array('priv' => 1), $user['id']);
			if($res) showAlert('User "'.$user['name'].'" successfully activated',1);
		} else showAlert('User already activated');
	} else showAlert('User not found');
} elseif(!empty($_GET['action']) && $_GET['action'] == 'logout') {
	setcookie('token','',time()-1);
	unset($_COOKIE['token']);
}

$user = null;
if(!isset($authtoken) && isset($_COOKIE['token'])) $authtoken = $_COOKIE['token'];
if(isset($authtoken)) {
	require_once(INC_DIR.'dbconn.php');
	$user = $db->getRow($authtoken);
	if($user && $user['priv']) {
		$assist = array(
			'newabs' => '+ Add new abstract',
			'tools' => '',
			'account' => $user['name']
		);
	}
}