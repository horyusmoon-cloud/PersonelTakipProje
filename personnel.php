<?php
// personnel.php
require_once 'config/database.php';
require_once 'includes/header.php';

$action = $_GET['action'] ?? 'list';
$error = '';
$success = '';

// Fetch Departments for dropdowns
$departments = $pdo->query("SELECT * FROM departments ORDER BY name ASC")->fetchAll();

// Handle actions
if ($action == 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM personnel WHERE id = ?");
    if($stmt->execute([$id])) {
        header("Location: personnel.php?msg=deleted");
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $department_id = !empty($_POST['department_id']) ? $_POST['department_id'] : null;
    $position = trim($_POST['position'] ?? '');
    $hire_date = trim($_POST['hire_date'] ?? '');
    $status = $_POST['status'] ?? 'Aktif';

    if (empty($first_name) || empty($last_name) || empty($email) || empty($hire_date)) {
        $error = "Lütfen zorunlu alanları (Ad, Soyad, E-posta, İşe Giriş Tarihi) doldurun.";
    } else {
        if ($action == 'add') {
            // Check if email exists
            $stmt = $pdo->prepare("SELECT id FROM personnel WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = "Bu e-posta adresi zaten kullanılıyor.";
            } else {
                $stmt = $pdo->prepare("INSERT INTO personnel (first_name, last_name, email, phone, department_id, position, hire_date, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                if($stmt->execute([$first_name, $last_name, $email, $phone, $department_id, $position, $hire_date, $status])) {
                    header("Location: personnel.php?msg=added");
                    exit;
                }
            }
        } elseif ($action == 'edit' && isset($_GET['id'])) {
            $id = $_GET['id'];
            // Check if email exists for other users
            $stmt = $pdo->prepare("SELECT id FROM personnel WHERE email = ? AND id != ?");
            $stmt->execute([$email, $id]);
            if ($stmt->fetch()) {
                $error = "Bu e-posta adresi başka bir personel tarafından kullanılıyor.";
            } else {
                $stmt = $pdo->prepare("UPDATE personnel SET first_name=?, last_name=?, email=?, phone=?, department_id=?, position=?, hire_date=?, status=? WHERE id=?");
                if($stmt->execute([$first_name, $last_name, $email, $phone, $department_id, $position, $hire_date, $status, $id])) {
                    header("Location: personnel.php?msg=updated");
                    exit;
                }
            }
        }
    }
}

// Fetch data for forms or lists
$person = null;
if ($action == 'edit' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM personnel WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $person = $stmt->fetch();
    if (!$person) {
        die("Personel bulunamadı.");
    }
}

$personnel_list = [];
if ($action == 'list') {
    $personnel_list = $pdo->query("
        SELECT p.*, d.name as department_name 
        FROM personnel p 
        LEFT JOIN departments d ON p.department_id = d.id 
        ORDER BY p.first_name ASC
    ")->fetchAll();
}
?>

<div class="mb-8 flex justify-between items-center">
    <div>
        <h2 class="text-3xl font-bold text-gray-800">Personel Yönetimi</h2>
        <p class="text-gray-600 mt-1">Şirket çalışanlarını görüntüleyin ve yönetin.</p>
    </div>
    <?php if ($action == 'list'): ?>
    <a href="?action=add" class="bg-corporate-600 hover:bg-corporate-700 text-white px-4 py-2 rounded-lg transition-colors shadow-sm flex items-center">
        <i class="fas fa-plus mr-2"></i> Yeni Personel
    </a>
    <?php else: ?>
    <a href="personnel.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors shadow-sm flex items-center">
        <i class="fas fa-arrow-left mr-2"></i> Geri Dön
    </a>
    <?php endif; ?>
</div>

<?php if (isset($_GET['msg'])): ?>
    <div class="bg-green-50 text-green-700 p-4 rounded-lg mb-6 border border-green-200">
        <?php 
        if($_GET['msg'] == 'added') echo "Personel başarıyla eklendi.";
        if($_GET['msg'] == 'updated') echo "Personel bilgileri güncellendi.";
        if($_GET['msg'] == 'deleted') echo "Personel sistemden silindi.";
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
                        <th class="px-6 py-4 font-medium">İletişim</th>
                        <th class="px-6 py-4 font-medium">Departman / Pozisyon</th>
                        <th class="px-6 py-4 font-medium">Durum</th>
                        <th class="px-6 py-4 font-medium text-right">İşlemler</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if(empty($personnel_list)): ?>
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">Kayıtlı personel bulunamadı.</td>
                    </tr>
                    <?php else: foreach ($personnel_list as $p): ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="h-10 w-10 rounded-full bg-corporate-100 text-corporate-800 flex items-center justify-center font-bold mr-3">
                                    <?= mb_substr($p['first_name'], 0, 1) . mb_substr($p['last_name'], 0, 1) ?>
                                </div>
                                <div>
                                    <div class="font-medium text-gray-800"><?= htmlspecialchars($p['first_name'] . ' ' . $p['last_name']) ?></div>
                                    <div class="text-sm text-gray-500">İşe Giriş: <?= date('d.m.Y', strtotime($p['hire_date'])) ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-800"><i class="fas fa-envelope text-gray-400 mr-2"></i><?= htmlspecialchars($p['email']) ?></div>
                            <div class="text-sm text-gray-600 mt-1"><i class="fas fa-phone text-gray-400 mr-2"></i><?= htmlspecialchars($p['phone'] ?: '-') ?></div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-gray-800"><?= htmlspecialchars($p['department_name'] ?? 'Belirtilmedi') ?></div>
                            <div class="text-sm text-gray-500"><?= htmlspecialchars($p['position'] ?? '-') ?></div>
                        </td>
                        <td class="px-6 py-4">
                            <?php if($p['status'] == 'Aktif'): ?>
                                <span class="px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">Aktif</span>
                            <?php else: ?>
                                <span class="px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">Pasif</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="?action=edit&id=<?= $p['id'] ?>" class="text-blue-600 hover:text-blue-900 mx-1 p-2 rounded hover:bg-blue-50 transition-colors" title="Düzenle">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="?action=delete&id=<?= $p['id'] ?>" onclick="return confirm('Bu personeli silmek istediğinize emin misiniz?')" class="text-red-600 hover:text-red-900 p-2 rounded hover:bg-red-50 transition-colors" title="Sil">
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
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 md:p-8">
        <form method="POST" action="">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="first_name">Ad <span class="text-red-500">*</span></label>
                    <input class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-corporate-600 focus:ring-1 focus:ring-corporate-600" id="first_name" name="first_name" type="text" value="<?= htmlspecialchars($_POST['first_name'] ?? $person['first_name'] ?? '') ?>" required>
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="last_name">Soyad <span class="text-red-500">*</span></label>
                    <input class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-corporate-600 focus:ring-1 focus:ring-corporate-600" id="last_name" name="last_name" type="text" value="<?= htmlspecialchars($_POST['last_name'] ?? $person['last_name'] ?? '') ?>" required>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="email">E-posta <span class="text-red-500">*</span></label>
                    <input class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-corporate-600 focus:ring-1 focus:ring-corporate-600" id="email" name="email" type="email" value="<?= htmlspecialchars($_POST['email'] ?? $person['email'] ?? '') ?>" required>
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="phone">Telefon</label>
                    <input class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-corporate-600 focus:ring-1 focus:ring-corporate-600" id="phone" name="phone" type="text" value="<?= htmlspecialchars($_POST['phone'] ?? $person['phone'] ?? '') ?>">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="department_id">Departman</label>
                    <select class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-corporate-600 focus:ring-1 focus:ring-corporate-600 bg-white" id="department_id" name="department_id">
                        <option value="">Seçiniz...</option>
                        <?php 
                        $selected_dept = $_POST['department_id'] ?? $person['department_id'] ?? '';
                        foreach ($departments as $dept): 
                        ?>
                            <option value="<?= $dept['id'] ?>" <?= $selected_dept == $dept['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($dept['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="position">Pozisyon</label>
                    <input class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-corporate-600 focus:ring-1 focus:ring-corporate-600" id="position" name="position" type="text" value="<?= htmlspecialchars($_POST['position'] ?? $person['position'] ?? '') ?>">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="hire_date">İşe Giriş Tarihi <span class="text-red-500">*</span></label>
                    <input class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-corporate-600 focus:ring-1 focus:ring-corporate-600" id="hire_date" name="hire_date" type="date" value="<?= htmlspecialchars($_POST['hire_date'] ?? $person['hire_date'] ?? date('Y-m-d')) ?>" required>
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="status">Durum</label>
                    <select class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-corporate-600 focus:ring-1 focus:ring-corporate-600 bg-white" id="status" name="status">
                        <?php $selected_status = $_POST['status'] ?? $person['status'] ?? 'Aktif'; ?>
                        <option value="Aktif" <?= $selected_status == 'Aktif' ? 'selected' : '' ?>>Aktif</option>
                        <option value="Pasif" <?= $selected_status == 'Pasif' ? 'selected' : '' ?>>Pasif</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center justify-end pt-4 border-t border-gray-100">
                <a href="personnel.php" class="text-gray-500 hover:text-gray-700 font-medium mr-4 transition-colors">İptal</a>
                <button type="submit" class="bg-corporate-600 hover:bg-corporate-700 text-white font-bold py-3 px-8 rounded-lg transition-colors shadow-sm">
                    <?= $action == 'add' ? 'Personeli Kaydet' : 'Değişiklikleri Kaydet' ?>
                </button>
            </div>
        </form>
    </div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
