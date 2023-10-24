<?php
require_once dirname(__FILE__) . '/../inc/defines.inc.php';
require_once dirname(__FILE__) . '/../inc/common.inc.php';
require_once 'clipboard.class.php';
require_once 'messageboard.class.php';

/**
 * Utility Class
 * 2009-9-11
 * @author Sun Junwen
 *
 */
class Utility {
    /**
     * Get base directory path.
     * @return string base directory path
     */
    public static function get_file_base_dir() {
        if (!defined('FILE_POSITION')) {
            return null;
        }
        if (FILE_POSITION == 'relative') {
            $base_dir = get_base_dir();
            $files_base_dir = $base_dir . FILES_DIR . '/';
            return $files_base_dir;
        } else if (FILE_POSITION == 'absolute') {
            $files_base_dir = FILES_DIR . '/';
            return $files_base_dir;
        }
    }

    /**
     * Format size string.
     * @param number $size size in bytes
     * @return string formatted size string
     */
    public static function format_size($size) {
        if ($size > 1024) {
            $size /= 1024.0;
            if ($size > 1024) {
                $size /= 1024.0;
                $size = round($size, 2);
                $size .= 'MB';
            } else {
                $size = round($size, 2);
                $size .= 'KB';
            }
        } else {
            $size .= 'B';
        }

        return $size;
    }

    /**
     * Get file ext.
     * @param string $file file path
     * @return string ext (exclude .)
     */
    public static function get_file_ext($file) {
        $dot_pos = strrpos($file, '.');
        $type = '';
        if ($dot_pos !== false) {
            $type = substr($file, $dot_pos + 1, strlen($file) - $dot_pos - 1);
        }
        return $type;
    }

    /**
     * Check file name.
     * @param string $name
     * @return bool
     */
    public static function check_name($name) {
        if (empty($name)) {
            return false;
        }
        if (false !== strpos($name, '..') ||
            false !== strpos($name, '/') ||
            false !== strpos($name, '\\') ||
            false !== strpos($name, '*') ||
            false !== strpos($name, '?') ||
            false !== strpos($name, '"') ||
            false !== strpos($name, '|') ||
            false !== strpos($name, '&') ||
            false !== strpos($name, '>') ||
            false !== strpos($name, '<')) {
            return false;
        }

        return true;
    }

    /**
     * Check path.
     * @param string $path
     * @return bool
     */
    public static function check_path($path, $allow_empty = false) {
        if (!$allow_empty && empty($path)) {
            return false;
        }
        if (false !== strpos($path, '..') ||
            false !== strpos($path, '*') ||
            false !== strpos($path, '?') ||
            false !== strpos($path, '"') ||
            false !== strpos($path, '|') ||
            false !== strpos($path, '&') ||
            false !== strpos($path, '>') ||
            false !== strpos($path, '<')) {
            return false;
        }

        return true;
    }

