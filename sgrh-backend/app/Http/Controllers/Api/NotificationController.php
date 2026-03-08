<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NotificationSgrh;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    // ── Mes notifications ─────────────────────────────────────────────────────
    public function index(Request $request): JsonResponse
    {
        $query = NotificationSgrh::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc');

        if ($request->filled('lu')) {
            $query->where('lu', filter_var($request->lu, FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $notifs = $query->paginate($request->get('per_page', 20));

        return response()->json([
            'data' => $notifs->map(fn($n) => [
                'id'         => $n->id,
                'titre'      => $n->titre,
                'message'    => $n->message,
                'type'       => $n->type,
                'lien'       => $n->lien,
                'lu'         => $n->lu,
                'lu_le'      => $n->lu_le?->format('Y-m-d H:i'),
                'created_at' => $n->created_at?->diffForHumans(),
            ]),
            'meta' => [
                'total'         => $notifs->total(),
                'non_lues'      => NotificationSgrh::where('user_id', $request->user()->id)->where('lu', false)->count(),
                'current_page'  => $notifs->currentPage(),
                'last_page'     => $notifs->lastPage(),
            ],
        ]);
    }

    // ── Marquer une notification comme lue ────────────────────────────────────
    public function marquerLue(Request $request, NotificationSgrh $notification): JsonResponse
    {
        if ($notification->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $notification->update([
            'lu'    => true,
            'lu_le' => now(),
        ]);

        return response()->json(['message' => 'Notification marquée comme lue.']);
    }

    // ── Tout marquer comme lu ─────────────────────────────────────────────────
    public function toutMarquerLu(Request $request): JsonResponse
    {
        NotificationSgrh::where('user_id', $request->user()->id)
            ->where('lu', false)
            ->update(['lu' => true, 'lu_le' => now()]);

        return response()->json(['message' => 'Toutes les notifications sont marquées comme lues.']);
    }

    // ── Supprimer une notification ────────────────────────────────────────────
    public function destroy(Request $request, NotificationSgrh $notification): JsonResponse
    {
        if ($notification->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $notification->delete();

        return response()->json(['message' => 'Notification supprimée.']);
    }

    // ── Compteur non lues (pour la navbar) ────────────────────────────────────
    public function nonLues(Request $request): JsonResponse
    {
        $count = NotificationSgrh::where('user_id', $request->user()->id)
            ->where('lu', false)
            ->count();

        return response()->json(['count' => $count]);
    }
}
