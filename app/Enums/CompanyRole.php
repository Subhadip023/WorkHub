<?php

namespace App\Enums;

/**
 * Role of a user inside one company (stored in company_users.role).
 *
 * Stored values are kept stable for existing data (0 = member, 1 = admin);
 * use rank() for comparisons, never the raw value.
 */
enum CompanyRole: int
{
    case Member = 0;
    case Admin = 1;
    case Manager = 2;
    case Owner = 3;

    /** Hierarchy position: higher outranks lower. */
    public function rank(): int
    {
        return match ($this) {
            self::Member => 0,
            self::Manager => 1,
            self::Admin => 2,
            self::Owner => 3,
        };
    }

    public function label(): string
    {
        return $this->name;
    }

    public function description(): string
    {
        return match ($this) {
            self::Owner => 'Full control, including deleting the organization and transferring ownership. Exactly one per organization.',
            self::Admin => 'Manages members, invitations, organization settings, the recycle bin and the activity log.',
            self::Manager => 'Manages every project: edits projects, credentials and API keys, and updates any task.',
            self::Member => 'Creates and works on tasks, notes and issues; edits tasks they created or are assigned to.',
        };
    }

    public function atLeast(self $minimum): bool
    {
        return $this->rank() >= $minimum->rank();
    }

    public function outranks(self $other): bool
    {
        return $this->rank() > $other->rank();
    }

    /**
     * Roles this actor may grant to someone else: strictly below their own rank.
     * Owner is never assignable (use ownership transfer).
     *
     * @return list<self>
     */
    public static function assignableBy(self $actor): array
    {
        return array_values(array_filter(
            self::cases(),
            fn (self $role) => $role !== self::Owner && $actor->outranks($role)
        ));
    }

    /** Roles that count as "administrator" for legacy checks (Admin and Owner). */
    public static function adminValues(): array
    {
        return [self::Admin->value, self::Owner->value];
    }

    /** Ordered from most to least privileged, for display. */
    public static function ordered(): array
    {
        $cases = self::cases();
        usort($cases, fn (self $a, self $b) => $b->rank() <=> $a->rank());

        return $cases;
    }

    /** Unknown stored values are treated as the least privileged role. */
    public static function fromStored(mixed $value): self
    {
        return self::tryFrom((int) $value) ?? self::Member;
    }
}
