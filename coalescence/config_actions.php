<?php

    require_once 'utils.php';

    new CoaActionSaveRules();
    new CoaActionUploadTheme();
    new CoaActionUploadRules();
    new CoaActionResetRules();
    new CoaActionExportRules();


    class CoaActionSaveRules {
        public function __construct() {
            add_action('admin_init', array($this, 'save_rules'));
        }


        // Rule saving
        public function save_rules() {
            if (isset($_POST['coalescence_save_rules'])) {
                if (!check_admin_referer('coalescence_save_rules_nonce', 'coalescence_save_rules_nonce')) {
                    return;
                }

                $rules_data = $_POST['rules'];

                # unescape XML
                $rules_data = stripslashes($rules_data);

                # ensure valid XML/rules.xml file
                $rules = new DOMDocument();
                $rules->formatOutput = true;
                $rules->preserveWhiteSpace = false;
                $success = $rules->loadXML($rules_data);

                if ($success === false) {
                    return new CoaMessage('error', 'Unable read rules XML');
                } else {
                    update_option('coalescence_rules', $rules_data);
                    return new CoaMessage('updated', 'Rules updated');
                }
            }
        }
    }


    class CoaActionUploadTheme {
        private $theme_dir;

        public function __construct() {
            $this->theme_dir = get_template_directory() . '/theme/';

            add_action('admin_init', array($this, 'upload_theme'));
        }


        // Theme zip uploading
        public function upload_theme() {

            if (isset($_POST['coalescence_upload_theme']) && isset($_FILES['theme_zip'])) {
                if (!check_admin_referer('coalescence_upload_theme_nonce', 'coalescence_upload_theme_nonce')) {
                    return;
                }

                $ret = $this->handle_theme_upload($_FILES['theme_zip']);

                if (isset($ret['error'])) {
                    return new CoaMessage('error', $ret['error']);
                }

                if (isset($ret['success'])) {
                    $rules = get_option('coalescence_rules');
                    $rules = simplexml_load_string($rules);
                    $rule = $rules->xpath('/rules/rules/theme[@name="default"]');
                    if ($rule) {
                        if (file_exists(get_template_directory() . '/theme/index.htm')) {
                            $rule[0]['href'] = 'theme/index.htm';
                        }
                        if (file_exists(get_template_directory() . '/theme/index.html')) {
                            $rule[0]['href'] = 'theme/index.html';
                        }
                        update_option('coalescence_rules', $rules->asXML());
                    }
                    return new CoaMessage('updated', $ret['success']);
                }
            }
        }

        private function handle_theme_upload($file) {
            function getUploadErrorMessage($error) {
                $uploadErrorMessages = array(
                    UPLOAD_ERR_INI_SIZE   => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
                    UPLOAD_ERR_FORM_SIZE  => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
                    UPLOAD_ERR_PARTIAL    => 'The uploaded file was only partially uploaded',
                    UPLOAD_ERR_NO_FILE    => 'No file was uploaded',
                    UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
                    UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                    UPLOAD_ERR_EXTENSION  => 'File upload stopped by extension',
                );

                if (isset($uploadErrorMessages[$error])) {
                    return $uploadErrorMessages[$error];
                }

                return 'Unknown upload error';
            }
            function tempdir($dir = false) {
                $tempfile = tempnam(sys_get_temp_dir(), '');
                if (file_exists($tempfile)) {
                    unlink($tempfile);
                }
                mkdir($tempfile);
                if (is_dir($tempfile)) {
                    return $tempfile;
                }
            }
            function rrmdir($dir, $include_self = true) {
                foreach (new RecursiveDirectoryIterator($dir) as $entry) {
                    if ($entry->getFilename() == '.' || $entry->getFilename() == '..') {
                        continue;
                    }

                    $file = $entry->getPathname();
                    if (is_dir($file)) {
                        rrmdir($file);
                    } else {
                        unlink($file);
                    }
                }
                if ($include_self) {
                    rmdir($dir);
                }
            }
            function rcopy($source_dir, $target_dir) {
                foreach (new RecursiveDirectoryIterator($source_dir) as $entry) {
                    if ($entry->getFilename() == '.' || $entry->getFilename() === '..') {
                        continue;
                    }

                    $file = $entry->getFilename();
                    $target = $target_dir . '/' . $file;
                    if (is_dir($entry->getPathName())) {
                        mkdir($target);
                        rcopy($source_dir . '/' . $file, $target_dir . '/' . $file);
                    } else {
                        copy($entry->getPathname(), $target);
                    }
                }
            }

            if ($file['error'] !== UPLOAD_ERR_OK) {
                $message = getUploadErrorMessage($file['error']);
                return array('error' => 'There was an error during the uploading process: ' . $message);
            }

            if ($file['type'] !== 'application/zip' || substr($file['name'], -4) !== '.zip') {
                return array('error' => 'Unrecognized archive.');
            }

            // unpack file
            $zip = new ZipArchive;
            $res = $zip->open($file['tmp_name']);
            if ($res !== true) {
                return array('error' => 'Unable to open zip archive.');
            }
            $tmp_dir = tempdir();
            $res = $zip->extractTo($tmp_dir);
            if ($res !== true) {
                rrmdir($tmp_dir);
                return array('error' => 'Unable to extract zip archive.');
            }

            // find index.html
            $base_dir = false;
            foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($tmp_dir)) as $entry) {
                if ($entry->getFilename() === 'index.html' || $entry->getFilename() === 'index.htm') {
                    $base_dir = dirname($entry->getPathname());
                    break;
                }
            }
            if (!$base_dir) {
                rrmdir($tmp_dir);
                return array('error' => 'Could not find an index file.');
            }

            // remove all files in html-theme directory
            rrmdir($this->theme_dir, false);

            // copy all files in directory of index.html and sub-folders to theme/
            rcopy($base_dir, $this->theme_dir);

            // clean up
            rrmdir($tmp_dir);

            return array('success' => 'The HTML theme has been uploaded.');
        }
    }


    class CoaActionUploadRules {
        public function __construct() {
            add_action('admin_init', array($this, 'upload_rules'));
        }

        // Rule file uploading
        public function upload_rules() {
            if (isset($_POST['coalescence_upload_rules']) && isset($_FILES['rules_xml'])) {
                if (!check_admin_referer('coalescence_upload_rules_nonce', 'coalescence_upload_rules_nonce')) {
                    return;
                }

                $ret = $this->handle_rules_upload($_FILES['rules_xml']);
                if (isset($ret['error'])) {
                    return new CoaMessage('error', $ret['error']);
                }
                if (isset($ret['success'])) {
                    return new CoaMessage('updated', $ret['success']);
                }
            }
        }

        private function handle_rules_upload($file) {
            function getUploadErrorMessage($error) {
                $uploadErrorMessages = array(
                    UPLOAD_ERR_INI_SIZE   => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
                    UPLOAD_ERR_FORM_SIZE  => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
                    UPLOAD_ERR_PARTIAL    => 'The uploaded file was only partially uploaded',
                    UPLOAD_ERR_NO_FILE    => 'No file was uploaded',
                    UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
                    UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                    UPLOAD_ERR_EXTENSION  => 'File upload stopped by extension',
                );

                if (isset($uploadErrorMessages[$error])) {
                    return $uploadErrorMessages[$error];
                }

                return 'Unknown upload error';
            }
            function isValidRulesFile($file) {
                libxml_use_internal_errors(true);
                $rules = simplexml_load_file($file);
                libxml_use_internal_errors(false);
                if ($rules === false) {
                    return false;
                }
                if (!isset($rules->rules)) {
                    return false;
                }
                return true;
            }

            if ($file['error'] !== UPLOAD_ERR_OK) {
                $message = getUploadErrorMessage($file['error']);
                return array('error' => 'There was an error during the uploading process: ' . $message);
            }

            // try to parse xml file
            if (!isValidRulesFile($file['tmp_name'])) {
                return array('error' => 'The uploaded file does not seem to be a valid rules.xml file containing rules, not saving the file.');
            }

            // save rules
            $rules = file_get_contents($file['tmp_name']);
            update_option('coalescence_rules', $rules);

            return array('success' => 'The uploaded rules.xml has been saved.');
        }
    }


    class CoaActionResetRules {
        public function __construct() {
            add_action('admin_init', array($this, 'default_rules'));
        }

        /*
         * Set default rules, if no rules are set
         */
        public function default_rules() {
            $rules = get_option('coalescence_rules');
            if ($rules === false) {
                $this->_reset_rules();
                return;
            }
            if (isset($_POST['coalescence_reset_rules'])) {
                if (!check_admin_referer('coalescence_reset_rules_nonce', 'coalescence_reset_rules_nonce')) {
                    return;
                }
                $this->_reset_rules();
                return new CoaMessage('updated', 'Rules have been reset.');
            }
        }

        private function _reset_rules() {
            $rules = file_get_contents(get_template_directory() . '/coalescence/rules.xml.default');
            update_option('coalescence_rules', $rules);
        }
    }


    class CoaActionExportRules {
        public function __construct() {
            add_action('admin_init', array($this, 'export_rules'));
        }

        /**
         * Export rules.xml file
         */
        public function export_rules() {
            if (!empty($_GET['coalescence_export_rules'])) {
                $rules = get_option('coalescence_rules');
                $filesize = strlen($rules);

                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename=rules.xml');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . $filesize);

                echo $rules;

                exit;
            }
        }
    }

?>
