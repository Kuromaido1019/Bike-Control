<!-- Logout Modal-->
<div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">¿Listo para salir?</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">Selecciona "Cerrar sesión" a continuación si estás listo para finalizar tu sesión actual.</div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancelar</button>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
                <a class="btn btn-primary" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    Cerrar sesión
                </a>
            </div>
        </div>
    </div>
</div>
