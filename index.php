<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
	<meta charset="utf-8">
	<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
	<link rel="icon" href="/favicon.ico" type="image/x-icon">
	<title>Git Server Statics</title>
	<style type="text/css">

body {
	background-color: darkgray;
	margin: 0;
	padding: 0;
}

h3 {
	background-color: #111;
	color: white;
	padding: 4px 5vw;
}

h4 {
	font-size: 80%;
	padding: 0 5vw;
}

ul {
	list-style: none;
	padding: 0;
	marging: 0;
}

li {
	font-size: 1vw;
}

body > ul > ul {
	border: 1px solid black;
	display: inline-block;
	margin: 0 1vw;
	padding: 0 0 0.5vw;
	vertical-align: top;
	background: linear-gradient(to bottom, #3f4c6b 0%,#3f4c6b 100%);

}

body > ul > ul > li:first-child {
	background-color: transparent;
	padding: 5px;
	font-size: 1.3vw;
	color: silver;
}

body > ul > ul > li:nth-child(2) {
	background-color: #A09EAE;
}

body > ul > ul > li:nth-child(3) {
	background-color: #4C4C4C;
}

body > ul > ul > li {
	background: silver;
	margin: 0 5px;
}

body > ul > ul > li:last-child ul {
	margin: 1px 0 0 15px;
}

a {
	color: silver;
	float: right;
	text-decoration: none;
	font-size: 1vw;
	margin: 0 0 0 10px;
}

a img {
	vertical-align: middle;
}

code {
	float: right;
	font-weight: bold;
	margin: 0 6px;
}

	</style>
</head>
<body>
<h3>Repositories</h3>
<?php

$force = isset($_REQUEST['force']) ? true : false;

/**
 * Array with a list of directories containing the repositories.
 *
 * @var string[] $repositories
 */
$repositories = glob("/Git/*");

/**
 * Supported Extensions.
 * @var string[] $extensions
 */
$extensions = [
	''		=> 'Other',
	'php'	=> 'PHP',
	'php3'	=> 'PHP',
	'php4'	=> 'PHP',
	'php5'	=> 'PHP',
	'php7'	=> 'PHP',
	'phtml'	=> 'PHP',
	'js'	=> 'JavaScript',
	'asp'	=> 'ASP Classic',
	'aspx'	=> 'ASP.net',
	'axd'	=> 'ASP.net',
	'asx'	=> 'ASP.net',
	'asmx'	=> 'ASP.net',
	'ashx'	=> 'ASP.net',
	'css'	=> 'Cascade Style Sheet',
	'cfm'	=> 'Coldfusion',
	'yaws'	=> 'Erlang',
	'swf'	=> 'Flash',
	'html'	=> 'HTML',
	'htm'	=> 'HTML',
	'xhtml'	=> 'xHTML',
	'j'		=> 'Java',
	'jav'	=> 'Java',
	'java'	=> 'Java',
	'jhtml'	=> 'Java',
	'jsp'	=> 'Java',
	'jspx'	=> 'Java',
	'wss'	=> 'Java',
	'do'	=> 'Java',
	'action'=> 'Java',
	'pl'	=> 'Perl',
	'py'	=> 'Python',
	'rb'	=> 'Ruby',
	'rhtml'	=> 'Ruby',
	'shtml'	=> 'SSI',
	'xml'	=> 'XML',
	'rss'	=> 'XML',
	'svg'	=> 'XML',
	'xls'	=> 'XML',
	'c'		=> 'C',
	'h'		=> 'C Header',
	'cc'	=> 'C++',
	'cpp'	=> 'C++',
	'cxx'	=> 'C++',
	'hxx'	=> 'C++ Header',
	'hpp'	=> 'C++ Header',
	'am'	=> 'Automake',
	'm4'	=> 'GNU M4',
	'in'	=> 'make',
	'ac'	=> 'Automake Config',
	'csv'	=> 'Comma-separated value file',
	'sql'	=> 'SQL',
	'doc'	=> 'Document',
	'txt'	=> 'text',
	'ini'	=> 'Initialization file',
	'conf'	=> 'Configuration file',
	'jar'	=> 'Java classes archive file',
	'l'		=> 'Lex',
	'll'	=> 'Lex',
	'log'	=> 'Log',
	'cs'	=> 'C#',
	'm'		=> 'Objective C',
	'mm'	=> 'Objective C',
	'luac'	=> 'Lua',
	'r'		=> 'R',
	'd'		=> 'D',
	'bas'	=> 'Basic',
	'pas'	=> 'Pascal',
	'sh'	=> 'shell script'

];

/**
 * Generate html information from repository.
 * @param string $location the repo location
 * @param string $name a friendly name to be used as a file name.
 * @return boolean return false if fails, true on sucess.
 */
function updateStatics($location, $name) {
	$contents = shell_exec("gitinspector --format=html --grading=true $location");
	if ($contents) {
		file_put_contents($name . '.html', $contents);
		return true;
	}
	return false;
}

/**
 * Uses the file extension to return the type information.
 *
 * @param string $fileName
 * @return string
 */
function detectFileType($fileName) {
	$pos = strrpos($fileName, '.');
	if ($pos == 0)
		return '';
	return substr($fileName, $pos + 1);
}

/**
 * Count file elements.
 * @param string $data
 * @return number[]
 */
function processFile($data, $type) {

	$lines = explode("\n", $data);

	$statics = [
		'total'		=> count($lines),
		'empty'		=> 0,
		'code'		=> 0,
		'comments'	=> 0,
	];

	$inCommentBlock = false;

	foreach ($lines as $line) {

		$line = str_replace([' ', "\t"], '', $line);

		if (empty($line)) {
			$statics['empty']++;
			continue;
		}

		if (($line[0] == '/' and $line[1] == '/') or $line[0] == '#') {
			$statics['comments']++;
			continue;
		}

		if (!$inCommentBlock and ($line[0] == '/' and $line[1] == '*')) {

			$statics['comments']++;
			if (strpos($line, '*/') !== false) {
				continue;
			}
			$inCommentBlock = true;
			continue;
		}
		if ($inCommentBlock and strpos($line, '*/') !== false) {
			$statics['comments']++;
			$inCommentBlock =  false;
			continue;
		}
		if ($inCommentBlock) {
			$statics['comments']++;
			continue;
		}

		$statics['code']++;
	}
	return $statics;
}

function readSettings() {
	$return = [];
	$settings = file_get_contents('settings.conf');
	foreach (explode(PHP_EOL, $settings) as $row) {
		foreach (explode(':', $row) as $key => $value) {
			$return[$key] = strtolower(trim($value));
		}
	}
	return $return;
}

$lastUpdate = file_get_contents('lastUpdate.txt');

$settings = readSettings();

/**
 * Files to ignore
 *
 * @var string[] $ignoreFiles
 */
$ignoreFiles = array_map('trim', explode(',', $settings['ignoreFiles']));
$ignoreFiles[] = '';

/**
 * Extensions to ignore.
 *
 * @var string[] $ignoreExtensions
 */
$ignoreExtensions = array_map('trim', explode(',', $settings['ignoreExtensions']));

$cwd = trim($settings['workingDirectory']);
var_dump($settings);
unset($settings);

if ($force or time() - $lastUpdate > 3600) {
	$lastUpdate = time();
	file_put_contents('lastUpdate.txt', $lastUpdate);
	exec('rm *.html');
	foreach ($repositories as $repo) {

		$totalFiles = 0;
		$ignoredFiles = 0;
		$statics = [];
		$byType = [
			'Total'		 => 0,
			'Comments'	 => 0,
			'Whitespaces'=> 0,
		];
		$box = "";

		$name = basename($repo, '.git');

		chdir("/Git/$name.git");
		$files = explode("\n", shell_exec("git ls-tree --name-status -r HEAD"));
		$totalFiles = count($files);
		foreach ($files as $file) {

			if (in_array($file, $ignoreFiles)) {
				$ignoredFiles++;
				continue;
			}

			$extension = strtolower(detectFileType($file));

			if (in_array($extension, $ignoreExtensions)) {
				$ignoredFiles++;
				continue;
			}

			if (!isset($extensions[$extension])) {
				$extension = '';
			}

			$contents = shell_exec("git show HEAD:$file");
			$statics = processFile($contents, $extension);
			$byType['Total']		+= $statics['total'];
			$byType['Comments']		+= $statics['comments'];
			$byType['Whitespaces']	+= $statics['empty'];
			if (isset($byType[$extensions[$extension]]))
				$byType[$extensions[$extension]] += $statics['code'];
			else
				$byType[$extensions[$extension]] = $statics['code'];
		}

		$box = "<li>Total Files:<code>$totalFiles</code></li><li>Ignored Files:<code>$ignoredFiles</code></li><li>Lines of Code<ul>";
		foreach ($byType as $type => $value) {
			$box .= "<li>$type:<code>$value</code></li>";
		}
		$box .= '</ul></li>';
		chdir($cwd);
		file_put_contents($name . 'Box.inc', $box);
		updateStatics($repo, $name);
	}
}

echo "<h4>Updated ". date("Y-m-d H:i", $lastUpdate) ."</h4>";
?>
	<ul>
<?php
foreach (glob("*.html") as $repo) {
	$name = basename($repo, '.html');
	echo "<ul><li>$name<a title='Click to see the statistics' href='$repo'><img src='statistic.png' alt='Statistics' /> View</a></li>";
	include "{$name}Box.inc";
	echo "</ul>";
}
?>
	</ul>
</body>
</html>