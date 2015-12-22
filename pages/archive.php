<?
function cite($a) {
	return '<cite><abbr>'.
	J_ABBR.'</abbr> <span>'.
	(J_YEAR+$a[0]).', '.
	$a[0].'('.$a[1].'): '.
	$a[2].'&ndash;'.$a[3].
	'</span></cite>';
}
function check($a, $d = 0) { return  !empty($_GET[$a]) || $d ? ' checked' : ''; }
function linkabs($a) { return '/archive/'.$a[0].'/'.$a[1].'/'.$a[2]; }
function linkpdf($a) { return '/pdf/'.$a[0].'/'.$a[1].'/'.$a[0].'.0'.$a[1].'.'.str_pad($a[2],3,0,STR_PAD_LEFT).'.pdf'; }
function linkedt($a) { return linker('/newabs?vol='.$a[0].'&amp;issue='.$a[1].'&amp;page='.$a[2], 'Edit', ' class="rht"'); }
function plural($n, $a) { return '<div>'.$n.' '.$a.($n > 1 ? 's' : '').'</div>'; }
function linker($a, $n = '', $x = '') {
	if(is_array($a)) {
		if($a[1] != 'http') {
			$n = $a[0];
			$a = 'http://'.$n;
		} else $a = $a[0];
		if(substr($a,10,7) == 'doi.org') $x = ' class="xref"';
	}
	if(!$n && !$x) $n = $a;
	return $a ? '<a href="'.$a.'"'.$x.'>'.$n.'</a>' : '';
}
function humansize($bytes, $decimals = 2) {
  $sz = 'BKMGTP';
  $factor = floor((strlen($bytes) - 1) / 3);
  if(!(strlen($bytes)%2)) $decimals = 0;
  return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
}
function getval($a, $name = '', $int = false) {
	$q = '"';
	if(!empty($_GET[$a])) {
		$val = $_GET[$a];
		$num = $val * 1;
		if($val === (string) $num || $int) {
			$val = $num;
			$q = '';
		}
		if($name * 1) $name = $a;
		if($name && $val) $val = $name.'='.$q.$val.$q;
	} else $val = '';
	return $val;
}
require_once(INC_DIR.'dbconn.php');
/*?>
		<a href="#add">+ Add new abstract</a>
<?
include 'newabs.php';
*/
$col = 'vol, issue';
$group = 'GROUP BY '.$col;
$query = 'SELECT %s FROM '.TBL_CON.' %s %s';
$ccol = 'vol,issue,page,end_page,section,doi,title,author,pdf';
$totrow = 0;
$subj = $db->getAll();
$cond = array();
$xtra = false;
$qval = getval('q', 'value');
$sec = getval('sec');

if($val = getval('vol', 1, 1)) { // identical name, force int
	$cond[] = $val;
	if($val = getval('issue', 1, 1)) {
		$col = $ccol;
		$cond[] = $val;
		$group = '';
		if($val = getval('page', 1, 1)) {
			$col = '*';
			$cond[] = $val;
		}
	}
} else {
	if($qval || $sec) {
		$keywords = '';
		$idx = array(
			'abs' => 'title,author,inst,abstract,keywords',
			'refs' => 'refs'
		);

		if($qval) {
			$qval = ' '.$qval;
			$keywords = implode(' +', preg_split('/\W+/', $_GET['q'], 0, PREG_SPLIT_NO_EMPTY));
		}

		if($sec * 1) $cond[] = getval('sec', 'section', 1);
		if($keywords) {
			foreach($idx as $k => $val)
				if(isset($_GET[$k]))
					$cmd[$k] = $val;
		} else $cmd = array(0);
		if(!isset($cmd)) $cmd = $idx;

		foreach($cmd as $k => $val) {
			if($val) $cond['kw'] = "MATCH($val) AGAINST ('+".$keywords."' IN BOOLEAN MODE)";
			$cmd[$k] = "SELECT $ccol FROM ".TBL_CON.($cond ? ' WHERE ' : '').implode(' AND ', $cond);
		}

		$query = implode(' UNION ',$cmd);
		$xtra = true;
	} ?>
		<div class="search">
			<form action="archive" method="get">
				<div class="full"><input type="text" name="q" placeholder="Search for keywords..."<?=$qval?>></div>
				<div><label class="btn" title="Includes: Title, Author, Institution, Abstract, Keywords"><input type="checkbox" name="abs"<?=check('abs',empty($_GET))?>><span>Content</span></label></div>
				<div><label class="btn"><input type="checkbox" name="refs"<?=check('refs')?>><span>References</span></label></div>
				<div><select name="sec" id="ignore">
					<option selected>All sections</option>
					<? foreach($subj as $k => $v) {
						$sel = $sec && $sec === $k ? ' selected' : '';
						echo '<option value="'.$k.'"'.$sel.'>'.$v.'</option>';
					} ?>
				</select></div>
				<div><button class="btn brd">Search</button></div>
			</form>
		</div>
<?
}
$cond = implode(' AND ', $cond);
if($cond) $cond = ' WHERE '.$cond;

