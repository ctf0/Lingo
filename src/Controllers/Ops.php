<?php

namespace ctf0\Lingo\Controllers;

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
            if (!$vendor && ends_with($dir, 'vendor')) {
                continue;
            }

            $locales[] = substr($dir, strrpos($dir, '/') + 1);
        }

        return $locales;
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
                $pre_format[$code][$key] = $v;
            }
        }

        $dir_name = $package_name
            ? "{$this->lang_path}/vendor/$package_name"
            : $this->lang_path;

        foreach ($pre_format as $locale => $value) {
            $dir = "$dir_name/$locale";
            $str = "<?php\n\nreturn " . var_export($value, true) . ';';

            // array(...) to [...]
            $str = str_replace('array (', '[', $str);
            $str = str_replace(')', ']', $str);
            $str = preg_replace('/=>\s+\[/', '=> [', $str);

            if (!$this->file->put("$dir/$file", $str)) {
                return false;
            }
        }

        return true;
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
        return response()->json(['message' => $msg ?? 'Something Went Wrong']);
    }
}
