<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function index()
    {
        auth()->user()->update(['last_chat_seen_at' => now()]);

        $users = User::where('id', '!=', auth()->id())
            ->with('role:id,slug')
            ->orderBy('name')
            ->get(['id', 'name', 'role_id', 'last_seen_at', 'is_invisible']);

        return view('chat.index', compact('users'));
    }

    public function messages(Request $request)
    {
        $sinceId    = (int) $request->query('since_id', 0);
        $recipientId = $request->query('user_id');

        $query = Message::with('sender:id,name')->where('id', '>', $sinceId);

        if ($recipientId) {
            $me = auth()->id();
            $them = (int) $recipientId;
            $query->where(function ($q) use ($me, $them) {
                $q->where('user_id', $me)->where('recipient_id', $them)
                  ->orWhere('user_id', $them)->where('recipient_id', $me);
            });
        } else {
            $query->whereNull('recipient_id');
        }

        $messages = $query->orderBy('id')->limit(100)->get();

        return response()->json($messages->map(fn($m) => [
            'id'         => $m->id,
            'user_id'    => $m->user_id,
            'sender'     => $m->sender->name,
            'body'       => $m->body,
            'created_at' => $m->created_at->format('H:i'),
            'is_mine'    => $m->user_id === auth()->id(),
        ]));
    }

    public function send(Request $request)
    {
        $data = $request->validate([
            'body'         => 'required|string|max:2000',
            'recipient_id' => 'nullable|integer|exists:users,id',
        ]);

        $message = Message::create([
            'user_id'      => auth()->id(),
            'recipient_id' => $data['recipient_id'] ?? null,
            'body'         => trim($data['body']),
        ]);

        $message->load('sender:id,name');

        return response()->json([
            'id'         => $message->id,
            'user_id'    => $message->user_id,
            'sender'     => $message->sender->name,
            'body'       => $message->body,
            'created_at' => $message->created_at->format('H:i'),
            'is_mine'    => true,
        ]);
    }

    public function online()
    {
        $query = User::online()
            ->select('id', 'name', 'role_id', 'is_invisible')
            ->with('role:id,name,slug')
            ->orderBy('name');

        // Non-admins don't see invisible users
        if (!auth()->user()->isAdmin()) {
            $query->where('is_invisible', false);
        }

        return response()->json($query->get()->map(fn($u) => [
            'id'           => $u->id,
            'name'         => $u->name,
            'is_invisible' => $u->is_invisible,
            'role_name'    => $u->role?->name,
        ]));
    }

    public function unread()
    {
        $since = auth()->user()->last_chat_seen_at ?? now()->subYears(10);

        $group = Message::whereNull('recipient_id')
            ->where('user_id', '!=', auth()->id())
            ->where('created_at', '>', $since)
            ->count();

        $private = Message::where('recipient_id', auth()->id())
            ->where('created_at', '>', $since)
            ->count();

        return response()->json(['count' => $group + $private]);
    }

    public function markRead()
    {
        auth()->user()->update(['last_chat_seen_at' => now()]);
        return response()->json(['ok' => true]);
    }

    public function usersList()
    {
        $users = User::where('id', '!=', auth()->id())
            ->orderBy('name')
            ->get(['id', 'name', 'last_seen_at', 'is_invisible']);

        return response()->json($users->map(fn($u) => [
            'id'           => $u->id,
            'name'         => $u->name,
            'online'       => $u->isOnline(),
            'is_invisible' => (bool) $u->is_invisible,
        ]));
    }

    public function unreadDetail()
    {
        $since = auth()->user()->last_chat_seen_at ?? now()->subYears(10);
        $me    = auth()->id();

        $group = Message::whereNull('recipient_id')
            ->where('user_id', '!=', $me)
            ->where('created_at', '>', $since)
            ->count();

        $byUser = Message::where('recipient_id', $me)
            ->where('created_at', '>', $since)
            ->selectRaw('user_id, COUNT(*) as cnt')
            ->groupBy('user_id')
            ->pluck('cnt', 'user_id');

        return response()->json([
            'group' => $group,
            'users' => $byUser,
            'total' => $group + $byUser->sum(),
        ]);
    }
}
