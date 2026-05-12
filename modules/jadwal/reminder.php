<?php
$reminders = fetch_all('SELECT id, tujuan, pesan, status, created_at FROM notifications ORDER BY created_at DESC LIMIT 20');
?>
<div class="card p-6">
    <h3 class="text-xl font-semibold mb-4">Reminder Otomatis</h3>
    <p class="text-slate-500 mb-6">Sistem ini mencatat notifikasi yang dikirim. Jalankan cron atau panggil endpoint API untuk otomatisasi.</p>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-slate-600">
                <tr>
                    <th class="px-4 py-3 text-left">Tujuan</th>
                    <th class="px-4 py-3 text-left">Pesan</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Waktu</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 bg-white">
                <?php if (count($reminders) === 0): ?>
                    <tr>
                        <td colspan="4" class="px-4 py-5 text-center text-slate-500">Belum ada notifikasi.</td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($reminders as $item): ?>
                    <tr>
                        <td class="px-4 py-3"><?php echo sanitize($item['tujuan']); ?></td>
                        <td class="px-4 py-3"><?php echo sanitize($item['pesan']); ?></td>
                        <td class="px-4 py-3"><?php echo sanitize($item['status']); ?></td>
                        <td class="px-4 py-3"><?php echo sanitize($item['created_at']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
