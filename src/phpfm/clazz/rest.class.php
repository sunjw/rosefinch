<?php
require_once dirname(__FILE__) . '/../log/log.func.php';
require_once dirname(__FILE__) . '/../inc/defines.inc.php';
require_once dirname(__FILE__) . '/../inc/common.inc.php';
require_once dirname(__FILE__) . '/../inc/gettext.inc.php';
require_once 'utility.class.php';
require_once 'clipboard.class.php';
require_once 'messageboard.class.php';

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
        $this->messageboard = Utility::get_messageboard();
        $this->clipboard = Utility::get_clipboard(false);
        $this->api = post_query('api');
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
            get_logger()->error('Not "return" found in request.');
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
        $api_v1_base = 'api/v1/';
        if (starts_with($this->api, $api_v1_base)) {
            $api = substr($this->api, strlen($api_v1_base));
            switch ($api) {
                case 'cut':
                case 'copy':
                    $this->handle_cut_copy();
                    break;
                case 'delete':
                    $this->handle_delete();
                    break;
                case 'newfolder':
                    $this->handle_newfolder();
                    break;
                case 'paste':
                    $this->handle_paste();
                    break;
                case 'rename':
                    $this->handle_rename();
                    break;
                case 'upload':
                    $this->handle_upload();
                    break;
                default:
                    get_logger()->error('Wrong API request: [' . $this->api . ']');
                    $this->response_json_400();
                    break;
            }
        } else {
            get_logger()->error('Wrong API request: [' . $this->api . ']');
            $this->response_json_400();
        }
    }

    /**
     * Cut and copy.
     */
    private function handle_cut_copy()
    {
        if ($this->clipboard == null) {
            get_logger()->error('Rest->clipboard is null.');
            $this->response_json_500();
            return;
        }

        if (!Utility::allow_to_modify()) {
            $message = 'Not allowed to cut or copy files.';
            get_logger()->warning($message);
            $this->messageboard->set_message(_($message), 2);
            $this->response_json_400();
            return;
        }

        $items = post_query('items');

        $items = explode('|', $items);
        $items = Utility::filter_paths($items);
        //print_r($files);

        $this->clipboard->set_items($this->api, $items);
        $message = 'Add items to clipboard: [' . join(',', $items) . ']';
        get_logger()->info($message);

        if ($this->clipboard->have_items()) {
            $message = _('Add items to clipboard:') . '&nbsp;<br />';
            $message .= htmlentities_utf8((join('***', $items)));
            $message = str_replace('***', '<br />', $message);
            $this->messageboard->set_message($message);
            $resp_obj = new RestRet();
            $resp_obj->message = $message;
            $this->response_json($resp_obj);
            return;
        }

        get_logger()->error('No item in clipboard.');
        $this->response_json_500();
    }

    /**
     * Delete.
     */
    private function handle_delete()
    {
        if (!Utility::allow_to_modify()) {
            $this->messageboard->set_message(_('Please login to delete files.'), 2);
            echo 'ok';
            return;
        }

        $items = post_query('items');
        $items = explode('|', $items);
        $items = Utility::filter_paths($items);

        $message = '';

        $count = count($items);
        for ($i = 0; $i < $count; $i++) {
            $success = false;
            $item = $items[$i];
            $sub_dir = dirname($item);
            $path = $this->files_base_dir . $item;
            get_logger()->info('try to delete: ' . $path);
            $message .= (_('Delete') . ' ' . htmlentities_utf8($item) . ' ');
            $path = convert_toplat($path);
            if (file_exists($path)) {
                if (is_dir($path)) {
                    $success = Utility::phpfm_rmdir($path);
                } else {
                    $success = @unlink($path);
                }
            }
            if ($success === true) {
                $message .= (_('succeed') . '<br />');
                $stat = 1;
            } else {
                $message .= ('<strong>' . _('failed') . '</strong><br />');
                $stat = 2;
            }
        }

        $this->messageboard->set_message($message, $stat);

        $this->response_redirect(false);
    }

    /**
     * Paste.
     */
    private function handle_paste()
    {
        if (!Utility::allow_to_modify()) {
            $this->messageboard->set_message(_('Please login to paste files.'), 2);
            echo 'ok';
            return;
        }
        $target_subdir = rawurldecode(post_query('subdir'));

        if ($this->clipboard != null) {
            $this->clipboard->paste($target_subdir);
        }

        //print_r($_GET);

        echo 'ok';
    }

    /**
     * New folder.
     */
    private function handle_newfolder()
    {
        if (!Utility::allow_to_modify()) {
            $this->messageboard->set_message(_('Please login to make a new folder.'), 2);
            $this->response_redirect(false);
        }

        $sub_dir = rawurldecode(post_query('subdir'));
        $name = post_query('newname');

        $success = false;
        if (false === strpos($sub_dir, '..') && Utility::check_name($name)) // filter
        {
            $name = $this->files_base_dir . $sub_dir . $name;
            get_logger()->info('mkdir: ' . $name);
            $name = convert_toplat($name);
            if (!file_exists($name)) {
                $success = @mkdir($name);
            }
        }

        if ($success === true) {
            $this->messageboard->set_message(
                _('Make new folder:') . '&nbsp;' . htmlentities_utf8(post_query('newname')) . '&nbsp;' . _('succeed'),
                1);
        } else {
            $this->messageboard->set_message(
                _('Make new folder:') . '&nbsp;' . htmlentities_utf8(post_query('newname')) . '&nbsp;<strong>' . _('failed') . '</strong>',
                2);
        }

        $this->response_redirect(false);
    }

    /**
     * Rename.
     */
    private function handle_rename()
    {
        if (!Utility::allow_to_modify()) {
            $this->messageboard->set_message(_('Please login to rename file.'), 2);
            $this->response_redirect(false);
        }

        //$sub_dir = rawurldecode(post_query('subdir'));
        $oldpath = post_query('renamePath');
        $sub_dir = '';
        if (strrpos($oldpath, '/') != false) {
            $sub_dir = substr($oldpath, 0, strrpos($oldpath, '/') + 1);
        }

        $oldname = post_query('oldname');
        $newname = post_query('newname');

        $success = false;
        if (false === strpos($sub_dir, '..') &&
            Utility::check_name($newname) && Utility::check_name($oldname)) {
            $oldname = $this->files_base_dir . $sub_dir . $oldname;
            $newname = $this->files_base_dir . $sub_dir . $newname;

            get_logger()->info('Try to rename: ' . $oldname . ' to ' . $newname);

            $success = Utility::phpfm_rename($oldname, $newname, false);

        }
        if ($success === true) {
            $this->messageboard->set_message(
                sprintf(_('Rename %s to %s ') . _('succeed'), htmlentities_utf8(post_query('oldname')), htmlentities_utf8(post_query('newname'))),
                1);
        } else {
            $this->messageboard->set_message(
                sprintf(_('Rename %s to %s ') . ' <strong>' . _('failed') . '<strong>', htmlentities_utf8(post_query('oldname')), htmlentities_utf8(post_query('newname'))),
                2);
        }

        $this->response_redirect(false);
    }

    /**
     * Upload.
     */
    private function handle_upload()
    {
        if (!Utility::allow_to_modify()) {
            $this->messageboard->set_message(_('Please login to upload file.'), 2);
            $this->response_redirect(false);
        }

        $used_ajax = post_query('ajax') == 'ajax';
        $sub_dir = rawurldecode(post_query('subdir'));
        //get_logger()->info('post_query='.$post_subdir);
        //get_logger()->info('sub_dir='.$sub_dir);

        if (isset($_FILES['uploadFile'])) {
            if (is_array($_FILES['uploadFile']['name'])) {
                // multi upload
                $upload_files = $_FILES['uploadFile'];
                $files_count = count($upload_files['name']);
                $multi_result = true;
                for ($i = 0; $i < $files_count; ++$i) {
                    $uploadfile = $this->files_base_dir . $sub_dir . $upload_files['name'][$i];
                    //print_r($upload_files['tmp_name']);
                    if (Utility::phpfm_move_uploaded_file($upload_files['tmp_name'][$i], $uploadfile)) {
                        get_logger()->info('upload success: ' . $uploadfile);
                    } else {
                        $multi_result = false;
                        get_logger()->info('upload failed: ' . $uploadfile);
                    }
                }

                if ($multi_result) {
                    $this->messageboard->set_message(
                        _('Upload files') . ' ' . _('succeed'),
                        1);
                } else {
                    $this->messageboard->set_message(
                        _('Upload some files') . ' <strong>' . _('failed') . '<strong>',
                        2);
                }
            } else {
                // single upload
                $uploadfile = $this->files_base_dir . $sub_dir . $_FILES['uploadFile']['name'];

                if (Utility::phpfm_move_uploaded_file($_FILES['uploadFile']['tmp_name'], $uploadfile)) {
                    $this->messageboard->set_message(
                        _('Upload') . ':&nbsp;' . $_FILES['uploadFile']['name'] . '&nbsp;' . _('succeed'),
                        1);
                    get_logger()->info('upload success: ' . $uploadfile);
                } else {
                    $this->messageboard->set_message(
                        _('Upload') . ':&nbsp;' . $_FILES['uploadFile']['name'] . ' <strong>' . _('failed') . '<strong>',
                        2);
                    get_logger()->info('upload failed: ' . $uploadfile);
                }
            }
        }

        if ($used_ajax) {
            echo 'ok';
        } else {
            $this->response_redirect(false);
        }
    }

}

?>
