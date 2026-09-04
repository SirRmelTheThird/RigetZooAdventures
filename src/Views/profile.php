<?php
require __DIR__ . '/layouts/header.php';
?>

<div class="page-content">
    <header class="page-header reveal">
        <p class="page-kicker">Account</p>
        <h1>Your Profile</h1>
        <p class="page-subtitle">View account details, reward points, and booking history.</p>
    </header>

    <div class="profile-layout">
        <section class="content-card reveal">
            <h2>Order History</h2>

            <?php if ($orders->isEmpty()): ?>
                <div class="empty-state" style="margin-top: 20px;">
                    <h3>No orders yet</h3>
                    <p>Start with a ticket, then return here to review your bookings.</p>
                    <a href="/tickets" class="btn-primary-rza">Start Booking</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Order</th>
                                <th>Date</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td><strong>#<?= $order->id ?></strong></td>
                                    <td><?= $order->created_at ? $order->created_at->format('M j, Y') : 'N/A' ?></td>
                                    <td>
                                        <?php if ($order->items && $order->items->count() > 0): ?>
                                            <ul class="mb-0 ps-3">
                                                <?php foreach ($order->items as $item): ?>
                                                    <li>
                                                        <?php if ($item->isTicket()): ?>
                                                            <?= $item->quantity ?>x <?= htmlspecialchars($item->ticket ? ($item->ticket->type . ' ' . $item->ticket->category) : 'Ticket') ?>
                                                            (<?= $item->start_date ? $item->start_date->format('M j, Y') : 'Flexible' ?>)
                                                        <?php elseif ($item->isAccommodation()): ?>
                                                            <?= htmlspecialchars($item->accommodation ? $item->accommodation->name : 'Accommodation') ?>
                                                            (<?= $item->start_date ? $item->start_date->format('M j') : '' ?> to <?= $item->end_date ? $item->end_date->format('M j, Y') : '' ?>)
                                                        <?php endif; ?>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php else: ?>
                                            <span class="text-muted">General Booking</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><strong>$<?= number_format((float)$order->total_amount, 2) ?></strong></td>
                                    <td>
                                        <?php
                                        $badgeClass = 'bg-warning text-dark';
                                        if ($order->order_status === 'Paid') {
                                            $badgeClass = 'bg-success text-white';
                                        } elseif ($order->order_status === 'Cancelled') {
                                            $badgeClass = 'bg-danger text-white';
                                        }
                                        ?>
                                        <span class="badge <?= $badgeClass ?> px-2 py-1">
                                            <?= htmlspecialchars($order->order_status) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>

        <aside class="cart-summary reveal">
            <h3>Account Information</h3>
            <?php $points = \Models\RewardPoint::getCustomerBalance($user->id); ?>
            <div class="summary-row"><span>Name</span><strong><?= htmlspecialchars($user->first_name ?? '') ?> <?= htmlspecialchars($user->last_name ?? '') ?></strong></div>
            <div class="summary-row"><span>Username</span><strong><?= htmlspecialchars($user->username ?? '') ?></strong></div>
            <div class="summary-row"><span>Email</span><strong><?= htmlspecialchars($user->email ?? '') ?></strong></div>
            <div class="summary-row"><span>Member Since</span><strong><?= $user->created_at ? $user->created_at->format('M j, Y') : 'N/A' ?></strong></div>
            <div class="summary-row"><span>Reward Points</span><strong><?= $points ?></strong></div>
            <a href="/logout" class="btn btn-secondary" style="width: 100%; margin-top: 20px;">Logout</a>
        </aside>
    </div>
</div>

<?php require __DIR__ . '/layouts/footer.php'; ?>
