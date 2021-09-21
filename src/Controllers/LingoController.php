<?php

namespace ctf0\Lingo\Controllers;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Filesystem\Filesystem;

class LingoController extends Controller
{
    use Ops;

    protected $lang_path;
    protected $langDirs;
    protected $file;
    protected $default_locale;

    public function __construct(Filesystem $file)
    {
        $this->lang_path = resource_path('lang');
        $this->langDirs  = $file->directories($this->lang_path);
        $this->file      = $file;
    }

    public function index()
    {
        return view('Lingo::lingo');
    }

    /**
     * [getFiles description].
     *
     * @param Request $request [description]
     *
     * @return [type] [description]
     */
    public function getFiles(Request $request)
    {
        $vendor   = $request->dir_name;
        $dir_name = $this->getPath($vendor);
        $def_dir  = $this->getDefaultLangDir($dir_name, $vendor);

        // new vendor / no files
        if (!$def_dir) {
            return $this->badResponse('NaN');
        }

        $files = collect($this->file->files($def_dir));
        $namespacedFiles = collect($this->file->directories($def_dir))
            ->map(fn (string $dirPath) => $this->file->files($dirPath));
        $files = $files->merge($namespacedFiles->collapse());

        $res = [];

        foreach ($files as $key) {
            $res[] = Str::remove("$def_dir/", $key);
        }

        return $this->goodResponse($res);
    }

    /**
     * [getVendorDirs description].
     *
     * @return [type] [description]
     */
    public function getVendorDirs()
    {
        $all_dirs = '';

        foreach ($this->langDirs as $dir) {
            if (Str::endsWith($dir, 'vendor')) {
                $all_dirs = $this->file->directories($dir);

                break;
            }
        }

        $res = [];

        foreach ($all_dirs as $key) {
            $res[] = substr($key, strrpos($key, '/') + 1);
        }

        return $res;
    }

    /**
     * [getFileData description].
     *
     * @param Request $request [description]
     *
     * @return [type] [description]
     */
    public function getFileData(Request $request)
    {
        $file_name = $request->file_name;
        $vendor    = $request->dir_name;
        $dir_name  = $this->getPath($vendor);
        $locales   = $this->getLocales($dir_name, $vendor);

        // new vendor / no locales
        if (!$locales) {
            return $this->badResponse('NaN');
        }

        // new vendor / have locales
        if (!$file_name) {
            return $this->goodResponse(compact('locales'));
        }

        $def_locale    = $locales[0];
        $def_file      = include "$dir_name/$def_locale/$file_name";
        $def_file_keys = array_keys($def_file);
        $res           = [];

        // no files to extract keys
        if (empty($def_file_keys)) {
            return $this->goodResponse([
                'locales' => $locales,
                'all'     => $res,
            ]);
        }

        foreach ($locales as $code) {
            foreach ($def_file_keys as $key) {
                // so we dont re-include the same file again
                if ($def_locale == $code) {
                    $data = $def_file[$key];
                    $res  = $this->rec($res, $key, $code, $data);
                    continue;
                }

                $inc  = include "$dir_name/$code/$file_name";
                $data = $inc[$key];
                $res  = $this->rec($res, $key, $code, $data);
            }
        }

        // sort keys
        ksort($res);

        return $this->goodResponse([
            'locales' => $locales,
            'all'     => collect($res)->map(function ($v, $k) {
                return [
                    'name'    => $k,
                    'locales' => $v,
                ];
            })->values(),
        ]);
    }

    protected function rec($res, $key, $code, $data)
    {
        if (is_array($data)) {
            $assoc[$key] = $data;

            foreach (Arr::dot($assoc) as $k => $v) {
                $res[$k][$code] = $v;
            }
        } else {
            $res[$key][$code] = $data;
        }

        return $res;
    }

    /**
     * [addNewTrans description].
     *
     * @param Request $request [description]
     */
    public function addNewLocale(Request $request)
    {
        $code     = $request->file_name;
        $vendor   = $request->dir_name;
        $dir_name = $this->getPath($vendor);
        $def_dir  = $this->getDefaultLangDir($dir_name, $vendor);
        $new_dir  = "$dir_name/$code";

        if ($this->file->exists($new_dir)) {
            return $this->badResponse("'$new_dir'" . trans('Lingo::messages.already_exist'));
        }

        if (!$def_dir) {
            if ($this->file->makeDirectory($new_dir)) {
                return $this->goodResponse(trans('Lingo::messages.success', ['attr' => "'{$request->dir_name}/$code'", 'state' => 'Created']));
            }
        } else {
            if ($this->file->copyDirectory($def_dir, $new_dir)) {
                return $this->goodResponse(trans('Lingo::messages.success', ['attr' => "'{$request->dir_name}/$code'", 'state' => 'Created']));
            }
        }

        return $this->badResponse();
    }

