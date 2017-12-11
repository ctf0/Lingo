<?php

use Illuminate\Support\Facades\Route;

// main
Route::get('/', 'LingoController@index')->name('index');
Route::get('scan-for-missing', 'LingoController@scanForMissing')->name('scan_for_missing');

// get
Route::get('vendor-dirs', 'LingoController@getVendorDirs')->name('vendor_dirs');
Route::post('get-files', 'LingoController@getFiles')->name('get_files');
Route::post('get-file-data', 'LingoController@getFileData')->name('get_file_data');

// save
Route::post('/', 'LingoController@addNewLocale')->name('add_new_locale');
Route::post('add-new-file', 'LingoController@addNewFile')->name('add_new_file');
Route::post('add-new-vendor', 'LingoController@addNewVendor')->name('add_new_vendor');
Route::post('save-file-data', 'LingoController@saveFileData')->name('save_file_data');

// delete
Route::post('delete-file', 'LingoController@deleteFile')->name('delete_file');
Route::post('delete-locale', 'LingoController@deleteLocale')->name('delete_locale');
Route::post('delete-vendor', 'LingoController@deleteVendor')->name('delete_vendor');
