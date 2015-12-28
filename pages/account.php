<?
if(!$user) return;
function localdate($date) { return preg_replace('/(\d+)\D+(\d+)\D+(\d+)(.+)/',"$3.$2.$1$4",$date); }
function getbit($len) { return bindec(str_pad($len ? 1 : 0, $len, 0)); }
function getint($bit) { return strlen($bit ? decbin($bit) : ''); }
$admin = $user['priv'] > 3;
$level = array(
	'deactivated',
	'read-only',
	'editor',
	'admin'
);
if(count($_POST) && $admin) { // todo: move to header
	$query = 'UPDATE '.TBL_USR.' SET priv = CASE id';
	foreach($_POST as $key => $val)
		$query .= ' WHEN '.substr($key, 1).' THEN '.$val;
	$res = $mysqli->query($query.' END');
	showAlert($mysqli->affected_rows.' users updated successfully', $res);
}
?>
		<div class="row"><span>Username:</span><div><?=$user['name']?></div></div>
		<div class="row"><span>Group:</span><div><?=$level[getint($user['priv'])]?></div></div>
		<div class="row"><span>E-mail addr:</span><div><?=$user['email']?></div></div>
		<div class="row"><span>Date created:</span><div><?=localdate($user['created'])?></div></div>
		<div class="row"><span>Last login:</span><div><?=localdate($user['last_login'])?></div></div>
		<div class="row"><span>
			<form action="home" method="get">
				<input type="hidden" name="action" value="logout">
				<button class="btn brd">Logout</button>
			</form>
		</span><div></div></div>
		<? if($admin) { ?>
		<div class="admin">
			<h3>Administration</h3>
			<form action="account" method="post">
			<div class="row"><span>User</span><div>Permissions</div></div>
			<?
			//array_pop($level);
			$res = $mysqli->query('SELECT * FROM '.TBL_USR);
			while($row = $res->fetch_assoc()) {
				echo '<div class="row"><span>'.$row['name'].'</span><div>';
				echo '<select name="u'.$row['id'].'">';
				foreach($level as $key => $val) {
					$sel = getbit($key) == $row['priv'] ? ' selected' : '';
					echo '<option value="'.getbit($key).'"'.$sel.'>'.ucfirst($val).'</option>';
				}
				echo '</select></div></div>';
			} ?>
			<div class="row">
				<input type="hidden" id="ignore" value="1" checked>
				<button class="btn btn-blu">Save</button>
			</div>
			</form>
		</div>
		<? } ?>