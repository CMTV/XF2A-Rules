<?php
/**
 * Rules xF2 addon by CMTV
 * Enjoy!
 */

namespace CMTV\Rules\Entity;

use CMTV\Rules\Constants as C;
use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

/**
 * COLUMNS
 * @property string rule_id
 * @property int rule_category_id
 * @property int display_order
 *
 * RELATIONS
 * @property RuleCategory Category
 */
class Rule extends TitleDescEntity
{
    public static function getPrePhrase(): string
    {
        return C::_('rule');
    }

    //
    // STRUCTURE
    //

    public static function getStructure(Structure $structure)
    {
        $structure->table = C::_db('rule');
        $structure->shortName = C::__('Rule');
        $structure->primaryKey = 'rule_id';

        $structure->columns = [
            'rule_id' => [
                'type' => self::STR,
                'maxLength' => 50,
                'required' => true,
                'unique' => true,
                'match' => 'alphanumeric'
            ],

            'rule_category_id' => [
                'type' => self::UINT
            ],

            'display_order' => [
                'type' => self::UINT,
                'default' => 0
            ]
        ];

        $structure->relations = [
            'Category' => [
                'type' => self::TO_ONE,
                'entity' => C::__('RuleCategory'),
                'conditions' => 'rule_category_id'
            ]
        ];

        // Adding title and description
        parent::addStructureElements($structure);

        return $structure;
    }
}