    /**
     * Get MIME by file ext.
     * @param string $file_extension file ext
     * @return string MIME
     */
    public static function get_mime_type($file_extension) {
        $mimetypes = array(
            'ez' => 'application/andrew-inset',
            'hqx' => 'application/mac-binhex40',
            'cpt' => 'application/mac-compactpro',
            'doc' => 'application/msword',
            'bin' => 'application/octet-stream',
            'dms' => 'application/octet-stream',
            'lha' => 'application/octet-stream',
            'lzh' => 'application/octet-stream',
            'exe' => 'application/octet-stream',
            'class' => 'application/octet-stream',
            'so' => 'application/octet-stream',
            'dll' => 'application/octet-stream',
            'oda' => 'application/oda',
            'pdf' => 'application/pdf',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',
            'smi' => 'application/smil',
            'smil' => 'application/smil',
            'wbxml' => 'application/vnd.wap.wbxml',
            'wmlc' => 'application/vnd.wap.wmlc',
            'wmlsc' => 'application/vnd.wap.wmlscriptc',
            'bcpio' => 'application/x-bcpio',
            'vcd' => 'application/x-cdlink',
            'pgn' => 'application/x-chess-pgn',
            'cpio' => 'application/x-cpio',
            'csh' => 'application/x-csh',
            'dcr' => 'application/x-director',
            'dir' => 'application/x-director',
            'dxr' => 'application/x-director',
            'dvi' => 'application/x-dvi',
            'spl' => 'application/x-futuresplash',
            'gtar' => 'application/x-gtar',
            'hdf' => 'application/x-hdf',
            'js' => 'application/x-javascript',
            'skp' => 'application/x-koan',
            'skd' => 'application/x-koan',
            'skt' => 'application/x-koan',
            'skm' => 'application/x-koan',
            'latex' => 'application/x-latex',
            'nc' => 'application/x-netcdf',
            'cdf' => 'application/x-netcdf',
            'sh' => 'application/x-sh',
            'shar' => 'application/x-shar',
            'swf' => 'application/x-shockwave-flash',
            'sit' => 'application/x-stuffit',
            'sv4cpio' => 'application/x-sv4cpio',
            'sv4crc' => 'application/x-sv4crc',
            'tar' => 'application/x-tar',
            'tcl' => 'application/x-tcl',
            'tex' => 'application/x-tex',
            'texinfo' => 'application/x-texinfo',
            'texi' => 'application/x-texinfo',
            't' => 'application/x-troff',
            'tr' => 'application/x-troff',
            'roff' => 'application/x-troff',
            'man' => 'application/x-troff-man',
            'me' => 'application/x-troff-me',
            'ms' => 'application/x-troff-ms',
            'ustar' => 'application/x-ustar',
            'src' => 'application/x-wais-source',
            'xhtml' => 'application/xhtml+xml',
            'xht' => 'application/xhtml+xml',
            'zip' => 'application/zip',
            'au' => 'audio/basic',
            'snd' => 'audio/basic',
            'mid' => 'audio/midi',
            'midi' => 'audio/midi',
            'kar' => 'audio/midi',
            'mpga' => 'audio/mpeg',
            'mp2' => 'audio/mpeg',
            'mp3' => 'audio/mpeg',
            'aif' => 'audio/x-aiff',
            'aiff' => 'audio/x-aiff',
            'aifc' => 'audio/x-aiff',
            'm3u' => 'audio/x-mpegurl',
            'ram' => 'audio/x-pn-realaudio',
            'rm' => 'audio/x-pn-realaudio',
            'rpm' => 'audio/x-pn-realaudio-plugin',
            'ra' => 'audio/x-realaudio',
            'wav' => 'audio/x-wav',
            'pdb' => 'chemical/x-pdb',
            'xyz' => 'chemical/x-xyz',
            'bmp' => 'image/bmp',
            'gif' => 'image/gif',
            'ief' => 'image/ief',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'png' => 'image/png',
            'tiff' => 'image/tiff',
            'tif' => 'image/tif',
            'djvu' => 'image/vnd.djvu',
            'djv' => 'image/vnd.djvu',
            'wbmp' => 'image/vnd.wap.wbmp',
            'ras' => 'image/x-cmu-raster',
            'pnm' => 'image/x-portable-anymap',
            'pbm' => 'image/x-portable-bitmap',
            'pgm' => 'image/x-portable-graymap',
            'ppm' => 'image/x-portable-pixmap',
            'rgb' => 'image/x-rgb',
            'xbm' => 'image/x-xbitmap',
            'xpm' => 'image/x-xpixmap',
            'xwd' => 'image/x-windowdump',
            'igs' => 'model/iges',
            'iges' => 'model/iges',
            'msh' => 'model/mesh',
            'mesh' => 'model/mesh',
            'silo' => 'model/mesh',
            'wrl' => 'model/vrml',
            'vrml' => 'model/vrml',
            'css' => 'text/css',
            'html' => 'text/html',
            'htm' => 'text/html',
            'asc' => 'text/plain',
            'txt' => 'text/plain',
            'rtx' => 'text/richtext',
            'rtf' => 'text/rtf',
            'sgml' => 'text/sgml',
            'sgm' => 'text/sgml',
            'tsv' => 'text/tab-seperated-values',
            'wml' => 'text/vnd.wap.wml',
            'wmls' => 'text/vnd.wap.wmlscript',
            'etx' => 'text/x-setext',
            'xml' => 'text/xml',
            'xsl' => 'text/xml',
            'mpeg' => 'video/mpeg',
            'mpg' => 'video/mpeg',
            'mpe' => 'video/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',
            'mxu' => 'video/vnd.mpegurl',
            'avi' => 'video/x-msvideo',
            'movie' => 'video/x-sgi-movie',
            'ice' => 'x-conference-xcooltalk',
            'wmv' => 'video/x-ms-wmv',
            'wma' => 'audio/x-ms-wma',
            'asf' => 'video/x-msvideo'
        );

        if (isset($mimetypes[$file_extension])) {
            $type = $mimetypes[$file_extension];
        } else {
            $type = 'application/force-download';
        }

        return $type;
    }

