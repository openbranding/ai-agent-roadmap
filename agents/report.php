<?php
/**
 * report.php — unified CLI dashboard
 * Features:
 *   - Color-coded + icon output
 *   - Group by agent
 *   - --collapse flag for compact view
 *   - --summary-only flag
 *   - --since YYYY-MM-DD filter
 *   - --export-csv / --export-json with unified filenames
 *   - --clean-exports with confirmation
 *   - --stats flag for KPI-style totals only
 */

date_default_timezone_set('UTC');

$logFile   = __DIR__ . '/../logs/agent-log.jsonl';
$exportDir = __DIR__ . '/../exports';

if (!file_exists($exportDir)) {
    mkdir($exportDir, 0777, true);
}

// Parse args
$args = $argv;
array_shift($args);

$summaryOnly = in_array('--summary-only', $args);
$collapse    = in_array('--collapse', $args);
$statsOnly   = in_array('--stats', $args);
$sinceIndex  = array_search('--since', $args);
$sinceDate   = $sinceIndex !== false ? ($args[$sinceIndex + 1] ?? null) : null;
$doCsv       = in_array('--export-csv', $args);
$doJson      = in_array('--export-json', $args);

// Handle cleanup immediately
if (in_array('--clean-exports', $argv)) {
    echo "⚠️  This will permanently delete all files in {$exportDir}\n";
    echo "Are you sure? (y/N): ";
    $confirm = strtolower(trim(fgets(STDIN)));

    if ($confirm === 'y' || $confirm === 'yes') {
        $files = glob($exportDir . '/*');
        $count = 0;
        foreach ($files as $f) {
            if (is_file($f)) {
                unlink($f);
                $count++;
            }
        }
        echo "🧹 Cleaned {$count} file(s) in {$exportDir}\n";
    } else {
        echo "❎ Cleanup aborted. No files were deleted.\n";
    }
    exit(0);
}

// Colors + icons
function decorate($text, $status) {
    return match ($status) {
        'completed'   => "\033[32m✅ {$text}\033[0m",
        'failed'      => "\033[1;31m❌ {$text}\033[0m",
        'pending'     => "\033[36m⏳ {$text}\033[0m",
        'in-progress' => "\033[33m📤 {$text}\033[0m",
        'log'         => "\033[35m📝 {$text}\033[0m",
        default       => $text,
    };
}

// Read logs
if (!file_exists($logFile)) {
    echo "No logs found.\n";
    exit(0);
}

$lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$lines = array_reverse($lines);

$entries = [];
foreach ($lines as $line) {
    $entry = json_decode($line, true);
    if (!$entry) continue;

    if ($sinceDate) {
        $entryDate = substr($entry['timestamp'], 0, 10);
        if ($entryDate < $sinceDate) continue;
    }
    $entries[] = $entry;
}

// Group by agent
$grouped = [];
foreach ($entries as $e) {
    $agent = $e['agent'] ?? 'Unknown';
    $grouped[$agent][] = $e;
}

// Build summary
$summary = [];
foreach ($entries as $e) {
    $day   = substr($e['timestamp'], 0, 10);
    $agent = $e['agent'] ?? 'Unknown';
    $status= $e['status'] ?? 'log';

    if (!isset($summary[$day][$agent])) {
        $summary[$day][$agent] = [
            'total'=>0,'completed'=>0,'failed'=>0,'pending'=>0,'in-progress'=>0,'log'=>0
        ];
    }

    $summary[$day][$agent]['total']++;
    if (isset($summary[$day][$agent][$status])) {
        $summary[$day][$agent][$status]++;
    }
}

// === KPI MODE (stats only) ===
if ($statsOnly) {
    $totals = ['total'=>0,'completed'=>0,'failed'=>0,'pending'=>0,'in-progress'=>0,'log'=>0];
    foreach ($summary as $agents) {
        foreach ($agents as $counts) {
            foreach ($totals as $k => $_) {
                $totals[$k] += $counts[$k];
            }
        }
    }

    // Decide dominant color for Overall Totals header
    $dominantColor = "\033[0m"; // default
    if ($totals['failed'] > 0) {
        $dominantColor = "\033[1;31m"; // bold red if any failures
    } elseif ($totals['in-progress'] > $totals['completed']) {
        $dominantColor = "\033[33m";   // yellow if more in-progress than completed
    } elseif ($totals['completed'] > 0) {
        $dominantColor = "\033[32m";   // green if mostly successful
    }

    echo "=== KPI Dashboard ===\n";
    echo $dominantColor . "📊 Overall Totals\033[0m\n";
    echo "   • Total:       \033[1;37m{$totals['total']}\033[0m\n"; // white bold
    echo "   • ✅ Completed: \033[32m{$totals['completed']}\033[0m\n"; // green
    echo "   • ❌ Failed:    \033[1;31m{$totals['failed']}\033[0m\n"; // bold red
    echo "   • ⏳ Pending:   \033[36m{$totals['pending']}\033[0m\n"; // cyan
    echo "   • 📤 In-Prog.:  \033[33m{$totals['in-progress']}\033[0m\n"; // yellow
    echo "   • 📝 Logs:      \033[35m{$totals['log']}\033[0m\n"; // magenta
    exit(0);
}


