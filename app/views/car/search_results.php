<h2>Available Transfers</h2>
<?php if (!empty($debug)): ?>
    <div style="margin-bottom:2em;">
        <pre style="background:#f8d7da;color:#721c24;padding:1em;border-radius:8px;overflow:auto;max-width:1000px;">Debug Info:
<?= $debug ?></pre>
    </div>
<?php endif; ?>
<?php if (empty($offers)): ?>
    <p>No transfer offers found for your search.</p>
    <a href="/car/search">Back to Search</a>
<?php else: ?>
    <form method="POST" action="/car/book">
    <table border="1" cellpadding="8">
        <tr>
            <th>Vehicle</th>
            <th>Provider</th>
            <th>Type</th>
            <th>Seats</th>
            <th>Price</th>
            <th>Action</th>
        </tr>
        <?php foreach ($offers as $offer): ?>
        <tr>
            <td>
                <?php if (!empty($offer['vehicle']['imageURL'])): ?>
                    <img src="<?= htmlspecialchars($offer['vehicle']['imageURL']) ?>" alt="Vehicle" width="80"><br>
                <?php endif; ?>
                <?= htmlspecialchars($offer['vehicle']['description'] ?? '') ?>
            </td>
            <td>
                <?= htmlspecialchars($offer['serviceProvider']['name'] ?? '') ?><br>
                <?php if (!empty($offer['serviceProvider']['logoUrl'])): ?>
                    <img src="<?= htmlspecialchars($offer['serviceProvider']['logoUrl']) ?>" alt="Provider Logo" width="60">
                <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($offer['transferType'] ?? '') ?></td>
            <td><?= htmlspecialchars($offer['vehicle']['seats'][0]['count'] ?? '') ?></td>
            <td>
                <?= htmlspecialchars($offer['quotation']['monetaryAmount'] ?? '') ?>
                <?= htmlspecialchars($offer['quotation']['currencyCode'] ?? '') ?>
            </td>
            <td>
                <button type="submit" name="offer_id" value="<?= htmlspecialchars($offer['id']) ?>">Book</button>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    </form>
<?php endif; ?>
<a href="/car/search">Back to Search</a> 