<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
	{
		$logFile = base_path('../ai-agent-roadmap/logs/agent-log.jsonl');
		$entries = [];

		if (file_exists($logFile)) {
			$lines = array_reverse(file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));

			foreach ($lines as $line) {
				$entry = json_decode($line, true);
				if (!$entry) continue;

				$entries[] = [
					'time'   => $entry['timestamp'],
					'agent'  => $entry['agent'] ?? 'Unknown',
					'status' => $entry['status'] ?? 'log',
					'task'   => $entry['task'] ?? '',
				];
			}
		}

		// Compute status counts
		$counts = [
			'all'        => count($entries),
			'completed'  => 0,
			'failed'     => 0,
			'pending'    => 0,
			'in-progress'=> 0,
			'log'        => 0,
		];

		foreach ($entries as $e) {
			$status = $e['status'];
			if (isset($counts[$status])) {
				$counts[$status]++;
			} else {
				$counts['log']++;
			}
		}

		return view('reports.index', compact('entries', 'counts'));
	}
	
	public function dashboard()
	{
		$logFile = base_path('../ai-agent-roadmap/logs/agent-log.jsonl');

		$stats = [
			'total'     => 0,
			'completed' => 0,
			'failed'    => 0,
			'pending'   => 0,
			'inProgress'=> 0,
		];

		if (file_exists($logFile)) {
			$lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

			foreach ($lines as $line) {
				$entry = json_decode($line, true);
				if (!$entry) continue;

				$stats['total']++;
				switch ($entry['status']) {
					case 'completed':    $stats['completed']++; break;
					case 'failed':       $stats['failed']++; break;
					case 'pending':      $stats['pending']++; break;
					case 'in-progress':  $stats['inProgress']++; break;
				}
			}
		}

		return view('dashboard', compact('stats'));
	}
	
	public function export($format)
	{
		$logFile = base_path('../ai-agent-roadmap/logs/agent-log.jsonl');
		if (!file_exists($logFile)) {
			return response()->json(['error' => 'Log file not found'], 404);
		}

		$entries = [];
		foreach (file($logFile) as $line) {
			$entries[] = json_decode($line, true);
		}

		if ($format === 'csv') {
			$filename = 'reports-' . now()->format('Ymd-His') . '.csv';
			$headers = [
				'Content-Type' => 'text/csv',
				'Content-Disposition' => "attachment; filename=\"$filename\"",
			];

			$callback = function() use ($entries) {
				$file = fopen('php://output', 'w');
				fputcsv($file, ['Time', 'Agent', 'Status', 'Task']);
				foreach ($entries as $e) {
					fputcsv($file, [$e['time'], $e['agent'], $e['status'], $e['task']]);
				}
				fclose($file);
			};

			return response()->stream($callback, 200, $headers);
		}

		if ($format === 'json') {
			$filename = 'reports-' . now()->format('Ymd-His') . '.json';
			return response()->json($entries, 200, [
				'Content-Disposition' => "attachment; filename=\"$filename\"",
				'Content-Type' => 'application/json',
			]);
		}

		return redirect()->route('reports.index')->with('error', 'Invalid export format.');
	}


}
