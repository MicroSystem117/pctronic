<div class="row justify-content-center mt-5">
    <div class="col-md-5">
        <div class="card card-custom p-4">
            <h3 class="mb-3">Iniciar Sesión</h3>
            <form action="index.php?url=login" method="POST">
                <div class="mb-3">
                    <label class="form-label">Cédula de Identidad</label>
                    <input type="text" name="ci" class="form-control" placeholder="Ej: 12345678" inputmode="numeric" pattern="[0-9]+" oninput="this.value = this.value.replace(/[^0-9]/g, '')" title="Solo se permiten números" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Contraseña</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Ingresar</button>
            </form>
            <div class="mt-3 text-center text-white-50">
                ¿Aún no tienes cuenta? <a href="index.php?url=register">Regístrate aquí</a>
            </div>
        </div>
    </div>
</div>
