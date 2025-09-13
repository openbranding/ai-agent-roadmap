<?php
/**
 * ContentAgent.php — Content Agent stub with step logging
 */

date_default_timezone_set('UTC');

$task = $argv[1] ?? "No task provided";

echo "📝 ContentAgent starting task: {$task}\n";

// Logging helper
function logStep($agent, $task, $status) {
    $logFile = __DIR__ . "/../logs/agent-log.jsonl";
    if (!file_exists(dirname($logFile))) {
        mkdir(dirname($logFile), 0777, true);
    }
    $logEntry = [
        "timestamp" => date('Y-m-d H:i:s'),
        "agent"     => $agent,
        "task"      => $task,
        "status"    => $status,
        "source"    => "ContentAgent"
    ];
    file_put_contents($logFile, json_encode($logEntry) . PHP_EOL, FILE_APPEND);
}

// Fake documentation steps
if (stripos($task, 'doc') !== false || stripos($task, 'write') !== false) {
    $steps = [
        "Opening docs/articles/setup.md",
        "Writing introduction",
        "Adding step-by-step installation guide",
        "Formatting with Markdown headings and code blocks"
    ];
    foreach ($steps as $s) {
        echo "   ➤ {$s}...\n";
        logStep("ContentAgent", $s, "in-progress");
        sleep(1);
    }
    echo "   ✅ Documentation draft saved successfully.\n";
    logStep("ContentAgent", "Documentation draft creation", "completed");
} else {
    echo "   ⚠️ Task not recognized as documentation task.\n";
    logStep("ContentAgent", "Unrecognized task: {$task}", "failed");
}

echo "📝 ContentAgent finished task: {$task}\n";
