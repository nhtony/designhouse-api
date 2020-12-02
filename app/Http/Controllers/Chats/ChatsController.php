<?php

namespace App\Http\Controllers\Chats;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ChatResource;
use App\Http\Resources\MessageResource;
use App\Repositories\Contracts\{IChat, IMessage};
use App\Repositories\Eloquent\Criteria\WithTrashed;
use Aws\History;

class ChatsController extends Controller
{
    protected $chats;
    protected $messages;

    public function __construct(IChat $chats, IMessage $messages)
    {
        $this->chats = $chats;
        $this->messages = $messages;
    }

    // send message to user
    public function sendMessages(Request $request)
    {
        // validate the request
        $this->validate($request, [
            'recipient' => ['required'],
            'body' => ['required']
        ]);

        $recipient = $request->recipient;
        $user = auth()->user();
        $body = $request->body;

        // check if there is an existing chat
        // between the auth user and the recipient
        $chat = $user->getChatWithUser($recipient);

        if (!$chat) {
            $chat = $this->chats->create([]);
            $this->chats->createParticipants($chat->id, [
                $user->id,
                $recipient
            ]);
        }

        // add the message to the chat
        $message = $this->messages->create([
            'user_id' => $user->id,
            'chat_id' => $chat->id,
            'body' =>  $body,
            'last_read' => null
        ]);

        return new MessageResource($message);
    }

    // get chats for user
    public function getUserChats()
    {
        $chats = $this->chats->getUserChats(auth()->id());
        return ChatResource::collection($chats);
    }

    public function getChatMessages($id)
    {
        $messages = $this->messages->withCriteria([
            new WithTrashed()
        ])->findWhere('chat_id', $id);
        return MessageResource::collection($messages);
    }

    public function markAsRead($id)
    {
        $chat = $this->chats->find($id);
        $chat->markAsReadForUser(auth()->id());

        return response()->json(['message' => 'succesful'], 200);
    }

    public function destroyMessage($id)
    {
        $message = $this->messages->find($id);
        $this->authorize('delete', $message);
        $message->delete($message);
    }
}
