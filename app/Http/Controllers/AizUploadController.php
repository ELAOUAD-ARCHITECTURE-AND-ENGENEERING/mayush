<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Upload;
use Response;
use Auth;
use Storage;
use Image;
use enshrined\svgSanitize\Sanitizer;
use Str;
use App\Models\AuditLog;
use App\Services\ClamavService;
use App\Events\SecurityAlert;


class AizUploadController extends Controller
{
    public function index(Request $request)
    {

        $all_uploads = (auth()->user()->user_type == 'seller') ? Upload::where('user_id', auth()->user()->id) : Upload::query();
        $search = null;
        $sort_by = null;

        if ($request->search != null) {
            $search = $request->search;
            $all_uploads->where('file_original_name', 'like', '%' . $request->search . '%');
        }

        $sort_by = $request->sort;
        switch ($request->sort) {
            case 'newest':
                $all_uploads->orderBy('created_at', 'desc');
                break;
            case 'oldest':
                $all_uploads->orderBy('created_at', 'asc');
                break;
            case 'smallest':
                $all_uploads->orderBy('file_size', 'asc');
                break;
            case 'largest':
                $all_uploads->orderBy('file_size', 'desc');
                break;
            default:
                $all_uploads->orderBy('created_at', 'desc');
                break;
        }

        $all_uploads = $all_uploads->paginate(60)->appends(request()->query());


        return (auth()->user()->user_type == 'seller')
            ? view('seller.uploads.index', compact('all_uploads', 'search', 'sort_by'))
            : view('backend.uploaded_files.index', compact('all_uploads', 'search', 'sort_by'));
    }

    public function create()
    {
        if (env('DEMO_MODE') == 'On') {
            flash(translate('Data can not change in demo mode.'))->info();
            return back();
        }

        return (auth()->user()->user_type == 'seller')
            ? view('seller.uploads.create')
            : view('backend.uploaded_files.create');
    }


    public function show_uploader(Request $request)
    {
        return view('uploader.aiz-uploader');
    }

    public function upload(Request $request)
    {
        $type = array(
            "jpg" => "image",
            "jpeg" => "image",
            "png" => "image",
            "svg" => "image",
            "webp" => "image",
            "gif" => "image",
            "mp4" => "video",
            "mpg" => "video",
            "mpeg" => "video",
            "webm" => "video",
            "ogg" => "video",
            "avi" => "video",
            "mov" => "video",
            "flv" => "video",
            "swf" => "video",
            "mkv" => "video",
            "wmv" => "video",
            "wma" => "audio",
            "aac" => "audio",
            "wav" => "audio",
            "mp3" => "audio",
            "zip" => "archive",
            "rar" => "archive",
            "7z" => "archive",
            "doc" => "document",
            "txt" => "document",
            "docx" => "document",
            "pdf" => "document",
            "csv" => "document",
            "xml" => "document",
            "ods" => "document",
            "xlr" => "document",
            "xls" => "document",
            "xlsx" => "document"
        );

        if ($request->hasFile('aiz_file')) {
            // Secure Evolution: Virus Scanning
            $clamav = app(ClamavService::class);
            if (!$clamav->scan($request->file('aiz_file'))) {
                $filename = $request->file('aiz_file')->getClientOriginalName();
                $ip = $request->ip();

                AuditLog::create([
                    'admin_user_id' => auth()->id(),
                    'target_user_id' => auth()->id(),
                    'action_type' => 'MALWARE_BLOCKED',
                    'description' => "Infected file rejected: {$filename}",
                    'ip_address' => $ip,
                ]);

                // Fire Security Alert for Slack
                event(new SecurityAlert("🚫 *Malware Blocked* during upload.\n*File:* `{$filename}`\n*User:* " . (auth()->user() ? auth()->user()->email : 'Guest') . "\n*IP:* `{$ip}`", 'critical'));

                return response()->json([
                    'status' => 'error',
                    'message' => translate('Infected file detected. Upload rejected.')
                ], 403);
            }

            $upload = new Upload;
            $extension = strtolower($request->file('aiz_file')->getClientOriginalExtension());

            if (
                env('DEMO_MODE') == 'On' &&
                isset($type[$extension]) &&
                $type[$extension] == 'archive'
            ) {
                return '{}';
            }

            if (isset($type[$extension])) {
                $upload->file_original_name = null;
                $arr = explode('.', $request->file('aiz_file')->getClientOriginalName());
                for ($i = 0; $i < count($arr) - 1; $i++) {
                    if ($i == 0) {
                        $upload->file_original_name .= $arr[$i];
                    } else {
                        $upload->file_original_name .= "." . $arr[$i];
                    }
                }

                if ($extension == 'svg') {
                    $sanitizer = new Sanitizer();
                    // Load the dirty svg
                    $dirtySVG = file_get_contents($request->file('aiz_file'));

                    // Pass it to the sanitizer and get it back clean
                    $cleanSVG = $sanitizer->sanitize($dirtySVG);

                    // Load the clean svg
                    file_put_contents($request->file('aiz_file'), $cleanSVG);
                }

                $size = $request->file('aiz_file')->getSize();
                $path = $request->file('aiz_file')->store('uploads/all', 'local');

                if (config('filesystems.default') != 'local') {
                    // Return MIME type ala mimetype extension
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    // Get the MIME type of the file
                    $file_mime = finfo_file($finfo, base_path('public/') . $path);

                    Storage::disk(config('filesystems.default'))->put(
                        $path,
                        file_get_contents(base_path('public/') . $path),
                        [
                            'visibility' => 'public',
                            'ContentType' =>  $extension == 'svg' ? 'image/svg+xml' : $file_mime
                        ]
                    );

                    if ($arr[0] != 'updates') {
                        unlink(base_path('public/') . $path);
                    }
                }

                $upload->extension = $extension;
                $upload->file_name = $path;
                $upload->user_id = Auth::user()->id;
                $upload->type = $type[$upload->extension];
                $upload->file_size = $size;
                $upload->save();
            }
            return '{}';
        }
    }

