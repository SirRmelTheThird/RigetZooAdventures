<?php

declare(strict_types=1);

use App\Models\ProfileContentInterface;
use Core\Constants\RedirectKey;
use Core\CSRF;
use Core\View\Format;
use Core\View\OrderPresenter;
use Core\View\View;

$pageTitle = 'Profile';
require __DIR__ . '/layouts/header.php';

$content   = View::content(ProfileContentInterface::class);
$orderCopy = $content->getOrders();
$account   = $content->getAccount();
$empty     = $content->getEmpty();

ob_start(); ?>
    <form action="<?= RedirectKey::LOGOUT ?>" method="POST">
        <?= CSRF::field() ?>
        <button type="submit" class="rz-btn rz-btn--light rz-btn--block"><?= Format::e($account['logout_label']) ?></button>
    </form>
<?php $actionsHtml = ob_get_clean(); ?>

<div class="rz-container rz-page">
    <?php View::partial('page-header', ['header' => $content->getHeader()]); ?>

    <div class="rz-checkout">
        <section class="rz-card rz-card--raised rz-reveal" aria-labelledby="orders-title">
            <h2 id="orders-title"><?= Format::e($orderCopy['title']) ?></h2>

            <?php if ($orders->isEmpty()): ?>
                <?php
                View::partial('empty-state', [
                    'icon'  => $empty['icon'],
                    'title' => $empty['title'],
                    'text'  => $empty['text'],
                    'actions' => [$empty['primary'] + ['variant' => 'primary']],
                ]);
                ?>
            <?php else: ?>
                <div class="rz-table-wrap">
                    <table class="rz-table">
                        <thead>
                            <tr>
                                <?php foreach ($orderCopy['columns'] as $column): ?>
                                    <th scope="col"><?= Format::e($column) ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td><strong>#<?= (int) $order->id ?></strong></td>
                                    <td><?= Format::date($order->created_at) ?></td>
                                    <td>
                                        <?php if ($order->items->isEmpty()): ?>
                                            <span class="rz-muted"><?= Format::e($orderCopy['general_booking']) ?></span>
                                        <?php endif; ?>
                                        <ul class="rz-table__items">
                                            <?php foreach ($order->items as $item): ?>
                                                <li><?= Format::e(OrderPresenter::itemSummary($item)) ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </td>
                                    <td><strong><?= Format::money($order->total_amount) ?></strong></td>
                                    <td>
                                        <span class="rz-status <?= OrderPresenter::statusClass($order->order_status) ?>">
                                            <?= Format::e($order->order_status->label()) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>

        <?php
        View::partial('summary-panel', [
            'title' => $account['title'],
            'rows' => [
                ['label' => 'Name', 'value' => trim($user->first_name . ' ' . $user->last_name)],
                ['label' => 'Username', 'value' => (string) $user->username],
                ['label' => 'Email', 'value' => (string) $user->email],
                ['label' => 'Member since', 'value' => Format::date($user->created_at)],
                ['label' => 'Reward points', 'value' => (string) $user->totalRewardPoints(), 'strong' => true],
            ],
            'actionsHtml' => $actionsHtml,
        ]);
        ?>
    </div>
</div>

<?php require __DIR__ . '/layouts/footer.php'; ?>