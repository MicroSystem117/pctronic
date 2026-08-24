<?php
$status = isset($_GET['status']) ? $_GET['status'] : null;
$alerts = [
    'answers_empty' => ['type' => 'warning', 'message' => 'Completa todas las respuestas.'],
    'wrong_answers' => ['type' => 'danger', 'message' => 'Alguna respuesta es incorrecta. Intenta de nuevo.']
];
$questions = isset($questions) && is_array($questions) ? $questions : [];
?>
<div class="auth-container d-flex justify-content-center align-items-center min-vh-100 py-5">
    <div class="auth-card card card-custom shadow-lg" style="max-width: 520px; width: 100%;">
        <div class="card-body px-4 py-5">
            <div class="text-center mb-4">
                <div class="auth-brand mb-3">
                    <img src="<?php echo htmlspecialchars(app_url('assets/Logo.png'), ENT_QUOTES, 'UTF-8'); ?>" alt="PCtronic" class="img-fluid">
                </div>
                <p class="text-white-50 mb-0">Responde tus preguntas de seguridad</p>
            </div>

            <?php if ($status && isset($alerts[$status])): ?>
                <div class="alert alert-<?php echo $alerts[$status]['type']; ?> alert-dismissible fade show" role="alert">
                    <?php echo $alerts[$status]['message']; ?>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>
            <?php endif; ?>

            <form action="index.php?url=auth/verify_questions" method="POST">
                <?php foreach ($questions as $q): ?>
                    <div class="mb-3">
                        <label class="form-label text-white"><?php echo htmlspecialchars($q['label'], ENT_QUOTES, 'UTF-8'); ?></label>
                        <input type="text" name="<?php echo htmlspecialchars($q['key'], ENT_QUOTES, 'UTF-8'); ?>" class="form-control bg-dark text-white border-secondary" placeholder="Tu respuesta" required autofocus>
                    </div>
                <?php endforeach; ?>
                <button type="submit" class="btn btn-primary w-100">Verificar respuestas</button>
            </form>

            <div class="text-center mt-3">
                <a href="index.php?url=login" class="text-white-50"><i class="bi bi-arrow-left"></i> Cancelar</a>
            </div>
        </div>
    </div>
</div>