    /**
     * Filter illegal file name.
     * @param array $files file names
     * @return array filtered file names
     */
    public static function filter_files($files) {
        $new_files = array();
        $count = count($files);
        for ($i = 0; $i < $count; $i++) {
            $file = $files[$i];
            if (Utility::check_name($file)) {
                array_push($new_files, $file);
            }
        }
        return $new_files;
    }

    /**
     * Filter illegal paths.
     * @param array $paths paths
     * @return array filtered paths
     */
    public static function filter_paths($paths) {
        $new_paths = array();
        $count = count($paths);
        for ($i = 0; $i < $count; $i++) {
            $path = $paths[$i];
            if (Utility::check_path($path)) {
                array_push($new_paths, $path);
            }
        }
        return $new_paths;
    }

    private static function get_name_part($name_part) {
        $suffix_len = 0;
        $suffix = substr($name_part, -1);
        if (strcmp($suffix, ')') == 0) {
            $suffix_len = 1;
            while (1) {
                $suffix = substr($name_part, -($suffix_len + 1), -1);
                if (!is_numeric($suffix)) {
                    break;
                }
                ++$suffix_len;
            }
            $suffix = substr($name_part, -($suffix_len + 1), 1);
            if (strcmp($suffix, '(') == 0) {
                ++$suffix_len;
            } else {
                $suffix_len = 0;
            }
        }
        if ($suffix_len > 0) {
            $name_part = substr($name_part, 0, -($suffix_len));
        }

        return $name_part;
    }

    /**
     * Resolve the same name.
     * @param string $name full path (UTF-8)
     * @return string new full path (UTF-8)
     */
    public static function resolve_same_name($name, $i = 2) {
        $file_name = get_basename($name);
        $dir_name = dirname($name);
        $dot_pos = strrpos($file_name, '.');

        $newname = '';

        $name_part = '';
        $type_part = '';
        if ($dot_pos !== false) {
            $name_part = substr($file_name, 0, $dot_pos);
            $type_part = substr($file_name, $dot_pos + 1, strlen($file_name) - $dot_pos - 1);

            $name_part = Utility::get_name_part($name_part);

            $name_part .= '(' . $i . ')';
            $newname = $name_part . '.' . $type_part;
        } else {
            $name_part = Utility::get_name_part($file_name);
            $newname = $name_part . '(' . $i . ')';
        }

        $newname = $dir_name . '/' . $newname;
        if (file_exists(convert_toplat($newname))) {
            $newname = Utility::resolve_same_name($newname, $i + 1);
        }

        return $newname;
    }

