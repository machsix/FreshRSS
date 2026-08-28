#!/usr/bin/env php
<?php
declare(strict_types=1);

require dirname(__DIR__) . '/cli/_cli.php';

$username = $_SERVER['argv'][1] ?? '';
if (!is_string($username) || $username === '') {
	fail('Usage: php app/refresh_all.php USERNAME');
}

performRequirementCheck(FreshRSS_Context::systemConf()->db['type'] ?? '');
$username = cliInitUser($username);
$feedDAO = FreshRSS_Factory::createFeedDao();

$processed = 0;
$newArticles = 0;

foreach ($feedDAO->listFeeds() as $feed) {
	$feed->clearCache();

	[$updated, , $new] = FreshRSS_feed_Controller::actualizeFeedsAndCommit(
		feed_id: $feed->id(),
	);

	$processed += $updated;
	$newArticles += $new;

	echo '[', $updated > 0 ? 'OK' : 'SKIPPED/ERROR', '] ', $feed->name(), "\n";
}

invalidateHttpCache($username);
echo "Processed $processed feeds; $newArticles new articles\n";
