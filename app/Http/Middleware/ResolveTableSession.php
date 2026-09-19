<?php

namespace App\Http\Middleware;

use App\Enums\SessionStatus;
use App\Models\OrderSession;
use App\Services\Customer\CustomerSessionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveTableSession
{
    public function __construct(private readonly CustomerSessionService $sessions)
    {
    }

    /**
     * Resolves the customer's active table session from the httpOnly cookie set by
     * CustomerSessionService::startSession(), so pages never trust anything the
     * customer's browser could tamper with beyond "which session_token am I holding."
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->cookie(CustomerSessionService::SESSION_COOKIE);

        $session = $token
            ? OrderSession::where('session_token', $token)->where('status', SessionStatus::Active)->first()
            : null;

        if ($session && $session->updated_at->lt(now()->subHours(CustomerSessionService::SESSION_TTL_HOURS))) {
            $session->update(['status' => SessionStatus::Expired]);
            $session = null;
        }

        if (! $session) {
            return redirect()->route('customer.scan-help')
                ->with('scan_reason', 'Your table session has ended. Please scan the QR code on your table again.');
        }

        $memberNo = $request->cookie($this->sessions->memberCookieName($session->id));

        if (! $memberNo || ! $session->members()->where('member_no', $memberNo)->exists()) {
            return redirect()->route('customer.scan-help')
                ->with('scan_reason', 'We couldn\'t find your table session. Please scan the QR code on your table again.');
        }

        $request->attributes->set('customerSession', $session);
        $request->attributes->set('customerTable', $session->table);
        $request->attributes->set('customerMemberNo', (int) $memberNo);

        return $next($request);
    }
}
