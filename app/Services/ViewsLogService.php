<?php

namespace App\Services;

use App\Models\ViewsLog;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

class ViewsLogService extends ServiceProvider
{
    public static function set(Request $request, $entityType = null, $entityId = null)
    {
        $entity = $entityType ? $entityType : $request->entityType;
        $id = $entityId ? $entityId : $request->entityId;

        if ($entity && $id) {
            $user = $request->user();

            $query = ViewsLog::query()
                ->where('entity_type', $entity)
                ->where('entity_id', $id);

            if ($user) {
                $query->where('created_by', $user->id);
            } else {
                $query->whereHas('userAgentData', function ($q) use ($request) {
                    $q->where('ip', $request->ip());
                });
            }

            $query->whereTime('created_at', '>', Carbon::now()->subHour());

            $res = $query->first();

            if (!$res) {
                $viewsLog = ViewsLog::create([
                    'entity_type' => $entity,
                    'entity_id' => $id,
                    'created_by' => $user ? $user->id : null,
                ]);

                UserAgentService::setData($request, $viewsLog);

                return response([
                    'message' => 'View accepted',
                ],
                    Response::HTTP_CREATED
                );
            } else {
                return response([
                    'message' => 'View don`t accepted',
                ],
                    Response::HTTP_OK
                );
            }
        }
    }
}
