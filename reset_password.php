<?php
require_once __DIR__ . '/config/branding.php';
require_once __DIR__ . '/config/conexion.php';

$error = '';
$success = '';

$token = $_GET['token'] ?? ($_POST['token'] ?? '');

if (!$token) {
    $error = 'Token inválido.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_password'])) {
    $token = $_POST['token'] ?? '';
    $password = $_POST['new_password'] ?? '';
    $password2 = $_POST['new_password_confirm'] ?? '';

    if (strlen($password) < 8) {
        $error = 'La contraseña debe tener al menos 8 caracteres.';
    } elseif ($password !== $password2) {
        $error = 'Las contraseñas no coinciden.';
    } else {
        // Validar token
        $stmt = $conn->prepare('SELECT id, token_expira FROM usuarios WHERE token_recovery = ? LIMIT 1');
        $stmt->bind_param('s', $token);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $res->num_rows === 1) {
            $row = $res->fetch_assoc();
            if ($row['token_expira'] < date('Y-m-d H:i:s')) {
                $error = 'El token expiró. Solicitá uno nuevo.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt2 = $conn->prepare('UPDATE usuarios SET password = ?, token_recovery = NULL, token_expira = NULL WHERE id = ?');
                $stmt2->bind_param('si', $hash, $row['id']);
                if ($stmt2->execute()) {
                    $success = 'Contraseña actualizada correctamente. Podés iniciar sesión.';
                } else {
                    $error = 'Error al actualizar la contraseña.';
                }
                $stmt2->close();
            }
        } else {
            $error = 'Token inválido.';
        }
        $stmt->close();
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Restablecer contraseña | <?= BRAND_NAME ?></title>
    <link rel="stylesheet" href="assets/css/global.css?v=<?= time(); ?>">
</head>
<body>
    <div style="max-width:480px;margin:60px auto;padding:20px;">
        <h2>Restablecer contraseña</h2>

        <?php if ($error): ?>
            <div style="background:#fdecea;color:#991b1b;padding:12px;border-radius:8px;margin-bottom:12px;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div style="background:#e6ffed;color:#064e3b;padding:12px;border-radius:8px;margin-bottom:12px;">
                <?= htmlspecialchars($success) ?>
            </div>
            <p><a href="login.php">Ir a login</a></p>
        <?php else: ?>
            <form method="post" action="">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                <label>Nueva contraseña</label>
                <input type="password" name="new_password" required minlength="8" style="width:100%;padding:10px;margin:8px 0;border-radius:6px;border:1px solid #333;background:#111;color:#fff;">
                <label>Confirmar contraseña</label>
                <input type="password" name="new_password_confirm" required minlength="8" style="width:100%;padding:10px;margin:8px 0;border-radius:6px;border:1px solid #333;background:#111;color:#fff;">
                <button type="submit" style="padding:10px 16px;background:#c5a059;color:#000;border:none;border-radius:6px;">Guardar</button>
            </form>
        <?php endif; ?>

    </div>
</body>
</html>
