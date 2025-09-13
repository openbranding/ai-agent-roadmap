<?php
// agents/agent_stub.php
// Simulates an AI agent logging tasks in structured JSON.

date_default_timezone_set('UTC');

$agentName = "DevAgent";

// Define valid statuses
$validStatuses = ['completed', 'failed', 'pending', 'log'];

// Get CLI args
array_shift($argv); // remove script name

$status = "completed";
$task = "";

// If first argument is a valid status, use it
if (count($argv) > 0 && in_array(strtolower($argv[0]), $validStatuses)) {
    $status = strtolower(array_shift($argv));
}

// The rest of the args form the task message
if (count($argv) > 0) {
    $task = implode(" ", $argv);
} else {
    $task = "No task specified";
}

// Define log file path
$logFile = __DIR__ . "/../logs/agent-log.jsonl";

// Prepare log entry
$logEntry = [
    "timestamp" => date('Y-m-d H:i:s'),
    "agent"     => $agentName,
    "task"      => $task,
    "status"    => $status
];

// Ensure logs folder exists
if (!file_exists(dirname($logFile))) {
    mkdir(dirname($logFile), 0777, true);
}

// Append JSON log entry
file_put_contents($logFile, json_encode($logEntry) . PHP_EOL, FILE_APPEND);

// Output to console
echo "✅ {$agentName} logged task: [{$status}] {$task}\n";
