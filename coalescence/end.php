<?php

    require_once 'utils.php';
    require_once 'engine.php';


    // save output to buffer
    $buffer = ob_get_contents();
    ob_end_clean();

    // don't transform if coalescence.off is an argument
    if (array_get($_GET, 'coalescence_off') != null) {
        echo($buffer);
        return;
    }

    // load/parse content
    $content = new DOMDocument();
    libxml_use_internal_errors(true);
    # fix encoding problems by forcing UTF-8 encoding
    $content->loadHTML('<?xml encoding="UTF-8">' . $buffer);
    libxml_use_internal_errors(false);

    // load and parse rules
    $rules = get_option('coalescence_rules');
    $rules = simplexml_load_string($rules);

    // execute rules
    $engine = new CoalescenceEngine($content, $rules);
    $page = $engine->execute_rules();

    // dump results
    echo($page->saveHTML());

?>
