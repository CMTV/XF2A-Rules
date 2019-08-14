<?php
/**
 * Rules xF2 addon by CMTV
 * Enjoy!
 */

namespace CMTV\Rules\Entity;

use CMTV\Rules\Constants as C;
use XF\Mvc\Entity\AbstractCollection;
use XF\Mvc\Entity\Structure;

/**
 * COLUMNS
 * @property int|null rule_category_id
 * @property string icon
 * @property int display_order
 *
 * RELATIONS
 * @property AbstractCollection|Rule[] Rules
 */
class RuleCategory extends TitleEntity
{
    public static function getPrePhrase(): string
    {
        return C::_('rule_category');
    }

    //
    // STRUCTURE
    //

    public static function getStructure(Structure $structure)
    {
        $structure->table = C::_db('rule_category');
        $structure->shortName = C::__('RuleCategory');
        $structure->primaryKey = 'rule_category_id';

        $structure->columns = [
            'rule_category_id' => [
                'type' => self::UINT,
                'autoIncrement' => true,
                'nullable' => true,
                'unique' => true
            ],

            'icon' => [
                'type' => self::STR,
                'maxLength' => 50,
                'default' => ''
            ],

            'display_order' => [
                'type' => self::UINT,
                'default' => 0
            ]
        ];

        $structure->relations = [
            'Rules' => [
                'entity' => C::__('Rule'),
                'type' => self::TO_MANY,
                'conditions' => [
                    ['rule_category_id', '=', '$rule_category_id']
                ]
            ]
        ];

        // Adding title
        parent::addStructureElements($structure);

        return $structure;
    }

    //
    // LIFE CYCLE
    //

    protected function _postDelete()
    {
        parent::_postDelete();

        $this->db()->update(C::_db('rule'),
            ['rule_category_id' => 0],
            'rule_category_id = ?',
            $this->rule_category_id
        );
    }
}