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

    <div class="col-md-6">
        <div class="card card-custom p-4 h-100">
            <h5 class="mb-3">Restablecer respaldo</h5>
            <p class="text-white-50">Carga un archivo SQL válido para restaurar el estado de la base de datos.</p>
            <form action="index.php?url=database" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="restore">
                <div class="mb-3">
                    <input type="file" name="sql_file" accept=".sql" class="form-control form-control-sm" required>
                </div>
                <button type="submit" class="btn btn-warning">
                    <i class="bi bi-arrow-counterclockwise"></i> Restaurar Respaldo
                </button>
            </form>
            <div class="mt-4 text-white-50 small">
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
