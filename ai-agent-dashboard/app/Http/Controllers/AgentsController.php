<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AgentsController extends Controller
{
    public function index()
{
    $logFile = base_path('../logs/agent-log.jsonl');
    $agents = ['DevAgent', 'ContentAgent']; // known agents

    $data = [];

    foreach ($agents as $agent) {
        $lastTask = 'No activity yet';
        $lastStatus = 'log';
        $lastSeen = '-';

        if (file_exists($logFile)) {
            $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach (array_reverse($lines) as $line) {
                $entry = json_decode($line, true);
                if ($entry && isset($entry['agent']) && $entry['agent'] === $agent) {
                    $lastTask   = $entry['task'] ?? 'Unknown';
                    $lastStatus = strtolower($entry['status'] ?? 'log');
                    $lastSeen   = $entry['time'] ?? ($entry['timestamp'] ?? '-');
                    break;
                }
            }
        }

        // Decorations
        $rowClass = '';
        $badgeClass = 'badge-secondary';
        $icon = '📝';
        $label = ucfirst($lastStatus);

        switch ($lastStatus) {
            case 'completed':   $rowClass='table-success'; $badgeClass='badge-success'; $icon='✅'; $label='Completed'; break;
            case 'failed':      $rowClass='table-danger';  $badgeClass='badge-danger';  $icon='❌'; $label='Failed'; break;
            case 'pending':     $rowClass='table-warning'; $badgeClass='badge-warning'; $icon='⏳'; $label='Pending'; break;
            case 'in-progress': $rowClass='table-info';    $badgeClass='badge-info';    $icon='📤'; $label='In Progress'; break;
            case 'dispatched':  $rowClass='';              $badgeClass='badge-secondary'; $icon='📝'; $label='Dispatched'; break;
        }

        $data[] = [
            'name'       => $agent,
            'last_task'  => $lastTask,
            'last_status'=> $lastStatus,
            'last_seen'  => $lastSeen,
            'rowClass'   => $rowClass,
            'badgeClass' => $badgeClass,
            'icon'       => $icon,
            'label'      => $label,
        ];
    }

    // Parse DevAgent-managed routes
    $webFile = base_path('routes/web.php');
    $devRoutes = [];
    if (file_exists($webFile)) {
        $lines = file($webFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (preg_match("/Route::get\\('([^']+)', \\[(.+)::class, 'index'\\]\\)/", $line, $matches)) {
                $devRoutes[] = [
                    'path' => $matches[1],
                    'controller' => $matches[2],
                ];
            }
        }
    }

    return view('agents.index', [
        'agents' => $data,
        'devRoutes' => $devRoutes,
    ]);
}


    public function history($agent)
    {
        $logFile = base_path('../logs/agent-log.jsonl');
        $entries = [];

        if (file_exists($logFile)) {
            $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            foreach ($lines as $line) {
                $data = json_decode($line, true);
                if (!$data || !isset($data['agent'])) {
                    continue;
                }

                if ($data['agent'] === $agent) {
                    $time = $data['time'] ?? $data['timestamp'] ?? '-';
                    $status = strtolower($data['status'] ?? 'log');

                    $rowClass = '';
                    $badgeClass = 'badge-secondary';
                    $icon = '📝'; 
                    $label = ucfirst($status);

                    switch ($status) {
                        case 'completed':   $rowClass='table-success'; $badgeClass='badge-success'; $icon='✅'; $label='Completed'; break;
                        case 'failed':      $rowClass='table-danger';  $badgeClass='badge-danger';  $icon='❌'; $label='Failed'; break;
                        case 'pending':     $rowClass='table-warning'; $badgeClass='badge-warning'; $icon='⏳'; $label='Pending'; break;
                        case 'in-progress': $rowClass='table-info';    $badgeClass='badge-info';    $icon='📤'; $label='In Progress'; break;
                        case 'dispatched':  $rowClass='';              $badgeClass='badge-secondary'; $icon='📝'; $label='Dispatched'; break;
                    }

                    $entries[] = [
                        'time'       => $time,
                        'status'     => $status,
                        'task'       => $data['task'] ?? '-',
                        'rowClass'   => $rowClass,
                        'badgeClass' => $badgeClass,
                        'icon'       => $icon,
                        'label'      => $label,
                    ];
                }
            }
        }

        return view('agents.history', [
            'entries' => $entries,
            'agentName' => $agent,
        ]);
    }

    public function addTask(Request $request, $agentName)
    {
        $task = $request->input('task');

        $logFile = base_path('../logs/agent-log.jsonl');
        $entry = [
            'time'   => date('Y-m-d H:i:s'),
            'agent'  => $agentName,
            'status' => 'dispatched',
            'task'   => $task,
            'source' => 'Dashboard',
        ];

        file_put_contents($logFile, json_encode($entry) . PHP_EOL, FILE_APPEND);

        return redirect()->route('agents.index')
            ->with('success', "Task dispatched to {$agentName}: {$task}");
    }
	
	// app/Http/Controllers/AgentsController.php

public function cleanupRoutes()
	{
		$routesFile = base_path('routes/web.php');
		$removed = []; // ✅ initialize

		if (file_exists($routesFile)) {
			$lines = file($routesFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
			$newLines = [];

			foreach ($lines as $line) {
				// Detect DevAgent-generated routes
				if (preg_match("/Route::get\\('([^']+)', \\[(.+)Controller::class, 'index'\\]\\)/", $line, $matches)) {
					$controllerName = $matches[2] . 'Controller';
					$controllerPath = app_path("Http/Controllers/{$controllerName}.php");

					if (!file_exists($controllerPath)) {
						$removed[] = $controllerName;
						continue; // skip this stale route
					}
				}
				$newLines[] = $line;
			}

			file_put_contents($routesFile, implode(PHP_EOL, $newLines) . PHP_EOL);
		}

		// --- ✅ Log cleanup event ---
		$logFile = base_path('../logs/agent-log.jsonl'); // consistent path
		if (!is_dir(dirname($logFile))) {
			mkdir(dirname($logFile), 0777, true); // ensure logs folder exists
		}

		$entry = [
			'time'   => date('Y-m-d H:i:s'),
			'agent'  => 'DevAgent',
			'status' => 'completed',
			'task'   => 'Cleanup routes (' . (count($removed) ?: 'no') . ' removed)',
			'source' => 'Dashboard',
		];
		file_put_contents($logFile, json_encode($entry) . PHP_EOL, FILE_APPEND);

		return redirect()->route('agents.index')
			->with('success', 'Cleanup complete. Removed: ' . (count($removed) ?: 'none'));
	}
	
	public function summaries()
	{
		$dir = base_path('../content'); // ✅ go up one level from Laravel root
		$files = glob($dir . '/summary-*.md');
		rsort($files);

		$summaries = array_map(function ($file) {
			return [
				'name' => basename($file),
				'time' => date("Y-m-d H:i:s", filemtime($file)),
			];
		}, $files);

		return response()->json($summaries);
	}

	public function viewSummary($filename)
	{
		$dir = base_path('../content'); // ✅ consistent path
		$filePath = $dir . '/' . basename($filename);

		if (!file_exists($filePath)) {
			return response()->json(['error' => 'File not found'], 404);
		}

		$content = file_get_contents($filePath);
		return response()->json([
			'name' => basename($filePath),
			'content' => $content
		]);
	}

}
