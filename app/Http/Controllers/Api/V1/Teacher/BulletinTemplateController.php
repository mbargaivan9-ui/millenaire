<?php

namespace App\Http\Controllers\Api\V1\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BulletinTemplateController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(['templates' => []]);
    }

    public function store(Request $request)
    {
        return response()->json(['success' => true], 201);
    }

    public function show(Request $request, $id)
    {
        return response()->json(['template' => []]);
    }

    public function update(Request $request, $id)
    {
        return response()->json(['success' => true]);
    }

    public function upload(Request $request, $id)
    {
        return response()->json(['success' => true]);
    }

    public function processOCR(Request $request, $id)
    {
        return response()->json(['success' => true]);
    }
}
