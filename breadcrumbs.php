<?php

    require_once 'coalescence/utils.php';

    $crumbs = array(
        array(
            'name' => get_bloginfo('name'),
            'href' => get_option('home'),
            'skip_separator' => true,
        )
    );

    if (is_category()) {
        array_push($crumbs, array(
            'name' => get_the_category(),
            'href' => get_the_category(get_the_ID()),
            'skip_separator' => true,
        ));
    }

    if (is_single() || is_page()) {
        array_push($crumbs, array(
            'name' => get_the_title(),
            'href' => get_permalink(get_the_ID()),
            'skip_separator' => true,
        ));
    }

?>

<div id="breadcrumbs">
    <ul id="breadcrumbs">
        <?php foreach ($crumbs as $crumb) { ?>
            <li>
                <?php if (!array_get($crumb, 'skip_separator', false)) { ?> <span class="separator">»</span> <?php } ?>
                <a href="<?php echo($crumb['href']); ?>"><?php echo($crumb['name']); ?></a>
            </li>
        <?php } ?>
    </ul>
</div>
