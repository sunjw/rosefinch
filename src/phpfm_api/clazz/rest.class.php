<?php
require_once dirname(__FILE__) . '/../log/log.func.php';
require_once dirname(__FILE__) . '/../inc/defines.inc.php';
require_once dirname(__FILE__) . '/../inc/common.inc.php';
require_once dirname(__FILE__) . '/../inc/gettext.inc.php';
require_once 'utility.class.php';
require_once 'clipboard.class.php';
require_once 'messageboard.class.php';
require_once 'filemanager.class.php';

/**
 * Rest API return object Class.
 */
class RestRet implements JsonSerializable
{
    public $code; // 0: OK, 400: request error, 500: internal error.
    public $message; // OK: '', error: 'some message'.
    public $data; // data array.

    function __construct($code = 0, $message = '', $data = [])
    {
        $this->code = $code;
        $this->message = $message;
        $this->data = $data;
    }

    public function jsonSerialize()
    {
        return [
            'code' => $this->code,
            'message' => $this->message,
            'data' => $this->data
        ];
    }
}

/**
 * Rest API Class.
 * 2009-10-7
 * @author Sun Junwen
 *
 */
class Rest
{
    private $files_base_dir;
    private $messageboard;
    private $clipboard;
    private $api;

    function __construct()
    {
        $this->files_base_dir = Utility::get_file_base_dir();
        $this->messageboard = Utility::get_messageboard(false);
        $this->clipboard = Utility::get_clipboard(false);
        $this->api = get_query('api');
    }

    /**
     * Remove prefix of api.
     * @param string $api
     * @param string $prefix
     * @return string api with prefix removed
     */
    private function api_chain($api, $prefix)
    {
        return substr($api, strlen($prefix));
    }

    /**
     * Response with 302 redirect.
     * Read "return" from request, rawurldecode and jump.
     * @param bool $from_get read "return" from GET, default is true
     */
    private function response_redirect($from_get = true)
    {
        if (post_query('noredirect') != '') {
            return;
        }

        if ($from_get) {
            $return_url = rawurldecode(get_query('return'));
        } else {
            $return_url = rawurldecode(post_query('return'));
        }

        if ($return_url == '') {
            get_logger()->error('response_redirect, no "return" found in request.');
            response_400();
        }

        redirect($return_url);
    }

    private function response_json($resp_obj)
    {
        header('Content-Type: application/json');
        echo json_encode($resp_obj);
        exit;
    }

    private function response_json_500()
    {
        $resp_obj = new RestRet(500, 'Internal Server Error.');
        $this->response_json($resp_obj);
    }

    private function response_json_400()
    {
        $resp_obj = new RestRet(400, 'Bad request.');
        $this->response_json($resp_obj);
    }

    /**
     * Handle API request.
     */
    public function handle_request()
    {
        $api_v1_prefix = 'api/v1/';
        if (starts_with($this->api, $api_v1_prefix)) {
            $api = $this->api_chain($this->api, $api_v1_prefix);

            $fm_prefix = 'fm/';
            $sys_prefix = 'sys/';
            if (starts_with($api, $fm_prefix)) {
                $api = $this->api_chain($api, $fm_prefix);
                $this->handle_fm_request($api);
            } elseif (starts_with($api, $sys_prefix)) {
                $api = $this->api_chain($api, $sys_prefix);
                $this->handle_sys_request($api);
            } else {
                get_logger()->error('handle_request, wrong API request: [' . $this->api . '].');
                $this->response_json_400();
                return;
            }
        } else {
            get_logger()->error('handle_request, wrong API request: [' . $this->api . '].');
            $this->response_json_400();
        }
    }

    private function handle_fm_request($api)
    {
        switch ($api) {
            case 'ls':
                $this->handle_list();
                break;
            case 'cut':
            case 'copy':
                $this->handle_cut_copy(($api == 'cut'));
                break;
            case 'paste':
                $this->handle_paste();
                break;
            case 'delete':
                $this->handle_delete();
                break;
            case 'newfolder':
                $this->handle_newfolder();
                break;
            case 'rename':
                $this->handle_rename();
                break;
            case 'upload':
                $this->handle_upload();
                break;
            default:
                get_logger()->error('handle_fm_request, wrong API request: [' . $this->api . '].');
                $this->response_json_400();
                break;
        }
    }

