<?
require_once(INC_DIR.'dbconn.php');
$subj = $db->getAll();
function prVal($c, $tx = '') {
	global $rec;
	$def = ' rows="';
	if($rec) {
		$val = htmlspecialchars($rec[$c]);
		if($tx) {
			$num = count(explode("\r\n", $val));
			$def .= $num > $tx ? $num : $tx;
			$val = $def.'">'.$val;
		} else $val = ' value="'.$val.'"';
	} else if($tx) {
		$val = $def.$tx.'">';
	} else $val = '';
	return ' name="'.$c.'"'.$val;
}
if(!empty($_GET['page'])) {
	$rec = $db->getRow($_GET, TBL_CON);
	if($rec) {
		array_unshift($prefix, 'Edit');
		$rec['doi'] = mkdoi($rec['doi']);
		$rec['pdf'] = getlang($rec['pdf']);
		$_GET = array_slice($rec,0,4);
	}
}

?>
		<div class="addabs">
			<a href="#addauto">Autofill &amp; format text</a>
			<div class="autofill">
				<div contenteditable="true" data-ph="Paste content here..."></div>
				<p>Paste content in the in above field. Each item must be newline-delimited, except for Institute and References - they can span any number of lines. The order of items should be as the fields below. First three should contain citation, section and DOI link. The following lines will be used to populate the subsequent fields. Abstract needs to be longer than 200 chars. Words &quot;keywords&quot; and &quot;abstract&quot; at the beginning of lines are cropped off.</p>
			</div>
			<form action="/<?=$path?>" name="newabs" method="post">
				<h3><?=isset($rec) ? 'Edit existing' : 'Add new'?> abstract</h3>
				<div class="row">
				<div class="dbl">
					<div class="dbl"><span>Vol:</span><input type="text" maxlength="2"<?=prVal('vol')?>></div>
					<div class="dbl rht"><span>Issue:</span><input type="text" maxlength="2"<?=prVal('issue')?>></div>
				</div>
				<div class="dbl rht">
					<div class="dbl"><span>Start page:</span><input type="text" maxlength="4"<?=prVal('page')?>></div>
					<div class="dbl rht"><span>End page:</span><input type="text" maxlength="4"<?=prVal('end_page')?>></div>
				</div>
				</div>
				<div class="row">
					<div class="dbl">
					<span>Section:</span><select name="section">
						<option disabled selected>Please select...</option><?
						foreach($subj as $k => $v) {
							$sel = $k == $rec['section'] ? ' selected' : '';
							echo '<option value="'.$k.'"'.$sel.'>'.$v.'</option>';
						}
						?>
						<option>Add new section...</option>
					</select></div>
					<div class="dbl rht"><span>DOI:</span><input type="text" maxlength="255"<?=prVal('doi')?>></div>
				</div>
				<div class="row"><span>Title:</span><input type="text" maxlength="255"<?=prVal('title')?>></div>
				<div class="row"><span>Author:</span><input type="text" maxlength="255"<?=prVal('author')?>></div>
				<div class="row"><span>Institute:</span><textarea<?=prVal('inst', 2)?></textarea></div>
				<div class="row"><span>Abstract:</span><textarea<?=prVal('abstract', 5)?></textarea></div>
				<div class="row"><span>Keywords:</span><input type="text" maxlength="255"<?=prVal('keywords')?>></div>
				<?
				if($rec) { ?>
					<div class="row"><span>Language:</span><input type="text" maxlength="255"<?=prVal('pdf')?>></div>
				<? } ?>
				<div class="row"><span>References:</span><textarea<?=prVal('refs', 2)?></textarea></div>
				<? foreach($_GET as $k => $v)
				echo '<input type="hidden" name="'.preg_replace('/\W/','',$k).'" value="'.($v*1).'">';
				?>
				<div class="norow">
					<label class="btn"><input type="checkbox" id="ignore"><span>Ignore warnings</span></label>
					<button class="btn brd">Submit</button>
				</div>
			</form>
		</div>