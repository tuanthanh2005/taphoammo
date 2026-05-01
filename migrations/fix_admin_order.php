<?php
$c = file_get_contents('d:/aicuatoi/app/Views/admin/order_detail.php');
$code = <<<EOT
                            <?php foreach (\$items as \$item): ?>
                            <tr>
                                <td>
                                    <strong><?= e(\$item['product_name']) ?></strong>
                                    <div><span class="badge bg-secondary"><?= e(\$item['item_status'] ?? 'processing') ?></span></div>
                                </td>
                                <td><?= e(\$item['seller_name']) ?></td>
                                <td><?= money(\$item['price']) ?></td>
                                <td><?= \$item['quantity'] ?></td>
                                <td class="fw-bold"><?= money(\$item['subtotal']) ?></td>
                            </tr>
                            <?php endforeach; ?>
EOT;
$search = <<<EOT
                            <?php foreach (\$items as \$item): ?>
                            <tr>
                                <td>
                                    <strong><?= e(\$item['product_name']) ?></strong>
                                </td>
                                <td><?= e(\$item['seller_name']) ?></td>
                                <td><?= money(\$item['price']) ?></td>
                                <td><?= \$item['quantity'] ?></td>
                                <td class="fw-bold"><?= money(\$item['subtotal']) ?></td>
                            </tr>
                            <?php endforeach; ?>
EOT;

$c = str_replace($search, $code, $c);
file_put_contents('d:/aicuatoi/app/Views/admin/order_detail.php', $c);