    /*
     * List directory.
     */
    private function handle_list()
    {
        $file_manager = new FileManager('index.php');
        $main_list = $file_manager->get_main_list();

        $resp_obj = new RestRet();

        $resp_obj->data['sort'] = array();
        $resp_obj->data['sort']['type'] = $file_manager->get_sort_type();
        $resp_obj->data['sort']['order'] = $file_manager->get_sort_order();

        $current_path_array = $file_manager->get_current_path_array();
        $resp_obj->data['current_path'] = $current_path_array;

        $main_list_count = count($main_list);
        for ($i = 0; $i < $main_list_count; $i++) {
            $main_list[$i]['mtime'] = $main_list[$i]['stat']['mtime'];
            $main_list[$i]['img_html'] = Utility::get_icon($main_list[$i]['type'], 36);
            $main_list[$i]['mtime_str'] = timetotimestr($main_list[$i]['mtime']);
            unset($main_list[$i]['stat']);
            unset($main_list[$i]['path']);
        }
        $resp_obj->data['main_list'] = $main_list;

        $this->response_json($resp_obj);
    }

    /**
     * Cut and copy.
     */
    private function handle_cut_copy($is_cut)
    {
        if ($this->clipboard == null) {
            get_logger()->error('handle_cut_copy, Rest->clipboard is null.');
            $this->response_json_500();
            return;
        }

        if (!Utility::allow_to_modify()) {
            get_logger()->warning('handle_cut_copy, not allowed to cut or copy.');
            $this->response_json_400();
            return;
        }

        $req_obj = read_body_json();
        $items = $req_obj['items'];
        $items = Utility::filter_paths($items);
        //print_r($files);

        $this->clipboard->set_items(($is_cut ? 'cut' : 'copy'), $items);
        $message = 'handle_cut_copy, add items to clipboard: [' . join(', ', $items) . '].';
        get_logger()->info($message);

        if ($this->clipboard->has_items()) {
            $message = _('Add items to clipboard:') . '&nbsp;<br />';
            $message .= htmlentities_utf8((join('***', $items)));
            $message = str_replace('***', '<br />', $message);
            $resp_obj = new RestRet();
            $resp_obj->message = $message;
            $this->response_json($resp_obj);
            return;
        }

        get_logger()->error('handle_cut_copy, no item in clipboard.');
        $this->response_json_500();
    }

    /**
     * Paste.
     */
    private function handle_paste()
    {
        if ($this->clipboard == null) {
            get_logger()->error('handle_paste, Rest->clipboard is null.');
            $this->response_json_500();
            return;
        }

        if (!Utility::allow_to_modify()) {
            get_logger()->warning('handle_paste, not allowed to paste.');
            $this->response_json_400();
            return;
        }

        $req_obj = read_body_json();
        $target_subdir = rawurldecode($req_obj['subdir']);
        $paste_result = $this->clipboard->paste($target_subdir);

        $message = '';
        $oper_str = ($paste_result['oper'] == 'cut') ? 'Cut' : 'Copy';
        $items = $paste_result['items'];
        foreach ($items as $item => $result) {
            $message .= (_($oper_str) . ' ' . htmlentities_utf8($item) . ' ');
            if ($result) {
                $message .= (_('succeed') . '<br />');
            } else {
                $message .= ('<strong>' . _('failed') . '</strong><br />');
            }
        }

        //print_r($_GET);

        $resp_obj = new RestRet();
        $resp_obj->message = $message;
        $this->response_json($resp_obj);
    }

