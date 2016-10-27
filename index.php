<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
	<meta charset="utf-8">
	<meta name="GitBareCLOC" content="Simple PHP script to count lines of code from a Git bare repository">
	<meta name="Copyright" content="Copyright © 2016, Patricio Rossi - Under GNU GPL Version 3">
	<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
	<link rel="icon" href="/favicon.ico" type="image/x-icon">
	<title>Git Servers Statics</title>
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


small {
	float: right;
	margin-right: 1vw;
}

	</style>
</head>
<body>
<h3>Repositories</h3>
<?php

$force = isset($_REQUEST['force']) ? true : false;

$errors = [];

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
	'txt'	=> 'Text',
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
	'sh'	=> 'Shell script'

];

/**
 * Generate html information from repository.
 * @param string $location the repo location
  * @return boolean return false if fails, true on success.
 */
function updateStatics($location) {
	$additionalInfo = null;
	global $additionalInfo;
	$newAI = str_replace('[repo]', $location, $additionalInfo);
	$contents = shell_exec($newAI);
	if ($contents) {
		file_put_contents(basename($location) . '.html', $contents);
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
	return strtolower(substr($fileName, $pos + 1));
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

/**
 * Process the settings file.
 *
 * @return string[]
 */
function readSettings() {
	$return = [];
	$settings = file_get_contents('settings.conf');
	foreach (explode(PHP_EOL, $settings) as $row) {
		if (empty($row)) {
			continue;
		}
		if ($row[0] == '#') {
			continue;
		}
		$pair = explode(':', $row);
		$return[$pair[0]] = trim($pair[1]);
	}
	return $return;
}

/**
 * Process the directories from a string and checks that the entries are valid.
 *
 * @param string $data
 * @return string[]
 */
function getRepositories($data) {
	$repos = [];
	foreach (explode(',', $data) as $repository) {
		trim($repository);
		if (strpos($repository, '*')) {
			$repos = array_merge($repos, glob($repository));
		}
		else {
			$repos[] = $repository;
		}
	}
	global $errors;
	// Test repos
	foreach ($repos as $key => $repo) {
		if (! @chdir($repo)) {
			$errors[] = "Invalid path $repo";
			unset($repos[$key]);
			continue;
		}
		$out = $result = null;
		@exec("git rev-parse", $out, $result);
		if ($result != 0) {
			$errors[] = "Invalid repository path $repo";
			unset($repos[$key]);
		}
	}
	return $repos;
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
$ignoreExtensions = array_map('strtolower', array_map('trim', explode(',', $settings['ignoreExtensions'])));

/**
 * Array with the repositories to process.
 *
 * @var string[] $repositories
 */
$repositories = getRepositories($settings['repositories']);

/**
 * Current working directory, where the program will run.
 *
 * @var string $cwd
 */
$cwd = trim($settings['workingDirectory']);
chdir($cwd);

$additionalInfo = $settings['additionalInformation'];

unset($settings);

if ($force or time() - $lastUpdate > 3600) {

	// Update timestamp
	$lastUpdate = time();
	file_put_contents('lastUpdate.txt', $lastUpdate);

	// Remove old files.
	@exec('rm *.html');

	// Process Repos
	foreach ($repositories as $repo) {

		$totalFiles = 0;
		$ignoredFiles = 0;
		$statistics = [];
		$byType = [
			'Total'		 => 0,
			'Comments'	 => 0,
			'Whitespaces'=> 0,
		];
		$box = "";

		chdir($repo);
		$files = explode("\n", shell_exec("git ls-tree --name-status -r HEAD"));
		$totalFiles = count($files);
		foreach ($files as $file) {

			if (in_array($file, $ignoreFiles)) {
				$ignoredFiles++;
				continue;
			}

			$extension = detectFileType($file);

			if (in_array($extension, $ignoreExtensions)) {
				$ignoredFiles++;
				continue;
			}

			if (!isset($extensions[$extension])) {
				$extension = '';
			}

			$contents = shell_exec("git show HEAD:$file");
			$statistics = processFile($contents, $extension);
			$byType['Total']		+= $statistics['total'];
			$byType['Comments']		+= $statistics['comments'];
			$byType['Whitespaces']	+= $statistics['empty'];
			if (isset($byType[$extensions[$extension]]))
				$byType[$extensions[$extension]] += $statistics['code'];
			else
				$byType[$extensions[$extension]] = $statistics['code'];
		}

		$box = "<li>Total Files:<code>$totalFiles</code></li><li>Ignored Files:<code>$ignoredFiles</code></li><li>Lines of Code<ul>";
		foreach ($byType as $type => $value) {
			$box .= "<li>$type:<code>$value</code></li>";
		}
		$box .= '</ul></li>';
		chdir($cwd);
		file_put_contents(basename($repo) . 'Box.inc', $box);
		updateStatics($repo);
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
<?php
if (count($errors)) {
	echo '<h3>Errors:</h3><ul>';
	foreach ($errors as $error) {
		echo "<li>$error</li>";
	}
	echo '</ul>';
}
?>
	<footer>
		<small>Copyright © 2016, Patricio Rossi - Under GNU GPL Version 3</small>
	</footer>
</body>
</html>