<?php
$status = isset($_GET['status']) ? $_GET['status'] : null;
$alerts = [
    'security_empty' => ['type' => 'warning', 'message' => 'Completa todas las preguntas y respuestas.']
];
$sec = isset($sec) && is_array($sec) ? $sec : [];
?>
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card card-custom shadow-lg">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <h3 class="text-white mb-1"><i class="bi bi-shield-lock"></i> Preguntas de Seguridad</h3>
                    <p class="text-white-50 mb-0">Configura 3 preguntas que te permitirán recuperar tu contraseña en caso de olvidarla.</p>
                </div>

                <?php if ($status && isset($alerts[$status])): ?>
                    <div class="alert alert-<?php echo $alerts[$status]['type']; ?> alert-dismissible fade show" role="alert">
                        <?php echo $alerts[$status]['message']; ?>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                    </div>
                <?php endif; ?>

                <form action="index.php?url=auth/manage_security" method="POST">
                    <div class="mb-3">
                        <label class="form-label text-white">Pregunta 1</label>
                        <input type="text" name="question1" class="form-control bg-dark text-white border-secondary" placeholder="Ej: ¿Cuál es el nombre de tu primera mascota?" value="<?php echo htmlspecialchars($sec['question1'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                        <input type="text" name="answer1" class="form-control bg-dark text-white border-secondary mt-2" placeholder="Respuesta 1" value="<?php echo htmlspecialchars($sec['answer1'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-white">Pregunta 2</label>
                        <input type="text" name="question2" class="form-control bg-dark text-white border-secondary" placeholder="Ej: ¿Ciudad donde naciste?" value="<?php echo htmlspecialchars($sec['question2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                        <input type="text" name="answer2" class="form-control bg-dark text-white border-secondary mt-2" placeholder="Respuesta 2" value="<?php echo htmlspecialchars($sec['answer2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-white">Pregunta 3</label>
                        <input type="text" name="question3" class="form-control bg-dark text-white border-secondary" placeholder="Ej: ¿Nombre de tu escuela primaria?" value="<?php echo htmlspecialchars($sec['question3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                        <input type="text" name="answer3" class="form-control bg-dark text-white border-secondary mt-2" placeholder="Respuesta 3" value="<?php echo htmlspecialchars($sec['answer3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Guardar preguntas</button>
                </form>
            </div>
        </div>
    </div>
</div>
