<?php

declare(strict_types=1);

namespace SimpleSAML\WSSecurity\XML\wsa;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\WSSecurity\Exception\ProtocolViolationException;
use SimpleSAML\XML\Constants;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\ExtendableAttributesTrait;
use SimpleSAML\XML\XMLStringElementTrait;

use function filter_var;

/**
 * Class representing WS-addressing AttributedURIType.
 *
 * You can extend the class without extending the constructor. Then you can use the methods available and the
 * class will generate an element with the same name as the extending class (e.g. \SimpleSAML\WSSecurity\wsa\Address).
 *
 * @package tvdijen/ws-security
 */
abstract class AbstractAttributedURIType extends AbstractWsaElement
{
    use ExtendableAttributesTrait;
    use XMLStringElementTrait;

    /** The namespace-attribute for the xs:any element */
    public const NAMESPACE = Constants::XS_ANY_NS_OTHER;


    /**
     * AbstractAttributedURIType constructor.
     *
     * @param string $value The localized string.
     * @param \DOMAttr[] $namespacedAttributes
     */
    final public function __construct(string $value, array $namespacedAttributes = [])
    {
        $this->setContent($value);
        $this->setAttributesNS($namespacedAttributes);
    }


    /**
     * Validate the content of the element.
     *
     * @param string $content  The value to go in the XML textContent
     * @throws \SimpleSAML\WSSecurity\Exception\ProtocolViolationException on failure
     * @return void
     */
    protected function validateContent(string $content): void
    {
        Assert::notEmpty($content);
        Assert::false(
            !filter_var($content, FILTER_VALIDATE_URL),
            $this->getQualifiedName() . ' is not a valid URL.',
            ProtocolViolationException::class,
        );
    }


    /**
     * Convert XML into a class instance
     *
     * @param \DOMElement $xml The XML element we should load
     * @return self
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException
     *   If the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, static::getLocalName(), InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, static::NS, InvalidDOMElementException::class);

        return new static($xml->textContent, self::getAttributesNSFromXML($xml));
    }


    /**
     * Convert this element to XML.
     *
     * @param \DOMElement|null $parent The element we should append this element to.
     * @return \DOMElement
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);
        $e->textContent = $this->getContent();

        foreach ($this->getAttributesNS() as $attr) {
            $e->setAttributeNS($attr['namespaceURI'], $attr['qualifiedName'], $attr['value']);
        }

        return $e;
    }
}
