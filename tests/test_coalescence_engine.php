<?php

// Emulate Wordpress
function get_template_directory() {
    return __DIR__;
}

function get_stylesheet_directory_uri() {
    return 'http://localhost/wp-content/themes/coalescence';
}


require_once 'coalescence/engine.php';


class CoalescenceEngineTest extends PHPUnit_Framework_TestCase {

    private function loadHtml($file) {
        $content = new DOMDocument();
        libxml_use_internal_errors(true);
        $content->loadHTMLFile(get_template_directory() . '/theme/' . $file);
        libxml_use_internal_errors(false);
        return $content;
    }


    private function evalXPath($doc, $query) {
        $doc_xpath = new DOMXpath($doc);
        $nodes = $doc_xpath->query($query);
        return $nodes;
    }

    private function evalXPathSingle($doc, $query) {
        $nodes = $this->evalXPath($doc, $query);
        return $nodes->item(0);
    }

    private function assertXPathMatches($doc, $query) {
        $node = $this->evalXPathSingle($doc, $query);
        $this->assertNotNull($node);
    }

    private function assertNotXPathMatches($doc, $query) {
        $node = $this->evalXPathSingle($doc, $query);
        $this->assertNull($node);
    }


    public function testCoalescenceTheme() {
        $engine = new CoalescenceEngine(null, null);
        $theme = $engine->load_theme(get_template_directory() . '/theme/index.html');

        // theme should be loaded
        $this->assertTrue($theme != null);

        // links/scripts should still be there
        $this->assertSelectEquals('html head script', null, 2, $theme);
        $this->assertSelectEquals('html head link', null, 2, $theme);
    }

    public function testCoalescenceThemeFixedLinks() {
        $engine = new CoalescenceEngine(null, null);
        $theme = $engine->load_theme(get_template_directory() . '/theme/index.html');

        // links to local files should be fixed
        $this->assertXPathMatches($theme, '//link[@href="http://localhost/wp-content/themes/coalescence/theme/local.css' . '"]');
        // links to remote files should not be fixed
        $this->assertXPathMatches($theme, '//link[@href="http://example.org/remote.css"]');
    }

    public function testCoalescenceThemeFixedScripts() {
        $engine = new CoalescenceEngine(null, null);
        $theme = $engine->load_theme(get_template_directory() . '/theme/index.html');

        // scripts to local files should be fixed
        $this->assertXPathMatches($theme, '//script[@src="http://localhost/wp-content/themes/coalescence/theme/local.js' . '"]');
        // scripts to remote files should not be fixed
        $this->assertXPathMatches($theme, '//script[@src="http://example.org/remote.js"]');
    }

    public function testCoalescenceThemeFixedImgs() {
        $engine = new CoalescenceEngine(null, null);
        $theme = $engine->load_theme(get_template_directory() . '/theme/index.html');

        // imgs to local files should be fixed
        $this->assertXPathMatches($theme, '//img[@src="http://localhost/wp-content/themes/coalescence/theme/local.png' . '"]');
        // imgs to remote files should not be fixed
        $this->assertXPathMatches($theme, '//img[@src="http://example.org/remote.png"]');
    }

    public function testCoalescenceThemeFixedConditional() {
        $engine = new CoalescenceEngine(null, null);
        $theme = $engine->load_theme(get_template_directory() . '/theme/index.html');

        $html = $theme->saveHTML();
        $this->assertContains("<link rel=\"stylesheet\" type=\"text/css\" href=\"http://localhost/wp-content/themes/coalescence/theme/local-conditional.css\" />", $html);
    }


    public function testCoalescenceReplace() {
        $content = $this->loadHtml('content.html');

        $engine = new CoalescenceEngine($content, null);
        $theme = $engine->load_theme(get_template_directory() . '/theme/index.html');

        // ensure original theme
        $this->assertXPathMatches($theme, '/html/body/div[@id="content"]');
        $this->assertNotXPathMatches($theme, '/html/body/article');

        $engine->replace('//article', '//div[@id="content"]');

        // theme should be altered
        $this->assertNotXPathMatches($theme, '/html/body/div[@id="content"]');
        $this->assertXPathMatches($theme, '/html/body/article');
    }

    public function testCoalescenceReplace_noMatchTheme() {
        $content = $this->loadHtml('content.html');
        $themeOrig = $this->loadHtml('index.html');

        $engine = new CoalescenceEngine($content, null);
        $theme = $engine->load_theme(get_template_directory() . '/theme/index.html');

        // ensure theme contains div#content
        $this->assertXPathMatches($theme, '/html/body/div[@id="content"]');

        $engine->replace('//div[@id="content"]', '//div[@id="test"]');

        // theme should not be altered
        $this->assertEqualXMLStructure($themeOrig->lastChild, $theme->lastChild);
    }

