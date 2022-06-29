<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class MediaController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @param Workspace $workspace
     * @return array|JsonResponse|void
     */
    public function store(Request $request, Workspace $workspace)
    {
        /**
         * Validate request.
         */
        $validator = Validator::make($request->all(), [
            'file' => 'max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), Response::HTTP_BAD_REQUEST);
        }

        /**
         * Create media.
         */
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = $file->getClientOriginalName();
            $folder = uniqid().'-'.now()->timestamp;

            $path = Storage::disk('public')->putFileAs(
                'temp/'.$folder, $file, $fileName
            );

            return [
                'url' => $path,
                'name' => $fileName,
                'size' => $file->getSize(),
                'id' => $folder,
                'thumb' => in_array($file->getClientOriginalExtension(),
                    ['jpeg', 'jpg', 'png']) ? Storage::url($path) : '',
            ];
        }
    }
}
