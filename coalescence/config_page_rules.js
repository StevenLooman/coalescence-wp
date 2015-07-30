function do_template(template, vars) {
    function replacer(match) {
        var name = match.substr(1, match.length - 2);
        return vars[name];
    }

    return template.replace(/\{[_a-z][_a-z0-9]+\}/gi, replacer);
}


function RuleView(parent, rule) {
    this.parent = parent;
    this.rule = rule;
    this.rule.view = this;
}

RuleView.prototype.render = function(container) {
    this.container = container || this.parent.$node;

    // render this
    this.$node = this._render();
    this.$node.data('view', this);
    this.container.append(this.$node);
};

RuleView.prototype.unrender = function() {
    delete this.rule.view;
    if (this.$node) {
        this.$node.remove();
        delete this.$node;
    }
};

RuleView.prototype._render = function() {
    var type = this.rule.nodeName;
    var selector_content = this.rule.attributes['content'] || this.rule.attributes['content-children'];
    var selector_theme = this.rule.attributes['theme'] || this.rule.attributes['theme-children'];

    var template =
        '<tr class="rule {rule_type}">' +
        '    <td class="type">{rule_type}</td>' +
        '    <td>{selector_content}</td>' +
        '    <td> {selector_theme}</td>' +
        '    <td class="actions">' +
        '        <button class="rule_up">Up</button>' +
        '        <button class="rule_down">Down</button>' +
        '        <button class="rule_delete">Delete</button>' +
        '    </td>' +
        '</tr>';
    var r = do_template(template, {
        rule_type: type,
        selector_content: selector_content && ('content: ' + selector_content.value) || '',
        selector_theme: selector_theme && ('theme: ' + selector_theme.value) || ''
    });
    var $tr = jQuery(r);

    // add event handlers
    var self = this;
    $tr.find('.rule_up').on('click', function(e) { self._moveUp(e); });
    $tr.find('.rule_down').on('click', function(e) { self._moveDown(e); });
    $tr.find('.rule_delete').on('click', function(e) { self._delete(e); });

    return $tr;
};

RuleView.prototype.highlightRule = function() {
    var $iframe_content = jQuery('#selector_content');
    var $iframe_theme = jQuery('#selector_theme');

    this.$node.addClass('selected');

    var content_xpath = this.rule.getAttribute('content') || this.rule.getAttribute('content-children');
    var $node = this._evalXpathIframe($iframe_content, content_xpath);
    $node.addClass('coalescence_selected');

    var theme_xpath = this.rule.getAttribute('theme') || this.rule.getAttribute('theme-children');
    $node = this._evalXpathIframe($iframe_theme, theme_xpath);
    $node.addClass('coalescence_selected');
};

RuleView.prototype.unhighlightRule = function() {
    if (!this.$node) {
        return;
    }

    var $iframe_content = jQuery('#selector_content');
    var $iframe_theme = jQuery('#selector_theme');

    this.$node.removeClass('selected');

    var content_xpath = this.rule.getAttribute('content') || this.rule.getAttribute('content-children');
    var $node = this._evalXpathIframe($iframe_content, content_xpath);
    $node.removeClass('coalescence_selected');

    var theme_xpath = this.rule.getAttribute('theme') || this.rule.getAttribute('theme-children');
    $node = this._evalXpathIframe($iframe_theme, theme_xpath);
    $node.removeClass('coalescence_selected');
};

RuleView.prototype._evalXpathIframe = function($iframe, xpath) {
    var doc = $iframe.contents()[0];
    var node = doc.evaluate(xpath, doc, null, XPathResult.FIRST_ORDERED_NODE_TYPE, null).singleNodeValue;
    var $node = $iframe.contents().find(node);
    return $node;
};

RuleView.prototype._moveUp = function(e) {
    this.unhighlightRule();

    var parentNode = this.rule.parentNode;

    function findPrevSiblingNode(node) {
        var prev = node.previousSibling;
        while (prev) {
            if (prev.nodeType != prev.TEXT_NODE) {
                return prev;
            }
            prev = prev.previousSibling;
        }

        return null;
    }

    // move this.rule up
    var prev = findPrevSiblingNode(this.rule);
    if (prev) {
        parentNode.insertBefore(this.rule, prev);
    }

    this.parent.update();
};

