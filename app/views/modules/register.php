<div class="row justify-content-center mt-5">
    <div class="col-md-6">
        <div class="card card-custom p-4">
            <h3 class="mb-3">Registro de Usuario</h3>
            <form action="index.php?url=register" method="POST">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Apellido</label>
                        <input type="text" name="surname" class="form-control" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Cédula de Identidad</label>
                    <input type="text" name="ci" class="form-control" placeholder="Ej: 12345678" required>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Fecha de Nacimiento</label>
                        <input type="date" name="birth" class="form-control">
                    </div>
                    <div class="col-md-6 mb-3"></div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Contraseña</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Confirmar contraseña</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-success w-100">Crear cuenta</button>
            </form>
            <div class="mt-3 text-center text-white-50">
                ¿Ya tienes cuenta? <a href="index.php?url=login">Ingresa aquí</a>
            </div>
        </div>
    </div>
</div>
