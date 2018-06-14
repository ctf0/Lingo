<?php

namespace ctf0\Lingo;

class LingoRoutes
{
    public static function routes()
    {
        app('router')->group([
            'prefix' => 'lingo',
            'as'     => 'lingo.',
        ], function () {
            // main
            app('router')->get('/', '\ctf0\Lingo\Controllers\LingoController@index')->name('index');
            app('router')->get('scan-for-missing', '\ctf0\Lingo\Controllers\LingoController@scanForMissing')->name('scan_for_missing');

            // get
            app('router')->get('vendor-dirs', '\ctf0\Lingo\Controllers\LingoController@getVendorDirs')->name('vendor_dirs');
            app('router')->post('get-files', '\ctf0\Lingo\Controllers\LingoController@getFiles')->name('get_files');
            app('router')->post('get-file-data', '\ctf0\Lingo\Controllers\LingoController@getFileData')->name('get_file_data');

            // save
            app('router')->post('/', '\ctf0\Lingo\Controllers\LingoController@addNewLocale')->name('add_new_locale');
            app('router')->post('add-new-file', '\ctf0\Lingo\Controllers\LingoController@addNewFile')->name('add_new_file');
            app('router')->post('add-new-vendor', '\ctf0\Lingo\Controllers\LingoController@addNewVendor')->name('add_new_vendor');
            app('router')->post('save-file-data', '\ctf0\Lingo\Controllers\LingoController@saveFileData')->name('save_file_data');

            // download
            app('router')->get('download-file', '\ctf0\Lingo\Controllers\LingoController@download')->name('download_file');
            app('router')->get('download-vendor', '\ctf0\Lingo\Controllers\LingoController@download')->name('download_dir');

            // delete
            app('router')->post('delete-file', '\ctf0\Lingo\Controllers\LingoController@deleteFile')->name('delete_file');
            app('router')->post('delete-locale', '\ctf0\Lingo\Controllers\LingoController@deleteLocale')->name('delete_locale');
            app('router')->post('delete-vendor', '\ctf0\Lingo\Controllers\LingoController@deleteVendor')->name('delete_vendor');
        });
    }
}
