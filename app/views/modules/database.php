<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2><i class="bi bi-hdd-network"></i> Base de Datos</h2>
        <p class="text-white-50 mb-0">Respalda y restablece tu base de datos desde el panel de administración.</p>
    </div>
</div>

<div class="row gy-4">
    <div class="col-md-6">
        <div class="card card-custom p-4 h-100">
            <h5 class="mb-3">Crear respaldo</h5>
            <p class="text-white-50">Genera un archivo SQL con la estructura y datos actuales de la base de datos.</p>
            <div class="d-flex gap-2 mb-3 flex-wrap">
                <form action="index.php?url=database" method="POST">
                    <input type="hidden" name="action" value="backup">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-cloud-download-fill"></i> Generar Respaldo
                    </button>
                </form>
                <form action="index.php?url=database" method="POST">
                    <input type="hidden" name="action" value="export_csv">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-file-earmark-spreadsheet-fill"></i> Exportar a CSV
                    </button>
                </form>
            </div>
            <p class="text-white-50 small">La exportación descarga todos los datos en archivos CSV comprimidos en un ZIP.</p>
            <?php $backupFolderPath = realpath(__DIR__ . '/../../storage/backups') ?: (__DIR__ . '/../../storage/backups'); ?>
            <div class="mt-4 text-white-50 small">
                <strong>Ubicación en el servidor:</strong>
                <div><code><?php echo htmlspecialchars($backupFolderPath); ?></code></div>
                <div class="mt-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="copyBackupFolderPath">
                        <i class="bi bi-clipboard"></i> Copiar ruta del servidor
                    </button>
                </div>
            </div>
        </div>
    </div>

<?php
$sqlBackups = array_filter($backups, function($b) {
    return isset($b['type']) && strtolower($b['type']) === 'sql';
});
?>
    <div class="col-md-6">
        <div class="card card-custom p-4 h-100">
            <h5 class="mb-3">Restablecer respaldo</h5>
            <p class="text-white-50">Restaura la base de datos desde los respaldos generados o subiendo un archivo SQL.</p>

            <?php if (!empty($sqlBackups)): ?>
                <form action="index.php?url=database" method="POST" class="mb-3" onsubmit="return confirm('¿Restablecer la base de datos con el respaldo seleccionado? Los datos actuales serán reemplazados.');">
                    <input type="hidden" name="action" value="restore_existing">
                    <label class="form-label text-white-50 small mb-1">Seleccionar respaldo del servidor:</label>
                    <div class="input-group mb-3">
                        <select name="file_name" class="form-select form-select-sm" required>
                            <option value="">-- Elige un respaldo generado --</option>
                            <?php foreach ($sqlBackups as $sb): ?>
                                <option value="<?php echo htmlspecialchars($sb['name']); ?>">
                                    <?php echo htmlspecialchars($sb['name']); ?> (<?php echo date('d/m/Y H:i', $sb['modified']); ?> - <?php echo number_format($sb['size'] / 1024, 2); ?> KB)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-warning btn-sm">
                            <i class="bi bi-arrow-counterclockwise"></i> Restaurar
                        </button>
                    </div>
                </form>
            <?php endif; ?>

            <details class="text-white-50 small mb-2" <?php echo empty($sqlBackups) ? 'open' : ''; ?>>
                <summary class="cursor-pointer text-info mb-2">
                    <i class="bi bi-upload me-1"></i> O subir un archivo .sql desde tu equipo
                </summary>
                <form action="index.php?url=database" method="POST" enctype="multipart/form-data" class="mt-2">
                    <input type="hidden" name="action" value="restore">
                    <div class="input-group input-group-sm">
                        <input type="file" name="sql_file" accept=".sql" class="form-control form-control-sm" required>
                        <button type="submit" class="btn btn-outline-warning">
                            Subir y Restaurar
                        </button>
                    </div>
                </form>
            </details>

            <div class="mt-3 text-white-50 small">
                <strong>Advertencia:</strong> La restauración reemplaza los datos actuales. Úsala con cuidado.
            </div>
        </div>
    </div>
</div>

<div class="card card-custom p-4 mt-4">
    <h5 class="mb-3">Archivos generados</h5>
    <?php if (!empty($backups)): ?>
        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle mb-0 pdf-exportable" data-pdf-title="Archivos de respaldo">
                <thead class="table-light">
                    <tr>
                        <th>Archivo</th>
                        <th>Tipo</th>
                        <th>Tamaño</th>
                        <th>Fecha</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($backups as $backup): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($backup['name']); ?></td>
                            <td><?php echo strtoupper(htmlspecialchars($backup['type'])); ?></td>
                            <td><?php echo number_format($backup['size'] / 1024, 2); ?> KB</td>
                            <td><?php echo date('d/m/Y H:i:s', $backup['modified']); ?></td>
                            <td>
                                <?php if (strtolower($backup['type']) === 'sql'): ?>
                                    <a href="index.php?url=database&action=restore_existing&file=<?php echo rawurlencode($backup['name']); ?>" class="btn btn-sm btn-warning me-2" onclick="return confirm('¿Restaurar la base de datos a este respaldo (<?php echo htmlspecialchars($backup['name'], ENT_QUOTES); ?>)? Esta acción sobrescribirá los datos actuales.');">
                                        <i class="bi bi-arrow-counterclockwise"></i> Restaurar
                                    </a>
                                <?php endif; ?>
                                <a href="index.php?url=database&action=download&file=<?php echo rawurlencode($backup['name']); ?>" class="btn btn-sm btn-outline-light me-2">
                                    <i class="bi bi-download"></i> Descargar
                                </a>
                                <a href="index.php?url=database&action=delete&file=<?php echo rawurlencode($backup['name']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar este respaldo? Esta acción no se puede deshacer.');">
                                    <i class="bi bi-trash"></i> Eliminar
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="text-white-50">
            No hay respaldos guardados aún. Genera uno para poder descargarlo o restaurarlo.
        </div>
    <?php endif; ?>
</div>

<script>
    document.addEventListener('app:content-ready', function() {
        var copyButton = document.getElementById('copyBackupFolderPath');
        if (copyButton) {
            copyButton.addEventListener('click', function() {
                var pathText = '<?php echo addslashes($backupFolderPath); ?>';
                navigator.clipboard.writeText(pathText).then(function() {
                    alert('Ruta de carpeta copiada al portapapeles.');
                }).catch(function() {
                    alert('No se pudo copiar la ruta. Usa Ctrl+C para copiar manualmente.');
                });
            });
        }
    });
</script>
