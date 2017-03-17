<?php

namespace kongfz;

/**
 * Html
 *
 * @author dongnan
 */
class Html {

    /**
     * 编译并输出less
     * @param string $path
     * @param array $importDirs
     * @return string
     */
    public static function compileLess($path, $importDirs = []) {
        $less = new \lessc();
        if (!empty($importDirs)) {
            $less->setImportDir($importDirs);
        }
        return '<style>' . $less->compileFile($path) . '</style>';
    }

    /**
     * 生成css html标签
     * @param string $path
     * @return string
     */
    public static function tagCss($path) {
        if (!preg_match('#^http(s?)|^/#', $path)) {
            $path = '/' . $path;
        }
        return '<link href="' . $path . '" rel="stylesheet" type="text/css" />' . PHP_EOL;
    }

    /**
     * 生成js html标签
     * @param string $path
     * @return string
     */
    public static function tagJs($path) {
        if (!preg_match('#^http(s?)|^/#', $path)) {
            $path = '/' . $path;
        }
        return '<script src="' . $path . '" type="text/javascript"></script>' . PHP_EOL;
    }

    /**
     * 格式化标签
     * @param string $tag
     * @return string
     */
    public static function formatTag($tag) {
        return trim(strtolower($tag));
    }

    /**
     * 压缩输出html
     * @param string $html
     * @return string
     */
    public static function compress($html) {
        $segments        = preg_split("/(<[^>]+?>)/si", $html, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        $compressed      = array();
        $stack           = array();
        $tag             = '';
        $half_open       = array('meta', 'input', 'link', 'img', 'br');
        $cannot_compress = array('pre', 'code', 'script', 'style');
        foreach ($segments as $seg) {
            if (trim($seg) === '') {
                continue;
            }
            //<.../>
            if (preg_match("!<([a-z0-9]+)[^>]*?/>!si", $seg, $match)) {
                //$tag = self::formatTag($match[1]);
                self::formatTag($match[1]);
                $compressed[] = $seg;
            } else if (preg_match("!</([a-z0-9]+)[^>]*?>!si", $seg, $match)) {//</..>
                $tag = self::formatTag($match[1]);
                if (count($stack) > 0 && $stack[count($stack) - 1] == $tag) {
                    array_pop($stack);
                    $compressed[] = $seg;
                }
                //这里再最好加一段判断，可以用于修复错误的html
                //...
            } else if (preg_match("!<([a-z0-9]+)[^>]*?>!si", $seg, $match)) {//<>
                $tag = self::formatTag($match[1]);
                //半闭合标签不需要入栈，如<br/>,<img/>
                if (!in_array($tag, $half_open)) {
                    array_push($stack, $tag);
                }
                $compressed[] = $seg;
            } else if (preg_match("~<![^>]*>~", $seg)) {
                //文档声明和注释，注释也不能删除，如<!--ie条件-->
                $compressed[] = $seg;
            } else {
                //$compressed[] = in_array($tag, $cannot_compress) ? $seg : preg_replace('!\s!', '', $seg);
                $compressed[] = $seg;
            }
        }

        return join('', $compressed);
    }

}