    /**
     * Delete.
     */
    private function handle_delete()
    {
        if (!Utility::allow_to_modify()) {
            get_logger()->warning('handle_delete, not allowed to delete.');
            $this->response_json_400();
            return;
        }

        $req_obj = read_body_json();
        $items = $req_obj['items'];
        $items = Utility::filter_paths($items);

        $code = 0;
        $message = '';

        $delete_result = true;
        $count = count($items);
        for ($i = 0; $i < $count; $i++) {
            $success = false;
            $item = $items[$i];
            //$sub_dir = dirname($item);
            $path = $this->files_base_dir . $item;
            get_logger()->info('handle_delete, try to delete: [' . $path . '].');
            $path = convert_toplat($path);
            if (file_exists($path)) {
                if (is_dir($path)) {
                    $success = Utility::phpfm_rmdir($path);
                } else {
                    $success = @unlink($path);
                }
            }
            if ($success != true) {
                $delete_result = false;
            }
        }

        if ($delete_result) {
            $code = 0;
            $message = 'Files/folders deleted successfully.';
        } else {
            $code = 500;
            $message = 'Some files/folders delete failed.';
        }

        $resp_obj = new RestRet();
        $resp_obj->code = $code;
        $resp_obj->message = $message;
        $this->response_json($resp_obj);
    }

    /**
     * New folder.
     */
    private function handle_newfolder()
    {
        if (!Utility::allow_to_modify()) {
            get_logger()->warning('handle_newfolder, not allowed to make new folder.');
            $this->response_json_400();
            return;
        }

        $req_obj = read_body_json();
        $sub_dir = rawurldecode($req_obj['subdir']);
        $name_req = $req_obj['newname'];
        $name = $name_req;

        $success = false;
        if (Utility::check_path($sub_dir, true) && Utility::check_name($name)) {
            $name = $this->files_base_dir . $sub_dir . $name;
            get_logger()->info('handle_newfolder, try to make new folder: [' . $name . '].');
            $name = convert_toplat($name);
            if (!file_exists($name)) {
                $success = @mkdir($name);
            }
        } else {
            get_logger()->error('handle_newfolder, illegal name: sub_dir=[' . $sub_dir . '], name=[' . $name . '].');
        }

        $name_html = htmlentities_utf8($name_req, true);
        $resp_obj = new RestRet();
        if ($success) {
            $resp_obj->message = 'Directory "' . $name_html . '" created successfully.';
        } else {
            $resp_obj->code = 400;
            $resp_obj->message = 'Directory "' . $name_html . '" create failed.';
        }

        $this->response_json($resp_obj);
    }

    /**
     * Rename.
     */
    private function handle_rename()
    {
        if (!Utility::allow_to_modify()) {
            get_logger()->warning('handle_rename, not allowed to rename.');
            $this->response_json_400();
            return;
        }

        $req_obj = read_body_json();
        //$sub_dir = rawurldecode(post_query('subdir'));
        $oldpath = $req_obj['renamePath'];
        $sub_dir = '';
        if (strrpos($oldpath, '/') != false) {
            $sub_dir = substr($oldpath, 0, strrpos($oldpath, '/') + 1);
        }

        $oldname_req = $req_obj['oldname'];
        $oldname = $oldname_req;
        $newname_req = $req_obj['newname'];
        $newname = $newname_req;

        $success = false;
        if (Utility::check_path($sub_dir, true) &&
            Utility::check_name($newname) && Utility::check_name($oldname)) {
            $oldname = $this->files_base_dir . $sub_dir . $oldname;
            $newname = $this->files_base_dir . $sub_dir . $newname;

            get_logger()->info('handle_rename, try to rename: [' . $oldname . '] to [' . $newname . '].');
            $success = Utility::phpfm_rename($oldname, $newname, false);
        } else {
            get_logger()->error('handle_rename, illegal name: sub_dir=[' . $sub_dir . '], oldname=[' . $oldname . '], newname=[' . $newname . '].');
        }

        $message = '';
        if ($success) {
            $message = sprintf(_('Rename %s to %s ') . _('succeed'),
                htmlentities_utf8($oldname_req), htmlentities_utf8($newname_req));
        } else {
            $message = sprintf(_('Rename %s to %s ') . ' <strong>' . _('failed') . '<strong>',
                htmlentities_utf8($oldname_req), htmlentities_utf8($newname_req));
        }

        $resp_obj = new RestRet();
        $resp_obj->message = $message;
        $this->response_json($resp_obj);
    }

