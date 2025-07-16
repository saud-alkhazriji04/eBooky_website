<?php 
$base_path = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/';
?>
<link rel="stylesheet" href="<?php echo $base_path; ?>css/style.css">
<?php include __DIR__ . '/../partials/navbar.php'; ?>
<div style="max-width:900px;margin:2rem auto 2rem auto;">
    <a href="<?php echo $base_path; ?>car/search" style="color:#003580;text-decoration:none;font-weight:500;display:inline-block;margin-bottom:1.5rem;">&larr; Back</a>
    <!-- Progress Stepper -->
    <div style="display:flex;justify-content:center;align-items:center;gap:2.5rem;margin-bottom:2.5rem;">
        <?php $steps = ['Search', 'Details', 'Payment', 'Confirmation']; $current = 2; ?>
        <?php foreach ($steps as $i => $step): ?>
            <div style="display:flex;flex-direction:column;align-items:center;">
                <div style="width:38px;height:38px;border-radius:50%;background:<?= ($i+1)==$current?'#003580':'#e0e7ef' ?>;color:<?= ($i+1)==$current?'#fff':'#003580' ?>;display:flex;align-items:center;justify-content:center;font-weight:600;font-size:1.2rem;"> <?= $i+1 ?> </div>
                <div style="margin-top:0.5em;font-size:1.05em;color:<?= ($i+1)==$current?'#003580':'#888' ?>;font-weight:<?= ($i+1)==$current?'600':'400' ?>;"> <?= $step ?> </div>
            </div>
            <?php if ($i < count($steps)-1): ?>
                <div style="width:48px;height:3px;background:#e0e7ef;margin:0 0.5em;"></div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
    <?php if (empty($offer)): ?>
        <div style="background:#fff;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,0.06);padding:2rem;text-align:center;margin-top:2rem;">
            <p>Transfer offer not found.</p>
        </div>
    <?php else: ?>
        <div style="background:#fff;border-radius:16px;box-shadow:0 2px 16px rgba(0,0,0,0.08);display:flex;gap:2rem;padding:2rem 2.5rem;margin-top:2rem;align-items:flex-start;">
            <div>
                <?php if (!empty($offer['vehicle']['imageURL'])): ?>
                    <img src="<?= htmlspecialchars($offer['vehicle']['imageURL']) ?>" alt="Vehicle" style="width:220px;border-radius:10px;">
                <?php endif; ?>
            </div>
            <div style="flex:1;">
                <h2 style="margin-top:0;"><?= htmlspecialchars($offer['vehicle']['description'] ?? 'Vehicle') ?></h2>
                <div style="margin-bottom:1em;color:#555;">
                    <b>Provider:</b> <?= htmlspecialchars($offer['serviceProvider']['name'] ?? '') ?><br>
                    <b>Type:</b> <?= htmlspecialchars($offer['transferType'] ?? '') ?><br>
                    <b>Seats:</b> <?= htmlspecialchars($offer['vehicle']['seats'][0]['count'] ?? '') ?><br>
                </div>
                <div style="margin-bottom:1em;">
                    <b>Price:</b> <span style="font-size:1.3rem;color:#fc5c7d;font-weight:600;">
                        <?= htmlspecialchars($offer['quotation']['monetaryAmount'] ?? '') ?> <?= htmlspecialchars($offer['quotation']['currencyCode'] ?? '') ?>
                    </span>
                </div>
                <?php if (!empty($offer['cancellationRules'])): ?>
                    <div style="margin-bottom:1em;">
                        <b>Cancellation Policy:</b>
                        <ul style="margin:0.5em 0 0 1.2em;">
                        <?php foreach ($offer['cancellationRules'] as $rule): ?>
                            <li><?= htmlspecialchars($rule['ruleDescription'] ?? '') ?></li>
                        <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                <form method="GET" action="<?php echo $base_path; ?>car/payment">
                    <input type="hidden" name="offer_id" value="<?= htmlspecialchars($offer['id']) ?>">
                    <button type="submit" class="btn-primary">Book</button>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php include __DIR__ . '/../partials/footer.php'; ?> 