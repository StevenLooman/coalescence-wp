<?php

    class CoalescenceEngine {
        private $coalescence_dir;
        private $theme_dir;
        private $theme_url;
        private $theme;
        private $rules;

        public function __construct($content, $rules) {
            $this->content = $content;
            $this->rules = $rules;

            $this->coalescence_dir = get_template_directory() . '/';
            $this->theme_dir = get_template_directory() . '/theme/';
            $this->theme_url = get_stylesheet_directory_uri() . '/theme/';
        }

        function fix_paths($doc, $tag_name, $attribute_name) {
            $nodes = $doc->getElementsByTagName($tag_name);
            foreach ($nodes as $node) {
                $attribute = $orig_attribute = $node->getAttribute($attribute_name);
                $pos = strpos($attribute, "?");
                if ($pos !== false) {
                    $attribute = substr($attribute, 0, $pos);
                }
                if ($attribute != null and file_exists($this->theme_dir . $attribute)) {
                    $node->setAttribute($attribute_name, $this->theme_url . $orig_attribute);
                }
            }

            foreach ($doc->getElementsByTagName('head') as $head) {
                foreach ($head->childNodes as $node) {
                    if ($node instanceof DOMComment) {
                        $re = "/(<" . $tag_name . "[^>]*" . $attribute_name . "=\")([^\"]*)(\"[^>]*>)/";
                        // XXX: TODO: doesnt this need a see-if-file-exists check too?
                        $fixed = preg_replace($re, '${1}' . $this->theme_url . '${2}${3}', $node->data);
                        $node->data = $fixed;
                    }
                }
            }
        }

        function load_theme($theme_path) {
            // see if file exists
            if (!file_exists($theme_path)) {
                return null;
            }
            if ($this->theme !== null) {
                return;
            }

            // load theme document
            $theme = new DOMDocument();
            libxml_use_internal_errors(true);
            $theme->loadHTMLFile($theme_path);
            libxml_use_internal_errors(false);

            // fix all css/js/img paths
            $this->fix_paths($theme, 'link', 'href');
            $this->fix_paths($theme, 'script', 'src');
            $this->fix_paths($theme, 'img', 'src');
            // coalescence_fix_paths($theme, 'a', 'href');

            $this->theme = $theme;
            return $theme;
        }

        private function _eval_if_path($if_path) {
            $path = $_SERVER['REQUEST_URI'];
            $re = '@' . $if_path . '@';

            return preg_match($re, $path) === 1;
        }

        private function _eval_if_content($content, $if_content) {
            $content_node = $this->_eval_xpath_single($content, $if_content);
            return $content_node != null;
        }

        private function _eval_xpath($doc, $xpath_expr) {
            $doc_xpath = new DOMXpath($doc);
            $nodes = $doc_xpath->query($xpath_expr);
            return $nodes;
        }

        private function _eval_xpath_single($doc, $xpath_expr) {
            return $this->_eval_xpath($doc, $xpath_expr)->item(0);
        }

        function replace($content_query, $theme_query) {
            $content_nodes = $this->_eval_xpath($this->content, $content_query);

            $theme_nodes = $this->_eval_xpath($this->theme, $theme_query);
            $theme_node = $theme_nodes->item(0);
            if ($theme_node == null) {
                return;
            }
            $parent_node = $theme_node->parentNode;

            // insert all content_nodes before theme-nodes
            // nodes are effectively replaced in line
            foreach ($content_nodes as $content_node) {
                $imported_node = $this->theme->importNode($content_node, true);
                $parent_node->insertBefore($imported_node, $theme_node);
            }

            // remove all theme-nodes
            foreach ($theme_nodes as $theme_node) {
                // prevent error by ensuring node to be removed is a child node
                // this makes replace match the first node of the expression only!
                if ($parent_node === $theme_node->parentNode) {
                    $parent_node->removeChild($theme_node);
                }
            }
        }

        private function _drop($doc, $doc_query) {
            $doc_nodes = $this->_eval_xpath($doc, $doc_query);

            foreach ($doc_nodes as $doc_node) {
                $doc_node->parentNode->removeChild($doc_node);
            }
        }

        function drop_theme($query) {
            $this->_drop($this->theme, $query);
        }

        function drop_content($query) {
            $this->_drop($this->content, $query);
        }

        function before($content_query, $theme_query) {
            $content_nodes = $this->_eval_xpath($this->content, $content_query);

            $theme_node = $this->_eval_xpath_single($this->theme, $theme_query);
            if ($theme_node == null) {
                return;
            }
            $parent_node = $theme_node->parentNode;

            foreach ($content_nodes as $content_node) {
                $imported_node = $this->theme->importNode($content_node, true);
                $parent_node->insertBefore($imported_node, $theme_node);
            }
        }

        function after($content_query, $theme_query) {
            $content_nodes = $this->_eval_xpath($this->content, $content_query);

            $theme_node = $this->_eval_xpath_single($this->theme, $theme_query);
            if ($theme_node == null) {
                return;
            }
            $parent_node = $theme_node->parentNode;

            foreach ($content_nodes as $content_node) {
                $imported_node = $this->theme->importNode($content_node, true);
                $parent_node->appendChild($imported_node);
            }
        }

        function copy($content_query, $theme_query, $attributes) {
            $content_node = $this->_eval_xpath_single($this->content, $content_query);
            if ($content_node == null) {
                return;
            }

            $theme_node = $this->_eval_xpath_single($this->theme, $theme_query);
            if ($theme_node == null) {
                return;
            }

            foreach (explode(' ', $attributes) as $attribute) {
                $attr_value = $content_node->getAttribute($attribute);
                $theme_node->setAttribute($attribute, $attr_value);
            }
        }

        function execute_rules($rules = null) {
            $rules = $rules ? $rules : $this->rules;

            foreach ($rules as $rule) {
                $attrs = $rule->attributes();

                $if_path = (string)$attrs['if-path'];
                if ($if_path != null && !$this->_eval_if_path($if_path)) {
                    continue;
                }

                $if_content = (string)$attrs['if-content'];
                if ($if_content != null && !$this->_eval_if_content($this->content, $if_content)) {
                    continue;
                }

                // add dummy child node to node if theme-children
                if ((string)$attrs['theme-children']) {
                    $theme_node = $this->_eval_xpath_single($this->theme, (string)$attrs['theme-children']);
                    if ($theme_node == null) {
                        // if rule does not match anything in theme, continue
                        continue;
                    }
                    $theme_temp_node = $this->theme->createElement('temp');
                    $theme_node->appendChild($theme_temp_node);
                }

                $rule_type = $rule->getName();
                $content_xpath = (string)$attrs['content'] ? (string)$attrs['content'] : ((string)$attrs['content-children'] ? (string)$attrs['content-children'] . '/node()' : null);
                $theme_xpath = (string)$attrs['theme'] ? (string)$attrs['theme'] : ((string)$attrs['theme-children'] ? (string)$attrs['theme-children'] . '/node()' : null);
                switch ($rule_type) {
                    case 'rules':
                        // execute child-rules
                        $this->execute_rules($rule);
                        break;

                    case 'theme':
                        // only load a theme when we don't have one already
                        if ($this->theme != null) {
                            break;
                        }
                        $href = (string)$attrs['href'];
                        $this->load_theme($this->coalescence_dir . $href);
                        break;

                    case 'replace':
                        $this->replace($content_xpath, $theme_xpath);
                        break;

                    case 'drop':
                        if ($content_xpath) {
                            $this->drop_content($content_xpath);
                        } elseif ($theme_xpath) {
                            $this->drop_theme($theme_xpath);
                        }
                        break;

                    case 'before':
                        $this->before($content_xpath, $theme_xpath);
                        break;

                    case 'after':
                        $this->after($content_xpath, $theme_xpath);
                        break;

                    case 'copy':
                        $this->copy($content_xpath, $theme_xpath, $attrs['attributes']);
                        break;
                }

                if ((string)$attrs['theme-children'] && $theme_temp_node->parentNode != null) {
                    $theme_node->removeChild($theme_temp_node);
                }
            }

            return $this->theme;
        }
    }

?>