    public function get_uploaded_files(Request $request)
    {
        $uploads = Upload::where('user_id', Auth::user()->id);
        if ($request->search != null) {
            $uploads->where('file_original_name', 'like', '%' . $request->search . '%');
        }
        if ($request->sort != null) {
            switch ($request->sort) {
                case 'newest':
                    $uploads->orderBy('created_at', 'desc');
                    break;
                case 'oldest':
                    $uploads->orderBy('created_at', 'asc');
                    break;
                case 'smallest':
                    $uploads->orderBy('file_size', 'asc');
                    break;
                case 'largest':
                    $uploads->orderBy('file_size', 'desc');
                    break;
                default:
                    $uploads->orderBy('created_at', 'desc');
                    break;
            }
        }
        return $uploads->paginate(60)->appends(request()->query());
    }

    public function destroy($id)
    {
        $upload = Upload::findOrFail($id);

        $this->authorize('delete', $upload);
        try {
            if (env('FILESYSTEM_DRIVER') != 'local') {
                Storage::disk(env('FILESYSTEM_DRIVER'))->delete($upload->file_name);
                if (file_exists(public_path() . '/' . $upload->file_name)) {
                    unlink(public_path() . '/' . $upload->file_name);
                }
            } else {
                $file_path = public_path() . '/' . $upload->file_name;
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
            }

            // Audit Log
            AuditLog::create([
                'admin_user_id' => auth()->user()->id,
                'action_type' => 'file_deletion',
                'description' => 'Deleted file: ' . ($upload->file_original_name ?: 'Unknown') . ' (' . $upload->file_name . ')',
                'ip_address' => request()->ip(),
            ]);

            $upload->delete();
            flash(translate('File deleted successfully'))->success();
        } catch (\Exception $e) {
            $upload->delete();
            flash(translate('File deleted successfully'))->success();
        }

        if (request()->ajax()) {
            return response()->json([
                'status' => 1,
                'message' => translate('File deleted successfully')
            ]);
        }
        return back();
    }

    public function trash(Request $request)
    {
        $all_uploads = Upload::onlyTrashed();
        $search = $request->search;
        $sort_by = $request->sort;

        if (auth()->user()->user_type != 'admin') {
            $all_uploads->where('user_id', auth()->user()->id);
        }

        if ($search != null) {
            $all_uploads->where('file_original_name', 'like', '%' . $search . '%');
        }

        switch ($sort_by) {
            case 'oldest':
                $all_uploads->orderBy('deleted_at', 'asc');
                break;
            case 'smallest':
                $all_uploads->orderBy('file_size', 'asc');
                break;
            case 'largest':
                $all_uploads->orderBy('file_size', 'desc');
                break;
            default:
                $sort_by = $sort_by ?: 'newest';
                $all_uploads->orderBy('deleted_at', 'desc');
                break;
        }

        $all_uploads = $all_uploads->paginate(60)->appends(request()->query());

        return auth()->user()->user_type == 'seller'
            ? view('seller.uploads.trash', compact('all_uploads', 'search', 'sort_by'))
            : view('backend.uploaded_files.trash', compact('all_uploads', 'search', 'sort_by'));
    }

    public function restore(Request $request)
    {
        $ids = (array) $request->id;

        if (empty($ids)) {
            return 0;
        }

        $uploads = Upload::onlyTrashed()->whereIn('id', $ids);

        if (auth()->user()->user_type != 'admin') {
            $uploads->where('user_id', auth()->user()->id);
        }

        foreach ($uploads->get() as $upload) {
            $upload->restore();
        }

        return 1;
    }

