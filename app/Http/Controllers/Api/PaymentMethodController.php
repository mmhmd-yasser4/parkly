<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentMethodController extends Controller
{
    public function index(Request $request)
    {
        $methods = $request->user()
            ->paymentMethods()
            ->whereNull('deleted_at')
            ->get();

        return response()->json($methods);
    }

    public function setDefault(Request $request, $id)
    {
        $user   = $request->user();
        $method = PaymentMethod::whereNull('deleted_at')->findOrFail($id);

        if ($method->user_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        DB::transaction(function () use ($user, $method) {
            $user->paymentMethods()->whereNull('deleted_at')->update(['is_default' => false]);
            $method->update(['is_default' => true]);
        });

        return response()->json($method->fresh());
    }

    public function destroy(Request $request, $id)
    {
        $user   = $request->user();
        $method = PaymentMethod::whereNull('deleted_at')->findOrFail($id);

        if ($method->user_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $wasDefault = $method->is_default;
        $method->delete();

        if ($wasDefault) {
            $next = $user->paymentMethods()->whereNull('deleted_at')->orderByDesc('created_at')->first();
            if ($next) {
                $next->update(['is_default' => true]);
            }
        }

        return response()->json(['message' => 'Payment method removed']);
    }
}
