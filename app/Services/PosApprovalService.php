<?php

namespace App\Services;

use App\Models\PosAuditLog;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class PosApprovalService
{
    public function authorizeCartChanges(array $payload, int $actorUserId): array
    {
        $voidEvents = collect($payload['void_events'] ?? [])->filter(fn ($event) => !empty($event['item_name']))->values();
        $discountValue = max(0, (float) ($payload['discount_value'] ?? 0));
        $discountType = (string) ($payload['discount_type'] ?? 'fixed');
        $subtotal = max(0, (float) ($payload['subtotal'] ?? 0));
        $discountAmount = $discountType === 'percent'
            ? $subtotal * min($discountValue, 100) / 100
            : $discountValue;
        $threshold = max(0, (float) (Setting::query()->where('key', 'discount_approval_threshold')->value('value') ?? 10));
        $discountPercent = $subtotal > 0 ? ($discountAmount / $subtotal) * 100 : 0;

        $needsVoidApproval = $voidEvents->isNotEmpty();
        $needsDiscountApproval = $discountAmount > 0 && $discountPercent >= $threshold;

        if (!$needsVoidApproval && !$needsDiscountApproval) {
            return [
                'approved' => false,
                'approver_id' => null,
                'matched' => null,
                'void_events' => $voidEvents->all(),
            ];
        }

        if ($needsDiscountApproval && blank($payload['discount_reason'] ?? null)) {
            throw new \RuntimeException('Discount reason is required before posting this bill.');
        }

        if ($needsVoidApproval && $voidEvents->contains(fn ($event) => blank($event['reason'] ?? null))) {
            throw new \RuntimeException('Each voided item needs a reason before the order can be saved.');
        }

        $approval = $this->resolveApproval((string) ($payload['manager_pin'] ?? ''));

        if (!$approval['approved']) {
            throw new \RuntimeException('Manager approval failed. Enter a valid manager PIN or manager/admin password.');
        }

        return $approval + ['void_events' => $voidEvents->all()];
    }

    public function logCartControls(array $approval, int $actorUserId, array $context): void
    {
        $voidEvents = collect($context['void_events'] ?? []);
        $saleId = $context['sale_id'] ?? null;
        $orderId = $context['order_id'] ?? null;

        if (($context['discount_amount'] ?? 0) > 0) {
            PosAuditLog::query()->create([
                'action_type' => 'discount_override',
                'actor_user_id' => $actorUserId,
                'approver_user_id' => $approval['approver_id'] ?? null,
                'sale_id' => $saleId,
                'order_id' => $orderId,
                'context' => [
                    'discount_amount' => round((float) $context['discount_amount'], 2),
                    'discount_type' => $context['discount_type'] ?? 'fixed',
                    'discount_value' => $context['discount_value'] ?? 0,
                    'reason' => $context['discount_reason'] ?? null,
                    'approval_source' => $approval['matched'] ?? null,
                    'reference' => $context['order_number'] ?? null,
                ],
            ]);
        }

        foreach ($voidEvents as $event) {
            PosAuditLog::query()->create([
                'action_type' => 'item_voided',
                'actor_user_id' => $actorUserId,
                'approver_user_id' => $approval['approver_id'] ?? null,
                'sale_id' => $saleId,
                'order_id' => $orderId,
                'context' => [
                    'item_id' => $event['item_id'] ?? null,
                    'item_name' => $event['item_name'] ?? null,
                    'qty' => $event['qty'] ?? null,
                    'reason' => $event['reason'] ?? null,
                    'approval_source' => $approval['matched'] ?? null,
                    'reference' => $context['order_number'] ?? null,
                    'status' => $context['status'] ?? null,
                ],
            ]);
        }
    }

    public function authorizeManagerAction(string $secret, string $reason): array
    {
        if (blank(trim($reason))) {
            throw new \RuntimeException('Reason is required for this controlled action.');
        }

        $approval = $this->resolveApproval($secret);
        if (! $approval['approved']) {
            throw new \RuntimeException('Manager approval failed. Enter a valid manager PIN or manager/admin password.');
        }

        return $approval;
    }

    private function resolveApproval(string $secret): array
    {
        if (blank($secret)) {
            return ['approved' => false, 'approver_id' => null, 'matched' => null];
        }

        $overrideHash = Setting::query()->where('key', 'manager_override_pin')->value('value');
        if ($overrideHash && Hash::check($secret, (string) $overrideHash)) {
            return ['approved' => true, 'approver_id' => null, 'matched' => 'settings_override_pin'];
        }

        $approver = User::query()
            ->whereIn('role', ['manager', 'admin'])
            ->where('is_active', true)
            ->get()
            ->first(fn (User $user) => Hash::check($secret, (string) $user->getAuthPassword()));

        if ($approver) {
            return ['approved' => true, 'approver_id' => $approver->id, 'matched' => 'manager_password'];
        }

        return ['approved' => false, 'approver_id' => null, 'matched' => null];
    }
}
