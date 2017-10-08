<?php

namespace ctf0\Lingo;

use Illuminate\Support\Facades\Route;

class LingoRoutes
{
    public static function routes()
    {
        Route::group([
            'prefix' => 'lingo',
            'as'     => 'lingo.',
        ], function () {
            // main
            Route::get('/', '\ctf0\Lingo\Controllers\LingoController@index')->name('index');
            Route::get('scan-for-missing', '\ctf0\Lingo\Controllers\LingoController@scanForMissing')->name('scan_for_missing');

            // get
            Route::get('vendor-dirs', '\ctf0\Lingo\Controllers\LingoController@getVendorDirs')->name('vendor_dirs');
            Route::post('get-files', '\ctf0\Lingo\Controllers\LingoController@getFiles')->name('get_files');
            Route::post('get-file-data', '\ctf0\Lingo\Controllers\LingoController@getFileData')->name('get_file_data');

            // save
            Route::post('/', '\ctf0\Lingo\Controllers\LingoController@addNewLocale')->name('add_new_locale');
            Route::post('add-new-file', '\ctf0\Lingo\Controllers\LingoController@addNewFile')->name('add_new_file');
            Route::post('add-new-vendor', '\ctf0\Lingo\Controllers\LingoController@addNewVendor')->name('add_new_vendor');
            Route::post('save-file-data', '\ctf0\Lingo\Controllers\LingoController@saveFileData')->name('save_file_data');

            // delete
            Route::post('delete-file', '\ctf0\Lingo\Controllers\LingoController@deleteFile')->name('delete_file');
            Route::post('delete-locale', '\ctf0\Lingo\Controllers\LingoController@deleteLocale')->name('delete_locale');
            Route::post('delete-vendor', '\ctf0\Lingo\Controllers\LingoController@deleteVendor')->name('delete_vendor');
        });
    }
}
