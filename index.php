<?php

include_once 'functions.php';

/**
 * Supported Extensions.
 *
 * @var string[] $extensions
 */
$extensions = [
	''       => 'Other',
	'php'    => 'PHP',
	'php3'   => 'PHP',
	'php4'   => 'PHP',
	'php5'   => 'PHP',
	'php7'   => 'PHP',
	'phtml'  => 'PHP',
	'js'     => 'JavaScript',
	'asp'    => 'ASP Classic',
	'aspx'   => 'ASP.net',
	'axd'    => 'ASP.net',
	'asx'    => 'ASP.net',
	'asmx'   => 'ASP.net',
	'ashx'   => 'ASP.net',
	'css'    => 'Cascade Style Sheet',
	'cfm'    => 'Coldfusion',
	'yaws'   => 'Erlang',
	'swf'    => 'Flash',
	'html'   => 'HTML',
	'htm'    => 'HTML',
	'xhtml'  => 'xHTML',
	'j'      => 'Java',
	'jav'    => 'Java',
	'java'   => 'Java',
	'jhtml'  => 'Java',
	'jsp'    => 'Java',
	'jspx'   => 'Java',
	'wss'    => 'Java',
	'do'     => 'Java',
	'action' => 'Java',
	'pl'     => 'Perl',
	'py'     => 'Python',
	'rb'     => 'Ruby',
	'rhtml'  => 'Ruby',
	'shtml'  => 'SSI',
	'xml'    => 'XML',
	'rss'    => 'XML',
	'svg'    => 'XML',
	'xls'    => 'XML',
	'c'      => 'C',
	'h'      => 'C Header',
	'cc'     => 'C++',
	'cpp'    => 'C++',
	'cxx'    => 'C++',
	'hh'     => 'C++ Header',
	'hxx'    => 'C++ Header',
	'hpp'    => 'C++ Header',
	'am'     => 'Automake',
	'm4'     => 'GNU M4',
	'in'     => 'make',
	'ac'     => 'Automake Config',
	'csv'    => 'Comma-separated value file',
	'sql'    => 'SQL',
	'doc'    => 'Document',
	'txt'    => 'Text',
	'md'     => 'Markdown text',
	'ini'    => 'Initialization file',
	'conf'   => 'Configuration file',
	'jar'    => 'Java classes archive file',
	'l'      => 'Lex',
	'll'     => 'Lex',
	'log'    => 'Log',
	'cs'     => 'C Sharp',
	'm'      => 'Objective C',
	'mm'     => 'Objective C',
	'luac'   => 'Lua',
	'r'      => 'R',
	'd'      => 'D',
	'bas'    => 'Basic',
	'pas'    => 'Pascal',
	'sh'     => 'Shell script'
];

/**
 * Force refresh
 *
 * @var bool $force
 */
$force = isset($_REQUEST['force']) ? true : false;

/**
 * Current working directory, where the program will run.
 *
 * @var string $cwd
 */
$cwd = getcwd();

/**
 * Used to report errors.
 *
 * @var string[] $errors
 */
$errors = [];

/**
 * The timestamp of the last update.
 *
 * @var number $lastUpdate
 */
$lastUpdate = timestamp();

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

$refresh = $settings['refresh'];

/**
 * Command line to get extra information.
 *
 * @var Ambiguous $additionalInfo
 */
$additionalInfo = $settings['additionalInformation'];

unset($settings);

if ($force or time() - $lastUpdate > $refresh) {

	// Update timestamp
	$lastUpdate = timestamp(time());

	// Remove old files.
	@exec("rm $cwd/Utils/*.html 2> /dev/null");
	@exec("rm $cwd/Utils/*.inc 2> /dev/null");

	// Process Repos
	foreach ($repositories as $repo) {

		$ignoredFiles = 0;
		$statistics   = [];
		$box          = "";

		$byType = [
			'Comments'    => 0,
			'Whitespaces' => 0,
		];

		// Move to git repository
		chdir($repo);

		$files = explode(PHP_EOL, shell_exec("git ls-tree --name-status -r HEAD"));

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

			$byType['Comments']    += $statistics['comments'];
			$byType['Whitespaces'] += $statistics['empty'];

			if (isset($byType[$extensions[$extension]])) {
				$byType[$extensions[$extension]] += $statistics['code'];
			}
			else {
				$byType[$extensions[$extension]] = $statistics['code'];
			}
		}

		$box = "<li>Total Files:<code>" . count($files) . "</code></li>
		        <li>Ignored Files:<code>$ignoredFiles</code></li>
		        <li>Lines of Code<ul>
		        <li>Total: <code>" . array_sum($byType) . '</code></li>
		        <li><span class="chart">' . implode(',', $byType) . '</span><li>';

		$color = 1;
		foreach ($byType as $type => $value) {
			$box .= "<li><span class='slide slide$color'></span>$type<code>$value</code></li>";
			if ($color == 16) {
				$color = 1;
			}
			else {
				$color++;
			}
		}

		$box .= '</ul></li>';

		if (!empty($additionalInfo)) {
			updateStatics($repo);
		}

		file_put_contents("$cwd/Generated/" . basename($repo) . 'Box.inc', $box);
	}
}

?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
	<meta charset="utf-8">
	<meta name="GitBareCLOC" content="Simple PHP script to count lines of code from a Git bare repository">
	<meta name="Copyright" content="Copyright © 2016, Patricio Rossi - Under GNU GPL Version 3">
	<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
	<link rel="icon" href="/favicon.ico" type="image/x-icon">
	<link rel="stylesheet" href="Utils/main.css">
	<title>Git Servers Statistics</title>
	<script type="text/javascript" src="Utils/jquery.min.js"></script>
	<script type="text/javascript" src="Utils/jquery.sparkline.min.js"></script>
	<script type="text/javascript">
//<!--
$(function() {
	$(".chart").sparkline('html', {
		type:       'pie',
		width:      '13vw',
		height:     '13vw',
		enableTagOptions: true,
		borderWidth: 1
	});
});
//-->
	</script>
</head>
<body>
	<h3>Repositories</h3>
	<h4>Updated <?= date("Y-m-d H:i", $lastUpdate);?></h4>
	<ul>
<?php
// Move to working directory
chdir($cwd);

// Draw Boxes
foreach (glob("Generated/*.inc") as $repo) {
	$name = str_replace('Box.inc','', basename($repo));
	echo "<ul><li>" . htmlspecialchars($name);
	// Link to extra statistical information.
	if (file_exists("Generated/$name.html")) {
		echo "<a title='Click to see the statistics' href='Generated/". rawurlencode($name) .".html'><img src='Utils/statistic.png' alt='Statistics' /> View</a>";
	}
	echo "</li>";
	include "$repo";
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