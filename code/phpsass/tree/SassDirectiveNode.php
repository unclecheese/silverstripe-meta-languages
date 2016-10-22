<?php
/* SVN FILE: $Id$ */
/**
 * SassDirectiveNode class file.
 * @author      Chris Yates <chris.l.yates@gmail.com>
 * @copyright   Copyright (c) 2010 PBM Web Development
 * @license      http://phamlp.googlecode.com/files/license.txt
 * @package      PHamlP
 * @subpackage  Sass.tree
 */

/**
 * SassDirectiveNode class.
 * Represents a CSS directive.
 * @package      PHamlP
 * @subpackage  Sass.tree
 */
class SassDirectiveNode extends SassNode
{
    const NODE_IDENTIFIER = '@';
    const MATCH = '/^(@[\w-]+)/';

  /**
   * SassDirectiveNode.
   * @param object source token
   * @return SassDirectiveNode
   */
  public function __construct($token)
  {
      parent::__construct($token);
  }
  
    protected function getDirective()
    {
        return $this->token->source;
        preg_match('/^(@[\w-]+)(?:\s*(\w+))*/', $this->token->source, $matches);
        array_shift($matches);
        $parts = implode(' ', $matches);
        return strtolower($parts);
    }

  /**
   * Parse this node.
   * @param SassContext the context in which this node is parsed
   * @return array the parsed node
   */
  public function parse($context)
  {
      $this->token->source = $this->script->evaluate($this->token->source, $context)->value;

      $this->children = $this->parseChildren($context);
      return array($this);
  }

  /**
   * Render this node.
   * @return string the rendered node
   */
  public function render()
  {
      $properties = array();
      foreach ($this->children as $child) {
          $properties[] = $child->render();
      } // foreach

    return $this->renderer->renderDirective($this, $properties);
  }

  /**
   * Returns a value indicating if the token represents this type of node.
   * @param object token
   * @return boolean true if the token represents this type of node, false if not
   */
  public static function isa($token)
  {
      return $token->source[0] === self::NODE_IDENTIFIER;
  }

  /**
   * Returns the directive
   * @param object token
   * @return string the directive
   */
  public static function extractDirective($token)
  {
      preg_match(self::MATCH, $token->source, $matches);
      return strtolower($matches[1]);
  }
}
