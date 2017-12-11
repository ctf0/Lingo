<?php

namespace ctf0\Lingo\Http\Controllers;

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
        return response()->json(['message' => $msg ?? trans('Lingo::messages.ajax_error')]);
    }
}
