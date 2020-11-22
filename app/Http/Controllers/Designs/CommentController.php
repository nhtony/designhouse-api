<?php

namespace App\Http\Controllers\Designs;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\CommentResource;
use App\Repositories\Contracts\IComment;
use App\Repositories\Contracts\IDesign;

class CommentController extends Controller
{
    protected $commnents;
    protected $designs;

    public function __construct(IComment $commnents, IDesign $designs)
    {
        $this->commnents = $commnents;
        $this->designs = $designs;
    }

    public function store(Request $request, $designId)
    {
        $this->validate($request, [
            'body' => ['required']
        ]);

        $commnent = $this->designs->addComment($designId, [
            'body' => $request->body,
            'user_id' => auth()->id(),
        ]);

        return new CommentResource($commnent);
    }

    public function update(Request $request, $id)
    {
        $commnent = $this->commnents->find($id);
        $this->authorize('update', $commnent);

        $this->validate($request, [
            'body' => ['required']
        ]);

        $updatedComment = $this->commnents->update($id, [
            'body' => $request->body
        ]);

        return new CommentResource($updatedComment);
    }

    public function destroy($id)
    {
        $commnent = $this->commnents->find($id);
        $this->authorize('delete', $commnent);

        $this->commnents->delete($commnent->id);

        return response()->json(['message'=> 'Item deleted'],200);
    }
}
