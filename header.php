<?php
// includes/header.php
require_once 'includes/auth.php';
requireLogin();

$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kurumsal Personel Takip Sistemi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        corporate: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            800: '#1e40af',
                            900: '#1e3a8a',
                        }
                    }
                }
            }
        }
    </script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 text-gray-800 font-sans antialiased flex h-screen overflow-hidden">

    <!-- Sidebar -->
    <aside class="w-64 bg-corporate-900 text-white flex flex-col hidden md:flex">
        <div class="h-16 flex items-center justify-center border-b border-corporate-800">
            <h1 class="text-xl font-bold tracking-wider">KPTS</h1>
        </div>
        <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
            <a href="index.php" class="flex items-center px-4 py-3 rounded-lg transition-colors <?= $current_page == 'index.php' ? 'bg-corporate-800 text-white' : 'text-corporate-100 hover:bg-corporate-800' ?>">
                <i class="fas fa-tachometer-alt w-6"></i>
                <span>Dashboard</span>
            </a>
            <a href="personnel.php" class="flex items-center px-4 py-3 rounded-lg transition-colors <?= in_array($current_page, ['personnel.php', 'personnel_form.php']) ? 'bg-corporate-800 text-white' : 'text-corporate-100 hover:bg-corporate-800' ?>">
                <i class="fas fa-users w-6"></i>
                <span>Personel</span>
            </a>
            <a href="departments.php" class="flex items-center px-4 py-3 rounded-lg transition-colors <?= in_array($current_page, ['departments.php']) ? 'bg-corporate-800 text-white' : 'text-corporate-100 hover:bg-corporate-800' ?>">
                <i class="fas fa-building w-6"></i>
                <span>Departmanlar</span>
            </a>
            <a href="leaves.php" class="flex items-center px-4 py-3 rounded-lg transition-colors <?= in_array($current_page, ['leaves.php', 'leave_form.php']) ? 'bg-corporate-800 text-white' : 'text-corporate-100 hover:bg-corporate-800' ?>">
                <i class="fas fa-calendar-alt w-6"></i>
                <span>İzinler</span>
            </a>
        </nav>
        <div class="p-4 border-t border-corporate-800">
            <div class="flex items-center mb-4 px-2 text-sm text-corporate-100">
                <i class="fas fa-user-circle text-xl mr-2"></i>
                <span><?= htmlspecialchars($_SESSION['username'] ?? 'Kullanıcı') ?></span>
            </div>
            <a href="logout.php" class="flex items-center justify-center w-full px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors">
                <i class="fas fa-sign-out-alt mr-2"></i>
                Çıkış Yap
            </a>
        </div>
    </aside>

    <!-- Mobile Header -->
    <div class="md:hidden flex items-center justify-between bg-corporate-900 text-white h-16 px-4 absolute w-full z-10">
        <h1 class="text-xl font-bold">KPTS</h1>
        <!-- Mobile Menu Toggle could be added here, keeping it simple for now -->
        <a href="logout.php" class="text-sm bg-red-600 px-3 py-1 rounded">Çıkış</a>
    </div>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-screen overflow-hidden pt-16 md:pt-0">
        <div class="flex-1 overflow-y-auto p-6 lg:p-8">
