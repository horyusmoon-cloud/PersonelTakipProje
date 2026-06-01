<?php
// index.php
require_once 'config/database.php';
require_once 'includes/header.php';

// Fetch Statistics
$total_personnel = $pdo->query("SELECT COUNT(*) FROM personnel")->fetchColumn();
$total_departments = $pdo->query("SELECT COUNT(*) FROM departments")->fetchColumn();
$pending_leaves = $pdo->query("SELECT COUNT(*) FROM leaves WHERE status = 'Bekliyor'")->fetchColumn();

// Fetch Recent Personnel
$recent_personnel = $pdo->query("
    SELECT p.*, d.name as department_name 
    FROM personnel p 
    LEFT JOIN departments d ON p.department_id = d.id 
    ORDER BY p.created_at DESC 
    LIMIT 5
")->fetchAll();
?>

<div class="mb-8">
    <h2 class="text-3xl font-bold text-gray-800">Dashboard</h2>
    <p class="text-gray-600 mt-1">Sisteme hoş geldiniz, işte güncel özet.</p>
</div>

<!-- Stats Grid -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center">
        <div class="bg-blue-50 text-corporate-600 rounded-lg w-12 h-12 flex items-center justify-center text-xl mr-4">
            <i class="fas fa-users"></i>
        </div>
        <div>
            <div class="text-gray-500 text-sm font-medium">Toplam Personel</div>
            <div class="text-2xl font-bold text-gray-800"><?= $total_personnel ?></div>
        </div>
    </div>
    
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center">
        <div class="bg-indigo-50 text-indigo-600 rounded-lg w-12 h-12 flex items-center justify-center text-xl mr-4">
            <i class="fas fa-building"></i>
        </div>
        <div>
            <div class="text-gray-500 text-sm font-medium">Departman Sayısı</div>
            <div class="text-2xl font-bold text-gray-800"><?= $total_departments ?></div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center">
        <div class="bg-orange-50 text-orange-600 rounded-lg w-12 h-12 flex items-center justify-center text-xl mr-4">
            <i class="fas fa-calendar-check"></i>
        </div>
        <div>
            <div class="text-gray-500 text-sm font-medium">Bekleyen İzinler</div>
            <div class="text-2xl font-bold text-gray-800"><?= $pending_leaves ?></div>
        </div>
    </div>
</div>

<!-- Recent Personnel Table -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
        <h3 class="text-lg font-semibold text-gray-800">Son Eklenen Personeller</h3>
        <a href="personnel.php" class="text-sm text-corporate-600 hover:text-corporate-800 font-medium">Tümünü Gör &rarr;</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 text-gray-500 text-sm uppercase tracking-wider">
                    <th class="px-6 py-3 font-medium">Ad Soyad</th>
                    <th class="px-6 py-3 font-medium">Departman</th>
                    <th class="px-6 py-3 font-medium">Pozisyon</th>
                    <th class="px-6 py-3 font-medium">Durum</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if(empty($recent_personnel)): ?>
                <tr>
                    <td colspan="4" class="px-6 py-8 text-center text-gray-500">Henüz personel eklenmemiş.</td>
                </tr>
                <?php else: ?>
                    <?php foreach ($recent_personnel as $person): ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="h-10 w-10 rounded-full bg-corporate-100 text-corporate-800 flex items-center justify-center font-bold mr-3">
                                    <?= mb_substr($person['first_name'], 0, 1) . mb_substr($person['last_name'], 0, 1) ?>
                                </div>
                                <div>
                                    <div class="font-medium text-gray-800"><?= htmlspecialchars($person['first_name'] . ' ' . $person['last_name']) ?></div>
                                    <div class="text-sm text-gray-500"><?= htmlspecialchars($person['email']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-gray-600"><?= htmlspecialchars($person['department_name'] ?? 'Belirtilmedi') ?></td>
                        <td class="px-6 py-4 text-gray-600"><?= htmlspecialchars($person['position']) ?></td>
                        <td class="px-6 py-4">
                            <?php if($person['status'] == 'Aktif'): ?>
                                <span class="px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">Aktif</span>
                            <?php else: ?>
                                <span class="px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">Pasif</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
