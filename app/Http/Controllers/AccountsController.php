<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Helpers\JWTHelper;
use App\Helpers\HttpStatusCodes;
use App\Validators\ValidatesAccountsRequests;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class AccountsController extends Controller
{
    use ValidatesAccountsRequests;

    /**
     * View user account
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        return response()->json(
            $request->user,
            HttpStatusCodes::SUCCESS_OK
        );
    }

    /**
     * Update user account
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws
     */
    public function update(Request $request)
    {
        $user = $request->user;

        $this->validateUpdate($request, $user);

        $user->update([
            'name' => $request->get('name', $user->name),
            'email' => $request->get('email', $user->email),
            'password' => $request->has('password') ? Hash::make($request->input('password')) : $user->password
        ]);

        $user->save();

        // If user changes password revoke all refresh_tokens
        if ($request->has('password')) {
            JWTHelper::revokeAllRefreshTokens($user->id);
        }

        return response()->json(
            $user,
            HttpStatusCodes::SUCCESS_OK
        );
    }

    /**
     * Delete user account
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function delete(Request $request)
    {
        JWTHelper::revokeAllRefreshTokens($request->user->id);
        $request->user->delete();

        return response()->json(
            null,
            HttpStatusCodes::SUCCESS_NO_CONTENT
        );
    }

    /**
     * Show all sessions
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function showSessions(Request $request) {
        $refresh_tokens = $request->user->refreshTokens();

        return response()->json(
            $refresh_tokens->get(),
            HttpStatusCodes::SUCCESS_OK
        );
    }

    /**
     * Revoke a session
     *
     * @param Request $request
     * @param $uuid
     *
     * @return JsonResponse
     */
    public function revokeSession(Request $request, $uuid) {
        $request->user->refreshTokens()
            ->findOrFail($uuid)
            ->delete();

        return response()->json(
            null,
            HttpStatusCodes::SUCCESS_NO_CONTENT
        );
    }
}
