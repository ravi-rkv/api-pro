<?php

namespace App\Models;

use App\Models\Permission;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TabList extends Model
{
    use HasFactory;

    public static function getChildTabData(array $permArray, $parent)
    {
        if (is_array($permArray) && count($permArray) > 0) {
            return TabList::where('is_active', 1)
                ->where('parent_id', $parent)
                ->whereIn('permission_id', $permArray)
                ->orderBy('tab_order', 'ASC')
                ->get()
                ->toArray(); // Return the result as an array
        }

        return [];
    }

    public static function getTabListByPerm(array $permArray, $select = null)
    {

        if (is_array($permArray) && count($permArray) > 0) {
            $query = TabList::where('is_active', 1)
                ->select('tab_id', 'tab_name', 'parent_id', 'tab_class', 'tab_icon', 'tab_url', 'tab_order', 'permission_id', 'is_active')
                ->whereIn('permission_id', $permArray)
                ->orderBy('tab_order', 'ASC');

            if ($select) {
                $query->select($select);
            }
            return $query->get()->toArray();
        }

        return [];
    }

    public static function getSingleTabData($tabId)
    {
        if (is_numeric($tabId)) {
            $result = TabList::where('tab_id', $tabId)
                ->where('is_active', 1)
                ->first();

            return $result ? $result->toArray() : null;
        }

        return null;
    }
}
