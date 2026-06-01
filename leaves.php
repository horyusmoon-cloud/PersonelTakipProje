<?php
// leaves.php
require_once 'config/database.php';
require_once 'includes/header.php';

$action = $_GET['action'] ?? 'list';
$error = '';
$success = '';

// Fetch Personnel for dropdowns
$personnel_list = $pdo->query("SELECT id, first_name, last_name FROM personnel WHERE status = 'Aktif' ORDER BY first_name ASC")->fetchAll();

// Handle actions
if ($action == 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM leaves WHERE id = ?");
    if($stmt->execute([$id])) {
        header("Location: leaves.php?msg=deleted");
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $personnel_id = $_POST['personnel_id'] ?? '';
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';
    $type = $_POST['type'] ?? 'Yıllık İzin';
    $status = $_POST['status'] ?? 'Bekliyor';
    $reason = trim($_POST['reason'] ?? '');

    if (empty($personnel_id) || empty($start_date) || empty($end_date)) {
        $error = "Lütfen personel, başlangıç ve bitiş tarihlerini doldurun.";
    } elseif ($start_date > $end_date) {
        $error = "Bitiş tarihi başlangıç tarihinden önce olamaz.";
    } else {
        if ($action == 'add') {
            $stmt = $pdo->prepare("INSERT INTO leaves (personnel_id, start_date, end_date, type, status, reason) VALUES (?, ?, ?, ?, ?, ?)");
            if($stmt->execute([$personnel_id, $start_date, $end_date, $type, $status, $reason])) {
                header("Location: leaves.php?msg=added");
                exit;
            }
        } elseif ($action == 'edit' && isset($_GET['id'])) {
            $id = $_GET['id'];
            $stmt = $pdo->prepare("UPDATE leaves SET personnel_id=?, start_date=?, end_date=?, type=?, status=?, reason=? WHERE id=?");
            if($stmt->execute([$personnel_id, $start_date, $end_date, $type, $status, $reason, $id])) {
                header("Location: leaves.php?msg=updated");
                exit;
            }
        }
    }
}

// Fetch data for forms or lists
$leave = null;
if ($action == 'edit' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM leaves WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $leave = $stmt->fetch();
    if (!$leave) {
        die("İzin kaydı bulunamadı.");
    }
}

$leaves = [];
if ($action == 'list') {
    $leaves = $pdo->query("
        SELECT l.*, p.first_name, p.last_name 
        FROM leaves l 
        JOIN personnel p ON l.personnel_id = p.id 
        ORDER BY l.created_at DESC
    ")->fetchAll();
}
?>

<div class="mb-8 flex justify-between items-center">
    <div>
        <h2 class="text-3xl font-bold text-gray-800">İzin ve Devamsızlık</h2>
        <p class="text-gray-600 mt-1">Personel izin taleplerini takip edin.</p>
    </div>
    <?php if ($action == 'list'): ?>
    <a href="?action=add" class="bg-corporate-600 hover:bg-corporate-700 text-white px-4 py-2 rounded-lg transition-colors shadow-sm flex items-center">
        <i class="fas fa-plus mr-2"></i> İzin Talebi Ekle
    </a>
    <?php else: ?>
    <a href="leaves.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors shadow-sm flex items-center">
        <i class="fas fa-arrow-left mr-2"></i> Geri Dön
    </a>
    <?php endif; ?>
</div>

<?php if (isset($_GET['msg'])): ?>
    <div class="bg-green-50 text-green-700 p-4 rounded-lg mb-6 border border-green-200">
        <?php 
        if($_GET['msg'] == 'added') echo "İzin talebi başarıyla eklendi.";
        if($_GET['msg'] == 'updated') echo "İzin talebi güncellendi.";
        if($_GET['msg'] == 'deleted') echo "İzin talebi sistemden silindi.";
        ?>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="bg-red-50 text-red-700 p-4 rounded-lg mb-6 border border-red-200">
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<?php if ($action == 'list'): ?>
    <!-- List View -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-gray-500 text-sm uppercase tracking-wider">
                        <th class="px-6 py-4 font-medium">Personel</th>
                        <th class="px-6 py-4 font-medium">İzin Türü</th>
                        <th class="px-6 py-4 font-medium">Tarih Aralığı</th>
                        <th class="px-6 py-4 font-medium">Durum</th>
                        <th class="px-6 py-4 font-medium text-right">İşlemler</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if(empty($leaves)): ?>
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">Kayıtlı izin talebi bulunamadı.</td>
                    </tr>
                    <?php else: foreach ($leaves as $l): ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 font-medium text-gray-800">
                            <?= htmlspecialchars($l['first_name'] . ' ' . $l['last_name']) ?>
                        </td>
                        <td class="px-6 py-4 text-gray-600"><?= htmlspecialchars($l['type']) ?></td>
                        <td class="px-6 py-4 text-gray-600">
                            <?= date('d.m.Y', strtotime($l['start_date'])) ?> - <?= date('d.m.Y', strtotime($l['end_date'])) ?>
                        </td>
                        <td class="px-6 py-4">
                            <?php 
                            if($l['status'] == 'Onaylandı') {
                                echo '<span class="px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">Onaylandı</span>';
                            } elseif($l['status'] == 'Reddedildi') {
                                echo '<span class="px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">Reddedildi</span>';
                            } else {
                                echo '<span class="px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Bekliyor</span>';
                            }
                            ?>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="?action=edit&id=<?= $l['id'] ?>" class="text-blue-600 hover:text-blue-900 mx-1 p-2 rounded hover:bg-blue-50 transition-colors" title="İncele / Düzenle">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="?action=delete&id=<?= $l['id'] ?>" onclick="return confirm('Bu izni silmek istediğinize emin misiniz?')" class="text-red-600 hover:text-red-900 p-2 rounded hover:bg-red-50 transition-colors" title="Sil">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php else: ?>
    <!-- Form View (Add / Edit) -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 md:p-8 max-w-3xl">
        <form method="POST" action="">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="personnel_id">Personel <span class="text-red-500">*</span></label>
                    <select class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-corporate-600 focus:ring-1 focus:ring-corporate-600 bg-white" id="personnel_id" name="personnel_id" required>
                        <option value="">Personel Seçiniz...</option>
                        <?php 
                        $selected_person = $_POST['personnel_id'] ?? $leave['personnel_id'] ?? '';
                        foreach ($personnel_list as $p): 
                        ?>
                            <option value="<?= $p['id'] ?>" <?= $selected_person == $p['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p['first_name'] . ' ' . $p['last_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="type">İzin Türü <span class="text-red-500">*</span></label>
                    <select class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-corporate-600 focus:ring-1 focus:ring-corporate-600 bg-white" id="type" name="type" required>
                        <?php $selected_type = $_POST['type'] ?? $leave['type'] ?? 'Yıllık İzin'; ?>
                        <option value="Yıllık İzin" <?= $selected_type == 'Yıllık İzin' ? 'selected' : '' ?>>Yıllık İzin</option>
                        <option value="Mazeret İzni" <?= $selected_type == 'Mazeret İzni' ? 'selected' : '' ?>>Mazeret İzni</option>
                        <option value="Hastalık İzni" <?= $selected_type == 'Hastalık İzni' ? 'selected' : '' ?>>Hastalık İzni</option>
                        <option value="Ücretsiz İzin" <?= $selected_type == 'Ücretsiz İzin' ? 'selected' : '' ?>>Ücretsiz İzin</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="start_date">Başlangıç Tarihi <span class="text-red-500">*</span></label>
                    <input class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-corporate-600 focus:ring-1 focus:ring-corporate-600" id="start_date" name="start_date" type="date" value="<?= htmlspecialchars($_POST['start_date'] ?? $leave['start_date'] ?? date('Y-m-d')) ?>" required>
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="end_date">Bitiş Tarihi <span class="text-red-500">*</span></label>
                    <input class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-corporate-600 focus:ring-1 focus:ring-corporate-600" id="end_date" name="end_date" type="date" value="<?= htmlspecialchars($_POST['end_date'] ?? $leave['end_date'] ?? date('Y-m-d', strtotime('+1 day'))) ?>" required>
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="reason">Açıklama / Mazeret</label>
                <textarea class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-corporate-600 focus:ring-1 focus:ring-corporate-600" id="reason" name="reason" rows="3"><?= htmlspecialchars($_POST['reason'] ?? $leave['reason'] ?? '') ?></textarea>
            </div>

            <div class="mb-8">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="status">Onay Durumu <span class="text-red-500">*</span></label>
                <select class="w-full md:w-1/2 px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-corporate-600 focus:ring-1 focus:ring-corporate-600 bg-white" id="status" name="status" required>
                    <?php $selected_status = $_POST['status'] ?? $leave['status'] ?? 'Bekliyor'; ?>
                    <option value="Bekliyor" <?= $selected_status == 'Bekliyor' ? 'selected' : '' ?>>Bekliyor</option>
                    <option value="Onaylandı" <?= $selected_status == 'Onaylandı' ? 'selected' : '' ?>>Onaylandı</option>
                    <option value="Reddedildi" <?= $selected_status == 'Reddedildi' ? 'selected' : '' ?>>Reddedildi</option>
                </select>
            </div>

            <div class="flex items-center justify-end pt-4 border-t border-gray-100">
                <a href="leaves.php" class="text-gray-500 hover:text-gray-700 font-medium mr-4 transition-colors">İptal</a>
                <button type="submit" class="bg-corporate-600 hover:bg-corporate-700 text-white font-bold py-3 px-8 rounded-lg transition-colors shadow-sm">
                    <?= $action == 'add' ? 'İzin Talebini Kaydet' : 'Değişiklikleri Kaydet' ?>
                </button>
            </div>
        </form>
    </div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
