<?php
namespace Agents;

class ContentAgent
{
    protected $logFile;
    protected $outputDir;

    public function __construct()
    {
        $this->logFile   = __DIR__ . '/../logs/agent-log.jsonl';
        $this->outputDir = __DIR__ . '/../content/';

        if (!is_dir($this->outputDir)) {
            mkdir($this->outputDir, 0777, true);
        }
    }

    /**
     * Dispatch a task to ContentAgent
     */
    public function handleTask(string $task)
    {
        $this->logStatus('📝 Dispatched', $task);

        if (stripos($task, 'summarize') !== false && stripos($task, 'log') !== false) {
            $count = $this->extractCount($task);
            $this->summarizeLastLogs($count, $task);
        } elseif (stripos($task, 'readme') !== false) {
            $this->generateReadme($task);
        } else {
            $this->logStatus('⚠️ Unsupported', $task);
        }
    }

    /**
     * Summarize last N logs into a Markdown file
     */
    protected function summarizeLastLogs(int $count = 10, string $task = '')
    {
        $this->logStatus('📤 In Progress', "Summarizing last {$count} logs");

        if (!file_exists($this->logFile)) {
            $this->logStatus('❌ Failed', "Log file not found");
            return;
        }

        $lines = file($this->logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $lastLogs = array_slice($lines, -$count);

        $parsed = [];
        foreach ($lastLogs as $line) {
            $parsed[] = json_decode($line, true);
        }

        $summary = "# Log Summary (Last {$count} entries)\n\n";
        foreach ($parsed as $entry) {
            if (!$entry) continue;
            $time   = $entry['time'] ?? '-';
            $status = $entry['status'] ?? '-';
            $msg    = $entry['message'] ?? '-';
            $agent  = $entry['agent'] ?? 'unknown';

            $summary .= "- **{$time}** [{$agent}] {$status} → {$msg}\n";
        }

        $filename = $this->outputDir . 'summary-' . date('Ymd-His') . '.md';
        file_put_contents($filename, $summary);

        $this->logStatus('✅ Completed', "Summary written to " . basename($filename));
    }

    /**
     * Example Readme generator (stub)
     */
    protected function generateReadme(string $task)
    {
        $this->logStatus('📤 In Progress', "Generating README.md");

        $content  = "# Project Documentation\n\n";
        $content .= "Generated on " . date('Y-m-d H:i:s') . "\n\n";
        $content .= "This is an auto-generated README stub.\n";

        file_put_contents($this->outputDir . 'README.md', $content);

        $this->logStatus('✅ Completed', "README.md created");
    }

    /**
     * Helper: extract N from phrases like "summarize last 10 logs"
     */
    protected function extractCount(string $task): int
    {
        preg_match('/\d+/', $task, $matches);
        return $matches ? (int) $matches[0] : 10;
    }

    /**
     * Log status update into agent-log.jsonl
     */
    protected function logStatus(string $status, string $message)
    {
        $entry = [
            'time'    => date('Y-m-d H:i:s'),
            'agent'   => 'ContentAgent',
            'status'  => $status,
            'message' => $message,
            'source'  => 'dashboard',  // could later capture IP/user
        ];

        file_put_contents($this->logFile, json_encode($entry) . PHP_EOL, FILE_APPEND);
    }
}
