<?
require_once(INC_DIR.'dbconn.php');
$subj = $db->getAll();
function prVal($c) {
	global $rec;
	return $rec ? ' value="'.$rec[$c].'"' : '';
}
if(!empty($_GET['vol']) && !empty($_GET['pg'])) {
	$rec = $db->getRow(array('vol' => $_GET['vol']*1, 'start_page' => $_GET['pg']*1), TBL_CON);
}

?>
		<div class="addabs">
			<a href="#addauto">Autofill &amp; format text</a>
			<div class="autofill">
				<div contenteditable="true" data-ph="Paste content here..."></div>
				<p>Paste content in the in following form. All fields must be newline-delimited. The order of the first three lines does not matter, but they should contain section, citation and doi reference (in any order). The following lines should be kept in exact order as the fields below, as each next line will be used to populate the subsequent field. Institute can span any number of lines until it reaches a line longer than 200 chars - that is considered an abstract. All remaining lines after Keywords go into References. Words &quot;keywords&quot; and &quot;abstract&quot; at the beginning of lines are cropped off.</p>
			</div>
			<form action="/<?=$path?>" name="newabs" method="post">
				<h3><?=isset($rec) ? 'Edit existing' : 'Add new'?> abstract</h3>
				<div class="row">
				<div class="dbl">
					<div class="dbl"><span>Vol:</span><input name="vol" type="text" maxlength="2"<?=prVal('vol')?>></div>
					<div class="dbl rht"><span>Issue:</span><input name="issue" type="text" maxlength="2"<?=prVal('issue')?>></div>
				</div>
				<div class="dbl rht">
					<div class="dbl"><span>Start page:</span><input name="start_page" type="text" maxlength="4"<?=prVal('start_page')?>></div>
					<div class="dbl rht"><span>End page:</span><input name="end_page" type="text" maxlength="4"<?=prVal('end_page')?>></div>
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
					<div class="dbl rht"><span>DOI:</span><input name="doi" type="text" maxlength="255"<?=prVal('doi')?>></div>
				</div>
				<div class="row"><span>Title:</span><input name="title" type="text" maxlength="255"<?=prVal('title')?>></div>
				<div class="row"><span>Author:</span><input name="author" type="text" maxlength="255"<?=prVal('author')?>></div>
				<div class="row"><span>Institute:</span><textarea name="inst"><?=$rec ? $rec['inst'] : ''?></textarea></div>
				<div class="row"><span>Abstract:</span><textarea name="abstract" rows="5"><?=$rec ? $rec['abstract'] : ''?></textarea></div>
				<div class="row"><span>Keywords:</span><input name="keywords" type="text" maxlength="255"<?=prVal('keywords')?>></div>
				<?
				if(isset($rec)) {
					$rec['pdf'] = getlang($rec['pdf']);
					?>
					<div class="row"><span>Language:</span><input name="pdf" type="text" maxlength="255"<?=prVal('pdf')?>></div>
				<? } ?>
				<div class="row"><span>References:</span><textarea name="refs"<?=$rec ? ' rows="'.count(explode("\r\n",$rec['refs'])).'">'.$rec['refs'] : '>'?></textarea></div>
				<? foreach($_GET as $k => $v)
				echo '<input type="hidden" name="'.preg_replace('/\W/','',$k).'" value="'.($v*1).'">';
				?>
				<div class="norow">
					<label class="btn"><input type="checkbox" id="ignore"><span>Ignore warnings</span></label>
					<button class="btn brd">Submit</button>
				</div>
			</form>
		</div>