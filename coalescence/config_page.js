(function($) {
    $(document).ready(function() {
        function getElementXPath(element) {
            var xpath = getElementXPathById(element);

            // if can't find by id, then try to find by class
            if (!xpath.match('//.+\\[@id=')) {
                xpath = getElementXPathByClass(element);
            }

            return xpath;
        }

        function getElementXPathById(element) {
            if (element === undefined || element === null || element.nodeName === '#document') {
                return '';
            }

            if (element.id) {
                return "//" + element.nodeName.toLowerCase() + "[@id='" + element.id + "']";
            }

            var index = 0;
            for (var sibling = element.previousSibling; sibling; sibling = sibling.previousSibling) {
                if (sibling.nodeType != Node.ELEMENT_NODE) {
                    continue;
                }

                if (sibling.nodeName == element.nodeName) {
                    ++index;
                }
            }

            var tagName = element.nodeName.toLowerCase();
            var parent = element.parentNode;
            var hasSibling = index > 0 || $.map(parent.children, function(e) { if (e.nodeName.toLowerCase() == tagName) return e; }).length > 1;
            var pathIndex = (hasSibling ? "[" + (index + 1) + "]" : "");
            return getElementXPathById(parent) + '/' + tagName + pathIndex;
        }

        function getElementXPathByClass(element) {
            if (element === undefined || element === null || element.nodeName === '#document') {
                return '';
            }

            var class_ = element.getAttribute('class');
            if (class_) {
                // try to find a unique class
                // remove coalescence visual indicators
                class_ = class_.replace('coalescence_hover', '').replace('coalescence_selected', '').trim();
                var doc = element.ownerDocument;
                var elements = doc.getElementsByClassName(class_);
                // XXX: TODO: add a blacklist with classes which should not be used, such as span[1..12]
                if (elements.length === 1) {
                    return "//" + element.nodeName.toLowerCase() + "[@class='" + class_ + "']";
                }
            }

            var index = 0;
            for (var sibling = element.previousSibling; sibling; sibling = sibling.previousSibling) {
                if (sibling.nodeType != Node.ELEMENT_NODE) {
                    continue;
                }

                if (sibling.nodeName == element.nodeName) {
                    ++index;
                }
            }

            var tagName = element.nodeName.toLowerCase();
            var parent = element.parentNode;
            var hasSibling = index > 0 || $.map(parent.children, function(e) { if (e.nodeName.toLowerCase() == tagName) return e; }).length > 1;
            var pathIndex = (hasSibling ? "[" + (index + 1) + "]" : "");
            return getElementXPathByClass(parent) + '/' + tagName + pathIndex;
        }


        window.coalescence = {
            'content': {
                'element': null,
                'prevClicked': []
            },
            'theme': {
                'element': null,
                'prevClicked': []
            },
            'rules': {
                'doc': null,
                'current_section_view': null,
                'last_rule': null
            }
        };

        function showTemplateRules(e) {
            var $select = $(e.target);
            var val = $select.val();

            $('#rules table').hide();
            var view = $('#rules table[data-template="' + val + '"]').data('view');
            view.$node.show();

            coalescence.rules.current_section_view = view;

            window.localStorage.setItem('coalescence.rule_section', val);
        }


        // saving of rules.xml
        $('.coalescence_config form').on('submit', function (e) {
            // gather all rules
            var doc = coalescence.rules.doc;
            var serializer = new XMLSerializer();
            var xml = serializer.serializeToString(doc);

            // store rules in text area
            $('textarea[name="rules"]').val(xml);

            // and let it post
        });


        function clear_element_selection() {
            // clear all selection
            $('#selected_content').val('');
            $('#selected_theme').val('');
            coalescence.content.prevClicked = [];
            coalescence.theme.prevClicked = [];
        }

        // event handler for selecting rules
        function rule_clicked(e) {
            clear_element_selection();

            // unhighlight old rule
            var view = coalescence.rules.last_rule;
            if (view) {
                view.unhighlightRule();
            }

            // highlight new rule
            var $tr = $(e.target).parents('tr');
            view = $tr.data('view');
            if (view) {
                view.highlightRule();
            }
            coalescence.rules.last_rule = view;

            return false;
        }
        $('#rules').on('click', rule_clicked);


        // creation new rules
        $('#replace').on('click', function (e) {
            if (!$('#selected_content').val() || !$('#selected_theme').val()) {
                alert('Content or theme expression missing, not creating rule.');
                return;
            }

            var doc = coalescence.rules.doc;
            var node = doc.createElement('replace');

            var content_children = doc.createAttribute('content-children');
            content_children.value = $('#selected_content').val();
            node.setAttributeNode(content_children);

            var theme_children = doc.createAttribute('theme-children');
            theme_children.value = $('#selected_theme').val();
            node.setAttributeNode(theme_children);

            // add new rule to current section
            var view = coalescence.rules.current_section_view;
            var rule = view.rule;
            rule.appendChild(node);
            view.update();
        });

        $('#drop_content').on('click', function (e) {
            var doc = coalescence.rules.doc;
            var node = doc.createElement('drop');

            var content = doc.createAttribute('content');
            content.value = $('#selected_content').val();
            node.setAttributeNode(content);

            // add new rule to current section
            var view = coalescence.rules.current_section_view;
            var rule = view.rule;
            rule.appendChild(node);
            view.update();
        });

        $('#drop_theme').on('click', function (e) {
            var doc = coalescence.rules.doc;
            var node = doc.createElement('drop');

            var theme = doc.createAttribute('theme');
            theme.value = $('#selected_theme').val();
            node.setAttributeNode(theme);

            // add new rule to current section
            var view = coalescence.rules.current_section_view;
            var rule = view.rule;
            rule.appendChild(node);
            view.update();
        });


        // iframe template/page chooser
        $('#content_page').on('change', function(e) {
            var $this = $(this);
            var val = $this.val();
            if (!val) {
                return;
            }

            var href = val + (val.indexOf('?') === -1 && '?' || '&') + 'coalescence.off=1';
            var $iframe = $('#selector_content');
            $iframe.attr('src', href);

            window.localStorage.setItem('coalescence.content_page', val);
        });

        $('#theme_page').on('change', function(e) {
            var $this = $(this);
            var val = $this.val();

            var $iframe = $('#selector_theme');
            $iframe.attr('src', val);

            window.localStorage.setItem('coalescence.theme_page', val);
        });

        // event handler for item clicked in content or theme
        $('#selector_content, #selector_theme').on('load', function(e) {
            var $this = $(this);
            var selector = $this.attr('id') == 'selector_content' ? 'content' : 'theme';
            var $contents = $(this).contents();

            // add styling to iframes
            var $css = $('<style> ' +
                '.coalescence_selected { border: 1px solid yellow; } ' +
                '.coalescence_hover    { border: 1px solid red;    } ' +
                '</style>');
            $contents.find('body').append($css);

            // event handler for item hover
            $contents.find('*').on('mouseenter', function (e) {
                // remove all highlighting on hover
                $contents.find('*').removeClass('coalescence_hover');

                // re-enable hover
                var $hovered = $(e.target);
                $hovered.addClass('coalescence_hover');
            });

            // event handler for element selection
            var selected = null;
            $contents.find('*').on('click', function (e) {
                var $clicked = $(e.target);

                // prevent navigating away
                e.preventDefault();

                // select parent if same previous is selected again
                var prevClicked = coalescence[selector].prevClicked;
                if (prevClicked[0] === $clicked[0]) {
                    selected = selected.parent();
                } else {
                    selected = $clicked;
                }

                // show use what is currently selected
                $contents.find('*').removeClass('coalescence_selected');
                selected.addClass('coalescence_selected');

                coalescence[selector].element = selected[0];

                var xpath = getElementXPath(selected[0]);
                $('#selected_' + selector).val(xpath);

                // bookkeeping of previously clicked/selected
                coalescence[selector].prevClicked = $clicked;
                return false;
            });
        });

        // event handler for xpath selector content or theme change
        $('#selected_content, #selected_theme').on('keyup', function(e) {
            var $this = $(this);
            var selector = $this.attr('id') == 'selected_content' ? 'content' : 'theme';
            var xpath = $this.val();

            var doc = $('#selector_' + selector).contents()[0];
            var $contents = $('#selector_' + selector).contents();
            $('#selector_' + selector).contents().find('*').removeClass('coalescence_selected');
            try {
                var nodes = doc.evaluate(xpath, doc, null, XPathResult.ORDERED_NODE_ITERATOR_TYPE, null);

                // collect nodes first, if we modify the document the iterator is invalidated
                var $nodes = [];
                var node = nodes.iterateNext();
                while (node !== null) {
                    var $node = $contents.find(node);
                    $nodes.push($node);
                    node = nodes.iterateNext();
                }

                $.each($nodes, function(idx) {
                    this.addClass('coalescence_selected');
                });

                $this.removeClass('error');
            } catch (err) {
                console.error('error', err);
                $this.addClass('error');
            }
        });

        // orientation chooser
        $('#selectors-horizontal').on('click', function(e) {
            $('.selector-group')
                .removeClass('full-width')
                .addClass('half-width');

            window.localStorage.setItem('coalescence.orientation', 'horizontal');
        });

        $('#selectors-vertical').on('click', function(e) {
            $('.selector-group')
                .removeClass('half-width')
                .addClass('full-width');

            window.localStorage.setItem('coalescence.orientation', 'vertical');
        });


        // tabbing
        $('.nav-tab').on('click', function(e) {
            var $this = $(this);
            var tabname = $this.attr('data-tab-id');

            $('.nav-tab').removeClass('nav-tab-active');
            $this.addClass('nav-tab-active');

            $('.tab-page').hide();
            $('#' + tabname).show();
        });


        // parse rules.xml
        (function() {
            var rules = $('textarea[name="rules"]').val();
            var doc = $.parseXML(rules);
            coalescence.rules.doc = doc;
        })();

        // build rule sections
        (function() {
            var doc = coalescence.rules.doc;
            var root = doc.firstChild;
            var $container = $('#rules');

            // build selector
            var $select = $('#rules-template-selector');
            $select.on('change', showTemplateRules);

            // build super rule views
            $.each(root.childNodes, function(idx, rule) {
                if (rule.nodeType === rule.TEXT_NODE ||
                    rule.nodeType === rule.COMMENT_NODE ||
                    rule.getAttribute('hidden')) {
                    return;
                }

                var rs = new RuleSectionView(rule);
                rs.render($container);
                var ruleName = rule.getAttribute('name');
                rs.$node.hide();
                coalescence.rules.current_section_view =
                    coalescence.rules.current_section_view || rs;

                var name = rule.getAttribute('name');
                var $option = $('<option />')
                    .text(name)
                    .val(name)
                    .data('view', rs);

                $select.append($option);
            });
        })();

        // build theme template selector
        (function() {
            var templates = $('#theme_templates').text();
            templates = JSON.parse(templates);
            $container = $('#templates');
            var doc = coalescence.rules.doc;
            // firefox does not have getElementsByName, filter manually
            var rules = doc.getElementsByTagName('rules');
            $.each(rules, function(idx, rule) {
                if (rule.getAttribute('name') !== 'selector-template') {
                    return;
                }

                $.each(rule.childNodes, function(idx, rule) {
                    if (rule.nodeType === rule.TEXT_NODE ||
                        rule.nodeType == rule.COMMENT_NODE) {
                        return;
                    }

                    var tv = new TemplateSelectorView(rule, templates);
                    tv.render($container);
                });
            });
        })();

        // fake local storage if needed, not saving state
        // XXX: TODO: fallback with cookies?
        (function() {
            if (!window.localStorage) {
                window.localStorage = {
                    getItem: function(key) { return null; },
                    setItem: function(key, value) {},
                    removeItem: function(key) {},
                    clear: function() {}
                };
            }
        })();

        // restore state
        (function() {
            var templates = $('#theme_templates').text();
            templates = JSON.parse(templates);
            var files = [];
            $.each(templates, function(e) { files.push(e.href); });

            var current = window.localStorage.getItem('coalescence.content_page') || $('#content_page').val();
            $('#content_page').val(current).trigger('change');

            current = window.localStorage.getItem('coalescence.theme_page') || $('#theme_page').val();
            if (current in files) {
                $('#theme_page').val(current);
            }
            $('#theme_page').trigger('change');

            current = window.localStorage.getItem('coalescence.rule_section') || 'global';
            $('#rules-template-selector').val(current).trigger('change');

            current = window.localStorage.getItem('coalescence.orientation') || 'horizontal';
            if (current == 'horizontal') {
                $('#selectors-horizontal').trigger('click');
            } else {
                $('#selectors-vertical').trigger('click');
            }
        })();

        // activate first tab
        (function() {
            $('.nav-tab').first().trigger('click');
        })();
    });
}(jQuery));