    public function bulk_force_delete(Request $request)
    {
        $ids = (array) $request->id;

        if (empty($ids)) {
            return 0;
        }

        $uploads = Upload::onlyTrashed()->whereIn('id', $ids);

        if (auth()->user()->user_type != 'admin') {
            $uploads->where('user_id', auth()->user()->id);
        }

        foreach ($uploads->get() as $upload) {
            $this->deletePhysicalUploadFile($upload);
            $upload->forceDelete();
        }

        return 1;
    }

    private function deletePhysicalUploadFile(Upload $upload)
    {
        if (!$upload->file_name) {
            return;
        }

        if (env('FILESYSTEM_DRIVER') != 'local') {
            Storage::disk(env('FILESYSTEM_DRIVER'))->delete($upload->file_name);
            $local_path = public_path() . '/' . $upload->file_name;
            if (file_exists($local_path)) {
                unlink($local_path);
            }
            return;
        }

        $file_path = public_path() . '/' . $upload->file_name;
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }


    public function bulk_uploaded_files_delete(Request $request)
    {
        if ($request->id) {
            $success_count = 0;
            $fail_count = 0;
            foreach ($request->id as $file_id) {
                $upload = Upload::find($file_id);
                if ($upload) {
                    // Authorization check via strict Policy
                    try {
                        $this->authorize('delete', $upload);
                    } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                        $fail_count++;
                        continue;
                    }

                    try {
                        if (env('FILESYSTEM_DRIVER') != 'local') {
                            Storage::disk(env('FILESYSTEM_DRIVER'))->delete($upload->file_name);
                            if (file_exists(public_path() . '/' . $upload->file_name)) {
                                unlink(public_path() . '/' . $upload->file_name);
                            }
                        } else {
                            $file_path = public_path() . '/' . $upload->file_name;
                            if (file_exists($file_path)) {
                                unlink($file_path);
                            }
                        }

                        // Audit Log
                        AuditLog::create([
                            'admin_user_id' => auth()->user()->id,
                            'action_type' => 'bulk_file_deletion',
                            'description' => 'Deleted file in bulk: ' . ($upload->file_original_name ?: 'Unknown') . ' (' . $upload->file_name . ')',
                            'ip_address' => request()->ip(),
                        ]);

                        $upload->delete();
                        $success_count++;
                    } catch (\Exception $e) {
                        $upload->delete();
                        $success_count++;
                    }
                }
            }
            return response()->json([
                'status' => 1,
                'success_count' => $success_count,
                'fail_count' => $fail_count,
                'message' => translate('Files deleted successfully')
            ]);
        } else {
            return response()->json([
                'status' => 0,
                'message' => translate('No files selected')
            ]);
        }
    }


    public function get_preview_files(Request $request)
    {
        $ids = explode(',', $request->ids);
        $files = Upload::whereIn('id', $ids)
            ->orderByRaw("FIELD(id, " . implode(',', $ids) . ")")
            ->get();
        $new_file_array = [];
        foreach ($files as $file) {
            $file['file_name'] = my_asset($file->file_name);
            if ($file->external_link) {
                $file['file_name'] = $file->external_link;
            }
            $new_file_array[] = $file;
        }
        return $new_file_array;
        // return $files;
    }

    public function all_file()
    {
        $uploads = Upload::all();
        foreach ($uploads as $upload) {
            try {
                if (env('FILESYSTEM_DRIVER') != 'local') {
                    Storage::disk(env('FILESYSTEM_DRIVER'))->delete($upload->file_name);
                    if (file_exists(public_path() . '/' . $upload->file_name)) {
                        unlink(public_path() . '/' . $upload->file_name);
                    }
                } else {
                    unlink(public_path() . '/' . $upload->file_name);
                }
                $upload->delete();
                flash(translate('File deleted successfully'))->success();
            } catch (\Exception $e) {
                $upload->delete();
                flash(translate('File deleted successfully'))->success();
            }
        }

        Upload::query()->truncate();

        return back();
    }

    //Download project attachment
    public function attachment_download($id)
    {
        $project_attachment = Upload::find($id);
        try {
            $file_path = public_path($project_attachment->file_name);
            return Response::download($file_path, $project_attachment->file_original_name.'.'.$project_attachment->extension);
        } catch (\Exception $e) {
            flash(translate('File does not exist!'))->error();
            return back();
        }
    }
    //Download project attachment
    public function file_info(Request $request)
    {
        $file = Upload::findOrFail($request['id']);
        $this->authorize('view', $file);

        return (auth()->user()->user_type == 'seller')
            ? view('seller.uploads.info', compact('file'))
            : view('backend.uploaded_files.info', compact('file'));
    }
}
