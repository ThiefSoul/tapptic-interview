<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ApiController extends Controller
{

    public function handle(Request $request)
    {
        // TODO: Add params validation
        $content = $request->all();
        $action = $content['action'];

        // TODO: extract it somewhere to avoid this method growing when new actions are added
        if ($action === 'like') {
            /** @var User $userA */
            $userA = User::query()->findOrFail($content['userA']);
            /** @var User $userB */
            $userB = User::query()->findOrFail($content['userB']);

            $userA->likeUser($userB);

            return response(status:Response::HTTP_OK);
        }
        if ($action === 'dislike') {
            /** @var User $userA */
            $userA = User::query()->findOrFail($content['userA']);
            /** @var User $userB */
            $userB = User::query()->findOrFail($content['userB']);

            $userA->dislikeUser($userB);

            return response(status:Response::HTTP_OK);
        }

        return response(sprintf('Action "%s" is not supported', $action), Response::HTTP_BAD_REQUEST);
    }

}
