<?php
// agents/agent_stub.php
// Simulates an AI agent logging tasks in structured JSON.

date_default_timezone_set('UTC');

$agentName = "DevAgent";
$task = $argv[1] ?? "No task specified";

// Define log file path
$logFile = __DIR__ . "/../logs/agent-log.jsonl";

// Prepare log entry
$logEntry = [
    "timestamp" => date('Y-m-d H:i:s'),
    "agent" => $agentName,
    "task" => $task,
    "status" => "completed"
];

// Ensure logs folder exists
if (!file_exists(dirname($logFile))) {
    mkdir(dirname($logFile), 0777, true);
}

// Append JSON log entry
file_put_contents($logFile, json_encode($logEntry) . PHP_EOL, FILE_APPEND);

// Output to console
echo "✅ {$agentName} logged task: {$task}\n";
