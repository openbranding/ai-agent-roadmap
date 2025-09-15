<?php
/**
 * brainbox.php — BrainBox Orchestrator
 * Phase 2 → Phase 3: Cross-agent automation enabled
 *
 * Usage examples:
 *   php agents/brainbox.php DevAgent "Scaffold FooController"
 *   php agents/brainbox.php ContentAgent "Write setup documentation"
 */

date_default_timezone_set('UTC');

// --- Parse CLI arguments ---
$agent = $argv[1] ?? null;
$task  = $argv[2] ?? null;

if (!$agent || !$task) {
    echo "⚠️ Usage: php agents/brainbox.php [DevAgent|ContentAgent] \"Task description\"\n";
    exit(1);
}

// --- Supported agents ---
$supportedAgents = ['DevAgent', 'ContentAgent'];
if (!in_array($agent, $supportedAgents)) {
    echo "⚠️ Unsupported agent: {$agent}. Choose from: " . implode(", ", $supportedAgents) . "\n";
    exit(1);
}

// --- Setup log file ---
$logFile = __DIR__ . "/../logs/agent-log.jsonl";
if (!file_exists(dirname($logFile))) {
    mkdir(dirname($logFile), 0777, true);
}

// --- Helper: write to log ---
function logEntry($agent, $task, $status, $source = "BrainBox") {
    global $logFile;
    $entry = [
        "time"   => date('Y-m-d H:i:s'),
        "agent"  => $agent,
        "task"   => $task,
        "status" => $status,
        "source" => $source
    ];
    file_put_contents($logFile, json_encode($entry) . PHP_EOL, FILE_APPEND);
}

// --- Dispatch to agent ---
function dispatchAgent($agent, $task) {
    $script = __DIR__ . "/{$agent}.php";

    logEntry($agent, $task, "dispatched");

    if (file_exists($script)) {
        $cmd = "php " . escapeshellarg($script) . " " . escapeshellarg($task);
        passthru($cmd);
    } else {
        logEntry($agent, $task, "failed", "BrainBox");
        echo "⚠️ {$agent} script not found. Dispatch recorded in logs only.\n";
    }
}

// --- Step 1: Run requested task ---
dispatchAgent($agent, $task);

// --- Step 2: Cross-agent automation ---
if ($agent === "DevAgent" && stripos($task, "Scaffold") !== false) {
    // Extract controller name if present
    preg_match('/Scaffold\s+(\w+)/i', $task, $matches);
    $controllerName = $matches[1] ?? "UnknownController";

    $docTask = "Generate README for {$controllerName}";
    echo "🔄 Cross-agent: Triggering ContentAgent → {$docTask}\n";

    dispatchAgent("ContentAgent", $docTask);
}
