<?php
/**
 * brainbox.php — Orchestrator skeleton
 * Phase 2: First BrainBox prototype
 *
 * Usage examples:
 *   php agents/brainbox.php DevAgent "Initialize Laravel project"
 *   php agents/brainbox.php ContentAgent "Write setup documentation"
 */

// Default timezone
date_default_timezone_set('UTC');

// Parse CLI arguments
$agent   = $argv[1] ?? null;
$task    = $argv[2] ?? null;

if (!$agent || !$task) {
    echo "⚠️  Usage: php agents/brainbox.php [DevAgent|ContentAgent] \"Task description\"\n";
    exit(1);
}

// Validate supported agents
$supportedAgents = ['DevAgent', 'ContentAgent'];
if (!in_array($agent, $supportedAgents)) {
    echo "⚠️  Unsupported agent: {$agent}. Choose from: " . implode(", ", $supportedAgents) . "\n";
    exit(1);
}

// === LOGGING SYSTEM (reuse same log as agentlog) ===
$logFile = __DIR__ . "/../logs/agent-log.jsonl";
if (!file_exists(dirname($logFile))) {
    mkdir(dirname($logFile), 0777, true);
}

$logEntry = [
    "timestamp" => date('Y-m-d H:i:s'),
    "agent"     => $agent,
    "task"      => $task,
    "status"    => "dispatched",
    "source"    => "BrainBox"
];

// Append to JSON log
file_put_contents($logFile, json_encode($logEntry) . PHP_EOL, FILE_APPEND);

// === SIMULATED AGENT RESPONSE (placeholder for Phase 2 expansion) ===
$response = match ($agent) {
    'DevAgent'     => "✅ {$agent} received task: {$task} (code-related)",
    'ContentAgent' => "📝 {$agent} received task: {$task} (content-related)",
    default        => "⚠️ Unknown agent"
};

echo $response . PHP_EOL;
