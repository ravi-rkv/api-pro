<?php

namespace App\Models;

use App\Models\Role;
use App\Models\TabList;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Permission extends Model
{
    use HasFactory;

    public static function getAssignedPermissionByType($roleId, $userId, $type = null)
    {
        // Validate inputs
        if (is_string($roleId) && is_string($userId) && !empty($roleId) && !empty($userId)) {
            if ($type && !is_string($type)) {
                return false;
            }

            $query1 = DB::table('role_has_permissions as rhp')
                ->join('roles as r', 'rhp.role_id', '=', 'r.role_id')
                ->join('permissions as p', 'rhp.permission_id', '=', 'p.permission_id')
                ->leftJoin('user_inactive_tabs as uit', function ($join) use ($userId) {
                    $join->on('uit.permission_id', '=', 'rhp.permission_id')
                        ->where('uit.uid', '=', $userId);
                })
                ->where([
                    ['r.role_id', '=', $roleId],
                    ['r.is_active', '=', 1],
                    ['p.is_active', '=', 1],
                    ['rhp.is_active', '=', 1],
                    ['uit.permission_id', '=', null],
                ])
                ->select('rhp.permission_id');
            if ($type) {
                $query1->where('p.type', '=', $type);
            }


            // Query 2
            $query2 = DB::table('user_has_permissions as uhp')
                ->join('permissions as p', 'uhp.permission_id', '=', 'p.permission_id')
                ->leftJoin('user_inactive_tabs as uit', function ($join) use ($userId) {
                    $join->on('uit.permission_id', '=', 'uhp.permission_id')
                        ->where('uit.uid', '=', $userId);
                })
                ->where([
                    ['uhp.is_active', '=', 1],
                    ['uhp.uid', '=', $userId],
                    ['p.is_active', '=', 1],
                    ['uit.permission_id', '=', null],
                ])
                ->select('uhp.permission_id');
            if ($type) {
                $query2->where('p.type', '=', $type);
            }


            $results = $query1
                ->unionAll($query2)
                ->orderBy('permission_id', 'asc')
                ->get();

            return $results->isNotEmpty() ? $results->mapWithKeys(function ($item) {
                return [$item->permission_id => [
                    'permission_id' => $item->permission_id
                ]];
            })->toArray()
                : [];

            return ($results->isNotEmpty()) ? $results->toArray() : [];
        }

        return null;
    }
}
