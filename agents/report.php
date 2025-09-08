<?php
/**
 * report.php — upgraded to parse structured JSON logs
 */

$logFile = __DIR__ . '/../logs/agent-log.txt';

echo "=== Agent Activity Report ===\n\n";

if (!file_exists($logFile)) {
    echo "⚠️  No logs found. Run agent_stub.php first.\n";
    exit(0);
}

$lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

// Show newest first
$lines = array_reverse($lines);

$count = 0;
foreach ($lines as $line) {
    $entry = json_decode($line, true);

    if (json_last_error() === JSON_ERROR_NONE && is_array($entry)) {
        $time   = $entry['timestamp'] ?? 'unknown time';
        $agent  = $entry['agent'] ?? 'unknown agent';
        $event  = $entry['event'] ?? 'log';
        $task   = $entry['task'] ?? '';

        echo "• [$time | $agent] $event: $task\n";
    } else {
        // fallback: raw line
        echo "• $line\n";
    }

    $count++;
}

if ($count === 0) {
    echo "⚠️  No entries found in log file.\n";
} else {
    echo "\n✅ End of report ($count entries)\n";
}
