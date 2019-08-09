<?php
/**
 * Rules xF2 addon by CMTV
 * Enjoy!
 */

namespace CMTV\Rules;

class Constants
{
    const ADDON_ID = [
        'CMTV',
        'Rules'
    ];

    public static function _(string $content = ''): string
    {
        $id = implode('_', self::ADDON_ID);
        return $id . (empty($content) ? '' : '_' . $content);
    }

    public static function __(string $content): string
    {
        return implode('\\', self::ADDON_ID) . ':' . $content;
    }

    public static function _db(string $content = ''): string
    {
        return strtolower('xf_' . self::_($content));
    }
}