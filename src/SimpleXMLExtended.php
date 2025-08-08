<?php

namespace Kboss\SzamlaAgent;

use ReturnTypeWillChange;
use SimpleXMLElement;


/**
 * SimpleXMLElement kiterjesztése
 */
class SimpleXMLExtended extends SimpleXMLElement {

    /**
     * @param  SimpleXMLElement $node
     * @param  string            $value
     * @return void
     */
    public function addCDataToNode(SimpleXMLElement $node, string $value = ''): void
    {
        if ($domElement = dom_import_simplexml($node)) {
            $domOwner = $domElement->ownerDocument;
            $domElement->appendChild($domOwner->createCDATASection($value));
        }
    }

    /**
     * @param string $name
     * @param string $value
     *
     * @return SimpleXMLElement
     */
    public function addChildWithCData(string $name = '', string $value = ''): SimpleXMLElement
    {
        $newChild = parent::addChild($name);
        if (SzamlaAgentUtil::isNotBlank($value)) {
            $this->addCDataToNode($newChild, $value);
        }
        return $newChild;
    }

    /**
     * @param string  $name
     * @param string|null $value
     * @param string|null $namespace
     * @return SimpleXMLElement|SimpleXMLExtended|null
     */
    #[ReturnTypeWillChange] public function addChild(string $name, ?string $value = null, ?string $namespace = null): SimpleXMLElement|SimpleXMLExtended|null
    {
        return parent::addChild($name, $value, $namespace);
    }

    /**
     * @param SimpleXMLElement $add
     */
    public function extend(SimpleXMLElement $add): void
    {
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
     * @param SimpleXMLElement $data
     * @return SimpleXMLElement
     */
    public function cleanXMLNode(SimpleXMLElement $data): SimpleXMLElement
    {
        $xmlString = $data->asXML();
        if (!str_contains($xmlString, '&')) {
            $cleanedXmlString = str_replace('&', '&amp;', $xmlString);
            $data = simplexml_load_string($cleanedXmlString);
        }
        return $data;
    }

    /**
     * Remove a SimpleXmlElement from it's parent
     * @return SimpleXMLExtended
     */
    public function remove(): SimpleXMLExtended
    {
        $node = dom_import_simplexml($this);
        $node->parentNode->removeChild($node);
        return $this;
    }

    /**
     * @param SimpleXMLElement $child
     *
     * @return SimpleXMLElement
     */
    public function removeChild(SimpleXMLElement $child): SimpleXMLElement
    {
        $node = dom_import_simplexml($this);
        $child = dom_import_simplexml($child);
        $node->removeChild($child);
        return $this;
    }
}