<?php

namespace ctf0\Lingo\Controllers;

use ZipStream\ZipStream;
use ZipStream\Option\Archive;

trait Ops
{
    /**
     * [getDefaultLangDir description].
     *
     * @param mixed $dir
     * @param mixed $vendor
     *
     * @return [type] [description]
     */
    protected function getDefaultLangDir($dir, $vendor = false)
    {
        $locales = $this->getLocales($dir, $vendor);

        if ($locales) {
            return "$dir/{$locales[0]}";
        }
    }

    /**
     * [getLocales description].
     *
     * @param [type] $dir    [description]
     * @param bool   $vendor [description]
     *
     * @return [type] [description]
     */
    protected function getLocales($dir, $vendor = false)
    {
        $locales = [];

        foreach ($this->file->directories($dir) as $dir) {
            if (!$vendor && str_contains($dir, 'vendor')) {
                continue;
            }

            $locales[] = $this->localeOnly($dir);
        }

        return $locales;
    }

    protected function localeOnly($dir)
    {
        return substr($dir, strrpos($dir, '/') + 1);
    }

    /**
     * [saveToFile description].
     *
     * @param [type]     $file         [description]
     * @param [type]     $data         [description]
     * @param null|mixed $package_name
     *
     * @return [type] [description]
     */
    protected function saveToFile($file, $data, $package_name = null)
    {
        $pre_format = [];

        foreach ($data as $key => $locales) {
            foreach ($locales as $code => $v) {
                $pre_format = $this->recSave($pre_format, $key, $code, $v);
            }
        }

        $dir_name = $package_name
            ? "{$this->lang_path}/vendor/$package_name"
            : $this->lang_path;

        foreach ($pre_format as $locale => $value) {
            $dir = "$dir_name/$locale";
            $str = "<?php\n\nreturn " . var_export($value, true) . ';';

            if (!$this->file->put("$dir/$file", $str)) {
                return false;
            }
        }

        return true;
    }

    protected function recSave($pre_format, $key, $code, $v)
    {
        if (strpos($key, '.')) {
            $assoc = [];
            $exp   = explode('.', $key);

            // convert array dot to associative
            while (!empty($exp)) {
                $assoc = [array_pop($exp) => $assoc];
            }

            // set value to last key
            array_set($assoc, $key, $v);

            // get root key
            $first_key = strtok($key, '.');

            // hacky fix to avoid indexed nesting
            $final = array_pop($assoc);
            $a     = key($final);
            $b     = current($final);

            // 3rd level deep
            if (is_array($b)) {
                $c = key($b);
                $d = current($b);

                $pre_format[$code][$first_key][$a][$c] = $d;
            } else {
                $pre_format[$code][$first_key][$a] = $b;
            }
        } else {
            $pre_format[$code][$key] = $v;
        }

        return $pre_format;
    }

    /**
     * zip & download.
     *
     * @param mixed $zipName
     * @param mixed $list
     * @param mixed $file_name
     * @param mixed $path
     * @param mixed $vendor
     */
    protected function zipAndDownload($file_name, $path, $vendor)
    {
        $list =  collect($this->getLocales($path, $vendor))->map(function ($locale) use ($file_name, $path) {
            return [
                'locale'=> $locale,
                'name'  => "$file_name",
                'path'  => "$path/$locale/$file_name",
            ];
        });

        return response()->stream(function () use ($file_name, $list) {
            $zipName = pathinfo($file_name, PATHINFO_FILENAME) . '.zip';
            $zip = new ZipStream($zipName, $this->getZipOptions());

            foreach ($list as $file) {
                $zip->addFileFromPath("{$file['locale']}/{$file['name']}", $file['path']);
            }

            $zip->finish();
        });
    }

    protected function zipAndDownloadDir($zipName, $list, $length)
    {
        return response()->stream(function () use ($zipName, $list, $length) {
            $zip = new ZipStream("$zipName.zip", $this->getZipOptions());

            foreach ($list as $file) {
                $filePath = $file->getRealPath();
                if (!is_dir($filePath)) {
                    $zip->addFileFromPath(substr($filePath, $length + 1), $filePath);
                }
            }

            $zip->finish();
        });
    }

    protected function getZipOptions()
    {
        $options = new Archive();
        // $options->setZeroHeader(true);
        $options->setContentType('application/octet-stream');
        $options->setSendHttpHeaders(true);
        $options->setHttpHeaderCallback('header');
        $options->setDeflateLevel(9);

        return $options;
    }

    /**
     * response.
     *
     * @param [type] $msg [description]
     *
     * @return [type] [description]
     */
    protected function goodResponse($msg)
    {
        return response()->json([
            'success'   => true,
            'message'   => $msg,
        ]);
    }

    protected function badResponse($msg = null)
    {
        return response()->json(['message' => $msg ?: trans('Lingo::messages.ajax_error')]);
    }
}
