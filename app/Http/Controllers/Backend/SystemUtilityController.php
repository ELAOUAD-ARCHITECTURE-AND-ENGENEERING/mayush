<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use ZipArchive;

class SystemUtilityController extends Controller
{
    public function clearCache(Request $request)
    {
        Artisan::call('optimize:clear');
        flash(translate('Cache cleared successfully'))->success();
        return back();
    }

    public function import_data(Request $request)
    {
        $upload_path = $request->file('uploaded_file')->store('uploads', 'local');
        $sql_path = $request->file('sql_file')->store('uploads', 'local');

        $zip = new ZipArchive;
        $zip->open(base_path('public/'.$upload_path));
        $zip->extractTo('public/uploads/all');

        $zip1 = new ZipArchive;
        $zip1->open(base_path('public/'.$sql_path));
        $zip1->extractTo('public/uploads');

        Artisan::call('cache:clear');
        $sql_path = base_path('public/uploads/demo_data.sql');
        DB::unprepared(file_get_contents($sql_path));
    }
}
