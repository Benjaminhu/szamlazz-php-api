<?php

namespace SzamlaAgent;

/**
 * SimpleXMLElement kiterjesztése
 *
 * @package SzamlaAgent
 */
class SimpleXMLExtended extends \SimpleXMLElement {

    /**
     * @param  \SimpleXMLElement $node
     * @param  string            $value
     * @return void
     */
    public function addCDataToNode(\SimpleXMLElement $node, $value = '') {
        if ($domElement = dom_import_simplexml($node)) {
            $domOwner = $domElement->ownerDocument;
            $domElement->appendChild($domOwner->createCDATASection($value));
        }
    }

    /**
     * @param string $name
     * @param string $value
     *
     * @return \SimpleXMLElement
     */
    public function addChildWithCData($name = '', $value = '') {
        $newChild = parent::addChild($name);
        if (SzamlaAgentUtil::isNotBlank($value)) {
            $this->addCDataToNode($newChild, $value);
        }
        return $newChild;
    }

    /**
     * @param  string $value
     * @return void
     */
    public function addCData($value = '')  {
        $this->addCDataToNode($this, $value);
    }

    /**
     * @param $name
     * @param $value
     * @param $namespace
     * @return \SimpleXMLElement|SimpleXMLExtended|null
     */
    #[\ReturnTypeWillChange]
    public function addChild($name, $value = null, $namespace = null) {
        return parent::addChild($name, $value, $namespace);
    }

    /**
     * @param \SimpleXMLElement $add
     */
    public function extend( $add ) {
        if ( $add->count() != 0 ) {
            $new = $this->addChild($add->getName());
        } else {
            $new = $this->addChild($add->getName(), $this->cleanXMLNode($add));
        }

        foreach ($add->attributes() as $a => $b) {
            $new->addAttribute($a, $b);
        }

        if ( $add->count() != 0 ) {
            foreach ($add->children() as $child) {
                $new->extend($child);
            }
        }
    }

    /**
     * @param \SimpleXMLElement $data
     * @return \SimpleXMLElement
     */
    public function cleanXMLNode( $data ) {
        $xmlString = $data->asXML();
        if (strpos($xmlString, '&') !== false) {
            $cleanedXmlString = str_replace('&', '&amp;', $xmlString);
            $data = simplexml_load_string($cleanedXmlString);
        }
        return $data;
    }

    /**
     * Remove a SimpleXmlElement from it's parent
     * @return $this
     */
    public function remove() {
        $node = dom_import_simplexml($this);
        $node->parentNode->removeChild($node);
        return $this;
    }

    /**
     * @param \SimpleXMLElement $child
     *
     * @return \SimpleXMLElement
     */
    public function removeChild(\SimpleXMLElement $child) {
        if ($child !== null) {
            $node = dom_import_simplexml($this);
            $child = dom_import_simplexml($child);
            $node->removeChild($child);
        }
        return $this;
    }
}