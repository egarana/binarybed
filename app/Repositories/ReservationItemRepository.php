<?php

namespace App\Repositories;

use App\Models\ReservationItem;

class ReservationItemRepository
{
    /**
     * Create a new reservation item with snapshotting.
     *
     * @param array $data
     * @return ReservationItem
     */
    public function create(array $data): ReservationItem
    {
        return ReservationItem::create($data);
    }

    /**
     * Update a reservation item.
     *
     * @param ReservationItem $item
     * @param array $data
     * @return ReservationItem
     */
    public function update(ReservationItem $item, array $data): ReservationItem
    {
        $item->update($data);

        return $item->fresh();
    }

    /**
     * Cancel a reservation item (soft cancel via status).
     *
     * @param ReservationItem $item
     * @return ReservationItem
     */
    public function cancel(ReservationItem $item): ReservationItem
    {
        $item->update(['status' => ReservationItem::STATUS_CANCELLED]);

        return $item->fresh();
    }

    /**
     * Reactivate a cancelled item.
     *
     * @param ReservationItem $item
     * @return ReservationItem
     */
    public function reactivate(ReservationItem $item): ReservationItem
    {
        $item->update(['status' => ReservationItem::STATUS_ACTIVE]);

        return $item->fresh();
    }
}