    /**
     * Rename.
     * @param string $oldname old full path (UTF-8)
     * @param string $newname new full path (UTF-8)
     * @param bool $deal_same_name need deal with the same name
     * @return bool
     */
    public static function phpfm_rename($oldname, $newname, $deal_same_name = false) {
        $newname_dir_part = dirname($newname);
        if ($newname_dir_part == $oldname) {
            return false;
        }

        $plat_oldname = convert_toplat($oldname);
        $plat_newname = convert_toplat($newname);
        if ($plat_oldname == $plat_newname) {
            return true;
        }

        // The same name.
        if (file_exists($plat_oldname)) {
            if (file_exists($plat_newname)) {
                if ($deal_same_name) {
                    $newname = Utility::resolve_same_name($newname);
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }

        $plat_newname = convert_toplat($newname);
        return @rename($plat_oldname, $plat_newname);
    }

    /**
     * Copy, deal with the same name, support directory.
     * @param string $oldname old path (UTF-8)
     * @param string $newname new path (UTF-8)
     * @return bool
     */
    public static function phpfm_copy($oldname, $newname) {
        $newname_dir_part = dirname($newname);
        if ($newname_dir_part == $oldname) {
            return false;
        }

        $plat_oldname = convert_toplat($oldname);
        $plat_newname = convert_toplat($newname);

        if (file_exists($plat_newname)) {
            $newname = Utility::resolve_same_name($newname);
        }
        $plat_newname = convert_toplat($newname);

        if (is_dir($plat_oldname)) {
            return xcopy($plat_oldname, $plat_newname); // xcopy for directory.
        } else {
            return @copy($plat_oldname, $plat_newname);
        }
    }

    /**
     * Remove directory.
     * @param string $path
     * @return bool
     */
    public static function phpfm_rmdir($path) {
        if (!$dh = @opendir($path)) {
            return false;
        }

        $success = true;
        while (false !== ($item = readdir($dh))) {
            if ($item != '.' && $item != '..') {
                $folder_content = $path . '/' . $item;

                if (is_file($folder_content)) {
                    $success = $success && @unlink($folder_content);
                } elseif (is_dir($folder_content)) {
                    $success = $success && Utility::phpfm_rmdir($folder_content);
                }
            }
        }
        closedir($dh);

        $success = $success && @rmdir($path);

        return $success;
    }

    /**
     * Better move_uploaded_file.
     * @param string $filename (UTF-8)
     * @param string $destination (UTF-8)
     * @return bool the same to move_uploaded_file
     */
    public static function phpfm_move_uploaded_file($filename, $destination) {
        $plat_destination = convert_toplat($destination);
        if (file_exists($plat_destination)) {
            $plat_destination = convert_toplat(Utility::resolve_same_name($destination));
        }

        $uploaded_file_src = convert_toplat($filename);
        //print_r($uploaded_file_src . ' -> ' . $plat_destination);
        return move_uploaded_file($uploaded_file_src, $plat_destination);
    }

    /**
     * Read MessageBoard from SESSION.
     * @param bool $need_new create new when not exists, default is true
     * @return MessageBoard message board or null
     */
    public static function get_messageboard($need_new = true) {
        if ($need_new) {
            $messageboard = isset($_SESSION['messageboard']) ? $_SESSION['messageboard'] : new MessageBoard();
            $_SESSION['messageboard'] = $messageboard; // put MessageBoard into SESSION.
        } else {
            $messageboard = isset($_SESSION['messageboard']) ? $_SESSION['messageboard'] : null;
        }

        return $messageboard;
    }

    /**
     * Read ClipBoard from SESSION.
     * @param bool $need_new create new when not exists, default is true
     * @return ClipBoard clipboard or null
     */
    public static function get_clipboard($need_new = true) {
        if ($need_new) {
            $clipboard = isset($_SESSION['clipboard']) ? $_SESSION['clipboard'] : new ClipBoard();
            $_SESSION['clipboard'] = $clipboard; // put ClipBoard into SESSION.
        } else {
            $clipboard = isset($_SESSION['clipboard']) ? $_SESSION['clipboard'] : null;
        }

        return $clipboard;
    }

    private static function allow_to($do) {
        if (!USERMNG) {
            return true;
        }

        $modify_permission = $do;
        if ($modify_permission == User::$NOBODY) {
            return true;
        }

        return false;
    }

    /**
     * Display user.
     */
    public static function display_user() {
        if (is_mobile_browser() || !USERMNG) {
            return '';
        }
    }

}

?>
