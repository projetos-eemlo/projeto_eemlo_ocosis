<?php
session_start();
require 'conexao.php';

// Verifica se usuário está logado e é admin
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['nivel'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
    $_SESSION['msg_erro'] = "ID inválido.";
    header("Location: usuarios.php");
    exit;
}

// Evita excluir o próprio usuário logado
if ($id == $_SESSION['usuario']['id']) {
    $_SESSION['msg_erro'] = "Você não pode excluir seu próprio usuário.";
    header("Location: usuarios.php");
    exit;
}

// Prepara e executa a exclusão
$stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $id);
if ($stmt->execute()) {
    $_SESSION['msg_sucesso'] = "Usuário excluído com sucesso.";
} else {
    $_SESSION['msg_erro'] = "Erro ao excluir usuário: " . $conn->error;
}

header("Location: usuarios.php");
exit;
