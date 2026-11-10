<?php
$payment = $data['payment'] ?? [];
$receiptNumber = $data['receipt_number'] ?? 'REC-000000';
$qrUrl = $data['qr_url'] ?? '';
$isApproved = ($payment['status'] ?? '') === 'Aprobado';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($data['page_title'] ?? 'Recibo de Pago'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #0f172a !important;
            color: #1e293b;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            padding: 30px 15px;
        }
        .receipt-card {
            max-width: 780px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }
        .receipt-header {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #ffffff;
            padding: 30px;
        }
        .receipt-body {
            padding: 35px 30px;
        }
        .receipt-footer {
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            padding: 20px 30px;
            font-size: 0.85rem;
            color: #64748b;
        }
        .stamp-badge {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 6px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 0.85rem;
        }
        .stamp-approved {
            background-color: #dcfce7;
            color: #15803d;
            border: 2px solid #86efac;
        }
        .stamp-pending {
            background-color: #fef9c3;
            color: #854d0e;
            border: 2px solid #fde047;
        }
        .stamp-rejected {
            background-color: #fee2e2;
            color: #b91c1c;
            border: 2px solid #fca5a5;
        }
        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
                color: #000000 !important;
            }
            .no-print {
                display: none !important;
            }
            .receipt-card {
                box-shadow: none !important;
                border: none !important;
                max-width: 100% !important;
                margin: 0 !important;
            }
            .receipt-header {
                background: #0f172a !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <!-- Barra de acciones (No imprimible) -->
    <div class="d-flex justify-content-between align-items-center mb-3 no-print" style="max-width: 780px; margin: 0 auto;">
        <a href="index.php?url=payments" class="btn btn-outline-light btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Volver a Pagos
        </a>
        <div class="d-flex gap-2">
            <button onclick="window.print();" class="btn btn-primary btn-sm">
                <i class="bi bi-printer me-1"></i> Imprimir / Guardar en PDF
            </button>
        </div>
    </div>

    <!-- Tarjeta del Recibo -->
    <div class="receipt-card">
        <!-- Encabezado -->
        <div class="receipt-header">
            <div class="row align-items-center">
                <div class="col-8">
                    <img src="<?php echo htmlspecialchars(app_url('assets/Logo.png'), ENT_QUOTES, 'UTF-8'); ?>" alt="PCtronic" style="max-height: 48px;" class="mb-2">
                    <h5 class="mb-0 fw-bold">Starlink Control • PCtronic</h5>
                    <small class="opacity-75">Servicios de Conectividad Satelital</small>
                </div>
                <div class="col-4 text-end">
                    <div class="badge bg-primary bg-opacity-50 text-white p-2 fs-6 mb-1 font-monospace"><?php echo htmlspecialchars($receiptNumber); ?></div>
                    <div class="small opacity-75">Fecha: <?php echo date('d/m/Y', strtotime($payment['payment_date'] ?? 'now')); ?></div>
                </div>
            </div>
        </div>

        <!-- Cuerpo -->
        <div class="receipt-body">
            <!-- Estatus y Monto -->
            <div class="row align-items-center mb-4 pb-3 border-bottom">
                <div class="col-sm-7">
                    <div class="text-muted small text-uppercase fw-bold mb-1">Estado de la Transacción</div>
                    <?php if ($isApproved): ?>
                        <div class="stamp-badge stamp-approved">
                            <i class="bi bi-patch-check-fill me-1"></i> PAGO APROBADO
                        </div>
                    <?php elseif (($payment['status'] ?? '') === 'Rechazado'): ?>
                        <div class="stamp-badge stamp-rejected">
                            <i class="bi bi-x-circle-fill me-1"></i> PAGO RECHAZADO
                        </div>
                    <?php else: ?>
                        <div class="stamp-badge stamp-pending">
                            <i class="bi bi-clock-history me-1"></i> PAGO EN REVISIÓN
                        </div>
                    <?php endif; ?>
                </div>
                <div class="col-sm-5 text-sm-end mt-3 mt-sm-0">
                    <div class="text-muted small text-uppercase fw-bold mb-1">Monto Total Recibido</div>
                    <div class="display-6 fw-bold text-primary">
                        $<?php echo number_format((float)($payment['amount'] ?? 0), 2); ?>
                        <span class="fs-6 fw-normal text-muted"><?php echo htmlspecialchars($payment['currency'] ?? 'USD'); ?></span>
                    </div>
                </div>
            </div>

            <!-- Datos del Cliente y Servicio -->
            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <h6 class="text-uppercase fw-bold text-secondary small mb-2"><i class="bi bi-person me-1"></i> Datos del Titular</h6>
                    <div class="fw-bold fs-6 text-dark"><?php echo htmlspecialchars($payment['cliente'] ?? 'Cliente General'); ?></div>
                    <div class="text-muted small">
                        <?php if (!empty($payment['client_ci'])): ?>
                            <div><strong>Cédula / ID:</strong> <?php echo htmlspecialchars($payment['client_ci']); ?></div>
                        <?php endif; ?>
                        <?php if (!empty($payment['client_phone'])): ?>
                            <div><strong>Teléfono:</strong> <?php echo htmlspecialchars($payment['client_phone']); ?></div>
                        <?php endif; ?>
                        <?php if (!empty($payment['pais'])): ?>
                            <div><strong>País / Región:</strong> <?php echo htmlspecialchars($payment['pais']); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <h6 class="text-uppercase fw-bold text-secondary small mb-2"><i class="bi bi-info-circle me-1"></i> Detalles del Pago</h6>
                    <div class="text-muted small">
                        <div><strong>Fecha de Operación:</strong> <?php echo date('d/m/Y', strtotime($payment['payment_date'] ?? 'now')); ?></div>
                        <div><strong>Meses Cancelados:</strong> <?php echo htmlspecialchars((string)($payment['months_paid'] ?? 1)); ?> mes(es)</div>
                        <?php if (!empty($payment['reviewed_at'])): ?>
                            <div><strong>Aprobado el:</strong> <?php echo date('d/m/Y H:i', strtotime($payment['reviewed_at'])); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Tabla de Conceptos -->
            <div class="table-responsive mb-4">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr class="small text-uppercase text-secondary">
                            <th>Concepto / Equipo</th>
                            <th>Plan de Servicio</th>
                            <th>Meses</th>
                            <th class="text-end">Importe</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <div class="fw-bold text-dark">Antena Starlink</div>
                                <div class="small text-muted font-monospace">Serial: <?php echo htmlspecialchars($payment['serial'] ?? '-'); ?></div>
                                <?php if (!empty($payment['nickname'])): ?>
                                    <div class="small text-muted">Alias: <?php echo htmlspecialchars($payment['nickname']); ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-secondary"><?php echo htmlspecialchars($payment['nombre_plan'] ?? 'Servicio Estándar'); ?></span>
                            </td>
                            <td><?php echo htmlspecialchars((string)($payment['months_paid'] ?? 1)); ?></td>
                            <td class="text-end fw-bold text-dark">$<?php echo number_format((float)($payment['amount'] ?? 0), 2); ?></td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3" class="text-end">Total Pagado:</th>
                            <th class="text-end fs-6 text-primary">$<?php echo number_format((float)($payment['amount'] ?? 0), 2); ?> <?php echo htmlspecialchars($payment['currency'] ?? 'USD'); ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Código QR de Verificación y Nota Legal -->
            <div class="row align-items-center g-3 pt-3 border-top">
                <div class="col-sm-3 text-center">
                    <?php if (!empty($qrUrl)): ?>
                        <img src="<?php echo htmlspecialchars($qrUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="QR de Verificación" class="img-thumbnail p-1" style="max-width: 120px;">
                        <div class="small text-muted mt-1" style="font-size: 0.7rem;">Escanear para validar</div>
                    <?php endif; ?>
                </div>
                <div class="col-sm-9">
                    <h6 class="fw-bold mb-1 small text-dark"><i class="bi bi-shield-check text-success me-1"></i> Comprobante Digital Oficial</h6>
                    <p class="text-muted small mb-0">
                        Este documento certifica la recepción y aprobación formal del pago correspondiente al servicio de internet satelital Starlink administrado por PCtronic. El código QR contiene la firma digital de verificación de la transacción.
                    </p>
                </div>
            </div>
        </div>

        <!-- Pie del Recibo -->
        <div class="receipt-footer text-center">
            <div>Starlink Control • PCtronic • Sistema de Administración y Cobranzas Técnicas</div>
            <div class="small opacity-75">Comprobante generado electrónicamente • ID de Transacción: #<?php echo $payment['id_payment'] ?? '0'; ?></div>
        </div>
    </div>
</div>

</body>
</html>

