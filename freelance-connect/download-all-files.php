<?php
session_start();
require_once 'config/db.php';

// Check if user is logged in and is a client
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'client') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$deal_id = $_GET['deal_id'] ?? '';

if (empty($deal_id)) {
    header("Location: view-proposals.php");
    exit();
}

// Verify client access to this deal
try {
    $stmt = $pdo->prepare("SELECT d.id, p.title FROM deals d LEFT JOIN proposals pr ON d.proposal_id = pr.id LEFT JOIN projects p ON pr.project_id = p.id WHERE d.id = ? AND d.client_id = ?");
    $stmt->execute([$deal_id, $user_id]);
    $deal = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$deal) {
        header("Location: view-proposals.php");
        exit();
    }
} catch (PDOException $e) {
    header("Location: view-proposals.php");
    exit();
}

// Get all files for this deal
try {
    $stmt = $pdo->prepare("SELECT * FROM deal_files WHERE deal_id = ? ORDER BY uploaded_at ASC");
    $stmt->execute([$deal_id]);
    $files = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    header("Location: client-download-files.php?id=" . $deal_id);
    exit();
}

if (empty($files)) {
    header("Location: client-download-files.php?id=" . $deal_id);
    exit();
}

// Create ZIP file
$zip = new ZipArchive();
$zipName = 'project_' . $deal_id . '_files_' . date('Y-m-d_H-i-s') . '.zip';
$zipPath = 'uploads/temp/' . $zipName;

// Create temp directory if it doesn't exist
if (!is_dir('uploads/temp/')) {
    mkdir('uploads/temp/', 0777, true);
}

if ($zip->open($zipPath, ZipArchive::CREATE) !== TRUE) {
    header("Location: client-download-files.php?id=" . $deal_id);
    exit();
}

// Add files to ZIP
foreach ($files as $file) {
    if (file_exists($file['file_path'])) {
        // Use original filename in ZIP
        $zip->addFile($file['file_path'], $file['file_name']);
    }
}

$zip->close();

// Set headers for download
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . $zipName . '"');
header('Content-Length: ' . filesize($zipPath));
header('Pragma: no-cache');
header('Expires: 0');

// Output file and delete it
readfile($zipPath);
unlink($zipPath);
exit();
?>