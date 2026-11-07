<?php
namespace usualtool\Lib;
/**
       * --------------------------------------------------------       
       *  |                  █   █ ▀▀█▀▀                    |           
       *  |                  █▄▄▄█   █                      |           
       *  |                                                 |           
       *  |    Author: Huang Hui                            |           
       *  |    Repository 1: https://gitee.com/usualtool    |           
       *  |    Repository 2: https://github.com/usualtool   |           
       *  |    Applicable to Apache 2.0 protocol.           |           
       * --------------------------------------------------------       
*/
/**
 * Html过滤器（防XSS）
 */
class HtmlFilter{
    /**
     * Html白名单
     */
    private static array $whitelist = [
        'p'       => [],
        'br'      => [],
        'strong'  => [],
        'b'       => [],
        'em'      => [],
        'i'       => [],
        'u'       => [],
        's'       => [],
        'del'     => [],
        'ins'     => [],
        'sub'     => [],
        'sup'     => [],
        'h1' => [], 'h2' => [], 'h3' => [], 'h4' => [], 'h5' => [], 'h6' => [],
        'div' => ['class', 'id'],
        'span' => ['class', 'id'],
        'blockquote' => [],
        'pre' => [],
        'code' => [],
        'ul' => [], 'ol' => [], 'li' => [],
        'a' => ['href', 'title', 'target', 'rel'],
        'img' => ['src', 'alt', 'width', 'height'],
        'table' => ['class', 'id'],
        'thead' => [], 'tbody' => [], 'tfoot' => [],
        'tr' => [],
        'th' => ['colspan', 'rowspan'],
        'td' => ['colspan', 'rowspan'],
    ];
    /**
     * 净化 HTML 字符串
     * @param string $html
     * @return string
     */
    public static function Sanitize(string $html): string{
        $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
        libxml_use_internal_errors(true);
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->loadHTML('<body>' . $html . '</body>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        $body = $dom->getElementsByTagName('body')->item(0);
        self::CleanNode($body);
        $cleanHtml = '';
        foreach ($body->childNodes as $node) {
            $cleanHtml .= $dom->saveHTML($node);
        }
        return $cleanHtml;
    }
    /**
     * 递归清理 DOM 节点
     */
    private static function CleanNode(\DOMNode $node): void{
        $nodesToRemove = [];
        foreach ($node->childNodes as $child) {
            if ($child->nodeType === XML_ELEMENT_NODE) {
                $tagName = strtolower($child->nodeName);
                if (!array_key_exists($tagName, self::$whitelist)) {
                    $nodesToRemove[] = $child;
                } else {
                    $allowedAttrs = self::$whitelist[$tagName];
                    $attrsToRemove = [];
                    foreach ($child->attributes as $attr) {
                        $attrName = strtolower($attr->nodeName);
                        if (!in_array($attrName, $allowedAttrs, true)) {
                            $attrsToRemove[] = $attr;
                            continue;
                        }
                        if ($attrName === 'href' || $attrName === 'src') {
                            $value = strtolower(trim($attr->value));
                            if (preg_match('/^(javascript|vbscript|data):/i', $value)) {
                                $attrsToRemove[] = $attr;
                            }
                        }
                    }
                    foreach ($attrsToRemove as $attr) {
                        $child->removeAttributeNode($attr);
                    }
                    self::CleanNode($child);
                }
            } elseif ($child->nodeType === XML_COMMENT_NODE || $child->nodeType === XML_PI_NODE) {
                $nodesToRemove[] = $child;
            }
        }
        foreach ($nodesToRemove as $child) {
            $node->removeChild($child);
        }
    }
}