<?
function check($a, $d = 0) { return  !empty($_GET[$a]) || $d ? ' checked' : ''; }
function cite($a) {	return '<i>'.J_NAME.'</i>. '.($a[0]+J_YEAR).', '.$a[0].' ('.$a[1].'):'.$a[2].'&ndash;'.$a[3]; }
function linkabs($a) { return '/archive/'.$a[0].'/'.$a[1].'/'.$a[2]; }
function linkpdf($a) { return '/pdf/'.$a[0].'/'.$a[1].'/'.$a[0].'.0'.$a[1].'.'.str_pad($a[2],3,0,STR_PAD_LEFT).'.pdf'; }
function linkedt($a) { return '<span class="rht"><a href="/newabs?vol='.$a[0].'&pg='.$a[2].'">Edit</a></span>'; }
function plural($n, $a) { return '<div>'.$n.' '.$a.($n > 1 ? 's' : '').'</div>'; }
function linker($a, $n = '', $x = '') {
	if(is_array($a)) {
		$a = $a[0];
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
require_once(INC_DIR.'dbconn.php');
/*?>
		<a href="#add">+ Add new abstract</a>
<?
include 'newabs.php';
*/
$col = 'vol, issue';
$cond = $qval = '';
$group = 'GROUP BY '.$col;
$query = 'SELECT %s FROM '.TBL_CON.' %s %s';
$ccol = 'vol,issue,start_page,end_page,section,doi,title,author,pdf';
$totrow = 0;
$subj = $db->getAll();

if(isset($_GET['vol']) && $_GET['vol']*1) {
	$cond = 'WHERE vol = '.$_GET['vol'];
	if(isset($_GET['issue']) && $_GET['issue']*1) {
		$col = $ccol;
		$cond .= ' AND issue = '.$_GET['issue'];
		$group = '';
		if(isset($_GET['start_page']) && $_GET['start_page']*1) {
			$col = '*';
			$cond .= ' AND start_page = '.$_GET['start_page'];
		}
	}
} else {
	if(!empty($_GET['q'])) {
		$qval = ' value="'.$_GET['q'].'"';
		$idx = array(
			'abs' => 'title,author,inst,abstract,keywords',
			'refs' => 'refs'
		);
		$keywords = preg_split('/\W+/', $_GET['q'], 0, PREG_SPLIT_NO_EMPTY);
		foreach($idx as $k => $val)
			if(isset($_GET[$k]))
				$cfg[] = "SELECT $ccol FROM ".TBL_CON." WHERE MATCH($val) AGAINST ('+".implode(' +',$keywords)."' IN BOOLEAN MODE)";
		if(isset($cfg)) {
			$query = implode(' UNION ',$cfg);
		}
	} ?>
		<div class="search">
			<form action="archive" method="get">
				<input type="text" name="q" placeholder="Search for keywords..."<?=$qval?>>
				<div class="rht">
					<label class="btn"><input type="checkbox" name="abs"<?=check('abs',empty($_GET))?>><span>Abstracts</span></label>
					<label class="btn"><input type="checkbox" name="refs"<?=check('refs')?>><span>References</span></label>
					<button class="btn brd">Search</button>
				</div>
			</form>
		</div>
<?
}

$res = $mysqli->query(sprintf($query.' ORDER BY vol DESC', $col, $cond, $group));
while ($row = $res->fetch_assoc()) {
	$arc[$row['vol']][$row['issue']][] = $row;
	$totrow++;
}
/*echo '<pre>';
print_r($arc);
echo '</pre>';*/
if(isset($arc)) {
if($qval)
	echo plural($totrow, 'result');
foreach($arc as $vol => $issue) {
	$year = J_YEAR + $vol;
	$cur = current($issue);
	$abs = $cur[0];
	if(isset($abs['abstract'])) {
		$loc = array_values(array_slice($abs,0,4));
		$pdf = linkpdf($loc);
		$edt = $user ? linkedt($loc) : '';
		echo '<div>'.$edt.cite($loc).'</div>';
		echo linker($abs['doi']);
		echo '<div class="section">'.$subj[$abs['section']].'</div>';
		echo '<h3>'.$abs['title'].'</h3>';
		echo '<i>'.$abs['author'].'</i>';
		echo '<ul><li>'.implode("</li><li>",explode("\r\n",$abs['inst'])).'</li></ul>';
		echo '<div class="panel"><div class="h">Abstract</div>';
		echo '<div>'.$abs['abstract'];
		echo '<p><strong>Keywords:</strong> '.$abs['keywords'].'</p>';
		echo '<p>Full text: '.linker($pdf, 'PDF ('.getlang($abs['pdf']).')').' '.
			humansize(@filesize($_SERVER['DOCUMENT_ROOT'].$pdf)).'</p></div></div>';
		if(strlen($abs['refs'])) {
			echo '<div class="panel"><div class="h">References</div>';
			echo '<div><ol class="ref"><li>'
				.implode("</li><li>",explode("\r\n",preg_replace_callback('/http[^\s<>"]+\b/','linker',$abs['refs'])))
				.'</li></ol></div></div>';
		}
	} elseif(isset($abs['title'])) {
		echo '<div class="content">';
		$cursec = '';
		foreach($issue as $cur) {
			$abs = $cur[0];
			$doi = $abs['doi'];
			$doi = substr($doi, 0, strrpos($doi, '.'));
			echo "<h2><span>".J_NAME." $year, Vol. $vol, Issue $abs[issue]</span>";
			echo linker($doi, $doi, ' class="rht"')."</h2>";
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
				echo '<h4>'.$edt.linker($url, $abs['title']).'</h4>';
				echo '<div>'.$abs['author'].'</div>';
				echo '<div class="dbl">'.cite($loc).'</div>';
				echo linker($abs['doi']);
				echo '<div class="clr"></div>';
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
			echo '<a href="/archive/'.$vol.'/'.$num.'" class="btn">Issue '.$num.'</a>'.PHP_EOL;
		}
		echo '</div></div>';
	}
}} else echo 'No records found';
?>