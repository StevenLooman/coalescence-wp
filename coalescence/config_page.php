<script type="text/javascript" src="<?= $coalescence_js ?>"></script>
<script type="text/javascript" src="<?= $coalescence_rules_js ?>"></script>

<div class="coalescence_config">
    <div class="data">
        <div id="theme_templates"><?php echo(json_encode($theme_templates)); ?></div>
        <div id="content_templates"><?php echo(json_encode($content_templates)); ?></div>
    </div>

    <h2 class="nav-tab-wrapper">
        <a href="#" data-tab-id="tab-config" class="nav-tab">Configuration</a>
        <a href="#" data-tab-id="tab-upload-theme" class="nav-tab">Upload HTML theme</a>
        <a href="#" data-tab-id="tab-backup-restore" class="nav-tab">Backup and restore</a>
    </h2>

    <div id="tab-config" class="tab-page">
        <h2>Configuration</h2>
        <div class="group">
            <div>
                <label>Orientation</label>
                <button id="selectors-horizontal">Horizontal</button>
                <button id="selectors-vertical">Vertical</button>
            </div>

            <div class="half-width selector-group">
                <h3>Content</h3>
                <div>
                    <select id="content_page">
                        <?php foreach ($pages as $page) { ?>
                            <option value="<?= $page['href'] ?>"><?= $page['title'] ?></option>
                        <?php } ?>
                    </select>
                </div>

                <iframe id="selector_content" class="selector full-width"></iframe>

                <div class="xpath_expr"><label for="selected_content">Selection</label><input type="text" id="selected_content"></div>
            </div>

            <div class="half-width selector-group">
                <h3>Theme</h3>
                <div>
                    <select id="theme_page">
                        <?php foreach ($theme_templates as $page) { ?>
                             <option value="<?= $page['href'] ?>"><?= $page['title'] ?></option>
                        <?php } ?>
                    </select>
                </div>

                <iframe id="selector_theme" class="selector full-width"></iframe>

                <div class="xpath_expr"><label for="selected_theme">Selection</label><input type="text" id="selected_theme"></div>
            </div>
        </div>

        <div class="group">
        </div>

        <div class="group">
            <h3>Rules</h3>

            <label>Template</label>
            <select id="rules-template-selector">
            </select>

            <div class="group">
                <label>New rule</label>
                <button id="replace">Replace</button>
                <button id="drop_content">Drop from left</button>
                <button id="drop_theme">Drop from right</button>
            </div>

            <div id="rules" class="group rules"></div>
        </div>

        <div class="group" id="templates">
            <h3>Wordpress template to HTML file mapping</h3>
        </div>

        <div class="group">
            <form action="" method="post">
                <?php wp_nonce_field('coalescence_save_rules_nonce', 'coalescence_save_rules_nonce'); ?>
                <textarea style="display: none;" name="rules"><?= $rules ?></textarea>
                <?php submit_button('Save', 'primary', 'coalescence_save_rules'); ?>
            </form>
        </div>
    </div>

    <div id="tab-upload-theme" class="tab-page">
        <h2>Upload HTML theme</h2>
        <p>Choose a zip file containing the HTML theme you want to upload. This HTML theme will then be installed in the site.<p>
        <div class="group">
            <form method="post" enctype="multipart/form-data">
                <?php wp_nonce_field('coalescence_upload_theme_nonce', 'coalescence_upload_theme_nonce'); ?>

                <label for="file">Choose zip file:</label>
                <input type="file" name="theme_zip">

                <?php submit_button('Upload', 'primary', 'coalescence_upload_theme'); ?>
            </form>
        </div>
    </div>

    <div id="tab-backup-restore" class="tab-page">
        <h2>Backup or restore configuration</h2>

        <h3>Backup</h3>
        <div class="group">
            <p>Download rules file: <a href="<?= $rules_xml_file ?>">rules.xml</a> (right click, Save as...)</p>
            <p>Download this file this file and keep it somewhere safe. Use it in case you need to restore your site.</p>
        </div>

        <h3>Restore</h3>
        <div class="group">
            <form method="post" enctype="multipart/form-data">
                <?php wp_nonce_field('coalescence_upload_rules_nonce', 'coalescence_upload_rules_nonce'); ?>

                <label for="file">Choose rules.xml file:</label>
                <input type="file" name="rules_xml">

                <br />
                <?php submit_button('Restore', 'primary', 'coalescence_upload_rules'); ?>
            </form>
        </div>

        <h3>Reset</h3>
        <div class="group">
            <form method="post">
                <?php wp_nonce_field('coalescence_reset_rules_nonce', 'coalescence_reset_rules_nonce'); ?>

                <p>Reset the rules. <b>All rules will be lost!</b></p>

                <?php submit_button('Reset', 'primary', 'coalescence_reset_rules'); ?>
            </form>
        </div>
    </div>
</div>
