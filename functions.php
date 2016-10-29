<?php

/**
 * Generate html information from repository.
 * @param string $location the repository location
 * @return boolean return false if fails, true on success.
 */
function updateStatics($location) {
	$command = str_replace('[repo]', $location, $GLOBALS['additionalInfo']);
	$contents = shell_exec($command);
	if ($contents) {
		file_put_contents($GLOBALS['cwd'] . '/Generated/' . basename($location) . '.html', $contents);
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
	if ($pos == 0) {
		return '';
	}
	return strtolower(substr($fileName, $pos + 1));
}

/**
 * Returns/updates the timestamp
 *
 * @param string $timestamp
 * @return string
 */
function timestamp($timestamp = null) {
	if (is_null($timestamp)) {
		return file_get_contents("{$GLOBALS['cwd']}/Generated/lastUpdate.txt");
	}
	file_put_contents("{$GLOBALS['cwd']}/Generated/lastUpdate.txt", $timestamp);
	return $timestamp;
}

/**
 * Count file elements.
 * @param string $data
 * @param string $type TODO: detect comments based on file type.
 * @return number[]
 */
function processFile($data, $type) {

	$lines = explode("\n", $data);

	$statistics = [
		'empty'    => 0,
		'code'     => 0,
		'comments' => 0,
	];

	$inCommentBlock = false;

	foreach ($lines as $line) {

		$line = str_replace([' ', "\t"], '', $line);

		if (empty($line)) {
			$statistics['empty']++;
			continue;
		}

		if (($line[0] == '/' and $line[1] == '/') or $line[0] == '#') {
			$statistics['comments']++;
			continue;
		}

		if (!$inCommentBlock and ($line[0] == '/' and $line[1] == '*')) {

			$statistics['comments']++;
			if (strpos($line, '*/') !== false) {
				continue;
			}
			$inCommentBlock = true;
			continue;
		}
		if ($inCommentBlock and strpos($line, '*/') !== false) {
			$statistics['comments']++;
			$inCommentBlock =  false;
			continue;
		}
		if ($inCommentBlock) {
			$statistics['comments']++;
			continue;
		}

		$statistics['code']++;
	}
	return $statistics;
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
		$repository = trim($repository);
		if (strpos($repository, '*')) {
			$repos = array_merge($repos, glob($repository));
		}
		else {
			$repos[] = $repository;
		}
	}

	// Test repos
	foreach ($repos as $key => $repo) {
		if (! @chdir($repo)) {
			$GLOBALS['errors'][] = "Invalid path $repo";
			unset($repos[$key]);
			continue;
		}
		$out = $result = null;
		@exec("git rev-parse > /dev/null", $out, $result);
		if ($result != 0) {
			$GLOBALS['errors'][] = "Invalid repository path $repo";
			unset($repos[$key]);
		}
	}
	return $repos;
}
