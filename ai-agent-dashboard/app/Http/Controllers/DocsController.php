<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DocsController extends Controller
{
    protected $contentDir;

    public function __construct()
    {
        $this->contentDir = base_path('../content');
    }

    public function index()
    {
        $files = glob($this->contentDir . '/*.md');
        rsort($files);

        $docs = array_map(function ($file) {
            return [
                'name' => basename($file),
                'time' => date("Y-m-d H:i:s", filemtime($file)),
            ];
        }, $files);

        return view('docs.index', compact('docs'));
    }

    public function view($filename)
    {
        $filePath = $this->contentDir . '/' . basename($filename);

        if (!file_exists($filePath)) {
            abort(404, 'File not found');
        }

        $content = file_get_contents($filePath);
        return response()->json(['content' => $content, 'name' => basename($filePath)]);
    }
}