    public function testCoalescenceReplace_noMatchContent() {
        $content = $this->loadHtml('content.html');

        $engine = new CoalescenceEngine($content, null);
        $theme = $engine->load_theme(get_template_directory() . '/theme/index.html');

        // ensure theme contains div#content
        $this->assertXPathMatches($theme, '/html/body/div[@id="content"]');

        $engine->replace('//div[@id="test"]', '//div[@id="content"]');

        // theme element should be dropped, even though it didn't match the content
        $this->assertNotXPathMatches($theme, '/html/body/div[@id="content"]');
    }


    public function testCoalescenceDropTheme() {
        $content = $this->loadHtml('content.html');

        $engine = new CoalescenceEngine($content, null);
        $theme = $engine->load_theme(get_template_directory() . '/theme/index.html');

        // ensure original theme
        $this->assertXPathMatches($theme, '/html/body/div[@id="content"]');

        $engine->drop_theme('//div[@id="content"]');

        // theme should be altered
        $this->assertNotXPathMatches($theme, '/html/body/div[@id="content"]');
    }

    public function testCoalescenceDropTheme_noMatch() {
        $content = $this->loadHtml('content.html');
        $themeOrig = $this->loadHtml('index.html');

        $engine = new CoalescenceEngine($content, null);
        $theme = $engine->load_theme(get_template_directory() . '/theme/index.html');

        // ensure original theme
        $this->assertXPathMatches($theme, '//body/div[@id="content"]');

        $engine->drop_theme('//div[@id="test"]');

        // theme should not be altered
        $this->assertEqualXMLStructure($themeOrig->lastChild, $theme->lastChild);
    }

    public function testCoalescenceDropContent() {
        $content = $this->loadHtml('content.html');
        $contentOrig = $this->loadHtml('content.html');

        $engine = new CoalescenceEngine($content, null);

        // ensure original content
        $this->assertXPathMatches($content, '/html/body/div[@id="content"]');

        $engine->drop_content('//div[@id="content"]');

        // content should be altered
        $this->assertNotXPathMatches($content, '/html/body/div[@id="content"]');
    }

    public function testCoalescenceDropContent_noMatch() {
        $content = $this->loadHtml('content.html');
        $contentOrig = $this->loadHtml('content.html');

        $engine = new CoalescenceEngine($content, null);

        // ensure original content
        $this->assertXPathMatches($content, '//body/div[@id="content"]');

        $engine->drop_content('//div[@id="test"]');

        // theme should not be altered
        $this->assertEqualXMLStructure($contentOrig->lastChild, $content->lastChild);
    }


    public function testCoalescenceBefore() {
        $content = $this->loadHtml('content.html');

        $engine = new CoalescenceEngine($content, null);
        $theme = $engine->load_theme(get_template_directory() . '/theme/index.html');

        // ensure original theme
        $this->assertXPathMatches($theme, '/html/body/div[@id="content"]');
        $this->assertNotXPathMatches($theme, '//article');

        $engine->before('//article', '//div[@id="content"]');

        // ensure placed before div
        $this->assertXPathMatches($theme, '/html/body/article[following-sibling::div[@id="content"]]');
    }

    public function testCoalescenceBefore_noMatchContent() {
        $content = $this->loadHtml('content.html');
        $themeOrig = $this->loadHtml('index.html');

        $engine = new CoalescenceEngine($content, null);
        $theme = $engine->load_theme(get_template_directory() . '/theme/index.html');

        // ensure original theme
        $this->assertXPathMatches($theme, '/html/body/div[@id="content"]');
        $this->assertNotXPathMatches($theme, '//article');

        $engine->before('//article[@id="test"]', '//div[@id="content"]');

        // theme should not be altered
        $this->assertEqualXMLStructure($themeOrig->lastChild, $theme->lastChild);
    }

    public function testCoalescenceBefore_noMatchTheme() {
        $content = $this->loadHtml('content.html');
        $themeOrig = $this->loadHtml('index.html');

        $engine = new CoalescenceEngine($content, null);
        $theme = $engine->load_theme(get_template_directory() . '/theme/index.html');

        // ensure original theme
        $this->assertXPathMatches($theme, '/html/body/div[@id="content"]');
        $this->assertNotXPathMatches($theme, '//article');

        $engine->before('//article', '//div[@id="test"]');

        // theme should not be altered
        $this->assertEqualXMLStructure($themeOrig->lastChild, $theme->lastChild);
    }