    /**
     * [addNewFile description].
     *
     * @param Request $request [description]
     */
    public function addNewFile(Request $request)
    {
        $file_name = $request->file_name;
        $dir_name  = $request->dir_name;
        $dirs      = $this->getLangDirs($dir_name);

        foreach ($dirs as $locale) {
            if (!$dir_name && Str::endsWith($locale, 'vendor')) {
                continue;
            }

            if ($this->file->exists("$locale/$file_name")) {
                return $this->badResponse("'$locale/$file_name'" . trans('Lingo::messages.already_exist'));
            }

            $this->file->put("$locale/$file_name", "<?php\n\nreturn [];");
        }

        return $dir_name
            ? $this->goodResponse(trans('Lingo::messages.success', ['attr' => "'$dir_name/*/$file_name'", 'state' => 'Created']))
            : $this->goodResponse(trans('Lingo::messages.success', ['attr' => "'lang/*/$file_name'", 'state' => 'Created']));
    }

    /**
     * [addNewVendor description].
     *
     * @param Request $request [description]
     */
    public function addNewVendor(Request $request)
    {
        $dir_name   = $request->dir_name;
        $path       = "{$this->lang_path}/vendor/$dir_name";
        $def_locale = config('app.locale');

        if ($this->file->exists($path)) {
            return $this->badResponse("'vendor/$dir_name'" . trans('Lingo::messages.already_exist'));
        }

        if ($this->file->makeDirectory($path)) {
            return $this->goodResponse(trans('Lingo::messages.success', ['attr' => "'vendor/$dir_name'", 'state' => 'Created']));
        }

        return $this->badResponse();
    }

    /**
     * [deleteFile description].
     *
     * @param Request $request [description]
     *
     * @return [type] [description]
     */
    public function deleteFile(Request $request)
    {
        $file_name = $request->file_name;
        $dir_name  = $request->dir_name;
        $dirs      = $this->getLangDirs($dir_name);
        $success   = true;

        foreach ($dirs as $locale) {
            if (!$dir_name && Str::endsWith($locale, 'vendor')) {
                continue;
            }

            $sucess = $this->file->delete("$locale/$file_name") ? true : false;
        }

        return $success == true
            ? $dir_name
                ? $this->goodResponse(trans('Lingo::messages.success', ['attr' => "'$dir_name/*/$file_name'", 'state' => 'Deleted']))
                : $this->goodResponse(trans('Lingo::messages.success', ['attr' => "'$file_name'", 'state' => 'Deleted']))
            : $this->badResponse();
    }

    public function download(Request $request)
    {
        $file_name = $request->file_name;
        $dir_name  = $request->dir_name;

        // vendor only
        if (!$file_name && $dir_name) {
            $path  = realpath("{$this->lang_path}/vendor/$dir_name");
            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));

            return $this->zipAndDownloadDir($dir_name, $files, strlen($path));
        }

        // vendor file
        if ($file_name && $dir_name) {
            $path = "{$this->lang_path}/vendor/$dir_name";
        }

        // file only
        if ($file_name && !$dir_name) {
            $path = $this->lang_path;
        }

        return $this->zipAndDownload($file_name, $path, $dir_name);
    }

    /**
     * [deleteLocale description].
     *
     * @param Request $request [description]
     *
     * @return [type] [description]
     */
    public function deleteLocale(Request $request)
    {
        $locale   = $request->locale;
        $dir_name = $request->dir_name;
        $path     = $this->getPath($dir_name);

        return $this->file->deleteDirectory("$path/$locale")
            ? $dir_name
                ? $this->goodResponse(trans('Lingo::messages.success', ['attr' => "'$dir_name/$locale'", 'state' => 'Deleted']))
                : $this->goodResponse(trans('Lingo::messages.success', ['attr' => "'$locale'", 'state' => 'Deleted']))
            : $this->badResponse();
    }

    /**
     * [deleteVendor description].
     *
     * @param Request $request [description]
     *
     * @return [type] [description]
     */
    public function deleteVendor(Request $request)
    {
        $dir_name = $request->dir_name;
        $path     = "{$this->lang_path}/vendor/$dir_name";

        return $this->file->deleteDirectory($path)
            ? $this->goodResponse(trans('Lingo::messages.success', ['attr' => "'vendor/$dir_name'", 'state' => 'Deleted']))
            : $this->badResponse();
    }

    /**
     * [saveFileData description].
     *
     * @param Request $request [description]
     *
     * @return [type] [description]
     */
    public function saveFileData(Request $request)
    {
        $dir_name  = $request->dir_name;
        $file_name = $request->file_name;
        $data      = collect($request->data)->map(function ($v, $k) {
            return [$v['name'] => $v['locales']];
        })->values();

        if ($this->saveToFile($file_name, $data, $dir_name)) {
            return $dir_name
                ? $this->goodResponse(trans('Lingo::messages.success', ['attr' => "'$dir_name/$file_name'", 'state' => 'Saved']))
                : $this->goodResponse(trans('Lingo::messages.success', ['attr' => "'$file_name'", 'state' => 'Saved']));
        }

        return $this->badResponse();
    }
}
