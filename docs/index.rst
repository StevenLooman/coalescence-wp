===============================
Coalescence for Wordpress Theme
===============================

:Author: Steven Looman

.. contents::

Introduction
============

This is the documentation for the Coalescence Theme. The first part of this document describes the installation of the theme. The second part of this document describes the inner workings of the theme.

Coalescence does all the hard work when your Wordpress content is combined with the HTML theme. The theme is driven by a file, rules.xml, which contains all the rules to transform and combine the content with the HTML theme.


Installation
============

The `getting started <getting_started.html>`_ guide describes how to install Coalescence, and the installation of the HTML theme.

Inner workings
==============

**WARNING** This is the second part of the document, describing the internals of the theme. If you are now well familiar with HTML and XPath, you probably do not want to build rule files by hand. Instead, you can use the Coalescence Theme configurator to do this for you.

If you do not want to use the Coalescence Theme configurator, you can control the engine by writing your own set of rules. The rules are located in the file rules.xml. This file is an XML file and can be opened by any text or XML editor. Before you edit this by hand, please make sure that you are confident to go this route and know what you are doing. Also, always make a backup if you have made any modifications through the Coalescence Theme configurator.

Rules work by matching HTML nodes using XPath expressions. See the `W3C XPath language standard <http://www.w3.org/TR/xpath/>`_ for more information on XPath.

rules.xml
---------

The Coalescence engine is controled by a set of rules. These rules specify what the transformation engine must do during the transformation. The file `rules.xml <../rules.xml>`_ contains all the transformation rules. The root node of this file is a rules-node. All rules are exectued in order.

For each rule, a description, an example, its parameters, and additional notes are listed below as a reference.

<rules>
~~~~~~~

**Description**:

A structure to group a set of rules. The rules-node is used as the root-node of the rules.xml file. Also, if can be used to

**Example**:

::

  <rules>
    <rules if-content="//div[@id='side_bar']">
      <drop content="//div[@id='drop_me_if_sidebar_1']" />
      <drop content="//div[@id='drop_me_if_sidebar_2']" />
    </rules>
    <drop content="//div[@id='drop_me_always']" />
  </rules>

This example shows nesting of the rules rule. The inner rules-rule is executed if the content contains a div tag with id 'side_bar'. If it is executed, it will drop the div tags with ids 'drop_me_if_sidebar_1' and 'drop_me_if_sidebar_2'.

**Parameters**:
  - if-path: Regular expression matching the request URI. If present and it matches, the rule is executed, otherwise the rule is skipped.
  - if-content: XPath expression matching the content. If present and it matches, the rule is executed, otherwise the rule is skipped.

The content-children parameter 'overwrites' the content parameter. The theme-children parameter 'overwrites' the theme parameter.

**Additional notes**:

The rules-rule is a great way to group sub-rules together to match only on specific pages. This way, you save lots of if-content conditions on all sub-rules.

<theme>
~~~~~~~

**Description**:

Load a HTML file from the theme. Any following rules which apply to a theme, are then applied to the loaded HTML file. After all applicable rules are applied, the resulting HTML is shown.

**Example**:

::

  <rules>
    <theme href="theme/index.html" if-content="//body[@class='home']" />
  </rules>

This example shows loading of a HTML file from the theme. The rule is executed if the the body tag in the content has the class 'home'.

**Parameters**:
  - href: Path to the HTML file to be loaded.
  - if-path: Regular expression matching the request URI. If present and it matches, the rule is executed, otherwise the rule is skipped.
  - if-content: XPath expression matching the content. If present and it matches, the rule is executed, otherwise the rule is skipped.

**Additional notes**:

If a previously theme rule already resulted in loading of a HTML file, the current theme-rule is ignored. Meaning that once a HTML file is loaded, no other theme file can be loaded anymore.

<replace>
~~~~~~~~~

**Description**:

Replace a node or nodes from the loaded theme by a node or nodes from the content.

**Example**:

::

  <rules>
    <replace content="//div[@id='sidebar-frontpage']//div[@id='text-1']" theme="//div[@id='widget-1']" />
  </rules>

This example replaces the div with id 'widget-1' in the theme by the div with id 'text-1' from the content, located in the front-page sidebar.

