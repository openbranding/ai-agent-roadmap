<?php
/**
 * agent_stub.php
 * A simple AI Agent placeholder that logs a fake task.
 */

date_default_timezone_set('UTC');

// Agent configuration
$agentName = "DevAgent";   // later we can create ContentAgent, TestAgent, etc.
$task = $argv[1] ?? "No task provided";  // Allow passing task as command line argument

// Log directory
$logDir = __DIR__ . "/../logs";
if (!is_dir($logDir)) {
    mkdir($logDir, 0777, true);
}

$logFile = $logDir . "/agent-log.txt";

// Create log entry
$timestamp = date("Y-m-d H:i:s");
$entry = "[{$timestamp} | {$agentName}] Task started: {$task}" . PHP_EOL;
$entry .= "[{$timestamp} | {$agentName}] Task completed: {$task}" . PHP_EOL . PHP_EOL;

// Write log
file_put_contents($logFile, $entry, FILE_APPEND);

echo "✅ {$agentName} completed task: {$task}" . PHP_EOL;
