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
            app('router')->setGroupNamespace('\ctf0\Lingo\Controllers');

            // main
            app('router')->get('/', 'LingoController@index')->name('index');
            app('router')->get('scan-for-missing', 'LingoController@scanForMissing')->name('scan_for_missing');

            // get
            app('router')->get('vendor-dirs', 'LingoController@getVendorDirs')->name('vendor_dirs');
            app('router')->post('get-files', 'LingoController@getFiles')->name('get_files');
            app('router')->post('get-file-data', 'LingoController@getFileData')->name('get_file_data');

            // save
            app('router')->post('/', 'LingoController@addNewLocale')->name('add_new_locale');
            app('router')->post('add-new-file', 'LingoController@addNewFile')->name('add_new_file');
            app('router')->post('add-new-vendor', 'LingoController@addNewVendor')->name('add_new_vendor');
            app('router')->post('save-file-data', 'LingoController@saveFileData')->name('save_file_data');

            // download
            app('router')->get('download-file', 'LingoController@download')->name('download_file');
            app('router')->get('download-vendor', 'LingoController@download')->name('download_dir');

            // delete
            app('router')->post('delete-file', 'LingoController@deleteFile')->name('delete_file');
            app('router')->post('delete-locale', 'LingoController@deleteLocale')->name('delete_locale');
            app('router')->post('delete-vendor', 'LingoController@deleteVendor')->name('delete_vendor');
        });
    }
}