    public function testCoalescenceAfter() {
        $content = $this->loadHtml('content.html');

        $engine = new CoalescenceEngine($content, null);
        $theme = $engine->load_theme(get_template_directory() . '/theme/index.html');

        // ensure original theme
        $this->assertXPathMatches($theme, '/html/body/div[@id="content"]');
        $this->assertNotXPathMatches($theme, '//article');

        $engine->after('//article', '//div[@id="content"]');

        // ensure placed before div
        $this->assertXPathMatches($theme, '/html/body/div[@id="content"][following-sibling::article]');
    }

    public function testCoalescenceAfter_noMatchContent() {
        $content = $this->loadHtml('content.html');
        $themeOrig = $this->loadHtml('index.html');

        $engine = new CoalescenceEngine($content, null);
        $theme = $engine->load_theme(get_template_directory() . '/theme/index.html');

        // ensure original theme
        $this->assertXPathMatches($theme, '/html/body/div[@id="content"]');
        $this->assertNotXPathMatches($theme, '//article');

        $engine->after('//article[@id="test"]', '//div[@id="content"]');

        // theme should not be altered
        $this->assertEqualXMLStructure($themeOrig->lastChild, $theme->lastChild);
    }

    public function testCoalescenceAfter_noMatchTheme() {
        $content = $this->loadHtml('content.html');
        $themeOrig = $this->loadHtml('index.html');

        $engine = new CoalescenceEngine($content, null);
        $theme = $engine->load_theme(get_template_directory() . '/theme/index.html');

        // ensure original theme
        $this->assertXPathMatches($theme, '/html/body/div[@id="content"]');
        $this->assertNotXPathMatches($theme, '//article');

        $engine->after('//article', '//div[@id="test"]');

        // theme should not be altered
        $this->assertEqualXMLStructure($themeOrig->lastChild, $theme->lastChild);
    }


    public function testCoalescenceCopy() {
        $content = $this->loadHtml('content.html');

        $engine = new CoalescenceEngine($content, null);
        $theme = $engine->load_theme(get_template_directory() . '/theme/index.html');

        // ensure original theme
        $this->assertXPathMatches($theme, '//div[@id="footer"]/p');
        $this->assertNotXPathMatches($theme, '//div[@id="footer"]/p[@class="discrete"]');

        $engine->copy('//p[@class="discrete"]', '//div[@id="footer"]/p', 'class');

        // ensure placed before div
        $this->assertXPathMatches($theme, '//div[@id="footer"]/p[@class="discrete"]');
    }

    public function testCoalescenceCopy_noMatchContent() {
        $content = $this->loadHtml('content.html');
        $themeOrig = $this->loadHtml('index.html');

        $engine = new CoalescenceEngine($content, null);
        $theme = $engine->load_theme(get_template_directory() . '/theme/index.html');

        // ensure original theme
        $this->assertXPathMatches($theme, '//div[@id="footer"]/p');
        $this->assertNotXPathMatches($theme, '//div[@id="footer"]/p[@class="discrete"]');

        $engine->copy('//p[@class="test"]', '//div[@id="footer"]/p', 'class');

        // theme should not be altered
        $this->assertEqualXMLStructure($themeOrig->lastChild, $theme->lastChild);
    }

    public function testCoalescenceCopy_noMatchTheme() {
        $content = $this->loadHtml('content.html');
        $themeOrig = $this->loadHtml('index.html');

        $engine = new CoalescenceEngine($content, null);
        $theme = $engine->load_theme(get_template_directory() . '/theme/index.html');

        // ensure original theme
        $this->assertXPathMatches($theme, '//div[@id="footer"]/p');
        $this->assertNotXPathMatches($theme, '//div[@id="footer"]/p[@class="discrete"]');

        $engine->copy('//p[@class="discrete"]', '//div[@id="test"]/p', 'class');

        // theme should not be altered
        $this->assertEqualXMLStructure($themeOrig->lastChild, $theme->lastChild);
    }

    public function testCoalescenceCopy_noMatchAttribute() {
        $content = $this->loadHtml('content.html');
        $themeOrig = $this->loadHtml('index.html');

        $engine = new CoalescenceEngine($content, null);
        $theme = $engine->load_theme(get_template_directory() . '/theme/index.html');

        // ensure original theme
        $this->assertXPathMatches($theme, '//div[@id="footer"]/p');
        $this->assertNotXPathMatches($theme, '//div[@id="footer"]/p[@class="discrete"]');

        $engine->copy('//p[@class="discrete"]', '//div[@id="footer"]/p', 'data-test');

        // theme should not be altered
        $this->assertEqualXMLStructure($themeOrig->lastChild, $theme->lastChild);
    }
}
?>