RuleView.prototype._moveDown = function(e) {
    this.unhighlightRule();

    var parentNode = this.rule.parentNode;

    function findNextSiblingNode(node) {
        if (!node) {
            return null;
        }
        var next = node.nextSibling;
        while (next) {
            if (next.nodeType != next.TEXT_NODE) {
                return next;
            }
            next = next.nextSibling;
        }

        return null;
    }

    // move this.rule down
    var next = findNextSiblingNode(this.rule);
    var nextnext = findNextSiblingNode(next);
    if (next && nextnext) {
        parentNode.insertBefore(this.rule, nextnext);
    } else if (next && !nextnext) {
        parentNode.appendChild(this.rule);
    }

    this.parent.update();
};

RuleView.prototype._delete = function(e) {
    this.unhighlightRule();

    var parentNode = this.rule.parentNode;
    parentNode.removeChild(this.rule);

    this.parent.removeChild(this.rule);
};


function RuleSectionView(rule) {
    this.rule = rule;
    this.children = [];
}

RuleSectionView.prototype.render = function(container) {
    this.container = container;

    // render this
    this.$node = this._render();
    this.$node.data('view', this);
    this.container.append(this.$node);

    // render children
    var self = this;
    jQuery.each(this.rule.childNodes, function(idx, child) {
        if (child.nodeType !== child.ELEMENT_NODE) { return; }

        // XXX: TODO: addChild is invalid method name
        // also, should be private?
        self.addChild(child);
    });
};

RuleSectionView.prototype.update = function() {
    this.unrender();
    this.render(this.container);
};

RuleSectionView.prototype.unrender = function() {
    // unrender children
    var self = this;
    jQuery.each(this.children, function(idx, child) {
        child.unrender();
    });

    this.$node.remove();
    delete this.$node;
};

RuleSectionView.prototype.addChild = function(rule) {
    var view = new RuleView(this, rule);
    view.render(this.$node);

    this.children.push(view);
};

RuleSectionView.prototype.removeChild = function(rule) {
    var view = rule.view;
    view.unrender();

    this.children = this.children.filter(function(v) { return v !== view; });
};

RuleSectionView.prototype._render = function() {
    var ruleName = this.rule.getAttribute('name');

    var template =
        '<table data-template="{name}">' +
        '   <tr>' +
        '       <th>Rule type</th>' +
        '       <th></th>' +
        '       <th></th>' +
        '       <th>Actions</th>' +
        '   </tr>' +
        '</table>';
    var r = do_template(template, {
        name: ruleName
    });
    var $table = jQuery(r);

    return $table;
};


function TemplateSelectorView(rule, templates) {
    this.rule = rule;
    this.templates = templates || [];
}

TemplateSelectorView.prototype.render = function(container) {
    this.container = container;

    this.$node = this._render();
    this.$node.data('view', this);
    this.container.append(this.$node);
};

TemplateSelectorView.prototype.unrender = function() {
    this.$node.remove();
    delete this.$node;
};

TemplateSelectorView.prototype.setTemplate = function(template) {
    if (!(template in this.templates)) {
        return;
    }

    var $sel = this.$node.find('select');
    $sel.val(template);
};

TemplateSelectorView.prototype._render = function() {
    var $div = jQuery('<div />');
    $div.data('view', this);

    var ruleName = this.rule.getAttribute('name');

    var $label = jQuery('<label />')
        .text(ruleName)
        .attr('for', ruleName);
    if (ruleName === 'default') {
        $label.html('<strong>' + ruleName + '</strong>');
    }
    $div.append($label);

    var $select = jQuery('<select />')
        .attr('name', ruleName);
    $div.append($select);

    var $option = jQuery('<option>None</option>')
        .attr('value', 'null');
    if (ruleName !== 'default') {
        $select.append($option);
    }

    // for all html files, add <option>
    var ruleHref = this.rule.getAttribute('href');
    jQuery.each(this.templates, function(idx, template) {
        var name = template['title'];
        var path = 'theme/' + name;
        var $option = jQuery('<option/>')
            .text(name)
            .attr('value', path);
        $select.append($option);

        if (path === ruleHref) {
            $option.attr('selected', 'selected');
        }
    });

    // event handlers
    var self = this;
    $select.on('change', function(e) { self._changed(e); });

    return $div;
};

TemplateSelectorView.prototype._changed = function(e) {
    var $select = this.$node.find('select');

    var template = $select.val();
    this.rule.setAttribute('href', template);

    var name = $select.attr('name');
    if (name !== 'default') {
        var if_content = 'false()';
        if (template !== 'null') {
            if_content = "//body[contains(concat(' ', normalize-space(@class), ' '), ' " + name + " ')]";
        }
        this.rule.setAttribute('if-content', if_content);
    }
};