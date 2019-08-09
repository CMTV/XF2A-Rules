<?php
/**
 * Rules xF2 addon by CMTV
 * Enjoy!
 */

namespace CMTV\Rules\Job;

use CMTV\Rules\Constants as C;
use XF\Job\AbstractRebuildJob;

class DeleteRules extends AbstractRebuildJob
{
    protected function getNextIds($start, $batch)
    {
        $db = $this->app->db();

        $table = C::_db('rule');
        $categoryId = $this->data['category_id'];

        $result = $db->fetchAllColumn(
            $db->limit("SELECT `rule_id` FROM `{$table}` WHERE `rule_category_id` = {$categoryId}",
                $this->data['batch'],
                $this->data['start']
            )
        );

        return $result;
    }

    protected function rebuildById($id)
    {
        $rule = $this->app->finder(C::__('Rule'))->whereId($id)->fetchOne();
        $rule->delete();
    }

    protected function getStatusType()
    {
        return \XF::phrase(C::_('deleting_rules_in_category'));
    }
}