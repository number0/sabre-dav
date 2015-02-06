<?php

namespace Sabre\DAVACL\Xml\Property;

use
    Sabre\DAV,
    Sabre\DAV\Exception\BadRequest,
    Sabre\Xml\Element,
    Sabre\Xml\Reader,
    Sabre\Xml\Writer;

/**
 * Principal property
 *
 * The principal property represents a principal from RFC3744 (ACL).
 * The property can be used to specify a principal or pseudo principals.
 *
 * @copyright Copyright (C) 2007-2015 fruux GmbH (https://fruux.com/).
 * @author Evert Pot (http://evertpot.com/)
 * @license http://sabre.io/license/ Modified BSD License
 */
class Principal extends DAV\Xml\Property\Href {

    /**
     * To specify a not-logged-in user, use the UNAUTHENTICATED principal
     */
    const UNAUTHENTICATED = 1;

    /**
     * To specify any principal that is logged in, use AUTHENTICATED
     */
    const AUTHENTICATED = 2;

    /**
     * Specific principals can be specified with the HREF
     */
    const HREF = 3;

    /**
     * Everybody, basically
     */
    const ALL = 4;

    /**
     * Principal-type
     *
     * Must be one of the UNAUTHENTICATED, AUTHENTICATED or HREF constants.
     *
     * @var int
     */
    protected $type;

    /**
     * Creates the property.
     *
     * The 'type' argument must be one of the type constants defined in this class.
     *
     * 'href' is only required for the HREF type.
     *
     * @param int $type
     * @param string|null $href
     */
    function __construct($type, $href = null) {

        $this->type = $type;
        if ($type===self::HREF && is_null($href)) {
            throw new DAV\Exception('The href argument must be specified for the HREF principal type.');
        }
        if ($href) {
            $href = rtrim($href,'/') . '/';
            parent::__construct($href);
        }

    }

    /**
     * Returns the principal type
     *
     * @return int
     */
    function getType() {

        return $this->type;

    }


    /**
     * The xmlSerialize metod is called during xml writing.
     *
     * Use the $writer argument to write its own xml serialization.
     *
     * An important note: do _not_ create a parent element. Any element
     * implementing XmlSerializble should only ever write what's considered
     * its 'inner xml'.
     *
     * The parent of the current element is responsible for writing a
     * containing element.
     *
     * This allows serializers to be re-used for different element names.
     *
     * If you are opening new elements, you must also close them again.
     *
     * @param Writer $writer
     * @return void
     */
    function xmlSerialize(Writer $writer) {

        switch($this->type) {

            case self::UNAUTHENTICATED :
                $writer->writeElement('{DAV:}unauthenticated');
                break;
            case self::AUTHENTICATED :
                $writer->writeElement('{DAV:}authenticated');
                break;
            case self::HREF :
                parent::xmlSerialize($writer);
                break;
            case self::ALL :
                $writer->writeElement('{DAV:}all');
                break;
        }

    }

    /**
     * The deserialize method is called during xml parsing.
     *
     * This method is called staticly, this is because in theory this method
     * may be used as a type of constructor, or factory method.
     *
     * Often you want to return an instance of the current class, but you are
     * free to return other data as well.
     *
     * Important note 2: You are responsible for advancing the reader to the
     * next element. Not doing anything will result in a never-ending loop.
     *
     * If you just want to skip parsing for this element altogether, you can
     * just call $reader->next();
     *
     * $reader->parseInnerTree() will parse the entire sub-tree, and advance to
     * the next element.
     *
     * @param Reader $reader
     * @return mixed
     */
    static function xmlDeserialize(Reader $reader) {

        $tree = $reader->parseInnerTree()[0];

        switch($tree['name']) {
            case '{DAV:}unauthenticated' :
                return new self(self::UNAUTHENTICATED);
            case '{DAV:}authenticated' :
                return new self(self::AUTHENTICATED);
            case '{DAV:}href':
                return new self(self::HREF, $tree['value']);
            case '{DAV:}all':
                return new self(self::ALL);
            default :
                throw new BadRequest('Unknown or unsupported principal type: ' . $tree['name']);
        }

    }

}
