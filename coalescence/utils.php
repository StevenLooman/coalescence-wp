<?php

    function array_get($array, $key, $default=null) {
        if (!array_key_exists($key, $array) || $array[$key] === null) {
            return $default;
        }

        return $array[$key];
    }


    class CoaMessage {
        private $class;
        private $message;

        public function __construct($class, $message) {
            $this->class = $class;
            $this->message = $message;

            add_action('admin_notices', array($this, 'show_message'));
        }

        public function show_message() {
            ?>
                <div class="<?= $this->class ?>">
                    <p>
                        <?= $this->message ?>
                    </p>
                </div>
            <?php
        }
    }

?>