$res = $mysqli->query(sprintf($query.' ORDER BY vol DESC', $col, $cond, $group));
while ($row = $res->fetch_assoc()) {
	$arc[$row['vol']][$row['issue']][] = $row;
	$totrow++;
}
/*echo '<pre>';
print_r($arc);
echo '</pre>';*/
if(isset($arc)) {
$cursec = '';
if($xtra)
	echo plural($totrow, 'result');
foreach($arc as $vol => $issue) {
	$year = J_YEAR + $vol;
	$cur = current($issue);
	$abs = $cur[0];
	if(isset($abs['abstract'])) {
		$opn = '<i>';
		$cls = '</i>';
		$bgn = explode($opn, $abs['keywords']);
		foreach($bgn as $val) {
			$end = explode($cls, $val);
			if(count($end) > 1)
				$end[0] = $opn.preg_replace('/,\s*/', $cls.', '.$opn, $end[0]).$cls;
			$kwd[] = implode($end);
		}
		$kwd = preg_split('/,\s*/', rtrim(implode('', $kwd), '.'));
		foreach($kwd as &$w)
			$w = linker('/archive?abs=on&amp;q='.urlencode(strip_tags($w)), $w);
		$loc = array_values(array_slice($abs,0,4));
		$pdf = linkpdf($loc);
		$edt = $user ? linkedt($loc) : '';
		echo $edt.'<div>'.cite($loc).'</div>';
		echo linker(mkdoi($abs['doi']));
		echo '<div class="section">'.$subj[$abs['section']].'</div>';
		echo '<h3>'.$abs['title'].'</h3>';
		echo '<i>'.$abs['author'].'</i>';
		echo '<ul><li>'.implode("</li><li>",explode("\r\n",$abs['inst'])).'</li></ul>';
		echo '<div class="panel"><div class="h">Abstract</div><div>';
		echo '<p>'.$abs['abstract'].'</p>';
		echo '<p><strong>Keywords:</strong> '.implode(', ', $kwd).'</p>';
		echo '<p>Full text: '.linker($pdf, 'PDF ('.getlang($abs['pdf']).')').' '.
			humansize(@filesize($_SERVER['DOCUMENT_ROOT'].$pdf)).'</p></div></div>';
		if(strlen($abs['refs'])) {
			echo '<div class="panel"><div class="h">References</div>';
			echo '<div><ol class="ref"><li>'
				.implode("</li><li>",explode("\r\n",preg_replace_callback('/\b(http|www)([^\s<>"&]|&(?![lg]t;))+\b\/?/','linker',$abs['refs'])))
				.'</li></ol></div></div>';
		}
	} elseif(isset($abs['title'])) {
		echo '<div class="content">';
		foreach($issue as $cur) {
			$abs = $cur[0];
			$doi = mkdoi($abs['doi']);
			$doi = substr($doi, 0, strrpos($doi, '.'));
			echo linker($doi, $doi, ' class="rht"');
			echo "<h2>".J_NAME." $year, Vol. $vol, Issue $abs[issue]</h2>";
			echo plural(count($cur), 'article');
			foreach($cur as $abs) {
				if($cursec != $subj[$abs['section']]) {
					$cursec = $subj[$abs['section']];
					echo "<h3>$cursec</h3>";
				}
				$loc = array_values(array_slice($abs,0,4));
				$url = linkabs($loc);
				$pdf = linkpdf($loc);
				$edt = $user ? linkedt($loc) : '';
				echo '<div class="entry">';
				echo $edt.'<h4>'.linker($url, $abs['title']).'</h4>';
				echo '<div>'.$abs['author'].'</div>';
				echo cite($loc).' '.linker(mkdoi($abs['doi']));
				if(file_exists($_SERVER['DOCUMENT_ROOT'].$pdf))
					echo '<div>'.linker($url, 'Abstract').' | Full text: '.
					linker($pdf, 'PDF ['.getlang($abs['pdf']).']').'</div>';
				echo '</div>';
			}
		}
		echo '</div>';
	} else {
		echo '<div class="panel">';
		echo '<h3 class="h">'."$year, Volume $vol".'</h3>';
		echo '<div>';
		foreach(array_keys($issue) as $num) {
			echo '<a href="/archive/'.$vol.'/'.$num.'" class="issue btn">'.
			'<img src="/img/cover-'.$year.'-'.$num.'.jpg" alt="cover">'.
			'<span class="btn">Issue '.$num.'</span>'.
			'</a>'.PHP_EOL;
		}
		echo '</div></div>';
	}
}} else sethead('No records found', $xtra ? 200 : 404);
?>