**Parameters**:
  - content: XPath expression matching a node in the content. If this parameter is given, the selected node will replace the selected node in the theme.
  - content-children: XPath expression matching a node in the content. If this parameter is given, the children of the selected node will replace the selected node in the theme.
  - theme: XPath expression matching a node in the theme. If this parameter is given, the selected node will be replaced by the selected node(s) in the content.
  - theme-children: XPath expression matching a node in the theme. If this parameter is given, the children of the selected node will be replaced by the selected node(s) in the content.
  - if-path: Regular expression matching the request URI. If present and it matches, the rule is executed, otherwise the rule is skipped.
  - if-content: XPath expression matching the content. If present and it matches, the rule is executed, otherwise the rule is skipped.

**Additional notes**:

If the content or content-children parameter does not match node in the content, the matched nodes in the theme, by the theme or theme-children parameters, are dropped.

If the theme or theme-children parameter does not match any node in the theme, the rule is skipped.

<drop>
~~~~~~

**Description**:

Drop a node or nodes from either the content of the loaded theme.

**Example**:

::

  <rules>
    <drop theme="//div[@id='widget-1']" />
    <drop content="//div[@id='text1-1']" />
  </rules>

The first drop-rule in this example drops the div with id 'widget-1' in the theme. The second drop-rule in this example drops the div with id 'text-1'.

**Parameters**:
  - content: XPath expression matching a node in the content. If this parameter is given, the selected node will be dropped from the content.
  - content-children: XPath expression matching a node in the content. If this parameter is given, the children of the selected node will be dropped from the content.
  - theme: XPath expression matching a node in the theme. If this parameter is given, the selected node will be dropped from the theme.
  - theme-children: XPath expression matching a node in the theme. If this parameter is given, the children of the selected node will be dropped from the theme.
  - if-path: Regular expression matching the request URI. If present and it matches, the rule is executed, otherwise the rule is skipped.
  - if-content: XPath expression matching the content. If present and it matches, the rule is executed, otherwise the rule is skipped.

**Additional notes**:

If no nodes are matched, the rule will be ignored.

<before>
~~~~~~~~

**Description**:

Insert a node or nodes from the content *before* a node or nodes in the theme.

**Example**:

::

  <rules>
    <before content="//article/h1" theme-children="//div[@id='content']" />
  </rules>

This example adds the h1 tag found in the article node before all the child nodes of the div node with id 'content' in the theme.

**Parameters**:
  - content: XPath expression matching a node in the content. If this parameter is given, the selected node will be added to the theme.
  - content-children: XPath expression matching a node in the content. If this parameter is given, the children of the selected node will added to the theme.
  - theme: XPath expression matching a node in the theme. If this parameter is given, the matched content nodes will be added before the selected node in the theme.
  - theme-children: XPath expression matching a node in the theme. If this parameter is given, the matched content nodes will be added before the children of matched node in the theme.
  - if-path: Regular expression matching the request URI. If present and it matches, the rule is executed, otherwise the rule is skipped.
  - if-content: XPath expression matching the content. If present and it matches, the rule is executed, otherwise the rule is skipped.

**Additional notes**:

If no nodes are matched, the rule will be ignored.

<after>
~~~~~~~

**Description**:

Insert a node or nodes from the content *after* a node or nodes in the theme.

**Example**:

::

  <rules>
    <after content="//footer" theme-children="//div[@id='content']" />
  </rules>

In this example, the footer from the content is added to the end of the div with id 'content'.

**Parameters**:
  - content: XPath expression matching a node in the content. If this parameter is given, the selected node will be added to the theme.
  - content-children: XPath expression matching a node in the content. If this parameter is given, the children of the selected node will added to the theme.
  - theme: XPath expression matching a node in the theme. If this parameter is given, the matched content nodes will be added after the selected node in the theme.
  - theme-children: XPath expression matching a node in the theme. If this parameter is given, the matched content nodes will be added after the children of matched node in the theme.
  - if-path: Regular expression matching the request URI. If present and it matches, the rule is executed, otherwise the rule is skipped.
  - if-content: XPath expression matching the content. If present and it matches, the rule is executed, otherwise the rule is skipped.

**Additional notes**:

If no nodes are matched, the rule will be ignored.

License
=======

The Coalescence for Wordpress theme has been released under the `GPLv2 license <../LICENCE.txt>`_.

Before re-releasing this theme, please take into consideration the amount of time it has cost me to build this and bring it to Wordpress. Without income, I cannot provide support for Coalescence.
