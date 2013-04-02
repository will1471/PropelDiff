<?php

/**
 * PropelDiff.
 *
 * Convinence class to compare two Propel Objects.
 *
 * Tested with Propel version 1.6.7.
 *
 * PropelDiff::getDiff() returns array of differences for each field name.
 *
 * @author Will Redman
 */
class PropelDiff
{

    /**
     *
     * @var BaseObject
     */
    protected $_object1;

    /**
     *
     * @var BaseObject
     */
    protected $_object2;

    /**
     *
     * @var string
     */
    protected $_object1Name;

    /**
     *
     * @var string
     */
    protected $_object2Name;


    /**
     * Class Constructor.
     *
     * @param BaseObject $object1
     * @param BaseObject $object2
     * @param string     $name1
     * @param string     $name2
     *
     * @throws InvalidArgumentException
     */
    public function __construct(BaseObject $object1, BaseObject $object2, $name1 = 'object1', $name2 = 'object2')
    {
        if ($object1::PEER !== $object2::PEER) {
            throw new InvalidArgumentException("Both objects should have the same type");
        }

        if ($name1 === $name2) {
            throw new InvalidArgumentException("Object names should be different");
        }

        $this->_object1 = $object1;
        $this->_object2 = $object2;
        $this->_object1Name = $name1;
        $this->_object2Name = $name2;
    }

    /**
     * Get the diff of the two objects.
     *
     * array(
     *     'fieldName' => array(
     *         'object1Name' => 'value',
     *         'object2Name' => 'differentValue'
     *     )
     *     ...
     * )
     * 
     * @param string $keyType Optional key type.
     *
     * @return array Array of diffs.
     *
     * @throws InvalidArgumentException
     */
    public function getDiff($keyType = BasePeer::TYPE_COLNAME)
    {
        if (!in_array($keyType, $this->_getAllFieldTypes())) {
            throw new InvalidArgumentException("Invalid key type");
        }

        $diffs = array();

        foreach ($this->_getPhpNames() as $name) {
            $method = 'get' . $name;
            if ($this->_object1->$method() !== $this->_object2->$method()) {
                $colName = $this->_translate($name, BasePeer::TYPE_PHPNAME, $keyType);
                $diffs[$colName] = array(
                    $this->_object1Name => $this->_object1->$method(),
                    $this->_object2Name => $this->_object2->$method(),
                );
            }
        }

        return $diffs;
    }
    
    /**
     * Get count of differences.
     *
     * @return int Count of differences.
     */
    public function getDiffCount()
    {
        return sizeof($this->getDiff());
    }
    
    /**
     * Are the obejcts the same.
     *
     * @return bool True if there are differences.
     */
    public function isDifferent()
    {
        return (bool) $this->getDiffCount() !== 0;
    }

    /**
     * Get Peer classname.
     *
     * @return string Peer classname.
     */
    private function _getPeer()
    {
        $obj = $this->_object1;
        return $obj::PEER;
    }

    /**
     * Get field names.
     *
     * @return array Array of Fields names.
     */
    private function _getPhpNames()
    {
        $peer = $this->_getPeer();
        return $peer::getFieldNames(BasePeer::TYPE_PHPNAME);
    }

    /**
     * Get field name types.
     *
     * @return array Array of all Propel field name types.
     */
    private function _getAllFieldTypes()
    {
        return array(
            BasePeer::TYPE_COLNAME,
            BasePeer::TYPE_FIELDNAME,
            BasePeer::TYPE_NUM,
            BasePeer::TYPE_PHPNAME,
            BasePeer::TYPE_RAW_COLNAME,
            BasePeer::TYPE_STUDLYPHPNAME
        );
    }

    /**
     * Translate field name from one to another.
     *
     * @param string $name Input field name.
     * @param string $from Input field name type.
     * @param string $to   Output field name type.
     *
     * @return string Translated field name,
     *
     * @throws InvalidArgumentException
     */
    private function _translate($name, $from, $to)
    {
        if (!in_array($from, $this->_getAllFieldTypes())) {
            throw new InvalidArgumentException("Invalid key type");
        }
        
        if (!in_array($to, $this->_getAllFieldTypes())) {
            throw new InvalidArgumentException("Invalid key type");
        }
        
        if ($to === $from) {
            return $name;
        }
        
        $peer = $this->_getPeer();
        return $peer::translateFieldName($name, $from, $to);
    }
}
