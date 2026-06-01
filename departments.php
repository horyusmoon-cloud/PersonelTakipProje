<?php
// departments.php
require_once 'config/database.php';
require_once 'includes/header.php';

$action = $_GET['action'] ?? 'list';
$error = '';
$success = '';

// Handle actions
if ($action == 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM departments WHERE id = ?");
    if($stmt->execute([$id])) {
        // success
        header("Location: departments.php?msg=deleted");
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (empty($name)) {
        $error = "Departman adı zorunludur.";
    } else {
        if ($action == 'add') {
            $stmt = $pdo->prepare("INSERT INTO departments (name, description) VALUES (?, ?)");
            $stmt->execute([$name, $description]);
            header("Location: departments.php?msg=added");
            exit;
        } elseif ($action == 'edit' && isset($_GET['id'])) {
            $id = $_GET['id'];
            $stmt = $pdo->prepare("UPDATE departments SET name = ?, description = ? WHERE id = ?");
            $stmt->execute([$name, $description, $id]);
            header("Location: departments.php?msg=updated");
            exit;
        }
    }
}

// Fetch data for forms or lists
$department = null;
if ($action == 'edit' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM departments WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $department = $stmt->fetch();
    if (!$department) {
        die("Departman bulunamadı.");
    }
}

$departments = [];
if ($action == 'list') {
    $departments = $pdo->query("SELECT * FROM departments ORDER BY name ASC")->fetchAll();
}
?>

<div class="mb-8 flex justify-between items-center">
    <div>
        <h2 class="text-3xl font-bold text-gray-800">Departmanlar</h2>
        <p class="text-gray-600 mt-1">Şirket içi departmanları yönetin.</p>
    </div>
    <?php if ($action == 'list'): ?>
    <a href="?action=add" class="bg-corporate-600 hover:bg-corporate-700 text-white px-4 py-2 rounded-lg transition-colors shadow-sm flex items-center">
        <i class="fas fa-plus mr-2"></i> Yeni Ekle
    </a>
    <?php else: ?>
    <a href="departments.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors shadow-sm flex items-center">
        <i class="fas fa-arrow-left mr-2"></i> Geri Dön
    </a>
    <?php endif; ?>
</div>

<?php if (isset($_GET['msg'])): ?>
    <div class="bg-green-50 text-green-700 p-4 rounded-lg mb-6 border border-green-200">
        <?php 
        if($_GET['msg'] == 'added') echo "Departman başarıyla eklendi.";
        if($_GET['msg'] == 'updated') echo "Departman başarıyla güncellendi.";
        if($_GET['msg'] == 'deleted') echo "Departman başarıyla silindi.";
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
                        <th class="px-6 py-4 font-medium">Departman Adı</th>
                        <th class="px-6 py-4 font-medium">Açıklama</th>
                        <th class="px-6 py-4 font-medium text-right">İşlemler</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if(empty($departments)): ?>
                    <tr>
                        <td colspan="3" class="px-6 py-8 text-center text-gray-500">Kayıt bulunamadı.</td>
                    </tr>
                    <?php else: foreach ($departments as $dept): ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 font-medium text-gray-800"><?= htmlspecialchars($dept['name']) ?></td>
                        <td class="px-6 py-4 text-gray-600"><?= htmlspecialchars($dept['description'] ?? '-') ?></td>
                        <td class="px-6 py-4 text-right">
                            <a href="?action=edit&id=<?= $dept['id'] ?>" class="text-blue-600 hover:text-blue-900 mx-2 p-2 rounded hover:bg-blue-50 transition-colors" title="Düzenle">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="?action=delete&id=<?= $dept['id'] ?>" onclick="return confirm('Bu departmanı silmek istediğinize emin misiniz? (Bağlı personellerin departmanı boşaltılacaktır)')" class="text-red-600 hover:text-red-900 p-2 rounded hover:bg-red-50 transition-colors" title="Sil">
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
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 md:p-8 max-w-2xl">
        <form method="POST" action="">
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="name">Departman Adı <span class="text-red-500">*</span></label>
                <input class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-corporate-600 focus:ring-1 focus:ring-corporate-600 transition-colors" id="name" name="name" type="text" value="<?= htmlspecialchars($department['name'] ?? '') ?>" required>
            </div>
            
            <div class="mb-8">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="description">Açıklama</label>
                <textarea class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-corporate-600 focus:ring-1 focus:ring-corporate-600 transition-colors" id="description" name="description" rows="4"><?= htmlspecialchars($department['description'] ?? '') ?></textarea>
            </div>
            
            <div class="flex items-center justify-end">
                <a href="departments.php" class="text-gray-500 hover:text-gray-700 font-medium mr-4">İptal</a>
                <button type="submit" class="bg-corporate-600 hover:bg-corporate-700 text-white font-bold py-3 px-6 rounded-lg transition-colors shadow-sm">
                    <?= $action == 'add' ? 'Kaydet' : 'Güncelle' ?>
                </button>
            </div>
        </form>
    </div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