    /**
     * Upload.
     */
    private function handle_upload()
    {
        $is_ajax = post_query('ajax') == 'ajax';
        $sub_dir = rawurldecode(post_query('subdir'));

        $code = 0;
        $message = '';

        //get_logger()->info('handle_upload, post_query='.$post_subdir);
        //get_logger()->info('handle_upload, sub_dir='.$sub_dir);

        if (!Utility::allow_to_modify()) {
            if (!$is_ajax) {
                $this->messageboard->set_message(_('Please login to upload file.'), 400);
                $this->response_redirect(false);
            } else {
                get_logger()->warning('handle_upload, not allowed to upload.');
                $this->response_json_400();
            }
            return;
        }

        if (isset($_FILES['uploadFile'])) {
            $upload_result = false;
            if (is_array($_FILES['uploadFile']['name'])) {
                // multi upload
                $upload_files = $_FILES['uploadFile'];
                $files_count = count($upload_files['name']);
                $multi_result = true;
                for ($i = 0; $i < $files_count; ++$i) {
                    $uploadfile = $this->files_base_dir . $sub_dir . $upload_files['name'][$i];
                    //print_r($upload_files['tmp_name']);
                    if (Utility::phpfm_move_uploaded_file($upload_files['tmp_name'][$i], $uploadfile)) {
                        get_logger()->info('handle_upload, upload success: [' . $uploadfile . '].');
                    } else {
                        $multi_result = false;
                        get_logger()->error('handle_upload, upload failed: [' . $uploadfile . '].');
                    }
                }

                $upload_result = $multi_result;
            } else {
                // single upload
                $uploadfile = $this->files_base_dir . $sub_dir . $_FILES['uploadFile']['name'];
                $upload_result = Utility::phpfm_move_uploaded_file($_FILES['uploadFile']['tmp_name'], $uploadfile);
                if ($upload_result) {
                    get_logger()->info('handle_upload, upload success: [' . $uploadfile . '].');
                } else {
                    get_logger()->error('handle_upload, upload failed: [' . $uploadfile . '].');
                }
            }

            if ($upload_result) {
                $code = 0;
                $message = 'Files uploaded successfully.';
                if (!$is_ajax) {
                    $this->messageboard->set_message($message);
                }
            } else {
                $code = 500;
                $message = 'Some files upload failed.';
                if (!$is_ajax) {
                    $this->messageboard->set_message($message, 500);
                }
            }
        } else {
            get_logger()->error('handle_upload, no $_FILES[\'uploadFile\'].');
            $code = 400;
            $message = 'No file upload.';
            if (!$is_ajax) {
                $this->messageboard->set_message($message, 500);
            }
        }

        if ($is_ajax) {
            $resp_obj = new RestRet();
            $resp_obj->code = $code;
            $resp_obj->message = $message;
            $this->response_json($resp_obj);
        } else {
            $this->response_redirect(false);
        }
    }

    private function handle_sys_request($api)
    {
        switch ($api) {
            case 'message':
                $this->handle_message();
                break;
            default:
                get_logger()->error('handle_sys_request, wrong API request: [' . $this->api . '].');
                $this->response_json_400();
                break;
        }
    }

    /**
     * Get message.
     */
    private function handle_message()
    {
        if ($this->messageboard == null) {
            get_logger()->error('handle_message, Rest->messageboard is null.');
            $this->response_json_500();
            return;
        }

        $resp_obj = new RestRet();

        $message = '';
        $stat = 0;
        if ($this->messageboard->has_message()) {
            $this->messageboard->get_message($message, $stat);
            $resp_obj->code = $stat;
            $resp_obj->message = $message;
        }

        $this->response_json($resp_obj);
    }

}

?>