// === Normal Report ===
if (!$summaryOnly) {
    echo "=== Agent Activity Report ===\n\n";
    if ($collapse) {
        foreach ($grouped as $agent => $logs) {
            $counts = ['completed'=>0,'failed'=>0,'pending'=>0,'in-progress'=>0,'log'=>0];
            foreach ($logs as $l) {
                $s = $l['status'];
                if (isset($counts[$s])) $counts[$s]++;
            }
            $dominant = "\033[0m";
            if ($counts['failed'] > 0) $dominant = "\033[31m";
            elseif ($counts['in-progress'] > 0) $dominant = "\033[33m";
            elseif ($counts['completed'] > 0) $dominant = "\033[32m";

            echo $dominant . sprintf(
                "%s (%d entries) — ✅ %d | ❌ %d | ⏳ %d | 📤 %d | 📝 %d\n",
                $agent, count($logs),
                $counts['completed'],$counts['failed'],$counts['pending'],
                $counts['in-progress'],$counts['log']
            ) . "\033[0m\n\n";
        }
    } else {
        foreach ($grouped as $agent => $logs) {
            echo "▼ {$agent}\n";
            foreach ($logs as $l) {
                $time   = $l['timestamp'];
                $status = $l['status'];
                $task   = $l['task'];
                $line = "• [{$time}] {$status}: {$task}";
                echo decorate($line, $status) . "\n";
            }
            echo "\n";
        }
    }
    echo "✅ End of report (" . count($entries) . " entries)\n\n";
}

// Print summary
echo "=== Daily Summary ===\n";
ksort($summary);

foreach ($summary as $day => $agents) {
    echo "- {$day}:\n";
    ksort($agents);

    $totals = ['total'=>0,'completed'=>0,'failed'=>0,'pending'=>0,'in-progress'=>0,'log'=>0];
    foreach ($agents as $agent => $counts) {
        foreach ($totals as $k => $_) {
            if (!isset($counts[$k])) $counts[$k] = 0;
        }
        foreach ($totals as $k => $_) {
            $totals[$k] += $counts[$k];
        }

        $dominantColor = "\033[0m";
        if ($counts['failed'] > 0) $dominantColor = "\033[31m";
        elseif ($counts['in-progress'] > 0) $dominantColor = "\033[33m";
        elseif ($counts['completed'] > 0) $dominantColor = "\033[32m";

        printf(
            $dominantColor .
            "   • %-12s | total: %2d | ✅ %2d | ❌ %2d | ⏳ %2d | 📤 %2d | 📝 %2d\n" .
            "\033[0m",
            $agent,
            $counts['total'],$counts['completed'],$counts['failed'],
            $counts['pending'],$counts['in-progress'],$counts['log']
        );
    }

    $dominantColor = "\033[36m";
    if ($totals['failed'] > 0) $dominantColor = "\033[31m";
    elseif ($totals['in-progress'] > 0) $dominantColor = "\033[33m";
    elseif ($totals['completed'] > 0) $dominantColor = "\033[32m";

    echo $dominantColor . sprintf(
        "   TOTAL        | total: %2d | ✅ %2d | ❌ %2d | ⏳ %2d | 📤 %2d | 📝 %2d\n\n",
        $totals['total'],$totals['completed'],$totals['failed'],
        $totals['pending'],$totals['in-progress'],$totals['log']
    ) . "\033[0m";
}

// Export block
if ($doCsv || $doJson) {
    $timestamp = date('Ymd-His');
    if ($doCsv) {
        $csvFile = $exportDir . "/report-{$timestamp}.csv";
        $fp = fopen($csvFile, 'w');
        fputcsv($fp, ['timestamp','agent','status','task']);
        foreach ($entries as $e) {
            fputcsv($fp, [$e['timestamp'],$e['agent'],$e['status'],$e['task']]);
        }
        fclose($fp);
        echo "\n📁 Exported to CSV: {$csvFile}\n";
    }
    if ($doJson) {
        $jsonFile = $exportDir . "/report-{$timestamp}.json";
        file_put_contents($jsonFile, json_encode($entries, JSON_PRETTY_PRINT));
        echo "📁 Exported to JSON: {$jsonFile}\n";
    }
}
